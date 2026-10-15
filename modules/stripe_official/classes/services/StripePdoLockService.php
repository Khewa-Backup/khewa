<?php

/**
 * Copyright (c) since 2010 Stripe, Inc. (https://stripe.com)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Stripe <https://support.stripe.com/contact/email>
 * @copyright Since 2010 Stripe, Inc.
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

use StripeOfficial\Classes\StripeProcessLogger;
use StripePsModule\Symfony\Component\Lock\LockFactory;
use StripePsModule\Symfony\Component\Lock\Store\PdoStore;

if (!defined('_PS_VERSION_')) {
    exit;
}

class StripePdoLockService
{
    protected $key;
    protected $ttl;

    const TABLE_NAME = _DB_PREFIX_ . 'stripe_lock_keys';

    public function __construct($key, $ttl)
    {
        $this->key = $key;
        $this->ttl = $ttl;
    }

    public function executeUnderLock(callable $executable)
    {
        $lock = null;
        try {
            $pdo = Db::getInstance()->connect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $store = new PdoStore($pdo, ['db_table' => self::TABLE_NAME]);
            $factory = new LockFactory($store);

            $lock = $factory->createLock($this->key, $this->ttl);

            $acquireParamCount = 0;
            try {
                $reflect = new ReflectionMethod($lock, 'acquire');
                $acquireParamCount = $reflect->getNumberOfParameters();
            } catch (ReflectionException $e) {
                // fallback below
            }

            $acquired = false;

            if ($acquireParamCount === 0) {
                try {
                    $lock->acquire();
                } catch (Throwable $e) {
                    StripeProcessLogger::logError('Exception during lock acquire for:' . $this->key . ' Error: ' . $e->getMessage(), 'StripePdoLockService');
                }
                $acquired = isset($lock) && $lock->isAcquired();
            } else {
                $start = microtime(true);
                while (microtime(true) - $start < (float) $this->ttl) {
                    try {
                        $acquired = $lock->acquire(false);
                    } catch (TypeError $e) {
                        try {
                            $lock->acquire();
                        } catch (Throwable $inner) {
                            StripeProcessLogger::logError('Exception during lock acquire fallback for:' . $this->key . ' Error: ' . $inner->getMessage(), 'StripePdoLockService');
                        }
                        $acquired = isset($lock) && $lock->isAcquired();
                    } catch (Throwable $e) {
                        StripeProcessLogger::logError('Exception during lock acquire for:' . $this->key . ' Error: ' . $e->getMessage(), 'StripePdoLockService');
                    }

                    if ($acquired) {
                        break;
                    }

                    usleep(50000);
                }
            }
        } catch (Exception $e) {
            StripeProcessLogger::logError(
                'Exception during lock setup for:' . $this->key . ' Error: ' . $e->getMessage() . ' ' . ($e->getPrevious() ? $e->getPrevious()->getMessage() : ''),
                'StripePdoLockService'
            );
        }

        if (isset($lock) && $lock->isAcquired()) {
            try {
                $executable();
            } catch (Throwable $e) {
                throw $e;
            } finally {
                try {
                    $lock->release();
                } catch (Throwable $e) {
                    StripeProcessLogger::logError('Exception during lock release for:' . $this->key . ' Error: ' . $e->getMessage(), 'StripePdoLockService');
                }
            }
        } elseif (!isset($lock)) {
            // lock couldn't be created (PDO/factory error): preserve fallback and run callable
            $executable();
        } else {
            // lock exists but wasn't acquired within TTL: preserve old fallback — run callable but log
            StripeProcessLogger::logWarning('Lock exists but not acquired for key: ' . $this->key . ' after ' . $this->ttl . 's; executing callable as fallback', 'StripePdoLockService');
            $executable();
        }
    }

    public static function createTable()
    {
        try {
            $pdo = Db::getInstance()->connect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $store = new PdoStore($pdo, ['db_table' => self::TABLE_NAME]);
            $store->createTable();
        } catch (Exception $e) {
            StripeProcessLogger::logError(
                'Exception during table creation:' . $e->getMessage(),
                'StripePdoLockService'
            );
        }
    }
}
