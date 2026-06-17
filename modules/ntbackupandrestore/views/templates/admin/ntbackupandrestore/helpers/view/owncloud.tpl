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
        <i class="icon_send_away icon_send_owncloud"></i>&nbsp;
        <span>
            {l s='Send the backup on a %1$s account.' sprintf=$ntbr_owncloud_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </span>
    </div>
    <div class="open_config_send_away_account">
        {if !$fct_crypt_exists}
            <div class="fct_crypt_error error alert alert-danger">
                <p>
                    {l s='%1$s cannot work with your current configuration. Please check the following requirements:' sprintf=$ntbr_owncloud_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                </p>
                <ul>
                    <li>
                        {l s='PHP openssl is not loaded. Please enable it in your hosting management to use %1$s.' sprintf=$ntbr_owncloud_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </li>
                </ul>
            </div>
            <br/>
        {/if}

        <p {if !$fct_crypt_exists || $light}class="deactivate"{/if}>
            <button type="button" id="send_owncloud_{$config_id|intval}" name="send_owncloud_{$config_id|intval}"
                class="btn btn-default send_owncloud {if $config.nb_owncloud_active_accounts > 0}enable{else}{if $config.nb_owncloud_accounts > 0}disable{/if}{/if}"
            >
                <i class="fas fa-cog"></i> {l s='Accounts configuration' mod='ntbackupandrestore'}
            </button>
        </p>
    </div>
    <div id="config_owncloud_accounts_{$config_id|intval}" class="panel config_send_away_account config_owncloud_accounts">
        <div class="panel-heading">
            <i class="fas fa-cog"></i>&nbsp;{l s='Send the backup on a %1$s account.' sprintf=$ntbr_owncloud_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </div>
        <input type="hidden" class="nb_account" name="nb_owncloud_account" value="{$owncloud_default.nb_account|intval}"/>
        <div>
            <p class="account_list" id="owncloud_tabs_{$config_id|intval}">
                <label>{l s='Account' mod='ntbackupandrestore'}</label>
                {assign var="active" value=1}
                {foreach $config.owncloud_accounts as $owncloud_account}
                    <button
                        type="button" value="{$owncloud_account.id_ntbr_owncloud|intval}" id="owncloud_account_{$config_id|intval}_{$owncloud_account.id_ntbr_owncloud|intval}"
                        class="btn btn-default choose_owncloud_account {if $active == 1}active{else}inactive{/if} {if $owncloud_account.active == 1}enable{else}disable{/if}"
                    >
                        {$owncloud_account.name|escape:'html':'UTF-8'}
                    </button>
                    {assign var="active" value=0}
                {/foreach}
                <button
                    type="button" id="owncloud_account_{$config_id|intval}_0" value="0" title="{l s='Add a new account (display an empty form)' mod='ntbackupandrestore'}"
                    class="btn btn-default choose_owncloud_account {if $active == 1}active{else}inactive{/if}"
                >
                    <i class="fas fa-plus"></i>
                </button>
            </p>
            <div class="owncloud_account" id="owncloud_account_{$config_id|intval}">
                {if isset($config.owncloud_accounts.0)}
                    {assign var="owncloud_id" value=$config.owncloud_accounts.0.id_ntbr_owncloud|intval}
                    {assign var="owncloud_name" value=$config.owncloud_accounts.0.name|escape:'html':'UTF-8'}
                    {assign var="owncloud_active" value=$config.owncloud_accounts.0.active|intval}
                    {assign var="owncloud_nb_backup" value=$config.owncloud_accounts.0.config_nb_backup|intval}
                    {assign var="owncloud_login" value=$config.owncloud_accounts.0.login|escape:'html':'UTF-8'}
                    {assign var="owncloud_pass" value=$fake_mdp|escape:'html':'UTF-8'}
                    {assign var="owncloud_server" value=$config.owncloud_accounts.0.server|escape:'html':'UTF-8'}
                    {assign var="owncloud_directory" value=$config.owncloud_accounts.0.directory|escape:'html':'UTF-8'}
                {else}
                    {assign var="owncloud_id" value=$owncloud_default.id_ntbr_owncloud|intval}
                    {assign var="owncloud_name" value=""}
                    {assign var="owncloud_active" value=$owncloud_default.active|intval}
                    {assign var="owncloud_nb_backup" value=$owncloud_default.config_nb_backup|intval}
                    {assign var="owncloud_login" value=$owncloud_default.login|escape:'html':'UTF-8'}
                    {assign var="owncloud_pass" value=""}
                    {assign var="owncloud_server" value=$owncloud_default.server|escape:'html':'UTF-8'}
                    {assign var="owncloud_directory" value=$owncloud_default.directory|escape:'html':'UTF-8'}
                {/if}

                <p>
                    <input
                        type="hidden" id="id_ntbr_owncloud_{$config_id|intval}" name="id_ntbr_owncloud_{$config_id|intval}" value="{$owncloud_id|intval}"
                        data-origin="{$owncloud_id|intval}" data-default="{$owncloud_default.id_ntbr_owncloud|intval}"
                    />
                    <label for="owncloud_name_{$config_id|intval}">{l s='Account name' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="owncloud_name_{$config_id|intval}" id="owncloud_name_{$config_id|intval}" value="{$owncloud_name|escape:'html':'UTF-8'}"
                            data-origin="{$owncloud_name|escape:'html':'UTF-8'}" data-default="" class="name_account"
                            placeholder="{l s='Fill in a name for this new account' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='Enabled' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" name="active_owncloud_{$config_id|intval}" id="active_owncloud_on_{$config_id|intval}" value="1"
                            {if $owncloud_active}checked="checked"{/if} data-origin="{$owncloud_active|intval}" data-default="{$owncloud_default.active|intval}"
                        />
                        <label class="t" for="active_owncloud_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="active_owncloud_{$config_id|intval}" id="active_owncloud_off_{$config_id|intval}" value="0"
                            {if !$owncloud_active}checked="checked"{/if} data-origin="{$owncloud_active|intval}" data-default="{$owncloud_default.active|intval}"
                        />
                        <label class="t" for="active_owncloud_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p>
                    <label for="nb_keep_backup_owncloud_{$config_id|intval}">
                        {l s='Backup to keep. 0 to never delete old backups' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="nb_keep_backup_owncloud_{$config_id|intval}" id="nb_keep_backup_owncloud_{$config_id|intval}" value="{$owncloud_nb_backup|intval}"
                            data-origin="{$owncloud_nb_backup|intval}" data-default="{$owncloud_default.config_nb_backup|intval}"
                            title="{l s='Delete old backups. 0 to never delete old backups' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label for="owncloud_user_{$config_id|intval}">{l s='User:' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="owncloud_user_{$config_id|intval}" id="owncloud_user_{$config_id|intval}" value="{$owncloud_login|escape:'html':'UTF-8'}"
                            data-origin="{$owncloud_login|escape:'html':'UTF-8'}" data-default="{$owncloud_default.login|escape:'html':'UTF-8'}"
                        />
                    </span>
                </p>
                <p>
                    <label for="owncloud_pass_{$config_id|intval}">{l s='Password:' mod='ntbackupandrestore'}</label>
                    <input type="password" class="decoy"/>
                    <span>
                        <input
                            type="password" name="owncloud_pass_{$config_id|intval}" id="owncloud_pass_{$config_id|intval}" value="{$owncloud_pass|escape:'html':'UTF-8'}"
                            autocomplete="new-password" data-origin="{$owncloud_pass|escape:'html':'UTF-8'}" data-default=""
                        />
                    </span>
                </p>
                <p>
                    <label for="owncloud_server_{$config_id|intval}">{l s='Server:' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="owncloud_server_{$config_id|intval}" id="owncloud_server_{$config_id|intval}" value="{$owncloud_server|escape:'html':'UTF-8'}"
                            data-origin="{$owncloud_server|escape:'html':'UTF-8'}" data-default="{$owncloud_default.server|escape:'html':'UTF-8'}"
                        />
                    </span>
                </p>
                <p>
                    <label for="owncloud_dir_{$config_id|intval}">
                        {l s='Directory (Do not use space):' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="owncloud_dir_{$config_id|intval}" id="owncloud_dir_{$config_id|intval}" placeholder="{l s='Ex: backups' mod='ntbackupandrestore'}"
                            value="{$owncloud_directory|escape:'html':'UTF-8'}" data-origin="{$owncloud_directory|escape:'html':'UTF-8'}"
                            data-default="{$owncloud_default.directory|escape:'html':'UTF-8'}"
                        />
                    </span>
                </p>
                <p>
                    <button type="button" name="get_files_owncloud_{$config_id|intval}" id="get_files_owncloud_{$config_id|intval}" class="btn btn-default get_files_owncloud display_2nt">
                        <i class="fas fa-list"></i> {l s='List %1$s files' sprintf=$ntbr_owncloud_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </button>
                </p>
                <p class="file_block" id="owncloud_files_{$config_id|intval}"></p>
            </div>
        </div>
        <div class="panel-footer">
            <button type="button" class="btn btn-default save_owncloud" id="save_owncloud_{$config_id|intval}" name="save_owncloud_{$config_id|intval}">
                <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
            </button>
            <button type="button" class="btn btn-default check_owncloud {if !$owncloud_id}hide{/if}" id="check_owncloud_{$config_id|intval}" name="check_owncloud_{$config_id|intval}">
                <i class="fas fa-sync-alt process_icon"></i> {l s='Check connection' mod='ntbackupandrestore'}
            </button>
            <button type="button" class="btn btn-default delete_owncloud" id="delete_owncloud_{$config_id|intval}" name="delete_owncloud_{$config_id|intval}">
                <i class="fas fa-trash-alt process_icon"></i> {l s='Delete' mod='ntbackupandrestore'}
            </button>
        </div>
    </div>
</div>