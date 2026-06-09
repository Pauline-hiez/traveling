<?php $page_bg = asset_url('frontend/assets/img/bg/voiture.jpg'); ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="admin-main max-w-[1400px] mx-auto px-4 sm:px-6 pb-20">
    <div class="admin-layout">
        <?php require COMPONENTS . 'admin-sidebar.php'; ?>

        <section class="admin-content">
            <div class="mb-8 md:mb-10">
                <h1 class="admin-page-title text-[var(--gold)]">
                    Bonjour <?= htmlspecialchars($user['pseudo'] ?? 'admin') ?>
                </h1>
                <p class="admin-page-lead">Vue d'ensemble des catégories du back-office.</p>
            </div>

            <div class="admin-stats-grid mb-6">
                <article class="admin-stat-card bloc-cuir p-4">
                    <p class="admin-stat-card__label">Articles</p>
                    <p class="admin-stat-card__value"><?= (int)($stats['articles']['total'] ?? 0) ?></p>
                    <p class="admin-stat-card__meta">Publiés: <?= (int)($stats['articles']['published'] ?? 0) ?> - Brouillons: <?= (int)($stats['articles']['draft'] ?? 0) ?></p>
                </article>

                <article class="admin-stat-card bloc-cuir p-4">
                    <p class="admin-stat-card__label">Catégorie Cinéma</p>
                    <p class="admin-stat-card__value"><?= (int)($stats['articles']['cinema'] ?? 0) ?></p>
                    <p class="admin-stat-card__meta">Articles liés au cinéma et aux séries</p>
                </article>

                <article class="admin-stat-card bloc-cuir p-4">
                    <p class="admin-stat-card__label">Catégorie Voyage</p>
                    <p class="admin-stat-card__value"><?= (int)($stats['articles']['voyage'] ?? 0) ?></p>
                    <p class="admin-stat-card__meta">Articles orientés lieux et destinations</p>
                </article>

                <article class="admin-stat-card bloc-cuir p-4">
                    <p class="admin-stat-card__label">Utilisateurs</p>
                    <p class="admin-stat-card__value"><?= (int)($stats['users']['total'] ?? 0) ?></p>
                    <p class="admin-stat-card__meta">Admins: <?= (int)($stats['users']['admin'] ?? 0) ?> - Modérateurs: <?= (int)($stats['users']['moderateur'] ?? 0) ?></p>
                </article>

                <article class="admin-stat-card bloc-cuir p-4">
                    <p class="admin-stat-card__label">Signalements en attente</p>
                    <p class="admin-stat-card__value"><?= (int)($stats['reports']['pending'] ?? 0) ?></p>
                    <p class="admin-stat-card__meta">Total signalements: <?= (int)($stats['reports']['total'] ?? 0) ?></p>
                </article>
            </div>
        </section>
    </div>
</main>

<?php require VIEWS . 'admin/_modal-article.php'; ?>