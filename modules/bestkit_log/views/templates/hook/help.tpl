<div class="panel-heading"><h2>{l s='Help' mod='bestkit_log'}</h2></div>

{if count($bestkit_logs.links)}
    <div class="alert alert-warning">
        <ul>
            {foreach $bestkit_logs.links as $link}
                <li>
                    <a href="{$link.href nofilter}">{$link.title|escape:'htmlall':'UTF-8'}</a>
                </li>
            {/foreach}
        </ul>
    </div>
{/if}
