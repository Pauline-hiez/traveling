<?php
// Section active + stats pour la sidebar admin
$section = $adminSection ?? 'dashboard';
$sidebarStats = $adminSidebarStats ?? [];
?>

<aside class="admin-sidebar bloc-cuir sticky top-[5.25rem] mt-24 rounded-2xl p-4 shadow-[0_14px_34px_rgba(0,0,0,0.28)]">
    <h2 class="mb-4 text-xs uppercase tracking-[0.12em] text-[var(--gold-bright)]">Administration</h2>

    <nav class="flex flex-col gap-2.5" aria-label="Navigation admin">
        <a href="<?= BASE_URL ?>admin" class="flex w-full items-center justify-between gap-2 rounded-[0.8rem] border border-[rgba(255,211,157,0.25)] bg-[rgba(45,11,11,0.6)] px-3 py-3 text-[1.01rem] font-semibold text-[var(--gold)] transition hover:-translate-x-0.5 hover:border-[rgba(255,211,157,0.55)] hover:bg-[rgba(74,2,2,0.72)] hover:shadow-[0_8px_18px_rgba(0,0,0,0.2)] <?= $section === 'dashboard' ? 'bg-[rgba(122,38,16,0.75)] border-[rgba(245,158,53,0.72)] shadow-[0_0_0_1px_rgba(245,158,53,0.25)_inset]' : '' ?>">
            <span>Dashboard</span>
        </a>

        <a href="<?= BASE_URL ?>admin/articles" class="flex w-full items-center justify-between gap-2 rounded-[0.8rem] border border-[rgba(255,211,157,0.25)] bg-[rgba(45,11,11,0.6)] px-3 py-3 text-[1.01rem] font-semibold text-[var(--gold)] transition hover:-translate-x-0.5 hover:border-[rgba(255,211,157,0.55)] hover:bg-[rgba(74,2,2,0.72)] hover:shadow-[0_8px_18px_rgba(0,0,0,0.2)] <?= $section === 'articles' ? 'bg-[rgba(122,38,16,0.75)] border-[rgba(245,158,53,0.72)] shadow-[0_0_0_1px_rgba(245,158,53,0.25)_inset]' : '' ?>">
            <span>Articles</span>
            <span class="min-w-[1.7rem] rounded-full border border-[rgba(245,158,53,0.55)] bg-[rgba(245,158,53,0.18)] px-2 py-0.5 text-center text-[0.75rem] font-bold text-[var(--gold-bright)]"><?= (int)($sidebarStats['articles'] ?? 0) ?></span>
        </a>

        <a href="<?= BASE_URL ?>admin/utilisateurs" class="flex w-full items-center justify-between gap-2 rounded-[0.8rem] border border-[rgba(255,211,157,0.25)] bg-[rgba(45,11,11,0.6)] px-3 py-3 text-[1.01rem] font-semibold text-[var(--gold)] transition hover:-translate-x-0.5 hover:border-[rgba(255,211,157,0.55)] hover:bg-[rgba(74,2,2,0.72)] hover:shadow-[0_8px_18px_rgba(0,0,0,0.2)] <?= $section === 'users' ? 'bg-[rgba(122,38,16,0.75)] border-[rgba(245,158,53,0.72)] shadow-[0_0_0_1px_rgba(245,158,53,0.25)_inset]' : '' ?>">
            <span>Utilisateurs</span>
            <span class="min-w-[1.7rem] rounded-full border border-[rgba(245,158,53,0.55)] bg-[rgba(245,158,53,0.18)] px-2 py-0.5 text-center text-[0.75rem] font-bold text-[var(--gold-bright)]"><?= (int)($sidebarStats['users'] ?? 0) ?></span>
        </a>

        <a href="<?= BASE_URL ?>admin/commentaires" class="flex w-full items-center justify-between gap-2 rounded-[0.8rem] border border-[rgba(255,211,157,0.25)] bg-[rgba(45,11,11,0.6)] px-3 py-3 text-[1.01rem] font-semibold text-[var(--gold)] transition hover:-translate-x-0.5 hover:border-[rgba(255,211,157,0.55)] hover:bg-[rgba(74,2,2,0.72)] hover:shadow-[0_8px_18px_rgba(0,0,0,0.2)] <?= $section === 'comments' ? 'bg-[rgba(122,38,16,0.75)] border-[rgba(245,158,53,0.72)] shadow-[0_0_0_1px_rgba(245,158,53,0.25)_inset]' : '' ?>">
            <span>Signalements</span>
            <span class="min-w-[1.7rem] rounded-full border border-[rgba(245,158,53,0.55)] bg-[rgba(245,158,53,0.18)] px-2 py-0.5 text-center text-[0.75rem] font-bold text-[var(--gold-bright)]"><?= (int)($sidebarStats['reports_pending'] ?? 0) ?></span>
        </a>
    </nav>

    <button class="btn admin-sidebar__publish mt-4 w-full text-base font-bold" onclick="document.getElementById('modal-article')?.classList.add('open')">
        + Publier un article
    </button>
</aside>