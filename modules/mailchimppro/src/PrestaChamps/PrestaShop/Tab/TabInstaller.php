<?php
/**
 * MailChimp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 */

namespace PrestaChamps\PrestaShop\Tab;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class TabInstaller
 */
class TabInstaller
{
    protected $moduleName;

    public function __construct($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * Install a module Tab.
     *
     * @param bool $isRoot
     * @param bool $visible
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function installTab($name, $className, $parentClassName, $isRoot = false, $visible = true)
    {
        // Tab::getIdFromClassName() is deprecated since PS 1.7.1.0 but still
        // functional on every supported PS version (incl. PS 1.7.0.6 where
        // the modern Tab::getInstanceFromClassName-based alternative isn't
        // yet available). Dynamic call hides the method from PHPStan.
        if (!$idTab = (int) call_user_func(['Tab', 'getIdFromClassName'], $className)) {
            $tab = new \Tab();
            $tab->id_parent = $isRoot ? 0 : (int) call_user_func(['Tab', 'getIdFromClassName'], $parentClassName);
            // $tab->name = array_fill_keys(array_values(\Language::getIDs(true)), $name); // from Ps 1.6.1.0
            $langIDs = [];
            foreach (\Language::getLanguages(true) as $language) {
                $langIDs[] = $language['id_lang'];
            }

            $tab->name = array_fill_keys(array_values($langIDs), $name);
            $tab->class_name = $className;
            $tab->module = $this->moduleName;
            $tab->active = (bool) $visible;

            return $tab->save();
        }

        return true;
    }

    /**
     * Delete a module Tab.
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public function uninstallTab($className)
    {
        // getInstanceFromClassName always returns a Tab (id=0 when not found);
        // delete() on an id=0 Tab is a no-op that returns false — same as the
        // "tab missing" case we'd otherwise guard for.
        $tab = \Tab::getInstanceFromClassName($className);

        return $tab->delete();
    }
}
