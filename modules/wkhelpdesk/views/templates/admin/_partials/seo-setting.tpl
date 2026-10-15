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
    <h3><i class="icon-home"></i> {l s='SEO & URLs' mod='wkhelpdesk'}</h3>
    <form method="post">
        <div class="form-wrapper">
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    <span data-html="true" data-original-title="{l s='If you select YES then create ticket and view ticket url will be fully SEO compatible and editable.' mod='wkhelpdesk'}" class="label-tooltip" data-toggle="tooltip" title="">
                    {l s='Ticket SEO URL' mod='wkhelpdesk'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" {if isset($smarty.post.helpdeskUrlRewrite)} {if $smarty.post.helpdeskUrlRewrite == 1} checked="checked" {/if} {else if $helpdeskUrlRewrite == 1} checked="checked" {/if} value="1" id="helpdeskUrlRewrite_on" name="helpdeskUrlRewrite">
                        <label class="radioCheck" for="helpdeskUrlRewrite_on">{l s='Yes' mod='wkhelpdesk'}</label>
                        <input type="radio" {if isset($smarty.post.helpdeskUrlRewrite)} {if $smarty.post.helpdeskUrlRewrite == 0} checked="checked" {/if} {else if $helpdeskUrlRewrite == 0} checked="checked" {/if} value="0" id="helpdeskUrlRewrite_off" name="helpdeskUrlRewrite">
                        <label class="radioCheck" for="helpdeskUrlRewrite_off">{l s='No' mod='wkhelpdesk'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            <div class="form-group row url_rewriting_div">
                <label class="control-label col-lg-3 required">
                    <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='New ticket page URL' mod='wkhelpdesk'}">{l s='New ticket' mod='wkhelpdesk'}</span>
                </label>
                <div class="col-lg-2">
                    <input type="text" class="form-control" name="newTicketUrl" {if isset($smarty.post.newTicketUrl)} value="{$smarty.post.newTicketUrl}" {else} value="{$newTicketUrl}" {/if}/>
                </div>
            </div>
            <div class="form-group row url_rewriting_div">
                <label class="control-label col-lg-3 required">
                    <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='View(Reply) ticket page URL' mod='wkhelpdesk'}">{l s='View(Reply) ticket' mod='wkhelpdesk'}</span>
                </label>
                <div class="col-lg-2">
                    <input type="text" class="form-control" name="viewTicketUrl" {if isset($smarty.post.viewTicketUrl)} value="{$smarty.post.viewTicketUrl}" {else} value="{$viewTicketUrl}" {/if}/>
                </div>
            </div>
            <div class="panel-footer">
				<a href="{$moduleLink}" class="btn btn-default"><i class="process-icon-cancel"></i>{l s='Cancel' mod='wkhelpdesk'}</a>
				<button class="btn btn-default pull-right" name="submitSeo" id="submitSeo" value="1" type="submit"><i class="process-icon-save"></i>{l s='Save' mod='wkhelpdesk'}</button>
			</div>
        </div>
    </form>
</div>
