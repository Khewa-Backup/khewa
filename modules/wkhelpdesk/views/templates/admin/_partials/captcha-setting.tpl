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
    <h3><i class="icon-list"></i> {l s='Google reCAPTCHA' mod='wkhelpdesk'}</h3>
    <form method="post">
        <div class="form-wrapper">
            <div class="form-group row">
                <label class="control-label col-lg-4">
                    <span data-html="true" data-original-title="{l s='If enabled, a captcha will be there in create ticket form.' mod='wkhelpdesk'}" class="label-tooltip" data-toggle="tooltip" title="">
                    {l s='Add reCAPTCHA on create ticket form' mod='wkhelpdesk'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" {if isset($smarty.post.enableCaptcha)} {if $smarty.post.enableCaptcha == 1} checked="checked" {/if} {else if $enableCaptcha == 1} checked="checked" {/if} value="1" id="enableCaptcha_on" name="enableCaptcha" class="captchaOption">
                        <label class="radioCheck" for="enableCaptcha_on">{l s='Yes' mod='wkhelpdesk'}</label>
                        <input type="radio" {if isset($smarty.post.enableCaptcha)} {if $smarty.post.enableCaptcha == 0} checked="checked" {/if} {else if $enableCaptcha == 0} checked="checked" {/if} value="0" id="enableCaptcha_off" name="enableCaptcha" class="captchaOption">
                        <label class="radioCheck" for="enableCaptcha_off">{l s='No' mod='wkhelpdesk'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-4">
                    <span data-html="true" data-original-title="{l s='If enabled, a captcha will be there in reply ticket form.' mod='wkhelpdesk'}" class="label-tooltip" data-toggle="tooltip" title="">
                    {l s='Add reCAPTCHA on reply ticket' mod='wkhelpdesk'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" {if isset($smarty.post.enableCaptchaViewTicket)} {if $smarty.post.enableCaptchaViewTicket == 1} checked="checked" {/if} {else if $enableCaptchaViewTicket == 1} checked="checked" {/if} value="1" id="enableCaptchaViewTicket_on" name="enableCaptchaViewTicket" class="captchaOption">
                        <label class="radioCheck" for="enableCaptchaViewTicket_on">{l s='Yes' mod='wkhelpdesk'}</label>
                        <input type="radio" {if isset($smarty.post.enableCaptchaViewTicket)} {if $smarty.post.enableCaptchaViewTicket == 0} checked="checked" {/if} {else if $enableCaptchaViewTicket == 0} checked="checked" {/if} value="0" id="enableCaptchaViewTicket_off" name="enableCaptchaViewTicket" class="captchaOption">
                        <label class="radioCheck" for="enableCaptchaViewTicket_off">{l s='No' mod='wkhelpdesk'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            <div class="form-group row PositionBlock">
                <label class="control-label col-lg-4 required">{l s='Site key' mod='wkhelpdesk'}</label>
                <div class="col-lg-6">
                    <input type="text" class="form-control" name="captchaSiteKey" {if isset($smarty.post.captchaSiteKey)} value="{$smarty.post.captchaSiteKey}" {else} value="{$captchaSiteKey}" {/if}/>
                </div>
            </div>
            <div class="form-group row PositionBlock">
                <label class="control-label col-lg-4 required">{l s='Secret key' mod='wkhelpdesk'}</label>
                <div class="col-lg-6">
                    <input type="text" class="form-control" name="captchaSecretKey" {if isset($smarty.post.captchaSecretKey)} value="{$smarty.post.captchaSecretKey}" {else} value="{$captchaSecretKey}" {/if}/>
                </div>
            </div>
            <div class="panel-footer">
				<a href="{$moduleLink}" class="btn btn-default"><i class="process-icon-cancel"></i>{l s='Cancel' mod='wkhelpdesk'}</a>
				<button class="btn btn-default pull-right" name="submitCaptcha" id="submitCaptcha" value="1" type="submit"><i class="process-icon-save"></i>{l s='Save' mod='wkhelpdesk'}</button>
			</div>
        </div>
    </form>
</div>
