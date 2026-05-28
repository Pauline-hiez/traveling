<?php $page_bg = ASSETS_URL . 'img/bg/newsletter.jpg'; ?>

<?php
$success = $success ?? false;
$message = $message ?? 'Une erreur est survenue.';
$buttonLabel = $success ? 'Retour à l’accueil' : 'Retour à l’accueil';
?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="-mt-20 flex min-h-screen flex-col items-center justify-center px-4 text-center">
    <div class="mb-8">
        <img src="<?= ASSETS_URL ?>img/icons/globe.png" alt="globe" class="globe-icon no-sepia mx-auto mb-6">
        <h1 class="m-0 text-6xl font-bold text-[var(--gold)] md:text-5xl">
            <?= $success ? 'Désabonnement confirmé' : 'Lien invalide' ?>
        </h1>
    </div>

    <div class="bloc-cuir p-8 max-w-4xl mb-8 rounded-full">
        <h2 class="mb-4 text-xl font-bold text-[var(--gold)] md:text-xl">
            <?= htmlspecialchars($message) ?>
        </h2>
        <p class="text-base md:text-lg leading-relaxed opacity-85">
            <?= $success ? 'Vous ne recevrez plus les prochains emails de newsletter.' : 'Si besoin, vous pouvez réessayer depuis le dernier email reçu.' ?>
        </p>
    </div>

    <a href="<?= BASE_URL ?>" class="btn"><?= htmlspecialchars($buttonLabel) ?></a>
</main>