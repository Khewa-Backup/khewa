{*
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   Proprietary
 *}
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>{l s='Merge ID' mod='mailchimppro'}</th>
            <th>{l s='Field label' mod='mailchimppro'}</th>
            <th>{l s='Merge tag' mod='mailchimppro'}</th>
            <th>{l s='Type' mod='mailchimppro'}</th>
            <th>{l s='Required' mod='mailchimppro'}</th>
            <th>{l s='Default value' mod='mailchimppro'}</th>
        </tr>
        </thead>
        <tbody>
        {foreach $merge_fields as $merge_field}
            <tr>
                <td>{$merge_field.merge_id|escape:'htmlall':'UTF-8'}</td>
                <td>{$merge_field.name|escape:'htmlall':'UTF-8'}</td>
                <td><code>{$merge_field.tag|escape:'htmlall':'UTF-8'}</code></td>
                <td>{$merge_field.type|escape:'htmlall':'UTF-8'}</td>
                <td>{if $merge_field.required}{l s='Yes' mod='mailchimppro'}{else}{l s='No' mod='mailchimppro'}{/if}</td>
                <td>{$merge_field.default_value|escape:'htmlall':'UTF-8'}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
