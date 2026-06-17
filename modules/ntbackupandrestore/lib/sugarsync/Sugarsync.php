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
require_once 'sugarsync-config.php';

class SugarsyncLib
{
    const PAGE = 'sugarsync';

    /**
     * @var string The url to get the refresh token
     */
    const REFRESH_TOKEN_URL = 'https://api.sugarsync.com/app-authorization';

    /**
     * @var string The url to get the access token
     */
    const ACCESS_TOKEN_URL = 'https://api.sugarsync.com/authorization';

    /**
     * @var string The url to get user informations
     */
    const INFOS_USER_URL = 'https://api.sugarsync.com/user';

    /**
     * @var string The url to get directory informations
     */
    const DIRECTORY_URL = 'https://api.sugarsync.com/folder';

    /**
     * @var string The url to get manage files
     */
    const FILE_URL = 'https://api.sugarsync.com/file';

    /**
     * @var int The maximal size of a file to upload
     */
    // const MAX_FILE_UPLOAD_SIZE = 1048576; // 1Mo (1 * 1024 * 1024 = 1 048 576)
    const MAX_FILE_UPLOAD_SIZE = 5242880; // 5Mo (5 * 1024 * 1024 = 5 242 880)
    // const MAX_FILE_UPLOAD_SIZE = 10485760; // 10Mo (10 * 1024 * 1024 = 10 485 760)

    // The current token
    private $token;
    // The current user
    private $user;
    // The app id
    private $app_id;
    // The access key id
    private $access_key_id;
    // The private access key
    private $private_access_key;
    // The sdk uri
    private $sdk_uri;
    // The physic sdk uri
    private $physic_sdk_uri;
    // Instance of NtbrCore
    private $ntbr;

    // Current part to upload
    private $part;

    // Total part to upload
    private $total_part;

    // Current downloaded percent
    private $percent;

    public function __construct($ntbr, $sdk_uri, $physic_sdk_uri, $token = '', $user = '')
    {
        $this->app_id = SUGARSYNC_APP_ID;
        $this->access_key_id = SUGARSYNC_ACCESS_KEY_ID;
        $this->private_access_key = SUGARSYNC_PRIVATE_ACCESS_KEY;
        $this->sdk_uri = $sdk_uri;
        $this->physic_sdk_uri = $physic_sdk_uri;
        $this->ntbr = $ntbr;

        if (!empty($token)) {
            $this->token = $token;
        }

        if (!empty($user)) {
            $this->user = $user;
        }
    }

    /**
     * Gets the refresh token
     *
     * @param string $login User login
     * @param string $password User password
     *
     * @return string The refresh token
     */
    public function getRefreshToken($login, $password)
    {
        $data = '<?xml version="1.0" encoding="UTF-8" ?>';
        $data .= '<appAuthorization>';
        $data .= '<username>' . mb_convert_encoding($login, 'UTF-8') . '</username>';
        $data .= '<password>' . mb_convert_encoding($password, 'UTF-8') . '</password>';
        $data .= '<application>' . SUGARSYNC_APP_ID . '</application>';
        $data .= '<accessKeyId>' . SUGARSYNC_ACCESS_KEY_ID . '</accessKeyId>';
        $data .= '<privateAccessKey>' . SUGARSYNC_PRIVATE_ACCESS_KEY . '</privateAccessKey>';
        $data .= '</appAuthorization>';

        $result = $this->apiPost(self::REFRESH_TOKEN_URL, $data);

        if (!$result['success'] || !is_string($result['result'])) {
            return false;
        }

        $matches = [];

        preg_match('/Location:(.*?)\n/i', $result['result'], $matches);

        if (!isset($matches[1])) {
            return false;
        }

        $refresh_token = trim($matches[1]);

        return $refresh_token;
    }

    /**
     * Gets the access token
     *
     * @param string $refresh_token refresh token
     *
     * @return string The access token
     */
    public function getAccessToken($refresh_token)
    {
        $data = '<?xml version="1.0" encoding="UTF-8" ?>';
        $data .= '<tokenAuthRequest>';
        $data .= '<accessKeyId>' . SUGARSYNC_ACCESS_KEY_ID . '</accessKeyId>';
        $data .= '<privateAccessKey>' . SUGARSYNC_PRIVATE_ACCESS_KEY . '</privateAccessKey>';
        $data .= '<refreshToken>' . $refresh_token . '</refreshToken>';
        $data .= '</tokenAuthRequest>';

        $result = $this->apiPost(self::ACCESS_TOKEN_URL, $data);

        if (!$result['success']
            || !is_string($result['result']['header'])
            || !isset($result['result']['body']['expiration'])
            || !isset($result['result']['body']['user'])
        ) {
            return false;
        }

        $matches = [];

        preg_match('/Location:(.*?)\n/i', $result['result']['header'], $matches);

        if (!isset($matches[1])) {
            return false;
        }

        $access_token = trim($matches[1]);
        $expire_in = date('Y-m-d H:i:s', strtotime($result['result']['body']['expiration']));
        $user = str_replace(self::INFOS_USER_URL . '/', '', $result['result']['body']['user']);

        $this->token = $access_token;
        $this->user = $user;

        $return = [
            'access_token' => $access_token,
            'expire_in' => $expire_in,
            'user' => $user,
        ];

        return $return;
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
            $curl_header[] = 'Authorization: ' . $this->token;
        }

        $curl_default_options = [
            CURLOPT_HTTPHEADER => $curl_header,
            CURLOPT_HEADER => true,
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
        return $this->ntbr->execCurl($curl, true);
    }

    /**
     * Performs a call to the API using the POST method.
     *
     * @param string $url the url of the API call
     * @param array|object $data the data to pass in the body of the request
     *
     * @return array the result of the execution of the curl
     */
    public function apiPost($url, $data, $header = [])
    {
        $header[] = 'Content-Type: application/xml; charset=UTF-8';
        $header[] = 'Content-Length: ' . strlen($data);

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
     * Performs a call to the API using the DELETE method.
     *
     * @param string $url the url of the API call
     *
     * @return bool the success or failure of the action
     */
    public function apiDelete($url)
    {
        $curl = $this->createCurl();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ];

        curl_setopt_array($curl, $options);

        $result = $this->execCurl($curl);

        return $result['success'];
    }

    /**
     * Performs a call to the API using the PUT method.
     *
     * @param string $url the path of the API call
     * @param string $stream the data to upload
     * @param array $header the data to pass in the header of the request
     * @param array $data the data to pass in the body of the request
     * @param float $filesize the size of the stream
     *
     * @return array the result of the execution of the curl
     */
    public function apiPut($url, $stream, $header = [], $data = [], $filesize = 0, $x = 0, $y = 0)
    {
        $curl = $this->createCurl([], $header);

        if (!(float) $filesize) {
            $stats = fstat($stream);
            $filesize = $stats[7];
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_NOPROGRESS => false,
            CURLOPT_PROGRESSFUNCTION => [$this, 'curlProgress'],
        ];

        curl_setopt_array($curl, $options);

        return $this->execCurl($curl);
    }

    public function curlProgress($curl, $download_size, $downloaded, $upload_size, $uploaded)
    {
        // refresh
        if ($this->ntbr->validRefresh(true)) {
            $this->log($uploaded, true);
        }

        $this->ntbr->refreshBackup(true);
    }

    /**
     * Test the connection to the account
     *
     * @return bool Connection result
     */
    public function testConnection()
    {
        $result = $this->getUserInfos($this->user);

        if ($result == false) {
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

        $result = $this->getUserInfos($this->user);

        if ($result != false) {
            if (isset($result['quota'], $result['quota']['limit'], $result['quota']['usage'])) {
                $quota_total = $result['quota']['limit']; // The user's total quota allocation (bytes).
                $quota_used = $result['quota']['usage']; // The user's used quota outside of shared folders (bytes).

                $quota_available = $quota_total - $quota_used;
            }
        }

        return $quota_available;
    }

    /**
     * Get the user's infos
     *
     * @return array User's infos
     */
    public function getUserInfos()
    {
        $result = $this->apiGet(self::INFOS_USER_URL . '/' . $this->user);
        // $this->log($result, true);

        if (isset($result['result'], $result['result']['body'])) {
            return $result['result']['body'];
        }

        return false;
    }

    /**
     * Get root directory
     *
     * @return string The root Directory
     */
    public function getRoot()
    {
        $result = $this->apiGet(self::INFOS_USER_URL . '/' . $this->user . '/workspaces/contents?type=folder');

        if (isset($result['result'], $result['result']['body'], $result['result']['body']['collection'])) {
            if (isset($result['result']['body']['collection']['contents'])) {
                $result = $this->apiGet($result['result']['body']['collection']['contents'] . '?type=folder');

                if (isset($result['result'], $result['result']['body'], $result['result']['body']['collection'])) {
                    if (isset($result['result']['body']['collection']['contents'])) {
                        return $result['result']['body']['collection'];
                    } elseif (isset($result['result']['body']['collection'][0]['contents'])) {
                        return $result['result']['body']['collection'][0]['contents'];
                    }
                }
            } elseif (isset($result['result']['body']['collection'][0]['contents'])) {
                $result = $this->apiGet($result['result']['body']['collection'][0]['contents'] . '?type=folder');

                if (isset($result['result'], $result['result']['body'], $result['result']['body']['collection'])) {
                    if (isset($result['result']['body']['collection']['contents'])) {
                        return $result['result']['body']['collection'];
                    } elseif (isset($result['result']['body']['collection'][0]['contents'])) {
                        return $result['result']['body']['collection'][0]['contents'];
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get the list of directories
     *
     * @return array List of directories
     */
    public function getDirectories()
    {
        $result = $this->apiGet(self::INFOS_USER_URL . '/' . $this->user . '/workspaces/contents?type=folder');
        $list = [];

        if (isset($result['result'], $result['result']['body'], $result['result']['body']['collection'])) {
            if (isset($result['result']['body']['collection']['contents'])) {
                $result = $this->apiGet($result['result']['body']['collection']['contents'] . '?type=folder');

                if (isset($result['result'], $result['result']['body'], $result['result']['body']['collection'])) {
                    if (isset($result['result']['body']['collection']['contents'])) {
                        $list[] = [
                            'name' => $result['result']['body']['collection']['displayName'],
                            'ref' => $result['result']['body']['collection']['ref'],
                            'children' => $this->getDirectoryChildren(basename($result['result']['body']['collection']['ref'])),
                        ];
                    } elseif (isset($result['result']['body']['collection'][0]['contents'])) {
                        foreach ($result['result']['body']['collection'] as $directory) {
                            $list[] = [
                                'name' => $directory['displayName'],
                                'ref' => $directory['ref'],
                                'children' => $this->getDirectoryChildren(basename($directory['ref'])),
                            ];
                        }
                    }
                }
            } elseif (isset($result['result']['body']['collection'][0]['contents'])) {
                foreach ($result['result']['body']['collection'] as $workspace) {
                    $result = $this->apiGet($workspace['contents'] . '?type=folder');

                    if (isset($result['result'], $result['result']['body'], $result['result']['body']['collection'])) {
                        if (isset($result['result']['body']['collection']['contents'])) {
                            $list[] = [
                                'name' => $result['result']['body']['collection']['displayName'],
                                'ref' => $result['result']['body']['collection']['ref'],
                                'children' => $this->getDirectoryChildren(basename($result['result']['body']['collection']['ref'])),
                            ];
                        } elseif (isset($result['result']['body']['collection'][0]['contents'])) {
                            foreach ($result['result']['body']['collection'] as $directory) {
                                $list[] = [
                                    'name' => $directory['displayName'],
                                    'ref' => $directory['ref'],
                                    'children' => $this->getDirectoryChildren(basename($directory['ref'])),
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $list;
    }

    /**
     * Get the list of directory children
     *
     * @return array List of directory children
     */
    public function getDirectoryChildren($id_parent)
    {
        $result = $this->apiGet(self::DIRECTORY_URL . '/' . $id_parent . '/contents?type=folder');
        $list = [];

        if (isset($result['result'], $result['result']['body'], $result['result']['body']['collection'])) {
            if (isset($result['result']['body']['collection']['contents'])) {
                $list[] = [
                    'name' => $result['result']['body']['collection']['displayName'],
                    'ref' => $result['result']['body']['collection']['ref'],
                    'children' => $this->getDirectoryChildren(basename($result['result']['body']['collection']['ref'])),
                ];
            } elseif (isset($result['result']['body']['collection'][0]['contents'])) {
                foreach ($result['result']['body']['collection'] as $directory) {
                    $list[] = [
                        'name' => $directory['displayName'],
                        'ref' => $directory['ref'],
                        'children' => $this->getDirectoryChildren(basename($directory['ref'])),
                    ];
                }
            }
        }

        return $list;
    }

    /**
     * Creates a file in the current account.
     *
     * @param string $name the name of the SugarSync file to be created
     * @param string $file_path the path to the file
     * @param string $id_parent The ID of the SugarSync folder into which to create the SugarSync file, or empty to
     *                          create it in the SugarSync root folder. Default: ''.
     * @param int $nb_part current part number
     * @param int $nb_part_total total parts to be sent
     *
     * @return bool The result of the file creation
     */
    public function uploadFile($name, $file_path, $id_parent = '', $nb_part = 1, $nb_part_total = 1)
    {
        // Create new file
        $mime = NtbrChild::getMimeType($file_path);

        $data_c = '<?xml version="1.0" encoding="UTF-8" ?>';
        $data_c .= '<file>';
        $data_c .= '<displayName>' . $name . '</displayName>';
        $data_c .= '<mediaType>' . $mime . '</mediaType>';
        $data_c .= '</file>';

        $result_c = $this->apiPost(self::DIRECTORY_URL . '/' . $id_parent, $data_c);

        if (!$result_c['success'] || !is_string($result_c['result'])) {
            $this->log('ERR' . $this->ntbr->l('The file was not created', self::PAGE));

            return false;
        }

        $matches_c = [];

        preg_match('/Location:(.*?)\n/i', $result_c['result'], $matches_c);

        if (!isset($matches_c[1])) {
            $this->log('ERR' . $this->ntbr->l('An error occured while creating the file', self::PAGE));

            return false;
        }

        $this->ntbr->sugarsync_session = trim($matches_c[1]);

        // Upload the file
        return $this->resumeUploadFile($file_path, $nb_part, $nb_part_total);
    }

    /**
     * Resume the upload a file on the account
     *
     * @param string $file_path the path of the file
     * @param int $nb_part current part number
     * @param int $nb_part_total total parts to be sent
     *
     * @return bool the success or failure of the action
     */
    public function resumeUploadFile($file_path, $nb_part = 1, $nb_part_total = 1)
    {
        /*$total_file_size = $this->ntbr->getFileSize($file_path);
        $file = fopen($file_path, 'rb');

        $header[] = 'Content-Length: '.$total_file_size;

        $this->part         = $nb_part;
        $this->total_part   = $nb_part_total;
        $this->percent      = 0;

        $result_upload = $this->apiPut($this->ntbr->sugarsync_session.'/data', $file, $header, array(), $total_file_size);
        fclose($file);

        if (!$result_upload['success'] || !$result_upload['result']) {
            return false;
        }

        //refresh
        $this->ntbr->refreshBackup(true);

        return $result_upload['success'];*/

        $result_upload = [
            'success' => true,
            'result' => '',
        ];

        $total_file_size = $this->ntbr->getFileSize($file_path);

        $byte_start = 0;
        $content_length = $total_file_size;

        if ($this->ntbr->sugarsync_position > 0) {
            $byte_start = $this->ntbr->sugarsync_position; // Next chunk
        }

        if ($total_file_size > self::MAX_FILE_UPLOAD_SIZE) {
            $content_length = self::MAX_FILE_UPLOAD_SIZE;
        }

        $byte_end = $byte_start + $content_length - 1;

        if ($byte_end > $total_file_size) {
            $byte_end = $total_file_size - 1;
            $content_length = $byte_end - $byte_start + 1;
        }

        $byte_to_go = $total_file_size - $this->ntbr->sugarsync_position;

        $file = fopen($file_path, 'r+');

        if ($this->ntbr->sugarsync_position > 0) {
            $file = $this->ntbr->goToPositionInFile($file, $this->ntbr->sugarsync_position, false);

            if ($file === false) {
                return false;
            }
        }

        while ($byte_to_go > 0) {
            $header = [
                'Content-Length: ' . $total_file_size,
                'Range: bytes=' . $byte_start . '-' . $byte_end,
            ];

            $datas = fread($file, $content_length);

            $percent = ($byte_end / $total_file_size) * 100;

            if ($nb_part_total > 1) {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::SUGARSYNC)
                    . ' ' . $nb_part . '/' . $nb_part_total . $this->ntbr->l(':', self::PAGE) . ' ' . (int) $percent . '%'
                );
            } else {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::SUGARSYNC)
                    . ' ' . (int) $percent . '%'
                );
            }

            $this->ntbr->sugarsync_position = $byte_end + 1;

            $result_upload = $this->apiPut($this->ntbr->sugarsync_session . '/data', $file, $header, $datas, $total_file_size, $byte_start, $byte_end);

            if (!$result_upload['success'] || !$result_upload['result']) {
                fclose($file);

                return false;
            }

            $byte_to_go -= $content_length;

            if ($byte_to_go < self::MAX_FILE_UPLOAD_SIZE) {
                $content_length = $byte_to_go;
            }

            $byte_start = $byte_end + 1;
            $this->ntbr->sugarsync_position = $byte_start;
            $byte_end = $byte_start + $content_length - 1;

            // refresh
            $this->ntbr->refreshBackup(true);
        }

        fclose($file);

        return $result_upload['success'];
    }

    /**
     * Delete a file on the account
     *
     * @param string $id_file The ID of the file
     *
     * @return bool the success or failure of the action
     */
    public function deleteFile($id_file)
    {
        return $this->apiDelete(self::FILE_URL . '/' . $id_file);
    }

    /**
     * Create a folder in the account
     *
     * @param string $folder_path the path of the folder
     *
     * @return bool the success or failure of the action
     */
    /*public function createFolder($folder_path)
    {
        $datas = array(
            'path' => $folder_path
        );

        $header = array('Content-Type: application/json');

        $result = $this->apiPost(self::API_URL.'files/create_folder_v2', json_encode($datas), $header);

        return $result['success'];
    }*/

    /**
     * Get all files of the directory
     *
     * @param string $id_dir The SugarSync directory to get the files from
     *
     * @return bool if the item exists
     */
    public function getListFiles($id_dir)
    {
        $result = $this->apiGet(self::DIRECTORY_URL . '/' . $id_dir . '/contents?type=file');
        $list = [];

        if (isset($result['success']) && $result['success']) {
            if (isset($result['result'], $result['result']['body'], $result['result']['body']['file'])) {
                if (isset($result['result']['body']['file']['displayName'])) {
                    $list[] = $result['result']['body']['file'];
                } elseif (isset($result['result']['body']['file'][0]['displayName'])) {
                    $list = $result['result']['body']['file'];
                }
            }
        }

        return $list;
    }

    /**
     * Check if a file exists on the SugarSync account
     *
     * @param string $id_dir The SugarSync directory to look in for the file
     *
     * @return bool if the item exists
     */
    public function checkExists($id_dir, $name)
    {
        $files = $this->getListFiles($id_dir);

        foreach ($files as $file) {
            if ($file['displayName'] == $name) {
                return basename($file['ref']);
            }
        }

        return false;
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
