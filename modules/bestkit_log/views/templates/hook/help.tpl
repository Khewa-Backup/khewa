{*
 * Module administration links.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 *}

<div class="panel-heading"><h2>{l s='Help' mod='bestkit_log'}</h2></div>

{if count($bestkit_logs.links)}
    <div class="alert alert-warning">
        <ul>
            {foreach $bestkit_logs.links as $link}
                <li>
                    <a href="{$link.href|escape:'html':'UTF-8'}">{$link.title|escape:'html':'UTF-8'}</a>
                </li>
            {/foreach}
        </ul>
    </div>
{/if}
