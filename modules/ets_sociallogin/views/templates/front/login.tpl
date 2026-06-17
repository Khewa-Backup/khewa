{*
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*}
{extends file="page.tpl"}
{block name='page_content'}
    {if isset($css) && $css|trim!==''}
        <link rel="stylesheet" href="{$css|escape:'quotes':'UTF-8'}">
    {/if}
    <div class="solo_register_form">
        <form id="solo_register_form" class="defaultForm form-horizontal"{if isset($action) && $action} action="{$action|escape:'quotes':'UTF-8'}"{/if} method="post" enctype="multipart/form-data">
            <section class="form-fields">
                <div class="form-group row">
                    <div class="col-md-12">
                        <h3>{l s='This email is already registered' mod='ets_sociallogin'}</h3>
                        <p>
                            {l s='This email address ([1]%s[/1]) has already been registered. Please sign in to your account and then connect your social account. If you don\'t remember your password, you can click' sprintf=[$profile->email] tags=['<a href="javascript:void(0)">'] mod='ets_sociallogin'}&nbsp;
                            <a href="{$forgot_password_url nofilter}">{l s='"Forgot your password"' mod="ets_sociallogin"}</a>&nbsp;{l s='or contact us to reset it.' mod='ets_sociallogin'}
                        </p>
                    </div>
                </div>
            </section>
            <footer class="form-footer text-xs-center">
                <a class="btn btn-primary" href="{$login_url nofilter}">{l s='Sign in' mod='ets_sociallogin'}</a>
            </footer>
        </form>
        <a class="btn btn-default pull-left" href="{$home_url nofilter}"><span>&lt;</span>{l s='Back to home page'  mod='ets_sociallogin'}</a>
    </div>
{/block}