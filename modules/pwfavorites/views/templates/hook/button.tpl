{**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

<a href="{if $customer.is_logged}#{else}{$urls.pages.authentication}{/if}"
   class="pw-fav-toggle{if $favorite} active{/if}"
   data-id-product="{$id_product}"
   data-product-name="{$product_name}"
>
  <span class="pw-fav-add">
    <i class="material-icons">favorite_border</i>
    <span class="pw-fav-btn-text">{l s='Add to my favorites' mod='pwfavorites'}</span>
  </span>
  <span class="pw-fav-remove">
    <i class="material-icons">favorite</i>
    <span class="pw-fav-btn-text">{l s='Remove from my favorites' mod='pwfavorites'}</span>
  </span>
  <script>
    if (typeof prestashop.pwFavorites !== 'undefined' && 'handleButtons' in prestashop.pwFavorites) {
      prestashop.pwFavorites.handleButtons();
    }
  </script>
</a>
