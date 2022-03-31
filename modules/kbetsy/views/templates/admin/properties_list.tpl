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
* Admin Properties List tpl file
*}
{foreach $property_list as $property}
<div class="form-group">
    <label class="control-label col-lg-3 {if $property['required'] == "1"} required {/if}">
        {$property['name']|escape:'htmlall':'UTF-8'}
    </label>
    <div class="col-lg-9">
        <select name="property_attr[{$property['id']|escape:'htmlall':'UTF-8'}]{if $property['multi'] == "1"}[]{/if}" class=" fixed-width-xl" id="property[{$property['id']|escape:'htmlall':'UTF-8'}]" {if $property['multi'] == "1"} multiple="multiple" {/if}>
            {if $property['multi'] != "1"}<option value="">{l s='Select' mod='kbetsy'}</option>{/if}
            {foreach $property['values'] as $option}
                {if in_array($option['id'], $property['selected'])}
                <option value="{$option['id']|escape:'htmlall':'UTF-8'}" selected="selected">{$option['name']|escape:'htmlall':'UTF-8'}</option>
                {else}
                <option value="{$option['id']|escape:'htmlall':'UTF-8'}">{$option['name']|escape:'htmlall':'UTF-8'}</option>
                {/if}
            {/foreach}
        </select>
    </div>
</div>
{/foreach}