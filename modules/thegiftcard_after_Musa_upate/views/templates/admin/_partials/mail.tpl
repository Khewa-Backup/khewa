{*
* 2022 - Keyrnel SARL
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
* @copyright 2022 - Keyrnel SARL
* @license   commercial
* International Registered Trademark & Property of Keyrnel SARL
*}

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>{$title|escape:'html':'UTF-8'}</title>
        <style>
            {literal}
            @media only screen and (max-width: 300px) {
                body {width:218px !important; margin:auto !important;}
                .table {width:195px !important; margin:auto !important;}
                .logo, .titleblock, .linkbelow, .box, .footer, .space_footer {width:auto !important;display: block !important;}
                span.title {font-size:20px !important;line-height: 23px !important}
                span.subtitle {font-size: 14px !important;line-height: 18px !important;padding-top:10px !important;display:block !important;}
                td.box p {font-size: 12px !important;font-weight: bold !important;}
                .table-recap table, .table-recap thead, .table-recap tbody, .table-recap th, .table-recap td, .table-recap tr {display: block !important;}
                .table-recap {width: 200px!important;}
                .table-recap tr td, .conf_body td {text-align:center !important;}
                .address {display: block !important;margin-bottom: 10px !important;}
                .space_address {display: none !important;}
            }
            @media only screen and (min-width: 301px) and (max-width: 500px) {
                body {width:308px!important;margin:auto!important;}
                .table {width:285px!important;margin:auto!important;}
                .logo, .titleblock, .linkbelow, .box, .footer, .space_footer {width:auto!important;display: block!important;}
                .table-recap table, .table-recap thead, .table-recap tbody, .table-recap th, .table-recap td, .table-recap tr {display: block !important;}
                .table-recap {width: 293px !important;}
                .table-recap tr td, .conf_body td {text-align:center !important;}

            }
            @media only screen and (min-width: 501px) and (max-width: 768px) {
                body {width:478px!important;margin:auto!important;}
                .table {width:450px!important;margin:auto!important;}
                .logo, .titleblock, .linkbelow, .box, .footer, .space_footer {width:auto!important;display: block!important;}
            }
            @media only screen and (max-device-width: 480px) {
                body {width:308px!important;margin:auto!important;}
                .table {width:285px;margin:auto!important;}
                .logo, .titleblock, .linkbelow, .box, .footer, .space_footer {width:auto!important;display: block!important;}
                .table-recap {width: 285px!important;}
                .table-recap tr td, .conf_body td {text-align:center!important;}
                .address {display: block !important;margin-bottom: 10px !important;}
                .space_address {display: none !important;}
            }
            {/literal}
        </style>
    </head>
    <body style="-webkit-text-size-adjust:none;background-color:#fff;width:650px;font-family:Open-sans, sans-serif;color:#555454;font-size:13px;line-height:18px;margin:auto">
        {$content nofilter}{* HTML, cannot escape *}
    </body>
</html>
