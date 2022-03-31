<div class="block_add_new_product">
    <div class="product-pack-attendee">
        <input class="attendee" id="attendee" name="AttendeeId" type="text" value="" placeholder="{l s='Search for a product' mod='featuredproducts'}"/>
    </div>
    <div class="product-pack-button">
        <button type="button" id="add_products_item" class="btn btn-default">
            <i class="icon-plus-sign-alt"></i> {l s='Add this product' mod='featuredproducts'}
        </button>
        <input id="{$class|escape:'htmlall':'UTF-8'}" name="{$class|escape:'htmlall':'UTF-8'}"  type="hidden" value="{if isset($ids) && $ids}{$ids|escape:'htmlall':'UTF-8'}{/if}" />
        <input id="class_products_add"  type="hidden" value="productIds" />
    </div>
    <div style="clear: both"></div>
</div>

<div class="block_added_products">
    <div class="header_block_added_products panel-heading"><i class="icon-plus-sign-alt"></i>{l s='Added products' mod='featuredproducts'}</div>
    {$list}
</div>
