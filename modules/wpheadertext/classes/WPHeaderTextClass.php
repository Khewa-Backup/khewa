<?php
/**
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class WPHeaderTextClass extends ObjectModel
{
    /**p @var integer wpheadertext id */
    public $id;

    /** @var integer wpheadertext id shop */
    public $id_shop;

    public $wpheadertext_file;

    /** @var string wpheadertext_image_link */
    public $wpheadertext_image_link;

    /** @var string wpheadertext_text */
    public $wpheadertext_text;

    /** @var string box_bg */
    public $box_bg;
    
    /** @var string box_brd */
    public $box_brd;
        
    /** @var string cls_btn */
    public $cls_btn;

    /** @var string icon_c */
    public $icon_c;
    
    /** @var string icon_bg */
    public $icon_bg;

    /** @var string icon_c */
    public $icon_c_h;
    
    /** @var string icon_bg_h */
    public $icon_bg_h;

    /** @var string icon_btn_bg */
    public $icon_btn_bg;

    /** @var string icon_btn_c */
    public $icon_btn_c;

    /** @var string icon_btn_c_h */
    public $icon_btn_c_h;

    /** @var string icon_btn_bg_h */
    public $icon_btn_bg_h;

    /** @var string mobile_sw */
    public $mobile_sw;

    /** @var string active */
    public $active;
    
    /** @var string googleyn */
    public $googleyn;

    /** @var string wp_google_link */
    public $wp_google_link;

    /** @var string wp_google_name */
    public $wp_google_name;
    
    /** @var string text_size */
    public $text_size;

    /** @var string text_size_mobile */
    public $text_size_mobile;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'wpheadertext',
        'primary' => 'id_wpheadertext',
        'multilang' => true,
        'fields' => array(
        'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
        'wpheadertext_image_link' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isUrl'),
        'wpheadertext_text' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
        'mobile_sw' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        'active' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        'googleyn' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        'wpheadertext_file' => array('type' => self::TYPE_STRING, 'validate' => 'isFileName', 'required' => false),
        'text_size' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
        'text_size_mobile' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
        'wp_google_link' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        'wp_google_name' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        'box_bg' => array('type' => self::TYPE_STRING, 'validate' => 'isColor'),
        'box_brd' => array('type' => self::TYPE_STRING, 'validate' => 'isColor'),
        'cls_btn' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        'icon_c' => array('type' => self::TYPE_STRING, 'validate' => 'isColor'),
        'icon_bg' => array('type' => self::TYPE_STRING, 'validate' => 'isColor'),
        'icon_c_h' => array('type' => self::TYPE_STRING, 'validate' => 'isColor'),
        'icon_bg_h' => array('type' => self::TYPE_STRING, 'validate' => 'isColor'),
        'icon_btn_bg' => array('type' => self::TYPE_STRING, 'validate' => 'isColor'),
        'icon_btn_c' => array('type' => self::TYPE_STRING, 'validate' => 'isColor'),
        'icon_btn_bg_h' => array('type' => self::TYPE_STRING, 'validate' => 'isColor'),
        'icon_btn_c_h' => array('type' => self::TYPE_STRING, 'validate' => 'isColor'),
        )
    );

    public static function getByIdShop($id_shop)
    {
        $id = Db::getInstance()->getValue('SELECT `id_wpheadertext` FROM `'._DB_PREFIX_.'wpheadertext` WHERE `id_shop` ='.(int)$id_shop);
        return new WPHeaderTextClass($id);
    }

    public function copyFromPost()
    {
        /* Classic fields */
        $classic_field = array('active', 'mobile_sw', 'googleyn', 'wpheadertext_file', 'text_size', 'text_size_mobile', 'wp_google_link', 'wp_google_name', 'box_bg', 'box_brd', 'cls_btn', 'icon_c', 'icon_bg', 'icon_c_h', 'icon_bg_h', 'icon_btn_bg', 'icon_btn_c', 'icon_btn_bg_h', 'icon_btn_c_h');
        foreach ($classic_field as $key => $value) {
            if (isset($key, $this) && $key != 'id_'.$this->table) {
                $this->{$key} = $value;
            }
        }

        /* Multilingual fields */
        if (count($this->fieldsValidateLang)) {
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                foreach ($this->fieldsValidateLang as $field => $validation) {
                    if (Tools::getIsset($field.'_'.(int)$language['id_lang'])) {
                        $this->{$field}[(int)$language['id_lang']] = Tools::getValue($field.'_'.(int)$language['id_lang']);
                    }
                }
            }
        }
    }
}
