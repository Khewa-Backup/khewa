<?php
/**
 * Khewa staff login code: the words shown on the code page and in the email subject.
 * English, and French for staff whose language is fr or qc.
 */

namespace KhewaTwoFactor;

final class Texts
{
    private const WORDS = [
        'en' => [
            'title' => 'Login code',
            'intro' => 'We sent a 6-digit code to %s. Type it below. It works for %d minutes.',
            'intro_none' => 'Ask for a code with the button below. We will email it to you.',
            'label' => 'Code from the email',
            'trust' => 'Trust this computer for %d days',
            'submit' => 'Check code',
            'resend' => 'Send a new code',
            'resend_wait' => 'You can ask for a new code in %d seconds.',
            'logout' => 'Log out',
            'ok_sent' => 'A new code is on its way. Check your email.',
            'err_wrong' => 'That code is wrong. You have %d tries left.',
            'err_expired' => 'That code is too old. Ask for a new code.',
            'err_locked' => 'Too many wrong tries. This code no longer works. Ask for a new code.',
            'err_none' => 'There is no code to check. Ask for a new code.',
            'err_form' => 'The form timed out. Please try again.',
            'err_wait' => 'Please wait %d seconds before asking for a new code.',
            'err_cap' => 'Too many codes this hour. Try again in %d minutes.',
            'err_send' => 'The email could not be sent. Try again in a minute, or ask the site owner.',
            'err_mail_off' => 'Email is turned off in this shop, so no code can be sent. Ask the site owner.',
            'ajax' => 'Login code needed. Reload the page and type the code from your email.',
            'mail_subject' => 'Your login code',
        ],
        'fr' => [
            'title' => 'Code de connexion',
            'intro' => 'Nous avons envoyé un code à 6 chiffres à %s. Entrez-le ci-dessous. Il est valide %d minutes.',
            'intro_none' => 'Demandez un code avec le bouton ci-dessous. Nous vous l\'enverrons par courriel.',
            'label' => 'Code reçu par courriel',
            'trust' => 'Faire confiance à cet ordinateur pendant %d jours',
            'submit' => 'Vérifier le code',
            'resend' => 'Envoyer un nouveau code',
            'resend_wait' => 'Vous pourrez demander un nouveau code dans %d secondes.',
            'logout' => 'Se déconnecter',
            'ok_sent' => 'Un nouveau code est en route. Vérifiez vos courriels.',
            'err_wrong' => 'Ce code est erroné. Il vous reste %d essais.',
            'err_expired' => 'Ce code est trop vieux. Demandez un nouveau code.',
            'err_locked' => 'Trop d\'essais ratés. Ce code ne fonctionne plus. Demandez un nouveau code.',
            'err_none' => 'Aucun code à vérifier. Demandez un nouveau code.',
            'err_form' => 'Le formulaire a expiré. Veuillez réessayer.',
            'err_wait' => 'Veuillez attendre %d secondes avant de demander un nouveau code.',
            'err_cap' => 'Trop de codes cette heure-ci. Réessayez dans %d minutes.',
            'err_send' => 'Le courriel n\'a pas pu être envoyé. Réessayez dans une minute ou contactez le responsable du site.',
            'err_mail_off' => 'Les courriels sont désactivés dans cette boutique, aucun code ne peut être envoyé. Contactez le responsable du site.',
            'ajax' => 'Code de connexion requis. Rechargez la page et entrez le code reçu par courriel.',
            'mail_subject' => 'Votre code de connexion',
        ],
    ];

    public static function iso(string $langIso): string
    {
        return in_array(strtolower($langIso), ['fr', 'qc'], true) ? 'fr' : 'en';
    }

    public static function get(string $key, string $iso, int|string ...$args): string
    {
        $text = self::WORDS[$iso][$key] ?? self::WORDS['en'][$key] ?? $key;

        return $args ? sprintf($text, ...$args) : $text;
    }
}
