{*
* 2007-2023 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2023 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{literal}
<script>
(function(){"use strict";var c=[],f={},a,e,d,b;if(!window.jQuery){a=function(g){c.push(g)};f.ready=function(g){a(g)};e=window.jQuery=window.$=function(g){if(typeof g=="function"){a(g)}return f};window.checkJQ=function(){if(!d()){b=setTimeout(checkJQ,100)}};b=setTimeout(checkJQ,100);d=function(){if(window.jQuery!==e){clearTimeout(b);var g=c.shift();while(g){jQuery(g);g=c.shift()}b=f=a=e=d=window.checkJQ=null;return true}return false}}})();
</script>
{/literal}

<script type="text/javascript">

{literal}
$(document).ready(function() {

			//alert($('.current-price span').attr('content'));
//$('.cart-total span.value').bind("DOMSubtreeModified",function(){
	$(document).on('click', '.bootstrap-touchspin-up', function(e){
		//alert($('.cart-total span.value').text());
		//updateLoyaltyView($('.cart-total span.value').text());
		location.reload();
	
	})
	$(document).on('click', '.bootstrap-touchspin-down', function(e){
		//alert($('.cart-total span.value').text());
		//updateLoyaltyView($('.cart-total span.value').text());
		location.reload();
	
	})

	$(document).on('click', '.remove-from-cart', function(e){
		//alert($('.cart-total span.value').text());
		//updateLoyaltyView($('.cart-total span.value').text());
		location.reload();
	
	})


	
	//updateLoyaltyView($('.cart-summary-line span.value').text());
});

function updateLoyaltyView(new_price) {
	//	return;
	new_price = new_price.replace('{/literal}{$currency.sign}{literal}', '') ;
	//alert(new_price);
	alert(Math.floor(parseFloat(new_price) / {/literal}{$point_rate}{literal}));
var points = Math.floor(parseFloat(new_price) / {/literal}{$point_rate}{literal});
//alert(points);
	if (!points) {
		$('#loyalty').html('{/literal}{l s='No reward points for this product.' mod='loyalty'}{literal}');
	}
	else
	{
		var content =  "{/literal}{l s='By checking out this shopping cart you can collect up to' mod='loyaltypointsrsi'}{literal} <b><span id=\"loyalty_points\">"+points+'</span> ';
		if (points > 1)
			content += "{/literal}{l s='loyalty points' mod='loyaltypointsrsi'}{literal}</b>. ";
		else
			content += "{/literal}{l s='loyalty point' mod='loyaltypointsrsi'}{literal} </b>. ";

		/*content += " {l s='Your cart will total' mod='loyaltypointsrsi'} <b><span id=\"total_loyalty_points\">"+total_points+'</span> ';*/
		/*if (total_points > 1)
			content += "{l s='loyalty points' mod='loyaltypointsrsi'}</b>. ";
		else
			content += "{l s='loyalty point' mod='loyaltypointsrsi'} </b>. ";*/

		content += "</b> {/literal}{l s='that can be converted into a voucher of' mod='loyaltypointsrsi'}{literal} ";
		content += '<span id="loyalty_price">{/literal}{Tools::convertPrice($voucher)|string_format:"%.2f"} {$currency.sign} {literal}</span>.';
		$('#loyalty').html(content);
	}
}
{/literal}

</script>
<!-- MODULE Loyalty -->
<p id="loyalty">
	{if $points > 0}
		{l s='By checking out this shopping cart you can collect up to' mod='loyaltypointsrsi'} <b>
		{if $points > 1}{l s='%d loyalty points' sprintf=[$points] mod='loyaltypointsrsi'}{else}{l s='%d loyalty point' sprintf=[$points] mod='loyaltypointsrsi'}{/if}</b>

		{l s='that can be converted into a voucher of' mod='loyaltypointsrsi'} {Tools::convertPrice($voucher)|string_format:"%.2f"} {$currency.sign} {if isset($guest_checkout) && $guest_checkout}<sup>*</sup>{/if}.<br />
		{if isset($guest_checkout) && $guest_checkout}<sup>*</sup> {l s='Not available for Instant checkout order' mod='loyaltypointsrsi'}{/if}
	{else}
		{l s='Add some products to your shopping cart to collect some loyalty points.' mod='loyaltypointsrsi'}
	{/if}
</p>
<!-- END : MODULE Loyalty -->
