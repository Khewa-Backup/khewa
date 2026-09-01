{*
 * khewacorechanges — module configuration page.
 * Shows the state of every carried change and lets you re-apply
 * managed files after a PrestaShop / theme / module update.
*}
<div class="panel">
	<div class="panel-heading"><i class="icon-wrench"></i> {l s='Khewa Core Changes' mod='khewacorechanges'}</div>
	<p>
		{l s='This module carries Khewa\'s custom changes to PrestaShop core, theme and third-party module files. After any PrestaShop, theme or module update, come back here and click "Re-apply all" so the customisations are copied back into place.' mod='khewacorechanges'}
	</p>
	<p>
		{l s='Full details of every change:' mod='khewacorechanges'} <code>modules/khewacorechanges/CORE_CHANGES.md</code> &mdash;
		{l s='what is handled and how to test:' mod='khewacorechanges'} <code>modules/khewacorechanges/UPDATE_SAFETY.md</code>
	</p>
</div>

<div class="panel">
	<div class="panel-heading"><i class="icon-files-o"></i> {l s='Managed files' mod='khewacorechanges'}</div>
	<p class="help-block">
		<span class="label label-success">identical</span> {l s='live file matches the module copy' mod='khewacorechanges'} &nbsp;
		<span class="label label-warning">differs</span> {l s='live file was changed (or overwritten by an update) — Apply pushes the module copy back, Pull refreshes the module copy from the live file' mod='khewacorechanges'} &nbsp;
		<span class="label label-danger">missing</span> {l s='live file does not exist yet' mod='khewacorechanges'}
	</p>
	<form method="post" action="{$kcc_form_action|escape:'html':'UTF-8'}">
		<table class="table">
			<thead>
				<tr>
					<th>#</th>
					<th>{l s='Live file' mod='khewacorechanges'}</th>
					<th>{l s='Module copy' mod='khewacorechanges'}</th>
					<th>{l s='State' mod='khewacorechanges'}</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			{foreach $kcc_rows as $row}
				<tr>
					<td>{$row.change|escape:'html':'UTF-8'}</td>
					<td><code>{$row.dest|escape:'html':'UTF-8'}</code></td>
					<td><small><code>{$row.src|escape:'html':'UTF-8'}</code></small></td>
					<td>
						{if $row.state == 'identical'}<span class="label label-success">identical</span>
						{elseif $row.state == 'differs'}<span class="label label-warning">differs</span>
						{elseif $row.state == 'missing'}<span class="label label-danger">missing</span>
						{else}<span class="label label-default">{$row.state|escape:'html':'UTF-8'}</span>{/if}
					</td>
					<td class="text-right">
						{if $row.state != 'identical' && $row.state != 'no_source'}
							<button type="submit" class="btn btn-default btn-xs" name="submitKhewaApplyOne" value="1" onclick="this.form.managed_key.value='{$row.key|escape:'html':'UTF-8'}';">
								<i class="icon-download"></i> {l s='Apply' mod='khewacorechanges'}
							</button>
						{/if}
						{if $row.state == 'differs'}
							<button type="submit" class="btn btn-default btn-xs" name="submitKhewaPullOne" value="1" onclick="return confirm('{l s='Replace the module copy with the live file?' mod='khewacorechanges' js=1}') && (this.form.managed_key.value='{$row.key|escape:'html':'UTF-8'}');">
								<i class="icon-upload"></i> {l s='Pull' mod='khewacorechanges'}
							</button>
						{/if}
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		<input type="hidden" name="managed_key" value="" />
		<div class="panel-footer">
			<button type="submit" class="btn btn-default pull-right" name="submitKhewaApplyAll" value="1">
				<i class="icon-refresh"></i> {l s='Re-apply all' mod='khewacorechanges'}
			</button>
		</div>
	</form>
</div>

<div class="panel">
	<div class="panel-heading"><i class="icon-code"></i> {l s='Class overrides' mod='khewacorechanges'}</div>
	<p class="help-block">{l s='Installed by PrestaShop when the module is installed. If one shows "missing", reset (uninstall + install) the module.' mod='khewacorechanges'}</p>
	<table class="table">
		<thead><tr><th>{l s='Override' mod='khewacorechanges'}</th><th>{l s='State' mod='khewacorechanges'}</th></tr></thead>
		<tbody>
		{foreach $kcc_overrides as $ov}
			<tr>
				<td><code>{$ov.file|escape:'html':'UTF-8'}</code></td>
				<td>
					{if $ov.state == 'installed'}<span class="label label-success">installed</span>
					{elseif $ov.state == 'foreign'}<span class="label label-warning">present but not from this module</span>
					{else}<span class="label label-danger">missing</span>{/if}
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>

<div class="panel">
	<div class="panel-heading"><i class="icon-plug"></i> {l s='Hooks & services (always active while the module is enabled)' mod='khewacorechanges'}</div>
	<ul>
		<li><code>displayBackOfficeHeader</code> &mdash; {l s='hides the "Total spent" badge in the Customers list (#7, #8)' mod='khewacorechanges'}</li>
		<li><code>actionEmailSendBefore</code> &mdash; {l s='serves order_conf from modules/khewacorechanges/mails/ with the pickup message (#9)' mod='khewacorechanges'}</li>
		<li><code>config/services.yml</code> &mdash; {l s='replaces the Orders grid query builder with the fast version (#3)' mod='khewacorechanges'}</li>
	</ul>
</div>
