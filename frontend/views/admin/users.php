<?php $page_bg = asset_url('frontend/assets/img/bg/voiture.jpg'); ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="admin-main max-w-[1400px] mx-auto px-4 sm:px-6 pb-20">
    <div class="admin-layout">
        <?php require COMPONENTS . 'admin-sidebar.php'; ?>

        <section class="admin-content">
            <div class="flex flex-wrap items-center gap-4 mb-6">
                <h1 class="admin-page-title text-[var(--gold)]">Gestion des utilisateurs</h1>
            </div>
            <div id="admin-alert" class="hidden mb-4 rounded-lg px-4 py-2 text-sm font-semibold"></div>

            <div class="admin-table-shell overflow-x-auto">
                <table class="admin-table min-w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-xs">Pseudo</th>
                            <th class="px-4 py-3 text-xs">Email</th>
                            <th class="px-4 py-3 text-xs">Rôle</th>
                            <th class="px-4 py-3 text-xs">Inscrit le</th>
                            <th class="px-4 py-3 text-xs">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="px-4 py-3 font-medium"><?= htmlspecialchars($u['pseudo']) ?></td>
                                <td class="px-4 py-3 text-xs opacity-80"><?= htmlspecialchars($u['email']) ?></td>
                                <td class="px-4 py-3">
                                    <span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span>
                                </td>
                                <td class="px-4 py-3 text-xs opacity-70"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                                <td class="px-4 py-3">
                                    <div class="admin-table-actions">
                                        <button class="btn admin-action-btn" onclick="viewUser(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['pseudo'])) ?>', '<?= htmlspecialchars(addslashes($u['email'])) ?>', '<?= htmlspecialchars(addslashes($u['role'])) ?>')" title="Voir" aria-label="Voir">
                                            👁
                                        </button>
                                        <select onchange="changeRole(<?= $u['id'] ?>, this.value); this.value = '';" class="form-input text-xs py-1 px-2 w-auto">
                                            <option value="">Changer de rôle</option>
                                            <option value="user">User</option>
                                            <option value="moderateur">Modérateur</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                        <button class="btn admin-action-btn admin-action-btn--danger" onclick="if(confirm('Supprimer cet utilisateur ?')) deleteUser(<?= $u['id'] ?>)" title="Supprimer" aria-label="Supprimer">
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
                <?php $baseUrl = BASE_URL . 'admin/utilisateurs';
                $query = [];
                $page = $page;
                $pages = $pages; ?>
                <?php require COMPONENTS . 'pagination.php'; ?>
            <?php endif; ?>
        </section>
    </div>

</main>