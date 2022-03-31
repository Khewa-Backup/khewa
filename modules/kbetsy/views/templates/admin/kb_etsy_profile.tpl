{if isset($custom_pricing)}
    <div class="form-group" id="etsy_custom_pricing" style="display: none;">
        <label class="control-label col-lg-3 required">{l s='Custom Pricing' mod='kbetsy'}</label>
        <div class="col-lg-9">
            <div class="col-lg-3">
                <input type="text" name="custom_price" id="custom_price" value="{if !empty($custom_pricing_array)}{$custom_pricing_array['custom_price']|escape:'htmlall':'UTF-8'}{/if}">
            </div>
            <div class="col-lg-3">
                <select name="price_type" class=" fixed-width-xl" id="price_type">
                    <option value="Fixed" {if !empty($custom_pricing_array)}{if $custom_pricing_array['price_type'] == 'Fixed'}selected{/if}{/if}>{l s='Fixed' mod='kbetsy'}</option>
                    <option value="Percentage" {if !empty($custom_pricing_array)}{if $custom_pricing_array['price_type'] == 'Percentage'}selected{/if}{/if}>{l s='Percentage' mod='kbetsy'}</option>
                </select>
            </div>
            <div class="col-lg-3">
                <select name="price_reduction" class=" fixed-width-xl" id="price_reduction">
                    <option value="increase" {if !empty($custom_pricing_array)}{if $custom_pricing_array['price_reduction'] == 'increase'}selected{/if}{/if}>{l s='Increase' mod='kbetsy'}</option>
                    <option value="decrease" {if !empty($custom_pricing_array)}{if $custom_pricing_array['price_reduction'] == 'decrease'}selected{/if}{/if}>{l s='Decrease' mod='kbetsy'}</option>
                </select>
            </div>

        </div>
    </div>
{/if}

{if isset($is_size_chart_image_exists)}
    <script>
        var is_size_chart_image_exists = {$is_size_chart_image_exists|escape:'htmlall':'UTF-8'};
</script>
{/if}
<style type="text/css">
    .ac_results li {
        padding: 6px 5px;
    }
    .ac_odd {
        background-color: #f5f5f5;
    }
    .ac_over {
        background-color: #eaeaea;
        color: #555;
        cursor: pointer;
    }
</style>
<script>
var profile_confirmation_text = "{l s='Do you want to update profile product details ? Click OK to update profile product details on etsy else click Cancel to update only profile.' mod='kbetsy'}";
</script>
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
*}