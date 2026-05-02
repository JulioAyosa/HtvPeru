<?php
/**
 * Cron Job: Auto-Publicador de Noticias
 * Uso: Configurar en cPanel para que se ejecute cada 1 minuto
 * Comando sugerido: php /ruta/absoluta/a/tu/public_html/cron_publicador.php
 */

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/app/Services/SocialPublisherService.php';
require_once __DIR__ . '/core/cache_helper.php';

// Para asegurar la misma zona horaria que el CMS
date_default_timezone_set('America/Lima');

try {
    $pdo = \Config\Database::getInstance();
    
    // Buscar noticias que están programadas y su fecha ya pasó
    $stmt = $pdo->prepare("SELECT id, titulo FROM noticias WHERE estado_publicacion = 'programado' AND fecha_programada IS NOT NULL AND fecha_programada <= NOW()");
    $stmt->execute();
    $noticias = $stmt->fetchAll();

    if (count($noticias) > 0) {
        $publisher = new \App\Services\SocialPublisherService($pdo);
        $ids_actualizados = [];

        foreach ($noticias as $noticia) {
            // Actualizar a publicado
            $stmt_update = $pdo->prepare("UPDATE noticias SET estado_publicacion = 'publicado' WHERE id = ?");
            if ($stmt_update->execute([$noticia['id']])) {
                $ids_actualizados[] = $noticia['id'];
                
                // Disparar redes sociales
                $publisher->publish($noticia['id']);

                // Opcional: Actualizar el estado en el registro de planificación (registro_contenidos)
                $pdo->prepare("UPDATE registro_contenidos SET rebote = '(completada)', completado = 1 WHERE titular = ?")->execute([$noticia['titulo']]);
            }
        }

        // Limpiar caché porque la página principal ha cambiado
        build_global_cache($pdo);

        echo "Cron ejecutado exitosamente. Se publicaron " . count($ids_actualizados) . " noticias: " . implode(", ", $ids_actualizados) . "\n";
    } else {
        echo "Cron ejecutado. No hay noticias pendientes para publicar en este momento.\n";
    }

} catch (\Exception $e) {
    echo "Error en cron: " . $e->getMessage() . "\n";
}
