{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

<div id="modulecontent" class="clearfix">
    <div id="menu">
        <div class="col-lg-2">
            <div class="list-group" v-on:click.prevent>
                <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('conf') }" v-on:click="makeActive('conf')"><i class="fa fa-cogs"></i> {l s='Global configuration' mod='psgiftcards'}</a>
                <a href="#" id="emailconf" class="list-group-item" v-bind:class="{ 'active': isActive('mail') }" v-on:click="makeActive('mail')"><i class="fa fa-cogs"></i> {l s='Email configuration' mod='psgiftcards'}</a>
                <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('stat') }" v-on:click="makeActive('stat')"><i class="fa fa-sliders"></i> {l s='Gift card list & stats' mod='psgiftcards'}</a>
                <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('cron') }" v-on:click="makeActive('cron')"><i class="fa fa-sliders"></i> {l s='Cron tasks' mod='psgiftcards'}</a>
                {if ($apifaq != '')}
                    <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('faq') }" v-on:click="makeActive('faq')"><i class="fa fa-question-circle"></i> {l s='Help' mod='psgiftcards'}</a>
                {/if}
            </div>
            <div class="list-group" v-on:click.prevent>
                <a class="list-group-item"><i class="icon-info"></i> {l s='Version' mod='psgiftcards'} {$module_version|escape:'htmlall':'UTF-8'}</a>
            </div>
        </div>
    </div>

    {* list your admin tpl *}
    <div id="conf" class="giftcards_menu addons-hide">
        {include file="./tabs/configuration.tpl"}
    </div>

    <div id="mail" class="giftcards_menu addons-hide">
        {include file="./tabs/email.tpl"}
    </div>

    <div id="stat" class="giftcards_menu addons-hide">
        {include file="./tabs/manageGiftcards.tpl"}
    </div>

    <div id="cron" class="giftcards_menu addons-hide">
        {include file="./tabs/cron.tpl"}
    </div>

    <div id="faq" class="giftcards_menu addons-hide">
        {if ($apifaq != '')}
            {include file="./tabs/help.tpl"}
        {/if}
    </div>

</div>

{* Use this if you want to send php var to your js *}
{literal}
<script type="text/javascript">
    var currentPage = "{/literal}{$currentPage|escape:'htmlall':'UTF-8'}{literal}";
    var moduleAdminLink = "{/literal}{$moduleAdminLink|escape:'htmlall':'UTF-8'}{literal}";
    var controller_url = "{/literal}{$controller_url|escape:'htmlall':'UTF-8'}{literal}";
    var ps_version = "{/literal}{$ps_version|escape:'htmlall':'UTF-8'}{literal}";
    var enableGiftcard = "{/literal}{l s='The gift card has been enabled !' mod='psgiftcards'}{literal}";
    var disableGiftcard = "{/literal}{l s='The gift card has been disabled !' mod='psgiftcards'}{literal}";
    var untagProduct = "{/literal}{l s='The product has been untagged as gift card.' mod='psgiftcards'}{literal}";
    var imageRemove = "{/literal}{l s='The image has been removed.' mod='psgiftcards'}{literal}";

    var sweetAlertTitle = "{/literal}{l s='Are you sure?' mod='psgiftcards'}{literal}";
    var sweetAlertMessage = "{/literal}{l s='The product will be disabled and untagged.' mod='psgiftcards'}{literal}";
</script>
{/literal}
