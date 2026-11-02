<?php
/**
 * Shared module class loader.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'bestkit_log/classes/BestkitLog.class.php';
require_once _PS_MODULE_DIR_ . 'bestkit_log/lib/FineDiff/finediff-code.php';
require_once _PS_MODULE_DIR_ . 'bestkit_log/classes/BestkitLogLoginAttempt.class.php';
require_once _PS_MODULE_DIR_ . 'bestkit_log/classes/BestkitLogVisit.class.php';
