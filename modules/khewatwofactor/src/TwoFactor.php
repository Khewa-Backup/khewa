<?php
/**
 * Khewa staff login code: all the rules in one place.
 *
 * - is the check on or off (file switch, config key, module on)
 * - make, send and check the 6-digit codes (only a hash is stored)
 * - "trust this computer" tokens (only a hash is stored)
 * - logs
 *
 * Plain PHP + PrestaShop legacy classes only, so the Symfony part (GateSubscriber)
 * and the legacy hook in the main module file can both use it.
 */

namespace KhewaTwoFactor;

use Configuration;
use Context;
use Db;
use Mail;
use PrestaShopLogger;

final class TwoFactor
{
    public const MODULE = 'khewatwofactor';

    /** Config key. Set it to 0 (for example from phpMyAdmin) to turn the check off. */
    public const CONFIG_ON = 'KHEWA2FA_ENABLED';
    public const CONFIG_TRUST_DAYS = 'KHEWA2FA_TRUST_DAYS';

    /** If a file with this name is in the module folder, the check is skipped. */
    public const SWITCH_FILE = 'DISABLE_2FA';

    public const SESSION_OK = 'khewatwofactor_ok';
    public const SESSION_BACK = 'khewatwofactor_back';
    public const SESSION_FLASH = 'khewatwofactor_flash';
    public const TRUST_COOKIE = 'khewa2fa_trust';
    public const CSRF_ID = 'khewatwofactor';

    public const CODE_TTL = 600;          // a code works for 10 minutes
    public const MAX_TRIES = 5;           // then the code is dead
    public const RESEND_WAIT = 60;        // seconds between two sends
    public const MAX_SENDS_PER_HOUR = 5;  // per staff member
    public const DEFAULT_TRUST_DAYS = 30;

    public const ST_LIVE = 0;
    public const ST_USED = 1;
    public const ST_LOCKED = 2;
    public const ST_REPLACED = 3;
    public const ST_EXPIRED = 4;
    public const ST_SEND_FAILED = 5;

    private static ?bool $on = null;

    public static function moduleDir(): string
    {
        return dirname(__DIR__) . '/';
    }

    public static function codeTable(): string
    {
        return _DB_PREFIX_ . 'khewatwofactor_code';
    }

    public static function trustTable(): string
    {
        return _DB_PREFIX_ . 'khewatwofactor_trusted';
    }

    /* ------------------------------------------------------------------ */
    /* On / off                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * The check runs only when ALL of these are true:
     * - there is no DISABLE_2FA file in the module folder
     * - the module is installed and turned on
     * - every KHEWA2FA_ENABLED row in the configuration table is 1
     * Read straight from the database (no cache) so a change in phpMyAdmin works at once.
     */
    public static function isOn(): bool
    {
        if (self::$on === null) {
            self::$on = self::readOn();
        }

        return self::$on;
    }

    public static function switchFileExists(): bool
    {
        return is_file(self::moduleDir() . self::SWITCH_FILE);
    }

    private static function readOn(): bool
    {
        if (self::switchFileExists()) {
            return false;
        }
        try {
            $db = Db::getInstance();
            $active = $db->getValue('SELECT `active` FROM `' . _DB_PREFIX_ . 'module` WHERE `name` = \'' . pSQL(self::MODULE) . '\'', false);
            if ((int) $active !== 1) {
                return false;
            }
            $rows = $db->executeS('SELECT `value` FROM `' . _DB_PREFIX_ . 'configuration` WHERE `name` = \'' . pSQL(self::CONFIG_ON) . '\'', true, false);
        } catch (\Throwable $e) {
            return false;
        }
        if (empty($rows)) {
            return false;
        }
        foreach ($rows as $row) {
            if (trim((string) $row['value']) !== '1') {
                return false;
            }
        }

        return true;
    }

    public static function resetCache(): void
    {
        self::$on = null;
    }

    public static function trustDays(): int
    {
        $days = (int) Configuration::getGlobalValue(self::CONFIG_TRUST_DAYS);

        return ($days >= 1 && $days <= 365) ? $days : self::DEFAULT_TRUST_DAYS;
    }

    /* ------------------------------------------------------------------ */
    /* Staff data                                                          */
    /* ------------------------------------------------------------------ */

    /**
     * @return array{id_employee:int, firstname:string, lastname:string, email:string, passwd:string, active:int, id_lang:int, iso_code:string}|null
     */
    public static function employee(int $idEmployee): ?array
    {
        $row = Db::getInstance()->getRow(
            'SELECT e.`id_employee`, e.`firstname`, e.`lastname`, e.`email`, e.`passwd`, e.`active`, e.`id_lang`, l.`iso_code`
            FROM `' . _DB_PREFIX_ . 'employee` e
            LEFT JOIN `' . _DB_PREFIX_ . 'lang` l ON l.`id_lang` = e.`id_lang`
            WHERE e.`id_employee` = ' . (int) $idEmployee,
            false
        );
        if (!$row) {
            return null;
        }

        return [
            'id_employee' => (int) $row['id_employee'],
            'firstname' => (string) $row['firstname'],
            'lastname' => (string) $row['lastname'],
            'email' => (string) $row['email'],
            'passwd' => (string) $row['passwd'],
            'active' => (int) $row['active'],
            'id_lang' => (int) $row['id_lang'],
            'iso_code' => (string) ($row['iso_code'] ?? 'en'),
        ];
    }

    public static function maskEmail(string $email): string
    {
        $at = strpos($email, '@');
        if ($at === false || $at < 1) {
            return '***';
        }

        return substr($email, 0, 1) . str_repeat('*', max(3, $at - 1)) . substr($email, $at);
    }

    /* ------------------------------------------------------------------ */
    /* Codes                                                               */
    /* ------------------------------------------------------------------ */

    /**
     * Ties a login session to one value. $sessionId / $sessionToken come from the PrestaShop
     * employee session (a new one is made at each login).
     */
    public static function sessionRef(int $idEmployee, int $sessionId, string $sessionToken): string
    {
        return hash_hmac('sha256', $idEmployee . '|' . $sessionId . '|' . $sessionToken, _COOKIE_KEY_);
    }

    private static function hashCode(int $idEmployee, string $ref, string $code): string
    {
        return hash_hmac('sha256', $idEmployee . '|' . $ref . '|' . $code, _COOKIE_KEY_);
    }

    /** Last code row for this login session, or null. */
    public static function lastCode(int $idEmployee, string $ref): ?array
    {
        $row = Db::getInstance()->getRow(
            'SELECT * FROM `' . self::codeTable() . '`
            WHERE `id_employee` = ' . (int) $idEmployee . ' AND `session_ref` = \'' . pSQL($ref) . '\'
            ORDER BY `id_code` DESC',
            false
        );

        return $row ?: null;
    }

    /**
     * Can a new code be sent now?
     *
     * @return array{ok:bool, reason:string, wait:int}
     */
    public static function canSend(int $idEmployee): array
    {
        $now = time();
        $db = Db::getInstance();
        $last = (int) $db->getValue('SELECT MAX(`date_add`) FROM `' . self::codeTable() . '` WHERE `id_employee` = ' . (int) $idEmployee, false);
        if ($last > 0 && $now - $last < self::RESEND_WAIT) {
            return ['ok' => false, 'reason' => 'wait', 'wait' => self::RESEND_WAIT - ($now - $last)];
        }
        $rows = $db->executeS(
            'SELECT `date_add` FROM `' . self::codeTable() . '`
            WHERE `id_employee` = ' . (int) $idEmployee . ' AND `date_add` > ' . ($now - 3600) . '
            ORDER BY `date_add` ASC',
            true,
            false
        );
        if (is_array($rows) && count($rows) >= self::MAX_SENDS_PER_HOUR) {
            return ['ok' => false, 'reason' => 'cap', 'wait' => max(1, (int) $rows[0]['date_add'] + 3600 - $now)];
        }

        return ['ok' => true, 'reason' => '', 'wait' => 0];
    }

    /** Seconds before "send again" is allowed (0 = now). */
    public static function resendWait(int $idEmployee): int
    {
        $check = self::canSend($idEmployee);

        return $check['ok'] ? 0 : $check['wait'];
    }

    /**
     * Makes a new code, stores its hash, emails it. Older live codes of this session stop working.
     *
     * @return string 'sent', 'mail_off', 'send_failed' or 'no_staff'
     */
    public static function sendCode(int $idEmployee, string $ref, string $ip): string
    {
        $staff = self::employee($idEmployee);
        if ($staff === null || $staff['active'] !== 1 || $staff['email'] === '') {
            return 'no_staff';
        }
        self::cleanup();

        $now = time();
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $db = Db::getInstance();
        $db->update('khewatwofactor_code', ['status' => self::ST_REPLACED], '`id_employee` = ' . (int) $idEmployee . ' AND `session_ref` = \'' . pSQL($ref) . '\' AND `status` = ' . self::ST_LIVE);
        $db->insert('khewatwofactor_code', [
            'id_employee' => (int) $idEmployee,
            'session_ref' => pSQL($ref),
            'code_hash' => pSQL(self::hashCode($idEmployee, $ref, $code)),
            'date_add' => $now,
            'expires' => $now + self::CODE_TTL,
            'attempts' => 0,
            'status' => self::ST_LIVE,
            'ip' => pSQL(substr($ip, 0, 45)),
        ]);
        $idCode = (int) $db->Insert_ID();

        if ((int) Configuration::get('PS_MAIL_METHOD') === Mail::METHOD_DISABLE) {
            $db->update('khewatwofactor_code', ['status' => self::ST_SEND_FAILED], '`id_code` = ' . $idCode);
            self::log('code NOT sent, shop email is turned off (PS_MAIL_METHOD = 3)', 3, $idEmployee, $ip);

            return 'mail_off';
        }

        $iso = Texts::iso($staff['iso_code']);
        $sent = false;
        try {
            $context = Context::getContext();
            $sent = Mail::send(
                $staff['id_lang'],
                'khewatwofactor_code',
                Texts::get('mail_subject', $iso),
                [
                    '{firstname}' => $staff['firstname'],
                    '{lastname}' => $staff['lastname'],
                    '{code}' => $code,
                    '{minutes}' => (string) (self::CODE_TTL / 60),
                    '{ip}' => $ip,
                ],
                $staff['email'],
                trim($staff['firstname'] . ' ' . $staff['lastname']),
                null,
                null,
                null,
                null,
                self::moduleDir() . 'mails/',
                false,
                isset($context->shop->id) ? (int) $context->shop->id : null
            );
        } catch (\Throwable $e) {
            self::log('code NOT sent, mail error: ' . $e->getMessage(), 3, $idEmployee, $ip);
            $sent = false;
        }

        if (!$sent) {
            $db->update('khewatwofactor_code', ['status' => self::ST_SEND_FAILED], '`id_code` = ' . $idCode);
            self::log('code NOT sent, mail sending failed', 3, $idEmployee, $ip);

            return 'send_failed';
        }
        self::log('code sent by email to ' . self::maskEmail($staff['email']), 1, $idEmployee, $ip);

        return 'sent';
    }

    /**
     * Checks a typed code against the live code of this login session.
     *
     * @return string 'ok', 'wrong', 'locked', 'expired' or 'none'
     */
    public static function checkCode(int $idEmployee, string $ref, string $input, string $ip): string
    {
        $row = self::lastCode($idEmployee, $ref);
        if ($row === null || (int) $row['status'] === self::ST_USED || (int) $row['status'] === self::ST_REPLACED || (int) $row['status'] === self::ST_SEND_FAILED) {
            self::log('login code refused: no live code', 2, $idEmployee, $ip);

            return 'none';
        }
        $id = (int) $row['id_code'];
        $db = Db::getInstance();
        if ((int) $row['status'] === self::ST_LOCKED) {
            self::log('login code refused: code is locked after too many wrong tries', 2, $idEmployee, $ip);

            return 'locked';
        }
        if ((int) $row['status'] === self::ST_EXPIRED || (int) $row['expires'] < time()) {
            $db->update('khewatwofactor_code', ['status' => self::ST_EXPIRED], '`id_code` = ' . $id . ' AND `status` = ' . self::ST_LIVE);
            self::log('login code refused: code is older than ' . (self::CODE_TTL / 60) . ' minutes', 2, $idEmployee, $ip);

            return 'expired';
        }

        $input = preg_replace('/\D+/', '', $input) ?? '';
        $good = strlen($input) === 6 && hash_equals((string) $row['code_hash'], self::hashCode($idEmployee, $ref, $input));

        if ($good) {
            // Only one request can turn a live code into a used one.
            $db->execute('UPDATE `' . self::codeTable() . '` SET `status` = ' . self::ST_USED . ' WHERE `id_code` = ' . $id . ' AND `status` = ' . self::ST_LIVE);
            if ((int) $db->Affected_Rows() === 1) {
                self::log('login code accepted', 1, $idEmployee, $ip);

                return 'ok';
            }
            self::log('login code refused: already used', 2, $idEmployee, $ip);

            return 'none';
        }

        $db->execute('UPDATE `' . self::codeTable() . '` SET `attempts` = `attempts` + 1 WHERE `id_code` = ' . $id . ' AND `status` = ' . self::ST_LIVE);
        $attempts = (int) $db->getValue('SELECT `attempts` FROM `' . self::codeTable() . '` WHERE `id_code` = ' . $id, false);
        if ($attempts >= self::MAX_TRIES) {
            $db->update('khewatwofactor_code', ['status' => self::ST_LOCKED], '`id_code` = ' . $id);
            self::log('LOCKOUT: ' . self::MAX_TRIES . ' wrong codes, the code is dead, a new one is needed', 3, $idEmployee, $ip);

            return 'locked';
        }
        self::log('login code refused: wrong code (try ' . $attempts . ' of ' . self::MAX_TRIES . ')', 2, $idEmployee, $ip);

        return 'wrong';
    }

    public static function triesLeft(int $idEmployee, string $ref): int
    {
        $row = self::lastCode($idEmployee, $ref);

        return $row === null ? 0 : max(0, self::MAX_TRIES - (int) $row['attempts']);
    }

    /** Removes old rows: codes older than a day, trusted computers past their end date. */
    public static function cleanup(): void
    {
        $db = Db::getInstance();
        $db->delete('khewatwofactor_code', '`date_add` < ' . (time() - 86400));
        $db->delete('khewatwofactor_trusted', '`expires` < ' . time());
    }

    /* ------------------------------------------------------------------ */
    /* Trusted computers                                                   */
    /* ------------------------------------------------------------------ */

    private static function passwdSig(string $passwdHash): string
    {
        return hash_hmac('sha256', $passwdHash, _COOKIE_KEY_);
    }

    /**
     * Stores a new trusted computer and returns the cookie value (the plain token is
     * never stored, only its hash).
     *
     * @return array{token:string, expires:int}|null
     */
    public static function createTrust(int $idEmployee, string $ip, string $userAgent): ?array
    {
        $staff = self::employee($idEmployee);
        if ($staff === null) {
            return null;
        }
        $token = bin2hex(random_bytes(32));
        $now = time();
        $expires = $now + self::trustDays() * 86400;
        Db::getInstance()->insert('khewatwofactor_trusted', [
            'id_employee' => (int) $idEmployee,
            'token_hash' => pSQL(hash('sha256', $token)),
            'passwd_sig' => pSQL(self::passwdSig($staff['passwd'])),
            'date_add' => $now,
            'last_used' => $now,
            'expires' => $expires,
            'ip' => pSQL(substr($ip, 0, 45)),
            'user_agent' => pSQL(mb_substr($userAgent, 0, 255)),
        ]);
        self::log('computer trusted for ' . self::trustDays() . ' days', 1, $idEmployee, $ip);

        return ['token' => $token, 'expires' => $expires];
    }

    /**
     * True when the cookie holds a trusted-computer token of this staff member that is
     * still in date, and the staff member is active and has not changed password since.
     */
    public static function checkTrust(int $idEmployee, mixed $cookieValue, string $ip): bool
    {
        if (!is_string($cookieValue) || !preg_match('/^[a-f0-9]{64}$/', $cookieValue)) {
            return false;
        }
        $db = Db::getInstance();
        $row = $db->getRow(
            'SELECT * FROM `' . self::trustTable() . '`
            WHERE `token_hash` = \'' . pSQL(hash('sha256', $cookieValue)) . '\' AND `id_employee` = ' . (int) $idEmployee,
            false
        );
        if (!$row) {
            return false;
        }
        $id = (int) $row['id_trusted'];
        if ((int) $row['expires'] < time()) {
            $db->delete('khewatwofactor_trusted', '`id_trusted` = ' . $id);

            return false;
        }
        $staff = self::employee($idEmployee);
        if ($staff === null || $staff['active'] !== 1) {
            $db->delete('khewatwofactor_trusted', '`id_trusted` = ' . $id);
            self::log('trusted computer refused: staff member is disabled', 2, $idEmployee, $ip);

            return false;
        }
        if (!hash_equals((string) $row['passwd_sig'], self::passwdSig($staff['passwd']))) {
            $db->delete('khewatwofactor_trusted', '`id_trusted` = ' . $id);
            self::log('trusted computer refused: password changed since it was trusted', 2, $idEmployee, $ip);

            return false;
        }
        $db->update('khewatwofactor_trusted', ['last_used' => time()], '`id_trusted` = ' . $id);
        self::log('login accepted: trusted computer, no code needed', 1, $idEmployee, $ip);

        return true;
    }

    public static function listTrusted(): array
    {
        $rows = Db::getInstance()->executeS(
            'SELECT t.*, e.`firstname`, e.`lastname`, e.`email`
            FROM `' . self::trustTable() . '` t
            LEFT JOIN `' . _DB_PREFIX_ . 'employee` e ON e.`id_employee` = t.`id_employee`
            ORDER BY t.`last_used` DESC',
            true,
            false
        );

        return is_array($rows) ? $rows : [];
    }

    public static function deleteTrusted(int $idTrusted): bool
    {
        return (bool) Db::getInstance()->delete('khewatwofactor_trusted', '`id_trusted` = ' . (int) $idTrusted);
    }

    public static function deleteAllTrusted(): bool
    {
        return (bool) Db::getInstance()->execute('DELETE FROM `' . self::trustTable() . '`');
    }

    /* ------------------------------------------------------------------ */
    /* Logs                                                                */
    /* ------------------------------------------------------------------ */

    /** Severity: 1 info, 2 warning, 3 error. Goes to Advanced Parameters > Logs. */
    public static function log(string $message, int $severity, int $idEmployee, string $ip): void
    {
        try {
            PrestaShopLogger::addLog(
                'Staff login code: ' . $message . ' (staff #' . $idEmployee . ', IP ' . $ip . ')',
                $severity,
                null,
                'Employee',
                $idEmployee,
                true,
                $idEmployee
            );
        } catch (\Throwable $e) {
            // never stop a login because a log line could not be written
        }
    }
}
