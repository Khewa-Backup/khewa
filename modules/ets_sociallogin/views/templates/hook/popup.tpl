{*
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*}
<!--Module: ets_socicallogin -->
{if isset($content) && $content}
<div class="ets_solo_popup">
    <span class="ets_solo_popup_close_overlay"></span>
    <div class="ets_solo_popup_content_wrap">
        <span class="ets_solo_popup_close">{l s='Close' mod='ets_sociallogin'}</span>
        <div class="ets_solo_popup_wrappper">
            <h3 class="ets_solo_popup_title">{$title|escape:'html':'UTF-8'}</h3>
            <div class="ets_solo_popup_content">
                {$content nofilter}
            </div>
        </div>
    </div>
</div>
{/if}
<!--/Module: ets_socicallogin -->