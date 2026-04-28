<?php
// rate_limiter.php — RISK-NEW-01 FIX: Rate limiting centralizado para APIs públicas

/**
 * Verifica si una IP ha excedido el límite de requests para una acción.
 * @return bool true si la request es permitida, false si debe bloquearse
 */
function check_rate_limit(PDO $pdo, string $action, int $max_requests = 10, int $window_minutes = 1): bool {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE ip = ? AND action = ? AND created_at > NOW() - INTERVAL ? MINUTE");
        $stmt->execute([$ip, $action, $window_minutes]);
        $count = (int)$stmt->fetchColumn();
        
        if ($count >= $max_requests) return false;
        
        $pdo->prepare("INSERT INTO rate_limits (ip, action) VALUES (?, ?)")->execute([$ip, $action]);
        return true;
    } catch (Exception $e) {
        // Si la tabla no existe aún, permitir (fail-open para no romper funcionalidad)
        return true;
    }
}
