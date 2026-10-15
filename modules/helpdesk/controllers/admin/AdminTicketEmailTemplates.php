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

class AdminTicketEmailTemplatesController extends ModuleAdminController
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

        $this->table = 'fmm_hd_emailtemp';
        $this->className = 'Ticketemailtemps';
        $this->identifier = 'emailtemp_id';
        $this->lang = true;
        $this->deleted = false;
        $this->colorOnBackground = false;
        $this->bootstrap = true;

        parent::__construct();
        $this->context = Context::getContext();

        $this->fields_list = array(
            'emailtemp_id' => array(
                'title'     => $this->module->l('ID'),
                'width' => 25
            ),
            'emailtemp_title' => array(
                'title'     => $this->module->l('Template Title'),
                'width' => 400
            )
        );

        $this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
    }

    public function renderList()
    {
        $defaultLanguage = (int)Configuration::get('PS_LANG_DEFAULT');

        $iso = Language::getIsoById($defaultLanguage);

        $dir = dirname(__FILE__).'/../../mails/'.$iso.'/';
        $files = scandir($dir);
        $cnt = count($files);
        $this->context->smarty->assign('files', $files);
        $this->context->smarty->assign('cnt', $cnt);
        $this->context->smarty->assign('module_dir', _PS_MODULE_DIR_);
        $this->context->smarty->assign('baseuri', __PS_BASE_URI__);
        $this->context->smarty->assign(array(
        'currentIndex' => self::$currentIndex,
        'currentToken' => $this->token,
        'id_lang'   =>  $this->context->language->id,
        ));

        parent::renderList();
        return $this->context->smarty->fetch(dirname(__FILE__).'/../../views/templates/admin/ticketemailtemps/edit_templates.tpl');
    }

    public function initProcess()
    {
        $action = Tools::getValue('action');
        
        if ($action == 'ajax') {
            $file_name = Tools::getValue('file');
            $cookie = Context::getContext()->cookie;
            $iso = Language::getIsoById((int)($cookie->id_lang));
            $file = _PS_MODULE_DIR_.'helpdesk/mails/'.$iso.'/'.$file_name;
            $contents = Tools::file_get_contents($file);
            echo $contents;
            exit;
        }

        if (Tools::isSubmit('save_templates')) {
            $filename = Tools::getValue('filename');
            $content_save = Tools::getValue('body_mail');
            $cookie = Context::getContext()->cookie;
            $iso = Language::getIsoById((int)($cookie->id_lang));
            $file = _PS_MODULE_DIR_.'helpdesk/mails/'.$iso.'/'.$filename;
            @chmod($file, 0777);
            file_put_contents($file, $content_save);
            file_put_contents($file, $content_save);
        }
        parent::initProcess();
    }
}
