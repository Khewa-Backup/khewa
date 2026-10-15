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
class WkDeletedFeature extends ObjectModel
{
    public $id_feature;
    public $feature_name;
    public $position;
    public $shop;
    public $lang;
    public $product_feature;
    public $feature_value;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'wk_deleted_feature',
        'primary' => 'id_wk_deleted_feature',
        'fields' => [
            'id_feature' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'feature_name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'shop' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'lang' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'product_feature' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'feature_value' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
        ],
    ];

    public function getFeatureDetailBeforeDelete($idFeature)
    {
        if ($idFeature) {
            $featureInfo = $this->getFeature($idFeature);
            if ($featureInfo) {
                $featureInfo['feature_name'] = $this->getFeatureName(
                    $idFeature,
                    Configuration::get('PS_LANG_DEFAULT')
                );
                $shop = $this->getFeature($idFeature, true);
                if ($shop) {
                    $featureInfo['shop'] = json_encode($shop);
                }
                $lang = $this->getFeature($idFeature, false, true);
                if ($lang) {
                    $featureInfo['lang'] = json_encode($lang);
                }
                $productFeature = $this->getProductFeatureByFeatureId($idFeature);
                if ($productFeature) {
                    $featureInfo['product_feature'] = json_encode($productFeature);
                } else {
                    $featureInfo['product_feature'] = null;
                }
                $featureValue = $this->getFeatureValueByFeatureId($idFeature);
                if ($featureValue) {
                    $featureInfo['feature_value'] = json_encode($featureValue);
                } else {
                    $featureInfo['feature_value'] = null;
                }
                $this->saveDeletedFeature($featureInfo);
            }
            $objEntityRestoreHistory = new WkEntityRestoreHistory();
            $objEntityRestoreHistory->addEntityHistory(8, $idFeature);
        }
    }

    public function getFeature($idFeature, $isShop = false, $isLang = false)
    {
        if ($idFeature) {
            if ($isShop) {
                return Db::getInstance()->executeS(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'feature_shop`
                    WHERE `id_feature` = ' . (int) $idFeature
                );
            } elseif ($isLang) {
                $allLang = [];
                foreach (Language::getLanguages() as $lang) {
                    $allLang[$lang['id_lang']] = Db::getInstance()->getRow(
                        'SELECT * FROM `' . _DB_PREFIX_ . 'feature_lang`
                        WHERE `id_feature` = ' . (int) $idFeature .
                            ' AND `id_lang` = ' . (int) $lang['id_lang']
                    );
                }

                return $allLang;
            } else {
                return Db::getInstance()->getRow(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'feature`
                    WHERE `id_feature` = ' . (int) $idFeature
                );
            }
        }

        return false;
    }

    public function getFeatureName($idFeature, $idLang)
    {
        if ($idFeature) {
            return Db::getInstance()->getValue(
                'SELECT `name` FROM `' . _DB_PREFIX_ . 'feature_lang`
                WHERE `id_feature` = ' . (int) $idFeature .
                    ' AND `id_lang` = ' . (int) $idLang
            );
        }
    }

    public function getProductFeatureByFeatureId($idFeature)
    {
        if ($idFeature) {
            return Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'feature_product`
                WHERE `id_feature` = ' . (int) $idFeature
            );
        }
    }

    public function getFeatureValueByFeatureId($idFeature)
    {
        if ($idFeature) {
            $features = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'feature_value`
                WHERE `id_feature` = ' . (int) $idFeature
            );
            if ($features) {
                $allFeatures = [];
                foreach ($features as $feature) {
                    if ($feature['id_feature_value']) {
                        $feature['lang'] = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . 'feature_value_lang`
                            WHERE `id_feature_value` = ' . (int) $feature['id_feature_value']
                        );
                    }
                    array_push($allFeatures, $feature);
                }

                return $allFeatures;
            }
        }

        return false;
    }

    public function saveDeletedFeature($featureInfo)
    {
        if (!empty($featureInfo) && $featureInfo) {
            $objDeletedFeature = new WkDeletedFeature();
            $objDeletedFeature->id_feature = $featureInfo['id_feature'];
            $objDeletedFeature->feature_name = $featureInfo['feature_name'];
            $objDeletedFeature->position = $featureInfo['position'];
            $objDeletedFeature->shop = $featureInfo['shop'];
            $objDeletedFeature->lang = $featureInfo['lang'];
            $objDeletedFeature->product_feature = $featureInfo['product_feature'];
            $objDeletedFeature->feature_value = $featureInfo['feature_value'];
            $objDeletedFeature->save();
        }
    }

    public function getDeletedFeatureDetail($idDeletedFeature)
    {
        if ($idDeletedFeature) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'wk_deleted_feature`
                WHERE `id_wk_deleted_feature` = ' . (int) $idDeletedFeature
            );
        }

        return false;
    }

    public function setFeatureProduct($idFeature, $idFeatureValue, $idProduct)
    {
        if ($idFeature && $idFeatureValue && $idProduct) {
            return Db::getInstance()->insert('feature_product', [
                'id_feature' => (int) $idFeature,
                'id_product' => (int) $idProduct,
                'id_feature_value' => (int) $idFeatureValue,
            ]);
        }

        return false;
    }

    public static function insertDataInPrimaryTable($featureInfo)
    {
        if ($featureInfo) {
            return Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'feature` (`id_feature`, `position`)
                VALUES (' . (int) $featureInfo['id_feature'] . ', ' . (int) $featureInfo['position'] . ')'
            );
        }

        return false;
    }

    public static function insertDataWithOldId($featureVal, $idFeature)
    {
        if ($featureVal && $idFeature) {
            return Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'feature_value` (`id_feature_value`, `id_feature`)
                VALUES (' . (int) $featureVal . ', ' . (int) $idFeature . ')'
            );
        }

        return false;
    }
}
