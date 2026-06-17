<?php
/**
 * 2007-2018 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class CartController extends CartControllerCore
{
    public function displayAjaxProductRefresh()
    {
        if ($this->id_product) {
            $url = $this->context->link->getProductLink(
                $this->id_product,
                null,
                null,
                null,
                $this->context->language->id,
                null,
                (int)Product::getIdProductAttributesByIdAttributes($this->id_product, Tools::getValue('group'), true),
                false,
                false,
                true,
                ['quantity_wanted' => (int)$this->qty]
            );
       $id_unique_ipa = (int)Product::getIdProductAttributesByIdAttributes($this->id_product, Tools::getValue('group'), true);
        $this->context->cookie->id_unique_ipa = $id_unique_ipa;//Configuration::updateValue('id_unique_ipa', $id_unique_ipa);
        $this->context->cookie->write();
        } else {
            $url = false;
        }
        //var_dump($this->context->cookie->id_unique_ipa); exit;
        ob_end_clean();
        header('Content-Type: application/json');
        $this->ajaxDie(Tools::jsonEncode([
            'success' => true,
            'productUrl' => $url
        ]));
    }
}
