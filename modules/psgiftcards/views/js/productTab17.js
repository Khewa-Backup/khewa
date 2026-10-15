/**
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
 */
$(document).ready(function() {

    getIfProductIsTag(id_product);
    var switchGiftcardTag = new Vue({
        el: '#giftcardSwitchTag',
        delimiters: ["((","))"],
        data: {
            isTag : ''
        },
        methods: {
            updateTag: function (id_product, event) {
                controller_url = controller_url.replace(/\amp;/g,'');
                var isChecked = $("input[name=giftcard_tag]:checked").val();
                if (isChecked != this.isTag) {
                    $.ajax({
                        type: 'POST',
                        dataType: 'HTML',
                        url: controller_url,
                        data: {
                            ajax: true,
                            action: 'TagProduct',
                            id_product: id_product,
                            isChecked: isChecked,
                        },
                        success: function(data) {
                            if (data == 'tag') {
                                switchGiftcardTag.isTag = 1;
                                $('#form_step2_id_tax_rules_group').attr("disabled", true);
                                showSuccessMessage(successTag);
                            } else if (data == 'untag') {
                                switchGiftcardTag.isTag = 0;
                                showSuccessMessage(successUntag);
                            } else {
                                $("input[name=giftcard_tag][value=" + 0 + "]").prop('checked', true);
                                showErrorMessage(warningTag);
                                console.log('bouh');
                            }
                        },
                        error: function(err) {
                            showErrorMessage(errorTag);
                        }
                    });
                }
            },
        }
    });

    function getIfProductIsTag(id_product) {
        controller_url = controller_url.replace(/\amp;/g,'');
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: controller_url,
            data: {
                ajax: true,
                action: 'IsTagAsGiftcard',
                id_product: id_product
            },
            success: function(data) {
                if (data == 1) {
                    $('#form_step2_id_tax_rules_group').attr("disabled", true);
                }
                switchGiftcardTag.isTag = data;
            },
            error: function(err) {
                console.log(err);
            }
        });
    }
});

