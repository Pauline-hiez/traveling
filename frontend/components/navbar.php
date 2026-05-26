<?php
// Navbar: rafraichit le role pour les droits admin
$user = $_SESSION['user'] ?? null;
$userId = (int)($user['id'] ?? 0);

if ($userId > 0) {
    if (!class_exists('User')) {
        require_once ROOT . '/backend/models/userModel.php';
    }

    $freshUser = (new User())->findById($userId);
    if (is_array($freshUser) && isset($freshUser['role'])) {
        $user['role'] = $freshUser['role'];
        $_SESSION['user']['role'] = $freshUser['role'];
    }
}

$current = basename($_SERVER['PHP_SELF']);

?>

<!-- Injection BASE_URL pour JS -->
<script>
    const BASE_URL = '<?= BASE_URL ?>';
</script>
<meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

<!-- Logo -->
<div id="logo-top" class="py-4 text-center">
    <a href="<?= BASE_URL ?>">
        <img src="<?= ASSETS_URL ?>img/logo/logo-traveling-jaune.png" alt="Traveling" class="no-sepia mx-auto h-[70px] w-auto">
        <p class="mt-1 font-title text-sm tracking-[0.3rem] text-[var(--gold)]">TRAVELING</p>
    </a>
</div>

<!-- Navbar -->
<nav id="main-navbar" class="relative mx-auto mb-8 max-w-[900px] w-[calc(100%-1rem)] overflow-hidden rounded-[22px] border border-[rgba(255,211,157,0.14)] bg-transparent shadow-none backdrop-blur-[6px] lg:rounded-[18px]" aria-label="Navigation principale">
    <!-- Fond étoilé -->
    <canvas id="navbar-stars" class="pointer-events-none absolute inset-0 h-full w-full" aria-hidden="true"></canvas>

    <!-- Vignette très légère pour garder la lisibilité sans bloc opaque -->
    <div class="pointer-events-none absolute inset-0 rounded-[22px] bg-[radial-gradient(circle_at_center,rgba(0,0,0,0.12),rgba(0,0,0,0.02)_58%,rgba(0,0,0,0)_100%)] lg:rounded-[18px]" aria-hidden="true"></div>

    <div class="relative z-10 flex items-center justify-between px-4 py-3 sm:px-6">
        <!-- Logo inlive -->
        <a id="nav-logo" href="<?= BASE_URL ?>" class="logo-inline hidden shrink-0 no-sepia">
            <img src="<?= ASSETS_URL ?>img/logo/logo-traveling-jaune.png" alt="Traveling" class="no-sepia h-9 w-auto">
        </a>

        <!-- Menu hamburger (responsive) -->
        <button id="nav-burger" type="button" class="relative z-20 inline-flex shrink-0 items-center justify-center border-0 bg-transparent p-2 leading-none text-[1.5rem] text-[var(--gold)] pointer-events-auto lg:hidden" aria-label="Menu" aria-expanded="false">
            ☰
        </button>

        <!-- Liens -->
        <ul id="nav-menu" class="hidden w-full flex-wrap items-center justify-center gap-6 list-none text-[0.95rem] text-[var(--gold)] lg:flex">
            <li><a href="<?= BASE_URL ?>" class="transition hover:text-white">Accueil</a></li>
            <li><a href="<?= BASE_URL ?>articles" class="transition hover:text-white">Articles</a></li>
            <li><a href="<?= BASE_URL ?>lieux" class="transition hover:text-white">Lieux de tournage</a></li>
            <li><a href="<?= BASE_URL ?>films" class="transition hover:text-white">Films &amp; Séries</a></li>

            <!-- Btn auth/profil -->
            <?php if ($user): ?>
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <li>
                        <a href="<?= BASE_URL ?>admin" class="btn px-4 py-1.5 text-sm">
                            Admin
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>profil" class="btn px-4 py-1.5 text-sm">
                            Mon profil
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?= BASE_URL ?>profil" class="btn px-4 py-1.5 text-sm">
                            Mon profil
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="<?= BASE_URL ?>auth/deconnexion" class="btn px-4 py-1.5 text-sm">
                        Déconnexion
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <button id="btn-auth" class="btn px-4 py-1.5 text-sm">Inscription / Connexion</button>
                </li>
            <?php endif ?>
        </ul>
    </div>

    <!-- Menu mobile déroulé -->
    <div id="nav-menu-mobile" class="relative z-20 hidden flex flex-col gap-3 px-6 pb-4 text-[var(--gold)] pointer-events-auto lg:hidden lg:px-0 lg:pb-4">
        <a href="<?= BASE_URL ?>" class="transition hover:text-white">Accueil</a>
        <a href="<?= BASE_URL ?>articles" class="transition hover:text-white">Articles</a>
        <a href="<?= BASE_URL ?>lieux" class="transition hover:text-white">Lieux de tournage</a>
        <a href="<?= BASE_URL ?>films" class="transition hover:text-white">Films &amp; Séries</a>
        <?php if ($user): ?>
            <?php if (($user['role'] ?? '') === 'admin'): ?>
                <li>
                    <a href="<?= BASE_URL ?>admin" class="btn w-full justify-center text-center">
                        Admin
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>profil" class="btn w-full justify-center text-center">
                        Mon profil
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <a href="<?= BASE_URL ?>profil" class="btn w-full justify-center text-center">
                        Mon profil
                    </a>
                </li>
            <?php endif; ?>
            <li>
                <a href="<?= BASE_URL ?>auth/deconnexion" class="btn w-full justify-center text-center">
                    Déconnexion
                </a>
            </li>
        <?php else: ?>
            <li>
                <button id="btn-auth-mobile" class="btn px-4 py-1.5 text-sm">Inscription / Connexion</button>
            </li>
        <?php endif ?>
    </div>
</nav>

<script>
    // Synchronise le burger avec le menu mobile
    document.getElementById('btn-auth-mobile')?.addEventListener('click', () => {
        document.getElementById('btn-auth')?.click();
    });
</script>
<script src="<?= ASSETS_URL ?>js/navbar.js" defer></script>