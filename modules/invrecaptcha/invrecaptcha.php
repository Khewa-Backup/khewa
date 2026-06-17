<?php
/**
 * Spam Protection - Invisible reCaptcha
 *
 * @author    WebshopWorks
 * @copyright 2018-2025 WebshopWorks.com
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
    ];
    protected $config;
    protected $isActive;
    protected $forms;
    protected $_html = '';

    public function __construct()
    {
        $this->name = 'invrecaptcha';
        $this->tab = 'administration';
        $this->version = '1.3.0';
        $this->author = 'WebshopWorks';
        $this->module_key = '589d8377a3df8b4f597826ea5430d9da';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        $this->displayName = $this->l('Spam Protection - Invisible reCaptcha');
        $this->description = $this->l('Protect your site against spam and abuse, while letting your real customers pass through with ease.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        parent::__construct();

        $json = Configuration::get('irc_config');
        $this->config = $json ? array_merge($this->defaults, json_decode($json, true)) : $this->defaults;
        $this->isActive = $this->config['sitekey'] && $this->config['secretkey'] && $this->config['forms'];
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
            'actionBeforeSubmitAccount',
            'actionSubmitAccountBefore',
        ], function ($carry, $hook) {
            return $carry && $this->registerHook($hook);
        }, parent::install());
    }

    public function uninstall()
    {
        Configuration::deleteByName('irc_config');
        Configuration::deleteByName('irc_blacklist_ips');
        Configuration::deleteByName('irc_blacklist_emails');

        return parent::uninstall();
    }

    public function registerHook($hook_name, $shop_list = null)
    {
        if ($res = parent::registerHook($hook_name, $shop_list)) {
            $this->updatePosition((int) Hook::getIdByName($hook_name), 0, 1);
        }

        return $res;
    }

    public function __call($method, $args)
    {
        // BC fix for PS 1.7.3 - 1.7.6
        if (!strcasecmp($method, 'hookActionFrontControllerAfterInit')) {
            return $this->hookActionFrontControllerInitAfter();
        }
        // BC fix for PS 1.6
        if (!strcasecmp($method, 'hookActionBeforeSubmitAccount')) {
            return $this->hookActionSubmitAccountBefore(...$args);
        }
        trigger_error('Call to undefined method ' . __CLASS__ . "::$method()", E_USER_ERROR);
    }

    public function hookActionFrontControllerInitAfter($params = [])
    {
        if (!$this->isActive) {
            return;
        }
        $ps16 = version_compare(_PS_VERSION_, '1.7', '<');
        $submits = [
            'contact' => ['ctrl' => '', 'key' => 'submitMessage', 'email' => 'from'],
            'newsletter' => ['ctrl' => '', 'key' => 'submitNewsletter'],
            'login' => ['ctrl' => 'authentication', 'key' => $ps16 ? 'SubmitLogin' : 'submitLogin'],
            'register' => ['ctrl' => 'authentication', 'key' => $ps16 ? (Tools::getValue('is_new_customer') ? 'submitAccount' : 'submitGuestAccount') : 'submitCreate'],
            'resetpass' => ['ctrl' => 'password', 'key' => 'email'],
            'review' => ['ctrl' => '', 'key' => 'action', 'val' => 'add_comment'],
            'jmsBlogComment' => ['ctrl' => 'post', 'key' => 'action', 'val' => 'submitComment'],
            'ybcBlogComment' => ['ctrl' => '', 'key' => 'bcsubmit'],
        ];
        foreach ($this->config['forms'] as $form) {
            $submit = $submits[$form];

            if (Tools::isSubmit($submit['key'])
                && (empty($submit['val']) || Tools::getValue($submit['key']) === $submit['val'])
                && (empty($submit['ctrl']) || Tools::getValue('controller') === $submit['ctrl'])
            ) {
                $email = Tools::getValue(isset($submit['email']) ? $submit['email'] : 'email');
                // Check Disposable Email Domains first (if enabled)
                if ($email && $this->config['check_disposable'] && $this->isEmailDisposable($email)) {
                    return $this->handleCaptchaResponse([
                        'success' => false,
                        'error-codes' => ['disposable-email-detected'],
                    ]);
                }
                $blacklistEmails = Configuration::get('irc_blacklist_emails');
                // Check Email Blacklist
                if ($email && $blacklistEmails && $this->isEmailBlacklisted($email, $blacklistEmails)) {
                    return $this->handleCaptchaResponse([
                        'success' => false,
                        'error-codes' => ['email-blacklisted'],
                    ]);
                }
                $ip = Tools::getRemoteAddr();
                $blacklistIps = Configuration::get('irc_blacklist_ips');
                // Check IP Blacklist
                if ($ip && $blacklistIps && $this->isIpBlacklisted($ip, $blacklistIps)) {
                    return $this->handleCaptchaResponse([
                        'success' => false,
                        'error-codes' => ['ip-blacklisted'],
                    ]);
                }
                // Proceed with normal captcha verification if not blacklisted
                return $this->handleCaptchaResponse($this->verifyCaptcha());
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

    protected function verifyCaptcha()
    {
        if ($resp = Tools::getValue('inv-recaptcha-response')) {
            static $res;

            if ($res === null) {
                $url = 'https://www.google.com/recaptcha/api/siteverify';
                $ctx = ['http' => [
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'method' => 'POST',
                    'timeout' => 5,
                    'content' => http_build_query([
                        'secret' => $this->config['secretkey'],
                        'response' => $resp,
                        'remoteip' => Tools::getRemoteAddr(),
                    ]),
                ]];
                $res = Tools::file_get_contents($url, false, stream_context_create($ctx), $ctx['http']['timeout']);
            }

            return $res ? json_decode($res, true) : [
                'success' => false,
                'error-codes' => ['connection-failed'],
            ];
        }

        return [
            'success' => false,
            'error-codes' => ['robot-detected'],
        ];
    }

    protected function handleCaptchaResponse($res)
    {
        $fail = empty($res['success']) || isset($res['score']) && $res['score'] < $this->config['score'];

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
            $errorMsg = $this->l('Captcha error: ') . implode(', ', $errorCodes);

            if (in_array('ip-blacklisted', $errorCodes)) {
                $errorMsg = $this->l('Your IP address has been blocked.');
            } elseif (in_array('email-blacklisted', $errorCodes)) {
                $errorMsg = $this->l('Your email address has been blocked.');
            } elseif (in_array('disposable-email-detected', $errorCodes)) {
                $errorMsg = $this->l('Disposable email addresses are not allowed.');
            }

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                exit(json_encode(['errors' => [$errorMsg], 'hasError' => true]));
            }
            $this->context->cookie->irc_error = $errorMsg;
            $domain = Tools::usingSecureMode() ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
            Tools::redirect($domain . $_SERVER['REQUEST_URI'] . '#recaptcha');
        }
    }

    public function hookActionSubmitAccountBefore($params)
    {
        if ($this->isActive && $this->context->controller instanceof FrontController && in_array('register', $this->config['forms'])) {
            $email = Tools::getValue('email');
            // Check Disposable Email Domains first (if enabled)
            if ($email && $this->config['check_disposable'] && $this->isEmailDisposable($email)) {
                return $this->handleCaptchaResponse([
                    'success' => false,
                    'error-codes' => ['disposable-email-detected'],
                ]);
            }
            $blacklistEmails = Configuration::get('irc_blacklist_emails');
            // Check Email Blacklist
            if ($email && $blacklistEmails && $this->isEmailBlacklisted($email, $blacklistEmails)) {
                return $this->handleCaptchaResponse([
                    'success' => false,
                    'error-codes' => ['email-blacklisted'],
                ]);
            }
            $ip = Tools::getRemoteAddr();
            $blacklistIps = Configuration::get('irc_blacklist_ips');
            // Check IP Blacklist for registration
            if ($ip && $blacklistIps && $this->isIpBlacklisted($ip, $blacklistIps)) {
                return $this->handleCaptchaResponse([
                    'success' => false,
                    'error-codes' => ['ip-blacklisted'],
                ]);
            }
            // Proceed with normal captcha verification if not blacklisted
            $this->handleCaptchaResponse($this->verifyCaptcha());
        }

        return true;
    }

    protected function postValidation()
    {
        $errors = [];

        if (Tools::isSubmit('submitInvReCaptcha')) {
            if (!Validate::isTableOrIdentifier(Tools::getValue('sitekey'))) {
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
                    $errors[] = sprintf($this->l('Invalid value for field: %s - %s'), $this->l('Protected forms'), $form['label']);
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
        if (Tools::isSubmit('submitInvReCaptcha')) {
            $forms = [];

            foreach ($this->forms as $form) {
                if (Tools::isSubmit('form_' . $form['name'])) {
                    $forms[] = $form['name'];
                }
            }
            $this->config = [
                'version' => (int) Tools::getValue('version', $this->defaults['version']),
                'sitekey' => Tools::getValue('sitekey'),
                'secretkey' => Tools::getValue('secretkey'),
                'score' => (float) Tools::getValue('score', $this->defaults['score']),
                'theme' => Tools::getValue('theme'),
                'pos' => Tools::getValue('pos'),
                'offset' => (int) Tools::getValue('offset', $this->defaults['offset']),
                'forms' => $forms,
                'check_disposable' => (bool) Tools::getValue('check_disposable'),
                'log' => (bool) Tools::getValue('log'),
            ];
            Configuration::updateValue('irc_config', json_encode($this->config));
            Configuration::updateValue('irc_blacklist_ips', Tools::getValue('blacklist_ips'));
            Configuration::updateValue('irc_blacklist_emails', Tools::getValue('blacklist_emails'));

            $this->_html .= $this->displayConfirmation($this->trans('Settings updated', [], 'Admin.Global'));
        }
        if (Tools::isSubmit('checkSecretKey')) {
            $this->config['secretkey'] = Tools::getValue('checkSecretKey');
            $json = json_encode($this->verifyCaptcha());

            exit(")]}'\n$json");
        }
    }

    public function getContent()
    {
        $this->forms = [
            ['name' => 'contact', 'label' => $this->l('Contact us')],
            ['name' => 'review', 'label' => $this->l('Write a review')],
            ['name' => 'newsletter', 'label' => $this->l('Signup for newsletter')],
            ['name' => 'register', 'label' => $this->l('Registration')],
            ['name' => 'resetpass', 'label' => $this->l('Forgot / Reset password')],
            ['name' => 'login', 'label' => $this->l('Log in')],
        ];
        if (Module::isEnabled('jmsblog')) {
            $this->forms[] = ['name' => 'jmsBlogComment', 'label' => 'JMS Blog - Submit comment'];
        }
        if (Module::isEnabled('ybc_blog')) {
            $this->forms[] = ['name' => 'ybcBlogComment', 'label' => 'ETS Blog - Send a comment'];
        }
        if ($this->postValidation()) {
            $this->postProcess();
        }
        $this->renderForm();

        return $this->_html;
    }

    protected function renderForm()
    {
        $hf = new HelperForm();
        $hf->show_toolbar = false;
        $hf->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $hf->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $hf->id = 'inv-recaptcha';
        $hf->identifier = $this->identifier;
        $hf->submit_action = 'submitInvReCaptcha';
        $hf->currentIndex = "{$this->context->link->getAdminLink('AdminModules', false)}&configure={$this->name}&tab_module={$this->tab}&module_name={$this->name}";
        $hf->token = Tools::getAdminTokenLite('AdminModules');
        $hf->tpl_vars = [
            'fields_value' => $this->getFieldsValue(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];
        $this->_html .= $hf->generateForm([
            ['form' => [
                'legend' => ['icon' => 'icon-cog', 'title' => $this->trans('Settings', [], 'Admin.Global')],
                'description' => $this->display(__FILE__, 'views/templates/admin/info.tpl'),
                'input' => [
                    [
                        'name' => 'version',
                        'type' => 'hidden',
                    ],
                    [
                        'name' => 'sitekey',
                        'label' => $this->l('Site key'),
                        'type' => 'text',
                        'required' => true,
                    ],
                    [
                        'name' => 'secretkey',
                        'label' => $this->l('Secret key'),
                        'type' => 'text',
                        'required' => true,
                    ],
                    [
                        'name' => 'score',
                        'label' => $this->l('Security level'),
                        'type' => $this->config['version'] < 3 ? 'hidden' : 'select',
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
                    ],
                    [
                        'name' => 'theme',
                        'label' => $this->trans('Theme', [], 'Admin.Design.Feature'),
                        'type' => 'select',
                        'options' => [
                            'id' => 'value',
                            'name' => 'label',
                            'query' => [
                                ['value' => 'light', 'label' => $this->l('Light')],
                                ['value' => 'dark', 'label' => $this->l('Dark')],
                            ],
                        ],
                        'desc' => $this->l('Select the color theme for the badge.'),
                    ],
                    [
                        'name' => 'pos',
                        'label' => $this->trans('Position', [], 'Admin.Global'),
                        'type' => 'select',
                        'options' => [
                            'id' => 'value',
                            'name' => 'label',
                            'query' => [
                                ['value' => 'right', 'label' => $this->l('Bottom right')],
                                ['value' => 'left', 'label' => $this->l('Bottom left')],
                            ],
                        ],
                        'desc' => $this->l('Choose where the badge will be displayed on the screen.'),
                    ],
                    [
                        'name' => 'offset',
                        'label' => $this->l('Distance from bottom'),
                        'type' => 'text',
                        'class' => 'fixed-width-sm',
                        'placeholder' => '14',
                        'suffix' => 'px',
                        'desc' => $this->l('Define the vertical distance (in pixels) of the badge from the bottom edge of the screen.'),
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
            ]],
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
            unset($this->config['version'], $this->config['secretkey'], $this->config['score'], $this->config['log'], $this->config['check_disposable']);
            Media::addJsDef(['ircConfig' => $this->config]);
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
            Configuration::get('PS_JS_THEME_CACHE')
                ? $controller->registerJavascript($hander, $path)
                : $controller->registerJavascript($hander, __PS_BASE_URI__ . "$path?v={$this->version}", ['server' => 'remote']);
        } else {
            $controller->js_files[] = __PS_BASE_URI__ . "$path?v={$this->version}";
        }
    }
}
