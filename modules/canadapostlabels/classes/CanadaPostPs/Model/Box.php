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

use \ObjectModel;
use \Db;
use \PrestaShopDatabaseException;
use \PrestaShopException;

class Box extends \ObjectModel implements \CanadaPost\BoxPacker\Box
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var float
     */
    public $width;

    /**
     * @var float
     */
    public $height;

    /**
     * @var float
     */
    public $length;

    /**
     * @var float
     */
    public $weight;

    /**
     * @var float
     */
    public $cube;

    /**
     * @var int
     */
    public $active;

    /** @var int Max weight per box in grams */
    public $maxWeight = 30000;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_box',
        'primary' => 'id_box',
        'fields' => array(
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
            'width' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'required' => true, 'size' => 32),
            'height' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'required' => true, 'size' => 32),
            'length' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'required' => true, 'size' => 32),
            'weight' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'required' => true, 'size' => 32),
            'cube' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'size' => 32),
            'active' => array('type' => self::TYPE_INT, 'validate' => 'isBool', 'required' => true, 'size' => 10),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public function save($null_values = false, $auto_date = true)
    {
        $this->name = pSQL($this->name);
        if (null !== $this->width) {
            $this->width = (float)number_format($this->width, 1, '.', '');
        }
        if (null !== $this->height) {
            $this->height = (float)number_format($this->height, 1, '.', '');
        }
        if (null !== $this->length) {
            $this->length = (float)number_format($this->length, 1, '.', '');
        }
        if (null !== $this->weight) {
            $this->weight = (float)number_format($this->weight, 3, '.', '');
        }
        $this->cube = (float)$this->length * $this->width * $this->height;
        return parent::save($null_values, $auto_date);
    }

    /*
     * Return all boxes
     * @return array
     *  */
    public static function getBoxes($where = false)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    /*
     * Return one box
     * @return array
     * */
    public static function getBox($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }

    /**
     * Convert dimensions to unit without saving to DB
     * @var $unit string
     * @return Box
     * */
    public function convertDimensionsToUnit($unit)
    {
        $Tools = new Tools();
        $method = 'to'.\Tools::ucfirst($unit);
        if (method_exists($Tools, $method)) {
            foreach (array('length', 'width', 'height') as $dimension) {
                $this->{$dimension} = \CanadaPostPs\Tools::$method($this->{$dimension});
            }
            $this->cube = (float)$this->length * $this->width * $this->height;
        }
        return $this;
    }

    /*
     * Return largest box
     * @return array
     * */
    public static function getLargestBox($active = false)
    {
        return \Db::getInstance()->getRow(
            'SELECT *, MAX(cube) as cube FROM ' . _DB_PREFIX_ . self::$definition['table'] . ($active ? ' WHERE `active` = 1' : '') . ' GROUP BY id_box ORDER BY cube DESC'
        );
    }

    /**
     * Reference for box type (e.g. SKU or description).
     *
     * @return string
     */
    public function getReference()
    {
        return $this->name;
    }

    /**
     * Outer width in mm.
     *
     * @return int
     */
    public function getOuterWidth()
    {
        return Tools::toMm($this->width);
    }

    /**
     * Outer length in mm.
     *
     * @return int
     */
    public function getOuterLength()
    {
        return Tools::toMm($this->length);
    }

    /**
     * Outer depth in mm.
     *
     * @return int
     */
    public function getOuterDepth()
    {
        return Tools::toMm($this->height);
    }

    /**
     * Empty weight in g.
     *
     * @return int
     */
    public function getEmptyWeight()
    {
        return Tools::toG($this->weight);
    }

    /**
     * Inner width in mm.
     *
     * @return int
     */
    public function getInnerWidth()
    {
        return Tools::toMm($this->width);
    }

    /**
     * Inner length in mm.
     *
     * @return int
     */
    public function getInnerLength()
    {
        return Tools::toMm($this->length);
    }

    /**
     * Inner depth in mm.
     *
     * @return int
     */
    public function getInnerDepth()
    {
        return Tools::toMm($this->height);
    }

    /**
     * Total inner volume of packing in mm^3.
     *
     * @return int
     */
    public function getInnerVolume()
    {
        return Tools::toMm($this->width) *  Tools::toMm($this->length) *  Tools::toMm($this->height);
    }

    /**
     * Girth (sum of 2 shortest dimensions * 2)
     *
     * @return string
     */
    public function getGirth()
    {
        $dimensions = array(Tools::toCm($this->width), Tools::toCm($this->length), Tools::toCm($this->height));
        asort($dimensions, SORT_NUMERIC);
        $two_shortest_sides = (array_slice($dimensions, 0, 2, true));
        return (array_sum($two_shortest_sides) * 2);
    }

    /**
     * Max weight the packaging can hold in g.
     *
     * @return int
     */
    public function getMaxWeight()
    {
        return $this->maxWeight;
    }
}
