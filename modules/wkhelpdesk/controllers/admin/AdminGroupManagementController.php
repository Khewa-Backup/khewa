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

class AdminGroupManagementController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->table = 'wk_hd_group';
        $this->className = 'WkHdGroup';
        $this->bootstrap = true;
        $this->lang = true;
        $this->addRowAction('edit');
        $this->addRowAction('view');
        $this->addRowAction('delete');
        $this->identifier = 'id';
        parent::__construct();
        $this->toolbar_title = $this->l('Groups');
        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            Shop::addTableAssociation('wk_hd_group', array('type' => 'shop', 'primary' => 'id'));
        }
        $this->_join .= WkHdGroup::addSqlAssociationCustom('wk_hd_group', 'a', false);
        $this->_where .= ' AND b.`id_shop`='.min(Shop::getContextListShopID());
        $this->_group = ' GROUP BY a.id';

        $this->_where .= ' AND a.`is_default_group` != 1';
        $this->_select = 'b.`group_name`';

        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $this->fields_list = array(
                'id' => array(
                    'title' => $this->l('ID'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs',
                ),
                'group_name' => array(
                    'title' => $this->l('Group name'),
                    'align' => 'center',
                ),
                'active' => array(
                    'title' => $this->l('Status'),
                    'active' => 'status',
                    'type' => 'bool',
                    'align' => 'center',
                ),
                'date_add' => array(
                    'title' => $this->l('Add date'),
                    'type' => 'datetime',
                    'filter_key' => 'a!date_add'
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
                'desc' => $this->l('Add new group'),
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
                $objTicketAgent = new WkHdTicketAgent();
                $allAgents = $objTicketAgent->getAllAgent();
                $smartyVars = array(
                    'languages' => Language::getLanguages(),
                    'total_languages' => count(Language::getLanguages()),
                    'current_lang' => Language::getLanguage((int) $this->context->language->id)
                );

                // get all ticket agents
                if ($allAgents) {
                    $smartyVars['allAgents'] = $allAgents;
                    if ($this->display == 'edit') {
                        $idGroup = Tools::getValue('id');
                        $objGroup = new WkHdGroup();
                        $objGroupAgentMapping = new WkHdGroupAgentMapping();

                        // get group information
                        $groupInfo = $objGroup->getGroupInfoByIdGroup($idGroup);

                        // get group language information
                        $groupLangInfo = $objGroup->getGroupLangInfoByIdGroup($idGroup);

                        if ($idGroup) {
                            if ($groupInfo && $groupLangInfo) {
                                foreach ($groupLangInfo as $group_lang) {
                                    $groupInfo['group_name'][$group_lang['id_lang']] = $group_lang['group_name'];
                                }

                                $smartyVars['groupInfo'] = $groupInfo;
                            } else {
                                $this->errors[] = $this->l('Group information not found.');
                            }
                        }

                        //get group and agent mapping information
                        $groupAgentMapping = $objGroupAgentMapping->getInfoByIdGroup($idGroup);
                        if ($idGroup) {
                            if ($groupAgentMapping) {
                                $smartyVars['groupAgentMapping'] = $groupAgentMapping;
                            } else {
                                $this->errors[] = $this->l('Group agent mapping information not found.');
                            }
                        }
                    }
                }

                $this->context->smarty->assign($smartyVars);
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

    public function renderView()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $idGroup = Tools::getValue('id');
            if ($idGroup) {
                $objGroup = new WkHdGroup();
                // get group information
                $groupInfo = $objGroup->getGroupInfoByIdGroup($idGroup, $this->context->language->id);
                if ($groupInfo) {
                    // get group and agent mapping information
                    $objGroupAgentMapping = new WkHdGroupAgentMapping();
                    $mappedGroupAgent = $objGroupAgentMapping->getMappedAgentInfoByIdGroup($idGroup);

                    $this->context->smarty->assign(
                        array(
                            'groupInfo' => $groupInfo,
                            'mappedGroupAgent' => $mappedGroupAgent,
                        )
                    );
                } else {
                    $this->errors[] = $this->l('Group information not found.');
                }
            } else {
                $this->errors[] = $this->l('Group information not found.');
            }

            return parent::renderView();
        } else {
            $this->errors[] = $this->l('You do not have access right to view this page.');
        }
    }

    public function processSave()
    {
        $objHdEmployee = new WkHdTicketAgent();
        if ($objHdEmployee->validateEmployee($this->context->employee)) {
            $groupNameError = 0;
            $idGroup = Tools::getValue('id'); //if edit
            $languages = Language::getLanguages();
            $selectedGroupAgent = Tools::getValue('groupAgent');
            $defaultIdLang = $this->context->language->id;

            //validate group name
            if (trim(Tools::getValue('group_name_'.$defaultIdLang))) {
                foreach ($languages as $language) {
                    if (!Validate::isCatalogName(Tools::getValue('group_name_'.$language['id_lang']))) {
                        $groupNameError = 1;
                    }
                    if (Tools::strlen(Tools::getValue('group_name_'.$language['id_lang'])) > 128) {
                        $groupNameError = 2;
                    }
                }
            } else {
                $defaultLang = Language::getLanguage((int) $defaultIdLang);
                $this->errors[] = $this->l('Group name is required ').$defaultLang['name'];
            }

            if ($groupNameError == 1) {
                $this->errors[] = $this->l(
                    'Group name must not have Invalid characters like '
                ).' <>;=#{}';
            }
            if ($groupNameError == 2) {
                $this->errors[] = $this->l(
                    'Maximum query type length allowed is 128 characters.'
                );
            }

            if (empty($selectedGroupAgent)) {
                $this->errors[] = $this->l('Select atleast one agent in this group.');
            }

            if (empty($this->errors)) {
                //if edit then first delete agent mapping
                if ($idGroup) {
                    $objGroup = new WkHdGroup((int) $idGroup);
                    $objGroupAgentMapping = new WkHdGroupAgentMapping();
                    $objGroupAgentMapping->deleteMappingByIdGroup($idGroup);
                // add new group
                } else {
                    $objGroup = new WkHdGroup();
                    $objGroup->is_default_group = (int) 0;
                    $objGroup->active = (int) 1;
                }

                foreach ($languages as $language) {
                    $idLang = $language['id_lang'];
                    if (!Tools::getValue('group_name_'.$idLang)) {
                        $idLang = $defaultIdLang;
                    }
                    $objGroup->group_name[$language['id_lang']] = pSQL(Tools::getValue('group_name_'.$idLang));
                }
                $objGroup->save();
                $addedIdGroup = $objGroup->id;

                // save language data
                if ($addedIdGroup) {
                    foreach ($selectedGroupAgent as $group_agent_id) {
                        $objGroupAgentMapping = new WkHdGroupAgentMapping();
                        $objGroupAgentMapping->id_agent = (int) $group_agent_id;
                        $objGroupAgentMapping->id_group = (int) $addedIdGroup;
                        $objGroupAgentMapping->save();
                    }
                }

                if (Tools::isSubmit('submitAdd'.$this->table.'AndStay')) {
                    if ($idGroup) {
                        Tools::redirectAdmin(
                            self::$currentIndex.'&id='.(int) $addedIdGroup.'&update'.
                            $this->table.'&conf=4&token='.$this->token
                        );
                    } else {
                        Tools::redirectAdmin(
                            self::$currentIndex.'&id='.(int) $addedIdGroup.'&update'.
                            $this->table.'&conf=3&token='.$this->token
                        );
                    }
                } else {
                    if ($idGroup) {
                        Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
                    } else {
                        Tools::redirectAdmin(self::$currentIndex.'&conf=3&token='.$this->token);
                    }
                }
            } else {
                if ($idGroup) {
                    $this->display = 'edit';
                } else {
                    $this->display = 'add';
                }
            }
        } else {
            $this->errors[] = $this->l('You do not have access right to view this page.');
        }
    }
}
