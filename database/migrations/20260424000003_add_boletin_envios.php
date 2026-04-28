<?php
// Migración: Tabla de tracking individual de envíos de boletines

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddBoletinEnvios extends AbstractMigration
{
    public function change(): void
    {
        if (!$this->hasTable('boletin_envios')) {
            $this->table('boletin_envios')
                ->addColumn('boletin_historial_id', 'integer', ['signed' => true])
                ->addColumn('suscriptor_id', 'integer', ['signed' => true])
                ->addColumn('enviado_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addForeignKey('boletin_historial_id', 'boletines_historial', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->addForeignKey('suscriptor_id', 'suscriptores', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->addIndex(['boletin_historial_id', 'suscriptor_id'], ['unique' => true, 'name' => 'idx_boletin_suscriptor'])
                ->create();
        }
    }
}
