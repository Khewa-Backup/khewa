{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2022, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div class="elegantalBootstrapWrapper">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-time"></i> {$header_title|escape:'html':'UTF-8'} : {l s='CRON Job Settings' mod='elegantalseoessentials'}
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-12">                        
                    <p>{$subject_note|escape:'html':'UTF-8'}</p>
                    <p>{l s='You will need to create a crontab for the following URL on your hosting server' mod='elegantalseoessentials'}: </p>
                    <div class="alert alert-info alert-link-icon">
                        {$cronUrl|escape:'html':'UTF-8'}
                    </div>
                    {l s='The following is an example of CRON command' mod='elegantalseoessentials'}: <br>
                    <div class="well">curl "{$cronUrl|escape:'html':'UTF-8'}"</div>
                    {l s='The following is an example of crontab which runs every hour' mod='elegantalseoessentials'}: <br>
                    <div class="well">0 * * * * curl "{$cronUrl|escape:'html':'UTF-8'}"</div>
                    <p>
                        {l s='Learn more about CRON' mod='elegantalseoessentials'}: <br>
                        <a href="https://en.wikipedia.org/wiki/Cron" target="_blank">https://en.wikipedia.org/wiki/Cron</a>
                    </p>
                    {if $cron_cpanel_doc}
                        <p>
                            {l s='Learn how to setup CRON Job in cPanel' mod='elegantalseoessentials'}: <br>
                            <a href="{$cron_cpanel_doc|escape:'html':'UTF-8'}" target="_blank">
                                {l s='User guide on how to setup CRON Job in cPanel' mod='elegantalseoessentials'}
                            </a>
                        </p>
                    {/if}
                    <p>
                        {l s='If you do not know how to setup CRON Job on your server, there is another easy way to do this.' mod='elegantalseoessentials'} 
                        <br>
                        {l s='You do not even need to open your cPanel or server. Just use any free or paid online CRON services, for example:' mod='elegantalseoessentials'} 
                        <a href="https://cron-job.org" target="_blank">https://cron-job.org</a>
                        <br>
                        {l s='You will select time and put command above (curl "http://....") and this online tool will take care of automatic execution of the module.' mod='elegantalseoessentials'} 
                    </p>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <a href="{$backUrl|escape:'html':'UTF-8'}" class="btn btn-default">
                <i class="process-icon-back"></i> {l s='Back' mod='elegantalseoessentials'}
            </a>
        </div>
    </div>
</div>