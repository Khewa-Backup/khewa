{*
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{block name='related_products_by_product'}
    {if $related_product_blocks}
        {foreach $related_product_blocks as $related_products}
            <section class="{if isset($ewrelatedproducts_type_visualization) && $ewrelatedproducts_type_visualization == 1}related-products-container{else}product-accessories{/if} clearfix">
                {if isset($ewrelatedproducts_show_title) && $ewrelatedproducts_show_title == 1}
                    <p class="h5 title-related-products text-uppercase">{if isset($ewrelatedproducts_custom_title) && $ewrelatedproducts_custom_title != ''}{$ewrelatedproducts_custom_title|escape:'html':'UTF-8'}{else}{l s='Other products of type' mod='ewrelatedproductspro'} "{$related_products.type_products}" {l s='with value' mod='ewrelatedproductspro'} "{$related_products.type_value_products}"{/if}</p>
                {/if}
                <div class="products{if isset($ewrelatedproducts_type_visualization) && $ewrelatedproducts_type_visualization == 1} owl-carousel owl-theme{/if}">
                    {foreach $related_products.products as $related_product}
                        {block name='product_miniature'}
                            {if isset($ewrelatedproducts_type_visualization) && $ewrelatedproducts_type_visualization == 1}<div class="item">{/if}
                                {block name='product_miniature_item'}
                                    <article class="product-miniature js-product-miniature" data-id-product="{$related_product.id_product|escape:'html':'utf-8'}" data-id-product-attribute="{$related_product.id_product_attribute|escape:'html':'utf-8'}" itemscope itemtype="http://schema.org/Product">
                                        <div class="thumbnail-container">
                                            {block name='product_thumbnail'}
                                                {if $related_product.cover}
                                                    <a href="{$related_product.url|escape:'html':'utf-8'}" class="thumbnail product-thumbnail">
                                                        {if isset($ewrelatedproducts_lazyload_images) && $ewrelatedproducts_lazyload_images == 1 && isset($ewrelatedproducts_type_visualization) && $ewrelatedproducts_type_visualization == 1}
                                                            <img
                                                                class="owl-lazy"
                                                                data-src="{$related_product.cover.bySize.home_default.url|escape:'html':'utf-8'}"
                                                                alt="{if !empty($related_product.cover.legend)}{$related_product.cover.legend|escape:'html':'utf-8'}{else}{$related_product.name|truncate:30:'...'}{/if}"
                                                                data-full-size-image-url="{$related_product.cover.large.url|escape:'html':'utf-8'}"
                                                            />
                                                        {else}
                                                            <img
                                                                src="{$related_product.cover.bySize.home_default.url|escape:'html':'utf-8'}"
                                                                alt="{if !empty($related_product.cover.legend)}{$related_product.cover.legend|escape:'html':'utf-8'}{else}{$related_product.name|truncate:30:'...'}{/if}"
                                                                data-full-size-image-url="{$related_product.cover.large.url|escape:'html':'utf-8'}"
                                                            />
                                                        {/if}
                                                    </a>
                                                {else}
                                                    <a href="{$related_product.url|escape:'html':'utf-8'}" class="thumbnail product-thumbnail">
                                                        <img src="{$urls.no_picture_image.bySize.home_default.url|escape:'html':'utf-8'}" />
                                                    </a>
                                                {/if}
                                            {/block}

                                            <div class="product-description">
                                                {block name='product_name'}
                                                    {if $page.page_name == 'index'}
                                                        <h3 class="h3 product-title" itemprop="name"><a href="{$related_product.url|escape:'html':'utf-8'}">{$related_product.name|truncate:30:'...'}</a></h3>
                                                    {else}
                                                        <h2 class="h3 product-title" itemprop="name"><a href="{$related_product.url|escape:'html':'utf-8'}">{$related_product.name|truncate:30:'...'}</a></h2>
                                                    {/if}
                                                {/block}

                                                {block name='product_price_and_shipping'}
                                                    {if $related_product.show_price}
                                                        <div class="product-price-and-shipping">
                                                            {if $related_product.has_discount}
                                                                {hook h='displayProductPriceBlock' product=$related_product type="old_price"}

                                                                <span class="sr-only">{l s='Regular price' mod='ewrelatedproductspro'}</span>
                                                                <span class="regular-price">{$related_product.regular_price|escape:'html':'utf-8'}</span>
                                                                {if $related_product.discount_type === 'percentage'}
                                                                    <span class="discount-percentage discount-product">{$related_product.discount_percentage|escape:'html':'utf-8'}</span>
                                                                {elseif $related_product.discount_type === 'amount'}
                                                                    <span class="discount-amount discount-product">{$related_product.discount_amount_to_display|escape:'html':'utf-8'}</span>
                                                                {/if}
                                                            {/if}

                                                            {hook h='displayProductPriceBlock' product=$related_product type="before_price"}

                                                            <span class="sr-only">{l s='Price' mod='ewrelatedproductspro'}</span>
                                                            <span itemprop="price" class="price">{$related_product.price|escape:'html':'utf-8'}</span>

                                                            {hook h='displayProductPriceBlock' product=$related_product type='unit_price'}

                                                            {hook h='displayProductPriceBlock' product=$related_product type='weight'}
                                                        </div>
                                                    {/if}
                                                {/block}

                                                {block name='product_reviews'}
                                                    {hook h='displayProductListReviews' product=$related_product}
                                                {/block}
                                            </div>

                                            <!-- @todo: use include file='catalog/_partials/product-flags.tpl'} -->
                                            {block name='product_flags'}
                                                <ul class="product-flags">
                                                    {foreach from=$related_product.flags item=flag}
                                                        <li class="product-flag {$flag.type|escape:'html':'utf-8'}">{$flag.label|escape:'html':'utf-8'}</li>
                                                    {/foreach}
                                                </ul>
                                            {/block}

                                            <div class="highlighted-informations{if !$related_product.main_variants} no-variants{/if} hidden-sm-down">
                                                {block name='quick_view'}
                                                    <a class="quick-view" href="#" data-link-action="quickview">
                                                        <i class="material-icons search">&#xE8B6;</i> {l s='Quick view' mod='ewrelatedproductspro'}
                                                    </a>
                                                {/block}

                                                {block name='product_variants'}
                                                    {if $related_product.main_variants}
                                                        {include file='catalog/_partials/variant-links.tpl' variants=$related_product.main_variants}
                                                    {/if}
                                                {/block}
                                            </div>
                                        </div>
                                    </article>
                                {/block}
                            {if isset($ewrelatedproducts_type_visualization) && $ewrelatedproducts_type_visualization == 1}</div>{/if}
                        {/block}
                    {/foreach}
                </div>
            </section>
        {/foreach}
    {/if}
{/block}

<script type="text/javascript">
    var ewrelatedproducts_lazyload_images = "{$ewrelatedproducts_lazyload_images|escape:'htmlall':'UTF-8'}";
    document.addEventListener('DOMContentLoaded', function() {
        $('.related-products-container .products.owl-carousel').find('.product-thumbnail img.owl-lazy').each(function(){
            if(!$(this).attr('src') || $(this).attr('src') == 'undefined'){
                $(this).attr('src', $(this).attr('data-src'));
                $(this).attr('data-src', null);
            }
        });
    });
</script>

<style>
    .related-products-container .owl-loaded .product-thumbnail img.owl-lazy{
        opacity: 1;
    }
</style>
