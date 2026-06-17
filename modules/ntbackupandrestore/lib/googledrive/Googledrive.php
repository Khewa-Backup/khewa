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
require_once 'googledrive-config.php';

class GoogledriveLib
{
    const PAGE = 'googledrive';

    /**
     * @var string the base URL for authorization requests
     */
    const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    /**
     * @var string the base URL for token requests
     */
    const TOKEN_URL = 'https://www.googleapis.com/oauth2/v4/token';

    /**
     * @var string the base URL for API requests
     */
    const API_URL = 'https://www.googleapis.com/drive/v3/';

    /**
     * @var string the base URL for API requests
     */
    const FILES_URL = 'https://www.googleapis.com/upload/drive/v3/';

    /**
     * @var int The maximal size of the log file
     */
    const MAX_FILE_LOG_SIZE = 200000000; // environs 200Mo (200 000 000)

    /**
     * @var int The maximal size sent to the Google Drive account
     */
    const MAX_FILE_UPLOAD_SIZE = 26214400; // 26,2144Mo (256 * 1024 * 100 = 26 214 400) ! Must be "multiples of 256 KB (256 x 1024 = 262 144 bytes) in size"

    /**
     * @var string The name of the current log file
     */
    const CURRENT_FILE_LOG_NAME = 'current_log_googledrive.txt';

    /**
     * @var string The name of old log file
     */
    const OLD_FILE_LOG_NAME = '_log_googledrive.txt';

    /**
     * View and manage the files in your Google Drive.
     */
    const DRIVE = 'https://www.googleapis.com/auth/drive';

    /**
     * View and manage its own configuration data in your Google Drive.
     */
    const DRIVE_APPDATA = 'https://www.googleapis.com/auth/drive.appdata';

    /**
     * View and manage Google Drive files and folders that you have opened or created with this app.
     */
    const DRIVE_FILE = 'https://www.googleapis.com/auth/drive.file';

    /**
     * View and manage metadata of files in your Google Drive.
     */
    const DRIVE_METADATA = 'https://www.googleapis.com/auth/drive.metadata';

    /**
     * View metadata for files in your Google Drive.
     */
    const DRIVE_METADATA_READONLY = 'https://www.googleapis.com/auth/drive.metadata.readonly';

    /**
     * View the photos, videos and albums in your Google Photos.
     */
    const DRIVE_PHOTOS_READONLY = 'https://www.googleapis.com/auth/drive.photos.readonly';

    /**
     * View the files in your Google Drive.
     */
    const DRIVE_READONLY = 'https://www.googleapis.com/auth/drive.readonly';

    /**
     * Modify your Google Apps Script scripts' behavior.
     */
    const DRIVE_SCRIPTS = 'https://www.googleapis.com/auth/drive.scripts';

    // The current token
    private $token;
    // The current access_right
    private $access_right;
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
    // The access type
    private $access_type;
    // Instance of NtbrCore
    private $ntbr;

    public function __construct($ntbr, $access_right, $sdk_uri, $physic_sdk_uri, $token = '')
    {
        if (!empty($token)) {
            $this->token = $token;
        }

        $valid_access_right = [
            self::DRIVE,
            self::DRIVE_APPDATA,
            self::DRIVE_FILE,
            self::DRIVE_METADATA,
            self::DRIVE_METADATA_READONLY,
            self::DRIVE_PHOTOS_READONLY,
            self::DRIVE_READONLY,
            self::DRIVE_SCRIPTS,
        ];

        if (empty($access_right) || !in_array($access_right, $valid_access_right)) {
            $this->access_right = self::DRIVE_READONLY;
        } else {
            $this->access_right = $access_right;
        }

        $googledrive_client_id = GlobConfNtbr::get('NTBR_GOOGLEDRIVE_CLIENT_ID');
        $googledrive_client_secret = GlobConfNtbr::get('NTBR_GOOGLEDRIVE_CLIENT_SECRET');

        if ($googledrive_client_id && trim($googledrive_client_id) != '') {
            $this->client_id = Apparatus::decrypt($googledrive_client_id);

            if (!is_string($this->client_id)) {
                $this->client_id = GOOGLEDRIVE_CLIENT_ID;
            }
        } else {
            $this->client_id = GOOGLEDRIVE_CLIENT_ID;
        }

        if ($googledrive_client_secret && trim($googledrive_client_secret) != '') {
            $this->client_secret = Apparatus::decrypt($googledrive_client_secret);

            if (!is_string($this->client_secret)) {
                $this->client_secret = GOOGLEDRIVE_CLIENT_SECRET;
            }
        } else {
            $this->client_secret = GOOGLEDRIVE_CLIENT_SECRET;
        }

        $this->sdk_uri = $sdk_uri;
        $this->physic_sdk_uri = $physic_sdk_uri;
        $this->redirect_uri = GOOGLEDRIVE_CALLBACK_URI;
        $this->access_type = GOOGLEDRIVE_ACCESS_TYPE;
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
     * Performs a call to the Google Drive API using the POST method.
     *
     * @param string $url the url of the API call
     * @param array|object $data the data to pass in the body of the request
     * @param array $header the data to pass in the header of the request
     *
     * @return array the result of the execution of the curl
     */
    public function apiPost($url, $data = [], $header = [], $add_options = [])
    {
        $header[] = 'Content-Type: application/x-www-form-urlencoded';

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
     * Performs a call to the Google Drive API using the GET method.
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
     * Performs a call to the Google Drive API using the PUT method.
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
        // $header[] = 'Content-Type: application/octet-stream';

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
     * Performs a call to the Google Drive API using the DELETE method.
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
            . '&prompt=consent'
            . '&redirect_uri=' . $this->redirect_uri
            . '&client_id=' . $this->client_id
            . '&scope=' . $this->access_right
            . '&access_type=' . $this->access_type;

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
            . '&scope='
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
     * Get the available quota of the current Google Drive account.
     *
     * @return int Available quota
     */
    public function getAvailableQuota()
    {
        $result = $this->apiGet(self::API_URL . 'about?fields=storageQuota');

        $quota_available = 0;
        $quota_total = 0;

        if ($result['success'] && !empty($result['result'])) {
            $quota_infos = $result['result']['storageQuota'];

            if (isset($quota_infos['limit'])) {
                $quota_total = $quota_infos['limit'];
                $quota_available = $quota_infos['limit'] - $quota_infos['usage'];
            } else {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account: No limit', self::PAGE), NtbrChild::GOOGLEDRIVE)
                );

                return '-1';
            }
        }

        $this->log(
            sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::GOOGLEDRIVE)
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
        $result = $this->apiGet(self::API_URL . 'about?fields=storageQuota');

        return $result['success'];
    }

    /**
     * Get the folders tree of the current Google Drive account.
     *
     * @param string $id_parent
     *
     * @return array folders tree
     */
    public function getChildrenTree($id_parent)
    {
        $childrens = [];

        $datas = '?q=';
        $datas .= urlencode('mimeType=') . "'" . urlencode('application/vnd.google-apps.folder') . "'"; // folder
        $datas .= urlencode(' and ') . "'" . $id_parent . "'" . urlencode(' in parents'); // with $id_parent as parent
        $datas .= urlencode(' and trashed = false'); // Not in the trash

        $result = $this->apiGet(self::API_URL . 'files' . $datas);

        if ($result['success'] && !empty($result['result'])) {
            if (isset($result['result']['files'])) {
                $childrens = $result['result']['files'];
            }
        }

        return $childrens;
    }

    /**
     * Get the children files of the current Google Drive account.
     *
     * @param string $id_parent
     *
     * @return array files
     */
    public function getChildrenFiles($id_parent)
    {
        $childrens = [];

        $datas = '?q=';
        $datas .= urlencode('mimeType!=') . "'" . urlencode('application/vnd.google-apps.folder') . "'"; // not folder
        $datas .= urlencode(' and ') . "'" . $id_parent . "'" . urlencode(' in parents'); // with $id_parent as parent
        $datas .= urlencode(' and trashed = false'); // Not in the trash
        $datas .= '&fields=' . urlencode('files(name,id,size)'); // Get file size too

        $result = $this->apiGet(self::API_URL . 'files' . $datas);

        if ($result['success'] && !empty($result['result'])) {
            if (isset($result['result']['files'])) {
                $childrens = $result['result']['files'];
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
        $datas = '?fields=' . urlencode('webContentLink'); // Get file download link

        $result = $this->apiGet(self::API_URL . 'files/' . $id_file . $datas);

        if ($result['success'] && !empty($result['result'])) {
            if (isset($result['result']['webContentLink'])) {
                return $result['result']['webContentLink'];
            }
        }

        return '';
    }

    /**
     * Check if file exists in Google Drive account and if so return the file ID.
     *
     * @param string $filename
     * @param string $id_parent
     *
     * @return string|bool Id of the file or false if not found
     */
    public function checkExists($filename, $id_parent)
    {
        $datas = '?q=';
        $datas .= urlencode('name=') . "'" . $filename . "'"; // name of the file
        $datas .= urlencode(' and ') . "'" . $id_parent . "'" . urlencode(' in parents'); // with $id_parent as parent
        $datas .= urlencode(' and trashed = false'); // Not in the trash

        $result = $this->apiGet(self::API_URL . 'files' . $datas);

        if ($result['success'] && !empty($result['result'])) {
            if (isset($result['result']['files'][0]['id'])) {
                return $result['result']['files'][0]['id'];
            }
        }

        return false;
    }

    /**
     * Delete a file on the Google Drive account
     *
     * @param string $id_file the path of the file on Google Drive
     *
     * @return bool the success or failure of the action
     */
    public function deleteFile($id_file)
    {
        $result = $this->apiDelete(self::API_URL . 'files/' . $id_file);

        return $result['success'];
    }

    /**
     * Upload a file on the Google Drive account
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

        $mime_type = NtbrChild::getMimeType($file_path);

        if ($mime_type == '') {
            return false;
        }

        $this->ntbr->googledrive_mime_type = $mime_type;

        $headers = [
            'Content-Type: application/json',
            'X-Upload-Content-Length: ' . $total_file_size,
            'X-Upload-Content-Type: ' . $mime_type,
        ];

        $options = [
            CURLOPT_HEADER => true,
        ];

        $datas =
            '{
                "name": "' . $name . '",
                "parents": [
                "' . $id_file_destination . '"
                ]
            }'
        ;

        $result_create_session = $this->apiPost(self::FILES_URL . 'files?uploadType=resumable', $datas, $headers, $options);

        if (!$result_create_session['success']) {
            return false;
        }

        $matches = [];

        list($header, $body) = explode("\r\n\r\n", $result_create_session['result'], 2);

        preg_match('/Location:(.*?)\n/i', $header, $matches);

        if (!isset($matches[1])) {
            return false;
        }

        $session = trim($matches[1]);
        $this->ntbr->googledrive_session = $session;
        $this->ntbr->googledrive_position = 0;

        return $this->resumeUploadFile($file_path, $nb_part, $nb_part_total);
    }

    /**
     * Resume the upload a file on the Google Drive account
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

        if ($this->ntbr->googledrive_position > 0) {
            $byte_start = $this->ntbr->googledrive_position; // Next chunk
        }

        if ($total_file_size > self::MAX_FILE_UPLOAD_SIZE) {
            $content_length = self::MAX_FILE_UPLOAD_SIZE;
        }

        $byte_end = $byte_start + $content_length - 1;

        if ($byte_end > $total_file_size) {
            $byte_end = $total_file_size - 1;
            $content_length = $byte_end - $byte_start + 1;
        }

        $byte_to_go = $total_file_size - $this->ntbr->googledrive_position;

        $file = fopen($file_path, 'r+');

        if ($this->ntbr->googledrive_position > 0) {
            $file = $this->ntbr->goToPositionInFile($file, $this->ntbr->googledrive_position, false);

            if ($file === false) {
                return false;
            }
        }

        while ($byte_to_go > 0) {
            $header = [
                'Content-Length: ' . $content_length,
                'Content-Type: ' . $this->ntbr->googledrive_mime_type,
                'Content-Range: bytes ' . $byte_start . '-' . $byte_end . '/' . $total_file_size,
            ];

            $datas = fread($file, $content_length);

            $percent = ($byte_end / $total_file_size) * 100;

            if ($nb_part_total > 1) {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::GOOGLEDRIVE)
                    . ' ' . $nb_part . '/' . $nb_part_total . $this->ntbr->l(':', self::PAGE) . ' ' . (int) $percent . '%'
                );
            } else {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::GOOGLEDRIVE)
                    . ' ' . (int) $percent . '%'
                );
            }

            $result_upload = $this->apiPut($this->ntbr->googledrive_session, $datas, $header, $total_file_size);

            if (!$result_upload['success'] || !$result_upload['result']) {
                fclose($file);

                return false;
            }

            // Search number of bytes really sent
            $matches = [];
            preg_match('/Range: bytes=\d+-(?<byte_end>\d+)/i', $result_upload['result'], $matches);

            if (isset($matches['byte_end']) && $byte_end != $matches['byte_end']) {
                $byte_end = $matches['byte_end'];
                $content_length = $byte_end - $byte_start + 1;

                $file = $this->ntbr->goToPositionInFile($file, $byte_end + 1, false);

                if ($file === false) {
                    return false;
                }
            }

            $byte_to_go -= $content_length;

            if ($byte_to_go < self::MAX_FILE_UPLOAD_SIZE) {
                $content_length = $byte_to_go;
            }

            $byte_start = $byte_end + 1;
            $this->ntbr->googledrive_position = $byte_start;
            $byte_end = $byte_start + $content_length - 1;

            // refresh
            $this->ntbr->refreshBackup(true);
        }

        fclose($file);

        return $result_upload['success'];
    }

    /**
     * Create a resumable session
     *
     * @param string $id_file_destination the destination of the file
     * @param string $name the name of the file
     * @param string $mime_type the mime of the file
     * @param int $total_file_size the total size of the file
     *
     * @return bool the success or failure of the action
     */
    public function createSession($id_file_destination, $name, $mime_type, $total_file_size)
    {
        $this->ntbr->googledrive_mime_type = $mime_type;

        $headers = [
            'Content-Type: application/json',
            'X-Upload-Content-Length: ' . $total_file_size,
            'X-Upload-Content-Type: ' . $mime_type,
        ];

        $options = [
            CURLOPT_HEADER => true,
        ];

        $datas =
            '{
                "name": "' . $name . '",
                "parents": [
                "' . $id_file_destination . '"
                ]
            }'
        ;

        $result_create_session = $this->apiPost(self::FILES_URL . 'files?uploadType=resumable', $datas, $headers, $options);

        if (!$result_create_session['success']) {
            return false;
        }

        $matches = [];

        list($header, $body) = explode("\r\n\r\n", $result_create_session['result'], 2);

        preg_match('/Location:(.*?)\n/i', $header, $matches);

        if (!isset($matches[1])) {
            return false;
        }

        $this->ntbr->googledrive_session = trim($matches[1]);
        $this->ntbr->googledrive_position = 0;

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

        $position = $this->ntbr->googledrive_position;
        $byte_start = $position;
        $byte_end = $byte_start + $content_size - 1;
        $byte_to_go = $total_file_size - $position;

        if ($byte_to_go > 0) {
            $header = [
                'Content-Length: ' . $content_size,
                'Content-Type: ' . $this->ntbr->googledrive_mime_type,
                'Content-Range: bytes ' . $byte_start . '-' . $byte_end . '/' . $total_file_size,
            ];

            $result_upload = $this->apiPut($this->ntbr->googledrive_session, $content, $header, $total_file_size);

            // $this->log($header, true);
            // $this->log($result_upload, true);

            if (!$result_upload['success'] || !$result_upload['result']) {
                return -1;
            }

            // Search number of bytes really sent
            $matches = [];
            preg_match('/Range: bytes=\d+-(?<byte_end>\d+)/i', $result_upload['result'], $matches);

            // If everything was not uploaded, we need to know it
            if (isset($matches['byte_end']) && $byte_end != $matches['byte_end']) {
                $byte_end = $matches['byte_end'];
                // $this->log('Change byte_end', true);
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
