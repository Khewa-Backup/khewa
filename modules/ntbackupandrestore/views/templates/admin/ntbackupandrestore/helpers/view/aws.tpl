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
        <i class="icon_send_away icon_send_aws"></i>&nbsp;
        <span>
            {l s='Send the backup on a %1$s account.' sprintf=$ntbr_s3_name|escape:'html':'UTF-8' mod='ntbackupandrestore'} (AWS, MinIO, Wasabi, Scaleway, Backblaze...)
        </span>
    </div>
    <div class="open_config_send_away_account">
        {if !$fct_crypt_exists}
            <div class="fct_crypt_error error alert alert-danger">
                <p>
                    {l s='%1$s cannot work with your current configuration. Please check the following requirements:' sprintf=$ntbr_s3_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                </p>
                <ul>
                    <li>
                        {l s='PHP openssl is not loaded. Please enable it in your hosting management to use %1$s.' sprintf=$ntbr_s3_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </li>
                </ul>
            </div>
            <br/>
        {/if}

        <p {if !$fct_crypt_exists || $light}class="deactivate"{/if}>
            <button type="button" id="send_aws_{$config_id|intval}" name="send_aws_{$config_id|intval}"
                class="btn btn-default send_aws {if $config.nb_aws_active_accounts > 0}enable{else}{if $config.nb_aws_accounts > 0}disable{/if}{/if}"
            >
                <i class="fas fa-cog"></i> {l s='Accounts configuration' mod='ntbackupandrestore'}
            </button>
        </p>
    </div>
    <div id="config_aws_accounts_{$config_id|intval}" class="panel config_send_away_account config_aws_accounts">
        <div class="panel-heading">
            <i class="fas fa-cog"></i>&nbsp;{l s='Send the backup on a %1$s account.' sprintf=$ntbr_s3_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
        </div>
        <input type="hidden" class="nb_account" name="nb_aws_account" value="{$aws_default.nb_account|intval}"/>
        <div>
            <p class="account_list" id="aws_tabs_{$config_id|intval}">
                <label>{l s='Account' mod='ntbackupandrestore'}</label>
                {assign var="active" value=1}
                {foreach $config.aws_accounts as $aws_account}
                    <button
                        type="button" id="aws_account_{$config_id|intval}_{$aws_account.id_ntbr_aws|intval}" value="{$aws_account.id_ntbr_aws|intval}"
                        class="btn btn-default choose_aws_account {if $active == 1}active{else}inactive{/if} {if $aws_account.active == 1}enable{else}disable{/if}"
                    >
                        {$aws_account.name|escape:'html':'UTF-8'}
                    </button>
                    {assign var="active" value=0}
                {/foreach}
                <button
                    type="button" id="aws_account_{$config_id|intval}_0" value="0" title="{l s='Add a new account (display an empty form)' mod='ntbackupandrestore'}"
                    class="btn btn-default choose_aws_account {if $active == 1}active{else}inactive{/if}"
                >
                    <i class="fas fa-plus"></i>
                </button>
            </p>
            <div class="aws_account" id="aws_account_{$config_id|intval}">
                {if isset($config.aws_accounts.0)}
                    {assign var="aws_id" value=$config.aws_accounts.0.id_ntbr_aws|intval}
                    {assign var="aws_name" value=$config.aws_accounts.0.name|escape:'html':'UTF-8'}
                    {assign var="aws_active" value=$config.aws_accounts.0.active|intval}
                    {assign var="aws_accept_unvalid_ssl" value=$config.aws_accounts.0.accept_unvalid_ssl|intval}
                    {assign var="aws_nb_backup" value=$config.aws_accounts.0.config_nb_backup|intval}
                    {assign var="aws_access_key_id" value=$fake_mdp|escape:'html':'UTF-8'}
                    {assign var="aws_secret_access_key" value=$fake_mdp|escape:'html':'UTF-8'}
                    {assign var="aws_region" value=$config.aws_accounts.0.region|escape:'html':'UTF-8'}
                    {assign var="aws_bucket" value=$config.aws_accounts.0.bucket|escape:'html':'UTF-8'}
                    {assign var="aws_host" value=$config.aws_accounts.0.host|escape:'html':'UTF-8'}
                    {assign var="aws_storage_class" value=$config.aws_accounts.0.storage_class|escape:'html':'UTF-8'}
                    {assign var="aws_directory_path" value=$config.aws_accounts.0.directory_path|escape:'html':'UTF-8'}
                    {assign var="aws_directory_key" value=$config.aws_accounts.0.directory_key|escape:'html':'UTF-8'}
                    {assign var="aws_type_s3" value=$config.aws_accounts.0.type_s3|intval}
                {else}
                    {assign var="aws_id" value=$aws_default.id_ntbr_aws|intval}
                    {assign var="aws_name" value=""}
                    {assign var="aws_active" value=$aws_default.active|intval}
                    {assign var="aws_accept_unvalid_ssl" value=$aws_default.accept_unvalid_ssl|intval}
                    {assign var="aws_nb_backup" value=$aws_default.config_nb_backup|intval}
                    {assign var="aws_access_key_id" value=""}
                    {assign var="aws_secret_access_key" value=""}
                    {assign var="aws_region" value=$aws_default.region|escape:'html':'UTF-8'}
                    {assign var="aws_bucket" value=$aws_default.bucket|escape:'html':'UTF-8'}
                    {assign var="aws_host" value=$aws_default.host|escape:'html':'UTF-8'}
                    {assign var="aws_storage_class" value=$aws_default.storage_class|escape:'html':'UTF-8'}
                    {assign var="aws_directory_path" value=$aws_default.directory_path|escape:'html':'UTF-8'}
                    {assign var="aws_directory_key" value=$aws_default.directory_key|escape:'html':'UTF-8'}
                    {assign var="aws_type_s3" value=$aws_default.type_s3|intval}
                {/if}

                <p>
                    <input
                        type="hidden" id="id_ntbr_aws_{$config_id|intval}" name="id_ntbr_aws_{$config_id|intval}"
                        value="{$aws_id|intval}" data-origin="{$aws_id|intval}" data-default="{$aws_default.id_ntbr_aws|intval}"
                    />
                    <label for="aws_name_{$config_id|intval}">{l s='Account name' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="aws_name_{$config_id|intval}" id="aws_name_{$config_id|intval}" value="{$aws_name|escape:'html':'UTF-8'}" class="name_account"
                            data-origin="{$aws_name|escape:'html':'UTF-8'}" data-default="" placeholder="{l s='Fill in a name for this new account' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='Type' mod='ntbackupandrestore'}</label>
                    <select name="aws_type_s3_{$config_id|intval}" id="aws_type_s3_{$config_id|intval}"
                            data-origin="{$aws_type_s3|intval}" data-default="{$aws_default.type_s3|intval}">
                        <option value="{$s3_type_aws|intval}" {if $aws_type_s3 == $s3_type_aws}selected="selected"{/if}>AWS</option>
                        <option value="{$s3_type_minio|intval}" {if $aws_type_s3 == $s3_type_minio}selected="selected"{/if}>MinIO</option>
                        <option value="{$s3_type_wasabi|intval}" {if $aws_type_s3 == $s3_type_wasabi}selected="selected"{/if}>Wasabi</option>
                        <option value="{$s3_type_scaleway|intval}" {if $aws_type_s3 == $s3_type_scaleway}selected="selected"{/if}>Scaleway</option>
                        <option value="{$s3_type_backblaze|intval}" {if $aws_type_s3 == $s3_type_backblaze}selected="selected"{/if}>Backblaze</option>
                        <option value="{$s3_type_vultr|intval}" {if $aws_type_s3 == $s3_type_vultr}selected="selected"{/if}>Vultr</option>
                        <option value="{$s3_type_other|intval}" {if $aws_type_s3 == $s3_type_other}selected="selected"{/if}>{l s='Other' mod='ntbackupandrestore'}</option>
                    </select>
                </p>
                <p>
                    <label>{l s='Enabled' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" name="active_aws_{$config_id|intval}" id="active_aws_on_{$config_id|intval}" {if $aws_active}checked="checked"{/if} value="1"
                            data-origin="{$aws_active|intval}" data-default="{$aws_default.active|intval}"
                        />
                        <label class="t" for="active_aws_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="active_aws_{$config_id|intval}" id="active_aws_off_{$config_id|intval}" {if !$aws_active}checked="checked"{/if} value="0"
                            data-origin="{$aws_active|intval}" data-default="{$aws_default.active|intval}"
                        />
                        <label class="t" for="active_aws_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p>
                    <label for="nb_keep_backup_aws_{$config_id|intval}">
                        {l s='Backup to keep. 0 to never delete old backups' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="text" name="nb_keep_backup_aws_{$config_id|intval}" id="nb_keep_backup_aws_{$config_id|intval}"
                            value="{$aws_nb_backup|intval}" data-origin="{$aws_nb_backup|intval}" data-default="{$aws_default.config_nb_backup|intval}"
                            title="{l s='Delete old backups. 0 to never delete old backups' mod='ntbackupandrestore'}"
                        />
                    </span>
                </p>
                <p>
                    <label for="aws_access_key_id_{$config_id|intval}">
                        {l s='Access key ID' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="password" name="aws_access_key_id_{$config_id|intval}" id="aws_access_key_id_{$config_id|intval}" value="{$aws_access_key_id|escape:'html':'UTF-8'}"
                            data-origin="{$aws_access_key_id|escape:'html':'UTF-8'}" data-default=""
                        />
                    </span>
                </p>
                <p>
                    <label for="aws_secret_access_key_{$config_id|intval}">
                        {l s='Secret access key' mod='ntbackupandrestore'}
                    </label>
                    <span>
                        <input
                            type="password" name="aws_secret_access_key_{$config_id|intval}" id="aws_secret_access_key_{$config_id|intval}"
                            value="{$aws_secret_access_key|escape:'html':'UTF-8'}" data-origin="{$aws_secret_access_key|escape:'html':'UTF-8'}" data-default=""
                        />
                    </span>
                </p>
                <p>
                    <label for="aws_region_{$config_id|intval}">{l s='Region' mod='ntbackupandrestore'}</label>
                    <i class="far fa-question-circle label-tooltip" data-toggle="tooltip" data-placement="left" data-html="true"
                        title="
                            {l s='Region examples for Amazon' mod='ntbackupandrestore'}
                            <table>
                                <tr>
                                    <th style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>{l s='Region Name' mod='ntbackupandrestore'}</th>
                                    <th style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>{l s='Region' mod='ntbackupandrestore'}</th>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>US East (Ohio)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>us-east-2</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>US East (N. Virginia)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>us-east-1</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>US West (N. California)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>us-west-1</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>US West (Oregon)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>us-west-2</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Asia Pacific (Hong Kong)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>ap-east-1</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Asia Pacific (Mumbai)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>ap-south-1</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Asia Pacific (Seoul)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>ap-northeast-2</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Asia Pacific (Singapore)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>ap-southeast-1</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Asia Pacific (Sydney)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>ap-southeast-2</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Asia Pacific (Tokyo)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>ap-northeast-1</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Canada (Central)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>ca-central-1</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>China (Beijing)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>cn-north-1</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>China (Ningxia)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>cn-northwest-1</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>EU (Frankfurt)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>eu-central-1</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>EU (Ireland)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>eu-west-1</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>EU (London)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>eu-west-2</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>EU (Paris)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>eu-west-3</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>EU (Stockholm)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>eu-north-1</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>South America (São Paulo)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>sa-east-1</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>AWS GovCloud (US-East)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>us-gov-east-1</td>
                                </tr>
                                <tr>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>AWS GovCloud (US)</td>
                                    <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>us-gov-west-1</td>
                                </tr>
                            </table>
                        "
                    ></i>
                    <span>
                        <input
                            type="text" name="aws_region_{$config_id|intval}" id="aws_region_{$config_id|intval}" value="{$aws_region|escape:'html':'UTF-8'}"
                            data-origin="{$aws_region|escape:'html':'UTF-8'}" data-default="{$aws_default.region|escape:'html':'UTF-8'}"
                        />
                    </span>
                </p>
                <p>
                    <label for="aws_host_{$config_id|intval}">{l s='Host' mod='ntbackupandrestore'}</label>
                    <i class="far fa-question-circle label-tooltip host_help_wasabi {if $aws_type_s3 != $s3_type_wasabi}hide{/if}" data-toggle="tooltip" data-placement="left" data-html="true"
                        title="
                            {l s='Host examples for Wasabi' mod='ntbackupandrestore'}
                                <table>
                                    <tr>
                                        <th style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>{l s='Region Name' mod='ntbackupandrestore'}</th>
                                        <th style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>{l s='Host' mod='ntbackupandrestore'}</th>
                                    </tr>
                                    <tr>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>US East 1 (N. Virginia)</td>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>s3.wasabisys.com</td>
                                    </tr>
                                    <tr>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Wasabi US East 2 (N. Virginia)</td>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>s3.us-east-2.wasabisys.com</td>
                                    </tr>
                                    <tr>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Wasabi US Central 1 (Texas)</td>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>s3.us-central-1.wasabisys.com</td>
                                    </tr>
                                    <tr>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Wasabi US West 1 (Oregon)</td>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>s3.us-west-1.wasabisys.com</td>
                                    </tr>
                                    <tr>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Wasabi CA Central 1 (Toronto)</td>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>s3.ca-central-1.wasabisys.com</td>
                                    </tr>
                                    <tr>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Wasabi EU Central 1 (Amsterdam)</td>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>s3.eu-central-1.wasabisys.com</td>
                                    </tr>
                                    <tr>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Wasabi EU Central 2 (Frankfurt)</td>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>s3.eu-central-2.wasabisys.com</td>
                                    </tr>
                                    <tr>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Wasabi EU West 1 (London)</td>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>s3.eu-west-1.wasabisys.com</td>
                                    </tr>
                                    <tr>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Wasabi EU West 2 (Paris)</td>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>s3.eu-west-2.wasabisys.com</td>
                                    </tr>
                                    <tr>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Wasabi AP Northeast 1 (Tokyo)</td>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>s3.ap-northeast-1.wasabisys.com</td>
                                    </tr>
                                    <tr>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Wasabi AP Northeast 2 (Osaka)</td>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>s3.ap-northeast-2.wasabisys.com</td>
                                    </tr>
                                    <tr>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Wasabi AP Southeast 1 (Singapore)</td>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>s3.ap-southeast-1.wasabisys.com</td>
                                    </tr>
                                    <tr>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>Wasabi AP Southeast 2 (Sydney)</td>
                                        <td style='border: 1px solid #fff; padding: 2px 5px; text-align: left;'>s3.ap-southeast-2.wasabisys.com</td>
                                    </tr>
                                </table>
                            "
                        ></i>
                    <span>
                        <input
                            type="text" name="aws_host_{$config_id|intval}" id="aws_host_{$config_id|intval}" value="{$aws_host|escape:'html':'UTF-8'}"
                            data-origin="{$aws_host|escape:'html':'UTF-8'}" data-default="{$aws_default.host|escape:'html':'UTF-8'}"
                            {if $aws_type_s3 == $s3_type_aws}readonly="readonly"{/if}
                        />
                    </span>
                </p>
                <p>
                    <label>{l s='Authorize not valid SSL certificat' mod='ntbackupandrestore'}</label>
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input
                            type="radio" name="accept_unvalid_ssl_aws_{$config_id|intval}" id="accept_unvalid_ssl_aws_on_{$config_id|intval}" {if $aws_accept_unvalid_ssl}checked="checked"{/if} value="1"
                            data-origin="{$aws_accept_unvalid_ssl|intval}" data-default="{$aws_default.accept_unvalid_ssl|intval}"
                        />
                        <label class="t" for="accept_unvalid_ssl_aws_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="accept_unvalid_ssl_aws_{$config_id|intval}" id="accept_unvalid_ssl_aws_off_{$config_id|intval}" {if !$aws_accept_unvalid_ssl}checked="checked"{/if} value="0"
                            data-origin="{$aws_accept_unvalid_ssl|intval}" data-default="{$aws_default.accept_unvalid_ssl|intval}"
                        />
                        <label class="t" for="accept_unvalid_ssl_aws_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
                <p>
                    <label for="aws_bucket_{$config_id|intval}">{l s='Bucket' mod='ntbackupandrestore'}</label>
                    <span>
                        <input
                            type="text" name="aws_bucket_{$config_id|intval}" id="aws_bucket_{$config_id|intval}" value="{$aws_bucket|escape:'html':'UTF-8'}"
                            data-origin="{$aws_bucket|escape:'html':'UTF-8'}" data-default="{$aws_default.bucket|escape:'html':'UTF-8'}"
                        />
                    </span>
                </p>
                <p>
                    <label for="aws_storage_class_{$config_id|intval}">{l s='Storage class' mod='ntbackupandrestore'}</label>
                    <a href="https://docs.aws.amazon.com/en_en/AmazonS3/latest/dev/storage-class-intro.html" target="_blank"><i class="far fa-question-circle"></i></a>
                    <select name="aws_storage_class_{$config_id|intval}" id="aws_storage_class_{$config_id|intval}"
                            data-origin="{$aws_storage_class|escape:'html':'UTF-8'}" data-default="{$aws_default.storage_class|escape:'html':'UTF-8'}">
                        <option value="STANDARD" {if $aws_storage_class == 'STANDARD'}selected="selected"{/if}>
                            STANDARD
                        </option>
                        <option value="REDUCED_REDUNDANCY" {if $aws_storage_class == 'REDUCED_REDUNDANCY'}selected="selected"{/if}>
                            REDUCED_REDUNDANCY
                        </option>
                        <option value="STANDARD_IA" {if $aws_storage_class == 'STANDARD_IA'}selected="selected"{/if}>
                            STANDARD_IA
                        </option>
                        <option value="ONEZONE_IA" {if $aws_storage_class == 'ONEZONE_IA'}selected="selected"{/if}>
                            ONEZONE_IA
                        </option>
                        <option value="INTELLIGENT_TIERING" {if $aws_storage_class == 'INTELLIGENT_TIERING'}selected="selected"{/if}>
                            INTELLIGENT_TIERING
                        </option>
                        <option value="GLACIER" {if $aws_storage_class == 'GLACIER'}selected="selected"{/if}>
                            GLACIER
                        </option>
                        <option value="DEEP_ARCHIVE" {if $aws_storage_class == 'DEEP_ARCHIVE'}selected="selected"{/if}>
                            DEEP_ARCHIVE
                        </option>
                    </select>
                </p>
                <div class="{if !$aws_id}hide{/if} directory_block">
                    <p>
                        <label for="aws_directory_path_{$config_id|intval}">
                            {l s='Directory' mod='ntbackupandrestore'}
                        </label>
                        <span>
                            <input
                                type="text" readonly="readonly" name="aws_directory_path_{$config_id|intval}" id="aws_directory_path_{$config_id|intval}"
                                value="{$aws_directory_path|escape:'html':'UTF-8'}"
                                data-origin="{$aws_directory_path|escape:'html':'UTF-8'}" data-default="{$aws_default.directory_path|escape:'html':'UTF-8'}"
                            />
                        </span>
                    </p>
                    <p>
                        <span>
                            <button type="button" id="display_aws_tree_{$config_id|intval}" name="display_aws_tree_{$config_id|intval}" class="btn btn-default display_aws_tree">
                                <i class="fas fa-sitemap"></i> {l s='Display list of directories' mod='ntbackupandrestore'}
                            </button>
                            <input
                                type="hidden" name="aws_directory_key_{$config_id|intval}" id="aws_directory_key_{$config_id|intval}" value="{$aws_directory_key|escape:'html':'UTF-8'}"
                                data-origin="{$aws_directory_key|escape:'html':'UTF-8'}" data-default="{$aws_default.directory_key|escape:'html':'UTF-8'}"
                            />
                        </span>
                    </p>
                    <p id="aws_tree_{$config_id|intval}" class="tree_block"></p>
                </div>
                <p>
                    <button type="button" name="get_files_aws_{$config_id|intval}" id="get_files_aws_{$config_id|intval}" class="btn btn-default get_files_aws display_2nt">
                        <i class="fas fa-list"></i> {l s='List %1$s files' sprintf=$ntbr_s3_name|escape:'html':'UTF-8' mod='ntbackupandrestore'}
                    </button>
                </p>
                <p class="file_block" id="aws_files_{$config_id|intval}"></p>
            </div>
        </div>
        <div class="panel-footer">
            <button type="button" id="save_aws_{$config_id|intval}" name="save_aws_{$config_id|intval}" class="btn btn-default save_aws">
                <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
            </button>
            <button type="button" id="check_aws_{$config_id|intval}" name="check_aws_{$config_id|intval}" class="btn btn-default check_aws {if !$aws_id}hide{/if}">
                <i class="fas fa-sync-alt process_icon"></i> {l s='Check connection' mod='ntbackupandrestore'}
            </button>
            <button type="button" id="delete_aws_{$config_id|intval}" name="delete_aws_{$config_id|intval}" class="btn btn-default delete_aws">
                <i class="fas fa-trash-alt process_icon"></i> {l s='Delete' mod='ntbackupandrestore'}
            </button>
        </div>
    </div>
</div>