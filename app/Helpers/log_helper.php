<?php
// log_helper.php — Función centralizada para registro de actividad
// Reemplaza las queries directas de INSERT INTO registro_actividad dispersas en los módulos

/**
 * Registra una acción en el log de actividad del sistema.
 * FASE 4: Ahora también registra en Monolog para tener un historial en archivo.
 */
function logActividad(PDO $pdo, int $user_id, string $accion, string $detalles = ''): void {
    try {
        $detalles = mb_substr($detalles, 0, 500); // Limitar longitud
        $stmt = $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $accion, $detalles]);
    } catch(Exception $e) {
        try {
            \App\Services\LoggerService::getInstance()->error('Error logging actividad en BD', [
                'user_id' => $user_id,
                'accion'  => $accion,
                'error'   => $e->getMessage(),
            ]);
        } catch (\Throwable $logErr) {
            error_log("Error logging actividad: " . $e->getMessage());
        }
    }
}
