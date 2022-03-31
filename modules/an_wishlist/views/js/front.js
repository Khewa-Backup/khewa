/**
* 2019 Anvanto
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Anvanto (anvantoco@gmail.com)
*  @copyright  2019 anvanto.com

*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
(function ($, window, undefined) {
    'use strict';
    
    $(document).on('click', '.js-an_wishlist-addremove', function () {
        
        var self = this;

        toggleData($(self).data('url'), function (data) {
            toggleStatus(self, data);
            removeProduct(self, data);
            changeCount(self, data);
            showModalAuth(data);
        });
        
    });
    
    function changeCount(self, data)
    {
        $('.js-an_wishlist-nav-count').html(data.count);
        $(self).closest('.js-an_wishlist-container').find('.js-an_wishlist-product-count').html(data.countWishlists);
    }
    
    function toggleStatus(self, data)
    {
        if (data.status) {
            $(self).addClass('an_wishlist-in');
        } else {
            $(self).removeClass('an_wishlist-in');
        }
    }
    
    function removeProduct(self, data)
    {
        if ($('body').attr('id') == 'cart' || $('body').attr('id') == 'module-an_wishlist-list') {
            $(self).closest('.js-product-miniature').remove();
        }
    }
    
    function showModalAuth(data)
    {
        if (data.modal) {
            $.magnificPopup.open({
                items: {
                    src: data.modal,
                    type: 'inline'
                }
            });
        }
    }
    
    function toggleData(url, callback)
    {
        $.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
        }).done(function (data) {
            callback(data);
        });
    }

})(jQuery, window);