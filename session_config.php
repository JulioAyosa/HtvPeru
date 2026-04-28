<?php
// session_config.php — MEJ-NEW-01 FIX: Configuración segura de sesiones
// Incluir ANTES de session_start() en cada módulo

if (session_status() === PHP_SESSION_NONE) {
    @ini_set('session.cookie_httponly', 1);
    @ini_set('session.cookie_samesite', 'Strict');
    @ini_set('session.use_strict_mode', 1);
    @ini_set('session.use_only_cookies', 1);

    // Solo activar Secure flag si estamos en HTTPS
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        @ini_set('session.cookie_secure', 1);
    }
}
