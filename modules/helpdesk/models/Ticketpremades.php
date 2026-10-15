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

class Ticketpremades extends ObjectModel
{
    public $id;
    public $premade_id;
    public $premade_title;
    public $premade_content;
    public $created_time;
    public $update_time;
    public $premade_status;
    
    public static $definition = array(
        'table' => 'fmm_hd_premade',
        'primary' => 'premade_id',
        'multilang' => true,
        'fields' => array(
                'premade_title'         =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true, 'size' => 64),
                'premade_content'       =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true),
                'premade_status'                    =>      array('type' => self::TYPE_BOOL),
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
            DELETE FROM `'._DB_PREFIX_.'fmm_hd_premade`
            WHERE `premade_id` = '.(int)$this->premade_id);
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
            $this->premade_id = Ticketstatus::getTicketPremade();
            $result = $result && $this->delete();
        }

        return $result;
    }
    
    public function getTicketPremade()
    {
        if (!(int)$this->id) {
            return false;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `premade_id` FROM '._DB_PREFIX_.'fmm_hd_premade WHERE `premade_id` = '.(int)$this->id);
    }
}
