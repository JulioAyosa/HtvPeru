<?php
// phinx.php — Configuración de Phinx (Sistema de Migraciones)
// FASE 2 MODERNIZACIÓN: Sistema formal de versionamiento de base de datos

// Cargar .env con phpdotenv para reutilizar las mismas credenciales
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/database/migrations',
        'seeds'      => '%%PHINX_CONFIG_DIR%%/database/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinx_migrations',
        'default_environment'     => 'development',

        'development' => [
            'adapter' => 'mysql',
            'host'    => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'name'    => $_ENV['DB_NAME'] ?? 'piura_noticias_db',
            'user'    => $_ENV['DB_USER'] ?? 'root',
            'pass'    => $_ENV['DB_PASS'] ?? '',
            'port'    => $_ENV['DB_PORT'] ?? '3306',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],

        'production' => [
            'adapter' => 'mysql',
            'host'    => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'name'    => $_ENV['DB_NAME'] ?? 'piura_noticias_db',
            'user'    => $_ENV['DB_USER'] ?? 'root',
            'pass'    => $_ENV['DB_PASS'] ?? '',
            'port'    => $_ENV['DB_PORT'] ?? '3306',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],
    'version_order' => 'creation',
];
