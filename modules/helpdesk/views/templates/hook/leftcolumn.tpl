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
{if $ps_ver >= 1}<link rel="stylesheet" type="text/css" href="{if $force_ssl == 1}{$base_dir_ssl|escape:'htmlall':'UTF-8'}{else}{$base_dir|escape:'htmlall':'UTF-8'}{/if}modules/helpdesk/views/css/fmmhelpdesk.css" />{/if}
<div id="help_desk_left" class="block{if $ps_ver >= 1} fmm_ps_17{/if}">
<h4 class="title_block"><a href="{$link->getModuleLink('helpdesk', 'helpdesk')|escape:'htmlall':'UTF-8'}" title="{l s='HelpDesk' mod='helpdesk'}">{l s='Help Desk' mod='helpdesk'}</a></h4>
{if empty($latest_tickets)}
<small>{l s='No tickets yet.' mod='helpdesk'}</small>
{else}
<div>
    <br/>
    <div class="block_content list-block" style="">
        <ul style="padding-left:3px;">
            {foreach from=$latest_tickets key=i item=thread}
                <li onclick="window.location.href='{$link->getModuleLink('helpdesk', 'helpdesk')|escape:'htmlall':'UTF-8'}?ticket_id={$thread.ticket_id|escape:'htmlall':'UTF-8'}&detail=1'" style="cursor: pointer;">{$thread.ticket_subject|escape:'htmlall':'UTF-8'}</li>
                {if $i >=2}{break}{/if}
            {/foreach}
        </ul>
        <a href="{$link->getModuleLink('helpdesk', 'helpdesk')|escape:'htmlall':'UTF-8'}" title="{l s='HelpDesk' mod='helpdesk'}">
        <input type="button" class="btn btn-default button button-small" value="{l s='View all' mod='helpdesk'}" style="padding: 3px;">
        </a>
    </div>
</div>
{/if}
<form id="searchbox" action="{$link->getModuleLink('helpdesk', 'helpdesk')|escape:'htmlall':'UTF-8'}" method="post" style="padding-top:15px">
    <div class="mediaSearchform">
        <input type="text" placeholder="{l s='Search Ticket' mod='helpdesk'}..." name="searchTxt" id="searchTxt" class="form-control" />
        <input type="hidden" name="action" id="action" value="search" />
        <input type="submit" class="btn btn-default button button-small" value="{l s='Search' mod='helpdesk'}" style="padding: 3px;margin-top: 5px;float:right">
    </div>
</form>
</div>