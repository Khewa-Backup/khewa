{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

<div id="email_unsubscribe" class="row clear">
    <div class="col-lg-12 col-xs-12">
        <label>{l s='Footer' mod='psgiftcards'}</label>
    </div>
    <div class="col-lg-10 col-xs-10">
        <div class="cap-lang-form">
            <textarea name="email_unsubscribe_{$lang.id_lang|intval}" class="cap-editor email_unsubscribe {if isset($template_datas[$lang.id_lang]['email_unsubscribe'])}has_content{/if}">
                {if isset($template_datas[$lang.id_lang]['email_unsubscribe'])}
                    {$template_datas[$lang.id_lang]['email_unsubscribe']}
                {else}
                    <p>To take advantage of your gift card, select one or several products of your choice on the website <a href=" {ldelim}$site_url{rdelim}" target="_blank" style="color: #414a56"> {ldelim}site_url{rdelim}</a> then add your code in the dedicated field in your cart before ending your purchase. This gift card can be used in several times : if the balance is positive, you will receive an email with the new code and the remaining amount.</p>
                {/if}
            </textarea>
        </div>
    </div>
    <div class="col-lg-10 col-xs-10">
        <p>{l s='Add the following tags to customize your message' mod='psgiftcards'}</p>
    </div>
    <div class="col-lg-10 col-xs-10">
        {foreach from=$unsubscribe_content key=name item=content}
            <button class="email_content_custom" data-content="{$content}" data-type="unsubscribe">
                <i class="material-icons">add_circle</i>
                {$name}
            </button>
        {/foreach}
    </div>
</div>
