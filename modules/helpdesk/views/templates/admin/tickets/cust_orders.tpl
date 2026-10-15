{*
* FMM Helpdesk Module
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright FMM Modules
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   FmmHelpdesk
*}
<div class="panel">
  <div class="panel-heading">
    {l s='Customer orders' d='Shop.Theme.Checkout'}
  </div>
  {if $cust_total_orders}
    <table class="table table-striped table-bordered table-labeled hidden-sm-down">
      <thead class="thead-default">
        <tr>
          <th>{l s='ID' d='Shop.Theme.Checkout'}</th>
          <th>{l s='Order reference' d='Shop.Theme.Checkout'}</th>
          <th>{l s='Customer' d='Shop.Theme.Checkout'}</th>
          <th>{l s='Total' d='Shop.Theme.Checkout'}</th>
          <th class="hidden-md-down">{l s='Payment' d='Shop.Theme.Checkout'}</th>
          <th class="hidden-md-down">{l s='Status' d='Shop.Theme.Checkout'}</th>
          <th>{l s='Date' d='Shop.Theme.Checkout'}</th>
          <th>{l s='Action' d='Shop.Theme.Checkout'}</th>
          <th>&nbsp;</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$cust_total_orders item=order}
          <tr>
            <td>{$order.id_order|escape:'htmlall':'UTF-8'}</th>
            <th scope="row">{$order.reference|escape:'htmlall':'UTF-8'}</th>
            <td class="text-xs-right">{$cust_info|escape:'htmlall':'UTF-8'}</td>
            <td class="text-xs-right">{$order.id_currency|escape:'htmlall':'UTF-8'}{$order.total_paid|string_format:"%.2f"|escape:'htmlall':'UTF-8'}</td>
            <td class="hidden-md-down">{$order.payment|escape:'htmlall':'UTF-8'}</td>
            <td>
              <span
                class="label label-pill"
                style="background-color:{$order.order_state_color|escape:'htmlall':'UTF-8'}"
              >
                {$order.order_state|escape:'htmlall':'UTF-8'}
              </span>
            </td>
            <td class="date">{$order.date_upd|escape:'htmlall':'UTF-8'}</td>
            <td class="text-sm-center order-actions">
              <a href="{$link->getAdminLink('AdminOrders')|escape:'htmlall':'UTF-8'}&id_order={$order.id_order|escape:'htmlall':'UTF-8'} &vieworder" data-link-action="view-order-details">
                {l s='View' d='Shop.Theme.Customeraccount'}
              </a>
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>

			
	{else}
			<p class="warning">{l s='No Order recevied yet' mod='helpdesk'}</p>
  {/if}
</div>