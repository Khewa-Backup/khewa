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

class SettingsForm extends Form
{
    public function configureHeader()
    {
        $this->setHeader($this->module->l('Settings', 'SettingsForm'), 'icon-cogs');
    }

    public function configureFields()
    {
        $this
            ->addSwitch('show_confirmation', [
                'desc' => $this->module->l('Display a confirmation message after adding/removing favorites', 'SettingsForm'),
                'label' => $this->module->l('Show confirmation', 'SettingsForm')
            ])
            ->addSwitch('move_button', [
                'desc' => $this->module->l('Moves the button to the top right corner of the product miniature', 'SettingsForm'),
                'label' => $this->module->l('Move button', 'SettingsForm')
            ])
            ->addField('product_miniature_selector', 'text', [
                'desc' => $this->module->l('The CSS selector of the product miniatures. Leave as-is if you are using the default ("classic") theme', 'SettingsForm'),
                'label' => $this->module->l('Product container CSS selector', 'SettingsForm')
            ])
            ->addField('product_thumbnail_selector', 'text', [
                'desc' => $this->module->l('Leave as-is if you are using the default ("classic") theme', 'SettingsForm'),
                'label' => $this->module->l('Product thumbnail CSS selector', 'SettingsForm')
            ])
            ->addField('button_color_add', 'color', [
                'desc' => $this->module->l('The color of the "Add to my favorites" button (when the product has not been added to the customer\'s favorites)', 'SettingsForm'),
                'label' => $this->module->l('"Add" button color', 'SettingsForm')
            ])
            ->addField('button_color_remove', 'color', [
                'desc' => $this->module->l('The color of the "Remove from my favorites" button (when the product has been added to the customer\'s favorites)', 'SettingsForm'),
                'label' => $this->module->l('"Remove" button color', 'SettingsForm')
            ]);
    }

    public function configureButtons()
    {
        $this->setSubmitButton($this->module->l('Save', 'SettingsForm'));
    }

    public function configureValues()
    {
        foreach (array_keys($this->fields) as $key) {
            $this->setValue($key, PwFavorites::getConfig($key));
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

        $addColor = Tools::getValue('button_color_add');
        $removeColor = Tools::getValue('button_color_remove');

        if (!Validate::isColor($addColor) || !Validate::isColor($removeColor)) {
            $this->displayMessage($this->module->l('Invalid color code', 'SettingsForm'));
        }

        if (!$this->context->controller->errors) {
            PwFavorites::setConfig('button_color_add', $addColor);
            PwFavorites::setConfig('button_color_remove', $removeColor);
            PwFavorites::setConfig('move_button', (bool)Tools::getValue('move_button'));
            PwFavorites::setConfig('product_miniature_selector', Tools::getValue('product_miniature_selector'));
            PwFavorites::setConfig('product_thumbnail_selector', Tools::getValue('product_thumbnail_selector'));
            PwFavorites::setConfig('show_confirmation', (bool)Tools::getValue('show_confirmation'));

            $this->module->generateCustomStylesheet();

            Tools::redirectAdmin($this->module->url(['confirm' => 'updatesettings']));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setDefaults()
    {
        $this->setSubmitAction('submitsettings');

        return parent::setDefaults();
    }

    /**
     * {@inheritdoc}
     */
    protected function setMessages()
    {
        $this->messages = [
            'confirm' => [
                'updatesettings' => $this->module->l('Settings successfully updated', 'SettingsForm')
            ]
        ];
    }
}
