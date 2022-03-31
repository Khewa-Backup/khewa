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

{literal}
<style type="text/css">
	BODY, TD, INPUT, SELECT, TEXTAREA, BUTTON, .normal {font-family:arial,helvetica,sans-serif; font-size:10pt; font-weight:normal; }
	.txtbld {font-weight: bold;}
	.btnwdcc {background: #293B85; font-weight: bold;color:#fff;}
	.btnwodcc {background: #8E0C34;color:#fff; }
</style>

<script type="text/javascript">
	function processwithdcc() {
		document.form1.dcc.value="1";
		document.form1.processdcc.disabled=true;
		document.form1.process.disabled=true;
		document.form1.submit();
	}
	function processwithoutdcc() {
		document.form1.dcc.value="0";
		document.form1.processdcc.disabled=true;
		document.form1.process.disabled=true;
		document.form1.submit();
	}
</script>
{/literal}

  <form name="form1" action="{$link->getModuleLink('virtualmerchant', 'payment', [], true)}" method="post">

  <input type="hidden" name="dispatchMethod" value="processTransaction">
  <input type="hidden" name="dcc">
  <input type="hidden" id="dccSubmit" name="dccSubmit" value="1"/>
  <input type="hidden" name="token" value="{$mytoken}">
  <input type="hidden" name="transactionid" value="{$response.id}">

  <table cellspacing="0" width="100%" cellpadding="0" align="center" border="1" bordercolor='#294984'>
	<tr bgcolor='#294984'><td height="25" colspan="4" style='color: #FFFFFF; font-size:10pt; font-weight:bold; '>{l s='SALE - Dynamic Currency Confirmation' mod='virtualmerchant'}</td></tr>
	<tr>
	    <td colspan="4">
		<table width="100%">
			<TR>
				<TD width="50%">{l s='Original Transaction Currency' mod='virtualmerchant'}</TD>
				<TD>{$original_txn_currency}</TD>
			</TR>
			<TR>
				<TD width="50%">{l s='Your Currency' mod='virtualmerchant'}</TD>
				<TD>{$response.ssl_txn_currency_code}</TD>
			</TR>
			<TR>
				<TD>{l s='Conversion Rate' mod='virtualmerchant'}</TD>
				<TD>{$response.ssl_conversion_rate}</TD>
			</TR>
			<TR>
				<TD>{l s='Markup(%)' mod='virtualmerchant'}</TD>
				<TD>{$response.ssl_markup}</TD>
			</TR>
			<TR>
				<TD>{l s='Total' mod='virtualmerchant'} ({$original_txn_currency})</TD>
				<TD>{$response.ssl_amount}</TD>
			</TR>
			<TR class="txtbld">
				<TD>{l s='Total' mod='virtualmerchant'} ({$response.ssl_txn_currency_code})</TD>
				<TD>{$response.ssl_cardholder_amount}</TD>
			</TR>
		</table>
	    </td>
	</tr>
	<TR height="50" align="center"  valign="center">
		<td colspan="4">
		<BR>
		<input type="button" class="btnwdcc" name="processdcc" value="{l s='Please charge my purchase in my home currency' mod='virtualmerchant'}" onclick="processwithdcc()">
		&nbsp;<input type="button" class="btnwodcc" name="process" value="{l s='Do not charge me in my home currency; charge my purchase in the foreign currency' mod='virtualmerchant'}" onclick="processwithoutdcc()">
		<P>{l s='All currency choices are final.' mod='virtualmerchant'}</P>
		</td>
	</TR>
  </table>
  </form>	
