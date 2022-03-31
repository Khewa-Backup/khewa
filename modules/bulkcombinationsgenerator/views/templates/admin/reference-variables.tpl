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

<div class="variables-container inline-block">
	<div class="variables-list alert-info">
		<h4>{l s='You can leave this field empty, or prepare a pattern using variables' mod='bulkcombinationsgenerator'}:</h4>
		{foreach $reference_variables as $var => $description}
			{if empty($include_duplicate_variables) && ($var == '{orig_ref}' || $var == '{orig_ref_without_base}')}{continue}{/if}
			<div class="var-row">
				<span class="var-name">{$var|escape:'html':'UTF-8'}</span> - {$description|escape:'html':'UTF-8'}
			</div>
		{/foreach}
		<a href="{$info_links.documentation.url|escape:'html':'UTF-8'}#page=3" target="_blank" class="no-decoration more-info">
			<i class="icon-file-text"></i> {l s='More info' mod='bulkcombinationsgenerator'}
		</a>
		<a href="#" class="toggleVariables no-decoration">&times;</a>
	</div>
	<a href="#" class="icon-info-circle toggleVariables no-decoration"></a>
</div>
{* since 2.1.0 *}
