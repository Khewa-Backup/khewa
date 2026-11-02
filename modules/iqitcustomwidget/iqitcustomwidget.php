<?php
/**
 * 2017 IQIT-COMMERCE.COM
 *
 * NOTICE OF LICENSE
 *
 * @author    IQIT-COMMERCE.COM <support@iqit-commerce.com>
 * @copyright 2017 IQIT-COMMERCE.COM
 * @license   GNU General Public License version 2
 *
 * You can not resell or redistribute this software.
 */


if (!defined('_PS_VERSION_')) {
    exit;
}

class IqitCustomWidget extends Module
{

    public function __construct()
    {
        $this->name = 'iqitcustomwidget';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'IQIT-COMMERCE.COM';
        $this->ps_versions_compliancy = ['min' => '8.0.0'];
        $this->displayName = $this->l('IQITCUSTOMWIDGET');
        $this->description = $this->l('Example module of custom widget for elementor page builder');

        parent::__construct();
    }


    public function install()
    {
        return parent::install() && $this->registerHook('actionRegisterElementorWidget') && $this->registerHook('displayHeader');
    }

    /**
     * Place module on elementor
     * You can register multiple widgets
     */
    public function hookActionRegisterElementorWidget($params)
    {
        $widgets_manager = $params['widgets_manager'];

        //repeat below lines for each widget you want to add
        include _PS_MODULE_DIR_ . $this->name . '/src/Widgets/WidgetCustom.php';
        $widgets_manager->register_widget('Elementor\WidgetCustom');
    }

    /**
     * Place to put assets
     */
    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/widget-custom.css');
        $this->context->controller->addJS($this->_path . 'views/js/widget-custom.js');
    }
}
