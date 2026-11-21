<?php
/**
 * Khewa staff login code: the Symfony part.
 *
 * PrestaShop 9.2 does the back office login with Symfony security (form_login on the
 * "main" firewall). Every back office page goes through the Symfony kernel: Symfony pages,
 * old (legacy) AdminControllers and admin ajax calls. So one kernel.request listener,
 * placed right after the firewall and PrestaShop's own session check (priority 7), can
 * stop every request of a staff member who typed the right password but not the code yet.
 *
 * - onKernelRequest (priority 5): no code yet -> the code page (normal page view),
 *   a 403 JSON answer (ajax, POST, anything else), or the code form actions.
 * - onLoginSuccess: right after the password is accepted. PrestaShop then writes the old
 *   "legacy" admin cookie with the staff id in it. Some old scripts (the file manager in
 *   the admin folder, old module ajax files) trust that cookie alone, so the staff id is
 *   taken out of it until the code is right.
 *
 * The listeners are wired in config/admin/services.yml in a way that keeps the back office
 * working when the module folder is renamed or deleted (see the notes there).
 */

namespace KhewaTwoFactor\EventSubscriber;

use Context;
use KhewaTwoFactor\GatePage;
use KhewaTwoFactor\Texts;
use KhewaTwoFactor\TwoFactor;
use PrestaShopBundle\Entity\Employee\Employee;
use PrestaShopBundle\Entity\Employee\EmployeeSession;
use PrestaShopBundle\Security\Admin\TokenAttributes;
use PrestaShopBundle\Utils\SafeUnserializeTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class GateSubscriber
{
    use SafeUnserializeTrait;

    /** Legacy cookie fields that make old scripts think the staff member is logged in. */
    private const LEGACY_LOGIN_FIELDS = ['id_employee', 'email', 'profile', 'passwd', 'session_id', 'session_token'];

    public function __construct(
        private readonly Security $security,
        private readonly RouterInterface $router,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    /* ------------------------------------------------------------------ */
    /* Listeners                                                           */
    /* ------------------------------------------------------------------ */

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        if (!TwoFactor::isOn()) {
            return;
        }
        $user = $event->getUser();
        if (!$user instanceof Employee) {
            return;
        }
        $request = $event->getRequest();
        $ref = $this->refFromToken($event->getAuthenticatedToken());
        if ($ref !== null && $request->hasSession()
            && TwoFactor::checkTrust($user->getId(), $request->cookies->get(TwoFactor::TRUST_COOKIE), (string) $request->getClientIp())) {
            $request->getSession()->set(TwoFactor::SESSION_OK, $ref);

            return;
        }
        $this->stripLegacyCookie(true);
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || !TwoFactor::isOn()) {
            return;
        }
        $user = $this->security->getUser();
        if (!$user instanceof Employee) {
            return;
        }
        $request = $event->getRequest();
        if (!$request->hasSession()) {
            return;
        }
        $ref = $this->refFromToken($this->security->getToken());
        if ($ref === null) {
            // No PrestaShop employee session: PrestaShop logs the user out by itself.
            return;
        }
        $session = $request->getSession();
        if (hash_equals($ref, (string) $session->get(TwoFactor::SESSION_OK, ''))) {
            return;
        }
        $idEmployee = $user->getId();
        $ip = (string) $request->getClientIp();
        if (TwoFactor::checkTrust($idEmployee, $request->cookies->get(TwoFactor::TRUST_COOKIE), $ip)) {
            $session->set(TwoFactor::SESSION_OK, $ref);

            return;
        }

        // Not checked yet: nothing of the back office may run.
        $this->stripLegacyCookie(false);
        $response = $this->handle($request, $session, $idEmployee, $ref, $ip);
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        $event->setResponse($response);
    }

    /**
     * Used by the legacy hook (actionAdminControllerInitBefore) as a second check.
     * PrestaShop 9.2 builds old AdminControllers (and runs their init()) while it matches
     * the route, before the firewall has loaded the login. So when Symfony security has no
     * login yet, the login is read from the session the same way PrestaShop does it.
     */
    public function isVerified(Request $request): bool
    {
        if (!TwoFactor::isOn()) {
            return true;
        }
        $token = $this->security->getToken() ?? $this->tokenFromSession($request);
        if (!$token?->getUser() instanceof Employee) {
            return true; // not logged in at all: Symfony security deals with it
        }
        $ref = $this->refFromToken($token);
        if ($ref === null || !$request->hasSession()) {
            return false;
        }

        return hash_equals($ref, (string) $request->getSession()->get(TwoFactor::SESSION_OK, ''));
    }

    /** Staff id of the login in this request (0 if none), for the legacy hook's log line. */
    public function employeeId(Request $request): int
    {
        $user = ($this->security->getToken() ?? $this->tokenFromSession($request))?->getUser();

        return $user instanceof Employee ? $user->getId() : 0;
    }

    /* ------------------------------------------------------------------ */
    /* Request handling                                                    */
    /* ------------------------------------------------------------------ */

    private function handle(Request $request, SessionInterface $session, int $idEmployee, string $ref, string $ip): Response
    {
        $staff = TwoFactor::employee($idEmployee);
        $iso = Texts::iso($staff['iso_code'] ?? 'en');

        if ($request->isMethod('POST') && $request->request->has('khewa2fa_action')) {
            return $this->handleAction($request, $session, $idEmployee, $ref, $ip, $iso);
        }

        $isPageView = ($request->isMethod('GET') || $request->isMethod('HEAD'))
            && !$request->isXmlHttpRequest()
            && !in_array('application/json', $request->getAcceptableContentTypes(), true);
        if (!$isPageView) {
            TwoFactor::log('blocked ' . $request->getMethod() . ' ' . $request->getPathInfo() . ' before the login code', 2, $idEmployee, $ip);

            return new JsonResponse(['error' => 'login_code_required', 'message' => Texts::get('ajax', $iso)], Response::HTTP_FORBIDDEN);
        }

        // Remember the page the staff member wanted, to go there after the code.
        if (!in_array($request->attributes->get('_route'), ['admin_login', 'admin_logout', 'khewatwofactor_code'], true)) {
            $session->set(TwoFactor::SESSION_BACK, $request->getUri());
        }

        // First view after the password: send the first code.
        if (TwoFactor::lastCode($idEmployee, $ref) === null) {
            $check = TwoFactor::canSend($idEmployee);
            if ($check['ok']) {
                $result = TwoFactor::sendCode($idEmployee, $ref, $ip);
                if ($result !== 'sent') {
                    $this->flash($session, 'err', Texts::get($result === 'mail_off' ? 'err_mail_off' : 'err_send', $iso));
                }
            } else {
                $this->flash($session, 'err', $this->limitText($check, $iso));
            }
        }

        $last = TwoFactor::lastCode($idEmployee, $ref);
        $hasCode = $last !== null && (int) $last['status'] === TwoFactor::ST_LIVE && (int) $last['expires'] >= time();
        $flash = $session->get(TwoFactor::SESSION_FLASH);
        $session->remove(TwoFactor::SESSION_FLASH);

        $html = GatePage::render([
            'iso' => $iso,
            'action' => $this->pageUrl(),
            'logout' => $this->router->generate('admin_logout'),
            'csrf' => $this->csrfTokenManager->getToken(TwoFactor::CSRF_ID)->getValue(),
            'email' => $staff['email'] ?? '',
            'has_code' => $hasCode,
            'wait' => TwoFactor::resendWait($idEmployee),
            'trust_days' => TwoFactor::trustDays(),
            'flash' => is_array($flash) ? $flash : null,
        ]);

        return new Response($html, Response::HTTP_OK, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function handleAction(Request $request, SessionInterface $session, int $idEmployee, string $ref, string $ip, string $iso): Response
    {
        $back = new RedirectResponse($this->pageUrl(), Response::HTTP_SEE_OTHER);

        $token = new CsrfToken(TwoFactor::CSRF_ID, (string) $request->request->get('khewa2fa_csrf', ''));
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            TwoFactor::log('code form refused: bad or old form token', 2, $idEmployee, $ip);
            $this->flash($session, 'err', Texts::get('err_form', $iso));

            return $back;
        }

        $action = (string) $request->request->get('khewa2fa_action', '');

        if ($action === 'resend') {
            $check = TwoFactor::canSend($idEmployee);
            if (!$check['ok']) {
                TwoFactor::log('new code refused: ' . ($check['reason'] === 'cap' ? 'too many codes this hour' : 'asked again too soon'), 2, $idEmployee, $ip);
                $this->flash($session, 'err', $this->limitText($check, $iso));

                return $back;
            }
            $result = TwoFactor::sendCode($idEmployee, $ref, $ip);
            if ($result === 'sent') {
                $this->flash($session, 'ok', Texts::get('ok_sent', $iso));
            } else {
                $this->flash($session, 'err', Texts::get($result === 'mail_off' ? 'err_mail_off' : 'err_send', $iso));
            }

            return $back;
        }

        if ($action !== 'verify') {
            return $back;
        }

        $result = TwoFactor::checkCode($idEmployee, $ref, (string) $request->request->get('khewa2fa_code', ''), $ip);
        if ($result !== 'ok') {
            $text = match ($result) {
                'wrong' => Texts::get('err_wrong', $iso, TwoFactor::triesLeft($idEmployee, $ref)),
                'expired' => Texts::get('err_expired', $iso),
                'locked' => Texts::get('err_locked', $iso),
                default => Texts::get('err_none', $iso),
            };
            $this->flash($session, 'err', $text);

            return $back;
        }

        // Right code: this login session is now fully open.
        $target = (string) $session->get(TwoFactor::SESSION_BACK, '');
        $session->remove(TwoFactor::SESSION_BACK);
        $session->migrate(false);
        $session->set(TwoFactor::SESSION_OK, $ref);
        if ($target === '' || !str_starts_with($target, $request->getSchemeAndHttpHost() . $request->getBasePath() . '/')) {
            $target = $this->router->generate('admin_homepage', [], RouterInterface::ABSOLUTE_URL);
        }
        $response = new RedirectResponse($target, Response::HTTP_SEE_OTHER);

        if ($request->request->get('khewa2fa_trust') === '1') {
            $trust = TwoFactor::createTrust($idEmployee, $ip, (string) $request->headers->get('User-Agent', ''));
            if ($trust !== null) {
                $response->headers->setCookie(Cookie::create(
                    TwoFactor::TRUST_COOKIE,
                    $trust['token'],
                    $trust['expires'],
                    rtrim($request->getBasePath(), '/') . '/',
                    null,
                    $request->isSecure(),
                    true,
                    false,
                    Cookie::SAMESITE_LAX
                ));
            }
        }

        return $response;
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * The module's own route (config/routes.yml). The code page is shown there and the forms
     * post there. Its controller is a core one (the back office home redirect), so a staff
     * member who already gave the code is just sent to the home page.
     */
    private function pageUrl(): string
    {
        return $this->router->generate('khewatwofactor_code', [], RouterInterface::ABSOLUTE_URL);
    }

    private function tokenFromSession(Request $request): ?TokenInterface
    {
        if (!$request->hasPreviousSession()) {
            return null;
        }
        $raw = $request->getSession()->get('_security_main');
        if (!is_string($raw)) {
            return null;
        }
        $token = $this->safelyUnserialize($raw);

        return $token instanceof TokenInterface ? $token : null;
    }

    private function refFromToken(?TokenInterface $token): ?string
    {
        if ($token === null || !$token->hasAttribute(TokenAttributes::EMPLOYEE_SESSION)) {
            return null;
        }
        $employeeSession = $token->getAttribute(TokenAttributes::EMPLOYEE_SESSION);
        $user = $token->getUser();
        if (!$employeeSession instanceof EmployeeSession || !$user instanceof Employee) {
            return null;
        }

        return TwoFactor::sessionRef($user->getId(), $employeeSession->getId(), $employeeSession->getToken());
    }

    private function stripLegacyCookie(bool $write): void
    {
        $cookie = Context::getContext()->cookie ?? null;
        if ($cookie === null) {
            return;
        }
        foreach (self::LEGACY_LOGIN_FIELDS as $field) {
            unset($cookie->{$field});
        }
        if ($write) {
            $cookie->write();
        }
    }

    private function flash(SessionInterface $session, string $type, string $text): void
    {
        $session->set(TwoFactor::SESSION_FLASH, ['type' => $type, 'text' => $text]);
    }

    /** @param array{ok:bool, reason:string, wait:int} $check */
    private function limitText(array $check, string $iso): string
    {
        return $check['reason'] === 'cap'
            ? Texts::get('err_cap', $iso, (int) ceil($check['wait'] / 60))
            : Texts::get('err_wait', $iso, $check['wait']);
    }
}
