<?php
/**
 * 2007-2017 ETSHybridauth
 *
 *  @author Hybridauth <https://hybridauth.github.io>
 *  @copyright  2009-2017 Hybridauth
 *  @license    https://hybridauth.github.io/license.html
 *  International Registered Trademark & Property of ETSHybridauth
 */

namespace ETSHybridauth\Storage;

/**
 * ETSHybridauth storage manager interface
 */

if (!defined('_PS_VERSION_')) { exit; }

interface StorageInterface
{
    /**
    * Retrieve a item from storage
    *
    * @param string $key
    *
    * @return mixed
    */
    public function get($key);

    /**
    * Add or Update an item to storage
    *
    * @param string $key
    * @param string|array|object $value
    */
    public function set($key, $value);

    /**
    * Delete an item from storage
    *
    * @param string $key
    */
    public function delete($key);

    /**
    * Delete a item from storage
    *
    * @param string $key
    */
    public function deleteMatch($key);

    /**
    * Clear all items in storage
    */
    public function clear();
}
