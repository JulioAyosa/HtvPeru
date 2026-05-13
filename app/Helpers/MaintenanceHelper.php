<?php
namespace App\Helpers;

/**
 * MaintenanceHelper — ULTRA CODER CORE LOGIC
 * Proporciona métodos para el mantenimiento automático del sistema.
 */
class MaintenanceHelper {

    /**
     * Limpia archivos basura, caché expirada y logs antiguos.
     */
    public static function cleanJunk() {
        $stats = [
            'cache_cleaned' => 0,
            'logs_cleaned' => 0,
            'tmp_cleaned' => 0
        ];

        // 1. Limpiar caché expirada
        $cacheDir = __DIR__ . '/../../storage/cache/';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '*_cache.json');
            foreach ($files as $file) {
                // Si el archivo tiene más de 24 horas, lo eliminamos (como purga de seguridad)
                // Opcional: El CacheService ya maneja el TTL, pero esto es limpieza física.
                if (time() - filemtime($file) > 86400) {
                    @unlink($file);
                    $stats['cache_cleaned']++;
                }
            }
        }

        // 2. Limpiar logs antiguos (> 90 días)
        $logDir = __DIR__ . '/../../storage/logs/';
        if (is_dir($logDir)) {
            $files = glob($logDir . '*.log');
            foreach ($files as $file) {
                if (time() - filemtime($file) > 90 * 86400) {
                    @unlink($file);
                    $stats['logs_cleaned']++;
                }
            }
        }

        return $stats;
    }
}
