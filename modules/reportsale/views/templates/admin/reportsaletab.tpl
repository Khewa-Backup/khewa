{*
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@buy-addons.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Buy-addons <contact@buy-addons.com>
*  @copyright  2007-2021 Buy-addons
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if $taskbar=="cronjob"}
	{if $bachecknoticron != 1}
		<div class='alert alert-danger'>
			<form method='post' enctype='multipart/form-data' >
		    {l s='You need set up cron job in your hosting with command ' mod='reportsale'}<br />
		        <strong>{$linkcronj|escape:'htmlall':'UTF-8'}</strong><br />
		        <button type='submit' class='btn btn-default' name='submit_checkcronjob' value='1'>{l s='Yes, I did' mod='reportsale'}<br /></button>
			</form>
		</div>
	{/if}
{/if}
<div>
	<ul class="nav nav-tabs bareporttab">
	    <li class="{if $taskbar=="basic"}active{/if}">
	        <a href="{$report_module|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&task=basic">{l s='Basic' mod='reportsale'}</a>
	    </li>
	    <li class="{if $taskbar=="taxes"}active{/if}">
	        <a href="{$report_module|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&task=taxes">{l s='Taxes' mod='reportsale'}</a>
	    </li>
	    <li class="{if $taskbar=="revenue"}active{/if}">
	        <a href="{$report_module|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&task=revenue">{l s='Revenue' mod='reportsale'}</a>
	    </li>
	    <li class="{if $taskbar=="all"}active{/if}">
	        <a href="{$report_module|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&task=all">{l s='All' mod='reportsale'}</a>
	    </li>
	    <li class="{if $taskbar=="product"}active{/if}">
	        <a href="{$report_module|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&task=product">{l s='Product' mod='reportsale'}</a>
	    </li>
	    <li class="{if $taskbar=="manufacturers"}active{/if}">
	        <a href="{$report_module|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&task=manufacturers">{l s='Manufacturers' mod='reportsale'}</a>
	    </li>
	    <li class="{if $taskbar=="supplier"}active{/if}">
	        <a href="{$report_module|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&task=supplier">{l s='Supplier' mod='reportsale'}</a>
	    </li>
	    <li class="{if $taskbar=="category"}active{/if}">
	        <a href="{$report_module|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&task=category">{l s='Category' mod='reportsale'}</a>
	    </li>
	    <li class="{if $taskbar=="client"}active{/if}">
	        <a href="{$report_module|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&task=client">{l s='Client' mod='reportsale'}</a>
	    </li>
	    <li class="{if $taskbar=="creditslips"}active{/if}">
	        <a href="{$report_module|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&task=creditslips">{l s='Credit Slips' mod='reportsale'}</a>
	    </li>
	    <li class="{if $taskbar=="cronjob"}active{/if}">
	        <a href="{$report_module|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&configure={$configure|escape:'htmlall':'UTF-8'}&task=cronjob">{l s='Cron Job' mod='reportsale'}</a>
	    </li>
	</ul>
</div>