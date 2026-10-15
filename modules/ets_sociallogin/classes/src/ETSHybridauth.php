<?php
/**
 * 2007-2017 ETSHybridauth
 *
 *  @author Hybridauth <https://hybridauth.github.io>
 *  @copyright  2009-2017 Hybridauth
 *  @license    https://hybridauth.github.io/license.html
 *  International Registered Trademark & Property of ETSHybridauth
 */

namespace ETSHybridauth;

use ETSHybridauth\Exception\InvalidArgumentException;
use ETSHybridauth\Exception\UnexpectedValueException;
use ETSHybridauth\Storage\StorageInterface;
use ETSHybridauth\Logger\LoggerInterface;
use ETSHybridauth\Logger\Logger;
use ETSHybridauth\HttpClient\HttpClientInterface;

/**
 * ETSHybridauth\ETSHybridauth
 *
 * For ease of use of multiple providers, ETSHybridauth implements the class ETSHybridauth\ETSHybridauth,
 * a sort of factory/façade which acts as an unified interface or entry point, and it expects a
 * configuration array containing the list of providers you want to use, their respective credentials
 * and authorized callback.
 */

if (!defined('_PS_VERSION_')) { exit; }

class ETSHybridauth
{
    /**
    * ETSHybridauth config.
    *
    * @var array
    */
    protected $config;

    /**
    * Storage.
    *
    * @var StorageInterface
    */
    protected $storage;

    /**
    * HttpClient.
    *
    * @var HttpClientInterface
    */
    protected $httpClient;

    /**
    * Logger.
    *
    * @var LoggerInterface
    */
    protected $logger;

    /**
    * PrestaShop Context.
    *
    * @var \Context
    */
    protected $context;

    /**
    * @param array|string        $config     Array with configuration or Path to PHP file that will return array
    * @param HttpClientInterface $httpClient
    * @param StorageInterface    $storage
    * @param LoggerInterface     $logger
    * @param \Context            $context
    *
    * @throws InvalidArgumentException
    */
    public function __construct(
        $config,
        HttpClientInterface $httpClient = null,
        StorageInterface $storage = null,
        LoggerInterface $logger = null,
        $context = null
    ) {
        if (is_string($config) && file_exists($config)) {
            $config = include $config;
        } elseif (! is_array($config)) {
            throw new InvalidArgumentException('ETSHybridauth config does not exist on the given path.');
        }

        $this->config = $config + array(
            'debug_mode'   => Logger::NONE,
            'debug_file'   => '',
            'curl_options' => null,
            'providers'    => array()
        );
        $this->storage = $storage;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->context = $context;
    }

    /**
    * Instantiate the given provider and authentication or authorization protocol.
    *
    * If not authenticated yet, the user will be redirected to the provider's site for
    * authentication/authorisation, otherwise it will simply return an instance of
    * provider's adapter.
    *
    * @param string $name adapter's name (case insensitive)
    *
    * @return \ETSHybridauth\Adapter\AdapterInterface
    * @throws InvalidArgumentException
    * @throws UnexpectedValueException
    */
    public function authenticate($name)
    {
        $adapter = $this->getAdapter($name);
        $adapter->authenticate();
        return $adapter;
    }

    /**
    * Returns a new instance of a provider's adapter by name
    *
    * @param string $name adapter's name (case insensitive)
    *
    * @return \ETSHybridauth\Adapter\AdapterInterface
    * @throws InvalidArgumentException
    * @throws UnexpectedValueException
    */
    public function getAdapter($name)
    {
        $config = $this->getProviderConfig($name);
        if (!$config)
            throw new InvalidArgumentException('Unknown Provider.');
        $adapter = isset($config['adapter']) ? $config['adapter'] : sprintf('ETSHybridauth\\Provider\\%s', $name);
	    if (!class_exists($adapter)) {
		    $adapter = null;
		    $fs = new \FilesystemIterator(dirname(__FILE__) . '/Provider/');
		    /** @var \SplFileInfo $file */
		    foreach ($fs as $file) {
			    if (!$file->isDir()) {
				    $provider = strtok($file->getFilename(), '.');
				    if ($name === mb_strtolower($provider)) {
					    $adapter = sprintf('ETSHybridauth\\Provider\\%s', $provider);
					    break;
				    }
			    }
		    }
		    if ($adapter === null) {
			    throw new InvalidArgumentException('Unknown Provider.');
		    }
	    }
        return new $adapter($config, $this->httpClient, $this->storage, $this->logger, $this->context);
    }

    /**
    * Get provider config by name.
    *
    * @param string $name adapter's name (case insensitive)
    *
    * @throws UnexpectedValueException
    * @throws InvalidArgumentException
    *
    * @return array|null
    */
    public function getProviderConfig($name)
    {
        $name = \Tools::strtolower($name);

        $providersConfig = array_change_key_case($this->config['providers'], CASE_LOWER);

        if (! isset($providersConfig[$name])) {
            return null;
            //throw new InvalidArgumentException('Unknown Provider.');
        }

        if (! $providersConfig[$name]['enabled']) {
            return null;
            //throw new UnexpectedValueException('Disabled Provider.');
        }

        $config = $providersConfig[$name];

        if (! isset($config['callback']) && isset($this->config['callback'])) {
            $config['callback'] = $this->config['callback'];
        }

        return $config;
    }

    /**
    * Returns a boolean of whether the user is connected with a provider
    *
    * @param string $name adapter's name (case insensitive)
    *
    * @return boolean
    * @throws InvalidArgumentException
    * @throws UnexpectedValueException
    */
    public function isConnectedWith($name)
    {
        return $this->getAdapter($name)->isConnected();
    }

    /**
     * Returns a list of enabled adapters names
     *
     * @return array
     */
    public function getProviders()
    {
        $providers = array();
        if (isset($this->config['providers']) && $this->config['providers']) {
            foreach ($this->config['providers'] as $name => $config) {
                if ($config['enabled']) {
                    $providers[] = $name;
                }
            }
        }
        return $providers;
    }

    /**
     * Returns a list of currently connected adapters names
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function getConnectedProviders()
    {
        $providers = array();
        if (($results = $this->getProviders())) {
            foreach ($results as $name) {
                if ($this->isConnectedWith($name)) {
                    $providers[] = $name;
                }
            }
        }
        return $providers;
    }

    /**
     * Returns a list of new instances of currently connected adapters
     *
     * @return \ETSHybridauth\Adapter\AdapterInterface[]
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function getConnectedAdapters()
    {
        $adapters = array();
        if (($providers = $this->getProviders())) {
            foreach ($providers as $name) {
                $adapter = $this->getAdapter($name);

                if ($adapter->isConnected()) {
                    $adapters[$name] = $adapter;
                }
            }
        }
        return $adapters;
    }

    /**
     * Disconnect all currently connected adapters at once
     */
    public function disconnectAllAdapters()
    {
        if (($providers = $this->getProviders())) {
            foreach ($providers as $name) {
                $adapter = $this->getAdapter($name);
                if ($adapter->isConnected()) {
                    $adapter->disconnect();
                }
            }
        }
    }

    /**
     * Disconnect adapter currently connected adapters at once and not included $provider.
     */
    public function disconnectAdapters($provider)
    {
        if ($provider && ($providers = $this->getProviders())) {
            foreach ($providers as $name) {
                if ($provider != $name) {
                    $adapter = $this->getAdapter($name);

                    if ($adapter->isConnected()) {
                        $adapter->disconnect();
                    }
                }
            }
        }
    }
}
