<?php
/**
 * Khewa PS 9.2: new file.
 *
 * PrestaShop 9 removed the l() translation method from admin controllers, so every trash page crashed
 * ("undefined method l"). This puts it back for the module's own admin pages. It reads the same
 * translation keys as before (translations/xx.php, "adminwk...controller_" keys).
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

trait WkTrashAdminTranslationTrait
{
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        $translated = $this->module->l($string, Tools::strtolower(get_class($this)));
        if ($addslashes) {
            $translated = addslashes($translated);
        }

        return $translated;
    }

    /**
     * Khewa PS 9.2: the "Successful restore." message was added at the end of the core message list and shown
     * with &conf=<number>. On 9.2 the page shows only its own fixed list of conf numbers (that number now means
     * "Successfully signed in to PrestaShop Addons"). So the redirect uses our own number and the message is
     * added to the page's confirmations here.
     */
    public function initContent()
    {
        $this->_conf[self::WKTRASH_RESTORE_CONF] = $this->l('Successful restore.');
        if ((int) Tools::getValue('conf') === self::WKTRASH_RESTORE_CONF) {
            $this->confirmations[] = $this->_conf[self::WKTRASH_RESTORE_CONF];
        }
        parent::initContent();
    }
}
