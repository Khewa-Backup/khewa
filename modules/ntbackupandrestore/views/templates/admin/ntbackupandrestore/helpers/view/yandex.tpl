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
        <i class="icon_send_away icon_send_yandex"></i>&nbsp;
        <span>
            {l s='Send the backup on a %1$s account.' sprintf=$ntbr_yandex_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </span>
    </div>
    <div class="open_config_send_away_account">
        {if !$fct_crypt_exists}
            <div class="fct_crypt_error error alert alert-danger">
                <p>
                    {l s='%1$s cannot work with your current configuration. Please check the following requirements:' sprintf=$ntbr_yandex_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                </p>
                <ul>
                    <li>
                        {l s='PHP openssl is not loaded. Please enable it in your hosting management to use %1$s.' sprintf=$ntbr_yandex_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </li>
                </ul>
            </div>
            <br/>
        {/if}

        <p {if !$fct_crypt_exists || $light}class="deactivate"{/if}>
            <button type="button" id="send_yandex_{$config_id|intval}" name="send_yandex_{$config_id|intval}"
                class="btn btn-default send_yandex {if $config.nb_yandex_active_accounts > 0}enable{else}{if $config.nb_yandex_accounts > 0}disable{/if}{/if}"
            >
                <i class="fas fa-cog"></i> {l s='Accounts configuration' mod='ntbackupandrestore'}
            </button>
        </p>
    </div>
    <div class="panel config_send_away_account config_yandex_accounts" >
        <div class="panel-heading">
            <i class="fas fa-cog"></i>
            &nbsp;{l s='Send the backup on a %1$s account.' sprintf=$ntbr_yandex_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </div>
        <input type="hidden" class="nb_account" name="nb_yandex_account" value="{$yandex_default.nb_account|intval}"/>
        <div>
            <p class="account_list" id="yandex_tabs_{$config_id|intval}">
                <label>{l s='Account' mod='ntbackupandrestore'}</label>
                {assign var="active" value=1}
                {foreach $config.yandex_accounts as $yandex_account}
                    <button
                        type="button" value="{$yandex_account.id_ntbr_yandex|intval}" id="yandex_account_{$config_id|intval}_{$yandex_account.id_ntbr_yandex|intval}"
                        class="btn btn-default choose_yandex_account {if $active == 1}active{else}inactive{/if} {if $yandex_account.active == 1}enable{else}disable{/if}"
                    >
                        {$yandex_account.name|escape:'html':'UTF-8'}
                    </button>
                    {assign var="active" value=0}
                {/foreach}
                <button
                    type="button" id="yandex_account_{$config_id|intval}_0" value="0" title="{l s='Add a new account (display an empty form)' mod='ntbackupandrestore'}"
                    class="btn btn-default choose_yandex_account {if $active == 1}active{else}inactive{/if}"
                >
                    <i class="fas fa-plus"></i>
                </button>
            </p>
            <div class="yandex_account" id="yandex_account_{$config_id|intval}">
                {if isset($config.yandex_accounts.0)}
                    {assign var="yandex_id" value=$config.yandex_accounts.0.id_ntbr_yandex|intval}
                    {assign var="yandex_name" value=$config.yandex_accounts.0.name|escape:'html':'UTF-8'}
                    {assign var="yandex_active" value=$config.yandex_accounts.0.active|intval}
                    {assign var="yandex_nb_backup" value=$config.yandex_accounts.0.config_nb_backup|intval}
                    {assign var="yandex_code" value=$fake_mdp|escape:'html':'UTF-8'}
                    {assign var="yandex_directory" value=$config.yandex_accounts.0.directory|escape:'html':'UTF-8'}
                {else}
                    {assign var="yandex_id" value=$yandex_default.id_ntbr_yandex|intval}
                    {assign var="yandex_name" value=""}
                    {assign var="yandex_active" value=$yandex_default.active|intval}
                    {assign var="yandex_nb_backup" value=$yandex_default.config_nb_backup|intval}
                    {assign var="yandex_code" value=""}
                    {assign var="yandex_directory" value=$yandex_default.directory|escape:'html':'UTF-8'}
                {/if}

                <p>
                    <input
                        type="hidden" id="id_ntbr_yandex_{$config_id|intval}" name="id_ntbr_yandex_{$config_id|intval}" value="{$yandex_id|intval}"
                        data-origin="{$yandex_id|intval}" data-default="{$yandex_default.id_ntbr_yandex|intval}"
                    />
                    <label for="yandex_name_{$config_id|intval}">{l s='Account name' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="yandex_name_{$config_id|intval}" id="yandex_name_{$config_id|intval}" value="{$yandex_name|escape:'html':'UTF-8'}" class="name_account"
                            data-origin="{$yandex_name|escape:'html':'UTF-8'}" data-default=""
                            placeholder="{l s='Fill in a name for this new account' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='Enabled' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" name="active_yandex_{$config_id|intval}" id="active_yandex_on_{$config_id|intval}" value="1"
                            {if $yandex_active}checked="checked"{/if} data-origin="{$yandex_active|intval}" data-default="{$yandex_default.active|intval}"
                        />
                        <label class="t" for="active_yandex_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="active_yandex_{$config_id|intval}" id="active_yandex_off_{$config_id|intval}" value="0"
                            {if !$yandex_active}checked="checked"{/if} data-origin="{$yandex_active|intval}" data-default="{$yandex_default.active|intval}"
                        />
                        <label class="t" for="active_yandex_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p>
                    <label for="nb_keep_backup_yandex_{$config_id|intval}">
                        {l s='Backup to keep. 0 to never delete old backups' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="nb_keep_backup_yandex_{$config_id|intval}" id="nb_keep_backup_yandex_{$config_id|intval}" class="nb_keep_backup_yandex"
                            value="{$yandex_nb_backup|intval}" data-origin="{$yandex_nb_backup|intval}" data-default="{$yandex_default.config_nb_backup|intval}"
                            title="{l s='Delete old backups. 0 to never delete old backups' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='1.' mod='ntbackupandrestore'}</label>
                    <button
                        type="button" name="authentification_yandex_{$config_id|intval}" id="authentification_yandex_{$config_id|intval}" class="btn btn-default"
                        onclick="window.open('{$yandex_authorizeUrl|escape:'html':'UTF-8'}');"
                    >
                        <i class="fab fa-yandex-international"></i> - {l s='Authentication' mod='ntbackupandrestore'}
                    </button>
                </p>
                <p>
                    <label>{l s='2. Click "Allow" (you might have to log in first)' mod='ntbackupandrestore'}</label>
                </p>
                <p>
                    <label for="yandex_code_{$config_id|intval}">
                        {l s='3. Copy the authorization code' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="password" name="yandex_code_{$config_id|intval}" id="yandex_code_{$config_id|intval}" value="{$yandex_code|escape:'html':'UTF-8'}"
                            data-origin="{$yandex_code|escape:'html':'UTF-8'}" data-default=""
                        />
                    </span>
                </p>
                <p>
                    <label for="yandex_dir_{$config_id|intval}">
                        {l s='Directory' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="yandex_dir_{$config_id|intval}" id="yandex_dir_{$config_id|intval}" placeholder="{l s='Ex: /backups' mod='ntbackupandrestore'}"
                            value="{$yandex_directory|escape:'html':'UTF-8'}"
                            data-origin="{$yandex_directory|escape:'html':'UTF-8'}" data-default="{$yandex_default.directory|escape:'html':'UTF-8'}"
                        />
                    </span>
                </p>
                <p>
                    <button type="button" name="get_files_yandex_{$config_id|intval}" id="get_files_yandex_{$config_id|intval}" class="btn btn-default get_files_yandex display_2nt">
                        <i class="fas fa-list"></i> {l s='List %1$s files' sprintf=$ntbr_yandex_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </button>
                </p>
                <p class="file_block" id="yandex_files_{$config_id|intval}"></p>
            </div>
        </div>
        <div class="panel-footer">
            <button type="button" class="btn btn-default save_yandex" id="save_yandex_{$config_id|intval}" name="save_yandex_{$config_id|intval}">
                <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
            </button>
            <button type="button" class="btn btn-default check_yandex {if !$yandex_id}hide{/if}" id="check_yandex_{$config_id|intval}" name="check_yandex_{$config_id|intval}">
                <i class="fas fa-sync-alt process_icon"></i> {l s='Check connection' mod='ntbackupandrestore'}
            </button>
            <button type="button" class="btn btn-default delete_yandex" id="delete_yandex_{$config_id|intval}" name="delete_yandex_{$config_id|intval}">
                <i class="fas fa-trash-alt process_icon"></i> {l s='Delete' mod='ntbackupandrestore'}
            </button>
        </div>
    </div>
</div>