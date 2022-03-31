<article class="mpm-featuredproducts-product-miniature mpm-featuredproducts-ps16 product-miniature product-miniature-slider js-product-miniature" data-id-product="{$product.id_product|escape:'html':'UTF-8'}" data-id-product-attribute="{$product.id_product_attribute|escape:'html':'UTF-8'}" itemscope itemtype="http://schema.org/Product">
    <div class="mpm-featuredproducts-img-container" >
        <a href="{$product.link|escape:'html':'UTF-8'}" class="{*thumbnail product-thumbnail*}">
            {if $settings->show_product_flags}
                <ul class="product-flags">
                    {if !empty($product.specific_prices) && isset($product.specific_prices.reduction_type) && $product.specific_prices.reduction_type === 'percentage'}
                        <li class="product-flag flag-price-reduction">-{$product.specific_prices.reduction * 100}%</li>
                    {/if}
                    {if !empty($product.new)}
                        <li class="product-flag flag-new">{l s='new' mod='featuredproducts'}</li>
                    {/if}
                    {if !empty($product.on_sale) && !empty($product.show_price)}
                        <li class="product-flag flag-onsale">{l s='sale' mod='featuredproducts'}</li>
                    {/if}
                </ul>
            {/if}

            <img class="mpm-featuredproducts-img" src="{$link->getImageLink($product.link_rewrite, $product.id_image, $settings->type_image)|escape:'html':'UTF-8'}" {*alt="{$product.cover.legend}"  data-full-size-image-url="{$product.cover.large.url}"*} >
        </a>
    </div>

    <div class="mpm-featuredproducts-product-info-container">

        {if $settings->show_product_variants || $settings->show_product_availability_status}
            <div class="mpm-featuredproducts-product-variants-block">

                {if isset($product.color_list)}

                    {if !$settings->show_product_variants}
                        <style>
                            .mpm-featuredproducts-product-variants-block .color-list-container {
                                visibility: hidden;
                            }
                        </style>
                    {/if}

                    <div class="color-list-container">{$product.color_list}</div>
                {/if}

                {if isset($product.available_for_order) && $product.available_for_order && !isset($restricted_country_mode)}
                    <span class="mpm-featuredproducts-product-availability-status" style="{if !$settings->show_product_availability_status}visibility: hidden{/if}">
                        {if ($product.allow_oosp || $product.quantity > 0)}
                            <i class="{if $product.quantity <= 0 && isset($product.allow_oosp) && !$product.allow_oosp}
                                            m-forbidden
                                         {elseif $product.quantity <= 0}
                                            m-forbidden
                                         {else} m-ic_check_black_18px{/if}">
                            </i>
                            {if $product.quantity <= 0}
                            {if $product.allow_oosp}
                                {if isset($product.available_later) && $product.available_later}
                                    {$product.available_later|escape:'html':'UTF-8'}
                                {else}
                                    {l s='In Stock' mod='featuredproducts'}
                                {/if}
                            {else}
                                {l s='Out of stock' mod='featuredproducts'}
                            {/if}
                        {else}
                            {if isset($product.available_now) && $product.available_now}
                                {$product.available_now|escape:'html':'UTF-8'}
                            {else}
                                {l s='In Stock' mod='featuredproducts'}
                            {/if}
                        {/if}
                        {elseif (isset($product.quantity_all_versions) && $product.quantity_all_versions > 0)}
                            <i class="m-forbidden"></i>
                            {l s='Out of stock' mod='featuredproducts'}
                            {*{l s='Available with different options'}*}
                        {else}
                            <i class="m-forbidden"></i>
                            {l s='Out of stock' mod='featuredproducts'}
                        {/if}
                    </span>
                {/if}
                <hr/>
            </div>
        {/if}

        {if $settings->show_product_name}
            <div class="mpm-featuredproducts-product-title-block">
                <h3 class="mpm-featuredproducts-product-title" itemprop="name">
                    <a href="{$product.link}">{$product.name|truncate:30:'...'|escape:'html':'UTF-8'}</a>
                </h3>
            </div>
        {/if}

        {if $settings->show_product_price}
            <div class="mpm-featuredproducts-product-price-block">
                {if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
                    {hook h="displayProductPriceBlock" product=$product type='before_price'}
                    {if $product.price_without_reduction > 0 && isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
                        {hook h="displayProductPriceBlock" product=$product type="old_price"}
                        <span class="old-price product-price">
                                    {displayWtPrice p=$product.price_without_reduction}
                                </span>
                        {hook h="displayProductPriceBlock" id_product=$product.id_product type="old_price"}
                    {/if}
                    <span class="price product-price">
                                {if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
                            </span>
                    {hook h="displayProductPriceBlock" product=$product type="price"}
                    {hook h="displayProductPriceBlock" product=$product type="unit_price"}
                    {hook h="displayProductPriceBlock" product=$product type='after_price'}
                {/if}
            </div>
        {/if}

        {if $settings->show_product_link_to_full_page || $settings->show_product_button_add || $settings->show_product_quickview}
            <div class="mpm-featuredproducts-product-actions-block">
                <div class="mpm-featuredproducts-link-to-product-page-block"
                     style="{if !$settings->show_product_link_to_full_page}
                                {if !$settings->show_product_button_add}
                                    display: none;
                                {else}
                                    visibility: hidden;
                                {/if}
                             {elseif $settings->show_product_link_to_full_page && !$settings->show_product_button_add && !$settings->show_product_quickview}
                                padding: 0;
                             {/if}">

                    <a href="{$product.link|escape:'html':'UTF-8'}" title="Go to product page"><i class="m-eye"></i></a>
                </div>


                {if $settings->show_product_button_add}
                    <div class="mpm-featuredproducts-add-to-cart-btn-block">
                        {if ($product.id_product_attribute == 0 || (isset($add_prod_display) && ($add_prod_display == 1))) && $product.available_for_order && !isset($restricted_country_mode) && $product.customizable != 2 && !$PS_CATALOG_MODE}
                            {if (!isset($product.customization_required) || !$product.customization_required) && ($product.allow_oosp || $product.quantity > 0)}
                                {capture}add=1&amp;id_product={$product.id_product|intval|escape:'html':'UTF-8'}{if isset($product.id_product_attribute) && $product.id_product_attribute}&amp;ipa={$product.id_product_attribute|intval|escape:'html':'UTF-8'}{/if}{if isset($static_token)}&amp;token={$static_token|escape:'html':'UTF-8'}{/if}{/capture}
                                <a class="ajax_add_to_cart_button add-to-cart mpm-featuredproducts-add-to-cart-btn" href="{$link->getPageLink('cart', true, NULL, $smarty.capture.default, false)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Add to cart' mod='featuredproducts'}" data-id-product-attribute="{$product.id_product_attribute|intval}" data-id-product="{$product.id_product|intval}" data-minimal_quantity="{if isset($product.product_attribute_minimal_quantity) && $product.product_attribute_minimal_quantity >= 1}{$product.product_attribute_minimal_quantity|intval}{else}{$product.minimal_quantity|intval}{/if}">
                                    {l s='ADD TO CART' mod='featuredproducts'}
                                </a>
                            {else}
                                <span class="ajax_add_to_cart_button add-to-cart mpm-featuredproducts-add-to-cart-btn disabled">
                                    {l s='ADD TO CART' mod='featuredproducts'}
                                </span>
                            {/if}
                        {/if}
                    </div>
                {/if}

                <div class="mpm-featuredproducts-quickview-block"
                     style="{if !$settings->show_product_quickview}
                                {if !$settings->show_product_button_add}
                                    display: none;
                                {else}
                                    visibility: hidden;
                                {/if}
                             {elseif $settings->show_product_quickview && !$settings->show_product_button_add && !$settings->show_product_link_to_full_page}
                                padding: 0;
                             {/if}">


                    <a class="quick-view" href="{$product.link|escape:'html':'UTF-8'}" rel="{$product.link|escape:'html':'UTF-8'}" data-fancybox-target="{$product.link|escape:'html':'UTF-8'}" title="Quickview">
                        <i class="m-search"></i>
                    </a>
                </div>
            </div>
        {/if}

        {if $settings->show_product_description}
            <div class="mpm-featuredproducts-product-description-block">{$product.description_short|truncate:100:'...' nofilter}</div>
        {/if}
    </div>
</article>