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
{extends file='page.tpl'}

{block name='content'}
  <section id="content-hook_order_confirmation" class="card">
    <div class="card-block">
      <div class="row">
        <div class="col-md-12">
          <p>
              {$translations['An error occured during your payment.']|escape:'htmlall':'UTF-8'}
              <br />
              {if $payment_retry_order_id}
                {$translations['Please [a @href1@]try again[/a] or contact the website owner.']|stripelreplace:['@href1@' => "javascript:void(0)\" id=\"stripe-try-again-button\""] nofilter}
                <form method="post" action="{$payment_retry_action|escape:'htmlall':'UTF-8'}" class="d-inline">
                  <input type="hidden" name="id_order" value="{$payment_retry_order_id|intval}">
                  <input type="hidden" name="retry_token" value="{$payment_retry_token|escape:'htmlall':'UTF-8'}">
                </form>
              {else}
                {$translations['Please [a @href1@]try again[/a] or contact the website owner.']|stripelreplace:['@href1@' => {$stripe_order_url|escape}] nofilter}
              {/if}<br/><br/>
          </p>
        </div>
      </div>
    </div>
  </section>
{/block}

{block name='javascript_bottom' append}
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('stripe-try-again-button');
    if (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var form = document.querySelector('form[action*="retryPayment"]') || btn.parentElement.querySelector('form');
        if (form) {
          form.submit();
        }
      });
    }
  });
</script>
{/block}
