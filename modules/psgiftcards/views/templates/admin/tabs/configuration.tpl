{**
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
 *}
<div class="panel col-lg-10 right-panel">
    <h3>
        <i class="fa fa-cog"></i> {l s='General settings' mod='psgiftcards'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
    </h3>
    <form id="pshomeslider-conf" method="post" action="" class="form-horizontal">
        <div class="pshomeslider-content">
            <h4 class="addons-title">1 / {l s='General configuration of your gift cards' mod='psgiftcards'}</h4>
            <br>
            {* GIFT CARD VALIDITY *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="control-label">
                                {l s='Gift card validity period' mod='psgiftcards'}
                        </label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-3">
                    <input class="addons-number-fields addons-inline-block" required="required" value="{$gc_validity|escape:'htmlall':'UTF-8'}" type="number" min="0" name="GC_VALIDITY">
                    <p class="addons-inline-block">{l s='months' mod='psgiftcards'}</p>
                    <div class="help-block">
                        {l s='This will define the time period a client has to use his/her gift card after it was bought.' mod='psgiftcards'}
                    </div>
                </div>
            </div>
            {* GIFT CARD VALIDITY *}

            {* GIFT CARD TAX *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="control-label">
                            {l s='Type of tax applied to your gift cards' mod='psgiftcards'}
                        </label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-2">
                    <select id="tax" class="form-control" name="GC_TAX">
                        {foreach from=$taxes item=taxe}
                        {* {$gc_validity|escape:'htmlall':'UTF-8'} *}
                        {if $gc_tax eq $taxe.id_tax_rules_group}
                            <option value="{$taxe.id_tax_rules_group|escape:'htmlall':'UTF-8'}" selected="selected">{$taxe.name|escape:'htmlall':'UTF-8'}</option>
                        {else}
                            <option value="{$taxe.id_tax_rules_group|escape:'htmlall':'UTF-8'}">{$taxe.name|escape:'htmlall':'UTF-8'}</option>
                        {/if}
                        {/foreach}
                    </select>
                </div>
            </div>
            {* GIFT CARD TAX *}

            <p class="alert alert-info">{l s='Note that in most cases, gift cards should be paid without any taxes, as clients pay the tax after, when they buy products on your website. You are responsible for complying with the e-commerce regulation of your country, especially the one related to gift cards.' mod='psgiftcards'}</p>

            {* GIFT CARD CODE PREFIX *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="control-label">
                                {l s='Gift card code prefix' mod='psgiftcards'}
                        </label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-2">
                    <input type="text" required="required" value="{$gc_giftcard_prefix_code|escape:'htmlall':'UTF-8'}" name="GC_GIFTCARD_PREFIX_CODE">
                    <div class="help-block">
                        {l s='This will define the prefix which will be used in front of the gift card code.' mod='psgiftcards'}
                    </div>
                </div>
            </div>
            {* GIFT CARD CODE PREFIX *}

            {* FREE SHIPPING *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="boldtext control-label">{l s='Include free shipping in gift cards' mod='psgiftcards'}</label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input class="yes" type="radio" name="GC_FREE_SHIPPING" id="free_shipping_on" data-toggle="collapse" data-target="#pause_hover" value="1" {if $gc_free_shipping eq 1}checked="checked"{/if}>
                        <label for="free_shipping_on" class="radioCheck">{l s='YES' mod='psgiftcards'}</label>

                        <input class="no" type="radio" name="GC_FREE_SHIPPING" id="free_shipping_off" data-toggle="collapse" data-target="#pause_hover" value="0" {if $gc_free_shipping eq 0}checked="checked"{/if}>
                        <label for="free_shipping_off" class="radioCheck">{l s='NO' mod='psgiftcards'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    <div class="help-block">
                        {l s='If you click No, the gift card\'s recipients will have to pay the shipping fees on their orders.' mod='psgiftcards'}
                    </div>
                </div>
            </div>
            {* FREE SHIPPING *}

            {* GIFT CARD ORDER STATE *}
            <div class="form-group">
                <div class="alert alert-warning">
                    <p>{l s='The customer will be able to configure the gift card as soon as the order status is \'paid\'.' mod='psgiftcards' mod='psgiftcards'}</p>
                </div>
            </div>
            {* GIFT CARD ORDER STATE *}

            <h4 class="addons-title">2 / {l s='Creation of your gift cards' mod='psgiftcards'}</h4>
            <div class="alert alert-info">
                <p>{l s='Gift cards are created and work as normal products from your catalog. You need to create a new product with a name, a price, an available quantity, a description, one or several images, etc. Nevertheless, they require several specific elements : ' mod='psgiftcards'}</p>
                <p>{l s='1. You need to select the "Virtual product" type on the top right of the product name, then save your changes. Note also that you need to delete combinations the product may have in order to use the Virtual product type.' mod='psgiftcards'}</p>
                <p>{l s='2. Then you need to go to the "Modules" tab of your product sheet, select Premium Gift Card and tag the product as a gift card in order to be associated to the module.' mod='psgiftcards'}</p>
            </div>

            <p>{l s='Create a new gift card product by clicking' mod='psgiftcards'} <a href="{$newProductLink|escape:'htmlall':'UTF-8'}" target="_blank" style="font-weight: bold;">{l s='here' mod='psgiftcards'}</a>.</p>
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" id="submitPsgiftcardsModule" name="submitPsgiftcardsModule" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='psgiftcards'}
            </button>
        </div>
    </form>
</div>
