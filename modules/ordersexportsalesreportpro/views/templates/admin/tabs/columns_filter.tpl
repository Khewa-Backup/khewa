{**
*
* NOTICE OF LICENSE
*
*  @author    IntelliPresta <tehran.alishov@gmail.com>
*  @copyright 2020 IntelliPresta
*  @license   Commercial License
*/
*}

<div id="data_export_orders_filter_fields" class="tab-pane data_export_filter_fields">
    <div class="alert alert-info alert-dismissible fade in order">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {l s='Here you define the columns of an order.' mod='ordersexportsalesreportpro'}
    </div>
    <div class="alert alert-info alert-dismissible fade in product hidden">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {l s='Here you define the columns of the products in an order.' mod='ordersexportsalesreportpro'}
    </div>
    <div class="alert alert-info alert-dismissible fade in payment hidden">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {l s='Here you define the columns of the payment of an order.' mod='ordersexportsalesreportpro'}
    </div>
    <div class="alert alert-info alert-dismissible fade in customer hidden">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {l s='Here you define the columns of the customer who has placed an order.' mod='ordersexportsalesreportpro'}
    </div>
    <div class="alert alert-info alert-dismissible fade in carrier hidden">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {l s='Here you define the columns of the carrier of an order.' mod='ordersexportsalesreportpro'}
    </div>
    <div class="alert alert-info alert-dismissible fade in address hidden">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {l s='Here you define the columns of the shipping or the invoice address of an order.' mod='ordersexportsalesreportpro'}
    </div>
    <div class="alert alert-info alert-dismissible fade in shop hidden">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {l s='Here you define the columns of the shop where an order has been placed.' mod='ordersexportsalesreportpro'}
    </div>
    <div class="row">
        <div class="list-group col-xs-4 col-md-3">
            <fieldset>
                <legend>{l s='Select a group to filter its fields:' mod='ordersexportsalesreportpro'}&nbsp;</legend>
                <div id="orders_columns_group" class="columns_group">
                    <a href="#" id="orders_filter_fields_order" class="list-group-item list-group-item-action active">{l s='Order' mod='ordersexportsalesreportpro'}
                        <span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span>
                        <span class="badge">0</span>
                    </a>
                    <a href="#" id="orders_filter_fields_product" class="list-group-item list-group-item-action">{l s='Product' mod='ordersexportsalesreportpro'}
                        <span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span>
                        <span class="badge">0</span>
                    </a>
                    <a href="#" id="orders_filter_fields_category" class="list-group-item list-group-item-action">{l s='Category' mod='ordersexportsalesreportpro'}
                        <span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span>
                        <span class="badge">0</span>
                    </a>
                    <a href="#" id="orders_filter_fields_manufacturer" class="list-group-item list-group-item-action">{l s='Brand' mod='ordersexportsalesreportpro'}
                        <span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span>
                        <span class="badge">0</span>
                    </a>
                    <a href="#" id="orders_filter_fields_supplier" class="list-group-item list-group-item-action">{l s='Supplier' mod='ordersexportsalesreportpro'}
                        <span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span>
                        <span class="badge">0</span>
                    </a>
                    <a href="#" id="orders_filter_fields_payment" class="list-group-item list-group-item-action">{l s='Payment' mod='ordersexportsalesreportpro'}
                        <span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span>
                        <span class="badge">0</span>
                    </a>
                    <a href="#" id="orders_filter_fields_customer" class="list-group-item list-group-item-action">{l s='Customer' mod='ordersexportsalesreportpro'}
                        <span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span>
                        <span class="badge">0</span>
                    </a>
                    <a href="#" id="orders_filter_fields_carrier" class="list-group-item list-group-item-action">{l s='Carrier' mod='ordersexportsalesreportpro'}
                        <span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span>
                        <span class="badge">0</span>
                    </a>
                    <a href="#" id="orders_filter_fields_address" class="list-group-item list-group-item-action">{l s='Address' mod='ordersexportsalesreportpro'}
                        <span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span>
                        <span class="badge">0</span>
                    </a>
                    <a href="#" id="orders_filter_fields_shop" class="list-group-item list-group-item-action">{l s='Shop' mod='ordersexportsalesreportpro'}
                        <span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span>
                        <span class="badge">0</span>
                    </a>
                </div>
            </fieldset>
        </div>
        <fieldset class="col-xs-7 pull-right">
            <legend>{l s='Order columns:' mod='ordersexportsalesreportpro'}&nbsp;</legend>
            <div class="row" id="columns_header">
                <button id="data_export_orders_select_all_columns" class="btn btn-default data_export_select_all_columns column_button"><i class="icon-check-square-o"></i> {l s='Select all' mod='ordersexportsalesreportpro'}</button>&nbsp;&nbsp;
                <button id="data_export_orders_reset_columns" class="btn btn-default data_export_reset_columns  column_button" href="#" data-value="0">{l s='Reset' mod='ordersexportsalesreportpro'}</button>&nbsp;&nbsp;
                <button id="data_export_orders_show_all" class="btn btn-default data_export_show_all  column_button" href="#" data-value="1">{l s='Show selected' mod='ordersexportsalesreportpro'}</button>&nbsp;&nbsp;
                <button id="data_export_orders_expand_all" class="btn btn-default data_export_expand_columns  column_button" href="#" data-value="0"><span>{l s='Expand' mod='ordersexportsalesreportpro'}</span> <i class="icon-angle-right"></i> </button>
                <span class="clearable pull-right">
                    <input id="columns_search" type="search" placeholder="{l s='Search...' mod='ordersexportsalesreportpro'}" />
                    <i class="clearable_clear">&times;</i>
                </span>
            </div>
            <div id="data_export_orders_scroll_columns" class="overflowable">
                <ul id="data_export_orders_order_columns" class="list-group item-list data_export_columns">
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="id_order"></i> {l s='Order ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="reference"></i> {l s='Order Reference' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-key="order" data-value="new_client"></i> {l s='New Client' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="id_cart"></i> {l s='Cart ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="id_lang"></i> {l s='Language ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="lang.name"></i> {l s='Language' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="current_state"></i> {l s='Current State ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_state.name"></i> {l s='Current State' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item absent"><i class="icon-check-square-o" data-value="order_state_history.order_history"></i> {l s='States History' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="secure_key"></i> {l s='Secure Key' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="payment"></i> {l s='Payment Method' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="id_currency"></i> {l s='Currency ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced absent"><i class="icon-square-o" data-value="currency.name"></i> {l s='Currency Name' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="currency.iso_code"></i> {l s='Currency Code' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="currency.conversion_rate"></i> {l s='Currency Conversion Rate' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="conversion_rate"></i> {l s='Conversion Rate' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="module"></i> {l s='Module' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_messages.message"></i> {l s='Messages' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="recyclable"></i> {l s='Recyclable' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="shipping_number"></i> {l s='Shipping Number' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="gift"></i> {l s='Gift' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="gift_message"></i> {l s='Gift Message' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="total_discounts"></i> {l s='Total Discounts' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="total_discounts_tax_incl"></i> {l s='Total Discounts (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="total_discounts_tax_excl"></i> {l s='Total Discounts (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="total_paid"></i> {l s='Total Paid' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-square-o" data-value="total_paid_base_curr"></i> {l s='Total Paid with Base Currency' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="total_paid_tax_incl"></i> {l s='Total Paid (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="total_paid_tax_excl"></i> {l s='Total Paid (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="total_paid_real"></i> {l s='Total Really Paid' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="total_products"></i> {l s='Total Products' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="total_products_wt"></i> {l s='Total Products With Tax' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="profit_amount"></i> {l s='Gross Profit Amount' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="profit_margin"></i> {l s='Gross Profit Margin' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="profit_percentage"></i> {l s='Gross Profit Percentage' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="net_profit_amount"></i> {l s='Net Profit Amount' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="net_profit_margin"></i> {l s='Net Profit Margin' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="net_profit_percentage"></i> {l s='Net Profit Percentage' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="cart_rule.id_cart_rule"></i> {l s='Cart Rule ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="cart_rule.name"></i> {l s='Cart Rule Name' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="cart_rule.value"></i> {l s='Cart Rule Value' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="cart_rule.value_tax_excl"></i> {l s='Cart Rule Value (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="cart_rule.free_shipping"></i> {l s='Free Shipping' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="total_shipping"></i> {l s='Total Shipping' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="total_shipping_tax_incl"></i> {l s='Total Shipping (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="total_shipping_tax_excl"></i> {l s='Total Shipping (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="canada_shipping_tax.amount"></i> {l s='Shipping Tax (CA 5%)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="quebec_shipping_tax.amount"></i> {l s='Shipping Tax (CA-QC 9.975%)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="carrier_tax_rate"></i> {l s='Carrier Tax Rate' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="total_wrapping"></i> {l s='Total Wrapping' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="total_wrapping_tax_incl"></i> {l s='Total Wrapping (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="total_Wrapping_tax_excl"></i> {l s='Total Wrapping (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_slip.total_products_tax_excl"></i> {l s='Total Refunded Products (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_slip.total_products_tax_incl"></i> {l s='Total Refunded Products (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_slip.total_shipping_tax_excl"></i> {l s='Total Refunded Shipping (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_slip.total_shipping_tax_incl"></i> {l s='Total Refunded Shipping (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_slip.amount"></i> {l s='Refunded Amount' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_slip.shipping_cost_amount"></i> {l s='Refunded Shipping Cost Amount' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="rock_total_paid_tax_excl"></i> {l s='Total Refunds ROCK (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="rock_total_paid_tax_incl"></i> {l s='Total Refunds ROCK (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="rock_total_shipping_tax_excl"></i> {l s='Total Refunded Shipping ROCK (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="rock_total_shipping_tax_incl"></i> {l s='Total Refunded Shipping ROCK (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="invoice_number"></i> {l s='Invoice Number' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="delivery_number"></i> {l s='Delivery Number' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="invoice_date"></i> {l s='Invoice Date' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="delivery_date"></i> {l s='Delivery Date' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="valid"></i> {l s='Valid' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="date_add"></i> {l s='Ordered At' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="date_upd"></i> {l s='Order Update Date' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                </ul>
                <ul id="data_export_orders_product_columns" class="list-group item-list data_export_columns hidden">
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="product_id"></i> {l s='Product ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="prod.reference"></i> {l s='Product Reference' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_detail_lang.product_name"></i> {l s='Product Name' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="order_detail_lang.description_short"></i> {l s='Product Short Description' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="order_detail_lang.description"></i> {l s='Product Long Description' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item absent"><i class="icon-check-square-o" data-value="product_link"></i> {l s='Product Link' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item absent"><i class="icon-check-square-o" data-value="product_image"></i> {l s='Product Image' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="product_image_link"></i> {l s='Product Image Link' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="prod_customs.customs"></i> {l s='Product Customizations' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item absent"><i class="icon-check-square-o" data-value="product_name"></i> {l s='Product Details' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="product_attribute_id"></i> {l s='Combination ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_detail_lang.attributes"></i> {l s='Combination' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item absent"><i class="icon-check-square-o" data-value="attribute_image"></i> {l s='Combination Image' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced absent"><i class="icon-square-o" data-value="attribute_image_link"></i> {l s='Combination Image Link' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="combination.reference"></i> {l s='Combination Reference' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="combination.ean13"></i> {l s='Combination EAN-13' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="combination.upc"></i> {l s='Combination UPC' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="attrib_customs.customs"></i> {l s='Combination Customizations' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="product_features.features"></i> {l s='Product Features' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="product_quantity"></i> {l s='Product Quantity' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="order_detail_lang.product_link_rewrite"></i> {l s='Product Link Rewrite' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="product_quantity_in_stock"></i> {l s='Product Quantity In Stock' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="product_quantity_refunded"></i> {l s='Product Quantity Refunded' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="product_quantity_return"></i> {l s='Product Quantity Returned' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="product_quantity_reinjected"></i> {l s='Product Quantity Reinjected' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="purchase_supplier_price"></i> {l s='Purchase Price from Supplier' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="product_price"></i> {l s='Product Price' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="total_price_tax_incl"></i> {l s='Total Price (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="total_price_tax_excl"></i> {l s='Total Price (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="unit_price_tax_incl"></i> {l s='Unit Price (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="unit_price_tax_excl"></i> {l s='Unit Price (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="canada_tax.unit_amount"></i> {l s='Unit Amount (CA 5%)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="canada_tax.total_amount"></i> {l s='Total Amount (CA 5%)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="quebec_tax.unit_amount"></i> {l s='Unit Amount (CA-QC 9.975%)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="quebec_tax.total_amount"></i> {l s='Total Amount (CA-QC 9.975%)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="total_shipping_price_tax_incl"></i> {l s='Total Shipping Price (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="total_shipping_price_tax_excl"></i> {l s='Total Shipping Price (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_slip_detail.product_quantity"></i> {l s='Refunded Product Quantity' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_slip_detail.unit_price_tax_excl"></i> {l s='Refunded Unit Price (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_slip_detail.unit_price_tax_incl"></i> {l s='Refunded Unit Price (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_slip_detail.total_price_tax_excl"></i> {l s='Refunded Total Price (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_slip_detail.total_price_tax_incl"></i> {l s='Refunded Total Price (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_slip_detail.amount_tax_excl"></i> {l s='Refunded Amount (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_slip_detail.amount_tax_incl"></i> {l s='Refunded Amount (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="original_product_price"></i> {l s='Original Product Price' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                        {if $show_orijinal_wholesale_price gt -1}
                        <li class="list-group-item advanced"><i class="icon-square-o" data-value="original_wholesale_price"></i> {l s='Original Wholesale Price' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                        {/if}
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="reduction_percent"></i> {l s='Reduction Percent' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="reduction_amount"></i> {l s='Reduction Amount' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="reduction_amount_tax_incl"></i> {l s='Reduction Amount (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="reduction_amount_tax_excl"></i> {l s='Reduction Amount (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="group_reduction"></i> {l s='Customer Group Reduction' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="product_quantity_discount"></i> {l s='Product Quantity Discount' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="product_ean13"></i> {l s='Product EAN-13' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                        {if $show_isbn gt -1}
                        <li class="list-group-item advanced"><i class="icon-square-o" data-value="product_isbn"></i> {l s='Product ISBN' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                        {/if}
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="product_upc"></i> {l s='Product UPC' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="product_reference"></i> {l s='Saletime Product Reference' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="prod.supplier_reference"></i> {l s='Product Supplier Reference' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="product_supplier_reference"></i> {l s='Saletime Product Supplier Reference' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="product_weight"></i> {l s='Product Weight' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                        {if $show_id_tax_rule_group gt -1}
                        <li class="list-group-item advanced"><i class="icon-square-o" data-value="id_tax_rules_group"></i> {l s='Tax Rules Group ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                        <li class="list-group-item"><i class="icon-check-square-o" data-value="tax_rules_group.name"></i> {l s='Tax Rules Group Name' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                        {/if}
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="tax_computation_method"></i> {l s='Tax Computation Method' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="order_details_tax.id_tax"></i> {l s='Tax ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="tax_name"></i> {l s='Tax Name (for older versions)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="tax_rate"></i> {l s='Tax Rate (for older versions)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_details_tax.name"></i> {l s='Tax Name' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_details_tax.rate"></i> {l s='Tax Rate' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_details_tax.unit_amount_tax"></i> {l s='Tax of Unit Amount' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="order_details_tax.total_amount_tax"></i> {l s='Tax of Total Amount' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="ecotax"></i> {l s='Ecotax' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="ecotax_tax_rate"></i> {l s='Ecotax Tax Rate' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="discount_quantity_applied"></i> {l s='Discount Quantity Applied' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="download_hash"></i> {l s='Download Hash' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="download_nb"></i> {l s='Number of Downloads' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="download_deadline"></i> {l s='Download Deadline' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                </ul>
                <ul id="data_export_orders_category_columns" class="list-group item-list data_export_columns hidden">
                    <li class="list-group-item"><i class="icon-square-o" data-value="cat.ids"></i> {l s='Category IDs' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="cat.names"></i> {l s='Category Names' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-square-o" data-value="id_category"></i> {l s='Default Category ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="category.name"></i> {l s='Default Category Name' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="description"></i> {l s='Default Category Description' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="link_rewrite"></i> {l s='Default Category Rewrite Link' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="category_link"></i> {l s='Default Category Link' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="category_image"></i> {l s='Default Category Image' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="category_image_link"></i> {l s='Default Category Image Link' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                </ul>
                <ul id="data_export_orders_manufacturer_columns" class="list-group item-list data_export_columns hidden">
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="id_manufacturer"></i> {l s='Brand ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="manufacturer.name"></i> {l s='Brand Name' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="manufacturer_link"></i> {l s='Brand Link' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="manufacturer_image"></i> {l s='Brand Image' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="manufacturer_image_link"></i> {l s='Brand Image Link' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                </ul>
                <ul id="data_export_orders_supplier_columns" class="list-group item-list data_export_columns hidden">
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="id_supplier"></i> {l s='Supplier ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="supplier.name"></i> {l s='Supplier Name' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="supplier_link"></i> {l s='Supplier Link' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="supplier_image"></i> {l s='Supplier Image' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="supplier_image_link"></i> {l s='Supplier Image Link' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                </ul>
                <ul id="data_export_orders_payment_columns" class="list-group item-list data_export_columns hidden">
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="id_order_payment"></i> {l s='Payment ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="id_currency"></i> {l s='Payment Currency ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced absent"><i class="icon-square-o" data-value="currency_name"></i> {l s='Payment Currency Name' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="currency_code"></i> {l s='Payment Currency Code' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="amount"></i> {l s='Total Payment Amount' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="payment_details"></i> {l s='Payment Details' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="payment_dt_add"></i> {l s='Payment Date' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="payment_methods"></i> {l s='Payment Methods' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="transaction_id"></i> {l s='Transaction ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                        {*<li class="list-group-item advanced"><i class="icon-square-o" data-value="card_number"></i> {l s='Card Number' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                        <li class="list-group-item advanced"><i class="icon-square-o" data-value="card_brand"></i> {l s='Card Brand' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                        <li class="list-group-item advanced"><i class="icon-square-o" data-value="card_expiration"></i> {l s='Card Expiration Date' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                        <li class="list-group-item advanced"><i class="icon-square-o" data-value="card_holder"></i> {l s='Card Holder' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                </ul>
                <ul id="data_export_orders_customer_columns" class="list-group item-list data_export_columns hidden">
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="id_customer"></i> {l s='Customer ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="email"></i> {l s='Customer Email' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="firstname"></i> {l s='Customer Firstname' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="lastname"></i> {l s='Customer Lastname' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="company"></i> {l s='Customer Company' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="def_group.id_group"></i> {l s='Default Group ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="def_group.name"></i> {l s='Default Group Name' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="groupp.group_ids"></i> {l s='Group IDs' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="groupp.group_names"></i> {l s='Group Names' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="gender_lang.name"></i> {l s='Customer Gender' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="birthday"></i> {l s='Customer Birthday' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="date_add"></i> {l s='Customer Registration Date' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="newsletter_date_add"></i> {l s='Date Subscribed to Newsletter' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                </ul>
                <ul id="data_export_orders_carrier_columns" class="list-group item-list data_export_columns hidden">
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="id_carrier"></i> {l s='Carrier ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="carrier_name.name"></i> {l s='Carrier Name' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="weight"></i> {l s='Weight (total)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="tracking_number"></i> {l s='Tracking Number' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="shipping_cost_tax_excl"></i> {l s='Carrier Cost (Tax excluded)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="shipping_cost_tax_incl"></i> {l s='Carrier Cost (Tax included)' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="shipping_cost_tax_amount"></i> {l s='Carrier Tax Amount' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="date_add"></i> {l s='Carrier Creation Date' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                </ul>
                <ul id="data_export_orders_address_columns" class="list-group item-list data_export_columns hidden">
                    <li class="list-group-item advanced absent"><i class="icon-square-o" data-value="order.id_address_invoice"></i> {l s='Invoice Address ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="address_invoice.firstname"></i> {l s='Customer Firstname of Invoice Address' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="address_invoice.lastname"></i> {l s='Customer Lastname of Invoice Address' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item absent "><i class="icon-check-square-o" data-value="address_invoice.alias"></i> {l s='Invoice Address Alias' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced absent"><i class="icon-square-o" data-value="invoice_country_lang.name"></i> {l s='Invoice Address Country' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced absent"><i class="icon-square-o" data-value="invoice_country.iso_code"></i> {l s='Invoice Address Country ISO code' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced absent"><i class="icon-square-o" data-value="invoice_state.name"></i> {l s='Invoice Address State' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item "><i class="icon-check-square-o" data-value="address_invoice.city"></i> {l s='Invoice Address City' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item "><i class="icon-check-square-o" data-value="address_invoice.address1"></i> {l s='Invoice Address 1' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item "><i class="icon-check-square-o" data-value="address_invoice.address2"></i> {l s='Invoice Address 2' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item "><i class="icon-check-square-o" data-value="address_invoice.postcode"></i> {l s='Invoice Address Postcode' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced "><i class="icon-square-o" data-value="address_invoice.phone"></i> {l s='Invoice Address Phone' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced "><i class="icon-square-o" data-value="address_invoice.phone_mobile"></i> {l s='Invoice Address Mobile Phone' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced "><i class="icon-square-o" data-value="address_invoice.company"></i> {l s='Invoice Address Company' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced "><i class="icon-square-o" data-value="address_invoice.vat_number"></i> {l s='Invoice Address VAT Number' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced absent"><i class="icon-square-o" data-value="order.id_address_delivery"></i> {l s='Delivery Address ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="address_delivery.firstname"></i> {l s='Customer Firstname of Delivery Address' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="address_delivery.lastname"></i> {l s='Customer Lastname of Delivery Address' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item "><i class="icon-check-square-o" data-value="address_delivery.alias"></i> {l s='Delivery Address Alias' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced absent"><i class="icon-square-o" data-value="delivery_country_lang.name"></i> {l s='Delivery Country' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced absent"><i class="icon-square-o" data-value="delivery_country.iso_code"></i> {l s='Delivery Country ISO Code' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced absent"><i class="icon-square-o" data-value="delivery_state.name"></i> {l s='Delivery State' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item "><i class="icon-check-square-o" data-value="address_delivery.city"></i> {l s='Delivery City' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item "><i class="icon-check-square-o" data-value="address_delivery.address1"></i> {l s='Delivery Address 1' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item "><i class="icon-check-square-o" data-value="address_delivery.address2"></i> {l s='Delivery Address 2' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item "><i class="icon-check-square-o" data-value="address_delivery.postcode"></i> {l s='Delivery Address Postcode' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced "><i class="icon-square-o" data-value="address_delivery.phone"></i> {l s='Delivery Address Phone' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced "><i class="icon-square-o" data-value="address_delivery.phone_mobile"></i> {l s='Delivery Address Mobile Phone' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced "><i class="icon-square-o" data-value="address_delivery.company"></i> {l s='Delivery Address Company' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced "><i class="icon-square-o" data-value="address_delivery.vat_number"></i> {l s='Delivery Address VAT Number' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                </ul>
                <ul id="data_export_orders_shop_columns" class="list-group item-list data_export_columns hidden">
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="id_shop"></i> {l s='Shop ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item"><i class="icon-check-square-o" data-value="shop.name"></i> {l s='Shop Name' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced"><i class="icon-square-o" data-value="id_shop_group"></i> {l s='Shop Group ID' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                    <li class="list-group-item advanced absent"><i class="icon-square-o" data-value="shop_group.name"></i> {l s='Shop Group Name' mod='ordersexportsalesreportpro'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                </ul>
            </div>
        </fieldset>   
    </div>
</div>
