{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer tohttp://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* @category  PrestaShop Module
* @author    knowband.com <support@knowband.com>
* @copyright 2017 Knowband
* @license   see file: LICENSE.txt
*
* Description
*
* Admin Header tabs file
*}
{*
 * Added this file to show the button for the custom details
 * @modifier Pragya Maurya
 * @date 13-06-2024
 *} 
<a onclick="show_ebay_customs({$profile_product_id|escape:'htmlall':'UTF-8'})" data-toggle="modal" class="">
    <i class="icon-{$icon|escape:'htmlall':'UTF-8'}"></i> {$action|escape:'htmlall':'UTF-8'}
</a>