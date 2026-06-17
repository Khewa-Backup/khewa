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

<div class="panel">
    <div class="panel-heading send_away_header">
        <i class="icon_send_away icon_send_ftp"></i>&nbsp;
        <span>
            {l s='Send the backup on a %1$s account.' sprintf=$ntbr_ftp_sftp_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </span>
    </div>
    <div class="open_config_send_away_account">
        {if !$fct_crypt_exists}
            <div class="fct_crypt_error error alert alert-danger">
                <p>
                    {l s='%1$s cannot work with your current configuration. Please check the following requirements:' sprintf=$ntbr_ftp_sftp_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                </p>
                <ul>
                    <li>
                        {l s='PHP openssl is not loaded. Please enable it in your hosting management to use %1$s.' sprintf=$ntbr_ftp_sftp_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </li>
                </ul>
            </div>
            <br/>
        {/if}
        <p {if !$fct_crypt_exists || $light}class="deactivate"{/if}>
            <button type="button" id="send_ftp_{$config_id|intval}" name="send_ftp_{$config_id|intval}"
                class="btn btn-default send_ftp {if $config.nb_ftp_active_accounts > 0}enable{else}{if $config.nb_ftp_accounts > 0}disable{/if}{/if}"
            >
                <i class="fas fa-cog"></i> {l s='Accounts configuration' mod='ntbackupandrestore'}
            </button>
        </p>
    </div>
    <div id="config_ftp_accounts_{$config_id|intval}" class="panel config_ftp_accounts config_send_away_account">
        <div class="panel-heading">
            <i class="fas fa-cog"></i>&nbsp;{l s='Send the backup on a %1$s account.' sprintf=$ntbr_ftp_sftp_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </div>
        <input type="hidden" class="nb_account" name="nb_ftp_account" value="{$ftp_default.nb_account|intval}"/>
        <div>
            <p class="account_list" id="ftp_tabs_{$config_id|intval}">
                <label>{l s='Account' mod='ntbackupandrestore'}</label>
                {assign var="active" value=1}
                {foreach $config.ftp_accounts as $ftp_account}
                    <button
                        type="button" id="ftp_account_{$config_id|intval}_{$ftp_account.id_ntbr_ftp|intval}" value="{$ftp_account.id_ntbr_ftp|intval}"
                        class="btn btn-default choose_ftp_account {if $active == 1}active{else}inactive{/if} {if $ftp_account.active == 1}enable{else}disable{/if}"
                    >
                        {$ftp_account.name|escape:'html':'UTF-8'}
                    </button>
                    {assign var="active" value=0}
                {/foreach}
                <button
                    type="button" id="ftp_account_{$config_id|intval}_0" value="0" title="{l s='Add a new account (display an empty form)' mod='ntbackupandrestore'}"
                    class="btn btn-default choose_ftp_account {if $active == 1}active{else}inactive{/if}"
                >
                    <i class="fas fa-plus"></i>
                </button>
            </p>
            <div class="ftp_account" id="ftp_account_{$config_id|intval}">
                {if isset($config.ftp_accounts.0)}
                    {assign var="ftp_id" value=$config.ftp_accounts.0.id_ntbr_ftp|intval}
                    {assign var="ftp_name" value=$config.ftp_accounts.0.name|escape:'html':'UTF-8'}
                    {assign var="ftp_active" value=$config.ftp_accounts.0.active|intval}
                    {assign var="ftp_nb_backup" value=$config.ftp_accounts.0.config_nb_backup|intval}
                    {assign var="ftp_sftp" value=$config.ftp_accounts.0.sftp|intval}
                    {assign var="ftp_ssl" value=$config.ftp_accounts.0.ssl|intval}
                    {assign var="ftp_passive_mode" value=$config.ftp_accounts.0.passive_mode|intval}
                    {assign var="ftp_nb_backup" value=$config.ftp_accounts.0.config_nb_backup|intval}
                    {assign var="ftp_server" value=$config.ftp_accounts.0.server|escape:'html':'UTF-8'}
                    {assign var="ftp_login" value=$config.ftp_accounts.0.login|escape:'html':'UTF-8'}
                    {assign var="ftp_pass" value=$fake_mdp|escape:'html':'UTF-8'}
                    {assign var="ftp_port" value=$config.ftp_accounts.0.port|intval}
                    {assign var="ftp_directory" value=$config.ftp_accounts.0.directory|escape:'html':'UTF-8'}
                {else}
                    {assign var="ftp_id" value=$ftp_default.id_ntbr_ftp|intval}
                    {assign var="ftp_name" value=""}
                    {assign var="ftp_active" value=$ftp_default.active|intval}
                    {assign var="ftp_nb_backup" value=$ftp_default.config_nb_backup|intval}
                    {assign var="ftp_sftp" value=$ftp_default.sftp|intval}
                    {assign var="ftp_ssl" value=$ftp_default.ssl|intval}
                    {assign var="ftp_passive_mode" value=$ftp_default.passive_mode|intval}
                    {assign var="ftp_nb_backup" value=$ftp_default.config_nb_backup|intval}
                    {assign var="ftp_server" value=$ftp_default.server|escape:'html':'UTF-8'}
                    {assign var="ftp_login" value=$ftp_default.login|escape:'html':'UTF-8'}
                    {assign var="ftp_pass" value=""}
                    {assign var="ftp_port" value=$ftp_default.port|intval}
                    {assign var="ftp_directory" value=$ftp_default.directory|escape:'html':'UTF-8'}
                {/if}

                <p>
                    <input
                        type="hidden" id="id_ntbr_ftp_{$config_id|intval}" name="id_ntbr_ftp_{$config_id|intval}"
                        value="{$ftp_id|intval}" data-origin="{$ftp_id|intval}" data-default="{$ftp_default.id_ntbr_ftp|intval}"
                    />
                    <label for="ftp_name_{$config_id|intval}">{l s='Account name' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="ftp_name_{$config_id|intval}" id="ftp_name_{$config_id|intval}" value="{$ftp_name|escape:'html':'UTF-8'}"
                            data-origin="{$ftp_name|escape:'html':'UTF-8'}" data-default="" class="name_account"
                            placeholder="{l s='Fill in a name for this new account' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='Enabled' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" name="active_ftp_{$config_id|intval}" id="active_ftp_on_{$config_id|intval}"
                            {if $ftp_active}checked="checked"{/if} value="1" data-origin="{$ftp_active|intval}" data-default="{$ftp_default.active|intval}"
                        />
                        <label class="t" for="active_ftp_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="active_ftp_{$config_id|intval}" id="active_ftp_off_{$config_id|intval}"
                            {if !$ftp_active}checked="checked"{/if} value="0" data-origin="{$ftp_active|intval}" data-default="{$ftp_default.active|intval}"
                        />
                        <label class="t" for="active_ftp_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p {if !$fct_crypt_exists}class="deactivate"{/if}>
                    <label class="send_sftp">
                        {l
                            s='%1$s (SSH File Transfer Protocol). It is different from %2$s or FTPS (File Transfer Protocol Secure). If you are not sure, it means it is not %1$s.'
                            sprintf=[$ntbr_sftp_name|escape:'html':'UTF-8', $ntbr_ftp_name|escape:'html':'UTF-8']
                            mod='ntbackupandrestore'
                        }
                    </label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" name="send_sftp_{$config_id|intval}" id="send_sftp_on_{$config_id|intval}" value="1" {if $ftp_sftp}checked="checked"{/if}
                            data-origin="{$ftp_sftp|intval}" data-default="{$ftp_default.sftp|intval}" class="send_sftp_on"
                        />
                        <label class="t" for="send_sftp_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="send_sftp_{$config_id|intval}" id="send_sftp_off_{$config_id|intval}" value="0" {if !$ftp_sftp}checked="checked"{/if}
                            data-origin="{$ftp_sftp|intval}" data-default="{$ftp_default.sftp|intval}" class="send_sftp_off"
                        />
                        <label class="t" for="send_sftp_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p class="option_ftp_ssl {if !$fct_crypt_exists || $os_windows}deactivate{/if}">
                    <label>{l s='SSL' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" name="ftp_ssl_{$config_id|intval}" id="ftp_ssl_on_{$config_id|intval}" {if $ftp_ssl}checked="checked"{/if} value="1"
                            data-origin="{$ftp_ssl|intval}" data-default="{$ftp_default.ssl|intval}" class="ftp_ssl_on"
                        />
                        <label class="t" for="ftp_ssl_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="ftp_ssl_{$config_id|intval}" id="ftp_ssl_off_{$config_id|intval}" value="0" {if !$ftp_ssl}checked="checked"{/if}
                            data-origin="{$ftp_ssl|intval}" data-default="{$ftp_default.ssl|intval}" class="ftp_ssl_off"
                        />
                        <label class="t" for="ftp_ssl_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p class="option_ftp_pasv">
                    <label>{l s='Passive mode' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" name="ftp_pasv_{$config_id|intval}" id="ftp_pasv_on_{$config_id|intval}" value="1" {if $ftp_passive_mode}checked="checked"{/if}
                            data-origin="{$ftp_passive_mode|intval}" data-default="{$ftp_default.passive_mode|intval}" class="ftp_pasv_on"
                        />
                        <label class="t" for="ftp_pasv_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="ftp_pasv_{$config_id|intval}" id="ftp_pasv_off_{$config_id|intval}" value="0" {if !$ftp_passive_mode}checked="checked"{/if}
                            data-origin="{$ftp_passive_mode|intval}" data-default="{$ftp_default.passive_mode|intval}" class="ftp_pasv_off"
                        />
                        <label class="t" for="ftp_pasv_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p>
                    <label for="nb_keep_backup_ftp_{$config_id|intval}">
                        {l s='Backup to keep. 0 to never delete old backups' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="nb_keep_backup_ftp_{$config_id|intval}" id="nb_keep_backup_ftp_{$config_id|intval}" value="{$ftp_nb_backup|intval}"
                            data-origin="{$ftp_nb_backup|intval}" data-default="{$ftp_default.config_nb_backup|intval}"
                            title="{l s='Delete old backups. 0 to never delete old backups' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label for="ftp_server_{$config_id|intval}">{l s='Server' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="ftp_server_{$config_id|intval}" id="ftp_server_{$config_id|intval}" value="{$ftp_server|escape:'html':'UTF-8'}"
                            data-origin="{$ftp_server|escape:'html':'UTF-8'}" data-default="{$ftp_default.server|escape:'html':'UTF-8'}"
                        />
                    </span>
                </p>
                <p>
                    <label for="ftp_login_{$config_id|intval}">{l s='Login' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="ftp_login_{$config_id|intval}" id="ftp_login_{$config_id|intval}" value="{$ftp_login|escape:'html':'UTF-8'}"
                            data-origin="{$ftp_login|escape:'html':'UTF-8'}" data-default="{$ftp_default.login|escape:'html':'UTF-8'}"
                        />
                    </span>
                </p>
                <p>
                    <label for="ftp_pass_{$config_id|intval}">{l s='Password' mod='ntbackupandrestore'}</label>
                    <input type="password" class="decoy" value=""/>
                    <span>
                        <input
                            autocomplete="new-password" type="password" name="ftp_pass_{$config_id|intval}" id="ftp_pass_{$config_id|intval}"
                            value="{$ftp_pass|escape:'html':'UTF-8'}" data-origin="{$ftp_pass|escape:'html':'UTF-8'}" data-default=""
                        />
                    </span>
                </p>
                <p>
                    <label for="ftp_port_{$config_id|intval}">{l s='Port' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="ftp_port_{$config_id|intval}" id="ftp_port_{$config_id|intval}" value="{$ftp_port|intval}" data-origin="{$ftp_port|intval}"
                            data-default="{$ftp_default.port|intval}" class="ftp_port"
                        />
                    </span>
                </p>
                <p>
                    <label for="ftp_dir_{$config_id|intval}">{l s='Directory' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="ftp_dir_{$config_id|intval}" placeholder="{l s='Ex: /backups' mod='ntbackupandrestore'}" value="{$ftp_directory|escape:'html':'UTF-8'}"
                            data-origin="{$ftp_directory|escape:'html':'UTF-8'}" data-default="{$ftp_default.directory|escape:'html':'UTF-8'}" id="ftp_dir_{$config_id|intval}"
                        />
                    </span>
                </p>
                <p>
                    <button type="button" name="get_files_ftp_{$config_id|intval}" id="get_files_ftp_{$config_id|intval}" class="btn btn-default get_files_ftp display_2nt">
                        <i class="fas fa-list"></i> {l s='List %1$s files' sprintf=$ntbr_ftp_sftp_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </button>
                </p>
                <p class="file_block" id="ftp_files_{$config_id|intval}"></p>
            </div>
        </div>
        <div class="panel-footer">
            <button type="button" id="save_ftp_{$config_id|intval}" name="save_ftp_{$config_id|intval}" class="btn btn-default save_ftp">
                <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
            </button>
            <button type="button" class="btn btn-default check_ftp {if !$ftp_id}hide{/if}" id="check_ftp_{$config_id|intval}" name="check_ftp_{$config_id|intval}">
                <i class="fas fa-sync-alt process_icon"></i> {l s='Check connection' mod='ntbackupandrestore'}
            </button>
            <button type="button" class="btn btn-default delete_ftp" id="delete_ftp_{$config_id|intval}" name="delete_ftp_{$config_id|intval}">
                <i class="fas fa-trash-alt process_icon"></i> {l s='Delete' mod='ntbackupandrestore'}
            </button>
        </div>
    </div>
</div>