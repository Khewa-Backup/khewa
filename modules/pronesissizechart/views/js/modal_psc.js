/*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2019 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

!function(o){o.pgwModal=function(n){var t={},e={mainClassName:"pgwModal",backdropClassName:"pgwModalBackdrop",maxWidth:500,titleBar:!0,closable:!0,closeOnEscape:!0,closeOnBackgroundClick:!0,closeContent:'<span class="pm-icon"></span>',loadingContent:"Loading in progress...",errorContent:"An error has occured. Please try again in a few moments."};if("undefined"!=typeof window.pgwModalObject&&(t=window.pgwModalObject),"object"==typeof n&&!n.pushContent){if(!n.url&&!n.target&&!n.content)throw new Error('PgwModal - There is no content to display, please provide a config parameter : "url", "target" or "content"');t.config={},t.config=o.extend({},e,n),window.pgwModalObject=t}var a=function(){return o(document).trigger("PgwModal::Create"),!0},i=function(){return o("#pgwModal .pm-title, #pgwModal .pm-content").html(""),o("#pgwModal .pm-close").html("").unbind("click"),!0},d=function(){return angular.element("body").injector().invoke(function(n){var t=angular.element(o("#pgwModal .pm-content")).scope();n(o("#pgwModal .pm-content"))(t),t.$digest()}),!0},c=function(n){return o("#pgwModal .pm-content").html(n),t.config.angular&&d(),l(),o(document).trigger("PgwModal::PushContent"),!0},l=function(){o("#pgwModal, #pgwModalBackdrop").fadeIn();var n=o(window).height(),t=o("#pgwModal .pm-body").height(),e=Math.round((n-t)/3);return 0>=e&&(e=0),o("#pgwModal .pm-body").css("margin-top",e),!0},r=function(){return t.config.modalData},g=function(){var n=o('<div style="width:50px;height:50px;overflow:auto"><div></div></div>').appendTo("body"),t=n.children();if("function"!=typeof t.innerWidth)return 0;var e=t.innerWidth()-t.height(90).innerWidth();return n.remove(),e},p=function(){return o("body").hasClass("pgwModalOpen")},s=function(){o("#pgwModal, #pgwModalBackdrop").fadeOut(),o("body").css("padding-right","").removeClass("pgwModalOpen"),i(),o(window).unbind("resize.PgwModal"),o(document).unbind("keyup.PgwModal"),o("#pgwModal").unbind("click.PgwModalBackdrop");try{delete window.pgwModalObject}catch(n){window.pgwModalObject=void 0}return o(document).trigger("PgwModal::Close"),!0},w=function(){if(0==o("#pgwModal").length?a():i(),o("#pgwModal").removeClass().addClass(t.config.mainClassName),o("#pgwModalBackdrop").removeClass().addClass(t.config.backdropClassName),t.config.closable?o("#pgwModal .pm-close").html(t.config.closeContent).click(function(){s()}).fadeIn():o("#pgwModal .pm-close").html("").unbind("click").fadeOut(),t.config.titleBar?o("#pgwModal .pm-title").fadeIn():o("#pgwModal .pm-title").fadeOut(),t.config.title&&o("#pgwModal .pm-title").text(t.config.title),t.config.maxWidth&&o("#pgwModal .pm-body").css("max-width",t.config.maxWidth),t.config.url){t.config.loadingContent&&o("#pgwModal .pm-content").html(t.config.loadingContent);var e={url:n.url,success:function(o){c(o)},error:function(){o("#pgwModal .pm-content").html(t.config.errorContent)}};t.config.ajaxOptions&&(e=o.extend({},e,t.config.ajaxOptions)),o.ajax(e)}else t.config.target?c(o(t.config.target).html()):t.config.content&&c(t.config.content);t.config.closeOnEscape&&t.config.closable&&o(document).bind("keyup.PgwModal",function(o){27==o.keyCode&&s()}),t.config.closeOnBackgroundClick&&t.config.closable&&o("#pgwModal").bind("click.PgwModalBackdrop",function(n){var t=o(n.target).hasClass("pm-container"),e=o(n.target).attr("id");(t||"pgwModal"==e)&&s()}),o("body").addClass("pgwModalOpen");var d=g();return d>0&&o("body").css("padding-right",d),o(window).bind("resize.PgwModal",function(){l()}),o(document).trigger("PgwModal::Open"),!0};return"string"==typeof n&&"close"==n?s():"string"==typeof n&&"reposition"==n?l():"string"==typeof n&&"getData"==n?r():"string"==typeof n&&"isOpen"==n?p():"object"==typeof n&&n.pushContent?c(n.pushContent):"object"==typeof n?w():void 0}}(window.Zepto||window.jQuery);
