/*
* 2007-2017 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$(document).ready(function() {
    var owl = $("#wp_footer_wrapper"); 
        owl.on({
    'initialized.owl.carousel': function () {
         owl.find('.owl-item').show();
         owl.removeClass('loader');
         toggleArrows();
    }
}).owlCarousel({
 responsive:{
        0:{
            items: 1,
            slideBy : 1            
        },
        400:{
            items: 2,
            slideBy : 2            
        },            
        768:{
            items: 4,
            slideBy : 4
        },
        992:{
           items: wpfooterlogos_items,
            slideBy : wpfooterlogos_scroll_items
        }
    },
        margin: 20, 
        navSpeed: wpfooterlogos_speed,
        autoplaySpeed: wpfooterlogos_speed,
        autoplay: wpfooterlogos_auto,
        autoplayHoverPause: wpfooterlogos_pause_hover,
        loop: wpfooterlogos_loop,
        nav: true,
        dots: false,
        navText: ['<i class="material-icons">&#xE5CB;</i>','<i class="material-icons">&#xE5CC;</i>'],
        responsiveBaseElement: "#header .container"
    });
    
    // disable buttons
    function toggleArrows(){ 
                if($(owl).find(".owl-item").last().hasClass('active') && 
                     $(owl).find(".owl-item.active").index() == $(owl).find(".owl-item").first().index()){
                        $(owl).find('.owl-nav .owl-next').addClass("disabled");
                        $(owl).find('.owl-nav .owl-prev').addClass("disabled"); 
                }
                //disable next
                else if($(owl).find(".owl-item").last().hasClass('active')){
                        $(owl).find('.owl-nav .owl-next').addClass("disabled");
                        $(owl).find('.owl-nav .owl-prev').removeClass("disabled"); 
                }
                //disable previus
                else if($(owl).find(".owl-item.active").index() == $(owl).find(".owl-item").first().index()) {
                        $(owl).find('.owl-nav .owl-next').removeClass("disabled"); 
                        $(owl).find('.owl-nav .owl-prev').addClass("disabled");
                }
                else{
                        $(owl).find('.owl-nav .owl-next,.owl-nav .owl-prev').removeClass("disabled");  
                }
        }

    // first load disabled left arrow
    $(owl).find('.owl-nav .owl-prev').addClass("disabled");
    // change arrows status after slide
    $(owl).on('translated.owl.carousel', function (event) { toggleArrows(); });
});