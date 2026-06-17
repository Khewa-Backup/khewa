<?php

namespace CrazyElements;

use CrazyElements\PrestaHelper;
use CrazyElements\Widget_Base;

if (!defined('_PS_VERSION_')) {
    exit; // Exit if accessed directly.
}

class Widget_ProductSingleTabs extends Widget_Base
{


    /**
     * Get widget name.
     *
     * Retrieve accordion widget name.
     *
     * @since  1.0.0
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'product_single_tabs';
    }

    /**
     * Get widget title.
     *
     * Retrieve accordion widget title.
     *
     * @since  1.0.0
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return PrestaHelper::__('Product Tabs', 'elementor');
    }

    /**
     * Get widget icon.
     *
     * Retrieve accordion widget icon.
     *
     * @since  1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon()
    {
        return 'ceicon-product-tabs-widget';
    }

    public function get_categories()
    {
        return array('products_layout');
    }

    /**
     * Register accordion widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function _register_controls()
    {
        $this->start_controls_section(
            'menu_style',
            array(
                'label' => PrestaHelper::__('Menu Style', 'elementor'),
            )
        );

        $this->add_control(
            'title',
            array(
                'label'       => PrestaHelper::__('Title', 'elementor'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => array(
                    'active' => true,
                ),
                'label_block' => true,
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'crazy_single_product_actions_typography',
                'label'    => PrestaHelper::__('Typography', 'elementor'),
                'selector' => '{{WRAPPER}} .crazy_elements_tab_sec ul li a',
            )
        );

        $this->add_control(
            'crazy_single_product_actions_color',
            array(
                'label'     => PrestaHelper::__('Label Color', 'elementor'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .crazy_elements_tab_sec ul li a' => 'color: {{VALUE}}',
                ),
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'desc_style',
            array(
                'label' => PrestaHelper::__('Content Style', 'elementor'),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'crazy_single_product_label_typography',
                'label'    => PrestaHelper::__('Label', 'elementor'),
                'selector' => '{{WRAPPER}} .crazy_elements_tab_sec label, {{WRAPPER}} .crazy_elements_tab_sec #product-details .label',
            )
        );

        $this->add_control(
            'crazy_single_product_actions_desc_color',
            array(
                'label'     => PrestaHelper::__('Label Color', 'elementor'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .crazy_elements_tab_sec label, {{WRAPPER}} .crazy_elements_tab_sec #product-details .label' => 'color: {{VALUE}}',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'crazy_single_product_text_typography',
                'label'    => PrestaHelper::__('Text', 'elementor'),
                'selector' => '{{WRAPPER}} .crazy_elements_tab_sec',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render accordion widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function render()
    {

        if (PrestaHelper::is_admin()) {
            return;
        }

        $controller_name = \Tools::getValue('controller');
        if ($controller_name == "product") {
            $settings     = $this->get_settings_for_display();
            $title        = $settings['title'];

            $out_put = '';
            $id_product = (int)\Tools::getValue('id_product');

            \Context::getcontext()->smarty->assign(
                array(
                    'show_flag' => "yes",
                    'jqZoomEnabled' => \Configuration::get('PS_DISPLAY_JQZOOM')
                )
            );

            $out_put .= \Context::getcontext()->smarty->fetch(_PS_MODULE_DIR_ . 'crazyelements/views/templates/front/crazy_single_product_tabs.tpl');
            echo $out_put;
        } else {
            echo 'Here will be Tabs';
        }
    }

    /**
     * Render accordion widget output in the editor.
     *
     * Written as a Backbone JavaScript template and used to generate the live preview.
     *
     * @since  1.0.0
     * @access protected
     */
    protected function _content_template()
    {
    }
}