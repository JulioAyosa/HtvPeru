<?php
// app/Views/admin/comentarios/index.php
// Variables: $msg, $comentarios, $page, $total_pages, $user_role
?>
<style>
    .btn-primary { background-color: var(--primary-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; transition: all 0.2s; }
    .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
    .pagination { display: flex; list-style: none; padding: 0; margin: 1.5rem 0 0 0; gap: 0.25rem; justify-content: center; }
    .pagination li a { padding: 0.5rem 0.85rem; border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: var(--text-main); font-size: 0.875rem; font-weight: 600; transition: all 0.2s; }
    .pagination li a:hover { background-color: #f1f5f9; }
    .pagination li.active a { background-color: var(--primary-color); color: white; border-color: var(--primary-color); }
    
    .table-container { background: white; border-radius: var(--radius-lg); box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; overflow: hidden; margin-top: 1.5rem; }
    .table-container table { width: 100%; border-collapse: collapse; }
    .table-container th { padding: 1rem 1.5rem; color: #475569; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; text-align: left; background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
    .table-container td { padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
    .table-container tr { transition: background 0.2s; }
    .table-container tr:hover { background: #fbfcfd; }
    
    .chk-container { display: flex; align-items: center; justify-content: center; }
    .chk-container input[type="checkbox"] { width: 1.1rem; height: 1.1rem; accent-color: var(--primary-color); cursor: pointer; }
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

<div class="table-container">
    <form method="POST" action="/piura_noticias_php/admin/comentarios/bulk" id="bulk-form">
        <?php echo csrf_field(); ?>
        
        <div style="padding: 1rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display:flex; align-items:center; gap:1rem;">
                <span style="font-weight:700; color:#334155; font-size:0.95rem;"><i class="ri-discuss-line" style="color:var(--primary-color);"></i> Listado de Comentarios</span>
            </div>
            
            <div style="display:flex; align-items:center; gap:0.5rem;">
                <select name="bulk_action" style="padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.85rem; font-weight: 600; outline: none; cursor: pointer; color: #475569;">
                    <option value="">-- Acción por lotes --</option>
                    <option value="aprobar">Aprobar seleccionados</option>
                    <option value="rechazar">Rechazar seleccionados</option>
                    <?php if($user_role === 'admin'): ?>
                    <option value="eliminar">Eliminar seleccionados</option>
                    <?php endif; ?>
                </select>
                <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1rem; font-size: 0.85rem;" onclick="return confirmBulkAction();"><i class="ri-check-double-line"></i> Aplicar</button>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <div class="chk-container">
                                <input type="checkbox" id="select-all" onclick="toggleAll(this)">
                            </div>
                        </th>
                        <th>Fecha</th>
                        <th>Autor / FB</th>
                        <th>Comentario</th>
                        <th>Noticia</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comentarios as $c): ?>
                    <tr>
                        <td style="text-align: center;">
                            <div class="chk-container">
                                <input type="checkbox" name="comment_ids[]" value="<?php echo $c['id']; ?>" class="row-checkbox">
                            </div>
                        </td>
                        <td style="font-size:0.85rem; color:var(--text-muted); white-space: nowrap;">
                            <i class="ri-calendar-line" style="vertical-align:middle; color:#94a3b8;"></i> <?php echo date('d M Y', strtotime($c['fecha'])); ?><br>
                            <i class="ri-time-line" style="vertical-align:middle; color:#94a3b8;"></i> <?php echo date('H:i', strtotime($c['fecha'])); ?>
                        </td>
                        <td>
                            <strong style="color: #1e293b;"><?php echo htmlspecialchars($c['nombre'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></strong><br>
                            <a href="<?php echo htmlspecialchars($c['facebook_url'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>" target="_blank" style="font-size:0.75rem; color:var(--primary-color); display:inline-flex; align-items:center; gap:0.25rem; margin-top:0.25rem; text-decoration:none;"><i class="ri-facebook-circle-fill" style="font-size:1rem;"></i> Ver Perfil</a>
                        </td>
                        <td style="max-width:350px;">
                            <div style="background: #f8fafc; padding: 0.75rem; border-radius: 8px; border: 1px solid #f1f5f9;">
                                <p style="margin:0; font-size:0.9rem; line-height:1.5; color:#334155; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                                    <?php echo htmlspecialchars($c['comentario'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>
                                </p>
                            </div>
                        </td>
                        <td style="max-width: 200px;">
                            <a href="/piura_noticias_php/<?php echo urlencode($c['noticia_slug'] ?? ''); ?>" target="_blank" style="font-size:0.85rem; color:var(--primary-color); text-decoration:none; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; font-weight:600;" title="<?php echo htmlspecialchars($c['noticia_titulo'] ?? '', ENT_QUOTES); ?>">
                                <?php echo htmlspecialchars($c['noticia_titulo'] ?? 'Noticia Eliminada', ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>
                            </a>
                        </td>
                        <td>
                            <span style="display:inline-block; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; <?php 
                                if($c['estado'] === 'Aprobado') echo 'background:#ecfdf5; color:#10b981; border:1px solid #a7f3d0;';
                                elseif($c['estado'] === 'Rechazado') echo 'background:#fef2f2; color:#ef4444; border:1px solid #fecaca;';
                                else echo 'background:#fffbeb; color:#f59e0b; border:1px solid #fde68a;';
                            ?>">
                                <?php echo strtoupper($c['estado'] ?: 'Pendiente'); ?>
                            </span>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: flex; gap: 0.25rem; justify-content: flex-end;">
                                <?php if($c['estado'] !== 'Aprobado'): ?>
                                    <a href="/piura_noticias_php/admin/comentarios/action?action=aprobar&id=<?php echo $c['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" style="background:white; color:#10b981; border:1px solid #a7f3d0; padding:6px; border-radius:6px; cursor:pointer; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; text-decoration:none;" onmouseover="this.style.background='#ecfdf5'" onmouseout="this.style.background='white'" title="Aprobar"><i class="ri-check-line"></i></a>
                                <?php endif; ?>
                                
                                <?php if($c['estado'] !== 'Rechazado'): ?>
                                    <a href="/piura_noticias_php/admin/comentarios/action?action=rechazar&id=<?php echo $c['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" style="background:white; color:#f59e0b; border:1px solid #fde68a; padding:6px; border-radius:6px; cursor:pointer; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; text-decoration:none;" onmouseover="this.style.background='#fffbeb'" onmouseout="this.style.background='white'" title="Rechazar"><i class="ri-close-line"></i></a>
                                <?php endif; ?>
                                
                                <?php if($user_role === 'admin'): ?>
                                    <a href="/piura_noticias_php/admin/comentarios/action?action=eliminar&id=<?php echo $c['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" style="background:white; color:#ef4444; border:1px solid #fecaca; padding:6px; border-radius:6px; cursor:pointer; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; text-decoration:none;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='white'" title="Eliminar" onclick="return confirm('¿Borrar permanentemente este comentario?');"><i class="ri-delete-bin-fill"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($comentarios) === 0): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:4rem 2rem; border:none;">
                            <i class="ri-discuss-line" style="font-size: 3rem; color: #cbd5e1; display: block; margin-bottom: 1rem;"></i>
                            <p style="color: var(--text-muted); margin: 0; font-size: 1.1rem;">No hay comentarios para mostrar.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<script>
function toggleAll(source) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
}

function confirmBulkAction() {
    const action = document.querySelector('select[name="bulk_action"]').value;
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    
    if (!action) {
        alert('Por favor selecciona una acción (Aprobar, Rechazar, Eliminar).');
        return false;
    }
    if (checkboxes.length === 0) {
        alert('Por favor selecciona al menos un comentario.');
        return false;
    }
    
    const count = checkboxes.length;
    let actionText = '';
    if (action === 'aprobar') actionText = 'aprobar';
    else if (action === 'rechazar') actionText = 'rechazar';
    else if (action === 'eliminar') actionText = 'eliminar permanentemente';
    
    return confirm(`¿Estás seguro que deseas ${actionText} los ${count} comentarios seleccionados?`);
}
</script>

<?php if ($total_pages > 1): ?>
    <ul class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="<?php echo ($i === $page) ? 'active' : ''; ?>">
                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
    </ul>
<?php endif; ?>
