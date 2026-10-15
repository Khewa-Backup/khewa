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
        <i class="icon_send_away icon_send_box"></i>&nbsp;
        <span>
            {l s='Send the backup on a %1$s account.' sprintf=$ntbr_box_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </span>
    </div>
    <div class="open_config_send_away_account">
        {if !$fct_crypt_exists}
            <div class="fct_crypt_error error alert alert-danger">
                <p>
                    {l s='%1$s cannot work with your current configuration. Please check the following requirements:' sprintf=$ntbr_box_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                </p>
                <ul>
                    <li>
                        {l s='PHP openssl is not loaded. Please enable it in your hosting management to use %1$s.' sprintf=$ntbr_box_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </li>
                </ul>
            </div>
            <br/>
        {/if}

        <p {if !$fct_crypt_exists || $light}class="deactivate"{/if}>
            <button type="button" id="send_box_{$config_id|intval}" name="send_box_{$config_id|intval}"
                class="btn btn-default send_box {if $config.nb_box_active_accounts > 0}enable{else}{if $config.nb_box_accounts > 0}disable{/if}{/if}"
            >
                <i class="fas fa-cog"></i> {l s='Accounts configuration' mod='ntbackupandrestore'}
            </button>
        </p>
    </div>
    <div id="config_box_accounts_{$config_id|intval}" class="panel config_send_away_account config_box_accounts">
        <div class="panel-heading">
            <i class="fas fa-cog"></i> &nbsp;{l s='Send the backup on a %1$s account.' sprintf=$ntbr_box_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </div>
        <input type="hidden" class="nb_account" name="nb_box_account" value="{$box_default.nb_account|intval}"/>
        <div>
            <p class="account_list" id="box_tabs_{$config_id|intval}">
                <label>{l s='Account' mod='ntbackupandrestore'}</label>
                {assign var="active" value=1}
                {foreach $config.box_accounts as $box_account}
                    <button
                        type="button" value="{$box_account.id_ntbr_box|intval}"
                        id="box_account_{$config_id|intval}_{$box_account.id_ntbr_box|intval}"
                        class="btn btn-default choose_box_account {if $active == 1}active{else}inactive{/if} {if $box_account.active == 1}enable{else}disable{/if}"
                    >
                        {$box_account.name|escape:'html':'UTF-8'}
                    </button>
                    {assign var="active" value=0}
                {/foreach}
                <button
                    type="button" id="box_account_{$config_id|intval}_0" value="0" title="{l s='Add a new account (display an empty form)' mod='ntbackupandrestore'}"
                    class="btn btn-default choose_box_account {if $active == 1}active{else}inactive{/if}"
                >
                    <i class="fas fa-plus"></i>
                </button>
            </p>
            <div class="box_account" id="box_account_{$config_id|intval}">
                {if isset($config.box_accounts.0)}
                    {assign var="box_id" value=$config.box_accounts.0.id_ntbr_box|intval}
                    {assign var="box_name" value=$config.box_accounts.0.name|escape:'html':'UTF-8'}
                    {assign var="box_active" value=$config.box_accounts.0.active|intval}
                    {assign var="box_nb_backup" value=$config.box_accounts.0.config_nb_backup|intval}
                    {assign var="box_code" value=$fake_mdp|escape:'html':'UTF-8'}
                    {assign var="box_directory_path" value=$config.box_accounts.0.directory_path|escape:'html':'UTF-8'}
                    {assign var="box_directory_key" value=$config.box_accounts.0.directory_key|escape:'html':'UTF-8'}
                {else}
                    {assign var="box_id" value=$box_default.id_ntbr_box|intval}
                    {assign var="box_name" value=""}
                    {assign var="box_active" value=$box_default.active|intval}
                    {assign var="box_nb_backup" value=$box_default.config_nb_backup|intval}
                    {assign var="box_code" value=""}
                    {assign var="box_directory_path" value=$box_default.directory_path|escape:'html':'UTF-8'}
                    {assign var="box_directory_key" value=$box_default.directory_key|escape:'html':'UTF-8'}
                {/if}

                <p>
                    <input type="hidden" id="id_ntbr_box_{$config_id|intval}" name="id_ntbr_box_{$config_id|intval}" value="{$box_id|intval}"
                        data-origin="{$box_id|intval}" data-default="{$box_default.id_ntbr_box|intval}"
                    />
                    <label for="box_name_{$config_id|intval}">
                        {l s='Account name' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="box_name_{$config_id|intval}" id="box_name_{$config_id|intval}" class="name_account"
                            value="{$box_name|escape:'html':'UTF-8'}" data-origin="{$box_name|escape:'html':'UTF-8'}" data-default=""
                            placeholder="{l s='Fill in a name for this new account' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='Enabled' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" name="active_box_{$config_id|intval}" id="active_box_on_{$config_id|intval}" value="1"
                            {if $box_active}checked="checked"{/if} data-origin="{$box_active|intval}" data-default="{$box_default.active|intval}"
                        />
                        <label class="t" for="active_box_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="active_box_{$config_id|intval}" id="active_box_off_{$config_id|intval}" value="0"
                            {if !$box_active}checked="checked"{/if} data-origin="{$box_active|intval}" data-default="{$box_default.active|intval}"
                        />
                        <label class="t" for="active_box_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p>
                    <label for="nb_keep_backup_box_{$config_id|intval}">
                        {l s='Backup to keep. 0 to never delete old backups' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="nb_keep_backup_box_{$config_id|intval}" id="nb_keep_backup_box_{$config_id|intval}" class="nb_keep_backup_box"
                            value="{$box_nb_backup|intval}" data-origin="{$box_nb_backup|intval}" data-default="{$box_default.config_nb_backup|intval}"
                            title="{l s='Delete old backups. 0 to never delete old backups' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='1.' mod='ntbackupandrestore'}</label>
                    <button
                        type="button" id="authentification_box_{$config_id|intval}" name="authentification_box_{$config_id|intval}" class="btn btn-default"
                        onclick="window.open('{$box_authorizeUrl|escape:'html':'UTF-8'}');"
                    >
                        {l s='Authentication' mod='ntbackupandrestore'}
                    </button>
                </p>
                <p>
                    <label>
                        {l s='2. Click "Allow" (you might have to log in first)' mod='ntbackupandrestore'}
                    </label>
                </p>
                <p>
                    <label for="box_code_{$config_id|intval}">
                        {l s='3. Copy the authorization code' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="password" name="box_code_{$config_id|intval}" id="box_code_{$config_id|intval}"
                            value="{$box_code|escape:'html':'UTF-8'}" data-origin="{$box_code|escape:'html':'UTF-8'}" data-default=""
                        />
                    </span>
                </p>
                <div class="{if !$box_id}hide{/if} directory_block">
                    <p>
                        <label for="box_dir_{$config_id|intval}">{l s='Directory' mod='ntbackupandrestore'}</label>
                        <span>
                            <input
                                type="text" id="box_dir_path_{$config_id|intval}" readonly="readonly" name="box_dir_path_{$config_id|intval}"
                                value="{$box_directory_path|escape:'html':'UTF-8'}"
                                data-origin="{$box_directory_path|escape:'html':'UTF-8'}" data-default="{$box_default.directory_path|escape:'html':'UTF-8'}"
                            />
                        </span>
                    </p>
                    <p>
                        <span>
                            <button
                                type="button" class="btn btn-default display_box_tree" id="display_box_tree_{$config_id|intval}"
                                name="display_box_tree_{$config_id|intval}"
                            >
                                <i class="fas fa-sitemap"></i> {l s='Display list of directories' mod='ntbackupandrestore'}
                            </button>
                            <input
                                type="hidden" id="box_dir_{$config_id|intval}" name="box_dir_{$config_id|intval}" value="{$box_directory_key|escape:'html':'UTF-8'}"
                                data-origin="{$box_directory_key|escape:'html':'UTF-8'}" data-default="{$box_default.directory_key|escape:'html':'UTF-8'}"
                            />
                        </span>
                    </p>
                    <p class="tree_block" id="box_tree_{$config_id|intval}"></p>
                </div>
                <p>
                    <button type="button" name="get_files_box_{$config_id|intval}" id="get_files_box_{$config_id|intval}" class="btn btn-default get_files_box display_2nt">
                        <i class="fas fa-list"></i> {l s='List %1$s files' sprintf=$ntbr_box_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </button>
                </p>
                <p class="file_block" id="box_files_{$config_id|intval}"></p>
            </div>
        </div>
        <div class="panel-footer">
            <button type="button" class="btn btn-default save_box" id="save_box_{$config_id|intval}" name="save_box_{$config_id|intval}">
                <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
            </button>
            <button
                type="button" id="check_box_{$config_id|intval}" name="check_box_{$config_id|intval}"
                class="btn btn-default check_box {if !$box_id}hide{/if}"
            >
                <i class="fas fa-sync-alt process_icon"></i> {l s='Check connection' mod='ntbackupandrestore'}
            </button>
            <button type="button" class="btn btn-default delete_box" id="delete_box_{$config_id|intval}" name="delete_box_{$config_id|intval}">
                <i class="fas fa-trash-alt process_icon"></i> {l s='Delete' mod='ntbackupandrestore'}
            </button>
        </div>
    </div>
</div>