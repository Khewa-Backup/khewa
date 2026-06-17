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
<div class="panel">
<div class="box">
<form action="{$url|escape:'htmlall':'UTF-8'}&add_ticket" method="post" id="add_ticket" name="helpdesk_form" enctype="multipart/form-data" class="std">
		<h3 class="page-subheading">{l s='Post a New Ticket of customer' mod='helpdesk'}</h3>
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
		<div class="form-group">

			<div class="required text">
				<div class="panel">
					<div class="panel-heading">
						{l s='Create a ticket of user' mod='helpdesk'}
					</div>
		<div class="col-lg-12" style="float: left;">
			<label for="title">{l s='Search Customers(please click on customer email)' mod='helpdesk'} <sup>*</sup> </label>
		</div>
		<div class="col-lg-12">
            <input type="text" id="search_id" class="city" placeholder="{l s='Search as Customer' mod='helpdesk'}" aria-label="Search as Customer" class="pull-left" data-validate="isGenericName"name="cust_email">
			<ul id="header_employee_box" class="component">
			   	<li id="employee_infos" class="dropdown hidden-quicklogin  ">
			      <a href="" id="dropdown-quick-login">
			      </a>
			      <ul id="employee_links" class="drop-menu drop-menu-right">
			         <li class="divider"></li>

			            <input type="hidden" id="base_url" name="url" value="{$url|escape:'htmlall':'UTF-8'}">
			            <input type="hidden" id="version" name="url" value="{$check_version|escape:'htmlall':'UTF-8'}">
			            <li class="divider" id="gap"></li>
               <div class="clearfix"></div><br/>

			     </ul>
		         </li>
			  </ul>
		</div>
			  <input type="hidden" name="cust_id" id="#cust_id">
			<div class="required text">
				<label for="title">{l s='Title' mod='helpdesk'} <sup>*</sup> </label>
				<input type="text" name="ticket_subject" id="ticket_subject" title="{l s='Title' mod='helpdesk'}" class="form-control is_required validate" data-validate="isGenericName" />
			</div>
			{if $HELPDESK_SHOW_DEPARTMENTS}
			{if $ticketDepartments}
			<div class="required form-group">
				<label for="t_department_id">{l s='Department' mod='helpdesk'} <sup>*</sup> </label>
				<select name="t_department_id" id="t_department_id" title="{l s='Department ' mod='helpdesk'}" class="form-control" style="width: 500px !important;">
					<option value="">{l s='Select Department' mod='helpdesk'}</option>
					{foreach from=$ticketDepartments item=ticketDepartment}
					<option value="{$ticketDepartment.departments_id|escape:'htmlall':'UTF-8'}">{$ticketDepartment.department_title|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
			</div>
			{/if}
			{/if}
			{if $HELPDESK_PRIORITIES}	
			{if $ticketPriorities}
			<div class="required form-group">
				<label for="priority">{l s='Priority' mod='helpdesk'} <sup>*</sup> </label>
				<select name="t_priority_id" id="t_priority_id" title="{l s='Priority ' mod='helpdesk'}" class="form-control" style="width: 500px !important;">
					<option value="">{l s='Select Priority' mod='helpdesk'}</option>
					{foreach from=$ticketPriorities item=ticketPriority}
					<option value="{$ticketPriority.priorities_id|escape:'htmlall':'UTF-8'}">{$ticketPriority.priorities_title|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
			</div>
			{/if}
			{/if}
			<div class="required form-group">
				<div class="col-lg-12">
				<label for="content">{l s='Message' mod='helpdesk'} <sup>*</sup> </label>
			</div>
				<div class="col-lg-12"><textarea name="content" id="" title="{l s='Message' mod='helpdesk'}" class="col-lg-12" rows="3" cols="26"></textarea></div>
			</div>
               <div class="clearfix"></div><br/>
			
			{if $HELPDESK_FILE_UPLOADS}
			<div class="form-group">
				<label for="content">{l s='Attachment' mod='helpdesk'}</label>
				<input name="helpdesk_attachment" id="helpdesk_attachment" value="" class="form-control" type="file" />
			</div>
			{if $HELPDESK_ACCEPTED_FILE_TYPES neq ""}<p class="inline-infos required">{l s='Supported Types: ' mod='helpdesk'}{$HELPDESK_ACCEPTED_FILE_TYPES|escape:'htmlall':'UTF-8'}</p>{/if}
			{/if}
            <div class="col-lg-12">
                <label class="form-group control-label col-lg-1">
                <span data-toggle="tooltip">{l s='Send user email' mod='helpdesk'}</span>
                </label>
                <div class="col-lg-8">
                <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" {if isset($snd_user_email) AND $snd_user_email == 1}checked="checked"{/if} value="1" id="snd_user_email_on" name="snd_user_email">
                <label for="snd_user_email_on" class="t">{l s='Yes' mod='helpdesk'}</label>
                <input type="radio" value="0" {if isset($snd_user_email) AND $snd_user_email == 0}checked="checked"{/if} id="snd_user_email_off" name="snd_user_email">
                <label for="snd_user_email_off" class="t">{l s='No' mod='helpdesk'}</label>
                <a class="slide-button btn"></a>
                </span>
                </div>
            </div>
               <div class="clearfix"></div><br/>
            <div class="col-lg-12">
                <label class="form-group control-label col-lg-1">
                <span data-toggle="tooltip">{l s='Send admin email' mod='helpdesk'}</span>
                </label>
                <div class="col-lg-8">
                <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" {if isset($snd_admin_email) AND $snd_admin_email == 1}checked="checked"{/if} value="1" id="snd_admin_email_on" name="snd_admin_email">
                <label for="snd_admin_email_on" class="t">{l s='Yes' mod='helpdesk'}</label>
                <input type="radio" value="0" {if isset($snd_admin_email) AND $snd_admin_email == 0}checked="checked"{/if} id="snd_admin_email_off" name="snd_admin_email">
                <label for="snd_admin_email_off" class="t">{l s='No' mod='helpdesk'}</label>
                <a class="slide-button btn"></a>
                </span>
                </div>
            </div>
               <div class="clearfix"></div><br/>
               

        </div>
               <div class="clearfix"></div><br/>

            		<div class="panel">
		<h3>{l s='Your answer to User' mod='helpdesk'}</h3>
		<div class="separation"></div>
		<table cellpadding="0" cellspacing="0">
				<tr>
					<label>{l s='Change Ticket Status' mod='helpdesk'}</label>
						<div class="margin-form">
							<select name="t_status_id" id="t_status_id" style="width: 200px;">
								{foreach from=$ticketStatuses item=ticketStatus}
								<option value="{$ticketStatus.ticketstatus_id|escape:'htmlall':'UTF-8'}">{$ticketStatus.ticketstatus_title|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
							<p class="preference_description"></p>
						</div>
			
						<label>{l s='Change Ticket Department' mod='helpdesk'}</label>
						<div class="margin-form">
							<select name="t_department_id" id="t_department_id" style="width: 200px;">
								{foreach from=$ticketDepartments item=ticketDepartment}
								<option value="{$ticketDepartment.departments_id|escape:'htmlall':'UTF-8'}">{$ticketDepartment.department_title|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
							<p class="preference_description"></p>
						</div>
						
						<label>{l s='Change Ticket Priority' mod='helpdesk'}</label>
						<div class="margin-form">
							<select name="t_priority_id" id="t_priority_id" style="width: 200px;">
								{foreach from=$ticketPriorities item=ticketPriority}
								<option value="{$ticketPriority.priorities_id|escape:'htmlall':'UTF-8'}">{$ticketPriority.priorities_title|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
						</div>
               <div class="clearfix"></div><br/>
						
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
			<p class="submit2">
				<input type="hidden" name="action" id="action" value="ticketsubmit" />
				<input type="submit" class="btn btn-primary button" value="{l s='Post Ticket' mod='helpdesk'}" id="submitAddress" name="submitAddress">
			</p>

		</div>
</form>
</div>
</div>