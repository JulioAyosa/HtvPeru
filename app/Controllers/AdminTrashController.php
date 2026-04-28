<?php
namespace App\Controllers;

use Core\Controller;
use Config\Database;

class AdminTrashController extends Controller {
    
    private $pdo;
    private $purge_days = 15;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $this->pdo = Database::getInstance();
    }

    private function logTrashActivity($uid, $action, $detail) {
        $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$uid, $action, $detail]);
    }

    private function runAutoPurge() {
        $purge_lock = __DIR__ . '/../../uploads/papelera/.purge_lock';
        $should_purge = !file_exists($purge_lock) || (time() - filemtime($purge_lock)) > 3600;

        if ($should_purge) {
            try {
                $this->pdo->query("DELETE FROM noticias WHERE deleted_at IS NOT NULL AND deleted_at < NOW() - INTERVAL {$this->purge_days} DAY");
                $this->pdo->query("DELETE FROM usuarios WHERE deleted_at IS NOT NULL AND deleted_at < NOW() - INTERVAL {$this->purge_days} DAY");
                $this->pdo->query("DELETE FROM comentarios WHERE deleted_at IS NOT NULL AND deleted_at < NOW() - INTERVAL {$this->purge_days} DAY");
                $this->pdo->query("DELETE FROM encuestas WHERE deleted_at IS NOT NULL AND deleted_at < NOW() - INTERVAL {$this->purge_days} DAY");
                $this->pdo->query("DELETE FROM categorias WHERE deleted_at IS NOT NULL AND deleted_at < NOW() - INTERVAL {$this->purge_days} DAY");
                $this->pdo->query("DELETE FROM publicidad WHERE deleted_at IS NOT NULL AND deleted_at < NOW() - INTERVAL {$this->purge_days} DAY");
                $this->pdo->query("DELETE FROM paginas WHERE deleted_at IS NOT NULL AND deleted_at < NOW() - INTERVAL {$this->purge_days} DAY");
            } catch (\Exception $e) { /* Silenciar errores */ }

            $trash_dir = 'uploads/papelera/';
            if (is_dir($trash_dir)) {
                $now = time();
                $files = glob($trash_dir . '*.*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        if ($now - filemtime($file) >= $this->purge_days * 24 * 60 * 60) {
                            @unlink($file);
                        }
                    }
                }
            }
            if (!is_dir(dirname($purge_lock))) @mkdir(dirname($purge_lock), 0755, true);
            @file_put_contents($purge_lock, date('Y-m-d H:i:s'));
        }
    }

    public function index() {
        require_permission('manage_settings');
        
        $this->runAutoPurge();

        $msg = $_GET['msg'] ?? '';

        $noticias = $this->pdo->query("SELECT id, titulo, fecha_publicacion, deleted_at FROM noticias WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll();
        $usuarios = $this->pdo->query("SELECT id, nombre_completo, email, deleted_at FROM usuarios WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll();
        $comentarios = $this->pdo->query("SELECT id, comentario as titulo, fecha as deleted_at FROM comentarios WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll();
        $encuestas = $this->pdo->query("SELECT id, pregunta as titulo, deleted_at FROM encuestas WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll();
        $categorias = $this->pdo->query("SELECT id, nombre as titulo, deleted_at FROM categorias WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll();
        $publicidad = $this->pdo->query("SELECT id, nombre as titulo, deleted_at FROM publicidad WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll();
        $paginas = $this->pdo->query("SELECT id, titulo, deleted_at FROM paginas WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll();

        $trash_dir = 'uploads/papelera/';
        $media_files = [];
        if (is_dir($trash_dir)) {
            $all_files = glob($trash_dir . '*.*');
            foreach ($all_files as $f) {
                if (is_file($f)) {
                    $media_files[] = [
                        'name' => basename($f),
                        'path' => $f,
                        'time' => filemtime($f)
                    ];
                }
            }
            usort($media_files, function($a, $b) { return $b['time'] - $a['time']; });
        }

        $this->render('admin/papelera/index', [
            'msg' => htmlspecialchars($msg),
            'noticias' => $noticias,
            'usuarios' => $usuarios,
            'comentarios' => $comentarios,
            'encuestas' => $encuestas,
            'categorias' => $categorias,
            'publicidad' => $publicidad,
            'paginas' => $paginas,
            'media_files' => $media_files,
            'purge_days' => $this->purge_days
        ], 'admin');
    }

    public function action() {
        require_permission('manage_settings');

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action_type']) && isset($_GET['type']) && isset($_GET['id'])) {

            $action = $_GET['action_type']; 
            $type = $_GET['type']; 
            $id = (int)$_GET['id'];
            $user_id = $_SESSION['user_id'];
            $msg = '';
            
            $typesMap = [
                'user' => ['table' => 'usuarios', 'name' => 'Usuario'],
                'comentario' => ['table' => 'comentarios', 'name' => 'Comentario'],
                'encuesta' => ['table' => 'encuestas', 'name' => 'Encuesta'],
                'categoria' => ['table' => 'categorias', 'name' => 'Categoría'],
                'publicidad' => ['table' => 'publicidad', 'name' => 'Publicidad'],
                'pagina' => ['table' => 'paginas', 'name' => 'Página Estática']
            ];

            if ($type === 'news') {
                if ($action === 'restore') {
                    $this->pdo->prepare("UPDATE noticias SET estado_publicacion = 'borrador', deleted_at = NULL WHERE id = ?")->execute([$id]);
                    $this->logTrashActivity($user_id, 'Actualización', "Restauró la noticia ID #$id desde la papelera");
                    $msg = "Noticia restaurada en Borradores.";
                } elseif ($action === 'delete') {
                    $stmt_fetch = $this->pdo->prepare("SELECT imagen_url, video_poster_url FROM noticias WHERE id = ?");
                    $stmt_fetch->execute([$id]);
                    $row_hd = $stmt_fetch->fetch();
                    if ($row_hd) {
                        if (!empty($row_hd['imagen_url']) && file_exists($row_hd['imagen_url'])) @unlink($row_hd['imagen_url']);
                        if (!empty($row_hd['video_poster_url']) && file_exists($row_hd['video_poster_url'])) @unlink($row_hd['video_poster_url']);
                    }
                    $this->pdo->prepare("DELETE FROM noticias WHERE id = ?")->execute([$id]);
                    $this->logTrashActivity($user_id, 'Eliminación', "Borró físicamente la noticia ID #$id");
                    $msg = "Noticia eliminada definitivamente.";
                }
            } elseif (isset($typesMap[$type])) {
                $tbl = $typesMap[$type]['table'];
                $nme = $typesMap[$type]['name'];
                if ($action === 'restore') {
                    $this->pdo->prepare("UPDATE $tbl SET deleted_at = NULL WHERE id = ?")->execute([$id]);
                    $this->logTrashActivity($user_id, 'Actualización', "Restauró $nme ID #$id desde la papelera");
                    $msg = "$nme restaurado exitosamente.";
                } elseif ($action === 'delete') {
                    if ($type === 'publicidad') {
                        $stmt_fetch = $this->pdo->prepare("SELECT imagen_url FROM publicidad WHERE id = ?");
                        $stmt_fetch->execute([$id]);
                        $row_hd = $stmt_fetch->fetch();
                        if ($row_hd && !empty($row_hd['imagen_url']) && file_exists($row_hd['imagen_url'])) @unlink($row_hd['imagen_url']);
                    }
                    $this->pdo->prepare("DELETE FROM $tbl WHERE id = ?")->execute([$id]);
                    $this->logTrashActivity($user_id, 'Eliminación', "Borró físicamente $nme ID #$id");
                    $msg = "$nme eliminado definitivamente.";
                }
            } elseif ($type === 'media') {
                $file = urldecode($_GET['id']);
                $trash_dir = 'uploads/papelera/';
                $path = $trash_dir . basename($file);
                if ($action === 'restore' && file_exists($path)) {
                    rename($path, 'uploads/' . basename($file));
                    $this->logTrashActivity($user_id, 'Actualización', 'Restauró multimedia: ' . basename($file));
                    $msg = "Multimedia restaurado.";
                } elseif ($action === 'delete' && file_exists($path)) {
                    unlink($path);
                    $this->logTrashActivity($user_id, 'Eliminación', 'Borró multimedia: ' . basename($file));
                    $msg = "Multimedia eliminado definitivamente.";
                }
            }
            header("Location: /piura_noticias_php/admin/papelera?msg=" . urlencode($msg));
            exit;
        }

        header('Location: /piura_noticias_php/admin/papelera');
        exit;
    }
}
