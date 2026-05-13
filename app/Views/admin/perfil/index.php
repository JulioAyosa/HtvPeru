<?php
// app/Views/admin/perfil/index.php
// Variables asumidas: $msg, $info
?>
<?php if ($msg): ?>
    <div class="alert <?php echo strpos($msg, 'Error') !== false ? 'alert-error' : 'alert-info'; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="admin-header">
    <div>
        <h1 style="margin:0;"><i class="ri-user-settings-fill" style="color:var(--primary-color)"></i> Mi Perfil</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Edita tus credenciales de acceso personales.</p>
    </div>
</div>

<div class="profile-card">
    <div style="text-align:center; padding-bottom: 2rem; border-bottom: 1px solid var(--border-color); margin-bottom: 2rem;">
        <?php if (!empty($info['avatar_url'])): ?>
            <img src="<?php echo htmlspecialchars(base_url($info['avatar_url'])); ?>" alt="Avatar" style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:4px solid var(--primary-light); margin-bottom:1rem; box-shadow:var(--shadow-md);">
        <?php else: ?>
            <i class="ri-user-smile-fill" style="font-size: 5rem; color: var(--primary-color); display:block; margin-bottom:1rem;"></i>
        <?php endif; ?>
        <span class="role-badge"><?php echo htmlspecialchars($info['rol']); ?></span>
        <h2 style="margin:0; font-size:1.5rem;"><?php echo htmlspecialchars($info['nombre_completo']); ?></h2>
        <p style="color: var(--text-muted); font-size:0.875rem; margin-top:0.5rem;">Miembro desde: <?php echo date('d M Y', strtotime($info['fecha_creacion'])); ?></p>
    </div>

    <form method="POST" action="<?= APP_BASE ?>/admin/perfil/action" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="form-row">
            <label><i class="ri-image-edit-line"></i> Cambiar Foto de Perfil (Avatar)</label>
            <input type="file" name="avatar" accept="image/*" style="padding: 0.5rem; background:white;" class="form-control">
        </div>
        <div class="form-row">
            <label><i class="ri-user-line"></i> Nombre Completo</label>
            <input type="text" name="nombre_completo" required value="<?php echo htmlspecialchars($info['nombre_completo']); ?>" class="form-control">
        </div>
        <div class="form-row">
            <label><i class="ri-mail-line"></i> Correo Electrónico</label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($info['email']); ?>" class="form-control">
        </div>
        <div class="form-row">
            <label><i class="ri-lock-password-line"></i> Contraseña Actual (requerida para cambiar contraseña)</label>
            <input type="password" name="current_password" placeholder="Ingresa tu contraseña actual para verificar" class="form-control">
        </div>
        <div class="form-row">
            <label><i class="ri-lock-password-line"></i> Nueva Contraseña</label>
            <input type="password" name="password" placeholder="Solo llena si deseas cambiarla (mín. 8 caracteres)" class="form-control">
            <span style="font-size:0.75rem; color:#6b7280; display:block; margin-top:0.5rem;">Déjalo en blanco para mantener tu contraseña actual intacta. Mínimo 8 caracteres.</span>
        </div>
        
        <div style="margin-top: 2rem;">
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content:center; padding: 0.75rem 2rem; font-size: 1rem;"><i class="ri-save-3-fill"></i> GUARDAR CAMBIOS</button>
        </div>
    </form>
</div>
