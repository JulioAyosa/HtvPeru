<?php
// file: conexion.example.php
// =============================================
// INSTRUCCIONES DE CONFIGURACIÓN
// =============================================
// 1. Copia este archivo y renómbralo a: conexion.php
// 2. Reemplaza los valores de ejemplo con los datos reales de tu servidor.
// 3. NUNCA subas conexion.php a Git (ya está en .gitignore).
// =============================================

// FASE 5: Autoloader Composer para módulos legacy que no pasan por el Front Controller
// Permite resolver clases PSR-4 (AssetManager, LoggerService, etc.)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
// HIGH-NEW-01 FIX: Incluir sanitizador globalmente para sanitize_css() y sanitize_html()
require_once __DIR__ . '/html_sanitizer.php';
// HIGH-NEW-02 FIX: Incluir validador de scripts para render_safe_script()
require_once __DIR__ . '/script_validator.php';

// SOLUCIÓN DEFINITIVA PARA TILDES Y CARACTERES ESPECIALES
ini_set('default_charset', 'utf-8');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

// FASE 3 MVC FIX: Uso estricto del Database Singleton para conexiones persistentes y optimizadas
require_once __DIR__ . '/config/Database.php';

// =============================================
// IMPORTANTE: Configura tu config/Database.php
// con los siguientes datos de tu servidor:
//
//   DB_HOST     = localhost  (o la IP de tu servidor MySQL)
//   DB_NAME     = piura_noticias_db
//   DB_USER     = TU_USUARIO_MYSQL
//   DB_PASS     = TU_CONTRASEÑA_MYSQL
//   DB_CHARSET  = utf8mb4
// =============================================

try {
    $pdo = \Config\Database::getInstance();
    $GLOBALS['pdo'] = $pdo;

    date_default_timezone_set('America/Lima');

    // CRIT-09 FIX: Expiración de sesión por inactividad (2 horas)
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
        $max_inactive = 7200; // 2 horas en segundos
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $max_inactive) {
            try {
                $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], 'Sesión Expirada', 'Sesión cerrada automáticamente por inactividad de 2 horas.']);
            } catch(Exception $ex) {}
            session_unset();
            session_destroy();
            header('Location: login.php?msg=' . urlencode('Tu sesión ha expirado por inactividad. Inicia sesión nuevamente.'));
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
                    header('Location: login.php?msg=' . urlencode('Tu sesión ha sido revocada de inmediato.'));
                    exit;
                }
                $_SESSION['last_validation'] = time();
            } catch(Exception $ex) {}
        }

        $_SESSION['last_activity'] = time();
    }
} catch (\PDOException $e) {
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
