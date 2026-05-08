<?php
// app/Views/admin/reportes/index.php
// Variables: $f_fecha_ini, $f_fecha_fin, $f_autor, $f_categoria, $noticias, $total_vistas, $total_noticias, $promedio_vistas, $lbl_cats, $val_cats, $lbl_top, $val_top, $autores, $categorias_disponibles
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .dashboard-container { width: 100%; max-width: 100%; margin: 0; }
    
    @media print {
        body { background: white; padding: 0; font-size: 10pt; }
        .admin-layout { display: flex; min-height: 100vh; background-color: #f8fafc; }
        .admin-sidebar { width: 260px; background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); color: white; padding: 1.25rem 1rem; display: flex; flex-direction: column; position: fixed; height: 100vh; overflow-y:auto; box-shadow: 4px 0 15px rgba(0,0,0,0.1); border-right: 1px solid rgba(255,255,255,0.05); }
        .admin-main { margin-left: 0 !important; padding: 0 !important; overflow: visible !important; }
        .no-print { display: none !important; }
        .kpi-box { border: 1px solid #ccc !important; box-shadow: none !important; padding: 0.75rem !important; }
        .kpi-box h3 { font-size: 0.75rem !important; }
        .kpi-box p { font-size: 1.5rem !important; }
        .report-card { box-shadow: none !important; border: none !important; margin: 0 !important; padding: 0 !important; }
        .dashboard-container { max-width: 100% !important; width: 100% !important; }
        .header-rep { margin-bottom: 1rem !important; padding-bottom: 0.5rem !important; }
        .header-rep h1 { font-size: 1.5rem !important; }
        table.rep-table th, table.rep-table td { padding: 4px 6px !important; font-size: 9pt !important; border-bottom: 1px solid #e2e8f0 !important; }
        table.rep-table { width: 100% !important; }
    }
    
    .header-rep { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--primary-color); padding-bottom: 1rem; margin-bottom: 2rem; }
    .header-rep h1 { margin: 0; font-size: 2rem; color: #0f172a; }
    .header-rep img { height: 50px; }
    
    .filters.no-print { background: white; padding: 1.5rem; border-radius: var(--radius-md); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 2rem; display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap; }
    .filter-group { display: flex; flex-direction: column; gap: 0.5rem; }
    .filter-group label { font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; }
    .filter-group input, .filter-group select { padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; font-family: inherit; }
    
    .btn-rep { padding: 0.5rem 1rem; background: var(--primary-color); color: white; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
    .btn-rep.sec { background: #10b981; }
    .btn-rep.out { background: transparent; color: #475569; border: 1px solid #cbd5e1; }
    
    .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
    .kpi-box { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 4px solid var(--primary-color); }
    .kpi-box h3 { margin: 0; font-size: 0.85rem; color: #64748b; text-transform: uppercase; }
    .kpi-box p { margin: 0.5rem 0 0; font-size: 2.2rem; font-weight: 800; color: #0f172a; }
    
    .charts-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-bottom: 2rem; }
    .report-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    .report-card h2 { margin: 0 0 1.5rem; font-size: 1.2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; }
    
    table.rep-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    table.rep-table th { text-align: left; background: #f8fafc; padding: 1rem; border-bottom: 2px solid #e2e8f0; color: #475569; }
    table.rep-table td { padding: 0.45rem 1rem; border-bottom: 1px solid #e2e8f0; text-align: left;}
</style>

<div class="dashboard-container">
    <div class="header-rep">
        <div>
            <h1>Informe Gerencial Consolidado</h1>
            <p style="margin: 0; color: #64748b;">Período generado: <?php echo date('d/m/Y', strtotime($f_fecha_ini)) . ' - ' . date('d/m/Y', strtotime($f_fecha_fin)); ?></p>
        </div>
        <img src="/piura_noticias_php/img/logo.webp" alt="HTVPERU Logo" style="filter: grayscale(100%); opacity: 0.8;">
    </div>

    <div class="filters no-print">
        <form method="GET" action="/piura_noticias_php/admin/reportes" style="display: contents;">
            <div class="filter-group">
                <label>Desde</label>
                <input type="date" name="fecha_ini" value="<?php echo htmlspecialchars($f_fecha_ini); ?>">
            </div>
            <div class="filter-group">
                <label>Hasta</label>
                <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($f_fecha_fin); ?>">
            </div>
            <div class="filter-group">
                <label>Autor / Periodista</label>
                <select name="autor">
                    <option value="">-- Todos los Autores --</option>
                    <?php foreach($autores as $a): ?>
                        <option value="<?php echo $a['id']; ?>" <?php if($f_autor == $a['id']) echo 'selected'; ?>><?php echo htmlspecialchars($a['nombre_completo']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Sección / Categoría</label>
                <select name="categoria">
                    <option value="">-- Todas las Secciones --</option>
                    <?php foreach($categorias_disponibles as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['categoria']); ?>" <?php if($f_categoria == $cat['categoria']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['categoria']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-rep"><i class="ri-filter-3-line"></i> Aplicar Filtro</button>
            <a href="/piura_noticias_php/admin/reportes" class="btn-rep out" title="Limpiar"><i class="ri-refresh-line"></i></a>
        </form>
        
        <div style="flex-grow: 1; text-align: right; display: flex; gap: 0.5rem; justify-content: flex-end;">
            <button onclick="window.print()" class="btn-rep"><i class="ri-printer-line"></i> Imprimir a PDF</button>
            <?php 
            $qs = $_GET;
            $qs['download_csv'] = 1;
            $csv_url = '?' . http_build_query($qs);
            ?>
            <a href="<?php echo htmlspecialchars($csv_url); ?>" class="btn-rep sec"><i class="ri-file-excel-2-line"></i> Bajar CSV Plano</a>
            <a href="/piura_noticias_php/admin" class="btn-rep out" style="margin-left:1rem;"><i class="ri-arrow-go-back-line"></i> Al Panel</a>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-box">
            <h3>Total Lecturas Acumuladas</h3>
            <p><?php echo number_format($total_vistas); ?></p>
        </div>
        <div class="kpi-box" style="border-color: #10b981;">
            <h3>Notas Interceptadas</h3>
            <p><?php echo number_format($total_noticias); ?></p>
        </div>
        <div class="kpi-box" style="border-color: #8b5cf6;">
            <h3>Promedio de Lectura por Nota</h3>
            <p><?php echo number_format($promedio_vistas); ?></p>
        </div>
    </div>

    <div class="charts-grid">
        <div class="report-card">
            <h2>Rendimiento por Categoría</h2>
            <canvas id="chartCategorias" style="max-height: 250px;"></canvas>
        </div>
        <div class="report-card">
            <h2>Top Noticias Absolutas (Audiencia)</h2>
            <canvas id="chartTop" style="max-height: 250px;"></canvas>
        </div>
    </div>

    <!-- Monitor de Productividad en Vivo -->
    <div class="report-card" style="margin-bottom: 2rem;">
        <h2><i class="ri-focus-3-line" style="color: var(--primary-color);"></i> Monitor de Productividad en Vivo (Hoy)</h2>
        <div style="overflow-x:auto;">
            <table class="rep-table">
                <thead>
                    <tr>
                        <th>Redactor / Staff</th>
                        <th>Rol / Regla</th>
                        <th style="text-align: center;">Progreso de Cuota</th>
                        <th style="text-align: center;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($staff_productivity)): ?>
                        <?php foreach($staff_productivity as $staff): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($staff['nombre']); ?></strong>
                            </td>
                            <td>
                                <span style="background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><?php echo htmlspecialchars($staff['rol']); ?></span>
                                <br><span style="font-size: 0.75rem; color: #94a3b8;"><?php echo $staff['is_custom'] ? 'Cuota Personal' : 'Cuota de Rol'; ?>: <?php echo $staff['cuota']; ?></span>
                            </td>
                            <td style="width: 40%; vertical-align: middle;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700; margin-bottom: 0.25rem;">
                                    <span><?php echo $staff['hoy']; ?> / <?php echo $staff['cuota']; ?> noticias</span>
                                    <span><?php echo $staff['pct']; ?>%</span>
                                </div>
                                <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                    <?php 
                                        $barColor = '#3b82f6';
                                        if ($staff['hoy'] > $staff['cuota']) $barColor = '#f59e0b';
                                        elseif ($staff['hoy'] == $staff['cuota']) $barColor = '#10b981';
                                        elseif ($staff['pct'] < 50) $barColor = '#ef4444';
                                    ?>
                                    <div style="height: 100%; width: <?php echo $staff['pct']; ?>%; background: <?php echo $barColor; ?>; border-radius: 4px;"></div>
                                </div>
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <?php if ($staff['hoy'] > $staff['cuota']): ?>
                                    <span style="display: inline-block; background: #fef3c7; color: #d97706; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;"><i class="ri-fire-fill"></i> ¡Sobresaliente!</span>
                                <?php elseif ($staff['hoy'] == $staff['cuota']): ?>
                                    <span style="display: inline-block; background: #d1fae5; color: #059669; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;"><i class="ri-check-double-line"></i> Meta Cumplida</span>
                                <?php elseif ($staff['pct'] >= 50): ?>
                                    <span style="display: inline-block; background: #dbeafe; color: #1d4ed8; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;"><i class="ri-loader-4-line"></i> Avanzando</span>
                                <?php else: ?>
                                    <span style="display: inline-block; background: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;"><i class="ri-alert-line"></i> En Peligro</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align: center; padding: 2rem;">No hay personal con cuotas activas actualmente.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="report-card">
        <h2>Muestra de Datos (Últimos 100 Registros)</h2>
        <div style="overflow-x:auto;">
            <table class="rep-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Noticia</th>
                        <th>URL Fuente</th>
                        <th>Sección</th>
                        <th>Autor</th>
                        <th>Audiencia</th>
                        <th>Fecha Pub.</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach(array_slice($noticias, 0, 100) as $n): ?>
                    <tr>
                        <td><?php echo $n['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars(mb_strimwidth($n['titulo'], 0, 60, '...')); ?></strong></td>
                        <td style="font-size:0.75rem; word-break:break-all;"><?php echo !empty($n['fuente_url']) ? '<a href="'.htmlspecialchars($n['fuente_url']).'" target="_blank" style="color:#64748b;">'.htmlspecialchars(mb_strimwidth($n['fuente_url'],0,25,'...')).'</a>' : '-'; ?></td>
                        <td><?php echo htmlspecialchars($n['categoria']); ?></td>
                        <td><?php echo htmlspecialchars($n['autor']); ?></td>
                        <td style="font-weight: 800;"><?php echo number_format($n['vistas']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($n['fecha_publicacion'])); ?></td>
                        <td><?php echo $n['estado_publicacion'] === 'publicado' ? '<span style="color:#10b981;">Público</span>' : '<span style="color:#f59e0b;">Archivado</span>'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($noticias) === 0): ?>
                    <tr><td colspan="8" style="text-align: center; padding: 2rem;">No se encontraron datos en este período.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(count($noticias) > 100): ?>
            <p class="no-print" style="text-align: center; font-size: 0.8rem; color: #94a3b8; margin-top: 1rem;">Para ver los <?php echo count($noticias); ?> registros completos, descarga el informe en formato CSV.</p>
        <?php endif; ?>
    </div>
</div>

<script>
const ctxCat = document.getElementById('chartCategorias').getContext('2d');
new Chart(ctxCat, {
    type: 'doughnut',
    data: {
        labels: <?php echo $lbl_cats; ?>,
        datasets: [{
            data: <?php echo $val_cats; ?>,
            backgroundColor: ['#003db3', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#0ea5e9'],
            borderWidth: 1
        }]
    },
    options: { responsive: true, maintainAspectRatio: false }
});

const ctxTop = document.getElementById('chartTop').getContext('2d');
new Chart(ctxTop, {
    type: 'bar',
    data: {
        labels: <?php echo $lbl_top; ?>,
        datasets: [{
            label: 'Número de Vistas Únicas',
            data: <?php echo $val_top; ?>,
            backgroundColor: '#003db3',
            borderRadius: 4
        }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
    }
});
</script>
