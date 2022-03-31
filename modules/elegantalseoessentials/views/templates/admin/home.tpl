{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2022, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div class="elegantalBootstrapWrapper">
    <div class="row">
        <div class="col-xs-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
            <div class="row elegantal_home_list_btns">
                <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                    <a class="elegantal_home_list_btn" href="{$adminUrl|escape:'html':'UTF-8'}&event=metaTagsList">
                        <span class="btn_icon"><i class="icon-search"></i></span>
                        <span class="btn_text">{l s='SEO Meta Tags' mod='elegantalseoessentials'}</span>
                    </a>
                </div>
                <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                    <a class="elegantal_home_list_btn" href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltList">
                        <span class="btn_icon"><i class="icon-image"></i></span>
                        <span class="btn_text">{l s='SEO Images Alt' mod='elegantalseoessentials'}</span>
                    </a>
                </div>
                <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                    <a class="elegantal_home_list_btn" href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList">
                        <span class="btn_icon"><i class="icon-code"></i></span>
                        <span class="btn_text">{l s='HTML Blocks' mod='elegantalseoessentials'}</span>
                    </a>
                </div>
                <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                    <a class="elegantal_home_list_btn" href="{$adminUrl|escape:'html':'UTF-8'}&event=redirectsList">
                        <span class="btn_icon"><i class="icon-random"></i></span>
                        <span class="btn_text">{l s='URL Redirects' mod='elegantalseoessentials'}</span>
                    </a>
                </div>
                <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                    <a class="elegantal_home_list_btn" href="{$adminUrl|escape:'html':'UTF-8'}&event=editSettingsSitelinksSearchbox">
                        <span class="btn_icon"><i class="icon-google"></i></span>
                        <span class="btn_text">{l s='Sitelinks Search' mod='elegantalseoessentials'}</span>
                    </a>
                </div>
                <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                    <a class="elegantal_home_list_btn" href="{$adminUrl|escape:'html':'UTF-8'}&event=editSettingsCanonical">
                        <span class="btn_icon"><i class="icon-link"></i></span>
                        <span class="btn_text">{l s='Canonical URL' mod='elegantalseoessentials'}</span>
                    </a>
                </div>
                <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                    <a class="elegantal_home_list_btn" href="{$adminUrl|escape:'html':'UTF-8'}&event=editSettingsHreflang">
                        <span class="btn_icon"><i class="icon-language"></i></span>
                        <span class="btn_text">{l s='Hreflang tags' mod='elegantalseoessentials'}</span>
                    </a>
                </div>
                <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                    <a class="elegantal_home_list_btn" href="{$adminUrl|escape:'html':'UTF-8'}&event=editSettingsNextPrev">
                        <span class="btn_icon"><i class="icon-copy"></i></span>
                        <span class="btn_text">{l s='next/prev tags' mod='elegantalseoessentials'}</span>
                    </a>
                </div>
                <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                    <a class="elegantal_home_list_btn" href="{$adminUrl|escape:'html':'UTF-8'}&event=pageNotFoundList">
                        <span class="btn_icon"><i class="icon-warning"></i></span>
                        <span class="btn_text">{l s='404 Error Pages' mod='elegantalseoessentials'}</span>
                    </a>
                </div>
                {if $documentationUrls}
                    <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                        <a class="elegantal_home_list_btn dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" target="_blank">
                            <span class="btn_icon">
                                <i class="icon-file-text-o"></i>
                                <span class="elegantal_version_num">v{$version|escape:'html':'UTF-8'}</span>
                            </span>
                            <span class="btn_text">{l s='Documentation' mod='elegantalseoessentials'}</span>
                        </a>
                        <ul class="dropdown-menu" style="left:0; right: auto;">
                            {foreach from=$documentationUrls key=docLang item=documentationUrl}
                                <li>
                                    <a href="{$documentationUrl|escape:'html':'UTF-8'}" target="_blank">
                                        {foreach from=$languages item=lang}
                                            {if $lang.iso_code == $docLang}
                                                <img src="{$img_lang_dir|escape:'html':'UTF-8'}{$lang.id_lang|intval}.jpg" style="max-width:16px" />&nbsp;
                                                {break}
                                            {/if}
                                        {/foreach}
                                        {if $docLang == 'en'}
                                            {l s='English' mod='elegantalseoessentials'}
                                        {elseif $docLang == 'fr'}
                                            {l s='French' mod='elegantalseoessentials'}
                                        {elseif $docLang == 'de'}
                                            {l s='German' mod='elegantalseoessentials'}
                                        {elseif $docLang == 'it'}
                                            {l s='Italian' mod='elegantalseoessentials'}
                                        {elseif $docLang == 'es'}
                                            {l s='Spanish' mod='elegantalseoessentials'}
                                        {elseif $docLang == 'ru'}
                                            {l s='Russian' mod='elegantalseoessentials'}
                                        {else}
                                            {$docLang|escape:'html':'UTF-8'}
                                        {/if}
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
                <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                    <a class="elegantal_home_list_btn" href="{$rateModuleUrl|escape:'html':'UTF-8'}" target="_blank">
                        <span class="btn_icon"><i class="icon-star"></i></span>
                        <span class="btn_text">{l s='Rate Module' mod='elegantalseoessentials'}</span>
                    </a>
                </div>
                <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
                    <a class="elegantal_home_list_btn" href="{$contactDeveloperUrl|escape:'html':'UTF-8'}" target="_blank">
                        <span class="btn_icon"><i class="icon-envelope-o"></i></span>
                        <span class="btn_text">{l s='Contact Us' mod='elegantalseoessentials'}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>