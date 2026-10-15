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
    <h3><i class="icon-envelope"></i> {l s='E-mail' mod='wkhelpdesk'}</h3>
    <form method="post">
        <div class="form-wrapper">
            <div class="form-group row">
                <label class="control-label col-lg-6">{l s='Confirmation mail to customer when new ticket is created' mod='wkhelpdesk'}</label>
                <div class="col-lg-6">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" {if isset($smarty.post.customerNotification)} {if $smarty.post.customerNotification == 1} checked="checked" {/if} {else if $customerNotification == 1} checked="checked" {/if} value="1" id="customerNotification_on" name="customerNotification">
                        <label class="radioCheck" for="customerNotification_on">{l s='Yes' mod='wkhelpdesk'}</label>
                        <input type="radio" {if isset($smarty.post.customerNotification)} {if $smarty.post.customerNotification == 0} checked="checked" {/if} {else if $customerNotification == 0} checked="checked" {/if} value="0" id="customerNotification_off" name="customerNotification">
                        <label class="radioCheck" for="customerNotification_off">{l s='No' mod='wkhelpdesk'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-6">{l s='Notification mail to agents when new ticket is created' mod='wkhelpdesk'}</label>
                <div class="col-lg-6">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" {if isset($smarty.post.agentNotification)} {if $smarty.post.agentNotification == 1} checked="checked" {/if} {else if $agentNotification == 1} checked="checked" {/if} value="1" id="agentNotification_on" name="agentNotification">
                        <label class="radioCheck" for="agentNotification_on">{l s='Yes' mod='wkhelpdesk'}</label>
                        <input type="radio" {if isset($smarty.post.agentNotification)} {if $smarty.post.agentNotification == 0} checked="checked" {/if} {else if $agentNotification == 0} checked="checked" {/if} value="0" id="agentNotification_off" name="agentNotification">
                        <label class="radioCheck" for="agentNotification_off">{l s='No' mod='wkhelpdesk'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-6">{l s='Mail to customer when ticket status updated to closed and resolved' mod='wkhelpdesk'}</label>
                <div class="col-lg-6">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" {if isset($smarty.post.statusUpdateMail)} {if $smarty.post.statusUpdateMail == 1} checked="checked" {/if} {else if $statusUpdateMail == 1} checked="checked" {/if} value="1" id="statusUpdateMail_on" name="statusUpdateMail">
                        <label class="radioCheck" for="statusUpdateMail_on">{l s='Yes' mod='wkhelpdesk'}</label>
                        <input type="radio" {if isset($smarty.post.statusUpdateMail)} {if $smarty.post.statusUpdateMail == 0} checked="checked" {/if} {else if $statusUpdateMail == 0} checked="checked" {/if} value="0" id="statusUpdateMail_off" name="statusUpdateMail">
                        <label class="radioCheck" for="statusUpdateMail_off">{l s='No' mod='wkhelpdesk'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-6">{l s='Mail to support agents when customer will reply' mod='wkhelpdesk'}</label>
                <div class="col-lg-6">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" {if isset($smarty.post.customerReplyMail)} {if $smarty.post.customerReplyMail == 1} checked="checked" {/if} {else if $customerReplyMail == 1} checked="checked" {/if} value="1" id="customerReplyMail_on" name="customerReplyMail">
                        <label class="radioCheck" for="customerReplyMail_on">{l s='Yes' mod='wkhelpdesk'}</label>
                        <input type="radio" {if isset($smarty.post.customerReplyMail)} {if $smarty.post.customerReplyMail == 0} checked="checked" {/if} {else if $customerReplyMail == 0} checked="checked" {/if} value="0" id="customerReplyMail_off" name="customerReplyMail">
                        <label class="radioCheck" for="customerReplyMail_off">{l s='No' mod='wkhelpdesk'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            <div class="panel-footer">
				<a href="{$moduleLink}" class="btn btn-default"><i class="process-icon-cancel"></i>{l s='Cancel' mod='wkhelpdesk'}</a>
				<button class="btn btn-default pull-right" name="submitMail" id="submitMail" value="1" type="submit"><i class="process-icon-save"></i>{l s='Save' mod='wkhelpdesk'}</button>
			</div>
        </div>
    </form>
</div>
