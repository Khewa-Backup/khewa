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
{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='My gift cards' mod='psgiftcards'}
{/block}

{block name='page_content'}
<div class="col-xs-12">
    <h6>{l s='Here are the gift cards you\'ve placed since your account was created. You can configure them or consult their details.' mod='psgiftcards'}</h6>
    <br>

    <div id="giftcardList">
        {* GIFTCARD TITLE LIST *}
        <div v-if="giftcards == ''">
            <article class="alert alert-info" role="alert" data-alert="warning">
                <ul>
                    <li>{l s='You haven\'t ordered any gift cards yet.' mod='psgiftcards'}</li>
                </ul>
            </article>
        </div>
        <div id="giftcards" v-cloak v-if="giftcards != ''">

        <table class="table table-striped table-bordered table-labeled xs-sm-down">
            <thead class="thead-default">
                <tr>
                    <th class="giftcard-space giftcard-bold">{l s='Reference' mod='psgiftcards'}</th>
                    <th class="giftcard-space giftcard-bold">{l s='Amount' mod='psgiftcards'}</th>
                    <th class="giftcard-space giftcard-bold">{l s='Payment' mod='psgiftcards'}</th>
                    <th class="giftcard-space giftcard-bold">{l s='Gift card status' mod='psgiftcards'}</th>
                    <th class="giftcard-space giftcard-bold">{l s='Purchase date' mod='psgiftcards'}</th>
                    <th class="giftcard-space giftcard-bold"></th>
                </tr>
            </thead>
            <tbody v-for="(giftcard, index) in giftcards">
                <tr>
                    <td class="giftcard-space giftcard-bold">(( giftcard.orderRef ))</td>
                    <td class="giftcard-space">(( giftcard.amount ))</td>
                    <td class="giftcard-space">(( giftcard.payment ))</td>
                    <td class="giftcard-space">
                        <span v-if="giftcard.id_state == 1" class="status-waiting">(( giftcard.status ))</span>
                        <span v-if="giftcard.id_state == 2" class="status-configure">(( giftcard.status ))</span>
                        <span v-if="giftcard.id_state == 6" class="status-configure">(( giftcard.status ))</span>
                        <span v-if="giftcard.id_state == 3" class="status-scheduled">(( giftcard.status ))</span>
                        <span v-if="giftcard.id_state == 4" class="status-downloaded">(( giftcard.status ))</span>
                        <span v-if="giftcard.id_state == 5" class="status-sent">(( giftcard.status ))</span>
                    </td>
                    <td class="giftcard-space">(( giftcard.purchaseDate ))</td>
                    <td class="giftcard-space">
                        <div>
                            <button class="toggle-detail btn btn-primary">{l s='Configure' mod='psgiftcards'}</button>
                        </div>
                    </td>
                </tr>
                <tr v-if="giftcard.id_state == 2 || giftcard.id_state == 3 || giftcard.id_state == 4 || giftcard.id_state == 5 || giftcard.id_state == 6" class="giftcard-content giftcard-hide">
                    <td colspan="6">
                        <div>
                            <form :id="'giftcardForm_' + giftcard.id" v-on:keydown="onChange(giftcard.id, index)" v-on:change="onChange(giftcard.id, index)" v-on:submit.prevent novalidate>
                                <p>{l s='Please fill in the necessary information before sending the gift card :' mod='psgiftcards'}</p>
                                <br>
                                <div class="col-md-6 left">
                                    <div class="form-group row">
                                        <label class="col-md-4 form-control-label required">{l s='Recipient\'s name' mod='psgiftcards'} <label class="require">*</label></label>
                                        <div class="col-md-8">
                                            <input class="form-control" name="recipient_name" type="text" v-model="giftcards[index].recipientName" required="" :disabled="giftcard.id_state == 3 || giftcard.id_state == 4 || giftcard.id_state == 5">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-4 form-control-label required">{l s='Your name' mod='psgiftcards'} <label class="require">*</label></label>
                                        <div class="col-md-8">
                                            <input class="form-control" name="buyer_name" type="text" v-model="giftcards[index].buyerName" required="" :disabled="giftcard.id_state == 3 || giftcard.id_state == 4 || giftcard.id_state == 5">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-4 form-control-label required">{l s='Add a text' mod='psgiftcards'}</label>
                                        <div class="col-md-8">
                                            <textarea class="form-control" rows="3" name="text" :disabled="giftcard.id_state == 3 || giftcard.id_state == 4 || giftcard.id_state == 5" v-model="giftcards[index].text"></textarea>
                                        </div>
                                    </div>

                                    {* <div class="form-group row">
                                        <label class="col-md-4 form-control-label required">{l s='Choose a picture' mod='psgiftcards'} <label class="require">*</label></label>
                                        <div class="col-md-8">
                                            <div class="giftcard-image">
                                                <ul>
                                                    <li v-for="link in giftcard.image_link">
                                                        <label class="select">
                                                            <input type="radio" name="giftcard_image" :value="link" :checked="giftcard.image == link">
                                                            <img :src="link" width="90" height="90">
                                                        </label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div> *}

                                    <p class="require">*{l s='mandatory information' mod='psgiftcards'}</p>
                                    <br>
                                    <div v-if="checkFields[giftcard.id]">
                                    <article class="alert alert-danger" role="alert" data-alert="warning">
                                        <p style="color:#a94442;">{l s='Error(s)' mod='psgiftcards'} :</p>
                                        <ul v-for="(value, key) in checkFields[giftcard.id]">
                                            <li v-if="key =='validateRecipientName' && value !=true">(( value ))</li>
                                            <li v-if="key =='validateBuyerName' && value !=true">(( value ))</li>
                                            <li v-if="key =='recipientMail' && value !=true">(( value ))</li>
                                            <li v-if="key =='sendDate' && value !=true">(( value ))</li>
                                        </ul>
                                    </article>
                                    </div>
                                </div>

                                <div class="col-md-6 right">

                                    <div class="form-group row">
                                        <label class="col-md-4 form-control-label">{l s='Gift card type' mod='psgiftcards'}</label>
                                        <div class="col-md-8">
                                            {* <div class="switch-field large" v-if="giftcard.type === '0'"> *}
                                            <div class="switch-field large">
                                                <input type="radio" class="giftcard-type" :id="'giftcard_type_' + giftcard.id + '_on'" name="giftcard_type" value="1" v-model="giftcards[index].type" :checked="giftcard.type == 1" :disabled="giftcard.id_state == 3 || giftcard.id_state == 4 || giftcard.id_state == 5"/>
                                                <label :for="'giftcard_type_' + giftcard.id + '_on'">{l s='Send by mail' mod='psgiftcards'}</label>
                                                <input type="radio" class="giftcard-type" :id="'giftcard_type_' + giftcard.id + '_off'" name="giftcard_type" value="0" v-model="giftcards[index].type" :checked="giftcard.type == 0" :disabled="giftcard.id_state == 3 || giftcard.id_state == 4 || giftcard.id_state == 5"/>
                                                <label :for="'giftcard_type_' + giftcard.id + '_off'">{l s='Download PDF' mod='psgiftcards'}</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div :id="'mailForm_' + giftcard.id" v-show="giftcard.type == 1">
                                        <div class="form-group row">
                                            <label class="col-md-4 form-control-label required">{l s='Recipient\'s email' mod='psgiftcards'} <label class="require">*</label></label>
                                            <div class="col-md-8">
                                                <input :data-id="giftcard.id" class="form-control email" name="recipient_mail" type="text" v-model="giftcards[index].recipientMail" required="" :disabled="giftcard.id_state == 3 || giftcard.id_state == 4 || giftcard.id_state == 5">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-md-4 form-control-label">{l s='To be sent' mod='psgiftcards'}</label>
                                            <div class="col-md-8">
                                                <div class="switch-field medium">
                                                    <input type="radio" class="giftcard-date" :id="'giftcard_send_' + giftcard.id + '_on'" name="giftcard_send" value="1" v-model="giftcards[index].sendLater" :checked="giftcard.sendLater == 1" :disabled="giftcard.id_state == 3 || giftcard.id_state == 4 || giftcard.id_state == 5"/>
                                                    <label :for="'giftcard_send_' + giftcard.id + '_on'">{l s='Later' mod='psgiftcards'}</label>
                                                    <input type="radio" class="giftcard-date" :id="'giftcard_send_' + giftcard.id + '_off'" name="giftcard_send" value="0" v-model="giftcards[index].sendLater" :checked="giftcard.sendLater == 0" :disabled="giftcard.id_state == 3 || giftcard.id_state == 4 || giftcard.id_state == 5"/>
                                                    <label :for="'giftcard_send_' + giftcard.id + '_off'">{l s='Now' mod='psgiftcards'}</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div :id="'dateField_' + giftcard.id" v-show="giftcard.sendLater == 1">
                                            <div class="form-group row">
                                                <label class="col-md-4 form-control-label required">{l s='Choose the date' mod='psgiftcards'} <label class="require">*</label></label>
                                                <div class="col-md-8">
                                                    <input class="form-control" v-model="giftcards[index].send_date" name="send_date" type="date" required placeholder="MM/DD/YYYY" :readonly="giftcard.id_state == 4 || giftcard.id_state == 5">
                                                    <span class="form-control-comment">{l s='(Ex. : 31/05/1970)' mod='psgiftcards'}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <article v-if="giftcard.id_state == 6 && giftcard.hasChanged == 0" class="alert alert-warning" role="alert" data-alert="warning">
                                            <ul>
                                                <li class="giftcards-list">{l s='Please review your information before clicking on Send / Download / Schedule. You will not be able to modify your configuration afterwards' mod='psgiftcards'}.</li>
                                            </ul>
                                        </article>
                                        <article v-if="giftcard.id_state == 3" class="alert alert-info" role="alert" data-alert="warning">
                                            <ul>
                                                <li class="giftcards-list">{l s='The gift card has been scheduled for' mod='psgiftcards'} <b>(( giftcard.send_date ))</b>. {l s='As long as it is not sent, you can still change the date' mod='psgiftcards'}.</li>
                                            </ul>
                                        </article>
                                        <article v-if="giftcard.id_state == 4" class="alert alert-info" role="alert" data-alert="warning">
                                            <ul>
                                                <li class="giftcards-list">{l s='Congratulations, you have successfully downloaded your gift card ! You can download it again by clicking on the Download button' mod='psgiftcards'}.</li>
                                            </ul>
                                        </article>
                                        <article v-if="giftcard.id_state == 5" class="alert alert-info" role="alert" data-alert="warning">
                                            <ul>
                                                <li class="giftcards-list">{l s='Congratulations, you have successfully sent your gift card ! You cannot send it again but you are able to download it by clicking on the Download button' mod='psgiftcards'}.</li>
                                            </ul>
                                        </article>
                                    </div>
                                    <a v-if="giftcard.id_state == 2 || giftcard.hasChanged == 1" id="save" @click="save(giftcard.id, index)" class="btn btn-success giftcard-button">{l s='Save' mod='psgiftcards'}</a>
                                    <button v-if="(giftcard.id_state == 6 || giftcard.id_state == 3) && giftcard.type == 1 && giftcard.sendLater == 0 && giftcard.hasChanged == 0" @click="configureGiftcard(giftcard.id, 'sendMail', index)" class="btn btn-success giftcard-button" :disabled="giftcard.id_state == 3 || giftcard.id_state == 5">{l s='Send mail' mod='psgiftcards'}</button>
                                    <button v-if="(giftcard.id_state == 6 || giftcard.id_state == 3) && giftcard.type == 1 && giftcard.sendLater == 1 && giftcard.hasChanged == 0" @click="configureGiftcard(giftcard.id, 'scheduleMail', index)" class="btn btn-success giftcard-button" :disabled="giftcard.id_state == 5">{l s='Schedule mail' mod='psgiftcards'}</button>
                                    {if !empty($gfLang)}
                                    <a v-if="(giftcard.id_state == 6 || giftcard.id_state == 4 || giftcard.id_state == 5) && giftcard.type == 0 && giftcard.hasChanged == 0" id="generatePdf" @click="configureGiftcard(giftcard.id, 'pdf', index)" class="btn btn-success giftcard-button" :disabled="giftcard.id_state == 3">{l s='Download PDF' mod='psgiftcards'}</a>
                                    {/if}
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr v-if="giftcard.id_state == 1" class="giftcard-content giftcard-hide">
                    <td colspan="6">
                        <article class="alert alert-info" role="alert" data-alert="warning">
                            <ul>
                                <li style="background: none">{l s='Before configuring your gift card, the payment needs to be validated' mod='psgiftcards'}</li>
                            </ul>
                        </article>
                    </td>
                </tr>
            </tbody>
        </table>

        </div>
    </div>
</div>
{literal}
<script type="text/javascript">
    var front_controller = "{/literal}{$front_controller|escape:'htmlall':'UTF-8'}{literal}";
    var pdf_controller = "{/literal}{$pdf_controller|escape:'htmlall':'UTF-8'}{literal}"; var ps_version = "{/literal}{$ps_version|escape:'htmlall':'UTF-8'}{literal}";
    var token = "{/literal}{$token|escape:'htmlall':'UTF-8'}{literal}";
</script>
{/literal}
{/block}
