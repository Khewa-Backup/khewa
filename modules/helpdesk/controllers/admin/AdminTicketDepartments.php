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

class AdminTicketDepartmentsController extends ModuleAdminController
{
    public function __construct()
    {
        $os = PHP_OS;
        switch ($os) {
            case 'Linux':
                define('SEPARATOR', '/');
                break;
            case 'Windows':
                define('SEPARATOR', '\\');
                break;
            default:
                define('SEPARATOR', '/');
                break;
        }

        $this->table = 'fmm_hd_departments';
        $this->className = 'Ticketdepartments';
        $this->identifier = 'departments_id';
        $this->lang = true;
        $this->deleted = false;
        $this->colorOnBackground = false;
        $this->bootstrap = true;
        parent::__construct();
        $this->context = Context::getContext();

        $this->fields_list = array(
            'departments_id' => array(
                'title'     => $this->module->l('ID'),
                'width' => 25
            ),
            'department_email' => array(
                'title'     => $this->module->l('Department Email'),
                'width' => 400
            ),
            'department_title' => array(
                'title'     => $this->module->l('Department Title'),
                'width' => 400
            )
        );

        $this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
    }

    public function renderList()
    {
        // Adds an Edit button for each result
        $this->addRowAction('edit');
        // Adds a Delete button for each result
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function renderForm()
    {
        $date = date('Y-m-d H:i:s');
        $this->fields_form = array(
            'tinymce' => false,
            'legend' => array(
                'title' => $this->l('Ticket Department'),
                'image' => '../img/admin/add.gif'
            ),

            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Department Title:'),
                    'name' => 'department_title',
                    'lang' => true,
                    'hint' => $this->l('Forbidden characters:').' <>;=#{}',
                    'size' => 60,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Department Email:'),
                    'name' => 'department_email',
                    'size' => 60,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' <>;=#{}'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'created_time',
                    'value' => $date
                ),
                
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Department Signature:'),
                    'name' => 'department_signature',
                    'lang' => false,
                    'cols' => 80,
                    'rows' => 10,
                    'class' => 'rte',
                    'hint' => $this->l('Invalid characters:').' <>;=#{}'
                ),
                
                array(
                    'type' => 'radio',
                    'label' => $this->l('Status:'),
                    'name' => 'department_status',
                    'required' => true,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                            )
                        )
                )
                )
        );

        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association'),
                'name' => 'checkBoxShopAsso',
                );
        }

        $this->fields_form['submit'] = array(
            'title' => $this->l('Save'),
            'class' => 'button pull-right'
        );

        if (!($ticketdepartment = $this->loadObject(true))) {
            return;
        }

        foreach ($this->_languages as $language) {
            $this->fields_value['department_title_'.$language['id_lang']] = htmlentities(Tools::stripslashes($this->getFieldValue(
                $ticketdepartment,
                'department_title',
                $language['id_lang']
            )), ENT_COMPAT, 'UTF-8');
        }

        return parent::renderForm();
    }
    
    public function init()
    {
        parent::init();
        Shop::addTableAssociation($this->table, array('type' => 'shop'));
        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'fmm_hd_departments_shop` sa ON (a.`departments_id` = sa.`departments_id` AND sa.id_shop = '.(int)$this->context->shop->id.') ';
        } if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->_where = ' AND sa.`id_shop` = '.(int)Context::getContext()->shop->id;
        }
    }
}
