{**

*

* NOTICE OF LICENSE

*

*  @author    IntelliPresta <tehran.alishov@gmail.com>

*  @copyright 2020 IntelliPresta

*  @license   Commercial License

*/

*}



<div id="data_export_orders_file_options" class="tab-pane">

    <div class="alert alert-info alert-dismissible fade in">

        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>

        <span>{l s='Here you can add useful options to see best sellers, top customers etc.' mod='ordersexportsalesreportpro'}</span>

    </div>

    <div class="form-group">
        <label class="control-label col-lg-3">
            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Shows each exported order in details separately.' mod='ordersexportsalesreportpro'}">
                <strong>{l s='Show Orders in Detail' mod='ordersexportsalesreportpro'}</strong>
            </span>
        </label>
        <div class="col-lg-9">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="orders_display_main_sales" id="data_export_orders_display_main_sales_yes" value="1" checked="checked" />
                <label for="data_export_orders_display_main_sales_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                <input type="radio" name="orders_display_main_sales" id="data_export_orders_display_main_sales_no" value="0" />
                <label for="data_export_orders_display_main_sales_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                <a class="slide-button btn"></a>
            </span>
            <p class="help-block">
            </p>
        </div>
    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays the daily sales data.' mod='ordersexportsalesreportpro'}">

                {l s='Show Daily Sales' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_daily_sales" id="data_export_orders_display_daily_sales_yes" value="1" />

                <label for="data_export_orders_display_daily_sales_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_daily_sales" id="data_export_orders_display_daily_sales_no" value="0" checked="checked" />

                <label for="data_export_orders_display_daily_sales_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays the monthly sales data.' mod='ordersexportsalesreportpro'}">

                {l s='Show Monthly Sales' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_monthly_sales" id="data_export_orders_display_monthly_sales_yes" value="1" />

                <label for="data_export_orders_display_monthly_sales_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_monthly_sales" id="data_export_orders_display_monthly_sales_no" value="0" checked="checked" />

                <label for="data_export_orders_display_monthly_sales_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays best-seller products of exported result.' mod='ordersexportsalesreportpro'}">

                {l s='Show Sales by Products' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_bestsellers" id="data_export_orders_display_bestsellers_yes" value="1" />

                <label for="data_export_orders_display_bestsellers_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_bestsellers" id="data_export_orders_display_bestsellers_no" value="0" checked="checked" />

                <label for="data_export_orders_display_bestsellers_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays sales data of the products with their combinations (if exist) of exported result.' mod='ordersexportsalesreportpro'}">

                {l s='Show Sales by Products with Combinations' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_product_combs" id="data_export_orders_display_product_combs_yes" value="1" />

                <label for="data_export_orders_display_product_combs_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_product_combs" id="data_export_orders_display_product_combs_no" value="0" checked="checked" />

                <label for="data_export_orders_display_product_combs_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays top-spending customers of exported result.' mod='ordersexportsalesreportpro'}">

                {l s='Show Sales by Customers' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_top_customers" id="data_export_orders_display_top_customers_yes" value="1" />

                <label for="data_export_orders_display_top_customers_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_top_customers" id="data_export_orders_display_top_customers_no" value="0" checked="checked" />

                <label for="data_export_orders_display_top_customers_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays sales by  payment options.' mod='ordersexportsalesreportpro'}">

                {l s='Show Sales by Payment Methods' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_payment_methods" id="data_export_orders_display_payment_methods_yes" value="1" />

                <label for="data_export_orders_display_payment_methods_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_payment_methods" id="data_export_orders_display_payment_methods_no" value="0" checked="checked" />

                <label for="data_export_orders_display_payment_methods_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>


    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays sales by taxes.' mod='ordersexportsalesreportpro'}">

                {l s='Show Taxes' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_taxes" id="data_export_orders_display_taxes_yes" value="1" />

                <label for="data_export_orders_display_taxes_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_taxes" id="data_export_orders_display_taxes_no" value="0" checked="checked" />

                <label for="data_export_orders_display_taxes_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>


{*
    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays sales by  payment options 2.' mod='ordersexportsalesreportpro'}">

                {l s='Show Sales by Payment Methods 2' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_payment_methods2" id="data_export_orders_display_payment_methods2_yes" value="1" />

                <label for="data_export_orders_display_payment_methods2_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_payment_methods2" id="data_export_orders_display_payment_methods2_no" value="0" checked="checked" />

                <label for="data_export_orders_display_payment_methods2_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

*}

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays categories with sales data.' mod='ordersexportsalesreportpro'}">

                {l s='Show Sales by Categories' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_category_sales" id="data_export_orders_display_category_sales_yes" value="1" />

                <label for="data_export_orders_display_category_sales_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_category_sales" id="data_export_orders_display_category_sales_no" value="0" checked="checked" />

                <label for="data_export_orders_display_category_sales_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays manufacturers with sales data.' mod='ordersexportsalesreportpro'}">

                {l s='Show Sales by Brands' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_manufacturer_sales" id="data_export_orders_display_manufacturer_sales_yes" value="1" />

                <label for="data_export_orders_display_manufacturer_sales_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_manufacturer_sales" id="data_export_orders_display_manufacturer_sales_no" value="0" checked="checked" />

                <label for="data_export_orders_display_manufacturer_sales_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays suppliers with sales data.' mod='ordersexportsalesreportpro'}">

                {l s='Show Sales by Suppliers' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_supplier_sales" id="data_export_orders_display_supplier_sales_yes" value="1" />

                <label for="data_export_orders_display_supplier_sales_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_supplier_sales" id="data_export_orders_display_supplier_sales_no" value="0" checked="checked" />

                <label for="data_export_orders_display_supplier_sales_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays product attributes with sales data.' mod='ordersexportsalesreportpro'}">

                {l s='Show Sales by Attributes' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_attribute_sales" id="data_export_orders_display_attribute_sales_yes" value="1" />

                <label for="data_export_orders_display_attribute_sales_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_attribute_sales" id="data_export_orders_display_attribute_sales_no" value="0" checked="checked" />

                <label for="data_export_orders_display_attribute_sales_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays features with sales data.' mod='ordersexportsalesreportpro'}">

                {l s='Show Sales by Features' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_feature_sales" id="data_export_orders_display_feature_sales_yes" value="1" />

                <label for="data_export_orders_display_feature_sales_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_feature_sales" id="data_export_orders_display_feature_sales_no" value="0" checked="checked" />

                <label for="data_export_orders_display_feature_sales_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays shops with sales data.' mod='ordersexportsalesreportpro'}">

                {l s='Show Sales by Shops' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_shop_sales" id="data_export_orders_display_shop_sales_yes" value="1" />

                <label for="data_export_orders_display_shop_sales_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_shop_sales" id="data_export_orders_display_shop_sales_no" value="0" checked="checked" />

                <label for="data_export_orders_display_shop_sales_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

</div>