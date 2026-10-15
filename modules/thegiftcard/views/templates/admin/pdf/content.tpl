{**
* 2023 - Keyrnel
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
* @author    Keyrnel
* @copyright 2023 - Keyrnel
* @license   commercial
* International Registered Trademark & Property of Keyrnel
*}

{$style nofilter}{* HTML, cannot escape *}

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table">
	<!-- Content -->
	<tr>
		<td width="100%" align="center">
			<h3>{$translations.headline|escape:'html':'UTF-8'}</h3>
			<p>
				<span style="font-size:11pt;">{$translations.subhead|escape:'html':'UTF-8'}</span>
			</p>
		</td>
	</tr>

	<tr>
		<td width="100%" height="30pt">&nbsp;</td>
	</tr>

	<tr>
		<td width="25%">&nbsp;</td>
		<td width="50%" align="center">
			<img src="{$image.url|escape:'html':'UTF-8'}" alt="{$translations.alt|escape:'html':'UTF-8'}" width="{$image.width|intval}px"
				height="{$image.height|intval}px" />
		</td>
		<td width="25%">&nbsp;</td>
	</tr>

	<tr>
		<td width="100%" height="30pt">&nbsp;</td>
	</tr>

	{if isset($translations.text)}
		<tr>
			<td width="100%" align="center">
				<p>
					<span style="font-size:11pt;">{$translations.text|escape:'html':'UTF-8'}</span>
				</p>
			</td>
		</tr>

		<tr>
			<td width="100%" height="30pt">&nbsp;</td>
		</tr>
	{/if}

	<tr>
		<td width="100%" class="panel">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tbody>
					<tr>
						<td width="100%" height="10pt">&nbsp;</td>
					</tr>
					<tr>
						<td width="10%">&nbsp;</td>
						<td width="80%">
							<h4>{$translations.information_headline|escape:'html':'UTF-8'}</h4>
							<p>
								<span>{$translations.information_1|escape:'html':'UTF-8'}</span><br>
								<span>{$translations.information_2|escape:'html':'UTF-8'}</span><br>
								<span>{$translations.information_3|escape:'html':'UTF-8'}</span>
							</p>
						</td>
						<td width="10%">&nbsp;</td>
					</tr>
					<tr>
						<td width="100%" height="10pt">&nbsp;</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>

	<tr>
		<td width="100%" height="30pt">&nbsp;</td>
	</tr>

	<tr>
		<td width="100%" class="panel">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tbody>
					<tr>
						<td width="100%" height="10pt">&nbsp;</td>
					</tr>
					<tr>
						<td width="10%">&nbsp;</td>
						<td width="80%">
							<h4>{$translations.howto_headline|escape:'html':'UTF-8'}</h4>
							<p>
								<span>{$translations.howto_content|escape:'html':'UTF-8'}</span>
							</p>
						</td>
						<td width="10%">&nbsp;</td>
					</tr>
					<tr>
						<td width="100%" height="10pt">&nbsp;</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>

	<tr>
		<td width="100%" height="30pt">&nbsp;</td>
	</tr>
</table>