<?php
namespace App\Controllers;

use Config\Database;
use PDO;

class ApiPublicController {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function heartbeat() {
        if (isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'alive', 'time' => time()]);
        } else {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['status' => 'expired']);
        }
        exit;
    }

    public function search() {
        header('Content-Type: application/json; charset=utf-8');
        
        $query = trim($_GET['q'] ?? '');
        if (mb_strlen($query) > 100) $query = mb_substr($query, 0, 100);
        if (strlen($query) < 2) {
            echo json_encode([]);
            exit;
        }

        $client_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        try {
            $rate_stmt = $this->pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE ip = ? AND action = 'search' AND created_at > NOW() - INTERVAL 1 MINUTE");
            $rate_stmt->execute([$client_ip]);
            if ((int)$rate_stmt->fetchColumn() >= 30) {
                echo json_encode([]);
                exit;
            }
            $this->pdo->prepare("INSERT INTO rate_limits (ip, action) VALUES (?, 'search')")->execute([$client_ip]);
        } catch (\Exception $e) {}

        try {
            $searchTerm = "%{$query}%";
            $stmt = $this->pdo->prepare("SELECT titulo, slug, imagen_url FROM noticias WHERE estado_publicacion='publicado' AND titulo LIKE ? ORDER BY fecha_publicacion DESC LIMIT 5");
            $stmt->execute([$searchTerm]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($results);
        } catch (\PDOException $e) {
            echo json_encode([]);
        }
        exit;
    }

    public function viewCounter() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['id']) ? (int)$input['id'] : 0;
        $slug = isset($input['slug']) ? trim($input['slug']) : '';
        
        if (!$id && !$slug) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Bad request']);
            exit;
        }

        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            // Basic rate limit to prevent DB flood from same IP in short time
            $rate_stmt = $this->pdo->prepare("SELECT COUNT(*) FROM cola_vistas WHERE ip_address = ? AND (noticia_slug = ? OR noticia_id = ?)");
            $rate_stmt->execute([$ip, $slug, $id]);
            if ((int)$rate_stmt->fetchColumn() < 3) {
                if ($slug) {
                    $this->pdo->prepare("INSERT INTO cola_vistas (noticia_slug, ip_address) VALUES (?, ?)")->execute([$slug, $ip]);
                } else {
                    $this->pdo->prepare("INSERT INTO cola_vistas (noticia_id, ip_address) VALUES (?, ?)")->execute([$id, $ip]);
                }
                echo json_encode(['success' => true, 'message' => 'View queued asynchronously']);
            } else {
                echo json_encode(['success' => true, 'message' => 'View limited by IP rules']);
            }
        } catch(\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'DB execution error']);
        }
        exit;
    }

    public function pollVote() {
        header('Content-Type: application/json; charset=utf-8');

        $allowed_host = $_SERVER['HTTP_HOST'] ?? '';
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if ($origin && parse_url($origin, PHP_URL_HOST) !== $allowed_host) {
            http_response_code(403);
            echo json_encode(['error' => 'Origen no autorizado.']);
            exit;
        }
        if (!$origin && $referer && parse_url($referer, PHP_URL_HOST) !== $allowed_host) {
            http_response_code(403);
            echo json_encode(['error' => 'Referencia no autorizada.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $encuesta_id = isset($_POST['encuesta_id']) ? (int)$_POST['encuesta_id'] : 0;
            $opcion_id = isset($_POST['opcion_id']) ? (int)$_POST['opcion_id'] : 0;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

            try {
                $rate_stmt = $this->pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE ip = ? AND action = 'vote' AND created_at > NOW() - INTERVAL 1 HOUR");
                $rate_stmt->execute([$ip]);
                if ((int)$rate_stmt->fetchColumn() >= 10) {
                    echo json_encode(['error' => 'Demasiados votos. Intenta más tarde.']);
                    exit;
                }
            } catch (\Exception $e) {}

            if ($encuesta_id > 0 && $opcion_id > 0) {
                $stmt_val = $this->pdo->prepare("SELECT id FROM encuestas_opciones WHERE id = ? AND encuesta_id = ?");
                $stmt_val->execute([$opcion_id, $encuesta_id]);
                if(!$stmt_val->fetch()) {
                    echo json_encode(['error' => 'Opción inválida.']);
                    exit;
                }

                $stmt_check = $this->pdo->prepare("SELECT id FROM encuestas_votos WHERE encuesta_id = ? AND ip_address = ?");
                $stmt_check->execute([$encuesta_id, $ip]);
                if ($stmt_check->fetch()) {
                    echo json_encode(['error' => 'Ya has votado.']);
                    exit;
                }

                if (isset($_COOKIE['voted_' . $encuesta_id])) {
                    echo json_encode(['error' => 'Ya has votado.']);
                    exit;
                }

                try {
                    $this->pdo->beginTransaction();
                    $stmt = $this->pdo->prepare("INSERT INTO encuestas_votos (encuesta_id, ip_address, opcion_id) VALUES (?, ?, ?)");
                    $stmt->execute([$encuesta_id, $ip, $opcion_id]);

                    $stmt_up = $this->pdo->prepare("UPDATE encuestas_opciones SET votos = votos + 1 WHERE id = ?");
                    $stmt_up->execute([$opcion_id]);
                    
                    $this->pdo->commit();

                    try {
                        $this->pdo->prepare("INSERT INTO rate_limits (ip, action) VALUES (?, 'vote')")->execute([$ip]);
                    } catch (\Exception $e) {}

                    setcookie('voted_' . $encuesta_id, '1', time() + (86400 * 30), "/");

                    echo json_encode(['success' => true]);
                    exit;
                } catch(\Exception $e) {
                    $this->pdo->rollBack();
                    echo json_encode(['error' => 'Error de base de datos. Tal vez ya votaste.']);
                    exit;
                }
            }
            echo json_encode(['error' => 'Datos inválidos.']);
        }
    }

    public function subscribe() {
        header('Content-Type: application/json; charset=utf-8');

        $allowed_host = $_SERVER['HTTP_HOST'] ?? '';
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if ($origin && parse_url($origin, PHP_URL_HOST) !== $allowed_host) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Origen no autorizado.']);
            exit;
        }
        if (!$origin && $referer && parse_url($referer, PHP_URL_HOST) !== $allowed_host) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Referencia no autorizada.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $honeypot = trim($_POST['website_hp'] ?? '');
            if (!empty($honeypot)) {
                echo json_encode(['success' => true, 'message' => '¡Suscripción exitosa!']);
                exit; 
            }

            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Email no válido.']);
                exit;
            }

            $client_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            try {
                $rate_stmt = $this->pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE ip = ? AND action = 'subscribe' AND created_at > NOW() - INTERVAL 1 MINUTE");
                $rate_stmt->execute([$client_ip]);
                if ((int)$rate_stmt->fetchColumn() >= 5) {
                    echo json_encode(['success' => false, 'message' => 'Demasiados intentos. Espera un momento.']);
                    exit;
                }
                $this->pdo->prepare("INSERT INTO rate_limits (ip, action) VALUES (?, 'subscribe')")->execute([$client_ip]);
            } catch (\Exception $e) {}

            try {
                $stmt = $this->pdo->prepare("INSERT INTO suscriptores (email) VALUES (?)");
                $stmt->execute([$email]);
                echo json_encode(['success' => true, 'message' => '¡Suscripción exitosa! Te enviaremos las mejores noticias.']);
            } catch (\PDOException $e) {
                if ($e->getCode() == 23000) { 
                    echo json_encode(['success' => false, 'message' => 'Este correo ya está suscrito a nuestro boletín.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error técnico al suscribirse.']);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
        }
    }
}
