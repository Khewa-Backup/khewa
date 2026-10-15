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

class AdminTicketPremadeTemplatesController extends ModuleAdminController
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

        $this->table = 'fmm_hd_premade';
        $this->className = 'Ticketpremades';
        $this->identifier = 'premade_id';
        $this->lang = true;
        $this->deleted = false;
        $this->colorOnBackground = false;
        $this->bootstrap = true;
        parent::__construct();
        $this->context = Context::getContext();

        $this->fields_list = array(
            'premade_id' => array(
                'title'     => $this->module->l('ID'),
                'width' => 25
            ),
            'premade_title' => array(
                'title'     => $this->module->l('Premade Title'),
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
                'title' => $this->l('Premade Reply'),
                'image' => '../img/admin/add.gif'
            ),
            
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Premade Title:'),
                    'name' => 'premade_title',
                    'lang' => true,
                    'hint' => $this->l('Forbidden characters:').' <>;=#{}',
                    'size' => 60,
                    'required' => true
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Content:'),
                    'name' => 'premade_content',
                    'lang' => true,
                    'cols' => 80,
                    'rows' => 10,
                    'class' => 'rte',
                    'hint' => $this->l('Invalid characters:').' <>;=#{}'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'created_time',
                    'value' => $date
                ),
                
                array(
                    'type' => 'radio',
                    'label' => $this->l('Status:'),
                    'name' => 'premade_status',
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
        
        $this->fields_form['submit'] = array(
            'title' => $this->l('   Save   '),
            'class' => 'button pull-right'
        );
        
        if (!($ticketpremade = $this->loadObject(true))) {
            return;
        } foreach ($this->_languages as $language) {
            $this->fields_value['premade_title_'.$language['id_lang']] = htmlentities(Tools::stripslashes($this->getFieldValue(
                $ticketpremade,
                'premade_title',
                $language['id_lang']
            )), ENT_COMPAT, 'UTF-8');
        }
        
        return parent::renderForm();
    }
}
