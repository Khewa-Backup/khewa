<?php

namespace CrazyElements;

use CrazyElements\PrestaHelper;
use CrazyElements\Widget_Base;

if (!defined('_PS_VERSION_')) {
    exit; // Exit if accessed directly.
}

class Widget_ProductSingleActions extends Widget_Base
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
        return 'product_single_actions';
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
        return PrestaHelper::__('Product Actions', 'elementor');
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
        return 'ceicon-product-action-widget';
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
            'section_title',
            array(
                'label' => PrestaHelper::__('General', 'elementor'),
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
                'selector' => '{{WRAPPER}} .product-actions span',
            )
        );

        $this->add_control(
            'crazy_single_product_actions_color',
            array(
                'label'     => PrestaHelper::__('Label Color', 'elementor'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .product-actions span' => 'color: {{VALUE}}',
                ),
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

            $out_put .= \Context::getcontext()->smarty->fetch(_PS_MODULE_DIR_ . 'crazyelements/views/templates/front/crazy_single_product_actions.tpl');
            echo $out_put;
        } else {
            echo 'Here will be Actions';
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