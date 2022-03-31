{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2022, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div id="product-ModuleElegantalseoessentials" class="panel product-tab">
    <h3>{l s='URL Redirect Manager' mod='elegantalseoessentials'}</h3>
    <div class="form-group">
        <label class="control-label col-md-3" for="elegantal_new_url">
            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Enter URL to which you want to redirect this product.' mod='elegantalseoessentials'} {l s='NOTE:' mod='elegantalseoessentials'} {l s='You should enter absolute URL with domain.' mod='elegantalseoessentials'}">
                {l s='Redirect URL' mod='elegantalseoessentials'}
            </span>
        </label>
        <div class="col-md-9">
            <input type="text" name="elegantal_new_url" id="elegantal_new_url" class="form-control" value="{$redirect.new_url|escape:'html':'UTF-8'}" placeholder="http://www.example.com/new-url">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-3" for="elegantal_redirect_type">
            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='A 301 redirect means that the page has permanently moved to a new location. A 302 redirect means that the move is only temporary. 303 Redirect is a "see other" redirect status indicating that the resource has been replaced.' mod='elegantalseoessentials'}">
                {l s='Redirect Type' mod='elegantalseoessentials'}
            </span>
        </label>
        <div class="col-md-7 col-lg-5">
            <select name="elegantal_redirect_type" id="elegantal_redirect_type" class="form-control">
                <option value="301" {if $redirect.redirect_type == 301}selected="selected"{/if}>
                    {l s='301 - URL permanently moved to a new location' mod='elegantalseoessentials'}
                </option>
                <option value="302" {if $redirect.redirect_type == 302}selected="selected"{/if}>
                    {l s='302 - URL temporarily moved to a new location' mod='elegantalseoessentials'}
                </option>
                <option value="303" {if $redirect.redirect_type == 303}selected="selected"{/if}>
                    {l s='303 - GET method used to retrieve information' mod='elegantalseoessentials'}
                </option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-3" for="elegantal_expires_at">
            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Specify date till which you want this redirect to be active. After the specified date, the redirection will not happen. Leave it empty in order to have this redirect all the time.' mod='elegantalseoessentials'}">
                {l s='Expires at' mod='elegantalseoessentials'}
            </span>
        </label>
        <div class="col-md-4 col-lg-3">
            <div class="input-group">
                <input type="text" name="elegantal_expires_at" id="elegantal_expires_at" value="{$redirect.expires_at|escape:'html':'UTF-8'}" class="form-control datepicker" data-hex="true" autocomplete="off" placeholder="YYYY-MM-DD">
                {if $ps_version < 1.7}
                    <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
                {else}
                    <div class="input-group-append"><div class="input-group-text"><i class="material-icons">date_range</i></div></div>
                {/if}
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-3">
            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='You can enable or disable this URL. Only active redirects will work.' mod='elegantalseoessentials'}">
                {l s='Active' mod='elegantalseoessentials'}
            </span>
        </label>
        <div class="col-md-6">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="elegantal_is_active" id="redirect_active_on" value="1" {if $redirect.is_active == 1}checked="checked"{/if}>
                <label for="redirect_active_on">Yes</label>
                <input type="radio" name="elegantal_is_active" id="redirect_active_off" value="0" {if $redirect.is_active == 0}checked="checked"{/if}>
                <label for="redirect_active_off">No</label>
                <a class="slide-button btn"></a>
            </span>
        </div>
    </div>
    {if $ps_version < 1.7}
        <div class="panel-footer">
            <a href="{$link->getAdminLink('AdminProducts')|escape:'html':'UTF-8'}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='elegantalseoessentials'}</a>
            <button type="submit" name="submitAddproduct" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='elegantalseoessentials'}</button>
            <button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save and stay' mod='elegantalseoessentials'}</button>
        </div>
    {/if}
</div>
