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
        <i class="icon_send_away icon_send_shadow_drive"></i>&nbsp;
        <span>
            {l s='Send the backup on a %1$s account.' sprintf=$ntbr_shadow_drive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </span>
    </div>
    <div class="open_config_send_away_account">
        {if !$fct_crypt_exists}
            <div class="fct_crypt_error error alert alert-danger">
                <p>
                    {l s='%1$s cannot work with your current configuration. Please check the following requirements:' sprintf=$ntbr_shadow_drive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                </p>
                <ul>
                    <li>
                        {l s='PHP openssl is not loaded. Please enable it in your hosting management to use %1$s.' sprintf=$ntbr_shadow_drive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </li>
                </ul>
            </div>
            <br/>
        {/if}

        <p {if !$fct_crypt_exists || $light}class="deactivate"{/if}>
            <button type="button" id="send_shadow_drive_{$config_id|intval}" name="send_shadow_drive_{$config_id|intval}"
                class="btn btn-default send_shadow_drive {if $config.nb_shadow_drive_active_accounts > 0}enable{else}{if $config.nb_shadow_drive_accounts > 0}disable{/if}{/if}"
            >
                <i class="fas fa-cog"></i> {l s='Accounts configuration' mod='ntbackupandrestore'}
            </button>
        </p>
    </div>
    <div id="config_shadow_drive_accounts_{$config_id|intval}" class="panel config_send_away_account config_shadow_drive_accounts">
        <div class="panel-heading">
            <i class="fas fa-cog"></i>&nbsp;{l s='Send the backup on a %1$s account.' sprintf=$ntbr_shadow_drive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </div>
        <input type="hidden" class="nb_account" name="nb_shadow_drive_account" value="{$shadow_drive_default.nb_account|intval}"/>
        <div>
            <p class="account_list" id="shadow_drive_tabs_{$config_id|intval}">
                <label>{l s='Account' mod='ntbackupandrestore'}</label>
                {assign var="active" value=1}
                {foreach $config.shadow_drive_accounts as $shadow_drive_account}
                    <button
                        type="button" value="{$shadow_drive_account.id_ntbr_shadow_drive|intval}" id="shadow_drive_account_{$config_id|intval}_{$shadow_drive_account.id_ntbr_shadow_drive|intval}"
                        class="btn btn-default choose_shadow_drive_account {if $active == 1}active{else}inactive{/if} {if $shadow_drive_account.active == 1}enable{else}disable{/if}"
                    >
                        {$shadow_drive_account.name|escape:'html':'UTF-8'}
                    </button>
                    {assign var="active" value=0}
                {/foreach}
                <button
                    type="button" id="shadow_drive_account_{$config_id|intval}_0" value="0" title="{l s='Add a new account (display an empty form)' mod='ntbackupandrestore'}"
                    class="btn btn-default choose_shadow_drive_account {if $active == 1}active{else}inactive{/if}"
                >
                    <i class="fas fa-plus"></i>
                </button>
            </p>
            <div class="shadow_drive_account" id="shadow_drive_account_{$config_id|intval}">
                {if isset($config.shadow_drive_accounts.0)}
                    {assign var="shadow_drive_id" value=$config.shadow_drive_accounts.0.id_ntbr_shadow_drive|intval}
                    {assign var="shadow_drive_name" value=$config.shadow_drive_accounts.0.name|escape:'html':'UTF-8'}
                    {assign var="shadow_drive_active" value=$config.shadow_drive_accounts.0.active|intval}
                    {assign var="shadow_drive_nb_backup" value=$config.shadow_drive_accounts.0.config_nb_backup|intval}
                    {assign var="shadow_drive_login" value=$config.shadow_drive_accounts.0.login|escape:'html':'UTF-8'}
                    {assign var="shadow_drive_pass" value=$fake_mdp|escape:'html':'UTF-8'}
                    {assign var="shadow_drive_server" value=$config.shadow_drive_accounts.0.server|escape:'html':'UTF-8'}
                    {assign var="shadow_drive_directory" value=$config.shadow_drive_accounts.0.directory|escape:'html':'UTF-8'}
                {else}
                    {assign var="shadow_drive_id" value=$shadow_drive_default.id_ntbr_shadow_drive|intval}
                    {assign var="shadow_drive_name" value=""}
                    {assign var="shadow_drive_active" value=$shadow_drive_default.active|intval}
                    {assign var="shadow_drive_nb_backup" value=$shadow_drive_default.config_nb_backup|intval}
                    {assign var="shadow_drive_login" value=$shadow_drive_default.login|escape:'html':'UTF-8'}
                    {assign var="shadow_drive_pass" value=""}
                    {assign var="shadow_drive_server" value=$shadow_drive_default.server|escape:'html':'UTF-8'}
                    {assign var="shadow_drive_directory" value=$shadow_drive_default.directory|escape:'html':'UTF-8'}
                {/if}

                <p>
                    <input
                        type="hidden" id="id_ntbr_shadow_drive_{$config_id|intval}" name="id_ntbr_shadow_drive_{$config_id|intval}"
                        value="{$shadow_drive_id|intval}" data-origin="{$shadow_drive_id|intval}" data-default="{$shadow_drive_default.id_ntbr_shadow_drive|intval}"
                    />
                    <label for="shadow_drive_name_{$config_id|intval}">{l s='Account name' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="shadow_drive_name_{$config_id|intval}" id="shadow_drive_name_{$config_id|intval}"
                            value="{$shadow_drive_name|escape:'html':'UTF-8'}" class="name_account" data-origin="{$shadow_drive_name|escape:'html':'UTF-8'}" data-default=""
                            placeholder="{l s='Fill in a name for this new account' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='Enabled' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" name="active_shadow_drive_{$config_id|intval}" id="active_shadow_drive_on_{$config_id|intval}" value="1"
                            {if $shadow_drive_active}checked="checked"{/if} data-origin="{$shadow_drive_active|intval}" data-default="{$shadow_drive_default.active|intval}"
                        />
                        <label class="t" for="active_shadow_drive_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="active_shadow_drive_{$config_id|intval}" id="active_shadow_drive_off_{$config_id|intval}" value="0"
                            {if !$shadow_drive_active}checked="checked"{/if} data-origin="{$shadow_drive_active|intval}" data-default="{$shadow_drive_default.active|intval}"
                        />
                        <label class="t" for="active_shadow_drive_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p>
                    <label for="nb_keep_backup_shadow_drive_{$config_id|intval}">
                        {l s='Backup to keep. 0 to never delete old backups' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="nb_keep_backup_shadow_drive_{$config_id|intval}" id="nb_keep_backup_shadow_drive_{$config_id|intval}"
                            value="{$shadow_drive_nb_backup|intval}" data-origin="{$shadow_drive_nb_backup|intval}" data-default="{$shadow_drive_default.config_nb_backup|intval}"
                            title="{l s='Delete old backups. 0 to never delete old backups' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label for="shadow_drive_user_{$config_id|intval}">{l s='User:' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="shadow_drive_user_{$config_id|intval}" id="shadow_drive_user_{$config_id|intval}" value="{$shadow_drive_login|escape:'html':'UTF-8'}"
                            data-origin="{$shadow_drive_login|escape:'html':'UTF-8'}" data-default="{$shadow_drive_default.login|escape:'html':'UTF-8'}"
                        />
                    </span>
                </p>
                <p>
                    <label for="shadow_drive_pass_{$config_id|intval}">{l s='Password:' mod='ntbackupandrestore'}</label>
                    <input type="password" class="decoy"/>
                    <span>
                        <input
                            type="password" name="shadow_drive_pass_{$config_id|intval}" id="shadow_drive_pass_{$config_id|intval}" value="{$shadow_drive_pass|escape:'html':'UTF-8'}"
                            autocomplete="new-password" data-origin="{$shadow_drive_pass|escape:'html':'UTF-8'}" data-default=""
                        />
                    </span>
                </p>
                <p>
                    <label for="shadow_drive_server_{$config_id|intval}">{l s='Server:' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="shadow_drive_server_{$config_id|intval}" id="shadow_drive_server_{$config_id|intval}" value="{$shadow_drive_server|escape:'html':'UTF-8'}"
                            data-origin="{$shadow_drive_server|escape:'html':'UTF-8'}" data-default="{$shadow_drive_default.server|escape:'html':'UTF-8'}"
                        />
                    </span>
                </p>
                <p>
                    <label for="shadow_drive_dir_{$config_id|intval}">
                        {l s='Directory (Do not use space):' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="shadow_drive_dir_{$config_id|intval}" id="shadow_drive_dir_{$config_id|intval}" placeholder="{l s='Ex: backups' mod='ntbackupandrestore'}"
                            value="{$shadow_drive_directory|escape:'html':'UTF-8'}" data-origin="{$shadow_drive_directory|escape:'html':'UTF-8'}"
                            data-default="{$shadow_drive_default.directory|escape:'html':'UTF-8'}"
                        />
                    </span>
                </p>
                <p>
                    <button type="button" name="get_files_shadow_drive_{$config_id|intval}" id="get_files_shadow_drive_{$config_id|intval}" class="btn btn-default get_files_shadow_drive display_2nt">
                        <i class="fas fa-list"></i> {l s='List %1$s files' sprintf=$ntbr_shadow_drive_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </button>
                </p>
                <p class="file_block" id="shadow_drive_files_{$config_id|intval}"></p>
            </div>
        </div>
        <div class="panel-footer">
            <button type="button" class="btn btn-default save_shadow_drive" id="save_shadow_drive_{$config_id|intval}" name="save_shadow_drive_{$config_id|intval}">
                <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
            </button>
            <button type="button" class="btn btn-default check_shadow_drive {if !$shadow_drive_id}hide{/if}" id="check_shadow_drive_{$config_id|intval}" name="check_shadow_drive_{$config_id|intval}">
                <i class="fas fa-sync-alt process_icon"></i> {l s='Check connection' mod='ntbackupandrestore'}
            </button>
            <button type="button" class="btn btn-default delete_shadow_drive" id="delete_shadow_drive_{$config_id|intval}" name="delete_shadow_drive_{$config_id|intval}">
                <i class="fas fa-trash-alt process_icon"></i> {l s='Delete' mod='ntbackupandrestore'}
            </button>
        </div>
    </div>
</div>