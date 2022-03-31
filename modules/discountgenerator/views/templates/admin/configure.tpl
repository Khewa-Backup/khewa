{**
 * DiscountGenerator Prestashop Module
 *
 * @author iRessources <support-prestashop@iressources.com>
 * @link http://www.iressources.com/
 * @copyright Copyright &copy; 2015-2019 iRessources
 * @version 1.4.1
 *}
<p><strong>{l s='SETTINGS AND USER GUIDE' mod='discountgenerator'}</strong></p>

<p>{l s='Thank you for purchasing the Discount generator module.' mod='discountgenerator'}</p>

<p>{l s='The module integrates directly into Catalog > Discounts tab. It expands native Prestashop cart rule functions and allows you to create important amounts of unique voucher codes inside one cart rule.' mod='discountgenerator'}</p>

<p>{l s='If you need to create several hundreds or even thousands of unique voucher codes in one click, activate the Discount generator module by checking the box named “Generate many unique vouchers”. Alternatively, if you want to create a regular cart rule (with one voucher code) please leave this box unchecked.' mod='discountgenerator'}</p>

<p>{l s='Please note that you will have to fill in all required fields in three tabs: Information, Conditions, and Actions.' mod='discountgenerator'}</p>

<p>{l s='On the front end your customers will be able to enter the voucher codes you\'ve generated in the standard input field for promotion codes.' mod='discountgenerator'}</p>

<p>{l s='Upon successful generation, you will be able to follow the use of voucher codes by your customers by downloading three diffent files called “Used”, “Unused”, “All”. The files are availble for dowload at the bottom of the module configuration page.' mod='discountgenerator'}</p>

<p>{l s='We hope you will enjoy generating voucher codes! Please feel free to contact us for any further questions using this form:' mod='discountgenerator'}
    <a href="http://addons.prestashop.com/en/order-history" target="_blank">http://addons.prestashop.com/en/order-history</a>
</p>

<p>{l s='If your website is a multistore, please keep the checked the option below "Activate module for this shop context". It is important for a correct use of vouchers across your stores.' mod='discountgenerator'}</p>

<p><a href="{$generate}">{l s='GO TO GENERATION OF VOUCHER CODES' mod='discountgenerator'}</a></p>

<br/>

{if $ps_version >= 1.6}
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s='"Discount Generator" module History' mod='discountgenerator'}
        </div>
        <div class="table-responsive-row clearfix">
            <table class="table order">
                <thead>
                <tr class="nodrag nodrop">
                    <th>{l s='Description' mod='discountgenerator'}</th>
                    <th>{l s='Creation date' mod='discountgenerator'}</th>
                    <th>{l s='All' mod='discountgenerator'}</th>
                    <th>{l s='Used' mod='discountgenerator'}</th>
                    <th>{l s='Unused' mod='discountgenerator'}</th>
                    <th>{l s='Delete' mod='discountgenerator'}</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$history item=file}
                    <tr>
                        <td>{$file.name}</td>
                        <td>{$file.date}</td>
                        <td>
                            <a href="{$link}&amp;generatetable&amp;conf=1&amp;id_group={$file.id_group}">{l s='Download' mod='discountgenerator'}</a>
                        </td>
                        <td>
                            <a href="{$link}&amp;generatetableused&amp;conf=1&amp;id_group={$file.id_group}">{l s='Download' mod='discountgenerator'}</a>
                        </td>
                        <td>
                            <a href="{$link}&amp;generatetablenew&amp;conf=1&amp;id_group={$file.id_group}">{l s='Download' mod='discountgenerator'}</a>
                        </td>
                        <td>
                            <a href="{$link}&amp;deletefile&amp;conf=1&amp;id_group={$file.id_group}">{l s='Delete' mod='discountgenerator'}</a>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{else}
    <fieldset style="margin-top: 30px;">
        <legend>{l s='"Discount Generator" module History' mod='discountgenerator'}</legend>
        <div class="table-responsive-row clearfix">
            <table class="table" style="width: 100%; margin-bottom:10px;">
                <thead>
                <tr class="nodrag nodrop">
                    <th>{l s='Description' mod='discountgenerator'}</th>
                    <th>{l s='Creation date' mod='discountgenerator'}</th>
                    <th>{l s='All' mod='discountgenerator'}</th>
                    <th>{l s='Used' mod='discountgenerator'}</th>
                    <th>{l s='Unused' mod='discountgenerator'}</th>
                    <th>{l s='Delete' mod='discountgenerator'}</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$history item=file}
                    <tr>
                        <td>{$file.name}</td>
                        <td>{$file.date}</td>
                        <td>
                            <a href="{$link}&amp;generatetable&amp;conf=1&amp;id_group={$file.id_group}">{l s='Download' mod='discountgenerator'}</a>
                        </td>
                        <td>
                            <a href="{$link}&amp;generatetableused&amp;conf=1&amp;id_group={$file.id_group}">{l s='Download' mod='discountgenerator'}</a>
                        </td>
                        <td>
                            <a href="{$link}&amp;generatetablenew&amp;conf=1&amp;id_group={$file.id_group}">{l s='Download' mod='discountgenerator'}</a>
                        </td>
                        <td>
                            <a href="{$link}&amp;deletefile&amp;conf=1&amp;id_group={$file.id_group}">{l s='Delete' mod='discountgenerator'}</a>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </fieldset>
{/if}
