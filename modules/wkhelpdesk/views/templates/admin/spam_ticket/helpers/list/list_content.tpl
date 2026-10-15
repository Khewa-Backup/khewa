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
			<div class="panel-heading">{l s='Global spam user tickets filter' mod='wkhelpdesk'}</div>
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
						<select name="ticket_customers" class="chosen form-control" id="ticket_customers">
							<option value="0" {if $selectedTicketCustomer == 0} selected="selected" {/if}>{l s='Filter by customer' mod='wkhelpdesk'}</option>
							{foreach $customerList as $id => $ticket_customer}
								<option value="{$id}" {if $selectedTicketCustomer == $id} selected="selected" {/if}>{$ticket_customer.name}({$ticket_customer.email})</option>
							{/foreach}
						</select>
					</div>

					<div class="form-group">
						<label class="control-label">{l s='Query type' mod='wkhelpdesk'}</label>
						<select name="queryType" class="chosen form-control" id="queryType">
							<option value="0" {if $selectedQueryType == 0} selected="selected" {/if}>{l s='Filter by query type' mod='wkhelpdesk'}</option>
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
                    {if isset($allStatus)}
                        {* {foreach $allStatus as $id => $status}
                        <li {if $selectedStatus == $status.id}class="active"{/if}>
                            <a {if $selectedStatus == $status.id}class="btn btn-primary"{/if} href="{$selected_admin_uri}&status={$status.id}">
                                <span class="name">{$status.ticket_status}</span>
                                <span class="badge">{$ticketStatusCount[$status.id]}</span>
                            </a>
                        </li>
                        {/foreach} *}
                    {/if}
                    <li class="active">
                        <a class="btn btn-primary" href="{$selected_admin_uri}">
                            <span class="name">{l s='Spam users' mod='wkhelpdesk'}</span>
                            <span class="badge">{$ticketStatusCount}</span>
                        </a>
                    </li>
			      </ul>
			    </div>
			</div>
		</nav>
		<div class="panel panel-default">
			<div class="panel-heading" style="padding:5px;">
				<span style="font-size:18px;">
                    {l s='Spam user ticket(s)' mod='wkhelpdesk'}
				</span>
			</div>
			<div class="panel-body">
				<div class="table-responsive clearfix" id="ticket_list">
					<table class="table" id="wk_ticket_list">
						<thead>
							<tr>
								<th>{l s='Ticket ID' mod='wkhelpdesk'}</th>
								<th>{l s='Subject' mod='wkhelpdesk'}</th>
                                {if isset($allShopContext)}
								    <th>{l s='Shop' mod='wkhelpdesk'}</th>
                                {/if}
								<th>{l s='Customer name' mod='wkhelpdesk'}</th>
								<th>{l s='Order' mod='wkhelpdesk'}</th>
								<th>{l s='Query type' mod='wkhelpdesk'}</th>
								<th>{l s='Create date/time' mod='wkhelpdesk'}</th>
							</tr>
						</thead>
						{if isset($ticketList)}
							<tbody>
								{foreach $ticketList as $ticket}
									<tr>
										<td><a href="{$adminUri}&id={$ticket.id}&updatewk_hd_ticket">#{$ticket.id}</a></td>
										<td><a href="{$adminUri}&id={$ticket.id}&updatewk_hd_ticket">{$ticket.subject|truncate:30}</a></td>
                                        {if isset($allShopContext)}
                                            <td>{$ticket.shop_name}</td>
                                        {/if}
										<td>{$ticket.customer_name}</td>
										<td>{$ticket.order_ref}</td>
										<td>{$ticket.query_name}</td>
										<td>{$ticket.date_add}</td>
									</tr>
								{/foreach}
							</tbody>
						{/if}
					</table>
				</div>
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

		if ($("#wk_ticket_list").length) {
			$('#wk_ticket_list').DataTable({
				"language": {
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
</script>
{else}
	<div class="alert alert-danger">
		{l s='You do not have access right to view this page.' mod='wkhelpdesk'}
	</div>
{/if}