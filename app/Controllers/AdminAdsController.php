<?php
namespace App\Controllers;

use Core\Controller;
use Config\Database;

class AdminAdsController extends Controller {
    
    private $pdo;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $this->pdo = Database::getInstance();
    }

    public function index() {
        require_permission('manage_ads');

        $msg = $_GET['msg'] ?? '';
        $msg_map = [
            'ok_1' => 'Anuncio creado exitosamente.',
            'ok_2' => 'Anuncio actualizado.',
            'eliminado' => 'Anuncio eliminado correctamente.'
        ];
        if (array_key_exists($msg, $msg_map)) {
            $msg = $msg_map[$msg];
        } else {
            $msg = htmlspecialchars($msg);
        }

        // Obtener Ads
        $ads = $this->pdo->query("SELECT * FROM publicidad WHERE deleted_at IS NULL ORDER BY fecha_creacion DESC")->fetchAll();

        $this->render('admin/publicidad/index', [
            'ads' => $ads,
            'msg' => $msg
        ], 'admin');
    }

    public function action() {
        require_permission('manage_ads');

        // CRUD Actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $action = $_POST['action'] ?? '';
            $redirect_msg = '';
            
            if ($action === 'create' || $action === 'update') {
                $nombre = $_POST['nombre'] ?? '';
                $ubicacion = $_POST['ubicacion'] ?? '';
                $tipo = $_POST['tipo'] ?? '';
                $enlace_url = $_POST['enlace_url'] ?? '';
                $codigo_script = $_POST['codigo_script'] ?? '';
                $estado = $_POST['estado'] ?? 'inactivo';
                
                $imagen_url = $_POST['imagen_url_actual'] ?? '';
                
                // Subida de imagen
                if ($tipo === 'imagen' && isset($_FILES['banner_upload']) && $_FILES['banner_upload']['error'] === UPLOAD_ERR_OK) {
                    
                    
                    $tmp = $_FILES['banner_upload']['tmp_name'];
                    $name = basename($_FILES['banner_upload']['name']);
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
                        $firewall = media_firewall_check($tmp, $name);
                        if (!$firewall['ok']) {
                            die("Error de seguridad en la imagen publicitaria: " . ($firewall['error'] ?? 'Archivo rechazado'));
                        }
                        
                        $targetPathWebp = 'img/promo_' . time() . '_' . rand(100, 999) . '.webp';
                        $image = false;
                        if ($ext === 'jpg' || $ext === 'jpeg') {
                            $image = @imagecreatefromjpeg($tmp);
                        } else if ($ext === 'png') {
                            $image = @imagecreatefrompng($tmp);
                            if ($image) {
                                imagepalettetotruecolor($image);
                                imagealphablending($image, true);
                                imagesavealpha($image, true);
                            }
                        }
                        if ($image && function_exists('imagewebp')) {
                            imagewebp($image, $targetPathWebp, 80);
                            imagedestroy($image);
                            $imagen_url = $targetPathWebp;
                        } else {
                            $target = 'img/promo_' . time() . '_' . rand(100, 999) . '.' . $ext;
                            if (@move_uploaded_file($tmp, $target)) {
                                $imagen_url = $target;
                            }
                        }
                    }
                }

                if ($action === 'create') {
                    $stmt = $this->pdo->prepare("INSERT INTO publicidad (nombre, ubicacion, tipo, imagen_url, enlace_url, codigo_script, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nombre, $ubicacion, $tipo, $imagen_url, $enlace_url, $codigo_script, $estado]);
                    $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], 'Creación', 'Creó un nuevo anuncio publicitario: ' . $nombre]);
                    $redirect_msg = 'ok_1';
                } else {
                    $id = (int)$_POST['id'];
                    $stmt_old = $this->pdo->prepare("SELECT * FROM publicidad WHERE id = ?");
                    $stmt_old->execute([$id]);
                    $old = $stmt_old->fetch();
                    $cambios = [];
                    if ($old && $old['nombre'] !== $nombre) $cambios[] = 'nombre';
                    if ($old && $old['ubicacion'] !== $ubicacion) $cambios[] = 'ubicación';
                    if ($old && $old['tipo'] !== $tipo) $cambios[] = 'tipo';
                    if ($old && $old['enlace_url'] !== $enlace_url) $cambios[] = 'enlace';
                    if ($old && $old['estado'] !== $estado) $cambios[] = 'estado';
                    if ($imagen_url !== '' && $old['imagen_url'] !== $imagen_url) $cambios[] = 'imagen/banner';
                    if ($codigo_script !== '' && $old['codigo_script'] !== $codigo_script) $cambios[] = 'script HTML';
                    
                    $stmt = $this->pdo->prepare("UPDATE publicidad SET nombre=?, ubicacion=?, tipo=?, imagen_url=?, enlace_url=?, codigo_script=?, estado=? WHERE id=?");
                    $stmt->execute([$nombre, $ubicacion, $tipo, $imagen_url, $enlace_url, $codigo_script, $estado, $id]);
                    
                    $detalles_msg = 'Actualizó el anuncio ID #' . $id . ' (' . $nombre . ')';
                    if (!empty($cambios)) {
                        $detalles_msg .= '. Modificó: ' . implode(', ', $cambios);
                    }
                    $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], 'Actualización', $detalles_msg]);
                    $redirect_msg = 'ok_2';
                }
            }
            
            
            build_global_cache($this->pdo);
            header('Location: " . APP_BASE . "admin/publicidad?msg=' . urlencode($redirect_msg));
            exit;
        }

        // Delete Action
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action_type']) && $_GET['action_type'] === 'delete') {

            $id = (int)$_GET['id'];
            
            $stmt = $this->pdo->prepare("SELECT imagen_url FROM publicidad WHERE id = ? AND tipo = 'imagen'");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row && !empty($row['imagen_url']) && file_exists($row['imagen_url'])) { @unlink($row['imagen_url']); }
            
            $stmt_del = $this->pdo->prepare("SELECT nombre FROM publicidad WHERE id = ?");
            $stmt_del->execute([$id]);
            $ad_name = $stmt_del->fetchColumn() ?: 'Desconocido';
            $this->pdo->prepare("UPDATE publicidad SET deleted_at = NOW() WHERE id = ?")->execute([$id]);
            $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], 'Eliminación', 'Borró definitivamente el anuncio ID #' . $id . ' (' . $ad_name . ')']);
            
            
            build_global_cache($this->pdo);
            
            header("Location: " . APP_BASE . "admin/publicidad?msg=eliminado");
            exit;
        }

        header('Location: " . APP_BASE . "admin/publicidad');
        exit;
    }
}
