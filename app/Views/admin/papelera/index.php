<?php
// app/Views/admin/papelera/index.php
// Variables: $msg, $noticias, $usuarios, $comentarios, $encuestas, $categorias, $publicidad, $paginas, $media_files, $purge_days

function timeLeft($timestamp, $purge_days) {
    $diff = ($timestamp + ($purge_days * 24 * 60 * 60)) - time();
    if ($diff <= 0) return 'Expirando...';
    
    $days = floor($diff / (60 * 60 * 24));
    $diff -= $days * (60 * 60 * 24);
    $hours = floor($diff / (60 * 60));
    $diff -= $hours * (60 * 60);
    $mins = floor($diff / 60);
    
    return "{$days}d {$hours}h {$mins}m";
}
?>

<style>
    .btn-primary { background-color: var(--primary-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: var(--radius-md); cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration:none; }
    .btn-danger { background-color: var(--danger); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; text-decoration:none; display:inline-flex; align-items:center; }
    
    .tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border-color); flex-wrap:wrap;}
    .tab-btn { background: none; border: none; padding: 0.75rem 1.25rem; font-family: var(--font-sans); font-weight: 600; color: var(--text-muted); cursor: pointer; font-size: 0.95rem; border-bottom: 3px solid transparent; margin-bottom: -2px; display:flex; align-items:center; gap:0.5rem;}
    .tab-btn.active { color: var(--primary-color); border-bottom-color: var(--primary-color); }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    
    table { width: 100%; border-collapse: collapse; background: white; box-shadow: var(--shadow-sm); border-radius: var(--radius-md); overflow: hidden; }
    th, td { padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: left; }
    th { background-color: var(--bg-main); color: var(--text-muted); font-size: 0.875rem; text-transform: uppercase; }
    .alert-success { background: #dcfce7; color: #166534; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-weight: 600; }
    
    .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; }
    .media-card { background: white; border-radius: var(--radius-md); border: 1px solid var(--border-color); overflow: hidden; box-shadow: var(--shadow-sm); }
    .media-preview { height: 150px; background: #e5e7eb; display:flex; align-items:center; justify-content:center; overflow:hidden;}
    .media-preview img { width: 100%; height: 100%; object-fit: cover; opacity: 0.7; filter: grayscale(100%); }
    .media-info { padding: 1rem; font-size: 0.8rem; }
    
    .tab-btn .badge { background:#f1f5f9; color:#64748b; border-radius:999px; min-width:24px; height:24px; padding:0 8px; display:inline-flex; justify-content:center; align-items:center; font-size:0.75rem; font-weight:700; margin-left:6px; transition:all 0.2s; }
    .tab-btn.active .badge { background:#fee2e2; color:#ef4444; }
</style>

<?php if ($msg): ?>
    <div class="alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="admin-header">
    <div>
        <h1 style="margin:0;"><i class="ri-delete-bin-fill" style="color:#ef4444;"></i> Papelera Central</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Control total. Los elementos eliminados de cualquier módulo reposarán aquí por 15 días antes de ser purgados de forma definitiva.</p>
    </div>
</div>

<div class="tabs">
    <button class="tab-btn active" onclick="openTab('news')">Noticias <span class="badge"><?=count($noticias)?></span></button>
    <button class="tab-btn" onclick="openTab('media')">Multimedia <span class="badge"><?=count($media_files)?></span></button>
    <button class="tab-btn" onclick="openTab('users')">Usuarios <span class="badge"><?=count($usuarios)?></span></button>
    <button class="tab-btn" onclick="openTab('comentarios')">Comentarios <span class="badge"><?=count($comentarios)?></span></button>
    <button class="tab-btn" onclick="openTab('categorias')">Categorías <span class="badge"><?=count($categorias)?></span></button>
    <button class="tab-btn" onclick="openTab('encuestas')">Encuestas <span class="badge"><?=count($encuestas)?></span></button>
    <button class="tab-btn" onclick="openTab('publicidad')">Publicidad <span class="badge"><?=count($publicidad)?></span></button>
    <button class="tab-btn" onclick="openTab('paginas')">Páginas <span class="badge"><?=count($paginas)?></span></button>
</div>

<div class="cfg-panel" style="padding: 2rem;">
    <div style="margin-bottom: 1.5rem; position:relative; width: 100%; max-width: 400px;">
        <i class="ri-search-line" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:#9ca3af;"></i>
        <input type="text" id="searchInput" placeholder="Buscar en la pestaña actual..." style="padding:0.75rem 1rem 0.75rem 2.5rem; border:1.5px solid #e2e8f0; border-radius:8px; font-family:var(--font-sans); width:100%; box-sizing: border-box; background:#f8fafc; transition:all 0.2s;" onkeyup="filterPapelera()" onfocus="this.style.background='white'; this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)';">
    </div>

<!-- TABS GENERIC GENERATOR -->
<?php 
$tabData = [
    ['id' => 'news', 'data' => $noticias, 'type' => 'news', 'active' => true],
    ['id' => 'users', 'data' => $usuarios, 'type' => 'user', 'col1' => 'nombre_completo'],
    ['id' => 'comentarios', 'data' => $comentarios, 'type' => 'comentario'],
    ['id' => 'categorias', 'data' => $categorias, 'type' => 'categoria'],
    ['id' => 'encuestas', 'data' => $encuestas, 'type' => 'encuesta'],
    ['id' => 'publicidad', 'data' => $publicidad, 'type' => 'publicidad'],
    ['id' => 'paginas', 'data' => $paginas, 'type' => 'pagina'],
];

foreach ($tabData as $t): 
    $activeClass = isset($t['active']) ? 'active' : '';
    $dataList = $t['data'];
?>
<div id="tab-<?=$t['id']?>" class="tab-content <?=$activeClass?>">
    <table id="table-<?=$t['id']?>" class="filterable-table">
        <thead>
            <tr>
                <th>Identificador / Título</th>
                <th>Eliminado el</th>
                <th>Tiempo Restante</th>
                <th style="text-align:right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dataList as $row): 
                $title = isset($t['col1']) ? $row[$t['col1']] : ($row['titulo'] ?? '-'); 
            ?>
            <tr>
                <td><strong style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;"><?=htmlspecialchars($title)?></strong></td>
                <td><?=date('d/m/Y H:i', strtotime($row['deleted_at']))?></td>
                <td style="color:#ef4444; font-weight:600;"><i class="ri-time-line"></i> <?=timeLeft(strtotime($row['deleted_at']), $purge_days)?></td>
                <td style="text-align:right; min-width: 220px;">
                    <a href="/piura_noticias_php/admin/papelera/action?action_type=restore&type=<?=$t['type']?>&id=<?=$row['id']?>&csrf_token=<?=csrf_token()?>" class="btn-primary" style="background:#10b981; margin-right:4px;"><i class="ri-refresh-line"></i> Restaurar</a>
                    <a href="/piura_noticias_php/admin/papelera/action?action_type=delete&type=<?=$t['type']?>&id=<?=$row['id']?>&csrf_token=<?=csrf_token()?>" class="btn-danger" onclick="return confirmDelete(event, '¿Seguro que deseas eliminar definitivamente esto?')"><i class="ri-delete-bin-fill"></i> Borrar</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($dataList)): ?>
            <tr><td colspan="4">
                <div style="text-align: center; padding: 3rem; background: #f8fafc; border-radius: 8px; border: 1px dashed var(--border-color); color: #64748b; margin: 1rem 0;">
                    <i class="ri-delete-bin-line" style="font-size: 3rem; display: block; margin-bottom: 1rem; color: #cbd5e1;"></i>
                    <h3 style="margin: 0; font-size: 1.1rem;">La papelera está vacía para este módulo.</h3>
                </div>
            </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endforeach; ?>

<!-- TAB MULTIMEDIA -->
<div id="tab-media" class="tab-content">
    <div class="media-grid" id="grid-media">
        <?php foreach ($media_files as $f): ?>
        <div class="media-card filterable">
            <div class="media-preview">
                <img src="<?='/piura_noticias_php/' . $f['path']?>" alt="<?=htmlspecialchars($f['name'])?>" onerror="this.outerHTML='<i class=\'ri-file-line\' style=\'font-size:3rem; color:#d1d5db;\'></i>'">
            </div>
            <div class="media-info">
                <div style="font-weight:600; text-overflow:ellipsis; overflow:hidden; white-space:nowrap; margin-bottom:8px;" class="filter-text" title="<?=htmlspecialchars($f['name'])?>"><?=htmlspecialchars($f['name'])?></div>
                <div style="color:#ef4444; font-weight:600; font-size:0.75rem; margin-bottom:12px;"><i class="ri-time-line"></i> <?=timeLeft($f['time'], $purge_days)?></div>
                <div style="display:flex; gap:4px;">
                    <a href="/piura_noticias_php/admin/papelera/action?action_type=restore&type=media&id=<?=urlencode($f['name'])?>&csrf_token=<?=csrf_token()?>" class="btn-primary" style="background:#10b981; padding:4px; width:100%; justify-content:center;" title="Restaurar"><i class="ri-refresh-line"></i></a>
                    <a href="/piura_noticias_php/admin/papelera/action?action_type=delete&type=media&id=<?=urlencode($f['name'])?>&csrf_token=<?=csrf_token()?>" class="btn-danger" style="padding:4px; width:100%; justify-content:center;" title="Borrar Físicamente" onclick="return confirmDelete(event, '¿Eliminar definitivamente este medio?')"><i class="ri-delete-bin-fill"></i></a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (empty($media_files)): ?>
    <div style="text-align: center; padding: 3rem; background: #f8fafc; border-radius: 8px; border: 1px dashed var(--border-color); color: #64748b; margin: 1rem 0; width: 100%; box-sizing: border-box;">
        <i class="ri-folder-image-line" style="font-size: 3rem; display: block; margin-bottom: 1rem; color: #cbd5e1;"></i>
        <h3 style="margin: 0; font-size: 1.1rem;">No hay medios en la papelera.</h3>
    </div>
    <?php endif; ?>
</div>

</div> <!-- End cfg-panel -->

<script>
    function openTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tabName).classList.add('active');
        event.currentTarget.classList.add('active');
        document.getElementById('searchInput').value = '';
        filterPapelera();
    }

    function filterPapelera() {
        const filter = document.getElementById('searchInput').value.toLowerCase();
        const activeTab = document.querySelector('.tab-content.active').id;
        
        if (activeTab === 'tab-media') {
            const items = document.querySelectorAll('#grid-media .filterable');
            items.forEach(item => {
                const text = item.querySelector('.filter-text').innerText.toLowerCase();
                item.style.display = text.includes(filter) ? '' : 'none';
            });
        } else {
            const rows = document.querySelectorAll('#' + activeTab + ' tbody tr');
            rows.forEach(row => {
                if (row.cells.length === 1) return; // empty row
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    }
</script>
