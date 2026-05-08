<?php
namespace App\Controllers;

use Config\Database;

class AdminPollController {
    
    public function __construct() {
        // Enforce dependencies and session
        require_once __DIR__ . '/../../conexion.php';
        
        
        
        if (function_exists('require_permission')) {
            require_permission('manage_polls');
        } else {
            // Fallback auth
            if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'editor'])) {
                header('HTTP/1.1 403 Forbidden');
                die('Acceso denegado.');
            }
        }
    }

    public function index() {
        global $pdo;
        
        $msg = $_GET['msg'] ?? '';

        $stmt_enc = $pdo->query("SELECT e.*, u.nombre_completo as autor FROM encuestas e LEFT JOIN usuarios u ON e.creador_id = u.id WHERE e.deleted_at IS NULL ORDER BY e.fecha_creacion DESC");
        $encuestas = $stmt_enc->fetchAll();

        $page_title = 'Gestión Integrada de Encuestas';
        
        ob_start();
        require __DIR__ . '/../Views/admin/encuestas/index.php';
        $view_content = ob_get_clean();
        
        // Change context back to root to ensure paths like 'img/' or 'css/' resolve if they are relative
        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/layouts/admin.php';
    }

    public function store() {
        global $pdo;
        
        

        $pregunta = trim($_POST['pregunta'] ?? '');
        $opciones = $_POST['opciones'] ?? [];
        $fecha_limite = !empty($_POST['fecha_limite']) ? $_POST['fecha_limite'] : null;
        $estado_inicial = isset($_POST['activar_ahora']) ? 'activo' : 'inactivo';
        
        $opciones_validas = array_filter($opciones, function($o) { return trim($o) !== ''; });

        if (!empty($pregunta) && count($opciones_validas) >= 2) {
            $pdo->beginTransaction();
            try {
                if ($estado_inicial === 'activo') {
                    $pdo->query("UPDATE encuestas SET estado = 'inactivo'");
                }
                $stmt = $pdo->prepare("INSERT INTO encuestas (pregunta, creador_id, estado, fecha_limite) VALUES (?, ?, ?, ?)");
                $stmt->execute([$pregunta, $_SESSION['user_id'], $estado_inicial, $fecha_limite]);
                $encuesta_id = $pdo->lastInsertId();
                
                $stmt_op = $pdo->prepare("INSERT INTO encuestas_opciones (encuesta_id, opcion_texto) VALUES (?, ?)");
                foreach($opciones_validas as $opText) {
                    $stmt_op->execute([$encuesta_id, trim($opText)]);
                }
                $pdo->commit();
                header("Location: /piura_noticias_php/admin/encuestas?msg=" . urlencode("Nueva encuesta creada correctamente"));
                exit;
            } catch(\Exception $e) {
                $pdo->rollBack();
                header("Location: /piura_noticias_php/admin/encuestas?msg=" . urlencode("Error al crear encuesta"));
                exit;
            }
        } else {
            header("Location: /piura_noticias_php/admin/encuestas?msg=" . urlencode("Debe incluir una pregunta y al menos 2 opciones"));
            exit;
        }
    }

    public function action() {
        global $pdo;
        
        

        $action = $_POST['type'] ?? '';
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            header("Location: /piura_noticias_php/admin/encuestas?msg=" . urlencode("ID de encuesta no válido"));
            exit;
        }

        if ($action === 'activate') {
            $pdo->query("UPDATE encuestas SET estado = 'inactivo'");
            $stmt = $pdo->prepare("UPDATE encuestas SET estado = 'activo' WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: /piura_noticias_php/admin/encuestas?msg=" . urlencode("Encuesta activada en portada"));
            exit;
        }

        if ($action === 'relaunch') {
            $pdo->query("UPDATE encuestas SET estado = 'inactivo'");
            $stmt = $pdo->prepare("UPDATE encuestas SET estado = 'activo' WHERE id = ?");
            $stmt->execute([$id]);
            $stmt_op = $pdo->prepare("UPDATE encuestas_opciones SET votos = 0 WHERE encuesta_id = ?");
            $stmt_op->execute([$id]);
            header("Location: /piura_noticias_php/admin/encuestas?msg=" . urlencode("Encuesta reiniciada y devuelta a la portada"));
            exit;
        }

        if ($action === 'pause') {
            $stmt = $pdo->prepare("UPDATE encuestas SET estado = 'inactivo' WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: /piura_noticias_php/admin/encuestas?msg=" . urlencode("Encuesta pausada/retirada de la portada"));
            exit;
        }

        if ($action === 'delete') {
            if ($_SESSION['user_role'] === 'admin') {
                $pdo->prepare("UPDATE encuestas SET deleted_at = NOW() WHERE id = ?")->execute([$id]);
                $msg = "Encuesta enviada a la Papelera";
            } else {
                $msg = "No tienes permiso para eliminar recursos.";
            }
            header("Location: /piura_noticias_php/admin/encuestas?msg=" . urlencode($msg));
            exit;
        }

        header("Location: /piura_noticias_php/admin/encuestas");
        exit;
    }
}
