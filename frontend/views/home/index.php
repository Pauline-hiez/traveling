<?php
$popular ??= null;
$latest ??= [];
$page_bg = ASSETS_URL . 'img/bg/hollywood.jpg';
?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="mx-auto max-w-[1100px] px-4 pb-16">

    <!-- Globe + Titre -->
    <div class="mb-8 text-center">
        <img src="<?= ASSETS_URL ?>img/icons/globe.png" alt="globe" class="globe-icon no-sepia">
        <h1 class="section-title mt-3 text-2xl text-[var(--gold)] md:text-3xl">
            EXPLOREZ LE MONDE A TRAVERS LE CINEMA
        </h1>
    </div>

    <!-- Article populaire -->
    <?php if ($popular): ?>
        <p class="mb-2 text-xs opacity-70">
            Publié le <?= date('d M Y', strtotime($popular['created_at'])) ?>
        </p>

        <div class="relative mb-12 overflow-hidden rounded-[var(--radius-card)] border border-[var(--gold)] bg-[rgba(20,5,5,0.35)] shadow-[0_18px_40px_rgba(0,0,0,0.35)]">
            <?php
            // Construire une source d'image fiable pour l'article populaire uniquement
            $pimg = $popular['img_cover'] ?? $popular['img_illus'] ?? '';
            if (empty($pimg)) {
                $src = ASSETS_URL . 'img/placeholder.jpg';
            } elseif (preg_match('~^https?://~i', $pimg)) {
                $src = $pimg;
            } elseif (strpos($pimg, 'assets/') !== false) {
                // si le chemin contient 'assets/' (p.ex. 'frontend/assets/img/...'),
                // on récupère la portion après 'assets/' et on la préfixe par ASSETS_URL
                $pos = strpos($pimg, 'assets/');
                $src = ASSETS_URL . substr($pimg, $pos + strlen('assets/'));
            } else {
                // chemin relatif simple
                $src = ASSETS_URL . ltrim($pimg, '/');
            }
            ?>
            <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($popular['title']) ?>" class="h-[420px] w-full object-cover">
            <div class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-[#140505]/90 via-[#140505]/45 to-transparent p-6 md:p-8">
                <h2 class="mb-2 max-w-[600px] font-title text-[clamp(1.2rem,3vw,2rem)] text-[var(--gold)]">
                    <?= htmlspecialchars($popular['title']) ?>
                </h2>
                <?php if (!empty($popular['subtitle'])): ?>
                    <p class="mb-4 max-w-[500px] text-[0.95rem] opacity-85">
                        <?= htmlspecialchars($popular['subtitle']) ?>
                    </p>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>articles/<?= $popular['id'] ?>" class="btn w-fit self-start">
                    Lire l'article
                </a>
            </div>

            <!-- Stats -->
            <div class="absolute bottom-4 right-4 flex gap-3 rounded-md bg-[rgba(20,5,5,0.6)] px-3 py-2 text-sm text-[var(--gold)] backdrop-blur-[2px]">
                <span>♥ <?= (int)$popular['likes'] ?></span>
                <span>💬 <?= (int)$popular['comments'] ?></span>
                <span>★ <?= (int)$popular['favorites'] ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Derniers articles -->
    <div class="mb-10">
        <h2 class="section-title text-xl md:text-2xl mb-2">Derniers articles</h2>
        <hr class="hr-gold mb-6">

        <?php if (!empty($latest)): ?>
            <p class="mb-3 text-xs opacity-70">
                Publié le <?= date('d F Y', strtotime($latest[0]['created_at'])) ?>
            </p>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($latest as $article): ?>
                <?php require COMPONENTS . 'small-cards.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="text-center">
        <a href="<?= BASE_URL ?>articles" class="btn">Voir tous les articles</a>
    </div>

</main>