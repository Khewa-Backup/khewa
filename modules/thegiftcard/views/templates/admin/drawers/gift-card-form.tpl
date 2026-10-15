{*
* 2023 - Keyrnel
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one shop.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
* If you have not received this module from us, thank you for contacting us.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author    Keyrnel
* @copyright 2023 - Keyrnel
* @license   commercial
* International Registered Trademark & Property of Keyrnel
*}

<div id="drawer" class="drawer">
    <div class="drawer-header">
        <h4 class="drawer-title">{l s='Add new gift card' mod='thegiftcard'}</h4>
        <button type="button" class="close" id="drawer-close">&times;</button>
    </div>
    <div class="drawer-body">
        {if isset($giftcard) && $giftcard->id && $template_vars && $amount_vars}
            <div id="block_templates" class="attributes"
                data-id-attribute-group="{$template_vars.id_attribute_group|intval}"
                data-rewrite-group-name="{$template_vars.rewrite_group_name|escape:'quotes':'UTF-8'}">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="header mb-3 mt-0">
                            <span class="title">{l s='Image' mod='thegiftcard'}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    {foreach from=$template_vars.attributes item=template}
                        {assign var=imageIds value="`$giftcard->id`-`$template.attribute_value`"}
                        {if !empty($template.legend)}
                            {assign var=imageTitle value=$template.legend|escape:'html':'UTF-8'}
                        {else}
                            {assign var=imageTitle value=$giftcard->name|escape:'html':'UTF-8'}
                        {/if}
                        <div class="col-xs-6 col-md-4 img_attribute">
                            <input type="radio" class="attribute_radio" name="template"
                                value="{$template.attribute_value|intval}" {if ($template.cover)} checked="checked" {/if} />
                            <div {if ($template.cover)}id="bigpic" {/if}
                                class="product-image-container {if ($template.cover)}selected{/if}"
                                data-id="{$template.attribute_value|intval}"
                                {if ($template.auto)}data-auto="{$template.auto|intval}" {/if}>
                                <img src="{$template.thumbnail|escape:'quotes':'UTF-8'}" alt="" class="imgm img-thumbnail" />
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
            <div id="block_amounts" class="attributes" data-id-attribute-group="{$amount_vars.id_attribute_group|intval}"
                data-rewrite-group-name="{$amount_vars.rewrite_group_name|escape:'quotes':'UTF-8'}">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="header mt-3 mb-3">
                            <span class="title">{l s='Amount' mod='thegiftcard'}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="content">
                            <div class="form-group">
                                <div class="form-content">
                                    <div class="input-group" style="width:210px;">
                                        <input type="hidden" name="amount" value="{$default_amount|intval}" />
                                        <span class="input-group-addon">{$currencySign|escape:'html':'UTF-8'}</span>
                                        <select name="amount_select">
                                            {foreach from=$amount_vars.attributes item=amount name=fo}
                                                <option value="{$amount.attribute_value|intval}"
                                                    {if $default_amount==$amount.attribute_value}selected="selected" {/if}>
                                                    {$amount.attribute_value_formatted|escape:'html':'UTF-8'}</option>
                                            {/foreach}
                                            {if $isCustomAmountFeatureActive}
                                                <option value="-1">{l s='Other amount' mod='thegiftcard'}</option>
                                            {/if}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            {if $isCustomAmountFeatureActive}
                                <div class="form-group" style="display:none">
                                    <div class="form-label">
                                        <label for="amount_input">
                                            {l s='Custom amount between' mod='thegiftcard'}
                                            {$custom_amount_from_formatted|escape:'html':'UTF-8'} {l s='and' mod='thegiftcard'}
                                            {$custom_amount_to_formatted|escape:'html':'UTF-8'} :
                                            <span
                                                style="display: block; font-size: 11px; font-style: italic; color: #525252; text-align: left;">{l s='Price range' mod='thegiftcard'}:
                                                {$pitch|escape:'html':'UTF-8'}</span>
                                        </label>
                                    </div>
                                    <div class="form-content">
                                        <div class="input-group" style="width:210px;">

                                            <input type="text" name="amount_input" value="" />
                                            <span class="input-group-addon">{$currencySign|escape:'html':'UTF-8'}</span>
                                        </div>
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
            {if $giftcard->text_fields|intval}
                <div id="block_customization">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="header mt-3 mb-3">
                                <span class="title">{l s='Customization' mod='thegiftcard'}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="content">
                                <div id="card_text_fields">
                                    {counter start=0 assign='customizationField'}
                                    {foreach from=$customizationFields item='field' name='customizationFields'}
                                        <div class="form-group">
                                            <div class="form-label">
                                                <label for="textField{$customizationField|intval}">
                                                    {if !empty($field.name)}
                                                        {$field.name|escape:'html':'UTF-8'}
                                                    {/if}
                                                </label>
                                            </div>
                                            <div class="form-content">
                                                <textarea name="textField{$field.id_customization_field|intval}"
                                                    class="form-control customization_block_input"
                                                    id="textField{$customizationField|intval}" rows="3" cols="20"></textarea>
                                            </div>
                                        </div>
                                        {counter}
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
            <div id="block_button">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="content">
                            <form id="buy_block" data-action="" method="post">
                                <input type="hidden" name="id_product" value="{$giftcard->id|intval}"
                                    id="product_page_product_id" />
                                <button type="button" class="btn btn-primary full-width mt-3" js-action="add-gift-card">
                                    {l s='Save' mod='thegiftcard'}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
    </div>
</div>

<script type="text/javascript">
    var pitch = {$pitch|intval};
    var custom_amount_from = {$custom_amount_from|intval};
    var custom_amount_to = {$custom_amount_to|intval};
    var invalidAmountMsg = "{l s='Please select a valid amount' mod='thegiftcard'}";

    function getProductAttribute() {
        var attributes = [];

        $('#block_templates input[type=radio]:checked, #block_amounts input[name="amount"]').each(function() {
            var attribute = new Object();
            attribute['id_attribute_group'] = $(this).closest('.attributes').attr('data-id-attribute-group');
            attribute['group_name'] = $(this).closest('.attributes').attr('data-rewrite-group-name');
            attribute['value'] = $(this).val();
            attributes.push(attribute);
        });

        return attributes;
    }

    function isAmountInList(amount) {
        if (isAmountInFixedList(amount) ||
            (amount >= custom_amount_from && amount <= custom_amount_to && amount % pitch == 0)
        ) {
            return true;
        }

        return false;
    }

    function isAmountInFixedList(amount) {
        var inList = false;

        $('#block_amounts select[name="amount_select"]').find('option').each(function() {
            if ($(this).val() == amount) {
                inList = true;
                return;
            }
        });

        return inList;
    }

    $(document).ready(function() {
        $(document).on({
            click: function() {
                var templateId = $(this).attr('data-id');

                $('.product-image-container').each(function() {
                    $(this).removeAttr('id').removeClass('selected');
                    if ('ontouchstart' in window) {
                        $(this).find('.view_larger').hide();
                    }
                    $('#block_templates input[type=radio]').prop('checked', false);

                });

                $(this).attr('id', 'bigpic').addClass('selected');

                if ('ontouchstart' in window) {
                    $(this).find('.view_larger').show();
                }

                $('#block_templates input[type=radio][value=' + templateId + ']').prop('checked',
                    true);

                var auto = $(this).attr('data-auto');
                if (typeof auto !== 'undefined' && auto !== false) {
                    $('#block_amounts select[name="amount_select"] option[value="' + auto + '"]')
                        .prop('selected', true);
                    $('#block_amounts select[name="amount_select"]').val(auto);
                    $('#block_amounts select[name="amount_select"]').trigger('change')
                }
            }
        }, '.product-image-container');

        $('#block_amounts select[name="amount_select"]').on('change', function() {
            $('#block_amounts input[name="amount_input"]').val('').closest('.form-group').hide();
            $('#block_amounts input[name="amount"]').val('');
            if ($(this).val() == -1) {
                $('#block_amounts input[name="amount_input"]').closest('.form-group').show();
            } else {
                $('#block_amounts input[name="amount"]').val($(this).val());
            }
        });

        $('#block_amounts input[name="amount_input"]').focusout(function() {
            var amount = $(this).val();

            $('#block_amounts input[name="amount"]').val(amount);

            if (!isAmountInList(amount)) {
                showErrorMessage(invalidAmountMsg);
            }
        });

        $('#block_button button[js-action="add-gift-card"]').on('click', function() {
            var customizationData = new Object();
            $('#card_text_fields .form-group').find('input[type=text], textarea').each(
                function() {
                    if (this.value) {
                        customizationData[$(this).attr('name')] = this.value;
                    }
                });

            var params = {
                attributes: getProductAttribute(),
                customizationData: customizationData,
                ajax: true,
                action: 'AddGiftCard'
            };

            $.ajax({
                type: 'POST',
                url: currentIndex,
                data: params,
                success: function(data) {
                    data = $.parseJSON(data);

                    if (!data.error) {
                        window.location.href = currentIndex + '&conf=3';
                    } else {
                        $.each(data.errors, function(key, value) {
                            showErrorMessage(value);
                        });
                    }
                },
                error: function(data) {
                    showErrorMessage("[TECHNICAL ERROR]");
                }
            });
        });
    });
</script>