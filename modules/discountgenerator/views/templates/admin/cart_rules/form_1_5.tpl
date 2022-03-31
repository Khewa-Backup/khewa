{**
 * DiscountGenerator Prestashop Module
 *
 * @author iRessources <support-prestashop@iressources.com>
 * @link http://www.iressources.com/
 * @copyright Copyright &copy; 2015-2019 iRessources
 * @version 1.4.1
 *}
{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}
<div class="leadin">{block name="leadin"}{/block}</div>
<div>
	<div class="productTabs">
		<ul class="tab">
			<li class="tab-row">
				<a class="tab-page" id="cart_rule_link_informations" href="javascript:displayCartRuleTab('informations');">{l s='Information' mod='discountgenerator'}</a>
			</li>
			<li class="tab-row">
				<a class="tab-page" id="cart_rule_link_conditions" href="javascript:displayCartRuleTab('conditions');">{l s='Conditions' mod='discountgenerator'}</a>
			</li>
			<li class="tab-row">
				<a class="tab-page" id="cart_rule_link_actions" href="javascript:displayCartRuleTab('actions');">{l s='Actions' mod='discountgenerator'}</a>
			</li>
		</ul>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$("#show_group_discount").change(function(){
			if($(this).is(":checked")) {
				$("#group_discount_properties").fadeIn().find("input").removeAttr("disabled");
				$("#single_code_field").fadeOut().find("input").attr("disabled","disabled");
			} else {
				$("#group_discount_properties").fadeOut().find("input").attr("disabled","disabled");
				$("#single_code_field").fadeIn().find("input").removeAttr("disabled");
			}
		});
	});
</script>
<form action="{$currentIndex|escape}&token={$currentToken|escape}&addcart_rule" id="cart_rule_form" method="post">
	{if $currentObject->id}<input type="hidden" name="id_cart_rule" value="{$currentObject->id|intval}" />{/if}
	<input type="hidden" id="currentFormTab" name="currentFormTab" value="informations" />
	<div id="cart_rule_informations" class="cart_rule_tab">
		<h4>{l s='Cart-rule information' mod='discountgenerator'}</h4>
		<div class="separation"></div>
		<table cellpadding="0" cellspacing="0">
			<tr>
				<td>
					<label>{l s='Name' mod='discountgenerator'}</label>
					<div class="margin-form">
						<div class="translatable">
							{foreach from=$languages item=language}
								<div class="lang_{$language.id_lang|intval}" style="display:{if $language.id_lang == $id_lang_default}block{else}none{/if};float:left">
									<input type="text" id="name_{$language.id_lang|intval}" name="name_{$language.id_lang|intval}" value="{$currentTab->getFieldValue($currentObject, 'name', $language.id_lang|intval)|escape:'htmlall':'UTF-8'}" style="width:400px" />
									<sup>*</sup>
								</div>
							{/foreach}
						</div>
						<p class="preference_description">{l s='This will be displayed in the cart summary, as well as on the invoice.' mod='discountgenerator'}</p>
					</div>
					<label>{l s='Description' mod='discountgenerator'}</label>
					<div class="margin-form">
						<textarea name="description" style="width:80%;height:100px">{$currentTab->getFieldValue($currentObject, 'description')|escape}</textarea>
						<p class="preference_description">{l s='For your eyes only. This will never be displayed to the customer.' mod='discountgenerator'}</p>
					</div>

					<!-- Custom block starts -->
					<label>{l s='Generate many unique vouchers' mod='discountgenerator'}</label>
					<div class="margin-form">
						<input type="checkbox" name="show_group_discount" id="show_group_discount" value="1"
							   {if isset($show_group_discount) && $show_group_discount == 1}checked="checked"{/if} />
					</div>
					<div id="group_discount_properties"
						 style="{if !isset($show_group_discount) || $show_group_discount == 0}display:none{/if}">
						<label for="coupon_quantity">{l s='Total quantity of generated coupons' mod='discountgenerator'}</label>
						<div class="margin-form">
							<input type="text" size="15" name="coupon_quantity" id="coupon_quantity"
								   value="{if isset($coupon_quantity)}{$coupon_quantity|intval}{/if}"/> <sup>*</sup>
							<p class="clear">{l s='Total number of unique vouchers' mod='discountgenerator'}</p>
						</div>
						<label>{l s='Code configurations' mod='discountgenerator'}</label>
						<div class="margin-form">
							<input type="text" placeholder="Prefix" size="15" name="code_prefix"
								   value="{if isset($code_prefix)}{$code_prefix|escape:'htmlall':'UTF-8'}{/if}"
								   onclick=""/>
							<input type="text" placeholder="Code mask" size="15" name="code_mask"
								   value="{if isset($code_mask)}{$code_mask|escape:'htmlall':'UTF-8'}{/if}" onclick=""
								   style="width:100px;"/> <sup>*</sup>
							<p class="clear">{l s='Code generation rules (mask: x - digit, y - letter, for example "xxxyy" )' mod='discountgenerator'}</p>
						</div>
					</div>
					<!-- Custom block ends -->

					<div id="single_code_field" style="{if isset($show_group_discount) && $show_group_discount == 1}display:none{/if}">
						<label>{l s='Code' mod='discountgenerator'}</label>
						<div class="margin-form">
							<input type="text" id="code" name="code" value="{$currentTab->getFieldValue($currentObject, 'code')|escape}" />
							<a href="javascript:gencode(8);" class="button">{l s='(Click to generate random code)' mod='discountgenerator'}</a>
							<p class="preference_description">{l s='Caution! The rule will automatically be applied if you leave this field blank.' mod='discountgenerator'}</p>
						</div>
					</div>
					<label>{l s='Highlight' mod='discountgenerator'}</label>
					<div class="margin-form">
						&nbsp;&nbsp;
						<input type="radio" name="highlight" id="highlight_on" value="1" {if $currentTab->getFieldValue($currentObject, 'highlight')|intval}checked="checked"{/if} />
						<label class="t" for="highlight_on"> <img src="../img/admin/enabled.gif" alt="{l s='Yes' mod='discountgenerator'}" title="{l s='Yes' mod='discountgenerator'}" style="cursor:pointer" /></label>
						&nbsp;&nbsp;
						<input type="radio" name="highlight" id="highlight_off" value="0"  {if !$currentTab->getFieldValue($currentObject, 'highlight')|intval}checked="checked"{/if} />
						<label class="t" for="highlight_off"> <img src="../img/admin/disabled.gif" alt="{l s='No' mod='discountgenerator'}" title="{l s='No' mod='discountgenerator'}" style="cursor:pointer" /></label>
						<p class="preference_description">
							{l s='If the voucher is not yet in the cart, it will be displayed in the cart summary.' mod='discountgenerator'}
						</p>
					</div>
					<label>{l s='Partial use' mod='discountgenerator'}</label>
					<div class="margin-form">
						&nbsp;&nbsp;
						<input type="radio" name="partial_use" id="partial_use_on" value="1" {if $currentTab->getFieldValue($currentObject, 'partial_use')|intval}checked="checked"{/if} />
						<label class="t" for="partial_use_on"> <img src="../img/admin/enabled.gif" alt="{l s='Allowed' mod='discountgenerator'}" title="{l s='Allowed' mod='discountgenerator'}" style="cursor:pointer" /></label>
						&nbsp;&nbsp;
						<input type="radio" name="partial_use" id="partial_use_off" value="0"  {if !$currentTab->getFieldValue($currentObject, 'partial_use')|intval}checked="checked"{/if} />
						<label class="t" for="partial_use_off"> <img src="../img/admin/disabled.gif" alt="{l s='Not allowed' mod='discountgenerator'}" title="{l s='Not allowed' mod='discountgenerator'}" style="cursor:pointer" /></label>
						<p class="preference_description">
							{l s='Only applicable if the voucher value is greater than the cart total.' mod='discountgenerator'}<br />
							{l s='If you do not allow partial use, the voucher value will be lowered to the total order amount. If you allow partial use, however, a new voucher will be created with the remainder.' mod='discountgenerator'}
						</p>
					</div>
					<label>{l s='Priority' mod='discountgenerator'}</label>
					<div class="margin-form">
						<input type="text" name="priority" value="{$currentTab->getFieldValue($currentObject, 'priority')|intval}" />
						<p class="preference_description">{l s='Cart rules are applied by priority. A cart rule with a priority of "1" will be processed before a cart rule with a priority of "2".' mod='discountgenerator'}</p>
					</div>
					<label>{l s='Status' mod='discountgenerator'}</label>
					<div class="margin-form">
						&nbsp;&nbsp;
						<input type="radio" name="active" id="active_on" value="1" {if $currentTab->getFieldValue($currentObject, 'active')|intval}checked="checked"{/if} />
						<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="{l s='Enabled' mod='discountgenerator'}" title="{l s='Enabled' mod='discountgenerator'}" style="cursor:pointer" /></label>
						&nbsp;&nbsp;
						<input type="radio" name="active" id="active_off" value="0"  {if !$currentTab->getFieldValue($currentObject, 'active')|intval}checked="checked"{/if} />
						<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="{l s='Disabled' mod='discountgenerator'}" title="{l s='Disabled' mod='discountgenerator'}" style="cursor:pointer" /></label>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<div id="cart_rule_conditions" class="cart_rule_tab">
		<h4>{l s='Cart-rule conditions' mod='discountgenerator'}</h4>
		<div class="separation"></div>
		{include file='controllers/cart_rules/conditions.tpl'}
	</div>
	<div id="cart_rule_actions" class="cart_rule_tab">
		<h4>{l s='Cart-rule actions' mod='discountgenerator'}</h4>
		<div class="separation"></div>
		{include file='controllers/cart_rules/actions.tpl'}
	</div>
	<div class="separation"></div>
	<div style="text-align:center">
		<input type="submit" value="{l s='Save' mod='discountgenerator'}" class="button" name="submitAddcart_rule" id="{$table|escape}_form_submit_btn" />
		<!--<input type="submit" value="{l s='Save and stay' mod='discountgenerator'}" class="button" name="submitAddcart_ruleAndStay" id="" />-->
	</div>
</form>
<script type="text/javascript">
	var product_rule_groups_counter = {if isset($product_rule_groups_counter)}{$product_rule_groups_counter|intval}{else}0{/if};
	var product_rule_counters = new Array();
	var currentToken = '{$currentToken|escape:'quotes'}';
	var currentFormTab = '{if isset($smarty.post.currentFormTab)}{$smarty.post.currentFormTab|escape:'quotes'}{else}informations{/if}';

	var languages = new Array();
	{foreach from=$languages item=language key=k}
	languages[{$k}] = {
		id_lang: {$language.id_lang},
		iso_code: '{$language.iso_code|escape:'quotes'}',
		name: '{$language.name|escape:'quotes'}'
	};
	{/foreach}
	displayFlags(languages, {$id_lang_default});
</script>
<script type="text/javascript" src="themes/default/template/controllers/cart_rules/form.js"></script>