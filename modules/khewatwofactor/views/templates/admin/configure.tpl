{*
 * Khewa staff login code: settings page.
 *}
<form method="post" action="{$k2fa_action|escape:'html':'UTF-8'}" class="form-horizontal">
  <input type="hidden" name="khewa2fa_form" value="1">
  <input type="hidden" name="khewa2fa_token" value="{$k2fa_token|escape:'html':'UTF-8'}">

  <div class="panel">
    <div class="panel-heading"><i class="icon-lock"></i> {l s='Staff login code' mod='khewatwofactor'}</div>

    {if $k2fa_mail_off}
      <div class="alert alert-danger">{l s='Shop email is turned off (PS_MAIL_METHOD = 3). No code can be sent, so staff cannot log in while the check is on. Fix the email settings first.' mod='khewatwofactor'}</div>
    {/if}
    {if $k2fa_switch_file}
      <div class="alert alert-warning">{l s='The file DISABLE_2FA is in the module folder, so the check is OFF. Delete that file to turn it back on.' mod='khewatwofactor'}</div>
    {/if}
    <p>
      {l s='Right now the check is:' mod='khewatwofactor'}
      {if $k2fa_is_on}<strong class="text-success">{l s='ON' mod='khewatwofactor'}</strong>{else}<strong class="text-danger">{l s='OFF' mod='khewatwofactor'}</strong>{/if}
    </p>

    <div class="form-group">
      <label class="control-label col-lg-3">{l s='Ask for a code at login' mod='khewatwofactor'}</label>
      <div class="col-lg-9">
        <span class="switch prestashop-switch fixed-width-lg">
          <input type="radio" name="khewa2fa_enabled" id="khewa2fa_enabled_on" value="1" {if $k2fa_enabled}checked="checked"{/if}>
          <label for="khewa2fa_enabled_on">{l s='Yes' mod='khewatwofactor'}</label>
          <input type="radio" name="khewa2fa_enabled" id="khewa2fa_enabled_off" value="0" {if !$k2fa_enabled}checked="checked"{/if}>
          <label for="khewa2fa_enabled_off">{l s='No' mod='khewatwofactor'}</label>
          <a class="slide-button btn"></a>
        </span>
      </div>
    </div>

    <div class="form-group">
      <label class="control-label col-lg-3" for="khewa2fa_trust_days">{l s='Trust a computer for (days)' mod='khewatwofactor'}</label>
      <div class="col-lg-2">
        <input type="number" min="1" max="365" name="khewa2fa_trust_days" id="khewa2fa_trust_days" value="{$k2fa_trust_days|intval}">
      </div>
    </div>

    <div class="panel-footer">
      <button type="submit" name="khewa2fa_save" value="1" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='khewatwofactor'}</button>
    </div>
  </div>

  <div class="panel">
    <div class="panel-heading"><i class="icon-desktop"></i> {l s='Trusted computers' mod='khewatwofactor'} ({$k2fa_trusted|count})</div>
    {if $k2fa_trusted}
      <table class="table">
        <thead>
          <tr>
            <th>{l s='Staff' mod='khewatwofactor'}</th>
            <th>{l s='Trusted on' mod='khewatwofactor'}</th>
            <th>{l s='Last used' mod='khewatwofactor'}</th>
            <th>{l s='Ends on' mod='khewatwofactor'}</th>
            <th>{l s='IP' mod='khewatwofactor'}</th>
            <th>{l s='Browser' mod='khewatwofactor'}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {foreach $k2fa_trusted as $row}
            <tr>
              <td>{$row.staff|escape:'html':'UTF-8'}</td>
              <td>{$row.date_add|escape:'html':'UTF-8'}</td>
              <td>{$row.last_used|escape:'html':'UTF-8'}</td>
              <td>{$row.expires|escape:'html':'UTF-8'}</td>
              <td>{$row.ip|escape:'html':'UTF-8'}</td>
              <td><small>{$row.user_agent|truncate:80:'...'|escape:'html':'UTF-8'}</small></td>
              <td><button type="submit" name="khewa2fa_delete" value="{$row.id|intval}" class="btn btn-default btn-sm"><i class="icon-trash"></i> {l s='Remove' mod='khewatwofactor'}</button></td>
            </tr>
          {/foreach}
        </tbody>
      </table>
      <div class="panel-footer">
        <button type="submit" name="khewa2fa_delete_all" value="1" class="btn btn-danger pull-right"><i class="icon-trash"></i> {l s='Remove all trusted computers' mod='khewatwofactor'}</button>
      </div>
    {else}
      <p>{l s='No trusted computers.' mod='khewatwofactor'}</p>
    {/if}
  </div>
</form>
