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

$(document).on('click', '#submit_email_test', (e) => {
    let cap_controller = getControllerName('email_test');
    let cap_controller_url = getControllerUrl('email_test');
    let email = $('#reminder_tests input').val();

    $.ajax({
        type: 'POST',
        url: cap_controller_url,
        dataType: 'html',
        data: {
            controller : cap_controller,
            action : 'updateEmailTest',
            email : email,
            ajax : true,
        },
        success : (data) => {
            $('#reminder_tests .ajax_return').hide();
            if (data == '0') {
                $('#reminder_tests .ajax_return.false').show();
            } else {
                $('#reminder_tests .ajax_return.true').show();
            }
        }
    });
})

