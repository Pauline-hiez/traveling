<?php
$lieu ??= [];
$lieuSummary ??= '';
$lieuCountry ??= null;
$lieuRegion ??= null;
$lieuType ??= null;
$lieuDisplayName ??= null;
$lieuExtraTags ??= [];
$articles ??= [];
$associatedFilms ??= [];
$sliderImages ??= [];
$heroImage ??= '';
$default_bg = 'frontend/assets/img/bg/james-bond-island.png';
$page_bg = asset_url($lieu['bg_lieux'] ?? $default_bg);
?>

<div class="page-bg" style="background-image:url('<?= $page_bg ?>')"></div>

<main class="max-w-6xl mx-auto px-4 sm:px-6 pb-20">
    <h1 class="section-title text-xl sm:text-2xl md:text-3xl mb-6 md:mb-8"><?= htmlspecialchars($lieu['name']) ?></h1>

    <div class="bloc-cuir p-5 md:p-6 mb-8">
        <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-5 md:gap-6 items-start">
            <div>
                <?php if (!empty($heroImage)): ?>
                    <img src="<?= asset_url($heroImage) ?>" alt="<?= htmlspecialchars($lieu['name']) ?>" class="w-full aspect-square object-cover rounded-lg border border-[rgba(225,191,123,.3)]" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22400%22%3E%3Crect fill=%22%234a0202%22 width=%22400%22 height=%22400%22/%3E%3C/svg%3E'">
                <?php endif; ?>
            </div>

            <div class="flex flex-col gap-4 text-sm">
                <p class="leading-relaxed">
                    <?= htmlspecialchars($lieu['name']) ?> <?= htmlspecialchars($lieuSummary ?? '') ?>
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php if (!empty($lieuCountry)): ?>
                        <div>
                            <p class="opacity-70 text-xs uppercase tracking-[0.2em] mb-1">Pays</p>
                            <p><strong><?= htmlspecialchars($lieuCountry) ?></strong></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($lieuRegion)): ?>
                        <div>
                            <p class="opacity-70 text-xs uppercase tracking-[0.2em] mb-1">Région</p>
                            <p><?= htmlspecialchars($lieuRegion) ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($lieu['lat']) && !empty($lieu['lng'])): ?>
                        <div>
                            <p class="opacity-70 text-xs uppercase tracking-[0.2em] mb-1">Coordonnées</p>
                            <p><?= htmlspecialchars((string)$lieu['lat']) ?>, <?= htmlspecialchars((string)$lieu['lng']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($lieu['description'])): ?>
                    <div class="border-t border-[rgba(225,191,123,.2)] pt-3">
                        <p class="opacity-70 text-xs uppercase tracking-[0.2em] mb-1">Description du lieu</p>
                        <p class="leading-relaxed"><?= nl2br(htmlspecialchars($lieu['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($lieu['author_tips'])): ?>
                    <div class="border-t border-[rgba(225,191,123,.2)] pt-3">
                        <p class="opacity-70 text-xs uppercase tracking-[0.2em] mb-1">Conseils de l'auteur</p>
                        <p class="leading-relaxed"><?= nl2br(htmlspecialchars($lieu['author_tips'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($lieu['author_suggestions'])): ?>
                    <div class="border-t border-[rgba(225,191,123,.2)] pt-3">
                        <p class="opacity-70 text-xs uppercase tracking-[0.2em] mb-1">Suggestions de l'auteur</p>
                        <p class="leading-relaxed"><?= nl2br(htmlspecialchars($lieu['author_suggestions'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($lieu['lat']) && !empty($lieu['lng'])): ?>
            <div class="mt-6">
                <div id="lieu-map" class="w-full rounded-xl border border-[var(--gold)] h-[180px]" data-lat="<?= htmlspecialchars((string)$lieu['lat']) ?>" data-lng="<?= htmlspecialchars((string)$lieu['lng']) ?>" data-name="<?= htmlspecialchars($lieu['name']) ?>"></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($sliderImages)): ?>
            <div class="mt-6">
                <h2 class="section-title text-lg sm:text-xl mb-4">Découvrez plus en images</h2>
                <div class="lieu-slider flex gap-3 h-[340px] overflow-hidden max-[900px]:flex-col max-[900px]:h-auto">
                    <?php foreach ($sliderImages as $index => $slide): ?>
                        <?php $title = trim((string)($slide['slider_title'] ?? '')); ?>
                        <?php $text = trim((string)($slide['slider_text'] ?? '')); ?>
                        <?php $captionFallback = trim((string)($slide['caption'] ?? '')); ?>
                        <button type="button" class="lieu-slide flex-[1_1_0%] border border-[rgba(255,211,157,0.28)] rounded-[28px] bg-cover bg-center bg-no-repeat relative transition-[flex-basis,flex-grow,filter] duration-700 ease-out cursor-pointer saturate-[0.85]<?= $index === 0 ? ' is-active' : '' ?>" style="background-image: url('<?= asset_url($slide['image_path'] ?? '') ?>');" aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>">
                            <?php if ($title !== '' || $text !== '' || $captionFallback !== ''): ?>
                                <div class="lieu-slide__text bloc-cuir absolute left-5 right-5 bottom-5 opacity-0 translate-y-2 transition-[opacity,transform] duration-300">
                                    <?php if ($title !== ''): ?>
                                        <p class="text-[0.95rem] font-semibold leading-[1.3] text-[var(--gold)]"><?= htmlspecialchars($title) ?></p>
                                    <?php endif; ?>
                                    <?php if ($text !== ''): ?>
                                        <p class="mt-1 text-[0.85rem] leading-[1.35] text-[var(--gold)] opacity-90"><?= htmlspecialchars($text) ?></p>
                                    <?php elseif ($captionFallback !== ''): ?>
                                        <p class="mt-1 text-[0.85rem] leading-[1.35] text-[var(--gold)] opacity-90"><?= htmlspecialchars($captionFallback) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($associatedFilms)): ?>
        <h2 class="section-title text-lg sm:text-xl mb-4">Films associes</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-5 mb-8">
            <?php foreach ($associatedFilms as $film): ?>
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
    <?php endif; ?>

    <?php if (!empty($articles)): ?>
        <h2 class="section-title text-lg sm:text-xl mb-4"><?= count($articles) ?> article(s) associe(s)</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-4">
            <?php foreach (array_slice($articles, 0, 3) as $article): ?>
                <?php require COMPONENTS . 'small-cards.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php if (count($articles) > 3): ?>
            <div id="art-extra" class="hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-4">
                <?php foreach (array_slice($articles, 3) as $article): ?>
                    <?php require COMPONENTS . 'small-cards.php'; ?>
                <?php endforeach; ?>
            </div>
            <div class="text-center"><button class="btn" data-toggle="art-extra">Voir plus</button></div>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-center opacity-70 py-6">Aucun article associe.</p>
    <?php endif; ?>
</main>