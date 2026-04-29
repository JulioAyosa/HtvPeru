<?php
// app/Views/admin/comentarios/index.php
// Variables: $msg, $comentarios, $page, $total_pages, $user_role
?>
<style>
    .btn-primary { background-color: var(--primary-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: var(--radius-md); cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; }
    table { width: 100%; border-collapse: collapse; background: white; box-shadow: var(--shadow-sm); border-radius: var(--radius-md); overflow: hidden; }
    th { background-color: var(--bg-main); color: var(--text-muted); font-size: 0.875rem; text-transform: uppercase; padding: 1rem; text-align: left; }
    td { padding: 0.5rem 1rem; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
    .alert { background: #dcfce7; color: #166534; padding: 1rem; border-radius: 6px; font-weight: 600; margin-bottom: 1.5rem; border-left: 4px solid #22c55e; }
    
    .pagination { display: flex; list-style: none; padding: 0; margin: 1rem 0 0 0; gap: 0.25rem; }
    .pagination li a { padding: 0.5rem 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); text-decoration: none; color: var(--text-main); font-size: 0.875rem; }
    .pagination li a:hover { background-color: var(--bg-main); }
    .pagination li.active a { background-color: var(--primary-color); color: white; border-color: var(--primary-color); }
</style>

<div class="admin-header">
    <div>
        <h1 style="margin:0;">Moderación de Comentarios</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Visualiza, aprueba o elimina los comentarios realizados por los usuarios.</p>
    </div>
</div>

<?php if($msg): ?>
    <div class="alert"><i class="ri-check-line"></i> <?php echo $msg; ?></div>
<?php endif; ?>

<div style="background:var(--bg-main); padding: 1rem; border: 1px solid var(--border-color); border-radius: 6px 6px 0 0; display:flex; justify-content:space-between; align-items:center;">
    <span style="font-weight:600;"><i class="ri-discuss-line"></i> Listado de Comentarios</span>
</div>
<div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Autor / FB</th>
                <th>Comentario</th>
                <th>Noticia</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comentarios as $c): ?>
            <tr>
                <td style="font-size:0.85rem; color:var(--text-muted);"><?php echo date('d/m/Y H:i', strtotime($c['fecha'])); ?></td>
                <td>
                    <strong><?php echo htmlspecialchars($c['nombre'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></strong><br>
                    <a href="<?php echo htmlspecialchars($c['facebook_url'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>" target="_blank" style="font-size:0.75rem; color:var(--primary-color);"><i class="ri-facebook-circle-fill"></i> Ver Perfil</a>
                </td>
                <td style="max-width:300px;">
                    <p style="margin:0; font-size:0.9rem; line-height:1.4; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                        <?php echo htmlspecialchars($c['comentario'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>
                    </p>
                </td>
                <td>
                    <a href="<?= base_url('/') ?>article.php?slug=<?php echo urlencode($c['noticia_slug'] ?? ''); ?>" target="_blank" style="font-size:0.85rem; color:var(--primary-color);">
                        <?php echo htmlspecialchars($c['noticia_titulo'] ?? 'Noticia Eliminada', ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>
                    </a>
                </td>
                <td>
                    <?php if($c['estado'] === 'Aprobado'): ?>
                        <span style="background:#10b981; color:white; padding:4px 8px; border-radius:4px; font-size:0.75rem; font-weight:bold;">Aprobado</span>
                    <?php elseif($c['estado'] === 'Rechazado'): ?>
                        <span style="background:#ef4444; color:white; padding:4px 8px; border-radius:4px; font-size:0.75rem; font-weight:bold;">Rechazado</span>
                    <?php else: ?>
                        <span style="background:#f59e0b; color:white; padding:4px 8px; border-radius:4px; font-size:0.75rem; font-weight:bold;">Pendiente</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($c['estado'] !== 'Aprobado'): ?>
                        <a href="<?= base_url('/') ?>admin/comentarios/action?action=aprobar&id=<?php echo $c['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" class="btn-primary" style="padding:4px 8px; font-size:0.8rem; background:#10b981; margin-right:4px;" title="Aprobar"><i class="ri-check-line"></i></a>
                    <?php endif; ?>
                    
                    <?php if($c['estado'] !== 'Rechazado'): ?>
                        <a href="<?= base_url('/') ?>admin/comentarios/action?action=rechazar&id=<?php echo $c['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" class="btn-primary" style="padding:4px 8px; font-size:0.8rem; background:#f59e0b; margin-right:4px;" title="Rechazar"><i class="ri-close-line"></i></a>
                    <?php endif; ?>
                    
                    <?php if($user_role === 'admin'): ?>
                        <a href="<?= base_url('/') ?>admin/comentarios/action?action=eliminar&id=<?php echo $c['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" class="btn-primary" style="padding:4px 8px; font-size:0.8rem; background:#ef4444;" title="Eliminar" onclick="return confirm('¿Seguro de borrar este comentario?');"><i class="ri-delete-bin-line"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(count($comentarios) === 0): ?>
            <tr>
                <td colspan="6" style="text-align:center; padding:2rem; color:var(--text-muted);">No hay comentarios aún.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total_pages > 1): ?>
    <ul class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="<?php echo ($i === $page) ? 'active' : ''; ?>">
                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
    </ul>
<?php endif; ?>
