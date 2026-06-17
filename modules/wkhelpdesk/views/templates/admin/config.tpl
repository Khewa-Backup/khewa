{**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

{if isset($errors)}
<div class="alert alert-danger">
	{foreach $errors as $error}
		{$error}<br />
	{/foreach}
</div>
{/if}

{if isset($successes)}
<div class="alert alert-success">
	{foreach $successes as $success}
		{$success}<br />
	{/foreach}
</div>
{/if}

{if isset($permissionError)}
	<div class="alert alert-danger">
		{l s='You do not have permission to configure this module.' mod='wkhelpdesk'}
	</div>
{else}
	<div class="row">
		<div class="col-lg-2" id="wk_hd_config_menu">
			<ul class="list-group wk_hd_list">
				<li class="list-group-item{if $wkhdsubmit == 'general'} active{/if}">
					<a data-toggle="tab" href="#general"><i class="icon-cogs"></i> {l s='General' mod='wkhelpdesk'}</a>
				</li>
				<li class="list-group-item{if $wkhdsubmit == 'mail'} active{/if}">
					<a data-toggle="tab" href="#mail"><i class="icon-envelope"></i> {l s='E-mail' mod='wkhelpdesk'}</a>
				</li>
				<li class="list-group-item{if $wkhdsubmit == 'seo'} active{/if}">
					<a data-toggle="tab" href="#seo"><i class="icon-home"></i> {l s='SEO & URLs' mod='wkhelpdesk'}</a>
				</li>
				<li class="list-group-item{if $wkhdsubmit == 'captcha'} active{/if}">
					<a data-toggle="tab" href="#captcha"><i class="icon-list"></i> {l s='Captcha' mod='wkhelpdesk'}</a>
				</li>
			</ul>
			<ul class='list-group'>
				<li class='list-group-item'>
					<a><i class="icon-info"></i> {l s='Version ' mod='wkhelpdesk'}{$version}</a>
				</li>
				<li class='list-group-item'>
					<a href="{$docLink}" target="_blank"><i class="icon-download"></i> {l s='Documentation' mod='wkhelpdesk'}</a>
				</li>
			</ul>
		</div>
		<div class="col-lg-10">
			<div class="tab-content">
				<div id="general" class="tab-pane fade{if $wkhdsubmit == 'general'} in active{/if}">
					{include file="./_partials/general-setting.tpl"}
				</div>
				<div id="mail" class="tab-pane fade{if $wkhdsubmit == 'mail'} in active{/if}">
					{include file="./_partials/mail-setting.tpl"}
				</div>
				<div id="seo" class="tab-pane fade{if $wkhdsubmit == 'seo'} in active{/if}">
					{include file="./_partials/seo-setting.tpl"}
				</div>
				<div id="captcha" class="tab-pane fade{if $wkhdsubmit == 'captcha'} in active{/if}">
					{include file="./_partials/captcha-setting.tpl"}
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		$(document).ready(function() {
			setupCaptchaForm();
			//hide and show text according to switch
			$(document).on('change', '.captchaOption', function(){
				setupCaptchaForm();
			});
			if ($('input[name="helpdeskUrlRewrite"]:checked').val() == '0') {
				$('.url_rewriting_div').hide();
			}

			$('input[name="helpdeskUrlRewrite"]').on("click", function(){
                if ($('input[name="helpdeskUrlRewrite"]:checked').val() == 0) {
                    $('.url_rewriting_div').hide();
                } else {
                    $('.url_rewriting_div').show();
                }

			});

			$('label[for="helpdeskUrlRewrite_on"]').on("click", function(){
				$('.url_rewriting_div').show();
			});
			function setupCaptchaForm()
			{
				if ($('#enableCaptcha_on').is(':checked')
					|| $('#enableCaptchaViewTicket_on').is(':checked')) {
					$('.PositionBlock').fadeIn();
				} else {
					$('.PositionBlock').fadeOut();
				}
			}
		});
	</script>

	<style>
		.mce-tinymce{
			width : auto !important;
		}
		.wk_hd_list i{
			margin-right: 4px;
		}
		#wk_hd_config_menu .list-group-item.active a{
			color: #ffffff;
			text-decoration: none;
		}
		#wk_hd_config_menu .list-group-item {
			padding: 0;
		}
		#wk_hd_config_menu .list-group-item a {
			padding: 10px 15px;
			display: block;
		}
		.margin_bottom{
			margin-bottom: 15px;
		}
		#wk_hd_config_menu a:hover {
			text-decoration: none;
			color:#111;
		}
		#wk_hd_config_menu a {
			color: #555;
			text-decoration: none;
		}
		.bootstrap label.control-label {
			padding-top: 7px;
			text-align: right;
		}
	</style>
	<script type="text/javascript">
		$(document).ready(function(){

		});
	</script>
{/if}