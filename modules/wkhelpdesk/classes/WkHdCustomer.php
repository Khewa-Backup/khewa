<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

class WkHdCustomer extends ObjectModel
{
    public $id;
    public $id_ps_customer;
    public $is_spam;
    public $first_name;
    public $last_name;
    public $email;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'wk_hd_customer',
        'primary' => 'id',
        'fields' => array(
            'id_ps_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'is_spam' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'first_name' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true,'size' => 32),
            'last_name' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32),
            'email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 128),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false)
        )
    );

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_hd_customer', array('type' => 'shop', 'primary' => 'id'));
    }

    /**
     * Used for deleting GDPR customer
     * @param  string $email
     * @return boolval
     */
    public function deleteHdCustomerByEmail($email)
    {
        $allCustomers = $this->getCustomerByEmail($email, true);
        $deleted = true;
        if (!empty($allCustomers)) {
            foreach ($allCustomers as $customerData) {
                $objCustomer = new WkHdCustomer((int) $customerData['id']);
                $deleted &= $objCustomer->delete();
            }
        }

        return $deleted;
    }

    public function getCustomerByEmail($email, $all = false)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_hd_customer` cust '
            . WkHdGroup::addSqlAssociationCustom('wk_hd_customer', 'cust').'
            WHERE cust.`email` = \''. pSQL($email).'\' GROUP BY cust.`id`';
        if ($all) {
            return Db::getInstance()->executeS($sql);
        } else {
            return Db::getInstance()->getRow($sql);
        }
    }

    public function updateCustomerInfoByEmail($email, $firstName, $lastName, $idCustomer)
    {
        $allCustomers = $this->getCustomerByEmail($email, true);
        $success = true;
        if (!empty($allCustomers)) {
            foreach ($allCustomers as $customerData) {
                $objCustomer = new WkHdCustomer((int) $customerData['id']);
                $objCustomer->first_name = pSQL($firstName);
                $objCustomer->last_name = pSQL($lastName);
                $objCustomer->id_ps_customer = (int) $idCustomer;
                $success &= $objCustomer->save();
            }
        }

        return $success;
    }

    public function updateCustomerInfoByIdCustomer($idCustomer, $firstName, $lastName, $email)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_hd_customer` cust '
            . WkHdGroup::addSqlAssociationCustom('wk_hd_customer', 'cust').'
            WHERE cust.`id_ps_customer` = '. (int) $idCustomer.' GROUP BY cust.`id`';
        $allCustomers = Db::getInstance()->executeS($sql);
        $success = true;
        if (!empty($allCustomers)) {
            foreach ($allCustomers as $customerData) {
                $objCustomer = new WkHdCustomer((int) $customerData['id']);
                $objCustomer->first_name = pSQL($firstName);
                $objCustomer->last_name = pSQL($lastName);
                $objCustomer->email = pSQL($email);
                $success &= $objCustomer->save();
            }
        }

        return $success;
    }

    public function replyMailToCustomer($mailParams)
    {
        Mail::Send(
            (int) $mailParams['{id_lang}'],
            'reply_mail_to_customer',
            Mail::l('New message alert', (int) $mailParams['{id_lang}'].' Tkt#'.$mailParams['{ticket_id}']),
            $mailParams,
            $mailParams['{email}'],
            $mailParams['{first_name}'].' '.$mailParams['{last_name}'],
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_.'wkhelpdesk/mails/',
            false,
            null,
            null
        );
    }

    public function mailToCustomerForStatusUpdate($mailParams)
    {
        Mail::Send(
            (int) $mailParams['{id_lang}'],
            'ticket_status_update_alert_to_customer',
            Mail::l('Ticket status update', (int) $mailParams['{id_lang}']),
            $mailParams,
            $mailParams['{email}'],
            $mailParams['{name}'],
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_.'wkhelpdesk/mails/',
            false,
            null,
            null
        );
    }
}
