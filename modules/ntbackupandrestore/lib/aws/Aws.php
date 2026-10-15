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
class AwsLib
{
    const PAGE = 'aws';

    /**
     * @var int The maximal size of a file to upload
     */
    const MAX_FILE_UPLOAD_SIZE = 10485760; // 10Mo (10 * 1024 * 1024 = 10 485 760)

    const STORAGE_CLASS_STANDARD = 'STANDARD';
    const STORAGE_CLASS_REDUCED_REDUNDANCY = 'REDUCED_REDUNDANCY';
    const STORAGE_CLASS_STANDARD_IA = 'STANDARD_IA';
    const STORAGE_CLASS_ONEZONE_IA = 'ONEZONE_IA';
    const STORAGE_CLASS_INTELLIGENT_TIERING = 'INTELLIGENT_TIERING';
    const STORAGE_CLASS_GLACIER = 'GLACIER';
    const STORAGE_CLASS_DEEP_ARCHIVE = 'DEEP_ARCHIVE';

    // The current token
    private $token;
    // The client ID
    private $client_id;
    // The client secret
    private $client_secret;
    // The region
    private $region;
    // The bucket
    private $bucket;
    // The host
    private $host;
    // The service
    private $service;
    // The date of the request
    public $current_date;
    // The sdk uri
    private $sdk_uri;
    // The physic sdk uri
    private $physic_sdk_uri;
    // The type of S3
    private $type_s3;
    // Should we authorized unvalid SSL certificat
    private $accept_unvalid_ssl;
    // Instance of NtbrCore
    private $ntbr;

    public function __construct($ntbr, $client_id, $client_secret, $region, $bucket, $host, $sdk_uri, $physic_sdk_uri, $type_s3, $accept_unvalid_ssl, $token = '')
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->region = $region;
        $this->bucket = $bucket;
        $this->host = $host;
        $this->service = 's3';
        $this->sdk_uri = $sdk_uri;
        $this->physic_sdk_uri = $physic_sdk_uri;
        $this->type_s3 = $type_s3;
        $this->accept_unvalid_ssl = $accept_unvalid_ssl;
        $this->ntbr = $ntbr;

        // $current_date = date('Y-m-d H:i:s', strtotime('2013-05-24 00:00:00'));
        $current_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) - date('Z'));
        $this->current_date = $current_date;

        if (!empty($token)) {
            $this->token = $token;
        }
    }

    /**
     * Get the scope.
     *
     * @return string The scope to use
     */
    public function getScope()
    {
        return date('Ymd', strtotime($this->current_date)) . '/' . $this->region . '/' . $this->service . '/aws4_request';
    }

    /**
     * Get the date to a working ISO8601 format
     *
     * @return string Date ISO8601
     */
    public function getDateISO8601()
    {
        return date('Ymd', strtotime($this->current_date)) . 'T' . date('His', strtotime($this->current_date)) . 'Z';
    }

    /**
     * Get the canonical and signed headers.
     *
     * @param array $headers header (array('header1_name' => 'header1_value', 'header2_name' => 'header2_value'))
     *
     * @return array Clean canonical and signed header
     */
    public function getCleanHeader($headers)
    {
        $clean_headers = [
            'canonical' => '',
            'signed' => '',
        ];

        ksort($headers, SORT_STRING | SORT_FLAG_CASE);

        foreach ($headers as $h_name => $h_value) {
            if ($clean_headers['signed'] != '') {
                $clean_headers['signed'] .= ';';
            }

            $clean_headers['canonical'] .= strtolower($h_name) . ':' . trim($h_value) . "\n";
            $clean_headers['signed'] .= strtolower($h_name);
        }

        return $clean_headers;
    }

    /**
     * Get the canonical request.
     *
     * @param string $http_verb GET|POST|PUT|....
     * @param string $ressource the path
     * @param array $params params (array('param1_name' => 'param1_value', 'param2_name' => 'param2_value'))
     * @param array $headers canonical and signed
     *
     * @return string Canonical
     */
    public function getCanonicalRequest($http_verb, $ressource, $params = [], $headers = [], $payload = 'UNSIGNED-PAYLOAD')
    {
        $canonical_query_string = '';
        $canonical_headers = '';
        $signed_headers = '';

        if (is_array($params) && count($params)) {
            ksort($params);

            foreach ($params as $p_name => $p_value) {
                if ($canonical_query_string != '') {
                    $canonical_query_string .= '&';
                }

                $canonical_query_string .= rawurlencode((string) $p_name) . '=' . rawurlencode((string) $p_value);
            }
        }

        if (isset($headers['canonical'])) {
            $canonical_headers = $headers['canonical'];
        }

        if (isset($headers['signed'])) {
            $signed_headers = $headers['signed'];
        }

        return $http_verb . "\n" . $ressource . "\n" . $canonical_query_string . "\n" . $canonical_headers . "\n" . $signed_headers . "\n" . $payload;
    }

    /**
     * Get the string to sign.
     *
     * @param string $canonical_request Canonical request
     *
     * @return string String to sign
     */
    public function getStringToSign($canonical_request)
    {
        $scope = $this->getScope();
        $date_time = $this->getDateISO8601();
        $hash_string = hash('sha256', $canonical_request);

        return 'AWS4-HMAC-SHA256' . "\n" . $date_time . "\n" . $scope . "\n" . $hash_string;
    }

    /**
     * Get the signing key.
     *
     * @return string Signing key
     */
    public function getSigningKey()
    {
        $date_key = hash_hmac('sha256', date('Ymd', strtotime($this->current_date)), 'AWS4' . $this->client_secret, true);
        $date_region_key = hash_hmac('sha256', $this->region, $date_key, true);
        $date_region_service_key = hash_hmac('sha256', $this->service, $date_region_key, true);

        return hash_hmac('sha256', 'aws4_request', $date_region_service_key, true);
    }

    /**
     * Get the signature.
     *
     * @return string Signature
     */
    public function getSignature($string_to_sign, $signing_key)
    {
        return hash_hmac('sha256', $string_to_sign, $signing_key);
    }

    /**
     * Get the host.
     *
     * @return string Host
     */
    public function getHost()
    {
        if ($this->type_s3 == NtbrChild::S3_TYPE_MINIO || $this->type_s3 == NtbrChild::S3_TYPE_VULTR) {
            $host = $this->host;
        } elseif ($this->type_s3 == NtbrChild::S3_TYPE_SCALEWAY || $this->type_s3 == NtbrChild::S3_TYPE_BACKBLAZE) {
            $host = $this->bucket . '.s3.' . $this->region . '.' . $this->host;
        } elseif ($this->type_s3 == NtbrChild::S3_TYPE_OTHER) {
            $host = $this->host;
        } else {
            $host = $this->bucket . '.' . $this->host;
        }

        if (strpos($host, 'https:') !== false) {
            $host = str_replace('https://', '', $host);
        }

        return $host;
    }

    /**
     * Get the authorization.
     *
     * @param string $url the path of the API call
     * @param string $action the action of the API call
     * @param array $params parameters of the API call
     * @param array $headers headers of the API call
     * @param string $payload the payload if there is one
     *
     * @return string Authorization
     */
    public function getAuthorization($url, $action, $params = [], $headers = [], $payload = '')
    {
        $host = $this->getHost();
        $hash_payload = hash('sha256', $payload);
        $other_headers = [
            'host' => $host,
            'x-amz-date' => $this->getDateISO8601(),
            'x-amz-content-sha256' => $hash_payload,
        ];

        $all_headers = array_merge($headers, $other_headers);
        $scope = $this->getScope();
        $clean_headers = $this->getCleanHeader($all_headers);
        $canonical_request = $this->getCanonicalRequest($action, $url, $params, $clean_headers, $hash_payload);
        // p($canonical_request);
        $string_to_sign = $this->getStringToSign($canonical_request);
        // p($string_to_sign);
        $signing_key = $this->getSigningKey();
        $signature = $this->getSignature($string_to_sign, $signing_key);
        // p($signature);
        $authorization = 'AWS4-HMAC-SHA256 Credential=' . $this->client_id . '/' . $scope . ',SignedHeaders=' . $clean_headers['signed'] . ',Signature=' . $signature;
        // p($authorization);
        return $authorization;
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
        if ($this->accept_unvalid_ssl) {
            $not_valid_ssl_authorized = true;
        } else {
            $not_valid_ssl_authorized = false;
        }

        $curl_default_options = [
            // Default option (http://php.net/manual/fr/function.curl-setopt.php)
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => ($not_valid_ssl_authorized ? 0 : 2),
            CURLOPT_SSL_VERIFYPEER => ($not_valid_ssl_authorized ? false : true),
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

        if (!isset($result['result']['Code']) && is_string($result['result']) && strpos($result['result'], '<Error><Code>') !== false) {
            $result['result'] = NtbrChild::decodeXml($result['result']);
        }

        if (isset($result['result']['Code'])) {
            $result['success'] = 0;

            if (isset($result['result']['Message'])) {
                $this->log($result['result']['Message'], true);
            }
        }

        return $result;
    }

    /**
     * Performs a call to the AWS API using the GET method.
     *
     * @param string $url the path of the API call
     * @param array $params parameters of the API call
     * @param array $headers headers of the API call
     *
     * @return array the response of the execution of the curl
     */
    public function apiGet($url, $params = [], $headers = [])
    {
        $host = $this->getHost();

        if ($this->type_s3 == NtbrChild::S3_TYPE_MINIO || $this->type_s3 == NtbrChild::S3_TYPE_VULTR) {
            if (strpos($url, '/' . $this->bucket . '/') === false) {
                $url = str_replace('//', '/', '/' . $this->bucket . '/' . $url);
            }
        }

        $authorization = $this->getAuthorization($url, 'GET', $params, $headers);

        if (strpos($host, 'https:') === false) {
            $url = 'https://' . $host . $url;
        } else {
            $url = $host . $url;
        }

        $list_params = '';

        if (is_array($params) && count($params)) {
            ksort($params);

            if ($this->type_s3 == NtbrChild::S3_TYPE_BACKBLAZE && isset($params['versions'])) {
                $list_params = '?versions';
                unset($params['versions']);
            }

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

        $curl = $this->createCurl();

        $curl_header = [
            'host: ' . $host,
            'authorization: ' . $authorization,
            'x-amz-date: ' . $this->getDateISO8601(),
            'x-amz-content-sha256: ' . hash('sha256', ''),
        ];

        if (is_array($headers) && count($headers)) {
            foreach ($headers as $key => $value) {
                $curl_header[] = $key . ': ' . $value;
            }
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $curl_header,
        ];

        curl_setopt_array($curl, $options);

        return $this->execCurl($curl);
    }

    /**
     * Performs a call to the AWS API using the POST method.
     *
     * @param string $url the path of the API call
     * @param string $data the data to upload
     * @param array $params parameters of the API call
     * @param array $headers headers of the API call
     * @param float $filesize The size of the string
     *
     * @return array the result of the execution of the curl
     */
    public function apiPost($url, $data = '', $params = [], $headers = [], $filesize = 0)
    {
        $host = $this->getHost();

        $headers['content-md5'] = base64_encode(hash('md5', $data, true));

        if ($this->type_s3 == NtbrChild::S3_TYPE_MINIO) {
            if (strpos($url, '/' . $this->bucket . '/') === false) {
                $url = str_replace('//', '/', '/' . $this->bucket . '/' . $url);
            }
        }

        $authorization = $this->getAuthorization($url, 'POST', $params, $headers, $data);

        if (strpos($host, 'https:') === false) {
            $url = 'https://' . $host . $url;
        } else {
            $url = $host . $url;
        }

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

        $curl = $this->createCurl();

        if (!(float) $filesize) {
            $filesize = strlen($data);
        }

        $curl_header = [
            'host: ' . $host,
            'authorization: ' . $authorization,
            'content-length: ' . $filesize,
            'x-amz-date: ' . $this->getDateISO8601(),
            'x-amz-content-sha256: ' . hash('sha256', $data),
        ];

        if (false != $data && '' != $data) {
            $curl_header[] = 'content-type: text/plain';
        }

        if (is_array($headers) && count($headers)) {
            foreach ($headers as $key => $value) {
                $curl_header[] = $key . ': ' . $value;
            }
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HTTPHEADER => $curl_header,
        ];

        if (false != $data && '' != $data) {
            $options[CURLOPT_POSTFIELDS] = $data;
        }

        curl_setopt_array($curl, $options);

        return $this->execCurl($curl);
    }

    /**
     * Performs a call to the AWS API using the PUT method.
     *
     * @param string $url the path of the API call
     * @param string $data the data to upload
     * @param bool $multipart if the upload is in multipart or not
     * @param array $params parameters of the API call
     * @param array $headers headers of the API call
     * @param string $content_type The MIME type of the data stream, or null if unknown. Default: null.
     * @param float $filesize The size of the stream
     *
     * @return array the result of the execution of the curl
     */
    public function apiPut($url, $data, $multipart = false, $params = [], $headers = [], $content_type = null, $filesize = 0)
    {
        $host = $this->getHost();

        $headers['content-md5'] = base64_encode(hash('md5', $data, true));

        if ($this->type_s3 == NtbrChild::S3_TYPE_MINIO) {
            if (strpos($url, '/' . $this->bucket . '/') === false) {
                $url = str_replace('//', '/', '/' . $this->bucket . '/' . $url);
            }
        }

        $authorization = $this->getAuthorization($url, 'PUT', $params, $headers, $data);

        if (strpos($host, 'https:') === false) {
            $url = 'https://' . $host . $url;
        } else {
            $url = $host . $url;
        }

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

        $curl = $this->createCurl();

        if (!(float) $filesize) {
            $filesize = strlen($data);
        }

        $curl_header = [
            'host: ' . $host,
            'authorization: ' . $authorization,
            'content-length: ' . $filesize,
            'x-amz-date: ' . $this->getDateISO8601(),
            'x-amz-content-sha256: ' . hash('sha256', $data),
        ];

        if ($multipart && '' !== $multipart) {
            $curl_header[] = 'expect: 100-continue';
        }

        if (null !== $content_type && '' !== $content_type) {
            $curl_header[] = 'content-type: ' . $content_type;
        }

        if (is_array($headers) && count($headers)) {
            foreach ($headers as $key => $value) {
                $curl_header[] = $key . ': ' . $value;
            }
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER => $curl_header,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HEADER => true,
        ];

        curl_setopt_array($curl, $options);
        $result = $this->execCurl($curl);

        if (stripos($result['result'], 'ETag') !== false) {
            $cut_by_line = preg_split('/(\r\n|\n|\r)/', $result['result']);

            $data = [];

            foreach ($cut_by_line as $line) {
                if (strpos($line, ':') === false) {
                    continue;
                }

                $explode = explode(':', $line);

                if (!isset($explode[0])) {
                    continue;
                }

                $data[$explode[0]] = trim(substr(str_replace($explode[0], '', $line), 1));
            }

            $result['result'] = $data;
        }

        return $result;
    }

    /**
     * Performs a call to the AWS API using the DELETE method.
     *
     * @param string $url the path of the API call
     * @param array $params parameters of the API call
     *
     * @return bool the success or failure of the action
     */
    public function apiDelete($url, $params = [])
    {
        $host = $this->getHost();

        if ($this->type_s3 == NtbrChild::S3_TYPE_MINIO || $this->type_s3 == NtbrChild::S3_TYPE_VULTR) {
            if (strpos($url, '/' . $this->bucket . '/') === false) {
                $url = str_replace('//', '/', '/' . $this->bucket . '/' . $url);
            }
        }

        $authorization = $this->getAuthorization($url, 'DELETE', $params);

        if (strpos($host, 'https:') === false) {
            $url = 'https://' . $host . $url;
        } else {
            $url = $host . $url;
        }

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

        $curl = $this->createCurl();

        $curl_header = [
            'host: ' . $host,
            'authorization: ' . $authorization,
            'x-amz-date: ' . $this->getDateISO8601(),
            'x-amz-content-sha256: ' . hash('sha256', ''),
        ];

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $curl_header,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ];

        curl_setopt_array($curl, $options);

        return $this->execCurl($curl);
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

        $params = [
            'list-type' => '2',
            'delimiter' => '/',
        ];

        if ($path && $path != '') {
            $params['prefix'] = $path;
        }

        if ($next_continuation_token && $next_continuation_token != '') {
            $params['continuation-token'] = $next_continuation_token;
        }

        $result = $this->apiGet('/', $params);

        if (!$result['success']) {
            return false;
        }

        if ($this->type_s3 == NtbrChild::S3_TYPE_VULTR) {
            // If no directories, there is no CommonPrefixes result in Vultr
            if (!isset($result['result']['CommonPrefixes'])) {
                $result['result']['CommonPrefixes'] = [];
            }
        }

        if (!isset($result['result']['CommonPrefixes'])) {
            return false;
        }

        if (is_array($result['result']['CommonPrefixes'])) {
            // If there is only one result, we need to transform it so it work the same as multi result
            if (isset($result['result']['CommonPrefixes']['Prefix'])) {
                $result['result']['CommonPrefixes'] = [0 => $result['result']['CommonPrefixes']];
            }

            foreach ($result['result']['CommonPrefixes'] as $dir) {
                if (!isset($dir['Prefix'])) {
                    continue;
                }

                $subdir = [];
                $children_directories = $this->getListDirectories($dir['Prefix']);

                if ($children_directories && is_array($children_directories)) {
                    $subdir = $children_directories;
                }

                if ($this->type_s3 == NtbrChild::S3_TYPE_MINIO) {
                    if (isset($params['prefix'])) {
                        $name = substr(str_replace($params['prefix'], '', $dir['Prefix']), 0, -1); // Remove the path and the last character ("/") so we only keep the directory name
                    } else {
                        $name = substr($dir['Prefix'], 0, -1); // Remove the last character ("/") so we only keep the directory name
                    }
                } else {
                    $name = substr(str_replace($path, '', $dir['Prefix']), 0, -1); // Remove the path and the last character ("/") so we only keep the directory name
                }

                $directories[] = [
                    'name' => $name,
                    'key' => $dir['Prefix'],
                    'subdir' => $subdir,
                ];
            }
        }

        if (isset($result['result']['IsTruncated']) && $result['result']['IsTruncated'] == true) {
            if (isset($result['result']['NextContinuationToken']) && $result['result']['NextContinuationToken'] != '') {
                $next_directories = $this->getListDirectories($path, $result['result']['NextContinuationToken']);

                if (is_array($next_directories)) {
                    $directories = array_merge($directories, $next_directories);
                }
            }
        }

        return $directories;
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

        if ($this->type_s3 == NtbrChild::S3_TYPE_BACKBLAZE) {
            $params = [
                'versions' => '',
                'delimiter' => '/',
                'encoding-type' => 'url',
            ];
        } else {
            $params = [
                'list-type' => '2',
                'delimiter' => '/',
            ];
        }

        if ($path && $path != '') {
            $params['prefix'] = $path;
        }

        if ($this->type_s3 == NtbrChild::S3_TYPE_MINIO || $this->type_s3 == NtbrChild::S3_TYPE_VULTR) {
            $params['prefix'] = preg_replace('/' . $this->bucket . '/', '', $path, 1); // Replace first occurence of bucket
        } elseif (isset($params['prefix']) && $this->type_s3 == NtbrChild::S3_TYPE_AWS && Tools::substr($params['prefix'], -1) != '/') {
            $params['prefix'] .= '/';
        }

        if ($this->type_s3 == NtbrChild::S3_TYPE_BACKBLAZE) {
            if ($next_continuation_token && $next_continuation_token != '') {
                $params['version-id-marker'] = $next_continuation_token;
            }
        } else {
            if ($next_continuation_token && $next_continuation_token != '') {
                $params['continuation-token'] = $next_continuation_token;
            }
        }

        $result = $this->apiGet('/', $params);

        if (!$result['success']
            || ($this->type_s3 == NtbrChild::S3_TYPE_BACKBLAZE && !isset($result['result']['Version']))
            || ($this->type_s3 != NtbrChild::S3_TYPE_BACKBLAZE && !isset($result['result']['Contents']))
        ) {
            return false;
        }

        if ($this->type_s3 == NtbrChild::S3_TYPE_BACKBLAZE) {
            $contents = $result['result']['Version'];
        } else {
            $contents = $result['result']['Contents'];
        }

        // If the result is an array
        if (is_array($contents)) {
            if (Tools::substr($path, -1) != '/') {
                $path .= '/';
            }

            // Just one result
            if (!isset($contents[0]) && isset($contents['Key'])) {
                // If it is a directory, we ignore it
                if (substr($contents['Key'], -1) != '/') {
                    if (strpos($contents['Key'], $path) !== false) {
                        $name = str_replace($path, '', $contents['Key']); // Remove the path so we only keep the file name
                        $id = $contents['Key'];
                    } else {
                        $name = $contents['Key'];
                        $id = $path . $contents['Key']; // Add the path
                    }

                    $files[] = [
                        'name' => $name,
                        'size' => $contents['Size'],
                        'id' => $id,
                        'version_id' => (isset($contents['VersionId'])) ? $contents['VersionId'] : '',
                    ];
                }
            } else {
                foreach ($contents as $content) {
                    // If it is a directory, we ignore it
                    if (substr($content['Key'], -1) == '/') {
                        continue;
                    }

                    if (strpos($content['Key'], $path) !== false) {
                        $name = str_replace($path, '', $content['Key']); // Remove the path so we only keep the file name
                        $id = $content['Key'];
                    } else {
                        $name = $content['Key'];
                        $id = $path . $content['Key']; // Add the path
                    }

                    $files[] = [
                        'name' => $name,
                        'size' => $content['Size'],
                        'id' => $id,
                        'version_id' => (isset($content['VersionId'])) ? $content['VersionId'] : '',
                    ];
                }
            }
        }

        if (isset($result['result']['IsTruncated']) && $result['result']['IsTruncated'] == true) {
            if ($this->type_s3 == NtbrChild::S3_TYPE_BACKBLAZE) {
                if (isset($result['result']['NextVersionIdMarker']) && $result['result']['NextVersionIdMarker'] != '') {
                    $next_files = $this->getListFiles($path, $result['result']['NextVersionIdMarker']);

                    $files = array_merge($files, $next_files);
                }
            } else {
                if (isset($result['result']['NextContinuationToken']) && $result['result']['NextContinuationToken'] != '') {
                    $next_files = $this->getListFiles($path, $result['result']['NextContinuationToken']);

                    $files = array_merge($files, $next_files);
                }
            }
        }

        return $files;
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

        $result = $this->apiGet('/', $params);

        return $result['success'];
    }

    /**
     * Check if a file exists
     *
     * @param string $file_name The name of the file
     * @param string $path the path to look into
     *
     * @return bool if the file exists
     */
    public function checkFileExists($file_name, $path)
    {
        $list_files = $this->getListFiles($path);

        if (is_array($list_files) && count($list_files)) {
            foreach ($list_files as $file) {
                if ($file['name'] == $file_name) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a directory exists
     *
     * @param string $dir_key The name of the directory
     *
     * @return bool if the directory exists
     */
    public function checkDirectoryExists($dir_key)
    {
        $clean_path = trim($dir_key, '/');

        if (strpos($clean_path, '/') !== false) {
            $parent_key = str_replace(strrchr($clean_path, '/'), '', $dir_key);
        } else {
            $parent_key = '';
        }

        $list_dirs = $this->getListDirectories($parent_key);
        if (is_array($list_dirs) && count($list_dirs)) {
            foreach ($list_dirs as $dir) {
                if ($dir['key'] == $dir_key) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Deletes a file in the current AWS account.
     *
     * @param string $path the path to the file to delete
     * @param string $version_id the version ID of the file to delete
     *
     * @return bool if the item was successfully deleted
     */
    public function deleteFile($path, $version_id = '')
    {
        if ($this->type_s3 == NtbrChild::S3_TYPE_BACKBLAZE && $version_id != '') {
            $params = [
                'versionId' => $version_id,
            ];

            return $this->apiDelete('/' . $path, $params);
        }

        if ($this->type_s3 == NtbrChild::S3_TYPE_VULTR) {
            $path = trim($path, '/');
        }

        return $this->apiDelete('/' . $path);
    }

    /**
     * Add a lifecycle to abort incomplete multipart upload
     *
     * @param string $prefix the prefix of the files on which use this lifecycle
     *
     * @return bool result of the request
     */
    public function addLifecycleAbortIncompleteMultipartUpload($prefix)
    {
        if ($this->type_s3 != NtbrChild::S3_TYPE_WASABI && $this->type_s3 != NtbrChild::S3_TYPE_OTHER && $this->type_s3 != NtbrChild::S3_TYPE_VULTR) {
            // Must be call every backup, in case the shop's name changed and so the prefix changed to
            $lifecycle = '
                <LifecycleConfiguration>
                    <Rule>
                        <ID>ntbackup_abort</ID>
                        <Filter>
                            <Prefix>' . $prefix . '</Prefix>
                        </Filter>
                        <Status>Enabled</Status>
                        <AbortIncompleteMultipartUpload>
                            <DaysAfterInitiation>1</DaysAfterInitiation>
                        </AbortIncompleteMultipartUpload>
                    </Rule>
                </LifecycleConfiguration>
            ';

            $params = [
                'lifecycle' => '',
            ];

            $result = $this->apiPut('/', $lifecycle, false, $params, [], '', strlen($lifecycle));

            return $result['success'];
        }

        return true;
    }

    /**
     * List incomplete multipart upload
     *
     * @return array list all upload ID from incomplete multipart upload
     */
    public function listMultipartUpload()
    {
        $list_multipart_upload = [];
        $url = '/';

        $params = [
            'uploads' => '',
        ];

        $result = $this->apiGet($url, $params);

        if (!$result['success'] || (!isset($result['result']['Upload']))) {
            return false;
        }

        if (isset($result['result']['Upload']['UploadId'])) {
            $list_multipart_upload[] = $result['result']['Upload']['UploadId'];
        } else {
            foreach ($result['result']['Upload'] as $upload) {
                $list_multipart_upload[] = $upload['UploadId'];
            }
        }

        return $list_multipart_upload;
    }

    /**
     * List all the uploaded parts of a specific multipart upload
     *
     * @param string $destination the destination of the file
     * @param string $upload_id the ID of th multipart
     *
     * @return array part of the multipart upload
     */
    public function listPartsMultipartUpload($destination, $upload_id)
    {
        $url = '/' . $destination;

        $params = [
            'uploadId' => $upload_id,
        ];

        $result = $this->apiGet($url, $params);

        if (!$result['success'] || !isset($result['result']['Part'])) {
            return false;
        }

        return $result['result']['Part'];
    }

    /**
     * Init a multi part upload
     *
     * @param string $destination the destination of the file
     * @param string $storage_class The storage class to use
     *
     * @return string ID of the multipart upload
     */
    public function initMultipartUpload($destination, $storage_class)
    {
        $url = '/' . ltrim($destination, '/');

        $params = [
            'uploads' => '',
        ];

        $headers = [
            'x-amz-storage-class' => $storage_class,
        ];

        $result = $this->apiPost($url, '', $params, $headers);

        if (!$result['success'] || !isset($result['result']['UploadId'])) {
            return false;
        }

        return $result['result']['UploadId'];
    }

    /**
     * Upload a part of the file
     *
     * @param string $destination the destination of the file
     * @param int $part_num the number of the part
     * @param string $upload_id the ID of the multipart upload
     * @param string $data the content to upload
     * @param float $filesize the size of the part
     *
     * @return bool success or failure of the request
     */
    public function uploadPart($destination, $part_num, $upload_id, $data, $filesize)
    {
        $url = '/' . ltrim($destination, '/');

        $params = [
            'partNumber' => (int) $part_num,
            'uploadId' => $upload_id,
        ];

        if (!is_string($data)) {
            return false;
        }

        $result = $this->apiPut($url, $data, true, $params, [], '', $filesize);

        if (!isset($result['result']['ETag']) && !isset($result['result']['etag'])) {
            // $this->log($result);
            $this->log('WAR' . sprintf($this->ntbr->l('The file %s cannot be created', self::PAGE), $destination));

            return false;
        }

        // Save ETag for later
        $this->ntbr->aws_etag[] = [
            'part_number' => $part_num,
            'etag' => isset($result['result']['ETag']) ? $result['result']['ETag'] : $result['result']['etag'],
        ];

        // $this->log($this->ntbr->aws_etag);

        return $result['success'];
    }

    /**
     * Finish a multipart upload
     *
     * @param string $destination the destination of the file
     * @param string $upload_id the ID of the multipart upload
     * @param float $filesize the size of the file to upload
     * @param array $list_parts the list of the parts uploaded
     *
     * @return bool success or failure of the request
     */
    public function completeMultipartUpload($destination, $upload_id, $filesize, $list_parts)
    {
        $url = '/' . ltrim($destination, '/');

        $complete_multipart_upload = '<CompleteMultipartUpload>';

        foreach ($list_parts as $part) {
            $complete_multipart_upload .= '<Part>';
            $complete_multipart_upload .= '<PartNumber>' . $part['part_number'] . '</PartNumber>';
            $complete_multipart_upload .= '<ETag>' . $part['etag'] . '</ETag>';
            $complete_multipart_upload .= '</Part>';
        }

        $complete_multipart_upload .= '</CompleteMultipartUpload>';

        $params = [
            // $complete_multipart_upload => '',
            'uploadId' => $upload_id,
        ];

        $headers = [
            'content-length: ' . $filesize,
        ];

        $result = $this->apiPost($url, $complete_multipart_upload, $params, $headers);

        if ($result['success'] === false || $result['code_http'] != '200') {
            $this->log(
                'WAR'
                . sprintf($this->ntbr->l('The %1$s upload of the file %2$s cannot be completed', self::PAGE), NtbrChild::AWS, $destination)
            );

            return false;
        }

        return true;
    }

    /**
     * Abort a multipart upload
     *
     * @param string $destination the destination of the file
     * @param string $upload_id the ID of the multipart upload
     *
     * @return bool success or failure of the request
     */
    public function abortMultipartUpload($destination, $upload_id)
    {
        $url = '/' . $destination;

        $params = [
            'uploadId' => $upload_id,
        ];

        $result = $this->apiDelete($url, $params);

        if (!$result['success']) {
            $this->log($this->ntbr->l('Cannot abort the upload ID', self::PAGE) . ' ' . $upload_id);
        }

        return $result['success'];
    }

    /**
     * Creates a file in the current AWS account.
     *
     * @param string $file_name the name of the AWS file to be created
     * @param string $file_path the path to the file to upload
     * @param string $aws_directory_key the directory where the file must be send
     * @param string $aws_storage_class The storage class to use
     * @param int $upload_num the number of the upload part to send
     * @param int $part_num the number of the file part
     * @param int $total_part_num the number total of the file part
     * @param string $prefix Optional. Common name between all files.
     *
     * @return bool The result of the file creation
     */
    public function uploadFile($file_name, $file_path, $aws_directory_key, $aws_storage_class, $upload_num, $part_num = 1, $total_part_num = 1, $prefix = '')
    {
        if ($prefix && '' != $prefix) {
            if ($this->type_s3 != NtbrChild::S3_TYPE_WASABI && $this->type_s3 != NtbrChild::S3_TYPE_OTHER && $this->type_s3 != NtbrChild::S3_TYPE_BACKBLAZE && $this->type_s3 != NtbrChild::S3_TYPE_VULTR) {
                // Add lifecycle abort incomplete multipart upload
                $this->log('addLifecycleAbortIncompleteMultipartUpload', true);
                if (!$this->addLifecycleAbortIncompleteMultipartUpload($prefix)) {
                    // $this->log('WAR'.$this->ntbr->l(
                    // 'The life cycle could not be added', self::PAGE));
                }
            }
        }

        $destination = $aws_directory_key . $file_name;

        $this->log('initMultipartUpload', true);
        // Init multipart upload
        $upload_id = $this->initMultipartUpload($destination, $aws_storage_class);

        if ($upload_id === false) {
            $this->log('WAR' . $this->ntbr->l('The upload cannot be initialize', self::PAGE));

            return false;
        }

        $this->ntbr->aws_upload_id = $upload_id;

        $this->log('resumeUploadFile', true);
        // Resume upload
        return $this->resumeUploadFile($upload_id, $file_name, $aws_directory_key, $file_path, $upload_num, $part_num, $total_part_num, 0);
    }

    /**
     * Resume creates a file in the current AWS account.
     *
     * @param string $upload_id the ID of the upload to continue
     * @param string $file_name the name of the AWS file to be created
     * @param string $aws_directory_key the directory where the file must be send
     * @param string $file_path the path to the file to upload
     * @param int $upload_num the number of the upload part to send
     * @param int $part_num the number of the file part
     * @param int $total_part_num the number total of the file part
     * @param float $position the position in the file
     *
     * @return bool The result of the file creation
     */
    public function resumeUploadFile($upload_id, $file_name, $aws_directory_key, $file_path, $upload_num, $part_num, $total_part_num, $position)
    {
        $aws_directory_key = '/' . ltrim($aws_directory_key, '/');

        $destination = $aws_directory_key . $file_name;

        // Upload part
        $total_file_size = (float) $this->ntbr->getFileSize($file_path);
        $rest_to_upload = $total_file_size - $position;
        $start_file_part = $position;

        if ($total_file_size <= 0) {
            $this->log('WAR' . $this->ntbr->l('The file size is not valid', self::PAGE));
        }

        $file = fopen($file_path, 'r');

        if ($position > 0) {
            // Go to the last position in the file
            $file = $this->ntbr->goToPositionInFile($file, $position, false);

            if ($file === false) {
                // Abort multipart
                $this->abortMultipartUpload($destination, $upload_id);

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

        if (!$upload_id || $upload_id == '') {
            $this->log('WAR' . $this->ntbr->l('The upload ID is not valid.', self::PAGE));
            // Abort multipart
            $this->abortMultipartUpload($destination, $upload_id);

            return false;
        }

        while ($rest_to_upload > 0) {
            $part_size_done = $total_file_size - $rest_to_upload;
            $percent = ($part_size_done / $total_file_size) * 100;

            if ($total_part_num > 1) {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::AWS)
                    . ' ' . $part_num . '/' . $total_part_num . ' : ' . (int) $percent . '%'
                );
            } else {
                $this->log(
                    sprintf($this->ntbr->l('Sending to %s account:', self::PAGE), NtbrChild::AWS)
                    . ' ' . (int) $percent . '%'
                );
            }

            $part_file = fread($file, $file_part_size);

            if (!is_string($part_file)) {
                $this->log('WAR' . $this->ntbr->l('Error while creating your file: the file cannot be read correctly.', self::PAGE) . ' (' . $file_path . ')');
                // Abort multipart
                $this->abortMultipartUpload($destination, $upload_id);

                return false;
            }

            $result_upload = $this->uploadPart($destination, $upload_num, $upload_id, $part_file, $file_part_size);

            if (!$result_upload) {
                // Abort multipart
                $this->log('WAR' . sprintf($this->ntbr->l('The upload of the file %s failed.', self::PAGE), $file_path));
                $this->abortMultipartUpload($destination, $upload_id);

                return false;
            }

            $start_file_part = $end_file_part + 1;
            $this->ntbr->aws_position = $start_file_part;
            $rest_to_upload -= $file_part_size;

            if ($rest_to_upload > self::MAX_FILE_UPLOAD_SIZE) {
                $file_part_size = self::MAX_FILE_UPLOAD_SIZE;
                $end_file_part = ($start_file_part + $file_part_size - 1);
            } else {
                $file_part_size = $rest_to_upload;
                $end_file_part = ($start_file_part + $rest_to_upload - 1);
            }

            ++$upload_num;
            $this->ntbr->aws_upload_part = $upload_num;

            // refresh
            $this->ntbr->refreshBackup(true);
        }

        fclose($file);

        if (count($this->ntbr->aws_etag)) {
            // Complete multipart
            if (!$this->completeMultipartUpload($destination, $upload_id, $total_file_size, $this->ntbr->aws_etag)) {
                $this->abortMultipartUpload($destination, $upload_id);

                return false;
            }
        } else {
            // Abort multipart
            $this->log('WAR' . $this->ntbr->l('No upload was successful.', self::PAGE));
            $this->abortMultipartUpload($destination, $upload_id);

            return false;
        }

        return true;
    }

    /**
     * Create a resumable session
     *
     * @param string $file_name the name of the AWS file to be created
     * @param string $aws_directory_key the directory where the file must be send
     * @param string $aws_storage_class Storage class to use
     * @param string $prefix Optional. Common name between all files.
     *
     * @return bool the success or failure of the action
     */
    public function createSession($file_name, $aws_directory_key, $aws_storage_class, $prefix = '')
    {
        if ($prefix && '' != $prefix) {
            if ($this->type_s3 != NtbrChild::S3_TYPE_WASABI && $this->type_s3 != NtbrChild::S3_TYPE_OTHER && $this->type_s3 != NtbrChild::S3_TYPE_VULTR) {
                // Add lifecycle abort incomplete multipart upload
                if (!$this->addLifecycleAbortIncompleteMultipartUpload($prefix)) {
                    // $this->log('WAR'.$this->ntbr->l(
                    // 'The life cycle could not be added', self::PAGE));
                }
            }
        }

        $destination = $aws_directory_key . $file_name;

        // Init multipart upload
        $upload_id = $this->initMultipartUpload($destination, $aws_storage_class);

        if ($upload_id === false) {
            $this->log('WAR' . $this->ntbr->l('The upload cannot be initialize', self::PAGE));

            return false;
        }

        $this->ntbr->aws_upload_id = $upload_id;

        return true;
    }

    /**
     * Resume the upload of the content on the account
     *
     * @param string $content the content to upload
     * @param int $content_size the size of the content to upload
     * @param int $total_file_size The total size of the file the content is part of
     * @param string $file_name the name of the AWS file to be created
     * @param string $aws_directory_key the directory where the file must be send
     * @param int $upload_num the number of the upload part to send
     * @param float $position the position in the file
     *
     * @return bool The result of the file creation
     */
    public function resumeUploadContent($content, $content_size, $total_file_size, $file_name, $aws_directory_key, $upload_num)
    {
        if ($total_file_size <= 0) {
            $this->log('WAR' . $this->ntbr->l('The file size is not valid', self::PAGE));

            return false;
        }

        $destination = $aws_directory_key . $file_name;

        if (!$this->ntbr->aws_upload_id || $this->ntbr->aws_upload_id == '') {
            $this->log('WAR' . $this->ntbr->l('The upload ID is not valid.', self::PAGE));
            // Abort multipart
            $this->abortMultipartUpload($destination, $this->ntbr->aws_upload_id);

            return false;
        }

        if (!is_string($content)) {
            $this->log('WAR' . $this->ntbr->l('Error while creating your file: the file cannot be read correctly.', self::PAGE));
            // Abort multipart
            $this->abortMultipartUpload($destination, $this->ntbr->aws_upload_id);

            return false;
        }

        $result_upload = $this->uploadPart($destination, $upload_num, $this->ntbr->aws_upload_id, $content, $content_size);

        if (!$result_upload) {
            // Abort multipart
            $this->log('WAR' . $this->ntbr->l('The upload of the file failed.', self::PAGE));
            $this->abortMultipartUpload($destination, $this->ntbr->aws_upload_id);

            return false;
        }

        ++$upload_num;

        $this->ntbr->aws_upload_part = $upload_num;
        $rest_to_upload = $total_file_size - ($this->ntbr->aws_position + $content_size);

        if ($rest_to_upload <= 0) {
            if (count($this->ntbr->aws_etag)) {
                // Complete multipart
                if (!$this->completeMultipartUpload($destination, $this->ntbr->aws_upload_id, $total_file_size, $this->ntbr->aws_etag)) {
                    $this->abortMultipartUpload($destination, $this->ntbr->aws_upload_id);

                    return false;
                }
            } else {
                // Abort multipart
                $this->log('WAR' . $this->ntbr->l('No upload was successful.', self::PAGE));
                $this->abortMultipartUpload($destination, $this->ntbr->aws_upload_id);

                return false;
            }
        }

        return true;
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
