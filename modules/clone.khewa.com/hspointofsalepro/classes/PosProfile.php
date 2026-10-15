<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 *
 */
class PosProfile extends Profile
{

    /**
     * default permission for manage
     * @var array
     */
    public static $manage_permissions = array(
        'dashboard',
        'salesReport',
        'commissionsReport',
        'orders',
        'setup'
    );

    /**
     * default permission for sale
     * @var array
     */
    public static $sale_permissions = array(
        'changeProductPrice',
        'giveDiscount',
        'completeOrder'
    );

    /**
     * @param int $id_lang
     *
     * @return array
     * <pre>
     * array(
     *  int => array(
     *      id_profile => int,
     *      name => string
     *  )
     * ...
     * )
     */
    public static function getProfiles($id_lang = null)
    {
        $profiles = parent::getProfiles($id_lang);
        $select_id_profile = explode(',', Configuration::get('POS_ID_PROFILES'));
        if (!empty($profiles)) {
            foreach ($profiles as &$profile) {
                $profile['checked'] = in_array($profile['id_profile'], $select_id_profile) ? 1 : 0;
            }
        }
        return $profiles;
    }

    /**
     *
     * @param array $profiles
     * @return array
     * <pre />
     * array(
     *  sale => array(
     *      string => boolean
     *  ),
     *  manage => array(
     *      string => boolean
     *  )
     */
    public static function getDefaultPermissions(array $profiles)
    {
        $permissions = array();
        foreach (self::$sale_permissions as $sale_permission) {
            foreach ($profiles as $profile) {
                $permissions['sales'][$sale_permission][$profile['id_profile']] = 1;
            }
        }
        foreach (self::$manage_permissions as $manage_permission) {
            foreach ($profiles as $profile) {
                $permissions['manages'][$manage_permission][$profile['id_profile']] = 1;
            }
        }
        return $permissions;
    }

    /**
     *
     * @param array $initial_permissions
     * @return array
     * <pre />
     * array(
     *  sale => array(
     *      string => boolean
     *  ),
     *  manage => array(
     *      string => boolean
     *  )
     */
    public static function formatPermissions(array $initial_permissions)
    {
        $permissions = array();
        foreach (self::$sale_permissions as $sale_permission) {
            $permissions['sales'][$sale_permission] = $initial_permissions['sales'][$sale_permission];
        }
        foreach (self::$manage_permissions as $manage_permission) {
            $permissions['manages'][$manage_permission] = $initial_permissions['manages'][$manage_permission];
        }
        return $permissions;
    }
}
