<?php

require_once ROOT . '/backend/config/database.php';
require_once ROOT . '/backend/services/MailService.php';
require_once ROOT . '/backend/services/RendererService.php';
require_once ROOT . '/backend/services/JsonResponseService.php';
require_once ROOT . '/models/articleModel.php';
require_once ROOT . '/backend/models/newsletterModel.php';
require_once ROOT . '/backend/middleware/CsrfMiddleware.php';


class PageController
{
    public function contact(): void
    {
        // Affiche la page contact
        $renderer = new \Services\Renderer();
        $renderer->addParamsArray(['title' => 'Contact - Traveling']);
        $renderer->addScript(ASSETS_URL . 'js/contact.js');
        echo $renderer->render('pages/contact');
    }

    public function faq(): void
    {
        // Affiche la FAQ
        $renderer = new \Services\Renderer();
        $renderer->addParamsArray(['title' => 'FAQ - Traveling']);
        $renderer->addScript(ASSETS_URL . 'js/faq.js');
        echo $renderer->render('pages/faq');
    }

    public function about(): void
    {
        // Page à propos
        $renderer = new \Services\Renderer();
        $renderer->addParamsArray(['title' => 'À propos - Traveling']);
        echo $renderer->render('pages/about');
    }

    public function mentions(): void
    {
        // Page mentions légales
        $renderer = new \Services\Renderer();
        $renderer->addParamsArray(['title' - 'Mentions légales & CGU - Traveling']);
        echo $renderer->render('pages/mentions');
    }

    public function sendContact(): void
    {
        CsrfMiddleware::verify();

        // Validation des champs
        $pseudo = trim($_POST['pseudo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$pseudo || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject || $message) {
            JsonResponse::jsonError('Tous les champs sont obligatoires.');
            return;
        }

        // Corps HTML du message
        // $html = "<p><strong>De :</strong> $pseudo ($email)</p><p><strong>Objet :</strong> $subject</p><p>" . nl2br(htmlspecialchars($message)) . "</p>";
        // MailService::send($_ENV['MAIL_FROM'] ?? '', "Contact Traveling : $subject", $html);
        // $this->jsonSuccess('Message envoyé, nous vous répondrons rapidement.');
    }

    public function newsletter(): void
    {
        CsrfMiddleware::verify();

        // Validation de l'email
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            JsonResponse::jsonError('Email invalide.');
            return;
        }

        // Inscription newsletter
        $ok = (new NEwsletter())->subscribe($email);
        $ok ? JsonResponse::jsonSuccess('Inscription réussie !') : JsonResponse::jsonError('Email déjà inscrit.');
    }

    public function newsletterUnsubscribe(): void
    {
        // Vérification du lien de désinscription
        $email = trim($_GET['email'] ?? '');
        $token = trim($_GET['token'] ?? '');

        $renderer = new \Services\Renderer();
        $renderer->addParamsArray(['title' => 'Désabonnement newsletter - Traveling']);

        $expectedToken = '';
        if ($email !== '') {
            $expectedToken = hash_hmac('sha256', $email, $_ENV['APP_KEY'] ?? 'traveling-secret');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $token === '' || !hash_equals($expectedToken, $token)) {
            http_response_code(400);
            $renderer->addParamsArray([
                'success' => false,
                'message' => 'Le lien de désabonnement est invalide ou a expiré.',
            ]);
            echo $renderer->render('pages/newsletter-unsubscribe');
            return;
        }

        $ok = (new Newsletter())->unsubscribe($email);

        if (!$ok) {
            http_response_code(500);
            $renderer->addParamsArray([
                'success' => false,
                'message' => 'Une erreur est survenue pendant le désabonnement. Réessayez plus tard.',
            ]);
            echo $renderer->render('page/newsletter-unsubscribe');
            return;
        }

        $renderer->addParamsArray([
            'success' => true,
            'message' => 'Votre adresse a bien été retirée de la newsletter.',
        ]);
        echo $renderer->render('pages/newsletter-unsubscribe');
    }

    public function newsletterPreview(): void
    {
        // Aperçu de la newsletter
        $articleModel = new Article();
        $since = date('Y-m-d H:i:s', strtotime('-7 days'));
        $articles = $articleModel->getPublishedSince($since, 4);

        $renderer = new \Services\Renderer();
        $renderer->addParamsArray([
            'title' => 'Aperçu newsletter - Traveling',
            'week_label' => 'semaine du ' . date('j F Y', strtotime('last monday')),
            'articles' => $articles,
            'news' => [],
            'article_count' => count($articles),
        ]);
        echo $renderer->render('emails/newsletter');
    }
}
