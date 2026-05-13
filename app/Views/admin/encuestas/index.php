<?php
// app/Views/admin/encuestas/index.php
// Asume variables: $msg, $encuestas, $pdo
?>
<h1 style="font-size: 2rem; color: #0f172a; margin-top: 0; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;"><i class="ri-bar-chart-2-fill" style="color:var(--primary-color)"></i> Gestión Integrada de Encuestas</h1>
<?php if($msg): ?><div class="alert alert-success"><i class="ri-checkbox-circle-fill"></i> <?=htmlspecialchars($msg)?></div><?php endif; ?>

<div class="card">
    <h3><i class="ri-add-box-line" style="color: var(--primary-color);"></i> Construir Nueva Encuesta</h3>
    <form method="POST" action="<?= APP_BASE ?>/admin/encuestas/create">
        <?php if(function_exists('csrf_field')) echo csrf_field(); ?>
        <div class="form-row">
            <label>Pregunta Principal</label>
            <input type="text" name="pregunta" required placeholder="Ej: ¿Cuál es el problema más urgente en tu distrito?" style="font-size:1.1rem;">
        </div>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-row" style="margin-bottom:0;">
                <label><i class="ri-timer-line"></i> Fecha Límite de la Encuesta (Opcional)</label>
                <input type="datetime-local" name="fecha_limite">
            </div>
            <div class="form-row" style="margin-bottom:0; display:flex; align-items:center; gap: 10px; padding-top: 1.5rem;">
                <input type="checkbox" name="activar_ahora" id="activar_ahora" value="1" checked style="width:20px; height:20px;">
                <label for="activar_ahora" style="margin:0; cursor:pointer;">Activar inmediatamente al guardar</label>
            </div>
        </div>
        
        <div id="opciones-container">
            <div class="form-row"><label>Opción 1</label><input type="text" name="opciones[]" required placeholder="Ingresa una respuesta"></div>
            <div class="form-row"><label>Opción 2</label><input type="text" name="opciones[]" required placeholder="Ingresa una respuesta"></div>
        </div>
        
        <button type="button" class="btn btn-secondary" onclick="agregarOpcion()" style="margin-bottom: 1rem;"><i class="ri-add-line"></i> Agregar más opciones</button>
        <div style="border-top:1px solid #e2e8f0; margin-top:1.5rem; padding-top:1.5rem;">
            <button type="submit" class="btn btn-primary" style="font-size:1rem;"><i class="ri-save-line"></i> Guardar y Crear Encuesta</button>
        </div>
    </form>
</div>

<style>
/* Estilos para el acordeón de encuestas */
.acc-container { display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem; }
.acc-item { background: white; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.03); }
.acc-header { background: #f8fafc; padding: 1rem 1.5rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-weight: bold; color: var(--text-main); font-size: 1.05rem; border-bottom: 1px solid transparent; transition: background 0.2s; }
.acc-header:hover { background: #f1f5f9; }
.acc-header.active { border-bottom-color: var(--border-color); }
.acc-header i.acc-icon { transition: transform 0.3s; color: #64748b; }
.acc-header.active i.acc-icon { transform: rotate(180deg); }

.acc-body { display: none; padding: 1.5rem; background: #ffffff; }
.acc-body.active { display: block; }
</style>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;"><i class="ri-history-line" style="color: var(--primary-color);"></i> Historial de Encuestas</h3>
        <button onclick="toggleAllAcc()" style="background:#e2e8f0; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:0.85rem; font-weight:600; color:#475569; display:inline-flex; align-items:center; gap:5px; transition: background 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">
            <i class="ri-expand-height-line"></i> Expandir / Colapsar
        </button>
    </div>
    
    <div class="acc-container">
        <?php if(empty($encuestas)): ?>
            <div style="text-align: center; padding: 3rem; background: #f8fafc; border-radius: 8px; border: 1px dashed var(--border-color); color: #64748b;">
                <i class="ri-bar-chart-2-line" style="font-size: 3rem; display: block; margin-bottom: 1rem; color: #cbd5e1;"></i>
                <h3 style="margin: 0; font-size: 1.1rem;">No hay encuestas creadas aún.</h3>
            </div>
        <?php endif; ?>

        <?php foreach($encuestas as $e): 
            // Obtener opciones de esta encuesta
            $stmt_ops = $pdo->prepare("SELECT * FROM encuestas_opciones WHERE encuesta_id = ?");
            $stmt_ops->execute([$e['id']]);
            $opciones = $stmt_ops->fetchAll();
            
            $total = 0;
            foreach($opciones as $op) $total += $op['votos'];
        ?>
        <div class="acc-item">
            <div class="acc-header" onclick="toggleAcc(this)">
                <div style="display: flex; align-items: center; gap: 1rem; flex: 1; min-width: 0;">
                    <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 60%; color: #1e293b;"><?=htmlspecialchars($e['pregunta'])?></div>
                    <?php if($e['estado']=='activo'): ?>
                        <span style="background:#dcfce7; color:#166534; padding:2px 8px; border-radius:4px; font-size:0.75rem; font-weight:bold; white-space: nowrap;">ACTIVA</span>
                    <?php else: ?>
                        <span style="background:#f1f5f9; color:#64748b; padding:2px 8px; border-radius:4px; font-size:0.75rem; font-weight:bold; white-space: nowrap;">INACTIVA</span>
                    <?php endif; ?>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; flex-shrink: 0;">
                    <span style="font-size:0.85rem; color:#64748b; font-weight: 600;"><i class="ri-user-smile-line"></i> <?=$total?> votos</span>
                    <i class="ri-arrow-down-s-line acc-icon"></i>
                </div>
            </div>
            <div class="acc-body">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    
                    <!-- Resultados de la Encuesta -->
                    <div>
                        <div style="font-size:0.8rem; color:#64748b; margin-bottom:1.25rem; display:flex; flex-wrap: wrap; gap:1rem;">
                            <span><i class="ri-calendar-line"></i> Creada: <?=date('d/m/Y H:i', strtotime($e['fecha_creacion']))?></span>
                            <?php if(!empty($e['fecha_limite'])): ?>
                            <span style="color: var(--danger); font-weight: 600;"><i class="ri-timer-line"></i> Expira: <?=date('d/m/Y H:i', strtotime($e['fecha_limite']))?></span>
                            <?php endif; ?>
                            <span><i class="ri-user-line"></i> Autor: <?=htmlspecialchars($e['autor'] ?? 'Desconocido')?></span>
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach($opciones as $op): 
                                $pct = $total > 0 ? round(($op['votos'] / $total) * 100) : 0;
                            ?>
                                <div>
                                    <div style="display:flex; justify-content:space-between; font-size: 0.95rem; font-weight: 600; color: #334155; margin-bottom: 0.3rem;">
                                        <span><?=htmlspecialchars($op['opcion_texto'])?> <span style="color:#94a3b8; font-weight:400; font-size:0.85rem;">(<?=$op['votos']?>)</span></span>
                                        <span style="color: var(--primary-color); font-weight: 800;"><?=$pct?>%</span>
                                    </div>
                                    <div class="poll-bar-container" style="background: #f1f5f9; height: 10px; border-radius: 5px; overflow: hidden;">
                                        <div class="poll-bar" style="width: <?=$pct?>%; background: var(--primary-color); height: 100%; transition: width 0.5s ease;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Panel de Acciones -->
                    <div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <h4 style="margin-top:0; margin-bottom: 1rem; color: #475569; font-size: 0.9rem; text-transform: uppercase;"><i class="ri-settings-4-line"></i> Opciones de Encuesta</h4>
                        
                        <?php if($e['estado']!='activo'): ?>
                            <form method="POST" action="<?= APP_BASE ?>/admin/encuestas/action" style="margin-bottom: 0.75rem;">
                                <?php if(function_exists('csrf_field')) echo csrf_field(); ?>
                                <input type="hidden" name="type" value="activate">
                                <input type="hidden" name="id" value="<?=$e['id']?>">
                                <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center; background: #10b981; border: none; color: white; padding: 0.6rem; border-radius: 6px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;"><i class="ri-check-line"></i> Habilitar en Web</button>
                            </form>
                            <form method="POST" action="<?= APP_BASE ?>/admin/encuestas/action" onsubmit="return confirmDelete(event, '¿Reactivar encuesta reiniciando todos los votos a 0?');" style="margin-bottom: 0.75rem;">
                                <?php if(function_exists('csrf_field')) echo csrf_field(); ?>
                                <input type="hidden" name="type" value="relaunch">
                                <input type="hidden" name="id" value="<?=$e['id']?>">
                                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; background: #3b82f6; border: none; color: white; padding: 0.6rem; border-radius: 6px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;"><i class="ri-refresh-line"></i> Reiniciar y Lanzar</button>
                            </form>
                        <?php else: ?>
                            <div style="padding: 0.75rem; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; color: #065f46; font-size: 0.85rem; margin-bottom: 1rem; display: flex; align-items: flex-start; gap: 0.5rem;">
                                <i class="ri-information-fill" style="font-size: 1.1rem; margin-top: -2px;"></i>
                                Esta es la encuesta activa actualmente. Se muestra en la portada del sitio web.
                            </div>
                            <form method="POST" action="<?= APP_BASE ?>/admin/encuestas/action" style="margin-bottom: 0.75rem;">
                                <?php if(function_exists('csrf_field')) echo csrf_field(); ?>
                                <input type="hidden" name="type" value="pause">
                                <input type="hidden" name="id" value="<?=$e['id']?>">
                                <button type="submit" class="btn btn-warning" style="width: 100%; justify-content: center; background: #f59e0b; border: none; color: white; padding: 0.6rem; border-radius: 6px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: background 0.2s;" onmouseover="this.style.background='#d97706'" onmouseout="this.style.background='#f59e0b'"><i class="ri-pause-circle-line"></i> Pausar Encuesta</button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <form method="POST" action="<?= APP_BASE ?>/admin/encuestas/action" onsubmit="return confirmDelete(event, '¿Enviar esta encuesta a la papelera? Podrás recuperarla luego.');">
                                <?php if(function_exists('csrf_field')) echo csrf_field(); ?>
                                <input type="hidden" name="type" value="delete">
                                <input type="hidden" name="id" value="<?=$e['id']?>">
                                <button type="submit" class="btn btn-danger" style="width: 100%; justify-content: center; background: white; border: 1px solid #ef4444; color: #ef4444; padding: 0.6rem; border-radius: 6px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: background 0.2s;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='white'"><i class="ri-delete-bin-line"></i> Enviar a Papelera</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    let opCounter = 2;
    function agregarOpcion() {
        opCounter++;
        const container = document.getElementById('opciones-container');
        const div = document.createElement('div');
        div.className = 'form-row';
        div.innerHTML = `<label>Opción ${opCounter} <small style="color:red; cursor:pointer; float:right;" onclick="this.parentElement.parentElement.remove()">Eliminar</small></label><input type="text" name="opciones[]" placeholder="Ingresa otra opción...">`;
        container.appendChild(div);
    }
    
    // Funciones del acordeón
    function toggleAcc(element) {
        element.classList.toggle('active');
        let bodyElement = element.nextElementSibling;
        if (bodyElement) {
            bodyElement.classList.toggle('active');
        }
    }

    let allExpanded = false;
    function toggleAllAcc() {
        allExpanded = !allExpanded;
        const headers = document.querySelectorAll('.acc-header');
        const bodies = document.querySelectorAll('.acc-body');
        
        headers.forEach(h => {
            if (allExpanded) h.classList.add('active');
            else h.classList.remove('active');
        });
        
        bodies.forEach(b => {
            if (allExpanded) b.classList.add('active');
            else b.classList.remove('active');
        });
    }
</script>
