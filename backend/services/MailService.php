<?php

require_once __DIR__ . '/CssInlinerService.php';
require_once __DIR__ . '/../models/articleModel.php';

class MailService
{
    public static function send(string $to, string $subject, string $htmlBody): bool
    {
        $transport = strtolower(trim((string)($_ENV['MAIL_TRANSPORT'] ?? 'smtp')));

        if ($transport === 'brevo_api') {
            return self::sendViaBrevoApi($to, $subject, $htmlBody);
        }
        return self::sendSmtp($to, $subject, $htmlBody);
    }

    private static function sendViaBrevoApi(string $to, string $subject, string $htmlBody): bool
    {
        // Vérifie la présence de la clé API
        $apiKey = trim((string)($_ENV['BREVO_API_KEY'] ?? ''));
        if ($apiKey === '') {
            self::logMailDebug('brevo_api', $to, 'no-key', 'BREVO_API_KEY not set');
            return false;
        }

        $fromEmail = trim((string)($_ENV['MAIL_FROM'] ?? 'noreply@localhost'));
        $fromName = trim((string)($_ENV['MAIL_FROM_NAME'] ?? 'Traveling'));

        $payload = [
            'sender' => ['name' => $fromName, 'email' => $fromEmail],
            'to' => [['email' => $to]],
            'subject' => $subject,
            'htmlContent' => $htmlBody,
            'textContent' => strip_tags($htmlBody),
            'headers' => [
                'List-Unsubscribe' => '<mailto:' . $fromEmail . '>'
            ],
            'replyTo' => ['email' => $fromEmail, 'name' => $fromName],
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        self::logMailDebug('brevo_api_resp', $to, (string)$httpCode, $err ?: ($resp !== false ? (is_string($resp) ? $resp : json_encode($resp)) : 'no-response'));
        return $resp !== false && ($httpCode >= 200 && $httpCode < 300);
    }

    private static function sendSmtp(string $to, string $subject, string $htmlBody): bool
    {
        $host = trim((string)($_ENV['MAIL_HOST'] ?? ''));
        $port = (int)($_ENV['MAIL_PORT'] ?? 587);
        $from = trim((string)($_ENV['MAIL_FROM'] ?? ''));
        $name = trim((string)($_ENV['MAIL_FROM_NAME'] ?? 'Traveling'));
        $username = trim((string)($_ENV['MAIL_USERNAME'] ?? ''));
        $password = trim((string)($_ENV['MAIL_PASSWORD'] ?? ''));
        $encryption = strtolower(trim((string)($_ENV['MAIL_ENCRYPTION'] ?? 'tls')));

        if ($host === '' || $from === '' || $username === '' || $password == '') {
            return false;
        }

        if ($encryption === '' || $encryption === 'none') {
            $encryption = 'tls';
        }

        $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $socket = @stream_socket_client($remote, $errno, $errstr, 10);
        if (!$socket) {
            self::logMailDebug('smtp_connect', $to, $host . ':' . $port, $errstr ?: 'connection failed');
            return false;
        }

        $subject = self::encodeSubject($subject);
        $headers = implode("\r\n", [
            'From: ' . $name . ' <' . $from . '>',
            'To: ' . $to,
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=utf-8',
        ]);

        $ok = self::smtpExpect($socket, [220])
            && self::smtpCommand($socket, 'EHLO ' . ($host ?: 'localhost'), [250]);

        if ($ok && $encryption === 'tls') {
            $ok = self::smtpCommand($socket, 'STARTLS', [220])
                && stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)
                && self::smtpCommand($socket, 'EHLO ' . ($host ?: 'localhost'), [250]);
        }

        if ($ok && $username !== '' && $password !== '') {
            $ok = self::smtpCommand($socket, 'AUTH LOGIN', [334])
                && self::smtpCommand($socket, base64_encode($username), [334])
                && self::smtpCommand($socket, base64_encode($password), [235]);
        }

        $ok = $ok
            && self::smtpCommand($socket, 'MAIL FROM:<' . $from . '>', [250])
            && self::smtpCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251])
            && self::smtpCommand($socket, 'DATA', [354]);

        if ($ok) {
            fwrite($socket, $headers . "\r\n\r\n" . $htmlBody . "\r\n.\r\n");
            $ok = self::smtpExpect($socket, [250]);
        }

        self::smtpCommand($socket, 'QUIT', [221]);
        fclose($socket);

        self::logMailDebug('smtp_result', $to, $host . ':' . $port, $ok ? 'sent' : 'failed');
        return $ok;
    }

    // Email de bienvenue après inscription
    public static function sendWelcome(string $to, string $pseudo): bool
    {
        // Construit le template et envoie
        $heroUrl = trim((string)($_ENV['MAIL_WELCOME_HERO_URL'] ?? 'https://row/githubusercontent.com/Pauline-hiez/assets/main/bienvenue.jpg'));
        $bodyHtml = self::renderEmailTemplate('emails/bienvenue', [
            'pseudo' => $pseudo,
        ]);
        $html = self::buildBrandedEmail('Bienvenue sur Traveling', $bodyHtml, 'Voir le site', BASE_URL, $heroUrl);
        return self::send($to, 'Bienvenue sur Traveling !', $html);
    }

    // Email d'avertissment (admin -> utilisateur signalé)
    public static function sendWarning(string $to, string $pseudo, string $reason, string $commentContent = ''): bool
    {
        // Construit le template et envoie
        $heroUrl = trim((string)($_ENV['MAIL_WARNING_HERO_URL'] ?? 'https://raw.githubusercontent.com/Pauline-hiez/assets/main/avertissement.jpg'));
        $bodyHtml = self::renderEmailTemplate('emails/avertissement', [
            'pseudo' => $pseudo,
            'reason' => $reason,
            'commentContent' => $commentContent,
        ]);
        $html = self::buildBrandedEmail('Avertissement', $bodyHtml, null, null, $heroUrl);
        return self::send($to, 'Avertissement - Traveling', $html);
    }

    public static function sendPasswordReset(string $to, string $pseudo, string $resetLink): bool
    {
        $heroUrl = trim((string)($_ENV['MAIL_PASSWORD_RESET_HERO_URL'] ?? 'https://raw.githubusercontent.com/Pauline-hiez/assets/main/mdp.jpg'));
        $bodyHtml = self::renderEmailTemplate('emails/mdp', [
            'pseudo' => $pseudo,
        ]);
        $html = self::buildBrandedEmail('Réinitialisation du mot de passe', $bodyHtml, 'Changer mon mot de passe', $resetLink, $heroUrl);

        return self::send($to, 'Réinitialisation du mot de passe - Traveling', $html);
    }

    // Newsletter
    public static function sendNewsletter(array $emails, string $subject, string $htmlBody): void
    {
        foreach ($emails as $email) {
            self::send($email, $subject, $htmlBody);
        }
    }

    private static function smtpCommand($socket, string $command, array $expectedCodes): bool
    {
        fwrite($socket, $command . "\r\n");
        $result = self::smtpExpect($socket, $expectedCodes);
        $respSummary = $result ? 'ok' : 'no-match';
        self::logMailDebug('smtp_command', '', $command, $respSummary);
        return $result;
    }

    private static function smtpExpect($socket, array $expectedCodes): bool
    {
        // Attend une réponse SMTP valide
        $response = '';
        while (!feof($socket)) {
            $line = fgets($socket, 515);
            if ($line === false) {
                break;
            }
            $response .= $line;
            if (preg_match('/^(\d{3})\s/', $line, $match)) {
                $code = (int)$match[1];
                $matched = in_array($code, $expectedCodes, true);
                if ($matched) {
                    // Log d'une réponse valide
                    self::logMailDebug('smtp_response', '', (string)$code, trim($line));
                    return true;
                }
                self::logMailDebug('smtp_unexpected', '', (string)$code, trim($line) . ' | full:' . str_replace("\n", '\\n', $response));
                return false;
            }
        }

        // Aucune réponse valide
        self::logMailDebug('smtp_noresponse', '', 'n/a', 'no response from server | full:' . str_replace("\n", '\\n', $response));
        return false;
    }

    private static function encodeSubject(string $subject): string
    {
        return function_exists('mb_encode_mimeheader')
            ? mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n")
            : $subject;
    }

    private static function renderEmailTemplate(string $template, array $data = []): string
    {
        // Charge un template PHP
        $templateFile = ROOT . '/frontend/views/' . ltrim($template, '/') . '.php';

        if (!file_exists($templateFile)) {
            throw new RuntimeException('Email teplate not found: ' . $templateFile);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $templateFile;
        return (string)ob_get_clean();
    }

    private static function buildBrandedEmail(string $title, string $contentHtml, ?string $ctaText = null, ?string $ctaLink = null, ?string $heroImageUrl = null): string
    {
        // Construit l'email HTML
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeCtaText = $ctaText !== null ? htmlspecialchars($ctaText, ENT_QUOTES, 'UTF-8') : null;
        $textureUrl = trim((string)($_ENV['MAIL_TEXTURE_URL'] ?? 'https://raw.githubusercontent.com/Pauline-hiez/assets/main/cuir.jpg'));
        $textureStyle = $textureUrl !== ''
            ? 'background-color:#3a281c;background-image:linear-gradient(rgba(45, 29, 20, 0.84), rgba(45, 29, 20, 0.84)), url(' . htmlspecialchars($textureUrl, ENT_QUOTES, 'UTF-8') . ');background-size:cover;'
            : 'background-color:#3a281c;';

        $heroHtml = '';
        $heroSrc = trim((string)($heroImageUrl ?? ''));
        if ($heroSrc !== '') {
            $heroHtml = '<img class="mail-transactional__hero" src="' . htmlspecialchars($heroSrc, ENT_QUOTES, 'UTF-8') . '" alt="' . $safeTitle . '" style="display:block;width:100%;max-width:100%;height:auto;border:0;margin:0 0 16px 0;">';
        }
        $ctaHtml = '';
        if ($safeCtaText !== null && !empty($ctaLink)) {
            $safeCtaLink = htmlspecialchars($ctaLink, ENT_QUOTES, 'UTF-8');
            $ctaHtml = "<p class=\"mail-transactional__cta-wrap\"><a class=\"mail-transactional__cta\" href=\"{$safeCtaLink}\">{$safeCtaText}</a></p>";
        }

        $html = "<!doctype html>
<html lang=\"fr\">
<head>
    <meta charset=\"utf-8\" />
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />
    <title>{$safeTitle}</title>
</head>
<body>
    <div class=\"mail-transactional\" style=\"{$textureStyle}\">{$heroHtml}
        <h1 class=\"mail-transactional__title\">{$safeTitle}</h1>
        <div class=\"mail-transactional__content\">{$contentHtml}{$ctaHtml}</div>
        <p class=\"mail-transactional__footer\">Cet email a ete envoye automatiquement par Traveling.</p>
    </div>
</body>
</html>";

        $cssFile = ROOT . '/frontend/assets/css/email.css';
        $css = file_exists($cssFile) ? (string)file_get_contents($cssFile) : '';

        // Inlining CSS si possible
        if ($css !== '' && class_exists('CssInliner')) {
            $inliner = new CssInliner($css);
            return $inliner->convert($html);
        }

        return $html;
    }

    private static function renderLatestPreview(array $articles): string
    {
        // Construit une section HTML pour les derniers articles
        if (empty($articles)) return '';
        $html = '<section class="newsletter-section"><h2 class="newsletter-section__title">Derniers articles</h2><div class="newsletter-articles">';
        foreach ($articles as $article) {
            $img = '';
            if (!empty($article['img_cover'])) {
                if (str_starts_with($article['img_cover'], 'http')) {
                    $img = $article['img_cover'];
                } else {
                    $img = (defined('BASE_URL') ? BASE_URL : '') . ltrim($article['img_cover'], '/');
                }
            }
            $title = htmlspecialchars($article['title'] ?? 'Sans titre', ENT_QUOTES, 'UTF-8');
            $subtitle = htmlspecialchars(mb_substr($article['subtitle'] ?? '', 0, 80), ENT_QUOTES, 'UTF-8');
            $id = (int)($article['id'] ?? 0);

            $html .= '<article class="newsletter-card">';
            if ($img !== '') {
                $html .= '<img src="' . htmlspecialchars($img, ENT_QUOTES, 'UTF-8') . '" alt="' . $title . '" class="newsletter-card__image">';
            } else {
                $html .= '<div class="newsletter-card__placeholder">🎬</div>';
            }
            $html .= '<div class="newsletter-card__body">';
            $html .= '<p class="newsletter-card__category">' . htmlspecialchars($article['category'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
            $html .= '<h3 class="newsletter-card__title">' . $title . '</h3>';
            $html .= '<p class="newsletter-card__subtitle">' . $subtitle . (strlen($subtitle) ? '...' : '') . '</p>';
            $html .= '<a href="' . (defined('BASE_URL') ? BASE_URL : '') . 'articles/' . $id . '" class="newsletter-btn">Lire</a>';
            $html .= '</div></article>';
        }
        $html .= '</div></section>';
        return $html;
    }

    private static function logMailDebug(string $stage, string $to, string $target, string $message): void
    {
        // Ecrit un log technique dans logs/
        $logDir = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $line = sprintf(
            "%s | %s | to=%s | target=%s | %s\n",
            date('Y-m-d H:i:s'),
            $stage,
            $to,
            $target,
            $message
        );

        @file_put_contents($logDir . '/mail_send.log', $line, FILE_APPEND);
    }
}
