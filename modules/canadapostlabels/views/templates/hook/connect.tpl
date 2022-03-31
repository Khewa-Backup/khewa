{*
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 *
 *}
<div class="panel">
	<div class="panel-heading">
		<i class="{$icon|escape:'html':'UTF-8'}"></i>
		{$title|escape:'html':'UTF-8'}
	</div>

	<div class="alert alert-info">
		{l s='Connect your Canada Post account by clicking the button below.' mod='canadapostlabels'}
		<br><br>
		{l s='If this is your first time creating a Canada Post account and/or adding a new credit card to your Canada Post profile, please allow up to 24 hours for your information to process before using this module. You may encounter errors during that time.' mod='canadapostlabels'}
	</div>
	<form action="https://www.canadapost.ca/cpotools/apps/drc/merchant" method="post" class="defaultForm form-horizontal">
		<input type="hidden" name="return-url" value="{$return_url|escape:'html':'UTF-8'}" />
		<input type="hidden" name="token-id" value="{$token_id|escape:'html':'UTF-8'}" />
		<input type="hidden" name="platform-id" value="{$platform_id|escape:'html':'UTF-8'}" />
		<div class="form-wrapper">
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='Connect Account' mod='canadapostlabels'}</label>
				<div class="col-lg-9">
					{if !$token_error}
						<button class="btn btn-default" name="submitConnect" type="submit">
							<img src="{$logo|escape:'html':'UTF-8'}" alt=""> {l s='Sign in with Canada Post' mod='canadapostlabels'}
						</button>
					{else}
						<label class="control-label" style="color:#f00">{$token_error|escape:'html':'UTF-8'}</label>
					{/if}
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='Account Status' mod='canadapostlabels'}</label>
				<div class="col-lg-9">
					<label class="control-label" style="color:#2b2b2b">{l s='Not Connected' mod='canadapostlabels'} <i class="icon-close"></i></label>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='Customer Number' mod='canadapostlabels'}</label>
				<div class="col-lg-9">
					<label class="control-label"></label>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='API Username' mod='canadapostlabels'}</label>
				<div class="col-lg-9">
					<label class="control-label"></label>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3">{l s='API Password' mod='canadapostlabels'}</label>
				<div class="col-lg-9">
					<label class="control-label"></label>
				</div>
			</div>
		</div>
	</form>
</div>
