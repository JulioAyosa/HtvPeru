<?php
namespace App\Controllers;

use Config\Database;

class AdminActivityController {
    
    public function __construct() {
        require_once __DIR__ . '/../../conexion.php';
        
        
        
        if (function_exists('require_permission')) {
            require_permission('view_reports');
        } else {
            if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'gerencia') {
                header('HTTP/1.1 403 Forbidden');
                die('Acceso denegado.');
            }
        }
    }

    public function index() {
        global $pdo;
        
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'];
        $user_role = $_SESSION['user_role'];
        
        $per_page = 50;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $per_page;

        $total_rows = (int)$pdo->query("SELECT COUNT(*) FROM registro_actividad")->fetchColumn();
        $total_pages = max(1, ceil($total_rows / $per_page));
        if ($page > $total_pages) $page = $total_pages;

        $stmt_act = $pdo->prepare("SELECT r.id, r.accion, r.detalles, r.fecha_registro, u.nombre_completo AS usuario 
                  FROM registro_actividad r 
                  JOIN usuarios u ON r.user_id = u.id 
                  ORDER BY r.fecha_registro DESC LIMIT :lim OFFSET :off");
        $stmt_act->bindValue(':lim', $per_page, \PDO::PARAM_INT);
        $stmt_act->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt_act->execute();
        $actividades = $stmt_act->fetchAll();

        $page_title = 'Registro de Actividad';
        
        ob_start();
        require __DIR__ . '/../Views/admin/actividad/index.php';
        $view_content = ob_get_clean();
        
        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/layouts/admin.php';
    }
}
