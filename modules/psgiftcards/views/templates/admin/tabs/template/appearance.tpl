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

<div id="cap_appearance_conf" class="cap_content_appearance_conf col-lg-12 selected">
    <div class="inner-template-section form-control-label" data-selected='1'>
        {l s='Appearance' mod='psgiftcards'}
        <i class="material-icons">keyboard_arrow_up</i>
    </div>

    {* Model appearance *}
    <div class="cap_content_conf_elems">

        {* 2x Color pickers *}
        <div class="row clear">
            <div id="primary_color" class="col-lg-4 col-xs-4">
                <div class="col-lg-12 col-xs-12">
                    <label>{l s='Primary color' mod='psgiftcards'}</label>
                </div>
                <div class="col-lg-12 col-xs-12">
                    <div class="ps_colorpicker1"></div>
                </div>
                <input type="hidden" class="data_input" name="primary_color"
                    value="{if isset($template_appearance['primary_color'])}{$template_appearance['primary_color']}{else}#00b9dc{/if}"
                />
            </div>
            <div id="secondary_color" class="col-lg-4 col-xs-4">
                <div class="col-lg-12 col-xs-12">
                    <label>{l s='Secondary color' mod='psgiftcards'}</label>
                </div>
                <div class="col-lg-12 col-xs-12">
                    <div class="ps_colorpicker2"></div>
                </div>
                <input type="hidden" class="data_input" name="secondary_color"
                    value="{if isset($template_appearance['secondary_color'])}{$template_appearance['secondary_color']}{else}#D78F00{/if}"
                />

            </div>
        </div>
        <div class="col-lg-10 col-xs-10">
            <p>{l s='This field will not have impact on the PDF' mod='psgiftcards'}</p>
        </div>

        <div class="row clear">
            <div class="col-lg-12 col-xs-12">
                <label>{l s='Email template' mod='psgiftcards'}</label>
            </div>
            <div class="col-lg-12 col-xs-12 customradios">
                <div class="col-lg-4 col-xs-4">
                    <label for="model_name_sendy" class="col-lg-12 col-xs-12 customradiodesign btn-radio {if (isset($template) && $template == 'sendy') || !isset($template)}selected{/if}">
                        <input type="radio" name="model_name" id="model_name_sendy" value="sendy"
                            {if (isset($template) && $template == 'sendy') || !isset($template)}checked{/if}
                        />
                        <label for="model_name_sendy">{l s='Sendy' mod='psgiftcards'}</label>
                    </label>
                </div>
                <div class="col-lg-4 col-xs-4">
                    <label for="model_name_boxy" class="col-lg-12 col-xs-12 customradiodesign btn-radio  {if isset($template) && $template == 'boxy'}selected{/if}">
                        <input type="radio" name="model_name" id="model_name_boxy" value="boxy"
                            {if isset($template) && $template == 'boxy'}checked{/if}
                        />
                        <label for="model_name_boxy">{l s='Boxy' mod='psgiftcards'}</label>
                    </label>
                </div>
                <div class="col-lg-4 col-xs-4">
                    <label for="model_name_puffy" class="col-lg-12 col-xs-12 customradiodesign btn-radio  {if isset($template) && $template == 'puffy'}selected{/if}">
                        <input type="radio" name="model_name" id="model_name_puffy" value="puffy"
                            {if isset($template) && $template == 'puffy'}checked{/if}
                        />
                        <label for="model_name_puffy">{l s='Puffy' mod='psgiftcards'}</label>
                    </label>
                </div>
            </div>
        </div>

    </div>
</div>
