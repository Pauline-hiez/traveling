<?php

$film ??= [];
$credits ??= [];
$cast ??= [];
$similarFilms ??= [];
$articles ??= [];

$director = '';
$isSeries = ($film['media_type'] ?? 'movie') === 'tv';
foreach (($credits['crew'] ?? []) as $m) {
    if ($m['job'] === 'Director') {
        $director = $m['name'];
        break;
    }
}

?>

<?php $page_bg = ASSETS_URL . 'img/bg/cinema.jpg'; ?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="max-w-6xl mx-auto px-4 sm:px-6 pb-20">
    <h1 class="section-title text-xl sm:text-2xl md:text-3xl mb-6 md:mb-8"><?= htmlspecialchars($film['title']) ?></h1>
    <!-- Infos film -->
    <div class="bloc-cuir p-5 md:p-6 flex flex-col sm:flex-row gap-5 md:gap-6 mb-8">
        <img src="<?= TmdbService::imgUrl($film['poster_path'] ?? null, 'w342') ?>" class="w-40 sm:w-48 rounded-lg flex-shrink-0 mx-auto sm:mx-0">
        <div class="flex flex-col gap-3 text-sm">
            <?php if (!empty($film['overview'])): ?>
                <p class="leading-relaxed"><?= htmlspecialchars($film['overview']) ?></p>
            <?php endif; ?>
            <?php if (!empty($film['genres_label'])): ?>
                <p><strong>Genre : </strong><?= htmlspecialchars($film['genres_label']) ?></p>
            <?php endif; ?>
            <?php if (!empty($film['year'])): ?>
                <p><strong>Année : </strong><?= htmlspecialchars($film['year']) ?></p>
            <?php endif; ?>
            <?php if ($isSeries): ?>
                <p><strong>Série</strong></p>
                <?php if (isset($film['number_of_seasons'])): ?>
                    <p><strong>Saison<?= (int)$film['number_of_seasons'] > 1 ? 's' : '' ?> : </strong><?= (int)$film['number_of_seasons'] ?></p>
                <?php endif; ?>
                <?php if (isset($film['number_of_episodes'])): ?>
                    <p><strong>Épisode<?= (int)$film['number_of_episodes'] > 1 ? 's' : '' ?> : </strong><?= (int)$film['number_of_episodes'] ?></p>
                <?php endif; ?>
                <?php if (!empty($film['first_air_date'])): ?>
                    <p class="opacity-60 text-xs">Début : <?= htmlspecialchars($film['first_air_date']) ?></p>
                <?php endif; ?>
                <?php if (!empty($film['last_air_date'])): ?>
                    <p class="opacity-60 text-xs">Fin : <?= htmlspecialchars($film['last_air_date']) ?></p>
                <?php elseif (!empty($film['status']) && $film['status'] === 'Returning Series'): ?>
                    <p class="opacity-60 text-xs">Fin : En cours</p>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($director): ?>
                <p><strong>Réalisateur : <?= htmlspecialchars($director) ?></strong></p>
            <?php endif; ?>
            <?php if (!empty($cast)): ?>
                <p><strong>Distribution : </strong><?= implode(', ', array_map(fn($a) => htmlspecialchars($a['name']), array_slice($cast, 0, 5))) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Articles associés -->
    <?php if (!empty($articles)): ?>
        <h2 class="section-title text-lg sm:text-xl mb-4"><?= count($articles) ?> article(s) associé(s)</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-4">
            <?php foreach (array_slice($articles, 0, 3) as $article): ?>
                <?php require COMPONENTS . 'small-cards.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php if (count($articles) > 3): ?>
            <div id="art-text" class="hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-4">
                <?php foreach (array_slice($articles, 3) as $article): ?>
                    <?php require COMPONENTS . 'small-cards.php'; ?>
                <?php endforeach ?>
            </div>
            <div class="text-center"><button class="btn" data-toggle="art-extra">Voir plus</button></div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Films similaires -->
    <?php if (!empty($similarFilms)): ?>
        <h2 class="section-title text-lg sm:text-xl mt-10 mb-4">Films similaires</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-5">
            <?php foreach ($similarFilms as $similarFilm): ?>
                <a href="<?= BASE_URL ?>films/<?= $similarFilm['id'] ?>" class="card-film">
                    <img src="<?= TmdbService::imgUrl($similarFilm['poster_path'] ?? null) ?>" alt="<?= htmlspecialchars($similarFilm['title']) ?>" loading="lazy" class="w-full aspect-[3/4] object-cover">
                    <div class="card-film__overlay bloc-cuir p-4">
                        <h3 class="mb-2 font-title text-xs font-bold uppercase leading-snug text-[var(--gold)]">
                            <?= htmlspecialchars($similarFilm['title']) ?>
                        </h3>
                        <?php if (!empty($similarFilm['release_date'])): ?>
                            <p class="text-xs opacity-70 mb-2"><?= substr($similarFilm['release_date'], 0, 4) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($similarFilm['overview'])): ?>
                            <p class="text-xs leading-relaxed opacity-90 card-film__excerpt">
                                <?= htmlspecialchars($similarFilm['overview']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>