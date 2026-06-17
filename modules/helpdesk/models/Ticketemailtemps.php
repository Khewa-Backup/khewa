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

class Ticketemailtemps extends ObjectModel
{
    public $id;
    public $emailtemp_id;
    public $emailtemp_title;
    public $created_time;
    public $update_time;
    public $emailtemp_status;
    public $new_ticket_user_subject;
    public $new_ticket_user_message;
    public $new_message_user_subject;
    public $new_message_user_message;
    public $close_ticket_user_subject;
    public $close_ticket_user_message;
    public $new_ticket_staff_subject;
    public $new_ticket_staff_message;
    public $new_message_staff_subject;
    public $new_message_staff_message;
    public $new_reply_user_subject;
    public $new_reply_user_message;
    
    public static $definition = array(
        'table' => 'fmm_hd_emailtemp',
        'primary' => 'emailtemp_id',
        'multilang' => true,
        'fields' => array(
                'emailtemp_title'               =>      array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 64),
                
                'new_ticket_user_subject'       =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 64),
                'new_ticket_user_message'       =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'),
                'new_message_user_subject'      =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 64),
                'new_message_user_message'      =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'),
                'close_ticket_user_subject'     =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 64),
                'close_ticket_user_message'     =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'),
                'new_ticket_staff_subject'      =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 64),
                'new_ticket_staff_message'      =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'),
                'new_message_staff_subject'     =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 64),
                'new_message_staff_message'     =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'),
                'new_reply_user_subject'        =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 64),
                'new_reply_user_message'        =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'),
                
                'emailtemp_status'              =>      array('type' => self::TYPE_BOOL),
                'created_time'                  =>      array('type' => self::TYPE_DATE),
        ),
    );


    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
    }

    public function delete()
    {
        $res = Db::getInstance()->execute('
            DELETE FROM `'._DB_PREFIX_.'fmm_hd_emailtemp`
            WHERE `emailtemp_id` = '.(int)$this->emailtemp_id);
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
            $this->emailtemp_id = Ticketemailtemps::getTicketEmailTemp();
            $result = $result && $this->delete();
        }

        return $result;
    }
    
    public function getTicketEmailTemp()
    {
        if (!(int)$this->id) {
            return false;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `emailtemp_id` FROM '._DB_PREFIX_.'fmm_hd_emailtemp WHERE `emailtemp_id` = '.(int)$this->id);
    }
    
    public static function getEmailContent($emailtemp_id)
    {
        $id_lang = Context::getContext()->language->id;
        
        $sql = 'SELECT *
            FROM '._DB_PREFIX_.'fmm_hd_emailtemp t
            LEFT JOIN '._DB_PREFIX_.'fmm_hd_emailtemp_lang tl
                ON (t.emailtemp_id = tl.emailtemp_id AND tl.id_lang = '.(int)$id_lang.')
            WHERE t.`emailtemp_status` = 1 AND t.emailtemp_id = '.(int)$emailtemp_id;
        $data = Db::getInstance()->getRow($sql);
        return $data;
    }
}
