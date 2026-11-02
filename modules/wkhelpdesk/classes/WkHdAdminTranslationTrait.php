<?php
/**
 * Khewa PS 9.2: new file.
 *
 * PrestaShop 9 removed the l() translation method from admin controllers, so every help desk
 * admin page crashed ("undefined method l"). This puts it back for the module's own admin pages.
 * It reads the same translation keys as before (translations/xx.php, "<controller class>_" keys).
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

trait WkHdAdminTranslationTrait
{
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        $translated = $this->module->l($string, Tools::strtolower(get_class($this)));
        if ($addslashes) {
            $translated = addslashes($translated);
        }

        return $translated;
    }
}
