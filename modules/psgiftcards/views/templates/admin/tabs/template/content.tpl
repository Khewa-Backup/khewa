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

<div id="cap_content_conf" class="cap_content_appearance_conf col-lg-12">
    <div class="inner-template-section form-control-label" data-selected='0'>
        {l s='Text & Content' mod='psgiftcards'}
        <i class="material-icons">keyboard_arrow_down</i>
    </div>

    <div class="cap_content_conf_elems display_none">
        <div class="row clear">
            <div class="col-lg-12">
                <label>{l s='Language to configure' mod='psgiftcards'}</label>
            </div>
            <div class="col-lg-7">
                <select name="cap-email-language" class="form-control">
                    {foreach from=$languages item=lang}
                        <option value="{$lang.id_lang}" {if $lang.id_lang == $employeeLangId}selected{/if}>{$lang.name}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        {* For each lang, add a form *}
        {foreach from=$languages key=k item=lang}
            <section class="content_by_lang lang_{$lang.id_lang|intval} {if $lang.id_lang != $employeeLangId}hide{/if}">
                <input type="hidden" name="id_lang" value="{$lang.id_lang|intval}"/>

                {* Email subject *}
                {include file="./content_elems/email_subject.tpl"}

                {* CKEDITOR Content *}
                {include file="./content_elems/email_content.tpl"}

                {* CKEDITOR Discount *}
                {include file="./content_elems/email_discount.tpl"}

                {* Call to action *}
                {include file="./content_elems/email_cta.tpl"}

                {* CKEDITOR Unsubscribe *}
                {include file="./content_elems/email_unsubscribe.tpl"}
            </section>
        {/foreach}
    </div>
</div>
