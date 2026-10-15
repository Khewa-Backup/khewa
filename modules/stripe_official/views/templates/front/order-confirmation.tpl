{**
 * Copyright (c) since 2010 Stripe, Inc. (https://stripe.com)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Stripe <https://support.stripe.com/contact/email>
 * @copyright Since 2010 Stripe, Inc.
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}
{* licence *}
<p>
  <b>
    {$translations['Congratulations, your order has been placed and will be processed soon.']|escape:'htmlall':'UTF-8'}
    </b><br/><br/>
    {$translations['Your order reference is [b]@target@[/b], you should receive a confirmation e-mail shortly.']|stripelreplace:['@target@' => {{$stripe_order_reference|escape:'htmlall'}}] nofilter}<br/><br/>
    {if $stripePayment->type == 'oxxo'}
      {$translations['Your can see your OXXO voucher [a @href1@]here[/a].']|stripelreplace:['@href1@' => {{$stripePayment->voucher_url|escape:'htmlall'}}, '@target@' => {'target="blank"'}] nofilter}
      <br/>
      <br/>
    {/if}
    {$translations['We appreciate your business.']|escape:'htmlall':'UTF-8'}
  <br/><br/>
</p><br/><br/>
