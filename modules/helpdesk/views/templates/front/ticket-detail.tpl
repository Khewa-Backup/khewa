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
{capture name=path}
	<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}">
		{l s='My account' mod='helpdesk'}</a>
		<span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>
	<a href="{$link->getModuleLink('helpdesk', 'helpdesk')|escape:'htmlall':'UTF-8'}" title="{l s='My Tickets' mod='helpdesk'}">{l s='My Tickets' mod='helpdesk'}</a>
		<span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>
		{$ticketData[0].ticket_subject|escape:'htmlall':'UTF-8'}
{/capture}
<h1 class="page-subheading">{$ticketData[0].ticket_subject|escape:'htmlall':'UTF-8'}</h1>

<div class="block-center" id="block-history">
	<table>
		<tr>
			<td align="left" style="float:left"><strong>{l s='Department' mod='helpdesk'}:</strong> {$ticketData[0].department_title|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td align="left" style="float:left"><strong>{l s='Status' mod='helpdesk'}:</strong> {$ticketData[0].ticketstatus_title|escape:'htmlall':'UTF-8'}</td>
		</tr>
		{if $ticketData[0].last_response_staff|date_format}<tr><td align="left"><strong>{l s='Last Staff Response' mod='helpdesk'}:</strong> {$ticketData[0].last_response_staff|date_format|escape:'htmlall':'UTF-8'}</td></tr>{/if}
	</table>
	
	{if $ticketResponsesData}
		<div class="block-center" id="block-history">
			<div class="separation"></div>
			<div class="panel" style="margin-top:18px !important;border-top: 1px solid #d6d4d4;">
			<h3>{l s='Ticket Responses' mod='helpdesk'}</h3>	
			<div class="separation"></div>
				
				{foreach from=$ticketResponsesData item=ticketResponse}
				
				<div class="row" {if $ticketResponse.r_client_id eq 0}style="margin-left:60px !important;"{else}style="margin-left:10px !important;"{/if}>
					<div class="message-item">
							<div style="margin-top: -8px;background-color: #eee;border-radius: 48px;display: inline-block;height: 49px;overflow: hidden;text-align: center;width: 49px;position: absolute;">
							{if $ticketResponse.r_client_id eq 0}
							 	<img src="{$path|escape:'htmlall':'UTF-8'}modules/helpdesk/views/img/icon-user-default.png" width="50" height="50" />
							 	{else}
							 	<img src="{$path|escape:'htmlall':'UTF-8'}modules/helpdesk/views/img/admin_ico.jpg" width="50" height="50" />
							 	{/if}
							</div>
						
						<div class="message-body" style="margin-left: 59px; margin-top: 17px;">
							<span class="message-date">&nbsp;
								<i class="icon-calendar"></i> - 
							 	{$ticketResponse.r_created_time|date_format:'%A, %b %d'|escape:'htmlall':'UTF-8'} - 
							 	<i class="icon-time"></i> 
							 	{$ticketResponse.r_created_time|date_format:'%H:%M:%S'|escape:'htmlall':'UTF-8'}
							</span>
							
						
							{if $ticketResponse.r_attachment}
								<span class="message-product">&nbsp;
									<i class="icon-link"></i>
									<a href="{$path|escape:'htmlall':'UTF-8'}img/{$ticketResponse.r_attachment|escape:'htmlall':'UTF-8'}" class="_blank">{l s='Attachment' mod='helpdesk'}</a>
								</span>
							{/if}
							<p style="margin-left:8px !important">{$ticketResponse.r_message|escape:'htmlall':'UTF-8'}</p>
						</div>
					</div>
				</div>
				{/foreach}
		</div>
	{else}
		<p class="warning alert alert-warning" style="clear:both;">{l s='No Message have been posted yet.' mod='helpdesk'}</p>
	{/if}
</div>

<div style="clear:both;  margin-top:20px;"></div>

{include file="$tpl_dir./errors.tpl"}

{if isset($confirmation) && $confirmation}
	<p class="success alert alert-success conf">
		{l s='Your message is submitted successfully.' mod='helpdesk'}
	</p>
{/if}

<p class="required"> <sup>*</sup> {l s='Required field' mod='helpdesk'}</p>

<form action="{$link->getModuleLink('helpdesk', 'helpdesk')|escape:'htmlall':'UTF-8'}{if $seo_url == 0}&{else}?{/if}ticket_id={$ticketData[0].ticket_id|escape:'htmlall':'UTF-8'}&detail=1" method="post" class="std box" id="add_address" name="helpdesk_form" enctype="multipart/form-data">
		<h3 class="page-subheading">{l s='Post a New Message' mod='helpdesk'}</h3>
		<div class="required form-group">
			<label for="content">{l s='Message' mod='helpdesk'} <sup>*</sup> </label>
			<textarea name="r_message" id="r_message" title="{l s='Message' mod='helpdesk'}" class="form-control"></textarea>
		</div>

		<div class="form-group">
				<label for="content">{l s='Attachment' mod='helpdesk'}</label>
				<input name="user_attachment" id="user_attachment" value="" class="form-control" type="file" />
			</div>

		{if $HELPDESK_DEFAULT_CLOSE_STATUS neq $ticketData[0].t_status_id}
			{if $HELPDESK_ALLOW_CUSTOMERS_CLOSE}
			<div class="form-group">
				<label for="content">{l s='Close on reply' mod='helpdesk'}&nbsp;</label>
				<input type="checkbox" name="close_on_reply" id="close_on_reply" value="1" class="form-group" />
			</div>
			{/if}
		{/if}
		<div class="submit2">
			<input type="hidden" name="action" id="action" value="sendmessage" />
			<input type="submit" class="button" value="{l s='Submit' mod='helpdesk'}" id="submitAddress" name="submitAddress">
		</div>
</form>

<ul class="footer_links">
	<li class="fleft">
		<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}">{l s='Back to your account.' mod='helpdesk'}</a></li>
</ul>