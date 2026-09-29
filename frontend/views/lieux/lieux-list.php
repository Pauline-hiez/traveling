<?php $page_bg = ASSETS_URL . 'img/bg/grece.jpg'; ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="max-w-6xl mx-auto px-4 sm:px-6 pb-20">
    <h1 class="section-title text-xl sm:text-2xl md:text-3xl mb-2">Lieux de tournage</h1>
    <hr class="hr-gold mb-6 md:mb-8">
    <form method="GET" action="<?= BASE_URL ?>lieux" class="flex flex-col sm:flex-row gap-3 mb-6 relative">
        <input type="text"
            name="search"
            value="<?= htmlspecialchars($search ?? '') ?>"
            class="form-input sm:flex-1"
            placeholder="Rechercher un lieu..."
            autocomplete="on"
            data-autocomplete-endpoint="<?= BASE_URL ?>lieux/autocomplete"
            data-autocomplete-type="lieux"
            data-suggest-container="#lieu-search-suggestions-box"
            list="lieu-search-suggestions">
        <datalist id="lieu-search-suggestions"></datalist>
        <div id="lieu-search-suggestions-box" class="suggestions-cuir hidden max-h-72 overflow-auto absolute left-0 right-0 mt-12 z-50"></div>
        <button type="submit" class="btn">Rechercher</button>
    </form>
    <?php if (empty($lieux)): ?>
        <p class="text-center opacity-70 py-10">Aucun lieu enregistré.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($lieux as $lieu): ?>
                <a href="<?= BASE_URL ?>lieux/<?= $lieu['id'] ?>" class="small-card bloc-cuir">
                    <?php if (!empty($lieu['representative_image'] ?? $lieu['img'])): ?>
                        <img src="<?= htmlspecialchars($lieu['representative_image'] ?? $lieu['img']) ?>" alt="<?= htmlspecialchars($lieu['name']) ?>" loading="lazy">
                    <?php else: ?>
                        <div class="flex aspect-video w-full items-center justify-center bg-[rgba(74,2,2,.4)] text-4xl">📍</div>
                    <?php endif; ?>
                    <div class="p-4">
                        <h3 class="font-title text-sm font-bold uppercase text-[var(--gold)]">
                            <?= htmlspecialchars($lieu['name']) ?>
                        </h3>
                        <?php if (!empty($lieu['country'])): ?>
                            <p class="text-xs opacity-60 mt-1"><?= htmlspecialchars($lieu['country']) ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($pages > 1): ?>
            <?php $baseUrl = BASE_URL . 'lieux';
            $query = ['search' => $search ?? ''];
            $page = $page;
            $pages = $pages; ?>
            <?php require COMPONENTS . 'pagination.php'; ?>
        <?php endif; ?>

    <?php endif; ?>
</main>