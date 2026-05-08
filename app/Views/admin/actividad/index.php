<?php
// app/Views/admin/actividad/index.php
// Variables asumidas: $actividades, $page, $total_pages, $total_rows, $offset, $per_page

if (!function_exists('formatDetalles')) {
    function formatDetalles($texto) {
        $texto = htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        
        $texto = preg_replace('/(ID #\d+)/i', '<span style="background:#e0e7ff; color:#3730a3; padding:2px 6px; border-radius:4px; font-size:0.75rem; font-weight:bold; border:1px solid #c7d2fe; white-space:nowrap;">$1</span>', $texto);
        
        $texto = preg_replace("/(?:&quot;|&#039;|')(.*?)(?:&quot;|&#039;|')/", "'<span style=\"color:#111827; font-weight:600; font-style:italic;\">$1</span>'", $texto);
        
        $texto = preg_replace('/(Estado: )([a-zA-Z0-9_]+)/i', '$1<span style="background:#fef3c7; color:#92400e; padding:2px 6px; border-radius:4px; font-size:0.75rem; font-weight:bold; text-transform:uppercase;">$2</span>', $texto);
        $texto = preg_replace('/( como )([a-zA-Z0-9_]+)/i', '$1<span style="background:#fef3c7; color:#92400e; padding:2px 6px; border-radius:4px; font-size:0.75rem; font-weight:bold; text-transform:uppercase;">$2</span>', $texto);
        
        $texto = preg_replace('/(para el \d{2}\/\d{2}\/\d{4} \d{2}:\d{2})/i', '<span style="color:#2563eb; font-weight:600;">$1</span>', $texto);

        return $texto;
    }
}
?>

<style>
    .log-timeline {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .log-item {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.5rem;
        display: flex;
        gap: 1.5rem;
        align-items: flex-start;
        box-shadow: var(--shadow-sm);
        transition: all 0.2s;
    }
    .log-item:hover {
        box-shadow: 0 8px 20px rgba(0,0,0,0.04);
        border-color: #cbd5e1;
        transform: translateY(-2px);
    }
    .log-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    .log-icon.creacion { background: linear-gradient(135deg, #22c55e, #16a34a); color: white; box-shadow: 0 4px 10px rgba(34, 197, 94, 0.2); }
    .log-icon.actualizacion { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2); }
    .log-icon.eliminacion { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2); }
    .log-icon.login { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; box-shadow: 0 4px 10px rgba(139, 92, 246, 0.2); }
    .log-icon.ot { background: linear-gradient(135deg, #94a3b8, #64748b); color: white; box-shadow: 0 4px 10px rgba(100, 116, 139, 0.2); }
    
    .log-content { flex-grow: 1; }
    .log-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .log-title {
        font-weight: 800;
        color: #1e293b;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .log-meta {
        font-size: 0.85rem;
        color: var(--text-muted);
        display: flex;
        gap: 1rem;
        align-items: center;
    }
    .log-user {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        background: #f1f5f9;
        padding: 4px 12px;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        font-weight: 700;
        color: #334155;
        font-size: 0.8rem;
    }
    .log-user i { color: var(--primary-color); }
    
    .log-details {
        color: #475569;
        font-size: 0.95rem;
        line-height: 1.6;
        background: #f8fafc;
        padding: 1rem;
        border-radius: 8px;
        border: 1px dashed #cbd5e1;
    }
    .log-id {
        font-family: monospace;
        color: #94a3b8;
        font-size: 0.8rem;
        margin-left: 0.5rem;
    }
</style>

<div class="log-timeline">
    <?php if(empty($actividades)): ?>
        <div style="text-align:center; padding: 3rem; background: white; border-radius: var(--radius-md); border: 1px dashed #cbd5e1; color: var(--text-muted);">
            <i class="ri-history-line" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 1rem; display: block;"></i>
            No hay actividad registrada aún en el sistema.
        </div>
    <?php else: ?>
        <?php foreach ($actividades as $a): 
            $clase = 'ot';
            $icon = 'ri-record-circle-line';
            
            if (stripos($a['accion'], 'Creación') !== false) {
                $clase = 'creacion'; $icon = 'ri-add-circle-fill';
            } elseif (stripos($a['accion'], 'Actualización') !== false || stripos($a['accion'], 'Edición') !== false) {
                $clase = 'actualizacion'; $icon = 'ri-edit-2-fill';
            } elseif (stripos($a['accion'], 'Eliminación') !== false || stripos($a['accion'], 'Bloqueo') !== false || stripos($a['accion'], 'Borrador') !== false) {
                $clase = 'eliminacion'; $icon = 'ri-delete-bin-fill';
            } elseif (stripos($a['accion'], 'Login') !== false || stripos($a['accion'], 'Sesión') !== false) {
                $clase = 'login'; $icon = 'ri-login-circle-fill';
            }
        ?>
        <div class="log-item">
            <div class="log-icon <?php echo $clase; ?>">
                <i class="<?php echo $icon; ?>"></i>
            </div>
            <div class="log-content">
                <div class="log-header">
                    <div class="log-title">
                        <?php echo htmlspecialchars(ucfirst($a['accion'])); ?>
                        <span class="log-id">#<?php echo str_pad($a['id'], 5, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="log-meta">
                        <span class="log-user">
                            <i class="ri-user-smile-fill"></i>
                            <?php echo htmlspecialchars($a['usuario']); ?>
                            <?php if(!empty($a['rol_usuario'])): ?>
                                <span style="opacity: 0.5; margin-left: 4px;">(<?php echo htmlspecialchars($a['rol_usuario']); ?>)</span>
                            <?php endif; ?>
                        </span>
                        <span style="display: flex; align-items: center; gap: 0.3rem;">
                            <i class="ri-time-line"></i>
                            <?php echo date('d M Y, H:i', strtotime($a['fecha_registro'])); ?>
                        </span>
                    </div>
                </div>
                <div class="log-details">
                    <?php echo formatDetalles($a['detalles'] ?? ''); ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($total_pages > 1): ?>
<div style="display:flex; justify-content:center; gap:0.5rem; margin-top:2rem; flex-wrap:wrap; align-items:center;">
    <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>" style="padding:0.5rem 1rem; background:white; border:1px solid var(--border-color); border-radius:var(--radius-md); color:var(--text-main); font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.25rem;"><i class="ri-arrow-left-s-line"></i> Anterior</a>
    <?php endif; ?>
    
    <?php 
    $start_page = max(1, $page - 3);
    $end_page = min($total_pages, $page + 3);
    for ($i = $start_page; $i <= $end_page; $i++): 
    ?>
        <a href="?page=<?php echo $i; ?>" style="padding:0.5rem 0.85rem; background:<?php echo $i === $page ? 'var(--primary-color)' : 'white'; ?>; color:<?php echo $i === $page ? 'white' : 'var(--text-main)'; ?>; border:1px solid <?php echo $i === $page ? 'var(--primary-color)' : 'var(--border-color)'; ?>; border-radius:var(--radius-md); font-weight:600; text-decoration:none;"><?php echo $i; ?></a>
    <?php endfor; ?>
    
    <?php if ($page < $total_pages): ?>
        <a href="?page=<?php echo $page + 1; ?>" style="padding:0.5rem 1rem; background:white; border:1px solid var(--border-color); border-radius:var(--radius-md); color:var(--text-main); font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.25rem;">Siguiente <i class="ri-arrow-right-s-line"></i></a>
    <?php endif; ?>
</div>
<?php endif; ?>
