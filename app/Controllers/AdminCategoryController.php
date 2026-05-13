<?php
namespace App\Controllers;

use Config\Database;

class AdminCategoryController {
    
    public function __construct() {
        require_once __DIR__ . '/../../config/bootstrap.php';
        
        
        
        if (function_exists('require_permission')) {
            require_permission('manage_categories');
        } else {
            if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'editor') {
                header('HTTP/1.1 403 Forbidden');
                die('Acceso denegado.');
            }
        }
    }

    private function createSlugCat($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        if(function_exists('iconv')) $text = @iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text);
    }

    public function index() {
        global $pdo;
        
        $msg_codes = [
            'eliminado' => 'Categoría eliminada del taxónomo.',
            'menu_ok' => 'Orden y Configuraciones del Menú Actualizados.',
            'img_ok' => 'Imagen de fondo actualizada exitosamente.',
            'img_error' => 'Error al subir la imagen.',
            'img_format' => 'Formato no permitido. Use JPG, PNG, WebP o GIF.',
            'img_removed' => 'Imagen de fondo eliminada.',
            'created' => 'Nueva categoría creada exitosamente.',
        ];
        $msg_key = $_GET['msg'] ?? '';
        $msg = $msg_codes[$msg_key] ?? '';

        $stmt_cfg = $pdo->query("SELECT clave, valor FROM configuracion");
        $configs = [];
        while ($row = $stmt_cfg->fetch()) { $configs[$row['clave']] = $row['valor']; }

        $categorias = $pdo->query("SELECT * FROM categorias WHERE deleted_at IS NULL ORDER BY orden ASC, id DESC")->fetchAll();

        $page_title = 'Taxonomías y Menú';
        
        ob_start();
        require __DIR__ . '/../Views/admin/categorias/index.php';
        $view_content = ob_get_clean();
        
        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/layouts/admin.php';
    }

    public function action() {
        global $pdo;

        $action = $_POST['action'] ?? ($_GET['action'] ?? '');
        $redirect_msg = '';

        if (isset($_GET['delete'])) {
            $id = (int)$_GET['delete'];
            $stmt_del = $pdo->prepare("SELECT nombre FROM categorias WHERE id = ?");
            $stmt_del->execute([$id]);
            $cat_name = $stmt_del->fetchColumn() ?: 'Desconocida';
            
            $pdo->prepare("UPDATE categorias SET deleted_at = NOW() WHERE id = ?")->execute([$id]);
            $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], 'Eliminación', 'Borró la categoría ID #' . $id . ' (' . $cat_name . ') del menú y sistema principal']);
            header("Location: " . APP_BASE . "admin/categorias?msg=eliminado");
            exit;
        }

        if ($action === 'bulk_update') {
            if(isset($_POST['cat_id']) && is_array($_POST['cat_id'])) {
                $stmt = $pdo->prepare("UPDATE categorias SET orden = ?, mostrar_menu = ?, estado = ?, nombre = ?, descripcion = ? WHERE id = ?");
                foreach ($_POST['cat_id'] as $index => $id) {
                    $orden = (int)$_POST['cat_orden'][$index];
                    $mostrar = isset($_POST['cat_mostrar'][$id]) ? 1 : 0;
                    $estado = $_POST['cat_estado'][$index];
                    $nombre = $_POST['cat_nombre'][$index];
                    $descripcion = $_POST['cat_descripcion'][$index] ?? '';
                    $stmt->execute([$orden, $mostrar, $estado, $nombre, $descripcion, $id]);
                }
                $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], 'Actualización Masiva', 'Menú y categorías reorganizadas']);
                $redirect_msg = 'menu_ok';
            }
        }
        
        if ($action === 'upload_bg') {
            $cat_id = (int)($_POST['cat_bg_id'] ?? 0);
            if ($cat_id > 0 && isset($_FILES['cat_bg_image']) && $_FILES['cat_bg_image']['error'] === UPLOAD_ERR_OK) {
                $firewall = media_firewall_check($_FILES['cat_bg_image']['tmp_name'], $_FILES['cat_bg_image']['name']);
                if ($firewall['ok']) {
                    $ext = pathinfo($_FILES['cat_bg_image']['name'], PATHINFO_EXTENSION);
                    $filename = 'cat_bg_' . $cat_id . '_' . time() . '.' . strtolower($ext);
                    $upload_dir = PUBLIC_PATH . 'uploads/categorias/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    
                    $old_stmt = $pdo->prepare("SELECT imagen_fondo FROM categorias WHERE id = ?");
                    $old_stmt->execute([$cat_id]);
                    $old_img = $old_stmt->fetchColumn();
                    if ($old_img && file_exists(PUBLIC_PATH . $old_img)) {
                        @unlink(PUBLIC_PATH . $old_img);
                    }
                    
                    require_once __DIR__ . '/../../app/services/MediaUploaderService.php';
                    $uploader = new \App\Services\MediaUploaderService($upload_dir);
                    $upload_res = $uploader->handleSingleUpload($_FILES['cat_bg_image']);
                    if ($upload_res['success']) {
                        $filename = basename($upload_res['url']);
                        $rel_path = 'uploads/categorias/' . $filename;
                        $pdo->prepare("UPDATE categorias SET imagen_fondo = ? WHERE id = ?")->execute([$rel_path, $cat_id]);
                        $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], 'Imagen Categoría', 'Imagen de fondo actualizada para categoría ID #' . $cat_id]);
                        $redirect_msg = 'img_ok';
                    } else {
                        $redirect_msg = 'img_error';
                    }
                } else {
                    $redirect_msg = 'img_format';
                }
            }
        }
        
        if ($action === 'remove_bg') {
            $cat_id = (int)($_POST['cat_bg_id'] ?? 0);
            if ($cat_id > 0) {
                $old_stmt = $pdo->prepare("SELECT imagen_fondo FROM categorias WHERE id = ?");
                $old_stmt->execute([$cat_id]);
                $old_img = $old_stmt->fetchColumn();
                if ($old_img && file_exists(PUBLIC_PATH . $old_img)) {
                    @unlink(PUBLIC_PATH . $old_img);
                }
                $pdo->prepare("UPDATE categorias SET imagen_fondo = NULL WHERE id = ?")->execute([$cat_id]);
                $redirect_msg = 'img_removed';
            }
        }
        
        if ($action === 'create') {
            $nombre = trim($_POST['nuevo_nombre']);
            $slug = $this->createSlugCat($nombre);
            
            $check = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE slug = ?");
            $check->execute([$slug]);
            if($check->fetchColumn() > 0) {
                $slug .= '-' . time();
            }
            
            $pdo->prepare("INSERT INTO categorias (nombre, slug, orden) VALUES (?, ?, 99)")->execute([$nombre, $slug]);
            $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], 'Crear Categoría', 'Nueva sección: ' . $nombre]);
            $redirect_msg = 'created';
        }

        header("Location: " . APP_BASE . "admin/categorias?msg=" . urlencode($redirect_msg));
        exit;
    }
}
