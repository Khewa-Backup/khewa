
{if isset($setting) && $setting && $page == 'new_settings'}
    <div class="list-settings-module-header"> <i class="icon-cogs"></i>{l s='  Saved settings for export'  mod='updateproducts'}</div>
    <ul class="list-settings-module">
        {foreach $setting as $key => $set}
            <li {if $set['id'] == $id} class="active_setting" {/if}>
                <span class="settings_key">{$set['id']|escape:'htmlall':'UTF-8'}.</span>
                <a href="{$base_url|escape:'htmlall':'UTF-8'}&settings={$set['id']|escape:'htmlall':'UTF-8'}" class="one_setting">{$set['name']|escape:'htmlall':'UTF-8'}</a>
                <a id-setting="{$set['id']|escape:'htmlall':'UTF-8'}" class="delete_setting btn btn-default"><i class="icon-trash"></i></a>
            </li>
        {/foreach}
    </ul>
{/if}


{if isset($setting_update) && $setting_update && $page == 'update_settings'}
    <div class="list-settings-module-header"> <i class="icon-cogs"></i>{l s='  Saved settings for update'  mod='updateproducts'}</div>
    <ul class="list-settings-module">
        {foreach $setting_update as $key => $set}
            <li {if $set['id'] == $idUpdate} class="active_setting_update" {/if}>
                <span class="settings_key_update">{$set['id']|escape:'htmlall':'UTF-8'}.</span>
                <a href="{$base_url|escape:'htmlall':'UTF-8'}&settingsUpdate={$set['id']|escape:'htmlall':'UTF-8'}" class="one_setting_update">{$set['name']|escape:'htmlall':'UTF-8'}</a>
                <a id-setting-update="{$set['id']|escape:'htmlall':'UTF-8'}" class="delete_setting_update btn btn-default"><i class="icon-trash"></i></a>
            </li>
        {/foreach}
    </ul>
{/if}