<?php

class TmdbService
{
    private static function normalizeMedia(array $data, string $type): array
    {
        // Normalise les champs entre films et séries
        if ($type === 'tv') {
            if (!isset($data['title']) && isset($data['name'])) {
                $data['title'] = $data['name'];
            }
            if (!isset($data['release_date']) && isset($data['first_air_date'])) {
                $data['release_date'] = $data['first_air_date'];
            }
            $data['media_type'] = 'tv';
        } else {
            $data['media_type'] = 'movie';
        }
        return $data;
    }

    private static function get(string $endpoint, array $params = []): ?array
    {
        // Requête simple vers l'API TMDB
        $params['api_key'] = $_ENV['TMDB_API_KEY'];
        $params['language'] = 'fr-FR';
        $url = $_ENV['TMDB_BASE_URL'] . $endpoint . '?' . http_build_query($params);

        $res = @file_get_contents($url);
        if ($res === false || $res === '') {
            return null;
        }

        $data = json_decode($res, true);
        return is_array($data) ? $data : null;
    }

    // Recherche (films et séries) par titre
    public static function search(string $query): array
    {
        // Recherche multi (film + série)
        $data = self::get('/search/multi', ['query' => $query]);
        return $data['results'] ?? [];
    }

    // Films populaires
    public static function getPopular(int $page = 1): array
    {
        $data = self::get('/movie/popular', ['page' => max(1, $page)]);
        return $data['results'] ?? [];
    }

    // Détail d'un film
    public static function getMovie(int $tmdbId): ?array
    {
        $movie = self::get('/movie/' . $tmdbId);
        return $movie ? self::normalizeMedia($movie, 'movie') : null;
    }

    public static function getDetails(int $tmdbId): ?array
    {
        $movie = self::get('/movie/' . $tmdbId);
        if (is_array($movie)) {
            return self::normalizeMedia($movie, 'movie');
        }

        $tv = self::get('/tv/' . $tmdbId);
        if (is_array($tv)) {
            return self::normalizeMedia($tv, 'tv');
        }

        return null;
    }

    // Réalisateur et distribution
    public static function getCredits(int $tmdbId): ?array
    {
        $credits = self::get('/movie/' . $tmdbId . '/credits');
        if (is_array($credits)) {
            return $credits;
        }
        return self::get('/tv/' . $tmdbId . '/credits');
    }

    // Films similaires
    public static function getSimilar(int $tmdbId): array
    {
        $data = self::get('/movie/' . $tmdbId . '/similar');
        if (!is_array($data)) {
            $data = self::get('/tv/' . $tmdbId . '/similar');
        }
        return $data['results'] ?? [];
    }

    // URL d'une image TMDB
    public static function imgUrl(?string $path, string $size = 'w500'): string
    {
        if (!$path) return 'assets/img/placeholder.jpg';
        return "https://image.tmdb.org/t/p/{$size}{$path}";
    }
}
