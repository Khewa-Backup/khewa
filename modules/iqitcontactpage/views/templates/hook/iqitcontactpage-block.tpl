{*
* 2017 IQIT-COMMERCE.COM
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement
*
* @author    IQIT-COMMERCE.COM <support@iqit-commerce.com>
* @copyright 2017 IQIT-COMMERCE.COM
* @license   Commercial license (You can not resell or redistribute this software.)
*
*}

{block name='iqitcontactpage-info-block'}
    <div class="col col-md block block-toggle block-iqitcontactpage js-block-toggle">
        <p class="block-title h5"><span>{l s='Contact us' mod='iqitcontactpage'}</span></p>
        <div class="block-content">
            {include 'module:iqitcontactpage/views/templates/hook/_partials/content.tpl'}
            {if $content}
                <hr/>
                <div class="part">
                    {$content nofilter}
                </div>
            {/if}
        </div>
    </div>
{/block}
