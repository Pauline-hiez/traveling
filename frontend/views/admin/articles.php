<?php $page_bg = asset_url('frontend/assets/img/bg/voiture.jpg'); ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="admin-main max-w-[1400px] mx-auto px-4 sm:px-6 pb-20">
    <div class="admin-layout">
        <?php require COMPONENTS . 'admin-sidebar.php'; ?>

        <section class="admin-content">
            <div class="flex flex-wrap items-center gap-4 mb-6 justify-between">
                <h1 class="admin-page-title text-[var(--gold)]">Gestion des articles</h1>
                <button class="btn" onclick="document.getElementById('modal-article').classList.add('open')">+ Publier</button>
            </div>

            <div id="admin-alert" class="hidden mb-4 rounded-lg px-4 py-2 text-sm font-semibold"></div>
            <div class="admin-table-shell overflow-x-auto">
                <table class="admin-table min-w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-xs">Titre</th>
                            <th class="px-4 py-3 text-xs">Auteur</th>
                            <th class="px-4 py-3 text-xs">Catégorie</th>
                            <th class="px-4 py-3 text-xs">Statut</th>
                            <th class="px-4 py-3 text-xs">Date</th>
                            <th class="px-4 py-3 text-xs">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $a): ?>
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium max-w-[200px] truncate"><?= htmlspecialchars(mb_substr($a['title'], 0, 50)) ?><?= mb_strlen($a['title']) > 50 ? '...' : '' ?></td>
                                <td class="px-4 py-3 text-xs opacity-70"><?= htmlspecialchars($a['author']) ?></td>
                                <td class="px-4 py-3 text-xs"><?= ucfirst($a['category']) ?></td>
                                <td class="px-4 py-3 text-xs font-semibold <?= $a['published'] ? 'text-emerald-400' : 'text-rose-400' ?>">
                                    <?php if ((int)($a['published'] ?? 0) === 1): ?>
                                        <span class="text-emerald-400">Publié</span>
                                    <?php elseif ((int)($a['published'] ?? 0) === 2): ?>
                                        <span class="text-yellow-400">En attente</span>
                                    <?php else: ?>
                                        <span class="text-rose-400">Brouillon</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-xs opacity-70"><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                                <td class="px-4 py-3">
                                    <div class="admin-table-actions">
                                        <a href="<?= BASE_URL ?>articles/<?= $a['id'] ?>" target="_blank" class="btn admin-action-btn" title="Voir" aria-label="Voir">
                                            👁
                                        </a>
                                        <button class="btn admin-action-btn admin-action-btn--warn" onclick="openArticleEditor(<?= $a['id'] ?>)" title="Modifier" aria-label="Modifier">
                                            ✎
                                        </button>
                                        <button class="btn admin-action-btn admin-action-btn--danger"
                                            title="Supprimer" aria-label="Supprimer"
                                            onclick="if(confirm('Supprimer cet article ?')) deleteArticle(<?= $a['id'] ?>)">
                                            🗑
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pages > 1): ?>
                <?php $baseUrl = BASE_URL . 'admin/articles';
                $query = [];
                $page = $page;
                $pages = $pages; ?>
                <?php require COMPONENTS . 'pagination.php'; ?>
            <?php endif; ?>
        </section>
    </div>

</main>

<?php require VIEWS . 'admin/_modal-article.php'; ?>