{*
* 2007-2019 Amazzing
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright 2007-2019 Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*}

{foreach $rows as $row}<tr class="att-row" data-id="{$row.id|intval}" data-group="{$row.id_group|intval}">
		<td class="att-id">{$row.id|intval}</td>
		<td class="att-name">
			<span class="group-name">{$row.group_name|escape:'html':'UTF-8'}:</span> {$row.name|escape:'html':'UTF-8'}
			<input type="hidden" name="a[values][{$row.id_group|intval}][{$row.id|intval}]" value="{$row.id|intval}">
		</td>
		{foreach $combination_fields as $field_input_name => $field}
			<td class="attribute-impact">
				<div class="no-value text-center">--</div>
				<div class="input">
					<select name="a[impacts][{$field_input_name|escape:'html':'UTF-8'}][{$row.id|intval}][prefix]" class="input-prefix">
						<option value="+" class="first"> + </option>
						<option value="-"> - </option>
					</select>
					<input type="text" name="a[impacts][{$field_input_name|escape:'html':'UTF-8'}][{$row.id|intval}][value]" class="impact-value">
					<select name="a[impacts][{$field_input_name|escape:'html':'UTF-8'}][{$row.id|intval}][suffix]" class="input-suffix">
						<option value="{$field.suffix|escape:'html':'UTF-8'}" class="first">{$field.suffix|escape:'html':'UTF-8'}</option>
						<option value="%" class="percentage">%</option>
					</select>
				</div>
			</td>
		{/foreach}
		<td class="text-right last">
			<a href="#" class="resetImpacts" data-toggle="tooltip" title="{l s='Reset' mod='bulkcombinationsgenerator'}"><i class="icon-eraser"></i></a>
			<a href="#" class="action-icon removeRow" data-toggle="tooltip" title="{l s='Remove' mod='bulkcombinationsgenerator'}"><i class="icon-trash"></i></a>
		</td>
</tr>{/foreach}
{* since 2.0.1 *}
