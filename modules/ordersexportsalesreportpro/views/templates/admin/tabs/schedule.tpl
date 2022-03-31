{**
*
* NOTICE OF LICENSE
*
*  @author    IntelliPresta <tehran.alishov@gmail.com>
*  @copyright 2020 IntelliPresta
*  @license   Commercial License
*/
*}

<div id="data_export_orders_schedule" class="tab-pane">
    {*    <div class="form-group">*}
    {*        <div class="col-lg-9">*}
    <div class="alert alert-info alert-dismissible fade in">
        <p>
            {l s='You can set cron jobs and export your sales any time you want.' mod='ordersexportsalesreportpro'}
            {l s="Make sure the 'curl' library is installed on your server." mod='ordersexportsalesreportpro'}
            {l s='To execute your cron tasks, please insert the following line in your cron tasks manager:' mod='ordersexportsalesreportpro'}

        </p>
        <br>
        <ul class="list-unstyled">
            <li><code>0 0 * * * curl "{$schedule_url}"</code></li>
        </ul>
        <br>
        <strong>{l s='Note: ' mod='ordersexportsalesreportpro'}</strong>
        {l s='If you want to send the export to specific emails among the enabled, append the needed IDs in form of "&email_ids=m,n,l" to the end of the URL, like ' mod='ordersexportsalesreportpro'}
        "{$schedule_url}&email_ids=1,3,4"{l s='. For FTP addresses append it like "&ftp_ids=2,3,5". This is helpful when you need all enabled addresses not to receive export file at the same time.' mod='ordersexportsalesreportpro'}
    </div>
    {*        </div>*}
    {*    </div>*}

    <div class="form-group">
        <label class="control-label col-lg-3">
            {l s='Enable Schedule' mod='ordersexportsalesreportpro'}
        </label>
        <div class="col-lg-9">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="orders_schedule" id="data_export_orders_schedule_yes" value="1" {if $schedule_enabled eq '1'} checked="checked"{/if} />
                <label for="data_export_orders_schedule_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                <input type="radio" name="orders_schedule" id="data_export_orders_schedule_no" value="0" {if $schedule_enabled ne '1'} checked="checked"{/if}  />
                <label for="data_export_orders_schedule_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                <a class="slide-button btn"></a>
            </span>
            <p class="help-block">
            </p>
            <br />
        </div>
    </div>
    <div class="schedule collapse {if $schedule_enabled eq '1'} in {/if}">
        <p class="condensed">{l s='Select export type' mod='ordersexportsalesreportpro'}</p>
        <div class="vomiting">
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='By Email' mod='ordersexportsalesreportpro'}
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="orders_schedule_use_email" id="data_export_orders_schedule_use_email_yes" value="1" {if $schedule_email_enabled eq '1'} checked="checked"{/if} />
                        <label for="data_export_orders_schedule_use_email_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                        <input type="radio" name="orders_schedule_use_email" id="data_export_orders_schedule_use_email_no" value="0" {if $schedule_email_enabled ne '1'} checked="checked"{/if} />
                        <label for="data_export_orders_schedule_use_email_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    {*<p class="help-block">
                    </p>*}
                </div>
            </div>
            <div class="schedule-email collapse {if $schedule_email_enabled eq '1'} in {/if} ">
                <div style="padding:0 30px 30px 30px;">
                    <div style="height:45px;">
                        <button id="refresh_scheduleEmails" class="btn btn-default refresh_button pull-left"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ordersexportsalesreportpro'}
                        </button>
                        <button id="data_export_orders_schedule_add_new_email" data-toggle="modal" data-target="#schedule_email_modal" type="button" class="btn btn-success pull-right"><i class="icon-plus"></i> {l s=' Add' mod='ordersexportsalesreportpro'}</button>
                    </div>
                    <table id="scheduleEmails_table" class="table table-striped table-bordered" style="width: 100%; table-layout: fixed;">
                        <thead>
                            <tr>
                                <th style="width: 1px;"></th>
                                <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                                <th>{l s='Email' mod='ordersexportsalesreportpro'}</th>
                                <th>{l s='Setting' mod='ordersexportsalesreportpro'}</th>
                                <th>{l s='Enabled' mod='ordersexportsalesreportpro'}</th>
                                <th>{l s='Action' mod='ordersexportsalesreportpro'}</th>
                                <th></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <br>
        <div class="vomiting">
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='By FTP' mod='ordersexportsalesreportpro'}
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="orders_schedule_use_ftp" id="data_export_orders_schedule_use_ftp_yes" value="1" {if $schedule_ftp_enabled eq '1'} checked="checked" {/if} />
                        <label for="data_export_orders_schedule_use_ftp_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                        <input type="radio" name="orders_schedule_use_ftp" id="data_export_orders_schedule_use_ftp_no" value="0" {if $schedule_ftp_enabled ne '1'} checked="checked" {/if} />
                        <label for="data_export_orders_schedule_use_ftp_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    {*<p class="help-block">
                    </p>*}
                </div>
            </div>
            <div class="schedule-ftp collapse {if $schedule_ftp_enabled eq '1'} in {/if}">
                <div style="padding:0 30px 30px 30px;">
                    <div style="height:45px;">
                        <button id="refresh_scheduleFTPs" class="btn btn-default refresh_button pull-left"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ordersexportsalesreportpro'}
                        </button>
                        <button id="data_export_orders_schedule_add_new_ftp" data-toggle="modal" data-target="#schedule_ftp_modal" type="button" class="btn btn-success pull-right"><i class="icon-plus"></i> {l s=' Add' mod='ordersexportsalesreportpro'}</button>
                    </div>
                    <table id="scheduleFTPs_table" class="table table-striped table-bordered" style="width: 100%; table-layout: fixed;">
                        <thead>
                            <tr>
                                <th style="width: 1px;"></th>
                                <th>{l s='ID' mod='ordersexportsalesreportpro'}</th>
                                <th>{l s='FTP Type' mod='ordersexportsalesreportpro'}</th>
                                <th>{l s='FTP URL' mod='ordersexportsalesreportpro'}</th>
                                <th>{l s='FTP Username' mod='ordersexportsalesreportpro'}</th>
                                <th>{l s='FTP Password' mod='ordersexportsalesreportpro'}</th>
                                <th>{l s='FTP Folder' mod='ordersexportsalesreportpro'}</th>
                                <th>{l s='Timestamp' mod='ordersexportsalesreportpro'}</th>
                                <th>{l s='Setting' mod='ordersexportsalesreportpro'}</th>
                                <th>{l s='Enabled' mod='ordersexportsalesreportpro'}</th>
                                <th>{l s='Action' mod='ordersexportsalesreportpro'}</th>
                                <th></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <br />
        <br />

        <div class="form-group">
            <label class="control-label col-lg-3">
                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='If the export file does not have any order, the empty file will not be sent to the receiver emails and/or FTP address.' mod='ordersexportsalesreportpro'}">
                    {l s='Do not send if export is empty' mod='ordersexportsalesreportpro'}
                </span>
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="orders_schedule_dont_send_empty" id="data_export_orders_schedule_dont_send_empty_yes" value="1" {if $schedule_dont_send_empty eq '1'} checked="checked" {/if} />
                    <label for="data_export_orders_schedule_dont_send_empty_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                    <input type="radio" name="orders_schedule_dont_send_empty" id="data_export_orders_schedule_dont_send_empty_no" value="0" {if $schedule_dont_send_empty ne '1'} checked="checked" {/if} />
                    <label for="data_export_orders_schedule_dont_send_empty_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                    <a class="slide-button btn"></a>
                </span>
                {*<p class="help-block">
                </p>*}
            </div>
        </div>

    </div>
</div>