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
class GoogleCloudLib
{
    const PAGE = 'googlecloud';

    /**
     * @var int The maximal size of a file to upload
     */
    const MAX_FILE_UPLOAD_SIZE = 10485760; // 10Mo (10 * 1024 * 1024 = 10 485 760)

    /**
     * @var string the base URL for API requests
     */
    const API_URL = 'https://storage.googleapis.com/storage/v1/b/';

    /**
     * @var string the upload URL for authorization requests
     */
    const API_UPLOAD = 'https://storage.googleapis.com/upload/storage/v1/b/';

    /**
     * @var string the base URL for authorization requests
     */
    const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    /**
     * @var string the base URL for token requests
     */
    const TOKEN_URL = 'https://www.googleapis.com/oauth2/v4/token';

    const REDIRECT_URI = 'https://oauth.2n-tech.com/get_oauth_code.php';
    const API_SCOPE = 'https://www.googleapis.com/auth/devstorage.full_control';
    const PROJECT_ID = 'ntbr-cloudstorage';
    const CLIENT_ID = '730815118241-jq8ovebeqqhk647lq20gph3v6e2uvbn8.apps.googleusercontent.com';
    const CLIENT_SECRET = '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';

    const STORAGE_CLASS_STANDARD = 'STANDARD';
    const STORAGE_CLASS_NEARLINE = 'NEARLINE';
    const STORAGE_CLASS_COLDLINE = 'COLDLINE';
    const STORAGE_CLASS_ARCHIVE = 'ARCHIVE';

    // The current token
    private $token;
    // The bucket
    private $bucket;
    // The sdk uri
    private $sdk_uri;
    // The physic sdk uri
    private $physic_sdk_uri;
    // Instance of NtbrCore
    private $ntbr;

    public function __construct($ntbr, $sdk_uri, $physic_sdk_uri, $bucket = '', $token = '')
    {
        $this->bucket = $bucket;
        $this->sdk_uri = $sdk_uri;
        $this->physic_sdk_uri = $physic_sdk_uri;
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
        return self::API_URL . $this->bucket . '/o';
    }

    /**
     * Get the url for upload requests.
     *
     * @return string url
     */
    public function getUploadUrl()
    {
        return self::API_UPLOAD . $this->bucket . '/o';
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
     * Performs a call to the API using the POST method.
     *
     * @param string $url the path of the API call
     * @param string $data the data to upload
     * @param array $params parameters of the API call
     * @param array $headers headers of the API call
     * @param float $filesize The size of the string
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
     * Performs a call to the Google Drive API using the PUT method.
     *
     * @param string $url the path of the API call
     * @param string $stream the data to upload
     * @param array $header the data to pass in the header of the request
     *
     * @return array the result of the execution of the curl
     */
    public function apiPut($url, $stream, $header = [])
    {
        $curl = $this->createCurl([], $header);

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $stream,
            CURLOPT_HEADER => true,
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
            . '?response_type=code'
            . '&prompt=consent'
            . '&redirect_uri=' . self::REDIRECT_URI
            . '&client_id=' . self::CLIENT_ID
            . '&scope=https://www.googleapis.com/auth/devstorage.full_control'
            . '&access_type=offline';

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
            . '&redirect_uri=' . self::REDIRECT_URI
            . '&client_id=' . self::CLIENT_ID
            . '&client_secret=' . self::CLIENT_SECRET
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
        $datas = 'client_id=' . self::CLIENT_ID
            . '&client_secret=' . self::CLIENT_SECRET
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
     * Test the connection
     *
     * @return bool Connection result
     */
    public function testConnection()
    {
        $params = [
            'list-type' => '2',
            'delimiter' => '/',
        ];

        $result = $this->apiGet($this->getUrl() . $this->convertParams($params));

        return $result['success'];
    }

    /**
     * Fetches the files
     *
     * @param string $path the path to look into
     *
     * @return array|bool list of files or false
     */
    public function getListFiles($path = '', $next_continuation_token = '')
    {
        $files = [];

        $params = [
            'delimiter' => '/',
        ];

        if ($path && $path != '') {
            $params['prefix'] = $path;
        }

        if ($next_continuation_token && $next_continuation_token != '') {
            $params['pageToken'] = $next_continuation_token;
        }

        $result = $this->apiGet($this->getUrl() . $this->convertParams($params));

        if (!$result['success'] || !isset($result['result']['items'])) {
            return false;
        }

        if (is_array($result['result']['items'])) {
            foreach ($result['result']['items'] as $item) {
                // ignore directories
                if (substr($item['name'], -1) == '/') {
                    continue;
                }

                $name = preg_replace('/' . str_replace('/', '\/', preg_quote(utf8_decode($path))) . '/', '', $item['name'], 1); // Remove directory name

                $files[] = [
                    'name' => $name,
                    'size' => $item['size'],
                    'id' => $item['id'],
                ];
            }
        }

        if (isset($result['result']['nextPageToken']) && $result['result']['nextPageToken'] != '') {
            $next_files = $this->getListFiles($path, $result['result']['nextPageToken']);
            $files = array_merge($files, $next_files);
        }

        return $files;
    }

    /**
     * Fetches the directories
     *
     * @param string $path the path to look into
     *
     * @return array|bool list of directories or false
     */
    public function getListDirectories($path = '', $next_continuation_token = '')
    {
        $directories = [];

        $params = [];

        if ($path && $path != '') {
            $params['prefix'] = $path;
        }

        if ($next_continuation_token && $next_continuation_token != '') {
            $params['pageToken'] = $next_continuation_token;
        }

        $result = $this->apiGet($this->getUrl() . $this->convertParams($params));

        if (!$result['success'] || !isset($result['result']['items'])) {
            return false;
        }

        if (is_array($result['result']['items'])) {
            foreach ($result['result']['items'] as $item) {
                // ignore files and ignore current path (we only want what is inside this path)
                if (substr($item['name'], -1) != '/' || $path == $item['name']) {
                    continue;
                }

                $name = substr(preg_replace('/' . str_replace('/', '\/', preg_quote(utf8_decode($path))) . '/', '', $item['name'], 1), 0, -1); // Remove the last character ("/") so we only keep the directory name
                $exp_dir = array_reverse(explode('/', $name));
                $tmp = [];

                foreach ($exp_dir as $k => $ed) {
                    $sub = [];

                    if (count($tmp)) {
                        $sub = $tmp;
                    }

                    if (!isset($tmp[$ed])) {
                        $tmp[$ed] = [
                            'name' => $ed,
                            'subdir' => $sub,
                        ];
                    } else {
                        $tmp[$ed] = array_merge($tmp[$ed], [
                            'name' => $ed,
                            'subdir' => $sub,
                        ]);
                    }

                    if ($k == 0) {
                        $tmp[$ed]['id'] = $item['id'];
                        $tmp[$ed]['path'] = $item['name'];
                    }

                    if (isset($exp_dir[$k - 1])) {
                        unset($tmp[$exp_dir[$k - 1]]);
                    }
                }

                $directories = array_replace_recursive($directories, $tmp);
            }
        }

        if (isset($result['result']['nextPageToken']) && $result['result']['nextPageToken'] != '') {
            $next_directories = $this->getListDirectories($path, $result['result']['nextPageToken']);
            $directories = array_merge($directories, $next_directories);
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
        $result = $this->apiGet($this->getUrl() . '/' . rawurlencode((string) $item_path));

        if (!$result['success']) {
            return false;
        }

        return $result['success'];
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
        $result = $this->apiDelete($this->getUrl() . '/' . rawurlencode((string) $item_path));

        if (!$result['success']) {
            return false;
        }

        return $result['success'];
    }

    /**
     * Create a resumable session
     *
     * @param string $path_destination the destination of the file
     * @param string $mime_type the mime of the file
     * @param int $total_file_size the total size of the file
     *
     * @return bool the success or failure of the action
     */
    public function createSession($path_destination, $mime_type, $total_file_size)
    {
        $headers = [
            'Content-Type: application/json',
            'X-Upload-Content-Length: ' . $total_file_size,
            'X-Upload-Content-Type: ' . $mime_type,
        ];

        $options = [
            CURLOPT_HEADER => true,
        ];

        $datas = '?uploadType=resumable&name=' . rawurlencode((string) $path_destination);

        $result_create_session = $this->apiPost($this->getUploadUrl() . $datas, '', $headers, $options);

        if (!$result_create_session['success']) {
            return false;
        }

        $matches = [];

        preg_match('/Location:(.*?)\n/i', $result_create_session['result'], $matches);

        if (!isset($matches[1])) {
            return false;
        }

        $this->ntbr->googlecloud_session = trim($matches[1]);
        $this->ntbr->googlecloud_position = 0;

        return true;
    }

    /**
     * Upload a file on the Google Cloud account
     *
     * @param string $file_path the path of the file
     * @param string $path_destination the destination of the file
     * @param int $nb_part current part number
     * @param int $nb_part_total total parts to be sent
     *
     * @return bool the success or failure of the action
     */
    public function uploadFile($file_path, $path_destination, $nb_part = 1, $nb_part_total = 1)
    {
        $total_file_size = $this->ntbr->getFileSize($file_path);

        $mime_type = NtbrChild::getMimeType($file_path);

        if ($mime_type == '') {
            return false;
        }

        $this->createSession($path_destination, $mime_type, $total_file_size);

        return $this->resumeUploadFile($file_path, $nb_part, $nb_part_total);
    }

    /**
     * Resume the upload a file on the Google Cloud account
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

        if ($this->ntbr->googlecloud_position > 0) {
            $byte_start = $this->ntbr->googlecloud_position; // Next chunk
        }

        if ($total_file_size > self::MAX_FILE_UPLOAD_SIZE) {
            $content_length = self::MAX_FILE_UPLOAD_SIZE;
        }

        $byte_end = $byte_start + $content_length - 1;

        if ($byte_end > $total_file_size) {
            $byte_end = $total_file_size - 1;
            $content_length = $byte_end - $byte_start + 1;
        }

        $byte_to_go = $total_file_size - $this->ntbr->googlecloud_position;

        $file = fopen($file_path, 'r+');

        if ($this->ntbr->googlecloud_position > 0) {
            $file = $this->ntbr->goToPositionInFile($file, $this->ntbr->googlecloud_position, false);

            if ($file === false) {
                return false;
            }
        }

        while ($byte_to_go > 0) {
            $header = [
                'Content-Length: ' . $content_length,
                'Content-Range: bytes ' . $byte_start . '-' . $byte_end . '/' . $total_file_size,
            ];

            $datas = fread($file, $content_length);

            $percent = ($byte_end / $total_file_size) * 100;

            if ($nb_part_total > 1) {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::GOOGLECLOUD)
                    . ' ' . $nb_part . '/' . $nb_part_total . $this->ntbr->l(':', self::PAGE) . ' ' . (int) $percent . '%'
                );
            } else {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::GOOGLECLOUD)
                    . ' ' . (int) $percent . '%'
                );
            }

            $result_upload = $this->apiPut($this->ntbr->googlecloud_session, $datas, $header);

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
            $this->ntbr->googlecloud_position = $byte_start;
            $byte_end = $byte_start + $content_length - 1;

            // refresh
            $this->ntbr->refreshBackup(true);
        }

        fclose($file);

        return $result_upload['success'];
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

        $position = $this->ntbr->googlecloud_position;
        $byte_start = $position;
        $byte_end = $byte_start + $content_size - 1;
        $byte_to_go = $total_file_size - $position;

        if ($byte_to_go > 0) {
            $header = [
                'Content-Length: ' . $content_size,
                'Content-Range: bytes ' . $byte_start . '-' . $byte_end . '/' . $total_file_size,
            ];

            $result_upload = $this->apiPut($this->ntbr->googlecloud_session, $content, $header);

            if (!$result_upload['success'] || !$result_upload['result']) {
                return -1;
            }

            // Search number of bytes really sent
            $matches = [];
            preg_match('/Range: bytes=\d+-(?<byte_end>\d+)/i', $result_upload['result'], $matches);

            // If everything was not uploaded, we need to know it
            if (isset($matches['byte_end']) && $byte_end != $matches['byte_end']) {
                $byte_end = $matches['byte_end'];
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
