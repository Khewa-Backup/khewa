<?php
/**
 * 2007-2020 ETS-Soft
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
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
	exit;
class Ets_superspeed_compressor_image
{
    protected static $instance;
    public $_resmush = 0;
    public $_google =0;
    public $_errors = array();
    public function __construct()
	{
        $this->name= 'ets_superspeed';
	    $this->context = Context::getContext(); 
    }
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Ets_superspeed_compressor_image();
        }
        return self::$instance;
    }
    public function compress($path, $type, $quality, $url_image = null, $quality_old = 0,$is_product=false)
    {
        if (Tools::isSubmit('btnSubmitImageOptimize') || Tools::isSubmit('btnSubmitImageAllOptimize') || Tools::isSubmit('btnSubmitPageCacheDashboard')) {
            $script_optimize = Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT');
            $image = 'old';
        } elseif (Tools::isSubmit('submitUploadImageCompress')) {
            $script_optimize = Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD');
            $image = 'upload';
        } elseif (Tools::isSubmit('submitBrowseImageOptimize')) {
            $script_optimize = Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE');
            $image = 'browse';
        } else {
            $script_optimize = Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT_NEW');
            $image = 'new';
        }
        if (!is_array($type)) {
            $name = $type;
            $source = $path . $name;
            $destination = $path . $name;
            $temp = $path . 'temp-' . $name;
        } else {
            $name = Tools::stripslashes($type['name']);
            $source = $path . '-' . $name . '.jpg';
            $destination = $path . '-' . $name . '.jpg';
            $temp = $path . '-' . $name . '-temp.jpg';
        }
        $file_size_old = Tools::ps_round(@filesize($source) / 1024, 2);
        if ($quality >= 100) {
            if (Configuration::get('ETS_SPEEP_RESUMSH') != 2)
                Configuration::updateValue('ETS_SPEEP_RESUMSH', 2);
            if($is_product)
            {
                $destination_webp = str_replace('.jpg','.webp',$destination);
                if(file_exists($destination_webp))
                    @unlink($destination_webp);
            }
            return array(
                'file_size' => $file_size_old,
                'optimize_type' => $script_optimize ? $script_optimize : 'google',
            );
        }
        $continue= (int)Tools::getValue('continue');
        if (self::checkOptimizeImageResmush($script_optimize) && $url_image && $quality < 100 && $this->_resmush < 10 && !$continue) {
            $this->_errors = array();
            if ($file_size = $this->compressByReSmush($url_image, $quality, $temp, $destination, $file_size_old,$is_product))
            {
                if(file_exists($path.'fileType'))
                        @unlink($path.'fileType');
                return $file_size;
            }
            else {
                $this->_resmush++;
                return false;
            }
        }
        
        if ($script_optimize == 'tynypng' && !$continue) {
            $tynypng_api_keys = explode(';', Configuration::get('ETS_SPEED_API_TYNY_KEY'));
            if (Configuration::get('ETS_SP_ERRORS_TINYPNG'))
                $errors_api = Tools::jsonDecode(Configuration::get('ETS_SP_ERRORS_TINYPNG'), true);
            else
                $errors_api = array();
            if ($tynypng_api_keys) {
                foreach ($tynypng_api_keys as $api_key) {
                    if (!isset($errors_api[$api_key]) || (isset($errors_api[$api_key]) && $errors_api[$api_key] <= 5)) {
                        $this->_errors = array();
                        if ($file_size = $this->compressByTyNyPNG($source, $api_key,$is_product)) {
                            if (isset($errors_api[$api_key]) && $errors_api[$api_key] != 1) {
                                $errors_api[$api_key] = 1;
                                Configuration::updateValue('ETS_SP_ERRORS_TINYPNG', Tools::jsonEncode($errors_api));
                            }
                            return $file_size;
                        } else {
                            if (isset($errors_api[$api_key]))
                                $errors_api[$api_key]++;
                            else
                                $errors_api[$api_key] = 1;
                            Configuration::updateValue('ETS_SP_ERRORS_TINYPNG', Tools::jsonEncode($errors_api));
                            return false;
                        }

                    }
                }
            }
        }
        $continue_webp = (int)Tools::getValue('continue_webp');
        if(($script_optimize=='google' || $continue_webp) && $this->_google <=5 && !$continue)
        {
            $this->_errors=array();   
            $mime_type= Tools::strtolower(mime_content_type($source)); 
            if($mime_type=='image/jpeg' || $mime_type=='image/png' ) //|| $mime_type =='image/gif'         
                $optimized = $this->compressByGoogleScript($source,$destination,$temp,$quality,$is_product);
            else
                return array(
                    'file_size' => $file_size_old,
                    'optimize_type' => 'google',
                );
            if($optimized)
            {
                $this->_google = 0;
                return $optimized;
            }
            else
            {
                $this->_google++; 
                return false;
            }
        }
        if ($script_optimize != 'php' && (!$continue || $continue_webp) && ($image == 'old' || $image == 'upload' || $image == 'browse')) {
            $mime_type = Tools::strtolower(mime_content_type($source));
            if ($image == 'upload')
                @unlink($source);
            if (($script_optimize == 'google' || $continue_webp) && ($mime_type == 'image/jpeg' || $mime_type == 'image/png')) {
                die(
                Tools::jsonEncode(
                    array(
                        'error' => $this->displayGoogleError($this->_errors, true),
                        'script_continue' => 'php',
                    )
                )
                );
            }

            die(
            Tools::jsonEncode(
                array(
                    'error' => $this->_errors ? $this->displayError($this->_errors, true) : $this->displayError($this->l('errors'), true),
                    'script_continue' => 'php',
                )
            )
            );
        }
        return $this->compressByPhp($path, $name, $source, $destination, $temp, $quality, $type, $file_size_old, $quality_old,$is_product);
    }
    public static function checkOptimizeImageResmush($script_optimize='')
    {
        if(!$script_optimize)
            $script_optimize = Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT');
        $whitelist = array(
            '127.0.0.1',
            '::1'
        );
        if (!in_array(Tools::getRemoteAddr(), $whitelist) && $script_optimize == 'resmush') {
            return true;
        }
        return false;
    }
    public function compressByReSmush($url_image, $quality, $temp, $destination, $file_size_old,$is_product = false)
    {
        $optimized_jpg_arr = Tools::jsonDecode(Tools::file_get_contents('http://api.resmush.it/ws.php?img=' . $url_image . ($quality < 80 ? '&qlty=' . (int)$quality : '')), true);
        if (isset($optimized_jpg_arr['dest'])) {
            $optimized_jpg_url = $optimized_jpg_arr['dest'];
            if (Configuration::get('ETS_SPEEP_RESUMSH') != 1)
                Configuration::updateValue('ETS_SPEEP_RESUMSH', 1);
            file_put_contents($temp, Tools::file_get_contents($optimized_jpg_url));
            $file_size = Tools::ps_round(@filesize($temp) / 1024, 2);
            if ($file_size > 0) {
                Tools::copy($temp, $destination);
                @unlink($temp);
                if($is_product)
                {
                    $destination_webp = str_replace('.jpg','.webp',$destination);
                    if(file_exists($destination_webp))
                        @unlink($destination_webp);
                }
                if ($file_size < $file_size_old)
                    return array(
                        'file_size' => $file_size,
                        'optimize_type' => 'resmush',
                    );
                else
                    return array(
                        'file_size' => $file_size_old,
                        'optimize_type' => 'resmush',
                    );
            } else {
                @unlink($temp);
                $this->_errors[] = $this->l('Resmush failed to create image');
                return false;
            }
        }
        $this->_errors[] = $this->l('Resmush failed to create image');
        return false;
    }
    public function compressByTyNyPNG($source, $api_key,$is_product = false)
    {
        $curl = curl_init();
        $curlOptions = array(
            CURLOPT_BINARYTRANSFER => 1,
            CURLOPT_HEADER => 1,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://api.tinypng.com/shrink',
            CURLOPT_USERAGENT => 'TinyPNG PHP v1',
            CURLOPT_USERPWD => 'api:' . $api_key,
        );
        curl_setopt_array($curl, $curlOptions);
        curl_setopt($curl, CURLOPT_POSTFIELDS, Tools::file_get_contents($source));
       
        $response = curl_exec($curl);
        $content = Tools::jsonDecode(Tools::substr($response, curl_getinfo($curl, CURLINFO_HEADER_SIZE)), true);
        if (isset($content['output']['url']) && $content['output']['url']) {
            if ($content['output']['size'] > 0) {
                if (file_put_contents($source, Tools::file_get_contents($content['output']['url'])) !== false) {
                    if($is_product)
                    {
                        $destination_webp = str_replace('.jpg','.webp',$source);
                        if(file_exists($destination_webp))
                            @unlink($destination_webp);
                    }
                    return array(
                        'file_size' => Tools::ps_round($content['output']['size'] / 1024, 2),
                        'optimize_type' => 'tynypng',
                    );
                }
            }
        }
        $this->_errors[] = $this->l('TinyPNG is not working. Your API key(s) is invalid or you may have reached API limit.');
        return false;
    }
    public function compressByGoogleScript($source,$destination,$temp,$quality,$is_product = false)
    {
        require dirname(__FILE__).'/classes/rosell-dk/vendor/autoload.php';
        $options = array(
            'converters' => array('cwebp','vips','imagick','gmagick','imagemagick','graphicsmagick','gd'),
            'alpha-quality' => (int)$quality
        );
        $optimize =  WebPConvert\WebPConvert::convert($source, $temp, $options,null);
        if($optimize && $file_size= Tools::ps_round(@filesize($temp)/1024,2))
        {
            if(Tools::isSubmit('btnSubmitPageCacheDashboard') || Tools::isSubmit('btnSubmitImageOptimize') || Tools::isSubmit('btnSubmitImageAllOptimize'))
                $is_webp = Configuration::get('ETS_SPEED_ENABLE_WEBP_FORMAT');
            else
                $is_webp = Configuration::get('ETS_SPEED_ENABLE_WEBP_FORMAT_NEW');
            $destination_webp = str_replace('.jpg','.webp',$destination);
            if($is_webp && $is_product)
                Tools::copy($temp,$destination_webp);
            else
            {
                Tools::copy($temp,$destination);
                if($is_product && file_exists($destination_webp))
                    @unlink($destination_webp);
            }
            @unlink($temp);
            return array(
                'file_size' => $file_size,
                'optimize_type' => Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') ? Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') : 'php',
            );
        }
        else
            return false;
    }
    public function compressByPhp($path, $name, $source, $destination, $temp, $quality, $type, $file_size_old, $quality_old,$is_product = false)
    {
        if ($this->png_has_transparency($source))
            return array(
                'file_size' => $file_size_old,
                'optimize_type' => Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') ? Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') : 'php',
            );

        ini_set('gd.jpeg_ignore_warning', 1);
        $temp2 = $path . 'temp2-' . $name;
        Tools::copy($source, $temp2);
        $image = @getimagesize($source);
        $default = false;
        if ($quality >= 100 || ($quality <= 80 && is_array($type) && isset($type['width']) && $type['width'] <= 260) || ($name == Configuration::get('PS_LOGO') && $quality <= 80)) {
            if($is_product)
            {
                $destination_webp = str_replace('.jpg','.webp',$destination);
                if(file_exists($destination_webp))
                    @unlink($destination_webp);
            }
            if ($quality_old <= 80)
                return array(
                    'file_size' => $file_size_old,
                    'optimize_type' => Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') ? Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') : 'php',
                );
            $default = true;
        }
        if ($image) {
            ini_set('gd.jpeg_ignore_warning', 1);
            $widthImage = $image[0];
            $heightImage = $image[1];

            $imageCanves = imagecreatetruecolor($widthImage, $heightImage);
            switch (Tools::strtolower($image['mime'])) {
                case 'image/jpeg':
                    $NewImage = imagecreatefromjpeg($source);
                    break;
                case 'image/JPEG':
                    $NewImage = imagecreatefromjpeg($source);
                    break;
                case 'image/png':
                    $NewImage = imagecreatefrompng($source);
                    break;
                case 'image/PNG':
                    $NewImage = imagecreatefrompng($source);
                    break;
                case 'image/gif':
                    $NewImage = imagecreatefromgif($source);
                    break;
                default:
                    return array(
                        'file_size' => $file_size_old,
                        'optimize_type' => Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') ? Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') : 'php',
                    );
            }

            $white = imagecolorallocate($imageCanves, 255, 255, 255);
            imagefill($imageCanves, 0, 0, $white);
            // Resize Image
            if (imagecopyresampled($imageCanves, $NewImage, 0, 0, 0, 0, $widthImage, $heightImage, $widthImage, $heightImage)) {
                // copy file
                if (imagejpeg($imageCanves, $destination, $default ? 80 : $quality)) {
                    imagedestroy($imageCanves);
                    if (Tools::copy($destination, $temp)) {
                        $file_size = Tools::ps_round(@filesize($temp) / 1024, 2);
                        if ($file_size > $file_size_old) {
                            Tools::copy($temp2, $destination);
                            $file_size = $file_size_old;
                        }
                        @unlink($temp);
                        @unlink($temp2);
                        if(file_exists($path.'fileType'))
                            @unlink($path.'fileType');
                        if($is_product)
                        {
                            $destination_webp = str_replace('.jpg','.webp',$destination);
                            if(file_exists($destination_webp))
                                @unlink($destination_webp);
                        }
                        return array(
                            'file_size' => $file_size,
                            'optimize_type' => Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') ? Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') : 'php',
                        );
                    }
                }
            }
        }
        @unlink($temp2);
        if(file_exists($path.'fileType'))
            @unlink($path.'fileType');
        return array(
            'file_size' => $file_size_old,
            'optimize_type' => Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') ? Configuration::get('ETS_SPEED_OPTIMIZE_SCRIPT') : 'php',
        );
    }
    public function png_has_transparency($filename)
    {
        if (Tools::strlen($filename) == 0 || !file_exists($filename))
            return false;
        if (ord(call_user_func('file_get_contents', $filename, false, null, 25, 1)) & 4) {
            return true;
        }
        $contents = Tools::file_get_contents($filename);
        if (stripos($contents, 'PLTE') !== false && stripos($contents, 'tRNS') !== false)
            return true;

        return false;
    }
    public function displayError($errors, $popup = false)
    {
        $this->context->smarty->assign(
            array(
                'errors' => $errors,
                'popup' => $popup
            )
        );
        return $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/hook/error.tpl');
    }
    public function displayGoogleError()
    {
        return $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/hook/google.tpl');
    }
    public function l($string)
    {
        return Translate::getModuleTranslation('ets_superspeed', $string, pathinfo(__FILE__, PATHINFO_FILENAME));
    }
}