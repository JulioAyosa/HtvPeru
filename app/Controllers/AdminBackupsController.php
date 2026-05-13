<?php
namespace App\Controllers;

use Core\Controller;
use Config\Database;
use PDO;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class AdminBackupsController extends Controller {
    
    private $pdo;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $this->pdo = Database::getInstance();
    }

    public function index() {
        require_permission('system_tools');

        $msg = '';
        $err = '';

        if (isset($_GET['msg'])) {
            if ($_GET['msg'] === 'import_ok') {
                $n = (int)($_GET['n'] ?? 0); $u = (int)($_GET['u'] ?? 0); $s = (int)($_GET['s'] ?? 0);
                $msg = "Proceso de Importación Completado.<br><strong>Nuevas Noticias:</strong> " . $n . "<br><strong>Reemplazadas:</strong> " . $u . "<br><strong>Omitidas:</strong> " . $s . ".";
            } elseif ($_GET['msg'] === 'trash_ok') {
                $msg = "El archivo de respaldo fue movido a la papelera. Será eliminado permanentemente después de 15 días.";
            }
        }
        if (isset($_GET['err'])) { $err = htmlspecialchars($_GET['err']); }
        
        $categorias_export = $this->pdo->query("SELECT nombre FROM categorias WHERE deleted_at IS NULL AND estado='activo' ORDER BY orden ASC")->fetchAll(PDO::FETCH_COLUMN);

        $this->render('admin/respaldos/index', [
            'msg' => $msg,
            'err' => $err,
            'categorias_export' => $categorias_export
        ], 'admin');
    }

    public function action() {
        require_permission('system_tools');

        if (isset($_GET['download_auto_backup']) && !empty($_GET['download_auto_backup'])) {
            $file_name = basename($_GET['download_auto_backup']); 
            $file_path = __DIR__ . '/../../backups/' . $file_name;
            if (file_exists($file_path) && strpos($file_name, '.zip') !== false) {
                ini_set('zlib.output_compression', 'Off');
                while (ob_get_level()) { ob_end_clean(); }
                clearstatcache();
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file_path));
                header('Connection: close');
                readfile($file_path);
                exit;
            } else {
                die("Archivo no encontrado o inválido.");
            }
        }

                if (isset($_GET['delete_auto_backup']) && !empty($_GET['delete_auto_backup'])) {
            $file_name = basename($_GET['delete_auto_backup']); 
            $file_path = __DIR__ . '/../../backups/' . $file_name;
            $papelera_dir = __DIR__ . '/../../backups/papelera/';
            if (!is_dir($papelera_dir)) mkdir($papelera_dir, 0755, true);
            
            if (file_exists($file_path) && strpos($file_name, '.zip') !== false) {
                rename($file_path, $papelera_dir . $file_name);
                $stmt_log = $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)");
                $stmt_log->execute([$_SESSION['user_id'], 'Backup Eliminado', "Movió el backup $file_name a la papelera (Retención 15 días)."]);
                header('Location: ' . APP_BASE . 'admin/respaldos?msg=trash_ok');
                exit;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

            if ($_POST['action'] === 'full_zip') {
                ob_start();
                try {
                    set_time_limit(0);
                    ini_set('memory_limit', '1024M');
                    
                    $inc_media = isset($_POST['inc_media']);
                    $inc_uploads = isset($_POST['inc_uploads']);

                    $timestamp = date('Ymd_His');
                    $zipFileName = 'HTVPERU_Backup_' . $timestamp . '.zip';
                    $tempDir = __DIR__ . '/../../backups/';
                    if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);
                    
                    $zipFilePath = $tempDir . $zipFileName;
                    $sqlFileName = 'database_backup.sql';
                    $sqlFilePath = $tempDir . $sqlFileName;
                    
                    $env_file = __DIR__ . '/../../.env';
                    $_env_vars = [];
                    if (file_exists($env_file)) {
                        $env_lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        foreach ($env_lines as $line) {
                            if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
                            list($k, $v) = explode('=', $line, 2);
                            $_env_vars[trim($k)] = trim($v);
                        }
                    }
                    $user = $_env_vars['DB_USER'] ?? 'root';
                    $pass = $_env_vars['DB_PASS'] ?? '';
                    $host = $_env_vars['DB_HOST'] ?? '127.0.0.1';
                    $db = $_env_vars['DB_NAME'] ?? 'piura_noticias_db';
                    
                    $mysqldump = 'c:\\xampp\\mysql\\bin\\mysqldump.exe';
                    if (!file_exists($mysqldump)) $mysqldump = 'mysqldump'; 
                    $mysql_cnf = $tempDir . '.my_backup.cnf';
                    file_put_contents($mysql_cnf, "[mysqldump]\nuser={$user}\npassword={$pass}\nhost={$host}\n");
                    $cmd = "\"$mysqldump\" --defaults-extra-file=" . escapeshellarg($mysql_cnf) . " \"$db\" > \"$sqlFilePath\"";
                    exec($cmd);
                    if(file_exists($mysql_cnf)) @unlink($mysql_cnf);
                    
                    $zip = new ZipArchive();
                    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                        if (file_exists($sqlFilePath)) $zip->addFile($sqlFilePath, $sqlFileName);
                        $rootPath = realpath(__DIR__ . '/../../');
                        
                        try {
                            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath, \FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY);
                            foreach ($files as $name => $f) {
                                if (!$f->isDir()) {
                                    $filePath = $f->getRealPath();
                                    $relativePath = substr($filePath, strlen($rootPath) + 1);
                                    $ext = strtolower($f->getExtension());
                                    if (in_array($ext, ['zip', 'rar', 'gz', 'tar', 'sql'])) continue;
                                    if (strpos(str_replace(DIRECTORY_SEPARATOR, '/', $relativePath), 'backups/') === 0) continue;
                                    
                                    $isCaptura = (strpos(str_replace(DIRECTORY_SEPARATOR, '/', $relativePath), 'capturas/') === 0);
                                    $isUpload = (strpos(str_replace(DIRECTORY_SEPARATOR, '/', $relativePath), 'public/uploads/') === 0);
                                    if (!$inc_media && $isCaptura) continue;
                                    if (!$inc_uploads && $isUpload) continue;
                                    
                                    @$zip->addFile($filePath, $relativePath);
                                }
                            }
                        } catch (\Exception $e) {
                            file_put_contents(__DIR__ . '/../../backups/error_iterator.log', $e->getMessage());
                        }
                        
                        $zip->close();
                        
                        $stmt_log = $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)");
                        $stmt_log->execute([$_SESSION['user_id'], 'Backup', "Generó un ZIP del sistema (Media: " . ($inc_media?'Sí':'No') . ", Uploads: " . ($inc_uploads?'Sí':'No') . ")."]);

                        ini_set('zlib.output_compression', 'Off');
                        while (ob_get_level()) { ob_end_clean(); }
                        clearstatcache();
                        header('Content-Type: application/zip');
                        header('Content-Disposition: attachment; filename="'.$zipFileName.'"');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($zipFilePath));
                        header('Connection: close');
                        
                        readfile($zipFilePath);

                        @unlink($zipFilePath);
                        @unlink($sqlFilePath);
                        exit;
                    } else {
                        ob_end_clean();
                        header('Location: ' . APP_BASE . 'admin/respaldos?err=' . urlencode("No se pudo crear el archivo ZIP. Permisos denegados en la carpeta."));
                        exit;
                    }
                } catch (\Throwable $ex) {
                    ob_end_clean();
                    file_put_contents(__DIR__ . '/../../backups/fatal_error.log', $ex->getMessage() . "\n" . $ex->getTraceAsString());
                    die("Ha ocurrido un error fatal al crear el backup: " . $ex->getMessage());
                }
            }
            
            if ($_POST['action'] === 'export') {
                $fecha_inicio = $_POST['fecha_inicio'] ?? '';
                $fecha_fin = $_POST['fecha_fin'] ?? '';
                $estado = $_POST['estado'] ?? 'todos';
                $categoria = $_POST['categoria'] ?? 'todas';

                $query = "SELECT * FROM noticias WHERE deleted_at IS NULL";
                $params = [];
                if ($fecha_inicio) { $query .= " AND DATE(fecha_publicacion) >= ?"; $params[] = $fecha_inicio; }
                if ($fecha_fin) { $query .= " AND DATE(fecha_publicacion) <= ?"; $params[] = $fecha_fin; }
                if ($estado !== 'todos') { $query .= " AND estado_publicacion = ?"; $params[] = $estado; }
                if ($categoria !== 'todas') { $query .= " AND categoria = ?"; $params[] = $categoria; }

                $stmt = $this->pdo->prepare($query);
                $stmt->execute($params);
                $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($noticias)) {
                    header('Location: ' . APP_BASE . 'admin/respaldos?err=' . urlencode("No se encontraron noticias con los filtros seleccionados para exportar."));
                    exit;
                } else {
                    $backup_data = [
                        'metadata' => [
                            'plataforma' => 'HTVPERU CMS',
                            'version_backup' => '1.0',
                            'fecha_generacion' => date('Y-m-d H:i:s'),
                            'total_registros' => count($noticias),
                            'filtros_usados' => ['desde' => $fecha_inicio, 'hasta' => $fecha_fin, 'estado' => $estado, 'categoria' => $categoria]
                        ],
                        'noticias' => $noticias
                    ];
                    $json = json_encode($backup_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $stmt_log = $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)");
                        $stmt_log->execute([$_SESSION['user_id'], 'Exportación', "Generó un archivo JSON exportando " . count($noticias) . " noticias."]);
                        header('Content-Type: application/json; charset=utf-8');
                        header('Content-Disposition: attachment; filename="backup_noticias_htvperu_' . date('Ymd_His') . '.json"');
                        header('Content-Length: ' . strlen($json));
                        echo $json;
                        exit;
                    } else {
                        header('Location: ' . APP_BASE . 'admin/respaldos?err=' . urlencode("Error del sistema al generar el archivo JSON."));
                        exit;
                    }
                }
            }

            if ($_POST['action'] === 'import') {
                $modo = $_POST['modo'] ?? 'omitir';
                if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['backup_file']['tmp_name'];
                    $json_content = file_get_contents($file_tmp);
                    $data = json_decode($json_content, true);

                    if ($data && isset($data['metadata']['plataforma']) && $data['metadata']['plataforma'] === 'HTVPERU CMS' && isset($data['noticias']) && is_array($data['noticias'])) {
                        $imported = 0; $skipped = 0; $updated = 0;
                        
                        foreach ($data['noticias'] as $n) {
                            $stmt_check = $this->pdo->prepare("SELECT id FROM noticias WHERE slug = ?");
                            $stmt_check->execute([$n['slug']]);
                            $exists_id = $stmt_check->fetchColumn();

                            $vistas_rest = isset($n['vistas']) ? $n['vistas'] : 0;
                            $safe_contenido = sanitize_html($n['contenido']);
                            $safe_titulo = strip_tags($n['titulo']);

                            if ($exists_id) {
                                if ($modo === 'omitir') {
                                    $skipped++;
                                } elseif ($modo === 'reemplazar') {
                                    $upd = $this->pdo->prepare("UPDATE noticias SET titulo=?, extracto=?, contenido=?, categoria=?, imagen_url=?, autor_id=?, es_destacada=?, seo_titulo=?, seo_descripcion=?, tags=?, fuente_nombre=?, fuente_url=?, video_poster_url=?, estado_publicacion=?, fecha_publicacion=?, fecha_programada=?, vistas=? WHERE id=?");
                                    $upd->execute([$safe_titulo, $n['extracto'], $safe_contenido, $n['categoria'], $n['imagen_url'], $_SESSION['user_id'], $n['es_destacada'], $n['seo_titulo'], $n['seo_descripcion'], $n['tags'], $n['fuente_nombre'], $n['fuente_url'], $n['video_poster_url'], $n['estado_publicacion'], $n['fecha_publicacion'], $n['fecha_programada'], $vistas_rest, $exists_id]);
                                    $updated++;
                                }
                            } else {
                                $ins = $this->pdo->prepare("INSERT INTO noticias (titulo, slug, extracto, contenido, categoria, imagen_url, autor_id, es_destacada, seo_titulo, seo_descripcion, tags, fuente_nombre, fuente_url, video_poster_url, estado_publicacion, fecha_publicacion, fecha_programada, vistas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                $ins->execute([$safe_titulo, $n['slug'], $n['extracto'], $safe_contenido, $n['categoria'], $n['imagen_url'], $_SESSION['user_id'], $n['es_destacada'], $n['seo_titulo'], $n['seo_descripcion'], $n['tags'], $n['fuente_nombre'], $n['fuente_url'], $n['video_poster_url'], $n['estado_publicacion'], $n['fecha_publicacion'], $n['fecha_programada'], $vistas_rest]);
                                $imported++;
                            }
                        }
                        $stmt_log = $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)");
                        $stmt_log->execute([$_SESSION['user_id'], 'Importación', "Restauró/Importó de JSON. Nuevas: $imported | Reemplazadas: $updated | Omitidas: $skipped."]);
                        header('Location: ' . APP_BASE . 'admin/respaldos?msg=import_ok&n=' . $imported . '&u=' . $updated . '&s=' . $skipped);
                        exit;
                    } else {
                        header('Location: ' . APP_BASE . 'admin/respaldos?err=' . urlencode("El archivo cargado no es un JSON válido o fue generado por otro sistema distinto a HTVPERU CMS."));
                        exit;
                    }
                } else {
                    header('Location: ' . APP_BASE . 'admin/respaldos?err=' . urlencode("Debes seleccionar un archivo JSON válido para importar."));
                    exit;
                }
            }
        }

        header('Location: ' . APP_BASE . 'admin/respaldos');
        exit;
    }
}
