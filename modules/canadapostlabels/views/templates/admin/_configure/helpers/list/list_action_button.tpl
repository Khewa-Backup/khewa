{*
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 *
 *}
<a href="{$href|escape:'html':'UTF-8'}" {if isset($target) && $target}target="{$target|escape:'html':'UTF-8'}"{/if} {if isset($confirm)} onclick="if (confirm('{$confirm|escape:'html':'UTF-8'}')){ldelim}return true;{rdelim}else{ldelim}event.stopPropagation(); event.preventDefault();{rdelim};"{/if} title="{$action|escape:'html':'UTF-8'}"{if isset($name)} name="{$name|escape:'html':'UTF-8'}"{/if} class="default {if isset($class)}{$class|escape:'html':'UTF-8'}{/if}">
    <i class="icon-{$icon|escape:'html':'UTF-8'}"></i> {$action|escape:'html':'UTF-8'}
</a>
