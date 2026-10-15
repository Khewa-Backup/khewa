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
require_once 'box-config.php';

class BoxLib
{
    const PAGE = 'box';

    /**
     * @var string the base URL for authorization requests
     */
    const AUTH_URL = 'https://account.box.com/api/oauth2/authorize/';

    /**
     * @var string the base URL for token requests
     */
    const TOKEN_URL = 'https://api.box.com/oauth2/token/';

    /**
     * @var string the base URL for API requests
     */
    const API_URL = 'https://api.box.com/2.0/';

    /**
     * @var string the base URL for API upload requests
     */
    const UPLOAD_URL = 'https://upload.box.com/api/2.0/';

    /**
     * @var int The maximal size of the log file
     */
    const MAX_FILE_LOG_SIZE = 200000000; // environs 200Mo (200 000 000)

    /**
     * @var int The maximal size sent to the Box account
     */
    const MAX_FILE_UPLOAD_SIZE = 20971520; // 20Mo (20 * 1024 * 1024 = 20 971 520)

    /**
     * @var string The name of the current log file
     */
    const CURRENT_FILE_LOG_NAME = 'current_log_box.txt';

    /**
     * @var string The name of old log file
     */
    const OLD_FILE_LOG_NAME = '_log_boxe.txt';

    // The current token
    private $token;
    // The current scopes
    private $scopes;
    // The client ID
    private $client_id;
    // The client secret
    private $client_secret;
    // The redirect uri
    private $redirect_uri;
    // The sdk uri
    private $sdk_uri;
    // The physic sdk uri
    private $physic_sdk_uri;
    // Instance of NtbrCore
    private $ntbr;

    public function __construct($ntbr, $sdk_uri, $physic_sdk_uri, $token = '')
    {
        if (!empty($token)) {
            $this->token = $token;
        }

        $this->client_id = BOX_CLIENT_ID;
        $this->client_secret = BOX_CLIENT_SECRET;
        $this->sdk_uri = $sdk_uri;
        $this->physic_sdk_uri = $physic_sdk_uri;
        $this->redirect_uri = BOX_CALLBACK_URI;
        $this->scopes = 'root_readwrite';
        $this->ntbr = $ntbr;
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
        return $this->ntbr->execCurl($curl);
    }

    /**
     * Performs a call to the Box API using the POST method.
     *
     * @param string $url the url of the API call
     * @param array|object $data the data to pass in the body of the request
     * @param array $header the data to pass in the header of the request
     * @param array $add_options the options to add
     *
     * @return array the result of the execution of the curl
     */
    public function apiPost($url, $data = [], $header = [], $add_options = [])
    {
        if (strpos($url, self::TOKEN_URL) !== false) {
            $header[] = 'Content-Type: application/x-www-form-urlencoded';
        } else {
            $header[] = 'Content-Type: application/json';
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
        ];

        $curl = $this->createCurl($options, $header);

        if (count($add_options)) {
            curl_setopt_array($curl, $add_options);
        }

        return $this->execCurl($curl);
    }

    /**
     * Performs a call to the Box API using the GET method.
     *
     * @param string $url the url of the API call
     * @param array $add_options the options to add
     *
     * @return array the response of the execution of the curl
     */
    public function apiGet($url, $add_options = [])
    {
        $curl = $this->createCurl();

        if (count($add_options)) {
            curl_setopt_array($curl, $add_options);
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        return $this->execCurl($curl);
    }

    /**
     * Performs a call to the Box API using the PUT method.
     *
     * @param string $url the path of the API call
     * @param string $stream the data to upload
     * @param array $header the data to pass in the header of the request
     * @param float $filesize the size of the stream
     *
     * @return array the result of the execution of the curl
     */
    public function apiPut($url, $stream, $header = [], $filesize = 0)
    {
        $header[] = 'Content-Type: application/octet-stream';

        $curl = $this->createCurl([], $header);

        if (!(float) $filesize) {
            $stats = fstat($stream);
            $filesize = $stats[7];
        }

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
     * Performs a call to the Box API using the DELETE method.
     *
     * @param string $url the url to call
     *
     * @return bool the success or failure of the action
     */
    public function apiDelete($url)
    {
        $curl = self::createCurl();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ];

        curl_setopt_array($curl, $options);

        return self::execCurl($curl);
    }

    /**
     * Gets the URL of the authorize in form.
     *
     * @return string|bool the login URL or false
     */
    public function getLogInUrl()
    {
        $url = self::AUTH_URL
            . '?response_type=code'
            . '&redirect_uri=' . $this->redirect_uri
            . '&client_id=' . $this->client_id
            . '&scope=' . $this->scopes;

        return $url;
    }

    /**
     * Gets the access token
     *
     * @return array|bool the access token or false
     */
    public function getToken($code)
    {
        $datas = 'code=' . $code
            . '&redirect_uri=' . $this->redirect_uri
            . '&client_id=' . $this->client_id
            . '&client_secret=' . $this->client_secret
            . '&grant_type=authorization_code';

        $result = $this->apiPost(self::TOKEN_URL, $datas);

        if ($result['success'] && !empty($result['result'])) {
            $result['result']['created'] = time();
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
        $datas = 'client_id=' . $this->client_id
            . '&client_secret=' . $this->client_secret
            . '&refresh_token=' . $refreshToken
            . '&grant_type=refresh_token';

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
     * Get the available quota of the current Box account.
     *
     * @return int Available quota
     */
    public function getAvailableQuota()
    {
        $result = $this->apiGet(self::API_URL . 'users/me/');

        $quota_available = 0;
        $quota_total = 0;

        if ($result['success'] && !empty($result['result'])) {
            $quota_total = $result['result']['space_amount'];
            $quota_available = $result['result']['space_amount'] - $result['result']['space_used'];
        }

        $this->log(
            sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::BOX)
            . ' ' . $this->ntbr->l('Available quota:', self::PAGE) . ' ' . $quota_available . '/' . $quota_total
        );

        return $quota_available;
    }

    /**
     * Test the connection
     *
     * @return bool Connection result
     */
    public function testConnection()
    {
        $result = $this->apiGet(self::API_URL . 'users/me');

        return $result['success'];
    }

    /**
     * Get the folders tree of the current Box account.
     *
     * @param string $id_parent
     *
     * @return array folders tree
     */
    public function getChildrenTree($id_parent, $offset = 0)
    {
        $childrens = [];

        $result = $this->apiGet(self::API_URL . 'folders/' . $id_parent . '/items/?offset=' . $offset);

        if ($result['success'] && !empty($result['result'])) {
            if (isset($result['result']['entries']) && is_array($result['result']['entries']) && isset($result['result']['total_count'])) {
                foreach ($result['result']['entries'] as $item) {
                    if ($item['type'] == 'folder') {
                        $childrens[] = $item;
                    }
                }

                $total_found = count($result['result']['entries']) + $offset;

                if ($total_found < $result['result']['total_count']) {
                    $childrens = array_merge($childrens, $this->getChildrenTree($id_parent, $total_found));
                }
            }
        }

        return $childrens;
    }

    /**
     * Get the children files of the current Box account.
     *
     * @param string $id_parent
     *
     * @return array files
     */
    public function getChildrenFiles($id_parent, $offset = 0)
    {
        $childrens = [];

        $result = $this->apiGet(self::API_URL . 'folders/' . $id_parent . '/items/?fields=size,type,id,name&offset=' . $offset);

        if ($result['success'] && !empty($result['result'])) {
            if (isset($result['result']['entries']) && is_array($result['result']['entries']) && isset($result['result']['total_count'])) {
                foreach ($result['result']['entries'] as $item) {
                    if ($item['type'] != 'folder') {
                        $childrens[] = $item;
                    }
                }

                $total_found = count($result['result']['entries']) + $offset;

                if ($total_found < $result['result']['total_count']) {
                    $childrens = array_merge($childrens, $this->getChildrenFiles($id_parent, $total_found));
                }
            }
        }

        return $childrens;
    }

    /**
     * Get a link to download the file
     *
     * @param string $id_file
     *
     * @return string file link
     */
    public function downloadFile($id_file)
    {
        $options = [
            CURLOPT_HEADER => true,
        ];

        $result = $this->apiGet(self::API_URL . 'files/' . $id_file . '/content/', $options);

        if ($result['success'] && !empty($result['result'])) {
            $matches = [];
            preg_match('/Location:(.*?)\n/i', $result['result'], $matches);

            if (isset($matches[1])) {
                return $matches[1];
            }
        }

        return '';
    }

    /**
     * Check if file exists in Box account and if so return the file ID.
     *
     * @param string $filename
     * @param string $id_parent
     *
     * @return string|bool Id of the file or false if not found
     */
    public function checkExists($filename, $id_parent)
    {
        $datas = '?';
        $datas .= 'type=file'; // only search for files
        $datas .= '&ancestor_folder_ids=' . $id_parent; // with $id_parent as parent
        $datas .= '&content_types=name'; // Search only for corresponding names
        $datas .= '&query="' . $filename . '"'; // The name to search for

        $result = $this->apiGet(self::API_URL . 'search' . $datas);

        if ($result['success'] && !empty($result['result'])) {
            if (isset($result['result']['entries'][0]['id'])) {
                return $result['result']['entries'][0]['id'];
            }
        }

        return false;
    }

    /**
     * Delete a file on the Box account
     *
     * @param string $id_file the path of the file on Box
     *
     * @return bool the success or failure of the action
     */
    public function deleteFile($id_file)
    {
        $result = $this->apiDelete(self::API_URL . 'files/' . $id_file . '/');

        return $result['success'];
    }

    /**
     * Upload a file on the Box account
     *
     * @param string $file_path the path of the file
     * @param string $id_file_destination the destination of the file
     * @param string $name the new name of the file
     * @param int $nb_part current part number
     * @param int $nb_part_total total parts to be sent
     *
     * @return bool the success or failure of the action
     */
    public function uploadFile($file_path, $id_file_destination, $name = '', $nb_part = 1, $nb_part_total = 1)
    {
        $total_file_size = filesize($file_path);

        if (!$name || $name == '') {
            $name = basename($file_path);
        }

        if (!$this->createSession($id_file_destination, $name, $total_file_size)) {
            return false;
        }

        $this->ntbr->box_position = 0;

        return $this->resumeUploadFile($file_path, $nb_part, $nb_part_total);
    }

    /**
     * Resume the upload a file on the Box account
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

        $total_file_size = $this->ntbr->getFileSize($file_path);

        $byte_start = 0;
        $content_length = $total_file_size;

        if ($this->ntbr->box_position > 0) {
            $byte_start = $this->ntbr->box_position; // Next chunk
        }

        $part_size = ($this->ntbr->box_session_part_size) ? $this->ntbr->box_session_part_size : self::MAX_FILE_UPLOAD_SIZE;

        if ($total_file_size > $part_size) {
            $content_length = $part_size;
        }

        $byte_end = $byte_start + $content_length - 1;

        if ($byte_end > $total_file_size) {
            $byte_end = $total_file_size - 1;
            $content_length = $byte_end - $byte_start + 1;
        }

        $byte_to_go = $total_file_size - $this->ntbr->box_position;

        $file = fopen($file_path, 'r+');

        if ($this->ntbr->box_position > 0) {
            $file = $this->ntbr->goToPositionInFile($file, $this->ntbr->box_position, false);

            if ($file === false) {
                return false;
            }
        }

        while ($byte_to_go > 0) {
            $datas = fread($file, $content_length);

            $header = [
                'Digest: sha=' . base64_encode(sha1($datas, true)),
                'Content-Range: bytes ' . $byte_start . '-' . $byte_end . '/' . $total_file_size,
            ];

            $percent = ($byte_end / $total_file_size) * 100;

            if ($nb_part_total > 1) {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::BOX)
                    . ' ' . $nb_part . '/' . $nb_part_total . $this->ntbr->l(':', self::PAGE) . ' ' . (int) $percent . '%'
                );
            } else {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::BOX)
                    . ' ' . (int) $percent . '%'
                );
            }

            $result_upload = $this->apiPut(self::UPLOAD_URL . 'files/upload_sessions/' . $this->ntbr->box_session, $datas, $header, $total_file_size);
            sleep(3);

            if (!$result_upload['success'] || !$result_upload['result']) {
                fclose($file);

                return false;
            }

            if (isset($result_upload['result']['offset']) && $byte_end != $result_upload['result']['offset']) {
                $byte_end = $result_upload['result']['offset'];
                $content_length = $byte_end - $byte_start + 1;

                $file = $this->ntbr->goToPositionInFile($file, $byte_end + 1, false);

                if ($file === false) {
                    return false;
                }
            }

            $byte_to_go -= $content_length;

            $part_size = ($this->ntbr->box_session_part_size) ? $this->ntbr->box_session_part_size : self::MAX_FILE_UPLOAD_SIZE;

            if ($byte_to_go < $part_size) {
                $content_length = $byte_to_go;
            }

            $byte_start = $byte_end + 1;
            $this->ntbr->box_position = $byte_start;
            $byte_end = $byte_start + $content_length - 1;

            // refresh
            $this->ntbr->refreshBackup(true);
        }

        fclose($file);

        $uploaded_parts = $this->apiGet(self::UPLOAD_URL . 'files/upload_sessions/' . $this->ntbr->box_session . '/parts');

        if (!$uploaded_parts['success'] || !$uploaded_parts['result'] || !isset($uploaded_parts['result']['entries'])) {
            $this->log($this->ntbr->l('The session parts cannot be found', self::PAGE), true);

            return false;
        }

        $list_parts = [];
        $list_parts['parts'] = $uploaded_parts['result']['entries'];
        $list_parts_json = json_encode($list_parts);

        $header = [
            'Digest: sha=' . base64_encode(hash_file('sha1', $file_path, true)),
        ];

        $result_commit = $this->apiPost(self::UPLOAD_URL . 'files/upload_sessions/' . $this->ntbr->box_session . '/commit', $list_parts_json, $header);

        return $result_commit['success'];
    }

    /**
     * Create a resumable session
     *
     * @param string $id_file_destination the destination of the file
     * @param string $name the name of the file
     * @param int $total_file_size the total size of the file
     *
     * @return bool the success or failure of the action
     */
    public function createSession($id_file_destination, $name, $total_file_size)
    {
        $datas =
            '{
                "folder_id": "' . $id_file_destination . '",
                "file_size": ' . $total_file_size . ',
                "file_name": "' . $name . '"
            }'
        ;

        $result_create_session = $this->apiPost(self::UPLOAD_URL . 'files/upload_sessions', $datas);

        if (!$result_create_session['success'] || !isset($result_create_session['result']['id'])) {
            $this->log($this->ntbr->l('The session cannot be created', self::PAGE), true);

            return false;
        }

        $this->ntbr->box_session = $result_create_session['result']['id'];
        $this->ntbr->box_session_part_size = $result_create_session['result']['part_size'];

        return true;
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

        $position = $this->ntbr->box_position;
        $byte_start = $position;
        $byte_end = $byte_start + $content_size - 1;
        $byte_to_go = $total_file_size - $position;

        if ($byte_to_go > 0) {
            $header = [
                'Digest: sha=' . base64_encode(sha1($content, true)),
                'Content-Range: bytes ' . $byte_start . '-' . $byte_end . '/' . $total_file_size,
                'Content-Type: application/octet-stream',
            ];

            $result_upload = $this->apiPut(self::UPLOAD_URL . 'files/upload_sessions/' . $this->ntbr->box_session, $content, $header, $total_file_size);

            // $this->log($header, true);
            // $this->log($result_upload, true);

            if (!$result_upload['success'] || !$result_upload['result']) {
                return -1;
            }

            if (isset($result_upload['result']['offset']) && $byte_end != $result_upload['result']['offset']) {
                $byte_end = $result_upload['result']['offset'];
            }

            $position = $byte_end + 1;
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
