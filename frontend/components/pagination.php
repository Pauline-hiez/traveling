<?php
// Pagination: $page (int), $pages (int), $baseUrl (string), $query (array)
// Defensive defaults
$baseUrl = (string)($baseUrl ?? strtok($_SERVER['REQUEST_URI'], '?') ?? '');
$query = (array)($query ?? $_GET);
$page = max(1, (int)($page ?? 1));
$pages = max(1, (int)($pages ?? 1));

// build URL closure to avoid redeclaring a named function when the component
// is included multiple times on the same page.
$build_page_url = function (int $p) use ($baseUrl, $query): string {
    $q = $query;
    $q['page'] = $p;
    $qs = http_build_query($q);
    return $baseUrl . ($qs !== '' ? '?' . $qs : '');
};

if ($pages <= 1) return;
?>
<nav class="pagination" aria-label="Pagination">
    <?php // Prev 
    ?>
    <?php if ($page > 1): ?>
        <a class="pagination__item pagination__arrow" href="<?= htmlspecialchars($build_page_url($page - 1)) ?>" aria-label="Page précédente">
            <span class="pagination__chev">‹</span> Précédent
        </a>
    <?php else: ?>
        <span class="pagination__item pagination__arrow pagination__item--disabled" aria-hidden="true">
            <span class="pagination__chev">‹</span> Précédent
        </span>
    <?php endif; ?>

    <?php // Page links: show first, ellipsis, around current, ellipsis, last 
    ?>
    <?php
    $window = 1; // number of pages adjacent to current
    $start = max(1, $page - $window);
    $end = min($pages, $page + $window);

    if ($start > 1) {
    ?>
        <a class="pagination__item" href="<?= htmlspecialchars($build_page_url(1)) ?>">1</a>
        <?php if ($start > 2): ?>
            <span class="pagination__item pagination__ellipsis">…</span>
        <?php endif; ?>
        <?php }
    for ($i = $start; $i <= $end; $i++):
        if ($i === $page): ?>
            <span class="pagination__item pagination__item--active" aria-current="page"><?= $i ?></span>
        <?php else: ?>
            <a class="pagination__item" href="<?= htmlspecialchars($build_page_url($i)) ?>"><?= $i ?></a>
        <?php endif;
    endfor;
    if ($end < $pages) {
        if ($end < $pages - 1) {
        ?>
            <span class="pagination__item pagination__ellipsis">…</span>
        <?php
        }
        ?>
        <a class="pagination__item" href="<?= htmlspecialchars($build_page_url($pages)) ?>"><?= $pages ?></a>
    <?php } ?>

    <?php // Next 
    ?>
    <?php if ($page < $pages): ?>
        <a class="pagination__item pagination__arrow" href="<?= htmlspecialchars($build_page_url($page + 1)) ?>" aria-label="Page suivante">
            Suivant <span class="pagination__chev">›</span>
        </a>
    <?php else: ?>
        <span class="pagination__item pagination__arrow pagination__item--disabled" aria-hidden="true">
            Suivant <span class="pagination__chev">›</span>
        </span>
    <?php endif; ?>
</nav>