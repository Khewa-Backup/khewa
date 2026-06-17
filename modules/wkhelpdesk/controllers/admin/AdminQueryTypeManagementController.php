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

class AdminQueryTypeManagementController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->table = 'wk_hd_query_type';
        $this->className = 'WkHdQueryType';
        $this->bootstrap = true;
        $this->lang = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->identifier = 'id';
        parent::__construct();
        $this->toolbar_title = $this->l('Query types');
        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            Shop::addTableAssociation('wk_hd_query_type', array('type' => 'shop', 'primary' => 'id'));
        }
        $this->_join .= WkHdGroup::addSqlAssociationCustom('wk_hd_query_type', 'a', false);
        $this->_where .= ' AND b.`id_shop`='.min(Shop::getContextListShopID());
        $this->_group = ' GROUP BY a.id';
        $this->_select = 'b.`query_name`';
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $this->fields_list = array(
                'id' => array(
                    'title' => $this->l('ID'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs',
                ),
                'query_name' => array(
                    'title' => $this->l('Query type'),
                    'align' => 'center',
                    'hint' => $this->l(
                        'This will be displayed in front office when any customer will create new ticket.'
                    ),
                ),
                'active' => array(
                    'title' => $this->l('Status'),
                    'active' => 'status',
                    'type' => 'bool',
                    'orderby' => false,
                    'align' => 'center',
                ),
                'date_add' => array(
                    'title' => $this->l('Add date'),
                    'type' => 'datetime',
                    'callback' => 'getFormatedDate',
                    'filter_key' => 'a!date_add'
                ),
                'date_upd' => array(
                    'title' => $this->l('Update date'),
                    'type' => 'datetime',
                    'callback' => 'getFormatedDate',
                    'filter_key' => 'a!date_upd'
                )
            );
        } else {
            $this->errors[] = $this->l('You do not have access right to view this page.');
        }

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
            ),
        );
    }

    public function getFormatedDate($val, $data)
    {
        return date("d-m-Y H:i:s", strtotime($val));
    }

    public function initContent()
    {
        if (($this->display == 'edit') && (Shop::getContext() == Shop::CONTEXT_SHOP)) {
            if (!$this->loadObject(true)) {
                Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
            }
        }
        return parent::initContent();
    }

    public function initToolbar()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            parent::initToolbar();
            $this->page_header_toolbar_btn['new'] = array(
                'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
                'desc' => $this->l('Add new query type'),
            );
        }
    }

    public function renderForm()
    {
        if (($this->display == 'edit') && (Shop::getContext() != Shop::CONTEXT_SHOP)) {
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/_partials/shop_warning.tpl'
            );
        } else {
            $objHdEmployee = new WkHdTicketAgent();
            if ($objHdEmployee->validateEmployee($this->context->employee)) {
                $curr_lang = $this->context->language->id;
                $objGroup = new WkHdGroup();

                // get all group information by language id
                $allGroup = $objGroup->getAllGroupInfoByIdLang($this->context->language->id);
                $smartyVar = array(
                    'languages' => Language::getLanguages(),
                    'total_languages' => count(Language::getLanguages()),
                    'current_lang' => Language::getLanguage((int) $curr_lang),
                );

                // get all group
                if ($allGroup) {
                    $smartyVar['allGroup'] = $allGroup;
                }

                if ($this->display == 'edit') {
                    $id = Tools::getValue('id');
                    $objQueryType = new WkHdQueryType();
                    // get query type info
                    $queryTypeInfo = $objQueryType->getQueryInfoById($id);

                    // get language information
                    $queryTypeLangInfo = $objQueryType->getQueryLangInfoById($id);

                    if ($queryTypeInfo && $queryTypeLangInfo) {
                        foreach ($queryTypeLangInfo as $query_type_lang) {
                            $queryTypeInfo['query_name'][$query_type_lang['id_lang']] = $query_type_lang['query_name'];
                        }
                        $smartyVar['queryTypeInfo'] = $queryTypeInfo;
                    } else {
                        $this->errors[] = $this->l('Query type information not found.');
                    }

                    $objGroupQueryMapping = new WkHdGroupQueryTypeMapping();

                    //get mapped group information
                    $groupQueryMapping = $objGroupQueryMapping->getInfoByIdQueryType($id);
                    if ($groupQueryMapping) {
                        $smartyVar['groupQueryMapping'] = $groupQueryMapping;
                    }
                }

                $this->context->smarty->assign($smartyVar);
                $this->fields_form = array(
                    'submit' => array(
                        'title' => $this->l('Save'),
                    ),
                );

                return parent::renderForm();
            } else {
                $this->errors[] = $this->l('You do not have access right to view this page.');
            }
        }
    }

    public function processSave()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $queryNameError = 0;
            $id = Tools::getValue('id');//if edit
            $languages = Language::getLanguages();
            $selectedIdGroup = Tools::getValue('idGroup');
            $defaultIdLang = $this->context->language->id;
            //  query type validation
            if (trim(Tools::getValue('query_name_'.$defaultIdLang))) {
                foreach ($languages as $language) {
                    if (!Validate::isCatalogName(Tools::getValue('query_name_'.$language['id_lang']))) {
                        $queryNameError = 1;
                    }
                    if (Tools::strlen(Tools::getValue('query_name_'.$language['id_lang'])) > 128) {
                        $queryNameError = 2;
                    }
                }
            } else {
                $default_lang = Language::getLanguage($defaultIdLang);
                $this->errors[] = $this->l('Query type is required ').$default_lang['name'];
            }

            if ($queryNameError == 1) {
                $this->errors[] = $this->l(
                    'Query Type must not have invalid characters'
                ).' <>;=#{}';
            }
            if ($queryNameError == 2) {
                $this->errors[] = $this->l(
                    'Maximum query type length allowed is 128 characters.'
                );
            }

            if (empty($selectedIdGroup)) {
                $objGroup = new WkHdGroup();
                $groupAvailable = $objGroup->getAllGroupInfoByIdLang($this->context->language->id);
                if (!empty($groupAvailable)) {
                    $this->errors[] = $this->l('Select atleast one group.');
                } else {
                    $this->errors[] = $this->l('Please create group first.');
                }
            }

            if (empty($this->errors)) {
                // id edit then first delete group mapped data
                if ($id) {
                    $objQueryType = new WkHdQueryType((int) $id);
                    $objGroupQueryMapping = new WkHdGroupQueryTypeMapping();
                    $objGroupQueryMapping->deleteMappingByIdQueryType($id);
                // add new query type
                } else {
                    $objQueryType = new WkHdQueryType();
                    $objQueryType->active = (int) 1;
                }

                foreach ($languages as $language) {
                    $lang_id = $language['id_lang'];
                    if (!Tools::getValue('query_name_'.$lang_id)) {
                        $lang_id = $defaultIdLang;
                    }
                    $objQueryType->query_name[$language['id_lang']] = pSQL(Tools::getValue('query_name_'.$lang_id));
                }
                $objQueryType->save();
                $savedIdQuery = $objQueryType->id;

                // save group mapping information
                if ($savedIdQuery) {
                    if ($selectedIdGroup) {
                        $objGroupQueryMapping = new WkHdGroupQueryTypeMapping();
                        $objGroupQueryMapping->id_group = (int) $selectedIdGroup;
                        $objGroupQueryMapping->id_query_type = (int) $savedIdQuery;
                        $objGroupQueryMapping->save();
                    }
                }

                if (Tools::isSubmit('submitAdd'.$this->table.'AndStay')) {
                    if ($id) {
                        Tools::redirectAdmin(
                            self::$currentIndex.'&id='.(int) $savedIdQuery.'&update'.$this->table.
                            '&conf=4&token='.$this->token
                        );
                    } else {
                        Tools::redirectAdmin(
                            self::$currentIndex.'&id='.(int) $savedIdQuery.'&update'.$this->table.
                            '&conf=3&token='.$this->token
                        );
                    }
                } else {
                    if ($id) {
                        Tools::redirectAdmin(self::$currentIndex.'&conf=3&token='.$this->token);
                    } else {
                        Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
                    }
                }
            } else {
                if ($id) {
                    $this->display = 'edit';
                } else {
                    $this->display = 'add';
                }
            }
        } else {
            $this->errors[] = $this->l('You do not have access right to view this page.');
        }
    }

    public function postProcess()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $objQueryType = new WkHdQueryType();
            if (Tools::isSubmit('deletewk_hd_query_type')) {
                if ($id = Tools::getValue('id')) {
                    $objTicket = new WkHdTicket();
                    // check is any ticket created for this query type
                    $isTicketsAvailable = $objTicket->getTicketsByIdQueryType($id);
                    if ($isTicketsAvailable) {
                        $this->errors[] = $this->l(
                            'You can not delete this query type because customer(s) are created ticket for this query type.'
                        );
                    } else {
                        // all query type can not deleted
                        $allQueryType = $objQueryType->getAllQueryType(false, true);
                        if (count($allQueryType) == 1) {
                            if ($allQueryType[0]['id'] == $id) {
                                $this->errors[] = $this->l(
                                    'You can not delete this query type because at least one active query type required.'
                                );
                            }
                        }
                    }
                }
            } elseif (Tools::isSubmit('statuswk_hd_query_type')) {
                if ($id = Tools::getValue('id')) {
                    $allQueryType = $objQueryType->getAllQueryType(false, true);

                    if (count($allQueryType) == 1) {
                        // all query type must not disabled
                        if ($allQueryType[0]['id'] == $id) {
                            $this->errors[] = $this->l(
                                'You can not disable this query type because at least one query type must enable.'
                            );
                        }
                    }
                }
            }

            if (empty($this->errors)) {
                parent::postProcess();
            }
        } else {
            $this->errors[] = $this->l('You do not have access right to view this page.');
        }
    }

    protected function processBulkDelete()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $toDelete = array();
            if (is_array($this->boxes) && !empty($this->boxes)) {
                $objTicket = new WkHdTicket();
                $count = 0;
                foreach ($this->boxes as $id) {
                    if (empty($this->errors)) {
                        // check is any ticket created for this query type
                        $isTicketsAvailable = $objTicket->getTicketsByIdQueryType($id);
                        if ($isTicketsAvailable) {
                            $this->errors[] = $this->l(
                                'You can not delete this query type because customer(s) are created ticket for this query type.'
                            );
                        } else {
                            // all query type can not deleted
                            $objQueryType = new WkHdQueryType();
                            $allQueryType = $objQueryType->getAllQueryType(false, true);
                            if (count($allQueryType) == 1) {
                                if ($allQueryType[0]['id'] == $id) {
                                    $this->errors[] = $this->l(
                                        'You can not delete all query type because at least one query active type required.'
                                    );
                                } else {
                                    $toDelete[$count] = $id;
                                    $count++;
                                }
                            } else {
                                $toDelete[$count] = $id;
                                $count++;
                            }
                        }
                    }
                }
            }
            if (count($toDelete) > 0) {
                foreach ($toDelete as $key => $del) {
                    $objQueryType = new WkHdQueryType();
                    $allQueryType = $objQueryType->getAllQueryType(false, true);
                    if (count($allQueryType) == count($toDelete)) {
                        if ($key) {
                            $objQueryType = new WkHdQueryType((int) $del);
                            $objQueryType->delete();
                        }
                    } else {
                        $objQueryType = new WkHdQueryType((int) $del);
                        $objQueryType->delete();
                    }
                }
            } elseif (empty($this->boxes)) {
                $this->errors[] = $this->l(
                    'You must select at least one element to delete.'
                );
            }

            if (empty($this->errors)) {
                Tools::redirectAdmin(self::$currentIndex.'&conf=2&token='.$this->token);
            }
        } else {
            $this->errors[] = $this->l('You do not have access right to view this page.');
        }
    }

    protected function processBulkDisableSelection()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            if (is_array($this->boxes) && !empty($this->boxes)) {
                foreach ($this->boxes as $id) {
                    if (empty($this->errors)) {
                        $objQueryType = new WkHdQueryType();
                        $allQueryType = $objQueryType->getAllQueryType(false, true);

                        if (count($allQueryType) == 1) {
                            // all query type must not disabled
                            if ($allQueryType[0]['id'] == $id) {
                                $this->errors[] = $this->l(
                                    'You can not disable all query type because at least one query type must enable.'
                                );
                            } else {
                                $object = new WkHdQueryType((int)$id);
                                $object->active = (int) 0;
                                $object->update();
                            }
                        } else {
                            $object = new WkHdQueryType((int)$id);
                            $object->active = (int) 0;
                            $object->update();
                        }
                    }
                }
            }

            if (empty($this->errors)) {
                Tools::redirectAdmin(self::$currentIndex.'&conf=5&token='.$this->token);
            }
        } else {
            $this->errors[] = $this->l('You do not have access right to view this page.');
        }
    }
}
