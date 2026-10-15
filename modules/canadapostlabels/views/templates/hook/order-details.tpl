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

{if isset($trackingSummaries) && $trackingSummaries|count > 0}
    {foreach $trackingSummaries as $trackingSummary}
        <section class="box canadapost-tracking">
            <div class="row">
                <div class="col-xs-2 col-sm-2 col-md-1 icon-container">
                    <i class="material-icons shipping-icon">local_shipping</i>
                </div>
                <div class="col-xs-10 col-sm-10 col-md-11 tracking-details">
                    <h3>{$progressStates[$trackingSummary.progressState].heading|escape:'html':'UTF-8'}</h3>
                    <ul>
                        <li></li>
                        <li>
                            {l s='Tracking Number' mod='canadapostlabels'}
                            {if isset($trackingSummary.trackingLink)} <a target="_blank" href="{$trackingSummary.trackingLink|escape:'html':'UTF-8'}">{$trackingSummary.CacheTracking->pin|escape:'html':'UTF-8'}</a>{/if}
                        </li>
                        {if isset($trackingSummary.actualDelivery)}
                            <li>{$trackingSummary.actualDelivery|escape:'html':'UTF-8'}</li>
                        {elseif isset($trackingSummary.delivers)}
                            <li>{$trackingSummary.delivers|escape:'html':'UTF-8'}</li>
                        {/if}
                        <li>
                            {$trackingSummary.CacheTracking->event_description|escape:'html':'UTF-8'}
                            {if isset($trackingSummary.CacheTracking->event_location)}{l s='at' mod='canadapostlabels'} {$trackingSummary.CacheTracking->event_location|escape:'html':'UTF-8'}{/if}
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row tracking-progress">
                <div class="col-xs-12">
                    <div class="progress-bar-container">
                        <div class="progress-bar-background">
                            {if isset($trackingSummary.progressColor) && isset($trackingSummary.progressWidth)}
                                <div class="progress-bar-fill" style="background-color: {$trackingSummary.progressColor|escape:'html':'UTF-8'}; width: {$trackingSummary.progressWidth|escape:'html':'UTF-8'}%"></div>
                            {/if}
                        </div>
                        <div class="progress-bar-labels">
                            <ul>
                                {foreach from=$progressStates key=id item=label}
                                    <li {if $trackingSummary.progressState == $id}class="progress-bar-current-state"{/if}>{$label.progressLabel|escape:'html':'UTF-8'}</li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    {/foreach}
{/if}
