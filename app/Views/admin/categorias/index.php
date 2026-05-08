<?php
// app/Views/admin/categorias/index.php
// Variables asumidas: $msg, $categorias, $configs
?>
<div class="admin-header">
    <div>
        <h1 style="margin:0;"><i class="ri-folder-open-line" style="color:var(--primary-color)"></i> Taxonomías y Categorías</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Crea nuevas categorías y organiza el Menú Superior del sitio público.</p>
    </div>
</div>

<?php if($msg): ?>
    <div class="alert alert-success"><i class="ri-check-line"></i> <?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="cfg-panel" style="padding: 1.5rem; margin-bottom: 2.5rem; display:flex; gap:1.5rem; align-items:center;">
    <div style="width: 48px; height: 48px; background: #ecfdf5; color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
        <i class="ri-folder-add-line"></i>
    </div>
    <form method="POST" action="/piura_noticias_php/admin/categorias/action" style="display:flex; gap:1rem; width:100%; align-items:flex-end; flex-wrap:wrap;">
        <input type="hidden" name="action" value="create">
        <?php echo csrf_field(); ?>
        <div style="flex:1; min-width:250px;">
            <label style="display:block; font-size:0.85rem; font-weight:700; color:#475569; margin-bottom: 0.4rem;">Nombre de Nueva Categoría</label>
            <input type="text" name="nuevo_nombre" required placeholder="Ej: Especiales 2026" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #e2e8f0; border-radius:8px; font-family:inherit; font-size:0.95rem; background:#f8fafc; transition:all 0.2s;" onfocus="this.style.background='white'; this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)';">
        </div>
        <button type="submit" class="btn btn-success btn-save" style="padding:0.75rem 1.5rem; border-radius:8px; background:linear-gradient(135deg, #10b981, #059669);"><i class="ri-add-circle-fill"></i> Añadir y Reordenar</button>
    </form>
</div>

<div class="cfg-panel" style="padding: 2rem;">
<form method="POST" action="/piura_noticias_php/admin/categorias/action">
    <input type="hidden" name="action" value="bulk_update">
    <?php echo csrf_field(); ?>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0;">
        <h3 style="margin:0; font-size:1.1rem; border:none; padding:0; display:flex; align-items:center; gap:0.5rem;"><i class="ri-list-ordered-2" style="color:var(--primary-color);"></i> Estructura del Menú Superior</h3>
        <button type="submit" class="btn btn-primary btn-save" style="padding: 0.6rem 1.25rem; font-size: 0.9rem;"><i class="ri-save-3-line"></i> Guardar Todo</button>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:60px;">Orden</th>
                <th>Nombre de Categoría y Descripción Corta</th>
                <th>Slug (URL)</th>
                <th style="text-align:center;">¿Mostrar en Navbar?</th>
                <th>Estado General</th>
                <th style="text-align:right;">Eliminar</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categorias as $c): ?>
            <tr>
                <td>
                    <input type="hidden" name="cat_id[]" value="<?php echo $c['id']; ?>">
                    <input type="number" name="cat_orden[]" value="<?php echo $c['orden']; ?>">
                </td>
                <td>
                    <input type="text" class="cat-input" name="cat_nombre[]" value="<?php echo htmlspecialchars($c['nombre']); ?>" placeholder="Nombre (Ej: Política)">
                    <input type="text" class="cat-input" name="cat_descripcion[]" value="<?php echo htmlspecialchars($c['descripcion'] ?? ''); ?>" placeholder="Frase: Encuentra todas las noticias..." style="font-size:0.8rem; font-weight:400; color:var(--text-muted); margin-top:5px; border-bottom:1px dashed #cbd5e1;">
                </td>
                <td style="color:var(--text-muted); font-family:monospace; font-size:0.85rem;">/seccion/<?php echo $c['slug']; ?></td>
                <td style="text-align:center;">
                    <div class="toggle-switch" style="margin: 0 auto;">
                        <input type="checkbox" name="cat_mostrar[<?php echo $c['id']; ?>]" value="1" <?php echo $c['mostrar_menu'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </div>
                </td>
                <td>
                    <select name="cat_estado[]" style="padding: 0.4rem 0.75rem; border-radius: 20px; font-weight: 700; font-size: 0.8rem; border: 1px solid <?php echo $c['estado']==='inactivo'?'#fecaca':'#bbf7d0'; ?>; background: <?php echo $c['estado']==='inactivo'?'#fef2f2':'#f0fdf4'; ?>; color: <?php echo $c['estado']==='inactivo'?'#dc2626':'#16a34a'; ?>; outline: none; cursor: pointer;">
                        <option value="activo" <?php echo $c['estado']==='activo'?'selected':''; ?>>ACTIVO</option>
                        <option value="inactivo" <?php echo $c['estado']==='inactivo'?'selected':''; ?>>INACTIVO</option>
                    </select>
                </td>
                <td style="text-align:right;">
                    <a href="/piura_noticias_php/admin/categorias/action?action=delete&delete=<?php echo $c['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" onclick="return confirmDelete(event, '¿Seguro? Si hay noticias con esta categoría seguirán existiendo, pero la categoría no se sugerirá ni aparecerá.')" style="color:#ef4444; padding:8px; display:inline-block; transition:all 0.2s; border-radius:6px;" onmouseover="this.style.background='#fef2f2';" onmouseout="this.style.background='transparent';"><i class="ri-delete-bin-fill" style="font-size:1.1rem;"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</form>
</div>

<div class="cfg-panel" style="padding: 2.5rem; margin-top: 2.5rem;">
    <h3 style="margin-bottom: 0.5rem;"><i class="ri-image-2-line" style="color:var(--primary-color);"></i> Imágenes de Fondo por Categoría</h3>
    <p style="color: var(--text-muted); margin: 0 0 1.5rem 0; font-size: 0.9rem;">Personaliza la cabecera visual de cada sección del portal. La imagen se mostrará como fondo detrás del nombre y la descripción de la categoría con un overlay oscuro para mantener la legibilidad del texto.</p>
    
    <div class="cat-bg-grid">
        <?php foreach ($categorias as $c): ?>
        <div class="cat-bg-card">
            <div class="cat-bg-card-header">
                <div>
                    <h4><?php echo htmlspecialchars($c['nombre']); ?></h4>
                    <span class="cat-slug">/seccion/<?php echo $c['slug']; ?></span>
                </div>
                <?php if(!empty($c['imagen_fondo'])): ?>
                    <span style="font-size:0.7rem; background:#dcfce7; color:#166534; padding:3px 8px; border-radius:20px; font-weight:600;"><i class="ri-check-line"></i> Activa</span>
                <?php else: ?>
                    <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; padding:3px 8px; border-radius:20px; font-weight:600;">Sin imagen</span>
                <?php endif; ?>
            </div>
            
            <div class="cat-bg-preview <?php echo empty($c['imagen_fondo']) ? 'no-image' : ''; ?>" 
                 <?php if(!empty($c['imagen_fondo'])): ?>style="background-image: url('/<?php echo htmlspecialchars($c['imagen_fondo']); ?>');"<?php endif; ?>
                 onclick="document.getElementById('bg_input_<?php echo $c['id']; ?>').click()">
                <?php if(!empty($c['imagen_fondo'])): ?>
                    <div class="cat-bg-overlay-text">
                        <span><?php echo htmlspecialchars($c['nombre']); ?></span>
                        <small><?php echo htmlspecialchars($c['descripcion'] ?? 'Descripción de la categoría'); ?></small>
                    </div>
                <?php else: ?>
                    <i class="ri-image-add-line placeholder-icon"></i>
                    <span class="placeholder-text">Click para añadir imagen</span>
                <?php endif; ?>
            </div>
            
            <div class="cat-bg-actions">
                <form method="POST" action="/piura_noticias_php/admin/categorias/action" enctype="multipart/form-data" style="display:flex; gap:0.5rem; width:100%; align-items:center;" id="form_bg_<?php echo $c['id']; ?>">
                    <input type="hidden" name="action" value="upload_bg">
                    <input type="hidden" name="cat_bg_id" value="<?php echo $c['id']; ?>">
                    <input type="file" name="cat_bg_image" id="bg_input_<?php echo $c['id']; ?>" accept="image/*" style="display:none;" onchange="document.getElementById('form_bg_<?php echo $c['id']; ?>').submit()">
                    <button type="button" class="btn-sm btn-upload" onclick="document.getElementById('bg_input_<?php echo $c['id']; ?>').click()"><i class="ri-upload-2-line"></i> Subir</button>
                    <?php if(!empty($c['imagen_fondo'])): ?>
                    <button type="button" class="btn-sm btn-remove" onclick="Swal.fire({title:'¿Eliminar la imagen?', icon:'warning', showCancelButton:true, confirmButtonColor:'#ef4444', confirmButtonText:'Sí, eliminar'}).then((r)=>{if(r.isConfirmed){var f=document.createElement('form');f.method='POST';f.action='/piura_noticias_php/admin/categorias/action';var a=document.createElement('input');a.type='hidden';a.name='action';a.value='remove_bg';f.appendChild(a);var b=document.createElement('input');b.type='hidden';b.name='cat_bg_id';b.value='<?php echo $c['id']; ?>';f.appendChild(b);var t=document.createElement('input');t.type='hidden';t.name='csrf_token';t.value='<?php echo csrf_token(); ?>';f.appendChild(t);document.body.appendChild(f);f.submit();}})"><i class="ri-delete-bin-line"></i> Quitar</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
