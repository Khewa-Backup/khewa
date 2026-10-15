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

/**
 * class AdminSuperSpeedAjaxController
 * @property Ets_superspeed $module
 */
class AdminSuperSpeedAjaxController extends ModuleAdminController
{
    public function init()
    {

       parent::init();
       if(Tools::isSubmit('changeSubmitImageOptimize') || Tools::isSubmit('btnSubmitImageOptimize') || Tools::isSubmit('btnSubmitImageAllOptimize') || Tools::isSubmit('submitUploadImageSave')||Tools::isSubmit('submitUploadImageCompress') || Tools::isSubmit('submitBrowseImageOptimize') || Tools::isSubmit('btnSubmitCleaneImageUnUsed'))
            $this->module->_postImage();
       if(Tools::isSubmit('btnSubmitPageCache') || Tools::isSubmit('clear_all_page_caches') || Tools::isSubmit('btnSubmitPageCacheDashboard') || Tools::isSubmit('btnRefreshSystemAnalyticsNew'))
            $this->module->_postPageCache();
       if(Tools::isSubmit('btnSubmitMinization'))
            $this->module->_postMinization();
       if(Tools::isSubmit('btnSubmitGzip'))
            $this->module->_postGzip();
       if(Tools::isSubmit('submitDeleteSystemAnalytics'))
       {
            $this->module->submitDeleteSystemAnalytics();
       }
       if(Tools::isSubmit('submitRunCronJob'))
       {
            $this->module->autoRefreshCache();
       }
       if (Tools::isSubmit('clearCloudflareCache')) {
            $zoneId = trim((string) Tools::getValue('ETS_SPEED_CLOUDFLARE_ZONE_ID'));
            $apiToken = trim((string) Tools::getValue('ETS_SPEED_CLOUDFLARE_API_TOKEN'));
            if ($zoneId === '' || $apiToken === '') {
                die(
                    json_encode(
                        array(
                            'errors' => $this->module->l('Zone ID and API Token are required', 'AdminSuperSpeedAjaxController'),
                        )
                    )
                );
            }
            if (!preg_match('/^[a-f0-9]{32}$/i', $zoneId)) {
                die(
                    json_encode(
                        array(
                            'errors' => $this->module->l('Zone ID format is not valid.', 'AdminSuperSpeedAjaxController'),
                        )
                    )
                );
            }
            $tokenLen = Tools::strlen($apiToken);
            if ($tokenLen < 20 || $tokenLen > 2048 || !preg_match('/^[\x20-\x7E]+$/', $apiToken)) {
                die(
                    json_encode(
                        array(
                            'errors' => $this->module->l('API Token format is not valid.', 'AdminSuperSpeedAjaxController'),
                        )
                    )
                );
            }
            $result = Ets_ss_class_cache::clearCloudflareCache($zoneId, $apiToken);
            $success = is_array($result) && !empty($result['success']);
            if ($success) {
                die(
                    json_encode(
                        array(
                            'success' => $this->module->l('Cloudflare cache cleared successfully', 'AdminSuperSpeedAjaxController'),
                        )
                    )
                );
            }
            $failMessage = is_array($result) && isset($result['message']) ? (string) $result['message'] : 'Unknown error';
            die(
                json_encode(
                    array(
                        'errors' => $this->module->displayError($this->module->l('Failed to clear Cloudflare cache: ', 'AdminSuperSpeedAjaxController') . $failMessage),
                    )
                )
            );
       }
    }
}