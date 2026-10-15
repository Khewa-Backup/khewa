<?php
/**
 * ETSHybridauth User Profile object represents the current logged in user profile.
 *
 * This class contains all user information retrieved from social login providers
 * like Facebook, Google, Twitter, etc.
 *
 * @author Hybridauth <https://hybridauth.github.io>
 * @copyright 2009-2017 Hybridauth
 * @license https://hybridauth.github.io/license.html
 * @since 1.0.0
 */

namespace ETSHybridauth\User;

use ETSHybridauth\Exception\UnexpectedValueException;

/**
 * ETSHybridauth\Userobject represents the current logged in user profile.
 */

if (!defined('_PS_VERSION_')) { exit; }

final class Profile
{
    /**
     * The Unique user's ID on the connected provider
     *
     * @var string|null
     */
    public $identifier = null;

    /**
     * User website, blog, web page
     *
     * @var string|null
     */
    public $webSiteURL = null;

    /**
     * URL link to profile page on the IDp web site
     *
     * @var string|null
     */
    public $profileURL = null;

    /**
     * URL link to user photo or avatar
     *
     * @var string|null
     */
    public $photoURL = null;

    /**
     * User displayName provided by the IDp or a concatenation of first and last name.
     *
     * @var string|null
     */
    public $displayName = null;

    /**
     * A short about_me description
     *
     * @var string|null
     */
    public $description = null;

    /**
     * User's first name
     *
     * @var string|null
     */
    public $firstName = null;

    /**
     * User's last name
     *
     * @var string|null
     */
    public $lastName = null;

    /**
     * User gender (male or female)
     *
     * @var string|null
     */
    public $gender = null;

    /**
     * User's preferred language
     *
     * @var string|null
     */
    public $language = null;

    /**
     * User age, we don't calculate it. We return it as is if the IDp provides it.
     *
     * @var int|null
     */
    public $age = null;

    /**
     * User birth day
     *
     * @var int|null
     */
    public $birthDay = null;

    /**
     * User birth month
     *
     * @var int|null
     */
    public $birthMonth = null;

    /**
     * User birth year
     *
     * @var int|null
     */
    public $birthYear = null;

    /**
     * User email. Note: not all IDp grant access to the user email
     *
     * @var string|null
     */
    public $email = null;

    /**
     * Verified user email. Note: not all IDp grant access to verified user email
     *
     * @var string|null
     */
    public $emailVerified = null;

    /**
     * User phone number
     *
     * @var string|null
     */
    public $phone = null;

    /**
     * Complete user address
     *
     * @var string|null
     */
    public $address = null;

    /**
     * User country
     *
     * @var string|null
     */
    public $country = null;

    /**
     * User region/state
     *
     * @var string|null
     */
    public $region = null;

    /**
     * User city
     *
     * @var string|null
     */
    public $city = null;

    /**
     * User postal/zip code
     *
     * @var string|null
     */
    public $zip = null;

    /**
     * Extra data which is related to the user
     *
     * @var array
     */
    public $data = array();

    /**
     * Prevent the providers adapters from adding new fields.
     *
     * This magic method is called when attempting to set a property that doesn't exist
     * or is not accessible. It throws an exception to prevent dynamic property creation.
     *
     * @param string $name  The name of the property being set
     * @param mixed  $value The value being assigned to the property
     *
     * @throws UnexpectedValueException When trying to add new properties
     *
     * @return void
     */
    public function __set($name, $value)
    {
        unset($value);
        throw new UnexpectedValueException(sprintf('Adding new property "%s" to %s is not allowed.', $name, __CLASS__));
    }
}
