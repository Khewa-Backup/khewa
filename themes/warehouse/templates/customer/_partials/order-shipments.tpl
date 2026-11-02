{**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<div class="box">
    <table class="table table-striped table-bordered hidden-sm-down">
        <thead class="thead-default">
            <tr>
                <th>{l s='Date' d='Shop.Theme.Global'}</th>
                <th>{l s='Carrier' d='Shop.Theme.Checkout'}</th>
                <th>{l s='Weight' d='Shop.Theme.Checkout'}</th>
                <th>{l s='Tracking number' d='Shop.Theme.Checkout'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$order.shipping item=line}
                <tr>
                    <td>{$line.date_add}</td>
                    <td>{$line.carrier_name}</td>
                    <td>{$line.package_weight}</td>
                    <td>
                        {if $line.carrier_tracking_url}
                            <a href="{$line.carrier_tracking_url}" target="_blank">{$line.tracking_number}</a>
                        {elseif $line.tracking_number}
                            {$line.tracking_number}
                        {else}
                            -
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
    <div class="hidden-md-up shipping-lines">
        {foreach from=$order.shipping item=line}
            <div class="shipping-line">
                <ul>
                    <li>
                        <strong>{l s='Date' d='Shop.Theme.Global'}</strong> {$line.date_add}
                    </li>
                    <li>
                        <strong>{l s='Carrier' d='Shop.Theme.Checkout'}</strong> {$line.carrier_name}
                    </li>
                    <li>
                        <strong>{l s='Weight' d='Shop.Theme.Checkout'}</strong> {$line.package_weight}
                    </li>
                    <li>
                        <strong>{l s='Tracking number' d='Shop.Theme.Checkout'}</strong>
                        {if $line.carrier_tracking_url}
                            <a href="{$line.carrier_tracking_url}" target="_blank">{$line.tracking_number}</a>
                        {elseif $line.tracking_number}
                            {$line.tracking_number}
                        {else}
                            -
                        {/if}
                    </li>
                </ul>
            </div>
        {/foreach}
    </div>
</div>