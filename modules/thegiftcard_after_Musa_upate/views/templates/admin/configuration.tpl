{*
* 2022 - Keyrnel SARL
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
* @copyright 2022 - Keyrnel SARL
* @license   commercial
* International Registered Trademark & Property of Keyrnel SARL
*}

<div id="modulecontent" class="clearfix">
    <!-- Nav tabs -->
    <div class="col-lg-2">
        <div class="list-group">
            <a href="#stats" class="list-group-item active" data-toggle="gc_tab"><i class="icon-bar-chart"></i> {l s='Statistics' mod='thegiftcard'}</a>
            <a href="#generator" class="list-group-item" data-toggle="gc_tab"><i class="icon-cogs"></i> {l s='Gift card generator' mod='thegiftcard'}</a>
            <a href="#emails" class="list-group-item" data-toggle="gc_tab"><i class="icon-pencil-square-o"></i> {l s='Email templates' mod='thegiftcard'}</a>
            <a href="#display" class="list-group-item" data-toggle="gc_tab"><i class="icon-picture-o"></i> {l s='Display settings' mod='thegiftcard'}</a>
            <a href="#translations" class="list-group-item" data-toggle="gc_tab"><i class="icon-globe"></i> {l s='Translations' mod='thegiftcard'}</a>
        </div>
    </div>
    <!-- Tab panes -->
    <form id="giftcard_form" class="form-horizontal" action="{$currentIndex|escape:'html':'UTF-8'}" method="post" autocomplete="off" enctype="multipart/form-data">
        <div class="tab-content col-lg-10">
            <div class="tab-pane active" id="stats">
                {include file="./tabs/stats.tpl"}
            </div>
            <div class="tab-pane" id="generator">
                {include file="./tabs/generator.tpl"}
            </div>
            <div class="tab-pane" id="emails">
                {include file="./tabs/emails.tpl"}
            </div>
            <div class="tab-pane" id="display">
                {include file="./tabs/display.tpl"}
            </div>
			<div class="tab-pane" id="translations">
                {include file="./tabs/translations.tpl"}
            </div>
        </div>
    </form>
</div>


<script src="//blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<script src="//blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script>
<script type="text/javascript">
	var currentIndex = "{$currentIndex|escape:'quotes':'UTF-8'}";
	var iso = "{$iso|escape:'html':'UTF-8'}";
	var pathCSS = "{$path_css|escape:'html':'UTF-8'}";
	var ad = "{$ad|escape:'html':'UTF-8'}";
  var defaultLanguage = "{$defaultLanguage|intval}";
  var defaultCurrency = "{$default_currency|intval}";
  var current_shop_id = {$current_shop_id|intval};

	$(document).ready(function(){
		tinySetup({
			editor_selector :"autoload_rte"
		});
		hideOtherLanguage(defaultLanguage);

        //tabs
        $(document).on('click', '.list-group-item', function() {
			var $el = $(this).parent().closest(".list-group").children(".active");
			if ($el.hasClass("active")) {
				target = $(this).find('i').attr('data-target');
				if (target !== undefined) {
					loadTable('#'+target);
				}
				$el.removeClass("active");
				$(this).addClass("active");
			}
		});

        $(document).on('click', '#giftcard_form [js-action="generate-pdf"]', function() {
            var params = {
                ajax: true,
                action: 'generatePdf',
                id_giftcard: $(this).closest('tr').attr('data-id')
            };

            $.post(currentIndex, params, null, 'json').then(function(resp) {
                if (!resp.error) {
                    var byteString = window.atob(resp.url.split(',')[1]);
                	var mimeString = resp.url
                		.split(',')[0]
                		.split(':')[1]
                		.split(';')[0];

                	var ab = new ArrayBuffer(byteString.length);
                	var ia = new Uint8Array(ab);
                	for (var i = 0; i < byteString.length; i++) {
                		ia[i] = byteString.charCodeAt(i);
                	}

                	var blob = new Blob([ab], { type: mimeString });
                	window.open(URL.createObjectURL(blob), '_blank');
                } else {
                    $.each(resp.errors, function(key, val) {
                        showErrorMessage(val);
                    });

                }
            }).fail(function(resp) {
                alert("[TECHNICAL ERROR]");
            });
        });
	});
</script>
