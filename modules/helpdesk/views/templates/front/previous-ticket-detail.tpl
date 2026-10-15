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
<link rel="stylesheet" type="text/css" media="screen" href="{if $force_ssl == 1}{$base_dir_ssl|escape:'htmlall':'UTF-8'}{else}{$base_dir|escape:'htmlall':'UTF-8'}{/if}modules/helpdesk/css/fmmhelpdesk.css" />
{capture name=path}
	<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}">
		{l s='My account' mod='helpdesk'}</a>
		<span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>
	<a href="{$link->getModuleLink('helpdesk', 'helpdesk')|escape:'htmlall':'UTF-8'}" title="{l s='My Tickets' mod='helpdesk'}">{l s='My Tickets' mod='helpdesk'}</a>
		<span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>
		{$ticketData[0].ticket_subject|escape:'htmlall':'UTF-8'}
{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<div id="helpdesk_block_account" style="margin-top:20px;">
	<h2>{$ticketData[0].ticket_subject|escape:'htmlall':'UTF-8'}</h2>
	
	<table cellpadding="0" cellspacing="0" border="0" align="left">
		<tr>
			<td align="left" style="float:left"><strong>{l s='Department' mod='helpdesk'}:</strong> {$ticketData[0].department_title|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td align="left" style="float:left"><strong>{l s='Status' mod='helpdesk'}:</strong> {$ticketData[0].ticketstatus_title|escape:'htmlall':'UTF-8'}</td>
		</tr>
		{if $ticketData[0].last_response_staff|date_format}<tr><td align="left"><strong>{l s='Last Staff Response' mod='helpdesk'}:</strong> {$ticketData[0].last_response_staff|date_format|escape:'htmlall':'UTF-8'}</td></tr>{/if}
	</table>
	
	{if $ticketResponsesData}
		<div class="helpdesk clearfix" style="clear:both;padding-top:30px;">
			<table cellpadding="0" cellspacing="0" border="1" width="100%">
				<tr>
					<th>Sender</th>
					<th>Message</th>
					<th>Date</th>
				</tr>
				{foreach from=$ticketResponsesData item=ticketResponse}
				<tr>
					<td>{if $ticketResponse.r_client_id eq 0}Admin{else}{$customername|escape:'htmlall':'UTF-8'}{/if}</td>
					<td style="text-align: left;">{$ticketResponse.r_message|escape:'htmlall':'UTF-8'}</td>
					<td>{$ticketResponse.r_created_time|date_format|escape:'htmlall':'UTF-8'}</td>
				</tr>
				{/foreach}
			</table>
		</div>
	{else}
		<p class="warning" style="clear:both;">{l s='No Message have been posted yet.' mod='helpdesk'}</p>
	{/if}

</div>

<div style="clear:both;  margin-top:20px;"></div>

{include file="$tpl_dir./errors.tpl"}

{if isset($confirmation) && $confirmation}
	<p class="success">
		{l s='Your message is submitted successfully.' mod='helpdesk'}
	</p>
{/if}

<p class="required"> <sup>*</sup> {l s='Required field' mod='helpdesk'}</p>

<form action="{$link->getModuleLink('helpdesk', 'helpdesk')|escape:'htmlall':'UTF-8'}?ticket_id={$ticketData[0].ticket_id|escape:'htmlall':'UTF-8'}&detail=1" method="post" id="add_address" name="helpdesk_form" enctype="multipart/form-data">
	<fieldset>
		<h3>{l s='Post a New Message' mod='helpdesk'}</h3>
		<p class="required textarea">
			<label for="content">{l s='Message' mod='helpdesk'} <sup>*</sup> </label>
			<textarea name="r_message" id="r_message" title="{l s='Message' mod='helpdesk'}" class="input-text required-entry" rows="8" cols="26"></textarea>
		</p>
		{if $HELPDESK_DEFAULT_CLOSE_STATUS neq $ticketData[0].t_status_id}
			{if $HELPDESK_ALLOW_CUSTOMERS_CLOSE}
			<p class="close_reply">
				<input type="checkbox" name="close_on_reply" id="close_on_reply" value="1" /> {l s='Close on reply' mod='helpdesk'}
			</p>
			{/if}
		{/if}
	</fieldset>
	<p class="submit2">
		<input type="hidden" name="action" id="action" value="sendmessage" />
		<input type="submit" class="button" value="{l s='Submit' mod='helpdesk'}" id="submitAddress" name="submitAddress">
	</p>
</form>

<ul class="footer_links">
	<li class="fleft">
		<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}"><img src="{$img_dir|escape:'htmlall':'UTF-8'}icon/my-account.gif" alt="" class="icon" /></a>
		<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}">{l s='Back to your account.' mod='helpdesk'}</a></li>
</ul>