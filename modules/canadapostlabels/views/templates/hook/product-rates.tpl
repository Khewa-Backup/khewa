{*
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
 *
 *}
{if isset($countries) && isset($hasAddress) && isset($isProduct)}
    <div id="canadapost-product-rates" class="{if $isProduct}card{else}card-block{/if}" data-product-attribute="{$id_product_attribute|escape:'html':'UTF-8'}">
        {if !$isProduct || $isQuickview}<form action="{$moduleCarrierControllerUrl|escape:'html':'UTF-8'}" method="post">{/if}
            <a href="#" data-toggle="collapse" data-target="#carrier-table" class="product-information">
                <i class="material-icons shipping-icon">local_shipping</i>
                {l s='Estimate Shipping' mod='canadapostlabels'}
                <i class="material-icons caret-down-icon">arrow_drop_down</i>
            </a>
            <div id="carrier-table" class="{if $isProduct}collapse{else}in{/if}">
                {if !$productError}
                <div class="row update-address-container">
                    <div class="col-lg-12">
                        <div class="clearfix">
                            {if $addresses|count == 0}
                                <select name="id_country" class="form-control form-control-select noUniform" id="id_country">
                                    {foreach $countries as $country}
                                        <option value="{$country.id_country|escape:'html':'UTF-8'}" {if $country.id_country == $selectedCountry}selected{/if}>{$country.name|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                                <input
                                        id="postcode"
                                        class="form-control {if isset($error) && $error != false}fieldError{/if}"
                                        type="text"
                                        name="postcode"
                                        value="{if isset($postcode)}{$postcode|escape:'html':'UTF-8'}{/if}"
                                        placeholder="{l s='Postal Code' mod='canadapostlabels'}"
                                        style="display: {if $selectedCountry == $id_canada || $selectedCountry == $id_us}inline-block{else}none{/if};"
                                >
                                <a href="{$moduleCarrierControllerUrl|escape:'html':'UTF-8'}" class="btn btn-primary submit-update-address button exclusive-medium">
                                    <span>{l s='Estimate Shipping' mod='canadapostlabels'}</span>
                                </a>
                            {else}
                                <select name="id_address" class="form-control form-control-select noUniform" id="id_address">
                                    {foreach from=$addresses key=id item=address}
                                        <option value="{$id|escape:'html':'UTF-8'}" {if $id == $selectedAddress}selected{/if}>{$address|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            {/if}
                        </div>
                    </div>
                </div>
                {/if}
                {if isset($error) && $error != false}
                    <p class="error">{$error|escape:'html':'UTF-8'}</p>
                {/if}
                {if isset($productError) && $productError != false}
                    <p class="error">{$productError|escape:'html':'UTF-8'}</p>
                {/if}
                {if isset($deliveryOptionList)}
                    <div class="delivery-list-container">
                        <table class="carrier-list table">
                            {foreach $deliveryOptionList as $deliveryOption}
                                <tr>
                                    {if $hasProducts == true && $hasAddress == true}
                                        <td class="carrier-radio">
                                            <input
                                                    type="radio"
                                                    name="carrier"
                                                    value="{$deliveryOption.id_carrier|escape:'html':'UTF-8'}"
                                                    {if isset($deliveryOption.selected) && $deliveryOption.selected == true}
                                                        checked="checked"
                                                    {/if}
                                            >
                                        </td>
                                    {/if}
                                    <td class="carrier-name">
                                        <b>{$deliveryOption.name|escape:'html':'UTF-8'}</b>
                                        {if isset($deliveryOption.delay)}
                                            <p class="carrier-delay">{$deliveryOption.delay|escape:'html':'UTF-8'}</p>
                                        {/if}
                                    </td>
                                    <td class="carrier-price">
                                        {if $deliveryOption.cost == 0}
                                            {l s='Free' mod='canadapostlabels'}
                                        {else}
                                            {Tools::displayPrice($deliveryOption.cost)|escape:'html':'UTF-8'}
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                {/if}
                <p class="loading">{l s='Loading' mod='canadapostlabels'}...</p>
            </div>
            {if !$isProduct || $isQuickview}</form>{/if}
    </div>
{/if}
