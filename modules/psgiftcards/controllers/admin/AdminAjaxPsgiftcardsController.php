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
class AdminAjaxPsgiftcardsController extends ModuleAdminController
{
    /** @var Psgiftcards */
    public $module;

    public function ajaxProcessIsTagAsGiftcard()
    {
        $id_product = (int) Tools::getValue('id_product');
        $isTag = Giftcard::getIdGiftcard($id_product);

        if (!empty($isTag)) {
            $result = '1';
        } else {
            $result = '0';
        }
        exit($result);
    }

    public function ajaxProcessTagProduct()
    {
        $id_product = (int) Tools::getValue('id_product');
        $isTag = pSQL(Tools::getValue('isChecked'));

        $product = new Product($id_product);
        $type = $product->getType();
        if ($type != Product::PTYPE_VIRTUAL) {
            exit('product is not virtual');
        }

        $ifGiftcardAlreadyExist = Giftcard::getGiftCardId($id_product, (int) Context::getContext()->shop->id);
        if ($ifGiftcardAlreadyExist) {
            $giftcard = new Giftcard($ifGiftcardAlreadyExist['id_giftcard']);
        } else {
            $giftcard = new Giftcard();
        }

        if ($isTag == '1') {
            $giftcard->id_product = $id_product;
            $giftcard->is_active = true;
            $giftcard->id_shop = (int) Context::getContext()->shop->id;
            $giftcard->save();

            //update tax product rule
            $product->id_tax_rules_group = Configuration::get('PS_GIFCARDS_TAX');
            $product->save();

            exit('tag');
        } else {
            $giftcard->delete();
            exit('untag');
        }
    }

    /**
     * ajaxProcessGetProductTagAsGiftcard
     *
     * @return array
     */
    public function ajaxProcessGetProductTagAsGiftcard()
    {
        $context = Context::getContext();
        $id_lang = $context->language->id;
        $products = [];
        $giftcards = Giftcard::getGiftcards();
        $link = new Link();

        foreach ($giftcards as $giftcard) {
            $product = new Product((int) $giftcard['id_product'], false, $id_lang);

            $link_rewrite = $product->link_rewrite;
            $id_image = Product::getCover((int) $giftcard['id_product']);
            $id_image = $id_image['id_image'];

            if ($this->module->ps_version == true) { // if on prestashop 1.7
                $image_link = $link->getImageLink($link_rewrite, $id_image, ImageType::getFormattedName('small'));

                $array = ['id_product' => $giftcard['id_product']];
                $product_link = $link->getAdminLink('AdminProducts', true, $array);
            } else { // if on pretashop 1.6
                $image_link = $link->getImageLink($link_rewrite, $id_image, ImageType::getFormatedName('small'));
                $product_link = $link->getAdminLink('AdminProducts', true) . '&id_product=' . $giftcard['id_product'] . '&updateproduct';
            }
            $image_link = Tools::getProtocol() . $image_link;

            if (file_exists($image_link) == false) {
                $image_link = '';
            }
            // get currency details
            $currencySymbol = $context->currency->sign;

            $products[] = [
                'id_giftcard' => $giftcard['id_giftcard'],
                'id_product' => $giftcard['id_product'],
                'product_name' => $product->name,
                'product_description' => substr($product->description_short, 0, 250),
                'product_price' => number_format(Product::getPriceStatic($giftcard['id_product'], true), 2),
                'currencySymbol' => $currencySymbol,
                'product_image' => $image_link,
                'isActive' => $product->active,
                'product_link' => $product_link,
            ];
        }
        exit(json_encode($products));
    }

    public function ajaxProcessSwitchState()
    {
        $id_product = (int) Tools::getValue('id_product');
        $id_giftcard = (int) Tools::getValue('id_giftcard');

        $giftcard = new Giftcard($id_giftcard);
        if ($giftcard->is_active == 0) {
            $giftcard->is_active = 1;
            $giftcard->save();

            $product = new Product($id_product);
            $product->active = true;
            $product->save();

            $result = 'enable';
        } else {
            $giftcard->is_active = 0;
            $giftcard->save();

            $product = new Product($id_product);
            $product->active = false;
            $product->save();

            $result = 'disable';
        }
        exit(json_encode($result));
    }

    public function ajaxProcessGetGiftcardsHistory()
    {
        // $context = Context::getContext();
        $giftcards = [];
        $giftcardsHistory = GiftcardHistory::getGiftcardsHistory();

        foreach ($giftcardsHistory as $giftcard) {
            $order = new Order($giftcard['id_order']);
            $orderDate = $order->date_add;

            $customer = new Customer($giftcard['id_customer']);
            $customerLastname = $customer->lastname;
            $customerFirstname = $customer->firstname;

            $giftcards[] = [
                'id_giftcard' => '#' . $giftcard['id_giftcard'],
                'id_order' => $giftcard['id_order'],
                'Lastname' => $customerLastname,
                'Firstname' => $customerFirstname,
                'Mail' => $customer->email,
                'Order date' => $orderDate,
                'Status' => $giftcard['id_state'],
            ];
        }
        exit(json_encode($giftcards));
    }

    public function ajaxProcessUntagProduct()
    {
        $id_product = (int) Tools::getValue('id_product');
        $id_giftcard = (int) Tools::getValue('id_giftcard');

        $giftcard = new Giftcard($id_giftcard);
        $giftcard->delete();

        $product = new Product($id_product);
        $product->active = false;
        $product->save();
    }

    /**
     * Save the template
     */
    public function ajaxProcessSaveTpl()
    {
        $data = Tools::getValue('templateData');

        $datasModify = new GiftCardDatasModify();
        $aTemplateData = $datasModify->parseStr($data);
        $iTemplateId = 0;

        // Step 1 : We save the template appearance configurations datas
        $style = explode('&', $data[0]);
        $primary = substr($style[0], 17);
        $secondary = substr($style[1], 19);
        $template = substr($style[2], 11);

        switch ($template) {
            case 'sendy':
                $iTemplateId = 0;
                break;
            case 'boxy':
                $iTemplateId = 1;
                break;
            case 'puffy':
                $iTemplateId = 2;
                break;
        }

        Configuration::updateValue('PS_GIFCARDS_TEMPLATE', $template);
        Configuration::updateValue('PS_GIFCARDS_PRIMARY_COLOR', str_replace('%23', '#', $primary));
        Configuration::updateValue('PS_GIFCARDS_SECONDARY_COLOR', str_replace('%23', '#', $secondary));
        // Step 2 : We save the template datas
        if (!empty($aTemplateData[1])) {
            $aPreparedDatasForQueries = $this->prepareContentDatas($iTemplateId, $aTemplateData[1]);
            $bSaveAllTemplateContentDatas = $this->saveContentConf($aPreparedDatasForQueries, $iTemplateId);
        }

        if (!empty($data[2])) {
            foreach ($data[2] as $key => $file) {
                $filename = str_replace(' ', '', $file['fileDL']['upload']['filename']);
                $image_content = explode('base64,', $file['fileDL']['dataURL']);
                $image_content = base64_decode($image_content[1]); // remove "data:image/png;base64,

                try {
                    file_put_contents(_PS_ROOT_DIR_ . '/modules/psgiftcards/views/img/DL/' . $filename, $image_content);

                    $data = [
                        'email_discount' => $filename,
                    ];

                    $update = Db::getInstance()->update('psgiftcards_mail_lang', $data, 'id_lang = ' . $file['langDL']);

                    if ($update !== false) {
                        $response = [
                            'status' => 'success',
                            'img_link' => Tools::getShopDomainSsl(true) . '/modules/psgiftcards/views/img/DL/' . $filename,
                            'lang' => $file['langDL'],
                        ];
                        $this->ajaxDie(json_encode($response));
                    }

                    $this->ajaxDie(json_encode($update));
                } catch (Exception $e) {
                    $this->ajaxDie(json_encode($e->getMessage()));
                }
            }
        }

        $response = [
            'status' => 'success',
        ];

        $this->ajaxDie(json_encode($response));
    }

    /**
     * prepareContentDatas
     *
     * @param int $iTemplateId
     * @param array $data
     *
     * @return bool|array
     */
    private function prepareContentDatas($iTemplateId, $data)
    {
        foreach ($data as $key => $aData) {
            $iLangId = (int) $aData['id_lang'];
            $currentLang = $this->context->language->id;

            if ($currentLang == $iLangId && empty($aData['email_subject'])) {
                return false;
            }
            // we save the data only if email_content and subject aren't empty
            if (!empty($aData['email_content_' . $iLangId]) && !empty($aData['email_subject'])) {
                // We escape all the datas
                $data[$key] = array_map('pSQL', $data[$key]);

                // remove all  "_idlang" from the data key (we must do that because of ckeditor instances names)
                // These following datas must keep their HTML tags
                $data[$key]['email_content'] = htmlentities($aData['email_content_' . $iLangId], ENT_QUOTES);
                $data[$key]['email_unsubscribe'] = htmlentities($aData['email_unsubscribe_' . $iLangId], ENT_QUOTES);
                unset($data[$key]['email_content_' . $iLangId]);
                unset($data[$key]['email_discount_' . $iLangId]);
                unset($data[$key]['email_unsubscribe_' . $iLangId]);

                // we add id_template and lang_iso values to the array (very important)
                $data[$key]['id_template'] = $iTemplateId;
                $data[$key]['lang_iso'] = Language::getIsoById($iLangId);
            } else {
                // We won't save or update this array
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * update the Content datas
     *
     * @return bool
     */
    private function saveContentConf($data, $iTemplateId)
    {
        foreach ($data as $key => $aData) {
            $id_lang = (int) $aData['id_lang'];
            $where = 'id_template = ' . $iTemplateId . ' AND id_lang = ' . $id_lang;

            $bDataRowAlreadyExist = $this->isEmailTemplateDataAlreadyExist($where);

            if ($bDataRowAlreadyExist == true) {
                if (!Db::getInstance()->update('psgiftcards_mail_lang', $aData, $where)) {
                    return false;
                }
            } else {
                if (!Db::getInstance()->insert('psgiftcards_mail_lang', $aData)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function isEmailTemplateDataAlreadyExist($where)
    {
        $bDataAlreadyExist = Db::getInstance()->executeS(
            'SELECT id_template
            FROM `' . _DB_PREFIX_ . 'psgiftcards_mail_lang`
            WHERE ' . pSQL($where)
        );

        return (bool) $bDataAlreadyExist;
    }

    public function ajaxProcessGetPdfData()
    {
        if (file_exists(_PS_ROOT_DIR_ . '/tools/tcpdf/tcpdf.php')) {
            require_once _PS_ROOT_DIR_ . '/tools/tcpdf/tcpdf.php';
        }

        $message = Tools::getValue('message');
        $footer = Tools::getValue('footer');
        $message = explode('</p>', $message);

        $replace = [
            'discount_value' => '25€',
            'discount_validity' => date('d-m-Y'),
            'discount_code' => 'CODE19',
            'shop_link' => Configuration::get('PS_SHOP_NAME'),
            '$site_url' => Configuration::get('PS_SHOP_NAME'),
        ];
        foreach ($replace as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
            $footer = str_replace('{' . $key . '}', $value, $footer);
        }
        foreach ($message as $key => $line) {
            if (strpos($line, '{buyer_name}')
                || strpos($line, '{recipient_name}')
                || strpos($line, '{buyer_message}')
                || empty($line)) {
                unset($message[$key]);
            } else {
                $message[$key] .= '</p>';
            }
        }
        array_pop($message);
        $message = implode($message);

        $data = [
            'shopName' => Configuration::get('PS_SHOP_NAME'),
            'recipientName' => 'John DOE',
            'buyerName' => 'Jane DOE',
            'text' => 'Message',
            'send_date' => date('d-m-Y'),
            'cart_rule_code' => 'GGC52BNG6PR2CVBER',
            'price' => 25,
            'site_url' => Configuration::get('PS_SHOP_DOMAIN'),
            'from' => $this->l('from'),
            'to' => $this->l('to'),
            'message' => $message,
            'text_footer' => $footer,
            'color' => Configuration::get('PS_GIFCARDS_PRIMARY_COLOR'),
            'img' => Tools::getShopDomain(true) . __PS_BASE_URI__ . '/modules/psgiftcards/views/img/giftcard.jpg',
            'circle' => Tools::getShopDomain(true) . __PS_BASE_URI__ . '/modules/psgiftcards/views/img/circle.png',
        ];

        $filename = _PS_MODULE_DIR_ . 'psgiftcards/views/templates/pdf/giftcardPdf.html';
        $content = Tools::file_get_contents($filename);
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        $content1 = $content;

        $pageLayout = [1050, 450]; // px
        /** @var TCPDF $pdf */
        $pdf = new \TCPDF('l', 'px', $pageLayout, true, 'UTF-8', false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setFont('dejavusans');

        $pdf->AddPage();

        $pdf->writeHTML($content1);
        $pdf->output(_PS_MODULE_DIR_ . 'psgiftcards/giftcard.pdf', 'F');

        return $this->ajaxDie(Tools::getShopDomain(true) . __PS_BASE_URI__ . 'modules/psgiftcards/giftcard.pdf');
    }

    public function ajaxProcessRemoveImage()
    {
        $langs = Language::getLanguages(true);
        switch (Configuration::get('PS_GIFCARDS_TEMPLATE')) {
            case 'sendy':
                $id_template = 0;
                break;
            case 'boxy':
                $id_template = 1;
                break;
            case 'puffy':
                $id_template = 2;
                break;

            default:
                $id_template = 0;
                break;
        }
        $data['email_discount'] = '';
        foreach ($langs as $lang) {
            $where = 'id_template = ' . $id_template . ' AND id_lang = ' . $lang['id_lang'];

            $bDataRowAlreadyExist = $this->isEmailTemplateDataAlreadyExist($where);

            if ($bDataRowAlreadyExist == true) {
                if (!Db::getInstance()->update('psgiftcards_mail_lang', $data, $where)) {
                    return false;
                }
            }
        }

        return $this->ajaxDie();
    }
}
