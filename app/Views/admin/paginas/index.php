<?php
// app/Views/admin/paginas/index.php
// Variables asumidas: $msg, $paginas
?>
<div class="admin-header">
    <div>
        <h1 style="margin:0;"><i class="ri-pages-line" style="color:var(--primary-color)"></i> Páginas Estáticas</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Crea apartados como "Nosotros", "Contacto" o "Términos".</p>
    </div>
    <button class="btn btn-primary" onclick="openModal()"><i class="ri-add-circle-fill"></i> Nueva Página</button>
</div>

<?php if($msg): ?>
    <div class="alert alert-success"><i class="ri-check-line"></i> <?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div style="background: white; border-radius: var(--radius-lg); box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 2rem;">
    <table style="width: 100%; border-collapse: collapse; margin: 0; border: none;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <th style="padding: 1rem 1.5rem; color: #475569; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; text-align: left; border: none;">Título</th>
                <th style="padding: 1rem 1.5rem; color: #475569; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; text-align: left; border: none;">URL / Archivo (Slug)</th>
                <th style="padding: 1rem 1.5rem; color: #475569; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; text-align: left; border: none;">Estado</th>
                <th style="padding: 1rem 1.5rem; color: #475569; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; text-align: left; border: none;">Última Modificación</th>
                <th style="padding: 1rem 1.5rem; color: #475569; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; text-align: right; border: none;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($paginas as $p): ?>
            <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.2s;" onmouseover="this.style.background='#fbfcfd'" onmouseout="this.style.background='transparent'">
                <td style="padding: 1rem 1.5rem; border: none;">
                    <div style="font-weight: 800; color: #1e293b; font-size: 0.95rem;">
                        <?php 
                        // Fix for bad character encoding from legacy DB inserts
                        $titulo = $p['titulo'];
                        $corrupt_map = [
                            '├í' => 'á', '├⌐' => 'é', '├¡' => 'í', '├│' => 'ó', '├║' => 'ú', '├▒' => 'ñ',
                            '├ü' => 'Á', '├ë' => 'É', '├ì' => 'Í', '├ô' => 'Ó', '├Ü' => 'Ú', '├æ' => 'Ñ',
                            '├á' => 'á', // fallback
                        ];
                        $titulo = strtr($titulo, $corrupt_map);
                        // Also fix lowercase é just in case 'TÉrminos' looks weird as 'TÉrminos'
                        $titulo = str_replace('TÉrminos', 'Términos', $titulo);
                        echo htmlspecialchars($titulo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        ?>
                    </div>
                </td>
                <td style="padding: 1rem 1.5rem; border: none;">
                    <a href="/piura_noticias_php/pagina/<?php echo $p['slug']; ?>" target="_blank" style="color:var(--primary-color); text-decoration: none; font-family: monospace; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.25rem; background: #eff6ff; padding: 4px 8px; border-radius: 4px; transition: all 0.2s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                        <i class="ri-external-link-line"></i> /pagina/<?php echo htmlspecialchars($p['slug']); ?>
                    </a>
                </td>
                <td style="padding: 1rem 1.5rem; border: none;">
                    <?php 
                        $estado = !empty($p['estado']) ? strtolower($p['estado']) : 'activo';
                    ?>
                    <span style="display:inline-block; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; <?php echo ($estado==='activo') ? 'background:#ecfdf5; color:#10b981; border:1px solid #a7f3d0;' : 'background:#fef2f2; color:#ef4444; border:1px solid #fecaca;'; ?>">
                        <?php echo strtoupper($estado); ?>
                    </span>
                </td>
                <td style="padding: 1rem 1.5rem; border: none; color: var(--text-muted); font-size: 0.85rem;">
                    <?php echo date('d M Y, H:i', strtotime($p['fecha_modificacion'])); ?>
                </td>
                <td style="padding: 1rem 1.5rem; border: none; text-align: right;">
                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <button onclick='editPagina(<?php echo $p['id']; ?>)' style="background:white; color:var(--primary-color); border:1px solid #bfdbfe; padding:6px; border-radius:6px; cursor:pointer; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='white'" title="Editar"><i class="ri-edit-2-line"></i></button>
                        <a href="/piura_noticias_php/admin/paginas/action?action=delete&delete=<?php echo $p['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" onclick="return confirm('¿Borrar definitivamente la página? Esta acción no se puede deshacer.')" style="background:white; color:#ef4444; border:1px solid #fecaca; padding:6px; border-radius:6px; cursor:pointer; text-decoration:none; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='white'"><i class="ri-delete-bin-fill"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($paginas)): ?>
            <tr>
                <td colspan="5" style="text-align:center; padding: 4rem 2rem; border: none;">
                    <i class="ri-file-text-line" style="font-size: 3rem; color: #cbd5e1; display: block; margin-bottom: 1rem;"></i>
                    <p style="color: var(--text-muted); margin: 0; font-size: 1.1rem;">No hay páginas creadas aún.</p>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- MODAL -->
<div id="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; padding: 2rem 0;">
    <div style="background: white; padding: 2.5rem; border-radius: var(--radius-lg); width: 100%; max-width: 900px; max-height: 95vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem; border-bottom:2px solid var(--border-color); padding-bottom:1rem;">
            <h2 id="modal-title" style="margin:0;"><i class="ri-file-text-line" style="color:var(--primary-color);"></i> Nueva Página</h2>
            <i class="ri-close-line" style="cursor:pointer; font-size:1.5rem; background:#f1f5f9; border-radius:50%; padding:0.25rem;" onclick="document.getElementById('modal').style.display='none'"></i>
        </div>
        
        <form method="POST" action="/piura_noticias_php/admin/paginas/store">
            <input type="hidden" name="action" id="form-action" value="create">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id" id="form-id" value="">
            
            <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                <div class="form-row" style="flex: 2; min-width: 300px;">
                    <label style="font-weight: 700; color: #334155;">Título de la Página</label>
                    <input type="text" name="titulo" id="titulo" required placeholder="Ej: Staff de Periodistas" style="width: 100%; padding: 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1.1rem; background: #f8fafc; transition: all 0.2s;" onfocus="this.style.background='white'; this.style.borderColor='var(--primary-color)';">
                </div>
                
                <div class="form-row" style="flex: 1; min-width: 200px;">
                    <label style="font-weight: 700; color: #334155;">Estado de Publicación</label>
                    <select name="estado" id="estado" style="width: 100%; padding: 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; background: #f8fafc; font-weight: 600;">
                        <option value="activo">ACTIVO (Público)</option>
                        <option value="inactivo">INACTIVO (Oculto)</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row" style="margin-top: 1rem;">
                <label style="font-weight: 700; color: #334155;">Contenido <span style="font-weight: normal; color: #94a3b8; font-size: 0.85rem;">(HTML permitido)</span></label>
                <div style="border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden;">
                    <textarea name="contenido" id="contenido_editor" rows="18"></textarea>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal').style.display='none'" style="padding:0.75rem 1.5rem; font-size:1rem; border-radius: 8px;"><i class="ri-close-line"></i> Cancelar</button>
                <button type="submit" class="btn btn-primary" style="padding:0.75rem 2rem; font-size:1rem; border-radius: 8px;"><i class="ri-save-3-line"></i> Guardar Página</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#contenido_editor',
        language: 'es',
        language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n/langs6/es.min.js',
        menubar: false,
        plugins: 'lists link image media preview code table wordcount',
        toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | code',
        setup: function (editor) {
            editor.on('change', function () {
                tinymce.triggerSave();
            });
        }
    });

    function openModal() {
        document.getElementById('modal-title').innerHTML = '<i class="ri-file-text-line" style="color:var(--primary-color);"></i> Nueva Página';
        document.getElementById('form-action').value = 'create';
        document.getElementById('form-id').value = '';
        document.getElementById('titulo').value = '';
        document.getElementById('estado').value = 'activo';
        if (tinymce.get('contenido_editor')) {
            tinymce.get('contenido_editor').setContent('');
        }
        document.getElementById('modal').style.display = 'flex';
    }

    function editPagina(id) {
        // Obtener datos vía fetch MVC route
        fetch('/piura_noticias_php/admin/paginas/get?id=' + id)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert('Error al obtener la página.');
                    return;
                }
                document.getElementById('modal-title').innerHTML = '<i class="ri-edit-box-line" style="color:var(--primary-color);"></i> Editar Página';
                document.getElementById('form-action').value = 'update';
                document.getElementById('form-id').value = data.id;
                document.getElementById('titulo').value = data.titulo;
                document.getElementById('estado').value = data.estado;
                if (tinymce.get('contenido_editor')) {
                    tinymce.get('contenido_editor').setContent(data.contenido);
                } else {
                    document.getElementById('contenido_editor').value = data.contenido;
                }
                document.getElementById('modal').style.display = 'flex';
            })
            .catch(err => {
                console.error('Error fetching page:', err);
                alert('Hubo un error al conectar con el servidor.');
            });
    }
</script>
