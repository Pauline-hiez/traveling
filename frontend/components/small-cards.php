<?php $article = (array)($article ?? []); ?>
<a href="<?= BASE_URL ?>articles/<?= $article['id'] ?>" class="small-card bloc-cuir">
    <?php // Carte article (format compact) 
    ?>
    <img src="<?= asset_url($article['img_illus'] ?? $article['img_cover'] ?? 'frontend/assets/img/placeholder.jpg') ?>"
        alt="<?= htmlspecialchars($article['title']) ?>" loading="lazy">
    <div class="relative flex-1 p-4">
        <h3 class="mb-2 font-title text-sm font-bold uppercase leading-snug text-[var(--gold)]">
            <?= htmlspecialchars($article['title']) ?>
        </h3>
        <?php if (!empty($article['subtitle'])): ?>
            <p class="line-clamp-3 text-xs leading-relaxed opacity-80">
                <?= htmlspecialchars(mb_substr($article['subtitle'], 0, 100)) ?>…
            </p>
        <?php endif; ?>
        <p class="mt-2 text-xs opacity-60">Publié le <?= isset($article['created_at']) ? date('d M Y', strtotime($article['created_at'])) : 'Date inconnue' ?></p>
        <p class="mt-0.5 text-xs opacity-60">Publié par <?= htmlspecialchars($article['author'] ?? ($article['pseudo'] ?? 'Anonyme')) ?></p>
    </div>
    <div class="card-stats flex gap-4 px-4 py-2 text-xs opacity-80">
        <span>♥ <?= (int)($article['likes'] ?? 0) ?></span>
        <span>💬 <?= (int)($article['comments'] ?? 0) ?></span>
        <span>★ <?= (int)($article['favorites'] ?? 0) ?></span>
    </div>
</a>