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
        <li class="tree-item {if $child.is_dir}is_dir{else}is_file{/if} {if $child.is_active}active{/if}">
            {if $child.is_dir}<i class="far fa-minus-square tree-toggler"></i>{/if}
            <span class="tree-item-name" onclick="scanFiles('{$child.path|escape:'html':'UTF-8'}', this);">
                <span>
                    {if $child.is_dir}<i class="fas fa-folder"></i>{else}<i class="fas fa-file"></i>{/if}
                    <label>
                        <span class="name">{$child.name|escape:'html':'UTF-8'}</span>
                        <span class="size_readable">[{$child.readable_size|escape:'html':'UTF-8'}]</span>
                        <span class="hide size">{$child.size|floatval}</span>
                    </label>
                </span>
            </span>
            {*{if $child.children && count($child.children)}
                {include file="./scan_tree_children.tpl" children=$child.children}
            {/if}*}
            <i class="fas fa-times" onclick="deleteScanFiles('{$child.path|escape:'html':'UTF-8'}', this);"></i>
        </li>
    {/foreach}
</ul>