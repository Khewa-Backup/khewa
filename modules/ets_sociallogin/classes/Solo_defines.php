<?php
/**
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
 */


if (!defined('_PS_VERSION_')) { exit; }

class Solo_defines extends Solo_translate
{
    public $socials;
    public $context;
    public $smarty;
    public $module;
    public $hooks;
    public $fields_list;
    public $social_networks;
    public $design;
    public $discount;
    public $settings;
    public static $configTabs;
    public static $instance;
    const NETWORK_ORDER = 'fb,gl,tw,li,ins,wp,tb,yh,ms,red,git,gil,skc,yan,dri,fsq,oln,pay,ozo,pin,wei,vim,dro,mai,vko,pix,mee,bit,dis,dqs,lin,bli,tik';

    public function __construct($context, $module)
    {
        parent::__construct($context, $module);
        if (isset($this->context->smarty))
            $this->smarty = $this->context->smarty;
    }

    public static function getInstance($context, $module)
    {
        if (!(isset(self::$instance)) || !self::$instance)
            self::$instance = new Solo_defines($context, $module);
        return self::$instance;
    }

    public function getFields($fields)
    {
        //socials
        if (trim($fields) == 'socials') {
            if (!(isset($this->socials)) || !$this->socials) {
                $this->socials = array(
                    'ozo' => array(
                        'name' => $this->l('Amazon', 'Solo_defines'),
                        'id_option' => 'ozo',
                        'label' => 'Amazon',
                        'link' => 'https://developer.amazon.com/lwa/sp/overview.html',
                        'doc' => 'Get_Amazon_API_key',
                    ),
                    'pay' => array(
                        'name' => $this->l('Paypal', 'Solo_defines'),
                        'id_option' => 'pay',
                        'label' => 'Paypal',
                        'link' => 'https://developer.paypal.com/developer/applications/',
                        'doc' => 'Get_PayPal_API_key',
                    ),
                    'fb' => array(
                        'name' => $this->l('Facebook', 'Solo_defines'),
                        'id_option' => 'fb',
                        'label' => 'Facebook',
                        'link' => 'https://developers.facebook.com/apps',
                        'doc' => 'Get_Facebook_API_key',
                    ),
                    'gl' => array(
                        'name' => $this->l('Google', 'Solo_defines'),
                        'id_option' => 'gl',
                        'label' => 'Google',
                        'link' => 'https://console.developers.google.com',
                        'doc' => 'Get_Google_API_key',
                    ),
                    'tw' => array(
                        'name' => $this->l('X', 'Solo_defines'),//Twitter
                        'id_option' => 'tw',
                        'label' => 'Twitter',//Twitter
                        'link' => 'https://dev.twitter.com/apps',
                        'doc' => 'Get_Twitter_API_key',
                    ),
                    'tb' => array(
                        'name' => $this->l('Tumblr', 'Solo_defines'),
                        'id_option' => 'tb',
                        'label' => 'Tumblr',
                        'link' => 'http://www.tumblr.com/oauth/apps',
                        'doc' => 'Get_Tumblr_API_key',
                    ),
                    'pin' => array(
                        'name' => $this->l('Pinterest', 'Solo_defines'),
                        'id_option' => 'pin',
                        'label' => 'Pinterest',
                        'link' => 'https://developers.pinterest.com/manage/',
                        'doc' => 'GetPinterestInfo',
                    ),
                    'li' => array(
                        'name' => $this->l('LinkedIn', 'Solo_defines'),
                        'id_option' => 'li',
                        'label' => 'LinkedIn',
                        'link' => 'https://developer.linkedin.com/',
                        'doc' => 'Get_LinkedIn_API_key',
                    ),
                    'ms' => array(
                        'name' => $this->l('Microsoft', 'Solo_defines'),
                        'id_option' => 'ms',
                        'label' => 'Microsoft',
                        'link' => 'https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade',
                        'link_new' => 'https://docs.microsoft.com/en-us/azure/app-service/configure-authentication-provider-microsoft',
                        'doc' => 'Get_Microsoft_Entra_API_key',
                    ),
                    'yh' => array(
                        'name' => $this->l('Yahoo', 'Solo_defines'),
                        'id_option' => 'yh',
                        'label' => 'Yahoo',
                        'link' => 'https://developer.yahoo.com/apps/',
                        'doc' => 'Get_Yahoo_API_key',
                    ),
                    'dro' => array(
                        'name' => $this->l('Dropbox', 'Solo_defines'),
                        'id_option' => 'dro',
                        'label' => 'Dropbox',
                        'link' => 'https://www.dropbox.com/developers/apps',
                        'doc' => 'Get_DropBox_API_key',
                    ),
                    'wp' => array(
                        'name' => $this->l('Wordpress', 'Solo_defines'),
                        'id_option' => 'wp',
                        'label' => 'WordPress',
                        'link' => 'https://developer.wordpress.com/apps/new/',
                        'doc' => 'Get_Wordpress_API_key',
                    ),
                    'red' => array(
                        'name' => $this->l('Reddit', 'Solo_defines'),
                        'id_option' => 'red',
                        'label' => 'Reddit',
                        'link' => 'https://www.reddit.com/prefs/apps',
                        'doc' => 'Get_Reddit_API_key',
                    ),
                    'yan' => array(
                        'name' => $this->l('Yandex', 'Solo_defines'),
                        'id_option' => 'yan',
                        'label' => 'Yandex',
                        'link' => 'https://oauth.yandex.com/client/new',
                        'doc' => 'Get_Yandex_API_key',
                    ),
                    'dri' => array(
                        'name' => $this->l('Dribbble', 'Solo_defines'),
                        'id_option' => 'dri',
                        'label' => 'Dribbble',
                        'link' => 'https://dribbble.com/account/applications/new',
                        'doc' => 'Get_Dribbble_API_key',
                    ),
                    'fsq' => array(
                        'name' => $this->l('Foursquare', 'Solo_defines'),
                        'id_option' => 'fsq',
                        'label' => 'Foursquare',
                        'link' => 'https://foursquare.com/developers/apps',
                        'doc' => 'Get_Foursquare_API_key',
                    ),
                    'oln' => array(
                        'name' => $this->l('Odnoklassniki', 'Solo_defines'),
                        'id_option' => 'oln',
                        'label' => 'Odnok',
                        'link' => 'https://ok.ru/devaccess',
                        'doc' => 'Get_OK_API_key',
                    ),
                    'wei' => array(
                        'name' => $this->l('Weibo', 'Solo_defines'),
                        'id_option' => 'wei',
                        'label' => 'Weibo',
                        'link' => 'http://open.weibo.com/apps/',
                        'doc' => 'Get_Weibo_API_key',
                    ),
                    'vim' => array(
                        'name' => $this->l('Vimeo', 'Solo_defines'),
                        'id_option' => 'vim',
                        'label' => 'Vimeo',
                        'link' => 'https://developer.vimeo.com/apps',
                        'doc' => 'Get_Vimeo_API_key',
                    ),
                    'mai' => array(
                        'name' => $this->l('Mailru', 'Solo_defines'),
                        'id_option' => 'mai',
                        'label' => 'Mailru',
                        'link' => 'https://api.mail.ru/apps',
                        'doc' => 'Get_Mailru_API_key',
                    ),
                    'vko' => array(
                        'name' => $this->l('Vkontakte', 'Solo_defines'),
                        'id_option' => 'vko',
                        'label' => 'Vkontakte',
                        'link' => 'https://vk.com/apps',
                        'doc' => 'Get_VK_API_key',
                    ),
                    'mee' => array(
                        'name' => $this->l('Meetup', 'Solo_defines'),
                        'id_option' => 'mee',
                        'label' => 'Meetup',
                        'link' => 'https://secure.meetup.com/meetup_api/oauth_consumers/',
                        'doc' => 'Get_MeetUp_API_key',
                    ),
                    'dis' => array(
                        'name' => $this->l('Discord', 'Solo_defines'),
                        'id_option' => 'dis',
                        'label' => 'Discord',
                        'link' => 'https://discordapp.com/developers/applications/',
                        'doc' => 'Get_Discord_API_key',
                    ),
                    'dqs' => array(
                        'name' => $this->l('Disqus', 'Solo_defines'),
                        'id_option' => 'dqs',
                        'label' => 'Disqus',
                        'link' => 'https://disqus.com/api/applications',
                        'doc' => 'Get_Disqus_API_key',
                    ),
                    'lin' => array(
                        'name' => $this->l('Line', 'Solo_defines'),
                        'id_option' => 'lin',
                        'label' => 'Line',
                        'link' => 'https://developers.line.biz/console/',
                        'doc' => 'Get_Line_API_key',
                    ),
                    'bli' => array(
                        'name' => $this->l('Blizzard', 'Solo_defines'),
                        'id_option' => 'bli',
                        'label' => 'Blizzard',
                        'link' => 'https://dev.battle.net/apps/',
                        'doc' => 'Get_Blizzard_API_key',
                    ),
                    'git' => array(
                        'name' => $this->l('GitHub', 'Solo_defines'),
                        'id_option' => 'git',
                        'label' => 'GitHub',
                        'link' => 'https://developer.github.com/v3/oauth/',
                        'doc' => 'Get_GitHub_API_key',
                    ),
                    'gil' => array(
                        'name' => $this->l('GitLab', 'Solo_defines'),
                        'id_option' => 'gil',
                        'label' => 'GitLab',
                        'link' => 'https://gitlab.com/oauth/applications/',
                        'doc' => 'Get_GitLab_API_key',
                    ),
                    'bit' => array(
                        'name' => $this->l('BitBucket', 'Solo_defines'),
                        'id_option' => 'bit',
                        'label' => 'BitBucket',
                        'link' => 'https://developer.atlassian.com/apps/create',
                        'doc' => 'Get_BitBucket_API_key',
                    ),
                    'skc' => array(
                        'name' => $this->l('StackExchange', 'Solo_defines'),
                        'id_option' => 'skc',
                        'label' => 'StackExc',
                        'link' => 'https://stackapps.com/apps/oauth/register',
                        'doc' => 'Get_StackExchange_API_key',
                    ),
                    'tik' => array(
                        'name' => $this->l('TikTok', 'Solo_defines'),
                        'id_option' => 'tik',
                        'label' => 'TikTok',
                        'link' => 'https://developers.tiktok.com/doc/getting-started-create-an-app/',
                    ),
                );
            }
            return $this->socials;
        } //hooks.
        elseif (trim($fields) == 'hooks') {
            if (!(isset($this->hooks)) || !$this->hooks) {
                $this->hooks = array(
                    '_TRP' => array(
                        'id_option' => 'trp',
                        'name' => $this->l('Registration page - on top', 'Solo_defines'),
                        'hook' => 'displayCustomerAccountFormTop',
                        'init' => 1,
                    ),
                    '_BRP' => array(
                        'id_option' => 'brp',
                        'name' => $this->l('Registration page - on bottom', 'Solo_defines'),
                        'hook' => 'displayCustomerAccountForm',
                        'init' => 0,
                    ),
                    '_LGP' => array(
                        'id_option' => 'lgp',
                        'name' => $this->l('Login page', 'Solo_defines'),
                        'hook' => 'displayCustomerLoginFormAfter',
                        'init' => 0,
                    ),
                    '_ALW' => array(
                        'id_option' => 'alw',
                        'name' => $this->l('My account login widget', 'Solo_defines'),
                        'hook' => (!$this->module->is16 ? 'displayTop' : ''),
                        'init' => 1,
                    ),
                    '_SLW' => array(
                        'id_option' => 'slw',
                        'name' => $this->l('Side login widget', 'Solo_defines'),
                        'hook' => ($this->module->is17 ? 'displayReassurance' : ''),
                        'init' => 1,
                    ),
                    '_NAV' => array(
                        'id_option' => 'nav',
                        'name' => $this->l('Header – top navigation', 'Solo_defines'),
                        'hook' => ($this->module->is17 ? 'displayNav2' : ($this->module->is16 ? 'displayNav' : '')),
                        'init' => 1,
                    ),

                    '_HEA' => array(
                        'id_option' => 'hea',
                        'name' => $this->l('Header – main header', 'Solo_defines'),
                        'hook' => 'displayHeader',
                        'init' => 0,
                    ),
                    ($this->module->is17 ? '_PAI' : '_PBN') => array(
                        'id_option' => $this->module->is17 ? 'pai' : 'pbn',
                        'name' => $this->module->is17 ? $this->l('Product page - additional info section', 'Solo_defines') : $this->l('Product page - below "add to cart" button', 'Solo_defines'),
                        'hook' => $this->module->is17 ? 'displayProductAdditionalInfo' : 'displayProductButtons',
                        'init' => 0,
                    ),
                    '_PTN' => array(
                        'id_option' => 'ptn',
                        'name' => $this->l('Product page – below product images', 'Solo_defines'),
                        'hook' => 'displayAfterProductThumbs',
                        'init' => 0,
                    ),
                    '_PRC' => array(
                        'id_option' => 'prc',
                        'name' => $this->l('Product page - right column', 'Solo_defines'),
                        'hook' => 'displayRightColumnProduct',
                        'init' => 0,
                    ),
                    '_PLC' => array(
                        'id_option' => 'plc',
                        'name' => $this->l('Product page - left column', 'Solo_defines'),
                        'hook' => 'displayLeftColumnProduct',
                        'init' => 0,
                    ),
                    '_SCF' => array(
                        'id_option' => 'scf',
                        'name' => $this->l('Checkout page', 'Solo_defines'),
                        'hook' => 'displayShoppingCartFooter',
                        'init' => 0,
                    ),
                    '_FOO' => array(
                        'id_option' => 'foo',
                        'name' => $this->l('Footer', 'Solo_defines'),
                        'hook' => 'displayFooter',
                        'init' => 0,
                    ),
                    '_CUS' => array(
                        'id_option' => 'cus',
                        'name' => $this->l('Custom hook', 'Solo_defines'),
                        'hook' => 'displaySoLoSocialLogin',
                        'init' => 0,
                    ),
                );
                if (!$this->module->is17)
                    unset($this->hooks['_PTN'], $this->hooks['_LGP']);
            }
            return $this->hooks;
        } //field_list.
        elseif (trim($fields) == 'fields_list') {
            if (!(isset($this->fields_list)) || !$this->fields_list) {
                $this->fields_list = array(
                    'id_ets_solo_user' => array(
                        'title' => $this->l('ID', 'Solo_defines'),
                        'align' => 'center',
                        'type' => 'int',
                        'filter_key' => 'su!id_ets_solo_user',
                        'class' => 'fixed-width-xs',
                        'orderby' => true,
                        'search' => true,
                    ),
                    'profile_img' => array(
                        'title' => $this->l('Profile picture', 'Solo_defines'),
                        'type' => 'text',
                        'align' => 'center ets_avatar',
                        'callback' => 'displayProfileImg',
                        'callback_object' => $this,
                        'orderby' => false,
                        'search' => false,
                    ),
                    'customer_name' => array(
                        'title' => $this->l('Customer name', 'Solo_defines'),
                        'type' => 'text',
                        'havingFilter' => true,
                        'orderby' => true,
                        'search' => true,
                    ),
                    'email' => array(
                        'title' => $this->l('Email', 'Solo_defines'),
                        'type' => 'text',
                        'filter_key' => 'c!email',
                        'orderby' => true,
                        'search' => true,
                    ),
                    'discount_code' => array(
                        'title' => $this->l('Discount Code', 'Solo_defines'),
                        'type' => 'text',
                        'callback' => 'displayCartRule',
                        'callback_object' => $this,
                        'filter_key' => 'su!discount_code',
                        'orderby' => true,
                        'search' => true,
                    ),
                    'last_login_type' => array(
                        'title' => $this->l('Last login type', 'Solo_defines'),
                        'type' => 'select',
                        'list' => $this->loginTypes(),
                        'filter_key' => 'su!last_login_type',
                        'orderby' => false,
                        'search' => false,
                    ),
                    'total_spent' => array(
                        'title' => $this->l('Total spent', 'Solo_defines'),
                        'align' => 'text-center',
                        'type' => 'price',
                        'callback' => 'setOrderCurrency',
                        'callback_object' => $this->module,
                        'badge_success' => true,
                        'havingFilter' => true,
                        'orderby' => true,
                        'search' => true,
                    ),
                    'date_add' => array(
                        'title' => $this->l('Registered date', 'Solo_defines'),
                        'type' => 'datetime',
                        'filter_key' => 'c!date_add',
                        'orderby' => true,
                        'search' => true,
                    ),
                    'last_login_time' => array(
                        'title' => $this->l('Last login time', 'Solo_defines'),
                        'type' => 'datetime',
                        'filter_key' => 'su!last_login_time',
                        'orderby' => true,
                        'search' => true,
                    ),
                );
            }
            return $this->fields_list;
        } //field_list.
        elseif (trim($fields) == 'social_networks') {
            if (!(isset($this->social_networks)) || !$this->social_networks) {
                $this->social_networks = array(
                    'form' => array(
                        'legend' => array(
                            'title' => '',
                            'icon' => 'icon-AdminAdmin'
                        ),
                        'input' => array(),
                        'submit' => array(
                            'title' => $this->l('Save', 'Solo_defines'),
                        ),
                        'name' => 'social_networks',
                    ),
                    'configs' => array(
                        // Facebook.
                        'ETS_SOLO_FACEBOOK_ENABLED' => array(
                            'label' => $this->l('Facebook', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'fb',
                        ),
                        'ETS_SOLO_FACEBOOK_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'fb',
                        ),
                        'ETS_SOLO_FACEBOOK_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'fb',
                        ),
                        'ETS_SOLO_FACEBOOK_CALLBACK' => array(
                            'label' => $this->l('Redirect URI', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URIs for each language. Copy all the URIs above and add them to the authorized redirect URIs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'fb',
                        ),
                        //Google
                        'ETS_SOLO_GOOGLE_ENABLED' => array(
                            'label' => $this->l('Google', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'gl',
                        ),
                        'ETS_SOLO_GOOGLE_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'gl',
                        ),
                        'ETS_SOLO_GOOGLE_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'gl',
                        ),
                        'ETS_SOLO_GOOGLE_CALLBACK' => array(
                            'label' => $this->l('Redirect URI', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URIs for each language. Copy all the URIs above and add them to the authorized redirect URIs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'gl',
                        ),
                        //Twitter
                        'ETS_SOLO_TWITTER_ENABLED' => array(
                            'label' => $this->l('X', 'Solo_defines'),//Twitter
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'tw',
                        ),
                        'ETS_SOLO_TWITTER_APP_ID' => array(
                            'label' => $this->l('Application Key', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'tw',
                        ),
                        'ETS_SOLO_TWITTER_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'tw',
                        ),
                        'ETS_SOLO_TWITTER_CALLBACK' => array(
                            'label' => $this->l('Callback URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Callback URLs for each language. Copy all the URLs above and add them to the authorized callback URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'tw',
                        ),
                        // Linkedin
                        'ETS_SOLO_LINKEDIN_ENABLED' => array(
                            'label' => $this->l('LinkedIn', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'li',
                        ),
                        'ETS_SOLO_LINKEDIN_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'li',
                        ),
                        'ETS_SOLO_LINKEDIN_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'li',
                        ),
                        'ETS_SOLO_LINKEDIN_CALLBACK' => array(
                            'label' => $this->l('Redirect URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URLs for each language. Copy all the URLs above and add them to the authorized redirect URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'li',
                        ),
                        //WordPress
                        'ETS_SOLO_WORDPRESS_ENABLED' => array(
                            'label' => $this->l('WordPress', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'wp',
                        ),
                        'ETS_SOLO_WORDPRESS_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'wp',
                        ),
                        'ETS_SOLO_WORDPRESS_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'wp',
                        ),
                        'ETS_SOLO_WORDPRESS_CALLBACK' => array(
                            'label' => $this->l('Redirect URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URLs for each language. Copy all the URLs above and add them to the authorized redirect URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'wp',
                        ),
                        //Tumblr
                        'ETS_SOLO_TUMBLR_ENABLED' => array(
                            'label' => $this->l('Tumblr', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'tb',
                        ),
                        'ETS_SOLO_TUMBLR_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'tb',
                        ),
                        'ETS_SOLO_TUMBLR_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'tb',
                        ),
                        'ETS_SOLO_TUMBLR_CALLBACK' => array(
                            'label' => $this->l('Callback URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Callback URLs for each language. Copy all the URLs above and add them to the authorized callback URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'tb',
                        ),
                        //Yahoo!
                        'ETS_SOLO_YAHOO_ENABLED' => array(
                            'label' => $this->l('Yahoo!', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'yh',
                        ),
                        'ETS_SOLO_YAHOO_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'yh',
                        ),
                        'ETS_SOLO_YAHOO_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'yh',
                        ),
                        'ETS_SOLO_YAHOO_CALLBACK' => array(
                            'label' => $this->l('Redirect URI', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URIs for each language. Copy all the URIs above and add them to the authorized redirect URIs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'yh',
                        ),
                        'ETS_SOLO_MICROSOFT_ENABLED' => array(
                            'label' => $this->l('Microsoft', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'ms',
                        ),
                        'ETS_SOLO_MICROSOFT_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'ms',
                        ),
                        'ETS_SOLO_MICROSOFT_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'ms',
                        ),
                        'ETS_SOLO_MICROSOFT_CALLBACK' => array(
                            'label' => $this->l('Redirect URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URLs for each language. Copy all the URLs above and add them to the authorized redirect URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'ms',
                        ),
                        //Reddit
                        'ETS_SOLO_REDDIT_ENABLED' => array(
                            'label' => $this->l('Reddit', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'red',
                        ),
                        'ETS_SOLO_REDDIT_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'red',
                        ),
                        'ETS_SOLO_REDDIT_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'red',
                        ),
                        'ETS_SOLO_REDDIT_CALLBACK' => array(
                            'label' => $this->l('Redirect URI', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URIs for each language. Copy all the URIs above and add them to the authorized redirect URIs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'red',
                        ),
                        //GitHub
                        'ETS_SOLO_GITHUB_ENABLED' => array(
                            'label' => $this->l('GitHub', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'git',
                        ),
                        'ETS_SOLO_GITHUB_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'git',
                        ),
                        'ETS_SOLO_GITHUB_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'git',
                        ),
                        'ETS_SOLO_GITHUB_CALLBACK' => array(
                            'label' => $this->l('Callback URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Callback URLs for each language. Copy all the URLs above and add them to the authorized callback URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'git',
                        ),
                        //GitLab
                        'ETS_SOLO_GITLAB_ENABLED' => array(
                            'label' => $this->l('GitLab', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'gil',
                        ),
                        'ETS_SOLO_GITLAB_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'gil',
                        ),
                        'ETS_SOLO_GITLAB_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'gil',
                        ),
                        'ETS_SOLO_GITLAB_CALLBACK' => array(
                            'label' => $this->l('Redirect URI', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URIs for each language. Copy all the URIs above and add them to the authorized redirect URIs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'gil',
                        ),
                        // Stackexchange
                        'ETS_SOLO_STACKEXC_ENABLED' => array(
                            'label' => $this->l('StackExchange', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'skc',
                        ),
                        'ETS_SOLO_STACKEXC_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'skc',
                        ),
                        'ETS_SOLO_STACKEXC_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'skc',
                        ),
                        'ETS_SOLO_STACKEXC_CALLBACK' => array(
                            'label' => $this->l('OAuth domain', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('To register an StackExchange app, you need to enter your OAuth domain and your website URL.', 'Solo_defines'),
                            'group' => 'skc',
                        ),
                        // Yandex
                        'ETS_SOLO_YANDEX_ENABLED' => array(
                            'label' => $this->l('Yandex', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'yan',
                        ),
                        'ETS_SOLO_YANDEX_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'yan',
                        ),
                        'ETS_SOLO_YANDEX_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'yan',
                        ),
                        'ETS_SOLO_YANDEX_CALLBACK' => array(
                            'label' => $this->l('Callback URI', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Callback URIs for each language. Copy all the URIs above and add them to the authorized callback URIs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'yan',
                        ),
                        // Dribbble
                        'ETS_SOLO_DRIBBBLE_ENABLED' => array(
                            'label' => $this->l('Dribbble', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'dri',
                        ),
                        'ETS_SOLO_DRIBBBLE_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'dri',
                        ),
                        'ETS_SOLO_DRIBBBLE_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'dri',
                        ),
                        'ETS_SOLO_DRIBBBLE_CALLBACK' => array(
                            'label' => $this->l('Callback URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Callback URLs for each language. Copy all the URLs above and add them to the authorized callback URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'dri',
                        ),
                        // Foursquare
                        'ETS_SOLO_FOURSQUARE_ENABLED' => array(
                            'label' => $this->l('Foursquare', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'fsq',
                        ),
                        'ETS_SOLO_FOURSQUARE_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'fsq',
                        ),
                        'ETS_SOLO_FOURSQUARE_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'fsq',
                        ),
                        'ETS_SOLO_FOURSQUARE_CALLBACK' => array(
                            'label' => $this->l('Redirect URI', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URIs for each language. Copy all the URIs above and add them to the authorized redirect URIs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'fsq',
                        ),
                        // Odnoklassniki
                        'ETS_SOLO_ODNOK_ENABLED' => array(
                            'label' => $this->l('Odnoklassniki', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'oln',
                        ),
                        'ETS_SOLO_ODNOK_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'oln',
                        ),
                        'ETS_SOLO_ODNOK_APP_KEY' => array(
                            'label' => $this->l('Application Key', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'oln',
                        ),
                        'ETS_SOLO_ODNOK_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'oln',
                        ),
                        'ETS_SOLO_ODNOK_CALLBACK' => array(
                            'label' => $this->l('Redirect URI', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URIs for each language. Copy all the URIs above and add them to the authorized redirect URIs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'oln',
                        ),
                        // paypal
                        'ETS_SOLO_PAYPAL_ENABLED' => array(
                            'label' => $this->l('PayPal', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'pay',
                        ),
                        'ETS_SOLO_PAYPAL_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'pay',
                        ),
                        'ETS_SOLO_PAYPAL_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'pay',
                        ),
                        'ETS_SOLO_PAYPAL_SANDBOX_MODE' => array(
                            'label' => $this->l('Use sandbox mode', 'Solo_defines'),
                            'type' => 'switch',
                            'default' => 1,
                            'group' => 'pay',
                        ),
                        'ETS_SOLO_PAYPAL_CALLBACK' => array(
                            'label' => $this->l('Return URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Return URLs for each language. Copy all the URLs above and add them to the authorized return URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'pay',
                        ),
                        // Amazon
                        'ETS_SOLO_AMAZON_ENABLED' => array(
                            'label' => $this->l('Amazon', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'ozo',
                        ),
                        'ETS_SOLO_AMAZON_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'ozo',
                        ),
                        'ETS_SOLO_AMAZON_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'ozo',
                        ),
                        'ETS_SOLO_AMAZON_CALLBACK' => array(
                            'label' => $this->l('Return URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Return URLs for each language. Copy all the URLs above and add them to the authorized return URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'ozo',
                        ),
                        // Pinterest
                        'ETS_SOLO_PINTEREST_ENABLED' => array(
                            'label' => $this->l('Pinterest', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'pin',
                        ),
                        'ETS_SOLO_PINTEREST_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'pin',
                        ),
                        'ETS_SOLO_PINTEREST_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'pin',
                        ),
                        'ETS_SOLO_PINTEREST_CALLBACK' => array(
                            'label' => $this->l('Redirect URI', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URIs for each language. Copy all the URIs above and add them to the authorized redirect URIs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'pin',
                        ),
                        // Weibo
                        'ETS_SOLO_WEIBO_ENABLED' => array(
                            'label' => $this->l('Weibo', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'wei',
                        ),
                        'ETS_SOLO_WEIBO_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'wei',
                        ),
                        'ETS_SOLO_WEIBO_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'wei',
                        ),
                        'ETS_SOLO_WEIBO_CALLBACK' => array(
                            'label' => $this->l('Redirect URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URLs for each language. Copy all the URLs above and add them to the authorized redirect URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'wei',
                        ),
                        // Vimeo
                        'ETS_SOLO_VIMEO_ENABLED' => array(
                            'label' => $this->l('Vimeo', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'vim',
                        ),
                        'ETS_SOLO_VIMEO_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'vim',
                        ),
                        'ETS_SOLO_VIMEO_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'vim',
                        ),
                        'ETS_SOLO_VIMEO_CALLBACK' => array(
                            'label' => $this->l('Callback URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Callback URLs for each language. Copy all the URLs above and add them to the authorized callback URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'vim',
                        ),
                        // dropbox
                        'ETS_SOLO_DROPBOX_ENABLED' => array(
                            'label' => $this->l('Dropbox', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'dro',
                        ),
                        'ETS_SOLO_DROPBOX_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'dro',
                        ),
                        'ETS_SOLO_DROPBOX_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'dro',
                        ),
                        'ETS_SOLO_DROPBOX_CALLBACK' => array(
                            'label' => $this->l('Redirect URI', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URIs for each language. Copy all the URIs above and add them to the authorized redirect URIs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'dro',
                        ),
                        // Mailru
                        'ETS_SOLO_MAILRU_ENABLED' => array(
                            'label' => $this->l('Mailru', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'mai',
                        ),
                        'ETS_SOLO_MAILRU_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'mai',
                        ),
                        'ETS_SOLO_MAILRU_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'mai',
                        ),
                        'ETS_SOLO_MAILRU_CALLBACK' => array(
                            'label' => $this->l('OAuth domain', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('After registering a new app on Mail.ru, please download the receive.html file and put it in the root folder of your site.', 'Solo_defines'),
                            'group' => 'mai',
                        ),
                        // Vkontakte
                        'ETS_SOLO_VKONTAKTE_ENABLED' => array(
                            'label' => $this->l('Vkontakte', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'vko',
                        ),
                        'ETS_SOLO_VKONTAKTE_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'vko',
                        ),
                        'ETS_SOLO_VKONTAKTE_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'vko',
                        ),
                        'ETS_SOLO_VKONTAKTE_CALLBACK' => array(
                            'label' => $this->l('Redirect URI', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URIs for each language. Copy all the URIs above and add them to the authorized redirect URIs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'vko',
                        ),
                        // meetup
                        'ETS_SOLO_MEETUP_ENABLED' => array(
                            'label' => $this->l('Meetup', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'mee',
                        ),
                        'ETS_SOLO_MEETUP_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'mee',
                        ),
                        'ETS_SOLO_MEETUP_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'mee',
                        ),
                        'ETS_SOLO_MEETUP_CALLBACK' => array(
                            'label' => $this->l('Callback URI', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Callback URIs for each language. Copy all the URIs above and add them to the authorized callback URIs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'mee',
                        ),
                        // bitbucket
                        'ETS_SOLO_BITBUCKET_ENABLED' => array(
                            'label' => $this->l('BitBucket', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'bit',
                        ),
                        'ETS_SOLO_BITBUCKET_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'bit',
                        ),
                        'ETS_SOLO_BITBUCKET_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'bit',
                        ),
                        'ETS_SOLO_BITBUCKET_CALLBACK' => array(
                            'label' => $this->l('Callback URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Callback URLs for each language. Copy all the URLs above and add them to the authorized callback URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'bit',
                        ),
                        // Discord
                        'ETS_SOLO_DISCORD_ENABLED' => array(
                            'label' => $this->l('Discord', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'dis',
                        ),
                        'ETS_SOLO_DISCORD_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'dis',
                        ),
                        'ETS_SOLO_DISCORD_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'dis',
                        ),
                        'ETS_SOLO_DISCORD_CALLBACK' => array(
                            'label' => $this->l('Redirect URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Redirect URLs for each language. Copy all the URLs above and add them to the authorized redirect URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'dis',
                        ),
                        // Disqus
                        'ETS_SOLO_DISQUS_ENABLED' => array(
                            'label' => $this->l('Disqus', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'dqs',
                        ),
                        'ETS_SOLO_DISQUS_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'dqs',
                        ),
                        'ETS_SOLO_DISQUS_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'dqs',
                        ),
                        'ETS_SOLO_DISQUS_CALLBACK' => array(
                            'label' => $this->l('Callback URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Callback URLs for each language. Copy all the URLs above and add them to the authorized callback URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'dqs',
                        ),
                        // Line
                        'ETS_SOLO_LINE_ENABLED' => array(
                            'label' => $this->l('Line', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'lin',
                        ),
                        'ETS_SOLO_LINE_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'lin',
                        ),
                        'ETS_SOLO_LINE_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'lin',
                        ),
                        'ETS_SOLO_LINE_CALLBACK' => array(
                            'label' => $this->l('Callback URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Callback URLs for each language. Copy all the URLs above and add them to the authorized callback URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'lin',
                        ),
                        // TikTok
                        'ETS_SOLO_TIKTOK_ENABLED' => array(
                            'label' => $this->l('TikTok', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'tik',
                        ),
                        'ETS_SOLO_TIKTOK_APP_ID' => array(
                            'label' => $this->l('Client key', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'tik',
                        ),
                        'ETS_SOLO_TIKTOK_APP_SECRET' => array(
                            'label' => $this->l('Client secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'tik',
                        ),
                        'ETS_SOLO_TIKTOK_CALLBACK' => array(
                            'label' => $this->l('Callback URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Callback URLs for each language. Copy all the URLs above and add them to the authorized callback URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'tik',
                        ),
                        // Blizzard
                        'ETS_SOLO_BLIZZARD_ENABLED' => array(
                            'label' => $this->l('Blizzard', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => 0,
                            'group' => 'bli',
                        ),
                        'ETS_SOLO_BLIZZARD_APP_ID' => array(
                            'label' => $this->l('Application ID', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'group' => 'bli',
                        ),
                        'ETS_SOLO_BLIZZARD_APP_SECRET' => array(
                            'label' => $this->l('Application Secret', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'jsType' => 'string',
                            'link' => '',
                            'group' => 'bli',
                        ),
                        'ETS_SOLO_BLIZZARD_CALLBACK' => array(
                            'label' => $this->l('Callback URL', 'Solo_defines'),
                            'type' => 'solo_url',
                            'desc' => $this->l('Your website may support multiple languages, requiring separate Callback URLs for each language. Copy all the URLs above and add them to the authorized callback URLs in your social network API settings to ensure seamless login for all users.', 'Solo_defines'),
                            'group' => 'bli',
                        ),
                    )
                );
            }
            return $this->social_networks;
        } //design.
        elseif (trim($fields) == 'design') {
            if (!(isset($this->design)) || !$this->design) {
                $this->design = array(
                    'form' => array(
                        'legend' => array(
                            'title' => '',
                            'icon' => 'icon-AdminAdmin'
                        ),
                        'input' => array(),
                        'submit' => array(
                            'title' => $this->l('Save', 'Solo_defines'),
                        ),
                        'name' => 'design',
                    ),
                    'configs' => array(
                        'ETS_SOLO_DISPLAY_SOCIAL_PAGE' => array(
                            'label' => $this->l('Display SOCIAL LOGIN button on', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => ($this->module->is17 ? 'lgp,trp,slw,alw' : 'trp,slw,alw'),
                            'common' => true,
                        ),
                        'ETS_SOLO_NETWORKS_ORDER' => array(
                            'label' => $this->l('Networks order', 'Solo_defines'),
                            'type' => 'hidden',
                            'default' => Solo_defines::NETWORK_ORDER,
                        ),
                        'ETS_SOLO_LOGIN_BUTTON_TYPE' => array(
                            'label' => $this->l('Login button type', 'Solo_defines'),
                            'type' => 'select',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id_option' => 'img',
                                        'name' => $this->l('Image icon', 'Solo_defines'),
                                    ),
                                    array(
                                        'id_option' => 'flat',
                                        'name' => $this->l('Flat Icon', 'Solo_defines'),
                                    ),
                                    array(
                                        'id_option' => 'name',
                                        'name' => $this->l('Icon and social network name', 'Solo_defines'),
                                    ),
                                    array(
                                        'id_option' => 'custom',
                                        'name' => $this->l('Icon and custom text', 'Solo_defines'),
                                    ),
                                ),
                                'id' => 'id_option',
                                'name' => 'name'
                            ),
                            'col' => 4,
                            'default' => array(
                                '_TRP' => 'name',
                                '_SLW' => 'custom',
                                '_ALW' => 'custom',
                                '_NAV' => 'img'
                            ),
                            'jsType' => 'string',
                        ),
                        'ETS_SOLO_FACEBOOK_TITLE' => array(
                            'label' => $this->l('Facebook title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Facebook', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Facebook', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Facebook', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_FACEBOOK_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_GOOGLE_TITLE' => array(
                            'label' => $this->l('Google title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Google', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Google', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Google', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_GOOGLE_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),

                        'ETS_SOLO_TWITTER_TITLE' => array(
                            'label' => $this->l('X title', 'Solo_defines'),//Twitter
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('X', 'Solo_defines'),//Twitter
                                '_SLW' => $this->l('Login with X', 'Solo_defines'),//Twitter
                                '_ALW' => $this->l('Login with X', 'Solo_defines'),//Twitter
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_TWITTER_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_LINKEDIN_TITLE' => array(
                            'label' => $this->l('LinkedIn title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('LinkedIn', 'Solo_defines'),
                                '_SLW' => $this->l('Login with LinkedIn', 'Solo_defines'),
                                '_ALW' => $this->l('Login with LinkedIn', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_LINKEDIN_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_WORDPRESS_TITLE' => array(
                            'label' => $this->l('WordPress title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('WordPress', 'Solo_defines'),
                                '_SLW' => $this->l('Login with WordPress', 'Solo_defines'),
                                '_ALW' => $this->l('Login with WordPress', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_WORDPRESS_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_TUMBLR_TITLE' => array(
                            'label' => $this->l('Tumblr title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Tumblr', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Tumblr', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Tumblr', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_TUMBLR_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_YAHOO_TITLE' => array(
                            'label' => $this->l('Yahoo title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Yahoo', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Yahoo', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Yahoo', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_YAHOO_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_MICROSOFT_TITLE' => array(
                            'label' => $this->l('Microsoft title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Microsoft', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Microsoft', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Microsoft', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_MICROSOFT_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),

                        'ETS_SOLO_REDDIT_TITLE' => array(
                            'label' => $this->l('Reddit title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Reddit', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Reddit', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Reddit', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_REDDIT_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_GITHUB_TITLE' => array(
                            'label' => $this->l('GitHub title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('GitHub', 'Solo_defines'),
                                '_SLW' => $this->l('Login with GitHub', 'Solo_defines'),
                                '_ALW' => $this->l('Login with GitHub', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_GITHUB_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_GITLAB_TITLE' => array(
                            'label' => $this->l('GitLab title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('GitLab', 'Solo_defines'),
                                '_SLW' => $this->l('Login with GitLab', 'Solo_defines'),
                                '_ALW' => $this->l('Login with GitLab', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_GITLAB_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_STACKEXC_TITLE' => array(
                            'label' => $this->l('StackExChange title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('StackExChange', 'Solo_defines'),
                                '_SLW' => $this->l('Login with StackExChange', 'Solo_defines'),
                                '_ALW' => $this->l('Login with StackExChange', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_STACKEXC_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_YANDEX_TITLE' => array(
                            'label' => $this->l('Yandex title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Yandex', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Yandex', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Yandex', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_YANDEX_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_DRIBBBLE_TITLE' => array(
                            'label' => $this->l('Dribbble title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Dribbble', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Dribbble', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Dribbble', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_DRIBBBLE_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_FOURSQUARE_TITLE' => array(
                            'label' => $this->l('Foursquare title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Foursquare', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Foursquare', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Foursquare', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_FOURSQUARE_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_ODNOK_TITLE' => array(
                            'label' => $this->l('Odnoklassniki title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Odnoklassniki', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Odnoklassniki', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Odnoklassniki', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_ODNOK_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),

                        'ETS_SOLO_PAYPAL_TITLE' => array(
                            'label' => $this->l('Paypal title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Paypal', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Paypal', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Paypal', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_PAYPAL_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_AMAZON_TITLE' => array(
                            'label' => $this->l('Amazon title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Amazon', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Amazon', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Amazon', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_AMAZON_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_PINTEREST_TITLE' => array(
                            'label' => $this->l('Pinterest title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Pinterest', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Pinterest', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Pinterest', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_PINTEREST_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_WEIBO_TITLE' => array(
                            'label' => $this->l('Weibo title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Weibo', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Weibo', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Weibo', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_WEIBO_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_VIMEO_TITLE' => array(
                            'label' => $this->l('Vimeo title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Vimeo', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Vimeo', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Vimeo', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_VIMEO_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_DROPBOX_TITLE' => array(
                            'label' => $this->l('Dropbox title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Dropbox', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Dropbox', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Dropbox', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_DROPBOX_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_MAILRU_TITLE' => array(
                            'label' => $this->l('Mailru title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Mailru', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Mailru', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Mailru', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_MAILRU_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_VKONTAKTE_TITLE' => array(
                            'label' => $this->l('Vkontakte title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Vkontakte', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Vkontakte', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Vkontakte', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_VKONTAKTE_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_MEETUP_TITLE' => array(
                            'label' => $this->l('Meetup title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Meetup', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Meetup', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Meetup', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_MEETUP_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_BITBUCKET_TITLE' => array(
                            'label' => $this->l('BitBucket title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('BitBucket', 'Solo_defines'),
                                '_SLW' => $this->l('Login with BitBucket', 'Solo_defines'),
                                '_ALW' => $this->l('Login with BitBucket', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_BITBUCKET_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_DISCORD_TITLE' => array(
                            'label' => $this->l('Discord title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Discord', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Discord', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Discord', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_DISCORD_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_DISQUS_TITLE' => array(
                            'label' => $this->l('Disqus title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Disqus', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Disqus', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Disqus', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_DISQUS_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_LINE_TITLE' => array(
                            'label' => $this->l('Line title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Line', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Line', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Line', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_LINE_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_BLIZZARD_TITLE' => array(
                            'label' => $this->l('Blizzard title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Blizzard', 'Solo_defines'),
                                '_SLW' => $this->l('Login with Blizzard', 'Solo_defines'),
                                '_ALW' => $this->l('Login with Blizzard', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_BLIZZARD_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_TIKTOK_TITLE' => array(
                            'label' => $this->l('TikTok title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('TikTok', 'Solo_defines'),
                                '_SLW' => $this->l('Login with TikTok', 'Solo_defines'),
                                '_ALW' => $this->l('Login with TikTok', 'Solo_defines'),
                            ),
                            'enabled' => Configuration::get('ETS_SOLO_TIKTOK_ENABLED') ? 1 : 0,
                            'group' => 'custom'
                        ),
                        'ETS_SOLO_BORDER' => array(
                            'label' => $this->l('Border', 'Solo_defines'),
                            'type' => 'select',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id_option' => 'default',
                                        'name' => $this->l('Default', 'Solo_defines'),
                                    ),
                                    array(
                                        'id_option' => 'rounded',
                                        'name' => $this->l('Rounded', 'Solo_defines'),
                                    ),
                                ),
                                'id' => 'id_option',
                                'name' => 'name'
                            ),
                            'col' => 4,
                            'default' => 'rounded',
                            'jsType' => 'string',
                        ),
                        'ETS_SOLO_BUTTON_SIZE' => array(
                            'label' => $this->l('Button Size', 'Solo_defines'),
                            'type' => 'select',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id_option' => 'small',
                                        'name' => $this->l('Small (24px)', 'Solo_defines'),
                                    ),
                                    array(
                                        'id_option' => 'medium',
                                        'name' => $this->l('Medium (30px)', 'Solo_defines'),
                                    ),
                                    array(
                                        'id_option' => 'large',
                                        'name' => $this->l('Large (45px)', 'Solo_defines'),
                                    ),
                                ),
                                'id' => 'id_option',
                                'name' => 'name'
                            ),
                            'col' => 4,
                            'default' => 'medium',
                            'jsType' => 'string',
                        ),
                        'ETS_SOLO_HIDE_ON_MOBILE' => array(
                            'label' => $this->l('Hide on mobile', 'Solo_defines'),
                            'type' => 'switch',
                            'default' => 0,
                        ),

                        //slide login.
                        'ETS_SOLO_STICKY_BTN_BG' => array(
                            'label' => $this->l('Sticky sign in button background color', 'Solo_defines'),
                            'type' => 'color',
                            'size' => 8,
                            'validate' => 'isColor',
                            'default' => '#2fb5d2',
                            'design' => array('_SLW'),
                        ),
                        'ETS_SOLO_STICKY_BTN_HOVER' => array(
                            'label' => $this->l('Sticky sign in button hover color', 'Solo_defines'),
                            'type' => 'color',
                            'size' => 8,
                            'validate' => 'isColor',
                            'default' => '#2592a9',
                            'design' => array('_SLW'),
                        ),
                        'ETS_SOLO_STICKY_BTN_TEXT' => array(
                            'label' => $this->l('Sticky sign in button text color', 'Solo_defines'),
                            'type' => 'color',
                            'size' => 8,
                            'validate' => 'isColor',
                            'default' => '#ffffff',
                            'design' => array('_SLW'),
                        ),
                        'ETS_SOLO_WIDGET_POSITION' => array(
                            'label' => $this->l('Widget position', 'Solo_defines'),
                            'type' => 'solo_option',
                            'values' => array(
                                array(
                                    'id_option' => 'left',
                                    'name' => $this->l('Left', 'Solo_defines')
                                ),
                                array(
                                    'id_option' => 'right',
                                    'name' => $this->l('Right', 'Solo_defines')
                                ),
                            ),
                            'default' => 'right',
                            'jsType' => 'string',
                            'design' => array('_SLW'),
                        ),
                        //end slide login page.

                        //account login and slide login in.
                        'ETS_SOLO_WIDGET_EVENT' => array(
                            'label' => $this->l('How to open widget', 'Solo_defines'),
                            'type' => 'solo_option',
                            'values' => array(
                                array(
                                    'id_option' => 'hover',
                                    'name' => $this->l('Hover on "Sign in" to open login widget', 'Solo_defines')
                                ),
                                array(
                                    'id_option' => 'click',
                                    'name' => $this->l('Click on "Sign in" to open login widget (Open login popup)', 'Solo_defines')
                                ),
                            ),
                            'default' => 'hover',
                            'jsType' => 'string',
                            'design' => array('_SLW', '_ALW'),
                        ),
                        //end account login slide and login.

                        'ETS_SOLO_LOGIN_TITLE' => array(
                            'label' => $this->l('Login title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => array(
                                '_TRP' => $this->l('Log in with social account', 'Solo_defines'),
                                '_NAV' => '',
                            ),
                        ),
                        'ETS_SOLO_ADDITIONAL_DESC' => array(
                            'label' => $this->l('Additional description', 'Solo_defines'),
                            'type' => 'textarea',
                            'cols' => 60,
                            'rows' => 3,
                            'lang' => true,
                            'jsType' => 'string',
                            'default' => '',
                        ),

                        'ETS_SOLO_SOCIAL_TO_USE' => array(
                            'label' => $this->l('Social networks to use', 'Solo_defines'),
                            'type' => 'select',
                            'options' => array(
                                'query' => $this->optionSocialNetworks(),
                                'id' => 'id_option',
                                'name' => 'name',
                            ),
                            'multiple' => true,
                            'default' => 'all',
                        ),
                        'ETS_SOLO_GOOGLE_THEME' => array(
                            'label' => $this->l('Style', 'Solo_defines'),
                            'type' => 'radio',
                            'values' => array(
                                array(
                                    'id' => 'google_theme_light',
                                    'value' => 'light',
                                    'label' => $this->l('Light', 'Solo_defines'),
                                ),
                                array(
                                    'id' => 'google_theme_dark',
                                    'value' => 'dark',
                                    'label' => $this->l('Dark', 'Solo_defines'),
                                ),
                            ),
                            'default' => 'light',
                        ),
                    )
                );
            }
            return $this->design;
        } //discount.
        elseif (trim($fields) == 'discount') {
            if (!(isset($this->discount)) || !$this->discount) {
                $currencies = Currency::getCurrenciesByIdShop($this->context->shop->id);
                $this->discount = array(
                    'form' => array(
                        'legend' => array(
                            'title' => $this->l('Discounts', 'Solo_defines'),
                        ),
                        'input' => array(),
                        'submit' => array(
                            'title' => $this->l('Save', 'Solo_defines'),
                        ),
                        'name' => 'discount',
                    ),
                    'configs' => array(
                        'ETS_SOLO_DISCOUNT_ENABLED' => array(
                            'label' => $this->l('Enable discount', 'Solo_defines'),
                            'type' => 'switch',
                            'default' => 0,
                            'desc' => $this->l('Give customer discount when they login using their social network accounts', 'Solo_defines'),
                        ),
                        'ETS_SOLO_DISCOUNT_OPTION' => array(
                            'label' => $this->l('Discount options', 'Solo_defines'),
                            'type' => 'solo_option',
                            'values' => array(
                                array(
                                    'id_option' => 'fixed',
                                    'name' => $this->l('Fixed discount code', 'Solo_defines')
                                ),
                                array(
                                    'id_option' => 'auto',
                                    'name' => $this->l('Generate discount code automatically', 'Solo_defines')
                                ),
                            ),
                            'default' => 'auto',
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_DISCOUNT_CODE' => array(
                            'label' => $this->l('Discount code', 'Solo_defines'),
                            'type' => 'text',
                            'col' => '2',
                            'required' => true,
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_FREE_SHIPPING' => array(
                            'label' => $this->l('Free shipping', 'Solo_defines'),
                            'type' => 'switch',
                            'default' => 0,
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_APPLY_DISCOUNT' => array(
                            'label' => $this->l('Apply a discount', 'Solo_defines'),
                            'type' => 'solo_option',
                            'values' => array(
                                array(
                                    'id_option' => 'percent',
                                    'name' => $this->l('Percentage (%)', 'Solo_defines')
                                ),
                                array(
                                    'id_option' => 'amount',
                                    'name' => $this->l('Amount', 'Solo_defines')
                                ),
                                array(
                                    'id_option' => 'off',
                                    'name' => $this->module->smarty_back_end(array('key' => 'ICON', 'text' => 'remove color_danger')) . $this->l('None', 'Solo_defines')
                                ),
                            ),
                            'default' => 'percent',
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_REDUCTION_PERCENT' => array(
                            'label' => $this->l('Discount percentage', 'Solo_defines'),
                            'type' => 'text',
                            'suffix' => '%',
                            'required' => true,
                            'col' => '2',
                            'desc' => $this->module->smarty_back_end(array('key' => 'ICON', 'text' => 'warning-sign')) . $this->l('Does not apply to the shipping costs', 'Solo_defines'),
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_REDUCTION_AMOUNT' => array(
                            'label' => $this->l('Amount', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'col' => '2',
                            'default' => '0',
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_ID_CURRENCY' => array(
                            'label' => null,
                            'type' => 'select',
                            'options' => array(
                                'query' => $currencies,
                                'id' => 'id_currency',
                                'name' => 'name',
                            ),
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_REDUCTION_TAX' => array(
                            'label' => null,
                            'type' => 'select',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id_option' => 0,
                                        'name' => $this->l('Tax excluded', 'Solo_defines')
                                    ),
                                    array(
                                        'id_option' => 1,
                                        'name' => $this->l('Tax included', 'Solo_defines')
                                    ),
                                ),
                                'id' => 'id_option',
                                'name' => 'name',
                            ),
                            'default' => '0',
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_APPLY_DISCOUNT_IN' => array(
                            'label' => $this->l('Discount availability', 'Solo_defines'),
                            'type' => 'text',
                            'required' => true,
                            'suffix' => 'day(s)',
                            'col' => '2',
                            'default' => '30',
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_DISCOUNT_PREFIX' => array(
                            'label' => $this->l('Discount prefix', 'Solo_defines'),
                            'type' => 'text',
                            'col' => '2',
                            'default' => 'SOCIAL',
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_DISCOUNT_NAME' => array(
                            'label' => $this->l('Voucher description', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'required' => true,
                            'default' => $this->l('SOCIAL LOGIN', 'Solo_defines'),
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_DISCOUNT_NETWORKS' => array(
                            'label' => $this->l('Networks to offer discount', 'Solo_defines'),
                            'type' => 'select',
                            'options' => array(
                                'query' => $this->optionSocialNetworks(),
                                'id' => 'id_option',
                                'name' => 'name',
                            ),
                            'multiple' => true,
                            'default' => 'all',
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_SEND_DISCOUNT' => array(
                            'label' => $this->l('Send discount', 'Solo_defines'),
                            'type' => 'select',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id_option' => 'email',
                                        'name' => $this->l('Via email', 'Solo_defines')
                                    ),
                                    array(
                                        'id_option' => 'popup',
                                        'name' => $this->l('Via popup', 'Solo_defines')
                                    ),
                                    array(
                                        'id_option' => 'both',
                                        'name' => $this->l('Both', 'Solo_defines')
                                    ),
                                ),
                                'id' => 'id_option',
                                'name' => 'name',
                            ),
                            'default' => 'both',
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_EMAIL_SUBJECT' => array(
                            'label' => $this->l('Email subject', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'default' => $this->l('Here is your discount!', 'Solo_defines'),
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_EMAIL_CONTENT' => array(
                            'label' => $this->l('Email content', 'Solo_defines'),
                            'type' => 'textarea',
                            'cols' => 60,
                            'rows' => 3,
                            'lang' => true,
                            'autoload_rte' => true,
                            'default' => $this->module->smarty_back_end(array('key' => 'EMAIL_CONTENT')),
                            'desc' => $this->module->smarty_back_end(array('key' => 'SHORTCODE')),
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_POPUP_TITLE' => array(
                            'label' => $this->l('Popup title', 'Solo_defines'),
                            'type' => 'text',
                            'lang' => true,
                            'default' => $this->l('Congratulations! You get our discount', 'Solo_defines'),
                            'group' => 'discount',
                        ),
                        'ETS_SOLO_POPUP_CONTENT' => array(
                            'label' => $this->l('Popup content', 'Solo_defines'),
                            'type' => 'textarea',
                            'cols' => 60,
                            'rows' => 3,
                            'lang' => true,
                            'autoload_rte' => true,
                            'default' => $this->module->smarty_back_end(array('key' => 'POPUP_CONTENT')),
                            'desc' => $this->module->smarty_back_end(array('key' => 'SHORTCODE')),
                            'group' => 'discount',
                        ),
                    )
                );
            }
            return $this->discount;
        } //settings.
        elseif (trim($fields) == 'settings') {
            if (!(isset($this->settings)) || !$this->settings) {
                $this->settings = array(
                    'form' => array(
                        'legend' => array(
                            'title' => $this->l('Settings', 'Solo_defines'),
                        ),
                        'input' => array(),
                        'submit' => array(
                            'title' => $this->l('Save', 'Solo_defines'),
                        ),
                        'name' => 'settings',
                    ),
                    'configs' => array(
                        'ETS_SOLO_CUSTOMER_GROUP' => array(
                            'label' => $this->l('Customer group', 'Solo_defines'),
                            'type' => 'select',
                            'options' => array(
                                'query' => (array)Group::getGroups($this->context->language->id),
                                'id' => 'id_group',
                                'name' => 'name'
                            ),
                            'col' => 6,
                            'desc' => $this->l('Assign customer to this group when they register using social buttons', 'Solo_defines'),
                            'default' => Configuration::get('PS_CUSTOMER_GROUP'),
                        ),
                        'ETS_SOLO_MY_ACCOUNT_PAGE' => array(
                            'label' => $this->l('Enable "Social Networks" menu in my account page', 'Solo_defines'),
                            'type' => 'switch',
                            'default' => 1,
                            'desc' => $this->l('Allow customer to see their social network connection status', 'Solo_defines'),
                        ),
                        'ETS_SOLO_SEND_PASSWORD' => array(
                            'label' => $this->l('Send password to customer', 'Solo_defines'),
                            'type' => 'switch',
                            'default' => 1,
                            'desc' => $this->l('Send a plain password to customer via email', 'Solo_defines')
                        ),
                    )
                );
            }
            return $this->settings;
        } //configTabs.
        elseif (trim($fields) == 'tabs') {
            if (!(isset(self::$configTabs)) || !self::$configTabs) {
                self::$configTabs = array(
                    array(
                        'name' => 'dashboard',
                        'label' => $this->l('Dashboard', 'Solo_defines'),
                    ),
                    array(
                        'name' => 'social_networks',
                        'label' => $this->l('Social Networks', 'Solo_defines'),
                    ),
                    array(
                        'name' => 'design',
                        'label' => $this->l('Positions', 'Solo_defines'),
                    ),
                    array(
                        'name' => 'discount',
                        'label' => $this->l('Discounts', 'Solo_defines'),
                    ),
                    array(
                        'name' => 'statistic',
                        'label' => $this->l('Statistics', 'Solo_defines'),
                    ),
                    array(
                        'name' => 'social_users',
                        'label' => $this->l('Social users', 'Solo_defines'),
                    ),
                    array(
                        'name' => 'settings',
                        'label' => $this->l('Settings', 'Solo_defines'),
                    )
                );
            }
            return self::$configTabs;
        }
    }

    public function loginTypes()
    {
        $res = array();
        if ($this->socials) {
            foreach ($this->socials as $key => $social) {
                $res[$key] = $social['name'];
            }
        }
        return $res;
    }

    public function optionSocialNetworks()
    {
        $_socials = array('all' => array(
            'name' => $this->l('All networks', 'Solo_defines'),
            'id_option' => 'all'
        ));
        if (($socials = $this->getFields('socials'))) {
            foreach ($socials as $key => $social) {
                if ((int)Configuration::get('ETS_SOLO_' . Tools::strtoupper($social['label']) . '_ENABLED'))
                    $_socials[$key] = $social;
            }
        }
        return $_socials;
    }

    public function displayProfileImg($value, $tr)
    {
        return $this->module->callBack(array(
            'value' => $value,
            'tr' => $tr,
            'type' => 'img'
        ));
    }

    public function displayCartRule($value, $tr)
    {
        if ($value) {
            $id_cart_rule = CartRule::getIdByCode($value);
            $cart_rule = new CartRule($id_cart_rule);
            if (!$cart_rule->quantity) {
                $label = $this->l('(Used by customer)', 'Solo_defines');
            } elseif (strtotime($cart_rule->date_to) < time()) {
                $label = $this->l('(Expired)', 'Solo_defines');
            }
            return $this->module->callBack(array(
                'value' => $value,
                'tr' => $tr,
                'type' => 'rule',
                'label' => isset($label) && $label ? $label : null,
            ));
        }
        return null;
    }
}
