<?php
namespace App\Middlewares;

use Core\Middleware\MiddlewareInterface;

class AdminMiddleware implements MiddlewareInterface {
    public function handle() {
        $allowed_roles = ['admin', 'gerencia', 'gerente', 'editor', 'autor'];
        if (!isset($_SESSION['user_role']) || !in_array(strtolower($_SESSION['user_role']), $allowed_roles)) {
            $base = defined('APP_BASE') ? APP_BASE : '';
            header("Location: {$base}/login.php");
            exit;
        }
    }
}
