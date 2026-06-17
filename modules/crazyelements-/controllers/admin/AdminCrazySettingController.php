<?php
defined( '_PS_VERSION_' ) or exit;
require_once _PS_MODULE_DIR_ . 'crazyelements/PrestaHelper.php';
require_once _PS_MODULE_DIR_ . 'crazyelements/includes/plugin.php';
use CrazyElements\PrestaHelper;
class AdminCrazySettingController extends AdminController {


	public $dirpaths                     = array();
	public $json_file_name               = '';
	public $svg_file_name                = '';
	public $new_json                     = array();
	public $custom_icon_upload_font_name = '';
	public $text_file_name               = 'fontarray.txt';
	public $new_json_file_name           = 'fontarray.json';
	public $folder_name                  = '';
	public $first_icon_name              = '';
	public function __construct() {
		$this->context   = Context::getContext();
		$this->bootstrap = true;
		$this->table     = 'configuration';
		parent::__construct();
	}

	public function renderList() {
		$check_yes           = '';
		$check_no            = '';
		$page_title_selector = PrestaHelper::get_option( 'page_title' );
		if ( PrestaHelper::get_option( 'presta_editor_enable' ) == 'yes' ) {
			$check_yes = 'checked = checked';
			$check_no  = '';
		}
		if ( PrestaHelper::get_option( 'presta_editor_enable', 'no' ) == 'no' ) {
			$check_no  = 'checked = checked';
			$check_yes = '';
		}
		$content_check_yes = '';
		$content_check_no  = '';
		$disable_ce = '';
		if ( PrestaHelper::get_option( 'crazy_content_disable' ) == 'yes' ) {
			$content_check_yes = 'checked = checked';
			$content_check_no  = '';
			$checked_pages = PrestaHelper::get_option( 'specific_page_disable' );
			$checked_pages = Tools::jsonDecode( $checked_pages, true );
			$page_types = array(
				'index' => 'Homepage',
				'cms' => 'Cms',
				'product' => 'Product',
				'category' => 'Product Category',
				'supplier' => 'Supplier',
				'manufacturer' => 'Manufacturer'
			);
			$page_options = '';
			foreach($page_types as $key => $p_type){
				$selected = '';
				
				if(isset($checked_pages[$key])){
					$selected = ' checked="checked" ';
				}
				$page_options .= '<div class="specific-page"><input type="checkbox" id="'.$key.'" name="specific_page['.$key.']" '.$selected.'> <span>' . $p_type. '</span> </div>';
			}
			$disable_ce = '<div class="form-group">
			<label class="control-label col-lg-3">Enable Crazyelements Content On</label>
			<div class="col-lg-9 specific-page-wrapper">
			'.$page_options.'
			</div>
		</div>';
		}
		if ( PrestaHelper::get_option( 'crazy_content_disable', 'no' ) == 'no' ) {
			$content_check_no  = 'checked = checked';
			$content_check_yes = '';
		}
		$validity_msg    = '';
		$info_msg = '';
		$license_bt      = 'Activate License';
		$ce_license_name = 'license_data';
		$tag_text        = 'Deactivated';
		$tag_class       = 'crazy-licence-deactive';
		$activate_l_msg = '';
		$activated_msg = '';
		if ( PrestaHelper::get_option( 'ce_licence', '' ) == '' ) {
			$validity_msg = '<div class="help-block"> Enter Your License.<a class="get-prod-bt" href="https://classydevs.com/prestashop-page-builder/pricing/" target="_blank"> Click Here </a> To Get A Valid License Key</div>';
			$activate_l_msg = ' Activate the License to Use this Feature';
		} else {
			$license_status = PrestaHelper::get_option( 'ce_licence_status', 'invalid' );
			if ( $license_status != 'valid' ) {
				if($license_status == 'expired'){
					$tag_text        = 'Expired';
					$validity_msg = '<div class="error-block"> Your License Key is Expired. Click Here. <a href="https://classydevs.com/prestashop-page-builder/pricing/" target="_blank" class="get-prod-bt"> to Get A New License. </a></div>';
				}else{
					$validity_msg = '<div class="error-block"> Your License Key is Invalid. Please Enter Valid License Key <a href="https://classydevs.com/prestashop-page-builder/pricing/" target="_blank" class="get-prod-bt"> Click Here </a> To Get A Valid License Key</div>';
				}
				
				$activate_l_msg = ' Activate the License to Use this Feature';
			} else {
				$expirydate = PrestaHelper::get_option( 'ce_licence_expires');
				$today = date("Y-m-d H:i:s"); 
				$cookie = new Cookie( 'check_update' );
				$cookie_version = $cookie->check_update;
				if(!isset($cookie_version) || $cookie_version == false){
					$cookie_version = CRAZY_VERSION;
				}
				$d_link = PrestaHelper::get_option( 'ce_new_v' );
				if($expirydate == 'lifetime'){
					$expiration_msg = "<span id='has_time'>You Have Lifetime License</span>";
				}else{
					$expirydate = date_create($expirydate);
					$today = date_create($today);
					$diff=date_diff($expirydate,$today);
					$expiration_msg = '';
					if($diff->days > 30){
						$expiration_msg = "<span id='has_time'>You need to renew your license in " . $diff->m . ' months and ' . $diff->d . ($diff->d>1 ? ' days' : " day"). '</span> <a class="how-renew" href="https://classydevs.com/docs/general-knowledgebase/licensing/how-to-renew-license/?utm_source=crazypro_bckoffice&utm_medium=crazypro_bckoffice&utm_campaign=crazypro_bckoffice&utm_id=crazypro_bckoffice&utm_term=crazypro_bckoffice&utm_content=crazypro_bckoffice" target="_blank"> How to renew?</a>';
					}elseif($diff->days < 30 && $diff->days > 0){
						$expiration_msg = "<span id='less_one_m'>You have only " . $diff->d . ($diff->d>1 ? ' days' : " day") . ' to renew your license.</span> <a class="how-renew" href="https://classydevs.com/docs/general-knowledgebase/licensing/how-to-renew-license/?utm_source=crazypro_bckoffice&utm_medium=crazypro_bckoffice&utm_campaign=crazypro_bckoffice&utm_id=crazypro_bckoffice&utm_term=crazypro_bckoffice&utm_content=crazypro_bckoffice" target="_blank"> How to renew?</a>';
					}else{
						if($diff->h > 0){
							$expiration_msg = '<span id="less_one_m">Your License License Will Expire Tomorrow.</span><a class="how-renew" href="https://classydevs.com/docs/general-knowledgebase/licensing/how-to-renew-license/?utm_source=crazypro_bckoffice&utm_medium=crazypro_bckoffice&utm_campaign=crazypro_bckoffice&utm_id=crazypro_bckoffice&utm_term=crazypro_bckoffice&utm_content=crazypro_bckoffice" target="_blank"> How to renew?</a>';
						}else{
							$expiration_msg = '<span id="less_one_m">Your License Has Expired. Please Renew Your License</span><a class="how-renew" href="https://classydevs.com/docs/general-knowledgebase/licensing/how-to-renew-license/?utm_source=crazypro_bckoffice&utm_medium=crazypro_bckoffice&utm_campaign=crazypro_bckoffice&utm_id=crazypro_bckoffice&utm_term=crazypro_bckoffice&utm_content=crazypro_bckoffice" target="_blank"> How to renew?</a>';
						}
						
					}
				}
				$validity_msg    = '<div class="success-block"> Your License is Activated </div>';
				$validity_msg    .= '<div class="success-block">'.$expiration_msg.'</div>';
				$info_msg = '<div class="col-lg-9 col-lg-offset-3 module-info"> Installed Version : ' . CRAZY_VERSION . '</div>
				<div class="col-lg-9 col-lg-offset-3 module-info"> Available Version : ' . $cookie_version . '<button type="submit" class="btn btn-default check-update-bt" name="check_update"><i class="process-icon-refresh icon-check-update"></i>Check Update
				</button></div>
				<div class="col-lg-9 col-lg-offset-3 module-info"> <a href="https://classydevs.com/docs/crazy-elements/?utm_source=crazylicsec&utm_medium=crazylicsec&utm_campaign=crazylicsec&utm_id=crazylicsec&utm_term=crazylicsec&utm_content=crazylicsec" target="_blank">Check Documentation</a></div>
				<div class="col-lg-9 col-lg-offset-3 module-info"> <a href="https://support.classydevs.com/" target="_blank">Get Support</a></div>';
				$license_bt      = 'Deactivate License';
				$ce_license_name = 'license_data_deactivate';
				$tag_text        = 'Activated';
				$tag_class       = 'crazy-licence-active';
			}
		}
		$selected_home_layout = PrestaHelper::get_option( 'crazy_home_layout', 'default' );
		$layout_types = array(
			'default' => 'Default',
			'crazy_canvas' => 'Crazy Canvas Layout',
			'crazy_fullwidth' => 'Crazy Fullwidth Layout'
		);
		$layout_options = "";
		foreach($layout_types as $key => $l_type){
			$selected = '';
			if($key == $selected_home_layout){
				$selected = ' selected="selected" ';
			}
			$layout_options .= '<option '.$selected.' value="'.$key.'">'.$l_type.'</option>';
		}
		$layout_html = '<select name="crazy_home_layout">'.$layout_options.'</select>';

		$custom_hooks = PrestaHelper::get_option( 'crazy_custom_hooks' );
		$custom_hooks = Tools::jsonDecode( $custom_hooks, true );
		$custom_hooks_html = '';
		$co = 1;
		if(isset($custom_hooks)){
			foreach($custom_hooks as $custom_hook => $mod_route){
				$custom_hooks_html .= '<div class="form-group"> <div id="conf_id_hook_data"><label class="control-label col-lg-2">('.$co.') Hook Name </label> <div class="col-lg-2"><input class="form-control " disabled="disabled" type="text" size="5" value="'.$custom_hook.'"></div><label class="control-label col-lg-2"> Page Rewrite </label> <div class="col-lg-2"><input class="form-control " disabled="disabled" type="text" size="5" value="'.$mod_route.'"> 
				</div><div class="col-lg-2"><input type="checkbox" id="vehicle1" name="remove_hook[]" value="'.$custom_hook.'"> Remove
				</div></div></div>';
				$co++;
			}
		}

		$fromhtml = '<div class="double_section"><form action="" id="configuration_form_3" method="post" enctype="multipart/form-data" class="form-horizontal"> 
        <div class="panel ce_licence_panel" id="configuration_fieldset_license"> <div class="panel-heading"> <i class="icon-cogs"></i> Enter License
        <div class="crazy-licence-status-area">
            <span class="crazy-licence-status ' . $tag_class . '">' . $tag_text . '</span>
        </div>
        </div><div class="form-wrapper"> <div class="form-group"> <div id="conf_id_license_data"> <label class="control-label col-lg-3"> License </label> <div class="col-lg-9"><input class="form-control " type="text" size="5" name="' . $ce_license_name . '" value="' . PrestaHelper::get_option( 'ce_licence' ) . '"> </div><div class="col-lg-9 col-lg-offset-3">' . $validity_msg . '</div>
		'.$info_msg.'
		</div></div></div>
        <div class="panel-footer"> 
		
        <button type="submit" class="btn btn-default pull-right" name="license_data_submit"><i class="process-icon-save"></i>' . $license_bt . '
        </button> 
		<button type="submit" class="btn btn-default pull-left" name="license_refresh"><i class="process-icon-refresh"></i>Refresh Activation
        </button>
        </div></div>
        </form>
		<form action="" id="configuration_form_4" method="post" enctype="multipart/form-data" class="form-horizontal"> 
        <div class="panel " id="configuration_fieldset_page_title_selector">
         <div class="panel-heading"> <i class="icon-cogs"></i> General Settings</div>
         <div class="form-wrapper"> 
         <div class="form-group">
          <div id="conf_id_page_title"> <label class="control-label col-lg-3"> Page Title Selector </label> 
          <div class="col-lg-9"><input class="form-control " type="text" size="5" name="page_title" value="' . $page_title_selector . '"> </div>
          <div class="col-lg-9 col-lg-offset-3"> 
          </div>
          </div>
          </div>
        <div class="form-group"> 
            <div id="conf_presta_editor_enable"> 
                <label class="control-label col-lg-3"> Enable Presta Editor </label> 
                <div class="col-lg-9"> <span class="switch prestashop-switch fixed-width-lg"> 
                <input type="radio" name="presta_editor_enable" id="presta_editor_enable_on" value="1" ' . $check_yes . '>
                <label for="presta_editor_enable_on" class="radioCheck">Yes</label>
                <input type="radio" name="presta_editor_enable" id="presta_editor_enable_off" value="0"  ' . $check_no . '>
                <label for="presta_editor_enable_off" class="radioCheck">No</label> <a class="slide-button btn"></a> </span> 
                </div>
                <div class="col-lg-9 col-lg-offset-3"> <div class="help-block"> Enable or disable Prestashop default editor </div></div>
            </div>
        </div>
        <div class="form-group"> 
            <div id="conf_crazy_content_disable"> 
                <label class="control-label col-lg-3"> Disable Crazyelements Content </label> 
                <div class="col-lg-9"> <span class="switch prestashop-switch fixed-width-lg"> 
                <input type="radio" name="crazy_content_disable" id="crazy_content_disable_on" value="1" ' . $content_check_yes . '>
                <label for="crazy_content_disable_on" class="radioCheck">Yes</label>
                <input type="radio" name="crazy_content_disable" id="crazy_content_disable_off" value="0"  ' . $content_check_no . '>
                <label for="crazy_content_disable_off" class="radioCheck">No</label> <a class="slide-button btn"></a> </span> 
                </div>
                <div class="col-lg-9 col-lg-offset-3"> <div class="help-block"> Enable or disable Crazyelements content in front </div></div>
            </div>
        </div>
		'.$disable_ce.'
        <div class="panel-footer"> <button type="submit" class="btn btn-default pull-right" name="page_title_submit"><i class="process-icon-save"></i> Save</button> </div>
          </div> 
          </div> 
        </form>
		</div>
		<form action="" id="configuration_form_6" method="post" enctype="multipart/form-data" class="form-horizontal"> 
        <div class="panel " id="configuration_fieldset_replace"> <div class="panel-heading"> <i class="icon-cogs"></i> Homepage Settings</div><div class="form-wrapper"> 
			<div class="form-group">
				<label class="control-label col-lg-3">Select Home Layout</label>
				<div class="col-lg-5">
					'.$layout_html.'
				</div>
				<div class="col-lg-9 col-lg-offset-3"> <div class="help-block"> Select Layout for your Homepage ' . $activate_l_msg . '</div></div>
			</div>
			<div class="form-group"> 
				<div id="conf_crazy_content_disable"> 
					<label class="control-label col-lg-3"> Clear displayHome Hook </label> 
					<div class="col-lg-9"> <span class="switch prestashop-switch fixed-width-lg"> 
					<input type="radio" name="remove_display_home_hook" id="remove_display_home_hook" value="1">
					<label for="remove_display_home_hook" class="radioCheck">Yes</label>
					<input type="radio" name="remove_display_home_hook" id="remove_display_home_hook_off" value="0" checked="checked">
					<label for="remove_display_home_hook_off" class="radioCheck">No</label> <a class="slide-button btn"></a> </span> 
					</div>
					<div class="col-lg-9 col-lg-offset-3"> <div class="help-block"> Remove all modules from displayHome hook </div></div>
				</div>
			</div>
		</div><div class="panel-footer"> <button type="submit" class="btn btn-default pull-right" name="crazy_home_settings"><i class="process-icon-save"></i> Save </button> </div></div>
        </form>
		<form action="" id="configuration_form_7" method="post" enctype="multipart/form-data" class="form-horizontal"> 
        <div class="panel " id="configuration_fieldset_replace"> <div class="panel-heading"> <i class="icon-cogs"></i> Add Custom Hooks</div><div class="form-wrapper"> 
			</div><div class="form-wrapper"> <div class="form-group"> <div id="conf_id_hook_data"> <label class="control-label col-lg-2"> Add Hook Name </label> <div class="col-lg-3"><input class="form-control " type="text" size="5" name="cust_hook_name"> 
			</div><label class="control-label col-lg-2"> Add Page Rewrite </label> <div class="col-lg-3"><input class="form-control " type="text" size="5" name="cust_modules_rewrite"> 
			</div></div></div>'.$custom_hooks_html.'
		</div><div class="panel-footer"> <button type="submit" class="btn btn-default pull-right" name="crazy_cust_hook"><i class="process-icon-save"></i> Save </button> </div></div>
        </form>
        <form action="" id="configuration_form_2" method="post" enctype="multipart/form-data" class="form-horizontal"> 
        <div class="panel " id="configuration_fieldset_replace"> <div class="panel-heading"> <i class="icon-cogs"></i> Update Site Address</div><div class="form-wrapper"> <div class="form-group"> <div id="conf_id_crazy_old_url"> <label class="control-label col-lg-3"> Old Url </label> <div class="col-lg-9"><input class="form-control " type="text" size="5" name="crazy_old_url" value=""> </div><div class="col-lg-9 col-lg-offset-3"> <div class="help-block"> Enter your old URL. </div></div></div></div><div class="form-group"> <div id="conf_id_crazy_new_url"> <label class="control-label col-lg-3"> New Url </label> <div class="col-lg-9"><input class="form-control " type="text" size="5" name="crazy_new_url" value=""> </div><div class="col-lg-9 col-lg-offset-3"> <div class="help-block"> Enter your new URL. </div></div></div></div></div><div class="panel-footer"> <button type="submit" class="btn btn-default pull-right" name="crazy_url_submit"><i class="process-icon-save"></i> Replace URL</button> </div></div>
        </form>
		<div class="double_section">
        <form action="" id="configuration_form_1" method="post" enctype="multipart/form-data" class="form-horizontal"> 
        <div class="panel " id="configuration_fieldset_cache"> 
            <div class="panel-heading"> <i class="icon-cogs"></i> Clear Cache for Crazy</div>
            <div class="form-wrapper"> 
                <div class="form-group"> 
                    <div id="conf_id_crazy_clear_cache"> 
                        <label class="control-label col-lg-3"> Clear Cache </label> 
                        <div class="col-lg-9"> <span class="switch prestashop-switch fixed-width-lg"> <input type="radio" name="crazy_clear_cache" id="crazy_clear_cache_on" value="1"><label for="crazy_clear_cache_on" class="radioCheck">Yes</label><input type="radio" name="crazy_clear_cache" id="crazy_clear_cache_off" value="0" checked="checked"><label for="crazy_clear_cache_off" class="radioCheck">No</label> <a class="slide-button btn"></a> </span> </div>
                        <div class="col-lg-9 col-lg-offset-3"> <div class="help-block"> If your css is not working clearing cache might help. </div></div>
                    </div>
                 </div>
            </div>
            <div class="panel-footer"> <button type="submit" class="btn btn-default pull-right" name="crazy_clear_cache_submit"><i class="process-icon-save"></i> Clear Cache</button> 
            </div>
        </div>
        </form>
        <form action="" id="configuration_form_5" method="post" enctype="multipart/form-data" class="form-horizontal"> 
        <div class="panel " id="configuration_fieldset_mailchimp"> <div class="panel-heading"> <i class="icon-cogs"></i> Enter Mailchimp API Key</div><div class="form-wrapper"> <div class="form-group"> <div id="conf_id_license_data"> <label class="control-label col-lg-3"> Api Key </label> <div class="col-lg-9"><input class="form-control " type="text" size="5" name="mailchimp_data" value="' . PrestaHelper::get_option( 'mailchimp_data' ) . '"> </div><div class="col-lg-9 col-lg-offset-3"> <div class="help-block"> Enter Your Mailchimp API Key. </div></div></div></div></div><div class="panel-footer"> <button type="submit" class="btn btn-default pull-right" name="mailchimp_data_submit"><i class="process-icon-save"></i> Save </button> </div></div>
        </form></div>';
		$html     = parent::renderList() . $fromhtml;
		return $html;
	}

	public function initContent() {
		PrestaHelper::get_lience_expired_date();
		if ( Tools::isSubmit( 'crazy_home_settings' ) ) {
			$crazy_home_layout = Tools::getValue( 'crazy_home_layout' );
			PrestaHelper::update_option( 'crazy_home_layout', $crazy_home_layout );
			$remove_display_home_hook = Tools::getValue( 'remove_display_home_hook' );
			if ( $remove_display_home_hook == '1' ) {
				$hookid = Hook::getIdByName('displayHome');
				$moduleslist = Hook::getModulesFromHook($hookid);
				
				foreach($moduleslist as $module){
					$mod_ins = Module::getInstanceByName( trim($module['name']) );
					$mod_ins->unregisterHook('displayHome');
				}
			}
			Tools::redirectAdmin( $this->context->link->getAdminLink( 'AdminCrazySetting' ) );
		}
		if ( Tools::isSubmit( 'crazy_cust_hook' ) ) {
			$cust_hook_name = Tools::getValue( 'cust_hook_name' );
			$cust_modules_rewrite = Tools::getValue( 'cust_modules_rewrite' );
			$remove_hook = Tools::getValue( 'remove_hook' );
			$custom_hooks = PrestaHelper::get_option( 'crazy_custom_hooks' );
			$custom_hooks = Tools::jsonDecode( $custom_hooks, true );
			if(isset($remove_hook)){
				foreach($remove_hook as $hook){
					unset($custom_hooks[$hook]);
				}
			}
			if($cust_hook_name != ''){				
				$custom_hooks[$cust_hook_name] = $cust_modules_rewrite;
				$custom_hooks = Tools::jsonEncode( $custom_hooks );
			}
			PrestaHelper::update_option( 'crazy_custom_hooks',  $custom_hooks);
			Tools::redirectAdmin( $this->context->link->getAdminLink( 'AdminCrazySetting' ) );
		}
		if ( Tools::isSubmit( 'page_title' ) ) {
			$page_title = Tools::getValue( 'page_title' );
			PrestaHelper::update_option( 'page_title', $page_title );
			$presta_editor_enable = Tools::getValue( 'presta_editor_enable' );
			if ( $presta_editor_enable == '1' ) {
				$presta_editor_enable = 'yes';
			} else {
				$presta_editor_enable = 'no';
			}
			PrestaHelper::update_option( 'presta_editor_enable', $presta_editor_enable );
			$crazy_content_disable = Tools::getValue( 'crazy_content_disable' );
			if ( $crazy_content_disable == '1' ) {
				$crazy_content_disable = 'yes';
			} else {
				$crazy_content_disable = 'no';
				PrestaHelper::update_option( 'specific_page_disable', '' );
			}
			$specific_page = Tools::getValue( 'specific_page' );
			$specific_page = Tools::jsonEncode( $specific_page );
			PrestaHelper::update_option( 'specific_page_disable', $specific_page );
			PrestaHelper::update_option( 'crazy_content_disable', $crazy_content_disable );
			$crazy_home_layout = Tools::getValue( 'crazy_home_layout' );
			PrestaHelper::update_option( 'crazy_home_layout', $crazy_home_layout );
			$remove_display_home_hook = Tools::getValue( 'remove_display_home_hook' );
			if ( $remove_display_home_hook == '1' ) {
				$hookid = Hook::getIdByName('displayHome');
				$moduleslist = Hook::getModulesFromHook($hookid);
				
				foreach($moduleslist as $module){
					$mod_ins = Module::getInstanceByName( trim($module['name']) );
					$mod_ins->unregisterHook('displayHome');
				}
			}
			Tools::redirectAdmin( $this->context->link->getAdminLink( 'AdminCrazySetting' ) );
		}
		if ( Tools::isSubmit( 'license_data' ) ) {
			$license_data = Tools::getValue( 'license_data' );

			if ( $license_data == '' ) {
				PrestaHelper::update_option( 'ce_licence_status', 'false' );
			}
			PrestaHelper::update_option( 'ce_licence_date', '' );
			PrestaHelper::update_option( 'ce_licence', $license_data );
			PrestaHelper::get_lience( $license_data );
			Tools::redirectAdmin( $this->context->link->getAdminLink( 'AdminCrazySetting' ) );
		}
		if ( Tools::isSubmit( 'check_update' ) ) {
			$cookie = new Cookie( 'check_update' );
			if ( isset( $cookie->check_update ) ) {
				unset($cookie->check_update);
			}
			
			Tools::redirectAdmin( $this->context->link->getAdminLink( 'AdminCrazySetting' ) );
		}
		if ( Tools::isSubmit( 'license_refresh' ) ) {
			$license_data = Tools::getValue( 'license_data_deactivate' );
			PrestaHelper::refresh_licence( $license_data );
			Tools::redirectAdmin( $this->context->link->getAdminLink( 'AdminCrazySetting' ) );
		}
		if ( Tools::isSubmit( 'license_data_deactivate' ) ) {
			$license_data = Tools::getValue( 'license_data_deactivate' );
			PrestaHelper::deactivated_licence( $license_data );
			Tools::redirectAdmin( $this->context->link->getAdminLink( 'AdminCrazySetting' ) );
		}
		
		if ( Tools::isSubmit( 'mailchimp_data' ) ) {
			$mailchimp_data = Tools::getValue( 'mailchimp_data' );
			PrestaHelper::update_option( 'mailchimp_data', $mailchimp_data );
			Tools::redirectAdmin( $this->context->link->getAdminLink( 'AdminCrazySetting' ) );
		}
		if ( Tools::isSubmit( 'crazy_clear_cache' ) ) {
			$crazy_clear_cache = Tools::getValue( 'crazy_clear_cache' );
			if ( $crazy_clear_cache ) {
				$this->clear_cache();
				Tools::redirectAdmin( $this->context->link->getAdminLink( 'AdminCrazySetting' ) );
			}
		}
		
		if ( Tools::isSubmit( 'crazy_old_url' ) && Tools::isSubmit( 'crazy_new_url' ) ) {
			$from = ! empty( Tools::getValue( 'crazy_old_url' ) ) ? Tools::getValue( 'crazy_old_url' ) : '';
			$to   = ! empty( Tools::getValue( 'crazy_new_url' ) ) ? Tools::getValue( 'crazy_new_url' ) : '';
			$this->replace_urls( $from, $to );
			$this->clear_cache();
		}
		parent::initContent();
	}

	public function initHeader() {
		parent::initHeader();
	}

	public function clear_cache() {
		$files = glob( _PS_MODULE_DIR_ . 'crazyelements/assets/css/frontend/css/*' ); // get all file names
		foreach ( $files as $file ) { // iterate files
			if ( is_file( $file ) ) {
				unlink( $file ); // delete file
			}
		}
		Db::getInstance()->delete( 'crazy_options', "option_name like '_elementor_css%'  OR option_name ='_elementor_global_css'" );
		Db::getInstance()->delete( 'crazy_options', "option_name ='elementor_remote_info_library'" );
		$admincontroller = Tools::getValue( 'controller' );
		$token           = Tools::getValue( 'token' );
		Configuration::updateValue( 'crazy_clear_cache', 0 );
	}

	public function replace_urls( $from, $to ) {
		if ( $from === $to ) {
			throw new \Exception( PrestaHelper::__( 'The `from` and `to` URL\'s must be different', 'elementor' ) );
		}
		$is_valid_urls = ( filter_var( $from, FILTER_VALIDATE_URL ) && filter_var( $to, FILTER_VALIDATE_URL ) );
		if ( ! $is_valid_urls ) {
			throw new \Exception( PrestaHelper::__( 'The `from` and `to` URL\'s must be valid URL\'s', 'elementor' ) );
		}
		Db::getInstance()->update(
			'crazy_content_lang',
			array(
				'resource' => array(
					'type'  => 'sql',
					'value' => "REPLACE(`resource`, '" . str_replace(
						'/',
						'\\\/',
						$from
					) . "','" . str_replace(
						'/',
						'\\\/',
						$to
					) . "')",
				),
			),
			"`resource` LIKE '[%' "
		);
	}
}