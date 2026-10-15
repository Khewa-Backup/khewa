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
* Admin instruction for adding redirection url in tpl file
*}  
<div class="row">
    <div class="col-lg-12">
        <div class="panel">                      
            <div class='panel-heading'>
                {l s='Add below redirect URL into the Callback URLs' mod='kbetsy'}

            </div>
            <div class='row'>
                
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='1. Go to Apps: ' mod='kbetsy'}<a href="https://www.etsy.com/developers/your-apps" target="_blank"> https://www.etsy.com/developers/your-apps</a></p>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='2. Click on the name of your app' mod='kbetsy'}</p>
                 <p>&nbsp;&nbsp;&nbsp;&nbsp;{l s='3. Scroll down and edit call back url and add given URL' mod='kbetsy'}</p>
                  
                    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp <b>{$redirect_url|escape:'htmlall':'UTF-8'}</b></p>
                    
                <br>

              
            </div>
        </div>
    </div>
    <div class="modal"></div>
</div>       