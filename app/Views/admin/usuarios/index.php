<?php
// app/Views/admin/usuarios/index.php
// Variables asumidas: $msg, $usuarios, $roles_disponibles, $user_id
?>
<?php if ($msg): ?>
    <div class="alert <?php echo strpos($msg, 'Error') !== false ? 'alert-error' : 'alert-info'; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="admin-header">
    <div>
        <h1 style="margin:0;"><i class="ri-group-line" style="color:var(--primary-color)"></i> Gestión de Usuarios</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Administra accesos y perfiles del portal.</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('create')"><i class="ri-user-add-line"></i> Nuevo Usuario</button>
</div>

<!-- Buscador y Filtros -->
<div style="display:flex; justify-content:space-between; margin-bottom: 1.5rem; gap:1rem; flex-wrap:wrap; background: white; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
    <div style="display:flex; gap:1rem; flex:1; align-items: center;">
        <div style="position:relative; width:100%; max-width:400px;">
            <i class="ri-search-line" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:#9ca3af;"></i>
            <input type="text" id="searchUsers" placeholder="Buscar usuario..." style="padding:0.75rem 1rem 0.75rem 2.5rem; border:1px solid #d1d5db; border-radius:6px; font-family:var(--font-sans); width:100%; box-sizing: border-box;" onkeyup="filterUsersTable()">
        </div>
    </div>
    <span style="font-size:0.85rem; color:var(--text-muted); align-self:center;"><i class="ri-information-line"></i> Haz clic en las columnas para ordenar asc/desc.</span>
</div>

<table id="usersTable">
    <thead>
        <tr>
            <th style="cursor:pointer;" onclick="sortTable(0)">ID <i class="ri-expand-up-down-line" style="font-size:0.75rem; color:#cbd5e1;"></i></th>
            <th style="cursor:pointer;" onclick="sortTable(1)">Nombre <i class="ri-expand-up-down-line" style="font-size:0.75rem; color:#cbd5e1;"></i></th>
            <th style="cursor:pointer;" onclick="sortTable(2)">Email <i class="ri-expand-up-down-line" style="font-size:0.75rem; color:#cbd5e1;"></i></th>
            <th style="cursor:pointer;" onclick="sortTable(3)">Rol <i class="ri-expand-up-down-line" style="font-size:0.75rem; color:#cbd5e1;"></i></th>
            <th style="cursor:pointer;" onclick="sortTable(4)">Fecha Creación <i class="ri-expand-up-down-line" style="font-size:0.75rem; color:#cbd5e1;"></i></th>
            <th style="text-align:right;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $u): ?>
        <tr data-date="<?php echo date('Y-m-d', strtotime($u['fecha_creacion'])); ?>">
            <td><?php echo $u['id']; ?></td>
            <td><strong><?php echo htmlspecialchars($u['nombre_completo']); ?></strong></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td>
                <?php if ($u['rol_id'] == 1): ?>
                    <span style="background:var(--primary-color); color:white; padding:2px 6px; border-radius:4px; font-size:0.7rem; font-weight:800;"><?php echo strtoupper($u['rol_nombre'] ?? 'Administrador'); ?></span>
                <?php elseif ($u['rol_id'] == 2): ?>
                    <span style="background:linear-gradient(90deg,#7c3aed,#a855f7); color:white; padding:2px 6px; border-radius:4px; font-size:0.7rem; font-weight:800;"><?php echo strtoupper($u['rol_nombre'] ?? 'Gerente'); ?></span>
                <?php else: ?>
                    <span style="background:#e5e7eb; color:var(--text-muted); padding:2px 6px; border-radius:4px; font-size:0.7rem; font-weight:800;"><?php echo strtoupper($u['rol_nombre'] ?? 'Personalizado'); ?></span>
                <?php endif; ?>
            </td>
            <td><?php echo date('d/m/Y', strtotime($u['fecha_creacion'])); ?></td>
            <td style="text-align:right; white-space:nowrap;">
                <?php 
                $can_edit = true;
                if ($u['id'] == 1 && $user_id != 1) $can_edit = false;
                ?>
                
                <?php if ($can_edit): ?>
                    <button class="btn btn-warning btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" onclick="openModal('edit', <?php echo htmlspecialchars(json_encode($u)); ?>)"><i class="ri-edit-box-line"></i> Editar</button>
                <?php endif; ?>
                
                <?php if ($u['id'] !== $user_id && $u['id'] != 1): ?>
                    <?php if (isset($u['estado']) && $u['estado'] === 'bloqueado'): ?>
                        <a href="<?= base_url('/') ?>admin/usuarios/action?unblock=<?php echo $u['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" class="btn btn-primary btn-sm" style="background:#10b981; border:none; padding: 0.25rem 0.5rem; font-size: 0.75rem;"><i class="ri-lock-unlock-line"></i> Desbloquear</a>
                    <?php else: ?>
                        <button class="btn btn-danger btn-sm" style="background:#4b5563; padding: 0.25rem 0.5rem; font-size: 0.75rem;" onclick="openBlockModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['nombre_completo'], ENT_QUOTES); ?>')"><i class="ri-lock-line"></i> Bloquear</button>
                    <?php endif; ?>
                    <button class="btn btn-danger btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" onclick="openDeleteModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['nombre_completo'], ENT_QUOTES); ?>')"><i class="ri-delete-bin-line"></i> Eliminar</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Modal Formulario de Usuario -->
<div id="modal-usuario" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2.5rem; border-radius: var(--radius-lg); width: 100%; max-width: 450px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem;">
            <h2 id="modal-title" style="margin:0;"><i class="ri-user-add-line"></i> Nuevo Usuario</h2>
            <i class="ri-close-line" style="cursor:pointer; font-size:1.5rem;" onclick="document.getElementById('modal-usuario').style.display='none'"></i>
        </div>
        <form method="POST" action="<?= base_url('/') ?>admin/usuarios/action" id="user-form">
            <input type="hidden" name="action" id="form-action" value="create">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="user_id" id="form-id" value="">
            
            <div class="form-row">
                <label>Nombre Completo</label>
                <input type="text" name="nombre_completo" id="form-nombre" required class="form-control">
            </div>
            <div class="form-row">
                <label>Correo Electrónico</label>
                <input type="email" name="email" id="form-email" required class="form-control">
            </div>
            <div class="form-row" id="rol-container">
                <label>Rol de Usuario</label>
                <select name="rol_id" id="form-rol" required class="form-control">
                    <?php foreach ($roles_disponibles as $rd): ?>
                        <option value="<?php echo $rd['id']; ?>"><?php echo htmlspecialchars($rd['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label>Contraseña <span id="pass-hint" style="font-weight:normal; font-size:0.75rem; color:var(--text-muted);">(Requerida)</span></label>
                <input type="password" name="password" id="form-password" required class="form-control">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;"><i class="ri-save-3-line"></i> Guardar Usuario</button>
        </form>
    </div>
</div>

<!-- Modal Bloqueo -->
<div id="modal-bloqueo" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2.5rem; border-radius: var(--radius-lg); width: 100%; max-width: 450px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem;">
            <h2 id="modal-block-title" style="margin:0; color: #4b5563;"><i class="ri-lock-line"></i> Bloquear Usuario</h2>
            <i class="ri-close-line" style="cursor:pointer; font-size:1.5rem;" onclick="document.getElementById('modal-bloqueo').style.display='none'"></i>
        </div>
        <form method="POST" action="<?= base_url('/') ?>admin/usuarios/action">
            <input type="hidden" name="action" value="block">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="block_user_id" id="form-block-id" value="">
            
            <p style="margin-bottom: 1.5rem; color: var(--text-muted); font-size: 0.95rem;">
                Estás a punto de bloquear al usuario <strong id="block-user-name"></strong>. No podrá acceder al sistema hasta que sea desbloqueado.
            </p>
            
            <div class="form-row">
                <label>Motivo de bloqueo</label>
                <textarea name="motivo_bloqueo" required placeholder="Ecribe el motivo o razón del bloqueo..." style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-family: inherit; resize: vertical; min-height: 80px; box-sizing: border-box;"></textarea>
            </div>
            
            <div class="form-row" style="margin-bottom: 1.5rem;">
                <label style="display: flex; gap: 0.5rem; align-items: center; cursor: pointer; font-weight: normal; margin-bottom: 0;">
                    <input type="checkbox" required> Confirmo que deseo bloquear este acceso.
                </label>
            </div>
            
            <button type="submit" class="btn btn-danger" style="width:100%; padding: 0.75rem; font-size: 1rem;"><i class="ri-lock-line"></i> Aceptar y Bloquear</button>
        </form>
    </div>
</div>

<!-- Modal Eliminar y Reasignar -->
<div id="modal-delete" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2.5rem; border-radius: var(--radius-lg); width: 100%; max-width: 500px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem;">
            <h2 id="modal-delete-title" style="margin:0; color: var(--danger);"><i class="ri-delete-bin-line"></i> Eliminar Usuario</h2>
            <i class="ri-close-line" style="cursor:pointer; font-size:1.5rem;" onclick="document.getElementById('modal-delete').style.display='none'"></i>
        </div>
        <form method="POST" action="<?= base_url('/') ?>admin/usuarios/action">
            <input type="hidden" name="action" value="delete">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="delete_user_id" id="form-delete-id" value="">
            
            <div class="alert alert-error" style="margin-bottom:1.5rem; font-size:0.9rem;">
                <strong>Atención:</strong> Vas a eliminar a <span id="delete-user-name" style="font-weight:800;"></span>. Si procedes, el sistema te permite reasignar sus noticias publicadas a otra persona. Si seleccionas "Eliminar Todo", también se borrarán todas las notas que escribió.
            </div>
            
            <div class="form-row">
                <label>¿Qué hacer con su contenido?</label>
                <select name="reassign_user_id" id="form-reassign-id" required class="form-control">
                    <option value="0">Eliminar todas sus noticias permanentemente</option>
                    <optgroup label="Transferir el contenido a:">
                        <?php foreach ($usuarios as $u): ?>
                            <?php if ($u['id'] !== $user_id && $u['id'] != 1): ?>
                            <option class="reassign-option" value="<?php echo $u['id']; ?>" data-id="<?php echo $u['id']; ?>">Reasignar a <?php echo htmlspecialchars($u['nombre_completo']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>
            
            <div class="form-row" style="margin-bottom: 1.5rem;">
                <label style="display: flex; gap: 0.5rem; align-items: center; cursor: pointer; font-weight: normal; margin-bottom: 0;">
                    <input type="checkbox" required> Confirmo la eliminación definitiva de este usuario.
                </label>
            </div>
            
            <button type="submit" class="btn btn-danger" style="width:100%; padding: 0.75rem; font-size: 1rem;"><i class="ri-delete-bin-line"></i> Sí, Ejecutar Acción</button>
        </form>
    </div>
</div>

<script>
    function filterUsersTable() {
        const textFilter = document.getElementById("searchUsers").value.toLowerCase();
        const rows = document.querySelectorAll("#usersTable tbody tr");
        rows.forEach(row => {
            if(row.cells.length === 1) return;
            const textContent = row.innerText.toLowerCase();
            row.style.display = textContent.includes(textFilter) ? "" : "none";
        });
    }

    let sortDirection = false;
    function sortTable(columnIndex) {
        const table = document.getElementById("usersTable");
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));
        if(rows.length === 0 || rows[0].cells.length === 1) return;
        
        sortDirection = !sortDirection;
        
        rows.sort((a, b) => {
            let aText = a.cells[columnIndex].innerText.trim();
            let bText = b.cells[columnIndex].innerText.trim();
            
            if (columnIndex === 0) { // IDs
                return sortDirection ? parseInt(aText) - parseInt(bText) : parseInt(bText) - parseInt(aText);
            }
            if (columnIndex === 4) { // Fechas
                let aDate = a.getAttribute("data-date") || "";
                let bDate = b.getAttribute("data-date") || "";
                return sortDirection ? aDate.localeCompare(bDate) : bDate.localeCompare(aDate);
            }
            return sortDirection ? aText.localeCompare(bText) : bText.localeCompare(aText);
        });
        
        tbody.innerHTML = "";
        rows.forEach(row => tbody.appendChild(row));
    }

    function openModal(mode, data = null) {
        const modal = document.getElementById('modal-usuario');
        const form = document.getElementById('user-form');
        const title = document.getElementById('modal-title');
        const action = document.getElementById('form-action');
        const passHint = document.getElementById('pass-hint');
        const passInput = document.getElementById('form-password');
        const rolContainer = document.getElementById('rol-container');
        const rolSelect = document.getElementById('form-rol');
        
        form.reset();
        
        if (mode === 'create') {
            title.innerHTML = '<i class="ri-user-add-line"></i> Nuevo Usuario';
            action.value = 'create';
            passHint.textContent = '(Requerida)';
            passInput.required = true;
            rolContainer.style.display = 'block';
        } else if (mode === 'edit' && data) {
            title.innerHTML = '<i class="ri-edit-box-line"></i> Editar Usuario';
            action.value = 'edit';
            document.getElementById('form-id').value = data.id;
            document.getElementById('form-nombre').value = data.nombre_completo;
            document.getElementById('form-email').value = data.email;
            if(data.rol_id) rolSelect.value = data.rol_id;
            
            if (data.id == 1) {
                rolContainer.style.display = 'none';
            } else {
                rolContainer.style.display = 'block';
            }
            
            passHint.textContent = '(Opcional: Déjalo en blanco para no cambiarla)';
            passInput.required = false;
        }
        
        modal.style.display = 'flex';
    }
    
    function openBlockModal(id, nombre) {
        document.getElementById('form-block-id').value = id;
        document.getElementById('block-user-name').textContent = nombre;
        document.getElementById('modal-bloqueo').style.display = 'flex';
    }
    
    function openDeleteModal(id, nombre) {
        document.getElementById('form-delete-id').value = id;
        document.getElementById('delete-user-name').textContent = nombre;
        
        const options = document.querySelectorAll('.reassign-option');
        options.forEach(opt => {
            if(parseInt(opt.getAttribute('data-id')) === id) {
                opt.style.display = 'none';
                opt.disabled = true;
            } else {
                opt.style.display = '';
                opt.disabled = false;
            }
        });
        
        document.getElementById('modal-delete').style.display = 'flex';
    }
</script>
