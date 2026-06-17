<?php


/**
 * <ModuleName> => cheque
 * <FileName> => validation.php
 * Format expected: <ModuleName><FileName>ModuleFrontController
 */
require_once _PS_MODULE_DIR_ . 'crazyelements/includes/plugin.php';
require_once _PS_MODULE_DIR_ . 'crazyelements/classes/CrazyPrdlayouts.php';

use CrazyElements\PrestaHelper;
use CrazyElements\Plugin;
class CrazyElementsPrdlayoutsModuleFrontController extends ModuleFrontController {



	public $ssl = true;
	public function setMedia( $isNewTheme = false ) {
		parent::setMedia();
	}
	public function initContent() {
		parent::initContent();

		$this->assignVariables();

		Plugin::instance()->initForAjax();

		$template_id       = Tools::getValue( 'elementor_library' );
		$query             = 'SELECT elements FROM ' . _DB_PREFIX_ . "crazy_library where id_crazy_library='$template_id'";
		$get_elements_data = Db::getInstance()->getValue( $query );

		$get_elements_data = json_decode( $get_elements_data, true );

			ob_start();
				Plugin::instance()->loadElementsForTemplate( $get_elements_data );
				$parsed_content = ob_get_contents();
			ob_end_clean();
			$this->context->smarty->assign(
				array(
					'parsed_content' => $parsed_content
				)
			);
		  // Will use the file modules/cheque/views/templates/front/validation.tpl

		 $this->setTemplate( 'module:crazyelements/views/templates/front/layoutbuilder.tpl' );
	}

	public function assignVariables() {
		PrestaHelper::$hook_current              = Tools::getValue( 'hook' );
		PrestaHelper::$id_content_global         = Tools::getValue( 'id_crazyprdlayouts' );
		PrestaHelper::$id_content_primary_global = PrestaHelper::getRealPostId( Tools::getValue( 'id_crazyprdlayouts' ) );
		PrestaHelper::$id_lang_global            = Tools::getValue( 'id_lang' );
		PrestaHelper::$id_shop_global            = $this->context->shop->id;
	}
}