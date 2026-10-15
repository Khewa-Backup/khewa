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
class OpenstackLib
{
    const PAGE = 'openstack';

    /**
     * @var int The maximal size of a file to upload
     */
    const MAX_FILE_UPLOAD_SIZE = 10485760; // 10Mo (10 * 1024 * 1024 = 10 485 760)

    /**
     * @var string The default container name
     */
    const DEFAULT_CONTAINER = 'default';

    // The current token
    private $token;
    // The current endpoint
    private $endpoint;
    // The sdk uri
    private $sdk_uri;
    // The physic sdk uri
    private $physic_sdk_uri;
    // Instance of NtbrCore
    private $ntbr;
    // The account type
    private $account_type;

    public function __construct($ntbr, $sdk_uri, $physic_sdk_uri, $token, $endpoint, $account_type)
    {
        $this->sdk_uri = $sdk_uri;
        $this->physic_sdk_uri = $physic_sdk_uri;
        $this->ntbr = $ntbr;
        $this->token = $token;
        $this->endpoint = $endpoint;
        $this->account_type = $account_type;
    }

    /**
     * Create a curl with default options and any other given options
     *
     * @param array $curl_more_options Further curl options to set. Default array().
     * @param array $curl_header Further header options to set. Default array().
     *
     * @return resource The curl
     */
    private function createCurl($curl_more_options = [], $curl_header = [])
    {
        $curl_header[] = 'X-Auth-Token: ' . $this->token;

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
     * Performs a call to the API using the GET method.
     *
     * @param string $path the path of the API call
     *
     * @return array the response of the execution of the curl
     */
    public function apiGet($path)
    {
        if ($path != '') {
            $url = $this->endpoint . '/' . self::DEFAULT_CONTAINER . '/' . urlencode((string) $path);
        } else {
            $url = $this->endpoint . '/' . self::DEFAULT_CONTAINER;
        }

        $curl = $this->createCurl();

        curl_setopt($curl, CURLOPT_URL, $url);

        return $this->execCurl($curl);
    }

    /**
     * Performs a call to the API using the POST method.
     *
     * @param string $url the url of the API call
     * @param array $data the data to pass in the body of the request
     *
     * @return array the result of the execution of the curl
     */
    public function apiPost($url, $data = [], $curl_header = [])
    {
        $curl = $this->createCurl();

        if ($url != '') {
            $url = $this->endpoint . '/' . urlencode((string) $url);
        } else {
            $url = $this->endpoint;
        }

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
     * Performs a call to the Openstack API using the PUT method.
     *
     * @param string $url the path of the API call
     * @param array $data the data to pass in the body of the request
     * @param array $header the data to pass in the header of the request
     * @param ressource $stream the data to upload
     *
     * @return array the result of the execution of the curl
     */
    public function apiPut($url, $data = [], $header = [], $stream = false, $file_size = 0)
    {
        $curl = $this->createCurl([], $header);

        if ($url != '') {
            $url = $this->endpoint . '/' . self::DEFAULT_CONTAINER . '/' . urlencode((string) $url);
        } else {
            $url = $this->endpoint . '/' . self::DEFAULT_CONTAINER;
        }

        if ($stream) {
            if (!$file_size) {
                $stats = fstat($stream);
                $file_size = $stats[7];
            }
            $options = [
                CURLOPT_URL => $url,
                CURLOPT_PUT => true,
                CURLOPT_INFILE => $stream,
                CURLOPT_INFILESIZE => $file_size,
            ];
        } else {
            $options = [
                CURLOPT_URL => $url,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => $data,
            ];
        }

        curl_setopt_array($curl, $options);

        return $this->execCurl($curl);
    }

    /**
     * Performs a call to the Openstack API using the DELETE method.
     *
     * @param string $url the url to call
     *
     * @return bool the success or failure of the action
     */
    public function apiDelete($url)
    {
        $curl = self::createCurl();

        if ($url != '') {
            $url = $this->endpoint . '/' . self::DEFAULT_CONTAINER . '/' . urlencode((string) $url);
        } else {
            $url = $this->endpoint . '/' . self::DEFAULT_CONTAINER;
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ];

        curl_setopt_array($curl, $options);

        return self::execCurl($curl);
    }

    /**
     * Performs a call to the Openstack API using the HEAD method.
     *
     * @param string $url the url to call
     *
     * @return bool the success or failure of the action
     */
    public function apiHead($url)
    {
        $curl = self::createCurl();

        if ($url != '') {
            $url = $this->endpoint . '/' . self::DEFAULT_CONTAINER . '/' . urlencode((string) $url);
        } else {
            $url = $this->endpoint . '/' . self::DEFAULT_CONTAINER;
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_NOBODY => true,
            CURLOPT_HEADER => true,
        ];

        curl_setopt_array($curl, $options);

        return self::execCurl($curl);
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
        $header = [
            'X-Container-Meta-Quota-Count: ' . (int) $quota,
        ];

        $result = $this->apiPut($directory, [], $header);

        return $result['success'];
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
        $options = '?format=json';

        if ($directory && $directory != '') {
            $options .= '&path=' . $directory;
        }

        $result = $this->apiGet($options);
        $list = [];

        foreach ($result['result'] as $child) {
            // Directory name end with a "/" or have type "application/directory"
            if (substr($child['name'], -1) == '/' || stripos($child['content_type'], 'directory') !== false) {
                $list[] = $child;
            }
        }

        return $list;
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
        $options = '?format=json';

        if ($directory && $directory != '') {
            $options .= '&path=' . $directory;
        }

        $result = $this->apiGet($options);
        $list = [];

        if (is_array($result['result'])) {
            foreach ($result['result'] as $child) {
                // File name do not end with a "/" or have type "application/directory"
                if (substr($child['name'], -1) != '/' && stripos($child['content_type'], 'directory') === false) {
                    $list[] = $child;
                }
            }
        }

        return $list;
    }

    /**
     * Create a folder in the account
     *
     * @param string $folder_path the path of the folder on the account
     *
     * @return bool the success or failure of the action
     */
    public function createFolder($folder_path)
    {
        // Directory should end with a "/"
        if (substr($folder_path, -1) != '/') {
            $folder_path .= '/';
        }

        $result = $this->apiPut($folder_path);

        return $result['success'];
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
        $datas = [];
        $header = [];

        if (!$destination || $destination == '') {
            $destination = basename($file_path);
        }

        $total_size = $this->ntbr->getFileSize($file_path);

        if ($total_size <= self::MAX_FILE_UPLOAD_SIZE) {
            $file = fopen($file_path, 'r+');

            if (!is_resource($file)) {
                return false;
            }

            $header[] = 'Content-Length: ' . $total_size;

            if ($nb_part_total > 1) {
                $this->log(sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), $this->account_type) . ' ' . $nb_part . '/' . $nb_part_total);
            }

            $result = $this->apiPut($destination, $datas, $header, $file, $total_size);
            fclose($file);

            return $result['success'];
        }

        return $this->resumeCreateFile($file_path, $destination, $nb_part, $nb_part_total);
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
        $datas = [];
        $header = [];

        if (!$destination || $destination == '') {
            $destination = basename($file_path);
        }

        $account_type = $this->account_type;
        $total_size = $this->ntbr->getFileSize($file_path);
        $mime_type = NtbrChild::getMimeType($file_path);
        $size_to_send = $total_size - $position; // Total size minus what have been sent already
        $rest_bytes = $size_to_send;

        if ($mime_type && $mime_type != '') {
            $header[] = 'Content-Type: ' . $mime_type;
        }

        // If there is too much too send we only send small parts
        if ($size_to_send > self::MAX_FILE_UPLOAD_SIZE) {
            $size_to_send = self::MAX_FILE_UPLOAD_SIZE;
        }

        $file = fopen($file_path, 'r+');

        if ($position > 0) {
            // Go to the last position in the file
            $file = $this->ntbr->goToPositionInFile($file, $position, false);

            if ($file === false) {
                return false;
            }
        }

        $last_percent = 0;

        while ($rest_bytes > 0) {
            $datas = fread($file, $size_to_send);
            $result = $this->apiPut($destination . '_parts/part' . str_pad($nb_chunk, 4, '0', STR_PAD_LEFT), $datas);

            $rest_bytes -= $size_to_send;
            $percent = (int) ((($total_size - $rest_bytes) / $total_size) * 100);

            if ($percent > $last_percent) {
                if ($nb_part_total > 1) {
                    $this->log(sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), $account_type) . ' ' . $nb_part . '/' . $nb_part_total . $this->ntbr->l(':', self::PAGE) . ' ' . (int) $percent . '%');
                } else {
                    $this->log(sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), $account_type) . ' ' . (int) $percent . '%');
                }

                $last_percent = $percent;
            }

            if (!$result['success']) {
                if ($result['code_http'] == 503) {
                    $this->log('WAR' . sprintf($this->ntbr->l('Sending to %s account: The server is currently unavailable. Please try again at a later time.', self::PAGE), $account_type));
                }

                return false;
            }

            $position_account = strtolower($account_type) . '_position';
            $nb_chunk_account = strtolower($account_type) . '_nb_chunk';
            $this->ntbr->$position_account = ($total_size - $rest_bytes);

            if ($rest_bytes < self::MAX_FILE_UPLOAD_SIZE) {
                $size_to_send = $rest_bytes;
            }

            ++$nb_chunk;

            $this->ntbr->$nb_chunk_account = $nb_chunk;

            // refresh
            $this->ntbr->refreshBackup(true);
        }

        $result = $this->apiPut($destination, '', ['X-Object-Manifest: ' . urlencode(utf8_encode((string) self::DEFAULT_CONTAINER)) . '/' . urlencode(utf8_encode((string) $destination)) . '_parts/']);

        fclose($file);

        return $result['success'];
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
        $account_type = $this->account_type;

        if ($total_file_size <= self::MAX_FILE_UPLOAD_SIZE && $content_size == $total_file_size) {
            $header[] = 'Content-Length: ' . $total_file_size;

            $result = $this->apiPut($destination, $content, $header);
        } else {
            $result = $this->apiPut($destination . '_parts/part' . str_pad($nb_chunk, 4, '0', STR_PAD_LEFT), $content);

            if (!$result['success']) {
                if ($result['code_http'] == 503) {
                    $this->log('WAR' . sprintf($this->ntbr->l('Sending to %s account: The server is currently unavailable. Please try again at a later time.', self::PAGE), $account_type));
                }

                return false;
            }

            ++$nb_chunk;

            $nb_chunk_account = strtolower($account_type) . '_nb_chunk';
            $this->ntbr->$nb_chunk_account = $nb_chunk;

            $rest_bytes = $total_file_size - $position - $content_size; // Total size minus what have been sent already

            if ($rest_bytes <= 0) {
                $result = $this->apiPut($destination, '', ['X-Object-Manifest: ' . urlencode(utf8_encode((string) self::DEFAULT_CONTAINER)) . '/' . urlencode(utf8_encode((string) $destination)) . '_parts/']);
            }
        }

        return $result['success'];
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
        // Try to delete the parts of the file (if there are some) before we delete the manifest
        $nb_chunk = 1;
        $continue = true;

        if ($this->checkExists($file_path . '_parts/part0001')) {
            while ($continue) {
                $res_del_dir = $this->apiDelete($file_path . '_parts/part' . str_pad($nb_chunk, 4, '0', STR_PAD_LEFT));

                if ($res_del_dir['code_http'] == '404') {
                    $continue = false;
                }

                ++$nb_chunk;
            }
        }

        $result = $this->apiDelete($file_path);

        return $result;
    }

    /**
     * Check if a file or folder exists in the account
     *
     * @param string $file_path The path of the file or folder on the account. A folder must end with "/"
     *
     * @return bool if the item exists
     */
    public function checkExists($file_path)
    {
        $result = $this->apiHead($file_path);
        $result_part = $this->apiHead($file_path . '_parts/part0001');

        if (stripos($result['result'], '200 OK') || stripos($result_part['result'], '200 OK')) {
            return true;
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
