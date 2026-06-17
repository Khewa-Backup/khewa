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

<div class="panel">
	<div class="panel-heading">
		<i class="icon-pencil-square-o"></i> {l s='Email templates' mod='thegiftcard'}
	</div>
	<div class="form-group">
		{if !$cronjobs}
			<div class="alert alert-info">{l s='Please activate cronjobs module to allow customers sending gift cards with datetime' mod='thegiftcard'}</div>
		{/if}
		<ul class="nav nav-pills">
			<li class="active">
				<a href="#buyer_email" data-toggle="gc_tab">{l s='Buyer email' mod='thegiftcard'}</a>
			</li>
			<li>
				<a href="#friend_email" data-toggle="gc_tab">{l s='Friend email' mod='thegiftcard'}</a>
			</li>
		</ul>
		<hr>
		<div class="tab-content">
			<div class="tab-pane active" id="buyer_email">
				<div class="form-group">
					<label class="control-label col-lg-3">
						<span class="label-tooltip" data-toggle="gc_tooltip">
							{l s='Gifcard image size' mod='thegiftcard'}
						</span>
					</label>
					<div class="col-sm-4">
						<div class="row">
							<div class="col-sm-6">
								<div class="input-group">
									<input class="input-medium" type="text" name="email_img_width" value="{Configuration::get('GIFTCARD_EMAIL_IMG_WIDTH')|intval}">
									<span class="input-group-addon">px</span>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="input-group">
									<input class="input-medium" type="text" name="email_img_height" value="{Configuration::get('GIFTCARD_EMAIL_IMG_HEIGHT')|intval}">
									<span class="input-group-addon">px</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-lg-3 required">
						<span class="label-tooltip" data-toggle="gc_tooltip">
							{l s='Subject' mod='thegiftcard'}
						</span>
					</label>
					<div class="col-lg-9">
						{foreach from=$languages item=language}
						{if $languages|count > 1}
						<div class="row">
							<div class="translatable-field lang-{$language.id_lang|intval}" {if $language.id_lang != $default_language}style="display:none"{/if}>
								<div class="col-lg-9">
						{/if}
									<input id="GIFTCARD_EMAIL_SUBJECT_PRINT_{$language.id_lang|intval}" type="text"  name="giftcard_email_subject[print][{$language.id_lang|intval}]" value="{Configuration::get('GIFTCARD_EMAIL_SUBJECT_PRINT', $language.id_lang|intval)}">
						{if $languages|count > 1}
								</div>
								<div class="col-lg-2">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="gc_dropdown">
										{$language.iso_code|escape:'html':'UTF-8'}
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu">
										{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang|intval});" tabindex="-1">{$language.name|escape:'html':'UTF-8'}</a></li>
										{/foreach}
									</ul>
								</div>
							</div>
						</div>
						{/if}
						{/foreach}
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-lg-3 required">
						<span class="label-tooltip" data-toggle="gc_tooltip">
							{l s='"title" tag' mod='thegiftcard'}
						</span>
					</label>
					<div class="col-lg-9">
						{foreach from=$languages item=language}
						{if $languages|count > 1}
						<div class="row">
							<div class="translatable-field lang-{$language.id_lang|intval}" {if $language.id_lang != $default_language}style="display:none"{/if}>
								<div class="col-lg-9">
						{/if}
									<input id="GIFTCARD_EMAIL_TITLE_PRINT_{$language.id_lang|intval}" type="text"  name="giftcard_email_title[print][{$language.id_lang|intval}]" value="{$print_email['title'][$language.iso_code|escape:'html':'UTF-8']|escape:'html':'UTF-8'}">
						{if $languages|count > 1}
								</div>
								<div class="col-lg-2">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="gc_dropdown">
										{$language.iso_code|escape:'html':'UTF-8'}
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu">
										{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang|intval});" tabindex="-1">{$language.name|escape:'html':'UTF-8'}</a></li>
										{/foreach}
									</ul>
								</div>
							</div>
						</div>
						{/if}
						{/foreach}
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-lg-3" for="description">
						<span class="label-tooltip" data-toggle="gc_tooltip">
							{l s='Content' mod='thegiftcard'}
						</span>
					</label>
					<div class="col-lg-9">
						{foreach from=$languages item=language}
						{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang|intval}">
							<div class="col-lg-9">
						{/if}
								<textarea id="GIFTCARD_EMAIL_CONTENT_PRINT_{$language.id_lang|intval}" name="giftcard_email_content[print][{$language.id_lang|intval}]" class="autoload_rte">{$print_email['content'][$language.iso_code|escape:'html':'UTF-8']}</textarea>
							<span class="counter" data-max="{if isset($max)}{$max|intval}{else}none{/if}"></span>
						{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="gc_dropdown">
									{$language.iso_code|escape:'html':'UTF-8'}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
									<li><a href="javascript:hideOtherLanguage({$language.id_lang|intval});">{$language.name|escape:'html':'UTF-8'}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
						{/if}
						{/foreach}

						<script type="text/javascript">
							$(".textarea-autosize").autosize();
						</script>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="friend_email">
				<div class="form-group">
					<label class="control-label col-lg-3">
						<span class="label-tooltip" data-toggle="gc_tooltip">
							{l s='Gifcard image size' mod='thegiftcard'}
						</span>
					</label>
					<div class="col-sm-4">
						<div class="row">
							<div class="col-sm-6">
								<div class="input-group">
									<input class="input-medium" type="text" name="email_img_width" value="{Configuration::get('GIFTCARD_EMAIL_IMG_WIDTH')|intval}">
									<span class="input-group-addon">px</span>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="input-group">
									<input class="input-medium" type="text" name="email_img_height" value="{Configuration::get('GIFTCARD_EMAIL_IMG_HEIGHT')|intval}">
									<span class="input-group-addon">px</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-lg-3 required">
						<span class="label-tooltip" data-toggle="gc_tooltip">
							{l s='Subject' mod='thegiftcard'}
						</span>
					</label>
					<div class="col-lg-9">
						{foreach from=$languages item=language}
						{if $languages|count > 1}
						<div class="row">
							<div class="translatable-field lang-{$language.id_lang|intval}" {if $language.id_lang != $default_language}style="display:none"{/if}>
								<div class="col-lg-9">
						{/if}
									<input id="GIFTCARD_EMAIL_SUBJECT_FRIEND_{$language.id_lang|intval}" type="text"  name="giftcard_email_subject[friend][{$language.id_lang|intval}]" value="{Configuration::get('GIFTCARD_EMAIL_SUBJECT_FRIEND', $language.id_lang|intval)}">
						{if $languages|count > 1}
								</div>
								<div class="col-lg-2">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="gc_dropdown">
										{$language.iso_code|escape:'html':'UTF-8'}
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu">
										{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang|intval});" tabindex="-1">{$language.name|escape:'html':'UTF-8'}</a></li>
										{/foreach}
									</ul>
								</div>
							</div>
						</div>
						{/if}
						{/foreach}
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-lg-3 required">
						<span class="label-tooltip" data-toggle="gc_tooltip">
							{l s='"title" tag' mod='thegiftcard'}
						</span>
					</label>
					<div class="col-lg-9">
						{foreach from=$languages item=language}
						{if $languages|count > 1}
						<div class="row">
							<div class="translatable-field lang-{$language.id_lang|intval}" {if $language.id_lang != $default_language}style="display:none"{/if}>
								<div class="col-lg-9">
						{/if}
									<input id="GIFTCARD_EMAIL_TITLE_FRIEND_{$language.id_lang|intval}" type="text"  name="giftcard_email_title[friend][{$language.id_lang|intval}]" value="{$friend_email['title'][$language.iso_code|escape:'html':'UTF-8']|escape:'html':'UTF-8'}">
						{if $languages|count > 1}
								</div>
								<div class="col-lg-2">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="gc_dropdown">
										{$language.iso_code|escape:'html':'UTF-8'}
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu">
										{foreach from=$languages item=language}
										<li><a href="javascript:hideOtherLanguage({$language.id_lang|intval});" tabindex="-1">{$language.name|escape:'html':'UTF-8'}</a></li>
										{/foreach}
									</ul>
								</div>
							</div>
						</div>
						{/if}
						{/foreach}
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-lg-3" for="description">
						<span class="label-tooltip" data-toggle="gc_tooltip">
							{l s='Content' mod='thegiftcard'}
						</span>
					</label>
					<div class="col-lg-9">
						{foreach from=$languages item=language}
						{if $languages|count > 1}
						<div class="translatable-field row lang-{$language.id_lang|intval}">
							<div class="col-lg-9">
						{/if}
								<textarea id="GIFTCARD_EMAIL_CONTENT_FRIEND_{$language.id_lang|intval}" name="giftcard_email_content[friend][{$language.id_lang|intval}]" class="autoload_rte">{$friend_email['content'][$language.iso_code|escape:'html':'UTF-8']}</textarea>
							<span class="counter" data-max="{if isset($max)}{$max|intval}{else}none{/if}"></span>
						{if $languages|count > 1}
							</div>
							<div class="col-lg-2">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="gc_dropdown">
									{$language.iso_code|escape:'html':'UTF-8'}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach from=$languages item=language}
									<li><a href="javascript:hideOtherLanguage({$language.id_lang|intval});">{$language.name|escape:'html':'UTF-8'}</a></li>
									{/foreach}
								</ul>
							</div>
						</div>
						{/if}
						{/foreach}

						<script type="text/javascript">
							$(".textarea-autosize").autosize();
						</script>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<button type="submit" name="submitGiftCard" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='thegiftcard'}</button>
	</div>
</div>
