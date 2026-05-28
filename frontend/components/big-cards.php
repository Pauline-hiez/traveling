<?php $article = (array)($article ?? []); ?>
<a href="<?= BASE_URL ?>articles/<?= $article['id'] ?>" class="big-card bloc-cuir">
    <?php // Carte article (grand format) 
    ?>
    <img src="<?= asset_url($article['img_illus'] ?? $article['img_cover'] ?? 'frontend/assets/img/placeholder.jpg') ?>" alt="<?= htmlspecialchars($article['title']) ?>" loading="lazy">
    <div class="card-body p-4 flex flex-col gap-2 justify-center">
        <h3 class="text-sm font-bold uppercase leading-snug" style="font-family:var(--font-title);color:var(--gold);">
            <?= htmlspecialchars($article['title']) ?>
        </h3>

        <?php if (!empty($article['subtitle'])): ?>
            <p class="text-xs opacity-80 leading-relaxed line-clamp-2">
                <?= htmlspecialchars(mb_substr($article['subtitle'], 0, 90)) ?>...
            </p>
        <?php endif; ?>

        <p class="text-xs opacity-60 mt-2">Publié le <?= isset($article['created_at']) ? date('d M Y', strtotime($article['created_at'])) : 'Date inconnue' ?></p>
        <p class="text-xs opacity-60 mt-0.5">Publié par <?= htmlspecialchars($article['author'] ?? ($article['pseudo'] ?? 'Anonyme')) ?></p>

        <div class="flex gap-4 text-xs opacity-70 mt-1">
            <span>♥ <?= (int)($article['likes']     ?? 0) ?></span>
            <span>💬 <?= (int)($article['comments']  ?? 0) ?></span>
            <span>★ <?= (int)($article['favorites'] ?? 0) ?></span>
        </div>
    </div>
</a>