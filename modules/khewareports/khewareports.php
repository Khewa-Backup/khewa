<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Khewareports extends Module
{
    // Default order state IDs (can be overridden in settings)
    const DEFAULT_STATE_CANCELED = 6;
    const DEFAULT_STATE_REFUNDED = 56;
    const DEFAULT_STATE_REFUNDED_OLD = 7;
    const DEFAULT_STATE_PARTIAL_REFUND = 25;
    const DEFAULT_STATE_PAYMENT_ERROR = 8;
    
    // Canadian Tax IDs
    const TAX_ID_CANADA_GST = 1;  // 5% GST
    const TAX_IDS_QUEBEC_QST = array(25, 34, 32, 31, 28);  // 9.975% QST
    
    // Default Payment Method Patterns (comma-separated values that map to each category)
    const DEFAULT_PM_CREDIT_CARD = 'Credit Card, Carte de crédit, Credit Card(instore)';
    const DEFAULT_PM_CASH = 'Cash, Comptant';
    const DEFAULT_PM_INTERAC = 'Interac';
    const DEFAULT_PM_GIFT_CARD = 'Gift card, Carte Cadeau, InStore Gift Card';
    const DEFAULT_PM_VOUCHER = 'Voucher';
    const DEFAULT_PM_CREDIT_SLIP = 'Credit Slip';
    const DEFAULT_PM_POS_MODULE = 'hspointofsalepro';
    
    public function __construct()
    {
        $this->name = 'khewareports';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Khewa';
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Khewa Reports');
        $this->description = $this->l('Reports and quick actions module for Khewa');
    }

    /**
     * Install module
     */
    public function install()
    {
        return parent::install() &&
            $this->registerHook('backOfficeHeader') &&
            $this->installTabs() &&
            $this->installDefaultConfig();
    }
    
    /**
     * Install default configuration values
     */
    protected function installDefaultConfig()
    {
        Configuration::updateValue('KHEWA_QUICK_EXPORT_PERIOD', 'daily');
        Configuration::updateValue('KHEWA_STATE_CANCELED', self::DEFAULT_STATE_CANCELED);
        Configuration::updateValue('KHEWA_STATE_REFUNDED', self::DEFAULT_STATE_REFUNDED);
        Configuration::updateValue('KHEWA_STATE_REFUNDED_OLD', self::DEFAULT_STATE_REFUNDED_OLD);
        Configuration::updateValue('KHEWA_STATE_PARTIAL_REFUND', self::DEFAULT_STATE_PARTIAL_REFUND);
        Configuration::updateValue('KHEWA_STATE_PAYMENT_ERROR', self::DEFAULT_STATE_PAYMENT_ERROR);
        
        // Payment method patterns (empty = use defaults)
        Configuration::updateValue('KHEWA_PM_CREDIT_CARD', '');
        Configuration::updateValue('KHEWA_PM_CASH', '');
        Configuration::updateValue('KHEWA_PM_INTERAC', '');
        Configuration::updateValue('KHEWA_PM_GIFT_CARD', '');
        Configuration::updateValue('KHEWA_PM_VOUCHER', '');
        Configuration::updateValue('KHEWA_PM_CREDIT_SLIP', '');
        Configuration::updateValue('KHEWA_PM_POS_MODULE', '');
        
        return true;
    }

    /**
     * Uninstall module
     */
    public function uninstall()
    {
        return $this->uninstallTabs() &&
            $this->uninstallConfig() &&
            parent::uninstall();
    }
    
    /**
     * Remove configuration values
     */
    protected function uninstallConfig()
    {
        Configuration::deleteByName('KHEWA_QUICK_EXPORT_PERIOD');
        Configuration::deleteByName('KHEWA_STATE_CANCELED');
        Configuration::deleteByName('KHEWA_STATE_REFUNDED');
        Configuration::deleteByName('KHEWA_STATE_REFUNDED_OLD');
        Configuration::deleteByName('KHEWA_STATE_PARTIAL_REFUND');
        Configuration::deleteByName('KHEWA_STATE_PAYMENT_ERROR');
        
        // Payment method patterns
        Configuration::deleteByName('KHEWA_PM_CREDIT_CARD');
        Configuration::deleteByName('KHEWA_PM_CASH');
        Configuration::deleteByName('KHEWA_PM_INTERAC');
        Configuration::deleteByName('KHEWA_PM_GIFT_CARD');
        Configuration::deleteByName('KHEWA_PM_VOUCHER');
        Configuration::deleteByName('KHEWA_PM_CREDIT_SLIP');
        Configuration::deleteByName('KHEWA_PM_POS_MODULE');
        
        return true;
    }

    /**
     * Install admin tabs
     */
    public function installTabs()
    {
        // Main Reports tab - "Khewa Reports"
        $tabReports = new Tab();
        $tabReports->active = 1;
        $tabReports->class_name = 'AdminKhewaReportsReports';
        $tabReports->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tabReports->name[$lang['id_lang']] = 'Khewa Reports';
        }
        $tabReports->id_parent = (int)Tab::getIdFromClassName('SELL');
        $tabReports->module = $this->name;
        if (!$tabReports->add()) {
            return false;
        }

        // QuickAction tab - "Export Quick"
        $tabQuickAction = new Tab();
        $tabQuickAction->active = 1;
        $tabQuickAction->class_name = 'AdminKhewaReportsQuickAction';
        $tabQuickAction->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tabQuickAction->name[$lang['id_lang']] = 'Export Quick';
        }
        $tabQuickAction->id_parent = (int)Tab::getIdFromClassName('SELL');
        $tabQuickAction->module = $this->name;
        if (!$tabQuickAction->add()) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall admin tabs
     */
    public function uninstallTabs()
    {
        $tabs = array(
            'AdminKhewaReportsReports',
            'AdminKhewaReportsQuickAction'
        );

        foreach ($tabs as $className) {
            $idTab = Tab::getIdFromClassName($className);
            if ($idTab) {
                $tab = new Tab($idTab);
                if (Validate::isLoadedObject($tab)) {
                    $tab->delete();
                }
            }
        }

        return true;
    }

    /**
     * Load the configuration form (settings page)
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitKhewaReportsModule')) {
            // Quick export period
            $quick_export_period = Tools::getValue('KHEWA_QUICK_EXPORT_PERIOD');
            if (in_array($quick_export_period, array('daily', 'weekly', 'monthly'))) {
                Configuration::updateValue('KHEWA_QUICK_EXPORT_PERIOD', $quick_export_period);
            }
            
            // Order state mappings
            Configuration::updateValue('KHEWA_STATE_CANCELED', (int)Tools::getValue('KHEWA_STATE_CANCELED'));
            Configuration::updateValue('KHEWA_STATE_REFUNDED', (int)Tools::getValue('KHEWA_STATE_REFUNDED'));
            Configuration::updateValue('KHEWA_STATE_REFUNDED_OLD', (int)Tools::getValue('KHEWA_STATE_REFUNDED_OLD'));
            Configuration::updateValue('KHEWA_STATE_PARTIAL_REFUND', (int)Tools::getValue('KHEWA_STATE_PARTIAL_REFUND'));
            Configuration::updateValue('KHEWA_STATE_PAYMENT_ERROR', (int)Tools::getValue('KHEWA_STATE_PAYMENT_ERROR'));
            
            // Payment method patterns (sanitize input)
            Configuration::updateValue('KHEWA_PM_CREDIT_CARD', pSQL(Tools::getValue('KHEWA_PM_CREDIT_CARD')));
            Configuration::updateValue('KHEWA_PM_CASH', pSQL(Tools::getValue('KHEWA_PM_CASH')));
            Configuration::updateValue('KHEWA_PM_INTERAC', pSQL(Tools::getValue('KHEWA_PM_INTERAC')));
            Configuration::updateValue('KHEWA_PM_GIFT_CARD', pSQL(Tools::getValue('KHEWA_PM_GIFT_CARD')));
            Configuration::updateValue('KHEWA_PM_VOUCHER', pSQL(Tools::getValue('KHEWA_PM_VOUCHER')));
            Configuration::updateValue('KHEWA_PM_CREDIT_SLIP', pSQL(Tools::getValue('KHEWA_PM_CREDIT_SLIP')));
            Configuration::updateValue('KHEWA_PM_POS_MODULE', pSQL(Tools::getValue('KHEWA_PM_POS_MODULE')));
            
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output . $this->displayForm();
    }

    /**
     * Get all order states for dropdown
     */
    protected function getOrderStates()
    {
        $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $states = OrderState::getOrderStates($id_lang);
        $options = array();
        $options[] = array('id' => 0, 'name' => $this->l('-- Select --'));
        foreach ($states as $state) {
            $options[] = array(
                'id' => $state['id_order_state'],
                'name' => $state['id_order_state'] . ' - ' . $state['name']
            );
        }
        return $options;
    }

    
    /**
     * Display settings form
     */
    protected function displayForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $order_states = $this->getOrderStates();

        $fields_form = array();
        
        // Quick Export Settings Form
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Quick Export Settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Export Period'),
                    'name' => 'KHEWA_QUICK_EXPORT_PERIOD',
                    'desc' => $this->l('Date range for quick export: Daily (today), Weekly (last 7 days), Monthly (last 30 days)'),
                    'options' => array(
                        'query' => array(
                            array('id' => 'daily', 'name' => $this->l('Daily')),
                            array('id' => 'weekly', 'name' => $this->l('Weekly')),
                            array('id' => 'monthly', 'name' => $this->l('Monthly')),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
            ),
        );
        
        // Order State Mapping Form
        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Order State Mapping'),
                'icon' => 'icon-tags'
            ),
            'description' => $this->l('Map order states for accurate report calculations. Select which order states correspond to each type.'),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Canceled Order State'),
                    'name' => 'KHEWA_STATE_CANCELED',
                    'desc' => $this->l('Orders with this state are considered canceled'),
                    'options' => array(
                        'query' => $order_states,
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Refunded Order State'),
                    'name' => 'KHEWA_STATE_REFUNDED',
                    'desc' => $this->l('Orders with this state are considered fully refunded'),
                    'options' => array(
                        'query' => $order_states,
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Refunded (Old) Order State'),
                    'name' => 'KHEWA_STATE_REFUNDED_OLD',
                    'desc' => $this->l('Legacy refunded state (if applicable)'),
                    'options' => array(
                        'query' => $order_states,
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Partial Refund Order State'),
                    'name' => 'KHEWA_STATE_PARTIAL_REFUND',
                    'desc' => $this->l('Orders with this state have partial refunds'),
                    'options' => array(
                        'query' => $order_states,
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Payment Error Order State'),
                    'name' => 'KHEWA_STATE_PAYMENT_ERROR',
                    'desc' => $this->l('Orders with payment errors'),
                    'options' => array(
                        'query' => $order_states,
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
            ),
        );
        
        // Payment Method Patterns Form
        $fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('Payment Method Patterns'),
                'icon' => 'icon-credit-card'
            ),
            'description' => $this->l('Add comma-separated patterns to match payment methods. Your values will be ADDED to the defaults below (not replace them). Duplicates are automatically removed (case-insensitive).'),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Credit Card'),
                    'name' => 'KHEWA_PM_CREDIT_CARD',
                    'desc' => $this->l('Always included: ') . self::DEFAULT_PM_CREDIT_CARD,
                    'placeholder' => $this->l('Add extra patterns here...'),
                    'class' => 'fixed-width-xxl',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Cash'),
                    'name' => 'KHEWA_PM_CASH',
                    'desc' => $this->l('Always included: ') . self::DEFAULT_PM_CASH,
                    'placeholder' => $this->l('Add extra patterns here...'),
                    'class' => 'fixed-width-xxl',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Interac'),
                    'name' => 'KHEWA_PM_INTERAC',
                    'desc' => $this->l('Always included: ') . self::DEFAULT_PM_INTERAC,
                    'placeholder' => $this->l('Add extra patterns here...'),
                    'class' => 'fixed-width-xxl',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Gift Card'),
                    'name' => 'KHEWA_PM_GIFT_CARD',
                    'desc' => $this->l('Always included: ') . self::DEFAULT_PM_GIFT_CARD,
                    'placeholder' => $this->l('Add extra patterns here...'),
                    'class' => 'fixed-width-xxl',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Voucher'),
                    'name' => 'KHEWA_PM_VOUCHER',
                    'desc' => $this->l('Always included: ') . self::DEFAULT_PM_VOUCHER,
                    'placeholder' => $this->l('Add extra patterns here...'),
                    'class' => 'fixed-width-xxl',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Credit Slip'),
                    'name' => 'KHEWA_PM_CREDIT_SLIP',
                    'desc' => $this->l('Always included: ') . self::DEFAULT_PM_CREDIT_SLIP,
                    'placeholder' => $this->l('Add extra patterns here...'),
                    'class' => 'fixed-width-xxl',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Point of Sale Module'),
                    'name' => 'KHEWA_PM_POS_MODULE',
                    'desc' => $this->l('Module name(s) for in-store orders. Always included: ') . self::DEFAULT_PM_POS_MODULE,
                    'placeholder' => $this->l('Add extra module names here...'),
                    'class' => 'fixed-width-xxl',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submitKhewaReportsModule';
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Set current values
        $helper->fields_value = array(
            'KHEWA_QUICK_EXPORT_PERIOD' => Configuration::get('KHEWA_QUICK_EXPORT_PERIOD', 'daily'),
            'KHEWA_STATE_CANCELED' => Configuration::get('KHEWA_STATE_CANCELED', self::DEFAULT_STATE_CANCELED),
            'KHEWA_STATE_REFUNDED' => Configuration::get('KHEWA_STATE_REFUNDED', self::DEFAULT_STATE_REFUNDED),
            'KHEWA_STATE_REFUNDED_OLD' => Configuration::get('KHEWA_STATE_REFUNDED_OLD', self::DEFAULT_STATE_REFUNDED_OLD),
            'KHEWA_STATE_PARTIAL_REFUND' => Configuration::get('KHEWA_STATE_PARTIAL_REFUND', self::DEFAULT_STATE_PARTIAL_REFUND),
            'KHEWA_STATE_PAYMENT_ERROR' => Configuration::get('KHEWA_STATE_PAYMENT_ERROR', self::DEFAULT_STATE_PAYMENT_ERROR),
            // Payment method patterns
            'KHEWA_PM_CREDIT_CARD' => Configuration::get('KHEWA_PM_CREDIT_CARD'),
            'KHEWA_PM_CASH' => Configuration::get('KHEWA_PM_CASH'),
            'KHEWA_PM_INTERAC' => Configuration::get('KHEWA_PM_INTERAC'),
            'KHEWA_PM_GIFT_CARD' => Configuration::get('KHEWA_PM_GIFT_CARD'),
            'KHEWA_PM_VOUCHER' => Configuration::get('KHEWA_PM_VOUCHER'),
            'KHEWA_PM_CREDIT_SLIP' => Configuration::get('KHEWA_PM_CREDIT_SLIP'),
            'KHEWA_PM_POS_MODULE' => Configuration::get('KHEWA_PM_POS_MODULE'),
        );

        return $helper->generateForm($fields_form);
    }
    
    /**
     * Get configured order states
     * @return array
     */
    public static function getConfiguredStates()
    {
        $canceled = Configuration::get('KHEWA_STATE_CANCELED');
        $refunded = Configuration::get('KHEWA_STATE_REFUNDED');
        $refunded_old = Configuration::get('KHEWA_STATE_REFUNDED_OLD');
        $partial_refund = Configuration::get('KHEWA_STATE_PARTIAL_REFUND');
        $payment_error = Configuration::get('KHEWA_STATE_PAYMENT_ERROR');

        return array(
            'canceled' => ($canceled !== false && $canceled !== '') ? (int)$canceled : (int)self::DEFAULT_STATE_CANCELED,
            'refunded' => ($refunded !== false && $refunded !== '') ? (int)$refunded : (int)self::DEFAULT_STATE_REFUNDED,
            'refunded_old' => ($refunded_old !== false && $refunded_old !== '') ? (int)$refunded_old : (int)self::DEFAULT_STATE_REFUNDED_OLD,
            'partial_refund' => ($partial_refund !== false && $partial_refund !== '') ? (int)$partial_refund : (int)self::DEFAULT_STATE_PARTIAL_REFUND,
            'payment_error' => ($payment_error !== false && $payment_error !== '') ? (int)$payment_error : (int)self::DEFAULT_STATE_PAYMENT_ERROR,
        );
    }
    
    /**
     * Get refund state IDs as array
     * @return array
     */
    public static function getRefundStateIds()
    {
        $states = self::getConfiguredStates();
        return array(
            $states['refunded'],
            $states['refunded_old'],
            $states['partial_refund']
        );
    }
    

    /**
     * Get excluded state IDs (canceled, payment error)
     * @return array
     */
    public static function getExcludedStateIds()
    {
        $states = self::getConfiguredStates();
        return array(
            $states['canceled'],
            $states['payment_error']
        );
    }

    /**
     * Get state IDs to exclude from Sales, SBPM, Tax, and Summary queries.
     * Excludes:
     *   - Canceled / Payment error: no valid sale
     *   - Partial refund (25): state-25 rows are refund records (same reference, new order row);
     *     they are counted via possible_refund_date and deducted from totals, not as sales.
     * Full refunds (7, 56) use possible_refund_date conditional exclusion.
     * @return array
     */
    public static function getSalesExcludedStateIds()
    {
        $states = self::getConfiguredStates();
        $ids = array(
            (int)$states['canceled'],
            (int)$states['payment_error'],
            (int)$states['partial_refund']
        );
        // Remove zeros (invalid) and duplicates
        $ids = array_values(array_unique(array_filter($ids, function ($v) { return $v > 0; })));
        // Hardcoded fallback: never return empty (would break SQL NOT IN ())
        if (empty($ids)) {
            $ids = array(
                (int)self::DEFAULT_STATE_CANCELED,
                (int)self::DEFAULT_STATE_PAYMENT_ERROR,
                (int)self::DEFAULT_STATE_PARTIAL_REFUND
            );
        }
        return $ids;
    }

    /**
     * Get the excluded states as a ready-to-use SQL string for NOT IN clauses.
     * Use this everywhere instead of building the string manually.
     * @return string e.g. "6,8,56,7,25"
     */
    public static function getSalesExcludedStatesSQL()
    {
        return implode(',', self::getSalesExcludedStateIds());
    }
    
    /**
     * Get FULL refund state IDs (refunded, refunded_old). NOT partial refund.
     * Used for possible_refund_date conditional exclusion logic:
     * orders in these states are excluded from payment amounts ONLY when
     * both date_add and possible_refund_date fall within the reporting period.
     * Partial refund (25) is excluded via getSalesExcludedStateIds() instead.
     * @return string comma-separated state IDs for SQL IN clause
     */

    public static function getRefundStatesSQL()
    {
        $states = self::getConfiguredStates();
        $ids = array_filter(array(
            (int)$states['refunded'],
            (int)$states['refunded_old']
        ), function ($v) { return $v > 0; });
        $ids = array_values(array_unique($ids));
        if (empty($ids)) {
            $ids = array(
                (int)self::DEFAULT_STATE_REFUNDED,
                (int)self::DEFAULT_STATE_REFUNDED_OLD
            );
        }
        return implode(',', $ids);
    }
    

    /**
     * Parse comma-separated string into array of trimmed values
     * @param string $str
     * @return array
     */
    protected static function parseCommaSeparated($str)
    {
        if (empty($str)) {
            return array();
        }
        $values = explode(',', $str);
        $result = array();
        foreach ($values as $val) {
            $trimmed = trim($val);
            if (!empty($trimmed)) {
                $result[] = $trimmed;
            }
        }
        return $result;
    }
    

    /**
     * Get merged and deduplicated payment method patterns
     * Combines user-defined patterns with defaults, removes duplicates (case-insensitive)
     * 
     * @param string $configKey Configuration key (e.g., 'KHEWA_PM_CREDIT_CARD')
     * @param string $defaultValue Default constant value
     * @return array Array of unique patterns
     */
    protected static function getMergedPatterns($configKey, $defaultValue)
    {
        // Get user-defined patterns
        $userPatterns = self::parseCommaSeparated(Configuration::get($configKey));
        
        // Get default patterns
        $defaultPatterns = self::parseCommaSeparated($defaultValue);
        
        // Merge: user patterns first, then defaults
        $merged = array_merge($userPatterns, $defaultPatterns);
        
        // Deduplicate (case-insensitive)
        $seen = array();
        $result = array();
        foreach ($merged as $pattern) {
            $lower = strtolower($pattern);
            if (!isset($seen[$lower])) {
                $seen[$lower] = true;
                $result[] = $pattern;
            }
        }
        
        return $result;
    }
    
    /**
     * Get all configured payment method patterns
     * @return array Associative array with all pattern categories
     */
    public static function getPaymentMethodPatterns()
    {
        return array(
            'credit_card' => self::getMergedPatterns('KHEWA_PM_CREDIT_CARD', self::DEFAULT_PM_CREDIT_CARD),
            'cash' => self::getMergedPatterns('KHEWA_PM_CASH', self::DEFAULT_PM_CASH),
            'interac' => self::getMergedPatterns('KHEWA_PM_INTERAC', self::DEFAULT_PM_INTERAC),
            'gift_card' => self::getMergedPatterns('KHEWA_PM_GIFT_CARD', self::DEFAULT_PM_GIFT_CARD),
            'voucher' => self::getMergedPatterns('KHEWA_PM_VOUCHER', self::DEFAULT_PM_VOUCHER),
            'credit_slip' => self::getMergedPatterns('KHEWA_PM_CREDIT_SLIP', self::DEFAULT_PM_CREDIT_SLIP),
            'pos_module' => self::getMergedPatterns('KHEWA_PM_POS_MODULE', self::DEFAULT_PM_POS_MODULE),
        );
    }
    
    /**
     * Build SQL LIKE conditions for a pattern category
     * @param array $patterns Array of pattern strings
     * @param string $columnName SQL column name (e.g., 'op.payment_method')
     * @return string SQL condition (e.g., "(col LIKE '%X%' OR col LIKE '%Y%')")
     */
    public static function buildLikeCondition($patterns, $columnName)
    {
        if (empty($patterns)) {
            return '1=0'; // No patterns = never match
        }
        
        $conditions = array();
        foreach ($patterns as $pattern) {
            $escaped = pSQL($pattern);
            $conditions[] = $columnName . ' LIKE "%' . $escaped . '%"';
        }
        
        return '(' . implode(' OR ', $conditions) . ')';
    }
    
    /**
     * Build SQL CASE statement for normalizing payment methods
     * @param string $columnName SQL column name (e.g., 'op.payment_method')
     * @return string SQL CASE statement
     */
    public static function buildPaymentMethodCase($columnName)
    {
        $patterns = self::getPaymentMethodPatterns();
        
        $case = 'CASE' . "\n";
        
        // Stripe Payment Link (Link via Stripe) - must be before generic Stripe
        // Matches: "Link via Stripe", "Stripe Payment Pro", "stripe_official"
        $case .= '    WHEN (LOWER(' . $columnName . ') LIKE "%link via stripe%" OR LOWER(' . $columnName . ') LIKE "%stripe payment pro%" OR LOWER(' . $columnName . ') LIKE "%stripe_official%") THEN "Link via Stripe"' . "\n";
        
        // Stripe Card (Card via Stripe) - generic Stripe payments
        // Matches: "Card via Stripe", "Payment by Stripe", or any other "stripe" reference
        $case .= '    WHEN (LOWER(' . $columnName . ') LIKE "%stripe%" OR LOWER(' . $columnName . ') LIKE "%payment by stripe%" OR LOWER(' . $columnName . ') LIKE "%card via stripe%") THEN "Card via Stripe"' . "\n";
        
        // PayPal
        $case .= '    WHEN (LOWER(' . $columnName . ') LIKE "%paypal%" OR LOWER(' . $columnName . ') LIKE "%pay pal%") THEN "PayPal"' . "\n";
        
        // Credit Card
        if (!empty($patterns['credit_card'])) {
            $case .= '    WHEN ' . self::buildLikeCondition($patterns['credit_card'], $columnName) . ' THEN "Credit Card"' . "\n";
        }
        
        // Cash
        if (!empty($patterns['cash'])) {
            $case .= '    WHEN ' . self::buildLikeCondition($patterns['cash'], $columnName) . ' THEN "Cash"' . "\n";
        }
        
        // Interac
        if (!empty($patterns['interac'])) {
            $case .= '    WHEN ' . self::buildLikeCondition($patterns['interac'], $columnName) . ' THEN "Interac"' . "\n";
        }
        
        // Gift Card
        if (!empty($patterns['gift_card'])) {
            $case .= '    WHEN ' . self::buildLikeCondition($patterns['gift_card'], $columnName) . ' THEN "Gift card"' . "\n";
        }
        
        // Voucher
        if (!empty($patterns['voucher'])) {
            $case .= '    WHEN ' . self::buildLikeCondition($patterns['voucher'], $columnName) . ' THEN "Voucher"' . "\n";
        }
        
        // Credit Slip
        if (!empty($patterns['credit_slip'])) {
            $case .= '    WHEN ' . self::buildLikeCondition($patterns['credit_slip'], $columnName) . ' THEN "Credit Slip"' . "\n";
        }
        
        $case .= '    ELSE TRIM(' . $columnName . ')' . "\n";
        $case .= 'END';
        
        return $case;
    }
    
    /**
     * Normalize a payment method string to the same keys used in SBPM (e.g. Credit Card, Cash, Interac).
     * Used when attributing partial refund orders to payment method buckets.
     * @param string $paymentMethod Raw payment method name from order.payment or order_payment
     * @return string Normalized key (e.g. "Credit Card", "Cash", "Interac", "PayPal", "Card via Stripe", "Link via Stripe", or trimmed original)
     */
    public static function normalizePaymentMethod($paymentMethod)
    {
        if ($paymentMethod === null || $paymentMethod === '') {
            return 'Other';
        }
        $p = strtolower(trim((string)$paymentMethod));
        if ($p === '') {
            return 'Other';
        }
        if (strpos($p, 'link via stripe') !== false || strpos($p, 'stripe payment pro') !== false || strpos($p, 'stripe_official') !== false) {
            return 'Link via Stripe';
        }
        if (strpos($p, 'stripe') !== false || strpos($p, 'payment by stripe') !== false || strpos($p, 'card via stripe') !== false) {
            return 'Card via Stripe';
        }
        if (strpos($p, 'paypal') !== false || strpos($p, 'pay pal') !== false) {
            return 'PayPal';
        }
        $patterns = self::getPaymentMethodPatterns();
        foreach (array('credit_card' => 'Credit Card', 'cash' => 'Cash', 'interac' => 'Interac') as $key => $label) {
            if (!empty($patterns[$key])) {
                foreach ($patterns[$key] as $pattern) {
                    if (stripos($p, $pattern) !== false) {
                        return $label;
                    }
                }
            }
        }
        return trim($paymentMethod);
    }


    /**
     * Build SQL condition for POS (in-store) module detection
     * @param string $columnName SQL column name (e.g., 'o.module')
     * @return string SQL condition
     */
    public static function buildPosModuleCondition($columnName)
    {
        $patterns = self::getPaymentMethodPatterns();
        
        if (empty($patterns['pos_module'])) {
            return $columnName . ' = "hspointofsalepro"'; // Fallback
        }
        
        $conditions = array();
        foreach ($patterns['pos_module'] as $module) {
            $conditions[] = $columnName . ' = "' . pSQL($module) . '"';
        }
        
        return '(' . implode(' OR ', $conditions) . ')';
    }
}


