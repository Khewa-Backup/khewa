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
		<i class="icon-picture-o"></i> {l s='Category settings' mod='thegiftcard'}
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3 required">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='Invalid characters:' mod='thegiftcard'} &lt;&gt;;=#{}">
				{l s='Category name' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			{foreach from=$languages item=language}
			{if $languages|count > 1}
			<div class="row">
				<div class="translatable-field lang-{$language.id_lang|intval}" {if $language.id_lang != $default_language}style="display:none"{/if}>
					<div class="col-lg-9">
			{/if}
						<input id="category_name_{$language.id_lang|intval}" type="text"  name="category_name_{$language.id_lang|intval}" value="{$category->name[$language.id_lang|intval]}">
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

	<div class="form-group">
		<label class="control-label col-lg-3" for="description">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='Invalid characters:' mod='thegiftcard'} &lt;&gt;;=#{}">
				{l s='Category description' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			{foreach from=$languages item=language}
			{if $languages|count > 1}
			<div class="translatable-field row lang-{$language.id_lang|intval}">
				<div class="col-lg-9">
			{/if}
					<textarea id="category_description_{$language.id_lang|intval}" name="category_description_{$language.id_lang|intval}" class="autoload_rte">{$category->description[$language.id_lang|intval]|htmlentitiesUTF8}</textarea>
				<span class="counter" data-max="{if isset($max)}{$max|intval}{else}none{/if}"></span>
			{if $languages|count > 1}
				</div>
				<div class="col-lg-2">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="gc_dropdown">
						{$language.iso_code|escape:'html':'UTF-8'}
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						{foreach from=$languages item=language}
						<li><a href="javascript:hideOtherLanguage({$language.id_lang|intval});">{$language.name|escape:'html':'UTF-8'}</a></li>
						{/foreach}
					</ul>
				</div>
			</div>
			{/if}
			{/foreach}

			<script type="text/javascript">
				$(".textarea-autosize").autosize();
			</script>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="gc_tooltip"
				title="{l s='Upload a banner image from your computer.' mod='thegiftcard'}">
				{l s='Category image' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			{$cat_uploader nofilter}{* HTML, cannot escape *}
		</div>
	</div>

	{if $topmenu}
	<div class="form-group">
		<label class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='Enable to display gift card page link in top menu.' mod='thegiftcard'}">
				{l s='Display in top menu' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" name="display_topmenu" id="display_topmenu_on" value="1" {if Configuration::get('GIFTCARD_DISPLAY_TOPMENU')|intval}checked="checked"{/if} />
				<label class="t" for="display_topmenu_on">{l s='Yes' mod='thegiftcard'}</label>
				<input type="radio" name="display_topmenu" id="display_topmenu_off" value="0" {if !Configuration::get('GIFTCARD_DISPLAY_TOPMENU')|intval}checked="checked"{/if} />
				<label class="t" for="display_topmenu_off">{l s='No' mod='thegiftcard'}</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>
	{/if}

	<div class="form-group">
		<label class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='Enable to display gift card category. Usefull for indexing gift card in search page' mod='thegiftcard'}">
				{l s='Display category' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" name="display_cat" id="display_cat_on" value="1" {if $category->active|intval}checked="checked"{/if} />
				<label class="t" for="display_cat_on">{l s='Yes' mod='thegiftcard'}</label>
				<input type="radio" name="display_cat" id="display_cat_off" value="0" {if !$category->active|intval}checked="checked"{/if} />
				<label class="t" for="display_cat_off">{l s='No' mod='thegiftcard'}</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>

	<div class="panel-footer">
		<button type="submit" name="submitGiftCard" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='thegiftcard'}</button>
	</div>
</div>

<div class="panel">
	<div class="panel-heading">
		<i class="icon-picture-o"></i> {l s='Product settings' mod='thegiftcard'}
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3 required">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='Invalid characters:' mod='thegiftcard'} &lt;&gt;;=#{}">
				{l s='Product name' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			{foreach from=$languages item=language}
			{if $languages|count > 1}
			<div class="row">
				<div class="translatable-field lang-{$language.id_lang|intval}" {if $language.id_lang != $default_language}style="display:none"{/if}>
					<div class="col-lg-9">
			{/if}
						<input id="product_name_{$language.id_lang|intval}" type="text"  name="product_name_{$language.id_lang|intval}" value="{$products_data[$default_currency].product->name[$language.id_lang|intval]}">
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

	<div class="form-group">
		<label class="control-label col-lg-3" for="description">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='Invalid characters:' mod='thegiftcard'} &lt;&gt;;=#{}">
				{l s='Short description' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			{foreach from=$languages item=language}
			{if $languages|count > 1}
			<div class="translatable-field row lang-{$language.id_lang|intval}">
				<div class="col-lg-9">
			{/if}
				<textarea id="product_description_short_{$language.id_lang|intval}" name="product_description_short_{$language.id_lang|intval}" class="autoload_rte">
					{$products_data[$default_currency].product->description_short[$language.id_lang|intval]|htmlentitiesUTF8}
				</textarea>
				<span class="counter" data-max="{if isset($max)}{$max|intval}{else}none{/if}"></span>
			{if $languages|count > 1}
				</div>
				<div class="col-lg-2">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="gc_dropdown">
						{$language.iso_code|escape:'html':'UTF-8'}
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						{foreach from=$languages item=language}
						<li><a href="javascript:hideOtherLanguage({$language.id_lang|intval});">{$language.name|escape:'html':'UTF-8'}</a></li>
						{/foreach}
					</ul>
				</div>
			</div>
			{/if}
			{/foreach}

			<script type="text/javascript">
				$(".textarea-autosize").autosize();
			</script>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-lg-3" for="description">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='Invalid characters:' mod='thegiftcard'} &lt;&gt;;=#{}">
				{l s='Description' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			{foreach from=$languages item=language}
			{if $languages|count > 1}
			<div class="translatable-field row lang-{$language.id_lang|intval}">
				<div class="col-lg-9">
			{/if}
					<textarea id="product_description_{$language.id_lang|intval}" name="product_description_{$language.id_lang|intval}" class="autoload_rte">{$products_data[$default_currency].product->description[$language.id_lang|intval]|htmlentitiesUTF8}</textarea>
				<span class="counter" data-max="{if isset($max)}{$max|intval}{else}none{/if}"></span>
			{if $languages|count > 1}
				</div>
				<div class="col-lg-2">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="gc_dropdown">
						{$language.iso_code|escape:'html':'UTF-8'}
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						{foreach from=$languages item=language}
						<li><a href="javascript:hideOtherLanguage({$language.id_lang|intval});">{$language.name|escape:'html':'UTF-8'}</a></li>
						{/foreach}
					</ul>
				</div>
			</div>
			{/if}
			{/foreach}

			<script type="text/javascript">
				$(".textarea-autosize").autosize();
			</script>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-lg-3" for="association">
			<span class="label-tooltip" data-toggle="gc_tooltip">
				{l s='Category association' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			{$categories_association nofilter}{* HTML, cannot escape *}
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-lg-3" for="visibility">
			<span class="label-tooltip" data-toggle="gc_tooltip">
				{l s='Gift card visibility' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-3">
			<select name="visibility" id="visibility">
				<option value="both" {if $products_data[$default_currency].product->visibility == 'both'}selected="selected"{/if} >{l s='Everywhere' mod='thegiftcard'}</option>
				<option value="catalog" {if $products_data[$default_currency].product->visibility == 'catalog'}selected="selected"{/if} >{l s='Catalog only' mod='thegiftcard'}</option>
				<option value="search" {if $products_data[$default_currency].product->visibility == 'search'}selected="selected"{/if} >{l s='Search only' mod='thegiftcard'}</option>
				<option value="none" {if $products_data[$default_currency].product->visibility == 'none'}selected="selected"{/if}>{l s='Nowhere' mod='thegiftcard'}</option>
			</select>
		</div>
	</div>



	<div class="panel-footer">
		<button type="submit" name="submitGiftCard" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='thegiftcard'}</button>
	</div>
</div>
