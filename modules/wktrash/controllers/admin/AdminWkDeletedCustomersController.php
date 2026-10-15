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
class AdminWkDeletedCustomersController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = false;
        $this->table = 'wk_deleted_customer';
        $this->className = 'WkDeletedCustomer';
        $this->identifier = 'id_wk_deleted_customer';
        $this->list_no_link = true;
        parent::__construct();

        $this->_select = '`id_wk_deleted_customer` as temp_deleted_customer_id';
        $this->fields_list = [
            'id_wk_deleted_customer' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'id_customer' => [
                'title' => $this->l('Customer ID'),
                'align' => 'center',
                'class' => 'fixed-width-xl',
            ],
            'firstname' => [
                'title' => $this->l('Customer name'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'temp_deleted_customer_id' => [
                'title' => $this->l('Restore'),
                'align' => 'center',
                'class' => 'fixed-width-xl',
                'search' => false,
                'callback' => 'getRestoreButton',
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items permanently?'),
            ],
            'restore' => [
                'text' => $this->l('Restore selected'),
                'icon' => 'icon-undo',
                'confirm' => $this->l('Restore selected items?'),
            ],
        ];
        $index = count($this->_conf);
        $this->_conf[$index] = $this->l('Successful restore.');
    }

    /**
     * To display restore button on deleted customer list
     *
     * @param [int] $idDeletedCustomer
     *
     * @return html
     */
    public function getRestoreButton($idDeletedCustomer)
    {
        if ($idDeletedCustomer) {
            $this->context->smarty->assign([
                'idDeletedEntity' => $idDeletedCustomer,
                'entityTable' => $this->table,
            ]);

            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/restore-button.tpl'
            );
        }

        return false;
    }

    /**
     * To render list for deleted customer
     *
     * @return void
     */
    public function renderList()
    {
        $this->addRowAction('delete');

        return parent::renderList();
    }

    /**
     * To hide add new button from deleted customer list
     *
     * @return void
     */
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    /**
     * To restore the data of customers.
     *
     * @return void
     */
    public function postProcess()
    {
        if (!$this->loadObject(true)) {
            return;
        }

        if (Tools::issubmit('restoreButton' . $this->table)) {
            if (Tools::getValue('restoreButton' . $this->table)) {
                $idDeletedCustomer = Tools::getValue('restoreButton' . $this->table);
                if ($idDeletedCustomer) {
                    $this->restoreCustomerAfterDeletion($idDeletedCustomer);
                }
                if (empty($this->context->controller->errors)) {
                    $index = count($this->_conf);
                    Tools::redirectAdmin(
                        AdminController::$currentIndex . '&token=' . $this->context->controller->token . '&conf=' . $index
                    );
                }
            }
        }
        parent::postProcess();
    }

    public function processBulkRestore()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            foreach ($this->boxes as $idDeletedCustomer) {
                if (!empty($idDeletedCustomer) && $idDeletedCustomer) {
                    $this->restoreCustomerAfterDeletion($idDeletedCustomer);
                }
            }
            if (empty($this->context->controller->errors)) {
                $index = count($this->_conf);
                Tools::redirectAdmin(
                    AdminController::$currentIndex . '&token=' . $this->context->controller->token . '&conf=' . $index
                );
            }
        } else {
            $this->context->controller->errors[] = $this->l('You must have select at least one customer to restore.');
        }
    }

    public function restoreCustomerAfterDeletion($idDeletedCustomer)
    {
        if (!empty($idDeletedCustomer) && $idDeletedCustomer) {
            $objDeletdCustomer = new WkDeletedCustomer($idDeletedCustomer);
            if (Validate::isLoadedObject($objDeletdCustomer)) {
                $customerInfo = $objDeletdCustomer->getDeletedCustomerDetail($idDeletedCustomer);
                if (!empty($customerInfo) && $customerInfo) {
                    $idNewCustomer = $this->restoreDeletedCustomer($customerInfo);
                    if (!empty($idNewCustomer) && $idNewCustomer) {
                        $objEntityHistory = new WkEntityRestoreHistory();
                        $historyId = $objEntityHistory->getIdByOldEntityId($customerInfo['id_customer'], 5);
                        if ($historyId) {
                            $objEntityHistory->updateEntityHistory($historyId, $idNewCustomer);
                        }
                        $objDeletdCustomer->delete();
                    }
                }
            }
        }
    }

    public function restoreDeletedCustomer($customerInfo)
    {
        if (!empty($customerInfo) && $customerInfo) {
            // Decode all manufacturer info
            $customerInfo['customer_group'] = json_decode($customerInfo['customer_group'], true);
            $customerInfo['customer_address'] = json_decode($customerInfo['customer_address'], true);
            $customerInfo['specific_price'] = json_decode($customerInfo['specific_price'], true);
            $customerInfo['customer_cart_rule'] = json_decode($customerInfo['customer_cart_rule'], true);
            $customerInfo['guest'] = json_decode($customerInfo['guest'], true);
            $customerInfo['customer_message'] = json_decode($customerInfo['customer_message'], true);
            $isExistId = Customer::customerIdExistsStatic($customerInfo['id_customer']);
            if (!empty($isExistId) && $isExistId) {
                $customer = new Customer($isExistId);
                $customer->deleted = 0;
                $customer->save();
            } else {
                $isExistEmail = Customer::customerExists($customerInfo['email'], false, true);
                if ($isExistEmail) {
                    $error = $this->l('A customer with same email has already registered.');
                    $this->context->controller->errors[] = $error;

                    return false;
                } else {
                    $customer = new Customer(); // Restore Customer with new ID
                    if (!Configuration::get('WK_RESTORE_ENTITY_NEW_ID')) {
                        // Restore Customer with Old ID
                        $wkResult = WkDeletedCustomer::insertDataInPrimaryTable($customerInfo);
                        if ($wkResult) {
                            $customer = new Customer($customerInfo['id_customer']);
                        }
                    }
                    $customer->id_shop_group = $customerInfo['id_shop_group'];
                    $customer->id_shop = $customerInfo['id_shop'];
                    $customer->id_gender = $customerInfo['id_gender'];
                    $customer->id_default_group = $customerInfo['id_default_group'];
                    $customer->id_lang = $customerInfo['id_lang'];
                    $customer->id_risk = $customerInfo['id_risk'];
                    $customer->company = $customerInfo['company'];
                    $customer->siret = $customerInfo['siret'];
                    $customer->ape = $customerInfo['ape'];
                    $customer->firstname = $customerInfo['firstname'];
                    $customer->lastname = $customerInfo['lastname'];
                    $customer->email = $customerInfo['email'];
                    $customer->passwd = $customerInfo['passwd'];
                    $customer->last_passwd_gen = $customerInfo['last_passwd_gen'];
                    $customer->birthday = $customerInfo['birthday'];
                    $customer->newsletter = $customerInfo['newsletter'];
                    $customer->ip_registration_newsletter = $customerInfo['ip_registration_newsletter'];
                    $customer->newsletter_date_add = $customerInfo['newsletter_date_add'];
                    $customer->optin = $customerInfo['optin'];
                    $customer->website = $customerInfo['website'];
                    $customer->outstanding_allow_amount = $customerInfo['outstanding_allow_amount'];
                    $customer->show_public_prices = $customerInfo['show_public_prices'];
                    $customer->max_payment_days = $customerInfo['max_payment_days'];
                    $customer->secure_key = $customerInfo['secure_key'];
                    $customer->note = $customerInfo['note'];
                    $customer->active = $customerInfo['active'];
                    $customer->is_guest = $customerInfo['is_guest'];
                    $customer->deleted = $customerInfo['deleted'];
                    $customer->reset_password_token = $customerInfo['reset_password_token'];
                    $customer->reset_password_validity = $customerInfo['reset_password_validity'];
                    $customer->save();
                }
            }
            if (isset($customer->id) && $customer->id) {
                if (!empty($isExistId) && $isExistId) {
                    if (!empty($customerInfo['customer_address']) && $customerInfo['customer_address']) {
                        foreach ($customerInfo['customer_address'] as $customerAddress) {
                            if (!empty($customerAddress) && $customerAddress) {
                                $objAddress = new Address($customerAddress['id_address']);
                                $objAddress->deleted = 0;
                                $objAddress->save();
                            }
                        }
                    }
                } else {
                    if (!empty($customerInfo['customer_group']) && $customerInfo['customer_group']) {
                        $groups = [];
                        foreach ($customerInfo['customer_group'] as $customerGroup) {
                            $groups[] = $customerGroup['id_group'];
                        }
                        $customer->addGroups($groups);
                    }
                    if (!empty($customerInfo['customer_address']) && $customerInfo['customer_address']) {
                        foreach ($customerInfo['customer_address'] as $customerAddress) {
                            if (!empty($customerAddress) && $customerAddress) {
                                $objAddress = new Address();
                                $objAddress->id_country = $customerAddress['id_country'];
                                $objAddress->id_state = $customerAddress['id_state'];
                                $objAddress->id_customer = $customer->id;
                                $objAddress->id_manufacturer = $customerAddress['id_manufacturer'];
                                $objAddress->id_supplier = $customerAddress['id_supplier'];
                                $objAddress->id_warehouse = $customerAddress['id_warehouse'];
                                $objAddress->alias = $customerAddress['alias'];
                                $objAddress->company = $customerAddress['company'];
                                $objAddress->lastname = $customerAddress['lastname'];
                                $objAddress->firstname = $customerAddress['firstname'];
                                $objAddress->address1 = $customerAddress['address1'];
                                $objAddress->address2 = $customerAddress['address2'];
                                $objAddress->postcode = $customerAddress['postcode'];
                                $objAddress->city = $customerAddress['city'];
                                $objAddress->other = $customerAddress['other'];
                                $objAddress->phone = $customerAddress['phone'];
                                $objAddress->phone_mobile = $customerAddress['phone_mobile'];
                                $objAddress->vat_number = $customerAddress['vat_number'];
                                $objAddress->dni = $customerAddress['dni'];
                                $objAddress->deleted = 0;
                                $objAddress->save();
                            }
                        }
                    }
                    if (!empty($customerInfo['customer_cart_rule']) && $customerInfo['customer_cart_rule']) {
                        foreach ($customerInfo['customer_cart_rule'] as $customerCartRule) {
                            if (!empty($customerCartRule) && $customerCartRule) {
                                $objCartRule = new CartRule();
                                $objCartRule->id_customer = $customer->id;
                                $objCartRule->date_from = $customerCartRule['date_from'];
                                $objCartRule->date_to = $customerCartRule['date_to'];
                                $objCartRule->description = $customerCartRule['description'];
                                $objCartRule->quantity = $customerCartRule['quantity'];
                                $objCartRule->quantity_per_user = $customerCartRule['quantity_per_user'];
                                $objCartRule->priority = $customerCartRule['priority'];
                                $objCartRule->partial_use = $customerCartRule['partial_use'];
                                $objCartRule->code = $customerCartRule['code'];
                                $objCartRule->minimum_amount = $customerCartRule['minimum_amount'];
                                $objCartRule->minimum_amount_tax = $customerCartRule['minimum_amount_tax'];
                                $objCartRule->minimum_amount_currency = $customerCartRule['minimum_amount_currency'];
                                $objCartRule->minimum_amount_shipping = $customerCartRule['minimum_amount_shipping'];
                                $objCartRule->country_restriction = $customerCartRule['country_restriction'];
                                $objCartRule->carrier_restriction = $customerCartRule['carrier_restriction'];
                                $objCartRule->group_restriction = $customerCartRule['group_restriction'];
                                $objCartRule->cart_rule_restriction = $customerCartRule['cart_rule_restriction'];
                                $objCartRule->product_restriction = $customerCartRule['product_restriction'];
                                $objCartRule->shop_restriction = $customerCartRule['shop_restriction'];
                                $objCartRule->free_shipping = $customerCartRule['free_shipping'];
                                $objCartRule->reduction_percent = $customerCartRule['reduction_percent'];
                                $objCartRule->reduction_amount = $customerCartRule['reduction_amount'];
                                $objCartRule->reduction_tax = $customerCartRule['reduction_tax'];
                                $objCartRule->reduction_currency = $customerCartRule['reduction_currency'];
                                $objCartRule->reduction_product = $customerCartRule['reduction_product'];
                                $objCartRule->reduction_exclude_special = $customerCartRule['reduction_exclude_special'];
                                $objCartRule->gift_product = $customerCartRule['gift_product'];
                                $objCartRule->gift_product_attribute = $customerCartRule['gift_product_attribute'];
                                $objCartRule->highlight = $customerCartRule['highlight'];
                                if ($customerCartRule['name'] && !empty($customerCartRule['name'])) {
                                    foreach ($customerCartRule['name'] as $lang) {
                                        $objCartRule->name[$lang['id_lang']] = $lang['name'];
                                    }
                                }
                                $objCartRule->save();
                            }
                        }
                    }
                    if (!empty($customerInfo['specific_price']) && $customerInfo['specific_price']) {
                        foreach ($customerInfo['specific_price'] as $specificPrice) {
                            if (!empty($specificPrice) && $specificPrice) {
                                $objSpecificPrice = new SpecificPrice();
                                $objSpecificPrice->id_specific_price_rule = (int) $specificPrice['id_specific_price_rule'];
                                $objSpecificPrice->id_cart = (int) $specificPrice['id_cart'];
                                $objSpecificPrice->id_product = (int) $specificPrice['id_product'];
                                $objSpecificPrice->id_shop = (int) $specificPrice['id_shop'];
                                $objSpecificPrice->id_shop_group = (int) $specificPrice['id_shop_group'];
                                $objSpecificPrice->id_currency = (int) $specificPrice['id_currency'];
                                $objSpecificPrice->id_country = (int) $specificPrice['id_country'];
                                $objSpecificPrice->id_group = (int) $specificPrice['id_group'];
                                $objSpecificPrice->id_customer = $customer->id;
                                $objSpecificPrice->id_product_attribute = (int) $specificPrice['id_product_attribute'];
                                $objSpecificPrice->price = $specificPrice['price'];
                                $objSpecificPrice->from_quantity = $specificPrice['from_quantity'];
                                $objSpecificPrice->reduction = $specificPrice['reduction'];
                                $objSpecificPrice->reduction_tax = $specificPrice['reduction_tax'];
                                $objSpecificPrice->reduction_type = $specificPrice['reduction_type'];
                                $objSpecificPrice->from = $specificPrice['from'];
                                $objSpecificPrice->to = $specificPrice['to'];
                                $objSpecificPrice->save();
                            }
                        }
                    }
                    if (!empty($customerInfo['guest']) && $customerInfo['guest']) {
                        $objGuest = new Guest($customerInfo['guest']);
                        $objGuest->id_customer = $customer->id;
                        $objGuest->save();
                    }
                    if (!empty($customerInfo['customer_message']) && $customerInfo['customer_message']) {
                        foreach ($customerInfo['customer_message'] as $thread) {
                            if (!empty($thread) && $thread) {
                                $objCustomerThread = new CustomerThread();
                                $objCustomerThread->id_shop = $thread['id_shop'];
                                $objCustomerThread->id_lang = $thread['id_lang'];
                                $objCustomerThread->id_contact = $thread['id_contact'];
                                $objCustomerThread->id_customer = $customer->id;
                                $objCustomerThread->id_order = $thread['id_order'];
                                $objCustomerThread->id_product = $thread['id_product'];
                                $objCustomerThread->status = $thread['status'];
                                $objCustomerThread->email = $thread['email'];
                                $objCustomerThread->token = $thread['token'];
                                $objCustomerThread->save();
                                if ($objCustomerThread->id && $thread['msg']) {
                                    foreach ($thread['msg'] as $message) {
                                        if (!empty($message) && $message) {
                                            $objCustomerMsg = new CustomerMessage();
                                            $objCustomerMsg->id_customer_thread = $objCustomerThread->id;
                                            $objCustomerMsg->id_employee = $message['id_employee'];
                                            $objCustomerMsg->message = $message['message'];
                                            $objCustomerMsg->file_name = $message['file_name'];
                                            $objCustomerMsg->ip_address = $message['ip_address'];
                                            $objCustomerMsg->user_agent = $message['user_agent'];
                                            $objCustomerMsg->private = $message['private'];
                                            $objCustomerMsg->read = $message['read'];
                                            $objCustomerMsg->save();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                return $customer->id;
            }
        }
    }

    /**
     * To set JS & CSS for controller
     *
     * @return void
     */
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        Media::addJsDef([
            'restore_selected_item' => $this->l('Restore selected item?'),
        ]);
        $this->context->controller->addJs(_PS_MODULE_DIR_ . $this->module->name . '/views/js/wk-restore-entity.js');
    }
}
