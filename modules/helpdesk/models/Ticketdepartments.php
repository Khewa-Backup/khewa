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

class Ticketdepartments extends ObjectModel
{
    public $id;
    public $departments_id;
    public $department_title;
    public $created_time;
    public $update_time;
    public $department_status;
    public $department_email;
    public $department_signature;
    public $department_type;
    
    public static $definition = array(
        'table' => 'fmm_hd_departments',
        'primary' => 'departments_id',
        'multilang' => true,
        'fields' => array(
                'department_title'      =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => false, 'size' => 64),
                'department_email'      =>      array('type' => self::TYPE_STRING),
                'department_signature'  =>      array('type' => self::TYPE_STRING),
                'department_status'     =>      array('type' => self::TYPE_INT),
                'created_time'          =>      array('type' => self::TYPE_DATE),
        ),
    );


    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
    }

    public function delete()
    {
        $res = Db::getInstance()->execute('
            DELETE FROM `'._DB_PREFIX_.'fmm_hd_departments`
            WHERE `departments_id` = '.(int)$this->departments_id);
        $res &= parent::delete();
        return $res;
    }

    /**
     * Delete several objects from database
     *
     * return boolean Deletion result
     */
    public function deleteSelection($selection)
    {
        if (!is_array($selection)) {
            die(Tools::displayError());
        }
        $result = true;
        foreach ($selection as $id) {
            $this->id = (int)$id;
            $this->departments_id = Ticketdepartments::getTicketDepartments();
            $result = $result && $this->delete();
        }

        return $result;
    }
    
    public function getTicketDepartments()
    {
        if (!(int)$this->id) {
            return false;
        }
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `departments_id` FROM '._DB_PREFIX_.'fmm_hd_departments WHERE `departments_id` = '.(int)$this->id);
    }
    
    public static function getDepartmentTitle($dep_id)
    {
        $id_lang = (int)Context::getContext()->cookie->id_lang;

        return Db::getInstance()->getRow('
            SELECT t1.*, t2.`department_title`
            FROM '._DB_PREFIX_.'fmm_hd_departments t1
            LEFT JOIN '._DB_PREFIX_.'fmm_hd_departments_lang t2
                ON (t1.departments_id = t2.departments_id AND t2.id_lang = '.(int)$id_lang.')
            WHERE t1.`departments_id` = '.(int)$dep_id);
    }
    
    public static function getDepartmentSignature($dep_id)
    {
        return Db::getInstance()->getRow('
            SELECT t1.`department_signature`
            FROM '._DB_PREFIX_.'fmm_hd_departments t1
            WHERE t1.`departments_id` = '.(int)$dep_id);
    }
}
