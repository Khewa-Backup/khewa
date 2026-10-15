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
<form method="post" action="{$current}&{if !empty($submit_action)}{$submit_action}{/if}&token={$token}" class="defaultForm form-horizontal {$name_controller}" enctype="multipart/form-data" id="groupForm">
	<div class="panel">
		<div class="panel-heading">
			{if isset($groupInfo)}
			<i class="icon-pencil"></i>
			{l s='Edit group' mod='wkhelpdesk'} {$groupInfo['group_name'][{$current_lang.id_lang}]}
			{else}
			<i class="icon-user"></i>
			{l s='Add new group' mod='wkhelpdesk'}
			{/if}
		</div>
		<div class="panel-body">
		{if isset($allAgents)}
			<div class="form-group">
				<label class="control-label col-lg-3 required">
					<span>{l s='Group name' mod='wkhelpdesk'} </span>
				</label>
				{if $total_languages > 1}
				<div class="col-lg-4">
				{else}
				<div class="col-lg-6">
				{/if}
					{if isset($groupInfo)}
						<input type="hidden" value="{$groupInfo.id}" name="id" />
						{foreach from=$languages item=language}
							{assign var="group_name" value="group_name_`$language.id_lang`"}
							<input type="text"
							id="group_name_{$language.id_lang}"
							name="group_name_{$language.id_lang}"
							value="{$groupInfo['group_name'][{$language.id_lang}]}"
							class="form-control group_name_all"
							data-lang-name="{$language.name}"
							{if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if} maxlength="128"/>
						{/foreach}
					{else}
						{foreach from=$languages item=language}
							{assign var="group_name" value="group_name_`$language.id_lang`"}
							<input type="text"
							id="group_name_{$language.id_lang}"
							name="group_name_{$language.id_lang}"
							value="{if isset($smarty.post.$group_name)}{$smarty.post.$group_name}{/if}"
							class="form-control group_name_all
							{if $current_lang.id_lang == $language.id_lang}group_default_lang_class{/if}"
							data-lang-name="{$language.name}"
							{if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if} maxlength="128"/>
						{/foreach}
					{/if}
				</div>
				{if $total_languages > 1}
				<div class="col-lg-2">
					<button type="button" id="group_lang_btn" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						{$current_lang.iso_code}
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						{foreach from=$languages item=language}
							<li>
								<a href="javascript:void(0)" onclick="showGroupLangField('{$language.iso_code}', {$language.id_lang});">{$language.name}</a>
							</li>
						{/foreach}
					</ul>
				</div>
				{/if}
			</div>
			<div class="form-group">
				<label class="control-label col-lg-3 required">
					<span>{l s='Select ticket agent' mod='wkhelpdesk'} </span>
				</label>
				<div class="col-lg-4 ">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th><input type="checkbox" id="main_check_box" name="main_check_box" /></th>
								<th>{l s='Ticket agent' mod='wkhelpdesk'}</th>
							</tr>
							{foreach $allAgents as $agent}
								<tr>
									<td>
										<input type="checkbox" value="{$agent.id}" class="access_right_box" name="groupAgent[]"
										{if isset($groupAgentMapping)}
											{foreach $groupAgentMapping as $groupAgent}
												{if $groupAgent.id_agent == $agent.id} checked="checked" {/if}
											{/foreach}
										{/if}
										/>
									</td>
									<td>
										{$agent.name}({$agent.email})
									</td>
								</tr>
							{/foreach}
						</thead>
					</table>
				</div>
			</div>
			</div>
			<div class="panel-footer">
				<a href="{$link->getAdminLink('AdminGroupManagement')}" class="btn btn-default">
					<i class="process-icon-cancel"></i> {l s='Cancel' mod='wkhelpdesk'}
				</a>
				<button type="submit" name="submitAdd{$table}" class="btn btn-default pull-right">
					<i class="process-icon-save"></i> {l s='Save' mod='wkhelpdesk'}
				</button>
				<button type="submit" name="submitAdd{$table}AndStay" class="btn btn-default pull-right">
					<i class="process-icon-save"></i> {l s='Save and stay' mod='wkhelpdesk'}
				</button>
			</div>
		{else}
			<div class="alert alert-danger">
				{l s='Ticket agent not found. First you need to create ticket agent.' mod='wkhelpdesk'}
			</div>
			</div>
			<div class="panel-footer">
				<a href="{$link->getAdminLink('AdminGroupManagement')}" class="btn btn-default"><i class="process-icon-back"></i> {l s='Back to list' mod='wkhelpdesk'}</a>
			</div>
		{/if}
	</div>
</form>
<script>
	$(document).ready(function(){
		var isSubmit = false;
		$(document).on('change', '#main_check_box', function(){
			if ($('#main_check_box').is(':checked')) {
				$('.access_right_box').attr('checked', 'checked');
			} else {
				$('.access_right_box').removeAttr('checked');
			}
		});
		$(document).on('submit', '#groupForm', function() {
			if (isSubmit) {
				return false;
			} else {
				isSubmit = true;
				return true;
			}
		});
	});
	function showGroupLangField(lang_iso_code, id_lang)
	{
		$('#group_lang_btn').html(lang_iso_code + ' <span class="caret"></span>');
		$('.group_name_all').hide();
		$('#group_name_'+id_lang).show();
	}
</script>