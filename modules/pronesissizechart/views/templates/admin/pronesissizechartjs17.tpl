{*
* 2007-2019 PrestaShop
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
*  @copyright  2007-2019 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{literal}
$(document).ready(function() {

		$('#psc_buttons_container .psc_button').toggleClass('btn-danger btn-success');
		$('#psc_buttons_container .psc_button').find('i').toggleClass('icon-check icon-times');
		
		$(document).on('click', '.psc_button', function() {
			$(this).toggleClass('btn-danger btn-success');
			$(this).find('i').toggleClass('icon-check icon-times');
			$('.'+$(this).attr('rel')).toggle();
		});
		
		$(document).on('click', '#addcol', function() {
	    var $tablerow = $('table.table-editor').find('tbody tr');
	    count = 0;
			var newcols = $("table.table-editor").find("tr:first td").length-1;
	    $tablerow.each(function(index, value){
	        count += 1;
	        var $listitem = $(this);
	        n = parseInt($listitem.index());
	        nval = $(this).closest('tr').attr('id');
	        cval = parseInt($(this).children().last().attr('id'))+1;
	        var $newCol = '<td class="psc_td" id="'+cval+'">';
{/literal}
	        {foreach from=$languages item=language}
	        	style = ($('.psc_button[rel="psc_lang{$language.id_lang|intval}"]').hasClass('btn-success')) ? 'block' : 'none';
	        	$newCol += '<div class="row psc_lang{$language.id_lang|intval}" style="display:'+ style +'"><div class="table-flag"><img src="{$base_url}img/l/{$language.id_lang|intval}.jpg"></div><div class="col-md-12"><input type="text"  class="form-control" name="cell['+nval+']['+cval+'][{$language.id_lang|intval}]" id="r'+nval+'-c'+cval+'-l{$language.id_lang|intval}"></div></div>';
	        {/foreach}
{literal}
	        $newCol+= '</td>';
	        $("table.table-editor tbody tr:eq(" + n + ")").append($newCol);
	    });
	    $("#remove-cols").append("<td class='remove'><a class='btn'><i class='material-icons'>delete</i></a></td>");
		});

		$(document).on('click', '#addrow', function() {
				addedrow = parseInt($('#table1 tr:last').attr('id'))+1;
		    $('table.table-editor').append('<tr class="remove-row" id="'+addedrow+'"></tr>');
		    $('table.table-editor tr:eq(0) td').each(function(index, value){
		    		if(index==0)
		    		{
		        	$("table.table-editor tr:eq(-1)").append('<td class="remrow" width="3%"><a class="btn"><i class="material-icons">delete</i></a></td>');
		        } 
		        else 
		        {
		        	var $newRow = '<td class="psc_td" id="'+(index-1)+'">';
{/literal}
		        	{foreach from=$languages item=language}
		        		style = ($('.psc_button[rel="psc_lang{$language.id_lang|intval}"]').hasClass('btn-success')) ? 'block' : 'none';
								$newRow += '<div class="row psc_lang{$language.id_lang|intval}" style="display:'+ style +'"><div class="table-flag"><img src="{$base_url}img/l/{$language.id_lang|intval}.jpg"></div><div class="col-md-12"><input type="text"  class="form-control" name="cell['+addedrow+']['+(index-1)+'][{$language.id_lang|intval}]" id="r'+addedrow+'-c'+(index-1)+'-l{$language.id_lang|intval}"></div></div>';
		        	{/foreach}
{literal}
		        	$newRow += '</td>';
		        	$("table.table-editor tr:eq(-1)").append($newRow);
		        }
		    });
		});

		$(document).on('click', 'td.remrow .btn', function() {
			$(this).parent().parent().remove();
		});

		$(document).on('click', 'td.remove .btn', function() {
		    var ndx = $(this).parent().index() + 1;
		    $('td:nth-child(' +ndx+ ')').remove();
		});
});
{/literal}
