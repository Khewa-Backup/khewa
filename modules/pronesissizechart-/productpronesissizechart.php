<?php
/**
* 2007-2014 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
$option = (int)Tools::getValue('option');
$ajax = (int)Tools::getValue('ajax');
$reloadtable = trim(Tools::getValue('reloadtable'));
$reloaddescription = trim(Tools::getValue('reloaddescription'));
$langtext = Tools::unSerialize(urldecode(Tools::getValue('langtext')));

if ($ajax != 1) die();

$default_table = '';
$default_description = '';
$languages = Language::getLanguages();
if ($reloadtable != '')
{
	$default_table = $reloadtable;
	$default_description = $reloaddescription;
}
elseif ($option > 0)
{
	if ($res = Db::getInstance()->getRow('select value,description from `'._DB_PREFIX_.'pronesissizechartgroups` where psc_group_id='.(int)$option))
		$default_table = $res['value'];
	$default_description = $res['description'];
}
elseif ($option == -1)
{
	$keys = array();
	foreach ($languages as $lang)
	{
		$keys[] = $lang['id_lang'];
		$default_description[$lang['id_lang']] = '';
	}
	$cell = array_fill(0, 3, array_fill(0, 3, array_fill_keys($keys, '')));
	$default_table = urlencode(serialize($cell));
	$default_description = urlencode(serialize($default_description));
}
else
{
	echo 'false';
	exit();
}

$description = array();
$description = Tools::unSerialize(urldecode($default_description));
$value = array();
$value = Tools::unSerialize(urldecode($default_table));

$max_height = count($value);
$max_width = max(array_map('count', $value));

$firstcol = 0;
$firstrow = 0;

$html_description = '<div class="row" style="padding-top: 5px;"><br/>
<label>'.$langtext[0].'</label></div><br/>';
foreach ($languages as $lang)
	$html_description .= '<div class="row" style="margin-bottom:10px"><div class="col-xs-1" style="text-align:center"><img src="'.
											__PS_BASE_URI__.'img/l/'.$lang['id_lang'].
											'.jpg" style="padding-top:8px"></div><div class="col-xs-11"><input type="text" name="description['.
											$lang['id_lang'].']" value="'.htmlentities($description[$lang['id_lang']], ENT_QUOTES, 'UTF-8').'"></div></div>';

$buttons = '';
foreach ($languages as $lang)
	$buttons .= '<a class="psc_button btn btn-danger" rel="psc_lang'.$lang['id_lang'].'"><i class="icon-times"></i> '.
							$lang['name'].' <img src="'.__PS_BASE_URI__.'img/l/'.$lang['id_lang'].'.jpg"></a>';

$html_content = '<label id="psc_lang_desc" '.((count($languages) > 1) ? : 'style="display:none"').
								'> '.$langtext[1].'</label><div id="psc_buttons_container" '.
								((count($languages) > 1) ? : 'style="display:none"').'>'.$buttons.
								'</div><div class="col-lg-12 table-container"><table class="table-editor table" id="table1" style="width:100%;">
								<tfoot><tr id="remove-cols">';
for ($i = 0; $i <= $max_width; $i++)
{
	if ($firstcol <= 1)
		$html_content .= '<td><a style="opacity:0" class="btn btn-danger"><i class="icon-trash"></i></a></td>';
	else
		$html_content .= '<td class="remove"><a class="btn btn-danger"><i class="icon-trash"></i></a></td>';
	$firstcol++;
}
$html_content .= '</tr></tfoot><tbody>';
foreach ($value as $rows => $row)
{
	$html_content .= '<tr id="'.$rows.'">';
	if ($firstrow == 0)
		$html_content .= '<td width="3%">&nbsp;</td>';
	else
		$html_content .= '<td class="remrow" width="3%"><a class="btn btn-danger"><i class="icon-trash"></i></a></td>';
	foreach ($row as $cols => $col)
	{
		$html_content .= '<td class="psc_td" id="'.$cols.'">';
		foreach ($languages as $lang)
			$html_content .= '<div class="row psc_lang'.$lang['id_lang'].'" style="display:block"><div class="table-flag"><img src="'.__PS_BASE_URI__.
											'img/l/'.$lang['id_lang'].'.jpg"></div><div class="col-xs-11"><input value="'.htmlentities($col[$lang['id_lang']], ENT_QUOTES, 'UTF-8').
												'" type="text" name="cell['.$rows.']['.$cols.']['.$lang['id_lang'].']" id="r'.
												$rows.'-c'.$cols.'-l'.$lang['id_lang'].'"></div></div>';
		$html_content .= '</td>';
	}
	$html_content .= '</tr>';
	$firstrow++;
}
$html_content .= '</tbody>
</table>
</div>
<div class="row row-add">
<a id="addrow" class="btn btn btn-success add-button" ><i class="icon-minus"></i> '.$langtext[2].'</a>
<a id="addcol" class="btn btn btn-success add-button" ><i class="icon-minus icon-rotate-90"></i> '.$langtext[3].'</a>';
echo $html_description.$html_content;
?>