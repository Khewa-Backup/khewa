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

if (file_exists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/ntbrfull.php')) {
    require_once dirname(__FILE__) . '/../../classes/ntbrfull.php';
} elseif (file_exists(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/classes/ntbrlight.php')) {
    require_once dirname(__FILE__) . '/../../classes/ntbrlight.php';
} else {
    exit('Missing override');
}

define('CONFIGURE_NTCRON', 'https://ntcron.2n-tech.com/app/configure.php?');
define('CONFIGURE_NTVERSION', 'https://version.2n-tech.com/set_email_version.php?source=xwtzt');

class AdminNtbackupandrestoreController extends ModuleAdminController
{
    const PAGE = 'adminntbackupandrestore';

    private $id_shop;
    private $id_shop_group;
    private $ntbr;

    public function __construct()
    {
        $this->display = 'view';
        $this->bootstrap = true;
        $this->multishop_context = Shop::CONTEXT_ALL;
        $this->context = Context::getContext();

        parent::__construct();

        if (version_compare(_PS_VERSION_, '1.6.0.12', '>=') === true) {
            $this->meta_title = ['NT ' . $this->l('Backup and Restore', self::PAGE)];
        } else {
            $this->meta_title = 'NT ' . $this->l('Backup and Restore', self::PAGE);
        }

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }

        $this->ntbr = new NtbrChild();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $module_path = $this->module->getPathUri();

        /*$version_script = '1.5';
        if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
            $version_script = '1.6';
        }*/

        /*$this->addCSS(
            array(
                $module_path.'views/css/style_'.$version_script.'.css?'.$this->module->version,
                $module_path.'views/css/style.css?'.$this->module->version,
            ),
            'all',
            null,
            false
        );*/

        $this->addCSS([
            $module_path . 'lib/fontawesome-free-6.1.1-web/css/all.min.css',
        ]);

        /*$this->addJS(
            array(
                $module_path.'views/js/script_'.$version_script.'.js?'.$this->module->version,
                $module_path.'views/js/script.js?'.$this->module->version,
            ),
            false
        );*/

        if (!NtBackupAndRestore::isPrestaEdition()) {
            $this->addJS([
                $module_path . 'lib/chartjs-2.9.4/node_modules/chart.js/dist/Chart.js',
            ]);
        }

        if (version_compare(_PS_VERSION_, '1.6.0', '<') === true) {
            $this->addJS([
                $module_path . 'views/js/moment-with-locales.min.js',
            ]);
        }

        return true;
    }

    public function ajaxPreprocess()
    {
        $type_module = $this->ntbr->getTypeModule();
        $light = ($type_module == '') ? 0 : 1;
        $is_presta_edition = NtBackupAndRestore::isPrestaEdition();

        if (!$light) {
            if (!$is_presta_edition) {
                if (Tools::isSubmit('display_ftp_account')) {
                    $this->displayFtpAccount();
                } elseif (Tools::isSubmit('display_dropbox_account')) {
                    $this->displayDropboxAccount();
                } elseif (Tools::isSubmit('display_yandex_account')) {
                    $this->displayYandexAccount();
                } elseif (Tools::isSubmit('display_owncloud_account')) {
                    $this->displayOwncloudAccount();
                } elseif (Tools::isSubmit('display_shadow_drive_account')) {
                    $this->displayShadowDriveAccount();
                } elseif (Tools::isSubmit('display_webdav_account')) {
                    $this->displayWebdavAccount();
                } elseif (Tools::isSubmit('display_googledrive_account')) {
                    $this->displayGoogledriveAccount();
                } elseif (Tools::isSubmit('display_googlecloud_account')) {
                    $this->displayGooglecloudAccount();
                } elseif (Tools::isSubmit('display_pcloud_account')) {
                    $this->displayPcloudAccount();
                } elseif (Tools::isSubmit('display_box_account')) {
                    $this->displayBoxAccount();
                } elseif (Tools::isSubmit('display_onedrive_account')) {
                    $this->displayOnedriveAccount();
                } elseif (Tools::isSubmit('display_sugarsync_account')) {
                    $this->displaySugarsyncAccount();
                } elseif (Tools::isSubmit('display_aws_account')) {
                    $this->displayAwsAccount();
                } elseif (Tools::isSubmit('save_ftp')) {
                    $this->saveFtp();
                } elseif (Tools::isSubmit('save_dropbox')) {
                    $this->saveDropbox();
                } elseif (Tools::isSubmit('save_yandex')) {
                    $this->saveYandex();
                } elseif (Tools::isSubmit('save_owncloud')) {
                    $this->saveOwncloud();
                } elseif (Tools::isSubmit('save_shadow_drive')) {
                    $this->saveShadowDrive();
                } elseif (Tools::isSubmit('save_webdav')) {
                    $this->saveWebdav();
                } elseif (Tools::isSubmit('save_googledrive_api')) {
                    $this->saveGoogledriveApi();
                } elseif (Tools::isSubmit('save_googledrive')) {
                    $this->saveGoogledrive();
                } elseif (Tools::isSubmit('save_googlecloud')) {
                    $this->saveGooglecloud();
                } elseif (Tools::isSubmit('save_pcloud')) {
                    $this->savePcloud();
                } elseif (Tools::isSubmit('save_box')) {
                    $this->saveBox();
                } elseif (Tools::isSubmit('save_onedrive')) {
                    $this->saveOnedrive();
                } elseif (Tools::isSubmit('save_sugarsync')) {
                    $this->saveSugarsync();
                } elseif (Tools::isSubmit('save_aws')) {
                    $this->saveAws();
                } elseif (Tools::isSubmit('check_connection_ftp')) {
                    $this->checkConnectionFtp();
                } elseif (Tools::isSubmit('check_connection_dropbox')) {
                    $this->checkConnectionDropbox();
                } elseif (Tools::isSubmit('check_connection_yandex')) {
                    $this->checkConnectionYandex();
                } elseif (Tools::isSubmit('check_connection_owncloud')) {
                    $this->checkConnectionOwncloud();
                } elseif (Tools::isSubmit('check_connection_shadow_drive')) {
                    $this->checkConnectionShadowDrive();
                } elseif (Tools::isSubmit('check_connection_webdav')) {
                    $this->checkConnectionWebdav();
                } elseif (Tools::isSubmit('check_connection_googledrive')) {
                    $this->checkConnectionGoogledrive();
                } elseif (Tools::isSubmit('check_connection_googlecloud')) {
                    $this->checkConnectionGooglecloud();
                } elseif (Tools::isSubmit('check_connection_pcloud')) {
                    $this->checkConnectionPcloud();
                } elseif (Tools::isSubmit('check_connection_box')) {
                    $this->checkConnectionBox();
                } elseif (Tools::isSubmit('check_connection_onedrive')) {
                    $this->checkConnectionOnedrive();
                } elseif (Tools::isSubmit('check_connection_sugarsync')) {
                    $this->checkConnectionSugarsync();
                } elseif (Tools::isSubmit('check_connection_aws')) {
                    $this->checkConnectionAws();
                } elseif (Tools::isSubmit('delete_ftp')) {
                    $this->deleteFtp();
                } elseif (Tools::isSubmit('delete_dropbox')) {
                    $this->deleteDropbox();
                } elseif (Tools::isSubmit('delete_yandex')) {
                    $this->deleteYandex();
                } elseif (Tools::isSubmit('delete_owncloud')) {
                    $this->deleteOwncloud();
                } elseif (Tools::isSubmit('delete_shadow_drive')) {
                    $this->deleteShadowDrive();
                } elseif (Tools::isSubmit('delete_webdav')) {
                    $this->deleteWebdav();
                } elseif (Tools::isSubmit('delete_googledrive')) {
                    $this->deleteGoogledrive();
                } elseif (Tools::isSubmit('delete_googlecloud')) {
                    $this->deleteGooglecloud();
                } elseif (Tools::isSubmit('delete_pcloud')) {
                    $this->deletePcloud();
                } elseif (Tools::isSubmit('delete_box')) {
                    $this->deleteBox();
                } elseif (Tools::isSubmit('delete_onedrive')) {
                    $this->deleteOnedrive();
                } elseif (Tools::isSubmit('delete_sugarsync')) {
                    $this->deleteSugarsync();
                } elseif (Tools::isSubmit('delete_aws')) {
                    $this->deleteAws();
                } elseif (Tools::isSubmit('display_googledrive_tree')) {
                    $this->displayGoogledriveTree();
                } elseif (Tools::isSubmit('display_googledrive_tree_child')) {
                    $this->displayGoogledriveTreeChild();
                } elseif (Tools::isSubmit('display_googlecloud_tree')) {
                    $this->displayGooglecloudTree();
                } elseif (Tools::isSubmit('display_googlecloud_tree_child')) {
                    $this->displayGooglecloudTreeChild();
                } elseif (Tools::isSubmit('display_pcloud_tree')) {
                    $this->displayPcloudTree();
                } elseif (Tools::isSubmit('display_pcloud_tree_child')) {
                    $this->displayPcloudTreeChild();
                } elseif (Tools::isSubmit('display_box_tree')) {
                    $this->displayBoxTree();
                } elseif (Tools::isSubmit('display_box_tree_child')) {
                    $this->displayBoxTreeChild();
                } elseif (Tools::isSubmit('display_onedrive_tree')) {
                    $this->displayOnedriveTree();
                } elseif (Tools::isSubmit('display_onedrive_tree_child')) {
                    $this->displayOnedriveTreeChild();
                } elseif (Tools::isSubmit('display_sugarsync_tree')) {
                    $this->displaySugarsyncTree();
                } elseif (Tools::isSubmit('display_sugarsync_tree_child')) {
                    $this->displaySugarsyncTreeChild();
                } elseif (Tools::isSubmit('display_aws_tree')) {
                    $this->displayAwsTree();
                } elseif (Tools::isSubmit('display_aws_tree_child')) {
                    $this->displayAwsTreeChild();
                } elseif (Tools::isSubmit('send_backup')) {
                    $this->sendBackupAway();
                } elseif (Tools::isSubmit('save_config_profile')) {
                    $this->saveConfigProfile();
                } elseif (Tools::isSubmit('delete_config')) {
                    $this->deleteConfig();
                } elseif (Tools::isSubmit('get_dropbox_files')) {
                    $this->getDropboxFiles();
                } elseif (Tools::isSubmit('download_dropbox_file')) {
                    $this->downloadDropboxFile();
                } elseif (Tools::isSubmit('delete_dropbox_file')) {
                    $this->deleteDropboxFile();
                } elseif (Tools::isSubmit('get_yandex_files')) {
                    $this->getYandexFiles();
                } elseif (Tools::isSubmit('download_yandex_file')) {
                    $this->downloadYandexFile();
                } elseif (Tools::isSubmit('delete_yandex_file')) {
                    $this->deleteYandexFile();
                } elseif (Tools::isSubmit('get_googledrive_files')) {
                    $this->getGoogledriveFiles();
                } elseif (Tools::isSubmit('download_googledrive_file')) {
                    $this->downloadGoogledriveFile();
                } elseif (Tools::isSubmit('delete_googledrive_file')) {
                    $this->deleteGoogledriveFile();
                } elseif (Tools::isSubmit('get_googlecloud_files')) {
                    $this->getGooglecloudFiles();
                } elseif (Tools::isSubmit('delete_googlecloud_file')) {
                    $this->deleteGooglecloudFile();
                } elseif (Tools::isSubmit('get_pcloud_files')) {
                    $this->getPcloudFiles();
                } elseif (Tools::isSubmit('delete_pcloud_file')) {
                    $this->deletePcloudFile();
                } elseif (Tools::isSubmit('get_box_files')) {
                    $this->getBoxFiles();
                } elseif (Tools::isSubmit('get_aws_files')) {
                    $this->getAwsFiles();
                } elseif (Tools::isSubmit('download_box_file')) {
                    $this->downloadBoxFile();
                } elseif (Tools::isSubmit('delete_box_file')) {
                    $this->deleteBoxFile();
                } elseif (Tools::isSubmit('get_onedrive_files')) {
                    $this->getOnedriveFiles();
                } elseif (Tools::isSubmit('download_onedrive_file')) {
                    $this->downloadOnedriveFile();
                } elseif (Tools::isSubmit('delete_onedrive_file')) {
                    $this->deleteOnedriveFile();
                } elseif (Tools::isSubmit('delete_aws_file')) {
                    $this->deleteAwsFile();
                } elseif (Tools::isSubmit('get_owncloud_files')) {
                    $this->getOwncloudFiles();
                } elseif (Tools::isSubmit('download_owncloud_file')) {
                    $this->downloadOwncloudFile();
                } elseif (Tools::isSubmit('delete_owncloud_file')) {
                    $this->deleteOwncloudFile();
                } elseif (Tools::isSubmit('get_shadow_drive_files')) {
                    $this->getShadowDriveFiles();
                } elseif (Tools::isSubmit('download_shadow_drive_file')) {
                    $this->downloadShadowDriveFile();
                } elseif (Tools::isSubmit('delete_shadow_drive_file')) {
                    $this->deleteShadowDriveFile();
                } elseif (Tools::isSubmit('get_webdav_files')) {
                    $this->getWebdavFiles();
                } elseif (Tools::isSubmit('download_webdav_file')) {
                    $this->downloadWebdavFile();
                } elseif (Tools::isSubmit('delete_webdav_file')) {
                    $this->deleteWebdavFile();
                } elseif (Tools::isSubmit('get_ftp_files')) {
                    $this->getFtpFiles();
                } elseif (Tools::isSubmit('download_ftp_file')) {
                    $this->downloadFtpFile();
                } elseif (Tools::isSubmit('delete_ftp_file')) {
                    $this->deleteFtpFile();
                } elseif (Tools::isSubmit('get_onedrive_authorize_url')) {
                    $this->getOnedriveAuthorizeUrl();
                } elseif (Tools::isSubmit('get_dropbox_authorize_url')) {
                    $this->getDropboxAuthorizeUrl();
                } elseif (Tools::isSubmit('get_backup_sodium_key')) {
                    $this->getBackupSodiumKey();
                } elseif (Tools::isSubmit('create_backup_sodium_key')) {
                    $this->createBackupSodiumKey();
                } elseif (Tools::isSubmit('generate_urls')) {
                    $this->generateUrls();
                } elseif (Tools::isSubmit('scan_dir_files')) {
                    $this->scanFiles();
                } elseif (Tools::isSubmit('delete_scan_files')) {
                    $this->deleteScanFiles();
                } elseif (Tools::isSubmit('get_directory_children')) {
                    $this->getDirectoryChidren();
                }
            }

            if (Tools::isSubmit('restore_backup')) {
                $this->restoreBackup();
            } elseif (Tools::isSubmit('end_restore_backup')) {
                $this->endRestoreBackup();
            }
        }

        if (!$is_presta_edition) {
            if (Tools::isSubmit('save_config')) {
                $this->saveConfig();
            } elseif (Tools::isSubmit('download_backup')) {
                $this->downloadBackup();
            } elseif (Tools::isSubmit('get_js_download')) {
                $this->getJsBackup();
            }
        }

        if (Tools::isSubmit('save_infos_backup')) {
            $this->saveInfosBackup();
        } elseif (Tools::isSubmit('delete_backup')) {
            $this->deleteBackup();
        } elseif (Tools::isSubmit('create_backup')) {
            $this->createBackup();
        } elseif (Tools::isSubmit('refresh_backup')) {
            $this->ntbr->log('backup refresh', true);
            $this->refreshBackup();
        } elseif (Tools::isSubmit('add_backup')) {
            $this->addBackup();
        } elseif (Tools::isSubmit('send_file_to_backup_dir')) {
            $this->sendFileToBackupDir();
        } elseif (Tools::isSubmit('get_time_between_refresh')) {
            $this->getTimeBetweenRefresh();
        } elseif (Tools::isSubmit('log_msg')) {
            $this->logMsg();
        } elseif (Tools::isSubmit('get_backup_download_data')) {
            $this->getBackupDownloadData();
        } elseif (Tools::isSubmit('save_automation')) {
            $this->saveAutomation();
        } elseif (Tools::isSubmit('upd_shop_url')) {
            $this->updShopUrl();
        }

        $this->ntbr->log('ERR' . $this->ntbr->l('An error occured', self::PAGE));
        exit(json_encode([]));
    }

    public function renderView()
    {
        $type_module = $this->ntbr->getTypeModule();
        $light = ($type_module == '') ? 0 : 1;
        $is_presta_edition = NtBackupAndRestore::isPrestaEdition() ? 1 : 0;
        $context = Context::getContext();
        $shop = $context->shop;
        $physic_path_modules = Apparatus::getRealPath(_PS_ROOT_DIR_ . '/modules') . '/';
        $fct_crypt_exists = true;
        $os_windows = false;
        $curl_exists = true;
        $param_secure_key = 'secure_key=' . $this->ntbr->secure_key;
        $default_config = new ConfigNtbr(ConfigNtbr::getIdDefault());
        $list_config = ConfigNtbr::getListConfigs();
        $multi_config = GlobConfNtbr::get('NTBR_MULTI_CONFIG');
        $authorized_type = [];

        $domain_use = Tools::getHttpHost();
        $protocol = Tools::getCurrentUrlProtocolPrefix();
        $shop_domain = $protocol . $domain_use;
        $base_uri = $shop->getBaseURI();
        $shop_url = $shop_domain . __PS_BASE_URI__;
        $ntbr_last_shop_url = $this->ntbr->decrypt(GlobConfNtbr::get('NTBR_LAST_SHOP_URL'));

        $shop_url_changed = false;

        if ($shop_url != $ntbr_last_shop_url) {
            if (!$is_presta_edition) {
                $shop_url_changed = true;
            } else {
                $physic_path_ajax = $physic_path_modules . $this->ntbr->name . '/ajax';
                $old_url_ajax = $ntbr_last_shop_url . 'modules/' . $this->ntbr->name . '/ajax';
                $domain_changed = false;

                // Check if there are out dated cron files that correspond to last shop url
                // If so, this is not a new edition install, but a change of domain in an already installed edition
                foreach ($list_config as $a_conf) {
                    $cron_path = $physic_path_ajax . '/php_cron_' . $a_conf['id_ntbr_config'] . '_' . $this->ntbr->secure_key . '.php';

                    if (Apparatus::checkFileExists($cron_path)) {
                        $old_content = Tools::file_get_contents($cron_path);

                        // If there is the old url, then it was installed already
                        if (strpos($old_content, $old_url_ajax) !== false) {
                            $domain_changed = true;
                            break;
                        }
                    }
                }

                if ($domain_changed) {
                    // Update domaine in automation
                    $this->ntbr->updShopUrl();

                    foreach ($list_config as $a_cnfg) {
                        $o_cnfg = new ConfigNtbr($a_cnfg['id_ntbr_config']);
                        $o_cnfg->backup_dir = '/srv/data/web/vhosts/' . $domain_use . '/';

                        $o_cnfg->update();
                    }
                } else {
                    // Force replace, since it is a new domain
                    Configuration::updateGlobalValue('NTBR_ID', $this->ntbr->createModuleId());

                    // This is basically an installation
                    $this->ntbr->setOperation(NtbrCore::OP_INSTALL);

                    // Enable automation
                    $hours = GlobConfNtbr::get('NTBR_AUTOMATION_2NT_HOURS');
                    $minutes = GlobConfNtbr::get('NTBR_AUTOMATION_2NT_MINUTES');

                    if ($this->ntbr->saveAutomation(1, $hours, $minutes)) {
                        GlobConfNtbr::set('NTBR_LAST_SHOP_URL', $this->ntbr->encrypt($shop_url));
                    }

                    // Init configs
                    GlobConfNtbr::set('NTBR_MULTI_CONFIG', 1);

                    $config_complete_exists = false;
                    $config_file_exists = false;
                    $config_dump_exists = false;

                    foreach ($list_config as $a_config) {
                        $o_config = new ConfigNtbr($a_config['id_ntbr_config']);

                        if ($o_config->type_backup == $this->ntbr->type_backup_complete) {
                            $config_complete_exists = true;
                        } elseif ($o_config->type_backup == $this->ntbr->type_backup_file) {
                            $config_file_exists = true;
                        } elseif ($o_config->type_backup == $this->ntbr->type_backup_base) {
                            $config_dump_exists = true;
                        }
                    }

                    if (!$config_complete_exists || !$config_file_exists || !$config_dump_exists) {
                        if (!$config_complete_exists) {
                            $this->ntbr->saveConfigProfile(true, 'Complète', $this->ntbr->type_backup_complete);
                        }

                        if (!$config_file_exists) {
                            $this->ntbr->saveConfigProfile(false, 'PS_FILES', $this->ntbr->type_backup_file);
                        }

                        if (!$config_dump_exists) {
                            $this->ntbr->saveConfigProfile(false, 'PS_DB', $this->ntbr->type_backup_base);
                        }

                        $list_config = ConfigNtbr::getListConfigs();
                    }

                    foreach ($list_config as $a_cnfg) {
                        $o_cnfg = new ConfigNtbr($a_cnfg['id_ntbr_config']);
                        $o_cnfg->nb_backup = 7;
                        $o_cnfg->backup_dir = '/srv/data/web/vhosts/' . $domain_use . '/';
                        $o_cnfg->time_between_refresh = 25;
                        $o_cnfg->part_size = 0;
                        $o_cnfg->ignore_directories = 'upload';

                        $o_cnfg->update();
                    }
                }
            }
        }

        if ($multi_config) {
            foreach ($list_config as $conf) {
                if (!in_array($conf['type_backup'], $authorized_type)) {
                    $authorized_type[] = $conf['type_backup'];
                }
            }
        } else {
            $authorized_type[] = $default_config->type_backup;
        }

        // Check if a backup is running
        $running_backup = (int) $this->ntbr->runningBackup();

        if (!$running_backup && Apparatus::checkFileExists(_PS_MODULE_DIR_ . $this->ntbr->name . '/' . NtbrChild::STOP_FILE)) {
            $this->ntbr->fileDelete(_PS_MODULE_DIR_ . $this->ntbr->name . '/' . NtbrChild::STOP_FILE);
        }

        if (Tools::isSubmit('hide_big_site')) {
            GlobConfNtbr::set('NTBR_BIG_WEBSITE_HIDE', 1);
        }

        $http_context = stream_context_create(
            ['http' => [
                'timeout' => 1,
            ],
            ]
        );

        $available_version = Tools::file_get_contents(NtbrCore::URL_VERSION, false, $http_context, 1);

        // version_compare return -1 if first version is smaller than the second,
        // 0 if they are equals and 1 if the second is smaller than the first
        // $available_version < $this->ntbr->version
        if (version_compare($this->ntbr->version, $available_version) == 1) {
            $available_version = 0; // Make sur the test in smarty will display the right thing
        }

        // Add IP for maintenance mode
        $this->ntbr->setMaintenanceIP();

        if ($base_uri == '/') {
            $base_uri = '';
        }

        $module_controller_link = $context->link->getAdminLink(NtBackupAndRestore::TAB_MODULE);

        if (Configuration::get('PS_SSL_ENABLED')) {
            $domain_config = ShopUrl::getMainShopDomainSSL();
        } else {
            $domain_config = ShopUrl::getMainShopDomain();
        }

        $current_address = $_SERVER['PHP_SELF'];
        $admin_directory = str_replace($base_uri, '', str_replace('index.php', '', $current_address));

        GlobConfNtbr::set('NTBR_ADMIN_DIR', str_replace('/', '', $admin_directory));

        $module_address_use = $protocol . $domain_use . $base_uri . $admin_directory . $module_controller_link;
        $module_address_config = $protocol . $domain_config . $base_uri . $admin_directory . $module_controller_link;

        $url_modules = $shop_url . 'modules/';
        $url_ajax = $url_modules . $this->ntbr->name . '/ajax';
        $documentation = $url_modules . $this->ntbr->name . '/readme_en.pdf';
        $googledrive_app = $url_modules . $this->ntbr->name . '/googledrive_app_en.pdf';
        $changelog = $url_modules . $this->ntbr->name . '/changelog.txt';
        $ajax_loader = $url_modules . $this->ntbr->name . '/views/img/ajax-loader.gif';
        $documentation_name = 'readme_en.pdf';
        $googledrive_app_name = 'googledrive_app_en.pdf';
        $this->id_shop = (int) Configuration::get('PS_SHOP_DEFAULT');
        $this->id_shop_group = Shop::getGroupFromShop($this->id_shop);

        clearstatcache();
        $list_module_content = $this->ntbr->listDirectoryContent($physic_path_modules . $this->ntbr->name);

        foreach ($list_module_content as $file) {
            if ($file['perm'] != NtbrCore::PERM_FILE || $file['perm'] != NtbrCore::PERM_DIR) {
                if (is_dir($file['path'])) {
                    try {
                        if (chmod($file['path'], octdec(NtbrCore::PERM_DIR)) !== true) {
                            $msg = sprintf(
                                $this->ntbr->l('The directory "%1$s" permission cannot be updated to %2$d', self::PAGE),
                                $file['path'],
                                NtbrCore::PERM_DIR
                            );
                            $this->ntbr->log($msg, true);
                            $this->ntbr->errors[] = $msg;
                        }
                    } catch (Throwable $t) {
                        // Executed only in PHP 7, will not match in PHP 5
                        $msg = sprintf(
                            $this->ntbr->l('The directory "%1$s" permission cannot be updated to %2$d', self::PAGE),
                            $file['path'],
                            NtbrCore::PERM_DIR
                        );
                        $this->ntbr->log($msg, true);
                        $this->ntbr->log($t->getMessage(), true);
                        $this->ntbr->errors[] = $msg;
                    } catch (Exception $ex) {
                        // Executed only in PHP 5, will not be reached in PHP 7
                        $msg = sprintf(
                            $this->ntbr->l('The directory "%1$s" permission cannot be updated to %2$d', self::PAGE),
                            $file['path'],
                            NtbrCore::PERM_DIR
                        );
                        $this->ntbr->log($msg, true);
                        $this->ntbr->log($ex->getMessage(), true);
                        $this->ntbr->errors[] = $msg;
                    }
                } else {
                    try {
                        if (chmod($file['path'], octdec(NtbrCore::PERM_FILE)) !== true) {
                            $msg = sprintf(
                                $this->ntbr->l('The file "%1$s" permission cannot be updated to %2$d', self::PAGE),
                                $file['path'],
                                NtbrCore::PERM_FILE
                            );
                            $this->ntbr->log($msg, true);
                            $this->ntbr->errors[] = $msg;
                        }
                    } catch (Throwable $t) {
                        // Executed only in PHP 7, will not match in PHP 5
                        $msg = sprintf(
                            $this->ntbr->l('The directory "%1$s" permission cannot be updated to %2$d', self::PAGE),
                            $file['path'],
                            NtbrCore::PERM_DIR
                        );
                        $this->ntbr->log($msg, true);
                        $this->ntbr->log($t->getMessage(), true);
                        $this->ntbr->errors[] = $msg;
                    } catch (Exception $ex) {
                        // Executed only in PHP 5, will not be reached in PHP 7
                        $msg = sprintf(
                            $this->ntbr->l('The file "%1$s" permission cannot be updated to %2$d', self::PAGE),
                            $file['path'],
                            NtbrCore::PERM_FILE
                        );
                        $this->ntbr->log($msg, true);
                        $this->ntbr->log($ex->getMessage(), true);
                        $this->ntbr->errors[] = $msg;
                    }
                }
            }
        }
        // p($this->ntbr->errors);
        // d($list_module_content);

        if (stripos(PHP_OS, 'win') !== false) {
            $os_windows = true;
        }

        if (!extension_loaded('openssl')) {
            $fct_crypt_exists = false;
            OwncloudNtbr::deactiveAllOwncloud();
            ShadowDriveNtbr::deactiveAllShadowDrive();
            WebdavNtbr::deactiveAllWebdav();
            FtpNtbr::deactiveAllFtpSftp();
            AwsNtbr::deactiveAllAws();
            DropboxNtbr::deactiveAllDropbox();
            YandexNtbr::deactiveAllYandex();
            GoogledriveNtbr::deactiveAllGoogledrive();
            GooglecloudNtbr::deactiveAllGooglecloud();
            PcloudNtbr::deactiveAllPcloud();
            BoxNtbr::deactiveAllBox();
            OnedriveNtbr::deactiveAllOnedrive();
            SugarsyncNtbr::deactiveAllSugarsync();
        }

        if (!extension_loaded('curl')) {
            $curl_exists = false;
        }

        if ($os_windows || !$fct_crypt_exists) {
            // for ftp_ssl_connect() to be available on Windows you must compile your own PHP binaries
            FtpNtbr::removeSSL();
        }

        // FTP
        $ftp_port_default = '21';
        $ftp_directory_default = '/';
        $ftp_default = FtpNtbr::getDefaultValues();

        // Dropbox
        $dropbox_default = DropboxNtbr::getDefaultValues();

        // Yandex
        $yandex_default = YandexNtbr::getDefaultValues();

        if ($light) {
            $yandex_authorizeUrl = '';
        } else {
            $yandex = $this->ntbr->connectToYandex();
            $yandex_authorizeUrl = $yandex->getLogInUrl();
        }

        // OneDrive
        $onedrive_default = OnedriveNtbr::getDefaultValues();

        // Google Drive
        $googledrive_default = GoogledriveNtbr::getDefaultValues();

        if ($light) {
            $googledrive_authorizeUrl = '';
        } else {
            $googledrive = $this->ntbr->connectToGoogledrive();
            $googledrive_authorizeUrl = $googledrive->getLogInUrl();
        }

        // Google Cloud Storage
        $googlecloud_default = GooglecloudNtbr::getDefaultValues();

        if ($light) {
            $googlecloud_authorizeUrl = '';
        } else {
            $googlecloud = $this->ntbr->connectToGooglecloud();
            $googlecloud_authorizeUrl = $googlecloud->getLogInUrl();
        }

        // pCloud
        $pcloud_default = PcloudNtbr::getDefaultValues();

        if ($light) {
            $pcloud_authorizeUrl = '';
        } else {
            $pcloud_authorizeUrl = PcloudLib::getLogInUrl();
        }

        // Box
        $box_default = BoxNtbr::getDefaultValues();

        if ($light) {
            $box_authorizeUrl = '';
        } else {
            $box = $this->ntbr->connectToBox();
            $box_authorizeUrl = $box->getLogInUrl();
        }

        // SugarSync
        $sugarsync_default = SugarsyncNtbr::getDefaultValues();

        // ownCloud
        $owncloud_default = OwncloudNtbr::getDefaultValues();

        // Shadow Drive
        $shadow_drive_default = ShadowDriveNtbr::getDefaultValues();

        // WebDAV
        $webdav_default = WebdavNtbr::getDefaultValues();

        // AWS
        $aws_default = AwsNtbr::getDefaultValues();

        $default_backup_dir = NtBackupAndRestore::getModuleBackupDirectory();

        // Scan files
        $scan_files_enable = false;

        foreach ($list_config as &$config) {
            $id_config = $config['id_ntbr_config'];

            // If the directory configured does not exists anymore, replace it by the default one
            if (!is_dir($config['backup_dir'])) {
                $config['backup_dir'] = $default_backup_dir;

                $o_config = new ConfigNtbr($id_config);
                $o_config->backup_dir = $config['backup_dir'];
                $o_config->update();
            }

            // Option products images
            if ($config['type_backup'] == $this->ntbr->type_backup_complete && $config['ignore_product_image'] == NtbrCore::PRODUCT_IMG_ONLY) {
                $config['ignore_product_image'] = NtbrCore::PRODUCT_IMG_AND_FILE;
            }

            // Scan files
            if ($config['scan_files']) {
                $scan_files_enable = true;
            }

            // Decrypt key
            if ($config['sodium_key']) {
                $config['sodium_key'] = $this->ntbr->decrypt($config['sodium_key']);
            }

            // Ignore tables
            if ($config['ignore_tables']) {
                $config['ignore_tables'] = ',' . $config['ignore_tables'] . ','; // make sur to separate name in case a table contain the name of an other table
            }

            // Not recreate tables
            if ($config['not_recreate_tables']) {
                $config['not_recreate_tables'] = ',' . $config['not_recreate_tables'] . ','; // make sur to separate name in case a table contain the name of an other table
            }

            // FTP
            $config['ftp_accounts'] = FtpNtbr::getListFtpAccounts($id_config);
            $config['nb_ftp_accounts'] = count($config['ftp_accounts']);
            $config['nb_ftp_active_accounts'] = FtpNtbr::getNbAccountsActive($id_config);

            // Dropbox
            $config['dropbox_accounts'] = DropboxNtbr::getListDropboxAccounts($id_config);
            $config['nb_dropbox_accounts'] = count($config['dropbox_accounts']);
            $config['nb_dropbox_active_accounts'] = DropboxNtbr::getNbAccountsActive($id_config);

            // Yandex
            $config['yandex_accounts'] = YandexNtbr::getListYandexAccounts($id_config);
            $config['nb_yandex_accounts'] = count($config['yandex_accounts']);
            $config['nb_yandex_active_accounts'] = YandexNtbr::getNbAccountsActive($id_config);

            // OneDrive
            $config['onedrive_accounts'] = OnedriveNtbr::getListOnedriveAccounts($id_config);
            $config['nb_onedrive_accounts'] = count($config['onedrive_accounts']);
            $config['nb_onedrive_active_accounts'] = OnedriveNtbr::getNbAccountsActive($id_config);

            // SugarSync
            $config['sugarsync_accounts'] = SugarsyncNtbr::getListSugarsyncAccounts($id_config);
            $config['nb_sugarsync_accounts'] = count($config['sugarsync_accounts']);
            $config['nb_sugarsync_active_accounts'] = SugarsyncNtbr::getNbAccountsActive($id_config);

            // Google Drive
            $config['googledrive_accounts'] = GoogledriveNtbr::getListGoogledriveAccounts($id_config);
            $config['nb_googledrive_accounts'] = count($config['googledrive_accounts']);
            $config['nb_googledrive_active_accounts'] = GoogledriveNtbr::getNbAccountsActive($id_config);

            // Google Cloud Storage
            $config['googlecloud_accounts'] = GooglecloudNtbr::getListGooglecloudAccounts($id_config);
            $config['nb_googlecloud_accounts'] = count($config['googlecloud_accounts']);
            $config['nb_googlecloud_active_accounts'] = GooglecloudNtbr::getNbAccountsActive($id_config);

            // pCloud
            $config['pcloud_accounts'] = PcloudNtbr::getListPcloudAccounts($id_config);
            $config['nb_pcloud_accounts'] = count($config['pcloud_accounts']);
            $config['nb_pcloud_active_accounts'] = PcloudNtbr::getNbAccountsActive($id_config);

            // Box
            $config['box_accounts'] = BoxNtbr::getListBoxAccounts($id_config);
            $config['nb_box_accounts'] = count($config['box_accounts']);
            $config['nb_box_active_accounts'] = BoxNtbr::getNbAccountsActive($id_config);

            // ownCloud
            $config['owncloud_accounts'] = OwncloudNtbr::getListOwncloudAccounts($id_config);
            $config['nb_owncloud_accounts'] = count($config['owncloud_accounts']);
            $config['nb_owncloud_active_accounts'] = OwncloudNtbr::getNbAccountsActive($id_config);

            // Shadow Drive
            $config['shadow_drive_accounts'] = ShadowDriveNtbr::getListShadowDriveAccounts($id_config);
            $config['nb_shadow_drive_accounts'] = count($config['shadow_drive_accounts']);
            $config['nb_shadow_drive_active_accounts'] = ShadowDriveNtbr::getNbAccountsActive($id_config);

            // WebDAV
            $config['webdav_accounts'] = WebdavNtbr::getListWebdavAccounts($id_config);
            $config['nb_webdav_accounts'] = count($config['webdav_accounts']);
            $config['nb_webdav_active_accounts'] = WebdavNtbr::getNbAccountsActive($id_config);

            // AWS
            $config['aws_accounts'] = AwsNtbr::getListAwsAccounts($id_config);
            $config['nb_aws_accounts'] = count($config['aws_accounts']);
            $config['nb_aws_active_accounts'] = AwsNtbr::getNbAccountsActive($id_config);
        }

        if (Tools::file_exists_cache(
            $physic_path_modules . $this->ntbr->name . '/readme_' . $this->context->language->iso_code . '.pdf'
        )
        ) {
            $documentation = $url_modules . $this->ntbr->name . '/readme_' . $this->context->language->iso_code . '.pdf';
            $documentation_name = 'readme_' . $this->context->language->iso_code . '.pdf';
        }

        if (Tools::file_exists_cache(
            $physic_path_modules . $this->ntbr->name . '/googledrive_app_' . $this->context->language->iso_code . '.pdf'
        )
        ) {
            $googledrive_app = $url_modules . $this->ntbr->name . '/googledrive_app_' . $this->context->language->iso_code . '.pdf';
            $googledrive_app_name = 'googledrive_app_' . $this->context->language->iso_code . '.pdf';
        }

        $display_translate_tab = true;
        $translate_lng = [];
        $translate_files = glob($physic_path_modules . $this->ntbr->name . '/translations/*.php');

        foreach ($translate_files as $trslt_file) {
            $translate_lng[] = basename($trslt_file, '.php');
        }

        if (in_array($this->context->language->iso_code, $translate_lng)) {
            $display_translate_tab = false;
        }

        $uncompress = NtbrCore::EXT_UNCOMPRESS; // tar
        $compress = NtbrCore::EXT_UNCOMPRESS . '.' . NtbrCore::EXT_COMPRESS; // tar.gz
        $uncompress_crypt = NtbrCore::EXT_UNCOMPRESS . '.' . NtbrCore::EXT_CRYPT; // tar.crypt
        $compress_crypt = NtbrCore::EXT_UNCOMPRESS . '.' . NtbrCore::EXT_COMPRESS . '.' . NtbrCore::EXT_CRYPT; // tar.gz.crypt

        $backup_files_to_upload = $this->ntbr->findOldBackups(0, false);
        $backup_files = $this->ntbr->findOldBackups();
        $restore_backup_files_complete = [];
        $restore_backup_files_file = [];
        $restore_backup_files_base = [];

        foreach ($backup_files as $key => &$b_file) {
            if (!$multi_config && $default_config->id != $b_file['id_config']) {
                $b_file['id_config'] = 0;
            }

            // Check if first (or only) file exist (in case backup_dir changed)
            $first_file = reset($b_file['part']);

            if (!Apparatus::checkFileExists($b_file['backup_dir'] . $first_file['name'])) {
                unset($backup_files[$key]);
            }

            if (Apparatus::endsWith($first_file['name'], $uncompress_crypt) || Apparatus::endsWith($first_file['name'], $compress_crypt)) {
                $b_file['crypted'] = true;
            } else {
                $b_file['crypted'] = false;
            }

            if ($b_file['id_config']) {
                $file_config = new ConfigNtbr($b_file['id_config']);

                if ($file_config->type_backup == $this->ntbr->type_backup_complete
                    && in_array($this->ntbr->type_backup_complete, $authorized_type)
                ) {
                    $restore_backup_files_complete[] = $b_file;
                } elseif ($file_config->type_backup == $this->ntbr->type_backup_file
                    && in_array($this->ntbr->type_backup_file, $authorized_type)
                ) {
                    $restore_backup_files_file[] = $b_file;
                } elseif ($file_config->type_backup == $this->ntbr->type_backup_base
                    && in_array($this->ntbr->type_backup_base, $authorized_type)
                ) {
                    $restore_backup_files_base[] = $b_file;
                }
            }
        }

        foreach ($backup_files_to_upload as $key => $b_to_up) {
            if (stripos($b_to_up['name'], '.' . $this->ntbr->type_backup_complete . '.') !== false
                && !in_array($this->ntbr->type_backup_complete, $authorized_type)
            ) {
                unset($backup_files_to_upload[$key]);
            } elseif (stripos($b_to_up['name'], '.' . $this->ntbr->type_backup_file . '.') !== false
                && !in_array($this->ntbr->type_backup_file, $authorized_type)
            ) {
                unset($backup_files_to_upload[$key]);
            } elseif (stripos($b_to_up['name'], '.' . $this->ntbr->type_backup_base . '.') !== false
                && !in_array($this->ntbr->type_backup_base, $authorized_type)
            ) {
                unset($backup_files_to_upload[$key]);
            }
        }

        $download_files_links = $this->ntbr->generateUrls(true);

        $this->ntbr->writeCronFiles(false);

        if (!$light) {
            $p_id = '20130';
        } else {
            $p_id = '45979';
        }

        $ads_url = 'https://addons.prestashop.com/';
        $ads_url_fr = $ads_url . 'fr/';
        $ads_url_en = $ads_url . 'en/';
        $ads_var = '?id_product=' . $p_id;

        if ($this->context->language->iso_code == 'fr') {
            $link_contact = $ads_url_fr . 'ecrire-au-developpeur' . $ads_var;
            $link_full_version = $ads_url_fr . 'migration-donnees-sauvegarde/' . $p_id . '-nt-sauvegarde-et-restaure.html';
            $link_rate = NtBackupAndRestore::RATE_LINK_FR;
        } else {
            $link_contact = $ads_url_en . 'write-to-developper' . $ads_var;
            $link_full_version = $ads_url_en . 'data-migration-backup/' . $p_id . '-nt-backup-and-restore.html';
            $link_rate = NtBackupAndRestore::RATE_LINK;
        }

        if ($is_presta_edition) {
            $link_contact = 'https://help-center.prestashop.com/fr/contact?utm_source=back-office&utm_medium=ntbackupandrestore';
        }

        $our_modules = [
            [
                'link' => ($this->context->language->iso_code == 'fr') ? NtBackupAndRestore::STATS_LINK_FR : NtBackupAndRestore::STATS_LINK,
                'name' => 'NT ' . $this->l('Statistics', self::PAGE),
                'desc' => $this->l('Displays statistics for your store', self::PAGE),
                'logo' => 'logo_ntstat.png',
            ],
            [
                'link' => ($this->context->language->iso_code == 'fr') ? NtBackupAndRestore::GEOLOC_LINK_FR : NtBackupAndRestore::GEOLOC_LINK,
                'name' => 'NT ' . $this->l('Geolocation', self::PAGE),
                'desc' => $this->l('Precisely geolocate on a map the postal addresses of customers, stores, manufacturers, suppliers and warehouses', self::PAGE),
                'logo' => 'logo_ntgeoloc.png',
            ],
            [
                'link' => ($this->context->language->iso_code == 'fr') ? NtBackupAndRestore::REDUC_LINK_FR : NtBackupAndRestore::REDUC_LINK,
                'name' => 'NT ' . $this->l('Reduction', self::PAGE),
                'desc' => $this->l('Easy and fast massive discount', self::PAGE),
                'logo' => 'logo_ntreduc.png',
            ],
            [
                'link' => ($this->context->language->iso_code == 'fr') ? NtBackupAndRestore::DEB_LINK_FR : '',
                'name' => 'NT ' . $this->l('DEB', self::PAGE),
                'desc' => $this->l('Create a DEB file you can import on Prodouane', self::PAGE),
                'logo' => 'logo_ntdeb.png',
            ],
        ];

        $list_infos = Backups::getListBackupsInfos();
        $activate_2nt_automation = NtbrCore::checkValidIp();
        $big_website = 0;

        if (!GlobConfNtbr::get('NTBR_BIG_WEBSITE_HIDE')) {
            $big_website = (int) GlobConfNtbr::get('NTBR_BIG_WEBSITE');
        }

        $total_scan_root_size = ScanSizeNtbr::getTotalSize();
        $last_scan_date = Tools::displayDate(ScanSizeNtbr::getLastDate(), null, true);
        $last_scan_config = ConfigNtbr::getNameById(GlobConfNtbr::get('NTBR_SCAN_ID_CONFIG'));

        $module_path = $this->module->getPathUri();
        $version_script = '1.5';

        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $version_script = '1.7';
        } elseif (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
            $version_script = '1.6';
        }

        $low_interest_tables = $this->ntbr->getLowInterestTables();

        $list_datatable_tables = [];
        $show_tables = Db::getInstance()->executeS('
            SELECT TABLE_NAME, ROUND((DATA_LENGTH + INDEX_LENGTH)) AS size_byte
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = "' . bqSQL(_DB_NAME_) . '"
            ORDER BY size_byte DESC
        ');

        foreach ($show_tables as $show_table) {
            $table_name = trim($show_table['TABLE_NAME']);

            if (!in_array($table_name, $low_interest_tables)) {
                $list_datatable_tables[] = [
                    'name' => $table_name,
                    'separated_name' => ',' . $table_name . ',',
                    'size' => $this->ntbr->readableSize($show_table['size_byte']),
                ];
            }
        }

        foreach ($low_interest_tables as &$table) {
            $table = preg_replace('/^' . _DB_PREFIX_ . '/', '', $table); // remove prefix
        }

        // Check if there is a tar or tar.gz at the website root
        $list_tar_targz_root = glob(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . '*.{' . $uncompress . ',' . $compress . ',' . $uncompress_crypt . ',' . $compress_crypt . '}', GLOB_BRACE);

        foreach ($list_tar_targz_root as &$tar_targz) {
            $tar_targz = basename($tar_targz);
        }

        $googledrive_client_id = GlobConfNtbr::get('NTBR_GOOGLEDRIVE_CLIENT_ID');
        $googledrive_client_secret = GlobConfNtbr::get('NTBR_GOOGLEDRIVE_CLIENT_SECRET');

        if ($googledrive_client_id && trim($googledrive_client_id) != '') {
            $googledrive_client_id = Apparatus::decrypt($googledrive_client_id);

            if (!is_string($googledrive_client_id)) {
                $googledrive_client_id = '';
            }
        } else {
            $googledrive_client_id = '';
        }

        if ($googledrive_client_secret && trim($googledrive_client_secret) != '') {
            $googledrive_client_secret = Apparatus::decrypt($googledrive_client_secret);

            if (!is_string($googledrive_client_secret)) {
                $googledrive_client_secret = '';
            }
        } else {
            $googledrive_client_secret = '';
        }

        $this->tpl_view_vars = [
            'light' => $light,
            'link_full_version' => $link_full_version,
            'url_ajax' => $url_ajax,
            'create_auto_cron_php' => $this->ntbr->normalizePath($physic_path_modules) . $this->ntbr->name . '/ajax/php_cron',
            'secure_key' => $this->ntbr->secure_key,
            'backup_progress' => $url_ajax . '/backup_progress.php?' . $param_secure_key,
            'backup_stop' => $url_ajax . '/backup_stop.php?' . $param_secure_key,
            'link_restore_file' => $shop_url . NtbrCore::NEW_RESTORE_NAME,
            'restore_lastlog' => $shop_url . 'lastlog.txt',
            'download_file' => $download_files_links['link'],
            'backup_files' => $backup_files,
            'backup_files_to_upload' => $backup_files_to_upload,
            'changelog' => $changelog,
            'documentation' => $documentation,
            'documentation_name' => $documentation_name,
            'googledrive_app' => $googledrive_app,
            'googledrive_app_name' => $googledrive_app_name,
            'display_translate_tab' => $display_translate_tab,
            'multi_config' => $multi_config,
            'automation_2nt' => GlobConfNtbr::get('NTBR_AUTOMATION_2NT'),
            'automation_2nt_hours' => GlobConfNtbr::get('NTBR_AUTOMATION_2NT_HOURS'),
            'automation_2nt_minutes' => GlobConfNtbr::get('NTBR_AUTOMATION_2NT_MINUTES'),
            'automation_2nt_ip' => GlobConfNtbr::get('NTBR_AUTOMATION_2NT_IP'),
            'activate_2nt_automation' => $activate_2nt_automation,
            'ftp_port_default' => $ftp_port_default,
            'ftp_directory_default' => $ftp_directory_default,
            'xsendfile_detected' => Tools::apacheModExists('xsendfile'),
            'sodium_detected' => extension_loaded('sodium'),
            'id_shop_group' => $this->id_shop_group,
            'id_shop' => $this->id_shop,
            'version' => $this->ntbr->version . $type_module,
            'available_version' => $available_version,
            'yandex_authorizeUrl' => $yandex_authorizeUrl,
            'googledrive_authorizeUrl' => $googledrive_authorizeUrl,
            'googlecloud_authorizeUrl' => $googlecloud_authorizeUrl,
            'pcloud_authorizeUrl' => $pcloud_authorizeUrl,
            'box_authorizeUrl' => $box_authorizeUrl,
            'ajax_loader' => $ajax_loader,
            'link_contact' => $link_contact,
            'link_rate' => $link_rate,
            'our_modules' => $our_modules,
            'module_address_use' => $module_address_use,
            'module_address_config' => $module_address_config,
            'fct_crypt_exists' => $fct_crypt_exists,
            'os_windows' => $os_windows,
            'curl_exists' => $curl_exists,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'min_memory_limit' => NtbrCore::SET_MEMORY_LIMIT,
            'min_time_new_backup' => NtbrCore::MIN_TIME_NEW_BACKUP,
            'max_time_before_refresh' => NtbrCore::MAX_TIME_BEFORE_REFRESH,
            'max_time_before_progress_refresh' => NtbrCore::MAX_TIME_BEFORE_PROGRESS_REFRESH,
            'time_before_warning_timeout' => NtbrCore::TIME_BEFORE_WARNING_TIMEOUT,
            'big_website' => $big_website,
            'ftp_default' => $ftp_default,
            'dropbox_default' => $dropbox_default,
            'yandex_default' => $yandex_default,
            'googledrive_default' => $googledrive_default,
            'googlecloud_default' => $googlecloud_default,
            'pcloud_default' => $pcloud_default,
            'box_default' => $box_default,
            'onedrive_default' => $onedrive_default,
            'sugarsync_default' => $sugarsync_default,
            'owncloud_default' => $owncloud_default,
            'shadow_drive_default' => $shadow_drive_default,
            'webdav_default' => $webdav_default,
            'aws_default' => $aws_default,
            'list_infos' => $list_infos,
            'backup_type_complete' => $this->ntbr->type_backup_complete,
            'backup_type_file' => $this->ntbr->type_backup_file,
            'backup_type_base' => $this->ntbr->type_backup_base,
            'restore_backup_files_complete' => $restore_backup_files_complete,
            'restore_backup_files_file' => $restore_backup_files_file,
            'restore_backup_files_base' => $restore_backup_files_base,
            'restore_backup_finish' => Tools::substr($this->ntbr->l('FINISH', self::PAGE), 0, 5),
            'restore_backup_error' => Tools::substr($this->ntbr->l('Error', self::PAGE), 0, 5),
            'list_config' => $list_config,
            'activate_log' => $default_config->activate_log,
            'id_current_config' => $default_config->id,
            'current_hour' => date('H:i:s'),
            'running_backup' => $running_backup,
            'fake_mdp' => NtbrChild::FAKE_MDP,
            'time_zone' => date_default_timezone_get(),
            'max_file_download_size' => NtbrChild::MAX_FILE_DOWNLOAD_SIZE,
            'default_backup_dir' => $default_backup_dir,
            'ntbr_dropbox_name' => NtbrChild::DROPBOX,
            'ntbr_yandex_name' => NtbrChild::YANDEX,
            'ntbr_owncloud_name' => NtbrChild::OWNCLOUD,
            'ntbr_shadow_drive_name' => NtbrChild::SHADOW_DRIVE,
            'ntbr_webdav_name' => NtbrChild::WEBDAV,
            'ntbr_googledrive_name' => NtbrChild::GOOGLEDRIVE,
            'ntbr_googlecloud_name' => NtbrChild::GOOGLECLOUD,
            'ntbr_pcloud_name' => NtbrChild::PCLOUD,
            'ntbr_box_name' => NtbrChild::BOX,
            'ntbr_onedrive_name' => NtbrChild::ONEDRIVE,
            'ntbr_ftp_sftp_name' => NtbrChild::FTP_SFTP,
            'ntbr_ftp_name' => NtbrChild::FTP,
            'ntbr_sftp_name' => NtbrChild::SFTP,
            'ntbr_s3_name' => NtbrChild::AWS,
            'ntbr_sugarsync_name' => NtbrChild::SUGARSYNC,
            'default_dump_max_values' => NtbrChild::DUMP_MAX_VALUES,
            'default_dump_lines_limit' => NtbrChild::DUMP_LINES_LIMIT,
            'scan_files_enable' => $scan_files_enable,
            'scan_root_size' => $total_scan_root_size,
            'scan_root_readable_size' => $this->ntbr->readableSize($total_scan_root_size),
            'last_scan_date' => $last_scan_date,
            'last_scan_config' => $last_scan_config,
            's3_type_aws' => NtbrChild::S3_TYPE_AWS,
            's3_type_minio' => NtbrChild::S3_TYPE_MINIO,
            's3_type_vultr' => NtbrChild::S3_TYPE_VULTR,
            's3_type_wasabi' => NtbrChild::S3_TYPE_WASABI,
            's3_type_scaleway' => NtbrChild::S3_TYPE_SCALEWAY,
            's3_type_backblaze' => NtbrChild::S3_TYPE_BACKBLAZE,
            's3_type_other' => NtbrChild::S3_TYPE_OTHER,
            's3_aws_host' => NtbrChild::S3_AWS_HOST,
            's3_scaleway_host' => NtbrChild::S3_SCALEWAY_HOST,
            's3_backblaze_host' => NtbrChild::S3_BACKBLAZE_HOST,
            'js_file_1' => $module_path . 'views/js/script_' . $version_script . '.js?' . $this->module->version,
            'js_file_2' => $module_path . 'views/js/script.js?' . $this->module->version,
            'css_file_1' => $module_path . 'views/css/style_' . $version_script . '.css?' . $this->module->version,
            'css_file_2' => $module_path . 'views/css/style.css?' . $this->module->version,
            'low_interest_tables' => implode(', ', $low_interest_tables),
            'shop_url_changed' => $shop_url_changed,
            'product_img_and_file' => NtbrCore::PRODUCT_IMG_AND_FILE,
            'product_img_none' => NtbrCore::PRODUCT_IMG_NONE,
            'product_img_only' => NtbrCore::PRODUCT_IMG_ONLY,
            'list_datatable_tables' => $list_datatable_tables,
            'list_tar_targz_root' => $list_tar_targz_root,
            'pcloud_location_id_us' => PcloudLib::LOCATION_ID_US,
            'pcloud_location_id_eu' => PcloudLib::LOCATION_ID_EU,
            'is_presta_edition' => $is_presta_edition,
            'googledrive_client_id' => $googledrive_client_id,
            'googledrive_client_secret' => $googledrive_client_secret,
        ];
        // d($list_config);
        return parent::renderView();
    }

    public function displayFtpAccount()
    {
        $id_ntbr_ftp = (int) Tools::getValue('id_ntbr_ftp');
        $ftp_account = $this->ntbr->displayFtpAccount($id_ntbr_ftp);
        $ftp_account['password'] = NtbrChild::FAKE_MDP;

        exit(json_encode(['ftp_account' => $ftp_account]));
    }

    public function displayDropboxAccount()
    {
        $id_ntbr_dropbox = (int) Tools::getValue('id_ntbr_dropbox');
        $dropbox_account = $this->ntbr->displayDropboxAccount($id_ntbr_dropbox);
        $dropbox_account['token'] = NtbrChild::FAKE_MDP;

        exit(json_encode(['dropbox_account' => $dropbox_account]));
    }

    public function displayYandexAccount()
    {
        $id_ntbr_yandex = (int) Tools::getValue('id_ntbr_yandex');
        $yandex_account = $this->ntbr->displayYandexAccount($id_ntbr_yandex);
        $yandex_account['token'] = NtbrChild::FAKE_MDP;

        exit(json_encode(['yandex_account' => $yandex_account]));
    }

    public function displayOwncloudAccount()
    {
        $id_ntbr_owncloud = (int) Tools::getValue('id_ntbr_owncloud');
        $owncloud_account = $this->ntbr->displayOwncloudAccount($id_ntbr_owncloud);
        $owncloud_account['password'] = NtbrChild::FAKE_MDP;

        exit(json_encode(['owncloud_account' => $owncloud_account]));
    }

    public function displayShadowDriveAccount()
    {
        $id_ntbr_shadow_drive = (int) Tools::getValue('id_ntbr_shadow_drive');
        $shadow_drive_account = $this->ntbr->displayShadowDriveAccount($id_ntbr_shadow_drive);
        $shadow_drive_account['password'] = NtbrChild::FAKE_MDP;

        exit(json_encode(['shadow_drive_account' => $shadow_drive_account]));
    }

    public function displayWebdavAccount()
    {
        $id_ntbr_webdav = (int) Tools::getValue('id_ntbr_webdav');
        $webdav_account = $this->ntbr->displayWebdavAccount($id_ntbr_webdav);
        $webdav_account['password'] = NtbrChild::FAKE_MDP;

        exit(json_encode(['webdav_account' => $webdav_account]));
    }

    public function displayGoogledriveAccount()
    {
        $id_ntbr_googledrive = (int) Tools::getValue('id_ntbr_googledrive');
        $googledrive_account = $this->ntbr->displayGoogledriveAccount($id_ntbr_googledrive);
        $googledrive_account['token'] = NtbrChild::FAKE_MDP;

        exit(json_encode(['googledrive_account' => $googledrive_account]));
    }

    public function displayGooglecloudAccount()
    {
        $id_ntbr_googlecloud = (int) Tools::getValue('id_ntbr_googlecloud');
        $googlecloud_account = $this->ntbr->displayGooglecloudAccount($id_ntbr_googlecloud);
        $googlecloud_account['token'] = NtbrChild::FAKE_MDP;

        exit(json_encode(['googlecloud_account' => $googlecloud_account]));
    }

    public function displayPcloudAccount()
    {
        $id_ntbr_pcloud = (int) Tools::getValue('id_ntbr_pcloud');
        $pcloud_account = $this->ntbr->displayPcloudAccount($id_ntbr_pcloud);
        $pcloud_account['token'] = NtbrChild::FAKE_MDP;

        exit(json_encode(['pcloud_account' => $pcloud_account]));
    }

    public function displayBoxAccount()
    {
        $id_ntbr_box = (int) Tools::getValue('id_ntbr_box');
        $box_account = $this->ntbr->displayBoxAccount($id_ntbr_box);
        $box_account['token'] = NtbrChild::FAKE_MDP;

        exit(json_encode(['box_account' => $box_account]));
    }

    public function displayOnedriveAccount()
    {
        $id_ntbr_onedrive = (int) Tools::getValue('id_ntbr_onedrive');
        $onedrive_account = $this->ntbr->displayOnedriveAccount($id_ntbr_onedrive);
        $onedrive_account['token'] = NtbrChild::FAKE_MDP;

        exit(json_encode(['onedrive_account' => $onedrive_account]));
    }

    public function displaySugarsyncAccount()
    {
        $id_ntbr_sugarsync = (int) Tools::getValue('id_ntbr_sugarsync');
        $sugarsync_account = $this->ntbr->displaySugarsyncAccount($id_ntbr_sugarsync);
        // $sugarsync_account['token'] = NtbrChild::FAKE_MDP;

        exit(json_encode(['sugarsync_account' => $sugarsync_account]));
    }

    public function displayAwsAccount()
    {
        $id_ntbr_aws = (int) Tools::getValue('id_ntbr_aws');
        $aws_account = $this->ntbr->displayAwsAccount($id_ntbr_aws);
        $aws_account['acces_key_id'] = NtbrChild::FAKE_MDP;
        $aws_account['secret_access_key'] = NtbrChild::FAKE_MDP;

        exit(json_encode(['aws_account' => $aws_account]));
    }

    public function generateUrls()
    {
        $urls = $this->ntbr->generateSecureUrls($this->id_shop_group, $this->id_shop);

        exit(json_encode(['urls' => $urls]));
    }

    public function logMsg()
    {
        $msg = Tools::getValue('msg');

        $this->ntbr->log($msg, true);

        exit(json_encode([]));
    }

    public function getBackupDownloadData()
    {
        $nb = Tools::getValue('nb');
        $backup = $this->ntbr->findThisBackup($nb);

        if (count($backup) > 1) {
            $file_name = $backup[$nb]['name'];
            $file_size = $backup[$nb]['size_byte'];
            $backup_dir = $backup[$nb]['backup_dir'];
        } else {
            $file_name = $backup[$nb . '.1']['name'];
            $file_size = $backup[$nb . '.1']['size_byte'];
            $backup_dir = $backup[$nb . '.1']['backup_dir'];
        }

        if (!$file_size) {
            $file_path = $backup_dir . $file_name;
            $file_size = $this->ntbr->getFileSize($file_path);

            if (!$file_size) {
                $this->ntbr->log($this->ntbr->l('Error, the backup size cannot be found', self::PAGE), true);
                exit(json_encode(['result' => 0]));
            }
        }

        exit(json_encode([
            'result' => 1,
            'file_name' => $file_name,
            'file_size' => $file_size,
            'backup_dir' => $this->ntbr->normalizePath($backup_dir),
        ]));
    }

    public function downloadBackup()
    {
        $pos = Tools::getValue('pos');
        $file_size = Tools::getValue('file_size');

        $infos = [
            'file_name' => Tools::getValue('file_name'),
            'backup_dir' => Tools::getValue('backup_dir'),
            'content' => '',
            'pos' => $pos,
            'file_size' => $file_size,
            'finish' => 0,
            'progress' => '',
        ];

        $file_path = $infos['backup_dir'] . $infos['file_name'];

        $size_to_read = $infos['file_size'] - $infos['pos'];

        if ($size_to_read > NtbrChild::MAX_FILE_DOWNLOAD_SIZE) {
            $size_to_read = NtbrChild::MAX_FILE_DOWNLOAD_SIZE;
        }

        $infos['content'] = $this->ntbr->getContentFromFile($file_path, $infos['pos'], $size_to_read, false);

        if ($infos['content'] === false) {
            exit(json_encode(['result' => 0]));
        }

        $infos['pos'] += $size_to_read;

        $infos['progress'] = $this->ntbr->l('Download backup:', self::PAGE) . ' '
            . $this->ntbr->readableSize($infos['pos']) . '/' . $this->ntbr->readableSize($infos['file_size']);

        if ($infos['pos'] >= $infos['file_size']) {
            $infos['finish'] = 1;
            $this->ntbr->log(
                $this->l('File downloaded:', self::PAGE) . ' ' . $infos['file_name']
                . ' (' . $infos['pos'] . '/' . $infos['file_size'] . ')',
                true
            );
        }

        exit($infos['content']);
        // die(json_encode(array('result' => 1, 'infos' => $infos)));
    }

    public function saveConfigProfile()
    {
        $is_default = (int) (bool) Tools::getValue('is_default');
        $name = Tools::getValue('name');
        $type = Tools::getValue('type');

        $result = $this->ntbr->saveConfigProfile($is_default, $name, $type);

        exit(json_encode(['result' => $result]));
    }

    public function deleteConfig()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $result = $this->ntbr->deleteConfig($id_ntbr_config);

        exit(json_encode(['result' => $result]));
    }

    public function getTimeBetweenRefresh()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $config = new ConfigNtbr($id_ntbr_config);

        exit(json_encode(['time_between_refresh' => $config->time_between_refresh]));
    }

    public function saveConfig()
    {
        $type_module = $this->ntbr->getTypeModule();
        $light = ($type_module == '') ? 0 : 1;

        $result = [
            'success' => true,
            'errors' => [],
        ];

        $values = Apparatus::getAllValues();

        $id_ntbr_config = (int) (isset($values['id_ntbr_config']) ? $values['id_ntbr_config'] : 0);
        $time_between_refresh = (int) (isset($values['time_between_refresh']) ? $values['time_between_refresh'] : 0);
        $time_pause_between_refresh = (int) (isset($values['time_pause_between_refresh']) ? $values['time_pause_between_refresh'] : 0);
        $time_between_progress_refresh = (int) (isset($values['time_between_progress_refresh']) ? $values['time_between_progress_refresh'] : 0);
        $is_default = (int) (bool) (isset($values['is_default']) ? $values['is_default'] : false);
        $disable = (int) (bool) (isset($values['disable']) ? $values['disable'] : false);
        $name_config = (isset($values['name']) ? $values['name'] : '');
        $mail_backup = (isset($values['mail_backup']) ? $values['mail_backup'] : '');
        $mail_version = (isset($values['mail_version']) ? $values['mail_version'] : '');

        if ($time_pause_between_refresh) {
            if ($time_pause_between_refresh >= $time_between_refresh) {
                $result['errors'][] = $this->ntbr->l('The duration of the pause between two intermediate renewal must be inferior to the intermedial renewal value.', self::PAGE);
            }
        }

        if (!isset($name_config) || !$name_config) {
            $result['errors'][] = $this->ntbr->l('The name of the configuration is required.', self::PAGE);
        }

        $id_default_config = ConfigNtbr::getIdDefault();

        // If this config is the default one, we need to choose a
        // new default config before removing default for this one
        if ($id_default_config == $id_ntbr_config && !$is_default) {
            $result['errors'][] = $this->ntbr->l('At least one configuration must be the default one', self::PAGE);
        }

        // The default config cannot be disabled
        if ($is_default && $disable) {
            $result['errors'][] = $this->ntbr->l('The default configuration cannot be disabled', self::PAGE);
        }

        if (count($result['errors'])) {
            $result['success'] = false;
            exit(json_encode(['result' => $result]));
        }

        if (!$mail_backup) {
            $mail_backup = Configuration::get('PS_SHOP_EMAIL');
        }

        if (!$mail_version) {
            $mail_version = Configuration::get('PS_SHOP_EMAIL');
        }

        $config = new ConfigNtbr($id_ntbr_config);
        $old_receive_email_version = $config->receive_email_version;
        $old_mail_version = $config->mail_version;
        $old_backup_dir = $config->backup_dir;
        $old_sodium_key = $config->sodium_key;

        if ($config->type_backup == $this->ntbr->type_backup_base) {
            $values['scan_files'] = false;
        }

        $config->is_default = $is_default;
        $config->disable = $disable;
        $config->name = $name_config;
        $config->activate_log = (int) (bool) (isset($values['activate_log']) ? $values['activate_log'] : false);
        $config->nb_backup = (int) (isset($values['nb_backup']) ? $values['nb_backup'] : 0);
        $config->mail_backup = $mail_backup;
        $config->mail_version = $mail_version;
        $config->dump_low_interest_tables = (int) (bool) (isset($values['dump_low_interest_table']) ? $values['dump_low_interest_table'] : false);
        $config->disable_refresh = (int) (bool) (isset($values['disable_refresh']) ? $values['disable_refresh'] : false);
        $config->increase_server_timeout = (int) (bool) (isset($values['increase_server_timeout']) ? $values['increase_server_timeout'] : false);
        $config->increase_server_memory = (int) (bool) (isset($values['increase_server_memory']) ? $values['increase_server_memory'] : false);
        $config->js_download = (int) (bool) (isset($values['js_download']) ? $values['js_download'] : false);
        $config->disable_curl_file_size = (int) (bool) (isset($values['disable_curl_file_size']) ? $values['disable_curl_file_size'] : false);
        $config->send_email = (int) (bool) (isset($values['send_email']) ? $values['send_email'] : false);
        $config->receive_email_version = (int) (bool) (isset($values['receive_email_version']) ? $values['receive_email_version'] : false);
        $config->email_only_error = (int) (bool) (isset($values['email_only_error']) ? $values['email_only_error'] : false);
        $config->maintenance = (int) (bool) (isset($values['maintenance']) ? $values['maintenance'] : false);
        $config->part_size = (int) (isset($values['part_size']) ? $values['part_size'] : 0);
        $config->max_file_to_backup = (int) (isset($values['max_file_to_backup']) ? $values['max_file_to_backup'] : 0);
        $config->dump_max_values = (int) (isset($values['dump_max_values']) ? $values['dump_max_values'] : 0);
        $config->dump_lines_limit = (int) (isset($values['dump_lines_limit']) ? $values['dump_lines_limit'] : 0);
        $config->time_between_backups = (int) (isset($values['time_between_backups']) ? $values['time_between_backups'] : 0);
        $config->server_timeout_value = (int) (isset($values['server_timeout_value']) ? $values['server_timeout_value'] : NtbrCore::SET_TIME_LIMIT);
        $config->server_memory_value = (int) (isset($values['server_memory_value']) ? $values['server_memory_value'] : NtbrCore::SET_MEMORY_LIMIT);
        $config->time_between_refresh = (int) $time_between_refresh;
        $config->time_pause_between_refresh = (int) $time_pause_between_refresh;
        $config->time_between_progress_refresh = ($time_between_progress_refresh) ? $time_between_progress_refresh : 1;
        $config->ignore_compression = (int) (bool) (isset($values['ignore_compression']) ? $values['ignore_compression'] : false);
        $config->scan_files = (int) (bool) (isset($values['scan_files']) ? $values['scan_files'] : false);

        if (!$light) {
            $multi_config = (int) (bool) (isset($values['multi_config']) ? $values['multi_config'] : false);
            $send_restore = (int) (bool) (isset($values['send_restore']) ? $values['send_restore'] : false);
            $activate_xsendfile = (int) (bool) (isset($values['activate_xsendfile']) ? $values['activate_xsendfile'] : false);
            $delete_local_backup = (int) (bool) (isset($values['delete_local_backup']) ? $values['delete_local_backup'] : false);
            $create_on_distant = (int) (bool) (isset($values['create_on_distant']) ? $values['create_on_distant'] : false);
            $crypt_backup = (int) (bool) (isset($values['crypt_backup']) ? $values['crypt_backup'] : false);
            $ignore_product_image = (int) (isset($values['ignore_product_image']) ? $values['ignore_product_image'] : NtbrChild::PRODUCT_IMG_AND_FILE);
            $only_origin_img = (int) (bool) (isset($values['only_origin_img']) ? $values['only_origin_img'] : false);
            $backup_dir = (isset($values['backup_dir']) ? $values['backup_dir'] : '');
            $ignore_directories = (isset($values['ignore_directories']) ? $values['ignore_directories'] : '');
            $ignore_file_types = (isset($values['ignore_files_types']) ? $values['ignore_files_types'] : '');
            $ignore_tables = (isset($values['ignore_tables']) ? $values['ignore_tables'] : '');
            $not_recreate_tables = (isset($values['not_recreate_tables']) ? $values['not_recreate_tables'] : '');

            $config = $this->ntbr->saveConfig(
                $config,
                $send_restore,
                $activate_xsendfile,
                $ignore_product_image,
                $only_origin_img,
                $delete_local_backup,
                $create_on_distant,
                $backup_dir,
                $ignore_directories,
                $ignore_file_types,
                $ignore_tables,
                $not_recreate_tables,
                $crypt_backup,
                $multi_config
            );

            if (!$config) {
                $result['success'] = false;
                $result['errors'][] = $this->ntbr->l('The full config could not be saved', self::PAGE);
            }

            if (!is_dir($config->backup_dir)) {
                $result['success'] = false;
                $result['errors'][] = sprintf(
                    $this->ntbr->l('The backup directory "%s" does not exists', self::PAGE),
                    $config->backup_dir
                );
            } else {
                // Backups should not be keep at the root of the shop
                $physic_path_root = Apparatus::getRealPath(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR);

                if ($config->backup_dir == $physic_path_root || $config->backup_dir == $physic_path_root . DIRECTORY_SEPARATOR) {
                    $result['success'] = false;
                    $result['errors'][] = sprintf(
                        $this->ntbr->l('The backup directory "%s" is not an authorized backup directory. Please choose an other one.', self::PAGE),
                        $config->backup_dir
                    );
                }
            }

            if ($config->crypt_backup && !$config->sodium_key) {
                $result['success'] = false;
                $result['errors'][] = $this->ntbr->l('The backup sodium key could not be created', self::PAGE);
            } elseif ($config->crypt_backup /* && !$old_sodium_key */) {
                $result['sodium_key'] = $this->ntbr->decrypt($config->sodium_key);
            }
        }

        if ($result['success']) {
            if (!$config->save()) {
                $result['success'] = false;
            }
        }

        if (!$result['success']) {
            $result['errors'][] = $this->ntbr->l('Error during the saving of your configuration.', self::PAGE);
        } else {
            if ($old_backup_dir != $config->backup_dir) {
                $old_backups = $this->ntbr->findOldBackups($config->id);

                if (isset($old_backups['parts']) && count($old_backups['parts'])) {
                    foreach ($old_backups['parts'] as $backup_file) {
                        if (!copy($old_backup_dir . $backup_file['name'], $config->backup_dir . $backup_file['name'])) {
                            $result['success'] = false;
                            $result['errors'][] = sprintf(
                                $this->ntbr->l('The backup %s could not be move to the new directory', self::PAGE),
                                $backup_file['name']
                            );
                        }
                    }
                }
            }

            // If the config change, upd in NtVersion
            if ($old_receive_email_version != $config->receive_email_version || $old_mail_version != $config->mail_version) {
                $shop_domain = Tools::getCurrentUrlProtocolPrefix() . Tools::getHttpHost();
                $shop_url = $shop_domain . __PS_BASE_URI__;
                $context = Context::getContext();
                $code_lang = str_replace('-', '_', Tools::strtolower($context->language->locale));

                $url = CONFIGURE_NTVERSION . '&email=' . urlencode((string) $config->mail_version) . '&site=' . urlencode((string) $shop_url) . '&code_lang=' . urlencode((string) $code_lang);

                if ($config->receive_email_version) {
                    // Save email in NtVersion
                    $url .= '&activate=1';
                } else {
                    // Delete email in NtVersion
                    $url .= '&activate=0';
                }

                $ntversion_result = Tools::file_get_contents($url);
                $result['success'] = ($ntversion_result == 'OK');

                if (!$result['success']) {
                    if ($config->receive_email_version) {
                        // Email was not added
                        $result['errors'][] = $this->ntbr->l('The email could not be added to the emails to warn when there is a new version of the module', self::PAGE);
                        $config->receive_email_version = false;
                    } else {
                        // Email was not deleted
                        $result['errors'][] = $this->ntbr->l('The email could not be removed from the emails to warn when there is a new version of the module', self::PAGE);
                        $config->receive_email_version = true;
                    }

                    $config->save();
                }
            }
        }

        exit(json_encode(['result' => $result]));
    }

    public function updShopUrl()
    {
        $result = $this->ntbr->updShopUrl();

        exit(json_encode(['result' => $result]));
    }

    public function saveAutomation()
    {
        $automation_2nt_ip = (int) Tools::getValue('automation_2nt_ip');
        $result = false;
        $errors = [];
        $update_maintenance_ip = false;

        // If not in localhost (so automation can be activated)
        if (Tools::isSubmit('automation_2nt')
            && Tools::isSubmit('automation_2nt_hours')
            && Tools::isSubmit('automation_2nt_minutes')
        ) {
            $automation_2nt = (int) (bool) Tools::getValue('automation_2nt');
            $automation_2nt_hours = (int) Tools::getValue('automation_2nt_hours');
            $automation_2nt_minutes = (int) Tools::getValue('automation_2nt_minutes');

            // If something change
            if (GlobConfNtbr::get('NTBR_AUTOMATION_2NT') != $automation_2nt
                || GlobConfNtbr::get('NTBR_AUTOMATION_2NT_HOURS') != $automation_2nt_hours
                || GlobConfNtbr::get('NTBR_AUTOMATION_2NT_MINUTES') != $automation_2nt_minutes
            ) {
                $result = $this->ntbr->saveAutomation($automation_2nt, $automation_2nt_hours, $automation_2nt_minutes);

                if ($result && $automation_2nt) {
                    $update_maintenance_ip = true;
                }
            } else {
                $result = true;
            }
        } else {
            $result = true;
        }

        if (GlobConfNtbr::get('NTBR_AUTOMATION_2NT_IP') != $automation_2nt_ip) {
            GlobConfNtbr::set('NTBR_AUTOMATION_2NT_IP', $automation_2nt_ip);

            if (GlobConfNtbr::get('NTBR_AUTOMATION_2NT_IP') != $automation_2nt_ip) {
                $this->ntbr->log($this->ntbr->l('Automation IP was not saved correctly', self::PAGE), true);
                $result = false;
            }

            $update_maintenance_ip = true;
        }

        if (!$result) {
            $errors[] = $this->ntbr->l('Error during the saving of your automation.', self::PAGE);
        } elseif ($update_maintenance_ip) {
            // Update automation IP in maintenance
            $this->ntbr->setMaintenanceIP();
        }

        exit(json_encode(['result' => $result, 'errors' => $errors]));
    }

    public function saveFtp()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $id_ntbr_ftp = (int) Tools::getValue('id_ntbr_ftp');
        $name = Tools::getValue('name');
        $active = (int) (bool) Tools::getValue('active');
        $sftp = (int) (bool) Tools::getValue('sftp');
        $ssl = (int) (bool) Tools::getValue('ssl');
        $passive_mode = (int) (bool) Tools::getValue('passive_mode');
        $config_nb_backup = (int) Tools::getValue('config_nb_backup');
        $server = Tools::getValue('server');
        $login = Tools::getValue('login');
        $password = Tools::getValue('password');
        $port = (int) Tools::getValue('port');
        $directory = Tools::getValue('directory');

        $result = $this->ntbr->saveFtp(
            $id_ntbr_config,
            $id_ntbr_ftp,
            $name,
            $active,
            $sftp,
            $ssl,
            $passive_mode,
            $config_nb_backup,
            $server,
            $login,
            $password,
            $port,
            $directory
        );

        exit(json_encode(['result' => $result]));
    }

    public function saveDropbox()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $id_ntbr_dropbox = (int) Tools::getValue('id_ntbr_dropbox');
        $name = Tools::getValue('name');
        $active = (int) (bool) Tools::getValue('active');
        $business = (int) (bool) Tools::getValue('business');
        $config_nb_backup = (int) Tools::getValue('config_nb_backup');
        $code = Tools::getValue('code');
        $directory = Tools::getValue('directory');

        $result = $this->ntbr->saveDropbox(
            $id_ntbr_config,
            $id_ntbr_dropbox,
            $name,
            $active,
            $business,
            $config_nb_backup,
            $code,
            $directory
        );

        exit(json_encode(['result' => $result]));
    }

    public function saveYandex()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $id_ntbr_yandex = (int) Tools::getValue('id_ntbr_yandex');
        $name = Tools::getValue('name');
        $active = (int) (bool) Tools::getValue('active');
        $config_nb_backup = (int) Tools::getValue('config_nb_backup');
        $code = Tools::getValue('code');
        $directory = Tools::getValue('directory');

        $result = $this->ntbr->saveYandex(
            $id_ntbr_config,
            $id_ntbr_yandex,
            $name,
            $active,
            $config_nb_backup,
            $code,
            $directory
        );

        exit(json_encode(['result' => $result]));
    }

    public function saveOwncloud()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $id_ntbr_owncloud = (int) Tools::getValue('id_ntbr_owncloud');
        $name = Tools::getValue('name');
        $active = (int) (bool) Tools::getValue('active');
        $config_nb_backup = (int) Tools::getValue('config_nb_backup');
        $login = Tools::getValue('login');
        $password = Tools::getValue('password');
        $server = Tools::getValue('server');
        $directory = Tools::getValue('directory');

        $result = $this->ntbr->saveOwncloud(
            $id_ntbr_config,
            $id_ntbr_owncloud,
            $name,
            $active,
            $config_nb_backup,
            $login,
            $password,
            $server,
            $directory
        );

        exit(json_encode(['result' => $result]));
    }

    public function saveShadowDrive()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $id_ntbr_shadow_drive = (int) Tools::getValue('id_ntbr_shadow_drive');
        $name = Tools::getValue('name');
        $active = (int) (bool) Tools::getValue('active');
        $config_nb_backup = (int) Tools::getValue('config_nb_backup');
        $login = Tools::getValue('login');
        $password = Tools::getValue('password');
        $server = Tools::getValue('server');
        $directory = Tools::getValue('directory');

        $result = $this->ntbr->saveShadowDrive(
            $id_ntbr_config,
            $id_ntbr_shadow_drive,
            $name,
            $active,
            $config_nb_backup,
            $login,
            $password,
            $server,
            $directory
        );

        exit(json_encode(['result' => $result]));
    }

    public function saveWebdav()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $id_ntbr_webdav = (int) Tools::getValue('id_ntbr_webdav');
        $name = Tools::getValue('name');
        $active = (int) (bool) Tools::getValue('active');
        $config_nb_backup = (int) Tools::getValue('config_nb_backup');
        $login = Tools::getValue('login');
        $password = Tools::getValue('password');
        $server = Tools::getValue('server');
        $directory = Tools::getValue('directory');

        $result = $this->ntbr->saveWebdav(
            $id_ntbr_config,
            $id_ntbr_webdav,
            $name,
            $active,
            $config_nb_backup,
            $login,
            $password,
            $server,
            $directory
        );

        exit(json_encode(['result' => $result]));
    }

    public function saveGoogledriveApi()
    {
        $result = [
            'success' => true,
            'errors' => [],
        ];

        $googledrive_client_id = Tools::getValue('googledrive_client_id');
        $googledrive_client_secret = Tools::getValue('googledrive_client_secret');

        if (trim($googledrive_client_id) != '') {
            $googledrive_client_id = Apparatus::encrypt($googledrive_client_id);
        }

        if (trim($googledrive_client_secret) != '') {
            $googledrive_client_secret = Apparatus::encrypt($googledrive_client_secret);
        }

        if (!GlobConfNtbr::set('NTBR_GOOGLEDRIVE_CLIENT_ID', $googledrive_client_id)) {
            $result['errors'][] = $this->ntbr->l('The Google Drive client ID could not be saved', self::PAGE);
        }

        if (!GlobConfNtbr::set('NTBR_GOOGLEDRIVE_CLIENT_SECRET', $googledrive_client_secret)) {
            $result['errors'][] = $this->ntbr->l('The Google Drive client secret could not be saved', self::PAGE);
        }

        if (count($result['errors'])) {
            $result['success'] = false;
        }

        exit(json_encode(['result' => $result]));
    }

    public function saveGoogledrive()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $id_ntbr_googledrive = (int) Tools::getValue('id_ntbr_googledrive');
        $name = Tools::getValue('name');
        $active = (int) (bool) Tools::getValue('active');
        $config_nb_backup = (int) Tools::getValue('config_nb_backup');
        $code = Tools::getValue('code');
        $directory_path = Tools::getValue('directory_path');
        $directory_key = Tools::getValue('directory_key');

        $result = $this->ntbr->saveGoogledrive(
            $id_ntbr_config,
            $id_ntbr_googledrive,
            $name,
            $active,
            $config_nb_backup,
            $code,
            $directory_path,
            $directory_key
        );

        exit(json_encode(['result' => $result]));
    }

    public function saveGooglecloud()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $id_ntbr_googlecloud = (int) Tools::getValue('id_ntbr_googlecloud');
        $name = Tools::getValue('name');
        $active = (int) (bool) Tools::getValue('active');
        $config_nb_backup = (int) Tools::getValue('config_nb_backup');
        $code = Tools::getValue('code');
        $bucket = Tools::getValue('bucket');
        $directory = Tools::getValue('directory');

        $result = $this->ntbr->saveGooglecloud(
            $id_ntbr_config,
            $id_ntbr_googlecloud,
            $name,
            $active,
            $config_nb_backup,
            $code,
            $bucket,
            $directory
        );

        exit(json_encode(['result' => $result]));
    }

    public function savePcloud()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $id_ntbr_pcloud = (int) Tools::getValue('id_ntbr_pcloud');
        $name = Tools::getValue('name');
        $active = (int) (bool) Tools::getValue('active');
        $config_nb_backup = (int) Tools::getValue('config_nb_backup');
        $code = Tools::getValue('code');
        $location_id = Tools::getValue('location_id');
        $directory_id = Tools::getValue('directory_id');
        $directory_path = Tools::getValue('directory_path');

        $result = $this->ntbr->savePcloud(
            $id_ntbr_config,
            $id_ntbr_pcloud,
            $name,
            $active,
            $config_nb_backup,
            $code,
            $location_id,
            $directory_id,
            $directory_path
        );

        exit(json_encode(['result' => $result]));
    }

    public function saveBox()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $id_ntbr_box = (int) Tools::getValue('id_ntbr_box');
        $name = Tools::getValue('name');
        $active = (int) (bool) Tools::getValue('active');
        $config_nb_backup = (int) Tools::getValue('config_nb_backup');
        $code = Tools::getValue('code');
        $directory_path = Tools::getValue('directory_path');
        $directory_key = Tools::getValue('directory_key');

        $result = $this->ntbr->saveBox(
            $id_ntbr_config,
            $id_ntbr_box,
            $name,
            $active,
            $config_nb_backup,
            $code,
            $directory_path,
            $directory_key
        );

        exit(json_encode(['result' => $result]));
    }

    public function saveOnedrive()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $id_ntbr_onedrive = (int) Tools::getValue('id_ntbr_onedrive');
        $name = Tools::getValue('name');
        $active = (int) (bool) Tools::getValue('active');
        $business = (int) (bool) Tools::getValue('business');
        $config_nb_backup = (int) Tools::getValue('config_nb_backup');
        $code = Tools::getValue('code');
        $directory_path = Tools::getValue('directory_path');
        $directory_key = Tools::getValue('directory_key');

        $result = $this->ntbr->saveOnedrive(
            $id_ntbr_config,
            $id_ntbr_onedrive,
            $name,
            $active,
            $config_nb_backup,
            $code,
            $directory_path,
            $directory_key,
            $business
        );

        exit(json_encode(['result' => $result]));
    }

    public function saveSugarsync()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $id_ntbr_sugarsync = (int) Tools::getValue('id_ntbr_sugarsync');
        $name = trim(Tools::getValue('name'));
        $active = (int) (bool) Tools::getValue('active');
        $config_nb_backup = (int) Tools::getValue('config_nb_backup');
        $login = trim(Tools::getValue('login'));
        $password = trim(Tools::getValue('password'));
        $directory_path = trim(Tools::getValue('directory_path'));
        $directory_key = trim(Tools::getValue('directory_key'));

        $result = $this->ntbr->saveSugarsync(
            $id_ntbr_config,
            $id_ntbr_sugarsync,
            $name,
            $active,
            $config_nb_backup,
            $login,
            $password,
            $directory_path,
            $directory_key
        );

        exit(json_encode(['result' => $result]));
    }

    public function saveAws()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $id_ntbr_aws = (int) Tools::getValue('id_ntbr_aws');
        $name = Tools::getValue('name');
        $active = (int) (bool) Tools::getValue('active');
        $config_nb_backup = (int) Tools::getValue('config_nb_backup');
        $access_key_id = Tools::getValue('access_key_id');
        $secret_access_key = Tools::getValue('secret_access_key');
        $region = Tools::getValue('region');
        $bucket = Tools::getValue('bucket');
        $host = Tools::getValue('host');
        $storage_class = Tools::getValue('storage_class');
        $directory_key = Tools::getValue('directory_key');
        $directory_path = Tools::getValue('directory_path');
        $type_s3 = Tools::getValue('type_s3');
        $accept_unvalid_ssl = (int) (bool) Tools::getValue('accept_unvalid_ssl');

        $result = $this->ntbr->saveAws(
            $id_ntbr_config,
            $id_ntbr_aws,
            $name,
            $active,
            $config_nb_backup,
            $access_key_id,
            $secret_access_key,
            $region,
            $bucket,
            $host,
            $storage_class,
            $directory_key,
            $directory_path,
            $type_s3,
            $accept_unvalid_ssl
        );

        exit(json_encode(['result' => $result]));
    }

    public function checkConnectionFtp()
    {
        $id_ntbr_ftp = (int) Tools::getValue('id_ntbr_ftp');
        $success = $this->ntbr->checkConnectionFtp($id_ntbr_ftp);

        exit(json_encode(['success' => $success]));
    }

    public function checkConnectionDropbox()
    {
        $id_ntbr_dropbox = (int) Tools::getValue('id_ntbr_dropbox');
        $success = $this->ntbr->checkConnectionDropbox($id_ntbr_dropbox);

        exit(json_encode(['success' => $success]));
    }

    public function checkConnectionYandex()
    {
        $id_ntbr_yandex = (int) Tools::getValue('id_ntbr_yandex');
        $success = $this->ntbr->checkConnectionYandex($id_ntbr_yandex);

        exit(json_encode(['success' => $success]));
    }

    public function checkConnectionOwncloud()
    {
        $id_ntbr_owncloud = (int) Tools::getValue('id_ntbr_owncloud');
        $success = $this->ntbr->checkConnectionOwncloud($id_ntbr_owncloud);

        exit(json_encode(['success' => $success]));
    }

    public function checkConnectionShadowDrive()
    {
        $id_ntbr_shadow_drive = (int) Tools::getValue('id_ntbr_shadow_drive');
        $success = $this->ntbr->checkConnectionShadowDrive($id_ntbr_shadow_drive);

        exit(json_encode(['success' => $success]));
    }

    public function checkConnectionWebdav()
    {
        $id_ntbr_webdav = (int) Tools::getValue('id_ntbr_webdav');
        $success = $this->ntbr->checkConnectionWebdav($id_ntbr_webdav);

        exit(json_encode(['success' => $success]));
    }

    public function checkConnectionGoogledrive()
    {
        $id_ntbr_googledrive = (int) Tools::getValue('id_ntbr_googledrive');
        $success = $this->ntbr->checkConnectionGoogledrive($id_ntbr_googledrive);

        exit(json_encode(['success' => $success]));
    }

    public function checkConnectionGooglecloud()
    {
        $id_ntbr_googlecloud = (int) Tools::getValue('id_ntbr_googlecloud');
        $success = $this->ntbr->checkConnectionGooglecloud($id_ntbr_googlecloud);

        exit(json_encode(['success' => $success]));
    }

    public function checkConnectionPcloud()
    {
        $id_ntbr_pcloud = (int) Tools::getValue('id_ntbr_pcloud');
        $success = $this->ntbr->checkConnectionPcloud($id_ntbr_pcloud);

        exit(json_encode(['success' => $success]));
    }

    public function checkConnectionBox()
    {
        $id_ntbr_box = (int) Tools::getValue('id_ntbr_box');
        $success = $this->ntbr->checkConnectionBox($id_ntbr_box);

        exit(json_encode(['success' => $success]));
    }

    public function checkConnectionOnedrive()
    {
        $id_ntbr_onedrive = (int) Tools::getValue('id_ntbr_onedrive');
        $success = $this->ntbr->checkConnectionOnedrive($id_ntbr_onedrive);

        exit(json_encode(['success' => $success]));
    }

    public function checkConnectionSugarsync()
    {
        $id_ntbr_sugarsync = (int) Tools::getValue('id_ntbr_sugarsync');
        $success = $this->ntbr->checkConnectionSugarsync($id_ntbr_sugarsync);

        exit(json_encode(['success' => $success]));
    }

    public function checkConnectionAws()
    {
        $id_ntbr_aws = (int) Tools::getValue('id_ntbr_aws');
        $success = $this->ntbr->checkConnectionAws($id_ntbr_aws);

        exit(json_encode(['success' => $success]));
    }

    public function deleteFtp()
    {
        $id_ntbr_ftp = (int) Tools::getValue('id_ntbr_ftp');
        $success = $this->ntbr->deleteFtp($id_ntbr_ftp);

        exit(json_encode(['success' => $success]));
    }

    public function deleteDropbox()
    {
        $id_ntbr_dropbox = (int) Tools::getValue('id_ntbr_dropbox');
        $success = $this->ntbr->deleteDropbox($id_ntbr_dropbox);

        exit(json_encode(['success' => $success]));
    }

    public function deleteYandex()
    {
        $id_ntbr_yandex = (int) Tools::getValue('id_ntbr_yandex');
        $success = $this->ntbr->deleteYandex($id_ntbr_yandex);

        exit(json_encode(['success' => $success]));
    }

    public function deleteOwncloud()
    {
        $id_ntbr_owncloud = (int) Tools::getValue('id_ntbr_owncloud');
        $success = $this->ntbr->deleteOwncloud($id_ntbr_owncloud);

        exit(json_encode(['success' => $success]));
    }

    public function deleteShadowDrive()
    {
        $id_ntbr_shadow_drive = (int) Tools::getValue('id_ntbr_shadow_drive');
        $success = $this->ntbr->deleteShadowDrive($id_ntbr_shadow_drive);

        exit(json_encode(['success' => $success]));
    }

    public function deleteWebdav()
    {
        $id_ntbr_webdav = (int) Tools::getValue('id_ntbr_webdav');
        $success = $this->ntbr->deleteWebdav($id_ntbr_webdav);

        exit(json_encode(['success' => $success]));
    }

    public function deleteGoogledrive()
    {
        $id_ntbr_googledrive = (int) Tools::getValue('id_ntbr_googledrive');
        $success = $this->ntbr->deleteGoogledrive($id_ntbr_googledrive);

        exit(json_encode(['success' => $success]));
    }

    public function deleteGooglecloud()
    {
        $id_ntbr_googlecloud = (int) Tools::getValue('id_ntbr_googlecloud');
        $success = $this->ntbr->deleteGooglecloud($id_ntbr_googlecloud);

        exit(json_encode(['success' => $success]));
    }

    public function deletePcloud()
    {
        $id_ntbr_pcloud = (int) Tools::getValue('id_ntbr_pcloud');
        $success = $this->ntbr->deletePcloud($id_ntbr_pcloud);

        exit(json_encode(['success' => $success]));
    }

    public function deleteBox()
    {
        $id_ntbr_box = (int) Tools::getValue('id_ntbr_box');
        $success = $this->ntbr->deleteBox($id_ntbr_box);

        exit(json_encode(['success' => $success]));
    }

    public function deleteOnedrive()
    {
        $id_ntbr_onedrive = (int) Tools::getValue('id_ntbr_onedrive');
        $success = $this->ntbr->deleteOnedrive($id_ntbr_onedrive);

        exit(json_encode(['success' => $success]));
    }

    public function deleteSugarsync()
    {
        $id_ntbr_sugarsync = (int) Tools::getValue('id_ntbr_sugarsync');
        $success = $this->ntbr->deleteSugarsync($id_ntbr_sugarsync);

        exit(json_encode(['success' => $success]));
    }

    public function deleteAws()
    {
        $id_ntbr_aws = (int) Tools::getValue('id_ntbr_aws');
        $success = $this->ntbr->deleteAws($id_ntbr_aws);

        exit(json_encode(['success' => $success]));
    }

    public function deleteBackup()
    {
        $result = false;

        if (Tools::isSubmit('nb')) {
            $result = $this->ntbr->deleteThisBackup(Tools::getValue('nb'));
        }

        exit(json_encode(['result' => $result]));
    }

    public function createBackup()
    {
        $current_time = time();
        $id_config = ConfigNtbr::getIdDefault();

        if (Tools::isSubmit('id_ntbr_config')) {
            $id_config = Tools::getValue('id_ntbr_config');
        }

        $config = new ConfigNtbr($id_config);
        $time_between_backups = $config->time_between_backups;

        if ($time_between_backups <= 0) {
            $time_between_backups = NtbrCore::MIN_TIME_NEW_BACKUP;
        }

        $ntbr_ongoing = GlobConfNtbr::get('NTBR_ONGOING');

        if ($current_time - $ntbr_ongoing >= $time_between_backups) {
            GlobConfNtbr::set('NTBR_ONGOING', time());
            $this->ntbr->backup($id_config);
            $update = $this->ntbr->updateBackupList();

            $total_scan_root_size = ScanSizeNtbr::getTotalSize();
            $last_scan_date = Tools::displayDate(ScanSizeNtbr::getLastDate(), null, true);
            $last_scan_config = ConfigNtbr::getNameById(GlobConfNtbr::get('NTBR_SCAN_ID_CONFIG'));

            exit(json_encode([
                'backuplist' => $update,
                'warnings' => $this->ntbr->warnings,
                'scan_root_size' => $total_scan_root_size,
                'scan_root_readable_size' => $this->ntbr->readableSize($total_scan_root_size),
                'last_scan_date' => $last_scan_date,
                'last_scan_config' => $last_scan_config,
            ]));
        } else {
            $time_to_wait = $time_between_backups - ($current_time - $ntbr_ongoing);
            $this->ntbr->log(
                'ERR' . sprintf(
                    $this->ntbr->l('For security reason, some time is needed between two backups. Please wait %d seconds', self::PAGE),
                    $time_to_wait
                )
            );
        }

        exit(json_encode([]));
    }

    public function refreshBackup()
    {
        $result = $this->ntbr->backup(ConfigNtbr::getIdDefault(), true);

        if ($result) {
            $update = $this->ntbr->updateBackupList();
            $total_scan_root_size = ScanSizeNtbr::getTotalSize();
            $last_scan_date = Tools::displayDate(ScanSizeNtbr::getLastDate(), null, true);
            $last_scan_config = ConfigNtbr::getNameById(GlobConfNtbr::get('NTBR_SCAN_ID_CONFIG'));

            exit(json_encode([
                'backuplist' => $update,
                'warnings' => $this->ntbr->warnings,
                'scan_root_size' => $total_scan_root_size,
                'scan_root_readable_size' => $this->ntbr->readableSize($total_scan_root_size),
                'last_scan_date' => $last_scan_date,
                'last_scan_config' => $last_scan_config,
            ]));
        }

        exit(json_encode([]));
    }

    public function getDirectoryChidren()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $directory = Tools::getValue('directory');

        $tree = $this->ntbr->getDirectoryTreeChildren($id_ntbr_config, $directory);

        exit(json_encode(['tree' => $tree]));
    }

    public function scanFiles()
    {
        $tree = $this->ntbr->getScanDirectoryTree(Tools::getValue('directory'));

        exit(json_encode(['tree' => $tree]));
    }

    public function deleteScanFiles()
    {
        $path = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . Tools::getValue('path');

        if (is_file($path)) {
            if (!Apparatus::checkFileExists($path)) {
                exit(json_encode(['result' => false]));
            }

            if (!$this->ntbr->fileDelete($path)) {
                exit(json_encode(['result' => false]));
            }
        } else {
            if (!is_dir($path)) {
                exit(json_encode(['result' => false]));
            }

            if (!$this->ntbr->directoryDelete($path)) {
                exit(json_encode(['result' => false]));
            }
        }

        exit(json_encode(['result' => true]));
    }

    public function getBackupSodiumKey()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $o_config = new ConfigNtbr($id_ntbr_config);

        $key = $this->ntbr->decrypt($o_config->sodium_key);

        if (!$key) {
            $this->createBackupSodiumKey();
        }

        exit(json_encode(['result' => true, 'key' => $key]));
    }

    public function createBackupSodiumKey()
    {
        $id_ntbr_config = (int) Tools::getValue('id_ntbr_config');
        $o_config = new ConfigNtbr($id_ntbr_config);
        $key = $this->ntbr->createBackupSodiumKey();

        if (!$key) {
            $this->ntbr->log($this->l('The backup sodium key could not be created', self::PAGE), true);
            exit(json_encode(['result' => false]));
        }

        $o_config->sodium_key = $this->ntbr->encrypt($key);

        if (!$o_config->update()) {
            $this->ntbr->log($this->l('The backup sodium key could not be saved', self::PAGE), true);
            exit(json_encode(['result' => false]));
        }

        exit(json_encode(['result' => true, 'key' => $key]));
    }

    public function getDropboxFiles()
    {
        $id_ntbr_dropbox = (int) Tools::getValue('id_ntbr_dropbox');
        $files = $this->ntbr->getDropboxFilesList($id_ntbr_dropbox);

        if (is_array($files)) {
            $res = 1;
        } else {
            $res = 0;
        }

        exit(json_encode(['res' => $res,  'files' => $files]));
    }

    public function deleteDropboxFile()
    {
        $id_ntbr_dropbox = (int) Tools::getValue('id_ntbr_dropbox');
        $nb_part = (int) Tools::getValue('nb_part');
        $file_name = Tools::getValue('file_name');

        $result = $this->ntbr->deleteDropboxFile($id_ntbr_dropbox, $file_name, $nb_part);

        exit(json_encode(['result' => (int) $result]));
    }

    public function downloadDropboxFile()
    {
        $id_ntbr_dropbox = (int) Tools::getValue('id_ntbr_dropbox');
        $id_file = Tools::getValue('id_file');

        $link = $this->ntbr->downloadDropboxFile($id_ntbr_dropbox, $id_file);

        exit(json_encode(['link' => $link]));
    }

    public function getYandexFiles()
    {
        $id_ntbr_yandex = (int) Tools::getValue('id_ntbr_yandex');
        $files = $this->ntbr->getYandexFilesList($id_ntbr_yandex);

        if (is_array($files)) {
            $res = 1;
        } else {
            $res = 0;
        }

        exit(json_encode(['res' => $res,  'files' => $files]));
    }

    public function deleteYandexFile()
    {
        $id_ntbr_yandex = (int) Tools::getValue('id_ntbr_yandex');
        $nb_part = (int) Tools::getValue('nb_part');
        $file_name = Tools::getValue('file_name');

        $result = $this->ntbr->deleteYandexFile($id_ntbr_yandex, $file_name, $nb_part);

        exit(json_encode(['result' => (int) $result]));
    }

    public function downloadYandexFile()
    {
        $id_ntbr_yandex = (int) Tools::getValue('id_ntbr_yandex');
        $path = Tools::getValue('path');

        $link = $this->ntbr->downloadYandexFile($id_ntbr_yandex, $path);

        exit(json_encode(['link' => $link]));
    }

    public function getGoogledriveFiles()
    {
        $id_ntbr_googledrive = (int) Tools::getValue('id_ntbr_googledrive');
        $files = $this->ntbr->getGoogledriveFilesList($id_ntbr_googledrive);

        if (is_array($files)) {
            $res = 1;
        } else {
            $res = 0;
        }

        exit(json_encode(['res' => $res,  'files' => $files]));
    }

    public function downloadGoogledriveFile()
    {
        $id_ntbr_googledrive = (int) Tools::getValue('id_ntbr_googledrive');
        $id_file = Tools::getValue('id_file');

        $link = $this->ntbr->downloadGoogledriveFile($id_ntbr_googledrive, $id_file);

        exit(json_encode(['link' => $link]));
    }

    public function deleteGoogledriveFile()
    {
        $id_ntbr_googledrive = (int) Tools::getValue('id_ntbr_googledrive');
        $nb_part = (int) Tools::getValue('nb_part');
        $file_name = Tools::getValue('file_name');

        $result = $this->ntbr->deleteGoogledriveFile($id_ntbr_googledrive, $file_name, $nb_part);

        exit(json_encode(['result' => (int) $result]));
    }

    public function getGooglecloudFiles()
    {
        $id_ntbr_googlecloud = (int) Tools::getValue('id_ntbr_googlecloud');
        $files = $this->ntbr->getGooglecloudFilesList($id_ntbr_googlecloud);

        if (is_array($files)) {
            $res = 1;
        } else {
            $res = 0;
        }

        exit(json_encode(['res' => $res,  'files' => $files]));
    }

    public function deleteGooglecloudFile()
    {
        $id_ntbr_googlecloud = (int) Tools::getValue('id_ntbr_googlecloud');
        $nb_part = (int) Tools::getValue('nb_part');
        $file_name = Tools::getValue('file_name');

        $result = $this->ntbr->deleteGooglecloudFile($id_ntbr_googlecloud, $file_name, $nb_part);

        exit(json_encode(['result' => (int) $result]));
    }

    public function getPcloudFiles()
    {
        $id_ntbr_pcloud = (int) Tools::getValue('id_ntbr_pcloud');
        $files = $this->ntbr->getPcloudFilesList($id_ntbr_pcloud);

        if (is_array($files)) {
            $res = 1;
        } else {
            $res = 0;
        }

        exit(json_encode(['res' => $res,  'files' => $files]));
    }

    public function deletePcloudFile()
    {
        $id_ntbr_pcloud = (int) Tools::getValue('id_ntbr_pcloud');
        $nb_part = (int) Tools::getValue('nb_part');
        $file_name = Tools::getValue('file_name');

        $result = $this->ntbr->deletePcloudFile($id_ntbr_pcloud, $file_name, $nb_part);

        exit(json_encode(['result' => (int) $result]));
    }

    public function getBoxFiles()
    {
        $id_ntbr_box = (int) Tools::getValue('id_ntbr_box');
        $files = $this->ntbr->getBoxFilesList($id_ntbr_box);

        if (is_array($files)) {
            $res = 1;
        } else {
            $res = 0;
        }

        exit(json_encode(['res' => $res,  'files' => $files]));
    }

    public function getAwsFiles()
    {
        $id_ntbr_aws = (int) Tools::getValue('id_ntbr_aws');
        $files = $this->ntbr->getAwsFilesList($id_ntbr_aws);

        if (is_array($files)) {
            $res = 1;
        } else {
            $res = 0;
        }

        exit(json_encode(['res' => $res,  'files' => $files]));
    }

    public function downloadBoxFile()
    {
        $id_ntbr_box = (int) Tools::getValue('id_ntbr_box');
        $id_file = Tools::getValue('id_file');

        $link = $this->ntbr->downloadBoxFile($id_ntbr_box, $id_file);

        exit(json_encode(['link' => $link]));
    }

    public function deleteBoxFile()
    {
        $id_ntbr_box = (int) Tools::getValue('id_ntbr_box');
        $nb_part = (int) Tools::getValue('nb_part');
        $file_name = Tools::getValue('file_name');

        $result = $this->ntbr->deleteBoxFile($id_ntbr_box, $file_name, $nb_part);

        exit(json_encode(['result' => (int) $result]));
    }

    public function getOnedriveFiles()
    {
        $id_ntbr_onedrive = (int) Tools::getValue('id_ntbr_onedrive');
        $files = $this->ntbr->getOnedriveFilesList($id_ntbr_onedrive);

        if (is_array($files)) {
            $res = 1;
        } else {
            $res = 0;
        }

        exit(json_encode(['res' => $res,  'files' => $files]));
    }

    public function getOnedriveAuthorizeUrl()
    {
        $business = (int) Tools::getValue('business');

        $onedrive = $this->ntbr->connectToOnedrive();
        $onedrive_authorizeUrl = $onedrive->getLogInUrl($business);

        exit(json_encode(['url' => $onedrive_authorizeUrl]));
    }

    public function getDropboxAuthorizeUrl()
    {
        $business = (int) Tools::getValue('business');

        $dropbox = $this->ntbr->connectToDropbox($business);
        $dropbox_authorizeUrl = $dropbox->getLogInUrl();

        exit(json_encode(['url' => $dropbox_authorizeUrl]));
    }

    public function downloadOnedriveFile()
    {
        $id_ntbr_onedrive = (int) Tools::getValue('id_ntbr_onedrive');
        $id_file = Tools::getValue('id_file');

        $link = $this->ntbr->downloadOnedriveFile($id_ntbr_onedrive, $id_file);

        exit(json_encode(['link' => $link]));
    }

    public function deleteOnedriveFile()
    {
        $id_ntbr_onedrive = (int) Tools::getValue('id_ntbr_onedrive');
        $nb_part = (int) Tools::getValue('nb_part');
        $file_name = Tools::getValue('file_name');

        $result = $this->ntbr->deleteOnedriveFile($id_ntbr_onedrive, $file_name, $nb_part);

        exit(json_encode(['result' => (int) $result]));
    }

    public function deleteAwsFile()
    {
        $id_ntbr_aws = (int) Tools::getValue('id_ntbr_aws');
        $nb_part = (int) Tools::getValue('nb_part');
        $file_name = Tools::getValue('file_name');

        $result = $this->ntbr->deleteAwsFile($id_ntbr_aws, $file_name, $nb_part);

        exit(json_encode(['result' => (int) $result]));
    }

    public function getOwncloudFiles()
    {
        $id_ntbr_owncloud = (int) Tools::getValue('id_ntbr_owncloud');
        $files = $this->ntbr->getOwncloudFilesList($id_ntbr_owncloud);

        if (is_array($files)) {
            $res = 1;
        } else {
            $res = 0;
        }

        exit(json_encode(['res' => $res,  'files' => $files]));
    }

    public function downloadOwncloudFile()
    {
        $id_ntbr_owncloud = (int) Tools::getValue('id_ntbr_owncloud');
        $id_file = Tools::getValue('id_file');
        $file_size = Tools::getValue('file_size');
        $pos = Tools::getValue('pos');

        $length = $file_size - $pos;

        if ($length > NtbrChild::MAX_FILE_DOWNLOAD_SIZE) {
            $length = NtbrChild::MAX_FILE_DOWNLOAD_SIZE;
        }

        $content = $this->ntbr->downloadOwncloudFile($id_ntbr_owncloud, $id_file, $pos, $length, $file_size);

        exit($content);
    }

    public function deleteOwncloudFile()
    {
        $id_ntbr_owncloud = (int) Tools::getValue('id_ntbr_owncloud');
        $nb_part = (int) Tools::getValue('nb_part');
        $file_name = Tools::getValue('file_name');

        $result = $this->ntbr->deleteOwncloudFile($id_ntbr_owncloud, $file_name, $nb_part);

        exit(json_encode(['result' => (int) $result]));
    }

    public function getShadowDriveFiles()
    {
        $id_ntbr_shadow_drive = (int) Tools::getValue('id_ntbr_shadow_drive');
        $files = $this->ntbr->getShadowDriveFilesList($id_ntbr_shadow_drive);

        if (is_array($files)) {
            $res = 1;
        } else {
            $res = 0;
        }

        exit(json_encode(['res' => $res,  'files' => $files]));
    }

    public function downloadShadowDriveFile()
    {
        $id_ntbr_shadow_drive = (int) Tools::getValue('id_ntbr_shadow_drive');
        $id_file = Tools::getValue('id_file');
        $file_size = Tools::getValue('file_size');
        $pos = Tools::getValue('pos');

        $length = $file_size - $pos;

        if ($length > NtbrChild::MAX_FILE_DOWNLOAD_SIZE) {
            $length = NtbrChild::MAX_FILE_DOWNLOAD_SIZE;
        }

        $content = $this->ntbr->downloadShadowDriveFile($id_ntbr_shadow_drive, $id_file, $pos, $length, $file_size);

        exit($content);
    }

    public function deleteShadowDriveFile()
    {
        $id_ntbr_shadow_drive = (int) Tools::getValue('id_ntbr_shadow_drive');
        $nb_part = (int) Tools::getValue('nb_part');
        $file_name = Tools::getValue('file_name');

        $result = $this->ntbr->deleteShadowDriveFile($id_ntbr_shadow_drive, $file_name, $nb_part);

        exit(json_encode(['result' => (int) $result]));
    }

    public function getWebdavFiles()
    {
        $id_ntbr_webdav = (int) Tools::getValue('id_ntbr_webdav');
        $files = $this->ntbr->getWebdavFilesList($id_ntbr_webdav);

        if (is_array($files)) {
            $res = 1;
        } else {
            $res = 0;
        }

        exit(json_encode(['res' => $res,  'files' => $files]));
    }

    public function downloadWebdavFile()
    {
        $id_ntbr_webdav = (int) Tools::getValue('id_ntbr_webdav');
        $id_file = Tools::getValue('id_file');
        $file_size = Tools::getValue('file_size');
        $pos = Tools::getValue('pos');

        $length = $file_size - $pos;

        if ($length > NtbrChild::MAX_FILE_DOWNLOAD_SIZE) {
            $length = NtbrChild::MAX_FILE_DOWNLOAD_SIZE;
        }

        $content = $this->ntbr->downloadWebdavFile($id_ntbr_webdav, $id_file, $pos, $length, $file_size);

        exit($content);
    }

    public function deleteWebdavFile()
    {
        $id_ntbr_webdav = (int) Tools::getValue('id_ntbr_webdav');
        $nb_part = (int) Tools::getValue('nb_part');
        $file_name = Tools::getValue('file_name');

        $result = $this->ntbr->deleteWebdavFile($id_ntbr_webdav, $file_name, $nb_part);

        exit(json_encode(['result' => (int) $result]));
    }

    public function getFtpFiles()
    {
        $id_ntbr_ftp = (int) Tools::getValue('id_ntbr_ftp');
        $files = $this->ntbr->getFtpFilesList($id_ntbr_ftp);

        if (is_array($files)) {
            $res = 1;
        } else {
            $res = 0;
        }

        exit(json_encode(['res' => $res,  'files' => $files]));
    }

    public function downloadFtpFile()
    {
        $id_ntbr_ftp = (int) Tools::getValue('id_ntbr_ftp');
        $id_file = Tools::getValue('id_file');
        $file_size = Tools::getValue('file_size');
        $pos = Tools::getValue('pos');

        $length = $file_size - $pos;

        if ($length > NtbrChild::MAX_FILE_DOWNLOAD_SIZE) {
            $length = NtbrChild::MAX_FILE_DOWNLOAD_SIZE;
        }

        $content = $this->ntbr->downloadFtpFile($id_ntbr_ftp, $id_file, $pos, $length);

        exit($content);
    }

    public function deleteFtpFile()
    {
        $id_ntbr_ftp = (int) Tools::getValue('id_ntbr_ftp');
        $nb_part = (int) Tools::getValue('nb_part');
        $file_name = Tools::getValue('file_name');

        $result = $this->ntbr->deleteFtpFile($id_ntbr_ftp, $file_name, $nb_part);

        exit(json_encode(['result' => (int) $result]));
    }

    public function displayGoogledriveTree()
    {
        $id_ntbr_googledrive = (int) Tools::getValue('id_ntbr_googledrive');
        $tree = $this->ntbr->displayGoogledriveTree($id_ntbr_googledrive);

        exit(json_encode(['tree' => $tree]));
    }

    public function displayGoogledriveTreeChild()
    {
        $id_ntbr_googledrive = (int) Tools::getValue('id_ntbr_googledrive');
        $id_parent = Tools::getValue('id_parent');
        $googledrive_dir = Tools::getValue('googledrive_dir');
        $level = Tools::getValue('level');
        $path = Tools::getValue('path');

        $tree = $this->ntbr->displayGoogledriveTreeChild(
            $id_ntbr_googledrive,
            $id_parent,
            $googledrive_dir,
            $level,
            $path
        );

        exit(json_encode(['tree' => $tree]));
    }

    public function displayGooglecloudTree()
    {
        $id_ntbr_googlecloud = (int) Tools::getValue('id_ntbr_googlecloud');
        $tree = $this->ntbr->displayGooglecloudTree($id_ntbr_googlecloud);

        exit(json_encode(['tree' => $tree]));
    }

    public function displayGooglecloudTreeChild()
    {
        $id_ntbr_googlecloud = (int) Tools::getValue('id_ntbr_googlecloud');
        $googlecloud_dir = Tools::getValue('googlecloud_dir');
        $level = Tools::getValue('level');
        $path = Tools::getValue('path');

        $tree = $this->ntbr->displayGooglecloudTreeChild(
            $id_ntbr_googlecloud,
            $googlecloud_dir,
            $level,
            $path
        );

        exit(json_encode(['tree' => $tree]));
    }

    public function displayPcloudTree()
    {
        $id_ntbr_pcloud = (int) Tools::getValue('id_ntbr_pcloud');
        $tree = $this->ntbr->displayPcloudTree($id_ntbr_pcloud);

        exit(json_encode(['tree' => $tree]));
    }

    public function displayPcloudTreeChild()
    {
        $id_ntbr_pcloud = (int) Tools::getValue('id_ntbr_pcloud');
        $pcloud_dir_id = Tools::getValue('pcloud_dir_id');
        $level = Tools::getValue('level');

        $tree = $this->ntbr->displayPcloudTreeChild($id_ntbr_pcloud, $pcloud_dir_id, $level);

        exit(json_encode(['tree' => $tree]));
    }

    public function displayBoxTree()
    {
        $id_ntbr_box = (int) Tools::getValue('id_ntbr_box');
        $tree = $this->ntbr->displayBoxTree($id_ntbr_box);

        exit(json_encode(['tree' => $tree]));
    }

    public function displayBoxTreeChild()
    {
        $id_ntbr_box = (int) Tools::getValue('id_ntbr_box');
        $id_parent = Tools::getValue('id_parent');
        $box_dir = Tools::getValue('box_dir');
        $level = Tools::getValue('level');
        $path = Tools::getValue('path');

        $tree = $this->ntbr->displayBoxTreeChild(
            $id_ntbr_box,
            $id_parent,
            $box_dir,
            $level,
            $path
        );

        exit(json_encode(['tree' => $tree]));
    }

    public function displayOnedriveTree()
    {
        $id_ntbr_onedrive = (int) Tools::getValue('id_ntbr_onedrive');
        $tree = $this->ntbr->displayOnedriveTree($id_ntbr_onedrive);

        exit(json_encode(['tree' => $tree]));
    }

    public function displayOnedriveTreeChild()
    {
        $id_ntbr_onedrive = (int) Tools::getValue('id_ntbr_onedrive');
        $id_parent = Tools::getValue('id_parent');
        $onedrive_dir = Tools::getValue('onedrive_dir');
        $level = Tools::getValue('level');
        $path = Tools::getValue('path');

        $tree = $this->ntbr->displayOnedriveTreeChild($id_ntbr_onedrive, $id_parent, $onedrive_dir, $level, $path);

        exit(json_encode(['tree' => $tree]));
    }

    public function displaySugarsyncTree()
    {
        $id_ntbr_sugarsync = (int) Tools::getValue('id_ntbr_sugarsync');
        $tree = $this->ntbr->displaySugarsyncTree($id_ntbr_sugarsync);

        exit(json_encode(['tree' => $tree]));
    }

    public function displaySugarsyncTreeChild()
    {
        $id_ntbr_sugarsync = (int) Tools::getValue('id_ntbr_sugarsync');
        $id_parent = Tools::getValue('id_parent');
        $sugarsync_dir = Tools::getValue('sugarsync_dir');
        $level = Tools::getValue('level');
        $path = Tools::getValue('path');

        $tree = $this->ntbr->displaySugarsyncTreeChild($id_ntbr_sugarsync, $id_parent, $sugarsync_dir, $level, $path);

        exit(json_encode(['tree' => $tree]));
    }

    public function displayAwsTree()
    {
        $id_ntbr_aws = (int) Tools::getValue('id_ntbr_aws');
        $tree = $this->ntbr->displayAwsTree($id_ntbr_aws);

        exit(json_encode(['tree' => $tree]));
    }

    public function displayAwsTreeChild()
    {
        $id_ntbr_aws = (int) Tools::getValue('id_ntbr_aws');
        $directory_key = Tools::getValue('directory_key');
        $directory_path = Tools::getValue('directory_path');
        $level = Tools::getValue('level');

        $tree = $this->ntbr->displayAwsTreeChild($id_ntbr_aws, $directory_key, $directory_path, $level);

        exit(json_encode(['tree' => $tree]));
    }

    public function sendBackupAway()
    {
        $nb = Tools::getValue('nb');
        $result = $this->ntbr->onlySendBackupAway($nb);

        if ($result !== false) {
            exit(json_encode($result));
        }

        exit(json_encode([]));
    }

    public function getJsBackup()
    {
        $nb = Tools::getValue('nb');
        $backup = $this->ntbr->findThisBackup($nb);

        if (count($backup) > 1) {
            $backup_name = preg_replace('/([0-9]+\.part\.)/', '', $backup[$nb]['name']);
        } else {
            $backup_name = $backup[$nb . '.1']['name'];
        }

        $config = new ConfigNtbr(Backups::getBackupIdConfig($backup_name));

        exit(json_encode(['js_download' => (int) $config->js_download]));
    }

    public function saveInfosBackup()
    {
        $backup_name = Tools::getValue('backup_name');
        $backup_comment = Tools::getValue('backup_comment');
        $backup_safe = Tools::getValue('backup_safe');

        if (!$backup_name || $backup_name == '') {
            exit(json_encode(['result' => '0']));
        }

        $infos = Backups::getBackupInfos($backup_name);

        if (isset($infos['id_ntbr_backups'])) {
            $backup = new Backups($infos['id_ntbr_backups']);
        }

        $backup->comment = $backup_comment;
        $backup->safe = $backup_safe;

        if ($backup->save()) {
            exit(json_encode(['result' => '1']));
        }

        exit(json_encode(['result' => '0']));
    }

    public function restoreBackup()
    {
        $backup_name = Tools::getValue('backup');
        $type_backup = Tools::getValue('type_backup');
        $encryption_key = Tools::getValue('encryption_key');

        $options_restore = $this->ntbr->restoreBackup($backup_name, $type_backup, $encryption_key);

        if ($options_restore === false) {
            exit(json_encode(['result' => '0']));
        }

        $backup_infos = Backups::getBackupInfos($backup_name);

        exit(json_encode(['result' => '1', 'options' => $options_restore, 'infos' => $backup_infos]));
    }

    public function endRestoreBackup()
    {
        $backup_name = Tools::getValue('backup_name');
        $comment = Tools::getValue('comment');
        $safe = Tools::getValue('safe');
        $success = Tools::getValue('success');
        $id_ntbr_config = Tools::getValue('id_ntbr_config');

        $res = $this->ntbr->endLocalRestore($backup_name, $comment, $safe, $id_ntbr_config, $success);

        if (!$res) {
            exit(json_encode(['result' => '0']));
        }

        exit(json_encode(['result' => '1']));
    }

    public function addBackup()
    {
        $backup_name = Tools::getValue('backup');
        $id_config = Tools::getValue('id_config');

        $backup = new Backups();
        $backup->id_ntbr_config = $id_config;
        $backup->backup_name = $backup_name;
        $backup->comment = '';
        $backup->safe = 0;

        if (!$backup->add()) {
            $this->ntbr->log($this->l('The backup infos were not saved', self::PAGE), true);
            exit(json_encode(['result' => '0']));
        }

        /*$update = $this->ntbr->updateBackupList();

        die(json_encode(array('backup_list' => $update, 'result' => '1')));*/
        exit(json_encode(['result' => '1']));
    }

    public function sendFileToBackupDir()
    {
        $filename = Tools::getValue('filename');

        if (!rename(_PS_ROOT_DIR_ . '/' . $filename, NtBackupAndRestore::getModuleBackupDirectory() . $filename)) {
            exit(json_encode(['result' => '0']));
        }

        exit(json_encode(['result' => '1']));
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title = 'NT ' . $this->l('Backup and Restore', self::PAGE);
    }

    /**
     * assign default action in page_header_toolbar_btn smarty var, if they are not set.
     * uses override to specifically add, modify or remove items
     */
    public function initPageHeaderToolbar()
    {
        if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
            if ($this->display == 'view' && !NtBackupAndRestore::isPrestaEdition()) {
                $this->page_header_toolbar_btn['save'] = [
                    'href' => '#',
                    'desc' => $this->l('Save', self::PAGE),
                ];
            }
            parent::initPageHeaderToolbar();
        }
    }

    /**
     * assign default action in toolbar_btn smarty var, if they are not set.
     * uses override to specifically add, modify or remove items
     */
    public function initToolbar()
    {
        if (version_compare(_PS_VERSION_, '1.6.0', '>=') !== true) {
            if ($this->display == 'view' && !NtBackupAndRestore::isPrestaEdition()) {
                // Default save button - action dynamically handled in javascript
                $this->toolbar_btn['save'] = [
                    'href' => '#',
                    'desc' => $this->l('Save', self::PAGE),
                ];
            }
        }
    }
}
