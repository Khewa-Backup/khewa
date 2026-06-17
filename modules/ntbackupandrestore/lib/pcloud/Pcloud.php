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
class PcloudLib
{
    const PAGE = 'pcloud';

    /**
     * @var int The maximal size of a file to upload
     */
    const MAX_FILE_UPLOAD_SIZE = 10485760; // 10Mo (10 * 1024 * 1024 = 10 485 760)

    /**
     * @var string the base URL for authorization requests
     */
    const AUTH_URL = 'https://my.pcloud.com/oauth2/authorize';

    /**
     * @var string the base URL for API requests
     */
    const API_URL_US = 'https://api.pcloud.com/';
    const API_URL_EU = 'https://eapi.pcloud.com/';

    const LOCATION_ID_US = 1;
    const LOCATION_ID_EU = 2;

    const REDIRECT_URI = 'https://oauth.2n-tech.com/get_oauth_code.php';
    const CLIENT_ID = 'x493nxlk5fX';
    const CLIENT_SECRET = 'xkH8EwYGnUjeE18CiuhFR0ySUnaV';

    // The current token
    private $token;
    // The sdk uri
    private $sdk_uri;
    // The physic sdk uri
    private $physic_sdk_uri;
    // The location id (1 - US, 2 - Europe)
    private $location_id;
    // Instance of NtbrCore
    private $ntbr;

    public function __construct($ntbr, $sdk_uri, $physic_sdk_uri, $location_id, $token = '')
    {
        $this->sdk_uri = $sdk_uri;
        $this->physic_sdk_uri = $physic_sdk_uri;
        $this->location_id = $location_id;
        $this->ntbr = $ntbr;

        if (!empty($token)) {
            $this->token = $token;
        }
    }

    /**
     * Get the url for requests.
     *
     * @return string url
     */
    public function getUrl()
    {
        if ($this->location_id == self::LOCATION_ID_EU) {
            return self::API_URL_EU;
        }

        return self::API_URL_US;
    }

    public function convertParams($params)
    {
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

        return $list_params;
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
            $curl_header[] = 'Authorization: Bearer ' . $this->token;
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
        $result = $this->ntbr->execCurl($curl);

        return $result;
    }

    /**
     * Performs a call to the API using the GET method.
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
     * Performs a call to the API using the PUT method.
     *
     * @param string $url the path of the API call
     * @param string $stream the data to upload
     * @param array $params parameters of the API call
     *
     * @return array the result of the execution of the curl
     */
    public function apiPut($url, $stream, $params = [])
    {
        $curl = $this->createCurl($params);

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $stream,
            CURLOPT_HTTPHEADER => ['Content-Type: text/html'],
            CURLOPT_BINARYTRANSFER => true,
        ];

        curl_setopt_array($curl, $options);

        return $this->execCurl($curl);
    }

    /**
     * Gets the URL of the authorize in form.
     *
     * @return string|bool the login URL or false
     */
    public static function getLogInUrl()
    {
        $url = self::AUTH_URL
            . '?client_id=' . self::CLIENT_ID
            . '&response_type=code'
            . '&force_reapprove=true'
            . '&redirect_uri=' . self::REDIRECT_URI;

        return $url;
    }

    /**
     * Gets the access token
     *
     * @return array|bool the access token or false
     */
    public function getToken($code)
    {
        $datas = 'client_id=' . self::CLIENT_ID
            . '&client_secret=' . self::CLIENT_SECRET
            . '&code=' . $code;

        $result = $this->apiGet($this->getUrl() . 'oauth2_token?' . $datas);

        if ($result['success'] && !empty($result['result']) && isset($result['result']['access_token']) && $result['result']['access_token']) {
            return $result['result']['access_token'];
        }

        return false;
    }

    /**
     * Test the connection
     *
     * @return bool Connection result
     */
    public function testConnection()
    {
        $result = $this->apiGet($this->getUrl() . 'userinfo');

        if (!$result['success'] || !isset($result['result']['userid'])) {
            return false;
        }

        return true;
    }

    /**
     * Get the available quota of the current account.
     *
     * @return int Available quota
     */
    public function getAvailableQuota()
    {
        $quota_available = 0;
        $quota_total = 0;

        $result = $this->apiGet($this->getUrl() . 'userinfo');

        if ($result['success'] && !empty($result['result'])) {
            if (isset($result['result']['usedquota'], $result['result']['quota'])) {
                $quota_total = $result['result']['quota']; // The user's total quota allocation (bytes).
                $quota_used = $result['result']['usedquota']; // The user's used quota (bytes).

                $quota_available = $quota_total - $quota_used;
            }
        }

        $this->log(
            sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::PCLOUD)
            . ' ' . $this->ntbr->l('Available quota:', self::PAGE) . ' ' . $quota_available . '/' . $quota_total
        );

        return $quota_available;
    }

    /**
     * Fetches the files
     *
     * @param string $folder_id the id of the folder to look into
     *
     * @return array|bool list of files or false
     */
    public function getListFiles($folder_id = 0)
    {
        $files = [];

        $params = [
            'folderid' => $folder_id,
        ];

        $result = $this->apiGet($this->getUrl() . 'listfolder' . $this->convertParams($params));

        if (!$result['success'] || !isset($result['result']['metadata']) || !isset($result['result']['metadata']['contents'])) {
            return false;
        }

        if (is_array($result['result']['metadata']['contents'])) {
            foreach ($result['result']['metadata']['contents'] as $item) {
                // ignore folder
                if ($item['isfolder']) {
                    continue;
                }

                $files[] = $item;
            }
        }

        return $files;
    }

    /**
     * Fetches the directories
     *
     * @param string $folder_id the id of the folder to look into
     *
     * @return array|bool list of directories or false
     */
    public function getListDirectories($folder_id = 0)
    {
        $directories = [];

        $params = [
            'folderid' => $folder_id,
        ];

        $result = $this->apiGet($this->getUrl() . 'listfolder' . $this->convertParams($params));

        if (!$result['success'] || !isset($result['result']['metadata']) || !isset($result['result']['metadata']['contents'])) {
            return false;
        }

        if (is_array($result['result']['metadata']['contents'])) {
            foreach ($result['result']['metadata']['contents'] as $item) {
                // ignore files
                if (!$item['isfolder']) {
                    continue;
                }

                // Remove first char if it is "d"
                if (substr($item['id'], 0, 1) == 'd') {
                    $item['id'] = substr($item['id'], 1);
                }

                $directories[] = $item;
            }
        }

        return $directories;
    }

    /**
     * Check if a file or directory exists
     *
     * @param string $item_path The item path (ex: backup/my_backup.tar.gz for a file or backup/ for a directory)
     *
     * @return bool if the file exists
     */
    public function checkExists($item_path)
    {
        $result = $this->apiGet($this->getUrl() . 'stat?path=' . rawurlencode((string) $item_path));

        if (!$result['success'] || !isset($result['result']['metadata']) || !isset($result['result']['metadata']['id'])) {
            return false;
        }

        return true;
    }

    /**
     * Deletes a file or directory in the current account.
     *
     * @param string $item_path The path to the item to delete. (ex: backup/my_backup.tar.gz for a file or backup/ for a directory)
     *
     * @return bool if the item was successfully deleted
     */
    public function deleteItem($item_path)
    {
        $result = $this->apiGet($this->getUrl() . 'deletefile?path=' . rawurlencode((string) $item_path));

        if (!$result['success'] || !isset($result['result']['metadata']) || !isset($result['result']['metadata']['isdeleted']) || !$result['result']['metadata']['isdeleted']) {
            return false;
        }

        return true;
    }

    /**
     * Create a resumable upload
     *
     * @return bool the success or failure of the action
     */
    public function createUpload()
    {
        $result = $this->apiGet($this->getUrl() . 'upload_create');

        if (!$result['success'] || !isset($result['result']['uploadid']) || !$result['result']['uploadid']) {
            return false;
        }

        $this->ntbr->pcloud_session = $result['result']['uploadid'];
        $this->ntbr->pcloud_position = 0;

        return true;
    }

    /**
     * End a resumable upload
     *
     * @param string $file_name the name of the file
     * @param string $folder_id the destination of the file
     *
     * @return bool the success or failure of the action
     */
    public function endUpload($file_name, $folder_id)
    {
        $params = [
            'uploadid' => $this->ntbr->pcloud_session,
            'name' => $file_name,
            'folderid' => $folder_id,
        ];

        $result = $this->apiGet($this->getUrl() . 'upload_save' . $this->convertParams($params));

        if (!$result['success']) {
            return false;
        }

        return true;
    }

    /**
     * Upload a file on the account
     *
     * @param string $file_path the path of the file
     * @param string $folder_id the destination of the file
     * @param int $nb_part current part number
     * @param int $nb_part_total total parts to be sent
     * @param string $file_name the name of the file
     *
     * @return bool the success or failure of the action
     */
    public function uploadFile($file_path, $folder_id, $nb_part = 1, $nb_part_total = 1, $file_name = '')
    {
        $this->createUpload();

        return $this->resumeUploadFile($file_path, $folder_id, $nb_part, $nb_part_total, $file_name);
    }

    /**
     * Resume the upload a file on the account
     *
     * @param string $file_path the path of the file
     * @param string $folder_id the destination of the file
     * @param int $nb_part current part number
     * @param int $nb_part_total total parts to be sent
     * @param string $file_name the name of the file
     *
     * @return bool the success or failure of the action
     */
    public function resumeUploadFile($file_path, $folder_id, $nb_part = 1, $nb_part_total = 1, $file_name = '')
    {
        $result_upload = [
            'success' => true,
            'result' => '',
        ];

        if ($file_name == '') {
            $file_name = basename($file_path);
        }

        $total_file_size = $this->ntbr->getFileSize($file_path);
        $content_length = $total_file_size;

        if ($total_file_size > self::MAX_FILE_UPLOAD_SIZE) {
            $content_length = self::MAX_FILE_UPLOAD_SIZE;
        }

        $byte_to_go = $total_file_size - $this->ntbr->pcloud_position;

        $file = fopen($file_path, 'r+');

        if ($this->ntbr->pcloud_position > 0) {
            $file = $this->ntbr->goToPositionInFile($file, $this->ntbr->pcloud_position, false);

            if ($file === false) {
                return false;
            }
        }

        while ($byte_to_go > 0) {
            $params = [
                'access_token' => $this->token,
                'uploadid' => $this->ntbr->pcloud_session,
                'uploadoffset' => $this->ntbr->pcloud_position,
            ];

            $datas = fread($file, $content_length);

            $percent = (($this->ntbr->pcloud_position + $content_length) / $total_file_size) * 100;

            if ($nb_part_total > 1) {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::PCLOUD)
                    . ' ' . $nb_part . '/' . $nb_part_total . $this->ntbr->l(':', self::PAGE) . ' ' . (int) $percent . '%'
                );
            } else {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::PCLOUD)
                    . ' ' . (int) $percent . '%'
                );
            }

            $result_upload = $this->apiPut($this->getUrl() . 'upload_write' . $this->convertParams($params), $datas);

            if (!$result_upload['success'] || !$result_upload['result']) {
                fclose($file);

                return false;
            }

            $this->ntbr->pcloud_position += $content_length;

            $byte_to_go -= $content_length;

            if (($this->ntbr->pcloud_position + $content_length) > $total_file_size) {
                $content_length = $total_file_size - $this->ntbr->pcloud_position;
            }

            // refresh
            $this->ntbr->refreshBackup(true);
        }

        fclose($file);

        return $this->endUpload($file_name, $folder_id);
    }

    /**
     * Resume the upload of the content on the account
     *
     * @param string $content the content to upload
     * @param int $content_size the size of the content to upload
     * @param int $total_file_size The total size of the file the content is part of
     *
     * @return bool the success or failure of the action
     */
    public function resumeUploadContent($content, $content_size, $total_file_size)
    {
        $result_upload = [
            'success' => true,
            'result' => '',
        ];

        $position = $this->ntbr->pcloud_position;
        $byte_to_go = $total_file_size - $this->ntbr->pcloud_position;

        if ($byte_to_go > 0) {
            $params = [
                'access_token' => $this->token,
                'uploadid' => $this->ntbr->pcloud_session,
                'uploadoffset' => $position,
            ];

            $result_upload = $this->apiPut($this->getUrl() . 'upload_write' . $this->convertParams($params), $content);

            if (!$result_upload['success'] || !$result_upload['result']) {
                return -1;
            }

            $position += $content_size;
        }

        return $position;
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
