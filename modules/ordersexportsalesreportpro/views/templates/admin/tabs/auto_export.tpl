{**
*
* NOTICE OF LICENSE
*
*  @author    IntelliPresta <tehran.alishov@gmail.com>
*  @copyright 2020 IntelliPresta
*  @license   Commercial License
*/
*}

<div id="data_export_orders_autoexport" class="tab-pane">
    <div class="alert alert-info alert-dismissible fade in">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {l s='When there is a new order or a status change, you can export the details of that order to emails and/or FTP addresses provided below.' mod='ordersexportsalesreportpro'}
        <br>
        {l s="Make sure the 'curl' library is installed on your server." mod='ordersexportsalesreportpro'}
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3">
            {l s='Enable Auto-Export' mod='ordersexportsalesreportpro'}
        </label>
        <div class="col-lg-9">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="orders_autoexport" id="data_export_orders_autoexport_yes" value="1" {if $autoexport_enabled eq '1'} checked="checked"{/if} />
                <label for="data_export_orders_autoexport_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                <input type="radio" name="orders_autoexport" id="data_export_orders_autoexport_no" value="0" {if $autoexport_enabled ne '1'} checked="checked"{/if}  />
                <label for="data_export_orders_autoexport_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                <a class="slide-button btn"></a>
            </span>
            <p class="help-block">
            </p>
            <br />
        </div>
    </div>
    <div class="auto-export collapse {if $autoexport_enabled eq '1'} in {/if}">
        <p class="condensed">{l s='Export automatically when' mod='ordersexportsalesreportpro'}</p>
        <div class="vomiting">
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <b>{l s='New Order' mod='ordersexportsalesreportpro'}</b>
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" data-id="0" name="orders_autoexport_order_states[0]" id="data_export_orders_autoexport_order_state_yes_0" value="1" {if in_array('0', $autoexport_on_what)} checked="checked"{/if} />
                        <label for="data_export_orders_autoexport_order_state_yes_0">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                        <input type="radio" data-id="0" name="orders_autoexport_order_states[0]" id="data_export_orders_autoexport_order_state_no_0" value="0" {if !in_array('0', $autoexport_on_what)} checked="checked"{/if} />
                        <label for="data_export_orders_autoexport_order_state_no_0">{l s='No' mod='ordersexportsalesreportpro'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    <p class="help-block">
                    </p>
                    <button id="data_export_orders_autoexport_reset" class="pull-right btn btn-default">
                        <i class="process-icon-refresh"></i>&nbsp; {l s='Reset triggers' mod='ordersexportsalesreportpro'}
                    </button>
                </div>
            </div>
            <br>
            {foreach from=$order_states item=order_state}
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label color_field" style="background-color:{$order_state.color};color:{if Tools::getBrightness($order_state.color) < 128}white{else}#383838{/if}">{$order_state.name}</span>
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" data-id="{$order_state.id_order_state}" name="orders_autoexport_order_states[{$order_state.id_order_state}]" id="data_export_orders_autoexport_order_state_yes_{$order_state.id_order_state}" value="1" {if in_array($order_state.id_order_state, $autoexport_on_what)} checked="checked"{/if} />
                            <label for="data_export_orders_autoexport_order_state_yes_{$order_state.id_order_state}">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                            <input type="radio" data-id="{$order_state.id_order_state}" name="orders_autoexport_order_states[{$order_state.id_order_state}]" id="data_export_orders_autoexport_order_state_no_{$order_state.id_order_state}" value="0" {if !in_array($order_state.id_order_state, $autoexport_on_what)} checked="checked"{/if} />
                            <label for="data_export_orders_autoexport_order_state_no_{$order_state.id_order_state}">{l s='No' mod='ordersexportsalesreportpro'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        <p class="help-block">
                        </p>
                    </div>
                </div>
            {/foreach}
        </div>
        <br>
        <br>
        <p class="condensed">{l s='Select export type' mod='ordersexportsalesreportpro'}</p>
        <div class="vomiting">
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='By Email' mod='ordersexportsalesreportpro'}
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="orders_autoexport_use_email" id="data_export_orders_autoexport_use_email_yes" value="1" {if $autoexport_email_enabled eq '1'} checked="checked"{/if} />
                        <label for="data_export_orders_autoexport_use_email_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                        <input type="radio" name="orders_autoexport_use_email" id="data_export_orders_autoexport_use_email_no" value="0" {if $autoexport_email_enabled ne '1'} checked="checked"{/if} />
                        <label for="data_export_orders_autoexport_use_email_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    {*<p class="help-block">
                    </p>*}
                </div>
            </div>
            <div class="auto-export-email collapse {if $autoexport_email_enabled eq '1'} in {/if} ">
                <div style="padding:0 30px 30px 30px;">
                    <div style="height:45px;">
                        <button id="refresh_autoexportEmails" class="btn btn-default refresh_button pull-left"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ordersexportsalesreportpro'}
                        </button>
                        <button id="data_export_orders_autoexport_add_new_email" data-toggle="modal" data-target="#autoexport_email_modal" type="button" class="btn btn-success pull-right"><i class="icon-plus"></i> {l s=' Add' mod='ordersexportsalesreportpro'}</button>
                    </div>
                    <table id="autoexportEmails_table" class="table table-striped table-bordered" style="width: 100%; table-layout: fixed;">
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
                        <input type="radio" name="orders_autoexport_use_ftp" id="data_export_orders_autoexport_use_ftp_yes" value="1" {if $autoexport_ftp_enabled eq '1'} checked="checked" {/if} />
                        <label for="data_export_orders_autoexport_use_ftp_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                        <input type="radio" name="orders_autoexport_use_ftp" id="data_export_orders_autoexport_use_ftp_no" value="0" {if $autoexport_ftp_enabled ne '1'} checked="checked" {/if} />
                        <label for="data_export_orders_autoexport_use_ftp_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    {*<p class="help-block">
                    </p>*}
                </div>
            </div>
            <div class="auto-export-ftp collapse {if $autoexport_ftp_enabled eq '1'} in {/if}">
                <div style="padding:0 30px 30px 30px;">
                    <div style="height:45px;">
                        <button id="refresh_autoexportFTPs" class="btn btn-default refresh_button pull-left"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ordersexportsalesreportpro'}
                        </button>
                        <button id="data_export_orders_autoexport_add_new_ftp" data-toggle="modal" data-target="#autoexport_ftp_modal" type="button" class="btn btn-success pull-right"><i class="icon-plus"></i> {l s=' Add' mod='ordersexportsalesreportpro'}</button>
                    </div>
                    <table id="autoexportFTPs_table" class="table table-striped table-bordered" style="width: 100%; table-layout: fixed;">
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
                    <input type="radio" name="orders_autoexport_dont_send_empty" id="data_export_orders_autoexport_dont_send_empty_yes" value="1" {if $autoexport_dont_send_empty eq '1'} checked="checked" {/if} />
                    <label for="data_export_orders_autoexport_dont_send_empty_yes">{l s='Yes' mod='ordersexportsalesreportpro'}</label>
                    <input type="radio" name="orders_autoexport_dont_send_empty" id="data_export_orders_autoexport_dont_send_empty_no" value="0" {if $autoexport_dont_send_empty ne '1'} checked="checked" {/if} />
                    <label for="data_export_orders_autoexport_dont_send_empty_no">{l s='No' mod='ordersexportsalesreportpro'}</label>
                    <a class="slide-button btn"></a>
                </span>
                {*<p class="help-block">
                </p>*}
            </div>
        </div>
        
    </div>
</div>