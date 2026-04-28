<?php
// includes/sidebar_social.php
// Carga configuraciones si no están cargadas previamente
if (!isset($global_configs) || !is_array($global_configs)) {
    $global_configs = [];
    try {
        if(isset($pdo)) {
            $c_stmt = $pdo->query("SELECT clave, valor FROM configuracion");
            while($r = $c_stmt->fetch()){ $global_configs[$r['clave']] = $r['valor']; }
        }
    } catch(Exception $e){}
}
?>

<?php if (false): // Desactivado porque ahora es flotante global ?>
<div class="sidebar-widget" style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:1.5rem; margin-top:2rem;">
    <h3 style="margin-top:0; border-bottom:2px solid var(--primary-color); padding-bottom:0.5rem; font-family:var(--font-sans); color:var(--text-main);">
        <i class="ri-instagram-fill" style="color:#d946ef;"></i> Síguenos
    </h3>
    <div style="display:flex; gap:0.5rem; margin-top:1rem; flex-wrap:wrap; justify-content:flex-start;">
        <?php if(!empty($global_configs['social_facebook'])): ?>
        <a href="<?php echo htmlspecialchars($global_configs['social_facebook']); ?>" target="_blank" style="width: 44px; height: 44px; background:var(--primary-light); color:var(--primary-color); display:flex; align-items:center; justify-content:center; border-radius:50%; font-size:1.35rem; transition:transform 0.2s;"><i class="ri-facebook-fill"></i></a>
        <?php endif; ?>
        
        <?php if(!empty($global_configs['social_twitter'])): ?>
        <a href="<?php echo htmlspecialchars($global_configs['social_twitter']); ?>" target="_blank" style="width: 44px; height: 44px; background:var(--primary-light); color:var(--text-main); display:flex; align-items:center; justify-content:center; border-radius:50%; font-size:1.35rem; transition:transform 0.2s;"><i class="ri-twitter-x-fill"></i></a>
        <?php endif; ?>
        
        <?php if(!empty($global_configs['social_instagram'])): ?>
        <a href="<?php echo htmlspecialchars($global_configs['social_instagram']); ?>" target="_blank" style="width: 44px; height: 44px; background:#fce7f3; color:#d946ef; display:flex; align-items:center; justify-content:center; border-radius:50%; font-size:1.35rem; transition:transform 0.2s;"><i class="ri-instagram-fill"></i></a>
        <?php endif; ?>
        
        <?php if(!empty($global_configs['social_youtube'])): ?>
        <a href="<?php echo htmlspecialchars($global_configs['social_youtube']); ?>" target="_blank" style="width: 44px; height: 44px; background:#fee2e2; color:#ef4444; display:flex; align-items:center; justify-content:center; border-radius:50%; font-size:1.35rem; transition:transform 0.2s;"><i class="ri-youtube-fill"></i></a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
