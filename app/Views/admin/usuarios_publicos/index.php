<?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="admin-header" style="margin-bottom: 2rem;">
    <div>
        <h1 style="margin-top:0; margin-bottom: 0.5rem; color: #0f172a;"><i class="ri-team-line" style="color:var(--primary-color)"></i> Usuarios Públicos (OAuth)</h1>
        <p style="color: var(--text-muted); margin:0;">Gestión de lectores registrados mediante Google o Facebook para comentar.</p>
    </div>
</div>

<!-- Buscador -->
<div style="background: white; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); margin-bottom: 1.5rem;">
    <form method="GET" action="<?= base_url('/') ?>admin/usuarios-publicos" style="display: flex; gap: 1rem; max-width: 500px;">
        <input type="text" name="search" placeholder="Buscar por nombre o correo..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 6px; font-family: var(--font-sans);">
        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;"><i class="ri-search-line"></i> Buscar</button>
        <?php if ($search): ?>
            <a href="<?= base_url('/') ?>admin/usuarios-publicos" class="btn btn-secondary" style="padding: 0.75rem 1rem;"><i class="ri-close-line"></i> Limpiar</a>
        <?php endif; ?>
    </form>
</div>

<!-- Tabla -->
<div style="background: white; border-radius: var(--radius-md); border: 1px solid var(--border-color); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 2rem;">
    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
            <tr>
                <th style="padding: 1rem; color: #475569; font-weight: 600;">USUARIO</th>
                <th style="padding: 1rem; color: #475569; font-weight: 600;">PROVEEDOR</th>
                <th style="padding: 1rem; color: #475569; font-weight: 600; text-align: center;">COMENTARIOS</th>
                <th style="padding: 1rem; color: #475569; font-weight: 600;">FECHA REGISTRO</th>
                <th style="padding: 1rem; color: #475569; font-weight: 600;">ESTADO</th>
                <th style="padding: 1rem; color: #475569; font-weight: 600; text-align: right;">ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($usuarios) > 0): ?>
                <?php foreach ($usuarios as $u): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 1rem; display: flex; align-items: center; gap: 1rem;">
                            <img src="<?php echo htmlspecialchars($u['avatar_url'] ?: '<?= base_url('/') ?>img/default_avatar.png'); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            <div>
                                <strong style="display: block; color: #1e293b;"><?php echo htmlspecialchars($u['nombre']); ?></strong>
                                <span style="font-size: 0.8rem; color: #64748b;"><?php echo htmlspecialchars($u['email']); ?></span>
                            </div>
                        </td>
                        <td style="padding: 1rem;">
                            <?php if ($u['proveedor'] === 'google'): ?>
                                <span style="display: inline-flex; align-items: center; gap: 4px; color: #dc2626; font-weight: 600; font-size: 0.85rem;"><i class="ri-google-fill"></i> Google</span>
                            <?php elseif ($u['proveedor'] === 'facebook'): ?>
                                <span style="display: inline-flex; align-items: center; gap: 4px; color: #2563eb; font-weight: 600; font-size: 0.85rem;"><i class="ri-facebook-circle-fill"></i> Facebook</span>
                            <?php else: ?>
                                <span style="color: #64748b; font-weight: 600; font-size: 0.85rem;"><i class="ri-global-line"></i> Local</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            <span style="background: #f1f5f9; padding: 4px 10px; border-radius: 12px; font-weight: bold; color: #475569;"><?php echo $u['total_comentarios']; ?></span>
                        </td>
                        <td style="padding: 1rem; color: #475569;">
                            <?php echo date('d/m/Y H:i', strtotime($u['fecha_registro'])); ?>
                        </td>
                        <td style="padding: 1rem;">
                            <?php if ($u['estado'] === 'activo'): ?>
                                <span style="background: #dcfce7; color: #16a34a; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><i class="ri-check-line"></i> Activo</span>
                            <?php else: ?>
                                <span style="background: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><i class="ri-forbid-line"></i> Bloqueado</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem; text-align: right;">
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <?php if ($u['estado'] === 'activo'): ?>
                                    <button onclick="confirmToggle(<?php echo $u['id']; ?>, 'bloquear', '<?php echo htmlspecialchars(addslashes($u['nombre'])); ?>')" class="btn btn-danger" style="padding: 4px 10px; font-size: 0.8rem;"><i class="ri-lock-line"></i> Bloquear</button>
                                <?php else: ?>
                                    <button onclick="confirmToggle(<?php echo $u['id']; ?>, 'activar', '<?php echo htmlspecialchars(addslashes($u['nombre'])); ?>')" class="btn btn-success" style="padding: 4px 10px; font-size: 0.8rem;"><i class="ri-unlock-line"></i> Activar</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="font-size: 0.75rem; color: #94a3b8;">Solo Admin</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="padding: 3rem; text-align: center; color: #94a3b8;">
                        <i class="ri-user-unfollow-line" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        No se encontraron usuarios públicos.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginación -->
<?php if (isset($total_pages) && $total_pages > 1): ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
    <div style="font-size: 0.85rem; color: var(--text-muted);">
        Mostrando pág <?php echo $page; ?> de <?php echo $total_pages; ?> (<?php echo $total_usuarios; ?> usuarios)
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem;"><i class="ri-arrow-left-s-line"></i> Anterior</a>
        <?php else: ?>
            <button disabled class="btn btn-secondary" style="padding: 0.5rem 1rem; opacity: 0.5; cursor: not-allowed;"><i class="ri-arrow-left-s-line"></i> Anterior</button>
        <?php endif; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Siguiente <i class="ri-arrow-right-s-line"></i></a>
        <?php else: ?>
            <button disabled class="btn btn-secondary" style="padding: 0.5rem 1rem; opacity: 0.5; cursor: not-allowed;">Siguiente <i class="ri-arrow-right-s-line"></i></button>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Modal Confirmación -->
<div id="confirmModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; padding: 1rem;">
    <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 400px; width: 100%; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <i id="confirmIcon" class="ri-error-warning-line" style="font-size: 3rem; color: var(--danger); margin-bottom: 1rem; display: block;"></i>
        <h3 id="confirmTitle" style="margin-top: 0; color: #1e293b;">Confirmar Acción</h3>
        <p id="confirmMessage" style="color: #475569; margin-bottom: 1.5rem;">¿Estás seguro de continuar?</p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <button onclick="closeConfirmModal()" class="btn btn-secondary" style="padding: 0.75rem 1.5rem;">Cancelar</button>
            <a id="confirmBtn" href="#" class="btn btn-danger" style="padding: 0.75rem 1.5rem;">Continuar</a>
        </div>
    </div>
</div>

<script>
function confirmToggle(id, accion, nombre) {
    const modal = document.getElementById('confirmModal');
    const title = document.getElementById('confirmTitle');
    const msg = document.getElementById('confirmMessage');
    const btn = document.getElementById('confirmBtn');
    const icon = document.getElementById('confirmIcon');
    
    if (accion === 'bloquear') {
        title.innerText = 'Bloquear Usuario';
        msg.innerHTML = `Al bloquear a <strong>${nombre}</strong>, no podrá dejar más comentarios con su cuenta OAuth en ninguna noticia. ¿Confirmar bloqueo?`;
        btn.innerText = 'Sí, Bloquear';
        btn.className = 'btn btn-danger';
        icon.className = 'ri-lock-line';
        icon.style.color = 'var(--danger)';
    } else {
        title.innerText = 'Activar Usuario';
        msg.innerHTML = `<strong>${nombre}</strong> podrá volver a comentar. ¿Confirmar activación?`;
        btn.innerText = 'Sí, Activar';
        btn.className = 'btn btn-success';
        icon.className = 'ri-unlock-line';
        icon.style.color = '#10b981';
    }
    
    btn.href = `<?= base_url('/') ?>admin/usuarios-publicos/toggle?id=${id}`;
    modal.style.display = 'flex';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}
</script>
