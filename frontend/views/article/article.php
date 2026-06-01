<?php

// Valeurs par défaut pour éviter les notices/linter lorsque la vue est ouverte seule
$user = $_SESSION['user'] ?? null;
$article = $article ?? [];
$comments = isset($comments) && is_array($comments) ? $comments : [];
$parents = $parents ?? null;
$children = $children ?? null;

// Si le contrôleur a déjà préparé les variables, ne pas les recalculer (évite les doublons/mismatches)
if (!is_array($parents)) {
    $parents = array_values(array_filter($comments, fn($c) => ($c['parent_id'] ?? null) === null));
}
if (!is_array($children)) {
    $children = [];
    foreach ($comments as $c) {
        if (!empty($c['parent_id'])) $children[$c['parent_id']][] = $c;
    }
}

?>

<?php $page_bg = asset_url($article['img_bg'] ?? 'frontend/assets/img/bg/cinema.jpg'); ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="max-w-6xl mx-auto px-4 sm:px-6 pb-20" data-article-id="<?= (int)$article['id'] ?>">

    <!-- Titre -->
    <div class="text-center mb-6 md:mb-8">
        <h1 class="px-2 text-2xl leading-tight text-[var(--gold)] sm:text-3xl md:text-4xl">
            <?= htmlspecialchars($article['title']) ?>
        </h1>
        <?php if (!empty($article['subtitle'])): ?>
            <p class="mt-2 text-sm md:text-base opacity-80 max-w-2xl mx-auto">
                <?= htmlspecialchars($article['subtitle']) ?>
            </p>
        <?php endif; ?>
        <img src="<?= asset_url('frontend/assets/img/icons/globe.png') ?>" alt="globe" class="no-sepia mx-auto w-12 md:w-16 mt-3">
    </div>

    <hr class="hr-gold mb-6 md:mb-8">

    <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">
        <!-- Colonne de gauche -->
        <div class="w-full lg:flex-1 flex flex-col gap-6">
            <!-- Image article + stats -->
            <div class="relative rounded-xl overflow-hidden border border-yellow-300/40">
                <img src="<?= asset_url($article['img_illus'] ?? 'frontend/assets/img/placeholder.jpg') ?>"
                    alt="<?= htmlspecialchars($article['img_caption'] ?? '') ?>"
                    class="w-full max-h-96 object-cover block">
                <!-- Date + auteur -->
                <span class="absolute left-3 top-3 rounded-md bg-[rgba(20,5,5,.75)] px-2 py-1 text-xs text-[var(--gold)]">
                    <?= date('d/m/Y H:i', strtotime($article['created_at'])) ?>
                    &nbsp;•&nbsp;
                    <span class="opacity-90">Publié par <?= htmlspecialchars($article['author'] ?? ($article['pseudo'] ?? 'Anonyme')) ?></span>
                </span>
                <!-- Stats -->
                <div class="absolute right-3 top-3 flex gap-2 rounded-md bg-[rgba(20,5,5,.75)] px-2 py-1 text-xs text-[var(--gold)]">
                    <span>♥ <?= (int)$article['likes'] ?></span>
                    <span>💬 <?= (int)$article['comments'] ?></span>
                    <span>★ <?= (int)$article['favorites'] ?></span>
                </div>
                <!-- Légende -->
                <?php if (!empty($article['img_caption'])): ?>
                    <div class="bloc-papier bloc-papier--paper absolute bottom-0 left-0 right-0 rounded-none border-x-0 border-b-0 px-4 py-4 text-center text-base font-semibold leading-snug text-[#2D0000] md:text-lg">
                        <span class="block w-full text-center text-[#2D0000]">
                            <?= htmlspecialchars($article['img_caption']) ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Contenu -->
            <div class="bloc-cuir p-5 md:p-6">
                <p class="article-content-text text-sm md:text-base leading-relaxed">
                    <?= nl2br(htmlspecialchars($article['content'])) ?>
                </p>
            </div>
            <!-- Citation -->
            <?php if (!empty($article['quote'])): ?>
                <div class="bloc-papier bloc-papier--paper border-l-4 border-l-[var(--gold-bright)] p-4 text-base italic md:p-5 md:text-lg">
                    <span class="block w-full text-center">
                        <?= htmlspecialchars($article['quote']) ?>
                    </span>
                </div>
            <?php endif; ?>
            <!-- Anecdote -->
            <?php if (!empty($article['anecdote'])): ?>
                <div class="bloc-cuir p-5 md:p-6">
                    <h3 class="mb-3 text-xs uppercase tracking-widest text-[var(--gold-bright)]">Anecdote</h3>
                    <p class="text-sm leading-relaxed"><?= nl2br(htmlspecialchars($article['anecdote'])) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Colonne droite -->
        <aside class="w-full lg:w-72 flex flex-col gap-4 lg:sticky lg:top-4">
            <!-- Films -->
            <?php if (!empty($tmdbFilms)): ?>
                <div class="bloc-cuir p-4">
                    <h3 class="mb-4 text-xs uppercase tracking-widest text-[var(--gold-bright)]">Films tournés ici</h3>
                    <div class="flex flex-col gap-3">
                        <?php foreach (array_slice($tmdbFilms, 0, 3) as $film): ?>
                            <a href="<?= BASE_URL ?>films/<?= $film['id'] ?>" class="flex items-center gap-3 text-[var(--gold)] no-underline transition hover:opacity-80">
                                <img src="<?= TmdbService::imgUrl($film['poster_path'] ?? null, 'w92') ?>" class="w-10 rounded flex-shrink-0">
                                <span class="text-xs leading-snug"><?= htmlspecialchars($film['title']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($tmdbFilms) > 3): ?>
                        <div id="films-extra" class="hidden flex-col gap-3 mt-3">
                            <?php foreach (array_slice($tmdbFilms, 3) as $film): ?>
                                <a href="<?= BASE_URL ?>films/<?= $film['id'] ?>"
                                    class="flex items-center gap-3 text-[var(--gold)] no-underline transition hover:opacity-80">
                                    <img src="<?= TmdbService::imgUrl($film['poster_path'] ?? null, 'w92') ?>"
                                        alt="" class="w-10 rounded flex-shrink-0">
                                    <span class="text-xs leading-snug"><?= htmlspecialchars($film['title']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <button class="btn btn-sm w-full mt-3" data-toggle="films-extra">Voir plus</button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Lieux + Carte -->
            <?php if (!empty($lieux)): ?>
                <div class="bloc-cuir p-4">
                    <h3 class="mb-3 text-xs uppercase tracking-widest text-[var(--gold-bright)]">Lieux de tournage</h3>
                    <div id="map" class="mb-3 h-[180px] rounded-lg border border-[var(--gold)]" data-lieux="<?= htmlspecialchars(json_encode($lieux, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES) ?>"></div>
                    <div class="flex flex-col gap-1">
                        <?php foreach ($lieux as $lieu): ?>
                            <a href="<?= BASE_URL ?>lieux/<?= $lieu['id'] ?>"
                                class="text-xs text-[var(--gold)] no-underline transition hover:opacity-80">
                                📍 <?= htmlspecialchars($lieu['name']) ?><?= $lieu['country'] ? ', ' . htmlspecialchars($lieu['country']) : '' ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Like / Favori -->
            <?php if ($user): ?>
                <div class="bloc-cuir p-4 flex gap-3 justify-center">
                    <button class="btn flex-1 justify-center<?= !empty($userLiked) ? ' active' : '' ?>" data-like="<?= $article['id'] ?>" aria-pressed="<?= !empty($userLiked) ? 'true' : 'false' ?>">
                        <span class="toggle-icon like-icon"><?= !empty($userLiked) ? '♥' : '♡' ?></span>
                        <span class="like-count ml-1"><?= (int)$article['likes'] ?></span>
                    </button>
                    <button class="btn flex-1 justify-center<?= !empty($userFavorited) ? ' active' : '' ?>" data-favori="<?= $article['id'] ?>" aria-pressed="<?= !empty($userFavorited) ? 'true' : 'false' ?>">
                        <span class="toggle-icon favorite-icon"><?= !empty($userFavorited) ? '★' : '☆' ?></span>
                        <span class="ml-1">Favori</span>
                    </button>
                </div>
            <?php endif; ?>
        </aside>
    </div>

    <!-- Commentaires -->
    <section class="bloc-cuir p-5 md:p-6 mt-8 md:mt-12">
        <h2 class="mb-5 text-base uppercase tracking-wide text-[var(--gold)]">
            Avis et commentaires
            <span class="text-xs opacity-60 normal-case ml-2">(<?= count($comments) ?>)</span>
        </h2>

        <?php foreach ($parents as $i => $comment): ?>
            <div class="comment <?= $i >= COMMENTS_PREVIEW ? 'hidden' : '' ?> mb-4" id="c<?= $comment['id'] ?>">
                <div class="flex justify-between items-start gap-3 mb-2">
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="<?= asset_url($comment['avatar'] ?? 'assets/img/default-avatar.png') ?>" alt="Avatar de <?= htmlspecialchars($comment['pseudo']) ?>" class="w-9 h-9 rounded-full object-cover border border-[rgba(255,211,157,.35)] flex-shrink-0">
                        <div class="min-w-0">
                            <strong class="text-sm block leading-tight"><?= htmlspecialchars($comment['pseudo']) ?></strong>
                            <small class="text-xs opacity-50 block"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php if ($user): ?>
                        <div class="flex gap-2 flex-wrap justify-end">
                            <button class="btn btn-sm" onclick="openReply(<?= $comment['id'] ?>)">↩ Répondre</button>
                            <button class="btn btn-sm" data-comment-like="<?= $comment['id'] ?>" aria-pressed="<?= !empty($comment['liked_by_user']) ? 'true' : 'false' ?>">
                                <span class="comment-like-icon"><?= !empty($comment['liked_by_user']) ? '♥' : '♡' ?></span>
                                <span class="ml-1 comment-like-count"><?= (int)($comment['likes_count'] ?? 0) ?></span>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="openReport(<?= $comment['id'] ?>)">⚑ Signaler</button>
                        </div>
                    <?php endif; ?>
                </div>
                <p class="text-sm leading-relaxed mb-2"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>

                <!-- Réponses -->
                <?php if (!empty($children[$comment['id']])): ?>
                    <div class="mt-3 ml-4 flex flex-col gap-3 border-l-2 border-[rgba(255,211,157,.2)] pl-4">
                        <?php foreach ($children[$comment['id']] as $reply): ?>
                            <div class="comment-reply" id="c<?= $reply['id'] ?>">
                                <div class="flex items-start gap-2 mb-1">
                                    <img src="<?= asset_url($reply['avatar'] ?? 'assets/img/default-avatar.png') ?>" alt="Avatar de <?= htmlspecialchars($reply['pseudo']) ?>" class="w-7 h-7 rounded-full object-cover border border-[rgba(255,211,157,.25)] flex-shrink-0">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex justify-between gap-2">
                                            <strong class="text-xs"><?= htmlspecialchars($reply['pseudo']) ?></strong>
                                            <small class="text-xs opacity-50"><?= date('d/m/Y H:i', strtotime($reply['created_at'])) ?></small>
                                        </div>
                                        <p class="text-xs leading-relaxed mt-1"><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                                    </div>
                                </div>
                                <?php if ($user): ?>
                                    <div class="flex gap-2 flex-wrap mt-2 ml-9">
                                        <button class="btn btn-sm" onclick="openReply(<?= $comment['id'] ?>)">↩ Répondre</button>
                                        <button class="btn btn-sm btn-danger" onclick="openReport(<?= $reply['id'] ?>)">⚑ Signaler</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <hr class="hr-gold my-3">
        <?php endforeach; ?>

        <?php if (count($parents) > COMMENTS_PREVIEW): ?>
            <button class="btn btn-sm mb-4" id="btn-more-comments">Voir plus de commentaires</button>
        <?php endif; ?>

        <div class="text-center mt-2">
            <?php if ($user): ?>
                <button class="btn" onclick="openComment()">Laisser un commentaire</button>
            <?php else: ?>
                <button class="btn" id="btn-auth">Connectez-vous pour commenter</button>
            <?php endif; ?>
        </div>
</main>

<!-- Modal commentaires + Signalements -->
<?php if ($user): ?>
    <div id="modal-comment" class="modal-overlay">
        <div class="modal p-6">
            <button class="absolute top-4 right-4 z-10 opacity-70 hover:opacity-100 cursor-pointer border-0 bg-transparent text-[1.3rem] text-[var(--gold)]" onclick="document.getElementById('modal-comment').classList.remove('open')">✕</button>
            <h3 class="text-base uppercase tracking-wide mb-4">Laisser un commentaire</h3>
            <div id="comment-alert" class="hidden mb-3 rounded-lg px-3 py-2 text-sm"></div>
            <form id="form-comment" class="flex flex-col gap-4">
                <?= CsrfMiddleware::field() ?>
                <input type="hidden" name="parent_id" id="comment-parent-id" value="">
                <div>
                    <label class="block text-xs mb-1 opacity-70">Pseudo</label>
                    <input type="text" value="<?= htmlspecialchars($user['pseudo']) ?>" disabled class="form-input opacity-60">
                </div>
                <div>
                    <label class="block text-xs mb-1 opacity-70">Commentaire <span class="opacity-50">(max 1000 car.)</span></label>
                    <textarea name="content" rows="4" maxlength="1000" required class="form-input resize-none"></textarea>
                </div>
                <button type="submit" class="btn w-full justify-center">Envoyer</button>
            </form>
        </div>
    </div>

    <div id="modal-report" class="modal-overlay">
        <div class="modal p-6">
            <button class="absolute top-4 right-4 z-10 opacity-70 hover:opacity-100 cursor-pointer border-0 bg-transparent text-[1.3rem] text-[var(--gold)]" onclick="document.getElementById('modal-report').classList.remove('open')">✕</button>
            <h3 class="text-base uppercase tracking-wide mb-4">Signaler ce commentaire</h3>
            <form id="form-report" class="flex flex-col gap-4">
                <?= CsrfMiddleware::field() ?>
                <input type="hidden" id="report-cid" value="">
                <div>
                    <label class="block text-xs mb-1 opacity-70">Motif</label>
                    <input type="text" name="reason" required class="form-input" placeholder="Ex : contenu inapproprié…">
                </div>
                <button type="submit" class="btn w-full justify-center">Envoyer le signalement</button>
            </form>
        </div>
    </div>
<?php endif; ?>