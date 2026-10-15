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
            <th>{l s='ID' mod='mailchimppro'}</th>
            <th>{l s='Email' mod='mailchimppro'}</th>
            {foreach $merge_field_defs as $merge_field_def}
                <th>{$merge_field_def.name|escape:'htmlall':'UTF-8'}</th>
            {/foreach}
            <th>{l s='Email type' mod='mailchimppro'}</th>
            <th>{l s='Status' mod='mailchimppro'}</th>
            <th>{l s='IP signup' mod='mailchimppro'}</th>
            <th>{l s='Signup time' mod='mailchimppro'}</th>
            <th>{l s='IP Opt-in' mod='mailchimppro'}</th>
            <th>{l s='Language' mod='mailchimppro'}</th>
			<th>{l s='Tags' mod='mailchimppro'}</th>
            <th>{l s='VIP' mod='mailchimppro'}</th>			
        </tr>
        </thead>
        <tbody>
        {foreach $members as $member}
            <tr>

                <td title="{$member.id|escape:'htmlall':'UTF-8'}" style="cursor: help; white-space: nowrap;">
                    {if $member.id|strlen > 9}{$member.id|substr:0:3|escape:'htmlall':'UTF-8'}&hellip;{$member.id|substr:-3|escape:'htmlall':'UTF-8'}{else}{$member.id|escape:'htmlall':'UTF-8'}{/if}
                </td>
                <td>{$member.email_address|escape:'htmlall':'UTF-8'}</td>
                {foreach $merge_field_defs as $merge_field_def}
                    {assign var=merge_value value=''}
                    {if isset($member.merge_fields[$merge_field_def.tag])}
                        {assign var=merge_value value=$member.merge_fields[$merge_field_def.tag]}
                    {/if}
                    <td>
                        {if is_array($merge_value)}
                            {$merge_value.addr1|default:''|escape:'htmlall':'UTF-8'}
                            {$merge_value.city|default:''|escape:'htmlall':'UTF-8'}
                            {$merge_value.zip|default:''|escape:'htmlall':'UTF-8'}
                            {$merge_value.country|default:''|escape:'htmlall':'UTF-8'}
                        {elseif $merge_value !== ''}
                            {$merge_value|escape:'htmlall':'UTF-8'}
                        {else}
                            &mdash;
                        {/if}
                    </td>
                {/foreach}
                <td>{$member.email_type|escape:'htmlall':'UTF-8'}</td>
                <td>
					<span class="member-status {$member.status|escape:'htmlall':'UTF-8'}">{$member.status|escape:'htmlall':'UTF-8'}</span>
				</td>
                <td>{$member.ip_signup|escape:'htmlall':'UTF-8'}</td>
                <td>{$member.timestamp_signup|escape:'htmlall':'UTF-8'}</td>
                <td>{$member.ip_opt|escape:'htmlall':'UTF-8'}</td>
                <td>{$member.language|escape:'htmlall':'UTF-8'}</td>
				<td>
					{if $member.tags_count}
						{foreach $member.tags as $tag}
							<span class="member-tag">{$tag.name|escape:'htmlall':'UTF-8'}</span>
						{/foreach}
					{else}
						—
					{/if}
				</td>
                <td>{$member.vip|escape:'htmlall':'UTF-8'}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>