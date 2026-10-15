<?php
/**
 * 2010-2021 Webkul.
 *
 * NOTICE OF LICENSE
 *
 * All right is reserved,
 * Please go through LICENSE.txt file inside our module
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please refer to CustomizationPolicy.txt file inside our module for more information.
 *
 * @author Webkul IN
 * @copyright 2010-2021 Webkul IN
 * @license LICENSE.txt
 */
class WkEntityRestoreHistory extends ObjectModel
{
    public $type;
    public $id_old_entity;
    public $id_new_entity;
    public $date_del;
    public $date_res;

    // const WK_PRODUCT_TYPE = 1;
    // const WK_CATEGORY_TYPE = 2;
    // const WK_BRAND_TYPE = 3;
    // const WK_SUPPLIER_TYPE = 4;
    // const WK_CUSTOMER_TYPE = 5;
    // const WK_ATTRIBUTE_Group_TYPE = 6;
    // const WK_ATTRIBUTE_TYPE = 7;
    // const WK_FEATURE_TYPE = 8;

    public static $definition = [
        'table' => 'wk_entity_restore_history',
        'primary' => 'id_wk_entity_restore_history',
        'fields' => [
            'type' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_old_entity' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_new_entity' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'date_del' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true],
            'date_res' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ],
    ];

    public function getIdByOldEntityId($oldEntityId, $type)
    {
        if ($oldEntityId) {
            return Db::getInstance()->getValue(
                'SELECT `id_wk_entity_restore_history`, `id_new_entity`
                FROM `' . _DB_PREFIX_ . 'wk_entity_restore_history`
                WHERE `id_old_entity` = ' . (int) $oldEntityId .
                    ' AND `type` = ' . (int) $type
            );
        }

        return false;
    }

    public function getIdByOldEntityAndType($oldEntityId, $type)
    {
        if ($oldEntityId) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'wk_entity_restore_history`
                WHERE `id_old_entity` = ' . (int) $oldEntityId .
                    ' AND `type` = ' . (int) $type
            );
        }

        return false;
    }

    public function addEntityHistory($type, $oldId)
    {
        $isAlready = $this->getIdByOldEntityAndType($oldId, $type);
        if (empty($isAlready)) {
            $objEntityRestoreHistory = new WkEntityRestoreHistory();
            $objEntityRestoreHistory->type = (int) $type;
            $objEntityRestoreHistory->id_old_entity = (int) $oldId;
            $objEntityRestoreHistory->date_del = date('Y-m-d H:i:s');
            $objEntityRestoreHistory->save();
        } else {
            $objEntityRestoreHistory = new WkEntityRestoreHistory($isAlready['id_wk_entity_restore_history']);
            $objEntityRestoreHistory->id_new_entity = 0;
            $objEntityRestoreHistory->date_del = date('Y-m-d H:i:s');
            $objEntityRestoreHistory->save();
        }
    }

    public function updateEntityHistory($historyId, $newId)
    {
        if ($historyId) {
            $objEntityRestoreHistory = new WkEntityRestoreHistory($historyId);
            $objEntityRestoreHistory->id_new_entity = $newId;
            $objEntityRestoreHistory->date_res = date('Y-m-d H:i:s');
            $objEntityRestoreHistory->save();
        }
    }
}
