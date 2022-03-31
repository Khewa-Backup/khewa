{*
* 2020 Anvanto
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
*  @author Anvanto <anvantoco@gmail.com>
*  @copyright  2020 Anvanto
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of Anvanto
*}

<div class="js-an_wishlist-container an_wishlist-container">
	<span data-url="{$an_wishlistAjax|escape:'quotes'}" class="btn btn-secondary an_wishlist-btn-product js-an_wishlist-addremove{if $status} an_wishlist-in{/if}">
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
    {l s='Add to wishlist' mod='an_wishlist'}{if ($config.display_likes_product)} (<span class="js-an_wishlist-product-count">{$countWishlists|intval}</span>){/if}</span>
</div>