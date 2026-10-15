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

<div id="email_content" class="row clear">
    <div class="col-lg-12 col-xs-12">
        <label>{l s='Email content' mod='psgiftcards'}</label>
    </div>
    <div class="col-lg-10 col-xs-10">
        <div class="cap-lang-form">
            <textarea name="email_content_{$lang.id_lang|intval}"  class="cap-editor email_content {if isset($template_datas[$lang.id_lang]['email_content'])}has_content{/if}">
                {if isset($template_datas[$lang.id_lang]['email_content'])}
                    {$template_datas[$lang.id_lang]['email_content'] nofilter}
                {else}
                    <p>From {ldelim}buyer_name{rdelim} to {ldelim}recipient_name{rdelim}<br></p>
                    <p>{ldelim}buyer_message{rdelim} </p>
                    <p>Enjoy your {ldelim}discount_value{rdelim} until {ldelim}discount_validity{rdelim} with the
                    following code :</p>
                    <p>{ldelim}discount_code{rdelim}</p>
                    <p>We wish you an excellent shopping on {ldelim}shop_link{rdelim}</p>
                {/if}
            </textarea>
        </div>
    </div>
    <div class="col-lg-10 col-xs-10">
        <p>{l s='Add the following tags to customize your message' mod='psgiftcards'}</p>
    </div>
    <div class="col-lg-10 col-xs-10">
        {foreach from=$custom_content key=name item=content}
            <button class="email_content_custom" data-content="{$content}" data-type="content">
                <i class="material-icons">add_circle</i>
                {$name}
            </button>
        {/foreach}
    </div>
</div>
