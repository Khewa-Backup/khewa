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
<form method="post" action="{$current}&{if !empty($submit_action)}{$submit_action}{/if}&token={$token}" class="defaultForm form-horizontal {$name_controller}" enctype="multipart/form-data" id="queryTypeForm">
	<div class="panel">
		<div class="panel-heading">
            {if isset($queryTypeInfo)}
			<i class="icon-pencil"></i>
			{l s='Edit status' mod='wkhelpdesk'}
			{else}
			<i class="icon-user"></i>
			{l s='Add new status' mod='wkhelpdesk'}
			{/if}
		</div>
        <div class="panel-body">
			<div class="form-group">
				<label class="control-label col-lg-3 required ps_wk_hd_ticket">
					<span>{l s='Status value' mod='wkhelpdesk'} </span>
				</label>
				<div class="col-lg-4">
                    <input type="hidden" name="id" value="{if isset($smarty.get.id)}{$smarty.get.id}{/if}">
                    {foreach from=$languages item=language}
                        {assign var="status_name" value="status_name_`$language.id_lang`"}
						<input type="text"
						id="status_name_{$language.id_lang}"
						name="status_name_{$language.id_lang}"
						value="{if isset($queryTypeInfo)}{$queryTypeInfo['ticket_status'][{$language.id_lang}]}{/if}"
						class="form-control status_name_all
						{if $current_lang.id_lang == $language.id_lang}status_name_default_lang_class{/if}"
						data-lang-name="{$language.name}"
						{if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if} maxlength='50'>
                    {/foreach}
				</div>
                {if $total_languages > 1}
                    <div class="col-lg-2">
                        <button type="button" id="status_name_lang_btn" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            {$current_lang.iso_code}
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            {foreach from=$languages item=language}
                                <li>
                                    <a href="javascript:void(0)" onclick="showQueryLangField('{$language.iso_code}', {$language.id_lang});">{$language.name}</a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
			</div>


        </div>

        <div class="panel-footer">
			<a href="{$link->getAdminLink('AdminTicketStatus')}" class="btn btn-default">
				<i class="process-icon-cancel"></i> {l s='Cancel' mod='wkhelpdesk'}
			</a>
			<button type="submit" name="submitAdd{$table}" class="btn btn-default pull-right">
				<i class="process-icon-save"></i> {l s='Save' mod='wkhelpdesk'}
			</button>
			<button type="submit" name="submitAdd{$table}AndStay" class="btn btn-default pull-right">
				<i class="process-icon-save"></i> {l s='Save and stay' mod='wkhelpdesk'}
			</button>
		</div>
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
		$(document).on('submit', '#queryTypeForm', function() {
			if (isSubmit) {
				return false;
			} else {
				isSubmit = true;
				return true;
			}
		});
	});
	function showQueryLangField(lang_iso_code, id_lang)
	{
		$('#status_name_lang_btn').html(lang_iso_code + ' <span class="caret"></span>');
		$('.status_name_all').hide();
		$('#status_name_'+id_lang).show();
	}
</script>