{**
 * Spam Protection - Google reCAPTCHA, hCaptcha & GDPR Captcha
 *
 * @author    WebshopWorks
 * @copyright 2018-2026 WebshopWorks.com
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 *}
<div id="irc-tab-stats" class="panel" style="{if 'stats' !== $irc_panel}display: none{/if}">
	<div class="irc-stats-filters">
		<div class="irc-filter-group">
			<label for="irc-date-preset">{l s='Date Range' mod='invrecaptcha'}</label>
			<select id="irc-date-preset" class="form-control">
				<option value="custom">{l s='Custom' mod='invrecaptcha'}</option>
				<option value="7days">{l s='Last 7 Days' mod='invrecaptcha'}</option>
				<option value="thisweek">{l s='This Week' mod='invrecaptcha'}</option>
				<option value="lastweek">{l s='Last Week' mod='invrecaptcha'}</option>
				<option value="30days" selected>{l s='Last 30 Days' mod='invrecaptcha'}</option>
				<option value="thismonth">{l s='This Month' mod='invrecaptcha'}</option>
				<option value="lastmonth">{l s='Last Month' mod='invrecaptcha'}</option>
				<option value="3months">{l s='All 3 Months' mod='invrecaptcha'}</option>
			</select>
		</div>
		<div class="irc-filter-group">
			<label for="irc-date-from">{l s='From' d='Admin.Global'}</label>
			<input type="date" id="irc-date-from" class="form-control" value="{$irc_date_from|escape:'html':'UTF-8'}">
		</div>
		<div class="irc-filter-group">
			<label for="irc-date-to">{l s='To' d='Admin.Global'}</label>
			<input type="date" id="irc-date-to" class="form-control" value="{$irc_date_to|escape:'html':'UTF-8'}">
		</div>
		<div class="irc-filter-group">
			<label for="irc-form-filter">{l s='Form' mod='invrecaptcha'}</label>
			<select id="irc-form-filter" class="form-control">
				<option value="">{l s='All forms' mod='invrecaptcha'}</option>
			{foreach $irc_forms as $f}
				<option value="{$f.name|escape:'html':'UTF-8'}">{$f.label|escape:'html':'UTF-8'}</option>
			{/foreach}
			</select>
		</div>
		<div class="irc-filter-group irc-filter-actions">
			<button type="button" id="irc-apply-filters" class="btn btn-default">
				<i class="icon-search"></i> {l s='Apply' d='Admin.Actions'}
			</button>
		</div>
	</div>

	<div class="irc-stats-cards">
		<div class="irc-card irc-card--total">
			<div class="irc-card__value" id="irc-stat-total">–</div>
			<div class="irc-card__label">{l s='Total Requests' mod='invrecaptcha'}</div>
		</div>
		<div class="irc-card irc-card--legit">
			<div class="irc-card__value" id="irc-stat-legit">–</div>
			<div class="irc-card__label">{l s='Legitimate' mod='invrecaptcha'}</div>
		</div>
		<div class="irc-card irc-card--blocked">
			<div class="irc-card__value" id="irc-stat-blocked">–</div>
			<div class="irc-card__label">{l s='Blocked' mod='invrecaptcha'}</div>
		</div>
		<div class="irc-card irc-card--rate">
			<div class="irc-card__value" id="irc-stat-rate">–</div>
			<div class="irc-card__label">{l s='Block Rate' mod='invrecaptcha'}</div>
		</div>
	</div>

	<div class="irc-chart-wrapper">
		<canvas id="irc-stats-chart" height="300"></canvas>
	</div>

	<div class="table-responsive">
		<table class="table" id="irc-stats-table">
			<thead>
				<tr>
					<th>{l s='Form' mod='invrecaptcha'}</th>
					<th class="text-center">{l s='Total' d='Admin.Global'}</th>
					<th class="text-center">{l s='Legitimate' mod='invrecaptcha'}</th>
					<th class="text-center">{l s='Blocked' mod='invrecaptcha'}</th>
					<th class="text-center">{l s='Block Rate' mod='invrecaptcha'}</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
