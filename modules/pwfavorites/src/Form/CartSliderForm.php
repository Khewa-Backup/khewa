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

namespace Pw\Favorites\Form;

use Pw\Favorites\Core\Form;
use PwFavorites;
use Tools;
use Validate;

class CartSliderForm extends Form
{
    public function configureHeader()
    {
        $this->setHeader($this->module->l('Cart slider configuration', 'CartSliderForm'), 'icon-cogs');
    }

    public function configureFields()
    {
        $this
            ->addSwitch('enabled', [
                'desc' => $this->module->l('Display a slider with the customer\'s favorite products in the cart summary', 'CartSliderForm'),
                'label' => $this->module->l('Show favorites in the cart summary', 'CartSliderForm')
            ])
            ->addField('max_slides_xs', 'text', [
                'col' => 3,
                'label' => $this->module->l('Max slides on mobile (small/portrait)', 'CartSliderForm'),
                'required' => true
            ])
            ->addField('max_slides_sm', 'text', [
                'col' => 3,
                'label' => $this->module->l('Max slides on mobile (large/landscape)', 'CartSliderForm'),
                'required' => true
            ])
            ->addField('max_slides_md', 'text', [
                'col' => 3,
                'label' => $this->module->l('Max slides on tablet', 'CartSliderForm'),
                'required' => true
            ])
            ->addField('max_slides_lg', 'text', [
                'col' => 3,
                'label' => $this->module->l('Max slides on desktop', 'CartSliderForm'),
                'required' => true
            ])
            ->addField('width_sm', 'text', [
                'col' => 3,
                'label' => $this->module->l('Viewport width on mobile (large/landscape)', 'CartSliderForm'),
                'required' => true,
                'suffix' => 'px'
            ])
            ->addField('width_md', 'text', [
                'col' => 3,
                'label' => $this->module->l('Viewport width on tablet', 'CartSliderForm'),
                'required' => true,
                'suffix' => 'px'
            ])
            ->addField('width_lg', 'text', [
                'col' => 3,
                'label' => $this->module->l('Viewport width on desktop', 'CartSliderForm'),
                'required' => true,
                'suffix' => 'px'
            ])
            ->addSwitch('infinite_loop', [
                'label' => $this->module->l('Infinite loop', 'CartSliderForm')
            ]);
    }

    public function configureButtons()
    {
        $this->setSubmitButton($this->module->l('Save', 'CartSliderForm'));
    }

    public function configureValues()
    {
        foreach (array_keys($this->fields) as $key) {
            $this->setValue($key, PwFavorites::getConfig('cart_slider_'.$key));
        }
    }

    /**
     * @throws \PrestaShopException
     */
    public function handleRequest()
    {
        if (!$this->isSubmitted()) {
            return;
        }

        $enabled = Tools::getValue('enabled');
        $maxSlidesXs = Tools::getValue('max_slides_xs');
        $maxSlidesSm = Tools::getValue('max_slides_sm');
        $maxSlidesMd = Tools::getValue('max_slides_md');
        $maxSlidesLg = Tools::getValue('max_slides_lg');
        $widthSm = Tools::getValue('width_sm');
        $widthMd = Tools::getValue('width_md');
        $widthLg = Tools::getValue('width_lg');
        $infiniteLoop = Tools::getValue('infinite_loop');

        if ($maxSlidesXs && $maxSlidesSm && $maxSlidesMd && $maxSlidesLg) {
            if (!Validate::isUnsignedInt($maxSlidesXs) || !Validate::isUnsignedInt($maxSlidesSm) || !Validate::isUnsignedInt($maxSlidesMd) || !Validate::isInt($maxSlidesLg)) {
                $this->displayMessage($this->module->l('Max slides must be integers', 'CartSliderForm'));
            }
        } else {
            $this->displayMessage($this->module->l('Max slides are required', 'CartSliderForm'));
        }

        if ($widthSm && $widthMd && $widthLg) {
            if (!Validate::isUnsignedInt($widthSm) || !Validate::isUnsignedInt($widthMd) || !Validate::isUnsignedInt($widthLg)) {
                $this->displayMessage($this->module->l('Viewport widths must be integers', 'CartSliderForm'));
            }
        } else {
            $this->displayMessage($this->module->l('Viewport widths are required', 'CartSliderForm'));
        }

        if (!$this->context->controller->errors) {
            PwFavorites::setConfig('cart_slider_enabled', (bool)$enabled);
            PwFavorites::setConfig('cart_slider_infinite_loop', (bool)$infiniteLoop);
            PwFavorites::setConfig('cart_slider_max_slides_xs', (int)$maxSlidesXs);
            PwFavorites::setConfig('cart_slider_max_slides_sm', (int)$maxSlidesSm);
            PwFavorites::setConfig('cart_slider_max_slides_md', (int)$maxSlidesMd);
            PwFavorites::setConfig('cart_slider_max_slides_lg', (int)$maxSlidesLg);
            PwFavorites::setConfig('cart_slider_width_sm', (int)$widthSm);
            PwFavorites::setConfig('cart_slider_width_md', (int)$widthMd);
            PwFavorites::setConfig('cart_slider_width_lg', (int)$widthLg);

            Tools::redirectAdmin($this->module->url(['confirm' => 'updatecartslider']));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setDefaults()
    {
        $this->setSubmitAction('submitcartslider');

        return parent::setDefaults();
    }

    /**
     * {@inheritdoc}
     */
    protected function setMessages()
    {
        $this->messages = [
            'confirm' => [
                'updatecartslider' => $this->module->l('Cart slider configuration successfully updated', 'CartSliderForm')
            ]
        ];
    }
}
