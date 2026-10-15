<?php
/**
 * 2013-2024 2N Technologies
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@2n-tech.com so we can send you a copy immediately.
 *
 * @author    2N Technologies <contact@2n-tech.com>
 * @copyright 2013-2024 2N Technologies
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/ntbr.php';

class NtbrChild extends NtbrCore
{
    const PAGE = 'ntbrfull';

    const STEP_SCAN_FILES = 1;
    const STEP_SCAN_FILES_CONTINUE = 2;

    protected $scan_step = 0;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the module type (empty if full)
     *
     * @return string Light or empty
     */
    public function getTypeModule()
    {
        return '';
    }

    /**
     * Start local restore
     *
     * @param string $backup_name Name of the backup
     * @param string $type_backup Type of the backup
     *
     * @return string|bool Options of the restoration or false if failure
     */
    public function startLocalRestore($backup_name, $type_backup, $encryption_key = '')
    {
        $old_restore = $this->restore_file;
        $new_restore = _PS_ROOT_DIR_ . '/' . self::NEW_RESTORE_NAME;
        $list_old_new_backup = [];
        $restore_backup_files = [];
        $options_restore = '';
        $backup_files = $this->findOldBackups();
        $id_config = $this->config->id;

        foreach ($backup_files as $b_file) {
            if (strpos($b_file['name'], $backup_name) !== false) {
                $restore_backup_files = $b_file['part'];
                $id_config = $b_file['id_config'];
            }
        }

        if (!Apparatus::checkFileExists($old_restore)) {
            return false;
        }

        $config = new ConfigNtbr($id_config);
        $backup_dir = $config->backup_dir;

        foreach ($restore_backup_files as $old_backup_files) {
            if (!Apparatus::checkFileExists($backup_dir . $old_backup_files['name'])) {
                return false;
            }

            $list_old_new_backup[] = [
                'old' => $backup_dir . $old_backup_files['name'],
                'new' => _PS_ROOT_DIR_ . '/' . $old_backup_files['name'],
            ];
        }

        // Move restore and backup file to the root of the website
        if (!copy($old_restore, $new_restore)) {
            $this->log($this->l('Cannot move restore script to the root directory', self::PAGE), true);

            return false;
        }

        foreach ($list_old_new_backup as $old_new_backup) {
            if (!rename($old_new_backup['old'], $old_new_backup['new'])) {
                return false;
            }
        }

        $options_restore .= 'from_module=true';
        $options_restore .= '&db_server=' . urlencode((string) _DB_SERVER_);
        $options_restore .= '&db_name=' . urlencode((string) _DB_NAME_);
        $options_restore .= '&db_user=' . urlencode((string) _DB_USER_);
        $options_restore .= '&db_passwd=' . urlencode((string) _DB_PASSWD_);
        $options_restore .= '&l=' . urlencode((string) $this->context->language->iso_code);

        if ($encryption_key) {
            $options_restore .= '&crypted_backup_key=' . urlencode((string) $encryption_key);
        }

        if ($type_backup == $this->type_backup_base) {
            $options_restore .= '&do_not_restore_files=true';
        } elseif ($type_backup == $this->type_backup_file) {
            $options_restore .= '&do_not_restore_database=true';
        }

        if (!$config->disable_refresh) {
            $options_restore .= '&activate_refresh=true&refresh_time=' . urlencode((string) $config->time_between_refresh);

            if ($config->time_pause_between_refresh) {
                $options_restore .= '&time_pause_between_refresh=' . urlencode((string) $config->time_pause_between_refresh);
            }

            if ($config->time_between_progress_refresh) {
                $options_restore .= '&progress_refresh_time=' . urlencode((string) $config->time_between_progress_refresh);
            }
        }

        if (!$config->increase_server_timeout) {
            $options_restore .= '&disable_time_limit=true';
        }

        return $options_restore;
    }

    /**
     * End local restore
     *
     * @param string $backup_name Name of the backup
     * @param string $comment Comment of the backup
     * @param bool $safe If the backup is considered safe
     * @param int $id_ntbr_config Id of the config
     *
     * @return string|bool Options of the restoration or false if failure
     */
    public function endLocalRestore($backup_name, $comment, $safe, $id_ntbr_config, $success)
    {
        $backup_files = [];

        $config = new ConfigNtbr($id_ntbr_config);
        $backup_dir = $config->backup_dir;

        if (Apparatus::checkFileExists(_PS_ROOT_DIR_ . '/' . self::NEW_RESTORE_NAME)) {
            unlink(_PS_ROOT_DIR_ . '/' . self::NEW_RESTORE_NAME);
        }

        if (Apparatus::endsWith($backup_name, self::EXT_UNCOMPRESS)) { // tar
            $base_name = Tools::substr($backup_name, 0, -Apparatus::strlen(self::EXT_UNCOMPRESS));
        } elseif (Apparatus::endsWith($backup_name, self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS)) { // tar.gz
            $base_name = Tools::substr($backup_name, 0, -Apparatus::strlen(self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS));
        } elseif (Apparatus::endsWith($backup_name, self::EXT_UNCOMPRESS . '.' . self::EXT_CRYPT)) { // tar.crypt
            $base_name = Tools::substr($backup_name, 0, -Apparatus::strlen(self::EXT_UNCOMPRESS . '.' . self::EXT_CRYPT));
        } elseif (Apparatus::endsWith($backup_name, self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS . '.' . self::EXT_CRYPT)) { // tar.gz.crypt
            $base_name = Tools::substr($backup_name, 0, -Apparatus::strlen(self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS . '.' . self::EXT_CRYPT));
        }

        if (($dir = opendir(_PS_ROOT_DIR_)) !== false) {
            while (($file = readdir($dir)) !== false) {
                if ($file == '.' || $file == '..' || is_dir(_PS_ROOT_DIR_ . $file)) {
                    continue;
                }

                if (strpos($file, $base_name) !== false) {
                    $backup_files[] = [
                        'old' => _PS_ROOT_DIR_ . '/' . $file,
                        'new' => $backup_dir . $file,
                    ];
                }
            }
        }

        // Move backup file to the module backup directory
        foreach ($backup_files as $old_new_backup) {
            if (!rename($old_new_backup['old'], $old_new_backup['new'])) {
                $this->log($this->l('The backup file was not put back in the module', self::PAGE), true);

                return false;
            }
        }

        if (!Backups::backupExist($backup_name)) {
            $backup = new Backups();
            $backup->id_ntbr_config = $id_ntbr_config;
            $backup->backup_name = $backup_name;
            $backup->comment = $comment;
            $backup->safe = $safe;

            if (!$backup->add()) {
                $this->log($this->l('The backup infos were not saved', self::PAGE), true);

                return false;
            }
        }

        // Keep a copy of the restore log
        if (!$success) {
            $backup_dir = NtBackupAndRestore::getModuleBackupDirectory();
            copy(_PS_ROOT_DIR_ . '/log.txt', $backup_dir . 'log_error_restore.txt');
        }

        if (Apparatus::checkFileExists(_PS_ROOT_DIR_ . '/log.txt')) {
            unlink(_PS_ROOT_DIR_ . '/log.txt');
        }

        if (Apparatus::checkFileExists(_PS_ROOT_DIR_ . '/lastlog.txt')) {
            unlink(_PS_ROOT_DIR_ . '/lastlog.txt');
        }

        return true;
    }

    /**
     * Download a file
     *
     * @param string $path Path of the file to download
     * @param string $mime Type/mime of the file to download
     * @param string $filename New name of the file to download (optional)
     */
    public function downloadFile($path, $mime, $filename = '')
    {
        // check if file exists
        if (is_dir($path) || !Apparatus::checkFileExists($path)) {
            header('HTTP/1.0 404 Not Found');
            exit('404 Not Found');
        }

        if ($filename == '') {
            $filename = basename($path);
        }

        $xsendfile = Tools::apacheModExists('xsendfile') && $this->config->activate_xsendfile;

        if ($xsendfile) {// Use XSendFile module
            header('X-Sendfile: ' . $path); // Apache
            header('X-Accel-Redirect: ' . $path); // Nginx
            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        } else {// Using php
            parent::downloadFile($path, $mime, $filename);
        }
    }

    /**
     * Ignore product image in the backup
     *
     * @param string $current_normalized_file Path of the file to check
     *
     * @return bool If the file must be ignore or not
     */
    public function ignoreProductImage($current_normalized_file)
    {
        $ignore_this_file = false;

        // If it is source dir, or img dir, do not ignore
        if ($current_normalized_file == $this->source_dir . '/' || $current_normalized_file == $this->source_dir . '/img/') {
            return $ignore_this_file;
        }

        // Check if it is a product image
        if (strpos($current_normalized_file, $this->source_dir . '/img/p/') !== false) {
            if ($this->config->ignore_product_image == self::PRODUCT_IMG_NONE
                && strpos($current_normalized_file, $this->source_dir . '/img/p/index.php') === false
            ) {
                $ignore_this_file = true;
            }
        } else {
            if ($this->config->ignore_product_image == self::PRODUCT_IMG_ONLY) {
                $ignore_this_file = true;
            }
        }

        return $ignore_this_file;
    }

    /**
     * Get backup directory
     *
     * @return string
     */
    public function getBackupDirectory()
    {
        if ($this->config->backup_dir) {
            if (Apparatus::checkFileExists($this->config->backup_dir)) {
                return $this->config->backup_dir;
            }
        }

        return NtBackupAndRestore::getModuleBackupDirectory();
    }

    /**
     * Get list of directories to ignore
     *
     * @return array List of directories to ignore
     */
    public function getDirectoriesToIgnore()
    {
        return $this->config->ignore_directories;
    }

    /**
     * getChildrenDirectories()
     *
     * Get children directories of a given directory
     *
     * @param string $dir Parent directory
     * @param int $id_config ID of the configuration
     *
     * @return array List of directories
     */
    public function getChildrenDirectories($dir = '', $id_config = 0)
    {
        if (!$this->source_dir) {
            $this->source_dir = $this->normalizePath(_PS_ROOT_DIR_);
        }

        $list = [];
        $root = rtrim($this->normalizePath(_PS_ROOT_DIR_), '/') . '/';

        if ($dir != '') {
            $dir = rtrim($this->normalizePath($dir), '/') . '/';
        }

        if (!$id_config) {
            $id_config = ConfigNtbr::getIdDefault();
        }

        $o_config = new ConfigNtbr($id_config);

        $item_to_ignore = explode(',', $o_config->ignore_directories);
        $children = glob($root . $dir . '{,.}**', GLOB_BRACE);

        foreach ($item_to_ignore as &$item) {
            $item = trim($item);
        }

        foreach ($children as $child) {
            $path = str_replace($root, '', $child);
            $name = basename($path);

            if ($name == '.' || $name == '..') {
                continue;
            }

            if (is_dir($child)) {
                $is_dir = true;
                $child = rtrim($this->normalizePath($child), '/') . '/';
                $size = 0;
            } else {
                $is_dir = false;
                $size = $this->readableFileSize($child);
            }

            $ignore = (in_array($path, $item_to_ignore)) ? 1 : 0;
            $always_ignore = ($this->shouldNeverBeBackuped($child)) ? 1 : 0;

            $list[((int) !$is_dir) . '_' . $name] = [
                'path' => $path,
                'name' => $name,
                'ignore' => $ignore,
                'is_dir' => $is_dir,
                'always_ignore' => $always_ignore,
                'size' => $size,
            ];
        }

        uksort($list, 'strnatcmp');

        return $list;
    }

    /**
     * getScanChildren()
     *
     * Get children of a given directory
     *
     * @param string $dir Parent directory
     *
     * @return array List of children
     */
    public function getScanChildren($dir = '')
    {
        $list = [];
        $root = rtrim($this->normalizePath(_PS_ROOT_DIR_), '/') . '/';

        if ($dir != '') {
            $dir = rtrim($this->normalizePath($dir), '/') . '/';
        }

        // $children = glob($root.$dir.'{,.}**', GLOB_BRACE);
        $children = ScanSizeNtbr::getDirChildren($root . $dir);

        foreach ($children as $child) {
            $path = str_replace($root, '', $child);
            $name = basename($path);

            if ($name == '.' || $name == '..') {
                continue;
            }

            if (is_dir($child)) {
                $is_dir = true;
                $size = ScanSizeNtbr::getSizeDirByPath($child);
            } else {
                $is_dir = false;
                $size = ScanSizeNtbr::getSizeFileByPath($child);
            }

            if ($size < 0) {
                continue;
            }

            $list[$size . '_' . $path] = [
                'path' => $path,
                'name' => $name,
                'size' => $size,
                'readable_size' => $this->readableSize($size),
                'is_dir' => $is_dir,
                'is_active' => false,
                'children' => [],
            ];
        }

        uksort($list, 'strnatcmp');

        return array_reverse($list, true);
    }

    /**
     * insertScanSize()
     *
     * Insert files size in database
     *
     * @return bool Success or echec of the insert
     */
    public function insertScanSize()
    {
        if (!is_array($this->a_scanned_files_to_add) || !count($this->a_scanned_files_to_add) || !$this->config->scan_files) {
            return true;
        }

        $values = '';
        $today = date('Y-m-d H:i:s');

        foreach ($this->a_scanned_files_to_add as $path => $size) {
            $values .= '("' . pSQL($this->normalizePath($path)) . '", ' . (int) $size . ', "' . pSQL($today) . '"),';
        }

        // Substr to remove last ","
        $req = '
            INSERT INTO ' . _DB_PREFIX_ . 'ntbr_scan_size (`file_path`, `file_size`, `date_add`)
            VALUES ' . Tools::substr($values, 0, -1) . ';
        ';

        $res = Db::getInstance()->execute($req);

        if (!$res) {
            $this->log($this->l('The insert of scanned files size failed', self::PAGE), true);
            $this->log($req, true);
            $this->log(Db::getInstance()->getMsgError(), true);

            return false;
        }

        $this->a_scanned_files_to_add = [];

        return true;
    }

    /**
     * Get list of file types to ignore
     *
     * @return array List of file types to ignore
     */
    public function getFileTypesToIgnore()
    {
        return $this->config->ignore_file_types;
    }

    /**
     * Get list of tables to ignore
     *
     * @return array List of tables to ignore
     */
    public function getTablesToIgnore()
    {
        return $this->config->ignore_tables;
    }

    /**
     * Get list of tables to not recreate
     *
     * @return array List of tables to not recreate
     */
    public function getTablesToNotRecreate()
    {
        $tables_to_ignore = explode(',', str_replace(' ', '', $this->config->ignore_tables));
        $tables_to_not_recreate = explode(',', str_replace(' ', '', $this->config->not_recreate_tables));

        $tables_in_both = [];

        foreach ($tables_to_not_recreate as $table) {
            if (!in_array($table, $tables_to_ignore)) {
                continue;
            }

            $tables_in_both[] = $table;
        }

        return implode(',', $tables_in_both);
    }

    /**
     * Get backup total size
     *
     * @return float Total size of the backup
     */
    public function getBackupTotalSize()
    {
        if (is_array($this->part_list) && count($this->part_list) > 0) {
            $total_size = 0;

            foreach ($this->part_list as $part) {
                $total_size += $this->getFileSize($part);
            }

            return $total_size;
        } else {
            if ($this->config->ignore_compression) {
                return $this->getFileSize($this->uncompressed_file);
            } else {
                if ($this->config->crypt_backup) {
                    return $this->getFileSize($this->compressed_crypted_file);
                } else {
                    return $this->getFileSize($this->compressed_file);
                }
            }
        }
    }

    /**
     * Get futur tar total size
     *
     * @param int $total_size Found total size at the moment
     *
     * @return float Total size of the futur tar
     */
    public function getFuturTarTotalSize()
    {
        if ($this->next_step == self::STEP_GET_FUTUR_TAR_SIZE) {
            $this->total_size = 0;
            $this->position_file_list_file = 0;
            $this->tar_files_size = [];
            $this->part_number = 1;
            $this->next_step = self::STEP_GET_FUTUR_TAR_SIZE_CONTINUE;
        }

        if ($this->getFileSize($this->file_list_file) <= 0) {
            return false;
        }

        if (!is_resource($this->handle_file_list_file)) {
            return false;
        }

        fseek($this->handle_file_list_file, $this->position_file_list_file);

        $end_size = (self::TAR_END_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? (SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES * 2) : 0));

        while (!feof($this->handle_file_list_file)) {
            $this->checkStopScript();

            $line = rtrim(fgets($this->handle_file_list_file));

            if (!$line) {
                continue;
            }

            $pos_cut = strrpos($line, ':');

            if ($pos_cut === false) {
                continue;
            }

            $file = self::binaryToString(Tools::substr($line, 0, $pos_cut));

            if (!$file) {
                continue;
            }

            $start_size = 0;
            $content_size = 0;

            if ($file != '') {
                if (in_array($file, $this->list_dump_files) && $this->config->type_backup == $this->type_backup_complete) {
                    $path_module_backup = 'modules/' . $this->name . '/' . self::BACKUP_FOLDER . '/';
                    $path_in_tar = str_replace($this->config_backup_dir, $path_module_backup, $file);
                    $filename = ltrim($path_in_tar, '/');
                } else {
                    // Normalize path
                    $current_normalized_file = $this->normalizePath($file);
                    $filename = ltrim(self::getPart($current_normalized_file, $this->base_length), '/');
                }

                $file_size = Tools::substr($line, $pos_cut + 1);

                if (!isset($this->tar_files_size[$this->part_number])) {
                    $this->tar_files_size[$this->part_number] = ($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0;
                    $this->total_size += ($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0;
                }

                $bloc_size = self::TAR_BLOCK_SIZE;

                // Start file size
                $start_size += $this->getHeaderFileSize($filename, false);

                // Content file size
                $content_size += floor(($file_size / $bloc_size) + (($file_size % $bloc_size > 0) ? 1 : 0)) * $bloc_size;

                if ($this->config->crypt_backup && $this->config->ignore_compression) {
                    $content_size += ($content_size / $bloc_size) * SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;
                }

                $total_file_tar = $start_size + $content_size + $end_size;

                // Check if future tar file size bigger than authorized
                if ($this->part_size > 0) {
                    // Tar file should not be bigger than part_size
                    if (($this->tar_files_size[$this->part_number] + $total_file_tar) > $this->part_size) {
                        if ($this->part_number == 1) {
                            // Change the name of the first (and only) file, currently listed
                            $this->part_list = [$this->part_file . '.' . $this->part_number . '.part.' . $this->ext_uncompress];
                        }

                        // End tar files
                        $this->tar_files_size[$this->part_number] += $end_size;
                        $this->total_size += $end_size;

                        // Log futur tar size
                        $this->log(sprintf($this->l('"%1$s" size', self::PAGE), basename($this->part_file . '.' . $this->part_number . '.part.' . $this->ext_uncompress)) . ' - ' . $this->readableSize($this->tar_files_size[$this->part_number]), true);

                        // The tar file will be too big, we need to have a new part
                        ++$this->part_number;

                        if (!isset($this->tar_files_size[$this->part_number])) {
                            $this->tar_files_size[$this->part_number] = ($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0;
                            $this->total_size += ($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0;
                        }

                        $this->part_list[] = $this->part_file . '.' . $this->part_number . '.part.' . $this->ext_uncompress;
                    }
                }

                $this->tar_files_size[$this->part_number] += $start_size + $content_size;
                $this->total_size += $start_size + $content_size;
            }

            $this->position_file_list_file = ftell($this->handle_file_list_file);

            // refresh
            $this->refreshBackup(true);
        }

        // End tar files
        $this->tar_files_size[$this->part_number] += $end_size;
        $this->total_size += $end_size;

        // Log futur tar size
        $this->log(sprintf($this->l('"%1$s" size', self::PAGE), basename($this->part_file . '.' . $this->part_number . '.part.' . $this->ext_uncompress)) . ' - ' . $this->readableSize($this->tar_files_size[$this->part_number]), true);
        $this->log($this->l('Total tar size', self::PAGE) . ' - ' . $this->readableSize($this->total_size), true);
        rewind($this->handle_file_list_file);
        $this->position_file_list_file = 0;

        return true;
    }

    /**
     * Delete local backup if backup is sent away
     *
     * @return bool Success or failure of the operation
     */
    protected function deleteLocalBackup()
    {
        if ($this->send_away_success && $this->config->delete_local_backup) {
            $list_active_ftp_accounts = FtpNtbr::getListActiveFtpAccounts($this->config->id);
            $list_active_dropbox_accounts = DropboxNtbr::getListActiveDropboxAccounts($this->config->id);
            $list_active_yandex_accounts = YandexNtbr::getListActiveYandexAccounts($this->config->id);
            $list_active_owncloud_accounts = OwncloudNtbr::getListActiveOwncloudAccounts($this->config->id);
            $list_active_shadow_drive_accounts = ShadowDriveNtbr::getListActiveShadowDriveAccounts($this->config->id);
            $list_active_webdav_accounts = WebdavNtbr::getListActiveWebdavAccounts($this->config->id);
            $list_active_googledrive_accounts = GoogledriveNtbr::getListActiveGoogledriveAccounts($this->config->id);
            $list_active_googlecloud_accounts = GooglecloudNtbr::getListActiveGooglecloudAccounts($this->config->id);
            $list_active_pcloud_accounts = PcloudNtbr::getListActivePcloudAccounts($this->config->id);
            $list_active_box_accounts = BoxNtbr::getListActiveBoxAccounts($this->config->id);
            $list_active_onedrive_accounts = OnedriveNtbr::getListActiveOnedriveAccounts($this->config->id);
            $list_active_aws_accounts = AwsNtbr::getListActiveAwsAccounts($this->config->id);
            $list_active_sugarsync_accounts = SugarsyncNtbr::getListActiveSugarsyncAccounts($this->config->id);

            if (count($list_active_ftp_accounts)
                || count($list_active_dropbox_accounts)
                || count($list_active_yandex_accounts)
                || count($list_active_owncloud_accounts)
                || count($list_active_shadow_drive_accounts)
                || count($list_active_webdav_accounts)
                || count($list_active_onedrive_accounts)
                || count($list_active_googledrive_accounts)
                || count($list_active_googlecloud_accounts)
                || count($list_active_pcloud_accounts)
                || count($list_active_box_accounts)
                || count($list_active_aws_accounts)
                || count($list_active_sugarsync_accounts)
            ) {
                foreach ($this->part_list as $part) {
                    if (Apparatus::checkFileExists($part)) {
                        $this->log($this->l('Delete local backup file:', self::PAGE) . ' ' . $part);

                        if (!$this->fileDelete($part)) {
                            $this->log($this->l('Delete local backup file failed:', self::PAGE) . ' ' . $part);

                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Initialize SFTP
     */
    public function initForSFTP()
    {
        if (!extension_loaded('openssl')) {
            return false;
        }

        $phpseclib_path = dirname(__FILE__) . '/../lib/phpseclib/vendor/';
        set_include_path(get_include_path() . PATH_SEPARATOR . $phpseclib_path);
        require_once $phpseclib_path . 'autoload.php';
    }

    /**
     * Connect to Dropbox
     *
     * @param bool $business Dropbox standard or business
     * @param string $access_token Dropbox token (optional)
     *
     * @return object new DropboxLib
     */
    public function connectToDropbox($business, $access_token = '')
    {
        $sdk_uri = $this->module_path . 'lib/dropbox/';
        $physic_sdk_uri = $this->module_path_physic . 'lib/dropbox/';

        if (!empty($access_token)) {
            $decode_token = json_decode($access_token, true);

            if (isset($decode_token['expires_in'])) {
                // If token expire in 30 minutes
                $expired = ($decode_token['created'] + ($decode_token['expires_in'] - 1800)) < time();

                if ($expired) {
                    $refresh_token = $this->decrypt($decode_token['refresh_token']);
                    $decode_token = $this->getDropboxRefreshToken($refresh_token, $business);

                    // Put back the refresh token (it not send back since it does not change
                    $decode_token['refresh_token'] = $refresh_token;

                    if (isset($this->dropbox_account_id) && $this->dropbox_account_id) {
                        $dec_tok = $decode_token;
                        $dec_tok['access_token'] = $this->encrypt($dec_tok['access_token']);
                        $dec_tok['refresh_token'] = $this->encrypt($dec_tok['refresh_token']);

                        $token = json_encode($dec_tok);

                        if ($token && $token != '') {
                            $dropbox = new DropboxNtbr($this->dropbox_account_id);
                            $dropbox->token = $token;

                            $dropbox->save();
                        }
                    }
                } else {
                    $decode_token['access_token'] = $this->decrypt($decode_token['access_token']);
                }
            } else {
                $decode_token['access_token'] = $this->decrypt($access_token);
            }

            return new DropboxLib($this, $sdk_uri, $physic_sdk_uri, $business, $decode_token['access_token']);
        } else {
            return new DropboxLib($this, $sdk_uri, $physic_sdk_uri, $business);
        }
    }

    /**
     * Connect to Yandex
     *
     * @param string $access_token Yandex token (optional)
     *
     * @return object new YandexLib
     */
    public function connectToYandex($access_token = '')
    {
        $sdk_uri = $this->module_path . 'lib/yandex/';
        $physic_sdk_uri = $this->module_path_physic . 'lib/yandex/';

        if (!empty($access_token)) {
            return new YandexLib($this, $sdk_uri, $physic_sdk_uri, $access_token);
        } else {
            return new YandexLib($this, $sdk_uri, $physic_sdk_uri);
        }
    }

    /**
     * Connect to ownCloud/Nextcloud
     *
     * @param string $server ownCloud server
     * @param string $user ownCloud user
     * @param string $pass ownCloud password
     *
     * @return object new OwncloudLib
     */
    public function connectToOwncloud($server, $user, $pass)
    {
        $sdk_uri = $this->module_path . 'lib/owncloud/';
        $physic_sdk_uri = $this->module_path_physic . 'lib/owncloud/';

        return new OwncloudLib($this, $server, $user, $pass, $sdk_uri, $physic_sdk_uri);
    }

    /**
     * Connect to Shadow Drive
     *
     * @param string $server Shadow Drive server
     * @param string $user Shadow Drive user
     * @param string $pass Shadow Drive password
     *
     * @return object new ShadowDriveLib
     */
    public function connectToShadowDrive($server, $user, $pass)
    {
        $sdk_uri = $this->module_path . 'lib/shadow_drive/';
        $physic_sdk_uri = $this->module_path_physic . 'lib/shadow_drive/';

        return new ShadowDriveLib($this, $server, $user, $pass, $sdk_uri, $physic_sdk_uri);
    }

    /**
     * Connect to WebDAV
     *
     * @param string $url WebDAV url
     * @param string $user WebDAV user
     * @param string $pass WebDAV password
     *
     * @return object new WebdavLib
     */
    public function connectToWebdav($url, $user, $pass)
    {
        $sdk_uri = $this->module_path . 'lib/webdav/';
        $physic_sdk_uri = $this->module_path_physic . 'lib/webdav/';

        return new WebdavLib($this, $url, $user, $pass, $sdk_uri, $physic_sdk_uri);
    }

    /**
     * Connect to AWS
     *
     * @param string $aws_id_key AWS ID key
     * @param string $aws_key AWS secret key
     * @param string $aws_region AWS region
     * @param string $aws_bucket AWS bucket
     * @param string $aws_host AWS host
     * @param string $aws_type_s3 AWS type_s3
     * @param string $aws_accept_unvalid_ssl AWS accept_unvalid_ssl
     *
     * @return object new AwsLib
     */
    public function connectToAws($aws_id_key, $aws_key, $aws_region, $aws_bucket, $aws_host, $aws_type_s3, $aws_accept_unvalid_ssl)
    {
        $sdk_uri = $this->module_path . 'lib/aws/';
        $physic_sdk_uri = $this->module_path_physic . 'lib/aws/';

        return new AwsLib($this, $aws_id_key, $aws_key, $aws_region, $aws_bucket, $aws_host, $sdk_uri, $physic_sdk_uri, $aws_type_s3, $aws_accept_unvalid_ssl);
    }

    /**
     * Connect to Openstack
     *
     * @param string $access_token Openstack token
     * @param string $end_point Openstack end point
     * @param string $account_type Openstack account type
     *
     * @return object new OpenstackLib
     */
    public function connectToOpenstack($access_token, $end_point, $account_type)
    {
        $sdk_uri = $this->module_path . 'lib/openstack/';
        $physic_sdk_uri = $this->module_path_physic . 'lib/openstack/';

        return new OpenstackLib($this, $sdk_uri, $physic_sdk_uri, $access_token, $end_point, $account_type);
    }

    /**
     * Connect to Google Drive
     *
     * @param string $access_token Google Drive token (optional)
     *
     * @return object new GoogledriveLib
     */
    public function connectToGoogledrive($access_token = '')
    {
        $access_right = GoogledriveLib::DRIVE;
        $sdk_uri = $this->module_path . 'lib/googledrive/';
        $physic_sdk_uri = $this->module_path_physic . 'lib/googledrive/';

        if (!empty($access_token)) {
            $decode_token = json_decode($access_token, true);

            // If token expire in 30 minutes
            $expired = ($decode_token['created'] + ($decode_token['expires_in'] - 1800)) < time();

            if ($expired) {
                $refresh_token = $this->decrypt($decode_token['refresh_token']);
                $decode_token = $this->getGoogledriveRefreshToken($refresh_token);
            } else {
                $decode_token['access_token'] = $this->decrypt($decode_token['access_token']);
            }

            if (!isset($decode_token['access_token'])) {
                return new GoogledriveLib($this, $access_right, $sdk_uri, $physic_sdk_uri);
            }

            return new GoogledriveLib($this, $access_right, $sdk_uri, $physic_sdk_uri, $decode_token['access_token']);
        } else {
            return new GoogledriveLib($this, $access_right, $sdk_uri, $physic_sdk_uri);
        }
    }

    /**
     * Connect to Google Cloud
     *
     * @param string $bucket Google Cloud bucket (optional)
     * @param string $access_token Google Cloud token (optional)
     *
     * @return object new GooglecloudLib
     */
    public function connectToGooglecloud($bucket = '', $access_token = '')
    {
        $sdk_uri = $this->module_path . 'lib/googlecloud/';
        $physic_sdk_uri = $this->module_path_physic . 'lib/googlecloud/';

        if (!empty($access_token)) {
            $decode_token = json_decode($access_token, true);

            // If token expire in 30 minutes
            $expired = ($decode_token['created'] + ($decode_token['expires_in'] - 1800)) < time();

            if ($expired) {
                $refresh_token = $this->decrypt($decode_token['refresh_token']);
                $decode_token = $this->getGooglecloudRefreshToken($refresh_token);
            } else {
                $decode_token['access_token'] = $this->decrypt($decode_token['access_token']);
            }

            if (!isset($decode_token['access_token'])) {
                return new GoogleCloudLib($this, $sdk_uri, $physic_sdk_uri, $bucket);
            }

            return new GoogleCloudLib($this, $sdk_uri, $physic_sdk_uri, $bucket, $decode_token['access_token']);
        } else {
            return new GoogleCloudLib($this, $sdk_uri, $physic_sdk_uri, $bucket);
        }
    }

    /**
     * Connect to pCloud
     *
     * @param int $location_id pCloud location ID
     * @param string $token pCloud token (optional)
     *
     * @return object new PcloudLib
     */
    public function connectToPcloud($location_id, $token = '')
    {
        $sdk_uri = $this->module_path . 'lib/pcloud/';
        $physic_sdk_uri = $this->module_path_physic . 'lib/pcloud/';

        if (!empty($token)) {
            $decode_token = $this->decrypt($token);

            return new PcloudLib($this, $sdk_uri, $physic_sdk_uri, $location_id, $decode_token);
        } else {
            return new PcloudLib($this, $sdk_uri, $physic_sdk_uri, $location_id);
        }
    }

    /**
     * Connect to Box
     *
     * @param string $access_token Box token (optional)
     *
     * @return object new BoxLib
     */
    public function connectToBox($access_token = '', $id_ntbr_box = 0)
    {
        $sdk_uri = $this->module_path . 'lib/box/';
        $physic_sdk_uri = $this->module_path_physic . 'lib/box/';

        if (!empty($access_token)) {
            $decode_token = json_decode($access_token, true);

            // If token expire in 30 minutes
            $expired = ($decode_token['created'] + ($decode_token['expires_in'] - 1800)) < time();

            if ($expired) {
                $refresh_token = $this->decrypt($decode_token['refresh_token']);
                $token = $this->getBoxRefreshToken($refresh_token);
                $decode_token = $token;

                $token['access_token'] = $this->encrypt($token['access_token']);
                $token['refresh_token'] = $this->encrypt($token['refresh_token']);

                if ($id_ntbr_box) {
                    $box = new BoxNtbr($id_ntbr_box);
                    $box->token = json_encode($token);
                    $box->update();
                }
            } else {
                $decode_token['access_token'] = $this->decrypt($decode_token['access_token']);
            }

            return new BoxLib($this, $sdk_uri, $physic_sdk_uri, $decode_token['access_token']);
        } else {
            return new BoxLib($this, $sdk_uri, $physic_sdk_uri);
        }
    }

    /**
     * Connect to OneDrive
     *
     * @param string $access_token OneDrive token (optional)
     * @param int $id_ntbr_onedrive ID OneDrive account (optional)
     *
     * @return object new OnedriveLib
     */
    public function connectToOnedrive($access_token = '', $id_ntbr_onedrive = 0)
    {
        $sdk_uri = $this->module_path . 'lib/onedrive/';
        $physic_sdk_uri = $this->module_path_physic . 'lib/onedrive/';
        $business = 0;
        $my_site = '';

        if ($id_ntbr_onedrive) {
            $onedrive = new OnedriveNtbr($id_ntbr_onedrive);
            $business = $onedrive->business;
            $my_site = $onedrive->my_site;
        }

        if (!empty($access_token)) {
            $decode_token = json_decode($access_token, true);

            // If token expire in 30 minutes
            $expired = ($decode_token['created'] + ($decode_token['expires_in'] - 1800)) < time();

            if ($expired) {
                $refresh_token = $this->decrypt($decode_token['refresh_token']);
                $access_token = $this->getOnedriveRefreshToken($refresh_token, $business);

                // If the token was not correctly found, try again
                if (!isset($access_token['created']) || !isset($access_token['expires_in']) || !isset($access_token['refresh_token'])) {
                    sleep(5);
                    $access_token = $this->getOnedriveRefreshToken($refresh_token, $business);
                }

                if (isset($access_token['created']) && isset($access_token['expires_in']) && isset($access_token['refresh_token'])) {
                    $access_token['access_token'] = $this->encrypt($access_token['access_token']);
                    $access_token['refresh_token'] = $this->encrypt($access_token['refresh_token']);

                    if ($id_ntbr_onedrive) {
                        $onedrive->token = json_encode($access_token);
                        $onedrive->update();
                    }

                    $decode_token['access_token'] = $this->decrypt($access_token['access_token']);
                }
            } else {
                $decode_token['access_token'] = $this->decrypt($decode_token['access_token']);
            }

            return new OnedriveLib($this, $sdk_uri, $physic_sdk_uri, $decode_token['access_token'], $business, $my_site);
        } else {
            return new OnedriveLib($this, $sdk_uri, $physic_sdk_uri, '', $business);
        }
    }

    /**
     * Connect to SugarSync
     *
     * @param string $access_token SugarSync token (optional)
     * @param int $id_ntbr_sugarsync ID SugarSync account (optional)
     *
     * @return object new SugarsyncLib
     */
    public function connectToSugarsync($access_token = '', $id_ntbr_sugarsync = 0)
    {
        $sdk_uri = $this->module_path . 'lib/sugarsync/';
        $physic_sdk_uri = $this->module_path_physic . 'lib/sugarsync/';

        if (!empty($access_token)) {
            $decode_token = json_decode($access_token, true);

            // If token expire in 30 minutes
            $expired = (strtotime($decode_token['expire_in']) - 1800) < time();

            if ($expired) {
                $refresh_token = $this->decrypt($decode_token['refresh_token']);
                $decode_token = $this->getSugarsyncAccessToken($refresh_token);

                // $this->log($refresh_token, true);

                if ($id_ntbr_sugarsync) {
                    $sugarsync = new SugarsyncNtbr($id_ntbr_sugarsync);
                    $sugarsync->token = json_encode($decode_token);
                    $sugarsync->update();
                }
            }

            $decode_token['access_token'] = $this->decrypt($decode_token['access_token']);

            return new SugarsyncLib(
                $this,
                $sdk_uri,
                $physic_sdk_uri,
                $decode_token['access_token'],
                $decode_token['user']
            );
        } else {
            return new SugarsyncLib($this, $sdk_uri, $physic_sdk_uri);
        }
    }

    /**
     * Test connection to Dropbox
     *
     * @param string $token Dropbox token
     * @param string $business Dropbox standard or business
     *
     * @return bool The success or failure of the connection
     */
    public function testDropboxConnection($token, $business)
    {
        $dropbox_lib = $this->connectToDropbox($business, $token);

        return (bool) $dropbox_lib->testConnection();
    }

    /**
     * Test connection to Yandex
     *
     * @param string $token Yandex token
     *
     * @return bool The success or failure of the connection
     */
    public function testYandexConnection($token)
    {
        $yandex_lib = $this->connectToYandex($token);

        return (bool) $yandex_lib->testConnection();
    }

    /**
     * Test connection to ownCloud/Nextcloud
     *
     * @param string $server ownCloud server
     * @param string $user ownCloud user
     * @param string $pass ownCloud password
     *
     * @return bool The success or failure of the connection
     */
    public function testOwncloudConnection($server, $user, $pass)
    {
        $owncloud_lib = $this->connectToOwncloud($server, $user, $pass);

        return (bool) $owncloud_lib->testConnection();
    }

    /**
     * Test connection to Shadow Drive
     *
     * @param string $server Shadow Drive server
     * @param string $user Shadow Drive user
     * @param string $pass Shadow Drive password
     *
     * @return bool The success or failure of the connection
     */
    public function testShadowDriveConnection($server, $user, $pass)
    {
        $shadow_drive_lib = $this->connectToShadowDrive($server, $user, $pass);

        return (bool) $shadow_drive_lib->testConnection();
    }

    /**
     * Test connection to WebDAV
     *
     * @param string $url WebDAV url
     * @param string $user WebDAV user
     * @param string $pass WebDAV password
     *
     * @return bool The success or failure of the connection
     */
    public function testWebdavConnection($url, $user, $pass)
    {
        $webdav_lib = $this->connectToWebdav($url, $user, $pass);

        return (bool) $webdav_lib->testConnection();
    }

    /**
     * Test connection to Google Drive
     *
     * @param string $token Google Drive token
     *
     * @return bool The success or failure of the connection
     */
    public function testGoogledriveConnection($token)
    {
        $googledrive_lib = $this->connectToGoogledrive($token);

        return (bool) $googledrive_lib->testConnection();
    }

    /**
     * Test connection to Google Cloud
     *
     * @param string $bucket Google Cloud bucket
     * @param string $token Google Cloud token
     *
     * @return bool The success or failure of the connection
     */
    public function testGooglecloudConnection($bucket, $token)
    {
        $googlecloud_lib = $this->connectToGooglecloud($bucket, $token);

        return (bool) $googlecloud_lib->testConnection();
    }

    /**
     * Test connection to pCloud
     *
     * @param int $location_id pCloud location ID
     * @param string $token pCloud token
     *
     * @return bool The success or failure of the connection
     */
    public function testPcloudConnection($location_id, $token)
    {
        $pcloud_lib = $this->connectToPcloud($location_id, $token);

        return (bool) $pcloud_lib->testConnection();
    }

    /**
     * Test connection to Box
     *
     * @param string $token Box token
     *
     * @return bool The success or failure of the connection
     */
    public function testBoxConnection($token, $id_ntbr_box)
    {
        $box_lib = $this->connectToBox($token, $id_ntbr_box);

        return (bool) $box_lib->testConnection();
    }

    /**
     * Test connection to OneDrive
     *
     * @param string $token OneDrive token
     * @param int $id_ntbr_onedrive ID OneDrive account
     *
     * @return bool The success or failure of the connection
     */
    public function testOnedriveConnection($token, $id_ntbr_onedrive)
    {
        $onedrive_lib = $this->connectToOnedrive($token, $id_ntbr_onedrive);

        return (bool) $onedrive_lib->testConnection();
    }

    /**
     * Test connection to AWS
     *
     * @param string $aws_id_key AWS ID key
     * @param string $aws_key AWS secret key
     * @param string $aws_region AWS region
     * @param string $aws_bucket AWS bucket
     * @param string $aws_host AWS host
     * @param string $aws_type_s3 AWS type_s3
     * @param string $aws_accept_unvalid_ssl AWS accept_unvalid_ssl
     *
     * @return bool The success or failure of the connection
     */
    public function testAwsConnection($aws_id_key, $aws_key, $aws_region, $aws_bucket, $aws_host, $aws_type_s3, $aws_accept_unvalid_ssl)
    {
        $aws_lib = $this->connectToAws($aws_id_key, $aws_key, $aws_region, $aws_bucket, $aws_host, $aws_type_s3, $aws_accept_unvalid_ssl);

        return (bool) $aws_lib->testConnection();
    }

    /**
     * Test connection to FTP
     *
     * @param string $ftp_server FTP server
     * @param string $ftp_login FTP login
     * @param string $ftp_pass FTP password
     * @param int $ftp_port FTP port
     * @param bool $ssl FTP ssl (enable or disable)
     * @param bool $pasv FTP passive mode (enable or disable)
     *
     * @return bool The success or failure of the connection
     */
    public function testFTP($ftp_server, $ftp_login, $ftp_pass, $ftp_port, $ssl = false, $pasv = false)
    {
        if ($ssl) {
            // Beware of the warning from php if failure
            $connection = @ftp_ssl_connect($ftp_server, (int) $ftp_port, self::FTP_TIMEOUT);
        } else {
            // Beware of the warning from php if failure
            $connection = @ftp_connect($ftp_server, (int) $ftp_port, self::FTP_TIMEOUT);
        }

        if (!$connection) {
            $this->log(
                sprintf(
                    $this->l('Impossible to connect to the %s. Please check your credential.', self::PAGE),
                    self::FTP
                )
            );

            return false;
        }

        // Beware of the warning from php if failure
        $return = @ftp_login($connection, $ftp_login, $ftp_pass);

        if ($return) {
            $return = ftp_pasv($connection, $pasv);
        }

        ftp_close($connection);

        if (!$return) {
            $this->log(
                sprintf(
                    $this->l('Impossible to connect to the %s. Please check your credential.', self::PAGE),
                    self::FTP
                )
            );
        }

        return $return;
    }

    /**
     * Test connection to SFTP
     *
     * @param string $ftp_server SFTP server
     * @param string $ftp_login SFTP login
     * @param string $ftp_pass SFTP password
     * @param int $ftp_port SFTP port
     *
     * @return bool The success or failure of the connection
     */
    public function testSFTP($ftp_server, $ftp_login, $ftp_pass, $ftp_port)
    {
        $return = true;

        $this->initForSFTP();

        $sftp_lib = new phpseclib\Net\SFTP($ftp_server, $ftp_port);

        // Beware of the warning from php if failure
        if (!@$sftp_lib->login($ftp_login, $ftp_pass)) {
            $return = false;
        }

        // The closing of the ftp can sometime bug so we can't test it
        if (!$this->closeSFTP($sftp_lib)) {
            // $return = false;
        }

        return $return;
    }

    /**
     * Test connection to SugarSync
     *
     * @param string $token SugarSync token
     * @param int $id_ntbr_sugarsync ID SugarSync account
     *
     * @return bool The success or failure of the connection
     */
    public function testSugarsyncConnection($token, $id_ntbr_sugarsync)
    {
        $sugarsync_lib = $this->connectToSugarsync($token, $id_ntbr_sugarsync);

        return (bool) $sugarsync_lib->testConnection();
    }

    /**
     * Send a file on a Dropbox account
     *
     * @return bool The success or failure of the operation
     */
    protected function sendFileToDropbox()
    {
        $dropbox = new DropboxNtbr($this->dropbox_account_id);

        if ($this->next_step == $this->step_send['dropbox']
            && (!isset($this->dropbox_nb_part)
            || $this->dropbox_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::DROPBOX));
        }

        $dropbox_lib = $this->connectToDropbox($dropbox->business, $dropbox->token);

        if (!$dropbox_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::DROPBOX)
            );

            return false;
        }

        if ($this->next_step == $this->step_send['dropbox']
            && (!isset($this->dropbox_nb_part)
            || $this->dropbox_nb_part == 1)
        ) {
            $this->dropbox_dir = $dropbox->directory;

            // Dropbox dir should start with a "/" except for root
            if ($this->dropbox_dir != '' && $this->dropbox_dir[0] !== '/') {
                $this->dropbox_dir = '/' . $this->dropbox_dir;
            } elseif ($this->dropbox_dir == '/') {
                $this->dropbox_dir = '';
            }

            $temp_directory = $this->dropbox_dir;

            // Dropbox dir should end with a "/" except when testing if exist
            if ($this->dropbox_dir != '' && Tools::substr($this->dropbox_dir, -1) != '/') {
                $temp_directory .= '/';
            }

            // If file already on Dropbox
            foreach ($this->part_list as $part) {
                $file_path = $part;
                $file_destination = $temp_directory . basename($part);

                $this->log(
                    $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                );

                if ($dropbox_lib->checkExists($file_destination)) {
                    $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);

                    if ($dropbox_lib->deleteFile($file_destination) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                    }
                }
            }

            // Get old backups list
            $old_backups = $this->getDropboxFiles($dropbox_lib, $temp_directory);
            $nb_backup_to_keep = $dropbox->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            // Get available size
            $available_space = $dropbox_lib->getAvailableQuota() + $size_old_backups;

            // Get size to upload
            $to_send_size = $this->total_size;

            if ($this->config->send_restore) {
                $total_file_size = $this->getFileSize($this->restore_file);
                $to_send_size += $total_file_size;
            }

            // Check if we will have enough size after deleting old backup, too add new backup
            if ($available_space < $to_send_size) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::DROPBOX) . ' '
                    . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                );

                return false;
            }

            // Delete old backup
            if (!$this->deleteDropboxOldBackup($dropbox->token, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::DROPBOX) . ' '
                    . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            // Check available space
            $new_available_space = $dropbox_lib->getAvailableQuota();

            if ($this->config->send_restore) {
                $total_file_size = $this->getFileSize($this->restore_file);
                $new_available_space -= $total_file_size;
            }

            if ($new_available_space <= $this->total_size) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::DROPBOX) . ' '
                    . $this->l('Not enough space available', self::PAGE)
                );

                return false;
            }

            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::DROPBOX) . ' '
                . $this->l('Check the path of your backup', self::PAGE)
            );

            if ($this->dropbox_dir != '') {
                // Check if the folder we want to use exists. If not we create it.
                if ($dropbox_lib->checkExists($this->dropbox_dir) === false) {
                    $this->log(
                        sprintf($this->l('Sending backup to %s account:', self::PAGE), self::DROPBOX)
                        . ' ' . $this->l('Create the directory', self::PAGE) . ' "' . $this->dropbox_dir . '"'
                    );

                    if ($dropbox_lib->createFolder($this->dropbox_dir) === false) {
                        $this->log(
                            'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::DROPBOX)
                            . ' ' . $this->l('Error while creating the directory', self::PAGE) . ' "' . $this->dropbox_dir . '"'
                        );

                        return false;
                    }
                }
            }

            // Dropbox dir should end with a "/" except when testing if exist
            $this->dropbox_dir = $temp_directory;

            $this->log(sprintf($this->l('Sending backup to %s account...', self::PAGE), self::DROPBOX));

            $this->dropbox_nb_part = 1;
            $this->dropbox_position = 0;
        }

        $nb_part = 1;

        foreach ($this->part_list as $part) {
            if ($nb_part == $this->dropbox_nb_part) {
                $file_path = $part;
                $file_destination = $this->dropbox_dir . basename($part);

                // Upload the file
                if ($this->next_step == $this->step_send['dropbox']) {
                    $this->next_step = $this->step_send['dropbox_resume'];

                    $upload_file = $dropbox_lib->uploadFile(
                        $file_path,
                        $file_destination,
                        $this->dropbox_nb_part,
                        $this->total_nb_part
                    );

                    if ($upload_file === false) {
                        return false;
                    }
                } else { // Resume the upload
                    $resume_upload_file = $dropbox_lib->resumeUploadFile(
                        $file_path,
                        $file_destination,
                        $this->dropbox_upload_id,
                        $this->dropbox_position,
                        $this->dropbox_nb_part,
                        $this->total_nb_part
                    );

                    if ($resume_upload_file === false) {
                        return false;
                    }
                }

                ++$this->dropbox_nb_part;
                // New part, so back to init values
                $this->next_step = $this->step_send['dropbox'];
                $this->dropbox_position = 0;
            }
            ++$nb_part;
        }

        $this->next_step = $this->step_send['dropbox_resume'];

        if ($this->config->send_restore) {
            $this->log(
                sprintf($this->l('Sending restore file to %s account...', self::PAGE), self::DROPBOX)
            );

            // Upload the file
            if ($dropbox_lib->uploadFile($this->restore_file, $this->dropbox_dir . self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a tar file on a Dropbox account
     *
     * @return bool The success or failure of the operation
     */
    protected function createTarOnDropbox()
    {
        $dropbox = new DropboxNtbr($this->dropbox_account_id);
        $tar_end_size = self::TAR_END_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? (SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES * 2) : 0);

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['dropbox']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->dropbox_nb_part) || $this->dropbox_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::DROPBOX));

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
            $this->files_done = 0;
            $this->position_file_list_file = 0;
            $this->part_number = 1;
            $this->old_percent = 0;
            $tar_name = basename($this->part_list[$this->part_number - 1]);

            if (count($this->part_list) != count($this->tar_files_size)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX) . ' '
                    . $this->l('The backup cannot be created', self::PAGE)
                );

                return false;
            }

            if (!isset($this->tar_files_size[$this->part_number]) || !$this->tar_files_size[$this->part_number]) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX) . ' '
                    . sprintf($this->l('The calculated size of the file %s is not valid', self::PAGE), $tar_name)
                );

                return false;
            }
        }

        $this->checkStopScript();

        $dropbox_lib = $this->connectToDropbox($dropbox->business, $dropbox->token);

        if (!$dropbox_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::DROPBOX)
            );

            return false;
        }

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['dropbox']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->dropbox_nb_part) || $this->dropbox_nb_part == 1)
        ) {
            $this->dropbox_dir = $dropbox->directory;

            // Dropbox dir should start with a "/" except for root
            if ($this->dropbox_dir != '' && $this->dropbox_dir[0] !== '/') {
                $this->dropbox_dir = '/' . $this->dropbox_dir;
            }

            $temp_directory = $this->dropbox_dir;

            // Dropbox dir should end with a "/" except when testing if exist
            if (Tools::substr($this->dropbox_dir, -1) != '/') {
                $temp_directory .= '/';
            }

            // Get old backups list
            $old_backups = $this->getDropboxFiles($dropbox_lib, $temp_directory);
            $nb_backup_to_keep = $dropbox->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            $this->checkStopScript();

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    $this->checkStopScript();

                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            $this->checkStopScript();

            // Get available size
            $available_space = $dropbox_lib->getAvailableQuota() + $size_old_backups;

            $this->checkStopScript();

            // Get size to upload
            $to_send_size = $this->total_size;

            if ($this->config->send_restore) {
                $total_file_size = $this->getFileSize($this->restore_file);

                $this->checkStopScript();

                $to_send_size += $total_file_size;
            }

            // Check if we will have enough size after deleting old backup, too add new backup
            if ($available_space < $to_send_size) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX) . ' '
                    . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                );

                return false;
            }

            $this->checkStopScript();

            // Delete old backup
            if (!$this->deleteDropboxOldBackup($dropbox->token, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX) . ' '
                    . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            $this->checkStopScript();

            // Check available space
            $new_available_space = $dropbox_lib->getAvailableQuota();

            $this->checkStopScript();

            if ($new_available_space < $to_send_size) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX) . ' '
                    . $this->l('Not enough space available', self::PAGE)
                );

                return false;
            }

            $this->log(
                sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX) . ' '
                . $this->l('Check the path of your backup', self::PAGE)
            );

            $this->checkStopScript();

            if ($this->dropbox_dir != '') {
                // Check if the folder we want to use exists. If not we create it.
                if ($dropbox_lib->checkExists($this->dropbox_dir) === false) {
                    $this->log(
                        sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX)
                        . ' ' . $this->l('Create the directory', self::PAGE) . ' "' . $this->dropbox_dir . '"'
                    );

                    $this->checkStopScript();

                    if ($dropbox_lib->createFolder($this->dropbox_dir) === false) {
                        $this->log(
                            'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX)
                            . ' ' . $this->l('Error while creating the directory', self::PAGE) . ' "' . $this->dropbox_dir . '"'
                        );

                        return false;
                    }
                }
            }

            $this->checkStopScript();

            // Dropbox dir should end with a "/" except when testing if exist
            $this->dropbox_dir = $temp_directory;

            $this->log(sprintf($this->l('Creating backup on %s account...', self::PAGE), self::DROPBOX));

            $this->dropbox_nb_part = 1;
            $this->dropbox_position = 0;
            $this->distant_tar_content = '';
            $this->distant_tar_content_size = 0;

            $this->distant_tar_content = $this->createTarStart();
            $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
        }

        $this->checkStopScript();

        if ($this->getFileSize($this->file_list_file) <= 0) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX)
                . ' ' . $this->l('No file to backup', self::PAGE)
            );

            return false;
        }

        $this->checkStopScript();

        if (!is_resource($this->handle_file_list_file)) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX)
                . ' ' . $this->l('Error while getting the files to backup', self::PAGE)
            );

            return false;
        }

        $this->handle_file_list_file = $this->goToPositionInFile(
            $this->handle_file_list_file,
            $this->position_file_list_file,
            false
        );

        $this->checkStopScript();

        while (!feof($this->handle_file_list_file)) {
            $this->checkStopScript();

            $line = rtrim(fgets($this->handle_file_list_file));

            if (!$line) {
                continue;
            }

            $pos_cut = strrpos($line, ':');

            if ($pos_cut === false) {
                continue;
            }

            $current_file = self::binaryToString(Tools::substr($line, 0, $pos_cut));

            if (!$current_file) {
                continue;
            }

            $this->checkStopScript();

            $found_size = Tools::substr($line, $pos_cut + 1);
            $tar_name = basename($this->part_list[$this->part_number - 1]);
            $path_in_tar = '';

            // Normalize path
            $current_normalized_file = $this->normalizePath($current_file);

            // Find relative filename
            if (!$path_in_tar) {
                $filename = ltrim(self::getPart($current_normalized_file, $this->base_length), '/');
            } else {
                $filename = ltrim($path_in_tar, '/');
            }

            // File information
            $info = $this->tarFileInfo($current_file);
            $diff_size = 0;

            if ($info['size'] != $found_size) {
                if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX)
                        . ' ' . sprintf(
                            $this->l('The file %1$s has changed of size from %2$s to %3$s', self::PAGE),
                            $current_file,
                            $this->readableSize($found_size) . ' (' . $found_size . ')',
                            $this->readableSize($info['size']) . ' (' . $info['size'] . ')'
                        )
                    );
                }

                $diff_size = $info['size'] - $found_size;
                $info['size'] = $found_size;
            }

            if (in_array($current_file, $this->list_dump_files) && $this->config->type_backup == $this->type_backup_complete) {
                $path_module_backup = 'modules/' . $this->name . '/' . self::BACKUP_FOLDER . '/';
                $path_in_tar = str_replace($this->config_backup_dir, $path_module_backup, $current_file);
            }

            $this->checkStopScript();

            // Create file header
            if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                // Start to add a new file to the tar
                $header = $this->createTarHeader($filename, $info);

                $this->distant_tar_content_size += $this->getHeaderFileSize($filename, false);
                $this->distant_tar_content .= $header;

                ++$this->files_done;

                $this->pos_file_to_tar = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE_CONTINUE;
            }

            if ($info['size'] > 0) {
                // Open the file
                if (($file_read = @fopen($current_file, 'rb')) === false) {
                    // If it is the first opening of the file
                    if ($this->pos_file_to_tar == 0) {
                        $this->log(
                            'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX)
                            . ' ' . $this->l('File', self::PAGE) . ' ' . $current_file . ' '
                            . $this->l('will be empty because the module can not open it, please check its rights and user owner', self::PAGE)
                        );
                    }
                }

                $this->checkStopScript();

                if (is_resource($file_read) && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE_CONTINUE) {
                    $file_read = $this->goToPositionInFile($file_read, $this->pos_file_to_tar);

                    if ($file_read === false) {
                        return false;
                    }
                }

                // Data of the file
                $leftsize = $info['size'] - $this->pos_file_to_tar;
                $blocksize = self::TAR_BLOCK_SIZE;

                while ($leftsize > 0) {
                    $this->checkStopScript();

                    if ($this->next_step == $this->step_send['dropbox']) {
                        // Create new resumable session
                        $create_session = $dropbox_lib->createSession();

                        if ($create_session === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be created', self::PAGE)
                            );

                            return false;
                        }

                        $this->dropbox_position = 0;
                        $this->next_step = $this->step_send['dropbox_resume'];
                    }

                    // If max size for Dropbox has been reach, we send the content or the size of the tar was reach
                    if ($this->distant_tar_content_size >= DropboxLib::MAX_FILE_UPLOAD_SIZE
                        || ($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                    ) {
                        $end_of_current_part = 0;

                        if (($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]) {
                            $this->distant_tar_content_size += $tar_end_size;
                            $this->distant_tar_content .= $this->createTarEnd();
                            $end_of_current_part = 1;
                        }

                        if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                            $this->log(
                                $this->l('The total size should not be less that the size we are now at', self::PAGE)
                                . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                            );
                        }

                        // Send content using the current session
                        $resume_upload_content = $dropbox_lib->resumeUploadContent(
                            $this->distant_tar_content,
                            $this->distant_tar_content_size,
                            $this->dropbox_dir . $tar_name,
                            $this->tar_files_size[$this->part_number],
                            $this->dropbox_upload_id,
                            $this->dropbox_position
                        );

                        if ($resume_upload_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be completed', self::PAGE)
                            );

                            return false;
                        } else {
                            $this->dropbox_position += $this->distant_tar_content_size;

                            $this->distant_tar_content = '';
                            $this->distant_tar_content_size = 0;
                        }

                        if ($end_of_current_part) {
                            // New part, so back to init values
                            ++$this->dropbox_nb_part;
                            ++$this->part_number;

                            $this->next_step = $this->step_send['dropbox'];
                            $this->dropbox_position = 0;
                            $this->old_percent = 0;
                            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;

                            $this->distant_tar_content = $this->createTarStart();
                            $this->distant_tar_content_size += (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
                        }
                    } else {
                        // Read data
                        $leftsize -= $blocksize;

                        if ($leftsize < 0) {
                            $blocksize += $leftsize;
                        }

                        $temp_content = '';

                        if (is_resource($file_read)) {
                            $temp_content = $this->createTarContent($file_read, $blocksize);
                        }

                        // Get where we are in the file
                        $this->pos_file_to_tar += $blocksize;

                        // If there is no content because the file size has changed (smaller file) we need to add \0
                        if ($diff_size < 0 && !$temp_content) {
                            // self::TAR_BLOCK_SIZE since createTarContent
                            // return self::TAR_BLOCK_SIZE size block (using self::pad)
                            $temp_content = self::pad('', self::TAR_BLOCK_SIZE);
                            $diff_size += self::TAR_BLOCK_SIZE;

                            if ($this->config->crypt_backup && $this->config->ignore_compression) {
                                $temp_content = $this->cryptBackup($temp_content);
                                $diff_size += SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;
                            }
                        } elseif ($temp_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX)
                                . ' ' . $this->l('The module was unable to read the file', self::PAGE)
                                . ' ' . $current_file . ', ' . $this->l('please check its rights and user owner', self::PAGE)
                            );

                            if (is_resource($file_read)) {
                                fclose($file_read);
                            }

                            return false;
                        }

                        $this->distant_tar_content .= $temp_content;
                        $this->distant_tar_content_size += self::TAR_BLOCK_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES : 0);

                        $percent = (($this->distant_tar_content_size + $this->dropbox_position) / $this->tar_files_size[$this->part_number]) * 100;

                        if ($percent >= $this->old_percent + 1) {
                            $this->old_percent = round($percent, 0);

                            if ($this->total_nb_part > 1) {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX)
                                    . ' ' . $this->part_number . '/' . $this->total_nb_part . $this->l(':', self::PAGE)
                                    . ' ' . (int) $percent . '%'
                                );
                            } else {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX)
                                    . ' ' . (int) $percent . '%'
                                );
                            }
                        }
                    }

                    // refresh
                    $this->refreshBackup(true);
                }

                // Close file
                if (is_resource($file_read)) {
                    fclose($file_read);
                }
            }

            /*if (basename($filename) == self::TEMP_EMPTY_FILE && file_exists($current_file)) {
                @unlink($current_file);
            }*/

            $this->position_file_list_file = ftell($this->handle_file_list_file);

            $this->checkStopScript();

            // Check if tar file size has reach its predicted size
            if (isset($this->tar_files_size[$this->part_number])
                && ($this->dropbox_position + $this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                && $this->dropbox_position < $this->tar_files_size[$this->part_number]
            ) {
                // The tar file will be too big, we need to close it and use a new one
                $this->distant_tar_content .= $this->createTarEnd();
                $this->distant_tar_content_size += $tar_end_size;

                if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                    $this->log(
                        $this->l('The total size should not be less that the size we are now at', self::PAGE)
                        . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                    );
                }

                // Finish sending current tar
                $resume_upload_content = $dropbox_lib->resumeUploadContent(
                    $this->distant_tar_content,
                    $this->distant_tar_content_size,
                    $this->dropbox_dir . $tar_name,
                    $this->tar_files_size[$this->part_number],
                    $this->dropbox_upload_id,
                    $this->dropbox_position
                );

                if ($resume_upload_content === false) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX)
                        . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                        . ' ' . $this->l('cannot be finished', self::PAGE)
                    );

                    return false;
                }

                // New part, so back to init values
                ++$this->dropbox_nb_part;
                ++$this->part_number;

                $this->next_step = $this->step_send['dropbox'];
                $this->dropbox_position = 0;
                $this->old_percent = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                $this->distant_tar_content = '';
                $this->distant_tar_content_size = 0;

                $this->distant_tar_content = $this->createTarStart();
                $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);

                // refresh
                $this->refreshBackup(true);
            }

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
        }

        // Check that there is no missing file
        if ($this->files_done < $this->total_files) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::DROPBOX)
                . $this->l('Be careful! Not all of your files have been added to the backup. Number of files backuped:', self::PAGE)
                . ' ' . $this->files_done . '/' . $this->total_files
            );

            return false;
        }

        $this->files_done = 0;
        $this->pos_file_to_tar = 0;

        $this->next_step = $this->step_send['dropbox_resume'];

        if ($this->config->send_restore) {
            $this->log(
                sprintf($this->l('Creating restore file on %s account...', self::PAGE), self::DROPBOX)
            );

            // Upload the file
            if ($dropbox_lib->uploadFile($this->restore_file, $this->dropbox_dir . self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Send a file on a Yandex account
     *
     * @return bool The success or failure of the operation
     */
    protected function sendFileToYandex()
    {
        $yandex = new YandexNtbr($this->yandex_account_id);
        $access_token = $this->decrypt($yandex->token);

        if ($this->next_step == $this->step_send['yandex']
            && (!isset($this->yandex_nb_part)
            || $this->yandex_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::YANDEX));
        }

        $yandex_lib = $this->connectToYandex($access_token);

        if (!$yandex_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::YANDEX)
            );

            return false;
        }

        if ($this->next_step == $this->step_send['yandex']
            && (!isset($this->yandex_nb_part)
            || $this->yandex_nb_part == 1)
        ) {
            $this->yandex_dir = $yandex->directory;

            // Yandex dir should start with a "/" except for root
            if ($this->yandex_dir != '' && $this->yandex_dir[0] !== '/') {
                $this->yandex_dir = '/' . $this->yandex_dir;
            }

            $temp_directory = $this->yandex_dir;

            // Yandex dir should end with a "/" except when testing if exist
            if (Tools::substr($this->yandex_dir, -1) != '/') {
                $temp_directory .= '/';
            }

            // If file already on Yandex
            foreach ($this->part_list as $part) {
                $file_path = $part;
                $file_destination = $temp_directory . basename($part);

                $this->log(
                    $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                );

                if ($yandex_lib->checkExists($file_destination)) {
                    $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);

                    if ($yandex_lib->deleteFile($file_destination) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                    }
                }
            }

            // Get old backups list
            $old_backups = $this->getYandexFiles($yandex_lib, $temp_directory);
            $nb_backup_to_keep = $yandex->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            // Get available size
            $available_space = $yandex_lib->getAvailableQuota() + $size_old_backups;

            // Get size to upload
            $to_send_size = $this->total_size;

            if ($this->config->send_restore) {
                $total_file_size = $this->getFileSize($this->restore_file);
                $to_send_size += $total_file_size;
            }

            // Check if we will have enough size after deleting old backup, too add new backup
            if ($available_space < $to_send_size) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::YANDEX) . ' '
                    . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                );

                return false;
            }

            // Delete old backup
            if (!$this->deleteYandexOldBackup($access_token, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::YANDEX) . ' '
                    . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            // Check available space
            $new_available_space = $yandex_lib->getAvailableQuota();

            if ($this->config->send_restore) {
                $total_file_size = $this->getFileSize($this->restore_file);
                $new_available_space -= $total_file_size;
            }

            if ($new_available_space <= $this->total_size) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::YANDEX) . ' '
                    . $this->l('Not enough space available', self::PAGE)
                );

                return false;
            }

            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::YANDEX) . ' '
                . $this->l('Check the path of your backup', self::PAGE)
            );

            if ($this->yandex_dir != '/') {
                // Check if the folder we want to use exists. If not we create it.
                if ($yandex_lib->checkExists($this->yandex_dir) === false) {
                    $this->log(
                        sprintf($this->l('Sending backup to %s account:', self::PAGE), self::YANDEX)
                        . ' ' . $this->l('Create the directory', self::PAGE) . ' "' . $this->yandex_dir . '"'
                    );

                    if ($yandex_lib->createFolder($this->yandex_dir) === false) {
                        $this->log(
                            'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::YANDEX)
                            . ' ' . $this->l('Error while creating the directory', self::PAGE) . ' "' . $this->yandex_dir . '"'
                        );

                        return false;
                    }
                }
            }

            // Yandex dir should end with a "/" except when testing if exist
            $this->yandex_dir = $temp_directory;

            $this->log(sprintf($this->l('Sending backup to %s account...', self::PAGE), self::YANDEX));

            $this->yandex_nb_part = 1;
            $this->yandex_position = 0;
        }

        $nb_part = 1;

        foreach ($this->part_list as $part) {
            if ($nb_part == $this->yandex_nb_part) {
                $file_path = $part;
                $file_destination = $this->yandex_dir . basename($part);

                // Upload the file
                if ($this->next_step == $this->step_send['yandex']) {
                    $this->next_step = $this->step_send['yandex_resume'];

                    $upload_file = $yandex_lib->uploadFile(
                        $file_path,
                        $file_destination,
                        $this->yandex_nb_part,
                        $this->total_nb_part
                    );

                    if ($upload_file === false) {
                        return false;
                    }
                } else { // Resume the upload
                    $resume_upload_file = $yandex_lib->resumeUploadFile(
                        $file_path,
                        $this->yandex_nb_part,
                        $this->total_nb_part
                    );

                    if ($resume_upload_file === false) {
                        return false;
                    }
                }

                ++$this->yandex_nb_part;
                // New part, so back to init values
                $this->next_step = $this->step_send['yandex'];
                $this->yandex_position = 0;
            }
            ++$nb_part;
        }

        $this->next_step = $this->step_send['yandex_resume'];

        if ($this->config->send_restore) {
            $this->log(
                sprintf($this->l('Sending restore file to %s account...', self::PAGE), self::YANDEX)
            );

            // Upload the file
            if ($yandex_lib->uploadFile($this->restore_file, $this->yandex_dir . self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a tar file on a Yandex account
     *
     * @return bool The success or failure of the operation
     */
    protected function createTarOnYandex()
    {
        $yandex = new YandexNtbr($this->yandex_account_id);
        $access_token = $this->decrypt($yandex->token);
        $tar_end_size = self::TAR_END_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? (SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES * 2) : 0);

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['yandex']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->yandex_nb_part) || $this->yandex_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::YANDEX));

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
            $this->files_done = 0;
            $this->position_file_list_file = 0;
            $this->part_number = 1;
            $this->old_percent = 0;
            $tar_name = basename($this->part_list[$this->part_number - 1]);

            if (count($this->part_list) != count($this->tar_files_size)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX) . ' '
                    . $this->l('The backup cannot be created', self::PAGE)
                );

                return false;
            }

            if (!isset($this->tar_files_size[$this->part_number]) || !$this->tar_files_size[$this->part_number]) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX) . ' '
                    . sprintf($this->l('The calculated size of the file %s is not valid', self::PAGE), $tar_name)
                );

                return false;
            }
        }

        $this->checkStopScript();

        $yandex_lib = $this->connectToYandex($access_token);

        if (!$yandex_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::YANDEX)
            );

            return false;
        }

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['yandex']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->yandex_nb_part) || $this->yandex_nb_part == 1)
        ) {
            $this->yandex_dir = $yandex->directory;

            // Yandex dir should start with a "/" except for root
            if ($this->yandex_dir != '' && $this->yandex_dir[0] !== '/') {
                $this->yandex_dir = '/' . $this->yandex_dir;
            }

            $temp_directory = $this->yandex_dir;

            // Yandex dir should end with a "/" except when testing if exist
            if (Tools::substr($this->yandex_dir, -1) != '/') {
                $temp_directory .= '/';
            }

            // Get old backups list
            $old_backups = $this->getYandexFiles($yandex_lib, $temp_directory);
            $nb_backup_to_keep = $yandex->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            $this->checkStopScript();

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    $this->checkStopScript();

                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            $this->checkStopScript();

            // Get available size
            $available_space = $yandex_lib->getAvailableQuota() + $size_old_backups;

            $this->checkStopScript();

            // Get size to upload
            $to_send_size = $this->total_size;

            if ($this->config->send_restore) {
                $total_file_size = $this->getFileSize($this->restore_file);

                $this->checkStopScript();

                $to_send_size += $total_file_size;
            }

            // Check if we will have enough size after deleting old backup, too add new backup
            if ($available_space < $to_send_size) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX) . ' '
                    . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                );

                return false;
            }

            $this->checkStopScript();

            // Delete old backup
            if (!$this->deleteYandexOldBackup($access_token, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX) . ' '
                    . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            $this->checkStopScript();

            // Check available space
            $new_available_space = $yandex_lib->getAvailableQuota();

            $this->checkStopScript();

            if ($new_available_space < $to_send_size) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX) . ' '
                    . $this->l('Not enough space available', self::PAGE)
                );

                return false;
            }

            $this->log(
                sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX) . ' '
                . $this->l('Check the path of your backup', self::PAGE)
            );

            $this->checkStopScript();

            if ($this->yandex_dir != '/') {
                // Check if the folder we want to use exists. If not we create it.
                if ($yandex_lib->checkExists($this->yandex_dir) === false) {
                    $this->log(
                        sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX)
                        . ' ' . $this->l('Create the directory', self::PAGE) . ' "' . $this->yandex_dir . '"'
                    );

                    $this->checkStopScript();

                    if ($yandex_lib->createFolder($this->yandex_dir) === false) {
                        $this->log(
                            'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX)
                            . ' ' . $this->l('Error while creating the directory', self::PAGE) . ' "' . $this->yandex_dir . '"'
                        );

                        return false;
                    }
                }
            }

            $this->checkStopScript();

            // Yandex dir should end with a "/" except when testing if exist
            $this->yandex_dir = $temp_directory;

            $this->log(sprintf($this->l('Creating backup on %s account...', self::PAGE), self::YANDEX));

            $this->yandex_nb_part = 1;
            $this->yandex_position = 0;
            $this->distant_tar_content = '';
            $this->distant_tar_content_size = 0;

            $this->distant_tar_content = $this->createTarStart();
            $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
        }

        $this->checkStopScript();

        if ($this->getFileSize($this->file_list_file) <= 0) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX)
                . ' ' . $this->l('No file to backup', self::PAGE)
            );

            return false;
        }

        $this->checkStopScript();

        if (!is_resource($this->handle_file_list_file)) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX)
                . ' ' . $this->l('Error while getting the files to backup', self::PAGE)
            );

            return false;
        }

        $this->handle_file_list_file = $this->goToPositionInFile(
            $this->handle_file_list_file,
            $this->position_file_list_file,
            false
        );

        $this->checkStopScript();

        while (!feof($this->handle_file_list_file)) {
            $this->checkStopScript();

            $line = rtrim(fgets($this->handle_file_list_file));

            if (!$line) {
                continue;
            }

            $pos_cut = strrpos($line, ':');

            if ($pos_cut === false) {
                continue;
            }

            $current_file = self::binaryToString(Tools::substr($line, 0, $pos_cut));

            if (!$current_file) {
                continue;
            }

            $this->checkStopScript();

            $found_size = Tools::substr($line, $pos_cut + 1);
            $tar_name = basename($this->part_list[$this->part_number - 1]);
            $path_in_tar = '';

            // Normalize path
            $current_normalized_file = $this->normalizePath($current_file);

            // Find relative filename
            if (!$path_in_tar) {
                $filename = ltrim(self::getPart($current_normalized_file, $this->base_length), '/');
            } else {
                $filename = ltrim($path_in_tar, '/');
            }

            // File information
            $info = $this->tarFileInfo($current_file);
            $diff_size = 0;

            if ($info['size'] != $found_size) {
                if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX)
                        . ' ' . sprintf(
                            $this->l('The file %1$s has changed of size from %2$s to %3$s', self::PAGE),
                            $current_file,
                            $this->readableSize($found_size) . ' (' . $found_size . ')',
                            $this->readableSize($info['size']) . ' (' . $info['size'] . ')'
                        )
                    );
                }

                $diff_size = $info['size'] - $found_size;
                $info['size'] = $found_size;
            }

            if (in_array($current_file, $this->list_dump_files) && $this->config->type_backup == $this->type_backup_complete) {
                $path_module_backup = 'modules/' . $this->name . '/' . self::BACKUP_FOLDER . '/';
                $path_in_tar = str_replace($this->config_backup_dir, $path_module_backup, $current_file);
            }

            $this->checkStopScript();

            // Create file header
            if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                // Start to add a new file to the tar
                $header = $this->createTarHeader($filename, $info);

                $this->distant_tar_content_size += $this->getHeaderFileSize($filename, false);
                $this->distant_tar_content .= $header;

                ++$this->files_done;

                $this->pos_file_to_tar = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE_CONTINUE;
            }

            if ($info['size'] > 0) {
                // Open the file
                if (($file_read = @fopen($current_file, 'rb')) === false) {
                    // If it is the first opening of the file
                    if ($this->pos_file_to_tar == 0) {
                        $this->log(
                            'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX)
                            . ' ' . $this->l('File', self::PAGE) . ' ' . $current_file . ' '
                            . $this->l('will be empty because the module can not open it, please check its rights and user owner', self::PAGE)
                        );
                    }
                }

                $this->checkStopScript();

                if (is_resource($file_read) && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE_CONTINUE) {
                    $file_read = $this->goToPositionInFile($file_read, $this->pos_file_to_tar);

                    if ($file_read === false) {
                        return false;
                    }
                }

                // Data of the file
                $leftsize = $info['size'] - $this->pos_file_to_tar;
                $blocksize = self::TAR_BLOCK_SIZE;

                while ($leftsize > 0) {
                    $this->checkStopScript();

                    if ($this->next_step == $this->step_send['yandex']) {
                        // Start the upload process
                        $start_upload = $yandex_lib->startUploadContent($this->yandex_dir . $tar_name, $this->tar_files_size[$this->part_number]);

                        if ($start_upload === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be created', self::PAGE)
                            );

                            return false;
                        }

                        $this->yandex_position = 0;
                        $this->next_step = $this->step_send['yandex_resume'];
                    }

                    // If max size for Yandex has been reach, we send the content or the size of the tar was reach
                    if ($this->distant_tar_content_size >= YandexLib::MAX_FILE_UPLOAD_SIZE
                        || ($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                    ) {
                        $end_of_current_part = 0;

                        if (($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]) {
                            $this->distant_tar_content_size += $tar_end_size;
                            $this->distant_tar_content .= $this->createTarEnd();
                            $end_of_current_part = 1;
                        }

                        if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                            $this->log(
                                $this->l('The total size should not be less that the size we are now at', self::PAGE)
                                . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                            );
                        }

                        // Send content using the current session
                        $resume_upload_content = $yandex_lib->resumeUploadContent(
                            $this->distant_tar_content,
                            $this->distant_tar_content_size,
                            $this->tar_files_size[$this->part_number]
                        );

                        if ($resume_upload_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be completed', self::PAGE)
                            );

                            return false;
                        } else {
                            $this->yandex_position += $this->distant_tar_content_size;

                            $this->distant_tar_content = '';
                            $this->distant_tar_content_size = 0;
                        }

                        if ($end_of_current_part) {
                            // New part, so back to init values
                            ++$this->yandex_nb_part;
                            ++$this->part_number;

                            $this->next_step = $this->step_send['yandex'];
                            $this->yandex_position = 0;
                            $this->old_percent = 0;
                            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;

                            $this->distant_tar_content = $this->createTarStart();
                            $this->distant_tar_content_size += (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
                        }
                    } else {
                        // Read data
                        $leftsize -= $blocksize;

                        if ($leftsize < 0) {
                            $blocksize += $leftsize;
                        }

                        $temp_content = '';

                        if (is_resource($file_read)) {
                            $temp_content = $this->createTarContent($file_read, $blocksize);
                        }

                        // Get where we are in the file
                        $this->pos_file_to_tar += $blocksize;

                        // If there is no content because the file size has changed (smaller file) we need to add \0
                        if ($diff_size < 0 && !$temp_content) {
                            // self::TAR_BLOCK_SIZE since createTarContent
                            // return self::TAR_BLOCK_SIZE size block (using self::pad)
                            $temp_content = self::pad('', self::TAR_BLOCK_SIZE);
                            $diff_size += self::TAR_BLOCK_SIZE;

                            if ($this->config->crypt_backup && $this->config->ignore_compression) {
                                $temp_content = $this->cryptBackup($temp_content);
                                $diff_size += SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;
                            }
                        } elseif ($temp_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX)
                                . ' ' . $this->l('The module was unable to read the file', self::PAGE)
                                . ' ' . $current_file . ', ' . $this->l('please check its rights and user owner', self::PAGE)
                            );

                            if (is_resource($file_read)) {
                                fclose($file_read);
                            }

                            return false;
                        }

                        $this->distant_tar_content .= $temp_content;
                        $this->distant_tar_content_size += self::TAR_BLOCK_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES : 0);

                        $percent = (($this->distant_tar_content_size + $this->yandex_position) / $this->tar_files_size[$this->part_number]) * 100;

                        if ($percent >= $this->old_percent + 1) {
                            $this->old_percent = round($percent, 0);

                            if ($this->total_nb_part > 1) {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX)
                                    . ' ' . $this->part_number . '/' . $this->total_nb_part . $this->l(':', self::PAGE)
                                    . ' ' . (int) $percent . '%'
                                );
                            } else {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX)
                                    . ' ' . (int) $percent . '%'
                                );
                            }
                        }
                    }

                    // refresh
                    $this->refreshBackup(true);
                }

                // Close file
                if (is_resource($file_read)) {
                    fclose($file_read);
                }
            }

            /*if (basename($filename) == self::TEMP_EMPTY_FILE) {
                @unlink($current_file);
            }*/

            $this->position_file_list_file = ftell($this->handle_file_list_file);

            $this->checkStopScript();

            // Check if tar file size has reach its predicted size
            if (isset($this->tar_files_size[$this->part_number])
                && ($this->yandex_position + $this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                && $this->yandex_position < $this->tar_files_size[$this->part_number]
            ) {
                // The tar file will be too big, we need to close it and use a new one
                $this->distant_tar_content .= $this->createTarEnd();
                $this->distant_tar_content_size += $tar_end_size;

                if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                    $this->log(
                        $this->l('The total size should not be less that the size we are now at', self::PAGE)
                        . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                    );
                }

                // Finish sending current tar
                $resume_upload_content = $yandex_lib->resumeUploadContent(
                    $this->distant_tar_content,
                    $this->distant_tar_content_size,
                    $this->tar_files_size[$this->part_number]
                );

                if ($resume_upload_content === false) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX)
                        . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                        . ' ' . $this->l('cannot be finished', self::PAGE)
                    );

                    return false;
                }

                // New part, so back to init values
                ++$this->yandex_nb_part;
                ++$this->part_number;

                $this->next_step = $this->step_send['yandex'];
                $this->yandex_position = 0;
                $this->old_percent = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                $this->distant_tar_content = '';
                $this->distant_tar_content_size = 0;

                $this->distant_tar_content = $this->createTarStart();
                $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);

                // refresh
                $this->refreshBackup(true);
            }
            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
        }

        // Check that there is no missing file
        if ($this->files_done < $this->total_files) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::YANDEX)
                . $this->l('Be careful! Not all of your files have been added to the backup. Number of files backuped:', self::PAGE)
                . ' ' . $this->files_done . '/' . $this->total_files
            );

            return false;
        }

        $this->files_done = 0;
        $this->pos_file_to_tar = 0;

        $this->next_step = $this->step_send['yandex_resume'];

        if ($this->config->send_restore) {
            $this->log(
                sprintf($this->l('Creating restore file on %s account...', self::PAGE), self::YANDEX)
            );

            // Upload the file
            if ($yandex_lib->uploadFile($this->restore_file, $this->yandex_dir . self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Connect to a FTP server
     *
     * @param string $ftp_server SFTP server
     * @param string $ftp_login SFTP login
     * @param string $ftp_pass SFTP password
     * @param int $ftp_port SFTP port
     * @param bool $ftp_ssl SFTP SSL
     * @param bool $ftp_pasv SFTP passive mode
     * @param string $ftp_dir SFTP directory
     *
     * @return ressource The FTP connexion
     */
    public function connectFtp($ftp_server, $ftp_login, $ftp_pass, $ftp_port, $ftp_ssl, $ftp_pasv, $ftp_dir = '')
    {
        if ($ftp_ssl) {
            $connection = ftp_ssl_connect($ftp_server, (int) $ftp_port, self::FTP_TIMEOUT);
        } else {
            // Beware of the warning from php if failure
            $connection = @ftp_connect($ftp_server, $ftp_port, self::FTP_TIMEOUT);
        }

        if (!$connection) {
            $this->log(
                'WAR'
                . sprintf($this->l('Unable to connect to the %s server, please verify your data', self::PAGE), self::FTP)
            );

            return false;
        }

        // Beware of the warning from php if failure
        $login = @ftp_login($connection, $ftp_login, $ftp_pass);

        if (!$login) {
            ftp_close($connection);
            $this->log(
                'WAR'
                . sprintf(
                    $this->l('Unable to log in the %s server, please verify your credentials', self::PAGE),
                    self::FTP
                )
            );

            return false;
        }

        ftp_pasv($connection, $ftp_pasv);

        if ($ftp_dir != '') {
            ftp_chdir($connection, $ftp_dir);
        }

        return $connection;
    }

    /**
     * Send a file on a FTP account
     *
     * @return bool The success or failure of the operation
     */
    protected function sendFileToFTP()
    {
        $ftp = new FtpNtbr($this->ftp_account_id);
        $ftp_server = $ftp->server;
        $ftp_login = $ftp->login;
        $ftp_pass = $this->decrypt($ftp->password);
        $ftp_port = $ftp->port;
        $ftp_ssl = $ftp->ssl;
        $ftp_pasv = $ftp->passive_mode;

        if ($this->next_step == $this->step_send['ftp'] && (!isset($this->ftp_nb_part) || $this->ftp_nb_part == 1)) {
            $this->ftp_dir = $ftp->directory;
            $this->ftp_nb_part = 1;

            // FTP dir should start and end with a /
            $this->ftp_dir = rtrim($this->normalizePath($this->ftp_dir), '/') . '/';
            if ($this->ftp_dir[0] !== '/') {
                $this->ftp_dir = '/' . $this->ftp_dir;
            }

            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::FTP));
        }

        $connection = $this->connectFtp($ftp_server, $ftp_login, $ftp_pass, (int) $ftp_port, $ftp_ssl, $ftp_pasv);

        if (!$connection) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::FTP)
            );

            return false;
        }

        if ($this->next_step == $this->step_send['ftp']) {
            $ftp_current_directory = ftp_pwd($connection);

            if (!isset($this->ftp_nb_part) || $this->ftp_nb_part == 1) {
                if ($ftp_current_directory != '/') {
                    $this->ftp_dir = $ftp_current_directory . $this->ftp_dir;
                }
            }
        }

        ftp_chdir($connection, $this->ftp_dir);

        if ($this->next_step == $this->step_send['ftp'] && (!isset($this->ftp_nb_part) || $this->ftp_nb_part == 1)) {
            // If file already on FTP
            foreach ($this->part_list as $part) {
                $file_destination = $this->ftp_dir . basename($part);

                $this->log(
                    $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                );
                // -1 == error. If file is bigger than 2GB we can have negative number, so be carefull
                if (ftp_size($connection, basename($part)) != -1) {
                    $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                    if (ftp_delete($connection, basename($part)) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                    }
                }
            }

            if (!$this->deleteFTPOldBackup($connection)) {
                ftp_close($connection);

                return false;
            }

            $this->ftp_position = 0;
        }

        $nb_part = 1;
        foreach ($this->part_list as $part) {
            if ($this->ftp_nb_part == $nb_part) {
                // Restart connection for each file to avoid server disconnection problems
                ftp_close($connection);
                $connection = $this->connectFtp(
                    $ftp_server,
                    $ftp_login,
                    $ftp_pass,
                    (int) $ftp_port,
                    $ftp_ssl,
                    $ftp_pasv,
                    $this->ftp_dir
                );

                if (!$connection) {
                    $this->log('WAR' . sprintf($this->l('An error occured while uploading your backup to the %s server, connection to ftp server was shutdown', self::PAGE), self::FTP));

                    return false;
                }

                $total_file_size = $this->getFileSize($part);
                $ftp_file_path = basename($part);
                $last_percent = 0;

                $file = fopen($part, 'r+');

                if ($file === false) {
                    $this->log('WAR' . sprintf($this->l('Unable to access backup file in order to send it by %s, please check file rights', self::PAGE), self::FTP));

                    return false;
                }

                if ($this->next_step == $this->step_send['ftp_resume']) {
                    // Go to the last position in the file
                    $file = $this->goToPositionInFile($file, $this->ftp_position, false);

                    if ($file === false) {
                        return false;
                    }
                } else {
                    $this->next_step = $this->step_send['ftp_resume'];
                }

                $byte_offset = $this->ftp_position;

                // Send the file
                $ftp_upload = ftp_nb_fput($connection, $ftp_file_path, $file, FTP_BINARY, $this->ftp_position);

                while ($ftp_upload == FTP_MOREDATA) {
                    $this->checkStopScript();
                    $byte_offset = ftell($file);

                    $percent = (int) (($byte_offset / $total_file_size) * 100);
                    if ($percent >= ($last_percent + 1)) {
                        if ($this->total_nb_part > 1) {
                            $this->log(
                                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::FTP)
                                . ' ' . $this->ftp_nb_part . '/' . $this->total_nb_part
                                . $this->l(':', self::PAGE) . ' ' . (int) $percent . '%'
                            );
                        } else {
                            $this->log(
                                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::FTP)
                                . ' ' . $percent . '%'
                            );
                        }

                        $last_percent = $percent;
                    }
                    $ftp_upload = ftp_nb_continue($connection);

                    if ($this->validRefresh(true)) {
                        ftp_close($connection);

                        if ($ftp_ssl) {
                            $connection = ftp_ssl_connect($ftp_server, (int) $ftp_port, self::FTP_TIMEOUT);
                        } else {
                            // Beware of the warning from php if failure
                            $connection = @ftp_connect($ftp_server, $ftp_port, self::FTP_TIMEOUT);
                        }

                        if (!$connection) {
                            $this->log(
                                'WAR'
                                . sprintf(
                                    $this->l('Unable to connect to the %s server, please verify your data', self::PAGE),
                                    self::FTP
                                )
                            );

                            return false;
                        }

                        // Beware of the warning from php if failure
                        $login = @ftp_login($connection, $ftp_login, $ftp_pass);
                        if (!$login) {
                            ftp_close($connection);
                            $this->log('WAR' . sprintf($this->l('Unable to log in the %s server, please verify your credentials', self::PAGE), self::FTP));

                            return false;
                        }

                        ftp_raw($connection, 'TYPE I');
                        $response = ftp_raw($connection, 'SIZE ' . $this->ftp_dir . $ftp_file_path);
                        $response_code = Tools::substr($response[0], 0, 3);
                        $response_msg = Tools::substr($response[0], 4);

                        if ($response_code == '213') {
                            $this->ftp_position = $response_msg;
                        } else {// Try once more after a small pause
                            sleep(3);
                            ftp_raw($connection, 'TYPE I');
                            $response = ftp_raw($connection, 'SIZE ' . $this->ftp_dir . $ftp_file_path);
                            $response_code = Tools::substr($response[0], 0, 3);
                            $response_msg = Tools::substr($response[0], 4);

                            if ($response_code == '213') {
                                $this->ftp_position = $response_msg;
                            } else {
                                $this->log('ftp_raw SIZE ' . $this->ftp_dir . $ftp_file_path . ' ' . $this->l('failed', self::PAGE), true);
                                $this->log($response, true);

                                $response = ftp_rawlist($connection, $this->ftp_dir);

                                if (is_array($response)) {
                                    foreach ($response as $child) {
                                        if (strpos($child, $ftp_file_path) !== false) {
                                            $chunks = preg_split("/\s+/", $child);

                                            list($item['rights'], $item['number'], $item['user'], $item['group'], $item['size'], $item['month'], $item['day'], $item['time']) = $chunks;

                                            if (isset($item['size']) && $item['size'] > 0) {
                                                $this->ftp_position = $item['size'];
                                            } else {
                                                $this->log('ftp_rawlist() ' . $this->ftp_dir . ' ' . $this->l('failed', self::PAGE), true);
                                                $this->log($item, true);
                                            }
                                        }
                                    }
                                } else {
                                    $this->log('ftp_rawlist() ' . $this->ftp_dir . ' ' . $this->l('failed', self::PAGE), true);
                                    $this->log('WAR' . $response_msg);

                                    return false;
                                }
                            }
                        }

                        // refresh
                        $this->refreshBackup(true);
                    }
                }

                if ($ftp_upload != FTP_FINISHED) {
                    $byte_offset = ftell($file);

                    if ($byte_offset != $total_file_size) {
                        ftp_close($connection);
                        $this->log(
                            'WAR'
                            . sprintf(
                                $this->l('An error occured while uploading your backup to the %1$s server, please check your %2$s server log and retry', self::PAGE),
                                self::FTP,
                                self::FTP
                            )
                        );

                        if (!$ftp_pasv) {
                            $this->log('WAR' . $this->l('Check if passive mode is needed', self::PAGE));
                        }

                        return false;
                    }
                }

                ++$this->ftp_nb_part;
                // New part, so back to init values
                $this->next_step = $this->step_send['ftp'];
                $this->ftp_position = 0;
            }

            ++$nb_part;
        }

        $this->next_step = $this->step_send['ftp_resume'];

        // Send restore file if needed
        if ($this->config->send_restore) {
            // Test if the connexion is still open
            if (ftp_pwd($connection) === false) {
                $this->log(
                    sprintf($this->l('Try to connect to the %s server. The connexion was lost.', self::PAGE), self::FTP)
                );

                if ($ftp_ssl) {
                    $connection = ftp_ssl_connect($ftp_server, (int) $ftp_port, self::FTP_TIMEOUT);
                } else {
                    // Beware of the warning from php if failure
                    $connection = @ftp_connect($ftp_server, $ftp_port, self::FTP_TIMEOUT);
                }

                if (!$connection) {
                    $this->log(
                        'WAR'
                        . sprintf(
                            $this->l('Unable to connect to the %s server, please verify your data', self::PAGE),
                            self::FTP
                        )
                    );

                    return false;
                }

                // Beware of the warning from php if failure
                $login = @ftp_login($connection, $ftp_login, $ftp_pass);
                if (!$login) {
                    ftp_close($connection);
                    $this->log(
                        'WAR'
                        . sprintf(
                            $this->l('Unable to log in the %s server, please verify your credentials', self::PAGE),
                            self::FTP
                        )
                    );

                    return false;
                }

                ftp_pasv($connection, $ftp_pasv);

                ftp_chdir($connection, $this->ftp_dir);
            }

            $file_destination = $this->ftp_dir . basename($this->restore_file);
            $this->log($this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination);
            // -1 == error. If file is bigger than 2GB we can have negative number, so be carefull
            if (ftp_size($connection, basename($this->restore_file)) != -1) {
                $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                if (ftp_delete($connection, basename($this->restore_file)) === false) {
                    $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                }
            }

            $this->log(sprintf($this->l('Sending restore file to %s account...', self::PAGE), self::FTP));

            $total_file_size = $this->getFileSize($this->restore_file);
            $ftp_file_path = self::NEW_RESTORE_NAME;
            $last_percent = 0;

            $file = fopen($this->restore_file, 'r+');

            if ($file === false) {
                $this->log('WAR' . sprintf($this->l('Unable to access restoration script in order to send it by %s, please check file rights', self::PAGE), self::FTP));
            } else {
                // Send the file
                $upload = ftp_nb_fput($connection, $ftp_file_path, $file, FTP_BINARY);

                while ($upload == FTP_MOREDATA) {
                    $this->checkStopScript();
                    $byte_offset = ftell($file);
                    $percent = (int) (($byte_offset / $total_file_size) * 100);
                    if ($percent >= ($last_percent + 1)) {
                        $this->log(
                            sprintf($this->l('Sending restore file to %s account:', self::PAGE), self::FTP)
                            . ' ' . $percent . '%'
                        );

                        $last_percent = $percent;
                    }
                    $upload = ftp_nb_continue($connection);
                }

                if ($upload != FTP_FINISHED) {
                    ftp_close($connection);
                    $this->log(
                        'WAR'
                        . sprintf(
                            $this->l('An error occured while uploading your restore file to the %1$s server, please check your %2$s server log and retry', self::PAGE),
                            self::FTP,
                            self::FTP
                        )
                    );

                    return false;
                }
            }
        }

        ftp_close($connection);

        return true;
    }

    /**
     * Send a file on a SFTP account
     *
     * @return bool The success or failure of the operation
     */
    protected function sendFileToSFTP()
    {
        $ftp = new FtpNtbr($this->ftp_account_id);
        $ftp_server = $ftp->server;
        $ftp_login = $ftp->login;
        $ftp_pass = $this->decrypt($ftp->password);
        $ftp_port = $ftp->port;

        if ($this->next_step == $this->step_send['ftp'] && (!isset($this->ftp_nb_part) || $this->ftp_nb_part == 1)) {
            $this->ftp_dir = $ftp->directory;

            // SFTP dir should start and end with a /
            $this->ftp_dir = rtrim($this->normalizePath($this->ftp_dir), '/') . '/';
            if ($this->ftp_dir[0] !== '/') {
                $this->ftp_dir = '/' . $this->ftp_dir;
            }
        }

        $this->initForSFTP();

        $sftp_lib = new phpseclib\Net\SFTP($ftp_server, $ftp_port);

        // Beware of the warning from php if failure
        if (!@$sftp_lib->login($ftp_login, $ftp_pass)) {
            $this->log(
                'WAR'
                . sprintf(
                    $this->l('Unable to connect to the %s server, please verify your credentials', self::PAGE),
                    self::SFTP
                )
            );

            return false;
        }

        if ($this->next_step == $this->step_send['ftp'] && (!isset($this->ftp_nb_part) || $this->ftp_nb_part == 1)) {
            if ($this->ftp_dir[0] !== '/') {
                $this->ftp_dir = '/' . $this->ftp_dir;
            }

            $this->ftp_dir = $sftp_lib->pwd() . $this->ftp_dir;

            // Check if the directory exists
            if (!$sftp_lib->is_dir($this->ftp_dir)) {
                // Create the directory
                if (!$sftp_lib->mkdir($this->ftp_dir)) {
                    $this->log($this->l('Error while creating the directory:', self::PAGE) . ' ' . $this->ftp_dir);

                    $this->ftp_dir = $sftp_lib->pwd();
                }
            }

            // If file already on SFTP
            foreach ($this->part_list as $part) {
                $file_destination = $this->ftp_dir . basename($part);

                $this->log(
                    $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                );
                if ($sftp_lib->file_exists($file_destination)) {
                    $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                    if ($sftp_lib->delete($file_destination) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                    }
                }
            }

            if ($this->config->send_restore) {
                $script_restore_destination = $this->ftp_dir . self::NEW_RESTORE_NAME;

                $this->log(
                    $this->l('Check if there is a previous version of the restore script:', self::PAGE) . ' ' . $script_restore_destination
                );

                if ($sftp_lib->file_exists($script_restore_destination)) {
                    $this->log($this->l('Delete previous version of the restore script:', self::PAGE) . ' ' . $script_restore_destination);

                    if ($sftp_lib->delete($script_restore_destination) === false) {
                        $this->log($this->l('Error while deleting the restore script:', self::PAGE) . ' ' . $script_restore_destination);
                    }
                }
            }

            if (!$this->deleteSFTPOldBackup($sftp_lib, $this->ftp_dir)) {
                $this->closeSFTP($sftp_lib);

                return false;
            }

            $this->ftp_nb_part = 1;
            $this->ftp_position = 0;
        }

        $sftp_lib->chdir($this->ftp_dir);

        $nb_part = 1;
        foreach ($this->part_list as $part) {
            if ($this->ftp_nb_part == $nb_part) {
                $total_file_size = $this->getFileSize($part);
                $byte_offset = $this->ftp_position;
                $last_percent = 0;
                $file = fopen($part, 'r+');

                if ($this->next_step == $this->step_send['ftp_resume'] && $this->ftp_position > 0) {
                    // Go to the last position in the file
                    $file = $this->goToPositionInFile($file, $this->ftp_position, false);

                    if ($file === false) {
                        return false;
                    }
                }

                $this->next_step = $this->step_send['ftp_resume'];

                while (!feof($file)) {
                    $this->checkStopScript();
                    $part_file = fread($file, self::MAX_FILE_UPLOAD_SIZE);
                    $byte_offset += self::MAX_FILE_UPLOAD_SIZE;
                    $this->ftp_position = $byte_offset;

                    if ($total_file_size == 0) {
                        $this->log(
                            'WAR' . $this->l('Your file seems to have an issue. Please check it.', self::PAGE) . ' ' . $part
                        );

                        return false;
                    }

                    $percent = (int) (($byte_offset / $total_file_size) * 100);

                    // if self::MAX_FILE_UPLOAD_SIZE > than what is left to upload
                    if ($percent > 100) {
                        $percent = 100;
                    }

                    if ($percent >= ($last_percent + 1)) {
                        if ($this->total_nb_part > 1) {
                            $this->log(
                                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SFTP) . ' '
                                . $nb_part . '/' . $this->total_nb_part . $this->l(':', self::PAGE) . ' ' . (int) $percent . '%'
                            );
                        } else {
                            $this->log(
                                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SFTP)
                                . ' ' . $percent . '%'
                            );
                        }

                        $last_percent = $percent;
                    }

                    if (!$sftp_lib->put(basename($part), $part_file, phpseclib\Net\SFTP::RESUME)) {
                        $this->log(
                            'WAR'
                            . sprintf(
                                $this->l('An error occured while uploading your backup to the %1$s server, please check your %2$s server log and retry', self::PAGE),
                                self::SFTP,
                                self::SFTP
                            )
                        );

                        return false;
                    }

                    // refresh
                    $this->refreshBackup(true);
                }

                ++$this->ftp_nb_part;
                // New part, so back to init values
                $this->next_step = $this->step_send['ftp'];
                $this->ftp_position = 0;
            }

            ++$nb_part;
        }

        $this->next_step = $this->step_send['ftp_resume'];

        if ($this->config->send_restore) {
            $this->log(
                sprintf($this->l('Sending restore file to %s account...', self::PAGE), self::SFTP)
            );

            $total_file_size = $this->getFileSize($this->restore_file);
            $byte_offset = 0;
            $last_percent = 0;
            $file = fopen($this->restore_file, 'r+');

            while (!feof($file)) {
                $this->checkStopScript();
                $part_file = fread($file, self::MAX_FILE_UPLOAD_SIZE);
                $byte_offset += self::MAX_FILE_UPLOAD_SIZE;

                if ($total_file_size == 0) {
                    $this->log(
                        'WAR'
                        . $this->l('Your file seems to have an issue. Please check it.', self::PAGE)
                        . ' ' . $this->restore_file
                    );

                    return false;
                }

                $percent = (int) (($byte_offset / $total_file_size) * 100);

                // if self::MAX_FILE_UPLOAD_SIZE > than what is left to upload
                if ($percent > 100) {
                    $percent = 100;
                }

                if ($percent >= ($last_percent + 1)) {
                    $this->log(
                        sprintf($this->l('Sending restore file to %s account:', self::PAGE), self::SFTP)
                        . ' ' . $percent . '%'
                    );

                    $last_percent = $percent;
                }

                if (!$sftp_lib->put(self::NEW_RESTORE_NAME, $part_file, phpseclib\Net\SFTP::RESUME)) {
                    $this->log(
                        'WAR'
                        . sprintf(
                            $this->l('An error occured while uploading your restore file to the %1$s server, please check your %2$s server log and retry', self::PAGE),
                            self::SFTP,
                            self::SFTP
                        )
                    );

                    return false;
                }
            }
        }

        $this->closeSFTP($sftp_lib);

        return true;
    }

    /**
     * Create a tar file on a SFTP account
     *
     * @return bool The success or failure of the operation
     */
    protected function createTarOnSFTP()
    {
        $ftp = new FtpNtbr($this->ftp_account_id);
        $ftp_server = $ftp->server;
        $ftp_login = $ftp->login;
        $ftp_pass = $this->decrypt($ftp->password);
        $ftp_port = $ftp->port;
        $tar_end_size = self::TAR_END_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? (SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES * 2) : 0);

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['ftp']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->ftp_nb_part) || $this->ftp_nb_part == 1)
        ) {
            $this->ftp_dir = $ftp->directory;

            // SFTP dir should start and end with a /
            $this->ftp_dir = rtrim($this->normalizePath($this->ftp_dir), '/') . '/';
            if ($this->ftp_dir[0] !== '/') {
                $this->ftp_dir = '/' . $this->ftp_dir;
            }

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
            $this->files_done = 0;
            $this->position_file_list_file = 0;
            $this->part_number = 1;
            $this->old_percent = 0;
            $tar_name = basename($this->part_list[$this->part_number - 1]);

            if (count($this->part_list) != count($this->tar_files_size)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SFTP)
                    . ' ' . $this->l('The backup cannot be created', self::PAGE)
                );

                return false;
            }

            if (!isset($this->tar_files_size[$this->part_number]) || !$this->tar_files_size[$this->part_number]) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SFTP) . ' '
                    . sprintf($this->l('The calculated size of the file %s is not valid', self::PAGE), $tar_name)
                );

                return false;
            }
        }

        $this->checkStopScript();

        $this->initForSFTP();

        $sftp_lib = new phpseclib\Net\SFTP($ftp_server, $ftp_port);

        // Beware of the warning from php if failure
        if (!@$sftp_lib->login($ftp_login, $ftp_pass)) {
            $this->log(
                'WAR'
                . sprintf(
                    $this->l('Unable to connect to the %s server, please verify your credentials', self::PAGE),
                    self::SFTP
                )
            );

            return false;
        }

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['ftp']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->ftp_nb_part) || $this->ftp_nb_part == 1)
        ) {
            if ($this->ftp_dir[0] !== '/') {
                $this->ftp_dir = '/' . $this->ftp_dir;
            }

            $this->ftp_dir = $sftp_lib->pwd() . $this->ftp_dir;

            // Check if the directory exists
            if (!$sftp_lib->is_dir($this->ftp_dir)) {
                // Create the directory
                if (!$sftp_lib->mkdir($this->ftp_dir)) {
                    $this->log($this->l('Error while creating the directory:', self::PAGE) . ' ' . $this->ftp_dir);

                    $this->ftp_dir = $sftp_lib->pwd();
                }
            }

            // If file already on SFTP
            foreach ($this->part_list as $part) {
                $file_destination = $this->ftp_dir . basename($part);

                $this->log(
                    $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                );
                if ($sftp_lib->file_exists($file_destination)) {
                    $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                    if ($sftp_lib->delete($file_destination) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                    }
                }
            }

            if ($this->config->send_restore) {
                $script_restore_destination = $this->ftp_dir . self::NEW_RESTORE_NAME;

                $this->log(
                    $this->l('Check if there is a previous version of the restore script:', self::PAGE) . ' ' . $script_restore_destination
                );

                if ($sftp_lib->file_exists($script_restore_destination)) {
                    $this->log($this->l('Delete previous version of the restore script:', self::PAGE) . ' ' . $script_restore_destination);

                    if ($sftp_lib->delete($script_restore_destination) === false) {
                        $this->log($this->l('Error while deleting the restore script:', self::PAGE) . ' ' . $script_restore_destination);
                    }
                }
            }

            if (!$this->deleteSFTPOldBackup($sftp_lib, $this->ftp_dir)) {
                $this->closeSFTP($sftp_lib);

                return false;
            }

            $this->ftp_nb_part = 1;
            $this->ftp_position = 0;
            $this->distant_tar_content = '';
            $this->distant_tar_content_size = 0;

            $this->distant_tar_content = $this->createTarStart();
            $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
        }

        $this->checkStopScript();

        $sftp_lib->chdir($this->ftp_dir);

        if ($this->getFileSize($this->file_list_file) <= 0) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SFTP)
                . ' ' . $this->l('No file to backup', self::PAGE)
            );

            return false;
        }

        if (!is_resource($this->handle_file_list_file)) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SFTP)
                . ' ' . $this->l('Error while getting the files to backup', self::PAGE)
            );

            return false;
        }

        $this->handle_file_list_file = $this->goToPositionInFile(
            $this->handle_file_list_file,
            $this->position_file_list_file,
            false
        );

        $this->checkStopScript();

        while (!feof($this->handle_file_list_file)) {
            $this->checkStopScript();

            $line = rtrim(fgets($this->handle_file_list_file));

            if (!$line) {
                continue;
            }

            $pos_cut = strrpos($line, ':');

            if ($pos_cut === false) {
                continue;
            }

            $current_file = self::binaryToString(Tools::substr($line, 0, $pos_cut));

            if (!$current_file) {
                continue;
            }

            $this->checkStopScript();

            $found_size = Tools::substr($line, $pos_cut + 1);
            $tar_name = basename($this->part_list[$this->part_number - 1]);
            $path_in_tar = '';

            // Normalize path
            $current_normalized_file = $this->normalizePath($current_file);

            // Find relative filename
            if (!$path_in_tar) {
                $filename = ltrim(self::getPart($current_normalized_file, $this->base_length), '/');
            } else {
                $filename = ltrim($path_in_tar, '/');
            }

            // File information
            $info = $this->tarFileInfo($current_file);
            $diff_size = 0;

            if ($info['size'] != $found_size) {
                if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SFTP)
                        . ' ' . sprintf(
                            $this->l('The file %1$s has changed of size from %2$s to %3$s', self::PAGE),
                            $current_file,
                            $this->readableSize($found_size) . ' (' . $found_size . ')',
                            $this->readableSize($info['size']) . ' (' . $info['size'] . ')'
                        )
                    );
                }

                $diff_size = $info['size'] - $found_size;
                $info['size'] = $found_size;
            }

            if (in_array($current_file, $this->list_dump_files) && $this->config->type_backup == $this->type_backup_complete) {
                $path_module_backup = 'modules/' . $this->name . '/' . self::BACKUP_FOLDER . '/';
                $path_in_tar = str_replace($this->config_backup_dir, $path_module_backup, $current_file);
            }

            $this->checkStopScript();

            // Create file header
            if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                // Start to add a new file to the tar
                $header = $this->createTarHeader($filename, $info);

                $this->distant_tar_content_size += $this->getHeaderFileSize($filename, false);
                $this->distant_tar_content .= $header;

                ++$this->files_done;

                $this->pos_file_to_tar = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE_CONTINUE;
            }

            if ($info['size'] > 0) {
                // Open the file
                if (($file_read = @fopen($current_file, 'rb')) === false) {
                    // If it is the first opening of the file
                    if ($this->pos_file_to_tar == 0) {
                        $this->log(
                            'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SFTP) . ' '
                            . $this->l('File', self::PAGE) . ' ' . $current_file . ' '
                            . $this->l('will be empty because the module can not open it, please check its rights and user owner', self::PAGE)
                        );
                    }
                }

                $this->checkStopScript();

                if (is_resource($file_read) && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE_CONTINUE) {
                    $file_read = $this->goToPositionInFile($file_read, $this->pos_file_to_tar);

                    if ($file_read === false) {
                        return false;
                    }
                }

                // Data of the file
                $leftsize = $info['size'] - $this->pos_file_to_tar;
                $blocksize = self::TAR_BLOCK_SIZE;

                while ($leftsize > 0) {
                    $this->checkStopScript();

                    if ($this->next_step == $this->step_send['ftp']) {
                        $this->ftp_position = 0;
                        $this->next_step = $this->step_send['ftp_resume'];
                    }

                    // If max size for SFTP has been reach, we send the content or the size of the tar was reach
                    if ($this->distant_tar_content_size >= self::MAX_FILE_UPLOAD_SIZE
                        || ($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                    ) {
                        $end_of_current_part = 0;

                        if (($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]) {
                            $this->distant_tar_content_size += $tar_end_size;
                            $this->distant_tar_content .= $this->createTarEnd();
                            $end_of_current_part = 1;
                        }

                        if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                            $this->log(
                                $this->l('The total size should not be less that the size we are now at', self::PAGE)
                                . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                            );
                        }

                        // Send content
                        $upload_content = $sftp_lib->put(
                            $tar_name,
                            $this->distant_tar_content,
                            phpseclib\Net\SFTP::RESUME
                        );

                        if ($upload_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SFTP)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be completed', self::PAGE)
                            );

                            return false;
                        } else {
                            $this->ftp_position += $this->distant_tar_content_size;

                            $this->distant_tar_content = '';
                            $this->distant_tar_content_size = 0;
                        }

                        if ($end_of_current_part) {
                            // New part, so back to init values
                            ++$this->ftp_nb_part;
                            ++$this->part_number;

                            $this->next_step = $this->step_send['ftp'];
                            $this->ftp_position = 0;
                            $this->old_percent = 0;
                            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;

                            $this->distant_tar_content = $this->createTarStart();
                            $this->distant_tar_content_size += (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
                        }
                    } else {
                        // Read data
                        $leftsize -= $blocksize;

                        if ($leftsize < 0) {
                            $blocksize += $leftsize;
                        }

                        $temp_content = '';

                        if (is_resource($file_read)) {
                            $temp_content = $this->createTarContent($file_read, $blocksize);
                        }

                        // Get where we are in the file
                        $this->pos_file_to_tar += $blocksize;

                        if ($diff_size < 0 && !$temp_content) {
                            // self::TAR_BLOCK_SIZE since createTarContent
                            // return self::TAR_BLOCK_SIZE size block (using self::pad)
                            $temp_content = self::pad('', self::TAR_BLOCK_SIZE);
                            $diff_size += self::TAR_BLOCK_SIZE;

                            if ($this->config->crypt_backup && $this->config->ignore_compression) {
                                $temp_content = $this->cryptBackup($temp_content);
                                $diff_size += SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;
                            }
                        } elseif ($temp_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SFTP)
                                . ' ' . $this->l('The module was unable to read the file', self::PAGE)
                                . ' ' . $current_file . ', ' . $this->l('please check its rights and user owner', self::PAGE)
                            );

                            if (is_resource($file_read)) {
                                fclose($file_read);
                            }

                            return false;
                        }

                        $this->distant_tar_content .= $temp_content;
                        $this->distant_tar_content_size += self::TAR_BLOCK_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES : 0);

                        $percent = (($this->distant_tar_content_size + $this->ftp_position) / $this->tar_files_size[$this->part_number]) * 100;

                        if ($percent >= $this->old_percent + 1) {
                            $this->old_percent = round($percent, 0);

                            if ($this->total_nb_part > 1) {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SFTP)
                                    . ' ' . $this->part_number . '/' . $this->total_nb_part . $this->l(':', self::PAGE)
                                    . ' ' . (int) $percent . '%'
                                );
                            } else {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SFTP)
                                    . ' ' . (int) $percent . '%'
                                );
                            }
                        }
                    }

                    // refresh
                    $this->refreshBackup(true);
                }

                // Close file
                if (is_resource($file_read)) {
                    fclose($file_read);
                }
            }

            /*if (basename($filename) == self::TEMP_EMPTY_FILE) {
                @unlink($current_file);
            }*/

            $this->position_file_list_file = ftell($this->handle_file_list_file);

            $this->checkStopScript();

            // Check if tar file size has reach its predicted size
            if (isset($this->tar_files_size[$this->part_number])
                && ($this->ftp_position + $this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                && $this->ftp_position < $this->tar_files_size[$this->part_number]
            ) {
                // The tar file will be too big, we need to close it and use a new one
                $this->distant_tar_content .= $this->createTarEnd();
                $this->distant_tar_content_size += $tar_end_size;

                if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                    $this->log(
                        $this->l('The total size should not be less that the size we are now at', self::PAGE)
                        . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                    );
                }

                // Finish sending current tar
                $upload_content = $sftp_lib->put(
                    $tar_name,
                    $this->distant_tar_content,
                    phpseclib\Net\SFTP::RESUME
                );

                if ($upload_content === false) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SFTP)
                        . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                        . ' ' . $this->l('cannot be finished', self::PAGE)
                    );

                    return false;
                }

                // New part, so back to init values
                ++$this->ftp_nb_part;
                ++$this->part_number;

                $this->next_step = $this->step_send['ftp'];
                $this->ftp_position = 0;
                $this->old_percent = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                $this->distant_tar_content = '';
                $this->distant_tar_content_size = 0;

                $this->distant_tar_content = $this->createTarStart();
                $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);

                // refresh
                $this->refreshBackup(true);
            }

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
        }

        // Check that there is no missing file
        if ($this->files_done < $this->total_files) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SFTP)
                . $this->l('Be careful! Not all of your files have been added to the backup. Number of files backuped:', self::PAGE)
                . ' ' . $this->files_done . '/' . $this->total_files
            );

            return false;
        }

        $this->files_done = 0;
        $this->pos_file_to_tar = 0;
        $this->next_step = $this->step_send['ftp_resume'];

        if ($this->config->send_restore) {
            $this->log(
                sprintf($this->l('Creating restore file on %s account...', self::PAGE), self::SFTP)
            );

            $total_file_size = $this->getFileSize($this->restore_file);
            $byte_offset = 0;
            $last_percent = 0;
            $file = fopen($this->restore_file, 'r+');

            while (!feof($file)) {
                $this->checkStopScript();
                $part_file = fread($file, self::MAX_FILE_UPLOAD_SIZE);
                $byte_offset += self::MAX_FILE_UPLOAD_SIZE;

                if ($total_file_size == 0) {
                    $this->log(
                        'WAR'
                        . $this->l('Your file seems to have an issue. Please check it.', self::PAGE)
                        . ' ' . $this->restore_file
                    );

                    return false;
                }

                $percent = (int) (($byte_offset / $total_file_size) * 100);

                // if self::MAX_FILE_UPLOAD_SIZE > than what is left to upload
                if ($percent > 100) {
                    $percent = 100;
                }

                if ($percent >= ($last_percent + 1)) {
                    $this->log(
                        sprintf($this->l('Creating restore file on %s account:', self::PAGE), self::SFTP)
                        . ' ' . $percent . '%'
                    );

                    $last_percent = $percent;
                }

                if (!$sftp_lib->put(self::NEW_RESTORE_NAME, $part_file, phpseclib\Net\SFTP::RESUME)) {
                    $this->log(
                        'WAR'
                        . sprintf(
                            $this->l('An error occured while uploading your restore file to the %1$s server, please check your %2$s server log and retry', self::PAGE),
                            self::SFTP,
                            self::SFTP
                        )
                    );

                    return false;
                }
            }
        }

        $this->closeSFTP($sftp_lib);

        return true;
    }

    /**
     * Send a file on a OneDrive account
     *
     * @return bool The success or failure of the operation
     */
    protected function sendFileToOnedrive()
    {
        $onedrive = new OnedriveNtbr($this->onedrive_account_id);
        $access_token = $onedrive->token;
        $onedrive_dir = $onedrive->directory_key;

        if ($access_token !== false) {
            if ($this->next_step == $this->step_send['onedrive']
                && (!isset($this->onedrive_nb_part) || $this->onedrive_nb_part == 1)
            ) {
                $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::ONEDRIVE));
            }

            $onedrive_lib = $this->connectToOnedrive($access_token, $onedrive->id);

            if (!$onedrive_lib->testConnection()) {
                $this->log(
                    'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::ONEDRIVE)
                );

                return false;
            }

            if ($this->next_step == $this->step_send['onedrive']
                && (!isset($this->onedrive_nb_part) || $this->onedrive_nb_part == 1)
            ) {
                if (Tools::substr($onedrive->directory_path, -1) != '/') {
                    $onedrive->directory_path .= '/';
                }

                // If file already on OneDrive
                foreach ($this->part_list as $part) {
                    $file_destination = $onedrive->directory_path . basename($part);

                    $this->log(
                        $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                    );
                    $id_file = $onedrive_lib->checkExists(basename($part), $onedrive_dir);

                    if ($id_file !== false) {
                        $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                        if ($onedrive_lib->deleteItem($id_file) === false) {
                            $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                        }
                    }
                }

                // Get old backups list
                $old_backups = $this->getOnedriveFiles($onedrive_lib, $onedrive->directory_key);
                $nb_backup_to_keep = $onedrive->config_nb_backup;
                $nb_files = count($old_backups);
                $size_old_backups = 0;

                // Get old backup (to delete) size
                if ($nb_backup_to_keep > 0) {
                    while ($nb_files >= $nb_backup_to_keep) {
                        if (isset($old_backups[$nb_files])) {
                            $size_old_backups += $old_backups[$nb_files]['size_byte'];

                            --$nb_files;
                        }
                    }
                }

                // Get available size
                $available_space = $onedrive_lib->fetchQuota() + $size_old_backups;

                // Get size to upload
                $to_send_size = $this->total_size;

                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);
                    $to_send_size += $total_file_size;
                }

                // Check if we will have enough size after deleting old backup, too add new backup
                if ($available_space < $to_send_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::ONEDRIVE) . ' '
                        . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                    );

                    return false;
                }

                // Delete old backup
                if (!$this->deleteOnedriveOldBackup($onedrive_lib, $old_backups)) {
                    $this->log(
                        'WAR'
                        . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::ONEDRIVE) . ' '
                        . $this->l('Error while deleting old backup', self::PAGE)
                    );

                    return false;
                }

                // Check available space
                $new_available_space = $onedrive_lib->fetchQuota();

                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);
                    $new_available_space -= $total_file_size;
                }

                if ($new_available_space <= $this->total_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::ONEDRIVE) . ' '
                        . $this->l('Not enough space available', self::PAGE)
                    );

                    return false;
                }

                $this->log(sprintf($this->l('Sending backup to %s account...', self::PAGE), self::ONEDRIVE));
                $this->onedrive_nb_part = 1;
                $this->onedrive_position = 0;
            }

            $nb_part = 1;

            // Upload the file
            foreach ($this->part_list as $part) {
                if ($nb_part == $this->onedrive_nb_part) {
                    $file_name = basename($part);
                    $file_path = $part;

                    if ($this->next_step == $this->step_send['onedrive']) {
                        $this->next_step = $this->step_send['onedrive_resume'];
                        // Upload the file
                        $create_file = $onedrive_lib->createFile(
                            $file_name,
                            $file_path,
                            $onedrive_dir,
                            $this->onedrive_nb_part,
                            $this->total_nb_part
                        );

                        if ($create_file === false) {
                            return false;
                        }
                    } else {
                        // Resume upload of the file
                        $resume_create_file = $onedrive_lib->resumeCreateFile(
                            $file_path,
                            $this->onedrive_session,
                            $this->onedrive_position,
                            $this->onedrive_nb_part,
                            $this->total_nb_part
                        );

                        if ($resume_create_file === false) {
                            return false;
                        }
                    }

                    ++$this->onedrive_nb_part;
                    // New part, so back to init values
                    $this->next_step = $this->step_send['onedrive'];
                    $this->onedrive_position = 0;
                }
                ++$nb_part;
            }

            $this->next_step = $this->step_send['onedrive_resume'];

            if ($this->config->send_restore) {
                $this->log(
                    sprintf($this->l('Sending restore file to %s account...', self::PAGE), self::ONEDRIVE)
                );

                // Upload the file
                if ($onedrive_lib->createFile(self::NEW_RESTORE_NAME, $this->restore_file, $onedrive_dir) === false) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Create a tar file on a OneDrive account
     *
     * @return bool The success or failure of the operation
     */
    protected function createTarOnOnedrive()
    {
        $onedrive = new OnedriveNtbr($this->onedrive_account_id);
        $access_token = $onedrive->token;
        $onedrive_dir = $onedrive->directory_key;
        $tar_end_size = self::TAR_END_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? (SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES * 2) : 0);

        $this->checkStopScript();

        if ($access_token === false) {
            $this->log(
                'WAR'
                . sprintf($this->l('Access to your %s account impossible', self::PAGE), self::ONEDRIVE)
            );

            return false;
        }

        if ($this->next_step == $this->step_send['onedrive']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->onedrive_nb_part) || $this->onedrive_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::ONEDRIVE));

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
            $this->files_done = 0;
            $this->position_file_list_file = 0;
            $this->part_number = 1;
            $this->old_percent = 0;
            $tar_name = basename($this->part_list[$this->part_number - 1]);

            if (count($this->part_list) != count($this->tar_files_size)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE) . ' '
                    . $this->l('The backup cannot be created', self::PAGE)
                );

                return false;
            }

            if (!isset($this->tar_files_size[$this->part_number]) || !$this->tar_files_size[$this->part_number]) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE) . ' '
                    . sprintf($this->l('The calculated size of the file %s is not valid', self::PAGE), $tar_name)
                );

                return false;
            }
        }

        $this->checkStopScript();

        $onedrive_lib = $this->connectToOnedrive($access_token, $onedrive->id);

        if (!$onedrive_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::ONEDRIVE)
            );

            return false;
        }

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['onedrive']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->onedrive_nb_part) || $this->onedrive_nb_part == 1)
        ) {
            if (Tools::substr($onedrive->directory_path, -1) != '/') {
                $onedrive->directory_path .= '/';
            }

            // Get old backups list
            $old_backups = $this->getOnedriveFiles($onedrive_lib, $onedrive->directory_key);
            $nb_backup_to_keep = $onedrive->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    $this->checkStopScript();

                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            $this->checkStopScript();

            // Get available size
            $available_space = $onedrive_lib->fetchQuota() + $size_old_backups;

            $this->checkStopScript();

            // Get size to upload
            $to_send_size = $this->total_size;

            if ($this->config->send_restore) {
                $total_file_size = $this->getFileSize($this->restore_file);

                $this->checkStopScript();

                $to_send_size += $total_file_size;
            }

            // Check if we will have enough size after deleting old backup, too add new backup
            if ($available_space < $to_send_size) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE) . ' '
                    . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                );

                return false;
            }

            $this->checkStopScript();

            // Delete old backup
            if (!$this->deleteOnedriveOldBackup($onedrive_lib, $old_backups)) {
                $this->log(
                    'WAR'
                    . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE) . ' '
                    . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            $this->checkStopScript();

            // Check available space
            $new_available_space = $onedrive_lib->fetchQuota();

            $this->checkStopScript();

            if ($this->config->send_restore) {
                $total_file_size = $this->getFileSize($this->restore_file);

                $this->checkStopScript();

                $new_available_space -= $total_file_size;
            }

            if ($new_available_space <= $this->total_size) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE) . ' '
                    . $this->l('Not enough space available', self::PAGE)
                );

                return false;
            }

            $this->log(sprintf($this->l('Creating backup on %s account...', self::PAGE), self::ONEDRIVE));

            $this->onedrive_nb_part = 1;
            $this->onedrive_position = 0;
            $this->distant_tar_content = '';
            $this->distant_tar_content_size = 0;

            $this->distant_tar_content = $this->createTarStart();
            $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
        }

        $this->checkStopScript();

        if ($this->getFileSize($this->file_list_file) <= 0) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE)
                . ' ' . $this->l('No file to backup', self::PAGE)
            );

            return false;
        }

        $this->checkStopScript();

        if (!is_resource($this->handle_file_list_file)) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE)
                . ' ' . $this->l('Error while getting the files to backup', self::PAGE)
            );

            return false;
        }

        $this->handle_file_list_file = $this->goToPositionInFile(
            $this->handle_file_list_file,
            $this->position_file_list_file,
            false
        );

        $this->checkStopScript();

        while (!feof($this->handle_file_list_file)) {
            $this->checkStopScript();

            $line = rtrim(fgets($this->handle_file_list_file));

            if (!$line) {
                continue;
            }

            $pos_cut = strrpos($line, ':');

            if ($pos_cut === false) {
                continue;
            }

            $current_file = self::binaryToString(Tools::substr($line, 0, $pos_cut));

            if ($current_file == '') {
                continue;
            }

            $this->checkStopScript();

            $found_size = Tools::substr($line, $pos_cut + 1);
            $tar_name = basename($this->part_list[$this->part_number - 1]);
            $path_in_tar = '';

            // Normalize path
            $current_normalized_file = $this->normalizePath($current_file);

            // Find relative filename
            if (!$path_in_tar) {
                $filename = ltrim(self::getPart($current_normalized_file, $this->base_length), '/');
            } else {
                $filename = ltrim($path_in_tar, '/');
            }

            // File information
            $info = $this->tarFileInfo($current_file);
            $diff_size = 0;

            if ($info['size'] != $found_size) {
                if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE)
                        . ' ' . sprintf(
                            $this->l('The file %1$s has changed of size from %2$s to %3$s', self::PAGE),
                            $current_file,
                            $this->readableSize($found_size) . ' (' . $found_size . ')',
                            $this->readableSize($info['size']) . ' (' . $info['size'] . ')'
                        )
                    );
                }

                $diff_size = $info['size'] - $found_size;
                $info['size'] = $found_size;
            }

            if (in_array($current_file, $this->list_dump_files) && $this->config->type_backup == $this->type_backup_complete) {
                $path_module_backup = 'modules/' . $this->name . '/' . self::BACKUP_FOLDER . '/';
                $path_in_tar = str_replace($this->config_backup_dir, $path_module_backup, $current_file);
            }

            $this->checkStopScript();

            // Create file header
            if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                // Start to add a new file to the tar
                $header = $this->createTarHeader($filename, $info);

                $this->distant_tar_content_size += $this->getHeaderFileSize($filename, false);
                $this->distant_tar_content .= $header;

                ++$this->files_done;

                $this->pos_file_to_tar = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE_CONTINUE;
            }

            if ($info['size'] > 0) {
                // Open the file
                if (($file_read = @fopen($current_file, 'rb')) === false) {
                    // If it is the first opening of the file
                    if ($this->pos_file_to_tar == 0) {
                        $this->log(
                            'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE)
                            . ' ' . $this->l('File', self::PAGE) . ' ' . $current_file . ' '
                            . $this->l('will be empty because the module can not open it, please check its rights and user owner', self::PAGE)
                        );
                    }
                }

                $this->checkStopScript();

                if (is_resource($file_read) && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE_CONTINUE) {
                    $file_read = $this->goToPositionInFile($file_read, $this->pos_file_to_tar);

                    if ($file_read === false) {
                        return false;
                    }
                }

                // Data of the file
                $leftsize = $info['size'] - $this->pos_file_to_tar;
                $blocksize = self::TAR_BLOCK_SIZE;

                while ($leftsize > 0) {
                    $this->checkStopScript();

                    if ($this->next_step == $this->step_send['onedrive']) {
                        // Create new resumable session
                        $create_session = $onedrive_lib->createSession($tar_name, $onedrive_dir);

                        if ($create_session === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be created', self::PAGE)
                            );

                            return false;
                        }

                        $this->onedrive_position = 0;
                        $this->next_step = $this->step_send['onedrive_resume'];
                    }

                    // If max size for OneDrive has been reach, we send the content or the size of the tar was reach
                    if ($this->distant_tar_content_size >= OnedriveLib::MAX_FILE_UPLOAD_SIZE
                        || ($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                    ) {
                        $end_of_current_part = 0;

                        if (($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]) {
                            $this->distant_tar_content_size += $tar_end_size;
                            $this->distant_tar_content .= $this->createTarEnd();
                            $end_of_current_part = 1;
                        }

                        if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                            $this->log(
                                $this->l('The total size should not be less that the size we are now at', self::PAGE)
                                . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                            );
                        }

                        // Send content using the current session
                        $resume_upload_content = $onedrive_lib->resumeUploadContent(
                            $this->distant_tar_content,
                            $this->distant_tar_content_size,
                            $this->tar_files_size[$this->part_number],
                            $this->onedrive_session,
                            $this->onedrive_position
                        );

                        if ($resume_upload_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be completed', self::PAGE)
                            );

                            return false;
                        } else {
                            $this->onedrive_position += $this->distant_tar_content_size;

                            $this->distant_tar_content = '';
                            $this->distant_tar_content_size = 0;
                        }

                        if ($end_of_current_part) {
                            // New part, so back to init values
                            ++$this->onedrive_nb_part;
                            ++$this->part_number;

                            $this->next_step = $this->step_send['onedrive'];
                            $this->onedrive_position = 0;
                            $this->old_percent = 0;
                            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;

                            $this->distant_tar_content = $this->createTarStart();
                            $this->distant_tar_content_size += (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
                        }
                    } else {
                        // Read data
                        $leftsize -= $blocksize;

                        if ($leftsize < 0) {
                            $blocksize += $leftsize;
                        }

                        $temp_content = '';

                        if (is_resource($file_read)) {
                            $temp_content = $this->createTarContent($file_read, $blocksize);
                        }

                        // Get where we are in the file
                        $this->pos_file_to_tar += $blocksize;

                        // If there is no content because the file size has changed (smaller file) we need to add \0
                        if ($diff_size < 0 && !$temp_content) {
                            // self::TAR_BLOCK_SIZE since createTarContent
                            // return self::TAR_BLOCK_SIZE size block (using self::pad)
                            $temp_content = self::pad('', self::TAR_BLOCK_SIZE);
                            $diff_size += self::TAR_BLOCK_SIZE;

                            if ($this->config->crypt_backup && $this->config->ignore_compression) {
                                $temp_content = $this->cryptBackup($temp_content);
                                $diff_size += SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;
                            }
                        } elseif ($temp_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE)
                                . ' ' . $this->l('The module was unable to read the file', self::PAGE)
                                . ' ' . $current_file . ', ' . $this->l('please check its rights and user owner', self::PAGE)
                            );

                            if (is_resource($file_read)) {
                                fclose($file_read);
                            }

                            return false;
                        }

                        $this->distant_tar_content .= $temp_content;
                        $this->distant_tar_content_size += self::TAR_BLOCK_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES : 0);

                        $percent = (($this->distant_tar_content_size + $this->onedrive_position) / $this->tar_files_size[$this->part_number]) * 100;

                        if ($percent >= $this->old_percent + 1) {
                            $this->old_percent = round($percent, 0);

                            if ($this->total_nb_part > 1) {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE)
                                    . ' ' . $this->part_number . '/' . $this->total_nb_part . $this->l(':', self::PAGE)
                                    . ' ' . (int) $percent . '%'
                                );
                            } else {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE)
                                    . ' ' . (int) $percent . '%'
                                );
                            }
                        }
                    }

                    // refresh
                    $this->refreshBackup(true);
                }

                // Close file
                if (is_resource($file_read)) {
                    fclose($file_read);
                }
            }

            /*if (basename($filename) == self::TEMP_EMPTY_FILE) {
                @unlink($current_file);
            }*/

            $this->position_file_list_file = ftell($this->handle_file_list_file);

            $this->checkStopScript();

            // Check if tar file size has reach its predicted size
            if (isset($this->tar_files_size[$this->part_number])
                && ($this->onedrive_position + $this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                && $this->onedrive_position < $this->tar_files_size[$this->part_number]
            ) {
                // The tar file will be too big, we need to close it and use a new one
                $this->distant_tar_content .= $this->createTarEnd();
                $this->distant_tar_content_size += $tar_end_size;

                if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                    $this->log(
                        $this->l('The total size should not be less that the size we are now at', self::PAGE)
                        . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                    );
                }

                // Finish sending current tar
                $resume_upload_content = $onedrive_lib->resumeUploadContent(
                    $this->distant_tar_content,
                    $this->distant_tar_content_size,
                    $this->tar_files_size[$this->part_number],
                    $this->onedrive_session,
                    $this->onedrive_position
                );

                if ($resume_upload_content === false) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE)
                        . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                        . ' ' . $this->l('cannot be completed', self::PAGE)
                    );

                    return false;
                }

                // New part, so back to init values
                ++$this->onedrive_nb_part;
                ++$this->part_number;

                $this->next_step = $this->step_send['onedrive'];
                $this->onedrive_position = 0;
                $this->old_percent = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                $this->distant_tar_content = '';
                $this->distant_tar_content_size = 0;

                $this->distant_tar_content = $this->createTarStart();
                $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);

                // refresh
                $this->refreshBackup(true);
            }
            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
        }

        // Check that there is no missing file
        if ($this->files_done < $this->total_files) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::ONEDRIVE)
                . $this->l('Be careful! Not all of your files have been added to the backup. Number of files backuped:', self::PAGE)
                . ' ' . $this->files_done . '/' . $this->total_files
            );

            return false;
        }

        $this->files_done = 0;
        $this->pos_file_to_tar = 0;

        $this->next_step = $this->step_send['onedrive_resume'];

        if ($this->config->send_restore) {
            $this->log(
                sprintf($this->l('Creating restore file on %s account...', self::PAGE), self::ONEDRIVE)
            );

            // Upload the file
            if ($onedrive_lib->createFile(self::NEW_RESTORE_NAME, $this->restore_file, $onedrive_dir) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Send a file on a SugarSync account
     *
     * @return bool The success or failure of the operation
     */
    protected function sendFileToSugarsync()
    {
        $sugarsync = new SugarsyncNtbr($this->sugarsync_account_id);
        $token = $sugarsync->token;
        $sugarsync_dir = $sugarsync->directory_key;

        if ($token !== false) {
            if ($this->next_step == $this->step_send['sugarsync']
                && (!isset($this->sugarsync_nb_part) || $this->sugarsync_nb_part == 1)
            ) {
                $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::SUGARSYNC));
            }

            $sugarsync_lib = $this->connectToSugarsync($token, $sugarsync->id);

            if (!$sugarsync_lib->testConnection()) {
                $this->log(
                    'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::SUGARSYNC)
                );

                return false;
            }

            if ($this->next_step == $this->step_send['sugarsync']
                && (!isset($this->sugarsync_nb_part) || $this->sugarsync_nb_part == 1)
            ) {
                if (Tools::substr($sugarsync->directory_path, -1) != '/') {
                    $sugarsync->directory_path .= '/';
                }

                // If file already on SugarSync
                foreach ($this->part_list as $part) {
                    $file_destination = $sugarsync->directory_path . basename($part);

                    $this->log(
                        $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                    );

                    $id_file = $sugarsync_lib->checkExists($sugarsync_dir, basename($part));

                    if ($id_file !== false) {
                        $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                        if (!$sugarsync_lib->deleteFile($id_file)) {
                            $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                        }
                    }
                }

                // Delete old backup
                if (!$this->deleteSugarsyncOldBackup($sugarsync_lib, $sugarsync_dir)) {
                    $this->log(
                        'WAR'
                        . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SUGARSYNC) . ' '
                        . $this->l('Error while deleting old backup', self::PAGE)
                    );

                    return false;
                }

                // Check available space
                $available_space = $sugarsync_lib->getAvailableQuota();

                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);
                    $available_space -= $total_file_size;
                }

                if ($available_space <= $this->total_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SUGARSYNC) . ' '
                        . $this->l('Not enough space available', self::PAGE)
                    );

                    return false;
                }

                $this->log(sprintf($this->l('Sending backup to %s account...', self::PAGE), self::SUGARSYNC));
                $this->sugarsync_nb_part = 1;
                $this->sugarsync_position = 0;
            }

            $nb_part = 1;

            // Upload the file
            foreach ($this->part_list as $part) {
                if ($nb_part == $this->sugarsync_nb_part) {
                    $file_name = basename($part);
                    $file_path = $part;

                    if ($this->next_step == $this->step_send['sugarsync']) {
                        $this->next_step = $this->step_send['sugarsync_resume'];
                        // Upload the file
                        $upload_file = $sugarsync_lib->uploadFile(
                            $file_name,
                            $file_path,
                            $sugarsync_dir,
                            $this->sugarsync_nb_part,
                            $this->total_nb_part
                        );

                        if ($upload_file === false) {
                            return false;
                        }
                    } else {
                        // Resume upload of the file
                        $resume_upload_file = $sugarsync_lib->resumeUploadFile(
                            $file_path,
                            $this->sugarsync_nb_part,
                            $this->total_nb_part
                        );

                        if ($resume_upload_file === false) {
                            return false;
                        }
                    }
                    ++$this->sugarsync_nb_part;
                    // New part, so back to init values
                    $this->next_step = $this->step_send['sugarsync'];
                    $this->sugarsync_position = 0;
                }

                ++$nb_part;
            }

            $this->next_step = $this->step_send['sugarsync_resume'];

            if ($this->config->send_restore) {
                $this->log(
                    sprintf($this->l('Sending restore file to %s account...', self::PAGE), self::SUGARSYNC)
                );

                $file_destination = $sugarsync->directory_path . self::NEW_RESTORE_NAME;

                $this->log(
                    $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                );

                $id_file = $sugarsync_lib->checkExists($sugarsync_dir, self::NEW_RESTORE_NAME);

                if ($id_file !== false) {
                    $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                    if (!$sugarsync_lib->deleteFile($id_file)) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                    }
                }

                // Upload the file
                if ($sugarsync_lib->uploadFile(self::NEW_RESTORE_NAME, $this->restore_file, $sugarsync_dir) === false) {
                    return false;
                }
            }

            return true;
        } else {
            $this->log(
                'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SUGARSYNC) . ' '
                . $this->l('Connection imposible', self::PAGE)
            );

            return false;
        }

        return false;
    }

    /**
     * Send a file on a ownCloud/Nextcloud account
     *
     * @return bool The success or failure of the operation
     */
    protected function sendFileToOwncloud()
    {
        $owncloud = new OwncloudNtbr($this->owncloud_account_id);
        $owncloud_server = $owncloud->server;
        $owncloud_user = $owncloud->login;
        $owncloud_pass = $this->decrypt($owncloud->password);
        $owncloud_dir = $owncloud->directory;

        if ($this->next_step == $this->step_send['owncloud']
            && (!isset($this->owncloud_nb_part) || $this->owncloud_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::OWNCLOUD));
        }

        $owncloud_lib = $this->connectToOwncloud($owncloud_server, $owncloud_user, $owncloud_pass);

        if (!$owncloud_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::OWNCLOUD)
            );

            return false;
        }

        // ownCloud dir should end with a "/" except when testing if exist
        if (Tools::substr($owncloud_dir, -1) != '/') {
            $owncloud_dir .= '/';
        }

        if ($this->next_step == $this->step_send['owncloud']
            && (!isset($this->owncloud_nb_part) || $this->owncloud_nb_part == 1)
        ) {
            // If file already on ownCloud
            foreach ($this->part_list as $part) {
                $file_destination = $owncloud_dir . basename($part);

                $this->log(
                    $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                );

                if ($owncloud_lib->fileExists($file_destination)) {
                    $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                    if ($owncloud_lib->deleteFile($file_destination) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);
                    }
                }
            }

            // Get old backups list
            $old_backups = $this->getOwncloudFiles($owncloud_lib, $owncloud->directory);
            $nb_backup_to_keep = $owncloud->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            // Get available size
            $available_space = $owncloud_lib->getAvailableQuota();

            if ($available_space >= 0) { // Negative value probably mean unlimitted space
                $available_space += $size_old_backups;

                // Get size to upload
                $to_send_size = $this->total_size;

                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);
                    $to_send_size += $total_file_size;
                }

                // Check if we will have enough size after deleting old backup, too add new backup
                if ($available_space < $to_send_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::OWNCLOUD) . ' '
                        . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                    );

                    return false;
                }
            }

            // Delete old backup
            if (!$this->deleteOwncloudOldBackup($owncloud_lib, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::OWNCLOUD)
                    . ' ' . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            // Check available space
            $new_available_space = $owncloud_lib->getAvailableQuota();

            if ($new_available_space >= 0) { // Negative value probably mean unlimitted space
                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);
                    $new_available_space -= $total_file_size;
                }

                if ($new_available_space <= $this->total_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::OWNCLOUD)
                        . ' ' . $this->l('Not enough space available', self::PAGE)
                    );

                    return false;
                }
            }

            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::OWNCLOUD) . ' '
                . $this->l('Check the path of your backup', self::PAGE)
            );

            // Check if the folder we want to use exists. If not we create it.
            if ($owncloud_lib->folderExists($owncloud->directory) === false) {
                $this->log(
                    sprintf($this->l('Sending backup to %s account:', self::PAGE), self::OWNCLOUD)
                    . ' ' . $this->l('Create the directory', self::PAGE) . ' "' . $owncloud->directory . '"'
                );

                if ($owncloud_lib->createFolder($owncloud->directory) === false) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::OWNCLOUD) . ' '
                        . $this->l('Error while creating the directory', self::PAGE) . ' "' . $owncloud->directory . '"'
                    );

                    return false;
                }
            }

            $this->log(sprintf($this->l('Sending backup to %s account...', self::PAGE), self::OWNCLOUD));

            $this->owncloud_session = rand();
            $this->owncloud_nb_part = 1;
            $this->owncloud_nb_chunk = 0;
            $this->owncloud_position = 0;
        }

        $nb_part = 1;

        // Upload the file
        foreach ($this->part_list as $part) {
            if ($nb_part == $this->owncloud_nb_part) {
                $file_name = basename($part);
                $file_path = $part;

                if ($this->next_step == $this->step_send['owncloud']) {
                    $this->next_step = $this->step_send['owncloud_resume'];
                }

                // Upload the file
                $upload_file = $owncloud_lib->uploadFile(
                    $file_path,
                    $owncloud_dir,
                    $file_name,
                    $this->owncloud_position,
                    $this->owncloud_nb_part,
                    $this->total_nb_part
                );

                if ($upload_file === false) {
                    return false;
                }

                ++$this->owncloud_nb_part;
                // New part, so back to init values
                $this->next_step = $this->step_send['owncloud'];
                $this->owncloud_position = 0;
                $this->owncloud_nb_chunk = 0;
                $this->owncloud_session = rand();
            }
            ++$nb_part;
        }

        $this->next_step = $this->step_send['owncloud_resume'];

        if ($this->config->send_restore) {
            $this->log(
                sprintf($this->l('Sending restore file to %s account...', self::PAGE), self::OWNCLOUD)
            );

            // Upload the file
            if ($owncloud_lib->uploadFile($this->restore_file, $owncloud_dir, self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a tar file on a ownCloud/Nextcloud account
     *
     * @return bool The success or failure of the operation
     */
    protected function createTarOnOwncloud()
    {
        $owncloud = new OwncloudNtbr($this->owncloud_account_id);
        $owncloud_server = $owncloud->server;
        $owncloud_user = $owncloud->login;
        $owncloud_pass = $this->decrypt($owncloud->password);
        $owncloud_dir = $owncloud->directory;
        $tar_end_size = self::TAR_END_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? (SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES * 2) : 0);

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['owncloud']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->owncloud_nb_part) || $this->owncloud_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::OWNCLOUD));

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
            $this->files_done = 0;
            $this->position_file_list_file = 0;
            $this->part_number = 1;
            $this->old_percent = 0;
            $tar_name = basename($this->part_list[$this->part_number - 1]);

            if (count($this->part_list) != count($this->tar_files_size)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD) . ' '
                    . $this->l('The backup cannot be created', self::PAGE)
                );

                return false;
            }

            if (!isset($this->tar_files_size[$this->part_number]) || !$this->tar_files_size[$this->part_number]) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD) . ' '
                    . sprintf($this->l('The calculated size of the file %s is not valid', self::PAGE), $tar_name)
                );

                return false;
            }
        }

        $this->checkStopScript();

        $owncloud_lib = $this->connectToOwncloud($owncloud_server, $owncloud_user, $owncloud_pass);

        if (!$owncloud_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::OWNCLOUD)
            );

            return false;
        }

        $this->checkStopScript();

        // ownCloud dir should end with a "/" except when testing if exist
        if (Tools::substr($owncloud_dir, -1) != '/') {
            $owncloud_dir .= '/';
        }

        if ($this->next_step == $this->step_send['owncloud']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->owncloud_nb_part) || $this->owncloud_nb_part == 1)
        ) {
            // Get old backups list
            $old_backups = $this->getOwncloudFiles($owncloud_lib, $owncloud->directory);
            $nb_backup_to_keep = $owncloud->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            $this->checkStopScript();

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    $this->checkStopScript();

                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            $this->checkStopScript();

            // Get available size
            $available_space = $owncloud_lib->getAvailableQuota();

            $this->checkStopScript();

            if ($available_space >= 0) { // Negative value probably mean unlimitted space
                $available_space += $size_old_backups;

                // Get size to upload
                $to_send_size = $this->total_size;

                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);

                    $this->checkStopScript();

                    $to_send_size += $total_file_size;
                }

                // Check if we will have enough size after deleting old backup, too add new backup
                if ($available_space < $to_send_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD) . ' '
                        . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                    );

                    return false;
                }
            }

            $this->checkStopScript();

            // Delete old backup
            if (!$this->deleteOwncloudOldBackup($owncloud_lib, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD)
                    . ' ' . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            $this->checkStopScript();

            // Check available space
            $new_available_space = $owncloud_lib->getAvailableQuota();

            $this->checkStopScript();

            if ($new_available_space >= 0) { // Negative value probably mean unlimitted space
                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);

                    $this->checkStopScript();

                    $new_available_space -= $total_file_size;
                }

                if ($new_available_space <= $this->total_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD)
                        . ' ' . $this->l('Not enough space available', self::PAGE)
                    );

                    return false;
                }
            }

            $this->log(
                sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD) . ' '
                . $this->l('Check the path of your backup', self::PAGE)
            );

            // Check if the folder we want to use exists. If not we create it.
            if ($owncloud_lib->folderExists($owncloud->directory) === false) {
                $this->log(
                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD)
                    . ' ' . $this->l('Create the directory', self::PAGE) . ' "' . $owncloud->directory . '"'
                );

                $this->checkStopScript();

                if ($owncloud_lib->createFolder($owncloud->directory) === false) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD) . ' '
                        . $this->l('Error while creating the directory', self::PAGE) . ' "' . $owncloud->directory . '"'
                    );

                    return false;
                }
            }

            $this->checkStopScript();

            $this->log(sprintf($this->l('Creating backup on %s account...', self::PAGE), self::OWNCLOUD));

            $this->owncloud_nb_part = 1;
            $this->owncloud_position = 0;
            $this->distant_tar_content = '';
            $this->distant_tar_content_size = 0;

            $this->distant_tar_content = $this->createTarStart();
            $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
        }

        $this->checkStopScript();

        if ($this->getFileSize($this->file_list_file) <= 0) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD)
                . ' ' . $this->l('No file to backup', self::PAGE)
            );

            return false;
        }

        $this->checkStopScript();

        if (!is_resource($this->handle_file_list_file)) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD)
                . ' ' . $this->l('Error while getting the files to backup', self::PAGE)
            );

            return false;
        }

        $this->handle_file_list_file = $this->goToPositionInFile(
            $this->handle_file_list_file,
            $this->position_file_list_file,
            false
        );

        $this->checkStopScript();

        while (!feof($this->handle_file_list_file)) {
            $this->checkStopScript();

            $line = rtrim(fgets($this->handle_file_list_file));

            if (!$line) {
                continue;
            }

            $pos_cut = strrpos($line, ':');

            if ($pos_cut === false) {
                continue;
            }

            $current_file = self::binaryToString(Tools::substr($line, 0, $pos_cut));

            if ($current_file == '') {
                continue;
            }

            $this->checkStopScript();

            $found_size = Tools::substr($line, $pos_cut + 1);
            $tar_name = basename($this->part_list[$this->part_number - 1]);
            $path_in_tar = '';

            // Normalize path
            $current_normalized_file = $this->normalizePath($current_file);

            // Find relative filename
            if (!$path_in_tar) {
                $filename = ltrim(self::getPart($current_normalized_file, $this->base_length), '/');
            } else {
                $filename = ltrim($path_in_tar, '/');
            }

            // File information
            $info = $this->tarFileInfo($current_file);
            $diff_size = 0;

            if ($info['size'] != $found_size) {
                if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD)
                        . ' ' . sprintf(
                            $this->l('The file %1$s has changed of size from %2$s to %3$s', self::PAGE),
                            $current_file,
                            $this->readableSize($found_size) . ' (' . $found_size . ')',
                            $this->readableSize($info['size']) . ' (' . $info['size'] . ')'
                        )
                    );
                }

                $diff_size = $info['size'] - $found_size;
                $info['size'] = $found_size;
            }

            if (in_array($current_file, $this->list_dump_files) && $this->config->type_backup == $this->type_backup_complete) {
                $path_module_backup = 'modules/' . $this->name . '/' . self::BACKUP_FOLDER . '/';
                $path_in_tar = str_replace($this->config_backup_dir, $path_module_backup, $current_file);
            }

            $this->checkStopScript();

            // Create file header
            if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                // Start to add a new file to the tar
                $header = $this->createTarHeader($filename, $info);

                $this->distant_tar_content_size += $this->getHeaderFileSize($filename, false);
                $this->distant_tar_content .= $header;

                ++$this->files_done;

                $this->pos_file_to_tar = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE_CONTINUE;
            }

            if ($info['size'] > 0) {
                // Open the file
                if (($file_read = @fopen($current_file, 'rb')) === false) {
                    // If it is the first opening of the file
                    if ($this->pos_file_to_tar == 0) {
                        $this->log(
                            'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD)
                            . ' ' . $this->l('File', self::PAGE) . ' ' . $current_file . ' '
                            . $this->l('will be empty because the module can not open it, please check its rights and user owner', self::PAGE)
                        );
                    }
                }

                $this->checkStopScript();

                if (is_resource($file_read) && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE_CONTINUE) {
                    $file_read = $this->goToPositionInFile($file_read, $this->pos_file_to_tar);

                    if ($file_read === false) {
                        return false;
                    }
                }

                // Data of the file
                $leftsize = $info['size'] - $this->pos_file_to_tar;
                $blocksize = self::TAR_BLOCK_SIZE;

                while ($leftsize > 0) {
                    $this->checkStopScript();

                    if ($this->next_step == $this->step_send['owncloud']) {
                        // Create new resumable session
                        $this->owncloud_session = rand();
                        $this->owncloud_nb_chunk = 0;
                        $this->owncloud_position = 0;
                        $this->next_step = $this->step_send['owncloud_resume'];
                    }

                    // If max size for ownCloud/Nextcloud has been reach,
                    // we send the content or the size of the tar was reach
                    if ($this->distant_tar_content_size >= OwncloudLib::MAX_CONTENT_UPLOAD_SIZE
                        || ($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                    ) {
                        $end_of_current_part = 0;

                        if (($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]) {
                            $this->distant_tar_content_size += $tar_end_size;
                            $this->distant_tar_content .= $this->createTarEnd();
                            $end_of_current_part = 1;
                        }

                        if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                            $this->log(
                                $this->l('The total size should not be less that the size we are now at', self::PAGE)
                                . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                            );
                        }

                        // Send content using the current session
                        $resume_upload_content = $owncloud_lib->uploadContent(
                            $this->distant_tar_content,
                            $this->distant_tar_content_size,
                            $this->tar_files_size[$this->part_number],
                            $tar_name,
                            $owncloud_dir
                        );

                        if ($resume_upload_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be completed', self::PAGE)
                            );

                            return false;
                        } else {
                            $this->owncloud_position += $this->distant_tar_content_size;

                            $this->distant_tar_content = '';
                            $this->distant_tar_content_size = 0;
                        }

                        if ($end_of_current_part) {
                            // New part, so back to init values
                            ++$this->owncloud_nb_part;
                            ++$this->part_number;

                            $this->next_step = $this->step_send['owncloud'];
                            $this->owncloud_position = 0;
                            $this->old_percent = 0;
                            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;

                            $this->distant_tar_content = $this->createTarStart();
                            $this->distant_tar_content_size += (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
                        }
                    } else {
                        // Read data
                        $leftsize -= $blocksize;

                        if ($leftsize < 0) {
                            $blocksize += $leftsize;
                        }

                        $temp_content = '';

                        if (is_resource($file_read)) {
                            $temp_content = $this->createTarContent($file_read, $blocksize);
                        }

                        // Get where we are in the file
                        $this->pos_file_to_tar += $blocksize;

                        // If there is no content because the file size has changed (smaller file) we need to add \0
                        if ($diff_size < 0 && !$temp_content) {
                            // self::TAR_BLOCK_SIZE since createTarContent
                            // return self::TAR_BLOCK_SIZE size block (using self::pad)
                            $temp_content = self::pad('', self::TAR_BLOCK_SIZE);
                            $diff_size += self::TAR_BLOCK_SIZE;

                            if ($this->config->crypt_backup && $this->config->ignore_compression) {
                                $temp_content = $this->cryptBackup($temp_content);
                                $diff_size += SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;
                            }
                        } elseif ($temp_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD)
                                . ' ' . $this->l('The module was unable to read the file', self::PAGE)
                                . ' ' . $current_file . ', ' . $this->l('please check its rights and user owner', self::PAGE)
                            );

                            if (is_resource($file_read)) {
                                fclose($file_read);
                            }

                            return false;
                        }

                        $this->distant_tar_content .= $temp_content;
                        $this->distant_tar_content_size += self::TAR_BLOCK_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES : 0);

                        $percent = (($this->distant_tar_content_size + $this->owncloud_position) / $this->tar_files_size[$this->part_number]) * 100;

                        if ($percent >= $this->old_percent + 1) {
                            $this->old_percent = round($percent, 0);

                            if ($this->total_nb_part > 1) {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD)
                                    . ' ' . $this->part_number . '/' . $this->total_nb_part . $this->l(':', self::PAGE)
                                    . ' ' . (int) $percent . '%'
                                );
                            } else {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD)
                                    . ' ' . (int) $percent . '%'
                                );
                            }
                        }
                    }

                    // refresh
                    $this->refreshBackup(true);
                }

                // Close file
                if (is_resource($file_read)) {
                    fclose($file_read);
                }
            }

            /*if (basename($filename) == self::TEMP_EMPTY_FILE) {
                @unlink($current_file);
            }*/

            $this->position_file_list_file = ftell($this->handle_file_list_file);

            $this->checkStopScript();

            // Check if tar file size has reach its predicted size
            if (isset($this->tar_files_size[$this->part_number])
                && ($this->owncloud_position + $this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                && $this->owncloud_position < $this->tar_files_size[$this->part_number]
            ) {
                // The tar file will be too big, we need to close it and use a new one
                $this->distant_tar_content .= $this->createTarEnd();
                $this->distant_tar_content_size += $tar_end_size;

                if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                    $this->log(
                        $this->l('The total size should not be less that the size we are now at', self::PAGE)
                        . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                    );
                }

                // Finish sending current tar
                $resume_upload_content = $owncloud_lib->uploadContent(
                    $this->distant_tar_content,
                    $this->distant_tar_content_size,
                    $this->tar_files_size[$this->part_number],
                    $tar_name,
                    $owncloud_dir
                );

                if ($resume_upload_content === false) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD)
                        . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                        . ' ' . $this->l('cannot be finished', self::PAGE)
                    );

                    return false;
                }

                // New part, so back to init values
                ++$this->owncloud_nb_part;
                ++$this->part_number;

                $this->next_step = $this->step_send['owncloud'];
                $this->owncloud_position = 0;
                $this->old_percent = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                $this->distant_tar_content = '';
                $this->distant_tar_content_size = 0;

                $this->distant_tar_content = $this->createTarStart();
                $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);

                // refresh
                $this->refreshBackup(true);
            }
            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
        }

        // Check that there is no missing file
        if ($this->files_done < $this->total_files) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::OWNCLOUD)
                . $this->l('Be careful! Not all of your files have been added to the backup. Number of files backuped:', self::PAGE)
                . ' ' . $this->files_done . '/' . $this->total_files
            );

            return false;
        }

        $this->files_done = 0;
        $this->pos_file_to_tar = 0;

        $this->next_step = $this->step_send['owncloud_resume'];

        if ($this->config->send_restore) {
            $this->log(
                sprintf($this->l('Creating restore file on %s account...', self::PAGE), self::OWNCLOUD)
            );
            // Upload the file
            if ($owncloud_lib->uploadFile($this->restore_file, $owncloud_dir, self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Send a file on a Shadow Drive account
     *
     * @return bool The success or failure of the operation
     */
    protected function sendFileToShadowDrive()
    {
        $shadow_drive = new ShadowDriveNtbr($this->shadow_drive_account_id);
        $shadow_drive_server = $shadow_drive->server;
        $shadow_drive_user = $shadow_drive->login;
        $shadow_drive_pass = $this->decrypt($shadow_drive->password);
        $shadow_drive_dir = $shadow_drive->directory;

        if ($this->next_step == $this->step_send['shadow_drive']
            && (!isset($this->shadow_drive_nb_part) || $this->shadow_drive_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::SHADOW_DRIVE));
        }

        $shadow_drive_lib = $this->connectToShadowDrive($shadow_drive_server, $shadow_drive_user, $shadow_drive_pass);

        if (!$shadow_drive_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::SHADOW_DRIVE)
            );

            return false;
        }

        // Shadow Drive dir should end with a "/" except when testing if exist
        if (Tools::substr($shadow_drive_dir, -1) != '/') {
            $shadow_drive_dir .= '/';
        }

        if ($this->next_step == $this->step_send['shadow_drive']
            && (!isset($this->shadow_drive_nb_part) || $this->shadow_drive_nb_part == 1)
        ) {
            // If file already on Shadow Drive
            foreach ($this->part_list as $part) {
                $file_destination = $shadow_drive_dir . basename($part);

                $this->log(
                    $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                );

                if ($shadow_drive_lib->fileExists($file_destination)) {
                    $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                    if ($shadow_drive_lib->deleteFile($file_destination) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);
                    }
                }
            }

            // Get old backups list
            $old_backups = $this->getShadowDriveFiles($shadow_drive_lib, $shadow_drive->directory);
            $nb_backup_to_keep = $shadow_drive->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            // Get available size
            $available_space = $shadow_drive_lib->getAvailableQuota();

            if ($available_space >= 0) { // Negative value probably mean unlimitted space
                $available_space += $size_old_backups;

                // Get size to upload
                $to_send_size = $this->total_size;

                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);
                    $to_send_size += $total_file_size;
                }

                // Check if we will have enough size after deleting old backup, too add new backup
                if ($available_space < $to_send_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SHADOW_DRIVE) . ' '
                        . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                    );

                    return false;
                }
            }

            // Delete old backup
            if (!$this->deleteShadowDriveOldBackup($shadow_drive_lib, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SHADOW_DRIVE)
                    . ' ' . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            // Check available space
            $new_available_space = $shadow_drive_lib->getAvailableQuota();

            if ($new_available_space >= 0) { // Negative value probably mean unlimitted space
                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);
                    $new_available_space -= $total_file_size;
                }

                if ($new_available_space <= $this->total_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SHADOW_DRIVE)
                        . ' ' . $this->l('Not enough space available', self::PAGE)
                    );

                    return false;
                }
            }

            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SHADOW_DRIVE) . ' '
                . $this->l('Check the path of your backup', self::PAGE)
            );

            // Check if the folder we want to use exists. If not we create it.
            if ($shadow_drive_lib->folderExists($shadow_drive->directory) === false) {
                $this->log(
                    sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SHADOW_DRIVE)
                    . ' ' . $this->l('Create the directory', self::PAGE) . ' "' . $shadow_drive->directory . '"'
                );

                if ($shadow_drive_lib->createFolder($shadow_drive->directory) === false) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SHADOW_DRIVE) . ' '
                        . $this->l('Error while creating the directory', self::PAGE) . ' "' . $shadow_drive->directory . '"'
                    );

                    return false;
                }
            }

            $this->log(sprintf($this->l('Sending backup to %s account...', self::PAGE), self::SHADOW_DRIVE));

            $this->shadow_drive_session = rand();
            $this->shadow_drive_nb_part = 1;
            $this->shadow_drive_nb_chunk = 0;
            $this->shadow_drive_position = 0;
        }

        $nb_part = 1;

        // Upload the file
        foreach ($this->part_list as $part) {
            if ($nb_part == $this->shadow_drive_nb_part) {
                $file_name = basename($part);
                $file_path = $part;

                if ($this->next_step == $this->step_send['shadow_drive']) {
                    $this->next_step = $this->step_send['shadow_drive_resume'];
                }

                // Upload the file
                $upload_file = $shadow_drive_lib->uploadFile(
                    $file_path,
                    $shadow_drive_dir,
                    $file_name,
                    $this->shadow_drive_position,
                    $this->shadow_drive_nb_part,
                    $this->total_nb_part
                );

                if ($upload_file === false) {
                    return false;
                }

                ++$this->shadow_drive_nb_part;
                // New part, so back to init values
                $this->next_step = $this->step_send['shadow_drive'];
                $this->shadow_drive_position = 0;
                $this->shadow_drive_nb_chunk = 0;
                $this->shadow_drive_session = rand();
            }
            ++$nb_part;
        }

        $this->next_step = $this->step_send['shadow_drive_resume'];

        if ($this->config->send_restore) {
            $this->log(
                sprintf($this->l('Sending restore file to %s account...', self::PAGE), self::SHADOW_DRIVE)
            );

            // Upload the file
            if ($shadow_drive_lib->uploadFile($this->restore_file, $shadow_drive_dir, self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a tar file on a Shadow Drive account
     *
     * @return bool The success or failure of the operation
     */
    protected function createTarOnShadowDrive()
    {
        $shadow_drive = new ShadowDriveNtbr($this->shadow_drive_account_id);
        $shadow_drive_server = $shadow_drive->server;
        $shadow_drive_user = $shadow_drive->login;
        $shadow_drive_pass = $this->decrypt($shadow_drive->password);
        $shadow_drive_dir = $shadow_drive->directory;
        $tar_end_size = self::TAR_END_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? (SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES * 2) : 0);

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['shadow_drive']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->shadow_drive_nb_part) || $this->shadow_drive_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::SHADOW_DRIVE));

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
            $this->files_done = 0;
            $this->position_file_list_file = 0;
            $this->part_number = 1;
            $this->old_percent = 0;
            $tar_name = basename($this->part_list[$this->part_number - 1]);

            if (count($this->part_list) != count($this->tar_files_size)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE) . ' '
                    . $this->l('The backup cannot be created', self::PAGE)
                );

                return false;
            }

            if (!isset($this->tar_files_size[$this->part_number]) || !$this->tar_files_size[$this->part_number]) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE) . ' '
                    . sprintf($this->l('The calculated size of the file %s is not valid', self::PAGE), $tar_name)
                );

                return false;
            }
        }

        $this->checkStopScript();

        $shadow_drive_lib = $this->connectToShadowDrive($shadow_drive_server, $shadow_drive_user, $shadow_drive_pass);

        if (!$shadow_drive_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::SHADOW_DRIVE)
            );

            return false;
        }

        $this->checkStopScript();

        // Shadow Drive dir should end with a "/" except when testing if exist
        if (Tools::substr($shadow_drive_dir, -1) != '/') {
            $shadow_drive_dir .= '/';
        }

        if ($this->next_step == $this->step_send['shadow_drive']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->shadow_drive_nb_part) || $this->shadow_drive_nb_part == 1)
        ) {
            // Get old backups list
            $old_backups = $this->getShadowDriveFiles($shadow_drive_lib, $shadow_drive->directory);
            $nb_backup_to_keep = $shadow_drive->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            $this->checkStopScript();

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    $this->checkStopScript();

                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            $this->checkStopScript();

            // Get available size
            $available_space = $shadow_drive_lib->getAvailableQuota();

            $this->checkStopScript();

            if ($available_space >= 0) { // Negative value probably mean unlimitted space
                $available_space += $size_old_backups;

                // Get size to upload
                $to_send_size = $this->total_size;

                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);

                    $this->checkStopScript();

                    $to_send_size += $total_file_size;
                }

                // Check if we will have enough size after deleting old backup, too add new backup
                if ($available_space < $to_send_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE) . ' '
                        . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                    );

                    return false;
                }
            }

            $this->checkStopScript();

            // Delete old backup
            if (!$this->deleteShadowDriveOldBackup($shadow_drive_lib, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE)
                    . ' ' . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            $this->checkStopScript();

            // Check available space
            $new_available_space = $shadow_drive_lib->getAvailableQuota();

            $this->checkStopScript();

            if ($new_available_space >= 0) { // Negative value probably mean unlimitted space
                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);

                    $this->checkStopScript();

                    $new_available_space -= $total_file_size;
                }

                if ($new_available_space <= $this->total_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE)
                        . ' ' . $this->l('Not enough space available', self::PAGE)
                    );

                    return false;
                }
            }

            $this->log(
                sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE) . ' '
                . $this->l('Check the path of your backup', self::PAGE)
            );

            // Check if the folder we want to use exists. If not we create it.
            if ($shadow_drive_lib->folderExists($shadow_drive->directory) === false) {
                $this->log(
                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE)
                    . ' ' . $this->l('Create the directory', self::PAGE) . ' "' . $shadow_drive->directory . '"'
                );

                $this->checkStopScript();

                if ($shadow_drive_lib->createFolder($shadow_drive->directory) === false) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE) . ' '
                        . $this->l('Error while creating the directory', self::PAGE) . ' "' . $shadow_drive->directory . '"'
                    );

                    return false;
                }
            }

            $this->checkStopScript();

            $this->log(sprintf($this->l('Creating backup on %s account...', self::PAGE), self::SHADOW_DRIVE));

            $this->shadow_drive_nb_part = 1;
            $this->shadow_drive_position = 0;
            $this->distant_tar_content = '';
            $this->distant_tar_content_size = 0;

            $this->distant_tar_content = $this->createTarStart();
            $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
        }

        $this->checkStopScript();

        if ($this->getFileSize($this->file_list_file) <= 0) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE)
                . ' ' . $this->l('No file to backup', self::PAGE)
            );

            return false;
        }

        $this->checkStopScript();

        if (!is_resource($this->handle_file_list_file)) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE)
                . ' ' . $this->l('Error while getting the files to backup', self::PAGE)
            );

            return false;
        }

        $this->handle_file_list_file = $this->goToPositionInFile(
            $this->handle_file_list_file,
            $this->position_file_list_file,
            false
        );

        $this->checkStopScript();

        while (!feof($this->handle_file_list_file)) {
            $this->checkStopScript();

            $line = rtrim(fgets($this->handle_file_list_file));

            if (!$line) {
                continue;
            }

            $pos_cut = strrpos($line, ':');

            if ($pos_cut === false) {
                continue;
            }

            $current_file = self::binaryToString(Tools::substr($line, 0, $pos_cut));

            if ($current_file == '') {
                continue;
            }

            $this->checkStopScript();

            $found_size = Tools::substr($line, $pos_cut + 1);
            $tar_name = basename($this->part_list[$this->part_number - 1]);
            $path_in_tar = '';

            // Normalize path
            $current_normalized_file = $this->normalizePath($current_file);

            // Find relative filename
            if (!$path_in_tar) {
                $filename = ltrim(self::getPart($current_normalized_file, $this->base_length), '/');
            } else {
                $filename = ltrim($path_in_tar, '/');
            }

            // File information
            $info = $this->tarFileInfo($current_file);
            $diff_size = 0;

            if ($info['size'] != $found_size) {
                if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE)
                        . ' ' . sprintf(
                            $this->l('The file %1$s has changed of size from %2$s to %3$s', self::PAGE),
                            $current_file,
                            $this->readableSize($found_size) . ' (' . $found_size . ')',
                            $this->readableSize($info['size']) . ' (' . $info['size'] . ')'
                        )
                    );
                }

                $diff_size = $info['size'] - $found_size;
                $info['size'] = $found_size;
            }

            if (in_array($current_file, $this->list_dump_files) && $this->config->type_backup == $this->type_backup_complete) {
                $path_module_backup = 'modules/' . $this->name . '/' . self::BACKUP_FOLDER . '/';
                $path_in_tar = str_replace($this->config_backup_dir, $path_module_backup, $current_file);
            }

            $this->checkStopScript();

            // Create file header
            if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                // Start to add a new file to the tar
                $header = $this->createTarHeader($filename, $info);

                $this->distant_tar_content_size += $this->getHeaderFileSize($filename, false);
                $this->distant_tar_content .= $header;

                ++$this->files_done;

                $this->pos_file_to_tar = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE_CONTINUE;
            }

            if ($info['size'] > 0) {
                // Open the file
                if (($file_read = @fopen($current_file, 'rb')) === false) {
                    // If it is the first opening of the file
                    if ($this->pos_file_to_tar == 0) {
                        $this->log(
                            'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE)
                            . ' ' . $this->l('File', self::PAGE) . ' ' . $current_file . ' '
                            . $this->l('will be empty because the module can not open it, please check its rights and user owner', self::PAGE)
                        );
                    }
                }

                $this->checkStopScript();

                if (is_resource($file_read) && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE_CONTINUE) {
                    $file_read = $this->goToPositionInFile($file_read, $this->pos_file_to_tar);

                    if ($file_read === false) {
                        return false;
                    }
                }

                // Data of the file
                $leftsize = $info['size'] - $this->pos_file_to_tar;
                $blocksize = self::TAR_BLOCK_SIZE;

                while ($leftsize > 0) {
                    $this->checkStopScript();

                    if ($this->next_step == $this->step_send['shadow_drive']) {
                        // Create new resumable session
                        $this->shadow_drive_session = rand();
                        $this->shadow_drive_nb_chunk = 0;
                        $this->shadow_drive_position = 0;
                        $this->next_step = $this->step_send['shadow_drive_resume'];
                    }

                    // If max size for Shadow Drive has been reach,
                    // we send the content or the size of the tar was reach
                    if ($this->distant_tar_content_size >= ShadowDriveLib::MAX_CONTENT_UPLOAD_SIZE
                        || ($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                    ) {
                        $end_of_current_part = 0;

                        if (($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]) {
                            $this->distant_tar_content_size += $tar_end_size;
                            $this->distant_tar_content .= $this->createTarEnd();
                            $end_of_current_part = 1;
                        }

                        if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                            $this->log(
                                $this->l('The total size should not be less that the size we are now at', self::PAGE)
                                . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                            );
                        }

                        // Send content using the current session
                        $resume_upload_content = $shadow_drive_lib->uploadContent(
                            $this->distant_tar_content,
                            $this->distant_tar_content_size,
                            $this->tar_files_size[$this->part_number],
                            $tar_name,
                            $shadow_drive_dir
                        );

                        if ($resume_upload_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be completed', self::PAGE)
                            );

                            return false;
                        } else {
                            $this->shadow_drive_position += $this->distant_tar_content_size;

                            $this->distant_tar_content = '';
                            $this->distant_tar_content_size = 0;
                        }

                        if ($end_of_current_part) {
                            // New part, so back to init values
                            ++$this->shadow_drive_nb_part;
                            ++$this->part_number;

                            $this->next_step = $this->step_send['shadow_drive'];
                            $this->shadow_drive_position = 0;
                            $this->old_percent = 0;
                            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;

                            $this->distant_tar_content = $this->createTarStart();
                            $this->distant_tar_content_size += (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
                        }
                    } else {
                        // Read data
                        $leftsize -= $blocksize;

                        if ($leftsize < 0) {
                            $blocksize += $leftsize;
                        }

                        $temp_content = '';

                        if (is_resource($file_read)) {
                            $temp_content = $this->createTarContent($file_read, $blocksize);
                        }

                        // Get where we are in the file
                        $this->pos_file_to_tar += $blocksize;

                        // If there is no content because the file size has changed (smaller file) we need to add \0
                        if ($diff_size < 0 && !$temp_content) {
                            // self::TAR_BLOCK_SIZE since createTarContent
                            // return self::TAR_BLOCK_SIZE size block (using self::pad)
                            $temp_content = self::pad('', self::TAR_BLOCK_SIZE);
                            $diff_size += self::TAR_BLOCK_SIZE;

                            if ($this->config->crypt_backup && $this->config->ignore_compression) {
                                $temp_content = $this->cryptBackup($temp_content);
                                $diff_size += SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;
                            }
                        } elseif ($temp_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE)
                                . ' ' . $this->l('The module was unable to read the file', self::PAGE)
                                . ' ' . $current_file . ', ' . $this->l('please check its rights and user owner', self::PAGE)
                            );

                            if (is_resource($file_read)) {
                                fclose($file_read);
                            }

                            return false;
                        }

                        $this->distant_tar_content .= $temp_content;
                        $this->distant_tar_content_size += self::TAR_BLOCK_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES : 0);

                        $percent = (($this->distant_tar_content_size + $this->shadow_drive_position) / $this->tar_files_size[$this->part_number]) * 100;

                        if ($percent >= $this->old_percent + 1) {
                            $this->old_percent = round($percent, 0);

                            if ($this->total_nb_part > 1) {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE)
                                    . ' ' . $this->part_number . '/' . $this->total_nb_part . $this->l(':', self::PAGE)
                                    . ' ' . (int) $percent . '%'
                                );
                            } else {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE)
                                    . ' ' . (int) $percent . '%'
                                );
                            }
                        }
                    }

                    // refresh
                    $this->refreshBackup(true);
                }

                // Close file
                if (is_resource($file_read)) {
                    fclose($file_read);
                }
            }

            /*if (basename($filename) == self::TEMP_EMPTY_FILE) {
                @unlink($current_file);
            }*/

            $this->position_file_list_file = ftell($this->handle_file_list_file);

            $this->checkStopScript();

            // Check if tar file size has reach its predicted size
            if (isset($this->tar_files_size[$this->part_number])
                && ($this->shadow_drive_position + $this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                && $this->shadow_drive_position < $this->tar_files_size[$this->part_number]
            ) {
                // The tar file will be too big, we need to close it and use a new one
                $this->distant_tar_content .= $this->createTarEnd();
                $this->distant_tar_content_size += $tar_end_size;

                if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                    $this->log(
                        $this->l('The total size should not be less that the size we are now at', self::PAGE)
                        . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                    );
                }

                // Finish sending current tar
                $resume_upload_content = $shadow_drive_lib->uploadContent(
                    $this->distant_tar_content,
                    $this->distant_tar_content_size,
                    $this->tar_files_size[$this->part_number],
                    $tar_name,
                    $shadow_drive_dir
                );

                if ($resume_upload_content === false) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE)
                        . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                        . ' ' . $this->l('cannot be finished', self::PAGE)
                    );

                    return false;
                }

                // New part, so back to init values
                ++$this->shadow_drive_nb_part;
                ++$this->part_number;

                $this->next_step = $this->step_send['shadow_drive'];
                $this->shadow_drive_position = 0;
                $this->old_percent = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                $this->distant_tar_content = '';
                $this->distant_tar_content_size = 0;

                $this->distant_tar_content = $this->createTarStart();
                $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);

                // refresh
                $this->refreshBackup(true);
            }
            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
        }

        // Check that there is no missing file
        if ($this->files_done < $this->total_files) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::SHADOW_DRIVE)
                . $this->l('Be careful! Not all of your files have been added to the backup. Number of files backuped:', self::PAGE)
                . ' ' . $this->files_done . '/' . $this->total_files
            );

            return false;
        }

        $this->files_done = 0;
        $this->pos_file_to_tar = 0;

        $this->next_step = $this->step_send['shadow_drive_resume'];

        if ($this->config->send_restore) {
            $this->log(
                sprintf($this->l('Creating restore file on %s account...', self::PAGE), self::SHADOW_DRIVE)
            );
            // Upload the file
            if ($shadow_drive_lib->uploadFile($this->restore_file, $shadow_drive_dir, self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Send a file on a WebDAV account
     *
     * @return bool The success or failure of the operation
     */
    protected function sendFileToWebdav()
    {
        $webdav = new WebdavNtbr($this->webdav_account_id);
        $webdav_server = $webdav->server;
        $webdav_user = $webdav->login;
        $webdav_pass = $this->decrypt($webdav->password);
        $webdav_dir = $webdav->directory;

        if ($this->next_step == $this->step_send['webdav']
            && (!isset($this->webdav_nb_part) || $this->webdav_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::WEBDAV));
        }

        $webdav_lib = $this->connectToWebdav($webdav_server, $webdav_user, $webdav_pass);

        if (!$webdav_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::WEBDAV)
            );

            return false;
        }

        // WebDAV dir should end with a "/" except when testing if exist
        if ($webdav_dir && Tools::substr($webdav_dir, -1) != '/') {
            $webdav_dir .= '/';
        }

        if ($this->next_step == $this->step_send['webdav']
            && (!isset($this->webdav_nb_part) || $this->webdav_nb_part == 1)
        ) {
            // If file already on WebDAV
            foreach ($this->part_list as $part) {
                $file_destination = $webdav_dir . basename($part);

                $this->log(
                    $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                );
                if ($webdav_lib->fileExists($file_destination)) {
                    $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                    if ($webdav_lib->deleteFile($file_destination) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part);
                    }
                }
            }

            // Get old backups list
            $old_backups = $this->getWebdavFiles($webdav_lib, $webdav->directory);
            $nb_backup_to_keep = $webdav->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            // Get available size
            $available_space = $webdav_lib->getAvailableQuota();

            if ($available_space != -1) {
                $available_space += $size_old_backups;

                // Get size to upload
                $to_send_size = $this->total_size;

                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);
                    $to_send_size += $total_file_size;
                }

                // Check if we will have enough size after deleting old backup, too add new backup
                if ($available_space < $to_send_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::WEBDAV) . ' '
                        . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                    );

                    return false;
                }
            }

            // Delete old backup
            if (!$this->deleteWebdavOldBackup($webdav_lib, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::WEBDAV) . ' '
                    . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            // Check available space
            $new_available_space = $webdav_lib->getAvailableQuota();

            if ($new_available_space != -1) {
                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);
                    $new_available_space -= $total_file_size;
                }

                if ($new_available_space <= $this->total_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::WEBDAV) . ' '
                        . $this->l('Not enough space available', self::PAGE)
                    );

                    return false;
                }
            }

            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::WEBDAV) . ' '
                . $this->l('Check the path of your backup', self::PAGE)
            );

            // Check if the folder we want to use exists. If not we create it.
            if ($webdav_lib->folderExists($webdav_dir) === false) {
                $this->log(
                    sprintf($this->l('Sending backup to %s account:', self::PAGE), self::WEBDAV)
                    . ' ' . $this->l('Create the directory', self::PAGE) . ' "' . $webdav->directory . '"'
                );

                if ($webdav_lib->createFolder($webdav_dir) === false) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::WEBDAV)
                        . ' ' . $this->l('Error while creating the directory', self::PAGE) . ' "' . $webdav->directory . '"'
                    );

                    return false;
                }
            }

            $this->log(sprintf($this->l('Sending backup to %s account...', self::PAGE), self::WEBDAV));

            $this->webdav_session = rand();
            $this->webdav_nb_part = 1;
            $this->webdav_nb_chunk = 0;
            $this->webdav_position = 0;
        }

        $nb_part = 1;

        // Upload the file
        foreach ($this->part_list as $part) {
            if ($nb_part == $this->webdav_nb_part) {
                $file_name = basename($part);
                $file_path = $part;

                if ($this->next_step == $this->step_send['webdav']) {
                    $this->next_step = $this->step_send['webdav_resume'];
                }

                // Upload the file
                $upload_file = $webdav_lib->uploadFile(
                    $file_path,
                    $webdav_dir,
                    $file_name,
                    $this->webdav_position,
                    $this->webdav_nb_part,
                    $this->total_nb_part
                );

                if ($upload_file === false) {
                    return false;
                }

                ++$this->webdav_nb_part;
                // New part, so back to init values
                $this->next_step = $this->step_send['webdav'];
                $this->webdav_position = 0;
                $this->webdav_nb_chunk = 0;
                $this->webdav_session = rand();
            }
            ++$nb_part;
        }

        $this->next_step = $this->step_send['webdav_resume'];

        if ($this->config->send_restore) {
            $this->log(
                sprintf($this->l('Sending restore file to %s account...', self::PAGE), self::WEBDAV)
            );

            // Upload the file
            if ($webdav_lib->uploadFile($this->restore_file, $webdav_dir, self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a tar file on a WebDAV account
     *
     * @return bool The success or failure of the operation
     */
    protected function createTarOnWebdav()
    {
        $webdav = new WebdavNtbr($this->webdav_account_id);
        $webdav_server = $webdav->server;
        $webdav_user = $webdav->login;
        $webdav_pass = $this->decrypt($webdav->password);
        $webdav_dir = $webdav->directory;
        $tar_end_size = self::TAR_END_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? (SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES * 2) : 0);

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['webdav']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->webdav_nb_part) || $this->webdav_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::WEBDAV));

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
            $this->files_done = 0;
            $this->position_file_list_file = 0;
            $this->part_number = 1;
            $this->old_percent = 0;
            $tar_name = basename($this->part_list[$this->part_number - 1]);

            if (count($this->part_list) != count($this->tar_files_size)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV) . ' '
                    . $this->l('The backup cannot be created', self::PAGE)
                );

                return false;
            }

            if (!isset($this->tar_files_size[$this->part_number]) || !$this->tar_files_size[$this->part_number]) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV) . ' '
                    . sprintf($this->l('The calculated size of the file %s is not valid', self::PAGE), $tar_name)
                );

                return false;
            }
        }

        $this->checkStopScript();

        $webdav_lib = $this->connectToWebdav($webdav_server, $webdav_user, $webdav_pass);

        if (!$webdav_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::WEBDAV)
            );

            return false;
        }

        $this->checkStopScript();

        // WebDAV dir should end with a "/" except when testing if exist
        if ($webdav_dir && Tools::substr($webdav_dir, -1) != '/') {
            $webdav_dir .= '/';
        }

        if ($this->next_step == $this->step_send['webdav']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->webdav_nb_part) || $this->webdav_nb_part == 1)
        ) {
            // Get old backups list
            $old_backups = $this->getWebdavFiles($webdav_lib, $webdav->directory);
            $nb_backup_to_keep = $webdav->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            $this->checkStopScript();

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    $this->checkStopScript();

                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            $this->checkStopScript();

            // Get available size
            $available_space = $webdav_lib->getAvailableQuota();

            $this->checkStopScript();

            if ($available_space != -1) {
                $available_space += $size_old_backups;

                // Get size to upload
                $to_send_size = $this->total_size;

                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);

                    $this->checkStopScript();

                    $to_send_size += $total_file_size;
                }

                // Check if we will have enough size after deleting old backup, too add new backup
                if ($available_space < $to_send_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV) . ' '
                        . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                    );

                    return false;
                }
            }

            $this->checkStopScript();

            // Delete old backup
            if (!$this->deleteWebdavOldBackup($webdav_lib, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV) . ' '
                    . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            $this->checkStopScript();

            // Check available space
            $new_available_space = $webdav_lib->getAvailableQuota();

            $this->checkStopScript();

            if ($new_available_space != -1) {
                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);

                    $this->checkStopScript();

                    $new_available_space -= $total_file_size;
                }

                if ($new_available_space <= $this->total_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV) . ' '
                        . $this->l('Not enough space available', self::PAGE)
                    );

                    return false;
                }
            }

            $this->log(
                sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV) . ' '
                . $this->l('Check the path of your backup', self::PAGE)
            );

            // Check if the folder we want to use exists. If not we create it.
            if ($webdav_lib->folderExists($webdav_dir) === false) {
                $this->log(
                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV)
                    . ' ' . $this->l('Create the directory', self::PAGE) . ' "' . $webdav->directory . '"'
                );

                $this->checkStopScript();

                if ($webdav_lib->createFolder($webdav_dir) === false) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV)
                        . ' ' . $this->l('Error while creating the directory', self::PAGE) . ' "' . $webdav->directory . '"'
                    );

                    return false;
                }
            }

            $this->checkStopScript();

            $this->log(sprintf($this->l('Creating backup on %s account...', self::PAGE), self::WEBDAV));

            $this->webdav_nb_part = 1;
            $this->webdav_position = 0;
            $this->distant_tar_content = '';
            $this->distant_tar_content_size = 0;

            $this->distant_tar_content = $this->createTarStart();
            $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
        }

        $this->checkStopScript();

        if ($this->getFileSize($this->file_list_file) <= 0) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV)
                . ' ' . $this->l('No file to backup', self::PAGE)
            );

            return false;
        }

        $this->checkStopScript();

        if (!is_resource($this->handle_file_list_file)) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV)
                . ' ' . $this->l('Error while getting the files to backup', self::PAGE)
            );

            return false;
        }

        $this->handle_file_list_file = $this->goToPositionInFile(
            $this->handle_file_list_file,
            $this->position_file_list_file,
            false
        );

        $this->checkStopScript();

        while (!feof($this->handle_file_list_file)) {
            $this->checkStopScript();

            $line = rtrim(fgets($this->handle_file_list_file));

            if (!$line) {
                continue;
            }

            $pos_cut = strrpos($line, ':');

            if ($pos_cut === false) {
                continue;
            }

            $current_file = self::binaryToString(Tools::substr($line, 0, $pos_cut));

            if (!$current_file) {
                continue;
            }

            $this->checkStopScript();

            $found_size = Tools::substr($line, $pos_cut + 1);
            $tar_name = basename($this->part_list[$this->part_number - 1]);
            $path_in_tar = '';

            // Normalize path
            $current_normalized_file = $this->normalizePath($current_file);

            // Find relative filename
            if (!$path_in_tar) {
                $filename = ltrim(self::getPart($current_normalized_file, $this->base_length), '/');
            } else {
                $filename = ltrim($path_in_tar, '/');
            }

            // File information
            $info = $this->tarFileInfo($current_file);
            $diff_size = 0;

            if ($info['size'] != $found_size) {
                if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV)
                        . ' ' . sprintf(
                            $this->l('The file %1$s has changed of size from %2$s to %3$s', self::PAGE),
                            $current_file,
                            $this->readableSize($found_size) . ' (' . $found_size . ')',
                            $this->readableSize($info['size']) . ' (' . $info['size'] . ')'
                        )
                    );
                }

                $diff_size = $info['size'] - $found_size;
                $info['size'] = $found_size;
            }

            if (in_array($current_file, $this->list_dump_files) && $this->config->type_backup == $this->type_backup_complete) {
                $path_module_backup = 'modules/' . $this->name . '/' . self::BACKUP_FOLDER . '/';
                $path_in_tar = str_replace($this->config_backup_dir, $path_module_backup, $current_file);
            }

            $this->checkStopScript();

            // Create file header
            if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                // Start to add a new file to the tar
                $header = $this->createTarHeader($filename, $info);

                $this->distant_tar_content_size += $this->getHeaderFileSize($filename, false);
                $this->distant_tar_content .= $header;

                ++$this->files_done;

                $this->pos_file_to_tar = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE_CONTINUE;
            }

            if ($info['size'] > 0) {
                // Open the file
                if (($file_read = @fopen($current_file, 'rb')) === false) {
                    // If it is the first opening of the file
                    if ($this->pos_file_to_tar == 0) {
                        $this->log(
                            'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV)
                            . ' ' . $this->l('File', self::PAGE) . ' ' . $current_file . ' '
                            . $this->l('will be empty because the module can not open it, please check its rights and user owner', self::PAGE)
                        );
                    }
                }

                $this->checkStopScript();

                if (is_resource($file_read) && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE_CONTINUE) {
                    $file_read = $this->goToPositionInFile($file_read, $this->pos_file_to_tar);

                    if ($file_read === false) {
                        return false;
                    }
                }

                // Data of the file
                $leftsize = $info['size'] - $this->pos_file_to_tar;
                $blocksize = self::TAR_BLOCK_SIZE;

                while ($leftsize > 0) {
                    $this->checkStopScript();

                    if ($this->next_step == $this->step_send['webdav']) {
                        // Create new resumable session
                        $this->webdav_session = rand();
                        $this->webdav_nb_chunk = 0;
                        $this->webdav_position = 0;
                        $this->next_step = $this->step_send['webdav_resume'];
                    }

                    // If max size for WebDAV has been reach, we send the content or the size of the tar was reach
                    if ($this->distant_tar_content_size >= WebdavLib::MAX_CONTENT_UPLOAD_SIZE
                        || ($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                    ) {
                        $end_of_current_part = 0;

                        if (($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]) {
                            $this->distant_tar_content_size += $tar_end_size;
                            $this->distant_tar_content .= $this->createTarEnd();
                            $end_of_current_part = 1;
                        }

                        if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                            $this->log(
                                $this->l('The total size should not be less that the size we are now at', self::PAGE)
                                . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                            );
                        }

                        // Send content using the current session
                        $resume_upload_content = $webdav_lib->uploadContent(
                            $this->distant_tar_content,
                            $this->distant_tar_content_size,
                            $this->tar_files_size[$this->part_number],
                            $tar_name,
                            $webdav_dir
                        );

                        if ($resume_upload_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be completed', self::PAGE)
                            );

                            return false;
                        } else {
                            $this->webdav_position += $this->distant_tar_content_size;

                            $this->distant_tar_content = '';
                            $this->distant_tar_content_size = 0;
                        }

                        if ($end_of_current_part) {
                            // New part, so back to init values
                            ++$this->webdav_nb_part;
                            ++$this->part_number;

                            $this->next_step = $this->step_send['webdav'];
                            $this->webdav_position = 0;
                            $this->old_percent = 0;
                            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;

                            $this->distant_tar_content = $this->createTarStart();
                            $this->distant_tar_content_size += (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
                        }
                    } else {
                        // Read data
                        $leftsize -= $blocksize;

                        if ($leftsize < 0) {
                            $blocksize += $leftsize;
                        }

                        $temp_content = '';

                        if (is_resource($file_read)) {
                            $temp_content = $this->createTarContent($file_read, $blocksize);
                        }

                        // Get where we are in the file
                        $this->pos_file_to_tar += $blocksize;

                        // If there is no content because the file size has changed (smaller file) we need to add \0
                        if ($diff_size < 0 && !$temp_content) {
                            // self::TAR_BLOCK_SIZE since createTarContent
                            // return self::TAR_BLOCK_SIZE size block (using self::pad)
                            $temp_content = self::pad('', self::TAR_BLOCK_SIZE);
                            $diff_size += self::TAR_BLOCK_SIZE;

                            if ($this->config->crypt_backup && $this->config->ignore_compression) {
                                $temp_content = $this->cryptBackup($temp_content);
                                $diff_size += SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;
                            }
                        } elseif ($temp_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV)
                                . ' ' . $this->l('The module was unable to read the file', self::PAGE)
                                . ' ' . $current_file . ', ' . $this->l('please check its rights and user owner', self::PAGE)
                            );

                            if (is_resource($file_read)) {
                                fclose($file_read);
                            }

                            return false;
                        }

                        $this->distant_tar_content .= $temp_content;
                        $this->distant_tar_content_size += self::TAR_BLOCK_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES : 0);

                        $percent = (($this->distant_tar_content_size + $this->webdav_position) / $this->tar_files_size[$this->part_number]) * 100;

                        if ($percent >= $this->old_percent + 1) {
                            $this->old_percent = round($percent, 0);

                            if ($this->total_nb_part > 1) {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV)
                                    . ' ' . $this->part_number . '/' . $this->total_nb_part . $this->l(':', self::PAGE)
                                    . ' ' . (int) $percent . '%'
                                );
                            } else {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV)
                                    . ' ' . (int) $percent . '%'
                                );
                            }
                        }
                    }

                    // refresh
                    $this->refreshBackup(true);
                }

                // Close file
                if (is_resource($file_read)) {
                    fclose($file_read);
                }
            }

            /*if (basename($filename) == self::TEMP_EMPTY_FILE) {
                @unlink($current_file);
            }*/

            $this->position_file_list_file = ftell($this->handle_file_list_file);

            $this->checkStopScript();

            // Check if tar file size has reach its predicted size
            if (isset($this->tar_files_size[$this->part_number])
                && ($this->webdav_position + $this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                && $this->webdav_position < $this->tar_files_size[$this->part_number]
            ) {
                // The tar file will be too big, we need to close it and use a new one
                $this->distant_tar_content .= $this->createTarEnd();
                $this->distant_tar_content_size += $tar_end_size;

                if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                    $this->log(
                        $this->l('The total size should not be less that the size we are now at', self::PAGE)
                        . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                    );
                }

                // Finish sending current tar
                $resume_upload_content = $webdav_lib->uploadContent(
                    $this->distant_tar_content,
                    $this->distant_tar_content_size,
                    $this->tar_files_size[$this->part_number],
                    $tar_name,
                    $webdav_dir
                );

                if ($resume_upload_content === false) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV)
                        . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                        . ' ' . $this->l('cannot be finished', self::PAGE)
                    );

                    return false;
                }

                // New part, so back to init values
                ++$this->webdav_nb_part;
                ++$this->part_number;

                $this->next_step = $this->step_send['webdav'];
                $this->webdav_position = 0;
                $this->old_percent = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                $this->distant_tar_content = '';
                $this->distant_tar_content_size = 0;

                $this->distant_tar_content = $this->createTarStart();
                $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);

                // refresh
                $this->refreshBackup(true);
            }

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
        }

        // Check that there is no missing file
        if ($this->files_done < $this->total_files) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::WEBDAV)
                . $this->l('Be careful! Not all of your files have been added to the backup. Number of files backuped:', self::PAGE)
                . ' ' . $this->files_done . '/' . $this->total_files
            );

            return false;
        }

        $this->files_done = 0;
        $this->pos_file_to_tar = 0;

        $this->next_step = $this->step_send['webdav_resume'];

        if ($this->config->send_restore) {
            $this->log(
                sprintf($this->l('Creating restore file on %s account...', self::PAGE), self::WEBDAV)
            );

            // Upload the file
            if ($webdav_lib->uploadFile($this->restore_file, $webdav_dir, self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Send a file on a Google Drive account
     *
     * @return bool The success or failure of the operation
     */
    protected function sendFileToGoogledrive()
    {
        $googledrive = new GoogledriveNtbr($this->googledrive_account_id);
        $access_token = $googledrive->token;
        $googledrive_dir = $googledrive->directory_key;

        if ($this->next_step == $this->step_send['googledrive']
            && (!isset($this->googledrive_nb_part) || $this->googledrive_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::GOOGLEDRIVE));
        }

        $googledrive_lib = $this->connectToGoogledrive($access_token);

        if (!$googledrive_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::GOOGLEDRIVE)
            );

            return false;
        }

        if ($this->next_step == $this->step_send['googledrive']
            && (!isset($this->googledrive_nb_part) || $this->googledrive_nb_part == 1)
        ) {
            if (Tools::substr($googledrive->directory_path, -1) != '/') {
                $googledrive->directory_path .= '/';
            }

            // If file already on Google Drive
            foreach ($this->part_list as $part) {
                $file_destination = $googledrive->directory_path . basename($part);

                $this->log(
                    $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                );
                $id_file = $googledrive_lib->checkExists(basename($part), $googledrive_dir);

                if ($id_file !== false) {
                    $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                    if ($googledrive_lib->deleteFile($id_file) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                    }
                }
            }

            // Get old backups list
            $old_backups = $this->getGoogledriveFiles($googledrive_lib, $googledrive_dir);
            $nb_backup_to_keep = $googledrive->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            // Get available size
            $available_space = $googledrive_lib->getAvailableQuota();

            if ($available_space != '-1') {
                $available_space += $size_old_backups;

                // Get size to upload
                $to_send_size = $this->total_size;

                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);
                    $to_send_size += $total_file_size;
                }

                // Check if we will have enough size after deleting old backup, too add new backup
                if ($available_space < $to_send_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::GOOGLEDRIVE) . ' '
                        . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                    );

                    return false;
                }
            }

            // Delete old backup
            if (!$this->deleteGoogledriveOldBackup($googledrive_lib, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::GOOGLEDRIVE)
                    . ' ' . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            // Check available space
            $new_available_space = $googledrive_lib->getAvailableQuota();

            if ($new_available_space != '-1') {
                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);
                    $new_available_space -= $total_file_size;
                }

                if ($new_available_space <= $this->total_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::GOOGLEDRIVE)
                        . ' ' . $this->l('Not enough space available', self::PAGE)
                    );

                    return false;
                }
            }

            $this->log(sprintf($this->l('Sending backup to %s account...', self::PAGE), self::GOOGLEDRIVE));
            $this->googledrive_nb_part = 1;
            $this->googledrive_position = 0;
        }

        $nb_part = 1;
        // Upload the file
        foreach ($this->part_list as $part) {
            if ($nb_part == $this->googledrive_nb_part) {
                if ($this->next_step == $this->step_send['googledrive']) {
                    $this->next_step = $this->step_send['googledrive_resume'];

                    // Upload the file
                    $upload_file = $googledrive_lib->uploadFile(
                        $part,
                        $googledrive_dir,
                        '',
                        $this->googledrive_nb_part,
                        $this->total_nb_part
                    );

                    if ($upload_file === false) {
                        return false;
                    }
                } else {
                    // Resume upload of the file
                    $resume_upload_file = $googledrive_lib->resumeUploadFile(
                        $part,
                        $this->googledrive_nb_part,
                        $this->total_nb_part
                    );

                    if ($resume_upload_file === false) {
                        return false;
                    }
                }
                ++$this->googledrive_nb_part;
                // New part, so back to init values
                $this->next_step = $this->step_send['googledrive'];
                $this->googledrive_position = 0;
            }

            ++$nb_part;
        }

        $this->next_step = $this->step_send['googledrive_resume'];

        if ($this->config->send_restore) {
            if (Tools::substr($googledrive->directory_path, -1) != '/') {
                $googledrive->directory_path .= '/';
            }

            $file_destination = $googledrive->directory_path . self::NEW_RESTORE_NAME;
            $id_file = $googledrive_lib->checkExists(self::NEW_RESTORE_NAME, $googledrive_dir);

            if ($id_file !== false) {
                $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                if ($googledrive_lib->deleteFile($id_file) === false) {
                    $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                }
            }

            $this->log(
                sprintf($this->l('Sending restore file to %s account...', self::PAGE), self::GOOGLEDRIVE)
            );

            // Upload the file
            if ($googledrive_lib->uploadFile($this->restore_file, $googledrive_dir, self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Send a file on a Google Cloud account
     *
     * @return bool The success or failure of the operation
     */
    protected function sendFileToGooglecloud()
    {
        $googlecloud = new GooglecloudNtbr($this->googlecloud_account_id);

        if ($this->next_step == $this->step_send['googlecloud']
            && (!isset($this->googlecloud_nb_part) || $this->googlecloud_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::GOOGLECLOUD));
        }

        $googlecloud_lib = $this->connectToGooglecloud($googlecloud->bucket, $googlecloud->token);

        if (!$googlecloud_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::GOOGLECLOUD)
            );

            return false;
        }

        if ($this->next_step == $this->step_send['googlecloud']
            && (!isset($this->googlecloud_nb_part) || $this->googlecloud_nb_part == 1)
        ) {
            if (Tools::substr($googlecloud->directory, -1) != '/') {
                $googlecloud->directory .= '/';
            }

            // If file already on Google Cloud
            foreach ($this->part_list as $part) {
                $file_destination = $googlecloud->directory . basename($part);

                $this->log(
                    $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                );

                if ($googlecloud_lib->checkExists($file_destination)) {
                    $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);

                    if ($googlecloud_lib->deleteItem($file_destination) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                    }
                }
            }

            // Get old backups list
            $old_backups = $this->getGooglecloudFiles($googlecloud_lib, $googlecloud->directory);
            $nb_backup_to_keep = $googlecloud->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            // Delete old backup
            if (!$this->deleteGooglecloudOldBackup($googlecloud_lib, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::GOOGLECLOUD)
                    . ' ' . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            $this->log(sprintf($this->l('Sending backup to %s account...', self::PAGE), self::GOOGLECLOUD));
            $this->googlecloud_nb_part = 1;
            $this->googlecloud_position = 0;
        }

        $nb_part = 1;

        foreach ($this->part_list as $part) {
            if ($nb_part == $this->googlecloud_nb_part) {
                $file_path = $part;
                $file_destination = $googlecloud->directory . basename($part);

                // Upload the file
                if ($this->next_step == $this->step_send['googlecloud']) {
                    $this->next_step = $this->step_send['googlecloud_resume'];

                    $upload_file = $googlecloud_lib->uploadFile(
                        $file_path,
                        $file_destination,
                        $this->googlecloud_nb_part,
                        $this->total_nb_part
                    );

                    if ($upload_file === false) {
                        return false;
                    }
                } else {// Resume upload of the file
                    $resume_upload_file = $googlecloud_lib->resumeUploadFile(
                        $file_path,
                        $this->googlecloud_nb_part,
                        $this->total_nb_part
                    );

                    if ($resume_upload_file === false) {
                        return false;
                    }
                }
                ++$this->googlecloud_nb_part;
                // New part, so back to init values
                $this->next_step = $this->step_send['googlecloud'];
                $this->googlecloud_position = 0;
            }

            ++$nb_part;
        }

        $this->next_step = $this->step_send['googlecloud_resume'];

        if ($this->config->send_restore) {
            $file_destination = $googlecloud->directory_path . self::NEW_RESTORE_NAME;

            if ($googlecloud_lib->checkExists($file_destination)) {
                $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);

                if ($googlecloud_lib->deleteItem($file_destination) === false) {
                    $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                }
            }

            $this->log(
                sprintf($this->l('Sending restore file to %s account...', self::PAGE), self::GOOGLECLOUD)
            );

            // Upload the file
            if ($googlecloud_lib->uploadFile($this->restore_file, $file_destination) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Send a file on a pCloud account
     *
     * @return bool The success or failure of the operation
     */
    protected function sendFileToPcloud()
    {
        $pcloud = new PcloudNtbr($this->pcloud_account_id);

        if ($this->next_step == $this->step_send['pcloud']
            && (!isset($this->pcloud_nb_part) || $this->pcloud_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::PCLOUD));
        }

        $pcloud_lib = $this->connectToPcloud($pcloud->location_id, $pcloud->token);

        if (!$pcloud_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::PCLOUD)
            );

            return false;
        }

        if ($pcloud->directory_path) {
            if (!$pcloud_lib->checkExists($pcloud->directory_path)) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::PCLOUD)
                    . ' ' . sprintf($this->l('Error, the directory %s does not exists. Your file will be at the root of your %s account', self::PAGE), $pcloud->directory_path, self::PCLOUD)
                );
                $pcloud->directory_path = '/';
                $pcloud->directory_id = 0;
            }
        }

        if ($this->next_step == $this->step_send['pcloud']
            && (!isset($this->pcloud_nb_part) || $this->pcloud_nb_part == 1)
        ) {
            if (Tools::substr($pcloud->directory_path, -1) != '/') {
                $pcloud->directory_path .= '/';
            }

            // If file already on pCloud
            foreach ($this->part_list as $part) {
                $file_destination = $pcloud->directory_path . basename($part);

                $this->log(
                    $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                );

                if ($pcloud_lib->checkExists($file_destination)) {
                    $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);

                    if ($pcloud_lib->deleteItem($file_destination) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                    }
                }
            }

            // Get old backups list
            $old_backups = $this->getPcloudFiles($pcloud_lib, $pcloud->directory_id);
            $nb_backup_to_keep = $pcloud->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            // Delete old backup
            if (!$this->deletePcloudOldBackup($pcloud_lib, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::PCLOUD)
                    . ' ' . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            $this->log(sprintf($this->l('Sending backup to %s account...', self::PAGE), self::PCLOUD));
            $this->pcloud_nb_part = 1;
            $this->pcloud_position = 0;
        }

        $nb_part = 1;

        foreach ($this->part_list as $part) {
            if ($nb_part == $this->pcloud_nb_part) {
                $file_path = $part;

                // Upload the file
                if ($this->next_step == $this->step_send['pcloud']) {
                    $this->next_step = $this->step_send['pcloud_resume'];

                    $upload_file = $pcloud_lib->uploadFile(
                        $file_path,
                        $pcloud->directory_id,
                        $this->pcloud_nb_part,
                        $this->total_nb_part
                    );

                    if ($upload_file === false) {
                        return false;
                    }
                } else {// Resume upload of the file
                    $resume_upload_file = $pcloud_lib->resumeUploadFile(
                        $file_path,
                        $pcloud->directory_id,
                        $this->pcloud_nb_part,
                        $this->total_nb_part
                    );

                    if ($resume_upload_file === false) {
                        return false;
                    }
                }
                ++$this->pcloud_nb_part;
                // New part, so back to init values
                $this->next_step = $this->step_send['pcloud'];
                $this->pcloud_position = 0;
            }

            ++$nb_part;
        }

        $this->next_step = $this->step_send['pcloud_resume'];

        if ($this->config->send_restore) {
            $file_destination = $pcloud->directory_path . self::NEW_RESTORE_NAME;

            if ($pcloud_lib->checkExists($file_destination)) {
                $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);

                if ($pcloud_lib->deleteItem($file_destination) === false) {
                    $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                }
            }

            $this->log(
                sprintf($this->l('Sending restore file to %s account...', self::PAGE), self::PCLOUD)
            );

            // Upload the file
            if ($pcloud_lib->uploadFile($this->restore_file, $pcloud->directory_id, 1, 1, self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a tar file on a Google Drive account
     *
     * @return bool The success or failure of the operation
     */
    protected function createTarOnGoogledrive()
    {
        $googledrive = new GoogledriveNtbr($this->googledrive_account_id);
        $access_token = $googledrive->token;
        $googledrive_dir = $googledrive->directory_key;
        $tar_end_size = self::TAR_END_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? (SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES * 2) : 0);

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['googledrive']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->googledrive_nb_part) || $this->googledrive_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::GOOGLEDRIVE));

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
            $this->files_done = 0;
            $this->position_file_list_file = 0;
            $this->part_number = 1;
            $this->old_percent = 0;
            $tar_name = basename($this->part_list[$this->part_number - 1]);

            if (count($this->part_list) != count($this->tar_files_size)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE)
                    . ' ' . $this->l('The backup cannot be created', self::PAGE)
                );

                return false;
            }

            if (!isset($this->tar_files_size[$this->part_number]) || !$this->tar_files_size[$this->part_number]) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE) . ' '
                    . sprintf($this->l('The calculated size of the file %s is not valid', self::PAGE), $tar_name)
                );

                return false;
            }
        }

        $this->checkStopScript();

        $googledrive_lib = $this->connectToGoogledrive($access_token);

        if (!$googledrive_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::GOOGLEDRIVE)
            );

            return false;
        }

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['googledrive']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->googledrive_nb_part) || $this->googledrive_nb_part == 1)
        ) {
            if (Tools::substr($googledrive->directory_path, -1) != '/') {
                $googledrive->directory_path .= '/';
            }

            // Get old backups list
            $old_backups = $this->getGoogledriveFiles($googledrive_lib, $googledrive_dir);
            $nb_backup_to_keep = $googledrive->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            $this->checkStopScript();

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    $this->checkStopScript();

                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            $this->checkStopScript();

            // Get available size
            $available_space = $googledrive_lib->getAvailableQuota();

            $this->checkStopScript();

            if ($available_space != '-1') {
                $available_space += $size_old_backups;

                // Get size to upload
                $to_send_size = $this->total_size;

                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);

                    $this->checkStopScript();

                    $to_send_size += $total_file_size;
                }

                // Check if we will have enough size after deleting old backup, too add new backup
                if ($available_space < $to_send_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE) . ' '
                        . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                    );

                    return false;
                }
            }

            $this->checkStopScript();

            // Delete old backup
            if (!$this->deleteGoogledriveOldBackup($googledrive_lib, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE)
                    . ' ' . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            $this->checkStopScript();

            // Check available space
            $new_available_space = $googledrive_lib->getAvailableQuota();

            $this->checkStopScript();

            if ($new_available_space != '-1') {
                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);

                    $this->checkStopScript();

                    $new_available_space -= $total_file_size;
                }

                if ($new_available_space <= $this->total_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE)
                        . ' ' . $this->l('Not enough space available', self::PAGE)
                    );

                    return false;
                }
            }

            $this->log(sprintf($this->l('Creating backup on %s account...', self::PAGE), self::GOOGLEDRIVE));

            $this->googledrive_nb_part = 1;
            $this->googledrive_position = 0;
            $this->distant_tar_content = '';
            $this->distant_tar_content_size = 0;

            $this->distant_tar_content = $this->createTarStart();
            $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
        }

        $this->checkStopScript();

        if ($this->getFileSize($this->file_list_file) <= 0) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE)
                . ' ' . $this->l('No file to backup', self::PAGE)
            );

            return false;
        }

        $this->checkStopScript();

        if (!is_resource($this->handle_file_list_file)) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE)
                . ' ' . $this->l('Error while getting the files to backup', self::PAGE)
            );

            return false;
        }

        $this->handle_file_list_file = $this->goToPositionInFile(
            $this->handle_file_list_file,
            $this->position_file_list_file,
            false
        );

        $this->checkStopScript();

        while (!feof($this->handle_file_list_file)) {
            $this->checkStopScript();

            $line = rtrim(fgets($this->handle_file_list_file));

            if (!$line) {
                continue;
            }

            $pos_cut = strrpos($line, ':');

            if ($pos_cut === false) {
                continue;
            }

            $current_file = self::binaryToString(Tools::substr($line, 0, $pos_cut));

            if (!$current_file) {
                continue;
            }

            $this->checkStopScript();

            $found_size = Tools::substr($line, $pos_cut + 1);
            $tar_name = basename($this->part_list[$this->part_number - 1]);
            $path_in_tar = '';

            // Normalize path
            $current_normalized_file = $this->normalizePath($current_file);

            // Find relative filename
            if (!$path_in_tar) {
                $filename = ltrim(self::getPart($current_normalized_file, $this->base_length), '/');
            } else {
                $filename = ltrim($path_in_tar, '/');
            }

            // File information
            $info = $this->tarFileInfo($current_file);
            $diff_size = 0;

            if ($info['size'] != $found_size) {
                if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE)
                        . ' ' . sprintf(
                            $this->l('The file %1$s has changed of size from %2$s to %3$s', self::PAGE),
                            $current_file,
                            $this->readableSize($found_size) . ' (' . $found_size . ')',
                            $this->readableSize($info['size']) . ' (' . $info['size'] . ')'
                        )
                    );
                }

                $diff_size = $info['size'] - $found_size;
                $info['size'] = $found_size;
            }

            if (in_array($current_file, $this->list_dump_files) && $this->config->type_backup == $this->type_backup_complete) {
                $path_module_backup = 'modules/' . $this->name . '/' . self::BACKUP_FOLDER . '/';
                $path_in_tar = str_replace($this->config_backup_dir, $path_module_backup, $current_file);
            }

            $this->checkStopScript();

            // Create file header
            if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                // Start to add a new file to the tar
                $header = $this->createTarHeader($filename, $info);

                $this->distant_tar_content_size += $this->getHeaderFileSize($filename, false);
                $this->distant_tar_content .= $header;

                ++$this->files_done;

                $this->pos_file_to_tar = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE_CONTINUE;
            }

            if ($info['size'] > 0) {
                // Open the file
                if (($file_read = @fopen($current_file, 'rb')) === false) {
                    // If it is the first opening of the file
                    if ($this->pos_file_to_tar == 0) {
                        $this->log(
                            'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE)
                            . ' ' . $this->l('File', self::PAGE) . ' ' . $current_file . ' '
                            . $this->l('will be empty because the module can not open it, please check its rights and user owner', self::PAGE)
                        );
                    }
                }

                $this->checkStopScript();

                if (is_resource($file_read) && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE_CONTINUE) {
                    $file_read = $this->goToPositionInFile($file_read, $this->pos_file_to_tar);

                    if ($file_read === false) {
                        return false;
                    }
                }

                // Data of the file
                $leftsize = $info['size'] - $this->pos_file_to_tar;
                $blocksize = self::TAR_BLOCK_SIZE;

                while ($leftsize > 0) {
                    $this->checkStopScript();

                    if ($this->next_step == $this->step_send['googledrive']) {
                        // Create new resumable session
                        $create_session = $googledrive_lib->createSession(
                            $googledrive_dir,
                            $tar_name,
                            ($this->config->crypt_backup && $this->config->ignore_compression) ? 'application/octet-stream' : 'application/x-tar',
                            $this->tar_files_size[$this->part_number]
                        );

                        if ($create_session === false) {
                            $this->log(
                                'WAR'
                                . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be created', self::PAGE)
                            );

                            return false;
                        }

                        $this->googledrive_position = 0;
                        $this->next_step = $this->step_send['googledrive_resume'];
                    }

                    // If max size for Google Drive has been reach, we send the content or the size of the tar was reach
                    if ($this->distant_tar_content_size >= GoogledriveLib::MAX_FILE_UPLOAD_SIZE
                        || ($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                    ) {
                        $end_of_current_part = 0;

                        if (($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]) {
                            $this->distant_tar_content_size += $tar_end_size;
                            $this->distant_tar_content .= $this->createTarEnd();
                            $end_of_current_part = 1;
                        }

                        if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                            $this->log(
                                $this->l('The total size should not be less that the size we are now at', self::PAGE)
                                . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                            );
                        }

                        // Send content using the current session
                        $position_after_upload = $googledrive_lib->resumeUploadContent(
                            $this->distant_tar_content,
                            $this->distant_tar_content_size,
                            $this->tar_files_size[$this->part_number]
                        );

                        if ($position_after_upload === -1) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be completed', self::PAGE)
                            );

                            return false;
                        } else {
                            // Calcul what should be the new position
                            $new_position = $this->googledrive_position + $this->distant_tar_content_size;
                            $this->googledrive_position = $position_after_upload;

                            // If Google Drive did not upload everything,
                            // we need to put back what is left in content and content size
                            if ($new_position != $position_after_upload) {
                                $diff = $new_position - $position_after_upload;
                                $end_of_current_part = 0;

                                $this->distant_tar_content_size = $diff;

                                // Use Apparatus::substr instead of Tools::substr
                                // so than it will cut using octets instead of characters
                                $this->distant_tar_content = Apparatus::substr(
                                    $this->distant_tar_content,
                                    $this->distant_tar_content_size * -1
                                );
                            } else {
                                $this->distant_tar_content = '';
                                $this->distant_tar_content_size = 0;
                            }
                        }

                        if ($end_of_current_part) {
                            // New part, so back to init values
                            ++$this->googledrive_nb_part;
                            ++$this->part_number;

                            $this->next_step = $this->step_send['googledrive'];
                            $this->googledrive_position = 0;
                            $this->old_percent = 0;
                            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;

                            $this->distant_tar_content = $this->createTarStart();
                            $this->distant_tar_content_size += (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
                        }
                    } else {
                        // Read data
                        $leftsize -= $blocksize;

                        if ($leftsize < 0) {
                            $blocksize += $leftsize;
                        }

                        $temp_content = '';

                        if (is_resource($file_read)) {
                            $temp_content = $this->createTarContent($file_read, $blocksize);
                        }

                        // Get where we are in the file
                        $this->pos_file_to_tar += $blocksize;

                        // If there is no content because the file size has changed (smaller file) we need to add \0
                        if ($diff_size < 0 && !$temp_content) {
                            // self::TAR_BLOCK_SIZE since createTarContent
                            // return self::TAR_BLOCK_SIZE size block (using self::pad)
                            $temp_content = self::pad('', self::TAR_BLOCK_SIZE);
                            $diff_size += self::TAR_BLOCK_SIZE;

                            if ($this->config->crypt_backup && $this->config->ignore_compression) {
                                $temp_content = $this->cryptBackup($temp_content);
                                $diff_size += SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;
                            }
                        } elseif ($temp_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE)
                                . ' ' . $this->l('The module was unable to read the file', self::PAGE)
                                . ' ' . $current_file . ', ' . $this->l('please check its rights and user owner', self::PAGE)
                            );

                            if (is_resource($file_read)) {
                                fclose($file_read);
                            }

                            return false;
                        }

                        $this->distant_tar_content .= $temp_content;
                        $this->distant_tar_content_size += self::TAR_BLOCK_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES : 0);

                        $percent = (($this->distant_tar_content_size + $this->googledrive_position) / $this->tar_files_size[$this->part_number]) * 100;

                        if ($percent >= $this->old_percent + 1) {
                            $this->old_percent = round($percent, 0);

                            if ($this->total_nb_part > 1) {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE)
                                    . ' ' . $this->part_number . '/' . $this->total_nb_part . $this->l(':', self::PAGE)
                                    . ' ' . (int) $percent . '%'
                                );
                            } else {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE)
                                    . ' ' . (int) $percent . '%'
                                );
                            }
                        }
                    }

                    // refresh
                    $this->refreshBackup(true);
                }

                // Close file
                if (is_resource($file_read)) {
                    fclose($file_read);
                }
            }

            /*if (basename($filename) == self::TEMP_EMPTY_FILE) {
                @unlink($current_file);
            }*/

            $this->position_file_list_file = ftell($this->handle_file_list_file);

            $this->checkStopScript();

            // Check if tar file size has reach its predicted size
            if (isset($this->tar_files_size[$this->part_number])
                && ($this->googledrive_position + $this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                && $this->googledrive_position < $this->tar_files_size[$this->part_number]
            ) {
                // The tar file will be too big, we need to close it and use a new one
                $this->distant_tar_content .= $this->createTarEnd();
                $this->distant_tar_content_size += $tar_end_size;

                if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                    $this->log(
                        $this->l('The total size should not be less that the size we are now at', self::PAGE)
                        . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                    );
                }

                // Finish sending current tar
                $position_after_upload = $googledrive_lib->resumeUploadContent(
                    $this->distant_tar_content,
                    $this->distant_tar_content_size,
                    $this->tar_files_size[$this->part_number]
                );

                if ($position_after_upload === -1) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE)
                        . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                        . ' ' . $this->l('cannot be finished', self::PAGE)
                    );

                    return false;
                }

                // New part, so back to init values
                ++$this->googledrive_nb_part;
                ++$this->part_number;

                $this->next_step = $this->step_send['googledrive'];
                $this->googledrive_position = 0;
                $this->old_percent = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                $this->distant_tar_content = '';
                $this->distant_tar_content_size = 0;

                $this->distant_tar_content = $this->createTarStart();
                $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);

                // refresh
                $this->refreshBackup(true);
            }

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
        }

        // Check that there is no missing file
        if ($this->files_done < $this->total_files) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLEDRIVE)
                . $this->l('Be careful! Not all of your files have been added to the backup. Number of files backuped:', self::PAGE)
                . ' ' . $this->files_done . '/' . $this->total_files
            );

            return false;
        }

        $this->files_done = 0;
        $this->pos_file_to_tar = 0;

        $this->next_step = $this->step_send['googledrive_resume'];

        if ($this->config->send_restore) {
            if (Tools::substr($googledrive->directory_path, -1) != '/') {
                $googledrive->directory_path .= '/';
            }

            $file_destination = $googledrive->directory_path . self::NEW_RESTORE_NAME;
            $id_file = $googledrive_lib->checkExists(self::NEW_RESTORE_NAME, $googledrive_dir);

            if ($id_file !== false) {
                $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);

                if ($googledrive_lib->deleteFile($id_file) === false) {
                    $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                }
            }

            $this->log(
                sprintf($this->l('Creating restore file on %s account...', self::PAGE), self::GOOGLEDRIVE)
            );

            // Upload the file
            if ($googledrive_lib->uploadFile($this->restore_file, $googledrive_dir, self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a tar file on a Google Cloud account
     *
     * @return bool The success or failure of the operation
     */
    protected function createTarOnGooglecloud()
    {
        $googlecloud = new GooglecloudNtbr($this->googlecloud_account_id);
        $tar_end_size = self::TAR_END_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? (SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES * 2) : 0);

        if (Tools::substr($googlecloud->directory, -1) != '/') {
            $googlecloud->directory .= '/';
        }

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['googlecloud']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->googlecloud_nb_part) || $this->googlecloud_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::GOOGLECLOUD));

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
            $this->files_done = 0;
            $this->position_file_list_file = 0;
            $this->part_number = 1;
            $this->old_percent = 0;
            $tar_name = basename($this->part_list[$this->part_number - 1]);

            if (count($this->part_list) != count($this->tar_files_size)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLECLOUD)
                    . ' ' . $this->l('The backup cannot be created', self::PAGE)
                );

                return false;
            }

            if (!isset($this->tar_files_size[$this->part_number]) || !$this->tar_files_size[$this->part_number]) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLECLOUD) . ' '
                    . sprintf($this->l('The calculated size of the file %s is not valid', self::PAGE), $tar_name)
                );

                return false;
            }
        }

        $this->checkStopScript();

        $googlecloud_lib = $this->connectToGooglecloud($googlecloud->bucket, $googlecloud->token);

        if (!$googlecloud_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::GOOGLECLOUD)
            );

            return false;
        }

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['googlecloud']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->googlecloud_nb_part) || $this->googlecloud_nb_part == 1)
        ) {
            // Get old backups list
            $old_backups = $this->getGooglecloudFiles($googlecloud_lib, $googlecloud->directory);
            $nb_backup_to_keep = $googlecloud->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            $this->checkStopScript();

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    $this->checkStopScript();

                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            $this->checkStopScript();

            // Delete old backup
            if (!$this->deleteGooglecloudOldBackup($googlecloud_lib, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLECLOUD)
                    . ' ' . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            $this->checkStopScript();

            $this->log(sprintf($this->l('Creating backup on %s account...', self::PAGE), self::GOOGLECLOUD));

            $this->googlecloud_nb_part = 1;
            $this->googlecloud_position = 0;
            $this->distant_tar_content = '';
            $this->distant_tar_content_size = 0;

            $this->distant_tar_content = $this->createTarStart();
            $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
        }

        $this->checkStopScript();

        if ($this->getFileSize($this->file_list_file) <= 0) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLECLOUD)
                . ' ' . $this->l('No file to backup', self::PAGE)
            );

            return false;
        }

        $this->checkStopScript();

        if (!is_resource($this->handle_file_list_file)) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLECLOUD)
                . ' ' . $this->l('Error while getting the files to backup', self::PAGE)
            );

            return false;
        }

        $this->handle_file_list_file = $this->goToPositionInFile(
            $this->handle_file_list_file,
            $this->position_file_list_file,
            false
        );

        $this->checkStopScript();

        while (!feof($this->handle_file_list_file)) {
            $this->checkStopScript();

            $line = rtrim(fgets($this->handle_file_list_file));

            if (!$line) {
                continue;
            }

            $pos_cut = strrpos($line, ':');

            if ($pos_cut === false) {
                continue;
            }

            $current_file = self::binaryToString(Tools::substr($line, 0, $pos_cut));

            if (!$current_file) {
                continue;
            }

            $this->checkStopScript();

            $found_size = Tools::substr($line, $pos_cut + 1);
            $tar_name = basename($this->part_list[$this->part_number - 1]);
            $path_in_tar = '';

            // Normalize path
            $current_normalized_file = $this->normalizePath($current_file);

            // Find relative filename
            if (!$path_in_tar) {
                $filename = ltrim(self::getPart($current_normalized_file, $this->base_length), '/');
            } else {
                $filename = ltrim($path_in_tar, '/');
            }

            // File information
            $info = $this->tarFileInfo($current_file);
            $diff_size = 0;

            if ($info['size'] != $found_size) {
                if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLECLOUD)
                        . ' ' . sprintf(
                            $this->l('The file %1$s has changed of size from %2$s to %3$s', self::PAGE),
                            $current_file,
                            $this->readableSize($found_size) . ' (' . $found_size . ')',
                            $this->readableSize($info['size']) . ' (' . $info['size'] . ')'
                        )
                    );
                }

                $diff_size = $info['size'] - $found_size;
                $info['size'] = $found_size;
            }

            if (in_array($current_file, $this->list_dump_files) && $this->config->type_backup == $this->type_backup_complete) {
                $path_module_backup = 'modules/' . $this->name . '/' . self::BACKUP_FOLDER . '/';
                $path_in_tar = str_replace($this->config_backup_dir, $path_module_backup, $current_file);
            }

            $this->checkStopScript();

            // Create file header
            if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                // Start to add a new file to the tar
                $header = $this->createTarHeader($filename, $info);

                $this->distant_tar_content_size += $this->getHeaderFileSize($filename, false);
                $this->distant_tar_content .= $header;

                ++$this->files_done;

                $this->pos_file_to_tar = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE_CONTINUE;
            }

            if ($info['size'] > 0) {
                // Open the file
                if (($file_read = @fopen($current_file, 'rb')) === false) {
                    // If it is the first opening of the file
                    if ($this->pos_file_to_tar == 0) {
                        $this->log(
                            'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLECLOUD)
                            . ' ' . $this->l('File', self::PAGE) . ' ' . $current_file . ' '
                            . $this->l('will be empty because the module can not open it, please check its rights and user owner', self::PAGE)
                        );
                    }
                }

                $this->checkStopScript();

                if (is_resource($file_read) && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE_CONTINUE) {
                    $file_read = $this->goToPositionInFile($file_read, $this->pos_file_to_tar);

                    if ($file_read === false) {
                        return false;
                    }
                }

                // Data of the file
                $leftsize = $info['size'] - $this->pos_file_to_tar;
                $blocksize = self::TAR_BLOCK_SIZE;

                while ($leftsize > 0) {
                    $this->checkStopScript();

                    if ($this->next_step == $this->step_send['googlecloud']) {
                        // Create new resumable session
                        $create_session = $googlecloud_lib->createSession(
                            $googlecloud->directory . $tar_name,
                            ($this->config->crypt_backup && $this->config->ignore_compression) ? 'application/octet-stream' : 'application/x-tar',
                            $this->tar_files_size[$this->part_number]
                        );

                        if ($create_session === false) {
                            $this->log(
                                'WAR'
                                . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLECLOUD)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be created', self::PAGE)
                            );

                            return false;
                        }

                        $this->googlecloud_position = 0;
                        $this->next_step = $this->step_send['googlecloud_resume'];
                    }

                    // If max size for Google Cloud has been reach, we send the content or the size of the tar was reach
                    if ($this->distant_tar_content_size >= GooglecloudLib::MAX_FILE_UPLOAD_SIZE
                        || ($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                    ) {
                        $end_of_current_part = 0;

                        if (($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]) {
                            $this->distant_tar_content_size += $tar_end_size;
                            $this->distant_tar_content .= $this->createTarEnd();
                            $end_of_current_part = 1;
                        }

                        if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                            $this->log(
                                $this->l('The total size should not be less that the size we are now at', self::PAGE)
                                . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                            );
                        }

                        // Send content using the current session
                        $position_after_upload = $googlecloud_lib->resumeUploadContent(
                            $this->distant_tar_content,
                            $this->distant_tar_content_size,
                            $this->tar_files_size[$this->part_number]
                        );

                        if ($position_after_upload === -1) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLECLOUD)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be completed', self::PAGE)
                            );

                            return false;
                        } else {
                            // Calcul what should be the new position
                            $new_position = $this->googlecloud_position + $this->distant_tar_content_size;
                            $this->googlecloud_position = $position_after_upload;

                            // If Google Cloud did not upload everything,
                            // we need to put back what is left in content and content size
                            if ($new_position != $position_after_upload) {
                                $diff = $new_position - $position_after_upload;
                                $end_of_current_part = 0;

                                $this->distant_tar_content_size = $diff;

                                // Use Apparatus::substr instead of Tools::substr
                                // so than it will cut using octets instead of characters
                                $this->distant_tar_content = Apparatus::substr(
                                    $this->distant_tar_content,
                                    $this->distant_tar_content_size * -1
                                );
                            } else {
                                $this->distant_tar_content = '';
                                $this->distant_tar_content_size = 0;
                            }
                        }

                        if ($end_of_current_part) {
                            // New part, so back to init values
                            ++$this->googlecloud_nb_part;
                            ++$this->part_number;

                            $this->next_step = $this->step_send['googlecloud'];
                            $this->googlecloud_position = 0;
                            $this->old_percent = 0;
                            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;

                            $this->distant_tar_content = $this->createTarStart();
                            $this->distant_tar_content_size += (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
                        }
                    } else {
                        // Read data
                        $leftsize -= $blocksize;

                        if ($leftsize < 0) {
                            $blocksize += $leftsize;
                        }

                        $temp_content = '';

                        if (is_resource($file_read)) {
                            $temp_content = $this->createTarContent($file_read, $blocksize);
                        }

                        // Get where we are in the file
                        $this->pos_file_to_tar += $blocksize;

                        // If there is no content because the file size has changed (smaller file) we need to add \0
                        if ($diff_size < 0 && !$temp_content) {
                            // self::TAR_BLOCK_SIZE since createTarContent
                            // return self::TAR_BLOCK_SIZE size block (using self::pad)
                            $temp_content = self::pad('', self::TAR_BLOCK_SIZE);
                            $diff_size += self::TAR_BLOCK_SIZE;

                            if ($this->config->crypt_backup && $this->config->ignore_compression) {
                                $temp_content = $this->cryptBackup($temp_content);
                                $diff_size += SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;
                            }
                        } elseif ($temp_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLECLOUD)
                                . ' ' . $this->l('The module was unable to read the file', self::PAGE)
                                . ' ' . $current_file . ', ' . $this->l('please check its rights and user owner', self::PAGE)
                            );

                            if (is_resource($file_read)) {
                                fclose($file_read);
                            }

                            return false;
                        }

                        $this->distant_tar_content .= $temp_content;
                        $this->distant_tar_content_size += self::TAR_BLOCK_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES : 0);

                        $percent = (($this->distant_tar_content_size + $this->googlecloud_position) / $this->tar_files_size[$this->part_number]) * 100;

                        if ($percent >= $this->old_percent + 1) {
                            $this->old_percent = round($percent, 0);

                            if ($this->total_nb_part > 1) {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLECLOUD)
                                    . ' ' . $this->part_number . '/' . $this->total_nb_part . $this->l(':', self::PAGE)
                                    . ' ' . (int) $percent . '%'
                                );
                            } else {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLECLOUD)
                                    . ' ' . (int) $percent . '%'
                                );
                            }
                        }
                    }

                    // refresh
                    $this->refreshBackup(true);
                }

                // Close file
                if (is_resource($file_read)) {
                    fclose($file_read);
                }
            }

            /*if (basename($filename) == self::TEMP_EMPTY_FILE) {
                @unlink($current_file);
            }*/

            $this->position_file_list_file = ftell($this->handle_file_list_file);

            $this->checkStopScript();

            // Check if tar file size has reach its predicted size
            if (isset($this->tar_files_size[$this->part_number])
                && ($this->googlecloud_position + $this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                && $this->googlecloud_position < $this->tar_files_size[$this->part_number]
            ) {
                // The tar file will be too big, we need to close it and use a new one
                $this->distant_tar_content .= $this->createTarEnd();
                $this->distant_tar_content_size += $tar_end_size;

                if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                    $this->log(
                        $this->l('The total size should not be less that the size we are now at', self::PAGE)
                        . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                    );
                }

                // Finish sending current tar
                $position_after_upload = $googlecloud_lib->resumeUploadContent(
                    $this->distant_tar_content,
                    $this->distant_tar_content_size,
                    $this->tar_files_size[$this->part_number]
                );

                if ($position_after_upload === -1) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLECLOUD)
                        . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                        . ' ' . $this->l('cannot be finished', self::PAGE)
                    );

                    return false;
                }

                // New part, so back to init values
                ++$this->googlecloud_nb_part;
                ++$this->part_number;

                $this->next_step = $this->step_send['googlecloud'];
                $this->googlecloud_position = 0;
                $this->old_percent = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                $this->distant_tar_content = '';
                $this->distant_tar_content_size = 0;

                $this->distant_tar_content = $this->createTarStart();
                $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);

                // refresh
                $this->refreshBackup(true);
            }

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
        }

        // Check that there is no missing file
        if ($this->files_done < $this->total_files) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::GOOGLECLOUD)
                . $this->l('Be careful! Not all of your files have been added to the backup. Number of files backuped:', self::PAGE)
                . ' ' . $this->files_done . '/' . $this->total_files
            );

            return false;
        }

        $this->files_done = 0;
        $this->pos_file_to_tar = 0;

        $this->next_step = $this->step_send['googlecloud_resume'];

        if ($this->config->send_restore) {
            $file_destination = $googlecloud->directory . self::NEW_RESTORE_NAME;
            $id_file = $googlecloud_lib->checkExists(self::NEW_RESTORE_NAME, $googlecloud->directory);

            if ($id_file !== false) {
                $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);

                if ($googlecloud_lib->deleteFile($id_file) === false) {
                    $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                }
            }

            $this->log(
                sprintf($this->l('Creating restore file on %s account...', self::PAGE), self::GOOGLECLOUD)
            );

            // Upload the file
            if ($googlecloud_lib->uploadFile($this->restore_file, $googlecloud->directory, self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a tar file on a pCloud account
     *
     * @return bool The success or failure of the operation
     */
    protected function createTarOnPcloud()
    {
        $pcloud = new PcloudNtbr($this->pcloud_account_id);
        $tar_end_size = self::TAR_END_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? (SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES * 2) : 0);

        if (Tools::substr($pcloud->directory_path, -1) != '/') {
            $pcloud->directory_path .= '/';
        }

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['pcloud']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->pcloud_nb_part) || $this->pcloud_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::PCLOUD));

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
            $this->files_done = 0;
            $this->position_file_list_file = 0;
            $this->part_number = 1;
            $this->old_percent = 0;
            $tar_name = basename($this->part_list[$this->part_number - 1]);

            if (count($this->part_list) != count($this->tar_files_size)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::PCLOUD)
                    . ' ' . $this->l('The backup cannot be created', self::PAGE)
                );

                return false;
            }

            if (!isset($this->tar_files_size[$this->part_number]) || !$this->tar_files_size[$this->part_number]) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::PCLOUD) . ' '
                    . sprintf($this->l('The calculated size of the file %s is not valid', self::PAGE), $tar_name)
                );

                return false;
            }
        }

        $this->checkStopScript();

        $pcloud_lib = $this->connectToPcloud($pcloud->location_id, $pcloud->token);

        if (!$pcloud_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::PCLOUD)
            );

            return false;
        }

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['pcloud']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->pcloud_nb_part) || $this->pcloud_nb_part == 1)
        ) {
            // Get old backups list
            $old_backups = $this->getPcloudFiles($pcloud_lib, $pcloud->directory_id);
            $nb_backup_to_keep = $pcloud->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            $this->checkStopScript();

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    $this->checkStopScript();

                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            $this->checkStopScript();

            // Delete old backup
            if (!$this->deletePcloudOldBackup($pcloud_lib, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::PCLOUD)
                    . ' ' . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            $this->checkStopScript();

            $this->log(sprintf($this->l('Creating backup on %s account...', self::PAGE), self::PCLOUD));

            $this->pcloud_nb_part = 1;
            $this->pcloud_position = 0;
            $this->distant_tar_content = '';
            $this->distant_tar_content_size = 0;

            $this->distant_tar_content = $this->createTarStart();
            $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
        }

        $this->checkStopScript();

        if ($this->getFileSize($this->file_list_file) <= 0) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::PCLOUD)
                . ' ' . $this->l('No file to backup', self::PAGE)
            );

            return false;
        }

        $this->checkStopScript();

        if (!is_resource($this->handle_file_list_file)) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::PCLOUD)
                . ' ' . $this->l('Error while getting the files to backup', self::PAGE)
            );

            return false;
        }

        $this->handle_file_list_file = $this->goToPositionInFile(
            $this->handle_file_list_file,
            $this->position_file_list_file,
            false
        );

        $this->checkStopScript();

        while (!feof($this->handle_file_list_file)) {
            $this->checkStopScript();

            $line = rtrim(fgets($this->handle_file_list_file));

            if (!$line) {
                continue;
            }

            $pos_cut = strrpos($line, ':');

            if ($pos_cut === false) {
                continue;
            }

            $current_file = self::binaryToString(Tools::substr($line, 0, $pos_cut));

            if (!$current_file) {
                continue;
            }

            $this->checkStopScript();

            $found_size = Tools::substr($line, $pos_cut + 1);
            $tar_name = basename($this->part_list[$this->part_number - 1]);
            $path_in_tar = '';

            // Normalize path
            $current_normalized_file = $this->normalizePath($current_file);

            // Find relative filename
            if (!$path_in_tar) {
                $filename = ltrim(self::getPart($current_normalized_file, $this->base_length), '/');
            } else {
                $filename = ltrim($path_in_tar, '/');
            }

            // File information
            $info = $this->tarFileInfo($current_file);
            $diff_size = 0;

            if ($info['size'] != $found_size) {
                if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::PCLOUD)
                        . ' ' . sprintf(
                            $this->l('The file %1$s has changed of size from %2$s to %3$s', self::PAGE),
                            $current_file,
                            $this->readableSize($found_size) . ' (' . $found_size . ')',
                            $this->readableSize($info['size']) . ' (' . $info['size'] . ')'
                        )
                    );
                }

                $diff_size = $info['size'] - $found_size;
                $info['size'] = $found_size;
            }

            if (in_array($current_file, $this->list_dump_files) && $this->config->type_backup == $this->type_backup_complete) {
                $path_module_backup = 'modules/' . $this->name . '/' . self::BACKUP_FOLDER . '/';
                $path_in_tar = str_replace($this->config_backup_dir, $path_module_backup, $current_file);
            }

            $this->checkStopScript();

            // Create file header
            if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                // Start to add a new file to the tar
                $header = $this->createTarHeader($filename, $info);

                $this->distant_tar_content_size += $this->getHeaderFileSize($filename, false);
                $this->distant_tar_content .= $header;

                ++$this->files_done;

                $this->pos_file_to_tar = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE_CONTINUE;
            }

            if ($info['size'] > 0) {
                // Open the file
                if (($file_read = @fopen($current_file, 'rb')) === false) {
                    // If it is the first opening of the file
                    if ($this->pos_file_to_tar == 0) {
                        $this->log(
                            'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::PCLOUD)
                            . ' ' . $this->l('File', self::PAGE) . ' ' . $current_file . ' '
                            . $this->l('will be empty because the module can not open it, please check its rights and user owner', self::PAGE)
                        );
                    }
                }

                $this->checkStopScript();

                if (is_resource($file_read) && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE_CONTINUE) {
                    $file_read = $this->goToPositionInFile($file_read, $this->pos_file_to_tar);

                    if ($file_read === false) {
                        return false;
                    }
                }

                // Data of the file
                $leftsize = $info['size'] - $this->pos_file_to_tar;
                $blocksize = self::TAR_BLOCK_SIZE;

                while ($leftsize > 0) {
                    $this->checkStopScript();

                    if ($this->next_step == $this->step_send['pcloud']) {
                        // Create new resumable session
                        $create_session = $pcloud_lib->createUpload();

                        if ($create_session === false) {
                            $this->log(
                                'WAR'
                                . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::PCLOUD)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be created', self::PAGE)
                            );

                            return false;
                        }

                        $this->pcloud_position = 0;
                        $this->next_step = $this->step_send['pcloud_resume'];
                    }

                    // If max size for pCloud has been reach, we send the content or the size of the tar was reach
                    if ($this->distant_tar_content_size >= PcloudLib::MAX_FILE_UPLOAD_SIZE
                        || ($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                    ) {
                        $end_of_current_part = 0;

                        if (($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]) {
                            $this->distant_tar_content_size += $tar_end_size;
                            $this->distant_tar_content .= $this->createTarEnd();
                            $end_of_current_part = 1;
                        }

                        if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                            $this->log(
                                $this->l('The total size should not be less that the size we are now at', self::PAGE)
                                . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                            );
                        }

                        // Send content using the current session
                        $position_after_upload = $pcloud_lib->resumeUploadContent(
                            $this->distant_tar_content,
                            $this->distant_tar_content_size,
                            $this->tar_files_size[$this->part_number]
                        );

                        if ($position_after_upload === -1) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::PCLOUD)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be completed', self::PAGE)
                            );

                            return false;
                        } else {
                            $this->pcloud_position = $position_after_upload;
                            $this->distant_tar_content = '';
                            $this->distant_tar_content_size = 0;
                        }

                        if ($end_of_current_part) {
                            // New part, so back to init values
                            ++$this->pcloud_nb_part;
                            ++$this->part_number;

                            $pcloud_lib->endUpload($tar_name, $pcloud->directory_id);

                            $this->next_step = $this->step_send['pcloud'];
                            $this->pcloud_position = 0;
                            $this->old_percent = 0;
                            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;

                            $this->distant_tar_content = $this->createTarStart();
                            $this->distant_tar_content_size += (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
                        }
                    } else {
                        // Read data
                        $leftsize -= $blocksize;

                        if ($leftsize < 0) {
                            $blocksize += $leftsize;
                        }

                        $temp_content = '';

                        if (is_resource($file_read)) {
                            $temp_content = $this->createTarContent($file_read, $blocksize);
                        }

                        // Get where we are in the file
                        $this->pos_file_to_tar += $blocksize;

                        // If there is no content because the file size has changed (smaller file) we need to add \0
                        if ($diff_size < 0 && !$temp_content) {
                            // self::TAR_BLOCK_SIZE since createTarContent
                            // return self::TAR_BLOCK_SIZE size block (using self::pad)
                            $temp_content = self::pad('', self::TAR_BLOCK_SIZE);
                            $diff_size += self::TAR_BLOCK_SIZE;

                            if ($this->config->crypt_backup && $this->config->ignore_compression) {
                                $temp_content = $this->cryptBackup($temp_content);
                                $diff_size += SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;
                            }
                        } elseif ($temp_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::PCLOUD)
                                . ' ' . $this->l('The module was unable to read the file', self::PAGE)
                                . ' ' . $current_file . ', ' . $this->l('please check its rights and user owner', self::PAGE)
                            );

                            if (is_resource($file_read)) {
                                fclose($file_read);
                            }

                            return false;
                        }

                        $this->distant_tar_content .= $temp_content;
                        $this->distant_tar_content_size += self::TAR_BLOCK_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES : 0);

                        $percent = (($this->distant_tar_content_size + $this->pcloud_position) / $this->tar_files_size[$this->part_number]) * 100;

                        if ($percent >= $this->old_percent + 1) {
                            $this->old_percent = round($percent, 0);

                            if ($this->total_nb_part > 1) {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::PCLOUD)
                                    . ' ' . $this->part_number . '/' . $this->total_nb_part . $this->l(':', self::PAGE)
                                    . ' ' . (int) $percent . '%'
                                );
                            } else {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::PCLOUD)
                                    . ' ' . (int) $percent . '%'
                                );
                            }
                        }
                    }

                    // refresh
                    $this->refreshBackup(true);
                }

                // Close file
                if (is_resource($file_read)) {
                    fclose($file_read);
                }
            }

            /*if (basename($filename) == self::TEMP_EMPTY_FILE) {
                @unlink($current_file);
            }*/

            $this->position_file_list_file = ftell($this->handle_file_list_file);

            $this->checkStopScript();

            // Check if tar file size has reach its predicted size
            if (isset($this->tar_files_size[$this->part_number])
                && ($this->pcloud_position + $this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                && $this->pcloud_position < $this->tar_files_size[$this->part_number]
            ) {
                // The tar file will be too big, we need to close it and use a new one
                $this->distant_tar_content .= $this->createTarEnd();
                $this->distant_tar_content_size += $tar_end_size;

                if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                    $this->log(
                        $this->l('The total size should not be less that the size we are now at', self::PAGE)
                        . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                    );
                }

                // Finish sending current tar
                $position_after_upload = $pcloud_lib->resumeUploadContent(
                    $this->distant_tar_content,
                    $this->distant_tar_content_size,
                    $this->tar_files_size[$this->part_number]
                );

                if ($position_after_upload === -1) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::PCLOUD)
                        . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                        . ' ' . $this->l('cannot be finished', self::PAGE)
                    );

                    return false;
                }

                // New part, so back to init values
                ++$this->pcloud_nb_part;
                ++$this->part_number;

                $pcloud_lib->endUpload($tar_name, $pcloud->directory_id);

                $this->next_step = $this->step_send['pcloud'];
                $this->pcloud_position = 0;
                $this->old_percent = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                $this->distant_tar_content = '';
                $this->distant_tar_content_size = 0;

                $this->distant_tar_content = $this->createTarStart();
                $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);

                // refresh
                $this->refreshBackup(true);
            }

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
        }

        // Check that there is no missing file
        if ($this->files_done < $this->total_files) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::PCLOUD)
                . $this->l('Be careful! Not all of your files have been added to the backup. Number of files backuped:', self::PAGE)
                . ' ' . $this->files_done . '/' . $this->total_files
            );

            return false;
        }

        $this->files_done = 0;
        $this->pos_file_to_tar = 0;

        $this->next_step = $this->step_send['pcloud_resume'];

        if ($this->config->send_restore) {
            $file_destination = $pcloud->directory_path . self::NEW_RESTORE_NAME;

            if ($pcloud_lib->checkExists($file_destination)) {
                $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);

                if ($pcloud_lib->deleteItem($file_destination) === false) {
                    $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                }
            }

            $this->log(
                sprintf($this->l('Creating restore file on %s account...', self::PAGE), self::PCLOUD)
            );

            // Upload the file
            if ($pcloud_lib->uploadFile($this->restore_file, $pcloud->directory_id, 1, 1, self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Send a file on a Box account
     *
     * @return bool The success or failure of the operation
     */
    protected function sendFileToBox()
    {
        $box = new BoxNtbr($this->box_account_id);
        $access_token = $box->token;
        $box_dir = $box->directory_key;

        if ($this->next_step == $this->step_send['box']
            && (!isset($this->box_nb_part) || $this->box_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::BOX));
        }

        $box_lib = $this->connectToBox($access_token, $box->id);

        if (!$box_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::BOX)
            );

            return false;
        }

        if ($this->next_step == $this->step_send['box']
            && (!isset($this->box_nb_part) || $this->box_nb_part == 1)
        ) {
            if (Tools::substr($box->directory_path, -1) != '/') {
                $box->directory_path .= '/';
            }

            // If file already on Box
            foreach ($this->part_list as $part) {
                $file_destination = $box->directory_path . basename($part);

                $this->log(
                    $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                );
                $id_file = $box_lib->checkExists(basename($part), $box_dir);

                if ($id_file !== false) {
                    $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                    if ($box_lib->deleteFile($id_file) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                    }
                }
            }

            // Get old backups list
            $old_backups = $this->getBoxFiles($box_lib, $box_dir);
            $nb_backup_to_keep = $box->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            // Get available size
            $available_space = $box_lib->getAvailableQuota();

            if ($available_space != '-1') {
                $available_space += $size_old_backups;

                // Get size to upload
                $to_send_size = $this->total_size;

                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);
                    $to_send_size += $total_file_size;
                }

                // Check if we will have enough size after deleting old backup, too add new backup
                if ($available_space < $to_send_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::BOX) . ' '
                        . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                    );

                    return false;
                }
            }

            // Delete old backup
            if (!$this->deleteBoxOldBackup($box_lib, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::BOX)
                    . ' ' . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            // Check available space
            $new_available_space = $box_lib->getAvailableQuota();

            if ($this->config->send_restore) {
                $total_file_size = $this->getFileSize($this->restore_file);
                $new_available_space -= $total_file_size;
            }

            if ($new_available_space <= $this->total_size) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::BOX)
                    . ' ' . $this->l('Not enough space available', self::PAGE)
                );

                return false;
            }

            $this->log(sprintf($this->l('Sending backup to %s account...', self::PAGE), self::BOX));
            $this->box_nb_part = 1;
            $this->box_position = 0;
        }

        $nb_part = 1;
        // Upload the file
        foreach ($this->part_list as $part) {
            if ($nb_part == $this->box_nb_part) {
                if ($this->next_step == $this->step_send['box']) {
                    $this->next_step = $this->step_send['box_resume'];

                    // Upload the file
                    $upload_file = $box_lib->uploadFile(
                        $part,
                        $box_dir,
                        '',
                        $this->box_nb_part,
                        $this->total_nb_part
                    );

                    if ($upload_file === false) {
                        return false;
                    }
                } else {
                    // Resume upload of the file
                    $resume_upload_file = $box_lib->resumeUploadFile(
                        $part,
                        $this->box_nb_part,
                        $this->total_nb_part
                    );

                    if ($resume_upload_file === false) {
                        return false;
                    }
                }
                ++$this->box_nb_part;
                // New part, so back to init values
                $this->next_step = $this->step_send['box'];
                $this->box_position = 0;
            }

            ++$nb_part;
        }

        $this->next_step = $this->step_send['box_resume'];

        if ($this->config->send_restore) {
            if (Tools::substr($box->directory_path, -1) != '/') {
                $box->directory_path .= '/';
            }

            $file_destination = $box->directory_path . self::NEW_RESTORE_NAME;
            $id_file = $box_lib->checkExists(self::NEW_RESTORE_NAME, $box);

            if ($id_file !== false) {
                $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                if ($box_lib->deleteFile($id_file) === false) {
                    $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                }
            }

            $this->log(
                sprintf($this->l('Sending restore file to %s account...', self::PAGE), self::BOX)
            );

            // Upload the file
            if ($box_lib->uploadFile($this->restore_file, $box_dir, self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a tar file on a Box account
     *
     * @return bool The success or failure of the operation
     */
    protected function createTarOnBox()
    {
        $box = new BoxNtbr($this->box_account_id);
        $access_token = $box->token;
        $box_dir = $box->directory_key;
        $tar_end_size = self::TAR_END_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? (SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES * 2) : 0);

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['box']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->box_nb_part) || $this->box_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::BOX));

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
            $this->files_done = 0;
            $this->position_file_list_file = 0;
            $this->part_number = 1;
            $this->old_percent = 0;
            $tar_name = basename($this->part_list[$this->part_number - 1]);

            if (count($this->part_list) != count($this->tar_files_size)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX)
                    . ' ' . $this->l('The backup cannot be created', self::PAGE)
                );

                return false;
            }

            if (!isset($this->tar_files_size[$this->part_number]) || !$this->tar_files_size[$this->part_number]) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX) . ' '
                    . sprintf($this->l('The calculated size of the file %s is not valid', self::PAGE), $tar_name)
                );

                return false;
            }
        }

        $this->checkStopScript();

        $box_lib = $this->connectToBox($access_token, $box->id);

        if (!$box_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::BOX)
            );

            return false;
        }

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['box']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->box_nb_part) || $this->box_nb_part == 1)
        ) {
            if (Tools::substr($box->directory_path, -1) != '/') {
                $box->directory_path .= '/';
            }

            // Get old backups list
            $old_backups = $this->getBoxFiles($box_lib, $box_dir);
            $nb_backup_to_keep = $box->config_nb_backup;
            $nb_files = count($old_backups);
            $size_old_backups = 0;

            $this->checkStopScript();

            // Get old backup (to delete) size
            if ($nb_backup_to_keep > 0) {
                while ($nb_files >= $nb_backup_to_keep) {
                    $this->checkStopScript();

                    if (isset($old_backups[$nb_files])) {
                        $size_old_backups += $old_backups[$nb_files]['size_byte'];

                        --$nb_files;
                    }
                }
            }

            $this->checkStopScript();

            // Get available size
            $available_space = $box_lib->getAvailableQuota();

            $this->checkStopScript();

            if ($available_space != '-1') {
                $available_space += $size_old_backups;

                // Get size to upload
                $to_send_size = $this->total_size;

                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);

                    $this->checkStopScript();

                    $to_send_size += $total_file_size;
                }

                // Check if we will have enough size after deleting old backup, too add new backup
                if ($available_space < $to_send_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX) . ' '
                        . $this->l('Error there will not be enough space available, even if we delete the old backup', self::PAGE)
                    );

                    return false;
                }
            }

            $this->checkStopScript();

            // Delete old backup
            if (!$this->deleteBoxOldBackup($box_lib, $old_backups)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX)
                    . ' ' . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            $this->checkStopScript();

            // Check available space
            $new_available_space = $box_lib->getAvailableQuota();

            $this->checkStopScript();

            if ($new_available_space != '-1') {
                if ($this->config->send_restore) {
                    $total_file_size = $this->getFileSize($this->restore_file);

                    $this->checkStopScript();

                    $new_available_space -= $total_file_size;
                }

                if ($new_available_space <= $this->total_size) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX)
                        . ' ' . $this->l('Not enough space available', self::PAGE)
                    );

                    return false;
                }
            }

            $this->log(sprintf($this->l('Creating backup on %s account...', self::PAGE), self::BOX));

            $this->box_nb_part = 1;
            $this->box_position = 0;
            $this->distant_tar_content = '';
            $this->distant_tar_content_size = 0;

            $this->distant_tar_content = $this->createTarStart();
            $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
        }

        $this->checkStopScript();

        if ($this->getFileSize($this->file_list_file) <= 0) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX)
                . ' ' . $this->l('No file to backup', self::PAGE)
            );

            return false;
        }

        $this->checkStopScript();

        if (!is_resource($this->handle_file_list_file)) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX)
                . ' ' . $this->l('Error while getting the files to backup', self::PAGE)
            );

            return false;
        }

        $this->handle_file_list_file = $this->goToPositionInFile(
            $this->handle_file_list_file,
            $this->position_file_list_file,
            false
        );

        $this->checkStopScript();

        while (!feof($this->handle_file_list_file)) {
            $this->checkStopScript();

            $line = rtrim(fgets($this->handle_file_list_file));

            if (!$line) {
                continue;
            }

            $pos_cut = strrpos($line, ':');

            if ($pos_cut === false) {
                continue;
            }

            $current_file = self::binaryToString(Tools::substr($line, 0, $pos_cut));

            if (!$current_file) {
                continue;
            }

            // $this->log($current_file, true);

            $this->checkStopScript();

            $found_size = Tools::substr($line, $pos_cut + 1);
            $tar_name = basename($this->part_list[$this->part_number - 1]);
            $path_in_tar = '';

            // Normalize path
            $current_normalized_file = $this->normalizePath($current_file);

            // Find relative filename
            if (!$path_in_tar) {
                $filename = ltrim(self::getPart($current_normalized_file, $this->base_length), '/');
            } else {
                $filename = ltrim($path_in_tar, '/');
            }

            // File information
            $info = $this->tarFileInfo($current_file);
            $diff_size = 0;

            if ($info['size'] != $found_size) {
                if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX)
                        . ' ' . sprintf(
                            $this->l('The file %1$s has changed of size from %2$s to %3$s', self::PAGE),
                            $current_file,
                            $this->readableSize($found_size) . ' (' . $found_size . ')',
                            $this->readableSize($info['size']) . ' (' . $info['size'] . ')'
                        )
                    );
                }

                $diff_size = $info['size'] - $found_size;
                $info['size'] = $found_size;
            }

            if (in_array($current_file, $this->list_dump_files) && $this->config->type_backup == $this->type_backup_complete) {
                $path_module_backup = 'modules/' . $this->name . '/' . self::BACKUP_FOLDER . '/';
                $path_in_tar = str_replace($this->config_backup_dir, $path_module_backup, $current_file);
            }

            $this->checkStopScript();

            // Create file header
            if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                // Start to add a new file to the tar
                $header = $this->createTarHeader($filename, $info);

                $this->distant_tar_content_size += $this->getHeaderFileSize($filename, false);
                $this->distant_tar_content .= $header;

                ++$this->files_done;

                $this->pos_file_to_tar = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE_CONTINUE;
            }

            if ($info['size'] > 0) {
                // Open the file
                if (($file_read = @fopen($current_file, 'rb')) === false) {
                    // If it is the first opening of the file
                    if ($this->pos_file_to_tar == 0) {
                        $this->log(
                            'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX)
                            . ' ' . $this->l('File', self::PAGE) . ' ' . $current_file . ' '
                            . $this->l('will be empty because the module can not open it, please check its rights and user owner', self::PAGE)
                        );
                    }
                }

                $this->checkStopScript();

                if (is_resource($file_read) && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE_CONTINUE) {
                    $file_read = $this->goToPositionInFile($file_read, $this->pos_file_to_tar);

                    if ($file_read === false) {
                        return false;
                    }
                }

                // Data of the file
                $leftsize = $info['size'] - $this->pos_file_to_tar;
                $blocksize = self::TAR_BLOCK_SIZE;

                while ($leftsize > 0) {
                    $this->checkStopScript();

                    if ($this->next_step == $this->step_send['box']) {
                        // Create new resumable session
                        $create_session = $box_lib->createSession(
                            $box_dir,
                            $tar_name,
                            $this->tar_files_size[$this->part_number]
                        );

                        if ($create_session === false) {
                            $this->log(
                                'WAR'
                                . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be created', self::PAGE)
                            );

                            return false;
                        }

                        $this->box_position = 0;
                        $this->next_step = $this->step_send['box_resume'];
                    }

                    // If max size for Box has been reach, we send the content or the size of the tar was reach
                    if ($this->distant_tar_content_size >= BoxLib::MAX_FILE_UPLOAD_SIZE
                        || ($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                    ) {
                        $end_of_current_part = 0;

                        if (($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]) {
                            $this->distant_tar_content_size += $tar_end_size;
                            $this->distant_tar_content .= $this->createTarEnd();
                            $end_of_current_part = 1;
                        }

                        if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                            $this->log(
                                $this->l('The total size should not be less that the size we are now at', self::PAGE)
                                . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                            );
                        }

                        // Send content using the current session
                        $position_after_upload = $box_lib->resumeUploadContent(
                            $this->distant_tar_content,
                            $this->distant_tar_content_size,
                            $this->tar_files_size[$this->part_number]
                        );

                        if ($position_after_upload === -1) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be completed', self::PAGE)
                            );

                            return false;
                        } else {
                            // Calcul what should be the new position
                            $new_position = $this->box_position + $this->distant_tar_content_size;
                            $this->box_position = $position_after_upload;

                            // If Box did not upload everything,
                            // we need to put back what is left in content and content size
                            if ($new_position != $position_after_upload) {
                                $diff = $new_position - $position_after_upload;
                                $end_of_current_part = 0;

                                $this->distant_tar_content_size = $diff;

                                // Use Apparatus::substr instead of Tools::substr
                                // so than it will cut using octets instead of characters
                                $this->distant_tar_content = Apparatus::substr(
                                    $this->distant_tar_content,
                                    $this->distant_tar_content_size * -1
                                );
                            } else {
                                $this->distant_tar_content = '';
                                $this->distant_tar_content_size = 0;
                            }
                        }

                        if ($end_of_current_part) {
                            // New part, so back to init values
                            ++$this->box_nb_part;
                            ++$this->part_number;

                            $this->next_step = $this->step_send['box'];
                            $this->box_position = 0;
                            $this->old_percent = 0;
                            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;

                            $this->distant_tar_content = $this->createTarStart();
                            $this->distant_tar_content_size += (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
                        }
                    } else {
                        // Read data
                        $leftsize -= $blocksize;

                        if ($leftsize < 0) {
                            $blocksize += $leftsize;
                        }

                        $temp_content = '';

                        if (is_resource($file_read)) {
                            $temp_content = $this->createTarContent($file_read, $blocksize);
                        }

                        // Get where we are in the file
                        $this->pos_file_to_tar += $blocksize;

                        // If there is no content because the file size has changed (smaller file) we need to add \0
                        if ($diff_size < 0 && !$temp_content) {
                            // self::TAR_BLOCK_SIZE since createTarContent
                            // return self::TAR_BLOCK_SIZE size block (using self::pad)
                            $temp_content = self::pad('', self::TAR_BLOCK_SIZE);
                            $diff_size += self::TAR_BLOCK_SIZE;

                            if ($this->config->crypt_backup && $this->config->ignore_compression) {
                                $temp_content = $this->cryptBackup($temp_content);
                                $diff_size += SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;
                            }
                        } elseif ($temp_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX)
                                . ' ' . $this->l('The module was unable to read the file', self::PAGE)
                                . ' ' . $current_file . ', ' . $this->l('please check its rights and user owner', self::PAGE)
                            );

                            if (is_resource($file_read)) {
                                fclose($file_read);
                            }

                            return false;
                        }

                        $this->distant_tar_content .= $temp_content;
                        $this->distant_tar_content_size += self::TAR_BLOCK_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES : 0);

                        $percent = (($this->distant_tar_content_size + $this->box_position) / $this->tar_files_size[$this->part_number]) * 100;

                        if ($percent >= $this->old_percent + 1) {
                            $this->old_percent = round($percent, 0);

                            if ($this->total_nb_part > 1) {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX)
                                    . ' ' . $this->part_number . '/' . $this->total_nb_part . $this->l(':', self::PAGE)
                                    . ' ' . (int) $percent . '%'
                                );
                            } else {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX)
                                    . ' ' . (int) $percent . '%'
                                );
                            }
                        }
                    }

                    // refresh
                    $this->refreshBackup(true);
                }

                // Close file
                if (is_resource($file_read)) {
                    fclose($file_read);
                }
            }

            /*if (basename($filename) == self::TEMP_EMPTY_FILE) {
                @unlink($current_file);
            }*/

            $this->position_file_list_file = ftell($this->handle_file_list_file);

            $this->checkStopScript();

            // Check if tar file size has reach its predicted size
            if (isset($this->tar_files_size[$this->part_number])
                && ($this->box_position + $this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                && $this->box_position < $this->tar_files_size[$this->part_number]
            ) {
                // The tar file will be too big, we need to close it and use a new one
                $this->distant_tar_content .= $this->createTarEnd();
                $this->distant_tar_content_size += $tar_end_size;

                if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                    $this->log(
                        $this->l('The total size should not be less that the size we are now at', self::PAGE)
                        . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                    );
                }

                // Finish sending current tar
                $position_after_upload = $box_lib->resumeUploadContent(
                    $this->distant_tar_content,
                    $this->distant_tar_content_size,
                    $this->tar_files_size[$this->part_number]
                );

                if ($position_after_upload === -1) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX)
                        . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                        . ' ' . $this->l('cannot be finished', self::PAGE)
                    );

                    return false;
                }

                // New part, so back to init values
                ++$this->box_nb_part;
                ++$this->part_number;

                $this->next_step = $this->step_send['box'];
                $this->box_position = 0;
                $this->old_percent = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                $this->distant_tar_content = '';
                $this->distant_tar_content_size = 0;

                $this->distant_tar_content = $this->createTarStart();
                $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);

                // refresh
                $this->refreshBackup(true);
            }

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
        }

        // Check that there is no missing file
        if ($this->files_done < $this->total_files) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::BOX)
                . $this->l('Be careful! Not all of your files have been added to the backup. Number of files backuped:', self::PAGE)
                . ' ' . $this->files_done . '/' . $this->total_files
            );

            return false;
        }

        $this->files_done = 0;
        $this->pos_file_to_tar = 0;

        $this->next_step = $this->step_send['box_resume'];

        if ($this->config->send_restore) {
            if (Tools::substr($box->directory_path, -1) != '/') {
                $box->directory_path .= '/';
            }

            $file_destination = $box->directory_path . self::NEW_RESTORE_NAME;
            $id_file = $box_lib->checkExists(self::NEW_RESTORE_NAME, $box_dir);

            if ($id_file !== false) {
                $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);

                if ($box_lib->deleteFile($id_file) === false) {
                    $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                }
            }

            $this->log(
                sprintf($this->l('Creating restore file on %s account...', self::PAGE), self::BOX)
            );

            // Upload the file
            if ($box_lib->uploadFile($this->restore_file, $box_dir, self::NEW_RESTORE_NAME) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Send a file on a AWS account
     *
     * @return bool The success or failure of the operation
     */
    protected function sendFileToAws()
    {
        $aws = new AwsNtbr($this->aws_account_id);
        $aws_directory_key = $aws->directory_key;
        $prefix = $this->correctFileName($this->getConfig('PS_SHOP_NAME'));

        if (false === $aws->bucket || '' == $aws->bucket) {
            $this->log('WAR' . $this->l('No bucket configured', self::PAGE));

            return false;
        }

        if (false === $aws->host || '' == $aws->host) {
            $this->log('WAR' . $this->l('No host configured', self::PAGE));

            return false;
        }

        if (false === $aws->type_s3 || '' == $aws->type_s3 || 0 == $aws->type_s3) {
            $this->log('WAR' . $this->l('No type configured', self::PAGE));

            return false;
        }

        if (false === $aws->storage_class
            || '' == $aws->storage_class
            || !in_array($aws->storage_class, [
                AwsLib::STORAGE_CLASS_STANDARD,
                AwsLib::STORAGE_CLASS_REDUCED_REDUNDANCY,
                AwsLib::STORAGE_CLASS_STANDARD_IA,
                AwsLib::STORAGE_CLASS_ONEZONE_IA,
                AwsLib::STORAGE_CLASS_INTELLIGENT_TIERING,
                AwsLib::STORAGE_CLASS_GLACIER,
                AwsLib::STORAGE_CLASS_DEEP_ARCHIVE,
            ])
        ) {
            $aws->storage_class = AwsLib::STORAGE_CLASS_STANDARD;
        }

        if ($this->next_step == $this->step_send['aws'] && (!isset($this->aws_nb_part) || $this->aws_nb_part == 1)) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::AWS));
        }

        $aws_lib = $this->connectToAws(
            $this->decrypt($aws->access_key_id),
            $this->decrypt($aws->secret_access_key),
            $aws->region,
            $aws->bucket,
            $aws->host,
            $aws->type_s3,
            $aws->accept_unvalid_ssl
        );

        if (!$aws_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::AWS)
            );

            return false;
        }

        if (Tools::substr($aws_directory_key, -1) != '/') {
            $aws_directory_key .= '/';
        }

        if ($this->next_step == $this->step_send['aws'] && (!isset($this->aws_nb_part) || $this->aws_nb_part == 1)) {
            // Check if directory exists
            if ($aws->directory_key != $aws->bucket) {
                $dir_exists = $aws_lib->checkDirectoryExists($aws->directory_key);

                if ($dir_exists === false) {
                    $this->log(
                        'WAR' . $this->l('Error while creating your file: directory unknow', self::PAGE)
                        . ' (' . $aws->directory_path . ')'
                    );

                    return false;
                }
            }

            // If file already on Aws
            foreach ($this->part_list as $part) {
                $file_destination = $aws_directory_key . basename($part);

                $this->log(
                    $this->l('Check if there is a previous version of the file:', self::PAGE) . ' ' . $file_destination
                );

                $file_exists = $aws_lib->checkFileExists(basename($part), $aws_directory_key);

                if ($file_exists !== false) {
                    $this->log($this->l('Delete previous version of the file:', self::PAGE) . ' ' . $file_destination);
                    if ($aws_lib->deleteFile($file_destination) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);
                    }
                }
            }

            // Delete old backup
            if (!$this->deleteAwsOldBackup($aws_lib)) {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::AWS) . ' '
                    . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            $this->log(sprintf($this->l('Sending backup to %s account...', self::PAGE), self::AWS));
            $this->aws_nb_part = 1;
            $this->aws_upload_part = 1;
            $this->aws_position = 0;
            $this->aws_etag = [];
        } else {
            if (!$this->aws_upload_id || $this->aws_upload_id == '') {
                $this->log(
                    'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::AWS) . ' '
                    . $this->l('Invalid upload ID', self::PAGE)
                );

                return false;
            }
        }

        $nb_part = 1;

        // Upload the file
        foreach ($this->part_list as $part) {
            if ($nb_part == $this->aws_nb_part) {
                $file_name = basename($part);
                $file_path = $part;

                if ($this->next_step == $this->step_send['aws']) {
                    $this->next_step = $this->step_send['aws_resume'];

                    // Upload the file
                    $upload_file = $aws_lib->uploadFile(
                        $file_name,
                        $file_path,
                        $aws_directory_key,
                        $aws->storage_class,
                        $this->aws_upload_part,
                        $this->aws_nb_part,
                        $this->total_nb_part,
                        $prefix
                    );

                    if ($upload_file === false) {
                        $this->log(
                            'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::AWS) . ' '
                            . $this->l('Upload failed', self::PAGE)
                        );

                        return false;
                    }
                } else {
                    // Resume upload of the file
                    $resume_upload_file = $aws_lib->resumeUploadFile(
                        $this->aws_upload_id,
                        $file_name,
                        $aws_directory_key,
                        $file_path,
                        $this->aws_upload_part,
                        $this->aws_nb_part,
                        $this->total_nb_part,
                        $this->aws_position
                    );

                    if ($resume_upload_file === false) {
                        $this->log(
                            'WAR' . sprintf($this->l('Sending backup to %s account:', self::PAGE), self::AWS)
                            . ' ' . $this->l('Upload failed', self::PAGE)
                        );

                        return false;
                    }
                }
                ++$this->aws_nb_part;
                // New part, so back to init values
                $this->next_step = $this->step_send['aws'];
                $this->aws_upload_part = 1;
                $this->aws_position = 0;
                $this->aws_etag = [];
            }
            ++$nb_part;
        }

        $this->next_step = $this->step_send['aws_resume'];

        if ($this->config->send_restore) {
            $this->aws_nb_part = 1;
            $nb_part_total = 1;
            $this->aws_upload_part = 1;
            $this->aws_position = 0;
            $this->aws_etag = [];

            $this->log(
                sprintf($this->l('Sending restore file to %s account...', self::PAGE), self::AWS)
            );

            // Upload the file
            $upload_file = $aws_lib->uploadFile(
                self::NEW_RESTORE_NAME,
                $this->restore_file,
                $aws_directory_key,
                $aws->storage_class,
                $this->aws_upload_part,
                $this->aws_nb_part,
                $nb_part_total,
                self::NEW_RESTORE_NAME
            );

            if ($upload_file === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a tar file on a AWS account
     *
     * @return bool The success or failure of the operation
     */
    protected function createTarOnAws()
    {
        $aws = new AwsNtbr($this->aws_account_id);
        $aws_directory_key = $aws->directory_key;
        $prefix = $this->correctFileName($this->getConfig('PS_SHOP_NAME'));
        $tar_end_size = self::TAR_END_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? (SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES * 2) : 0);

        if (false === $aws->bucket || '' == $aws->bucket) {
            $this->log('WAR' . $this->l('No bucket configured', self::PAGE));

            return false;
        }

        if (false === $aws->host || '' == $aws->host) {
            $this->log('WAR' . $this->l('No host configured', self::PAGE));

            return false;
        }

        if (false === $aws->type_s3 || '' == $aws->type_s3 || 0 == $aws->type_s3) {
            $this->log('WAR' . $this->l('No type configured', self::PAGE));

            return false;
        }

        if (false === $aws->storage_class
            || '' == $aws->storage_class
            || !in_array($aws->storage_class, [
                AwsLib::STORAGE_CLASS_STANDARD,
                AwsLib::STORAGE_CLASS_REDUCED_REDUNDANCY,
                AwsLib::STORAGE_CLASS_STANDARD_IA,
                AwsLib::STORAGE_CLASS_ONEZONE_IA,
                AwsLib::STORAGE_CLASS_INTELLIGENT_TIERING,
                AwsLib::STORAGE_CLASS_GLACIER,
                AwsLib::STORAGE_CLASS_DEEP_ARCHIVE,
            ])
        ) {
            $aws->storage_class = AwsLib::STORAGE_CLASS_STANDARD;
        }

        $this->checkStopScript();

        if (Tools::substr($aws_directory_key, -1) != '/') {
            $aws_directory_key .= '/';
        }

        if ($this->next_step == $this->step_send['aws']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->aws_nb_part) || $this->aws_nb_part == 1)
        ) {
            $this->log(sprintf($this->l('Connect to your %s account...', self::PAGE), self::AWS));

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
            $this->files_done = 0;
            $this->position_file_list_file = 0;
            $this->part_number = 1;
            $this->old_percent = 0;
            $tar_name = basename($this->part_list[$this->part_number - 1]);

            if (count($this->part_list) != count($this->tar_files_size)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS)
                    . ' ' . $this->l('The backup cannot be created', self::PAGE)
                );

                return false;
            }

            if (!isset($this->tar_files_size[$this->part_number]) || !$this->tar_files_size[$this->part_number]) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS) . ' '
                    . sprintf($this->l('The calculated size of the file %s is not valid', self::PAGE), $tar_name)
                );

                return false;
            }
        }

        $this->checkStopScript();

        $aws_lib = $this->connectToAws(
            $this->decrypt($aws->access_key_id),
            $this->decrypt($aws->secret_access_key),
            $aws->region,
            $aws->bucket,
            $aws->host,
            $aws->type_s3,
            $aws->accept_unvalid_ssl
        );

        if (!$aws_lib->testConnection()) {
            $this->log(
                'WAR' . sprintf($this->l('Connection to your %s account impossible', self::PAGE), self::AWS)
            );

            return false;
        }

        $this->checkStopScript();

        if ($this->next_step == $this->step_send['aws']
            && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE
            && (!isset($this->aws_nb_part) || $this->aws_nb_part == 1)
        ) {
            // Check if directory exists
            if ($aws->directory_key != $aws->bucket) {
                $dir_exists = $aws_lib->checkDirectoryExists($aws->directory_key);

                if ($dir_exists === false) {
                    $this->log(
                        'WAR' . $this->l('Error while creating your file: directory unknow', self::PAGE)
                        . ' (' . $aws->directory_path . ')'
                    );

                    return false;
                }
            }

            // Delete old backup
            if (!$this->deleteAwsOldBackup($aws_lib)) {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS) . ' '
                    . $this->l('Error while deleting old backup', self::PAGE)
                );

                return false;
            }

            $this->checkStopScript();

            $this->log(sprintf($this->l('Creating backup on %s account...', self::PAGE), self::AWS));

            $this->aws_nb_part = 1;
            $this->aws_position = 0;
            $this->distant_tar_content = '';
            $this->distant_tar_content_size = 0;
            $this->aws_upload_part = 1;
            $this->aws_etag = [];

            $this->distant_tar_content = $this->createTarStart();
            $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
        } elseif ($this->next_step == $this->step_send['aws_resume']) {
            if (!$this->aws_upload_id || $this->aws_upload_id == '') {
                $this->log(
                    'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS) . ' '
                    . $this->l('Invalid upload ID', self::PAGE)
                );

                return false;
            }
        }

        $this->checkStopScript();

        if ($this->getFileSize($this->file_list_file) <= 0) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS)
                . ' ' . $this->l('No file to backup', self::PAGE)
            );

            return false;
        }

        $this->checkStopScript();

        if (!is_resource($this->handle_file_list_file)) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS)
                . ' ' . $this->l('Error while getting the files to backup', self::PAGE)
            );

            return false;
        }

        $this->handle_file_list_file = $this->goToPositionInFile(
            $this->handle_file_list_file,
            $this->position_file_list_file,
            false
        );

        $this->checkStopScript();

        while (!feof($this->handle_file_list_file)) {
            $this->checkStopScript();

            $line = rtrim(fgets($this->handle_file_list_file));

            if (!$line) {
                continue;
            }

            $pos_cut = strrpos($line, ':');

            if ($pos_cut === false) {
                continue;
            }

            $current_file = self::binaryToString(Tools::substr($line, 0, $pos_cut));

            if (!$current_file) {
                continue;
            }

            $this->checkStopScript();

            $found_size = Tools::substr($line, $pos_cut + 1);
            $tar_name = basename($this->part_list[$this->part_number - 1]);
            $path_in_tar = '';

            // Normalize path
            $current_normalized_file = $this->normalizePath($current_file);

            // Find relative filename
            if (!$path_in_tar) {
                $filename = ltrim(self::getPart($current_normalized_file, $this->base_length), '/');
            } else {
                $filename = ltrim($path_in_tar, '/');
            }

            // File information
            $info = $this->tarFileInfo($current_file);
            $diff_size = 0;

            if ($info['size'] != $found_size) {
                if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS)
                        . ' ' . sprintf(
                            $this->l('The file %1$s has changed of size from %2$s to %3$s', self::PAGE),
                            $current_file,
                            $this->readableSize($found_size) . ' (' . $found_size . ')',
                            $this->readableSize($info['size']) . ' (' . $info['size'] . ')'
                        )
                    );
                }

                $diff_size = $info['size'] - $found_size;
                $info['size'] = $found_size;
            }

            if (in_array($current_file, $this->list_dump_files) && $this->config->type_backup == $this->type_backup_complete) {
                $path_module_backup = 'modules/' . $this->name . '/' . self::BACKUP_FOLDER . '/';
                $path_in_tar = str_replace($this->config_backup_dir, $path_module_backup, $current_file);
            }

            $this->checkStopScript();

            // Create file header
            if ($this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE) {
                // Start to add a new file to the tar
                $header = $this->createTarHeader($filename, $info);

                $this->distant_tar_content_size += $this->getHeaderFileSize($filename, false);
                $this->distant_tar_content .= $header;

                ++$this->files_done;

                $this->pos_file_to_tar = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE_CONTINUE;
            }

            if ($info['size'] > 0) {
                // Open the file
                if (($file_read = @fopen($current_file, 'rb')) === false) {
                    // If it is the first opening of the file
                    if ($this->pos_file_to_tar == 0) {
                        $this->log(
                            'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS)
                            . ' ' . $this->l('File', self::PAGE) . ' ' . $current_file . ' '
                            . $this->l('will be empty because the module can not open it, please check its rights and user owner', self::PAGE)
                        );
                    }
                }

                $this->checkStopScript();

                if (is_resource($file_read) && $this->secondary_next_step == self::SECONDARY_STEP_TAR_FILE_CONTINUE) {
                    $file_read = $this->goToPositionInFile($file_read, $this->pos_file_to_tar);

                    if ($file_read === false) {
                        return false;
                    }
                }

                // Data of the file
                $leftsize = $info['size'] - $this->pos_file_to_tar;
                $blocksize = self::TAR_BLOCK_SIZE;

                while ($leftsize > 0) {
                    $this->checkStopScript();

                    if ($this->next_step == $this->step_send['aws']) {
                        // Create new resumable session
                        $create_session = $aws_lib->createSession($tar_name, $aws_directory_key, $aws->storage_class, $prefix);

                        if ($create_session === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be created', self::PAGE)
                            );

                            return false;
                        }

                        $this->aws_position = 0;
                        $this->next_step = $this->step_send['aws_resume'];
                    }

                    // If max size for AWS has been reach, we send the content or the size of the tar was reach
                    if ($this->distant_tar_content_size >= AwsLib::MAX_FILE_UPLOAD_SIZE
                        || ($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                    ) {
                        $end_of_current_part = 0;

                        if (($this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]) {
                            $this->distant_tar_content_size += $tar_end_size;
                            $this->distant_tar_content .= $this->createTarEnd();
                            $end_of_current_part = 1;
                        }

                        if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                            $this->log(
                                $this->l('The total size should not be less that the size we are now at', self::PAGE)
                                . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                            );
                        }

                        // Send content using the current session
                        $resume_upload_content = $aws_lib->resumeUploadContent(
                            $this->distant_tar_content,
                            $this->distant_tar_content_size,
                            $this->tar_files_size[$this->part_number],
                            $tar_name,
                            $aws_directory_key,
                            $this->aws_upload_part
                        );

                        if ($resume_upload_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS)
                                . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                                . ' ' . $this->l('cannot be completed', self::PAGE)
                            );

                            return false;
                        } else {
                            $this->aws_position += $this->distant_tar_content_size;

                            $this->distant_tar_content = '';
                            $this->distant_tar_content_size = 0;
                        }

                        if ($end_of_current_part) {
                            // New part, so back to init values
                            ++$this->aws_nb_part;
                            ++$this->part_number;

                            $this->next_step = $this->step_send['aws'];
                            $this->aws_position = 0;
                            $this->old_percent = 0;
                            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                            $this->aws_upload_part = 1;
                            $this->aws_etag = [];

                            $this->distant_tar_content = $this->createTarStart();
                            $this->distant_tar_content_size += (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);
                        }
                    } else {
                        // Read data
                        $leftsize -= $blocksize;

                        if ($leftsize < 0) {
                            $blocksize += $leftsize;
                        }

                        $temp_content = '';

                        if (is_resource($file_read)) {
                            $temp_content = $this->createTarContent($file_read, $blocksize);
                        }

                        // Get where we are in the file
                        $this->pos_file_to_tar += $blocksize;

                        // If there is no content because the file size has changed (smaller file) we need to add \0
                        if ($diff_size < 0 && !$temp_content) {
                            // self::TAR_BLOCK_SIZE since createTarContent
                            // return self::TAR_BLOCK_SIZE size block (using self::pad)
                            $temp_content = self::pad('', self::TAR_BLOCK_SIZE);
                            $diff_size += self::TAR_BLOCK_SIZE;

                            if ($this->config->crypt_backup && $this->config->ignore_compression) {
                                $temp_content = $this->cryptBackup($temp_content);
                                $diff_size += SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;
                            }
                        } elseif ($temp_content === false) {
                            $this->log(
                                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS)
                                . ' ' . $this->l('The module was unable to read the file', self::PAGE)
                                . ' ' . $current_file . ', ' . $this->l('please check its rights and user owner', self::PAGE)
                            );

                            if (is_resource($file_read)) {
                                fclose($file_read);
                            }

                            return false;
                        }

                        $this->distant_tar_content .= $temp_content;
                        $this->distant_tar_content_size += self::TAR_BLOCK_SIZE + (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES : 0);

                        $percent = (($this->distant_tar_content_size + $this->aws_position) / $this->tar_files_size[$this->part_number]) * 100;

                        if ($percent >= $this->old_percent + 1) {
                            $this->old_percent = round($percent, 0);

                            if ($this->total_nb_part > 1) {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS)
                                    . ' ' . $this->part_number . '/' . $this->total_nb_part . $this->l(':', self::PAGE)
                                    . ' ' . (int) $percent . '%'
                                );
                            } else {
                                $this->log(
                                    sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS)
                                    . ' ' . (int) $percent . '%'
                                );
                            }
                        }
                    }

                    // refresh
                    $this->refreshBackup(true);
                }

                // Close file
                if (is_resource($file_read)) {
                    fclose($file_read);
                }
            }

            /*if (basename($filename) == self::TEMP_EMPTY_FILE) {
                @unlink($current_file);
            }*/

            $this->position_file_list_file = ftell($this->handle_file_list_file);

            $this->checkStopScript();

            // Check if tar file size has reach its predicted size
            if (isset($this->tar_files_size[$this->part_number])
                && ($this->aws_position + $this->distant_tar_content_size + $tar_end_size) >= $this->tar_files_size[$this->part_number]
                && $this->aws_position < $this->tar_files_size[$this->part_number]
            ) {
                // The tar file will be too big, we need to close it and use a new one
                $this->distant_tar_content .= $this->createTarEnd();
                $this->distant_tar_content_size += $tar_end_size;

                if ($this->distant_tar_content_size > $this->tar_files_size[$this->part_number]) {
                    $this->log(
                        $this->l('The total size should not be less that the size we are now at', self::PAGE)
                        . ' (' . $this->distant_tar_content_size . '/' . $this->tar_files_size[$this->part_number]
                    );
                }

                // Finish sending current tar
                $resume_upload_content = $aws_lib->resumeUploadContent(
                    $this->distant_tar_content,
                    $this->distant_tar_content_size,
                    $this->tar_files_size[$this->part_number],
                    $tar_name,
                    $aws_directory_key,
                    $this->aws_upload_part
                );

                if ($resume_upload_content === false) {
                    $this->log(
                        'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS)
                        . ' ' . $this->l('File', self::PAGE) . ' ' . $tar_name
                        . ' ' . $this->l('cannot be finished', self::PAGE)
                    );

                    return false;
                }

                // New part, so back to init values
                ++$this->aws_nb_part;
                ++$this->part_number;

                $this->next_step = $this->step_send['aws'];
                $this->aws_position = 0;
                $this->old_percent = 0;
                $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                $this->distant_tar_content = '';
                $this->distant_tar_content_size = 0;
                $this->aws_upload_part = 1;
                $this->aws_etag = [];

                $this->distant_tar_content = $this->createTarStart();
                $this->distant_tar_content_size = (($this->config->crypt_backup && $this->config->ignore_compression) ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES : 0);

                // refresh
                $this->refreshBackup(true);
            }

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
        }

        // Check that there is no missing file
        if ($this->files_done < $this->total_files) {
            $this->log(
                'WAR' . sprintf($this->l('Creating backup on %s account:', self::PAGE), self::AWS)
                . $this->l('Be careful! Not all of your files have been added to the backup. Number of files backuped:', self::PAGE)
                . ' ' . $this->files_done . '/' . $this->total_files
            );

            return false;
        }

        $this->files_done = 0;
        $this->pos_file_to_tar = 0;
        $this->next_step = $this->step_send['aws_resume'];

        if ($this->config->send_restore) {
            $this->aws_nb_part = 1;
            $nb_part_total = 1;
            $this->aws_upload_part = 1;
            $this->aws_position = 0;
            $this->aws_etag = [];

            $this->log(
                sprintf($this->l('Creating restore file on %s account...', self::PAGE), self::AWS)
            );

            // Upload the file
            $upload_file = $aws_lib->uploadFile(
                self::NEW_RESTORE_NAME,
                $this->restore_file,
                $aws_directory_key,
                $aws->storage_class,
                $this->aws_upload_part,
                $this->aws_nb_part,
                $nb_part_total,
                self::NEW_RESTORE_NAME
            );

            if ($upload_file === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Clean files after creating on distant account
     *
     * @return bool The success or failure of the operation
     */
    protected function cleanAfterCreateOnDistantAccount()
    {
        if ($this->next_step == self::STEP_CLEAN_FILES) {
            $this->files_done = 0;
            $this->position_file_list_file = 0;
        }

        $this->checkStopScript();

        if (!is_resource($this->handle_file_list_file)) {
            return false;
        }

        $this->handle_file_list_file = $this->goToPositionInFile(
            $this->handle_file_list_file,
            $this->position_file_list_file,
            false
        );

        $this->checkStopScript();

        while (!feof($this->handle_file_list_file)) {
            $this->checkStopScript();

            $line = rtrim(fgets($this->handle_file_list_file));

            if (!$line) {
                continue;
            }

            $pos_cut = strrpos($line, ':');

            if ($pos_cut === false) {
                continue;
            }

            $current_file = self::binaryToString(Tools::substr($line, 0, $pos_cut));

            if (!$current_file) {
                continue;
            }

            // Normalize path
            $current_normalized_file = $this->normalizePath($current_file);

            // Find relative filename
            $filename = ltrim(self::getPart($current_normalized_file, $this->base_length), '/');

            if (basename($filename) == self::TEMP_EMPTY_FILE) {
                @unlink($current_file);
            }

            $this->position_file_list_file = ftell($this->handle_file_list_file);

            $this->secondary_next_step = self::STEP_CLEAN_FILES_CONTINUE;
        }

        $this->files_done = 0;
        $this->next_step = self::STEP_FINISH;

        return true;
    }

    /**
     * Delete old backup files on a Dropbox account
     *
     * @param string $token Dropbox token
     * @param array $old_backups Dropbox old backups
     *
     * @return bool The success or failure of the connection
     */
    protected function deleteDropboxOldBackup($token, $old_backups)
    {
        $dropbox = new DropboxNtbr($this->dropbox_account_id);
        $nb_backup_to_keep = $dropbox->config_nb_backup;

        // Do we need to delete old backups
        if ($nb_backup_to_keep == 0) {
            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::DROPBOX) . ' '
                . $this->l('Keep all old backup', self::PAGE)
            );

            return true;
        }

        $this->log(
            sprintf($this->l('Sending backup to %s account:', self::PAGE), self::DROPBOX)
            . ' ' . $this->l('Deleting old backup', self::PAGE)
        );

        $nb_files = count($old_backups);

        // Do we really need to delete old backups
        if ($nb_files < $nb_backup_to_keep) {
            return true;
        }

        $dropbox_lib = $this->connectToDropbox($dropbox->business, $token);
        $success = true;
        $temp_directory = $this->dropbox_dir;

        // Dropbox dir should end with a "/" except when testing if exist
        if (Tools::substr($this->dropbox_dir, -1) != '/') {
            $temp_directory .= '/';
        }

        // Yes we have to delete old backups
        if ($nb_backup_to_keep > 0) {
            while ($nb_files >= $nb_backup_to_keep) {
                foreach ($old_backups[$nb_files]['part'] as $part) {
                    if ($dropbox_lib->deleteFile($temp_directory . basename($part['name'])) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);
                        $success = false;
                    }
                }

                --$nb_files;
            }
        }

        return $success;
    }

    /**
     * Delete old backup files on a Yandex account
     *
     * @param string $access_token Yandex token
     * @param array $old_backups Yandex old backups
     *
     * @return bool The success or failure of the connection
     */
    protected function deleteYandexOldBackup($access_token, $old_backups)
    {
        $yandex = new YandexNtbr($this->yandex_account_id);
        $nb_backup_to_keep = $yandex->config_nb_backup;

        // Do we need to delete old backups
        if ($nb_backup_to_keep == 0) {
            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::YANDEX) . ' '
                . $this->l('Keep all old backup', self::PAGE)
            );

            return true;
        }

        $this->log(
            sprintf($this->l('Sending backup to %s account:', self::PAGE), self::YANDEX)
            . ' ' . $this->l('Deleting old backup', self::PAGE)
        );

        $nb_files = count($old_backups);

        // Do we really need to delete old backups
        if ($nb_files < $nb_backup_to_keep) {
            return true;
        }

        $yandex_lib = $this->connectToYandex($access_token);
        $success = true;
        $temp_directory = $this->yandex_dir;

        // Yandex dir should end with a "/" except when testing if exist
        if (Tools::substr($this->yandex_dir, -1) != '/') {
            $temp_directory .= '/';
        }

        // Yes we have to delete old backups
        if ($nb_backup_to_keep > 0) {
            while ($nb_files >= $nb_backup_to_keep) {
                foreach ($old_backups[$nb_files]['part'] as $part) {
                    if ($yandex_lib->deleteFile($temp_directory . basename($part['name'])) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);
                        $success = false;
                    }
                }

                --$nb_files;
            }
        }

        return $success;
    }

    /**
     * Delete old backup files on a ownCloud/Nextcloud account
     *
     * @param object $owncloud_lib ownCloud to use
     * @param array $old_backups ownCloud old backups
     *
     * @return bool The success or failure of the connection
     */
    protected function deleteOwncloudOldBackup($owncloud_lib, $old_backups)
    {
        $owncloud = new OwncloudNtbr($this->owncloud_account_id);
        $nb_backup_to_keep = $owncloud->config_nb_backup;

        // Do we need to delete old backups
        if ($nb_backup_to_keep == 0) {
            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::OWNCLOUD)
                . ' ' . $this->l('Keep all old backup', self::PAGE)
            );

            return true;
        }

        $this->log(
            sprintf($this->l('Sending backup to %s account:', self::PAGE), self::OWNCLOUD)
            . ' ' . $this->l('Deleting old backup', self::PAGE)
        );

        $nb_files = count($old_backups);

        // Do we really need to delete old backups
        if ($nb_files < $nb_backup_to_keep) {
            return true;
        }

        // Yes we have to delete old backups
        if ($nb_backup_to_keep > 0) {
            while ($nb_files >= $nb_backup_to_keep) {
                foreach ($old_backups[$nb_files]['part'] as $part) {
                    if ($owncloud_lib->deleteFile($part['file_id']) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);
                    }
                }

                --$nb_files;
            }
        }

        return true;
    }

    /**
     * Delete old backup files on a Shadow Drive account
     *
     * @param object $shadow_drive_lib Shadow Drive to use
     * @param array $old_backups Shadow Drive old backups
     *
     * @return bool The success or failure of the connection
     */
    protected function deleteShadowDriveOldBackup($shadow_drive_lib, $old_backups)
    {
        $shadow_drive = new ShadowDriveNtbr($this->shadow_drive_account_id);
        $nb_backup_to_keep = $shadow_drive->config_nb_backup;

        // Do we need to delete old backups
        if ($nb_backup_to_keep == 0) {
            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SHADOW_DRIVE)
                . ' ' . $this->l('Keep all old backup', self::PAGE)
            );

            return true;
        }

        $this->log(
            sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SHADOW_DRIVE)
            . ' ' . $this->l('Deleting old backup', self::PAGE)
        );

        $nb_files = count($old_backups);

        // Do we really need to delete old backups
        if ($nb_files < $nb_backup_to_keep) {
            return true;
        }

        // Yes we have to delete old backups
        if ($nb_backup_to_keep > 0) {
            while ($nb_files >= $nb_backup_to_keep) {
                foreach ($old_backups[$nb_files]['part'] as $part) {
                    if ($shadow_drive_lib->deleteFile($part['file_id']) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);
                    }
                }

                --$nb_files;
            }
        }

        return true;
    }

    /**
     * Delete old backup files on a WebDAV account
     *
     * @param object $webdav_lib WebDAV to use
     * @param array $old_backups WebDAV old backups
     *
     * @return bool The success or failure of the connection
     */
    protected function deleteWebdavOldBackup($webdav_lib, $old_backups)
    {
        $webdav = new WebdavNtbr($this->webdav_account_id);
        $nb_backup_to_keep = $webdav->config_nb_backup;

        // Do we need to delete old backups
        if ($nb_backup_to_keep == 0) {
            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::WEBDAV) . ' '
                . $this->l('Keep all old backup', self::PAGE)
            );

            return true;
        }

        $this->log(
            sprintf($this->l('Sending backup to %s account:', self::PAGE), self::WEBDAV)
            . ' ' . $this->l('Deleting old backup', self::PAGE)
        );

        $nb_files = count($old_backups);

        // Do we really need to delete old backups
        if ($nb_files < $nb_backup_to_keep) {
            return true;
        }

        // Yes we have to delete old backups
        if ($nb_backup_to_keep > 0) {
            while ($nb_files >= $nb_backup_to_keep) {
                foreach ($old_backups[$nb_files]['part'] as $part) {
                    if ($webdav_lib->deleteFile($part['file_id']) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);
                    }
                }

                --$nb_files;
            }
        }

        return true;
    }

    /**
     * Delete old backup files on a Google Drive account
     *
     * @param object $googledrive_lib Google Drive to use
     * @param array $old_backups Google Drive old backups
     *
     * @return bool The success or failure of the connection
     */
    protected function deleteGoogledriveOldBackup($googledrive_lib, $old_backups)
    {
        $googledrive = new GoogledriveNtbr($this->googledrive_account_id);
        $nb_backup_to_keep = $googledrive->config_nb_backup;

        // Do we need to delete old backups
        if ($nb_backup_to_keep == 0) {
            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::GOOGLEDRIVE)
                . ' ' . $this->l('Keep all old backup', self::PAGE)
            );

            return true;
        }

        $this->log(
            sprintf($this->l('Sending backup to %s account:', self::PAGE), self::GOOGLEDRIVE)
            . ' ' . $this->l('Deleting old backup', self::PAGE)
        );

        $nb_files = count($old_backups);

        // Do we really need to delete old backups
        if ($nb_files < $nb_backup_to_keep) {
            return true;
        }

        // Yes we have to delete old backups
        if ($nb_backup_to_keep > 0) {
            while ($nb_files >= $nb_backup_to_keep) {
                foreach ($old_backups[$nb_files]['part'] as $part) {
                    // We delete the file by the ID link to the its name
                    if ($googledrive_lib->deleteFile($part['file_id']) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);
                    }
                }
                --$nb_files;
            }
        }

        return true;
    }

    /**
     * Delete old backup files on a Google Cloud account
     *
     * @param object $googlecloud_lib Google Cloud to use
     * @param array $old_backups Google Cloud old backups
     *
     * @return bool The success or failure of the connection
     */
    protected function deleteGooglecloudOldBackup($googlecloud_lib, $old_backups)
    {
        $googlecloud = new GooglecloudNtbr($this->googlecloud_account_id);
        $nb_backup_to_keep = $googlecloud->config_nb_backup;

        // Do we need to delete old backups
        if ($nb_backup_to_keep == 0) {
            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::GOOGLECLOUD)
                . ' ' . $this->l('Keep all old backup', self::PAGE)
            );

            return true;
        }

        $this->log(
            sprintf($this->l('Sending backup to %s account:', self::PAGE), self::GOOGLECLOUD)
            . ' ' . $this->l('Deleting old backup', self::PAGE)
        );

        $nb_files = count($old_backups);

        // Do we really need to delete old backups
        if ($nb_files < $nb_backup_to_keep) {
            return true;
        }

        $success = true;

        // Dir should end with a "/"
        if (Tools::substr($googlecloud->directory, -1) != '/') {
            $googlecloud->directory .= '/';
        }

        // Yes we have to delete old backups
        if ($nb_backup_to_keep > 0) {
            while ($nb_files >= $nb_backup_to_keep) {
                foreach ($old_backups[$nb_files]['part'] as $part) {
                    // We delete the file by the ID link to the its name
                    if ($googlecloud_lib->deleteItem($googlecloud->directory . basename($part['name'])) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);
                        $success = false;
                    }
                }
                --$nb_files;
            }
        }

        return $success;
    }

    /**
     * Delete old backup files on a pCloud account
     *
     * @param object $pcloud_lib pCloud to use
     * @param array $old_backups pCloud old backups
     *
     * @return bool The success or failure of the connection
     */
    protected function deletePcloudOldBackup($pcloud_lib, $old_backups)
    {
        $pcloud = new PcloudNtbr($this->pcloud_account_id);
        $nb_backup_to_keep = $pcloud->config_nb_backup;

        // Do we need to delete old backups
        if ($nb_backup_to_keep == 0) {
            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::PCLOUD)
                . ' ' . $this->l('Keep all old backup', self::PAGE)
            );

            return true;
        }

        $this->log(
            sprintf($this->l('Sending backup to %s account:', self::PAGE), self::PCLOUD)
            . ' ' . $this->l('Deleting old backup', self::PAGE)
        );

        $nb_files = count($old_backups);

        // Do we really need to delete old backups
        if ($nb_files < $nb_backup_to_keep) {
            return true;
        }

        $success = true;

        // Dir should end with a "/"
        if (Tools::substr($pcloud->directory_path, -1) != '/') {
            $pcloud->directory_path .= '/';
        }

        // Yes we have to delete old backups
        if ($nb_backup_to_keep > 0) {
            while ($nb_files >= $nb_backup_to_keep) {
                foreach ($old_backups[$nb_files]['part'] as $part) {
                    // We delete the file by the ID link to the its name
                    if ($pcloud_lib->deleteItem($pcloud->directory_path . basename($part['name'])) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);
                        $success = false;
                    }
                }
                --$nb_files;
            }
        }

        return $success;
    }

    /**
     * Delete old backup files on a Box account
     *
     * @param object $box_lib Box to use
     * @param array $old_backups Box old backups
     *
     * @return bool The success or failure of the connection
     */
    protected function deleteBoxOldBackup($box_lib, $old_backups)
    {
        $box = new BoxNtbr($this->box_account_id);
        $nb_backup_to_keep = $box->config_nb_backup;

        // Do we need to delete old backups
        if ($nb_backup_to_keep == 0) {
            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::BOX)
                . ' ' . $this->l('Keep all old backup', self::PAGE)
            );

            return true;
        }

        $this->log(
            sprintf($this->l('Sending backup to %s account:', self::PAGE), self::BOX)
            . ' ' . $this->l('Deleting old backup', self::PAGE)
        );

        $nb_files = count($old_backups);

        // Do we really need to delete old backups
        if ($nb_files < $nb_backup_to_keep) {
            return true;
        }

        // Yes we have to delete old backups
        if ($nb_backup_to_keep > 0) {
            while ($nb_files >= $nb_backup_to_keep) {
                foreach ($old_backups[$nb_files]['part'] as $part) {
                    // We delete the file by the ID link to the its name
                    if ($box_lib->deleteFile($part['file_id']) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);
                    }
                }
                --$nb_files;
            }
        }

        return true;
    }

    /**
     * Delete old backup files on a OneDrive account
     *
     * @param object $onedrive_lib OneDrive to use
     * @param array $old_backups OneDrive old backups
     *
     * @return bool The success or failure of the connection
     */
    protected function deleteOnedriveOldBackup($onedrive_lib, $old_backups)
    {
        $onedrive = new OnedriveNtbr($this->onedrive_account_id);
        $nb_backup_to_keep = $onedrive->config_nb_backup;
        // $onedrive->directory_key

        // Do we need to delete old backups
        if ($nb_backup_to_keep == 0) {
            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::ONEDRIVE)
                . ' ' . $this->l('Keep all old backup', self::PAGE)
            );

            return true;
        }

        $this->log(
            sprintf($this->l('Sending backup to %s account:', self::PAGE), self::ONEDRIVE)
            . ' ' . $this->l('Deleting old backup', self::PAGE)
        );

        $nb_files = count($old_backups);

        // Do we really need to delete old backups
        if ($nb_files < $nb_backup_to_keep) {
            return true;
        }

        // Yes we have to delete old backups
        if ($nb_backup_to_keep > 0) {
            while ($nb_files >= $nb_backup_to_keep) {
                foreach ($old_backups[$nb_files]['part'] as $part) {
                    if (!$onedrive_lib->deleteItem($part['file_id'])) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);
                    }
                }
                --$nb_files;
            }
        }

        return true;
    }

    /**
     * Delete old backup files on a SugarSync account
     *
     * @param object $sugarsync_lib SugarSync to use
     * @param string $id_directory SugarSync directory ID
     *
     * @return bool The success or failure of the connection
     */
    protected function deleteSugarsyncOldBackup($sugarsync_lib, $id_directory)
    {
        $sugarsync = new SugarsyncNtbr($this->sugarsync_account_id);
        $nb_backup_to_keep = $sugarsync->config_nb_backup;

        // Do we need to delete old backups
        if ($nb_backup_to_keep == 0) {
            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SUGARSYNC)
                . ' ' . $this->l('Keep all old backup', self::PAGE)
            );

            return true;
        }

        $this->log(
            sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SUGARSYNC)
            . ' ' . $this->l('Deleting old backup', self::PAGE)
        );

        $files = $sugarsync_lib->getListFiles($id_directory);

        if (!is_array($files) || !count($files)) {
            return true; // No file to delete
        }

        $old_backups = [];
        $old_backups_sort = [];

        foreach ($files as $file) {
            if (strpos($file['displayName'], '.' . self::EXT_UNCOMPRESS . '.') !== false
                || (
                    $this->config->send_restore
                    && strpos($file['displayName'], self::NEW_RESTORE_NAME) !== false
                )
            ) {
                if (strpos($file['displayName'], '.' . $this->config->type_backup) !== false) {
                    $old_backups[basename($file['ref'])] = $file['displayName'];
                }
            }
        }

        $clean_list_old_backups = $this->cleanListBackup($old_backups);

        foreach ($old_backups as $id_file => $name_file) {
            $old_backups_sort[$name_file] = $id_file;
        }

        $nb_files = count($clean_list_old_backups);

        // Do we really need to delete old backups
        if ($nb_files < $nb_backup_to_keep) {
            return true;
        }

        // Yes we have to delete old backups
        if ($nb_backup_to_keep > 0) {
            while ($nb_files >= $nb_backup_to_keep) {
                foreach ($clean_list_old_backups[$nb_files]['part'] as $part) {
                    if (!isset($old_backups_sort[$part['name']])) {
                        $this->log($this->l('Error unknown file:', self::PAGE) . ' ' . $part['name']);
                    } else {
                        if (!$sugarsync_lib->deleteFile($old_backups_sort[$part['name']])) {
                            $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);
                        }
                    }
                }
                --$nb_files;
            }
        }

        return true;
    }

    /**
     * Delete old backup files on a AWS account
     *
     * @param object $aws_lib AWS to use
     *
     * @return bool The success or failure of the connection
     */
    public function deleteAwsOldBackup($aws_lib)
    {
        $aws = new AwsNtbr($this->aws_account_id);
        $nb_backup_to_keep = $aws->config_nb_backup;
        $aws_directory_key = $aws->directory_key;
        $aws_directory_path = $aws->directory_path;
        $aws_bucket = $aws->bucket;
        $infos = [];

        // Do we need to delete old backups
        if ($nb_backup_to_keep == 0) {
            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::AWS)
                . ' ' . $this->l('Keep all old backup', self::PAGE)
            );

            return true;
        }

        $this->log(
            sprintf($this->l('Sending backup to %s account:', self::PAGE), self::AWS)
            . ' ' . $this->l('Deleting old backup', self::PAGE)
        );

        if (Tools::substr($aws_directory_key, -1) != '/') {
            $aws_directory_key .= '/';
        }

        if (Tools::substr($aws_directory_path, -1) != '/') {
            $aws_directory_path .= '/';
        }

        if (Tools::substr($aws_bucket, -1) != '/') {
            $aws_bucket .= '/';
        }

        if ($aws_directory_path != $aws_bucket || $aws->type_s3 == NtbrChild::S3_TYPE_AWS) {
            $children = $aws_lib->getListFiles($aws_directory_key);
        } else {
            $children = $aws_lib->getListFiles();
        }

        if ($children === false) {
            return true; // No child to delete
        }

        $old_backups = [];

        foreach ($children as $child) {
            $infos_file = pathinfo($child['name']);

            if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                || strpos($child['name'], '.' . self::EXT_UNCOMPRESS . '.') !== false
                || (
                    $this->config->send_restore
                    && strpos($child['name'], self::NEW_RESTORE_NAME) !== false
                )
            ) {
                if (strpos($child['name'], '.' . $this->config->type_backup) !== false) {
                    $old_backups[$child['name']] = $child['name'];
                    $infos[$child['name']] = $child;
                }
            }
        }

        $clean_list_old_backups = $this->cleanListBackup($old_backups);
        $nb_files = count($clean_list_old_backups);

        // Do we really need to delete old backups
        if ($nb_files < $nb_backup_to_keep) {
            return true;
        }

        // Yes we have to delete old backups
        if ($nb_backup_to_keep > 0) {
            while ($nb_files >= $nb_backup_to_keep) {
                foreach ($clean_list_old_backups[$nb_files]['part'] as $part) {
                    // We can now delete the file
                    if ($aws->type_s3 == NtbrChild::S3_TYPE_BACKBLAZE && isset($infos[$part['name']]['version_id'])) {
                        if ($aws_lib->deleteFile($infos[$part['name']]['id'], $infos[$part['name']]['version_id']) === false) {
                            $this->log(
                                $this->l('Error while deleting the file:', self::PAGE) . ' ' . $aws_directory_key . $part['name']
                            );
                        }
                    } else {
                        if ($aws_lib->deleteFile($infos[$part['name']]['id']) === false) {
                            $this->log(
                                $this->l('Error while deleting the file:', self::PAGE) . ' ' . $aws_directory_key . $part['name']
                            );
                        }
                    }
                }

                --$nb_files;
            }
        }

        return true;
    }

    /**
     * Delete old backup files on a FTP account
     *
     * @param ressource $connection FTP connection
     *
     * @return bool The success or failure of the connection
     */
    protected function deleteFTPOldBackup($connection)
    {
        $ftp = new FtpNtbr($this->ftp_account_id);
        $nb_backup_to_keep = $ftp->config_nb_backup;

        // Do we need to delete old backups
        if ($nb_backup_to_keep == 0) {
            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::FTP)
                . ' ' . $this->l('Keep all old backup', self::PAGE)
            );

            return true;
        }

        $this->log(
            sprintf($this->l('Sending backup to %s account:', self::PAGE), self::FTP)
            . ' ' . $this->l('Deleting old backup', self::PAGE)
        );

        // Find all old backups
        $files = ftp_nlist($connection, '');

        if ($files === false) {
            $files = ftp_rawlist($connection, ''); // get the files of the directory using ftp_rawlist instead

            if (is_array($files)) {
                foreach ($files as &$item) {
                    $item = Tools::substr($item, strrpos($item, ' ') + 1);
                }
            }
        }

        if ($files === false) {
            $this->log(
                sprintf($this->l('Error while listing old %s files', self::PAGE), self::FTP)
            );

            return false;
        }

        $old_backups = [];

        foreach ($files as $file) {
            $infos_file = pathinfo($file);

            if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                || strpos($file, '.' . self::EXT_UNCOMPRESS . '.') !== false
                || (
                    $this->config->send_restore
                    && strpos($file, self::NEW_RESTORE_NAME) !== false
                )
            ) {
                if (strpos($file, '.' . $this->config->type_backup) !== false) {
                    $old_backups[] = $file;
                }
            }
        }

        $clean_list_old_backups = $this->cleanListBackup($old_backups);
        $nb_files = count($clean_list_old_backups);

        // Do we really need to delete old backups
        if ($nb_files < $nb_backup_to_keep) {
            return true;
        }

        // Yes we have to delete old backups
        if ($nb_backup_to_keep > 0) {
            while ($nb_files >= $nb_backup_to_keep) {
                foreach ($clean_list_old_backups[$nb_files]['part'] as $part) {
                    if (!ftp_delete($connection, basename($part['name']))) {
                        $this->log($this->l('Delete old backup file failed:', self::PAGE) . basename($part['name']));

                        return false;
                    }
                }
                --$nb_files;
            }
        }

        return true;
    }

    /**
     * Delete old backup files on a SFTP account
     *
     * @param object $sftp_lib SFTP to use
     * @param string $ftp_dir SFTP directory
     *
     * @return bool The success or failure of the connection
     */
    protected function deleteSFTPOldBackup($sftp_lib, $ftp_dir)
    {
        $ftp = new FtpNtbr($this->ftp_account_id);
        $nb_backup_to_keep = $ftp->config_nb_backup;

        // Do we need to delete old backups
        if ($nb_backup_to_keep == 0) {
            $this->log(
                sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SFTP)
                . ' ' . $this->l('Keep all old backup', self::PAGE)
            );

            return true;
        }

        $this->log(
            sprintf($this->l('Sending backup to %s account:', self::PAGE), self::SFTP)
            . ' ' . $this->l('Deleting old backup', self::PAGE)
        );

        // Find all old backups
        $files = $sftp_lib->nlist($ftp_dir);
        $old_backups = [];

        if (is_array($files)) {
            foreach ($files as $file) {
                $infos_file = pathinfo($file);

                if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                    || strpos($file, '.' . self::EXT_UNCOMPRESS . '.') !== false
                    || (
                        $this->config->send_restore
                        && strpos($file, self::NEW_RESTORE_NAME) !== false
                    )
                ) {
                    if (strpos($file, '.' . $this->config->type_backup) !== false) {
                        $old_backups[] = $file;
                    }
                }
            }
        }

        $clean_list_old_backups = $this->cleanListBackup($old_backups);

        $nb_files = count($clean_list_old_backups);

        // Do we really need to delete old backups
        if ($nb_files < $nb_backup_to_keep) {
            return true;
        }

        // Yes we have to delete old backups
        if ($nb_backup_to_keep > 0) {
            while ($nb_files >= $nb_backup_to_keep) {
                foreach ($clean_list_old_backups[$nb_files]['part'] as $part) {
                    if (!$sftp_lib->delete($ftp_dir . basename($part['name']))) {
                        $this->log($this->l('Delete old backup file failed:', self::PAGE) . basename($part['name']));

                        return false;
                    }
                }
                --$nb_files;
            }
        }

        return true;
    }

    /**
     * Get Dropbox access token
     *
     * @param string $dropbox_code Code that will give the token
     *
     * @return string The token
     */
    public function getDropboxAccessToken($dropbox_code, $business)
    {
        $dropbox_lib = $this->connectToDropbox($business);
        $access_token = $dropbox_lib->getToken($dropbox_code);

        return $access_token;
    }

    /**
     * Get Yandex access token
     *
     * @param string $yandex_code Code that will give the token
     *
     * @return string The token
     */
    public function getYandexAccessToken($yandex_code)
    {
        $yandex_lib = $this->connectToYandex();
        $access_token = $yandex_lib->getToken($yandex_code);

        return $access_token;
    }

    /**
     * Get Google Drive access token
     *
     * @param string $googledrive_code Code that will give the token
     *
     * @return string The token
     */
    public function getGoogledriveAccessToken($googledrive_code)
    {
        $googledrive_lib = $this->connectToGoogledrive();
        $access_token = $googledrive_lib->getToken($googledrive_code);

        if (empty($access_token) || !$access_token) {
            return false;
        }

        return $access_token;
    }

    /**
     * Get Google Cloud access token
     *
     * @param string $googlecloud_code Code that will give the token
     *
     * @return string The token
     */
    public function getGooglecloudAccessToken($googlecloud_code)
    {
        $googlecloud_lib = $this->connectToGooglecloud();
        $access_token = $googlecloud_lib->getToken($googlecloud_code);

        if (empty($access_token) || !$access_token) {
            return false;
        }

        return $access_token;
    }

    /**
     * Get pCloud access token
     *
     * @param int $location_id pCloud location ID
     * @param string $pcloud_code Code that will give the token
     *
     * @return string The token
     */
    public function getPcloudAccessToken($location_id, $pcloud_code)
    {
        $pcloud_lib = $this->connectToPcloud($location_id);
        $token = $pcloud_lib->getToken($pcloud_code);

        if (empty($token) || !$token) {
            return false;
        }

        return $token;
    }

    /**
     * Get Box access token
     *
     * @param string $box_code Code that will give the token
     *
     * @return string The token
     */
    public function getBoxAccessToken($box_code)
    {
        $box_lib = $this->connectToBox();
        $access_token = $box_lib->getToken($box_code);

        if (empty($access_token) || !$access_token) {
            return false;
        }

        return $access_token;
    }

    /**
     * Get SugarSync refresh token
     *
     * @param string $login Login of the user account
     * @param string $password Password of the user account
     *
     * @return string The token
     */
    public function getSugarsyncRefreshToken($login, $password)
    {
        $sugarsync_lib = $this->connectToSugarsync();
        $refresh_token = $sugarsync_lib->getRefreshToken($login, $password);

        if (empty($refresh_token) || !$refresh_token) {
            return false;
        }

        return $refresh_token;
    }

    /**
     * Get Dropbox refresh token
     *
     * @param string $refresh_token Code that will give the refresh token
     *
     * @return string The token
     */
    public function getDropboxRefreshToken($refresh_token, $business)
    {
        $dropbox_lib = $this->connectToDropbox($business);

        return $dropbox_lib->getRefreshToken($refresh_token);
    }

    /**
     * Get Google Drive refresh token
     *
     * @param string $refresh_token Code that will give the refresh token
     *
     * @return string The token
     */
    public function getGoogledriveRefreshToken($refresh_token)
    {
        $googledrive_lib = $this->connectToGoogledrive();

        return $googledrive_lib->getRefreshToken($refresh_token);
    }

    /**
     * Get Google Cloud refresh token
     *
     * @param string $refresh_token Refresh token that will give the new token
     *
     * @return string The token
     */
    public function getGooglecloudRefreshToken($refresh_token)
    {
        $googlecloud_lib = $this->connectToGooglecloud();

        return $googlecloud_lib->getRefreshToken($refresh_token);
    }

    /**
     * Get Box refresh token
     *
     * @param string $refresh_token Code that will give the refresh token
     *
     * @return string The token
     */
    public function getBoxRefreshToken($refresh_token)
    {
        $box_lib = $this->connectToBox();

        return $box_lib->getRefreshToken($refresh_token);
    }

    /**
     * Get OneDrive refresh token
     *
     * @param string $refresh_token Code that will give the refresh token
     * @param int $business If it is a business account
     *
     * @return string The token
     */
    public function getOnedriveRefreshToken($refresh_token, $business)
    {
        $onedrive_lib = $this->connectToOnedrive();

        return $onedrive_lib->getRefreshToken($refresh_token, $business);
    }

    /**
     * Get SugarSync access token
     *
     * @param string $refresh_token Code that will give the refresh token
     *
     * @return string The token
     */
    public function getSugarsyncAccessToken($refresh_token)
    {
        $sugarsync_lib = $this->connectToSugarsync();

        $access_token = $sugarsync_lib->getAccessToken($refresh_token);

        $decode_token = [];
        $decode_token['refresh_token'] = $this->encrypt($refresh_token);
        $decode_token['access_token'] = $this->encrypt($access_token['access_token']);
        $decode_token['expire_in'] = $access_token['expire_in'];
        $decode_token['user'] = $access_token['user'];

        return $decode_token;
    }

    /**
     * Close SFTP
     *
     * @param object $sftp_lib SFTP to close
     *
     * @return bool The success or failure of the operation
     */
    public function closeSFTP($sftp_lib)
    {
        if ($sftp_lib->exec('exit;') === false) {
            return false;
        }

        return true;
    }

    /**
     * Get a list of all backup files of the chosen directory in Dropbox account
     *
     * @param object $dropbox_lib Dropbox to use
     * @param string $dropbox_dir Dropbox directory to search in
     *
     * @return array|string The files of the Dropbox directory or an error message
     */
    public function getDropboxFiles($dropbox_lib, $dropbox_dir)
    {
        // Get informations on the directory and his children
        $folder_children = $dropbox_lib->listFolderChildren($dropbox_dir);
        $children = $folder_children['entries']; // get contents of the directory
        $files_name = [];
        $files = [];
        $infos = [];
        $total_size = 0;

        if (is_array($children)) {
            foreach ($children as $child) {
                $child = (array) $child;
                $infos_file = pathinfo($child['path_lower']);

                if ($child['.tag'] == 'file') {
                    if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                        || strpos($child['path_lower'], '.' . self::EXT_UNCOMPRESS . '.') !== false
                    ) {
                        if (strpos($child['path_lower'], '.' . $this->config->type_backup) !== false) {
                            $files_name[] = $child['name'];
                            $infos[$child['name']] = $child;
                        }
                    }
                }
            }
            $files = $this->cleanListBackup($files_name);

            foreach ($files as &$file) {
                $total_size = 0;

                if (is_array($file['part'])) {
                    foreach ($file['part'] as &$part) {
                        if (isset($infos[$part['name']])) {
                            $total_size += $infos[$part['name']]['size'];
                            $part['size_byte'] = $infos[$part['name']]['size'];
                            $part['size'] = $this->readableSize($infos[$part['name']]['size']);
                            $part['file_id'] = $infos[$part['name']]['id'];
                        }
                    }
                } else {
                    $total_size = $infos[$file['name']]['size'];
                }

                if (isset($infos[$file['name']])) {
                    $file['file_id'] = $infos[$file['name']]['id'];
                }

                $file['size_byte'] = $total_size;
                $file['size'] = $this->readableSize($total_size);
            }
        }

        // d($files);
        return $files;
    }

    /**
     * Get a list of all backup files of the chosen directory in Yandex account
     *
     * @param object $yandex_lib Yandex to use
     * @param string $yandex_dir Yandex directory to search in
     *
     * @return array|string The files of the Yandex directory or an error message
     */
    public function getYandexFiles($yandex_lib, $yandex_dir)
    {
        // Get informations on the directory and his children
        $children = $yandex_lib->listFolderChildren($yandex_dir);

        $files_name = [];
        $files = [];
        $infos = [];
        $total_size = 0;

        if (is_array($children)) {
            foreach ($children as $child) {
                if ($child['type'] == 'file') {
                    $extension = Tools::substr(strrchr($child['name'], '.'), 1);

                    if ((isset($extension) && $extension == self::EXT_UNCOMPRESS)
                        || strpos($child['name'], '.' . self::EXT_UNCOMPRESS . '.') !== false
                    ) {
                        if (strpos($child['name'], '.' . $this->config->type_backup) !== false) {
                            $files_name[] = $child['name'];
                            $infos[$child['name']] = $child;
                        }
                    }
                }
            }

            $files = $this->cleanListBackup($files_name);

            foreach ($files as &$file) {
                $total_size = 0;

                if (is_array($file['part'])) {
                    foreach ($file['part'] as &$part) {
                        if (isset($infos[$part['name']])) {
                            $total_size += $infos[$part['name']]['size'];
                            $part['size_byte'] = $infos[$part['name']]['size'];
                            $part['size'] = $this->readableSize($infos[$part['name']]['size']);
                            $part['file_id'] = str_replace('disk:', '', $infos[$part['name']]['path']);
                        }
                    }
                } else {
                    $total_size = $infos[$file['name']]['size'];
                }

                if (isset($infos[$file['name']])) {
                    $file['file_id'] = str_replace('disk:', '', $infos[$file['name']]['path']);
                }

                $file['size_byte'] = $total_size;
                $file['size'] = $this->readableSize($total_size);
            }
        }

        // d($files);
        return $files;
    }

    /**
     * Get a list of all backup files of the chosen directory in Google Drive account
     *
     * @param object $googledrive_lib Google Drive to use
     * @param string $googledrive_dir Google Drive directory to search in
     *
     * @return array|string The files of the Google Drive directory or an error message
     */
    public function getGoogledriveFiles($googledrive_lib, $googledrive_dir)
    {
        // List the directory children
        $children = $googledrive_lib->getChildrenFiles($googledrive_dir);
        $files_name = [];
        $infos = [];
        $total_size = 0;

        foreach ($children as $child) {
            $infos_file = pathinfo($child['name']);

            if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                || strpos($child['name'], '.' . self::EXT_UNCOMPRESS . '.') !== false
            ) {
                if (strpos($child['name'], '.' . $this->config->type_backup) !== false) {
                    $files_name[$child['name']] = $child['name'];
                    $infos[$child['name']] = $child;
                }
            }
        }

        $files = $this->cleanListBackup($files_name);

        foreach ($files as &$file) {
            $total_size = 0;

            if (is_array($file['part'])) {
                foreach ($file['part'] as &$part) {
                    if (isset($infos[$part['name']])) {
                        $total_size += $infos[$part['name']]['size'];
                        $part['size_byte'] = $infos[$part['name']]['size'];
                        $part['size'] = $this->readableSize($infos[$part['name']]['size']);
                        $part['file_id'] = $infos[$part['name']]['id'];
                    }
                }
            } else {
                $total_size = $infos[$file['name']]['size'];
            }

            if (isset($infos[$file['name']])) {
                $file['file_id'] = $infos[$file['name']]['id'];
            }

            $file['size_byte'] = $total_size;
            $file['size'] = $this->readableSize($total_size);
        }

        // d($files);
        return $files;
    }

    /**
     * Get a list of all backup files of the chosen directory in Google Cloud account
     *
     * @param object $googlecloud_lib Google Cloud to use
     * @param string $googlecloud_dir Google Cloud directory to search in
     *
     * @return array|string The files of the Google Cloud directory or an error message
     */
    public function getGooglecloudFiles($googlecloud_lib, $googlecloud_dir)
    {
        // List the directory children
        $children = $googlecloud_lib->getListFiles($googlecloud_dir);
        $files_name = [];
        $infos = [];
        $total_size = 0;

        if (is_array($children)) {
            foreach ($children as $child) {
                $infos_file = pathinfo($child['name']);

                if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                    || strpos($child['name'], '.' . self::EXT_UNCOMPRESS . '.') !== false
                ) {
                    if (strpos($child['name'], '.' . $this->config->type_backup) !== false) {
                        $files_name[$child['name']] = $child['name'];
                        $infos[$child['name']] = $child;
                    }
                }
            }
        }

        $files = $this->cleanListBackup($files_name);

        foreach ($files as &$file) {
            $total_size = 0;

            if (is_array($file['part'])) {
                foreach ($file['part'] as &$part) {
                    if (isset($infos[$part['name']])) {
                        $total_size += (int) $infos[$part['name']]['size'];

                        $part['size_byte'] = $infos[$part['name']]['size'];
                        $part['size'] = $this->readableSize($infos[$part['name']]['size']);
                        $part['file_id'] = $infos[$part['name']]['id'];
                    }
                }
            } else {
                $total_size = (int) $infos[$file['name']]['size'];
            }

            if (isset($infos[$file['name']])) {
                $file['file_id'] = $infos[$file['name']]['id'];
            }

            $file['size_byte'] = $total_size;
            $file['size'] = $this->readableSize($total_size);
        }

        return $files;
    }

    /**
     * Get a list of all backup files of the chosen directory in pCloud account
     *
     * @param object $pcloud_lib pCloud to use
     * @param int $pcloud_dir_id pCloud directory ID to search in
     *
     * @return array|string The files of the pCloud directory or an error message
     */
    public function getPcloudFiles($pcloud_lib, $pcloud_dir_id)
    {
        // List the directory children
        $children = $pcloud_lib->getListFiles($pcloud_dir_id);
        $files_name = [];
        $infos = [];
        $total_size = 0;

        if (is_array($children)) {
            foreach ($children as $child) {
                $infos_file = pathinfo($child['name']);

                if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                    || strpos($child['name'], '.' . self::EXT_UNCOMPRESS . '.') !== false
                ) {
                    if (strpos($child['name'], '.' . $this->config->type_backup) !== false) {
                        $files_name[$child['name']] = $child['name'];
                        $infos[$child['name']] = $child;
                    }
                }
            }
        }

        $files = $this->cleanListBackup($files_name);

        foreach ($files as &$file) {
            $total_size = 0;

            if (is_array($file['part'])) {
                foreach ($file['part'] as &$part) {
                    if (isset($infos[$part['name']])) {
                        $total_size += (int) $infos[$part['name']]['size'];

                        $part['size_byte'] = $infos[$part['name']]['size'];
                        $part['size'] = $this->readableSize($infos[$part['name']]['size']);
                        $part['file_id'] = $infos[$part['name']]['id'];
                    }
                }
            } else {
                $total_size = (int) $infos[$file['name']]['size'];
            }

            if (isset($infos[$file['name']])) {
                $file['file_id'] = $infos[$file['name']]['id'];
            }

            $file['size_byte'] = $total_size;
            $file['size'] = $this->readableSize($total_size);
        }

        return $files;
    }

    /**
     * Get a list of all backup files of the chosen directory in Box account
     *
     * @param object $box_lib Box to use
     * @param string $box_dir Box directory to search in
     *
     * @return array|string The files of the Box directory or an error message
     */
    public function getBoxFiles($box_lib, $box_dir)
    {
        // List the directory children
        $children = $box_lib->getChildrenFiles($box_dir);
        $files_name = [];
        $infos = [];
        $total_size = 0;

        foreach ($children as $child) {
            $infos_file = pathinfo($child['name']);

            if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                || strpos($child['name'], '.' . self::EXT_UNCOMPRESS . '.') !== false
            ) {
                if (strpos($child['name'], '.' . $this->config->type_backup) !== false) {
                    $files_name[$child['name']] = $child['name'];
                    $infos[$child['name']] = $child;
                }
            }
        }

        $files = $this->cleanListBackup($files_name);

        foreach ($files as &$file) {
            $total_size = 0;

            if (is_array($file['part'])) {
                foreach ($file['part'] as &$part) {
                    if (isset($infos[$part['name']])) {
                        $total_size += $infos[$part['name']]['size'];
                        $part['size_byte'] = $infos[$part['name']]['size'];
                        $part['size'] = $this->readableSize($infos[$part['name']]['size']);
                        $part['file_id'] = $infos[$part['name']]['id'];
                    }
                }
            } else {
                $total_size = $infos[$file['name']]['size'];
            }

            if (isset($infos[$file['name']])) {
                $file['file_id'] = $infos[$file['name']]['id'];
            }

            $file['size_byte'] = $total_size;
            $file['size'] = $this->readableSize($total_size);
        }

        // d($files);
        return $files;
    }

    /**
     * Get a list of all backup files of the chosen directory in Box account
     *
     * @param object $aws_lib AWS to use
     * @param string $aws_directory_key AWS directory to search in
     *
     * @return array|string The files of the AWS directory or an error message
     */
    public function getAwsFiles($aws_lib, $aws_directory_key)
    {
        // List the directory children
        $children = $aws_lib->getListFiles($aws_directory_key);
        $files_name = [];
        $infos = [];
        $total_size = 0;

        if ($children && is_array($children)) {
            foreach ($children as $child) {
                $infos_file = pathinfo($child['name']);

                if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                    || strpos($child['name'], '.' . self::EXT_UNCOMPRESS . '.') !== false
                ) {
                    if (strpos($child['name'], '.' . $this->config->type_backup) !== false) {
                        $files_name[$child['name']] = $child['name'];
                        $infos[$child['name']] = $child;
                    }
                }
            }
        }

        $files = $this->cleanListBackup($files_name);

        foreach ($files as &$file) {
            $total_size = 0;

            if (is_array($file['part'])) {
                foreach ($file['part'] as &$part) {
                    if (isset($infos[$part['name']])) {
                        $total_size += $infos[$part['name']]['size'];
                        $part['size_byte'] = $infos[$part['name']]['size'];
                        $part['size'] = $this->readableSize($infos[$part['name']]['size']);
                        $part['file_id'] = $infos[$part['name']]['id'];
                        $part['version_id'] = $infos[$part['name']]['version_id'];
                    }
                }
            } else {
                $total_size = $infos[$file['name']]['size'];
            }

            if (isset($infos[$file['name']])) {
                $file['file_id'] = $infos[$file['name']]['id'];
                $file['version_id'] = $infos[$file['name']]['version_id'];
            }

            $file['size_byte'] = $total_size;
            $file['size'] = $this->readableSize($total_size);
        }

        // $this->log($files, true);
        return $files;
    }

    /**
     * Get a list of all backup files of the chosen directory in Onedrive account
     *
     * @param object $onedrive_lib Onedrive to use
     * @param string $onedrive_dir Onedrive directory to search in
     *
     * @return array|string The files of the Onedrive directory or an error message
     */
    public function getOnedriveFiles($onedrive_lib, $onedrive_dir)
    {
        // List the directory children
        $children = $onedrive_lib->getListChildren($onedrive_dir);
        $files_name = [];
        $infos = [];
        $total_size = 0;

        foreach ($children as $child) {
            $infos_file = pathinfo($child['name']);

            if (!$child['is_folder']) {
                if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                    || strpos($child['name'], '.' . self::EXT_UNCOMPRESS . '.') !== false
                ) {
                    if (strpos($child['name'], '.' . $this->config->type_backup) !== false) {
                        $files_name[$child['name']] = $child['name'];
                        $infos[$child['name']] = $child;
                    }
                }
            }
        }

        $files = $this->cleanListBackup($files_name);

        foreach ($files as &$file) {
            $total_size = 0;

            if (is_array($file['part'])) {
                foreach ($file['part'] as &$part) {
                    if (isset($infos[$part['name']])) {
                        $total_size += $infos[$part['name']]['size'];
                        $part['size_byte'] = $infos[$part['name']]['size'];
                        $part['size'] = $this->readableSize($infos[$part['name']]['size']);
                        $part['file_id'] = $infos[$part['name']]['id'];
                    }
                }
            } else {
                $total_size = $infos[$file['name']]['size'];
            }

            if (isset($infos[$file['name']])) {
                $file['file_id'] = $infos[$file['name']]['id'];
            }

            $file['size_byte'] = $total_size;
            $file['size'] = $this->readableSize($total_size);
        }
        // d($files);

        return $files;
    }

    /**
     * Get a list of all backup files of the chosen directory in ownCloud/Nextcloud account
     *
     * @param object $owncloud_lib ownCloud/Nextcloud to use
     * @param string $owncloud_dir ownCloud/Nextcloud directory to search in
     *
     * @return array|string The files of the ownCloud/Nextcloud directory or an error message
     */
    public function getOwncloudFiles($owncloud_lib, $owncloud_dir)
    {
        // List the directory children
        $children = $owncloud_lib->getFileChildren($owncloud_dir); // get the files of the directory

        $files_name = [];
        $infos = [];
        $total_size = 0;

        foreach ($children as $child) {
            $infos_file = pathinfo($child['name']);

            if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                || strpos($child['name'], '.' . self::EXT_UNCOMPRESS . '.') !== false
            ) {
                if (strpos($child['name'], '.' . $this->config->type_backup) !== false) {
                    $child['name'] = basename($child['name']);

                    if ($owncloud_dir == '') {
                        $child['path'] = str_replace('/remote.php/webdav/', '', $child['name']);
                    } else {
                        if (Tools::substr($owncloud_dir, -1) != '/') {
                            $owncloud_dir .= '/';
                        }
                        $child['path'] = $owncloud_dir . $child['name'];
                    }

                    $files_name[] = $child['name'];
                    $infos[$child['name']] = $child;
                }
            }
        }

        $files = $this->cleanListBackup($files_name);

        foreach ($files as &$file) {
            $total_size = 0;

            if (is_array($file['part'])) {
                foreach ($file['part'] as &$part) {
                    if (isset($infos[$part['name']])) {
                        $total_size += $infos[$part['name']]['size'];
                        $part['size_byte'] = $infos[$part['name']]['size'];
                        $part['size'] = $this->readableSize($infos[$part['name']]['size']);
                        $part['file_id'] = $infos[$part['name']]['path'];
                    }
                }
            } else {
                $total_size = $infos[$file['name']]['size'];
            }

            if (isset($infos[$file['name']])) {
                $file['file_id'] = $infos[$file['name']]['path'];
            }

            $file['size_byte'] = $total_size;
            $file['size'] = $this->readableSize($total_size);
        }

        // d($files);
        return $files;
    }

    /**
     * Get a list of all backup files of the chosen directory in Shadow Drive account
     *
     * @param object $shadow_drive_lib Shadow Drive to use
     * @param string $shadow_drive_dir Shadow Drive directory to search in
     *
     * @return array|string The files of the Shadow Drive directory or an error message
     */
    public function getShadowDriveFiles($shadow_drive_lib, $shadow_drive_dir)
    {
        // List the directory children
        $children = $shadow_drive_lib->getFileChildren($shadow_drive_dir); // get the files of the directory

        $files_name = [];
        $infos = [];
        $total_size = 0;

        foreach ($children as $child) {
            $infos_file = pathinfo($child['name']);

            if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                || strpos($child['name'], '.' . self::EXT_UNCOMPRESS . '.') !== false
            ) {
                if (strpos($child['name'], '.' . $this->config->type_backup) !== false) {
                    $child['name'] = basename($child['name']);

                    if ($shadow_drive_dir == '') {
                        $child['path'] = str_replace('/remote.php/webdav/', '', $child['name']);
                    } else {
                        if (Tools::substr($shadow_drive_dir, -1) != '/') {
                            $shadow_drive_dir .= '/';
                        }
                        $child['path'] = $shadow_drive_dir . $child['name'];
                    }

                    $files_name[] = $child['name'];
                    $infos[$child['name']] = $child;
                }
            }
        }

        $files = $this->cleanListBackup($files_name);

        foreach ($files as &$file) {
            $total_size = 0;

            if (is_array($file['part'])) {
                foreach ($file['part'] as &$part) {
                    if (isset($infos[$part['name']])) {
                        $total_size += $infos[$part['name']]['size'];
                        $part['size_byte'] = $infos[$part['name']]['size'];
                        $part['size'] = $this->readableSize($infos[$part['name']]['size']);
                        $part['file_id'] = $infos[$part['name']]['path'];
                    }
                }
            } else {
                $total_size = $infos[$file['name']]['size'];
            }

            if (isset($infos[$file['name']])) {
                $file['file_id'] = $infos[$file['name']]['path'];
            }

            $file['size_byte'] = $total_size;
            $file['size'] = $this->readableSize($total_size);
        }

        // d($files);
        return $files;
    }

    /**
     * Get a list of all backup files of the chosen directory in WebDAV account
     *
     * @param object $webdav_lib WebDAV to use
     * @param string $webdav_dir WebDAV directory to search in
     *
     * @return array|string The files of the WebDAV directory or an error message
     */
    public function getWebdavFiles($webdav_lib, $webdav_dir)
    {
        // List the directory children
        $children = $webdav_lib->getFileChildren($webdav_dir); // get the files of the directory

        $files_name = [];
        $infos = [];
        $total_size = 0;

        foreach ($children as $child) {
            $infos_file = pathinfo($child['name']);

            if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                || strpos($child['name'], '.' . self::EXT_UNCOMPRESS . '.') !== false
            ) {
                if (strpos($child['name'], '.' . $this->config->type_backup) !== false) {
                    $child['name'] = basename($child['name']);

                    if ($webdav_dir == '') {
                        $child['path'] = str_replace('/remote.php/webdav/', '', $child['name']);
                    } else {
                        if (Tools::substr($webdav_dir, -1) != '/') {
                            $webdav_dir .= '/';
                        }
                        $child['path'] = $webdav_dir . $child['name'];
                    }
                    $files_name[] = $child['name'];
                    $infos[$child['name']] = $child;
                }
            }
        }

        $files = $this->cleanListBackup($files_name);

        foreach ($files as &$file) {
            $total_size = 0;

            if (is_array($file['part'])) {
                foreach ($file['part'] as &$part) {
                    if (isset($infos[$part['name']])) {
                        $total_size += $infos[$part['name']]['size'];
                        $part['size_byte'] = $infos[$part['name']]['size'];
                        $part['size'] = $this->readableSize($infos[$part['name']]['size']);
                        $part['file_id'] = $infos[$part['name']]['path'];
                    }
                }
            } else {
                $total_size = $infos[$file['name']]['size'];
            }

            if (isset($infos[$file['name']])) {
                $file['file_id'] = $infos[$file['name']]['path'];
            }

            $file['size_byte'] = $total_size;
            $file['size'] = $this->readableSize($total_size);
        }

        // d($files);
        return $files;
    }

    /**
     * Get a list of all backup files of the chosen directory in FTP account
     *
     * @param object $connection FTP to use
     *
     * @return array|string The files of the FTP directory or an error message
     */
    public function getFtpFiles($connection)
    {
        // List the directory children
        $children = ftp_nlist($connection, ''); // get the files of the directory

        if ($children === false) {
            $children = ftp_rawlist($connection, ''); // get the files of the directory using ftp_rawlist instead

            if (is_array($children)) {
                foreach ($children as &$item) {
                    $item = Tools::substr($item, strrpos($item, ' ') + 1);
                }
            }
        }

        $files_name = [];
        $infos = [];
        $total_size = 0;

        if (is_array($children)) {
            foreach ($children as $child) {
                $infos_file = pathinfo($child);

                if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                    || strpos($child, '.' . self::EXT_UNCOMPRESS . '.') !== false
                ) {
                    if (strpos($child, '.' . $this->config->type_backup) !== false) {
                        $files_name[] = $child;
                        $infos[$child] = [
                            'name' => $child,
                            'size' => ftp_size($connection, $child),
                        ];
                    }
                }
            }
        }

        $files = $this->cleanListBackup($files_name);

        foreach ($files as &$file) {
            $total_size = 0;

            if (is_array($file['part'])) {
                foreach ($file['part'] as &$part) {
                    if (isset($infos[$part['name']])) {
                        $total_size += $infos[$part['name']]['size'];
                        $part['size_byte'] = $infos[$part['name']]['size'];
                        $part['size'] = $this->readableSize($infos[$part['name']]['size']);
                        $part['file_id'] = $part['name'];
                    }
                }
            } else {
                $total_size = $infos[$file['name']]['size'];
            }

            if (isset($infos[$file['name']])) {
                $file['file_id'] = $file['name'];
            }

            $file['size_byte'] = $total_size;
            $file['size'] = $this->readableSize($total_size);
        }

        // d($files);
        return $files;
    }

    /**
     * Get a list of all backup files of the chosen directory in SFTP account
     *
     * @param object $sftp_lib SFTP to use
     * @param string $sftp_directory SFTP directory
     *
     * @return array|string The files of the SFTP directory or an error message
     */
    public function getSftpFiles($sftp_lib, $sftp_directory)
    {
        // List the directory children
        $children = $sftp_lib->nlist($sftp_directory); // get the files of the directory

        $files_name = [];
        $infos = [];
        $total_size = 0;

        if (is_array($children)) {
            foreach ($children as $child) {
                $infos_file = pathinfo($child);

                if ((isset($infos_file['extension']) && $infos_file['extension'] == self::EXT_UNCOMPRESS)
                    || strpos($child, '.' . self::EXT_UNCOMPRESS . '.') !== false
                ) {
                    if (strpos($child, '.' . $this->config->type_backup) !== false) {
                        $path = $sftp_directory . $child;

                        $files_name[] = $child;

                        $infos[$child] = [
                            'name' => $child,
                            'size' => $sftp_lib->size($path),
                        ];
                    }
                }
            }
        }

        $files = $this->cleanListBackup($files_name);

        foreach ($files as &$file) {
            $total_size = 0;

            if (is_array($file['part'])) {
                foreach ($file['part'] as &$part) {
                    if (isset($infos[$part['name']])) {
                        $total_size += $infos[$part['name']]['size'];
                        $part['size_byte'] = $infos[$part['name']]['size'];
                        $part['size'] = $this->readableSize($infos[$part['name']]['size']);
                        $part['file_id'] = $part['name'];
                    }
                }
            } else {
                $total_size = $infos[$file['name']]['size'];
            }

            if (isset($infos[$file['name']])) {
                $file['file_id'] = $file['name'];
            }

            $file['size_byte'] = $total_size;
            $file['size'] = $this->readableSize($total_size);
        }

        // d($files);
        return $files;
    }

    /**
     * Get a list of all directories of the Google Drive account
     *
     * @param string $googledrive_dir Google Drive directory
     * @param int $id_ntbr_googledrive Google Drive ID
     *
     * @return string The tree of Google Drive directories
     */
    public function getGoogledriveTree($googledrive_dir, $id_ntbr_googledrive)
    {
        $googledrive = new GoogledriveNtbr($id_ntbr_googledrive);
        $parent_name = $this->l('Home', self::PAGE);

        $this->smarty->assign([
            'config_id' => $googledrive->id_ntbr_config,
            'level' => 1,
            'id_parent' => self::GOOGLEDRIVE_ROOT_ID,
            'path' => $parent_name,
            'parent_name' => $parent_name,
            'googledrive_dir' => $googledrive_dir,
        ]);

        return $this->display(
            _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php',
            $this->template_path . 'googledrive_tree.tpl'
        );
    }

    /**
     * Get a list of all children directories of the parent directory of the Google Drive account
     *
     * @param string $access_token Google Drive token
     * @param string $id_parent Google Drive ID parent directory
     * @param string $googledrive_dir Google Drive ID selected directory
     * @param int $level Google Drive level of the directories
     * @param string $parent_path Google Drive path parent directory
     * @param int $id_ntbr_config Config ID
     *
     * @return string The children directories tree of the given parent directory
     */
    public function getGoogledriveTreeChildren(
        $access_token,
        $id_parent,
        $googledrive_dir,
        $level,
        $parent_path,
        $id_ntbr_config
    ) {
        $googledrive_lib = $this->connectToGoogledrive($access_token);
        ++$level;

        $this->smarty->assign([
            'children' => $googledrive_lib->getChildrenTree($id_parent),
            'level' => $level,
            'config_id' => $id_ntbr_config,
            'googledrive_dir' => $googledrive_dir,
            'parent_path' => $parent_path,
        ]);

        return $this->display(
            _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php',
            $this->template_path . 'googledrive_tree_children.tpl'
        );
    }

    /**
     * Get a list of all directories of the Google Cloud account
     *
     * @param int $id_ntbr_googlecloud Google Cloud ID
     *
     * @return string The tree of Google Drive directories
     */
    public function getGooglecloudTree($id_ntbr_googlecloud)
    {
        $googlecloud = new GooglecloudNtbr($id_ntbr_googlecloud);
        $parent_name = $googlecloud->bucket;

        $this->smarty->assign([
            'config_id' => $googlecloud->id_ntbr_config,
            'level' => 1,
            'parent_path' => $parent_name,
            'googlecloud_dir' => $googlecloud->directory,
        ]);

        return $this->display(
            _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php',
            $this->template_path . 'googlecloud_tree.tpl'
        );
    }

    /**
     * Get a list of all directories of the pCloud account
     *
     * @param int $id_ntbr_pcloud pCloud ID
     *
     * @return string The tree of Google Drive directories
     */
    public function getPcloudTree($id_ntbr_pcloud)
    {
        $pcloud = new PcloudNtbr($id_ntbr_pcloud);
        $parent_name = '/';

        $this->smarty->assign([
            'config_id' => $pcloud->id_ntbr_config,
            'level' => 1,
            'parent_path' => $parent_name,
            'pcloud_dir_path' => $pcloud->directory_path,
            'pcloud_dir_id' => $pcloud->directory_id,
        ]);

        return $this->display(
            _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php',
            $this->template_path . 'pcloud_tree.tpl'
        );
    }

    /**
     * Get a list of all children directories of the parent directory of the Google Cloud account
     *
     * @param string $bucket Google Cloud bucket
     * @param string $access_token Google Cloud token
     * @param string $id_parent Google Cloud ID parent directory
     * @param string $googlecloud_dir Google Cloud ID selected directory
     * @param int $level Google Cloud level of the directories
     * @param string $parent_path Google Cloud path parent directory
     * @param int $id_ntbr_config Config ID
     *
     * @return string The children directories tree of the given parent directory
     */
    public function getGooglecloudTreeChildren(
        $bucket,
        $access_token,
        $googlecloud_dir,
        $level,
        $parent_path,
        $id_ntbr_config
    ) {
        $googlecloud_lib = $this->connectToGooglecloud($bucket, $access_token);
        $children = $googlecloud_lib->getListDirectories($parent_path);

        if (!$children) {
            return '';
        }

        ++$level;

        $this->smarty->assign([
            'children' => $children,
            'level' => $level,
            'config_id' => $id_ntbr_config,
            'googlecloud_dir' => $googlecloud_dir,
            'parent_path' => $parent_path,
        ]);

        return $this->display(
            _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php',
            $this->template_path . 'googlecloud_tree_children.tpl'
        );
    }

    /**
     * Get a list of all children directories of the parent directory of the pCloud account
     *
     * @param int $id_ntbr_pcloud pCloud ntbr ID
     * @param int $pcloud_dir_id pCloud ID selected directory
     * @param int $level pCloud level of the directories
     *
     * @return string The children directories tree of the given parent directory
     */
    public function getPcloudTreeChildren($id_ntbr_pcloud, $pcloud_dir_id, $level)
    {
        $pcloud = new PcloudNtbr($id_ntbr_pcloud);
        $pcloud_lib = $this->connectToPcloud($pcloud->location_id, $pcloud->token);
        $children = $pcloud_lib->getListDirectories($pcloud_dir_id);

        if (!$children) {
            return '';
        }

        ++$level;

        $this->smarty->assign([
            'children' => $children,
            'level' => $level,
            'config_id' => $pcloud->id_ntbr_config,
            'pcloud_dir_id' => $pcloud->directory_id,
            'pcloud_dir_path' => $pcloud->directory_path,
        ]);

        return $this->display(
            _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php',
            $this->template_path . 'pcloud_tree_children.tpl'
        );
    }

    /**
     * Get a list of all directories of the Box account
     *
     * @param string $box_dir Box directory
     * @param int $id_ntbr_box Box ID
     *
     * @return string The tree of Box directories
     */
    public function getBoxTree($box_dir, $id_ntbr_box)
    {
        $box = new BoxNtbr($id_ntbr_box);
        $parent_name = $this->l('Home', self::PAGE);

        $this->smarty->assign([
            'config_id' => $box->id_ntbr_config,
            'level' => 1,
            'id_parent' => self::BOX_ROOT_ID,
            'path' => $parent_name,
            'parent_name' => $parent_name,
            'box_dir' => $box_dir,
        ]);

        return $this->display(
            _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php',
            $this->template_path . 'box_tree.tpl'
        );
    }

    /**
     * Get a list of all children directories of the parent directory of the Box account
     *
     * @param string $access_token Box token
     * @param string $id_parent Box ID parent directory
     * @param string $box_dir Box ID selected directory
     * @param int $level Box level of the directories
     * @param string $parent_path Box path parent directory
     * @param int $id_ntbr_config Config ID
     * @param int $id_ntbr_box Box account ID
     *
     * @return string The children directories tree of the given parent directory
     */
    public function getBoxTreeChildren(
        $access_token,
        $id_parent,
        $box_dir,
        $level,
        $parent_path,
        $id_ntbr_config,
        $id_ntbr_box
    ) {
        $box_lib = $this->connectToBox($access_token, $id_ntbr_box);
        ++$level;

        $this->smarty->assign([
            'children' => $box_lib->getChildrenTree($id_parent),
            'level' => $level,
            'config_id' => $id_ntbr_config,
            'box_dir' => $box_dir,
            'parent_path' => $parent_path,
        ]);

        return $this->display(
            _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php',
            $this->template_path . 'box_tree_children.tpl'
        );
    }

    /**
     * Get a list of all directories of the OneDrive account
     *
     * @param string $access_token OneDrive token
     * @param string $onedrive_dir OneDrive ID selected directory
     * @param int $id_ntbr_onedrive OneDrive account ID
     *
     * @return string The tree of OneDrive directories
     */
    public function getOnedriveTree($access_token, $onedrive_dir, $id_ntbr_onedrive)
    {
        $onedrive_lib = $this->connectToOnedrive($access_token, $id_ntbr_onedrive);
        $onedrive = new OnedriveNtbr($id_ntbr_onedrive);
        $parent_name = $this->l('Home', self::PAGE);

        $this->smarty->assign([
            'level' => 1,
            'config_id' => $onedrive->id_ntbr_config,
            'id_parent' => $onedrive_lib->getRootID(),
            'path' => $parent_name,
            'parent_name' => $parent_name,
            'onedrive_dir' => $onedrive_dir,
        ]);

        return $this->display(
            _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php',
            $this->template_path . 'onedrive_tree.tpl'
        );
    }

    /**
     * Get a list of all children directories of the parent directory of the OneDrive account
     *
     * @param string $access_token OneDrive token
     * @param string $onedrive_dir OneDrive ID selected directory
     * @param string $id_parent OneDrive ID parent directory
     * @param int $level OneDrive level of the directories
     * @param string $parent_path OneDrive path parent directory
     * @param int $id_ntbr_onedrive OneDrive account ID
     *
     * @return string The children directories tree of the given parent directory
     */
    public function getOnedriveTreeChildren(
        $access_token,
        $onedrive_dir,
        $id_parent,
        $level,
        $parent_path,
        $id_ntbr_onedrive
    ) {
        $display_tree = false; // We add the list only if there is at least one folder
        $onedrive_lib = $this->connectToOnedrive($access_token, $id_ntbr_onedrive);
        $onedrive = new OnedriveNtbr($id_ntbr_onedrive);
        $children = $onedrive_lib->getListChildren($id_parent);
        ++$level;

        if ($children === false) {
            return '';
        }

        foreach ($children as $child) {
            if ($child['is_folder']) {
                $display_tree = true;
            }
        }

        if (!$display_tree) {
            return '';
        }

        $this->smarty->assign([
            'children' => $children,
            'level' => $level,
            'config_id' => $onedrive->id_ntbr_config,
            'onedrive_dir' => $onedrive_dir,
            'parent_path' => $parent_path,
        ]);

        return $this->display(
            _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php',
            $this->template_path . 'onedrive_tree_children.tpl'
        );
    }

    /**
     * Get a list of all directories of the SugarSync account
     *
     * @param string $access_token SugarSync token
     * @param string $sugarsync_dir SugarSync ID selected directory
     * @param int $id_ntbr_sugarsync SugarSync account ID
     *
     * @return string The tree of SugarSync directories
     */
    public function getSugarsyncTree($access_token, $sugarsync_dir, $id_ntbr_sugarsync)
    {
        $sugarsync_lib = $this->connectToSugarsync($access_token, $id_ntbr_sugarsync);
        $sugarsync = new SugarsyncNtbr($id_ntbr_sugarsync);
        $root = $sugarsync_lib->getRoot();
        $parent_name = $root['displayName'];

        $this->smarty->assign([
            'level' => 1,
            'config_id' => $sugarsync->id_ntbr_config,
            'id_parent' => basename($root['ref']),
            'path' => $parent_name,
            'parent_name' => $parent_name,
            'sugarsync_dir' => $sugarsync_dir,
        ]);

        return $this->display(
            _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php',
            $this->template_path . 'sugarsync_tree.tpl'
        );
    }

    /**
     * Get a list of all children directories of the parent directory of the SugarSync account
     *
     * @param string $access_token SugarSync token
     * @param string $sugarsync_dir SugarSync ID selected directory
     * @param string $id_parent SugarSync ID parent directory
     * @param int $level SugarSync level of the directories
     * @param string $parent_path SugarSync path parent directory
     * @param int $id_ntbr_sugarsync SugarSync account ID
     *
     * @return string The children directories tree of the given parent directory
     */
    public function getSugarsyncTreeChildren(
        $access_token,
        $sugarsync_dir,
        $id_parent,
        $level,
        $parent_path,
        $id_ntbr_sugarsync
    ) {
        $sugarsync_lib = $this->connectToSugarsync($access_token, $id_ntbr_sugarsync);
        $sugarsync = new SugarsyncNtbr($id_ntbr_sugarsync);
        $children = $sugarsync_lib->getDirectoryChildren($id_parent);
        ++$level;

        foreach ($children as &$child) {
            $child['id'] = basename($child['ref']);
        }

        $this->smarty->assign([
            'children' => $children,
            'level' => $level,
            'config_id' => $sugarsync->id_ntbr_config,
            'parent_path' => $parent_path,
            'sugarsync_dir' => $sugarsync_dir,
        ]);

        return $this->display(
            _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php',
            $this->template_path . 'sugarsync_tree_children.tpl'
        );
    }

    /**
     * Get a list of all directories of the AWS account
     *
     * @param int $id_ntbr_aws AWS account ID
     *
     * @return string The tree of AWS directories
     */
    public function getAwsTree($id_ntbr_aws)
    {
        // Root level (bucket)
        $aws = new AwsNtbr($id_ntbr_aws);

        $this->smarty->assign([
            'level' => 1,
            'config_id' => $aws->id_ntbr_config,
            'aws' => $aws,
        ]);

        return $this->display(
            _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php',
            $this->template_path . 'aws_tree.tpl'
        );
    }

    /**
     * Get a list of all children directories of the parent directory of the AWS account
     *
     * @param string $id_parent AWS name parent directory
     * @param int $level AWS level of the directories
     * @param string $parent_path AWS path parent directory
     * @param int $id_ntbr_aws AWS account ID
     *
     * @return string The children directories tree of the given parent directory
     */
    public function getAwsTreeChildren($id_parent, $level, $parent_path, $id_ntbr_aws)
    {
        $aws = new AwsNtbr($id_ntbr_aws);
        $aws_lib = $this->connectToAws(
            $this->decrypt($aws->access_key_id),
            $this->decrypt($aws->secret_access_key),
            $aws->region,
            $aws->bucket,
            $aws->host,
            $aws->type_s3,
            $aws->accept_unvalid_ssl
        );

        ++$level;

        if ($id_parent == $aws->bucket) {
            // Only directory must be use as parent. No need if it is the bucket
            $children = $aws_lib->getListDirectories();
        } else {
            $children = $aws_lib->getListDirectories($id_parent);
        }

        if ($children === false || !count($children)) {
            return '';
        }

        $this->smarty->assign([
            'children' => $children,
            'level' => $level,
            'config_id' => $aws->id_ntbr_config,
            'parent_path' => $parent_path,
            'aws' => $aws,
        ]);

        return $this->display(
            _PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.php',
            $this->template_path . 'aws_tree_children.tpl'
        );
    }

    /**
     * Send backup away
     */
    protected function sendBackupAway()
    {
        if ($this->config->crypt_backup && !$this->backup_sodium_key) {
            $this->backup_sodium_key = sodium_base642bin($this->decrypt($this->config->sodium_key), SODIUM_BASE64_VARIANT_ORIGINAL);
        }

        if ($this->config->create_on_distant
            && ($this->next_step == self::STEP_GET_FUTUR_TAR_SIZE
            || $this->next_step == self::STEP_GET_FUTUR_TAR_SIZE_CONTINUE)
        ) {
            if (!$this->getFuturTarTotalSize()) {
                $this->log(
                    'WAR'
                    . $this->l('Unable to send backup file to distant account. The tar size cannot be found', self::PAGE)
                );

                $this->next_step = self::STEP_FINISH;
            } else {
                $this->next_step = self::STEP_SEND_AWAY;
            }

            $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
            $this->files_done = 0;
            $this->position_file_list_file = 0;
            $this->part_number = 1;
            $this->old_percent = 0;
            $this->distant_tar_content = '';
            $this->distant_tar_content_size = 0;
        }

        if ($this->next_step == self::STEP_SEND_AWAY) {
            $this->next_step = $this->step_send['ftp'];
        }

        $this->total_nb_part = count($this->part_list);

        // d($this->total_size);

        if ($this->next_step == $this->step_send['ftp'] || $this->next_step == $this->step_send['ftp_resume']) {
            // Get all ftp accounts
            $ftp_accounts = FtpNtbr::getListActiveFtpAccounts($this->config->id);

            if (count($ftp_accounts)) {
                foreach ($ftp_accounts as $ftp_account) {
                    $this->checkStopScript();

                    if (!$this->ftp_account_id) {
                        // If we have no account id save we save the current ftp account id
                        $this->ftp_account_id = $ftp_account['id_ntbr_ftp'];
                    } elseif ($this->ftp_account_id != $ftp_account['id_ntbr_ftp']) {
                        // If we have an id save we need to find the right account to pursue the sending to that account
                        continue;
                    }

                    if ($ftp_account['sftp']) {
                        // Send backup to SFTP account
                        if ($this->next_step == $this->step_send['ftp']
                            && (!isset($this->ftp_nb_part) || $this->ftp_nb_part == 1)
                        ) {
                            $this->log(
                                sprintf($this->l('Sending backup to %s account', self::PAGE), self::SFTP)
                                . ' ' . $ftp_account['name'] . '...'
                            );
                        }

                        // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                        if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                            $sftp_success = $this->createTarOnSFTP();
                        } else {
                            $sftp_success = $this->sendFileToSFTP();
                        }

                        $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                        $this->files_done = 0;
                        $this->position_file_list_file = 0;
                        $this->part_number = 1;
                        $this->old_percent = 0;
                        $this->distant_tar_content = '';
                        $this->distant_tar_content_size = 0;

                        if ($sftp_success) {
                            $this->log(
                                sprintf($this->l('Sending to %s account: Finish', self::PAGE), self::SFTP)
                            );
                            $this->send_away_success = 1;
                        } else {
                            $this->log(
                                'WAR'
                                . sprintf($this->l('Unable to send backup file to %s account', self::PAGE), self::SFTP)
                                . ' ' . $ftp_account['name']
                            );
                        }
                    } else {
                        // Send backup to FTP account
                        if ($this->next_step == $this->step_send['ftp']
                            && (!isset($this->ftp_nb_part) || $this->ftp_nb_part == 1)
                        ) {
                            $this->log(
                                sprintf($this->l('Sending backup to %s account', self::PAGE), self::FTP)
                                . ' ' . $ftp_account['name'] . '...'
                            );
                        }

                        // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                        if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                            $this->log('WAR' . sprintf($this->l('The option to create a backup file directly in distant account is not available with %s account', self::PAGE), self::FTP));

                            $ftp_success = false;
                        } else {
                            $ftp_success = $this->sendFileToFTP();
                        }

                        $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                        $this->files_done = 0;
                        $this->position_file_list_file = 0;
                        $this->part_number = 1;
                        $this->old_percent = 0;
                        $this->distant_tar_content = '';
                        $this->distant_tar_content_size = 0;

                        if ($ftp_success) {
                            $this->log(
                                sprintf($this->l('Sending to %s account: Finish', self::PAGE), self::FTP)
                            );
                            $this->send_away_success = 1;
                        } else {
                            $this->log(
                                'WAR'
                                . sprintf($this->l('Unable to send backup file to %s account', self::PAGE), self::FTP)
                                . ' ' . $ftp_account['name']
                            );
                        }
                    }

                    // We reset the id so that we will save the id in the next iteration of the loop
                    $this->ftp_account_id = 0;
                    $this->ftp_nb_part = 1;
                    // Next step is to send to a new ftp (if there is still one)
                    $this->next_step = $this->step_send['ftp'];
                }

                // If we send to all ftp account, then we can go to the next step
                $this->next_step = $this->step_send['dropbox'];

                // refresh
                $this->refreshBackup();
            } else {
                $this->next_step = $this->step_send['dropbox'];
            }
        }

        if ($this->next_step == $this->step_send['dropbox'] || $this->next_step == $this->step_send['dropbox_resume']) {
            // Get all dropbox accounts
            $dropbox_accounts = DropboxNtbr::getListActiveDropboxAccounts($this->config->id);

            if (count($dropbox_accounts)) {
                foreach ($dropbox_accounts as $dropbox_account) {
                    $this->checkStopScript();

                    if (!$this->dropbox_account_id) {
                        // If we have no account id save we save the current dropbox account id
                        $this->dropbox_account_id = $dropbox_account['id_ntbr_dropbox'];
                    } elseif ($this->dropbox_account_id != $dropbox_account['id_ntbr_dropbox']) {
                        // If we have an id save we need to find the right account to pursue the sending to that account
                        continue;
                    }

                    // Send backup to Dropbox account
                    if ($this->next_step == $this->step_send['dropbox']
                        && (!isset($this->dropbox_nb_part) || $this->dropbox_nb_part == 1)
                    ) {
                        $this->log(
                            sprintf($this->l('Sending backup to %s account', self::PAGE), self::DROPBOX)
                            . ' ' . $dropbox_account['name'] . '...'
                        );
                    }

                    // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                    if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                        $dropbox_success = $this->createTarOnDropbox();
                    } else {
                        $dropbox_success = $this->sendFileToDropbox();
                    }

                    $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                    $this->files_done = 0;
                    $this->position_file_list_file = 0;
                    $this->part_number = 1;
                    $this->old_percent = 0;
                    $this->distant_tar_content = '';
                    $this->distant_tar_content_size = 0;

                    if ($dropbox_success) {
                        $this->log(
                            sprintf($this->l('Sending to %s account: Finish', self::PAGE), self::DROPBOX)
                        );
                        $this->send_away_success = 1;
                    } else {
                        $this->log(
                            'WAR'
                            . sprintf($this->l('Unable to send backup file to %s account', self::PAGE), self::DROPBOX)
                            . ' ' . $dropbox_account['name']
                        );
                    }

                    // We reset the id so that we will save the id in the next iteration of the loop
                    $this->dropbox_account_id = 0;
                    $this->dropbox_nb_part = 1;
                    // Next step is to send to a new dropbox (if there is still one)
                    $this->next_step = $this->step_send['dropbox'];
                }

                // If we send to all dropbox account, then we can go to the next step
                $this->next_step = $this->step_send['owncloud'];
                // refresh
                $this->refreshBackup();
            } else {
                $this->next_step = $this->step_send['owncloud'];
            }
        }

        if ($this->next_step == $this->step_send['owncloud']
            || $this->next_step == $this->step_send['owncloud_resume']
        ) {
            // Get all ownCloud accounts
            $owncloud_accounts = OwncloudNtbr::getListActiveOwncloudAccounts($this->config->id);

            if (count($owncloud_accounts)) {
                foreach ($owncloud_accounts as $owncloud_account) {
                    $this->checkStopScript();

                    if (!$this->owncloud_account_id) {
                        // If we have no account id save we save the current ownCloud account id
                        $this->owncloud_account_id = $owncloud_account['id_ntbr_owncloud'];
                    } elseif ($this->owncloud_account_id != $owncloud_account['id_ntbr_owncloud']) {
                        // If we have an id save we need to find the right account to pursue the sending to that account
                        continue;
                    }

                    // Send backup to ownCloud account
                    if ($this->next_step == $this->step_send['owncloud']
                        && (!isset($this->owncloud_nb_part) || $this->owncloud_nb_part == 1)
                    ) {
                        $this->log(
                            sprintf($this->l('Sending backup to %s account', self::PAGE), self::OWNCLOUD)
                            . ' ' . $owncloud_account['name'] . '...'
                        );
                    }

                    // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                    if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                        $owncloud_success = $this->createTarOnOwncloud();
                    } else {
                        $owncloud_success = $this->sendFileToOwncloud();
                    }

                    $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                    $this->files_done = 0;
                    $this->position_file_list_file = 0;
                    $this->part_number = 1;
                    $this->old_percent = 0;
                    $this->distant_tar_content = '';
                    $this->distant_tar_content_size = 0;

                    if ($owncloud_success) {
                        $this->log(
                            sprintf($this->l('Sending to %s account: Finish', self::PAGE), self::OWNCLOUD)
                        );
                        $this->send_away_success = 1;
                    } else {
                        $this->log(
                            'WAR'
                            . sprintf(
                                $this->l('Unable to send backup file to %s account', self::PAGE),
                                self::OWNCLOUD
                            )
                            . ' ' . $owncloud_account['name']
                        );
                    }

                    // We reset the id so that we will save the id in the next iteration of the loop
                    $this->owncloud_account_id = 0;
                    $this->owncloud_nb_part = 1;
                    // Next step is to send to a new ownCloud (if there is still one)
                    $this->next_step = $this->step_send['owncloud'];
                }

                // If we send to all ownCloud account, then we can go to the next step
                $this->next_step = $this->step_send['webdav'];

                // refresh
                $this->refreshBackup();
            } else {
                $this->next_step = $this->step_send['webdav'];
            }
        }

        if ($this->next_step == $this->step_send['webdav'] || $this->next_step == $this->step_send['webdav_resume']) {
            // Get all WebDAV accounts
            $webdav_accounts = WebdavNtbr::getListActiveWebdavAccounts($this->config->id);

            if (count($webdav_accounts)) {
                foreach ($webdav_accounts as $webdav_account) {
                    $this->checkStopScript();

                    if (!$this->webdav_account_id) {
                        // If we have no account id save we save the current WebDAV account id
                        $this->webdav_account_id = $webdav_account['id_ntbr_webdav'];
                    } elseif ($this->webdav_account_id != $webdav_account['id_ntbr_webdav']) {
                        // If we have an id save we need to find the right account to pursue the sending to that account
                        continue;
                    }

                    // Send backup to WebDAV account
                    if ($this->next_step == $this->step_send['webdav']
                        && (!isset($this->webdav_nb_part) || $this->webdav_nb_part == 1)
                    ) {
                        $this->log(
                            sprintf($this->l('Sending backup to %s account', self::PAGE), self::WEBDAV)
                            . ' ' . $webdav_account['name'] . '...'
                        );
                    }

                    // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                    if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                        $webdav_success = $this->createTarOnWebdav();
                    } else {
                        $webdav_success = $this->sendFileToWebdav();
                    }

                    $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                    $this->files_done = 0;
                    $this->position_file_list_file = 0;
                    $this->part_number = 1;
                    $this->old_percent = 0;
                    $this->distant_tar_content = '';
                    $this->distant_tar_content_size = 0;

                    if ($webdav_success) {
                        $this->log(
                            sprintf($this->l('Sending to %s account: Finish', self::PAGE), self::WEBDAV)
                        );
                        $this->send_away_success = 1;
                    } else {
                        $this->log(
                            'WAR'
                            . sprintf($this->l('Unable to send backup file to %s account', self::PAGE), self::WEBDAV)
                            . ' ' . $webdav_account['name']
                        );
                    }

                    // We reset the id so that we will save the id in the next iteration of the loop
                    $this->webdav_account_id = 0;
                    $this->webdav_nb_part = 1;
                    // Next step is to send to a new WebDAV (if there is still one)
                    $this->next_step = $this->step_send['webdav'];
                }

                // If we send to all WebDAV account, then we can go to the next step
                $this->next_step = $this->step_send['googledrive'];

                // refresh
                $this->refreshBackup();
            } else {
                $this->next_step = $this->step_send['googledrive'];
            }
        }

        if ($this->next_step == $this->step_send['googledrive']
            || $this->next_step == $this->step_send['googledrive_resume']
        ) {
            // Get all Google Drive accounts
            $googledrive_accounts = GoogledriveNtbr::getListActiveGoogledriveAccounts($this->config->id);

            if (count($googledrive_accounts)) {
                foreach ($googledrive_accounts as $googledrive_account) {
                    $this->checkStopScript();

                    if (!$this->googledrive_account_id) {
                        // If we have no account id save we save the current Google Drive account id
                        $this->googledrive_account_id = $googledrive_account['id_ntbr_googledrive'];
                    } elseif ($this->googledrive_account_id != $googledrive_account['id_ntbr_googledrive']) {
                        // If we have an id save we need to find the right account to pursue the sending to that account
                        continue;
                    }

                    // Send backup to Google Drive account
                    if ($this->next_step == $this->step_send['googledrive']
                        && (!isset($this->googledrive_nb_part) || $this->googledrive_nb_part == 1)
                    ) {
                        // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                        if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                            $this->log(
                                sprintf($this->l('Creating backup on %s account', self::PAGE), self::GOOGLEDRIVE)
                                . ' ' . $googledrive_account['name'] . '...'
                            );
                        } else {
                            $this->log(
                                sprintf($this->l('Sending backup to %s account', self::PAGE), self::GOOGLEDRIVE)
                                . ' ' . $googledrive_account['name'] . '...'
                            );
                        }
                    }

                    // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                    if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                        $googledrive_success = $this->createTarOnGoogledrive();
                    } else {
                        $googledrive_success = $this->sendFileToGoogledrive();
                    }

                    $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                    $this->files_done = 0;
                    $this->position_file_list_file = 0;
                    $this->part_number = 1;
                    $this->old_percent = 0;
                    $this->distant_tar_content = '';
                    $this->distant_tar_content_size = 0;

                    if ($googledrive_success) {
                        $this->log(
                            sprintf($this->l('Sending to %s account: Finish', self::PAGE), self::GOOGLEDRIVE)
                        );

                        $this->send_away_success = 1;
                    } else {
                        $this->log(
                            'WAR'
                            . sprintf(
                                $this->l('Unable to send backup file to %s account', self::PAGE),
                                self::GOOGLEDRIVE
                            )
                            . ' ' . $googledrive_account['name']
                        );
                    }

                    // We reset the id so that we will save the id in the next iteration of the loop
                    $this->googledrive_account_id = 0;
                    $this->googledrive_nb_part = 1;
                    // Next step is to send to a new Google Drive (if there is still one)
                    $this->next_step = $this->step_send['googledrive'];
                }

                // If we send to all Google Drive account, then we can go to the next step
                $this->next_step = $this->step_send['googlecloud'];

                // refresh
                $this->refreshBackup();
            } else {
                $this->next_step = $this->step_send['googlecloud'];
            }
        }

        if ($this->next_step == $this->step_send['googlecloud']
            || $this->next_step == $this->step_send['googlecloud_resume']
        ) {
            // Get all Google Cloud accounts
            $googlecloud_accounts = GooglecloudNtbr::getListActiveGooglecloudAccounts($this->config->id);

            if (count($googlecloud_accounts)) {
                foreach ($googlecloud_accounts as $googlecloud_account) {
                    $this->checkStopScript();

                    if (!$this->googlecloud_account_id) {
                        // If we have no account id save we save the current Google Cloud account id
                        $this->googlecloud_account_id = $googlecloud_account['id_ntbr_googlecloud'];
                    } elseif ($this->googlecloud_account_id != $googlecloud_account['id_ntbr_googlecloud']) {
                        // If we have an id save we need to find the right account to pursue the sending to that account
                        continue;
                    }

                    // Send backup to Google Cloud account
                    if ($this->next_step == $this->step_send['googlecloud']
                        && (!isset($this->googlecloud_nb_part) || $this->googlecloud_nb_part == 1)
                    ) {
                        // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                        if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                            $this->log(
                                sprintf($this->l('Creating backup on %s account', self::PAGE), self::GOOGLECLOUD)
                                . ' ' . $googlecloud_account['name'] . '...'
                            );
                        } else {
                            $this->log(
                                sprintf($this->l('Sending backup to %s account', self::PAGE), self::GOOGLECLOUD)
                                . ' ' . $googlecloud_account['name'] . '...'
                            );
                        }
                    }

                    // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                    if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                        $googlecloud_success = $this->createTarOnGooglecloud();
                    } else {
                        $googlecloud_success = $this->sendFileToGooglecloud();
                    }

                    $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                    $this->files_done = 0;
                    $this->position_file_list_file = 0;
                    $this->part_number = 1;
                    $this->old_percent = 0;
                    $this->distant_tar_content = '';
                    $this->distant_tar_content_size = 0;

                    if ($googlecloud_success) {
                        $this->log(
                            sprintf($this->l('Sending to %s account: Finish', self::PAGE), self::GOOGLECLOUD)
                        );

                        $this->send_away_success = 1;
                    } else {
                        $this->log(
                            'WAR'
                            . sprintf(
                                $this->l('Unable to send backup file to %s account', self::PAGE),
                                self::GOOGLECLOUD
                            )
                            . ' ' . $googlecloud_account['name']
                        );
                    }

                    // We reset the id so that we will save the id in the next iteration of the loop
                    $this->googlecloud_account_id = 0;
                    $this->googlecloud_nb_part = 1;
                    // Next step is to send to a new Google Cloud (if there is still one)
                    $this->next_step = $this->step_send['googlecloud'];
                }

                // If we send to all Google Cloud account, then we can go to the next step
                $this->next_step = $this->step_send['pcloud'];

                // refresh
                $this->refreshBackup();
            } else {
                $this->next_step = $this->step_send['pcloud'];
            }
        }

        if ($this->next_step == $this->step_send['pcloud']
            || $this->next_step == $this->step_send['pcloud_resume']
        ) {
            // Get all pCloud accounts
            $pcloud_accounts = PcloudNtbr::getListActivePcloudAccounts($this->config->id);

            if (count($pcloud_accounts)) {
                foreach ($pcloud_accounts as $pcloud_account) {
                    $this->checkStopScript();

                    if (!$this->pcloud_account_id) {
                        // If we have no account id save we save the current pCloud account id
                        $this->pcloud_account_id = $pcloud_account['id_ntbr_pcloud'];
                    } elseif ($this->pcloud_account_id != $pcloud_account['id_ntbr_pcloud']) {
                        // If we have an id save we need to find the right account to pursue the sending to that account
                        continue;
                    }

                    // Send backup to pCloud account
                    if ($this->next_step == $this->step_send['pcloud']
                        && (!isset($this->pcloud_nb_part) || $this->pcloud_nb_part == 1)
                    ) {
                        // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                        if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                            $this->log(
                                sprintf($this->l('Creating backup on %s account', self::PAGE), self::PCLOUD)
                                . ' ' . $pcloud_account['name'] . '...'
                            );
                        } else {
                            $this->log(
                                sprintf($this->l('Sending backup to %s account', self::PAGE), self::PCLOUD)
                                . ' ' . $pcloud_account['name'] . '...'
                            );
                        }
                    }

                    // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                    if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                        $pcloud_success = $this->createTarOnPcloud();
                    } else {
                        $pcloud_success = $this->sendFileToPcloud();
                    }

                    $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                    $this->files_done = 0;
                    $this->position_file_list_file = 0;
                    $this->part_number = 1;
                    $this->old_percent = 0;
                    $this->distant_tar_content = '';
                    $this->distant_tar_content_size = 0;

                    if ($pcloud_success) {
                        $this->log(
                            sprintf($this->l('Sending to %s account: Finish', self::PAGE), self::PCLOUD)
                        );

                        $this->send_away_success = 1;
                    } else {
                        $this->log(
                            'WAR'
                            . sprintf(
                                $this->l('Unable to send backup file to %s account', self::PAGE),
                                self::PCLOUD
                            )
                            . ' ' . $pcloud_account['name']
                        );
                    }

                    // We reset the id so that we will save the id in the next iteration of the loop
                    $this->pcloud_account_id = 0;
                    $this->pcloud_nb_part = 1;
                    // Next step is to send to a new pCloud (if there is still one)
                    $this->next_step = $this->step_send['pcloud'];
                }

                // If we send to all Google Cloud account, then we can go to the next step
                $this->next_step = $this->step_send['onedrive'];

                // refresh
                $this->refreshBackup();
            } else {
                $this->next_step = $this->step_send['onedrive'];
            }
        }

        if ($this->next_step == $this->step_send['onedrive']
            || $this->next_step == $this->step_send['onedrive_resume']
        ) {
            // Get all OneDrive accounts
            $onedrive_accounts = OnedriveNtbr::getListActiveOnedriveAccounts($this->config->id);

            if (count($onedrive_accounts)) {
                foreach ($onedrive_accounts as $onedrive_account) {
                    $this->checkStopScript();

                    if (!$this->onedrive_account_id) {
                        // If we have no account id save we save the current OneDrive account id
                        $this->onedrive_account_id = $onedrive_account['id_ntbr_onedrive'];
                    } elseif ($this->onedrive_account_id != $onedrive_account['id_ntbr_onedrive']) {
                        // If we have an id save we need to find the right account to pursue the sending to that account
                        continue;
                    }

                    // Send backup to OneDrive account
                    if ($this->next_step == $this->step_send['onedrive']
                        && (!isset($this->onedrive_nb_part) || $this->onedrive_nb_part == 1)
                    ) {
                        $this->log(
                            sprintf($this->l('Sending backup to %s account', self::PAGE), self::ONEDRIVE)
                            . ' ' . $onedrive_account['name'] . '...'
                        );
                    }

                    // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                    if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                        $onedrive_success = $this->createTarOnOnedrive();
                    } else {
                        $onedrive_success = $this->sendFileToOnedrive();
                    }

                    $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                    $this->files_done = 0;
                    $this->position_file_list_file = 0;
                    $this->part_number = 1;
                    $this->old_percent = 0;
                    $this->distant_tar_content = '';
                    $this->distant_tar_content_size = 0;

                    if ($onedrive_success) {
                        $this->log(
                            sprintf($this->l('Sending to %s account: Finish', self::PAGE), self::ONEDRIVE)
                        );
                        $this->send_away_success = 1;
                    } else {
                        $this->log(
                            'WAR'
                            . sprintf($this->l('Unable to send backup file to %s account', self::PAGE), self::ONEDRIVE)
                            . ' ' . $onedrive_account['name']
                        );
                    }

                    // We reset the id so that we will save the id in the next iteration of the loop
                    $this->onedrive_account_id = 0;
                    $this->onedrive_nb_part = 1;
                    // Next step is to send to a new OneDrive (if there is still one)
                    $this->next_step = $this->step_send['onedrive'];
                }

                // If we send to all OneDrive account, then we can go to the next step
                $this->next_step = $this->step_send['shadow_drive'];

                // refresh
                $this->refreshBackup();
            } else {
                $this->next_step = $this->step_send['shadow_drive'];
            }
        }

        if ($this->next_step == $this->step_send['shadow_drive'] || $this->next_step == $this->step_send['shadow_drive_resume']) {
            // Get all Shadow Drive accounts
            $shadow_drive_accounts = ShadowDriveNtbr::getListActiveShadowDriveAccounts($this->config->id);

            if (count($shadow_drive_accounts)) {
                foreach ($shadow_drive_accounts as $shadow_drive_account) {
                    $this->checkStopScript();

                    if (!$this->shadow_drive_account_id) {
                        // If we have no account id save we save the current Shadow Drive account id
                        $this->shadow_drive_account_id = $shadow_drive_account['id_ntbr_shadow_drive'];
                    } elseif ($this->shadow_drive_account_id != $shadow_drive_account['id_ntbr_shadow_drive']) {
                        // If we have an id save we need to find the right account to pursue the sending to that account
                        continue;
                    }

                    // Send backup to Shadow Drive account
                    if ($this->next_step == $this->step_send['shadow_drive']
                        && (!isset($this->shadow_drive_nb_part) || $this->shadow_drive_nb_part == 1)
                    ) {
                        $this->log(
                            sprintf($this->l('Sending backup to %s account', self::PAGE), self::SHADOW_DRIVE)
                            . ' ' . $shadow_drive_account['name'] . '...'
                        );
                    }

                    // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                    if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                        $shadow_drive_success = $this->createTarOnShadowDrive();
                    } else {
                        $shadow_drive_success = $this->sendFileToShadowDrive();
                    }

                    $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                    $this->files_done = 0;
                    $this->position_file_list_file = 0;
                    $this->part_number = 1;
                    $this->old_percent = 0;
                    $this->distant_tar_content = '';
                    $this->distant_tar_content_size = 0;

                    if ($shadow_drive_success) {
                        $this->log(
                            sprintf($this->l('Sending to %s account: Finish', self::PAGE), self::SHADOW_DRIVE)
                        );
                        $this->send_away_success = 1;
                    } else {
                        $this->log(
                            'WAR'
                            . sprintf(
                                $this->l('Unable to send backup file to %s account', self::PAGE),
                                self::SHADOW_DRIVE
                            )
                            . ' ' . $shadow_drive_account['name']
                        );
                    }

                    // We reset the id so that we will save the id in the next iteration of the loop
                    $this->shadow_drive_account_id = 0;
                    $this->shadow_drive_nb_part = 1;
                    // Next step is to send to a new shadow_drive (if there is still one)
                    $this->next_step = $this->step_send['shadow_drive'];
                }

                // If we send to all Shadow Drive account, then we can go to the next step
                $this->next_step = $this->step_send['aws'];

                // refresh
                $this->refreshBackup();
            } else {
                $this->next_step = $this->step_send['aws'];
            }
        }

        if ($this->next_step == $this->step_send['aws']
            || $this->next_step == $this->step_send['aws_resume']
        ) {
            // Get all AWS accounts
            $aws_accounts = AwsNtbr::getListActiveAwsAccounts($this->config->id);

            if (count($aws_accounts)) {
                foreach ($aws_accounts as $aws_account) {
                    $this->checkStopScript();

                    if (!$this->aws_account_id) {
                        // If we have no account id save we save the current AWS account id
                        $this->aws_account_id = $aws_account['id_ntbr_aws'];
                    } elseif ($this->aws_account_id != $aws_account['id_ntbr_aws']) {
                        // If we have an id save we need to find the right account to pursue the sending to that account
                        continue;
                    }

                    // Send backup to AWS account
                    if ($this->next_step == $this->step_send['aws']
                        && (!isset($this->aws_nb_part) || $this->aws_nb_part == 1)
                    ) {
                        $this->log(
                            sprintf($this->l('Sending backup to %s account', self::PAGE), self::AWS)
                            . ' ' . $aws_account['name'] . '...'
                        );
                    }

                    // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                    if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                        $aws_success = $this->createTarOnAws();
                    } else {
                        $aws_success = $this->sendFileToAws();
                    }

                    $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                    $this->files_done = 0;
                    $this->position_file_list_file = 0;
                    $this->part_number = 1;
                    $this->old_percent = 0;
                    $this->distant_tar_content = '';
                    $this->distant_tar_content_size = 0;

                    if ($aws_success) {
                        $this->log(
                            sprintf($this->l('Sending to %s account: Finish', self::PAGE), self::AWS)
                        );

                        $this->send_away_success = 1;
                    } else {
                        $this->log(
                            'WAR'
                            . sprintf($this->l('Unable to send backup file to %s account', self::PAGE), self::AWS)
                            . ' ' . $aws_account['name']
                        );
                    }

                    // We reset the id so that we will save the id in the next iteration of the loop
                    $this->aws_account_id = 0;
                    $this->aws_nb_part = 1;
                    // Next step is to send to a new AWS (if there is still one)
                    $this->next_step = $this->step_send['aws'];
                }

                // If we send to all AWS account, then we can go to the next step
                $this->next_step = $this->step_send['yandex'];

                // refresh
                $this->refreshBackup();
            } else {
                $this->next_step = $this->step_send['yandex'];
            }
        }

        if ($this->next_step == $this->step_send['yandex'] || $this->next_step == $this->step_send['yandex_resume']) {
            // Get all yandex accounts
            $yandex_accounts = YandexNtbr::getListActiveYandexAccounts($this->config->id);

            if (count($yandex_accounts)) {
                foreach ($yandex_accounts as $yandex_account) {
                    $this->checkStopScript();

                    if (!$this->yandex_account_id) {
                        // If we have no account id save we save the current yandex account id
                        $this->yandex_account_id = $yandex_account['id_ntbr_yandex'];
                    } elseif ($this->yandex_account_id != $yandex_account['id_ntbr_yandex']) {
                        // If we have an id save we need to find the right account to pursue the sending to that account
                        continue;
                    }

                    // Send backup to Yandex account
                    if ($this->next_step == $this->step_send['yandex']
                        && (!isset($this->yandex_nb_part) || $this->yandex_nb_part == 1)
                    ) {
                        $this->log(
                            sprintf($this->l('Sending backup to %s account', self::PAGE), self::YANDEX)
                            . ' ' . $yandex_account['name'] . '...'
                        );
                    }

                    // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                    if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                        $yandex_success = $this->createTarOnYandex();
                    } else {
                        $yandex_success = $this->sendFileToYandex();
                    }

                    $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                    $this->files_done = 0;
                    $this->position_file_list_file = 0;
                    $this->part_number = 1;
                    $this->old_percent = 0;
                    $this->distant_tar_content = '';
                    $this->distant_tar_content_size = 0;

                    if ($yandex_success) {
                        $this->log(
                            sprintf($this->l('Sending to %s account: Finish', self::PAGE), self::YANDEX)
                        );
                        $this->send_away_success = 1;
                    } else {
                        $this->log(
                            'WAR'
                            . sprintf($this->l('Unable to send backup file to %s account', self::PAGE), self::YANDEX)
                            . ' ' . $yandex_account['name']
                        );
                    }

                    // We reset the id so that we will save the id in the next iteration of the loop
                    $this->yandex_account_id = 0;
                    $this->yandex_nb_part = 1;
                    // Next step is to send to a new yandex (if there is still one)
                    $this->next_step = $this->step_send['yandex'];
                }

                // If we send to all yandex account, then we can go to the next step
                $this->next_step = $this->step_send['box'];
                // refresh
                $this->refreshBackup();
            } else {
                $this->next_step = $this->step_send['box'];
            }
        }

        if ($this->next_step == $this->step_send['box'] || $this->next_step == $this->step_send['box_resume']) {
            // Get all box accounts
            $box_accounts = BoxNtbr::getListActiveBoxAccounts($this->config->id);

            if (count($box_accounts)) {
                foreach ($box_accounts as $box_account) {
                    $this->checkStopScript();

                    if (!$this->box_account_id) {
                        // If we have no account id save we save the current box account id
                        $this->box_account_id = $box_account['id_ntbr_box'];
                    } elseif ($this->box_account_id != $box_account['id_ntbr_box']) {
                        // If we have an id save we need to find the right account to pursue the sending to that account
                        continue;
                    }

                    // Send backup to Box account
                    if ($this->next_step == $this->step_send['box']
                        && (!isset($this->box_nb_part) || $this->box_nb_part == 1)
                    ) {
                        $this->log(
                            sprintf($this->l('Sending backup to %s account', self::PAGE), self::BOX)
                            . ' ' . $box_account['name'] . '...'
                        );
                    }

                    // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                    if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                        $this->log('WAR' . sprintf($this->l('The option to create a backup file directly in distant account is not available with %s account', self::PAGE), self::BOX));

                        $box_success = false;
                    } else {
                        $box_success = $this->sendFileToBox();
                    }

                    $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                    $this->files_done = 0;
                    $this->position_file_list_file = 0;
                    $this->part_number = 1;
                    $this->old_percent = 0;
                    $this->distant_tar_content = '';
                    $this->distant_tar_content_size = 0;

                    if ($box_success) {
                        $this->log(
                            sprintf($this->l('Sending to %s account: Finish', self::PAGE), self::BOX)
                        );
                        $this->send_away_success = 1;
                    } else {
                        $this->log(
                            'WAR'
                            . sprintf($this->l('Unable to send backup file to %s account', self::PAGE), self::BOX)
                            . ' ' . $box_account['name']
                        );
                    }

                    // We reset the id so that we will save the id in the next iteration of the loop
                    $this->box_account_id = 0;
                    $this->box_nb_part = 1;
                    // Next step is to send to a new box (if there is still one)
                    $this->next_step = $this->step_send['box'];
                }

                // If we send to all box account, then we can go to the next step
                $this->next_step = $this->step_send['sugarsync'];
                // refresh
                $this->refreshBackup();
            } else {
                $this->next_step = $this->step_send['sugarsync'];
            }
        }

        if ($this->next_step == $this->step_send['sugarsync']
            || $this->next_step == $this->step_send['sugarsync_resume']
        ) {
            // Get all SugarSync accounts
            $sugarsync_accounts = SugarsyncNtbr::getListActiveSugarsyncAccounts($this->config->id);

            if (count($sugarsync_accounts)) {
                foreach ($sugarsync_accounts as $sugarsync_account) {
                    $this->checkStopScript();

                    if (!$this->sugarsync_account_id) {
                        // If we have no account id save we save the current SugarSync account id
                        $this->sugarsync_account_id = $sugarsync_account['id_ntbr_sugarsync'];
                    } elseif ($this->sugarsync_account_id != $sugarsync_account['id_ntbr_sugarsync']) {
                        // If we have an id save we need to find the right account to pursue the sending to that account
                        continue;
                    }

                    // Send backup to SugarSync account
                    if ($this->next_step == $this->step_send['sugarsync']
                        && (!isset($this->sugarsync_nb_part) || $this->sugarsync_nb_part == 1)
                    ) {
                        $this->log(
                            sprintf($this->l('Sending backup to %s account', self::PAGE), self::SUGARSYNC)
                            . ' ' . $sugarsync_account['name'] . '...'
                        );
                    }

                    // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
                    if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
                        $sugarsync_success = $this->createTarOnSugarsync();
                    } else {
                        $sugarsync_success = $this->sendFileToSugarsync();
                    }

                    $this->secondary_next_step = self::SECONDARY_STEP_TAR_FILE;
                    $this->files_done = 0;
                    $this->position_file_list_file = 0;
                    $this->part_number = 1;
                    $this->old_percent = 0;
                    $this->distant_tar_content = '';
                    $this->distant_tar_content_size = 0;

                    if ($sugarsync_success) {
                        $this->log(
                            sprintf($this->l('Sending to %s account: Finish', self::PAGE), self::SUGARSYNC)
                        );
                        $this->send_away_success = 1;
                    } else {
                        $this->log(
                            'WAR'
                            . sprintf($this->l('Unable to send backup file to %s account', self::PAGE), self::SUGARSYNC)
                            . ' ' . $sugarsync_account['name']
                        );
                    }

                    // We reset the id so that we will save the id in the next iteration of the loop
                    $this->sugarsync_account_id = 0;
                    $this->sugarsync_nb_part = 1;
                    // Next step is to send to a new SugarSync (if there is still one)
                    $this->next_step = $this->step_send['sugarsync'];
                }

                // If we send to all SugarSync account, then we can go to the next step
                if ($this->config->create_on_distant) {
                    $this->next_step = self::STEP_CLEAN_FILES;
                } else {
                    $this->next_step = self::STEP_FINISH;
                }

                // refresh
                $this->refreshBackup();
            } else {
                if ($this->config->create_on_distant) {
                    $this->next_step = self::STEP_CLEAN_FILES;
                } else {
                    $this->next_step = self::STEP_FINISH;
                }
            }
        }

        // If create on distant, we need to remove temp files ntbr.tmp after all the distant creating are done
        if ($this->next_step == self::STEP_CLEAN_FILES
            || $this->next_step == self::STEP_CLEAN_FILES_CONTINUE
        ) {
            $this->cleanAfterCreateOnDistantAccount();

            $this->next_step = self::STEP_FINISH;
        }

        // Create on tar if the option is enabled and we are not trying to send a local backup (using the option in backups list)
        if ($this->config->create_on_distant && is_array($this->tar_files_size)) {
            if (is_resource($this->handle_list_dir_file)) {
                fclose($this->handle_list_dir_file);
            }
            if (is_resource($this->handle_file_list_file)) {
                fclose($this->handle_file_list_file);
            }

            foreach ($this->getOldDumpFiles() as $dump_file) {
                $this->fileDelete($dump_file);
            }

            $this->fileDelete($this->list_dir_file);
            $this->fileDelete($this->file_list_file);
        }
    }

    public function saveConfigProfile($is_default, $name, $type)
    {
        $config = new ConfigNtbr();
        $config->is_default = $is_default;
        $config->name = $name;
        $config->type_backup = $type;

        $result = [
            'id_profile' => 0,
            'success' => 1,
            'errors' => [],
        ];

        if (!$config->name || $config->name == '') {
            $result['errors'][] = $this->l('The name of the configuration is required.', self::PAGE);
        }

        if (!$config->type_backup || !in_array($config->type_backup, [
            $this->type_backup_complete,
            $this->type_backup_file,
            $this->type_backup_base,
        ])) {
            $result['errors'][] = $this->l('The type of the configuration is no valid', self::PAGE);
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        $config->add();

        $result['id_profile'] = $config->id;

        return $result;
    }

    public function deleteConfig($id_ntbr_config)
    {
        $config = new ConfigNtbr($id_ntbr_config);

        return (int) $config->delete();
    }

    public function saveConfig(
        $config,
        $send_restore,
        $activate_xsendfile,
        $ignore_product_image,
        $only_origin_img,
        $delete_local_backup,
        $create_on_distant,
        $backup_dir,
        $ignore_directories,
        $ignore_file_types,
        $ignore_tables,
        $not_recreate_tables,
        $crypt_backup,
        $multi_config
    ) {
        $config->send_restore = $send_restore;
        $config->activate_xsendfile = $activate_xsendfile;
        $config->ignore_product_image = $ignore_product_image;
        $config->only_origin_img = $only_origin_img;
        $config->delete_local_backup = $delete_local_backup;
        $config->create_on_distant = $create_on_distant;
        $config->crypt_backup = $crypt_backup;
        $config->backup_dir = $backup_dir;
        $config->ignore_directories = $ignore_directories;
        $config->ignore_file_types = $ignore_file_types;
        $config->ignore_tables = $ignore_tables;

        // Make sure table to not recreate are also ignored
        $tables_to_ignore = explode(',', str_replace(' ', '', $ignore_tables));
        $tables_to_not_recreate = explode(',', str_replace(' ', '', $not_recreate_tables));
        $tables_in_both = [];

        foreach ($tables_to_not_recreate as $table) {
            if (!in_array($table, $tables_to_ignore)) {
                continue;
            }

            $tables_in_both[] = $table;
        }

        $config->not_recreate_tables = implode(',', $tables_in_both);

        GlobConfNtbr::set('NTBR_MULTI_CONFIG', $multi_config);

        if (!GlobConfNtbr::get('NTBR_MULTI_CONFIG') == $multi_config) {
            return false;
        }

        // If crypt backup enable and sodium key not created, create it
        if ($config->crypt_backup && !$config->sodium_key) {
            $config->sodium_key = $this->encrypt($this->createBackupSodiumKey());
        }

        return $config;
    }

    public function displayFtpAccount($id_ntbr_ftp)
    {
        return FtpNtbr::getFtpAccountById($id_ntbr_ftp);
    }

    public function displayDropboxAccount($id_ntbr_dropbox)
    {
        return DropboxNtbr::getDropboxAccountById($id_ntbr_dropbox);
    }

    public function displayYandexAccount($id_ntbr_yandex)
    {
        return YandexNtbr::getYandexAccountById($id_ntbr_yandex);
    }

    public function displayOwncloudAccount($id_ntbr_owncloud)
    {
        return OwncloudNtbr::getOwncloudAccountById($id_ntbr_owncloud);
    }

    public function displayShadowDriveAccount($id_ntbr_shadow_drive)
    {
        return ShadowDriveNtbr::getShadowDriveAccountById($id_ntbr_shadow_drive);
    }

    public function displayWebdavAccount($id_ntbr_webdav)
    {
        return WebdavNtbr::getWebdavAccountById($id_ntbr_webdav);
    }

    public function displayGoogledriveAccount($id_ntbr_googledrive)
    {
        return GoogledriveNtbr::getGoogledriveAccountById($id_ntbr_googledrive);
    }

    public function displayGooglecloudAccount($id_ntbr_googlecloud)
    {
        return GooglecloudNtbr::getGooglecloudAccountById($id_ntbr_googlecloud);
    }

    public function displayPcloudAccount($id_ntbr_pcloud)
    {
        return PcloudNtbr::getPcloudAccountById($id_ntbr_pcloud);
    }

    public function displayBoxAccount($id_ntbr_box)
    {
        return BoxNtbr::getBoxAccountById($id_ntbr_box);
    }

    public function displayOnedriveAccount($id_ntbr_onedrive)
    {
        return OnedriveNtbr::getOnedriveAccountById($id_ntbr_onedrive);
    }

    public function displaySugarsyncAccount($id_ntbr_sugarsync)
    {
        return SugarsyncNtbr::getSugarsyncAccountById($id_ntbr_sugarsync);
    }

    public function displayAwsAccount($id_ntbr_aws)
    {
        return AwsNtbr::getAwsAccountById($id_ntbr_aws);
    }

    public function saveFtp(
        $id_ntbr_config,
        $id_ntbr_ftp,
        $name,
        $active,
        $sftp,
        $ssl,
        $passive_mode,
        $config_nb_backup,
        $server,
        $login,
        $password,
        $port,
        $directory
    ) {
        $result = [
            'success' => 1,
            'errors' => [],
            'id_ntbr_ftp' => 0,
        ];

        // Required data
        if (!$name || trim($name) == '') {
            $result['errors'][] = $this->l('The name is required.', self::PAGE);
        }

        if (!$server || trim($server) == '') {
            $result['errors'][] = $this->l('The server is required.', self::PAGE);
        }

        if (!$login || trim($login) == '') {
            $result['errors'][] = $this->l('The login is required.', self::PAGE);
        }

        if ($id_ntbr_ftp) {
            $ftp = new FtpNtbr($id_ntbr_ftp);

            if (!$password || trim($password) == '' || $password == self::FAKE_MDP) {
                $password = $this->decrypt($ftp->password);
            }
        } else {
            $ftp = new FtpNtbr();
        }

        if (!$password || trim($password) == '') {
            $result['errors'][] = $this->l('The password is required.', self::PAGE);
        }

        // Data validity
        if (!Validate::isGenericName($name)) {
            $result['errors'][] = $this->l('The account name is not valid. Please do not use those characters', self::PAGE) . ' "<>={}"';
        } else {
            $name_exists_id = FtpNtbr::getIdByName($id_ntbr_config, $name);

            if ($name_exists_id && $id_ntbr_ftp != $name_exists_id) {
                $result['errors'][] = $this->l('The account name is already used', self::PAGE);
            }
        }

        if ($sftp && $ssl) {
            $result['errors'][] = sprintf($this->l('%s cannot use SSL', self::PAGE), self::SFTP);
        }

        if ($sftp && $passive_mode) {
            $result['errors'][] = sprintf($this->l('%s cannot use passive mode', self::PAGE), self::SFTP);
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        // Check connection
        if ($sftp) {
            // Connect with SFTP
            if ($active) {
                if (!$this->testSFTP($server, $login, $password, $port)) {
                    $result['errors'][] = sprintf(
                        $this->l('Unable to connect to your %s account', self::PAGE),
                        self::SFTP
                    );
                }
            }
        } else {
            // Connect with FTP
            if ($active) {
                if (!$this->testFTP($server, $login, $password, $port, $ssl, $passive_mode)) {
                    $result['errors'][] = sprintf(
                        $this->l('Unable to connect to your %s account', self::PAGE),
                        self::FTP
                    );
                }
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        // Save data
        $ftp->id_ntbr_config = $id_ntbr_config;
        $ftp->name = $name;
        $ftp->active = $active;
        $ftp->sftp = $sftp;
        $ftp->ssl = $ssl;
        $ftp->passive_mode = $passive_mode;
        $ftp->config_nb_backup = $config_nb_backup;
        $ftp->server = $server;
        $ftp->login = $login;
        $ftp->password = $this->encrypt($password);
        $ftp->port = $port;
        $ftp->directory = $directory;

        if (!$ftp->save()) {
            $result['success'] = 0;
        }

        $result['id_ntbr_ftp'] = $ftp->id;

        return $result;
    }

    public function saveDropbox(
        $id_ntbr_config,
        $id_ntbr_dropbox,
        $name,
        $active,
        $business,
        $config_nb_backup,
        $code,
        $directory
    ) {
        $token = '';

        $result = [
            'success' => 1,
            'errors' => [],
            'id_ntbr_dropbox' => 0,
        ];

        // Required data
        if (!$name || trim($name) == '') {
            $result['errors'][] = $this->l('The name is required.', self::PAGE);
        }

        // Data validity
        if (!Validate::isGenericName($name) || trim($name) == '') {
            $result['errors'][] = $this->l('The account name is not valid. Please do not use those characters', self::PAGE) . ' "<>={}"';
        } else {
            $name_exists_id = DropboxNtbr::getIdByName($id_ntbr_config, $name);

            if ($name_exists_id && $id_ntbr_dropbox != $name_exists_id) {
                $result['errors'][] = $this->l('The account name is already used', self::PAGE);
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        if ($id_ntbr_dropbox) {
            $dropbox = new DropboxNtbr($id_ntbr_dropbox);
        } else {
            $dropbox = new DropboxNtbr();
        }

        // Check connection
        $connection = true;

        if ($code != '' && $code != self::FAKE_MDP) {
            // Get new token
            $decode_token = $this->getDropboxAccessToken($code, $business);

            $decode_token['access_token'] = $this->encrypt($decode_token['access_token']);
            $decode_token['refresh_token'] = $this->encrypt($decode_token['refresh_token']);

            $token = json_encode($decode_token);
        } else {
            // Get current token
            $token = DropboxNtbr::getDropboxTokenById($id_ntbr_dropbox);

            if ($token && $token != '' && $active) {
                $connection = $this->testDropboxConnection($token, $business);
            }
        }

        if (!$token || $token == '') {
            $connection = false;
        }

        if (!$connection && $active) {
            $result['errors'][] = sprintf($this->l('Unable to connect to your %s account', self::PAGE), self::DROPBOX);
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        $dropbox->id_ntbr_config = $id_ntbr_config;
        $dropbox->name = $name;
        $dropbox->active = $active;
        $dropbox->business = $business;
        $dropbox->config_nb_backup = $config_nb_backup;
        $dropbox->directory = $directory;
        $dropbox->token = $token;

        if (!$dropbox->save()) {
            $result['success'] = 0;
        }

        $result['id_ntbr_dropbox'] = $dropbox->id;

        return $result;
    }

    public function saveYandex(
        $id_ntbr_config,
        $id_ntbr_yandex,
        $name,
        $active,
        $config_nb_backup,
        $code,
        $directory
    ) {
        $token = '';

        $result = [
            'success' => 1,
            'errors' => [],
            'id_ntbr_yandex' => 0,
        ];

        // Required data
        if (!$name || trim($name) == '') {
            $result['errors'][] = $this->l('The name is required.', self::PAGE);
        }

        // Data validity
        if (!Validate::isGenericName($name) || trim($name) == '') {
            $result['errors'][] = $this->l('The account name is not valid. Please do not use those characters', self::PAGE) . ' "<>={}"';
        } else {
            $name_exists_id = YandexNtbr::getIdByName($id_ntbr_config, $name);

            if ($name_exists_id && $id_ntbr_yandex != $name_exists_id) {
                $result['errors'][] = $this->l('The account name is already used', self::PAGE);
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        if ($id_ntbr_yandex) {
            $yandex = new YandexNtbr($id_ntbr_yandex);
        } else {
            $yandex = new YandexNtbr();
        }

        // Check connection
        $connection = true;

        if ($code != '' && $code != self::FAKE_MDP) {
            // Get new token
            $token = $this->getYandexAccessToken($code);
        } else {
            // Get current token
            $token = $this->decrypt(YandexNtbr::getYandexTokenById($id_ntbr_yandex));
            if ($token && $token != '' && $active) {
                $connection = $this->testYandexConnection($token);
            }
        }

        if (!$token || $token == '') {
            $connection = false;
        }

        if (!$connection && $active) {
            $result['errors'][] = sprintf($this->l('Unable to connect to your %s account', self::PAGE), self::YANDEX);
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        if ($directory == '') {
            $directory = '/';
        }

        $yandex->id_ntbr_config = $id_ntbr_config;
        $yandex->name = $name;
        $yandex->active = $active;
        $yandex->config_nb_backup = $config_nb_backup;
        $yandex->directory = $directory;
        $yandex->token = $this->encrypt($token);

        if (!$yandex->save()) {
            $result['success'] = 0;
        }

        $result['id_ntbr_yandex'] = $yandex->id;

        return $result;
    }

    public function saveOwncloud(
        $id_ntbr_config,
        $id_ntbr_owncloud,
        $name,
        $active,
        $config_nb_backup,
        $login,
        $password,
        $server,
        $directory
    ) {
        $result = [
            'success' => 1,
            'errors' => [],
            'id_ntbr_owncloud' => 0,
        ];

        // Required data
        if (!$name || trim($name) == '') {
            $result['errors'][] = $this->l('The name is required.', self::PAGE);
        }

        if (!$server || trim($server) == '') {
            $result['errors'][] = $this->l('The server is required.', self::PAGE);
        }

        if (!$login || trim($login) == '') {
            $result['errors'][] = $this->l('The login is required.', self::PAGE);
        }

        if ($id_ntbr_owncloud) {
            $owncloud = new OwncloudNtbr($id_ntbr_owncloud);

            if (!$password || trim($password) == '' || $password == self::FAKE_MDP) {
                $password = $this->decrypt($owncloud->password);
            }
        } else {
            $owncloud = new OwncloudNtbr();
        }

        if (!$password || trim($password) == '') {
            $result['errors'][] = $this->l('The password is required.', self::PAGE);
        }

        // Data validity
        if (!Validate::isGenericName($name) || trim($name) == '') {
            $result['errors'][] = $this->l('The account name is not valid. Please do not use those characters', self::PAGE) . ' "<>={}"';
        } else {
            $name_exists_id = OwncloudNtbr::getIdByName($id_ntbr_config, $name);

            if ($name_exists_id && $id_ntbr_owncloud != $name_exists_id) {
                $result['errors'][] = $this->l('The account name is already used', self::PAGE);
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        // Check connection (if active)
        if ($active) {
            $connection = $this->testOwncloudConnection($server, $login, $password);

            if (!$connection) {
                $result['errors'][] = sprintf(
                    $this->l('Unable to connect to your %s account', self::PAGE),
                    self::OWNCLOUD
                );
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        // Save data
        $owncloud->id_ntbr_config = $id_ntbr_config;
        $owncloud->name = $name;
        $owncloud->active = $active;
        $owncloud->config_nb_backup = $config_nb_backup;
        $owncloud->login = $login;
        $owncloud->password = $this->encrypt($password);
        $owncloud->server = $server;
        $owncloud->directory = $directory;

        if (!$owncloud->save()) {
            $result['success'] = 0;
        }

        $result['id_ntbr_owncloud'] = $owncloud->id;

        return $result;
    }

    public function saveShadowDrive(
        $id_ntbr_config,
        $id_ntbr_shadow_drive,
        $name,
        $active,
        $config_nb_backup,
        $login,
        $password,
        $server,
        $directory
    ) {
        $result = [
            'success' => 1,
            'errors' => [],
            'id_ntbr_shadow_drive' => 0,
        ];

        // Required data
        if (!$name || trim($name) == '') {
            $result['errors'][] = $this->l('The name is required.', self::PAGE);
        }

        if (!$server || trim($server) == '') {
            $result['errors'][] = $this->l('The server is required.', self::PAGE);
        }

        if (!$login || trim($login) == '') {
            $result['errors'][] = $this->l('The login is required.', self::PAGE);
        }

        if ($id_ntbr_shadow_drive) {
            $shadow_drive = new ShadowDriveNtbr($id_ntbr_shadow_drive);

            if (!$password || trim($password) == '' || $password == self::FAKE_MDP) {
                $password = $this->decrypt($shadow_drive->password);
            }
        } else {
            $shadow_drive = new ShadowDriveNtbr();
        }

        if (!$password || trim($password) == '') {
            $result['errors'][] = $this->l('The password is required.', self::PAGE);
        }

        // Data validity
        if (!Validate::isGenericName($name) || trim($name) == '') {
            $result['errors'][] = $this->l('The account name is not valid. Please do not use those characters', self::PAGE) . ' "<>={}"';
        } else {
            $name_exists_id = ShadowDriveNtbr::getIdByName($id_ntbr_config, $name);

            if ($name_exists_id && $id_ntbr_shadow_drive != $name_exists_id) {
                $result['errors'][] = $this->l('The account name is already used', self::PAGE);
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        // Check connection (if active)
        if ($active) {
            $connection = $this->testShadowDriveConnection($server, $login, $password);

            if (!$connection) {
                $result['errors'][] = sprintf(
                    $this->l('Unable to connect to your %s account', self::PAGE),
                    self::SHADOW_DRIVE
                );
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        // Save data
        $shadow_drive->id_ntbr_config = $id_ntbr_config;
        $shadow_drive->name = $name;
        $shadow_drive->active = $active;
        $shadow_drive->config_nb_backup = $config_nb_backup;
        $shadow_drive->login = $login;
        $shadow_drive->password = $this->encrypt($password);
        $shadow_drive->server = $server;
        $shadow_drive->directory = $directory;

        if (!$shadow_drive->save()) {
            $result['success'] = 0;
        }

        $result['id_ntbr_shadow_drive'] = $shadow_drive->id;

        return $result;
    }

    public function saveWebdav(
        $id_ntbr_config,
        $id_ntbr_webdav,
        $name,
        $active,
        $config_nb_backup,
        $login,
        $password,
        $server,
        $directory
    ) {
        $result = [
            'success' => 1,
            'errors' => [],
            'id_ntbr_webdav' => 0,
        ];

        // Required data
        if (!$name || trim($name) == '') {
            $result['errors'][] = $this->l('The name is required.', self::PAGE);
        }

        if (!$server || trim($server) == '') {
            $result['errors'][] = $this->l('The URL is required.', self::PAGE);
        }

        if (!$login || trim($login) == '') {
            $result['errors'][] = $this->l('The login is required.', self::PAGE);
        }

        if ($id_ntbr_webdav) {
            $webdav = new WebdavNtbr($id_ntbr_webdav);

            if (!$password || trim($password) == '' || $password == self::FAKE_MDP) {
                $password = $this->decrypt($webdav->password);
            }
        } else {
            $webdav = new WebdavNtbr();
        }

        if (!$password || trim($password) == '') {
            $result['errors'][] = $this->l('The password is required.', self::PAGE);
        }

        // Data validity
        if (!Validate::isGenericName($name) || trim($name) == '') {
            $result['errors'][] = $this->l('The account name is not valid. Please do not use those characters', self::PAGE) . ' "<>={}"';
        } else {
            $name_exists_id = WebdavNtbr::getIdByName($id_ntbr_config, $name);

            if ($name_exists_id && $id_ntbr_webdav != $name_exists_id) {
                $result['errors'][] = $this->l('The account name is already used', self::PAGE);
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        // Check connection
        if ($active) {
            $connection = $this->testWebdavConnection($server, $login, $password);

            if (!$connection) {
                $result['errors'][] = sprintf(
                    $this->l('Unable to connect to your %s account', self::PAGE),
                    self::WEBDAV
                );
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        // Save data
        $webdav->id_ntbr_config = $id_ntbr_config;
        $webdav->name = $name;
        $webdav->active = $active;
        $webdav->config_nb_backup = $config_nb_backup;
        $webdav->login = $login;
        $webdav->password = $this->encrypt($password);
        $webdav->server = $server;
        $webdav->directory = $directory;

        if (!$webdav->save()) {
            $result['success'] = 0;
        }

        $result['id_ntbr_webdav'] = $webdav->id;

        return $result;
    }

    public function saveGoogledrive(
        $id_ntbr_config,
        $id_ntbr_googledrive,
        $name,
        $active,
        $config_nb_backup,
        $code,
        $directory_path,
        $directory_key
    ) {
        $token = '';

        $result = [
            'success' => 1,
            'errors' => [],
            'id_ntbr_googledrive' => 0,
        ];

        // Required data
        if (!$name || trim($name) == '') {
            $result['errors'][] = $this->l('The name is required.', self::PAGE);
        }

        // Data validity
        if (!Validate::isGenericName($name) || trim($name) == '') {
            $result['errors'][] = $this->l('The account name is not valid. Please do not use those characters', self::PAGE) . ' "<>={}"';
        } else {
            $name_exists_id = GoogledriveNtbr::getIdByName($id_ntbr_config, $name);

            if ($name_exists_id && $id_ntbr_googledrive != $name_exists_id) {
                $result['errors'][] = $this->l('The account name is already used', self::PAGE);
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        if ($id_ntbr_googledrive) {
            $googledrive = new GoogledriveNtbr($id_ntbr_googledrive);
        } else {
            $googledrive = new GoogledriveNtbr();
        }

        // Check connection
        $connection = true;

        if ($code != '' && $code != self::FAKE_MDP) {
            // Get new token
            $decode_token = $this->getGoogledriveAccessToken($code);

            $decode_token['access_token'] = $this->encrypt($decode_token['access_token']);
            $decode_token['refresh_token'] = $this->encrypt($decode_token['refresh_token']);

            $token = json_encode($decode_token);
        } else {
            // Get current token
            $token = GoogledriveNtbr::getGoogledriveTokenById($id_ntbr_googledrive);

            if ($token && $token != '' && $active) {
                $connection = $this->testGoogledriveConnection($token);
            }
        }

        if (!$token || $token == '') {
            $connection = false;
        }

        if (!$connection && $active) {
            $result['errors'][] = sprintf(
                $this->l('Unable to connect to your %s account', self::PAGE),
                self::GOOGLEDRIVE
            );
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        if (!$directory_key || $directory_key == '') {
            $directory_key = NtbrCore::GOOGLEDRIVE_ROOT_ID;
        }

        if (!$directory_path || $directory_path == '') {
            $directory_path = $this->l('Home', self::PAGE);
        }

        $googledrive->id_ntbr_config = $id_ntbr_config;
        $googledrive->name = $name;
        $googledrive->active = $active;
        $googledrive->config_nb_backup = $config_nb_backup;
        $googledrive->directory_path = $directory_path;
        $googledrive->directory_key = $directory_key;
        $googledrive->token = $token;

        if (!$googledrive->save()) {
            $result['success'] = 0;
        }

        $result['id_ntbr_googledrive'] = $googledrive->id;

        return $result;
    }

    public function saveGooglecloud(
        $id_ntbr_config,
        $id_ntbr_googlecloud,
        $name,
        $active,
        $config_nb_backup,
        $code,
        $bucket,
        $directory
    ) {
        $token = '';

        $result = [
            'success' => 1,
            'errors' => [],
            'id_ntbr_googlecloud' => 0,
        ];

        // Required data
        if (!$name || trim($name) == '') {
            $result['errors'][] = $this->l('The name is required.', self::PAGE);
        }

        if (!$bucket || trim($bucket) == '') {
            $result['errors'][] = $this->l('The bucket is required.', self::PAGE);
        }

        // Data validity
        if (!Validate::isGenericName($name) || trim($name) == '') {
            $result['errors'][] = $this->l('The account name is not valid. Please do not use those characters', self::PAGE) . ' "<>={}"';
        } else {
            $name_exists_id = GooglecloudNtbr::getIdByName($id_ntbr_config, $name);

            if ($name_exists_id && $id_ntbr_googlecloud != $name_exists_id) {
                $result['errors'][] = $this->l('The account name is already used', self::PAGE);
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        if ($id_ntbr_googlecloud) {
            $googlecloud = new GooglecloudNtbr($id_ntbr_googlecloud);
        } else {
            $googlecloud = new GooglecloudNtbr();
        }

        // Check connection
        $connection = true;

        if ($code != '' && $code != self::FAKE_MDP) {
            // Get new token
            $decode_token = $this->getGooglecloudAccessToken($code);

            $decode_token['access_token'] = $this->encrypt($decode_token['access_token']);
            $decode_token['refresh_token'] = $this->encrypt($decode_token['refresh_token']);

            $token = json_encode($decode_token);
        } else {
            // Get current token
            $token = GooglecloudNtbr::getGooglecloudTokenById($id_ntbr_googlecloud);

            if ($token && $token != '' && $active) {
                $connection = $this->testGooglecloudConnection($bucket, $token);
            }
        }

        if (!$token || $token == '') {
            $connection = false;
        }

        if (!$connection && $active) {
            $result['errors'][] = sprintf(
                $this->l('Unable to connect to your %s account', self::PAGE),
                self::GOOGLECLOUD
            );
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        $googlecloud->id_ntbr_config = $id_ntbr_config;
        $googlecloud->name = $name;
        $googlecloud->active = $active;
        $googlecloud->config_nb_backup = $config_nb_backup;
        $googlecloud->bucket = $bucket;
        $googlecloud->directory = $directory;
        $googlecloud->token = $token;

        if (!$googlecloud->save()) {
            $result['success'] = 0;
        }

        $result['id_ntbr_googlecloud'] = $googlecloud->id;

        return $result;
    }

    public function savePcloud(
        $id_ntbr_config,
        $id_ntbr_pcloud,
        $name,
        $active,
        $config_nb_backup,
        $code,
        $location_id,
        $directory_id,
        $directory_path
    ) {
        $token = '';

        $result = [
            'success' => 1,
            'errors' => [],
            'id_ntbr_pcloud' => 0,
        ];

        // Required data
        if (!$name || trim($name) == '') {
            $result['errors'][] = $this->l('The name is required.', self::PAGE);
        }

        if (!$location_id) {
            $result['errors'][] = $this->l('The location is required.', self::PAGE);
        }

        // Data validity
        if (!Validate::isGenericName($name) || trim($name) == '') {
            $result['errors'][] = $this->l('The account name is not valid. Please do not use those characters', self::PAGE) . ' "<>={}"';
        } else {
            $name_exists_id = PcloudNtbr::getIdByName($id_ntbr_config, $name);

            if ($name_exists_id && $id_ntbr_pcloud != $name_exists_id) {
                $result['errors'][] = $this->l('The account name is already used', self::PAGE);
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        if ($id_ntbr_pcloud) {
            $pcloud = new PcloudNtbr($id_ntbr_pcloud);
        } else {
            $pcloud = new PcloudNtbr();
        }

        // Check connection
        $connection = true;

        if ($code != '' && $code != self::FAKE_MDP) {
            // Get new token
            $decode_token = $this->getPcloudAccessToken($location_id, $code);
            $token = $this->encrypt($decode_token);
        } else {
            // Get current token
            $token = PcloudNtbr::getPcloudTokenById($id_ntbr_pcloud);

            if ($token && $token != '' && $active) {
                $connection = $this->testPcloudConnection($location_id, $token);
            }
        }

        if (!$token || $token == '') {
            $connection = false;
        }

        if (!$connection && $active) {
            $result['errors'][] = sprintf(
                $this->l('Unable to connect to your %s account', self::PAGE),
                self::PCLOUD
            );
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        $pcloud->id_ntbr_config = $id_ntbr_config;
        $pcloud->name = $name;
        $pcloud->active = $active;
        $pcloud->config_nb_backup = $config_nb_backup;
        $pcloud->location_id = $location_id;
        $pcloud->directory_id = $directory_id;
        $pcloud->directory_path = $directory_path;
        $pcloud->token = $token;

        if (!$pcloud->save()) {
            $result['success'] = 0;
        }

        $result['id_ntbr_pcloud'] = $pcloud->id;

        return $result;
    }

    public function saveBox(
        $id_ntbr_config,
        $id_ntbr_box,
        $name,
        $active,
        $config_nb_backup,
        $code,
        $directory_path,
        $directory_key
    ) {
        $token = '';

        $result = [
            'success' => 1,
            'errors' => [],
            'id_ntbr_box' => 0,
        ];

        // Required data
        if (!$name || trim($name) == '') {
            $result['errors'][] = $this->l('The name is required.', self::PAGE);
        }

        // Data validity
        if (!Validate::isGenericName($name) || trim($name) == '') {
            $result['errors'][] = $this->l('The account name is not valid. Please do not use those characters', self::PAGE) . ' "<>={}"';
        } else {
            $name_exists_id = BoxNtbr::getIdByName($id_ntbr_config, $name);

            if ($name_exists_id && $id_ntbr_box != $name_exists_id) {
                $result['errors'][] = $this->l('The account name is already used', self::PAGE);
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        if ($id_ntbr_box) {
            $box = new BoxNtbr($id_ntbr_box);
        } else {
            $box = new BoxNtbr();
        }

        // Check connection
        $connection = true;

        if ($code != '' && $code != self::FAKE_MDP) {
            // Get new token
            $decode_token = $this->getBoxAccessToken($code);

            $decode_token['access_token'] = $this->encrypt($decode_token['access_token']);
            $decode_token['refresh_token'] = $this->encrypt($decode_token['refresh_token']);

            $token = json_encode($decode_token);
        } else {
            // Get current token
            $token = BoxNtbr::getBoxTokenById($id_ntbr_box);

            if ($token && $token != '' && $active) {
                $connection = $this->testBoxConnection($token, $id_ntbr_box);
            }
        }

        if (!$token || $token == '') {
            $connection = false;
        }

        if (!$connection && $active) {
            $result['errors'][] = sprintf(
                $this->l('Unable to connect to your %s account', self::PAGE),
                self::BOX
            );
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        if (!$directory_key || $directory_key == '') {
            $directory_key = NtbrCore::BOX_ROOT_ID;
        }

        if (!$directory_path || $directory_path == '') {
            $directory_path = $this->l('Home', self::PAGE);
        }

        $box->id_ntbr_config = $id_ntbr_config;
        $box->name = $name;
        $box->active = $active;
        $box->config_nb_backup = $config_nb_backup;
        $box->directory_path = $directory_path;
        $box->directory_key = $directory_key;
        $box->token = $token;

        if (!$box->save()) {
            $result['success'] = 0;
        }

        $result['id_ntbr_box'] = $box->id;

        return $result;
    }

    public function saveOnedrive(
        $id_ntbr_config,
        $id_ntbr_onedrive,
        $name,
        $active,
        $config_nb_backup,
        $code,
        $directory_path,
        $directory_key,
        $business
    ) {
        $token = '';
        $my_site = '';

        $result = [
            'success' => 1,
            'errors' => [],
            'id_ntbr_onedrive' => 0,
        ];

        // Required data
        if (!$name || trim($name) == '') {
            $result['errors'][] = $this->l('The name is required.', self::PAGE);
        }

        // Data validity
        if (!Validate::isGenericName($name) || trim($name) == '') {
            $result['errors'][] = $this->l('The account name is not valid. Please do not use those characters', self::PAGE) . ' "<>={}"';
        } else {
            $name_exists_id = OnedriveNtbr::getIdByName($id_ntbr_config, $name);

            if ($name_exists_id && $id_ntbr_onedrive != $name_exists_id) {
                $result['errors'][] = $this->l('The account name is already used', self::PAGE);
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        if ($id_ntbr_onedrive) {
            $onedrive = new OnedriveNtbr($id_ntbr_onedrive);
        // $my_site = $onedrive->my_site;
        } else {
            $onedrive = new OnedriveNtbr();
        }

        // Check connection
        $connection = true;

        if ($code != '' && $code != self::FAKE_MDP) {
            $onedrive_lib = $this->connectToOnedrive();

            // Get new token
            $access_token = $onedrive_lib->getToken($code, $business);

            if (empty($access_token) || !$access_token) {
                $connection = false;
            } else {
                $decode_token = $access_token;

                $decode_token['access_token'] = $this->encrypt($decode_token['access_token']);
                $decode_token['refresh_token'] = $this->encrypt($decode_token['refresh_token']);

                $token = json_encode($decode_token);
            }
        } else {
            // Get current token
            $token = OnedriveNtbr::getOnedriveTokenById($id_ntbr_onedrive);

            if ($token && $token != '' && $active) {
                $connection = $this->testOnedriveConnection($token, $id_ntbr_onedrive);
            }
        }

        if (!$token || $token == '') {
            $connection = false;
        }

        if (!$connection && $active) {
            $result['errors'][] = sprintf($this->l('Unable to connect to your %s account', self::PAGE), self::ONEDRIVE);
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        if (!$directory_key || $directory_key == '') {
            $onedrive_lib = $this->connectToOnedrive($token, $id_ntbr_onedrive);
            $directory_key = $onedrive_lib->getRootID();
        }

        if (!$directory_path || $directory_path == '') {
            $directory_path = $this->l('Home', self::PAGE);
        }

        $onedrive->id_ntbr_config = $id_ntbr_config;
        $onedrive->name = $name;
        $onedrive->active = $active;
        $onedrive->config_nb_backup = $config_nb_backup;
        $onedrive->directory_path = $directory_path;
        $onedrive->directory_key = $directory_key;
        $onedrive->token = $token;
        $onedrive->my_site = $my_site;
        $onedrive->business = $business;

        if (!$onedrive->save()) {
            $result['success'] = 0;
        }

        $result['id_ntbr_onedrive'] = $onedrive->id;

        return $result;
    }

    public function saveSugarsync(
        $id_ntbr_config,
        $id_ntbr_sugarsync,
        $name,
        $active,
        $config_nb_backup,
        $login,
        $password,
        $directory_path,
        $directory_key
    ) {
        $token = '';

        $result = [
            'success' => 1,
            'errors' => [],
            'id_ntbr_sugarsync' => 0,
        ];

        // Required data
        if (!$name || $name == '') {
            $result['errors'][] = $this->l('The name is required.', self::PAGE);
        }

        if ($login != '' && $password == '') {
            $result['errors'][] = $this->l('The password is required.', self::PAGE);
        } elseif ($login == '' && $password != '') {
            $result['errors'][] = $this->l('The login is required.', self::PAGE);
        }

        // Data validity
        if (!Validate::isGenericName($name) || $name == '') {
            $result['errors'][] = $this->l('The account name is not valid. Please do not use those characters', self::PAGE) . ' "<>={}"';
        } else {
            $name_exists_id = SugarsyncNtbr::getIdByName($id_ntbr_config, $name);

            if ($name_exists_id && $id_ntbr_sugarsync != $name_exists_id) {
                $result['errors'][] = $this->l('The account name is already used', self::PAGE);
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        if ($id_ntbr_sugarsync) {
            $sugarsync = new SugarsyncNtbr($id_ntbr_sugarsync);
        } else {
            $sugarsync = new SugarsyncNtbr();
        }

        // Check connection
        $connection = true;

        if ($login != '' && ($password != '' && $password != self::FAKE_MDP)) {
            // Get new token
            $refresh_token = $this->getSugarsyncRefreshToken($login, $password);
            $decode_token = $this->getSugarsyncAccessToken($refresh_token);
            $token = json_encode($decode_token);
        } else {
            // Get current token
            $token = SugarsyncNtbr::getSugarsyncTokenById($id_ntbr_sugarsync);

            if ($token && $token != '' && $active) {
                $connection = $this->testSugarsyncConnection($token, $id_ntbr_sugarsync);
            }
        }

        if (!$token || $token == '') {
            $connection = false;
        }

        if (!$connection && $active) {
            $result['errors'][] = sprintf(
                $this->l('Unable to connect to your %s account', self::PAGE),
                self::SUGARSYNC
            );
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        if (!$directory_key || $directory_key == '') {
            $sugarsync_lib = $this->connectToSugarsync($token, $id_ntbr_sugarsync);
            $root = $sugarsync_lib->getRoot();

            $directory_key = basename($root['ref']);
            $directory_path = $root['displayName'];
        }

        $sugarsync->id_ntbr_config = $id_ntbr_config;
        $sugarsync->name = $name;
        $sugarsync->active = $active;
        $sugarsync->config_nb_backup = $config_nb_backup;
        $sugarsync->directory_path = $directory_path;
        $sugarsync->directory_key = $directory_key;
        $sugarsync->token = $token;
        $sugarsync->login = $login;

        if (!$sugarsync->save()) {
            $result['success'] = 0;
        }

        $result['id_ntbr_sugarsync'] = $sugarsync->id;

        return $result;
    }

    /**
     * Get SugarSync user informations
     *
     * @param string $token SugarSync token
     * @param int $id_ntbr_sugarsync ID SugarSync account
     *
     * @return array The information of the user
     */
    public function getSugarsyncUserInformation($token, $id_ntbr_sugarsync)
    {
        $sugarsync_lib = $this->connectToSugarsync($token, $id_ntbr_sugarsync);

        return $sugarsync_lib->getUserInfos();
    }

    public function saveAws(
        $id_ntbr_config,
        $id_ntbr_aws,
        $name,
        $active,
        $config_nb_backup,
        $access_key_id,
        $secret_access_key,
        $region,
        $bucket,
        $host,
        $storage_class,
        $directory_key,
        $directory_path,
        $type_s3,
        $accept_unvalid_ssl
    ) {
        $result = [
            'success' => 1,
            'errors' => [],
            'id_ntbr_aws' => 0,
        ];

        // Required data
        if (!$name || trim($name) == '') {
            $result['errors'][] = $this->l('The name is required.', self::PAGE);
        }

        if (!$id_ntbr_aws) {
            $aws = new AwsNtbr();

            if (!$access_key_id || trim($access_key_id) == '') {
                $result['errors'][] = $this->l('The access key ID is required.', self::PAGE);
            }

            if (!$secret_access_key || trim($secret_access_key) == '') {
                $result['errors'][] = $this->l('The secret access key is required.', self::PAGE);
            }
        } else {
            $aws = new AwsNtbr($id_ntbr_aws);

            if (!$access_key_id || trim($access_key_id) == '' || $access_key_id == NtbrChild::FAKE_MDP) {
                $access_key_id = $this->decrypt($aws->access_key_id);
            }

            if (!$secret_access_key || trim($secret_access_key) == '' || $secret_access_key == NtbrChild::FAKE_MDP) {
                $secret_access_key = $this->decrypt($aws->secret_access_key);
            }
        }

        if (!$region || trim($region) == '') {
            $result['errors'][] = $this->l('The region is required.', self::PAGE);
        }

        if (!$bucket || trim($bucket) == '') {
            $result['errors'][] = $this->l('The bucket is required.', self::PAGE);
        }

        if (!$host || trim($host) == '') {
            $result['errors'][] = $this->l('The host is required.', self::PAGE);
        }

        if (!$type_s3 || trim($type_s3) == '') {
            $result['errors'][] = $this->l('The type is required.', self::PAGE);
        }

        if (!$storage_class
            || !in_array($storage_class, [
                AwsLib::STORAGE_CLASS_STANDARD,
                AwsLib::STORAGE_CLASS_REDUCED_REDUNDANCY,
                AwsLib::STORAGE_CLASS_STANDARD_IA,
                AwsLib::STORAGE_CLASS_ONEZONE_IA,
                AwsLib::STORAGE_CLASS_INTELLIGENT_TIERING,
                AwsLib::STORAGE_CLASS_GLACIER,
                AwsLib::STORAGE_CLASS_DEEP_ARCHIVE,
            ])
        ) {
            $storage_class = AwsLib::STORAGE_CLASS_STANDARD;
        }

        // Data validity
        if (!Validate::isGenericName($name) || trim($name) == '') {
            $result['errors'][] = $this->l('The account name is not valid. Please do not use those characters', self::PAGE) . ' "<>={}"';
        } else {
            $name_exists_id = AwsNtbr::getIdByName($id_ntbr_config, $name);

            if ($name_exists_id && $id_ntbr_aws != $name_exists_id) {
                $result['errors'][] = $this->l('The account name is already used', self::PAGE);
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        // Check connection
        if ($active) {
            $connection = $this->testAwsConnection($access_key_id, $secret_access_key, $region, $bucket, $host, $type_s3, $accept_unvalid_ssl);

            if (!$connection) {
                $result['errors'][] = sprintf($this->l('Unable to connect to your %s account', self::PAGE), self::AWS);
            }
        }

        if (count($result['errors'])) {
            $result['success'] = 0;

            return $result;
        }

        // Save data
        if (!$directory_key) {
            $directory_key = $bucket;
        }

        if (!$directory_path) {
            $directory_path = $bucket;
        }

        $aws->id_ntbr_config = $id_ntbr_config;
        $aws->name = $name;
        $aws->active = $active;
        $aws->config_nb_backup = $config_nb_backup;
        $aws->access_key_id = $this->encrypt($access_key_id);
        $aws->secret_access_key = $this->encrypt($secret_access_key);
        $aws->region = $region;
        $aws->bucket = $bucket;
        $aws->host = $host;
        $aws->storage_class = $storage_class;
        $aws->directory_key = $directory_key;
        $aws->directory_path = $directory_path;
        $aws->type_s3 = $type_s3;
        $aws->accept_unvalid_ssl = $accept_unvalid_ssl;

        if (!$aws->save()) {
            $result['success'] = 0;
        }

        $result['id_ntbr_aws'] = $aws->id;

        return $result;
    }

    public function checkConnectionFtp($id_ntbr_ftp)
    {
        if (!$id_ntbr_ftp) {
            return 0;
        }

        $ftp = new FtpNtbr($id_ntbr_ftp);
        $password = $this->decrypt($ftp->password);

        if ($ftp->sftp) {
            // Connect with SFTP
            if ($this->testSFTP($ftp->server, $ftp->login, $password, $ftp->port)) {
                return 1;
            }
        } else {
            // Connect with FTP
            if ($this->testFTP($ftp->server, $ftp->login, $password, $ftp->port, $ftp->ssl, $ftp->passive_mode)) {
                return 1;
            }
        }

        return 0;
    }

    public function checkConnectionDropbox($id_ntbr_dropbox)
    {
        if (!$id_ntbr_dropbox) {
            return 0;
        }

        $o_dropbox = new DropboxNtbr($id_ntbr_dropbox);

        if ($o_dropbox->token && $o_dropbox->token != '') {
            return (int) (bool) $this->testDropboxConnection($o_dropbox->token, $o_dropbox->business);
        }

        return 0;
    }

    public function checkConnectionYandex($id_ntbr_yandex)
    {
        if (!$id_ntbr_yandex) {
            return 0;
        }

        $token = $this->decrypt(YandexNtbr::getYandexTokenById($id_ntbr_yandex));

        if ($token && $token != '') {
            return (int) (bool) $this->testYandexConnection($token);
        }

        return 0;
    }

    public function checkConnectionOwncloud($id_ntbr_owncloud)
    {
        if (!$id_ntbr_owncloud) {
            return 0;
        }

        $owncloud = new OwncloudNtbr($id_ntbr_owncloud);
        $password = $this->decrypt($owncloud->password);

        if ($this->testOwncloudConnection($owncloud->server, $owncloud->login, $password)) {
            return 1;
        }

        return 0;
    }

    public function checkConnectionShadowDrive($id_ntbr_shadow_drive)
    {
        if (!$id_ntbr_shadow_drive) {
            return 0;
        }

        $shadow_drive = new ShadowDriveNtbr($id_ntbr_shadow_drive);
        $password = $this->decrypt($shadow_drive->password);

        if ($this->testShadowDriveConnection($shadow_drive->server, $shadow_drive->login, $password)) {
            return 1;
        }

        return 0;
    }

    public function checkConnectionWebdav($id_ntbr_webdav)
    {
        if (!$id_ntbr_webdav) {
            return 0;
        }

        $webdav = new WebdavNtbr($id_ntbr_webdav);
        $password = $this->decrypt($webdav->password);

        if ($this->testWebdavConnection($webdav->server, $webdav->login, $password)) {
            return 1;
        }

        return 0;
    }

    public function checkConnectionGoogledrive($id_ntbr_googledrive)
    {
        if (!$id_ntbr_googledrive) {
            return 0;
        }

        $token = GoogledriveNtbr::getGoogledriveTokenById($id_ntbr_googledrive);
        if ($token && $token != '') {
            return (int) (bool) $this->testGoogledriveConnection($token);
        }

        return 0;
    }

    public function checkConnectionGooglecloud($id_ntbr_googlecloud)
    {
        if (!$id_ntbr_googlecloud) {
            return 0;
        }

        $googlecloud = new GooglecloudNtbr($id_ntbr_googlecloud);

        if ($googlecloud->token && $googlecloud->token != '' && $googlecloud->bucket && $googlecloud->bucket != '') {
            return (int) (bool) $this->testGooglecloudConnection($googlecloud->bucket, $googlecloud->token);
        }

        return 0;
    }

    public function checkConnectionPcloud($id_ntbr_pcloud)
    {
        if (!$id_ntbr_pcloud) {
            return 0;
        }

        $pcloud = new PcloudNtbr($id_ntbr_pcloud);

        if ($pcloud->token && $pcloud->token != '') {
            return (int) (bool) $this->testPcloudConnection($pcloud->location_id, $pcloud->token);
        }

        return 0;
    }

    public function checkConnectionBox($id_ntbr_box)
    {
        if (!$id_ntbr_box) {
            return 0;
        }

        $token = BoxNtbr::getBoxTokenById($id_ntbr_box);
        if ($token && $token != '') {
            return (int) (bool) $this->testBoxConnection($token, $id_ntbr_box);
        }

        return 0;
    }

    public function checkConnectionOnedrive($id_ntbr_onedrive)
    {
        if (!$id_ntbr_onedrive) {
            return 0;
        }

        $token = OnedriveNtbr::getOnedriveTokenById($id_ntbr_onedrive);
        if ($token && $token != '') {
            return (int) (bool) $this->testOnedriveConnection($token, $id_ntbr_onedrive);
        }

        return 0;
    }

    public function checkConnectionSugarsync($id_ntbr_sugarsync)
    {
        if (!$id_ntbr_sugarsync) {
            return 0;
        }

        $token = SugarsyncNtbr::getSugarsyncTokenById($id_ntbr_sugarsync);
        if ($token && $token != '') {
            return (int) (bool) $this->testSugarsyncConnection($token, $id_ntbr_sugarsync);
        }

        return 0;
    }

    public function checkConnectionAws($id_ntbr_aws)
    {
        if (!$id_ntbr_aws) {
            return 0;
        }

        $aws = new AwsNtbr($id_ntbr_aws);

        return (int) (bool) $this->testAwsConnection(
            $this->decrypt($aws->access_key_id),
            $this->decrypt($aws->secret_access_key),
            $aws->region,
            $aws->bucket,
            $aws->host,
            $aws->type_s3,
            $aws->accept_unvalid_ssl
        );
    }

    public function deleteFtp($id_ntbr_ftp)
    {
        if (!$id_ntbr_ftp) {
            return 0;
        }

        $ftp = new FtpNtbr($id_ntbr_ftp);

        return (int) (bool) $ftp->delete();
    }

    public function deleteDropbox($id_ntbr_dropbox)
    {
        if (!$id_ntbr_dropbox) {
            return 0;
        }

        $dropbox = new DropboxNtbr($id_ntbr_dropbox);

        return (int) (bool) $dropbox->delete();
    }

    public function deleteYandex($id_ntbr_yandex)
    {
        if (!$id_ntbr_yandex) {
            return 0;
        }

        $yandex = new YandexNtbr($id_ntbr_yandex);

        return (int) (bool) $yandex->delete();
    }

    public function deleteOwncloud($id_ntbr_owncloud)
    {
        if (!$id_ntbr_owncloud) {
            return 0;
        }

        $owncloud = new OwncloudNtbr($id_ntbr_owncloud);

        return (int) (bool) $owncloud->delete();
    }

    public function deleteShadowDrive($id_ntbr_shadow_drive)
    {
        if (!$id_ntbr_shadow_drive) {
            return 0;
        }

        $shadow_drive = new ShadowDriveNtbr($id_ntbr_shadow_drive);

        return (int) (bool) $shadow_drive->delete();
    }

    public function deleteWebdav($id_ntbr_webdav)
    {
        if (!$id_ntbr_webdav) {
            return 0;
        }

        $webdav = new WebdavNtbr($id_ntbr_webdav);

        return (int) (bool) $webdav->delete();
    }

    public function deleteGoogledrive($id_ntbr_googledrive)
    {
        if (!$id_ntbr_googledrive) {
            return 0;
        }

        $googledrive = new GoogledriveNtbr($id_ntbr_googledrive);

        return (int) (bool) $googledrive->delete();
    }

    public function deleteGooglecloud($id_ntbr_googlecloud)
    {
        if (!$id_ntbr_googlecloud) {
            return 0;
        }

        $googlecloud = new GooglecloudNtbr($id_ntbr_googlecloud);

        return (int) (bool) $googlecloud->delete();
    }

    public function deletePcloud($id_ntbr_pcloud)
    {
        if (!$id_ntbr_pcloud) {
            return 0;
        }

        $pcloud = new PcloudNtbr($id_ntbr_pcloud);

        return (int) (bool) $pcloud->delete();
    }

    public function deleteBox($id_ntbr_box)
    {
        if (!$id_ntbr_box) {
            return 0;
        }

        $box = new BoxNtbr($id_ntbr_box);

        return (int) (bool) $box->delete();
    }

    public function deleteOnedrive($id_ntbr_onedrive)
    {
        if (!$id_ntbr_onedrive) {
            return 0;
        }

        $onedrive = new OnedriveNtbr($id_ntbr_onedrive);

        return (int) (bool) $onedrive->delete();
    }

    public function deleteSugarsync($id_ntbr_sugarsync)
    {
        if (!$id_ntbr_sugarsync) {
            return 0;
        }

        $sugarsync = new SugarsyncNtbr($id_ntbr_sugarsync);

        return (int) (bool) $sugarsync->delete();
    }

    public function deleteAws($id_ntbr_aws)
    {
        if (!$id_ntbr_aws) {
            return 0;
        }

        $aws = new AwsNtbr($id_ntbr_aws);

        return (int) (bool) $aws->delete();
    }

    public function getDropboxFilesList($id_ntbr_dropbox)
    {
        $files = $this->l('You need to register a valid authorization code to see the files', self::PAGE);

        if ($id_ntbr_dropbox) {
            $dropbox = new DropboxNtbr($id_ntbr_dropbox);
            $dropbox_lib = $this->connectToDropbox($dropbox->business, $dropbox->token);
            $dropbox_dir = $dropbox->directory;

            // Dropbox dir should end with a "/" except when testing if exist
            if (Tools::substr($dropbox_dir, -1) != '/') {
                $dropbox_dir .= '/';
            }

            // Get informations on the directory and his children
            $files = $this->getDropboxFiles($dropbox_lib, $dropbox_dir);
        } else {
            $files = $this->l('Unknown account', self::PAGE);
        }
        // d($files);
        return $files;
    }

    public function deleteDropboxFile($id_ntbr_dropbox, $file_name, $nb_part)
    {
        $dropbox = new DropboxNtbr($id_ntbr_dropbox);
        $dropbox_lib = $this->connectToDropbox($dropbox->business, $dropbox->token);

        // Dropbox dir should start with a "/" except for root
        if ($dropbox->directory != '' && $dropbox->directory[0] !== '/') {
            $dropbox->directory = '/' . $dropbox->directory;
        }

        // Dropbox dir should end with a "/" except when testing if exist
        if (Tools::substr($dropbox->directory, -1) != '/') {
            $dropbox->directory .= '/';
        }

        if ($nb_part > 1) {
            $success = true;

            for ($i = 1; $i <= $nb_part; ++$i) {
                if (Apparatus::endsWith($file_name, self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS . '.' . self::EXT_CRYPT)) { // tar.gz.crypt
                    $extension = self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS . '.' . self::EXT_CRYPT;
                } elseif (Apparatus::endsWith($file_name, self::EXT_UNCOMPRESS . '.' . self::EXT_CRYPT)) { // tar.crypt
                    $extension = self::EXT_UNCOMPRESS . '.' . self::EXT_CRYPT;
                } elseif (Apparatus::endsWith($file_name, self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS)) { // tar.gz
                    $extension = self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS;
                } else { // tar
                    $extension = self::EXT_UNCOMPRESS;
                }

                $file_destination = $dropbox->directory . basename($file_name, '.' . $extension) . '.' . $i . '.part.' . $extension;

                if ($dropbox_lib->checkExists($file_destination)) {
                    if ($dropbox_lib->deleteFile($file_destination) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);

                        $success = false;
                    }
                }
            }

            return $success;
        } else {
            $file_destination = $dropbox->directory . $file_name;

            if ($dropbox_lib->checkExists($file_destination)) {
                if ($dropbox_lib->deleteFile($file_destination) === false) {
                    $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);

                    return false;
                }
            }
        }

        return true;
    }

    public function downloadDropboxFile($id_ntbr_dropbox, $id_file)
    {
        $dropbox = new DropboxNtbr($id_ntbr_dropbox);
        $dropbox_lib = $this->connectToDropbox($dropbox->business, $dropbox->token);

        $file_infos = $dropbox_lib->downloadFile($id_file);

        if (!isset($file_infos['link'])) {
            return false;
        }

        return $file_infos['link'];
    }

    public function getYandexFilesList($id_ntbr_yandex)
    {
        $files = $this->l('You need to register a valid authorization code to see the files', self::PAGE);

        if ($id_ntbr_yandex) {
            $yandex = new YandexNtbr($id_ntbr_yandex);
            $access_token = $this->decrypt($yandex->token);
            $yandex_lib = $this->connectToYandex($access_token);
            $yandex_dir = $yandex->directory;

            // Yandex dir should end with a "/" except when testing if exist
            if (Tools::substr($yandex_dir, -1) != '/') {
                $yandex_dir .= '/';
            }

            // Get informations on the directory and his children
            $files = $this->getYandexFiles($yandex_lib, $yandex_dir);
        } else {
            $files = $this->l('Unknown account', self::PAGE);
        }
        // d($files);
        return $files;
    }

    public function deleteYandexFile($id_ntbr_yandex, $file_name, $nb_part)
    {
        $yandex = new YandexNtbr($id_ntbr_yandex);
        $access_token = $this->decrypt($yandex->token);
        $yandex_lib = $this->connectToYandex($access_token);

        // Yandex dir should start with a "/" except for root
        if ($yandex->directory != '' && $yandex->directory[0] !== '/') {
            $yandex->directory = '/' . $yandex->directory;
        }

        // Yandex dir should end with a "/" except when testing if exist
        if (Tools::substr($yandex->directory, -1) != '/') {
            $yandex->directory .= '/';
        }

        if ($nb_part > 1) {
            $success = true;

            for ($i = 1; $i <= $nb_part; ++$i) {
                if (Apparatus::endsWith($file_name, self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS . '.' . self::EXT_CRYPT)) { // tar.gz.crypt
                    $extension = self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS . '.' . self::EXT_CRYPT;
                } elseif (Apparatus::endsWith($file_name, self::EXT_UNCOMPRESS . '.' . self::EXT_CRYPT)) { // tar.crypt
                    $extension = self::EXT_UNCOMPRESS . '.' . self::EXT_CRYPT;
                } elseif (Apparatus::endsWith($file_name, self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS)) { // tar.gz
                    $extension = self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS;
                } else { // tar
                    $extension = self::EXT_UNCOMPRESS;
                }

                $file_destination = $yandex->directory . basename($file_name, '.' . $extension) . '.' . $i . '.part.' . $extension;

                if ($yandex_lib->checkExists($file_destination)) {
                    if ($yandex_lib->deleteFile($file_destination) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);

                        $success = false;
                    }
                }
            }

            return $success;
        } else {
            $file_destination = $yandex->directory . $file_name;

            if ($yandex_lib->checkExists($file_destination)) {
                if ($yandex_lib->deleteFile($file_destination) === false) {
                    $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);

                    return false;
                }
            }
        }

        return true;
    }

    public function downloadYandexFile($id_ntbr_yandex, $id_file)
    {
        $yandex = new YandexNtbr($id_ntbr_yandex);
        $access_token = $this->decrypt($yandex->token);
        $yandex_lib = $this->connectToYandex($access_token);

        $file_infos = $yandex_lib->downloadFile($id_file);

        if (!isset($file_infos['href'])) {
            return false;
        }

        return $file_infos['href'];
    }

    public function getGoogledriveFilesList($id_ntbr_googledrive)
    {
        $files = $this->l('You need to register a valid authorization code to see the files', self::PAGE);

        if ($id_ntbr_googledrive) {
            $googledrive = new GoogledriveNtbr($id_ntbr_googledrive);
            $access_token = $googledrive->token;
            $googledrive_lib = $this->connectToGoogledrive($access_token);

            // Get informations on the directory and his children
            $files = $this->getGoogledriveFiles($googledrive_lib, $googledrive->directory_key);
        } else {
            $files = $this->l('Unknown account', self::PAGE);
        }
        // d($files);
        return $files;
    }

    public function downloadGoogledriveFile($id_ntbr_googledrive, $id_file)
    {
        $googledrive = new GoogledriveNtbr($id_ntbr_googledrive);
        $access_token = $googledrive->token;
        $googledrive_lib = $this->connectToGoogledrive($access_token);

        $link = $googledrive_lib->downloadFile($id_file);

        return $link;
    }

    public function deleteGoogledriveFile($id_ntbr_googledrive, $file_name, $nb_part)
    {
        $googledrive = new GoogledriveNtbr($id_ntbr_googledrive);
        $access_token = $googledrive->token;
        $googledrive_lib = $this->connectToGoogledrive($access_token);

        // Get informations on the directory and his children
        $files = $this->getGoogledriveFiles($googledrive_lib, $googledrive->directory_key);

        foreach ($files as $file) {
            if ($file['name'] == $file_name) {
                if ($nb_part > 1) {
                    $success = true;

                    foreach ($file['part'] as $part) {
                        if ($googledrive_lib->deleteFile($part['file_id']) === false) {
                            $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);

                            $success = false;
                        }
                    }

                    return $success;
                } else {
                    if ($googledrive_lib->deleteFile($file['file_id']) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file['name']);

                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function getGooglecloudFilesList($id_ntbr_googlecloud)
    {
        $files = $this->l('You need to register a valid authorization code to see the files', self::PAGE);

        if ($id_ntbr_googlecloud) {
            $googlecloud = new GooglecloudNtbr($id_ntbr_googlecloud);
            $googlecloud_lib = $this->connectToGooglecloud($googlecloud->bucket, $googlecloud->token);

            // Get informations on the directory and his children
            $files = $this->getGooglecloudFiles($googlecloud_lib, $googlecloud->directory);
        } else {
            $files = $this->l('Unknown account', self::PAGE);
        }
        // d($files);
        return $files;
    }

    public function deleteGooglecloudFile($id_ntbr_googlecloud, $file_name, $nb_part)
    {
        $googlecloud = new GooglecloudNtbr($id_ntbr_googlecloud);
        $googlecloud_lib = $this->connectToGooglecloud($googlecloud->bucket, $googlecloud->token);

        // Dir should end with a "/"
        if (Tools::substr($googlecloud->directory, -1) != '/') {
            $googlecloud->directory .= '/';
        }

        if ($nb_part > 1) {
            $success = true;

            for ($i = 1; $i <= $nb_part; ++$i) {
                if (Apparatus::endsWith($file_name, self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS . '.' . self::EXT_CRYPT)) { // tar.gz.crypt
                    $extension = self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS . '.' . self::EXT_CRYPT;
                } elseif (Apparatus::endsWith($file_name, self::EXT_UNCOMPRESS . '.' . self::EXT_CRYPT)) { // tar.crypt
                    $extension = self::EXT_UNCOMPRESS . '.' . self::EXT_CRYPT;
                } elseif (Apparatus::endsWith($file_name, self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS)) { // tar.gz
                    $extension = self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS;
                } else { // tar
                    $extension = self::EXT_UNCOMPRESS;
                }

                $file_destination = $googlecloud->directory . basename($file_name, '.' . $extension) . '.' . $i . '.part.' . $extension;

                if ($googlecloud_lib->checkExists($file_destination)) {
                    if ($googlecloud_lib->deleteItem($file_destination) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);

                        $success = false;
                    }
                }
            }

            return $success;
        } else {
            $file_destination = $googlecloud->directory . $file_name;

            if ($googlecloud_lib->checkExists($file_destination)) {
                if ($googlecloud_lib->deleteItem($file_destination) === false) {
                    $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);

                    return false;
                }
            }
        }

        return true;
    }

    public function getPcloudFilesList($id_ntbr_pcloud)
    {
        $files = $this->l('You need to register a valid authorization code to see the files', self::PAGE);

        if ($id_ntbr_pcloud) {
            $pcloud = new PcloudNtbr($id_ntbr_pcloud);
            $pcloud_lib = $this->connectToPcloud($pcloud->location_id, $pcloud->token);

            // Get informations on the directory and his children
            $files = $this->getPcloudFiles($pcloud_lib, $pcloud->directory_id);
        } else {
            $files = $this->l('Unknown account', self::PAGE);
        }
        // d($files);
        return $files;
    }

    public function deletePcloudFile($id_ntbr_pcloud, $file_name, $nb_part)
    {
        $pcloud = new PcloudNtbr($id_ntbr_pcloud);
        $pcloud_lib = $this->connectToPcloud($pcloud->location_id, $pcloud->token);

        // Dir should end with a "/"
        if (Tools::substr($pcloud->directory_path, -1) != '/') {
            $pcloud->directory_path .= '/';
        }

        if ($nb_part > 1) {
            $success = true;

            for ($i = 1; $i <= $nb_part; ++$i) {
                if (Apparatus::endsWith($file_name, self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS . '.' . self::EXT_CRYPT)) { // tar.gz.crypt
                    $extension = self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS . '.' . self::EXT_CRYPT;
                } elseif (Apparatus::endsWith($file_name, self::EXT_UNCOMPRESS . '.' . self::EXT_CRYPT)) { // tar.crypt
                    $extension = self::EXT_UNCOMPRESS . '.' . self::EXT_CRYPT;
                } elseif (Apparatus::endsWith($file_name, self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS)) { // tar.gz
                    $extension = self::EXT_UNCOMPRESS . '.' . self::EXT_COMPRESS;
                } else { // tar
                    $extension = self::EXT_UNCOMPRESS;
                }

                $file_destination = $pcloud->directory_path . basename($file_name, '.' . $extension) . '.' . $i . '.part.' . $extension;

                if ($pcloud_lib->checkExists($file_destination)) {
                    if ($pcloud_lib->deleteItem($file_destination) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);

                        $success = false;
                    }
                }
            }

            return $success;
        } else {
            $file_destination = $pcloud->directory_path . $file_name;

            if ($pcloud_lib->checkExists($file_destination)) {
                if ($pcloud_lib->deleteItem($file_destination) === false) {
                    $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file_destination);

                    return false;
                }
            }
        }

        return true;
    }

    public function getAwsFilesList($id_ntbr_aws)
    {
        $files = $this->l('You need to register a valid authorization code to see the files', self::PAGE);

        if ($id_ntbr_aws) {
            $aws = new AwsNtbr($id_ntbr_aws);

            if (false === $aws->bucket || '' == $aws->bucket) {
                $this->log('WAR' . $this->l('No bucket configured', self::PAGE));

                return false;
            }

            if (false === $aws->host || '' == $aws->host) {
                $this->log('WAR' . $this->l('No host configured', self::PAGE));

                return false;
            }

            if (false === $aws->type_s3 || '' == $aws->type_s3 || 0 == $aws->type_s3) {
                $this->log('WAR' . $this->l('No type configured', self::PAGE));

                return false;
            }

            $aws_lib = $this->connectToAws(
                $this->decrypt($aws->access_key_id),
                $this->decrypt($aws->secret_access_key),
                $aws->region,
                $aws->bucket,
                $aws->host,
                $aws->type_s3,
                $aws->accept_unvalid_ssl
            );

            // Get informations on the directory and his children
            $files = $this->getAwsFiles($aws_lib, $aws->directory_key);
        } else {
            $files = $this->l('Unknown account', self::PAGE);
        }
        // d($files);
        return $files;
    }

    public function getBoxFilesList($id_ntbr_box)
    {
        $files = $this->l('You need to register a valid authorization code to see the files', self::PAGE);

        if ($id_ntbr_box) {
            $box = new BoxNtbr($id_ntbr_box);
            $access_token = $box->token;
            $box_lib = $this->connectToBox($access_token, $id_ntbr_box);

            // Get informations on the directory and his children
            $files = $this->getBoxFiles($box_lib, $box->directory_key);
        } else {
            $files = $this->l('Unknown account', self::PAGE);
        }
        // d($files);
        return $files;
    }

    public function downloadBoxFile($id_ntbr_box, $id_file)
    {
        $box = new BoxNtbr($id_ntbr_box);
        $access_token = $box->token;
        $box_lib = $this->connectToBox($access_token, $id_ntbr_box);

        $link = $box_lib->downloadFile($id_file);

        return $link;
    }

    public function deleteBoxFile($id_ntbr_box, $file_name, $nb_part)
    {
        $box = new BoxNtbr($id_ntbr_box);
        $access_token = $box->token;
        $box_lib = $this->connectToBox($access_token, $id_ntbr_box);

        // Get informations on the directory and his children
        $files = $this->getBoxFiles($box_lib, $box->directory_key);

        foreach ($files as $file) {
            if ($file['name'] == $file_name) {
                if ($nb_part > 1) {
                    $success = true;

                    foreach ($file['part'] as $part) {
                        if ($box_lib->deleteFile($part['file_id']) === false) {
                            $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);

                            $success = false;
                        }
                    }

                    return $success;
                } else {
                    if ($box_lib->deleteFile($file['file_id']) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file['name']);

                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function getOnedriveFilesList($id_ntbr_onedrive)
    {
        $files = $this->l('You need to register a valid authorization code to see the files', self::PAGE);

        if ($id_ntbr_onedrive) {
            $onedrive = new OnedriveNtbr($id_ntbr_onedrive);
            $access_token = $onedrive->token;
            $onedrive_lib = $this->connectToOnedrive($access_token, $id_ntbr_onedrive);

            // Get informations on the directory and his children
            $files = $this->getOnedriveFiles($onedrive_lib, $onedrive->directory_key);
        } else {
            $files = $this->l('Unknown account', self::PAGE);
        }
        // d($files);
        return $files;
    }

    public function downloadOnedriveFile($id_ntbr_onedrive, $id_file)
    {
        $onedrive = new OnedriveNtbr($id_ntbr_onedrive);
        $access_token = $onedrive->token;
        $onedrive_lib = $this->connectToOnedrive($access_token);

        $infos = $onedrive_lib->getMetadatas($id_file);

        if (isset($infos['@content.downloadUrl'])) {
            return $infos['@content.downloadUrl'];
        }

        return false;
    }

    public function deleteOnedriveFile($id_ntbr_onedrive, $file_name, $nb_part)
    {
        $onedrive = new OnedriveNtbr($id_ntbr_onedrive);
        $access_token = $onedrive->token;
        $onedrive_lib = $this->connectToOnedrive($access_token, $id_ntbr_onedrive);

        // Get informations on the directory and his children
        $files = $this->getOnedriveFiles($onedrive_lib, $onedrive->directory_key);

        if ($files === false) {
            return true; // No file to delete
        }

        foreach ($files as $file) {
            if ($file['name'] == $file_name) {
                if ($nb_part > 1) {
                    $success = true;

                    foreach ($file['part'] as $part) {
                        if ($onedrive_lib->deleteItem($part['file_id']) === false) {
                            $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);

                            $success = false;
                        }
                    }

                    return $success;
                } else {
                    if ($onedrive_lib->deleteItem($file['file_id']) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file['name']);

                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function deleteAwsFile($id_ntbr_aws, $file_name, $nb_part)
    {
        $aws = new AwsNtbr($id_ntbr_aws);

        if (false === $aws->bucket || '' == $aws->bucket) {
            $this->log('WAR' . $this->l('No bucket configured', self::PAGE));

            return false;
        }

        if (false === $aws->host || '' == $aws->host) {
            $this->log('WAR' . $this->l('No host configured', self::PAGE));

            return false;
        }

        if (false === $aws->type_s3 || '' == $aws->type_s3 || 0 == $aws->type_s3) {
            $this->log('WAR' . $this->l('No type configured', self::PAGE));

            return false;
        }

        $aws_lib = $this->connectToAws(
            $this->decrypt($aws->access_key_id),
            $this->decrypt($aws->secret_access_key),
            $aws->region,
            $aws->bucket,
            $aws->host,
            $aws->type_s3,
            $aws->accept_unvalid_ssl
        );

        // Get informations on the directory and his children
        $files = $this->getAwsFiles($aws_lib, $aws->directory_key);

        if ($files === false) {
            return true; // No file to delete
        }

        foreach ($files as $file) {
            if ($file['name'] == $file_name) {
                if ($nb_part > 1) {
                    $success = true;

                    foreach ($file['part'] as $part) {
                        if ($aws->type_s3 == NtbrChild::S3_TYPE_BACKBLAZE && isset($part['version_id'])) {
                            if ($aws_lib->deleteFile($part['file_id'], $part['version_id']) === false) {
                                $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);

                                $success = false;
                            }
                        } else {
                            if ($aws_lib->deleteFile($part['file_id']) === false) {
                                $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);

                                $success = false;
                            }
                        }
                    }

                    return $success;
                } else {
                    if ($aws->type_s3 == NtbrChild::S3_TYPE_BACKBLAZE && isset($file['version_id'])) {
                        if ($aws_lib->deleteFile($file['file_id'], $file['version_id']) === false) {
                            $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file['name']);

                            return false;
                        }
                    } else {
                        if ($aws_lib->deleteFile($file['file_id']) === false) {
                            $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file['name']);

                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    public function getOwncloudFilesList($id_ntbr_owncloud)
    {
        $files = $this->l('You need to register a valid authorization code to see the files', self::PAGE);

        if ($id_ntbr_owncloud) {
            $owncloud = new OwncloudNtbr($id_ntbr_owncloud);
            $owncloud_pass = $this->decrypt($owncloud->password);
            $owncloud_lib = $this->connectToOwncloud($owncloud->server, $owncloud->login, $owncloud_pass);

            // Get informations on the directory and his children
            $files = $this->getOwncloudFiles($owncloud_lib, $owncloud->directory);
        } else {
            $files = $this->l('Unknown account', self::PAGE);
        }
        // d($files);
        return $files;
    }

    public function downloadOwncloudFile($id_ntbr_owncloud, $id_file, $pos, $length, $file_size)
    {
        $owncloud = new OwncloudNtbr($id_ntbr_owncloud);
        $owncloud_pass = $this->decrypt($owncloud->password);
        $owncloud_lib = $this->connectToOwncloud($owncloud->server, $owncloud->login, $owncloud_pass);

        $content = $owncloud_lib->downloadFile($id_file, $pos, $length, $file_size);

        return $content;
    }

    public function deleteOwncloudFile($id_ntbr_owncloud, $file_name, $nb_part)
    {
        $owncloud = new OwncloudNtbr($id_ntbr_owncloud);
        $owncloud_pass = $this->decrypt($owncloud->password);
        $owncloud_lib = $this->connectToOwncloud($owncloud->server, $owncloud->login, $owncloud_pass);

        // Get informations on the directory and his children
        $files = $this->getOwncloudFiles($owncloud_lib, $owncloud->directory);

        if ($files === false) {
            return true; // No file to delete
        }

        foreach ($files as $file) {
            if ($file['name'] == $file_name) {
                if ($nb_part > 1) {
                    $success = true;

                    foreach ($file['part'] as $part) {
                        if ($owncloud_lib->deleteFile($part['file_id']) === false) {
                            $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);

                            $success = false;
                        }
                    }

                    return $success;
                } else {
                    if ($owncloud_lib->deleteFile($file['file_id']) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file['name']);

                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function getShadowDriveFilesList($id_ntbr_shadow_drive)
    {
        $files = $this->l('You need to register a valid authorization code to see the files', self::PAGE);

        if ($id_ntbr_shadow_drive) {
            $shadow_drive = new ShadowDriveNtbr($id_ntbr_shadow_drive);
            $shadow_drive_pass = $this->decrypt($shadow_drive->password);
            $shadow_drive_lib = $this->connectToShadowDrive($shadow_drive->server, $shadow_drive->login, $shadow_drive_pass);

            // Get informations on the directory and his children
            $files = $this->getShadowDriveFiles($shadow_drive_lib, $shadow_drive->directory);
        } else {
            $files = $this->l('Unknown account', self::PAGE);
        }
        // d($files);
        return $files;
    }

    public function downloadShadowDriveFile($id_ntbr_shadow_drive, $id_file, $pos, $length, $file_size)
    {
        $shadow_drive = new ShadowDriveNtbr($id_ntbr_shadow_drive);
        $shadow_drive_pass = $this->decrypt($shadow_drive->password);
        $shadow_drive_lib = $this->connectToShadowDrive($shadow_drive->server, $shadow_drive->login, $shadow_drive_pass);

        $content = $shadow_drive_lib->downloadFile($id_file, $pos, $length, $file_size);

        return $content;
    }

    public function deleteShadowDriveFile($id_ntbr_shadow_drive, $file_name, $nb_part)
    {
        $shadow_drive = new ShadowDriveNtbr($id_ntbr_shadow_drive);
        $shadow_drive_pass = $this->decrypt($shadow_drive->password);
        $shadow_drive_lib = $this->connectToShadowDrive($shadow_drive->server, $shadow_drive->login, $shadow_drive_pass);

        // Get informations on the directory and his children
        $files = $this->getShadowDriveFiles($shadow_drive_lib, $shadow_drive->directory);

        if ($files === false) {
            return true; // No file to delete
        }

        foreach ($files as $file) {
            if ($file['name'] == $file_name) {
                if ($nb_part > 1) {
                    $success = true;

                    foreach ($file['part'] as $part) {
                        if ($shadow_drive_lib->deleteFile($part['file_id']) === false) {
                            $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);

                            $success = false;
                        }
                    }

                    return $success;
                } else {
                    if ($shadow_drive_lib->deleteFile($file['file_id']) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file['name']);

                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function getWebdavFilesList($id_ntbr_webdav)
    {
        $files = $this->l('You need to register a valid authorization code to see the files', self::PAGE);

        if ($id_ntbr_webdav) {
            $webdav = new WebdavNtbr($id_ntbr_webdav);
            $webdav_pass = $this->decrypt($webdav->password);
            $webdav_lib = $this->connectToWebdav($webdav->server, $webdav->login, $webdav_pass);

            // Get informations on the directory and his children
            $files = $this->getWebdavFiles($webdav_lib, $webdav->directory);
        } else {
            $files = $this->l('Unknown account', self::PAGE);
        }
        // d($files);
        return $files;
    }

    public function downloadWebdavFile($id_ntbr_webdav, $id_file, $pos, $length, $file_size)
    {
        $webdav = new WebdavNtbr($id_ntbr_webdav);
        $webdav_pass = $this->decrypt($webdav->password);
        $webdav_lib = $this->connectToWebdav($webdav->server, $webdav->login, $webdav_pass);

        $content = $webdav_lib->downloadFile($id_file, $pos, $length, $file_size);

        return $content;
    }

    public function deleteWebdavFile($id_ntbr_webdav, $file_name, $nb_part)
    {
        $webdav = new WebdavNtbr($id_ntbr_webdav);
        $webdav_pass = $this->decrypt($webdav->password);
        $webdav_lib = $this->connectToWebdav($webdav->server, $webdav->login, $webdav_pass);

        // Get informations on the directory and his children
        $files = $this->getWebdavFiles($webdav_lib, $webdav->directory);

        if ($files === false) {
            return true; // No file to delete
        }

        foreach ($files as $file) {
            if ($file['name'] == $file_name) {
                if ($nb_part > 1) {
                    $success = true;

                    foreach ($file['part'] as $part) {
                        if ($webdav_lib->deleteFile($part['file_id']) === false) {
                            $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']);

                            $success = false;
                        }
                    }

                    return $success;
                } else {
                    if ($webdav_lib->deleteFile($file['file_id']) === false) {
                        $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file['name']);

                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function getFtpFilesList($id_ntbr_ftp)
    {
        $files = $this->l('You need to register a valid authorization code to see the files', self::PAGE);

        if ($id_ntbr_ftp) {
            $ftp = new FtpNtbr($id_ntbr_ftp);
            $ftp_server = $ftp->server;
            $ftp_login = $ftp->login;
            $ftp_pass = $this->decrypt($ftp->password);
            $ftp_port = $ftp->port;
            $ftp_ssl = $ftp->ssl;
            $ftp_pasv = $ftp->passive_mode;
            $ftp_directory = $ftp->directory;

            // FTP/SFTP dir should start and end with a /
            $ftp_directory = rtrim($this->normalizePath($ftp_directory), '/') . '/';
            if ($ftp_directory[0] !== '/') {
                $ftp_directory = '/' . $ftp_directory;
            }

            if (!$ftp->sftp) {
                $connection = $this->connectFtp(
                    $ftp_server,
                    $ftp_login,
                    $ftp_pass,
                    (int) $ftp_port,
                    $ftp_ssl,
                    $ftp_pasv
                );

                if (!$connection) {
                    return $files;
                }

                $ftp_current_directory = ftp_pwd($connection);

                if ($ftp_current_directory != '/') {
                    $ftp_directory = $ftp_current_directory . $ftp_directory;
                }

                ftp_chdir($connection, $ftp_directory);

                // Get informations on the directory and his children
                $files = $this->getFtpFiles($connection);

                ftp_close($connection);
            } else {
                $this->initForSFTP();

                $sftp_lib = new phpseclib\Net\SFTP($ftp_server, $ftp_port);

                // Beware of the warning from php if failure
                if (!@$sftp_lib->login($ftp_login, $ftp_pass)) {
                    $this->log(
                        'WAR'
                        . sprintf(
                            $this->l('Unable to connect to the %s server, please verify your credentials', self::PAGE),
                            self::SFTP
                        ),
                        true
                    );

                    return $files;
                }

                $ftp_directory = $sftp_lib->pwd() . $ftp_directory;

                // Get informations on the directory and his children
                $files = $this->getSftpFiles($sftp_lib, $ftp_directory);

                $this->closeSFTP($sftp_lib);
            }
        } else {
            $files = $this->l('Unknown account', self::PAGE);
        }
        // d($files);
        return $files;
    }

    public function downloadFtpFile($id_ntbr_ftp, $id_file, $pos, $length)
    {
        $content = '';

        if ($id_ntbr_ftp) {
            $ftp = new FtpNtbr($id_ntbr_ftp);
            $ftp_server = $ftp->server;
            $ftp_login = $ftp->login;
            $ftp_pass = $this->decrypt($ftp->password);
            $ftp_port = $ftp->port;
            $ftp_ssl = $ftp->ssl;
            $ftp_pasv = $ftp->passive_mode;
            $ftp_directory = $ftp->directory;

            // FTP/SFTP dir should start and end with a /
            $ftp_directory = rtrim($this->normalizePath($ftp_directory), '/') . '/';
            if ($ftp_directory[0] !== '/') {
                $ftp_directory = '/' . $ftp_directory;
            }

            if (!$ftp->sftp) {
                if ($ftp_ssl) {
                    $type = 'ftps://';
                } else {
                    $type = 'ftp://';
                }

                $start = $pos;
                $end = ($pos + $length) - 1;

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $type . $ftp_server . ':' . (int) $ftp_port . $ftp_directory . $id_file);
                curl_setopt($curl, CURLOPT_USERPWD, $ftp_login . ':' . $ftp_pass);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_RANGE, $start . '-' . $end);

                if ($ftp_pasv) {
                    curl_setopt($curl, CURLOPT_FTP_USE_EPSV, true);
                }

                $curl_copy = curl_copy_handle($curl);
                $content = curl_exec($curl);

                if ($content === false && strpos(curl_error($curl), 'HTTP/2') !== false) {
                    $this->log('HTTP/2 ' . $this->l('issue', self::PAGE), true);

                    curl_close($curl);
                    $curl = curl_copy_handle($curl_copy);
                    curl_close($curl_copy);

                    // The HTTP/2 is not supported, try again with HTTP/1
                    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    $content = curl_exec($curl);
                } else {
                    curl_close($curl_copy);
                }
            } else {
                $this->initForSFTP();

                $sftp_lib = new phpseclib\Net\SFTP($ftp_server, $ftp_port);

                // Beware of the warning from php if failure
                if (!@$sftp_lib->login($ftp_login, $ftp_pass)) {
                    $this->log(
                        'WAR'
                        . sprintf(
                            $this->l('Unable to connect to the %s server, please verify your credentials', self::PAGE),
                            self::SFTP
                        ),
                        true
                    );

                    return $content;
                }

                $ftp_directory = $sftp_lib->pwd() . $ftp_directory;

                $content = $sftp_lib->get($ftp_directory . $id_file, false, $pos, $length);

                $this->closeSFTP($sftp_lib);
            }
        }

        return $content;
    }

    public function deleteFtpFile($id_ntbr_ftp, $file_name, $nb_part)
    {
        if ($id_ntbr_ftp) {
            $ftp = new FtpNtbr($id_ntbr_ftp);
            $ftp_server = $ftp->server;
            $ftp_login = $ftp->login;
            $ftp_pass = $this->decrypt($ftp->password);
            $ftp_port = $ftp->port;
            $ftp_ssl = $ftp->ssl;
            $ftp_pasv = $ftp->passive_mode;
            $ftp_directory = $ftp->directory;

            // FTP/SFTP dir should start and end with a /
            $ftp_directory = rtrim($this->normalizePath($ftp_directory), '/') . '/';
            if ($ftp_directory[0] !== '/') {
                $ftp_directory = '/' . $ftp_directory;
            }

            if (!$ftp->sftp) {
                $connection = $this->connectFtp(
                    $ftp_server,
                    $ftp_login,
                    $ftp_pass,
                    (int) $ftp_port,
                    $ftp_ssl,
                    $ftp_pasv
                );

                if (!$connection) {
                    return false;
                }

                $ftp_current_directory = ftp_pwd($connection);

                if ($ftp_current_directory != '/') {
                    $ftp_directory = $ftp_current_directory . $ftp_directory;
                }

                ftp_chdir($connection, $ftp_directory);

                // Get informations on the directory and his children
                $files = $this->getFtpFiles($connection);

                if ($files === false) {
                    ftp_close($connection);

                    return true; // No file to delete
                }

                $success = true;

                foreach ($files as $file) {
                    if ($file['name'] == $file_name) {
                        if ($nb_part > 1) {
                            foreach ($file['part'] as $part) {
                                if (!ftp_delete($connection, basename($part['name']))) {
                                    $this->log(
                                        $this->l('Error while deleting the file:', self::PAGE) . ' ' . $part['name']
                                    );

                                    $success = false;
                                }
                            }
                        } else {
                            if (!ftp_delete($connection, basename($file['name']))) {
                                $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file['name']);

                                $success = false;
                            }
                        }
                    }
                }

                ftp_close($connection);

                return $success;
            } else {
                $this->initForSFTP();

                $sftp_lib = new phpseclib\Net\SFTP($ftp_server, $ftp_port);

                // Beware of the warning from php if failure
                if (!@$sftp_lib->login($ftp_login, $ftp_pass)) {
                    $this->log(
                        'WAR'
                        . sprintf(
                            $this->l('Unable to connect to the %s server, please verify your credentials', self::PAGE),
                            self::SFTP
                        ),
                        true
                    );

                    return false;
                }

                $ftp_directory = $sftp_lib->pwd() . $ftp_directory;

                // Get informations on the directory and his children
                $files = $this->getSftpFiles($sftp_lib, $ftp_directory);

                if ($files === false) {
                    $this->closeSFTP($sftp_lib);

                    return true; // No file to delete
                }

                $success = true;

                foreach ($files as $file) {
                    if ($file['name'] == $file_name) {
                        if ($nb_part > 1) {
                            foreach ($file['part'] as $part) {
                                if (!$sftp_lib->delete($ftp_directory . basename($part['name']))) {
                                    $this->log(
                                        $this->l('Delete old backup file failed:', self::PAGE) . basename($part['name'])
                                    );
                                    $success = false;
                                }
                            }
                        } else {
                            if (!$sftp_lib->delete($ftp_directory . basename($file['name']))) {
                                $this->log($this->l('Error while deleting the file:', self::PAGE) . ' ' . $file['name']);

                                $success = false;
                            }
                        }
                    }
                }

                $this->closeSFTP($sftp_lib);

                return $success;
            }
        }

        return true;
    }

    public function displayGoogledriveTree($id_ntbr_googledrive)
    {
        $tree = $this->l('You need to register a valid authorization code to choose a directory', self::PAGE);

        if ($id_ntbr_googledrive) {
            $googledrive = new GoogledriveNtbr($id_ntbr_googledrive);
            $tree = $this->getGoogledriveTree($googledrive->directory_key, $id_ntbr_googledrive);
        } else {
            $tree = $this->l('Unknown account', self::PAGE);
        }

        return $tree;
    }

    public function displayGoogledriveTreeChild(
        $id_ntbr_googledrive,
        $id_parent,
        $googledrive_dir,
        $level,
        $path
    ) {
        $tree = $this->l('You need to register a valid authorization code to choose a directory', self::PAGE);

        if ($id_ntbr_googledrive) {
            $googledrive = new GoogledriveNtbr($id_ntbr_googledrive);

            $tree = $this->getGoogledriveTreeChildren(
                $googledrive->token,
                $id_parent,
                $googledrive_dir,
                $level,
                $path,
                $googledrive->id_ntbr_config
            );
        } else {
            $tree = $this->l('Unknown account', self::PAGE);
        }

        return $tree;
    }

    public function displayGooglecloudTree($id_ntbr_googlecloud)
    {
        $tree = $this->l('You need to register a valid authorization code to choose a directory', self::PAGE);

        if ($id_ntbr_googlecloud) {
            $tree = $this->getGooglecloudTree($id_ntbr_googlecloud);
        } else {
            $tree = $this->l('Unknown account', self::PAGE);
        }

        return $tree;
    }

    public function displayGooglecloudTreeChild(
        $id_ntbr_googlecloud,
        $googlecloud_dir,
        $level,
        $path
    ) {
        $tree = $this->l('You need to register a valid authorization code to choose a directory', self::PAGE);

        if ($id_ntbr_googlecloud) {
            $googlecloud = new GooglecloudNtbr($id_ntbr_googlecloud);

            $tree = $this->getGooglecloudTreeChildren(
                $googlecloud->bucket,
                $googlecloud->token,
                $googlecloud_dir,
                $level,
                $path,
                $googlecloud->id_ntbr_config
            );
        } else {
            $tree = $this->l('Unknown account', self::PAGE);
        }

        return $tree;
    }

    public function displayPcloudTree($id_ntbr_pcloud)
    {
        $tree = $this->l('You need to register a valid authorization code to choose a directory', self::PAGE);

        if ($id_ntbr_pcloud) {
            $tree = $this->getPcloudTree($id_ntbr_pcloud);
        } else {
            $tree = $this->l('Unknown account', self::PAGE);
        }

        return $tree;
    }

    public function displayPcloudTreeChild($id_ntbr_pcloud, $pcloud_dir_id, $level)
    {
        $tree = $this->l('You need to register a valid authorization code to choose a directory', self::PAGE);

        if ($id_ntbr_pcloud) {
            $tree = $this->getPcloudTreeChildren($id_ntbr_pcloud, $pcloud_dir_id, $level);
        } else {
            $tree = $this->l('Unknown account', self::PAGE);
        }

        return $tree;
    }

    public function displayBoxTree($id_ntbr_box)
    {
        $tree = $this->l('You need to register a valid authorization code to choose a directory', self::PAGE);

        if ($id_ntbr_box) {
            $box = new BoxNtbr($id_ntbr_box);
            $tree = $this->getBoxTree($box->directory_key, $id_ntbr_box);
        } else {
            $tree = $this->l('Unknown account', self::PAGE);
        }

        return $tree;
    }

    public function displayBoxTreeChild(
        $id_ntbr_box,
        $id_parent,
        $box_dir,
        $level,
        $path
    ) {
        $tree = $this->l('You need to register a valid authorization code to choose a directory', self::PAGE);

        if ($id_ntbr_box) {
            $box = new BoxNtbr($id_ntbr_box);

            $tree = $this->getBoxTreeChildren(
                $box->token,
                $id_parent,
                $box_dir,
                $level,
                $path,
                $box->id_ntbr_config,
                $id_ntbr_box
            );
        } else {
            $tree = $this->l('Unknown account', self::PAGE);
        }

        return $tree;
    }

    public function displayOnedriveTree($id_ntbr_onedrive)
    {
        $tree = $this->l('You need to register a valid authorization code to choose a directory', self::PAGE);

        if ($id_ntbr_onedrive) {
            $onedrive = new OnedriveNtbr($id_ntbr_onedrive);
            $tree = $this->getOnedriveTree(
                $onedrive->token,
                $onedrive->directory_key,
                $id_ntbr_onedrive
            );
        } else {
            $tree = $this->l('Unknown account', self::PAGE);
        }

        return $tree;
    }

    public function displayOnedriveTreeChild($id_ntbr_onedrive, $id_parent, $onedrive_dir, $level, $path)
    {
        $tree = $this->l('You need to register a valid authorization code to choose a directory', self::PAGE);

        if ($id_ntbr_onedrive) {
            $onedrive = new OnedriveNtbr($id_ntbr_onedrive);
            $tree = $this->getOnedriveTreeChildren(
                $onedrive->token,
                $onedrive_dir,
                $id_parent,
                $level,
                $path,
                $id_ntbr_onedrive
            );
        } else {
            $tree = $this->l('Unknown account', self::PAGE);
        }

        return $tree;
    }

    public function displaySugarsyncTree($id_ntbr_sugarsync)
    {
        $tree = $this->l('You need to register a valid authorization code to choose a directory', self::PAGE);

        if ($id_ntbr_sugarsync) {
            $sugarsync = new SugarsyncNtbr($id_ntbr_sugarsync);
            $tree = $this->getSugarsyncTree(
                $sugarsync->token,
                $sugarsync->directory_key,
                $id_ntbr_sugarsync
            );
        } else {
            $tree = $this->l('Unknown account', self::PAGE);
        }

        return $tree;
    }

    public function displaySugarsyncTreeChild($id_ntbr_sugarsync, $id_parent, $sugarsync_dir, $level, $path)
    {
        $tree = $this->l('You need to register a valid authorization code to choose a directory', self::PAGE);

        if ($id_ntbr_sugarsync) {
            $sugarsync = new SugarsyncNtbr($id_ntbr_sugarsync);
            $tree = $this->getSugarsyncTreeChildren(
                $sugarsync->token,
                $sugarsync_dir,
                $id_parent,
                $level,
                $path,
                $id_ntbr_sugarsync
            );
        } else {
            $tree = $this->l('Unknown account', self::PAGE);
        }

        return $tree;
    }

    public function displayAwsTree($id_ntbr_aws)
    {
        $tree = $this->l('You need to register a valid account to choose a directory', self::PAGE);

        if ($id_ntbr_aws) {
            $tree = $this->getAwsTree($id_ntbr_aws);
        } else {
            $tree = $this->l('Unknown account', self::PAGE);
        }

        return $tree;
    }

    public function displayAwsTreeChild($id_ntbr_aws, $directory_key, $directory_path, $level)
    {
        $tree = $this->l('You need to register a valid account to choose a directory', self::PAGE);

        if ($id_ntbr_aws) {
            $tree = $this->getAwsTreeChildren($directory_key, $level, $directory_path, $id_ntbr_aws);
        } else {
            $tree = $this->l('Unknown account', self::PAGE);
        }

        return $tree;
    }

    public function onlySendBackupAway($nb)
    {
        $current_time = time();
        $ntbr_ongoing = GlobConfNtbr::get('NTBR_ONGOING');
        $backups = $this->findThisBackup($nb);

        if (strpos($nb, '.') === false) {
            // We download all the files
            if (is_array($backups)) {
                $first_backup = reset($backups);
            }

            if (!is_array($first_backup) || !isset($first_backup['name'])) {
                $this->log('ERR' . $this->l('The backup was not found', self::PAGE));

                return false;
            }

            $backup_name = $first_backup['name'];
            $backup_list = [];

            foreach ($backups as $backup) {
                $backup_list[] = $backup['name'];
            }
        } else {
            // We download only one file
            if (!is_array($backups) || !isset($backups[$nb]) || !isset($backups[$nb]['name'])) {
                $this->log('ERR' . $this->l('The backup was not found', self::PAGE));

                return false;
            }

            $backup_name = $backups[$nb]['name'];
            $backup_list = [$backups[$nb]['name']];
        }

        $clean_file = preg_replace('/([0-9]+\.part\.)/', '', $backup_name);
        $config = new ConfigNtbr(Backups::getBackupIdConfig($clean_file));
        $time_between_backups = $config->time_between_backups;

        if (!$config->id) {
            $this->log('ERR' . $this->l('Unknown configuration', self::PAGE));

            return false;
        }

        if ($time_between_backups <= 0) {
            $time_between_backups = NtbrCore::MIN_TIME_NEW_BACKUP;
        }

        if ($current_time - $ntbr_ongoing >= $time_between_backups) {
            GlobConfNtbr::set('NTBR_ONGOING', time());

            $result = $this->backup($config->id, false, false, NtbrCore::STEP_SEND_AWAY, $backup_name, $backup_list);

            if ($result) {
                $update = $this->updateBackupList();

                return ['backuplist' => $update, 'warnings' => $this->warnings];
            }
        } else {
            $time_to_wait = $time_between_backups - ($current_time - $ntbr_ongoing);
            $this->log(
                'ERR' . sprintf(
                    $this->l('For security reason, some time is needed between two backups. Please wait %d seconds', self::PAGE),
                    $time_to_wait
                )
            );
        }

        return false;
    }

    public function restoreBackup($backup_name, $type_backup, $encryption_key = '')
    {
        if (!$backup_name || $backup_name == '' || !$type_backup || $type_backup == '') {
            return 0;
        }

        $options_restore = $this->startLocalRestore($backup_name, $type_backup, $encryption_key);

        if ($options_restore === false) {
            return 0;
        }

        return $options_restore;
    }

    public function generateSecureUrls($id_shop_group, $id_shop)
    {
        return $this->generateUrls(false, $id_shop_group, $id_shop);
    }

    public function getTmpDistFileContent()
    {
        if ($this->config->create_on_distant) {
            if (Apparatus::checkFileExists($this->tmp_dist_file)) {
                if (!($handle_tmp_dist_file = fopen($this->tmp_dist_file, 'a+'))) {
                    $this->log('ERR' . $this->l('The temporary distant file cannot be opened', self::PAGE));
                    $this->endWithError();
                }

                try {
                    // Make sur the file has the correct right
                    if (chmod($this->tmp_dist_file, octdec(self::PERM_FILE)) !== true) {
                        $this->log(
                            sprintf(
                                $this->l('The file "%1$s" permission cannot be updated to %2$d', self::PAGE),
                                $this->tmp_dist_file,
                                self::PERM_FILE
                            )
                        );
                    }
                } catch (Throwable $t) {
                    // Executed only in PHP 7, will not match in PHP 5
                    $this->log(
                        sprintf(
                            $this->l('The file "%1$s" permission cannot be updated to %2$d', self::PAGE),
                            $this->tmp_dist_file,
                            self::PERM_FILE
                        )
                    );

                    $this->log($t->getMessage(), true);
                } catch (Exception $e) {
                    // Executed only in PHP 5, will not be reached in PHP 7
                    $this->log(
                        sprintf(
                            $this->l('The file "%1$s" permission cannot be updated to %2$d', self::PAGE),
                            $this->tmp_dist_file,
                            self::PERM_FILE
                        )
                    );

                    $this->log($e->getMessage(), true);
                }

                if (!rewind($handle_tmp_dist_file)) {
                    $this->log('ERR' . $this->l('The temporary distant file cannot be rewind', self::PAGE));
                    $this->endWithError();
                }

                $this->distant_tar_content = Apparatus::hex2bin(fgets($handle_tmp_dist_file));
                // $this->distant_tar_content = fgets($handle_tmp_dist_file);

                fclose($handle_tmp_dist_file);
            } else {
                $this->distant_tar_content = '';
            }
        }

        return true;
    }

    public function writeTmpDistFile()
    {
        if ($this->config->create_on_distant) {
            if (!isset($this->distant_tar_content) || !$this->distant_tar_content) {
                $this->distant_tar_content = '';
            }

            if (!($handle_tmp_dist_file = fopen($this->tmp_dist_file, 'w+'))) {
                $this->log('ERR' . $this->l('The temporary distant file cannot be opened', self::PAGE));
                $this->endWithError();
            }

            $content = '';

            if ($this->distant_tar_content) {
                $content = bin2hex($this->distant_tar_content);
            }

            // $content = $this->distant_tar_content;

            if (fwrite($handle_tmp_dist_file, $content) === false) {
                $this->log('ERR' . $this->l('The temporary distant file cannot be written', self::PAGE));
                $this->log($content, true);
                $this->endWithError();
            }

            fclose($handle_tmp_dist_file);
        }
    }

    public function createBackupSodiumKey()
    {
        return sodium_bin2base64(sodium_crypto_secretstream_xchacha20poly1305_keygen(), SODIUM_BASE64_VARIANT_ORIGINAL);
    }

    public function cryptBackup($chunk_to_crypt)
    {
        if (!$this->backup_sodium_init_state) {
            $this->log('ERR' . $this->l('The cryptage cannot be done', self::PAGE));
            $this->endWithError();
        }

        // Need to be crypted by chunk of 512o, to be decrypted latter
        $list_chunks = str_split($chunk_to_crypt, self::TAR_BLOCK_SIZE);
        $text_crypted = '';

        foreach ($list_chunks as $chunk) {
            $text_crypted .= sodium_crypto_secretstream_xchacha20poly1305_push($this->backup_sodium_init_state, $chunk);
        }

        // Overwrite security variables
        sodium_memzero($chunk_to_crypt);

        return $text_crypted;
    }

    public function cryptCompressedBackup()
    {
        // Compressed backup need to be crypted after being compressed, not before, since crypted file cannot be compressed

        if ($this->next_step == self::STEP_COMPRESS_CRYPT && $this->num_file_to_crypt == 1) {
            if (Apparatus::checkFileExists($this->compressed_crypted_file)) {
                $this->fileDelete($this->compressed_crypted_file);
            }

            // Remove all files from the list
            $this->part_list = [];

            $this->backup_sodium_key = sodium_base642bin($this->decrypt($this->config->sodium_key), SODIUM_BASE64_VARIANT_ORIGINAL);
        }

        if ($this->num_file_to_crypt <= $this->part_number) {
            if ($this->next_step == self::STEP_COMPRESS_CRYPT) {
                if ($this->part_number == 1) {
                    $this->uncompressed_file = $this->part_file . '.' . $this->ext_uncompress;
                    $this->compressed_file = $this->part_file . '.' . $this->ext_compress;
                    $this->compressed_crypted_file = $this->compressed_file . '.' . self::EXT_CRYPT;
                } else {
                    $this->uncompressed_file = $this->part_file . '.' . $this->num_file_to_crypt . '.part.' . $this->ext_uncompress;
                    $this->compressed_file = $this->part_file . '.' . $this->num_file_to_crypt . '.part.' . $this->ext_compress;
                    $this->compressed_crypted_file = $this->compressed_file . '.' . self::EXT_CRYPT;
                }

                // Open gz file
                if (($this->handle_gz_file = fopen($this->compressed_file, 'rb')) === false) {
                    $this->log(
                        'ERR' . $this->l('The gz file cannot be opened', self::PAGE) . ' (' . $this->compressed_file . ')'
                    );
                    $this->endWithError();
                }

                // Open crypted file
                if (($this->handle_crypted_file = fopen($this->compressed_crypted_file, 'wb')) === false) {
                    $this->log('ERR' . $this->l('The crypted compress file cannot be opened', self::PAGE) . ' (' . $this->compressed_crypted_file . ')');
                    $this->endWithError();
                }

                $this->compress_crypted_position = 0;
                $this->old_percent = 0;
                $this->compress_crypted_size_done = 0;

                if ($this->next_step == self::STEP_COMPRESS_CRYPT) {
                    // Write crypted header
                    list($this->backup_sodium_init_state, $this->backup_sodium_init_header) = sodium_crypto_secretstream_xchacha20poly1305_init_push($this->backup_sodium_key);
                    fwrite($this->handle_crypted_file, $this->backup_sodium_init_header);
                }

                $this->next_step = self::STEP_COMPRESS_CRYPT_CONTINUE;
            }
        }

        if ($this->next_step == self::STEP_COMPRESS_CRYPT_CONTINUE) {
            if ($this->num_file_to_crypt > 1) {
                $new_compressed_file = $this->part_file . '.' . $this->num_file_to_crypt . '.part.' . $this->ext_compress;
                $new_compressed_crypted_file = $new_compressed_file . '.' . self::EXT_CRYPT;

                if ($this->compressed_file != $new_compressed_file) {
                    if (is_resource($this->handle_gz_file)) {
                        gzclose($this->handle_gz_file);
                    }

                    if (is_resource($this->handle_crypted_file)) {
                        fclose($this->handle_crypted_file);
                    }

                    $this->compressed_file = $new_compressed_file;
                    $this->compressed_crypted_file = $new_compressed_crypted_file;

                    // Open crypted file
                    if (($this->handle_crypted_file = fopen($this->compressed_crypted_file, 'wb')) === false) {
                        $this->log('ERR' . $this->l('The crypted compress file cannot be opened', self::PAGE) . ' (' . $this->compressed_crypted_file . ')');
                        $this->endWithError();
                    }

                    // Open gz file
                    if (($this->handle_gz_file = fopen($this->compressed_file, 'rb')) === false) {
                        $this->log(
                            'ERR' . $this->l('The gz file cannot be opened', self::PAGE) . ' (' . $this->compressed_file . ')'
                        );
                        $this->endWithError();
                    }

                    $this->compress_crypted_position = 0;
                    $this->old_percent = 0;
                    $this->compress_crypted_size_done = 0;

                    // Write crypted header
                    list($this->backup_sodium_init_state, $this->backup_sodium_init_header) = sodium_crypto_secretstream_xchacha20poly1305_init_push($this->backup_sodium_key);
                    fwrite($this->handle_crypted_file, $this->backup_sodium_init_header);
                }
            }

            if ($this->compress_crypted_position > (PHP_INT_MAX - 1)) {
                $refresh = !$this->config->disable_refresh;
                $time_refresh = $this->config->time_between_refresh;
                $part_size = $this->config->part_size * 1024 * 1024;

                // if the refresh is activated and its <= to the default value
                // and there is no multipart small enough
                if ($refresh && $time_refresh <= self::MAX_TIME_BEFORE_REFRESH
                    && ($part_size <= 0 || $part_size >= (PHP_INT_MAX - 1))
                ) {
                    GlobConfNtbr::set('NTBR_BIG_WEBSITE', 1);
                }
            }

            $this->handle_gz_file = $this->goToPositionInFile(
                $this->handle_gz_file,
                $this->compress_crypted_position
            );

            if ($this->handle_gz_file === false) {
                $this->endWithError();
            }

            $this->content_for_crypted_gz = '';
            $this->size_content_for_crypted_gz = 0;

            // Crypt gz file

            while (!feof($this->handle_gz_file)) {
                $read = fread($this->handle_gz_file, self::TAR_BLOCK_SIZE);

                if ($read === false) {
                    $this->log('ERR' . $this->l('The gz file is no longer readable.', self::PAGE));
                    $this->endWithError();
                }

                $this->content_for_crypted_gz .= sodium_crypto_secretstream_xchacha20poly1305_push($this->backup_sodium_init_state, $read);
                $this->size_content_for_crypted_gz += self::TAR_BLOCK_SIZE;

                // If the content size is big enough, write it
                if ($this->size_content_for_crypted_gz >= self::MAX_FILE_COMPRESS_W_SIZE) {
                    // Write data
                    fwrite($this->handle_crypted_file, $this->content_for_crypted_gz);
                    $this->content_for_crypted_gz = '';
                    $this->size_content_for_crypted_gz = 0;
                }

                $this->compress_crypted_size_done += self::TAR_BLOCK_SIZE;

                // Compute percentage progression
                if ($this->compress_files_size[$this->num_file_to_crypt] > 0) {
                    $percent = ($this->compress_crypted_size_done * 100) / $this->compress_files_size[$this->num_file_to_crypt];
                } else {
                    $percent = 0;
                }

                if ($percent > 100) {
                    $percent = 100;
                }

                if ($percent >= $this->old_percent + 1) {
                    $this->old_percent = round($percent, 0);
                    if ($this->part_number == 1) {
                        $this->log($this->l('Crypting files:', self::PAGE) . ' ' . round($percent, 0) . '%');
                    } else {
                        $this->log(
                            $this->l('Crypting files:', self::PAGE)
                            . ' ' . $this->num_file_to_crypt . '/' . $this->part_number . $this->l(':', self::PAGE)
                            . ' ' . round($percent, 0) . '%'
                        );
                    }
                }

                // Get where we are in the file
                $this->compress_crypted_position = $this->compress_crypted_size_done;

                // refresh
                $this->refreshBackup(true);
            }

            // Write what is left
            fwrite($this->handle_crypted_file, $this->content_for_crypted_gz);
            $this->content_for_crypted_gz = '';
            $this->size_content_for_crypted_gz = 0;

            // Close gz file
            if (!fclose($this->handle_gz_file)) {
                $this->log('WAR' . $this->l('The gz file was not closed', self::PAGE));

                return false;
            }

            // Close crypted file
            if (!fclose($this->handle_crypted_file)) {
                $this->log('WAR' . $this->l('The crypted file was not closed', self::PAGE));

                return false;
            }

            // Log file size
            $this->log(sprintf($this->l('"%1$s" size', self::PAGE), basename($this->compressed_crypted_file)) . ' - ' . $this->readableSize($this->getFileSize($this->compressed_crypted_file)), true);

            $this->part_list[] = $this->compressed_crypted_file;
            $this->total_size += $this->compress_files_size[$this->num_file_to_crypt];
            $this->fileDelete($this->compressed_file);
            $this->log($this->l('Delete file', self::PAGE) . ' ' . $this->compressed_file);
            ++$this->num_file_to_crypt;

            // refresh
            $this->refreshBackup();

            // There is still some files to compress
            if ($this->num_file_to_crypt <= $this->part_number) {
                return $this->cryptCompressedBackup();
            }
        }

        // Override sodium security variables
        if ($this->backup_sodium_init_state) {
            sodium_memzero($this->backup_sodium_init_state);
        }

        if ($this->backup_sodium_init_header) {
            sodium_memzero($this->backup_sodium_init_header);
        }

        if ($this->backup_sodium_key) {
            sodium_memzero($this->backup_sodium_key);
        }

        return true;
    }
}
