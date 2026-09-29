<?php $page_bg = ASSETS_URL . 'img/bg/cinema.jpg'; ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="max-w-6xl mx-auto px-4 sm:px-6 pb-20">
    <h1 class="section-title text-xl sm:text-2xl md:text-3xl mb-2">Films &amp; Séries</h1>
    <hr class="hr-gold mb-6 md:mb-8">
    <form method="GET" action="<?= BASE_URL ?>films" class="flex flex-col sm:flex-row gap-3 mb-8 relative">
        <input type="text"
            id="film-search-input"
            name="search"
            value="<?= htmlspecialchars($search ?? '') ?>"
            class="form-input sm:flex-1"
            placeholder="Rechercher un film ou une serie"
            autocomplete="off"
            data-autocomplete-endpoint="<?= BASE_URL ?>films/autocomplete"
            data-suggest-container="#film-search-suggestions-box"
            data-autocomplete-type="films"
            data-autocomplete-show-images="1"
            list="film-search-suggestions">
        <datalist id="film-search-suggestions"></datalist>
        <div id="film-search-suggestions-box" class="absolute left-0 right-0 mt-12 z-50 suggestions-cuir hidden max-h-72 overflow-auto"></div>
        <button type="submit" class="btn">Rechercher</button>
    </form>

    <?php if (empty($films)): ?>
        <p class="text-center opacity-70 py-10">Aucun film trouvé.</p>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-5">
            <?php foreach ($films as $film): ?>
                <a href="<?= BASE_URL ?>films/<?= $film['id'] ?>" class="card-film">
                    <img src="<?= TmdbService::imgUrl($film['poster_path'] ?? null) ?>" alt="<?= htmlspecialchars($film['title']) ?>" loading="lazy" class="w-full aspect-[3/4] object-cover">
                    <div class="card-film__overlay bloc-cuir p-4">
                        <h3 class="mb-2 font-title text-xs font-bold uppercase leading-snug text-[var(--gold)]">
                            <?= htmlspecialchars($film['title']) ?>
                        </h3>
                        <?php if (!empty($film['release_date'])): ?>
                            <p class="text-xs opacity-70 mb-2"><?= substr($film['release_date'], 0, 4) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($film['overview'])): ?>
                            <p class="text-xs leading-relaxed opacity-90 card-film__excerpt">
                                <?= htmlspecialchars($film['overview']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($pages > 1): ?>
            <?php $baseUrl = BASE_URL . 'films';
            $query = ['search' => $search ?? ''];
            $page = $page;
            $pages = $pages; ?>
            <?php require COMPONENTS . 'pagination.php'; ?>
        <?php endif; ?>
    <?php endif; ?>
</main>