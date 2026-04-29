<?php
// app/Views/admin/optimizador/index.php
// Variables: $msg, $active_tab, $gd_installed, $unoptimized, $orphaned, $total_wasted_bytes, $total_current_bytes, $total_orphan_bytes, $controller
?>
<style>
    .alert-info { background: #dcfce7; color: #166534; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 2rem; font-weight: 600; box-shadow:0 4px 6px rgba(0,0,0,0.05);}
    .alert-error { background: #fee2e2; color: #991b1b; padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 2rem; font-weight: 600; }
    
    .opt-card { background:white; padding:1.5rem; border-radius:12px; border:1px solid #e5e7eb; box-shadow:0 10px 15px -3px rgba(0,0,0,0.05); display:flex; align-items:center; gap:1.5rem; transition: transform 0.2s;} .opt-card:hover { transform: translateY(-5px); }
    .opt-card-icon { width:60px; height:60px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:2rem; }
    .opt-card-blue { background:rgba(59, 130, 246, 0.1); color:#3b82f6;}
    .opt-card-green { background:rgba(16, 185, 129, 0.1); color:#10b981;}
    .opt-val { font-size:1.75rem; font-weight:800; color:#111827; margin:0;}
    .opt-label { font-size:0.85rem; color:#6b7280; font-weight:600;}

    .table-container { background:white; border-radius:12px; border:1px solid #e5e7eb; box-shadow:0 10px 15px -3px rgba(0,0,0,0.05); overflow-x:auto; }
    .data-table { width:100%; border-collapse:collapse; text-align:left; }
    .data-table th { background:#f9fafb; font-weight:600; color:#374151; padding:1rem; border-bottom:1px solid var(--border-color); }
    .data-table td { padding:1rem; border-bottom:1px solid var(--border-color); color:#4b5563; font-size:0.9rem; }
    .data-table tbody tr:hover { background:#f3f4f6; }
    
    .data-table input[type="checkbox"] { width:18px; height:18px; accent-color:var(--primary-color); cursor:pointer;}
    
    .badge-img { padding:4px 8px; border-radius:4px; font-weight:600; font-size:0.75rem; text-transform:uppercase; background:#e5e7eb; color:#374151;}
    .badge-jpg { background:#fee2e2; color:#b91c1c; }
    .badge-png { background:#e0e7ff; color:#4338ca; }
    .badge-ghost { background:#1f2937; color:white; }

    .bulk-bar { position:fixed; bottom:-100px; left:50%; transform:translateX(-50%); background:#1f2937; padding:1.25rem 2.5rem; border-radius:40px; display:flex; gap:2.5rem; align-items:center; z-index:1000; box-shadow:0 15px 35px rgba(0,0,0,0.5); transition:bottom 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); color:white; }
    .bulk-bar.visible { bottom: 30px; }
    .btn-magic { background:linear-gradient(135deg, #3b82f6, #8b5cf6); border:none; color:white; padding:0.75rem 2rem; border-radius:40px; font-weight:800; font-size:1rem; cursor:pointer; display:flex; align-items:center; gap:0.5rem; box-shadow:0 10px 15px -3px rgba(139, 92, 246, 0.5); transition:transform 0.2s;}
    .btn-magic:hover { transform:scale(1.05); }
    .btn-danger-rounded { background:linear-gradient(135deg, #ef4444, #b91c1c); border:none; color:white; padding:0.75rem 2rem; border-radius:40px; font-weight:800; font-size:1rem; cursor:pointer; display:flex; align-items:center; gap:0.5rem; box-shadow:0 10px 15px -3px rgba(239, 68, 68, 0.5); transition:transform 0.2s; }
    .btn-danger-rounded:hover { transform:scale(1.05); }
    
    @keyframes spin { 100% { transform: rotate(360deg); } }

    .tab-btn { background:white; box-shadow:0 2px 4px rgba(0,0,0,0.05); color:#6b7280; padding:0.75rem 1.5rem; border-radius:30px; border:1px solid #d1d5db; font-weight:600; cursor:pointer; font-size:0.95rem; transition:all 0.2s; margin-bottom: 0.5rem;}
    .tab-btn.active-webp { background:#1f2937; color:white; border-color:#1f2937; }
    .tab-btn.active-ghosts { background:#dc2626; color:white; border-color:#dc2626; }
</style>

<?php if ($msg): ?>
    <div class="<?php echo strpos($msg, 'Error') !== false ? 'alert-error' : 'alert-info'; ?>"><i class="ri-checkbox-circle-fill"></i> <?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="admin-header">
    <div>
        <h1 style="margin:0;"><i class="ri-paint-brush-line" style="color:var(--primary-color);"></i> Suite del Optimizador</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Fuerza la compresión WebP por hardware o purga los Archivos Huérfanos de tu sistema.</p>
    </div>
</div>

<div style="display:flex; gap:1rem; margin-bottom:2rem; border-bottom:2px solid #e5e7eb; padding-bottom:1rem; flex-wrap:wrap;">
    <button onclick="switchTab('webp')" id="tab-webp" class="tab-btn <?= $active_tab === 'webp' ? 'active-webp' : '' ?>"><i class="ri-image-edit-fill"></i> Compresor JPG/PNG</button>
    <button onclick="switchTab('ghosts')" id="tab-ghosts" class="tab-btn <?= $active_tab === 'ghosts' ? 'active-ghosts' : '' ?>"><i class="ri-ghost-line"></i> Archivos Huérfanos</button>
</div>

<!-- TAB 1: WEBP -->
<div id="section-webp" style="display:<?= $active_tab === 'webp' ? 'block' : 'none' ?>;">
    <?php if (!$gd_installed): ?>
        <div class="alert-error" style="border-left:4px solid #b91c1c; display:flex; gap:1.5rem; align-items:flex-start;">
            <i class="ri-error-warning-fill" style="font-size:2.5rem; margin-top:-5px;"></i>
            <div style="font-weight:400;">
                <strong style="font-size:1.15rem; display:block; margin-bottom: 0.15rem;">Motor Gráfico de PHP Apagado (Librería GD)</strong>
                Para el compresor WebP, debes activar <code>extension=gd</code> en tu php.ini.
            </div>
        </div>
    <?php endif; ?>

    <!-- KPIs -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1.5rem; margin-bottom:2rem;">
        <div class="opt-card">
            <div class="opt-card-icon opt-card-blue"><i class="ri-image-add-fill"></i></div>
            <div>
                <p class="opt-label">CRUDOS DETECTADOS</p>
                <h3 class="opt-val"><?= count($unoptimized) ?> <span style="font-size:1rem; color:#9ca3af; font-weight:400;">Archivos</span></h3>
            </div>
        </div>
        <div class="opt-card">
            <div class="opt-card-icon" style="background:rgba(245, 158, 11, 0.1); color:#f59e0b;"><i class="ri-hard-drive-2-fill"></i></div>
            <div>
                <p class="opt-label">PESO TOTAL ACTUAL</p>
                <h3 class="opt-val"><?= $controller->publicFormatBytes($total_current_bytes) ?> <span style="font-size:1rem; color:#9ca3af; font-weight:400;">Ocupados</span></h3>
            </div>
        </div>
        <div class="opt-card">
            <div class="opt-card-icon opt-card-green"><i class="ri-leaf-fill"></i></div>
            <div>
                <p class="opt-label">IMPACTO ESTIMADO SEO</p>
                <h3 class="opt-val">-<?= $controller->publicFormatBytes($total_wasted_bytes) ?> <span style="font-size:1rem; color:#10b981; font-weight:400;">Ahorrados</span></h3>
            </div>
        </div>
    </div>

    <form id="optimizeForm" action="<?= base_url('/') ?>admin/optimizador/action" method="POST">
        <input type="hidden" name="optimize_files" value="1">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="webp_quality" id="hiddenQuality" value="80">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px;"><input type="checkbox" id="selectAllWebp" title="Seleccionar Todo"></th>
                        <th>Minuatura Original</th>
                        <th>Formato Original</th>
                        <th style="text-align:right;">Peso Actual</th>
                        <th style="text-align:right;">Estimación WebP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($unoptimized)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:3rem; color:#9ca3af;"><i class="ri-check-double-fill" style="font-size:3rem; color:#10b981; margin-bottom:1rem; display:block;"></i> 100% optimizado.</td></tr>
                    <?php else: ?>
                        <?php foreach($unoptimized as $u): $badge = $u['ext']==='png' ? 'badge-png' : 'badge-jpg'; ?>
                        <tr>
                            <td><input type="checkbox" name="selected_files[]" value="<?= htmlspecialchars($u['rel_path']) ?>" class="chk-webp" data-size="<?= $u['size'] ?>" data-savings="<?= $u['est_savings'] ?>"></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:1rem;">
                                    <div style="width:60px; height:45px; border-radius:6px; background:#e5e7eb; overflow:hidden;"><img src="<?= $u['path'] ?>" style="width:100%; height:100%; object-fit:cover;"></div>
                                    <div style="font-weight:600; color:#111827; max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= htmlspecialchars($u['name']) ?>"><?= htmlspecialchars($u['name']) ?></div>
                                </div>
                            </td>
                            <td><span class="badge-img <?= $badge ?>"><?= $u['ext'] ?></span> <span style="font-size:0.75rem; color:#6b7280; font-family:monospace;"><?= htmlspecialchars($u['rel_path']) ?></span></td>
                            <td style="text-align:right; font-weight:600;"><?= $controller->publicFormatBytes($u['size']) ?></td>
                            <td style="text-align:right; font-weight:800; color:#10b981;">~<?= $controller->publicFormatBytes($u['size'] - $u['est_savings']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<!-- TAB 2: GHOSTS -->
<div id="section-ghosts" style="display:<?= $active_tab === 'ghosts' ? 'block' : 'none' ?>;">
    
    <div class="opt-card" style="margin-bottom:2rem; background:#fff1f2; border-color:#fecdd3;">
        <div class="opt-card-icon" style="background:#fecaca; color:#dc2626;"><i class="ri-ghost-fill"></i></div>
        <div>
            <p class="opt-label" style="color:#e11d48;">ESPACIO DESPERDICIADO FANTASMA</p>
            <h3 class="opt-val" style="color:#be123c;"><?= $controller->publicFormatBytes($total_orphan_bytes) ?> <span style="font-size:1rem; font-weight:400;">(<?= count($orphaned) ?> huérfanos)</span></h3>
            <p style="margin:0; font-size:0.8rem; margin-top:0.25rem; color:#9f1239;">Escaneamos la BD completa (Noticias, Perfiles). Estos archivos ya NO están en uso.</p>
        </div>
    </div>

    <form id="ghostForm" action="<?= base_url('/') ?>admin/optimizador/action" method="POST">
        <input type="hidden" name="delete_ghosts" value="1">
        <?php echo csrf_field(); ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px;"><input type="checkbox" id="selectAllGhosts" title="Seleccionar Todo"></th>
                        <th>Archivo Huérfano</th>
                        <th>Tipo</th>
                        <th style="text-align:right;">Peso en Servidor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orphaned)): ?>
                    <tr><td colspan="4" style="text-align:center; padding:3rem; color:#9ca3af;"><i class="ri-shield-check-fill" style="font-size:3rem; color:#3b82f6; margin-bottom:1rem; display:block;"></i> Cero archivos fantasma. Tu limpieza es impecable.</td></tr>
                    <?php else: ?>
                        <?php foreach($orphaned as $o): ?>
                        <tr>
                            <td><input type="checkbox" name="ghost_files[]" value="<?= htmlspecialchars($o['rel_path']) ?>" class="chk-ghost" data-size="<?= $o['size'] ?>"></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:1rem;">
                                    <div style="width:40px; height:40px; border-radius:6px; background:#e5e7eb; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                                        <?php if(in_array($o['ext'], ['jpg','png','webp','gif'])): ?>
                                            <img src="<?= $o['path'] ?>" style="width:100%; height:100%; object-fit:cover;">
                                        <?php else: ?>
                                            <i class="ri-film-line" style="color:#9ca3af; font-size:1.5rem;"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div style="font-weight:600; color:#111827; max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= htmlspecialchars($o['name']) ?>"><?= htmlspecialchars($o['name']) ?></div>
                                        <div style="font-size:0.75rem; color:#ef4444; font-family:monospace; margin-top:2px;"><?= htmlspecialchars($o['rel_path']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge-img badge-ghost"><?= $o['ext'] ?></span></td>
                            <td style="text-align:right; font-weight:800; color:#4b5563;"><?= $controller->publicFormatBytes($o['size']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<!-- Barra WEBP -->
<div class="bulk-bar" id="barWebp">
    <div style="display:flex; flex-direction:column;">
        <span style="font-weight:600; font-size:1.15rem; color:#e5e7eb;"><span id="selCountWebp" style="color:white; font-size:1.5rem;">0</span> Archivos</span>
        <span style="font-size:0.85rem; color:#9ca3af;">Ahorro: <strong id="selSavingsWebp" style="color:#10b981;">0 KB</strong></span>
    </div>
    <div style="display:flex; gap:1.5rem; align-items:center;">
        <button type="button" onclick="confirmWebp()" class="btn-magic"><i class="ri-magic-line"></i> Comprimir a WebP</button>
    </div>
</div>

<!-- Barra HUÉRFANOS -->
<div class="bulk-bar" id="barGhosts" style="background:#7f1d1d;">
    <div style="display:flex; flex-direction:column;">
        <span style="font-weight:600; font-size:1.15rem; color:#fca5a5;"><span id="selCountGhosts" style="color:white; font-size:1.5rem;">0</span> Huérfanos marcados</span>
        <span style="font-size:0.85rem; color:#fca5a5;">A liberar: <strong id="selSizeGhosts" style="color:white;">0 KB</strong></span>
    </div>
    <div style="display:flex; gap:1.5rem; align-items:center;">
        <button type="button" onclick="confirmGhosts()" class="btn-danger-rounded"><i class="ri-fire-fill"></i> Destruir Huérfanos</button>
    </div>
</div>

<!-- Modal WebP -->
<div id="modalWebp" style="position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:2000; display:none; align-items:center; justify-content:center; opacity:0; transition:opacity 0.2s;">
    <div id="modalWebpBox" style="background:white; padding:2.5rem; border-radius:12px; max-width:450px; width:90%; text-align:center; box-shadow:0 10px 25px rgba(0,0,0,0.2); transform:scale(0.9); transition:transform 0.2s;">
        <h3 style="margin-top:0; font-size:1.5rem; color:#111827; margin-bottom: 0.15rem;"><i class="ri-settings-4-fill" style="color:#3b82f6;"></i> Nivel de Compresión</h3>
        <p style="color:#6b7280; font-size:0.95rem; margin-bottom:1.5rem;">Ajusta la agresividad de WebP para <strong id="modalCountTextWebp" style="color:#111827;">0</strong> archivos.</p>
        
        <div style="margin-bottom: 2rem; text-align:left; background:#f9fafb; padding:1.5rem; border-radius:8px; border:1px solid #e5e7eb;">
            <label style="font-weight:800; display:block; margin-bottom:1rem; font-size:0.95rem; color:#111827;">Calidad WebP (Slider): <span id="qValue" style="color:#3b82f6; font-size:1.2rem;">80</span>%</label>
            <input type="range" id="qSlider" min="50" max="100" value="80" style="width:100%; accent-color:#3b82f6; cursor:ew-resize;" oninput="document.getElementById('qValue').textContent = this.value; document.getElementById('hiddenQuality').value = this.value;">
            <div style="display:flex; justify-content:space-between; font-size:0.8rem; color:#6b7280; margin-top:0.5rem; font-weight:600;">
                <span>50% (Max Ahorro)</span>
                <span>100% (Sin Pérdida)</span>
            </div>
        </div>

        <div style="display:flex; gap:1rem; justify-content:center;">
            <button type="button" onclick="hideModalWebp()" style="padding:0.75rem 1.5rem; border:1px solid #d1d5db; background:white; cursor:pointer; font-weight:600; border-radius:6px; color:#374151;">Cancelar</button>
            <button type="button" id="btnExecuteWebp" onclick="execWebp()" style="padding:0.75rem 1.5rem; background:#3b82f6; border:none; cursor:pointer; font-weight:600; border-radius:6px; color:white;"><i class="ri-magic-line"></i> INICIAR COMPRESIÓN</button>
        </div>
    </div>
</div>

<!-- Modal Ghosts -->
<div id="modalGhosts" style="position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:2000; display:none; align-items:center; justify-content:center; opacity:0; transition:opacity 0.2s;">
    <div id="modalGhostsBox" style="background:#111827; padding:2.5rem; border-radius:12px; max-width:450px; width:90%; text-align:center; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5); transform:scale(0.9); transition:transform 0.2s; border:1px solid #374151;">
        <div style="background:#7f1d1d; color:#fca5a5; width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:3rem; margin:0 auto 1.5rem auto;">
            <i class="ri-error-warning-fill"></i>
        </div>
        <h3 style="margin-top:0; font-size:1.5rem; color:white; margin-bottom: 0.5rem;">ALERTA DE PELIGRO</h3>
        <p style="color:#9ca3af; font-size:0.95rem; margin-bottom:2rem; line-height:1.5;">Estás a punto de <strong style="color:#fca5a5;">ELIMINAR FÍSICAMENTE</strong> <span id="modalCountTextGhosts" style="color:#ef4444; font-weight:bold; font-size:1.2rem;">0</span> archivos de tu disco duro. Esta acción es definitiva y no se puede deshacer.</p>
        
        <div style="display:flex; gap:1rem; justify-content:center;">
            <button type="button" onclick="hideModalGhosts()" style="padding:0.75rem 1.5rem; border:1px solid #374151; background:transparent; cursor:pointer; font-weight:600; border-radius:6px; color:#9ca3af; transition:all 0.2s;">Cancelar</button>
            <button type="button" id="btnExecuteGhosts" onclick="execGhosts()" style="padding:0.75rem 1.5rem; background:#dc2626; border:none; cursor:pointer; font-weight:600; border-radius:6px; color:white; transition:all 0.2s;"><i class="ri-fire-fill"></i> ANIQUILAR AHORA</button>
        </div>
    </div>
</div>

<script>
    function formatBytesJS(bytes) {
        if (!+bytes) return '0 Bytes';
        const k = 1024, sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'], i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`;
    }

    const chksWebp = document.querySelectorAll('.chk-webp');
    const chksGhost = document.querySelectorAll('.chk-ghost');
    
    function updateBars() {
        const wLen = document.querySelectorAll('.chk-webp:checked').length;
        const gLen = document.querySelectorAll('.chk-ghost:checked').length;
        
        let wSav = 0; document.querySelectorAll('.chk-webp:checked').forEach(c => wSav += parseFloat(c.getAttribute('data-savings')));
        let gSz = 0; document.querySelectorAll('.chk-ghost:checked').forEach(c => gSz += parseFloat(c.getAttribute('data-size')));
        
        document.getElementById('selCountWebp').textContent = wLen;
        document.getElementById('selSavingsWebp').textContent = '-' + formatBytesJS(wSav);
        
        document.getElementById('selCountGhosts').textContent = gLen;
        document.getElementById('selSizeGhosts').textContent = formatBytesJS(gSz);
        
        if(wLen > 0 && document.getElementById('section-webp').style.display !== 'none') { document.getElementById('barWebp').classList.add('visible'); } else { document.getElementById('barWebp').classList.remove('visible'); }
        if(gLen > 0 && document.getElementById('section-ghosts').style.display === 'block') { document.getElementById('barGhosts').classList.add('visible'); } else { document.getElementById('barGhosts').classList.remove('visible'); }
    }

    chksWebp.forEach(c => c.addEventListener('change', updateBars));
    chksGhost.forEach(c => c.addEventListener('change', updateBars));
    
    if(document.getElementById('selectAllWebp')) document.getElementById('selectAllWebp').addEventListener('change', function(){ chksWebp.forEach(c => c.checked = this.checked); updateBars(); });
    if(document.getElementById('selectAllGhosts')) document.getElementById('selectAllGhosts').addEventListener('change', function(){ chksGhost.forEach(c => c.checked = this.checked); updateBars(); });

    document.querySelectorAll('tbody tr').forEach(tr => {
        tr.addEventListener('click', function(e) {
            if(e.target.tagName !== 'INPUT' && e.target.tagName !== 'LABEL') {
                const c = this.querySelector('input[type="checkbox"]');
                if(c) { c.checked = !c.checked; updateBars(); }
            }
        });
    });

    function switchTab(t) {
        document.getElementById('section-webp').style.display = t==='webp' ? 'block' : 'none';
        document.getElementById('section-ghosts').style.display = t==='ghosts' ? 'block' : 'none';
        
        document.getElementById('tab-webp').className = t==='webp' ? 'tab-btn active-webp' : 'tab-btn';
        document.getElementById('tab-ghosts').className = t==='ghosts' ? 'tab-btn active-ghosts' : 'tab-btn';
        updateBars();
        
        const url = new URL(window.location);
        url.searchParams.set('tab', t);
        window.history.pushState({}, '', url);
    }

    function confirmWebp() {
        document.getElementById('modalCountTextWebp').textContent = document.querySelectorAll('.chk-webp:checked').length;
        const m = document.getElementById('modalWebp');
        m.style.display = 'flex';
        setTimeout(() => { m.style.opacity = '1'; document.getElementById('modalWebpBox').style.transform = 'scale(1)'; }, 10);
    }
    function hideModalWebp() {
        const m = document.getElementById('modalWebp');
        m.style.opacity = '0';
        document.getElementById('modalWebpBox').style.transform = 'scale(0.9)';
        setTimeout(() => m.style.display = 'none', 200);
    }
    function execWebp() {
        const b = document.getElementById('btnExecuteWebp');
        b.innerHTML = '<i class="ri-loader-4-line" style="animation: spin 1s linear infinite;"></i> PROCESANDO...';
        b.style.pointerEvents = 'none';
        document.getElementById('optimizeForm').submit();
    }

    function confirmGhosts() {
        document.getElementById('modalCountTextGhosts').textContent = document.querySelectorAll('.chk-ghost:checked').length;
        const m = document.getElementById('modalGhosts');
        m.style.display = 'flex';
        setTimeout(() => { m.style.opacity = '1'; document.getElementById('modalGhostsBox').style.transform = 'scale(1)'; }, 10);
    }
    
    function hideModalGhosts() {
        const m = document.getElementById('modalGhosts');
        m.style.opacity = '0';
        document.getElementById('modalGhostsBox').style.transform = 'scale(0.9)';
        setTimeout(() => m.style.display = 'none', 200);
    }
    
    function execGhosts() {
        const b = document.getElementById('btnExecuteGhosts');
        b.innerHTML = '<i class="ri-loader-4-line" style="animation: spin 1s linear infinite;"></i> PURGANDO...';
        b.style.pointerEvents = 'none';
        document.getElementById('ghostForm').submit();
    }
</script>
