<?php
/**
 * Cron Job: Worker de Tareas Ligeras (Vistas y Auto-Publicador)
 * Uso: Configurar en cPanel para que se ejecute cada 1 o 5 minutos
 * Comando sugerido: php /ruta/absoluta/a/tu/public_html/cron_worker.php
 */

$is_cli = php_sapi_name() === 'cli';

// Auth Token desde .env
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

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/app/Services/SocialPublisherService.php';
require_once __DIR__ . '/core/cache_helper.php';

date_default_timezone_set('America/Lima');

try {
    $pdo = \Config\Database::getInstance();
    $cacheService = new \App\Services\CacheService();
    $now_db = date('Y-m-d H:i:s');
    
    // 1. Auto-publicar noticias programadas
    $stmt = $pdo->prepare("SELECT id, titulo FROM noticias WHERE estado_publicacion = 'programado' AND fecha_programada IS NOT NULL AND fecha_programada <= ?");
    $stmt->execute([$now_db]);
    $noticias = $stmt->fetchAll();

    if (count($noticias) > 0) {
        $publisher = new \App\Services\SocialPublisherService($pdo);
        $ids_actualizados = [];

        foreach ($noticias as $noticia) {
            $stmt_update = $pdo->prepare("UPDATE noticias SET estado_publicacion = 'publicado' WHERE id = ?");
            if ($stmt_update->execute([$noticia['id']])) {
                $ids_actualizados[] = $noticia['id'];
                $publisher->publish($noticia['id']);
                try {
                    $pdo->prepare("UPDATE registro_contenidos SET rebote = '(completada)', completado = 1 WHERE titular = ?")->execute([$noticia['titulo']]);
                } catch(\Exception $e) {}
            }
        }
        
        // Limpiar caché
        $cacheService->clearAll();
        echo "Auto-publicadas: " . count($ids_actualizados) . " noticias.\n";
    }

    // 2. Volcar Cola de Vistas
    $pdo->beginTransaction();
    try {
        $pdo->exec("UPDATE noticias n JOIN (SELECT noticia_id, COUNT(*) as suma FROM cola_vistas WHERE noticia_id > 0 GROUP BY noticia_id) c ON n.id = c.noticia_id SET n.vistas = n.vistas + c.suma");
        $pdo->exec("UPDATE noticias n JOIN (SELECT noticia_slug, COUNT(*) as suma FROM cola_vistas WHERE noticia_slug != '' GROUP BY noticia_slug) c ON n.slug = c.noticia_slug SET n.vistas = n.vistas + c.suma");
        $pdo->exec("DELETE FROM cola_vistas");
        $pdo->commit();
        echo "Cola de vistas volcada exitosamente.\n";
    } catch (\Exception $e) {
        $pdo->rollBack();
        echo "Error al volcar cola de vistas: " . $e->getMessage() . "\n";
    }

    // 3. Limpieza de seguridad temporal
    try {
        $pdo->exec("DELETE FROM rate_limits WHERE created_at < NOW() - INTERVAL 1 DAY");
        $pdo->exec("DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL 1 DAY");
    } catch (\Exception $e) {}

} catch (\Exception $e) {
    echo "Error en cron_worker: " . $e->getMessage() . "\n";
}
