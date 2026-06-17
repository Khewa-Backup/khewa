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

<img src="{$ajax_loader}" id="ajax_loader_img" style="display: none;position: absolute;top: 20%;left: 50%;z-index: 10000;" />
<script type="text/javascript" src="{$tinymceJsLink}"></script>
<script type="text/javascript" src="{$tinymceJsSetupLink}"></script>

<form method="post" action="{$current}&{if !empty($submit_action)}{$submit_action}{/if}&token={$token}" class="defaultForm form-horizontal {$name_controller}" enctype="multipart/form-data" id="replyForm">
{if $add == 1}
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-user"></i>
			{l s='Create new ticket' mod='wkhelpdesk'}
		</div>
		<div class="panel-body">
			<fieldset>
				<div class="clearfix">
					<div class="form-group">
						<label for="firstname" class="control-label required">{l s='First name' mod='wkhelpdesk'}</label>
						<input class="form-control" type="text" name="firstname" id="firstname" {if isset($firstname)} value="{$firstname}" readonly="readonly" {else if isset($smarty.post.firstname)} value="{$smarty.post.firstname}" {/if}/>
					</div>

					<div class="form-group">
						<label for="lastname" class="control-label required">{l s='Last name' mod='wkhelpdesk'}</label>
						<input class="form-control" type="text" name="lastname" id="lastname" {if isset($lastname)} value="{$lastname}" readonly="readonly" {else if isset($smarty.post.lastname)} value="{$smarty.post.lastname}" {/if}/>
					</div>

					<div class="form-group">
						<label for="email" class="control-label required">{l s='Email' mod='wkhelpdesk'}</label>
						<input class="form-control" type="text" name="email" id="email" {if isset($email)} value="{$email}" readonly="readonly" {else if isset($smarty.post.email)} value="{$smarty.post.email}" {/if}/>
					</div>

					<div class="form-group">
						<label for="queryType" class="control-label required">{l s='Query type' mod='wkhelpdesk'}</label>
						<div class="row">
							<div class="col-lg-3">
								<select id="queryType" class="form-control" name="queryType">
									<option value="0">{l s='-- Choose --' mod='wkhelpdesk'}</option>
                                    {if isset($allQueryType)}
                                        {foreach $allQueryType as $queryType}
                                            <option value="{$queryType.id}"{if isset($smarty.request.queryType) && $smarty.request.queryType == $queryType.id} selected="selected"{/if}>{$queryType.query_name}</option>
                                        {/foreach}
                                    {/if}
								</select>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="reference" class="control-label">{l s='Order reference' mod='wkhelpdesk'}</label>
						<input class="form-control" type="text" name="reference" id="reference" {if isset($reference)} value="{$reference}" readonly="readonly" {else if isset($smarty.post.reference)} value="{$smarty.post.reference}" {/if}/>
					</div>

					<div class="form-group">
						<label for="ticketAttachment">{l s='Attachment :' mod='wkhelpdesk'}</label>
                        <div style="display:flex">
						    <input type="file" id="ticketAttachment" name="ticketAttachment" value="" size="chars" />
                            <button type="button" id="removeImage" class="btn btn-primary mx-2">Remove</button>
                        </div>
						<p class="form-control-static">{l s='Valid file extension(s) are ' mod='wkhelpdesk'}{$fileExtensions}.</p>
						<p class="form-control-static">{l s='Maximum file size for all files: ' mod='wkhelpdesk'}{$attachmentMaxSize}{l s='MB' mod='wkhelpdesk'}</p>
					</div>

					<div class="form-group">
						<a class="btn btn-primary" id="hd_btn_other_attachment">
							<span>{l s='Attach More Files' mod='wkhelpdesk'}</span>
						</a>
						<div id="hd_other_files"></div>
			        </div>

					<div class="form-group">
						<label for="subject" class="control-label required">{l s='Subject' mod='wkhelpdesk'}</label>
						<input class="form-control" type="text" name="subject" id="subject" {if isset($smarty.post.subject)} value="{$smarty.post.subject}" {else if isset($smarty.post.subject)} value="{$smarty.post.subject}" {/if}/>
					</div>

					<div class="form-group">
						<label for="message" class="control-label required">{l s='Message' mod='wkhelpdesk'}</label>
						<textarea name="message" id="message" cols="2" rows="8" class="wk_tinymce form-control">{if isset($smarty.post.message)}{$smarty.post.message|escape:'quotes':'UTF-8'}{/if}</textarea>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="panel-footer">
			<a href="{$link->getAdminLink('AdminAllTicket')}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='wkhelpdesk'}</a>
			<button type="submit" name="submitAdd{$table}" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Create' mod='wkhelpdesk'}</button>
			<button type="submit" name="submitAdd{$table}AndStay" class="btn btn-default pull-right">
				<i class="process-icon-save"></i> {l s='Create and stay' mod='wkhelpdesk'}
			</button>
		</div>
	</div>
{else if $ticketViewAccess == 1}
	<div class="panel">
		<div class="panel-heading"><h2>{l s='Ticket details' mod='wkhelpdesk'}</h2></div>
		<div class="panel-body">
			<div class="row" style="margin-bottom: 5px;">
				<div class="col-lg-3"><span class="col-lg-12 text-right">{l s='Ticket ID' mod='wkhelpdesk'}</span></div>
				<div class="col-lg-9">{$ticketDetails.ticket_id}</div>
			</div>
			<div class="row" style="margin-bottom: 5px;">
				<div class="col-lg-3"><span class="col-lg-12 text-right">{l s='Query type' mod='wkhelpdesk'}</span></div>
				<div class="col-lg-9">{$ticketDetails.query_name}</div>
			</div>

			<div class="row" style="margin-bottom: 5px;">
				<div class="col-lg-3"><span class="col-lg-12 text-right">{l s='Ticket create date' mod='wkhelpdesk'}</span></div>
				<div class="col-lg-9">{$ticketDetails.date_add}</div>
			</div>

			<div class="row" style="margin-bottom: 5px;">
				<div class="col-lg-3"><span class="col-lg-12 text-right">{l s='Subject' mod='wkhelpdesk'}</span></div>
				<div class="col-lg-9">{$ticketDetails.subject}</div>
			</div>

			<div class="row" style="margin-bottom: 5px;">
				<div class="col-lg-3"><span class="col-lg-12 text-right">{l s='Current status' mod='wkhelpdesk'}</span></div>
				<div class="col-lg-9">
				{$objHelpDesk->getStatusTextById($ticketDetails.id_status)}
				</div>
			</div>

			<div class="row" style="margin-bottom: 5px;">
				<div class="col-lg-3"><span class="col-lg-12 text-right">{l s='Assigned to' mod='wkhelpdesk'}</span></div>
				<div class="col-lg-3">
				{if $ticketDetails.assigned_agent_id == 0 OR $ticketDetails.assigned_agent_name == ''}
					{l s='Unassigned' mod='wkhelpdesk'}
				{else}
					{$ticketDetails.assigned_agent_name}{if $removeAccessRight == 1} <button id="remove_agent" type="button" class="btn btn-primary">{l s='Remove' mod='wkhelpdesk'}</button>{/if}
				{/if}
				</div>
			</div>

			{if $ticketDetails.id_order != 0}
				{assign var="order_link" value="{$link->getAdminLink(AdminOrders)}&vieworder&id_order={$ticketDetails.id_order}"}
				<div class="row" style="margin-bottom: 5px;">
					<div class="col-lg-3"><span class="col-lg-12 text-right">{l s='Order Id' mod='wkhelpdesk'}</span></div>
					<div class="col-lg-9"><a href="{$order_link}" class="badge _blank">#{$ticketDetails.id_order}</a> <a href="{$order_link}" class="btn btn-default _blank">{l s='See More' mod='wkhelpdesk'}</a></div>
				</div>
			{/if}
		</div>
	</div>
	<div class="panel">
		<div class="panel-heading">
			<div id="conv_heading">
                <div class="row">
                    <div class="col-sm-3">
                        <span class="pull-left" style="font-size: 18px !important;">{l s='Ticket conversation' mod='wkhelpdesk'}</span>
                    </div>
                    <div class="col-sm-9">
                        <span class="pull-right">
                        <a href="" data-status="{if $ticketDetails.is_spam eq 0}1{else}0{/if}" class="btn {if $ticketDetails.is_spam eq 0}btn-danger{else}btn-success{/if}" id="spam_ticket">{if $ticketDetails.is_spam eq 0}{l s='Mark spam' mod='wkhelpdesk'}{else}{l s='Mark not spam' mod='wkhelpdesk'}{/if} <i class="fa-solid fa-envelope"></i></i></a>
                        </span>
                        {if $deleteAccessRight == 1}
                        <span class="pull-right">
                        <a href="{$link->getAdminLink('AdminAllTicket')|escape:'quotes':'UTF-8'}&deletewk_hd_ticket&id={$ticketDetails.ticket_id}" class="btn btn-default"  style="margin-right: 5px;" id="delete_ticket">{l s='Delete this ticket'  mod='wkhelpdesk'} <i class="icon-trash"></i></a>
                        </span>
                        {/if}
                        <span class="pull-right" id="status_error_msg"></span>
                    </div>
                </div>
			</div>
		</div>

		<div class="panel-body" style="padding: 0px !important;">
			{foreach $ticketConversation as $ticket_msg}
				{if $ticket_msg.id_customer != 0}
					<div class="well">
						<p><span class="pull-left"><i class="icon-user"></i> <b>{$ticket_msg.first_name} {$ticket_msg.last_name}</b>
                         ({if $ticket_msg.id_ps_customer neq 0}{l s='Customer' mod='wkhelpdesk'}{else}{l s='Visitor' mod='wkhelpdesk'}{/if})</span>
							<span class="pull-right">
								<span class="timeline-date"><i class="icon-calendar"></i> {dateFormat date=$ticket_msg.date_add full=0} - <i class="icon-time"></i> {$ticket_msg.date_add|substr:11:5}</span>
							</span>
						</p><br />

						{$ticket_msg.message|stripslashes}
						{foreach $objTicketAttachment->getAttachmentByIdMsg($ticket_msg.id) as $attachment}
							<a class="btn btn-default" href="{$link->getModuleLink('wkhelpdesk', 'downloadattachment', ['token' => $attachment['attachment_token'], 'id' => $attachment['id']])}" target="_blank"><i class="icon-download"></i> {l s='Download file' mod='wkhelpdesk'}</a>
						{/foreach}
					</div>
				{else}
					{if $ticket_msg.is_internal_note == 1}
						<div class="alert alert-info">
							<span class="pull-right">
								<span class="timeline-date"><i class="icon-calendar"></i> {dateFormat date=$ticket_msg.date_add full=0} - <i class="icon-time"></i> {$ticket_msg.date_add|substr:11:5}</span>
							</span>

							<p style="text-align: center;">({l s='Internal note by:' mod='wkhelpdesk'} {$ticket_msg.name} ({$ticket_msg.profile} - {$ticket_msg.email}))</p>
							{$ticket_msg.message|stripslashes}
							{foreach $objTicketAttachment->getAttachmentByIdMsg($ticket_msg.id) as $attachment}
								<a class="btn btn-default" href="{$link->getModuleLink('wkhelpdesk', 'downloadattachment', ['token' => $attachment['attachment_token'], 'id' => $attachment['id']])}" target="_blank"><i class="icon-download"></i> {l s='Download file' mod='wkhelpdesk'}</a>
							{/foreach}
						</div>
					{else if $ticket_msg.is_agent_assign == 1 || $ticket_msg.is_agent_assign == 2}
						<div class="alert alert-warning">
							<span class="pull-right">
								<span class="timeline-date"><i class="icon-calendar"></i> {dateFormat date=$ticket_msg.date_add full=0} - <i class="icon-time"></i> {$ticket_msg.date_add|substr:11:5}</span>
							</span>

							{if $ticket_msg.is_agent_assign == 1}
								<p style="text-align: center;">
									{$ticket_msg.name} ({$ticket_msg.profile})
									{l s='assigned agent from ' mod='wkhelpdesk'}
									{if $ticket_msg.agent_from == 0}
										{l s='Unassigned' mod='wkhelpdesk'}
									{else}
										{assign var="temp_name" value="{$objTicketAgent->getAgentNameById($ticket_msg.agent_from)}"}
										{if $temp_name == 'not_found'}
											{l s='Deleted agent' mod='wkhelpdesk'}
										{else}
											{$temp_name}
										{/if}
									{/if}
									{l s='to' mod='wkhelpdesk'}
									{assign var="temp_name" value="{$objTicketAgent->getAgentNameById($ticket_msg.agent_to)}"}
									{if $temp_name == 'not_found'}
										{l s='Deleted agent' mod='wkhelpdesk'}
									{else}
										{$temp_name}
									{/if}
								</p>
								{$ticket_msg.message|stripslashes}
								{foreach $objTicketAttachment->getAttachmentByIdMsg($ticket_msg.id) as $attachment}
									<a class="btn btn-default" href="{$link->getModuleLink('wkhelpdesk', 'downloadattachment', ['token' => $attachment['attachment_token'], 'id' => $attachment['id']])}" target="_blank"><i class="icon-download"></i> {l s='Download file' mod='wkhelpdesk'}</a>
								{/foreach}
							{else}
								<p style="text-align: center;">
									{$ticket_msg.name}
									{l s='remove agent ' mod='wkhelpdesk'}
									{if $ticket_msg.agent_from == 0}
										{l s='Unassigned' mod='wkhelpdesk'}
									{else}
										{assign var="temp_name" value="{$objTicketAgent->getAgentNameById($ticket_msg.agent_from)}"}
										{if $temp_name == 'not_found'}
											{l s='Deleted agent' mod='wkhelpdesk'}
										{else}
											{$temp_name}
										{/if}
									{/if}
								</p>
							{/if}
						</div>
					{else if $ticket_msg.is_status_update == 1}
						<div class="status_upd">
							<div class="alert alert-success">
								<span class="pull-right">
									<span class="timeline-date"><i class="icon-calendar"></i> {dateFormat date=$ticket_msg.date_add full=0} - <i class="icon-time"></i> {$ticket_msg.date_add|substr:11:5}</span>
								</span>

								<p style="text-align: center;">
									{l s='Status updated by ' mod='wkhelpdesk'}
									{if $ticket_msg.name != null}
										{$ticket_msg.name}
									{else}
										{l s='SYSTEM' mod='wkhelpdesk'}
									{/if}
									{l s='from' mod='wkhelpdesk'}
									{$objHelpDesk->getStatusTextById($ticket_msg.status_from)}
									{l s='to' mod='wkhelpdesk'}
									{$objHelpDesk->getStatusTextById($ticket_msg.status_to)}</p>
							</div>
						</div>
					{else}
						<div class="well agent_msg_div">
							<p><span class="pull-left"><i class="icon-user"></i> <b>{$ticket_msg.name}
                            </b>({$ticket_msg.profile})</span>
								<span class="pull-right">
									<span class="timeline-date"><i class="icon-calendar"></i> {dateFormat date=$ticket_msg.date_add full=0} - <i class="icon-time"></i> {$ticket_msg.date_add|substr:11:5}</span>
								</span>
							</p><br />

							{$ticket_msg.message|stripslashes}
							{foreach $objTicketAttachment->getAttachmentByIdMsg($ticket_msg.id) as $attachment}
								<a class="btn btn-default" href="{$link->getModuleLink('wkhelpdesk', 'downloadattachment', ['token' => $attachment['attachment_token'], 'id' => $attachment['id']])}" target="_blank"><i class="icon-download"></i> {l s='Download file' mod='wkhelpdesk'}</a>
							{/foreach}
						</div>
					{/if}
				{/if}
			{/foreach}
				<input type="hidden" name="idAgent" id="idAgent" value="{$agentInfo.id}">
				<input type="hidden" name="id" id="idTicket" value="{$ticketDetails.ticket_id}">
		</div>
	</div>
{/if}
</form>
<script type="text/javascript">
	$(document).ready(function(){
		var img_remove = "{l s='Remove' js=1 mod='wkhelpdesk'}";
		var choosefile_fileButtonHtml = "{l s='Choose File' js=1 mod='wkhelpdesk'}";
		var nofileselect_fileDefaultHtml = "{l s='No file selected' js=1 mod='wkhelpdesk'}";
		var prev_img = "{l s='Please select previous attachment.' js=1 mod='wkhelpdesk'}"
		var status_error = "{l s='There is some technical error, Please try again later.' js=1 mod='wkhelpdesk'}"
		var delete_msg = "{l s='Delete this ticket?.' js=1 mod='wkhelpdesk'}"
		var all_ticket_link = "{$link->getAdminLink('AdminAllTicket')}";
		var isSubmit = false;
		var i =1;
		$('#assigned_agent_div').hide(); // hide dynamic because chosen plugin have some issue to hide from html
		// if image selected
		$(document).on("click", "#hd_btn_other_attachment", function(e){
			e.preventDefault();
			var cover_img = $("#ticketAttachment").val();
			if (!cover_img) {
				$('#ticketAttachment').focus();
				return false;
			} else {
				showOtherImage();
			}
		});

		//code for showing other attachment upload link
		function showOtherImage()
		{
		    var newdiv = document.createElement('div');
		    newdiv.setAttribute("id", "childDiv" + i);
		    newdiv.setAttribute("class", "hdChildDivClass");
		    newdiv.innerHTML = "<div class='col-md-8'><input type='file' id='ticketOtherAttachment"+i+"' name='ticketOtherAttachment[]' class='hd_other_file_attachment'/></div><a class='hd_btn_other_remove btn btn-primary'><span>"+img_remove+"</span></a>";
		    var ni = document.getElementById('hd_other_files');
		    ni.appendChild(newdiv);
		    i++;
		}

		$('input[type="file"]').on('change', function() {
			checkFileSize(this);
		});

		$(document).on('change', '.hd_other_file_attachment', function() {
			checkFileSize(this);
		});


		// Other image div remove event
		$(document).on("click", ".hd_btn_other_remove", function(){
			$(this).parent(".hdChildDivClass").remove();
		});


		$(document).on('click', '#delete_ticket', function(event){
			if (confirm(delete_msg)) {
				return true;
			} else {
				event.stopPropagation();
				event.preventDefault();
			}
		});

		$(document).on('click', '#remove_agent', function(){
			var idTicket = $('#idTicket').val();
			var idAgent = $("#idAgent").val();
			$("#ajax_loader_img").show();
			$("body").css('opacity', '0.5');
			$.ajax({
				type: "POST",
				url: all_ticket_link,
				data: {
					ajax:true,
					action: 'removeAgent',
					idTicket:idTicket,
					idAgent: idAgent
				},
				dataType: "json",
				success: function(result) {
					if (result.status == 'success') {
						window.location.href = all_ticket_link+"&updatewk_hd_ticket&id="+idTicket+"&conf=4";
					} else {
						$("#ajax_loader_img").hide();
						$("body").css('opacity', '1');
						$(this).append(status_error);
					}
				},
				error: function(){
					$("#ajax_loader_img").hide();
					$("body").css('opacity', '1');
					$(this).append(status_error);
				}
			});
		});

        $(document).on('click', '#spam_ticket', function(e){
			e.preventDefault();
			var status = $(this).data('status');
			var idTicket = $('#idTicket').val();
			var idAgent = $("#idAgent").val();
			$("#ajax_loader_img").show();
			$("body").css('opacity', '0.5');
			$.ajax({
				type: "POST",
				url: all_ticket_link,
				data: {
					ajax:true,
					action: 'markCustomerAsSpam',
					idTicket:idTicket,
					status: status,
					idAgent: idAgent
				},
				dataType: "json",
				success: function(result) {
					if (result.status == 'success') {
						window.location.href = all_ticket_link+"&updatewk_hd_ticket&id="+idTicket+"&conf=4";
					} else {
						$("#ajax_loader_img").hide();
						$("body").css('opacity', '1');
						$("#status_error_msg").text(result.msg);
						$("#status_error_msg").show();
					}
				},
				error: function(){
					$("#ajax_loader_img").hide();
					$("body").css('opacity', '1');
					$("#status_error_msg").text(status_error);
					$("#status_error_msg").show();
				}
			});
		});
	});
</script>