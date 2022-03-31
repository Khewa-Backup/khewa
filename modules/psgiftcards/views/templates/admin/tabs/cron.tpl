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
    <div id="sliderManager">
    <h3>
        <i class="fa fa-tasks"></i> {l s='Configure your cron tasks' mod='psgiftcards'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
    </h3>

    <div rv-if="slider.sliderTotal | eq 0" class="alert alert-info" style="display:block;">
        <p>{l s='Configuration of the module is almost complete ! In order to send gift card by mail automatically, you need to set up a cron job which is a process that allows you to schedule regular tasks.' mod='psgiftcards'}<br /></p>
        <p>{l s='Choose one of the 3 options below to activate mails :' mod='psgiftcards'}<br /></p>
    </div>

    <h4 class="addons-title">1 / {l s='Automatic mails using an external free service (recommended option)' mod='psgiftcards'}</h4>
    <p class="addons-text">{l s='We recommend you to use an external free service like' mod='psgiftcards'} <a href="http://easycron.com" target="_blank">www.easycron.com</a> {l s='to set up your automatic mails.' mod='psgiftcards'}<br>
    {l s='You need to create one task with the following url :' mod='psgiftcards'}
    </p>
    <span style="background: #f1f1f1;padding: 8px;margin-left: 5%;margin-bottom: 40px;">{$cron_url}</span>

    <div>&nbsp;</div>
    <h4 class="addons-title">2 / {l s='Automatic mails (on your own server)' mod='psgiftcards'}</h4>
    <p class="addons-text">{l s='You can use your own server to register a cron job. We advise you to contact your host provider for instructions.' mod='psgiftcards'}<br /></p>

    <h4 class="addons-title">3 / {l s='Send mail manually' mod='psgiftcards'}</h4>
    <p class="addons-text" style="display: inline">{l s='You can also send mails manually, to do so, you should click on the button below' mod='psgiftcards'} :</p>
    <a href="{$ps_base_dir}/modules/psgiftcards/cron.php?token={$tokenCron|escape:'htmlall':'UTF-8'}&id_shop={$id_shop|escape:'htmlall':'UTF-8'}" target="_blank" class="btn btn-primary">{l s='Execute cron task' mod='psgiftcards'}</a>

    </div>
</div>
