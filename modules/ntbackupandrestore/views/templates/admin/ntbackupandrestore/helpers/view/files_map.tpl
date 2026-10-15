{*
* 2013-2024 2N Technologies
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to contact@2n-tech.com so we can send you a copy immediately.
*
* @author    2N Technologies <contact@2n-tech.com>
* @copyright 2013-2024 2N Technologies
* @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<div class="panel-heading">
    <i class="fas fa-sitemap"></i>
    &nbsp;{l s='Files map' mod='ntbackupandrestore'}
</div>

{if $light}
    <div class="light_version_error alert alert-info hint">
        <p>
            {l s='This feature is only available in the' mod='ntbackupandrestore'}
            <a href="{$link_full_version|escape:'htmlall':'UTF-8'}">
                {l s='full version of the module' mod='ntbackupandrestore'}
            </a>
            {l s='which makes it possible to display a file map of the directories and files of the backup.' mod='ntbackupandrestore'}
        </p>
    </div>
{else}
    {if !$scan_files_enable}
        <div class="alert alert-info hint">
            <p>
                {l s='The files map is disabled in all configurations.' mod='ntbackupandrestore'}
                {l s='You can enable it in the "Configuration" tab of the module.' mod='ntbackupandrestore'}
            </p>
        </div>
    {/if}

    <div id="scan_files_result">
        <div id="scan_files_list">
            <p class="no_scan">
                {l s='A backup need to be done for the files map to be displayed' mod='ntbackupandrestore'}
            </p>
            <p class="scan_done">
                {l s='File scan done on' mod='ntbackupandrestore'} <span id="last_scan_date">{$last_scan_date|escape:'html':'UTF-8'}</span>
                {l s='during backup with profile' mod='ntbackupandrestore'} <span id="last_scan_config">{$last_scan_config|escape:'html':'UTF-8'}</span>
            </p>
            <ul class="tree scan_done">
                <li class="tree-item is_dir">
                    <span class="tree-item-name" onclick="scanFiles('', this);">
                        <span>
                            <i class="fas fa-folder"></i>
                            <label>
                                <span class="name">{l s='Root' mod='ntbackupandrestore'}</span>
                                <span class="size_readable">[{$scan_root_readable_size|escape:'html':'UTF-8'}]</span>
                                <span class="hide size">{$scan_root_size|floatval}</span>
                            </label>
                        </span>
                    </span>
                </li>
            </ul>
        </div>
        <div id="scan_graph_block">
            <canvas id="scan_files_graph" height="400"></canvas>
        </div>
    </div>
{/if}