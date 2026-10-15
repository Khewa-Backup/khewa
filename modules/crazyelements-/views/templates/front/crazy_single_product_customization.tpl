{if $product.is_customizable && count($product.customizations.fields)}
    {block name='product_customization'}
    {include file="catalog/_partials/product-customization.tpl" customizations=$product.customizations}
    {/block}
{/if}