<?php
// app/Views/admin/encuestas/index.php
// Asume variables: $msg, $encuestas, $pdo
?>
<h1 style="font-size: 2rem; color: #0f172a; margin-top: 0; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;"><i class="ri-bar-chart-2-fill" style="color:var(--primary-color)"></i> Gestión Integrada de Encuestas</h1>
<?php if($msg): ?><div class="alert alert-success"><i class="ri-checkbox-circle-fill"></i> <?=htmlspecialchars($msg)?></div><?php endif; ?>

<div class="card">
    <h3><i class="ri-add-box-line" style="color: var(--primary-color);"></i> Construir Nueva Encuesta</h3>
    <form method="POST" action="<?= base_url('/') ?>admin/encuestas/create">
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

<div class="card">
    <h3><i class="ri-history-line" style="color: var(--primary-color);"></i> Historial de Encuestas</h3>
    <table>
        <tr>
            <th>Pregunta y Resultados</th>
            <th>Estado</th>
            <th>Total Votos</th>
            <th>Acción</th>
        </tr>
        <?php foreach($encuestas as $e): 
            // Obtener opciones de esta encuesta
            $stmt_ops = $pdo->prepare("SELECT * FROM encuestas_opciones WHERE encuesta_id = ?");
            $stmt_ops->execute([$e['id']]);
            $opciones = $stmt_ops->fetchAll();
            
            $total = 0;
            foreach($opciones as $op) $total += $op['votos'];
        ?>
        <tr>
            <td>
                <strong style="color: #1e293b; font-size: 1.05rem; display: block; margin-bottom: 0.25rem;"><?=htmlspecialchars($e['pregunta'])?></strong>
                <div style="font-size:0.75rem; color:#64748b; margin-bottom:1rem; display:flex; gap:1rem;">
                    <span><i class="ri-calendar-line"></i> <?=date('d/m/Y H:i', strtotime($e['fecha_creacion']))?></span>
                    <?php if(!empty($e['fecha_limite'])): ?>
                    <span style="color: var(--danger); font-weight: 600;"><i class="ri-timer-line"></i> Expira: <?=date('d/m/Y H:i', strtotime($e['fecha_limite']))?></span>
                    <?php endif; ?>
                    <span><i class="ri-user-line"></i> <?=htmlspecialchars($e['autor'] ?? 'Desconocido')?></span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach($opciones as $op): 
                        $pct = $total > 0 ? round(($op['votos'] / $total) * 100) : 0;
                    ?>
                        <div>
                            <div style="display:flex; justify-content:space-between; max-width:400px; font-size: 0.9rem; font-weight: 600; color: #475569;">
                                <span><?=htmlspecialchars($op['opcion_texto'])?> <span style="color:#94a3b8; font-weight:400; font-size:0.8rem;">(<?=$op['votos']?>)</span></span>
                                <span style="color: var(--primary-color);"><?=$pct?>%</span>
                            </div>
                            <div class="poll-bar-container">
                                <div class="poll-bar" style="width: <?=$pct?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </td>
            <td>
                <?php if($e['estado']=='activo'): ?>
                    <span style="background:#22c55e; color:white; padding:4px 8px; border-radius:4px; font-size:0.8rem; font-weight:bold;">TENDENCIA ACTUAL</span>
                <?php else: ?>
                    <span style="background:#94a3b8; color:white; padding:4px 8px; border-radius:4px; font-size:0.8rem;">INACTIVA</span>
                <?php endif; ?>
            </td>
            <td><b><?=$total?></b> votos</td>
            <td>
                <?php if($e['estado']!='activo'): ?>
                    <form method="POST" action="<?= base_url('/') ?>admin/encuestas/action" style="display:inline-block; width:100%; margin-bottom: 0.5rem;">
                        <?php if(function_exists('csrf_field')) echo csrf_field(); ?>
                        <input type="hidden" name="type" value="activate">
                        <input type="hidden" name="id" value="<?=$e['id']?>">
                        <button type="submit" class="btn btn-success" style="width: 100%;"><i class="ri-check-line"></i> Habilitar en Web</button>
                    </form>
                    <form method="POST" action="<?= base_url('/') ?>admin/encuestas/action" onsubmit="return confirm('¿Reactivar encuesta reiniciando todos los votos a 0?');" style="display:inline-block; width:100%; margin-bottom: 0.5rem;">
                        <?php if(function_exists('csrf_field')) echo csrf_field(); ?>
                        <input type="hidden" name="type" value="relaunch">
                        <input type="hidden" name="id" value="<?=$e['id']?>">
                        <button type="submit" class="btn btn-primary" style="width: 100%; background: #3b82f6;"><i class="ri-refresh-line"></i> Reiniciar y Lanzar</button>
                    </form>
                <?php endif; ?>
                <?php if($_SESSION['user_role'] === 'admin'): ?>
                    <form method="POST" action="<?= base_url('/') ?>admin/encuestas/action" onsubmit="return confirm('¿Enviar esta encuesta a la papelera? Podrás recuperarla luego.');" style="display:inline-block; width:100%;">
                        <?php if(function_exists('csrf_field')) echo csrf_field(); ?>
                        <input type="hidden" name="type" value="delete">
                        <input type="hidden" name="id" value="<?=$e['id']?>">
                        <button type="submit" class="btn btn-danger" style="width: 100%;"><i class="ri-delete-bin-line"></i> Eliminar</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
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
</script>
