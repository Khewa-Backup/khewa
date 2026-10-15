<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_0_7($object)
{
    try {
        upgradeCustomsProductsDb();
    } catch (Exception $e) {
        return true;
    }
    return true;
}

/**
 * Convert id_product values to id_order_detail values in OrderLabelCustomsProduct table
 * This change is to allow the module to use the id_order_detail value when creating a label
 * which fixes a bug when the customer orders multiple quantities of the same product in different
 * combinations.
 *
 * @throws PrestaShopDatabaseException
 * @throws PrestaShopException
 */
function upgradeCustomsProductsDb()
{
    $OrderLabelSettingsCollection = new PrestaShopCollection('\CanadaPostPs\OrderLabelSettings');
    $orderLabelSettingsArray = $OrderLabelSettingsCollection->getAll();

    /** @var \CanadaPostPs\OrderLabelSettings $OrderLabelSettings */
    foreach ($orderLabelSettingsArray as $OrderLabelSettings) {

        // Loop through every order that has an associated OrderLabelSettings object
        $Order = new \Order($OrderLabelSettings->id_order);
        if (\Validate::isLoadedObject($Order)) {
            $orderProducts = $Order->getProducts();

            // Loop through every order product
            foreach ($orderProducts as $orderProduct) {
                // Get all customs product objects that still use the old "id_product" value for this OrderLabelSettings object
                $customsProducts = \CanadaPostPs\OrderLabelCustomsProduct::getOrderLabelCustomsProducts(array(
                    'id_order_label_settings' => $OrderLabelSettings->id,
                    'id_product' => $orderProduct['id_product']
                ));

                // Update the "id_product" value to "id_order_detail"
                if (!empty($customsProducts)) {
                    foreach ($customsProducts as $customsProduct) {
                        $OrderLabelCustomsProduct = new \CanadaPostPs\OrderLabelCustomsProduct($customsProduct['id_order_label_customs_product']);
                        if (\Validate::isLoadedObject($OrderLabelCustomsProduct)) {
                            $OrderLabelCustomsProduct->id_product = $orderProduct['id_order_detail'];
                            $OrderLabelCustomsProduct->save();
                        }
                    }
                }
            }
        }
    }
}
