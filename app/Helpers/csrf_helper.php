<?php
// csrf_helper.php — CRIT-04 FIX + HIGH-R01 FIX: Protección CSRF con Pool de Tokens
// Incluir este archivo en cada módulo admin DESPUÉS de session_start()

define('CSRF_TOKEN_POOL_SIZE', 5); // HIGH-R01: Permite N tokens simultáneos (multi-tab safe)

/**
 * Genera un token CSRF, lo agrega al pool y devuelve el HTML del input hidden.
 * HIGH-R01 FIX: Usa pool de tokens para soportar múltiples tabs abiertas.
 */
function csrf_field(): string {
    $token = _csrf_generate();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Devuelve solo el valor de un token válido (para URLs o JS).
 */
function csrf_token(): string {
    return _csrf_generate();
}

/**
 * Genera un nuevo token y lo agrega al pool de la sesión.
 */
function _csrf_generate(): string {
    if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }
    
    // Reutilizar el último token si existe (evita generar uno nuevo cada vez que se renderiza el form)
    if (!empty($_SESSION['csrf_tokens'])) {
        return end($_SESSION['csrf_tokens']);
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][] = $token;
    
    // Mantener el pool acotado
    if (count($_SESSION['csrf_tokens']) > CSRF_TOKEN_POOL_SIZE) {
        $_SESSION['csrf_tokens'] = array_slice($_SESSION['csrf_tokens'], -CSRF_TOKEN_POOL_SIZE);
    }
    
    // Compatibilidad backward: también setear el token simple
    $_SESSION['csrf_token'] = $token;
    
    return $token;
}

/**
 * Valida el token CSRF recibido contra el pool de la sesión.
 * HIGH-R01 FIX: Acepta cualquier token del pool (multi-tab safe).
 * Detiene la ejecución si no coincide.
 */
function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    
    if (empty($token)) {
        http_response_code(403);
        die('<h1>403 - Solicitud rechazada</h1><p>Token de seguridad faltante. <a href="javascript:history.back()">Volver</a></p>');
    }
    
    $valid = false;
    
    // Check against token pool
    if (!empty($_SESSION['csrf_tokens']) && is_array($_SESSION['csrf_tokens'])) {
        foreach ($_SESSION['csrf_tokens'] as $key => $stored_token) {
            if (hash_equals($stored_token, $token)) {
                $valid = true;
                // Consumir el token usado (prevent replay)
                unset($_SESSION['csrf_tokens'][$key]);
                $_SESSION['csrf_tokens'] = array_values($_SESSION['csrf_tokens']);
                break;
            }
        }
    }
    
    // Fallback: check legacy single token
    if (!$valid && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        $valid = true;
    }
    
    if (!$valid) {
        http_response_code(403);
        die('<h1>403 - Solicitud rechazada</h1><p>Token de seguridad inválido o expirado. <a href="javascript:history.back()">Volver</a></p>');
    }
    
    // Generar nuevo token para el próximo formulario
    $new_token = bin2hex(random_bytes(32));
    if (!isset($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }
    $_SESSION['csrf_tokens'][] = $new_token;
    if (count($_SESSION['csrf_tokens']) > CSRF_TOKEN_POOL_SIZE) {
        $_SESSION['csrf_tokens'] = array_slice($_SESSION['csrf_tokens'], -CSRF_TOKEN_POOL_SIZE);
    }
    $_SESSION['csrf_token'] = $new_token;
}
