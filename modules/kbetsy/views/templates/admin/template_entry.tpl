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
<div class="form-group">
    <div class="col-lg-3">
        <input type="hidden" class='existing_entry' name="template_entry[{$template_entry['id_etsy_shipping_templates_entries']|escape:'htmlall':'UTF-8'}][existing_entry]" value="{$template_entry['existing_entry']|escape:'htmlall':'UTF-8'}" />
        <input type="hidden" class='entry_id' name="template_entry[{$template_entry['id_etsy_shipping_templates_entries']|escape:'htmlall':'UTF-8'}][id_etsy_shipping_templates_entries]" value="{$template_entry['id_etsy_shipping_templates_entries']|escape:'htmlall':'UTF-8'}" />
        {if $template_entry['shipping_entry_destination_region_id']}
            {assign var="type" value="2"}
        {else if $template_entry['shipping_entry_destination_country_id']}
            {assign var="type" value="1"}
        {else}
            {assign var="type" value="1"}
        {/if}
        <label class="control-label required col-lg-12" style="display: block; text-align: left">{l s='Destination Type' mod='kbetsy'}</label>
        <div class="col-lg-12" style="display: block">
            <select name="template_entry[{$template_entry['id_etsy_shipping_templates_entries']|escape:'htmlall':'UTF-8'}][destination_type]" class="destination_type" {if $template_entry['existing_entry'] == true} disabled='disabled' {/if}>
                {if $template_entry['shipping_entry_destination_country_id']}
                <option value="1" selected="">{l s='Country' mod='kbetsy'}</option>
                {else}
                <option value="1">{l s='Country' mod='kbetsy'}</option>
                {/if}
                {if $template_entry['shipping_entry_destination_region_id']}
                <option value="2" selected="">{l s='Region' mod='kbetsy'}</option>
                {else}
                <option value="2">{l s='Region' mod='kbetsy'}</option>
                {/if}
            </select>
            <p class="help-block">{l s='Choose a destination type as country or region' mod='kbetsy'}</p>
        </div>
    </div>
    <div class="col-lg-3 country_list" style="{if $type != "1"} display: none; {/if}">
        <label class="control-label required col-lg-12" style="display: block; text-align: left">{l s='Destination Country' mod='kbetsy'}</label>
        <div class="col-lg-12" style="display: block">
            <select name="template_entry[{$template_entry['id_etsy_shipping_templates_entries']|escape:'htmlall':'UTF-8'}][shipping_desination_country]" {if $template_entry['existing_entry'] == true} disabled='disabled' {/if}>
                {if $template_entry['shipping_entry_destination_country_id'] == 0} 
                    <option value="0" selected="selected">{l s='Anywhere else' mod='kbetsy'}</option>
                {else}
                    <option value="0" >{l s='Anywhere else' mod='kbetsy'}</option>
                {/if}
                {foreach $countries_list as $country}
                {if $template_entry['shipping_entry_destination_country_id'] == $country['id_option']} 
                <option value="{$country['id_option']|escape:'htmlall':'UTF-8'}" selected="selected">{$country['name']|escape:'htmlall':'UTF-8'}</option>
                {else}
                <option value="{$country['id_option']|escape:'htmlall':'UTF-8'}">{$country['name']|escape:'htmlall':'UTF-8'}</option>
                {/if}
                {/foreach}
            </select>
            {if $template_entry['existing_entry'] == true}
            <input type="hidden" name="template_entry[{$template_entry['id_etsy_shipping_templates_entries']|escape:'htmlall':'UTF-8'}][shipping_desination_country_id]" value="{$template_entry['shipping_entry_destination_country_id']|escape:'htmlall':'UTF-8'}" />
            {/if}
            <p class="help-block">{l s='This is an destination country of Shipment' mod='kbetsy'}</p>
        </div>
    </div>

    <div class="col-lg-3 region_list" style="{if $type != "2"} display: none; {/if}">
        <label class="control-label required col-lg-12" style="display: block; text-align: left">{l s='Destination Region' mod='kbetsy'}</label>
        <div class="col-lg-12" style="display: block">
            <select name="template_entry[{$template_entry['id_etsy_shipping_templates_entries']|escape:'htmlall':'UTF-8'}][shipping_destination_region]" onchange="setEntryDestinationRegion()" {if $template_entry['existing_entry'] == true} disabled='disabled' {/if}>
                {foreach $regions_list as $region}
                {if $template_entry['shipping_entry_destination_region_id'] == $region['id_option']} 
                <option value="{$region['id_option']|escape:'htmlall':'UTF-8'}" selected="selected">{$region['name']|escape:'htmlall':'UTF-8'}</option>
                {else}
                <option value="{$region['id_option']|escape:'htmlall':'UTF-8'}">{$region['name']|escape:'htmlall':'UTF-8'}</option>
                {/if}
                {/foreach}
            </select>
            {if $template_entry['existing_entry'] == true}
                <input type="hidden" name="template_entry[{$template_entry['id_etsy_shipping_templates_entries']|escape:'htmlall':'UTF-8'}][shipping_destination_region_id]" value="{$template_entry['shipping_entry_destination_region_id']|escape:'htmlall':'UTF-8'}" />
            {/if}
            <p class="help-block">{l s='Choose a destination region of Shipment' mod='kbetsy'}</p>
        </div>
    </div>

    <div class="col-lg-3">
        <label class="control-label required col-lg-12" style="display: block; text-align: left">{l s='One item cost' mod='kbetsy'}</label>
        <div class="col-lg-12" style="display: block">
            <input type="text" name="template_entry[{$template_entry['id_etsy_shipping_templates_entries']|escape:'htmlall':'UTF-8'}][shipping_primary_cost]" value="{$template_entry['shipping_entry_primary_cost']|escape:'htmlall':'UTF-8'}" required="required"/>
        </div>
    </div>

    <div class="col-lg-3">
        <label class="control-label required col-lg-12" style="display: block; text-align: left">
            {l s='Additional item cost' mod='kbetsy'}
        </label>
        <div class="col-lg-10" style="display: block">
            <input type="text" name="template_entry[{$template_entry['id_etsy_shipping_templates_entries']|escape:'htmlall':'UTF-8'}][shipping_secondary_cost]" value="{$template_entry['shipping_entry_secondary_cost']|escape:'htmlall':'UTF-8'}" required="required"/>
        </div>
        <div class="col-lg-2" style="display: block; margin-top: 5px">
            <a title="{l s='Delete' mod='kbetsy'}" href="javascript://" class="deleteEntry">
                <i class="icon-trash"></i>
            </a>
        </div>
    </div>
</div>


