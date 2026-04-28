<?php
namespace App\Models;

use Config\Database;
use PDO;

class ArticleModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getLatest($limit = 15) {
        $stmt = $this->pdo->prepare("SELECT titulo, id, slug, categoria, imagen_url, video_poster_url, fecha_publicacion FROM noticias WHERE categoria != 'Publicidad' AND estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getHighlighted($limit = 9) {
        $stmt = $this->pdo->prepare("SELECT id, slug, titulo, categoria, extracto, imagen_url, video_poster_url FROM noticias WHERE categoria != 'Publicidad' AND estado_publicacion = 'publicado' ORDER BY es_destacada DESC, fecha_publicacion DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMostRead($limit = 5) {
        $stmt = $this->pdo->prepare("SELECT id, slug, titulo FROM noticias WHERE categoria != 'Publicidad' AND estado_publicacion = 'publicado' ORDER BY vistas DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getBySlug($slug) {
        $stmt = $this->pdo->prepare("SELECT * FROM noticias WHERE slug = :slug AND estado_publicacion = 'publicado' LIMIT 1");
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch();
    }
}
