<?php
// app/Views/admin/multimedia/index.php
// Variables: $files, $msg, $user_role, $user_name

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
    $bytes /= pow(1024, $pow); 
    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 
?>

<style>
    .filters-bar { display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap; background:white; padding:1.5rem; border-radius:var(--radius-md); box-shadow:var(--shadow-sm); border:1px solid var(--border-color); align-items:center; }
    .filters-bar input, .filters-bar select { padding: 0.45rem 1rem; border: 1px solid #d1d5db; border-radius: 6px; outline:none; font-family:inherit; color:#111827; box-sizing:border-box; }
    .filters-bar select { min-width: 160px; flex-shrink: 0; }
    .filters-bar input:focus, .filters-bar select:focus { border-color:var(--primary-color); }
    
    .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; }
    .media-card { background: white; border-radius: var(--radius-md); border: 1px solid var(--border-color); overflow: hidden; box-shadow: var(--shadow-sm); position:relative; }
    .media-card-selected { border: 2px solid var(--primary-color); transform:scale(0.98); opacity:0.9; }
    
    .media-preview { height: 160px; background: #e5e7eb; display:flex; align-items:center; justify-content:center; overflow:hidden; border-bottom:1px solid var(--border-color);}
    .media-preview img, .media-preview video { width: 100%; height: 100%; object-fit: contain; pointer-events:none; background:#f9fafb;}
    
    .media-info { padding: 1rem; font-size: 0.8rem; }
    .media-title { font-weight: 600; color: #111827; margin-bottom: 0.4rem; word-break: break-all; line-height: 1.3; font-size: 0.85rem; }
    .media-meta { color: var(--text-muted); display:flex; justify-content:space-between; margin-top:0.5rem; border-top:1px solid #e5e7eb; padding-top:0.5rem; }
    
    .media-actions { position: absolute; inset: 0; background: rgba(0,0,0,0.65); display: flex; gap: 0.4rem; align-items: center; justify-content: center; opacity: 0; transition: all 0.2s; z-index: 20; padding:1rem;}
    .media-card:hover .media-actions { opacity: 1; }
    .media-actions button, .media-actions a { background: white; color: #111827; border: none; width: 36px; height: 36px; font-size: 1.1rem; border-radius: 50%; display:flex; align-items:center; justify-content:center; cursor: pointer; text-decoration:none; transition: all 0.2s ease; flex-shrink:0;}
    .media-actions button:hover, .media-actions a:hover { background: var(--primary-color); color: white; transform: scale(1.15); }
    
    .card-checkbox { position:absolute; top:0.5rem; left:0.5rem; z-index:25; width:22px; height:22px; accent-color:var(--primary-color); cursor:pointer;}
    
    .alert-info { background: #dcfce7; color: #166534; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-weight: 600; }
    .alert-error { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-weight: 600; }
    .upload-zone { border: 2px dashed var(--border-color); padding: 3rem; text-align: center; border-radius: var(--radius-md); background: #fafafa; margin-bottom: 2rem; cursor:pointer;}
    .upload-zone:hover { background:white; border-color:var(--primary-color); }
    
    .toast { visibility: hidden; min-width: 250px; background-color: #111827; color: #fff; text-align: center; border-radius: 8px; padding: 12px 24px; position: fixed; z-index: 1000; left: 50%; bottom: 30px; transform: translateX(-50%); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content:center; gap: 8px; font-weight: 600; font-size: 0.95rem; opacity: 0; transition: opacity 0.3s, bottom 0.3s; pointer-events:none; }
    .toast.show { visibility: visible; opacity: 1; bottom: 50px; }
    
    .bulk-bar { position:fixed; bottom:-100px; left:50%; transform:translateX(-50%); background:#1f2937; padding:1rem 2rem; border-radius:30px; display:flex; gap:2rem; align-items:center; z-index:1000; box-shadow:0 10px 25px rgba(0,0,0,0.5); transition:bottom 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); color:white; }
    .bulk-bar.visible { bottom: 30px; }
    
    #deleteModal { position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:2000; display:none; align-items:center; justify-content:center; opacity:0; transition:opacity 0.2s;}
    #deleteModal.active { opacity:1; }
    #deleteModalBox { background:white; padding:2.5rem; border-radius:12px; max-width:400px; width:90%; text-align:center; box-shadow:0 10px 25px rgba(0,0,0,0.2); transform:scale(0.9); transition:transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    #deleteModal.active #deleteModalBox { transform:scale(1); }
</style>

<?php if ($msg): ?>
    <div class="<?php echo strpos($msg, 'Error') !== false ? 'alert-error' : 'alert-info'; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="admin-header">
    <div>
        <h1 style="margin:0;">Gestor Multimedia</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Administra las imágenes y videos subidos al servidor.</p>
    </div>
</div>

<form action="/piura_noticias_php/admin/multimedia/action" method="POST" enctype="multipart/form-data" class="upload-zone" onclick="document.getElementById('file_upload').click()">
    <i class="ri-upload-cloud-2-line" style="font-size: 3rem; color: var(--text-muted);"></i>
    <h3 style="margin: 1rem 0 0.5rem;">Subir Archivos al Servidor</h3>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Formatos soportados: JPG, PNG, WEBP, MP4, etc. Puedes seleccionar varios archivos a la vez.</p>
    <input type="file" name="file_upload[]" id="file_upload" multiple accept="image/*,video/mp4,video/webm" style="display:none;" onchange="this.form.submit()">
</form>

<div class="filters-bar">
    <div style="flex-grow:1; position:relative; min-width:250px;">
        <i class="ri-search-line" style="position:absolute; top:50%; left:1rem; transform:translateY(-50%); color:var(--text-muted); font-size:1.2rem;"></i>
        <input type="text" id="searchInput" placeholder="Escribe para filtrar al instante..." style="width:100%; padding-left:3rem;">
    </div>
    <div>
        <select id="typeFilter">
            <option value="all">Tipos (Todos)</option>
            <option value="image">Sólo Imágenes</option>
            <option value="video">Sólo Videos</option>
        </select>
    </div>
    <div>
        <select id="folderFilter">
            <option value="all">Carpetas (Todas)</option>
            <?php 
                $folders = array_unique(array_column($files, 'folder_tag'));
                sort($folders);
                foreach($folders as $fdir):
            ?>
            <option value="<?php echo htmlspecialchars($fdir); ?>">uploads<?php echo htmlspecialchars($fdir); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<form method="POST" action="/piura_noticias_php/admin/multimedia/action" id="bulkForm">
    <input type="hidden" name="bulk_delete" value="yes">
    <?php echo csrf_field(); ?>
    <div class="media-grid" id="mediaGrid">
        <?php if(empty($files)): ?>
            <p style="color:var(--text-muted); grid-column:1/-1;">No hay archivos multimedia en el servidor.</p>
        <?php endif; ?>

        <?php foreach ($files as $f): 
            $is_video = in_array($f['ext'], ['mp4','webm','ogg']);
            $type_tag = $is_video ? 'video' : 'image';
            $full_url = 'http://'.$_SERVER['HTTP_HOST'].'/piura_noticias_php/'.$f['path'];
            
            $meses = ['January' => 'enero', 'February' => 'febrero', 'March' => 'marzo', 'April' => 'abril', 'May' => 'mayo', 'June' => 'junio', 'July' => 'julio', 'August' => 'agosto', 'September' => 'septiembre', 'October' => 'octubre', 'November' => 'noviembre', 'December' => 'diciembre'];
            $file_date = date('j \d\e F \d\e Y', $f['time']);
            $file_date = str_replace(array_keys($meses), array_values($meses), $file_date);
            
            $file_size = formatBytes($f['size']);
            $dimensions = '';
            if (!$is_video) {
                $img_info = @getimagesize($f['path']);
                if ($img_info) {
                    $dimensions = $img_info[0] . ' por ' . $img_info[1] . ' píxeles';
                }
            } else {
                $dimensions = 'N/A';
            }
        ?>
        <div class="media-card filter-item" data-name="<?php echo htmlspecialchars(strtolower($f['name'])); ?>" data-type="<?php echo $type_tag; ?>" data-folder="<?php echo htmlspecialchars($f['folder_tag']); ?>">
            
            <input type="checkbox" name="selected_files[]" value="<?php echo htmlspecialchars($f['rel_path']); ?>" class="card-checkbox" title="Seleccionar archivo">

            <div class="media-actions">
                <button type="button" onclick="openViewModal('<?php echo addslashes(htmlspecialchars($f['name'])); ?>', '<?php echo $full_url; ?>', <?php echo $is_video?'true':'false'; ?>, '<?php echo addslashes(htmlspecialchars($f['rel_path'])); ?>', '<?php echo addslashes($file_date); ?>', '<?php echo addslashes($file_size); ?>', '<?php echo addslashes($dimensions); ?>', '<?php echo addslashes($f['ext']); ?>')" title="Ver Detalles"><i class="ri-eye-line"></i></button>
                <a href="<?php echo '/piura_noticias_php/' . $f['path']; ?>" download="<?php echo htmlspecialchars($f['name']); ?>" title="Descargar al Equipo"><i class="ri-download-2-line"></i></a>
                <button type="button" onclick="copyToClipboard('<?php echo $full_url; ?>')" title="Copiar Enlace Público"><i class="ri-links-line"></i></button>
                <?php if($user_role === 'admin'): ?>
                <a href="#" onclick="showDeleteModal('<?php echo addslashes(htmlspecialchars($f['name'])); ?>', '<?php echo addslashes(htmlspecialchars($f['rel_path'])); ?>', <?php echo $is_video?'true':'false'; ?>, '<?php echo $full_url; ?>'); return false;" title="Mover a Papelera"><i class="ri-delete-bin-line"></i></a>
                <?php endif; ?>
            </div>
            
            <div class="media-preview">
                <?php if ($is_video): ?>
                    <video src="<?php echo '/piura_noticias_php/' . $f['path']; ?>#t=0.5" muted preload="metadata" style="width:100%; height:100%; object-fit:cover; pointer-events:none;"></video>
                    <div style="position:absolute; top:0.5rem; right:0.5rem; background:#111827; color:white; padding:4px 8px; border-radius:6px; font-size:0.75rem; font-weight:600; display:flex; align-items:center; gap:4px; box-shadow:0 2px 4px rgba(0,0,0,0.5);"><i class="ri-movie-2-fill" style="color:#60a5fa;"></i> VIDEO</div>
                <?php else: ?>
                    <img src="<?php echo '/piura_noticias_php/' . $f['path']; ?>" alt="<?php echo htmlspecialchars($f['name']); ?>" style="width:100%; height:100%; object-fit:cover;">
                    <div style="position:absolute; top:0.5rem; right:0.5rem; background:white; color:#111827; padding:4px 8px; border-radius:6px; font-size:0.75rem; font-weight:600; display:flex; align-items:center; gap:4px; box-shadow:0 2px 4px rgba(0,0,0,0.2);"><i class="ri-image-fill" style="color:#f59e0b;"></i> IMAGEN</div>
                <?php endif; ?>
            </div>
            
            <div class="media-info" style="cursor:pointer;" onclick="toggleCardSelection(this)">
                <div class="media-title" title="<?php echo htmlspecialchars($f['name']); ?>"><?php echo htmlspecialchars($f['name']); ?></div>
                <div style="font-size:0.7rem; color:#6b7280; margin-bottom: 0.15rem; word-break:break-all; line-height:1.3;" title="Ruta Completa">
                    <i class="ri-map-pin-2-fill" style="color:var(--primary-color);"></i> uploads/<?php echo htmlspecialchars($f['rel_path']); ?>
                </div>
                <div class="media-meta">
                    <span><?php echo formatBytes($f['size']); ?></span>
                    <span><?php echo date('d/m/Y', $f['time']); ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</form>

<div class="bulk-bar" id="bulkBar">
    <span style="font-weight:600; font-size:1.1rem;"><span id="bulkCount">0</span> archivos seleccionados</span>
    <div style="display:flex; gap:1.5rem; align-items:center;">
        <button type="button" onclick="selectAllVisible()" style="background:transparent; border:none; color:#60a5fa; cursor:pointer; font-weight:600; font-family:inherit; font-size:0.95rem;">Seleccionar Filtrados</button>
        <button type="button" onclick="confirmBulkDelete()" style="background:#ef4444; border:none; color:white; padding:0.6rem 1.75rem; border-radius:40px; font-weight:600; font-size:0.95rem; cursor:pointer; display:flex; align-items:center; gap:0.5rem; box-shadow:0 4px 6px rgba(239,68,68,0.3); transition:transform 0.2s;"><i class="ri-delete-bin-5-fill"></i> Mover Lote a Papelera</button>
        <button type="button" onclick="deselectAll()" style="background:transparent; border:none; color:#9ca3af; cursor:pointer; font-size:1.5rem;" title="Cerrar"><i class="ri-close-circle-fill"></i></button>
    </div>
</div>

<div id="deleteModal">
    <div id="deleteModalBox">
        <h3 style="margin-top:0; font-size:1.5rem; color:#111827; margin-bottom: 0.15rem;"><i class="ri-alert-fill" style="color:#f59e0b;"></i> ¿A la papelera?</h3>
        <p style="color:#6b7280; font-size:0.95rem; margin-bottom:1.5rem;">El archivo se almacenará ahí por 15 días.</p>
        
        <div id="modalPreviewBox" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:1.5rem; margin-bottom:1.5rem; display:flex; flex-direction:column; align-items:center; word-break:break-all;">
        </div>

        <div style="display:flex; gap:1rem; justify-content:center;">
            <button onclick="hideDeleteModal()" style="padding:0.75rem 1.5rem; border:1px solid #d1d5db; background:white; cursor:pointer; font-weight:600; border-radius:6px; color:#374151; font-family:inherit; transition:background 0.2s;">Cancelar</button>
            <a href="#" id="modalConfirmBtn" onclick="executeDelete(this.href); return false;" style="padding:0.75rem 1.5rem; background:#ef4444; border:none; cursor:pointer; font-weight:600; border-radius:6px; color:white; text-decoration:none; display:flex; align-items:center; gap:0.5rem; box-shadow:0 2px 4px rgba(239,68,68,0.3); transition:background 0.2s;"><i class="ri-delete-bin-2-fill"></i> SÍ, ENTENDIDO</a>
        </div>
    </div>
</div>

<div id="viewModal" style="position:fixed; inset:0; background:rgba(240, 240, 241, 0.95); z-index:3000; display:none; align-items:center; justify-content:center; opacity:0; transition:opacity 0.2s;">
    <div style="background:white; max-width:100vw; width:100%; height:100vh; display:flex; flex-direction:column; box-shadow:0 0 15px rgba(0,0,0,0.1); position:relative; box-sizing:border-box;" id="viewModalBox">
        
        <div style="background: white; border-bottom: 1px solid #dcdcde; display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1.5rem; height: 35px; box-sizing: content-box;">
            <h3 style="margin:0; font-size:1.15rem; font-weight:600; color:#1d2327;">Detalles del adjunto</h3>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <button style="background:transparent; border:none; color:#1d2327; font-size:1.5rem; cursor:pointer;" disabled><i class="ri-arrow-left-s-line" style="opacity:0.3;"></i></button>
                <button style="background:transparent; border:none; color:#1d2327; font-size:1.5rem; cursor:pointer;" disabled><i class="ri-arrow-right-s-line" style="opacity:0.3;"></i></button>
                <div style="width:1px; height:24px; background:#dcdcde; margin: 0 0.5rem;"></div>
                <button onclick="closeViewModal()" style="background:transparent; border:none; color:#1d2327; font-size:1.6rem; cursor:pointer; transition:color 0.2s;"><i class="ri-close-line"></i></button>
            </div>
        </div>
        
        <div style="flex-grow:1; display:flex; overflow:hidden;">
            <div id="viewModalContent" style="width:65%; background:#f0f0f1; display:flex; align-items:center; justify-content:center; overflow:hidden; padding:2rem; border-right:1px solid #dcdcde;">
            </div>
            
            <div style="width:35%; background:white; padding:1.5rem; overflow-y:auto; display:flex; flex-direction:column; gap:1.5rem;">
                
                <div style="font-size: 0.8rem; color: #50575e; line-height: 1.5;">
                    <div style="margin-bottom:0.15rem;"><strong style="font-weight:600; color:#1d2327;">Subido el:</strong> <span id="wpDate"></span></div>
                    <div style="margin-bottom:0.15rem;"><strong style="font-weight:600; color:#1d2327;">Subido por:</strong> <span><?php echo $user_name; ?></span></div>
                    <div style="margin-bottom:0.15rem;"><strong style="font-weight:600; color:#1d2327;">Subido a:</strong> <span>(Sin adjuntar)</span></div>
                    <div style="margin-bottom:0.15rem;"><strong style="font-weight:600; color:#1d2327;">Nombre del archivo:</strong> <span id="viewModalTitle"></span></div>
                    <div style="margin-bottom:0.15rem;"><strong style="font-weight:600; color:#1d2327;">Tipo de archivo:</strong> <span id="wpExt"></span></div>
                    <div style="margin-bottom:0.15rem;"><strong style="font-weight:600; color:#1d2327;">Tamaño del archivo:</strong> <span id="wpSize"></span></div>
                    <div style="margin-bottom:0.15rem;"><strong style="font-weight:600; color:#1d2327;">Dimensiones:</strong> <span id="wpDim"></span></div>
                </div>
                
                <div style="border-top:1px solid #dcdcde; padding-top:1.5rem; display:flex; flex-direction:column; gap:1.25rem;">
                    <div style="display:flex; flex-direction:column; gap:0.25rem;">
                        <label style="font-size:0.85rem; font-weight:600; color:#1d2327;">Texto alternativo SEO</label>
                        <input type="text" id="wpAlt" style="padding:0.6rem; border:1px solid #dcdcde; border-radius:6px; outline:none; color:#646970; background:#f9fafb; font-family:inherit;" readonly>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:0.25rem;">
                        <label style="font-size:0.85rem; font-weight:600; color:#1d2327;">Título interno del archivo</label>
                        <input type="text" id="wpTitleField" style="padding:0.6rem; border:1px solid #dcdcde; border-radius:6px; outline:none; color:#646970; background:#f9fafb; font-family:inherit;" readonly>
                    </div>
                    
                    <div style="display:flex; flex-direction:column; gap:0.25rem;">
                        <label style="font-size:0.85rem; font-weight:600; color:#1d2327;">Enlace URL Directo</label>
                        <div style="display:flex; border: 1px solid var(--primary-color); border-radius:6px; overflow:hidden;">
                            <input type="text" id="wpUrl" style="flex-grow:1; padding:0.6rem; border:none; outline:none; background:#eff6ff; color:#1e40af; font-family:monospace; font-size:0.85rem;" readonly>
                            <button type="button" onclick="copyToClipboard(document.getElementById('wpUrl').value)" style="background:var(--primary-color); border:none; color:white; padding:0 1.25rem; font-weight:600; font-family:inherit; cursor:pointer; transition:transform 0.1s;"><i class="ri-file-copy-line"></i> Copiar</button>
                        </div>
                    </div>
                </div>
                
                <div style="border-top:1px solid #dcdcde; margin-top:0.5rem; padding-top:1.5rem; display:flex; justify-content:flex-end; font-size:0.9rem;">
                    <?php if($user_role === 'admin'): ?>
                        <a href="#" id="wpDelete" style="color:#ef4444; border: 1px solid #ef4444; border-radius: 6px; padding: 0.5rem 1rem; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:0.35rem; transition: background 0.2s;"><i class="ri-delete-bin-fill"></i> Enviar a Papelera</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(url) {
        navigator.clipboard.writeText(url).then(() => {
            showToast("¡Enlace copiado al portapapeles!");
        }).catch(() => {
            showToast("No se pudo copiar el enlace");
        });
    }
    function showToast(msg) {
        let toast = document.getElementById("custom-toast");
        if (!toast) {
            toast = document.createElement("div");
            toast.id = "custom-toast";
            toast.className = "toast";
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="ri-checkbox-circle-fill" style="color: #10b981; font-size: 1.25rem;"></i> <span>${msg}</span>`;
        toast.style.display = 'flex';
        setTimeout(() => toast.classList.add("show"), 10);
        setTimeout(() => { toast.classList.remove("show"); }, 3000);
    }

    const searchBox = document.getElementById('searchInput');
    const typeSelect = document.getElementById('typeFilter');
    const folderSelect = document.getElementById('folderFilter');
    const cards = document.querySelectorAll('.filter-item');

    function executeJSFilter() {
        const query = searchBox.value.toLowerCase();
        const typeVar = typeSelect.value;
        const folderVar = folderSelect.value;

        cards.forEach(card => {
            const n = card.getAttribute('data-name');
            const t = card.getAttribute('data-type');
            const f = card.getAttribute('data-folder');

            let shows = true;
            if (query && !n.includes(query)) shows = false;
            if (typeVar !== 'all' && typeVar !== t) shows = false;
            if (folderVar !== 'all' && folderVar !== f) shows = false;

            card.style.display = shows ? 'block' : 'none';
            
            if (!shows) {
                const cb = card.querySelector('.card-checkbox');
                if(cb.checked) {
                    cb.checked = false;
                    card.classList.remove('media-card-selected');
                }
            }
        });
        handleBulkBarVisibility();
    }

    searchBox.addEventListener('input', executeJSFilter);
    typeSelect.addEventListener('change', executeJSFilter);
    folderSelect.addEventListener('change', executeJSFilter);

    const bulkBar = document.getElementById('bulkBar');
    const bulkCount = document.getElementById('bulkCount');
    const allCheckboxes = document.querySelectorAll('.card-checkbox');

    function toggleCardSelection(infoElement) {
        const card = infoElement.closest('.media-card');
        const cb = card.querySelector('.card-checkbox');
        cb.checked = !cb.checked;
        if(cb.checked) card.classList.add('media-card-selected');
        else card.classList.remove('media-card-selected');
        handleBulkBarVisibility();
    }

    allCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const card = this.closest('.media-card');
            if(this.checked) card.classList.add('media-card-selected');
            else card.classList.remove('media-card-selected');
            handleBulkBarVisibility();
        });
    });

    function handleBulkBarVisibility() {
        const count = document.querySelectorAll('.card-checkbox:checked').length;
        bulkCount.textContent = count;
        if (count > 0) {
            bulkBar.classList.add('visible');
        } else {
            bulkBar.classList.remove('visible');
        }
    }

    function selectAllVisible() {
        const visibleInputs = document.querySelectorAll('.media-card[style*="display: block;"], .media-card:not([style*="display"])');
        visibleInputs.forEach(c => {
            const cb = c.querySelector('.card-checkbox');
            cb.checked = true;
            c.classList.add('media-card-selected');
        });
        handleBulkBarVisibility();
    }

    function deselectAll() {
        allCheckboxes.forEach(cb => { cb.checked = false; cb.closest('.media-card').classList.remove('media-card-selected'); });
        handleBulkBarVisibility();
    }

    function releaseLocksAndExecute(callback) {
        document.querySelectorAll('video').forEach(v => { v.removeAttribute('src'); v.load(); });
        setTimeout(callback, 250);
    }

    function confirmBulkDelete() {
        const num = document.querySelectorAll('.card-checkbox:checked').length;
        if(confirm(`Estás asumiendo la eliminación simultánea de ${num} archivos.\n\nMovidos a la papelera (pueden ser restaurados los prox. 15 días).\n¿Proceder con la eliminación en bloque?`)) {
            releaseLocksAndExecute(() => {
                document.getElementById('bulkForm').submit();
            });
        }
    }

    const dModal = document.getElementById('deleteModal');
    const dModalBox = document.getElementById('deleteModalBox');

    function showDeleteModal(name, rel_path, isVideo, url) {
        const previewBox = document.getElementById('modalPreviewBox');
        let inner = '';
        
        if (isVideo) {
            inner = `<div style="background:#1f2937; height:120px; width:100%; border-radius:8px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 6px rgba(0,0,0,0.3); margin-bottom:1rem;"><i class="ri-movie-2-line" style="color:#60a5fa; font-size:3rem;"></i></div>`;
        } else {
            inner = `<img src="${url}" style="max-height:150px; border-radius:8px; margin-bottom:1rem; box-shadow:0 4px 6px rgba(0,0,0,0.1); max-width:100%;">`;
        }
        
        inner += `<strong style="color:#111827; font-size:0.9rem;">${name}</strong>`;
        previewBox.innerHTML = inner;
        
        document.getElementById('modalConfirmBtn').href = '/piura_noticias_php/admin/multimedia/action?action_type=delete&file=' + encodeURIComponent(rel_path) + '&csrf_token=<?php echo csrf_token(); ?>';
        
        dModal.style.display = 'flex';
        setTimeout(() => dModal.classList.add('active'), 10);
    }

    function hideDeleteModal() {
        dModal.classList.remove('active');
        setTimeout(() => dModal.style.display = 'none', 200);
    }

    function executeDelete(ruta) {
        releaseLocksAndExecute(() => {
            window.location.href = ruta;
        });
    }

    const vModal = document.getElementById('viewModal');
    const vModalBox = document.getElementById('viewModalBox');

    function openViewModal(name, url, isVideo, relPath, fDate, fSize, fDim, fExt) {
        const content = document.getElementById('viewModalContent');
        
        document.getElementById('viewModalTitle').textContent = name;
        document.getElementById('wpTitleField').value = name;
        document.getElementById('wpAlt').value = name.replace(/\.[^/.]+$/, "");
        document.getElementById('wpDate').textContent = fDate;
        document.getElementById('wpExt').textContent = isVideo ? 'video/' + fExt : 'image/' + fExt;
        document.getElementById('wpSize').textContent = fSize;
        document.getElementById('wpDim').textContent = fDim;
        document.getElementById('wpUrl').value = url;
        
        <?php if($user_role === 'admin'): ?>
        document.getElementById('wpDelete').onclick = function() {
            closeViewModal();
            setTimeout(() => {
                showDeleteModal(name, relPath, isVideo, url);
            }, 200);
            return false;
        };
        <?php endif; ?>
        
        if (isVideo) {
            content.innerHTML = `<video src="${url}" controls autoplay style="max-width:100%; max-height:100%; object-fit:contain; box-shadow:0 0 15px rgba(0,0,0,0.1); outline:none;"></video>`;
        } else {
            content.innerHTML = `<img src="${url}" style="max-width:100%; max-height:100%; object-fit:contain; box-shadow:0 0 15px rgba(0,0,0,0.1);">`;
        }
        
        vModal.style.display = 'flex';
        setTimeout(() => {
            vModal.style.opacity = '1';
        }, 10);
    }

    function closeViewModal() {
        const content = document.getElementById('viewModalContent');
        const vid = content.querySelector('video');
        if(vid) { vid.pause(); vid.removeAttribute('src'); vid.load(); }
        
        vModal.style.opacity = '0';
        setTimeout(() => { 
            vModal.style.display = 'none'; 
            content.innerHTML = ''; 
        }, 200);
    }
</script>
