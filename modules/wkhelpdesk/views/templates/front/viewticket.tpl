{**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

{extends file=$layout}
{block name='content'}
<script type="text/javascript" src="{$smarty.const._MODULE_DIR_}wkhelpdesk/views/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="{$smarty.const._MODULE_DIR_}wkhelpdesk/views/js/tinymce/tinymce_wk_setup.js"></script>
<style>
	.ticketMainAttachment .group-span-filestyle .btn-default{
		color: #f8f8f8;
		background: #b67a24;
	}

</style>

{if isset($smarty.get.sent)}
	<div class="alert alert-success">{l s='Your message sent successfully.' mod='wkhelpdesk'}</div>
{/if}

{if isset($error)}
	<div class="alert alert-danger">
		{if $error == 1}
			{l s='Either you do not have access right to view this ticket details or this ticket details are not found.' mod='wkhelpdesk'}
		{/if}
	</div>
{else}
	<div class="page-title wk_hd_main_div" style="background-color:{$bgColor};">
		<span class="bottom-indent" style="color:{$textColor};">
			{l s='Ticket Details' mod='wkhelpdesk'}
		</span>
	</div>
	<div class="wk_hd_main_div">
		<div class="msg_div row">
			<div class="col-sm-6">
				<div class="row">
					<div class="col-lg-3">{l s='Ticket ID' mod='wkhelpdesk'} :</div>
					<div class="col-lg-9 wk_hd_td_value">#{$ticketDetails.ticket_id}</div>
				</div>
				<div class="row">
					<div class="col-lg-3">{l s='Subject' mod='wkhelpdesk'} :</div>
					<div class="col-lg-9 wk_hd_td_value">{$ticketDetails.subject}</div>
				</div>
				<div class="row">
					<div class="col-lg-3">{l s='Query type' mod='wkhelpdesk'} :</div>
					<div class="col-lg-9 wk_hd_td_value">{$ticketDetails.query_name}</div>
				</div>
			</div>

			<div class="col-sm-6">
				<div class="row">
					<div class="col-lg-3">{l s='Date' mod='wkhelpdesk'} :</div>
					<div class="col-lg-9 wk_hd_td_value">
						<span class="timeline-date">
							{$ticketDetails.date_add|date_format:"%d-%b-%Y"}
						</span>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-3" style="padding-right: 0px;">{l s='Current status' mod='wkhelpdesk'}:</div>
					<div class="col-lg-9 wk_hd_td_value">
						<span class="wk_hd_ticket_status d-inline-block" style="background:{if $ticketDetails.id_status <= 6 }{$statusColors[($ticketDetails.id_status - 1)]}{else}deepskyblue{/if}">{$statusNameWithId[0]}</span>
					</div>
				</div>
				<div class="row">
					{if $ticketDetails.id_order > 0}
						<div class="col-lg-3">{l s='Order ID' mod='wkhelpdesk'} :</div>
						<div class="col-lg-9 wk_hd_td_value">
							<a href="{$link->getPageLink('order-detail', true, null, ['id_order' => $ticketDetails.id_order])}">
								<span class="btn-info" style="padding:2px 8px; border-radius:20px;">#{$ticketDetails.id_order}</span>
                                <button class="btn btn-primary mt-media-5">{l s='Order details' mod='wkhelpdesk'}</button>
							</a>
						</div>
					{/if}
				</div>
			</div>
		</div>
	</div>
	<span class="d-block h4 my-2 m-y-2">{l s='Ticket conversation' mod='wkhelpdesk'}</span>
	<div class="container wk_hd_main_div p-2 p-x-2 p-y-2">
		{foreach $ticketConversation as $ticket_msg}
			{if $ticket_msg.id_customer != 0}
				{assign var="name" value="{$ticket_msg.first_name} {$ticket_msg.last_name}"}
				{include file="module:wkhelpdesk/views/templates/front/_partials/sent_message.tpl" ticket_msg=$ticket_msg is_attachment=count($objTicketAttachment->getAttachmentByIdMsg($ticket_msg.id)) objTicketAttachment=$objTicketAttachment name=$name}
			{else if $ticket_msg.is_internal_note == 0 AND $ticket_msg.is_status_update == 0}
				{assign var="name" value="Support team"}
				{include file="module:wkhelpdesk/views/templates/front/_partials/received_message.tpl" ticket_msg=$ticket_msg is_attachment=count($objTicketAttachment->getAttachmentByIdMsg($ticket_msg.id)) objTicketAttachment=$objTicketAttachment name=$name}
			{/if}
		{/foreach}
	</div>
	<div class="container wk_hd_main_div p-2 p-x-2 p-y-2 m-t-2">
		<form action="{$formUrl}" method="post" class="contact-form-box" enctype="multipart/form-data" accept-charset="UTF-8,ISO-8859-1,UTF-16" id="replyform">
			<input type="hidden" name="hdIdCustomer" value="{$ticketDetails.hd_id_customer}">
			<div class="form-group wk-form-group">
				<label for="hd_message" class="control-label">{l s='You' mod='wkhelpdesk'}</label>
				<textarea name="hd_message" id="hd_message" cols="2" rows="8" class="wk_tinymce form-control">{if isset($smarty.post.hd_message)}{$smarty.post.hd_message|escape:'quotes':'UTF-8'}{/if}</textarea>
			</div>

			<div class="form-group wk-form-group mb-0 m-b-0">
				<label>{l s='Attachment :' mod='wkhelpdesk'}</label>
                <div style="display:flex">
				<div class="ticketMainAttachment">
					<input type="file" id="ticketAttachment" name="ticketAttachment" value="" class="filestyle" size="chars" data-buttonText="{l s='Choose file' mod='wkhelpdesk'}"/>
				</div>
                <button type="button" id="removeImage" class="btn btn-primary mx-2">{l s='Remove' mod='wkhelpdesk'}</button>
                </div>
				{*<p class="form-control-static m-0">
					{l s='Valid file extension(s) are ' mod='wkhelpdesk'}{$fileExtensions}.
				</p>*}
				<p class="form-control-static m-0">{l s='Maximum file size: ' mod='wkhelpdesk'}{$attachmentMaxSize}{l s='MB' mod='wkhelpdesk'}</p>
			</div>
			<div id="hd_other_files"></div>
			<div class="form-group wk-form-group">
				<a class="btn btn-primary" id="hd_btn_other_attachment">
					<span>{l s='Attach more files' mod='wkhelpdesk'}</span>
				</a>
			</div>
			{if isset($wkHdCaptchaSiteKey)}
				<div class="g-recaptcha" data-sitekey="{$wkHdCaptchaSiteKey}"></div>
			{/if}
			<div class="submit">
				<button type="submit" name="replyTicket" id="replyTicket" class="btn btn-primary pr-0">
					<span>
						{l s='Send' mod='wkhelpdesk'}
					</span>
					<i class="material-icons">&#xE315;</i>
				</button>
			</div>
		</form>
	</div>
{/if}
{/block}