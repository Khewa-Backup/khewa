/**
* 2007-2022 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/
function addCustomerLogin(fieldsHtml) {
    var chDiv = $('<div id="container-customer"></div>');
    chDiv.append(fieldsHtml);


    $('#container-customer').before(chDiv);
}
$(document).ready(function () {


    $("#search_id").keyup(function () {
        var search_n_val = $('#search_id').val();
        var url_module = $('#base_url').val();
        var version = $('#version').val();
        var check;
        $(".userlist").remove();


        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: url_module + '&name=' + search_n_val,


            //data: "search_n_val="+search_n_val,
            success: function (data) {
                //alert(data);


                var array = [];


                if (data != null) {


                    $.each(data, function (index, value) {
                        var id_name = value['id_customer'];

                        // var gh="index.php/?controller=AdminQuickLoginStats&customer_id={id_name|escape:'htmlall':'UTF-8'}";
                        var fname = value['firstname'];
                        var lname = value['lastname'];
                        var email = value['email'];
                        if (version >= '1.7.0.0') {
                            $("#gap").after("<ul class = userlist><li style="+"margin-left:-116px;"+"><i class=icon-user></i><a class='hrefclick1'>"
                                + fname + " " + lname + " <a><a class='hrefclick' href="+"#href"+"><i class='icon-inbox'></i>" + email + "</a>"
                                + "</li><li class= divider ><input type='hidden' class='hrefclick'</li></ul>");
                        } else {
                            $("#gap").after("<ul class = userlist><li style="+"margin-left:-1206px;"+"><i class = 'fa fa-user' ></i> <a class='hrefclick1'>"
                                + fname + " " + lname + " <a><a class='hrefclick' href="+"#href"+"><i class='icon-inbox'></i>" + email + "</a>"
                                + "</li><li class= divider ><input type='hidden' class='hrefclick'</li></ul>");
                        }
                        $('.hrefclick').click(function() {
                           $('#search_id').val($(this).text());
                           $('.userlist').hide();
                                $('#cust_id').val();
                        });
                    });
                } else {

                }
            },

        });

    });

});
