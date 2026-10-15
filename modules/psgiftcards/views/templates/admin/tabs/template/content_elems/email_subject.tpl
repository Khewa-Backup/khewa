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

<div class="subject row clear">
    <div class="col-lg-12 col-xs-12">
        <label>{l s='Email subject' mod='psgiftcards'}</label>
    </div>
    <div id="email_subject" class="col-lg-10 col-xs-10">
        <input type="text" autocomplete="off" class="form-control cap-lang-form" name="email_subject"  placeholder="{l s='Subject' mod='psgiftcards'}"
            value="{if isset($template_datas[$lang.id_lang]['email_subject'])}{$template_datas[$lang.id_lang]['email_subject']}{/if}"
        />
        <span class="caract-count">
            <span class="amount">{if isset($template_datas[$lang.id_lang]['email_subject'])}{$template_datas[$lang.id_lang]['email_subject']|strlen}{else}0{/if}</span>/100
            {l s='characters' mod='psgiftcards'}
        </span>
    </div>
    <div class="col-lg-10 col-xs-10">
        <p>{l s='Fill the subject to enable this lang.' mod='psgiftcards'}</p>
        <p>{l s='This field will not have impact on the PDF' mod='psgiftcards'}</p>
    </div>
    <div class="subject-tags col-lg-10 col-xs-10">
        {foreach from=$custom_content key=name item=content}
            {if $content == '{first_name}' || $content == '{last_name}' || $content == '{gender}'}
            <button class="email_subject_custom" data-content="{$content}" data-type="subject">
                <i class="material-icons">add_circle</i>
                {$name}
            </button>
            {/if}
        {/foreach}
    </div>
</div>
