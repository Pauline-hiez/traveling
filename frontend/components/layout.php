<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Traveling') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <?php if (!empty($css) && is_array($css)): ?>
        <?php foreach ($css as $c): ?>
            <link rel="stylesheet" href="<?= $c ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <?php // Helpers + elements globaux 
    ?>
    <?php require COMPONENTS . 'asset_helper.php'; ?>
    <?php require COMPONENTS . 'navbar.php'; ?>
    <?php require COMPONENTS . 'auth-modal.php'; ?>

    <?php // Vue principale 
    ?>
    <?php if (isset($viewFile) && file_exists($viewFile)) include $viewFile; ?>

    <?php require COMPONENTS . 'footer.php'; ?>

    <script src="<?= ASSETS_URL ?>js/auth.js"></script>
    <script src="<?= ASSETS_URL ?>js/app.js"></script>

    <?php if (!empty($scripts) && is_array($scripts)): ?>
        <?php foreach ($scripts as $s): ?>
            <script src="<?= $s ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>

</html>