<?php $page_bg = asset_url('frontend/assets/img/bg/voiture.jpg'); ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="admin-main max-w-[1400px] mx-auto px-4 sm:px-6 pb-20">
    <div class="admin-layout">
        <?php require COMPONENTS . 'admin-sidebar.php'; ?>

        <section class="admin-content">
            <div class="flex flex-wrap items-center gap-4 mb-6">
                <h1 class="admin-page-title text-[var(--gold)]">Gestion des signalements</h1>
            </div>
            <div id="admin-alert" class="hidden mb-4 rounded-lg px-4 py-2 text-sm font-semibold"></div>

            <?php if (empty($reports)): ?>
                <div class="bloc-cuir p-5">
                    <p class="text-sm opacity-60">Aucun signalement en attente.</p>
                </div>
            <?php else: ?>
                <div class="admin-table-shell overflow-x-auto">
                    <table class="admin-table min-w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-xs">Signalé par</th>
                                <th class="px-4 py-3 text-xs">Auteur</th>
                                <th class="px-4 py-3 text-xs">Commentaires</th>
                                <th class="px-4 py-3 text-xs">Motif</th>
                                <th class="px-4 py-3 text-xs">Statut</th>
                                <th class="px-4 py-3 text-xs">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $r): ?>
                                <tr>
                                    <td class="px-4 py-3 text-xs"><?= htmlspecialchars($r['reported']) ?></td>
                                    <td class="px-4 py-3 text-xs"><?= htmlspecialchars($r['reported']) ?></td>
                                    <td class="px-4 py-3 text-xs max-w-[180px] truncate" title="<?= htmlspecialchars($r['comment_content']) ?>"><?= htmlspecialchars(mb_substr($r['comment_content'], 0, 50)) ?>...</td>
                                    <td class="px-4 py-3 text-xs"><?= htmlspecialchars($r['reason']) ?></td>
                                    <td class="px-4 py-3 text-xs">
                                        <span class="badge <?= !empty($r['treated_at']) ? 'badge-admin' : 'badge-user' ?>">
                                            <?= !empty($r['treated_at']) ? 'Traité' : 'En attente' ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="admin-table-actions">
                                            <a class="btn admin-action-btn" href="<?= BASE_URL ?>articles/<?= (int)$r['article_id'] ?>#c<?= (int)$r['comment_id'] ?>" target="_blank" title="Voir" aria-label="Voir">
                                                👁
                                            </a>
                                            <button class="btn admin-action-btn admin-action-btn--warn" onclick="warnUser(<?= $r['comment_id'] ?>, '<?= addslashes(htmlspecialchars($r['comment_id'])) ?>')" title="Avertir" aria-label="Avertir">
                                                ⚠
                                            </button>
                                            <button class="btn admin-action-btn admin-action-btn--danger" onclick="deleteComment(<?= $r['comment_id'] ?>)" title="Supprimer" aria-label="Supprimer">
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
                    <?php $baseUrl = BASE_URL . 'admin/commentaires';
                    $query = [];
                    $page = $page;
                    $pages = $pages; ?>
                    <?php require COMPONENTS . 'pagination.php'; ?>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
</main>