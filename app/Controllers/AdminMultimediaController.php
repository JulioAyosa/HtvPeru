<?php
namespace App\Controllers;

use Core\Controller;
use Config\Database;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class AdminMultimediaController extends Controller {
    
    private $pdo;
    private $project_root;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $this->project_root = realpath(__DIR__ . '/../../') . '/';
        $this->pdo = Database::getInstance();
    }

    public function index() {
        require_permission('manage_media');
        
        if ($_SESSION['user_role'] === 'gerente') {
            header("Location: /piura_noticias_php/admin/reportes"); // todo: fix route later
            exit;
        }

        $user_role = $_SESSION['user_role'];
        $upload_dir = $this->project_root . 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $msg = $_GET['msg'] ?? '';
        $msg_map = [
            'ok_1' => 'Error: No se lograron mover todos los archivos seleccionados debido a permisos de Windows.',
            'ok_2' => 'No seleccionaste ningún archivo para eliminar.',
            'eliminado' => 'Archivo(s) enviado(s) a la papelera correctamente.'
        ];
        if (array_key_exists($msg, $msg_map)) {
            $msg = $msg_map[$msg];
        } else {
            $msg = htmlspecialchars($msg);
        }

        // Obtener Archivos de Forma Recursiva
        $files = [];
        if (is_dir($upload_dir)) {
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg'];
            
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($upload_dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $f = str_replace('\\', '/', $file->getPathname());
                    $normalized_dir = rtrim(str_replace('\\', '/', $upload_dir), '/') . '/';
                    
                    if (strpos($f, $normalized_dir) === 0) {
                        $rel_dir = substr($f, strlen($normalized_dir));
                    } else {
                        $rel_dir = basename($f); 
                    }
                    
                    if (strpos($rel_dir, 'papelera/') === 0) continue;
                    
                    $ext = strtolower($file->getExtension());
                    if (in_array($ext, $allowed_exts)) {
                        $dir_name = dirname($rel_dir) === '.' ? '/' : '/' . dirname($rel_dir);
                        // path relativo al proyecto para URLs del navegador
                        $browser_path = 'uploads/' . $rel_dir;
                        $files[] = [
                            'name' => basename($f),
                            'rel_path' => $rel_dir,
                            'path' => $browser_path,
                            'ext' => $ext,
                            'size' => $file->getSize(),
                            'time' => $file->getMTime(),
                            'folder_tag' => $dir_name
                        ];
                    }
                }
            }
            usort($files, function($a, $b) { return $b['time'] - $a['time']; });
        }

        // Build usage map: where each file is used
        $usage_map = [];
        try {
            // Noticias
            $stmt = $this->pdo->query("SELECT id, titulo, imagen_url FROM noticias WHERE imagen_url IS NOT NULL AND imagen_url != ''");
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $fname = basename($row['imagen_url']);
                $usage_map[$fname][] = ['type' => 'noticia', 'label' => 'Portada de noticia', 'detail' => $row['titulo'], 'id' => $row['id'], 'icon' => 'ri-newspaper-line', 'color' => '#3b82f6'];
            }
            // Usuarios avatar
            $stmt = $this->pdo->query("SELECT id, nombre_completo, avatar_url FROM usuarios WHERE avatar_url IS NOT NULL AND avatar_url != ''");
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $fname = basename($row['avatar_url']);
                $usage_map[$fname][] = ['type' => 'avatar', 'label' => 'Foto de perfil', 'detail' => $row['nombre_completo'], 'id' => $row['id'], 'icon' => 'ri-user-line', 'color' => '#8b5cf6'];
            }
            // Publicidad
            $stmt = $this->pdo->query("SELECT id, titulo, imagen_url FROM publicidad WHERE imagen_url IS NOT NULL AND imagen_url != ''");
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $fname = basename($row['imagen_url']);
                $usage_map[$fname][] = ['type' => 'publicidad', 'label' => 'Anuncio publicitario', 'detail' => $row['titulo'], 'id' => $row['id'], 'icon' => 'ri-advertisement-line', 'color' => '#f59e0b'];
            }
            // Categorías
            $stmt = $this->pdo->query("SELECT id, nombre, imagen_fondo FROM categorias WHERE imagen_fondo IS NOT NULL AND imagen_fondo != ''");
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $fname = basename($row['imagen_fondo']);
                $usage_map[$fname][] = ['type' => 'categoria', 'label' => 'Fondo de categoría', 'detail' => $row['nombre'], 'id' => $row['id'], 'icon' => 'ri-folder-image-line', 'color' => '#10b981'];
            }
        } catch (\Exception $e) { /* silently fail */ }

        $this->render('admin/multimedia/index', [
            'files' => $files,
            'msg' => $msg,
            'user_role' => $user_role,
            'user_name' => htmlspecialchars($_SESSION['user_name']),
            'usage_map' => $usage_map
        ], 'admin');
    }

    public function action() {
        require_permission('manage_media');
        
        $user_role = $_SESSION['user_role'];
        if ($user_role === 'gerente') {
            header("Location: /piura_noticias_php/admin/reportes");
            exit;
        }

        $upload_dir = $this->project_root . 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        // Subida múltiple
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_upload']) && !isset($_POST['bulk_delete'])) {
            require_once __DIR__ . '/../Services/MediaUploaderService.php';
            
            $uploader = new \App\Services\MediaUploaderService($upload_dir);
            $result = $uploader->handleMultipleUpload($_FILES['file_upload']);
            
            if ($result['success'] > 0) {
                $stmt_log = $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)");
                $stmt_log->execute([$_SESSION['user_id'], 'Creación', "Subió " . $result['success'] . " archvios multimedia seguros mediante API OOP"]);
            }
            
            $msg = $result['msg'];
            header('Location: /piura_noticias_php/admin/multimedia?msg=' . urlencode($msg));
            exit;
        }

        // Borrado múltiple
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete']) && $user_role === 'admin') {

            $files_to_delete = $_POST['selected_files'] ?? [];
            if (!empty($files_to_delete) && is_array($files_to_delete)) {
                $success = 0;
                $failed = 0;
                $trash_dir = $upload_dir . 'papelera/';
                if (!is_dir($trash_dir)) mkdir($trash_dir, 0755, true);

                foreach ($files_to_delete as $file) {
                    $rel_path = basename($file);
                    $path = rtrim($upload_dir, '/') . '/' . ltrim($rel_path, '/');
                    if (file_exists($path) && is_file($path)) {
                        $file_name = basename($path);
                        $lock_path = $path . '.lock';
                        $fp = fopen($lock_path, 'w+');
                        if ($fp && flock($fp, LOCK_EX)) {
                            if (rename($path, $trash_dir . $file_name)) {
                                touch($trash_dir . $file_name);
                                $success++;
                            } else {
                                $failed++;
                            }
                            flock($fp, LOCK_UN);
                        } else {
                            $failed++;
                        }
                        if ($fp) fclose($fp);
                        @unlink($lock_path);
                    } else {
                        $failed++;
                    }
                }
                
                if ($success > 0) {
                    $stmt_log = $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)");
                    $stmt_log->execute([$_SESSION['user_id'], 'Papelera', "Movió $success archivo(s) multimedia a la papelera simultáneamente en lote."]);
                    $msg = "$success archivo(s) movidos a la papelera." . ($failed > 0 ? " ($failed fallaron)" : "");
                    header('Location: /piura_noticias_php/admin/multimedia?msg=' . urlencode($msg));
                    exit;
                } else {
                    $redirect_msg = 'ok_1';
                }
            } else {
                $redirect_msg = 'ok_2';
            }
            header('Location: /piura_noticias_php/admin/multimedia?msg=' . urlencode($redirect_msg ?? 'ok_1'));
            exit;
        }

        // Borrado individual
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action_type']) && $_GET['action_type'] === 'delete' && $user_role === 'admin') {

            $rel_path = basename($_GET['file']);
            $path = rtrim($upload_dir, '/') . '/' . ltrim($rel_path, '/');
            
            if (file_exists($path) && is_file($path)) {
                $trash_dir = $upload_dir . 'papelera/';
                if (!is_dir($trash_dir)) mkdir($trash_dir, 0755, true);
                
                $file_name = basename($path);
                
                if (rename($path, $trash_dir . $file_name)) {
                    touch($trash_dir . $file_name);
                    $stmt_log = $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)");
                    $stmt_log->execute([$_SESSION['user_id'], 'Papelera', "Envió archivo multimedia a la papelera: $file_name"]);
                    
                    header("Location: /piura_noticias_php/admin/multimedia?msg=eliminado");
                    exit;
                } else {
                    $msg = "Error: No se pudo enviar el archivo a la papelera.";
                }
            } else {
                $msg = "Error: El archivo no existe o no se puede eliminar.";
            }
            header("Location: /piura_noticias_php/admin/multimedia?msg=" . urlencode($msg));
            exit;
        }
        
        // Redirección por defecto si no hay acción válida
        header('Location: /piura_noticias_php/admin/multimedia');
        exit;
    }
}
