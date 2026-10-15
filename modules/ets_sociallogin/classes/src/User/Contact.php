<?php
/**
 * 2007-2017 ETSHybridauth
 *
 *  @author Hybridauth <https://hybridauth.github.io>
 *  @copyright  2009-2017 Hybridauth
 *  @license    https://hybridauth.github.io/license.html
 *  International Registered Trademark & Property of ETSHybridauth
 */

namespace ETSHybridauth\User;

use ETSHybridauth\Exception\UnexpectedValueException;

/**
 * ETSHybridauth\User\Contact
 */

if (!defined('_PS_VERSION_')) { exit; }

final class Contact
{
    /**
    * The Unique contact user ID
    *
    * @var string
    */
    public $identifier = null;

    /**
    * User website, blog, web page
    *
    * @var string
    */
    public $webSiteURL = null;

    /**
    * URL link to profile page on the IDp web site
    *
    * @var string
    */
    public $profileURL = null;

    /**
    * URL link to user photo or avatar
    *
    * @var string
    */
    public $photoURL = null;

    /**
    * User displayName provided by the IDp or a concatenation of first and last name
    *
    * @var string
    */
    public $displayName = null;

    /**
    * A short about_me
    *
    * @var string
    */
    public $description = null;

    /**
    * User email. Not all of IDp grant access to the user email
    *
    * @var string
    */
    public $email = null;

    /**
    * Prevent the providers adapters from adding new fields.
    *
    * @param string $name
    * @param mixed $value
    *
    * @throws UnexpectedValueException
    */
    public function __set($name, $value)
    {
	    unset($value);
        throw new UnexpectedValueException(sprintf('Adding new property "%s" to %s is not allowed.', $name, __CLASS__));
    }
}
