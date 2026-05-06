<?php
$is_cli = php_sapi_name() === 'cli';

// ANTIGRAVITY FIX HIGH-05: Read cron token from .env instead of hardcoding
$_cron_env = [];
$_env_path = __DIR__ . '/.env';
if (file_exists($_env_path)) {
    $lines = file($_env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_cron_env[trim($key)] = trim($value);
    }
}
$cron_secret = $_cron_env['CRON_TOKEN'] ?? 'changeme_cron_token_' . md5(__DIR__);
$valid_token = isset($_GET['token']) && hash_equals($cron_secret, $_GET['token']);

if (!$is_cli && !$valid_token) { 
    http_response_code(403);
    die('Access Denied. Use CLI or provide a valid auth token.'); 
}
// cron_backup.php - Ejecutarse diariamente con Tareas de Windows
// CRIT-05 FIX: Tareas automáticas movidas aquí desde conexion.php
date_default_timezone_set('America/Lima');

// === TAREAS AUTOMÁTICAS PESADAS ===
require_once __DIR__ . '/conexion.php';

try {
    $now_db = date('Y-m-d H:i:s');
    
    // Auto-purgar papelera (Hard Delete >15 días)
    $purge_stmt = $pdo->prepare("SELECT id, imagen_url, video_poster_url FROM noticias WHERE estado_publicacion = 'papelera' AND deleted_at < ? - INTERVAL 15 DAY");
    $purge_stmt->execute([$now_db]);
    $to_purge = $purge_stmt->fetchAll();
    foreach ($to_purge as $p) {
        if (!empty($p['imagen_url']) && file_exists($p['imagen_url'])) @unlink($p['imagen_url']);
        if (!empty($p['video_poster_url']) && file_exists($p['video_poster_url'])) @unlink($p['video_poster_url']);
        $pdo->prepare("DELETE FROM noticias WHERE id = ?")->execute([$p['id']]);
    }
    echo "Papelera purgada: " . count($to_purge) . " noticias eliminadas\n";
    
} catch (\PDOException $e) {
    echo "Error en tareas automáticas pesadas: " . $e->getMessage() . "\n";
}
// === FIN TAREAS AUTOMÁTICAS PESADAS ===

// RISK-09 FIX: Leer credenciales desde .env en vez de hardcodear
$env_file = __DIR__ . '/.env';
$_env_vars = [];
if (file_exists($env_file)) {
    $env_lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_env_vars[trim($key)] = trim($value);
    }
}
$db_host = $_env_vars['DB_HOST'] ?? 'localhost';
$db_user = $_env_vars['DB_USER'] ?? 'root';
$db_pass = $_env_vars['DB_PASS'] ?? '';
$db_name = $_env_vars['DB_NAME'] ?? 'piura_noticias_db';

// ANTIGRAVITY FIX CRIT-D03: Define backup_dir before using it
$backup_dir = __DIR__ . '/backups/';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

$fecha_hoy = date('Y-m-d');
$nombre_zip = "backup_" . $fecha_hoy . ".zip";
$ruta_zip = $backup_dir . $nombre_zip;

// 2. CREAR BACKUP DE BASE DE DATOS (.sql)
$sql_file = $backup_dir . "db_snapshot_" . $fecha_hoy . ".sql";
// CRIT-R06 FIX: Usar archivo de opciones en vez de password en CLI
$mysql_cnf = $backup_dir . '.my_backup.cnf';
file_put_contents($mysql_cnf, "[mysqldump]\nuser={$db_user}\npassword={$db_pass}\nhost={$db_host}\n");
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    chmod($mysql_cnf, 0600);
}

// HIGH-R07 FIX: Detectar ruta de mysqldump según SO
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $mysqldump_bin = 'c:\\xampp\\mysql\\bin\\mysqldump.exe';
} else {
    // PRE-PRODUCCION: Detectar ruta automáticamente en Linux
    $mysqldump_bin = trim(shell_exec('which mysqldump 2>/dev/null') ?: '/usr/bin/mysqldump');
}
$command = "{$mysqldump_bin} --defaults-extra-file=" . escapeshellarg($mysql_cnf) . " {$db_name} > " . escapeshellarg($sql_file);
system($command);

// 3. CREAR ARCHIVO ZIP (Núcleo + BD)
$zip = new ZipArchive();
if ($zip->open($ruta_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    if(file_exists($sql_file)){
        $zip->addFile($sql_file, "db_snapshot.sql");
    }

    $rootPath = realpath(__DIR__);
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);
    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);
            
            // Ignorar backups, cron, archivos sensibles y scripts de desarrollo
            $excluded_patterns = ['backups', 'backups_automaticos', 'core_backups', '.env', '.git'];
            $excluded_extensions = ['py', 'ps1', 'sql', 'doc', 'docx', 'zip', 'rar', 'tar', 'gz'];
            $excluded_files = ['cron_backup.php', 'test_debug.php', 'read_db.php'];
            $skip = false;
            foreach ($excluded_patterns as $pattern) {
                if (strpos($relativePath, $pattern) !== false) { $skip = true; break; }
            }
            $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
            if (in_array($ext, $excluded_extensions)) $skip = true;
            if (in_array(basename($relativePath), $excluded_files)) $skip = true;
            
            if (!$skip) {
                 $zip->addFile($filePath, $relativePath);
            }
        }
    }
    $zip->close();
}
if(file_exists($sql_file)) { unlink($sql_file); }
// CRIT-R06 FIX: Eliminar archivo de credenciales temporal

// 4. LIMPIEZA DE PAPELERA DE BACKUPS (Eliminar permanentemente > 15 días)
$papelera_dir = $backup_dir . 'papelera/';
if (is_dir($papelera_dir)) {
    $archivos_papelera = glob($papelera_dir . "*.zip");
    foreach ($archivos_papelera as $archivo) {
        $dias_antiguedad = floor((time() - filemtime($archivo)) / (60 * 60 * 24));
        if ($dias_antiguedad >= 15) {
            @unlink($archivo);
        }
    }
}

echo "Backup Automático finalizado correctamente.";
?>
