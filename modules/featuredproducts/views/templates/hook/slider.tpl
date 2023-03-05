<div class="container mpm-featuredproducts-slider-container">
    <div class="row mpm-featuredproducts-slider-head">
        <h1 class="col-md-6 col-sm-6 col-xs-6 mpm-featuredproducts-slider-title">{$settings->title|escape:'htmlall':'UTF-8'}</h1>
        <div class="col-md-6 col-sm-6 col-xs-6 mpm-featuredproducts-slider-navigation"></div>
    </div>

    <div class="mpm-featuredproducts-slider-navigation-status"></div>

    <div class="mpm-featuredproducts-slider-wrapper products row products-grid {if !$settings->use_custom_design}mpm-featuredproducts-default-miniature-design{/if}"
         data-pause="{$settings->pause|escape:'htmlall':'UTF-8'}"
         data-speed="{$settings->speed|escape:'htmlall':'UTF-8'}"
         data-show="{$settings->number_of_visible_slides|escape:'htmlall':'UTF-8'}"
         data-scroll="{$settings->scroll_slides|escape:'htmlall':'UTF-8'}"
         data-control="{$settings->show_control|escape:'htmlall':'UTF-8'}"
         data-arrow="{$settings->show_navigation_arrow|escape:'htmlall':'UTF-8'}"
         data-auto="{$settings->auto_scroll|escape:'htmlall':'UTF-8'}"
         data-stop="{$settings->stop_after_hover|escape:'htmlall':'UTF-8'}"
         data-count-sliders="{count($products)|escape:'htmlall':'UTF-8'}"

         data-slider-navigation-color="{$settings->slider_navigation_color|escape:'htmlall':'UTF-8'}"
         data-slide-background-color="{$settings->slide_background_color|escape:'htmlall':'UTF-8'}"
         data-product-name-color="{$settings->product_name_color|escape:'htmlall':'UTF-8'}"
         data-product-name-hover-color="{$settings->product_name_hover_color|escape:'htmlall':'UTF-8'}"
         data-product-description-color="{$settings->product_description_color|escape:'htmlall':'UTF-8'}"
         data-product-price-color="{$settings->product_price_color|escape:'htmlall':'UTF-8'}"
         data-product-regular-price-color="{$settings->product_regular_price_color|escape:'htmlall':'UTF-8'}"
         data-product-link-to-full-page-color="{$settings->product_link_to_full_page_color|escape:'htmlall':'UTF-8'}"
         data-product-link-to-full-page-hover-color="{$settings->product_link_to_full_page_hover_color|escape:'htmlall':'UTF-8'}"
         data-product-quickview-color="{$settings->product_quickview_color|escape:'htmlall':'UTF-8'}"
         data-product-quickview-hover-color="{$settings->product_quickview_hover_color|escape:'htmlall':'UTF-8'}"
         data-product-addtocart-background-color="{$settings->product_addtocart_background_color|escape:'htmlall':'UTF-8'}"
         data-product-addtocart-background-hover-color="{$settings->product_addtocart_hover_background_color|escape:'htmlall':'UTF-8'}"
         data-product-addtocart-color="{$settings->product_addtocart_color|escape:'htmlall':'UTF-8'}"
         data-product-addtocart-hover-color="{$settings->product_addtocart_hover_color|escape:'htmlall':'UTF-8'}"
         data-product-availability-status-text-color="{$settings->product_availability_status_text_color|escape:'htmlall':'UTF-8'}"
         data-product-availability-status-icon-color="{$settings->product_availability_status_icon_color|escape:'htmlall':'UTF-8'}"
         data-ps-version="{$ps_version|escape:'htmlall':'UTF-8'}">

        
        {foreach from=$products item="product"}
            {include file=$path_to_product_min product=$product}
        {/foreach}
    </div>
</div>

