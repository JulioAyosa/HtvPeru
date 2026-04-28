<?php
namespace App\Services;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

/**
 * LoggerService — FASE 4 MODERNIZACIÓN
 * Servicio centralizado de logging usando Monolog.
 * Reemplaza todos los error_log() dispersos por un sistema profesional
 * con rotación de archivos, niveles de severidad y formato estándar.
 * 
 * Uso:
 *   $log = \App\Services\LoggerService::getInstance();
 *   $log->error('Mensaje de error', ['contexto' => 'datos']);
 *   $log->warning('Algo sospechoso pasó');
 *   $log->info('Acción completada');
 */
class LoggerService
{
    private static ?Logger $instance = null;
    private static ?Logger $securityInstance = null;

    /**
     * Logger principal de la aplicación.
     * Escribe en storage/logs/app-YYYY-MM-DD.log con rotación de 30 días.
     */
    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            $logDir = __DIR__ . '/../../storage/logs';
            
            // Crear directorio si no existe
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }

            self::$instance = new Logger('htvperu');

            // Formato legible: [2026-04-24 12:00:00] htvperu.ERROR: Mensaje {contexto}
            $formatter = new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                'Y-m-d H:i:s',
                true,  // allowInlineLineBreaks
                true   // ignoreEmptyContextAndExtra
            );

            // Handler con rotación diaria (máximo 30 archivos)
            $handler = new RotatingFileHandler(
                $logDir . '/app.log',
                30,           // maxFiles: 30 días de retención
                Logger::DEBUG // Nivel mínimo: registrar todo
            );
            $handler->setFormatter($formatter);

            self::$instance->pushHandler($handler);
        }

        return self::$instance;
    }

    /**
     * Logger de seguridad separado.
     * Escribe en storage/logs/security-YYYY-MM-DD.log
     * Para eventos como: scripts bloqueados, CSRF fallidos, intentos de login, etc.
     */
    public static function security(): Logger
    {
        if (self::$securityInstance === null) {
            $logDir = __DIR__ . '/../../storage/logs';
            
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }

            self::$securityInstance = new Logger('security');

            $formatter = new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message% %context%\n",
                'Y-m-d H:i:s',
                true,
                true
            );

            $handler = new RotatingFileHandler(
                $logDir . '/security.log',
                90,             // 90 días de retención para logs de seguridad
                Logger::WARNING // Solo WARNING y superiores
            );
            $handler->setFormatter($formatter);

            self::$securityInstance->pushHandler($handler);
        }

        return self::$securityInstance;
    }
}
