<?php
/**
 * @author    ELEGANTAL <info@elegantal.com>
 * @copyright (c) 2023, ELEGANTAL <www.elegantal.com>
 * @license   Proprietary License - It is forbidden to resell or redistribute copies of the module or modified copies of the module.
 */

/**
 * This is an object model class used to manage redirects
 */
class ElegantalSeoEssentialsRedirects extends ElegantalSeoEssentialsObjectModel
{

    public $tableName = 'elegantalseoessentials_redirects';
    public static $definition = array(
        'table' => 'elegantalseoessentials_redirects',
        'primary' => 'id_elegantalseoessentials_redirects',
        'multishop' => true,
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'old_url' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'new_url' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'redirect_type' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'is_active' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'created_at' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'expires_at' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        if (!$id || empty($this->created_at) || $this->created_at == '0000-00-00 00:00:00') {
            $this->created_at = date('Y-m-d H:i:s');
        }

        if ($this->expires_at == '0000-00-00 00:00:00') {
            $this->expires_at = null;
        }

        if (method_exists('Shop', 'addTableAssociation')) {
            Shop::addTableAssociation($this->tableName, array('type' => 'shop'));
        }
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
