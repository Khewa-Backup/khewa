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

<div class="panel-heading">
    <i class="fas fa-cogs"></i>
    &nbsp;{l s='Configuration' mod='ntbackupandrestore'}
</div>

{if !$curl_exists}
    <div class="curl_warning alert alert-warning warn">
        <p>
            {l s='PHP curl not loaded. Curl is required to increase performance if you have backup files larger than 2 GB. Please enable it in your hosting management if this is the case.' mod='ntbackupandrestore'}
        </p>
    </div>
    <br/>
{/if}

<div class="multi_config {if $light}light_version{/if}">
    <p>
        <select name="choose_config" id="choose_config">
            {foreach $list_config as $config}
                <option value="{$config.id_ntbr_config|intval}" {if $config.id_ntbr_config == $id_current_config}selected="selected"{/if}>
                    {$config.name|escape:'htmlall':'UTF-8'}
                </option>
            {/foreach}
        </select>
    </p>
</div>

{foreach $list_config as $config}
    {assign var='config_id' value=$config.id_ntbr_config|intval}

    <div id="config_{$config_id|intval}" class="panel form_config">
        <div class="panel multi_config {if $light}light_version{/if}">
            <div class="panel-heading">
                <i class="fas fa-cog"></i>&nbsp;{l s='Configuration profile' mod='ntbackupandrestore'}
                <input type="hidden" class="id_config" value="{$config_id|intval}"/>
            </div>
            <p>
                <label>{l s='Backup type:' mod='ntbackupandrestore'}</label>
                {if $config.type_backup == $backup_type_complete}
                    {l s='Complete' mod='ntbackupandrestore'}
                {else}
                    {if $config.type_backup == $backup_type_file}
                        {l s='File' mod='ntbackupandrestore'}
                    {else}
                        {l s='Dump' mod='ntbackupandrestore'}
                    {/if}
                {/if}
            </p>
            <p>
                <label for="name_{$config_id|intval}">{l s='Name' mod='ntbackupandrestore'}</label>
                <span>
                    <input
                        type="text" name="name_{$config_id|intval}" id="name_{$config_id|intval}" value="{$config.name|escape:'html':'UTF-8'}"
                        title="{l s='Configuration\'s name' mod='ntbackupandrestore'}"
                    />
                </span>
            </p>
            <p class="default_config">
                <label>{l s='Default configuration' mod='ntbackupandrestore'}</label>
                <span class="switch prestashop-switch fixed-width-lg {if $light}deactivate{/if}">
                    <input
                        type="radio" name="is_default_{$config_id|intval}" id="is_default_on_{$config_id|intval}"
                        value="1" {if $config.is_default}checked="checked"{/if} class="is_default_on"
                    />
                    <label class="t" for="is_default_on_{$config_id|intval}">
                        {l s='Yes' mod='ntbackupandrestore'}
                    </label>
                    <input
                        type="radio" name="is_default_{$config_id|intval}" id="is_default_off_{$config_id|intval}"
                        value="0" {if !$config.is_default}checked="checked"{/if} class="is_default_off"
                    />
                    <label class="t" for="is_default_off_{$config_id|intval}">
                        {l s='No' mod='ntbackupandrestore'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
            </p>
            <p>
                <label>{l s='Disable' mod='ntbackupandrestore'}</label>
                <span class="switch prestashop-switch fixed-width-lg">
                    <input
                        type="radio" name="disable_{$config_id|intval}" id="disable_on_{$config_id|intval}"
                        value="1" {if $config.disable}checked="checked"{/if} class="disable_on"
                    />
                    <label class="t" for="disable_on_{$config_id|intval}">
                        {l s='Yes' mod='ntbackupandrestore'}
                    </label>
                    <input
                        type="radio" name="disable_{$config_id|intval}" id="disable_off_{$config_id|intval}"
                        value="0" {if !$config.disable}checked="checked"{/if} class="disable_off"
                    />
                    <label class="t" for="disable_off_{$config_id|intval}">
                        {l s='No' mod='ntbackupandrestore'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
            </p>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <i class="far fa-hdd"></i>&nbsp;{l s='Backups to keep on this server' mod='ntbackupandrestore'}
            </div>
            <p>
                <label for="nb_backup_{$config_id|intval}">
                    {l s='Backup to keep locally. 0 to never delete old backups' mod='ntbackupandrestore'}
                </label>
                <span>
                    <input
                        type="text" name="nb_backup_{$config_id|intval}" id="nb_backup_{$config_id|intval}" value="{$config.nb_backup|intval}"
                        title="{l s='Delete old backups in local. 0 to never delete old backups' mod='ntbackupandrestore'}"
                    />
                </span>
            </p>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <i class="fas fa-envelope"></i>&nbsp;
                <span>
                    {l s='Send an email with the date and hour of the beginning and end of the backup and the result message.' mod='ntbackupandrestore'}
                </span>
            </div>
            <p>
                <span class="switch prestashop-switch fixed-width-lg">
                    <input
                        type="radio" name="send_email_{$config_id|intval}" class="send_email_on" id="send_email_on_{$config_id|intval}"
                        value="1" {if $config.send_email}checked="checked"{/if}
                    />
                    <label class="t" for="send_email_on_{$config_id|intval}">
                        {l s='Yes' mod='ntbackupandrestore'}
                    </label>
                    <input
                        type="radio" name="send_email_{$config_id|intval}" class="send_email_off" id="send_email_off_{$config_id|intval}"
                        value="0" {if !$config.send_email}checked="checked"{/if}
                    />
                    <label class="t" for="send_email_off_{$config_id|intval}">
                        {l s='No' mod='ntbackupandrestore'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
            </p>
            <div id="change_mail_{$config_id|intval}" class="panel change_mail">
                <div class="panel-heading">
                    <i class="fas fa-cog"></i>&nbsp;
                    {l s='Send an email with the date and hour of the beginning and end of the backup and the result message.' mod='ntbackupandrestore'}
                </div>
                <p>
                    <label>{l s='Send an email only if there is an error' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" name="email_only_error_{$config_id|intval}" id="email_only_error_on_{$config_id|intval}"
                            value="1" {if $config.email_only_error}checked="checked"{/if}
                        />
                        <label class="t" for="email_only_error_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="email_only_error_{$config_id|intval}" id="email_only_error_off_{$config_id|intval}"
                            value="0" {if !$config.email_only_error}checked="checked"{/if}
                        />
                        <label class="t" for="email_only_error_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p>
                    <label for="mail_backup_{$config_id|intval}">
                        {l s='Emails you want to use to receive message from this module (separated by ";" if more than one)' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="mail_backup_{$config_id|intval}" id="mail_backup_{$config_id|intval}"
                            value="{$config.mail_backup|escape:'html':'UTF-8'}" title="{l s='You will receive your notification on those emails' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <i class="fas fa-envelope"></i>&nbsp;
                <span>
                    {l s='Receive an email when there is a new version.' mod='ntbackupandrestore'}
                </span>
            </div>
            <p>
                <span class="switch prestashop-switch fixed-width-lg">
                    <input
                        type="radio" name="receive_email_version_{$config_id|intval}" class="receive_email_version_on" id="receive_email_version_on_{$config_id|intval}"
                        value="1" {if $config.receive_email_version}checked="checked"{/if}
                    />
                    <label class="t" for="receive_email_version_on_{$config_id|intval}">
                        {l s='Yes' mod='ntbackupandrestore'}
                    </label>
                    <input
                        type="radio" name="receive_email_version_{$config_id|intval}" class="receive_email_version_off" id="receive_email_version_off_{$config_id|intval}"
                        value="0" {if !$config.receive_email_version}checked="checked"{/if}
                    />
                    <label class="t" for="receive_email_version_off_{$config_id|intval}">
                        {l s='No' mod='ntbackupandrestore'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
            </p>
            <div id="change_mail_version_{$config_id|intval}" class="panel change_mail_version">
                <div class="panel-heading">
                    <i class="fas fa-cog"></i>&nbsp;
                    {l s='Receive an email when there is a new version.' mod='ntbackupandrestore'}
                </div>
                <p>
                    <label for="mail_version_{$config_id|intval}">
                        {l s='Emails you want to use to receive message when there is a new version (separated by ";" if more than one)' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="mail_version_{$config_id|intval}" id="mail_version_{$config_id|intval}"
                            value="{$config.mail_version|escape:'html':'UTF-8'}" title="{l s='You will receive your notification on those emails' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <i class="fas fa-share"></i>&nbsp;<span>{l s='Send away.' mod='ntbackupandrestore'}</span>
            </div>
            {if $light}
                <div class="light_version_error alert alert-info hint">
                    <p>
                        {l s='Remote sending allows you to secure your backup by sending it automatically to another physical location. If your server is unavailable (crash, hack, fire ...), you can restore your shop with your backup located elsewhere. This feature is only available in the' mod='ntbackupandrestore'}
                        <a href="{$link_full_version|escape:'htmlall':'UTF-8'}">
                            {l s='full version of the module' mod='ntbackupandrestore'}
                        </a>.
                        {l s='You can send your backups to' mod='ntbackupandrestore'}
                        {$ntbr_ftp_name|escape:'html':'UTF-8'},
                        FTPS,
                        {$ntbr_sftp_name|escape:'html':'UTF-8'},
                        {$ntbr_dropbox_name|escape:'html':'UTF-8'},
                        {$ntbr_owncloud_name|escape:'html':'UTF-8'},
                        {$ntbr_webdav_name|escape:'html':'UTF-8'},
                        {$ntbr_googledrive_name|escape:'html':'UTF-8'},
                        {$ntbr_googledrive_name|escape:'html':'UTF-8'|cat:' G Suite'},
                        {$ntbr_googlecloud_name|escape:'html':'UTF-8'},
                        {'Microsoft '|cat:$ntbr_onedrive_name|escape:'html':'UTF-8'},
                        {'Microsoft '|cat:$ntbr_onedrive_name|escape:'html':'UTF-8'|cat:' Business'},
                        {$ntbr_shadow_drive_name|escape:'html':'UTF-8'},
                        {'Amazon AWS '|cat:$ntbr_s3_name|escape:'html':'UTF-8'},
                        {'Minio '|cat:$ntbr_s3_name|escape:'html':'UTF-8'},
                        {'Wasabi '|cat:$ntbr_s3_name|escape:'html':'UTF-8'},
                        {$ntbr_yandex_name|escape:'html':'UTF-8'},
                        {$ntbr_box_name|escape:'html':'UTF-8'},
                        {$ntbr_pcloud_name|escape:'html':'UTF-8'}.
                    </p>
                </div>
                <br/>
            {/if}
            <div class="{if $light}light_version{/if} send_away_block">
                {include file="./ftp.tpl"}
                {include file="./dropbox.tpl"}
                {include file="./yandex.tpl"}
                {include file="./owncloud.tpl"}
                {include file="./webdav.tpl"}
                {include file="./googledrive.tpl"}
                {include file="./googlecloud.tpl"}
                {include file="./pcloud.tpl"}
                {include file="./box.tpl"}
                {include file="./onedrive.tpl"}
                {include file="./shadow_drive.tpl"}
                {include file="./aws.tpl"}
                {include file="./sugarsync.tpl"}

                <div class="panel">
                    <div class="panel-heading">
                        <i class="fas fa-history"></i>&nbsp;{l s='Send the restore file too.' mod='ntbackupandrestore'}
                    </div>
                    <p class="send_restore {if $light}deactivate{/if}">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="send_restore_{$config_id|intval}" id="send_restore_on_{$config_id|intval}" value="1" {if $config.send_restore}checked="checked"{/if}/>
                            <label class="t" for="send_restore_on_{$config_id|intval}">
                                {l s='Yes' mod='ntbackupandrestore'}
                            </label>
                            <input type="radio" name="send_restore_{$config_id|intval}" id="send_restore_off_{$config_id|intval}" value="0" {if !$config.send_restore}checked="checked"{/if}/>
                            <label class="t" for="send_restore_off_{$config_id|intval}">
                                {l s='No' mod='ntbackupandrestore'}
                            </label>
                            <a class="slide-button btn"></a>
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <div class="panel">
            {if $light}
                <div class="light_version_error alert alert-info hint">
                    <p>
                        {l s='These advanced options are only available in the' mod='ntbackupandrestore'}
                        <a href="{$link_full_version|escape:'htmlall':'UTF-8'}">
                            {l s='full version of the module' mod='ntbackupandrestore'}
                        </a>.
                    </p>
                </div>
                <br/>
            {/if}
            {if $config.type_backup != $backup_type_base}
            <div {if $light}class="light_version"{/if}>
                <p>
                    <label>
                        {l s='Enable file mapping during backup.' mod='ntbackupandrestore'}
                    </label>
                    <span class="switch prestashop-switch fixed-width-lg scan_files {if $light}deactivate{/if}">
                        <input
                            type="radio" name="scan_files_{$config_id|intval}" id="scan_files_on_{$config_id|intval}"
                            value="1" {if $config.scan_files}checked="checked"{/if}
                        />
                        <label class="t" for="scan_files_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="scan_files_{$config_id|intval}" id="scan_files_off_{$config_id|intval}"
                            class="scan_files_off" value="0" {if !$config.scan_files}checked="checked"{/if}
                        />
                        <label class="t" for="scan_files_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
            </div>
            {/if}
        </div>
        {include file="./advanced_configuration.tpl"}
        <div class="panel-footer">
            <button id="nt_delete_config_btn_{$config_id|intval}" class="btn btn-default pull-right nt_delete_config_btn multi_config">
                <i class="fas fa-trash-alt process_icon"></i> {l s='Remove this profile' mod='ntbackupandrestore'}
            </button>
            <button id="nt_save_config_btn_{$config_id|intval}" class="btn btn-default pull-right nt_save_config_btn">
                <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
            </button>
        </div>
    </div>
{/foreach}
<div class="panel">
    <div {if $light}class="light_version"{/if}>
        <p>
            <label>{l s='Multiple configurations' mod='ntbackupandrestore'}</label>
            <span class="switch prestashop-switch fixed-width-lg {if $light}deactivate{/if}">
                <input type="radio" name="multi_config" class="multi_config_on" id="multi_config_on" value="1" {if $multi_config}checked="checked"{/if}/>
                <label class="t" for="multi_config_on">
                    {l s='Yes' mod='ntbackupandrestore'}
                </label>
                <input type="radio" name="multi_config" class="multi_config_off" id="multi_config_off" value="0" {if !$multi_config}checked="checked"{/if}/>
                <label class="t" for="multi_config_off">
                    {l s='No' mod='ntbackupandrestore'}
                </label>
                <a class="slide-button btn"></a>
            </span>
        </p>
    </div>
    <div class="panel-footer">
        <button id="nt_save_multi_config_btn" class="btn btn-default pull-right">
            <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
        </button>
    </div>
</div>
<p>
    <button type="button" class="btn btn-default" name="display_progress" id="display_progress">
        {l s='Enable progress display for the running backup.' mod='ntbackupandrestore'}
    </button>
</p>
