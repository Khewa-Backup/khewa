{*
* 2013-2023 2N Technologies
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
* @copyright 2013-2023 2N Technologies
* @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<div class="panel">
    <div class="panel-heading send_away_header">
        <i class="icon_send_away icon_send_hubic"></i>&nbsp;
        <span>
            {l s='Send the backup on a %1$s account.' sprintf=$ntbr_hubic_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </span>
    </div>
    <div class="open_config_send_away_account">
        {if !$fct_crypt_exists}
            <div class="fct_crypt_error error alert alert-danger">
                <p>
                    {l s='%1$s cannot work with your current configuration. Please check the following requirements:' sprintf=$ntbr_hubic_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                </p>
                <ul>
                    <li>
                        {l s='PHP openssl is loaded. Please enable it in your hosting management to use %1$s.' sprintf=$ntbr_hubic_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </li>
                </ul>
            </div>
            <br/>
        {/if}

        <p {if !$fct_crypt_exists || $light}class="deactivate"{/if}>
            <button type="button" id="send_hubic_{$config_id|intval}" name="send_hubic_{$config_id|intval}"
                class="btn btn-default send_hubic {if $config.nb_hubic_active_accounts > 0}enable{else}{if $config.nb_hubic_accounts > 0}disable{/if}{/if}"
            >
                <i class="fas fa-cog"></i> {l s='Accounts configuration' mod='ntbackupandrestore'}
            </button>
        </p>
    </div>
    <div id="config_hubic_accounts_{$config_id|intval}" class="panel config_send_away_account config_hubic_accounts">
        <div class="panel-heading">
            <i class="fas fa-cog"></i>&nbsp;{l s='Send the backup on a %1$s account.' sprintf=$ntbr_hubic_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </div>
        <input type="hidden" class="nb_account" name="nb_hubic_account" value="{$hubic_default.nb_account|intval}"/>
        <div>
            <p class="account_list" id="hubic_tabs_{$config_id|intval}">
                <label>{l s='Account' mod='ntbackupandrestore'}</label>
                {assign var="active" value=1}
                {foreach $config.hubic_accounts as $hubic_account}
                    <button
                        type="button" id="hubic_account_{$config_id|intval}_{$hubic_account.id_ntbr_hubic|intval}" value="{$hubic_account.id_ntbr_hubic|intval}"
                        class="btn btn-default choose_hubic_account {if $active == 1}active{else}inactive{/if} {if $hubic_account.active == 1}enable{else}disable{/if}"
                    >
                        {$hubic_account.name|escape:'html':'UTF-8'}
                    </button>
                    {assign var="active" value=0}
                {/foreach}
                <button
                    type="button" id="hubic_account_{$config_id|intval}_0" value="0" title="{l s='Add a new account (display an empty form)' mod='ntbackupandrestore'}"
                    class="btn btn-default choose_hubic_account {if $active == 1}active{else}inactive{/if}"
                >
                    <i class="fas fa-plus"></i>
                </button>
            </p>
            <div class="hubic_account" id="hubic_account_{$config_id|intval}">
                {if isset($config.hubic_accounts.0)}
                    {assign var="hubic_id" value=$config.hubic_accounts.0.id_ntbr_hubic|intval}
                    {assign var="hubic_name" value=$config.hubic_accounts.0.name|escape:'html':'UTF-8'}
                    {assign var="hubic_active" value=$config.hubic_accounts.0.active|intval}
                    {assign var="hubic_nb_backup" value=$config.hubic_accounts.0.config_nb_backup|intval}
                    {assign var="hubic_code" value=$fake_mdp|escape:'html':'UTF-8'}
                    {assign var="hubic_directory" value=$config.hubic_accounts.0.directory|escape:'html':'UTF-8'}
                {else}
                    {assign var="hubic_id" value=$hubic_default.id_ntbr_hubic|intval}
                    {assign var="hubic_name" value=""}
                    {assign var="hubic_active" value=$hubic_default.active|intval}
                    {assign var="hubic_nb_backup" value=$hubic_default.config_nb_backup|intval}
                    {assign var="hubic_code" value=""}
                    {assign var="hubic_directory" value=$hubic_default.directory|escape:'html':'UTF-8'}
                {/if}

                <p>
                    <input
                        type="hidden" id="id_ntbr_hubic_{$config_id|intval}" name="id_ntbr_hubic_{$config_id|intval}"
                        value="{$hubic_id|intval}" data-origin="{$hubic_id|intval}" data-default="{$hubic_default.id_ntbr_hubic|intval}"
                    />
                    <label for="hubic_name_{$config_id|intval}">{l s='Account name' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="hubic_name_{$config_id|intval}" id="hubic_name_{$config_id|intval}"
                            value="{$hubic_name|escape:'html':'UTF-8'}" class="name_account" data-origin="{$hubic_name|escape:'html':'UTF-8'}" data-default=""
                            placeholder="{l s='Fill in a name for this new account' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='Enabled' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" name="active_hubic_{$config_id|intval}" id="active_hubic_on_{$config_id|intval}" value="1"
                            {if $hubic_active}checked="checked"{/if} data-origin="{$hubic_active|intval}" data-default="{$hubic_default.active|intval}"
                        />
                        <label class="t" for="active_hubic_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="active_hubic_{$config_id|intval}" id="active_hubic_off_{$config_id|intval}" value="0"
                            {if !$hubic_active}checked="checked"{/if} data-origin="{$hubic_active|intval}" data-default="{$hubic_default.active|intval}"
                        />
                        <label class="t" for="active_hubic_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p>
                    <label for="nb_keep_backup_hubic_{$config_id|intval}">
                        {l s='Backup to keep. 0 to never delete old backups' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="nb_keep_backup_hubic_{$config_id|intval}" id="nb_keep_backup_hubic_{$config_id|intval}"
                            value="{$hubic_nb_backup|intval}" data-origin="{$hubic_nb_backup|intval}" data-default="{$hubic_default.config_nb_backup|intval}"
                            title="{l s='Delete old backups. 0 to never delete old backups' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='1.' mod='ntbackupandrestore'}</label>
                    <button
                        type="button" name="authentification_hubic_{$config_id|intval}" id="authentification_hubic_{$config_id|intval}"
                        class="btn btn-default" onclick="window.open('{$hubic_authorizeUrl|escape:'html':'UTF-8'}');"
                    >
                        {l s='Authentication' mod='ntbackupandrestore'}
                    </button>
                </p>
                <p>
                    <label>{l s='2. Click "Allow" (you might have to log in first)' mod='ntbackupandrestore'}</label>
                </p>
                <p>
                    <label for="hubic_code_{$config_id|intval}">
                        {l s='3. Copy the authorization code' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="password" name="hubic_code_{$config_id|intval}" id="hubic_code_{$config_id|intval}" value="{$hubic_code|escape:'html':'UTF-8'}"
                            data-origin="{$hubic_code|escape:'html':'UTF-8'}" data-default=""
                        />
                    </span>
                </p>
                <p>
                    <label for="hubic_dir_{$config_id|intval}">
                        {l s='Directory' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="hubic_dir_{$config_id|intval}" placeholder="{l s='Ex: backups' mod='ntbackupandrestore'}" value="{$hubic_directory|escape:'html':'UTF-8'}"
                            data-origin="{$hubic_directory|escape:'html':'UTF-8'}" data-default="{$hubic_default.directory|escape:'html':'UTF-8'}" id="hubic_dir_{$config_id|intval}"
                        />
                    </span>
                </p>
            </div>
        </div>
        <div class="panel-footer">
            <button type="button" id="save_hubic_{$config_id|intval}" name="save_hubic_{$config_id|intval}" class="btn btn-default save_hubic">
                <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
            </button>
            <button type="button" id="check_hubic_{$config_id|intval}" name="check_hubic_{$config_id|intval}" class="btn btn-default check_hubic {if !$hubic_id}hide{/if}">
                <i class="fas fa-sync-alt process_icon"></i> {l s='Check connection' mod='ntbackupandrestore'}
            </button>
            <button type="button" id="delete_hubic_{$config_id|intval}" name="delete_hubic_{$config_id|intval}" class="btn btn-default delete_hubic">
                <i class="fas fa-trash-alt process_icon"></i> {l s='Delete' mod='ntbackupandrestore'}
            </button>
        </div>
    </div>
</div>