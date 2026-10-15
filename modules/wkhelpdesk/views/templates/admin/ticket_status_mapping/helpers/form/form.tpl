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

<form method="post" action="{$current}&{if !empty($submit_action)}{$submit_action}{/if}&token={$token}" class="defaultForm form-horizontal {$name_controller}" enctype="multipart/form-data">
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-pencil"></i>
			{l s='Edit status mapping' mod='wkhelpdesk'}
		</div>
		<div class="panel-body">
			<input type="hidden" value="{$mappedStatus.id}" name="id" />
			<input type="hidden" value="{$mappedStatus.id_status}" name="idStatus" />

			<div class="form-group">
				<label class="control-label col-lg-3">
					<span>
                        {$statusText}
					</span>
				</label>

				<div class="col-lg-4">
					<select class="fixed-width-xl" name="idStatusSelected" id="idStatusSelected">
                    {if isset($allStatus)}
						{foreach $allStatus as $status}
							<option value="{$status.id}" {if $status.id == $mappedStatus.id_status_selected} selected="selected" {/if}>
                                {$status.ticket_status}
							</option>
						{/foreach}
                    {/if}
					</select>
				</div>
			</div>
			<div class="panel-footer">
				<a href="{$link->getAdminLink('AdminTicketStatusMapping')}" class="btn btn-default">
					<i class="process-icon-cancel"></i> {l s='Cancel' mod='wkhelpdesk'}
				</a>
				<button type="submit" name="submitAdd{$table}" class="btn btn-default pull-right">
					<i class="process-icon-save"></i> {l s='Save' mod='wkhelpdesk'}
				</button>
			</div>
		</div>
	</div>
</form>