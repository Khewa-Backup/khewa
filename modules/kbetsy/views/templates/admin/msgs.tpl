{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer tohttp://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* @category  PrestaShop Module
* @author    knowband.com <support@knowband.com>
* @copyright 2018 Knowband
* @license   see file: LICENSE.txt
*
* Description
*
* Admin Header tabs file
*}
<div class="bootstrap">
    <div class="alert {$type|escape:'htmlall':'UTF-8'}" style="display:block;">
        {if $KbMessageLink != ''}
            <a href="{$KbMessageLink}">{$message|strip_tags:false}</a>  {*variable contains URL, can't escape*}
        {else}
            {$message|strip_tags:false} {*Variable contains HTML, can't escape*}
        {/if}
    </div>
</div>