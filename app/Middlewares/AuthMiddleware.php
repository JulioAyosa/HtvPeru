<?php
namespace App\Middlewares;

use Core\Middleware\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface {
    public function handle() {
        if (!isset($_SESSION['user_id'])) {
            $base = defined('APP_BASE') ? APP_BASE : '';
            header("Location: {$base}/login.php");
            exit;
        }
    }
}
