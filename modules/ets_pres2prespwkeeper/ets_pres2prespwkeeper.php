<?php
/**
 * 2007-2018 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2018 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
	exit;      
class Ets_pres2prespwkeeper extends Module
{
    public $is14;
    protected $_html = '';
	protected $_postErrors = array();
    public function __construct()
	{
		$this->name = 'ets_pres2prespwkeeper';
		$this->tab = 'front_office_features';
		$this->version = '1.0.5';
		$this->author = 'ETS-Soft';
		$this->need_instance = 0;
		$this->secure_key = Tools::encrypt($this->name);        
		$this->bootstrap = true;
        $this->module_key = '8c4686a2fe6d643fe0dea93e2e0a7082';
		parent::__construct();
        $this->context = Context::getContext();
        $this->url_module = $this->_path;
        $this->displayName = $this->l('Prestashop Password Keeper');
		$this->description = $this->l('Keep old passwords when migrate data between Prestashop websites using Prestashop Migrator');
        $this->is14 = version_compare(_PS_VERSION_, '1.5.0', '<=')&&version_compare(_PS_VERSION_, '1.4.0', '>=');
    }
    /**
	 * @see Module::install()
	 */
    public function install()
	{
        return parent::install() && $this->overrideDir();     
    }
    /**
	 * @see Module::uninstall()
	 */
	public function uninstall()
	{
        Configuration::deleteByName('ETS_NEW_COOKIE_KEY');
        return parent::uninstall();
    }
    public function overrideDir()
    {
        if (!$this->is14)
            return true;  
         /*@override class*/   
        $dir = _PS_ROOT_DIR_.'/override/classes';
        if(!is_dir($dir)){
            @mkdir($dir, 0777);
        }
        if (is_dir($dir)){
            if (($dest =  $dir.'/Customer.php') && !file_exists($dest)){
                $source = dirname(__FILE__).'/classes/Customer14.php';
                Ets_pres2prespwkeeper::copy($source, $dest);
            }
            if (($dest =  $dir.'/Employee.php') && !file_exists($dest)){
                $source = dirname(__FILE__).'/classes/Employee14.php';
                Ets_pres2prespwkeeper::copy($source, $dest);
            }
        }
        return true;
    }  
    public static function copy($source, $destination, $stream_context = null)
    {
        if (is_null($stream_context) && !preg_match('/^https?:\/\//', $source)) {
            return @copy($source, $destination);
        }
        return @file_put_contents($destination, Tools::file_get_contents($source, false, $stream_context));
    }
    protected function _postValidation()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			if (!Tools::getValue('ETS_NEW_COOKIE_KEY'))
				$this->_postErrors[] = $this->l('_COOKIE_KEY_ of source site is required');
		}
	}
	protected function _postProcess()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			Configuration::updateValue('ETS_NEW_COOKIE_KEY', Tools::getValue('ETS_NEW_COOKIE_KEY'));
		}
		$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
	}
    public function getContent()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= $this->displayError($err);
		}
		$this->_html .= $this->renderForm();
		return $this->_html;
	}
    public function renderForm()
	{
	   if($this->is14)
       {
            $this->context->smarty->assign(
                array(
                    'ETS_NEW_COOKIE_KEY'=> Tools::getValue('ETS_NEW_COOKIE_KEY',Configuration::get('ETS_NEW_COOKIE_KEY')),
                )
            );
            $this->_html.= $this->display(__FILE__,'views/templates/hook/form.tpl');
       }
       else
       {
            $fields_form = array(
    			'form' => array(
    				'legend' => array(
    					'title' => $this->l('Setting'),
    					'icon' => 'icon-envelope'
    				),
    				'input' => array(
    					array(
    						'type' => 'text',
    						'label' => $this->l('_COOKIE_KEY_ of source Prestashop website'),
    						'name' => 'ETS_NEW_COOKIE_KEY',
    						'required' => true,
                            'desc' => $this->l('_COOKIE_KEY_ is provided when you finish the migration using Prestashop Migrator. It is also available on settings file (settings.inc.php) of source website.')
    					),
    				),
    				'submit' => array(
    					'title' => $this->l('Save'),
    				)
    			),
    		);
    		$helper = new HelperForm();
    		$helper->show_toolbar = false;
    		$helper->table = $this->table;
    		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
    		$helper->default_form_language = $lang->id;
    		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
    		$this->fields_form = array();
    		$helper->id = (int)Tools::getValue('id_carrier');
    		$helper->identifier = $this->identifier;
    		$helper->submit_action = 'btnSubmit';
    		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
    		$helper->token = Tools::getAdminTokenLite('AdminModules');
    		$helper->tpl_vars = array(
    			'fields_value' => $this->getConfigFieldsValues(),
    			'languages' => $this->context->controller->getLanguages(),
    			'id_language' => $this->context->language->id
    		);
    		return $helper->generateForm(array($fields_form));
       }
	   
	}
	public function getConfigFieldsValues()
	{
		return array(
			'ETS_NEW_COOKIE_KEY' => Tools::getValue('ETS_NEW_COOKIE_KEY',Configuration::get('ETS_NEW_COOKIE_KEY')),
		);
	}  
}