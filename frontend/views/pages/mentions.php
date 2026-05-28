<?php $page_bg = ASSETS_URL . 'img/bg/paris-bg.jpg'; ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="max-w-5xl mx-auto px-4 sm:px-6 pb-20">
    <h1 class="section-title text-xl sm:text-2xl md:text-3xl mb-2">Mentions légales &amp; CGU</h1>
    <img src="<?= ASSETS_URL ?>img/icons/globe.png" class="no-sepia mx-auto w-14 md:w-20 mb-6">
    <div class="bloc-cuir p-5 max-w-2xl mx-auto text-center mb-8">
        <p class="text-sm leading-relaxed opacity-85">Bienvenue sur Traveling.<br>
            Afin de garantir une utilisation conforme et sécurisée de notre site, veuillez prendre connaissance de nos mentions légales et CGU.</p>
    </div>
    <div class="grid grid-cols-1 overflow-hidden rounded-xl border border-[var(--gold)] md:grid-cols-2">
        <div class="bloc-cuir flex flex-col gap-4 border-0 p-5 md:rounded-none md:border-r md:border-[rgba(255,211,157,.2)]">
            <?php foreach (
                [
                    ["Editeur du site", "Traveling est édité par une équipe de passionnés de cinéma et de voyages."],
                    ["Hébergement", "Le site est hébergé via Plesk sur un serveur dédié sécurisé."],
                    ["Propriété intellectuelle", "Tout le contenu est protégé par le droit d'auteur. Toute reproduction est interdite."],
                ] as [$t, $c]
            ): ?>
                <div>
                    <h3 class="mb-2 text-xs uppercase tracking-wider text-[var(--gold-bright)]"><?= $t ?></h3>
                    <p class="text-xs leading-relaxed opacity-80"><?= $c ?></p>
                </div>
                <hr class="hr-gold">
            <?php endforeach; ?>
        </div>
        <div class="bloc-cuir flex flex-col gap-4 border-0 p-5 md:rounded-none">
            <?php foreach (
                [
                    ["Données personnelles", "Les données collectées sont utilisées uniquement pour le fonctionnement du site. Droit d'accès et suppression disponibles."],
                    ["Cookies", "Uniquement des cookies de session pour l'authentification. Auncun cookie publicitaire."],
                    ["Conditions d'utilisation", "En utilisant ce site, vous vous engagez à respecter les règles : respect mutuel, pas de contenu illégal."],
                ] as [$t, $c]
            ): ?>
                <div>
                    <h3 class="mb-2 text-xs uppercase tracking-wider text-[var(--gold-bright)]"><?= $t ?></h3>
                    <p class="text-xs leading-relaxed opacity-80"><?= $c ?></p>
                </div>
                <hr class="hr-gold">
            <?php endforeach; ?>
        </div>
    </div>
</main>