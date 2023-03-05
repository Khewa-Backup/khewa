
{if $settings->use_custom_design}

    <article class="mpm-featuredproducts-product-miniature product-miniature product-miniature-slider js-product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}" itemscope itemtype="http://schema.org/Product">
        <div class="mpm-featuredproducts-img-container">
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

            <a href="{$product.url}" class="thumbnail product-thumbnail">
                <img class="mpm-featuredproducts-img" src="{$product.cover.bySize.{$settings->type_image}.url}" alt="{$product.cover.legend}"  data-full-size-image-url="{$product.cover.large.url}" >
            </a>
        </div>

        <div class="mpm-featuredproducts-product-info-container">

            {if $settings->show_product_variants || $settings->show_product_availability_status}
                <div class="mpm-featuredproducts-product-variants-block">

                    <div class="variant-links" style="{if !$product.main_variants || !$settings->show_product_variants}visibility: hidden{/if}">
                        {foreach from=$product.main_variants item=variant}
                            <a href="{$variant.url}"
                               class="{$variant.type}"
                               title="{$variant.name}"
                                    {if $variant.html_color_code} style="background-color: {$variant.html_color_code}" {/if}
                                    {if $variant.texture} style="background-image: url({$variant.texture})" {/if}
                            ><span class="sr-only">{$variant.name}</span></a>
                        {/foreach}
                        <span class="js-count count"></span>
                    </div>

                    <span class="mpm-featuredproducts-product-availability-status" style="{if !$product.show_availability || !$settings->show_product_availability_status}visibility: hidden{/if}">
                        {if $product.availability === 'unavailable'}
                            <i class="m-forbidden"></i>
                            {l s='Out of stock' mod='featuredproducts'}
                        {else}
                            <i class="m-ic_check_black_18px"></i>
                            {l s='In stock' mod='featuredproducts'}
                        {/if}
                    </span>

                    <hr/>
                </div>
            {/if}

            {if $settings->show_product_name}
                <div class="mpm-featuredproducts-product-title-block">
                    <h3 class="mpm-featuredproducts-product-title" itemprop="name">
                        <a href="{$product.url}">{$product.name|truncate:30:'...'}</a>
                    </h3>
                </div>
            {/if}

            {if $settings->show_product_price}
                <div class="mpm-featuredproducts-product-price-block">
                    {if $product.has_discount}
                        {hook h='displayProductPriceBlock' product=$product type="old_price"}
                        <span class="regular-price">{$product.regular_price}</span>
                    {/if}
                    {hook h='displayProductPriceBlock' product=$product type="before_price"}
                    <span itemprop="price" class="price">{$product.price}</span>
                    {hook h='displayProductPriceBlock' product=$product type='unit_price'}
                    {hook h='displayProductPriceBlock' product=$product type='weight'}
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

                    <a href="{$product.url}" title="Go to product page"><i class="m-eye"></i></a>
                    </div>

                    {if $settings->show_product_button_add}
                        <div class="mpm-featuredproducts-add-to-cart-btn-block product-add-to-cart">
                            <form action="{$urls.pages.cart}" method="post" id="add-to-cart-or-refresh">
                                <input type="hidden" name="token" value="{$static_token}">
                                <input type="hidden" name="id_product" value="{$product.id}" id="product_page_product_id">
                                <button {if $product.availability === 'unavailable'}disabled{/if} class="btn add-to-cart mpm-featuredproducts-add-to-cart-btn" data-button-action="add-to-cart" type="submit" {if !$product.add_to_cart_url}disabled{/if}>
                                    {l s='ADD TO CART' mod='featuredproducts'}
                                </button>
                            </form>
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

                        <a class="quick-view" href="#" data-link-action="quickview" title="Quick View">
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
{else}
    {include file='catalog/_partials/miniatures/product.tpl' variants=$product.main_variants}
{/if}
