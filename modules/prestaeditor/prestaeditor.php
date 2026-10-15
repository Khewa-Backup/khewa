<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestaEditor extends Module
{
    public function __construct()
    {
        $this->name = 'prestaeditor';
        $this->tab = 'administration';
        $this->version = '1.0.5';
        $this->author = 'Codex';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Presta File Editor');
        $this->description = $this->l('Console-unlocked back office file editor with chunked encrypted saves.');
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() && $this->ensureAdminTab();
    }

    public function uninstall()
    {
        return $this->uninstallTab() && parent::uninstall();
    }

    public function getContent()
    {
        $this->ensureAdminTab();
        $link = $this->context->link->getAdminLink('AdminPrestaEditor');

        return '<div class="panel"><h3>' . $this->displayName . '</h3><p>'
            . $this->l('Open the editor from Advanced Parameters, or use the button below.')
            . '</p><a class="btn btn-default" href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">'
            . $this->l('Open file editor') . '</a></div>';
    }

    public function ensureAdminTab()
    {
        return $this->installTab();
    }

    private function installTab()
    {
        $idTab = (int) Tab::getIdFromClassName('AdminPrestaEditor');
        $tab = $idTab ? new Tab($idTab) : new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminPrestaEditor';
        $tab->module = $this->name;
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminAdvancedParameters');

        if (!$tab->id_parent) {
            $tab->id_parent = 0;
        }

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int) $lang['id_lang']] = 'Presta File Editor';
        }

        $saved = $idTab ? (bool) $tab->update() : (bool) $tab->add();

        if ($saved) {
            $this->grantTabAccess((int) $tab->id);
        }

        return $saved;
    }

    private function grantTabAccess($idTab)
    {
        if (!$idTab) {
            return false;
        }

        $profiles = Db::getInstance()->executeS('SELECT `id_profile` FROM `' . _DB_PREFIX_ . 'profile`');
        if (!is_array($profiles)) {
            return false;
        }

        if ($this->usesAuthorizationRoles()) {
            return $this->grantAuthorizationRoleAccess($profiles, $idTab);
        }

        foreach ($profiles as $profile) {
            $idProfile = (int) $profile['id_profile'];
            if (!$idProfile) {
                continue;
            }

            $sql = 'REPLACE INTO `' . _DB_PREFIX_ . 'access` '
                . '(`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) VALUES '
                . '(' . $idProfile . ', ' . (int) $idTab . ', 1, 1, 1, 1)';

            if (!Db::getInstance()->execute($sql)) {
                return false;
            }
        }

        return true;
    }

    private function usesAuthorizationRoles()
    {
        $column = Db::getInstance()->executeS(
            'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'access` LIKE \'id_authorization_role\''
        );

        return is_array($column) && count($column) > 0;
    }

    private function grantAuthorizationRoleAccess($profiles, $idTab)
    {
        if (method_exists('Tab', 'initAccess')) {
            Tab::initAccess((int) $idTab);
        }

        $roleIds = $this->getAuthorizationRoleIds();
        if (!$roleIds) {
            return false;
        }

        foreach ($profiles as $profile) {
            $idProfile = (int) $profile['id_profile'];
            if (!$idProfile) {
                continue;
            }

            foreach ($roleIds as $idRole) {
                $exists = (int) Db::getInstance()->getValue(
                    'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'access` '
                    . 'WHERE `id_profile` = ' . $idProfile . ' AND `id_authorization_role` = ' . (int) $idRole
                );

                if (!$exists) {
                    Db::getInstance()->execute(
                        'INSERT INTO `' . _DB_PREFIX_ . 'access` (`id_profile`, `id_authorization_role`) VALUES '
                        . '(' . $idProfile . ', ' . (int) $idRole . ')'
                    );
                }
            }
        }

        return true;
    }

    private function getAuthorizationRoleIds()
    {
        $roleIds = array();
        $slugs = array(
            'ROLE_MOD_TAB_ADMINPRESTAEDITOR_READ',
            'ROLE_MOD_TAB_ADMINPRESTAEDITOR_CREATE',
            'ROLE_MOD_TAB_ADMINPRESTAEDITOR_UPDATE',
            'ROLE_MOD_TAB_ADMINPRESTAEDITOR_DELETE',
        );

        foreach ($slugs as $slug) {
            $escapedSlug = pSQL($slug);
            $idRole = (int) Db::getInstance()->getValue(
                'SELECT `id_authorization_role` FROM `' . _DB_PREFIX_ . 'authorization_role` '
                . 'WHERE `slug` = "' . $escapedSlug . '"'
            );

            if (!$idRole) {
                Db::getInstance()->execute(
                    'INSERT INTO `' . _DB_PREFIX_ . 'authorization_role` (`slug`) VALUES ("' . $escapedSlug . '")'
                );
                $idRole = (int) Db::getInstance()->getValue(
                    'SELECT `id_authorization_role` FROM `' . _DB_PREFIX_ . 'authorization_role` '
                    . 'WHERE `slug` = "' . $escapedSlug . '"'
                );
            }

            if ($idRole) {
                $roleIds[] = $idRole;
            }
        }

        return $roleIds;
    }

    private function uninstallTab()
    {
        $idTab = (int) Tab::getIdFromClassName('AdminPrestaEditor');

        if (!$idTab) {
            return true;
        }

        $tab = new Tab($idTab);

        return (bool) $tab->delete();
    }
}
