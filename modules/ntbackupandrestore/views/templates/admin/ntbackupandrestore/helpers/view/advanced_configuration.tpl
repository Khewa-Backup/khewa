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

<p>
    <button
        type="button" name="nt_advanced_config_{$config_id|intval}" id="nt_advanced_config_{$config_id|intval}"
        class="btn btn-default nt_advanced_config"
    >
        <i class="fas fa-sliders-h"></i> {l s='Advanced' mod='ntbackupandrestore'}
    </button>
</p>
<div id="nt_advanced_config_diplay_{$config_id|intval}" class="nt_advanced_config_diplay">
    {if $config.is_default}
    <p>
        <label>
            {l s='Enable debug log. Write a file with all messages of the module. Only for debug.' mod='ntbackupandrestore'}
        </label>
        <span class="switch prestashop-switch fixed-width-lg">
            <input type="radio" name="activate_log_{$config_id|intval}" id="activate_log_on_{$config_id|intval}" value="1" {if $config.activate_log}checked="checked"{/if}/>
            <label class="t" for="activate_log_on_{$config_id|intval}">{l s='Yes' mod='ntbackupandrestore'}</label>
            <input type="radio" name="activate_log_{$config_id|intval}" id="activate_log_off_{$config_id|intval}" value="0" {if !$config.activate_log}checked="checked"{/if}/>
            <label class="t" for="activate_log_off_{$config_id|intval}">{l s='No' mod='ntbackupandrestore'}</label>
            <a class="slide-button btn"></a>
        </span>
    </p>
    {/if}
    <p>
        <label for="part_size_{$config_id|intval}">
            {l s='Size max (in MB) for your backup files. 0 if you want to have only one file for your backup, whatever the size' mod='ntbackupandrestore'}
        </label>
        <span>
            <input
                type="text" name="part_size_{$config_id|intval}" id="part_size_{$config_id|intval}" value="{$config.part_size|intval}"
                title="{l s='Cut your backup so each parts has a size inferior to the one set. 0 if you do not want to use this functionality' mod='ntbackupandrestore'}"
            />
        </span>
    </p>
    <p>
        <label for="max_file_to_backup_{$config_id|intval}">
            {l s='Size max (in MB) of the files to add within the backup. 0 if you want to backup all your files, whatever the size' mod='ntbackupandrestore'}
        </label>
        <span>
            <input
                type="text" name="max_file_to_backup_{$config_id|intval}" id="max_file_to_backup_{$config_id|intval}" value="{$config.max_file_to_backup|intval}"
                title="{l s='Ignore files with a size equal or larger than this value. 0 if you do not want to use this functionality' mod='ntbackupandrestore'}"
            />
        </span>
    </p>
    {if $config.type_backup != $backup_type_file}
    <p>
        <label for="dump_max_values_{$config_id|intval}">
            {l s='Max values line per dump line. Lower number means more disk access. Do not touch this option unless 2N support requires it. This has consequences on restoration capabilities.' mod='ntbackupandrestore'}
            ({l s='Default value' mod='ntbackupandrestore'} {$default_dump_max_values|intval})
        </label>
        <span>
            <input
                type="text" name="dump_max_values_{$config_id|intval}" id="dump_max_values_{$config_id|intval}" value="{$config.dump_max_values|intval}"
                title="{l s='Number of line max to insert at once in your database during restoration. If there are too many datas at once, your mysql server may become too stressed and stop the restauration.' mod='ntbackupandrestore'}"
            />
        </span>
    </p>
    {/if}
    {if $config.type_backup != $backup_type_file}
    <p>
        <label for="dump_lines_limit_{$config_id|intval}">
            {l s='Max lines number for each database access during dump. Higher number means higher memory use. Do not touch this option unless 2N support requires it.' mod='ntbackupandrestore'}
            ({l s='Default value' mod='ntbackupandrestore'} {$default_dump_lines_limit|intval})
        </label>
        <span>
            <input
                type="text" name="dump_lines_limit_{$config_id|intval}" id="dump_lines_limit_{$config_id|intval}" value="{$config.dump_lines_limit|intval}"
                title="{l s='Number of line max to select at once in your database during backup. If there are too many datas at once, you could reach the memory limit of your server.' mod='ntbackupandrestore'}"
            />
        </span>
    </p>
    {/if}
    <p>
        <label>
            {l s='Disable intermediate renewal. The backup will be performed without interruption but the server timeout must be large enough.' mod='ntbackupandrestore'}
        </label>
        <span class="switch prestashop-switch fixed-width-lg disable_refresh">
            <input type="radio" class="disable_refresh_on" name="disable_refresh_{$config_id|intval}" id="disable_refresh_on_{$config_id|intval}" value="1" {if $config.disable_refresh}checked="checked"{/if} />
            <label class="t" for="disable_refresh_on_{$config_id|intval}">{l s='Yes' mod='ntbackupandrestore'}</label>
            <input type="radio" name="disable_refresh_{$config_id|intval}" id="disable_refresh_off_{$config_id|intval}" value="0" {if !$config.disable_refresh}checked="checked"{/if}/>
            <label class="t" for="disable_refresh_off_{$config_id|intval}">{l s='No' mod='ntbackupandrestore'}</label>
            <a class="slide-button btn"></a>
        </span>
    </p>
    <p>
        <label for="time_between_refresh_{$config_id|intval}">
            {l s='Duration of intermediate renewal (default %1$d seconds). Must be slightly lower than the server timeout' sprintf=$max_time_before_refresh mod='ntbackupandrestore'}
            {l s='(Currently, your server max execution time is %1$d seconds.)' sprintf=$max_execution_time mod='ntbackupandrestore'}
        </label>
        <span>
            <input type="text" name="time_between_refresh_{$config_id|intval}" id="time_between_refresh_{$config_id|intval}" value="{$config.time_between_refresh|intval}"/>
        </span>
    </p>
    <p>
        <label for="time_pause_between_refresh_{$config_id|intval}">
            {l s='Duration of the pause between two intermediate renewal (default 0 second). Useful for small servers, it saves some resources but progress may be less reactive' mod='ntbackupandrestore'}
        </label>
        <span>
            <input type="text" name="time_pause_between_refresh_{$config_id|intval}" id="time_pause_between_refresh_{$config_id|intval}" value="{$config.time_pause_between_refresh|intval}"/>
        </span>
    </p>
    <p>
        <label for="time_between_progress_refresh_{$config_id|intval}">
            {l s='Duration between progress refresh (default %1$d second). Useful for small servers, it saves some resources but progress may be less reactive.' sprintf=$max_time_before_progress_refresh mod='ntbackupandrestore'}
        </label>
        <span>
            <input
                type="text" name="time_between_progress_refresh_{$config_id|intval}" id="time_between_progress_refresh_{$config_id|intval}"
                value="{$config.time_between_progress_refresh|intval}"
            />
        </span>
    </p>
    <p>
        <label>
            {l s='Attempt to increase server timeout (Currently, your server max execution time is %1$d seconds.)' sprintf=$max_execution_time mod='ntbackupandrestore'}
        </label>
        <span class="switch prestashop-switch fixed-width-lg increase_server_timeout_block">
            <input
                type="radio" name="increase_server_timeout_{$config_id|intval}" id="increase_server_timeout_on_{$config_id|intval}" value="1"
                {if $config.increase_server_timeout}checked="checked"{/if}
            />
            <label class="t" for="increase_server_timeout_on_{$config_id|intval}">
                {l s='Yes' mod='ntbackupandrestore'}
            </label>
            <input
                type="radio" name="increase_server_timeout_{$config_id|intval}" id="increase_server_timeout_off_{$config_id|intval}" value="0"
                class="increase_server_timeout_off" {if !$config.increase_server_timeout}checked="checked"{/if}
            />
            <label class="t" for="increase_server_timeout_off_{$config_id|intval}">
                {l s='No' mod='ntbackupandrestore'}
            </label>
            <a class="slide-button btn"></a>
        </span>
    </p>
    <p>
        <label for="server_timeout_value_{$config_id|intval}">{l s='New timeout limit.' mod='ntbackupandrestore'}</label>
        <span>
            <input type="text" name="server_timeout_value_{$config_id|intval}" id="server_timeout_value_{$config_id|intval}" value="{$config.server_timeout_value|intval}"/>
        </span>
    </p>
    <p>
        <label>
            {l s='Launch downloads in Javascript (Useful if your server interrupts downloads before the end).' mod='ntbackupandrestore'}
        </label>
        <span class="switch prestashop-switch fixed-width-lg js_download_block">
            <input
                type="radio" name="js_download_{$config_id|intval}" id="js_download_on_{$config_id|intval}"
                value="1" {if $config.js_download}checked="checked"{/if}
            />
            <label class="t" for="js_download_on_{$config_id|intval}">
                {l s='Yes' mod='ntbackupandrestore'}
            </label>
            <input
                type="radio" name="js_download_{$config_id|intval}" id="js_download_off_{$config_id|intval}"
                class="js_download_off" value="0" {if !$config.js_download}checked="checked"{/if}
            />
            <label class="t" for="js_download_off_{$config_id|intval}">
                {l s='No' mod='ntbackupandrestore'}
            </label>
            <a class="slide-button btn"></a>
        </span>
    </p>
    <p>
        <label>
            {l s='Do not use curl to get files size (useful if your server limit curl usage).' mod='ntbackupandrestore'}
        </label>
        <span class="switch prestashop-switch fixed-width-lg disable_curl_file_size_block">
            <input
                type="radio" name="disable_curl_file_size_{$config_id|intval}" id="disable_curl_file_size_on_{$config_id|intval}"
                value="1" {if $config.disable_curl_file_size}checked="checked"{/if}
            />
            <label class="t" for="disable_curl_file_size_on_{$config_id|intval}">
                {l s='Yes' mod='ntbackupandrestore'}
            </label>
            <input
                type="radio" name="disable_curl_file_size_{$config_id|intval}" id="disable_curl_file_size_off_{$config_id|intval}"
                class="disable_curl_file_size_off" value="0" {if !$config.disable_curl_file_size}checked="checked"{/if}
            />
            <label class="t" for="disable_curl_file_size_off_{$config_id|intval}">
                {l s='No' mod='ntbackupandrestore'}
            </label>
            <a class="slide-button btn"></a>
        </span>
    </p>
    <p>
        <label>
            {l s='Attempt to increase the memory limit to the maximum usually required (%1$dMB). Currently, the memory limit of your server is' sprintf=$min_memory_limit mod='ntbackupandrestore'} {$memory_limit|intval}{l s='MB' mod='ntbackupandrestore'}
        </label>
        <span class="switch prestashop-switch fixed-width-lg increase_server_memory_block">
            <input
                type="radio" name="increase_server_memory_{$config_id|intval}" id="increase_server_memory_on_{$config_id|intval}"
                value="1" {if $config.increase_server_memory}checked="checked"{/if}
            />
            <label class="t" for="increase_server_memory_on_{$config_id|intval}">
                {l s='Yes' mod='ntbackupandrestore'}
            </label>
            <input
                type="radio" name="increase_server_memory_{$config_id|intval}" id="increase_server_memory_off_{$config_id|intval}"
                class="increase_server_memory_off" value="0" {if !$config.increase_server_memory}checked="checked"{/if}
            />
            <label class="t" for="increase_server_memory_off_{$config_id|intval}">
                {l s='No' mod='ntbackupandrestore'}
            </label>
            <a class="slide-button btn"></a>
        </span>
    </p>
    <p>
        <label for="server_memory_value_{$config_id|intval}">{l s='New memory limit.' mod='ntbackupandrestore'}</label>
        <span>
            <input type="text" name="server_memory_value_{$config_id|intval}" id="server_memory_value_{$config_id|intval}" value="{$config.server_memory_value|intval}"/>
        </span>
    </p>
    {if $config.type_backup != $backup_type_file}
    <p>
        <label>
            {l s='Dump low interest table. For efficiency, the module do not backup some tables (statistics tables) which may be very big and not very useful. If you want to backup them, enable this option. The backup may take much more time and have a bigger size.' mod='ntbackupandrestore'}
            {l s='Tables ignored:' mod='ntbackupandrestore'} {$low_interest_tables|escape:'html':'UTF-8'|truncate:75:" ..."} {*connections, connections_page, connections_source, statssearch, guest...*}
        </label>
        <span class="switch prestashop-switch fixed-width-lg">
            <input
                type="radio" name="dump_low_interest_table_{$config_id|intval}" id="dump_low_interest_table_on_{$config_id|intval}"
                value="1" {if $config.dump_low_interest_tables}checked="checked"{/if}
            />
            <label class="t" for="dump_low_interest_table_on_{$config_id|intval}">
                {l s='Yes' mod='ntbackupandrestore'}
            </label>
            <input
                type="radio" name="dump_low_interest_table_{$config_id|intval}" id="dump_low_interest_table_off_{$config_id|intval}"
                value="0" {if !$config.dump_low_interest_tables}checked="checked"{/if}
            />
            <label class="t" for="dump_low_interest_table_off_{$config_id|intval}">
                {l s='No' mod='ntbackupandrestore'}
            </label>
            <a class="slide-button btn"></a>
        </span>
    </p>
    {/if}
    <p>
        <label>
            {l s='Put your shop in maintenance while creating your backup. Attention, your shop will be unusable for the duration of the backup.' mod='ntbackupandrestore'}
        </label>
        <span class="switch prestashop-switch fixed-width-lg">
            <input type="radio" name="maintenance_{$config_id|intval}" id="maintenance_on_{$config_id|intval}" value="1" {if $config.maintenance}checked="checked"{/if}/>
            <label class="t" for="maintenance_on_{$config_id|intval}">{l s='Yes' mod='ntbackupandrestore'}</label>
            <input type="radio" name="maintenance_{$config_id|intval}" id="maintenance_off_{$config_id|intval}" value="0" {if !$config.maintenance}checked="checked"{/if}/>
            <label class="t" for="maintenance_off_{$config_id|intval}">{l s='No' mod='ntbackupandrestore'}</label>
            <a class="slide-button btn"></a>
        </span>
    </p>
    <p>
        <label for="time_between_backups_{$config_id|intval}">
            {l s='Security duration between backups (default %1$d seconds). Prevents simultaneous launch of backups.' sprintf=$min_time_new_backup mod='ntbackupandrestore'}
        </label>
        <span>
            <input type="text" name="time_between_backups_{$config_id|intval}" id="time_between_backups_{$config_id|intval}" value="{$config.time_between_backups|intval}"/>
        </span>
    </p>
    <p>
        <label>
            {l s='Do not compress backup. Useful for small servers, it saves some resources but backup file may be twice bigger.' mod='ntbackupandrestore'}
        </label>
        <span class="switch prestashop-switch fixed-width-lg {if $config.create_on_distant}deactivate{/if}">
            <input
                type="radio" name="ignore_compression_{$config_id|intval}" id="ignore_compression_on_{$config_id|intval}"
                value="1" {if $config.ignore_compression || $config.create_on_distant}checked="checked"{/if}
            />
            <label class="t" for="ignore_compression_on_{$config_id|intval}">
                {l s='Yes' mod='ntbackupandrestore'}
            </label>
            <input
                type="radio" name="ignore_compression_{$config_id|intval}" id="ignore_compression_off_{$config_id|intval}"
                value="0" {if !$config.ignore_compression && !$config.create_on_distant}checked="checked"{/if}
            />
            <label class="t" for="ignore_compression_off_{$config_id|intval}">
                {l s='No' mod='ntbackupandrestore'}
            </label>
            <a class="slide-button btn"></a>
        </span>
    </p>
    <div {if $light}class="panel"{/if}>
        {if $light}
            <div class="light_version_error alert alert-info hint">
                <p>
                    {l s='These advanced options are only available in the' mod='ntbackupandrestore'}
                    <a href="{$link_full_version|escape:'htmlall':'UTF-8'}">
                        {l s='full version of the module' mod='ntbackupandrestore'}
                    </a>.
                    {l s='Compatibility with XSendFile allows the server to save resources while downloading your backup. Ignoring the products images make a much lighter backup faster. This is particularly useful for developers to test a production version locally. The ability to not compress the backup make a faster but heavier backup.' mod='ntbackupandrestore'}
                </p>
            </div>
            <br/>
        {/if}
        <div {if $light}class="light_version"{/if}>
            <p>
                <label>
                    {l s='Encrypt the backup. Backup files will be encrypted with a secret key.' mod='ntbackupandrestore'}<br/>
                    {if !$sodium_detected}
                        <i>{l s='The PHP Sodium extension that allows encryption is not enabled. Consult your hosting provider for its activation.' mod='ntbackupandrestore'}</i>
                    {/if}
                </label>
                <span class="alert alert-warning warn {if !$sodium_detected || $light}deactivate{/if}">
                    {l s='The encryption key will be randomly generated and shown below after enabling this option.' mod='ntbackupandrestore'}<br/>
                    {l s='Keep it somewhere safely. You will need it to decrypt the file.' mod='ntbackupandrestore'}<br/>
                    <span class="bold">{l s='Without the encryption key, you will not be able to restore your backup !' mod='ntbackupandrestore'}</span><br/>
                    {l s='You can generate a new encryption key by clicking on the "Reset key" button.' mod='ntbackupandrestore'}<br/>
                    {l s='This new secret key will be used for new backups.' mod='ntbackupandrestore'}<br/>
                    {l s='Encrypted backups need the key they were encrypted with in order to restore them.' mod='ntbackupandrestore'}
                </span>
                <span class="switch prestashop-switch fixed-width-lg crypt_backup {if $light}deactivate{/if}">
                    <input
                        type="radio" name="crypt_backup_{$config_id|intval}" id="crypt_backup_on_{$config_id|intval}"
                        value="1" {if $config.crypt_backup}checked="checked"{/if}
                         {if !$sodium_detected}disabled="disabled"{/if}
                    />
                    <label class="t" for="crypt_backup_on_{$config_id|intval}">
                        {l s='Yes' mod='ntbackupandrestore'}
                    </label>
                    <input
                        type="radio" name="crypt_backup_{$config_id|intval}" id="crypt_backup_off_{$config_id|intval}"
                        value="0" {if !$config.crypt_backup}checked="checked"{/if}
                         {if !$sodium_detected}disabled="disabled"{/if}
                    />
                    <label class="t" for="crypt_backup_off_{$config_id|intval}">
                        {l s='No' mod='ntbackupandrestore'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
            </p>
            <div class="crypt_backup_key" id="crypt_backup_key_{$config_id|intval}">
                <label>{l s='Secret key:' mod='ntbackupandrestore'}</label>
                <div class="input-group">
                    <span class="sodium_key">{$config.sodium_key|escape:'html':'UTF-8'}</span>
                    <span class="input-group-addon copy_sodium_key"><i class="fa-regular fa-copy"></i></span>
                </div>
            </div>
            <p {if !$config.crypt_backup || $light || !$sodium_detected}class="hide"{/if}>
                <button type="button" class="btn btn-default crypt_backup_reset_key" id="crypt_backup_reset_key_{$config_id|intval}">{l s='Reset the key' mod='ntbackupandrestore'}</button>
                <button type="button" class="btn btn-default crypt_backup_reveal_key" id="crypt_backup_reveal_key_{$config_id|intval}">{l s='Reveal the secret key' mod='ntbackupandrestore'}</button>
            </p>
            <p>
                <label>
                    {l s='Enable XSendfile. XSendFile enables fast file download with very low use of processor and memory.' mod='ntbackupandrestore'}
                    {if !$xsendfile_detected}
                        <br/>
                        <i>{l s='XSendFile not detected' mod='ntbackupandrestore'}</i>
                    {/if}
                </label>
                <br/>
                <span class="switch prestashop-switch fixed-width-lg {if $light}deactivate{/if}">
                    <input
                        type="radio" name="activate_xsendfile_{$config_id|intval}" id="activate_xsendfile_on_{$config_id|intval}" value="1"
                        {if $config.activate_xsendfile}checked="checked"{/if} {if !$xsendfile_detected}disabled="disabled"{/if}
                    />
                    <label class="t" for="activate_xsendfile_on_{$config_id|intval}">
                        {l s='Yes' mod='ntbackupandrestore'}
                    </label>
                    <input
                        type="radio" name="activate_xsendfile_{$config_id|intval}" id="activate_xsendfile_off_{$config_id|intval}" value="0"
                        {if !$config.activate_xsendfile}checked="checked"{/if} {if !$xsendfile_detected}disabled="disabled"{/if}
                    />
                    <label class="t" for="activate_xsendfile_off_{$config_id|intval}">
                        {l s='No' mod='ntbackupandrestore'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
            </p>
            {if $config.type_backup != $backup_type_base}
                <p>
                    <label for="ignore_product_image_{$config_id|intval}">{l s='Product images backup:' mod='ntbackupandrestore'}</label>
                    <select name="ignore_product_image_{$config_id|intval}" id="ignore_product_image_{$config_id|intval}" class="ignore_product_image">
                        <option value="{$product_img_and_file|intval}" {if $config.ignore_product_image == $product_img_and_file}selected="selected"{/if}>
                            {l s='Do not ignore product images' mod='ntbackupandrestore'}
                        </option>
                        <option value="{$product_img_none|intval}" {if $config.ignore_product_image == $product_img_none}selected="selected"{/if}>
                            {l s='Ignore product images' mod='ntbackupandrestore'}
                        </option>
                        <option value="{$product_img_only|intval}" {if $config.ignore_product_image == $product_img_only && $config.type_backup == $backup_type_file}selected="selected"{/if} {if $config.type_backup != $backup_type_file}disabled{/if}>
                            {l s='Save only product images' mod='ntbackupandrestore'}
                            {if $config.type_backup != $backup_type_file} {l s='(The backup type must be File to only save products images)' mod='ntbackupandrestore'}{/if}
                        </option>
                    </select>
                </p>
                <p>
                    <label>
                        {l s='Only save products source image. Thumbnails will not be saved, you will have to regenerate them after restoring (Menu Design > Image Setting). This may significantly reduce the size of the backup.' mod='ntbackupandrestore'}
                    </label>
                    <span class="switch prestashop-switch fixed-width-lg {if $light}deactivate{/if}">
                        <input
                            type="radio" name="only_origin_img_{$config_id|intval}" id="only_origin_img_on_{$config_id|intval}"
                            value="1" {if $config.only_origin_img}checked="checked"{/if}
                        />
                        <label class="t" for="only_origin_img_on_{$config_id|intval}">
                            {l s='Yes' mod='ntbackupandrestore'}
                        </label>
                        <input
                            type="radio" name="only_origin_img_{$config_id|intval}" id="only_origin_img_off_{$config_id|intval}"
                            value="0" {if !$config.only_origin_img}checked="checked"{/if}
                        />
                        <label class="t" for="only_origin_img_off_{$config_id|intval}">
                            {l s='No' mod='ntbackupandrestore'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </p>
            {/if}
            <p>
                <label>
                    {l s='Delete your local backup file if the backup is sent elsewhere.' mod='ntbackupandrestore'}
                </label>
                <span class="switch prestashop-switch fixed-width-lg {if $light}deactivate{/if}">
                    <input
                        type="radio" name="delete_local_backup_{$config_id|intval}" id="delete_local_backup_on_{$config_id|intval}"
                        value="1" {if $config.delete_local_backup}checked="checked"{/if}
                    />
                    <label class="t" for="delete_local_backup_on_{$config_id|intval}">
                        {l s='Yes' mod='ntbackupandrestore'}
                    </label>
                    <input
                        type="radio" name="delete_local_backup_{$config_id|intval}" id="delete_local_backup_off_{$config_id|intval}"
                        value="0" {if !$config.delete_local_backup}checked="checked"{/if}
                    />
                    <label class="t" for="delete_local_backup_off_{$config_id|intval}">
                        {l s='No' mod='ntbackupandrestore'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
            </p>
            <p>
                <label>
                    {l s='Create your backup file on your distant accounts directly.' mod='ntbackupandrestore'}
                    {l s='Works with' mod='ntbackupandrestore'}
                    {$ntbr_sftp_name|escape:'html':'UTF-8'},
                    {$ntbr_dropbox_name|escape:'html':'UTF-8'},
                    {$ntbr_yandex_name|escape:'html':'UTF-8'},
                    {$ntbr_owncloud_name|escape:'html':'UTF-8'},
                    {$ntbr_webdav_name|escape:'html':'UTF-8'},
                    {$ntbr_googledrive_name|escape:'html':'UTF-8'},
                    {$ntbr_googlecloud_name|escape:'html':'UTF-8'},
                    {$ntbr_onedrive_name|escape:'html':'UTF-8'},
                    {$ntbr_shadow_drive_name|escape:'html':'UTF-8'},
                    {$ntbr_s3_name|escape:'html':'UTF-8'},
                    {$ntbr_pcloud_name|escape:'html':'UTF-8'}.
                    {l s='Does not works with %1$s or %2$s.' sprintf=[$ntbr_ftp_name|escape:'html':'UTF-8', $ntbr_box_name|escape:'html':'UTF-8'] mod='ntbackupandrestore'}
                    {l s='The backup will not be compressed with this option.' mod='ntbackupandrestore'}
                </label>
                <span class="switch prestashop-switch fixed-width-lg create_on_distant {if $light}deactivate{/if}">
                    <input
                        type="radio" name="create_on_distant_{$config_id|intval}" id="create_on_distant_on_{$config_id|intval}"
                        value="1" {if $config.create_on_distant}checked="checked"{/if}
                    />
                    <label class="t" for="create_on_distant_on_{$config_id|intval}">
                        {l s='Yes' mod='ntbackupandrestore'}
                    </label>
                    <input
                        type="radio" name="create_on_distant_{$config_id|intval}" id="create_on_distant_off_{$config_id|intval}"
                        value="0" {if !$config.create_on_distant}checked="checked"{/if}
                    />
                    <label class="t" for="create_on_distant_off_{$config_id|intval}">
                        {l s='No' mod='ntbackupandrestore'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
            </p>
            <p>
                <label for="backup_dir_{$config_id|intval}">
                    {l s='Choose another directory to save your backups in. Do not use this option if you are not sure of what you are doing!' mod='ntbackupandrestore'}
                    {l s='Default directory:' mod='ntbackupandrestore'} {$default_backup_dir|escape:'html':'UTF-8'}
                    {l s='(Warning. Your shop root is not an authorized backup directory)' mod='ntbackupandrestore'}
                </label>
                <span>
                    <input type="text" name="backup_dir_{$config_id|intval}" id="backup_dir_{$config_id|intval}" value="{$config.backup_dir|escape:'html':'UTF-8'}"/>
                </span>
            </p>
        </div>
    </div>
    <div id="not_backup_{$config_id|intval}" class="panel not_backup">
        <div class="panel-heading">
            <i class="fas fa-cog"></i>&nbsp;{l s='Ignore some directories, files, files types and tables.' mod='ntbackupandrestore'}
        </div>
        {if $light}
            <div class="light_version_error alert alert-info hint">
                <p>
                    {l s='This feature is only available in the' mod='ntbackupandrestore'}
                    <a href="{$link_full_version|escape:'htmlall':'UTF-8'}">
                        {l s='full version of the module' mod='ntbackupandrestore'}
                    </a>
                    {l s='which makes it possible to not save the files that are not useful to you or that are too big to be saved every time. It can be pdf files, video files or any other type of file. You can also skip entire directories.' mod='ntbackupandrestore'}
                </p>
            </div>
            <br/>
        {/if}
        <div {if $light}class="deactivate light_version"{/if}>
            <p class="alert alert-warning warn">
                {l s='Be careful, use these advanced options only if you know exactly what you are doing. Deleting folders, files or tables required to run prestashop will make your backup unusable!' mod='ntbackupandrestore'}
            </p>
            {if $config.type_backup != $backup_type_base}
            <div>
                <label for="ignore_directories_{$config_id|intval}">
                    {l s='Do not save the content of the following directories nor the following files:' mod='ntbackupandrestore'}
                    <i class="far fa-question-circle label-tooltip" data-toggle="tooltip" data-placement="right" data-html="true"
                        title="{l s='The directories will still be created, but without their content (except for .htaccess and index.php)' mod='ntbackupandrestore'}"
                    ></i>
                </label>
                <span>
                    <input
                        type="hidden" name="ignore_directories_{$config_id|intval}" id="ignore_directories_{$config_id|intval}" value="{$config.ignore_directories|escape:'html':'UTF-8'}"
                        title="{l s='Do not save the following directories/files.' mod='ntbackupandrestore'}"
                    />
                </span>

                <div id="tree_directories_{$config_id|intval}" class="panel">
                    <ul class="directories_tree tree">
                        <li class="tree-item">
                            <span class="tree-item-name" onclick="getDirectoryChildren('', this);">
                                <i class="fas fa-folder"></i>
                                <label class="tree-toggler">{l s='Root' mod='ntbackupandrestore'}</label>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            {/if}
            <br/>
            {if $config.type_backup != $backup_type_base}
            <p>
                <label for="ignore_files_types_{$config_id|intval}">
                    {l s='Do not save the following types of files. Separe all the values by ",". Ex: .mp4, .pdf' mod='ntbackupandrestore'}
                </label>
                <span>
                    <input
                        type="text" name="ignore_files_types_{$config_id|intval}" id="ignore_files_types_{$config_id|intval}" value="{$config.ignore_file_types|escape:'html':'UTF-8'}"
                        title="{l s='Do not save the following types of files.' mod='ntbackupandrestore'}"
                    />
                </span>
            </p>
            <br/>
            {/if}
            {if $config.type_backup != $backup_type_file}
            <div>
                <label for="ignore_tables_{$config_id|intval}">
                    {l s='Do not save the following tables.' mod='ntbackupandrestore'}
                </label>
                <span>
                    <input
                        type="hidden" name="ignore_tables_{$config_id|intval}" id="ignore_tables_{$config_id|intval}" value="{$config.ignore_tables|escape:'html':'UTF-8'}"
                        title="{l s='Do not save the following tables.' mod='ntbackupandrestore'}"
                    />
                </span>
                <br/>
                <button type="button" class="btn btn-default ignore_tables_select_all" id="ignore_tables_select_all_{$config_id|intval}">{l s='Select all' mod='ntbackupandrestore'}</button>
                <button type="button" class="btn btn-default ignore_tables_unselect_all" id="ignore_tables_unselect_all_{$config_id|intval}">{l s='Unselect all' mod='ntbackupandrestore'}</button>
                <br/>
                <br/>
                <ul class="panel list_ignore_tables" id="list_ignore_tables_{$config_id|intval}">
                    {foreach $list_datatable_tables as $table}
                        <li><input type="checkbox" value="{$table.name|escape:'html':'UTF-8'}" {if strpos($config.ignore_tables, $table.separated_name) !== false}checked{/if}/> {$table.name|escape:'html':'UTF-8'} <span class="size">({$table.size|escape:'html':'UTF-8'})</span></li>
                    {/foreach}
                </ul>
            </div>
            <br/>
            <div>
                <label for="not_recreate_tables_{$config_id|intval}">
                    {l s='Do not recreate the following excluded tables during restoration. Separe all the values by ",". Ex: ps_log, ps_mail' mod='ntbackupandrestore'}
                </label>
                <span>
                    <input
                        type="hidden" name="not_recreate_tables_{$config_id|intval}" id="not_recreate_tables_{$config_id|intval}" value="{$config.not_recreate_tables|escape:'html':'UTF-8'}"
                        title="{l s='Do not recreate the following excluded tables during restoration.' mod='ntbackupandrestore'}"
                    />
                </span>
                <br/>
                <button type="button" class="btn btn-default not_recreate_tables_select_all" id="not_recreate_tables_select_all_{$config_id|intval}">{l s='Select all' mod='ntbackupandrestore'}</button>
                <button type="button" class="btn btn-default not_recreate_tables_unselect_all" id="not_recreate_tables_unselect_all_{$config_id|intval}">{l s='Unselect all' mod='ntbackupandrestore'}</button>
                <br/>
                <br/>
                <ul class="panel list_not_recreate_tables" id="list_not_recreate_tables_{$config_id|intval}">
                    {foreach $list_datatable_tables as $table}
                        <li><input type="checkbox" value="{$table.name|escape:'html':'UTF-8'}" {if strpos($config.not_recreate_tables, $table.separated_name) !== false}checked{/if}/> {$table.name|escape:'html':'UTF-8'} <span class="size">({$table.size|escape:'html':'UTF-8'})</span></li>
                    {/foreach}
                </ul>
            </div>
            {/if}
        </div>
    </div>
</div>
