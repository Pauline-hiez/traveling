<?php
// Variables passed from NewsletterService::buildWeeklyContent()
$logoSrc = $logoSrc ?? '';
$heroBg = $heroBg ?? '';
$textureBg = $textureBg ?? '';
$news = $news ?? [];
$week_label = $week_label ?? '';
$featured = $featured ?? null;
$others = $others ?? [];
?>

<?php $preheader = $preheader ?? 'Votre dose d\'evasion cinematographique est arrivee.'; ?>
<div style="display:none;visibility:hidden;opacity:0;color:transparent;height:0;width:0;overflow:hidden;">
    <?= htmlspecialchars($preheader) ?>
</div>

<div style="margin:0;padding:22px;background:#15171b;background-image:url('<?= htmlspecialchars($textureBg) ?>');background-size:cover;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:920px;margin:0 auto;background:#1e130f;border:1px solid #5d3f24;box-shadow:0 0 20px rgba(0,0,0,.35);">
        <tr>
            <td style="padding:12px 16px;background:rgba(0,0,0,.28);font-family:Georgia,serif;font-size:12px;color:#e7c08a;">
                <span style="float:left;">Votre dose d'evasion cinematographique</span>
                <span style="float:right;"><a href="<?= htmlspecialchars(BASE_URL . 'newsletter/apercu') ?>" style="color:#e7c08a;text-decoration:underline;">Voir cet email dans votre navigateur</a></span>
            </td>
        </tr>

        <tr>
            <td style="padding:0;background:#2a1a12;">
                <img src="<?= htmlspecialchars($heroBg) ?>" alt="Traveling" style="display:block;width:100%;max-width:920px;height:auto;border:0;margin:0;">
            </td>
        </tr>

        <tr>
            <td style="padding:22px 22px 8px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#d9bf93 url('<?= htmlspecialchars($textureBg) ?>') center/cover;border:1px solid #9f7f58;">
                    <tr>
                        <td style="padding:18px 20px;font-family:Georgia,serif;color:#3d2418;font-size:16px;line-height:1.45;background:rgba(255,241,218,.52);">
                            <div style="font-size:28px;line-height:1.15;margin-bottom:8px;">Bonjour voyageur,</div>
                            <div style="font-size:16px;line-height:1.55;">
                                Chaque semaine, nous vous emmenons a la decouverte de lieux de tournage mythiques,
                                d'histoires fascinantes et d'inspirations pour vos prochaines aventures.
                            </div>
                            <div style="font-size:17px;font-weight:bold;margin-top:10px;">Voici votre selection de la semaine.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <?php if ($featured): ?>
            <tr>
                <td style="padding:8px 22px 6px;font-family:Georgia,serif;color:#e9c184;text-align:center;font-size:16px;letter-spacing:1px;">A LA UNE CETTE SEMAINE</td>
            </tr>
            <tr>
                <td style="padding:0 22px 16px;">
                    <table role="presentation" class="newsletter-feature" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #72502f;background:rgba(33,20,14,.78) url('<?= htmlspecialchars($textureBg) ?>') center/cover;">
                        <tr>
                            <td class="newsletter-feature__image-cell" width="52%" style="padding:0;vertical-align:top;">
                                <?php if (!empty($featured['resolved_image'])): ?>
                                    <img src="<?= htmlspecialchars($featured['resolved_image']) ?>" alt="<?= $featured['title_safe'] ?>" style="display:block;width:100%;height:240px;object-fit:cover;border:0;">
                                <?php else: ?>
                                    <div style="height:240px;line-height:240px;text-align:center;font-size:40px;color:#e9c184;background:#2e1c14;">🎬</div>
                                <?php endif; ?>
                            </td>
                            <td class="newsletter-feature__content-cell" width="48%" style="padding:18px 18px;vertical-align:top;font-family:Georgia,serif;color:#e7c48d;background:rgba(22,11,7,.38);">
                                <div style="font-size:11px;letter-spacing:1px;color:#b9884d;margin-bottom:6px;">★ <?= $featured['category_label'] ?> DE LA SEMAINE</div>
                                <div style="font-size:24px;line-height:1.18;font-weight:bold;margin-bottom:8px;"><?= $featured['title_safe'] ?></div>
                                <?php if (!empty($featured['subtitle_safe'])): ?>
                                    <div style="font-size:16px;line-height:1.42;color:#f3d8b0;margin-bottom:12px;"><?= $featured['subtitle_safe'] ?></div>
                                <?php endif; ?>
                                <a href="<?= htmlspecialchars($featured['url']) ?>" style="display:inline-block;padding:9px 16px;background:#4A0202;color:#FFD39D;text-decoration:none;border:1px solid #FFD39D;border-radius:4px;font-family:Georgia,serif;font-size:16px;font-weight:bold;">Lire l'article</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($others)): ?>
            <tr>
                <td style="padding:8px 22px 8px;font-family:Georgia,serif;color:#e9c184;text-align:center;font-size:15px;letter-spacing:1px;">AUTRES ARTICLES A DECOUVRIR</td>
            </tr>
            <tr>
                <td style="padding:0 22px 16px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <?php foreach ($others as $index => $article): ?>
                                <td class="newsletter-other__item" width="<?= floor(100 / count($others)) ?>%" style="padding:0 <?= $index < count($others) - 1 ? '8' : '0' ?>px 0 0;vertical-align:top;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #6e4c2d;background:rgba(30,18,12,.78) url('<?= htmlspecialchars($textureBg) ?>') center/cover;">
                                        <tr>
                                            <td style="padding:0;">
                                                <?php if (!empty($article['resolved_image'])): ?>
                                                    <img src="<?= htmlspecialchars($article['resolved_image']) ?>" alt="<?= $article['title_safe'] ?>" style="display:block;width:100%;height:150px;object-fit:cover;border:0;">
                                                <?php else: ?>
                                                    <div style="height:150px;line-height:150px;text-align:center;font-size:32px;color:#e9c184;background:#2e1c14;">🎬</div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:10px 10px 12px;font-family:Georgia,serif;color:#efcf9f;background:rgba(22,11,7,.34);">
                                                <div style="font-size:11px;letter-spacing:1px;color:#b9884d;margin-bottom:6px;"><?= $article['category_label'] ?></div>
                                                <div style="font-size:18px;line-height:1.25;font-weight:bold;margin-bottom:6px;"><?= $article['title_safe'] ?></div>
                                                <?php if (!empty($article['subtitle_safe'])): ?>
                                                    <div style="font-size:13px;line-height:1.35;color:#f2d8b1;margin-bottom:8px;"><?= $article['subtitle_safe'] ?></div>
                                                <?php endif; ?>
                                                <a href="<?= htmlspecialchars($article['url']) ?>" style="color:#c53b2a;text-decoration:none;font-size:13px;">Decouvrir →</a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($news)): ?>
            <tr>
                <td style="padding:4px 22px 4px;font-family:Georgia,serif;color:#e9c184;text-align:center;font-size:15px;letter-spacing:1px;">NOUVEAUTES DU SITE</td>
            </tr>
            <tr>
                <td style="padding:0 22px 16px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #6e4c2d;background:rgba(30,18,12,.78) url('<?= htmlspecialchars($textureBg) ?>') center/cover;">
                        <tr>
                            <td style="padding:12px 14px;font-family:Georgia,serif;color:#efcf9f;font-size:13px;line-height:1.5;background:rgba(22,11,7,.34);">
                                <?php foreach ($news as $item): ?>
                                    <div style="margin-bottom:8px;">• <?= htmlspecialchars(($item['title'] ?? '') . (!empty($item['text']) ? ' - ' . $item['text'] : '')) ?></div>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php endif; ?>

        <tr>
            <td style="padding:18px 22px 26px;border-top:1px solid #6b4a2b;font-family:Georgia,serif;color:#c89f72;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="newsletter-footer__left" width="50%" style="vertical-align:top;">
                            <img src="<?= htmlspecialchars($logoSrc) ?>" alt="Traveling" style="display:block;width:40px;max-width:40px;height:auto;margin-bottom:6px;">
                            <div style="font-size:20px;color:#e8c489;">TRAVELING</div>
                            <div style="font-size:12px;line-height:1.4;">Explore le monde a travers le cinema</div>
                        </td>
                        <td class="newsletter-footer__right" width="50%" style="vertical-align:top;text-align:right;">
                            <div style="font-size:13px;color:#e8c489;margin-bottom:6px;">UNE QUESTION ?</div>
                            <div style="font-size:12px;line-height:1.4;">Repondez simplement a cet email,<br>nous serons ravis de vous lire.</div>
                        </td>
                    </tr>
                </table>

                <div style="margin-top:14px;font-size:11px;line-height:1.45;text-align:center;">
                    Vous recevez cet email car vous etes inscrit a la newsletter Traveling.<br>
                    <a href="{{UNSUB_LINK}}" style="color:#e7c08a;text-decoration:underline;">Se desinscrire</a>
                </div>
            </td>
        </tr>
    </table>
</div>