{*exclude-mayank*}
{if isset($product) && !empty($product)}
<tr>
    <td class="kb-etsy-product-id" style="text-align: center;">{$product->id|escape:'htmlall':'UTF-8'}</td>
    <td style="">
       {* <img src="{$link->getImageLink($product->link_rewrite, $product->id_image, 'home_default')|escape:'html':'UTF-8'}" width="65px" height="65px"/>*} {$product->name|escape:'htmlall':'UTF-8'}
    </td>
    <td style="width: 25px;text-align: center;">
        <span class="kb-product-remove" style="color: #ff0000;cursor: pointer;"><i class="icon-remove"></i></span>
    </td>
</tr>
{/if}
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
*}