<?php
/**
 * 2013-2024 2N Technologies
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@2n-tech.com so we can send you a copy immediately.
 *
 * @author    2N Technologies <contact@2n-tech.com>
 * @copyright 2013-2024 2N Technologies
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/AwsNtbr.php';
require_once dirname(__FILE__) . '/classes/Backups.php';
require_once dirname(__FILE__) . '/classes/BoxNtbr.php';
require_once dirname(__FILE__) . '/classes/ConfigNtbr.php';
require_once dirname(__FILE__) . '/classes/DropboxNtbr.php';
require_once dirname(__FILE__) . '/classes/YandexNtbr.php';
require_once dirname(__FILE__) . '/classes/FtpNtbr.php';
require_once dirname(__FILE__) . '/classes/GlobConfNtbr.php';
require_once dirname(__FILE__) . '/classes/GooglecloudNtbr.php';
require_once dirname(__FILE__) . '/classes/GoogledriveNtbr.php';
require_once dirname(__FILE__) . '/classes/OnedriveNtbr.php';
require_once dirname(__FILE__) . '/classes/OwncloudNtbr.php';
require_once dirname(__FILE__) . '/classes/PcloudNtbr.php';
require_once dirname(__FILE__) . '/classes/ScanSizeNtbr.php';
require_once dirname(__FILE__) . '/classes/ShadowDriveNtbr.php';
require_once dirname(__FILE__) . '/classes/SugarsyncNtbr.php';
require_once dirname(__FILE__) . '/classes/WebdavNtbr.php';
require_once dirname(__FILE__) . '/lib/aws/Aws.php';
require_once dirname(__FILE__) . '/lib/box/Box.php';
require_once dirname(__FILE__) . '/lib/dropbox/Dropbox.php';
require_once dirname(__FILE__) . '/lib/yandex/Yandex.php';
require_once dirname(__FILE__) . '/lib/googlecloud/GoogleCloud.php';
require_once dirname(__FILE__) . '/lib/googledrive/Googledrive.php';
require_once dirname(__FILE__) . '/lib/onedrive/OneDrive.php';
require_once dirname(__FILE__) . '/lib/openstack/Openstack.php';
require_once dirname(__FILE__) . '/lib/owncloud/Owncloud.php';
require_once dirname(__FILE__) . '/lib/pcloud/Pcloud.php';
require_once dirname(__FILE__) . '/lib/shadow_drive/ShadowDriveLib.php';
require_once dirname(__FILE__) . '/lib/sugarsync/Sugarsync.php';
require_once dirname(__FILE__) . '/lib/webdav/Webdav.php';

if (!class_exists('Apparatus')) {
    require_once dirname(__FILE__) . '/lib/apparatus/Apparatus.php';
}

class NtBackupAndRestore extends Module
{
    const MODULE_NAME = 'ntbackupandrestore';

    const TAB_2NT = 'NTModules';
    const NAME_TAB_2NT = 'NT Modules';
    const TAB_MODULE = 'AdminNtbackupandrestore';
    const NAME_TAB = 'NtBackupAndRestore';

    const INSTALL_SQL_FILE = 'sql/install.sql';
    const UNINSTALL_SQL_FILE = 'sql/uninstall.sql';
    const SAVE_CONFIG_FILE = 'backup/save_config.cfg';

    const RATE_LINK = 'http://addons.prestashop.com/en/ratings.php';
    const RATE_LINK_FR = 'http://addons.prestashop.com/fr/ratings.php';
    const GEOLOC_LINK = 'https://addons.prestashop.com/en/international-localization/18661-nt-geoloc-precisely-locate-your-customers-and-stores.html';
    const GEOLOC_LINK_FR = 'https://addons.prestashop.com/fr/international-localisation/18661-nt-geoloc-geolocalisez-precisement-vos-clients.html';
    const REDUC_LINK = 'https://addons.prestashop.com/en/promotions-gifts/11404-nt-reduction-easy-and-fast-massive-discount.html';
    const REDUC_LINK_FR = 'https://addons.prestashop.com/fr/promotions-cadeaux/11404-nt-reduction-promotions-en-masse-facile-et-rapide.html';
    const STATS_LINK = 'https://addons.prestashop.com/en/analytics-statistics/48694-ntstats-powerful-and-useful-statistics.html';
    const STATS_LINK_FR = 'https://addons.prestashop.com/fr/analyses-statistiques/48694-ntstats-statistiques-puissantes-et-utiles.html';
    const DEB_LINK_FR = 'https://addons.prestashop.com/fr/comptabilite-facturation/27107-nt-deb.html';
    const NT_URL_OPERATION = 'https://tm.2n-tech.com/set_operation.php?';

    public $nt_dev;

    public function __construct()
    {
        $this->name = 'ntbackupandrestore';
        $this->tab = 'administration';
        $this->version = '14.7.0';
        $this->author = '2N Technologies';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.5', 'max' => '8.1.99'];
        $key_full = '652f1358e0ab9984c886ef5fceac8675';
        $key_light = '4271039ddcff6fad89705a81386abdec';
        $this->module_key = '652f1358e0ab9984c886ef5fceac8675';
        $this->secure_key = Tools::encrypt($this->name);

        // Delete one of the class if both exists, depending on the module_key (except when in dev environment)
        if (!@Apparatus::checkFileExists(_PS_ROOT_DIR_ . '/../ntbr_mode_dev.txt')) {
            $this->nt_dev = false;

            if (Apparatus::checkFileExists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/ntbrfull.php')
                && $this->module_key == $key_light
            ) {
                unlink(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/ntbrfull.php');
            } elseif (Apparatus::checkFileExists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/ntbrlight.php')
                && $this->module_key == $key_full
            ) {
                unlink(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/ntbrlight.php');
            }
        } else {
            $this->nt_dev = true;
        }

        parent::__construct();

        $this->displayName = $this->l('2N Technologies Backup And Restore');
        $this->description = $this->l('Backup your prestashop site and easily restore it wherever you want');
        // $this->confirmUninstall = $this->l(
        // 'Do you really want to uninstall this module?');

        $this->tabs[] = [
            'parent_class' => self::TAB_2NT,
            'parent_name' => self::NAME_TAB_2NT,
            'tab_class' => self::TAB_MODULE,
            'tab_name' => self::NAME_TAB,
        ];

        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $this->tabs[] = [
                'parent_class' => 'AdminAdvancedParameters',
                'parent_name' => self::NAME_TAB_2NT,
                'tab_class' => self::TAB_MODULE . 'Tab',
                'tab_name' => self::NAME_TAB,
            ];
        } else {
            $this->tabs[] = [
                'parent_class' => 'AdminTools',
                'parent_name' => self::NAME_TAB_2NT,
                'tab_class' => self::TAB_MODULE . 'Tab',
                'tab_name' => self::NAME_TAB,
            ];
        }
    }

    /**
     * Execute a SQL file
     *
     * @param string $file_path The path of the SQL file
     *
     * @return bool Success or failure of the operation
     */
    public function executeFile($file_path)
    {
        // Check if the file exists
        if (!Apparatus::checkFileExists($file_path)) {
            return Tools::displayError('Error : no sql file !');
        } elseif (!$sql = Tools::file_get_contents($file_path)) {// Get file content
            return Tools::displayError('Error : there is a problem with your install sql file !');
        }

        $sql_replace = str_replace(['PREFIX_', 'ENGINE_TYPE'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sql);
        $sql = preg_split("/;\s*[\r\n]+/", trim($sql_replace));

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute(trim($query))) {
                return Tools::displayError('Error : this query doesn\'t work ! ' . $query);
            }
        }

        return true;
    }

    /**
     * @see Module::install()
     */
    public function install()
    {
        require_once dirname(__FILE__) . '/classes/ntbr.php';

        $physic_path_modules = Apparatus::getRealPath(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules') . DIRECTORY_SEPARATOR;
        $backup_dir = $physic_path_modules . $this->name . DIRECTORY_SEPARATOR . NtbrCore::BACKUP_FOLDER . DIRECTORY_SEPARATOR;

        $install_on_tab = true;

        // Install on tab
        foreach ($this->tabs as $tab) {
            if (!$this->installOnTab($tab['tab_class'], $tab['tab_name'], $tab['parent_class'], $tab['parent_name'])) {
                $install_on_tab = false;
            }
        }

        if (!$install_on_tab) {
            $this->_errors[] = $this->l('The module cannot be install on its tabs.');

            return false;
        }

        // Make sure the database was removed
        $this->executeFile(dirname(__FILE__) . '/' . self::UNINSTALL_SQL_FILE);

        // Create new data base table
        $this->executeFile(dirname(__FILE__) . '/' . self::INSTALL_SQL_FILE);

        $s_save_config_file = dirname(__FILE__) . '/' . self::SAVE_CONFIG_FILE;

        $b_create_default_config = false;

        $shop_domain = Tools::getCurrentUrlProtocolPrefix() . Tools::getHttpHost();
        $shop_url = $shop_domain . __PS_BASE_URI__;

        // We initialize the configuration
        $rd_hour = mt_rand(2, 5); // Rand hour between 2 and 5
        $rd_min = mt_rand(1, 59); // Rand minutes between 1 and 59

        $glob_config_values = [
            'NTBR_ADMIN_DIR' => '',
            'NTBR_AUTOMATION_2NT' => 0,
            'NTBR_AUTOMATION_2NT_HOURS' => $rd_hour,
            'NTBR_AUTOMATION_2NT_IP' => 0,
            'NTBR_AUTOMATION_2NT_MINUTES' => $rd_min,
            'NTBR_BIG_WEBSITE' => 0,
            'NTBR_BIG_WEBSITE_HIDE' => 0,
            'NTBR_HASH' => '',
            'NTBR_HASH_TEMP' => '',
            'NTBR_LAST_SHOP_URL' => $shop_url,
            'NTBR_MULTI_CONFIG' => 0,
            'NTBR_ONGOING' => 0,
            'NTBR_ONGOING_ID_CONFIG' => 0,
            'NTBR_ONGOING_REFRESH' => 0,
            'NTBR_SCAN_ID_CONFIG' => 0,
            'NTBR_SEL' => '',
            'NTBR_SEL_TEMP' => '',
            'NTBR_GOOGLEDRIVE_CLIENT_ID' => '',
            'NTBR_GOOGLEDRIVE_CLIENT_SECRET' => '',
        ];

        // Put back config if it exists
        if (Apparatus::checkFileExists($s_save_config_file)) {
            $b_at_least_one_conf = false;

            if ($handle_save_config_file = fopen($s_save_config_file, 'a+')) {
                rewind($handle_save_config_file);

                $a_list_configs = json_decode(fgets($handle_save_config_file), true);

                fclose($handle_save_config_file);

                // We initialize the configuration
                if (isset($a_list_configs['glob'])) {
                    $a_glob_config = $a_list_configs['glob'];

                    if (isset($a_glob_config['NTBR_AUTOMATION_2NT_HOURS'])) {
                        $glob_config_values['NTBR_AUTOMATION_2NT_HOURS'] = $a_glob_config['NTBR_AUTOMATION_2NT_HOURS'];
                    }

                    if (isset($a_glob_config['NTBR_AUTOMATION_2NT_MINUTES'])) {
                        $glob_config_values['NTBR_AUTOMATION_2NT_MINUTES'] = $a_glob_config['NTBR_AUTOMATION_2NT_MINUTES'];
                    }

                    if (isset($a_glob_config['NTBR_AUTOMATION_2NT'])) {
                        $glob_config_values['NTBR_AUTOMATION_2NT'] = $a_glob_config['NTBR_AUTOMATION_2NT'];
                    }

                    if (isset($a_glob_config['NTBR_ONGOING'])) {
                        $glob_config_values['NTBR_ONGOING'] = $a_glob_config['NTBR_ONGOING'];
                    }

                    if (isset($a_glob_config['NTBR_ONGOING_REFRESH'])) {
                        $glob_config_values['NTBR_ONGOING_REFRESH'] = $a_glob_config['NTBR_ONGOING_REFRESH'];
                    }

                    if (isset($a_glob_config['NTBR_LAST_SHOP_URL'])) {
                        $glob_config_values['NTBR_LAST_SHOP_URL'] = $a_glob_config['NTBR_LAST_SHOP_URL'];
                    }

                    if (isset($a_glob_config['NTBR_ADMIN_DIR'])) {
                        $glob_config_values['NTBR_ADMIN_DIR'] = $a_glob_config['NTBR_ADMIN_DIR'];
                    }

                    if (isset($a_glob_config['NTBR_BIG_WEBSITE_HIDE'])) {
                        $glob_config_values['NTBR_BIG_WEBSITE_HIDE'] = $a_glob_config['NTBR_BIG_WEBSITE_HIDE'];
                    }

                    if (isset($a_glob_config['NTBR_BIG_WEBSITE'])) {
                        $glob_config_values['NTBR_BIG_WEBSITE'] = $a_glob_config['NTBR_BIG_WEBSITE'];
                    }

                    if (isset($a_glob_config['NTBR_HASH_TEMP'])) {
                        $glob_config_values['NTBR_HASH_TEMP'] = $a_glob_config['NTBR_HASH_TEMP'];
                    }

                    if (isset($a_glob_config['NTBR_AUTOMATION_2NT_IP'])) {
                        $glob_config_values['NTBR_AUTOMATION_2NT_IP'] = $a_glob_config['NTBR_AUTOMATION_2NT_IP'];
                    }

                    if (isset($a_glob_config['NTBR_SCAN_ID_CONFIG'])) {
                        $glob_config_values['NTBR_SCAN_ID_CONFIG'] = $a_glob_config['NTBR_SCAN_ID_CONFIG'];
                    }

                    if (isset($a_glob_config['NTBR_MULTI_CONFIG'])) {
                        $glob_config_values['NTBR_MULTI_CONFIG'] = $a_glob_config['NTBR_MULTI_CONFIG'];
                    }

                    if (isset($a_glob_config['NTBR_ONGOING_ID_CONFIG'])) {
                        $glob_config_values['NTBR_ONGOING_ID_CONFIG'] = $a_glob_config['NTBR_ONGOING_ID_CONFIG'];
                    }

                    if (isset($a_glob_config['NTBR_SEL_TEMP'])) {
                        $glob_config_values['NTBR_SEL_TEMP'] = $a_glob_config['NTBR_SEL_TEMP'];
                    }

                    if (isset($a_glob_config['NTBR_SEL'])) {
                        $glob_config_values['NTBR_SEL'] = $a_glob_config['NTBR_SEL'];
                    }

                    if (isset($a_glob_config['NTBR_HASH'])) {
                        $glob_config_values['NTBR_HASH'] = $a_glob_config['NTBR_HASH'];
                    }

                    if (isset($a_glob_config['NTBR_GOOGLEDRIVE_CLIENT_ID'])) {
                        $glob_config_values['NTBR_GOOGLEDRIVE_CLIENT_ID'] = $a_glob_config['NTBR_GOOGLEDRIVE_CLIENT_ID'];
                    }

                    if (isset($a_glob_config['NTBR_GOOGLEDRIVE_CLIENT_SECRET'])) {
                        $glob_config_values['NTBR_GOOGLEDRIVE_CLIENT_SECRET'] = $a_glob_config['NTBR_GOOGLEDRIVE_CLIENT_SECRET'];
                    }
                } elseif (isset($a_list_configs['gen'])) { // Compatibility with old version
                    $default_id_shop = (int) Configuration::get('PS_SHOP_DEFAULT');
                    $default_id_shop_group = Shop::getGroupFromShop($default_id_shop);

                    if (isset($a_list_configs['gen'][$default_id_shop_group . '_' . $default_id_shop])) {
                        $a_gen_config = $a_list_configs['gen'][$default_id_shop_group . '_' . $default_id_shop];

                        if (isset($a_gen_config['NTBR_AUTOMATION_2NT_HOURS'])) {
                            $glob_config_values['NTBR_AUTOMATION_2NT_HOURS'] = $a_gen_config['NTBR_AUTOMATION_2NT_HOURS'];
                        }

                        if (isset($a_gen_config['NTBR_AUTOMATION_2NT_MINUTES'])) {
                            $glob_config_values['NTBR_AUTOMATION_2NT_MINUTES'] = $a_gen_config['NTBR_AUTOMATION_2NT_MINUTES'];
                        }

                        if (isset($a_gen_config['NTBR_AUTOMATION_2NT'])) {
                            $glob_config_values['NTBR_AUTOMATION_2NT'] = $a_gen_config['NTBR_AUTOMATION_2NT'];
                        }

                        if (isset($a_gen_config['NTBR_ONGOING'])) {
                            $glob_config_values['NTBR_ONGOING'] = $a_gen_config['NTBR_ONGOING'];
                        }

                        if (isset($a_gen_config['NTBR_ONGOING_REFRESH'])) {
                            $glob_config_values['NTBR_ONGOING_REFRESH'] = $a_gen_config['NTBR_ONGOING_REFRESH'];
                        }

                        if (isset($a_gen_config['NTBR_LAST_SHOP_URL'])) {
                            $glob_config_values['NTBR_LAST_SHOP_URL'] = $a_gen_config['NTBR_LAST_SHOP_URL'];
                        }

                        if (isset($a_gen_config['NTBR_ADMIN_DIR'])) {
                            $glob_config_values['NTBR_ADMIN_DIR'] = $a_gen_config['NTBR_ADMIN_DIR'];
                        }

                        if (isset($a_gen_config['NTBR_BIG_WEBSITE_HIDE'])) {
                            $glob_config_values['NTBR_BIG_WEBSITE_HIDE'] = $a_gen_config['NTBR_BIG_WEBSITE_HIDE'];
                        }

                        if (isset($a_gen_config['NTBR_BIG_WEBSITE'])) {
                            $glob_config_values['NTBR_BIG_WEBSITE'] = $a_gen_config['NTBR_BIG_WEBSITE'];
                        }

                        if (isset($a_gen_config['NTBR_HASH_TEMP'])) {
                            $glob_config_values['NTBR_HASH_TEMP'] = $a_gen_config['NTBR_HASH_TEMP'];
                        }

                        if (isset($a_gen_config['NTBR_AUTOMATION_2NT_IP'])) {
                            $glob_config_values['NTBR_AUTOMATION_2NT_IP'] = $a_gen_config['NTBR_AUTOMATION_2NT_IP'];
                        }

                        if (isset($a_gen_config['NTBR_SCAN_ID_CONFIG'])) {
                            $glob_config_values['NTBR_SCAN_ID_CONFIG'] = $a_gen_config['NTBR_SCAN_ID_CONFIG'];
                        }

                        if (isset($a_gen_config['NTBR_MULTI_CONFIG'])) {
                            $glob_config_values['NTBR_MULTI_CONFIG'] = $a_gen_config['NTBR_MULTI_CONFIG'];
                        }

                        if (isset($a_gen_config['NTBR_ONGOING_ID_CONFIG'])) {
                            $glob_config_values['NTBR_ONGOING_ID_CONFIG'] = $a_gen_config['NTBR_ONGOING_ID_CONFIG'];
                        }

                        if (isset($a_gen_config['NTBR_SEL_TEMP'])) {
                            $glob_config_values['NTBR_SEL_TEMP'] = $a_gen_config['NTBR_SEL_TEMP'];
                        }

                        if (isset($a_gen_config['NTBR_SEL'])) {
                            $glob_config_values['NTBR_SEL'] = $a_gen_config['NTBR_SEL'];
                        }

                        if (isset($a_gen_config['NTBR_HASH'])) {
                            $glob_config_values['NTBR_HASH'] = $a_gen_config['NTBR_HASH'];
                        }

                        if (isset($a_gen_config['NTBR_GOOGLEDRIVE_CLIENT_ID'])) {
                            $glob_config_values['NTBR_GOOGLEDRIVE_CLIENT_ID'] = $a_gen_config['NTBR_GOOGLEDRIVE_CLIENT_ID'];
                        }

                        if (isset($a_gen_config['NTBR_GOOGLEDRIVE_CLIENT_SECRET'])) {
                            $glob_config_values['NTBR_GOOGLEDRIVE_CLIENT_SECRET'] = $a_gen_config['NTBR_GOOGLEDRIVE_CLIENT_SECRET'];
                        }
                    }
                }

                if (isset($a_list_configs['ntbr'])) {
                    foreach ($a_list_configs['ntbr'] as $a_config) {
                        if (!isset($a_config['id_ntbr_config'])
                            || !isset($a_config['is_default'])
                            || !isset($a_config['name'])
                            || !isset($a_config['type_backup'])
                        ) {
                            continue;
                        }

                        // Insert config
                        $o_config = new ConfigNtbr($a_config['id_ntbr_config']);

                        $o_config->is_default = $a_config['is_default'];
                        $o_config->name = $a_config['name'];
                        $o_config->type_backup = $a_config['type_backup'];
                        $o_config->disable = (isset($a_config['disable'])) ? $a_config['disable'] : 0;
                        $o_config->nb_backup = (isset($a_config['nb_backup'])) ? $a_config['nb_backup'] : 3;
                        $o_config->send_email = (isset($a_config['send_email'])) ? $a_config['send_email'] : 0;
                        $o_config->receive_email_version = (isset($a_config['receive_email_version'])) ? $a_config['receive_email_version'] : 0;
                        $o_config->email_only_error = (isset($a_config['email_only_error'])) ? $a_config['email_only_error'] : 0;
                        $o_config->mail_backup = (isset($a_config['mail_backup'])) ? $a_config['mail_backup'] : Configuration::get('PS_SHOP_EMAIL');
                        $o_config->mail_version = (isset($a_config['mail_version'])) ? $a_config['mail_version'] : Configuration::get('PS_SHOP_EMAIL');
                        $o_config->send_restore = (isset($a_config['send_restore'])) ? $a_config['send_restore'] : 0;
                        $o_config->activate_log = (isset($a_config['activate_log'])) ? $a_config['activate_log'] : 0;
                        $o_config->part_size = (isset($a_config['part_size'])) ? $a_config['part_size'] : 2000;
                        $o_config->max_file_to_backup = (isset($a_config['max_file_to_backup'])) ? $a_config['max_file_to_backup'] : 0;
                        $o_config->dump_max_values = (isset($a_config['dump_max_values'])) ? $a_config['dump_max_values'] : NtbrCore::DUMP_MAX_VALUES;
                        $o_config->dump_lines_limit = (isset($a_config['dump_lines_limit'])) ? $a_config['dump_lines_limit'] : NtbrCore::DUMP_LINES_LIMIT;
                        $o_config->disable_refresh = (isset($a_config['disable_refresh'])) ? $a_config['disable_refresh'] : 0;
                        $o_config->time_between_refresh = (isset($a_config['time_between_refresh'])) ? $a_config['time_between_refresh'] : NtbrCore::MAX_TIME_BEFORE_REFRESH;
                        $o_config->time_pause_between_refresh = (isset($a_config['time_pause_between_refresh'])) ? $a_config['time_pause_between_refresh'] : 0;
                        $o_config->time_between_progress_refresh = (isset($a_config['time_between_progress_refresh'])) ? $a_config['time_between_progress_refresh'] : NtbrCore::MAX_TIME_BEFORE_PROGRESS_REFRESH;
                        $o_config->increase_server_timeout = (isset($a_config['increase_server_timeout'])) ? $a_config['increase_server_timeout'] : 0;
                        $o_config->increase_server_memory = (isset($a_config['increase_server_memory'])) ? $a_config['increase_server_memory'] : 0;
                        $o_config->js_download = (isset($a_config['js_download'])) ? $a_config['js_download'] : 0;
                        $o_config->disable_curl_file_size = (isset($a_config['disable_curl_file_size'])) ? $a_config['disable_curl_file_size'] : 0;
                        $o_config->scan_files = (isset($a_config['scan_files'])) ? $a_config['scan_files'] : 0;
                        $o_config->server_timeout_value = (isset($a_config['server_timeout_value'])) ? $a_config['server_timeout_value'] : NtbrCore::SET_TIME_LIMIT;
                        $o_config->server_memory_value = (isset($a_config['server_memory_value'])) ? $a_config['server_memory_value'] : NtbrCore::SET_MEMORY_LIMIT;
                        $o_config->dump_low_interest_tables = (isset($a_config['dump_low_interest_tables'])) ? $a_config['dump_low_interest_tables'] : 0;
                        $o_config->maintenance = (isset($a_config['maintenance'])) ? $a_config['maintenance'] : 0;
                        $o_config->time_between_backups = (isset($a_config['time_between_backups'])) ? $a_config['time_between_backups'] : NtbrCore::MIN_TIME_NEW_BACKUP;
                        $o_config->activate_xsendfile = (isset($a_config['activate_xsendfile'])) ? $a_config['activate_xsendfile'] : 0;
                        $o_config->ignore_product_image = (isset($a_config['ignore_product_image'])) ? $a_config['ignore_product_image'] : NtbrCore::PRODUCT_IMG_AND_FILE;
                        $o_config->only_origin_img = (isset($a_config['only_origin_img'])) ? $a_config['only_origin_img'] : 0;
                        $o_config->ignore_compression = (isset($a_config['ignore_compression'])) ? $a_config['ignore_compression'] : 0;
                        $o_config->delete_local_backup = (isset($a_config['delete_local_backup'])) ? $a_config['delete_local_backup'] : 0;
                        $o_config->create_on_distant = (isset($a_config['create_on_distant'])) ? $a_config['create_on_distant'] : 0;
                        $o_config->crypt_backup = (isset($a_config['crypt_backup'])) ? $a_config['crypt_backup'] : 0;
                        $o_config->sodium_key = (isset($a_config['sodium_key'])) ? $a_config['sodium_key'] : '';
                        $o_config->backup_dir = (isset($a_config['backup_dir'])) ? $a_config['backup_dir'] : $backup_dir;
                        $o_config->ignore_directories = (isset($a_config['ignore_directories'])) ? $a_config['ignore_directories'] : 'upload';
                        $o_config->ignore_file_types = (isset($a_config['ignore_file_types'])) ? $a_config['ignore_file_types'] : '';
                        $o_config->ignore_tables = (isset($a_config['ignore_tables'])) ? $a_config['ignore_tables'] : '';
                        $o_config->not_recreate_tables = (isset($a_config['not_recreate_tables'])) ? $a_config['not_recreate_tables'] : '';

                        if (!$o_config->save()) {
                            self::lg(
                                sprintf(
                                    $this->l('The config %1$s ID %2$d could not be saved'),
                                    $a_config['name'],
                                    $a_config['is_default']
                                )
                            );

                            continue;
                        } elseif (isset($a_config['disable']) && $a_config['disable']) {
                            // Adding config force the disable attr to 0, so we need to update if
                            $o_config->disable = $a_config['disable'];
                            $o_config->save();
                        }

                        $b_at_least_one_conf = true;

                        if (isset($a_config['aws_accounts'])) {
                            $a_config['aws_accounts'] = json_decode($a_config['aws_accounts'], true);

                            foreach ($a_config['aws_accounts'] as $a_aws_account) {
                                $o_aws = new AwsNtbr();

                                $o_aws->id_ntbr_config = $o_config->id;
                                $o_aws->active = $a_aws_account['active'];
                                $o_aws->name = $a_aws_account['name'];
                                $o_aws->config_nb_backup = $a_aws_account['config_nb_backup'];
                                $o_aws->access_key_id = $a_aws_account['access_key_id'];
                                $o_aws->secret_access_key = $a_aws_account['secret_access_key'];
                                $o_aws->region = $a_aws_account['region'];
                                $o_aws->bucket = $a_aws_account['bucket'];
                                $o_aws->host = $a_aws_account['host'];
                                $o_aws->storage_class = (isset($a_aws_account['storage_class']) ? $a_aws_account['storage_class'] : AwsLib::STORAGE_CLASS_STANDARD);
                                $o_aws->directory_key = $a_aws_account['directory_key'];
                                $o_aws->directory_path = $a_aws_account['directory_path'];
                                $o_aws->type_s3 = $a_aws_account['type_s3'];
                                $o_aws->accept_unvalid_ssl = $a_aws_account['accept_unvalid_ssl'];

                                $o_aws->save();
                            }
                        }

                        if (isset($a_config['dropbox_accounts'])) {
                            $a_config['dropbox_accounts'] = json_decode($a_config['dropbox_accounts'], true);

                            foreach ($a_config['dropbox_accounts'] as $a_dropbox_account) {
                                $o_dropbox = new DropboxNtbr();

                                $o_dropbox->id_ntbr_config = $o_config->id;
                                $o_dropbox->active = $a_dropbox_account['active'];
                                $o_dropbox->business = $a_dropbox_account['active'];
                                $o_dropbox->name = $a_dropbox_account['name'];
                                $o_dropbox->config_nb_backup = $a_dropbox_account['config_nb_backup'];
                                $o_dropbox->directory = $a_dropbox_account['directory'];
                                $o_dropbox->token = $a_dropbox_account['token'];

                                $o_dropbox->save();
                            }
                        }

                        if (isset($a_config['yandex_accounts'])) {
                            $a_config['yandex_accounts'] = json_decode($a_config['yandex_accounts'], true);

                            foreach ($a_config['yandex_accounts'] as $a_yandex_account) {
                                $o_yandex = new YandexNtbr();

                                $o_yandex->id_ntbr_config = $o_config->id;
                                $o_yandex->active = $a_yandex_account['active'];
                                $o_yandex->name = $a_yandex_account['name'];
                                $o_yandex->config_nb_backup = $a_yandex_account['config_nb_backup'];
                                $o_yandex->directory = $a_yandex_account['directory'];
                                $o_yandex->token = $a_yandex_account['token'];

                                $o_yandex->save();
                            }
                        }

                        if (isset($a_config['googledrive_accounts'])) {
                            $a_config['googledrive_accounts'] = json_decode($a_config['googledrive_accounts'], true);

                            foreach ($a_config['googledrive_accounts'] as $a_googledrive_account) {
                                $o_googledrive = new GoogledriveNtbr();

                                $o_googledrive->id_ntbr_config = $o_config->id;
                                $o_googledrive->active = $a_googledrive_account['active'];
                                $o_googledrive->name = $a_googledrive_account['name'];
                                $o_googledrive->config_nb_backup = $a_googledrive_account['config_nb_backup'];
                                $o_googledrive->directory_key = $a_googledrive_account['directory_key'];
                                $o_googledrive->directory_path = $a_googledrive_account['directory_path'];
                                $o_googledrive->token = $a_googledrive_account['token'];

                                $o_googledrive->save();
                            }
                        }

                        if (isset($a_config['googlecloud_accounts'])) {
                            $a_config['googlecloud_accounts'] = json_decode($a_config['googlecloud_accounts'], true);

                            foreach ($a_config['googlecloud_accounts'] as $a_googlecloud_account) {
                                $o_googlecloud = new GooglecloudNtbr();

                                $o_googlecloud->id_ntbr_config = $o_config->id;
                                $o_googlecloud->active = $a_googlecloud_account['active'];
                                $o_googlecloud->name = $a_googlecloud_account['name'];
                                $o_googlecloud->config_nb_backup = $a_googlecloud_account['config_nb_backup'];
                                $o_googlecloud->bucket = $a_googlecloud_account['bucket'];
                                $o_googlecloud->directory = $a_googlecloud_account['directory'];
                                $o_googlecloud->token = $a_googlecloud_account['token'];

                                $o_googlecloud->save();
                            }
                        }

                        if (isset($a_config['pcloud_accounts'])) {
                            $a_config['pcloud_accounts'] = json_decode($a_config['pcloud_accounts'], true);

                            foreach ($a_config['pcloud_accounts'] as $a_pcloud_account) {
                                $o_pcloud = new PcloudNtbr();

                                $o_pcloud->id_ntbr_config = $o_config->id;
                                $o_pcloud->active = $a_pcloud_account['active'];
                                $o_pcloud->name = $a_pcloud_account['name'];
                                $o_pcloud->config_nb_backup = $a_pcloud_account['config_nb_backup'];
                                $o_pcloud->location_id = $a_pcloud_account['location_id'];
                                $o_pcloud->directory_id = $a_pcloud_account['directory_id'];
                                $o_pcloud->directory_path = $a_pcloud_account['directory_path'];
                                $o_pcloud->token = $a_pcloud_account['token'];

                                $o_pcloud->save();
                            }
                        }

                        if (isset($a_config['box_accounts'])) {
                            $a_config['box_accounts'] = json_decode($a_config['box_accounts'], true);

                            foreach ($a_config['box_accounts'] as $a_box_account) {
                                $o_box = new BoxNtbr();

                                $o_box->id_ntbr_config = $o_config->id;
                                $o_box->active = $a_box_account['active'];
                                $o_box->name = $a_box_account['name'];
                                $o_box->config_nb_backup = $a_box_account['config_nb_backup'];
                                $o_box->directory_key = $a_box_account['directory_key'];
                                $o_box->directory_path = $a_box_account['directory_path'];
                                $o_box->token = $a_box_account['token'];

                                $o_box->save();
                            }
                        }

                        if (isset($a_config['onedrive_accounts'])) {
                            $a_config['onedrive_accounts'] = json_decode($a_config['onedrive_accounts'], true);

                            foreach ($a_config['onedrive_accounts'] as $a_onedrive_account) {
                                $o_onedrive = new OnedriveNtbr();

                                $o_onedrive->id_ntbr_config = $o_config->id;
                                $o_onedrive->active = $a_onedrive_account['active'];
                                $o_onedrive->name = $a_onedrive_account['name'];
                                $o_onedrive->config_nb_backup = $a_onedrive_account['config_nb_backup'];
                                $o_onedrive->directory_key = $a_onedrive_account['directory_key'];
                                $o_onedrive->directory_path = $a_onedrive_account['directory_path'];
                                $o_onedrive->token = $a_onedrive_account['token'];
                                $o_onedrive->business = $a_onedrive_account['business'];
                                $o_onedrive->my_site = $a_onedrive_account['my_site'];

                                $o_onedrive->save();
                            }
                        }

                        if (isset($a_config['owncloud_accounts'])) {
                            $a_config['owncloud_accounts'] = json_decode($a_config['owncloud_accounts'], true);

                            foreach ($a_config['owncloud_accounts'] as $a_owncloud_account) {
                                $o_owncloud = new OwncloudNtbr();

                                $o_owncloud->id_ntbr_config = $o_config->id;
                                $o_owncloud->active = $a_owncloud_account['active'];
                                $o_owncloud->name = $a_owncloud_account['name'];
                                $o_owncloud->config_nb_backup = $a_owncloud_account['config_nb_backup'];
                                $o_owncloud->login = $a_owncloud_account['login'];
                                $o_owncloud->password = $a_owncloud_account['password'];
                                $o_owncloud->server = $a_owncloud_account['server'];
                                $o_owncloud->directory = $a_owncloud_account['directory'];

                                $o_owncloud->save();
                            }
                        }

                        if (isset($a_config['shadow_drive_accounts'])) {
                            $a_config['shadow_drive_accounts'] = json_decode($a_config['shadow_drive_accounts'], true);

                            foreach ($a_config['shadow_drive_accounts'] as $a_shadow_drive_account) {
                                $o_shadow_drive = new ShadowDriveNtbr();

                                $o_shadow_drive->id_ntbr_config = $o_config->id;
                                $o_shadow_drive->active = $a_shadow_drive_account['active'];
                                $o_shadow_drive->name = $a_shadow_drive_account['name'];
                                $o_shadow_drive->config_nb_backup = $a_shadow_drive_account['config_nb_backup'];
                                $o_shadow_drive->login = $a_shadow_drive_account['login'];
                                $o_shadow_drive->password = $a_shadow_drive_account['password'];
                                $o_shadow_drive->server = $a_shadow_drive_account['server'];
                                $o_shadow_drive->directory = $a_shadow_drive_account['directory'];

                                $o_shadow_drive->save();
                            }
                        }

                        if (isset($a_config['sugarsync_accounts'])) {
                            $a_config['sugarsync_accounts'] = json_decode($a_config['sugarsync_accounts'], true);

                            foreach ($a_config['sugarsync_accounts'] as $a_sugarsync_account) {
                                $o_sugarsync = new SugarsyncNtbr();

                                $o_sugarsync->id_ntbr_config = $o_config->id;
                                $o_sugarsync->active = $a_sugarsync_account['active'];
                                $o_sugarsync->name = $a_sugarsync_account['name'];
                                $o_sugarsync->config_nb_backup = $a_sugarsync_account['config_nb_backup'];
                                $o_sugarsync->directory_key = $a_sugarsync_account['directory_key'];
                                $o_sugarsync->directory_path = $a_sugarsync_account['directory_path'];
                                $o_sugarsync->token = $a_sugarsync_account['token'];
                                $o_sugarsync->login = $a_sugarsync_account['login'];

                                $o_sugarsync->save();
                            }
                        }

                        if (isset($a_config['webdav_accounts'])) {
                            $a_config['webdav_accounts'] = json_decode($a_config['webdav_accounts'], true);

                            foreach ($a_config['webdav_accounts'] as $a_webdav_account) {
                                $o_webdav = new WebdavNtbr();

                                $o_webdav->id_ntbr_config = $o_config->id;
                                $o_webdav->active = $a_webdav_account['active'];
                                $o_webdav->name = $a_webdav_account['name'];
                                $o_webdav->config_nb_backup = $a_webdav_account['config_nb_backup'];
                                $o_webdav->login = $a_webdav_account['login'];
                                $o_webdav->password = $a_webdav_account['password'];
                                $o_webdav->server = $a_webdav_account['server'];
                                $o_webdav->directory = $a_webdav_account['directory'];

                                $o_webdav->save();
                            }
                        }

                        if (isset($a_config['ftp_accounts'])) {
                            $a_config['ftp_accounts'] = json_decode($a_config['ftp_accounts'], true);

                            foreach ($a_config['ftp_accounts'] as $a_ftp_account) {
                                $o_ftp = new FtpNtbr();

                                $o_ftp->id_ntbr_config = $o_config->id;
                                $o_ftp->active = $a_ftp_account['active'];
                                $o_ftp->name = $a_ftp_account['name'];
                                $o_ftp->config_nb_backup = $a_ftp_account['config_nb_backup'];
                                $o_ftp->sftp = $a_ftp_account['sftp'];
                                $o_ftp->ssl = $a_ftp_account['ssl'];
                                $o_ftp->passive_mode = $a_ftp_account['passive_mode'];
                                $o_ftp->server = $a_ftp_account['server'];
                                $o_ftp->login = $a_ftp_account['login'];
                                $o_ftp->password = $a_ftp_account['password'];
                                $o_ftp->port = $a_ftp_account['port'];
                                $o_ftp->directory = $a_ftp_account['directory'];

                                $o_ftp->save();
                            }
                        }

                        if (isset($a_config['backups'])) {
                            $a_config['backups'] = json_decode($a_config['backups'], true);

                            foreach ($a_config['backups'] as $a_backups) {
                                $o_backups = new Backups();

                                $o_backups->id_ntbr_config = $o_config->id;
                                $o_backups->backup_name = $a_backups['backup_name'];
                                $o_backups->comment = $a_backups['comment'];
                                $o_backups->safe = $a_backups['safe'];

                                $o_backups->save();
                            }
                        }
                    }
                }

                if (!$b_at_least_one_conf) {
                    $b_create_default_config = true;
                }
            }

            unlink($s_save_config_file);
        } else {
            $list_config = ConfigNtbr::getListConfigs();

            if (!count($list_config)) {
                $b_create_default_config = true;
            }
        }

        if (!GlobConfNtbr::set('NTBR_AUTOMATION_2NT_HOURS', $glob_config_values['NTBR_AUTOMATION_2NT_HOURS'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Automation config cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_AUTOMATION_2NT_MINUTES', $glob_config_values['NTBR_AUTOMATION_2NT_MINUTES'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Automation minute config cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_AUTOMATION_2NT', $glob_config_values['NTBR_AUTOMATION_2NT'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Automation config cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_ONGOING', $glob_config_values['NTBR_ONGOING'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Check ongoing backup config cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_ONGOING_REFRESH', $glob_config_values['NTBR_ONGOING_REFRESH'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Check ongoing refresh config cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_LAST_SHOP_URL', Apparatus::encrypt($glob_config_values['NTBR_LAST_SHOP_URL']))) {
            $this->_errors[] = $this->l('The configuration cannot be created: The last shop url cannot be saved.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_ADMIN_DIR', $glob_config_values['NTBR_ADMIN_DIR'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Backup directory config cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_BIG_WEBSITE_HIDE', $glob_config_values['NTBR_BIG_WEBSITE_HIDE'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Display big shop warning config cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_BIG_WEBSITE', $glob_config_values['NTBR_BIG_WEBSITE'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Big shop info cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_HASH_TEMP', $glob_config_values['NTBR_HASH_TEMP'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Temporary hash cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_AUTOMATION_2NT_IP', $glob_config_values['NTBR_AUTOMATION_2NT_IP'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Automation IP config cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_SCAN_ID_CONFIG', $glob_config_values['NTBR_SCAN_ID_CONFIG'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Scan ID config cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_MULTI_CONFIG', $glob_config_values['NTBR_MULTI_CONFIG'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Multi config cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_ONGOING_ID_CONFIG', $glob_config_values['NTBR_ONGOING_ID_CONFIG'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Ongoing ID cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_SEL_TEMP', $glob_config_values['NTBR_SEL_TEMP'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Temporary SEL cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_SEL', $glob_config_values['NTBR_SEL'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: SEL cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_HASH', $glob_config_values['NTBR_HASH'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Hash cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_GOOGLEDRIVE_CLIENT_ID', $glob_config_values['NTBR_GOOGLEDRIVE_CLIENT_ID'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Google Drive client ID cannot be created.');

            return false;
        }

        if (!GlobConfNtbr::set('NTBR_GOOGLEDRIVE_CLIENT_SECRET', $glob_config_values['NTBR_GOOGLEDRIVE_CLIENT_SECRET'])) {
            $this->_errors[] = $this->l('The configuration cannot be created: Google Drive client secret cannot be created.');

            return false;
        }

        $module_id = Configuration::get('NTBR_ID');

        // Check module ID exists
        if (!$module_id || $module_id == '') {
            // Unique value for all shop that should not be remove or modify
            if (!Configuration::updateGlobalValue('NTBR_ID', $this->createModuleId())) {
                $this->_errors[] = $this->l('The configuration cannot be created: Module ID cannot be created.');

                return false;
            }
        }

        if ($b_create_default_config) {
            // Insert config default data
            $config = new ConfigNtbr();

            $max_execution_time = ini_get('max_execution_time');

            if ($max_execution_time < NtbrCore::MAX_TIME_BEFORE_REFRESH) {
                $time_between_refresh = NtbrCore::MAX_TIME_BEFORE_REFRESH;
            } elseif ($max_execution_time >= NtbrCore::PROBABLE_TIMEOUT_LIMIT) {
                $time_between_refresh = NtbrCore::PROBABLE_TIMEOUT_LIMIT - 5;
            } else {
                $time_between_refresh = $max_execution_time - 5;
            }

            $config->is_default = 1;
            $config->disable = 0;
            $config->name = $this->l('Complete');
            $config->type_backup = 'complete';
            $config->nb_backup = 3;
            $config->send_email = 0;
            $config->receive_email_version = 0;
            $config->email_only_error = 0;
            $config->mail_backup = Configuration::get('PS_SHOP_EMAIL');
            $config->mail_version = Configuration::get('PS_SHOP_EMAIL');
            $config->send_restore = 0;
            $config->activate_log = 0;
            $config->part_size = 2000;
            $config->max_file_to_backup = 0;
            $config->dump_max_values = NtbrCore::DUMP_MAX_VALUES;
            $config->dump_lines_limit = NtbrCore::DUMP_LINES_LIMIT;
            $config->disable_refresh = 0;
            $config->time_between_refresh = $time_between_refresh;
            $config->time_pause_between_refresh = 0;
            $config->time_between_progress_refresh = NtbrCore::MAX_TIME_BEFORE_PROGRESS_REFRESH;
            $config->increase_server_timeout = 0;
            $config->increase_server_memory = 0;
            $config->js_download = 0;
            $config->disable_curl_file_size = 0;
            $config->scan_files = 0;
            $config->server_timeout_value = NtbrCore::SET_TIME_LIMIT;
            $config->server_memory_value = NtbrCore::SET_MEMORY_LIMIT;
            $config->dump_low_interest_tables = 0;
            $config->maintenance = 0;
            $config->time_between_backups = NtbrCore::MIN_TIME_NEW_BACKUP;
            $config->activate_xsendfile = 0;
            $config->ignore_product_image = NtbrCore::PRODUCT_IMG_AND_FILE;
            $config->only_origin_img = 0;
            $config->ignore_compression = 0;
            $config->delete_local_backup = 0;
            $config->create_on_distant = 0;
            $config->crypt_backup = 0;
            $config->sodium_key = '';
            $config->backup_dir = $backup_dir;
            $config->ignore_directories = 'upload';
            $config->ignore_file_types = '';
            $config->ignore_tables = '';
            $config->not_recreate_tables = '';

            if (!$config->save()) {
                $this->_errors[] = $this->l('The configuration cannot be created: The default config cannot be created.');

                return false;
            }
        }

        // Create file with all the varibles need for crons
        $this->deleteCronFiles(); // Delete them first if they already exists
        $this->writeCronFiles(true);

        if (parent::install()) {
            $this->setOperation(NtbrCore::OP_INSTALL);

            return true;
        }

        return false;
    }

    /**
     * @see Module::uninstall()
     */
    public function uninstall()
    {
        require_once dirname(__FILE__) . '/classes/ntbr.php';

        if (self::tableExists('ntbr_config')) {
            $a_list_configs = [];

            // Save the current configuration
            $a_list_configs['ntbr'] = ConfigNtbr::getListConfigs();

            foreach ($a_list_configs['ntbr'] as &$a_config) {
                if (self::tableExists('ntbr_aws')) {
                    $a_config['aws_accounts'] = json_encode(AwsNtbr::getListAwsAccounts($a_config['id_ntbr_config']));
                }

                if (self::tableExists('ntbr_dropbox')) {
                    $a_config['dropbox_accounts'] = json_encode(
                        DropboxNtbr::getListDropboxAccounts($a_config['id_ntbr_config'])
                    );
                }

                if (self::tableExists('ntbr_yandex')) {
                    $a_config['yandex_accounts'] = json_encode(
                        YandexNtbr::getListYandexAccounts($a_config['id_ntbr_config'])
                    );
                }

                if (self::tableExists('ntbr_googledrive')) {
                    $a_config['googledrive_accounts'] = json_encode(
                        GoogledriveNtbr::getListGoogledriveAccounts($a_config['id_ntbr_config'])
                    );
                }

                if (self::tableExists('ntbr_googlecloud')) {
                    $a_config['googlecloud_accounts'] = json_encode(
                        GooglecloudNtbr::getListGooglecloudAccounts($a_config['id_ntbr_config'])
                    );
                }

                if (self::tableExists('ntbr_pcloud')) {
                    $a_config['pcloud_accounts'] = json_encode(
                        PcloudNtbr::getListPcloudAccounts($a_config['id_ntbr_config'])
                    );
                }

                if (self::tableExists('ntbr_box')) {
                    $a_config['box_accounts'] = json_encode(
                        BoxNtbr::getListBoxAccounts($a_config['id_ntbr_config'])
                    );
                }

                if (self::tableExists('ntbr_onedrive')) {
                    $a_config['onedrive_accounts'] = json_encode(
                        OnedriveNtbr::getListOnedriveAccounts($a_config['id_ntbr_config'])
                    );
                }

                if (self::tableExists('ntbr_owncloud')) {
                    $a_config['owncloud_accounts'] = json_encode(
                        OwncloudNtbr::getListOwncloudAccounts($a_config['id_ntbr_config'])
                    );
                }

                if (self::tableExists('ntbr_shadow_drive')) {
                    $a_config['shadow_drive_accounts'] = json_encode(
                        ShadowDriveNtbr::getListShadowDriveAccounts($a_config['id_ntbr_config'])
                    );
                }

                if (self::tableExists('ntbr_sugarsync')) {
                    $a_config['sugarsync_accounts'] = json_encode(
                        SugarsyncNtbr::getListSugarsyncAccounts($a_config['id_ntbr_config'])
                    );
                }

                if (self::tableExists('ntbr_webdav')) {
                    $a_config['webdav_accounts'] = json_encode(
                        WebdavNtbr::getListWebdavAccounts($a_config['id_ntbr_config'])
                    );
                }

                if (self::tableExists('ntbr_ftp')) {
                    $a_config['ftp_accounts'] = json_encode(FtpNtbr::getListFtpAccounts($a_config['id_ntbr_config']));
                }

                if (self::tableExists('ntbr_backups')) {
                    $a_config['backups'] = json_encode(Backups::getListBackupsInfosByConfig($a_config['id_ntbr_config']));
                }
            }

            if (self::tableExists('ntbr_glob_conf')) {
                // We initialize the configuration
                $a_list_configs['glob'] = [];

                $a_list_configs['glob'] = [
                    'NTBR_AUTOMATION_2NT_HOURS' => GlobConfNtbr::get('NTBR_AUTOMATION_2NT_HOURS'),
                    'NTBR_AUTOMATION_2NT_MINUTES' => GlobConfNtbr::get('NTBR_AUTOMATION_2NT_MINUTES'),
                    'NTBR_AUTOMATION_2NT' => GlobConfNtbr::get('NTBR_AUTOMATION_2NT'),
                    'NTBR_ONGOING' => GlobConfNtbr::get('NTBR_ONGOING'),
                    'NTBR_ONGOING_REFRESH' => GlobConfNtbr::get('NTBR_ONGOING_REFRESH'),
                    'NTBR_LAST_SHOP_URL' => Apparatus::decrypt(GlobConfNtbr::get('NTBR_LAST_SHOP_URL')),
                    'NTBR_ADMIN_DIR' => GlobConfNtbr::get('NTBR_ADMIN_DIR'),
                    'NTBR_BIG_WEBSITE_HIDE' => GlobConfNtbr::get('NTBR_BIG_WEBSITE_HIDE'),
                    'NTBR_BIG_WEBSITE' => GlobConfNtbr::get('NTBR_BIG_WEBSITE'),
                    'NTBR_HASH_TEMP' => GlobConfNtbr::get('NTBR_HASH_TEMP'),
                    'NTBR_AUTOMATION_2NT_IP' => GlobConfNtbr::get('NTBR_AUTOMATION_2NT_IP'),
                    'NTBR_SCAN_ID_CONFIG' => GlobConfNtbr::get('NTBR_SCAN_ID_CONFIG'),
                    'NTBR_MULTI_CONFIG' => GlobConfNtbr::get('NTBR_MULTI_CONFIG'),
                    'NTBR_ONGOING_ID_CONFIG' => GlobConfNtbr::get('NTBR_ONGOING_ID_CONFIG'),
                    'NTBR_SEL_TEMP' => GlobConfNtbr::get('NTBR_SEL_TEMP'),
                    'NTBR_SEL' => GlobConfNtbr::get('NTBR_SEL'),
                    'NTBR_HASH' => GlobConfNtbr::get('NTBR_HASH'),
                    'NTBR_GOOGLEDRIVE_CLIENT_ID' => GlobConfNtbr::get('NTBR_GOOGLEDRIVE_CLIENT_ID'),
                    'NTBR_GOOGLEDRIVE_CLIENT_SECRET' => GlobConfNtbr::get('NTBR_GOOGLEDRIVE_CLIENT_SECRET'),
                ];
            }

            if ($handle_save_config_file = fopen(dirname(__FILE__) . '/' . self::SAVE_CONFIG_FILE, 'w+')) {
                fwrite($handle_save_config_file, json_encode($a_list_configs));
                fclose($handle_save_config_file);
            }

            $this->setOperation(NtbrCore::OP_UNINSTALL);
        }

        // Delete Back-office tab
        foreach ($this->tabs as $tab) {
            $this->uninstallTab($tab['tab_class']);
        }

        // Delete the database table
        $this->executeFile(dirname(__FILE__) . '/' . self::UNINSTALL_SQL_FILE);

        // Delete the global configuration
        if (self::tableExists('ntbr_glob_conf')) {
            GlobConfNtbr::deleteByName('NTBR_AUTOMATION_2NT_HOURS');
            GlobConfNtbr::deleteByName('NTBR_AUTOMATION_2NT_MINUTES');
            GlobConfNtbr::deleteByName('NTBR_AUTOMATION_2NT');
            GlobConfNtbr::deleteByName('NTBR_ONGOING');
            GlobConfNtbr::deleteByName('NTBR_ONGOING_REFRESH');
            GlobConfNtbr::deleteByName('NTBR_LAST_SHOP_URL');
            GlobConfNtbr::deleteByName('NTBR_ADMIN_DIR');
            GlobConfNtbr::deleteByName('NTBR_BIG_WEBSITE_HIDE');
            GlobConfNtbr::deleteByName('NTBR_BIG_WEBSITE');
            GlobConfNtbr::deleteByName('NTBR_HASH_TEMP');
            GlobConfNtbr::deleteByName('NTBR_AUTOMATION_2NT_IP');
            GlobConfNtbr::deleteByName('NTBR_SCAN_ID_CONFIG');
            GlobConfNtbr::deleteByName('NTBR_MULTI_CONFIG');
            GlobConfNtbr::deleteByName('NTBR_ONGOING_ID_CONFIG');
            GlobConfNtbr::deleteByName('NTBR_SEL_TEMP');
            GlobConfNtbr::deleteByName('NTBR_SEL');
            GlobConfNtbr::deleteByName('NTBR_HASH');
            GlobConfNtbr::deleteByName('NTBR_GOOGLEDRIVE_CLIENT_ID');
            GlobConfNtbr::deleteByName('NTBR_GOOGLEDRIVE_CLIENT_SECRET');
        }

        $this->deleteCronFiles();

        return parent::uninstall();
    }

    public function uninstallTab($tab_class)
    {
        $img_tab_path = _PS_ROOT_DIR_ . '/img/t/';
        $module_path = _PS_MODULE_DIR_ . '/' . $this->name . '/';
        $id_tab = Tab::getIdFromClassName($tab_class);

        if ($id_tab) {
            $tab = new Tab((int) $id_tab);
            $id_parent = $tab->id_parent;
            $parent_tab = new Tab((int) $id_parent);

            if (Apparatus::checkFileExists($img_tab_path . $tab->class_name . '.gif')) {
                unlink($img_tab_path . $tab->class_name . '.gif');
            }

            $tab->delete();

            if (Tab::getNbTabs($id_parent) <= 0 && $parent_tab->class_name == self::TAB_2NT) {
                $tab_parent = new Tab((int) $id_parent);
                $img = $tab_parent->class_name . '.gif';

                if (Apparatus::checkFileExists($img_tab_path . $img)) {
                    unlink($img_tab_path . $img);
                }

                if (version_compare(_PS_VERSION_, '1.6', '<') && Apparatus::checkFileExists($module_path . $img)) {
                    unlink($module_path . $img);
                }

                $tab_parent->delete();
            }
        }
    }

    /**
     * Install the module in a tab
     *
     * @param string $tab_class Tab class
     * @param string $tab_name Tab name
     * @param string $tab_parent_class Tab parent's class
     * @param string $tab_parent_name Tab parent's name
     *
     * @return bool
     */
    public function installOnTab($tab_class, $tab_name, $tab_parent_class, $tab_parent_name = '')
    {
        $img_tab_path = _PS_ROOT_DIR_ . '/img/t/';
        $module_path = _PS_MODULE_DIR_ . $this->name . '/';

        if (version_compare(_PS_VERSION_, '1.6', '>')) {
            $logo_path = $module_path . 'views/img/tab_logo_grey.png';
        } else {
            $logo_path = $module_path . 'views/img/tab_logo_color.png';
        }

        $id_tab_parent = Tab::getIdFromClassName($tab_parent_class);

        // If the parent tab does not exist yet, create it
        if (!$id_tab_parent) {
            $tab_parent = new Tab();
            $tab_parent->class_name = $tab_parent_class;
            $tab_parent->module = $this->name;
            $tab_parent->id_parent = 0;

            foreach (Language::getLanguages(false) as $lang) {
                $tab_parent->name[(int) $lang['id_lang']] = $tab_parent_name;
            }

            if (!$tab_parent->save()) {
                $this->_errors[] = sprintf($this->l('Unable to create the "%s" tab'), $tab_parent_class);

                return false;
            }

            $id_tab_parent = $tab_parent->id;
        }

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            if (!Apparatus::checkFileExists($img_tab_path . $tab_parent_class . '.gif')) {
                if (version_compare(_PS_VERSION_, '1.5.5.0', '>=') === true) {
                    $copy = Tools::copy($logo_path, $img_tab_path . $tab_parent_class . '.gif');
                } else {
                    // Tools::copy does not exists before Prestashop 1.5.5.0
                    $copy = copy($logo_path, $img_tab_path . $tab_parent_class . '.gif');
                }

                if (!$copy) {
                    $this->_errors[] = sprintf($this->l('Unable to copy logo.gif in %s'), $img_tab_path);
                    self::lg(sprintf($this->l('Unable to copy logo.gif in %s'), $img_tab_path));
                }
            }
        }

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            if (!Apparatus::checkFileExists($module_path . $tab_parent_class . '.gif')) {
                if (version_compare(_PS_VERSION_, '1.5.5.0', '>=') === true) {
                    $copy = Tools::copy($logo_path, $module_path . $tab_parent_class . '.gif');
                } else {
                    // Tools::copy does not exists before Prestashop 1.5.5.0
                    $copy = copy($logo_path, $module_path . $tab_parent_class . '.gif');
                }

                if (!$copy) {
                    $this->_errors[] = sprintf($this->l('Unable to copy logo.gif in %s'), $module_path);
                    self::lg(sprintf($this->l('Unable to copy logo.gif in %s'), $module_path));
                }
            }
        }

        // If the tab does not exist yet, create it
        if (!Tab::getIdFromClassName($tab_class)) {
            $tab = new Tab();
            $tab->class_name = $tab_class;
            $tab->module = $this->name;
            $tab->id_parent = (int) $id_tab_parent;

            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $tab_name;
            }

            if (version_compare(_PS_VERSION_, '1.7', '>=') === true && $tab_class == self::TAB_MODULE) {
                $tab->icon = 'backup';
            }

            if (!$tab->save()) {
                $this->_errors[] = sprintf($this->l('Unable to create the "%s" tab'), $tab_class);

                return false;
            }
        }

        if (Apparatus::checkFileExists($logo_path)) {
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                if (!Apparatus::checkFileExists($img_tab_path . $tab_class . '.gif')) {
                    if (version_compare(_PS_VERSION_, '1.5.5.0', '>=') === true) {
                        $copy = Tools::copy($logo_path, $img_tab_path . $tab_class . '.gif');
                    } else {
                        // Tools::copy does not exists before Prestashop 1.5.5.0
                        $copy = copy($logo_path, $img_tab_path . $tab_class . '.gif');
                    }

                    if (!$copy) {
                        $this->_errors[] = sprintf($this->l('Unable to copy logo.gif in %s'), $img_tab_path);
                        self::lg(sprintf($this->l('Unable to copy logo.gif in %s'), $img_tab_path));
                    }
                }
            }

            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                if (!Apparatus::checkFileExists($module_path . $tab_class . '.gif')) {
                    if (version_compare(_PS_VERSION_, '1.5.5.0', '>=') === true) {
                        $copy = Tools::copy($logo_path, $module_path . $tab_class . '.gif');
                    } else {
                        // Tools::copy does not exists before Prestashop 1.5.5.0
                        $copy = copy($logo_path, $module_path . $tab_class . '.gif');
                    }

                    if (!$copy) {
                        $this->_errors[] = sprintf($this->l('Unable to copy logo.gif in %s'), $module_path);
                        self::lg(sprintf($this->l('Unable to copy logo.gif in %s'), $module_path));
                    }
                }
            }
        }

        return true;
    }

    public static function tableExists($table)
    {
        // Try a select statement against the table
        // Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
        try {
            $result = Db::getInstance()->getValue('
                SELECT 1
                FROM `' . bqSQL(_DB_PREFIX_ . $table) . '`
            ');
        } catch (Exception $e) {
            // We got an exception == table not found
            return false;
        }

        // Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
        return $result !== false;
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink(self::TAB_MODULE));
    }

    public function createModuleId()
    {
        $prefix = (self::isPrestaEdition(false)) ? 'EDT' : 'STD';

        return $prefix . '_' . Tools::passwdGen(30);
    }

    public function setOperation($operation)
    {
        if (NtbrCore::checkValidIp()) {
            $module_id = Configuration::get('NTBR_ID');

            // Check module ID exists
            if (!$module_id || $module_id == '') {
                Configuration::updateGlobalValue('NTBR_ID', $this->createModuleId());

                $module_id = Configuration::get('NTBR_ID');
            }

            if ($module_id && $module_id != '') {
                if (file_exists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/ntbrfull.php')) {
                    require_once dirname(__FILE__) . '/classes/ntbrfull.php';
                } elseif (file_exists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/ntbrlight.php')) {
                    require_once dirname(__FILE__) . '/classes/ntbrlight.php';
                } else {
                    self::lg('Missing override');
                }

                $ntbr = new NtbrChild();
                $type_module = $ntbr->getTypeModule();
                $light = ($type_module == '') ? 0 : 1;

                // Call the operation url
                $url = self::NT_URL_OPERATION
                . 'module=' . urlencode((string) ($this->name . (($light) ? '_light' : '')))
                . '&id_module=' . urlencode((string) $module_id)
                . '&version=' . urlencode((string) $this->version)
                . '&operation=' . $operation;

                try {
                    Tools::file_get_contents($url);
                } catch (Throwable $t) {
                    self::lg($t->getMessage());
                } catch (Exception $ex) {
                    self::lg($ex->getMessage());
                }
            }
        }
    }

    public static function isPrestaEdition($b = true)
    {
        if ($b) {// Not a test
            return false;
        }

        return Module::isEnabled('smb_edition');
    }

    public function deleteCronFiles()
    {
        $physic_path_modules = Apparatus::getRealPath(_PS_ROOT_DIR_ . '/modules') . '/';
        $physic_path_ajax = $physic_path_modules . $this->name . '/ajax';

        if (self::tableExists('ntbr_config')) {
            $list_config = ConfigNtbr::getListConfigs();

            foreach ($list_config as $config) {
                $id_config = $config['id_ntbr_config'];

                $file_backup_path = $physic_path_ajax . '/backup_' . $id_config . '_' . $this->secure_key . '.php';
                $file_cron_path = $physic_path_ajax . '/php_cron_' . $id_config . '_' . $this->secure_key . '.php';

                if (Apparatus::checkFileExists($file_backup_path)) {
                    unlink($file_backup_path);
                }

                if (Apparatus::checkFileExists($file_cron_path)) {
                    unlink($file_cron_path);
                }
            }
        }

        // Old version of the file (before the config became part of the file name
        $file_path = $physic_path_ajax . '/backup_' . $this->secure_key . '.php';

        if (Apparatus::checkFileExists($file_path)) {
            unlink($file_path);
        }
    }

    public function writeCronFiles($install)
    {
        $physic_path_modules = Apparatus::getRealPath(_PS_ROOT_DIR_ . '/modules') . '/';
        $shop_domain = Tools::getCurrentUrlProtocolPrefix() . Tools::getHttpHost();
        $url_modules = $shop_domain . __PS_BASE_URI__ . 'modules/';
        $url_ajax = $url_modules . $this->name . '/ajax';
        $physic_path_ajax = $physic_path_modules . $this->name . '/ajax';
        $param_secure_key = 'secure_key=' . $this->secure_key;

        $list_crons = [];

        $content_backup = '<?php ';
        $content_backup .= '$config="&config=1"; ';
        $content_backup .= 'if (isset($_GET["config"])) $config="&config=".$_GET["config"]; ';
        $content_backup .= 'header("Location: ' . $url_ajax . '/backup.php?' . $param_secure_key . '".$config); ';
        $content_backup .= 'exit();';

        $cron_backup = [
            'file_path' => $physic_path_ajax . '/backup_' . $this->secure_key . '.php',
            'content' => $content_backup,
        ];

        $list_crons[] = $cron_backup;

        $list_config = ConfigNtbr::getListConfigs();

        foreach ($list_config as $config) {
            $id_config = $config['id_ntbr_config'];

            /*$content = '<?php ';
            $content .= '$curl_handle=curl_init(); ';
            $content .= 'curl_setopt($curl_handle,CURLOPT_FOLLOWLOCATION, true); ';
            $content .= 'curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, true); ';
            $content .= 'curl_setopt($curl_handle,CURLOPT_MAXREDIRS, 10000); ';
            $content .= 'curl_setopt($curl_handle, CURLOPT_URL, "' . $url_ajax . '/backup.php?' . $param_secure_key . '&config=' . $id_config . '"); ';
            $content .= '$result = curl_exec($curl_handle); ';
            $content .= 'curl_close($curl_handle); ';
            $content .= 'if (empty($result)) ';
            $content .= 'echo "An error occured during backup"; ';
            $content .= 'else ';
            $content .= 'echo $result;';*/

            $content = '<?php ';
            $content .= '$secure_key="' . $this->secure_key . '"; ';
            $content .= '$id_config=' . $id_config . '; ';
            $content .= 'require_once dirname(__FILE__) . "/backup.php"; ';

            $cron_auto_php = [
                'file_path' => $physic_path_ajax . '/php_cron_' . $id_config . '_' . $this->secure_key . '.php',
                'content' => $content,
            ];

            $list_crons[] = $cron_auto_php;

            $content_backup = '<?php ';
            $content_backup .= 'header("Location: ' . $url_ajax . '/backup.php?' . $param_secure_key . '&config=' . $id_config . '"); ';
            $content_backup .= 'exit();';

            $cron_backup = [
                'file_path' => $physic_path_ajax . '/backup_' . $id_config . '_' . $this->secure_key . '.php',
                'content' => $content_backup,
            ];

            $list_crons[] = $cron_backup;
        }

        foreach ($list_crons as $cron) {
            $file_path = $cron['file_path'];
            $content = $cron['content'];

            if (Apparatus::checkFileExists($file_path)) {
                $old_content = Tools::file_get_contents($file_path);

                if (strpos($old_content, $url_ajax) === false) {
                    unlink($file_path);
                }
            }

            if (!Apparatus::checkFileExists($file_path)) {
                $file = fopen($file_path, 'w+');
                fwrite($file, $content);
                fclose($file);

                try {
                    if (chmod($file_path, octdec(NtbrCore::PERM_FILE)) !== true) {
                        // The log fonction is this class child's function.
                        // It cannot be use when called from this class, only from its child
                        if (!$install) {
                            $this->log(
                                sprintf(
                                    $this->l('The file "%1$s" permission cannot be updated to %2$d'),
                                    $file_path,
                                    NtbrCore::PERM_FILE
                                ),
                                true
                            );
                        }
                    }
                } catch (Throwable $t) {
                    // Executed only in PHP 7, will not match in PHP 5
                    // The log fonction is this class child's function.
                    // It cannot be use when called from this class, only from its child
                    if (!$install) {
                        $this->log(
                            sprintf(
                                $this->l('The file "%1$s" permission cannot be updated to %2$d'),
                                $file_path,
                                NtbrCore::PERM_FILE
                            ),
                            true
                        );

                        $this->log($t->getMessage(), true);
                    }
                } catch (Exception $e) {
                    // Executed only in PHP 5, will not be reached in PHP 7
                    // The log fonction is this class child's function.
                    // It cannot be use when called from this class, only from its child
                    if (!$install) {
                        $this->log(
                            sprintf(
                                $this->l('The file "%1$s" permission cannot be updated to %2$d'),
                                $file_path,
                                NtbrCore::PERM_FILE
                            ),
                            true
                        );

                        $this->log($e->getMessage(), true);
                    }
                }
            }
        }
    }

    /**
     * Get module backup directory
     *
     * @return string
     */
    public static function getModuleBackupDirectory()
    {
        $physic_path_modules = Apparatus::getRealPath(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules') . DIRECTORY_SEPARATOR;

        return $physic_path_modules . self::MODULE_NAME . DIRECTORY_SEPARATOR . NtbrCore::BACKUP_FOLDER . DIRECTORY_SEPARATOR;
    }

    public static function lg($message)
    {
        if (!is_string($message)) {
            $message = print_r($message, true);
        } else {
            $message = html_entity_decode($message, ENT_COMPAT, 'UTF-8');
        }

        $module_backup_dir = self::getModuleBackupDirectory();

        $path = $module_backup_dir . 'log.txt';

        if (!($file = fopen($path, 'a+'))) {
            return false;
        }

        if (fwrite($file, date(NtbrCore::LOG_DATE_FORMAT) . ' ' . $message . "\n") === false) {
            return false;
        }

        if (!fclose($file)) {
            return false;
        }

        return true;
    }
}
