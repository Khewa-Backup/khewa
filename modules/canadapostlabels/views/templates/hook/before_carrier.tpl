{*
*  2019 Zack Hussain
*
*  @author      Zack Hussain <me@zackhussain.ca>
*  @copyright   2019 Zack Hussain
*
*  DISCLAIMER
*
*  Do not redistribute without my permission. Feel free to modify the code as needed.
*  Modifying the code may break future PrestaShop updates.
*  Do not remove this comment containing author information and copyright.
*
*}
{if !empty($errorArray)}
    <div class="alert alert-warning">
        <h5>{l s='We could not get rates from Canada Post due to the following error(s): ' mod='canadapostlabels'}</h5>
        <ul>
            {foreach from=$errorArray key=name item=error}
                <li><b>{l s='Canada Post' mod='canadapostlabels'} {$name|escape:'html':'UTF-8'}:</b> {$error|escape:'html':'UTF-8'}</li>
            {/foreach}
        </ul>
    </div>
{/if}
