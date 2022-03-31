{*
* This file is part of the 'WK Inventory' module feature.
* Developped by Khoufi Wissem (2017).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div class="modal-body">
    <div class="alert alert-warning" id="import_details_stop" style="display:none;"> {l s='Aborting, please wait...' mod='wkinventory'} </div>
    <p id="import_details_progressing"> {l s='Updating your shop' mod='wkinventory'}...</p>
    <div class="alert alert-success" id="import_details_finished" style="display:none;">{l s='Shop updated!' mod='wkinventory'}</div>
    <div class="alert alert-warning" id="import_details_empty" style="display:none;"> {l s='No products updated!' mod='wkinventory'}</div>
    <div id="import_messages_div" style="max-height:250px; overflow:auto;">
        <div class="alert alert-danger" id="import_details_error" style="display:none;"> {l s='Errors occurred:' mod='wkinventory'}<br/>
            <ul>
            </ul>
        </div>
        <div class="alert alert-warning" id="import_details_post_limit" style="display:none;">{l s='Warning, the current update may require a PHP setting update, to allow more data to be handled. If the current process stops before the end, you should increase your PHP post_max_size setting to' mod='wkinventory'} <span id="import_details_post_limit_value">16MB</span> {l s='at least, and try again' mod='wkinventory'}.</div>
        <div class="alert alert-warning" id="import_details_warning" style="display:none;"> {l s='Some warnings were detected. Please check the details:' mod='wkinventory'}<br/>
            <ul>
            </ul>
        </div>
        <div class="alert alert-info" id="import_details_info" style="display:none;"> {l s='We made the following adjustments:' mod='wkinventory'}<br/>
            <ul>
            </ul>
        </div>
    </div>
    <div id="import_validate_div" style="margin-top:17px;">
        <div class="pull-right" id="import_validation_details" default-value="{l s='Validating process...' mod='wkinventory'}"> &nbsp; </div>
        <div class="progress active progress-striped" style="display: block; width: 100%">
            <div class="progress-bar progress-bar-info" role="progressbar" style="width: 0%" id="validate_progressbar_done">
				<span><span id="validate_progression_done">0</span>% {l s='Validated' mod='wkinventory'}</span>
            </div>
            <div class="progress-bar progress-bar-info" role="progressbar" id="validate_progressbar_next" style="opacity: 0.5 ;width: 0%"> <span class="sr-only">{l s='Processing next page...' mod='wkinventory'}</span> </div>
        </div>
    </div>
    <div id="import_progress_div" style="display:none;">
        <div class="pull-right" id="import_progression_details" default-value="{l s='Updating your shop' mod='wkinventory'}">...&nbsp;</div>
        <div class="progress active progress-striped" style="display: block; width: 100%">
            <div class="progress-bar progress-bar-info" role="progressbar" style="width: 0%" id="import_progressbar_done2"> <span>{l s='Saving products...' mod='wkinventory'}</span> </div>
            <div class="progress-bar progress-bar-success" role="progressbar" style="width: 0%" id="import_progressbar_done"> <span></span> </div>
            <div class="progress-bar progress-bar-success progress-bar-stripes active" role="progressbar" id="import_progressbar_next" style="opacity: 0.5 ;width: 0%"> <span class="sr-only">{l s='Processing next page...' mod='wkinventory'}</span></div>
        </div>
    </div>
    <div class="modal-footer">
        <div class="input-group pull-right">
            <button type="button" class="btn btn-primary" tabindex="-1" id="import_continue_button" style="display: none;"> {l s='Ignore warnings and continue?' mod='wkinventory'} </button>
            &nbsp;
            <button type="button" class="btn btn-default" tabindex="-1" id="import_stop_button"> {l s='Abort process' mod='wkinventory'} </button>
            &nbsp;
            <button type="button" class="btn btn-danger" data-dismiss="modal" tabindex="-1" id="update_close_button" style="display: none;"> {l s='Close' mod='wkinventory'} </button>
        </div>
    </div>
</div>
