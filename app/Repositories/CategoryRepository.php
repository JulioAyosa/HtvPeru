<?php
namespace App\Repositories;

use PDO;

class CategoryRepository {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getCategoryByNameOrSlug($name, $slug) {
        $stmt = $this->pdo->prepare("SELECT nombre, descripcion, imagen_fondo FROM categorias WHERE nombre = ? OR slug = ? LIMIT 1");
        $stmt->execute([$name, $slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAllActive() {
        $stmt = $this->pdo->query("SELECT nombre, slug FROM categorias WHERE estado='activo'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
