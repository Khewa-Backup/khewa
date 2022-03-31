{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* @category  PrestaShop Module
* @author    knowband.com <support@knowband.com>
* @copyright 2017 Knowband
* @license   see file: LICENSE.txt
*
* Description
*
* Admin Synchronization tpl file
*}

<script>
    var sync_msg = "{l s='Synchronization completed.' mod='kbetsy'}";
    var sync_fail_msg = "{l s='Synchronization failed.' mod='kbetsy'}";
</script>

<div class="row">
    <div class="col-lg-6">
        <div class="panel">                      
            <div class='panel-heading'>
                {l s='Products Synchronization' mod='kbetsy'}
            </div>
            <div class='row'>
                <div class="profileTabs col-lg-12">
                    <div class="form-group" style="height: 75px">
                        <div style="text-align: center; margin-bottom: 10px">
                            <a href="{$sync_products_listing_url|escape:'htmlall':'UTF-8'}" target="_blank" class="btn btn-info"  role="button">{l s='Sync Products' mod='kbetsy'}</a>
                        </div>                            
                        <p class='help-block'>{l s='The above operation will sync the items on the Etsy. New items will be listed on the Etsy. Item quantity & other info of the product will be synced on Etsy via this action.' mod='kbetsy'}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel">                      
            <div class='panel-heading'>
                {l s='Sync Listing Status' mod='kbetsy'}
            </div>
            <div class='row'>
                <div class="profileTabs col-lg-12">
                    <div class="form-group" style="height: 75px">
                        <div style="text-align: center; margin-bottom: 10px">
                            <a href="{$sync_products_listing_status_url|escape:'htmlall':'UTF-8'}" target="_blank" class="btn btn-info"  role="button">{l s='Update Listing Status' mod='kbetsy'}</a>
                        </div>                            
                        <p class='help-block'>{l s='The above operation will sync the status of the items from the Etsy to the module. If item is set to active OR ended in the Etsy account, then this operation will mark the order Inactive/Ended on the Etsy.' mod='kbetsy'}</p>
                    </div>  
                </div>
            </div>
        </div>
    </div>
</div>

<div class="clearfix"></div>

<div class="row">
    <div class="col-lg-6">
        <div class="panel">                      
            <div class='panel-heading'>
                {l s='Orders Synchronization' mod='kbetsy'}
            </div>
            <div class='row'>
                <div class="profileTabs col-lg-12">
                    <div class="form-group">
                        <div class="form-group" style="height: 75px">
                            <div style="text-align: center; margin-bottom: 10px">
                                <a href="{$sync_orders_listing_url|escape:'htmlall':'UTF-8'}" target="_blank" class="btn btn-info" role="button">{l s='Import Orders' mod='kbetsy'}</a>
                            </div>                            
                            <p class='help-block'>{l s='The above operation will import the orders from the Etsy to Prestashop.' mod='kbetsy'}</p>
                        </div>  
                    </div>  
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel">                      
            <div class='panel-heading'>
                {l s='Orders Status Update' mod='kbetsy'}
            </div>
            <div class='row'>
                <div class="profileTabs col-lg-12">
                    <div class="form-group" style="height: 75px">
                        <div style="text-align: center; margin-bottom: 10px">
                            <a href="{$sync_orders_status_url|escape:'htmlall':'UTF-8'}" target="_blank" class="btn btn-info" role="button">{l s='Update Orders Status on Etsy' mod='kbetsy'}</a>
                        </div>                            
                        <p class='help-block'>{l s='The above operation sync the orders status from the PrestaShop to Etsy. If the order is marked as Shipping on the PrestaShop then above operation mark order as Shipped on Etsy.' mod='kbetsy'}</p>
                    </div> 
                </div>  
            </div>
        </div>
    </div>
</div>

<div class="clearfix"></div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel">                      
            <div class='panel-heading'>
                {l s='Cron Instructions' mod='kbetsy'}
            </div>
            <div class='row'>
                <p>
                    {l s='Add the cron to your store via control panel/putty to synchronize data between your store and Etsy Marketplace. Please find the instructions to setup crons below.' mod='kbetsy'}
                </p>

                <p style="margin: 30px 0px 10px 0px; font-size: 14px">
                    <b>{l s='URLs to Add to Cron via Control Panel' mod='kbetsy'}</b>
                </p>
                <p><b>{l s='Sync Products on Etsy (Every 10mins)' mod='kbetsy'}</b><br/> {$sync_products_listing_url|escape:'htmlall':'UTF-8'}</p>
                <p><b>{l s='Products Listing Status Synchronization (Every 3hours)' mod='kbetsy'}</b><br/> {$sync_products_listing_status_url|escape:'htmlall':'UTF-8'}</p>
                <p><b>{l s='Orders Import (Every hour)' mod='kbetsy'}</b><br/> {$sync_orders_listing_url|escape:'htmlall':'UTF-8'}</p>
                <p><b>{l s='Update Orders Status on Etsy (Every hour)' mod='kbetsy'}</b><br/> {$sync_orders_status_url|escape:'htmlall':'UTF-8'}</p>


                <p style="margin: 30px 0px 10px 0px; font-size: 14px">
                    <b>{l s='Cron setup via SSH' mod='kbetsy'}</b>
                </p>

                <p><b>{l s='Sync Products on Etsy' mod='kbetsy'}</b><br/> */10 * * * * curl -O /dev/null {$sync_products_listing_url|escape:'htmlall':'UTF-8'}</p>
                <p><b>{l s='Products Listing Status Synchronization' mod='kbetsy'}</b><br/> */30 * * * * curl -O /dev/null {$sync_products_listing_status_url|escape:'htmlall':'UTF-8'}</p>
                <p><b>{l s='Orders Import' mod='kbetsy'}</b><br/> 0 */1 * * * curl -O /dev/null {$sync_orders_listing_url|escape:'htmlall':'UTF-8'}</p>
                <p><b>{l s='Update Orders Status on Etsy' mod='kbetsy'}</b><br/> 0 */1 * * * curl -O /dev/null {$sync_orders_status_url|escape:'htmlall':'UTF-8'}</p>
            </div>
        </div>
    </div>
</div>
