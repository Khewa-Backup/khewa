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
    {*
    * Added Transmit, Shipping carrier, Min and max delivery time Fields
    * @date 10-04-2023
    *@author Tanisha Gupta
    *}
    <div class="col-lg-3">
        <label class="control-label required col-lg-12" style="display: block; text-align: left">{l s='Transmit Time data' mod='kbetsy'}</label>
        <div class="col-lg-12" style="display: block">
            <select name="template_entry[{$template_entry['id_etsy_shipping_templates_entries']|escape:'htmlall':'UTF-8'}][shipping_entry_transmit_type]" class ="entry_transmit_type">
                {if $template_entry['shipping_entry_transmit_type'] == 'time_delivery' || $template_entry['shipping_entry_transmit_type'] == ''} 
                    <option value="time_delivery" selected="selected">{l s='Delivery Time' mod='kbetsy'}</option>
                {else}
                    <option value="time_delivery" >{l s='Delivery Time' mod='kbetsy'}</option>
                {/if}
                {if $template_entry['shipping_entry_transmit_type'] == 'shipping_carrier'} 
                    <option value="shipping_carrier" selected="selected">{l s='Shipping Carrier' mod='kbetsy'}</option>
                {else}
                    <option value="shipping_carrier" >{l s='Shipping Carrier' mod='kbetsy'}</option>
                {/if}
            </select>   
        </div>
    </div>
    <div class="col-lg-3 carrier_list" style="display:none">
        <label class="control-label col-lg-12" style="display: block; text-align: left">{l s='Delivery service' mod='kbetsy'}</label>
        <div class="col-lg-12" style="display: block">
            <select name="template_entry[{$template_entry['id_etsy_shipping_templates_entries']|escape:'htmlall':'UTF-8'}][shipping_carrier_id]">
            {if isset($template_entry['shipping_carrier_list'])} 
                {foreach $template_entry['shipping_carrier_list'] as $carrierList}
                <optgroup label="{$carrierList['etsy_shipping_carrier_name']}"> {*Variable contains HTML, can't escape*}
                   {foreach $carrierList['carrier_list'] as $value }
                       {assign var = "shippingcarrier" value ="`$carrierList['etsy_shipping_carrier_id']`.`$value['mail_class_key']`"}
                      {if $shippingcarrier == $template_entry['shipping_carrier_id'] }
                        <option value="{$shippingcarrier }" selected="selected">{$value['name']}</option> {*Variable contains HTML, can't escape*}
                      {else}
                      <option value="{$shippingcarrier}">{$value['name']}</option> {*Variable contains HTML, can't escape*}
                   {/if}

                {/foreach}
                {/foreach }
            {/if}  
            </select>
        </div>
    </div>          
    <div class="col-lg-3 min_delivery_days">
        <label class="control-label col-lg-12 min_delivery_days" style="display: block; text-align: left">{l s='Min. Delivery Time' mod='kbetsy'}</label>
        <div class="col-lg-12" style="display: block">
            <select name="template_entry[{$template_entry['id_etsy_shipping_templates_entries']|escape:'htmlall':'UTF-8'}][shipping_entry_min_delivery_days]"/>
                {for $i = 1 to 45}
                    <option value="{$i}" {if $i == $template_entry['shipping_entry_min_delivery_days']} selected="selected" {/if}>{$i}</option> {*Variable contains HTML, can't escape*}
                {/for}
            </select>
        </div>
    </div>   
    <div class="col-lg-3 max_delivery_days">
        <label class="control-label col-lg-12" style="display: block; text-align: left">{l s='Max. Delivery Time' mod='kbetsy'}</label>
        <div class="col-lg-12" style="display: block">
            <select name="template_entry[{$template_entry['id_etsy_shipping_templates_entries']|escape:'htmlall':'UTF-8'}][shipping_entry_max_delivery_days]"/>
                {for $i = 1 to 45}
                    <option value="{$i}" {if $i == $template_entry['shipping_entry_max_delivery_days']} selected="selected" {/if}>{$i}</option> {*Variable contains HTML, can't escape*}
                {/for}
            </select>
        </div>
    </div>     
</div>


