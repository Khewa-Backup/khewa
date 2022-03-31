{*
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 *
 *}
{if $output && !empty($output)}
    {$output nofilter} {* var containing HTML *}
{/if}

<div id="canadapost" class="row">
    <div class="sidebar col-xs-12 col-sm-3 col-md-2 col-lg-2">
        <nav class="list-group formTabs">
            {if isset($config_tabs) && !empty($config_tabs)}
                {foreach from=$config_tabs key=id item=tab}
                    <a id="{$id|escape:'html':'UTF-8'}_tab" class="list-group-item {if isset($tab.enabled) && $tab.enabled == false}disabled{/if}" href="#{$id|escape:'html':'UTF-8'}">
                        <i class="{$tab.icon|escape:'html':'UTF-8'}"></i> {$tab.title|escape:'html':'UTF-8'}
                        {if isset($tab.badge) && $tab.badge != false}
                            <span class="badge badge-primary badge-pill">{$tab.badge|escape:'html':'UTF-8'}</span>
                        {/if}
                    </a>
                {/foreach}
            {/if}
            <a href="#development" id="development_tab" class="list-group-item">
                <i class="icon-wrench"></i> {l s='Custom Development' mod='canadapostlabels'}
            </a>
        </nav>

        <div class="list-group">
            <a href="{$readmeUrl|escape:'html':'UTF-8'}" target="_blank" class="list-group-item">
                <i class="icon-file"></i> {l s='Documentation' mod='canadapostlabels'}
            </a>
            <a href="{$faqUrl|escape:'html':'UTF-8'}" target="_blank" class="list-group-item">
                <i class="icon-question-circle"></i> {l s='FAQs' mod='canadapostlabels'}
            </a>
            <a href="{$contactUrl|escape:'html':'UTF-8'}" target="_blank" class="list-group-item">
                <i class="icon-envelope"></i> {l s='Contact Support' mod='canadapostlabels'}
            </a>
        </div>

        <div class="list-group">
            <a href="{$contactUrl|escape:'html':'UTF-8'}" target="_blank" class="list-group-item">
                <i class="icon-envelope"></i> {l s='Feedback' mod='canadapostlabels'}
            </a>
            <a href="{$rateUrl|escape:'html':'UTF-8'}" target="_blank" class="list-group-item">
                <i class="icon-star rate"></i> {l s='Rate this module' mod='canadapostlabels'}
            </a>
        </div>

        <div class="list-group">
            <div class="list-group-item">
                <i class="icon-info"></i> {l s='Version' mod='canadapostlabels'} {$module_version|escape:'html':'UTF-8'}
            </div>
        </div>

        {include file="../admin/logo.tpl"}

    </div>
    <div class="forms tab-content">
        {if isset($config_forms) && !empty($config_forms)}
            {foreach from=$config_forms key=id item=form}
                <div id="{$id|escape:'html':'UTF-8'}" class="tab-pane">
                    {$form nofilter} {* var containing HTML *}
                </div>
            {/foreach}
        {/if}

        {include file="./development.tpl"}

    </div>
</div>
