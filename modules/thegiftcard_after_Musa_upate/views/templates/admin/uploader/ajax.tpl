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
* @copyright  2022 - Keyrnel SARL
* @license commercial
* International Registered Trademark & Property of Keyrnel SARL
*}
<div class="form-group">
	<div class="col-lg-12">
		<input id="{$id|escape:'html':'UTF-8'}" type="file" name="{$name|escape:'html':'UTF-8'}[]"{if isset($url)} data-url="{$url|escape:'quotes':'UTF-8'}"{/if}{if isset($multiple) && $multiple} multiple="multiple"{/if} style="width:0px;height:0px;" />
		<button class="btn btn-default" data-style="expand-right" data-size="s" type="button" id="{$id|escape:'html':'UTF-8'}-add-button">
			<i class="icon-folder-open"></i> {if isset($multiple) && $multiple}{l s='Add files...' mod='thegiftcard'}{else}{l s='Add file...' mod='thegiftcard'}{/if}
		</button>
	</div>
</div>
<div class="well" style="display:none">
	<div id="{$id|escape:'html':'UTF-8'}-files-list"></div>
	<button class="ladda-button btn btn-primary" data-style="expand-right" type="button" id="{$id|escape:'html':'UTF-8'}-upload-button" style="display:none;">
		<span class="ladda-label"><i class="icon-check"></i> {if isset($multiple) && $multiple}{l s='Upload files' mod='thegiftcard'}{else}{l s='Upload file' mod='thegiftcard'}{/if}</span>
	</button>
</div>
<div class="row" style="display:none">
	<div class="alert alert-success" id="{$id|escape:'html':'UTF-8'}-success"></div>
</div>
<div class="row" style="display:none">
	<div class="alert alert-danger" id="{$id|escape:'html':'UTF-8'}-errors"></div>
</div>
<script type="text/javascript">
	function humanizeSize(bytes)
	{
		if (typeof bytes !== 'number') {
			return '';
		}

		if (bytes >= 1000000000) {
			return (bytes / 1000000000).toFixed(2) + ' GB';
		}

		if (bytes >= 1000000) {
			return (bytes / 1000000).toFixed(2) + ' MB';
		}

		return (bytes / 1000).toFixed(2) + ' KB';
	}

	$( document ).ready(function() {
		{if isset($multiple) && isset($max_files)}
			var {$id|escape:'html':'UTF-8'}_max_files = {$max_files - $files|count};
		{/if}

		{if isset($files) && $files}
		$('#{$id|escape:'html':'UTF-8'}-images-thumbnails').parent().show();
		{/if}

		var {$id|escape:'html':'UTF-8'}_upload_button = Ladda.create( document.querySelector('#{$id|escape:'html':'UTF-8'}-upload-button' ));
		var {$id|escape:'html':'UTF-8'}_total_files = 0;

		$('#{$id|escape:'html':'UTF-8'}').fileupload({
			dataType: 'json',
			async: false,
			autoUpload: false,
			previewMaxWidth: 158,
     		previewMaxHeight: 158,
			maxFileSize: Math.abs({$post_max_size|intval}),
			start: function (e) {
				{$id|escape:'html':'UTF-8'}_upload_button.start();
				$('#{$id|escape:'html':'UTF-8'}-upload-button').unbind('click'); //Important as we bind it for every elements in add function
			},
			fail: function (e, data) {
				$('#{$id|escape:'html':'UTF-8'}-errors').html(data.errorThrown.message).parent().show();
				$('#{$id|escape:'html':'UTF-8'}-files-list').html('').parent().hide();
			},
			done: function (e, data) {
				if (data.result) {
					if (typeof data.result.{$name|escape:'html':'UTF-8'} !== 'undefined') {
						for (var i=0; i<data.result.{$name|escape:'html':'UTF-8'}.length; i++) {
							if (typeof data.result.{$name|escape:'html':'UTF-8'}[i].error !== 'undefined' && data.result.{$name|escape:'html':'UTF-8'}[i].error != '') {
								$('#{$id|escape:'html':'UTF-8'}-errors').html('<strong>'+data.result.{$name|escape:'html':'UTF-8'}[i].name+'</strong> : '+data.result.{$name|escape:'html':'UTF-8'}[i].error).parent().show();
								$('#{$id|escape:'html':'UTF-8'}-files-list').html('').parent().hide();
							}
							else
							{
								$(data.context).appendTo($('#{$id|escape:'html':'UTF-8'}-success'));
								$('#{$id|escape:'html':'UTF-8'}-success').parent().show();

								if (data.result.{$name|escape:'html':'UTF-8'}[i] !== null && data.result.{$name|escape:'html':'UTF-8'}[i].status == 'ok')
								{
									var response = data.result.{$name|escape:'html':'UTF-8'}[i];
									var cover = "icon-check-empty";
									var legend = {};
									var tags = {};

									if (response.cover == "1")
										cover = "icon-check-sign";

									// if (typeof response.legend !== 'undefined' && response.legend != null)
									// 	legend = response.legend[{$default_language|intval}];

									imageLine(response.ids, response.id, response.path, response.position, cover, response.shops, legend, tags, 0);
									$("#img" + response.id).remove();
									$("#imageTable").tableDnDUpdate();
								}
							}

						}
					}

					if (data.result.default_amounts.length) {
						$.each(data.result.default_amounts, function(id_image, default_amount) {
							imageLineAmount(
								default_amount.currency.id_currency,
								default_amount.currency.sign,
								default_amount.image.id,
								default_amount.image.path,
								default_amount.amount,
								default_amount.auto
							)
						});
					}

					$(data.context).find('button').remove();
				}
			},
		}).on('fileuploadalways', function (e, data) {
				{$id|escape:'html':'UTF-8'}_total_files--;

				if ({$id|escape:'html':'UTF-8'}_total_files == 0)
				{
					{$id|escape:'html':'UTF-8'}_upload_button.stop();
					$('#{$id|escape:'html':'UTF-8'}-upload-button').unbind('click');
					$('#{$id|escape:'html':'UTF-8'}-files-list').parent().hide();
				}
		}).on('fileuploadadd', function(e, data) {
			if (typeof {$id|escape:'html':'UTF-8'}_max_files !== 'undefined') {
				if ({$id|escape:'html':'UTF-8'}_total_files >= {$id|escape:'html':'UTF-8'}_max_files) {
					e.preventDefault();
					alert('{l s='You can upload a maximum of %s files'|sprintf:$max_files mod='thegiftcard'}');
					return;
				}
			}

			data.context = $('<div/>').addClass('form-group').appendTo($('#{$id|escape:'html':'UTF-8'}-files-list'));
			var preview = $('<div/>').addClass('col-xs-3').append('<img src="" class="img-thumbnail" />').appendTo(data.context);

			var fileName = $('<span/>').addClass('col-xs-7').append('<strong>'+data.files[0].name+'</strong> ('+humanizeSize(data.files[0].size)+')').appendTo(data.context);

			var button = $('<button/>').addClass('col-xs-2 btn btn-default pull-right').prop('type', 'button').html('<i class="icon-trash"></i> {l s='Remove' mod='thegiftcard'}').appendTo(data.context).on('click', function() {
				{$id|escape:'html':'UTF-8'}_total_files--;
				data.files = null;

				var total_elements = $(this).parent().siblings('div.form-group').length;
				$(this).parent().remove();

				if (total_elements == 0) {
					$('#{$id|escape:'html':'UTF-8'}-files-list').html('').parent().hide();
				}
			});

			$('#{$id|escape:'html':'UTF-8'}-files-list').parent().show();
			$('#{$id|escape:'html':'UTF-8'}-upload-button').show().bind('click', function () {
				if (data.files != null)
					data.submit();
			});

			{$id|escape:'html':'UTF-8'}_total_files++;
		}).on('fileuploadprocessalways', function (e, data) {
			var index = data.index,
				file = data.files[index],
				canvas = data.files[index].preview;

			if (canvas !== undefined) {
				$(data.context).find('img').attr('src', canvas.toDataURL());
			}

			if (file.error) {
				$('#{$id|escape:'html':'UTF-8'}-errors').append('<div class="form-group"><i class="icon-picture-o"></i> <strong>'+file.name+'</strong> ('+humanizeSize(file.size)+') : '+file.error+'</div>').parent().show();
				$('#{$id|escape:'html':'UTF-8'}-files-list').html('').parent().hide();
				$(data.context).find('button').trigger('click');
			}
		}).on('fileuploadsubmit', function (e, data) {
			var params = new Object();
			//
			// $('input[id^="legend_"]').each(function()
			// {
			// 	id = $(this).prop("id").replace("legend_", "legend[") + "]";
			// 	params[id] = $(this).val();
			// });
			//
			data.formData = params;
		});

		$('#{$id|escape:'html':'UTF-8'}-add-button').on('click', function() {
			$('#{$id|escape:'html':'UTF-8'}-success').html('').parent().hide();
			$('#{$id|escape:'html':'UTF-8'}-errors').html('').parent().hide();
			$('#{$id|escape:'html':'UTF-8'}').trigger('click');
		});
	});
</script>
