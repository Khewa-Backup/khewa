<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/*
 * Created SyncReturnPolicy class for return policy synchronization similar to SyncShopSection
 * @modifier Himanshu Vishwakarma
 * @date 15-12-2025
 */
class SyncReturnPolicy extends Module
{

    public function __construct()
    {
        parent::__construct();
    }

    /** Functions to sync etsy return policies to PrestaShop */
    public static function syncEtsyReturnPolicies()
    {
        $method_name = 'SyncReturnPolicy::syncEtsyReturnPolicies()';
        EtsyModule::auditLogEntry('Job execution started to import return policies from etsy to prestashop store.', $method_name);
        $etsyReturnPolicies = array();
        $shop = EtsyModule::etsyGetShopDetails();
        if(isset($shop['shop_id'])){
            $etsyQueryString = array();
            $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/policies/return';
            $etsyRequestMethod = 'GET';

            $return_policies = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);
            if (!empty($return_policies) && isset($return_policies['results']) && count($return_policies['results'])) {
                if (!empty($return_policies['results'])) {
                    foreach ($return_policies['results'] as $return_policy) {
                        $return_policy_id = isset($return_policy['return_policy_id']) ? $return_policy['return_policy_id'] : (isset($return_policy['id']) ? $return_policy['id'] : '');
                        if (!empty($return_policy_id)) {
                            $etsyReturnPolicies[] = $return_policy_id;

                            /* If Return policy doesn't exist in the Db, Insert the same OR update the values */
                            $result = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "etsy_return_policy WHERE return_policy_id = '" . pSQL($return_policy_id) . "'");
                            
                            $accepts_returns = isset($return_policy['accepts_returns']) ? (int)$return_policy['accepts_returns'] : 0;
                            $accepts_exchanges = isset($return_policy['accepts_exchanges']) ? (int)$return_policy['accepts_exchanges'] : 0;
                            $return_deadline = isset($return_policy['return_deadline']) ? (int)$return_policy['return_deadline'] : 0;
                            
                            if ($result === false) {
                                Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_return_policy (return_policy_id, shop_id, accepts_returns, accepts_exchanges, return_deadline) VALUES ('" . pSQL($return_policy_id) . "', '" . pSQL($shop['shop_id']) . "', '" . (int)$accepts_returns . "', '" . (int)$accepts_exchanges . "', '" . (int)$return_deadline . "')");
                                $log_entry = 'New return policy imported from etsy. Return Policy ID: ' . $return_policy_id . '<br>Accepts Returns: ' . ($accepts_returns ? 'Yes' : 'No') . '<br>Accepts Exchanges: ' . ($accepts_exchanges ? 'Yes' : 'No') . '<br>Return Deadline: ' . $return_deadline . ' days';
                                EtsyModule::auditLogEntry($log_entry, $method_name);
                            } else {
                                Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_return_policy SET "
                                        . "shop_id = '" . pSQL($shop['shop_id']) . "', "
                                        . "accepts_returns = '" . (int)$accepts_returns . "', "
                                        . "accepts_exchanges = '" . (int)$accepts_exchanges . "', "
                                        . "return_deadline = '" . (int)$return_deadline . "' "
                                        . "WHERE return_policy_id = '" . pSQL($return_policy_id) . "'");
                            }
                        }
                    }
                }
                /* Delete the Return policies which are no longer available in the Etsy */
                if (!empty($etsyReturnPolicies)) {
                    Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_return_policy WHERE (return_policy_id IS NOT NULL AND return_policy_id != '' AND return_policy_id != '0') AND return_policy_id NOT IN ('" . implode("','", $etsyReturnPolicies) . "')");
                } else {
                    Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_return_policy WHERE (return_policy_id IS NOT NULL AND return_policy_id != '' AND return_policy_id != '0')");
                }
            } else {
                if (isset($return_policies['error'])) {
                    EtsyModule::auditLogEntry("Error in syncing etsy return policies to prestashop: " . $return_policies['error'], $method_name);
                } else {
                    EtsyModule::auditLogEntry("No return policies found or error in syncing etsy return policies to prestashop.", $method_name);
                }
            }
        } else {
            EtsyModule::auditLogEntry("Error in syncing etsy return policies to prestashop: " . (isset($shop['error']) ? $shop['error'] : 'Shop ID not found'), $method_name);
        }
        EtsyModule::auditLogEntry('Job execution completed to import return policies from etsy to prestashop store.', $method_name);
        return true;
    }

    /** Create Return Policy on Etsy */
    public static function createReturnPolicy($returnPolicy)
    {
        $method_name = 'SyncReturnPolicy::createReturnPolicy()';
        EtsyModule::auditLogEntry('Creating return policy on etsy. Shop ID: ' . (isset($returnPolicy['shop_id']) ? $returnPolicy['shop_id'] : 'N/A'), $method_name);

        $returnPolicyDetails = array(
            'accepts_returns' => isset($returnPolicy['accepts_returns']) ? (int)$returnPolicy['accepts_returns'] : 0,
            'accepts_exchanges' => isset($returnPolicy['accepts_exchanges']) ? (int)$returnPolicy['accepts_exchanges'] : 0,
            'return_deadline' => isset($returnPolicy['return_deadline']) ? (int)$returnPolicy['return_deadline'] : 0,
        );
        /*
         * Converted data into the URL-encoded query string from the array
         * 28-12-2024
         */
        $returnPolicyDetails = http_build_query($returnPolicyDetails);
        
        $shop = EtsyModule::etsyGetShopDetails();
        if(isset($shop['shop_id'])){
            $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/policies/return';
            $etsyRequestMethod = 'POST';
            $response = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $returnPolicyDetails);
            if (!empty($response) && isset($response['return_policy_id'])) {
                $returnPolicyId = $response['return_policy_id'];
                if (!empty($returnPolicyId)) {
                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_return_policy SET return_policy_id = '" . pSQL($returnPolicyId) . "' WHERE id_etsy_return_policy = '" . (int) $returnPolicy['id_etsy_return_policy'] . "'");
                }
                EtsyModule::auditLogEntry('Return policy creation on etsy completed. Return Policy ID: ' . $returnPolicyId, $method_name);
                return true;
            } else {
                EtsyModule::auditLogEntry("Error in creating the return policy on etsy: " . (isset($response['error']) ? $response['error'] : 'Unknown error'), $method_name);
                return false;
            }
        } else {
            EtsyModule::auditLogEntry("Error in creating the return policy on etsy: " . (isset($shop['error']) ? $shop['error'] : 'Shop ID not found'), $method_name);
            return false;
        }
    }

    /** Update Return Policy on Etsy */
    public static function updateReturnPolicy($returnPolicy)
    {
        $method_name = 'SyncReturnPolicy::updateReturnPolicy()';
        EtsyModule::auditLogEntry('Updating return policy on etsy. Return Policy ID: ' . (isset($returnPolicy['return_policy_id']) ? $returnPolicy['return_policy_id'] : 'N/A'), $method_name);

        $returnPolicyDetails = array(
            'accepts_returns' => isset($returnPolicy['accepts_returns']) ? (int)$returnPolicy['accepts_returns'] : 0,
            'accepts_exchanges' => isset($returnPolicy['accepts_exchanges']) ? (int)$returnPolicy['accepts_exchanges'] : 0,
            'return_deadline' => isset($returnPolicy['return_deadline']) ? (int)$returnPolicy['return_deadline'] : 0,
        );
        /*
         * Converted data into the URL-encoded query string from the array
         * @modifier Himanshu Vishwakarma
	 * @date 15-12-2025
         */
        $returnPolicyDetails = http_build_query($returnPolicyDetails);
        
        $shop = EtsyModule::etsyGetShopDetails();
        if(isset($shop['shop_id']) && !empty($returnPolicy['return_policy_id'])){
            $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/policies/return/' . $returnPolicy['return_policy_id'];
            $etsyRequestMethod = 'PUT';
            $response = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $returnPolicyDetails);
            if (!empty($response) && isset($response['return_policy_id'])) {
                EtsyModule::auditLogEntry('Return policy updation on etsy completed. Return Policy ID: ' . $response['return_policy_id'], $method_name);
                return true;
            } else {
                EtsyModule::auditLogEntry("Error in updating the return policy on etsy: " . (isset($response['error']) ? $response['error'] : 'Unknown error'), $method_name);
                return false;
            }
        } else {
            EtsyModule::auditLogEntry("Error in updating the return policy on etsy: " . (isset($shop['error']) ? $shop['error'] : 'Shop ID or Return Policy ID not found'), $method_name);
            return false;
        }
    }

    /** Delete Return Policy from Etsy */
    public static function deleteReturnPolicy($returnPolicy)
    {
        $method_name = 'SyncReturnPolicy::deleteReturnPolicy()';
        EtsyModule::auditLogEntry('Deleting the return policy from Etsy. Return Policy ID: ' . (isset($returnPolicy['return_policy_id']) ? $returnPolicy['return_policy_id'] : 'N/A'), $method_name);
        $shop = EtsyModule::etsyGetShopDetails();
        if(isset($shop['shop_id']) && !empty($returnPolicy['return_policy_id'])){
            $etsyRequestURI = '/shops/' . $shop['shop_id'] . '/policies/return/' . $returnPolicy['return_policy_id'];
            $etsyRequestMethod = 'DELETE';
            $etsyQueryString = array();
            $response = EtsyModule::etsyGetResponse($etsyRequestURI, $etsyRequestMethod, $etsyQueryString);
            if (!empty($response) && isset($response['error'])) {
                EtsyModule::auditLogEntry("Error in deleting the return policy from etsy: " . $response['error'], $method_name);
                return false;  
            } else {
                Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_return_policy WHERE return_policy_id = '" . pSQL($returnPolicy['return_policy_id']) . "' AND id_etsy_return_policy = '" . (int) $returnPolicy['id_etsy_return_policy'] . "'");
                EtsyModule::auditLogEntry('Return policy deleted from etsy. Return Policy ID: ' . $returnPolicy['return_policy_id'], $method_name);
                return true;
            }
        } else {
            EtsyModule::auditLogEntry("Error in deleting the return policy from etsy: " . (isset($shop['error']) ? $shop['error'] : 'Shop ID or Return Policy ID not found'), $method_name);
            return false;
        }
    }
}

