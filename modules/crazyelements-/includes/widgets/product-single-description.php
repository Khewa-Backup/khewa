<?php

namespace CrazyElements;

use CrazyElements\PrestaHelper;
use CrazyElements\Widget_Base;

if (!defined('_PS_VERSION_')) {
    exit; // Exit if accessed directly.
}

class Widget_ProductSingleDescription extends Widget_Base
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
        return 'product_single_description';
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
        return PrestaHelper::__('Product Description', 'elementor');
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
        return 'ceicon-product-description-widget';
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
            'general',
            array(
                'label' => PrestaHelper::__('General', 'elementor'),
            )
        );
        $this->add_control(
            'description_type',
            array(
                'label'      => PrestaHelper::__('Type', 'plugin-domain'),
                'type'       => Controls_Manager::SELECT,
                'options'    => array(
                    'description'       => 'Default',
                    'short_description' => 'Short',
                ),
                'default'    => 'short_description',
            )
        );
        $this->end_controls_section();

        // Style Tab
        $this->start_controls_section(
            'style',
            array(
                'label' => PrestaHelper::__('Content', 'elecounter'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'crazy_single_product_desc_color',
            array(
                'label'     => PrestaHelper::__('Color', 'elementor'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .crazy_single_product_desc, {{WRAPPER}} .crazy_single_product_desc p' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'crazy_single_product_desc_typography',
                'label'    => PrestaHelper::__('Title Typography', 'elecounter'),
                'selector' => '{{WRAPPER}} .crazy_single_product_desc, {{WRAPPER}} .crazy_single_product_desc p',
            )
        );

        $this->end_controls_section();
        // Style Tab
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
        $settings           = $this->get_settings_for_display();
        $description_type   = $settings['description_type'];
        if (PrestaHelper::is_admin()) {
            return;
        }
        $controller_name = \Tools::getValue('controller');
        if ($controller_name == "product") {
            $out_put = '';
            $id_product = (int)\Tools::getValue('id_product');

            \Context::getcontext()->smarty->assign(
                array(
                    'show_flag' => "yes",
                    'jqZoomEnabled' => \Configuration::get('PS_DISPLAY_JQZOOM')
                )
            );

            if ($description_type == 'description') {
                $out_put .= \Context::getcontext()->smarty->fetch(_PS_MODULE_DIR_ . 'crazyelements/views/templates/front/crazy_single_product_description.tpl');
            } else {
                $out_put .= \Context::getcontext()->smarty->fetch(_PS_MODULE_DIR_ . 'crazyelements/views/templates/front/crazy_single_product_short_description.tpl');
            }
            echo $out_put;
        } else {
            $out_put = "<div class=\"crazy_single_product_desc\">Here will be $description_type </div>";
            echo $out_put;
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