<?php
// Migración Inicial: Esquema completo de la base de datos
// Generada a partir de database_full.sql
// Esta migración SOLO se ejecuta en bases de datos nuevas (vacías).
// Si ya tienes las tablas, Phinx las saltará gracias a los IF NOT EXISTS.

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitSchema extends AbstractMigration
{
    public function change(): void
    {
        // ============================================================
        // 1. TABLA DE USUARIOS
        // ============================================================
        if (!$this->hasTable('usuarios')) {
            $this->table('usuarios')
                ->addColumn('nombre_completo', 'string', ['limit' => 100])
                ->addColumn('email', 'string', ['limit' => 100])
                ->addColumn('password_hash', 'string', ['limit' => 255])
                ->addColumn('rol', 'enum', [
                    'values' => ['admin', 'editor', 'gerencia'],
                    'default' => 'editor',
                ])
                ->addColumn('estado', 'enum', [
                    'values' => ['activo', 'bloqueado'],
                    'default' => 'activo',
                ])
                ->addColumn('motivo_bloqueo', 'text', ['null' => true, 'default' => null])
                ->addColumn('avatar_url', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('fecha_creacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('deleted_at', 'timestamp', ['null' => true, 'default' => null])
                ->addIndex('email', ['unique' => true, 'name' => 'idx_email'])
                ->addIndex('estado', ['name' => 'idx_estado'])
                ->create();
        }

        // ============================================================
        // 2. TABLA DE NOTICIAS
        // ============================================================
        if (!$this->hasTable('noticias')) {
            $this->table('noticias')
                ->addColumn('titulo', 'string', ['limit' => 255])
                ->addColumn('slug', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('extracto', 'string', ['limit' => 500])
                ->addColumn('contenido', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM])
                ->addColumn('categoria', 'string', ['limit' => 50])
                ->addColumn('distrito', 'string', ['limit' => 100, 'null' => true, 'default' => null])
                ->addColumn('imagen_url', 'string', ['limit' => 500])
                ->addColumn('video_poster_url', 'string', ['limit' => 500, 'null' => true, 'default' => null])
                ->addColumn('autor_id', 'integer', ['signed' => true])
                ->addColumn('es_destacada', 'boolean', ['default' => false])
                ->addColumn('estado_publicacion', 'enum', [
                    'values' => ['borrador', 'publicado', 'programado', 'papelera'],
                    'default' => 'borrador',
                ])
                ->addColumn('fecha_publicacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('fecha_programada', 'timestamp', ['null' => true, 'default' => null])
                ->addColumn('vistas', 'integer', ['signed' => false, 'default' => 0])
                ->addColumn('tags', 'string', ['limit' => 500, 'null' => true, 'default' => null])
                ->addColumn('fuente_nombre', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('fuente_url', 'string', ['limit' => 500, 'null' => true, 'default' => null])
                ->addColumn('seo_titulo', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('seo_descripcion', 'string', ['limit' => 500, 'null' => true, 'default' => null])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addColumn('deleted_at', 'timestamp', ['null' => true, 'default' => null])
                ->addForeignKey('autor_id', 'usuarios', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->addIndex('slug', ['unique' => true, 'name' => 'idx_slug'])
                ->addIndex(['estado_publicacion', 'fecha_publicacion'], ['name' => 'idx_estado_fecha'])
                ->addIndex(['categoria', 'estado_publicacion', 'fecha_publicacion'], ['name' => 'idx_categoria_estado'])
                ->addIndex(['es_destacada', 'estado_publicacion'], ['name' => 'idx_destacada'])
                ->addIndex('distrito', ['name' => 'idx_distrito'])
                ->create();
        }

        // ============================================================
        // 3. TABLA DE COMENTARIOS
        // ============================================================
        if (!$this->hasTable('comentarios')) {
            $this->table('comentarios')
                ->addColumn('noticia_id', 'integer', ['signed' => true])
                ->addColumn('nombre', 'string', ['limit' => 100])
                ->addColumn('facebook_url', 'string', ['limit' => 500, 'null' => true, 'default' => null])
                ->addColumn('comentario', 'text')
                ->addColumn('estado', 'enum', [
                    'values' => ['Pendiente', 'Aprobado', 'Rechazado'],
                    'default' => 'Pendiente',
                ])
                ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true, 'default' => null])
                ->addColumn('fecha', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('deleted_at', 'timestamp', ['null' => true, 'default' => null])
                ->addForeignKey('noticia_id', 'noticias', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->addIndex(['noticia_id', 'estado'], ['name' => 'idx_noticia_estado'])
                ->addIndex(['ip_address', 'fecha'], ['name' => 'idx_ip_fecha'])
                ->create();
        }

        // ============================================================
        // 4. TABLA DE CATEGORÍAS
        // ============================================================
        if (!$this->hasTable('categorias')) {
            $this->table('categorias')
                ->addColumn('nombre', 'string', ['limit' => 100])
                ->addColumn('slug', 'string', ['limit' => 100])
                ->addColumn('descripcion', 'text', ['null' => true, 'default' => null])
                ->addColumn('color', 'string', ['limit' => 7, 'default' => '#2563eb'])
                ->addColumn('icono', 'string', ['limit' => 50, 'default' => 'ri-folder-line'])
                ->addColumn('imagen_fondo', 'string', ['limit' => 500, 'null' => true, 'default' => null])
                ->addColumn('orden', 'integer', ['default' => 0])
                ->addColumn('mostrar_menu', 'boolean', ['default' => true])
                ->addColumn('estado', 'enum', [
                    'values' => ['activo', 'inactivo'],
                    'default' => 'activo',
                ])
                ->addColumn('fecha_creacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('deleted_at', 'timestamp', ['null' => true, 'default' => null])
                ->addIndex('nombre', ['unique' => true])
                ->addIndex('slug', ['unique' => true])
                ->create();
        }

        // ============================================================
        // 5. TABLA DE SUSCRIPTORES (Newsletter)
        // ============================================================
        if (!$this->hasTable('suscriptores')) {
            $this->table('suscriptores')
                ->addColumn('email', 'string', ['limit' => 255])
                ->addColumn('fecha_suscripcion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex('email', ['unique' => true, 'name' => 'idx_suscriptor_email'])
                ->create();
        }

        // ============================================================
        // 6. TABLA DE HISTORIAL DE BOLETINES
        // ============================================================
        if (!$this->hasTable('boletines_historial')) {
            $this->table('boletines_historial')
                ->addColumn('asunto', 'string', ['limit' => 255])
                ->addColumn('contenido', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM, 'null' => true])
                ->addColumn('destinatarios', 'json', ['null' => true, 'default' => null])
                ->addColumn('total_enviados', 'integer', ['default' => 0])
                ->addColumn('fecha_envio', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->create();
        }

        // ============================================================
        // 7. TABLAS DE ENCUESTAS
        // ============================================================
        if (!$this->hasTable('encuestas')) {
            $this->table('encuestas')
                ->addColumn('pregunta', 'string', ['limit' => 500])
                ->addColumn('estado', 'enum', [
                    'values' => ['activa', 'cerrada'],
                    'default' => 'activa',
                ])
                ->addColumn('fecha_limite', 'timestamp', ['null' => true, 'default' => null])
                ->addColumn('fecha_creacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('deleted_at', 'timestamp', ['null' => true, 'default' => null])
                ->create();
        }

        if (!$this->hasTable('encuestas_opciones')) {
            $this->table('encuestas_opciones')
                ->addColumn('encuesta_id', 'integer', ['signed' => true])
                ->addColumn('opcion_texto', 'string', ['limit' => 255])
                ->addColumn('votos', 'integer', ['signed' => false, 'default' => 0])
                ->addForeignKey('encuesta_id', 'encuestas', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->create();
        }

        if (!$this->hasTable('encuestas_votos')) {
            $this->table('encuestas_votos')
                ->addColumn('encuesta_id', 'integer', ['signed' => true])
                ->addColumn('opcion_id', 'integer', ['signed' => true])
                ->addColumn('ip_address', 'string', ['limit' => 45])
                ->addColumn('fecha', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addForeignKey('encuesta_id', 'encuestas', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->addIndex(['encuesta_id', 'ip_address'], ['name' => 'idx_encuesta_ip'])
                ->create();
        }

        // ============================================================
        // 8. TABLA DE PUBLICIDAD
        // ============================================================
        if (!$this->hasTable('publicidad')) {
            $this->table('publicidad')
                ->addColumn('nombre', 'string', ['limit' => 255])
                ->addColumn('tipo', 'enum', [
                    'values' => ['imagen', 'adsense'],
                    'default' => 'imagen',
                ])
                ->addColumn('ubicacion', 'string', ['limit' => 50])
                ->addColumn('imagen_url', 'string', ['limit' => 500, 'null' => true, 'default' => null])
                ->addColumn('enlace_url', 'string', ['limit' => 500, 'null' => true, 'default' => null])
                ->addColumn('codigo_script', 'text', ['null' => true, 'default' => null])
                ->addColumn('estado', 'enum', [
                    'values' => ['activo', 'inactivo'],
                    'default' => 'activo',
                ])
                ->addColumn('vistas', 'integer', ['signed' => false, 'default' => 0])
                ->addColumn('clics', 'integer', ['signed' => false, 'default' => 0])
                ->addColumn('fecha_creacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('deleted_at', 'timestamp', ['null' => true, 'default' => null])
                ->create();
        }

        // ============================================================
        // 9. TABLA DE CONFIGURACIÓN GLOBAL
        // ============================================================
        if (!$this->hasTable('configuracion')) {
            $this->table('configuracion')
                ->addColumn('clave', 'string', ['limit' => 100])
                ->addColumn('valor', 'text', ['null' => true, 'default' => null])
                ->addColumn('tipo', 'string', ['limit' => 50, 'default' => 'texto'])
                ->addIndex('clave', ['unique' => true, 'name' => 'idx_clave'])
                ->create();
        }

        // ============================================================
        // 10. TABLA DE PÁGINAS ESTÁTICAS
        // ============================================================
        if (!$this->hasTable('paginas_estaticas')) {
            $this->table('paginas_estaticas')
                ->addColumn('titulo', 'string', ['limit' => 255])
                ->addColumn('slug', 'string', ['limit' => 255])
                ->addColumn('contenido', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM, 'null' => true])
                ->addColumn('estado', 'enum', [
                    'values' => ['publicado', 'borrador'],
                    'default' => 'publicado',
                ])
                ->addColumn('fecha_creacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addColumn('deleted_at', 'timestamp', ['null' => true, 'default' => null])
                ->addIndex('slug', ['unique' => true])
                ->create();
        }

        // ============================================================
        // 11. TABLA DE REGISTRO DE ACTIVIDAD (Audit Log)
        // ============================================================
        if (!$this->hasTable('registro_actividad')) {
            $this->table('registro_actividad')
                ->addColumn('user_id', 'integer', ['signed' => true])
                ->addColumn('accion', 'string', ['limit' => 100])
                ->addColumn('detalles', 'text', ['null' => true])
                ->addColumn('fecha_registro', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['user_id', 'fecha_registro'], ['name' => 'idx_user_fecha'])
                ->create();
        }

        // ============================================================
        // 12. TABLA DE INTENTOS DE LOGIN (Brute Force Protection)
        // ============================================================
        if (!$this->hasTable('login_attempts')) {
            $this->table('login_attempts')
                ->addColumn('ip', 'string', ['limit' => 45])
                ->addColumn('email', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('attempted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['ip', 'attempted_at'], ['name' => 'idx_ip_time'])
                ->create();
        }

        // ============================================================
        // 13. TABLA DE RATE LIMITS (API Protection)
        // ============================================================
        if (!$this->hasTable('rate_limits')) {
            $this->table('rate_limits')
                ->addColumn('ip', 'string', ['limit' => 45])
                ->addColumn('action', 'string', ['limit' => 50])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['ip', 'action', 'created_at'], ['name' => 'idx_ip_action_time'])
                ->create();
        }

        // ============================================================
        // 14. COLA DE VISTAS (Async View Counter)
        // ============================================================
        if (!$this->hasTable('cola_vistas')) {
            $this->table('cola_vistas')
                ->addColumn('noticia_id', 'integer', ['signed' => true, 'null' => true, 'default' => null])
                ->addColumn('noticia_slug', 'string', ['limit' => 255, 'null' => true, 'default' => null])
                ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true, 'default' => null])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex('noticia_id', ['name' => 'idx_noticia_id'])
                ->addIndex('noticia_slug', ['name' => 'idx_noticia_slug'])
                ->create();
        }
    }
}
