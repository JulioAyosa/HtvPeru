<?php
namespace App\Controllers;

use Config\Database;
use App\Services\CacheService;

class AdminDashboardController {
    
    public function __construct() {
        require_once __DIR__ . '/../../conexion.php';
        require_once __DIR__ . '/../../watermark.php';
        require_once __DIR__ . '/../../media_firewall.php';
        
        if (isset($_GET['logout'])) {
            session_destroy();
            header("Location: /piura_noticias_php/login.php");
            exit;
        }
        
        
    }

    private function logActividad($pdo, $user_id, $accion, $detalles = '') {
        $stmt = $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $accion, $detalles]);
    }

    private function createSlug($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text) ?: 'noticia-' . time();
    }

    public function index() {
        global $pdo;
        
        $msg = isset($_GET['msg']) ? htmlspecialchars(urldecode($_GET['msg'])) : '';
        $user_role = $_SESSION['user_role'] ?? 'editor';

        $edit_data = null;
        if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
            $stmt_e = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
            $stmt_e->execute([(int)$_GET['id']]);
            $edit_data = $stmt_e->fetch();
            if ($edit_data && $_SESSION['user_role'] === 'editor' && $edit_data['autor_id'] != $_SESSION['user_id']) {
                die("No tienes permisos para ver y editar esta noticia (Seguridad Cross-Tenant).");
            }
        }

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $query = "SELECT n.id, n.titulo, n.categoria, n.fecha_publicacion, n.es_destacada, n.estado_publicacion, n.fecha_programada, n.vistas, u.nombre_completo AS autor 
                  FROM noticias n JOIN usuarios u ON n.autor_id = u.id WHERE n.deleted_at IS NULL ORDER BY n.fecha_publicacion DESC LIMIT $limit OFFSET $offset";
        $noticias = $pdo->query($query)->fetchAll();

        $total_noticias = $pdo->query("SELECT COUNT(*) FROM noticias WHERE deleted_at IS NULL")->fetchColumn();
        $total_pages = ceil($total_noticias / $limit);

        // Notificaciones
        $pending_comments = $pdo->query("SELECT COUNT(*) FROM comentarios WHERE estado = 'pendiente'")->fetchColumn();
        $scheduled_news = $pdo->query("SELECT COUNT(*) FROM noticias WHERE estado_publicacion = 'programado' AND fecha_programada >= NOW() AND deleted_at IS NULL")->fetchColumn();
        $new_users = 0;
        try {
            $new_users = $pdo->query("SELECT COUNT(*) FROM usuarios_publicos WHERE DATE(fecha_registro) = CURDATE()")->fetchColumn();
        } catch(\Exception $e) {}
        
        $notifications = [
            'comments' => $pending_comments,
            'scheduled' => $scheduled_news,
            'users' => $new_users,
            'total' => $pending_comments + $scheduled_news + $new_users
        ];

        $stats_row = $pdo->query("
            SELECT 
                SUM(CASE WHEN estado_publicacion != 'papelera' THEN 1 ELSE 0 END) AS not_total,
                SUM(CASE WHEN estado_publicacion = 'publicado' THEN 1 ELSE 0 END) AS pub,
                SUM(CASE WHEN estado_publicacion = 'borrador' THEN 1 ELSE 0 END) AS bor,
                SUM(CASE WHEN estado_publicacion = 'programado' AND deleted_at IS NULL THEN 1 ELSE 0 END) AS prg,
                SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) AS pap
            FROM noticias
        ")->fetch();
        $stats = [
            'not' => (int)($stats_row['not_total'] ?? 0),
            'pub' => (int)($stats_row['pub'] ?? 0),
            'bor' => (int)($stats_row['bor'] ?? 0),
            'prg' => (int)($stats_row['prg'] ?? 0),
            'pap' => (int)($stats_row['pap'] ?? 0),
            'usu' => (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE deleted_at IS NULL")->fetchColumn()
        ];

        $page_title = 'Gestión de Entradas';
        
        ob_start();
        require __DIR__ . '/../Views/admin/dashboard/index.php';
        $view_content = ob_get_clean();
        
        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/layouts/admin.php';
    }

    public function action() {
        global $pdo;

        $action = $_GET['action'] ?? ($_POST['action'] ?? '');
        $id = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
        
        if ($action == 'delete' && $id) {
            if ($_SESSION['user_role'] === 'admin') {
                $stmt_fetch = $pdo->prepare("SELECT titulo FROM noticias WHERE id = ?");
                $stmt_fetch->execute([$id]);
                $tit_del = $stmt_fetch->fetchColumn() ?: 'Desconocido';

                $stmt_del = $pdo->prepare("UPDATE noticias SET estado_publicacion = 'papelera', deleted_at = NOW() WHERE id = ?");
                if ($stmt_del->execute([$id])) {
                    $this->logActividad($pdo, $_SESSION['user_id'] ?? 0, 'Papelera', "Envió noticia ID #$id ('$tit_del') a la papelera");
                    header("Location: /piura_noticias_php/admin?msg=" . urlencode("Noticia '$tit_del' enviada a la papelera. Se eliminará en 15 días."));
                    exit;
                }
            } else {
                header("Location: /piura_noticias_php/admin?msg=" . urlencode("Error: No tienes permiso para eliminar noticias."));
                exit;
            }
        }

        if ($action == 'restore' && $id) {
            $stmt_fetch = $pdo->prepare("SELECT titulo FROM noticias WHERE id = ?");
            $stmt_fetch->execute([$id]);
            $tit_res = $stmt_fetch->fetchColumn() ?: 'Desconocido';

            $stmt_res = $pdo->prepare("UPDATE noticias SET estado_publicacion = 'borrador', deleted_at = NULL WHERE id = ?");
            if ($stmt_res->execute([$id])) {
                $this->logActividad($pdo, $_SESSION['user_id'] ?? 0, 'Restauración', "Restauró noticia ID #$id ('$tit_res')");
                header("Location: /piura_noticias_php/admin?msg=" . urlencode("Noticia '$tit_res' restaurada en Borradores."));
                exit;
            }
        }

        if ($action == 'hard_delete' && $id) {
            if ($_SESSION['user_role'] === 'admin') {
                $stmt_fetch = $pdo->prepare("SELECT titulo, imagen_url, video_poster_url FROM noticias WHERE id = ?");
                $stmt_fetch->execute([$id]);
                $row_hd = $stmt_fetch->fetch();

                if ($row_hd) {
                    if (!empty($row_hd['imagen_url']) && file_exists(__DIR__ . '/../../' . $row_hd['imagen_url'])) @unlink(__DIR__ . '/../../' . $row_hd['imagen_url']);
                    if (!empty($row_hd['video_poster_url']) && file_exists(__DIR__ . '/../../' . $row_hd['video_poster_url'])) @unlink(__DIR__ . '/../../' . $row_hd['video_poster_url']);
                    
                    $stmt_del_hd = $pdo->prepare("DELETE FROM noticias WHERE id = ?");
                    if ($stmt_del_hd->execute([$id])) {
                        $this->logActividad($pdo, $_SESSION['user_id'] ?? 0, 'Eliminación', "Eliminó permanentemente noticia ID #$id ('{$row_hd['titulo']}')");
                        header("Location: /piura_noticias_php/admin?msg=" . urlencode("La noticia y sus archivos fueron eliminados permanentemente."));
                        exit;
                    }
                }
            } else {
                header("Location: /piura_noticias_php/admin?msg=" . urlencode("Error: No tienes permisos administrativos para hacer limpiezas de la base de datos."));
                exit;
            }
        }

        if ($action == 'duplicate' && $id) {
            $stmt_dup = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
            $stmt_dup->execute([$id]);
            $original = $stmt_dup->fetch();
            
            if ($original) {
                $nuevo_titulo = $original['titulo'] . ' (Copia)';
                $nuevo_slug = $this->createSlug($nuevo_titulo) . '-' . time();
                $estado_dup = 'borrador';
                
                $stmt_insert = $pdo->prepare("INSERT INTO noticias (titulo, slug, extracto, contenido, categoria, distrito, imagen_url, autor_id, es_destacada, seo_titulo, seo_descripcion, tags, fuente_nombre, fuente_url, video_poster_url, estado_publicacion, fecha_programada) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_insert->execute([
                    $nuevo_titulo, $nuevo_slug, $original['extracto'], $original['contenido'], $original['categoria'], $original['distrito'] ?? null, $original['imagen_url'], 
                    $_SESSION['user_id'] ?? 1, 0, $original['seo_titulo'], $original['seo_descripcion'], $original['tags'], 
                    $original['fuente_nombre'], $original['fuente_url'], $original['video_poster_url'], $estado_dup, null
                ]);
                
                $nuevo_id = $pdo->lastInsertId();
                $this->logActividad($pdo, $_SESSION['user_id'] ?? 0, 'Duplicación', "Duplicó noticia ID #$id ('{$original['titulo']}') como ID #$nuevo_id");
                
                header("Location: /piura_noticias_php/admin?msg=" . urlencode("Noticia duplicada como Borrador."));
                exit;
            }
        }

        header("Location: /piura_noticias_php/admin");
        exit;
    }

    public function bulkAction() {
        global $pdo;

        $bulk_action = $_POST['bulk_action'] ?? '';
        $bulk_ids = $_POST['bulk_ids'] ?? [];
        $msg = "No se aplicó ninguna acción.";
        
        if (!empty($bulk_ids) && !empty($bulk_action)) {
            $ids_string = implode(',', array_map('intval', $bulk_ids));
            $count = count($bulk_ids);
            
            $allowed_bulk = ['publicado', 'borrador', 'papelera'];
            if (in_array($bulk_action, $allowed_bulk)) {
                if ($bulk_action === 'papelera') {
                    $pdo->query("UPDATE noticias SET estado_publicacion = 'papelera', deleted_at = NOW() WHERE id IN ($ids_string)");
                    $this->logActividad($pdo, $_SESSION['user_id'] ?? 0, 'Acción Lote', "Envió $count noticias a la papelera");
                    $msg = "Se enviaron $count noticias a la papelera.";
                } else {
                    $stmt_bulk = $pdo->prepare("UPDATE noticias SET estado_publicacion = ? WHERE id IN ($ids_string)");
                    $stmt_bulk->execute([$bulk_action]);
                    $this->logActividad($pdo, $_SESSION['user_id'] ?? 0, 'Acción Lote', "Cambió estado a '$bulk_action' para $count noticias");
                    $msg = "Se actualizaron $count noticias a '$bulk_action'.";
                }
            }
        }
        
        header("Location: /piura_noticias_php/admin?msg=" . urlencode($msg));
        exit;
    }

    public function store() {
        global $pdo;

        $cacheService = new \App\Services\CacheService();
        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['user_role'];

        $current_action = $_POST['action_type'] ?? 'create';
        $edit_item_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
        
        $titulo = $_POST['titulo'] ?? '';
        $categoria = $_POST['categoria'] ?? '';
        $distrito = !empty($_POST['distrito']) ? $_POST['distrito'] : null;
        $extracto = $_POST['extracto'] ?? '';
        $contenido = sanitize_html($_POST['contenido'] ?? '');
        $es_destacada = (isset($_POST['es_destacada']) && $user_role === 'admin') ? 1 : 0;
        
        $seo_titulo = !empty($_POST['seo_titulo']) ? $_POST['seo_titulo'] : null;
        $seo_descripcion = !empty($_POST['seo_descripcion']) ? $_POST['seo_descripcion'] : null;
        $tags = !empty($_POST['tags']) ? $_POST['tags'] : null;
        $fuente_nombre = !empty($_POST['fuente_nombre']) ? $_POST['fuente_nombre'] : null;
        $fuente_url = !empty($_POST['fuente_url']) ? $_POST['fuente_url'] : null;
        $estado_publicacion = $_POST['estado_publicacion'] ?? 'borrador';
        
        if ($user_role !== 'admin' && in_array($estado_publicacion, ['publicado', 'programado'])) {
            $estado_publicacion = 'revision';
        }
        $fecha_programada = !empty($_POST['fecha_programada']) ? str_replace('T', ' ', $_POST['fecha_programada']) . ':00' : null;
        
        $slug = $this->createSlug($titulo);
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM noticias WHERE slug = ? AND id != ?");
        $stmt_check->execute([$slug, $edit_item_id]);
        if ($stmt_check->fetchColumn() > 0) {
            $slug .= '-' . time();
        }
        
        $imagen_url = '';
        $video_poster_url = null;
        
        $cat_folder = !empty($categoria) ? preg_replace('/[^a-zA-Z0-9\-_]/', '', $categoria) : 'Sin_Categoria';
        $uploadDir = 'uploads/' . $cat_folder . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
        $fullUploadDir = __DIR__ . '/../../' . $uploadDir;
        if (!is_dir($fullUploadDir)) mkdir($fullUploadDir, 0755, true);
        
        if (isset($_FILES['media_upload']) && $_FILES['media_upload']['error'] === UPLOAD_ERR_OK && $_FILES['media_upload']['size'] <= 50 * 1024 * 1024) {
            $fileTemp = $_FILES['media_upload']['tmp_name'];
            $fileType = mime_content_type($fileTemp);
            $fileNameRaw = basename($_FILES['media_upload']['name']);
            
            if (strpos($fileType, 'image/') === 0) {
                $baseName = pathinfo($fileNameRaw, PATHINFO_FILENAME);
                $cleanName = time() . '_' . preg_replace("/[^a-zA-Z0-9\.\-_]/", "", $baseName);
                $targetPathWebp = $uploadDir . $cleanName . '.webp';
                
                $imgSize = @getimagesize($fileTemp);
                if ($imgSize && ($imgSize[0] > 3500 || $imgSize[1] > 3500)) {
                    die("Error de Seguridad: La imagen excede la resolución máxima permitida (3500px). Evitando sobrecarga de memoria del servidor.");
                }
                
                $image = null;
                if (function_exists('imagecreatefromjpeg')) {
                    if ($fileType == 'image/jpeg') { $image = @imagecreatefromjpeg($fileTemp); }
                    elseif ($fileType == 'image/png') { 
                        $image = @imagecreatefrompng($fileTemp); 
                        if ($image) { imagepalettetotruecolor($image); imagealphablending($image, true); imagesavealpha($image, true); }
                    }
                    elseif ($fileType == 'image/gif') { $image = @imagecreatefromgif($fileTemp); }
                    elseif ($fileType == 'image/webp') { $image = @imagecreatefromwebp($fileTemp); }
                }
                
                if ($image) {
                    $width = imagesx($image);
                    $height = imagesy($image);
                    $max_width = 1200;
                    
                    if ($width > $max_width) {
                        $new_width = $max_width;
                        $new_height = floor($height * ($max_width / $width));
                        $resized = imagecreatetruecolor($new_width, $new_height);
                        if ($fileType == 'image/png') {
                            imagealphablending($resized, false);
                            imagesavealpha($resized, true);
                            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                            imagefilledrectangle($resized, 0, 0, $new_width, $new_height, $transparent);
                        }
                        imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                        imagedestroy($image);
                        $image = $resized;
                    }
                    imagewebp($image, __DIR__ . '/../../' . $targetPathWebp, 80);
                    imagedestroy($image);
                    $imagen_url = $targetPathWebp;
                } else {
                    require_once __DIR__ . '/../../app/services/MediaUploaderService.php';
                    $uploader = new \App\Services\MediaUploaderService($fullUploadDir);
                    $upload_res = $uploader->handleSingleUpload(['name' => $fileNameRaw, 'type' => $fileType, 'tmp_name' => $fileTemp, 'error' => UPLOAD_ERR_OK, 'size' => $_FILES['media_upload']['size']]);
                    if ($upload_res['success']) {
                        $fallbackPath = ltrim($upload_res['url'], '/');
                        applyWatermark(__DIR__ . '/../../' . $fallbackPath, $pdo);
                        $imagen_url = $fallbackPath;
                    }
                }
            } else {
                require_once __DIR__ . '/../../app/services/MediaUploaderService.php';
                $uploader = new \App\Services\MediaUploaderService($fullUploadDir);
                $upload_res = $uploader->handleSingleUpload($_FILES['media_upload']);
                if ($upload_res['success']) {
                    $imagen_url = ltrim($upload_res['url'], '/');
                } else {
                    $msg = "Error al guardar el multimedia principal.";
                }
            }
        }
    
        if (isset($_FILES['poster_upload']) && $_FILES['poster_upload']['error'] === UPLOAD_ERR_OK) {
            $fileTempP = $_FILES['poster_upload']['tmp_name'];
            $fileNameRawP = basename($_FILES['poster_upload']['name']);
            $firewall_result = media_firewall_check($fileTempP, $fileNameRawP);
            if (!$firewall_result['ok']) {
                die("Error de Seguridad en archivo Poster: " . ($firewall_result['error'] ?? 'Archivo rechazado'));
            }
            $ext = strtolower(pathinfo($fileNameRawP, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                die("Extensión no permitida para la imagen de portada.");
            }
            require_once __DIR__ . '/../../app/services/MediaUploaderService.php';
            $uploader = new \App\Services\MediaUploaderService($fullUploadDir);
            $upload_res = $uploader->handleSingleUpload($_FILES['poster_upload']);
            if ($upload_res['success']) {
                $video_poster_url = ltrim($upload_res['url'], '/');
            }
        }
    
        if ($titulo && $categoria && $contenido) {
            try {
                $pdo->beginTransaction();
                if ($es_destacada) $pdo->query("UPDATE noticias SET es_destacada = 0");
    
                if ($current_action === 'create') {
                    if (!$imagen_url && $estado_publicacion === 'publicado') { 
                        $pdo->rollBack();
                        header("Location: /piura_noticias_php/admin?msg=" . urlencode("Error: Debes subir una imagen principal para nuevas noticias."));
                        exit;
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO noticias (titulo, slug, extracto, contenido, categoria, distrito, imagen_url, autor_id, es_destacada, seo_titulo, seo_descripcion, tags, fuente_nombre, fuente_url, video_poster_url, estado_publicacion, fecha_programada) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$titulo, $slug, $extracto, $contenido, $categoria, $distrito, $imagen_url, $user_id, $es_destacada, $seo_titulo, $seo_descripcion, $tags, $fuente_nombre, $fuente_url, $video_poster_url, $estado_publicacion, $fecha_programada]);
                        $nuevo_id = $pdo->lastInsertId();
                        $extra_log = ($estado_publicacion === 'programado' && $fecha_programada) ? " para el " . date('d/m/Y H:i', strtotime($fecha_programada)) : '';
                        $this->logActividad($pdo, $user_id, 'Creación', "Creó noticia ID #$nuevo_id - '$titulo' como $estado_publicacion" . $extra_log);
                        
                        $map_seccion = $categoria;
                        if(in_array($categoria, ['Nacional','Local (Regional)','Local','Policiales','Economía','Salud','Tendencias'])) $map_seccion = 'Actualidad';
                        elseif ($categoria == 'Política') $map_seccion = 'Politica';
                        elseif (!in_array($categoria, ['Deportes', 'Entretenimiento', 'Publicidad'])) $map_seccion = 'Actualidad';
                        
                        $estado_rebote = ($estado_publicacion === 'programado') ? '(programada)' : (($estado_publicacion === 'borrador') ? '(pendiente)' : '(completada)');
                        $completado_flag = ($estado_publicacion === 'publicado') ? 1 : 0;
                        $enlace_auto = "article.php?id=" . $nuevo_id;
                        
                        try {
                            $stmt_plan = $pdo->prepare("INSERT INTO registro_contenidos (fecha, hora, titular, enlace, fuente_url, usuario_id, seccion, plataforma, rebote, completado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt_plan->execute([date('Y-m-d'), date('H:i'), $titulo, $enlace_auto, $fuente_url, $user_id, $map_seccion, 'Web', $estado_rebote, $completado_flag]);
                        } catch (\Exception $e) {}
    
                        $pdo->commit();
                        $cacheService->clear('home');
                        header("Location: /piura_noticias_php/admin?msg=" . urlencode("Noticia guardada exitosamente y agregada al Planificador ($estado_publicacion)."));
                        exit;
                    }
                } elseif ($current_action === 'update' && $edit_item_id) {
                    $original_updated_at = $_POST['original_updated_at'] ?? '';
                    if (!empty($original_updated_at)) {
                        $stmt_check_lock = $pdo->prepare("SELECT updated_at FROM noticias WHERE id = ?");
                        $stmt_check_lock->execute([$edit_item_id]);
                        $db_updated_at = $stmt_check_lock->fetchColumn();
                        if ($db_updated_at && $db_updated_at !== $original_updated_at) {
                            $pdo->rollBack();
                            header("Location: /piura_noticias_php/admin?msg=" . urlencode("Conflicto: Esta noticia ha sido modificada por otro usuario recientemente."));
                            exit;
                        }
                    }
    
                    if ($_SESSION['user_role'] === 'editor') {
                        $stmt_owner = $pdo->prepare("SELECT autor_id FROM noticias WHERE id = ?");
                        $stmt_owner->execute([$edit_item_id]);
                        if ($stmt_owner->fetchColumn() != $_SESSION['user_id']) {
                            $pdo->rollBack();
                            die("No tienes permisos para modificar esta noticia (Seguridad Cross-Tenant).");
                        }
                    }
                    $sql = "UPDATE noticias SET titulo=?, slug=?, extracto=?, contenido=?, categoria=?, distrito=?, es_destacada=?, seo_titulo=?, seo_descripcion=?, tags=?, fuente_nombre=?, fuente_url=?, estado_publicacion=?, fecha_programada=?";
                    $params = [$titulo, $slug, $extracto, $contenido, $categoria, $distrito, $es_destacada, $seo_titulo, $seo_descripcion, $tags, $fuente_nombre, $fuente_url, $estado_publicacion, $fecha_programada];
                    if ($imagen_url) { $sql .= ", imagen_url=?"; $params[] = $imagen_url; }
                    if ($video_poster_url) { $sql .= ", video_poster_url=?"; $params[] = $video_poster_url; }
                    $sql .= " WHERE id=?";
                    $params[] = $edit_item_id;
                    
                    $stmt = $pdo->prepare($sql);
                    if (!$stmt->execute($params)) {
                        $pdo->rollBack();
                        header("Location: /piura_noticias_php/admin?msg=" . urlencode("Error al actualizar la base de datos."));
                        exit;
                    } else {
                        $extra_log = ($estado_publicacion === 'programado' && $fecha_programada) ? " para el " . date('d/m/Y H:i', strtotime($fecha_programada)) : '';
                        $this->logActividad($pdo, $user_id, 'Actualización', "Actualizó noticia ID #$edit_item_id ('$titulo') - Estado: $estado_publicacion" . $extra_log);
                        
                        $pdo->commit();
                        $cacheService->clear('home');
                        header("Location: /piura_noticias_php/admin?msg=" . urlencode("Noticia ID #$edit_item_id actualizada exitosamente a '$estado_publicacion'."));
                        exit;
                    }
                }
            } catch(\Exception $ex) {
                $pdo->rollBack();
                header("Location: /piura_noticias_php/admin?msg=" . urlencode("Error DB: " . $ex->getMessage()));
                exit;
            }
        } else {
            header("Location: /piura_noticias_php/admin?msg=" . urlencode("Error: Faltan campos obligatorios principales."));
            exit;
        }
    }
}
