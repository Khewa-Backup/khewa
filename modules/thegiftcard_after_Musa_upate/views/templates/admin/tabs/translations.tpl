{*
* 2017 Keyrnel
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one shop.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
* If you have not received this module from us, thank you for contacting us.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author Keyrnel
* @copyright  2022 - Keyrnel SARL
* @license commercial
* International Registered Trademark & Property of Keyrnel SARL
*}

<div class="panel">
	<div class="panel-heading">
		<i class="icon-globe"></i> {l s='Translations' mod='thegiftcard'}
	</div>
	{if (isset($products_data[$default_currency].translations) && count($products_data[$default_currency].translations))}
	{foreach from=$products_data[$default_currency].translations item=translation}
	<div class="form-group">
		<label class="control-label col-lg-3 {if isset($translation.required) && $translation.required}required{/if}">
			<span>{$translation.label|escape:'html':'UTF-8'}</span>
		</label>
		<div class="col-lg-9">
			{foreach from=$languages item=language}
			{if $languages|count > 1}
			<div class="row">
				<div class="translatable-field lang-{$language.id_lang|intval}" {if $language.id_lang != $default_language}style="display:none"{/if}>
					<div class="col-lg-9">
			{/if}
						<input type="text" name="translations[{$translation.name|escape:'html':'UTF-8'}][{$language.id_lang|intval}]" value="{$translation.value[{$language.id_lang|intval}]|escape:'html':'UTF-8'}">
			{if $languages|count > 1}
					</div>
					<div class="col-lg-2">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="gc_dropdown">
							{$language.iso_code|escape:'html':'UTF-8'}
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							{foreach from=$languages item=language}
							<li><a href="javascript:hideOtherLanguage({$language.id_lang|intval});" tabindex="-1">{$language.name|escape:'html':'UTF-8'}</a></li>
							{/foreach}
						</ul>
					</div>
				</div>
			</div>
			{/if}
			{/foreach}
		</div>
	</div>
	{/foreach}
	{/if}
	<div class="panel-footer">
		<button type="submit" name="submitGiftCard" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='thegiftcard'}</button>
	</div>
</div>
