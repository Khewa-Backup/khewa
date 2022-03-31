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

<section id="save_reminder" class="cap_popin">
    <div class="content_cancel">
        <h2>{l s='Error !' mod='psgiftcards'}</h2>
        <div class="error_text col-lg-12">
            <p>{l s='The reminder could not be created for the following reasons :' mod='psgiftcards'}</p>
            <div class="target_error_list error_list">
                <p>{l s='Errors in target step: ' mod='psgiftcards'}</p>
                <ul></ul>
            </div>
            <div class="discount_error_list error_list">
                <p>{l s='Errors in discount step: ' mod='psgiftcards'}</p>
                <ul></ul>
            </div>
            <div class="template_error_list error_list">
                <p>{l s='Errors in template step: ' mod='psgiftcards'}</p>
                <ul></ul>
            </div>
        </div>
        <div class="buttons d-flex justify-content-end col-lg-12">
            <button name="ok" type="submit" class="btn btn-primary">{l s='I understand' mod='psgiftcards'}</button>
        </div>
    </div>
</section>