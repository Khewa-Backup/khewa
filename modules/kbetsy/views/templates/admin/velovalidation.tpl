{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* @category  PrestaShop Module
* @author    knowband.com <support@knowband.com>
* @copyright 2017 Knowband
* @license   see file: LICENSE.txt
*
* Description
*
* Admin Velovalidation tpl file
*}
<script>
    velovalidation.setErrorLanguage({
        empty_fname: "{l s='Please enter First name.' mod='kbetsy'}",
        empty_mname: "{l s='Please enter middle name.' mod='kbetsy'}",
        only_alphabet: "{l s='Only alphabets are allowed.' mod='kbetsy'}",
        empty_lname: "{l s='Please enter Last name.' mod='kbetsy'}",
        alphanumeric: "{l s='Field should be alphanumeric.' mod='kbetsy'}",
        empty_pass: "{l s='Please enter Password.' mod='kbetsy'}",
        specialchar_pass: "{l s='Password should contain atleast 1 special character.' mod='kbetsy'}",
        alphabets_pass: "{l s='Password should contain alphabets.' mod='kbetsy'}",
        capital_alphabets_pass: "{l s='Password should contain atleast 1 capital letter.' mod='kbetsy'}",
        small_alphabets_pass: "{l s='Password should contain atleast 1 small letter.' mod='kbetsy'}",
        digit_pass: "{l s='Password should contain atleast 1 digit.' mod='kbetsy'}",
        empty_field: "{l s='Field can not be empty.' mod='kbetsy'}",
        number_field: "{l s='You can enter only numbers.' mod='kbetsy'}",
        positive_number: "{l s='Number should be greater than 0.' mod='kbetsy'}",
        empty_email: "{l s='Please enter Email.' mod='kbetsy'}",
        validate_email: "{l s='Please enter a valid Email.' mod='kbetsy'}",
        empty_country: "{l s='Please enter country name.' mod='kbetsy'}",
        empty_city: "{l s='Please enter city name.' mod='kbetsy'}",
        empty_state: "{l s='Please enter state name.' mod='kbetsy'}",
        empty_proname: "{l s='Please enter product name.' mod='kbetsy'}",
        empty_catname: "{l s='Please enter category name.' mod='kbetsy'}",
        empty_zip: "{l s='Please enter zip code.' mod='kbetsy'}",
        empty_username: "{l s='Please enter Username.' mod='kbetsy'}",
        invalid_date: "{l s='Invalid date format.' mod='kbetsy'}",
        invalid_sku: "{l s='Invalid SKU format.' mod='kbetsy'}",
        empty_sku: "{l s='Please enter SKU.' mod='kbetsy'}",
        validate_range: "{l s='Number is not in the valid range.' mod='kbetsy'}",
        empty_address: "{l s='Please enter address.' mod='kbetsy'}",
        empty_company: "{l s='Please enter company name.' mod='kbetsy'}",
        invalid_phone: "{l s='Phone number is invalid.' mod='kbetsy'}",
        empty_phone: "{l s='Please enter phone number.' mod='kbetsy'}",
        empty_brand: "{l s='Please enter brand name.' mod='kbetsy'}",
        empty_shipment: "{l s='Please enter Shimpment.' mod='kbetsy'}",
        invalid_ip: "{l s='Invalid IP format.' mod='kbetsy'}",
        invalid_url: "{l s='Invalid URL format.' mod='kbetsy'}",
        empty_url: "{l s='Please enter URL.' mod='kbetsy'}",
        empty_amount: "{l s='Amount can not be empty.' mod='kbetsy'}",
        valid_amount: "{l s='Amount should be numeric.' mod='kbetsy'}",
        specialchar_zip: "{l s='Zip should not have special characters.' mod='kbetsy'}",
        specialchar_sku: "{l s='SKU should not have special characters.' mod='kbetsy'}",
        valid_percentage: "{l s='Percentage should be in number.' mod='kbetsy'}",
        between_percentage: "{l s='Percentage should be between 0 and 100.' mod='kbetsy'}",
        specialchar_size: "{l s='Size should not have special characters.' mod='kbetsy'}",
        specialchar_upc: "{l s='UPC should not have special characters.' mod='kbetsy'}",
        specialchar_ean: "{l s='EAN should not have special characters.' mod='kbetsy'}",
        specialchar_bar: "{l s='Barcode should not have special characters.' mod='kbetsy'}",
        positive_amount: "{l s='Amount should be positive.' mod='kbetsy'}",
        invalid_color: "{l s='Color is not valid.' mod='kbetsy'}",
        specialchar: "{l s='Special characters are not allowed.' mod='kbetsy'}",
        script: "{l s='Script tags are not allowed.' mod='kbetsy'}",
        style: "{l s='Style tags are not allowed.' mod='kbetsy'}",
        iframe: "{l s='Iframe tags are not allowed.' mod='kbetsy'}",
        not_image: "{l s='Uploaded file is not an image' mod='kbetsy'}",
        image_size: "{l s='Uploaded file size must be less than #.' mod='kbetsy'}",
    });
    var lang_err = "{l s='You can not select default language in sync languages.' mod='kbetsy'}";
    var amount_err = "{l s='Please enter valid amount (e.g. 3.50).' mod='kbetsy'}";
    var amount_max_err = "{l s='Cost must be between 0.00 to 20000.00' mod='kbetsy'}";
    var range_err = "{l s='Please enter valid number between 0-50 (0,1,2,3,4,5,6,7,8,9,10,15,20,25,30,35,40,45,50).' mod='kbetsy'}";
    var range_max_err = "{l s='Please enter valid number between 0-10.' mod='kbetsy'}";
    var min_proc_err = "{l s='Please enter minimum number of processing days.' mod='kbetsy'}";
    var max_proc_err = "{l s='Please enter maximum number of processing days.' mod='kbetsy'}";
    var process_day_err = "{l s='Minimum Processing Days cannot be greater than to Maximum Processing Days.' mod='kbetsy'}";
    var country_err = "{l s='Please choose Destination Country.' mod='kbetsy'}";
    var region_err = "{l s='Please choose Destination Region.' mod='kbetsy'}";
    var max_qty_err = "{l s='Maximum Quantity cannot be less than minimum quantity' mod='kbetsy'}";
    var min_qty_vald = "{l s='Minimum quantity cannot be greater than 999.' mod='kbetsy'}";
    var max_qty_vald = "{l s='Maximum quantity cannot be greater than 999.' mod='kbetsy'}";
    var min_qty_zero = "{l s='Quantity cannot be zero' mod='kbetsy'}";
    var store_cat_proc = "{l s='Please select store category to proceed.' mod='kbetsy'}";
    var store_profile_product = "{l s='Please select atleast one product to contine.' mod='kbetsy'}";
    var size_chart_image_missing = "{l s='Size chart Image missing.Please upload the same..' mod='kbetsy'}";
</script>