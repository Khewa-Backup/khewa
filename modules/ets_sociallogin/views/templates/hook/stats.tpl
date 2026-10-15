{*
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*}
<div class="solo_admin_statistic">
    <div class="solo_admin_filter">
        <form id="solo_admin_filter_chart" class="defaultForm form-horizontal" action="{$action|escape:'quotes':'UTF-8'}" enctype="multipart/form-data" method="POST">
            <div class="solo_admin_filter_chart_settings">
                <div class="solo_admin_filter_date">
                    <label>{l s='Month' mod='ets_sociallogin'}</label>
                    <select id="months" name="months" class="form-control">
                        <option value="" {if !$solo_month} selected="selected"{/if}>{l s='All' mod='ets_sociallogin'}</option>
                        {foreach from=$months key=k item=month}
                            <option value="{$k|intval}"{if $solo_month == $k} selected="selected"{/if}>{l s=$month mod='ets_sociallogin'}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="solo_admin_filter_date">
                    <label>{l s='Year' mod='ets_sociallogin'}</label>
                    <select id="years" name="years" class="form-control">
                        <option value="" {if !$solo_year} selected="selected"{/if}>{l s='All' mod='ets_sociallogin'}</option>
                        {foreach from=$years item=year}
                            <option value="{$year|intval}" {if $solo_year == $year} selected="selected"{/if}>{$year|intval}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="solo_admin_filter_country">
                    <label>{l s='Country' mod='ets_sociallogin'}</label>
                    <select id="countries" name="countries" class="form-control">
                        <option value=""{if !$solo_country} selected="selected"{/if}>{l s='All' mod='ets_sociallogin'}</option>
                        {foreach from=$countries item=country}
                            <option value="{$country.id_country|intval}" {if $solo_country == $country.id_country} selected="selected"{/if}>{$country.name|escape:'html':'UTF-8'}</option>
                        {/foreach}
                        <option value="-1" {if $solo_country == -1} selected="selected"{/if}>{l s='Unregistered' mod='ets_sociallogin'}</option>
                    </select>
                </div>
                <div class="solo_admin_filter_button">
                    <button name="submitFilterChart" class="btn btn-default" type="submit">{l s='Filter' mod='ets_sociallogin'}</button>
                    <button name="submitResetChart" class="btn btn-default" type="submit">{l s='Reset' mod='ets_sociallogin'}</button>
                </div>
            </div>
        </form>
    </div>
    <div class="solo_admin_chart">
        <div class="line_chart">
            <span class="solo_admin_chart_label">{l s='Traffic change chart' mod='ets_sociallogin'}</span>
            <svg style="width:100%; height: 400px;"></svg>
        </div>
    </div>
    <div class="solo_admin_bar_chart">
        <div class="bar_chart">
            <span class="solo_admin_chart_label">{l s='Comparison' mod='ets_sociallogin'}</span>
            <canvas style="width: 100%;height: 800px"></canvas>
        </div>
        <div class="pie_chart">
            <span class="solo_admin_chart_label">{l s='Traffic ratio chart' mod='ets_sociallogin'}</span>
            <div class="ets-piechart-box">
                <svg></svg>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var ets_solo_x_days = '{l s='Day' mod='ets_sociallogin'}';
    var ets_solo_x_months = '{l s='Month' mod='ets_sociallogin'}';
    var ets_solo_x_years = '{l s='Year' mod='ets_sociallogin'}';
    var ets_solo_y_label = '{l s='Count' mod='ets_sociallogin'}';
    var ets_solo_pie_chart = {$pie_chart|json_encode};
    var ets_solo_line_chart = {$line_chart|json_encode}
    var ets_solo_bar_chart = {$bar_chart|json_encode}
</script>