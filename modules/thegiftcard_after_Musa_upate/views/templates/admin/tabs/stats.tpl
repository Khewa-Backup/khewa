{*
* 2017 Keyrnel
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one shop.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
* If you have not received this module from us, thank you for contacting us.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author Keyrnel
* @copyright  2022 - Keyrnel SARL
* @license commercial
* International Registered Trademark & Property of Keyrnel SARL
*}

{if isset($statistics['mail_error']) && $statistics['mail_error'] > 0}
<div class="alert alert-danger">
	<span>{$statistics['mail_error']|intval} {l s='emails have not been automatically sent to users. Use the following button to manually send them.' mod='thegiftcard'}</<span>
	<div class="btn-group"><a class="btn btn-default" href="{$currentIndex|escape:'html':'UTF-8'}&sendEmails=1">Send emails</a></div>
</div>
{/if}

{if isset($statistics['currency_missing']) && $statistics['currency_missing'] > 0}
<div class="alert alert-warning">
	<span>{$statistics['currency_missing']|intval} {l s='currencies must be indexed' mod='thegiftcard'}</<span>
	<div class="btn-group"><a class="btn btn-default" href="{$currentIndex|escape:'html':'UTF-8'}&indexCurrencies=1">Index currencies</a></div>
</div>
{/if}

<div class="panel">
	<div class="panel-heading">
		<i class="icon-bar-chart"></i> {l s='Statistics' mod='thegiftcard'}
	</div>
	<table class="table">
		<thead>
			<tr>
				<th class="center"><span class="title_box ">{l s='ID' mod='thegiftcard'}</span></th>
				<th><span class="title_box">{l s='Template' mod='thegiftcard'}</span></th>
				<th><span class="title_box">{l s='Amount' mod='thegiftcard'}</span></th>
				<th><span class="title_box">{l s='Voucher code' mod='thegiftcard'}</span></th>
				<th><span class="title_box">{l s='Beneficiary' mod='thegiftcard'}</span></th>
				<th><span class="title_box ">{l s='Used' mod='thegiftcard'}</span></th>
				<th><span class="title_box ">{l s='Validity' mod='thegiftcard'}</span></th>
				<th><span class="title_box">{l s='Order' mod='thegiftcard'}</span></th>
				<th><span class="title_box">{l s='Actions' mod='thegiftcard'}</span></th>
			</tr>
		</thead>
		<tbody>
			{if isset($statistics['giftcards']) && count($statistics['giftcards'])}
				{foreach $statistics['giftcards'] as $giftcard}
				<tr {if $giftcard.should_be_sent}class="danger"{/if} data-id="{$giftcard.id_giftcard|intval}">
					<td class="center">{$giftcard.id_giftcard|intval}</td>
					<td>
						<a href="{$giftcard.img_url|escape:'html':'UTF-8'}" class="fancybox">
						<img
							src="{$giftcard.img_url|escape:'html':'UTF-8'}"
							alt="{l s='gift card' mod='thegiftcard'}"
							title="{l s='gift card' mod='thegiftcard'}"
							style="max-width:80px"
							class="img-thumbnail" />
						</a>
					</td>
					<td>{Tools::displayPrice($giftcard.reduction_amount|intval, $giftcard.id_currency|intval)}</td>
					<td><a href="{$giftcard.cart_rule_url|escape:'html':'UTF-8'}">{$giftcard.code|escape:'html':'UTF-8'}</a></td>
					<td>
						<b>{$giftcard.beneficiary|escape:'html':'UTF-8'}</b><br/>
						{if $giftcard.sent}
							<span class="badge badge-success">{l s='email send' mod='thegiftcard'}</span>
							<div class="btn-group"><a class="btn btn-default" href="{$currentIndex|escape:'html':'UTF-8'}&resendEmail={$giftcard.id_giftcard|intval}">{l s='resend' mod='thegiftcard'}</a></div>
						{else}
							<span class="badge badge-danger">{l s='email not send' mod='thegiftcard'}</span>
						{/if}
					</td>
					<td>
						{foreach $giftcard.consumption as $consumption}
							<a href="{$consumption.order_url|escape:'html':'UTF-8'}" class="badge-consumption">
								<span class="badge {if $consumption.badge==='total'}badge-success{else}badge-warning{/if}">
									{Tools::displayPrice($consumption.amount|floatval, $consumption.id_currency|intval)}
								</span>
							</a>
						{/foreach}
					</td>
					<td>{l s='Date from:' mod='thegiftcard'} {dateFormat date=$giftcard.date_from}<br/>{l s='Date to:' mod='thegiftcard'} {dateFormat date=$giftcard.date_to}</td>
					<td>{l s='Reference:' mod='thegiftcard'} <a href="{$giftcard.order_url|escape:'html':'UTF-8'}">{$giftcard.reference|escape:'html':'UTF-8'}</a><br/>{l s='Date:' mod='thegiftcard'} {dateFormat date=$giftcard.date_purschased}</td>
					<td>
						<button type="button" class="btn btn-primary" js-action="generate-pdf">{l s='Generate pdf' mod='thegiftcard'}</button>
					</td>
				</tr>
				{/foreach}
			{else}
				<td class="list-empty" colspan="11">
					<div class="list-empty-msg">
						<i class="icon-warning-sign list-empty-icon"></i>
						{l s='No records found' mod='thegiftcard'}
					</div>
				</td>
			{/if}
		</tbody>
	</table>
</div>
