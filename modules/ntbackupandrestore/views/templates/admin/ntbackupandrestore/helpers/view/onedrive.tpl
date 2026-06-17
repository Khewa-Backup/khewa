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
        <i class="icon_send_away icon_send_onedrive"></i>&nbsp;
        <span>
            {l s='Send the backup on a %1$s account.' sprintf=$ntbr_onedrive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </span>
    </div>
    <div class="open_config_send_away_account">
        {if !$fct_crypt_exists}
            <div class="fct_crypt_error error alert alert-danger">
                <p>
                    {l s='%1$s cannot work with your current configuration. Please check the following requirements:' sprintf=$ntbr_onedrive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                </p>
                <ul>
                    <li>
                        {l s='PHP openssl is not loaded. Please enable it in your hosting management to use %1$s.' sprintf=$ntbr_onedrive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </li>
                </ul>
            </div>
            <br/>
        {/if}

        <p {if !$fct_crypt_exists || $light}class="deactivate"{/if}>
            <button type="button" id="send_onedrive_{$config_id|intval}" name="send_onedrive_{$config_id|intval}"
                class="btn btn-default send_onedrive {if $config.nb_onedrive_active_accounts > 0}enable{else}{if $config.nb_onedrive_accounts > 0}disable{/if}{/if}"
            >
                <i class="fas fa-cog"></i> {l s='Accounts configuration' mod='ntbackupandrestore'}
            </button>
        </p>
    </div>
    <div id="config_onedrive_accounts_{$config_id|intval}" class="panel config_send_away_account config_onedrive_accounts">
        <div class="panel-heading">
            <i class="fas fa-cog"></i>&nbsp;{l s='Send the backup on a %1$s account.' sprintf=$ntbr_onedrive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </div>
        <input type="hidden" class="nb_account" name="nb_onedrive_account" value="{$onedrive_default.nb_account|intval}"/>
        <div>
            <p class="account_list" id="onedrive_tabs_{$config_id|intval}">
                <label>{l s='Account' mod='ntbackupandrestore'}</label>
                {assign var="active" value=1}
                {foreach $config.onedrive_accounts as $onedrive_account}
                    <button
                        type="button" value="{$onedrive_account.id_ntbr_onedrive|intval}" id="onedrive_account_{$config_id|intval}_{$onedrive_account.id_ntbr_onedrive|intval}"
                        class="btn btn-default choose_onedrive_account {if $active == 1}active{else}inactive{/if} {if $onedrive_account.active == 1}enable{else}disable{/if}"
                    >
                        {$onedrive_account.name|escape:'html':'UTF-8'}
                    </button>
                    {assign var="active" value=0}
                {/foreach}
                <button
                    type="button" id="onedrive_account_{$config_id|intval}_0" value="0" title="{l s='Add a new account (display an empty form)' mod='ntbackupandrestore'}"
                    class="btn btn-default choose_onedrive_account {if $active == 1}active{else}inactive{/if}"
                >
                    <i class="fas fa-plus"></i>
                </button>
            </p>
            <div class="onedrive_account" id="onedrive_account_{$config_id|intval}">
                {if isset($config.onedrive_accounts.0)}
                    {assign var="onedrive_id" value=$config.onedrive_accounts.0.id_ntbr_onedrive|intval}
                    {assign var="onedrive_name" value=$config.onedrive_accounts.0.name|escape:'html':'UTF-8'}
                    {assign var="onedrive_active" value=$config.onedrive_accounts.0.active|intval}
                    {assign var="onedrive_business" value=$config.onedrive_accounts.0.business|intval}
                    {assign var="onedrive_nb_backup" value=$config.onedrive_accounts.0.config_nb_backup|intval}
                    {assign var="onedrive_code" value=$fake_mdp|escape:'html':'UTF-8'}
                    {assign var="onedrive_directory_path" value=$config.onedrive_accounts.0.directory_path|escape:'html':'UTF-8'}
                    {assign var="onedrive_directory_key" value=$config.onedrive_accounts.0.directory_key|escape:'html':'UTF-8'}
                {else}
                    {assign var="onedrive_id" value=$onedrive_default.id_ntbr_onedrive|intval}
                    {assign var="onedrive_name" value=""}
                    {assign var="onedrive_active" value=$onedrive_default.active|intval}
                    {assign var="onedrive_business" value=$onedrive_default.business|intval}
                    {assign var="onedrive_nb_backup" value=$onedrive_default.config_nb_backup|intval}
                    {assign var="onedrive_code" value=""}
                    {assign var="onedrive_directory_path" value=$onedrive_default.directory_path|escape:'html':'UTF-8'}
                    {assign var="onedrive_directory_key" value=$onedrive_default.directory_key|escape:'html':'UTF-8'}
                {/if}

                <p>
                    <input
                        type="hidden" id="id_ntbr_onedrive_{$config_id|intval}" name="id_ntbr_onedrive_{$config_id|intval}" value="{$onedrive_id|intval}"
                        data-origin="{$onedrive_id|intval}" data-default="{$onedrive_default.id_ntbr_onedrive|intval}"
                    />
                    <label for="onedrive_name_{$config_id|intval}">
                        {l s='Account name' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="onedrive_name_{$config_id|intval}" id="onedrive_name_{$config_id|intval}" value="{$onedrive_name|escape:'html':'UTF-8'}" class="name_account"
                            data-origin="{$onedrive_name|escape:'html':'UTF-8'}" data-default="" placeholder="{l s='Fill in a name for this new account' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='Enabled' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" id="active_onedrive_on_{$config_id|intval}" name="active_onedrive_{$config_id|intval}" value="1"
                            {if $onedrive_active}checked="checked"{/if} data-origin="{$onedrive_active|intval}" data-default="{$onedrive_default.active|intval}"
                        />
                        <label class="t" for="active_onedrive_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" id="active_onedrive_off_{$config_id|intval}" name="active_onedrive_{$config_id|intval}" value="0"
                            {if !$onedrive_active}checked="checked"{/if} data-origin="{$onedrive_active|intval}" data-default="{$onedrive_default.active|intval}"
                        />
                        <label class="t" for="active_onedrive_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p>
                    <label>{l s='Business' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" id="business_onedrive_on_{$config_id|intval}" name="business_onedrive_{$config_id|intval}" value="1"
                            {if $onedrive_business}checked="checked"{/if} data-origin="{$onedrive_business|intval}" data-default="{$onedrive_default.business|intval}"
                        />
                        <label class="t" for="business_onedrive_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" id="business_onedrive_off_{$config_id|intval}" name="business_onedrive_{$config_id|intval}" value="0"
                            {if !$onedrive_business}checked="checked"{/if} data-origin="{$onedrive_business|intval}" data-default="{$onedrive_default.business|intval}"
                        />
                        <label class="t" for="business_onedrive_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p>
                    <label for="nb_keep_backup_onedrive_{$config_id|intval}">
                        {l s='Backup to keep. 0 to never delete old backups' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            name="nb_keep_backup_onedrive_{$config_id|intval}" id="nb_keep_backup_onedrive_{$config_id|intval}" value="{$onedrive_nb_backup|intval}" data-origin="{$onedrive_nb_backup|intval}"
                            type="text" data-default="{$onedrive_default.config_nb_backup|intval}" title="{l s='Delete old backups. 0 to never delete old backups' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='1.' mod='ntbackupandrestore'}</label>
                    <button
                        type="button" name="authentification_onedrive_{$config_id|intval}" class="btn btn-default"
                        id="authentification_onedrive_{$config_id|intval}" onclick="getOnedriveAuthorizeUrl({$config_id|intval});" {*onclick="window.open('{$onedrive_authorizeUrl|escape:'html':'UTF-8'}');"*}
                    >
                        <i class="fas fa-cloud"></i> - {l s='Authentication' mod='ntbackupandrestore'}
                    </button>
                </p>
                <p>
                    <label>{l s='2. Click "Allow" (you might have to log in first)' mod='ntbackupandrestore'}</label>
                </p>
                <p>
                    <label for="onedrive_code_{$config_id|intval}">
                        {l s='3. Copy the authorization code' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="password" name="onedrive_code_{$config_id|intval}" id="onedrive_code_{$config_id|intval}"
                            value="{$onedrive_code|escape:'html':'UTF-8'}" data-origin="{$onedrive_code|escape:'html':'UTF-8'}" data-default=""
                        />
                    </span>
                </p>
                <div class="{if !$onedrive_id}hide{/if} directory_block">
                    <p>
                        <label for="onedrive_dir_path_{$config_id|intval}">
                            {l s='Directory' mod='ntbackupandrestore'}
                        </label>
                        <span>
                            <input
                                type="text" name="onedrive_dir_path_{$config_id|intval}" readonly="readonly" id="onedrive_dir_path_{$config_id|intval}" class="onedrive_dir_path"
                                value="{$onedrive_directory_path|escape:'html':'UTF-8'}"
                                data-origin="{$onedrive_directory_path|escape:'html':'UTF-8'}" data-default="{$onedrive_default.directory_path|escape:'html':'UTF-8'}"
                            />
                        </span>
                    </p>
                    <p>
                        <span>
                            <button
                                type="button" name="display_onedrive_tree_{$config_id|intval}" id="display_onedrive_tree_{$config_id|intval}" class="btn btn-default display_onedrive_tree"
                            >
                                <i class="fas fa-sitemap"></i> {l s='Display list of directories' mod='ntbackupandrestore'}
                            </button>
                            <input
                                type="hidden" name="onedrive_dir_{$config_id|intval}" id="onedrive_dir_{$config_id|intval}" value="{$onedrive_directory_key|escape:'html':'UTF-8'}"
                                data-origin="{$onedrive_directory_key|escape:'html':'UTF-8'}" data-default="{$onedrive_default.directory_key|escape:'html':'UTF-8'}" class="onedrive_dir"
                            />
                        </span>
                    </p>
                    <p class="tree_block" id="onedrive_tree_{$config_id|intval}"></p>
                </div>
                <p>
                    <button type="button" name="get_files_onedrive_{$config_id|intval}" id="get_files_onedrive_{$config_id|intval}" class="btn btn-default get_files_onedrive display_2nt">
                        <i class="fas fa-list"></i> {l s='List %1$s files' sprintf=$ntbr_onedrive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </button>
                </p>
                <p class="file_block" id="onedrive_files_{$config_id|intval}"></p>
            </div>
        </div>
        <div class="panel-footer">
            <button type="button" id="save_onedrive_{$config_id|intval}" name="save_onedrive_{$config_id|intval}" class="btn btn-default save_onedrive">
                <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
            </button>
            <button type="button" id="check_onedrive_{$config_id|intval}" name="check_onedrive_{$config_id|intval}" class="btn btn-default check_onedrive {if !$onedrive_id}hide{/if}">
                <i class="fas fa-sync-alt process_icon"></i> {l s='Check connection' mod='ntbackupandrestore'}
            </button>
            <button type="button" id="delete_onedrive_{$config_id|intval}" name="delete_onedrive_{$config_id|intval}" class="btn btn-default delete_onedrive">
                <i class="fas fa-trash-alt process_icon"></i> {l s='Delete' mod='ntbackupandrestore'}
            </button>
        </div>
    </div>
</div>