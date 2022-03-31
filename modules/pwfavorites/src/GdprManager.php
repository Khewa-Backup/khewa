<?php
/**
 * 2007-2020 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace Pw\Favorites;

use Context;
use Customer;
use Module;

class GdprManager
{
    /**
     * @var \PwFavorites
     */
    protected $module;

    /**
     * Creates a new GDPR Manager.
     *
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * Deletes a customer's advantage cards data.
     *
     * @param string $email
     *
     * @return string
     */
    public function delete($email)
    {
        $result = true;
        foreach (Customer::getCustomersByEmail($email) as $customer) {
            $result &= FavoritesManager::deleteByCustomer($customer['id_customer']);
        }

        return json_encode($result ? true : $this->module->l('Unable to delete favorites using email', 'GdprManager'));
    }

    /**
     * Exports a customer's advantage cards data.
     *
     * @param string $email
     *
     * @return string
     *
     * @throws \PrestaShopException
     */
    public function export($email)
    {
        $context = Context::getContext();

        $favorites = [];
        foreach (FavoritesManager::getByCustomerEmail($email) as $product) {
            $favorites[] = [
                $this->module->l('Product name', 'GdprManager') => $product['name'],
                $this->module->l('Product URL', 'GdprManager') => $context->link->getProductLink($product['id_product'])
            ];
        }

        return json_encode($favorites ? $favorites : $this->module->l('No favorites', 'GdprManager'));
    }
}
