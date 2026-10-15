{**
*
* NOTICE OF LICENSE
*
*  @author    IntelliPresta <tehran.alishov@gmail.com>
*  @copyright 2020 IntelliPresta
*  @license   Commercial License
*/
*}

<div id="fader"></div>
<img id='spinner' src='{$module_dir}views/img/spinner.svg' style="display:none" />
{include file='./modals.tpl'}
<div id="data_export">
    <form id="data_export_form" class="defaultForm form-horizontal AdminOrdersExport" action="" method="post" enctype="multipart/form-data" autocomplete="off" novalidate>
        {*        <input id="pdf_blank" type="hidden" name="pdf_blank" value="1" />*}
        <input type="hidden" id="orders_selectedColumns" name="orders_selectedColumns" value="" />
        <div class="panel" id="data_export_orders_panel">
            <div class="panel-heading">
                <i class="icon-bar-chart" style="font-size:13px" aria-hidden="true"></i>
                {l s='Advanced Sales Reports' mod='ordersexportsalesreportpro'}
            </div>
            <div class="panel-body">
                <div class="form-wrapper">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#data_export_orders_general_settings">
                                <i class="icon icon-wrench"></i>
                                {l s='General Settings' mod='ordersexportsalesreportpro'}</a></li>
                        <li><a data-toggle="tab" href="#data_export_orders_filter_data">
                                <i class="icon icon-filter"></i>
                                {l s='Sales Filter' mod='ordersexportsalesreportpro'}</a></li>
                        <li><a data-toggle="tab" href="#data_export_orders_filter_fields">
                                <i class="icon icon-sliders"></i>
                                {l s='Columns Filter' mod='ordersexportsalesreportpro'}</a></li>
                        <li><a data-toggle="tab" href="#data_export_orders_file_options">
                                <i class="icon icon-line-chart"></i>
                                {l s='Sales Summaries' mod='ordersexportsalesreportpro'}</a></li>
                        <li><a data-toggle="tab" href="#data_export_orders_save">
                                <i class="icon icon-save"></i>
                                {l s='Save' mod='ordersexportsalesreportpro'}</a></li>
                        <li><a data-toggle="tab" href="#data_export_orders_autoexport">
                                <i class="icon icon-arrow-circle-o-right"></i>
                                {l s='Auto Export' mod='ordersexportsalesreportpro'}</a></li>
                        <li><a data-toggle="tab" href="#data_export_orders_schedule">
                                <i class="icon icon-clock-o"></i>
                                {l s='Schedule' mod='ordersexportsalesreportpro'}</a></li>
                        <li><a data-toggle="tab" href="#data_export_orders_support">
                                <i class="icon icon-support"></i>
                                {l s='Support' mod='ordersexportsalesreportpro'}</a></li>
                    </ul>
                    <div class="tab-content">
                        {include file='./tabs/general_settings.tpl'}
                        {include file='./tabs/data_filter.tpl'}
                        {include file='./tabs/columns_filter.tpl'}
                        {include file='./tabs/file_options.tpl'}
                        {include file='./tabs/save.tpl'}
                        {include file='./tabs/auto_export.tpl'}
                        {include file='./tabs/schedule.tpl'}
                        {include file='./tabs/support.tpl'}
                    </div>
                </div>
                <!-- /.form-wrapper -->
            </div>
            <div class="panel-footer" id="data_export_submit_panel">
                <div class="text-center">
                    <button type="submit" value="1" id="data_export_form_submit_btn" name="get_orders" class="btn btn-default">
                        <i class="process-icon-export"></i>{l s='Export Sales' mod='ordersexportsalesreportpro'}
                    </button>
                </div>
            </div>
        </div>
        {*<div class="panel col-xs-4 col-xs-push-4" id="data_export_submit_panel">

        </div>*}
    </form>
</div>

<script>
    var controller_link = "{$controller_link}";

    var setGroupsTable = true;
    var setCustomersTable = true;
    var setOrdersTable = true;
    var setOrderStatesTable = true;
    var setPaymentMethodsTable = true;
    var setManufacturersTable = true;
    var setSuppliersTable = true;
    var setAttributesTable = true;
    var setFeaturesTable = true;
    var setCarriersTable = true;
    var setShopsTable = true;
    var setCartRulesTable = true;
    var setCountriesTable = true;
    var setCurrenciesTable = true;
    var setProductsTable = true;

    var edit = "{l s='Edit' mod='ordersexportsalesreportpro'}";
    var delet = "{l s='Remove' mod='ordersexportsalesreportpro'}";
    var default_setting = "-- {l s='Default' mod='ordersexportsalesreportpro'} --";
    var add_email2 = "{l s='add email' mod='ordersexportsalesreportpro'}";
    var sure_to_delete = "{l s='Are you sure to delete?' mod='ordersexportsalesreportpro'}";
    var will_be_changed_to_default = "{l s='Email and FTP addresses with this setting will have the default setting. Are you sure to delete?' mod='ordersexportsalesreportpro'}";
    var add_ftp = "{l s='Add a new FTP address' mod='ordersexportsalesreportpro'}";
    var edit_ftp = "{l s='Edit FTP address' mod='ordersexportsalesreportpro'}";
    var add_email = "{l s='Add a new Email address' mod='ordersexportsalesreportpro'}";
    var edit_email = "{l s='Edit Email address' mod='ordersexportsalesreportpro'}";
    var fill_required_fields = "{l s='Fill required fields.' mod='ordersexportsalesreportpro'}";
    var invalid_numerical_value = "{l s='Invalid numerical value of seconds' mod='ordersexportsalesreportpro'}";
    var invalid_receiver_emails = "{l s='Invalid receiver email(s)' mod='ordersexportsalesreportpro'}";
    var invalid_settings_mame = "{l s='Enter a valid settings name' mod='ordersexportsalesreportpro'}";
    var settings_reset = "{l s='Settings were reset.' mod='ordersexportsalesreportpro'}";
    var settings_applied = "{l s='Settings were applied.' mod='ordersexportsalesreportpro'}";
    var empty_ftp_url = "{l s='FTP URL is empty.' mod='ordersexportsalesreportpro'}";
    var empty_ftp_folder = "{l s='FTP file path is empty.' mod='ordersexportsalesreportpro'}";
    var target_email_ftp = "{l s='The file will be sent to the email(s) and/or FTP you entered.' mod='ordersexportsalesreportpro'}";
    var collapse = "{l s='Collapse' mod='ordersexportsalesreportpro'}";
    var collapse_all = "{l s='Collapse all' mod='ordersexportsalesreportpro'}";
    var expand = "{l s='Expand' mod='ordersexportsalesreportpro'}";
    var expand_all = "{l s='Expand all' mod='ordersexportsalesreportpro'}";
    var show_all = "{l s='Show all' mod='ordersexportsalesreportpro'}";
    var show_selected = "{l s='Show selected' mod='ordersexportsalesreportpro'}";
    var apply = "{l s='Apply' mod='ordersexportsalesreportpro'}";
    var no_saved_setting = "{l s='No saved setting' mod='ordersexportsalesreportpro'}";
    var dtableLoadError = "{l s='Error while loading some data tables. You can reload the unloaded tables manually.' mod='ordersexportsalesreportpro'}";
    var keyColumns = {
        order: "{l s='Order columns' mod='ordersexportsalesreportpro'}",
        product: "{l s='Product columns' mod='ordersexportsalesreportpro'}",
        payment: "{l s='Payment columns' mod='ordersexportsalesreportpro'}",
        customer: "{l s='Customer columns' mod='ordersexportsalesreportpro'}",
        carrier: "{l s='Carrier columns' mod='ordersexportsalesreportpro'}",
        address: "{l s='Address columns' mod='ordersexportsalesreportpro'}",
        shop: "{l s='Shop columns' mod='ordersexportsalesreportpro'}",
        category: "{l s='Category columns' mod='ordersexportsalesreportpro'}",
        manufacturer: "{l s='Brand columns' mod='ordersexportsalesreportpro'}",
        supplier: "{l s='Supplier columns' mod='ordersexportsalesreportpro'}"
    };
    optGroup = {
        order: "{l s='Order' mod='ordersexportsalesreportpro'}",
        product: "{l s='Product' mod='ordersexportsalesreportpro'}",
        payment: "{l s='Payment' mod='ordersexportsalesreportpro'}",
        customer: "{l s='Customer' mod='ordersexportsalesreportpro'}",
        carrier: "{l s='Carrier' mod='ordersexportsalesreportpro'}",
        address: "{l s='Address' mod='ordersexportsalesreportpro'}",
        shop: "{l s='Shop' mod='ordersexportsalesreportpro'}",
        category: "{l s='Category' mod='ordersexportsalesreportpro'}",
        manufacturer: "{l s='Brand' mod='ordersexportsalesreportpro'}",
        supplier: "{l s='Supplier' mod='ordersexportsalesreportpro'}"
    };
</script>
