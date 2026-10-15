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
<div class="panel" style="margin-top:18px !important">
			<h3>{l s='Customer Orders message' mod='helpdesk'}</h3>	
			<div class="separation"></div>
			{if $cust_orders_msgs}
				{foreach from=$cust_orders_msgs item=ordermsg}
				<hr>
				<div class="bg-info" style="border-radius:2px;height:40px;">
					<h4 style="margin-left:10px;padding-top:10px;">ID Order:{$ordermsg['id_order']|escape:'htmlall':'UTF-8'}
					,Referernce:{$ordermsg['reference']|escape:'htmlall':'UTF-8'}</h4>
				</div>
				{foreach from=$ordermsg|@array_reverse:true key=k item=msg}
				{if $k!=="id_order" && $k!=="reference"}
				<div class="row" {if $msg.id_employee eq 0}style="margin-left:-50px !important"{/if}>
					<div class="message-item">
						<div class="message-avatar">
							<div class="avatar-md">
							 	{if $msg.id_employee neq 0}
							 	<img src="{$path|escape:'htmlall':'UTF-8'}modules/helpdesk/views/img/icon-user-default.png" />
							 	{else}
							 	<img src="{$path|escape:'htmlall':'UTF-8'}modules/helpdesk/views/img/admin_ico.jpg" />
							 	{/if}
							</div>
						</div>
						<div class="message-body">
							{if $msg.id_employee neq 0}Admin{else} {$msg["cfirstname"]|escape:'htmlall':'UTF-8'}{$msg["clastname"]|escape:'htmlall':'UTF-8'}{/if}
							<span class="message-date">&nbsp;
								<i class="icon-calendar"></i> - 
							 	{$msg.date_add|date_format:'%A, %b %d'|escape:'htmlall':'UTF-8'} - 
							 	<i class="icon-time"></i> 
							 	{$msg.date_add|date_format:'%H:%M:%S'|escape:'htmlall':'UTF-8'}
							</span>
							
							<p class="message-item-text">{$msg['message']|nl2br}{*HTML content*} </p>
						</div>
					</div>
				</div>
				{/if}
				{/foreach}
				{/foreach}
			</div>
			
			{else}
			<p class="warning">{l s='No Message have been posted yet.' mod='helpdesk'}</p>
		{/if}