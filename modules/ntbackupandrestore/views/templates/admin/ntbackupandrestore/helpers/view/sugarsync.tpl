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

<div class="panel hidden hide">
    <div class="panel-heading send_away_header">
        <i class="icon_send_away icon_send_sugarsync"></i>&nbsp;
        <span>
            {l s='Send the backup on a %1$s account.' sprintf=$ntbr_sugarsync_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </span>
    </div>
    <div class="open_config_send_away_account">
        {if !$fct_crypt_exists}
            <div class="fct_crypt_error error alert alert-danger">
                <p>
                    {l s='%1$s cannot work with your current configuration. Please check the following requirements:' sprintf=$ntbr_sugarsync_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                </p>
                <ul>
                    <li>
                        {l s='PHP openssl is not loaded. Please enable it in your hosting management to use %1$s.' sprintf=$ntbr_sugarsync_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </li>
                </ul>
            </div>
            <br/>
        {/if}

        <p {if !$fct_crypt_exists || $light}class="deactivate"{/if}>
            <button type="button" id="send_sugarsync_{$config_id|intval}" name="send_sugarsync_{$config_id|intval}"
                class="btn btn-default send_sugarsync {if $config.nb_sugarsync_active_accounts > 0}enable{else}{if $config.nb_sugarsync_accounts > 0}disable{/if}{/if}"
            >
                <i class="fas fa-cog"></i> {l s='Accounts configuration' mod='ntbackupandrestore'}
            </button>
        </p>
    </div>
    <div id="config_sugarsync_accounts_{$config_id|intval}" class="panel config_send_away_account config_sugarsync_accounts">
        <div class="panel-heading">
            <i class="fas fa-cog"></i>&nbsp;{l s='Send the backup on a %1$s account.' sprintf=$ntbr_sugarsync_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </div>
        <input type="hidden" class="nb_account" name="nb_sugarsync_account" value="{$sugarsync_default.nb_account|intval}"/>
        <div>
            <p class="account_list" id="sugarsync_tabs_{$config_id|intval}">
                <label>{l s='Account' mod='ntbackupandrestore'}</label>
                {assign var="active" value=1}
                {foreach $config.sugarsync_accounts as $sugarsync_account}
                    <button
                        type="button" value="{$sugarsync_account.id_ntbr_sugarsync|intval}" id="sugarsync_account_{$config_id|intval}_{$sugarsync_account.id_ntbr_sugarsync|intval}"
                        class="btn btn-default choose_sugarsync_account {if $active == 1}active{else}inactive{/if} {if $sugarsync_account.active == 1}enable{else}disable{/if}"
                    >
                        {$sugarsync_account.name|escape:'html':'UTF-8'}
                    </button>
                    {assign var="active" value=0}
                {/foreach}
                <button
                    type="button" id="sugarsync_account_{$config_id|intval}_0" value="0" title="{l s='Add a new account (display an empty form)' mod='ntbackupandrestore'}"
                    class="btn btn-default choose_sugarsync_account {if $active == 1}active{else}inactive{/if}"
                >
                    <i class="fas fa-plus"></i>
                </button>
            </p>
            <div class="sugarsync_account" id="sugarsync_account_{$config_id|intval}">
                {if isset($config.sugarsync_accounts.0)}
                    {assign var="sugarsync_id" value=$config.sugarsync_accounts.0.id_ntbr_sugarsync|intval}
                    {assign var="sugarsync_name" value=$config.sugarsync_accounts.0.name|escape:'html':'UTF-8'}
                    {assign var="sugarsync_active" value=$config.sugarsync_accounts.0.active|intval}
                    {assign var="sugarsync_nb_backup" value=$config.sugarsync_accounts.0.config_nb_backup|intval}
                    {assign var="sugarsync_login" value=$config.sugarsync_accounts.0.login|escape:'html':'UTF-8'}
                    {assign var="sugarsync_password" value=$fake_mdp|escape:'html':'UTF-8'}
                    {assign var="sugarsync_directory_path" value=$config.sugarsync_accounts.0.directory_path|escape:'html':'UTF-8'}
                    {assign var="sugarsync_directory_key" value=$config.sugarsync_accounts.0.directory_key|escape:'html':'UTF-8'}
                {else}
                    {assign var="sugarsync_id" value=$sugarsync_default.id_ntbr_sugarsync|intval}
                    {assign var="sugarsync_name" value=""}
                    {assign var="sugarsync_active" value=$sugarsync_default.active|intval}
                    {assign var="sugarsync_nb_backup" value=$sugarsync_default.config_nb_backup|intval}
                    {assign var="sugarsync_login" value=""}
                    {assign var="sugarsync_password" value=""}
                    {assign var="sugarsync_directory_path" value=$sugarsync_default.directory_path|escape:'html':'UTF-8'}
                    {assign var="sugarsync_directory_key" value=$sugarsync_default.directory_key|escape:'html':'UTF-8'}
                {/if}

                <p>
                    <input
                        type="hidden" id="id_ntbr_sugarsync_{$config_id|intval}" name="id_ntbr_sugarsync_{$config_id|intval}" value="{$sugarsync_id|intval}"
                        data-origin="{$sugarsync_id|intval}" data-default="{$sugarsync_default.id_ntbr_sugarsync|intval}"
                    />
                    <label for="sugarsync_name_{$config_id|intval}">
                        {l s='Account name' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="sugarsync_name_{$config_id|intval}" id="sugarsync_name_{$config_id|intval}" value="{$sugarsync_name|escape:'html':'UTF-8'}" class="name_account"
                            data-origin="{$sugarsync_name|escape:'html':'UTF-8'}" data-default="" placeholder="{l s='Fill in a name for this new account' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='Enabled' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" id="active_sugarsync_on_{$config_id|intval}" name="active_sugarsync_{$config_id|intval}" value="1"
                            {if $sugarsync_active}checked="checked"{/if} data-origin="{$sugarsync_active|intval}" data-default="{$sugarsync_default.active|intval}"
                        />
                        <label class="t" for="active_sugarsync_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" id="active_sugarsync_off_{$config_id|intval}" name="active_sugarsync_{$config_id|intval}" value="0"
                            {if !$sugarsync_active}checked="checked"{/if} data-origin="{$sugarsync_active|intval}" data-default="{$sugarsync_default.active|intval}"
                        />
                        <label class="t" for="active_sugarsync_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p>
                    <label for="nb_keep_backup_sugarsync_{$config_id|intval}">
                        {l s='Backup to keep. 0 to never delete old backups' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="nb_keep_backup_sugarsync_{$config_id|intval}" id="nb_keep_backup_sugarsync_{$config_id|intval}" value="{$sugarsync_nb_backup|intval}"
                            data-origin="{$sugarsync_nb_backup|intval}" data-default="{$sugarsync_default.config_nb_backup|intval}"
                            title="{l s='Delete old backups. 0 to never delete old backups' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label for="sugarsync_login_{$config_id|intval}">
                        {l s='Login' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="sugarsync_login_{$config_id|intval}" id="sugarsync_login_{$config_id|intval}"
                            value="{$sugarsync_login|escape:'html':'UTF-8'}" data-origin="{$sugarsync_login|escape:'html':'UTF-8'}" data-default=""
                        />
                    </span>
                </p>
                <p>
                    <label for="sugarsync_password_{$config_id|intval}">
                        {l s='Password' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="password" name="sugarsync_password_{$config_id|intval}" id="sugarsync_password_{$config_id|intval}"
                            value="{$sugarsync_password|escape:'html':'UTF-8'}" data-origin="{$sugarsync_password|escape:'html':'UTF-8'}" data-default=""
                        />
                    </span>
                </p>
                <div class="{if !$sugarsync_id}hide{/if} directory_block">
                    <p>
                        <label for="sugarsync_dir_path_{$config_id|intval}">
                            {l s='Directory' mod='ntbackupandrestore'}
                        </label>
                        <span>
                            <input
                                type="text" name="sugarsync_dir_path_{$config_id|intval}" readonly="readonly" id="sugarsync_dir_path_{$config_id|intval}" class="sugarsync_dir_path"
                                value="{$sugarsync_directory_path|escape:'html':'UTF-8'}"
                                data-origin="{$sugarsync_directory_path|escape:'html':'UTF-8'}" data-default="{$sugarsync_default.directory_path|escape:'html':'UTF-8'}"
                            />
                        </span>
                    </p>
                    <p>
                        <span>
                            <button
                                type="button" name="display_sugarsync_tree_{$config_id|intval}" id="display_sugarsync_tree_{$config_id|intval}" class="btn btn-default display_sugarsync_tree"
                            >
                                <i class="fas fa-sitemap"></i> {l s='Display list of directories' mod='ntbackupandrestore'}
                            </button>
                            <input
                                type="hidden" name="sugarsync_dir_{$config_id|intval}" id="sugarsync_dir_{$config_id|intval}" value="{$sugarsync_directory_key|escape:'html':'UTF-8'}"
                                data-origin="{$sugarsync_directory_key|escape:'html':'UTF-8'}" data-default="{$sugarsync_default.directory_key|escape:'html':'UTF-8'}" class="sugarsync_dir"
                            />
                        </span>
                    </p>
                    <p class="tree_block" id="sugarsync_tree_{$config_id|intval}"></p>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="button" id="save_sugarsync_{$config_id|intval}" name="save_sugarsync_{$config_id|intval}" class="btn btn-default save_sugarsync">
                <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
            </button>
            <button type="button" id="check_sugarsync_{$config_id|intval}" name="check_sugarsync_{$config_id|intval}" class="btn btn-default check_sugarsync {if !$sugarsync_id}hide{/if}">
                <i class="fas fa-sync-alt process_icon"></i> {l s='Check connection' mod='ntbackupandrestore'}
            </button>
            <button type="button" id="delete_sugarsync_{$config_id|intval}" name="delete_sugarsync_{$config_id|intval}" class="btn btn-default delete_sugarsync">
                <i class="fas fa-trash-alt process_icon"></i> {l s='Delete' mod='ntbackupandrestore'}
            </button>
        </div>
    </div>
</div>