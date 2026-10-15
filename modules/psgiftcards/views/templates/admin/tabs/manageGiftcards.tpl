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
    <div id="giftcards">
        <h3>
            <i class="fa fa-gift"></i> {l s='Gift cards\' list' mod='psgiftcards'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
        </h3>
        <div v-if="giftcards.length == 0" class="alert alert-info" style="display:block;">
            <p>{l s='There is no giftcard yet. You can create your first giftcard by clicking on "Create new giftcard".' mod='psgiftcards'}<br /></p>
        </div>
        <div v-if="giftcards.length > 0" style="display:block;">
            <p>{l s='Find here the list of your gift cards. You can manage them.' mod='psgiftcards'}<br /></p>
        </div>
        <div class="row">
            <div v-for="(giftcard, index) in giftcards" class="giftcard col-lg-4">
                <div class="panel">
                    <div class="panel-heading">
                        <span>(( giftcard.product_name ))</span>
                        <span class="pull-right">#(( giftcard.id_giftcard ))</span>
                    </div>
                    <div class="panel-content">
                        {*<div class="giftcard-image" :style="'background-size:cover;background-position: center center;background-image:url('+giftcard.product_image+')'" :alt="giftcard.product_name">*}
                        <div class="giftcard-image" :alt="giftcard.product_name">
                            <img v-if="giftcard.product_image != ''"  style="width:100px;height:100px;" :src="giftcard.product_image" :alt="giftcard.product_name">
                        </div>
                        <div class="description">
                            <span v-if="giftcard.product_description != false" v-html="giftcard.product_description"></span>
                            <span class="price">(( giftcard.product_price )) (( giftcard.currencySymbol ))</span>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <a :href="giftcard.product_link" target="_blank" class="setup-customer btn btn-default pull-left"><i class="icon-cog"></i> {l s='Configure' mod='psgiftcards'}</a>
                        <button @click="untagProduct(giftcard.id_product, giftcard.id_giftcard)" type="button" class="btn btn-danger pull-right"><i class="icon-trash"></i> {l s='Delete' mod='psgiftcards'}</button>

                        <button v-if="giftcard.isActive == 1" @click="switchState(giftcard.id_product, giftcard.id_giftcard)" type="button" class="disable-giftcard btn btn-success pull-right"><i class="icon-check"></i> {l s='Enabled' mod='psgiftcards'}</button>
                        <button v-if="giftcard.isActive == 0" @click="switchState(giftcard.id_product, giftcard.id_giftcard)" type="button" class="enable-giftcard btn btn-warning pull-right"><i class="icon-remove"></i> {l s='Disabled' mod='psgiftcards'}</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="buttons">
            <a href="{$newProductLink|escape:'htmlall':'UTF-8'}" target="_blank" type="button" class="btn btn-primary"> {l s='Create new giftcard' mod='psgiftcards'}</a>
            <a type="button" @click="getGiftcards()" class="btn btn-warning"><i class="fa fa-refresh"></i></a>
        </div>
    </div>
</div>

<div class="panel col-lg-10 right-panel giftcards-right-panel">
    <h3>
        <i class="fa fa-pie-chart"></i> {l s='Some stats' mod='psgiftcards'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
    </h3>
    <div class="row" style="margin-left: 150px;">
        <div class="col-sm-6 col-lg-4">
            <div id="" data-toggle="tooltip" class="box-stats label-tooltip" style="color:#00B9DC">
                <div class="kpi-content">
                        <i class="icon-gift" style="color:#00B9DC"></i>
                            <span class="title">{l s='Total number of bought gift cards' mod='psgiftcards'}</span>
                    <span class="subtitle">{l s='Since start' mod='psgiftcards'}</span>
                    <span class="value">{$totalGiftcardSold|escape:'htmlall':'UTF-8'}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div id="" data-toggle="tooltip" class="box-stats label-tooltip color2">
                <div class="kpi-content">
                        <i class="icon-user"></i>
                            <span class="title">{l s='Total number of used gift cards' mod='psgiftcards'}</span>
                    <span class="subtitle">{l s='Since start' mod='psgiftcards'}</span>
                    <span class="value">{$totalGiftcardUsed|escape:'htmlall':'UTF-8'}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div id="" data-toggle="tooltip" class="box-stats label-tooltip color3">
                <div class="kpi-content">
                        <i class="icon-money"></i>
                            <span class="title">{l s='Total turnover from gift cards' mod='psgiftcards'}</span>
                    <span class="subtitle">{l s='Since start' mod='psgiftcards'}</span>
                    <span class="value">{$totalAmount|escape:'htmlall':'UTF-8'}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel col-lg-10 right-panel giftcards-right-panel">
    <h3>
        <i class="fa fa-history"></i> {l s='Gift cards history' mod='psgiftcards'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
    </h3>
    <table id="tableGiftcardHisotry" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>{l s='Gift card ID' mod='psgiftcards'}</th>
                <th>{l s='Order ID' mod='psgiftcards'}</th>
                <th>{l s='Client name' mod='psgiftcards'}</th>
                <th>{l s='Code' mod='psgiftcards'}</th>
                <th>{l s='Price' mod='psgiftcards'}</th>
                <th>{l s='Gift card status' mod='psgiftcards'}</th>
                <th>{l s='Purchase date' mod='psgiftcards'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$giftcardsHistory item=giftcard}
            <tr>
                <td>#{$giftcard['id_giftcard']|escape:'htmlall':'UTF-8'}</td>
                <td><a href="index.php?controller=AdminOrders&id_order={$giftcard['id_order']|escape:'htmlall':'UTF-8'}&vieworder&token={$tokenOrder|escape:'htmlall':'UTF-8'}" target="_blank">#{$giftcard['id_order']|escape:'htmlall':'UTF-8'}</a></td>
                <td>{$giftcard['name']|escape:'htmlall':'UTF-8'}</td>
                <td><a href="index.php?controller=AdminCartRules&id_cart_rule={$giftcard['id_cartRule']|escape:'htmlall':'UTF-8'}&updatecart_rule&token={$tokenCartRule|escape:'htmlall':'UTF-8'}" target="_blank">{$giftcard['code']|escape:'htmlall':'UTF-8'}</a></td>
                <td>{$giftcard['price']|escape:'htmlall':'UTF-8'}</td>
                <td>
                    {$giftcard['status']|escape:'htmlall':'UTF-8'}
                </td>
                <td>{$giftcard['purchase_date']|escape:'htmlall':'UTF-8'}</td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>
