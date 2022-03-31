{*
* This file is part of module Virtual Merchant
*
*  @author    Bellini Services <bellini@bellini-services.com>
*  @copyright 2007-2017 bellini-services.com
*  @license   readme
*
* Your purchase grants you usage rights subject to the terms outlined by this license.
*
* You CAN use this module with a single, non-multi store configuration, production installation and unlimited test installations of PrestaShop.
* You CAN make any modifications necessary to the module to make it fit your needs. However, the modified module will still remain subject to this license.
*
* You CANNOT redistribute the module as part of a content management system (CMS) or similar system.
* You CANNOT resell or redistribute the module, modified, unmodified, standalone or combined with another product in any way without prior written (email) consent from bellini-services.com.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*}

{extends "$layout"}

{block name="content"}

<form action="{$link->getModuleLink('virtualmerchant', 'payment', [], true)}" method="post" id="creditForm" name="creditForm">
	<input type="hidden" id="paymentSubmit" name="paymentSubmit" value="1"/>
	<input type="hidden" name="token" value="{$mytoken}">
	<div class="box cheque-box">
	    <h3 class="page-subheading">{l s='Pay by Credit Card' mod='virtualmerchant'}</h3>
	    <p class="cheque-indent">
		<strong class="dark">
		    {l s='You have chosen to pay by Credit Card.' mod='virtualmerchant'} {l s='Here is a short summary of your order:' mod='virtualmerchant'}
		</strong>
	    </p>
	    <p>
		- {l s='The total amount of your order is' mod='virtualmerchant'}
		<span id="amount" class="price">{$vm_total}</span>
	    </p>

		{if isset($perrors) and ($perrors|@count > 0)}
		<fieldset>
			<div class="alert error" id="errorDiv">
				<img alt="{l s='Error: ' mod='virtualmerchant'}" src="{$this_path_vm}views/img/forbbiden.gif" />
				{l s='One or more errors were encountered. Please fix to continue.' mod='virtualmerchant'}
				<ol>
				{foreach from=$perrors item=error}
					<li>{$error}</li>
				{/foreach}
				</ol>
			</div>
		</fieldset>
		{/if}

		<p style="color: red;"><b>{l s='Please enter your credit card information below.' mod='virtualmerchant'}.</b></p>

		<div class="card-js" id="vmcard">
			<input class="card-number" name="card-number">
			<input class="expiry-month" name="expiry-month">
			<input class="expiry-year" name="expiry-year">
			<input class="cvc" name="cvc">
		</div>
		<a id="single_image" href="{$this_path_vm}views/img/CVC.jpg">{l s='Where is My CVV/CVC Number?' mod='virtualmerchant'}</a>
	</div>

	<p class="cart_navigation clearfix" id="cart_navigation" style="padding-top: 1rem;">
	    <a class="button-exclusive btn btn-default" href="{$link->getPageLink('order', true, NULL, "step=3")}">
		<i class="icon-chevron-left"></i>{l s='Other payment methods' mod='virtualmerchant'}
	    </a>
	    <button class="btn btn-primary continue pull-xs-right" id="paymentSubmit" type="submit">
		<span>{l s='I confirm my order' mod='virtualmerchant'}<i class="icon-chevron-right right"></i></span>
	    </button>
	</p>
</form>

{/block}

