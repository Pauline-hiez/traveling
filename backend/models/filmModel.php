<?php

class Film
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getTmdbIdsByArticle(int $articleId): array
    {
        // TMDB ID's associés à un article
        $stmt = $this->db->prepare("SELECT tmdb_id FROM article_films WHERE article_id = :aid");
        $stmt->execute([':aid' => $articleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getPublishedTmdbIds(): array
    {
        $sql = "SELECT af.tmdb_id, MAX(a.created_at) AS last_mention
                FROM article_films af
                JOIN articles a ON a.id = af.article_id
                WHERE a.published = 1
                GROUP BY af.tmdb_id
                ORDER BY last_mention DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getArticlesByTmdb(int $tmdbId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.pseudo AS author
             FROM articles a
             JOIN article_films af ON af.article_id = a.id
             JOIN users u ON u.id = a.author_id
             WHERE af.tmdb_id = :tid AND a.published = 1
             ORDER BY a.created_at DESC"
        );
        $stmt->execute([':tid' => $tmdbId]);
        return $stmt->fetchAll();
    }
}
