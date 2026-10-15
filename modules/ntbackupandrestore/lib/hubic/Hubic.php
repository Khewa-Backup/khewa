<?php
/**
* 2013-2023 2N Technologies
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to contact@2n-tech.com so we can send you a copy immediately.
*
* @author    2N Technologies <contact@2n-tech.com>
* @copyright 2013-2023 2N Technologies
* @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
require_once 'hubic-config.php';

class HubicLib
{
    const PAGE = 'hubic';

    /**
     * @var string the base URL for API requests
     */
    const API_URL = 'https://api.hubic.com/1.0/';

    /**
     * @var string the base URL for authorization requests
     */
    const AUTH_URL = 'https://api.hubic.com/oauth/auth';

    /**
     * @var string the base URL for token requests
     */
    const TOKEN_URL = 'https://api.hubic.com/oauth/token';

    /**
     * @var int The maximal size of a file to upload
     */
    const MAX_FILE_UPLOAD_SIZE = 10485760; // 10Mo (10 * 1024 * 1024 = 10 485 760)

    // The current token
    private $token;
    // The client ID
    private $client_id;
    // The client secret
    private $client_secret;
    // The redirect URI
    private $redirect_uri;
    // The sdk uri
    private $sdk_uri;
    // The physic sdk uri
    private $physic_sdk_uri;
    // Instance of NtbrCore
    private $ntbr;
    // The credential token
    private $credential_token;
    // The credential end point
    private $credential_end_point;

    public function __construct($ntbr, $sdk_uri, $physic_sdk_uri, $token = '')
    {
        $this->client_id = HUBIC_CLIENT_ID;
        $this->client_secret = HUBIC_CLIENT_SECRET;
        $this->redirect_uri = HUBIC_CALLBACK_URI;
        $this->sdk_uri = $sdk_uri;
        $this->physic_sdk_uri = $physic_sdk_uri;
        $this->ntbr = $ntbr;

        if (!empty($token)) {
            $this->token = $token;
        }
    }

    public function setCredential($token, $endpoint)
    {
        $this->credential_token = $token;
        $this->credential_end_point = $endpoint;
    }

    /**
     * Create a curl with default options and any other given options
     *
     * @param array $curl_more_options Further curl options to set. Default array().
     *
     * @return resource The curl
     */
    private function createCurl($curl_more_options = [])
    {
        if (!empty($this->token)) {
            $curl_header[] = 'Authorization: Bearer ' . $this->token;
        } else {
            $curl_header[] = 'Authorization: Basic ' . base64_encode($this->client_id . ':' . $this->client_secret);
        }

        $curl_default_options = [
            // Default option (http://php.net/manual/fr/function.curl-setopt.php)
            CURLOPT_HTTPHEADER => $curl_header,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CAINFO => $this->physic_sdk_uri . '../cacert.pem',
        ];

        $curl = curl_init();

        curl_setopt_array($curl, $curl_default_options);

        if (count($curl_more_options)) {
            curl_setopt_array($curl, $curl_more_options);
        }

        return $curl;
    }

    /**
     * Execute a curl and return it's result
     *
     * @param resource $curl the curl to execute
     *
     * @return array the result of the execution of the curl
     */
    private function execCurl($curl)
    {
        return $this->ntbr->execCurl($curl);
    }

    /**
     * Gets the URL of the log in form. After login, the browser is redirected to
     * the redirect URL, and a code is passed as a GET parameter to this URL.
     *
     * The browser is also redirected to this URL if the user is already logged
     * in.
     *
     * @return string|bool the login URL or false
     */
    public function getLogInUrl()
    {
        if (null === $this->client_id) {
            $this->log('The client ID must be set to call getLoginUrl()');

            return false;
        }

        $scopes = 'usage.r,credentials.r';

        // When using this URL, the browser will eventually be redirected to the
        // callback URL with a code passed in the URL query string (the name of the
        // variable is "code"). This is suitable for PHP.
        $url = self::AUTH_URL
            . '?client_id=' . urlencode($this->client_id)
            . '&redirect_uri=' . urlencode($this->redirect_uri)
            . '&response_type=code'
            . '&scope=' . urlencode($scopes)
            . '&state=hubic';

        return $url;
    }

    /**
     * Performs a call to the hubiC API using the GET method.
     *
     * @param string $path the path of the API call
     *
     * @return array the response of the execution of the curl
     */
    public function apiGet($path)
    {
        $url = self::API_URL . $path;

        $curl = $this->createCurl();

        curl_setopt($curl, CURLOPT_URL, $url);

        return $this->execCurl($curl);
    }

    /**
     * Performs a call to the hubiC API using the POST method.
     *
     * @param string $url the url of the API call
     * @param array $data the data to pass in the body of the request
     *
     * @return array the result of the execution of the curl
     */
    public function apiPost($url, $data = [], $curl_header = [])
    {
        $curl = $this->createCurl();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $curl_header,
            CURLOPT_POSTFIELDS => $data,
            CURLINFO_HEADER_OUT => true,
        ];

        curl_setopt_array($curl, $options);

        return $this->execCurl($curl);
    }

    /**
     * Gets the access token
     *
     * @return array|bool the access token or false
     */
    public function getToken($code)
    {
        $datas = [
            'code' => $code,
            'redirect_uri' => urlencode($this->redirect_uri),
            'grant_type' => 'authorization_code',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
        ];

        $result = $this->apiPost(self::TOKEN_URL, $datas);

        if ($result['success'] && !empty($result['result'])) {
            $result['result']['created'] = time();
            $this->token = $result['result']['access_token'];
        } else {
            return false;
        }

        return $result['result'];
    }

    /**
     * Fetches a fresh access token with the given refresh token.
     *
     * @param string $refreshToken
     *
     * @return string|bool the refresh access token or false
     */
    public function getRefreshToken($refreshToken)
    {
        $datas = [
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
        ];

        $result = $this->apiPost(self::TOKEN_URL, $datas);

        if ($result['success'] && !empty($result['result']) && isset($result['result']['access_token'])) {
            $this->token = $result['result']['access_token'];
            $result['result']['created'] = time();
        } else {
            return false;
        }

        return $result['result'];
    }

    /**
     * Fetches the quota of the current hubiC account.
     *
     * @return int Available quota
     */
    public function fetchQuota()
    {
        $result = $this->apiGet('account/usage');
        $quota_used = 0;
        $quota_total = 0;

        if ($result['success'] && !empty($result['result'])) {
            if (isset($result['result']['used'])) {
                $quota_used = $result['result']['used'];
            }
            if (isset($result['result']['quota'])) {
                $quota_total = $result['result']['quota'];
            }
        }

        $quota_available = $quota_total - $quota_used;

        $this->log(
            sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::HUBIC)
            . ' ' . $this->ntbr->l('Available quota:', self::PAGE) . ' ' . $quota_available . '/' . $quota_total
        );

        return $quota_available;
    }

    /**
     * Test the connection to the account
     *
     * @return bool Connection result
     */
    public function testConnection()
    {
        $result = $this->apiGet('account/usage');

        return $result['success'];
    }

    /**
     * Get credential to manage files
     *
     * @return string Credential
     */
    public function getCredential()
    {
        $result = $this->apiGet('account/credentials');

        if ($result['success'] && !empty($result['result'])) {
            if (isset($result['result']['endpoint'], $result['result']['token'])) {
                $this->credential_end_point = $result['result']['endpoint'];
                $this->credential_token = $result['result']['token'];

                return $result['result'];
            }
        }

        return false;
    }

    /**
     * Update max quota in folder
     *
     * @param int $quota the new quota of the folder on the account
     * @param string $directory the path of the folder on the account
     *
     * @return bool the success or failure of the action
     */
    public function updateQuotaFolder($quota, $directory = '')
    {
        $openstack = $this->ntbr->connectToOpenstack($this->credential_token, $this->credential_end_point, NtbrChild::HUBIC);

        return $openstack->updateQuotaFolder($quota, $directory);
    }

    /**
     * List directories
     *
     * @param string $directory the path of the directory
     *
     * @return array the list of directories
     */
    public function listDirectories($directory = '')
    {
        $openstack = $this->ntbr->connectToOpenstack($this->credential_token, $this->credential_end_point, NtbrChild::HUBIC);

        return $openstack->listDirectories($directory);
    }

    /**
     * List files
     *
     * @param string $directory the path of the directory with the files
     *
     * @return array the list of files
     */
    public function listFiles($directory = '')
    {
        $openstack = $this->ntbr->connectToOpenstack($this->credential_token, $this->credential_end_point, NtbrChild::HUBIC);

        return $openstack->listFiles($directory);
    }

    /**
     * Create a folder in the hubiC account
     *
     * @param string $folder_path the path of the folder on the hubiC account
     *
     * @return bool the success or failure of the action
     */
    public function createFolder($folder_path)
    {
        $openstack = $this->ntbr->connectToOpenstack($this->credential_token, $this->credential_end_point, NtbrChild::HUBIC);

        return $openstack->createFolder($folder_path);
    }

    /**
     * Create a file in the account
     *
     * @param string $file_path the file to send
     * @param string $destination the path of the folder on the account
     * @param int $nb_part current part number
     * @param int $nb_part_total total parts to be sent
     *
     * @return bool the success or failure of the action
     */
    public function createFile($file_path, $destination = '', $nb_part = 1, $nb_part_total = 1)
    {
        $openstack = $this->ntbr->connectToOpenstack($this->credential_token, $this->credential_end_point, NtbrChild::HUBIC);

        return $openstack->createFile($file_path, $destination, $nb_part, $nb_part_total);
    }

    /**
     * Resume the creating of the file in the account
     *
     * @param string $file_path the file to send
     * @param string $destination the path of the folder on the account
     * @param int $nb_part current part number
     * @param int $nb_part_total total parts to be sent
     * @param int $position position in the file
     * @param int $nb_chunk chunk to send for this part/file
     *
     * @return bool the success or failure of the action
     */
    public function resumeCreateFile($file_path, $destination = '', $nb_part = 1, $nb_part_total = 1, $position = 0, $nb_chunk = 1)
    {
        $openstack = $this->ntbr->connectToOpenstack($this->credential_token, $this->credential_end_point, NtbrChild::HUBIC);

        return $openstack->resumeCreateFile($file_path, $destination, $nb_part, $nb_part_total, $position, $nb_chunk);
    }

    /**
     * Upload content on the account
     *
     * @param string $content the content to upload
     * @param int $content_size the size of the content to upload
     * @param int $total_file_size The total size of the file the content is part of
     * @param string $destination the path of the folder on the account
     * @param int $position position in the file
     * @param int $nb_chunk chunk to send for this part/file
     *
     * @return bool the success or failure of the action
     */
    public function uploadContent($content, $content_size, $total_file_size, $destination, $position = 0, $nb_chunk = 1)
    {
        $openstack = $this->ntbr->connectToOpenstack($this->credential_token, $this->credential_end_point, NtbrChild::HUBIC);

        return $openstack->uploadContent($content, $content_size, $total_file_size, $destination, $position, $nb_chunk);
    }

    /**
     * Delete a file in the account
     *
     * @param string $file_path the path of the file on the account
     *
     * @return bool the success or failure of the action
     */
    public function deleteFile($file_path)
    {
        $openstack = $this->ntbr->connectToOpenstack($this->credential_token, $this->credential_end_point, NtbrChild::HUBIC);

        return $openstack->deleteFile($file_path);
    }

    /**
     * Check if a file or folder exists in the account
     *
     * @param string $file_path the path of the file on the account
     *
     * @return bool if the item exists
     */
    public function checkExists($file_path)
    {
        $openstack = $this->ntbr->connectToOpenstack($this->credential_token, $this->credential_end_point, NtbrChild::HUBIC);

        return $openstack->checkExists($file_path);
    }

    /**
     * Log()
     *
     * Log message to file
     *
     * @param string $message Message to log
     *
     * @return void
     */
    public function log($message, $not_display_in_module = false)
    {
        $this->ntbr->log($message, $not_display_in_module);
    }
}
