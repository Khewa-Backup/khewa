{*
 * 2020 ZH Media
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

<a href="{$href|escape:'html':'UTF-8'}"
        {foreach from=$attributes key=attributeName item=attributeValue}
          {$attributeName|escape:'html':'UTF-8'}="{$attributeValue|escape:'html':'UTF-8'}"
        {/foreach}
>
  {$text nofilter}
</a>
