<?php
$faqs = [
    ["Qu'est-ce que Traveling ?",             "Traveling est un blog qui fait le lien entre le cinéma et les voyages en répertoriant les lieux de tournage de films à travers le monde."],
    ["Comment laisser un commentaire ?",      "Vous devez créer un compte et vous connecter pour pouvoir commenter les articles."],
    ["Comment ajouter un article en favori ?", "Cliquez sur l'étoile ★ présente sur chaque page d'article. Vos favoris sont accessibles depuis votre profil."],
    ["Qui peut publier des articles ?",       "Seuls les modérateurs et les administrateurs peuvent publier des articles."],
    ["Comment contacter l'équipe ?",          "Rendez-vous sur la page Contact et remplissez le formulaire. Nous vous répondrons rapidement."],
]
?>

<?php $page_bg = asset_url('frontend/assets/img/bg/velo.jpg'); ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="max-w-4xl mx-auto px-4 py-12">
    <img src="<?= asset_url('frontend/assets/img/icons/globe.png') ?>" alt="globe" class="globe-icon no-sepia">
    <h1 class="about-page__title section-title mt-3 text-2xl text-[var(--gold)] md:text-3xl">
        Foire aux questions (FAQ)
    </h1>

    <div class="bloc-cuir p-5 mb-8">
        <p class="text-sm leading-relaxed opacity-85 text-center">
            Bienvenue dans la section FAQ de traveling.<br>
            Vous trouverez ici les réponses aux questions les plus fréquentes posées sur notre site et notre concept.
        </p>
    </div>

    <div class="flex flex-col gap-3">
        <?php foreach ($faqs as $i => [$q, $a]): ?>
            <div>
                <button onclick="toggleFaq(<?= $i ?>)" class="bloc-cuir flex w-full cursor-pointer items-center justify-between gap-4 border-0 p-4 text-left text-sm font-semibold">
                    <span><?= htmlspecialchars($q) ?></span>
                    <span id="faq-icon-<?= $i ?>" class="flex-shrink-0 transition-transform">▼</span>
                </button>
                <div id="faq-ans-<?= $i ?>" class="bloc-papier bloc-papier--paper hidden rounded-b-[var(--radius-card)] border-t-0 p-4">
                    <p class="text-sm leading-relaxed"><?= htmlspecialchars($a) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>