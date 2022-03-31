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

{include file='./suggestions.tpl'}

{* *}
<ul class="nav nav-pills nav-fill" style="margin-bottom: 15px;">
  <li class="nav-item">
    <a class="nav-link btn btn-default" href="{$admUrl|escape:'htmlall':'UTF-8'}&configure=an_wishlist">{l s='Settings' mod='an_wishlist'}</a>
  </li>
  <li class="nav-item">
	<a class="nav-link btn btn-default" href="{$admUrl|escape:'htmlall':'UTF-8'}&configure=an_wishlist&antab=products">{l s='Products' mod='an_wishlist'}</a>
  </li>
  <li class="nav-item">
	<a class="nav-link btn btn-default" href="{$admUrl|escape:'htmlall':'UTF-8'}&configure=an_wishlist&antab=customers">{l s='Customers' mod='an_wishlist'}</a>
  </li>  
</ul>
