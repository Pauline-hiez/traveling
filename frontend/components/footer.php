<footer class="relative mt-16 overflow-hidden">
    <?php // Footer global 
    ?>
    <!-- Texture cuir avec vraie transparence -->
    <div class="absolute inset-0 z-0 bg-[url('<?= ASSETS_URL ?>img/texture/cuir.jpg')] bg-cover bg-center opacity-60"></div>

    <!-- Voile léger pour garder la lisibilité du texte -->
    <div class="absolute inset-0 z-10 bg-[rgba(20,5,5,0.22)]"></div>

    <!-- HR défradés -->
    <div class="relative z-20">
        <hr class="hr-gold m-0">
        <hr class="hr-gold m-0">
        <hr class="hr-gold m-0">
    </div>

    <div class="relative z-20 flex flex-wrap items-start justify-between gap-8 px-4 py-8 sm:px-8">
        <!-- logo gauche -->
        <div class="flex flex-col items-start gap-2">
            <img src="<?= ASSETS_URL ?>img/logo/logo-traveling.png" alt="Traveling" class="no-sepia h-14 w-auto">
            <p class="font-title text-[0.95rem] tracking-[0.3rem] text-[var(--gold)]">TRAVELING</p>
            <p class="text-[0.75rem] text-[var(--gold)] opacity-70">EXPLOREZ LE MONDE À TRAVERS LE CINÉMA</p>
        </div>

        <!-- Newsletter -->
        <div class="flex min-w-[220px] max-w-[360px] flex-1 flex-col gap-3">
            <h3 class="font-title text-[1.1rem] uppercase tracking-[0.1em] text-[var(--gold)]">NEWSLETTER</h3>
            <p class="text-[0.82rem] text-[var(--gold)] opacity-80">Recevez les dernières informations</p>

            <div id="newsletter-alert" class="alert hidden"></div>

            <form id="form-newsletter" class="flex gap-2">
                <?= CsrfMiddleware::field() ?>
                <input type="email" name="email" placeholder="Votre email..." required class="flex-1">
                <button type="submit" class="btn whitespace-nowrap">S'inscrire</button>
            </form>
        </div>

        <!-- Liens droite -->
        <nav aria-label="Liens utiles">
            <ul class="flex list-none flex-col gap-2 text-[0.9rem] text-[var(--gold)]">
                <li><a href="<?= BASE_URL ?>about" class="transition hover:text-white">À propos</a></li>
                <li><a href="<?= BASE_URL ?>mentions-legales" class="transition hover:text-white">Mentions légales</a></li>
                <li><a href="<?= BASE_URL ?>faq" class="transition hover:text-white">FAQ</a></li>
                <li><a href="<?= BASE_URL ?>contact" class="transition hover:text-white">Contact</a></li>
            </ul>
        </nav>
    </div>
</footer>

<script src="<?= ASSETS_URL ?>js/scrollbar.js"></script>