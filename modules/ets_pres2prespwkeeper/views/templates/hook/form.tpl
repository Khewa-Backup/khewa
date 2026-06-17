{*
* 2007-2018 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
* 
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2018 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<form action="" method="post">
	<fieldset>
	<legend><img src="../img/admin/contact.gif" />{l s='Config' mod='ets_pres2prespwkeeper'}</legend>
		<table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
			<tr>
                <td width="130" style="height: 35px;">{l s='_COOKIE_KEY_ Source Prestashop website.' mod='ets_pres2prespwkeeper'}</td>
                <td><input type="text" name="ETS_NEW_COOKIE_KEY" value="{$ETS_NEW_COOKIE_KEY|escape:'html':'UTF-8'}" style="width: 300px;" /></td>
            </tr>
			<tr>
                <td colspan="2" align="center">
                    <input class="button" name="btnSubmit" value="{l s='Update settings' mod='ets_pres2prespwkeeper'}" type="submit" />
                </td>
            </tr>
		</table>
	</fieldset>
</form>