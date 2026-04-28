<?php
namespace App\Models;

use Config\Database;
use PDO;

class UserModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getActiveUsers() {
        $stmt = $this->pdo->query("SELECT id, nombre_completo, email, rol, fecha_creacion, estado, avatar_url FROM usuarios WHERE deleted_at IS NULL ORDER BY nombre_completo ASC");
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT id, nombre_completo, email, rol, estado FROM usuarios WHERE id = :id AND deleted_at IS NULL LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email AND deleted_at IS NULL LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }
}
