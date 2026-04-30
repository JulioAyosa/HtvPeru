<?php
// app/Views/admin/control_contenidos/index.php
// Variables asumidas: $user_role, $user_id, $user_name, $is_admin, $msg, $f_fecha_ini, $f_fecha_fin, $f_autor, $f_plataforma, $registros, $autores
?>
<div class="header-rep">
    <div>
        <h1>Planificador de Contenidos</h1>
        <p style="margin: 0; color: #64748b;">Registro de actividades y publicaciones diarias.</p>
    </div>
</div>

<?php if($msg): ?>
    <div class="alert alert-success"><i class="ri-check-line"></i> <?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<!-- Filtros Superiores -->
<div class="filters">
    <form method="GET" style="display: contents;" action="/piura_noticias_php/admin/contenidos">
        <div class="filter-group">
            <label>Desde Fecha</label>
            <input type="date" name="fecha_ini" value="<?php echo htmlspecialchars($f_fecha_ini); ?>">
        </div>
        <div class="filter-group">
            <label>Hasta Fecha</label>
            <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($f_fecha_fin); ?>">
        </div>
        <?php if ($is_admin): ?>
        <div class="filter-group">
            <label>Redactor</label>
            <select name="autor">
                <option value="">-- Todos --</option>
                <?php foreach($autores as $a): ?>
                    <option value="<?php echo $a['id']; ?>" <?php if($f_autor == $a['id']) echo 'selected'; ?>><?php echo htmlspecialchars($a['nombre_completo']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="filter-group">
            <label>Plataforma</label>
            <select name="plataforma">
                <option value="">-- Todas --</option>
                <option value="Web" <?php if($f_plataforma=='Web') echo 'selected';?>>Web / Portal</option>
                <option value="Facebook" <?php if($f_plataforma=='Facebook') echo 'selected';?>>Facebook</option>
                <option value="Youtube" <?php if($f_plataforma=='Youtube') echo 'selected';?>>Youtube</option>
                <option value="Instagram" <?php if($f_plataforma=='Instagram') echo 'selected';?>>Instagram</option>
                <option value="Twitter" <?php if($f_plataforma=='Twitter') echo 'selected';?>>Twitter</option>
            </select>
        </div>
        <button type="submit" class="btn-rep"><i class="ri-filter-3-line"></i> Filtrar</button>
        <a href="/piura_noticias_php/admin/contenidos" class="btn-rep out" title="Limpiar"><i class="ri-refresh-line"></i></a>
    </form>
    
    <div style="flex-grow: 1; text-align: right; display: flex; gap: 0.5rem; justify-content: flex-end;">
        <?php 
        $qs = $_GET;
        $qs['download_csv'] = 1;
        $csv_url = '?' . http_build_query($qs);
        ?>
        <a href="<?php echo htmlspecialchars($csv_url); ?>" class="btn-rep sec"><i class="ri-file-excel-2-line"></i> Exportar a Excel</a>
    </div>
</div>

<style>
/* Estilos para el nuevo acordeón */
.acc-container { display: flex; flex-direction: column; gap: 1rem; margin-top: 1.5rem; }
.acc-user { background: white; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.acc-user-header { background: #f8fafc; padding: 1rem 1.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-weight: bold; color: var(--text-main); font-size: 1.1rem; border-bottom: 1px solid transparent; transition: background 0.2s; }
.acc-user-header:hover { background: #f1f5f9; }
.acc-user-header.active { border-bottom-color: var(--border-color); }
.acc-user-header i { transition: transform 0.3s; }
.acc-user-header.active i { transform: rotate(180deg); }

.acc-user-body { display: none; padding: 0; background: #ffffff; }
.acc-user-body.active { display: block; }

.acc-date { border-bottom: 1px solid var(--border-color); }
.acc-date:last-child { border-bottom: none; }
.acc-date-header { padding: 0.75rem 1.5rem; padding-left: 2.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; background: #ffffff; color: var(--text-main); font-weight: 600; transition: background 0.2s; }
.acc-date-header:hover { background: #f8fafc; }
.acc-date-header i { transition: transform 0.3s; color: #94a3b8; }
.acc-date-header.active i { transform: rotate(180deg); }

.acc-date-body { display: none; padding: 1rem 1.5rem 1rem 3.5rem; background: #fcfcfc; border-top: 1px solid #f1f5f9; }
.acc-date-body.active { display: block; }

.acc-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.acc-table th { text-align: left; padding: 0.5rem; border-bottom: 2px solid #e2e8f0; color: #64748b; font-weight: 600; }
.acc-table td { padding: 0.5rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.acc-table tr:last-child td { border-bottom: none; }

.insert-card { background: white; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.insert-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; align-items: end; }
</style>

<!-- Formulario de Inserción Rápida -->
<?php if ($user_role !== 'gerente'): ?>
<div class="insert-card">
    <h3 style="margin-top:0; margin-bottom: 1rem; color: var(--primary-color);"><i class="ri-add-circle-fill"></i> Nueva Publicación</h3>
    <form method="POST" action="/piura_noticias_php/admin/contenidos/store" class="insert-grid">
        <input type="hidden" name="action" value="add">
        <?php echo csrf_field(); ?>
        
        <div><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Fecha</label><input type="date" name="fecha" class="input-excel" value="<?php echo date('Y-m-d'); ?>" required style="width:100%;"></div>
        <div><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Hora</label><input type="time" name="hora" class="input-excel" value="<?php echo date('H:i'); ?>" required style="width:100%;"></div>
        <div><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">H. Prog.</label><input type="time" name="hora_publicacion" class="input-excel" title="Hora Programada" style="width:100%;"></div>
        <div style="grid-column: span 2;"><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Titular</label><input type="text" name="titular" class="input-excel" placeholder="Escribe el titular..." required style="width:100%;"></div>
        <div><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Enlace</label><input type="text" name="enlace" class="input-excel" placeholder="URL cortada..." style="width:100%;"></div>
        
        <?php if ($is_admin): ?>
            <div><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Redactor</label>
            <select name="usuario_id" class="input-excel" style="width:100%;">
                <?php foreach($autores as $a): ?>
                    <option value="<?php echo $a['id']; ?>" <?php if($user_id == $a['id']) echo 'selected'; ?>><?php echo htmlspecialchars($a['nombre_completo']); ?></option>
                <?php endforeach; ?>
            </select></div>
        <?php endif; ?>
        
        <div><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Sección</label><select name="seccion" class="input-excel" style="width:100%;">
            <option value="Actualidad">Actualidad</option>
            <option value="Deportes">Deportes</option>
            <option value="Entretenimiento">Entretenimiento</option>
            <option value="Politica">Politica</option>
            <option value="Publicidad">Publicidad (C)</option>
            <option value="Flyer">Flyer (A)</option>
            <option value="Shorts">Shorts</option>
        </select></div>
        <div><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Plat.</label><select name="plataforma" class="input-excel" style="width:100%;">
            <option value="Web">Web</option>
            <option value="Facebook">Facebook</option>
            <option value="Youtube">Youtube</option>
            <option value="Instagram">Instagram</option>
        </select></div>
        <div>
            <label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Listo</label>
            <div style="display:flex; align-items:center; gap: 10px;">
                <input type="checkbox" name="completado" class="chk-excel" checked style="margin:0;">
                <button type="submit" style="background:var(--primary-color); display:inline-flex; align-items:center; justify-content:center; color:white; border:none; padding: 0.35rem; border-radius:4px; font-size:1.1rem; cursor:pointer; width:100%; height:32px; transition: background 0.2s;" title="Guardar"><i class="ri-save-line"></i> Guardar</button>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Acordeón de Contenidos -->
<div class="acc-container">
    <?php if(empty($agrupados)): ?>
        <div style="text-align: center; padding: 3rem; background: white; border-radius: 8px; border: 1px solid var(--border-color); color: #64748b;">
            <i class="ri-folder-open-line" style="font-size: 3rem; display: block; margin-bottom: 1rem; color: #cbd5e1;"></i>
            <h3>No hay datos para mostrar en este rango de fechas.</h3>
        </div>
    <?php endif; ?>

    <?php foreach($agrupados as $autor => $fechas): 
        // Calculate total for author
        $total_autor = 0;
        foreach($fechas as $pubs) { $total_autor += count($pubs); }
    ?>
    <div class="acc-user">
        <div class="acc-user-header" onclick="toggleAcc(this, 'user')">
            <span><i class="ri-user-smile-line" style="margin-right: 8px; color: var(--primary-color);"></i> <?php echo htmlspecialchars($autor); ?></span>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <span style="font-size:0.8rem; background: #e2e8f0; padding: 2px 8px; border-radius: 50px; color: #475569;"><?php echo $total_autor; ?> publicacion(es) totales</span>
                <i class="ri-arrow-down-s-line"></i>
            </div>
        </div>
        <div class="acc-user-body">
            
            <?php foreach($fechas as $fecha => $pubs): 
                $count_pubs = count($pubs);
                $is_empty = $count_pubs === 0;
            ?>
            <div class="acc-date">
                <div class="acc-date-header" onclick="toggleAcc(this, 'date')">
                    <span style="color: <?php echo $is_empty ? '#94a3b8' : 'var(--text-main)'; ?>;">
                        <i class="ri-calendar-event-line" style="margin-right: 6px;"></i> <?php echo date('d/m/Y', strtotime($fecha)); ?>
                    </span>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <?php if($is_empty): ?>
                            <span style="font-size:0.75rem; background: #fee2e2; color: #ef4444; padding: 2px 8px; border-radius: 4px; font-weight: bold;">Sin publicaciones</span>
                        <?php else: ?>
                            <span style="font-size:0.75rem; background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 4px; font-weight: bold;"><?php echo $count_pubs; ?> reg.</span>
                        <?php endif; ?>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                </div>
                
                <div class="acc-date-body">
                    <?php if($is_empty): ?>
                        <p style="margin: 0; color: #94a3b8; font-style: italic; font-size: 0.9rem;">No se registró actividad este día.</p>
                    <?php else: ?>
                        <table class="acc-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Hora</th>
                                    <th>Titular</th>
                                    <th>Sección / Plat.</th>
                                    <th style="width: 50px; text-align:center;">✓</th>
                                    <th style="width: 50px; text-align:center;">Del</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pubs as $r): 
                                    $row_bg = "";
                                    if(strtoupper($r['seccion']) === 'PUBLICIDAD') $row_bg = "background: #f0f9ff;";
                                    if(strtoupper($r['seccion']) === 'FLYER') $row_bg = "background: #fefce8;";
                                ?>
                                <tr style="<?php echo $row_bg; ?>">
                                    <td style="font-weight:600; color:#475569;"><?php echo date('H:i', strtotime($r['hora'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($r['titular']); ?></strong><br>
                                        <?php if(!empty($r['enlace'])): 
                                            $link = $r['enlace'];
                                            if (strpos($link, 'http') !== 0) {
                                                $link = base_url(ltrim($link, '/'));
                                            }
                                        ?>
                                        <a href="<?php echo htmlspecialchars($link); ?>" target="_blank" style="font-size:0.75rem; color:var(--primary-color);">Ver Enlace</a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="font-size:0.75rem; background:#e2e8f0; padding:2px 4px; border-radius:3px;"><?php echo htmlspecialchars($r['seccion']); ?></span>
                                        <span style="font-size:0.75rem; background:#e0e7ff; color:#4338ca; padding:2px 4px; border-radius:3px;"><?php echo htmlspecialchars($r['plataforma']); ?></span>
                                    </td>
                                    <td style="text-align:center;">
                                        <a href="/piura_noticias_php/admin/contenidos/action?toggle_id=<?php echo $r['id']; ?>&val=<?php echo $r['completado'] ? 0 : 1; ?>&csrf_token=<?php echo csrf_token(); ?>" style="color: <?php echo $r['completado'] ? '#10b981' : '#cbd5e1'; ?>; font-size:1.4rem; transition: transform 0.2s;" title="<?php echo $r['completado'] ? 'Completado' : 'Pendiente'; ?>">
                                            <i class="<?php echo $r['completado'] ? 'ri-checkbox-circle-fill' : 'ri-checkbox-blank-circle-line'; ?>"></i>
                                        </a>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php if($is_admin): ?>
                                        <a href="/piura_noticias_php/admin/contenidos/action?delete_id=<?php echo $r['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" onclick="return confirm('¿Eliminar?');" style="color:#ef4444; font-size:1.2rem;"><i class="ri-delete-bin-line"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function toggleAcc(element, type) {
    element.classList.toggle('active');
    let bodyElement = element.nextElementSibling;
    if (bodyElement) {
        bodyElement.classList.toggle('active');
    }
}
</script>
