<?php
// app/Views/admin/control_contenidos/index.php
// Variables asumidas: $user_role, $user_id, $user_name, $is_admin, $msg, $f_fecha_ini, $f_fecha_fin, $f_autor, $f_plataforma, $registros, $autores
?>
<div class="admin-header">
    <div>
        <h1 style="margin:0;"><i class="ri-calendar-todo-line" style="color:var(--primary-color)"></i> Planificador de Contenidos</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Registro de actividades y publicaciones diarias.</p>
    </div>
</div>

<?php if($msg): ?>
    <div class="alert alert-success"><i class="ri-check-line"></i> <?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<!-- Dashboard Stats -->
<?php if(!empty($agrupados)): ?>
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:0.75rem; margin-bottom:1rem;">
    <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border:1px solid #bfdbfe; border-radius:10px; padding:1rem 1.2rem; display:flex; align-items:center; gap:0.8rem;">
        <div style="background:#3b82f6; color:#fff; width:40px; height:40px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1.2rem;"><i class="ri-calendar-check-line"></i></div>
        <div><div style="font-size:1.4rem; font-weight:800; color:#1e40af;"><?php echo $stats_hoy; ?></div><div style="font-size:0.75rem; color:#64748b; font-weight:600;">Publicaciones Hoy</div></div>
    </div>
    <div style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border:1px solid #bbf7d0; border-radius:10px; padding:1rem 1.2rem; display:flex; align-items:center; gap:0.8rem;">
        <div style="background:#10b981; color:#fff; width:40px; height:40px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1.2rem;"><i class="ri-bar-chart-box-line"></i></div>
        <div><div style="font-size:1.4rem; font-weight:800; color:#059669;"><?php echo number_format($stats_total); ?></div><div style="font-size:0.75rem; color:#64748b; font-weight:600;">Total Período</div></div>
    </div>
    <div style="background: linear-gradient(135deg, #fdf2f8, #fce7f3); border:1px solid #fbcfe8; border-radius:10px; padding:1rem 1.2rem; display:flex; align-items:center; gap:0.8rem;">
        <div style="background:#ec4899; color:#fff; width:40px; height:40px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1.2rem;"><i class="ri-eye-line"></i></div>
        <div><div style="font-size:1.4rem; font-weight:800; color:#be185d;"><?php echo number_format($stats_vistas); ?></div><div style="font-size:0.75rem; color:#64748b; font-weight:600;">Vistas Totales</div></div>
    </div>
    <div style="background: linear-gradient(135deg, #fffbeb, #fef3c7); border:1px solid #fde68a; border-radius:10px; padding:1rem 1.2rem; display:flex; align-items:center; gap:0.8rem;">
        <div style="background:#f59e0b; color:#fff; width:40px; height:40px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1.2rem;"><i class="ri-trophy-line"></i></div>
        <div><div style="font-size:1rem; font-weight:800; color:#d97706;"><?php echo htmlspecialchars($stats_top_autor); ?></div><div style="font-size:0.75rem; color:#64748b; font-weight:600;">Más Activo (<?php echo $stats_top_count; ?> pub.)</div></div>
    </div>
</div>
<?php endif; ?>

<!-- Filtros Superiores -->
<div style="background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
    <form method="GET" action="<?= APP_BASE ?>/admin/contenidos">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div style="padding: 0.6rem 0.85rem; display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width:26px; height:26px; border-radius:6px; background:#3b82f6; color:white; display:flex; align-items:center; justify-content:center; font-size:0.8rem;"><i class="ri-calendar-line"></i></div>
                    <div>
                        <h4 style="margin:0; font-size:0.8rem; font-weight:700; color:#334155;">Desde Fecha</h4>
                    </div>
                </div>
                <div style="padding: 0.5rem 0.85rem; background: white;">
                    <input type="date" name="fecha_ini" value="<?php echo htmlspecialchars($f_fecha_ini); ?>" style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:0.4rem 0.5rem; font-family:inherit; font-size:0.85rem;">
                </div>
            </div>
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div style="padding: 0.6rem 0.85rem; display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width:26px; height:26px; border-radius:6px; background:#10b981; color:white; display:flex; align-items:center; justify-content:center; font-size:0.8rem;"><i class="ri-calendar-check-line"></i></div>
                    <div>
                        <h4 style="margin:0; font-size:0.8rem; font-weight:700; color:#334155;">Hasta Fecha</h4>
                    </div>
                </div>
                <div style="padding: 0.5rem 0.85rem; background: white;">
                    <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($f_fecha_fin); ?>" style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:0.4rem 0.5rem; font-family:inherit; font-size:0.85rem;">
                </div>
            </div>
            <?php if ($is_admin): ?>
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div style="padding: 0.6rem 0.85rem; display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width:26px; height:26px; border-radius:6px; background:#8b5cf6; color:white; display:flex; align-items:center; justify-content:center; font-size:0.8rem;"><i class="ri-user-star-line"></i></div>
                    <div>
                        <h4 style="margin:0; font-size:0.8rem; font-weight:700; color:#334155;">Redactor</h4>
                    </div>
                </div>
                <div style="padding: 0.5rem 0.85rem; background: white;">
                    <select name="autor" style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:0.4rem 0.5rem; font-family:inherit; font-size:0.85rem; background:white;">
                        <option value="">-- Todos --</option>
                        <?php foreach($autores as $a): ?>
                            <option value="<?php echo $a['id']; ?>" <?php if($f_autor == $a['id']) echo 'selected'; ?>><?php echo htmlspecialchars($a['nombre_completo']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div style="padding: 0.6rem 0.85rem; display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width:26px; height:26px; border-radius:6px; background:#f59e0b; color:white; display:flex; align-items:center; justify-content:center; font-size:0.8rem;"><i class="ri-smartphone-line"></i></div>
                    <div>
                        <h4 style="margin:0; font-size:0.8rem; font-weight:700; color:#334155;">Plataforma</h4>
                    </div>
                </div>
                <div style="padding: 0.5rem 0.85rem; background: white;">
                    <select name="plataforma" style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:0.4rem 0.5rem; font-family:inherit; font-size:0.85rem; background:white;">
                        <option value="">-- Todas --</option>
                        <option value="Web" <?php if($f_plataforma=='Web') echo 'selected';?>>Web / Portal</option>
                        <option value="Facebook" <?php if($f_plataforma=='Facebook') echo 'selected';?>>Facebook</option>
                        <option value="Youtube" <?php if($f_plataforma=='Youtube') echo 'selected';?>>Youtube</option>
                        <option value="Instagram" <?php if($f_plataforma=='Instagram') echo 'selected';?>>Instagram</option>
                        <option value="TikTok" <?php if($f_plataforma=='TikTok') echo 'selected';?>>TikTok</option>
                        <option value="Twitter" <?php if($f_plataforma=='Twitter') echo 'selected';?>>Twitter / X</option>
                    </select>
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" style="background: linear-gradient(135deg, var(--primary-color), #1d4ed8); color: white; border: none; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; transition: all 0.2s; box-shadow: 0 2px 8px rgba(37,99,235,0.3);" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'"><i class="ri-filter-3-line"></i> Filtrar</button>
                <a href="<?= APP_BASE ?>/admin/contenidos" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 0.6rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.9rem; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'" title="Limpiar"><i class="ri-refresh-line"></i></a>
            </div>
            <div>
                <?php 
                $qs = $_GET;
                $qs['download_csv'] = 1;
                $csv_url = '?' . http_build_query($qs);
                ?>
                <a href="<?php echo htmlspecialchars($csv_url); ?>" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; text-decoration: none; transition: all 0.2s; box-shadow: 0 2px 8px rgba(16,185,129,0.3);" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'"><i class="ri-file-excel-2-line"></i> Exportar a Excel</a>
            </div>
        </div>
    </form>
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

.acc-month-header { background: #f1f5f9; padding: 0.85rem 1.5rem; padding-left: 2rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-weight: 600; color: #334155; border-bottom: 1px solid var(--border-color); }
.acc-month-header:hover { background: #e2e8f0; }
.acc-month-header i { transition: transform 0.3s; }
.acc-month-header.active i { transform: rotate(180deg); }
.acc-month-body { display: none; }
.acc-month-body.active { display: block; }

.acc-week-header { background: #f8fafc; padding: 0.8rem 1.5rem; padding-left: 2.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0; }
.acc-week-header:hover { background: #f1f5f9; }
.acc-week-header i { transition: transform 0.3s; }
.acc-week-header.active i { transform: rotate(180deg); }
.acc-week-body { display: none; }
.acc-week-body.active { display: block; }

.acc-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.acc-table th { text-align: left; padding: 0.5rem; border-bottom: 2px solid #e2e8f0; color: #64748b; font-weight: 600; }
.acc-table td { padding: 0.5rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.acc-table tr:last-child td { border-bottom: none; }

.insert-card { background: white; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.insert-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; align-items: end; }
</style>

<!-- Formulario de Inserción Rápida -->
<?php if ($user_role !== 'gerente'): ?>
<div style="background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 2px solid #eff6ff;">
        <div style="width:36px; height:36px; border-radius:10px; background: linear-gradient(135deg, var(--primary-color), #3b82f6); color:white; display:flex; align-items:center; justify-content:center; font-size:1.1rem; box-shadow: 0 2px 8px rgba(37,99,235,0.3);"><i class="ri-add-circle-fill"></i></div>
        <div>
            <h3 style="margin:0; font-size: 1.1rem; font-weight: 800; color: #0f172a;">Nueva Publicación</h3>
            <p style="margin:0; font-size: 0.75rem; color: #94a3b8;">Registra una nueva entrada en el planificador</p>
        </div>
    </div>
    <form method="POST" action="<?= APP_BASE ?>/admin/contenidos/store">
        <input type="hidden" name="action" value="add">
        <?php echo csrf_field(); ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <!-- Fecha -->
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div style="padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.4rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width:22px; height:22px; border-radius:5px; background:#3b82f6; color:white; display:flex; align-items:center; justify-content:center; font-size:0.7rem;"><i class="ri-calendar-line"></i></div>
                    <span style="font-size:0.75rem; font-weight:700; color:#334155;">Fecha</span>
                </div>
                <div style="padding: 0.4rem 0.75rem; background: white;">
                    <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:0.35rem 0.4rem; font-family:inherit; font-size:0.85rem;">
                </div>
            </div>
            <!-- Hora -->
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div style="padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.4rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width:22px; height:22px; border-radius:5px; background:#10b981; color:white; display:flex; align-items:center; justify-content:center; font-size:0.7rem;"><i class="ri-time-line"></i></div>
                    <span style="font-size:0.75rem; font-weight:700; color:#334155;">Hora</span>
                </div>
                <div style="padding: 0.4rem 0.75rem; background: white;">
                    <input type="time" name="hora" value="<?php echo date('H:i'); ?>" required style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:0.35rem 0.4rem; font-family:inherit; font-size:0.85rem;">
                </div>
            </div>
            <!-- Hora Programada -->
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div style="padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.4rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width:22px; height:22px; border-radius:5px; background:#8b5cf6; color:white; display:flex; align-items:center; justify-content:center; font-size:0.7rem;"><i class="ri-timer-line"></i></div>
                    <span style="font-size:0.75rem; font-weight:700; color:#334155;">H. Prog.</span>
                </div>
                <div style="padding: 0.4rem 0.75rem; background: white;">
                    <input type="time" name="hora_publicacion" title="Hora Programada" style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:0.35rem 0.4rem; font-family:inherit; font-size:0.85rem;">
                </div>
            </div>
            <!-- Titular (span 2) -->
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden; grid-column: span 2;">
                <div style="padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.4rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width:22px; height:22px; border-radius:5px; background:#0ea5e9; color:white; display:flex; align-items:center; justify-content:center; font-size:0.7rem;"><i class="ri-edit-line"></i></div>
                    <span style="font-size:0.75rem; font-weight:700; color:#334155;">Titular</span>
                </div>
                <div style="padding: 0.4rem 0.75rem; background: white;">
                    <input type="text" name="titular" placeholder="Escribe el titular de la publicación..." required style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:0.35rem 0.5rem; font-family:inherit; font-size:0.85rem;">
                </div>
            </div>
            <!-- Enlace -->
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div style="padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.4rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width:22px; height:22px; border-radius:5px; background:#f59e0b; color:white; display:flex; align-items:center; justify-content:center; font-size:0.7rem;"><i class="ri-link"></i></div>
                    <span style="font-size:0.75rem; font-weight:700; color:#334155;">Enlace</span>
                </div>
                <div style="padding: 0.4rem 0.75rem; background: white;">
                    <input type="text" name="enlace" placeholder="URL cortada..." style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:0.35rem 0.5rem; font-family:inherit; font-size:0.85rem;">
                </div>
            </div>
            <!-- Fuente -->
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div style="padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.4rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width:22px; height:22px; border-radius:5px; background:#ec4899; color:white; display:flex; align-items:center; justify-content:center; font-size:0.7rem;"><i class="ri-global-line"></i></div>
                    <span style="font-size:0.75rem; font-weight:700; color:#334155;">Fuente</span>
                </div>
                <div style="padding: 0.4rem 0.75rem; background: white;">
                    <input type="url" name="fuente_url" placeholder="URL original..." style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:0.35rem 0.5rem; font-family:inherit; font-size:0.85rem;">
                </div>
            </div>
            
            <?php if ($is_admin): ?>
            <!-- Redactor -->
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div style="padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.4rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width:22px; height:22px; border-radius:5px; background:#6366f1; color:white; display:flex; align-items:center; justify-content:center; font-size:0.7rem;"><i class="ri-user-star-line"></i></div>
                    <span style="font-size:0.75rem; font-weight:700; color:#334155;">Redactor</span>
                </div>
                <div style="padding: 0.4rem 0.75rem; background: white;">
                    <select name="usuario_id" style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:0.35rem 0.4rem; font-family:inherit; font-size:0.85rem; background:white;">
                        <?php foreach($autores as $a): ?>
                            <option value="<?php echo $a['id']; ?>" <?php if($user_id == $a['id']) echo 'selected'; ?>><?php echo htmlspecialchars($a['nombre_completo']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Sección -->
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div style="padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.4rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width:22px; height:22px; border-radius:5px; background:#14b8a6; color:white; display:flex; align-items:center; justify-content:center; font-size:0.7rem;"><i class="ri-folder-line"></i></div>
                    <span style="font-size:0.75rem; font-weight:700; color:#334155;">Sección</span>
                </div>
                <div style="padding: 0.4rem 0.75rem; background: white;">
                    <select name="seccion" style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:0.35rem 0.4rem; font-family:inherit; font-size:0.85rem; background:white;">
                        <option value="Actualidad">Actualidad</option>
                        <option value="Deportes">Deportes</option>
                        <option value="Entretenimiento">Entretenimiento</option>
                        <option value="Politica">Politica</option>
                        <option value="Publicidad">Publicidad (C)</option>
                        <option value="Flyer">Flyer (A)</option>
                        <option value="Shorts">Shorts</option>
                    </select>
                </div>
            </div>
            <!-- Plataforma -->
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div style="padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.4rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width:22px; height:22px; border-radius:5px; background:#f97316; color:white; display:flex; align-items:center; justify-content:center; font-size:0.7rem;"><i class="ri-smartphone-line"></i></div>
                    <span style="font-size:0.75rem; font-weight:700; color:#334155;">Plat.</span>
                </div>
                <div style="padding: 0.4rem 0.75rem; background: white;">
                    <select name="plataforma" style="width:100%; border:1px solid #cbd5e1; border-radius:6px; padding:0.35rem 0.4rem; font-family:inherit; font-size:0.85rem; background:white;">
                        <option value="Web">Web</option>
                        <option value="Facebook">Facebook</option>
                        <option value="Youtube">Youtube</option>
                        <option value="Instagram">Instagram</option>
                        <option value="TikTok">TikTok</option>
                        <option value="Twitter">Twitter / X</option>
                    </select>
                </div>
            </div>
            <!-- Guardar -->
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div style="padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.4rem; border-bottom: 1px solid #e2e8f0;">
                    <div style="width:22px; height:22px; border-radius:5px; background:#22c55e; color:white; display:flex; align-items:center; justify-content:center; font-size:0.7rem;"><i class="ri-checkbox-circle-line"></i></div>
                    <span style="font-size:0.75rem; font-weight:700; color:#334155;">Listo</span>
                </div>
                <div style="padding: 0.4rem 0.75rem; background: white;">
                    <div style="display:flex; align-items:center; gap: 8px;">
                        <input type="checkbox" name="completado" checked style="margin:0; width:18px; height:18px; accent-color: var(--primary-color); cursor:pointer;">
                        <button type="submit" style="background: linear-gradient(135deg, var(--primary-color), #1d4ed8); color:white; border:none; padding: 0.4rem 1rem; border-radius:6px; font-size:0.85rem; font-weight:700; cursor:pointer; width:100%; display:inline-flex; align-items:center; justify-content:center; gap:0.3rem; transition: all 0.2s; box-shadow: 0 2px 6px rgba(37,99,235,0.3);" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'" title="Guardar"><i class="ri-save-line"></i> Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Controles del Acordeón -->
<?php if(!empty($agrupados)): ?>
<div style="display:flex; gap:0.5rem; margin-top:1rem; margin-bottom:-0.5rem; align-items:center; flex-wrap:wrap;">
    <button onclick="toggleAllAcc(true)" style="background:#e2e8f0; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:0.85rem; font-weight:600; color:#475569; display:inline-flex; align-items:center; gap:5px; transition: background 0.2s;"
        onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">
        <i class="ri-expand-diagonal-line"></i> Expandir Todo
    </button>
    <button onclick="toggleAllAcc(false)" style="background:#e2e8f0; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:0.85rem; font-weight:600; color:#475569; display:inline-flex; align-items:center; gap:5px; transition: background 0.2s;"
        onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">
        <i class="ri-collapse-diagonal-line"></i> Colapsar Todo
    </button>
    <div style="flex-grow:1;"></div>
    <div style="position:relative;">
        <i class="ri-search-line" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#94a3b8;"></i>
        <input type="text" id="searchTitular" oninput="filterByTitular(this.value)" placeholder="Buscar por titular..." style="padding:6px 12px 6px 32px; border:1px solid #e2e8f0; border-radius:6px; font-size:0.85rem; width:220px; outline:none; transition: border 0.2s;" onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='#e2e8f0'">
    </div>
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

    <?php foreach($agrupados as $autor => $meses): 
        // Calculate total for author
        $total_autor = 0; $vistas_autor = 0; $comp_autor = 0; $pend_autor = 0;
        foreach($meses as $semanas) { 
            foreach($semanas as $fechas) {
                foreach($fechas as $pubs) { 
                    $total_autor += count($pubs); 
                    foreach($pubs as $p) { 
                        $vistas_autor += intval($p['vistas'] ?? 0);
                        if($p['completado']) $comp_autor++; else $pend_autor++;
                    }
                }
            }
        }
    ?>
    <div class="acc-user">
        <div class="acc-user-header" onclick="toggleAcc(this, 'user')">
            <span><i class="ri-user-smile-line" style="margin-right: 8px; color: var(--primary-color);"></i> <?php echo htmlspecialchars($autor); ?></span>
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span style="font-size:0.8rem; background: #e2e8f0; padding: 2px 8px; border-radius: 50px; color: #475569;"><?php echo $total_autor; ?> pub.</span>
                <span style="font-size:0.8rem; background: #fce7f3; padding: 2px 8px; border-radius: 50px; color: #be185d;"><i class="ri-eye-line"></i> <?php echo number_format($vistas_autor); ?></span>
                <?php if($comp_autor > 0): ?><span style="font-size:0.75rem; background:#dcfce7; color:#059669; padding:2px 6px; border-radius:50px;" title="Completados">✅ <?php echo $comp_autor; ?></span><?php endif; ?>
                <?php if($pend_autor > 0): ?><span style="font-size:0.75rem; background:#fef3c7; color:#d97706; padding:2px 6px; border-radius:50px;" title="Pendientes">⏳ <?php echo $pend_autor; ?></span><?php endif; ?>
                <i class="ri-arrow-down-s-line"></i>
            </div>
        </div>
        <div class="acc-user-body">
            
            <?php foreach($meses as $mes_nombre => $semanas): 
                $total_mes = 0; $vistas_mes = 0; $comp_mes = 0; $pend_mes = 0;
                foreach($semanas as $f) {
                    foreach($f as $p) { 
                        $total_mes += count($p); 
                        foreach($p as $pp) { 
                            $vistas_mes += intval($pp['vistas'] ?? 0);
                            if($pp['completado']) $comp_mes++; else $pend_mes++;
                        }
                    }
                }
            ?>
            <div class="acc-month">
                <div class="acc-month-header" onclick="toggleAcc(this)">
                    <span><i class="ri-calendar-2-line" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($mes_nombre); ?></span>
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        <span style="font-size:0.75rem; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 4px; font-weight: bold;"><?php echo $total_mes; ?> pub.</span>
                        <span style="font-size:0.75rem; background: #fce7f3; color: #be185d; padding: 2px 8px; border-radius: 4px; font-weight: bold;"><i class="ri-eye-line"></i> <?php echo number_format($vistas_mes); ?></span>
                        <?php if($comp_mes > 0): ?><span style="font-size:0.7rem; background:#dcfce7; color:#059669; padding:1px 5px; border-radius:3px;">✅<?php echo $comp_mes; ?></span><?php endif; ?>
                        <?php if($pend_mes > 0): ?><span style="font-size:0.7rem; background:#fef3c7; color:#d97706; padding:1px 5px; border-radius:3px;">⏳<?php echo $pend_mes; ?></span><?php endif; ?>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                </div>
                <div class="acc-month-body">

                    <?php foreach($semanas as $semana_nombre => $fechas): 
                        $total_semana = 0; $vistas_semana = 0; $comp_sem = 0; $pend_sem = 0;
                        foreach($fechas as $p) { 
                            $total_semana += count($p); 
                            foreach($p as $pp) { 
                                $vistas_semana += intval($pp['vistas'] ?? 0);
                                if($pp['completado']) $comp_sem++; else $pend_sem++;
                            }
                        }
                    ?>
                    <div class="acc-week">
                        <div class="acc-week-header" onclick="toggleAcc(this)">
                            <span><i class="ri-calendar-view-day-line" style="margin-right: 6px; color:#64748b;"></i> <?php echo htmlspecialchars($semana_nombre); ?></span>
                            <div style="display: flex; align-items: center; gap: 0.6rem;">
                                <span style="font-size:0.75rem; background: #fef3c7; color: #d97706; padding: 2px 8px; border-radius: 4px; font-weight: bold;"><?php echo $total_semana; ?> pub.</span>
                                <span style="font-size:0.75rem; background: #fce7f3; color: #be185d; padding: 2px 8px; border-radius: 4px; font-weight: bold;"><i class="ri-eye-line"></i> <?php echo number_format($vistas_semana); ?></span>
                                <?php if($comp_sem > 0): ?><span style="font-size:0.7rem; background:#dcfce7; color:#059669; padding:1px 5px; border-radius:3px;">✅<?php echo $comp_sem; ?></span><?php endif; ?>
                                <?php if($pend_sem > 0): ?><span style="font-size:0.7rem; background:#fef3c7; color:#d97706; padding:1px 5px; border-radius:3px;">⏳<?php echo $pend_sem; ?></span><?php endif; ?>
                                <i class="ri-arrow-down-s-line"></i>
                            </div>
                        </div>
                        <div class="acc-week-body">
                            
                            <?php foreach($fechas as $fecha => $pubs): 
                                $count_pubs = count($pubs);
                                $is_empty = $count_pubs === 0;
                            ?>
                            <?php 
                                $is_today = ($fecha === date('Y-m-d'));
                                $today_border = $is_today ? 'border-left: 4px solid #10b981;' : '';
                            ?>
                            <div class="acc-date">
                                <div class="acc-date-header" onclick="toggleAcc(this, 'date')" style="padding-left: 3.5rem; <?php echo $today_border; ?>">
                                    <span style="color: <?php echo $is_empty ? '#94a3b8' : 'var(--text-main)'; ?>;">
                                        <i class="ri-calendar-event-line" style="margin-right: 6px;"></i> <?php echo date('d/m/Y', strtotime($fecha)); ?>
                                        <?php if($is_today): ?>
                                            <span style="font-size:0.7rem; background:#10b981; color:#fff; padding:1px 6px; border-radius:3px; margin-left:6px; font-weight:bold;">HOY</span>
                                        <?php endif; ?>
                                    </span>
                                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                                        <?php if($is_empty): ?>
                                            <span style="font-size:0.75rem; background: #fee2e2; color: #ef4444; padding: 2px 8px; border-radius: 4px; font-weight: bold;">Sin publicaciones</span>
                                        <?php else: 
                                            $vistas_dia = 0;
                                            foreach($pubs as $pp) { $vistas_dia += intval($pp['vistas'] ?? 0); }
                                        ?>
                                            <span style="font-size:0.75rem; background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 4px; font-weight: bold;"><?php echo $count_pubs; ?> reg.</span>
                                            <span style="font-size:0.75rem; background: #fce7f3; color: #be185d; padding: 2px 8px; border-radius: 4px; font-weight: bold;"><i class="ri-eye-line"></i> <?php echo number_format($vistas_dia); ?></span>
                                        <?php endif; ?>
                                        <i class="ri-arrow-down-s-line"></i>
                                    </div>
                                </div>
                                
                                <div class="acc-date-body" style="padding-left: 4.5rem;">
                                    <?php if($is_empty): ?>
                                        <p style="margin: 0; color: #94a3b8; font-style: italic; font-size: 0.9rem;">No se registró actividad este día.</p>
                                    <?php else: ?>
                                        <table class="acc-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 60px;">Hora</th>
                                                    <th>Titular</th>
                                                    <th>Sección / Plat.</th>
                                                    <th style="width: 70px; text-align:center;"><i class="ri-eye-line"></i> Vistas</th>
                                                    <th style="width: 50px; text-align:center;">✓</th>
                                                    <th style="width: 100px; text-align:center;">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($pubs as $r): 
                                                    $row_bg = "";
                                                    if(strtoupper($r['seccion']) === 'PUBLICIDAD') $row_bg = "background: #f0f9ff;";
                                                    if(strtoupper($r['seccion']) === 'FLYER') $row_bg = "background: #fefce8;";
                                                ?>
                                                <tr style="<?php echo $row_bg; ?>">
                                                    <td style="font-weight:600; color:#475569;">
                                                        <?php echo date('H:i', strtotime($r['hora'])); ?>
                                                        <?php if(!empty($r['hora_publicacion'])): ?>
                                                            <div style="font-size:0.7rem; color:#8b5cf6; font-weight:600; margin-top:2px;" title="Hora programada">
                                                                <i class="ri-timer-line"></i> <?php echo date('H:i', strtotime($r['hora_publicacion'])); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($r['titular']); ?></strong><br>
                                                        <div style="display:flex; gap:10px; margin-top:4px;">
                                                            <?php if(!empty($r['enlace'])): 
                                                                $link = $r['enlace'];
                                                                if (strpos($link, 'http') !== 0) {
                                                                    $link = base_url(ltrim($link, '/'));
                                                                }
                                                            ?>
                                                            <a href="<?php echo htmlspecialchars($link); ?>" target="_blank" style="font-size:0.75rem; color:var(--primary-color); display:inline-flex; align-items:center; gap:3px;"><i class="ri-external-link-line"></i> Link Corto</a>
                                                            <?php endif; ?>
                                                            
                                                            <?php if(!empty($r['fuente_url'])): ?>
                                                            <a href="<?php echo htmlspecialchars($r['fuente_url']); ?>" target="_blank" style="font-size:0.75rem; color:#64748b; display:inline-flex; align-items:center; gap:3px;"><i class="ri-article-line"></i> Fuente</a>
                                                            <?php else: ?>
                                                            <span style="font-size:0.75rem; color:#cbd5e1; display:inline-flex; align-items:center; gap:3px;"><i class="ri-article-line"></i> Sin Fuente</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span style="font-size:0.75rem; background:#e2e8f0; padding:2px 4px; border-radius:3px;"><?php echo htmlspecialchars($r['seccion']); ?></span>
                                                        <?php 
                                                            $plat = $r['plataforma'] ?? '';
                                                            $plat_map = [
                                                                'Web' => ['ri-globe-line', '#0ea5e9', '#e0f2fe'],
                                                                'Facebook' => ['ri-facebook-fill', '#1877F2', '#dbeafe'],
                                                                'Youtube' => ['ri-youtube-fill', '#FF0000', '#fee2e2'],
                                                                'Instagram' => ['ri-instagram-fill', '#E4405F', '#fce7f3'],
                                                                'TikTok' => ['ri-tiktok-fill', '#000000', '#f1f5f9'],
                                                                'Twitter' => ['ri-twitter-x-fill', '#000000', '#f1f5f9'],
                                                            ];
                                                            $pi = $plat_map[$plat] ?? ['ri-link', '#64748b', '#f1f5f9'];
                                                        ?>
                                                        <span style="font-size:0.75rem; background:<?php echo $pi[2]; ?>; color:<?php echo $pi[1]; ?>; padding:2px 6px; border-radius:3px; display:inline-flex; align-items:center; gap:3px;">
                                                            <i class="<?php echo $pi[0]; ?>"></i> <?php echo htmlspecialchars($plat); ?>
                                                        </span>
                                                    </td>
                                                    <td style="text-align:center; font-weight:600; color:#be185d; font-size:0.85rem;">
                                                        <i class="ri-eye-line" style="margin-right:2px;"></i> <?php echo number_format(intval($r['vistas'] ?? 0)); ?>
                                                    </td>
                                                    <td style="text-align:center;">
                                                        <form method="POST" action="<?= APP_BASE ?>/admin/contenidos/action" style="display:inline;">
                                                            <input type="hidden" name="toggle_id" value="<?php echo $r['id']; ?>">
                                                            <input type="hidden" name="val" value="<?php echo $r['completado'] ? 0 : 1; ?>">
                                                            <?php echo csrf_field(); ?>
                                                            <button type="submit" style="background:none; border:none; cursor:pointer; color: <?php echo $r['completado'] ? '#10b981' : '#cbd5e1'; ?>; font-size:1.4rem; padding:0; transition: transform 0.2s;" title="<?php echo $r['completado'] ? 'Completado' : 'Pendiente'; ?>">
                                                                <i class="<?php echo $r['completado'] ? 'ri-checkbox-circle-fill' : 'ri-checkbox-blank-circle-line'; ?>"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td style="text-align:center;">
                                                        <div style="display:flex; gap:4px; justify-content:center; align-items:center;">
                                                            <a href="javascript:void(0)" onclick='openEditModal(<?php echo json_encode($r, JSON_HEX_APOS|JSON_HEX_QUOT); ?>)' style="color:#3b82f6; font-size:1.1rem;" title="Editar"><i class="ri-edit-line"></i></a>
                                                            <a href="javascript:void(0)" onclick="openDupMenu(this, <?php echo $r['id']; ?>)" style="color:#8b5cf6; font-size:1.1rem;" title="Duplicar"><i class="ri-file-copy-line"></i></a>
                                                            <?php if($is_admin): ?>
                                                            <form method="POST" action="<?= APP_BASE ?>/admin/contenidos/action" style="display:inline;" onsubmit="return confirm('¿Eliminar esta publicación?');">
                                                                <input type="hidden" name="delete_id" value="<?php echo $r['id']; ?>">
                                                                <?php echo csrf_field(); ?>
                                                                <button type="submit" style="background:none; border:none; cursor:pointer; color:#ef4444; font-size:1.1rem; padding:0;" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                            </form>
                                                            <?php endif; ?>
                                                        </div>
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
            </div>
            <?php endforeach; ?>
            
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal de Edición -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;" onclick="if(event.target===this)closeEditModal()">
    <div style="background:#fff; border-radius:12px; padding:1.5rem; width:95%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 style="margin:0; color:var(--primary-color);"><i class="ri-edit-line"></i> Editar Publicación</h3>
            <button onclick="closeEditModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#94a3b8;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_BASE ?>/admin/contenidos/store">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="edit_id" id="edit_id">
            <?php echo csrf_field(); ?>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                <div><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Fecha</label><input type="date" name="fecha" id="edit_fecha" class="input-excel" style="width:100%;" required></div>
                <div><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Hora</label><input type="time" name="hora" id="edit_hora" class="input-excel" style="width:100%;" required></div>
                <div style="grid-column:span 2;"><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Titular</label><input type="text" name="titular" id="edit_titular" class="input-excel" style="width:100%;" required></div>
                <div><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Enlace</label><input type="text" name="enlace" id="edit_enlace" class="input-excel" style="width:100%;"></div>
                <div><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Fuente</label><input type="url" name="fuente_url" id="edit_fuente" class="input-excel" style="width:100%;"></div>
                <div><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Sección</label>
                    <select name="seccion" id="edit_seccion" class="input-excel" style="width:100%;">
                        <option value="Actualidad">Actualidad</option><option value="Deportes">Deportes</option><option value="Entretenimiento">Entretenimiento</option><option value="Politica">Politica</option><option value="Publicidad">Publicidad (C)</option><option value="Flyer">Flyer (A)</option><option value="Shorts">Shorts</option>
                    </select>
                </div>
                <div><label style="font-size:0.8rem; font-weight:bold; color:#64748b;">Plataforma</label>
                    <select name="plataforma" id="edit_plataforma" class="input-excel" style="width:100%;">
                        <option value="Web">Web</option><option value="Facebook">Facebook</option><option value="Youtube">Youtube</option><option value="Instagram">Instagram</option><option value="TikTok">TikTok</option><option value="Twitter">Twitter / X</option>
                    </select>
                </div>
                <div style="display:flex; align-items:center; gap:8px; padding-top:1.2rem;">
                    <input type="checkbox" name="completado" id="edit_completado" class="chk-excel">
                    <label for="edit_completado" style="font-size:0.85rem; color:#64748b; cursor:pointer;">Completado</label>
                </div>
                <div style="padding-top:1.2rem;">
                    <button type="submit" style="background:var(--primary-color); color:#fff; border:none; padding:8px 20px; border-radius:6px; cursor:pointer; font-weight:600; width:100%; display:flex; align-items:center; justify-content:center; gap:6px;"><i class="ri-save-line"></i> Guardar Cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Menu Duplicar -->
<div id="dupMenu" style="display:none; position:absolute; background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:0.5rem; box-shadow:0 8px 24px rgba(0,0,0,0.15); z-index:9998; min-width:140px;">
    <form method="POST" action="<?= APP_BASE ?>/admin/contenidos/store" id="dupForm">
        <input type="hidden" name="action" value="duplicate">
        <input type="hidden" name="source_id" id="dup_source_id">
        <?php echo csrf_field(); ?>
        <div style="font-size:0.75rem; color:#94a3b8; padding:4px 8px; font-weight:600;">Duplicar en:</div>
        <?php $dup_plats = ['Web','Facebook','Youtube','Instagram','TikTok','Twitter']; foreach($dup_plats as $dp): ?>
        <button type="submit" name="new_plataforma" value="<?php echo $dp; ?>" style="display:block; width:100%; text-align:left; background:none; border:none; padding:6px 10px; cursor:pointer; font-size:0.85rem; border-radius:4px; transition:background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'">
            <?php echo $dp; ?>
        </button>
        <?php endforeach; ?>
    </form>
</div>

<script>
function toggleAcc(element, type) {
    element.classList.toggle('active');
    let bodyElement = element.nextElementSibling;
    if (bodyElement) {
        bodyElement.classList.toggle('active');
    }
}

function toggleAllAcc(expand) {
    const headers = document.querySelectorAll('.acc-user-header, .acc-month-header, .acc-week-header, .acc-date-header');
    const bodies = document.querySelectorAll('.acc-user-body, .acc-month-body, .acc-week-body, .acc-date-body');
    headers.forEach(h => {
        if (expand) h.classList.add('active');
        else h.classList.remove('active');
    });
    bodies.forEach(b => {
        if (expand) b.classList.add('active');
        else b.classList.remove('active');
    });
}

// 3.3 Smart search — hides empty sections, scrolls to first match
function filterByTitular(query) {
    const q = query.toLowerCase().trim();
    
    // Reset everything when search is empty
    if (q === '') {
        document.querySelectorAll('.acc-table tbody tr').forEach(r => { r.style.display = ''; r.style.background = ''; });
        document.querySelectorAll('.acc-date').forEach(d => d.style.display = '');
        document.querySelectorAll('.acc-week').forEach(w => w.style.display = '');
        document.querySelectorAll('.acc-month').forEach(m => m.style.display = '');
        document.querySelectorAll('.acc-user').forEach(u => u.style.display = '');
        toggleAllAcc(false);
        return;
    }

    // First collapse all
    toggleAllAcc(false);
    let firstMatch = null;

    // 1. Filter rows, hide/show dates
    document.querySelectorAll('.acc-date').forEach(dateDiv => {
        const table = dateDiv.querySelector('.acc-table');
        if (!table) { dateDiv.style.display = 'none'; return; }
        const rows = table.querySelectorAll('tbody tr');
        let hasMatch = false;
        rows.forEach(row => {
            const td = row.querySelector('td:nth-child(2)');
            if (!td) return;
            if (td.textContent.toLowerCase().includes(q)) {
                row.style.display = ''; row.style.background = '#fefce8';
                hasMatch = true;
                if (!firstMatch) firstMatch = row;
            } else {
                row.style.display = 'none'; row.style.background = '';
            }
        });
        dateDiv.style.display = hasMatch ? '' : 'none';
        if (hasMatch) {
            const h = dateDiv.querySelector('.acc-date-header');
            const b = dateDiv.querySelector('.acc-date-body');
            if (h) h.classList.add('active');
            if (b) b.classList.add('active');
        }
    });

    // 2. Hide weeks with no visible dates
    document.querySelectorAll('.acc-week').forEach(weekDiv => {
        const has = Array.from(weekDiv.querySelectorAll('.acc-week-body .acc-date')).some(d => d.style.display !== 'none');
        weekDiv.style.display = has ? '' : 'none';
        if (has) {
            const h = weekDiv.querySelector('.acc-week-header');
            const b = weekDiv.querySelector('.acc-week-body');
            if (h) h.classList.add('active');
            if (b) b.classList.add('active');
        }
    });

    // 3. Hide months with no visible weeks
    document.querySelectorAll('.acc-month').forEach(monthDiv => {
        const has = Array.from(monthDiv.querySelectorAll('.acc-month-body .acc-week')).some(w => w.style.display !== 'none');
        monthDiv.style.display = has ? '' : 'none';
        if (has) {
            const h = monthDiv.querySelector('.acc-month-header');
            const b = monthDiv.querySelector('.acc-month-body');
            if (h) h.classList.add('active');
            if (b) b.classList.add('active');
        }
    });

    // 4. Hide authors with no visible months
    document.querySelectorAll('.acc-user').forEach(userDiv => {
        const has = Array.from(userDiv.querySelectorAll('.acc-user-body .acc-month')).some(m => m.style.display !== 'none');
        userDiv.style.display = has ? '' : 'none';
        if (has) {
            const h = userDiv.querySelector('.acc-user-header');
            const b = userDiv.querySelector('.acc-user-body');
            if (h) h.classList.add('active');
            if (b) b.classList.add('active');
        }
    });

    // 5. Scroll to first match
    if (firstMatch) {
        setTimeout(() => firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' }), 150);
    }
}

// 3.1 Edit modal
function openEditModal(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_fecha').value = data.fecha;
    document.getElementById('edit_hora').value = data.hora ? data.hora.substring(0,5) : '';
    document.getElementById('edit_titular').value = data.titular || '';
    document.getElementById('edit_enlace').value = data.enlace || '';
    document.getElementById('edit_fuente').value = data.fuente_url || '';
    document.getElementById('edit_seccion').value = data.seccion || 'Actualidad';
    document.getElementById('edit_plataforma').value = data.plataforma || 'Web';
    document.getElementById('edit_completado').checked = data.completado == 1;
    const modal = document.getElementById('editModal');
    modal.style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// 3.2 Duplicate menu
function openDupMenu(anchor, sourceId) {
    const menu = document.getElementById('dupMenu');
    document.getElementById('dup_source_id').value = sourceId;
    const rect = anchor.getBoundingClientRect();
    menu.style.top = (rect.bottom + window.scrollY + 4) + 'px';
    menu.style.left = (rect.left + window.scrollX - 50) + 'px';
    menu.style.display = 'block';
    // Close on click outside
    setTimeout(() => {
        document.addEventListener('click', closeDupOnOutside);
    }, 10);
}
function closeDupOnOutside(e) {
    const menu = document.getElementById('dupMenu');
    if (!menu.contains(e.target)) {
        menu.style.display = 'none';
        document.removeEventListener('click', closeDupOnOutside);
    }
}

// Close modal with Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
        document.getElementById('dupMenu').style.display = 'none';
    }
});
</script>
