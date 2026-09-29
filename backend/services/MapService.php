<?php

class MapService
{
    private const LANGUAGE = 'fr';

    // Recherche de lieu via Nominatim
    public static function search(string $query): array
    {
        // Construit l'URL de recherche
        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
            'q' => $query,
            'format' => 'json',
            'limit' => 5,
            'addressdetails' => 1,
            'accept-language' => self::LANGUAGE,
        ]);

        $ctx = stream_context_create(['http' => ['header' => 'User-Agent: Traveling/1.0']]);
        $res = @file_get_contents($url, false, $ctx);

        return $res ? json_decode($res, true) : [];
    }

    // Détail d'un lieu par coordonnées
    public static function reverse(float $lat, float $lng): ?array
    {
        // Construit l'URL de reverse geocode
        $url = 'https://nominatim.openstreetmap.org/reverse?' . http_build_query([
            'lat' => $lat,
            'lon' => $lng,
            'format' => 'json',
            'addressdetails' => 1,
            'zoom' => 18,
            'extratags' => 1,
            'namedetails' => 1,
            'accept-language' => self::LANGUAGE,
        ]);

        $ctx = stream_context_create(['http' => ['header' => 'User-Agent: Traveling/1.0']]);
        $res = @file_get_contents($url, false, $ctx);

        // Retourne les détails ou null
        return $res ? json_decode($res, true) : null;
    }
}
