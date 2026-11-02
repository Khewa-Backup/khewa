<?php
/**
 * Symfony Security subscriber for employee authentication attempts.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Entity\Employee\Employee as DoctrineEmployee;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class BestkitLogAuthenticationSubscriber implements EventSubscriberInterface
{
    public function __construct()
    {
        require_once dirname(__DIR__) . '/includer.php';
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
        );
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        if (!$this->isBackOfficeAuthentication($event->getFirewallName())) {
            return;
        }

        $employee = $event->getUser();
        if (!$employee instanceof DoctrineEmployee) {
            return;
        }

        BestkitLogLoginAttempt::recordAuthenticationAttempt(
            $employee->getEmail(),
            true,
            $employee->getId(),
            trim($employee->getFirstName() . ' ' . $employee->getLastName()),
            (string) $event->getRequest()->getClientIp(),
            (string) $event->getRequest()->headers->get('User-Agent', '')
        );
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        if (!$this->isBackOfficeAuthentication($event->getFirewallName())) {
            return;
        }

        BestkitLogLoginAttempt::recordAuthenticationAttempt(
            (string) $event->getRequest()->request->get('email', ''),
            false,
            0,
            '',
            (string) $event->getRequest()->getClientIp(),
            (string) $event->getRequest()->headers->get('User-Agent', '')
        );
    }

    private function isBackOfficeAuthentication(string $firewallName): bool
    {
        return $firewallName === 'main';
    }
}
