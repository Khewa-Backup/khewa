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
require_once 'dropbox-config.php';

class DropboxLib
{
    const PAGE = 'dropbox';
    /**
     * @var string the base URL for authorization requests
     */
    const AUTH_URL = 'https://www.dropbox.com/oauth2/authorize';

    /**
     * @var string the base URL for token requests
     */
    const TOKEN_URL = 'https://api.dropboxapi.com/oauth2/token';

    /**
     * @var string the base URL for API requests
     */
    const API_URL = 'https://api.dropboxapi.com/2/';

    /**
     * @var string the base URL for files requests
     */
    const FILES_URL = 'https://content.dropboxapi.com/2/';

    /**
     * @var int The maximal size of a file to upload
     */
    const MAX_FILE_UPLOAD_SIZE = 10485760; // 10Mo (10 * 1024 * 1024 = 10 485 760)

    /**
     * @var int The maximal size of the log file
     */
    const MAX_FILE_LOG_SIZE = 200000000; // environs 200Mo (200 000 000)

    /**
     * @var string The name of the current log file
     */
    const CURRENT_FILE_LOG_NAME = 'current_log_dropbox.txt';

    /**
     * @var string The name of old log file
     */
    const OLD_FILE_LOG_NAME = '_log_dropbox.txt';

    // The current token
    private $token;
    // The app key
    private $app_key;
    // The app secret
    private $app_secret;
    // The sdk uri
    private $sdk_uri;
    // The physic sdk uri
    private $physic_sdk_uri;
    // Instance of NtbrCore
    private $ntbr;
    // team_member_id
    private $team_member_id;
    // team_root_id
    private $team_root_id;

    public function __construct($ntbr, $sdk_uri, $physic_sdk_uri, $business, $token = '')
    {
        if (!empty($token)) {
            $this->token = $token;
        }

        $this->app_key = ($business) ? DROPBOX_BUSINESS_APP_KEY : DROPBOX_APP_KEY;
        $this->app_secret = ($business) ? DROPBOX_BUSINESS_APP_SECRET : DROPBOX_APP_SECRET;
        $this->sdk_uri = $sdk_uri;
        $this->physic_sdk_uri = $physic_sdk_uri;
        $this->ntbr = $ntbr;
        $this->team_member_id = ($business) ? $this->getAuthenticatedAdmin() : '';
        $this->team_root_id = ($business) ? $this->getTeamMemberFolderId() : '';
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
        } else {
            $clientCredentials = $this->app_key . ':' . $this->app_secret;
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
     * Performs a call to the Dropbox API using the POST method.
     *
     * @param string $url the url of the API call
     * @param array|object $data the data to pass in the body of the request
     *
     * @return array the result of the execution of the curl
     */
    public function apiPost($url, $data = [], $header = [])
    {
        if (!empty($this->team_member_id) && !empty($this->team_root_id)) {
            $header[] = 'Dropbox-API-Path-Root: {".tag": "root", "root": "' . $this->team_root_id . '"}';
        }

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
     * Performs a call to the Dropbox API using the GET method.
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
     * Gets the URL of the authorize in form.
     *
     * @return string|bool the login URL or false
     */
    public function getLogInUrl()
    {
        $url = self::AUTH_URL
            . '?client_id=' . $this->app_key
            . '&response_type=code&token_access_type=offline';

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

        if ($result['success'] && !empty($result['result'])) {
            $result['result']['created'] = time();

            return $result['result'];
        }

        return false;
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
        $datas = 'refresh_token=' . $refreshToken
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
     * Test the connection to the Dropbox account
     *
     * @return bool Connection result
     */
    public function testConnection()
    {
        $header = ['Content-Type: application/json'];

        if (!empty($this->team_member_id)) {
            $header[] = 'Dropbox-API-Select-User: ' . $this->team_member_id;
        }

        $result = $this->apiPost(self::API_URL . 'users/get_space_usage', json_encode(null), $header);

        return $result['success'];
    }

    /**
     * Get team member ID
     *
     * @return string Team member ID
     */
    public function getAuthenticatedAdmin()
    {
        $result = $this->apiPost(self::API_URL . 'team/token/get_authenticated_admin', json_encode(null), ['Content-Type: application/json']);

        if ($result['success'] && !empty($result['result'])) {
            if (isset($result['result']['admin_profile']['team_member_id'])) {
                return $result['result']['admin_profile']['team_member_id'];
            }
        }

        return '';
    }

    /**
     * Get team member folder ID
     *
     * @return string Team member folder ID
     */
    public function getTeamMemberFolderId()
    {
        $header = ['Content-Type: application/json'];

        if (!empty($this->team_member_id)) {
            $header[] = 'Dropbox-API-Select-Admin: ' . $this->team_member_id;
        }

        $result = $this->apiPost(self::API_URL . '/users/get_current_account', json_encode(null), $header);

        if ($result['success'] && !empty($result['result'])) {
            if (isset($result['result']['root_info']['root_namespace_id'])) {
                return $result['result']['root_info']['root_namespace_id'];
            }
        }

        return false;
    }

    /**
     * Get the available quota of the current Dropbox account.
     *
     * @return int Available quota
     */
    public function getAvailableQuota()
    {
        $quota_available = 0;
        $quota_total = 0;
        $header = ['Content-Type: application/json'];

        if (!empty($this->team_member_id)) {
            $header[] = 'Dropbox-API-Select-User: ' . $this->team_member_id;
        }

        $result = $this->apiPost(self::API_URL . 'users/get_space_usage', json_encode(null), $header);

        if ($result['success'] && !empty($result['result'])) {
            if (isset($result['result']['used'], $result['result']['allocation']['allocated'])) {
                $quota_total = $result['result']['allocation']['allocated']; // The user's total quota allocation (bytes).
                $quota_used = $result['result']['used']; // The user's used quota outside of shared folders (bytes).
                // $quota_shared_used = $result['result']['quota_info']['shared']; // The user's used quota in shared folders (bytes).

                $quota_available = $quota_total - $quota_used;
            }
        }

        $this->log(
            sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::DROPBOX)
            . ' ' . $this->ntbr->l('Available quota:', self::PAGE) . ' ' . $quota_available . '/' . $quota_total
        );

        return $quota_available;
    }

    /**
     * Upload a file on the Dropbox account
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
        $file_part_size_max = self::MAX_FILE_UPLOAD_SIZE;
        $url_start = self::FILES_URL . 'files/upload_session/start';
        $byte_offset = 0;
        $header = ['Content-Type: application/octet-stream'];

        if (!empty($this->team_member_id)) {
            $header[] = 'Dropbox-API-Select-Admin: ' . $this->team_member_id;
        }

        $file = fopen($file_path, 'r+');
        $total_file_size = filesize($file_path);

        if ($file_part_size_max >= $total_file_size) {
            $file_part_size_max = $total_file_size;
        }

        $part_file = fread($file, $file_part_size_max);
        $result = $this->apiPost($url_start, $part_file, $header);

        if ($result['success'] === false) {
            return false;
        }

        $uploadId = $result['result']['session_id'];
        $this->ntbr->dropbox_upload_id = $uploadId;
        $byte_offset += $file_part_size_max;
        $this->ntbr->dropbox_position = $byte_offset;

        return $this->resumeUploadFile($file_path, $file_destination, $uploadId, $byte_offset, $nb_part, $nb_part_total);
    }

    /**
     * Resume the upload of a file on the Dropbox account
     *
     * @param string $file_path the path of the file
     * @param string $file_destination the destination of the file
     * @param string $uploadId id of the upload
     * @param int $position position in the file
     * @param int $nb_part current part number
     * @param int $nb_part_total total parts to be sent
     *
     * @return bool the success or failure of the action
     */
    public function resumeUploadFile($file_path, $file_destination, $uploadId, $position, $nb_part = 1, $nb_part_total = 1)
    {
        $file_part_size_max = self::MAX_FILE_UPLOAD_SIZE;
        $url_append = self::FILES_URL . 'files/upload_session/append_v2';
        $url_finish = self::FILES_URL . 'files/upload_session/finish';
        $header = ['Content-Type: application/octet-stream'];
        $byte_offset = $position;

        if (!empty($this->team_member_id)) {
            $header[] = 'Dropbox-API-Select-Admin: ' . $this->team_member_id;
        }

        $file = fopen($file_path, 'r+');
        $total_file_size = filesize($file_path);

        if ($position > 0) {
            // Go to the last position in the file
            $file = $this->ntbr->goToPositionInFile($file, $position, false);

            if ($file === false) {
                return false;
            }
        }

        while (!feof($file)) {
            $part_file = fread($file, $file_part_size_max);
            $rest = $total_file_size - $byte_offset;

            if ($rest < $file_part_size_max) {
                $file_part_size_max = $rest;
            }

            $byte_to_go = $total_file_size - ($byte_offset + $file_part_size_max);

            $percent = ($byte_offset / $total_file_size) * 100;

            if ($nb_part_total > 1) {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::DROPBOX)
                    . ' ' . $nb_part . '/' . $nb_part_total . $this->ntbr->l(':', self::PAGE) . ' ' . (int) $percent . '%'
                );
            } else {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::DROPBOX)
                    . ' ' . (int) $percent . '%'
                );
            }

            if ($byte_to_go > 0) {
                $header_append = $header;

                $datas_header_append = [
                    'cursor' => [
                        'session_id' => $uploadId,
                        'offset' => $byte_offset,
                    ],
                ];

                $header_append[] = 'Dropbox-API-Arg: ' . json_encode($datas_header_append);
                $result = $this->apiPost($url_append, $part_file, $header_append);

                if ($result['success'] !== false) {
                    $byte_offset += $file_part_size_max;
                    $this->ntbr->dropbox_position = $byte_offset;
                } else {
                    return false;
                }
            } else {
                $header_finish = $header;

                if ($file_destination[0] !== '/') {
                    $file_destination = '/' . $file_destination;
                }

                $datas_header_finish = [
                    'cursor' => [
                        'session_id' => $uploadId,
                        'offset' => $byte_offset,
                    ],
                    'commit' => [
                        'path' => $file_destination,
                        'mode' => 'add',
                        'autorename' => true,
                    ],
                ];

                $header_finish[] = 'Dropbox-API-Arg: ' . json_encode($datas_header_finish);
                $result_commit = $this->apiPost($url_finish, $part_file, $header_finish);
                fclose($file);

                return $result_commit['success'];
            }

            // refresh
            $this->ntbr->refreshBackup(true);
        }

        return false;
    }

    /**
     * Create a resumable session
     *
     * @return bool the success or failure of the action
     */
    public function createSession()
    {
        $url_start = self::FILES_URL . 'files/upload_session/start';
        $header = ['Content-Type: application/octet-stream'];

        if (!empty($this->team_member_id)) {
            $header[] = 'Dropbox-API-Select-Admin: ' . $this->team_member_id;
        }

        $result = $this->apiPost($url_start, '', $header);

        if ($result['success'] === false) {
            return false;
        }

        $this->ntbr->dropbox_upload_id = $result['result']['session_id'];
        $this->ntbr->old_percent = 0;

        return true;
    }

    /**
     * Resume the upload of the content on the Dropbox account
     *
     * @param string $content the content to upload
     * @param int $content_size the size of the content to upload
     * @param string $file_destination the destination of the file
     * @param int $total_file_size The total size of the file the content is part of
     * @param string $uploadId id of the upload
     * @param int $position position in the file
     *
     * @return bool the success or failure of the action
     */
    public function resumeUploadContent($content, $content_size, $file_destination, $total_file_size, $uploadId, $position)
    {
        $url_append = self::FILES_URL . 'files/upload_session/append_v2';
        $url_finish = self::FILES_URL . 'files/upload_session/finish';
        $header = ['Content-Type: application/octet-stream'];
        $byte_offset = $position;
        $byte_to_go = $total_file_size - ($byte_offset + $content_size);

        if (!empty($this->team_member_id)) {
            $header[] = 'Dropbox-API-Select-Admin: ' . $this->team_member_id;
        }

        if ($byte_to_go > 0) {
            $datas_header = [
                'cursor' => [
                    'session_id' => $uploadId,
                    'offset' => $byte_offset,
                ],
            ];

            $header[] = 'Dropbox-API-Arg: ' . json_encode($datas_header);
            $result = $this->apiPost($url_append, $content, $header);

            return $result['success'];
        } else {
            $datas_header = [
                'cursor' => [
                    'session_id' => $uploadId,
                    'offset' => $byte_offset,
                ],
                'commit' => [
                    'path' => $file_destination,
                    'mode' => 'add',
                    'autorename' => true,
                ],
            ];

            $header[] = 'Dropbox-API-Arg: ' . json_encode($datas_header);
            $result_commit = $this->apiPost($url_finish, $content, $header);

            $this->ntbr->old_percent = 0;

            return $result_commit['success'];
        }

        return false;
    }

    /**
     * Delete a file on the Dropbox account
     *
     * @param string $file_path the path of the file on Dropbox
     *
     * @return bool the success or failure of the action
     */
    public function deleteFile($file_path)
    {
        if ($file_path != '' && $file_path[0] != '/') {
            $file_path = '/' . $file_path;
        } elseif ($file_path == '/') {
            $file_path = ''; // Root folder should be an empty string rather than a "/"
        }

        $datas = [
            'path' => $file_path,
        ];

        $header = ['Content-Type: application/json'];

        if (!empty($this->team_member_id)) {
            $header[] = 'Dropbox-API-Select-Admin: ' . $this->team_member_id;
        }

        $result = $this->apiPost(self::API_URL . 'files/delete', json_encode($datas), $header);

        return $result['success'];
    }

    /**
     * Create a folder in the Dropbox account
     *
     * @param string $folder_path the path of the folder on Dropbox
     *
     * @return bool the success or failure of the action
     */
    public function createFolder($folder_path)
    {
        if ($folder_path == '') {
            return true; // Root directory, no creation needed
        }

        $datas = [
            'path' => $folder_path,
        ];

        $header = ['Content-Type: application/json'];

        if (!empty($this->team_member_id)) {
            $header[] = 'Dropbox-API-Select-Admin: ' . $this->team_member_id;
        }

        $result = $this->apiPost(self::API_URL . 'files/create_folder_v2', json_encode($datas), $header);

        return $result['success'];
    }

    /**
     * Check if a file or folder exists on the Dropbox account
     *
     * @param string $item_path the path of the file or folder on Dropbox
     *
     * @return bool if the item exists
     */
    public function checkExists($item_path = '')
    {
        if (!isset($item_path[0]) || $item_path[0] !== '/') {
            $item_path = '/' . $item_path;
        }

        $datas = [
            'path' => $item_path,
        ];

        if (!empty($this->team_member_id)) {
            $datas = [
                'path' => 'ns:' . $this->getTeamMemberFolderId() . $item_path,
            ];
        }

        $header = ['Content-Type: application/json'];

        if (!empty($this->team_member_id)) {
            $header[] = 'Dropbox-API-Select-Admin: ' . $this->team_member_id;
        }

        $result = $this->apiPost(self::API_URL . 'files/get_metadata', json_encode($datas), $header);

        if ($result['success'] === false) {
            return false;
        }

        // If the result is not file or folder then we did not found what we were looking for
        if (isset($result['result']['.tag'])) {
            if (!in_array($result['result']['.tag'], ['file', 'folder'])) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Get the children of a folder on the Dropbox account
     *
     * @param string $item_path the path of the folder on Dropbox
     *
     * @return bool|array the children of the folder or the failure of the action
     */
    public function listFolderChildren($item_path = '')
    {
        if ($item_path != '' && $item_path[0] != '/') {
            $item_path = '/' . $item_path;
        } elseif ($item_path == '/') {
            $item_path = ''; // Root folder should be an empty string rather than a "/"
        }

        $datas = [
            'path' => $item_path,
            'recursive' => true,
        ];

        $header = ['Content-Type: application/json'];

        if (!empty($this->team_member_id)) {
            $header[] = 'Dropbox-API-Select-Admin: ' . $this->team_member_id;
        }

        $result = $this->apiPost(self::API_URL . 'files/list_folder', json_encode($datas), $header);

        if ($result['success'] === false) {
            return false;
        }

        if (isset($result['result']['has_more'], $result['result']['entries'], $result['result']['cursor']) && $result['result']['has_more']) {
            $result['result']['entries'] = array_merge($result['result']['entries'], $this->listRestFolderChildren($result['result']['cursor']));
        }

        return (array) $result['result'];
    }

    /**
     * Get the rest of the children of a folder on the Dropbox account
     *
     * @param string $cursor The cursor return by Dropbox from the request to list files
     *
     * @return bool|array the children of the folder or the failure of the action
     */
    public function listRestFolderChildren($cursor)
    {
        $datas = [
            'cursor' => $cursor,
        ];

        $header = ['Content-Type: application/json'];

        if (!empty($this->team_member_id)) {
            $header[] = 'Dropbox-API-Select-Admin: ' . $this->team_member_id;
        }

        $result = $this->apiPost(self::API_URL . 'files/list_folder/continue', json_encode($datas), $header);

        if ($result['success'] === false) {
            return false;
        }

        if (!isset($result['result']['entries'])) {
            return [];
        }

        if (isset($result['result']['has_more'], $result['result']['entries'], $result['result']['cursor']) && $result['result']['has_more']) {
            $result['result']['entries'] = array_merge($result['result']['entries'], $this->listRestFolderChildren($result['result']['cursor']));
        }

        return (array) $result['result']['entries'];
    }

    /**
     * Download a file
     *
     * @param string $file_id The ID (or the path) of the file to download
     *
     * @return bool|array infos of the file to download or the failure of the action
     */
    public function downloadFile($file_id)
    {
        $datas = [
            'path' => $file_id,
        ];

        $header = ['Content-Type: application/json'];

        if (!empty($this->team_member_id)) {
            $header[] = 'Dropbox-API-Select-User: ' . $this->team_member_id;
        }

        $result = $this->apiPost(self::API_URL . 'files/get_temporary_link', json_encode($datas), $header);

        if ($result['success'] === false) {
            return false;
        }

        return (array) $result['result'];
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
