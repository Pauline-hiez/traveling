<?php $page_bg = ASSETS_URL . 'img/bg/hobbit.jpg'; ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="-mt-20 flex min-h-screen flex-col items-center justify-center px-4 text-center">

    <!-- Globe + Titre 404 -->
    <div class="mb-8">
        <img src="<?= ASSETS_URL ?>img/icons/globe.png" alt="globe" class="globe-icon no-sepia mx-auto mb-6">
        <h1 class="m-0 text-8xl font-bold text-[var(--gold)] shadow-[0_4px_20px_rgba(0,0,0,.8)] md:text-9xl">— 404 —</h1>
    </div>

    <!-- Bloc de texte -->
    <div class="bloc-cuir p-8 max-w-4xl mb-8 rounded-full">
        <h2 class="mb-4 text-2xl font-bold text-[var(--gold)] md:text-3xl">
            Oups ! Il semble que vous vous soyez égaré en chemin.
        </h2>
        <p class="text-base md:text-lg leading-relaxed opacity-85">
            Cette page n'existe pas
        </p>
    </div>

    <!-- Bouton retour -->
    <a href="<?= BASE_URL ?>" class="btn">Retour à l'accueil</a>

</main>