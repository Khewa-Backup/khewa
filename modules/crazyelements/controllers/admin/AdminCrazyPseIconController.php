<?php
require_once (dirname(__FILE__) . '/../../classes/PseIcon.php');

require_once _PS_MODULE_DIR_ . 'crazyelements/PrestaHelper.php';

use CrazyElements\PrestaHelper;

class AdminCrazyPseIconController extends AdminController {
    public $dirpaths = array();
    public $json_file_name = '';
    public $svg_file_name = '';
    public $new_json = array();
    public $custom_icon_upload_font_name = '';
    public $text_file_name = 'fontarray.txt';
    public $new_json_file_name = 'fontarray.json';
    public $folder_name = '';
    public $first_icon_name = '';
    public $allowedExts = array("zip");

    public function __construct() { 
        $this->table = 'crazy_options';  // give the table name which we have create,
        $this->className = 'PseIcon'; // class name of our object model
        $this->lang = false;
        $this->identifier = "id_options";
        $this->deleted = false;
        $this->bootstrap = true;
        $this->module = 'crazyelements';
        $this->_filter = 'and option_name="custom_icon_upload_fonts"';
        parent::__construct(); 
        $ce_licence=PrestaHelper::get_option('ce_licence','false');
        if($ce_licence!="false"){
            $this->fields_options = array(
                'icon' => array(
                    'title' => $this->l('Update Icon'),
                    'icon' => 'icon-cogs',
                    'fields' => array(
                        'crazy_new_icon' => array(
                            'title' => $this->l('Add Icomoon Icon'),
                            'desc' => $this->l('Enter your old URL.'),
                            'type' => 'file',
                            'name'=>'crazy_new_icon'
                        ),
                    ),
                    'submit' => array('title' => $this->l('Add Icon'))
                ),
            );
        }
        parent::__construct();
        $this->actions = array('delete');
    }


    public function renderList() {
        $ce_licence=PrestaHelper::get_option('ce_licence','false');
        if($ce_licence=="false"){
             $active_link=PrestaHelper::get_setting_page_url();
            return '<style>.need_to_active {font-size: 20px;color: #495157 !important;font-weight: bold;}.need_to_active_a {font-size: 12px;font-weight: bold;}</style><div class="panel col-lg-12"> <div class="panel-heading"> Need To Active Licence.<span class="badge"></span></div><div class="col-md-12"><div class="need_to_active">Need To Active Licence.</div><a class="need_to_active_a" href="'.$active_link.'">Click For Active.</a></div></div>';
        }
        $iconlistarray = PrestaHelper::get_option('custom_icon_upload_fonts');
        $iconlistarray = \Tools::jsonDecode($iconlistarray,true);
        if(empty($iconlistarray)){return parent::renderList();}
        $iconlist = $this->custom_icon_upload_return_with_title( $iconlistarray);
        $html='';
        $html.='<div class="panel col-lg-12"> <div class="panel-heading"> Icon Preview<span class="badge"></span></div><div class="row fontgroup">';
        foreach($iconlistarray as $key=> $icon){
            $context = Context::getContext();
            $context->controller->addCSS( $icon['mainurl'].'style.css');
            $html.='<div class="col-lg-6">';
            $html.='<div class="title">'. $icon['fontname'].'<span data-target="'.$icon['fontname'].'" class="font_remove"><i class="material-icons">'.$this->l('delete').'</i></span></div>';
            foreach( $iconlist[ $icon['fontname'] ] as $iconclass ){
                $html.= '<i title="'.$iconclass.'" class="'.$iconclass.'"></i>';
            }
            $html.='</div>';
        }
        $html.='</div></div><style>
        .fontgroup .title { font-size: 20px; text-transform: capitalize;}
        .fontgroup i {font-size: 20px; margin-right: 10px; float: left; margin-top: 15px; padding: 5px; border: 1px solid #eee; box-shadow: 0 0 0 1px #ddd; border-radius: 2px; cursor: pointer; }
        .fontgroup .col-md-2 { height: 60px; }
        .fontgroup span { position: relative; top: 50%; transform: translateY(-50%); float: left; font-size: 20px; text-transform: capitalize;text-decoration: underline; }.fontgroup .col-md-12 {
            border-bottom: 1px solid #eee;
            margin-bottom: 2px;
        }
        .fontgroup .font_remove {float: right;top: 23%;}
        .fontgroup .font_remove i {background: #f003;border: none;}
        </style>';
        $ultimateajaxurl = Context::getContext()->link->getAdminLink( 'AdminCrazyPseIcon' ).'&ajax=1';
        $html.= "<script type=\"text/javascript\">
        $('.font_remove').click(function(){
            var r = confirm('".$this->l('Do you really want to delete this?')."');
            if (r == true) {
                $.ajax({
                    type: 'POST',
                    cache: false,
                
                    url:'$ultimateajaxurl', 
                    data: {
                        ajax: true,
                        controller: 'AdminCrazyPseIcon',
                        action: 'RemoveCustomicon', 
                        target: $(this).attr('data-target'),
                    },
                    success: function(data) {
                        console.log(data)
                        if(data=='success'){
                            window.location.reload()
                        }
                    },
                    error: function(data) {
                        console.log(data)
                        console.log('error')
                    },
                });
            } 
        })

        </script>";
        $htmlfinal= parent::renderList().$html;
        return $htmlfinal."&nbsp";
    }

    public function ajaxProcessRemoveCustomicon() {
    
        $iconlistarray = PrestaHelper::get_option('custom_icon_upload_fonts');
        $iconlistarray = \Tools::jsonDecode($iconlistarray,true);
        unset($iconlistarray[$_POST['target']]);
        PrestaHelper::update_option('custom_icon_upload_fonts', $iconlistarray);
        echo 'success';
    }

    public function initContent() {
        PrestaHelper::get_lience_expired_date();
        if (Tools::isSubmit('submitOptionscrazy_options') ) {
            $this->folder_name=dirname(__DIR__, 2)."/assets/icons/";
            $extension = pathinfo($_FILES["crazy_new_icon"]['name'], PATHINFO_EXTENSION);
            if(!in_array($extension, $this->allowedExts)){
                return parent::initContent();
            }
            if(file_exists($this->folder_name. $_FILES["crazy_new_icon"]['name'])){
                echo "File Already Exist";
                return parent::initContent();
            }
            if (move_uploaded_file($_FILES['crazy_new_icon']['tmp_name'], $this->folder_name. $_FILES["crazy_new_icon"]['name'])) {
                $path=$this->folder_name. $_FILES["crazy_new_icon"]['name'];
                $filename = pathinfo($path, PATHINFO_FILENAME);
                $zip = new ZipArchive;
                $res = $zip->open($path);
                if ($res === true) {
                    $zip->extractTo( $this->folder_name . $filename);
                    $zip->close();
                    $this->dirpaths['unzippeddir'] =$this->folder_name  . $filename;
                    $this->dirpaths['unzippedurl'] =CRAZY_URL . 'assets/icons/' . $filename;
                    $this->dirpaths['fontsvgdir'] =  $this->dirpaths['unzippeddir'] . '/fonts';
                } else {
                    echo "Doh! I couldn't open $path";
                    die();
                }
                $this->custom_icon_upload_create();
            } else {
            echo "File was not uploaded";
            }
        }
        parent::initContent();
    }

    public function custom_icon_upload_create()
    {
        $this->json_file_name = $this->custom_icon_upload_find_file($this->dirpaths['unzippeddir'], 'json');
        $this->svg_file_name = $this->custom_icon_upload_find_file($this->dirpaths['fontsvgdir'], 'svg');
        if (empty($this->json_file_name) || empty($this->svg_file_name)) {
            $this->custom_icon_upload_remove_dir($this->dirpaths['unzippeddir']);
            die(__('Json or SVG file not found', 'custom-icon-upload'));
        }
        $jsonresponse = '';
        $svgresponse = '';
        if (file_exists($this->dirpaths['fontsvgdir'] .'/'. $this->svg_file_name)) {
            $svgresponse = file_get_contents($this->dirpaths['fontsvgdir'] .'/'. $this->svg_file_name);
        }
        if (file_exists($this->dirpaths['unzippeddir'] .'/'. $this->json_file_name)) {
            $jsonresponse = file_get_contents($this->dirpaths['unzippeddir'] .'/'. $this->json_file_name);
        }
        if ('' !== $jsonresponse && '' !== $svgresponse) {
            $xml = simplexml_load_string($svgresponse);
            $font_attr = $xml->defs->font->attributes();
            $this->custom_icon_upload_font_name = (string) $font_attr['id'];
            $file_contents = json_decode($jsonresponse);
            if($file_contents==''){
                return false;
            }
            if (!isset($file_contents->IcoMoonType)) {
                $this->custom_icon_upload_remove_dir($this->dirpaths['unzippeddir']);
                die(PrestaHelper::__('Only Support IcoMoon App Font.', 'custom-icon-upload'));
            }
            $icons = $file_contents->icons;
            foreach ($icons as $icon) {
                $icon_name = $icon->properties->name;
                $icon_class = str_replace(' ', '', $icon_name);
                $icon_class = str_replace(',', ' ', $icon_class);
                $this->new_json[$icon_name] = array(
                    "class" => $icon_class,
                );
            }
            if (!empty($this->new_json) && $this->custom_icon_upload_font_name != '') {
                $this->custom_icon_upload_create_text_file();
                $this->custom_icon_upload_create_css_again();
                $this->custom_icon_upload_create_option();
            }
        }
        return false;
    }

    public function custom_icon_upload_create_option()
    {
        $fontsoption = PrestaHelper::get_option('custom_icon_upload_fonts');
        $fontsoption = \Tools::jsonDecode($fontsoption,true);
        if (empty($fontsoption)) {
            $fontsoption = array();
        }
        if (isset($fontsoption[$this->custom_icon_upload_font_name])) {
            die(PrestaHelper::__('Same Name Font Already Install', 'custom-icon-upload'));
        }
        $fontsoption[$this->custom_icon_upload_font_name] = array(
            'maindir' => $this->dirpaths['unzippeddir'].'/',
            'mainurl' => $this->dirpaths['unzippedurl'].'/',
            'filename' => $this->text_file_name,
            'jsonfilename' => $this->new_json_file_name,
            'icondir' => $this->folder_name,
            'fontname' => $this->custom_icon_upload_font_name,
            'firsticonname' => $this->first_icon_name,
        );
        
        PrestaHelper::update_option('custom_icon_upload_fonts', $fontsoption);
    }

    public function custom_icon_upload_create_css_again()
    {
        $stylefile = $this->dirpaths['unzippeddir'] . '/style.css';
        $getcssfile = @file_get_contents($stylefile);
        if ($getcssfile) {
            $str = str_replace('icon-', $this->custom_icon_upload_font_name . '-', $getcssfile);
            $str = str_replace('.icon {', '[class^="' . $this->custom_icon_upload_font_name . '-"], [class*=" ' . $this->custom_icon_upload_font_name . '-"] {', $str);
            $str = str_replace('i {', '[class^="' . $this->custom_icon_upload_font_name . '-"], [class*=" ' . $this->custom_icon_upload_font_name . '-"] {', $str);
            $str = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $str);
            unlink($stylefile);
            $fp = fopen($stylefile, 'w');
            fwrite($fp, $str);
            fclose($fp);
            chmod($stylefile, 0777);
        } else {
            die(PrestaHelper::__('Unable to write css. Upload icons downloaded only from icomoon', 'custom-icon-upload'));
        }
    }

    public function custom_icon_upload_create_text_file()
    {
        $arr = array();
        foreach ($this->new_json as $key => $value) {
            $font_array[$this->custom_icon_upload_font_name . '-' . $key] = $this->custom_icon_upload_font_name . '-' . $value['class'];
            $arr['icons'][] = $this->custom_icon_upload_font_name . '-' . $value['class'];
            if ($this->first_icon_name == '') {
                $this->first_icon_name = $this->custom_icon_upload_font_name . '-' . $value['class'];
            }
        }
        $fp = fopen($this->dirpaths['unzippeddir'] . '/' . $this->new_json_file_name, 'w');
        fwrite($fp, json_encode($arr));
        fclose($fp);
        chmod($this->dirpaths['unzippeddir'] . '/' . $this->new_json_file_name, 0777);
        $fp = fopen($this->dirpaths['unzippeddir'] . '/' . $this->text_file_name, 'w');
        fwrite($fp, json_encode($font_array));
        fclose($fp);
        chmod($this->dirpaths['unzippeddir'] . '/' . $this->text_file_name, 0777);
    }


    public function custom_icon_upload_find_file($path, $type)
    {
        if (!is_dir($path)) {
            die(PrestaHelper::__('Same Name Font Already Install DIR', 'custom-icon-upload'));
        }
        $files = scandir($path);
        foreach ($files as $file) {
            if (strpos(strtolower($file), $type) !== false && $file[0] != '.') {
                return $file;
            }
        }
    }

    public function custom_icon_upload_remove_dir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->custom_icon_upload_remove_dir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            chmod($dir, 0777);
            rmdir($dir);
        }
    }

    public function custom_icon_upload_return_with_title($fonts)
    {
        if (empty($fonts)) {
            return false;
        }
        $icon = array();
        foreach ($fonts as $font) {
            if (file_exists($font['maindir'] . $font['filename'])) {
            
                $handle = fopen($font['maindir'] . $font['filename'], "r");
                $contents = fread($handle, filesize($font['maindir'] . $font['filename']));
                $textfiledecode = json_decode($contents);
                foreach ($textfiledecode as $key => $con) {
                    $icon[$font['fontname']][$key] = $con;
                }
                fclose($handle);
            }
        }
        return $icon;
    }

}