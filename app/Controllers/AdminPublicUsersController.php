<?php
namespace App\Controllers;

class AdminPublicUsersController {
    public function __construct() {
        require_once __DIR__ . '/../../config/bootstrap.php';
        
        // Verificar que sea admin o gerente
        $role = $_SESSION['user_role'] ?? '';
        if ($role !== 'admin' && $role !== 'gerente') {
            header("Location: " . APP_BASE . "admin?msg=" . urlencode("No tienes permiso para acceder a este módulo."));
            exit;
        }
    }

    public function index() {
        global $pdo;

        $msg = isset($_GET['msg']) ? htmlspecialchars(urldecode($_GET['msg'])) : '';
        
        // Paginación
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Búsqueda
        $search = $_GET['search'] ?? '';
        $where = "1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (nombre LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $query = "SELECT up.*, 
                  (SELECT COUNT(*) FROM comentarios c WHERE c.usuario_publico_id = up.id) AS total_comentarios
                  FROM usuarios_publicos up 
                  WHERE $where 
                  ORDER BY fecha_registro DESC 
                  LIMIT $limit OFFSET $offset";
                  
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $usuarios = $stmt->fetchAll();

        // Total para paginación
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM usuarios_publicos WHERE $where");
        $stmt_count->execute($params);
        $total_usuarios = $stmt_count->fetchColumn();
        $total_pages = ceil($total_usuarios / $limit);

        $page_title = 'Usuarios Públicos (OAuth)';
        
        ob_start();
        require __DIR__ . '/../Views/admin/usuarios_publicos/index.php';
        $view_content = ob_get_clean();
        
        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/layouts/admin.php';
    }

    public function toggleStatus($id) {
        global $pdo;
        
        if ($_SESSION['user_role'] !== 'admin') {
            header("Location: " . APP_BASE . "admin/usuarios-publicos?msg=" . urlencode("No tienes permisos para bloquear usuarios."));
            exit;
        }

        $stmt = $pdo->prepare("SELECT estado FROM usuarios_publicos WHERE id = ?");
        $stmt->execute([$id]);
        $current_status = $stmt->fetchColumn();

        if ($current_status) {
            $new_status = ($current_status === 'activo') ? 'bloqueado' : 'activo';
            $stmt_update = $pdo->prepare("UPDATE usuarios_publicos SET estado = ? WHERE id = ?");
            $stmt_update->execute([$new_status, $id]);
            
            // Log de actividad
            $stmt_log = $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)");
            $stmt_log->execute([$_SESSION['user_id'], 'Moderación OAuth', "Cambió estado de usuario público #$id a $new_status"]);
            
            header("Location: " . APP_BASE . "admin/usuarios-publicos?msg=" . urlencode("Estado del usuario actualizado a $new_status."));
            exit;
        }

        header("Location: " . APP_BASE . "admin/usuarios-publicos?msg=" . urlencode("Usuario no encontrado."));
        exit;
    }
}
