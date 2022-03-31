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
<div id="canadapost" class="row">
    <div class="col-lg-12">
        {if isset($form_tabs) && !empty($form_tabs)}
        <div class="form-container panel card">
            <div class="panel-heading card-header">
              {$icon nofilter} Canada Post
            </div>
         {/if}
            {if isset($form_tabs) && !empty($form_tabs)}
                <div class="sidebar col-xs-12 col-sm-3 col-md-2 col-lg-2">
                    <nav class="list-group formTabs">
                        {foreach from=$form_tabs key=id item=tab}
                            <a id="{$id|escape:'html':'UTF-8'}_tab" class="list-group-item nav-link {if isset($tab.enabled) && $tab.enabled == false}disabled{/if}" href="#{$id|escape:'html':'UTF-8'}">
                                {if $tab.icon}{$tab.icon nofilter}{/if} {$tab.title|escape:'html':'UTF-8'}
                                {if isset($tab.badge) && $tab.badge != false}
                                    <span class="badge badge-primary badge-pill">{$tab.badge|escape:'html':'UTF-8'}</span>
                                {/if}
                            </a>
                        {/foreach}
                    </nav>

                    {*{include file="module:$module_name/views/templates/admin/logo.tpl"}*}

                </div>
            {/if}

            <div class="forms {if isset($form_tabs) && !empty($form_tabs)}tab-content{/if}">
                {if $forms && !empty($forms)}
                    {foreach from=$forms key=id item=form}
                        <div id="{$id|escape:'html':'UTF-8'}" class="{if isset($form_tabs) && !empty($form_tabs)}tab-pane{/if} col-lg-12">
                            {$form nofilter} {* var containing HTML *}
                        </div>
                    {/foreach}
                {/if}
            </div>

            <!-- Modal -->
            {if isset($modal)}
                {$modal nofilter} {* var containing HTML *}
            {/if}

            <!-- Logo -->
            {if isset($logo)}
                {$logo nofilter} {* var containing HTML *}
            {/if}
        {if isset($form_tabs) && !empty($form_tabs)}
        </div>
        {/if}
    </div>
</div>
