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

<table>
    <thead>
        <tr>
            <th>Título</th>
            <th>URL / Archivo (Slug)</th>
            <th>Estado</th>
            <th>Última Modificación</th>
            <th style="text-align:right;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($paginas as $p): ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($p['titulo']); ?></strong></td>
            <td><a href="/piura_noticias_php/pagina/<?php echo $p['slug']; ?>" target="_blank" style="color:var(--primary-color);"><i class="ri-external-link-line"></i> /pagina/<?php echo $p['slug']; ?></a></td>
            <td>
                <span class="badge <?php echo $p['estado']==='activo' ? 'badge-activo' : 'badge-inactivo'; ?>">
                    <?php echo strtoupper($p['estado']); ?>
                </span>
            </td>
            <td><?php echo $p['fecha_modificacion']; ?></td>
            <td style="text-align:right;">
                <button onclick='editPagina(<?php echo $p['id']; ?>)' style="background:#e0f2fe; color:#0369a1; border:none; padding:6px 10px; border-radius:4px; cursor:pointer;" title="Editar"><i class="ri-edit-2-line"></i></button>
                <a href="/piura_noticias_php/admin/paginas/action?action=delete&delete=<?php echo $p['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" onclick="return confirm('¿Borrar definitivamente?')" style="background:#fee2e2; color:#b91c1c; border:none; padding:6px 10px; border-radius:4px; cursor:pointer; text-decoration:none;"><i class="ri-delete-bin-line"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($paginas)): ?>
        <tr><td colspan="5" style="text-align:center; padding: 2rem;">No hay páginas creadas.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

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
            
            <div class="form-row">
                <label>Título de la Página</label>
                <input type="text" name="titulo" id="titulo" required placeholder="Ej: Staff de Periodistas">
            </div>
            
            <div class="form-row">
                <label>Contenido</label>
                <textarea name="contenido" id="contenido_editor" rows="15"></textarea>
            </div>

            <div class="form-row">
                <label>Estado</label>
                <select name="estado" id="estado">
                    <option value="activo">ACTIVO (Público)</option>
                    <option value="inactivo">INACTIVO (Oculto)</option>
                </select>
            </div>
            
            <div style="text-align:right; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary" style="padding:1rem 2rem; font-size:1.1rem;"><i class="ri-save-3-line"></i> Guardar Página</button>
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
        fetch('/admin/paginas/get?id=' + id)
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
