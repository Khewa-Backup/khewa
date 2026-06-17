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

<div class="panel-heading">
    <i class="far fa-clock"></i>
    &nbsp;{l s='Automation' mod='ntbackupandrestore'}
</div>
<div>
    <div {if !$activate_2nt_automation}class="deactivate"{/if}>
        {if !$activate_2nt_automation}
            <p class="error alert alert-danger">
                {l s='This option is not available for local websites.' mod='ntbackupandrestore'}
            </p>
        {/if}
        <p>
            <label for="automation_2nt" id="automation_2nt">
                <i class="far fa-question-circle label-tooltip" data-toggle="tooltip" data-placement="right" data-html="true"
                   title="{l s='This automation will launch a daily backup. If you are using the multi configurations option, it will launch your default config. If not it will launch a complete backup.' mod='ntbackupandrestore'}"
                ></i>
                {l s='Automation by 2n-tech.com at' mod='ntbackupandrestore'}
            </label>
            <select id="automation_2nt_hours" name="automation_2nt_hours">
                {for $i=0; $i<24; $i++}
                    {if $i < 10}
                        {assign var='hours' value="0$i"}
                    {else}
                        {assign var='hours' value=$i}
                    {/if}
                    <option {if $automation_2nt_hours == $i}selected="selected"{/if} value="{$i|intval}">{$hours|escape:'html':'UTF-8'}</option>
                {/for}
            </select>
            H
            <select id="automation_2nt_minutes" name="automation_2nt_minutes">
                {for $i=0; $i<60; $i++}
                    {if $i < 10}
                        {assign var='minutes' value="0$i"}
                    {else}
                        {assign var='minutes' value=$i}
                    {/if}
                    <option {if $automation_2nt_minutes == $i}selected="selected"{/if} value="{$i|intval}">{$minutes|escape:'html':'UTF-8'}</option>
                {/for}
            </select>
                {l s='Current server time:' mod='ntbackupandrestore'}
                <span id="current_hour">{$current_hour|escape:'html':'UTF-8'}</span>
                <span id="time_zone">({$time_zone|escape:'html':'UTF-8'})</span>
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="automation_2nt" id="automation_2nt_on" value="1" {if $automation_2nt}checked="checked"{/if}/>
                <label class="t" for="automation_2nt_on">
                    {l s='Yes' mod='ntbackupandrestore'}
                </label>
                <input type="radio" name="automation_2nt" id="automation_2nt_off" value="0"  {if !$automation_2nt}checked="checked"{/if}/>
                <label class="t" for="automation_2nt_off">
                    {l s='No' mod='ntbackupandrestore'}
                </label>
                <a class="slide-button btn"></a>
            </span>
        </p>
        <p class="alert alert-warning warn">
            {l s='The automation service by 2n-tech.com only start your backup automatically at the specified time. Your data is not sent to the 2n-tech.com server. It\'s always your server that runs your backup.' mod='ntbackupandrestore'}
        </p>
    </div>
    {if $shop_url_changed}
        {if !$fct_crypt_exists}
            <div class="fct_crypt_error error alert alert-danger">
                <p>
                    {l s='Update domain cannot work with your current configuration. Please check the following requirements:' mod='ntbackupandrestore'}
                </p>
                <ul>
                    <li>
                        {l s='PHP openssl is not loaded. Please enable it in your hosting management to use update domain.' mod='ntbackupandrestore'}
                    </li>
                </ul>
            </div>
            <br/>
        {/if}

        <p {if !$fct_crypt_exists}class="deactivate"{/if}>
            <button type="button" class="btn btn-default" id="nt_shop_url_changed" name="nt_shop_url_changed">
                <i class="fas fa-sync-alt"></i>
                {l s='Update domain' mod='ntbackupandrestore'}
            </button>
        </p>
    {/if}
    <p>
        <button type="button" class="btn btn-default" id="nt_advanced_automation" name="nt_advanced_automation">
            <i class="fas fa-sliders-h"></i>
            {l s='Advanced' mod='ntbackupandrestore'}
        </button>
    </p>
    <div id="nt_advanced_automation_diplay">
        <div class="panel">
            <div class="panel-heading">
                <i class="far fa-clock"></i>&nbsp;{l s='Advanced automation - Cron.' mod='ntbackupandrestore'}
            </div>
            <p>
                {l s='If you want to backup your site automatically yourself, you can create a CRON on your server.' mod='ntbackupandrestore'} <br/>
                {l s='The way to do this depends on your hosting.' mod='ntbackupandrestore'} <br/>
                {l s='To simplify the task, you will find below several usual techniques.' mod='ntbackupandrestore'} <br/>
            </p>

            <div id="cron_block">
                <ul id="nt_advanced_automation_tab">
                    <li id="nt_aat_0" class="active">{l s='WGet' mod='ntbackupandrestore'}</li>
                    {if !$is_presta_edition}
                        <li id="nt_aat_1">{l s='Path' mod='ntbackupandrestore'}</li>
                    {/if}
                    <li id="nt_aat_2">{l s='URL' mod='ntbackupandrestore'}</li>
                    <li id="nt_aat_3">{l s='cURL' mod='ntbackupandrestore'}</li>
                    <li id="nt_aat_4">{l s='PHP Script' mod='ntbackupandrestore'}</li>
                </ul>
                <div class="clear"></div>

                <div class="nt_aat" id="nt_aat_0_content">
                    <p>{l s='WGet works with most web hosts.' mod='ntbackupandrestore'}</p>
                    <div id="cron_wget"></div>
                </div>
                {if !$is_presta_edition}
                    <div class="nt_aat" id="nt_aat_1_content">
                        <p>{l s='Direct path to start the backup. Useful for hosters who only allow CRONs through PHP CLI' mod='ntbackupandrestore'}</p>
                        <div id="cron_path"></div>
                    </div>
                {/if}
                <div class="nt_aat" id="nt_aat_2_content">
                    <p>{l s='Direct URL to start the backup. Useful for services sites of Web Cron.' mod='ntbackupandrestore'}</p>
                    <div id="cron_url"></div>
                </div>
                <div class="nt_aat" id="nt_aat_3_content">
                    <p>{l s='CURL works with some web hosts.' mod='ntbackupandrestore'}</p>
                    <div id="cron_curl"></div>
                </div>
                <div class="nt_aat" id="nt_aat_4_content">
                    <p>{l s='You can directly integrate into your PHP scripts the backup startup' mod='ntbackupandrestore'}</p>
                    <div id="cron_php_script"></div>
                </div>
            </div>
            {if !$is_presta_edition}
                {if $light}
                    <div class="light_version_error alert alert-info hint">
                        <p>
                            {l s='This advanced option is only available in the' mod='ntbackupandrestore'}
                            <a href="{$link_full_version|escape:'htmlall':'UTF-8'}">
                                {l s='full version of the module' mod='ntbackupandrestore'}
                            </a>.
                        </p>
                    </div>
                    <br/>
                {/if}
                <div class="{if $light}light_version{/if}">
                    <p>{l s='You can also automate your backup download with a secure link. Please click on the button below to generate this secure link.' mod='ntbackupandrestore'}</p>
                    <p>
                        <button type="button" name="generate_url" id="generate_url" class="btn btn-default">
                            <i class="fas fa-link"></i>
                            {l s='Generate secure download link' mod='ntbackupandrestore'}
                        </button>
                    </p>
                    <div id="download_links">
                        <p>{l s='You can download the backup with this URL:' mod='ntbackupandrestore'}</p>
                        <p class="backup_link"></p>
                        {if $activate_log}
                            <p>{l s='You can download the log with this URL:' mod='ntbackupandrestore'}</p>
                            <p class="backup_log"></p>
                        {/if}
                    </div>
                </div>
            {/if}
        </div>
        <div>
            <p>
                <label for="automation_2nt_ip">{l s='Automation by 2n-tech.com IP authorization. Automation by 2n-tech.com requires IP to be authorized to start automation if maintenance mode is enabled (default IPv4 and IPv6):' mod='ntbackupandrestore'}</label>
                <select name="automation_2nt_ip" id="automation_2nt_ip">
                    <option value="0" {if $automation_2nt_ip == 0}selected="selected"{/if}>
                        {l s='Authorize IPv4 and IPv6' mod='ntbackupandrestore'}
                    </option>
                    <option value="1" {if $automation_2nt_ip == 1}selected="selected"{/if}>
                        {l s='Authorize only IPv4' mod='ntbackupandrestore'}
                    </option>
                    <option value="2" {if $automation_2nt_ip == 2}selected="selected"{/if}>
                        {l s='Authorize only IPv6' mod='ntbackupandrestore'}
                    </option>
                    <option value="3" {if $automation_2nt_ip == 3}selected="selected"{/if}>
                        {l s='Authorize neither IPv4 nor IPv6' mod='ntbackupandrestore'}
                    </option>
                </select>
            </p>
        </div>
    </div>
    <div class="panel-footer">
        <button id="nt_save_automation_btn" class="btn btn-default pull-right">
            <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
        </button>
    </div>
</div>