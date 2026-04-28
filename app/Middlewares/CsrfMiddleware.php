<?php
namespace App\Middlewares;

use Core\Middleware\MiddlewareInterface;

class CsrfMiddleware implements MiddlewareInterface {
    public function handle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
        }
    }
}
