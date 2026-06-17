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
{if !empty($nets) && !empty($design)}

<div class="ets_solo_social_wrapper{if !empty($is_rtl)} rtl{/if} {if isset($design.ETS_SOLO_WIDGET_EVENT) && $design.ETS_SOLO_WIDGET_EVENT}{$design.ETS_SOLO_WIDGET_EVENT|escape:'html':'UTF-8'}{/if}{if $hook == 'slw' || $hook == 'alw' || ($hook=='admin' && ($pos == 'slw' || $pos == 'alw'))} slw_and_alw{/if} {$hook|escape:'html':'UTF-8'}{if isset($design.ETS_SOLO_WIDGET_POSITION) && $design.ETS_SOLO_WIDGET_POSITION} pos_{$design.ETS_SOLO_WIDGET_POSITION|escape:'quotes':'UTF-8'}{/if}{if isset($design.ETS_SOLO_HIDE_ON_MOBILE) && $design.ETS_SOLO_HIDE_ON_MOBILE} hide_on_mobile{else} mobile{/if}">
    {if $hook=='lgp' || $hook=='brp'}<div class="ets_solo_or"><span>{l s='Or login with' mod='ets_sociallogin'}</span></div>{/if}
    {if $hook == 'slw'}
        <div class="ets_solo_slide_widget">
            <span class="ets_solo_title">
            {if $is17}
                <i class="material-icons">perm_identity</i>
            {else}
                <i class="icon icon-user fa fa-user"></i>
            {/if}<br/>
            {l s='Sign In' mod='ets_sociallogin'}</span>
        </div>
        <style type="text/css">
            {literal}
            .ets_solo_slide_widget:not(.active){
                {/literal}{if isset($design.ETS_SOLO_STICKY_BTN_BG) && $design.ETS_SOLO_STICKY_BTN_BG|trim !== ''}background-color:{$design.ETS_SOLO_STICKY_BTN_BG|escape:'html':'UTF-8'}{/if}{literal};
                {/literal}{if isset($design.ETS_SOLO_STICKY_BTN_TEXT) && $design.ETS_SOLO_STICKY_BTN_TEXT|trim !== ''}color:{$design.ETS_SOLO_STICKY_BTN_TEXT|escape:'html':'UTF-8'}{/if}{literal};
            }{/literal}
            {literal}
            .ets_solo_slide_widget:not(.active):hover{
                {/literal}{if isset($design.ETS_SOLO_STICKY_BTN_HOVER) && $design.ETS_SOLO_STICKY_BTN_HOVER|trim !== ''}background-color:{$design.ETS_SOLO_STICKY_BTN_HOVER|escape:'html':'UTF-8'}{/if}{literal};
            }{/literal}
        </style>
    {/if}
    {if $hook=='alw' && !$design.ETS_SOLO_HIDE_ON_MOBILE}
        <div class="ets_solo_table">
        <div class="ets_solo_tablecell">
            <div class="ets_solo_tablecontent">
    {/if}
    {if $hook=='alw' && !$design.ETS_SOLO_HIDE_ON_MOBILE || $hook=='slw' && !$design.ETS_SOLO_HIDE_ON_MOBILE}
            <span class="ets_solo_account_close button" title="">
                <span class="text_close">{l s='Close' mod='ets_sociallogin'}</span>
            </span>
            <span class="ets_solo_account_close overlay">

            </span>
        {/if}
    <div class="ets_solo_wrapper_content{if $hook == 'slw' || $hook == 'alw' || ($hook=='admin' && ($pos == 'slw' || $pos == 'alw')) } slw_and_alw{/if} {$hook|escape:'html':'UTF-8'}">

        {if $hook != 'admin'}
            {if isset($design.ETS_SOLO_LOGIN_TITLE) &&  $design.ETS_SOLO_LOGIN_TITLE != ''}
            <h3 class="ets_solo_social_title">
                {$design.ETS_SOLO_LOGIN_TITLE|escape:'html':'UTF-8'}
            </h3>
            {/if}
        {else}
            <h3 class="ets_solo_social_title">
                {l s='Preview login buttons' mod='ets_sociallogin'}
            </h3>
        {/if}

        {if $hook == 'admin'}<h4 class="ets_solo_title">{$design.ETS_SOLO_LOGIN_TITLE|escape:'html':'UTF-8'}</h4>{/if}
        <ul class="ets_solo_social">
            {assign var="NETWORKS_ORDER" value = explode(',', $design.ETS_SOLO_NETWORKS_ORDER)}
            {assign var="SOCIAL_TO_USE" value = in_array('all', $design.ETS_SOLO_SOCIAL_TO_USE)}
            {assign var="SOCIAL_GOOGLE_ENABLED" value = in_array('all', $design.ETS_SOLO_SOCIAL_TO_USE) || in_array('gl', $design.ETS_SOLO_SOCIAL_TO_USE)}
            {if $NETWORKS_ORDER|count > 1}{foreach from=$NETWORKS_ORDER item='net'}
                {assign var="SOCIAL_TO_USE_IK" value=($SOCIAL_TO_USE || in_array($net, $design.ETS_SOLO_SOCIAL_TO_USE))}
                {if isset($socials.$net) &&  (in_array($socials.$net.label, $nets) && $SOCIAL_TO_USE_IK || $hook == 'admin')}
                    <li title="{$socials.$net.name|escape:'html':'UTF-8'}" class="ets_solo_social_item item_type_{$design.ETS_SOLO_LOGIN_BUTTON_TYPE|escape:'html':'UTF-8'} {$socials.$net.label|lower|escape:'html':'UTF-8'}{if in_array($socials.$net.label, $nets) && $SOCIAL_TO_USE_IK} active{/if} {if $SOCIAL_GOOGLE_ENABLED && $socials.$net.id_option=='gl'}google_new_desginer {if $design.ETS_SOLO_GOOGLE_THEME}{$design.ETS_SOLO_GOOGLE_THEME|escape:'html':'UTF-8'}{else}light{/if}{/if}"
                        {if $hook == 'admin'}data-group="{$net|escape:'quotes':'UTF-8'}"{/if} data-auth="{$socials.$net.label|escape:'html':'UTF-8'}">

                        <span class="ets_solo_social_btn {$design.ETS_SOLO_BUTTON_SIZE|escape:'html':'UTF-8'} {$design.ETS_SOLO_BORDER|escape:'html':'UTF-8'} {$design.ETS_SOLO_LOGIN_BUTTON_TYPE|escape:'html':'UTF-8'}">

                        {if $design.ETS_SOLO_LOGIN_BUTTON_TYPE != 'img' || $hook == 'admin'}
                            {if $hook == 'admin'}
                                <span style="{if $design.ETS_SOLO_LOGIN_BUTTON_TYPE == 'img'}display: none;{/if}" class="ets_solo_btn flat name custom">
                            {/if}
                            {if $net == 'gl'}
                                <i class="svg_gl">
                                <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 48 48" width="96px" height="96px">
                                <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                                <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                                <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                                <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
                                </svg>
                                </i>
                            {else}

                                <i title="{$socials.$net.name|escape:'html':'UTF-8'}"
                                   class="{if $net == 'bli' || $net == 'lin' || $net == 'dqs' || $net == 'dis' || $net == 'pix' || $net == 'vko' || $net == 'mai'}{if $hook == 'admin' && !$is15}icon{else}icon icon-{if $net != 'ms'}{$socials.$net.label|lower|escape:'html':'UTF-8'} {else}microsoft{/if} fa fa{/if}-{if $net == 'skc'}stack-overflow {/if}{if $net != 'ms'}{$socials.$net.label|lower|escape:'html':'UTF-8'}{else}microsoft{/if}{/if}">

                                    {if $net == 'skc'}
                                        <svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="stack-exchange" class="svg-inline--fa fa-stack-exchange fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M17.7 332.3h412.7v22c0 37.7-29.3 68-65.3 68h-19L259.3 512v-89.7H83c-36 0-65.3-30.3-65.3-68v-22zm0-23.6h412.7v-85H17.7v85zm0-109.4h412.7v-85H17.7v85zM365 0H83C47 0 17.7 30.3 17.7 67.7V90h412.7V67.7C430.3 30.3 401 0 365 0z"></path></svg>
                                    {elseif $net == 'bit'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1007 859q8 63-50.5 101t-111.5 6q-39-17-53.5-58t-.5-82 52-58q36-18 72.5-12t64 35.5 27.5 67.5zm111-21q-14-107-113-164t-197-13q-63 28-100.5 88.5t-34.5 129.5q4 91 77.5 155t165.5 56q91-8 152-84t50-168zm239-542q-20-27-56-44.5t-58-22-71-12.5q-291-47-566 2-43 7-66 12t-55 22-50 43q30 28 76 45.5t73.5 22 87.5 11.5q228 29 448 1 63-8 89.5-12t72.5-21.5 75-46.5zm57 1035q-8 26-15.5 76.5t-14 84-28.5 70-58 56.5q-86 48-189.5 71.5t-202 22-201.5-18.5q-46-8-81.5-18t-76.5-27-73-43.5-52-61.5q-25-96-57-292l6-16 18-9q223 148 506.5 148t507.5-148q21 6 24 23t-5 45-8 37zm181-961q-26 167-111 655-5 30-27 56t-43.5 40-54.5 31q-252 126-610 88-248-27-394-139-15-12-25.5-26.5t-17-35-9-34-6-39.5-5.5-35q-9-50-26.5-150t-28-161.5-23.5-147.5-22-158q3-26 17.5-48.5t31.5-37.5 45-30 46-22.5 48-18.5q125-46 313-64 379-37 676 50 155 46 215 122 16 20 16.5 51t-5.5 54z"/></svg>
                                    {elseif $net == 'gil'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M104 706l792 1015-868-630q-18-13-25-34.5t0-42.5l101-308zm462 0h660l-330 1015zm-198-612l198 612h-462l198-612q8-23 33-23t33 23zm1320 612l101 308q7 21 0 42.5t-25 34.5l-868 630 792-1015zm0 0h-462l198-612q8-23 33-23t33 23z"/></svg>
                                    {elseif $net == 'git'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M896 128q209 0 385.5 103t279.5 279.5 103 385.5q0 251-146.5 451.5t-378.5 277.5q-27 5-40-7t-13-30q0-3 .5-76.5t.5-134.5q0-97-52-142 57-6 102.5-18t94-39 81-66.5 53-105 20.5-150.5q0-119-79-206 37-91-8-204-28-9-81 11t-92 44l-38 24q-93-26-192-26t-192 26q-16-11-42.5-27t-83.5-38.5-85-13.5q-45 113-8 204-79 87-79 206 0 85 20.5 150t52.5 105 80.5 67 94 39 102.5 18q-39 36-49 103-21 10-45 15t-57 5-65.5-21.5-55.5-62.5q-19-32-48.5-52t-49.5-24l-20-3q-21 0-29 4.5t-5 11.5 9 14 13 12l7 5q22 10 43.5 38t31.5 51l10 23q13 38 44 61.5t67 30 69.5 7 55.5-3.5l23-4q0 38 .5 88.5t.5 54.5q0 18-13 30t-40 7q-232-77-378.5-277.5t-146.5-451.5q0-209 103-385.5t279.5-279.5 385.5-103zm-477 1103q3-7-7-12-10-3-13 2-3 7 7 12 9 6 13-2zm31 34q7-5-2-16-10-9-16-3-7 5 2 16 10 10 16 3zm30 45q9-7 0-19-8-13-17-6-9 5 0 18t17 7zm42 42q8-8-4-19-12-12-20-3-9 8 4 19 12 12 20 3zm57 25q3-11-13-16-15-4-19 7t13 15q15 6 19-6zm63 5q0-13-17-11-16 0-16 11 0 13 17 11 16 0 16-11zm58-10q-2-11-18-9-16 3-14 15t18 8 14-14z"/></svg>
                                    {elseif $net == 'mee'}
                                        <svg width="40" height="40" viewBox="0 0 2048 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1398 1234q-4-24-27.5-34t-49.5-10.5-48.5-12.5-25.5-38q-5-47 33-139.5t75-181 32-127.5q-14-101-117-103-45-1-75 16l-3 2-5 2.5-4.5 2-5 2-5 .5-6-1.5-6-3.5-6.5-5q-3-2-9-8.5t-9-9-8.5-7.5-9.5-7.5-9.5-5.5-11-4.5-11.5-2.5q-30-5-48 3t-45 31q-1 1-9 8.5t-12.5 11-15 10-16.5 5.5-17-3q-54-27-84-40-41-18-94 5t-76 65q-16 28-41 98.5t-43.5 132.5-40 134-21.5 73q-22 69 18.5 119t110.5 46q30-2 50.5-15t38.5-46q7-13 79-199.5t77-194.5q6-11 21.5-18t29.5 0q27 15 21 53-2 18-51 139.5t-50 132.5q-6 38 19.5 56.5t60.5 7 55-49.5q4-8 45.5-92t81.5-163.5 46-88.5q20-29 41-28 29 0 25 38-2 16-65.5 147.5t-70.5 159.5q-12 53 13 103t74 74q17 9 51 15.5t71.5 8 62.5-14 20-48.5zm-951 216q3 15-5 27.5t-23 15.5q-14 3-26.5-5t-15.5-23q-3-14 5-27t22-16 27 5 16 23zm570 263q12 17 8.5 37.5t-20.5 32.5-37.5 8-32.5-21q-11-17-7.5-37.5t20.5-32.5 37.5-8 31.5 21zm-776-812q-18 27-49.5 33t-57.5-13q-26-18-32-50t12-58q18-27 49.5-33t57.5 12q26 19 32 50.5t-12 58.5zm1290 677q19 28 13 61.5t-34 52.5-60.5 13-51.5-34-13-61 33-53q28-19 60.5-13t52.5 34zm112-604q69 113 42.5 244.5t-134.5 207.5q-90 63-199 60-20 80-84.5 127t-143.5 44.5-140-57.5q-12 9-13 10-103 71-225 48.5t-193-126.5q-50-73-53-164-83-14-142.5-70.5t-80.5-128-2-152 81-138.5q-36-60-38-128t24.5-125 79.5-98.5 121-50.5q32-85 99-148t146.5-91.5 168-17 159.5 66.5q72-21 140-17.5t128.5 36 104.5 80 67.5 115 17.5 140.5q52 16 87 57t45.5 89-5.5 99.5-58 87.5zm-1124-660q14 20 9.5 44.5t-24.5 38.5q-19 14-43.5 9.5t-37.5-24.5q-14-20-9.5-44.5t24.5-38.5q19-14 43.5-9.5t37.5 24.5zm159-281q4 16-5 30.5t-26 18.5-31-5.5-18-26.5q-3-17 6.5-31t25.5-18q17-4 31 5.5t17 26.5zm1186 948q4 20-6.5 37t-30.5 21q-19 4-36-6.5t-21-30.5 6.5-37 30.5-22q20-4 36.5 7.5t20.5 30.5zm-664-893q16 27 8.5 58.5t-35.5 47.5q-27 16-57.5 8.5t-46.5-34.5q-16-28-8.5-59t34.5-48 58-9 47 36zm746 656q4 15-4 27.5t-23 16.5q-15 3-27.5-5.5t-15.5-22.5q-3-15 5-28t23-16q14-3 26.5 5t15.5 23zm-191-241q15 22 10.5 49t-26.5 43q-22 15-49 10t-42-27-10-49 27-43 48.5-11 41.5 28z"/></svg>
                                    {elseif $net == 'vim'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1709 518q-10 236-332 651-333 431-562 431-142 0-240-263-44-160-132-482-72-262-157-262-18 0-127 76l-77-98q24-21 108-96.5t130-115.5q156-138 241-146 95-9 153 55.5t81 203.5q44 287 66 373 55 249 120 249 51 0 154-161 101-161 109-246 13-139-109-139-57 0-121 26 120-393 459-382 251 8 236 326z"/></svg>
                                    {elseif $net == 'wei'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M675 1284q21-34 11-69t-45-50q-34-14-73-1t-60 46q-22 34-13 68.5t43 50.5 74.5 2.5 62.5-47.5zm94-121q8-13 3.5-26.5t-17.5-18.5q-14-5-28.5.5t-21.5 18.5q-17 31 13 45 14 5 29-.5t22-18.5zm174 107q-45 102-158 150t-224 12q-107-34-147.5-126.5t6.5-187.5q47-93 151.5-139t210.5-19q111 29 158.5 119.5t2.5 190.5zm312-160q-9-96-89-170t-208.5-109-274.5-21q-223 23-369.5 141.5t-132.5 264.5q9 96 89 170t208.5 109 274.5 21q223-23 369.5-141.5t132.5-264.5zm308 4q0 68-37 139.5t-109 137-168.5 117.5-226 83-270.5 31-275-33.5-240.5-93-171.5-151-65-199.5q0-115 69.5-245t197.5-258q169-169 341.5-236t246.5 7q65 64 20 209-4 14-1 20t10 7 14.5-.5 13.5-3.5l6-2q139-59 246-59t153 61q45 63 0 178-2 13-4.5 20t4.5 12.5 12 7.5 17 6q57 18 103 47t80 81.5 34 116.5zm-74-624q42 47 54.5 108.5t-6.5 117.5q-8 23-29.5 34t-44.5 4q-23-8-34-29.5t-4-44.5q20-63-24-111t-107-35q-24 5-45-8t-25-37q-5-24 8-44.5t37-25.5q60-13 119 5.5t101 65.5zm181-163q87 96 112.5 222.5t-13.5 241.5q-9 27-34 40t-52 4-40-34-5-52q28-82 10-172t-80-158q-62-69-148-95.5t-173-8.5q-28 6-52-9.5t-30-43.5 9.5-51.5 43.5-29.5q123-26 244 11.5t208 134.5z"/></svg>
                                    {elseif $net == 'oln'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M896 907q-188 0-321-133t-133-320q0-188 133-321t321-133 321 133 133 321q0 187-133 320t-321 133zm0-677q-92 0-157.5 65.5t-65.5 158.5q0 92 65.5 157.5t157.5 65.5 157.5-65.5 65.5-157.5q0-93-65.5-158.5t-157.5-65.5zm523 732q13 27 15 49.5t-4.5 40.5-26.5 38.5-42.5 37-61.5 41.5q-115 73-315 94l73 72 267 267q30 31 30 74t-30 73l-12 13q-31 30-74 30t-74-30q-67-68-267-268l-267 268q-31 30-74 30t-73-30l-12-13q-31-30-31-73t31-74l267-267 72-72q-203-21-317-94-39-25-61.5-41.5t-42.5-37-26.5-38.5-4.5-40.5 15-49.5q10-20 28-35t42-22 56 2 65 35q5 4 15 11t43 24.5 69 30.5 92 24 113 11q91 0 174-25.5t120-50.5l38-25q33-26 65-35t56-2 42 22 28 35z"/></svg>
                                    {elseif $net == 'fsq'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1256 434l37-194q5-23-9-40t-35-17h-712q-23 0-38.5 17t-15.5 37v1101q0 7 6 1l291-352q23-26 38-33.5t48-7.5h239q22 0 37-14.5t18-29.5q24-130 37-191 4-21-11.5-40t-36.5-19h-294q-29 0-48-19t-19-48v-42q0-29 19-47.5t48-18.5h346q18 0 35-13.5t20-29.5zm227-222q-15 73-53.5 266.5t-69.5 350-35 173.5q-6 22-9 32.5t-14 32.5-24.5 33-38.5 21-58 10h-271q-13 0-22 10-8 9-426 494-22 25-58.5 28.5t-48.5-5.5q-55-22-55-98v-1410q0-55 38-102.5t120-47.5h888q95 0 127 53t10 159zm0 0l-158 790q4-17 35-173.5t69.5-350 53.5-266.5z"/></svg>
                                    {elseif $net == 'dri'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1152 1500q-42-241-140-498h-2l-2 1q-16 6-43 16.5t-101 49-137 82-131 114.5-103 148l-15-11q184 150 418 150 132 0 256-52zm-185-607q-21-49-53-111-311 93-673 93-1 7-1 21 0 124 44 236.5t124 201.5q50-89 123.5-166.5t142.5-124.5 130.5-81 99.5-48l37-13q4-1 13-3.5t13-4.5zm-107-212q-120-213-244-378-138 65-234 186t-128 272q302 0 606-80zm684 319q-210-60-409-29 87 239 128 469 111-75 185-189.5t96-250.5zm-805-741q-1 0-2 1 1-1 2-1zm590 145q-185-164-433-164-76 0-155 19 131 170 246 382 69-26 130-60.5t96.5-61.5 65.5-57 37.5-40.5zm223 485q-3-232-149-410l-1 1q-9 12-19 24.5t-43.5 44.5-71 60.5-100 65-131.5 64.5q25 53 44 95 2 5 6.5 17t7.5 17q36-5 74.5-7t73.5-2 69 1.5 64 4 56.5 5.5 48 6.5 36.5 6 25 4.5zm112 7q0 209-103 385.5t-279.5 279.5-385.5 103-385.5-103-279.5-279.5-103-385.5 103-385.5 279.5-279.5 385.5-103 385.5 103 279.5 279.5 103 385.5z"/></svg>
                                    {elseif $net == 'yan'}
                                        <svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="yandex" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512" class="svg-inline--fa fa-yandex fa-w-8 fa-2x"><path fill="currentColor" d="M153.1 315.8L65.7 512H2l96-209.8c-45.1-22.9-75.2-64.4-75.2-141.1C22.7 53.7 90.8 0 171.7 0H254v512h-55.1V315.8h-45.8zm45.8-269.3h-29.4c-44.4 0-87.4 29.4-87.4 114.6 0 82.3 39.4 108.8 87.4 108.8h29.4V46.5z" class=""></path></svg>
                                    {elseif $net == 'red'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1095 1167q16 16 0 31-62 62-199 62t-199-62q-16-15 0-31 6-6 15-6t15 6q48 49 169 49 120 0 169-49 6-6 15-6t15 6zm-307-181q0 37-26 63t-63 26-63.5-26-26.5-63q0-38 26.5-64t63.5-26 63 26.5 26 63.5zm395 0q0 37-26.5 63t-63.5 26-63-26-26-63 26-63.5 63-26.5 63.5 26 26.5 64zm251-120q0-49-35-84t-85-35-86 36q-130-90-311-96l63-283 200 45q0 37 26 63t63 26 63.5-26.5 26.5-63.5-26.5-63.5-63.5-26.5q-54 0-80 50l-221-49q-19-5-25 16l-69 312q-180 7-309 97-35-37-87-37-50 0-85 35t-35 84q0 35 18.5 64t49.5 44q-6 27-6 56 0 142 140 243t337 101q198 0 338-101t140-243q0-32-7-57 30-15 48-43.5t18-63.5zm358 30q0 182-71 348t-191 286-286 191-348 71-348-71-286-191-191-286-71-348 71-348 191-286 286-191 348-71 348 71 286 191 191 286 71 348z"/></svg>
                                    {elseif $net == 'dro'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M402 707l494 305-342 285-490-319zm986 555v108l-490 293v1l-1-1-1 1v-1l-489-293v-108l147 96 342-284v-2l1 1 1-1v2l343 284zm-834-1144l342 285-494 304-338-270zm836 589l338 271-489 319-343-285zm-151-589l489 319-338 270-494-304z"/></svg>
                                    {elseif $net == 'wp'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M127 896q0-163 67-313l367 1005q-196-95-315-281t-119-411zm1288-39q0 19-2.5 38.5t-10 49.5-11.5 44-17.5 59-17.5 58l-76 256-278-826q46-3 88-8 19-2 26-18.5t-2.5-31-28.5-13.5l-205 10q-75-1-202-10-12-1-20.5 5t-11.5 15-1.5 18.5 9 16.5 19.5 8l80 8 120 328-168 504-280-832q46-3 88-8 19-2 26-18.5t-2.5-31-28.5-13.5l-205 10q-7 0-23-.5t-26-.5q105-160 274.5-253.5t367.5-93.5q147 0 280.5 53t238.5 149h-10q-55 0-92 40.5t-37 95.5q0 12 2 24t4 21.5 8 23 9 21 12 22.5 12.5 21 14.5 24 14 23q63 107 63 212zm-506 106l237 647q1 6 5 11-126 44-255 44-112 0-217-32zm661-436q95 174 95 369 0 209-104 385.5t-279 278.5l235-678q59-169 59-276 0-42-6-79zm-674-527q182 0 348 71t286 191 191 286 71 348-71 348-191 286-286 191-348 71-348-71-286-191-191-286-71-348 71-348 191-286 286-191 348-71zm0 1751q173 0 331.5-68t273-182.5 182.5-273 68-331.5-68-331.5-182.5-273-273-182.5-331.5-68-331.5 68-273 182.5-182.5 273-68 331.5 68 331.5 182.5 273 273 182.5 331.5 68z"/></svg>
                                    {elseif $net == 'ms'}
                                        <svg width="40" height="40" viewBox="0 0 23 23" xmlns="http://www.w3.org/2000/svg"><path fill="#F25022" d="M1 1h10v10H1z"/><path fill="#7FBA00" d="M12 1h10v10H12z"/><path fill="#00A4EF" d="M1 12h10v10H1z"/><path fill="#FFB900" d="M12 12h10v10H12z"/></svg>
                                    {elseif $net == 'yahoo'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M987 957l13 707q-62-11-105-11-41 0-105 11l13-707q-40-69-168.5-295.5t-216.5-374.5-181-287q58 15 108 15 44 0 111-15 63 111 133.5 229.5t167 276.5 138.5 227q37-61 109.5-177.5t117.5-190 105-176 107-189.5q54 14 107 14 56 0 114-14-28 39-60 88.5t-49.5 78.5-56.5 96-49 84q-146 248-353 610z"/></svg>
                                    {elseif $net == 'yh'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M987 957l13 707q-62-11-105-11-41 0-105 11l13-707q-40-69-168.5-295.5t-216.5-374.5-181-287q58 15 108 15 44 0 111-15 63 111 133.5 229.5t167 276.5 138.5 227q37-61 109.5-177.5t117.5-190 105-176 107-189.5q54 14 107 14 56 0 114-14-28 39-60 88.5t-49.5 78.5-56.5 96-49 84q-146 248-353 610z"/></svg>
                                    {elseif $net == 'pin'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1664 896q0 209-103 385.5t-279.5 279.5-385.5 103q-111 0-218-32 59-93 78-164 9-34 54-211 20 39 73 67.5t114 28.5q121 0 216-68.5t147-188.5 52-270q0-114-59.5-214t-172.5-163-255-63q-105 0-196 29t-154.5 77-109 110.5-67 129.5-21.5 134q0 104 40 183t117 111q30 12 38-20 2-7 8-31t8-30q6-23-11-43-51-61-51-151 0-151 104.5-259.5t273.5-108.5q151 0 235.5 82t84.5 213q0 170-68.5 289t-175.5 119q-61 0-98-43.5t-23-104.5q8-35 26.5-93.5t30-103 11.5-75.5q0-50-27-83t-77-33q-62 0-105 57t-43 142q0 73 25 122l-99 418q-17 70-13 177-206-91-333-281t-127-423q0-209 103-385.5t279.5-279.5 385.5-103 385.5 103 279.5 279.5 103 385.5z"/></svg>
                                    {elseif $net == 'li'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M477 625v991h-330v-991h330zm21-306q1 73-50.5 122t-135.5 49h-2q-82 0-132-49t-50-122q0-74 51.5-122.5t134.5-48.5 133 48.5 51 122.5zm1166 729v568h-329v-530q0-105-40.5-164.5t-126.5-59.5q-63 0-105.5 34.5t-63.5 85.5q-11 30-11 81v553h-329q2-399 2-647t-1-296l-1-48h329v144h-2q20-32 41-56t56.5-52 87-43.5 114.5-15.5q171 0 275 113.5t104 332.5z"/></svg>
                                    {elseif $net == 'tb'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1328 1329l80 237q-23 35-111 66t-177 32q-104 2-190.5-26t-142.5-74-95-106-55.5-120-16.5-118v-544h-168v-215q72-26 129-69.5t91-90 58-102 34-99 15-88.5q1-5 4.5-8.5t7.5-3.5h244v424h333v252h-334v518q0 30 6.5 56t22.5 52.5 49.5 41.5 81.5 14q78-2 134-29z"/></svg>
                                    {elseif $net == 'tw'}
                                        <svg width="40" height="40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
                                    {elseif $net == 'fb'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1343 12v264h-157q-86 0-116 36t-30 108v189h293l-39 296h-254v759h-306v-759h-255v-296h255v-218q0-186 104-288.5t277-102.5q147 0 228 12z"/></svg>
                                    {elseif $net == 'gl'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M896 786h725q12 67 12 128 0 217-91 387.5t-259.5 266.5-386.5 96q-157 0-299-60.5t-245-163.5-163.5-245-60.5-299 60.5-299 163.5-245 245-163.5 299-60.5q300 0 515 201l-209 201q-123-119-306-119-129 0-238.5 65t-173.5 176.5-64 243.5 64 243.5 173.5 176.5 238.5 65q87 0 160-24t120-60 82-82 51.5-87 22.5-78h-436v-264z"/></svg>
                                    {elseif $net == 'pay'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1647 646q18 84-4 204-87 444-565 444h-44q-25 0-44 16.5t-24 42.5l-4 19-55 346-2 15q-5 26-24.5 42.5t-44.5 16.5h-251q-21 0-33-15t-9-36q9-56 26.5-168t26.5-168 27-167.5 27-167.5q5-37 43-37h131q133 2 236-21 175-39 287-144 102-95 155-246 24-70 35-133 1-6 2.5-7.5t3.5-1 6 3.5q79 59 98 162zm-172-282q0 107-46 236-80 233-302 315-113 40-252 42 0 1-90 1l-90-1q-100 0-118 96-2 8-85 530-1 10-12 10h-295q-22 0-36.5-16.5t-11.5-38.5l232-1471q5-29 27.5-48t51.5-19h598q34 0 97.5 13t111.5 32q107 41 163.5 123t56.5 196z"/></svg>
                                    {elseif $net == 'ozo'}
                                        <svg width="40" height="40" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1551 1476q15-6 26-3t11 17.5-15 33.5q-13 16-44 43.5t-95.5 68-141 74-188 58-229.5 24.5q-119 0-238-31t-209-76.5-172.5-104-132.5-105-84-87.5q-8-9-10-16.5t1-12 8-7 11.5-2 11.5 4.5q192 117 300 166 389 176 799 90 190-40 391-135zm207-115q11 16 2.5 69.5t-28.5 102.5q-34 83-85 124-17 14-26 9t0-24q21-45 44.5-121.5t6.5-98.5q-5-7-15.5-11.5t-27-6-29.5-2.5-35 0-31.5 2-31 3-22.5 2q-6 1-13 1.5t-11 1-8.5 1-7 .5h-10l-3-.5-2-1.5-1.5-3q-6-16 47-40t103-30q46-7 108-1t76 24zm-394-443q0 31 13.5 64t32 58 37.5 46 33 32l13 11-227 224q-40-37-79-75.5t-58-58.5l-19-20q-11-11-25-33-38 59-97.5 102.5t-127.5 63.5-140 23-137.5-21-117.5-65.5-83-113-31-162.5q0-84 28-154t72-116.5 106.5-83 122.5-57 130-34.5 119.5-18.5 99.5-6.5v-127q0-65-21-97-34-53-121-53-6 0-16.5 1t-40.5 12-56 29.5-56 59.5-48 96l-294-27q0-60 22-119t67-113 108-95 151.5-65.5 190.5-24.5q100 0 181 25t129.5 61.5 81 83 45 86 12.5 73.5v589zm-672 21q0 86 70 133 66 44 139 22 84-25 114-123 14-45 14-101v-162q-59 2-111 12t-106.5 33.5-87 71-32.5 114.5z"/></svg>
                                    {elseif $net == 'tik'}
                                        <svg viewBox="0 0 48 48" width="48px" height="48px">
                                        <path fill="#ec407a" fill-rule="evenodd" d="M29.208,20.607c1.576,1.126,3.507,1.788,5.592,1.788v-4.011 c-0.395,0-0.788-0.041-1.174-0.123v3.157c-2.085,0-4.015-0.663-5.592-1.788v8.184c0,4.094-3.321,7.413-7.417,7.413 c-1.528,0-2.949-0.462-4.129-1.254c1.347,1.376,3.225,2.23,5.303,2.23c4.096,0,7.417-3.319,7.417-7.413L29.208,20.607L29.208,20.607 z M30.657,16.561c-0.805-0.879-1.334-2.016-1.449-3.273v-0.516h-1.113C28.375,14.369,29.331,15.734,30.657,16.561L30.657,16.561z M19.079,30.832c-0.45-0.59-0.693-1.311-0.692-2.053c0-1.873,1.519-3.391,3.393-3.391c0.349,0,0.696,0.053,1.029,0.159v-4.1 c-0.389-0.053-0.781-0.076-1.174-0.068v3.191c-0.333-0.106-0.68-0.159-1.03-0.159c-1.874,0-3.393,1.518-3.393,3.391 C17.213,29.127,17.972,30.274,19.079,30.832z" clip-rule="evenodd"/><path fill="#fff" fill-rule="evenodd" d="M28.034,19.63c1.576,1.126,3.507,1.788,5.592,1.788v-3.157 c-1.164-0.248-2.194-0.856-2.969-1.701c-1.326-0.827-2.281-2.191-2.561-3.788h-2.923v16.018c-0.007,1.867-1.523,3.379-3.393,3.379 c-1.102,0-2.081-0.525-2.701-1.338c-1.107-0.558-1.866-1.705-1.866-3.029c0-1.873,1.519-3.391,3.393-3.391 c0.359,0,0.705,0.056,1.03,0.159V21.38c-4.024,0.083-7.26,3.369-7.26,7.411c0,2.018,0.806,3.847,2.114,5.183 c1.18,0.792,2.601,1.254,4.129,1.254c4.096,0,7.417-3.319,7.417-7.413L28.034,19.63L28.034,19.63z" clip-rule="evenodd"/><path fill="#81d4fa" fill-rule="evenodd" d="M33.626,18.262v-0.854c-1.05,0.002-2.078-0.292-2.969-0.848 C31.445,17.423,32.483,18.018,33.626,18.262z M28.095,12.772c-0.027-0.153-0.047-0.306-0.061-0.461v-0.516h-4.036v16.019 c-0.006,1.867-1.523,3.379-3.393,3.379c-0.549,0-1.067-0.13-1.526-0.362c0.62,0.813,1.599,1.338,2.701,1.338 c1.87,0,3.386-1.512,3.393-3.379V12.772H28.095z M21.635,21.38v-0.909c-0.337-0.046-0.677-0.069-1.018-0.069 c-4.097,0-7.417,3.319-7.417,7.413c0,2.567,1.305,4.829,3.288,6.159c-1.308-1.336-2.114-3.165-2.114-5.183 C14.374,24.749,17.611,21.463,21.635,21.38z" clip-rule="evenodd"/></svg>
                                    {/if}
                                </i>
                            {/if}
                            {if $hook == 'admin'}</span>{/if}
                        {/if}
                        {if $design.ETS_SOLO_LOGIN_BUTTON_TYPE == 'img' || $hook == 'admin'}
                            <img style="{if $design.ETS_SOLO_LOGIN_BUTTON_TYPE != 'img'}display: none;{/if}" class="ets_solo_btn img" src="{$base_url|escape:'quotes':'UTF-8'}views/img/32x32/new/{if $net == 'ms'}microsoft.svg{else}{$socials.$net.label|lower|escape:'quotes':'UTF-8'}.png{/if}" alt="{$socials.$net.name|escape:'html':'UTF-8'}" title="{$socials.$net.name|escape:'html':'UTF-8'}" />
                        {/if}
                        {if $design.ETS_SOLO_LOGIN_BUTTON_TYPE == 'name' || $hook == 'admin'}
                            {if $hook == 'admin'}<span style="{if $design.ETS_SOLO_LOGIN_BUTTON_TYPE != 'name'}display: none;{/if}" class="ets_solo_btn name">{/if}{$socials.$net.name|escape:'html':'UTF-8'}{if $hook == 'admin'}</span>{/if}
                        {/if}
                        {if $design.ETS_SOLO_LOGIN_BUTTON_TYPE == 'custom' || $hook == 'admin'}
                            {assign var='title' value='ETS_SOLO_'|cat:$socials.$net.label|upper|cat:'_TITLE'}
                            {if $hook == 'admin'}<span style="{if $design.ETS_SOLO_LOGIN_BUTTON_TYPE != 'custom'}display: none;{/if}" class="ets_solo_btn custom title">{/if}{if $design.$title}{$design.$title|escape:'html':'UTF-8'}{else}{$socials.$net.name|escape:'html':'UTF-8'}{/if}{if $hook == 'admin'}</span>{/if}
                        {/if}
                    </span>
                    </li>
                {/if}
            {/foreach}{/if}
        </ul>
        {if $design.ETS_SOLO_ADDITIONAL_DESC || $hook == 'admin'}<div class="ets_solo_social_desc">{/if}
            {$design.ETS_SOLO_ADDITIONAL_DESC nofilter}
        {if $design.ETS_SOLO_ADDITIONAL_DESC || $hook == 'admin'}</div>{/if}
        {if $hook == 'slw' || $hook == 'alw' || ($hook=='admin' && ($pos == 'slw' || $pos == 'alw'))}
            <div class="ets_solo_or"><span>{l s='Or login with' mod='ets_sociallogin'}</span></div>
            <section class="solo-login-form">
                <form class="solo-login-form-{$hook|escape:'quotes':'UTF-8'}" data-hook="{$hook|escape:'quotes':'UTF-8'}" action="{if isset($submit) && $submit}{$submit|escape:'html':'UTF-8'}{/if}" method="post">
                    <section>
                        <div class="form-group row ">
                            <div class="col-md-12">
                                <input class="form-control" name="email" value="" placeholder="{l s='Enter your email...' mod='ets_sociallogin'}" {if $hook != 'admin'}required=""{/if} type="email">
                            </div>
                        </div>
                        <div class="form-group row ">
                            <div class="col-md-12">
                                <input class="form-control js-child-focus js-visible-password" placeholder="{l s='Enter your password...' mod='ets_sociallogin'}" name="password" value="" pattern="{literal}.{5,}{/literal}" {if $hook != 'admin'}required=""{/if} type="password">
                            </div>
                        </div>
                    </section>
                    <footer class="form-footer text-sm-center clearfix">
                        {if $hook!='admin'}
                        <input name="ajax" value="1" type="hidden">
                        <input name="solo_submitLogin" value="1" type="hidden">
                        {/if}
                        <button class="solo-submit-login-{$hook|escape:'quotes':'UTF-8'} btn btn-primary" data-link-action="sign-in" type="{if $hook!='admin'}submit{else}button{/if}">{l s='Login' mod='ets_sociallogin'}</button>
                        <div class="forgot-password">
                            <a href="{if $hook!='admin'}{$recover_password|escape:'quotes':'UTF-8'}{else}javascript:void(0){/if}" rel="nofollow">{l s='Forgot your password?' mod='ets_sociallogin'}</a>
                        </div>
                        <div class="no-account">
                            <a href="{if $hook!='admin'}{$create_account|escape:'quotes':'UTF-8'}{else}javascript:void(0){/if}" data-link-action="display-register-form">{l s='No account? Create one here' mod='ets_sociallogin'}</a>
                        </div>
                    </footer>
                </form>
            </section>
        {/if}
        {if $hook == 'trp'}
        <div class="ets_solo_or"><span>{l s='Or login with' mod='ets_sociallogin'}</span></div>
        {/if}
        {if $hook == 'admin'}
            <p class="solo_note">
                {l s='Drag & drop the social icons to change their order on the front office' mod='ets_sociallogin'}
            </p>
        {/if}
    </div>
    {if $hook=='alw' && !$design.ETS_SOLO_HIDE_ON_MOBILE}
        </div>
        </div>
        </div>
    {/if}
</div>

{/if}
