<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema - HTVPERU (PHP)</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <?= \App\Services\AssetManager::css('css/style.css') ?>
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: var(--bg-main); }
        .login-card { background: var(--bg-card); padding: 3rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); width: 100%; max-width: 400px; text-align: center; }
        .login-logo { font-size: 2rem; font-weight: 800; margin-bottom: 2rem; letter-spacing: -1px; }
        .login-logo span { color: var(--primary-color); }
        .form-group { margin-bottom: 1.5rem; text-align: left; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.875rem; color: var(--text-muted); }
        .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-family: inherit; font-size: 1rem; }
        .form-control:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px var(--primary-light); }
        .btn-submit { width: 100%; padding: 0.75rem; background-color: var(--primary-color); color: white; border: none; border-radius: var(--radius-md); font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 1rem; }
        .btn-submit:hover { background-color: var(--primary-hover); }
        .error-msg { color: var(--danger); font-size: 0.875rem; margin-bottom: 1rem; padding: 0.5rem; background: #fee2e2; border-radius: 4px; }
        .login-info { margin-top: 1.5rem; font-size: 0.75rem; color: var(--text-muted); background: var(--primary-light); padding: 1rem; border-radius: var(--radius-md); text-align: left; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo" style="display:flex; align-items:center; justify-content:center; gap:0.75rem;">
            <img src="<?= base_url('/') ?>img/logo.webp" alt="Logo" style="height:48px; width:auto;">
            <div style="display: flex; flex-direction: column; align-items: flex-start;">
                <div style="line-height: 1;">HTV<span>PERU</span></div>
                <span style="font-size: 0.70rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 2px;">Una Mirada al Mundo</span>
            </div>
        </div>
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($msg_success)): ?>
            <div style="background:#dcfce7; color:#166534; padding:1rem; border-radius:4px; font-size:0.875rem; margin-bottom:1rem;"><?php echo htmlspecialchars($msg_success); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['recover']) || (isset($action) && $action === 'recover')): ?>
        <form method="POST" action="<?= base_url('/') ?>login.php">
            <input type="hidden" name="action" value="recover">
            <div class="form-group">
                <label for="email">Ingresa tu Correo Registrado</label>
                <input type="email" name="email" id="email" class="form-control" required placeholder="tu-email@dominio.com">
            </div>
            <button type="submit" class="btn-submit" style="background:#4b5563;">Enviar Enlace de Recuperación</button>
            <a href="<?= base_url('/') ?>login.php" style="display:block; margin-top:1.5rem; font-size:0.875rem; color:var(--text-muted); text-decoration:none;"><i class="ri-arrow-left-line"></i> Volver a Iniciar Sesión</a>
        </form>
        <?php else: ?>
        <form method="POST" action="<?= base_url('/') ?>login.php">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label for="email">Usuario (Email)</label>
                <input type="email" name="email" id="email" class="form-control" required placeholder="tu-email@dominio.com">
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
                <a href="<?= base_url('/') ?>login.php?recover=1" style="display:block; text-align:right; font-size:0.75rem; margin-top:0.5rem; color:var(--text-muted); text-decoration:none;">¿Olvidaste tu contraseña?</a>
            </div>
            <button type="submit" class="btn-submit">Ingresar Seguramente</button>
        </form>
        <?php endif; ?>
        
        <a href="<?= base_url('/') ?>index.php" style="display: block; margin-top: 1.5rem; font-size: 0.875rem; color: var(--primary-color);">Volver al sitio público</a>
    </div>
</body>
</html>
