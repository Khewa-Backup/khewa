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


{extends file=$layout}
{block name='content'}
<script type="text/javascript" src="{$smarty.const._MODULE_DIR_}wkhelpdesk/views/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="{$smarty.const._MODULE_DIR_}wkhelpdesk/views/js/tinymce/tinymce_wk_setup.js"></script>

{if isset($smarty.get.created)}
	<div class="alert alert-success">
		{l s='Your ticket has been created successfully. Our support department will contact you very soon.' mod='wkhelpdesk'}
	</div>
{/if}

<div class="page-title" style="background-color:{$bgColor};">
	<span class="bottom-indent" style="color:{$textColor};">
		{l s='Customer service' mod='wkhelpdesk'} - {l s='Create new ticket' mod='wkhelpdesk'}
	</span>
</div>

<div class="hd_main_div padding_div">
	<form action="{url entity='module' name='wkhelpdesk' controller='createticket'}" method="post" class="contact-form-box" enctype="multipart/form-data" accept-charset="UTF-8,ISO-8859-1,UTF-16" id="createticketform">
		<fieldset>
			<div class="clearfix">
				<div class="form-group wk-form-group col-lg-6">
					<label for="firstname" class="control-label required">
						{l s='First name' mod='wkhelpdesk'}
					</label>
					<input class="form-control" type="text" name="firstname" id="firstname" {if isset($firstname)} value="{$firstname}" readonly="readonly" {else if isset($smarty.post.firstname)} value="{$smarty.post.firstname}" {/if}/>
				</div>

				<div class="form-group wk-form-group col-lg-6">
					<label for="lastname" class="control-label required">
						{l s='Last name' mod='wkhelpdesk'}
					</label>
					<input class="form-control" type="text" name="lastname" id="lastname" {if isset($lastname)} value="{$lastname}" readonly="readonly" {else if isset($smarty.post.lastname)} value="{$smarty.post.lastname}" {/if}/>
				</div>

				<div class="form-group wk-form-group  col-lg-12">
					<label for="email" class="control-label required">
						{l s='Email' mod='wkhelpdesk'}
					</label>
					<input class="form-control" type="text" name="email" id="email" {if isset($email)} value="{$email}" readonly="readonly" {else if isset($smarty.post.email)} value="{$smarty.post.email}" {/if}/>
				</div>

				<div class="form-group wk-form-group col-lg-6">
					<label for="queryType" class="control-label required">
						{l s='Query type' mod='wkhelpdesk'}
					</label>
					<div class="row">
						<div class="col-lg-12">
							<select id="queryType" class="form-control" name="queryType">
								<option value="0">{l s='-- Choose --' mod='wkhelpdesk'}</option>
                                {if isset($queryTypes)}
								 {foreach $queryTypes as $queryType}
									<option value="{$queryType.id}"{if isset($smarty.request.queryType) && $smarty.request.queryType == $queryType.id} selected="selected"{/if}>{$queryType.query_name}</option>
								{/foreach}
                                {/if}
							</select>
						</div>
					</div>
				</div>

				<div class="form-group wk-form-group col-lg-6">
					<label for="reference" class="control-label">
						{l s='Order reference' mod='wkhelpdesk'}
					</label>
					<input class="form-control" type="text" name="reference" id="reference" {if isset($reference)} value="{$reference}" readonly="readonly" {else if isset($smarty.post.reference)} value="{$smarty.post.reference}" {/if}/>
				</div>
				<div class="form-group wk-form-group m-0 col-lg-12">
					<label for="">
						{l s='Attachment :' mod='wkhelpdesk'}
					</label>
                    <div style="display:flex">
					<div class="ticketMainAttachment">
						<input type="file" id="ticketAttachment" name="ticketAttachment" value="" class="filestyle" size="chars" data-buttonText="{l s='Choose file' mod='wkhelpdesk'}"/>
					</div>
                    <button type="button" id="removeImage" class="btn btn-primary mx-2">{l s='Remove' mod='wkhelpdesk'}</button>
                    </div>
					<p class="form-control-static m-0">
						{l s='Valid file extension(s) are ' mod='wkhelpdesk'}{$fileExtensions}.
					</p>
					<p class="form-control-static m-0">
						{l s='Maximum file size for all files: ' mod='wkhelpdesk'}{$attachmentMaxSize}{l s='MB' mod='wkhelpdesk'}
					</p>
				</div>
				<div id="hd_other_files col-lg-12"></div>
				<div class="form-group wk-form-group col-lg-12">
					<a class="btn btn-primary" id="hd_btn_other_attachment">
						<span>{l s='Attach more files' mod='wkhelpdesk'}</span>
					</a>
		        </div>

				<div class="form-group wk-form-group col-lg-12">
					<label for="subject" class="control-label required">
						{l s='Subject' mod='wkhelpdesk'}
					</label>
					<input class="form-control" type="text" name="subject" id="subject" {if isset($smarty.post.subject)} value="{$smarty.post.subject}" {else if isset($smarty.post.subject)} value="{$smarty.post.subject}" {/if}/>
				</div>

				<div class="form-group wk-form-group col-lg-12">
					<label for="message" class="control-label required">
						{l s='Message' mod='wkhelpdesk'}
					</label>
					<textarea name="message" id="message" cols="2" rows="8" class="wk_tinymce form-control">{if isset($smarty.post.message)}{$smarty.post.message}{/if}</textarea>
				</div>
				{hook h='displayGDPRConsent' mod='psgdpr' id_module=$id_module}
				{if isset($wkHdCaptchaSiteKey)}
					<div class="g-recaptcha col-lg-12" data-sitekey="{$wkHdCaptchaSiteKey}"></div>
				{/if}
				<div class="submit col-lg-12">
					<button type="submit" name="createTicket" id="createTicket" class="btn btn-primary pull-right pr-0">
						<span>
							{l s='Create' mod='wkhelpdesk'}
						</span>
						<i class="material-icons">&#xE315;</i>
					</button>
				</div>
			</div>
		</fieldset>
	</form>
</div>
{/block}
