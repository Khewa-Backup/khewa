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
<div v-cloak v-if="validApiKey" class="panel panel-advanced-settings" v-show="currentPage === 'advanced-settings'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-cog la-2x"></i>
            {l s='Advanced settings' mod='mailchimppro'}
        </span>
    </h3>
    <div class="panel-body">
        <div class="form-group">
            <label>{l s='Sync type' mod='mailchimppro'}</label>
            <div class="clearfix"></div>
            {* *********
            {literal}
                <Multiselect
                    v-model="cronjobBasedSync"
                    :can-deselect="false"
                    :can-clear="false"
                    :mode="'single'"
                    :options="[{'label': 'Hook based','value': false},{'label': 'Cronjob based','value': true}]"
                >
                </Multiselect>
            {/literal}
            ********* *}
            <div class="btn-group" role="group">
                <button type="button" class="btn " v-on:click="cronjobBasedSync = false"
                        :class="cronjobBasedSync == false ? 'btn-primary' : 'btn-default'">
                    {l s='Instant/Hook based' mod='mailchimppro'}
                </button>
                <button type="button" class="btn " v-on:click="cronjobBasedSync = true"
                        :class="cronjobBasedSync == true ? 'btn-primary' : 'btn-default'">
                    {l s='Cronjob based' mod='mailchimppro'}
                </button>
            </div>
        </div>
        <div class="form-group">
            <label>{l s='Multi instance mode' mod='mailchimppro'}</label>
            <div class="clearfix"></div>
            {literal}
                <Multiselect
                    v-model="multiInstanceMode"
                    :can-deselect="false"
                    :can-clear="false"
                    :mode="'single'"
                    :options="[{'label': 'No','value': false},{'label': 'Yes','value': true}]"
                >
                </Multiselect>
            {/literal}
            <p class="form-text text-muted">
                {l s='Prefix the shop ID with the domain to enable a multiple Prestashop instances on the same account. REQUIRES RE-SYNC after change' mod='mailchimppro'}
            </p>
        </div>
        {if isset($configurationUrl) && $configurationUrl}
            <hr v-if="listId && storeSynced">
            <div v-if="listId && storeSynced" class="form-group">
                <label>{l s='Delete all e-commerce data for the current store from your Mailchimp account' mod='mailchimppro'}</label>
                <div class="alert alert-danger">
                    <p>{l s='Once you delete these datas, there is no going back. Please be certain.' mod='mailchimppro'}</p>
                </div>
                <div>
                    <button v-on:click="deleteMailchimpEcommerceData" class="btn btn-default delete-ecommerce-data">
                        {l s='Delete' mod='mailchimppro'}
                    </button>
                </div>
            </div>
        {/if}

        {if isset($configurationUrl) && $configurationUrl}
            {include file="./_fetch_script_block.tpl"}
        {/if}
    </div>
</div>

