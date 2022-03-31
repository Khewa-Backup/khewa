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

<div class="an_wishlist-nav-container">
	<div class="an_wishlist-nav">
		<a href="{$link->getModuleLink('an_wishlist', 'list', array(), true)|escape:'htmlall':'UTF-8'}" title="{l s='My wishlist' mod='an_wishlist'}">
			{l s='My wishlist' mod='an_wishlist'}{if ($config.display_likes_nav)} (<span class="js-an_wishlist-nav-count">{$count|intval}</span>){/if}
		</a>
	</div>
</div>
