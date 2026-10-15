<?php

/**
 * 2023 - Keyrnel
 *
 * NOTICE OF LICENSE
 *
 * The source code of this module is under a commercial license.
 * Each license is unique and can be installed and used on only one shop.
 * Any reproduction or representation total or partial of the module, one or more of its components,
 * by any means whatsoever, without express permission from us is prohibited.
 * If you have not received this module from us, thank you for contacting us.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @author    Keyrnel
 * @copyright 2023 - Keyrnel
 * @license   commercial
 * International Registered Trademark & Property of Keyrnel
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HTMLTemplateGiftCardCore extends HTMLTemplate
{
    public $data;
    public $id_lang;

    public function __construct($data, $smarty)
    {
        $this->data = $data;
        $this->smarty = $smarty;
        $this->id_lang = isset($this->data['lang_id']) ? $this->data['lang_id'] : 0;
        $this->date = Tools::displayDate(date('Y-m-d H:i:s'));
        $this->available_in_your_account = false;
        $this->title = $this->data['module']->l('Gift card', 'HTMLTemplateGiftCard', $this->id_lang);
        $this->setShopId();
    }

    protected function setShopId()
    {
        if (isset($this->data['shop_id']) && $this->data['shop_id']) {
            $id_shop = (int) $this->data['shop_id'];
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

        $width = 0;
        $height = 0;
        if (!empty($logo)) {
            list($width, $height) = getimagesize(_PS_IMG_DIR_ . $logo);
        }

        // Limit the height of the logo for the PDF render
        $maximum_height = 100;
        if ($height > $maximum_height) {
            $ratio = $maximum_height / $height;
            $height *= $ratio;
            $width *= $ratio;
        }

        $this->smarty->assign([
            'logo_path' => version_compare(_PS_VERSION_, '1.7', '>=')
                ? Tools::getShopProtocol() . Tools::getMediaServer(_PS_IMG_ . $logo) . _PS_IMG_ . $logo
                : _PS_IMG_DIR_ . $logo,
            'logo_width' => $width,
            'logo_height' => $height,
        ]);

        return $this->smarty->fetch($this->getTemplate('header'));
    }

    public function getContent()
    {
        $this->smarty->assign([
            'image' => $this->getImageData(),
            'translations' => $this->getTranslations(),
            'style' => $this->smarty->fetch($this->getTemplate('style')),
        ]);

        return $this->smarty->fetch($this->getTemplate('content'));
    }

    public function getImageData()
    {
        return [
            'url' => $this->data['image_url'],
            'width' => (int) $this->data['image_width'],
            'height' => (int) $this->data['image_height'],
        ];
    }

    public function getTranslations()
    {
        return [
            'headline' => isset($this->data['beneficiary']) || isset($this->data['customer'])
                ? sprintf($this->data['module']->l('Hi %s', 'HTMLTemplateGiftCard', $this->id_lang), isset($this->data['beneficiary']) ? $this->data['beneficiary'] : $this->data['customer'])
                : $this->data['module']->l('Hi', 'HTMLTemplateGiftCard', $this->id_lang),
            'subhead' => isset($this->data['beneficiary']) && isset($this->data['customer'])
                ? sprintf($this->data['module']->l('You received a gift card worth %s from %s', 'HTMLTemplateGiftCard', $this->id_lang), $this->data['giftcard_amount'], $this->data['customer'])
                : sprintf($this->data['module']->l('Here is your gift card worth %s', 'HTMLTemplateGiftCard', $this->id_lang), $this->data['giftcard_amount']),
            'alt' => $this->data['module']->l('gift_card', 'HTMLTemplateGiftCard', $this->id_lang),
            'text' => isset($this->data['giftcard_message']) ? $this->data['giftcard_message'] : null,
            'information_headline' => $this->data['module']->l('Information about the gift card', 'HTMLTemplateGiftCard', $this->id_lang),
            'information_1' => sprintf($this->data['module']->l('Amount : %s', 'HTMLTemplateGiftCard', $this->id_lang), $this->data['giftcard_amount']),
            'information_2' => sprintf($this->data['module']->l('Code : %s', 'HTMLTemplateGiftCard', $this->id_lang), $this->data['giftcard_code']),
            'information_3' => sprintf($this->data['module']->l('Expiry date : %s', 'HTMLTemplateGiftCard', $this->id_lang), $this->data['giftcard_expiration']),
            'howto_headline' => $this->data['module']->l('How to use the gift card', 'HTMLTemplateGiftCard', $this->id_lang),
            'howto_content' => sprintf($this->data['module']->l('Available %s from the purchase date, the gift card is usable throughout the store %s (%s). It can be used in several times, including sale seasons, and can be combined with another payment method. To use the gift card, simply copy/paste the code above during the payment process of your next order.', 'HTMLTemplateGiftCard', $this->id_lang), $this->data['giftcard_expiration_date'], $this->data['shop_name'], $this->data['shop_url']),
        ];
    }

    public function getPagination()
    {
        return '';
    }

    public function getBulkFilename()
    {
        return 'gift_card.pdf';
    }

    public function getFilename()
    {
        return $this->data['module']->l('gift_card', 'HTMLTemplateGiftCard', $this->id_lang) . '.pdf';
    }

    protected function getTemplate($template_name)
    {
        $template = false;
        $default_template = _PS_MODULE_DIR_ . 'thegiftcard/views/templates/admin/pdf/' . $template_name . '.tpl';
        $overridden_template = _PS_THEME_DIR_ . 'modules/thegiftcard/views/templates/admin/pdf/' . $template_name . '.tpl';
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
}
