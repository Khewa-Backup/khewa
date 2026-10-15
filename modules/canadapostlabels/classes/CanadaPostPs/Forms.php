<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

namespace CanadaPostPs;

use BoxPacker\PackedBox;
use \Configuration;
use \AdminController;
use \Context;
use \Country;
use \HelperForm;
use \HelperOptions;
use \HelperList;
use \Language;
use \ObjectModel;
use \Order;
use \State;
use \Validate;
use Symfony\Bundle\SecurityBundle\Tests\Functional\Bundle\AclBundle\Entity\Car;
use Symfony\Component\Validator\Constraints\Valid;

class Forms extends \CanadaPostLabels
{
    public $tabs;

    public static $preferencesValues = array(
        'MODE',
        'SAVE_INFO',
        'LOGGING',
        'ACCOUNT_TYPE',
        'DEV_API_USER',
        'DEV_API_PASS',
        'PROD_API_USER',
        'PROD_API_PASS',
        'CUSTOMER_NUMBER',
        'PLATFORM_ID',
        'CONTRACT',
        'DOWNLOAD_ID',
        'TOKEN_REQUEST',
        'CREDIT',
        'VS',
        'VE',
    );

    public static $ratesValues = array(
        'RATE_SIGNATURE',
        'DELAY',
        'CARRIER_IMAGE',
        'CARRIER_LOGO_FILE',
        'DEFAULT_WEIGHT',
        'MAX_BOXES',
        'DELAY',
        'ESTIMATES',
        'CONTENTS_VALUE',
        'TAX',
        'SPLIT_TYPE',
        'ESTIMATOR',
    );

    public static $labelDefaultValues = array(
        'REQUESTED_SHIPPING_POINT',
        'SHIPPING_POINT',
        'PICKUP',
        'UPDATE_TRACKING_NUMBER',
        'UPDATE_ORDER_STATUS',
        'ORDER_STATUS',
        'SEND_IN_TRANSIT_EMAIL',
        'ORDER_ID_REFERENCE',
        'REFUND_EMAIL',
        'OPEN_LABEL_ON_CREATION',
        'INCLUDE_RETURN_LABEL',
        'INCLUDE_INVOICE',
        'box',
        'group-id',
        'sender',
        'options',
        'non_delivery_options',
        'COV-option-amount',
        'COD-option-amount',
        'COD-option-qualifier-1',
        'notification',
        'show-packing-instructions',
        'show-postage-rate',
        'show-insured-value',
        'cost-centre',
        'unpackaged',
        'oversized',
        'mailing-tube',
        'output-format',
        'intended-method-of-payment',
        'reason-for-export',
        'other-reason',
        'certificate-number',
        'licence-number',
        'invoice-number',
        'return-spec',
        'return-recipient',
        'return-service-code',
        'returner',
        'receiver',
    );

    public static $bulkLabelDefaultValues = array(
        'LABELS_ORDER_BY',
        'LABELS_ORDER_WAY',
        'LABEL_DELAY',
    );

    public static $trackingDefaultValues = array(
        'ENABLE_FRONT_TRACKING',
        'ENABLE_DELIVERY_UPDATE',
        'DELIVERED_ORDER_STATUS',
    );

    public static $multiSelectValues = array(
        'EXCLUDE_ORDER_STATUSES',
        'TRACK_ORDER_STATUSES',
    );

    public function __construct()
    {
        $this->tabs = $this->getConfigTabs();
        parent::__construct();
    }

    /**
     * Dynamically generate HTML form
     *
     * @param array $fields
     * @param string $form
     * @param bool $object
     * @param string|bool $action
     * @param string|bool $token
     * @param array|bool $values
     *
     * @return string html
     * */
    public function renderForm($fields, $form_id, $object = false, $action = false, $token = true, $values = false)
    {
        $helper                           = new HelperForm();

        // Use appropriate submit action if form is config or object
        $submitAction = 'submit' . \Tools::ucfirst($form_id);
        if ($object) {
            $obj          = $this->getNamespace() . \Tools::ucfirst($form_id);
            $submitAction = 'save' . _DB_PREFIX_ . $obj::$definition['table'];
            $helper->table = $obj::$definition['table'];
            $helper->identifier = $obj::$definition['primary'];

            if (Tools::getIsset($obj::$definition['primary'])) {
                $helper->id = Tools::getValue($obj::$definition['primary']);
            }
        }

        $formTabs = array();
        // Merge tabs from each form to JS formTabs variable
        if (isset($fields['form']['form_tabs'])) {
            $jsDef = \Media::getJsDef();
            if (array_key_exists('formTabs', $jsDef)) {
                $formTabs = $jsDef['formTabs'];
            }
            $formTabs = array_merge($formTabs, $fields['form']['form_tabs']);

            \Media::addJsDef(array(
                'formTabs' => $formTabs
            ));
        }

        $helper->show_toolbar             = false;
        $lang                             = new Language((int)\Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language    = $lang->id;
        $helper->module                   = $this;
        $helper->allow_employee_form_lang = \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->submit_action            = $submitAction;
        $helper->currentIndex             = $action ? $action : $this->getAdminLink(
            'AdminModules',
            false,
            '',
            array(
                    'configure' => $this->name,
                    'tab_module' => $this->tab,
                    'module_name' => $this->name
                )
            );
        $helper->token                    = $token ? Tools::getAdminTokenLite($this->context->controller->controller_name) : false;
        $fullBaseFolder = _PS_BO_ALL_THEMES_DIR_
                          . $this->context->employee->bo_theme . DIRECTORY_SEPARATOR
                          . 'template' . DIRECTORY_SEPARATOR
                          . 'helpers' . DIRECTORY_SEPARATOR
                          . 'form' . DIRECTORY_SEPARATOR;
        $helper->tpl_vars                 = array(
            'uri'          => $this->getPathUri(),
            'fields_value' => $values ? $values : $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
            'extendFormFile' => $fullBaseFolder . 'form.tpl',
            'formTabs' => $formTabs
        );

        if ($object && \Shop::isFeatureActive() && \Shop::isTableAssociated($obj::$definition['table'])) {
            $helper->tpl_vars['asso_shop'] = $helper->renderAssoShop();
        }

        return $helper->generateForm(array($fields));
    }

    /* Display Disconnect button */
    public function renderAccountForm()
    {
        $form = 'account';

        $fields = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->tabs[$form]['title'],
                    'icon'  => $this->tabs[$form]['icon'],
                ),
            ),
        );

        if (!$this->isConnected()) {

            // We load a custom template file as Canada Post doesn't accept the form created by HelperForm
            $values = $this->getConfigFieldsValues();
            $this->context->smarty->assign(array(
                'return_url'  => $values['return-url'],
                'token_id'    => $values['token-id'],
                'platform_id' => $values['platform-id'],
                'title'       => $this->tabs[$form]['title'],
                'icon'        => $this->tabs[$form]['icon'],
                'logo'        => $this->getPathUri() . 'views/img/logo_icon.png',
                'token_error' => $this->context->cookie->__isset('canadapost_token') ? false : $this->l(Tools::$error_messages['TOKEN_MISSING']),
            ));

            return $this->context->smarty->fetch(_PS_MODULE_DIR_. $this->name . '/views/templates/hook/connect.tpl');
        } else {
            $account_status = \CanadaPostPs\Tools::renderHtmlTag(
                'label',
                'Connected ' . \CanadaPostPs\Tools::renderHtmlTag('i', '', array('class' =>'icon-check')),
                array('class' => 'control-label account-connected', 'style' => 'color: green;')
            );

            $fields['form']['description'] = $this->l('If this is your first time creating a Canada Post account and/or adding a new credit card to your Canada Post profile, please allow up to 24 hours for your information to process before using this module. You may encounter errors during that time.');
            $fields['form']['buttons']     = array(
                array(
                    'type'  => 'submit',
                    'title' => $this->l('Disconnect Account'),
                    'name'  => 'submitDisconnect',
                    'icon'  => 'icon-unlink',
                ),
            );
        }

        $fields['form']['input'][] = array(
            'type'         => 'html',
            'label'        => $this->l('Account Status'),
            'html_content' => $account_status,
            'name'         => 'status',
            'class'        => 'fixed-width-xxl',
        );
        $fields['form']['input'][] = array(
            'type'         => 'html',
            'label'        => $this->l('Customer Number'),
            'html_content' => \CanadaPostPs\Tools::renderHtmlTag(
                'label',
                ($this->isConnected() ? self::getConfig('CUSTOMER_NUMBER') : ''),
                array('class' => 'control-label account-connected')
            ),
            'name'         => 'api_username',
            'class'        => 'fixed-width-xxl',
        );
        if (self::getConfig('CONTRACT')) {
            $fields['form']['input'][] = array(
                'type'         => 'html',
                'label'        => $this->l('Contract Number'),
                'html_content' => \CanadaPostPs\Tools::renderHtmlTag(
                    'label',
                    ($this->isConnected() ? self::getConfig('CONTRACT') : ''),
                    array('class' => 'control-label account-connected')
                ),
                'name'         => 'api_password',
                'class'        => 'fixed-width-xxl',
            );
        }
        $fields['form']['input'][] = array(
            'type'         => 'html',
            'label'        => $this->l('API Username'),
            'html_content' => \CanadaPostPs\Tools::renderHtmlTag(
                'label',
                ($this->isConnected() ? self::getConfig('PROD_API_USER') : ''),
                array('class' => 'control-label account-connected')
            ),
            'name'         => 'api_username',
            'class'        => 'fixed-width-xxl',
        );
        $fields['form']['input'][] = array(
            'type'         => 'html',
            'label'        => $this->l('API Password'),
            'html_content' => \CanadaPostPs\Tools::renderHtmlTag(
                'label',
                ($this->isConnected() ? self::getConfig('PROD_API_PASS') : ''),
                array('class' => 'control-label account-connected')
            ),
            'name'         => 'api_password',
            'class'        => 'fixed-width-xxl',
        );

        return $this->renderForm($fields, $this->tabs[$form]['id'], false);
    }

    /* Display preferences */
    public function renderPreferencesForm()
    {
        $form = 'preferences';

        $fields = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->tabs[$form]['title'],
                    'icon'  => $this->tabs[$form]['icon'],
                ),
                'input'  => array(
                    array(
                        'type'   => 'switch',
                        'label'  => $this->l('Live Mode'),
                        'name'   => self::PREFIX . 'MODE',
                        'desc'   => $this->l('"Yes" to process real shipments, "No" for test shipments.'),
                        'class'  => 't',
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type'   => 'radio',
                        'label'  => $this->l('Canada Post Account Type'),
                        'desc'   => $this->l('Choose your Canada Post account type to view features related to that account type (e.g. Manifests, 4x6 labels). This setting is automatically set for you depending on whether your Canada Post account has a "Contract Number" or not. If your account type gets upgraded to Commercial Contract and you are experiencing issues, it is recommended to Disconnect & Reconnect your account in the Account tab. NOTE: If you are a non-contract Canada Post customer and wish to use 4x6 labels, you can change this setting to "Commercial Contract", make sure to change your default label size to 4x6 in the "Labels" preferences.'),
                        'name'   => self::PREFIX . 'ACCOUNT_TYPE',
                        'class'  => 't',
                        'values' => array(
                            array(
                                'id'    => 'REGULAR',
                                'value' => 1,
                                'label' => $this->l('Regular/Solutions for Small Businesses')
                            ),
                            array(
                                'id'    => 'CONTRACT',
                                'value' => 2,
                                'label' => $this->l('Commercial Contract')
                            ),
                        ),
                    ),
                    array(
                        'type'   => 'switch',
                        'label'  => $this->l('Save info on uninstall'),
                        'name'   => self::PREFIX . 'SAVE_INFO',
                        'desc'   => $this->l('Saves your settings. If on, your addresses, carriers and boxes will still be here when you reinstall.'),
                        'class'  => 't',
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type'   => 'switch',
                        'label'  => $this->l('Error Logging'),
                        'name'   => self::PREFIX . 'LOGGING',
                        'desc'   => $this->l('When enabled, the module will log errors in the Prestashop logs (Advanced Parameters > Logs). Useful for debugging and finding out why rates are not showing up.'),
                        'class'  => 't',
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type'         => 'html',
                        'label'        => $this->l('Measurement Units'),
                        'html_content' => \Configuration::get('PS_DIMENSION_UNIT') . ', ' . \Configuration::get('PS_WEIGHT_UNIT'),
                        'name'         => 'measurement',
                        'class'        => 'fixed-width-xxl',
                        'desc'         => $this->l('Your store\'s units of measurement can be changed in the "Localization" menu. If you change your units, you will have to convert the dimensions on your boxes and products to the new units.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        return $this->renderForm($fields, $this->tabs[$form]['id']);
    }

    public function renderRatesForm()
    {
        $form = 'rates';

        $maxBoxesSelect = array();
        for ($i = 1; $i <= 25; $i++) {
            $maxBoxesSelect[] = array(
                'id'   => $i,
                'name' => $i . ' ' . ($i > 1 ? $this->l('boxes') : $this->l('box')),
            );
        }
        $delaysSelect = array();
        for ($i = 0; $i <= 29; $i++) {
            $delaysSelect[] = array(
                'id'   => $i,
                'name' => $i . ' ' . ($i == 1 ? $this->l('day') : $this->l('days')),
            );
        }
        $fields = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->tabs[$form]['title'],
                    'icon'  => $this->tabs[$form]['icon'],
                ),
                'input'  => array(
                    array(
                        'type'   => 'checkbox',
                        'label'  => $this->l('Canada Methods'),
                        'name'   => self::PREFIX . 'METHODS',
                        'values' => array(
                            'query' => Method::getMethods(array('group' => 'DOM')),
                            'id'    => 'code',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'   => 'checkbox',
                        'label'  => $this->l('USA Methods'),
                        'name'   => self::PREFIX . 'METHODS',
                        'values' => array(
                            'query' => Method::getMethods(array('group' => 'USA')),
                            'id'    => 'code',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'   => 'checkbox',
                        'label'  => $this->l('International Methods'),
                        'name'   => self::PREFIX . 'METHODS',
                        'values' => array(
                            'query' => Method::getMethods(array('group' => 'INT')),
                            'id'    => 'code',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'   => 'switch',
                        'label'  => $this->l('Tax Included'),
                        'desc'   => $this->l('Get rates from Canada Post with/without taxes. You can add your own tax rates to the carriers in the Shipping > Carriers menu.'),
                        'name'   => self::PREFIX . 'TAX',
                        'class'  => 't',
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type'   => 'switch',
                        'label'  => $this->l('Enable Shipping Estimator'),
                        'desc'   => $this->l('Allow customers to estimate shipping costs on the product and cart pages.'),
                        'name'   => self::PREFIX . 'ESTIMATOR',
                        'class'  => 't',
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type'   => 'switch',
                        'label'  => $this->l('Show Carrier Logo'),
                        'desc'   => $this->l('Display the Canada Post logo next to each Canada Post carrier on the cart page.'),
                        'name'   => self::PREFIX . 'CARRIER_IMAGE',
                        'class'  => 't',
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type'  => 'file',
                        'label' => $this->l('Carrier Logo'),
                        'name'  => self::PREFIX . 'CARRIER_LOGO_FILE',
                        'desc'  => $this->l('Upload a logo for your Canada Post carriers. The recommended dimensions are 40px x 40px if you are using the default theme. You may need to clear your browser cache to view the newly uploaded image.'),
                        'lang'  => false,
                    ),
                    array(
                        'type'   => 'radio',
                        'label'  => $this->l('Multiple Box Rates'),
                        'desc'   => $this->l('Choose how the module calculates rates for multiple boxes when the cart contains more products than your largest box can fit.') . \CanadaPostPs\Tools::renderHtmlTag('br').\CanadaPostPs\Tools::renderHtmlTag('br') .
                                    $this->l('Off: Disables multiple boxes and only uses 1 box.') . \CanadaPostPs\Tools::renderHtmlTag('br').\CanadaPostPs\Tools::renderHtmlTag('br') .
                                    $this->l('Simple & Fast: Finds the rate for the LARGEST required box and MULTIPLIES the rate by the amount of boxes needed. e.g. For 3 boxes: $10 * 3 boxes = $30 Shipping Fee. This method only calls the Canada Post API once to get the rates for the cart.') . \CanadaPostPs\Tools::renderHtmlTag('br').\CanadaPostPs\Tools::renderHtmlTag('br') .
                                    $this->l('Accurate & Slower: Finds the rate for EVERY box required to fit all the products in the cart and SUMS all the rates together. e.g. For 3 boxes: $10 + $11 + $12 = $33 Shipping Fee. This method will call the Canada Post API once for each box that the cart requires. Carts requiring many boxes may have a longer loading time when retrieving rates. If your store has a lot of traffic, you may reach your Canada Post API throttle limit which results in an API timeout for 60 seconds. You can request an increased API limit from Canada Post - more info on the throttle limit can be found here: ') . \CanadaPostPs\Tools::renderHtmlLink('https://www.canadapost.ca/cpo/mc/business/productsservices/developers/throttlelimits.jsf', null, array('target' => '_blank')),
                        'name'   => self::PREFIX . 'SPLIT_TYPE',
                        'class'  => 't',
                        'values' => array(
                            array(
                                'id'    => 'OFF',
                                'value' => 1,
                                'label' => $this->l('Off')
                            ),
                            array(
                                'id'    => 'SIMPLE',
                                'value' => 2,
                                'label' => $this->l('Simple & Fast (recommended)')
                            ),
                            array(
                                'id'    => 'COMPLEX',
                                'value' => 3,
                                'label' => $this->l('Accurate & Slower')
                            )
                        ),
                    ),
                    array(
                        'type'    => 'select',
                        'label'   => $this->l('Box Packing Limit'),
                        'hint'    => $this->l('Select 1 for standard Canada Post rates without product splitting.'),
                        'desc'    => $this->l('Choose the max amount of boxes to split products between when the cart contains more products than your largest box can fit. Select 1 for standard Canada Post rates without product splitting.') . \CanadaPostPs\Tools::renderHtmlTag('br').\CanadaPostPs\Tools::renderHtmlTag('br') .
                                     $this->l('The module uses a sophisticated box packing algorithm to determine the smallest box(es) that will fit all the products in the customer\'s cart; it can SPLIT the products into multiple boxes when the cart has more products than your largest box can fit. The Canada Post weight limit per box is 30kg and the module will spread weight across multiple boxes if required.') . \CanadaPostPs\Tools::renderHtmlTag('br').\CanadaPostPs\Tools::renderHtmlTag('br') .
                                     $this->l('IMPORTANT: If you set "Multiple Box Rates" to "Accurate & Slower", the module retrieves rates from Canada Post for each individual box the module used; that means that the Canada Post API is called once for each box, which may result in your API account reaching its limit and being timed out for a minute. Carts that require many boxes may experience a slower loading time when getting rates.') . \CanadaPostPs\Tools::renderHtmlTag('br').\CanadaPostPs\Tools::renderHtmlTag('br') .
                                     $this->l('If the cart requires more boxes than the maximum allowed, the module will use boxes that can fit the largest products in the cart until it reaches the maximum. Limit is 25.'),
                        'name'    => self::PREFIX . 'MAX_BOXES',
                        'options' => array(
                            'id'    => 'id',
                            'name'  => 'name',
                            'query' => $maxBoxesSelect
                        )
                    ),
                    array(
                        'type'    => 'select',
                        'label'   => $this->l('Delivery Delay'),
                        'desc'    => $this->l('Amount of days to add to the ship date for delivery date estimates. e.g. Enter 3 if you ship your orders 3 days after they are placed; this will increase the displayed delivery estimate by 3 days. Leave as 0 to use the current date.'),
                        'class'   => 'fixed-width-xs',
                        'suffix'  => 'days',
                        'name'    => self::PREFIX . 'DELAY',
                        'options' => array(
                            'id'    => 'id',
                            'name'  => 'name',
                            'query' => $delaysSelect
                        )
                    ),
                    array(
                        'type'   => 'switch',
                        'label'  => $this->l('Show Delivery Time Estimates'),
                        'desc'   => $this->l('e.g. "2 Business Days". Showing estimates on the checkout page requires adding one line of code to your theme. Please follow the module instructions to add the code.'),
                        'name'   => self::PREFIX . 'ESTIMATES',
                        'class'  => 't',
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name'  => 'submitUpdateMethods',
                ),
            ),
        );

        return $this->renderForm($fields, 'rates');
    }

    public function renderOrderTrackingForm()
    {
        $form = 'ordertracking';

        $orderStatuses = \OrderState::getOrderStates($this->context->language->id);

        $fields = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->tabs[$form]['title'],
                    'icon'  => $this->tabs[$form]['icon'],
                ),
                'description' => $this->l('The "Auto-Update Order Status When Delivered" feature requires that you have the free "Cron tasks manager" module made by PrestaShop installed and enabled, and a cron job setup to run hourly. Read how to set up the cron job in the documentation:') . \CanadaPostPs\Tools::renderHtmlLink(_MODULE_DIR_ . $this->name . '/Readme.html#setting-up-cron-job', 'Setting Up Cron Job', array('target' => '_blank')) . \CanadaPostPs\Tools::renderHtmlTag('br').\CanadaPostPs\Tools::renderHtmlTag('br') .$this->l('For PrestaShop versions 1.7.0.0 to 1.7.0.5: A theme modification may be required to make this feature work due to a bug in the default theme. See the documentation: ') . \CanadaPostPs\Tools::renderHtmlLink(_MODULE_DIR_ . $this->name . '/Readme.html#tracking', 'Tracking', array('target' => '_blank')),
                'input'  => array(
                    array(
                        'type'   => 'switch',
                        'label'  => $this->l('Enable Order Tracking'),
                        'desc'   => $this->l('Choose to allow customers to see the tracking progress for their order on the Order Details page.'),
                        'name'   => self::PREFIX . 'ENABLE_FRONT_TRACKING',
                        'class'  => 't',
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type'   => 'switch',
                        'label'  => $this->l('Auto-Update Order Status When Delivered'),
                        'desc'   => $this->l('Automatically update an order to a custom status when the Canada Post tracking says it has been delivered. Orders may have multiple shipments and only the most recent shipment will be tracked/updated.'),
                        'name'   => self::PREFIX . 'ENABLE_DELIVERY_UPDATE',
                        'class'  => 't',
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type'   => 'hidden',
                        'name'   => self::PREFIX . 'TRACK_ORDER_STATUSES_FIELD',
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Order Statuses to Track'),
                        'name'     => self::PREFIX . 'TRACK_ORDER_STATUSES',
                        'desc' => $this->l('Choose multiple statuses to track and automatically update when delivered. Hold Ctrl/Cmd + Click to select or deselect multiple.'),
                        'multiple' => true,
                        'size' => count($orderStatuses),
                        'options'  => array(
                            'id'    => 'id_order_state',
                            'name'  => 'name',
                            'query' => $orderStatuses,
                        ),
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Delivered Order Status'),
                        'name'     => self::PREFIX . 'DELIVERED_ORDER_STATUS',
                        'desc' => $this->l('Choose which status to update an order with once it has been tracked as delivered. This status cannot be included in the "Order Statuses to Track" setting.'),
                        'options'  => array(
                            'id'    => 'id_order_state',
                            'name'  => 'name',
                            'query' => $orderStatuses,
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name'  => 'submitUpdateTracking',
                ),
            ),
        );

        return $this->renderForm($fields, $form);
    }

    public function renderLabelsForm()
    {
        $form = 'labels';

        $fields = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->tabs[$form]['title'],
                    'icon'  => $this->tabs[$form]['icon']
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name'  => 'submitUpdateLabels',
                )
            ),
        );

        $divider = array(
            array(
                'type'         => 'html',
                'label'        => sprintf('%s', \CanadaPostPs\Tools::renderHtmlTag('b', $this->l('Default Label Values'))),
                'html_content' => \CanadaPostPs\Tools::renderHtmlTag(
                    'label',
                    $this->l('Select default values to pre-populate the "Create Label" form.'),
                    array('class' => 'control-label')
                ),
                'name'         => 'divider',
            )
        );

        // Reuse fields from Create Label form
        $labelFields = array_merge(
            $this->getParcelFields('parcel'),
            $this->getAddressFields('address'),
            $this->getOptionsFields('options'),
            $this->getPreferencesFields('preferences', true),
            $this->getCustomsFields('customs'),
            $this->getReturnFields('return')
        );

        $configValues = $this->getConfigFieldsValues();

        // Add module PREFIX to field names and remove 'tab' value from arrays
        foreach ($labelFields as $key => $field) {
            // Only show fields that will be saved in the configuration DB
            if (!in_array(self::PREFIX.$field['name'], array_keys($configValues))) {
                unset($labelFields[$key]);
            } else {
                $labelFields[$key]['name'] = self::PREFIX.$labelFields[$key]['name'];
                $labelFields[$key]['label'] =  sprintf('%s "%s"', $this->l('Default'), $labelFields[$key]['label']);
                unset($labelFields[$key]['tab']);

                // Prepend "None" value to all select fields
                if ($labelFields[$key]['type'] == 'select') {
                    $none = array(
                        $labelFields[$key]['options']['id'] => 0,
                        $labelFields[$key]['options']['name'] => $this->l('None')
                    );
                    array_unshift($labelFields[$key]['options']['query'], $none);
                }

                if ($labelFields[$key]['name'] == self::PREFIX.'box') {
                    $labelFields[$key]['desc'] = $this->l('Leave as "None" to let the module pre-populate the Box field using a "Box Packing" algorithm based on the products contained in the order.');
                }
            }
        }

        $contractFields = array();
        if (self::getConfig('CONTRACT')) {
            $contractFields = array(
                array(
                    'type'   => 'switch',
                    'label'  => $this->l('Include Return Labels in Label PDF'),
                    'desc'   => $this->l('Choose to include the return label in the shipping label PDF for an order when a return label was created at label creation. This is only for return labels included in the original shipping label and not for return labels created separately.'),
                    'name'   => self::PREFIX . 'INCLUDE_RETURN_LABEL',
                    'class'  => 't',
                    'values' => array(
                        array(
                            'id'    => 'active_on',
                            'value' => true,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id'    => 'active_off',
                            'value' => false,
                            'label' => $this->l('No')
                        )
                    ),
                ),
            );
        }

        $inputFields = array_merge(
            $this->getLabelDefaultFields(),
            $contractFields,
            $divider,
            $labelFields
        );

        $fields['form']['input'] = $inputFields;

        return $this->renderForm($fields, $this->tabs[$form]['id']);
    }

    public function renderBulkLabelsForm()
    {
        $form = 'bulklabels';

        $orderStatuses = \OrderState::getOrderStates($this->context->language->id);

        $labelSortByOptions = array();
        $sortByOptions = array(
            'id_order' => 'Order ID',
            'shipment_date_add' => 'Shipping Date',
            'order_date_add' => 'Order Date',
        );
        foreach ($sortByOptions as $id => $sortOption) {
            $labelSortByOptions[] = array(
                'id'    => $id,
                'label' => $sortOption,
                'value' => $id,
            );
        }

        $labelSortWayOptions = array();
        $sortWayOptions = array(
            'ASC' => 'Ascending',
            'DESC' => 'Descending',
        );
        foreach ($sortWayOptions as $id => $sortOption) {
            $labelSortWayOptions[] = array(
                'id'    => $id,
                'label' => $sortOption,
                'value' => $id,
            );
        }

        $delays = array();
        for ($i = 0; $i <= 5000000; $i += 250000) {
            $delays[] = array(
                'id' => $i,
                'name' => number_format($i/1000000, 2, '.', '') . ' ' . $this->l('seconds'),
            );
        }

        $fields = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->tabs[$form]['title'],
                    'icon'  => $this->tabs[$form]['icon']
                ),
                'input' => array(
                    array(
                        'type'   => 'hidden',
                        'name'   => self::PREFIX . 'EXCLUDE_ORDER_STATUSES_FIELD',
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Exclude Order Statuses'),
                        'name'     => self::PREFIX . 'EXCLUDE_ORDER_STATUSES',
                        'desc' => $this->l('Choose multiple statuses to exclude from the Bulk Order Labels page. Hold Ctrl/Cmd + Click to select or deselect multiple.'),
                        'multiple' => true,
                        'size' => count($orderStatuses),
                        'options'  => array(
                            'id'    => 'id_order_state',
                            'name'  => 'name',
                            'query' => $orderStatuses,
                        ),
                    ),
                    array(
                        'type'     => 'radio',
                        'label'    => $this->l('Sort Labels By'),
                        'name'     => self::PREFIX . 'LABELS_ORDER_BY',
                        'desc' => $this->l('Choose how to sort batches of labels when printing.'),
                        'values'  => $labelSortByOptions
                    ),
                    array(
                        'type'     => 'radio',
                        'label'    => $this->l('Sort Labels Direction'),
                        'name'     => self::PREFIX . 'LABELS_ORDER_WAY',
                        'desc' => $this->l('Choose to sort labels in ascending or descending order.'),
                        'values'  => $labelSortWayOptions
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Delay Between Labels'),
                        'name'     => self::PREFIX . 'LABEL_DELAY',
                        'desc' => $this->l('Seconds to wait between each label when more than 60 labels are requested to avoid hitting the Canada Post API limit and timing out. Set a longer delay if you are receiving "Rejected by SLM Monitor" errors from Canada Post when creating bulk labels. RECOMMENDED: 1.00s if you have a standard Canada Post API limit and 0.25s if you have an upgraded Canada Post API limit. See the documentation to request an API upgrade.'),
                        'options'  => array(
                            'id'    => 'id',
                            'name'  => 'name',
                            'query' => $delays,
                        ),
                    ),
                    array(
                        'type'     => 'html',
                        'label'    => $this->l('PHP Max Execution Time'),
                        'name'     => 'timeout',
                        'desc' => $this->l('Your server\'s "max_execution_time" determines how long a script is allowed to run. Ask your webhost or developer to raise this value if you are experiencing server timeouts when creating bulk labels.'),
                        'html_content' => \CanadaPostPs\Tools::renderHtmlTag(
                            'label',
                            \CanadaPostPs\Tools::renderHtmlTag('b', sprintf('%s seconds', ini_get('max_execution_time'))),
                            array('class' => 'control-label')
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name'  => 'submitUpdateBulkLabels',
                )
            ),
        );

        return $this->renderForm($fields, $this->tabs[$form]['id']);
    }

    public function renderAddressList()
    {
        $form = 'address';

        $fields_list               = array();
        $fields_list['id_address'] = array(
            'title'   => $this->l('ID'),
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['name']       = array(
            'title'   => $this->l('Name'),
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['address1']   = array(
            'title'   => $this->l('Address Line 1'),
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['address2']   = array(
            'title'   => $this->l('Address Line 2'),
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['company']    = array(
            'title'   => $this->l('Company'),
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['city']       = array(
            'title'   => $this->l('City'),
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['postcode']   = array(
            'title'   => $this->l('Zip/Postal Code'),
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['id_state']   = array(
            'title'   => $this->l('State/Province'),
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['id_country'] = array(
            'title'   => $this->l('Country'),
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['phone']      = array(
            'title'   => $this->l('Phone'),
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['origin']     = array(
            'title'   => $this->l('Origin'),
            'hint'    => $this->l('Select the address origin used for rate calculation.'),
            'align'   => 'center',
            'active'  => 'origin',
            'type'    => 'bool',
            'class'   => 'fixed-width-sm',
            'search'  => false,
            'orderby' => false,
        );

        $addresses = Address::getAddresses();
        foreach ($addresses as $k => $v) {
            $addresses[$k]['id_country'] = \Country::getNameById($this->context->language->id, $v['id_country']);
            $addresses[$k]['id_state']   = \State::getNameById($v['id_state']);
        }

        $helper                     = new HelperList();
        $helper->shopLinkType       = '';
        $helper->simple_header      = false;
        $helper->identifier         = 'id_address';
        $helper->listTotal          = count($addresses);
        $helper->show_toolbar       = true;
        $helper->actions            = array('edit', 'delete');
        $helper->bulk_actions       = array(
            'delete' => array(
                'text'    => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon'    => 'icon-trash'
            )
        );
        $helper->title              = $this->tabs[$form]['title'];
        $helper->table              = _DB_PREFIX_ . Address::$definition['table'];
        $helper->token              = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex       = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&add' . _DB_PREFIX_ . Address::$definition['table'] . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new')
        );

        return $helper->generateList($addresses, $fields_list);
    }

    public function renderAddressForm()
    {
        $form = 'address';

        $fields = array(
            'form' => array(
                'legend'  => array(
                    'title' => $this->tabs[$form]['title'],
                    'icon'  => $this->tabs[$form]['icon'],
                ),
                'input'   => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_address',
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'active',
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Alias'),
                        'class'    => 'fixed-width-lg',
                        'name'     => 'name',
                        'required' => true,
                        'hint'     => array(
                            $this->l('Name to identify the address (e.g. store name).'),
                        )
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Company'),
                        'class' => 'fixed-width-lg',
                        'name'  => 'company',
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Address'),
                        'class'    => 'fixed-width-lg',
                        'name'     => 'address1',
                        'required' => true
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Address (2)'),
                        'class' => 'fixed-width-lg',
                        'name'  => 'address2'
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Zip code'),
                        'class'    => 'fixed-width-lg',
                        'name'     => 'postcode',
                        'required' => true
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('City'),
                        'class'    => 'fixed-width-lg',
                        'name'     => 'city',
                        'required' => true,
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Country'),
                        'name'     => 'id_country',
                        'required' => true,
                        'options'  => array(
                            'id'    => 'id_country',
                            'name'  => 'name',
                            'query' => array(
                                array(
                                    'id_country' => \Country::getByIso('CA'),
                                    'name'       => \Country::getNameById(
                                        $this->context->language->id,
                                        \Country::getByIso('CA')
                                    )
                                )
                            )
                        )
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('State'),
                        'name'     => 'id_state',
                        'required' => true,
                        'options'  => array(
                            'id'    => 'id_state',
                            'name'  => 'name',
                            'query' => \State::getStatesByIdCountry(\Country::getByIso('CA'))
                        )
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Phone'),
                        'class'    => 'fixed-width-lg',
                        'name'     => 'phone',
                        'required' => true,
                    ),
                    array(
                        'type'     => 'switch',
                        'label'    => $this->l('Set as Origin'),
                        'name'     => 'origin',
                        'required' => false,
                        'class'    => 't',
                        'is_bool'  => true,
                        'values'   => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                        'desc'     => $this->l('Use this address as the origin for rate calculation. At least one address must be set as origin.')
                    ),
                ),
                'submit'  => array(
                    'title' => $this->l('Save'),
                ),
                'buttons' => array(
                    array(
                        'href'  => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Back to list'),
                        'icon'  => 'process-icon-back'
                    )
                )
            ),
        );

        return $this->renderForm($fields, $this->tabs[$form]['id'], true);
    }

    public function renderBoxList()
    {
        $form = 'box';

        $fields_list           = array();
        $fields_list['id_box'] = array(
            'title'   => $this->l('ID'),
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['name']   = array(
            'title'   => $this->l('Name'),
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['width']  = array(
            'title'   => $this->l('Width') . ' (' . \Configuration::get('PS_DIMENSION_UNIT') . ')',
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['height'] = array(
            'title'   => $this->l('Depth') . ' (' . \Configuration::get('PS_DIMENSION_UNIT') . ')',
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['length'] = array(
            'title'   => $this->l('Length') . ' (' . \Configuration::get('PS_DIMENSION_UNIT') . ')',
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['weight'] = array(
            'title'   => $this->l('Weight') . ' (' . \Configuration::get('PS_WEIGHT_UNIT') . ')',
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['active'] = array(
            'title'   => $this->l('Rates'),
            'hint'    => $this->l('Enable/Disable the box for front office rate calculation.'),
            'align'   => 'center',
            'active'  => 'status',
            'type'    => 'bool',
            'class'   => 'fixed-width-sm',
            'search'  => false,
            'orderby' => false,
        );

        $boxes = Box::getBoxes();

        $helper                     = new HelperList();
        $helper->shopLinkType       = '';
        $helper->simple_header      = false;
        $helper->identifier         = 'id_box';
        $helper->listTotal          = count($boxes);
        $helper->show_toolbar       = true;
        $helper->actions            = array('edit', 'delete');
        $helper->bulk_actions       = array(
            'enableSelection'  => array(
                'text' => $this->l('Enable selection'),
                'icon' => 'icon-power-off text-success'
            ),
            'disableSelection' => array(
                'text' => $this->l('Disable selection'),
                'icon' => 'icon-power-off text-danger'
            ),
            'divider'          => array(
                'text' => 'divider'
            ),
            'delete'           => array(
                'text'    => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon'    => 'icon-trash'
            )
        );
        $helper->title              = $this->tabs[$form]['title'];
        $helper->table              = _DB_PREFIX_ . Box::$definition['table'];
        $helper->token              = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex       = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&add' . _DB_PREFIX_ . Box::$definition['table'] . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new')
        );

        return $helper->generateList($boxes, $fields_list);
    }


    public function renderBoxForm()
    {
        $form = 'box';

        $fields = array(
            'form' => array(
                'legend'  => array(
                    'title' => $this->tabs[$form]['title'],
                    'icon'  => $this->tabs[$form]['icon'],
                ),
                'input'   => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_box',
                    ),
                    array(
                        'type'        => 'text',
                        'label'       => $this->l('Name'),
                        'class'       => 'fixed-width-lg',
                        'name'        => 'name',
                        'hint'        => 'Name of your box, e.g. Small Box',
                        'placeholder' => 'Small Box',
                    ),
                    array(
                        'type'        => 'text',
                        'label'       => $this->l('Width'),
                        'class'       => 'fixed-width-xs',
                        'name'        => 'width',
                        'suffix'      => \Configuration::get('PS_DIMENSION_UNIT'),
                        'hint'        => 'Width of your box, e.g. 15.0',
                        'placeholder' => '0.0',
                    ),
                    array(
                        'type'        => 'text',
                        'label'       => $this->l('Depth'),
                        'class'       => 'fixed-width-xs',
                        'name'        => 'height',
                        'suffix'      => \Configuration::get('PS_DIMENSION_UNIT'),
                        'hint'        => 'Depth of your box, e.g. 15.0',
                        'placeholder' => '0.0',
                    ),
                    array(
                        'type'        => 'text',
                        'label'       => $this->l('Length'),
                        'class'       => 'fixed-width-xs',
                        'name'        => 'length',
                        'suffix'      => \Configuration::get('PS_DIMENSION_UNIT'),
                        'hint'        => 'Length of your box, e.g. 15.0',
                        'placeholder' => '0.0',
                    ),
                    array(
                        'type'        => 'text',
                        'label'       => $this->l('Weight'),
                        'desc'        => 'Weight of your box when empty, e.g. 0.010. This weight will be added onto the total weight for rate calculation.',
                        'class'       => 'fixed-width-sm',
                        'name'        => 'weight',
                        'suffix'      => \Configuration::get('PS_WEIGHT_UNIT'),
                        'hint'        => 'Weight of your box when empty, e.g. 0.010. This weight will be added onto the total weight for rate calculation.',
                        'placeholder' => '0.000',
                    ),
                    array(
                        'type'     => 'switch',
                        'label'    => $this->l('Rates'),
                        'name'     => 'active',
                        'required' => false,
                        'class'    => 't',
                        'is_bool'  => true,
                        'values'   => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                        'hint'     => $this->l('Enable/Disable the box for front office rate calculation.')
                    ),
                ),
                'submit'  => array(
                    'title' => $this->l('Save'),
                ),
                'buttons' => array(
                    array(
                        'href'  => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Back to list'),
                        'icon'  => 'process-icon-back'
                    )
                )
            ),
        );

        return $this->renderForm($fields, $this->tabs[$form]['id'], true);
    }

    public function renderGroupList()
    {
        $form = 'group';

        $fields_list             = array();
        $fields_list['id_group'] = array(
            'title'   => $this->l('ID'),
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );
        $fields_list['name']     = array(
            'title'   => $this->l('Name'),
            'type'    => 'text',
            'search'  => false,
            'orderby' => false,
        );

        $groups = Group::getGroups();

        $helper                     = new HelperList();
        $helper->shopLinkType       = '';
        $helper->simple_header      = false;
        $helper->identifier         = 'id_group';
        $helper->listTotal          = count($groups);
        $helper->show_toolbar       = true;
        $helper->actions            = array('edit', 'delete');
        $helper->bulk_actions       = array(
            'delete' => array(
                'text'    => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon'    => 'icon-trash'
            )
        );
        $helper->title              = $this->tabs[$form]['title'];
        $helper->table              = _DB_PREFIX_ . Group::$definition['table'];
        $helper->token              = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex       = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&add' . _DB_PREFIX_ . Group::$definition['table'] . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new')
        );

        return $helper->generateList($groups, $fields_list);
    }

    public function renderGroupForm()
    {
        $form = 'group';

        $fields = array(
            'form' => array(
                'legend'      => array(
                    'title' => $this->tabs[$form]['title'],
                    'icon'  => $this->tabs[$form]['icon'],
                ),
                'description' => $this->l('Groups are used to organize shipments into groups and transmitting them at separate times. You can choose which group a shipment will belong to when creating a shipment label.'),
                'input'       => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_group',
                    ),
                    array(
                        'type'        => 'text',
                        'label'       => $this->l('Name'),
                        'class'       => 'fixed-width-lg',
                        'name'        => 'name',
                        'placeholder' => 'Default',
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'active',
                    ),
                ),
                'submit'      => array(
                    'title' => $this->l('Save'),
                ),
                'buttons'     => array(
                    array(
                        'href'  => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Back to list'),
                        'icon'  => 'process-icon-back'
                    )
                )
            ),
        );

        return $this->renderForm($fields, $this->tabs[$form]['id'], true);
    }

    public function renderCarrierMappingList()
    {
        $form = 'carriermapping';

        $fields_list             = array(
            'id_carrier' => array(
                'title'   => $this->l('ID'),
                'type'    => 'text',
                'search'  => false,
                'orderby' => false,
            ),
            'name' => array(
                'title'   => $this->l('PrestaShop Carrier'),
                'type'    => 'text',
                'search'  => false,
                'orderby' => false,
            ),
            'mname' => array(
                'title'   => $this->l('Mapped Canada Post Carrier'),
                'type'    => 'text',
                'search'  => false,
                'orderby' => false,
                'hint' => $this->l('Map your carrier to a Canada Post carrier to pre-select it for label creation and bulk label creation.')
            ),
        );

        $sql = '
		SELECT c.*, cl.delay, m.name AS mname, cm.id_carrier_mapping
		FROM `' . _DB_PREFIX_ . 'carrier` c
		LEFT JOIN `' . _DB_PREFIX_ . 'carrier_lang` cl ON (c.`id_carrier` = cl.`id_carrier` AND cl.`id_lang` = ' . (int) $this->context->language->id . \Shop::addSqlRestrictionOnLang('cl') . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'carrier_zone` cz ON (cz.`id_carrier` = c.`id_carrier`) ' . \Shop::addSqlAssociation('carrier', 'c') . '
		LEFT JOIN `' . _DB_PREFIX_ . CarrierMapping::$definition['table'] . '` cm ON (cm.`id_carrier` = c.`id_carrier`)
		LEFT JOIN `' . _DB_PREFIX_ . Method::$definition['table'] . '` m ON (m.`id_method` = cm.`id_mapped_carrier`)
		WHERE c.`deleted` = 0
		AND c.`active` = 1
		GROUP BY c.`id_carrier` ORDER BY c.`position` ASC
        ';

        $carriers = \Db::getInstance()->executeS($sql);

        foreach ($carriers as $key => $carrier) {
            if ($carrier['name'] == '0') {
                $carriers[$key]['name'] = \Carrier::getCarrierNameFromShopName();
            }
        }

        $helper                     = new HelperList();
        $helper->shopLinkType       = '';
        $helper->simple_header      = false;
        $helper->identifier         = 'id_carrier';
        $helper->show_toolbar       = false;
        $helper->module             = $this;
        $helper->actions            = array('edit', 'unmap');
        $helper->title              = $this->tabs[$form]['title'];
        $helper->table              = _DB_PREFIX_ . CarrierMapping::$definition['table'];
        $helper->token              = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex       = AdminController::$currentIndex . '&configure=' . $this->name;

        return $helper->generateList($carriers, $fields_list);
    }

    public function renderCarrierMappingForm()
    {
        $form = 'carriermapping';
        $Carrier = new \Carrier(Tools::getValue('id_carrier'));

        $fields = array(
            'form' => array(
                'legend'      => array(
                    'title' => $this->tabs[$form]['title'],
                    'icon'  => $this->tabs[$form]['icon'],
                ),
                'description' => $this->l('Map your carrier to a Canada Post carrier to pre-select it for label creation and bulk label creation.'),
                'input'       => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_carrier',
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'id_carrier_mapping',
                    ),
                    array(
                        'type'        => 'html',
                        'label'       => $this->l('PrestaShop Carrier'),
                        'html_content' => \CanadaPostPs\Tools::renderHtmlTag(
                            'label',
                            \CanadaPostPs\Tools::renderHtmlTag('b', $Carrier->name),
                            array('class' => 'control-label')
                        ),
                        'class'       => 'fixed-width-lg',
                        'name'        => 'name',
                        'placeholder' => 'Default',
                        'disabled' => true,
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Mapped Carrier'),
                        'name'     => 'id_mapped_carrier',
                        'required' => true,
                        'options'  => array(
                            'id'    => 'id_method',
                            'name'  => 'name',
                            'query' => Method::getMethods(),
                        )
                    ),
                ),
                'submit'      => array(
                    'title' => $this->l('Save'),
                ),
                'buttons'     => array(
                    array(
                        'href'  => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Back to list'),
                        'icon'  => 'process-icon-back'
                    )
                )
            ),
        );

        $carrierMapping = CarrierMapping::getCarrierMappingByCarrierId($Carrier->id);
        $id_mapped_carrier = $carrierMapping ? $carrierMapping['id_mapped_carrier'] : false;
        $id_carrier_mapping = $carrierMapping ? $carrierMapping['id_carrier_mapping'] : false;

        $values = array();
        $values['id_carrier'] = $Carrier->id;
        $values['id_carrier_mapping'] = Tools::getValue('id_carrier_mapping', $id_carrier_mapping);
        $values['id_mapped_carrier'] = Tools::getValue('id_mapped_carrier', $id_mapped_carrier);

        return $this->renderForm($fields, $this->tabs[$form]['id'], true, false, true, $values);
    }

    public function renderRateDiscountList()
    {
        $form = 'ratediscount';

        $fields_list             = array(
            'id_cpl_rate_discount' => array(
                'title'   => $this->l('ID'),
                'type'    => 'text',
                'search'  => false,
                'orderby' => false,
            ),
            'name' => array(
                'title'   => $this->l('Canada Post Carrier'),
                'type'    => 'text',
                'search'  => false,
                'orderby' => false,
            ),
            'apply_discount' => array(
                'title'   => $this->l('Type'),
                'type'    => 'text',
                'search'  => false,
                'orderby' => false,
            ),
            'discount_value' => array(
                'title'   => $this->l('Amount/Percent'),
                'type'    => 'text',
                'search'  => false,
                'orderby' => false,
            ),
            'order_value' => array(
                'title'   => $this->l('Orders Above'),
                'type'    => 'text',
                'callback' => 'formatPrice',
                'callback_object' => $this,
                'search'  => false,
                'orderby' => false,
            ),
            'include_tax' => array(
                'title'   => $this->l('Tax Incl'),
                'type'    => 'bool',
                'search'  => false,
                'orderby' => false,
            ),
            'include_shipping' => array(
                'title'   => $this->l('Shipping Incl'),
                'type'    => 'bool',
                'search'  => false,
                'orderby' => false,
            ),
            'include_discounts' => array(
                'title'   => $this->l('Discounts Incl'),
                'type'    => 'bool',
                'search'  => false,
                'orderby' => false,
            ),
            'active' => array(
                'title'   => $this->l('Active'),
                'hint'    => $this->l('Enable/Disable the discount.'),
                'align'   => 'center',
                'active'  => 'status',
                'type'    => 'bool',
                'class'   => 'fixed-width-sm',
                'search'  => false,
                'orderby' => false,
            )
        );

        $sql = '
		SELECT rd.*, m.name as name
		FROM `' . _DB_PREFIX_ . RateDiscount::$definition['table'] . '` rd'.
         ($this->context->shop->id ? \Shop::addSqlAssociation(RateDiscount::$definition['table'], 'rd') : '') .'
		LEFT JOIN `' . _DB_PREFIX_ . Method::$definition['table'] . '` m ON (m.`id_method` = rd.`id_method`)
        ';

        $carriers = \Db::getInstance()->executeS($sql);

        $helper                     = new HelperList();
        $helper->shopLinkType       = '';
        $helper->simple_header      = false;
        $helper->identifier         = RateDiscount::$definition['primary'];
        $helper->show_toolbar       = true;
        $helper->module             = $this;
        $helper->actions            = array('edit', 'delete');
        $helper->listTotal          = count(RateDiscount::getRateDiscounts(false, $this->context->shop->id));
        $helper->title              = $this->tabs[$form]['title'];
        $helper->table              = _DB_PREFIX_ . RateDiscount::$definition['table'];
        $helper->token              = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex       = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->bulk_actions       = array(
            'enableSelection'  => array(
                'text' => $this->l('Enable selection'),
                'icon' => 'icon-power-off text-success'
            ),
            'disableSelection' => array(
                'text' => $this->l('Disable selection'),
                'icon' => 'icon-power-off text-danger'
            ),
            'divider'          => array(
                'text' => 'divider'
            ),
            'delete'           => array(
                'text'    => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon'    => 'icon-trash'
            )
        );
        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&add' . _DB_PREFIX_ . RateDiscount::$definition['table'] . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new')
        );

        return $helper->generateList($carriers, $fields_list);
    }

    public function renderRateDiscountForm()
    {
        $form = 'ratediscount';

        // Only select methods that don't have discounts already applied
        // unless we're currently editing an existing discount
//        if (Tools::getIsset(RateDiscount::$definition['primary'])) {
//            $RateDiscount = new RateDiscount(Tools::getValue(RateDiscount::$definition['primary']));
//            $methods = Method::getMethods(array('id_method' => $RateDiscount->id_method));
//        } else {
//            $sql = '
//                SELECT *
//                FROM `' . _DB_PREFIX_ . Method::$definition['table'] . '`
//                WHERE `id_method` NOT IN (
//                    SELECT `id_method` FROM `' . _DB_PREFIX_ . RateDiscount::$definition['table'] . '`
        //		    )';
//
//            $methods = \Db::getInstance()->executeS($sql);
//        }

        $discountTypes = array();
        foreach (RateDiscount::$discountTypes as $id => $discountType) {
            $discountTypes[] = array(
                'id'    => $id,
                'label' => $this->l($discountType),
                'value' => $id,
            );
        }

        $fields = array(
            'form' => array(
                'legend'      => array(
                    'title' => $this->tabs[$form]['title'],
                    'icon'  => $this->tabs[$form]['icon'],
                ),
                'description' => $this->l('Create a discount rule for this carrier. Only one rule per carrier is allowed for each shop.'),
                'input'       => array(
                    array(
                        'type' => 'hidden',
                        'name' => RateDiscount::$definition['primary'],
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Canada Post Carrier'),
                        'name'     => 'id_method',
                        'required' => true,
                        'options'  => array(
                            'id'    => 'id_method',
                            'name'  => 'name',
                            'query' => Method::getMethods(),
                        )
                    ),
                    array(
                        'type'   => 'radio',
                        'label'  => $this->l('Discount Type'),
                        'name'   => 'apply_discount',
                        'values' => $discountTypes,
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Discount Value'),
                        'prefix' => '$ or %',
                        'class'    => 'fixed-width-md',
                        'name'     => 'discount_value',
                        'required' => false
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Discount Value Currency'),
                        'desc' => $this->l('The currency of the discount amount if "Discount Type" is "Amount".'),
                        'name'     => 'id_discount_currency',
                        'required' => false,
                        'options'  => array(
                            'id'    => 'id_currency',
                            'name'  => 'iso_code',
                            'query' => \Currency::getCurrencies(),
                        )
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Order Total is At Least'),
                        'prefix' => '$',
                        'desc' => $this->l('Leave as 0.00 to apply to all orders.'),
                        'class'    => 'fixed-width-md',
                        'name'     => 'order_value',
                        'required' => false
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Order Total Currency'),
                        'desc' => $this->l('The currency to calculate order totals with.'),
                        'name'     => 'id_order_currency',
                        'options'  => array(
                            'id'    => 'id_currency',
                            'name'  => 'iso_code',
                            'query' => \Currency::getCurrencies(),
                        )
                    ),
                    array(
                        'type'     => 'switch',
                        'label'    => $this->l('Include Tax in Order Total'),
                        'name'     => 'include_tax',
                        'desc' => $this->l('Choose to calculate order totals incl/excl taxes.'),
                        'required' => false,
                        'class'    => 't',
                        'is_bool'  => true,
                        'values'   => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type'     => 'switch',
                        'label'    => $this->l('Include Shipping in Order Total'),
                        'name'     => 'include_shipping',
                        'desc' => $this->l('Choose to calculate order totals incl/excl the shipping fee for THIS carrier (not the carrier chosen in the cart).'),
                        'required' => false,
                        'class'    => 't',
                        'is_bool'  => true,
                        'values'   => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type'     => 'switch',
                        'label'    => $this->l('Include Discounts in Order Total'),
                        'name'     => 'include_discounts',
                        'desc' => $this->l('Choose to calculate order totals incl/excl discounts/reductions.'),
                        'required' => false,
                        'class'    => 't',
                        'is_bool'  => true,
                        'values'   => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type'     => 'switch',
                        'label'    => $this->l('Active'),
                        'name'     => 'active',
                        'required' => false,
                        'class'    => 't',
                        'is_bool'  => true,
                        'values'   => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit'      => array(
                    'title' => $this->l('Save'),
                ),
                'buttons'     => array(
                    array(
                        'href'  => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Back to list'),
                        'icon'  => 'process-icon-back'
                    )
                )
            ),
        );

        if (\Shop::isFeatureActive()) {
            $fields['form']['input'] = array_merge(
                $fields['form']['input'],
                array(
                    array(
                        'type' => 'shop',
                        'label' => $this->l('Shop association'),
                        'name' => 'checkBoxShopAsso',
                    )
                )
            );
        }

        return $this->renderForm($fields, $this->tabs[$form]['id'], true);
    }

    public function renderVerifyForm()
    {
        $form = 'verify';

        $fields = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->tabs[$form]['title'],
                    'icon'  => $this->tabs[$form]['icon']
                ),
                'input'  => array(
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Enter your Email'),
                        'class' => 'fixed-width-xxl',
                        'name'  => self::PREFIX . 'VE',
                        'desc'  => $this->l('This must be the email that you purchased the module with.'),
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Enter your Serial Number'),
                        'class' => 'fixed-width-xxl',
                        'name'  => self::PREFIX . 'VS',
                        'desc'  => $this->l('The serial number was emailed to the address you used to purchase the module with. If you never received one, please contact me at ') .\CanadaPostPs\Tools::renderHtmlLink('https://zhmedia.ca/contact', null, array('target' => '_blank')),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Activate'),
                )
            ),
        );

        return $this->renderForm($fields, $this->tabs[$form]['id']);
    }

    /**
     * @param int|bool $id_order
     * */
    public function renderCreateLabelForm($id_order = false, $action = false)
    {
        $form = 'createLabel';

        $fields = array(
            'form' => array(
//                'legend'  => array(
//                    'title' => $this->l('Canada Post: Create Label'),
//                    'icon'  => 'icon-print'
//                ),
                'form_tabs'    => array(
                    'parcel'      => $this->l('Parcel'),
                    'address'     => $this->l('Addresses'),
                    'options'     => $this->l('Options'),
                    'preferences' => $this->l('Preferences'),
                    'customs'     => $this->l('Customs'),
                    'return'      => $this->l('Return Label'),
                ),
                'buttons' => array(
                    array(
                        'type'  => 'submit',
                        'title' => $this->l('Create Label'),
                        'name'  => 'submitCreateLabel',
                        'icon'  => 'icon-print',
                        'class' => 'btn-primary btn-create-label'
                    ),
                    array(
                        'type'  => 'button',
                        'title' => $this->l('Update Rate'),
                        'name'  => 'submitUpdateRate',
                        'icon'  => 'icon-refresh',
                        'class' => 'btn-update-rate'
                    ),
                ),
            ),
        );

        if (Tools::isSubmit('editlabel') && !Tools::getIsset('ajax')) {
            $fields['form']['buttons'] = array_merge(
                $fields['form']['buttons'],
                array(
                    array(
                        'type' => 'button',
                        'title' => $this->l('Close'),
                        'name'  => 'submitClose',
                        'icon'  => 'process-icon-close',
                        'class' => 'btn-close-edit btn btn-default pull-right'
                    )
                )
            );
        }

        if (self::getConfig('MODE') == 0) {
            $fields['form']['description'] = $this->l('Test Mode Activated: A test label will be created and your Canada Post account will not be billed.');
        }

        if ($id_order) {
            $Order = new Order($id_order);
//            $Address = new Address($Order->id_address_delivery);
            $products = $Order->getProducts();
        } else {
            $products = array(
                array(
                    'product_name' => $this->l('Product 1'),
                    'id_order_detail'   => 1,
                    'is_virtual' => 0,
                )
            );
        }

        $customsProducts = array();
        foreach ($products as $product) {
            if ($product['is_virtual']) {
                continue;
            }
            $customsProducts = array_merge(
                $customsProducts,
                $this->getCustomsProductFields('customs', $product, $id_order)
            );
        }

        if (!$id_order) {
            $customsProducts[] = array(
                'type'             => 'html',
                'label'            => '',
                'form_group_class' => 'addCustomsProduct',
                'name'             => 'divider',
                'html_content'     => \CanadaPostPs\Tools::renderHtmlLink(
                    '#',
                    \CanadaPostPs\Icon::getIconHtml('plus-sign') . ' ' . $this->l('Add a Product'),
                    array('id' => 'addCustomsProduct', 'class' => 'btn btn-default')
                ),
                'tab'              => 'customs',
            );
        }

        $fields['form']['input'] = array_merge(
            $this->getParcelFields('parcel'),
            $this->getAddressFields('address'),
            $this->getOptionsFields('options'),
            $this->getPreferencesFields('preferences'),
            $this->getCustomsFields('customs'),
            $customsProducts,
            $this->getReturnFields('return'),
            $this->getHiddenFields($id_order),
            $this->getRateFields($id_order)
        );

        return $this->renderForm(
            $fields,
            $form,
            false,
            $action,
            false,
            $this->getCreateLabelFormFieldValues($id_order, $products)
        );
    }

    /**
     * @param int|bool $id_order
     * */
    public function renderCreateReturnLabelForm($id_order = false, $action = false)
    {
        $form = 'createReturnLabel';

        $fields = array(
            'form' => array(
//                'legend'  => array(
//                    'title' => $this->l('Canada Post: Create Return Label'),
//                    'icon'  => 'icon-undo'
//                ),
                'form_tabs'    => array(
                    'parcel-return'      => $this->l('Parcel'),
                    'address-return'     => $this->l('Addresses'),
                    'options-return' => $this->l('Options'),
                    'preferences-return' => $this->l('Preferences'),
                ),
                'buttons' => array(
                    array(
                        'type'  => 'submit',
                        'title' => $this->l('Create Return Label'),
                        'name'  => 'submitCreateReturnLabel',
                        'icon'  => 'icon-print',
                        'class' => 'btn-primary btn-create-label'
                    ),
                ),
            ),
        );

        if (self::getConfig('MODE') == 0) {
            $fields['form']['description'] = $this->l('Test Mode Activated: A test return label will be created and your Canada Post account will not be billed.');
        }

        // Only show Return Label form on Canadian orders
        $canadaOrder = false;
        if ($id_order) {
            $Order = new Order($id_order);
            if (\Validate::isLoadedObject($Order)) {
                $Address = new \Address($Order->id_address_delivery);
                if (\Country::getIsoById($Address->id_country) == 'CA') {
                    $canadaOrder = true;
                }
            }
        }

        $returnFields = array_merge(
            $this->getParcelFields('parcel-return', true),
            $this->getAddressFields('address-return', true),
            $this->getOptionsFields('options-return'),
            $this->getPreferencesFields('preferences-return', true),
            $this->getHiddenFields($id_order)
        );

        $returnFieldNames = array(
            'receiver',
            'divider',
            'service-code',
            'name',
            'company',
            'address-line-1',
            'address-line-2',
            'city',
            'prov-state',
            'postal-zip-code',
            'client-voice-number',
            'box',
            'weight',
            'length',
            'width',
            'height',
            'output-format',
            'customer-ref-1',
            'customer-ref-2',
            'email',
            'notification',
            'id_order',
        );

        // Remove unused fields
        foreach ($returnFields as $key => $field) {
            if (!in_array($field['name'], $returnFieldNames)) {
                unset($returnFields[$key]);
            }
        }

        if (($id_order && $canadaOrder) || !$id_order) {
            $fields['form']['input'] = $returnFields;
        } else {
            $fields['form']['description'] = $this->l('Return labels are only available for Canadian shipping addresses.');
            $fields['form']['input'] = array();
            unset($fields['form']['buttons']);
        }

        return $this->renderForm(
            $fields,
            $form,
            false,
            $action,
            false,
            $this->getCreateLabelFormFieldValues($id_order, array())
        );
    }

    public function getParcelFields($tab, $return = false)
    {
        $methodOptionGroups = array();
        foreach (Method::$shipping_methods as $group => $methods) {
            // Return labels are only domestic
            if ($return && $group != 'DOM') {
                break;
            }
            $query = array();
            foreach ($methods as $k => $v) {
                $query[] = array(
                    'id_option' => $k,
                    'name'      => sprintf('%s - %s', $k, $v),
                );
            }
            $methodOptionGroups[] = array(
                'label' => $group,
                'query' => $query,
            );
        }

        $boxes = array();
        foreach (Box::getBoxes(array('active' => true)) as $box) {
            $boxes[] = array(
                'id_box' => $box['id_box'],
                'name'   => sprintf(
                    '%s (%s x %s x %s - %s)',
                    $box['name'],
                    $box['length'],
                    $box['width'],
                    $box['height'] . \Configuration::get('PS_DIMENSION_UNIT'),
                    $box['weight'] . \Configuration::get('PS_WEIGHT_UNIT')
                ),
            );
        }

        $parcelFields = array(
            'service-code' => array(
                'type'    => 'select',
                'label'   => $this->l('Service Method'),
                'name'    => 'service-code',
                'tab'     => $tab,
                'options' => array(
                    'optiongroup' => array(
                        'label' => 'label',
                        'query' => $methodOptionGroups,
                    ),
                    'options'     => array(
                        'id'    => 'id_option',
                        'name'  => 'name',
                        'query' => 'query',
                    ),
                ),
            ),
            'box' => array(
                'type'    => 'select',
                'label'   => $this->l('Box'),
                'name'    => 'box',
                'hint'    => $this->l('The value of this field is not used when creating a label. Changing this field will simply populate the dimension fields for you with the box\'s dimensions. The most optimal box is pre-selected for you based on the products in this order, but you can change the dimensions to any custom value.'),
                'tab'     => $tab,
                'class'      => 'box',
                'boxes'   => Box::getBoxes(array('active' => true)),
                'options' => array(
                    'id'    => 'id_box',
                    'name'  => 'name',
                    'query' => $boxes,
                ),
            ),
            'weight' => array(
                'type'        => 'text',
                'label'       => $this->l('Weight'),
                'class'       => 'weight fixed-width-xs',
                'name'        => 'weight',
                'tab'         => $tab,
                'suffix'      => 'kg',
                'hint'        => $this->l('Total weight of your parcel in KG including packaging weight, e.g. 0.100'),
                'placeholder' => '0.000',
            ),
            'length' => array(
                'type'        => 'text',
                'label'       => $this->l('Length'),
                'class'       => 'length fixed-width-xs',
                'name'        => 'length',
                'tab'         => $tab,
                'suffix'      => 'cm',
                'hint'        => $this->l('Length of your box in CM, e.g. 10.0'),
                'placeholder' => '0.0',
            ),
            'width' => array(
                'type'        => 'text',
                'label'       => $this->l('Width'),
                'class'       => 'width fixed-width-xs',
                'name'        => 'width',
                'tab'         => $tab,
                'suffix'      => 'cm',
                'hint'        => $this->l('Width of your box in CM, e.g. 10.0'),
                'placeholder' => '0.0',
            ),
            'height' => array(
                'type'        => 'text',
                'label'       => $this->l('Height'),
                'class'       => 'height fixed-width-xs',
                'name'        => 'height',
                'tab'         => $tab,
                'suffix'      => 'cm',
                'hint'        => $this->l('Height of your box in CM, e.g. 10.0'),
                'placeholder' => '0.0',
            ),
        );

        if (self::getConfig('CONTRACT') && !$return) {
            $groupField = array(
                'group-id' => array(
                    'type'    => 'select',
                    'label'   => $this->l('Group'),
                    'name'    => 'group-id',
                    'hint'    => $this->l('The purpose of a Group is to group several shipments together to include on the same manifest. For example, grouping is useful in the following scenarios: You have multiple fulfillment locations; You want all shipments in a group to be shipped on the same day; You want to group shipments together for internal reference or billing purposes.'),
                    'tab'     => $tab,
                    'options' => array(
                        'id'    => 'id_group',
                        'name'  => 'name',
                        'query' => Group::getGroups(),
                    ),
                ),
            );
            $parcelFields = array_merge($parcelFields, $groupField);
        }

        return $parcelFields;
    }

    public function getAddressFields($tab, $return = false)
    {
        return array(
            'sender' => array(
                'type'    => 'select',
                'label'   => $return ? $this->l('Receiver') : $this->l('Return/From Address'),
                'name'    => $return ? 'receiver' : 'sender',
                'hint'    => $return ? $this->l('The destination address of the return label.') : $this->l('The address that will appear in the “From” address of the label'),
                'tab'     => $tab,
                'options' => array(
                    'id'    => 'id_address',
                    'name'  => 'name',
                    'query' => array_map(function ($k) {
                        $k['name'] = sprintf('%s - %s, %s', $k['name'], $k['address1'], $k['postcode']);

                        return $k;
                    }, Address::getAddresses()),
                )
            ),
            'divider' => array(
                'type'         => 'html',
                'label'        => sprintf(
                    '%s',
                    \CanadaPostPs\Tools::renderHtmlTag('b',
                        $return ? $this->l('Returner') : $this->l('Destination')
                    )
                ),
                'html_content' => \CanadaPostPs\Tools::renderHtmlTag('hr'),
                'name'         => 'divider',
                'tab'          => $tab,
            ),
            'name' => array(
                'type'  => 'text',
                'label' => $this->l('Name'),
                'class' => 'fixed-width-xl',
                'name'  => 'name',
                'tab'   => $tab,
            ),
            'company' => array(
                'type'  => 'text',
                'label' => $this->l('Company'),
                'hint'  => $this->l('Max length 44.'),
                'class' => 'fixed-width-xl',
                'name'  => 'company',
                'tab'   => $tab,
            ),
            'address-line-1' => array(
                'type'  => 'text',
                'label' => $this->l('Address Line 1'),
                'hint'  => $this->l('Max length 44.'),
                'class' => 'fixed-width-xl',
                'name'  => 'address-line-1',
                'tab'   => $tab,
            ),
            'address-line-2' => array(
                'type'  => 'text',
                'label' => $this->l('Address Line 2'),
                'hint'  => $this->l('Max length 44.'),
                'class' => 'fixed-width-xl',
                'name'  => 'address-line-2',
                'tab'   => $tab,
            ),
            'additional-address-info' => array(
                'type'  => 'text',
                'label' => $this->l('Additional Address Info'),
                'hint'  => $this->l('Additional address information for the destination. This information is printed directly above address line 1 on the shipping label. Max length 44.'),
                'class' => 'fixed-width-xl',
                'name'  => 'additional-address-info',
                'tab'   => $tab,
            ),
            'client-voice-number' => array(
                'type'  => 'text',
                'label' => $this->l('Phone Number'),
                'hint'  => $this->l('Phone number at the destination. Not required for domestic shipments unless the Deliver to Post Office option has been selected.'),
                'class' => 'fixed-width-xl',
                'name'  => 'client-voice-number',
                'tab'   => $tab,
            ),
            'city' => array(
                'type'  => 'text',
                'label' => $this->l('City'),
                'class' => 'fixed-width-xl',
                'name'  => 'city',
                'tab'   => $tab,
            ),
            'prov-state' => array(
                'type'        => 'text',
                'label'       => $this->l('Province/State Code'),
                'hint'        => $this->l('Standard province code for provinces within Canada (e.g. ON). Standard state code for U.S. states (e.g. NY). Free form for states and provinces of other countries.'),
                'placeholder' => 'ON',
                'class'       => 'fixed-width-xl',
                'name'        => 'prov-state',
                'tab'         => $tab,
            ),
            'country-code' => array(
                'type'    => 'select',
                'label'   => $this->l('Country'),
                'class'   => 'fixed-width-xl',
                'name'    => 'country-code',
                'tab'     => $tab,
                'options' => array(
                    'id'    => 'iso_code',
                    'name'  => 'name',
                    'query' => \Country::getCountries($this->context->language->id),
                )
            ),
            'postal-zip-code' => array(
                'type'  => 'text',
                'label' => $this->l('Postal/Zip Code'),
                'hint'  => $this->l('Can be one of the following: 6-character alphanumeric for Canada (A9A9A9), 5-digit or 5-4 digit numeric code for US, up to 14 characters (free format) for other countries. Required for countries that require a Postal Code or zip code (e.g. Canada, U.S.A.)'),
                'class' => 'fixed-width-xl',
                'name'  => 'postal-zip-code',
                'tab'   => $tab,
            ),
        );
    }

    public function getOptionsFields($tab)
    {
        $options = array();
        foreach (Method::$options as $id_option => $name) {
            $options[] = array(
                'id'   => $id_option,
                'name' => sprintf('%s - %s', $id_option, $name),
            );
        }

        $nonDeliveryOptions = array();
        foreach (Method::$non_delivery_options as $id_option => $name) {
            $nonDeliveryOptions[] = array(
                'id'    => $id_option,
                'label' => sprintf('%s - %s', $id_option, $name),
                'value' => $id_option,
            );
        }

        return array(
            array(
                'type'   => 'checkbox',
                'label'  => $this->l('Delivery Options'),
                'name'   => 'options',
                'tab'    => $tab,
                'values' => array(
                    'query' => $options,
                    'id'    => 'id',
                    'name'  => 'name'
                ),
            ),
            array(
                'type'   => 'radio',
                'form_group_class' => 'non_delivery_options',
                'label'  => $this->l('Non-Delivery Options'),
                'name'   => 'non_delivery_options',
                'desc'   => $this->l('For USA or Intl shipments, choose how to treat a parcel that cannot be delivered.'),
                'tab'    => $tab,
                'values' => $nonDeliveryOptions,
            ),
            array(
                'type'        => 'text',
                'label'       => $this->l('COV Amount'),
                'hint'        => $this->l('Required if "Coverage" option is selected.'),
                'placeholder' => '0.00',
                'prefix'      => 'CAD$',
                'class'       => 'fixed-width-sm required COV',
                'name'        => 'COV-option-amount',
                'tab'         => $tab,
            ),
            array(
                'type'        => 'text',
                'label'       => $this->l('COD Amount'),
                'hint'        => $this->l('Required if "Collect on Delivery" option is selected.'),
                'placeholder' => '0.00',
                'prefix'      => 'CAD$',
                'class'       => 'fixed-width-sm required COD',
                'name'        => 'COD-option-amount',
                'tab'         => $tab,
            ),
            array(
                'type'    => 'switch',
                'label'   => $this->l('Add Shipping Cost to specified COD Amount'),
                'class'   => 'fixed-width-sm required COD t',
                'name'    => 'COD-option-qualifier-1',
                'tab'     => $tab,
                'is_bool' => true,
                'values'  => array(
                    array(
                        'id'    => 'active_on',
                        'value' => 1,
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => 0,
                    )
                ),
            ),
            array(
                'type'        => 'text',
                'label'       => $this->l('D2PO Post Office ID'),
                'hint'        => $this->l('Required if "Deliver to Post Office" option is selected.'),
                'desc'        => $this->l('The ID of the post office to deliver the parcel to.'),
                'placeholder' => '3102',
                'class'       => 'fixed-width-sm required D2PO',
                'name'        => 'D2PO-option-qualifier-2',
                'tab'         => $tab,
            ),
            array(
                'type'        => 'text',
                'label'       => $this->l('Notification Email'),
                'hint'        => $this->l('Required if "Deliver to Post Office" and "Send Notifications" options are selected.'),
                'desc'        => $this->l('Send delivery updates to this email. For D2PO, this email will be used to notify the customer that their parcel is ready for pickup.'),
                'placeholder' => 'email@example.com',
                'class'       => 'fixed-width-xl required notifications',
                'name'        => 'email',
                'tab'         => $tab,
            ),
            array(
                'type'   => 'checkbox',
                'label'  => $this->l('Send Notification'),
                'class'  => 'required notifications',
                'name'   => 'notification',
                'hint'   => $this->l('Required if "Deliver to Post Office" option is selected.'),
                'tab'    => $tab,
                'values' => array(
                    'query' => array(
                        array(
                            'id'   => 'on-shipment',
                            'name' => 'On Shipment',
                        ),
                        array(
                            'id'   => 'on-exception',
                            'name' => 'On Exception',
                        ),
                        array(
                            'id'   => 'on-delivery',
                            'name' => 'On Delivery',
                        ),
                    ),
                    'id'    => 'id',
                    'name'  => 'name'
                )
            ),
        );
    }

    public function getPreferencesFields($tab, $return = false)
    {
        $printOutputOptions = array();
        foreach (array('8.5x11', '4x6') as $format) {
            $printOutputOptions[] = array(
                'id'    => $format,
                'label' => $format,
                'value' => $format,
            );
        }
        $methodOfPayment = array();
        foreach (array('CreditCard', 'Account') as $payment) {
            $methodOfPayment[] = array(
                'id'    => $payment,
                'label' => $payment,
                'value' => $payment,
            );
        }

        $preferencesFields = array(
            array(
                'type'    => 'switch',
                'label'   => $this->l('Show Packing Instructions'),
                'class'   => 'required notifications',
                'name'    => 'show-packing-instructions',
                'hint'    => $this->l('Choose whether packing instructions are to be rendered on the label.'),
                'tab'     => $tab,
                'is_bool' => true,
                'values'  => array(
                    array(
                        'id'    => 'active_on',
                        'value' => 1,
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => 0,
                    )
                ),
            ),
            array(
                'type'    => 'switch',
                'label'   => $this->l('Show Postage Rate'),
                'class'   => 'required notifications',
                'name'    => 'show-postage-rate',
                'hint'    => $this->l('Choose whether the postal rate is to be shown on the label.'),
                'tab'     => $tab,
                'is_bool' => true,
                'values'  => array(
                    array(
                        'id'    => 'active_on',
                        'value' => 1,
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => 0,
                    )
                ),
            ),
            array(
                'type'    => 'switch',
                'label'   => $this->l('Show Insured Value'),
                'class'   => 'required notifications',
                'name'    => 'show-insured-value',
                'hint'    => $this->l('Choose whether the insured value is to be shown on the label. This element is required only for insured U.S.A. and international shipments.'),
                'tab'     => $tab,
                'is_bool' => true,
                'values'  => array(
                    array(
                        'id'    => 'active_on',
                        'value' => 1,
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => 0,
                    )
                ),
            ),
            array(
                'type'  => 'text',
                'label' => $this->l('Cost Centre'),
                'hint'  => $this->l('This is a value you assign for use by your applications. The value you enter here will appear on your invoice and in the PosteCS secure email that Canada Post uses to send your invoice.'),
                'class' => 'fixed-width-xl',
                'name'  => 'cost-centre',
                'tab'   => $tab,
            ),
            array(
                'type'  => 'text',
                'label' => $this->l('Customer Reference 1'),
                'hint'  => $this->l('This is a user-defined value available for use by your applications. (e.g. you could use this field as an internal "order id"). The value you enter here will appear on the shipping label, in Track and – for customers who subscribe to our Automated Parcel Tracking service – in your APT file.'),
                'class' => 'fixed-width-xl',
                'name'  => 'customer-ref-1',
                'tab'   => $tab,
            ),
            array(
                'type'  => 'text',
                'label' => $this->l('Customer Reference 2'),
                'hint'  => $this->l('This is a user-defined value available for use by your applications. (e.g. you could use this field as an internal "order id"). The value you enter here will appear on the shipping label, in Track and – for customers who subscribe to our Automated Parcel Tracking service – in your APT file.'),
                'class' => 'fixed-width-xl',
                'name'  => 'customer-ref-2',
                'tab'   => $tab,
            ),
            array(
                'type'    => 'switch',
                'label'   => $this->l('Unpackaged'),
                'hint'    => $this->l('Indicates whether a shipment is unpackaged or not. For example, auto tires may be an example of an unpackaged shipment.'),
                'class'   => 't',
                'name'    => 'unpackaged',
                'tab'     => $tab,
                'is_bool' => true,
                'values'  => array(
                    array(
                        'id'    => 'active_on',
                        'value' => 1,
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => 0,
                    )
                ),
            ),
            array(
                'type'    => 'switch',
                'label'   => $this->l('Oversized'),
                'hint'    => $this->l('Indicates whether the parcel is oversized or not. Note: If parcel dimensions have been provided, then this element will be automatically determined (as either true or false) based on the parcel dimensions (regardless of whether you include a value for the "oversized" element field). However, if no dimensions are provided, then you can specify that a parcel is oversized (or not) using this element.'),
                'class'   => 't',
                'name'    => 'oversized',
                'tab'     => $tab,
                'is_bool' => true,
                'values'  => array(
                    array(
                        'id'    => 'active_on',
                        'value' => 1,
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => 0,
                    )
                ),
            ),
            array(
                'type'    => 'switch',
                'label'   => $this->l('Mailing Tube'),
                'hint'    => $this->l('Indicates whether a shipment is contained in a mailing tube. e.g. a poster tube'),
                'class'   => 't',
                'name'    => 'mailing-tube',
                'tab'     => $tab,
                'is_bool' => true,
                'values'  => array(
                    array(
                        'id'    => 'active_on',
                        'value' => 1,
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => 0,
                    )
                ),
            ),
        );

        if ($this->isContract() || $return) {
            $preferencesFields['output-format'] =  array(
                'type'   => 'radio',
                'label'  => $this->l('Print Output Format'),
                'name'   => 'output-format',
                'tab'    => $tab,
                'values' => $printOutputOptions,
            );
        }

        if (self::getConfig('CONTRACT')) {
            $preferencesFields['intended-method-of-payment'] = array(
                'type'   => 'radio',
                'label'  => $this->l('Method of Payment'),
                'hint'   => $this->l('Indicates the method of payment for this shipment.'),
                'name'   => 'intended-method-of-payment',
                'tab'    => $tab,
                'values' => $methodOfPayment,
            );
        }

        return $preferencesFields;
    }

    public function getCustomsFields($tab)
    {
        $reasonForExportOptions = array(
            'DOC' => 'document',
            'SAM' => 'commercial sample',
            'REP' => 'repair or warranty',
            'SOG' => 'sale of goods',
            'OTH' => 'other'
        );
        $reasonForExport        = array();
        foreach ($reasonForExportOptions as $id => $name) {
            $reasonForExport[] = array(
                'id'    => $id,
                'label' => sprintf('%s - %s', $id, $name),
                'value' => $id,
            );
        }

        return array(
            array(
                'type'  => 'text',
                'label' => $this->l('Destination Currency'),
                'hint'  => $this->l('This is the currency of the receiving country. Value must be: CAD for Canadian currency, USD for U.S. currency, Other valid ISO currency code. This value is pre-populated using the order\'s country\'s default currency in the International > Locations > Countries menu.'),
                'class' => 'fixed-width-sm',
                'name'  => 'currency',
                'tab'   => $tab,
            ),
            array(
                'type'  => 'text',
                'label' => $this->l('Conversion Rate From CAD'),
                'hint'  => $this->l('The conversion rate from the Canadian dollar to the currency you entered in the currency field above; for example, if you used USD as the target currency and $1.00 CAD = $0.85 USD, the conversion rate is 0.85.'),
                'class' => 'fixed-width-sm',
                'name'  => 'conversion-rate-from-cad',
                'tab'   => $tab,
            ),
            array(
                'type'   => 'radio',
                'label'  => $this->l('Reason For Export'),
                'hint'   => $this->l('A code that represents the reason for export, which assists with border crossing.'),
                'name'   => 'reason-for-export',
                'tab'    => $tab,
                'values' => $reasonForExport,
            ),
            array(
                'type'      => 'text',
                'label'     => $this->l('Other Reason'),
                'desc'      => $this->l('Required if Reason For Export is "other". Minimum 4 characters; maximum 44 characters.'),
                'class'     => 'fixed-width-xl',
                'name'      => 'other-reason',
                'maxlength' => 44,
                'tab'       => $tab,
            ),
            array(
                'type'  => 'text',
                'label' => $this->l('Certificate Number'),
                'hint'  => $this->l('If required by customs at the destination, the number of the government/agency certificate or permit.'),
                'class' => 'fixed-width-sm',
                'name'  => 'certificate-number',
                'tab'   => $tab,
            ),
            array(
                'type'  => 'text',
                'label' => $this->l('Licence Number'),
                'hint'  => $this->l('If required by customs at the destination, the number of the government/agency import or export licence.'),
                'class' => 'fixed-width-sm',
                'name'  => 'licence-number',
                'tab'   => $tab,
            ),
            array(
                'type'  => 'text',
                'label' => $this->l('Invoice Number'),
                'hint'  => $this->l('If required by customs at the destination, the commercial invoice number.'),
                'class' => 'fixed-width-sm',
                'name'  => 'invoice-number',
                'tab'   => $tab,
            ),
        );
    }

    /**
     * @param string|bool $tab
     * @param array|bool $products
     * */
    public function getCustomsProductFields($tab, $product, $id_order)
    {
        $formGroupClass = sprintf('product-%s', $product['id_order_detail']);

        $fields = array(
            array(
                'type'             => 'html',
                'label'            => \Tools::substr($product['product_name'], 0, 100),
                'form_group_class' => $formGroupClass,
                'name'             => 'divider',
                'html_content'     => $id_order ? '' : \CanadaPostPs\Tools::renderHtmlLink(
                    '#',
                    \CanadaPostPs\Icon::getIconHtml('minus-sign') . ' Remove ' . \Tools::substr($product['product_name'], 0, 100),
                    array(
                        'target' => '_blank',
                        'class' => 'btn btn-default product removeCustomsProduct',
                        'data-product' => $product['id_order_detail']
                    )
                ),
                'tab'              => $tab,
            ),
            array(
                'type'             => 'text',
                'label'            => $this->l('Description'),
                'hint'             => $this->l('Max length 45'),
                'form_group_class' => $formGroupClass,
                'class'            => 'fixed-width-xl',
                'name'             => sprintf('item[%s][customs-description]', $product['id_order_detail']),
                'maxlength'        => 45,
                'tab'              => $tab,
            ),
            array(
                'type'             => 'text',
                'label'            => $this->l('Qty'),
                'form_group_class' => $formGroupClass,
                'class'            => 'fixed-width-sm',
                'name'             => sprintf('item[%s][customs-number-of-units]', $product['id_order_detail']),
                'tab'              => $tab,
            ),
            array(
                'type'             => 'text',
                'label'            => $this->l('HS Tariff Code'),
                'form_group_class' => $formGroupClass,
                'class'            => 'fixed-width-xl',
                'hint'             => $this->l('Format: 9999.99.99.99'),
                'name'             => sprintf('item[%s][hs-tariff-code]', $product['id_order_detail']),
                'tab'              => $tab,
            ),
            array(
                'type'             => 'text',
                'label'            => $this->l('SKU'),
                'form_group_class' => $formGroupClass,
                'class'            => 'fixed-width-md',
                'hint'             => $this->l('Max length 15'),
                'maxlength'        => 15,
                'name'             => sprintf('item[%s][sku]', $product['id_order_detail']),
                'tab'              => $tab,
            ),
            array(
                'type'             => 'text',
                'label'            => $this->l('Weight'),
                'form_group_class' => $formGroupClass,
                'class'            => 'fixed-width-sm',
                'suffix'           => 'kg',
                'name'             => sprintf('item[%s][unit-weight]', $product['id_order_detail']),
                'tab'              => $tab,
            ),
            array(
                'type'             => 'text',
                'label'            => $this->l('Value Per Unit'),
                'form_group_class' => $formGroupClass,
                'class'            => 'fixed-width-sm',
                'prefix'           => 'CAD$',
                'name'             => sprintf('item[%s][customs-value-per-unit]', $product['id_order_detail']),
                'tab'              => $tab,
            ),
            array(
                'type'             => 'text',
                'label'            => $this->l('Country of Origin'),
                'hint'             => $this->l('Optional. 2-character valid country code.'),
                'form_group_class' => $formGroupClass,
                'class'            => 'fixed-width-sm',
                'placeholder'      => 'CA',
                'name'             => sprintf('item[%s][country-of-origin]', $product['id_order_detail']),
                'tab'              => $tab,
            ),
            array(
                'type'             => 'text',
                'label'            => $this->l('Province of Origin'),
                'hint'             => $this->l('2-character valid province code. Required if country of origin is Canada. The province of origin of the goods.'),
                'form_group_class' => $formGroupClass,
                'class'            => 'fixed-width-sm',
                'placeholder'      => 'ON',
                'name'             => sprintf('item[%s][province-of-origin]', $product['id_order_detail']),
                'tab'              => $tab,
            ),
            array(
                'type'             => 'html',
                'label'            => '',
                'form_group_class' => $formGroupClass.' product-last',
                'name'             => 'divider',
                'html_content'     => '',
                'tab'              => $tab,
            ),
        );

        return $fields;
    }

    public function getReturnFields($tab)
    {
        if (!self::getConfig('CONTRACT')) {
            return array();
        }

        return array(
            array(
                'type'    => 'switch',
                'label'   => $this->l('Return Label'),
                'class'   => 't',
                'name'    => 'return-spec',
                'hint'    => $this->l('Generate a return label with this shipment that is paid for at the time of label creation.'),
                'tab'     => $tab,
                'is_bool' => true,
                'values'  => array(
                    array(
                        'id'    => 'active_on',
                        'value' => 1,
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => 0,
                    )
                ),
            ),
            array(
                'type'    => 'select',
                'label'   => $this->l('Return Label Address'),
                'name'    => 'return-recipient',
                'tab'     => $tab,
                'options' => array(
                    'id'    => 'id_address',
                    'name'  => 'name',
                    'query' => array_map(function ($k) {
                        $k['name'] = sprintf('%s - %s, %s', $k['name'], $k['address1'], $k['postcode']);

                        return $k;
                    }, Address::getAddresses()),
                )
            ),
            array(
                'type'    => 'select',
                'label'   => $this->l('Return Method'),
                'name'    => 'return-service-code',
                'tab'     => $tab,
                'options' => array(
                    'id'    => 'code',
                    'name'  => 'name',
                    'query' => array_map(function ($k) {
                        $k['name'] = sprintf('%s - %s', $k['code'], $k['name']);

                        return $k;
                    }, Method::getMethods(array('group' => 'DOM'))),
                )
            ),
        );
    }

    public function getHiddenFields($id_order = false)
    {
        $fields = array();
        if ($id_order) {
            $orderFields = array(
                array(
                    'type' => 'hidden',
                    'name' => 'id_order',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'vieworder',
                ),
            );
            $fields = array_merge($fields, $orderFields);
        }

        return $fields;
    }

    public function getRateFields($id_order = false)
    {
        $fields = array(
            array(
                'type'             => 'html',
                'label'            => $this->l('Live Rate:'),
                'form_group_class' => 'live-rate success',
                'name'             => 'rate',
                'html_content'     => \CanadaPostPs\Tools::renderHtmlTag(
                    'label',
                    null,
                    array('class' => 'control-label', 'id' => 'live-rate')
                ),
            ),
        );

        if ($id_order) {
            $OrderLabelSettings = OrderLabelSettings::getOrderLabelSettingForOrderId($id_order);
            if ($OrderLabelSettings) {
                $labelText = $this->l('Label settings have been modified.');
            } else {
                $labelText = $this->l('Label settings unmodified. Modifications to the above settings will be saved automatically.');
            }
            $fields[] = array(
                'type'             => 'html',
                'label'            => $this->l('Saved Changes:'),
                'form_group_class' => 'save-changes info',
                'name'             => 'save-changes',
                'html_content'     => \CanadaPostPs\Tools::renderHtmlTag(
                    'label',
                    $labelText,
                    array('class' => 'control-label', 'id' => 'save-changes')
                ),
            );
        }

        return $fields;
    }

    public function getDividerField($label = false)
    {
        return array(
            array(
                'type'             => 'html',
                'label'            => $label ? $this->l($label) : '',
                'name'             => 'divider',
                'html_content'     => \CanadaPostPs\Tools::renderHtmlTag('hr'),
            )
        );
    }

    public function getLabelDefaultFields()
    {
        return array(
            array(
                'type'   => 'switch',
                'label'  => $this->l('Auto-Update Tracking Number on Label Creation'),
                'desc'   => $this->l('Choose whether to automatically update the tracking number of an order after creating a label for it.'),
                'name'   => self::PREFIX . 'UPDATE_TRACKING_NUMBER',
                'class'  => 't',
                'values' => array(
                    array(
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => $this->l('No')
                    )
                ),
            ),
            array(
                'type'   => 'switch',
                'label'  => $this->l('Auto-Update Order Status on Label Creation'),
                'desc'   => $this->l('Choose whether to automatically update the status of an order after creating a label for it.'),
                'name'   => self::PREFIX . 'UPDATE_ORDER_STATUS',
                'class'  => 't',
                'values' => array(
                    array(
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => $this->l('No')
                    )
                ),
            ),
            array(
                'type'   => 'select',
                'label'  => $this->l('Order Status'),
                'desc'   => $this->l('Choose which status to update an order with after creating a label.'),
                'name'   => self::PREFIX . 'ORDER_STATUS',
                'class'  => 'fixed-width-xl',
                'options'  => array(
                    'id'    => 'id_order_state',
                    'name'  => 'name',
                    'query' => \OrderState::getOrderStates($this->context->language->id)
                )
            ),
            array(
                'type'   => 'switch',
                'label'  => $this->l('Send Tracking Emails'),
                'desc'   => $this->l('Send the customer a tracking email on label creation. The email uses the "In Transit" email template and contains a tracking link based on the carrier\'s "Tracking URL" found in the Shipping > Carriers settings.'),
                'name'   => self::PREFIX . 'SEND_IN_TRANSIT_EMAIL',
                'class'  => 't',
                'values' => array(
                    array(
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => $this->l('No')
                    )
                ),
            ),
            array(
                'type'   => 'switch',
                'label'  => $this->l('Pickup by Canada Post'),
                'desc'   => $this->l('Set to Yes if your shipments are picked up by Canada Post or a third party. Provide the Postal Code of your pickup location in "Requested Shipping Point". Set to No if you deposit your shipments yourself and provide "Shipping Point ID" instead.'),
                'name'   => self::PREFIX . 'PICKUP',
                'class'  => 't',
                'values' => array(
                    array(
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => $this->l('No')
                    )
                ),
            ),
            array(
                'type'   => 'text',
                'label'  => $this->l('Requested Shipping Point'),
                'desc'   => $this->l('Mandatory if "Pickup by Canada Post" to "Yes". Postal Code of the location where your shipments are picked up. The Postal Code you provide is used for pricing purposes. If you deposit your shipments yourself, you have 2 choices: Omit this field and provide the site number of your deposit location in Shipping Point ID. (recommended), or: Provide the postal code of your deposit location here and omit Shipping Point ID.'),
                'name'   => self::PREFIX . 'REQUESTED_SHIPPING_POINT',
                'class'  => 'fixed-width-md',
                'placeholder' => 'A1A1A1',
            ),
            array(
                'type'   => 'text',
                'label'  => $this->l('Shipping Point ID'),
                'desc'   => sprintf(
                    $this->l('Mandatory if "Pickup by Canada Post" to "No". If you deposit your items at a Post Office or other Canada Post facility, provide the site number of the deposit location in this field. Look up the site number using %s (select "PARCELS - ANY COMBINATION OF PRODUCTS" AS "Type of Mailing"). This information is used for pricing.'),
                     \CanadaPostPs\Tools::renderHtmlLink('https://www.canadapost.ca/cpotools/apps/fdl/business/findDepositLocation?execution=e1s1', 'Find a Deposit Location', array('target' => '_blank'))
                ),
                'name'   => self::PREFIX . 'SHIPPING_POINT',
                'class'  => 'fixed-width-md',
                'placeholder' => '3102',
            ),
            array(
                'type'   => 'switch',
                'label'  => $this->l('Use Order Reference as "Customer Reference"'),
                'desc'   => $this->l('Choose to use the Order Reference as the "Customer Reference 1" label value to be displayed on the label for an order.'),
                'name'   => self::PREFIX . 'ORDER_ID_REFERENCE',
                'class'  => 't',
                'values' => array(
                    array(
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => $this->l('No')
                    )
                ),
            ),
            array(
                'type'   => 'text',
                'label'  => $this->l('Label Refund Email'),
                'desc'   => $this->l('Enter the email used for label refund notifications. You must enter a valid email to refund refundable labels. Only labels that are not on a manifest are refundable.'),
                'name'   => self::PREFIX . 'REFUND_EMAIL',
                'class'  => 'fixed-width-xl',
                'placeholder' => 'example@email.com',
            ),
            array(
                'type'   => 'switch',
                'label'  => $this->l('Auto-Open Label PDF on Creation'),
                'desc'   => $this->l('Choose to automatically open the label PDF immediately after creating it.'),
                'name'   => self::PREFIX . 'OPEN_LABEL_ON_CREATION',
                'class'  => 't',
                'values' => array(
                    array(
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => $this->l('No')
                    )
                ),
            ),
            array(
                'type'   => 'switch',
                'label'  => $this->l('Include Commercial Invoice in Label PDF'),
                'desc'   => $this->l('Choose to include the commercial invoice in the shipping label PDF for an order for international shipments. '),
                'name'   => self::PREFIX . 'INCLUDE_INVOICE',
                'class'  => 't',
                'values' => array(
                    array(
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => $this->l('No')
                    )
                ),
            ),
        );
    }

    /**
     * @param bool|int $id_order
     * @param array $products
     * */
    public function getCreateLabelFormFieldValues($id_order, $products)
    {
        $values = array();

        $fields = array_merge(
            Method::$labelFields,
            array_map(function ($option) {
                return 'options_'.$option;
            }, array_keys(Method::$options)),
            array_map(function ($option) {
                return 'notification_'.$option;
            }, Method::$notifications)
        );

        // Get saved settings from DB if available
        if ($id_order) {
            $values['id_order']  = Tools::getValue('id_order', $id_order);
            $values['vieworder'] = Tools::getIsset('vieworder') ? true : false;

            $orderLabelSettingsArr = OrderLabelSettings::getOrderLabelSettingForOrderId($id_order);
            if ($orderLabelSettingsArr) {
                $OrderLabelSettings = new OrderLabelSettings($orderLabelSettingsArr['id_order_label_settings']);

                foreach ($fields as $field) {
                    // Change dash to underscore to match DB names
                    $classField = str_replace('-', '_', $field);

                    foreach (OrderLabelSettings::$labelSettings as $labelSetting) {
                        if (property_exists($OrderLabelSettings->{$labelSetting}, $classField)) {
                            $values[$field] = $OrderLabelSettings->{$labelSetting}->{$classField};
                        }
                    }
                }
                $values['items'] = array();
                foreach ($OrderLabelSettings->customsProducts as $product) {
                    $index = sprintf('item[%s]', $product['id_product']);
                    foreach (Method::$customsProductFields as $customsProductField) {
                        $classField = str_replace('-', '_', $customsProductField);
                        $values[$index.'['.$customsProductField.']'] = $product[$classField];
                        $values['items'][$product['id_product']][$customsProductField] = $product[$classField];
                    }
                }

                return $values;
            }
        }

        foreach ($fields as $field) {
            $values[$field] = Tools::getValue($field, self::getConfig($field));
        }

        // Init total weight
        $weight = 0;
        $CAD  = new \Currency(\Currency::getIdByIsoCode('CAD'));

        $productArr = array();
        foreach ($products as $product) {
            if ($product['is_virtual']) {
                continue;
            }


            if ($id_order) {
                // Add product weight
                $weight += Tools::toKg($product['weight']);

                $index = sprintf('item[%s]', $product['id_order_detail']);
                $quantity = Tools::getValue($index.'[customs-number-of-units]', $product['product_quantity']);


                $productArr[$product['id_order_detail']]['customs-description'] = Tools::getValue($index.'[customs-description]', \Tools::substr(
                    $product['product_name'],
                    0,
                    44
                ));
                $productArr[$product['id_order_detail']]['customs-number-of-units'] = $quantity;
                $productArr[$product['id_order_detail']]['hs-tariff-code'] = Tools::getValue($index.'[hs-tariff-code]');
                $productArr[$product['id_order_detail']]['sku'] = Tools::getValue($index.'[sku]', \Tools::substr(
                    $product['reference'],
                    0,
                    14
                ));
                if ($product['weight'] <= 0) {
                    $productArr[$product['id_order_detail']]['unit-weight'] = '0.001';
                } else {
                    $productArr[$product['id_order_detail']]['unit-weight'] = Tools::getValue($index . '[unit-weight]', Tools::toKg($product['weight']));
                }

                $product_price = Tools::convertPriceFull((float)$product['product_price'], $this->context->currency, $CAD);

                $productArr[$product['id_order_detail']]['customs-value-per-unit'] = Tools::getValue($index.'[customs-value-per-unit]', $product_price);
                $productArr[$product['id_order_detail']]['country-of-origin'] = Tools::getValue($index.'[country-of-origin]', false);
                $productArr[$product['id_order_detail']]['province-of-origin'] = Tools::getValue($index.'[province-of-origin]', false);

                foreach ($productArr[$product['id_order_detail']] as $key => $value) {
                    $values[$index.'['.$key.']'] = $value;
                }
            } else {
                foreach (Method::$customsProductFields as $field) {
                    $index          = sprintf('item[%s][%s]', $product['id_order_detail'], $field);
                    $values[$index] = Tools::getValue($index, false);
                }
            }
        }

        if (Tools::getIsset('item')) {
            foreach (Tools::getValue('item') as $id => $item) {
                $productArr[$id] = $item;
            }
        }

        $values['items'] = $productArr;

        // Get box value
        if ($id_default_box = self::getConfig('box')) {
            $selectedBox = new Box($id_default_box);
        }

        if ($id_order) {
            $Order    = new Order($id_order);
            $Address  = new \Address($Order->id_address_delivery);
            $Customer = new \Customer($Order->id_customer);

            $values['id_order']  = Tools::getValue('id_order', $id_order);
            $values['vieworder'] = Tools::getIsset('vieworder') ? true : false;

            // Pack products and get largest required box
            if (!$id_default_box) {
                if (!$Order->isVirtual()) {
                    $API          = new API();
                    $packedBoxArr = $API->packProducts($Order->getCartProducts());
                    /* @var \CanadaPost\BoxPacker\PackedBox $PackedBox */
                    $PackedBox   = end($packedBoxArr);
                    $selectedBox = new Box($PackedBox->getBox()->id);
                    $weight      = Tools::convertUnitFromTo($PackedBox->getWeight(), 'g', 'kg', 3);
                } else {
                    $boxes       = Box::getBoxes();
                    $selectedBox = new Box($boxes[0]['id_box']);
                }
            } else {
                $weight += Tools::convertUnitFromTo($selectedBox->getEmptyWeight(), 'g', 'kg', 3);
            }

            // Get Service Method value
            /* @var Method $Method */
            if ($carrierMapping = CarrierMapping::getCarrierMappingByCarrierId($Order->id_carrier)) {
                $method = Method::getMethod($carrierMapping['id_mapped_carrier']);
                $values['service-code'] = Tools::getValue('service-code', $method['code']);
            } elseif ($method = Method::getMethodByCarrierId($Order->id_carrier)) {
                $values['service-code'] = Tools::getValue('service-code', $method['code']);
            } else {
                $group   = \CanadaPostPs\Method::getMethodGroup(\Country::getIsoById($Address->id_country));
                $methods = Method::getMethods(array('group' => $group, 'active' => 1));
                if (!empty($methods)) {
                    $values['service-code'] = Tools::getValue('service-code', $methods[0]['code']);
                } else {
                    // If no active methods, select first inactive method for group
                    $methods = Method::getMethods(array('group' => $group));
                    $values['service-code'] = Tools::getValue('service-code', $methods[0]['code']);
                }
            }

            // Get Address values
            if ($values['sender'] == 0) {
                $addresses = Address::getAddresses(array('active' => 1));
                $values['sender'] = Tools::getValue('sender', $addresses[0]['id_address']);
            }

            $values['name']                    = Tools::getValue(
                'name',
                sprintf('%s %s', $Address->firstname, $Address->lastname)
            );
            $values['company']                 = Tools::getValue('company', $Address->company);
            $values['address-line-1']          = Tools::getValue('address-line-1', $Address->address1);
            $values['address-line-2']          = Tools::getValue('address-line-2', $Address->address2);
            $values['additional-address-info'] = Tools::getValue('additional-address-info', $Address->other);
            $values['client-voice-number']     = Tools::getValue('client-voice-number', $Address->phone);
            $values['city']                    = Tools::getValue('city', $Address->city);

            $State                = new \State($Address->id_state);
            $state_iso            = \Validate::isLoadedObject($State) ? $State->iso_code : false;
            $values['prov-state'] = Tools::getValue('prov-state', $state_iso);

            $values['country-code']    = Tools::getValue('country-code', \Country::getIsoById($Address->id_country));
            $values['postal-zip-code'] = Tools::getValue('postal-zip-code', $Address->postcode);

            // Email
            $values['email'] = Tools::getValue('email', $Customer->email);

            // Customer Ref
            $reference                = self::getConfig('ORDER_ID_REFERENCE') ? $Order->reference : false;
            $values['customer-ref-1'] = Tools::getValue('customer-ref-1', $reference);

            // Customs
            $Country         = new \Country($Address->id_country);
            $CountryCurrency = new \Currency($Country->id_currency);
            if ($CountryCurrency->iso_code == 0) {
                $CountryCurrency = new \Currency(\Configuration::get('PS_CURRENCY_DEFAULT'));
            }
            $values['currency']                 = Tools::getValue('currency', $CountryCurrency->iso_code);
            $values['conversion-rate-from-cad'] = Tools::getValue(
                'conversion-rate-from-cad',
                number_format($CountryCurrency->conversion_rate, 3, '.', '')
            );
        } else {
            // Get first box
            if (!$id_default_box) {
                $boxes       = Box::getBoxes();
                $selectedBox = new Box($boxes[0]['id_box']);
            }
            $weight += Tools::toKg($selectedBox->weight);

            $values['country-code'] = Tools::getValue('country-code', 'CA');
        }

        // Get group if not submitted
        if (self::getConfig('CONTRACT')) {
            if (!$values['group-id']) {
                $groups = Group::getGroups();
                if (!empty($groups)) {
                    $values['group-id'] = $groups[0]['id_group'];
                }
            }
        } else {
            $values['group-id'] = null;
            $values['contract-id'] = null;
        }

        // Populate box and dimension fields with box values
        $values['box']    = Tools::getValue('box', $selectedBox->id);
        $values['weight'] = Tools::getValue('weight', $weight);
        foreach (array('length', 'width', 'height') as $dimension) {
            $values[$dimension] = Tools::getValue($dimension, Tools::toCm($selectedBox->{$dimension}));
        }

        return $values;
    }

    public function renderShipmentList($title, $where = false, $currentIndex = false)
    {
        $shipments = $where ? Shipment::getShipments($where) : Shipment::getShipments();

        $helper                = new HelperList();
//        $helper->title         = $title;
        $helper->shopLinkType  = '';

        // in 1.7.7+, we use the Symfony request
        $id_order = null;
        if (self::psVersionIsAtLeast('1.7.7') && isset($this->context->container)) {
            // Check if this is an order and get the individual order's URL
            if ($orderId = $this->getRequestStack()->getCurrentRequest()->attributes->get('orderId')) {
                $id_order = $orderId;
            }
        } else {
            $id_order = Tools::getValue('id_order', false);
        }

        $helper->simple_header = $id_order ? true : false;
        $helper->identifier    = Shipment::$definition['primary'];
        $helper->listTotal     = count($shipments);
        $helper->show_toolbar  = false;
        $helper->actions       = $this->getShipmentsActions();
        $helper->bulk_actions  = $this->getShipmentsBulkActions();
        $helper->module        = $this;
        $helper->no_link         = true;
        $helper->table         = Shipment::$definition['table'];
        $helper->token         = Tools::getAdminTokenLite($this->context->controller->controller_name);
        $helper->currentIndex  = $currentIndex ? $currentIndex : AdminController::$currentIndex;
        $helper->base_folder = _PS_BO_ALL_THEMES_DIR_
                               . $this->context->employee->bo_theme . DIRECTORY_SEPARATOR
                               . 'template' . DIRECTORY_SEPARATOR
                               . 'helpers' . DIRECTORY_SEPARATOR
                               . 'list' . DIRECTORY_SEPARATOR;
        $helper->tpl_vars['baseFolder'] = $helper->base_folder;

        return $helper->generateList($shipments, $this->getShipmentFieldsList());
    }



    public function renderReturnShipmentList($title, $where = false, $currentIndex = false)
    {
        $returnShipments = $where ? ReturnShipment::getReturnShipments($where) : ReturnShipment::getReturnShipments();

        $actions = array('printreturn');
        $bulk_actions = array(
            'printreturn' => array(
                'text'    => $this->l('Print selected'),
                'icon'    => 'icon-print'
            )
        );

        $helper                = new HelperList();
//        $helper->title         = $title;
        $helper->shopLinkType  = '';

        // in 1.7.7+, we use the Symfony request
        $id_order = null;
        if (self::psVersionIsAtLeast('1.7.7') && isset($this->context->container)) {
            // Check if this is an order and get the individual order's URL
            if ($orderId = $this->getRequestStack()->getCurrentRequest()->attributes->get('orderId')) {
                $id_order = $orderId;
            }
        } else {
            $id_order = Tools::getValue('id_order', false);
        }
        $helper->simple_header = $id_order ? true : false;
        $helper->identifier    = ReturnShipment::$definition['primary'];
        $helper->listTotal     = count($returnShipments);
        $helper->show_toolbar  = false;
        $helper->actions       = $actions;
        $helper->bulk_actions  = $bulk_actions;
        $helper->module        = $this;
        $helper->no_link         = true;
        $helper->table         = ReturnShipment::$definition['table'];
        $helper->token         = Tools::getAdminTokenLite($this->context->controller->controller_name);
        $helper->currentIndex  = $currentIndex ? $currentIndex : AdminController::$currentIndex;
        $helper->base_folder = _PS_BO_ALL_THEMES_DIR_
                               . $this->context->employee->bo_theme . DIRECTORY_SEPARATOR
                               . 'template' . DIRECTORY_SEPARATOR
                               . 'helpers' . DIRECTORY_SEPARATOR
                               . 'list' . DIRECTORY_SEPARATOR;
        $helper->tpl_vars['baseFolder'] = $helper->base_folder;

        return $helper->generateList($returnShipments, $this->getReturnShipmentFieldsList());
    }

    public function getShipmentFieldsList()
    {
        $methods = Method::getMethods();
        $methodsArr = array();
        foreach ($methods as $method) {
            $methodsArr[$method['code']] = $method['name'];
        }
        $fields_list = array(
            'id_shipment' => array(
                'title'   => $this->l('ID'),
                'type'    => 'text',
                'orderby' => true,
                'search' => true
            ),
            'id_order' => array(
                'title'   => $this->l('Order ID'),
                'type'    => 'text',
                'callback' => 'getOrderLink',
                'callback_object' => $this,
                'remove_onclick' => true,
                'orderby' => true,
                'search' => true
            ),
            'id_batch' => array(
                'title'   => $this->l('Batch'),
                'type'    => 'text',
                'orderby' => true,
                'search' => true
            ),
//            'name' => array(
//                'title'   => $this->l('Name'),
//                'type'    => 'text',
//                'orderby' => true,
//                'search' => true
//            ),
//            'city' => array(
//                'title'   => $this->l('City'),
//                'type'    => 'text',
//                'orderby' => true,
//                'search' => true
//            ),
//            'prov_state' => array(
//                'title'   => $this->l('Province/State'),
//                'type'    => 'text',
//                'orderby' => true,
//                'search' => true
//            ),
//            'country_code' => array(
//                'title'   => $this->l('Country'),
//                'type'    => 'text',
//                'orderby' => true,
//                'search' => true
//            ),
            'postal_zip_code' => array(
                'title'   => $this->l('Postal/Zip Code'),
                'type'    => 'text',
                'orderby' => true,
                'search' => true
            ),
            'service_code' => array(
                'title'   => $this->l('Service Method'),
                'type'    => 'text',
                'callback' => 'getMethodName',
                'callback_object' => $this,
                'type'    => 'select',
                'list' => $methodsArr,
                'filter_key' => 'a!service_code',
                'order_key' => 'gl!name',
                'orderby' => true,
                'search' => true
            ),
            'tracking_pin' => array(
                'title'   => $this->l('Tracking Number'),
                'type'    => 'text',
                'remove_onclick' => true,
                'callback' => 'getTrackingLink',
                'callback_object' => $this,
                'orderby' => true,
                'search' => true
            ),
            'shipment_id' => array(
                'title'   => $this->l('Shipment ID'),
                'type'    => 'text',
                'orderby' => true,
                'search' => true
            ),
            'date_add' => array(
                'title'   => $this->l('Date'),
                'type'    => 'datetime',
                'orderby' => true,
                'search' => true
            ),
        );

        if (self::getConfig('CONTRACT')) {
            $groups = Group::getGroups();
            $groupsArr = array();
            foreach ($groups as $group) {
                $groupsArr[$group['id_group']] = $group['name'];
            }
            $fields_list['id_group'] = array(
                'title'   => $this->l('Group'),
                'type'    => 'select',
                'list' => $groupsArr,
                'filter_key' => 'a!id_group',
                'filter_type' => 'int',
                'order_key' => 'gl!name',
                'callback' => 'getGroupName',
                'callback_object' => $this,
            );
            $fields_list['transmitted'] = array(
                'title'   => $this->l('Transmitted'),
                'type'    => 'bool',
                'callback' => 'formatTransmitted',
                'callback_object' => $this,
            );
        }
        $fields_list['voided'] = array(
            'title'   => $this->l('Voided'),
            'type'    => 'bool',
            'callback' => 'formatVoid',
            'callback_object' => $this,
        );

//        $fields_list['commercial_invoice_link'] = array(
//            'title'   => $this->l('Has Invoice'),
//            'type'    => 'text',
//            'callback' => 'formatCommercialInvoice',
//            'callback_object' => $this,
//            'orderby' => false,
//            'search' => false
//        );


        // Hide id_order column if we're on the Order page
        if (Tools::getIsset('id_order')) {
            unset($fields_list['id_order']);
        }

        return $fields_list;
    }

    public function getReturnShipmentFieldsList()
    {
        $methods = Method::getMethods(array('group' => 'DOM'));
        $methodsArr = array();
        foreach ($methods as $method) {
            $methodsArr[$method['code']] = $method['name'];
        }
        return array(
            'id_return_shipment' => array(
                'title'   => $this->l('ID'),
                'type'    => 'text',
            ),
            'id_order' => array(
                'title'   => $this->l('Order ID'),
                'type'    => 'text',
                'callback' => 'getOrderLink',
                'callback_object' => $this,
                'remove_onclick' => true,
            ),
            'name' => array(
                'title'   => $this->l('Name'),
                'type'    => 'text',
            ),
            'city' => array(
                'title'   => $this->l('City'),
                'type'    => 'text',
            ),
            'province' => array(
                'title'   => $this->l('Province'),
                'type'    => 'text',
            ),
            'postal_code' => array(
                'title'   => $this->l('Postal Code'),
                'type'    => 'text',
            ),
            'service_code' => array(
                'title'   => $this->l('Service Method'),
                'type'    => 'text',
                'callback' => 'getMethodName',
                'callback_object' => $this,
                'type'    => 'select',
                'list' => $methodsArr,
                'filter_key' => 'a!service_code',
                'order_key' => 'gl!name',
                'orderby' => true,
                'search' => true
            ),
            'tracking_pin' => array(
                'title'   => $this->l('Tracking Number'),
                'type'    => 'text',
                'callback' => 'getTrackingLink',
                'callback_object' => $this,
                'remove_onclick' => true,
            ),
            'date_add' => array(
                'title'   => $this->l('Date'),
                'type'    => 'datetime',
            ),
        );
    }

    public function renderViewShipmentsForm($action, $token)
    {
        $fields = array(
            'form' => array(
                'legend'  => array(
                    'title' => $this->l('Sync Shipments With Canada Post'),
                ),
                'description' => $this->l('This sync feature should be used in cases where an error occurred during label creation and the module failed to properly store it, or if the shipments were accidentally deleted from your database. Retrieve any shipments missing from this list by syncing with the Canada Post servers. Note that any shipments that have been voided or transmitted can no longer be retrieved from Canada Post. If there are more than 30 missing shipments, the process could take several minutes to complete due to the Canada Post API limit, please let the page finish loading to let the process complete.'),
                'input' => array(),
                'buttons' => array(
                    array(
                        'type'  => 'submit',
                        'title' => $this->l('Sync with Canada Post'),
                        'name'  => 'submitSyncShipments',
                        'icon'  => 'icon-refresh',
                        'class' => 'btn-primary'
                    ),
                ),
            ),
        );

        $values = array();

        if (self::getConfig('CONTRACT')) {
            $fields_form = array(
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Group'),
                    'name'    => 'group-id',
                    'hint'    => $this->l('Select which group to sync from.'),
                    'options' => array(
                        'id'    => 'id_group',
                        'name'  => 'name',
                        'query' => Group::getGroups(),
                    ),
                ),
            );

            $values['group-id'] = self::getConfig('group-id') ? self::getConfig('group-id') : false;
        } else {
            $fields_form = array(
                array(
                    'type'    => 'date',
                    'label'   => $this->l('From'),
                    'name'    => 'from',
                ),
                array(
                    'type'    => 'date',
                    'label'   => $this->l('To'),
                    'name'    => 'to',
                ),
            );

            $DateTime = new \DateTime();
            $values['to'] = $DateTime->format('Y-m-d');
            $values['from'] = $DateTime->modify('-7 days')->format('Y-m-d');
        }

        $fields['form']['input'] = $fields_form;

        return $this->renderForm(
            $fields,
            'syncShipments',
            false,
            $action,
            $token,
            $values
        );
    }

    public function getShipmentsActions()
    {
        $actions = array('print', 'printCommercialInvoice');
        if (self::getConfig('CONTRACT')) {
            $actions[] = 'void';
        } else {
            $actions[] = 'refund';
        }

        return $actions;
    }

    public function getShipmentsBulkActions()
    {
        $bulk_actions = array(
            'print' => array(
                'text'    => $this->l('Print selected'),
                'icon'    => 'icon-print'
            ),
        );

        if (self::getConfig('CONTRACT')) {
            $bulk_actions['void'] = array(
                'text'    => $this->l('Void selected'),
                'confirm' => $this->l('Void selected items?'),
                'icon'    => 'icon-trash'
            );
        } else {
            $bulk_actions['refund'] = array(
                'text'    => $this->l('Refund selected'),
                'confirm' => $this->l('Refund selected items?'),
                'icon'    => 'icon-dollar'
            );
        }

        return $bulk_actions;
    }

    /*
     * Process form submit for label and shipments
     * */
    public function postProcessShipments($redirectUrl = false)
    {
        $API      = new \CanadaPostPs\API();
        $id_order = null;
        if (self::psVersionIsAtLeast('1.7.7') && isset($this->context->container)) {
            if ($orderId = $this->getRequestStack()->getCurrentRequest()->attributes->get('orderId')) {
                $id_order = $orderId;
            }
        } else {
            $id_order = Tools::getValue('id_order', false);
        }

        if (Tools::isSubmit('submitSaveChanges')) {
            $API->processSubmitSaveChanges();
        }

        if (Tools::isSubmit('submitCreateLabel')) {
            if ($id_order) {
                $Order = new \Order($id_order);
                $products = $Order->getProducts();
            } else {
                $products = array();
            }
            $values = $this->getCreateLabelFormFieldValues($id_order, $products);
            $API->processSubmitCreateLabel($values, $redirectUrl);
        }

        if (Tools::isSubmit('submitCreateReturnLabel')) {
            $API->processSubmitCreateReturnLabel($redirectUrl, $id_order);
        }

        // Print single label
        if (Tools::isSubmit('id_shipment') && Tools::isSubmit('print')) {
            $Shipment = new \CanadaPostPs\Shipment(Tools::getValue('id_shipment'));
            $API->processSubmitPrint(
                $Shipment,
                $this->getLabelsShippingPathLocal(),
                $this->getLabelsShippingPathUri(),
                $Shipment->shipment_id
            );
        }

        // Print single commercial invoice
        if (Tools::isSubmit('id_shipment') && Tools::isSubmit('print_commercial_invoice')) {
            $Shipment = new \CanadaPostPs\Shipment(Tools::getValue('id_shipment'));
            $API->processSubmitPrint(
                $Shipment,
                $this->getLabelsShippingPathLocal(),
                $this->getLabelsShippingPathUri(),
                $Shipment->shipment_id.'_invoice',
                true
            );
        }

        // Print single return label
        if (Tools::isSubmit('id_return_shipment') && Tools::isSubmit('print_return')) {
            $ReturnShipment = new \CanadaPostPs\ReturnShipment(Tools::getValue('id_return_shipment'));
            $API->processSubmitPrint(
                $ReturnShipment,
                $this->getLabelsReturnsPathLocal(),
                $this->getLabelsReturnsPathUri(),
                $ReturnShipment->tracking_pin
            );
        }

        // Refund single label
        if (Tools::isSubmit('id_shipment') && Tools::isSubmit('refund')) {
            $API->processSubmitRefund($redirectUrl);
        }

        // Void single label
        if (Tools::isSubmit('id_shipment') && Tools::isSubmit('void')) {
            $API->processSubmitVoid($redirectUrl);
        }
        // Bulk print
        if (Tools::isSubmit('submitBulkprint'.\CanadaPostPs\Shipment::$definition['table']) &&
             Tools::getIsset(\CanadaPostPs\Shipment::$definition['table'].'Box')
        ) {
            $API->processSubmitBulkPrint('\CanadaPostPs\Shipment', $this->getLabelsShippingPathLocal(), 'shipment_id', $this->getLabelsShippingPathUri());
        }
        if (
            Tools::isSubmit('submitBulkprintreturn'.\CanadaPostPs\ReturnShipment::$definition['table']) &&
            Tools::getIsset(\CanadaPostPs\ReturnShipment::$definition['table'].'Box')
        ) {
            $API->processSubmitBulkPrint('\CanadaPostPs\ReturnShipment', $this->getLabelsReturnsPathLocal(), 'tracking_pin', $this->getLabelsReturnsPathUri());
        }

        // Bulk refund
        if (
            Tools::isSubmit('submitBulkrefund'.\CanadaPostPs\Shipment::$definition['table']) &&
            Tools::getIsset(\CanadaPostPs\Shipment::$definition['table'].'Box')
        ) {
            $API->processSubmitBulkRefund($redirectUrl);
        }

        // Bulk void
        if (
            Tools::isSubmit('submitBulkvoid'.\CanadaPostPs\Shipment::$definition['table']) &&
            Tools::getIsset(\CanadaPostPs\Shipment::$definition['table'].'Box')
        ) {
            $API->processSubmitBulkVoid($redirectUrl);
        }
    }

    public function renderTransmitShipmentForm($action, $token)
    {
        $fields_form = array(
            'form' => array(
                'legend'  => array(
                    'title' => $this->l('Transmit Shipments'),
                ),
                'description' => $this->l('Transmit a group of shipments to generate a manifest.'),
                'input' => array(),
                'buttons' => array(
                    array(
                        'type'  => 'submit',
                        'title' => $this->l('Transmit Group'),
                        'name'  => 'submitTransmitShipments',
                        'icon'  => 'icon-send',
                        'class' => 'btn-primary btn-create-label'
                    ),
                ),
            ),
        );

        $transmitFieldNames = array(
            'group-id',
            'sender',
            'PICKUP',
            'REQUESTED_SHIPPING_POINT',
            'SHIPPING_POINT',
            'intended-method-of-payment',
        );

        // Reuse fields from config/label form and remove unsued ones
        $transmitFields = array_merge(
            $this->getParcelFields(false),
            $this->getAddressFields(false),
            $this->getLabelDefaultFields(),
            $this->getPreferencesFields(false)
        );

        // Remove unused fields and strip module prefix from field names
        foreach ($transmitFields as $key => $field) {
            $transmitFields[$key]['name'] = str_replace(self::PREFIX, '', $field['name']);

            // Replace "Return/From Address" to "Manifest Address"
            if ($field['name'] == 'sender') {
                $transmitFields[$key]['label'] = $this->l('Manifest Address');
            }

            if (!in_array($transmitFields[$key]['name'], $transmitFieldNames)) {
                unset($transmitFields[$key]);
            }
        }

        $fields_form['form']['input'] = $transmitFields;

        return $this->renderForm(
            $fields_form,
            'transmitShipments',
            false,
            $action,
            $token,
            $this->getCreateLabelFormFieldValues(false, array())
        );
    }

    public function getManifestFieldsList()
    {
        return array(
            'id_manifest' => array(
                'title'   => $this->l('ID'),
                'type'    => 'text',
                'orderby' => true,
                'search' => true
            ),
            'poNumber' => array(
                'title'   => $this->l('PO Number'),
                'type'    => 'text',
                'orderby' => false,
                'search' => true
            ),
            'contractId' => array(
                'title'   => $this->l('Contract ID'),
                'type'    => 'text',
                'orderby' => false,
                'search' => true
            ),
            'methodOfPayment' => array(
                'title'   => $this->l('Payment Method'),
                'type'    => 'text',
                'orderby' => false,
                'search' => false
            ),
            'totalCost' => array(
                'title'   => $this->l('Total Cost'),
                'type'    => 'text',
                'callback' => 'formatPrice',
                'callback_object' => $this,
                'orderby' => true,
                'search' => true
            ),
            'manifestDateTime' => array(
                'title'   => $this->l('Manifest Date'),
                'type'    => 'date',
                'orderby' => true,
                'search' => true
            ),
            'date_add' => array(
                'title'   => $this->l('Date Created'),
                'type'    => 'datetime',
                'orderby' => true,
                'search' => true
            ),
        );
    }
    public function renderViewManifestsForm($action, $token)
    {
        $fields = array(
            'form' => array(
                'legend'  => array(
                    'title' => $this->l('Sync Manifests With Canada Post'),
                ),
                'description' => $this->l('This sync feature should be used in cases where an error occurred during manifest creation and the module failed to properly store it, or if the manifests were accidentally deleted from your database. Retrieve any manifests missing from this list by syncing with the Canada Post servers. If there are more than 30 missing manifests, the process could take several minutes to complete due to the Canada Post API limit.'),
                'input' => array(),
                'buttons' => array(
                    array(
                        'type'  => 'submit',
                        'title' => $this->l('Sync with Canada Post'),
                        'name'  => 'submitSyncManifests',
                        'icon'  => 'icon-refresh',
                        'class' => 'btn-primary'
                    ),
                ),
            ),
        );

        $values = array();
        $fields_form = array(
            array(
                'type'    => 'date',
                'label'   => $this->l('From'),
                'name'    => 'from',
            ),
            array(
                'type'    => 'date',
                'label'   => $this->l('To'),
                'name'    => 'to',
            ),
        );

        $DateTime = new \DateTime();
        $values['to'] = $DateTime->format('Y-m-d');
        $values['from'] = $DateTime->modify('-7 days')->format('Y-m-d');


        $fields['form']['input'] = $fields_form;

        return $this->renderForm(
            $fields,
            'syncManifests',
            false,
            $action,
            $token,
            $values
        );
    }

    public function renderTrackingForm($action, $token)
    {
        $fields_form = array(
            'form' => array(
                'legend'  => array(
                    'title' => $this->l('Track Parcel'),
                ),
                'input' => array(
                    array(
                        'type'   => 'text',
                        'label'  => $this->l('Tracking Number'),
                        'name'   => 'tracking-pin',
                        'class'  => 'fixed-width-xl',
                    ),
                ),
                'buttons' => array(
                    array(
                        'type'  => 'submit',
                        'title' => $this->l('Track'),
                        'name'  => 'submitTracking',
                        'icon'  => 'icon-crosshairs',
                        'class' => 'btn-primary'
                    ),
                ),
            ),
        );

        $values = array(
            'tracking-pin' => Tools::getValue('tracking-pin', false)
        );

        return $this->renderForm(
            $fields_form,
            'tracking',
            false,
            $action,
            $token,
            $values
        );
    }

    public function renderPerformanceForm()
    {
        $dirs = array(
            'LabelsShipping' => $this->l('Shipping Label Folder'),
            'LabelsReturns' => $this->l('Return Label Folder'),
            'Batch' => $this->l('Batch/Bulk Label Folder'),
            'Manifests' => $this->l('Manifests Folder'),
        );

        $fields_form = array(
            'form' => array(
                'legend'  => array(
                    'title' => $this->l('Performance & Storage'),
                ),
                'description' => $this->l('To save storage space, consider deleting old shipping label PDFs that you no longer need or moving them off-server. If you have the free "Cron task manager" module by PrestaShop installed and a cron job setup, the module will regularly delete cached front-office rates older than 3 months (cpl_cache & cpl_cache_rate tables). Read how to set up the cron job in the documentation: ') . \CanadaPostPs\Tools::renderHtmlLink(_MODULE_DIR_ . $this->name . '/Readme.html#setting-up-cron-job', 'Setting Up Cron Job', array('target' => '_blank')),
            ),
        );

        $totalSize = 0;
        $inputFields = array();

        $inputFields[] = array(
            'type' => 'html',
            'name' => 'divider',
            'label' => $this->l('Folders'),
            'html_content' => \CanadaPostPs\Tools::renderHtmlTag('hr'),
        );

        foreach ($dirs as $id => $label) {
            $pathLocalMethod = 'get'.$id.'PathLocal';
            $pathUriMethod = 'get'.$id.'PathUri';
            $sizeMb = Tools::getDirectorySize($this->$pathLocalMethod());

            $inputFields[] = array(
                'type' => 'html',
                'name' => $id,
                'label' => $label,
                'html_content' => sprintf('%s MB', \CanadaPostPs\Tools::renderHtmlTag('b', $sizeMb)),
                'desc' => $this->$pathUriMethod()
            );

            $totalSize += $sizeMb;
        }

        $inputFields[] = array(
            'type' => 'html',
            'name' => 'divider',
            'label' => $this->l('Database Tables'),
            'html_content' => \CanadaPostPs\Tools::renderHtmlTag('hr'),
        );

        foreach ($this->getModels() as $className) {
            $Class = $this->getNamespace() . $className;
            $Obj = new $Class();
            $sizeMb = Tools::getTableSize($Obj::$definition['table']);
            if (!$sizeMb) {
                continue;
            }

            $totalSize += $sizeMb;

            $inputFields[] = array(
                'type' => 'html',
                'name' => $className,
                'label' => $className,
                'html_content' => sprintf('%s MB', \CanadaPostPs\Tools::renderHtmlTag('b', $sizeMb)),
                'desc' => 'Table: '.$Obj::$definition['table']
            );
        }

        $inputFields[] = array(
            'type' => 'html',
            'name' => 'total_size',
            'label' => $this->l('Total Storage'),
            'html_content' => sprintf(
                '%s%s MB',
                \CanadaPostPs\Tools::renderHtmlTag('hr'),
                \CanadaPostPs\Tools::renderHtmlTag('b', $totalSize, array('style' => 'color: #38b326;'))
            ),
            'desc' => $this->l('Total amount of storage spaced used by the module.'),
        );

        $fields_form['form']['input'] = $inputFields;

        $values = array(
            'tracking-pin' => Tools::getValue('tracking-pin', false)
        );

        return $this->renderForm(
            $fields_form,
            'performance',
            false,
            false,
            true,
            $values
        );
    }

    /*
     * Return group name for HelperList callback
     * */
    public function getGroupName($id_group)
    {
        $Group = new Group($id_group);
        if (\Validate::isLoadedObject($Group)) {
            return $Group->name;
        } else {
            return '';
        }
    }

    /*
     * Return tracking link HelperList callback
     * */
    public function getTrackingLink($tracking_number)
    {
        $url = Tools::str_replace_once('@', $tracking_number, Method::$tracking_url);
        return \CanadaPostPs\Tools::renderHtmlLink(
            $url, $tracking_number, array('target' => '_blank')
        );
    }
    /*
     * Return order link HelperList callback
     * */
    public function getOrderLink($id_order)
    {
        if ($id_order > 0) {
            return \CanadaPostPs\Tools::renderHtmlLink(
                $this->getAdminLink(
                    'AdminOrders',
                    true,
                    '',
                    array('id_order' => $id_order, 'vieworder' => true)
                ), $id_order
            );
        } else {
            return '';
        }
    }
    /*
     * Return order link HelperList callback
     * */
    public function getErrorMessage($id_order_error)
    {
        if ($id_order_error > 0) {
            return \CanadaPostPs\Tools::renderHtmlLink(
                $this->getAdminLink(
                    $this->context->controller->controller_name,
                    true,
                    '',
                    array('id_order_error' => $id_order_error, 'viewerror' => 1)
                ),
                \CanadaPostPs\Icon::getIconHtml('exclamation-circle') . ' Error',
                array('class' => 'order-error-link')
            );
        } else {
            return '';
        }
    }

    /*
     * Return order link HelperList callback
     * */
    public function getBatchErrorMessage($id_batch)
    {
        if ($id_batch > 0) {
            $orderErrors = OrderError::getOrderErrors(array('id_batch' => $id_batch));
            $count = count($orderErrors);
            return \CanadaPostPs\Tools::renderHtmlLink(
                $this->getAdminLink(
                    $this->context->controller->controller_name,
                    true,
                    '',
                    array('id_batch' => $id_batch, 'viewbatcherrors' => 1)
                ),
                \CanadaPostPs\Icon::getIconHtml('exclamation-circle') . $count . ($count > 1 ? ' Errors' : ' Error'),
                array('class' => 'order-error-link')
            );
        } else {
            return '';
        }
    }

    /*
     * Return full service name from service code
     * */
    public function getMethodName($serviceCode)
    {
        if ($Method = Method::getMethodByCode($serviceCode)) {
            return $Method['name'];
        } else {
            return $serviceCode;
        }
    }

    /*
     * Make voided shipment red
     * */
    public function formatVoid($void)
    {
        if ($void == 1) {
            return \CanadaPostPs\Tools::renderHtmlTag('span', $this->l('Voided'), array('class' => 'badge badge-danger'));
        } else {
            return '';
        }
    }

    /*
     * Make voided shipment red
     * */
    public function formatTransmitted($transmitted)
    {
        if ($transmitted == 1) {
            return \CanadaPostPs\Tools::renderHtmlTag('span', $this->l('Yes'), array('class' => 'badge'));
        } else {
            return '';
        }
    }

    /*
     * Add invoice icon for shipments with invoices
     * */
    public function formatCommercialInvoice($link)
    {
        if (!empty($link)) {
            return \CanadaPostPs\Icon::getIconHtml('file-text');
        } else {
            return '';
        }
    }

    /*
     * Format price for manifest total
     * */
    public function formatPrice($price)
    {
        $currency = new \Currency(\Currency::getIdByIsoCode('CAD'));

        return \Tools::displayPrice($price, $currency);
    }

    /*
     * Format rate for bulk labels
     * */
    public function formatLiveRate($price, $row)
    {
        $currency = new \Currency(\Currency::getIdByIsoCode('CAD'));

        $link =  \CanadaPostPs\Tools::renderHtmlLink(
            $this->getAdminLink(
                'AdminCanadaPostLabelsCreateLabel',
                true,
                '',
                array('id_order' => $row['id_order'])
            ),
            \CanadaPostPs\Icon::getIconHtml('refresh') . ($price ? \Tools::displayPrice($price, $currency) : ''),
            array('class' => 'btn-fetch-bulk-rate')
        );

        return $link;
    }
}
