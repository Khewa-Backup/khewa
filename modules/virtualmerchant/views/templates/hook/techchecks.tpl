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

<div class="panel">
	<fieldset>
		<legend><img src="../modules/virtualmerchant/views/img/checks-icon.gif" alt="{l s='Technical Checks' mod='virtualmerchant'}" />{l s='Technical Checks' mod='virtualmerchant'}</legend>

		{if $check_result}
		
		<div class="conf">{l s='Good news! All the checks were successfully performed.' mod='virtualmerchant'}</div>

		{else}

		<div class="warn">{l s='Unfortunately, at least one issue is preventing you from using the module. Please fix the issue and reload this page.' mod='virtualmerchant'}</div>

		{/if}

		<table cellspacing="0" cellpadding="0" class="virtualmerchant-technical">
		{foreach $requirements as $k => $requirement}
			{if $requirement['result']}
			    {assign var="image" value="ok"}
			{else}
			    {assign var="image" value="forbbiden"}
			{/if}
			<tr>
				<td><img src="../modules/virtualmerchant/views/img/{$image|escape:'htmlall':'UTF-8'}.gif" alt="" /></td>
				<td>{$requirement.name|escape:'htmlall':'UTF-8'}</td>
			</tr>
		{/foreach}
		</table>

	</fieldset>
</div>