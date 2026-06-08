<?php
$user = $profileUser ?? ($_SESSION['user'] ?? []);
$canManagePublishedArticles = isset($user['role']) && in_array($user['role'], ['moderateur', 'admin'], true);
$allFavorites ??= [];
$favorites ??= [];
$myArticles ??= [];
?>

<?php
// Load avatar/background positions from database
$positions = [
    'avatar_pos_x' => (int)($user['avatar_pos_x'] ?? 50),
    'avatar_pos_y' => (int)($user['avatar_pos_y'] ?? 50),
    'background_pos_x' => (int)($user['background_pos_x'] ?? 50),
    'background_pos_y' => (int)($user['background_pos_y'] ?? 50),
];
?>

<?php $page_bg = asset_url($user['background'] ?? $user['bg'] ?? null, 'frontend/assets/img/bg/hp.jpg'); ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>'); background-position: <?= htmlspecialchars($positions['background_pos_x']) ?>% <?= htmlspecialchars($positions['background_pos_y']) ?>%;"></div>

<main class="max-w-6xl mx-auto px-4 sm:px-6 pb-20">

    <!-- En tête profil -->
    <div class="profile-hero bloc-cuir p-5 md:p-6 mb-8">
        <div class="profile-hero__top">
            <div class="profile-hero__identity">
                <div class="profile-hero__avatar-block">
                    <img src="<?= asset_url($user['avatar'] ?? null, 'frontend/assets/img/avatars/avatar-defaut.jpg') ?>" alt="Avatar" class="no-sepia profile-hero__avatar" style="object-fit:cover; object-position: <?= htmlspecialchars($positions['avatar_pos_x']) ?>% <?= htmlspecialchars($positions['avatar_pos_y']) ?>%;">

                </div>

                <div class="profile-hero__content">
                    <h2 class="profile-hero__pseudo"><?= htmlspecialchars($user['pseudo'] ?? $user['name'] ?? '') ?></h2>
                    <p class="profile-hero__email"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                    <p class="profile-hero__since">Membre depuis <?= htmlspecialchars($memberSince ?? 'Date inconnue') ?></p>
                    <div class="profile-hero__role">
                        <?= strtoupper(htmlspecialchars($user['role'] ?? 'user')) ?>
                    </div>
                </div>
            </div>

            <div class="profile-hero__stats">
                <div class="profile-hero__stat">
                    <div class="profile-hero__stat-value"><?= (int)($favoriteCount ?? 0) ?></div>
                    <div class="profile-hero__stat-label">Favoris</div>
                </div>
                <div class="profile-hero__stat">
                    <div class="profile-hero__stat-value"><?= (int)($likeCount ?? 0) ?></div>
                    <div class="profile-hero__stat-label">Likes</div>
                </div>
                <div class="profile-hero__stat">
                    <div class="profile-hero__stat-value"><?= (int)($commentCount ?? 0) ?></div>
                    <div class="profile-hero__stat-label">Commentaires</div>
                </div>
                <?php if ($canManagePublishedArticles): ?>
                    <div class="profile-hero__stat">
                        <div class="profile-hero__stat-value"><?= (int)(count($myArticles ?? [])) ?></div>
                        <div class="profile-hero__stat-label">Articles publiés</div>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="profile-hero__actions">
            <div class="profile-hero__action profile-hero__action--left">
                <form id="form-avatar" enctype="multipart/form-data" method="POST" action="<?= BASE_URL ?>profil/avatar" class="profile-hero__upload">
                    <?= CsrfMiddleware::field() ?>
                    <label class="btn profile-hero__action-btn cursor-pointer">
                        Modifier mon avatar
                        <input type="file" name="avatar" accept="image/*" class="hidden" onchange="uploadProfileImage(this.form)">
                        <input type="hidden" name="avatar_pos_x" id="avatar_pos_x" value="<?= htmlspecialchars($positions['avatar_pos_x']) ?>">
                        <input type="hidden" name="avatar_pos_y" id="avatar_pos_y" value="<?= htmlspecialchars($positions['avatar_pos_y']) ?>">
                    </label>
                </form>
            </div>

            <div class="profile-hero__action profile-hero__action--center">
                <form id="form-bg" enctype="multipart/form-data" method="POST" action="<?= BASE_URL ?>profil/background" class="profile-hero__background">
                    <?= CsrfMiddleware::field() ?>
                    <label class="btn profile-hero__action-btn cursor-pointer whitespace-nowrap">
                        Modifier mon fond d'écran
                        <input type="file" name="background" accept="image/*" class="hidden" onchange="uploadProfileImage(this.form)">
                        <input type="hidden" name="background_pos_x" id="background_pos_x" value="<?= htmlspecialchars($positions['background_pos_x']) ?>">
                        <input type="hidden" name="background_pos_y" id="background_pos_y" value="<?= htmlspecialchars($positions['background_pos_y']) ?>">
                    </label>
                </form>
            </div>

            <div class="profile-hero__action profile-hero__action--right">
                <?php if ($canManagePublishedArticles): ?>
                    <button type="button" class="btn profile-hero__action-btn" onclick="document.getElementById('modal-article')?.classList.add('open')">Publier un article</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Contenu -->
    <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">

        <!-- Colonne gauche : Favoris -->
        <div class="w-full lg:flex-1 flex flex-col gap-4">
            <h2 class="section-title text-lg sm:text-xl">Mes articles favoris</h2>
            <?php if (empty($favorites)): ?>
                <div class="bloc-cuir p-5">
                    <p class="text-sm opacity-60">Aucun favori pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="hidden flex-col gap-4 sm:flex">
                    <?php foreach ($favorites as $article): ?>
                        <?php require COMPONENTS . 'big-cards.php'; ?>
                    <?php endforeach; ?>
                </div>
                <?php $favExtra = array_slice($allFavorites, FAVORITES_PREVIEW); ?>
                <?php if (!empty($favExtra)): ?>
                    <div id="fav-extra" class="hidden flex-col gap-4">
                        <?php foreach ($favExtra as $article): ?>
                            <?php require COMPONENTS . 'big-cards.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 md:gap-6 mb-10 sm:hidden">
                        <?php foreach (array_slice($allFavorites, 0, FAVORITES_PREVIEW) as $article): ?>
                            <?php require COMPONENTS . 'small-cards.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($allFavorites) > FAVORITES_PREVIEW): ?>
                        <div id="fav-extra-mobile" class="hidden grid grid-cols-1 sm:grid-cols-3 gap-5 md:gap-6 mb-10 sm:hidden">
                            <?php foreach (array_slice($allFavorites, FAVORITES_PREVIEW) as $article): ?>
                                <?php require COMPONENTS . 'small-cards.php'; ?>
                            <?php endforeach; ?>
                        </div>
                        <button id="btn-fav-toggle" class="btn self-start" data-toggle="fav-extra,fav-extra-mobile" aria-expanded="false">Voir plus</button>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 md:gap-6 mb-10 sm:hidden">
                        <?php foreach ($favorites as $article): ?>
                            <?php require COMPONENTS . 'small-cards.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($canManagePublishedArticles): ?>
                <h2 class="section-title text-lg sm:text-xl mt-6">Mes articles publiés</h2>
                <?php if (empty($myArticles)): ?>
                    <div class="bloc-cuir p-5">
                        <p class="text-sm opacity-60">Vous n'avez pas encore publié d'articles.</p>
                    </div>
                <?php else: ?>
                    <?php $myPreview = array_slice($myArticles, 0, FAVORITES_PREVIEW); ?>
                    <?php $myExtra = array_slice($myArticles, FAVORITES_PREVIEW); ?>
                    <div class="hidden flex-col gap-4 sm:flex">
                        <?php foreach ($myPreview as $article): ?>
                            <?php require COMPONENTS . 'big-cards.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($myExtra)): ?>
                        <div id="my-articles-extra" class="hidden flex-col gap-4">
                            <?php foreach ($myExtra as $article): ?>
                                <?php require COMPONENTS . 'big-cards.php'; ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 md:gap-6 mb-10 sm:hidden">
                            <?php foreach (array_slice($myArticles, 0, FAVORITES_PREVIEW) as $article): ?>
                                <?php require COMPONENTS . 'small-cards.php'; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($myArticles) > FAVORITES_PREVIEW): ?>
                            <div id="my-articles-extra-mobile" class="hidden grid grid-cols-1 sm:grid-cols-3 gap-5 md:gap-6 mb-10 sm:hidden">
                                <?php foreach (array_slice($myArticles, FAVORITES_PREVIEW) as $article): ?>
                                    <?php require COMPONENTS . 'small-cards.php'; ?>
                                <?php endforeach; ?>
                            </div>
                            <button id="btn-my-articles-toggle" class="btn self-start" data-toggle="my-articles-extra,my-articles-extra-mobile" aria-expanded="false">Voir plus</button>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 md:gap-6 mb-10 sm:hidden">
                            <?php foreach ($myPreview as $article): ?>
                                <?php require COMPONENTS . 'small-cards.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Colonne droite : Actions + historique -->
        <aside class="w-full lg:w-72 flex flex-col gap-5 lg:sticky lg:top-4">

            <!-- Actions -->
            <div class="bloc-cuir p-5 flex flex-col gap-3">
                <h3 class="text-xs uppercase tracking-widest mb-1">Actions</h3>
                <button class="btn w-full justify-center" onclick="openModal('modal-email')">Modifier mon email</button>
                <button class="btn w-full justify-center" onclick="openModal('modal-pseudo')">Modifier mon pseudo</button>
                <button class="btn w-full justify-center" onclick="openModal('modal-password')">Modifier mon mot de passe</button>
                <button class="btn btn-danger w-full justify-center" onclick="if(confirm('Supprimer définitivement votre compte ?')) deleteAccount()">
                    Supprimer mon compte
                </button>
            </div>

            <!-- Hitorique -->
            <div class="bloc-cuir p-5 flex flex-col gap-3">
                <h3 class="mb-1 text-xs uppercase tracking-widest text-[var(--gold-bright)]">Historique</h3>
                <?php if (empty($history)): ?>
                    <p class="text-xs opacity-60">Aucune action enregistrée.</p>
                <?php else: ?>
                    <?php foreach (array_slice($history, 0, HISTORY_PREVIEW) as $h): ?>
                        <div class="border-b border-[rgba(255,211,157,.12)] pb-2 text-xs">
                            <p><?= htmlspecialchars($h['action']) ?></p>
                            <p class="opacity-50 mt-0.5"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                    <?php $extraH = array_slice($history, HISTORY_PREVIEW); ?>
                    <?php if (!empty($extraH)): ?>
                        <div id="hist-extra" class="hidden flex-col gap-3">
                            <?php foreach ($extraH as $h): ?>
                                <div class="border-b border-[rgba(255,211,157,.12)] pb-2 text-xs">
                                    <p><?= htmlspecialchars($h['action']) ?></p>
                                    <p class="opacity-50 mt-0.5"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="btn btn-sm self-start" data-toggle="hist-extra">Voir plus</button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</main>

<?php if ($canManagePublishedArticles): ?>
    <?php require VIEWS . 'admin/_modal-article.php'; ?>
<?php endif; ?>

<!-- Modal action profil -->
<?php foreach (
    [
        ['modal-email', 'Modifier mon email', 'form-email', [['email', 'email', 'Nouvel email']]],
        ['modal-pseudo', 'Modifier mon pseudo', 'form-pseudo', [['text', 'pseudo', 'Nouveau pseudo']]],
        ['modal-password', 'Modifier mon mot de passe', 'form-password', [
            ['password', 'current_password', 'Mot de passe actuel'],
            ['password', 'new_password', 'Nouveau mot de passe (8min)'],
            ['password', 'confirm_password', 'Confirmer'],
        ]],
    ] as [$id, $title, $fid, $fields]
): ?>

    <div id="<?= $id ?>" class="modal-overlay">
        <div class="modal p-6">
            <button class="absolute top-4 right-4 z-10 opacity-70 hover:opacity-100 transition" onclick="closeModal('<?= $id ?>')">
                X
            </button>
            <h3 class="text-sm uppercase tracking-wide mb-4"><?= $title ?></h3>
            <div id="<?= $fid ?>-alert" class="hidden mb-3 rounded-lg px-3 py-2 text-sm"></div>
            <form id="<?= $fid ?>" class="flex flex-col gap-4">
                <?= CsrfMiddleware::field() ?>
                <?php foreach ($fields as [$type, $name, $label]): ?>
                    <div>
                        <label class="block text-xs opacity-70 mb-1"><?= $label ?></label>
                        <input type="<?= $type ?>" name="<?= $name ?>" class="form-input" required>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn w-full justify-center">Enregistrer</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>