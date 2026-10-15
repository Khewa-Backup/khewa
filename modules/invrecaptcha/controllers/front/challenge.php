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

class InvReCaptchaChallengeModuleFrontController extends ModuleFrontController
{
    /** @var InvReCaptcha */
    public $module;

    public function postProcess()
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Pragma: no-cache');
        header('Content-Type: application/json');

        $config = $this->module->getConfig();

        if (empty($config['provider']) || 'altcha' !== $config['provider'] || empty($config['secretkey'])) {
            exit(json_encode(['error' => true]));
        }

        require_once _PS_MODULE_DIR_ . 'invrecaptcha/classes/Altcha.php';

        $complexity = isset($config['complexity']) ? $config['complexity'] : 'low';
        $expiry = isset($config['expiry']) ? (int) $config['expiry'] : 300;
        $challenge = Altcha::generateChallenge($config['secretkey'], $complexity, $expiry);

        exit(json_encode($challenge));
    }
}
