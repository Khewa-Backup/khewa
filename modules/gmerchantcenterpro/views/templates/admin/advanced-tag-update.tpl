{*
*
* Google merchant center Pro
*
* @author    BusinessTech.fr - https://www.businesstech.fr
* @copyright Business Tech - https://www.businesstech.fr
* @license   Commercial
*
*           ____    _______
*          |  _ \  |__   __|
*          | |_) |    | |
*          |  _ <     | |
*          | |_) |    | |
*          |____/     |_|
*
*}
<div class="bootstrap">
	{if !empty($bUpdate)}
		<div class="alert alert-success">{l s='Your tags have been updated' mod='gmerchantcenterpro'}</div>
	{elseif !empty($aErrors)}
		{include file="`$sErrorInclude`"}
	{/if}
</div>

<div class="clr_hr"></div>
<div class="clr_20"></div>