{*
* 2017 Keyrnel
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
* @author Keyrnel
* @copyright  2017 - Keyrnel SARL
* @license commercial
* International Registered Trademark & Property of Keyrnel SARL
*}

<script type="text/javascript">
    var current_shop_id = {$current_shop_id|intval};

	{literal}
	function imageLine(ids, id, path, position, cover, shops, legend, tags, imageLang) {
		var line = $("#lineType").html();
		line = line.replace(/images_id/g, ids);
		line = line.replace(/image_id/g, id);
		line = line.replace(/(\/)?[a-z]{0,2}-default/g, function($0, $1){
			return $1 ? $1 + path : $0;
		});
		line = line.replace(/image_path/g, path);
		line = line.replace(/image_position/g, position);
		line = line.replace(/icon-check-empty/g, cover);
		line = line.replace(/<tbody>/gi, "");
		line = line.replace(/<\/tbody>/gi, "");

		{/literal}
		{foreach from=$languages item=language}
			var idLang = {$language.id_lang|intval};
			line = line.replace('legendVal_'+idLang, legend.hasOwnProperty(idLang) ? legend[idLang] : '');
			line = line.replace('tagsVal_'+idLang, tags.hasOwnProperty(idLang) ? tags[idLang] : '');
		{/foreach}
		{literal}

		if (shops != false)
		{
			$.each(shops, function(key, value){
				if (value == 1)
					line = line.replace('id="' + key + '' + id + '"','id="' + key + '' + id + '" checked=checked');
			});
		}

		$("#imageList").append(line);
        $('#imageList tr#'+id).find('select[name="imageLang"] option[value='+imageLang+']').attr('selected', 'selected')
		$('#imageList tr#'+id).find('input[class="tagify"]').tagify();
	}

    function imageLineAmount(id_currency, currency_sign, id_image, path, default_amount, auto) {
        var id_currency_selected = $('#currency_selector').val();
        id_currency != id_currency_selected ?  $('#lineTypeAmount').find('.currency-field').hide() : $('#lineTypeAmount').find('.currency-field').show();

        $('#lineTypeAmount').find('input[name="default_amount_id_image"]').removeAttr('value').attr('value', default_amount);
        $('#lineTypeAmount').find('input[name="auto_select_amount_id_image"]').removeAttr('checked');
        if (auto) {
            $('#lineTypeAmount').find('input[name="auto_select_amount_id_image"]').attr('checked', true)
        }

        var line = $('#lineTypeAmount').html();
		line = line.replace(/id_currency/g, id_currency);
		line = line.replace(/currency_sign/g, currency_sign);
		line = line.replace(/id_image/g, id_image);
		line = line.replace(/(\/)?[a-z]{0,2}-default/g, function($0, $1){
			return $1 ? $1 + path : $0;
		});

		$("#imageListAmount").append(line);
	}

	function changeCartRuleCombination(){
		if ($('#use_cart_rule_off').prop('checked'))
			$('#cart_rule').hide();
		else
			$('#cart_rule').show();
	}

    function hideOtherCurrency(id) {
        $('.currency-field').hide();
        $('.currency-'+id).show();
    }

	$(document).ready(function(){
		{/literal}
        {foreach from=$products_data[{$default_currency|intval}].images item=image}
            assoc = {literal}"{"{/literal};
            {if $shops}
                {foreach from=$shops item=shop}
                    assoc += '"{$shop.id_shop|intval}" : {if $image->isAssociatedToShop($shop.id_shop)}1{else}0{/if},';
                {/foreach}
            {/if}
            if (assoc != {literal}"{"{/literal})
            {
                assoc = assoc.slice(0, -1);
                assoc += {literal}"}"{/literal};
                assoc = jQuery.parseJSON(assoc);
            }
            else
                assoc = false;

            var legend = {};
            {foreach from=$image->legend key=id_lang item=value}
                legend["{$id_lang|intval}"] = "{$value|escape:'html':'UTF-8'}";
            {/foreach}

            var tags = {};
            {foreach from=$products_data[{$default_currency|intval}].tags[$image->id] key=id_lang item=value}
                tags["{$id_lang|intval}"] = "{$value|escape:'html':'UTF-8'}";
            {/foreach}

            imageLine("{$products_data[{$default_currency|intval}].images_id[{$image->position|intval}]|escape:'html':'UTF-8'}", {$image->id|intval}, "{$image->getExistingImgPath()|escape:'html':'UTF-8'}", {$image->position|intval}, "{if $image->cover}icon-check-sign{else}icon-check-empty{/if}", assoc, legend, tags, {$products_data[{$default_currency|intval}].images_lang.{$image->id}|intval});

        {/foreach}

        {foreach from=$products_data key=id_currency item=data}
            {foreach from=$data.images item=image}
                {if isset($data.images_amount[$image->id])}
                    imageLineAmount({$id_currency|intval}, "{$data.currency.sign|escape:'html':'UTF-8'}", {$image->id|intval}, "{$image->getExistingImgPath()|escape:'html':'UTF-8'}", "{$data.images_amount[$image->id].default_amount|escape:'html':'UTF-8'}", {$data.images_amount[$image->id].auto|intval});
                {/if}
            {/foreach}
        {/foreach}

		{literal}
		var originalOrder = false;

		$("#imageTable").tableDnD({
            dragHandle: 'dragHandle',
			onDragClass: 'myDragClass',
			onDragStart: function(table, row) {
					originalOrder = $.tableDnD.serialize();
					reOrder = ':even';
					if (table.tBodies[0].rows[1] && $('#' + table.tBodies[0].rows[1].id).hasClass('alt_row'))
							reOrder = ':odd';
					$(table).find('#' + row.id).parent('tr').addClass('myDragClass');
			},
			onDrop: function(table, row) {
				if (originalOrder != $.tableDnD.serialize()) {
					current = $(row).attr("id");
					stop = false;
					image_up = "{";
					$("#imageList").find("tr").each(function(i) {
						$("#td_" +  $(this).attr("id")).html('<div class="dragGroup"><div class="positions">'+(i + 1)+'</div></div>');
						if (!stop || (i + 1) == 2)
							image_up += '"' + $(this).attr("data-ids") + '" : ' + (i + 1) + ',';
					});
					image_up = image_up.slice(0, -1);
					image_up += "}";
					updateImagePosition(image_up);
				}
			}
		});

        $('#currency_selector').on('change', function() {
            hideOtherCurrency(this.value);
        });

		function afterDeleteProductImage(selector, data) {
			data = $.parseJSON(data);
			if (data.confirmations.length != 0) {
				cover = 0;
				if (selector.hasClass('icon-check-sign'))
					cover = 1;
				selector.remove();
				if (cover)
					$("#imageTable tr").eq(1).find(".covered").addClass('icon-check-sign');
				refreshImagePositions($("#imageTable"));

                if (data.deleted_ids.length != 0) {
                    $.each(data.deleted_ids, function(key, id) {
                        $('#default_amount_' + id).remove();
                    });
                }

				showSuccessMessage(data.confirmations);
			} else {
                $.each(data.errors, function(k, v) {
                    showErrorMessage(v);
                });
            }

		}

		function refreshImagePositions(imageTable) {
			var reg = /_[0-9]$/g;
			var up_reg  = new RegExp("imgPosition=[0-9]+&");

			imageTable.find("tbody tr").each(function(i,el) {
				$(el).find("td.positionImage").html(i + 1);
			});
			imageTable.find("tr td.dragHandle a:hidden").show();
			imageTable.find("tr td.dragHandle:first a:first").hide();
			imageTable.find("tr td.dragHandle:last a:last").hide();
		}

		$(document).on('click', '.delete_product_image', function() {
            var selector = $(this).closest('tr');
			var ids = selector.attr('data-ids');
			data = {
				"action":"deleteProductImage",
				"ids_image":ids,
				"id_category" : {/literal}{$id_category|intval}{literal},
				"token" : "{/literal}{$currentToken|escape:'html':'UTF-8'}{literal}",
				"ajax" : 1
			};

			if (confirm("{/literal}{l s='Are you sure?' js=1}{literal}"))
			$.ajax({
				url : "{/literal}{$currentIndex|escape:'quotes':'UTF-8'}{literal}",
				data : data,
				type : 'POST',
				success : function(data) {
					return afterDeleteProductImage(selector, data);
				},
				error : function(data) {
					alert("[TECHNICAL ERROR]");
				}
			});
		});

        $(document).on('click', '.image_shop', function() {
            if ($(this).prop('checked')) {
                $(this).attr("checked", "checked")
            } else {
                $(this).removeAttr('checked')
            }
        });

		$(document).on('click', '.covered', function() {
            var self = $(this);
            var id = self.closest('tr').attr('id');
			var ids = self.closest('tr').attr('data-ids');
            var previousCover;

			$("#imageList .cover i").each( function(i) {
                if ($(this).hasClass('icon-check-sign')) {
                    previousCover = $(this);
                }

				$(this).removeClass('icon-check-sign').addClass('icon-check-empty');
			});


			if (current_shop_id != 0)
				$('#' + current_shop_id + id).attr('check', true);
			else
				self.parent().parent().parent().children('td input').attr('check', true);

			var data = {
				"action":"updateCover",
				"ids_image":ids,
				"token" : "{/literal}{$currentToken|escape:'html':'UTF-8'}{literal}",
				"ajax" : 1
			};

			$.ajax({
				url : "{/literal}{$currentIndex|escape:'quotes':'UTF-8'}{literal}",
				data : data,
				type : 'POST',
				success : function(data){
					data = $.parseJSON(data);
					if (data.confirmations.length != 0) {
                        self.addClass('icon-check-sign');
                        showSuccessMessage(data.confirmations);
                    } else {
                        if (typeof previousCover !== 'undefined') {
                            previousCover.addClass('icon-check-sign');
                        }

                        $.each(data.errors, function(k, v) {
                            showErrorMessage(v);
                        });
                    }
				},
				error : function(data){
					alert("[TECHNICAL ERROR]");
				}
			});

		});

		function updateImagePosition(json) {
			var data = {
				"action":"updateImagePosition",
				"json":json,
				"token" : "{/literal}{$currentToken|escape:'quotes':'UTF-8'}{literal}",
				"ajax" : 1
			};

			$.ajax({
				url : "{/literal}{$currentIndex|escape:'quotes':'UTF-8'}{literal}",
				data : data,
				type : 'POST',
				success : function(data){
					data = $.parseJSON(data);
					if (data.confirmations.length != 0) {
						showSuccessMessage(data.confirmations);
					} else {
                        $.each(data.errors, function(k, v) {
                            showErrorMessage(v);
                        });
                    }
				},
				error : function(data){
					alert("[TECHNICAL ERROR]");
				}
			});
		}

		// update tags & caption
		$(document).on('click', '.update_caption_tags', function() {
			var selector = $(this).closest('tr');
			var ids_image = selector.attr('data-ids');
            var image_lang = selector.find('select[name="imageLang"]').val();
			var legends = [];
            var tags = [];
            var image_shops = [];

            selector.find('input[name="imageShop"]:checked').each(function() {
                var shop = $(this).val();
                image_shops.push(shop);
            });

			selector.find('input[name^="legend"]').each(function() {
				var id_lang = $(this).attr('id').substring($(this).attr('id').lastIndexOf("_") + 1);
				var legend = {
					id_lang: id_lang,
					value: $(this).val()
				}
				legends.push(legend);
			});

			var tags = [];
			selector.find('input[name^="tags"]').each(function() {
				var id_lang = $(this).attr('id').substring($(this).attr('id').lastIndexOf("_") + 1);
				var tag = {
					id_lang: id_lang,
					value: $(this).val()
				}
				tags.push(tag);
			});

			var data = {
				"action":"updateCaptionTags",
				"ids_image":ids_image,
                "image_lang":image_lang,
				"legends":legends,
                "shops":image_shops,
				"tags":tags,
				"token" : "{/literal}{$currentToken|escape:'html':'UTF-8'}{literal}",
				"ajax" : 1
			};

			$.ajax({
				url : "{/literal}{$currentIndex|escape:'quotes':'UTF-8'}{literal}",
				data : data,
				type : 'POST',
				success : function(data){
					data = $.parseJSON(data);
					if (data.confirmations.length != 0) {
						showSuccessMessage(data.confirmations);
					} else {
                        $.each(data.errors, function(k, v) {
                            showErrorMessage(v);
                        });
                    }
				},
				error : function(data){
					alert("[TECHNICAL ERROR]");
				}
			});
		});

		changeCartRuleCombination();
	});

	{/literal}
</script>

<div class="panel">
	<div class="panel-heading">
		<i class="icon-cogs"></i> {l s='Templates' mod='thegiftcard'}
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="gc_tooltip">
				{l s='Image' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			{$uploader  nofilter}{* HTML, cannot escape *}
		</div>
	</div>
	<div class="table-responsive">
		<table class="table tableDnD" id="imageTable">
			<thead>
				<tr class="nodrag nodrop">
					<th class="fixed-width-sm"><span class="title_box">{l s='Image' mod='thegiftcard'}</span></th>
					<th class="fixed-width-xs"><span class="title_box">{l s='Cover' mod='thegiftcard'}</span></th>
					<th class="fixed-width-xs"><span class="title_box">{l s='Position' mod='thegiftcard'}</span></th>
					<th class="fixed-width-lg"><span class="title_box">{l s='Caption' mod='thegiftcard'}</span></th>
					<th class="fixed-width-lg"><span class="title_box">{l s='Tags' mod='thegiftcard'}</span></th>
					<th class="fixed-width-lg"><span class="title_box">{l s='Lang visibility' mod='thegiftcard'}</span></th>
                    {if $shops}
    					{foreach from=$shops item=shop}
    						<th class="fixed-width-xs"><span class="title_box">{$shop.name}</span></th>
    					{/foreach}
    				{/if}
					<th class="fixed-width-lg"></th> <!-- action -->
				</tr>
			</thead>
			<tbody id="imageList">
			</tbody>
		</table>
		<table id="lineType" style="display:none;">
			<tr id="image_id" data-ids="images_id">
				<td>
					<a href="{$smarty.const._THEME_PROD_DIR_|escape:'html':'UTF-8'}image_path.jpg" class="fancybox">
						<img
							src="{$smarty.const._THEME_PROD_DIR_|escape:'html':'UTF-8'}{$iso_lang|escape:'html':'UTF-8'}-default-{$imageType|escape:'html':'UTF-8'}.jpg"
							alt="legend"
							title="legend"
							class="img-thumbnail" />
					</a>
				</td>
				<td class="cover">
					<a href="#" style="margin-left:8px">
						<i class="icon-check-empty icon-2x covered"></i>
					</a>
				</td>
				<td id="td_image_id" class="pointer dragHandle center positionImage">
					<div class="dragGroup">
						<div class="positions">
							image_position
						</div>
					</div>
				</td>

				<td class="caption">
					{foreach from=$languages item=language}
						{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang|intval}">
							<div class="col-lg-8">
						{/if}
								<input type="text" id="legend_{$language.id_lang|intval}" name="legend[{$language.id_lang|intval}]" value="legendVal_{$language.id_lang|intval}"/>
						{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="gc_dropdown" tabindex="-1">
									{$language.iso_code|escape:'quotes':'UTF-8'}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
									<li>
										<a href="javascript:hideOtherLanguage({$language.id_lang|intval});">{$language.name|escape:'quotes':'UTF-8'}</a>
									</li>
									{/foreach}
								</ul>
							</div>
						</div>
						{/if}
					{/foreach}
				</td>
				<td class="tag">
					{foreach from=$languages item=language}
						{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang|intval}">
							<div class="col-lg-8">
						{/if}
								<input type="text" id="tags_{$language.id_lang|intval}" class="tagify" name="tags[{$language.id_lang|intval}]" value="tagsVal_{$language.id_lang|intval}"/>
						{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="gc_dropdown" tabindex="-1">
									{$language.iso_code|escape:'quotes':'UTF-8'}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
									<li>
										<a href="javascript:hideOtherLanguage({$language.id_lang|intval});">{$language.name|escape:'quotes':'UTF-8'}</a>
									</li>
									{/foreach}
								</ul>
							</div>
						</div>
						{/if}
					{/foreach}
				</td>
                <td class="image_lang">
                    <select name="imageLang">
                        <option value="0">{l s='All languages' mod='thegiftcard'}</option>
                        {foreach from=$languages item=language}
                        <option value="{$language.id_lang|intval}">{$language.name|escape:'html':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </td>
                {if $shops}
    				{foreach from=$shops item=shop}
    				<td>
    					<input
    						type="checkbox"
    						class="image_shop"
    						name="imageShop"
    						id="{$shop.id_shop}image_id"
    						value="{$shop.id_shop}" />
    				</td>
    				{/foreach}
    			{/if}
				<td>
					<a href="#" class="update_caption_tags btn btn-default">
						<i class="icon-random"></i> {l s='Update' mod='thegiftcard'}
					</a>
					<a href="#" class="delete_product_image btn btn-default" >
						<i class="icon-trash"></i> {l s='Delete this image' mod='thegiftcard'}
					</a>
				</td>
			</tr>
		</table>
	</div>
	<div class="panel-footer">
		<button type="submit" name="submitGiftCard" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='thegiftcard'}</button>
	</div>
</div>

<div class="panel">
	<div class="panel-heading">
		<i class="icon-cogs"></i> {l s='Amounts' mod='thegiftcard'}
	</div>
	<div class="alert alert-info">
		{l s='You must enter the custom amount limit, then pick up fixed amounts which will be direclty selectable by customers in front-office.' mod='thegiftcard'}
	</div>
    <div class="form-group">
		<label class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='Select currency you want to edit' mod='thegiftcard'}">
				{l s='Currency' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			<select id="currency_selector" name="currency_selector">
                {foreach from=$currencies item=currency}
                <option value="{$currency.id_currency|intval}" {if $currency.id_currency == $default_currency}selected="selected"{/if}>{$currency.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
		</div>
	</div>
    <div class="form-group">
		<label class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='Enable to active the gift card for the selected currency' mod='thegiftcard'}">
				{l s='Active' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
            {foreach from=$products_data key=id_currency item=data}
            <span class="switch prestashop-switch fixed-width-lg currency-field currency-{$id_currency|intval}" {if $id_currency != $default_currency}style="display:none;"{/if}>
				<input type="radio" name="active_{$data.product->id|intval}" id="active_on_{$data.product->id|intval}" value="1" {if $data.active|intval}checked="checked"{/if}/>
				<label class="t" for="active_on_{$data.product->id|intval}">{l s='Yes' mod='thegiftcard'}</label>
				<input type="radio" name="active_{$data.product->id|intval}" id="active_off_{$data.product->id|intval}" value="0" {if !$data.active|intval}checked="checked"{/if}/>
				<label class="t" for="active_off_{$data.product->id|intval}">{l s='No' mod='thegiftcard'}</label>
				<a class="slide-button btn"></a>
			</span>
            {/foreach}
		</div>
	</div>
    <div class="form-group">
		<label class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='Select tax applied to the gift card' mod='thegiftcard'}">
				{l s='Tax' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
            {foreach from=$products_data key=id_currency item=data}
			<select name="id_tax_rules_group_{$data.product->id|intval}" class="currency-field currency-{$id_currency|intval}" {if $id_currency != $default_currency}style="display:none;"{/if}>
                {foreach from=$tax_rules_groups item=group}
                <option value="{$group.id_tax_rules_group|intval}" {if $group.id_tax_rules_group == $data.id_tax_rules_group}selected="selected"{/if}>{$group.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
            {/foreach}
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='Set the limit of custom amounts' mod='thegiftcard'}">
				{l s='Custom amount' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="row">
				<div class="col-sm-6">
                    {foreach from=$products_data key=id_currency item=data}
                    <div class="input-group currency-field currency-{$id_currency|intval}" {if $id_currency != $default_currency}style="display:none;"{/if}>
                        <span class="input-group-addon">{l s='from' mod='thegiftcard'}</span>
                        <input type="text" class="input-medium" name="custom_amount_from_{$data.product->id|intval}" value="{$data.custom_amounts.from|intval}" />
                        <span class="input-group-addon">{$data.currency.sign|escape:'html':'UTF-8'}</span>
                    </div>
                    {/foreach}
				</div>
				<div class="col-sm-6">
                    {foreach from=$products_data key=id_currency item=data}
                    <div class="input-group currency-field currency-{$id_currency|intval}" {if $id_currency != $default_currency}style="display:none;"{/if}>
                        <span class="input-group-addon">{l s='to' mod='thegiftcard'}</span>
                        <input type="text" class="input-medium" name="custom_amount_to_{$data.product->id|intval}" value="{$data.custom_amounts.to|intval}" />
                        <span class="input-group-addon">{$data.currency.sign|escape:'html':'UTF-8'}</span>
                    </div>
                    {/foreach}
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="custom_amount_pitch">
			<span class="label-tooltip" data-toggle="gc_tooltip"
				title="{l s='Set the pitch between each custom amount. More the pitch is low, more the process will be long.' mod='thegiftcard'}">
				{l s='Pitch between each custom amount' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="row">
				<div class="col-lg-3">
                    {foreach from=$products_data key=id_currency item=data}
                    <div class="input-group currency-field currency-{$id_currency|intval}" {if $id_currency != $default_currency}style="display:none;"{/if}>
                        <input type="text" class="input-medium" name="custom_amount_pitch_{$data.product->id|intval}" value="{$data.pitch|intval}" />
                        <span class="input-group-addon">{$data.currency.sign|escape:'html':'UTF-8'}</span>
                    </div>
                    {/foreach}
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='Each amount has to be followed by a comma.' mod='thegiftcard'}">
				{l s='Fixed amount' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
            {foreach from=$products_data key=id_currency item=data}
            <div class="input-group currency-field currency-{$id_currency|intval}" {if $id_currency != $default_currency}style="display:none;"{/if}>
                <input type="text" class="input-medium" name="fixed_amounts_{$data.product->id|intval}" value="{$data.fixed_amounts|escape:'html':'UTF-8'}" />
                <span class="input-group-addon">{$data.currency.sign|escape:'html':'UTF-8'}</span>
            </div>
            {/foreach}
		</div>
	</div>
    <div class="form-group">
		<label class="control-label col-lg-3" for="p_default">
			<span class="label-tooltip" data-toggle="gc_tooltip"
				title="{l s='Set the default amount which will be displayed on front-office' mod='thegiftcard'}">
				{l s='Default amount' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="panel default_amounts">
                <div id="imageListAmount">
                </div>
                <div id="lineTypeAmount" style="display:none;">
                    <div id="default_amount_id_image" class="currency-field currency-id_currency">
                        <img
                            src="{$smarty.const._THEME_PROD_DIR_|escape:'html':'UTF-8'}{$iso_lang|escape:'html':'UTF-8'}-default-{$imageType|escape:'html':'UTF-8'}.jpg"
                            alt="legend"
                            title="legend"
                            class="img-thumbnail"
                        />
                        <div>
                            <div class="input-group">
                                <span class="input-group-addon">{l s='Amount' mod='thegiftcard'}</span>
                                <input type="text" class="input-medium" name="default_amount_id_image" value="" />
                                <span class="input-group-addon">currency_sign</span>
                            </div>
                            <div class="checkbox">
                                <label class="">
                                    <input type="checkbox" name="auto_select_amount_id_image" value="1">
                                    {l s='Automatically select this amount on template click' mod='thegiftcard'}
                                </label>
                            </div>
                        </div>

                    </div>
                </div>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<button type="submit" name="submitGiftCard" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='thegiftcard'}</button>
	</div>
</div>

<div class="panel">
	<div class="panel-heading">
		<i class="icon-cogs"></i> {l s='Settings' mod='thegiftcard'}
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3 required" for="datetime">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='The default period is one year.' mod='thegiftcard'}">
				{l s='Datetime validity' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-6">
			<div class="row">
				<div class="col-lg-3">
					<input type="text" name="expiration_time" id="expiration_time" value="{Configuration::get('GIFTCARD_EXPIRATION_TIME')|intval}">
				</div>
				<div class="col-lg-3">
					<select name="expiration_date" id="expiration_date">
						<option value="day" {if Configuration::get('GIFTCARD_EXPIRATION_DATE') == 'day'}selected="selected"{/if}>{l s='Day' mod='thegiftcard'}</option>
						<option value="month" {if Configuration::get('GIFTCARD_EXPIRATION_DATE') == 'month'}selected="selected"{/if}>{l s='Month' mod='thegiftcard'}</option>
						<option value="year" {if Configuration::get('GIFTCARD_EXPIRATION_DATE') == 'year'}selected="selected"{/if}>{l s='Year' mod='thegiftcard'}</option>
					</select>
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='Enable to automatically delete deprecated combinations created by users. (reduce module load time)' mod='thegiftcard'}">
				{l s='Automatically delete unused gift cart combinations' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" name="use_cache" id="use_cache_on" value="1" {if Configuration::get('GIFTCARD_USE_CACHE')|intval}checked="checked"{/if} />
				<label class="t" for="use_cache_on">{l s='Yes' mod='thegiftcard'}</label>
				<input type="radio" name="use_cache" id="use_cache_off" value="0" {if !Configuration::get('GIFTCARD_USE_CACHE')|intval}checked="checked"{/if} />
				<label class="t" for="use_cache_off">{l s='No' mod='thegiftcard'}</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="gc_tooltip"
			title="{l s='Enable to allow or not the combination of gift cards with other cart rules' mod='thegiftcard'}">
				{l s='Use cart rule combinations feature' mod='thegiftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" name="use_cart_rule" id="use_cart_rule_on" value="1" {if Configuration::get('GIFTCARD_USE_CART_RULE')|intval}checked="checked"{/if} onchange="changeCartRuleCombination()"/>
				<label class="t" for="use_cart_rule_on">{l s='Yes' mod='thegiftcard'}</label>
				<input type="radio" name="use_cart_rule" id="use_cart_rule_off" value="0" {if !Configuration::get('GIFTCARD_USE_CART_RULE')|intval}checked="checked"{/if} onchange="changeCartRuleCombination()"/>
				<label class="t" for="use_cart_rule_off">{l s='No' mod='thegiftcard'}</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>
	<div id="cart_rule" class="form-group">
		<label class="control-label col-lg-3">
			<span>{l s='Allow combinations with other cart rules' mod='thegiftcard'}</span>
		</label>
		<div class="col-lg-9">
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" name="cart_rule" id="cart_rule_on" value="1" {if Configuration::get('GIFTCARD_CART_RULE')|intval}checked="checked"{/if} />
				<label class="t" for="cart_rule_on">{l s='Yes' mod='thegiftcard'}</label>
				<input type="radio" name="cart_rule" id="cart_rule_off" value="0" {if !Configuration::get('GIFTCARD_CART_RULE')|intval}checked="checked"{/if} />
				<label class="t" for="cart_rule_off">{l s='No' mod='thegiftcard'}</label>
				<a class="slide-button btn"></a>
			</span>
		</div>
	</div>
	<div class="panel-footer">
		<button type="submit" name="submitGiftCard" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='thegiftcard'}</button>
	</div>
</div>
