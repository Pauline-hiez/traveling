<?php
$search ??= '';
$sort ??= 'date';
$category ??= '';
$page ??= 1;
$pages ??= 1;
$articles ??= [];
$page_bg = ASSETS_URL . 'img/bg/italie.jpg';
?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="max-w-6xl mx-auto px-4 sm:px-6 pb-20">
    <h1 class="section-title text-xl sm:text-2xl md:text-3xl mb-2">Retrouvez tous nos articles</h1>
    <hr class="hr-gold mb-6 md:mb-8">

    <!-- Filtres -->
    <form method="GET" action="<?= BASE_URL ?>articles" class="flex flex-col sm:flex-row flex-wrap gap-3 mb-8">
        <div class="relative sm:flex-1 min-w-0">
            <input type="text"
                name="search"
                placeholder="Rechercher..."
                value="<?= htmlspecialchars($search) ?>"
                class="form-input w-full"
                autocomplete="on"
                data-autocomplete-endpoint="<?= BASE_URL ?>articles/autocomplete"
                data-autocomplete-type="articles"
                data-suggest-container="#article-search-suggestions-box"
                list="article-search-suggestions">
            <datalist id="article-search-suggestions"></datalist>
            <div id="article-search-suggestions-box" class="suggestions-cuir hidden max-h-72 overflow-auto absolute left-0 right-0 mt-12 z-50"></div>
        </div>
        <select name="sort" class="form-input sm:w-48">
            <option value="date" <?= $sort === 'date' ? 'selected' : '' ?>>Trier par date</option>
            <option value="popularity" <?= $sort === 'popularity' ? 'selected' : '' ?>>Trier par popularité</option>
        </select>
        <select name="category" class="form-input sm:w-48">
            <option value="">Toutes catégories</option>
            <option value="cinema" <?= $category === 'cinema' ? 'selected' : '' ?>>Cinéma</option>
            <option value="voyage" <?= $category === 'voyage' ? 'selected' : '' ?>>Voyage</option>
        </select>
        <button type="submit" class="btn">Rechercher</button>
    </form>

    <!-- Grille -->
    <?php if (empty($articles)): ?>
        <p class="text-center opacity-70 py-10">Aucun article trouvé.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 md:gap-6 mb-10">
            <?php foreach ($articles as $article) : ?>
                <?php require COMPONENTS . 'small-cards.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
        <?php
        $baseUrl = BASE_URL . 'articles';
        $query = ['search' => $search, 'category' => $category, 'sort' => $sort];
        $page = $page;
        $pages = $pages;
        ?>
        <?php require COMPONENTS . 'pagination.php'; ?>
    <?php endif; ?>
</main>