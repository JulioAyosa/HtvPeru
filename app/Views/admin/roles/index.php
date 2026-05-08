<?php
// app/Views/admin/roles/index.php
// Variables asumidas: $msg, $roles, $permisos_agrupados, $rol_permisos
?>
<?php if ($msg): ?>
    <div class="alert <?php echo strpos($msg, 'Error') !== false ? 'alert-error' : 'alert-info'; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="admin-header">
    <div>
        <h1 style="margin:0;"><i class="ri-shield-keyhole-line" style="color:var(--primary-color)"></i> Roles y Permisos</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Administra qué pueden y no pueden hacer los usuarios en el sistema.</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('create')"><i class="ri-add-circle-fill"></i> Crear Nuevo Rol</button>
</div>

<div class="cfg-panel" style="padding: 2rem;">
    <h3 style="margin-top:0; margin-bottom:1.5rem; font-size:1.1rem; color:#1e293b; border-bottom:1px solid #e2e8f0; padding-bottom:0.75rem;"><i class="ri-shield-keyhole-line" style="color:var(--primary-color);"></i> Listado de Roles Activos</h3>
    <table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre del Rol</th>
            <th>Descripción</th>
            <th>Nivel</th>
            <th style="text-align:right;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($roles as $r): ?>
        <tr>
            <td><?php echo $r['id']; ?></td>
            <td><strong><?php echo htmlspecialchars($r['nombre']); ?></strong></td>
            <td><?php echo htmlspecialchars($r['descripcion']); ?></td>
            <td>
                <?php if ($r['is_system']): ?>
                    <span style="background:#e0e7ff; color:#4338ca; padding:2px 6px; border-radius:4px; font-size:0.7rem; font-weight:800;">SISTEMA</span>
                <?php else: ?>
                    <span style="background:#f1f5f9; color:#64748b; padding:2px 6px; border-radius:4px; font-size:0.7rem; font-weight:800;">PERSONALIZADO</span>
                <?php endif; ?>
            </td>
            <td style="text-align:right; white-space:nowrap;">
                <?php
                $perms_json = htmlspecialchars(json_encode($rol_permisos[$r['id']] ?? []));
                $rol_json = htmlspecialchars(json_encode($r));
                ?>
                <button class="btn btn-warning btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" onclick="openModal('edit', <?php echo $rol_json; ?>, <?php echo $perms_json; ?>)"><i class="ri-settings-3-line"></i> Configurar Permisos</button>
                
                <?php if (!$r['is_system']): ?>
                    <button class="btn btn-danger btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" onclick="openDeleteModal(<?php echo $r['id']; ?>, '<?php echo htmlspecialchars($r['nombre'], ENT_QUOTES); ?>')"><i class="ri-delete-bin-line"></i></button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    </table>
</div>

<!-- Modal Formulario de Rol -->
<div id="modal-rol" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 1000; justify-content: center; align-items: center; overflow-y: auto; padding: 2rem 0;">
    <div class="cfg-panel" style="width: 100%; max-width: 650px; max-height: 90vh; overflow-y: auto; margin: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.2); padding: 0;">
        <div style="display:flex; justify-content:space-between; align-items: center; position: sticky; top: 0; background: white; padding: 2rem 2.5rem 1.5rem 2.5rem; border-bottom: 1px solid #e2e8f0; z-index: 10; margin-bottom: 1.5rem;">
            <h2 id="modal-title" style="margin:0; font-size: 1.25rem; display:flex; align-items:center; gap:0.5rem;"><i class="ri-shield-keyhole-line" style="color:var(--primary-color);"></i> Nuevo Rol</h2>
            <i class="ri-close-line" style="cursor:pointer; font-size:1.5rem; color:#64748b;" onclick="document.getElementById('modal-rol').style.display='none'"></i>
        </div>
        <form method="POST" action="/piura_noticias_php/admin/roles/action" id="role-form" style="padding: 0 2.5rem 0 2.5rem;">
            <input type="hidden" name="action" id="form-action" value="create_role">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="rol_id" id="form-id" value="">
            
            <div class="form-row">
                <label>Nombre del Rol</label>
                <input type="text" name="nombre" id="form-nombre" required placeholder="Ej: Redactor SEO" class="form-control">
            </div>
            <div class="form-row">
                <label>Descripción</label>
                <textarea name="descripcion" id="form-desc" placeholder="Breve descripción del propósito de este rol..." rows="2" class="form-control"></textarea>
            </div>
            
            <h3 style="margin-top: 1.5rem; margin-bottom: 0.5rem; font-size: 1.1rem; color: #1e293b;"><i class="ri-list-check"></i> Asignación de Permisos</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">Selecciona los módulos y acciones a las que este rol tendrá acceso.</p>
            
            <div class="permisos-grid">
                <?php foreach ($permisos_agrupados as $modulo => $permisos): ?>
                    <div class="modulo-card">
                        <div class="modulo-title"><?php echo htmlspecialchars($modulo); ?></div>
                        <?php foreach ($permisos as $p): ?>
                            <label class="permiso-item" style="display:flex; justify-content:space-between; align-items:center; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 0.5rem; cursor:pointer; background: white; transition: all 0.2s;">
                                <div class="permiso-info">
                                    <span class="permiso-nombre" style="font-weight: 500; color: #1e293b;"><?php echo htmlspecialchars($p['descripcion']); ?></span>
                                </div>
                                <div class="toggle-switch">
                                    <input type="checkbox" name="permisos[]" value="<?php echo $p['id']; ?>" class="permiso-checkbox" id="perm-<?php echo $p['id']; ?>">
                                    <span class="slider"></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="position: sticky; bottom: 0; background: white; padding-top: 1.5rem; padding-bottom: 2rem; margin-top: 1.5rem; border-top: 1px solid #e2e8f0; z-index: 10;">
                <button type="submit" class="btn btn-primary btn-save" style="width:100%; justify-content: center; padding: 0.75rem; font-size: 1.05rem;"><i class="ri-save-3-line"></i> Guardar Configuración de Rol</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Eliminar -->
<div id="modal-delete" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 1000; justify-content: center; align-items: center;">
    <div class="cfg-panel" style="width: 100%; max-width: 400px; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.2); margin: 0;">
        <i class="ri-alert-line" style="font-size: 3.5rem; color: var(--danger); margin-bottom: 1rem; display: block;"></i>
        <h2 style="margin-bottom: 0.5rem; color: #1e293b;">¿Eliminar este Rol?</h2>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Estás a punto de eliminar el rol <strong id="delete-rol-name"></strong>. Esta acción no se puede deshacer.</p>
        
        <form method="POST" action="/piura_noticias_php/admin/roles/action">
            <input type="hidden" name="action" value="delete_role">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="rol_id" id="form-delete-id" value="">
            
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <button type="button" class="btn btn-primary" style="background: #9ca3af; border:none; padding: 0.75rem 1.5rem;" onclick="document.getElementById('modal-delete').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-danger" style="padding: 0.75rem 1.5rem;">Sí, Eliminar Rol</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(mode, data = null, permisos_actuales = []) {
        const modal = document.getElementById('modal-rol');
        const form = document.getElementById('role-form');
        const title = document.getElementById('modal-title');
        const action = document.getElementById('form-action');
        
        form.reset();
        document.querySelectorAll('.permiso-checkbox').forEach(cb => {
            cb.checked = false;
            cb.disabled = false;
        });
        
        if (mode === 'create') {
            title.innerHTML = '<i class="ri-shield-keyhole-line"></i> Crear Nuevo Rol';
            action.value = 'create_role';
            document.getElementById('form-nombre').disabled = false;
        } else if (mode === 'edit' && data) {
            title.innerHTML = '<i class="ri-settings-3-line"></i> Configurar Rol: ' + data.nombre;
            action.value = 'edit_role';
            document.getElementById('form-id').value = data.id;
            document.getElementById('form-nombre').value = data.nombre;
            document.getElementById('form-desc').value = data.descripcion;
            
            if (data.is_system == 1) {
                document.getElementById('form-nombre').readOnly = true;
            } else {
                document.getElementById('form-nombre').readOnly = false;
            }
            
            if (data.id == 1) {
                document.querySelectorAll('.permiso-checkbox').forEach(cb => {
                    cb.checked = true;
                    cb.disabled = true; 
                });
            } else {
                permisos_actuales.forEach(p_id => {
                    const cb = document.getElementById('perm-' + p_id);
                    if(cb) cb.checked = true;
                });
            }
        }
        
        modal.style.display = 'flex';
    }
    
    function openDeleteModal(id, nombre) {
        document.getElementById('form-delete-id').value = id;
        document.getElementById('delete-rol-name').textContent = nombre;
        document.getElementById('modal-delete').style.display = 'flex';
    }
</script>
