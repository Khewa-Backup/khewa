/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @category  PrestaShop Module
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 knowband
 * @license   see file: LICENSE.txt
 */
//JS used on the product listing page
/**
 * To handle the JS of the custom details and modals shown while updating the product details
 * @modifier Pragya Maurya
 * @date 13-06-2024
 */ 
function show_ebay_listing_error(c)
{
    //$('.'+c).fancybox();
}

/**
 * Updated JS to fix the "fancybox is not a function" error
 * @modifier Himanshu Vishwakarma
 * @date 05-08-2025
 */
$(document).ready(function() {
    $('a[class*="etsy-error-"]').fancybox({
        type: 'inline',
        padding: 10
    });
});

/**
 * This function will be called once the user click on the "Update product Details" button on the product listing page
 * Responsible to fetch the product details and show the title and description(tinymce) of the product
 * @author Pragya Maurya
 * @date 10-06-2024
 * PMMay2024 etsy-custom-product-details enhancement
 */
function show_ebay_customs(profile_product_id)
{
    $('#modal_details').modal({ 'show': true});
    //Initialise the tinymce editor
    if (!tinymce.get('product_custom_description')) {
        tinySetup({
            selector: 'textarea[name="product_custom_description"]'
        });
    }
 
    $.ajax({
        url: action_get_custom_details,
        data: '&ajax=true&method=getCustomdetails&id_etsy_profile_products=' + profile_product_id,
        type: 'post',
        datatype: 'json',
        success: function (json)
        {
            if(json['profile_product_id'] != 0){
            $('#modal_details input[name="id_etsy_profile_product_custom"]').val(json['profile_product_id']);
            }
            $('#modal_details input[name="product_custom_title"').val(json['title']);

            setTimeout(function() {
            tinymce.get('product_custom_description').setContent(json['description']);
            } , 1000);
            $('#showcustomdetails').show();
            if(json['flag']){
                $('#modal_details input[name="product_custom_title"').attr('disabled', true);
                tinymce.activeEditor.getBody().setAttribute('contenteditable', false);

                $('#enableInputsCheckbox').prop('checked', true);
            }else{
                $('#modal_details input[name="product_custom_title"').attr('disabled', false);
                $('#enableInputsCheckbox').prop('checked', false);
              //  tinymce.activeEditor.setMode('design'); 
              tinymce.activeEditor.getBody().setAttribute('contenteditable', true);

            }
         
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert('Technical error occurred. Contact to support.');
            closeTemplateTranslationPopup();
        }
    });
}

/**
 * Save the details of the products in the database we have provided a checkbox as well in case if admin wants to use the default one
 * @author Pragya Maurya
 * @date 10-06-2024
 * PMMay2024 etsy-custom-product-details enhancement
 */
function saveCustomDetails(profile_product_id){
     var desc = tinymce.get('product_custom_description').getContent();
     var defaultprop = $('#enableInputsCheckbox').prop('checked');
    var formData = $('#modal_details input, #modal_details textarea, #modal_details input[name="id_etsy_profile_product_custom"]').serialize();
    
    formData += '&product_custom_description=' + encodeURIComponent(desc);
    formData += '&ajax=true&method=updatecustomdetails&id_etsy_profile_products=' + profile_product_id;
    formData += '&defaultdetails=' + (defaultprop ? '1' : '0');
    
    $.ajax({
        url: action_get_custom_details,
        data: formData,
        type: 'post',
        datatype: 'json',
        success: function (json)
        {
          
           tinymce.get('product_custom_description').setContent('');
           $('#modal_details').modal('hide');
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert('Technical error occurred. Contact to support.');
            closeTemplateTranslationPopup();
        }
    });

}
/**
 * Function responsible to close a pop up and clear the tinymce content
 * @author Pragya Maurya
 * @date 10-06-2024
 * PMMay2024 etsy-custom-product-details enhancement
 */
function closePopupTemplate()
{
    tinymce.get('product_custom_description').setContent('');
    $('#modal_details').modal('hide'); 
}


$(document).ready(function() {
/**
 * To enable and disabled the fields of the modal based on the checkbox
 * @author Pragya Maurya
 * @date 20-05-2024
 * PMMay2024 etsy-custom-product-details enhancement
 */
    $('#enableInputsCheckbox').change(function() {
        if ($(this).prop('checked')) {
            $('#modal_details input[name="product_custom_title"').attr('disabled', true);
            tinymce.activeEditor.getBody().setAttribute('contenteditable', false);
        } else {
            $('#modal_details input[name="product_custom_title"').attr('disabled', false); // Add disabled property
            tinymce.activeEditor.getBody().setAttribute('contenteditable', true);
        }
    });

});