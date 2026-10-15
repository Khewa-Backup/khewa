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
* Admin Attribute Dropdown tpl file
*}
<select name="attribute_list[]" id="attribute_list">
    <option value="">{l s='Select Store Attribute' mod='kbetsy'}</option>
    {foreach $options as $option}
        <option value="{$option['id_attribute_group']|escape:'htmlall':'UTF-8'}" {if $option['id_attribute_group'] == $attribute_id}selected="selected"{/if}>{$option['name']|escape:'htmlall':'UTF-8'}</option>
    {/foreach}
</select>
