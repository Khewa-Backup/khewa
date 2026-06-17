<?php
/**
 * 2007-2017 ETSHybridauth
 *
 * @author Hybridauth <https://hybridauth.github.io>
 * @copyright  2009-2017 Hybridauth
 * @license    https://hybridauth.github.io/license.html
 *  International Registered Trademark & Property of ETSHybridauth
 */

namespace ETSHybridauth\Provider;

use ETSHybridauth\Adapter\OAuth2;
use ETSHybridauth\Data;
use ETSHybridauth\Exception\UnexpectedApiResponseException;
use ETSHybridauth\User;

/**
 * LinkedIn OAuth2 provider adapter.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class LinkedIn extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    protected $scope = 'openid profile email';//r_liteprofile r_emailaddress

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.linkedin.com/v2/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://www.linkedin.com/oauth/v2/authorization';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://www.linkedin.com/oauth/v2/accessToken';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://docs.microsoft.com/en-us/linkedin/shared/authentication/authentication';

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $fields = [
            'id',
            'firstName',
            'lastName',
            'profilePicture(displayImage~:playableStreams)',
        ];


        $response = $this->apiRequest('userinfo');//, 'GET', ['projection' => '(' . implode(',', $fields) . ')']
        $data = new Data\Collection($response);

        if (!$data->exists('sub')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();
        // Handle localized names.
        $userProfile->firstName = $data->get('given_name');
        $userProfile->lastName = $data->get('family_name');
        $userProfile->identifier = $data->get('sub');
        $userProfile->email = $data->get('email');
        $userProfile->emailVerified = $userProfile->email;
        $userProfile->displayName = $data->get('name') ?: trim($userProfile->firstName . ' ' . $userProfile->lastName);
        $userProfile->photoURL = $data->get('picture');

        return $userProfile;
    }

    /**
     * Returns a user photo.
     *
     * @param array $elements
     *   List of file identifiers related to this artifact.
     *
     * @return string|null
     *   The user photo URL.
     *
     * @see https://docs.microsoft.com/en-us/linkedin/shared/references/v2/profile/profile-picture
     */
    public function getUserPhotoUrl($elements)
    {
        if (is_array($elements)) {
            // Get the largest picture from the list which is the last one.
            $element = end($elements);
            if (!empty($element->identifiers)) {
                return reset($element->identifiers)->identifier;
            }
        }

        return null;
    }

    /**
     * Returns an email address of user.
     *
     * @return string|null
     *   The user email address.
     *
     * @throws \Exception
     */
    public function getUserEmail()
    {
        $response = $this->apiRequest('emailAddress', 'GET', [
            'q' => 'members',
            'projection' => '(elements*(handle~))',
        ]);
        $data = new Data\Collection($response);

        foreach ($data->filter('elements')->toArray() as $element) {
            $item = new Data\Collection($element);

            if ($email = $item->filter('handle~')->get('emailAddress')) {
                return $email;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @see https://docs.microsoft.com/en-us/linkedin/consumer/integrations/self-serve/share-on-linkedin
     * @throws \Exception
     */
    public function setUserStatus($status, $userID = null)
    {
        if (strpos($this->scope, 'w_member_social') === false) {
            throw new \Exception('Set user status requires w_member_social permission!');
        }

        if (is_string($status)) {
            $status = [
                'author' => 'urn:li:person:' . $userID,
                'lifecycleState' => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary' => [
                            'text' => $status,
                        ],
                        'shareMediaCategory' => 'NONE',
                    ],
                ],
                'visibility' => [
                    'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
                ],
            ];
        }


        $headers = [
            'Content-Type' => 'application/json',
            'x-li-format' => 'json',
            'X-Restli-Protocol-Version' => '2.0.0',
        ];

        $response = $this->apiRequest("ugcPosts", 'POST', $status, $headers);

        return $response;
    }

    /**
     * Returns a preferred locale for given field.
     *
     * @param \ETSHybridauth\Data\Collection $data
     *   A data to check.
     * @param string $field_name
     *   A field name to perform.
     *
     * @return string
     *   A field locale.
     */
    protected function getPreferredLocale($data, $field_name)
    {
        $locale = $data->filter($field_name)->filter('preferredLocale');
        $language = $locale->get('language');
        $country = $locale->get('country');

        if ($language && $country) {
            return $language . '_' . $country;
        }

        return 'en_US';
    }
}
