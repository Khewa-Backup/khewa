{*
*
* Google merchant center Pro
*
* @author BusinessTech.fr
* @copyright Business Tech
*
*           ____    _______
*          |  _ \  |__   __|
*          | |_) |    | |
*          |  _ <     | |
*          | |_) |    | |
*          |____/     |_|
*
*}

<div class="col-xs-12">

	<div class="clr_50"></div>
	
	<div class="row">
		<div class="col-xs-12 col-lg-3 text-center">
			<i class="fa fa-money gsa-icon-market" aria-hidden="true"></i>
			<div class="clr_10"></div>
			<p class="advantages-label">{l s='Promote at lower cost' mod='gmerchantcenterpro'}</p>
			<p>{l s='No subscription fees' mod='gmerchantcenterpro'}
			<br />{l s='Only commissions on sales' mod='gmerchantcenterpro'}
			<br />{l s='Commission rates lower than Amazon' mod='gmerchantcenterpro'}</p>			
		</div>
		<div class="col-xs-12 col-lg-3 text-center">
			<i class="fa fa-desktop gsa-icon-market" aria-hidden="true"></i>
			<div class="clr_10"></div>
			<p class="advantages-label">{l s='Improve your visibility' mod='gmerchantcenterpro'}</p>
			<p>{l s='Products for purchase on Google Shopping' mod='gmerchantcenterpro'}
			<br />{l s='Products for purchase on Google Voice Assistant' mod='gmerchantcenterpro'}
			<br/>{l s='Soon on all Google platforms' mod='gmerchantcenterpro'}</p>
		</div>
		<div class="col-xs-12 col-lg-3 text-center">
			<i class="fa fa-line-chart gsa-icon-market" aria-hidden="true"></i>
			<div class="clr_10"></div>
			<p class="advantages-label">{l s='Increase your sales' mod='gmerchantcenterpro'}</p>
			<p>{l s='People buy directly on Google' mod='gmerchantcenterpro'}
			<br/>{l s='Payment info saved' mod='gmerchantcenterpro'}
			<br/>{l s='Login info saved' mod='gmerchantcenterpro'}</p>	
		</div>
		<div class="col-xs-12 col-lg-3 text-center">
			<i class="fa fa-handshake-o gsa-icon-market" aria-hidden="true"></i>
			<div class="clr_10"></div>
			<p class="advantages-label">{l s='Boost loyalty' mod='gmerchantcenterpro'}</p>
			<p>{l s='Simple, secure and fast buying' mod='gmerchantcenterpro'}
			<br />{l s='Google Purchase Warranties' mod='gmerchantcenterpro'}
			<br />{l s='Visibility that increases with purchases number' mod='gmerchantcenterpro'}</p>
		</div>
	</div>

	<div class="clr_50"></div>

	<div class="col-xs-12 text-center">
		{if $sCurrentIso == "fr"} 
			<a href="https://docs.google.com/forms/d/e/1FAIpQLSfRhvz2jOIsnZYPgWXjx-BnO52thLkWZ8_ZBPARqIT-e7w4zw/viewform?usp=sf_link" target="_blank" class="text-center btn btn-lg btn-success btn-request-beta"><i class="fa fa-sign-in"></i>&nbsp;{l s='Request beta access' mod='gmerchantcenterpro'}</a>
		{else}
			<a href="https://docs.google.com/forms/d/e/1FAIpQLSeUUTWh9NS3eln_MSihgmArirO4Uq6ghzDIV9JEIgqa9w7cug/viewform?usp=sf_link" target="_blank" class="text-center btn btn-lg btn-success btn-request-beta"><i class="fa fa-sign-in"></i>&nbsp;{l s='Request beta access' mod='gmerchantcenterpro'}</a>
		{/if}
	</div>	
	
	<div class="clr_50"></div>

	<p class="col-xs-12 market-text">
		<a href="https://www.google.com/retail/solutions/shopping-actions/" target="_blank">{l s='Google Shopping Actions' mod='gmerchantcenterpro'}</a>&nbsp;{l s='is a program that allows every Internet user to buy, in a single transaction, products coming from different sites directly on Google. In addition, thanks to the recording of payment and delivery information, their shopping journey is made as simple as possible.' mod='gmerchantcenterpro'}
	</p class="col-xs-12">

	<div class="clr_10"></div>

	<div class="col-xs-12 market-text">
		{l s='For more details:' mod='gmerchantcenterpro'}
		<div class="clr_10"></div>
		<ul>
			<li><a href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/331">{l s='How to participate in Google Shopping Actions program?' mod='gmerchantcenterpro'}</a></li>
			<li><a href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/332">{l s='What are the advantages of Google Shopping Actions for e-merchants?' mod='gmerchantcenterpro'}</a></li>
			<li><a href="{$smarty.const._GMCP_BT_FAQ_MAIN_URL|escape:'htmlall':'UTF-8'}{$sFaqLang|escape:'htmlall':'UTF-8'}/faq/333">{l s='Why sign up for our beta now?' mod='gmerchantcenterpro'}</a></li>
		</ul>	
	</div>	

	<div class="clr_10"></div>
	<div class="clr_hr"></div>
	<div class="clr_20"></div>
</div>	