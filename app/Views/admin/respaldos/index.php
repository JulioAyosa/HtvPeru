<?php
// app/Views/admin/respaldos/index.php
// Variables: $msg, $err, $categorias_export
?>
<style>
    .alert-success { background: #dcfce7; color: #166534; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-weight: 600; border:1px solid #bbf7d0; }
    .alert-error { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-weight: 600; border:1px solid #fecaca; }
    
    .cards-container { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start; }
    @media(max-width: 900px) { .cards-container { grid-template-columns: 1fr; } }
    
    .module-card { background: white; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 2rem; }
    .module-title { font-size: 1.25rem; font-weight: 600; display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem; padding-bottom:0.5rem; border-bottom:1px solid var(--border-color);}
    
    .form-group { margin-bottom: 1.25rem; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 0.15rem; font-size: 0.875rem; color: #374151; }
    .form-control { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: var(--primary-color); }
    
    .btn { display: inline-flex; align-items: center; justify-content:center; gap: 0.5rem; padding: 0.75rem 1.5rem; font-weight: 600; cursor: pointer; border-radius: 6px; border: none; font-size: 0.95rem; width:100%; transition:all 0.2s;}
    .btn-primary { background-color: var(--primary-color); color: white; }
    .btn-primary:hover { background-color: #1d4ed8; }
    .btn-success { background-color: #10b981; color: white; }
    .btn-success:hover { background-color: #059669; }
    
    .file-drop { border: 2px dashed #d1d5db; padding: 3rem 1rem; text-align: center; border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s; position:relative; overflow:hidden;}
    .file-drop:hover { background: #f9fafb; border-color: #9ca3af; }
    .file-drop input[type="file"] { position:absolute; left:0; top:0; opacity:0; width:100%; height:100%; cursor:pointer; }
</style>

<?php if ($msg): ?>
    <div class="alert-success"><i class="ri-checkbox-circle-fill"></i> <?php echo $msg; ?></div>
<?php endif; ?>
<?php if ($err): ?>
    <div class="alert-error"><i class="ri-error-warning-fill"></i> <?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<div class="admin-header">
    <div>
        <h1 style="margin:0;"><i class="ri-database-2-fill" style="color:var(--primary-color);"></i> Gestión de Respaldos</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Importa o exporta tu base de datos de noticias en un archivo comprimido de alta fidelidad (JSON).</p>
    </div>
</div>

<div class="cards-container">
    <!-- BACKUP SISTEMA COMPLETO -->
    <div class="module-card" style="grid-column: 1 / -1; border-color: #ef4444; background: #fff1f2;">
        <div class="module-title" style="color: #b91c1c;">
            <i class="ri-hard-drive-2-fill" style="color:#ef4444; font-size:1.5rem;"></i> Backup Total del Núcleo (.ZIP)
        </div>
        <p style="color:#7f1d1d; font-size:0.9rem; margin-top:0; margin-bottom:1rem;">Genera una copia de seguridad integral que incluye el código fuente exacto y la base de datos estructural. <strong>Protege tu portal ante desastres.</strong></p>
        
        <form method="POST" action="<?= base_url('/') ?>admin/respaldos/action" id="form-backup-zip">
            <input type="hidden" name="action" value="full_zip">
            <?php echo csrf_field(); ?>
            
            <div style="display:flex; gap: 2rem; margin-bottom: 1.5rem; flex-wrap:wrap;">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; color:#991b1b; font-weight:600; font-size:0.9rem;">
                    <input type="checkbox" name="inc_media" id="chk_media" checked>
                    Incluir Galería Multimedia (/capturas)
                </label>
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; color:#991b1b; font-weight:600; font-size:0.9rem;">
                    <input type="checkbox" name="inc_uploads" id="chk_uploads">
                    Incluir Documentos (/uploads)
                </label>
            </div>
            
            <div style="background: #fee2e2; padding: 1rem; border-radius: 6px; margin-bottom: 0; border:1px dashed #f87171; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span style="display:block; font-size:0.75rem; text-transform:uppercase; color:#b91c1c; font-weight:800; letter-spacing:1px;">Estimación de Tamaño</span>
                    <strong style="font-size: 1.5rem; color:#7f1d1d;" id="size-estimator">Calculando...</strong>
                </div>
                <button type="submit" class="btn btn-primary" style="background:#dc2626; border-color:#b91c1c; width:auto; padding: 0.75rem 2rem;" onclick="this.innerHTML='<i class=\'ri-loader-4-line ri-spin\'></i> Empacando...'; this.style.opacity='0.7';">
                    <i class="ri-install-fill"></i> Descargar ZIP
                </button>
            </div>
        </form>
        
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const chkMedia = document.getElementById('chk_media');
                const chkUploads = document.getElementById('chk_uploads');
                const estimator = document.getElementById('size-estimator');
                let sizes = { core: 0, db: 0, media: 0, uploads: 0 };
                
                function updateSize() {
                    let total = sizes.core + sizes.db + 150000;
                    if (chkMedia.checked) total += sizes.media;
                    if (chkUploads.checked) total += sizes.uploads;
                    
                    const mb = (total / (1024 * 1024)).toFixed(2);
                    estimator.innerText = mb + ' MB';
                }
                
                chkMedia.addEventListener('change', updateSize);
                chkUploads.addEventListener('change', updateSize);
                
                fetch('<?= base_url('/') ?>api/admin/backup_size')
                    .then(res => res.json())
                    .then(data => {
                        sizes.core = data.core_size || 0;
                        sizes.db = data.db_size || 0;
                        sizes.media = data.media_size || 0;
                        sizes.uploads = data.uploads_size || 0;
                        updateSize();
                    })
                    .catch(e => {
                        estimator.innerText = "Desconocido";
                    });
            });
        </script>
    </div>

    <!-- EXPORTACIÓN -->
    <div class="module-card">
        <div class="module-title">
            <i class="ri-download-cloud-2-line" style="color:var(--primary-color); font-size:1.5rem;"></i> Exportar Noticias
        </div>
        <form method="POST" action="<?= base_url('/') ?>admin/respaldos/action">
            <input type="hidden" name="action" value="export">
            <?php echo csrf_field(); ?>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label>Desde Fecha (Opcional)</label>
                    <input type="date" name="fecha_inicio" class="form-control">
                </div>
                <div class="form-group">
                    <label>Hasta Fecha (Opcional)</label>
                    <input type="date" name="fecha_fin" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label>Estado de Publicación</label>
                <select name="estado" class="form-control">
                    <option value="todos">Todos los Estados</option>
                    <option value="publicado">Publicados</option>
                    <option value="borrador">Borradores</option>
                    <option value="programado">Programados</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Filtrar por Categoría</label>
                <select name="categoria" class="form-control">
                    <option value="todas">Todas las Categorías</option>
                    <?php foreach ($categorias_export as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-top:2rem;">
                <button type="submit" class="btn btn-primary"><i class="ri-file-download-fill"></i> Generar y Descargar JSON</button>
                <p style="font-size:0.75rem; color:var(--text-muted); text-align:center; margin-top:0.75rem;">El servidor forzará una descarga directa del archivo que podrás almacenar de manera segura.</p>
            </div>
        </form>
    </div>

    <!-- IMPORTACIÓN -->
    <div class="module-card">
        <div class="module-title">
            <i class="ri-upload-cloud-2-line" style="color:#10b981; font-size:1.5rem;"></i> Restaurar / Importar
        </div>
        <form method="POST" action="<?= base_url('/') ?>admin/respaldos/action" enctype="multipart/form-data">
            <input type="hidden" name="action" value="import">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label>Seleccionar Archivo de Respaldo (.json)</label>
                <div class="file-drop" id="drop-zone">
                    <i class="ri-file-upload-line" style="font-size:3rem; color:#9ca3af; display:block; margin-bottom:1rem;"></i>
                    <span style="font-weight:600; color:#374151;">Haz clic o arrastra un archivo .json aquí</span><br>
                    <span style="font-size:0.75rem; color:#9ca3af;" id="file-name-display">El documento debe haber sido exportado desde HTVPERU CMS.</span>
                    <input type="file" name="backup_file" id="backup_file" accept=".json" required>
                </div>
            </div>

            <div class="form-group">
                <label>¿Qué hacer si una Noticia ya existe?</label>
                <select name="modo" class="form-control">
                    <option value="omitir">Ignorarla (Mantener versión actual de la BD)</option>
                    <option value="reemplazar">Reemplazarla (Sobrescribir con la versión del Backup)</option>
                </select>
            </div>

            <div style="margin-top:2rem;">
                <button type="submit" class="btn btn-success" onclick="if(document.getElementById('backup_file').files.length===0) { alert('Debes seleccionar un archivo'); return false; } return confirm('¿Deseas iniciar la importación masiva ahora?');">
                    <i class="ri-save-3-fill"></i> Procesar Importación
                </button>
                <p style="font-size:0.75rem; color:var(--text-muted); text-align:center; margin-top:0.75rem;">Los medios visuales (imágenes o videos) no cambiarán ya que sus links hacen referencia al servidor actual.</p>
            </div>
        </form>
    </div>

    <!-- BEGIN SISTEMA AUTOMATICO BACKUP -->
    <div class="module-card" style="grid-column: 1 / -1; border-color: #10b981; background: #ecfdf5; margin-top: 1.5rem;">
        <div class="module-title" style="color: #047857; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <i class="ri-history-fill" style="color:#10b981; font-size:1.5rem; margin-right: 0.5rem; transform: translateY(3px); display: inline-block;"></i>
                Historial Automático de Backups
            </div>
            <small style="font-weight: normal; font-size: 0.85rem; color: #047857; margin-top:0.3rem; display:block;">Retención: 1, 7, 15, 30, 60, 90 y 365 días</small>
        </div>
        
        <div style="background: white; border-radius: 8px; border: 1px solid #d1fae5; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px 16px; color: #475569; font-weight: 600; font-size: 0.9rem;">Fecha del Backup</th>
                        <th style="padding: 12px 16px; color: #475569; font-weight: 600; font-size: 0.9rem;">Antigüedad</th>
                        <th style="padding: 12px 16px; color: #475569; font-weight: 600; font-size: 0.9rem;">Tamaño</th>
                        <th style="padding: 12px 16px; color: #475569; font-weight: 600; font-size: 0.9rem;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $backup_dir = __DIR__ . '/../../../../backups/';
                    if (file_exists($backup_dir)) {
                        $archivos = glob($backup_dir . "*.zip");
                        rsort($archivos);
                        
                        if(count($archivos) > 0){
                            foreach ($archivos as $archivo) {
                                $nombre = basename($archivo);
                                $fecha_archivo = date("Y-m-d", filemtime($archivo));
                                $tamano = round(filesize($archivo) / 1048576, 2) . ' MB';
                                $dias_antiguedad = floor((time() - filemtime($archivo)) / (60 * 60 * 24));
                                
                                $etiqueta = "Hace " . $dias_antiguedad . " días";
                                if($dias_antiguedad == 0) $etiqueta = "Hoy";
                                elseif($dias_antiguedad == 1) $etiqueta = "Ayer";

                                echo "<tr style='border-bottom: 1px solid #e2e8f0; transition: background 0.2s;' onmouseover='this.style.backgroundColor=\"#f1f5f9\"' onmouseout='this.style.backgroundColor=\"transparent\"'>
                                        <td style='padding: 12px 16px; color: #334155; font-weight: 500;'>{$fecha_archivo}</td>
                                        <td style='padding: 12px 16px;'><span style='background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 99px; font-size: 0.8rem; font-weight: 600;'>{$etiqueta}</span></td>
                                        <td style='padding: 12px 16px; color: #64748b;'>{$tamano}</td>
                                        <td style='padding: 12px 16px;'>
                                            <a href='<?= base_url('/') ?>admin/respaldos/action?delete_auto_backup={$nombre}' style='display: inline-flex; align-items: center; background: #ef4444; color: white; text-decoration: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; transition: background 0.2s; margin-right: 5px;' onclick=\"return confirm('¿Mover esta copia a la papelera por 15 días?')\" onmouseover=\"this.style.backgroundColor='#dc2626'\" onmouseout=\"this.style.backgroundColor='#ef4444'\"><i class='ri-delete-bin-line' style='margin-right: 6px; font-size: 1.1rem;'></i> Eliminar</a>
                                            <a href='<?= base_url('/') ?>admin/respaldos/action?download_auto_backup={$nombre}' style='display: inline-flex; align-items: center; background: #10b981; color: white; text-decoration: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; transition: background 0.2s;' onmouseover='this.style.backgroundColor=\"#059669\"' onmouseout='this.style.backgroundColor=\"#10b981\"'>
                                               <i class='ri-download-cloud-2-line' style='margin-right: 6px; font-size: 1.1rem;'></i> Descargar ZIP
                                            </a>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='padding: 20px; text-align: center; color: #64748b;'>Aún no hay copias automáticas. La primera se generará esta medianoche.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='padding: 20px; text-align: center; color: #64748b;'>El sistema cron se ejecutará por primera vez esta noche.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const fileInput = document.getElementById('backup_file');
    const display = document.getElementById('file-name-display');
    const dropZone = document.getElementById('drop-zone');

    fileInput.addEventListener('change', function(e) {
        if (this.files && this.files.length > 0) {
            const file = this.files[0];
            const sizeKB = (file.size / 1024).toFixed(1);
            display.innerHTML = `<span style="color:var(--primary-color); font-weight:bold;">${file.name}</span> (${sizeKB} KB listo para subir)`;
            dropZone.style.borderColor = "var(--primary-color)";
            dropZone.style.background = "#eff6ff";
        } else {
            display.innerHTML = 'El documento debe haber sido exportado desde HTVPERU CMS.';
            dropZone.style.borderColor = "#d1d5db";
            dropZone.style.background = "transparent";
        }
    });
</script>
