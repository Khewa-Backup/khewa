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
<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
<link rel="stylesheet" type="text/css" media="screen" href="{if $force_ssl == 1}{$base_dir_ssl|escape:'htmlall':'UTF-8'}{else}{$base_dir|escape:'htmlall':'UTF-8'}{/if}modules/helpdesk/css/fmmhelpdesk.css" />
{capture name=path}
	<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}">
		{l s='My account' mod='helpdesk'}</a>
		<span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>{l s='My Tickets' mod='helpdesk'}
{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<div id="helpdesk_block_account" style="margin-top:20px;">
	<h2>{l s='My Tickets' mod='helpdesk'}</h2>
	{if $userPostedTickets}
		<div class="helpdesk clearfix">
			<table cellpadding="0" cellspacing="0" border="1" width="100%">
				<tr>
					<th>{l s='Ticket No.' mod='helpdesk'}</th>
					<th>{l s='Subject' mod='helpdesk'}</th>
					<th>{l s='Posted' mod='helpdesk'}</th>
					<th>{l s='Last Staff Response' mod='helpdesk'}</th>
					<th>{l s='Department' mod='helpdesk'}</th>
					<th>{l s='Priority' mod='helpdesk'}</th>
					<th>{l s='Status' mod='helpdesk'}</th>
					<th>{l s='Threads' mod='helpdesk'}</th>
				</tr>
				{foreach from=$userPostedTickets item=userPostedTicket}
				<tr onclick="window.location.href='{$link->getModuleLink('helpdesk', 'helpdesk')|escape:'htmlall':'UTF-8'}?ticket_id={$userPostedTicket.ticket_id|escape:'htmlall':'UTF-8'}&detail=1'" style="cursor: pointer;">
					<td>{$userPostedTicket.ticket_id|escape:'htmlall':'UTF-8'}</td>
					<td>{$userPostedTicket.ticket_subject|escape:'htmlall':'UTF-8'}</td>
					<td>{$userPostedTicket.t_created_time|date_format|escape:'htmlall':'UTF-8'}</td>
					<td>{$userPostedTicket.last_response_staff|date_format:'%H:%M:%S'|escape:'htmlall':'UTF-8'}</td>
					<td>{$userPostedTicket.department_title|escape:'htmlall':'UTF-8'}</td>
					<td>{$userPostedTicket.priorities_title|escape:'htmlall':'UTF-8'}</td>
					<td>{$userPostedTicket.ticketstatus_title|escape:'htmlall':'UTF-8'}</td>
					<td>{$userPostedTicket.total_replies|escape:'htmlall':'UTF-8'}</td>
				</tr>
				{/foreach}
			</table>
		</div>
	{else}
		<p class="warning">{l s='No tickets have been posted yet.' mod='helpdesk'}</p>
	{/if}

</div>

<div style="clear:both;"></div>

{include file="$tpl_dir./errors.tpl"}

{if isset($confirmation) && $confirmation}
	<p class="success">
		{l s='Your ticket is posted successfully.' mod='helpdesk'}
	</p>
{/if}

<p class="required"> <sup>*</sup> Required field</p>

<form action="{$link->getModuleLink('helpdesk', 'helpdesk')|escape:'htmlall':'UTF-8'}" method="post" id="add_address" name="helpdesk_form" enctype="multipart/form-data">
	<fieldset>
		<h3>{l s='Post a New Ticket' mod='helpdesk'}</h3>
		<p class="required text">
			<label for="title">{l s='Title' mod='helpdesk'} <sup>*</sup> </label>
			<input type="text" name="ticket_subject" id="ticket_subject" title="{l s='Title' mod='helpdesk'}" class="input-text required-entry" />
		</p>
		{if $HELPDESK_SHOW_DEPARTMENTS}
		{if $ticketDepartments}
		<p class="required select">
			<label for="department">{l s='Department' mod='helpdesk'} <sup>*</sup> </label>
			<select name="t_department_id" id="t_department_id" title="{l s='Department ' mod='helpdesk'}" class="input-text">
				<option value="">{l s='Select Department' mod='helpdesk'}</option>
				{foreach from=$ticketDepartments item=ticketDepartment}
				<option value="{$ticketDepartment.departments_id|escape:'htmlall':'UTF-8'}">{$ticketDepartment.department_title|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			</select>
      	</p>
		{/if}
		{/if}
      	{if $HELPDESK_PRIORITIES}	
		{if $ticketPriorities}
		<p class="required select">
			<label for="priority">{l s='Priority' mod='helpdesk'} <sup>*</sup> </label>
			<select name="t_priority_id" id="t_priority_id" title="{l s='Priority ' mod='helpdesk'}" class="input-text">
				<option value="">{l s='Select Priority' mod='helpdesk'}</option>
				{foreach from=$ticketPriorities item=ticketPriority}
				<option value="{$ticketPriority.priorities_id|escape:'htmlall':'UTF-8'}">{$ticketPriority.priorities_title|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			</select>
      	</p>
		{/if}
		{/if}
		<p class="required textarea">
			<label for="content">{l s='Message' mod='helpdesk'} <sup>*</sup> </label>
			<textarea name="content" id="content" title="{l s='Message' mod='helpdesk'}" class="input-text required-entry" rows="3" cols="26"></textarea>
		</p>
		{if $HELPDESK_FILE_UPLOADS}
      	<p class="text attachment">
			<label for="content">{l s='Attachment' mod='helpdesk'}</label>
			<input name="helpdesk_attachment" id="helpdesk_attachment" value="" class="input-text" type="file" />
      	</p>
		{if $HELPDESK_ACCEPTED_FILE_TYPES neq ""}<p class="inline-infos required">{l s='Supported Types: ' mod='helpdesk'}{$HELPDESK_ACCEPTED_FILE_TYPES|escape:'htmlall':'UTF-8'}</p>{/if}
		{/if}
		
		{if $HELPDESK_ENABLE_GOOGLE_CAPTCHA eq 1}
		<p class="wide">
            <label for="security_code" class="required"><b>{l s='Security Code Message:' mod='helpdesk'}</b></label>
            <div style="padding-left:12%" class="g-recaptcha" id="Gcaptcha"></div>
        	</p> 
        		{literal}
        		<script>
        			var onloadCallback = function() {
        			grecaptcha.render('Gcaptcha', {
          			'sitekey' : "{/literal}{$sitekey|escape:'htmlall':'UTF-8'}{literal}",
          			'callback' : VerifyCallback
        			});
        			};
        		</script>
        		{/literal}
        	<p class="submit2">
				<input type="hidden" name="action" id="action" value="ticketsubmit" />
				<input type="submit" class="button DataTrigger_test" value="{l s='Save' mod='helpdesk'}" id="submitAddress" style="display:none"  name="submitAddress">
			</p>
			<p>&nbsp;</p>
		{else}
		<p class="submit2">
				<input type="hidden" name="action" id="action" value="ticketsubmit" />
				<input type="submit" class="button" value="{l s='Save' mod='helpdesk'}" id="submitAddress" name="submitAddress">
		</p>	
		{/if}
	</fieldset>
</form>

<ul class="footer_links">
	<li class="fleft">
		<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}"><img src="{$img_dir|escape:'htmlall':'UTF-8'}icon/my-account.gif" alt="" class="icon" /></a>
		<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}">{l s='Back to your account.' mod='helpdesk'}</a></li>
</ul>
{literal}
<script>
function VerifyCallback(response) { $('.DataTrigger_test').show('submit');}
</script>
{/literal}