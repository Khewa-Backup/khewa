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
* @copyright  2017 - Keyrnel SARL
* @license commercial
* International Registered Trademark & Property of Keyrnel SARL
*}

{if $errors_nb > 0}
	{$errors_rendered nofilter}{* HTML, cannot escape *}
{else}
<script type="text/javascript">
var is17 = {if version_compare($smarty.const._PS_VERSION_,'1.7','>=')}true{else}false{/if};
var pitch = {$pitch|intval};
var ajax_allowed = {$ajax_allowed|intval};
var attribute_anchor_separator = "{$attribute_anchor_separator|escape:'html':'UTF-8'}";
var custom_amount_from = {$custom_amount_from|intval};
var custom_amount_to = {$custom_amount_to|intval};
var invalidAmountMsg = "{l s='Please select a valid amount' mod='thegiftcard'}";
var printAtHome = {GiftCardModel::PRINT_AT_HOME|intval};
var sendToFriend = {GiftCardModel::SEND_TO_FRIEND|intval};
</script>

{if isset($product) && $product->id}
<div id="giftcard_product">
	<div id="block_category">
	{if isset($category) && $category->id}
		<div class="row content_scene_cat">
			<!-- Category image -->
			<div class="content_scene_cat_bg" {if $category->id_image}style="background:url({$link->getCatImageLink($category->link_rewrite, $category->id_image)|escape:'quotes':'UTF-8'}) 0 bottom no-repeat; min-height:{$categorySize.height|intval}px;" {/if}>
				<span class="category-name">
					{$category->name|escape:'html':'UTF-8'}
				</span>
				{if $category->description}
				<div class="cat_desc">
					<div class="rte">{$category->description nofilter}{* HTML, cannot escape *}</div>
				</div>
				{/if}
			</div>
		</div>
	{/if}
	</div>
	{if $template_vars && $amount_vars && $active}
	<div id="block_templates" class="attributes" data-id-attribute-group="{$template_vars.id_attribute_group|intval}" data-rewrite-group-name="{$template_vars.rewrite_group_name|escape:'quotes':'UTF-8'}">
		<div class="row">
			<div class="col-lg-12">
				<div class="header">
					<i class="icon-photo"></i><span class="title">{l s='Choose a template' mod='thegiftcard'}</span>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="content">
					<ul class="nav nav-tabs" {if count($template_vars.tags) < 2} style="display:none;"{/if}>
						{foreach from=$template_vars.tags key=tag item=count name=fo}
						<li {if $smarty.foreach.fo.first}class="active"{/if}>
							<a href="#{$tag|escape:'html':'UTF-8'}" data-toggle="gc_tab">{$tag|escape:'html':'UTF-8'} <span class="badge">{$count|intval}</span></a>
						</li>
						{/foreach}
					</ul>
					<div class="tab-content row">
					{foreach from=$template_vars.tags key=tag item=count name=fo}
						<div class="tab-pane {if $smarty.foreach.fo.first}active{/if}" id="{$tag|escape:'html':'UTF-8'}">
						{foreach from=$template_vars.attributes item=template}
						{if in_array($tag, $template.tags) || $smarty.foreach.fo.first}
							{assign var=imageIds value="`$product->id`-`$template.attribute_value`"}
							{if !empty($template.legend)}
								{assign var=imageTitle value=$template.legend|escape:'html':'UTF-8'}
							{else}
								{assign var=imageTitle value=$product->name|escape:'html':'UTF-8'}
							{/if}
							<div class="col-xs-4 col-md-3 img_attribute">
								{if $smarty.foreach.fo.first}
								<input type="radio" class="attribute_radio" name="template" value="{$template.attribute_value|intval}" {if ($template.cover)} checked="checked"{/if} />
								{/if}
								<div {if ($template.cover)}id="bigpic"{/if} class="product-image-container {if ($template.cover)}selected{/if}" data-id="{$template.attribute_value|intval}" {if ($template.auto)}data-auto="{$template.auto|intval}"{/if}>
									{* <img class="replace-2x img-responsive" id="thumb_{$template.attribute_value|intval}" src="{$link->getImageLink($product->link_rewrite, $imageIds)|escape:'quotes':'UTF-8'}" alt="{$imageTitle|escape:'html':'UTF-8'}" title="{$imageTitle|escape:'html':'UTF-8'}" itemprop="image" /> *}
                                    {$template.thumbnail nofilter}{* HTML, cannot escape *}
									<div class="view_larger" style="display:none">
										<a href="{$link->getImageLink($product->link_rewrite, $imageIds)|escape:'quotes':'UTF-8'}" data-fancybox-group="other-views" class="fancybox" title="{$imageTitle|escape:'html':'UTF-8'}">
											<i class="icon-zoom"></i>
										</a>
									</div>
								</div>
							</div>
						{/if}
						{/foreach}
						</div>
					{/foreach}
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="block_amounts" class="attributes" data-id-attribute-group="{$amount_vars.id_attribute_group|intval}" data-rewrite-group-name="{$amount_vars.rewrite_group_name|escape:'quotes':'UTF-8'}">
		<div class="row">
			<div class="col-lg-12">
				<div class="header">
					<i class="icon-giftcard"></i><span class="title">{l s='Choose the amount' mod='thegiftcard'}</span>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="content">
					<div class="form-group">
						<div class="form-content">
							<div class="input-group" style="width:120px;">
								<span class="input-group-addon">{$currencySign|escape:'html':'UTF-8'}</span>
								<select name="amount_select">
								{foreach from=$amount_vars.attributes item=amount name=fo}
									<option value="{$amount.attribute_value|intval}" {if $default_amount==$amount.attribute_value}selected="selected"{/if}>{Tools::displayPrice($amount.attribute_value|intval)}</option>
								{/foreach}
									<option value="-1">{l s='Other amount' mod='thegiftcard'}</option>
								</select>
							</div>
						</div>
					</div>
					<div class="form-group" style="display:none">
						<div class="form-label">
							<label for="amount_input">
								{l s='Custom amount between' mod='thegiftcard'} {Tools::displayPrice($custom_amount_from|intval)} {l s='and' mod='thegiftcard'} {Tools::displayPrice($custom_amount_to|intval)} :
								<span style="display: block; font-size: 11px; font-style: italic; color: #525252; text-align: left;">{l s='Price range' mod='thegiftcard'}: {Tools::displayPrice($pitch|intval)}</span>
							</label>
						</div>
						<div class="form-content">
							<div class="input-group" style="width:120px;">
								<input type="hidden" name="amount" value="{$default_amount|intval}"/>
								<input type="text"  name="amount_input" value=""/>
								<span class="input-group-addon">{$currencySign|escape:'html':'UTF-8'}</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="block_customization">
		<div class="row">
			<div class="col-lg-12">
				<div class="header">
					<i class="icon-email"></i><span class="title">{l s='Choose your sending method' mod='thegiftcard'}</span>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="content">
					<div class="form-group">
						<div class="form-inline mr-30">
							<input type="radio" class="attribute_radio_custom" name="sending_method" value="{GiftCardModel::PRINT_AT_HOME|intval}" checked="checked"/>
							<span>{l s='Print at home' mod='thegiftcard'}</span>
						</div>
						{if $product->text_fields|intval}
						<div class="form-inline">
							<input type="radio" class="attribute_radio_custom beneficiary" name="sending_method" value="{GiftCardModel::SEND_TO_FRIEND|intval}" />
							<span>{l s='Send to a friend' mod='thegiftcard'}</span>
						</div>
						{/if}
					</div>
					{if $product->text_fields|intval}
					<div id="card_text_fields" style="display:none">
					{counter start=0 assign='customizationField'}
					{foreach from=$customizationFields item='field' name='customizationFields'}
						<div class="form-group">
							<div class="form-label">
								<label for="textField{$customizationField|intval}">
								{if !empty($field.name)}
									{$field.name|escape:'html':'UTF-8'}
								{/if}
								</label>
							</div>
							<div class="form-content">
								{if $field.id_customization_field == Configuration::get("GIFTCARD_CUST_DATE_`$product->id|intval`")}
								<div class="input-group" style="width:120px;">
									<input class="datepicker" type="text" name="textField{$field.id_customization_field|intval}" value="" id="textField{$customizationField|intval}" />
									<span class="input-group-addon"><i class="icon-calendar"></i></span>
								</div>
								{else}
								<textarea name="textField{$field.id_customization_field|intval}" class="form-control customization_block_input" id="textField{$customizationField|intval}" rows="3" cols="20"></textarea>
								{/if}
							</div>
						</div>
					{counter}
					{/foreach}
					</div>
				{/if}
				</div>
			</div>
		</div>
	</div>
	<div id="block_button">
		<div class="row">
			<div class="col-lg-12">
				<div class="content">
					<form id="buy_block" data-action="{$link->getModuleLink('thegiftcard', 'page')|escape:'html':'UTF-8'}" action="{$link->getPageLink('cart')|escape:'html':'UTF-8'}" method="post">
						<input type="hidden" name="token" value="{$static_token|escape:'html':'UTF-8'}" />
						<input type="hidden" name="id_product" value="{$product->id|intval}" id="product_page_product_id" />
						<input type="hidden" name="add" value="1" />
						<input type="hidden" name="qty" value="1" />
						<button type="button" name="Submit" class="btn btn-primary">
							{l s='Add to cart' mod='thegiftcard'}</span>
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
	{else}
		{if !$active}{l s='The gift card is not available for this currency yet' mod='thegiftcard'}{else}{l s='No gift card for the moment..' mod='thegiftcard'}{/if}
	{/if}
</div>
{/if}
{/if}
