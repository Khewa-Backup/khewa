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
<script type="text/javascript">
function loadPremadeText(premade_id)
{
	var dataString = "premade_id=" + premade_id + "&action=get_premade_reply";
	var appendText = document.getElementById('append_text');
	var reply_msg = document.getElementById('reply_msg');
	if(premade_id!=0){
    $.ajax({  
        type: "POST",  
        url: "index.php/?controller=AdminTickets&ticket_id={/literal}{$ticket_details[0].ticket_id|escape:'htmlall':'UTF-8'}{literal}&updatefmm_hd_tickets&token={/literal}{$currentToken|escape:'htmlall':'UTF-8'}{literal}",  
        data: dataString, 
        success: function(response)
        {
			showResponseText(reply_msg, response, appendText)
        }
    });
	}
}
function showResponseText(myField, myValue, appendText) {
	if(appendText.checked == true){
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos)+ myValue+ myField.value.substring(endPos, myField.value.length);
	}else {
		myField.focus();
		myField.value = myValue;
	}
}
</script>
{/literal}
<div id="top">
		<h4>{l s='Ticket # ' mod='helpdesk'}{$ticket_details[0].ticket_id|escape:'htmlall':'UTF-8'}</h4>
		<div class="separation"></div>
		<table cellspacing="5" cellpadding="0" border="0" width="100%">
            <tbody>
            	<tr>
	                <td width="25%"><strong>{l s='Ticket Status' mod='helpdesk'}:</strong></td>
	                <td width="25%">{$ticket_details[0].ticketstatus_title|escape:'htmlall':'UTF-8'}</td>
	                <td width="25%"><strong>{l s='Posted By' mod='helpdesk'}:</strong></td>
	                <td width="25%">{$ticket_details[0].customer|escape:'htmlall':'UTF-8'}</td>
                </tr>
            <tr>
                <td><strong>{l s='Priority' mod='helpdesk'}:</strong></td>
                <td>{$ticket_details[0].priorities_title|escape:'htmlall':'UTF-8'}</td>
                <td><strong>{l s='Email' mod='helpdesk'}:</strong></td>
                <td>{$ticket_details[0].email|escape:'htmlall':'UTF-8'}</td>
            </tr>
            <tr>
                <td><strong>{l s='Department' mod='helpdesk'}:</strong></td>
                <td>{$ticket_details[0].department_title|escape:'htmlall':'UTF-8'}</td>
                <td><strong>{l s='Ticket Creation Date' mod='helpdesk'}:</strong></td>
                <td>{$ticket_details[0].t_created_time|escape:'htmlall':'UTF-8'}</td>
            </tr>
            <tr>
                <td><strong>{l s='Last Staff Response' mod='helpdesk'}:</strong></td>
                <td>{$ticket_details[0].last_response_staff|escape:'htmlall':'UTF-8'}</td>
                                <td><strong>{l s='Last Client Response' mod='helpdesk'}:</strong></td>
                <td>{$ticket_details[0].last_response_client|escape:'htmlall':'UTF-8'}</td>
                            </tr>
			{if $ticket_details[0].ticket_attachment}
            <tr>
            	<td><strong>{l s='Attachment' mod='helpdesk'}:</strong></td>
                <td align="left" colspan="3"><a href="../img/{$ticket_details[0].ticket_attachment|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Download' mod='helpdesk'}</a></td>
            </tr>     
			{/if}       
            <tr>
            	<td><strong>{l s='Ticket Subject' mod='helpdesk'}:</strong></td>
                <td align="left" colspan="3">{$ticket_details[0].ticket_subject|escape:'htmlall':'UTF-8'}</td>
            </tr>
                    </tbody></table>
					
			<div class="separation"></div>
						
			<h4>{l s='Ticket Options' mod='helpdesk'}</h4>
		
			<div class="separation"></div>
		
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<label>{l s='Change Ticket Status' mod='helpdesk'}</label>
						<div class="margin-form">
							<select name="t_status_id" id="t_status_id" style="width: 200px;">
								{foreach from=$ticketStatuses item=ticketStatus}
								<option value="{$ticketStatus.ticketstatus_id|escape:'htmlall':'UTF-8'}" {if $ticket_details[0].t_status_id eq $ticketStatus.ticketstatus_id}selected="selected"{/if}>{$ticketStatus.ticketstatus_title|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
							<p class="preference_description"></p>
						</div>
			
						<label>{l s='Change Ticket Department' mod='helpdesk'}</label>
						<div class="margin-form">
							<select name="t_department_id" id="t_department_id" style="width: 200px;">
								{foreach from=$ticketDepartments item=ticketDepartment}
								<option value="{$ticketDepartment.departments_id|escape:'htmlall':'UTF-8'}" {if $ticket_details[0].t_department_id eq $ticketDepartment.departments_id}selected="selected"{/if}>{$ticketDepartment.department_title|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
							<p class="preference_description"></p>
						</div>
						
						<label>{l s='Change Ticket Priority' mod='helpdesk'}</label>
						<div class="margin-form">
							<select name="t_priority_id" id="t_priority_id" style="width: 200px;">
								{foreach from=$ticketPriorities item=ticketPriority}
								<option value="{$ticketPriority.priorities_id|escape:'htmlall':'UTF-8'}" {if $ticket_details[0].t_priority_id eq $ticketPriority.priorities_id}selected="selected"{/if}>{$ticketPriority.priorities_title|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
						</div>
						<input type="hidden" name="created_time" id="created_time" value="{$smarty.now|date_format:"%Y-%m-%d"|escape:'htmlall':'UTF-8'}" />
					</td>
				</tr>
			</table>
			<div class="separation"></div>
			<div class="panel" style="margin-top:18px !important">
			<h3>{l s='Ticket Responses' mod='helpdesk'}</h3>	
			<div class="separation"></div>
			{if $ticketResponsesData}
			
				{foreach from=$ticketResponsesData item=ticketResponse}
				<div class="row" {if !$ticketResponse.r_client_id eq 0}style="margin-left:-50px !important"{/if}>
					<div class="message-item">
						<div class="message-avatar">
							<div class="avatar-md">

							 	{if $ticketResponse.r_client_id eq 0}
							 	<img src="{$path|escape:'htmlall':'UTF-8'}modules/helpdesk/views/img/icon-user-default.png" />
							 	{else}
							 	<img src="{$path|escape:'htmlall':'UTF-8'}modules/helpdesk/views/img/admin_ico.jpg" />
							 	{/if}
							</div>
						</div>
						<div class="message-body">
							{if $ticketResponse.r_client_id eq 0}Admin{else}{$ticket_details[0].customer|escape:'htmlall':'UTF-8'}{/if}
							<span class="message-date">&nbsp;
								<i class="icon-calendar"></i> - 
							 	{$ticketResponse.r_created_time|date_format:'%A, %b %d'|escape:'htmlall':'UTF-8'} - 
							 	<i class="icon-time"></i> 
							 	{$ticketResponse.r_created_time|date_format:'%H:%M:%S'|escape:'htmlall':'UTF-8'}
							</span>
							
							{if $ticketResponse.r_attachment}
							<span class="message-product">&nbsp;
								<i class="icon-link"></i> <a href="{$path|escape:'htmlall':'UTF-8'}img/{$ticketResponse.r_attachment|escape:'htmlall':'UTF-8'}" class="_blank">{l s='Attachment' mod='helpdesk'}</a></span>
							{/if}
							<p class="message-item-text">{$ticketResponse.r_message|nl2br}{*HTML content*} </p>
						</div>
					</div>
				</div>
				{/foreach}
			</div>
			
			{else}
			<p class="warning">{l s='No Message have been posted yet.' mod='helpdesk'}</p>
		{/if}
		<div class="panel">
		<h3>{l s='Your answer to User' mod='helpdesk'}</h3>
		<div class="separation"></div>
		<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<div class="margin-form">
							<textarea style="width:500px; height:200px;" id="reply_msg" name="reply_msg"></textarea>
							<p class="preference_description"></p>
						</div>

						<div class="form-group">
							<label for="content">{l s='Attachment' mod='helpdesk'}</label>
							<input style="border:1px solid #cccccc" name="admin_attachment" id="admin_attachment" value="" type="file" />
						</div>
						<div class="margin-form">
							<select onchange="loadPremadeText(this.value);" id="premade_id" name="premade_id">
								<option value="0">{l s='Select a premade' mod='helpdesk'}</option>
								{foreach from=$ticketPremades item=ticketPremade}
								<option value="{$ticketPremade.premade_id|escape:'htmlall':'UTF-8'}">{$ticketPremade.premade_title|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
							<input type="checkbox" value="1" id="append_text" name="append_text"> {l s='Append' mod='helpdesk'}
						</div>
						<div class="margin-form">
							{l s='Append Department Signature' mod='helpdesk'} <input type="radio" checked="checked" value="1" id="append_signature" name="append_signature"> {l s='Yes' mod='helpdesk'} <input type="radio" value="0" id="append_signature" name="append_signature"> {l s='No' mod='helpdesk'}
						</div>
						<div class="margin-form">
							<input type="checkbox" value="1" id="close_on_reply" name="close_on_reply" /> {l s='Close on reply' mod='helpdesk'}
						</div>
					</td>
				</tr>
		</table>
		</div>
		</div>