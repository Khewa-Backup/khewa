<div class="content_fields">
    {*<div class="url_base_setting"><a href="{$url_base|escape:'htmlall':'UTF-8'}"><i class="icon-refresh process-icon-refresh"></i>{l s='Reset filters' mod='updateproducts'}</a></div>*}

    <div class="productTabs ">
        <div class="fields_list list-group">
            <a class="list-group-item active" data-tab="updateTabInformation">{l s='Information' mod='updateproducts'}</a>
            <a class="list-group-item" data-tab="updateTabPrices">{l s='Prices' mod='updateproducts'}</a>
            <a class="list-group-item" data-tab="updateTabSeo">{l s='SEO' mod='updateproducts'}</a>
            <a class="list-group-item" data-tab="updateTabAssociations">{l s='Associations' mod='updateproducts'}</a>
            <a class="list-group-item" data-tab="updateTabShipping">{l s='Shipping' mod='updateproducts'}</a>
            <a class="list-group-item" data-tab="updateTabCombinations">{l s='Combinations' mod='updateproducts'}</a>
            <a class="list-group-item" data-tab="updateTabQuantities">{l s='Quantities' mod='updateproducts'}</a>
            <a class="list-group-item" data-tab="updateTabImages">{l s='Images' mod='updateproducts'}</a>
            <a class="list-group-item" data-tab="updateTabFeatures">{l s='Features' mod='updateproducts'}</a>
            <a class="list-group-item" data-tab="updateTabCustomization">{l s='Customization' mod='updateproducts'}</a>
            <a class="list-group-item" data-tab="updateTabAttachments">{l s='Attachments' mod='updateproducts'}</a>
            <a class="list-group-item" data-tab="updateTabSuppliers">{l s='Suppliers' mod='updateproducts'}</a>
        </div>
    </div>

    <div class="block_all_fields">
        {foreach $all_fields AS $key => $block}
            <div class="field_list_base field_list_{$key|escape:'htmlall':'UTF-8'} {if $key == 'updateTabInformation' || $key == 'updateTabInformation'}active{/if}">
                <div class="field_list_header">
                    <input data-page="update" class="search_base_fields" placeholder="{l s='Search' mod='updateproducts'}">
                </div>
                <ul class="block_base_fields">
                    {foreach  $block AS $value}
                        {if $set && !in_array($value['name'], $set)}
                            <li data-tab="{$value['tab']|escape:'htmlall':'UTF-8'}" data-name="{$value['name']|escape:'htmlall':'UTF-8'}" data-value="{$value['val']|escape:'htmlall':'UTF-8'}"  {if isset($value['hint']) && $value['hint']}class="isset_hint" data-hint="{$value['hint']|escape:'htmlall':'UTF-8'}"{/if}>
                                {if isset($value['hint']) && $value['hint']}  <i class="icon-info icon-info-fields"></i>  {/if}
                                {$value['name']|escape:'htmlall':'UTF-8'}
                            </li>
                        {/if}
                        {if !$set}
                            <li data-tab="{$value['tab']|escape:'htmlall':'UTF-8'}"  data-name="{$value['name']|escape:'htmlall':'UTF-8'}" data-value="{$value['val']|escape:'htmlall':'UTF-8'}" {if isset($value['hint']) && $value['hint']}class="isset_hint"  data-hint="{$value['hint']|escape:'htmlall':'UTF-8'}"{/if}>
                                {if isset($value['hint']) && $value['hint']}  <i class="icon-info icon-info-fields"></i>  {/if}
                                {$value['name']|escape:'htmlall':'UTF-8'}
                            </li>
                        {/if}
                    {/foreach}
                </ul>
            </div>
        {/foreach}

    </div>
    <div class="navigation-fields navigation-fields-base">

        <div class="navigation-button">
            <button data-page="update" type="button" class="btn btn-default add_base_filds_all add_fild_right">{l s='Add all ' mod='updateproducts'}<i class="icon-arrow-right"></i></button>
            <button data-page="update" type="button" class="btn btn-default add_base_filds add_fild_right">{l s='Add ' mod='updateproducts'}<i class="icon-arrow-right"></i></button>
            <button data-page="update" type="button" class="btn btn-default remove_base_filds add_fild_right">{l s='Remove ' mod='updateproducts'}<i class="icon-arrow-left"></i></button>
            <button data-page="update" type="button" class="btn btn-default remove_base_filds_all add_fild_right">{l s='Remove all ' mod='updateproducts'}<i class="icon-arrow-left"></i></button>
        </div>

    </div>
    <div class="block_selected_fields">

        <div class="field_list_selected">
            <div class="field_list_header">
                <input data-page="update" class="search_selected_fields" placeholder="{l s='Search' mod='updateproducts'}">
            </div>
            <ul class="selected_fields">
                {foreach from=$selected key=key item=select}
                    <li data-tab="{$select['tab']|escape:'htmlall':'UTF-8'}"  data-name="{$select['name']|escape:'htmlall':'UTF-8'}" data-value="{$key|escape:'htmlall':'UTF-8'}" class="{if isset($select['hint']) && $select['hint']} isset_hint {/if}{if isset($select['disabled']) && $select['disabled']} disable_fields {/if}"  {if isset($select['hint']) && $select['hint']}data-hint="{$select['hint']|escape:'htmlall':'UTF-8'}"{/if}>
                        {if isset($select['hint']) && $select['hint']}  <i class="icon-info icon-info-fields"></i>  {/if}
                        {$select['name']|escape:'htmlall':'UTF-8'}
                        <i class="icon-arrows icon-arrows-select-fields"></i>
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>
</div>