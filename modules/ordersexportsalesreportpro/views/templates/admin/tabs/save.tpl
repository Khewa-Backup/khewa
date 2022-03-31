{**
*
* NOTICE OF LICENSE
*
*  @author    IntelliPresta <tehran.alishov@gmail.com>
*  @copyright 2020 IntelliPresta
*  @license   Commercial License
*/
*}

<div id="data_export_orders_save" class="tab-pane">
    <div class="alert alert-info alert-dismissible fade in">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <span>{l s='Here you can save the configurations you set in the previous tabs.' mod='ordersexportsalesreportpro'}</span>
    </div>
    <h3 class="save_header">{l s='Save Selected Settings' mod='ordersexportsalesreportpro'}</h3>
    {*<div id="notification" style="display:none;"></div>*}
    <div class="row">
        <div class="form-group">
            <label class="control-label col-sm-5 col-md-3 required">
                {l s='Name of the settings' mod='ordersexportsalesreportpro'}
            </label>
            <div class=" col-sm-5 col-md-3">
                <input type="text"
                       id="data_export_orders_settings_name"
                       value=""
                       class=""
                       size="33"	
                       required="required" />
            </div>
            <div class="col-sm-2 col-md-1">
                <button class="data_export_form_save_btn btn btn-default">
                    <i class="process-icon-save"></i> {l s='Save' mod='ordersexportsalesreportpro'}
                </button>
            </div>
        </div>
        <hr class="open_hr">
        <h3 class="save_header">{l s='Apply from saved settings' mod='ordersexportsalesreportpro'}</h3>
        <ul id="configs" class="list-group col-xs-8 col-md-6 scrollable">
            {foreach from=$configs item=config}
                <li data-id="{$config.id_orders_export_srpro}" data-name="{$config.name}" data-config='{$config.configuration}' data-datatables='{$config.datatables}' class="list-group-item"><b>{$config.name}</b>
                    <span class="pull-right">
                        <input type="button" class="btn btn-default apply_config" value="{l s='Apply' mod='ordersexportsalesreportpro'}"> | 
                        <i title="{$config.title}" class="icon-trash" style="color:#c50000" aria-hidden="true"></i>
                    </span>
                </li>
            {/foreach}
            {if $configs|@count eq 0}
                <span style="color: #999;"><i class="icon-warning-sign"></i> {l s='No saved setting' mod='ordersexportsalesreportpro'}</span>
            {/if}
        </ul>
        <div class="col-md-6">
            <button id="data_export_orders_reset_settings" class="btn btn-default pull-right"><i class="icon-refresh"></i>
                {l s='Discard Changes' mod='ordersexportsalesreportpro'}
            </button>
        </div>
    </div>
</div>