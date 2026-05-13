<?php
// file: conexion.php
// Script de conexión a la base de datos MySQL

// FASE 5: Autoloader Composer para módulos legacy que no pasan por el Front Controller
// Permite resolver clases PSR-4 (AssetManager, LoggerService, etc.)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
// HIGH-NEW-01 FIX: Incluir sanitizador globalmente para sanitize_css() y sanitize_html()
require_once __DIR__ . '/../app/Helpers/html_sanitizer.php';
// HIGH-NEW-02 FIX: Incluir validador de scripts para render_safe_script()
require_once __DIR__ . '/../scripts/script_validator.php';
// HIGH-NEW-03 FIX: Incluir helpers de multimedia
require_once __DIR__ . '/../app/Helpers/watermark.php';
require_once __DIR__ . '/../app/Helpers/media_firewall.php';

// SOLUCIÓN DEFINITIVA PARA TILDES Y CARACTERES ESPECIALES
ini_set('default_charset', 'utf-8');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

// PRE-PRODUCCIÓN: Constante APP_BASE dinámica
// En producción (raíz del dominio): APP_BASE = ''
// En desarrollo (subdirectorio):    APP_BASE = '/piura_noticias_php'
if (!defined('APP_BASE')) {
    $script_dir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    // Detectar si estamos en un subdirectorio conocido
    if (stripos($script_dir, '/piura_noticias_php') !== false) {
        define('APP_BASE', '/piura_noticias_php');
    } else {
        define('APP_BASE', '');
    }
}

// DEFINICIÓN DE RUTAS FÍSICAS (FileSystem)
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__) . '/');
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', ROOT_PATH . 'public/');
// URL base completa para redirecciones absolutas
if (!defined('APP_URL')) {
    $app_url_env = $_ENV['APP_URL'] ?? '';
    define('APP_URL', !empty($app_url_env) ? rtrim($app_url_env, '/') : '');
}

// FASE 3 MVC FIX: Uso estricto del Database Singleton para conexiones persistentes y optimizadas
require_once __DIR__ . '/Database.php';

// PRE-PRODUCCION: Procesar entorno y errores
$app_env = $_ENV['APP_ENV'] ?? 'development';
$app_debug = filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN);

if ($app_debug === false || $app_env === 'production') {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// PRE-PRODUCCION: Forzar HTTPS si estamos en producción y no es localhost
if ($app_env === 'production' && php_sapi_name() !== 'cli') {
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        // Redirigir a HTTPS
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
        if ($host !== 'localhost' && $host !== '127.0.0.1') {
            header('Location: https://' . $host . $request_uri, true, 301);
            exit;
        }
    }
}

try {
    $pdo = \Config\Database::getInstance();
    $GLOBALS['pdo'] = $pdo;

    // CRIT-05 FIX: Pseudo-CRON eliminado de conexion.php
    // La auto-publicación y purga de papelera se ejecutan ahora desde cron_backup.php
    // Las claves de configuración deben crearse mediante el script de migración, no en cada request.
    date_default_timezone_set('America/Lima');

    // CRIT-09 FIX: Expiración de sesión por inactividad (2 horas)
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
        $max_inactive = 7200; // 2 horas en segundos
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $max_inactive) {
            // Registrar expiración antes de destruir
            try {
                $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], 'Sesión Expirada', 'Sesión cerrada automáticamente por inactividad de 2 horas.']);
            } catch(Exception $ex) {}
            session_unset();
            session_destroy();
            $base = defined('APP_BASE') ? APP_BASE : '';
            header('Location: ' . $base . '/login.php?msg=' . urlencode('Tu sesión ha expirado por inactividad. Inicia sesión nuevamente.'));
            exit;
        }
        // Verificación en caliente de bloqueo (Lazy Cache cada 5mins p/ evitar I/O)
        $lazy_check_interval = 300;
        if (!isset($_SESSION['last_validation']) || (time() - $_SESSION['last_validation']) > $lazy_check_interval) {
            try {
                $stmt_check = $pdo->prepare("SELECT estado, motivo_bloqueo FROM usuarios WHERE id = ?");
                $stmt_check->execute([$_SESSION['user_id']]);
                $user_check = $stmt_check->fetch();
                if ($user_check && $user_check['estado'] === 'bloqueado') {
                    session_unset();
                    session_destroy();
                    $base = defined('APP_BASE') ? APP_BASE : '';
                    header('Location: ' . $base . '/login.php?msg=' . urlencode('Tu sesión ha sido revocada de inmediato.'));
                    exit;
                }
                $_SESSION['last_validation'] = time();
            } catch(Exception $ex) {}
        }

        $_SESSION['last_activity'] = time();
    }
} catch (\PDOException $e) {
    // FASE 4: Logger Monolog en lugar de error_log nativo
    try {
        \App\Services\LoggerService::getInstance()->critical('Error de conexión a la BD', [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
        ]);
    } catch (\Throwable $logErr) {
        error_log("Error de conexión a la BD: " . $e->getMessage());
    }
    die("Error de conexión al servidor. Por favor, intente más tarde o contacte al administrador.");
}

