{*
* FMM Helpdesk Module
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright FMM Modules
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   FmmHelpdesk
*}


{literal}
<style type="text/css">
form#fmm_hd_tickets_form{ background-color:#ebedf4; border:1px solid #ccced7; min-height:404px; padding: 5px 10px 10px; margin-left:140px;}
form#fmm_hd_tickets_form h3 { font-size:14px; font-weight:normal;}
form#fmm_hd_tickets_form h4 { font-size:18px; font-weight:normal;}
h4{padding-top: 0;margin-top: 0;}
</style>
{/literal}

<div class="leadin">{block name="leadin"}{/block}</div>
{if isset($ticket_id)}
<div class="col-lg-2" id="fmmhelpdesk">
 	<div class="productTabs">
		<ul class="tab">
			<li class="tab-row">
				<a class="tab-page" id="advance_blog_link_informations" href="javascript:displayCartRuleTab('informations');">
				{l s='Ticket Detail' mod='helpdesk'}</a>
			</li>
			<li class="tab-row">
				<a class="tab-page" id="advance_blog_link_notes" href="javascript:displayCartRuleTab('notes');">
				{l s='Internal Notes' mod='helpdesk'}</a>
			</li>
			<li class="tab-row">
				<a class="tab-page" id="advance_blog_link_orders" href="javascript:displayCartRuleTab('orders');">
				{l s='Customer Orders' mod='helpdesk'}</a>
			</li>
			<li class="tab-row">
				<a class="tab-page" id="advance_blog_link_ordmsg" href="javascript:displayCartRuleTab('ordmsg');">
				{l s='Customer Orders message' mod='helpdesk'}</a>
			</li>
		</ul>
	</div>
</div>
<form action="{$currentIndex|escape:'htmlall':'UTF-8'}&token={$currentToken|escape:'htmlall':'UTF-8'}&submitAddfmm_hd_tickets" name="fmm_hd_tickets_form" id="" method="post" enctype="multipart/form-data">
	<div id="tabPane1" class="tab-pane col-lg-10 panel">
		{if $currentObject->id}<input type="hidden" name="ticket_id" value="{$currentObject->id|intval}" />{/if}
		<input type="hidden" id="currentFormTab" name="currentFormTab" value="informations" />
		<div id="advance_blog_informations" class="cart_rule_tab">
			{include file=$informations}
			<div style="text-align:center">
				<input type="submit" value="{l s='Save' mod='helpdesk'}" class="button btn btn-default" name="submitAddfmm_hd_tickets" id="{$table|escape:'htmlall':'UTF-8'}_form_submit_btn" />
			</div>
		</div>
		<div id="advance_blog_notes" class="cart_rule_tab">
			{include file=$notes}
			<div style="text-align:center">
				<input type="submit" value="{l s='Save' mod='helpdesk'}" class="button btn btn-default" name="submitAddfmm_hd_tickets" id="{$table|escape:'htmlall':'UTF-8'}_form_submit_btn" />
			</div>
		</div>
		<div id="advance_blog_orders" class="cart_rule_tab">
			{include file=$cust_orders_temp}
		</div>
		<div id="advance_blog_ordmsg" class="cart_rule_tab">
			{include file=$cust_orders_msgs_temp}
		</div>
		<div class="panel-footer">
			<a href="{$toolbar_btn['cancel']['href']|escape:'htmlall':'UTF-8'}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='helpdesk'}</a>
		</div>
	</div>
</form>
{else}
	{include file=$addticket}
{/if}
<script language="javascript">
	var currentToken = "{$currentToken|escape:'htmlall':'UTF-8'}";
	var currentFormTab = "{if isset($smarty.post.currentFormTab)}{$smarty.post.currentFormTab|escape:'htmlall':'UTF-8'}{else}informations{/if}";
	
	var languages = new Array();
	{foreach from=$languages item=language key=k}
		languages[{$k|escape:'htmlall':'UTF-8'}] = {
			id_lang: {$language.id_lang|escape:'htmlall':'UTF-8'},
			iso_code: "{$language.iso_code|escape:'htmlall':'UTF-8'}",
			name: "{$language.name|escape:'htmlall':'UTF-8'}"
		};
	{/foreach}
	displayFlags(languages, {$id_lang_default|escape:'htmlall':'UTF-8'});

	function displayCartRuleTab(tab)
	{
		$('.cart_rule_tab').hide();
		$('.tab-page').removeClass('selected');
		$('#advance_blog_' + tab).show();
		$('#advance_blog_link_' + tab).addClass('selected');
		$('#currentFormTab').val(tab);
	}
	
	$('.cart_rule_tab').hide();
	$('.tab-page').removeClass('selected');
	$('#advance_blog_' + currentFormTab).show();
	$('#advance_blog_link_' + currentFormTab).addClass('selected');
	
</script>

{literal}
<style type="text/css">
/*== PS 1.6 ==*/
.bootstrap #fmmhelpdesk ul.tab { list-style:none; padding:0; margin:0}

.bootstrap #fmmhelpdesk ul.tab li a {background-color: white;border: 1px solid #DDDDDD;display: block;margin-bottom: -1px;padding: 10px 15px;}
.bootstrap #fmmhelpdesk ul.tab li a { display:block; color:#555555; text-decoration:none}
.bootstrap #fmmhelpdesk ul.tab li a.selected { color:#fff; background:#00AFF0}

</style>
{/literal}

