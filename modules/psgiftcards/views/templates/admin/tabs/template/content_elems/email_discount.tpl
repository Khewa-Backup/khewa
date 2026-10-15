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

<div id="email_discount" class="row clear">
    <div class="col-lg-12 col-xs-12">
        <label>{l s='Add an image' mod='psgiftcards'}</label>
    </div>
    <div class="col-lg-10 col-xs-10">
        <form action="#" class="dz-clickable importDropzone lang_{$lang.id_lang} {if $lang.id_lang == $employeeLangId}active{/if}">
            <div class="loader"></div>
            <div class="module-import-start">
                <i class="module-import-start-icon material-icons">cloud_upload</i><br>
                <p class="module-import-start-main-text">
                    {l s='Drop image here or' mod='psgiftcards'} <a href="#" class="module-import-start-select-manual">{l s='select file' mod='psgiftcards'}</a>
                </p>
                <p class="module-import-start-footer-text">
                    {l s='Recommended size 800 * 800px for default theme, JPG, GIF or PNG format.' mod='psgiftcards'}
                </p>
            </div>
            <div class="module-import-failure">
                <i class="module-import-failure-icon material-icons">error</i><br>
                <p class="module-import-failure-msg">{l s='Oops... Upload failed.' mod='psgiftcards'}</p>
                <a href="#" class="module-import-failure-details-action">{l s='What happened?' mod='psgiftcards'}</a>
                <div class="module-import-failure-details">{l s='An error has occurred.' mod='psgiftcards'}</div>
                <p>
                    <a class="module-import-failure-retry btn btn-tertiary" href="#">{l s='Try again' mod='psgiftcards'}</a>
                </p>
            </div>
            <div class="module-import-success">
                <i class="module-import-success-icon material-icons">done</i><br>
                <p class="module-import-success-msg"></p>
            </div>
            <input type="hidden" name="action" value="UploadChildTheme" />
            <div class="dz-default dz-message"><span></span></div><input name="childthemefile" type="file" class="dz-hidden-input" style="visibility: hidden; position: absolute; top: 0px; left: 0px; height: 0px; width: 0px;">
        </form>
    </div>
</div>
