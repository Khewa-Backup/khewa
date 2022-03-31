{*
* 2021 Anvanto
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
*  @author Anvanto <anvantoco@gmail.com>
*  @copyright  2021 Anvanto
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of Anvanto
*}

<div class="panel col-lg-12">
    <div class="panel-heading">
        <span>{l s='Customers' mod='an_wishlist'}</span>
    </div>
    <div class="panel-body">
		
		<p>
			<a class="btn btn-default export-csv" href="{$admUrl|escape:'htmlall':'UTF-8'}&configure=an_wishlist&antab=customers&export=1"><i class="icon-cloud-upload"></i>{l s='CSV Export' mod='an_wishlist'}</a>
		</p>
		
       <table class="table">
	   <thead>
	   <tr>
			<th><span class="title_box active">{l s='First Name' mod='an_wishlist'}</span></th>
			<th><span class="title_box active">{l s='Last Name' mod='an_wishlist'}</span></th>
			<th><span class="title_box active">{l s='Email' mod='an_wishlist'}</span></th>
			<th><span class="title_box active">{l s='Last Wishlist update or visit' mod='an_wishlist'}</span></th>
			<th><span class="title_box active">{l s='In Wishlist' mod='an_wishlist'}</span></th>
	   </tr>
	   </thead>
	   <tbody>
	   {foreach from=$customers item=item}
	   <tr>
			<td>{$item.firstname|escape:'htmlall':'UTF-8'}</td>
			<td>{$item.lastname|escape:'htmlall':'UTF-8'}</td>
			<td>{$item.email|escape:'htmlall':'UTF-8'}</td>
			<td>{$item.anw_date_upd|escape:'htmlall':'UTF-8'}</td>
			<td>{$item.countProducts|intval}</td>

	   </tr>
	   {/foreach}
	   </tbody>
	   </table>
	   
	   
	   
    </div>
</div>