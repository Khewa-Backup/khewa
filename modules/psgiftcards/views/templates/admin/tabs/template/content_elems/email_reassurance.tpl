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

<div id="reassurance" class="row clear cap-email-reassurance">
    <div class="col-lg-12 col-xs-12">
        <label>{l s='Reassurance elements' mod='psgiftcards'}</label>
    </div>
    <div class="reassurance_section col-lg-12 col-xs-12">
        <div class="reassurance_selectimg col-lg-3 col-xs-3" data-id="1">
            <div class="reassurance_block">
                <i class="material-icons arrow">keyboard_arrow_down</i>
            </div>
            <div class="col-lg-12 col-xs-12 reassurance_1">

                <input type="hidden" name="email_reassurance_img1"
                value="{if !empty($template_datas[$lang.id_lang]['email_reassurance_img1'])}{$template_datas[$lang.id_lang]['email_reassurance_img1']}{/if}"
                />
            </div>
        </div>
        <div class="col-lg-7 col-xs-7">
            <input type="text" autocomplete="off" class="form-control cap-lang-form" name="email_reassurance_text1" data-reassurance="1"
                value="{if isset($template_datas[$lang.id_lang]['email_reassurance_text1'])}{$template_datas[$lang.id_lang]['email_reassurance_text1']}{/if}"
            />
            <span class="caract-count">
                <span class="amount">{if isset($template_datas[$lang.id_lang]['email_reassurance_text1'])}{$template_datas[$lang.id_lang]['email_reassurance_text1']|strlen}{else}0{/if}</span>/100
                {l s='characters' mod='psgiftcards'}
            </span>
        </div>
    </div>
    <div class="reassurance_section col-lg-12 col-xs-12">
        <div class="reassurance_selectimg col-lg-3 col-xs-3" data-id="2">
            <div class="reassurance_block">
                <i class="material-icons arrow">keyboard_arrow_down</i>
            </div>
            <div class="col-lg-12 col-xs-12 reassurance_2">

                <input type="hidden" name="email_reassurance_img2"
                value="{if !empty($template_datas[$lang.id_lang]['email_reassurance_img2'])}{$template_datas[$lang.id_lang]['email_reassurance_img2']}{/if}"
                />
            </div>
        </div>
        <div class="col-lg-7 col-xs-7">
            <input type="text" autocomplete="off" class="form-control cap-lang-form" name="email_reassurance_text2" data-reassurance="2"
                value="{if isset($template_datas[$lang.id_lang]['email_reassurance_text2'])}{$template_datas[$lang.id_lang]['email_reassurance_text2']}{/if}"
            />
            <span class="caract-count">
                <span class="amount">{if isset($template_datas[$lang.id_lang]['email_reassurance_text2'])}{$template_datas[$lang.id_lang]['email_reassurance_text2']|strlen}{else}0{/if}</span>/100
                {l s='characters' mod='psgiftcards'}
            </span>
        </div>
    </div>
    <div class="reassurance_section col-lg-12 col-xs-12">
        <div class="reassurance_selectimg col-lg-3 col-xs-3" data-id="3">
            <div class="reassurance_block">
                <i class="material-icons arrow">keyboard_arrow_down</i>
            </div>
            <div class="col-lg-12 col-xs-12 reassurance_3">

                <input type="hidden" name="email_reassurance_img3"
                value="{if !empty($template_datas[$lang.id_lang]['email_reassurance_img3'])}{$template_datas[$lang.id_lang]['email_reassurance_img3']}{/if}"
                />
            </div>
        </div>
        <div class="col-lg-7 col-xs-7">
            <input type="text" autocomplete="off" class="form-control cap-lang-form" name="email_reassurance_text3" data-reassurance="3"
                value="{if isset($template_datas[$lang.id_lang]['email_reassurance_text3'])}{$template_datas[$lang.id_lang]['email_reassurance_text3']}{/if}"
            />
            <span class="caract-count">
                <span class="amount">{if isset($template_datas[$lang.id_lang]['email_reassurance_text3'])}{$template_datas[$lang.id_lang]['email_reassurance_text3']|strlen}{else}0{/if}</span>/100
                {l s='characters' mod='psgiftcards'}
            </span>
        </div>
    </div>
</div>
