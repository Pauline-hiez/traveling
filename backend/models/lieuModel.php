<?php

class Lieu
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        // Tous les lieux par ordre alphabétique
        return $this->db->query("SELECT * FROM lieux OREDER BY name ASC")->fetchAll();
    }

    public function getPublishedLieux(): array
    {
        // Lieux liés à des articles publiés avec image
        $sql = "SELECT l.*,
                       (
                           SELECT COALESCE(a.img_bg, a.img_illus, a.img_cover)
                           FROM articles a
                           JOIN article_lieux al ON al.article_id = a.id
                           WHERE al.lieu_id = l.id AND a.published = 1
                           ORDER BY a.created_at DESC
                           LIMIT 1
                       ) AS representative_image,
                       MAX(a.created_at) AS last_mention
                FROM lieux l
                JOIN article_lieux al ON al.lieu_id = l.id
                JOIN articles a ON a.id = al.article_id
                WHERE a.published = 1
                GROUP BY l.id
                ORDER BY last_mention DESC, l.name ASC";

        return $this->db->query($sql)->fetchAll();
    }

    public function getById(int $id): array|false
    {
        // Lieu par ID
        $stmt = $this->db->prepare("SELECT * FROM lieux WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getByArticle(int $articleId): array
    {
        // Lieux associés à un article
        $stmt = $this->db->prepare("
            SELECT l.* FROM lieux l
            JOIN article_lieux al ON al.lieu_id = l.id
            WHERE al.article_id = :aid
        ");
        $stmt->execute([':aid' => $articleId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        // Création d'un lieu
        $this->db->prepare("
            INSERT INTO lieux (name, country, lat, lng, img, bg_lieux)
            VALUES (:name, :country, :lat, :lng, :img, :bg_lieux)
        ")->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function getArticles(int $lieuId): array
    {
        // Articles publiés liés à un lieu
        $stmt = $this->db->prepare("
            SELECT a.*, u.pseudo AS author
            FROM articles a
            JOIN article_lieux al ON al.article_id = a.id 
            JOIN users u ON u.id = a.author_id 
            WHERE al.lieu_id = :lid AND a.published = 1
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([':lid' => $lieuId]);
        return $stmt->fetchAll();
    }

    public function getRepresentativeImage(int $lieuId): ?string
    {
        // Image représentative d'un lieu
        $stmt = $this->db->prepare("
            SELECT COALESCE(a.img_bg, a.img_illus, a.img_cover) AS image
            FROM articles a
            JOIN article_lieux al ON al.article_id = a.id
            WHERE al.lieu_id = :lid AND a.published = 1
            ORDER BY a.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([':lid' => $lieuId]);
        $image = $stmt->fetchColumn();

        return $image ?: null;
    }

    public function getAssociatedTmdbIds(int $lieuId): array
    {
        // ID's TMDB associés aux articles d'un lieu
        $stmt = $this->db->prepare("
            SELECT af.tmdb_id, MAX(a.created_at) AS last_mention
            FROM article_films af
            JOIN articles a ON a.id = af.article_id
            JOIN article_lieux al ON al.article_id = a.id
            WHERE al.lieu_id = :lid AND a.published = 1
            GROUP BY af.tmdb_id
            ORDER BY last_mention DESC
        ");
        $stmt->execute([':lid' => $lieuId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function setImage(int $id, string $imagePath): void
    {
        // Met à jour l'image d'un lieu
        $stmt = $this->db->prepare("UPDATE lieux SET img = :img WHERE id = :id");
        $stmt->execute([':img' => $imagePath, ':id' => $id]);
    }

    public function setBackround(int $id, string $bgPath): void
    {
        // Met à jour le fond d'un lieu
        $stmt = $this->db->prepare("UPDATE lieux SET bg_lieux = :bg_lieux WHERE id = :id");
        $stmt->execute([':bg_lieux' => $bgPath, ':id' => $id]);
    }

    public function getSliderImagesByLieu(int $lieuId): array
    {
        $stmt = $this->db->prepare(
            "SELECT asi.id, asi.article_id, asi.image_path, asi.slider_title, asi.slider_text, asi.caption, a.created_at
             FROM article_slider_images asi
             JOIN articles a ON a.id = asi.article_id
             JOIN article_lieux al ON al.article_id = a.id
             WHERE al.lieu_id = :lid AND a.published = 1
             ORDER BY a.created_at DESC, asi.id ASC"
        );
        $stmt->execute([':lid' => $lieuId]);
        return $stmt->fetchAll();
    }

    public function updateDetails(int $id, array $data): void
    {
        // Met à jour les champs déscriptifs d'un lieu
        $allowed = ['description', 'author_tips', 'author_suggestions'];
        $data = array_filter(
            $data,
            fn($value, $key) => in_array($key, $allowed, true) && $value !== null && $value !== '',
            ARRAY_FILTER_USE_BOTH
        );

        if (empty($data)) {
            return;
        }
        $sets = implode(', ', array_map(static fn($key) => "$key = :$key", array_keys($data)));
        $data['id'] = $id;
        $this->db->prepare("UPDATE lieux SET $sets WHERE id = :id")->execute($data);
    }
}
