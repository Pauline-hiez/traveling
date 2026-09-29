<?php
// Ne pas afficher la modale si l'utilisateur est connecte
if (!empty($_SESSION['user'])) return;
?>

<div id="modal-auth" class="modal-overlay" role="dialog" aria-modal="true" aria-label="Authentification">
    <div class="modal auth-modal p-6 md:p-8">
        <button id="modal-auth-close" aria-label="Fermer" class="absolute top-4 right-4 z-10 text-xl leading-none opacity-70 hover:opacity-100 transition" style="background:none;border:none;color:var(--gold);cursor:pointer;">
            X
        </button>

        <!-- Onglets / alerte -->
        <div class="flex mb-5 border-b" style="border-color:rgba(255,211,157,0.2);">
            <button id="tab-register"
                class="flex-1 py-2 text-sm font-semibold transition-opacity"
                style="background:none;border:none;color:var(--gold);cursor:pointer;border-bottom:2px solid var(--gold-bright);">
                Inscription
            </button>
            <button id="tab-login"
                class="flex-1 py-2 text-sm font-semibold opacity-55 transition-opacity"
                style="background:none;border:none;color:var(--gold);cursor:pointer;border-bottom:2px solid transparent;">
                Connexion
            </button>
        </div>

        <!-- Connexion -->
        <div id="panel-login" class="hidden">
            <form id="form-login" class="flex flex-col gap-4" novalidate>
                <?= CsrfMiddleware::field() ?>
                <div>
                    <label class="block text-xs mb-1 opacity-75" style="color:var(--gold);">Email</label>
                    <input type="email" name="email" class="form-input" required autocomplete="email">
                </div>
                <div>
                    <label class="block text-xs mb-1 opacity-75" style="color:var(--gold);">Mot de passe</label>
                    <input type="password" name="password" class="form-input" required autocomplete="current-password">
                </div>
                <p class="text-right text-xs">
                    <a href="#" id="go-forgot" class="underline" style="color:var(--gold-bright);">Mot de passe oublié ?</a>
                </p>
                <button type="submit" class="btn w-full justify-center">Se connecter</button>
            </form>
            <form id="form-forgot" class="hidden mt-3 flex flex-col gap-3" novalidate>
                <?= CsrfMiddleware::field() ?>
                <label class="block text-xs opacity-75" style="color:var(--gold);">Recevoir un mot de passe temporaire par email</label>
                <input type="email" name="email" class="form-input" required autocomplete="email" placeholder="Votre email">
                <button type="submit" class="btn w-full justify-center">Envoyer</button>
                <p class="text-center text-xs">
                    <a href="#" id="go-back-login" class="underline" style="color:var(--gold-bright);">Retour à la connexion</a>
                </p>
            </form>
            <hr class="hr-gold my-4">
            <p class="text-center text-xs" style="color:var(--gold);">
                Pas encore inscrit ?
                <a href="#" id="go-register" class="underline" style="color:var(--gold-bright);">Inscrivez-vous</a>
            </p>
        </div>

        <!-- Inscription -->
        <div id="panel-register">
            <form id="form-register" class="flex flex-col gap-4" novalidate>
                <?= CsrfMiddleware::field() ?>
                <div>
                    <label class="block text-xs mb-1 opacity-75" style="color:var(--gold);">Pseudo</label>
                    <input type="text" name="pseudo" class="form-input" required minlength="3" autocomplete="username">
                </div>
                <div>
                    <label class="block text-xs mb-1 opacity-75" style="color:var(--gold);">Email</label>
                    <input type="email" name="email" class="form-input" required autocomplete="email">
                </div>
                <div>
                    <label class="block text-xs mb-1 opacity-75" style="color:var(--gold);">
                        Mot de passe <span class="opacity-60">(8 car. min)</span>
                    </label>
                    <input type="password" name="password" class="form-input" required minlength="8" autocomplete="new-password">
                </div>
                <div>
                    <label class="block text-xs mb-1 opacity-75" style="color:var(--gold);">Confirmer</label>
                    <input type="password" name="password_confirm" class="form-input" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn w-full justify-center">Créer mon compte</button>
            </form>
            <hr class="hr-gold my-4">
            <p class="text-center text-xs" style="color:var(--gold);">
                Déjà inscrit ?
                <a href="#" id="go-login" class="underline" style="color:var(--gold-bright);">Connectez-vous !</a>
            </p>
        </div>

        <hr class="hr-gold my-4">
        <div class="flex gap-3">
            <a href="<?= BASE_URL ?>auth/google" class="btn btn-ghost flex-1 justify-center gap-2 text-sm auth-google-btn">
                <img src="<?= ASSETS_URL ?>img/icons/google.png" alt="Google" class="no-sepia h-5 w-5">
                Google
            </a>
        </div>
    </div>
</div>