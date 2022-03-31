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

use Pw\Favorites\FavoritesManager;

class PwFavoritesAjaxModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;

    /**
     * Processes AJAX requests.
     *
     * @throws PrestaShopException
     */
    public function postProcess()
    {



        if (!$this->isAjax()) {
            $this->redirect();
        }

        if (!$this->context->customer->isLogged()) {
            $this->unauthorized();
        }

        if (Tools::isSubmit('fav')) {
            if ($this->isMethod('GET')) {
                $this->getFavorite(Tools::getValue('id_product'));
            }

            if ($this->isMethod('POST')) {
                $this->postFavorite(Tools::getValue('id_product'));
            }

            if ($this->isMethod('DELETE')) {
                $this->deleteFavorite(Tools::getValue('id_product'));
            }

            if ($this->isMethod('PUT')) {
                $this->putFavorite(Tools::getValue('id_product'));
            }

            $this->methodNotAllowed(['GET', 'POST', 'PUT', 'DELETE']);
        }

        $this->notFound();
    }

    /**
     * Removes a product from the customer's favorites.
     *
     * @param int $id_product The product id
     */
    protected function deleteFavorite($id_product)
    {
        // Check if product ID was sent
        if (!$id_product) {
            $this->badRequest($this->module->l('Product ID is missing', 'ajax'));
        }

        if (FavoritesManager::delete($id_product, $this->context->customer->id)) {
            $this->noContent();
        }

        $this->badRequest($this->module->l('Could not remove product from favorites', 'ajax'));
    }

    /**
     * Returns a JSON response indicating whether or not a product is in the customer's favorites.
     * 0: The product is not in the favorites list
     * 1. The product is in the favorites list
     *
     * @param int $id_product The product id
     *
     * @throws PrestaShopException
     */
    protected function getFavorite($id_product)
    {
        // Check if product ID was sent
        if (!$id_product) {
            $this->badRequest($this->module->l('Product ID is missing', 'ajax'));
        }

        // Check if product exists
        if (!Validate::isLoadedObject(new Product($id_product))) {
            $this->notFound($this->module->l('Product not found', 'ajax'));
        }

        $this->json(
            (int)FavoritesManager::get($id_product, $this->context->customer->id)
        );
    }

    /**
     * Adds a product to the customer's favorites.
     *
     * @param int $id_product The product id
     *
     * @throws PrestaShopDatabaseException
     */
    protected function postFavorite($id_product)
    {
        // Check if product ID was sent
        if (!$id_product) {
            $this->badRequest($this->module->l('Product ID is missing', 'ajax'));
        }

        if (FavoritesManager::add($id_product, $this->context->customer->id)) {
            $this->created('');
        }

        $this->badRequest($this->module->l('Could not add product to favorites', 'ajax'));
    }

    /**
     * Adds or removes a product from the customer's favorites.
     *
     * @param int $id_product The product id
     *
     * @throws PrestaShopException
     */
    protected function putFavorite($id_product)
    {
        // Check if product ID was sent
        if (!$id_product) {
            $this->badRequest($this->module->l('Product ID is missing', 'ajax'));
        }

        // Check if product exists
        if (!Validate::isLoadedObject(new Product($id_product))) {
            $this->notFound($this->module->l('Product not found', 'ajax'));
        }

        if (FavoritesManager::get($id_product, $this->context->customer->id)) {
            $this->json(
                (int)!FavoritesManager::delete($id_product, $this->context->customer->id)
            );
        }

        $this->json(
            (int)FavoritesManager::add($id_product, $this->context->customer->id)
        );
    }

    /*****************************************************/
    /* -------------------- HELPERS -------------------- */
    /*****************************************************/

    /**
     * Sends an HTTP Bad Request response in JSON format.
     *
     * @param string|null $message The error message
     */
    protected function badRequest($message = null)
    {
        $this->json([
            'code' => 400,
            'message' => $message ? $message : $this->module->l('Bad request', 'ajax')
        ], 400);
    }

    /**
     * Sends an HTTP Created response.
     *
     * @param mixed $data
     */
    protected function created($data)
    {
        $this->json($data, 201);
    }

    /**
     * Checks if the request was sent using AJAX.
     *
     * @return bool
     */
    protected function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            'xmlhttprequest' === Tools::strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    /**
     * Checks the request method.
     *
     * @param string $method The request method
     *
     * @return bool
     */
    protected function isMethod($method)
    {
        return !empty($_SERVER['REQUEST_METHOD']) && Tools::strtoupper($method) === $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Sends a JSON response.
     *
     * @param mixed $data The data for the response body
     * @param int   $status The HTTP status code
     *
     * @return string
     */
    protected function json($data, $status = 200)
    {
        if (200 !== $status) {
            http_response_code($status);
        }

        header('Content-Type: application/json');

        ob_clean();
        echo json_encode($data);
        die;
    }

    /**
     * Sends an HTTP Method Not Allowed response.
     *
     * @param string|string[] $allowedMethods The list of valid HTTP methods
     */
    protected function methodNotAllowed($allowedMethods)
    {
        $this->json([
            'code' => 405,
            'message' => sprintf(
                $this->module->l('Method not allowed. Allowed methods are: %s', 'ajax'),
                is_array($allowedMethods) ? implode(', ', $allowedMethods) : $allowedMethods
            )
        ], 405);
    }

    /**
     * Sends an HTTP No Content response.
     */
    protected function noContent()
    {
        ob_clean();
        http_response_code(204);
        die;
    }

    /**
     * Sends an HTTP Not Found response in JSON format.
     *
     * @param string|null $message The error message
     */
    protected function notFound($message = null)
    {
        $this->json([
            'code' => 404,
            'message' => $message ? $message : $this->module->l('Not found', 'ajax')
        ], 404);
    }

    /**
     * Sends an HTTP Unauthorized response.
     *
     * @param string|null $message
     */
    protected function unauthorized($message = null)
    {
        $this->json([
            'code' => 401,
            'message' => $message ? $message : $this->module->l('Unauthorized', 'ajax')
        ], 401);
    }
}
