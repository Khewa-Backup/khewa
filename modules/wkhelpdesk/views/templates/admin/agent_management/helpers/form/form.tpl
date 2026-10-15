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

<form method="post" action="{$current}&{if !empty($submit_action)}{$submit_action}{/if}&token={$token}" class="defaultForm form-horizontal {$name_controller}" enctype="multipart/form-data" id="agentForm">
	<div class="panel">
		<div class="panel-heading">
			{if isset($agentInfo)}
				<i class="icon-pencil"></i>
				{l s='Edit access right of ' mod='wkhelpdesk'}{$agentInfo.name|escape:'html':'UTF-8'} {l s='agent' mod='wkhelpdesk'}
			{else}
				<i class="icon-user"></i>
				{l s='Add new ticket agent' mod='wkhelpdesk'}
			{/if}
		</div>
		<div class="panel-body">
			{if isset($employees) OR isset($agentInfo)}
				{if isset($agentInfo)}
					<input type="hidden" value="{$agentInfo.id|escape:'html':'UTF-8'}" name="id" />
				{else}
					<div class="form-group">
						<label class="control-label col-lg-3">
							<span>{l s='Select employee' mod='wkhelpdesk'} </span>
						</label>
						<div class="col-lg-4 ">
							<select class="fixed-width-xl" name="idEmployee" id="idEmployee">
								{foreach $employees as $employee}
									<option value="{$employee.id_employee|escape:'html':'UTF-8'}">
										({$employee.id_employee|escape:'html':'UTF-8'}) {$employee.firstname|escape:'html':'UTF-8'} {$employee.lastname|escape:'html':'UTF-8'}
									</option>
								{/foreach}
							</select>
						</div>
					</div>
				{/if}

				<div class="form-group">
					<label class="control-label col-lg-3 required">
						<span>{l s='Select access right' mod='wkhelpdesk'} </span>
					</label>

					<div class="col-lg-4 ">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th><input type="checkbox" id="main_check_box" name="main_check_box" /></th>
									<th>{l s='Access Rights' mod='wkhelpdesk'}</th>
								</tr>
								{foreach $allAccessRight as $accessRight}
									<tr>
										<td>
											<input type="checkbox" value="{$accessRight.id|escape:'html':'UTF-8'}" class="access_right_box" name="accessRight[]" {if isset($mappedAccessRights)} {foreach $mappedAccessRights as $temp_access} {if $temp_access.id_access_right == $accessRight.id} checked="checked" {/if} {/foreach} {/if} />
										</td>
										<td>
											{if $accessRight.access_right_text == 'create'}
												{l s='Create' mod='wkhelpdesk'}
											{else if $accessRight.access_right_text == 'delete'}
												{l s='Delete' mod='wkhelpdesk'}
											{else if $accessRight.access_right_text == 'update'}
												{l s='Update' mod='wkhelpdesk'}
											{else if $accessRight.access_right_text == 'assign'}
												{l s='Assign' mod='wkhelpdesk'}
											{else if $accessRight.access_right_text == 'remove'}
												{l s='Remove' mod='wkhelpdesk'}
											{/if}
										</td>
									</tr>
								{/foreach}
							</thead>
						</table>
					</div>
				</div>
			</div>
			<div class="panel-footer">
				<a href="{$link->getAdminLink('AdminAgentManagement')|escape:'html':'UTF-8'}" class="btn btn-default">
					<i class="process-icon-cancel"></i> {l s='Cancel' mod='wkhelpdesk'}
				</a>
				<button type="submit" name="submitAdd{$table|escape:'html':'UTF-8'}" class="btn btn-default pull-right">
					<i class="process-icon-save"></i> {l s='Save' mod='wkhelpdesk'}
				</button>
				<button type="submit" name="submitAdd{$table|escape:'html':'UTF-8'}AndStay" class="btn btn-default pull-right">
					<i class="process-icon-save"></i> {l s='Save and stay' mod='wkhelpdesk'}
				</button>
			</div>
		{else}
			<div class="alert alert-danger">
				{l s='Either no employee found or all employees are ticket agent.' mod='wkhelpdesk'}
			</div>
			</div>
			<div class="panel-footer">
				<a href="{$link->getAdminLink('AdminAgentManagement')|escape:'html':'UTF-8'}" class="btn btn-default">
					<i class="process-icon-back"></i> {l s='Back to list' mod='wkhelpdesk'}
				</a>
			</div>
		{/if}
	</div>
</form>

<script type="text/javascript">
$(document).ready(function(){
	var isSubmit = false;
	$(document).on('change', '#main_check_box', function(){
		if ($('#main_check_box').is(':checked')) {
			$('.access_right_box').attr('checked', 'checked');
		} else {
			$('.access_right_box').removeAttr('checked');
		}
	});
	$(document).on('submit', '#agentForm', function() {
		if (isSubmit) {
			return false;
		} else {
			isSubmit = true;
			return true;
		}
	});
});
</script>