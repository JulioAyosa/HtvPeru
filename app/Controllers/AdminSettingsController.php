<?php
namespace App\Controllers;

use Core\Controller;
use Config\Database;

class AdminSettingsController extends Controller {
    
    private $pdo;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $this->pdo = Database::getInstance();
    }

    public function index() {
        require_permission('manage_settings');

        $msg = '';
        if (isset($_GET['exito']) && $_GET['exito'] == '1') {
            $msg = "Configuraciones guardadas exitosamente.";
        }

        $configs = [];
        $stmt = $this->pdo->query("SELECT clave, valor FROM configuracion");
        while ($row = $stmt->fetch()) {
            $configs[$row['clave']] = $row['valor'];
        }
        
        $categorias_select = $this->pdo->query("SELECT nombre, slug FROM categorias WHERE estado='activo' ORDER BY orden ASC")->fetchAll();

        $this->render('admin/configuracion/index', [
            'msg' => $msg,
            'configs' => $configs,
            'categorias_select' => $categorias_select
        ], 'admin');
    }

    public function action() {
        require_permission('manage_settings');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_config') {

            $uploadDir = 'img/';
            
            $configs_old = [];
            $stmt_old = $this->pdo->query("SELECT clave, valor FROM configuracion");
            while ($row = $stmt_old->fetch()) {
                $configs_old[$row['clave']] = $row['valor'];
            }
            $cambios = [];

            $text_keys = [
                'site_title', 'site_slogan', 'color_primario', 'color_secundario', 
                'google_analytics_id', 'facebook_pixel_id', 'contact_email', 'contact_phone', 
                'social_facebook', 'social_twitter', 'social_instagram', 'social_youtube', 'social_whatsapp',
                'social_tiktok', 'social_twitch', 'social_kick', 'social_threads',
                'social_telegram', 'social_discord', 'social_pinterest', 'social_spotify', 'social_linkedin',
                'tv_envivo_url', 'tv_envivo_estado', 'cat_urgente', 'cat_carrusel', 'cat_distrito',
                'footer_text', 'watermark_estado',
                'script_header', 'script_footer', 'seo_og_desc',
                'alert_top_estado', 'alert_top_texto', 'alert_top_url', 'cookie_banner_estado',
                'ui_mostrar_urgente', 'ui_mostrar_carrusel', 'ui_mostrar_stories', 'ui_mostrar_policial',
                'modo_mantenimiento', 'theme_font_family', 'theme_custom_css', 'header_height',
                'header_logo_scale', 'header_search_width', 'header_actions_gap',
                'social_login_estado', 'google_client_id', 'google_client_secret', 'facebook_app_id', 'facebook_app_secret'
            ];
            
            $stmt_update = $this->pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = ? AND tipo = 'texto'");
            $stmt_insert = $this->pdo->prepare("INSERT INTO configuracion (clave, valor, tipo) VALUES (?, ?, 'texto')");
            
            require_once __DIR__ . '/../../script_validator.php';

            foreach ($text_keys as $key) {
                if (isset($_POST[$key])) {
                    if (in_array($key, ['script_header', 'script_footer']) && !validate_injected_script($_POST[$key], $key, $this->pdo, $_SESSION['user_id'])) { continue; }
                    if (!isset($configs_old[$key])) {
                        $cambios[] = $key;
                        $stmt_insert->execute([$key, $_POST[$key]]);
                    } elseif ($configs_old[$key] !== $_POST[$key]) {
                        $cambios[] = $key;
                        $stmt_update->execute([$_POST[$key], $key]);
                    }
                }
            }
            
            $file_keys = [
                'logo_url' => 'logo_upload', 
                'favicon_url' => 'favicon_upload', 
                'watermark_url' => 'watermark_upload',
                'seo_og_image' => 'seo_og_image_upload',
                'header_bg_url' => 'header_bg_upload',
                'privacy_policy_url' => 'privacy_policy_upload'
            ];
            $stmt_file_update = $this->pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = ? AND tipo = 'archivo'");
            $stmt_file_insert = $this->pdo->prepare("INSERT INTO configuracion (clave, valor, tipo) VALUES (?, ?, 'archivo')");
            
            foreach ($file_keys as $db_key => $input_name) {
                if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {
                    require_once __DIR__ . '/../../media_firewall.php';
                    $tmp = $_FILES[$input_name]['tmp_name'];
                    $name = basename($_FILES[$input_name]['name']);
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, ['png', 'jpg', 'jpeg', 'svg', 'ico', 'webp', 'pdf'])) {
                        if ($ext !== 'pdf' && $ext !== 'svg' && $ext !== 'ico') {
                            $firewall = media_firewall_check($tmp, $name);
                            if (!$firewall['ok']) {
                                die("Error de seguridad en el archivo de configuración: " . ($firewall['error'] ?? 'Archivo rechazado'));
                            }
                        }
                        
                        $unique_name = $db_key . '_' . time() . '.' . $ext;
                        $relative_path = $uploadDir . $unique_name;
                        $target = __DIR__ . '/../../' . $relative_path;
                        
                        if (move_uploaded_file($tmp, $target)) {
                            $cambios[] = $db_key;
                            if (!isset($configs_old[$db_key])) {
                                $stmt_file_insert->execute([$db_key, $relative_path]);
                            } else {
                                $stmt_file_update->execute([$relative_path, $db_key]);
                            }
                        }
                    }
                }
            }
            
            if (!empty($cambios)) {
                $detalle_cambios = "Se modificaron " . count($cambios) . " ajustes: " . implode(', ', $cambios);
                if(strlen($detalle_cambios) > 250) {
                    $detalle_cambios = substr($detalle_cambios, 0, 247) . '...';
                }
                $log_stmt = $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)");
                $log_stmt->execute([$_SESSION['user_id'], 'Actualización', $detalle_cambios]);
            } else {
                $log_stmt = $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)");
                $log_stmt->execute([$_SESSION['user_id'], 'Actualización', 'Ingresó y guardó panel de ajustes sin modificar valores']);
            }
            
            require_once __DIR__ . '/../Helpers/view_helper.php';
            build_global_cache($this->pdo);
            header("Location: /piura_noticias_php/admin/configuracion?exito=1");
            exit;
        }

        header('Location: /piura_noticias_php/admin/configuracion');
        exit;
    }
}
