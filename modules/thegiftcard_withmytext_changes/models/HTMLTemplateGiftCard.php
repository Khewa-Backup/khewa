<?php
/**
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class HTMLTemplateGiftCardCore extends HTMLTemplate
{
    public $data;

    public function __construct($data = array(), $smarty)
    {
        $this->data = $data;
        $this->smarty = $smarty;
        $this->date = Tools::displayDate(date('Y-m-d H:i:s'));
        $this->available_in_your_account = false;
        $this->title = self::l('Gift card');
        $this->setShopId();
    }

    protected function setShopId()
    {
        if (isset($this->data['shop_id']) && $this->data['shop_id']) {
            $id_shop = (int)$this->data['shop_id'];
        } else {
            $id_shop = (int) Context::getContext()->shop->id;
        }

        $this->shop = new Shop($id_shop);
        if (Validate::isLoadedObject($this->shop)) {
            Shop::setContext(Shop::CONTEXT_SHOP, (int) $this->shop->id);
        }
    }

    protected function getLogo()
    {
        $id_shop = (int) $this->shop->id;

        $invoiceLogo = Configuration::get('PS_LOGO_INVOICE', null, null, $id_shop);
        if ($invoiceLogo && file_exists(_PS_IMG_DIR_ . $invoiceLogo)) {
            return $invoiceLogo;
        }

        $logo = Configuration::get('PS_LOGO', null, null, $id_shop);
        if ($logo && file_exists(_PS_IMG_DIR_ . $logo)) {
            return $logo;
        }

        return null;
    }

    public function getHeader()
    {
        $logo = $this->getLogo();
        $width = $height = 0;

        if (!empty($path_logo)) {
            list($width, $height) = getimagesize($path_logo);
        }

        $this->smarty->assign(array(
           'logo_path' => _PS_IMG_DIR_ . $logo,
           'logo_width' => $width,
           'logo_height' => $height,
       ));

        return $this->smarty->fetch($this->getTemplate('header'));
    }

    public function getContent()
    {
        $this->smarty->assign(array(
            'data' => $this->data,
            'style' => $this->smarty->fetch($this->getTemplate('style')),
        ));

        return $this->smarty->fetch($this->getTemplate('content'));
    }

    public function getPagination()
    {
        return;
    }

    public function getBulkFilename()
    {
        return 'gift_card.pdf';
    }

    public function getFilename()
    {
        return self::l('gift_card').'.pdf';
    }

    protected function getTemplate($template_name)
    {
        $template = false;
        $default_template = _PS_MODULE_DIR_.'thegiftcard/views/templates/pdf/'.$template_name.'.tpl';
        $overridden_template = _PS_THEME_DIR_.'modules/thegiftcard/views/templates/pdf/'.$template_name.'.tpl';
        if (file_exists($overridden_template)) {
            $template = $overridden_template;
        } elseif (file_exists($default_template)) {
            $template = $default_template;
        }

        if ($template) {
            return $template;
        }

        return parent::getTemplate($template_name);
    }

    protected static function l($string)
    {
        return Translate::getModuleTranslation('thegiftcard', $string, 'htmltemplategiftcard');
    }
}
