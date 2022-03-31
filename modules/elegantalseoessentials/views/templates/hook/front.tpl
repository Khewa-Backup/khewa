{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2022, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
{if $canonical_url}
    <link rel="canonical" href="{$canonical_url nofilter}" />
{/if}
{if $hreflangs && is_array($hreflangs)}
    {foreach from=$hreflangs key=iso_code item=hreflang}
        <link rel="alternate" hreflang="{$iso_code|escape:'html':'UTF-8'}" href="{$hreflang nofilter}" />
    {/foreach}
{/if}
{if $next_prev_tags.prev}
    <link rel="prev" href="{$next_prev_tags.prev nofilter}" />
    <link rel="prefetch" href="{$next_prev_tags.prev nofilter}" /> {* ask the browser to request prev page items to store in the cache for reference later *}
{/if}
{if $next_prev_tags.next}
    <link rel="next" href="{$next_prev_tags.next nofilter}" />
    <link rel="prerender" href="{$next_prev_tags.next nofilter}" /> {* ask the browser to load the next page in the background so that navigation will be faster *}
{/if}
{if $next_prev_tags.prev && $settings.is_enable_noindex_on_paginated_pages}
    <meta name="robots" content="noindex">
{/if}
{if $settings.is_enable_sitelinks_searchbox && $controller == 'index'}
    {literal}
        <script type="application/ld+json">
            {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "url": "{/literal}{$shop_url|escape:'html':'UTF-8'}{literal}",
            "potentialAction": {
            "@type": "SearchAction",
            "target": "{/literal}{$sitelinks_searchbox_target nofilter}{literal}",
            "query-input": "required name=search_term_string"
            }
            }
        </script>
    {/literal}
{/if}