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
        <i class="icon_send_away icon_send_googledrive"></i>&nbsp;
        <span>
            {l s='Send the backup on a %1$s account.' sprintf=$ntbr_googledrive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </span>
    </div>
    <div class="open_config_send_away_account">
        {if !$fct_crypt_exists}
            <div class="fct_crypt_error error alert alert-danger">
                <p>
                    {l s='%1$s cannot work with your current configuration. Please check the following requirements:' sprintf=$ntbr_googledrive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                </p>
                <ul>
                    <li>
                        {l s='PHP openssl is not loaded. Please enable it in your hosting management to use %1$s.' sprintf=$ntbr_googledrive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </li>
                </ul>
            </div>
            <br/>
        {/if}

        <p {if !$fct_crypt_exists || $light}class="deactivate"{/if}>
            <button type="button" id="send_googledrive_{$config_id|intval}" name="send_googledrive_{$config_id|intval}"
                class="btn btn-default send_googledrive {if $config.nb_googledrive_active_accounts > 0}enable{else}{if $config.nb_googledrive_accounts > 0}disable{/if}{/if}"
            >
                <i class="fas fa-cog"></i> {l s='Accounts configuration' mod='ntbackupandrestore'}
            </button>
        </p>

        <div class="panel" id="googledrive_api_conf">
            <div class="panel-heading">
                <span>
                    <i class="fas fa-cog"></i> &nbsp;{l s='%1$s API configuration' sprintf=$ntbr_googledrive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                </span>
            </div>
            <div>
                <p>
                    {l s='To send your files to a Google Drive account, you need to create an application on your Google account. Please follow the instructions in this file:' mod='ntbackupandrestore'}
                    <a href="{$googledrive_app|escape:'html':'UTF-8'}" title="{$googledrive_app_name|escape:'html':'UTF-8'}" target="_blank">
                        <img src="../modules/ntbackupandrestore/views/img/logo_pdf_min.png"/>
                    </a>
                </p>
                <p {if !$fct_crypt_exists || $light}class="deactivate"{/if}>
                    <label for="googledrive_client_id">{l s='Client ID' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="googledrive_client_id" id="googledrive_client_id" class="googledrive_client_id"
                            value="{$googledrive_client_id|escape:'html':'UTF-8'}" title="{l s='Client ID' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p {if !$fct_crypt_exists || $light}class="deactivate"{/if}>
                    <label for="googledrive_client_secret">{l s='Client secret' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="googledrive_client_secret" id="googledrive_client_secret" class="googledrive_client_secret"
                            value="{$googledrive_client_secret|escape:'html':'UTF-8'}" title="{l s='Client secret' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
            </div>
            <div class="panel-footer">
                <p>
                {l s='After saving, you will be able to configure your Google Drive accounts.' mod='ntbackupandrestore'}
                </p>
                <button type="button" class="btn btn-default save_googledrive_api_conf" id="save_googledrive_api_conf" name="save_googledrive_api_conf">
                    <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
                </button>
            </div>
        </div>
    </div>
    <div id="config_googledrive_accounts_{$config_id|intval}" class="panel config_send_away_account config_googledrive_accounts">
        <div class="panel-heading">
            <i class="fas fa-cog"></i> &nbsp;{l s='Send the backup on a %1$s account.' sprintf=$ntbr_googledrive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </div>
        <input type="hidden" class="nb_account" name="nb_googledrive_account" value="{$googledrive_default.nb_account|intval}"/>
        <div>
            <p class="account_list" id="googledrive_tabs_{$config_id|intval}">
                <label>{l s='Account' mod='ntbackupandrestore'}</label>
                {assign var="active" value=1}
                {foreach $config.googledrive_accounts as $googledrive_account}
                    <button
                        type="button" value="{$googledrive_account.id_ntbr_googledrive|intval}"
                        id="googledrive_account_{$config_id|intval}_{$googledrive_account.id_ntbr_googledrive|intval}"
                        class="btn btn-default choose_googledrive_account {if $active == 1}active{else}inactive{/if} {if $googledrive_account.active == 1}enable{else}disable{/if}"
                    >
                        {$googledrive_account.name|escape:'html':'UTF-8'}
                    </button>
                    {assign var="active" value=0}
                {/foreach}
                <button
                    type="button" id="googledrive_account_{$config_id|intval}_0" value="0" title="{l s='Add a new account (display an empty form)' mod='ntbackupandrestore'}"
                    class="btn btn-default choose_googledrive_account {if $active == 1}active{else}inactive{/if}"
                >
                    <i class="fas fa-plus"></i>
                </button>
            </p>
            <div class="googledrive_account" id="googledrive_account_{$config_id|intval}">
                {if isset($config.googledrive_accounts.0)}
                    {assign var="googledrive_id" value=$config.googledrive_accounts.0.id_ntbr_googledrive|intval}
                    {assign var="googledrive_name" value=$config.googledrive_accounts.0.name|escape:'html':'UTF-8'}
                    {assign var="googledrive_active" value=$config.googledrive_accounts.0.active|intval}
                    {assign var="googledrive_nb_backup" value=$config.googledrive_accounts.0.config_nb_backup|intval}
                    {assign var="googledrive_code" value=$fake_mdp|escape:'html':'UTF-8'}
                    {assign var="googledrive_directory_path" value=$config.googledrive_accounts.0.directory_path|escape:'html':'UTF-8'}
                    {assign var="googledrive_directory_key" value=$config.googledrive_accounts.0.directory_key|escape:'html':'UTF-8'}
                {else}
                    {assign var="googledrive_id" value=$googledrive_default.id_ntbr_googledrive|intval}
                    {assign var="googledrive_name" value=""}
                    {assign var="googledrive_active" value=$googledrive_default.active|intval}
                    {assign var="googledrive_nb_backup" value=$googledrive_default.config_nb_backup|intval}
                    {assign var="googledrive_code" value=""}
                    {assign var="googledrive_directory_path" value=$googledrive_default.directory_path|escape:'html':'UTF-8'}
                    {assign var="googledrive_directory_key" value=$googledrive_default.directory_key|escape:'html':'UTF-8'}
                {/if}

                <p>
                    <input type="hidden" id="id_ntbr_googledrive_{$config_id|intval}" name="id_ntbr_googledrive_{$config_id|intval}" value="{$googledrive_id|intval}"
                        data-origin="{$googledrive_id|intval}" data-default="{$googledrive_default.id_ntbr_googledrive|intval}"
                    />
                    <label for="googledrive_name_{$config_id|intval}">
                        {l s='Account name' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="googledrive_name_{$config_id|intval}" id="googledrive_name_{$config_id|intval}" class="name_account"
                            value="{$googledrive_name|escape:'html':'UTF-8'}" data-origin="{$googledrive_name|escape:'html':'UTF-8'}" data-default=""
                            placeholder="{l s='Fill in a name for this new account' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='Enabled' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" name="active_googledrive_{$config_id|intval}" id="active_googledrive_on_{$config_id|intval}" value="1"
                            {if $googledrive_active}checked="checked"{/if} data-origin="{$googledrive_active|intval}" data-default="{$googledrive_default.active|intval}"
                        />
                        <label class="t" for="active_googledrive_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="active_googledrive_{$config_id|intval}" id="active_googledrive_off_{$config_id|intval}" value="0"
                            {if !$googledrive_active}checked="checked"{/if} data-origin="{$googledrive_active|intval}" data-default="{$googledrive_default.active|intval}"
                        />
                        <label class="t" for="active_googledrive_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p>
                    <label for="nb_keep_backup_googledrive_{$config_id|intval}">
                        {l s='Backup to keep. 0 to never delete old backups' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="nb_keep_backup_googledrive_{$config_id|intval}" id="nb_keep_backup_googledrive_{$config_id|intval}" class="nb_keep_backup_googledrive"
                            value="{$googledrive_nb_backup|intval}" data-origin="{$googledrive_nb_backup|intval}" data-default="{$googledrive_default.config_nb_backup|intval}"
                            title="{l s='Delete old backups. 0 to never delete old backups' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='1.' mod='ntbackupandrestore'}</label>
                    <button
                        type="button" id="authentification_googledrive_{$config_id|intval}" name="authentification_googledrive_{$config_id|intval}" class="btn btn-default"
                        onclick="window.open('{$googledrive_authorizeUrl|escape:'html':'UTF-8'}');"
                    >
                        <i class="fab fa-google-drive"></i> - {l s='Authentication' mod='ntbackupandrestore'}
                    </button>
                </p>
                <p>
                    <label>
                        {l s='2. Click "Allow" (you might have to log in first)' mod='ntbackupandrestore'}
                    </label>
                </p>
                <p>
                    <label for="googledrive_code_{$config_id|intval}">
                        {l s='3. Copy the authorization code' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="password" name="googledrive_code_{$config_id|intval}" id="googledrive_code_{$config_id|intval}"
                            value="{$googledrive_code|escape:'html':'UTF-8'}" data-origin="{$googledrive_code|escape:'html':'UTF-8'}" data-default=""
                        />
                    </span>
                </p>
                <div class="{if !$googledrive_id}hide{/if} directory_block">
                    <p>
                        <label for="googledrive_dir_{$config_id|intval}">{l s='Directory' mod='ntbackupandrestore'}</label>
                        <span>
                            <input
                                type="text" id="googledrive_dir_path_{$config_id|intval}" readonly="readonly" name="googledrive_dir_path_{$config_id|intval}"
                                value="{$googledrive_directory_path|escape:'html':'UTF-8'}"
                                data-origin="{$googledrive_directory_path|escape:'html':'UTF-8'}" data-default="{$googledrive_default.directory_path|escape:'html':'UTF-8'}"
                            />
                        </span>
                    </p>
                    <p>
                        <span>
                            <button
                                type="button" class="btn btn-default display_googledrive_tree" id="display_googledrive_tree_{$config_id|intval}"
                                name="display_googledrive_tree_{$config_id|intval}"
                            >
                                <i class="fas fa-sitemap"></i> {l s='Display list of directories' mod='ntbackupandrestore'}
                            </button>
                            <input
                                type="hidden" id="googledrive_dir_{$config_id|intval}" name="googledrive_dir_{$config_id|intval}" value="{$googledrive_directory_key|escape:'html':'UTF-8'}"
                                data-origin="{$googledrive_directory_key|escape:'html':'UTF-8'}" data-default="{$googledrive_default.directory_key|escape:'html':'UTF-8'}"
                            />
                        </span>
                    </p>
                    <p class="tree_block" id="googledrive_tree_{$config_id|intval}"></p>
                </div>
                <p>
                    <button type="button" name="get_files_googledrive_{$config_id|intval}" id="get_files_googledrive_{$config_id|intval}" class="btn btn-default get_files_googledrive display_2nt">
                        <i class="fas fa-list"></i> {l s='List %1$s files' sprintf=$ntbr_googledrive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </button>
                </p>
                <p class="file_block" id="googledrive_files_{$config_id|intval}"></p>
            </div>
        </div>
        <div class="panel-footer">
            <button type="button" class="btn btn-default save_googledrive" id="save_googledrive_{$config_id|intval}" name="save_googledrive_{$config_id|intval}">
                <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
            </button>
            <button
                type="button" id="check_googledrive_{$config_id|intval}" name="check_googledrive_{$config_id|intval}"
                class="btn btn-default check_googledrive {if !$googledrive_id}hide{/if}"
            >
                <i class="fas fa-sync-alt process_icon"></i> {l s='Check connection' mod='ntbackupandrestore'}
            </button>
            <button type="button" class="btn btn-default delete_googledrive" id="delete_googledrive_{$config_id|intval}" name="delete_googledrive_{$config_id|intval}">
                <i class="fas fa-trash-alt process_icon"></i> {l s='Delete' mod='ntbackupandrestore'}
            </button>
        </div>
    </div>
</div>