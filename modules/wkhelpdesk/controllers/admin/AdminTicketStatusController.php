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

class AdminTicketStatusController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->table = 'wk_hd_status_code';
        $this->className = 'WkHdStatusCode';
        $this->bootstrap = true;
        $this->identifier = 'id';
        $this->lang = true;
        parent::__construct();
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->identifier = 'id';
        parent::__construct();
        $this->toolbar_title = $this->l('Ticket status');
        Shop::addTableAssociation('wk_hd_status_code', array('type' => 'shop', 'primary' => 'id'));
        $this->_join .= WkHdGroup::addSqlAssociationCustom('wk_hd_status_code', 'a', false);
        $this->_where = ' AND b.id_shop = '.Context::getContext()->shop->id;
        $this->_group = ' GROUP BY a.id';

        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $this->fields_list = array(
                'id' => array(
                    'title' => $this->l('ID'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs',
                ),
                'ticket_status' => array(
                    'title' => $this->l('Status'),
                    'align' => 'center',
                ),
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

    public function processDelete()
    {
        if (Tools::getValue('id') <= 6) {
            $this->errors[] = $this->l('You can not delete predefine status.');
        } else {
            $objStatus = new WkHdStatusCode((int) Tools::getValue('id'));
            $objStatus->delete();
            $mapObj = new WkHdStatusMapping((int) Tools::getValue('id'));
            $mapObj->delete();
        }
        if (empty($this->errors)) {
            Tools::redirectAdmin(self::$currentIndex.'&conf=1&token='.$this->token);
        }
    }

    public function initToolbar()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            parent::initToolbar();
            $this->page_header_toolbar_btn['new'] = array(
                'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
                'desc' => $this->l('Add new status'),
            );
        }
    }

    public function renderForm()
    {
        if (Shop::getContext() != Shop::CONTEXT_SHOP) {
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/_partials/shop_warning.tpl'
            );
        } else {
            $objHdEmployee = new WkHdTicketAgent();
            if ($objHdEmployee->validateEmployee($this->context->employee)) {
                $curr_lang = $this->context->language->id;
                $this->context->smarty->assign(
                    array(
                        'languages' => Language::getLanguages(),
                        'total_languages' => count(Language::getLanguages()),
                        'current_lang' => Language::getLanguage((int) $curr_lang),
                    )
                );
                if ($id = Tools::getValue('id')) {
                    $objStatus = new WkHdStatusCode();
                    // get query type info
                    $queryTypeInfo = $objStatus->getStatusInfoById($id);

                    // get language information
                    $queryTypeLangInfo = $objStatus->getStatusLangInfoById($id);

                    if ($queryTypeInfo && $queryTypeLangInfo) {
                        foreach ($queryTypeLangInfo as $query_type_lang) {
                            $queryTypeInfo['ticket_status'][$query_type_lang['id_lang']] = $query_type_lang['ticket_status'];
                        }
                        $smartyVar['queryTypeInfo'] = $queryTypeInfo;
                        $this->context->smarty->assign($smartyVar);
                    } else {
                        $this->errors[] = $this->l('Status value information not found.');
                    }
                }
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
            $statusNameError = 0;
            $id = Tools::getValue('id');
            $languages = Language::getLanguages();
            $defaultIdLang = $this->context->language->id;
            //  query type validation
            if (trim(Tools::getValue('status_name_'.$defaultIdLang))) {
                foreach ($languages as $language) {
                    if (!Validate::isCatalogName(Tools::getValue('status_name_'.$language['id_lang']))) {
                        $statusNameError = 1;
                    }
                    if (Tools::strlen(Tools::getValue('status_name_'.$language['id_lang'])) > 50) {
                        $statusNameError = 2;
                    }
                }
            } else {
                $default_lang = Language::getLanguage($defaultIdLang);
                $this->errors[] = $this->l('Please input status value in ').$default_lang['name'];
            }

            if ($statusNameError == 1) {
                $this->errors[] = $this->l(
                    'Status value must not have invalid characters'
                ).' <>;=#{}';
            }
            if ($statusNameError == 2) {
                $this->errors[] = $this->l(
                    'Maximum query type length allowed is 50 characters.'
                );
            }

            if (empty($this->errors)) {
                // id edit then first delete group mapped data
                if ($id) {
                    $objStatus = new WkHdStatusCode((int) $id);
                // add new query type
                } else {
                    $objStatus = new WkHdStatusCode();
                }

                foreach ($languages as $language) {
                    $lang_id = $language['id_lang'];
                    if (!Tools::getValue('status_name_'.$lang_id)) {
                        $lang_id = $defaultIdLang;
                    }
                    $objStatus->ticket_status[$language['id_lang']] = pSQL(Tools::getValue('status_name_'.$lang_id));
                }
                $objStatus->save();
                $savedIdQuery = $objStatus->id;
                if (!$id) {
                    $mapObj = new WkHdStatusMapping();
                    $mapObj->id_status = (int) $savedIdQuery;
                    $mapObj->id_status_selected = (int) 0;
                    $mapObj->save();
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


    protected function processBulkDelete()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $toDelete = array();
            if (is_array($this->boxes) && !empty($this->boxes)) {
                $objTicket = new WkHdTicket();
                $count = 0;
                foreach ($this->boxes as $id) {
                    if ($id <= 6) {
                        $this->errors[] = $this->l('You can not delete predefine status.');
                    } else {
                        $objStatus = new WkHdStatusCode((int) $id);
                        $objStatus->delete();
                        $mapObj = new WkHdStatusMapping((int) $id);
                        $mapObj->delete();
                    }
                }
            } else {
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
}
