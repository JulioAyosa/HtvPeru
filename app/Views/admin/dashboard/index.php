<?php
// app/Views/admin/dashboard/index.php
// Variables asumed: $msg, $user_role, $stats, $noticias, $edit_data
?>
<?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<?php if ($user_role === 'editor'): ?>
    <div class="alert" style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem;">
        Aviso: Como Editor tienes permisos limitados. No puedes eliminar noticias ni destacarlas en la portada.
    </div>
<?php endif; ?>

<div class="admin-header">
    <div>
        <h1 style="margin-top:0; margin-bottom: 0.5rem; color: #0f172a;"><i class="ri-article-line" style="color:var(--primary-color)"></i> Gestión de Entradas</h1>
        <p style="color: var(--text-muted); margin:0;">Módulo de Edición Premium para Redactores.</p>
    </div>
    <?php if ($user_role !== 'gerente'): ?>
    <button class="btn btn-primary" onclick="openEditorModal()"><i class="ri-edit-box-line"></i> Escribir Noticia</button>
    <?php endif; ?>
</div>

<!-- Dashboard Stats -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div style="background: white; padding: 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border-left: 4px solid var(--primary-color);">
        <h3 style="margin: 0; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Total Noticias</h3>
        <p style="margin: 0.5rem 0 0; font-size: 2rem; font-weight: 800; color: #111827;"><?php echo $stats['not']; ?></p>
    </div>
    <div style="background: white; padding: 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border-left: 4px solid #10b981;">
        <h3 style="margin: 0; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Publicadas</h3>
        <p style="margin: 0.5rem 0 0; font-size: 2rem; font-weight: 800; color: #111827;"><?php echo $stats['pub']; ?></p>
    </div>
    <div style="background: white; padding: 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border-left: 4px solid #f59e0b;">
        <h3 style="margin: 0; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Borradores</h3>
        <p style="margin: 0.5rem 0 0; font-size: 2rem; font-weight: 800; color: #111827;"><?php echo $stats['bor']; ?></p>
    </div>
    <div style="background: white; padding: 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border-left: 4px solid #8b5cf6;">
        <h3 style="margin: 0; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Programadas</h3>
        <p style="margin: 0.5rem 0 0; font-size: 2rem; font-weight: 800; color: #111827;"><?php echo $stats['prg']; ?></p>
    </div>
    <div style="background: white; padding: 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border-left: 4px solid #ef4444;">
        <h3 style="margin: 0; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Usuarios</h3>
        <p style="margin: 0.5rem 0 0; font-size: 2rem; font-weight: 800; color: #111827;"><?php echo $stats['usu']; ?></p>
    </div>
    <div style="background: white; padding: 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border-left: 4px solid #94a3b8;">
        <h3 style="margin: 0; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Papelera</h3>
        <p style="margin: 0.5rem 0 0; font-size: 2rem; font-weight: 800; color: #111827;"><?php echo $stats['pap']; ?></p>
    </div>
</div>

<!-- Controles y Buscador -->
<form id="bulkForm" method="POST" action="/piura_noticias_php/admin/dashboard/bulk">
    <?php echo csrf_field(); ?>
    
    <?php if ($user_role !== 'gerente'): ?>
    <div style="display:flex; justify-content:space-between; margin-bottom: 1rem; align-items:center; background: white; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); flex-wrap:wrap; gap:1rem;">
        <div style="display:flex; gap:0.5rem; align-items:center;">
            <input type="checkbox" id="selectAllTop" onclick="toggleAll(this)" style="width:1.25rem; height:1.25rem; cursor:pointer;" title="Seleccionar Todas las Visibles">
            <span style="font-weight:600; font-size:0.85rem; color:var(--text-muted); margin-right:1rem;">Seleccionar Todas</span>
            
            <select name="bulk_action" id="bulkSelect" style="padding:0.5rem; border:1px solid #cbd5e1; border-radius:4px; font-family:var(--font-sans); background:white;">
                <option value="">-- Acción por lotes --</option>
                <option value="publicado">Poner como Públicas</option>
                <option value="borrador">Mover a Borradores</option>
                <?php if ($user_role === 'admin'): ?>
                <option value="papelera">Mover a Papelera</option>
                <?php endif; ?>
            </select>
            <button type="button" onclick="if(document.getElementById('bulkSelect').value !== '') document.getElementById('bulkForm').submit(); else alert('Elige una acción por lote válida.');" class="btn btn-secondary" style="padding:0.5rem 1rem;"><i class="ri-check-line"></i> Aplicar a las marcadas</button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Buscador y Filtros -->
    <div style="display:flex; justify-content:space-between; margin-bottom: 1.5rem; gap:1rem; flex-wrap:wrap; background: white; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <div style="display:flex; gap:1rem; flex:1; align-items: center;">
            <div style="position:relative; width:100%; max-width:400px;">
                <i class="ri-search-line" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:#9ca3af;"></i>
                <input type="text" id="searchTable" placeholder="Buscar noticia por título, categoría o autor..." style="padding:0.75rem 1rem 0.75rem 2.5rem; border:1px solid #d1d5db; border-radius:6px; font-family:var(--font-sans); width:100%; box-sizing: border-box;" onkeyup="filterAdminTable()">
            </div>
            <input type="date" id="dateFilter" style="padding:0.75rem; border:1px solid #d1d5db; border-radius:6px; font-family:var(--font-sans); color:var(--text-muted);" onchange="filterAdminTable()">
            <button type="button" onclick="document.getElementById('searchTable').value=''; document.getElementById('dateFilter').value=''; filterAdminTable();" class="btn btn-secondary"><i class="ri-refresh-line"></i> Limpiar Filtros</button>
        </div>
        <span style="font-size:0.85rem; color:var(--text-muted); align-self:center;"><i class="ri-information-line"></i> Haz clic en las columnas para ordenar asc/desc.</span>
    </div>

    <table id="mainNewsTable" style="margin-bottom: 2rem;">
        <thead>
            <tr>
                <?php if ($user_role !== 'gerente'): ?>
                <th style="width: 40px; text-align:center;"><i class="ri-checkbox-multiple-line"></i></th>
                <?php endif; ?>
                <th onclick="sortTable(1)">TÍTULO <i class="ri-expand-up-down-line"></i></th>
                <th onclick="sortTable(2)">CATEGORÍA <i class="ri-expand-up-down-line"></i></th>
                <th onclick="sortTable(3)">AUTOR <i class="ri-expand-up-down-line"></i></th>
                <th onclick="sortTable(4)">FECHA <i class="ri-expand-up-down-line"></i></th>
                <th onclick="sortTable(5)">VISITAS <i class="ri-expand-up-down-line"></i></th>
                <th onclick="sortTable(6)">ESTADO <i class="ri-expand-up-down-line"></i></th>
                <?php if ($user_role !== 'gerente'): ?>
                <th style="text-align:right;">ACCIONES</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($noticias as $n): ?>
            <tr data-date="<?php echo date('Y-m-d', strtotime($n['fecha_publicacion'])); ?>">
                <?php if ($user_role !== 'gerente'): ?>
                <td style="text-align:center;">
                    <input type="checkbox" name="bulk_ids[]" value="<?php echo $n['id']; ?>" class="row-checkbox" style="width:1.25rem; height:1.25rem; cursor:pointer;">
                </td>
                <?php endif; ?>
                <td><strong><?php echo htmlspecialchars($n['titulo']); ?></strong></td>
                <td><span style="background:#e0e7ff; color:#4338ca; padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:800;"><?php echo htmlspecialchars($n['categoria']); ?></span></td>
                <td><?php echo htmlspecialchars($n['autor']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($n['fecha_publicacion'])); ?></td>
                <td><i class="ri-eye-line" style="color:var(--text-muted); margin-right:4px;"></i> <strong><?php echo number_format($n['vistas']); ?></strong></td>
                <td>
                    <?php if ($n['estado_publicacion'] === 'papelera'): ?>
                        <span style="background:#f1f5f9; color:#64748b; padding:4px 10px; border-radius:4px; font-size:0.75rem; font-weight:600;"><i class="ri-delete-bin-line"></i> Papelera</span>
                    <?php elseif ($n['estado_publicacion'] === 'borrador'): ?>
                        <span style="background:#fef3c7; color:#d97706; padding:4px 10px; border-radius:4px; font-size:0.75rem; font-weight:600;"><i class="ri-draft-line"></i> Borrador</span>
                    <?php elseif ($n['estado_publicacion'] === 'programado'): ?>
                        <span style="background:#ede9fe; color:#7c3aed; padding:4px 10px; border-radius:4px; font-size:0.75rem; font-weight:600;"><i class="ri-timer-line"></i> Prog. (<?php echo date('d/m H:i', strtotime($n['fecha_programada'])); ?>)</span>
                    <?php else: ?>
                        <?php if ($n['es_destacada']): ?>
                            <span style="background:var(--danger); color:white; padding:4px 10px; border-radius:4px; font-size:0.75rem; font-weight:600;">Hero</span>
                        <?php else: ?>
                            <span style="background:#10b981; color:white; padding:4px 10px; border-radius:4px; font-size:0.75rem; font-weight:600;">Público</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <?php if ($user_role !== 'gerente'): ?>
                <td style="text-align:right; white-space:nowrap;">
                    <?php if ($n['estado_publicacion'] === 'papelera'): ?>
                        <?php if ($user_role === 'admin'): ?>
                            <a href="/piura_noticias_php/admin/dashboard/action?action=restore&id=<?php echo $n['id']; ?>" class="btn btn-success" style="padding:4px 8px; font-size:0.75rem;"><i class="ri-refresh-line"></i> Restaurar</a>
                            <a href="/piura_noticias_php/admin/dashboard/action?action=hard_delete&id=<?php echo $n['id']; ?>" class="btn btn-danger" style="padding:4px 8px; font-size:0.75rem;" onclick="return confirm('¿Seguro que deseas eliminar esta noticia PERMANENTEMENTE?')"><i class="ri-close-circle-line"></i> Hard Delete</a>
                        <?php else: ?>
                            <span style="font-size:0.75rem; color:#9ca3af;">En Papelera</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="/article.php?id=<?php echo $n['id']; ?>" style="background:var(--primary-color); color:white; display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:4px; font-size:0.9rem; text-decoration:none; margin-right:4px;" target="_blank" title="Ver"><i class="ri-eye-line"></i></a>
                        <a href="/piura_noticias_php/admin?action=edit&id=<?php echo $n['id']; ?>" style="background:#fef08a; color:#b45309; border:1px solid #fde047; display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:4px; font-size:0.9rem; text-decoration:none; margin-right:4px;" title="Editar"><i class="ri-edit-2-line"></i></a>
                        <a href="/piura_noticias_php/admin/dashboard/action?action=duplicate&id=<?php echo $n['id']; ?>" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:4px; font-size:0.9rem; text-decoration:none; margin-right:4px;" title="Duplicar"><i class="ri-file-copy-line"></i></a>
                        <?php if ($user_role === 'admin'): ?>
                            <a href="/piura_noticias_php/admin/dashboard/action?action=delete&id=<?php echo $n['id']; ?>" style="background:#fee2e2; color:#dc2626; border:1px solid #fecaca; display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:4px; font-size:0.9rem; text-decoration:none;" onclick="return confirm('¿Enviar esta noticia a la Papelera?')" title="Borrar a Papelera"><i class="ri-delete-bin-line"></i></a>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</form>

<!-- Modal Formulario Extendido -->
<div id="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; padding: 1rem; box-sizing: border-box;">
    <div style="background: white; padding: 2.5rem; border-radius: var(--radius-lg); width: 100%; max-width: 1000px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2); box-sizing: border-box;">
        <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem; border-bottom:2px solid var(--border-color); padding-bottom:1rem;">
            <h2 id="modal-title" style="margin:0;"><i class="ri-quill-pen-line" style="color:var(--primary-color);"></i> <?php echo $edit_data ? 'Editar Noticia #'.$edit_data['id'] : 'Añadir entrada (Editor Interactivo)'; ?></h2>
            <i class="ri-close-line" style="cursor:pointer; font-size:1.5rem; background:#f1f5f9; border-radius:50%; padding:0.25rem;" onclick="closeEditorModal()"></i>
        </div>
        
        <form method="POST" action="/piura_noticias_php/admin/dashboard/store" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action_type" value="<?php echo $edit_data ? 'update' : 'create'; ?>">
            <?php if ($edit_data): ?>
                <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
                <input type="hidden" name="original_updated_at" value="<?php echo htmlspecialchars($edit_data['updated_at'] ?? ''); ?>">
            <?php endif; ?>
            
            <div class="form-row"><input type="text" name="titulo" required placeholder="Escribe un título..." style="font-size:1.5rem; font-weight:800; padding:1rem;" value="<?php echo htmlspecialchars($edit_data['titulo'] ?? ''); ?>"></div>
            
            <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                    <label>Categoría</label>
                    <select name="categoria" id="categoria_select" required onchange="toggleDistrito()">
                        <?php 
                        $cats = ['Nacional','Local (Regional)','Política','Policiales','Economía','Deportes','Entretenimiento','Salud','Tendencias','Publicidad'];
                        $selected_cat = $edit_data['categoria'] ?? 'Nacional';
                        if ($selected_cat === 'Local') $selected_cat = 'Local (Regional)';
                        
                        foreach($cats as $c) {
                            $sel = ($selected_cat === $c) ? 'selected' : '';
                            $style = ($c==='Publicidad') ? 'style="background:#fed7aa; color:#9a3412; font-weight:bold;"' : '';
                            echo "<option value='$c' $sel $style>$c</option>";
                        }
                        ?>
                    </select>
                </div>
                <div id="distrito_box" style="display:none; background: #eff6ff; padding: 10px; border-radius: 6px; border: 1px dashed #93c5fd;">
                    <label style="color: #1e3a8a;"><i class="ri-map-pin-line"></i> Distrito / Provincia</label>
                    <select name="distrito" id="distrito_select" style="background: white; border-color: #bfdbfe;">
                        <option value="">Selecciona Distrito (Opcional)</option>
                        <?php
                        $distritos = ['Ayabaca', 'Chulucanas', 'Huancabamba', 'Morropón', 'Paita', 'Piura', 'Sechura', 'Sullana', 'Talara', 'Varios / General'];
                        $sel_dist = $edit_data['distrito'] ?? '';
                        foreach($distritos as $d) {
                            $s = ($sel_dist === $d) ? 'selected' : '';
                            echo "<option value='$d' $s>$d</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
                <h3 style="margin-top: 0; font-size: 1.1rem; color: #334155; display:flex; align-items:center; gap:0.5rem; border-bottom: 2px solid #cbd5e1; padding-bottom:0.5rem; margin-bottom:1rem;"><i class="ri-image-add-fill"></i> Archivos Multimedia Nativo</h3>
                <div class="form-row">
                    <label>Imagen o Video Principal (.mp4, .jpg, .png)</label>
                    <input type="file" name="media_upload" accept="image/*,video/mp4,video/webm" style="background:white;" <?php echo $edit_data ? '' : 'required'; ?>>
                    <?php if($edit_data): ?><span style="font-size:0.75rem; color:var(--primary-color);">* Archivo actual: <?php echo basename($edit_data['imagen_url']); ?> (Sube uno nuevo solo si deseas reemplazarlo)</span><?php endif; ?>
                </div>
                <div class="form-row" style="margin-bottom:0;">
                    <label>Poster para Video (Opcional, miniatura si subiste MP4)</label>
                    <input type="file" name="poster_upload" accept="image/*" style="background:white;">
                    <span style="font-size:0.75rem; color:#64748b;">* Reemplaza el recuadro negro del video con una portada visible.</span>
                </div>
            </div>

            <div class="form-row">
                <label>Resumen / Bajada</label>
                <textarea name="extracto" required maxlength="250" placeholder="Escribe un breve resumen de 2 líneas..."><?php echo htmlspecialchars($edit_data['extracto'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-row">
                <label>Cuerpo Oficial (Texto Completo - WYSIWYG)</label>
                <textarea name="contenido" id="editor-contenido" placeholder="Desarrolla el contenido aquí..."><?php echo htmlspecialchars($edit_data['contenido'] ?? ''); ?></textarea>
            </div>
            
            <div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
                <h3 style="margin-top: 0; font-size: 1.1rem; color: #334155; display:flex; align-items:center; gap:0.5rem; border-bottom: 2px solid #cbd5e1; padding-bottom:0.5rem; margin-bottom:1rem;"><i class="ri-google-fill"></i> Yoast SEO & Metadatos</h3>
                <div id="seo-semaforo" style="margin-bottom: 1rem; padding: 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 6px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 0.95rem; display: flex; align-items: center; gap: 5px;">
                        Estado SEO: <span id="seo-score-badge" style="padding: 2px 8px; border-radius: 4px; color: white; font-weight: bold; background: #94a3b8;">Por evaluar</span>
                    </h4>
                    <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.85rem; color: #475569;" id="seo-checks">
                        <li id="check-title"><i class="ri-checkbox-blank-circle-line"></i> Título SEO: Mínimo 40, máximo 60 caracteres.</li>
                        <li id="check-desc"><i class="ri-checkbox-blank-circle-line"></i> Descripción: Mínimo 120, máximo 156 caracteres.</li>
                        <li id="check-words"><i class="ri-checkbox-blank-circle-line"></i> Contenido: Mínimo 300 palabras.</li>
                        <li id="check-kw"><i class="ri-checkbox-blank-circle-line"></i> Keyword Density: Introduce una Keyphrase.</li>
                    </ul>
                    <div style="margin-top: 10px;">
                        <input type="text" id="seo_focus_kw" placeholder="Palabra clave objetivo (Focus Keyword)" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem;">
                    </div>
                </div>
                <div class="form-row">
                    <label>Título SEO (Aparecerá en Google)</label>
                    <input type="text" id="seo_titulo" name="seo_titulo" placeholder="Ej: Fuerte lluvia inunda avenidas..." value="<?php echo htmlspecialchars($edit_data['seo_titulo'] ?? ''); ?>">
                    <div id="seo_titulo_counter" style="font-size: 0.75rem; color: #64748b; text-align: right; margin-top: 4px;">0 / 60</div>
                </div>
                <div class="form-row">
                    <label>Descripción Meta (Snippet)</label>
                    <textarea id="seo_descripcion" name="seo_descripcion" placeholder="Escribe un párrafo atractivo..."><?php echo htmlspecialchars($edit_data['seo_descripcion'] ?? ''); ?></textarea>
                    <div id="seo_desc_counter" style="font-size: 0.75rem; color: #64748b; text-align: right; margin-top: 4px;">0 / 156</div>
                </div>
            </div>

            <div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
                <h3 style="margin-top: 0; font-size: 1.1rem; color: #334155; display:flex; align-items:center; gap:0.5rem; border-bottom: 2px solid #cbd5e1; padding-bottom:0.5rem; margin-bottom:1rem;"><i class="ri-price-tag-3-fill"></i> Taxonomías y Citaciones (JNews Style)</h3>
                <div class="form-row">
                    <label>Etiquetas (Separadas por comas)</label>
                    <input type="text" name="tags" placeholder="Ej: minsa, chiclayo, sismo" value="<?php echo htmlspecialchars($edit_data['tags'] ?? ''); ?>">
                </div>
                <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:0;">
                    <div>
                        <label>Fuente (Nombre)</label>
                        <input type="text" name="fuente_nombre" placeholder="Ej: Diario El Correo" value="<?php echo htmlspecialchars($edit_data['fuente_nombre'] ?? ''); ?>">
                    </div>
                    <div>
                        <label>URL de la Fuente</label>
                        <input type="url" name="fuente_url" placeholder="Ej: https://diariocorreo.pe/..." value="<?php echo htmlspecialchars($edit_data['fuente_url'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <?php if ($user_role === 'admin'): ?>
            <div style="background:#fff7ed; padding: 1.5rem; border-radius: 8px; border: 1px solid #fed7aa; margin-bottom: 1.5rem;">
                <label style="display:inline-flex; align-items:center; gap:0.5rem; font-weight:800; color:#c2410c;">
                    <input type="checkbox" name="es_destacada" value="1" style="width:1.25rem; height:1.25rem;" <?php echo (!empty($edit_data['es_destacada'])) ? 'checked' : ''; ?>> 
                    Fijar como MEGA HERO Inicial en Portada
                </label>
            </div>
            <?php endif; ?>

            <div style="background:#f8fafc; padding: 1.5rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
                <h3 style="margin-top: 0; font-size: 1.1rem; color: #334155; display:flex; align-items:center; gap:0.5rem; border-bottom: 2px solid #cbd5e1; padding-bottom:0.5rem; margin-bottom:1rem;"><i class="ri-settings-4-fill"></i> Estado de Publicación y Programación</h3>
                <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:0;">
                    <div>
                        <label>Estado</label>
                        <select name="estado_publicacion" id="estado_select" required onchange="updateSubmitButton()">
                            <?php 
                            $estados = ['publicado' => 'Publicar Inmediatamente', 'borrador' => 'Guardar como Borrador', 'programado' => 'Programar para después'];
                            $sel_estado = $edit_data['estado_publicacion'] ?? 'publicado';
                            foreach($estados as $val => $label) {
                                $s = ($sel_estado === $val) ? 'selected' : '';
                                echo "<option value='$val' $s>$label</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label>Fecha de Programación <small style="font-weight:normal; color:var(--text-muted);">(Solo si seleccionaste 'Programar')</small></label>
                        <input type="datetime-local" name="fecha_programada" value="<?php echo !empty($edit_data['fecha_programada']) ? date('Y-m-d\TH:i', strtotime($edit_data['fecha_programada'])) : ''; ?>">
                    </div>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <button type="button" class="btn btn-secondary" style="padding: 1rem 2rem; font-size: 1.1rem;" onclick="closeEditorModal()"><i class="ri-close-circle-line"></i> CANCELAR / CERRAR</button>
                <button type="submit" id="btn-submit-main" class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;"><i class="ri-send-plane-fill"></i> PUBLICAR NOTICIA</button>
            </div>
        </form>
    </div>
</div>

<script src="/piura_noticias_php/js/ckeditor/ckeditor.js"></script>
<script>
    function toggleDistrito() {
        const cat = document.getElementById('categoria_select').value;
        const distBox = document.getElementById('distrito_box');
        if (cat === 'Local (Regional)' || cat === 'Local') {
            distBox.style.display = 'block';
        } else {
            distBox.style.display = 'none';
            document.getElementById('distrito_select').value = '';
        }
    }
    
    function updateSubmitButton() {
        const select = document.getElementById('estado_select');
        const btn = document.getElementById('btn-submit-main');
        if (select.value === 'borrador') {
            btn.innerHTML = '<i class="ri-save-line"></i> GUARDAR BORRADOR';
            btn.style.backgroundColor = '#d97706'; // naranja
        } else if (select.value === 'programado') {
            btn.innerHTML = '<i class="ri-timer-line"></i> PROGRAMAR NOTICIA';
            btn.style.backgroundColor = '#7c3aed'; // morado
        } else {
            btn.innerHTML = '<i class="ri-send-plane-fill"></i> PUBLICAR NOTICIA';
            btn.style.backgroundColor = 'var(--primary-color)';
        }
    }

    let editorInitialized = false;

    function initEditor() {
        if (editorInitialized) return;
        if (typeof CKEDITOR !== 'undefined') {
            try {
                CKEDITOR.replace('editor-contenido', {
                    height: 400,
                    extraPlugins: 'colorbutton,font,justify',
                    removeButtons: 'Save,NewPage,ExportPdf,Preview,Print,Templates,Cut,Copy,Paste,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,BidiLtr,BidiRtl,Language,Anchor,Flash,Smiley,PageBreak,Iframe,About,ShowBlocks,Maximize',
                    on: {
                        change: function() { evaluarSEO(); },
                        key: function() { setTimeout(evaluarSEO, 100); }
                    }
                });
                
                editorInitialized = true;
            } catch(e) {
                alert("Error al inicializar CKEditor: " + e.message);
            }
        } else {
            alert("CKEditor no se cargó correctamente desde el CDN. Revisa tu conexión o bloqueador de anuncios.");
        }
    }

    function openEditorModal() {
        document.getElementById('modal').style.display='flex';
        initEditor();
    }

    function closeEditorModal() {
        document.getElementById('modal').style.display='none';
        if(window.location.search.includes('action=edit')) window.location='/piura_noticias_php/admin';
    }

    document.addEventListener("DOMContentLoaded", function() {
        toggleDistrito();
        if (document.getElementById('estado_select')) updateSubmitButton();
        
        <?php if ($edit_data): ?>
        openEditorModal();
        <?php endif; ?>
    });

    function evaluarSEO() {
        const titleInput = document.getElementById('seo_titulo');
        const descInput = document.getElementById('seo_descripcion');
        const kwInput = document.getElementById('seo_focus_kw');
        if(!titleInput || !descInput || !kwInput) return;

        const tLen = titleInput.value.length;
        const dLen = descInput.value.length;
        const kw = kwInput.value.trim().toLowerCase();
        
        let bodyText = "";
        if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['editor-contenido']) {
            // Eliminar etiquetas HTML para contar palabras reales
            bodyText = CKEDITOR.instances['editor-contenido'].getData().replace(/<[^>]*>?/gm, ' ').toLowerCase();
        } else {
            let ta = document.getElementById('editor-contenido');
            if(ta) bodyText = ta.value.toLowerCase();
        }
        const wordCount = bodyText.trim().split(/\s+/).filter(w => w.length > 0).length;

        let score = 0;

        const checkT = document.getElementById('check-title');
        document.getElementById('seo_titulo_counter').textContent = tLen + ' / 60';
        if(tLen >= 40 && tLen <= 60) {
            checkT.innerHTML = '<i class="ri-checkbox-circle-fill" style="color:#22c55e;"></i> Título SEO: Óptimo ('+tLen+' caracteres).';
            score++;
        } else {
            checkT.innerHTML = '<i class="ri-close-circle-fill" style="color:#ef4444;"></i> Título SEO: Deben ser 40-60 caracteres ('+tLen+').';
        }

        const checkD = document.getElementById('check-desc');
        document.getElementById('seo_desc_counter').textContent = dLen + ' / 156';
        if(dLen >= 120 && dLen <= 156) {
            checkD.innerHTML = '<i class="ri-checkbox-circle-fill" style="color:#22c55e;"></i> Descripción: Óptima ('+dLen+' caracteres).';
            score++;
        } else {
            checkD.innerHTML = '<i class="ri-close-circle-fill" style="color:#ef4444;"></i> Descripción: Deben ser 120-156 caracteres ('+dLen+').';
        }

        const checkW = document.getElementById('check-words');
        if(wordCount >= 300) {
            checkW.innerHTML = '<i class="ri-checkbox-circle-fill" style="color:#22c55e;"></i> Contenido: Suficiente ('+wordCount+' palabras).';
            score++;
        } else {
            checkW.innerHTML = '<i class="ri-close-circle-fill" style="color:#ef4444;"></i> Contenido: Muy corto ('+wordCount+'/300 palabras).';
        }

        const checkK = document.getElementById('check-kw');
        if(kw.length > 2) {
            const regex = new RegExp(kw.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'), 'g');
            const occurs = (bodyText.match(regex) || []).length;
            if(occurs > 0) {
                checkK.innerHTML = '<i class="ri-checkbox-circle-fill" style="color:#22c55e;"></i> Keyword Density: Encontrada ' + occurs + ' veces en el cuerpo.';
                score++;
            } else {
                checkK.innerHTML = '<i class="ri-close-circle-fill" style="color:#ef4444;"></i> Keyword Density: La palabra clave no aparece en el contenido.';
            }
        } else {
            checkK.innerHTML = '<i class="ri-information-fill" style="color:#3b82f6;"></i> Keyword Density: Escribe una palabra clave para evaluar.';
        }

        const badge = document.getElementById('seo-score-badge');
        if(score === 4) {
            badge.textContent = 'Excelente';
            badge.style.backgroundColor = '#22c55e';
        } else if (score >= 2) {
            badge.textContent = 'Regular';
            badge.style.backgroundColor = '#eab308';
        } else {
            badge.textContent = 'Pobre';
            badge.style.backgroundColor = '#ef4444';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const titleInput = document.getElementById('seo_titulo');
        const descInput = document.getElementById('seo_descripcion');
        const kwInput = document.getElementById('seo_focus_kw');
        
        if(titleInput) {
            titleInput.addEventListener('input', evaluarSEO);
            descInput.addEventListener('input', evaluarSEO);
            kwInput.addEventListener('input', evaluarSEO);
        }
    });

    function filterAdminTable() {
        const textFilter = document.getElementById("searchTable").value.toLowerCase();
        const dateFilter = document.getElementById("dateFilter").value;
        
        const rows = document.querySelectorAll("#mainNewsTable tbody tr");
        rows.forEach(row => {
            if(row.cells.length === 1) return;
            const textContent = row.innerText.toLowerCase();
            const rowDate = row.getAttribute("data-date");
            const matchesText = textContent.includes(textFilter);
            const matchesDate = dateFilter === "" || rowDate === dateFilter;
            
            if (matchesText && matchesDate) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }
    
    function toggleAll(source) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        for(let i=0; i<checkboxes.length; i++) {
            if (checkboxes[i].closest('tr').style.display !== 'none') {
                checkboxes[i].checked = source.checked;
            }
        }
    }

    let sortDirection = false;
    function sortTable(columnIndex) {
        const table = document.getElementById("mainNewsTable");
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));
        if(rows.length === 0 || rows[0].cells.length === 1) return;
        
        sortDirection = !sortDirection;
        
        rows.sort((a, b) => {
            let aText = a.cells[columnIndex].innerText.trim();
            let bText = b.cells[columnIndex].innerText.trim();
            
            if (columnIndex === 4) {
                let aDate = a.getAttribute("data-date") || "";
                let bDate = b.getAttribute("data-date") || "";
                return sortDirection ? aDate.localeCompare(bDate) : bDate.localeCompare(aDate);
            }
            
            return sortDirection 
                ? aText.localeCompare(bText, undefined, { numeric: true, sensitivity: 'base' }) 
                : bText.localeCompare(aText, undefined, { numeric: true, sensitivity: 'base' });
        });
        
        tbody.innerHTML = "";
        rows.forEach(row => tbody.appendChild(row));
    }

    let lastUpdatedAt = document.querySelector('input[name="original_updated_at"]')?.value || '';
    let lastAutosaveContent = '';
    
    function autoSaveNota() {
        const isEditing = document.querySelector('input[name="action_type"]')?.value === 'update';
        const editId = document.querySelector('input[name="edit_id"]')?.value;
        if (!isEditing || !editId) return;

        const titulo = document.querySelector('input[name="titulo"]').value;
        let contenido = '';
        if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['editor-contenido']) {
            contenido = CKEDITOR.instances['editor-contenido'].getData();
        } else {
            contenido = document.getElementById('editor-contenido').value;
        }

        if (titulo === '' || contenido === '' || contenido === lastAutosaveContent) return;
        lastAutosaveContent = contenido;

        const formData = new FormData();
        formData.append('id', editId);
        formData.append('titulo', titulo);
        formData.append('contenido', contenido);
        formData.append('last_updated_at', lastUpdatedAt);
        
        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
        if (csrfToken) formData.append('csrf_token', csrfToken);

        fetch('/api/admin/autosave', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('seo-score-badge');
            if (data.status === 'success') {
                if (data.new_updated_at) {
                    lastUpdatedAt = data.new_updated_at;
                    const inputOrig = document.querySelector('input[name="original_updated_at"]');
                    if (inputOrig) inputOrig.value = lastUpdatedAt;
                }
                badge.innerHTML = '<i class="ri-save-3-line"></i> Autoguardado ' + data.time;
                badge.style.backgroundColor = '#10b981';
            } else if (data.conflict) {
                alert('⚠️ ATENCIÓN: ' + data.message);
                badge.innerHTML = '<i class="ri-error-warning-fill"></i> CONFLICTO';
                badge.style.backgroundColor = '#ef4444';
            }
        })
        .catch(err => console.error("Autosave error", err));
    }

    setInterval(autoSaveNota, 30000);
</script>
