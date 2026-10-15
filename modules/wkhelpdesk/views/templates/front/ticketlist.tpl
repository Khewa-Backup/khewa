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
{if isset($smarty.get.created)}
	<div class="alert alert-success">
		{l s='Your ticket has been created successfully. Our support department will contact you very soon.' mod='wkhelpdesk'}
	</div>
{/if}

<div class="page-title" style="background-color:{$hd_bg_color};">
	<span class="bottom-indent" style="color:{$hd_text_color};">
		{l s='Ticket List' mod='wkhelpdesk'}
	</span>
</div>

<div class="hd_main_div padding_div">
	<div class="table-responsive" id="ticket_list">
		<table class="table" id="wk_ticket_list">
			<thead>
				<tr>
					<th>{l s='Ticket ID' mod='wkhelpdesk'}</th>
					<th>{l s='Query Type' mod='wkhelpdesk'}</th>
					<th>{l s='Subject' mod='wkhelpdesk'}</th>
					<th>{l s='Status' mod='wkhelpdesk'}</th>
					<th>{l s='Create date/time' mod='wkhelpdesk'}</th>
					<th>{l s='Details' mod='wkhelpdesk'}</th>
				</tr>
			</thead>
			<tbody>
				{if isset($ticketList)}
					{foreach $ticketList as $ticket}
						{assign var="ticketUrl" value="{url entity='module' name='wkhelpdesk' controller='viewticket' params=['id' => $ticket.id]}"}
						<tr>
							<td>
								{$ticket.id}
							</td>
							<td>
								{$ticket.query_name}
							</td>
							<td>
								<a href="{$ticketUrl}">
									{$ticket.subject}
								</a>
							</td>
							<td>
                                <span class="wk_hd_ticket_status" style="background:{if $ticket.id_status <= 6 }{$statusColors[($ticket.id_status - 1)]}{else}deepskyblue{/if}">
									{$ticket.status}
								</span>
							</td>
							<td>{dateFormat date=$ticket.date_add full=1}</td>
							<td class="wk_hd_ticket_view_btn">
								<a href="{$ticketUrl}" class="btn btn-primary" title="{l s='Preview' mod='wkhelpdesk'}">
									<span class="view_ticket"><i class="fa fa-eye"></i></span>
								</a>
							</td>
						</tr>
					{/foreach}
				{/if}
			</tbody>
		</table>
	</div>
</div>
{/block}
