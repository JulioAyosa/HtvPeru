<?php
// Migración: Sistema RBAC (Roles y Permisos)
// Traslada la lógica de migrate_rbac.php a una migración formal

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddRbacTables extends AbstractMigration
{
    public function change(): void
    {
        // ============================================================
        // TABLA DE ROLES
        // ============================================================
        if (!$this->hasTable('roles')) {
            $this->table('roles')
                ->addColumn('nombre', 'string', ['limit' => 50])
                ->addColumn('descripcion', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('is_system', 'boolean', ['default' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->create();
        }

        // ============================================================
        // TABLA DE PERMISOS
        // ============================================================
        if (!$this->hasTable('permisos')) {
            $this->table('permisos')
                ->addColumn('clave', 'string', ['limit' => 50])
                ->addColumn('modulo', 'string', ['limit' => 50])
                ->addColumn('descripcion', 'string', ['limit' => 255])
                ->addIndex('clave', ['unique' => true])
                ->create();
        }

        // ============================================================
        // TABLA PIVOT: ROL ↔ PERMISOS
        // ============================================================
        if (!$this->hasTable('rol_permisos')) {
            $this->table('rol_permisos', ['id' => false, 'primary_key' => ['rol_id', 'permiso_id']])
                ->addColumn('rol_id', 'integer', ['signed' => true])
                ->addColumn('permiso_id', 'integer', ['signed' => true])
                ->addForeignKey('rol_id', 'roles', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->addForeignKey('permiso_id', 'permisos', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->create();
        }

        // ============================================================
        // MODIFICAR TABLA USUARIOS: Agregar rol_id
        // ============================================================
        $usersTable = $this->table('usuarios');
        if (!$usersTable->hasColumn('rol_id')) {
            $usersTable
                ->addColumn('rol_id', 'integer', [
                    'null' => true,
                    'default' => 3,
                    'after' => 'password_hash',
                    'signed' => true,
                ])
                ->addForeignKey('rol_id', 'roles', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                ->update();
        }
    }

    /**
     * Seed de datos RBAC iniciales.
     * Se ejecuta después del change() automáticamente solo la primera vez.
     */
    public function up(): void
    {
        // Llamar a change() primero
        parent::up();

        // Solo insertar datos si las tablas están vacías
        $rolesCount = $this->fetchRow('SELECT COUNT(*) AS cnt FROM roles');
        if ((int)($rolesCount['cnt'] ?? 0) === 0) {
            // Roles base
            $this->execute("INSERT INTO roles (id, nombre, descripcion, is_system) VALUES 
                (1, 'Administrador', 'Control total del sistema', 1),
                (2, 'Gerente', 'Acceso a reportes, analíticas y gestión comercial', 1),
                (3, 'Editor', 'Creación y edición de contenido', 1)
            ");
        }

        $permisosCount = $this->fetchRow('SELECT COUNT(*) AS cnt FROM permisos');
        if ((int)($permisosCount['cnt'] ?? 0) === 0) {
            // Permisos
            $this->execute("INSERT INTO permisos (clave, modulo, descripcion) VALUES 
                ('manage_users', 'Usuarios', 'Crear, editar, bloquear y eliminar usuarios'),
                ('manage_roles', 'Roles', 'Gestionar roles y permisos del sistema'),
                ('manage_categories', 'Categorías', 'Crear, editar y eliminar categorías'),
                ('manage_news', 'Noticias', 'Administrar todas las noticias'),
                ('publish_news', 'Noticias', 'Publicar noticias directamente'),
                ('manage_comments', 'Comentarios', 'Aprobar, rechazar o eliminar comentarios'),
                ('manage_media', 'Multimedia', 'Subir y eliminar archivos multimedia'),
                ('manage_polls', 'Encuestas', 'Crear y gestionar encuestas'),
                ('manage_ads', 'Publicidad', 'Gestionar banners y anuncios publicitarios'),
                ('view_reports', 'Reportes', 'Ver estadísticas y reportes del sistema'),
                ('manage_settings', 'Configuración', 'Acceso a ajustes globales, SEO, sitemap'),
                ('manage_pages', 'Páginas', 'Crear y editar páginas estáticas'),
                ('manage_newsletters', 'Boletines', 'Enviar y gestionar boletines informativos'),
                ('system_tools', 'Sistema', 'Optimización de BD y Respaldos')
            ");

            // Asignar todos los permisos al Administrador
            $this->execute("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) SELECT 1, id FROM permisos");

            // Permisos del Gerente
            $this->execute("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) SELECT 2, id FROM permisos WHERE clave IN ('view_reports', 'manage_ads', 'manage_polls')");

            // Permisos del Editor
            $this->execute("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) SELECT 3, id FROM permisos WHERE clave IN ('manage_news', 'publish_news', 'manage_media', 'manage_comments', 'manage_categories')");
        }

        // Migrar datos del ENUM rol → rol_id (si existen usuarios sin rol_id)
        $this->execute("UPDATE usuarios SET rol_id = 1 WHERE rol = 'admin' AND (rol_id IS NULL OR rol_id = 3)");
        $this->execute("UPDATE usuarios SET rol_id = 2 WHERE rol IN ('gerencia', 'gerente') AND (rol_id IS NULL OR rol_id = 3)");
        $this->execute("UPDATE usuarios SET rol_id = 3 WHERE rol IN ('editor', 'autor') AND (rol_id IS NULL OR rol_id = 3)");
    }
}
