<?php $page_bg = ASSETS_URL . 'img/bg/hollywood.jpg'; ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="-mt-20 flex min-h-screen items-center justify-center px-4">
    <div class="bloc-cuir w-full max-w-md p-8">
        <h1 class="mb-4 text-2xl">Connexion</h1>
        <form method="POST" action="<?= BASE_URL ?>auth/login" class="flex flex-col gap-3">
            <?= CsrfMiddleware::field() ?>
            <input type="email" name="email" placeholder="Email" required class="form-input">
            <input type="password" name="password" placeholder="Mot de passe" required class="form-input">
            <button class="btn" type="submit">Se connecter</button>
        </form>
        <p class="mt-4 text-sm">Pas encore de compte ? <a href="<?= BASE_URL ?>register" class="opacity-90">Inscription</a></p>
    </div>
</main>