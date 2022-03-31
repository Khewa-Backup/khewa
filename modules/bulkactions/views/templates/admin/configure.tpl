{*
* 2007-2019 Amazzing
*
* NOTICE OF LICENSE
*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright 2007-2019 Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="panel ba">
	<p>
		<span class="confirmation">{l s='Module is ready to use' mod='bulkactions'} <i class="icon-check"></i></span>
		{l s='No any additional setup is required' mod='bulkactions'}</p>
	<p>
		{l s='Bulk action tools are available for [1]products[/1], [1]combinations[/1], [1]categories[/1] and [1]customers[/1]' mod='bulkactions' tags=['<b>']}
	</p>
	<p class="info">
		<a href="{$info_links.documentation|escape:'html':'UTF-8'}" target="_blank">
			<i class="icon-file-text"></i> {l s='Documentation' mod='bulkactions'}
		</a>
		<a href="{$info_links.changelog|escape:'html':'UTF-8'}" target="_blank">
			<i class="icon-code-fork"></i> {l s='Changelog' mod='bulkactions'}
		</a>
		<a href="{$info_links.contact|escape:'html':'UTF-8'}" target="_blank">
			<i class="icon-envelope"></i> {l s='Contact us' mod='bulkactions'}
		</a>
		<a href="{$info_links.modules|escape:'html':'UTF-8'}" target="_blank">
			<i class="icon-download"></i> {l s='Our modules' mod='bulkactions'}
		</a>
	</p>
</div>
{* since 1.1.0 *}
