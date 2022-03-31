{*
* 2007-2020 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
* 
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2021 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
{if $image_category.total_image || $image_supplier.total_image || $image_manufacturer.total_image || $image_product.total_image}
    <div class="alert alert-warning">{l s='There are unused images taking space on your server. Those images can be cleared to save your hosting space. For safety, you are recommended to make a backup of img/ folder before clearing unused images.' mod='ets_superspeed'}</div>
    <ul>
        {if $image_category.total_image}
            <input type="hidden" value="1" name="unused_category_images"/>
            <li><label><b>{$image_category.total_image|intval}</b>&nbsp;{l s='unused category image(s)' mod='ets_superspeed'} {$image_category.total_size|escape:'html':'UTF-8'} </label></li>
        {/if} 
        {if $image_supplier.total_image}
            <input type="hidden" value="1" name="unused_supplier_images"/>
            <li><label><b>{$image_supplier.total_image|intval}</b>&nbsp;{l s='unused supplier image(s)' mod='ets_superspeed'} {$image_category.total_size|escape:'html':'UTF-8'} </label></li>
        {/if}
        {if $image_manufacturer.total_image}
            <input type="hidden" value="1" name="unused_manufacturer_images"/>
            <li><label><b>{$image_manufacturer.total_image|intval}</b>&nbsp;{l s='unused manufacturer image(s)' mod='ets_superspeed'} {$image_category.total_size|escape:'html':'UTF-8'} </label></li>
        {/if}
        {if $image_product.total_image}
            <input type="hidden" value="1" name="unused_product_images"/>
            <li><label><b>{$image_product.total_image|intval}</b>&nbsp;{l s='unused product image(s)' mod='ets_superspeed'} {$image_product.total_size|escape:'html':'UTF-8'} </label></li>
        {/if}   
    </ul>
    <button class="btn btn-default sp_cleaner_image"><i class="icon-trash"></i> {l s='Clear all unused images' mod='ets_superspeed'}</button>
{else}
    <div class="alert alert-info">
        {l s='Congratulations! Your website is good here. No unused images found. Nothing to do.' mod='ets_superspeed'}
    </div>
{/if}