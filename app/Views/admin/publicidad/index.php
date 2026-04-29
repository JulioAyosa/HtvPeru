<?php
// app/Views/admin/publicidad/index.php
// Variables: $ads, $msg
?>
<style>
    .btn-primary { background-color: var(--primary-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: var(--radius-md); cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; }
    table { width: 100%; border-collapse: collapse; background: white; box-shadow: var(--shadow-sm); border-radius: var(--radius-md); overflow: hidden; }
    th { background-color: var(--bg-main); color: var(--text-muted); font-size: 0.875rem; text-transform: uppercase; padding: 1rem; text-align: left; }
    td { padding: 1rem; border-bottom: 1px solid var(--border-color); }
    .alert { background: #dcfce7; color: #166534; padding: 1rem; border-radius: 6px; font-weight: 600; margin-bottom: 1.5rem; border-left: 4px solid #22c55e; }
    
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; padding: 2rem 0; }
    .modal-content { background: white; padding: 2rem; border-radius: var(--radius-lg); width: 100%; max-width: 600px; max-height: 95vh; overflow-y: auto; }
    .form-row { margin-bottom: 1rem; }
    .form-row label { display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 0.15rem; color: #475569; }
    .form-row input, .form-row select, .form-row textarea { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; font-family: inherit; box-sizing:border-box;}
    
    .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
    .badge-activo { background: #dcfce7; color: #166534; }
    .badge-inactivo { background: #fee2e2; color: #991b1b; }
    .badge-adsense { background: #fef08a; color: #854d0e; }
    .badge-img { background: #e0f2fe; color: #0369a1; }
</style>

<div class="admin-header">
    <div>
        <h1 style="margin:0;">Gestión de Publicidad</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Administra Banners e inyecciones de Google AdSense en el sitio.</p>
    </div>
    <button class="btn-primary" onclick="openModal()"><i class="ri-add-circle-fill"></i> Crear Nuevo Anuncio</button>
</div>

<?php if($msg): ?>
    <div class="alert"><i class="ri-check-line"></i> <?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Nombre / Campaña</th>
            <th>Tipo</th>
            <th>Ubicación</th>
            <th>Vistas / Clics</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ads as $a): ?>
        <tr>
            <td>
                <strong><?php echo htmlspecialchars($a['nombre']); ?></strong><br>
                <?php if($a['tipo']==='imagen' && !empty($a['imagen_url'])): ?>
                    <img src="<?php echo '<?= base_url('/') ?>' . htmlspecialchars($a['imagen_url']); ?>" style="height:30px; object-fit:contain; border:1px solid #ccc; margin-top:4px;">
                <?php else: ?>
                    <span style="font-size:0.7rem; color:#6b7280;">(&lt;script&gt; inyectado)</span>
                <?php endif; ?>
            </td>
            <td>
                <span class="badge <?php echo $a['tipo']==='adsense' ? 'badge-adsense' : 'badge-img'; ?>">
                    <?php echo strtoupper($a['tipo']); ?>
                </span>
            </td>
            <td><?php echo str_replace('_', ' ', strtoupper($a['ubicacion'])); ?></td>
            <td>
                <i class="ri-eye-line"></i> <?php echo number_format($a['vistas']); ?> <br>
                <i class="ri-cursor-fill"></i> <?php echo number_format($a['clics']); ?>
            </td>
            <td>
                <span class="badge <?php echo $a['estado']==='activo' ? 'badge-activo' : 'badge-inactivo'; ?>">
                    <?php echo strtoupper($a['estado']); ?>
                </span>
            </td>
            <td>
                <button onclick='editAd(<?php echo json_encode($a); ?>)' style="background:#e0f2fe; color:#0369a1; border:none; padding:4px 8px; border-radius:4px; cursor:pointer;" title="Editar"><i class="ri-edit-2-line"></i></button>
                <a href="<?= base_url('/') ?>admin/publicidad/action?action_type=delete&id=<?php echo $a['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" onclick="return confirm('¿Borrar anuncio definitivamente?')" style="background:#fee2e2; color:#b91c1c; border:none; padding:4px 8px; border-radius:4px; cursor:pointer; text-decoration:none;"><i class="ri-delete-bin-line"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($ads)): ?>
        <tr><td colspan="6" style="text-align:center; padding: 2rem;">No hay anuncios creados.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- MODAL -->
<div id="modal" class="modal">
    <div class="modal-content">
        <div style="display:flex; justify-content:space-between; margin-bottom:1rem; border-bottom:1px solid #e5e7eb; padding-bottom:1rem;">
            <h2 id="modal-title" style="margin:0;">Nuevo Anuncio</h2>
            <i class="ri-close-line" style="cursor:pointer; font-size:1.5rem;" onclick="document.getElementById('modal').style.display='none'"></i>
        </div>
        
        <form method="POST" action="<?= base_url('/') ?>admin/publicidad/action" enctype="multipart/form-data">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" id="form-action" value="create">
            <input type="hidden" name="id" id="form-id" value="">
            <input type="hidden" name="imagen_url_actual" id="imagen_url_actual" value="">
            
            <div class="form-row">
                <label>Nombre de la Campaña / Identificador interno</label>
                <input type="text" name="nombre" id="nombre" required placeholder="Ej: Banner Zapatillas Nike Navideño">
            </div>
            
            <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                    <label>Ubicación en el Portal</label>
                    <select name="ubicacion" id="ubicacion" required>
                        <option value="cabecera">Cabecera (Debajo del menú)</option>
                        <option value="sidebar_top">Barra Lateral Top (Derecha)</option>
                        <option value="sidebar_bottom">Barra Lateral Abajo (Derecha)</option>
                        <option value="interior_superior">Interior Noticia (Arriba)</option>
                        <option value="interior_inferior">Interior Noticia (Debajo del texto)</option>
                    </select>
                </div>
                <div>
                    <label>Tipo de Anuncio</label>
                    <select name="tipo" id="tipo" required onchange="toggleTipos()">
                        <option value="imagen">Banner Gráfico Clickeable</option>
                        <option value="adsense">Código AdSense / HTML Script</option>
                    </select>
                </div>
            </div>

            <!-- BLOQUE IMAGEN -->
            <div id="box-imagen" style="background:#f8fafc; padding:1rem; border-radius:6px; border:1px dashed #cbd5e1; margin-bottom:1rem;">
                <div class="form-row">
                    <label>Subir Imagen (.jpg, .png, .gif animados)</label>
                    <input type="file" name="banner_upload" id="banner_upload" accept="image/*">
                </div>
                <div class="form-row" style="margin-bottom:0;">
                    <label>Enlace de Destino (URL al hacer clic)</label>
                    <input type="url" name="enlace_url" id="enlace_url" placeholder="https://...">
                </div>
            </div>

            <!-- BLOQUE ADSENSE -->
            <div id="box-adsense" style="display:none; background:#fefce8; padding:1rem; border-radius:6px; border:1px dashed #fef08a; margin-bottom:1rem;">
                <div class="form-row" style="margin-bottom:0;">
                    <label>Pegar Código AdSense o Script Iframe</label>
                    <textarea name="codigo_script" id="codigo_script" rows="4" placeholder="<script data-ad-client=...></script>"></textarea>
                </div>
            </div>

            <div class="form-row">
                <label>Estado de Publicación</label>
                <select name="estado" id="estado">
                    <option value="activo">ACTIVO (Público)</option>
                    <option value="inactivo">INACTIVO (Pausado)</option>
                </select>
            </div>
            
            <div style="text-align:right; margin-top: 1rem;">
                <button type="submit" class="btn-primary" style="padding:1rem 2rem; font-size:1.1rem;"><i class="ri-save-3-line"></i> Guardar Anuncio</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleTipos() {
        var tipo = document.getElementById('tipo').value;
        if (tipo === 'imagen') {
            document.getElementById('box-imagen').style.display = 'block';
            document.getElementById('box-adsense').style.display = 'none';
        } else {
            document.getElementById('box-imagen').style.display = 'none';
            document.getElementById('box-adsense').style.display = 'block';
        }
    }

    function openModal() {
        document.getElementById('modal-title').innerText = 'Nuevo Anuncio';
        document.getElementById('form-action').value = 'create';
        document.getElementById('form-id').value = '';
        document.getElementById('nombre').value = '';
        document.getElementById('enlace_url').value = '';
        document.getElementById('codigo_script').value = '';
        document.getElementById('imagen_url_actual').value = '';
        document.getElementById('modal').style.display = 'flex';
        toggleTipos();
    }

    function editAd(ad) {
        document.getElementById('modal-title').innerText = 'Editar Anuncio #' + ad.id;
        document.getElementById('form-action').value = 'update';
        document.getElementById('form-id').value = ad.id;
        document.getElementById('nombre').value = ad.nombre;
        document.getElementById('ubicacion').value = ad.ubicacion;
        document.getElementById('tipo').value = ad.tipo;
        document.getElementById('enlace_url').value = ad.enlace_url;
        document.getElementById('codigo_script').value = ad.codigo_script;
        document.getElementById('estado').value = ad.estado;
        document.getElementById('imagen_url_actual').value = ad.imagen_url;
        
        toggleTipos();
        document.getElementById('modal').style.display = 'flex';
    }
</script>
