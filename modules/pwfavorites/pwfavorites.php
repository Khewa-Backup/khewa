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

require_once _PS_MODULE_DIR_.'pwfavorites/vendor/autoload.php';

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use Pw\Favorites\Core\Module as PwModule;
use Pw\Favorites\FavoritesManager;
use Pw\Favorites\Form\CartSliderForm;
use Pw\Favorites\Form\SettingsForm;
use Pw\Favorites\GdprManager;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PwFavorites extends PwModule implements WidgetInterface
{
    /**
     * @see PwModule::$configuration
     */
    public static $configuration = [
        'button_color_add' => '#CC0000',
        'button_color_remove' => '#363636',
        'cart_slider_enabled' => true,
        'cart_slider_infinite_loop' => false,
        'cart_slider_max_slides_xs' => 2,
        'cart_slider_max_slides_sm' => 2,
        'cart_slider_max_slides_md' => 2,
        'cart_slider_max_slides_lg' => 2,
        'cart_slider_width_sm' => 767,
        'cart_slider_width_md' => 991,
        'cart_slider_width_lg' => 1199,
        'move_button' => true,
        'product_miniature_selector' => 'article.product-miniature.js-product-miniature',
        'product_thumbnail_selector' => '.product-thumbnail',
        'show_confirmation' => true
    ];

    /**
     * @see PwModule::$hooks
     */
    public static $hooks = [
        'actionDeleteGDPRCustomer',
        'actionExportGDPRData',
        'displayBeforeBodyClosingTag',
        'displayCustomerAccount',
        'displayHeader',
        'displayNav2',
        'displayProductButtons',
        'displayProductListReviews',
        'displayShoppingCartFooter',
        'registerGDPRConsent'
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->author = 'pilipiliweb';
        $this->name = 'pwfavorites';
        $this->tab = 'front_office_features';
        $this->version = '2.1.0';

        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->module_key = '8d3b9eed6195746654b5e963b0f923e4';

        parent::__construct();

        $this->displayName = $this->l('Favorite Products');
        $this->description = $this->l('Add products to favorites');
    }

    /**
     * Displays the module's configuration page.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->write([
            new SettingsForm($this),
            new CartSliderForm($this)
        ]);
    }

    /**
     * HOOK: Deletes PW Favorites customer data for GDPR.
     *
     * @param array $params An array containing the customer's identifier (email, phone, ...)
     *
     * @return string
     */
    public function hookActionDeleteGDPRCustomer($params)
    {
        if (empty($params['email']) || !Validate::isEmail($params['email'])) {
            return null;
        }

        return (new GdprManager($this))->delete($params['email']);
    }

    /**
     * HOOK: Exports customer favorite products for GDPR.
     *
     * @param array $params An array containing the customer's identifier (email, phone, ...)
     *
     * @return string
     *
     * @throws PrestaShopException
     */
    public function hookActionExportGDPRData($params)
    {
        if (empty($params['email']) || !Validate::isEmail($params['email'])) {
            return null;
        }

        return (new GdprManager($this))->export($params['email']);
    }

    /**
     * HOOK: Removes a product from the favorites when it is deleted.
     *
     * @param array $params
     */
    public function hookActionProductDelete($params)
    {
        if (empty($params['id_product'])) {
            return;
        }

        FavoritesManager::deleteByProduct($params['id_product']);
    }

    /**
     * HOOK: Displays the alerts container before the HTML body closing tag.
     *
     * @return string
     */
    public function hookDisplayBeforeBodyClosingTag()
    {
        return $this->render('hook/before_body_closing_tag.tpl');
    }

    /**
     * HOOK: Displays a link to the customer's favorite products page in the customer's account page.
     *
     * @return string
     */
    public function hookDisplayCustomerAccount()
    {
        return $this->render('hook/customer_account.tpl', [
            'favorites_url' => $this->context->link->getModuleLink($this->name, 'favorites')
        ]);
    }

    /**
     * HOOK: Adds Javascript and CSS to the front office.
     */
    public function hookDisplayHeader()
    {
        Media::addJsDef([
            $this->name => [
                'move_button' => (bool)self::getConfig('move_button'),
                'product_miniature_selector' => self::getConfig('product_miniature_selector'),
                'product_thumbnail_selector' => self::getConfig('product_thumbnail_selector'),
                'show_confirmation' => (bool)self::getConfig('show_confirmation'),
                'slider' => [
                    'infinite_loop' => (bool)self::getConfig('cart_slider_infinite_loop'),
                    'max_slides_xs' => (int)self::getConfig('cart_slider_max_slides_xs'),
                    'max_slides_sm' => (int)self::getConfig('cart_slider_max_slides_sm'),
                    'max_slides_md' => (int)self::getConfig('cart_slider_max_slides_md'),
                    'max_slides_lg' => (int)self::getConfig('cart_slider_max_slides_lg'),
                    'width_sm' => (int)self::getConfig('cart_slider_width_sm'),
                    'width_md' => (int)self::getConfig('cart_slider_width_md'),
                    'width_lg' => (int)self::getConfig('cart_slider_width_lg')
                ],
                'translations' => [
                    'favorite_added' => $this->l('"%1$s" has been added to %2$smy favorites%3$s'),
                    'favorite_removed' => $this->l('"%1$s" has been removed from %2$smy favorites%3$s')
                ],
                'urls' => [
                    'ajax' => $this->context->link->getModuleLink($this->name, 'ajax', ['fav' => true]),
                    'favorites' => $this->context->link->getModuleLink($this->name, 'favorites')
                ]
            ]
        ]);

        $this->context->controller->addJqueryPlugin('bxslider');

        $this->registerStylesheet('front');
        $this->registerStylesheet('custom');

        $this->registerJavascript('front');
    }

    /**
     * HOOK: Displays a slider with the customer's favorite product at the bottom of the cart page.
     *
     * @param array $params
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws ReflectionException
     */
    public function hookDisplayShoppingCartFooter($params)
    {
        if (!$this->context->customer->isLogged() || !self::getConfig('cart_slider_enabled')) {
            return null;
        }

        if (!$products = FavoritesManager::getNotInCart($params['cart'])) {
            return null;
        }

        return $this->render('hook/shopping_cart_footer.tpl', [
            'products' => $this->getPresentedProducts($products)
        ]);
    }

    /**
     * Generates the stylesheet for custom colors and themes.
     */
    public function generateCustomStylesheet()
    {
        $css = $this->render('hook/custom.css.tpl', [
            'button_color_add' => self::getConfig('button_color_add'),
            'button_color_remove' => self::getConfig('button_color_remove'),
            'move_button' => (bool)self::getConfig('move_button'),
            'product_miniature_selector' => self::getConfig('product_miniature_selector'),
            'product_thumbnail_selector' => self::getConfig('product_thumbnail_selector')
        ], true);

        file_put_contents($this->getModuleDir().'/views/css/custom.css', $css);
    }

    /**
     * @param array $products
     *
     * @return array
     *
     * @throws ReflectionException
     */
    protected function getPresentedProducts($products)
    {
        $assembler = new ProductAssembler($this->context);
        $presenterFactory = new ProductPresenterFactory($this->context);

        $listingPresenter = $presenterFactory->getPresenter();
        $presenterSettings = $presenterFactory->getPresentationSettings();

        $list = array();
        foreach ($products as $product) {
            $presented = $listingPresenter->present(
                $presenterSettings,
                $assembler->assembleProduct($product),
                $this->context->language
            );

            $list[] = $presented;
        }

        return $list;
    }

    /**
     * Renders the module's widget.
     *
     * @param string $hookName
     * @param array  $configuration
     *
     * @return string
     */
    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (!empty($configuration['id_product']) || !empty($configuration['product'])) {
            return $this->render('hook/button.tpl', $this->getWidgetVariables($hookName, $configuration));
        }

        return $this->render('hook/link.tpl');
    }

    /**
     * Gets the module's widget's variables.
     *
     * @param string $hookName
     * @param array  $configuration
     *
     * @return array
     */
    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        if (empty($configuration['id_product']) && empty($configuration['product'])) {
            return [];
        }

        $id_product = !empty($configuration['id_product']) ? $configuration['id_product'] : $configuration['product']->id;

        $favorite = [];
        if ($this->context->customer->isLogged()) {
            $favorite = FavoritesManager::getWithProductName($id_product, $this->context->customer->id);
        }

        return [
            'favorite' => $favorite ? (bool)$favorite['id_customer'] : false,
            'id_product' => (int)$id_product,
            'product_name' => $favorite ? $favorite['name'] : null
        ];
    }
}
