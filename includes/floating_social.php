<?php
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
<style>
/* WhatsApp Pill Button */
.whatsapp-float-global {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    color: white;
    padding: 12px 24px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
    font-weight: 800;
    text-decoration: none;
    box-shadow: 0 10px 20px rgba(37, 211, 102, 0.4);
    z-index: 9999;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    font-family: var(--font-sans, 'Inter', sans-serif);
}
.whatsapp-float-global:hover {
    transform: translateY(-5px) scale(1.03);
    box-shadow: 0 15px 30px rgba(37, 211, 102, 0.5);
    color: white;
}
.whatsapp-float-global i { font-size: 1.6rem; animation: pulseWpp 2s infinite; }

/* Social Icons Stack */
.floating-social-column {
    position: fixed;
    bottom: 90px;
    right: 25px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    z-index: 9000;
    align-items: flex-end;
}

.social-circle-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    text-decoration: none;
    color: white;
    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    opacity: 0.95;
    position: relative;
    overflow: hidden;
}

.social-circle-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.2);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.social-circle-btn:hover {
    transform: translateY(-5px) scale(1.1);
    opacity: 1;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    color: white;
}

.social-circle-btn:hover::before { opacity: 1; }

.sc-fb { background: #1877f2; }
.sc-tw { background: #0f1419; } /* Modern X dark */
.sc-ig { background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); }
.sc-yt { background: #ff0000; }
.sc-tk { background: #000000; }
.sc-twc { background: #9146ff; }
.sc-kk { background: #53fc18; color: #000 !important; }
.sc-th { background: #000000; }
.sc-tg { background: #0088cc; }
.sc-dc { background: #5865F2; }
.sc-pt { background: #E60023; }
.sc-sp { background: #1DB954; }
.sc-in { background: #0a66c2; }

@keyframes pulseWpp {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

@media(max-width: 768px) {
    .whatsapp-float-global span { display: none; }
    .whatsapp-float-global { padding: 15px; border-radius: 50%; right: 15px; bottom: 15px; }
    .whatsapp-float-global i { margin: 0; font-size: 1.8rem; }
    .floating-social-column { bottom: 85px; right: 20px; }
}
</style>

<!-- Floating Social Column (Above WhatsApp) -->
<?php 
$has_social = false;
$socials = ['social_facebook', 'social_twitter', 'social_instagram', 'social_youtube', 'social_tiktok', 'social_twitch', 'social_kick', 'social_threads', 'social_telegram', 'social_discord', 'social_pinterest', 'social_spotify', 'social_linkedin'];
foreach($socials as $s) { if(!empty($global_configs[$s])) { $has_social = true; break; } }
if ($has_social): 
?>
<div class="floating-social-column">
    <?php if(!empty($global_configs['social_facebook'])): ?>
    <a href="<?php echo htmlspecialchars($global_configs['social_facebook']); ?>" target="_blank" class="social-circle-btn sc-fb" title="Facebook"><i class="ri-facebook-fill"></i></a>
    <?php endif; ?>
    <?php if(!empty($global_configs['social_twitter'])): ?>
    <a href="<?php echo htmlspecialchars($global_configs['social_twitter']); ?>" target="_blank" class="social-circle-btn sc-tw" title="Twitter / X"><i class="ri-twitter-x-fill"></i></a>
    <?php endif; ?>
    <?php if(!empty($global_configs['social_threads'])): ?>
    <a href="<?php echo htmlspecialchars($global_configs['social_threads']); ?>" target="_blank" class="social-circle-btn sc-th" title="Threads"><i class="ri-threads-fill"></i></a>
    <?php endif; ?>
    <?php if(!empty($global_configs['social_instagram'])): ?>
    <a href="<?php echo htmlspecialchars($global_configs['social_instagram']); ?>" target="_blank" class="social-circle-btn sc-ig" title="Instagram"><i class="ri-instagram-fill"></i></a>
    <?php endif; ?>
    <?php if(!empty($global_configs['social_youtube'])): ?>
    <a href="<?php echo htmlspecialchars($global_configs['social_youtube']); ?>" target="_blank" class="social-circle-btn sc-yt" title="YouTube"><i class="ri-youtube-fill"></i></a>
    <?php endif; ?>
    <?php if(!empty($global_configs['social_tiktok'])): ?>
    <a href="<?php echo htmlspecialchars($global_configs['social_tiktok']); ?>" target="_blank" class="social-circle-btn sc-tk" title="TikTok"><i class="ri-tiktok-fill"></i></a>
    <?php endif; ?>
    <?php if(!empty($global_configs['social_twitch'])): ?>
    <a href="<?php echo htmlspecialchars($global_configs['social_twitch']); ?>" target="_blank" class="social-circle-btn sc-twc" title="Twitch"><i class="ri-twitch-fill"></i></a>
    <?php endif; ?>
    <?php if(!empty($global_configs['social_kick'])): ?>
    <a href="<?php echo htmlspecialchars($global_configs['social_kick']); ?>" target="_blank" class="social-circle-btn sc-kk" title="Kick"><i class="ri-live-fill"></i></a>
    <?php endif; ?>
    <?php if(!empty($global_configs['social_telegram'])): ?>
    <a href="<?php echo htmlspecialchars($global_configs['social_telegram']); ?>" target="_blank" class="social-circle-btn sc-tg" title="Telegram"><i class="ri-telegram-fill"></i></a>
    <?php endif; ?>
    <?php if(!empty($global_configs['social_discord'])): ?>
    <a href="<?php echo htmlspecialchars($global_configs['social_discord']); ?>" target="_blank" class="social-circle-btn sc-dc" title="Discord"><i class="ri-discord-fill"></i></a>
    <?php endif; ?>
    <?php if(!empty($global_configs['social_pinterest'])): ?>
    <a href="<?php echo htmlspecialchars($global_configs['social_pinterest']); ?>" target="_blank" class="social-circle-btn sc-pt" title="Pinterest"><i class="ri-pinterest-fill"></i></a>
    <?php endif; ?>
    <?php if(!empty($global_configs['social_spotify'])): ?>
    <a href="<?php echo htmlspecialchars($global_configs['social_spotify']); ?>" target="_blank" class="social-circle-btn sc-sp" title="Spotify"><i class="ri-spotify-fill"></i></a>
    <?php endif; ?>
    <?php if(!empty($global_configs['social_linkedin'])): ?>
    <a href="<?php echo htmlspecialchars($global_configs['social_linkedin']); ?>" target="_blank" class="social-circle-btn sc-in" title="LinkedIn"><i class="ri-linkedin-fill"></i></a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Floating WhatsApp (Bottom Right) -->
<?php if(!empty($global_configs['social_whatsapp'])): ?>
<a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $global_configs['social_whatsapp']); ?>?text=Hola%20HTVPERU,%20tengo%20información%20o%20denuncia" target="_blank" class="whatsapp-float-global">
    <i class="ri-whatsapp-fill"></i> <span>¿Hola, necesitas denunciar algo?</span>
</a>
<?php endif; ?>
