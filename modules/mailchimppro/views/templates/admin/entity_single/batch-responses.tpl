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
<div class="accordion" id="batch-responses-accordion">
    {foreach $responses as $key => $response}
        <div class="accordion-item {if $response->status_code == 200}accordion-item-success{else}accordion-item-warning{/if}">
            <div class="accordion-header" id="batch-response-heading-{$key|escape:'htmlall':'UTF-8'}" data-toggle="collapse" data-target="#batch-response-body-{$key|escape:'htmlall':'UTF-8'}" aria-expanded="false" aria-controls="batch-response-body-{$key|escape:'htmlall':'UTF-8'}">
                <div class="accordion-title h3">
                    <span>
                        {if $response->status_code == 200}
                            <i class="material-icons icon-arrow-forward text-success">&#xe86c;</i>
                        {else}
                            <i class="material-icons icon-arrow-forward text-warning">&#xe002;</i>
                        {/if}
                        {$response->operation_id|escape:'htmlall':'UTF-8'}
                    </span>
                    <div>
                        <i class="material-icons icon-down">&#xe313;</i>
                    </div>
                </div>
            </div>
            <div id="batch-response-body-{$key|escape:'htmlall':'UTF-8'}" class="accordion-collapse collapse" aria-labelledby="batch-response-heading-{$key|escape:'htmlall':'UTF-8'}">
                <div class="accordion-body">
                    <pre>{var_export($response->response)|escape:'htmlall':'UTF-8'}</pre>
                </div>
            </div>
        </div>
        {if !$response@last}<hr>{/if}
    {/foreach}
</div>
