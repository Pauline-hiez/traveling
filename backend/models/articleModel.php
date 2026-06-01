<?php

class Article
{
    private PDO $db;
    private ?bool $hasPublishAtColumn = null;
    private ?array $articleColumns = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getMostPopular(): array|false
    {
        $sql = "SELECT a.*, u.pseudo AS author,
                       COUNT(DISTINCT l.user_id) AS likes,
                       COUNT(DISTINCT c.id)       AS comments,
                       COUNT(DISTINCT f.user_id)  AS favorites
                FROM articles a
                JOIN users u ON u.id = a.author_id
                LEFT JOIN likes     l ON l.article_id = a.id
                LEFT JOIN comments  c ON c.article_id = a.id
                LEFT JOIN favorites f ON f.article_id = a.id
                WHERE a.published = 1
                GROUP BY a.id
                ORDER BY (COUNT(DISTINCT l.user_id) + COUNT(DISTINCT c.id) + COUNT(DISTINCT f.user_id)) DESC
                LIMIT 1";
        return $this->db->query($sql)->fetch();
    }

    public function getLatest(int $limit = 3): array
    {
        $sql = "SELECT a.*, u.pseudo AS author,
                       COUNT(DISTINCT l.user_id) AS likes,
                       COUNT(DISTINCT c.id)       AS comments,
                       COUNT(DISTINCT f.user_id)  AS favorites
                FROM articles a
                JOIN users u ON u.id = a.author_id
                LEFT JOIN likes     l ON l.article_id = a.id
                LEFT JOIN comments  c ON c.article_id = a.id
                LEFT JOIN favorites f ON f.article_id = a.id
                WHERE a.published = 1
                GROUP BY a.id
                ORDER BY a.created_at DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPaginated(int $page, string $search = '', string $category = '', string $sort = 'date'): array
    {
        $offset = ($page - 1) * ARTICLE_PER_PAGE;
        $where = ['a.published = 1'];
        $params = [];

        if ($search !== '') {
            $where[] = '(a.title LIKE :search_title OR a.subtitle LIKE :search_subtitle)';
            $params[':search_title'] = "%$search%";
            $params[':search_subtitle'] = "%$search%";
        }

        if ($category !== '' && $this->hasArticleColumn('category')) {
            $where[] = 'a.category = :category';
            $params[':category'] = $category;
        }

        $orderBy = $sort === 'popularity'
            ? '(COUNT(DISTINCT l.user_id) + COUNT(DISTINCT c.id) + COUNT(DISTINCT f.user_id)) DESC'
            : 'a.created_at DESC';
        $whereStr = implode(' AND ', $where);

        $sql = "SELECT a.*, u.pseudo AS author,
                       COUNT(DISTINCT l.user_id) AS likes,
                       COUNT(DISTINCT c.id)       AS comments,
                       COUNT(DISTINCT f.user_id)  AS favorites
                FROM articles a
                JOIN users u ON u.id = a.author_id
                LEFT JOIN likes     l ON l.article_id = a.id
                LEFT JOIN comments  c ON c.article_id = a.id
                LEFT JOIN favorites f ON f.article_id = a.id
                WHERE $whereStr
                GROUP BY a.id
                ORDER BY $orderBy
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', ARTICLE_PER_PAGE, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countFiltered(string $search = '', string $category = ''): int
    {
        $where = ['published = 1'];
        $params = [];

        if ($search !== '') {
            $where[] = '(title LIKE :search_title OR subtitle LIKE :search_subtitle)';
            $params[':search_title'] = "%$search%";
            $params[':search_subtitle'] = "%$search%";
        }

        if ($category !== '' && $this->hasArticleColumn('category')) {
            $where[] = 'category = :category';
            $params[':category'] = $category;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM articles WHERE ' . implode(' AND ', $where));
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getById(int $id): array|false
    {
        $sql = "SELECT a.*, u.pseudo AS author,
                       COUNT(DISTINCT l.user_id) AS likes,
                       COUNT(DISTINCT c.id)       AS comments,
                       COUNT(DISTINCT f.user_id)  AS favorites
                FROM articles a
                JOIN users u ON u.id = a.author_id
                LEFT JOIN likes     l ON l.article_id = a.id
                LEFT JOIN comments  c ON c.article_id = a.id
                LEFT JOIN favorites f ON f.article_id = a.id
                WHERE a.id = :id AND a.published = 1
                GROUP BY a.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getAdminById(int $id): array|false
    {
        $sql = "SELECT a.*, u.pseudo AS author,
                       COUNT(DISTINCT l.user_id) AS likes,
                       COUNT(DISTINCT c.id)       AS comments,
                       COUNT(DISTINCT f.user_id)  AS favorites
                FROM articles a
                JOIN users u ON u.id = a.author_id
                LEFT JOIN likes     l ON l.article_id = a.id
                LEFT JOIN comments  c ON c.article_id = a.id
                LEFT JOIN favorites f ON f.article_id = a.id
                WHERE a.id = :id
                GROUP BY a.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getAll(): array
    {
        return $this->db->query(
            "SELECT a.*, u.pseudo AS author
             FROM articles a
             JOIN users u ON u.id = a.author_id
             ORDER BY a.created_at DESC"
        )->fetchAll();
    }

    public function searchTitles(string $query, int $limit = 6): array
    {
        $imageColumns = [];
        foreach (['img_cover', 'img_illus', 'img_bg'] as $column) {
            if ($this->hasArticleColumn($column)) {
                $imageColumns[] = $column;
            }
        }
        $imageExpr = $imageColumns
            ? 'COALESCE(' . implode(', ', $imageColumns) . ') AS image'
            : 'NULL AS image';

        $stmt = $this->db->prepare(
            "SELECT id, title, $imageExpr
             FROM articles
             WHERE published = 1 AND title LIKE :q
             ORDER BY created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':q', '%' . $query . '%');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return array_map(static fn($row) => [
            'id' => $row['id'],
            'label' => $row['title'],
            'image' => $row['image'] ?? null,
        ], $rows ?: []);
    }

    public function create(array $data): int
    {
        if (($data['published'] ?? 0) === 2 && !$this->hasPublishAtColumn()) {
            $data['published'] = 1;
            unset($data['publish_at']);
        }

        $fields = [];
        foreach (['author_id', 'title', 'subtitle', 'content', 'quote', 'anecdote', 'img_cover', 'img_illus', 'img_caption', 'img_bg', 'category', 'published', 'publish_at'] as $field) {
            if (array_key_exists($field, $data) && $this->hasArticleColumn($field)) {
                $fields[] = $field;
            }
        }

        $fields = array_values(array_unique($fields));
        $placeholders = array_map(static fn($field) => ':' . $field, $fields);
        $data = array_intersect_key($data, array_flip($fields));

        $sql = 'INSERT INTO articles (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function publishDue(): void
    {
        if (!$this->hasPublishAtColumn()) {
            return;
        }
        $this->db->prepare('UPDATE articles SET published = 1, publish_at = NULL WHERE published = 2 AND publish_at IS NOT NULL AND publish_at <= NOW()')->execute();
    }

    public function update(int $id, array $data): void
    {
        if (empty($data)) {
            return;
        }

        if (!$this->hasPublishAtColumn() && isset($data['published']) && (int) $data['published'] === 2) {
            $data['published'] = 1;
            unset($data['publish_at']);
        }

        $data = array_filter(
            $data,
            fn($value, $key) => $value !== null && $value !== '' && $this->columnExists($key),
            ARRAY_FILTER_USE_BOTH
        );

        if (empty($data)) {
            return;
        }

        $sets = implode(', ', array_map(static fn($key) => "$key = :$key", array_keys($data)));
        $data['id'] = $id;
        $this->db->prepare("UPDATE articles SET $sets WHERE id = :id")->execute($data);
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM articles WHERE id = :id")->execute([':id' => $id]);
    }

    public function getUserFavorites(int $userId, int $limit = 0): array
    {
        $limitStr = $limit > 0 ? "LIMIT $limit" : '';
        $sql = "SELECT a.*, u.pseudo AS author,
                       COUNT(DISTINCT l.user_id)  AS likes,
                       COUNT(DISTINCT c.id)        AS comments,
                       COUNT(DISTINCT f2.user_id)  AS favorites,
                       MAX(fav.created_at)         AS favorited_at
                FROM favorites fav
                JOIN articles a   ON a.id  = fav.article_id
                JOIN users u      ON u.id  = a.author_id
                LEFT JOIN likes     l  ON l.article_id  = a.id
                LEFT JOIN comments  c  ON c.article_id  = a.id
                LEFT JOIN favorites f2 ON f2.article_id = a.id
                WHERE fav.user_id = :uid AND a.published = 1
                GROUP BY a.id
                ORDER BY favorited_at DESC $limitStr";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function getByAuthor(int $authorId, int $limit = 0): array
    {
        $limitStr = $limit > 0 ? "LIMIT $limit" : '';
        $sql = "SELECT a.*, u.pseudo AS author,
                       COUNT(DISTINCT l.user_id)  AS likes,
                       COUNT(DISTINCT c.id)        AS comments,
                       COUNT(DISTINCT f.user_id)  AS favorites
                FROM articles a
                JOIN users u ON u.id = a.author_id
                LEFT JOIN likes     l ON l.article_id = a.id
                LEFT JOIN comments  c ON c.article_id = a.id
                LEFT JOIN favorites f ON f.article_id = a.id
                WHERE a.author_id = :aid AND a.published = 1
                GROUP BY a.id
                ORDER BY a.created_at DESC $limitStr";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':aid' => $authorId]);
        return $stmt->fetchAll();
    }

    public function syncFilms(int $articleId, array $tmdbIds): void
    {
        $this->db->prepare("DELETE FROM article_films WHERE article_id = :id")->execute([':id' => $articleId]);
        $stmt = $this->db->prepare("INSERT INTO article_films (article_id, tmdb_id) VALUES (:aid, :tid)");
        foreach ($tmdbIds as $tid) {
            $stmt->execute([':aid' => $articleId, ':tid' => (int) $tid]);
        }
    }

    public function syncLieux(int $articleId, array $lieuIds): void
    {
        $this->db->prepare("DELETE FROM article_lieux WHERE article_id = :id")->execute([':id' => $articleId]);
        $stmt = $this->db->prepare('INSERT INTO article_lieux (article_id, lieu_id) VALUES (:aid, :lid)');
        foreach ($lieuIds as $lid) {
            $stmt->execute([':aid' => $articleId, ':lid' => (int) $lid]);
        }
    }

    public function replaceSliderImages(int $articleId, array $images): void
    {
        $this->db->prepare('DELETE FROM article_slider_images WHERE article_id = :id')->execute([':id' => $articleId]);

        if (empty($images)) {
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO article_slider_images (article_id, image_path, slider_title, slider_text) VALUES (:aid, :path, :title, :text)'
        );

        foreach ($images as $image) {
            $stmt->execute([
                ':aid' => $articleId,
                ':path' => $image['path'],
                ':title' => $image['title'],
                ':text' => $image['text'],
            ]);
        }
    }

    public function getPublishedSince(string $since, int $limit = 4): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.pseudo AS author,
                    COUNT(DISTINCT l.user_id) AS likes,
                    COUNT(DISTINCT c.id)       AS comments,
                    COUNT(DISTINCT f.user_id)  AS favorites
             FROM articles a
             JOIN users u ON u.id = a.author_id
             LEFT JOIN likes     l ON l.article_id = a.id
             LEFT JOIN comments  c ON c.article_id = a.id
             LEFT JOIN favorites f ON f.article_id = a.id
             WHERE a.published = 1 AND a.created_at >= :since
             GROUP BY a.id
             ORDER BY a.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':since', $since);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function hasPublishAtColumn(): bool
    {
        if ($this->hasPublishAtColumn !== null) {
            return $this->hasPublishAtColumn;
        }

        $this->hasPublishAtColumn = $this->columnExists('publish_at');
        return $this->hasPublishAtColumn;
    }

    private function hasArticleColumn(string $column): bool
    {
        return $this->columnExists($column);
    }

    private function columnExists(string $column): bool
    {
        $columns = $this->getArticleColumns();
        return in_array($column, $columns, true);
    }

    private function getArticleColumns(): array
    {
        if ($this->articleColumns !== null) {
            return $this->articleColumns;
        }

        $stmt = $this->db->query('SHOW COLUMNS FROM articles');
        $this->articleColumns = array_map(static fn($row) => (string) $row['Field'], $stmt->fetchAll());
        return $this->articleColumns;
    }
}
