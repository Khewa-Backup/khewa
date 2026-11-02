<?php
/**
 * Khewa PS 9.2: new file.
 *
 * Help desk 5.0.0 to 5.0.2. The vendor says only files change. 5.0.2 no longer uses the displayHeader
 * and displayBackOfficeHeader hooks, so they are taken off. A broken hook name with a missing first
 * letter ("ctionObjectWkHdTicketMsgAddAfter") left by an old install is taken off too.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_0_2($module)
{
    $module->unregisterHook('displayHeader');
    $module->unregisterHook('displayBackOfficeHeader');
    $idBadHook = (int) Hook::getIdByName('ctionObjectWkHdTicketMsgAddAfter', false);
    if ($idBadHook) {
        Db::getInstance()->delete('hook_module', 'id_hook = ' . $idBadHook . ' AND id_module = ' . (int) $module->id);
    }
    $module->registerHook('registerGDPRConsent');

    return true;
}
