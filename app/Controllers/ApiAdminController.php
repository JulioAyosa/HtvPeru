<?php
namespace App\Controllers;

use Config\Database;
use PDO;

class ApiAdminController {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
        // Verificación estricta de seguridad
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            die(json_encode(['status' => 'error', 'message' => 'No autorizado']));
        }
    }

    public function autosave() {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die(json_encode(['error' => 'Method not allowed']));
        }
        
        $token = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_tokens']) || !in_array($token, $_SESSION['csrf_tokens'], true)) {
            if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
                http_response_code(403);
                die(json_encode(['status' => 'error', 'message' => 'Token CSRF inválido']));
            }
        }
        
        $titulo = $_POST['titulo'] ?? 'Borrador sin título';
        $contenido = $_POST['contenido'] ?? '';
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $last_updated_at = $_POST['last_updated_at'] ?? '';
        
        if ($id === 0) {
            echo json_encode(['status' => 'info', 'message' => 'Autoguardado requiere salvar manualmente la primera vez.']);
            exit;
        }

        // Validación de Concurrencia (Optimistic Locking)
        if (!empty($last_updated_at)) {
            $stmt_check = $this->pdo->prepare("SELECT updated_at FROM noticias WHERE id = ?");
            $stmt_check->execute([$id]);
            $current_updated_at = $stmt_check->fetchColumn();
            
            if ($current_updated_at && strtotime($current_updated_at) > strtotime($last_updated_at)) {
                http_response_code(409);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Conflicto: El artículo fue modificado por otro usuario/ventana recientemente.',
                    'conflict' => true
                ]);
                exit;
            }
        }

        if ($_SESSION['user_role'] === 'admin') {
            $stmt = $this->pdo->prepare("UPDATE noticias SET titulo = ?, contenido = ? WHERE id = ? AND estado_publicacion != 'publicado'");
            $params = [$titulo, $contenido, $id];
        } else {
            $stmt = $this->pdo->prepare("UPDATE noticias SET titulo = ?, contenido = ? WHERE id = ? AND estado_publicacion != 'publicado' AND autor_id = ?");
            $params = [$titulo, $contenido, $id, $_SESSION['user_id']];
        }
        
        if ($stmt->execute($params)) {
            // Read fresh updated_at token to pass back to client
            $stmt_new = $this->pdo->prepare("SELECT updated_at FROM noticias WHERE id = ?");
            $stmt_new->execute([$id]);
            $new_updated_at = $stmt_new->fetchColumn();
            
            echo json_encode(['status' => 'success', 'time' => date('H:i:s'), 'new_updated_at' => $new_updated_at]);
        } else {
            echo json_encode(['status' => 'error']);
        }
        exit;
    }

    public function backupSize() {
        header('Content-Type: application/json');
        
        $dbName = $_ENV['DB_NAME'] ?? 'piura_noticias_db';
        $stmtDB = $this->pdo->prepare("
            SELECT SUM(data_length + index_length) AS db_size 
            FROM information_schema.tables 
            WHERE table_schema = ?
        ");
        $stmtDB->execute([$dbName]);
        $db_size = (int)$stmtDB->fetchColumn() ?: 0;
        
        $getDirSize = function($path) {
            $size = 0;
            if (is_dir($path)) {
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
                foreach ($iterator as $file) {
                    if ($file->isFile()) $size += $file->getSize();
                }
            }
            return $size;
        };
        
        $uploads_size = $getDirSize(__DIR__ . '/../../uploads');
        $media_size = $getDirSize(__DIR__ . '/../../capturas');
        $core_size = $getDirSize(__DIR__ . '/../../app') + $getDirSize(__DIR__ . '/../../core') + $getDirSize(__DIR__ . '/../../vendor');
        
        echo json_encode([
            'db_size' => $db_size,
            'uploads_size' => $uploads_size,
            'media_size' => $media_size,
            'core_size' => $core_size
        ]);
        exit;
    }

    public function boletinDestinatarios() {
        header('Content-Type: application/json');
        $historialId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$historialId) {
            echo json_encode([]);
            exit;
        }
        
        $stmt = $this->pdo->prepare("SELECT s.email FROM boletin_envios be JOIN suscriptores s ON be.suscriptor_id = s.id WHERE be.boletin_historial_id = ?");
        $stmt->execute([$historialId]);
        $destinatarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode($destinatarios);
        exit;
    }
}
