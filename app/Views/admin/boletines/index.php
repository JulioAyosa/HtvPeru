<?php
// app/Views/admin/boletines/index.php
// Variables: $msg, $configs, $suscriptores, $total_subs, $historial
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<style>
    .btn-primary { background-color: var(--primary-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: var(--radius-md); cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; }
    table { width: 100%; border-collapse: collapse; background: white; box-shadow: var(--shadow-sm); border-radius: var(--radius-md); overflow: hidden; }
    th { background-color: var(--bg-main); color: var(--text-muted); font-size: 0.875rem; text-transform: uppercase; padding: 1rem; text-align: left; cursor: pointer; transition: color 0.2s; }
    th:hover { color: var(--primary-color); }
    td { padding: 1rem; border-bottom: 1px solid var(--border-color); }
    .alert { background: #dcfce7; color: #166534; padding: 1rem; border-radius: 6px; font-weight: 600; margin-bottom: 1.5rem; border-left: 4px solid #22c55e; }
    
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; padding: 2rem 0; }
    .modal-content { background: white; padding: 2rem; border-radius: var(--radius-md); width: 100%; max-width: 900px; max-height: 95vh; overflow-y: auto; }
    .form-row { margin-bottom: 1rem; }
    .form-row label { display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 0.15rem; color: #475569; }
    .form-row input, .form-row select, .form-row textarea { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; font-family: inherit; box-sizing:border-box;}
    
    .stat-card { background: white; padding: 1.5rem; border-radius: 8px; flex: 1; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); }
    .stat-card h3 { margin: 0 0 0.5rem 0; font-size: 0.9rem; color: var(--text-muted); text-transform: uppercase;}
    .stat-card .value { font-size: 2.5rem; font-weight: 800; color: var(--primary-color); display: flex; align-items: center; gap: 1rem; }
</style>

<div class="admin-header">
    <div>
        <h1 style="margin:0;">Audiencia y Boletines</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Gestiona tu comunidad y envía noticias en masa a los correos registrados.</p>
    </div>
    <button class="btn-primary" onclick="openModal()"><i class="ri-mail-add-fill"></i> Redactar Nuevo Boletín</button>
</div>

<?php if($msg): ?>
    <div class="alert"><i class="ri-check-line"></i> <?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div style="display:flex; gap:2rem; margin-bottom:2rem; flex-wrap:wrap;">
    <div class="stat-card">
        <h3>Total Suscriptores Activos</h3>
        <div class="value"><i class="ri-team-line" style="color:#d1d5db;"></i> <?php echo $total_subs; ?></div>
    </div>
    <div class="stat-card">
        <h3>Tasa de Apertura Promedio</h3>
        <div class="value"><i class="ri-mail-open-line" style="color:#d1d5db;"></i> --%</div>
    </div>
</div>

<div style="display:flex; justify-content:space-between; margin-bottom: 1.5rem; gap:1rem; flex-wrap:wrap; background: white; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
    <div style="display:flex; gap:1rem; flex:1; align-items: center;">
        <div style="position:relative; width:100%; max-width:400px;">
            <i class="ri-search-line" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:#9ca3af;"></i>
            <input type="text" id="searchSuscriptores" placeholder="Buscar suscriptor por correo..." style="padding:0.75rem 1rem 0.75rem 2.5rem; border:1px solid #d1d5db; border-radius:6px; font-family:inherit; width:100%; box-sizing: border-box;" onkeyup="filterTable('suscriptoresTable', 'searchSuscriptores')">
        </div>
    </div>
    <span style="font-size:0.85rem; color:var(--text-muted); align-self:center;"><i class="ri-information-line"></i> Haz clic en las columnas para ordenar asc/desc.</span>
</div>

<div style="overflow-x:auto;">
    <table id="suscriptoresTable">
        <thead>
            <tr>
                <th onclick="sortTable(0, 'suscriptoresTable')">ID <i class="ri-expand-up-down-line" style="font-size:0.75rem; color:#cbd5e1;"></i></th>
                <th onclick="sortTable(1, 'suscriptoresTable')">CORREO ELECTRÓNICO (SUSCRIPTOR ACTIVO) <i class="ri-expand-up-down-line" style="font-size:0.75rem; color:#cbd5e1;"></i></th>
                <th onclick="sortTable(2, 'suscriptoresTable')">FECHA DE INSCRIPCIÓN <i class="ri-expand-up-down-line" style="font-size:0.75rem; color:#cbd5e1;"></i></th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($suscriptores as $s): ?>
            <tr>
                <td style="color:#64748b;">#<?php echo $s['id']; ?></td>
                <td><strong><?php echo htmlspecialchars($s['email']); ?></strong></td>
                <td><?php echo date('d M Y - H:i', strtotime($s['fecha_suscripcion'])); ?></td>
                <td>
                    <form method="POST" action="<?= base_url('/') ?>admin/boletines/action" style="display:inline;" onsubmit="return confirm('¿Eliminar este suscriptor permanentemente?')">
                        <input type="hidden" name="action" value="delete_subscriber">
                        <input type="hidden" name="subscriber_id" value="<?php echo $s['id']; ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" style="background:#fee2e2; color:#b91c1c; border:none; padding:4px 8px; border-radius:4px; cursor:pointer; font-family:inherit;"><i class="ri-user-unfollow-line"></i> Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($suscriptores)): ?>
            <tr><td colspan="4" style="text-align:center; padding: 2rem;">Aún no tienes correos suscritos.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<h2 style="margin-top:3.5rem; margin-bottom:1.5rem; font-size:1.2rem; color:var(--text-main); display:flex; justify-content:space-between; align-items:center;">
    <span><i class="ri-history-line" style="color:var(--primary-color);"></i> Historial de Campañas Enviadas</span>
    
    <div style="position:relative; width:100%; max-width:300px; font-weight:normal;">
        <i class="ri-search-line" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:#9ca3af;"></i>
        <input type="text" id="searchHistorial" placeholder="Buscar boletín..." style="padding:0.6rem 1rem 0.6rem 2.5rem; border:1px solid #d1d5db; border-radius:6px; font-family:inherit; width:100%; box-sizing: border-box; font-size: 0.9rem;" onkeyup="filterTable('historialTable', 'searchHistorial')">
    </div>
</h2>
<div style="overflow-x:auto;">
    <table id="historialTable">
        <thead>
            <tr>
                <th onclick="sortTable(0, 'historialTable')">ID <i class="ri-expand-up-down-line" style="font-size:0.75rem; color:#cbd5e1;"></i></th>
                <th onclick="sortTable(1, 'historialTable')">ASUNTO DEL BOLETÍN <i class="ri-expand-up-down-line" style="font-size:0.75rem; color:#cbd5e1;"></i></th>
                <th onclick="sortTable(2, 'historialTable')">FECHA DE ENVÍO <i class="ri-expand-up-down-line" style="font-size:0.75rem; color:#cbd5e1;"></i></th>
                <th onclick="sortTable(3, 'historialTable')">ENTREGADOS <i class="ri-expand-up-down-line" style="font-size:0.75rem; color:#cbd5e1;"></i></th>
                <th style="text-align:center;">DETALLES</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($historial as $h): ?>
            <tr>
                <td style="color:#64748b;">#<?php echo $h['id']; ?></td>
                <td><strong><?php echo htmlspecialchars($h['asunto']); ?></strong></td>
                <td><?php echo date('d M Y - H:i', strtotime($h['fecha_envio'])); ?></td>
                <td><span style="background:#dcfce7; color:#166534; padding:3px 8px; border-radius:12px; font-weight:700; font-size:0.85rem;"><i class="ri-check-double-line"></i> <?php echo $h['total_enviados']; ?> correos</span></td>
                <td style="text-align:center;">
                    <button onclick="viewDestinatarios(<?php echo $h['id']; ?>)" style="background:#3b82f6; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; font-size:0.85rem;"><i class="ri-eye-line"></i> Ver Destinatarios</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($historial)): ?>
            <tr><td colspan="5" style="text-align:center; padding: 2rem; color:var(--text-muted);">Aún no has enviado ningún boletín masivo.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- MODAL BOLETIN -->
<div id="modal" class="modal">
    <div class="modal-content">
        <div style="display:flex; justify-content:space-between; margin-bottom:1rem; border-bottom:1px solid #e5e7eb; padding-bottom:1rem;">
            <h2 id="modal-title" style="margin:0;"><i class="ri-mail-send-fill" style="color:var(--primary-color);"></i> Difusión Masiva (Newsletter)</h2>
            <i class="ri-close-line" style="cursor:pointer; font-size:1.5rem;" onclick="document.getElementById('modal').style.display='none'"></i>
        </div>
        
        <form method="POST" action="<?= base_url('/') ?>admin/boletines/action">
            <input type="hidden" name="action" value="send_newsletter">
            <?php echo csrf_field(); ?>
            
            <div style="background:#ecfdf5; padding: 1rem; border-radius:6px; margin-bottom:1rem; border-left:4px solid #10b981; font-size:0.85rem; color:#065f46;">
                <strong>Destino de difusión:</strong> Este mensaje se enviará silenciosa y automáticamente a los <?php echo $total_subs; ?> suscriptores de la base de datos.
            </div>

            <div class="form-row">
                <label>Asunto del Correo (Titular Atractivo)</label>
                <input type="text" name="subject" required placeholder="Ej: ÚLTIMO MINUTO: Accidente en vía principal...">
            </div>
            
            <div class="form-row">
                <label>Contenido del Boletín</label>
                <textarea name="contenido" id="contenido_editor" rows="15"></textarea>
            </div>
            
            <div style="text-align:right; margin-top: 1rem;">
                <button type="submit" class="btn-primary" style="padding:1rem 2rem; font-size:1.1rem; background:#10b981;"><i class="ri-send-plane-fill"></i> Lanzar Boletín Masivo</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DESTINATARIOS -->
<div id="modal-dest" class="modal">
    <div class="modal-content" style="max-width:600px;">
        <div style="display:flex; justify-content:space-between; margin-bottom:1rem; border-bottom:1px solid #e5e7eb; padding-bottom:1rem;">
            <h2 style="margin:0;"><i class="ri-group-line" style="color:var(--primary-color);"></i> Lista de Destinatarios Exacta</h2>
            <i class="ri-close-line" style="cursor:pointer; font-size:1.5rem;" onclick="document.getElementById('modal-dest').style.display='none'"></i>
        </div>
        
        <div id="modal-dest-content" style="max-height: 400px; overflow-y:auto; border: 1px solid #e5e7eb; border-radius:6px; padding:0;">
            <div style="text-align:center; padding:2rem;"><i class="ri-loader-4-line ri-spin"></i> Cargando...</div>
        </div>
    </div>
</div>

<script>
    tinymce.init({
        selector: '#contenido_editor',
        language: 'es',
        menubar: false,
        plugins: 'lists link image media preview code table wordcount',
        toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright | link image media | code',
        setup: function (editor) {
            editor.on('change', function () {
                tinymce.triggerSave();
            });
        }
    });

    function openModal() {
        if (tinymce.get('contenido_editor') && tinymce.get('contenido_editor').getContent() === '') {
            tinymce.get('contenido_editor').setContent('<h2 style="text-align: center;">HTV PERU RESUMEN DE HOY</h2><p>Hola, aquí te traemos las noticias más relevantes de la región.</p><ul><li>Titular 1: ... <a href="#">[Leer más]</a></li></ul>');
        }
        document.getElementById('modal').style.display = 'flex';
    }

    async function viewDestinatarios(historialId) {
        document.getElementById('modal-dest').style.display = 'flex';
        document.getElementById('modal-dest-content').innerHTML = '<div style="text-align:center; padding:2rem;"><i class="ri-loader-4-line ri-spin"></i> Cargando...</div>';
        
        try {
            let response = await fetch('<?= base_url('/') ?>api/admin/boletin_destinatarios?id=' + historialId);
            let html = await response.text();
            document.getElementById('modal-dest-content').innerHTML = html;
        } catch(e) {
            document.getElementById('modal-dest-content').innerHTML = '<div style="padding:2rem; color:red;">Error de conexión.</div>';
        }
    }

    // --- SISTEMA DE BÚSQUEDA Y ORDENAMIENTO (Nativo Vanilla JS) ---

    function filterTable(tableId, inputId) {
        const textFilter = document.getElementById(inputId).value.toLowerCase();
        const table = document.getElementById(tableId);
        const tbody = table.querySelector("tbody");
        const rows = document.querySelectorAll("#" + tableId + " tbody tr");
        
        rows.forEach(row => {
            if(row.cells.length === 1) return; // Omite filas de mensaje vacío
            const textContent = row.innerText.toLowerCase();
            if (textContent.includes(textFilter)) row.style.display = "";
            else row.style.display = "none";
        });
    }
    
    let sortDirection = false;
    function sortTable(columnIndex, tableId) {
        const table = document.getElementById(tableId);
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));
        if(rows.length === 0 || rows[0].cells.length === 1) return;
        
        sortDirection = !sortDirection;
        
        rows.sort((a, b) => {
            let aText = a.cells[columnIndex].innerText.trim();
            let bText = b.cells[columnIndex].innerText.trim();
            
            return sortDirection 
                ? aText.localeCompare(bText, undefined, { numeric: true, sensitivity: 'base' }) 
                : bText.localeCompare(aText, undefined, { numeric: true, sensitivity: 'base' });
        });
        
        tbody.innerHTML = "";
        rows.forEach(row => tbody.appendChild(row));
    }
</script>
