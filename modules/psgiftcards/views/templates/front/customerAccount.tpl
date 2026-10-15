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
{if $ps_version} {*if ps is 1.7*}
    <a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="identity-link" href="{$link->getModuleLink('psgiftcards', 'Giftcards')}">
        <span class="link-item">
            <i class="material-icons">card_giftcard</i> {l s='My gift cards' mod='psgiftcards'}
        </span>
    </a>
{else} {*if ps is 1.6*}
    <li>
        <a href="{$link->getModuleLink('psgiftcards', 'Giftcards')}" title="{l s='My gift cards' mod='psgiftcards'}">
            <i class="fa fa-gift"></i>
            <span>{l s='My gift cards' mod='psgiftcards'}</span>
        </a>
    </li>
{/if}
