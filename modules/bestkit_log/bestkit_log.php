<?php
/**
 * Bestkit Admin Log module.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'bestkit_log/includer.php';

class bestkit_log extends Module
{
    const PREFIX = 'bestkit_log_';
    const COOKIE_LOG_VISIT_PARAM_NAME = 'bestkit_log_visit';

    protected $_hooks = array(
        'actionObjectAddAfter',
        'actionObjectUpdateBefore',
        'actionObjectUpdateAfter',
        'actionObjectDeleteBefore',
        'actionObjectDeleteAfter',
        'actionAdminControllerSetMedia',
    );

    protected $_tabs = array(
        array(
            'class_name' => 'AdminBestkitLogs',
            'parent' => null,
            'name' => 'Bestkit Admin log'
        ),
        array(
            'class_name' => 'AdminBestkitLog',
            'parent' => 'AdminBestkitLogs',
            'name' => 'Actions Log '
        ),
        array(
            'class_name' => 'AdminBestkitLogLoginAttempts',
            'parent' => 'AdminBestkitLogs',
            'name' => 'Login Attempts '
        ),
        array(
            'class_name' => 'AdminBestkitLogVisitHistory',
            'parent' => 'AdminBestkitLogs',
            'name' => 'Visit History '
        ),
    );

    public function __construct()
    {
        $this->name = 'bestkit_log';
        $this->tab = 'administration';
        $this->version = '2.0.1';
        $this->author = 'best-kit';
        $this->need_instance = 0;
        $this->module_key = '8a8e23d4093aa682ad4dd8dac1878c8b';
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array(
            'min' => '9.0.0',
            'max' => '9.99.99',
        );

        parent::__construct();

        $this->displayName = $this->l('Admin/Employee actions log');
        $this->description = $this->l('Log back-office actions, employee login attempts and visited administration pages.');
    }

    public function getDir($file = '')
    {
        return _PS_MODULE_DIR_ . $this->name . DIRECTORY_SEPARATOR . $file;
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $queries = include $this->getDir('sql/install.php');
        foreach ($queries as $query) {
            if (!Db::getInstance()->execute($query)) {
                $this->uninstall();

                return false;
            }
        }

        foreach ($this->_hooks as $hook) {
            if (!$this->registerHook($hook)) {
                $this->uninstall();

                return false;
            }
        }

        $languages = Language::getLanguages(false);
        foreach ($this->_tabs as $tabData) {
            $tab = new Tab();
            $tab->class_name = $tabData['class_name'];
            $tab->id_parent = $tabData['parent'] === null
                ? 0
                : (int) Tab::getIdFromClassName($tabData['parent']);
            $tab->module = $this->name;

            foreach ($languages as $language) {
                $tab->name[(int) $language['id_lang']] = $this->l(trim($tabData['name']));
            }

            if (!$tab->add()) {
                $this->uninstall();

                return false;
            }
        }

        return true;
    }

    public function uninstall()
    {
        $result = true;
        $queries = include $this->getDir('sql/uninstall.php');
        foreach ($queries as $query) {
            $result = Db::getInstance()->execute($query) && $result;
        }

        foreach ($this->_tabs as $tabData) {
            $tabId = (int) Tab::getIdFromClassName($tabData['class_name']);
            if ($tabId > 0) {
                $tab = new Tab($tabId);
                $result = $tab->delete() && $result;
            }
        }

        return parent::uninstall() && $result;
    }

    public function _renderHelp() {
        $this->context->smarty->assign(array(
            'bestkit_logs' => array(
                'links' => array(
                    array(
                        'title' => $this->l('Actions Log'),
                        'href' => $this->context->link->getAdminLink('AdminBestkitLog'),
                    ),
                    array(
                        'title' => $this->l('Login Attempts'),
                        'href' => $this->context->link->getAdminLink('AdminBestkitLogLoginAttempts'),
                    ),
                    array(
                        'title' => $this->l('Visit History'),
                        'href' => $this->context->link->getAdminLink('AdminBestkitLogVisitHistory'),
                    ),
                )
            )
        ));

        return $this->display(__FILE__, 'help.tpl');
    }

    public function getContent()
    {
        return $this->_renderHelp();
    }

    public function getLogExceptions()
    {
        return BestkitLog::getLogExceptions();
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (!Validate::isLoadedObject($this->context->employee)) {
            return;
        }

        $visitId = $this->context->cookie->__isset(self::COOKIE_LOG_VISIT_PARAM_NAME)
            ? (int) $this->context->cookie->__get(self::COOKIE_LOG_VISIT_PARAM_NAME)
            : 0;
        if (!$visitId || !BestkitLogVisit::belongsToEmployee($visitId, (int) $this->context->employee->id)) {
            $visitId = BestkitLogVisit::registerNewVisit($this->context);
            $this->context->cookie->__set(self::COOKIE_LOG_VISIT_PARAM_NAME, $visitId);
        }

        if ($visitId > 0) {
            BestkitLogVisit::recordVisitDetails($visitId);
        }
    }

    /**
     * Only back-office actions are logged (BestkitLog::saveLog() requires an employee),
     * so front-office object changes (carts, customers, connections...) are skipped early
     */
    protected function isEmployeeContext()
    {
        // Khewa PS 9.2: never log while one of our own hooks is already running. Loading the "before" copy of an
        // object (new $class($id)) can save other objects, which ran our hooks again and again until PHP ran out
        // of memory (seen on the POS till when adding to the cart).
        if (self::$khewaBusy) {
            return false;
        }

        return isset($this->context->employee) && Validate::isLoadedObject($this->context->employee);
    }

    /** Khewa PS 9.2: true while a logging hook is running (stops the hooks calling themselves). */
    protected static $khewaBusy = false;

    public function hookActionObjectDeleteBefore($params)
    {
        if (!$this->isEmployeeContext()) {
            return;
        }
        self::$khewaBusy = true; // Khewa PS 9.2: see isEmployeeContext()
        try {
            $this->khewaActionObjectDeleteBefore($params);
        } finally {
            self::$khewaBusy = false;
        }
    }

    protected function khewaActionObjectDeleteBefore($params)
    {
        if (!isset($params['object']) || !is_object($params['object'])) {
            return;
        }

        BestkitLog::putToRegistry(
            BestkitLog::getRegistryName($params['object']),
            $params['object']
        );
    }

    public function hookActionObjectAddAfter($params)
    {
        if (!$this->isEmployeeContext()) {
            return;
        }
        self::$khewaBusy = true; // Khewa PS 9.2: see isEmployeeContext()
        try {
            $this->khewaActionObjectAddAfter($params);
        } finally {
            self::$khewaBusy = false;
        }
    }

    protected function khewaActionObjectAddAfter($params)
    {
        if (!isset($params['object'])) {
            return;
        }

        BestkitLog::saveLog(
            $params['object'],
            null,
            'add',
            $this->context,
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? 'ajax action' : ''
        );
    }

    public function hookActionObjectUpdateBefore($params)
    {
        if (!$this->isEmployeeContext()) {
            return;
        }
        self::$khewaBusy = true; // Khewa PS 9.2: see isEmployeeContext()
        try {
            $this->khewaActionObjectUpdateBefore($params);
        } finally {
            self::$khewaBusy = false;
        }
    }

    protected function khewaActionObjectUpdateBefore($params)
    {
        if (!isset($params['object']) || !is_object($params['object'])) {
            return;
        }

        if (in_array(get_class($params['object']), $this->getLogExceptions(), true)) {
            return;
        }

        $class = get_class($params['object']);
        try {
            $original = new $class($params['object']->id);
        } catch (Throwable $exception) {
            return;
        }

        BestkitLog::putToRegistry(
            BestkitLog::getRegistryName($params['object']),
            $original
        );
    }

    public function hookActionObjectUpdateAfter($params)
    {
        if (!$this->isEmployeeContext()) {
            return;
        }
        self::$khewaBusy = true; // Khewa PS 9.2: see isEmployeeContext()
        try {
            $this->khewaActionObjectUpdateAfter($params);
        } finally {
            self::$khewaBusy = false;
        }
    }

    protected function khewaActionObjectUpdateAfter($params)
    {
        if (!isset($params['object'])) {
            return;
        }

        BestkitLog::saveLog(
            $params['object'],
            BestkitLog::getFromRegistry(BestkitLog::getRegistryName($params['object'])),
            'update',
            $this->context,
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? 'ajax action' : ''
        );
    }

    public function hookActionObjectDeleteAfter($params)
    {
        if (!$this->isEmployeeContext()) {
            return;
        }
        self::$khewaBusy = true; // Khewa PS 9.2: see isEmployeeContext()
        try {
            $this->khewaActionObjectDeleteAfter($params);
        } finally {
            self::$khewaBusy = false;
        }
    }

    protected function khewaActionObjectDeleteAfter($params)
    {
        if (!isset($params['object'])) {
            return;
        }

        BestkitLog::saveLog(
            $params['object'],
            BestkitLog::getFromRegistry(BestkitLog::getRegistryName($params['object'])),
            'delete',
            $this->context,
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? 'ajax action' : ''
        );
    }

}
