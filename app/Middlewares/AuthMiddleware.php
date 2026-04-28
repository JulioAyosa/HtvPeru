<?php
namespace App\Middlewares;

use Core\Middleware\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface {
    public function handle() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login.php");
            exit;
        }
    }
}
