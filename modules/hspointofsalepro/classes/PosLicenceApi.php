<?php
/**
 * Get license for PrestaShop
 *
 * @author Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */

/**
 * Work with API of licence server
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class PosLicenceApi
{
    /**
     * Url to other server.
     * @var string
     */
    public $url = 'https://rockpos.com/module/hsaddonslicense/manage';

    /**
     * Key access to module license in server.
     * @var string
     */
    protected $api_key='f245f335aff5c098b2f6b95c7254b882';

    /**
     * Entity data of module.
     * @var Object
     */
    protected $module;

    /**
     * Construct
     * @param string $api_key
     * @param Module $module
     */
    public function __construct($module)
    {
        if (Validate::isLoadedObject($module)) {
            $this->module = $module;
        }
    }

    /**
     * Use curl push and get information from other server.
     * @param LicenceParameter $params
     * @return string format json
     */
    protected function requestCurl($params)
    {
        $params['api_key'] = $this->api_key;
        $str_params = http_build_query($params);
        $url = $this->url.'?'.$str_params;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('AUTH-KEY: '.$this->api_key));
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    /**
     * Use file_get_contents push and get information from other server.
     * @param LicenceParameter $params
     * @return string format json
     */
    protected function requestFileGetContent($params)
    {
        $params['api_key'] = $this->api_key;
        $str_params = http_build_query($params);
        $url = $this->url.'?'.$str_params;
        $result = Tools::file_get_contents($url);
        return $result;
    }

    /**
     * Call function be long to format_reqest
     * @param LicenceParameter $params
     * @return array
     *  ['success'] => boolean
     *  ['data'] => Array (
     *              .......
     *            )
     *
     */
    public function request($params, $license_keys)
    {
        if (empty($params)) {
            return false;
        }
        $current_time = strtotime('now');
        $next_time = $params['time_life'];
        $cache = $params['cache'];
        $success = array();
        if ($this->checkExistKey(array_values($license_keys)) && $current_time <= $next_time && !empty($cache)) {
            /* Get cache*/
            $cache_data = json_decode($cache, true);
            if (!empty($cache_data) && $cache_data['is_validated'] && !$cache_data['is_refund']) {
                return true;
            } else {
                foreach (array_values($license_keys) as $name) {
                    Configuration::deleteByName($name);
                }
                return false;
            }
        } else {
            $shop = Context::getContext()->shop;
            $params['action'] = 'validate';
            $params['domain'] = $shop->domain;
            $result = $this->getValueRequest($params);
            $data = json_decode($result, true);
            if (!empty($data)) {
                if ($data['is_validated'] && !$data['is_refund']) {
                    $success[] = Configuration::updateValue($license_keys['time_life'], strtotime('+1 week'));
                    $success[] = Configuration::updateValue($license_keys['cache'], $result);
                } else {
                    foreach (array_values($license_keys) as $name) {
                        $success[] = Configuration::deleteByName($name);
                    }
                }
            }
        }
        return array_sum($success) >= count($success);
    }
    
    /**
     * Check key exist in configuration.
     * @param array $key_configurations
     * @return bool
     */
    protected function checkExistKey($key_configurations)
    {
        $success = array();
        if (!empty($key_configurations)) {
            foreach ($key_configurations as $key) {
                $success[] = Configuration::hasKey($key);
            }
        }
        return array_sum($success) >= count($success);
    }

    /**
     * Get value after send information.
     * @param LicenceParameter $params
     * @return string format json
     */
    protected function getValueRequest($params)
    {
        $result = array();
        if (function_exists('curl_version') && ini_get('allow_url_fopen')) {
            $result = $this->requestCurl($params);
        } elseif (Tools::file_get_contents(__FILE__) && ini_get('allow_url_fopen')) {
            $result = $this->requestFileGetContent($params);
        }
        return (!empty($result)) ? $result : json_encode($result);
    }
    
    /**
     * Get time exist cache
     * @param string $key_configuration
     * @return float
     */
    protected function getCacheTime($key_configuration)
    {
        $next_time = 0;
        if ($this->checkExistKey($key_configuration)) {
            $data = json_decode(Configuration::get($key_configuration), true);
            if (is_array($data) && !empty($data)) {
                $next_time = $data['time'];
            }
        }
        return $next_time;
    }
}
