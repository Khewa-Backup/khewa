<?php
/**
 * Spam Protection - Google reCAPTCHA, hCaptcha & GDPR Captcha
 *
 * @author    WebshopWorks
 * @copyright 2018-2026 WebshopWorks.com
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class InvReCaptcha extends Module
{
    protected $defaults = [
        'provider' => 'grecaptcha',
        'version' => 3,
        'sitekey' => '',
        'secretkey' => '',
        'score' => 0.5,
        'theme' => '',
        'pos' => '',
        'offset' => 14,
        'forms' => [],
        'log' => false,
        'check_disposable' => false,
        'complexity' => 2,
        'expiry' => 300,
    ];
    protected $config;
    protected $isActive;
    protected $forms;
    protected $_html = '';

    public function __construct()
    {
        $this->name = 'invrecaptcha';
        $this->tab = 'administration';
        $this->version = '2.0.1';
        $this->author = 'WebshopWorks';
        $this->module_key = '589d8377a3df8b4f597826ea5430d9da';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        $this->controllers = ['challenge'];
        $this->displayName = $this->l('Spam Protection - Google reCAPTCHA, hCaptcha and GDPR Captcha');
        $this->description = $this->l('Protect your site against spam and abuse, while letting your real customers pass through with ease.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        parent::__construct();

        $json = Configuration::get('irc_config');
        $this->config = $json ? array_merge($this->defaults, json_decode($json, true)) : $this->defaults;
        $this->isActive = ($this->config['sitekey'] || 'altcha' === $this->config['provider']) && $this->config['secretkey'] && $this->config['forms'];
        // BC fix for PS < 1.7.3
        $this->context->controller instanceof FrontController
            && version_compare(_PS_VERSION_, '1.7.3', '<')
            && $this->hookActionFrontControllerInitAfter();
    }

    public function install()
    {
        Shop::isFeatureActive() && Shop::setContext(Shop::CONTEXT_ALL);

        Configuration::updateValue('irc_config', json_encode($this->defaults));

        return array_reduce([
            'actionFrontControllerAfterInit',
            'actionFrontControllerInitAfter',
            'actionFrontControllerSetMedia',
        ], function ($carry, $hook) {
            return $carry && $this->registerHook($hook);
        }, parent::install()) && $this->installDb();
    }

    public function uninstall()
    {
        Configuration::deleteByName('irc_config');
        Configuration::deleteByName('irc_blacklist_ips');
        Configuration::deleteByName('irc_blacklist_emails');

        return parent::uninstall() && $this->uninstallDb();
    }

    public function installDb()
    {
        return Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'invrecaptcha_stats` (
                `id_stat` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_shop` int(10) unsigned NOT NULL,
                `form` varchar(64) NOT NULL,
                `provider` varchar(32) NOT NULL,
                `result` tinyint(1) unsigned NOT NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id_stat`),
                KEY `id_shop` (`id_shop`),
                KEY `form` (`form`),
                KEY `date_add` (`date_add`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'
        );
    }

    protected function uninstallDb()
    {
        return Db::getInstance()->execute(
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'invrecaptcha_stats`'
        );
    }

    public function registerHook($hook_name, $shop_list = null)
    {
        if ($res = parent::registerHook($hook_name, $shop_list)) {
            $this->updatePosition((int) Hook::getIdByName($hook_name), false, 1);
        }

        return $res;
    }

    public function __call($method, $args)
    {
        // BC fix for PS 1.7.3 - 1.7.6
        if (!strcasecmp($method, 'hookActionFrontControllerAfterInit')) {
            return $this->hookActionFrontControllerInitAfter(...$args);
        }
        // BC fix for PS 1.6
        if (!strcasecmp($method, 'hookActionBeforeSubmitAccount')) {
            return $this->hookActionSubmitAccountBefore(...$args);
        }
        // Ignore other hooks
        if (stripos($method, 'hook') !== 0) {
            throw new BadMethodCallException('Call to undefined method ' . __CLASS__ . "::$method()");
        }
    }

    public function hookActionFrontControllerInitAfter($params = [])
    {
        if (!$this->isActive || $this->context->controller instanceof InvReCaptchaChallengeModuleFrontController) {
            return;
        }
        $ps16 = version_compare(_PS_VERSION_, '1.7', '<');
        $submits = [
            'contact' => ['key' => 'submitMessage', 'email' => 'from'],
            'newsletter' => Tools::getIsset('submitNewsletter')
                ? ['key' => 'submitNewsletter'] // BC for PS 1.6 & Creative Popup/Elements
                : ['mod' => 'ps_emailsubscription', 'key' => 'controller', 'val' => 'subscription'],
            'login' => ['key' => $ps16 ? 'SubmitLogin' : 'submitLogin'],
            'register' => ['key' => $ps16 ? (Tools::getValue('is_new_customer') ? 'submitAccount' : 'submitGuestAccount') : 'submitCreate'],
            'resetpass' => ['ctrl' => 'password', 'key' => 'email'],
            'mailalert' => ['mod' => 'ps_emailalerts', 'key' => 'process', 'val' => 'add', 'email' => 'customer_email'],
            'review' => $ps16
                ? ['mod' => 'productcomments', 'key' => 'action', 'val' => 'add_comment']
                : ['mod' => 'productcomments', 'key' => 'controller', 'val' => 'PostComment'],
            'jmsBlogComment' => ['mod' => 'jmsblog', 'key' => 'action', 'val' => 'submitComment'],
            'ybcBlogComment' => ['mod' => 'ybc_blog', 'key' => 'bcsubmit'],
        ];
        foreach ($this->config['forms'] as $form) {
            $submit = $submits[$form];

            if (Tools::isSubmit($submit['key'])
                && (empty($submit['val']) || Tools::getValue($submit['key']) === $submit['val'])
                && (empty($submit['ctrl']) || Tools::getValue('controller') === $submit['ctrl'])
                && (empty($submit['mod']) || Tools::getValue('module') === $submit['mod'])
            ) {
                $email = Tools::getValue(isset($submit['email']) ? $submit['email'] : 'email');
                // Check Disposable Email Domains first (if enabled)
                if ($email && $this->config['check_disposable'] && $this->isEmailDisposable($email)) {
                    return $this->handleCaptchaResponse([
                        'success' => false,
                        'error-codes' => ['disposable-email-detected'],
                    ], $form);
                }
                $blacklistEmails = Configuration::get('irc_blacklist_emails');
                // Check Email Blacklist
                if ($email && $blacklistEmails && $this->isEmailBlacklisted($email, $blacklistEmails)) {
                    return $this->handleCaptchaResponse([
                        'success' => false,
                        'error-codes' => ['email-blacklisted'],
                    ], $form);
                }
                $ip = Tools::getRemoteAddr();
                $blacklistIps = Configuration::get('irc_blacklist_ips');
                // Check IP Blacklist
                if ($ip && $blacklistIps && $this->isIpBlacklisted($ip, $blacklistIps)) {
                    return $this->handleCaptchaResponse([
                        'success' => false,
                        'error-codes' => ['ip-blacklisted'],
                    ], $form);
                }
                // Proceed with normal captcha verification if not blacklisted
                return $this->handleCaptchaResponse($this->verifyCaptcha(), $form);
            }
        }
    }

    /**
     * Check if an IP address matches any pattern in the blacklist.
     * Supports exact IPs, wildcard patterns (*), and CIDR notation.
     *
     * @param string $ip The user's IP address
     * @param string $blacklistString The newline-separated string of blacklist patterns
     *
     * @return bool True if the IP is blacklisted, false otherwise
     */
    protected function isIpBlacklisted($ip, $blacklistString)
    {
        $patterns = array_filter(array_map('trim', explode("\n", $blacklistString)));
        $ip_long = ip2long($ip);

        if (false === $ip_long) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if (strpos($pattern, '/') !== false) {
                // CIDR Check
                list($subnet, $mask) = explode('/', $pattern, 2);
                $subnet_long = ip2long($subnet);

                if (false !== $subnet_long && is_numeric($mask) && $mask >= 0 && $mask <= 32) {
                    $mask_long = -1 << (32 - (int) $mask);

                    if (($ip_long & $mask_long) == ($subnet_long & $mask_long)) {
                        return true;
                    }
                }
            } elseif (strpos($pattern, '*') !== false) {
                // Wildcard Check
                if (preg_match('/^' . str_replace('\*', '\d+', preg_quote($pattern, '/')) . '$/', $ip)) {
                    return true;
                }
            } elseif ($ip === $pattern) {
                // Exact Match
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an email address belongs to a known disposable domain.
     *
     * @param string $email The user's email address
     *
     * @return bool True if the email domain is disposable, false otherwise
     */
    protected function isEmailDisposable($email)
    {
        if (!Validate::isEmail($email)) {
            return false;
        }

        $domain = strtolower(substr(strrchr($email, '@'), 1));
        $disposables = include _PS_MODULE_DIR_ . $this->name . '/data/disposable_domains.php';

        return isset($disposables[$domain]);
    }

    /**
     * Check if an email address matches any pattern in the blacklist.
     * Supports exact emails and wildcard patterns (*).
     *
     * @param string $email The user's email address
     * @param string $blacklistString The newline-separated string of blacklist patterns
     *
     * @return bool True if the email is blacklisted, false otherwise
     */
    protected function isEmailBlacklisted($email, $blacklistString)
    {
        $email = strtolower($email);
        $patterns = array_filter(array_map('trim', explode("\n", $blacklistString)));

        if (!Validate::isEmail($email)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            $pattern = strtolower($pattern);

            if (strpos($pattern, '*') !== false) {
                // Wildcard Check
                if (preg_match('/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/', $email)) {
                    return true;
                }
            } elseif ($email === $pattern) {
                // Exact Match
                return true;
            }
        }

        return false;
    }

    protected function recordStat($form, $success)
    {
        return Db::getInstance()->insert('invrecaptcha_stats', [
            'id_shop' => (int) $this->context->shop->id,
            'form' => pSQL($form),
            'provider' => pSQL($this->config['provider']),
            'result' => (int) $success,
            'date_add' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function verifyCaptcha()
    {
        if ($response = Tools::getValue('invrecaptcha_response')) {
            if ('altcha' === $this->config['provider']) {
                require_once _PS_MODULE_DIR_ . 'invrecaptcha/classes/Altcha.php';

                if (!$this->config['secretkey']) {
                    return [
                        'success' => false,
                        'error-codes' => ['missing-secret-key'],
                    ];
                }
                if (!Altcha::verifySolution($response, $this->config['secretkey'])) {
                    return [
                        'success' => false,
                        'error-codes' => ['invalid-input-response'],
                    ];
                }

                return ['success' => true];
            }

            static $result;

            if ($result === null) {
                $http = [
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'method' => 'POST',
                    'content' => http_build_query([
                        'sitekey' => $this->config['sitekey'],
                        'secret' => $this->config['secretkey'],
                        'response' => $response,
                        'remoteip' => Tools::getRemoteAddr(),
                    ]),
                    'timeout' => 5,
                ];
                $raw = Tools::file_get_contents(
                    'hcaptcha' === $this->config['provider'] ? 'https://hcaptcha.com/siteverify' : 'https://www.google.com/recaptcha/api/siteverify',
                    false,
                    stream_context_create(['http' => &$http]),
                    $http['timeout']
                );
                $result = $raw ? json_decode($raw, true) : [
                    'success' => false,
                    'error-codes' => ['connection-failed'],
                ];
            }

            return $result;
        }

        return [
            'success' => false,
            'error-codes' => ['missing-captcha'],
        ];
    }

    protected function handleCaptchaResponse($res, $form)
    {
        $fail = empty($res['success']) || isset($res['score']) && $res['score'] < $this->config['score'];

        $this->recordStat($form, !$fail);

        if ($this->config['log']) {
            $res['debug'] = [
                'url' => $_SERVER['REQUEST_URI'],
                'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
                'ip' => $_SERVER['REMOTE_ADDR'],
            ];
            PrestaShopLogger::addLog('Spam Protection: ' . json_encode($res, JSON_UNESCAPED_SLASHES), $fail ? 3 : 1, 0, 'SwiftMessage', 0, true);
        }
        if ($fail) {
            $errorCodes = isset($res['error-codes']) ? $res['error-codes'] : ['low-score'];
            $errorMsg = $this->l('Captcha error:') . ' ' . implode(', ', $errorCodes);

            if (in_array('ip-blacklisted', $errorCodes)) {
                $errorMsg = $this->l('Your IP address has been blocked.');
            } elseif (in_array('email-blacklisted', $errorCodes)) {
                $errorMsg = $this->l('Your email address has been blocked.');
            } elseif (in_array('disposable-email-detected', $errorCodes)) {
                $errorMsg = $this->l('Disposable email addresses are not allowed.');
            }

            if ('mailalert' === $form) {
                exit(json_encode(['message' => $errorMsg, 'error' => true]));
            }
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                exit(json_encode(['errors' => [$errorMsg], 'hasError' => true]));
            }
            $this->context->cookie->__set('irc_error', $errorMsg);
            $domain = Tools::usingSecureMode() ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
            Tools::redirect($domain . $_SERVER['REQUEST_URI'] . '#recaptcha');
        }
    }

    /**
     * @deprecated Since 2.0.0
     */
    public function hookActionSubmitAccountBefore($params)
    {
        return true;
    }

    protected function postValidation()
    {
        $errors = [];

        if (Tools::isSubmit('submitInvReCaptcha')) {
            if (!in_array(Tools::getValue('provider'), ['grecaptcha', 'hcaptcha', 'altcha'])) {
                $errors[] = sprintf($this->l('Invalid value for field: %s'), $this->l('Captcha Provider'));
            }
            if (Tools::getValue('provider') !== 'altcha' && !Validate::isTableOrIdentifier(Tools::getValue('sitekey'))) {
                $errors[] = sprintf($this->l('Invalid value for field: %s'), $this->l('Site key'));
            }
            if (!Validate::isTableOrIdentifier(Tools::getValue('secretkey'))) {
                $errors[] = sprintf($this->l('Invalid value for field: %s'), $this->l('Secret key'));
            }
            if (!in_array(Tools::getValue('theme'), ['light', 'dark'])) {
                $errors[] = sprintf($this->l('Invalid value for field: %s'), $this->l('Theme'));
            }
            if (!in_array(Tools::getValue('pos'), ['right', 'left'])) {
                $errors[] = sprintf($this->l('Invalid value for field: %s'), $this->l('Position'));
            }
            if (!Validate::isInt(Tools::getValue('offset'))) {
                $errors[] = $this->l('Invalid value: Distance from bottom should be integer!');
            }
            foreach ($this->forms as $form) {
                $value = Tools::getValue('form_' . $form['name']);

                if (!empty($value) && 'on' !== $value) {
                    $errors[] = sprintf($this->l('Invalid value for field: %s'), $this->l('Protected forms') . ' - ' . $form['label']);
                }
            }
        }
        if (count($errors)) {
            $this->_html .= $this->displayError(version_compare(_PS_VERSION_, '1.6.1', '<') ? implode(",\n", $errors) : $errors);

            return false;
        }

        return true;
    }

    protected function postProcess()
    {
        if ($this->context->controller->ajax && method_exists($this, $method = 'ajaxProcess' . Tools::getValue('ajax'))) {
            $this->$method();
        }

        if (Tools::isSubmit('submitInvReCaptcha')) {
            $forms = [];

            foreach ($this->forms as $form) {
                if (Tools::isSubmit('form_' . $form['name'])) {
                    $forms[] = $form['name'];
                }
            }
            $this->config = [
                'provider' => Tools::getValue('provider', $this->defaults['provider']),
                'version' => (int) Tools::getValue('version', $this->defaults['version']),
                'sitekey' => Tools::getValue('sitekey'),
                'secretkey' => Tools::getValue('secretkey'),
                'score' => (float) Tools::getValue('score', $this->defaults['score']),
                'complexity' => (int) Tools::getValue('complexity', $this->defaults['complexity']),
                'theme' => Tools::getValue('theme'),
                'pos' => Tools::getValue('pos'),
                'offset' => (int) Tools::getValue('offset', $this->defaults['offset']),
                'forms' => $forms,
                'check_disposable' => (bool) Tools::getValue('check_disposable'),
                'expiry' => (int) $this->config['expiry'],
                'log' => (bool) $this->config['log'],
            ];
            Configuration::updateValue('irc_config', json_encode($this->config));

            $this->_html .= $this->displayConfirmation($this->trans('Settings updated', [], 'Admin.Global'));
        }

        if (Tools::isSubmit('submitAdvanced')) {
            $this->config['log'] = (bool) Tools::getValue('log');
            $this->config['expiry'] = (int) Tools::getValue('expiry', $this->defaults['expiry']) ?: 300;

            Configuration::updateValue('irc_config', json_encode($this->config));
            Configuration::updateValue('irc_blacklist_ips', Tools::getValue('blacklist_ips'));
            Configuration::updateValue('irc_blacklist_emails', Tools::getValue('blacklist_emails'));

            $this->_html .= $this->displayConfirmation($this->trans('Settings updated', [], 'Admin.Global'));
        }
    }

    public function ajaxProcessChallenge()
    {
        if (in_array($provider = Tools::getValue('provider'), ['grecaptcha', 'hcaptcha', 'altcha'])) {
            $this->config['provider'] = $provider;
            $this->config['sitekey'] = Tools::getValue('sitekey');
            $this->config['secretkey'] = Tools::getValue('secretkey');
            $json = json_encode($this->verifyCaptcha());
            exit(")]}'\n$json");
        }
    }

    public function ajaxProcessGetStats()
    {
        $this->cleanStats();

        $dateFrom = Tools::getValue('date_from', date('Y-m-d', strtotime('-30 days')));
        $dateTo = Tools::getValue('date_to', date('Y-m-d'));
        $form = Tools::getValue('form', '');

        $where = 'WHERE s.`id_shop` = ' . (int) $this->context->shop->id
            . ' AND s.`date_add` >= "' . pSQL($dateFrom) . ' 00:00:00"'
            . ' AND s.`date_add` <= "' . pSQL($dateTo) . ' 23:59:59"';

        if ($form) {
            $where .= ' AND s.`form` = "' . pSQL($form) . '"';
        }

        $db = Db::getInstance();

        // Summary totals
        $totals = $db->getRow('
            SELECT
                COUNT(*) as total,
                SUM(s.`result` = 1) as legitimate,
                SUM(s.`result` = 0) as blocked
            FROM `' . _DB_PREFIX_ . 'invrecaptcha_stats` s
            ' . $where
        );

        // Per-form breakdown
        $byForm = $db->executeS('
            SELECT
                s.`form`,
                COUNT(*) as total,
                SUM(s.`result` = 1) as legitimate,
                SUM(s.`result` = 0) as blocked
            FROM `' . _DB_PREFIX_ . 'invrecaptcha_stats` s
            ' . $where . '
            GROUP BY s.`form`
            ORDER BY total DESC
        ');

        // Daily time-series
        $daily = $db->executeS('
            SELECT
                DATE(s.`date_add`) as date,
                SUM(s.`result` = 1) as legitimate,
                SUM(s.`result` = 0) as blocked
            FROM `' . _DB_PREFIX_ . 'invrecaptcha_stats` s
            ' . $where . '
            GROUP BY DATE(s.`date_add`)
            ORDER BY date ASC
        ');

        exit(json_encode([
            'totals' => [
                'total' => (int) $totals['total'],
                'legitimate' => (int) $totals['legitimate'],
                'blocked' => (int) $totals['blocked'],
                'block_rate' => $totals['total'] > 0
                    ? round($totals['blocked'] / $totals['total'] * 100, 1)
                    : 0,
            ],
            'byForm' => $byForm,
            'daily' => $daily,
        ]));
    }

    protected function cleanStats()
    {
        Db::getInstance()->execute('
            DELETE FROM `' . _DB_PREFIX_ . 'invrecaptcha_stats`
            WHERE `date_add` < DATE_SUB(NOW(), INTERVAL 90 DAY)
        ');
    }

    public function getContent()
    {
        $controller = $this->context->controller;
        $controller->css_files[$this->_path . 'views/css/h-captcha.css?' . $this->version] = 'all';
        $controller->css_files[$this->_path . 'views/css/admin.css?' . $this->version] = 'all';
        $controller->js_files[] = $this->_path . 'views/js/admin.js?' . $this->version;
        $controller->js_files[] = $this->_path . 'views/lib/chart.min.js?4.5.1';

        Media::addJsDef([
            'i18n' => [
                'legitimate' => $this->l('Legitimate'),
                'blocked' => $this->l('Blocked'),
                // hCaptcha
                'protected' => $this->l('Protected by hCaptcha'),
                'privacy' => $this->l('Privacy'),
                'terms' => $this->l('Terms'),
            ],
        ]);
        $this->forms = [
            ['name' => 'contact', 'label' => $this->l('Contact us')],
            ['name' => 'newsletter', 'label' => $this->l('Signup for newsletter')],
            ['name' => 'login', 'label' => $this->l('Log in')],
            ['name' => 'register', 'label' => $this->l('Registration') . ' & ' . $this->trans('Order as a guest', [], 'Shop.Theme.Checkout')],
            ['name' => 'resetpass', 'label' => $this->l('Forgot / Reset password')],
        ];
        Module::isEnabled('ps_emailalerts') && $this->forms[] = [
            'name' => 'mailalert',
            'label' => $this->trans('Product availability', [], 'Modules.Mailalerts.Admin') . " ({$this->trans('Mail alerts', [], 'Modules.Mailalerts.Admin')})",
        ];
        Module::isEnabled('productcomments') && $this->forms[] = [
            'name' => 'review',
            'label' => $this->l('Write a review') . " ({$this->trans('Product Comments', [], 'Modules.Productcomments.Admin')})",
        ];
        Module::isEnabled('jmsblog') && $this->forms[] = [
            'name' => 'jmsBlogComment',
            'label' => $this->l('Submit comment') . ' (JMS Blog)',
        ];
        Module::isEnabled('ybc_blog') && $this->forms[] = [
            'name' => 'ybcBlogComment',
            'label' => $this->l('Send a comment') . ' (ETS Blog)',
        ];
        if ($this->postValidation()) {
            $this->postProcess();
        }

        $this->context->smarty->assign([
            'irc_panel' => $panel = Tools::getValue('panel', 'settings'),
            'irc_panel_url' => $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&panel=',
        ]);
        $this->_html .= $this->display(__FILE__, 'views/templates/admin/tabs.tpl');

        $hf = $this->initHelperForm();
        $this->_html .= $this->renderSettingsTab($hf, $panel);
        $this->_html .= $this->renderStatsTab();
        $this->_html .= $this->renderAdvancedTab($hf, $panel);

        return $this->_html;
    }

    protected function initHelperForm()
    {
        $hf = new HelperForm();
        $hf->show_toolbar = false;
        $hf->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $hf->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $hf->id = 'inv-recaptcha';
        $hf->identifier = $this->identifier;
        $hf->currentIndex = "{$this->context->link->getAdminLink('AdminModules', false)}&configure={$this->name}&tab_module={$this->tab}&module_name={$this->name}";
        $hf->token = Tools::getAdminTokenLite('AdminModules');
        $hf->tpl_vars = [
            'fields_value' => $this->getFieldsValue(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $hf;
    }

    protected function renderSettingsTab($hf, $activePanel)
    {
        $this->context->smarty->assign('style', 'settings' !== $activePanel ? 'display: none' : '');
        $hf->submit_action = 'submitInvReCaptcha';
        $form = [
            'id_form' => 'irc-tab-settings',
            'input' => [
                [
                    'name' => 'protection',
                    'type' => 'html',
                    'html_content' => $this->l('Choose Protection'),
                    'form_group_class' => 'h3',
                ],
                [
                    'name' => 'provider',
                    'label' => $this->l('Provider'),
                    'type' => 'radio',
                    'values' => [
                        [
                            'id' => 'provider_grecaptcha',
                            'value' => 'grecaptcha',
                            'label' => 'Google reCAPTCHA',
                        ],
                        [
                            'id' => 'provider_hcaptcha',
                            'value' => 'hcaptcha',
                            'label' => 'hCaptcha',
                        ],
                        [
                            'id' => 'provider_altcha',
                            'value' => 'altcha',
                            'label' => 'ALTCHA ' . $this->l('(100% GDPR Compliant, self-hosted)'),
                        ],
                    ],
                    'desc' => $this->display(__FILE__, 'views/templates/admin/info.tpl'),
                    'form_group_class' => 'provider',
                ],
                [
                    'name' => 'grecaptcha_settings',
                    'type' => 'html',
                    'html_content' => $this->l('Google reCAPTCHA Settings'),
                    'form_group_class' => 'g-captcha h3',
                ],
                [
                    'name' => 'hcaptcha_settings',
                    'type' => 'html',
                    'html_content' => $this->l('hCaptcha Settings'),
                    'form_group_class' => 'h-captcha h3',
                ],
                [
                    'name' => 'altcha_settings',
                    'type' => 'html',
                    'html_content' => $this->l('ALTCHA Settings'),
                    'form_group_class' => 'a-captcha h3',
                ],
                [
                    'name' => 'version',
                    'type' => 'hidden',
                ],
                [
                    'name' => 'sitekey',
                    'label' => $this->l('Site key'),
                    'type' => 'textbutton',
                    'required' => true,
                    'button' => [
                        'label' => $this->l('Get Keys'),
                        'attributes' => [
                            'name' => 'getkeys',
                        ],
                    ],
                    'form_group_class' => 'g-captcha h-captcha',
                ],
                [
                    'name' => 'secretkey',
                    'label' => $this->l('Secret key'),
                    'type' => 'textbutton',
                    'required' => true,
                    'button' => [
                        'label' => $this->l('Generate'),
                        'class' => 'a-captcha',
                        'attributes' => [
                            'name' => 'generate',
                        ],
                    ],
                ],
                [
                    'name' => 'complexity',
                    'label' => $this->l('Complexity'),
                    'type' => 'select',
                    'class' => 'fixed-width-xl',
                    'options' => [
                        'id' => 'value',
                        'name' => 'label',
                        'query' => [
                            ['value' => 1, 'label' => $this->trans('Min', [], 'Admin.Global')],
                            ['value' => 2, 'label' => $this->l('Low') . " ({$this->trans('Default', [], 'Admin.Global')})"],
                            ['value' => 3, 'label' => $this->l('Medium')],
                            ['value' => 4, 'label' => $this->l('High')],
                            ['value' => 5, 'label' => $this->trans('Max', [], 'Admin.Global')],
                        ],
                    ],
                    'form_group_class' => 'a-captcha',
                    'desc' => $this->l('Select the complexity level for the ALTCHA challenge.'),
                ],
                [
                    'name' => 'score',
                    'label' => $this->l('Security level'),
                    'type' => $this->config['version'] < 3 ? 'hidden' : 'select',
                    'class' => 'fixed-width-xl',
                    'options' => [
                        'id' => 'value',
                        'name' => 'label',
                        'query' => [
                            ['value' => 0.1, 'label' => "0.1 ({$this->trans('Min', [], 'Admin.Global')})"],
                            ['value' => 0.2, 'label' => 0.2],
                            ['value' => 0.3, 'label' => 0.3],
                            ['value' => 0.4, 'label' => 0.4],
                            ['value' => 0.5, 'label' => "0.5 ({$this->trans('Default', [], 'Admin.Global')})"],
                            ['value' => 0.6, 'label' => 0.6],
                            ['value' => 0.7, 'label' => 0.7],
                            ['value' => 0.8, 'label' => 0.8],
                            ['value' => 0.9, 'label' => "0.9 ({$this->trans('Max', [], 'Admin.Global')})"],
                        ],
                    ],
                    'form_group_class' => 'g-captcha',
                ],
                [
                    'name' => 'general_settings',
                    'type' => 'html',
                    'html_content' => $this->l('General Settings'),
                    'form_group_class' => 'h3',
                ],
                [
                    'name' => 'form',
                    'label' => $this->l('Protected forms'),
                    'type' => 'checkbox',
                    'values' => [
                        'id' => 'name',
                        'name' => 'label',
                        'query' => $this->forms,
                    ],
                    'desc' => $this->l('Enable spam protection for the selected forms on your website.'),
                ],
                [
                    'name' => 'check_disposable',
                    'label' => $this->l('Check for Disposable Emails'),
                    'type' => 'switch',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'disposable_on',
                            'value' => true,
                            'label' => $this->trans('Enabled', [], 'Admin.Global'),
                        ],
                        [
                            'id' => 'disposable_off',
                            'value' => false,
                            'label' => $this->trans('Disabled', [], 'Admin.Global'),
                        ],
                    ],
                    'desc' => $this->l('Block submissions using known disposable email domains.'),
                ],
                [
                    'name' => 'style_settings',
                    'type' => 'html',
                    'html_content' => $this->l('Style Settings'),
                    'form_group_class' => 'h3 g-captcha h-captcha',
                ],
                [
                    'name' => 'theme',
                    'label' => $this->trans('Theme', [], 'Admin.Design.Feature'),
                    'type' => 'select',
                    'class' => 'fixed-width-xl',
                    'options' => [
                        'id' => 'value',
                        'name' => 'label',
                        'query' => [
                            ['value' => 'light', 'label' => $this->l('Light')],
                            ['value' => 'dark', 'label' => $this->l('Dark')],
                        ],
                    ],
                    'desc' => $this->l('Select the color theme for the badge.'),
                    'form_group_class' => 'g-captcha h-captcha',
                ],
                [
                    'name' => 'pos',
                    'label' => $this->trans('Position', [], 'Admin.Global'),
                    'type' => 'select',
                    'class' => 'fixed-width-xl',
                    'options' => [
                        'id' => 'value',
                        'name' => 'label',
                        'query' => [
                            ['value' => 'right', 'label' => $this->l('Bottom right')],
                            ['value' => 'left', 'label' => $this->l('Bottom left')],
                        ],
                    ],
                    'desc' => $this->l('Choose where the badge will be displayed on the screen.'),
                    'form_group_class' => 'g-captcha h-captcha',
                ],
                [
                    'name' => 'offset',
                    'label' => $this->l('Distance from bottom'),
                    'type' => 'text',
                    'class' => 'fixed-width-sm',
                    'placeholder' => '14',
                    'suffix' => 'px',
                    'desc' => $this->l('Define the vertical distance (in pixels) of the badge from the bottom edge of the screen.'),
                    'form_group_class' => 'g-captcha h-captcha',
                ],
            ],
            'submit' => ['title' => $this->trans('Save', [], 'Admin.Global')],
        ];

        return $hf->generateForm([
            'form' => ['form' => &$form],
        ]);
    }

    protected function renderStatsTab()
    {
        $this->context->smarty->assign([
            'irc_date_from' => date('Y-m-d', strtotime('-30 days')),
            'irc_date_to' => date('Y-m-d'),
            'irc_forms' => $this->forms,
        ]);

        return $this->display(__FILE__, 'views/templates/admin/stats.tpl');
    }

    protected function renderAdvancedTab($hf, $activePanel)
    {
        $this->context->smarty->assign('style', 'advanced' !== $activePanel ? 'display: none' : '');
        $hf->submit_action = 'submitAdvanced';
        $form = [
            'id_form' => 'irc-tab-advanced',
            'input' => [
                [
                    'name' => 'advanced',
                    'type' => 'html',
                    'html_content' => $this->l('Advanced Settings'),
                    'form_group_class' => 'h3',
                ],
                [
                    'name' => 'blacklist_emails',
                    'label' => $this->l('Blacklist Emails / Patterns'),
                    'type' => 'textarea',
                    'rows' => 4,
                    'desc' => $this->l('Enter one email address or pattern per line (e.g., spammer@example.com, *@mail.ru, *@*.cn, user@*). Emails matching these patterns will always fail the captcha check.'),
                ],
                [
                    'name' => 'blacklist_ips',
                    'label' => $this->l('Blacklist IPs / Patterns'),
                    'type' => 'textarea',
                    'rows' => 4,
                    'desc' => $this->l('Enter one IP address or pattern per line (e.g., 192.168.1.1, 192.168.1.*, 10.0.0.0/24). Requests from matching IPs will always fail the captcha check.'),
                ],
                [
                    'name' => 'expiry',
                    'label' => $this->l('Challenge Expiration'),
                    'type' => 'text',
                    'class' => 'fixed-width-sm',
                    'placeholder' => '300',
                    'suffix' => 's',
                    'form_group_class' => 'a-captcha',
                    'desc' => $this->l('Maximum validity duration (in seconds) for the ALTCHA challenge after generation.'),
                ],
                [
                    'name' => 'log',
                    'label' => $this->trans('Logs', [], 'Admin.Navigation.Menu'),
                    'type' => 'switch',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'log_on',
                            'value' => true,
                            'label' => $this->trans('Enabled', [], 'Admin.Global'),
                        ],
                        [
                            'id' => 'log_off',
                            'value' => false,
                            'label' => $this->trans('Disabled', [], 'Admin.Global'),
                        ],
                    ],
                    'desc' => $this->l('Successful and failed captcha verification attempts will be recorded in the PrestaShop logs (under Advanced Parameters > Logs). This can be useful for troubleshooting and monitoring.'),
                ],
            ],
            'submit' => ['title' => $this->trans('Save', [], 'Admin.Global')],
        ];

        return $hf->generateForm([
            'form' => ['form' => &$form],
        ]);
    }

    public function getConfig()
    {
        return $this->config;
    }

    protected function getFieldsValue()
    {
        $fields = $this->config;
        $fields['blacklist_ips'] = Configuration::get('irc_blacklist_ips');
        $fields['blacklist_emails'] = Configuration::get('irc_blacklist_emails');

        if (!empty($fields['forms'])) {
            foreach ($fields['forms'] as $form) {
                $fields['form_' . $form] = 'on';
            }
        }

        return $fields;
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        if ($this->isActive) {
            Media::addJsDef([
                'ircConfig' => [
                    'lang' => $this->context->language->iso_code,
                    'provider' => $provider = $this->config['provider'],
                    'forms' => $this->config['forms'],
                ] + ('altcha' === $provider ? [
                    'challengeurl' => $this->context->link->getModuleLink('invrecaptcha', 'challenge'),
                ] : [ // grecaptcha / hcaptcha
                    'sitekey' => $this->config['sitekey'],
                    'theme' => $this->config['theme'],
                    'pos' => $this->config['pos'],
                    'offset' => $this->config['offset'],
                    'i18n' => 'hcaptcha' === $provider ? [
                        'protected' => $this->l('Protected by hCaptcha'),
                        'privacy' => $this->l('Privacy'),
                        'terms' => $this->l('Terms'),
                    ] : [],
                ]),
            ]);
            if ('hcaptcha' === $provider) {
                $this->registerStylesheet('invrecaptcha-hcaptcha', 'modules/invrecaptcha/views/css/h-captcha.css');
            }
            $this->registerJavascript('invrecaptcha', 'modules/invrecaptcha/views/js/invrecaptcha.js');
        }
        if (!empty($this->context->cookie->irc_error)) {
            $this->context->controller->errors[] = $this->context->cookie->irc_error;
            unset($this->context->cookie->irc_error);
        }
    }

    protected function registerJavascript($hander, $path)
    {
        if (method_exists($controller = $this->context->controller, 'registerJavascript')) {
            (int) _PS_VERSION_ >= 8 || Configuration::get('PS_CSS_THEME_CACHE')
                ? $controller->registerJavascript($hander, $path, ['version' => $this->version])
                : $controller->registerJavascript($hander, '//' . Tools::getMediaServer($path = __PS_BASE_URI__ . "$path?{$this->version}") . $path, ['server' => 'remote']);
        } else {
            $controller->js_files[] = __PS_BASE_URI__ . "$path?{$this->version}";
        }
    }

    protected function registerStylesheet($hander, $path)
    {
        if (method_exists($controller = $this->context->controller, 'registerStylesheet')) {
            (int) _PS_VERSION_ >= 8 || Configuration::get('PS_CSS_THEME_CACHE')
                ? $controller->registerStylesheet($hander, $path, ['version' => $this->version])
                : $controller->registerStylesheet($hander, '//' . Tools::getMediaServer($path = __PS_BASE_URI__ . "$path?{$this->version}") . $path, ['server' => 'remote']);
        } else {
            $controller->css_files[__PS_BASE_URI__ . "$path?{$this->version}"] = 'all';
        }
    }

    protected function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            return $this->l($id);
        }

        return $this->context->getTranslator()->trans($id, $parameters, $domain, $locale);
    }
}
