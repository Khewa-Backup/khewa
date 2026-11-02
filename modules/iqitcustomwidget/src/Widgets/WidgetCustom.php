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

namespace Elementor;

if ( ! defined( 'ELEMENTOR_ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Hello widget class.
 *
 * This is a sample widget for developers.
 */

class WidgetCustom extends Widget_Base
{
    public function get_id() {
        return 'widget-custom';
    }

    public function get_title() {
        return \IqitElementorWpHelper::__( 'Custom Widget sample', 'elementor' );
    }

    public function get_icon() {
        return 'image-box';
    }

    /**
     * Do not edit widget category
    */
    public function get_categories()
    {
        $category = 'custom';
        return [$category];
    }

    protected function _register_controls() {
        $this->add_control(
            'section_title',
            [
                'label' => \IqitElementorWpHelper::__( 'HTML Code', 'elementor' ),
                'type' => Controls_Manager::SECTION,
            ]
        );

        $this->add_control(
            'html',
            [
                'label' => '',
                'type' => Controls_Manager::TEXTAREA,
                'default' => '',
                'description' => \IqitElementorWpHelper::__( 'DO NOT use it for CSS or JS codes. Only clean HTML will work. For more advance code please use "custom tpl" widget', 'elementor' ),
                'placeholder' => \IqitElementorWpHelper::__( 'Enter your embed code here', 'elementor' ),
                'section' => 'section_title',
                'show_label' => false,
            ]
        );

        $this->add_control(
            'section_image',
            [
                'label' => \IqitElementorWpHelper::__( 'Image', 'elementor' ),
                'type' => Controls_Manager::SECTION,
            ]
        );
        
        $this->add_control(
            'image',
            [
                'label' => \IqitElementorWpHelper::__( 'Choose Image', 'elementor' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => UtilsElementor::get_placeholder_image_src(),
                ],
                'section' => 'section_image',
            ]
        );

        $this->add_control(
            'section_style_image',
            [
                'type'  => Controls_Manager::SECTION,
                'label' => \IqitElementorWpHelper::__( 'Image', 'elementor' ),
                'tab'   => self::TAB_STYLE,
            ]
        );

        $this->add_control(
            'space',
            [
                'label' => \IqitElementorWpHelper::__( 'Size (px)', 'elementor' ),
                'type' => Controls_Manager::SLIDER,
                'tab' => self::TAB_STYLE,
                'section' => 'section_style_image',
                'default' => [
                    'size' => 100,
                    'unit' => 'px',
                ],
                'size_units' => [ 'px' ],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-widget-custom-image img' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label' => \IqitElementorWpHelper::__( 'Alignment', 'elementor' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => \IqitElementorWpHelper::__( 'Left', 'elementor' ),
                        'icon' => 'align-left',
                    ],
                    'center' => [
                        'title' => \IqitElementorWpHelper::__( 'Center', 'elementor' ),
                        'icon' => 'align-center',
                    ],
                    'right' => [
                        'title' => \IqitElementorWpHelper::__( 'Right', 'elementor' ),
                        'icon' => 'align-right',
                    ],
                ],
                'default' => 'center',
                'tab' => self::TAB_STYLE,
                'section' => 'section_style_image',
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ],
            ]
        );

    }
    
    /**
     * Content generation for frontend 
    */
    protected function render( $instance = [] ) {

        if ( !empty( $instance['image']['url'] ) ) {
            $image_width =  $instance['image']['width'] ? 'width="' .\IqitElementorWpHelper::absint( $instance['image']['width']). '"' : '';
            $image_height = $instance['image']['height'] ? 'height="' .\IqitElementorWpHelper::absint( $instance['image']['height']). '"' : '';
            $image_class_html = ' class="img-fluid"';
    
            $image_html = sprintf( '<img src="%s" %s %s   %s />', \IqitElementorWpHelper::esc_attr( \IqitElementorWpHelper::getImage($instance['image']['url'])), $image_width, $image_height, $image_class_html );
    
            
            $image_html =  '<div class="elementor-widget-custom-image">'. $image_html . '</div>';
    
            echo $image_html;
        }
        
        echo '<div class="elementor-widget-custom-html">'. $instance['html']. '</div>';
    }

    /**
     * Content generation for editor - you can empty it if you want, then widget will be refresesh via ajax after each change(there maybe some delay)
     * To not use it please create empty method:
     * protected function content_template() {}
    */
    protected function content_template() {
        ?>
        <# if ( '' !== settings.image.url ) { #>
            <div class="elementor-widget-custom-image">
            <#
                var imgClass   = 'img-fluid', 
                    image_html = 'aa';

                image_html = '<img src="' + settings.image.url + '"    ' + (settings.image.width ? 'width="' + settings.image.width + '" ' : '')  +  (settings.image.height ? 'height="' + settings.image.height + '" ' : '') + '    class="' + imgClass + '" />';
                print( image_html );
                #>
            </div>
        <# } #>
        <div class="elementor-widget-custom-html"> {{{ settings.html }}}</div>
        <?php
    }

}
