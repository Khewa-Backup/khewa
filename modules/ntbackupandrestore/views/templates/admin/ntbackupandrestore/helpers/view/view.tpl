{*
* 2013-2024 2N Technologies
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to contact@2n-tech.com so we can send you a copy immediately.
*
* @author    2N Technologies <contact@2n-tech.com>
* @copyright 2013-2024 2N Technologies
* @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}
<link rel="stylesheet" href="{$css_file_1|escape:'html':'UTF-8'}">
<link rel="stylesheet" href="{$css_file_2|escape:'html':'UTF-8'}">
<script type="text/javascript">
    var light                           = {$light|intval};
    var admin_link_ntbr                 = "{$link->getAdminLink('AdminNtbackupandrestore')|escape:'javascript':'UTF-8'}";
	var download_file                   = "{$download_file|escape:'javascript':'UTF-8'}";
	var backup_progress                 = "{$backup_progress|escape:'javascript':'UTF-8'}";
	var backup_stop                     = "{$backup_stop|escape:'javascript':'UTF-8'}";
	var ajax_loader                     = "{$ajax_loader|escape:'javascript':'UTF-8'}";
	var link_restore_file               = "{$link_restore_file|escape:'javascript':'UTF-8'}";
	var restore_lastlog                 = "{$restore_lastlog|escape:'javascript':'UTF-8'}";
    var FINISH5                         = "{$restore_backup_finish|escape:'html':'UTF-8'}";
    var ERROR5                          = "{$restore_backup_error|escape:'html':'UTF-8'}";
	var start_backup                    = "{l s='Start backup...' mod='ntbackupandrestore'}";
	var start_download                  = "{l s='Start download...' mod='ntbackupandrestore'}";
	var nt_btn_save                     = "{l s='Save' mod='ntbackupandrestore'}";
	var cron_config_txt                 = "{l s='To start the backup of the profile' mod='ntbackupandrestore'}";
	var backup_link_text                = "{l s='Download the backup' mod='ntbackupandrestore'}";
	var create_success                  = "{l s='Success! The backup seems to have works correctly, however you should download the backup file and check its validity.' mod='ntbackupandrestore'}";
	var delete_success                  = "{l s='Your files are deleted' mod='ntbackupandrestore'}";
	var delete_error                    = "{l s='Error during the deletion of your files' mod='ntbackupandrestore'}";
	var import_success                  = "{l s='Your files are imported' mod='ntbackupandrestore'}";
	var import_error                    = "{l s='Error during the import of your files' mod='ntbackupandrestore'}";
	var save_infos_backup_success       = "{l s='Your backup infos are updated' mod='ntbackupandrestore'}";
	var save_infos_backup_error         = "{l s='Error, your backup infos were not updated' mod='ntbackupandrestore'}";
	var restore_backup_error            = "{l s='Error, your backup was not restored' mod='ntbackupandrestore'}";
	var restore_backup_warning          = "{l s='Do not touch anything while the restoration is running' mod='ntbackupandrestore'}";
	var restore_backup_success          = "{l s='Your backup is restored' mod='ntbackupandrestore'}";
	var save_profile_config_error       = "{l s='Error, the profile could not be created' mod='ntbackupandrestore'}";
	var save_config_success             = "{l s='Your configuration is saved' mod='ntbackupandrestore'}";
	var file_to_bck_dir_success         = "{l s='Your backup has been moved' mod='ntbackupandrestore'}";
	var file_to_bck_dir_error           = "{l s='Your backup could not be moved' mod='ntbackupandrestore'}";
	var delete_config_error             = "{l s='Error while deleting your configuration' mod='ntbackupandrestore'}";
	var delete_config_success           = "{l s='Your configuration is deleted' mod='ntbackupandrestore'}";
	var upd_shop_url_success            = "{l s='Your domain has been updated' mod='ntbackupandrestore'}";
	var upd_shop_url_error              = "{l s='Your domain could not been updated' mod='ntbackupandrestore'}";
	var error_delete_config_default     = "{l s='You need to choose a new default configuration before you delete this one' mod='ntbackupandrestore'}";
	var confirm_change_account          = "{l s='Be carefull. All update not saved will be lost for this account. Are you sure you want to leave?' mod='ntbackupandrestore'}";
	var confirm_delete_account          = "{l s='Are you sure you want to delete this account?' mod='ntbackupandrestore'}";
	var confirm_save_config             = "{l s='Do you want to save your configuration?' mod='ntbackupandrestore'}";
	var confirm_restore_backup          = "{l s='Are you sure you want to restore your site from your backup file? If you click Yes, the restoration will start and SHOULD NOT be stopped until it is complete or your site will be rendered unusable.' mod='ntbackupandrestore'}";
	var confirm_delete_backup           = "{l s='Are you sure you want to delete this backup?' mod='ntbackupandrestore'}";
	var confirm_delete_profile          = "{l s='Are you sure you want to delete this profile?' mod='ntbackupandrestore'}";
	var confirm_send_away_backup        = "{l s='Are you sure you want to send away this backup?' mod='ntbackupandrestore'}";
	var confirm_delete                  = "{l s='Are you sure you want to delete?' mod='ntbackupandrestore'}";
	var save_automation_success         = "{l s='Your automation is saved' mod='ntbackupandrestore'}";
	var save_api_success                = "{l s='Your API configuration is saved' mod='ntbackupandrestore'}";
	var save_api_error                  = "{l s='Error, your API configuration was not saved' mod='ntbackupandrestore'}";
	var save_account_success            = "{l s='Your account is saved' mod='ntbackupandrestore'}";
	var save_account_error              = "{l s='Error, your account was not saved' mod='ntbackupandrestore'}";
	var delete_account_success          = "{l s='Your account is deleted' mod='ntbackupandrestore'}";
	var delete_account_error            = "{l s='Error, your account was not deleted' mod='ntbackupandrestore'}";
	var check_connection_success        = "{l s='Your connection is working' mod='ntbackupandrestore'}";
	var check_connection_error          = "{l s='Error, your connection is not working' mod='ntbackupandrestore'}";
	var tree_loading_error              = "{l s='An error occured while trying to load the tree' mod='ntbackupandrestore'}";
	var distant_files_loading_error     = "{l s='An error occured while trying to load the files' mod='ntbackupandrestore'}";
	var cron_backup_error               = "{l s='An error occured during backup' mod='ntbackupandrestore'}";
	var prompt_name_account             = "{l s='Please give a name to your new account' mod='ntbackupandrestore'}";
	var no_backup_selected              = "{l s='You do not have any backup selected' mod='ntbackupandrestore'}";
	var list_backups_see                = "{l s='See' mod='ntbackupandrestore'}";
	var list_backups_download           = "{l s='Download' mod='ntbackupandrestore'}";
	var list_backups_import             = "{l s='Import' mod='ntbackupandrestore'}";
	var list_backups_delete             = "{l s='Delete' mod='ntbackupandrestore'}";
	var list_backups_colons             = "{l s=':' mod='ntbackupandrestore'}";
	var list_backups_send_away          = "{l s='Send away' mod='ntbackupandrestore'}";
	var list_backups_comment            = "{l s='Comment' mod='ntbackupandrestore'}";
	var list_backups_safe_label         = "{l s='Safe?' mod='ntbackupandrestore'}";
	var list_backups_safe_title         = "{l s='This backup has been tested and is safe to used. It should not be deleted' mod='ntbackupandrestore'}";
	var error_download_distant_file     = "{l s='Error, the file cannot be downloaded' mod='ntbackupandrestore'}";
	var error_create_sodium_key         = "{l s='Error, the encryption key cannot be created' mod='ntbackupandrestore'}";
	var warning_while_restoring         = "{l s='WARNING! The restoration of the backup is in progress. Do not touch anything and wait. Restoration MUST NOT be interrupted otherwise your site will be completely unusable!' mod='ntbackupandrestore'}";
	var id_shop_group                   = {$id_shop_group|intval};
	var id_shop                         = {$id_shop|intval};
	var ftp_port_default                = {$ftp_port_default|intval};
	var ftp_directory_default           = "{$ftp_directory_default|escape:'html':'UTF-8'}";
	var url_ajax                        = "{$url_ajax|escape:'javascript':'UTF-8'}";
	var create_auto_cron_php            = "{$create_auto_cron_php|escape:'javascript':'UTF-8'}";
	var secure_key                      = "{$secure_key|escape:'html':'UTF-8'}";
    var running_backup                  = {$running_backup|intval};
    var activate_2nt_automation         = {$activate_2nt_automation|intval};
    var fake_mdp                        = "{$fake_mdp|escape:'html':'UTF-8'}";
    var list_infos                      = new Array();
    var list_configs                    = new Array();
	var add_backup_error                = "{l s='Error, your backup was not added' mod='ntbackupandrestore'}";
	var add_backup_success              = "{l s='Your backup is added' mod='ntbackupandrestore'}";
	var warning_probable_timeout        = "{l s='A refresh should have been done a while ago but was prevented. It is possible you have reach a timeout' mod='ntbackupandrestore'}";
	var failed_ajax_backup              = "ERR{l s='Something went wrong with the backup' mod='ntbackupandrestore'}";
	var choose_txt                      = "{l s='Choose' mod='ntbackupandrestore'}";
	var safe_txt                        = "{l s='Safe:' mod='ntbackupandrestore'}";
	var yes_txt                         = "{l s='Yes' mod='ntbackupandrestore'}";
	var no_txt                          = "{l s='No' mod='ntbackupandrestore'}";
	var scan_files_error                = "{l s='Error, your files could not be scanned' mod='ntbackupandrestore'}";
	var scan_files_delete_error         = "{l s='Error, the deletion could not be done' mod='ntbackupandrestore'}";
    var time_before_warning_timeout     = {$time_before_warning_timeout|intval};
    var max_file_download_size          = {$max_file_download_size|intval};
    var backup_type_complete            = "{$backup_type_complete|escape:'htmlall':'UTF-8'}";
    var backup_type_file                = "{$backup_type_file|escape:'htmlall':'UTF-8'}";
    var backup_type_base                = "{$backup_type_base|escape:'htmlall':'UTF-8'}";
    var s3_label_host                   = "{l s='Host' mod='ntbackupandrestore'}";
    var s3_label_endpoint               = "{l s='Endpoint' mod='ntbackupandrestore'}";
    var s3_label_secret_access_key      = "{l s='Secret access key' mod='ntbackupandrestore'}";
    var s3_label_application_key        = "{l s='Application key' mod='ntbackupandrestore'}";
    var s3_label_access_key_id          = "{l s='Access key ID' mod='ntbackupandrestore'}";
    var s3_label_application_key_id     = "{l s='Application key ID' mod='ntbackupandrestore'}";
    var s3_aws_host                     = "{$s3_aws_host|escape:'htmlall':'UTF-8'}";
    var s3_scaleway_host                = "{$s3_scaleway_host|escape:'htmlall':'UTF-8'}";
    var s3_backblaze_host               = "{$s3_backblaze_host|escape:'htmlall':'UTF-8'}";
    var s3_type_aws                     = {$s3_type_aws|intval};
    var s3_type_minio                   = {$s3_type_minio|intval};
    var s3_type_vultr                   = {$s3_type_vultr|intval};
    var s3_type_wasabi                  = {$s3_type_wasabi|intval};
    var s3_type_scaleway                = {$s3_type_scaleway|intval};
    var s3_type_backblaze               = {$s3_type_backblaze|intval};
    var s3_type_other                   = {$s3_type_other|intval};
    var product_img_none                = {$product_img_none|intval};
    var is_presta_edition               = {$is_presta_edition|intval};

    {foreach from=$list_infos item=info}
        {assign var='name' value=$info.backup_name}
        if (!list_infos["{$name|escape:'html':'UTF-8'}"]) {
            list_infos["{$name|escape:'html':'UTF-8'}"] = new Array();
        }

        list_infos["{$name|escape:'html':'UTF-8'}"]['id_ntbr_backups']  = "{$info.id_ntbr_backups|intval}";
        list_infos["{$name|escape:'html':'UTF-8'}"]['backup_name']      = "{$name|escape:'html':'UTF-8'}";
        list_infos["{$name|escape:'html':'UTF-8'}"]['comment']          = "{$info.comment|escape:'html':'UTF-8'}";
        list_infos["{$name|escape:'html':'UTF-8'}"]['safe']             = "{$info.safe|intval}";
    {/foreach}

    {foreach from=$list_config key=nb item=config}
        if (!list_configs["{$nb|intval}"]) {
            list_configs["{$nb|intval}"] = new Array();
        }

        list_configs["{$nb|intval}"]['id_ntbr_config']  = "{$config.id_ntbr_config|intval}";
        list_configs["{$nb|intval}"]['is_default']      = "{$config.is_default|intval}";
        list_configs["{$nb|intval}"]['name']            = "{$config.name|escape:'html':'UTF-8'}";
    {/foreach}
</script>
{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}
<div id="ntbackupandrestore">
    {if $module_address_use != $module_address_config}
        <div id="shop_error" class="error alert alert-danger">
            <p>
                {l s='Warning!' mod='ntbackupandrestore'}
                <br/>
                {l s='You are not connected on the primary address of your shop!' mod='ntbackupandrestore'}
                <br/>
                <span>
                    {l s='So that the module works correctly, use' mod='ntbackupandrestore'}
                    <a href="{$module_address_config|escape:'html':'UTF-8'}">{$module_address_config|escape:'html':'UTF-8'}</a>
                    {l s='instead of' mod='ntbackupandrestore'}
                    <a href="{$module_address_use|escape:'html':'UTF-8'}">{$module_address_use|escape:'html':'UTF-8'}</a>
                </span>
            </p>
        </div>
    {/if}
    {if !$is_presta_edition && $big_website}
        <div id="big_website_error" class="error alert alert-danger">
            <span class="close">X</span>
            <p>
                {l s='Warning!' mod='ntbackupandrestore'}
                <br/>
                {l s='Your backup has a large size (> 2 GB). Compressing your backup may be faster if you:' mod='ntbackupandrestore'}
            </p>
            <ol>
                <li>{l s='Increase the maximum timeout of your server and in the advanced options of the module, increase the duration of the intermediate renewal to the maximum timeout of your server.' mod='ntbackupandrestore'}</li>
                <li>{l s='Or increase the maximum timeout of your server and disable intermediate renewal in the advanced options of the module.' mod='ntbackupandrestore'}</li>
                <li>{l s='Or create fragmented backups by specifying a maximum size of 2000 MB in the advanced options of the module.' mod='ntbackupandrestore'}</li>
                <li>{l s='Or disable compression in the advanced options of the module.' mod='ntbackupandrestore'}</li>
            </ol>
            <p>
                {l s='Do not hesitate to contact us if necessary!' mod='ntbackupandrestore'}
            </p>
        </div>
    {/if}
    <div id="result">
        <div class="error alert alert-danger"></div>
        <div class="confirm alert alert-success"></div>
    </div>
	<div class="clear"></div>
	<div class="sidebar navigation col-md-2">
		<nav id="nt_tab" class="list-group">
			<a id="nt_tab0" class="list-group-item active"><i class="fas fa-archive"></i>&nbsp;{l s='Backup' mod='ntbackupandrestore'}</a>
            {if !$is_presta_edition}
                <a id="nt_tab7" class="list-group-item multi_config"><i class="fas fa-cogs"></i>&nbsp;{l s='Add profile' mod='ntbackupandrestore'}</a>
                <a id="nt_tab1" class="list-group-item configuration"><i class="fas fa-cogs"></i>&nbsp;{l s='Configuration' mod='ntbackupandrestore'}</a>
            {/if}
			<a id="nt_tab2" class="list-group-item"><i class="far fa-clock"></i>&nbsp;{l s='Automation' mod='ntbackupandrestore'}</a>
			<a id="nt_tab5" class="list-group-item"><i class="fas fa-history"></i>&nbsp;{l s='Restoration' mod='ntbackupandrestore'}</a>
            {if !$is_presta_edition}
                <a id="nt_tab9" class="list-group-item"><i class="fas fa-sitemap"></i>&nbsp;{l s='Files map' mod='ntbackupandrestore'}</a>
            {/if}
			<a id="nt_tab8" class="list-group-item faq"><i class="fas fa-question-circle"></i>&nbsp;{l s='FAQ' mod='ntbackupandrestore'}</a>
            {if !$is_presta_edition}
                <a id="nt_tab3" class="list-group-item"><i class="fas fa-book"></i>&nbsp;{l s='Documentation' mod='ntbackupandrestore'}</a>
            {/if}
			<a id="nt_tab4" class="list-group-item"><i class="fas fa-envelope"></i>&nbsp;{l s='Contact' mod='ntbackupandrestore'}</a>
			<a id="nt_tab10" class="list-group-item"><i class="fas fa-store"></i>&nbsp;{l s='Our modules' mod='ntbackupandrestore'}</a>
            {if !$is_presta_edition}
                {*{if $display_translate_tab}*}
                <a id="nt_tab6" class="list-group-item"><i class="fas fa-globe-americas"></i>&nbsp;{l s='Help us translate into your language' mod='ntbackupandrestore'}</a>
                {*{/if}*}
            {/if}
		</nav>
		<nav class="list-group">
            {if !$is_presta_edition}
                <a id="nt_request" class="list-group-item" href="{$link_contact|escape:'html':'UTF-8'}" target="_blank">
                    <i class="far fa-lightbulb"></i>&nbsp;{l s='Request feature' mod='ntbackupandrestore'}
                </a>
            {/if}
            <a href="{$changelog|escape:'html':'UTF-8'}" target="_blank" id="nt_version" class="list-group-item">
                <i class="fas fa-info"></i>&nbsp;{l s='Version' mod='ntbackupandrestore'} {$version|escape:'html':'UTF-8'} {if $is_presta_edition}PE{/if}
                {if $available_version > $version}({$available_version|escape:'html':'UTF-8'} {l s='avail' mod='ntbackupandrestore'}){/if}
            </a>
            <div class="list-group-item info_nt_module">
                <p>
                    <a target="_blank" href="https://addons.prestashop.com/fr/2_community-developer?contributor=311046">
                        <span>{l s='Proudly developed by' mod='ntbackupandrestore'}</span><br/>
                        <img class="nt_logo" src="../modules/ntbackupandrestore/views/img/logo_module.png" alt="2N Technologies"/>
                    </a>
                </p>
                <p>
                    <a target="_blank" href="https://addons.prestashop.com/fr/2_community-developer?contributor=311046">
                        <span>{l s='We are a Prestashop Superhero seller' mod='ntbackupandrestore'}</span><br/><br/>
                        <img class="rank_logo" src="../modules/ntbackupandrestore/views/img/super_hero.png" alt="2N Technologies"/>
                    </a>
                </p>
                {if !$is_presta_edition}
                    <p class="module_rating">
                        <a href="{$link_rate|escape:'html':'UTF-8'}" target="_blank">
                            <span>{l s='Rate and comment this module' mod='ntbackupandrestore'}</span><br/>
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </a>
                    </p>
                    <p>
                        <a target="_blank" href="{$link_contact|escape:'html':'UTF-8'}">{l s='Contact us' mod='ntbackupandrestore'}</a>
                    </p>
                    <p>
                        <a href="{$documentation|escape:'html':'UTF-8'}" target="_blank" title="{l s='Documentation' mod='ntbackupandrestore'}"><i class="fas fa-book"></i></a>
                        <a href="{$link_contact|escape:'html':'UTF-8'}" target="_blank" title="{l s='Support' mod='ntbackupandrestore'}"><i class="far fa-life-ring"></i></a>
                        <a href="{$link_contact|escape:'html':'UTF-8'}" target="_blank" title="{l s='Translate' mod='ntbackupandrestore'}"><i class="fas fa-globe-americas"></i></a>
                        <a href="{$link_contact|escape:'html':'UTF-8'}" target="_blank" title="{l s='Ask a feature' mod='ntbackupandrestore'}"><i class="far fa-lightbulb"></i></a>
                    </p>
                {/if}
            </div>
		</nav>
	</div>
	<div>
		<div id="nt_tab0_content" class="tab col-md-10">
			{include file="./backup.tpl"}
		</div>
        {if !$is_presta_edition}
            <div id="nt_tab1_content" class="tab panel col-md-10">
                {include file="./configuration.tpl"}
            </div>
            <div id="nt_tab7_content" class="panel tab col-md-10">
                {include file="./configuration_profile.tpl"}
            </div>
            <div id="nt_tab9_content" class="panel tab col-md-10">
                {include file="./files_map.tpl"}
            </div>
            <div id="nt_tab3_content" class="panel tab col-md-10">
                {include file="./documentation.tpl"}
            </div>
            <div id="nt_tab6_content" class="panel tab col-md-10">
                {include file="./translate.tpl"}
            </div>
        {/if}
		<div id="nt_tab2_content" class="panel tab col-md-10">
			{include file="./automation.tpl"}
		</div>
		<div id="nt_tab4_content" class="panel tab col-md-10">
			{include file="./contact.tpl"}
		</div>
		<div id="nt_tab10_content" class="panel tab col-md-10">
            {include file="./our_modules.tpl"}
        </div>
		<div id="nt_tab5_content" class="panel tab col-md-10">
			{include file="./restoration.tpl"}
		</div>
		<div id="nt_tab8_content" class="panel tab col-md-10">
			{include file="./faq.tpl"}
		</div>
		<div class="clear"></div>
	</div>
    <div class="clear"></div>
    <div id="loader_container">
        <div id="grey_background"></div>
        <div id="loader_txt"></div>
        <img id="loader" src="{$ajax_loader|escape:'html':'UTF-8'}"/>
    </div>
    <script type="text/javascript" src="{$js_file_1|escape:'html':'UTF-8'}"></script>
    <script type="text/javascript" src="{$js_file_2|escape:'html':'UTF-8'}"></script>
</div>

