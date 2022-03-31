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

<div class="panel product-tab-content">
    <h3>
        <i class="icon-gift"></i>
        {l s='Giftcards module' mod='psgiftcards'}
    </h3>
    <div id="gc">
        <div class="panel-body">
            <section class="col-lg-xslg-12">
                <p>{l s='Please follow the tutorial below for creating a Gift Card : ' mod='psgiftcards'}</p>
            </section>
            <section class="col-lg-12 col-xs-12">
                <div class="section-title col-lg-12 col-xs-12">
                    <div class="col-lg-1 col-xs-1">
                        <span class="puce">1</span>
                    </div>
                    <div class="col-lg-11 col-xs-11">
                        <p>{l s='Enter your product name in the dedicated field above. I.e : Gift Card.' mod='psgiftcards'}</p>
                    </div>
                </div>
            </section>
            <section class="col-lg-12 col-xs-12">
                <div class="section-title col-lg-12 col-xs-12">
                    <div class="col-lg-1 col-xs-1">
                        <span class="puce">2</span>
                    </div>
                    <div class="col-lg-11 col-xs-11">
                        <p>{l s='Select Virtual Product in the drop-down menu.' mod='psgiftcards'}</p>
                    </div>
                </div>
            </section>
            <section class="col-lg-12 col-xs-12">
                <div class="section-title col-lg-12 col-xs-12">
                    <div class="col-lg-1 col-xs-1">
                        <span class="puce">3</span>
                    </div>
                    <div class="col-lg-11 col-xs-11">
                        <p>{l s='Save at the bottom of the page.' mod='psgiftcards'}</p>
                    </div>
                </div>
            </section>
            <section class="col-lg-12 col-xs-12">
                <div class="section-title col-lg-12 col-xs-12">
                    <div class="col-lg-1 col-xs-1">
                        <span class="puce">4</span>
                    </div>
                    <div class="col-lg-11 col-xs-11">
                        <p>{l s='Tag this product as a gift card.' mod='psgiftcards'}</p>
                    </div>
                </div>
            </section>
            <section class="col-lg-12 col-xs-12">
                <div class="section-title col-lg-12 col-xs-12">
                    <div class="col-lg-1 col-xs-1">
                        <span class="puce">5</span>
                    </div>
                    <div class="col-lg-11 col-xs-11">
                        <p>{l s='Configure your product in every tab as if it would be a standard product.' mod='psgiftcards'}</p>
                    </div>
                </div>
            </section>
            <section class="col-lg-12 col-xs-12">
                <div class="section-title col-lg-12 col-xs-12">
                    <div class="col-lg-1 col-xs-1">
                        <span class="puce">6</span>
                    </div>
                    <div class="col-lg-11 col-xs-11">
                        <p>{l s='Save your configurations.' mod='psgiftcards'}</p>
                    </div>
                </div>
            </section>
            <section class="col-lg-12 col-xs-12">
                <div class="section-title col-lg-12 col-xs-12">
                    <div class="col-lg-1 col-xs-1">
                        <span class="puce">7</span>
                    </div>
                    <div class="col-lg-11 col-xs-11">
                        <p>{l s='Switch, thanks to the radio button below, your product on online mode' mod='psgiftcards'}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>

{if $ps_version eq 1} {* IF ON PRESTAHOP 1.7 *}
    <div>
        <div class="col-md-12 form-group">
            <div class="row">
                <div class="col_xl-2 col-lg-4">
                    <label class="form-control-label">
                        <h2 class="addons-inline-block">{l s='Tag this product as a gift card' mod='psgiftcards'}</h2>
                    </label>
                    <div class="addons-inline-block gc-margin-left">
                        <div id="giftcardSwitchTag" class="switch-field large">
                            <input type="radio" class="giftcard-type" id="{$id_product|escape:'htmlall':'UTF-8'}_off" @click="updateTag({$id_product|escape:'htmlall':'UTF-8'})" name="giftcard_tag" value="0" :checked="isTag == 0"/>
                            <label for="{$id_product|escape:'htmlall':'UTF-8'}_off">{l s='Untag' mod='psgiftcards'}</label>
                            <input type="radio" class="giftcard-type" id="{$id_product|escape:'htmlall':'UTF-8'}_on" @click="updateTag({$id_product|escape:'htmlall':'UTF-8'})" name="giftcard_tag" value="1" :checked="isTag == 1"/>
                            <label for="{$id_product|escape:'htmlall':'UTF-8'}_on">{l s='Tag' mod='psgiftcards'}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{else} {* IF ON PRESTAHOP 1.6 *}
    {* TAG AS GIFTCARD *}
    <div class="form-group">
        <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
            <div class="text-right">
                <label class="boldtext control-label">{l s='Tag this product as a gift card' mod='psgiftcards'}</label>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">
                <div id="giftcardSwitchTag" class="switch-field large">
                    <input type="radio" class="giftcard-type" id="giftcard_type_{$id_product|escape:'htmlall':'UTF-8'}_off" @click="updateTag({$id_product|escape:'htmlall':'UTF-8'})" name="giftcard_tag" value="0" :checked="isTag == 0"/>
                    <label for="giftcard_type_{$id_product|escape:'htmlall':'UTF-8'}_off">{l s='Untag' mod='psgiftcards'}</label>
                    <input type="radio" class="giftcard-type" id="giftcard_type_{$id_product|escape:'htmlall':'UTF-8'}_on" @click="updateTag({$id_product|escape:'htmlall':'UTF-8'})" name="giftcard_tag" value="1" :checked="isTag == 1"/>
                    <label for="giftcard_type_{$id_product|escape:'htmlall':'UTF-8'}_on">{l s='Tag' mod='psgiftcards'}</label>
                </div>
        </div>
    </div>
{/if}
</div>
{literal}
<script type="text/javascript">
    var id_product = "{/literal}{$id_product|escape:'htmlall':'UTF-8'}{literal}";
    var successTag = "{/literal}{l s='The product has been tagged as gift card !' mod='psgiftcards'}{literal}";
    var successUntag = "{/literal}{l s='The product has been removed as gift card !' mod='psgiftcards'}{literal}";
    var warningTag = "{/literal}{l s='The product needs to be a virtual product in order to tag it as gift card.' mod='psgiftcards'}{literal}";
    var errorTag = "{/literal}{l s='Error ! Product cannot be tagged.' mod='psgiftcards'}{literal}";
    var controller_url = "{/literal}{$controller_url|escape:'htmlall':'UTF-8'}{literal}";
    var ps_version = "{/literal}{$ps_version|escape:'htmlall':'UTF-8'}{literal}";
</script>
{/literal}
