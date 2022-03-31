{**
*
* NOTICE OF LICENSE
*
*  @author    IntelliPresta <tehran.alishov@gmail.com>
*  @copyright 2020 IntelliPresta
*  @license   Commercial License
*/
*}

<div id="data_export_orders_filter_data" class="tab-pane">
    <div class="alert alert-info alert-dismissible fade in">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <span>{l s='If all items of a group (e.g. Customers) are selected or deselected, they are not included in the filter.' mod='ordersexportsalesreportpro'}</span>
    </div>
    <div class="row">
        <button id="expand_data_filter" class="btn btn-primary pull-right">
            <span>{l s='Expand all' mod='ordersexportsalesreportpro'}</span>
            <i class="icon-angle-right"></i>
        </button>
    </div>
    <br />
    <h3>
        <i class="icon-calendar"></i> 
        {l s='Filter By Date' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-down pull-right"></i>
    </h3>
    {*    <br>*}
    <div class="collapse in">
        <div class="form-group date_collapser">
            <label class="control-label col-lg-3">
                <b>{l s='Order Creation Date ' mod='ordersexportsalesreportpro'}</b>
            </label>
            <div class="col-lg-9">
                <select id="data_export_orders_creation_date" name="orders_creation_date" class="fixed-width-xl">
                    <option value="no_date">-- {l s='All time' mod='ordersexportsalesreportpro'} --</option> 
                    <option value="today">{l s='Today' mod='ordersexportsalesreportpro'}</option> 
                    <option value="last_24_hours">{l s='Last 24 hours' mod='ordersexportsalesreportpro'}</option> 
                    <option value="yesterday">{l s='Yesterday' mod='ordersexportsalesreportpro'}</option>
                    <option value="this_week">{l s='This week' mod='ordersexportsalesreportpro'}</option>
                    <option value="last_week">{l s='Last week' mod='ordersexportsalesreportpro'}</option>
                    <option selected="selected" value="this_month">{l s='This month' mod='ordersexportsalesreportpro'}</option>
                    <option value="last_month">{l s='Last month' mod='ordersexportsalesreportpro'}</option>
                    <option value="select_date">{l s='Select date' mod='ordersexportsalesreportpro'}</option>
                </select>
            </div>
        </div>
        <div class="form-group collapse">
            <label class="control-label col-lg-3">
                {l s='Select a period ' mod='ordersexportsalesreportpro'}
            </label>
            <div class="col-lg-9">
                <div class="col-lg-4">
                    <div class="input-group fixed-width-xl">
                        <span class="input-group-addon">
                            <i class="icon-calendar"></i>
                            {l s='From' mod='ordersexportsalesreportpro'}
                        </span>
                        <input
                            id="data_export_orders_from_date"
                            name="orders_from_date"
                            type="text"
                            data-hex="true"
                            class="datepicker"
                            value="" />
                    </div>
                </div>
                {*<div class="col-lg-2">
                </div>*}
                <div class="col-lg-4">
                    <div class="input-group datepicker fixed-width-xl">
                        <span class="input-group-addon">
                            <i class="icon-calendar"></i>
                            {l s='Till' mod='ordersexportsalesreportpro'}
                        </span>
                        <input
                            id="data_export_orders_to_date"
                            name="orders_to_date"
                            type="text"
                            data-hex="true"
                            class=""
                            value="" />
                    </div>
                </div>
            </div>
        </div>

        <br>

        <div class="form-group date_collapser">
            <label class="control-label col-lg-3">
                <b>{l s='Invoice Date ' mod='ordersexportsalesreportpro'}</b>
            </label>
            <div class="col-lg-9">
                <select id="data_export_orders_invoice_date" name="orders_invoice_date" class="fixed-width-xl">
                    <option value="no_date">-- {l s='All time' mod='ordersexportsalesreportpro'} --</option> 
                    <option value="today">{l s='Today' mod='ordersexportsalesreportpro'}</option> 
                    <option value="last_24_hours">{l s='Last 24 hours' mod='ordersexportsalesreportpro'}</option> 
                    <option value="yesterday">{l s='Yesterday' mod='ordersexportsalesreportpro'}</option>
                    <option value="this_week">{l s='This week' mod='ordersexportsalesreportpro'}</option>
                    <option value="last_week">{l s='Last week' mod='ordersexportsalesreportpro'}</option>
                    <option value="this_month">{l s='This month' mod='ordersexportsalesreportpro'}</option>
                    <option value="last_month">{l s='Last month' mod='ordersexportsalesreportpro'}</option>
                    <option value="select_date">{l s='Select date' mod='ordersexportsalesreportpro'}</option>
                </select>
            </div>
        </div>
        <div class="form-group collapse">
            <label class="control-label col-lg-3">
                {l s='Select a period ' mod='ordersexportsalesreportpro'}
            </label>
            <div class="col-lg-9">
                <div class="col-lg-4">
                    <div class="input-group fixed-width-xl">
                        <span class="input-group-addon">
                            <i class="icon-calendar"></i>
                            {l s='From' mod='ordersexportsalesreportpro'}
                        </span>
                        <input
                            id="data_export_orders_invoice_from_date"
                            name="orders_invoice_from_date"
                            type="text"
                            data-hex="true"
                            class="datepicker"
                            value="" />
                    </div>
                </div>
                {*<div class="col-lg-2">
                </div>*}
                <div class="col-lg-4">
                    <div class="input-group datepicker fixed-width-xl">
                        <span class="input-group-addon">
                            <i class="icon-calendar"></i>
                            {l s='Till' mod='ordersexportsalesreportpro'}
                        </span>
                        <input
                            id="data_export_orders_invoice_to_date"
                            name="orders_invoice_to_date"
                            type="text"
                            data-hex="true"
                            class=""
                            value="" />
                    </div>
                </div>
            </div>
        </div>

        <br>

        <div class="form-group date_collapser">
            <label class="control-label col-lg-3">
                <b>{l s='Delivery Date ' mod='ordersexportsalesreportpro'}</b>
            </label>
            <div class="col-lg-9">
                <select id="data_export_orders_delivery_date" name="orders_delivery_date" class="fixed-width-xl">
                    <option value="no_date">-- {l s='All time' mod='ordersexportsalesreportpro'} --</option> 
                    <option value="today">{l s='Today' mod='ordersexportsalesreportpro'}</option> 
                    <option value="last_24_hours">{l s='Last 24 hours' mod='ordersexportsalesreportpro'}</option> 
                    <option value="yesterday">{l s='Yesterday' mod='ordersexportsalesreportpro'}</option>
                    <option value="this_week">{l s='This week' mod='ordersexportsalesreportpro'}</option>
                    <option value="last_week">{l s='Last week' mod='ordersexportsalesreportpro'}</option>
                    <option value="this_month">{l s='This month' mod='ordersexportsalesreportpro'}</option>
                    <option value="last_month">{l s='Last month' mod='ordersexportsalesreportpro'}</option>
                    <option value="select_date">{l s='Select date' mod='ordersexportsalesreportpro'}</option>
                </select>
            </div>
        </div>
        <div class="form-group collapse">
            <label class="control-label col-lg-3">
                {l s='Select a period ' mod='ordersexportsalesreportpro'}
            </label>
            <div class="col-lg-9">
                <div class="col-lg-4">
                    <div class="input-group fixed-width-xl">
                        <span class="input-group-addon">
                            <i class="icon-calendar"></i>
                            {l s='From' mod='ordersexportsalesreportpro'}
                        </span>
                        <input
                            id="data_export_orders_delivery_from_date"
                            name="orders_delivery_from_date"
                            type="text"
                            data-hex="true"
                            class="datepicker"
                            value="" />
                    </div>
                </div>
                {*<div class="col-lg-2">
                </div>*}
                <div class="col-lg-4">
                    <div class="input-group datepicker fixed-width-xl">
                        <span class="input-group-addon">
                            <i class="icon-calendar"></i>
                            {l s='Till' mod='ordersexportsalesreportpro'}
                        </span>
                        <input
                            id="data_export_orders_delivery_to_date"
                            name="orders_delivery_to_date"
                            type="text"
                            data-hex="true"
                            class=""
                            value="" />
                    </div>
                </div>
            </div>
        </div>

        <br>

        <div class="form-group date_collapser">
            <label class="control-label col-lg-3">
                <b>{l s='Payment Date ' mod='ordersexportsalesreportpro'}</b>
            </label>
            <div class="col-lg-9">
                <select id="data_export_orders_payment_date" name="orders_payment_date" class="fixed-width-xl">
                    <option value="no_date">-- {l s='All time' mod='ordersexportsalesreportpro'} --</option> 
                    <option value="today">{l s='Today' mod='ordersexportsalesreportpro'}</option> 
                    <option value="last_24_hours">{l s='Last 24 hours' mod='ordersexportsalesreportpro'}</option> 
                    <option value="yesterday">{l s='Yesterday' mod='ordersexportsalesreportpro'}</option>
                    <option value="this_week">{l s='This week' mod='ordersexportsalesreportpro'}</option>
                    <option value="last_week">{l s='Last week' mod='ordersexportsalesreportpro'}</option>
                    <option value="this_month">{l s='This month' mod='ordersexportsalesreportpro'}</option>
                    <option value="last_month">{l s='Last month' mod='ordersexportsalesreportpro'}</option>
                    <option value="select_date">{l s='Select date' mod='ordersexportsalesreportpro'}</option>
                </select>
            </div>
        </div>
        <div class="form-group collapse">
            <label class="control-label col-lg-3">
                {l s='Select a period ' mod='ordersexportsalesreportpro'}
            </label>
            <div class="col-lg-9">
                <div class="col-lg-4">
                    <div class="input-group fixed-width-xl">
                        <span class="input-group-addon">
                            <i class="icon-calendar"></i>
                            {l s='From' mod='ordersexportsalesreportpro'}
                        </span>
                        <input
                            id="data_export_orders_payment_from_date"
                            name="orders_payment_from_date"
                            type="text"
                            data-hex="true"
                            class="datepicker"
                            value="" />
                    </div>
                </div>
                {*<div class="col-lg-2">
                </div>*}
                <div class="col-lg-4">
                    <div class="input-group datepicker fixed-width-xl">
                        <span class="input-group-addon">
                            <i class="icon-calendar"></i>
                            {l s='Till' mod='ordersexportsalesreportpro'}
                        </span>
                        <input
                            id="data_export_orders_payment_to_date"
                            name="orders_payment_to_date"
                            type="text"
                            data-hex="true"
                            class=""
                            value="" />
                    </div>
                </div>
            </div>
        </div>


        <br>


        <div class="form-group date_collapser">
            <label class="control-label col-lg-3">
                <b>{l s='Shipping Date ' mod='ordersexportsalesreportpro'}</b>
            </label>
            <div class="col-lg-9">
                <select id="data_export_orders_shipping_date" name="orders_shipping_date" class="fixed-width-xl">
                    <option value="no_date">-- {l s='All time' mod='ordersexportsalesreportpro'} --</option> 
                    <option value="today">{l s='Today' mod='ordersexportsalesreportpro'}</option> 
                    <option value="last_24_hours">{l s='Last 24 hours' mod='ordersexportsalesreportpro'}</option> 
                    <option value="yesterday">{l s='Yesterday' mod='ordersexportsalesreportpro'}</option>
                    <option value="this_week">{l s='This week' mod='ordersexportsalesreportpro'}</option>
                    <option value="last_week">{l s='Last week' mod='ordersexportsalesreportpro'}</option>
                    <option value="this_month">{l s='This month' mod='ordersexportsalesreportpro'}</option>
                    <option value="last_month">{l s='Last month' mod='ordersexportsalesreportpro'}</option>
                    <option value="select_date">{l s='Select date' mod='ordersexportsalesreportpro'}</option>
                </select>
            </div>
        </div>
        <div class="form-group collapse">
            <label class="control-label col-lg-3">
                {l s='Select a period ' mod='ordersexportsalesreportpro'}
            </label>
            <div class="col-lg-9">
                <div class="col-lg-4">
                    <div class="input-group fixed-width-xl">
                        <span class="input-group-addon">
                            <i class="icon-calendar"></i>
                            {l s='From' mod='ordersexportsalesreportpro'}
                        </span>
                        <input
                            id="data_export_orders_shipping_from_date"
                            name="orders_shipping_from_date"
                            type="text"
                            data-hex="true"
                            class="datepicker"
                            value="" />
                    </div>
                </div>
                {*<div class="col-lg-2">
                </div>*}
                <div class="col-lg-4">
                    <div class="input-group datepicker fixed-width-xl">
                        <span class="input-group-addon">
                            <i class="icon-calendar"></i>
                            {l s='Till' mod='ordersexportsalesreportpro'}
                        </span>
                        <input
                            id="data_export_orders_shipping_to_date"
                            name="orders_shipping_to_date"
                            type="text"
                            data-hex="true"
                            class=""
                            value="" />
                    </div>
                </div>
            </div>
        </div>

        <br>
        <br>
        <br>
    </div>

    {*    {if $shops|@count gt 1}*}
    {*    <hr>*}
    <h3>
        <i class="icon-home"></i> 
        {l s='Filter By Shop' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i> 
    </h3>

    <div class="collapse">
        <select id="ctrl-show-selected-shops" name="ctrl-show-selected-shops" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_shops" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="shops_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Shop Group' mod='ordersexportsalesreportpro'}</th>
                </tr>
            </thead>
        </table>
        {*    <hr>*}
        <br>
        <br>
        <br>
    </div>

    <h3>
        <i class="icon-users"></i> 
        {l s='Filter By Customer Group' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i>
    </h3>
    {*    <br>*}
    <div class="collapse">
        <div class="form-group">
            <label class="control-label col-lg-3">
                <i>{l s='Without Group' mod='ordersexportsalesreportpro'}</i>
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="orders_group_without" id="data_export_orders_group_yes_without" value="1" checked="checked"/>
                    <label for="data_export_orders_group_yes_without">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                    <input type="radio" name="orders_group_without" id="data_export_orders_group_no_without" value="0"/>
                    <label for="data_export_orders_group_no_without">{l s='No' mod='ordersexportsalesreportpro'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">
                    {l s='Sales without group (if any)' mod='ordersexportsalesreportpro'}
                </p>
            </div>
        </div>
        <select id="ctrl-show-selected-groups" name="ctrl-show-selected-groups" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_groups" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="groups_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Discount' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Members' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Show Prices' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Creation Date' mod='ordersexportsalesreportpro'}</th>
                </tr>
            </thead>
        </table>
        <br>
        <br>
        <br>
    </div>


    <h3>
        <i class="icon-user"></i> 
        {l s='Filter By Customer' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i>
    </h3>
    {*    <br>*}
    <div class="collapse">
        <div class="form-group">
            <label class="control-label col-lg-3">
                <i>{l s='Without Customer' mod='ordersexportsalesreportpro'}</i>
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="orders_customer_without" id="data_export_orders_customer_yes_without" value="1" checked="checked"/>
                    <label for="data_export_orders_customer_yes_without">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                    <input type="radio" name="orders_customer_without" id="data_export_orders_customer_no_without" value="0"/>
                    <label for="data_export_orders_customer_no_without">{l s='No' mod='ordersexportsalesreportpro'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">
                    {l s='Sales without customer (if any)' mod='ordersexportsalesreportpro'}
                </p>
            </div>
        </div>
        <select id="ctrl-show-selected-customers" name="ctrl-show-selected-customers" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_customers" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="customers_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Social Title' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='First Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Last Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Email' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Group' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Status' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Newsletter' mod='ordersexportsalesreportpro'}</th>
                        {*                <th>{l s='Deleted' mod='ordersexportsalesreportpro'}</th>*}
                </tr>
            </thead>
        </table>
        <br>
        <br>
        <br>
    </div>

    <h3>
        <i class="icon-credit-card"></i> 
        {l s='Filter By Order' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i> 
    </h3>
    <div class="collapse">
        <select id="ctrl-show-selected-orders" name="ctrl-show-selected-orders" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_orders" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="orders_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Invoice #' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Reference' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='New Client' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Delivery' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Customer' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Total' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Payment' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Date' mod='ordersexportsalesreportpro'}</th>
                </tr>
            </thead>
        </table>
        <br>
        <br>
        <br>
    </div>

    <h3>
        <i class="icon-cubes"></i> 
        {l s='Filter By Order State' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i> 
    </h3>
    <div class="collapse">
        <select id="ctrl-show-selected-orderStates" name="ctrl-show-selected-orderStates" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_orderStates" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="orderStates_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Icon' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Email Sending' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Delivery' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Invoice' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Email Template' mod='ordersexportsalesreportpro'}</th>
                        {*                <th>{l s='Deleted' mod='ordersexportsalesreportpro'}</th>*}
                </tr>
            </thead>
        </table>
        <br>
        <br>
        <br>
    </div>


    {*<hr>*}
    <h3>
        <i class="icon-cc-amex"></i> 
        {l s='Filter By Payment Method' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i>
    </h3>
    <div class="collapse">
        <select id="ctrl-show-selected-paymentMethods" name="ctrl-show-selected-paymentMethods" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_paymentMethods" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="paymentMethods_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='Logo' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Module Name' mod='ordersexportsalesreportpro'}</th>
                </tr>
            </thead>
        </table>
        <br>
        <br>
        <br>
    </div>

    <h3>
        <i class="icon-level-down"></i> 
        {l s='Filter By Cart Rule' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i>
    </h3>
    <div class="collapse">
        <div class="form-group">
            <label class="control-label col-lg-3">
                <i>{l s='Without Cart Rule' mod='ordersexportsalesreportpro'}</i>
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="orders_cart_rule_without" id="data_export_orders_cart_rule_yes_without" value="1" checked="checked"/>
                    <label for="data_export_orders_cart_rule_yes_without">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                    <input type="radio" name="orders_cart_rule_without" id="data_export_orders_cart_rule_no_without" value="0"/>
                    <label for="data_export_orders_cart_rule_no_without">{l s='No' mod='ordersexportsalesreportpro'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">
                    {l s='Sales without cart rule (if any)' mod='ordersexportsalesreportpro'}
                </p>
            </div>
        </div>
        <select id="ctrl-show-selected-cartRules" name="ctrl-show-selected-cartRules" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_cartRules" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="cartRules_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Priority' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Code' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Quantity' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Expiration Date' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Status' mod='ordersexportsalesreportpro'}</th>
                </tr>
            </thead>
        </table>
        <br>
        <br>
        <br>
    </div>

    <h3>
        <i class="icon-truck"></i> 
        {l s='Filter By Carrier' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i>
    </h3>
    {*    <br>*}
    <div class="collapse">
        <div class="form-group">
            <label class="control-label col-lg-3">
                <i>{l s='Without Carrier' mod='ordersexportsalesreportpro'}</i>
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="orders_carrier_without" id="data_export_orders_carrier_yes_without" value="1" checked="checked"/>
                    <label for="data_export_orders_carrier_yes_without">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                    <input type="radio" name="orders_carrier_without" id="data_export_orders_carrier_no_without" value="0"/>
                    <label for="data_export_orders_carrier_no_without">{l s='No' mod='ordersexportsalesreportpro'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">
                    {l s='Sales without carrier (if any)' mod='ordersexportsalesreportpro'}
                </p>
            </div>
        </div>
        <select id="ctrl-show-selected-carriers" name="ctrl-show-selected-carriers" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_carriers" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="carriers_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Reference' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Logo' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Delay' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Status' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Free Shipping' mod='ordersexportsalesreportpro'}</th>
                </tr>
            </thead>
        </table>
        <br>
        <br>
        <br>
    </div>

    <h3>
        <i class="icon-book"></i> 
        {l s='Filter By Product' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i>
    </h3>
    <div class="collapse">
        <select id="ctrl-show-selected-products" name="ctrl-show-selected-products" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_products" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="products_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Image' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Reference' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Category' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Base Price' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Final Price' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Quantity' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Status' mod='ordersexportsalesreportpro'}</th>
                </tr>
            </thead>
        </table>
        <br>
        <br>
        <br>
    </div>

    <h3>
        <i class="icon-tag"></i> 
        {l s='Filter By Default Category' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i>
    </h3>
    <div class="collapse">
        <div class="form-group">
            <label class="control-label col-lg-3">
                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Since the category filter takes all the selected categories into account, if you create a new setting, then add a new category, the orders containing the products that have this category as default will not be exported with that setting. In this case, you should disable the category filter.' mod='ordersexportsalesreportpro'}">
                    {l s='Enabled' mod='ordersexportsalesreportpro'}
                </span>
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="orders_category_whether_filter" id="data_export_orders_category_yes_whether_filter" value="1" />
                    <label for="data_export_orders_category_yes_whether_filter">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                    <input type="radio" name="orders_category_whether_filter" id="data_export_orders_category_no_whether_filter" value="0" checked="checked" />
                    <label for="data_export_orders_category_no_whether_filter">{l s='No' mod='ordersexportsalesreportpro'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">
                    <i>{l s='If disabled, the default category filter will not be applied!' mod='ordersexportsalesreportpro'}</i>
                </p>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">
                <i>{l s='Without Default Category' mod='ordersexportsalesreportpro'}</i>
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="orders_category_without" id="data_export_orders_category_yes_without" value="1" checked="checked" />
                    <label for="data_export_orders_category_yes_without">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                    <input type="radio" name="orders_category_without" id="data_export_orders_category_no_without" value="0"/>
                    <label for="data_export_orders_category_no_without">{l s='No' mod='ordersexportsalesreportpro'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">
                    {l s='Products without default category (if any)' mod='ordersexportsalesreportpro'}
                </p>
            </div>
        </div>
        {$categories_tree}
        <br>
        <br>
        {*        <br>*}
    </div>

    <h3>
        <i class="icon-bars"></i> 
        {l s='Filter By Attribute' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i>
    </h3>
    {*    <br>*}
    <div class="collapse">
        <div class="form-group">
            <label class="control-label col-lg-3">
                <i>{l s='Without Attribute' mod='ordersexportsalesreportpro'}</i>
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="orders_attribute_without" id="data_export_orders_attribute_yes_without" value="1" checked="checked"/>
                    <label for="data_export_orders_attribute_yes_without">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                    <input type="radio" name="orders_attribute_without" id="data_export_orders_attribute_no_without" value="0"/>
                    <label for="data_export_orders_attribute_no_without">{l s='No' mod='ordersexportsalesreportpro'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">
                    {l s='Sales without product attribute (if any)' mod='ordersexportsalesreportpro'}
                </p>
            </div>
        </div>
        <select id="ctrl-show-selected-attributes" name="ctrl-show-selected-attributes" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_attributes" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="attributes_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Group Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Group Type' mod='ordersexportsalesreportpro'}</th>
                </tr>
            </thead>
        </table>
        <br>
        <br>
        <br>
    </div>

    {*<hr>*}
    <h3>
        <i class="icon-tasks"></i> 
        {l s='Filter By Feature' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i>
    </h3>
    {*    <br>*}
    <div class="collapse">
        <div class="form-group">
            <label class="control-label col-lg-3">
                <i>{l s='Without Feature' mod='ordersexportsalesreportpro'}</i>
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="orders_feature_without" id="data_export_orders_feature_yes_without" value="1" checked="checked"/>
                    <label for="data_export_orders_feature_yes_without">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                    <input type="radio" name="orders_feature_without" id="data_export_orders_feature_no_without" value="0"/>
                    <label for="data_export_orders_feature_no_without">{l s='No' mod='ordersexportsalesreportpro'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">
                    {l s='Sales without product feature (if any)' mod='ordersexportsalesreportpro'}
                </p>
            </div>
        </div>
        <select id="ctrl-show-selected-features" name="ctrl-show-selected-features" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_features" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="features_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Value' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Custom' mod='ordersexportsalesreportpro'}</th>
                </tr>
            </thead>
        </table>
        <br>
        <br>
        <br>
    </div>

    {*<hr>*}
    <h3>
        <i class="icon-certificate"></i> 
        {l s='Filter By Brand' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i>
    </h3>
    {*    <br>*}
    <div class="collapse">
        <div class="form-group">
            <label class="control-label col-lg-3">
                <i>{l s='Without Brand' mod='ordersexportsalesreportpro'}</i>
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="orders_manufacturer_without" id="data_export_orders_manufacturer_yes_without" value="1" checked="checked"/>
                    <label for="data_export_orders_manufacturer_yes_without">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                    <input type="radio" name="orders_manufacturer_without" id="data_export_orders_manufacturer_no_without" value="0"/>
                    <label for="data_export_orders_manufacturer_no_without">{l s='No' mod='ordersexportsalesreportpro'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">
                    {l s='Sales without brand (if any)' mod='ordersexportsalesreportpro'}
                </p>
            </div>
        </div>
        <select id="ctrl-show-selected-manufacturers" name="ctrl-show-selected-manufacturers" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_manufacturers" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="manufacturers_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Logo' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Addresses' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Products' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Status' mod='ordersexportsalesreportpro'}</th>
                </tr>
            </thead>
        </table>
        <br>
        <br>
        <br>
    </div>

    {*<hr>*}
    <h3>
        <i class="icon-circle-o-notch"></i> 
        {l s='Filter By Default Supplier' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i>
    </h3>
    {*    <br>*}
    <div class="collapse">
        <div class="form-group">
            <label class="control-label col-lg-3">
                <i>{l s='Without Supplier' mod='ordersexportsalesreportpro'}</i>
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="orders_supplier_without" id="data_export_orders_supplier_yes_without" value="1" checked="checked"/>
                    <label for="data_export_orders_supplier_yes_without">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                    <input type="radio" name="orders_supplier_without" id="data_export_orders_supplier_no_without" value="0"/>
                    <label for="data_export_orders_supplier_no_without">{l s='No' mod='ordersexportsalesreportpro'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">
                    {l s='Sales without supplier (if any)' mod='ordersexportsalesreportpro'}
                </p>
            </div>
        </div>
        <select id="ctrl-show-selected-suppliers" name="ctrl-show-selected-suppliers" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_suppliers" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="suppliers_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Logo' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Products' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Status' mod='ordersexportsalesreportpro'}</th>
                </tr>
            </thead>
        </table>
        <br>
        <br>
        <br>
    </div>


    <h3>
        <i class="icon-map-marker"></i> 
        {l s='Filter By Delivery Country' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i>
    </h3>
    <div class="collapse">
        <select id="ctrl-show-selected-countries" name="ctrl-show-selected-countries" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_countries" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="countries_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Country' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='ISO Code' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Call prefix' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Zone' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Status' mod='ordersexportsalesreportpro'}</th>
                </tr>
            </thead>
        </table>
        <br>
        <br>
        <br>
    </div>


    {*<hr>*}
    <h3>
        <i class="icon-money"></i> 
        {l s='Filter By Currency' mod='ordersexportsalesreportpro'}
        <i class="icon-chevron-right pull-right"></i>
    </h3>
    <div class="collapse">
        <select id="ctrl-show-selected-currencies" name="ctrl-show-selected-currencies" class="show_selected pull-left">
            <option value="all" selected>{l s='Show all' mod='ordersexportsalesreportpro'}</option>
            <option value="selected">{l s='Show selected' mod='ordersexportsalesreportpro'}</option>
            <option value="not-selected">{l s='Show deselected' mod='ordersexportsalesreportpro'}</option>
        </select>
        <button id="refresh_currencies" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
            {l s='Reload table' mod='ordersexportsalesreportpro'}
        </button>
        <br>
        <table id="currencies_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
            <thead>
                <tr>
                    <th></th>
                    <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Name' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='ISO Code' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Symbol' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Conversion Rate' mod='ordersexportsalesreportpro'}</th>
                    <th>{l s='Status' mod='ordersexportsalesreportpro'}</th>
                        {*                <th>{l s='Deleted' mod='ordersexportsalesreportpro'}</th>*}
                </tr>
            </thead>
        </table>
        <br>
        <br>
        <br>
    </div>
</div>
