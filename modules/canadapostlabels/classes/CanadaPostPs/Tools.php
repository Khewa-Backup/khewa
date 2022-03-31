<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

namespace CanadaPostPs;

use CanadaPostWs\Type\Rating\RatingInfoType;
use \Country;
use \Db;
use DateTime;
use \Configuration;
use \PrestaShopException;
use \Currency;
use Exception;
use \State;

class Tools extends \Tools
{
    public static $units = array(
        'DIMENSION' => array(
            'mm' => array('mm', 'mms', 'millimeter', 'millimetre', 'millimeters', 'millimetres'),
            'cm' => array('cm', 'cms', 'centimeter', 'centimetre', 'centimeters', 'centimetres'),
            'm' => array('m', 'meter', 'metre', 'meters', 'metres'),
            'in' => array('in', 'ins', 'inc', 'inch', 'inches'),
            'ft' => array('ft', 'feet', 'f', 'foot'),
            'yd' => array('yd', 'y', 'yard', 'yrd', 'yards', 'yrds', 'yds', 'ys'),
        ),
        'WEIGHT' => array(
            'g' => array('g', 'gram', 'grams', 'gs', 'gms', 'gm'),
            'mg' => array('mg', 'milligram', 'milligrams', 'mgs'),
            'kg' => array('kg', 'kgs', 'kilogram', 'kilograms'),
            'lbs' => array('lb', 'lbs', 'pound', 'pounds'),
            'oz' => array('oz', 'ounce', 'ounces'),
        )
    );

    public static $conversions = array(
        'mm' => array(
            'mm' => 1,
            'cm' => 10,
            'm' => 1000,
            'in' => 25.40,
            'ft' => 304.8,
            'yd' => 914.4,
        ),
        'cm' => array(
            'mm' => 0.1,
            'cm' => 1,
            'm' => 100,
            'in' => 2.54,
            'ft' => 30.48,
            'yd' => 91.44,
        ),
        'in' => array(
            'mm' => 0.03937007874,
            'cm' => 0.3937007874,
            'm' => 39.37007874,
            'in' => 1,
            'ft' => 12,
            'yd' => 396,
        ),
        'g' => array(
            'g' => 1,
            'mg' => 0.001,
            'kg' => 1000,
            'lbs' => 453.592370,
            'oz' => 28.349523125,
        ),
        'kg' => array(
            'g' => 0.001,
            'mg' => 0.000001,
            'kg' => 1,
            'lbs' => 0.453592370,
            'oz' => 0.0283495231,
        ),
        'lbs' => array(
            'g' => 0.00220462262185,
            'mg' => 0.0000022046226218,
            'kg' => 2.20462262185,
            'lbs' => 1,
            'oz' => 0.0625,
        ),
    );

    public static $error_messages = array(
        'TOKEN_CANCEL' => 'This module will be unable to submit requests on your behalf until you accept the terms and conditions, but your relationship with Canada Post remains in effect. You may try again.',
        'TOKEN_SERVICE_UNAVAILABLE' => 'The Canada Post service is currently unavailable, please try again later.',
        'TOKEN_FAILURE' => 'Failure to connect with Canada Post. Your token might have expired, please refresh the page to generate a new one. Your account might also have already been connected. Please try again later.',
        'TOKEN_CONTACT_SUPPORT' => 'Error retrieving token. Please contact the module creator at me@zackhussain.ca and provide this error message.',
        'TOKEN_MISSING' => 'Resolve errors to connect. Token is required. Try closing this window and re-accessing it or reinstalling the module.',
        'TOKEN_ERROR' => 'Error retrieving token from Canada Post platform provider (zhmedia.ca): "%s". Please contact your webhost, developer, or the module developer to fix this issue.',

        'MERCHANT_INFO_ERROR' => 'Error retrieving merchant information from Canada Post platform provider (zhmedia.ca): "%s". Please contact your webhost, developer, or the module developer to fix this issue.',

        'CONNECT_ACCOUNT' => 'Please connect your Canada Post account in the module configuration page to use this feature.',
        'CONTRACT_ONLY' => 'This feature is for Commercial Contract Canada Post accounts only.',

        'INVALID_UNITS' => 'Invalid %s unit "%s", must be one of: %s. Your store units can be changed in the "Localization" menu.',
        'MISSING_CURRENCY' => 'The currency CAD must be added in Localization > Currencies and can be either enabled or disabled. This is needed for the conversion rate.',
        'MISSING_BOX' => 'At least 1 box must entered.',
        'MISSING_GROUP' => 'At least 1 group must entered.',
        'MISSING_ADDRESS' => 'At least 1 address must entered.',
        'REQUIRED_FIELD' => '%s is required.',
        'REQUIRED_CONDITIONAL_FIELD' => '%s is required when %s is selected.',

        'CONFLICTING_STATUSES' => '"Delivered Order Status" cannot be included in "Order Statuses to Track".',
        'CONFLICTING_DISCOUNTS' => 'A discount for this carrier already exists for at least one of the selected shops.',

        'CANNOT_DELETE_ORIGIN' => 'You cannot delete your Origin address. Please set another address as Origin before deleting.',
        'CANNOT_DELETE_GROUP' => 'You need at least 1 Group. Please add another Group before deleting.',

        'API_TEST_FAILED' => 'Canada Post Web Service Connection Test Failed: ',
        'PRODUCTS_DONT_FIT_BOX' => 'Box Sizing Warning: the following items do not fit in any of your boxes. The module will not be able to use dynamic box packing when these products are present in a cart; the rates will instead be calculated using (1) of the largest active box (%s).',

        'FILE_UPLOAD' => 'An error occurred while attempting to upload the file.',

        'RATES_NOT_RETURNED' => 'Canada Post: could not retrieve rates for %s in cart #%s. The products/boxes may be too large for the carrier(s).',

        'CANNOT_PRINT' => 'Cannot print a label for a shipment that has been voided or transmitted.',
        'CANNOT_VOID' => 'Cannot void a shipment that has already been voided or transmitted, please try refunding it instead.',
        'CANNOT_REFUND' => 'Cannot refund a shipment that has already been refunded or voided.',
        'NO_REFUND_LINK' => 'Cannot refund shipment #%s because it is non-refundable. It may have already been refunded, please check your Canada Post billing history to verify the status of the shipment.',
        'NO_REFUND_EMAIL' => 'You must enter a Label Refund Email in the module configuration page to refund shipments.',
        'CANNOT_BULK_PRINT' => 'No labels were able to be printed.',

        'CANNOT_GET_SHIPMENTS' => 'An error occurred and no shipments were returned.',
        'CANNOT_GET_SHIPMENT_DETAILS' => 'Cannot get shipment details for shipment %s.',

        'CANNOT_GET_MANIFESTS' => 'An error occurred and no manifests were returned.',
        'CANNOT_GET_MANIFEST_DETAILS' => 'Cannot get manifest details for manifest %s.',

        'CANNOT_PRINT_BATCH' => 'Batch PDF file not found, please look for it in the following directory: %s.',
        'BATCH_ERRORS' => 'Some labels were unable to be created due to errors, please fix the errors and try again. The successful labels from batch #%s can be printed from the View Batches page.',
    );

    /*
     * Check USPS API settings and service availability
     * @throws PrestaShopException
     * */
    public static function checkApi($prefix)
    {
        if (!\Configuration::get($prefix.'PROD_API_PASS') ||
            !\Configuration::get($prefix.'PROD_API_USER') ||
            !\Configuration::get($prefix.'CUSTOMER_NUMBER') ||
            !\Validate::isLoadedObject(Address::getOriginAddress())
        ) {
            return false;
        }

        $API = new API();

        $senderAddress = Address::getOriginAddress();
        $destinationAddress = new \Address();
        $destinationAddress->postcode = 'K1N9J7';
        $destinationAddress->id_country = \Country::getByIso('CA');
        $boxes = Box::getBoxes(array('active' => 1));
        if (!empty($boxes)) {
            $Box = new Box($boxes[0]['id_box']);
            $Box->convertDimensionsToUnit('cm');
        } else {
            throw new PrestaShopException('You must add at least one active box in the Boxes preferences.');
        }

        $Rating = $API->getRates(array('DOM.EP'), $senderAddress, $destinationAddress, $Box, '0.100', array());

        try {
            if ($Rating->isSuccess() && $Rating->getResponse() instanceof RatingInfoType) {
                return true;
            } else {
                throw new PrestaShopException(self::formatErrorMessage($Rating->getErrorMessage()));
            }
        } catch (Exception $e) {
            throw new PrestaShopException($e->getMessage());
        }
    }

    /*
     * Convert dimensions from store's unit
     * @throws PrestaShopException
     * */
    public static function toMm($unit)
    {
        return self::convertUnit($unit, 'DIMENSION', 'mm');
    }

    /*
     * Convert dimensions from store's unit
     * @throws PrestaShopException
     * */
    public static function toCm($unit)
    {
        return self::convertUnit($unit, 'DIMENSION', 'cm');
    }

    /*
     * Convert dimensions from store's unit
     * @throws PrestaShopException
     * */
    public static function toIn($unit)
    {
        return self::convertUnit($unit, 'DIMENSION', 'in');
    }

    /*
     * Convert weight from store's unit
     * @throws PrestaShopException
     * */
    public static function toG($unit)
    {
        return self::convertUnit($unit, 'WEIGHT', 'g', 3);
    }

    /*
     * Convert weight from store's unit
     * @throws PrestaShopException
     * */
    public static function toKg($unit)
    {
        return self::convertUnit($unit, 'WEIGHT', 'kg', 3);
    }

    /*
     * Convert weight from store's unit
     * @throws PrestaShopException
     * */
    public static function toLbs($unit)
    {
        return self::convertUnit($unit, 'WEIGHT', 'lbs', 3);
    }

    /*
     * Convert store dimensions to MM
     * @throws PrestaShopException
     * */
    public static function convertUnit($unit, $type, $to = 'mm', $decimals = 1)
    {
        $store_unit = Tools::strtolower(\Configuration::get('PS_'.$type.'_UNIT'));
        foreach (self::$units[$type] as $u => $labels) {
            if (in_array($store_unit, $labels) && array_key_exists($to, self::$conversions)) {
                return number_format($unit * self::$conversions[$to][$u], $decimals, '.', '');
            }
        }
        throw new PrestaShopException('Invalid unit of measurement.');
    }

    /*
     * Convert any dimensions
     * */
    public static function convertUnitFromTo($amount, $from, $to, $decimals = 1)
    {
        return number_format($amount*self::$conversions[$to][$from], $decimals, '.', '');
    }

    /* Return the conversion rate from CAD */
    public static function getConversionRateFromCad($currency)
    {
        $id_cad = \Db::getInstance()->getValue('SELECT `id_currency` FROM `'._DB_PREFIX_.'currency` WHERE `iso_code` = "CAD"');

        $conversionRate = 1;
        $currencyOrigin = new Currency((int)$id_cad);
        $conversionRate /= $currencyOrigin->conversion_rate;
        $conversionRate *= $currency->conversion_rate;
        return number_format($conversionRate, 3, '.', '');
    }

    /*
     * Get proper unit of measurement based on self::$units
     * e.g. "lb" should return "lbs"
     * @param string $unit
     * @return string
     * @throws PrestaShopException
     * */
    public static function getUnit($unit)
    {
        foreach (self::$units as $type => $units) {
            foreach ($units as $unitName => $unitNameVariants) {
                if (in_array($unit, $unitNameVariants)) {
                    return $unitName;
                }
            }
        }
        throw new PrestaShopException('Invalid unit of measurement.');
    }

    /*
     * Check if table exists
     *
     * @var string|array
     * @return bool
     * */
    public static function tableExists($table)
    {
        // Try a select statement against the table
        // Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
        try {
            $table = _DB_PREFIX_ . $table;
            $result = \Db::getInstance()->executeS("SELECT 1 FROM $table LIMIT 1");
        } catch (\PrestaShopDatabaseException $e) {
            // We got an exception == table not found
            return false;
        }

        // Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
        return $result !== false;
    }

    /*
     * Format and sanitize Where clause
     *
     * @var string|array
     * @return string
     * */
    public static function sanitizeWhere($where)
    {
        $whereStr = '';
        if ($where && is_array($where)) {
            $whereStr = ' WHERE '. implode(' AND ', array_map(function ($v, $k) {
                return '`'.pSQL($k).'` = '.(is_int($v) ? (int)$v : '"'.pSQL($v).'"').'';
            }, $where, array_keys($where)));
        } elseif ($where && is_string($where)) {
            $whereStr = ' WHERE '.strip_tags($where);
        }
        return $whereStr;
    }

    public static function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public static function daysUntilDate($date)
    {
        if (!Tools::validateDate($date)) {
            return false;
        }

        $a = new DateTime(date('Y-m-d h:i:s'));
        $b = new DateTime($date.' '.date('h:i:s'));
        return $a->diff($b)->days;
    }

    /*
     * Makes error messages from Canada Post more human readable
     * e.g. Removes strings like "{http://www.canadapost.ca/ws/ship/rate-v3}" and "/rs/ship/price: cvc-simple-type 1:"
     * @param string $message
     * @return string
     * */
    public static function formatErrorMessage($message)
    {
        return preg_replace('/^(?:\/.+?}|\/.+?:.+?:)(.+)(?:{.+?})(.+)/', '$1$2', $message);
    }

    /**
     * @var Exception $e
     * @return string
     * */
    public static function formatException(Exception $e)
    {
        return sprintf('%s in file %s at line %s', $e->getMessage(), $e->getFile(), $e->getLine());
    }

    /*
     * Merge serialized string into $_GET
     * */
    public static function parseAndMergeGet($string)
    {
        $params = array();
        parse_str($string, $params);
        $_GET = array_merge($_GET, $params);
    }

    /* If array keys are numeric, it means there's more than one of the same key. Return count of a key */
    public static function countArray($array)
    {
        $count = 0;
        foreach ($array as $k => $v) {
            if (is_numeric($k)) {
                $count++;
            } else {
                return $count += 1;
            }
        }
        return $count;
    }

    public static function is_asso($a)
    {
        foreach (array_keys($a) as $key) {
            if (!is_int($key)) {
                return true;
            }
        }
        return false;
    }

    public static function isCanadianPostalCode($postcode)
    {
        return preg_match('/^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$/', Tools::strtoupper($postcode));
    }

    /**
     * @var string|bool $values
     * @return array|bool
     * */
    public static function getMultiSelectValues($values)
    {
        if ($values && !empty($values)) {
            return explode(',', $values);
        } else {
            return false;
        }
    }

    /**
     * @var array|bool $values
     * @return string|bool
     * */
    public static function setMultiSelectValues($values)
    {
        if ($values && !empty($values)) {
            return implode(',', $values);
        } else {
            return false;
        }
    }

    /**
     * Get proper encoding for PDF files
     * */
    public static function strlenPDF($str)
    {
        $encoding = 'UTF-8';
        if (function_exists('mb_detect_encoding')) {
            $encoding = mb_detect_encoding($str);
        }

        return \Tools::strlen($str, $encoding);
    }

    public static function getDirectorySize($path)
    {
        $megaBytesTotal = 0;
        $path = realpath($path);
        if ($path!==false && $path!='' && file_exists($path)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $object) {
                $megaBytesTotal += $object->getSize();
            }
        }
        if ($megaBytesTotal > 0) {
            $megaBytesTotal = $megaBytesTotal / 1024 / 1024;
        }
        return number_format($megaBytesTotal, 2);
    }

    public static function getTableSize($table)
    {
        $result = \Db::getInstance()->executeS('SHOW TABLE STATUS WHERE `name` = "'._DB_PREFIX_.$table.'"');

        if ($result) {
            $megaBytesTotal = $result[0]['Data_length'] + $result[0]['Index_length'];
        } else {
            return false;
        }

        if ($megaBytesTotal > 0) {
            $megaBytesTotal = $megaBytesTotal / 1024 / 1024;
        }
        return number_format($megaBytesTotal, 2);
    }

    /*
        Post request
    */
    public static function call($url, $postfields)
    {
        try {
            $handle = curl_init();

            curl_setopt($handle, CURLOPT_URL, $url);
            curl_setopt($handle, CURLOPT_POST, true);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $postfields);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);

            $curl = curl_exec($handle);
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            curl_close($handle);

            if ($httpCode != 200) {
                throw new Exception($httpCode);
            }
        } catch (Exception $e) {
            die("Connection to ZH Media failed. " . $e->getMessage());
        }

        return json_decode($curl);
    }

    public static function verify($prefix, $email, $serial)
    {
        $postfields = array(
            "email" => Tools::safeOutput($email),
            "serial" => Tools::safeOutput($serial),
            "download" => \Configuration::get($prefix.'DOWNLOAD_ID'),
        );

        $url = 'https://zhmedia.ca/genuine/verify.php';

        $verify = Tools::call($url, $postfields);

        $response = array();
        if (!$verify->error) {
            $response['status'] = 1;
            $response['message'] = $verify->success;
            \Configuration::updateGlobalValue($prefix.'VS', $serial);
            \Configuration::updateGlobalValue($prefix.'VE', $email);
        } else {
            $response['status'] = 0;
            $response['message'] = $verify->error;
        }
        return $response;
    }

    public static function update($download, $version)
    {
        $postfields = array(
            "module_id" => Tools::safeOutput($download),
            "version" => Tools::safeOutput($version),
        );

        $url = 'https://zhmedia.ca/genuine/update.php';

        $update = Tools::call($url, $postfields);

        if (!$update->error) {
            if ($update->update) {
                return $update->update;
            }
            return false;
        } else {
            return $update->error;
        }
    }

    /*
     * Migrate old module database tables to the new ones
     * Old tables: cpl_boxes, cpl_methods, cpl_groups
     * New Tables: cpl_box, cpl_method, cpl_group
     * @since 4.0.0
     * */
    public static function migrateLegacyTables()
    {
        if (Tools::tableExists('cpl_boxes') ||
            Tools::tableExists('cpr_boxes') ||
            Tools::tableExists('cpc_boxes')) {
            $table = '';
            // in order of importance. "cpc_" is the most important table to migrate from
            if (Tools::tableExists('cpc_boxes')) {
                $table = 'cpc_boxes';
            } elseif (Tools::tableExists('cpl_boxes')) {
                $table = 'cpl_boxes';
            } elseif (Tools::tableExists('cpr_boxes')) {
                $table = 'cpr_boxes';
            }
            $legacyBoxes = \Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.$table);
            foreach ($legacyBoxes as $legacyBox) {
                $box = array(
                    'id_box'   => $legacyBox['id_box'],
                    'name'     => $legacyBox['name'],
                    'width'    => $legacyBox['width'],
                    'height'   => $legacyBox['height'],
                    'length'   => $legacyBox['length'],
                    'weight'   => $legacyBox['weight'],
                    'cube'     => $legacyBox['cube'],
                    'active'   => 1,
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                );
                if (!\Db::getInstance()->insert('cpl_box', $box, false, true, \Db::ON_DUPLICATE_KEY)) {
                    return false;
                }
            }
        }
        if (Tools::tableExists('cpl_methods') ||
            Tools::tableExists('cpr_methods') ||
            Tools::tableExists('cpc_methods')) {
            $table = '';
            // in order of importance. "cpc_" is the most important table to migrate from
            if (Tools::tableExists('cpc_methods')) {
                $table = 'cpc_methods';
            } elseif (Tools::tableExists('cpl_methods')) {
                $table = 'cpl_methods';
            } elseif (Tools::tableExists('cpr_methods')) {
                $table = 'cpr_methods';
            }
            $legacyMethods = \Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.$table);
            foreach ($legacyMethods as $legacyMethod) {
                $method = array(
                    'id_method'          => $legacyMethod['id_method'],
                    'id_carrier'         => $legacyMethod['id_carrier'],
                    'id_carrier_history' => $legacyMethod['id_carrier_history'],
                    'name'               => $legacyMethod['name'],
                    'code'               => $legacyMethod['code'],
                    'group'              => $legacyMethod['group'],
                    'active'             => $legacyMethod['active'],
                    'date_add'           => date('Y-m-d H:i:s'),
                    'date_upd'           => date('Y-m-d H:i:s'),
                );
                if (!\Db::getInstance()->insert('cpl_method', $method, false, true, \Db::ON_DUPLICATE_KEY)) {
                    return false;
                }
            }
        }
        if (Tools::tableExists('cpl_groups') ||
            Tools::tableExists('cpc_groups')) {
            $table = '';
            // in order of importance. "cpc_" is the most important table to migrate from
            if (Tools::tableExists('cpc_groups')) {
                $table = 'cpl_groups';
            } elseif (Tools::tableExists('cpl_groups')) {
                $table = 'cpl_groups';
            }
            $legacyGroups = \Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.$table);
            foreach ($legacyGroups as $legacyGroup) {
                $method = array(
                    'id_group'          => $legacyGroup['id_group'],
                    'name'          => $legacyGroup['name'],
                    'active'             => 1,
                    'date_add'           => date('Y-m-d H:i:s'),
                    'date_upd'           => date('Y-m-d H:i:s'),
                );
                if (!\Db::getInstance()->insert('cpl_group', $method, false, true, \Db::ON_DUPLICATE_KEY)) {
                    return false;
                }
            }
        }
        return true;
    }

    /* Populate Methods DB with service methods */
    public static function installMethods()
    {
        $methodsArr = Method::getMethods();
        if (empty($methodsArr)) {
            foreach (Method::$shipping_methods as $type => $methods) {
                foreach ($methods as $id => $name) {
                    $m         = new Method();
                    $m->name   = pSQL($name);
                    $m->code   = pSQL($id);
                    $m->group  = pSQL($type);
                    $m->active = 0;
                    if (!$m->save()) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public static function installAddress()
    {
        $addresses = Address::getAddresses();
        if (empty($addresses)) {
            $address = new Address();
            $address->name = pSQL('Default Address');
            $address->address1 = pSQL('123 Demo St');
            $address->company = pSQL('Demo Inc.');
            $address->city = pSQL('Ottawa');
            $address->postcode = pSQL('K1P5A0');
            $address->phone = pSQL('5555555555');
            $address->origin = (int)1;
            $address->active = (int)1;
            $address->id_country = \Country::getByIso('CA');
            $address->id_state = \State::getIdByIso('ON');

            return $address->save();
        }
        return true;
    }

    public static function installBox()
    {
        $boxes = Box::getBoxes();
        if (empty($boxes)) {
            $box = new Box();
            $box->name = pSQL('Default Box');
            $box->width = (float)'10.0';
            $box->height = (float)'10.0';
            $box->length = (float)'10.0';
            $box->weight = (float)'0.100';
            $box->active = (int)1;
            $box->cube = (float)'1000.0';

            return $box->save();
        }
        return true;
    }

    public static function installGroup()
    {
        $groups = Group::getGroups();
        if (empty($groups)) {
            $group = new Group();
            $group->name = pSQL('Default');
            $group->active = (int)1;

            return $group->save();
        }
        return true;
    }


    /**
     * @param string $tag
     * @param string $content
     * @param array $attr
     * @param bool $closingTag
     *
     * @return bool|string
     * @throws \SmartyException
     */
    public static function renderHtmlTag($tag, $content = null, $attr = array(), $closingTag = true)
    {
        $Context = \Context::getContext();

        if (in_array($tag, array('br', 'hr'))) {
            $closingTag = false;
        }

        $Context->smarty->assign(array(
            'tag' => $tag,
            'htmlContent' => $content,
            'attributes' => $attr,
            'closingTag' => $closingTag
        ));

        return $Context->smarty->fetch(_PS_MODULE_DIR_. 'canadapostlabels/views/templates/hook/tag.tpl');
    }

    /**
     * @param string $href
     * @param string $text
     * @param array $attr
     *
     * @return bool|string
     * @throws \SmartyException
     */
    public static function renderHtmlLink($href, $text = null, $attr = array())
    {
        $Context = \Context::getContext();

        if (null === $text) {
            $text = $href;
        }

        $Context->smarty->assign(array(
            'href' => $href,
            'text' => $text,
            'attributes' => $attr
        ));

        return $Context->smarty->fetch(_PS_MODULE_DIR_. 'canadapostlabels/views/templates/hook/link.tpl');
    }
}
