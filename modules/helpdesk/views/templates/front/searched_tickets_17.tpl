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
{extends file='page.tpl'}

{block name="page_content"}
<h1 class="page-subheading">{l s='Searched Tickets' mod='helpdesk'}</h1>
	{if $result[0]["ticket_id"]}
		<div class="block-center" id="block-history">
			<table class="table table-bordered">
				<thead>
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
				</thead>
				<tbody>
				{foreach from=$result item=ticket_item}
				<tr onclick="window.location.href='{$link->getModuleLink('helpdesk', 'helpdesk')|escape:'htmlall':'UTF-8'}?ticket_id={$ticket_item.ticket_id|escape:'htmlall':'UTF-8'}&detail=1'" style="cursor: pointer;">
					<td>{$ticket_item.ticket_id|escape:'htmlall':'UTF-8'}</td>
					<td>{$ticket_item.ticket_subject|escape:'htmlall':'UTF-8'}</td>
					<td>{$ticket_item.t_created_time|date_format|escape:'htmlall':'UTF-8'}</td>
					<td>{$ticket_item.last_response_staff|date_format:'%H:%M:%S'|escape:'htmlall':'UTF-8'}</td>
					<td>{$ticket_item.department_title|escape:'htmlall':'UTF-8'}</td>
					<td>{$ticket_item.priorities_title|escape:'htmlall':'UTF-8'}</td>
					<td>{$ticket_item.ticketstatus_title|escape:'htmlall':'UTF-8'}</td>
					<td>{$ticket_item.total_replies|escape:'htmlall':'UTF-8'}</td>
				</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
	{else}
		<p class="warning">{l s='No tickets matched to your search crieria.' mod='helpdesk'}</p>
	{/if}

	<div style="clear:both;"></div>
{/block}