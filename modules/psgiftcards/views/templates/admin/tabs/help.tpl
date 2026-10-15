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

<div class="panel col-lg-10 right-panel">
    <h3>
        <i class="fa fa-question-circle"></i> {l s='Help for the psgiftcards module' mod='psgiftcards'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
    </h3>
    <div class="helpContentParent">
        <div class="helpContentLeft">
            <div class="left">
                <img src="{$logo_path|escape:'htmlall':'UTF-8'}" alt=""/>
            </div>
            <div class="right">
                <p><span class="data_label" style="color:#00aff0;"><b>{l s='This module allows you to create and sell gift cards to your clients on your website' mod='psgiftcards'}</b></span></p>
                <br>
                <div>
                    <div class="numberCircle">1</div>
                    <div class="numberCircleText">
                    <p class="numberCircleText">
                        {l s='Create an unlimited number of gift cards easily with your choice of amount and visuals, in the languages and currencies of your choice' mod='psgiftcards'}
                    </p>
                    </div>
                </div>
                <div>
                    <div class="numberCircle">2</div>
                    <div class="numberCircleText">
                    <p class="numberCircleText">
                        {l s='Choose your cards’ length of validity' mod='psgiftcards'}
                    </p>
                    </div>
                </div>
                <div>
                    <div class="numberCircle">3</div>
                    <div class="numberCircleText">
                    <p class="numberCircleText">
                        {l s='Customer-personalized gift cards (message, immediate or planned delivery, by email or downloading the gift card pdf, etc.)' mod='psgiftcards'}
                    </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="helpContentRight">
            <div class="helpContentRight-sub">
                <b>{l s='Need help ?' mod='psgiftcards'}</b><br>
                {l s='Find here the documentation of this module' mod='psgiftcards'}
                <a class="btn btn-primary" href="{$doc|escape:'htmlall':'UTF-8'}" target="_blank" style="margin-left:20px;" href="#">
                    <i class="fa fa-book"></i> {l s='Documentation' mod='psgiftcards'}</a>
                </a>
                <br><br>
                <div class="tab-pane panel" id="faq">
                    <div class="panel-heading"><i class="icon-question"></i> {l s='FAQ' mod='psgiftcards'}</div>
                    {foreach from=$apifaq item=categorie name='faq'}
                        <span class="faq-h1">{$categorie->title|escape:'htmlall':'UTF-8'}</span>
                        <ul>
                            {foreach from=$categorie->blocks item=QandA}
                                {if !empty($QandA->question)}
                                    <li>
                                        <span class="faq-h2"><i class="icon-info-circle"></i> {$QandA->question|escape:'htmlall':'UTF-8'}</span>
                                        <p class="faq-text hide">
                                            {$QandA->answer|escape:'htmlall':'UTF-8'|replace:"\n":"<br />"}
                                        </p>
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                        {if !$smarty.foreach.faq.last}<hr/>{/if}
                    {/foreach}
                </div>
                {l s='You couldn\'t find any answer to your question ?' mod='psgiftcards'}
                <b><a href="http://addons.prestashop.com/contact-form.php" target="_blank">{l s='Contact us on PrestaShop Addons' mod='psgiftcards'}</a></b>
            </div>
        </div>
    </div>
</div>
