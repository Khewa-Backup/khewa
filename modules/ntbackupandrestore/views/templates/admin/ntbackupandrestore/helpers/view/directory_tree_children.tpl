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

<ul class="tree">
    {foreach $children as $child}
        <li class="tree-item">
            <span class="tree-item-name {if $child.ignore || $child.always_ignore}tree-selected{/if}">
                <input type="checkbox" name="ignore_directories_{$id_ntbr_config|intval}" value="{$child.path|escape:'html':'UTF-8'}"
                    {if $child.ignore || $child.always_ignore}checked="checked"{/if}
                    {if !$child.always_ignore}onclick="$(this).parent().toggleClass('tree-selected');"{else}onclick="$(this).attr('checked', 'checked').prop('checked', true);"{/if}
                    {if $child.always_ignore}class="deactivate" readonly="readonly" disabled="disabled"{/if}/>
                <span onclick="getDirectoryChildren('{$child.path|escape:'html':'UTF-8'}', this);">
                    {if $child.is_dir}<i class="fas fa-folder"></i>{else}<i class="fas fa-file"></i>{/if}
                    <label class="tree-toggler">{$child.name|escape:'html':'UTF-8'}{if !$child.is_dir} <span class="size">({$child.size|escape:'html':'UTF-8'})</span>{/if}</label>
                </span>
            </span>
        </li>
    {/foreach}
</ul>