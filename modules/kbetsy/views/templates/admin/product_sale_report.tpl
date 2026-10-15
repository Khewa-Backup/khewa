{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* @category  PrestaShop Module
* @author    knowband.com <support@knowband.com>
* @copyright 2017 Knowband
* @license   see file: LICENSE.txt
*
* Description
*
* Admin Customize Product Title Placeholders tpl file
*}
{*{if !isset($is_report_display)}*}
<div class="panel">
    <div class="panel-heading">
        {l s='Sales Report' mod='kbetsy'}
    </div>
    <div class="panel-body">
        <div class="row product_sale_report">
            <div class="col-lg-3">
                <div class="form-group">
                    <label class="control-label required">
                        {l s='Products' mod='kbetsy'}
                    </label>
                    <select name='sku[]' data-placeholder="{l s='Select product from list' mod='kbetsy'}" class="form-control" id="product-sale-sku-select" multiple="multiple">
                        {if !empty($productDetails)}
                            {foreach $productDetails as $detail}
                                <option value="{$detail['reference']|escape:'htmlall':'UTF-8'}">{$detail['name']|escape:'htmlall':'UTF-8'} ({$detail['reference']|escape:'htmlall':'UTF-8'})</option>
                            {/foreach}
                        {/if}
                    </select>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label class="control-label required">
                        {l s='Start Date' mod='kbetsy'}
                    </label>
                    <div class="input-group">
                        <input type="text" name="start_date" class="form-control start_date input-medium" value="{'-2 month'|date_format:"%Y-%m-%d"|escape:'htmlall':'UTF-8'}">
                        <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label class="control-label required">
                        {l s='End Date' mod='kbetsy'}
                    </label>
                    <div class="input-group">
                        <input type="text" name="end_date" class="form-control input-medium end_date" value="{$smarty.now|date_format:"%Y-%m-%d"|escape:'htmlall':'UTF-8'}">
                        <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label class="control-label">
                        {l s='Group by' mod='kbetsy'}
                    </label>
                    <select name="groupby" class="form-control" id="group_by">
                        <option value="days" selected="selected">{l s='Days' mod='kbetsy'}</option>
                        <option value="months">{l s='Month' mod='kbetsy'}</option>
                        <option value="years">{l s='Year' mod='kbetsy'}</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-1">
                <div class="form-group" >
                    <label class="control-label" style="width:100%;height: 12px;"> </label>
                    <button type="button" class="btn btn-primary productsalereportbtn">{l s='Filter' mod='kbetsy'}</button>
                </div>
            </div>

        </div>
        <div class="productsalereporttable">
            <table class="table responsive">
                <thead class="">
                    <tr>
                        <th scope="col">{l s='Product Reference' mod='kbetsy'}</th>
                        <th scope="col">{l s='Total Sales' mod='kbetsy'}</th>
                        <th scope="col">{l s='Total Products' mod='kbetsy'}</th>
                        <th scope="col">{l s='Total Revenue' mod='kbetsy'}</th>
                    </tr>
                </thead>
                <tbody>
                    {if !empty($data) && isset($data)}
                        {foreach $data as $item}
                            {if $item['count'] > 0}
                                <tr>
                                    <td>{$item['sku']|escape:'htmlall':'UTF-8'}</td>
                                    <td>{$item['count']|escape:'htmlall':'UTF-8'}</td>
                                    <td>{if isset($item['total_product'])}{$item['total_product']|escape:'htmlall':'UTF-8'}{else}0{/if}</td>
                                    <td>{$item['total']|escape:'htmlall':'UTF-8'}</td>
                                </tr>
                            {/if}
                        {/foreach}
                    {else}
                        <tr>
                            <td colspan="4" style="text-align: center;"> {l s='No order found.' mod='kbetsy'}</td>
                        </tr>
                    {/if}
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    var currentText = '{l s='Now'  mod='kbetsy' js=1}';
    var closeText = '{l s='Done'  mod='kbetsy' js=1}';
    var timeOnlyTitle = '{l s='Choose Time'  mod='kbetsy' js=1}';
    var timeText = '{l s='Time' mod='kbetsy' js=1}';
    var hourText = '{l s='Hour' mod='kbetsy' js=1}';
    var minuteText = '{l s='Minute' mod='kbetsy' js=1}';
    var end_date_error = "{l s='End date cannot be previous to start date.' mod='kbetsy'}";
    var module_path = "{$module_path}";{*Variable contains URL content, escape not required*}
    var technical_error = "{l s='There is some technical error' mod='kbetsy'}";
    var sku_empty_error = "{l s='Please select atleast one sku.' mod='kbetsy'}";

</script>

<style>

    .modal {
        display:    none;
        position:   fixed;
        z-index:    1000;
        top:        0;
        left:       0;
        height:     100%;
        width:      100%;
        background: rgba( 255, 255, 255, .8 ) 
            url('{$loader|escape:'quotes':'UTF-8'}') 
            50% 50% 
            no-repeat;
    }

    /* When the body has the loading class, we turn
       the scrollbar off with overflow:hidden */
    body.loading {
        overflow: hidden;   
    }

    /* Anytime the body has the loading class, our
       modal element will be visible */
    body.loading .modal {
        display: block;
    }
</style>
<div class="modal"></div>