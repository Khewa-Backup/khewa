{*
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2021 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*} 

<div id="wpalert-header" class="wpalert-header-seen {if $wpheadertext->mobile_sw == 0}hidden-phone{/if}">
	<div class="wpalert-wrapper container">
		<div id="wpalert-text">
		{if $wpheadertext->wpheadertext_text}{$wpheadertext->wpheadertext_text nofilter}{/if}

		{if $wpheadertextIsFile}
			{if $wpheadertext->wpheadertext_image_link}<a href="{$wpheadertext->wpheadertext_image_link|escape:'htmlall':'UTF-8'}" title="{l s='Payment methods' mod='wpheadertext'}">{/if}
				{if $wpheadertext->wpheadertext_file}<img src="{$link->getMediaLink($image_path)|escape:'htmlall':'UTF-8'}" alt="{l s='Payment methods' mod='wpheadertext'}" />{/if}
			{if $wpheadertext->wpheadertext_image_link}</a>{/if}      
		{/if}

		</div>
		{if $wpheadertext->cls_btn == 1}
		<div class="wpalert-header-close">
	    	<svg id="wpclose-small" viewBox="0 0 512 512" width="100%" height="100%">
	    		<title>{l s='Close' mod='wpheadertext'}</title>
	    		<polygon points="512 59.09 452.91 0 256 196.91 59.09 0 0 59.09 196.91 256 0 452.91 59.09 512 256 315.09 452.91 512 512 452.91 315.09 256 512 59.09"></polygon>
	  		</svg>
		</div>
		{/if}
    </div>
</div>  	
