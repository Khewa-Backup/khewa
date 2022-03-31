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

{if count($products)>0 AND $config.display_wishlist_in_cart=='1'}
<div class="an_wishlist-list-cart">
	<h1>{l s='My wishlist' mod='an_wishlist'}</h1>

	<section id="products" class="clearfix"> 
		<div class="products row">
		{foreach from=$products item="product"}
		  {include file='catalog/_partials/miniatures/product.tpl' product=$product}
		{/foreach}
		</div>
	</section>
</div>
{/if}