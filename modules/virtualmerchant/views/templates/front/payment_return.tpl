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

<p>
{l s='Your order on' mod='virtualmerchant'} <span class="bold">{$shop_name}</span> {l s='is complete and has been assigned reference' mod='virtualmerchant'} <span class="bold">{$reference}</span>.
	<br /><br /><span class="bold">{l s='Your order will be sent as soon as possible.' mod='virtualmerchant'}</span>
	<br /><br />{l s='An e-mail has been sent to you with this information.' mod='virtualmerchant'}
	<br /><br />{l s='For any questions or for further information, please contact our' mod='virtualmerchant'} <a class="link-button" href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='virtualmerchant'}</a>.
</p>
