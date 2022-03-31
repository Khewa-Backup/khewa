<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
class psgiftcardsGiftcardsModuleFrontController extends ModuleFrontController
{
    /**
     * Override property definition from parent class
     *
     * @var Psgiftcards
     */
    public $module;

    /**
     * @var bool
     */
    public $display_column_right;

    /**
     * @var bool
     */
    public $display_column_left;

    public function initContent()
    {
        $this->display_column_right = false;
        $this->display_column_left = false;
        $context = Context::getContext();
        if (empty($context->customer->id)) {
            Tools::redirect('index.php');
        }

        parent::initContent();

        $ps_version = (bool) version_compare(_PS_VERSION_, '1.7', '>=');

        $params = [
            'token' => hash('md5', $context->customer->secure_key),
        ];

        $this->context->smarty->assign([
            'front_controller' => Context::getContext()->link->getModuleLink('psgiftcards', 'Giftcards', $params, true),
            'pdf_controller' => Context::getContext()->link->getModuleLink('psgiftcards', 'GiftcardsPdf', $params, true),
            'ps_version' => (bool) version_compare(_PS_VERSION_, '1.7', '>='),
            'gfLang' => count(GiftcardHistory::getGiftcardsMailsLangbyLang($context->language->id)),
        ]);

        if ($ps_version === true) { // if on ps 17
            $this->setTemplate('module:psgiftcards/views/templates/front/customerAccountDetails17.tpl');
        } else { // if on ps 16
            $this->setTemplate('customerAccountDetails16.tpl');
        }
    }

    public function setMedia()
    {
        $js_path = _PS_MODULE_DIR_ . 'psgiftcards/views/js/';
        $css_path = _PS_MODULE_DIR_ . 'psgiftcards/views/css/';

        parent::setMedia();
        $this->context->controller->addJS($js_path . 'customerDetails.js');
        $this->context->controller->addJS($js_path . 'vue.min.js');
        $this->context->controller->addCSS($css_path . 'customerDetails.css');
    }

    /**
     * getBreadcrumbLinks
     *
     * @return array
     */
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();

        return $breadcrumb;
    }

    public function displayAjaxSaveGiftcard()
    {
        $aGiftcard = Tools::getValue('giftcard');
        $this->checkToken($aGiftcard['id']);

        $validate = $this->checkFields($aGiftcard);
        if ($validate !== true) {
            exit(json_encode($validate));
        }

        $aErrors = [];

        $oGiftcard = new GiftcardHistory((int) $aGiftcard['id']);
        $oGiftcard->type = (int) $aGiftcard['type'];
        $oGiftcard->sendLater = (int) $aGiftcard['sendLater'];
        $oGiftcard->recipientName = pSQL($aGiftcard['recipientName']);
        $oGiftcard->buyerName = pSQL($aGiftcard['buyerName']);
        $oGiftcard->text = $aGiftcard['text'];

        if ($aGiftcard['type'] == 1) {
            $oGiftcard->recipientMail = pSQL($aGiftcard['recipientMail']);
        }
        if ($aGiftcard['sendLater'] == 1) {
            $oGiftcard->send_date = date('Y-m-d', strtotime($aGiftcard['send_date']));
        } else {
            $oGiftcard->send_date = date('Y-m-d');
        }

        $oGiftcard->id_state = 6; // 6 = status save
        $oGiftcard->save();

        exit(json_encode('success update'));
    }

    public function displayAjaxRefreshGiftcardList()
    {
        $context = Context::getContext();
        $id_customer = $context->customer->id;
        $id_shop = $context->shop->id;
        $id_lang = $context->language->id;

        $giftcards = [];
        $giftcardList = GiftcardHistory::getGiftcardsHistoryByCustomer($id_customer, $id_shop);
        foreach ($giftcardList as $giftcard) {
            // get order details
            $order = new Order($giftcard['id_order']);
            $orderState = new OrderState($order->current_state);
            $orderStateName = $orderState->name[$id_lang];

            // get currency details
            $currency = new Currency($order->id_currency);
            $currencySymbol = $currency->sign;

            // get product details
            $link = new Link();
            $product = new Product($giftcard['id_product']);
            $product_link = $product->link_rewrite[$id_lang];
            $product_img = $product->getImages($id_lang);
            $image_link = [];
            // $protocol = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://');
            foreach ($product_img as $item) {
                $image = $link->getImageLink($product_link, $item['id_image']);
                $pos = strpos($image, '/');
                $image_link[] = Tools::substr($link->getImageLink($product_link, $item['id_image']), $pos, 100);
            }

            /** @var Psgiftcards $module */
            $module = $this->module;

            // set state
            switch ($giftcard['id_state']) {
                case '1':
                    $state = $module->awaiting_validation;
                    break;
                case '2':
                    $state = $module->to_configure;
                    break;
                case '3':
                    $state = $module->scheduled;
                    break;
                case '4':
                    $state = $module->downloaded;
                    break;
                case '5':
                    $state = $module->sent;
                    break;
                case '6':
                    $state = $module->to_configure;
                    break;
                default:
                    $state = '';
            }

            $send_date = date('d-m-Y', strtotime($giftcard['send_date']));

            // regroup all details
            array_push($giftcards, [
                'id' => (int) $giftcard['id_giftcard_history'],
                'orderRef' => $order->reference,
                'type' => $giftcard['type'],
                'sendLater' => $giftcard['sendLater'],
                'purchaseDate' => date('d-m-Y', strtotime($order->date_add)),
                'currency' => $currencySymbol,
                'amount' => $giftcard['amount'],
                'buyerName' => $giftcard['buyerName'],
                'recipientName' => $giftcard['recipientName'],
                'recipientMail' => $giftcard['recipientMail'],
                'text' => htmlspecialchars($giftcard['text']),
                'send_date' => $send_date,
                'payment' => $orderStateName,
                'id_state' => $giftcard['id_state'],
                'status' => $state,
                'image' => $giftcard['image'],
                'image_link' => $image_link,
                'id_customer' => $giftcard['id_customer'],
                'hasChanged' => 0,
            ]);
        }
        exit(json_encode($giftcards));
    }

    public function checkFields($giftcard)
    {
        $translations = $this->module->frontControllerTranslations;
        $errors = [];

        $context = Context::getContext();
        $id_shop = $context->shop->id;

        $id_giftcard = (int) $giftcard['id'];
        $buyerName = $giftcard['buyerName'];
        $recipientName = $giftcard['recipientName'];

        $errors[$id_giftcard]['validate'] = true;
        if (!empty($recipientName)) {
            $errors[$id_giftcard]['validateRecipientName'] = true;
        } else {
            $errors[$id_giftcard]['validateRecipientName'] = $translations['recipientNameEmpty'];
            $errors[$id_giftcard]['validate'] = false;
        }

        if (!empty($buyerName)) {
            $errors[$id_giftcard]['validateBuyerName'] = true;
        } else {
            $errors[$id_giftcard]['validateBuyerName'] = $translations['buyerNameEmpty'];
            $errors[$id_giftcard]['validate'] = false;
        }

        $recipientMail = $giftcard['recipientMail'];
        $send_date = $giftcard['send_date'];

        $type = $giftcard['type'];
        $sendLater = $giftcard['sendLater'];
        if ($type == 1) {
            if (!empty($recipientMail)) {
                if (Validate::isEmail($recipientMail)) {
                    $errors[$id_giftcard]['recipientMail'] = true;
                } else {
                    $errors[$id_giftcard]['recipientMail'] = $translations['recipientMailError'];
                    $errors[$id_giftcard]['validate'] = false;
                }
            } else {
                $errors[$id_giftcard]['recipientMail'] = $translations['recipientMailEmpty'];
                $errors[$id_giftcard]['validate'] = false;
            }

            if ($sendLater == 1) {
                if (!empty($recipientMail)) {
                    if (Validate::isEmail($recipientMail)) {
                        $errors[$id_giftcard]['recipientMail'] = true;
                    } else {
                        $errors[$id_giftcard]['recipientMail'] = $translations['recipientMailError'];
                        $errors[$id_giftcard]['validate'] = false;
                    }
                } else {
                    $errors[$id_giftcard]['recipientMail'] = $translations['recipientMailEmpty'];
                    $errors[$id_giftcard]['validate'] = false;
                }
                $send_date = date('Y-m-d', strtotime($send_date));
                if (!empty($send_date)) {
                    if ($send_date < date('Y-m-d')) {
                        $errors[$id_giftcard]['sendDate'] = $translations['invalidDate'];
                        $errors[$id_giftcard]['validate'] = false;
                    } elseif (Validate::isDate($send_date)) {
                        $errors[$id_giftcard]['sendDate'] = true;
                    } else {
                        $errors[$id_giftcard]['sendDate'] = $translations['notValidDate'];
                        $errors[$id_giftcard]['validate'] = false;
                    }
                } else {
                    $errors[$id_giftcard]['recipientMail'] = $translations['provideDate'];
                    $errors[$id_giftcard]['validate'] = false;
                }
            }
        }

        if ($errors[$id_giftcard]['validate']) {
            $errors = [];
        }

        if (empty($errors)) {
            return true;
        } else {
            return $errors;
        }
    }

    public function displayAjaxSendMail()
    {
        /** @var Psgiftcards $module */
        $module = Module::getInstanceByName('psgiftcards');
        $translations = $module->frontControllerTranslations;

        $context = Context::getContext();
        $id_shop = $context->shop->id;
        $shop = $context->shop->getAddress();
        $id_lang = $context->language->id;
        $id_giftcard = (int) Tools::getValue('id_giftcard');
        $this->checkToken($id_giftcard);
        $cartRule = $this->module->generateCartRule($id_giftcard);

        $giftcard = GiftcardHistory::getGiftcardById($id_giftcard);

        if ($giftcard['id_state'] != 6) {
            return;
        }
        $currencySymbol = $context->currency->sign;
        $price = str_replace('.00', '', number_format(Product::getPriceStatic($giftcard['id_product'], true), 2)) . $currencySymbol;

        $customer = new Customer((int) $giftcard['id_customer']);

        $giftcardsMailLang = GiftcardHistory::getGiftcardsMailsLang();

        $subject = $text = $cta = $unsubscribe = false;
        $iso_lang = 'en';
        foreach ($giftcardsMailLang as $gcml) {
            if ($gcml['id_lang'] == $id_lang) {
                $subject = $gcml['email_subject'];
                $text = $gcml['email_content'];
                $cta = $gcml['email_cta'];
                $unsubscribe = $gcml['email_unsubscribe'];
                $img_name = $gcml['email_discount'];
                $iso_lang = $gcml['lang_iso'];
            }
        }

        $text = str_replace('{buyer_name}', $giftcard['buyerName'], $text);
        $text = str_replace('{recipient_name}', $giftcard['recipientName'], $text);
        $text = str_replace('{buyer_message}', '<div class="gift-message-content" style="background:#f1f3f5;background-color:#f1f3f5;Margin:0px auto;max-width:600px;height:150px;padding-top:15px;">' . $giftcard['text'] . '</div>', $text);
        $text = str_replace('{gift_card_value}', $giftcard['amount'], $text);
        $text = str_replace('{gift_card_validity}', $cartRule['validity'], $text);
        $text = str_replace('{discount_value}', $giftcard['amount'], $text);
        $text = str_replace('{discount_validity}', $cartRule['validity'], $text);
        $text = str_replace('{discount_code}', $cartRule['code'], $text);
        $text = str_replace('{shop_link}', Configuration::get('PS_SHOP_DOMAIN'), $text);
        $text = str_replace('{site_url}', Configuration::get('PS_SHOP_DOMAIN'), $text);
        $unsubscribe = str_replace('{shop_link}', Configuration::get('PS_SHOP_DOMAIN'), $unsubscribe);

        $content = html_entity_decode($text);
        $image = '';
        if (!empty($img_name) && $img_name != 'To configure') {
            $image = _PS_BASE_URL_ . $module->img_path . 'DL/' . $img_name;
        }
        $color1 = Configuration::get('PS_GIFCARDS_PRIMARY_COLOR');
        $color2 = Configuration::get('PS_GIFCARDS_SECONDARY_COLOR');
        if (strpos($color1, '#') === false) {
            $color1 = '#' . $color1;
        }
        if (strpos($color2, '#') === false) {
            $color2 = '#' . $color2;
        }

        $data = [
            '{{color1}}' => $color1,
            '{{color2}}' => $color2,
            '{{last_message}}' => $unsubscribe,
            '{{blabla}}' => $translations['giftCard'],
            '{{shopName}}' => Configuration::get('PS_SHOP_NAME'),
            '{{recipientName}}' => $giftcard['recipientName'],
            '{{buyerName}}' => $giftcard['buyerName'],
            '{{text}}' => $giftcard['text'],
            '{{validity}}' => $cartRule['validity'],
            '{{cart_rule_code}}' => $cartRule['code'],
            '{{price}}' => $price,
            '{{site_url}}' => Configuration::get('PS_SHOP_DOMAIN'),
            '{{shop_addr1}}' => $shop->address1,
            '{{shop_addr2}}' => $shop->address2,
            '{{shop_zipcode}}' => $shop->postcode,
            '{{shop_city}}' => $shop->city,
            '{{shop_country}}' => $shop->address2,
            '{{shop_phone}}' => $shop->phone,
            '{{shop_fax}}' => $shop->address2,
            '{{imdage_url}}' => _PS_BASE_URL_ . $module->img_path . 'giftcardMail.png',
            '{{shop_name}}' => Configuration::get('PS_SHOP_NAME'),
            '{{shop_logo}}' => _PS_BASE_URL_ . '/img/' . Configuration::get('PS_LOGO'),
            '{{discount_code}}' => $cartRule['code'],
            '{{content}}' => $content,
            '{{CTA}}' => $cta,
            '{{img_link}}' => $image,
        ];

        $dir = _PS_MODULE_DIR_ . 'psgiftcards/mails/';

        Mail::Send(
            $customer->id_lang,
            Configuration::get('PS_GIFCARDS_TEMPLATE'),
            $translations['mailObject'],
            $data,
            $giftcard['recipientMail'],
            null,
            null,
            null,
            null,
            null,
            $dir
        );

        GiftcardHistory::setStatus($id_giftcard, 5);

        unset($cartRule);
        unset($customer);
        exit(json_encode('mail send'));
    }

    public function displayAjaxScheduleMail()
    {
        $id_giftcard = (int) Tools::getValue('id_giftcard');
        $this->checkToken($id_giftcard);

        GiftcardHistory::setStatus($id_giftcard, 3);
    }

    public function checkToken($id_giftcard)
    {
        $id_customer = GiftcardHistory::getCustomerById($id_giftcard);
        $customer = new Customer($id_customer);
        $secure_key = hash('md5', $customer->secure_key);
        $token = pSQL(Tools::getValue('token'));

        if (Tools::strlen($token) <= 0 || empty($token) || empty($secure_key) || $secure_key != $token) {
            exit('bad token');
        }
    }
}
