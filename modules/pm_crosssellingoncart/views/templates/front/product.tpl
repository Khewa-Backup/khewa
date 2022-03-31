{**
 * pm_crosssellingoncart
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2019 - http://www.presta-module.com
 * @license   Commercial
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 *}

<article class="product-miniature js-product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}" itemscope itemtype="http://schema.org/Product">
    <div class="thumbnail-container{if !empty($csoc_display["{$csoc_prefix}_DISPLAY_BUTTON"])} with-button{/if}">
        {block name='product_thumbnail'}
            {if !empty($csoc_display["{$csoc_prefix}_DISPLAY_IMG"])}
                {if $product.cover}
                    <a href="{$product.url}" class="thumbnail product-thumbnail">
                        <img
                            src="{$product.cover.bySize.{$imageSize}.url}"
                            alt="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:30:'...'}{/if}"
                            data-full-size-image-url="{$product.cover.large.url}"
                        />
                    </a>
                {else}
                    <a href="{$product.url}" class="thumbnail product-thumbnail">
                        <img src="{$urls.no_picture_image.bySize.{$imageSize}.url}" />
                    </a>
                {/if}
            {/if}
        {/block}

        <div class="product-description">
            {block name='product_name'}
                {if !empty($csoc_display["{$csoc_prefix}_DISPLAY_TITLE"])}
                    <h2 class="h3 product-title" itemprop="name"><a href="{$product.url}">{$product.name|truncate:30:'...'}</a></h2>
                {/if}
            {/block}

            {block name='product_price_and_shipping'}
                {if $product.show_price && !empty($csoc_display["{$csoc_prefix}_DISPLAY_PRICE"])}
                    <div
                        class="product-price-and-shipping"
                        itemprop="offers"
                        itemscope
                        itemtype="https://schema.org/Offer"
                    >
                        <meta itemprop="priceCurrency" content="{$currency.iso_code}">
                        {if $product.has_discount}
                            {hook h='displayProductPriceBlock' product=$product type="old_price"}

                            <span class="sr-only">{l s='Regular price' d='Shop.Theme.Catalog'}</span>
                            <span class="regular-price">{$product.regular_price}</span>
                            {if $product.discount_type === 'percentage'}
                                <span class="discount-percentage discount-product">{$product.discount_percentage}</span>
                            {elseif $product.discount_type === 'amount'}
                                <span class="discount-amount discount-product">{$product.discount_amount_to_display}</span>
                            {/if}
                        {/if}

                        {hook h='displayProductPriceBlock' product=$product type="before_price"}

                        <span class="sr-only">{l s='Price' d='Shop.Theme.Catalog'}</span>
                        <span itemprop="price" class="price">{$product.price}</span>

                        {hook h='displayProductPriceBlock' product=$product type='unit_price'}

                        {hook h='displayProductPriceBlock' product=$product type='weight'}
                    </div>
                {/if}

                {block name='product_reviews'}
                    {hook h='displayProductListReviews' product=$product}
                {/block}
            {/block}

            {block name='button_display'}
                {if !empty($csoc_display["{$csoc_prefix}_DISPLAY_BUTTON"])}
                    <div class="button_display text-xs-center">
                        <form action="{$urls.pages.cart}" method="post">
                            <input type="hidden" name="token" value="{$static_token}">
                            <input type="hidden" name="id_product" value="{$product.id}" id="product_page_product_id">
                            <input type="hidden" name="id_customization" value="0" id="product_customization_id">
                            <input type="hidden" name="qty" value="{$product.minimal_quantity}">
                            <button class="btn btn-primary add-to-cart" data-button-action="add-to-cart" type="submit">
                                <i class="material-icons shopping-cart"></i>
                                {l s='Add to cart' mod='pm_crosssellingoncart'}
                            </button>
                        </form>
                    </div>
                {/if}
            {/block}
        </div>

        {block name='product_flags'}
            <ul class="product-flags">
                {foreach from=$product.flags item=flag}
                    {if (!empty($flag.label))}
                        <li class="product-flag {$flag.type}">{$flag.label}</li>
                    {/if}
                {/foreach}
            </ul>
        {/block}

        <div class="highlighted-informations{if !$product.main_variants} no-variants{/if} hidden-sm-down">
            {block name='quick_view'}
                <a class="quick-view" href="#" data-link-action="quickview">
                    <i class="material-icons search">&#xE8B6;</i> {l s='Quick view' d='Shop.Theme.Actions'}
                </a>
            {/block}

            {block name='product_variants'}
                {if $product.main_variants}
                    {include file='catalog/_partials/variant-links.tpl' variants=$product.main_variants}
                {/if}
            {/block}
        </div>
    </div>
</article>
