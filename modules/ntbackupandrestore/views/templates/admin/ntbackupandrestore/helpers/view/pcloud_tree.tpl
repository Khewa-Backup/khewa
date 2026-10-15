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

<ul id="pcloud_dir_{$config_id|intval}" class="pcloud_tree">
    <li class="level-{$level|intval}">
        <span>
            <input type="radio" class="pcloud_dir" name="pcloud_dir_{$config_id|intval}" value="0" {if $pcloud_dir_id == 0}checked="checked"{/if} id="0_{$config_id|intval}"/>
            <input type="hidden" name="pcloud_path_{$config_id|intval}" value="{$parent_path|escape:'html':'UTF-8'}"/>
            <label for="0_{$config_id|intval}">{$parent_path|escape:'html':'UTF-8'}</label>
            <i class="far fa-plus-square" onclick="getPcloudTreeChildren('0', '{$level|intval}', '/', this);"></i>
        </span>
    </li>
</ul>