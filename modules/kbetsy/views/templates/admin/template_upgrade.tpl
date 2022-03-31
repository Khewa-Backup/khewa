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
        <input type="hidden" class='existing_entry' name="template_upgrade[{$template_upgrade['id_etsy_shipping_upgrades']|escape:'htmlall':'UTF-8'}][existing_entry]" value="{$template_upgrade['existing_entry']|escape:'htmlall':'UTF-8'}" />
        <input type="hidden" class='upgrade_id' name="template_upgrade[{$template_upgrade['id_etsy_shipping_upgrades']|escape:'htmlall':'UTF-8'}][id_etsy_shipping_upgrades]" value="{$template_upgrade['id_etsy_shipping_upgrades']|escape:'htmlall':'UTF-8'}" />
        <label class="control-label required col-lg-12" style="display: block; text-align: left">
            {l s='Name' mod='kbetsy'}
        </label>
        <div class="col-lg-12" style="display: block">
            <input type="text" name="template_upgrade[{$template_upgrade['id_etsy_shipping_upgrades']|escape:'htmlall':'UTF-8'}][shipping_upgrade_title]" value="{$template_upgrade['shipping_upgrade_title']|escape:'htmlall':'UTF-8'}" class="" maxlength="50" required="required">
            <p class="help-block">{l s='This is what shoppers will see at checkout. Make it clear and descriptive.' mod='kbetsy'}</p>
        </div>
    </div>
    <div class="col-lg-3">
        <label class="control-label required col-lg-12" style="display: block; text-align: left">
            {l s='Destination Type' mod='kbetsy'}
        </label>
        <div class="col-lg-12" style="display: block">
            <select name="template_upgrade[{$template_upgrade['id_etsy_shipping_upgrades']|escape:'htmlall':'UTF-8'}][shipping_upgrade_destination]" {if $template_upgrade['existing_entry'] == true} disabled='disabled' {/if}>
                <option value="1" {if $template_upgrade['shipping_upgrade_destination'] == "1"} selected="" {/if}>{l s='International' mod='kbetsy'}</option>
                <option value="0" {if $template_upgrade['shipping_upgrade_destination'] == "0"} selected="" {/if}>{l s='Domestic' mod='kbetsy'}</option>
            </select>
            <p class="help-block">
                {l s='Choose a destination type as domestic or international' mod='kbetsy'}
            </p>
        </div>
    </div>
    <div class="col-lg-3">
        <label class="control-label required col-lg-12" style="display: block; text-align: left">
            {l s='One Item Cost' mod='kbetsy'}
        </label>
        <div class="col-lg-12" style="display: block">
            <input type="text" name="template_upgrade[{$template_upgrade['id_etsy_shipping_upgrades']|escape:'htmlall':'UTF-8'}][shipping_upgrade_primary_cost]" value="{$template_upgrade['shipping_upgrade_primary_cost']|escape:'htmlall':'UTF-8'}" class="" maxlength="50" required="required">
            <p class="help-block">
                {l s='This is for a delivery upgrade, which is a price added in addition to your standard postage price.' mod='kbetsy'}
            </p>
        </div>
    </div>
    <div class="col-lg-3">
        <label class="control-label required col-lg-12" style="display: block; text-align: left">
            {l s='Additional Item Cost' mod='kbetsy'}
        </label>
        <div class="col-lg-10" style="display: block">
            <input type="text" name="template_upgrade[{$template_upgrade['id_etsy_shipping_upgrades']|escape:'htmlall':'UTF-8'}][shipping_upgrade_secondary_cost]" value="{$template_upgrade['shipping_upgrade_secondary_cost']|escape:'htmlall':'UTF-8'}" class="" maxlength="50" required="required">
        </div>
        <div class="col-lg-2" style="display: block; margin-top: 5px">
            <a title="{l s='Delete' mod='kbetsy'}" href="javascript://" class="deleteUpgrade">
                <i class="icon-trash"></i>
            </a>
        </div>
    </div>
</div>