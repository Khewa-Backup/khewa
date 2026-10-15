{*
 * PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 *}
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>{l s='Foreign ID' mod='mailchimppro'}</th>
            <th>{l s='Store ID' mod='mailchimppro'}</th>
            <th>{l s='Platform' mod='mailchimppro'}</th>
            <th>{l s='Domain' mod='mailchimppro'}</th>
            <th>{l s='Site script' mod='mailchimppro'}</th>
            <th class="text-center">{l s='Pixel' mod='mailchimppro'}</th>
        </tr>
        </thead>
        <tbody>
        {foreach $sites as $site}
            <tr>
                <td>{$site.foreign_id|escape:'htmlall':'UTF-8'}</td>
                <td>{$site.store_id|escape:'htmlall':'UTF-8'}</td>
                <td>{$site.platform|escape:'htmlall':'UTF-8'}</td>
                <td>{$site.domain|escape:'htmlall':'UTF-8'}</td>
                <td>{$site.site_script.url|escape:'htmlall':'UTF-8'}</td>
                <td class="text-center">
                    {if isset($site.is_pixel_enabled) && $site.is_pixel_enabled}
                        <span title="{l s='Pixel is enabled for this connected site' mod='mailchimppro'}">✔️</span>
                    {else}
                        <span title="{l s='Pixel is not enabled for this connected site' mod='mailchimppro'}">❌️</span>
                    {/if}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>