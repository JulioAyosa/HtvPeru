<?php
namespace Config;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $pdo;

    private $host = 'localhost';
    private $dbname = 'piura_noticias_db'; // CRIT-FIX: Updated real DB name according to earlier tests
    private $user = 'root';
    private $pass = ''; // Asumido vacío localmente o cargado de .env

    private function __construct() {
        // FASE 1 MODERNIZACIÓN: Cargar .env con vlucas/phpdotenv (estándar de la industria)
        // Reemplaza el parser manual anterior. Maneja comillas, tipos, comentarios inline y más.
        $env_file = __DIR__ . '/..';
        if (file_exists($env_file . '/.env')) {
            $dotenv = \Dotenv\Dotenv::createImmutable($env_file);
            $dotenv->safeLoad(); // safeLoad no lanza excepción si .env falta
        }

        $this->host = $_ENV['DB_HOST'] ?? $this->host;
        $this->dbname = $_ENV['DB_NAME'] ?? $this->dbname;
        $this->user = $_ENV['DB_USER'] ?? $this->user;
        $this->pass = $_ENV['DB_PASS'] ?? $this->pass;

        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Parada en error fuerte (Hardening)
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false, // Evitar mitigación y usar true prepared statements físicos del motor MySQL
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // FASE 4: Logger Monolog en lugar de error_log nativo
            try {
                \App\Services\LoggerService::getInstance()->critical('DB Connection Failed', [
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'host'    => $this->host,
                    'dbname'  => $this->dbname,
                ]);
            } catch (\Throwable $logErr) {
                error_log("DB Connection Failed: " . $e->getMessage());
            }
            http_response_code(503);
            header("Retry-After: 60");
            die('<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Mantenimiento - HTVPERU</title><style>body{background:#111827;color:white;font-family:sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;text-align:center;} h1{color:#fbbf24;} p{color:#9ca3af;}</style></head><body><div><h1>Sobrecarga Temporal</h1><p>Nuestros sistemas están procesando actualizaciones. Por favor, recarga la página en unos minutos.</p></div></body></html>');
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    // Prohibir clonación para singleton
    protected function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}
