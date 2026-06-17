{*
* FMM Helpdesk Module
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright FMM Modules
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   FmmHelpdesk
*}
{literal}
<script>
 	function sub(file){
 			$.ajax({
 			type: "POST",
			url:"{/literal}{$link->getAdminLink('AdminTicketEmailTemplates', false)|escape:'htmlall':'UTF-8'}{literal}&action=ajax&file="+file,
			success:function(data){
                $('#content').show();
                //$('#body_mail').html(data).fadeIn('slow');
                $('#body_mail').val(data);
                $('#filename').val(file);
			}
 			
 		});
 		
 	}
</script>
{/literal}

<div class="panel">
	<fieldset style="width: 900px;">
		<legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/AdminHelpdesk.png" alt="" title="" /> {l s='Email Templates' mod='helpdesk'}</legend>
       	<div style="float:left; margin-left:15px;">
		<table class="table" style="width:260px;background-color: #fff;">
            <th>{l s='Templates in TXT format' mod='helpdesk'}</th><th align="center">{l s='Edit' mod='helpdesk'}</th>
        	{foreach $files as $key=>$file}
        		
            	{assign var=iso value="."|explode:$file}
            	
            	{foreach $iso as $txt}
                	{if $txt == 'txt'}
                	<tr>
                        <td>{$file|escape:'htmlall':'UTF-8'}</td>
                        <td><input type="button" onclick="sub('{$file|escape:'htmlall':'UTF-8'}');" name="save" value="{$file|escape:'htmlall':'UTF-8'}" style="background: url(../img/admin/edit.gif) repeat scroll 0 0 rgba(0, 0, 0, 0);border: medium none;border-radius: 0;color: wheat;width: 16px;"></td>
                      </tr>

                    {/if}  
           		{/foreach}
        	{/foreach}
        </table>
        </div>

        <div style="float:left; margin-left:15px;">
		<table class="table" style="width:260px;background-color: #fff;">
            <th>Templates in HTML format</th><th align="center">{l s='Edit' mod='helpdesk'}</th>
        	{foreach $files as $file}
        		
            	{assign var=iso value="."|explode:$file}
            	
            	{foreach $iso as $txt}
                	{if $txt == 'html'}
                	<tr>
                        <td>{$file|escape:'htmlall':'UTF-8'}</td>
                        <td><input type="button" onclick="sub('{$file|escape:'htmlall':'UTF-8'}');" name="save" value="{$file|escape:'htmlall':'UTF-8'}" style="background: url(../img/admin/edit.gif) repeat scroll 0 0 rgba(0, 0, 0, 0);border: medium none;border-radius: 0;color: wheat;width: 16px;"></td>
                      </tr>
                    {/if}  
           		{/foreach}
        	{/foreach}
        </table>
        </div>
    </fieldset>

    <form id="form_f" method="post" action="{$currentIndex|escape:'htmlall':'UTF-8'}&token={$currentToken|escape:'html'}&save_templates" >
        <fieldset style="width: 900px;">
            <div id="content" style="margin-left: 0;margin-top: 29px;width: 70%;">
                <input type="hidden" name="filename" id="filename">
                <textarea cols="120" rows="32" id="body_mail" name="body_mail" style="float:left"></textarea>
            </div>
            <input type="submit" class="btn btn-default" name="save" value="{l s='Save' mod='helpdesk'}" style="display: table-caption;margin-top: 10px;">
        </fieldset>
    </form>
</div>
    

