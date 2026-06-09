<?php

require_once ROOT . '/backend/models/newsletterModel.php';
require_once ROOT . '/backend/models/articleModel.php';
require_once ROOT . '/backend/services/CssInlinerService.php';

class NewsletterService
{
    private Newsletter $newsletterModel;
    private Article $articleModel;

    private const MAX_ARTICLES = 3;

    public function __construct()
    {
        $this->newsletterModel = new Newsletter();
        $this->articleModel = new Article();
    }

    // Envoi hebdomadaire
    public function sendWeekly(): void
    {
        $emails = $this->newsletterModel->getAll();
        $content = $this->buildWeeklyContent();

        // Si aucun contenu dans la semaine, pas d'envoi
        if (empty($content['articles']) && empty($content['news'])) {
            echo "[Newsletter] Aucun contenu cette semaine, envoi annulé.\n";
            return;
        }

        $html = $this->renderTemplate($content);
        $subject = $this->buildSubject($content);

        $sent = 0;
        $failed = 0;

        foreach ($emails as $email) {
            // Lien de désabonnement unique par email
            $unsubToken = hash_hmac('sha256', $email, $_ENV['APP_KEY'] ?? 'traveling-secret');
            $unsubLink = BASE_URL . 'newsletter/desabonnement?email=' . urlencode($email) . '&token=' . $unsubToken;
            $finalHtml = str_replace('{{UNSUB_LINK}}', $unsubLink, $html);
            $success = MailService::send($email, $subject, $finalHtml);
            $success ? $sent++ : $failed++;

            // Pause pour ne pas surcharger le serveut SMTP
            usleep(100000); // 0.1 seconde entre chaque envoi
        }
        echo "[Newsletter] Envoi terminé : $sent succès, $failed échecs.\n";
        $this->logSend($sent, $failed, $subject);
    }

    private function buildWeeklyContent(): array
    {
        $since = date('Y-m-d 00:00:00', strtotime('monday this week'));

        // Nouveaux articles publiés cette semaine
        $articles = $this->articleModel->getPublishedSince($since, self::MAX_ARTICLES);

        // Actualités et fonctionnalités (texte fixe ou à faire évoluer)
        $news = $this->getWeeklyNews();
        $data = [
            'articles' => $articles,
            'news' => $news,
            'week_label' => $this->getWeekLabel(),
            'article_count' => count($articles),
        ];
        return array_merge($data, $this->prepareTemplateData($articles));
    }

    // Prépare toutes les donénes de présentation pour la template
    private function prepareTemplateData(array $articles): array
    {
        $urls = $this->loadEmailUrls();

        $featured = $articles[0] ?? null;
        $others = array_slice($articles, 1, 2);

        if ($featured) {
            $this->enrichArticle($featured, true, $urls);
        }

        foreach ($others as &$article) {
            $this->enrichArticle($article, false, $urls);
        }

        return [
            'logoSrc' => $urls['logo'],
            'heroBg' => $urls['hero'],
            'textureBg' => $urls['texture'],
            'featured' => $featured,
            'others' => $others,
        ];
    }

    // Charge les URL's des emails depuis la config
    private function loadEmailUrls(): array
    {
        return [
            'logo' => !empty($_ENV['MAIL_LOGO_URL'])
                ? $_ENV['MAIL_LOGO_URL']
                : BASE_URL . 'frontend/assets/img/logo/logo-traveling.png',
            'hero' => !empty($_ENV['MAIL_NEWSLETTER_HERO_URL'])
                ? $_ENV['MAIL_NEWSLETTER_HERO_URL']
                : BASE_URL . 'frontend/assets/img/bg/hollywood2.jpg',
            'texture' => !empty($_ENV['MAIL_TEXTURE_URL'])
                ? $_ENV['MAIL_TEXTURE_URL']
                : 'https://raw.githubusercontent.com/Pauline-hiez/assets/main/cuir.jpg',
            'cinema' => !empty($_ENV['MAIL_NEWSLETTER_CINEMA_IMAGE_URL'])
                ? $_ENV['MAIL_NEWSLETTER_CINEMA_IMAGE_URL']
                : 'https://raw.githubusercontent.com/Pauline-hiez/assets/main/cinema.jpg',
            'voyage' => !empty($_ENV['MAIL_NEWSLETTER_IMAGE_URL'])
                ? $_ENV['MAIL_NEWSLETTER_VOYAGE_IMAGE_URL']
                : 'https://raw.githubusercontent.com/Pauline-hiez/assets/main/voyage.jpg',
        ];
    }

    // Enrichit un article avec des images résolues et labels formatés
    private function enrichArticle(array &$article, bool $isFeatured, array $urls): void
    {
        // Ajoute les champs utilisés dans l'email
        $category = ($article['category'] ?? 'voyage') === 'cinema' ? 'cinema' : 'voyage';
        $article['resolved_image'] = $this->resolveArticleImage($article['img_cover'] ?? '', $category, $urls)
            ?: $urls[$category];

        $article['category_label'] = strtoupper(
            $isFeatured
                ? ($category === 'cinema' ? 'CINEMA' : 'DESTINATION')
                : ($category === 'cinema' ? 'FILM' : 'GUIDE')
        );

        $article['url'] = BASE_URL . 'articles/' . (int)($article['id'] ?? 0);
        $article['title_safe'] = htmlspecialchars($article['title'] ?? 'Sans titre', ENT_QUOTES, 'UTF-8');

        $maxLen = $isFeatured ? 180 : 80;
        $article['subtitle_safe'] = htmlspecialchars(
            mb_strimwidth((string)($article['subtitle'] ?? ''), 0, $maxLen, '...'),
            ENT_QUOTES,
            'UTF-8'
        );
    }

    // Résout le chemin d'une image d'article
    private function resolveArticleImage(?string $path, string $category, array $urls): string
    {
        $value = trim((string)($path ?? ''));

        if ($value === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        if (function_exists('asset_url')) {
            return asset_url($value);
        }

        return BASE_URL . ltrim($value, '/');
    }

    // Actualités manuelles de la semaine
    private function getWeeklyNews(): array
    {
        // Pour l'instant, contenu vide
        return [];
    }

    // Construit le sujet de l'email
    private function buildSubject(array $content): string
    {
        $week = $content['week_label'];
        if (($content['article_count'] ?? 0) > 0) {
            return "🎬 Traveling - " . $content['article_count'] . " nouveau" . ($content['article_count'] > 1 ? 'x articles' : 'article') . " cette semaine ($week)";
        }
        return "🎬 Traveling - Les actus de la semaine ($week)";
    }

    // Retourne le label de la semaine
    private function getWeekLabel(): string
    {
        return 'semaine du ' . date('j F Y', strtotime('last monday'));
    }

    // Rend la template HTML avec les données
    private function renderTemplate(array $content): string
    {
        $templateFile = ROOT . '/frontend/views/emails/newsletter.php';
        if (!file_exists($templateFile)) {
            throw new RuntimeException("Template email introuvable : $templateFile");
        }

        extract($content, EXTR_SKIP);

        ob_start();
        include $templateFile;
        $html = ob_get_clean();

        $cssFile = ROOT . '/frontend/assets/css/email.css';
        if (class_exists('\CssInliner')) {
            $css = file_exists($cssFile) ? file_get_contents($cssFile) : '';
            if (!empty($css)) {
                $inliner = new \CssInliner($css);
                $inlined = $inliner->convert($html);
                return $inlined;
            }
        }
        return $html;
    }

    // Journalise l'envoi dans un fichier log
    private function logSend(int $sent, int $failed, string $subject): void
    {
        $logFile = ROOT . '/backend/logs/newsletter.log';
        $dir = dirname($logFile);

        if (!is_dir($logFile)) mkdir($dir, 0755, true);

        $line = sprintf(
            "[%s] Sujet: \"%s\" | Envoyés: %d | Echecs: %d\n",
            date('Y-m-d H:i:s'),
            $subject,
            $sent,
            $failed
        );
        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
