<!-- INICIO MODAL PRIVACIDAD -->
<?php if(!empty($global_configs['privacy_policy_url'])): ?>
<style>
.privacy-modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px); z-index: 99999; display: none; align-items: center; justify-content: center;
    opacity: 0; transition: opacity 0.3s ease;
}
.privacy-modal-overlay.active { display: flex; opacity: 1; }
.privacy-modal-container {
    background: white; width: 90%; max-width: 800px; max-height: 90vh; border-radius: 12px;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); display: flex; flex-direction: column; overflow: hidden;
    transform: translateY(20px); transition: transform 0.3s ease;
}
.privacy-modal-overlay.active .privacy-modal-container { transform: translateY(0); }
.privacy-modal-header {
    background: var(--primary-color); color: white; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center;
}
.privacy-modal-header h3 { margin: 0; font-size: 1.25rem; font-weight: 700; display:flex; align-items:center; gap:0.5rem; }
.privacy-modal-close {
    background: transparent; border: none; color: white; font-size: 1.5rem; cursor: pointer; opacity: 0.8; transition: opacity 0.2s;
}
.privacy-modal-close:hover { opacity: 1; }
.privacy-modal-body { flex: 1; padding: 0; min-height: 60vh; background:#f1f5f9; }
</style>
<div id="privacyPolicyModal" class="privacy-modal-overlay" onclick="closePrivacyModal(event)">
    <div class="privacy-modal-container" onclick="event.stopPropagation()">
        <div class="privacy-modal-header">
            <h3><i class="ri-shield-keyhole-line"></i> Políticas de Privacidad</h3>
            <button class="privacy-modal-close" onclick="closePrivacyModal(event)">&times;</button>
        </div>
        <div class="privacy-modal-body">
            <iframe id="privacyPolicyIframe" data-src="<?php echo htmlspecialchars($global_configs['privacy_policy_url']); ?>#toolbar=0" style="width:100%; height:100%; border:none;"></iframe>
        </div>
    </div>
</div>
<script>
function openPrivacyModal(e) {
    if(e) e.preventDefault();
    var iframe = document.getElementById("privacyPolicyIframe");
    if(iframe && !iframe.src && iframe.dataset.src) {
        iframe.src = iframe.dataset.src;
    }
    document.getElementById("privacyPolicyModal").classList.add("active");
    document.body.style.overflow = "hidden";
}
function closePrivacyModal(e) {
    if(e) e.preventDefault();
    var iframe = document.getElementById("privacyPolicyIframe");
    if(iframe) iframe.removeAttribute("src");
    document.getElementById("privacyPolicyModal").classList.remove("active");
    document.body.style.overflow = "auto";
}
</script>
<?php endif; ?>
<!-- FIN MODAL PRIVACIDAD -->
