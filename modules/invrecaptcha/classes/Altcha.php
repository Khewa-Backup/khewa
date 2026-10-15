<?php
/**
 * Spam Protection - Google reCAPTCHA, hCaptcha & GDPR Captcha
 *
 * @author    WebshopWorks
 * @copyright 2018-2026 WebshopWorks.com
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Altcha
{
    public static function generateChallenge($secret, $complexity = 'low', $expires = 300)
    {
        $salt = bin2hex(random_bytes(12)) . '?' . http_build_query([
            'expires' => time() + (int) $expires,
        ]);
        $range = self::getComplexity($complexity);
        $secret_number = random_int($range['min'], $range['max']);
        $challenge = hash('sha256', $salt . $secret_number);
        $signature = hash_hmac('sha256', $challenge, $secret);

        return [
            'algorithm' => 'SHA-256',
            'challenge' => $challenge,
            'maxnumber' => $range['max'],
            'salt' => $salt,
            'signature' => $signature,
        ];
    }

    protected static function getComplexity($complexity)
    {
        switch ($complexity) {
            case 1:
            case 'minimal':
                $min = 10;
                $max = 200;
                break;
            case 2:
            case 'low':
                $min = 200;
                $max = 2000;
                break;
            case 3:
            case 'medium':
                $min = 2000;
                $max = 20000;
                break;
            case 4:
            case 'high':
                $min = 20000;
                $max = 50000;
                break;
            case 5:
            case 'very_high':
                $min = 70000;
                $max = 150000;
                break;
            default:
                $min = 200;
                $max = 1000;
        }

        return [
            'min' => $min,
            'max' => $max,
        ];
    }

    public static function verifySolution($payload, $secret)
    {
        $data = json_decode(base64_decode($payload), true);

        if (!$data || !isset($data['algorithm'], $data['challenge'], $data['salt'], $data['signature'], $data['number'])) {
            return false;
        }
        // Parse salt parameters
        $params = [];
        $salt_parts = explode('?', $data['salt']);

        if (isset($salt_parts[1])) {
            parse_str($salt_parts[1], $params);
        }
        // Check expiration
        if (!empty($params['expires'])) {
            $expires = (int) $params['expires'];

            if ($expires > 0 && $expires < time()) {
                return false;
            }
        }

        return $data['algorithm'] === 'SHA-256'
            && $data['challenge'] === hash('sha256', $data['salt'] . $data['number'])
            && $data['signature'] === hash_hmac('sha256', $data['challenge'], $secret);
    }
}
