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
var point_rate = {$point_rate};
var point_value = {$point_value};
var points_in_cart = {$points_in_cart};
var none_award = {$none_award};

$(document).ready(function() {

			//alert($('.current-price span').attr('content'));

	$(document).on('change', '.current-price span', function(e){
		//alert($('.current-price span').attr('content'));
		updateLoyaltyView($('.current-price span').attr('content'));
	})
	updateLoyaltyView($('.current-price span').attr('content'));
});

function updateLoyaltyView(new_price) {
	if (typeof(new_price) == 'undefined' || typeof(productPriceWithoutReduction) == 'undefined')
	//	return;

	var points = Math.floor(new_price / point_rate);
	var total_points = points_in_cart + points;
	var voucher = total_points * point_value;

	if (!none_award && productPriceWithoutReduction != new_price) {
		$('#loyalty').html(loyalty_already);
	} else if (!points) {
	} else {
		var content =  "<i class='material-icons'>attach_money</i> {l s='By buying this product you can collect up to' mod='loyaltypointsrsi'} <strong><span id=\"loyalty_points\">"+points+'</span> ';
		if (points > 1)
			content += "{l s='loyalty points' mod='loyaltypointsrsi'}</strong>. ";
		else
			content += "{l s='loyalty point' mod='loyaltypointsrsi'} </strong>. ";

		content += " {l s='Your cart will total' mod='loyaltypointsrsi'} <strong><span id=\"total_loyalty_points\">"+total_points+'</span> ';
		if (total_points > 1)
			content += "{l s='loyalty points' mod='loyaltypointsrsi'}</strong>. ";
		else
			content += "{l s='loyalty point' mod='loyaltypointsrsi'} </strong>. ";

		content += "</strong> {l s='that can be converted into a voucher of' mod='loyaltypointsrsi'} ";
		content += '<strong><span id="loyalty_price">'+FormatCurrency(voucher)+'</span></strong>.';
		$('#loyalty').html(content);
	}
}
{literal}
function FormatCurrency(price)
{
	// if you modified this function, don't forget to modify the PHP function displayPrice (in the Tools.php class)
	var blank = ' ';
    var currencySign = {/literal}"{$currency.sign}"{literal};
	var price = parseFloat(price).toFixed(10);
    var priceDisplayPrecision = 2;
    var currencyFormat = 1;

	if (currencyFormat == 1)
		return currencySign + blank + number_format(price, priceDisplayPrecision, ',', '.');
	if (currencyFormat == 2)
		return (number_format(price, priceDisplayPrecision, ' ', ',') + blank + currencySign);
	if (currencyFormat == 3)
		return (currencySign + blank + number_format(price, priceDisplayPrecision, '.', ','));
	if (currencyFormat == 4)
		return (number_format(price, priceDisplayPrecision, ',', '.') + blank + currencySign);
	if (currencyFormat == 5)
		return (currencySign + blank + number_format(price, priceDisplayPrecision, '\'', '.'));
	return price;
}

function number_format(number, decimals, dec_point, thousands_sep) {
  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function (n, prec) {
      var k = Math.pow(10, prec);
      return '' + Math.round(n * k) / k;
    };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
}
{/literal}
</script>
<p id="loyalty" class="align_justify">
	{if $points}
		<i class='material-icons'>attach_money</i> {l s='By buying this product you can collect up to' mod='loyaltypointsrsi'} <strong><span id="loyalty_points">{$points}</span>
		{if $points > 1}{l s='loyalty points' mod='loyaltypointsrsi'}{else}{l s='loyalty point' mod='loyaltypointsrsi'}{/if}</strong>.
		{l s='Your cart will total' mod='loyaltypointsrsi'} <strong><span id="total_loyalty_points">{$total_points}</span>
		{if $total_points > 1}{l s='points' mod='loyaltypointsrsi'}{else}{l s='point' mod='loyaltypointsrsi'}{/if}</strong> {l s='that can be converted into a voucher of' mod='loyaltypointsrsi'}
		<strong><span id="loyalty_price"> {Tools::convertPrice($voucher)|string_format:"%.2f"} {$currency.sign}</span></strong>.
	{else}
		{if isset($no_pts_discounted) && $no_pts_discounted == 1}
			{l s='No reward points for this product because there\'s already a discount.' mod='loyaltypointsrsi'}
		{else}
			{l s='No reward points for this product.' mod='loyaltypointsrsi'}
		{/if}
	{/if}
</p>
