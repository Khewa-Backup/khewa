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

<div id="email_cta" class="row clear cap-email-cta">
    <div class="col-lg-12 col-xs-12">
        <label>{l s='Call to action text' mod='psgiftcards'}</label>
    </div>
    <div class="col-lg-10 col-xs-10">
        <p>{l s='The CTA will redirect to your shop' mod='psgiftcards'} <br />
        {l s='If nothing is written, no CTA will be shown.' mod='psgiftcards'}<br />
        {l s='This field will not have impact on the PDF' mod='psgiftcards'}
        </p>
    </div>
    <div class="col-lg-10 col-xs-10">
        <input type="text" autocomplete="off" name="email_cta" placeholder="(optional)"
        value="{if !empty($template_datas[$lang.id_lang]['email_cta'])}{$template_datas[$lang.id_lang]['email_cta']}{/if}"
        />
        <span class="caract-count">
            <span class="amount">{if isset($template_datas[$lang.id_lang]['email_cta'])}{$template_datas[$lang.id_lang]['email_cta']|strlen}{else}0{/if}</span>/25
            {l s='characters' mod='psgiftcards'}
        </span>
    </div>
</div>
