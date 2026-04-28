<?php
namespace App\Repositories;

use PDO;
use Exception;

class NewsRepository {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function searchNoticias($query, $limit = 50) {
        $stmt = $this->pdo->prepare("SELECT id, slug, titulo, categoria, extracto, imagen_url, video_poster_url, fecha_publicacion FROM noticias WHERE (titulo LIKE ? OR contenido LIKE ?) AND estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC LIMIT ?");
        $stmt->bindValue(1, "%$query%", PDO::PARAM_STR);
        $stmt->bindValue(2, "%$query%", PDO::PARAM_STR);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUltimas($limit = 15) {
        $stmt = $this->pdo->prepare("SELECT titulo, id, slug FROM noticias WHERE categoria != 'Publicidad' AND estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDestacadas($limit = 9) {
        $stmt = $this->pdo->prepare("SELECT id, slug, titulo, categoria, extracto, imagen_url, video_poster_url FROM noticias WHERE categoria != 'Publicidad' AND estado_publicacion = 'publicado' ORDER BY es_destacada DESC, fecha_publicacion DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecientes($limit = 10, $offset = 9) {
        $stmt = $this->pdo->prepare("SELECT id, slug, titulo, categoria, imagen_url, video_poster_url, fecha_publicacion FROM noticias WHERE categoria != 'Publicidad' AND estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMasLeido($limit = 5) {
        $stmt = $this->pdo->prepare("SELECT id, slug, titulo FROM noticias WHERE categoria != 'Publicidad' AND estado_publicacion = 'publicado' ORDER BY vistas DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTodasNoticias($limit = 50) {
        $stmt = $this->pdo->prepare("SELECT id, slug, titulo, categoria, imagen_url, video_poster_url, fecha_publicacion FROM noticias WHERE categoria != 'Publicidad' AND estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRegionales($limit = 24) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, slug, titulo, categoria, distrito, imagen_url, video_poster_url, fecha_publicacion FROM noticias WHERE (categoria LIKE 'Local%' OR categoria = 'Regional') AND estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC LIMIT ?");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            return [];
        }
    }

    public function getByCategoria($categoria, $limit = 4) {
        $stmt = $this->pdo->prepare("SELECT id, slug, titulo, categoria, imagen_url, video_poster_url FROM noticias WHERE categoria = ? AND estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC LIMIT ?");
        $stmt->bindValue(1, $categoria, PDO::PARAM_STR);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEntretenimiento($limit = 6) {
        $stmt = $this->pdo->prepare("SELECT id, slug, titulo, categoria, imagen_url, video_poster_url FROM noticias WHERE categoria IN ('Entretenimiento', 'Tendencias', 'Salud') AND estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStories($limit = 12) {
        $stmt = $this->pdo->prepare("SELECT id, slug, titulo, imagen_url, video_poster_url FROM noticias WHERE estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC, id DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBreakingNews($categoria) {
        $stmt = $this->pdo->prepare("SELECT slug, titulo FROM noticias WHERE categoria = ? AND estado_publicacion = 'publicado' AND fecha_publicacion >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY fecha_publicacion DESC LIMIT 1");
        $stmt->bindValue(1, $categoria, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAnuncioCabecera() {
        $stmt = $this->pdo->query("SELECT titulo, imagen_url, fuente_url FROM noticias WHERE categoria = 'Publicidad' AND estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getArticleBySlug($slug) {
        $stmt = $this->pdo->prepare("SELECT n.*, u.nombre_completo AS autor FROM noticias n JOIN usuarios u ON n.autor_id = u.id WHERE n.slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getArticleById($id) {
        $stmt = $this->pdo->prepare("SELECT n.*, u.nombre_completo AS autor FROM noticias n JOIN usuarios u ON n.autor_id = u.id WHERE n.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getRelacionadas($categoria, $exclude_id, $limit = 3) {
        $stmt = $this->pdo->prepare("SELECT id, slug, titulo, categoria, imagen_url, video_poster_url FROM noticias WHERE categoria = ? AND id != ? AND estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC LIMIT ?");
        $stmt->bindValue(1, $categoria, PDO::PARAM_STR);
        $stmt->bindValue(2, $exclude_id, PDO::PARAM_INT);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryPaginated($categoria, $limit, $offset) {
        $stmt = $this->pdo->prepare("
            SELECT n.id, n.slug, n.titulo, n.categoria, n.extracto, n.imagen_url, n.video_poster_url, n.fecha_publicacion, u.nombre_completo AS autor 
            FROM noticias n JOIN usuarios u ON n.autor_id = u.id 
            WHERE n.categoria = :categoria AND n.estado_publicacion = 'publicado' 
            ORDER BY n.fecha_publicacion DESC LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':categoria', $categoria, \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countCategory($categoria) {
        $total_stmt = $this->pdo->prepare("SELECT COUNT(*) FROM noticias WHERE categoria = ? AND estado_publicacion = 'publicado'");
        $total_stmt->execute([$categoria]);
        return $total_stmt->fetchColumn();
    }

    public function searchFulltextPaginated($boolean_q, $limit, $offset) {
        $stmt = $this->pdo->prepare("
            SELECT n.id, n.slug, n.titulo, n.categoria, n.extracto, n.imagen_url, n.video_poster_url, n.fecha_publicacion, u.nombre_completo AS autor 
            FROM noticias n JOIN usuarios u ON n.autor_id = u.id 
            WHERE MATCH(n.titulo, n.extracto, n.tags) AGAINST(:q1 IN BOOLEAN MODE) AND n.estado_publicacion = 'publicado' 
            ORDER BY n.fecha_publicacion DESC LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':q1', $boolean_q, \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countSearchFulltext($boolean_q) {
        $total_stmt = $this->pdo->prepare("SELECT COUNT(*) FROM noticias WHERE MATCH(titulo, extracto, tags) AGAINST(? IN BOOLEAN MODE) AND estado_publicacion = 'publicado'");
        $total_stmt->execute([$boolean_q]);
        return $total_stmt->fetchColumn();
    }

    public function getUltimasPaginated($limit, $offset) {
        $stmt = $this->pdo->prepare("
            SELECT n.id, n.slug, n.titulo, n.categoria, n.extracto, n.imagen_url, n.video_poster_url, n.fecha_publicacion, u.nombre_completo AS autor 
            FROM noticias n JOIN usuarios u ON n.autor_id = u.id 
            WHERE n.estado_publicacion = 'publicado' 
            ORDER BY n.fecha_publicacion DESC LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByTagPaginated($tag_name, $limit, $offset) {
        $stmt = $this->pdo->prepare("
            SELECT n.id, n.slug, n.titulo, n.categoria, n.extracto, n.imagen_url, n.video_poster_url, n.fecha_publicacion, u.nombre_completo AS autor 
            FROM noticias n 
            JOIN usuarios u ON n.autor_id = u.id 
            WHERE n.tags LIKE :q AND n.estado_publicacion = 'publicado' 
            ORDER BY n.fecha_publicacion DESC 
            LIMIT :limit OFFSET :offset
        ");
        $searchTerm = '%' . $tag_name . '%';
        $stmt->bindValue(':q', $searchTerm, \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByTag($tag_name) {
        $total_stmt = $this->pdo->prepare("SELECT COUNT(*) FROM noticias WHERE tags LIKE ? AND estado_publicacion = 'publicado'");
        $searchTerm = '%' . $tag_name . '%';
        $total_stmt->execute([$searchTerm]);
        return $total_stmt->fetchColumn();
    }
}
