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
    <i class="fas fa-cogs"></i>
    &nbsp;{l s='Add profile' mod='ntbackupandrestore'}
</div>

<div class="panel {if $light}light_version{/if}">
    <div class="panel-heading">
        <i class="fas fa-cog"></i>&nbsp;{l s='Add a new profile' mod='ntbackupandrestore'}
    </div>
    <p>
        <label for="profile_name">{l s='Name' mod='ntbackupandrestore'}</label>
        <span>
            <input type="text" name="profile_name" id="profile_name" placeholder="{l s='Fill in a name for this new profile' mod='ntbackupandrestore'}" value=""/>
        </span>
    </p>
    <p>
        <label for="profile_type">{l s='Type' mod='ntbackupandrestore'}</label>
        <select name="profile_type" id="profile_type">
            <option value="{$backup_type_complete|escape:'html':'UTF-8'}">
                {l s='Complete' mod='ntbackupandrestore'}
            </option>
            <option value="{$backup_type_file|escape:'html':'UTF-8'}">
                {l s='File' mod='ntbackupandrestore'}
            </option>
            <option value="{$backup_type_base|escape:'html':'UTF-8'}">
                {l s='Dump' mod='ntbackupandrestore'}
            </option>
        </select>
    </p>
    {*<p>
        <label>{l s='Default configuration' mod='ntbackupandrestore'}</label>
        <span class="switch prestashop-switch fixed-width-lg {if $light}deactivate{/if}">
            <input type="radio" name="profile_is_default" id="profile_is_default_on" value="1"/>
            <label class="t" for="profile_is_default_on">{l s='Yes' mod='ntbackupandrestore'}</label>
            <input type="radio" name="profile_is_default" id="profile_is_default_off" value="0" checked="checked"/>
            <label class="t" for="profile_is_default_off">{l s='No' mod='ntbackupandrestore'}</label>
            <a class="slide-button btn"></a>
        </span>
    </p>*}
</div>
<div class="panel-footer">
    <button id="nt_save_config_profile_btn" class="btn btn-default pull-right">
        <i class="far fa-save process_icon"></i> {l s='Save' mod='ntbackupandrestore'}
    </button>
</div>