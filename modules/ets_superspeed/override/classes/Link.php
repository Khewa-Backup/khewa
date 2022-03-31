<?php
/**
 * 2007-2020 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

class Link extends LinkCore
{
    public function getImageLink($name, $ids, $type = null)
    {
        $notDefault = false;
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $moduleManagerBuilder = PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder::getInstance();
            $moduleManager = $moduleManagerBuilder->build();
            static $watermarkLogged = null;
            static $watermarkHash = null;
            static $psLegacyImages = null;
            if ($watermarkLogged === null) {
                $watermarkLogged = Configuration::get('WATERMARK_LOGGED');
                $watermarkHash = Configuration::get('WATERMARK_HASH');
                $psLegacyImages = Configuration::get('PS_LEGACY_IMAGES');
            }
            if (!empty($type) && $watermarkLogged &&
                ($moduleManager->isInstalled('watermark') && $moduleManager->isEnabled('watermark')) &&
                isset(Context::getContext()->customer->id)
            ) {
                $type .= '-' . $watermarkHash;
            }
        }
        else
        {
            if (($type != '') && Configuration::get('WATERMARK_LOGGED') && (Module::isInstalled('watermark') && Module::isEnabled('watermark')) && isset(Context::getContext()->customer->id)) {
                $type .= '-'.Configuration::get('WATERMARK_HASH');
            }
            $psLegacyImages =Configuration::get('PS_LEGACY_IMAGES');
        }    
        $is_webp = false;
        $theme = ((Shop::isFeatureActive() && file_exists(_PS_PROD_IMG_DIR_ . $ids . ($type ? '-' . $type : '') . '-' . Context::getContext()->shop->theme_name . '.jpg')) ? '-' . Context::getContext()->shop->theme_name : '');
        if (($psLegacyImages
                && (file_exists(_PS_PROD_IMG_DIR_ . $ids . ($type ? '-' . $type : '') . $theme . '.jpg')))
            || ($notDefault = strpos($ids, 'default') !== false)) {
            if ($this->allow == 1 && !$notDefault) {
                $uriPath = __PS_BASE_URI__ . $ids . ($type ? '-' . $type : '') . $theme . '/' . $name . '.jpg';
            } else {
                $uriPath = _THEME_PROD_DIR_ . $ids . ($type ? '-' . $type : '') . $theme . '.jpg';
            }
            if(file_exists(_PS_PROD_IMG_DIR_ . $ids . ($type ? '-' . $type : '')  . '.webp'))
                $is_webp = true;
        } else {
            $splitIds = explode('-', $ids);
            $idImage = (isset($splitIds[1]) ? $splitIds[1] : $splitIds[0]);
            $theme = ((Shop::isFeatureActive() && file_exists(_PS_PROD_IMG_DIR_ . Image::getImgFolderStatic($idImage) . $idImage . ($type ? '-' . $type : '') . '-' . (int) Context::getContext()->shop->theme_name . '.jpg')) ? '-' . Context::getContext()->shop->theme_name : '');
            if ($this->allow == 1) {
                $uriPath = __PS_BASE_URI__ . $idImage . ($type ? '-' . $type : '') . $theme . '/' . $name . '.jpg';
            } else {
                $uriPath = _THEME_PROD_DIR_ . Image::getImgFolderStatic($idImage) . $idImage . ($type ? '-' . $type : '') . $theme . '.jpg';
            }
            if(file_exists(_PS_PROD_IMG_DIR_ . Image::getImgFolderStatic($idImage) . $idImage . ($type ? '-' . $type : '') . '.webp'))
                $is_webp = true;
        }
        if($is_webp)
        {
            $url = $this->protocol_content . Tools::getMediaServer($uriPath) . $uriPath;
            return str_replace('.jpg','.webp',$url);
        }
        else
            return $this->protocol_content . Tools::getMediaServer($uriPath) . $uriPath;
    }
}