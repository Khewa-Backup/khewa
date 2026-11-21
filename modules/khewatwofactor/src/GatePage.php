<?php
/**
 * Khewa staff login code: the HTML of the "type your code" page.
 * Self-contained (no outside CSS or JS), every value is escaped.
 */

namespace KhewaTwoFactor;

final class GatePage
{
    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * @param array{iso:string, action:string, logout:string, csrf:string, email:string, has_code:bool, wait:int, trust_days:int, flash:?array} $p
     */
    public static function render(array $p): string
    {
        $iso = $p['iso'];
        $t = static fn (string $key, int|string ...$args): string => self::e(Texts::get($key, $iso, ...$args));

        $flash = '';
        if (!empty($p['flash']['text'])) {
            $class = ($p['flash']['type'] ?? '') === 'ok' ? 'ok' : 'err';
            $flash = '<p class="msg ' . $class . '" role="alert">' . self::e((string) $p['flash']['text']) . '</p>';
        }

        $intro = $p['has_code']
            ? $t('intro', TwoFactor::maskEmail($p['email']), TwoFactor::CODE_TTL / 60)
            : $t('intro_none');

        $wait = $p['wait'] > 0 ? '<p class="small">' . $t('resend_wait', $p['wait']) . '</p>' : '';

        $action = self::e($p['action']);
        $csrf = self::e($p['csrf']);

        return '<!DOCTYPE html>
<html lang="' . ($iso === 'fr' ? 'fr' : 'en') . '">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>' . $t('title') . '</title>
<style>
body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#f1f3f5;color:#25272b}
.box{max-width:400px;margin:8vh auto;background:#fff;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.08);padding:28px}
h1{font-size:22px;margin:0 0 12px}
p{line-height:1.45}
label{display:block;font-weight:600;margin:16px 0 6px}
input.code{width:100%;box-sizing:border-box;font-size:28px;letter-spacing:8px;text-align:center;padding:10px;border:1px solid #bbcdd2;border-radius:6px}
.check{display:flex;gap:8px;align-items:center;margin:14px 0;font-weight:400}
button{width:100%;padding:12px;font-size:16px;border:0;border-radius:6px;cursor:pointer}
.main{background:#25b9d7;color:#fff}
.second{background:#eef2f4;color:#25272b;margin-top:18px}
.msg{padding:10px 12px;border-radius:6px}
.err{background:#fde8e8;color:#8a1f11}
.ok{background:#e7f6ea;color:#1e6b2d}
.small{font-size:13px;color:#6c757d}
.out{display:block;text-align:center;margin-top:18px;color:#6c757d}
</style>
</head>
<body>
<div class="box">
<h1>' . $t('title') . '</h1>
' . $flash . '
<p>' . $intro . '</p>
<form method="post" action="' . $action . '" autocomplete="off">
<input type="hidden" name="khewa2fa_action" value="verify">
<input type="hidden" name="khewa2fa_csrf" value="' . $csrf . '">
<label for="khewa2fa_code">' . $t('label') . '</label>
<input class="code" id="khewa2fa_code" name="khewa2fa_code" type="text" inputmode="numeric" pattern="[0-9 ]*" maxlength="7" autocomplete="one-time-code" required autofocus>
<label class="check"><input type="checkbox" name="khewa2fa_trust" value="1"> ' . $t('trust', $p['trust_days']) . '</label>
<button class="main" type="submit">' . $t('submit') . '</button>
</form>
<form method="post" action="' . $action . '">
<input type="hidden" name="khewa2fa_action" value="resend">
<input type="hidden" name="khewa2fa_csrf" value="' . $csrf . '">
<button class="second" type="submit">' . $t('resend') . '</button>
</form>
' . $wait . '
<a class="out" href="' . self::e($p['logout']) . '">' . $t('logout') . '</a>
</div>
</body>
</html>';
    }
}
