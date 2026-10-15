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
{if isset($content)}
    <div class="mailchimp-pro-content-container">
        {* BO controller-composed HTML (navbar + entity-list + pagination); each piece is escape-controlled at its own render site. *}
        {$content nofilter}
    </div>
{/if}