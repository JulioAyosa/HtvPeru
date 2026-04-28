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
    .badge-creacion { background: #dcfce7; color: #166534; }
    .badge-actualizacion { background: #e0f2fe; color: #0369a1; }
    .badge-eliminacion { background: #fee2e2; color: #991b1b; }
    .badge-ot { background: #f3f4f6; color: #374151; }
</style>

<div class="admin-header">
    <div>
        <h1 style="margin:0;"><i class="ri-history-line" style="color:var(--primary-color)"></i> Registro de Actividad (Log)</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Auditoría de las últimas acciones realizadas por los usuarios en el CMS.</p>
    </div>
</div>

<div style="margin-bottom: 1.5rem; background: white; padding: 1rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border-left: 4px solid #6366f1; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
    <p style="margin:0; font-size:0.9rem; color:var(--text-muted);"><i class="ri-information-line"></i> Mostrando registros <?php echo $offset + 1; ?>–<?php echo min($offset + $per_page, $total_rows); ?> de <?php echo number_format($total_rows); ?> totales. Página <?php echo $page; ?>/<?php echo $total_pages; ?>.</p>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Fecha y Hora</th>
            <th>Usuario</th>
            <th>Acción</th>
            <th>Detalles</th>
        </tr>
    </thead>
    <tbody>
        <?php if(empty($actividades)): ?>
            <tr><td colspan="5" style="text-align:center;">No hay actividad registrada aún.</td></tr>
        <?php else: ?>
            <?php foreach ($actividades as $a): 
                $clase = 'badge-ot';
                if ($a['accion'] === 'Creación') $clase = 'badge-creacion';
                elseif ($a['accion'] === 'Actualización') $clase = 'badge-actualizacion';
                elseif ($a['accion'] === 'Eliminación' || $a['accion'] === 'Bloqueo') $clase = 'badge-eliminacion';
            ?>
            <tr>
                <td><?php echo $a['id']; ?></td>
                <td style="white-space:nowrap;"><?php echo date('d/m/Y H:i', strtotime($a['fecha_registro'])); ?></td>
                <td><strong><?php echo htmlspecialchars($a['usuario']); ?></strong></td>
                <td><span class="badge <?php echo $clase; ?>" style="padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><?php echo htmlspecialchars($a['accion']); ?></span></td>
                <td><?php echo formatDetalles($a['detalles'] ?? ''); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

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
