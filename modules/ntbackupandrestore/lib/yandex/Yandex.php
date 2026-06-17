<?php
/**
 * 2013-2024 2N Technologies
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
 * @copyright 2013-2024 2N Technologies
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
require_once 'yandex-config.php';

class YandexLib
{
    const PAGE = 'yandex';
    /**
     * @var string the base URL for authorization requests
     */
    const AUTH_URL = 'https://oauth.yandex.com/authorize';

    /**
     * @var string the base URL for token requests
     */
    const TOKEN_URL = 'https://oauth.yandex.com/token';

    /**
     * @var string the base URL for API requests
     */
    const API_URL = 'https://cloud-api.yandex.net/v1/disk/';

    /**
     * @var string the base URL for files requests
     */
    const FILES_URL = 'https://cloud-api.yandex.net/v1/disk/resources';

    /**
     * @var int The maximal size of a file to upload
     */
    const MAX_FILE_UPLOAD_SIZE = 1048576; // 1Mo (1 * 1024 * 1024 = 1 048 576)

    /**
     * @var int The maximal size of the log file
     */
    const MAX_FILE_LOG_SIZE = 200000000; // environs 200Mo (200 000 000)

    /**
     * @var string The name of the current log file
     */
    const CURRENT_FILE_LOG_NAME = 'current_log_yandex.txt';

    /**
     * @var string The name of old log file
     */
    const OLD_FILE_LOG_NAME = '_log_yandex.txt';

    /**
     * @var int The number max of characters for the backup name
     */
    const NAME_MAX_CHAR = 255;

    /**
     * @var int The number max of characters for the backup path
     */
    const PATH_MAX_CHAR = 32760;

    /**
     * @var int The number max of characters for the backup path
     */
    const FILE_MAX_SIZE = 10737418240; // 10Go (10 * 1024 * 1024 * 1024 = 10 737 418 240)

    // The current token
    private $token;
    // The app key
    private $app_id;
    // The app secret
    private $app_pwd;
    // The sdk uri
    private $sdk_uri;
    // The physic sdk uri
    private $physic_sdk_uri;
    // Instance of NtbrCore
    private $ntbr;

    public function __construct($ntbr, $sdk_uri, $physic_sdk_uri, $token = '')
    {
        $this->app_id = YANDEX_APP_ID;
        $this->app_pwd = YANDEX_APP_PWD;
        $this->sdk_uri = $sdk_uri;
        $this->physic_sdk_uri = $physic_sdk_uri;
        $this->ntbr = $ntbr;

        if (!empty($token)) {
            $this->token = $token;
        }
    }

    /**
     * Create a curl with default options and any other given options
     *
     * @param array $curl_more_options Further curl options to set. Default array().
     *
     * @return resource The curl
     */
    private function createCurl($curl_more_options = [], $curl_header = [])
    {
        if (!empty($this->token)) {
            $curl_header[] = 'Authorization: OAuth ' . $this->token;
        } else {
            $clientCredentials = $this->app_id . ':' . $this->app_pwd;
            $authHeaderValue = 'Basic ' . base64_encode($clientCredentials);

            $curl_header[] = 'Authorization: ' . $authHeaderValue;
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
     * Performs a call to the Yandex API using the POST method.
     *
     * @param string $url the url of the API call
     * @param array|object $data the data to pass in the body of the request
     *
     * @return array the result of the execution of the curl
     */
    public function apiPost($url, $data = [], $header = [])
    {
        $curl = $this->createCurl([], $header);

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
        ];

        curl_setopt_array($curl, $options);

        return $this->execCurl($curl);
    }

    /**
     * Performs a call to the Yandex API using the GET method.
     *
     * @param string $url the url of the API call
     *
     * @return array the response of the execution of the curl
     */
    public function apiGet($url)
    {
        $curl = $this->createCurl();

        curl_setopt($curl, CURLOPT_URL, $url);

        return $this->execCurl($curl);
    }

    /**
     * Performs a call to the Yandex API using the PUT method to send file data.
     *
     * @param string $url the path of the API call
     * @param string $stream the data to upload
     * @param array $header the data to pass in the header of the request
     * @param float $filesize the size of the stream
     *
     * @return array the result of the execution of the curl
     */
    public function apiPutData($url, $stream, $header = [], $filesize = 0)
    {
        // $header[] = 'Content-Type: application/octet-stream';
        $curl = $this->createCurl([], $header);

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_INFILESIZE => $filesize,
            CURLOPT_POSTFIELDS => $stream,
            CURLOPT_HEADER => true,
        ];

        curl_setopt_array($curl, $options);

        return $this->execCurl($curl);
    }

    /**
     * Performs a call to the Yandex API using the PUT method not to send file data.
     *
     * @param string $url the path of the API call
     * @param array $params parameters of the API call
     *
     * @return array the result of the execution of the curl
     */
    public function apiPut($url, $params = [])
    {
        $curl = $this->createCurl();

        $list_params = '';

        if (is_array($params) && count($params)) {
            ksort($params);

            foreach ($params as $p_name => $p_value) {
                if ($list_params != '') {
                    $list_params .= '&';
                } else {
                    $list_params = '?';
                }

                $list_params .= rawurlencode((string) $p_name) . '=' . rawurlencode((string) $p_value);
            }
        }

        $url .= $list_params;

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'PUT',
        ];

        curl_setopt_array($curl, $options);

        return $this->execCurl($curl);
    }

    /**
     * Performs a call to the Yandex API using the DELETE method.
     *
     * @param string $url the path of the API call
     * @param array $params parameters of the API call
     *
     * @return bool the success or failure of the action
     */
    public function apiDelete($url, $params = [])
    {
        $curl = $this->createCurl();

        $list_params = '';

        if (is_array($params) && count($params)) {
            ksort($params);

            foreach ($params as $p_name => $p_value) {
                if ($list_params != '') {
                    $list_params .= '&';
                } else {
                    $list_params = '?';
                }

                $list_params .= rawurlencode((string) $p_name) . '=' . rawurlencode((string) $p_value);
            }
        }

        $url .= $list_params;

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ];

        curl_setopt_array($curl, $options);

        return $this->execCurl($curl);
    }

    /**
     * Gets the URL of the authorize in form.
     *
     * @return string|bool the login URL or false
     */
    public function getLogInUrl()
    {
        $url = self::AUTH_URL
            . '?client_id=' . $this->app_id
            . '&response_type=code';

        return $url;
    }

    /**
     * Gets the URL of the authorize in form.
     *
     * @return string|bool the login URL or false
     */
    public function getToken($code)
    {
        $datas = [
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];

        $result = $this->apiPost(self::TOKEN_URL, $datas);

        if ($result['success']) {
            return $result['result']['access_token'];
        }

        return false;
    }

    /**
     * Test the connection to the Yandex account
     *
     * @return bool Connection result
     */
    public function testConnection()
    {
        $result = $this->apiGet(self::API_URL);

        return $result['success'];
    }

    /**
     * Get the available quota of the current Yandex account.
     *
     * @return int Available quota
     */
    public function getAvailableQuota()
    {
        $quota_available = 0;
        $quota_total = 0;
        $result = $this->apiGet(self::API_URL);

        if ($result['success'] && !empty($result['result'])) {
            if (isset($result['result']['used_space'], $result['result']['total_space'])) {
                $quota_total = $result['result']['total_space']; // The user's total quota allocation (bytes).
                $quota_used = $result['result']['used_space']; // The user's used quota outside of shared folders (bytes).

                $quota_available = $quota_total - $quota_used;
            }
        }

        $this->log(
            sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::YANDEX)
            . ' ' . $this->ntbr->l('Available quota:', self::PAGE) . ' ' . $quota_available . '/' . $quota_total
        );

        return $quota_available;
    }

    /**
     * Check the validity of
     *
     * @param string $file_destination the destination of the file
     * @param int $total_file_size The total size of the file
     *
     * @return bool the success or failure of the action
     */
    public function checkUploadValidity($file_destination, $total_file_size)
    {
        // Check path size
        if ($this->ntbr->getLength($file_destination) >= self::PATH_MAX_CHAR) {
            $this->log(
                sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::YANDEX)
                . sprintf($this->ntbr->l('cannot upload a file with a path longer than %1$d characters (%2$s)', self::PAGE), self::PATH_MAX_CHAR, $file_destination)
            );

            return false;
        }
        // Check name size
        $name = basename($file_destination);

        if ($this->ntbr->getLength($name) >= self::NAME_MAX_CHAR) {
            $this->log(
                sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::YANDEX)
                . sprintf($this->ntbr->l('cannot upload a file with a name longer than %1$d characters (%2$s)', self::PAGE), self::NAME_MAX_CHAR, $name)
            );

            return false;
        }
        // Check file size
        if ($total_file_size >= self::FILE_MAX_SIZE) {
            $this->log(
                sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::YANDEX)
                . sprintf(
                    $this->ntbr->l('cannot upload a file exceeding %1$s (%2$s)', self::PAGE),
                    $this->ntbr->readableSize(self::FILE_MAX_SIZE),
                    $this->ntbr->readableSize($total_file_size)
                )
            );

            return false;
        }

        return true;
    }

    /**
     * Upload a file on the Yandex account
     *
     * @param string $file_path the path of the file
     * @param string $file_destination the destination of the file
     * @param int $nb_part current part number
     * @param int $nb_part_total total parts to be sent
     *
     * @return bool the success or failure of the action
     */
    public function uploadFile($file_path, $file_destination, $nb_part = 1, $nb_part_total = 1)
    {
        if (!$this->checkUploadValidity($file_destination, $this->ntbr->getFileSize($file_path))) {
            return false;
        }

        $res = $this->apiGet(self::FILES_URL . '/upload?path=' . rawurlencode((string) $file_destination));

        if ($res['success'] === false) {
            $this->log(
                sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::YANDEX)
                . ' ' . $nb_part . '/' . $nb_part_total . ' - ' . $this->ntbr->l('cannot create the upload url', self::PAGE)
            );

            return false;
        }

        $this->ntbr->yandex_position = 0;
        $this->ntbr->yandex_upload_url = $res['result']['href'];

        return $this->resumeUploadFile($file_path, $nb_part, $nb_part_total);
    }

    /**
     * Resume the upload of a file on the Yandex account
     *
     * @param string $file_path the path of the file
     * @param int $nb_part current part number
     * @param int $nb_part_total total parts to be sent
     *
     * @return bool the success or failure of the action
     */
    public function resumeUploadFile($file_path, $nb_part = 1, $nb_part_total = 1)
    {
        $result_upload = [
            'success' => true,
            'result' => '',
        ];

        $filesize = (float) $this->ntbr->getFileSize($file_path);
        $total_file_size = $filesize;
        $rest_to_upload = $total_file_size - $this->ntbr->yandex_position;
        $start_file_part = $this->ntbr->yandex_position;

        $file = fopen($file_path, 'r+');

        // Go to the last position in the file
        if ($this->ntbr->yandex_position > 0) {
            $file = $this->ntbr->goToPositionInFile($file, $this->ntbr->yandex_position, false);

            if ($file === false) {
                return false;
            }
        }

        if ($rest_to_upload > self::MAX_FILE_UPLOAD_SIZE) {
            $file_part_size = self::MAX_FILE_UPLOAD_SIZE;
            $end_file_part = $start_file_part + $file_part_size - 1; // Size minus 1 cause we start from 0 in the size parts.
        } else {
            $file_part_size = $rest_to_upload;
            $end_file_part = $start_file_part + $rest_to_upload - 1; // Size minus 1 cause we start from 0 in the size parts.
        }

        while ($rest_to_upload > 0) {
            $headers = [
                'Content-Length: ' . $file_part_size,
                'Content-Range: bytes ' . $start_file_part . '-' . $end_file_part . '/' . $total_file_size,
            ];

            $percent = ($end_file_part / $total_file_size) * 100;

            if ($nb_part_total > 1) {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::YANDEX)
                    . ' ' . $nb_part . '/' . $nb_part_total . $this->ntbr->l(':', self::PAGE) . ' ' . (int) $percent . '%'
                );
            } else {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::YANDEX)
                    . ' ' . (int) $percent . '%'
                );
            }

            $part_file = fread($file, $file_part_size);

            $result_upload = $this->apiPutData($this->ntbr->yandex_upload_url, $part_file, $headers, $file_part_size);

            if (!$result_upload['success'] || !$result_upload['result']) {
                fclose($file);

                return false;
            }

            $start_file_part = ($end_file_part + 1);
            $rest_to_upload -= $file_part_size;

            if ($rest_to_upload > self::MAX_FILE_UPLOAD_SIZE) {
                $file_part_size = self::MAX_FILE_UPLOAD_SIZE;
                $end_file_part = ($start_file_part + $file_part_size - 1);
            } else {
                $file_part_size = $rest_to_upload;
                $end_file_part = ($start_file_part + $rest_to_upload - 1);
            }

            $this->ntbr->yandex_position = $start_file_part;

            // refresh
            $this->ntbr->refreshBackup(true);
        }

        fclose($file);

        return $result_upload['success'];
    }

    /**
     * Upload content on the account
     *
     * @param string $file_destination the destination of the file
     * @param int $total_file_size The total size of the file the content is part of
     * @param bool $check_validity If the validity of the backup infos should be checked (default value: true)
     *
     * @return bool the success or failure of the action
     */
    public function startUploadContent($file_destination, $total_file_size, $check_validity = true)
    {
        if ($check_validity) {
            if (!$this->checkUploadValidity($file_destination, $total_file_size)) {
                return false;
            }
        }

        $res = $this->apiGet(self::FILES_URL . '/upload?path=' . rawurlencode((string) $file_destination));

        if ($res['success'] === false) {
            return false;
        }

        $this->ntbr->yandex_upload_url = $res['result']['href'];

        return true;
    }

    /**
     * Resume the upload of the content on the Yandex account
     *
     * @param string $content the content to upload
     * @param int $content_size the size of the content to upload
     * @param int $total_file_size The total size of the file the content is part of
     *
     * @return bool the success or failure of the action
     */
    public function resumeUploadContent($content, $content_size, $total_file_size)
    {
        $start_file_part = $this->ntbr->yandex_position;
        $end_file_part = $start_file_part + $content_size - 1; // Size minus 1 cause we start from 0 in the size parts.

        $headers = [
            'Content-Length: ' . $content_size,
            'Content-Range: bytes ' . $start_file_part . '-' . $end_file_part . '/' . $total_file_size,
        ];

        // $this->log($headers, true);

        $result_upload = $this->apiPutData($this->ntbr->yandex_upload_url, $content, $headers, $content_size);

        if ($result_upload['code_http'] == 498) {
            // The upload url is not valid anymore
            $this->startUploadContent($content, $total_file_size, false);

            // Try the upload again
            $result_upload = $this->apiPutData($this->ntbr->yandex_upload_url, $content, $headers, $content_size);

            // $this->log($result_upload, true);
        }

        if (!$result_upload['success']) {
            return false;
        }

        return true;
    }

    /**
     * Delete a file on the Yandex account
     *
     * @param string $file_path the path of the file on Yandex
     *
     * @return bool the success or failure of the action
     */
    public function deleteFile($file_path)
    {
        if ($file_path[0] != '/') {
            $file_path = '/' . $file_path;
        }

        $datas = [
            'path' => $file_path,
            'permanently' => true,
        ];

        $result = $this->apiDelete(self::FILES_URL, $datas);

        return $result['success'];
    }

    /**
     * Create a folder in the Yandex account
     *
     * @param string $folder_path the path of the folder on Yandex
     *
     * @return bool the success or failure of the action
     */
    public function createFolder($folder_path)
    {
        if ($folder_path[0] != '/') {
            $folder_path = '/' . $folder_path;
        }

        $datas = [
            'path' => $folder_path,
        ];

        $result = $this->apiPut(self::FILES_URL, $datas);

        return $result['success'];
    }

    /**
     * Check if a file or folder exists on the Yandex account
     *
     * @param string $item_path the path of the file or folder on Yandex
     *
     * @return bool if the item exists
     */
    public function checkExists($item_path = '')
    {
        $result = $this->apiGet(self::FILES_URL . '?path=' . rawurlencode((string) $item_path));

        if ($result['success'] === false) {
            return false;
        }

        // If the result does not return a valid answer
        if (!isset($result['result']['path'])) {
            return false;
        }

        return true;
    }

    /**
     * Get the children of a folder on the Yandex account
     *
     * @param string $item_path the path of the folder on Yandex
     *
     * @return bool|array the children of the folder or the failure of the action
     */
    public function listFolderChildren($item_path = '', $offset = 0)
    {
        if ($item_path[0] != '/') {
            $item_path = '/' . $item_path;
        }

        $result = $this->apiGet(self::FILES_URL . '?path=' . rawurlencode((string) $item_path) . '&offset=' . $offset);

        if ($result['success'] === false) {
            return false;
        }

        $list = [];

        if (isset($result['result']['_embedded'], $result['result']['_embedded']['items'], $result['result']['_embedded']['total'])) {
            $list = $result['result']['_embedded']['items'];
            $found = count($result['result']['_embedded']['items']) + $offset;

            if ($found < $result['result']['_embedded']['total']) {
                $list = array_merge($list, $this->listFolderChildren($item_path, $found));
            }
        }

        return $list;
    }

    /**
     * Download a file
     *
     * @param string $path The file path
     *
     * @return bool|array infos of the file to download or the failure of the action
     */
    public function downloadFile($path)
    {
        $result = $this->apiGet(self::FILES_URL . '/download?path=' . rawurlencode((string) $path));

        if ($result['success'] === false) {
            return false;
        }

        return $result['result'];
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
