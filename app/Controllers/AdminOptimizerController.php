<?php
namespace App\Controllers;

use Core\Controller;
use Config\Database;
use PDO;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class AdminOptimizerController extends Controller {
    
    private $pdo;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        ini_set('memory_limit', '1024M');
        $this->pdo = Database::getInstance();
    }

    private function formatBytes($bytes, $precision = 2) {  
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
        $bytes /= pow(1024, $pow); 
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    }

    public function index() {
        require_permission('system_tools');

        $msg = '';
        if (isset($_GET['msg'])) { 
            $msg = htmlspecialchars($_GET['msg']); 
        }
        $active_tab = isset($_GET['tab']) && $_GET['tab'] === 'ghosts' ? 'ghosts' : 'webp';

        $gd_installed = function_exists('imagecreatefromjpeg') && function_exists('imagewebp');
        $scan_dir = realpath(__DIR__ . '/../../');

        // Extraer DBMassive String para detectar Huérfanos
        $stmt_db = $this->pdo->query("SELECT imagen_url, video_poster_url, contenido FROM noticias");
        $noticias_data = $stmt_db->fetchAll(PDO::FETCH_ASSOC);
        $stmt_usu = $this->pdo->query("SELECT avatar_url FROM usuarios");
        $usu_data = $stmt_usu->fetchAll(PDO::FETCH_ASSOC);
        $stmt_cat = $this->pdo->query("SELECT imagen_fondo FROM categorias WHERE imagen_fondo IS NOT NULL");
        $cat_data = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
        $stmt_conf = $this->pdo->query("SELECT valor FROM configuracion WHERE clave IN ('logo_url','logo_footer','favicon_url')");
        $conf_data = $stmt_conf->fetchAll(PDO::FETCH_ASSOC);
        
        // Fix: Also check Static Pages and Ads for used media!
        $stmt_pag = $this->pdo->query("SELECT contenido FROM paginas");
        $pag_data = $stmt_pag->fetchAll(PDO::FETCH_ASSOC);
        $stmt_pub = $this->pdo->query("SELECT imagen_url FROM publicidad WHERE tipo = 'imagen'");
        $pub_data = $stmt_pub->fetchAll(PDO::FETCH_ASSOC);

        $massive_string = '';
        foreach($noticias_data as $row) {
            $massive_string .= ($row['imagen_url'] ?? '') . ' ' . ($row['video_poster_url'] ?? '') . ' ' . ($row['contenido'] ?? '') . ' ';
        }
        foreach($usu_data as $row) {
            $massive_string .= ($row['avatar_url'] ?? '') . ' ';
        }
        foreach($cat_data as $row) {
            $massive_string .= ($row['imagen_fondo'] ?? '') . ' ';
        }
        foreach($conf_data as $row) {
            $massive_string .= ($row['valor'] ?? '') . ' ';
        }
        foreach($pag_data as $row) {
            $massive_string .= ($row['contenido'] ?? '') . ' ';
        }
        foreach($pub_data as $row) {
            $massive_string .= ($row['imagen_url'] ?? '') . ' ';
        }

        $unoptimized = [];
        $orphaned = [];
        $total_wasted_bytes = 0;
        $total_current_bytes = 0;
        $total_orphan_bytes = 0;

        if (is_dir($scan_dir)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($scan_dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $f_path = str_replace('\\', '/', $file->getPathname());
                    
                    if (strpos($f_path, '/uploads/') === false) { continue; }
                    
                    if (strpos($f_path, '/papelera/') !== false || strpos($f_path, '/img/') !== false || strpos($f_path, '/css/') !== false || strpos($f_path, '/capturas/') !== false || strpos($f_path, '/backups_automaticos/') !== false) { continue; }

                    $ext = strtolower($file->getExtension());
                    $f = str_replace('\\', '/', $file->getPathname());
                    $normalized_dir = rtrim(str_replace('\\', '/', $scan_dir), '/') . '/';
                    $rel_dir = strpos($f, $normalized_dir) === 0 ? substr($f, strlen($normalized_dir)) : basename($f);
                    
                    if (in_array($ext, ['jpg','jpeg','png','webp','gif','mp4','webm','ogg'])) {
                        $filename = basename($f);
                        if (strpos($massive_string, $filename) === false && $filename !== '.gitkeep') {
                            $size = $file->getSize();
                            $total_orphan_bytes += $size;
                            $orphaned[] = [
                                'name' => $filename,
                                'rel_path' => $rel_dir,
                                'path' => APP_BASE . '/' . $rel_dir,
                                'ext' => $ext,
                                'size' => $size
                            ];
                        }
                    }

                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        $total_current_bytes += $file->getSize();
                        $savings = $file->getSize() * 0.60;
                        $total_wasted_bytes += $savings;
                        
                        $unoptimized[] = [
                            'name' => basename($f),
                            'rel_path' => $rel_dir,
                            'path' => APP_BASE . '/' . $rel_dir,
                            'ext' => $ext,
                            'size' => $file->getSize(),
                            'est_savings' => $savings
                        ];
                    }
                }
            }
            usort($unoptimized, function($a, $b) { return $b['est_savings'] - $a['est_savings']; });
            usort($orphaned, function($a, $b) { return $b['size'] - $a['size']; });
        }

        $this->render('admin/optimizador/index', [
            'msg' => $msg,
            'active_tab' => $active_tab,
            'gd_installed' => $gd_installed,
            'unoptimized' => $unoptimized,
            'orphaned' => $orphaned,
            'total_wasted_bytes' => $total_wasted_bytes,
            'total_current_bytes' => $total_current_bytes,
            'total_orphan_bytes' => $total_orphan_bytes,
            'controller' => $this
        ], 'admin');
    }

    public function action() {
        require_permission('system_tools');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $scan_dir = realpath(__DIR__ . '/../../');
            
            if (isset($_POST['delete_ghosts'])) {
                $ghosts = $_POST['ghost_files'] ?? [];
                if (!empty($ghosts) && is_array($ghosts)) {
                    $del_count = 0;
                    $del_bytes = 0;
                    foreach($ghosts as $g) {
                        $p = rtrim($scan_dir, '/\\') . '/' . ltrim(str_replace(['../', '..\\'], '', $g), '/');
                        if(file_exists($p) && is_file($p)) {
                            $del_bytes += filesize($p);
                            unlink($p);
                            $del_count++;
                        }
                    }
                    $mb = $this->formatBytes($del_bytes);
                    if($del_count > 0) {
                        $stmt_log = $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)");
                        $stmt_log->execute([$_SESSION['user_id'], 'Limpieza', "Fulminó $del_count archivos fantasma liberando $mb de espacio."]);
                        $msg = "Se eliminaron $del_count archivos huérfanos permanentemente. Espacio liberado: $mb.";
                    }
                    header("Location: " . APP_BASE . "admin/optimizador?tab=ghosts&msg=" . urlencode($msg ?? ''));
                    exit;
                }
            }

            if (isset($_POST['optimize_files'])) {
                $gd_installed = function_exists('imagecreatefromjpeg') && function_exists('imagewebp');
                if (!$gd_installed) {
                    $msg = "Error: Módulo GD inactivo. Activa 'extension=gd' en tu php.ini.";
                    header("Location: " . APP_BASE . "admin/optimizador?msg=" . urlencode($msg));
                    exit;
                }

                $files_to_optimize = $_POST['selected_files'] ?? [];
                $quality = isset($_POST['webp_quality']) ? (int)$_POST['webp_quality'] : 80;
                if($quality < 10 || $quality > 100) $quality = 80;

                if (!empty($files_to_optimize) && is_array($files_to_optimize)) {
                    $success = 0;
                    $failed = 0;
                    $saved_bytes = 0;
                    
                    foreach ($files_to_optimize as $file_rel) {
                        $rel_path = str_replace(['../', '..\\'], '', $file_rel);
                        $path = rtrim($scan_dir, '/\\') . '/' . ltrim($rel_path, '/');
                        
                        if (file_exists($path) && is_file($path)) {
                            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                                $original_size = filesize($path);
                                $new_rel_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $rel_path);
                                $new_path = rtrim($scan_dir, '/\\') . '/' . ltrim($new_rel_path, '/');
                                
                                $img = null;
                                if ($ext === 'jpg' || $ext === 'jpeg') {
                                    $img = @imagecreatefromjpeg($path);
                                } else if ($ext === 'png') {
                                    $img = @imagecreatefrompng($path);
                                    if ($img) {
                                        imagepalettetotruecolor($img);
                                        imagealphablending($img, true);
                                        imagesavealpha($img, true);
                                    }
                                }
                                
                                if ($img) {
                                    if (imagewebp($img, $new_path, $quality)) {
                                        imagedestroy($img);
                                        $new_size = filesize($new_path);
                                        unlink($path);
                                        $success++;
                                        $saved_bytes += ($original_size - $new_size);
                                        
                                        $old_db_path = ltrim($rel_path, '/');
                                        $new_db_path = ltrim($new_rel_path, '/');
                                        
                                        $stmt = $this->pdo->prepare("UPDATE noticias SET imagen_url = REPLACE(imagen_url, ?, ?), video_poster_url = REPLACE(video_poster_url, ?, ?), contenido = REPLACE(contenido, ?, ?)");
                                        $stmt->execute([$old_db_path, $new_db_path, $old_db_path, $new_db_path, $old_db_path, $new_db_path]);
                                        
                                        $stmt_usu = $this->pdo->prepare("UPDATE usuarios SET avatar_url = REPLACE(avatar_url, ?, ?)");
                                        $stmt_usu->execute([$old_db_path, $new_db_path]);
                                        
                                        $stmt_cat = $this->pdo->prepare("UPDATE categorias SET imagen_fondo = REPLACE(imagen_fondo, ?, ?)");
                                        $stmt_cat->execute([$old_db_path, $new_db_path]);
                                        
                                        $stmt_cfg = $this->pdo->prepare("UPDATE configuracion SET valor = REPLACE(valor, ?, ?) WHERE clave IN ('logo_url','logo_footer','favicon_url')");
                                        $stmt_cfg->execute([$old_db_path, $new_db_path]);
                                    } else {
                                        $failed++;
                                    }
                                } else {
                                    $failed++;
                                }
                            }
                        }
                    }
                    $saved_mb = round($saved_bytes / 1048576, 2);
                    if ($success > 0) {
                        $stmt_log = $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)");
                        $stmt_log->execute([$_SESSION['user_id'], 'Optimización', "Convirtió $success imágenes a WebP ($quality%). Ahorro: $saved_mb MB."]);
                        $msg = "$success archivos convertidos a WebP exitosamente al $quality%. Recuperaste {$saved_mb} MB." . ($failed > 0 ? " ($failed fallaron)" : "");
                        header("Location: " . APP_BASE . "admin/optimizador?msg=" . urlencode($msg));
                        exit;
                    } else {
                        $msg = "Error: No se lograron optimizar los archivos.";
                        header("Location: " . APP_BASE . "admin/optimizador?msg=" . urlencode($msg));
                        exit;
                    }
                } else {
                    $msg = "No seleccionaste ningún archivo para optimizar.";
                    header("Location: " . APP_BASE . "admin/optimizador?msg=" . urlencode($msg));
                    exit;
                }
            }
        }
        
        header("Location: " . APP_BASE . "admin/optimizador");
        exit;
    }

    public function publicFormatBytes($bytes, $precision = 2) {
        return $this->formatBytes($bytes, $precision);
    }
}
