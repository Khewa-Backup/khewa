{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* @category  PrestaShop Module
* @author    knowband.com <support@knowband.com>
* @copyright 2017 Knowband
* @license   see file: LICENSE.txt
*
* Description
*
* Admin Customize Product Title Placeholders tpl file
*}
<div class="alert alert-info" id="customize_product_title_block" style="display:none;">
    <h4>{l s='You can use the following place-holders to customize the product title on Etsy' mod='kbetsy'}</h4>
    <ul>
        <li>{'{id_product}'|escape:'htmlall':'UTF-8'} {l s=' for Product ID' mod='kbetsy'}</li>
        <li>{'{product_title}'|escape:'htmlall':'UTF-8'} {l s=' for Product title' mod='kbetsy'}</li>
        <li>{'{manufacturer_name}'|escape:'htmlall':'UTF-8'} {l s=' for Manufacturer name' mod='kbetsy'}</li>
        <li>{'{supplier_name}'|escape:'htmlall':'UTF-8'} {l s=' for Supplier name' mod='kbetsy'}</li>
        <li>{'{reference}'|escape:'htmlall':'UTF-8'} {l s=' for Product Reference' mod='kbetsy'}</li>
{*        <li>{'{supplier_reference}'} {l s=' for Supplier Reference'}</li>*}
        <li>{'{ean13}'|escape:'htmlall':'UTF-8'} {l s=' for Product EAN13' mod='kbetsy'}</li>
        <li>{'{short_description}'|escape:'htmlall':'UTF-8'} {l s=' for Product Short Description' mod='kbetsy'}</li>
        <li>{'{price}'|escape:'htmlall':'UTF-8'} {l s=' for Product Price' mod='kbetsy'}</li>
    </ul>
</div>
{*exclude-mayank*}
<div class="kb-add-etsy-product" style="display: none;">
    <input type="hidden" name="exclude_product" value="{if isset($product_mapping)}{if !empty($product_mapping) && is_array($product_mapping)}{foreach $product_mapping as $product}{$product['product']->id|escape:'htmlall':'UTF-8'}-{/foreach}{/if}{/if}">
    <input type="hidden" name="kbetsy_selected_products" value="{if isset($product_mapping)}{if !empty($product_mapping) && is_array($product_mapping)}{foreach $product_mapping as $product}{$product['product']->id|escape:'htmlall':'UTF-8'}-{/foreach}{/if}{/if}">
    <div>
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <span class="title_box">{l s='ID' mod='kbetsy'}</span>
                    </th>
                    <th>
                        <span class="title_box">{l s='Product' mod='kbetsy'}</span>
                    </th>

                    <th>
                        <span class="title_box">{l s='Action' mod='kbetsy'}</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                {if isset($product_mapping)}
                    {if !empty($product_mapping) && is_array($product_mapping)}
                        {foreach $product_mapping as $product}
                            <tr>
                                <td class="kb-etsy-product-id" style="text-align: center;">{$product['product']->id|escape:'htmlall':'UTF-8'}</td>
                                <td>
                                    {$product['product']->name|escape:'htmlall':'UTF-8'}
                                </td>
                                <td style="text-align: center;">
                                    <span class="kb-product-remove" style="color: #ff0000;cursor: pointer;"><i class="icon-remove"></i></span>
                                </td>
                            </tr>
                        {/foreach}
                    {/if}
                {/if}
            </tbody>
        </table>
    </div>
</div>


{*exclude-mayank*}
<script>
    var controller_path = "{$controller_path}";{*Variable contains URL content, escape not required*}
    var KbcurrentToken = "{$KbcurrentToken|escape:'htmlall':'UTF-8'}";

</script>