<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium, is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

class AdminCanadaPostLabelsCreateBulkLabelsController extends ModuleAdminController
{
    protected $statuses_array = array();

    protected $carriers_array = array();

    protected $methods_array = array();

    public function __construct()
    {
        $this->identifier = 'id_order';
        $this->table = 'orders';
        $this->className = 'Order';

        parent::__construct();

        $this->_select = '
		a.id_currency,
		a.id_order AS id_pdf,
		CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
		osl.`name` AS `osname`,
		os.`color`,
		ca.`name` AS caname,
		CASE
		    WHEN cacherate.`rate` IS NOT NULL THEN cacherate.`rate`
            ELSE ""
        END AS rate,
		CASE
		    WHEN olcarrier.`name` IS NOT NULL THEN olcarrier.`name`
		    WHEN mappedmethod.`name` IS NOT NULL THEN mappedmethod.`name`
		    WHEN ordermethod.`name` IS NOT NULL THEN ordermethod.`name`
		    WHEN activegroupmethod.`name` IS NOT NULL THEN activegroupmethod.`name`
		    WHEN groupmethod.`name` IS NOT NULL THEN groupmethod.`name`
            ELSE null
        END AS mappedname,
		oe.`id_order_error` AS ordererror,
		IF((SELECT ols.id_order_label_settings FROM `' . _DB_PREFIX_ . \CanadaPostPs\OrderLabelSettings::$definition['table'] . '` ols WHERE ols.id_order = a.id_order LIMIT 1) > 0, 1, 0) as modified,
		IF((SELECT coe.id_order_error FROM `' . _DB_PREFIX_ . \CanadaPostPs\OrderError::$definition['table'] . '` coe WHERE coe.id_order = a.id_order LIMIT 1) > 0, 1, 0) as iserror,
		IF((SELECT so.id_order FROM `' . _DB_PREFIX_ . 'orders` so WHERE so.id_customer = a.id_customer AND so.id_order < a.id_order LIMIT 1) > 0, 0, 1) as new,
		country_lang.name as cname,
		state.iso_code as sisocode,
		IF(a.valid, 1, 0) badge_success';

        $this->_join = '
		LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)
		INNER JOIN `' . _DB_PREFIX_ . 'address` address ON address.id_address = a.id_address_delivery
		INNER JOIN `' . _DB_PREFIX_ . 'country` country ON address.id_country = country.id_country
		INNER JOIN `' . _DB_PREFIX_ . 'country_lang` country_lang ON (country.`id_country` = country_lang.`id_country` AND country_lang.`id_lang` = ' . (int) $this->context->language->id . ')
		INNER JOIN `' . _DB_PREFIX_ . 'state` state ON address.id_state = state.id_state
		LEFT JOIN `' . _DB_PREFIX_ . 'order_carrier` oc ON (oc.`id_order` = a.`id_order`)
		LEFT JOIN `' . _DB_PREFIX_ . 'carrier` ca ON (ca.`id_carrier` = oc.`id_carrier`)
		LEFT JOIN `' . _DB_PREFIX_ . \CanadaPostPs\OrderLabelSettings::$definition['table'] . '` ols ON (ols.`id_order` = a.`id_order`)
		LEFT JOIN `' . _DB_PREFIX_ . \CanadaPostPs\OrderLabelParcel::$definition['table'] . '` olp ON (olp.`id_order_label_settings` = ols.`id_order_label_settings`)
		LEFT JOIN `' . _DB_PREFIX_ . \CanadaPostPs\OrderError::$definition['table'] . '` oe ON (oe.`id_order` = a.`id_order`)
		LEFT JOIN `' . _DB_PREFIX_ . \CanadaPostPs\CarrierMapping::$definition['table'] . '` cm ON (cm.`id_carrier` = oc.`id_carrier`)
		LEFT JOIN `' . _DB_PREFIX_ . \CanadaPostPs\Method::$definition['table'] . '` mappedmethod ON (mappedmethod.`id_method` = cm.`id_mapped_carrier`)
		LEFT JOIN `' . _DB_PREFIX_ . \CanadaPostPs\Method::$definition['table'] . '` olcarrier ON (olcarrier.`code` = olp.`service_code`)
		LEFT JOIN `' . _DB_PREFIX_ . \CanadaPostPs\Method::$definition['table'] . '` ordermethod ON (ordermethod.`id_carrier` = ca.`id_carrier`)
		LEFT JOIN (
		    SELECT * FROM `' . _DB_PREFIX_ . \CanadaPostPs\Method::$definition['table'] . '`
		    WHERE `active` = 1
		    GROUP BY `group`
		) activegroupmethod ON (activegroupmethod.`group` = (
		    CASE
		        WHEN country.`iso_code` = "CA" THEN "DOM"
		        WHEN country.`iso_code` = "US" THEN "USA"
		        ELSE "INT"
		    END
		))
		LEFT JOIN (
		    SELECT * FROM `' . _DB_PREFIX_ . \CanadaPostPs\Method::$definition['table'] . '`
		    GROUP BY `group`
		) groupmethod ON (groupmethod.`group` = (
		    CASE
		        WHEN country.`iso_code` = "CA" THEN "DOM"
		        WHEN country.`iso_code` = "US" THEN "USA"
		        ELSE "INT"
		    END
		))
		LEFT JOIN `' . _DB_PREFIX_ . \CanadaPostPs\Cache::$definition['table'] . '` cache ON (cache.`id_cart` = a.`id_cart`)
		LEFT JOIN `' . _DB_PREFIX_ . \CanadaPostPs\CacheRate::$definition['table'] . '` cacherate ON (
		    cacherate.`id_cache` = cache.`id_cache`
		    AND cacherate.`id_carrier` = (
                CASE
                    WHEN olcarrier.`id_carrier` IS NOT NULL THEN olcarrier.`id_carrier`
                    WHEN mappedmethod.`id_carrier` IS NOT NULL THEN mappedmethod.`id_carrier`
                    WHEN ordermethod.`id_carrier` IS NOT NULL THEN ordermethod.`id_carrier`
                    WHEN activegroupmethod.`id_carrier` IS NOT NULL THEN activegroupmethod.`id_carrier`
                    WHEN groupmethod.`id_carrier` IS NOT NULL THEN groupmethod.`id_carrier`
                    ELSE NULL
                END
		    )
		)
		LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = a.`current_state`)
		LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $this->context->language->id . ')';

        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        $module = $this->module;
        // Don't select orders with an excluded order status
        if ($excludedStatuses = \CanadaPostPs\Tools::getMultiSelectValues($module::getConfig('EXCLUDE_ORDER_STATUSES'))) {
            foreach ($excludedStatuses as $excludedStatus) {
                unset($this->statuses_array[$excludedStatus]);
            }

            $this->_where .= '
            AND a.current_state NOT IN (' . implode(', ', $excludedStatuses) . ')
            ';
        }

        $carriers = Carrier::getCarriers(
            (int) $this->context->language->id,
            true,
            false,
            false,
            null,
            Carrier::ALL_CARRIERS
        );
        foreach ($carriers as $carrier) {
            $this->carriers_array[$carrier['id_carrier']] = $carrier['name'];
        }

        $methods = \CanadaPostPs\Method::getMethods();
        foreach ($methods as $method) {
            $this->methods_array[$method['id_method']] = $method['name'];
        }

        $Forms = new \CanadaPostPs\Forms();

        $this->fields_list = array(
            'id_order' => array(
                'title'   => $this->module->l('Order'),
                'type'    => 'text',
                'class' => 'fixed-width-xs',
                'orderby' => true,
                'search' => true
            ),
            'customer' => array(
                'title'   => $this->module->l('Customer'),
                'havingFilter' => true,
            ),
            'total_paid_tax_incl' => array(
                'title'   => $this->module->l('Total'),
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'badge_success' => true,
            ),
            'caname' => array(
                'title'   => $this->module->l('Order Carrier'),
                'type' => 'select',
                'list' => $this->carriers_array,
                'filter_key' => 'ca!id_carrier',
                'filter_type' => 'int',
                'order_key' => 'caname',
                'hint' => $this->module->l('The carrier that the customer chose when placing their order.'),
            ),
            'mappedname' => array(
                'title'   => $this->module->l('Label Carrier'),
                'type' => 'select',
                'list' => $this->methods_array,
                'hint' => $this->module->l('Map your PrestaShop carriers to Canada Post carriers in the module configuration page to pre-select them for label creation and bulk label creation. Modifications to order label settings will override the Carrier Mappings.'),
                'orderby' => false,
                'search' => false,
                'filter_key' => false,
            ),
            'total_shipping_tax_incl' => array(
                'title'   => $this->module->l('Shipping Paid'),
                'type' => 'price',
                'currency' => true,
                'hint' => $this->module->l('Shipping amount paid by the customer (tax included).'),
                'class' => 'fixed-width-xs',
            ),
            'rate' => array(
                'title'   => $this->module->l('Live Rate'),
                'type' => 'text',
                'callback' => 'formatLiveRate',
                'callback_object' => $Forms,
                'hint' => $this->module->l('Real-time Canada Post rate for Label Carrier if available. Click the refresh icons to fetch new rates. Rates are tax incl/excl depending on your Rates preferences in the module configuration page. If an error occurs, refresh the page and click on the "Error" link for the order to view the message.'),
                'class' => 'fixed-width-xs',
            ),
            'ordererror' => array(
                'title'   => $this->module->l('Error'),
                'type' => 'bool',
                'class' => 'fixed-width-xs',
                'callback' => 'getErrorMessage',
                'callback_object' => $Forms,
                'havingFilter' => true,
                'filter_key' => 'iserror',
                'filter_type' => 'int',
                'hint' => $this->module->l('Errors that occurred during label creation. Click the red "Error" link to view the error message.'),
            ),
            'modified' => array(
                'title'   => $this->module->l('Modified'),
                'type' => 'bool',
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
                'hint' => $this->module->l('Whether the label settings for an order have been modified.'),
            ),
            'osname' => array(
                'title'   => $this->module->l('Status'),
                'type' => 'select',
                'color' => 'color',
                'list' => $this->statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname',
            ),
            'date_add' => array(
                'title'   => $this->module->l('Date'),
                'align' => 'text-right',
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ),
        );

        if (Country::isCurrentlyUsed('country', true)) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT DISTINCT c.id_country, cl.`name`, s.id_state, s.iso_code
			FROM `' . _DB_PREFIX_ . 'orders` o
			' . Shop::addSqlAssociation('orders', 'o') . '
			INNER JOIN `' . _DB_PREFIX_ . 'address` a ON a.id_address = o.id_address_delivery
			INNER JOIN `' . _DB_PREFIX_ . 'country` c ON a.id_country = c.id_country
			INNER JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = ' . (int) $this->context->language->id . ')
			INNER JOIN `' . _DB_PREFIX_ . 'state` s ON a.id_state = s.id_state
			ORDER BY cl.name ASC');

            $country_array = array();
            $state_array = array();
            foreach ($result as $row) {
                $country_array[$row['id_country']] = $row['name'];
                $state_array[$row['id_state']] = $row['iso_code'];
            }

            $part1 = array_slice($this->fields_list, 0, 3);
            $part2 = array_slice($this->fields_list, 3);
            $part1['cname'] = array(
                'title'   => $this->module->l('Country'),
                'type' => 'select',
                'list' => $country_array,
                'filter_key' => 'country!id_country',
                'filter_type' => 'int',
                'order_key' => 'cname',
            );
            $part1['sisocode'] = array(
                'title'   => $this->module->l('State'),
                'type' => 'select',
                'list' => $state_array,
                'filter_key' => 'state!id_state',
                'filter_type' => 'int',
                'order_key' => 'sisocode',
            );
            $this->fields_list = array_merge($part1, $part2);
        }

        $this->_orderBy = 'id_order';
        $this->_orderWay = 'DESC';
        $this->list_no_link = true;

        $this->actions = array('editlabel', 'vieworder');
        $this->bulk_actions = array(
            'createlabel' => array(
                'text'    => $this->module->l('Create Labels for Selected'),
                'icon'    => 'icon-print'
            )
        );

        $this->bootstrap = true;

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
        }
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function initContent()
    {
        parent::initContent();

        if (!$this->module->isConnected() || !$this->module->isVerified()) {
            $this->errors[] = $this->module->l(\CanadaPostPs\Tools::$error_messages['CONNECT_ACCOUNT']);
            return false;
        }

        $this->informations[] = \CanadaPostPs\Tools::renderHtmlLink(
            _MODULE_DIR_ . $this->module->name . '/Readme.html#bulk-labels',
            $this->module->l('Bulk Labels Documentation'),
            array('target' => '_blank')
        );
        $this->informations[] = $this->module->l('Create a batch of labels by selecting multiple orders and clicking "Bulk actions" > "Create Labels for Selected".');
        $this->informations[] = $this->module->l('Re-print batches of labels on the "View Batches" page.');
        $this->informations[] = $this->module->l('Creating large amounts of labels may take several minutes to complete, please allow the page to finish loading and do not refresh/cancel the page load.');
        $this->informations[] = $this->module->l('View the module documentation/configuration if you reach your Canada Post API limit ("Rejected by SLM Monitor") or server timeout limit when creating large amounts of labels.');

        $Forms = new \CanadaPostPs\Forms();

        // Process form submits
        $Forms->postProcessShipments($this->context->link->getAdminLink($this->controller_name));

        // modal.tpl template vars
        $this->modals[] = array(
            'modal_id' => 'labelModal',
            'modal_title' => \CanadaPostPs\Tools::renderHtmlTag('h4', $this->module->l('Print Label')),
            'modal_content' => \CanadaPostPs\Tools::renderHtmlTag(
                    'div', null, array('class' => 'modal-body')
                ) . $this->module->logo,
            'modal_actions' => true,
            'modal_class' => 'zhmedia-modal'
        );

        // modal.tpl template vars
        $this->modals[] = array(
            'modal_id' => 'errorModal',
            'modal_title' => \CanadaPostPs\Tools::renderHtmlTag('h4', $this->module->l('Error Message')),
            'modal_content' => \CanadaPostPs\Tools::renderHtmlTag(
                    'div', null, array('class' => 'modal-body')
                ) . $this->module->logo,
            'modal_actions' => true,
            'modal_class' => 'zhmedia-modal'
        );

        // modal.tpl template vars
        $this->modals[] = array(
            'modal_id' => 'editLabelModal',
            'modal_title' => \CanadaPostPs\Tools::renderHtmlTag('h4', $this->module->l('Edit Label Settings')),
            'modal_content' => \CanadaPostPs\Tools::renderHtmlTag(
                    'div', null, array('class' => 'modal-body')
                ) . $this->module->logo,
            'modal_actions' => true,
            'modal_class' => 'zhmedia-modal'
        );
    }

    public function postProcess()
    {
        // Process error messages
        if (Tools::isSubmit('id_order_error') && Tools::isSubmit('viewerror')) {
            $OrderError = new \CanadaPostPs\OrderError(Tools::getValue('id_order_error'));
            $response = array();
            if (Validate::isLoadedObject($OrderError)) {
                $error = sprintf(
                    '%s %s %s %s',
                    \CanadaPostPs\Tools::renderHtmlTag(
                        'h4',
                        $this->module->l('Error creating label for Order ID') . ' ' . $OrderError->id_order . ':'
                    ),
                    $OrderError->errorMessage,
                    \CanadaPostPs\Tools::renderHtmlTag('br') . \CanadaPostPs\Tools::renderHtmlTag('br'),
                    $this->module->l('Please fix the errors and try again.')
                );

                if (Tools::getIsset('ajax')) {
                    $response['response'] = $this->module->displayError($error);
                    die(json_encode($response));
                }

                $this->errors[] = $OrderError->errorMessage;
            } else {
                if (Tools::getIsset('ajax')) {
                    $response['error'] = $this->module->l('Unable to retrieve error message');
                    die(json_encode($response));
                }
            }
        }

        if (Tools::isSubmit('id_order') && Tools::isSubmit('editlabel') && Tools::getIsset('ajax')) {
            echo $this->getEditForm();
            die();
        }

        if (Tools::isSubmit('id_order') && Tools::isSubmit('updateOrderRate') && Tools::getIsset('ajax')) {
            echo $this->getEditForm();
            die();
        }


        // Bulk print
        if (Tools::isSubmit('submitBulkcreatelabel'.Order::$definition['table']) &&
            Tools::getIsset(Order::$definition['table'].'Box')
        ) {
            $API = new \CanadaPostPs\API();
            $API->processSubmitBulkCreateLabel();
        }

        parent::postProcess();
    }

    public function getEditForm()
    {
        $Forms = new \CanadaPostPs\Forms();

        $this->context->smarty->assign(array(
            'forms' => array(
                'createLabel' => $Forms->renderCreateLabelForm(
                    \Tools::getValue('id_order'),
                    $this->context->link->getAdminLink($this->controller_name)
                ),
            ),
            'form_tabs' => false
        ));

        return $this->context->smarty->fetch(sprintf(_PS_MODULE_DIR_.'%s/views/templates/hook/forms.tpl', $this->module->name));
    }

    public function renderList()
    {
        $output = '';

        if (Tools::isSubmit('id_order') && Tools::isSubmit('editlabel')) {
            $output .= $this->getEditForm();
        }

        return $output . parent::renderList() . $this->module->logo;
    }

    public function displayCreateLabelLink($token, $id, $name)
    {
        return $this->module->displayCreateLabelLink($token, $id, $name);
    }

    public function displayEditLabelLink($token, $id, $name)
    {
        $helper = new HelperList();
        $helper->module = $this->module;
        $tpl = $helper->createTemplate('list_action_button.tpl');

        $tpl->assign(array(
            'href' => $this->module->getAdminLink($this->controller_name, true, '', array('id_order' => $id, 'editlabel' => true)),
            'action' => $this->module->l('Edit Label Settings'),
            'name' => $name,
            'icon' => 'gear',
            'target' => false,
            'class' => 'edit-label-settings'
        ));

        return $tpl->fetch();
    }

    public function displayViewOrderLink($token, $id, $name)
    {
        $helper = new HelperList();
        $helper->module = $this->module;
        $tpl = $helper->createTemplate('list_action_button.tpl');

        $tpl->assign(array(
            'href' => $this->module->getAdminLink('AdminOrders', true, '', array('id_order' => $id, 'vieworder' => true)),
            'action' => $this->module->l('View Order'),
            'name' => $name,
            'icon' => 'search-plus',
            'target' => '_blank'
        ));

        return $tpl->fetch();
    }
}
