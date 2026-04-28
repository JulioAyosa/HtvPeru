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

<div class="excel-table-container">
    <table class="excel-table">
        <thead>
            <tr>
                <th style="width: 90px;">Fecha</th>
                <th style="width: 80px;">Hora</th>
                <th style="width: 90px;">Hora Publ.</th>
                <th>Titular / Nota</th>
                <th>Enlace</th>
                <th>Fuente</th>
                <?php if ($is_admin): ?><th>Redactor</th><?php endif; ?>
                <th style="width: 120px;">Sección</th>
                <th style="width: 100px;">Plataforma</th>
                <th style="width: 120px;">Rebote / Estado</th>
                <th style="width: 50px; text-align:center;">✓</th>
                <th style="width: 70px; text-align:center;">Acción</th>
            </tr>
        </thead>
        <tbody>
            <!-- Fila de Inserción Rápida -->
            <?php if ($user_role !== 'gerente'): ?>
            <tr style="background-color: #f8fafc; border-bottom: 2px solid var(--primary-color);">
                <form method="POST" action="/piura_noticias_php/admin/contenidos/store">
                    <input type="hidden" name="action" value="add">
                    <?php echo csrf_field(); ?>
                    <td><input type="date" name="fecha" class="input-excel" value="<?php echo date('Y-m-d'); ?>" required></td>
                    <td><input type="time" name="hora" class="input-excel" value="<?php echo date('H:i'); ?>" required></td>
                    <td><input type="time" name="hora_publicacion" class="input-excel" title="Hora Programada a Salir"></td>
                    <td><input type="text" name="titular" class="input-excel" placeholder="Escribe el titular..." required></td>
                    <td><input type="text" name="enlace" class="input-excel" placeholder="URL cortada..."></td>
                    <td><input type="url" name="fuente_url" class="input-excel" placeholder="URL Fuente..."></td>
                    
                    <?php if ($is_admin): ?>
                    <td style="text-align: center; vertical-align: middle;">
                        <span style="font-size:0.8rem; background:#e2e8f0; color:#475569; padding:4px 8px; border-radius:4px; font-weight:600; white-space:nowrap; cursor:not-allowed;" title="Asignación automática e intransferible">
                            <?php echo htmlspecialchars($user_name); ?>
                        </span>
                        <input type="hidden" name="usuario_id" value="<?php echo $user_id; ?>">
                    </td>
                    <?php endif; ?>
                    
                    <td>
                        <select name="seccion" class="input-excel">
                            <option value="Actualidad">Actualidad</option>
                            <option value="Deportes">Deportes</option>
                            <option value="Entretenimiento">Entretenimiento</option>
                            <option value="Politica">Politica</option>
                            <option value="Publicidad">Publicidad (C)</option>
                            <option value="Flyer">Flyer (A)</option>
                            <option value="Shorts">Shorts</option>
                        </select>
                    </td>
                    <td>
                        <select name="plataforma" class="input-excel">
                            <option value="Web">Web</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Youtube">Youtube</option>
                            <option value="Instagram">Instagram</option>
                        </select>
                    </td>
                    <td>
                        <select name="rebote" class="input-excel">
                            <option value="(completada)">(completada)</option>
                            <option value="(programada)">(programada)</option>
                            <option value="(pendiente)">(pendiente)</option>
                        </select>
                    </td>
                    <td style="text-align: center;"><input type="checkbox" name="completado" class="chk-excel" checked></td>
                    <td style="text-align: center;"><button type="submit" style="background:var(--primary-color); display:inline-flex; align-items:center; justify-content:center; color:white; border:none; padding: 0.35rem; border-radius:4px; font-size:1.1rem; cursor:pointer; width:30px; height:30px; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'" title="Guardar Registro"><i class="ri-save-line"></i></button></td>
                </form>
            </tr>
            <?php endif; ?>
            
            <!-- Listado de Registros -->
            <?php foreach($registros as $r): 
                $r_style = "";
                $sec = strtoupper($r['seccion']);
                if ($sec === 'PUBLICIDAD') $r_style = "row-publicidad";
                if ($sec === 'FLYER') $r_style = "row-flyer";
            ?>
            <tr class="<?php echo $r_style; ?>">
                <td><?php echo date('d/m/Y', strtotime($r['fecha'])); ?></td>
                <td><?php echo date('H:i', strtotime($r['hora'])); ?></td>
                <td><span style="background:#e0e7ff; color:#4338ca; padding:2px 6px; border-radius:4px; font-weight:800; font-size:0.75rem; letter-spacing:0.5px;"><?php echo !empty($r['hora_publicacion']) ? date('H:i', strtotime($r['hora_publicacion'])) : '-'; ?></span></td>
                <td><strong><?php echo htmlspecialchars($r['titular']); ?></strong></td>
                <td><a href="<?php echo htmlspecialchars($r['enlace']); ?>" target="_blank" style="color:var(--primary-color);text-decoration:underline;">Link...</a></td>
                <td style="font-size:0.75rem; word-break:break-all;"><?php echo !empty($r['fuente_url']) ? '<a href="'.htmlspecialchars($r['fuente_url']).'" target="_blank" style="color:#64748b;">'.htmlspecialchars(mb_strimwidth($r['fuente_url'],0,25,'...')).'</a>' : '-'; ?></td>
                
                <?php if ($is_admin): ?>
                <td><span style="font-size:0.8rem; background:#cbd5e1; padding:2px 6px; border-radius:4px;"><?php echo htmlspecialchars($r['autor']); ?></span></td>
                <?php endif; ?>
                
                <td><?php echo htmlspecialchars($r['seccion']); ?></td>
                <td><?php echo htmlspecialchars($r['plataforma']); ?></td>
                <td><span style="font-size:0.8rem; color:#475569; font-weight:600;"><?php echo htmlspecialchars($r['rebote']); ?></span></td>
                
                <td style="text-align: center;">
                    <a href="/piura_noticias_php/admin/contenidos/action?toggle_id=<?php echo $r['id']; ?>&val=<?php echo $r['completado'] ? 0 : 1; ?>&csrf_token=<?php echo csrf_token(); ?>" style="color: <?php echo $r['completado'] ? '#10b981' : '#94a3b8'; ?>; font-size:1.6rem; line-height:1; display:inline-block; transition: transform 0.2s, color 0.2s;" title="Marcar como <?php echo $r['completado'] ? 'Pendiente' : 'Completado'; ?>" onmouseover="this.style.transform='scale(1.1)'; this.style.color='<?php echo $r['completado'] ? '#059669' : '#64748b';?>';" onmouseout="this.style.transform='none'; this.style.color='<?php echo $r['completado'] ? '#10b981' : '#94a3b8';?>';">
                        <?php if($r['completado']): ?>
                            <i class="ri-checkbox-circle-fill"></i>
                        <?php else: ?>
                            <i class="ri-checkbox-blank-circle-line"></i>
                        <?php endif; ?>
                    </a>
                </td>
                <td style="text-align: center;">
                    <?php if ($is_admin): ?>
                    <a href="/piura_noticias_php/admin/contenidos/action?delete_id=<?php echo $r['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" onclick="return confirm('¿Eliminar este registro permanentemente?');" style="background:#fee2e2; color:#ef4444; border:none; padding:4px 6px; border-radius:4px; width:26px; height:26px; font-size:0.95rem; display:inline-flex; align-items:center; justify-content:center; text-decoration:none; transition: transform 0.2s, background 0.2s;" onmouseover="this.style.transform='translateY(-1px)'; this.style.background='#fecaca';" onmouseout="this.style.transform='none'; this.style.background='#fee2e2';" title="Eliminar"><i class="ri-delete-bin-line"></i></a>
                    <?php else: ?>
                    <span style="color:#cbd5e1; font-size:0.75rem;">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if(count($registros) === 0): ?>
            <tr><td colspan="<?php echo $is_admin ? '12' : '11'; ?>" style="text-align: center; padding: 2rem; color: #64748b; font-weight:600;">No hay registros en este período.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
