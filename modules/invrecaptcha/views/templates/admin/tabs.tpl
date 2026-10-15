{**
 * Spam Protection - Google reCAPTCHA, hCaptcha & GDPR Captcha
 *
 * @author    WebshopWorks
 * @copyright 2018-2026 WebshopWorks.com
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 *}
<div class="panel irc-tabs-container">
	<div class="irc-tabs-header">
		<div class="irc-tabs-left">
			<img src="{$smarty.const.__PS_BASE_URI__|escape:'html':'UTF-8'}modules/invrecaptcha/logo.png" alt="logo" class="img img-responsive irc-logo">
			<div class="btn-group">
				<a href="{$irc_panel_url|escape:'html':'UTF-8'}settings" class="btn btn-default {if $irc_panel == 'settings'}active{/if}">
					<i class="icon-cog"></i>
					<span class="hidden-inline-xs">{l s='Settings' d='Admin.Global'}</span>
				</a>
				<a href="{$irc_panel_url|escape:'html':'UTF-8'}stats" class="btn btn-default {if $irc_panel == 'stats'}active{/if}">
					<i class="icon-bar-chart"></i>
					<span class="hidden-inline-xs">{l s='Stats' d='Admin.Navigation.Menu'}</span>
				</a>
				<a href="{$irc_panel_url|escape:'html':'UTF-8'}advanced" class="btn btn-default {if $irc_panel == 'advanced'}active{/if}">
					<i class="icon-wrench"></i>
					<span class="hidden-inline-xs">{l s='Advanced' d='Admin.Global'}</span>
				</a>
			</div>
		</div>
		<div class="irc-tabs-right">
			<a href="https://docs.webshopworks.com/spam-protection" target="_blank" class="btn btn-link">
				<i class="icon-book"></i> {l s='Documentation' mod='invrecaptcha'}
			</a>
		</div>
	</div>
</div>