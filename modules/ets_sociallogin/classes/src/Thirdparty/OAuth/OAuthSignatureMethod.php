<?php
/**
 * 2007-2017 ETSHybridauth
 *
 *  @author Hybridauth <https://hybridauth.github.io>
 *  @copyright  2009-2017 Hybridauth
 *  @license    https://hybridauth.github.io/license.html
 *  International Registered Trademark & Property of ETSHybridauth
 */

namespace ETSHybridauth\Thirdparty\OAuth;

if (!defined('_PS_VERSION_')) { exit; }

abstract class OAuthSignatureMethod
{
    /**
    * Needs to return the name of the Signature Method (ie HMAC-SHA1)
    *
    * @return string
    */
    abstract public function get_name();

    /**
    * Build up the signature
    * NOTE: The output of this function MUST NOT be urlencoded.
    * the encoding is handled in OAuthRequest when the final
    * request is serialized
    *
    * @param OAuthRequest $request
    * @param OAuthConsumer $consumer
    * @param object|null $token
    * @return string
    */
    abstract public function build_signature(OAuthRequest $request, OAuthConsumer $consumer, $token);

    /**
    * Verifies that a given signature is correct
    *
    * @param OAuthRequest $request
    * @param OAuthConsumer $consumer
    * @param object|null $token
    * @param string $signature
    * @return bool
    */
    public function check_signature(OAuthRequest $request, OAuthConsumer $consumer, $token, $signature)
    {
        $built = $this->build_signature($request, $consumer, $token);

        // Check for zero length, although unlikely here
        if (\Tools::strlen($built) == 0 || \Tools::strlen($signature) == 0) {
            return false;
        }

        if (\Tools::strlen($built) != \Tools::strlen($signature)) {
            return false;
        }

        // Avoid a timing leak with a (hopefully) time insensitive compare
        $result = 0;
        for ($i = 0; $i < \Tools::strlen($signature); $i ++) {
            $result |= ord($built [$i]) ^ ord($signature [$i]);
        }

        return $result == 0;
    }
}
