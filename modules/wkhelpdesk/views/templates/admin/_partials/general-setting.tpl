{**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

<div class="panel">
    <h3><i class="icon-cogs"></i> {l s='General' mod='wkhelpdesk'}</h3>
    <form method="post">
        <div class="form-wrapper">
            {* Customization By Ram Chandra *}
            <div class="alert alert-info">
                <span>{l s='Please click here to import messages from IMAP server' mod='wkhelpdesk'}:&nbsp;</span>
                <button type="submit" name="wksyncimap" class="btn btn-default">{l s='Import messages' mod='wkhelpdesk'}</button>
            </div>
            {* END *}

            <div class="form-group row">
                <label class="control-label col-lg-3 required">
                    <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Help desk page title background color' mod='wkhelpdesk'}">{l s='Page title background color' mod='wkhelpdesk'}</span>
                </label>
                <div class="col-lg-2">
                    <div class="input-group">
                        <input type="color" data-hex="true" class="color mColorPickerInput" name="bgColor" {if isset($smarty.post.bgColor)} value="{$smarty.post.bgColor}" {else} value="{$bgColor}" {/if}/>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-3 required">
                    <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Help desk page title text color' mod='wkhelpdesk'}">{l s='Page title text color' mod='wkhelpdesk'}</span>
                </label>
                <div class="col-lg-2">
                    <div class="input-group">
                        <input type="color" data-hex="true" class="color mColorPickerInput" name="textColor" {if isset($smarty.post.textColor)} value="{$smarty.post.textColor}" {else} value="{$textColor}" {/if}/>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-3 required">
                    <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Select attachment file type(s)' mod='wkhelpdesk'}">{l s='Select attachment file type(s)' mod='wkhelpdesk'}</span>
                </label>
                <div class="col-lg-2">
                    <select id="fileType" name="fileType[]" multiple="multiple">
                        {foreach $fileType as $attachment_file}
                            <option value="{$attachment_file.ext_name}" {if $attachment_file.is_availble == 1} selected="selected" {/if}>{$attachment_file.ext_name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    <span data-html="true" data-original-title="{l s='If disabled, only logged in users will be able to create ticket.' mod='wkhelpdesk'}" class="label-tooltip" data-toggle="tooltip" title="">
                    {l s='Allow visitors to create ticket' mod='wkhelpdesk'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" {if isset($smarty.post.enabledGuestTicket)} {if $smarty.post.enabledGuestTicket == 1} checked="checked" {/if} {else if $enabledGuestTicket == 1} checked="checked" {/if} value="1" id="enabledGuestTicket_on" name="enabledGuestTicket">
                        <label class="radioCheck" for="enabledGuestTicket_on">{l s='Yes' mod='wkhelpdesk'}</label>
                        <input type="radio" {if isset($smarty.post.enabledGuestTicket)} {if $smarty.post.enabledGuestTicket == 0} checked="checked" {/if} {else if $enabledGuestTicket == 0} checked="checked" {/if} value="0" id="enabledGuestTicket_off" name="enabledGuestTicket">
                        <label class="radioCheck" for="enabledGuestTicket_off">{l s='No' mod='wkhelpdesk'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            {* Abdul code *}
            <div class="form-group clearfix">
                <div class="panel-body">
                    <div class="alert alert-info">
                        <p>{l s='If you want automatically import messages from IMAP Server ' mod='wkhelpdesk'}</p>
                        <br />
                        <p>{l s='Please set the CRON, insert the following line in your cron tasks manager:' mod='wkhelpdesk'}</p>
                        <br />
                        <ul class="list-unstyled">
                            <li><code>{$cron_url}</code></li>
                        </ul>
                    </div>
                </div>
            </div>
            {* end *}
            <div class="panel-footer">
				<a href="{$moduleLink}" class="btn btn-default"><i class="process-icon-cancel"></i>{l s='Cancel' mod='wkhelpdesk'}</a>
				<button class="btn btn-default pull-right" name="submitGeneral" id="submitGeneral" value="1" type="submit"><i class="process-icon-save"></i>{l s='Save' mod='wkhelpdesk'}</button>
			</div>
        </div>
    </form>
</div>
<script>
$('.mColorPickerInput').mColorPicker({
    imageFolder: '../img/admin/'
});
</script>