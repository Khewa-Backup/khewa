{**

*

* NOTICE OF LICENSE

*

*  @author    IntelliPresta <tehran.alishov@gmail.com>

*  @copyright 2020 IntelliPresta

*  @license   Commercial License

*/

*}



<div id="data_export_orders_general_settings" class="tab-pane in active">

    <div class="alert alert-info alert-dismissible fade in">

        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>

        <span>{l s='Here you define main options of the document you will export.' mod='ordersexportsalesreportpro'}</span>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <b>{l s='Export as ' mod='ordersexportsalesreportpro'}</b>

        </label>

        <div class="col-lg-9 export_as">

            <div class="radio">

                <label><input type="radio" name="orders_export_as" value="excel" checked>{l s='Excel' mod='ordersexportsalesreportpro'}</label>

            </div>

            <div class="radio">

                <label class="p1_5"><input type="radio" name="orders_export_as" value="csv">{l s='CSV' mod='ordersexportsalesreportpro'}</label>

            </div>

            <div class="radio">

                <label class="p1_5"><input type="radio" name="orders_export_as" value="html">{l s='HTML' mod='ordersexportsalesreportpro'}</label>

            </div>

            <div class="radio">

                <label class="p1_5"><input type="radio" name="orders_export_as" value="pdf">{l s='PDF' mod='ordersexportsalesreportpro'}</label>

            </div>

        </div>

    </div>

    <br>

    <div class="form-group">

        <label class="control-label col-lg-3 required">

            {l s='File name' mod='ordersexportsalesreportpro'}

        </label>

        <div class="col-lg-3">

            <div class="input-group fixed-width-xxl">

                <span class="input-group-addon">

                    <i class="icon-file-text"></i>

                </span>

                <input type="text"

                       name="orders_doc_name"

                       id="data_export_orders_doc_name"

                       value="{l s='Sales' mod='ordersexportsalesreportpro'}"

                       class=""

                       size="33"	

                       required="required" />

            </div>

            <p class="help-block">

                {l s='This is a name of the document you will download.' mod='ordersexportsalesreportpro'}

            </p>

        </div>

    </div>

    <div class="form-group">
        <label class="control-label col-lg-3">
            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Adds "Order Creation Date" to the end of the file' mod='ordersexportsalesreportpro'}">
                {l s='Append timestamp to file name' mod='ordersexportsalesreportpro'}
            </span>
        </label>
        <div class="col-lg-3">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="orders_general_add_ts" id="data_export_orders_general_add_ts_yes" value="1" checked="checked" />
                <label for="data_export_orders_general_add_ts_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                <input type="radio" name="orders_general_add_ts" id="data_export_orders_general_add_ts_no" value="0" />
                <label for="data_export_orders_general_add_ts_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                <a class="slide-button btn"></a>
            </span>
            {*<p class="help-block">
            </p>*}
        </div>
    </div>
<br>
    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Specify how you want to get your file' mod='ordersexportsalesreportpro'}">

                {l s='Target action' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <select name="orders_target_action"

                    class="fixed-width-xxl"

                    id="data_export_orders_target_action">

                <option value="download">{l s='Download' mod='ordersexportsalesreportpro'}</option>

                <option value="email">{l s='Email' mod='ordersexportsalesreportpro'}</option>

                <option value="ftp">{l s='FTP' mod='ordersexportsalesreportpro'}</option>

            </select>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="target_action_email collapse">      

        <div class="form-group">

            <label class="control-label col-lg-3">

                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Destination emails that file will be sent' mod='ordersexportsalesreportpro'}">

                    {l s='Send to these email(s)' mod='ordersexportsalesreportpro'}

                </span>

            </label>

            <div class="col-lg-9">

                <div class="input-group corrected_width">

                    <span class="input-group-addon">

                        <i class="icon-envelope-o"></i>

                    </span>

                    <input type="text"

                           id="data_export_orders_target_action_to_emails"

                           name="target_action_to_emails"

                           value=""

                           class=""

                           size="33" />

                </div>

                <p class="help-block">

                    {l s='Type an email and' mod='ordersexportsalesreportpro'}

                    <b style="color:#555;">{l s='hit Enter' mod='ordersexportsalesreportpro'}</b>

                    {l s='or' mod='ordersexportsalesreportpro'}

                    <b style="color:#555;">{l s='Spacebar' mod='ordersexportsalesreportpro'}</b>

            </div>

        </div>

    </div>

    <div class="target_action_ftp collapse">

        <div class="form-group">

            <label class="control-label col-lg-3">

                {l s='FTP Type' mod='ordersexportsalesreportpro'}

            </label>

            <div class="col-lg-9">

                <select class="fixed-width-xxl" id="data_export_orders_target_action_ftp_type" name="orders_target_action_ftp_type">

                    <option value="ftp">{l s='FTP' mod='ordersexportsalesreportpro'}</option>

                    <option value="ftps">{l s='FTPS (Explicit)' mod='ordersexportsalesreportpro'}</option>

                    <option value="sftp">{l s='SFTP' mod='ordersexportsalesreportpro'}</option>

                </select>

            </div>

        </div>

        <div class="target_action_ftp_mode collapse in">

            <div class="form-group">

                <label class="control-label col-lg-3">

                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='In passive mode, data connections are initiated by the client, rather than by the server. It may be needed if the client is behind firewall.' mod='ordersexportsalesreportpro'}">

                        {l s='FTP Mode' mod='ordersexportsalesreportpro'}

                    </span>

                </label>

                <div class="col-lg-9">

                    <select class="fixed-width-xxl" id="data_export_orders_target_action_ftp_mode" name="orders_target_action_ftp_mode">

                        <option value="active">{l s='Active' mod='ordersexportsalesreportpro'}</option>

                        <option value="passive">{l s='Passive' mod='ordersexportsalesreportpro'}</option>

                    </select>

                </div>

            </div>

        </div>

        <div class="form-group">

            <label class="control-label col-lg-3 required">

                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Domain name or IP address' mod='ordersexportsalesreportpro'}">

                    {l s='FTP URL' mod='ordersexportsalesreportpro'}

                </span>

            </label>

            <div class="col-lg-3">

                <div class="input-group fixed-width-xxl">

                    <span class="input-group-addon">

                        <i class="icon-link"></i>

                    </span>

                    <input type="text"

                           name="orders_target_action_ftp_url"

                           id="data_export_orders_target_action_ftp_url"

                           value="{$target_action_ftp_url}"

                           class=""

                           size="33"	

                           required="required" />

                </div>

                {*<p class="help-block">

                {l s='ftp://example.com or sftp://example.com:22' mod='ordersexportsalesreportpro'}

                </p>*}

            </div>

        </div>

        <div class="form-group">

            <label class="control-label col-lg-3">

                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Default FTP port is 21 for FTP, and 22 for SFTP, if no port specified.' mod='ordersexportsalesreportpro'}">

                    {l s='FTP Port' mod='ordersexportsalesreportpro'}

                </span>

            </label>

            <div class="col-lg-3">

                <div class="input-group fixed-width-xxl">

                    <span class="input-group-addon">

                        <i class="icon-circle-thin"></i>

                    </span>

                    <input type="text"

                           name="orders_target_action_ftp_port"

                           id="data_export_orders_target_action_ftp_port"

                           value=""

                           class=""

                           size="33" />

                </div>

            </div>

        </div>

        <div class="form-group">

            <label class="control-label col-lg-3 required">

                {l s='FTP username' mod='ordersexportsalesreportpro'}

            </label>

            <div class="col-lg-3">

                <div class="input-group fixed-width-xxl">

                    <span class="input-group-addon">

                        <i class="icon-user"></i>

                    </span>

                    <input type="text"

                           name="orders_target_action_ftp_username"

                           id="data_export_orders_target_action_ftp_username"

                           value=""

                           class=""

                           size="33"	

                           required="required" />

                </div>

            </div>

        </div>

        <div class="form-group">

            <label class="control-label col-lg-3 required">

                {l s='FTP password' mod='ordersexportsalesreportpro'}

            </label>

            <div class="col-lg-3">

                <div class="input-group fixed-width-xxl">

                    <span class="input-group-addon">

                        <i class="icon-lock"></i>

                    </span>

                    <input type="password"

                           name="orders_target_action_ftp_password"

                           id="data_export_orders_target_action_ftp_password"

                           value=""

                           class=""

                           size="33"	

                           required="required" />

                </div>

            </div>

        </div>

        <div class="form-group">

            <label class="control-label col-lg-3">

                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='The default folder is "public_ftp" if no name is written here, the file name is always the name you enter for file, and the type is XLSX if no setting selected. e.g. Sales.xlsx' mod='ordersexportsalesreportpro'}">

                    {l s='FTP folder' mod='ordersexportsalesreportpro'}

                </span>

            </label>

            <div class="col-lg-3">

                <div class="input-group fixed-width-xxl">

                    <span class="input-group-addon">

                        <i class="icon-folder-open"></i>

                    </span>

                    <input type="text"

                           name="orders_target_action_ftp_folder"

                           id="data_export_orders_target_action_ftp_folder"

                           value=""

                           class=""

                           size="33" />

                    {*<div class="input-group-addon">

                    <input class="file_type_ext" name="orders_target_action_ftp_file_ext" type="hidden" value=".xlsx" />

                    <span class="file_type_text input-group-text">.xlsx</span>

                    </div> *}

                </div>

                <p class="help-block">

                    {l s='e.g. public_ftp/sales' mod='ordersexportsalesreportpro'}

                </p>

            </div>

        </div>

    </div>

    <br>

    <div class="form-group">

        <label class="control-label col-lg-3">

            {l s='Language' mod='ordersexportsalesreportpro'}

        </label>

        <div class="col-lg-3">

            <select name="orders_language"

                    class="{if $languages|@count gt 10} chosen {/if} fixed-width-xxl"

                    id="data_export_orders_language">

                {foreach from=$languages item=language}

                    {if $lang_id neq $language.id_lang}

                        <option value="{$language.id_lang}">{$language.name}</option>

                    {else}

                        <option selected value="{$language.id_lang}">{$language.name}</option>

                    {/if}

                {/foreach}

            </select>

            <p class="help-block">

                {l s='The language of data (Headers may be excluded)' mod='ordersexportsalesreportpro'}

            </p> 

        </div>

    </div>

    <div class="csv_options collapse">

        <div class="form-group">

            <label class="control-label col-lg-3">

                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='e.g. a,b,c or a;b;c or a   b   c' mod='ordersexportsalesreportpro'}">

                    {l s='CSV delimiter' mod='ordersexportsalesreportpro'}

                </span>

            </label>

            <div class="col-lg-9">

                <select name="orders_csv_delimiter"

                        class="fixed-width-xxl"

                        id="data_export_orders_csv_delimiter">

                    <option value=";">; {l s='(semicolon)' mod='ordersexportsalesreportpro'}</option>

                    <option value=",">, {l s='(comma)' mod='ordersexportsalesreportpro'}</option>

                    <option value="t">\t {l s='(tab)' mod='ordersexportsalesreportpro'}</option>

                </select>

                <p class="help-block">

                </p>

            </div>

        </div>

        <div class="form-group">

            <label class="control-label col-lg-3">

                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='e.g. a,b,c or "a","b","c"' mod='ordersexportsalesreportpro'}">

                    {l s='CSV enclosure' mod='ordersexportsalesreportpro'}

                </span>

            </label>

            <div class="col-lg-9">

                <select name="orders_csv_enclosure"

                        class="fixed-width-xxl"

                        id="data_export_orders_csv_enclosure">

                    <option value="quot">"" {l s='(quotation marks)' mod='ordersexportsalesreportpro'}</option>

                    <option value="none">{l s=' (nothing)' mod='ordersexportsalesreportpro'}</option>

                </select>

                <p class="help-block">

                </p>

            </div>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='If there are more than one product in an Order, all rows beloging to each Order are repeated for each of its products, except for Product data itself, of course. (See Columns Filter -> Product -> Product columns). If this option enabled, these same rows are merged. CSV is excluded, because it\'s text-based.' mod='ordersexportsalesreportpro'}">

                {l s='Each order in one line' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_merge_helper" id="data_export_orders_merge_helper" value="1"  checked="checked" />

                <label for="data_export_orders_merge_helper">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_merge_helper" id="data_export_orders_unmerge_helper" value="0"/>

                <label for="data_export_orders_unmerge_helper">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

                {l s='This causes to merge the same rows of an order.' mod='ordersexportsalesreportpro'}

            </p>

            <input type="radio" name="orders_merge" id="data_export_orders_merge" value="1" class="hidden" checked="checked" />

            <input type="radio" name="orders_merge" id="data_export_orders_unmerge" value="0" class="hidden" />

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Merging the same orders and sorting by a product field cannot be realized at the same time, because sorting by product may separate the same order data.' mod='ordersexportsalesreportpro'}">

                {l s='Sort by' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-3">

            <select name="orders_sort" {*multiple*}

                    class="fixed-width-xxl"

                    id="data_export_orders_sort">

            </select>

            <p class="help-block">

                {l s='Select a column' mod='ordersexportsalesreportpro'}

            </p>

        </div>

        <div class="col-lg-5 col-lg-offset-1 data_export_sort_radio">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_sort_asc" id="data_export_orders_sort_asc" value="1"/>

                <label for="data_export_orders_sort_asc">{l s='ASC' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_sort_asc" id="data_export_orders_sort_desc" value="0" checked="checked" />

                <label for="data_export_orders_sort_desc">{l s='DESC' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

                {l s='Ascending or descending' mod='ordersexportsalesreportpro'}

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            {l s='Date format' mod='ordersexportsalesreportpro'}

        </label>

        <div class="col-lg-9">

            <select name="orders_date_format"

                    class="fixed-width-xxl"

                    id="data_export_orders_date_format">

                <option value="Y-m-d">yyyy-mm-dd (e.g. 2018-04-07)&lrm;</option>

                <option value="d/m/Y">dd/mm/yyyy (e.g. 07/04/2018)&lrm;</option>

                <option value="Y/m/d">yyyy/mm/dd (e.g. 2018/04/07)&lrm;</option>

                <option value="m/d/e">mm/dd/yyyy (e.g. 04/07/2018)&lrm;</option>

                <option value="d.m.Y">dd.mm.yyyy (e.g. 07.04.2018)&lrm;</option>

                <option value="Ymd">yyyymmdd (e.g. 20180704)&lrm;</option>

                <option value="e/c/Y">d/m/yyyy (e.g. 7/4/2018)&lrm;</option>

                <option value="c/e/Y">m/d/yyyy (e.g. 4/7/2018)&lrm;</option>

                <option value="e.c.Y">d.m.yyyy (e.g. 7.4.2018)&lrm;</option>

                <option value="e/c/y">d/m/yy (e.g. 7/4/18)&lrm;</option>

                <option value="c/e/Y">m/d/yy (e.g. 4/7/2018)&lrm;</option>

                <option value="e.c.y">d.m.yy (e.g. 7.4.18)&lrm;</option>

                <option value="d b Y">dd mmm yyyy (e.g. 07 Apr 2018)&lrm;</option>

                <option value="e b Y">d mmm yyyy (e.g. 7 Apr 2018)&lrm;</option>

                <option value="e b y">d mmm yy (e.g. 7 Apr 18)&lrm;</option>

                <option value="d M Y">dd mmmm yyyy (e.g. 07 April 2018)&lrm;</option>

                <option value="e M Y">d mmmm yyyy (e.g. 7 April 2018)&lrm;</option>

                <option value="e M y">d mmmm yy (e.g. 7 April 18)&lrm;</option>

                <option value="Ymd">yyyymmdd (e.g. 20180407)&lrm;</option>

            </select>

            <p class="help-block">

                {l s='The date in the sample is the 7th April, 2018.' mod='ordersexportsalesreportpro'}

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            {l s='Time format' mod='ordersexportsalesreportpro'}

        </label>

        <div class="col-lg-9">

            <select name="orders_time_format"

                    class="fixed-width-xxl"

                    id="data_export_orders_time_format">

                <option value="H:i:s">HH:mm:ss (e.g. 09:23:51)&lrm;</option>

                <option value="k:i:s">H:mm:ss (e.g. 9:23:51)&lrm;</option>

                <option value="h:i:s p">hh:mm:ss (e.g. 09:23:51 AM)&lrm;</option>

                <option value="l:i:s p">h:mm:ss (e.g. 9:23:51 AM)&lrm;</option>

                <option value="His">HHmmss (e.g. 092351)&lrm;</option>

                <option value="no_time">-- {l s='No time' mod='ordersexportsalesreportpro'} --&lrm;</option>

            </select>

            <p class="help-block">

                {l s='The time in the sample is 9:23:51 morning.' mod='ordersexportsalesreportpro'}

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Image types in the link' mod='ordersexportsalesreportpro'}">

                {l s='Image type' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <select name="orders_image_type"

                    class="fixed-width-xxl"

                    id="data_export_orders_image_type">

                <option selected value="">{l s='Original' mod='ordersexportsalesreportpro'}</option>

                {foreach from=$image_types item=image_type}

                    <option value="{$image_type.name}">{$image_type.name}</option>

                {/foreach}

            </select>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays the header row of the file.' mod='ordersexportsalesreportpro'}">

                {l s='Display header' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_header" id="data_export_orders_display_header_yes" value="1" checked="checked"/>

                <label for="data_export_orders_display_header_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_header" id="data_export_orders_display_header_no" value="0"/>

                <label for="data_export_orders_display_header_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays the footer of the file as header.' mod='ordersexportsalesreportpro'}">

                {l s='Display footer' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_footer" id="data_export_orders_display_footer_yes" value="1" checked="checked"/>

                <label for="data_export_orders_display_footer_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_footer" id="data_export_orders_display_footer_no" value="0"/>

                <label for="data_export_orders_display_footer_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays the totals of money columns.' mod='ordersexportsalesreportpro'}">

                {l s='Display totals' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_totals" id="data_export_orders_display_totals_yes" value="1" checked="checked"/>

                <label for="data_export_orders_display_totals_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_totals" id="data_export_orders_display_totals_no" value="0"/>

                <label for="data_export_orders_display_totals_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays currency symbol with money amounts, kg, etc.' mod='ordersexportsalesreportpro'}">

                {l s='Display currency symbol, units, etc.' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_currency_symbol" id="data_export_orders_display_currency_symbol_yes" value="1" checked="checked"/>

                <label for="data_export_orders_display_currency_symbol_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_currency_symbol" id="data_export_orders_display_currency_symbol_no" value="0"/>

                <label for="data_export_orders_display_currency_symbol_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Displays gross and net profit columns.' mod='ordersexportsalesreportpro'}">

                {l s='Display explanations' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <span class="switch prestashop-switch fixed-width-lg">

                <input type="radio" name="orders_display_explanations" id="data_export_orders_display_explanations_yes" value="1" checked="checked"/>

                <label for="data_export_orders_display_explanations_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>

                <input type="radio" name="orders_display_explanations" id="data_export_orders_display_explanations_no" value="0"/>

                <label for="data_export_orders_display_explanations_no">{l s='No' mod='ordersexportsalesreportpro'}</label>

                <a class="slide-button btn"></a>

            </span>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Decimal separator for fractional numbers' mod='ordersexportsalesreportpro'}">

                {l s='Decimal symbol' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <select name="orders_decimal_separator"

                    class="fixed-width-xxl"

                    id="data_export_orders_decimal_separator">

                <option value=".">. ({l s='Dot' mod='ordersexportsalesreportpro'})</option>

                <option value=",">, ({l s='Comma' mod='ordersexportsalesreportpro'})</option>

            </select>

            <p class="help-block">

            </p>

        </div>

    </div>

    <div class="form-group">

        <label class="control-label col-lg-3">

            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Specify how many digits you want to see after decimal symbol.' mod='ordersexportsalesreportpro'}">

                {l s='Fractional part' mod='ordersexportsalesreportpro'}

            </span>

        </label>

        <div class="col-lg-9">

            <select name="orders_round"

                    class="fixed-width-xxl"

                    id="data_export_orders_round">

                <option value="0">{l s='0 digit' mod='ordersexportsalesreportpro'}</option>

                <option value="1">{l s='1 digit' mod='ordersexportsalesreportpro'}</option>

                <option selected value="2">{l s='2 digits' mod='ordersexportsalesreportpro'}</option>

                <option value="3">{l s='3 digits' mod='ordersexportsalesreportpro'}</option>

                <option value="4">{l s='4 digits' mod='ordersexportsalesreportpro'}</option>
                <option value="5">{l s='5 digits' mod='ordersexportsalesreportpro'}</option>
                <option value="6">{l s='6 digits' mod='ordersexportsalesreportpro'}</option>

            </select>

            <p class="help-block">

            </p>

        </div>

    </div>

</div>

