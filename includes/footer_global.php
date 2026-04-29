<footer class="footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <div style="display:flex; align-items:center; gap:1.25rem; margin-bottom:1.5rem;">
                <img src="<?php echo htmlspecialchars(!empty($global_configs['logo_url']) ? $global_configs['logo_url'] : 'img/logo.webp'); ?>" alt="Logo" class="footer-logo" style="height:56px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.4));">
                <div style="display: flex; flex-direction: column;">
                    <h2 class="logo-title-footer" style="font-size: 2.6rem; margin:0; line-height: 1; font-family: 'Arial Black', 'Montserrat', Impact, var(--font-sans); font-weight:900; letter-spacing:-1.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.8);"><span class="htv-text" style="color:white;">HTV</span><span class="peru-text" style="color:var(--primary-color);">PERU</span></h2>
                    <span style="font-size: 0.70rem; color: #9ca3af; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; margin-top: 4px;"><?php echo htmlspecialchars($global_configs['site_slogan'] ?? 'Una Mirada al Mundo'); ?></span>
                </div>
            </div>
            <div style="width: 40px; height: 3px; background-color: var(--primary-color); margin-bottom: 1rem; border-radius:3px;"></div>
            <p style="color: #9ca3af; font-size: 0.90rem; line-height: 1.6; max-width: 320px; font-weight: 500;">
                El portal de noticias líder en la región, manteniéndote informado con la verdad, rapidez y objetividad que mereces de forma independiente.
            </p>
        </div>
        <div class="footer-links">
            <h4>Secciones</h4>
            <ul style="list-style:none; padding:0; margin:0;">
                <?php foreach($menu_cats_dynamic as $c): ?>
                <li style="margin-bottom:0.5rem;"><a href="category.php?slug=<?php echo urlencode($c['slug']); ?>"><?php echo htmlspecialchars($c['nombre']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="footer-links">
            <h4>Legal / Red</h4>
            <ul style="list-style:none; padding:0; margin:0;">
                <?php
                if (!isset($pdo)) {
                    require_once 'conexion.php';
                }
                $pag_stmt = $pdo->query("SELECT titulo, slug FROM paginas WHERE estado='activo' ORDER BY titulo ASC");
                while($p = $pag_stmt->fetch()):
                ?>
                <li style="margin-bottom:0.5rem;"><a href="pagina.php?s=<?php echo $p['slug']; ?>"><?php echo htmlspecialchars($p['titulo']); ?></a></li>
                <?php endwhile; ?>
                <?php if(!empty($global_configs['privacy_policy_url'])): ?>
                <li style="margin-bottom:0.5rem; margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 0.5rem;">
                    <a href="<?php echo htmlspecialchars($global_configs['privacy_policy_url']); ?>" target="_blank">
                        <i class="ri-shield-keyhole-line" style="vertical-align:middle; margin-right:4px;"></i> Políticas de Privacidad
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="footer-contact">
            <h4>Contacto</h4>
            <p>Email: <?php echo htmlspecialchars($global_configs['contact_email'] ?? 'prensa@htv.pe'); ?></p>
            <p>WhatsApp: <?php echo htmlspecialchars($global_configs['contact_phone'] ?? '+51 ...'); ?></p>
            <div class="footer-social" style="display:flex; gap:1rem; margin-top:1rem; font-size:1.5rem;">
                <?php if(!empty($global_configs['social_facebook'])): ?><a href="<?php echo htmlspecialchars($global_configs['social_facebook']); ?>" target="_blank"><i class="ri-facebook-circle-fill"></i></a><?php endif; ?>
                <?php if(!empty($global_configs['social_twitter'])): ?><a href="<?php echo htmlspecialchars($global_configs['social_twitter']); ?>" target="_blank"><i class="ri-twitter-x-line"></i></a><?php endif; ?>
                <?php if(!empty($global_configs['social_instagram'])): ?><a href="<?php echo htmlspecialchars($global_configs['social_instagram']); ?>" target="_blank"><i class="ri-instagram-line"></i></a><?php endif; ?>
                <?php if(!empty($global_configs['social_youtube'])): ?><a href="<?php echo htmlspecialchars($global_configs['social_youtube']); ?>" target="_blank"><i class="ri-youtube-fill"></i></a><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="footer-bottom" style="text-align: center; padding: 2rem; background: #030712; color: #4b5563; margin-top:2rem;">
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($global_configs['footer_text'] ?? 'HTVPERU. Todos los derechos reservados.'); ?></p>
    </div>
</footer>

<!-- PWA Service Worker Registration -->
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/piura_noticias_php/sw.js').then(function(registration) {
      console.log('ServiceWorker registration successful with scope: ', registration.scope);
    }, function(err) {
      console.log('ServiceWorker registration failed: ', err);
    });
  });
}
</script>
