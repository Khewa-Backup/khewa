<?php
/**
 * Khewa staff login code
 *
 * Two-step login for BACK OFFICE STAFF: after the right password, a 6-digit code is
 * emailed to the staff member, and nothing of the back office opens until that code
 * is typed in. Customers are not touched.
 *
 * Where the check lives:
 *   1. config/admin/services.yml -> src/EventSubscriber/GateSubscriber.php
 *      A Symfony kernel.request listener. In PrestaShop 9.2 every back office request
 *      (Symfony pages, old AdminControllers, admin ajax) goes through it.
 *      Plus a LoginSuccess listener that keeps the old admin cookie empty until the code.
 *   2. hookActionAdminControllerInitBefore (below): a second check for old AdminControllers.
 *
 * Ways to turn it off with no back office access (cPanel / FTP / phpMyAdmin):
 *   - rename or delete the folder modules/khewatwofactor
 *   - put a file named DISABLE_2FA in modules/khewatwofactor/
 *   - in the database: UPDATE ps_configuration SET value = '0' WHERE name = 'KHEWA2FA_ENABLED';
 * See khewa-docs/khewatwofactor.md.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use KhewaTwoFactor\TwoFactor;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

class Khewatwofactor extends Module
{
    public function __construct()
    {
        $this->name = 'khewatwofactor';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Khewa';
        $this->bootstrap = true;
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '9.0.0', 'max' => _PS_VERSION_];
        parent::__construct();

        $this->displayName = $this->l('Khewa staff login code');
        $this->description = $this->l('After the password, staff must type a code sent to their email before the back office opens.');
    }

    /* ------------------------------------------------------------------ */
    /* Install / uninstall                                                 */
    /* ------------------------------------------------------------------ */

    public function install()
    {
        return parent::install()
            && $this->installDb()
            && Configuration::updateGlobalValue(TwoFactor::CONFIG_ON, 1)
            && Configuration::updateGlobalValue(TwoFactor::CONFIG_TRUST_DAYS, TwoFactor::DEFAULT_TRUST_DAYS)
            && $this->registerHook('actionAdminControllerInitBefore');
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallDb()
            && Configuration::deleteByName(TwoFactor::CONFIG_ON)
            && Configuration::deleteByName(TwoFactor::CONFIG_TRUST_DAYS);
    }

    private function installDb(): bool
    {
        $engine = _MYSQL_ENGINE_;
        $db = Db::getInstance();

        return $db->execute('CREATE TABLE IF NOT EXISTS `' . TwoFactor::codeTable() . '` (
                `id_code` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_employee` INT UNSIGNED NOT NULL,
                `session_ref` CHAR(64) NOT NULL,
                `code_hash` CHAR(64) NOT NULL,
                `date_add` INT UNSIGNED NOT NULL,
                `expires` INT UNSIGNED NOT NULL,
                `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                `status` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                `ip` VARCHAR(45) NOT NULL DEFAULT \'\',
                PRIMARY KEY (`id_code`),
                KEY `employee_session` (`id_employee`, `session_ref`),
                KEY `employee_date` (`id_employee`, `date_add`)
            ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4')
            && $db->execute('CREATE TABLE IF NOT EXISTS `' . TwoFactor::trustTable() . '` (
                `id_trusted` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_employee` INT UNSIGNED NOT NULL,
                `token_hash` CHAR(64) NOT NULL,
                `passwd_sig` CHAR(64) NOT NULL,
                `date_add` INT UNSIGNED NOT NULL,
                `last_used` INT UNSIGNED NOT NULL,
                `expires` INT UNSIGNED NOT NULL,
                `ip` VARCHAR(45) NOT NULL DEFAULT \'\',
                `user_agent` VARCHAR(255) NOT NULL DEFAULT \'\',
                PRIMARY KEY (`id_trusted`),
                UNIQUE KEY `token_hash` (`token_hash`),
                KEY `id_employee` (`id_employee`)
            ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4');
    }

    private function uninstallDb(): bool
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . TwoFactor::codeTable() . '`, `' . TwoFactor::trustTable() . '`');
    }

    /* ------------------------------------------------------------------ */
    /* Second check for old (legacy) AdminControllers                      */
    /* ------------------------------------------------------------------ */

    public function hookActionAdminControllerInitBefore(array $params): void
    {
        if (!TwoFactor::isOn()) {
            return;
        }
        $container = SymfonyContainer::getInstance();
        if ($container === null || !$container->has('khewatwofactor.gate')) {
            return;
        }
        $request = $container->get('request_stack')->getCurrentRequest();
        $gate = $container->get('khewatwofactor.gate');
        if ($request === null || $gate->isVerified($request)) {
            return;
        }
        // PrestaShop 9.2 builds old AdminControllers while it matches the route, before the
        // Symfony listener runs. So stop here already: ajax/POST get a 403, a page view is
        // sent to the back office home, where the code page is shown.
        if ($request->isXmlHttpRequest() || !$request->isMethod('GET')) {
            TwoFactor::log('blocked ' . $request->getMethod() . ' ' . Tools::getValue('controller') . ' (old admin page) before the login code', 2, $gate->employeeId($request), Tools::getRemoteAddr());
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'login_code_required']);
            exit;
        }
        Tools::redirectAdmin($request->getSchemeAndHttpHost() . $request->getBaseUrl() . '/');
    }

    /* ------------------------------------------------------------------ */
    /* Settings page                                                       */
    /* ------------------------------------------------------------------ */

    public function getContent()
    {
        $output = '';
        // Form token tied to the staff member and the shop secret. (In 9.2 getAdminTokenLite()
        // gives a new value at each call, so it cannot be compared.) PrestaShop also checks the
        // _token in the page address on every back office request.
        $formToken = hash_hmac('sha256', 'khewatwofactor-settings|' . (int) $this->context->employee->id, _COOKIE_KEY_);

        if (Tools::isSubmit('khewa2fa_form')) {
            if (!hash_equals($formToken, (string) Tools::getValue('khewa2fa_token'))) {
                $output .= $this->displayError($this->l('The form timed out. Please try again.'));
            } elseif (Tools::isSubmit('khewa2fa_save')) {
                $days = (int) Tools::getValue('khewa2fa_trust_days');
                if ($days < 1 || $days > 365) {
                    $output .= $this->displayError($this->l('Trust days must be between 1 and 365.'));
                } else {
                    Configuration::updateGlobalValue(TwoFactor::CONFIG_ON, Tools::getValue('khewa2fa_enabled') === '1' ? 1 : 0);
                    Configuration::updateGlobalValue(TwoFactor::CONFIG_TRUST_DAYS, $days);
                    TwoFactor::resetCache();
                    $output .= $this->displayConfirmation($this->l('Settings saved.'));
                }
            } elseif (Tools::isSubmit('khewa2fa_delete_all')) {
                TwoFactor::deleteAllTrusted();
                $output .= $this->displayConfirmation($this->l('All trusted computers removed.'));
            } elseif (Tools::getValue('khewa2fa_delete')) {
                TwoFactor::deleteTrusted((int) Tools::getValue('khewa2fa_delete'));
                $output .= $this->displayConfirmation($this->l('Trusted computer removed.'));
            }
        }

        $trusted = [];
        foreach (TwoFactor::listTrusted() as $row) {
            $trusted[] = [
                'id' => (int) $row['id_trusted'],
                'staff' => trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? '')) . ' (#' . (int) $row['id_employee'] . ')',
                'date_add' => date('Y-m-d H:i', (int) $row['date_add']),
                'last_used' => date('Y-m-d H:i', (int) $row['last_used']),
                'expires' => date('Y-m-d H:i', (int) $row['expires']),
                'ip' => (string) $row['ip'],
                'user_agent' => (string) $row['user_agent'],
            ];
        }

        $this->context->smarty->assign([
            'k2fa_action' => $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name]),
            'k2fa_token' => $formToken,
            'k2fa_enabled' => (int) Configuration::getGlobalValue(TwoFactor::CONFIG_ON) === 1,
            'k2fa_trust_days' => TwoFactor::trustDays(),
            'k2fa_is_on' => TwoFactor::isOn(),
            'k2fa_switch_file' => TwoFactor::switchFileExists(),
            'k2fa_mail_off' => (int) Configuration::get('PS_MAIL_METHOD') === Mail::METHOD_DISABLE,
            'k2fa_trusted' => $trusted,
        ]);

        return $output . $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }
}
