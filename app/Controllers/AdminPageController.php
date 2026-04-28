<?php
namespace App\Controllers;

use Config\Database;

class AdminPageController {
    
    public function __construct() {
        require_once __DIR__ . '/../../conexion.php';
        require_once __DIR__ . '/../../html_sanitizer.php';
        
        
        
        if (function_exists('require_permission')) {
            require_permission('manage_pages');
        } else {
            if ($_SESSION['user_role'] !== 'admin') {
                header('HTTP/1.1 403 Forbidden');
                die('Acceso denegado.');
            }
        }
    }

    public function index() {
        global $pdo;
        
        $msg_codes = [
            'eliminado' => 'Página eliminada permanentemente.',
            'created' => 'Página creada exitosamente.',
            'create_error' => 'Error al crear la página. ¿Quizás el título ya existe?',
            'updated' => 'Página actualizada exitosamente.',
            'update_error' => 'Error al actualizar la página.',
        ];
        $msg_key = $_GET['msg'] ?? '';
        $msg = $msg_codes[$msg_key] ?? '';

        $stmt_cfg = $pdo->query("SELECT clave, valor FROM configuracion");
        $configs = [];
        while ($row = $stmt_cfg->fetch()) { $configs[$row['clave']] = $row['valor']; }

        $paginas = $pdo->query("SELECT id, titulo, slug, estado, fecha_modificacion FROM paginas WHERE deleted_at IS NULL ORDER BY titulo ASC")->fetchAll();

        $page_title = 'Páginas Estáticas';
        
        ob_start();
        require __DIR__ . '/../Views/admin/paginas/index.php';
        $view_content = ob_get_clean();
        
        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/layouts/admin.php';
    }

    public function store() {
        global $pdo;

        $action = $_POST['action'] ?? '';
        $redirect_msg = '';
        
        if ($action === 'create' || $action === 'update') {
            $titulo = $_POST['titulo'] ?? '';
            $base_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo), '-'));
            $slug = $base_slug;
            $contenido = sanitize_html($_POST['contenido'] ?? '');
            $estado = $_POST['estado'] ?? 'activo';
            $id_check = ($action === 'update') ? (int)($_POST['id'] ?? 0) : 0;
            
            $counter = 1;
            while (true) {
                $stmt_chk = $pdo->prepare("SELECT id FROM paginas WHERE slug = ? AND id != ?");
                $stmt_chk->execute([$slug, $id_check]);
                if (!$stmt_chk->fetch()) break;
                $slug = $base_slug . '-' . $counter;
                $counter++;
            }
            
            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO paginas (titulo, slug, contenido, estado) VALUES (?, ?, ?, ?)");
                if($stmt->execute([$titulo, $slug, $contenido, $estado])) {
                    $nuevo_id = $pdo->lastInsertId();
                    $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], 'Creación', 'Creó la página estática ID #' . $nuevo_id . ' (' . $titulo . ')']);
                    $redirect_msg = 'created';
                } else {
                    $redirect_msg = 'create_error';
                }
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $stmt_old = $pdo->prepare("SELECT * FROM paginas WHERE id = ?");
                $stmt_old->execute([$id]);
                $old = $stmt_old->fetch();
                $cambios = [];
                if ($old && $old['titulo'] !== $titulo) $cambios[] = 'título';
                if ($old && $old['slug'] !== $slug) $cambios[] = 'URL';
                if ($old && $old['contenido'] !== $contenido) $cambios[] = 'contenido HTML';
                if ($old && $old['estado'] !== $estado) $cambios[] = 'estado';
                
                $stmt = $pdo->prepare("UPDATE paginas SET titulo=?, slug=?, contenido=?, estado=? WHERE id=?");
                if ($stmt->execute([$titulo, $slug, $contenido, $estado, $id])) {
                    $detalles_msg = 'Actualizó página estática ID #' . $id . ' (' . $titulo . ')';
                    if (!empty($cambios)) {
                        $detalles_msg .= '. Modificó: ' . implode(', ', $cambios);
                    }
                    $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], 'Actualización', $detalles_msg]);
                    $redirect_msg = 'updated';
                } else {
                    $redirect_msg = 'update_error';
                }
            }
        }
        
        header('Location: /piura_noticias_php/admin/paginas?msg=' . urlencode($redirect_msg));
        exit;
    }
    
    public function action() {
        global $pdo;

        if (isset($_GET['delete'])) {
            $id = (int)$_GET['delete'];
            $stmt_del = $pdo->prepare("SELECT titulo FROM paginas WHERE id = ?");
            $stmt_del->execute([$id]);
            $pag_name = $stmt_del->fetchColumn() ?: 'Desconocida';
            
            $pdo->prepare("UPDATE paginas SET deleted_at = NOW() WHERE id = ?")->execute([$id]);
            $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], 'Eliminación', 'Borró de forma permanente la página ID #' . $id . ' (' . $pag_name . ')']);
            header("Location: /piura_noticias_php/admin/paginas?msg=eliminado");
            exit;
        }
        header("Location: /piura_noticias_php/admin/paginas");
        exit;
    }
    
    public function get() {
        global $pdo;
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT id, titulo, contenido, estado FROM paginas WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            header('Content-Type: application/json');
            echo json_encode($data);
        } else {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Invalid ID']);
        }
        exit;
    }
}
