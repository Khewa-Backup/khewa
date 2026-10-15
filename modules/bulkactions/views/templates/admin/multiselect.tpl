{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="multiselect atts">
	<div class="multiselect-value">{l s='Select combinations by attributes' mod='bulkactions'}</div>
	<div class="multiselect-options">
		{if !$options}<div class="multiselect-group">--</div>{/if}
		{foreach $options as $group_name => $group_attributes}
			<div class="multiselect-group">
				<div class="multiselect-group-title">{$group_name|escape:'html':'UTF-8'}:</div>
				{foreach $group_attributes as $att_name => $comb_ids}
					<label class="multiselect-option">
						<input type="checkbox" value="{implode('-', $comb_ids)|escape:'html':'UTF-8'}" class="multiselect-input">
						{$att_name|escape:'html':'UTF-8'}
					</label>
				{/foreach}
			</div>
		{/foreach}
	</div>
</div>
{* since 1.3.0 *}
