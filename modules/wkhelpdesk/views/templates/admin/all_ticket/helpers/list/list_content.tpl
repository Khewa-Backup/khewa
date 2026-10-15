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

{if $isAgent == 1}
<div class="row">
	<div class="col-lg-3">
		<div class="panel panel-default">
			<div class="panel-heading">{l s='Global tickets filter' mod='wkhelpdesk'}</div>
			<div class="panel-body">
				<form class="form-horizontal" method="post" action="">
					<div class="form-group">
						<label class="control-label">{l s='Ticket number' mod='wkhelpdesk'}</label>
						<div class="input-group">
						<input name="ticket_number" class="form-control" id="ticket_number" type="text" placeholder="{l s='Ex. 1562' mod='wkhelpdesk'}"/>
						<div class="input-group-addon">
							<i class="icon-search" id="ticket_search_icon"></i>
							<img src="{$ajaxLoader}" id="ajax_loader_img" style="display: none;" />
						</div>
    					</div>
						<ul id="tickets_ul" class="list-group"></ul>
					</div>

					<div class="form-group">
						<label class="control-label">{l s='Customer' mod='wkhelpdesk'}</label>
						<select name="ticket_customers" class="chosen" id="ticket_customers">
							<option value="0" {if $selectedTicketCustomer == 0} selected="selected" {/if} disabled>{l s='Filter by customer' mod='wkhelpdesk'}</option>
							{foreach $customerList as $id => $ticket_customer}
								<option value="{$id}" {if $selectedTicketCustomer == $id} selected="selected" {/if}>{$ticket_customer.name}({$ticket_customer.email})</option>
							{/foreach}
						</select>
					</div>

					<div class="form-group">
						<label class="control-label">{l s='Query type' mod='wkhelpdesk'}</label>
						<select name="queryType" class="chosen" id="queryType">
							<option value="0" {if $selectedQueryType == 0} selected="selected" {/if} disabled>{l s='Filter by query type' mod='wkhelpdesk'}</option>
                            {if isset($allQueryType)}
							{foreach $allQueryType as $queryType}
								<option value="{$queryType.id}" {if $selectedQueryType == $queryType.id} selected="selected" {/if}>{$queryType.query_name}</option>
							{/foreach}
                            {/if}
						</select>
					</div>

					<div class="form-group">
						<a href="{$adminUri}" class="btn btn-default">
							{l s='Reset filter' mod='wkhelpdesk'}
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="col-lg-9 well">
		<nav class="navbar navbar-default">
			<div class="container-fluid">
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			      <ul class="nav navbar-nav">
			      	{assign var="selected_admin_uri" value="{$adminUri}"}
			      	{if $selectedTicketCustomer != 0}
			      		{assign var="selected_admin_uri" value="{$selected_admin_uri}&ticket_customer={$selectedTicketCustomer}"}
			      	{/if}
			      	{if $selectedQueryType != 0}
			      		{assign var="selected_admin_uri" value="{$selected_admin_uri}&queryType={$selectedQueryType}"}
			      	{/if}
					{* Customization By Ram Chandra *}
					<li {if $selectedStatus == 0}class="active"{/if}>
						<a {if $selectedStatus == 0}class="btn btn-primary"{/if} href="{$selected_admin_uri}&status=0">
							<span class="name">{l s='All tickets' mod='wkhelpdesk'}</span>
							<span class="badge">{$ticketStatusCount[0]}</span>
						</a>
					</li>
					{* END *}
                    {if isset($allStatus)}
                        {foreach $allStatus as $id => $status}
                        {if $objHelpDesk->getMappedStatusIdByStatus({$status.ticket_status})}
                        <li {if $selectedStatus == $status.id}class="active"{/if}>
                            <a {if $selectedStatus == $status.id}class="btn btn-primary"{/if} href="{$selected_admin_uri}&status={$status.id}">
                                <span class="name">{$status.ticket_status}</span>
                                <span class="badge">{$ticketStatusCount[$status.id]}</span>
                            </a>
                        </li>
                        {/if}
                        {/foreach}
                    {/if}
			      </ul>
			    </div>
			</div>
		</nav>
		<div class="panel panel-default">
			<div class="panel-heading" style="padding:5px;">
				<span style="font-size:18px;">
					{* Customization By Ram Chandra *}
                    {if ($selectedStatus > 0) && isset($allStatus) && isset($allStatus[$selectedStatus])}
						{$allStatus[$selectedStatus].ticket_status}{l s=' ticket(s)' mod='wkhelpdesk'}
					{* {if  isset($allStatus)} *}
						{* {foreach $allStatus as $id => $status}
                            {if $selectedStatus == $status.id}
                                {$status.ticket_status}{l s=' ticket(s)' mod='wkhelpdesk'}
                            {/if}
                        {/foreach} *}
					{else}
						{l s='All tickets' mod='wkhelpdesk'}
					{* END *}
                    {/if}
				</span>
			</div>
			<div class="panel-body">
				<div class="table-responsive clearfix" id="ticket_list">
					<table class="table" id="wk_ticket_list_{$openedStatus}" >
						<thead>
							<tr>
								{* Customization #1012187 *}
								<th><input type="checkbox" id="wk-check-all"></th>
								{* Customization end #1012187 *}
								<th>{l s='Ticket ID' mod='wkhelpdesk'}</th>
								<th>{l s='Subject' mod='wkhelpdesk'}</th>
                                {if isset($allShopContext)}
								    <th>{l s='Shop' mod='wkhelpdesk'}</th>
                                {/if}
								<th>{l s='Customer name' mod='wkhelpdesk'}</th>
                                <th>{l s='Customer email' mod='wkhelpdesk'}</th>
								<th>{l s='Order reference' mod='wkhelpdesk'}</th>
								{* Customization By Ram Chandra *}
								{if $selectedStatus == 0}
								    <th>{l s='Status' mod='wkhelpdesk'}</th>
                                {/if}
								{* END *}
								<th>{l s='Query type' mod='wkhelpdesk'}</th>
								<th>{l s='Create date/time' mod='wkhelpdesk'}</th>
							</tr>
						</thead>
						{if isset($ticketList)}
							<tbody>
								{foreach $ticketList as $ticket}
									<tr>
										{* Customization #1012187 *}
										<td><input type="checkbox" class="wk-select-check" value="{$ticket.id}"></td>
										{* Customization end #1012187*}
										<td><a href="{$adminUri}&id={$ticket.id}&updatewk_hd_ticket{if isset($smarty.get.status)}&back_url={$smarty.get.status}{/if}">#{$ticket.id}</a></td>
										<td><a href="{$adminUri}&id={$ticket.id}&updatewk_hd_ticket{if isset($smarty.get.status)}&back_url={$smarty.get.status}{/if}">{$ticket.subject|truncate:30}</a></td>
                                        {if isset($allShopContext)}
                                            <td>{$ticket.shop_name}</td>
                                        {/if}
										<td>{$ticket.customer_name}</td>
                                        <td>{$ticket.email}</td>
										<td>{$ticket.order_ref}</td>
										{* Customization By Ram Chandra *}
										{if ($selectedStatus == 0)}
											<td>
												{if isset($allStatus) && isset($allStatus[$ticket.id_status])}
													{* Customization By Ravindra Gautam *}
													{if $updateAccessRight == 1}
														<span class="pull-right" style="margin-right: 5px;">
															<div class="btn-group">
																<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
																	style="color:white;background: {if $allStatus[$ticket.id_status].color_code|strlen > 0}{$allStatus[$ticket.id_status].color_code}{else}gray;{/if};"
																>
																{$allStatus[$ticket.id_status].ticket_status} <span class="caret" style="margin-left: 5px;"></span>
																</button>
							
																<ul class="dropdown-menu">
																	{foreach $statusList as $key => $status}
																	{if $objHelpDesk->getMappedStatusIdByStatus({$status.ticket_status})}
																		<li><a href="#" data-status="{$status.ticket_status}" data-id_status="{$key}" data-id_ticket="{$ticket.id}" class="change_ticket_status">{$status.ticket_status}</a></li>
																	{/if}
																	{/foreach}
																</ul>
															</div>
														</span>
													{else}
														<span class="badge" style="background: {if $allStatus[$ticket.id_status].color_code|strlen > 0}{$allStatus[$ticket.id_status].color_code}{else}gray;{/if};">{$allStatus[$ticket.id_status].ticket_status}</span>
													{/if}
													{* END *}
												{/if}
											</td>
										{/if}
										{* END *}
										<td>{$ticket.query_name}</td>
										<td>{$ticket.date_add}</td>
									</tr>
								{/foreach}
							</tbody>
						{/if}
					</table>
				</div>
				{* Customization #1012187 *}
				<input type="hidden" name="idAgent" id="idAgent" value="{$agentInfo['id']}">
				{if $updateAccessRight == 1}
					<span class="pull-left" style="margin-right: 5px;">
						<div class="btn-group">
							<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							{l s='Change status' mod='wkhelpdesk'} <span class="caret" style="margin-left: 5px;"></span>
							</button>

							<ul class="dropdown-menu">
								{foreach $statusList as $status}
								{if $objHelpDesk->getMappedStatusIdByStatus({$status.ticket_status})}
									<li><a href="#" data-status="{$status.ticket_status}" class="wk_change_ticket_status">{$status.ticket_status}</a></li>
								{/if}
								{/foreach}
							</ul>
						</div>
					</span>
				{/if}
				{* Customization end #1012187 *}
			</div>
		</div>
	</div>
</div>
<style type="text/css">
	#tickets_ul li {
		padding: 5px 15px !important;
	}
</style>
<script type="text/javascript">
	$(document).ready(function(){
		var display_name = "{l s='Display' js=1 mod='wkhelpdesk'}";
		var records_name = "{l s='records per page' js=1 mod='wkhelpdesk'}";
		var no_product = "{l s='No ticket found' js=1 mod='wkhelpdesk'}";
		var show_page = "{l s='Showing page' js=1 mod='wkhelpdesk'}";
		var show_of = "{l s='of' js=1 mod='wkhelpdesk'}";
		var no_record = "{l s='No records available' js=1 mod='wkhelpdesk'}";
		var filter_from = "{l s='filtered from' js=1 mod='wkhelpdesk'}";
		var t_record = "{l s='total records' js=1 mod='wkhelpdesk'}";
		var search_item = "{l s='Search' js=1 mod='wkhelpdesk'}";
		var p_page = "{l s='Previous' js=1 mod='wkhelpdesk'}";
		var n_page = "{l s='Next' js=1 mod='wkhelpdesk'}";
		var admin_uri = "{$adminUri}";
		var selected_status = "{$selectedStatus}";
		var selected_query_type = "{$selectedQueryType}";
		var selected_ticket_customer = "{$selectedTicketCustomer}";
        var openedStatus = "{$openedStatus}";

		if ($("#wk_ticket_list_"+openedStatus).length) {
			$('#wk_ticket_list_'+openedStatus).DataTable({
                "pageLength": 50,
				"aaSorting": [[8, "desc"]],
				"language": {
					"stateSave": true,
					"lengthMenu": display_name+" _MENU_ "+records_name,
					"zeroRecords": no_product,
					"info": show_page+" _PAGE_ "+ show_of +" _PAGES_ ",
					"infoEmpty": no_record,
					"infoFiltered": "("+filter_from +" _MAX_ "+ t_record+")",
					"sSearch" : search_item,
					"oPaginate": {
						"sPrevious": p_page,
						"sNext": n_page
					}
				}
			});
		}

		var redirect_uri = admin_uri+'&status='+selected_status;
        $(document).on('click', '#ticket_search_icon', function(){
			window.location.href = redirect_uri+'&queryType=&ticket_customer=&id_ticket='+ $('#ticket_number').val();
		});

		$(document).on('change', '#ticket_customers', function(){
			window.location.href = redirect_uri+'&ticket_customer='+$(this).val()+'&queryType='+selected_query_type;
		});

		$(document).on('change', '#queryType', function(){
			window.location.href = redirect_uri+'&queryType='+$(this).val()+'&ticket_customer='+selected_ticket_customer;
		});

		var idAgent = "{$agentInfo.id}";
		var isSuperAdmin = "{$agentInfo.is_super_admin}";
		var searchTicketNumber = null;
		var xhr;
		$(document).click(function(){
			$("#tickets_ul").hide();
		});
		$('#ticket_number').on('keyup', function(){
			$("#tickets_ul").hide();
			$("#tickets_ul").html('');
			ticketNumber = $(this).val();
			if (ticketNumber.length > 2) {
				$("#ticket_search_icon").hide();
				$("#ajax_loader_img").show();
				if(xhr && xhr.readystate != 4){
		            xhr.abort();
		        }
				$xhr = $.ajax({
					type: "POST",
					url: admin_uri,
					data: {
						ajax:true,
						idAgent:idAgent,
						action: 'searchTicket',
						ticketNumber:ticketNumber,
						isSuperAdmin: isSuperAdmin
					},
					dataType: "json",
					success: function(result) {
						if (result.status == 'success') {
							$(result.info).each(function(index, item){
								$("#tickets_ul").append("<li class='list-group-item'><a href='"+admin_uri+"&updatewk_hd_ticket&id="+item.id+"'>"+item.id+"</a></li>");
								$('#tickets_ul li a').css('cursor','pointer');
							});
							$("#tickets_ul").show();
						}
						$("#ajax_loader_img").hide();
						$("#ticket_search_icon").show();
					},
					error: function(){
						$("#ajax_loader_img").hide();
						$("#ticket_search_icon").show();
					}
				});
			} else {
				$("#tickets_ul").hide();
				$("#tickets_ul").html('');
			}
		});

		$(document).on('mouseover', '#tickets_ul li', function(){
			$(this).addClass('active');
			$(this).children().attr('style', 'color: #ffffff !important;cursor:pointer;');
		});
		$(document).on('mouseout', '#tickets_ul li', function(){
			$(this).removeClass('active');
			$(this).children().removeAttr('style');
		});
        $('.chosen-search > input').removeAttr('readonly');

	});
	// Customization By Ravindra Gautam 
	var all_ticket_link = "{$link->getAdminLink('AdminAllTicket')}";
	var id_agent = "{$agentInfo.id}";
	$(document).on('click', '.change_ticket_status', function(e){
		e.preventDefault();
		var element = $(this);
		var status = $(this).data('status');
		var idStatus = $(this).data('id_status');
		var idTicket = $(this).data('id_ticket');
		var idAgent = id_agent;
		$("#ajax_loader_img").show();
		$("body").css('opacity', '0.5');
		$.ajax({
			type: "POST",
			url: all_ticket_link,
			data: {
				ajax:true,
				action: 'changeTicketStatus',
				idTicket:idTicket,
				status: status,
				idAgent: idAgent
			},
			dataType: "json",
			success: function(result) {
				if (result.status == 'success') {
					var statusColorCodes = ['#25b9d7', 'mediumseagreen', 'blueviolet', '#fbbb22', '#72c279', '#e08f95'];
					var color_code = '';
					if (typeof statusColorCodes[idStatus] != 'undefined') {
						color_code = statusColorCodes[idStatus];
					}
					element.parent().closest('.btn-group').find('button').html(status + `<span class="caret" style="margin-left: 5px;"></span>`)
					element.parent().closest('.btn-group').find('button').css('background', color_code)
					$("#ajax_loader_img").hide();
					$("body").css('opacity', '1');
					showSuccessMessage(result.msg);
				} else {
					$("#ajax_loader_img").hide();
					$("body").css('opacity', '1');
					showErrorMessage(result.msg);
				}
			},
			error: function(){
				$("#ajax_loader_img").hide();
				$("body").css('opacity', '1');
				showErrorMessage(status_error);
			}
		});
	});
	// END
</script>
{else}
	<div class="alert alert-danger">
		{l s='You do not have access right to view this page.' mod='wkhelpdesk'}
	</div>
{/if}
