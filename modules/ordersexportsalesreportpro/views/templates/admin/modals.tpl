{**
*
* NOTICE OF LICENSE
*
*  @author    IntelliPresta <tehran.alishov@gmail.com>
*  @copyright 2020 IntelliPresta
*  @license   Commercial License
*/
*}

<div class="modal fade" id="emailModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="icon icon-exclamation-circle" style="color:#08c108;" aria-hidden="true"></i> {l s='Note' mod='ordersexportsalesreportpro'}</h4>
            </div>
            <div class="modal-body">
                <p>{l s='Due to large data, it took too long to generate file within the specified time, so the file will be sent to the FTP and/or email(s) you entered.' mod='ordersexportsalesreportpro'}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='ordersexportsalesreportpro'}</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="emailModal2" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="icon icon-exclamation-triangle" style="color:orange;" aria-hidden="true"></i> {l s='Warning' mod='ordersexportsalesreportpro'}</h4>
            </div>
            <div class="modal-body">
                <p>{l s="Request couldn't be sent to server, because time interval was too short." mod='ordersexportsalesreportpro'}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='ordersexportsalesreportpro'}</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="emailModal3" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="icon icon-exclamation-circle" style="color:orange;" aria-hidden="true"></i> {l s='Warning' mod='ordersexportsalesreportpro'}</h4>
            </div>
            <div class="modal-body">
                <p class="error">{l s='An error occurred.' mod='ordersexportsalesreportpro'}</p>
                <br>
                <a class="btn btn-default" target="_blank" href="https://addons.prestashop.com/en/contact-us?id_product=47799">
                    <i class="icon-external-link"></i> {l s='Contact us' mod='ordersexportsalesreportpro'}
                </a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='ordersexportsalesreportpro'}</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="autoexport_ftp_modal" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{l s='Add a new FTP address' mod='ordersexportsalesreportpro'}</h4>
            </div>
            <div class="modal-body form-horizontal">
                <input type="hidden" name="orders_autoexport_ftp_id" id="data_export_orders_autoexport_ftp_id" value="" />
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Enabled' mod='ordersexportsalesreportpro'}
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="orders_autoexport_ftp_active" id="data_export_orders_autoexport_ftp_active_yes" value="1" checked="checked"/>
                            <label for="data_export_orders_autoexport_ftp_active_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                            <input type="radio" name="orders_autoexport_ftp_active" id="data_export_orders_autoexport_ftp_active_no" value="0" />
                            <label for="data_export_orders_autoexport_ftp_active_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        {*<p class="help-block">
                        </p>*}
                    </div>
                </div>
                <br>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='FTP Type' mod='ordersexportsalesreportpro'}
                    </label>
                    <div class="col-lg-9">
                        <select class="fixed-width-xxl" id="data_export_orders_autoexport_ftp_type" name="orders_autoexport_ftp_type">
                            <option value="ftp">{l s='FTP' mod='ordersexportsalesreportpro'}</option>
                            <option value="ftps">{l s='FTPS (Explicit)' mod='ordersexportsalesreportpro'}</option>
                            <option value="sftp">{l s='SFTP' mod='ordersexportsalesreportpro'}</option>
                        </select>
                    </div>
                </div>
                <div class="autoexport_ftp_mode collapse in">
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='In passive mode, data connections are initiated by the client, rather than by the server. It may be needed if the client is behind firewall.' mod='ordersexportsalesreportpro'}">
                                {l s='FTP Mode' mod='ordersexportsalesreportpro'}
                            </span>
                        </label>
                        <div class="col-lg-9">
                            <select class="fixed-width-xxl" id="data_export_orders_autoexport_ftp_mode" name="orders_autoexport_ftp_mode">
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
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-link"></i>
                            </span>
                            <input type="text"
                                   name="orders_autoexport_ftp_url"
                                   id="data_export_orders_autoexport_ftp_url"
                                   value=""
                                   class=""
                                   size="33"	
                                   required="required" />
                        </div>
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
                                   name="orders_autoexport_ftp_port"
                                   id="data_export_orders_autoexport_ftp_port"
                                   value=""
                                   class=""
                                   size="33" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3 required">
                        {l s='FTP Username' mod='ordersexportsalesreportpro'}
                    </label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-user"></i>
                            </span>
                            <input type="text"
                                   name="orders_autoexport_ftp_username"
                                   id="data_export_orders_autoexport_ftp_username"
                                   value=""
                                   class=""
                                   size="33"	
                                   required="required" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3 required">
                        {l s='FTP Password' mod='ordersexportsalesreportpro'}
                    </label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-lock"></i>
                            </span>
                            <input type="text"
                                   name="orders_autoexport_ftp_password"
                                   id="data_export_orders_autoexport_ftp_password"
                                   value=""
                                   class=""
                                   size="33" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='The default folder is "public_ftp" if no name is written here, the file name is always "Order_" followed by the order\'s ID followed by its status combined with "_" among them, and the type is XLSX if no setting selected. e.g. Order_72_New.xlsx or Order_65_Payment Accepted.xlsx' mod='ordersexportsalesreportpro'}">
                            {l s='FTP Folder' mod='ordersexportsalesreportpro'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-folder-open"></i>
                            </span>
                            <input type="text"
                                   name="orders_autoexport_ftp_folder"
                                   id="data_export_orders_autoexport_ftp_folder"
                                   value=""
                                   class=""
                                   size="33" />
                        </div>
                        <p class="help-block">
                            {l s='e.g. public_ftp/sales' mod='ordersexportsalesreportpro'}
                        </p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Adds timestamp in "_Y-m-d_His" format to the end of file name. e.g. Order_72_New_2020-01-14_152852.xlsx' mod='ordersexportsalesreportpro'}">
                            {l s='Add Timestamp' mod='ordersexportsalesreportpro'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="orders_autoexport_ftp_add_ts" id="data_export_orders_autoexport_ftp_add_ts_yes" value="1" />
                            <label for="data_export_orders_autoexport_ftp_add_ts_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                            <input type="radio" name="orders_autoexport_ftp_add_ts" id="data_export_orders_autoexport_ftp_add_ts_no" value="0" checked="checked" />
                            <label for="data_export_orders_autoexport_ftp_add_ts_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        {*<p class="help-block">
                        </p>*}
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Select a setting on which exports should be created.' mod='ordersexportsalesreportpro'}">
                            {l s='Setting' mod='ordersexportsalesreportpro'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <select class="fixed-width-xxl"
                                id="data_export_orders_autoexport_ftp_setting">
                            <option value="orders_default">-- {l s='Default' mod='ordersexportsalesreportpro'} --</option>
                            {foreach from=$configs item=config}
                                <option value="{$config.name}">{$config.name}</option>
                            {/foreach}
                        </select>
                        <p class="help-block">
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="data_export_orders_autoexport_ftp_save" class="pull-right btn btn-default">
                    <i class="process-icon-save"></i> {l s='Save' mod='ordersexportsalesreportpro'}
                </button>
            </div>
        </div>

    </div>
</div>
<div class="modal fade" id="autoexport_email_modal" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{l s='Add a new Email address' mod='ordersexportsalesreportpro'}</h4>
            </div>
            <div class="modal-body form-horizontal">
                <input type="hidden" name="orders_autoexport_email_id" id="data_export_orders_autoexport_email_id" value="" />
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Enabled' mod='ordersexportsalesreportpro'}
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="orders_autoexport_email_active" id="data_export_orders_autoexport_email_active_yes" value="1" checked="checked"/>
                            <label for="data_export_orders_autoexport_email_active_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                            <input type="radio" name="orders_autoexport_email_active" id="data_export_orders_autoexport_email_active_no" value="0" />
                            <label for="data_export_orders_autoexport_email_active_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        {*<p class="help-block">
                        </p>*}
                    </div>
                </div>
                <br>
                <div class="form-group">
                    <label class="control-label col-lg-3 required">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Domain name or IP address' mod='ordersexportsalesreportpro'}">
                            {l s='Email Address' mod='ordersexportsalesreportpro'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-at"></i>
                            </span>
                            <input type="text"
                                   name="orders_autoexport_email_address"
                                   id="data_export_orders_autoexport_email_address"
                                   value=""
                                   class=""
                                   size="33"	
                                   required="required" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Select a setting on which exports should be created.' mod='ordersexportsalesreportpro'}">
                            {l s='Setting' mod='ordersexportsalesreportpro'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <select class="fixed-width-xxl"
                                id="data_export_orders_autoexport_email_setting">
                            <option value="orders_default">-- {l s='Default' mod='ordersexportsalesreportpro'} --</option>
                            {foreach from=$configs item=config}
                                <option value="{$config.name}">{$config.name}</option>
                            {/foreach}
                        </select>
                        <p class="help-block">
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="data_export_orders_autoexport_email_save" class="pull-right btn btn-default">
                    <i class="process-icon-save"></i> {l s='Save' mod='ordersexportsalesreportpro'}
                </button>
            </div>
        </div>

    </div>
</div>
<div class="modal fade" id="schedule_ftp_modal" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{l s='Add a new FTP address' mod='ordersexportsalesreportpro'}</h4>
            </div>
            <div class="modal-body form-horizontal">
                <input type="hidden" name="orders_schedule_ftp_id" id="data_export_orders_schedule_ftp_id" value="" />
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Enabled' mod='ordersexportsalesreportpro'}
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="orders_schedule_ftp_active" id="data_export_orders_schedule_ftp_active_yes" value="1" checked="checked"/>
                            <label for="data_export_orders_schedule_ftp_active_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                            <input type="radio" name="orders_schedule_ftp_active" id="data_export_orders_schedule_ftp_active_no" value="0" />
                            <label for="data_export_orders_schedule_ftp_active_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        {*<p class="help-block">
                        </p>*}
                    </div>
                </div>
                <br>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='FTP Type' mod='ordersexportsalesreportpro'}
                    </label>
                    <div class="col-lg-9">
                        <select class="fixed-width-xxl" id="data_export_orders_schedule_ftp_type" name="orders_schedule_ftp_type">
                            <option value="ftp">{l s='FTP' mod='ordersexportsalesreportpro'}</option>
                            <option value="ftps">{l s='FTPS (Explicit)' mod='ordersexportsalesreportpro'}</option>
                            <option value="sftp">{l s='SFTP' mod='ordersexportsalesreportpro'}</option>
                        </select>
                    </div>
                </div>
                <div class="schedule_ftp_mode collapse in">
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='In passive mode, data connections are initiated by the client, rather than by the server. It may be needed if the client is behind firewall.' mod='ordersexportsalesreportpro'}">
                                {l s='FTP Mode' mod='ordersexportsalesreportpro'}
                            </span>
                        </label>
                        <div class="col-lg-9">
                            <select class="fixed-width-xxl" id="data_export_orders_schedule_ftp_mode" name="orders_schedule_ftp_mode">
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
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-link"></i>
                            </span>
                            <input type="text"
                                   name="orders_schedule_ftp_url"
                                   id="data_export_orders_schedule_ftp_url"
                                   value=""
                                   class=""
                                   size="33"	
                                   required="required" />
                        </div>
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
                                   name="orders_schedule_ftp_port"
                                   id="data_export_orders_schedule_ftp_port"
                                   value=""
                                   class=""
                                   size="33" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3 required">
                        {l s='FTP Username' mod='ordersexportsalesreportpro'}
                    </label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-user"></i>
                            </span>
                            <input type="text"
                                   name="orders_schedule_ftp_username"
                                   id="data_export_orders_schedule_ftp_username"
                                   value=""
                                   class=""
                                   size="33"	
                                   required="required" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3 required">
                        {l s='FTP Password' mod='ordersexportsalesreportpro'}
                    </label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-lock"></i>
                            </span>
                            <input type="text"
                                   name="orders_schedule_ftp_password"
                                   id="data_export_orders_schedule_ftp_password"
                                   value=""
                                   class=""
                                   size="33" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='The default folder is "public_ftp" if no name is written here, the file name is always the name you enter for file, and the type is XLSX if no setting selected. e.g. Sales.xlsx' mod='ordersexportsalesreportpro'}">
                            {l s='FTP Folder' mod='ordersexportsalesreportpro'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-folder-open"></i>
                            </span>
                            <input type="text"
                                   name="orders_schedule_ftp_folder"
                                   id="data_export_orders_schedule_ftp_folder"
                                   value=""
                                   class=""
                                   size="33" />
                        </div>
                        <p class="help-block">
                            {l s='e.g. public_ftp/sales' mod='ordersexportsalesreportpro'}
                        </p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Adds timestamp in "_Y-m-d_His" format to file name to avoid overwriting. e.g. Sales_2020-01-14_152852.xlsx' mod='ordersexportsalesreportpro'}">
                            {l s='Add Timestamp' mod='ordersexportsalesreportpro'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="orders_schedule_ftp_add_ts" id="data_export_orders_schedule_ftp_add_ts_yes" value="1" checked="checked"/>
                            <label for="data_export_orders_schedule_ftp_add_ts_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                            <input type="radio" name="orders_schedule_ftp_add_ts" id="data_export_orders_schedule_ftp_add_ts_no" value="0" />
                            <label for="data_export_orders_schedule_ftp_add_ts_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        {*<p class="help-block">
                        </p>*}
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Select a setting on which exports should be created.' mod='ordersexportsalesreportpro'}">
                            {l s='Setting' mod='ordersexportsalesreportpro'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <select class="fixed-width-xxl"
                                id="data_export_orders_schedule_ftp_setting">
                            <option value="orders_default">-- {l s='Default' mod='ordersexportsalesreportpro'} --</option>
                            {foreach from=$configs item=config}
                                <option value="{$config.name}">{$config.name}</option>
                            {/foreach}
                        </select>
                        <p class="help-block">
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="data_export_orders_schedule_ftp_save" class="pull-right btn btn-default">
                    <i class="process-icon-save"></i> {l s='Save' mod='ordersexportsalesreportpro'}
                </button>
            </div>
        </div>

    </div>
</div>
<div class="modal fade" id="schedule_email_modal" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{l s='Add a new Email address' mod='ordersexportsalesreportpro'}</h4>
            </div>
            <div class="modal-body form-horizontal">
                <input type="hidden" name="orders_schedule_email_id" id="data_export_orders_schedule_email_id" value="" />
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Enabled' mod='ordersexportsalesreportpro'}
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="orders_schedule_email_active" id="data_export_orders_schedule_email_active_yes" value="1" checked="checked"/>
                            <label for="data_export_orders_schedule_email_active_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                            <input type="radio" name="orders_schedule_email_active" id="data_export_orders_schedule_email_active_no" value="0" />
                            <label for="data_export_orders_schedule_email_active_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        {*<p class="help-block">
                        </p>*}
                    </div>
                </div>
                <br>
                <div class="form-group">
                    <label class="control-label col-lg-3 required">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Domain name or IP address' mod='ordersexportsalesreportpro'}">
                            {l s='Email Address' mod='ordersexportsalesreportpro'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-at"></i>
                            </span>
                            <input type="text"
                                   name="orders_schedule_email_address"
                                   id="data_export_orders_schedule_email_address"
                                   value=""
                                   class=""
                                   size="33"	
                                   required="required" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Select a setting on which exports should be created.' mod='ordersexportsalesreportpro'}">
                            {l s='Setting' mod='ordersexportsalesreportpro'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <select class="fixed-width-xxl"
                                id="data_export_orders_schedule_email_setting">
                            <option value="orders_default">-- {l s='Default' mod='ordersexportsalesreportpro'} --</option>
                            {foreach from=$configs item=config}
                                <option value="{$config.name}">{$config.name}</option>
                            {/foreach}
                        </select>
                        <p class="help-block">
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="data_export_orders_schedule_email_save" class="pull-right btn btn-default">
                    <i class="process-icon-save"></i> {l s='Save' mod='ordersexportsalesreportpro'}
                </button>
            </div>
        </div>

    </div>
</div>
