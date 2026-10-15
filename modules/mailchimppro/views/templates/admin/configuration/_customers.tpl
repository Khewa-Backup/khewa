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
 <div v-cloak v-if="validApiKey" class="panel panel-customers" v-show="currentPage === 'customers'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-users  la-2x"></i>
            {l s='Customers' mod='mailchimppro'}
        </span>
        <div class="panel-heading-action">
            <button id="desc-attribute_group-new"
                    v-on:click="saveSettings"
                    v-if="validApiKey"
                    title="{l s='Save settings' mod='mailchimppro'}"
                    class="list-toolbar-btn btn btn-success">
                <i class="las la-save la-2x"></i>
                <span>{l s='Save' mod='mailchimppro'}</span>
            </button>
        </div>
    </h3>
    <div class="panel-body">
        <h2>{l s='Sync customers' mod='mailchimppro'}</h2>
        <div class="form-group">
            <div class="clearfix"></div>
            <div class="btn-group large" role="group">
                <button type="button" class="btn " v-on:click="syncCustomers = true"
                        :class="syncCustomers == true ? 'btn-primary' : 'btn-default'">
                    {l s='Yes' mod='mailchimppro'}
                </button>
                <button type="button" class="btn " v-on:click="syncCustomers = false"
                        :class="syncCustomers == false ? 'btn-primary' : 'btn-default'">
                    {l s='No' mod='mailchimppro'}
                </button>
            </div>
        </div>
        <hr>
        <div :class="syncCustomers == true ? 'sync-settings-container' : 'sync-settings-container no-sync-type'">
            <h2>{l s='Filter customers to sync' mod='mailchimppro'}</h2>
            <div class="form-group">
                <label>{l s='By status' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn "
                            v-on:click="customerSyncFilterEnabled = [0,1]"
                            :class="[arrayEquals(customerSyncFilterEnabled, [0,1]) ? 'btn-primary' : 'btn-default']">
                        {l s='All' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn "
                            v-on:click="customerSyncFilterEnabled = [1]"
                            :class="[arrayEquals(customerSyncFilterEnabled, [1]) ? 'btn-primary' : 'btn-default']">
                        {l s='Only active' mod='mailchimppro'}
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label>{l s='By newsletter opt in status' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn "
                            v-on:click="customerSyncFilterNewsletter = [0, 1]"
                            :class="[arrayEquals(customerSyncFilterNewsletter, [0,1]) ? 'btn-primary' : 'btn-default']">
                        {l s='All (transactional + opted-in)' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn "
                            v-on:click="customerSyncFilterNewsletter = [1]"
                            :class="[arrayEquals(customerSyncFilterNewsletter, [1]) ? 'btn-primary' : 'btn-default']">
                        {l s='Only opted-in' mod='mailchimppro'}
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label>{l s='By registration date' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                <span>{l s='Only from:' mod='mailchimppro'} </span>
                <div class="btn-group" role="group">
                    <button type="button" class="btn" v-on:click="customerSyncFilterPeriod = 'onlyNew'"
                            :class="[customerSyncFilterPeriod === 'onlyNew' ? 'btn-primary' : 'btn-default']">
                        {l s='New customers' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn" v-on:click="customerSyncFilterPeriod = '-1 months'"
                            :class="[customerSyncFilterPeriod === '-1 months' ? 'btn-primary' : 'btn-default']">
                        {l s='Last 1 month' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn" v-on:click="customerSyncFilterPeriod = '-3 months'"
                            :class="[customerSyncFilterPeriod === '-3 months' ? 'btn-primary' : 'btn-default']">
                        {l s='Last 3 months' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn" v-on:click="customerSyncFilterPeriod = '-6 months'"
                            :class="[customerSyncFilterPeriod === '-6 months' ? 'btn-primary' : 'btn-default']">
                        {l s='Last 6 months' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn" v-on:click="customerSyncFilterPeriod = '-1 year'"
                            :class="[customerSyncFilterPeriod === '-1 year' ? 'btn-primary' : 'btn-default']">
                        {l s='Last 1 year' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn" v-on:click="customerSyncFilterPeriod = 'all'"
                            :class="[customerSyncFilterPeriod === 'all' ? 'btn-primary' : 'btn-default']">
                        {l s='All' mod='mailchimppro'}
                    </button>
                </div>
                <div class="clearfix"></div>
                <ul class="form-text text-muted list-unstyled" style="margin-top: 10px">
                    <li>
                        <b>{l s='Only new customers' mod='mailchimppro'}:</b>
                        {l s='Customers registered afer the synchronization will be added to Mailchimp' mod='mailchimppro'}
                    </li>
                    <li>
                        <b>{l s='Only from last 1 month' mod='mailchimppro'}:</b>
                        {l s='Customers registered from the last 1 months and the new customers will be added to Mailchimp' mod='mailchimppro'}
                    </li>
                    <li>
                        <b>{l s='Only from last 3 month' mod='mailchimppro'}:</b>
                        {l s='Customers registered from the last 3 months and the new customers will be added to Mailchimp' mod='mailchimppro'}
                    </li>
                    <li>
                        <b>{l s='Only from last 6 month' mod='mailchimppro'}:</b>
                        {l s='Customers registered from the last 6 months and the new customers will be added to Mailchimp' mod='mailchimppro'}
                    </li>
                    <li>
                        <b>{l s='Only from last 1 year' mod='mailchimppro'}:</b>
                        {l s='Customers registered from the last 1 year and the new customers will be added to Mailchimp' mod='mailchimppro'}
                    </li>
                    <li>
                        <b>{l s='All' mod='mailchimppro'}:</b>
                        {l s='All the customers will be added to Mailchimp' mod='mailchimppro'}
                    </li>
                </ul>
            </div>
            <hr>
            <h2>{l s='Tag customers' mod='mailchimppro'}</h2>
            <div class="form-group">
                <label>{l s='Add customer groups as tag' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                {literal}
                    <Multiselect
                        v-model="customerSyncTagDefaultGroup"
                        :can-deselect="false"
                        :can-clear="false"
                        :mode="'single'"
                        :options="[{'label': 'No','value': 'no'},{'label': 'Yes','value': 'all'},{'label': 'Default group only','value': 'default'}]"
                    >
                    </Multiselect>
                {/literal}
            </div>
            <div class="form-group">
                <label>{l s='Add customer gender as tag' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                {literal}
                    <Multiselect
                        v-model="customerSyncTagGender"
                        :can-deselect="false"
                        :can-clear="false"
                        :mode="'single'"
                        :options="[{'label': 'Yes','value': true},{'label': 'No','value': false}]"
                    >
                    </Multiselect>
                {/literal}
            </div>
            <div class="form-group">
                <label>{l s='Add customer language as tag' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                {literal}
                    <Multiselect
                        v-model="customerSyncTagLanguage"
                        :can-deselect="false"
                        :can-clear="false"
                        :mode="'single'"
                        :options="[{'label': 'Yes','value': true},{'label': 'No','value': false}]"
                    >
                    </Multiselect>
                {/literal}
            </div>
        </div>

        <div class="col-lg-12">
            <hr>
            <h2>{l s='Company' mod='mailchimppro'}</h2>
            <p class="help-block">
                {l s='The customer company is synced to the COMPANY field of your Mailchimp audience, so you can segment business customers. Contacts without a company are left untouched — an empty value is never sent, so anything you maintain in Mailchimp by hand stays intact.' mod='mailchimppro'}
            </p>

            <div class="form-group">
                <label>{l s='If the customer has no company, use the one from their address' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                {literal}
                    <Multiselect
                        v-model="customerSyncCompanyFromAddress"
                        :can-deselect="false"
                        :can-clear="false"
                        :mode="'single'"
                        :options="[{'label': 'Yes','value': true},{'label': 'No','value': false}]"
                    >
                    </Multiselect>
                {/literal}
                <p class="help-block">
                    {l s='Used only as a fallback — the customer own company field always wins when it is filled in. Off by default: a company on a delivery address does not necessarily mean the customer is a business, and including it inflates a professional-customers segment. Turn it on when the address is where your company data actually lives.' mod='mailchimppro'}
                </p>
            </div>

            <div v-cloak v-if="!b2bModeEnabled && !customerSyncCompanyFromAddress" class="alert alert-info">
                {l s='B2B mode is off in this shop, so customers have no company field of their own and nothing will be sent to Mailchimp. Set the option above to Yes to use the company from their address instead, or enable B2B mode in Shop Parameters > Customer Settings.' mod='mailchimppro'}
            </div>

            <div v-cloak v-if="companyFieldStatus === 'missing'" class="alert alert-warning mc-company-field-alert">
                <p>
                    {l s='The COMPANY field is missing from your Mailchimp audience, so company values are not stored.' mod='mailchimppro'}
                </p>
                <p v-cloak v-if="companyFieldError === 'merge-field-cap-reached'">
                    {l s='It could not be created because the audience already has the maximum of 30 fields. Delete an unused field in Mailchimp, then try again here.' mod='mailchimppro'}
                </p>
                <button type="button" class="btn btn-default"
                        v-on:click="createCompanyField"
                        :disabled="companyFieldWorking">
                    <i class="las la-plus"></i>
                    {l s='Create COMPANY field' mod='mailchimppro'}
                </button>
            </div>
        </div>
    </div>
</div>
