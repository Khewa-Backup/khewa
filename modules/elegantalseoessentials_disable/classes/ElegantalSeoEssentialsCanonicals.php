<?php
/**
 * @author    ELEGANTAL <info@elegantal.com>
 * @copyright (c) 2023, ELEGANTAL <www.elegantal.com>
 * @license   Proprietary License - It is forbidden to resell or redistribute copies of the module or modified copies of the module.
 */

/**
 * This is an object model class used to manage custom canonical URLs
 */
class ElegantalSeoEssentialsCanonicals extends ElegantalSeoEssentialsObjectModel
{

    public $replicate_all_languages = false;
    public $tableName = 'elegantalseoessentials_canonicals';
    public static $definition = array(
        'table' => 'elegantalseoessentials_canonicals',
        'primary' => 'id_elegantalseoessentials_canonicals',
        'multilang' => true,
        'multishop' => true,
        'fields' => array(
            'old_url' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'lang' => true),
            'new_url' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'lang' => true),
            'is_active' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'created_at' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        if (!$id || empty($this->created_at) || $this->created_at == '0000-00-00 00:00:00') {
            $this->created_at = date('Y-m-d H:i:s');
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
