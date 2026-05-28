<?php

function asset_url(?string $path, string $fallback = ''): string
{
    // Retourne une URL d'asset normalisée
    $value = trim((string)($path ?? ''));
    if ($value === '') {
        $value = $fallback;
    }

    if (preg_match('#^https?://#i', $value) || str_starts_with($value, '//')) {
        return $value;
    }

    $value = str_replace(['frontend/assets/img/uploads/', 'assets/img/uploads/', '/assets/img/uploads/'], ['frontend/assets/img/', 'assets/img/', 'assets/img/'], $value);
    $value = ltrim($value, '/');
    if (str_starts_with($value, 'assets/')) {
        $value = 'frontend/' . $value;
    }
    return BASE_URL . $value;
}
