<?php
/**
 * FMM Helpdesk Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author    FMM Modules
 * @copyright FMM Modules
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  FMM Modules
 * @package   FmmHelpdesk
 */

class Ticketstatus extends ObjectModel
{
    public $id;
    public $ticketstatus_id;
    public $ticketstatus_title;
    public $created_time;
    public $update_time;
    public $ticketstatus_status;
    
    public static $definition = array(
        'table' => 'fmm_hd_ticketstatus',
        'primary' => 'ticketstatus_id',
        'multilang' => true,
        'fields' => array(
                'ticketstatus_title'        =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => false, 'size' => 64),
                'ticketstatus_status'                   =>      array('type' => self::TYPE_BOOL),
                'created_time'      =>      array('type' => self::TYPE_DATE),
        ),
    );


    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
    }

    public function delete()
    {
        $res = Db::getInstance()->execute('
            DELETE FROM `'._DB_PREFIX_.'fmm_hd_ticketstatus`
            WHERE `ticketstatus_id` = '.(int)$this->ticketstatus_id);
        $res &= parent::delete();
        return $res;
    }

    public function deleteSelection($selection)
    {
        if (!is_array($selection)) {
            die(Tools::displayError());
        }

        $result = true;
        foreach ($selection as $id) {
            $this->id = (int)$id;
            $this->ticketstatus_id = Ticketstatus::getTicketStatus();
            $result = $result && $this->delete();
        }
        return $result;
    }

    public function getTicketStatus()
    {
        if (!(int)$this->id) {
            return false;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `ticketstatus_id` FROM '._DB_PREFIX_.'fmm_hd_ticketstatus WHERE `ticketstatus_id` = '.(int)$this->id);
    }

    public static function getStatusTitle($statusId)
    {
        $id_lang = (int)Context::getContext()->cookie->id_lang;
        return Db::getInstance()->getRow('
            SELECT t1.*, t2.`ticketstatus_title`
            FROM '._DB_PREFIX_.'fmm_hd_ticketstatus t1
            LEFT JOIN '._DB_PREFIX_.'fmm_hd_ticketstatus_lang t2
                ON (t1.ticketstatus_id = t2.ticketstatus_id AND t2.id_lang = '.(int)$id_lang.')
            WHERE t1.`ticketstatus_id` = '.(int)$statusId);
    }
}
