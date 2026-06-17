<?php
/**
 * 2007-2017 ETSHybridauth
 *
 * @author Hybridauth <https://hybridauth.github.io>
 * @copyright  2009-2017 Hybridauth
 * @license    https://hybridauth.github.io/license.html
 *  International Registered Trademark & Property of ETSHybridauth
 */

namespace ETSHybridauth\Storage;

use ETSHybridauth\Exception\RuntimeException;

/**
 * ETSHybridauth storage manager
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Session implements StorageInterface
{
    /**
     * Context
     *
     * @var mixed
     */
    protected $context;

    /**
     * Namespace
     *
     * @var string
     */
    protected $storeNamespace = 'ETSHybridauth::STORAGE';

    /**
     * Key prefix
     *
     * @var string
     */
    protected $keyPrefix = 'etl_';

    /**
     * Initiate a new session
     *
     * @param mixed $context The context to be used by the session
     * @throws RuntimeException
     */
    public function __construct($context)
    {
        if (headers_sent()) {
            throw new RuntimeException('HTTP headers already sent to browser and ETSHybridauth won\'t be able to start/resume PHP cookie.');
        }
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $key = $this->keyPrefix . \Tools::strtolower($key);
        if (isset($this->context->cookie, $this->context->cookie->$key)) {
            if (json_decode($this->context->cookie->$key) && json_last_error() == JSON_ERROR_NONE) {
                return json_decode($this->decode($key, $this->context->cookie->$key), true);
            } else {
                return $this->decode($key, $this->context->cookie->$key);
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $key = $this->keyPrefix . \Tools::strtolower($key);
        if (is_array($value) || is_object($value)) {
            $this->context->cookie->$key = $this->encode($key, json_encode($value));
        } else {
            $this->context->cookie->$key = $this->encode($key, (string)$value);
        }
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function decode($key, $value)
    {
        return @strpos($key, 'amazon') !== false && (@strpos($key, 'access_token') !== false || @strpos($key, 'refresh_token') !== false) ? call_user_func('base64_decode', $value) : $value;
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function encode($key, $value)
    {
        return @strpos($key, 'amazon') !== false && (@strpos($key, 'access_token') !== false || @strpos($key, 'refresh_token') !== false) ? call_user_func('base64_encode', $value) : $value;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        if (isset($this->context->cookie) && count(($cookies = $this->context->cookie->getFamily($this->keyPrefix)))) {
            foreach ($cookies as $k => $v) {
                unset($this->context->cookie->$k, $v);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $key = $this->keyPrefix . \Tools::strtolower($key);
        if (isset($this->context->cookie, $this->context->cookie->$key)) {
            unset($this->context->cookie->$key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMatch($key)
    {
        $key = $this->keyPrefix . \Tools::strtolower($key);
        if (isset($this->context->cookie) && ($cookies = $this->context->cookie->getFamily($key))) {
            foreach ($cookies as $k => $v) {
                if (strstr($k, $key)) {
                    unset($this->context->cookie->$k, $v);
                }
            }
        }
    }

    /**
     * Check value to find if it was serialized.
     *
     * If $data is not an string, then returned value will always be false.
     * Serialized data is always a string.
     *
     * @param string $data Value to check to see if was serialized.
     * @param bool $strict Optional. Whether to be strict about the end of the string. Default true.
     * @return bool False if not serialized and true if it was.
     * @since 2.0.5
     *
     */
    public function is_serialized($data, $strict = true)
    {
        // if it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (\Tools::strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = \Tools::substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace)
                return false;
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3)
                return false;
            if (false !== $brace && $brace < 4)
                return false;
        }
        $token = $data[0];
        switch ($token) {
            case 's' :
                if ($strict) {
                    if ('"' !== \Tools::substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
            // or else fall through
            case 'a' :
            case 'O' :
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b' :
            case 'i' :
            case 'd' :
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }
        return false;
    }
}
