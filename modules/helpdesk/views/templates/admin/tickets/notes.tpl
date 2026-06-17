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
<div id="notes">
    	<h4>{l s='Internal Notes' mod='helpdesk'}</h4>
		<div class="separation"></div>
        {if $ticketNotesData}
		<table cellspacing="0" cellpadding="0" width="100%" class="table">
			{assign var="alt" value="1"}
			{foreach from=$ticketNotesData item=ticketNote}
			<tr {if $alt eq 1}class="alt_row"{/if}>
				<td>{$ticketNote.note_created|date_format:'%A, %b %d %H:%M:%S'|escape:'htmlall':'UTF-8'}</td>
			</tr>
			{if $alt eq 1}
			{assign var="alt" value="0"}
			{else}
			{assign var="alt" value="1"}
			{/if}
			<tr {if $alt eq 1}class="alt_row"{/if}>
				<td style="text-align: left;">{$ticketNote.note_content|escape:'htmlall':'UTF-8'}</td>
			</tr>
			{if $alt eq 1}
			{assign var="alt" value="0"}
			{else}
			{assign var="alt" value="1"}
			{/if}
			{/foreach}
		</table>
		<div class="separation"></div>
		{/if}
		<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<label>{l s='Note Title' mod='helpdesk'}</label>
						<div class="margin-form">
							<input type="text" id="notes_title" name="notes_title" style="width:300px;">
							<p class="preference_description"></p>
						</div>
			
						<label>{l s='Note Content' mod='helpdesk'}</label>
						<div class="margin-form">
							<textarea style="width:300px; height:120px;" id="notes_content" name="notes_content"></textarea>
							<p class="preference_description"></p>
						</div>
					</td>
				</tr>
			</table>
		
    </div>