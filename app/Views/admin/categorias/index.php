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

<div style="background: white; padding: 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); margin-bottom: 2rem; border-left: 4px solid #10b981; display:flex; gap:1rem; align-items:flex-end;">
    <form method="POST" action="/piura_noticias_php/admin/categorias/action" style="display:flex; gap:1rem; width:100%; align-items:flex-end;">
        <input type="hidden" name="action" value="create">
        <?php echo csrf_field(); ?>
        <div style="flex:1;">
            <label style="display:block; font-size:0.85rem; font-weight:600; color:#475569; margin-bottom: 0.15rem;">Nueva Categoría</label>
            <input type="text" name="nuevo_nombre" required placeholder="Ej: Especiales 2026" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px; font-family:inherit;">
        </div>
        <button type="submit" class="btn btn-success" style="padding:0.75rem 1.5rem;"><i class="ri-add-line"></i> Añadir y Reordenar</button>
    </form>
</div>

<form method="POST" action="/piura_noticias_php/admin/categorias/action">
    <input type="hidden" name="action" value="bulk_update">
    <?php echo csrf_field(); ?>
    <div style="background:var(--bg-main); padding: 1rem; border: 1px solid var(--border-color); border-radius: 6px 6px 0 0; display:flex; justify-content:space-between; align-items:center;">
        <span style="font-weight:600;"><i class="ri-list-ordered-2"></i> Estructura del Menú Superior</span>
        <button type="submit" class="btn btn-primary"><i class="ri-save-3-line"></i> Guardar Todo (Orden, Menú y Nombres)</button>
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
                    <input type="checkbox" class="toggle-checkbox" name="cat_mostrar[<?php echo $c['id']; ?>]" value="1" <?php echo $c['mostrar_menu'] ? 'checked' : ''; ?>>
                </td>
                <td>
                    <select name="cat_estado[]" style="font-weight:600; <?php echo $c['estado']==='inactivo'?'color:#dc2626;':'color:#166534;'; ?>">
                        <option value="activo" <?php echo $c['estado']==='activo'?'selected':''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $c['estado']==='inactivo'?'selected':''; ?>>Inactivo</option>
                    </select>
                </td>
                <td style="text-align:right;">
                    <a href="/piura_noticias_php/admin/categorias/action?action=delete&delete=<?php echo $c['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" onclick="return confirm('¿Seguro? Si hay noticias con esta categoría seguirán existiendo, pero la categoría no se sugerirá ni aparecerá.')" style="color:#b91c1c; padding:8px;"><i class="ri-delete-bin-line"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</form>

<div class="cat-bg-section" style="margin-top: 2.5rem;">
    <h2><i class="ri-image-2-line" style="color:var(--primary-color);"></i> Imágenes de Fondo por Categoría</h2>
    <p>Personaliza la cabecera visual de cada sección del portal. La imagen se mostrará como fondo detrás del nombre y la descripción de la categoría con un overlay oscuro para mantener la legibilidad del texto.</p>
    
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
                    <button type="button" class="btn-sm btn-remove" onclick="if(confirm('¿Eliminar la imagen de fondo de esta categoría?')){var f=document.createElement('form');f.method='POST';f.action='/admin/categorias/action';var a=document.createElement('input');a.type='hidden';a.name='action';a.value='remove_bg';f.appendChild(a);var b=document.createElement('input');b.type='hidden';b.name='cat_bg_id';b.value='<?php echo $c['id']; ?>';f.appendChild(b);var t=document.createElement('input');t.type='hidden';t.name='csrf_token';t.value='<?php echo csrf_token(); ?>';f.appendChild(t);document.body.appendChild(f);f.submit();}"><i class="ri-delete-bin-line"></i> Quitar</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
