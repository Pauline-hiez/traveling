<?php $page_bg = ASSETS_URL . 'img/bg/hollywood.jpg'; ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="-mt-20 flex min-h-screen items-center justify-center px-4">
    <div class="bloc-cuir w-full max-w-md p-8">
        <h1 class="mb-4 text-2xl">Réinitialisation du mot de passe</h1>

        <?php if (!empty($message)): ?>
            <p class="mb-4 text-sm text-red-200"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if (!empty($validToken)): ?>
            <form method="POST" action="<?= BASE_URL ?>auth/reinitialiser-mot-de-passe" class="flex flex-col gap-3">
                <?= CsrfMiddleware::field() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="password" name="password" placeholder="Nouveau mot de passe" required minlength="8" class="form-input">
                <input type="password" name="password_confirm" placeholder="Confirmer" required minlength="8" class="form-input">
                <button class="btn" type="submit">Mettre à jour</button>
            </form>
        <?php else: ?>
            <p class="text-sm opacity-80">Retournez à la connexion pour demander un nouveau lien.</p>
            <a href="<?= BASE_URL ?>?login=1" class="btn mt-4">Demander un nouveau lien</a>
            <a href="<?= BASE_URL ?>" class="btn mt-4">Retour à l'accueil</a>
        <?php endif; ?>
    </div>
</main>