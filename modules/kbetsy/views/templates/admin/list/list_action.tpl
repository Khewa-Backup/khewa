{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* @category  PrestaShop Module
* @author    knowband.com <support@knowband.com>
* @copyright 2017 Knowband
* @license   see file: LICENSE.txt
*
* Description
*
* Admin List Action tpl file
*}

{*
* Modified to use the target blank in case of the sync on profile level
* @modifier Pragya Maurya
* @date 13-06-2024
* PMJune2024 custom-profile-level-sync
*}
{if isset($should_blank) && $should_blank == 1}
<a href="{$href|escape:'html':'UTF-8'}" target="_blank" title="{$action|escape:'htmlall':'UTF-8'}" class="edit" {if isset($onclick) && $onclick != ""} onclick="{$onclick|escape:'htmlall':'UTF-8'}" {/if}>
    <i class="icon-{$icon|escape:'htmlall':'UTF-8'}"></i> {$action|escape:'htmlall':'UTF-8'}
</a>
{else}
<a href="{$href|escape:'html':'UTF-8'}" title="{$action|escape:'htmlall':'UTF-8'}" class="edit">
	<i class="icon-{$icon|escape:'htmlall':'UTF-8'}"></i> {$action|escape:'htmlall':'UTF-8'}
</a>
{/if}
