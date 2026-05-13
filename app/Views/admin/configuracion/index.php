<?php
// app/Views/admin/configuracion/index.php
// Variables: $msg, $configs, $categorias_select
?>

<style>
    /* ═══════════════════════════════════════════════
       PREMIUM CONFIGURATION PANEL — Design System
       ═══════════════════════════════════════════════ */

    .cfg-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.75rem; }
    @media(max-width: 1024px) { .cfg-grid { grid-template-columns: 1fr; } }

    /* ── Panel Cards ── */
    .cfg-panel {
        background: white;
        padding: 2rem 2.25rem;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 16px rgba(0,0,0,0.03);
        margin-bottom: 1.75rem;
        border: 1px solid #e2e8f0;
        border-top: none;
        position: relative;
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }
    .cfg-panel::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), #3b82f6, #60a5fa);
        border-radius: 16px 16px 0 0;
    }
    .cfg-panel:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.06), 0 8px 32px rgba(0,0,0,0.04);
    }
    .cfg-panel h3 {
        margin-top: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 0.85rem;
        margin-bottom: 1.75rem;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        letter-spacing: -0.01em;
    }
    .cfg-panel h3 i {
        font-size: 1.3rem;
        background: linear-gradient(135deg, var(--primary-color), #3b82f6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* ── Form Groups ── */
    .form-group { margin-bottom: 1.5rem; }
    .form-group label {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-weight: 700;
        font-size: 0.82rem;
        margin-bottom: 0.45rem;
        color: #334155;
        letter-spacing: 0.01em;
        text-transform: uppercase;
    }
    .form-group label i {
        font-size: 1rem;
    }
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="url"],
    .form-group input[type="number"],
    .form-group input[type="password"] {
        width: 100%;
        padding: 0.7rem 0.95rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-family: inherit;
        font-size: 0.92rem;
        box-sizing: border-box;
        background: #f8fafc;
        color: #1e293b;
        transition: all 0.25s ease;
    }
    .form-group input[type="text"]:focus,
    .form-group input[type="email"]:focus,
    .form-group input[type="url"]:focus,
    .form-group input[type="number"]:focus,
    .form-group input[type="password"]:focus {
        border-color: var(--primary-color);
        background: white;
        outline: none;
        box-shadow: 0 0 0 3.5px rgba(37, 99, 235, 0.1);
    }
    .form-group input[type="color"] {
        width: 52px;
        height: 44px;
        padding: 3px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .form-group input[type="color"]:hover { border-color: var(--primary-color); }

    .form-group input[type="file"] {
        width: 100%;
        padding: 0.65rem 0.85rem;
        border: 2px dashed #cbd5e1;
        border-radius: 10px;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        box-sizing: border-box;
        font-size: 0.85rem;
        color: #64748b;
        cursor: pointer;
        transition: all 0.25s ease;
    }
    .form-group input[type="file"]:hover {
        border-color: var(--primary-color);
        background: linear-gradient(135deg, #eff6ff, #e0f2fe);
    }
    .form-group select {
        width: 100%;
        padding: 0.7rem 0.95rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        box-sizing: border-box;
        font-family: inherit;
        font-size: 0.92rem;
        background: #f8fafc;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.25s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 2.5rem;
    }
    .form-group select:focus {
        border-color: var(--primary-color);
        background-color: white;
        outline: none;
        box-shadow: 0 0 0 3.5px rgba(37, 99, 235, 0.1);
    }
    .form-group textarea {
        width: 100%;
        padding: 0.75rem 0.95rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-family: inherit;
        font-size: 0.92rem;
        box-sizing: border-box;
        background: #f8fafc;
        color: #1e293b;
        resize: vertical;
        transition: all 0.25s ease;
    }
    .form-group textarea:focus {
        border-color: var(--primary-color);
        background: white;
        outline: none;
        box-shadow: 0 0 0 3.5px rgba(37, 99, 235, 0.1);
    }

    /* ── Image Previews ── */
    .img-preview {
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        padding: 1.25rem;
        text-align: center;
        border-radius: 10px;
        margin-bottom: 0.5rem;
        border: 1.5px solid #e2e8f0;
        transition: border-color 0.2s;
    }
    .img-preview:hover { border-color: #94a3b8; }
    .img-preview img { max-width: 100%; max-height: 80px; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1)); }

    /* ── Save Button ── */
    .btn-save {
        background: linear-gradient(135deg, var(--primary-color), #2563eb);
        color: white;
        border: none;
        padding: 0.85rem 1.75rem;
        border-radius: 12px;
        font-size: 0.95rem;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        letter-spacing: 0.01em;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
    }
    .btn-save:hover {
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
    }
    .btn-save:active { transform: translateY(0); }

    /* ── Alert ── */
    .alert {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #14532d;
        padding: 1rem 1.25rem;
        border-radius: 12px;
        font-weight: 600;
        margin-bottom: 1.5rem;
        border-left: 4px solid #22c55e;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.92rem;
    }

    /* ═══ TABS LATERALES PREMIUM ═══ */
    .settings-container {
        display: flex;
        gap: 2rem;
        align-items: flex-start;
        margin-top: 1.5rem;
    }
    .settings-sidebar {
        min-width: 260px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 16px rgba(0,0,0,0.03);
        overflow: hidden;
        position: sticky;
        top: 80px;
        border: 1px solid #e2e8f0;
    }
    .settings-tab {
        display: flex;
        align-items: center;
        gap: 11px;
        width: 100%;
        text-align: left;
        padding: 1.1rem 1.5rem;
        background: none;
        border: none;
        border-left: 4px solid transparent;
        font-size: 0.92rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.25s ease;
        border-bottom: 1px solid #f1f5f9;
        font-family: inherit;
        position: relative;
    }
    .settings-tab:last-child { border-bottom: none; }
    .settings-tab:hover {
        background: #f8fafc;
        color: var(--primary-color);
        padding-left: 1.65rem;
    }
    .settings-tab.active {
        background: linear-gradient(90deg, #eff6ff, #f8fafc);
        color: var(--primary-color);
        border-left-color: var(--primary-color);
        font-weight: 700;
    }
    .settings-tab i { font-size: 1.2rem; opacity: 0.85; }
    .settings-tab.active i { opacity: 1; }

    .settings-content { flex: 1; min-width: 0; }
    .tab-pane { display: none; }
    .tab-pane.active { display: block; animation: cfgFadeIn 0.35s ease-out forwards; }
    @keyframes cfgFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ═══ SOCIAL MEDIA CARDS ═══ */
    .social-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
    }
    .social-card {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.85rem 1rem;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.25s ease;
    }
    .social-card:hover {
        border-color: var(--social-accent, #94a3b8);
        background: white;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    .social-card .social-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        flex-shrink: 0;
        background: var(--social-accent, #64748b);
    }
    .social-card .social-input-wrap {
        flex: 1;
        min-width: 0;
    }
    .social-card .social-input-wrap label {
        display: block;
        font-weight: 700;
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .social-card .social-input-wrap input {
        width: 100%;
        padding: 0.45rem 0.65rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-family: inherit;
        font-size: 0.85rem;
        box-sizing: border-box;
        background: white;
        color: #1e293b;
        transition: all 0.2s;
    }
    .social-card .social-input-wrap input:focus {
        border-color: var(--social-accent, var(--primary-color));
        outline: none;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
    }

    /* ═══ CONTACT INFO BLOCK ═══ */
    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }
    @media(max-width: 700px) { .contact-grid { grid-template-columns: 1fr; } }

    .contact-field {
        position: relative;
    }
    .contact-field .field-icon {
        position: absolute;
        top: 2.35rem;
        left: 0.85rem;
        font-size: 1.1rem;
        color: #94a3b8;
        pointer-events: none;
        z-index: 1;
    }
    .contact-field input {
        padding-left: 2.5rem !important;
    }

    /* ═══ PRIVACY / PDF BLOCK ═══ */
    .pdf-upload-zone {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        padding: 1.75rem;
        border-radius: 12px;
        text-align: center;
        border: 2px dashed #cbd5e1;
        margin-bottom: 0.65rem;
        transition: all 0.25s;
    }
    .pdf-upload-zone:hover {
        border-color: var(--primary-color);
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
    }
    .pdf-upload-zone i {
        font-size: 2.5rem;
        display: block;
        margin-bottom: 0.35rem;
    }

    /* ═══ FLOATING SAVE BAR ═══ */
    .floating-save-bar {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 9999;
        background: white;
        padding: 0.75rem 1rem;
        border-radius: 50px;
        box-shadow: 0 8px 32px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(148, 163, 184, 0.15);
        display: flex;
        align-items: center;
        gap: 1rem;
        backdrop-filter: blur(12px);
        animation: floatUp 0.5s ease-out;
    }
    @keyframes floatUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ═══ SECTION DESCRIPTIONS ═══ */
    .section-desc {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: -0.75rem;
        margin-bottom: 1.75rem;
        line-height: 1.5;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .section-desc i { font-size: 1rem; color: #94a3b8; }

    /* ═══ LAYOUT MODULE CARDS ═══ */
    .module-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 1.25rem;
        margin-top: 0.5rem;
    }
    .module-card {
        background: white;
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
    }
    .module-card:hover {
        border-color: #93c5fd;
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.1);
        transform: translateY(-2px);
    }
    .module-card.is-inactive {
        border-color: #fecaca;
    }
    .module-card.is-inactive .module-header {
        background: linear-gradient(135deg, #fef2f2, #fff5f5);
    }
    .module-card.is-inactive .module-icon-wrap {
        background: linear-gradient(135deg, #fca5a5, #f87171) !important;
    }
    .module-header {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 1.15rem 1.25rem;
        background: linear-gradient(135deg, #f8fafc, #eff6ff);
        border-bottom: 1px solid #e2e8f0;
    }
    .module-icon-wrap {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: white;
        background: linear-gradient(135deg, var(--primary-color), #3b82f6);
        flex-shrink: 0;
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25);
    }
    .module-info h4 {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .module-info p {
        margin: 0.15rem 0 0 0;
        font-size: 0.72rem;
        color: #94a3b8;
        line-height: 1.3;
    }
    .module-body {
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .module-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.3rem 0.7rem;
        border-radius: 20px;
        background: #dcfce7;
        color: #166534;
    }
    .module-status.off {
        background: #fee2e2;
        color: #991b1b;
    }
    .module-status .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #22c55e;
        display: inline-block;
        animation: statusPulse 2s ease-in-out infinite;
    }
    .module-status.off .status-dot {
        background: #ef4444;
        animation: none;
    }
    @keyframes statusPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    /* CSS Toggle Switch */
    .toggle-switch {
        position: relative;
        width: 48px;
        height: 26px;
        flex-shrink: 0;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }
    .toggle-switch .slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background: #cbd5e1;
        border-radius: 26px;
        transition: all 0.35s ease;
    }
    .toggle-switch .slider::before {
        content: '';
        position: absolute;
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background: white;
        border-radius: 50%;
        transition: all 0.35s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .toggle-switch input:checked + .slider {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        box-shadow: 0 0 8px rgba(34, 197, 94, 0.3);
    }
    .toggle-switch input:checked + .slider::before {
        transform: translateX(22px);
    }
    .toggle-switch input:focus + .slider {
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
    }

    /* Hidden native select for form submission */
    .module-card select.module-native-select {
        display: none;
    }

    /* ═══ API PANELS ═══ */
    .api-panel {
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.75rem;
        background: white;
        position: relative;
        overflow: hidden;
        transition: all 0.25s;
    }
    .api-panel::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: var(--api-accent, #e2e8f0);
    }
    .api-panel:hover {
        border-color: var(--api-accent, #94a3b8);
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    }
    .api-panel h4 {
        margin-top: 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 1rem;
        font-weight: 800;
        color: var(--api-accent, #475569);
    }
    .api-panel h4 i { font-size: 1.4rem; }
    .api-panel > p {
        font-size: 0.82rem;
        color: #64748b;
        margin-top: -0.25rem;
        line-height: 1.5;
    }

    /* ═══ DANGER ZONE ═══ */
    .danger-zone {
        background: linear-gradient(135deg, #fff5f5, #fee2e2);
        padding: 1.75rem;
        border-radius: 12px;
        border: 1.5px solid #fecaca;
    }

    /* ═══ DARK MODE OVERRIDES ═══ */
    html[data-admin-theme="dark"] .cfg-panel {
        background: #1e293b;
        border-color: #334155;
    }
    html[data-admin-theme="dark"] .cfg-panel h3 {
        color: #f1f5f9;
        border-bottom-color: #334155;
    }
    html[data-admin-theme="dark"] .settings-sidebar {
        background: #1e293b;
        border-color: #334155;
    }
    html[data-admin-theme="dark"] .settings-tab {
        color: #94a3b8;
        border-bottom-color: #334155;
    }
    html[data-admin-theme="dark"] .settings-tab:hover {
        background: #334155;
        color: #60a5fa;
    }
    html[data-admin-theme="dark"] .settings-tab.active {
        background: linear-gradient(90deg, rgba(37,99,235,0.15), transparent);
        color: #60a5fa;
        border-left-color: #3b82f6;
    }
    html[data-admin-theme="dark"] .social-card {
        background: #0f172a;
        border-color: #334155;
    }
    html[data-admin-theme="dark"] .social-card:hover {
        background: #1e293b;
    }
    html[data-admin-theme="dark"] .social-card .social-input-wrap input {
        background: #1e293b;
        border-color: #475569;
        color: #e2e8f0;
    }
    html[data-admin-theme="dark"] .social-card .social-input-wrap label {
        color: #94a3b8;
    }
    html[data-admin-theme="dark"] .form-group label { color: #94a3b8; }
    html[data-admin-theme="dark"] .form-group input,
    html[data-admin-theme="dark"] .form-group select,
    html[data-admin-theme="dark"] .form-group textarea {
        background: #0f172a;
        border-color: #475569;
        color: #e2e8f0;
    }
    html[data-admin-theme="dark"] .module-card {
        background: #1e293b;
        border-color: #334155;
    }
    html[data-admin-theme="dark"] .module-header {
        background: linear-gradient(135deg, #1e293b, #0f172a);
        border-bottom-color: #334155;
    }
    html[data-admin-theme="dark"] .module-info h4 { color: #f1f5f9; }
    html[data-admin-theme="dark"] .module-info p { color: #64748b; }
    html[data-admin-theme="dark"] .module-card.is-inactive .module-header {
        background: linear-gradient(135deg, rgba(127,29,29,0.15), rgba(30,41,59,1));
    }
    html[data-admin-theme="dark"] .api-panel {
        background: #0f172a;
        border-color: #334155;
    }
    html[data-admin-theme="dark"] .floating-save-bar {
        background: #1e293b;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    }
    html[data-admin-theme="dark"] .pdf-upload-zone {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        border-color: #475569;
    }
    html[data-admin-theme="dark"] .danger-zone {
        background: linear-gradient(135deg, rgba(127,29,29,0.15), rgba(185,28,28,0.1));
        border-color: #7f1d1d;
    }
    html[data-admin-theme="dark"] .img-preview {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        border-color: #334155;
    }

    @media(max-width: 850px) {
        .settings-container { flex-direction: column; }
        .settings-sidebar { width: 100%; position: static; }
        .social-grid { grid-template-columns: 1fr; }
        .contact-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="admin-header">
    <div>
        <h1 style="margin:0;">Gestor de Configuración</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Control maestro de ajustes, SEO, apariencia y APIs del sistema.</p>
    </div>
</div>

<?php if($msg): ?>
    <div class="alert"><i class="ri-check-line"></i> <?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<form method="POST" action="<?= APP_BASE ?>/admin/configuracion/action" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save_config">
    <?php echo csrf_field(); ?>

    <div class="settings-container">
        <!-- BARRA LATERAL -->
        <div class="settings-sidebar">
            <button type="button" class="settings-tab active" data-target="tab-general"><i class="ri-settings-4-line"></i> General y SEO</button>
            <button type="button" class="settings-tab" data-target="tab-apariencia"><i class="ri-palette-line"></i> Apariencia y UI</button>
            <button type="button" class="settings-tab" data-target="tab-social"><i class="ri-share-line"></i> Redes y Contacto</button>
            <button type="button" class="settings-tab" data-target="tab-autopub"><i class="ri-rocket-2-fill"></i> Auto-Publicador</button>
            <button type="button" class="settings-tab" data-target="tab-avanzado"><i class="ri-code-s-slash-line"></i> Avanzado y APIs</button>
            <button type="button" class="settings-tab" data-target="tab-cuotas"><i class="ri-focus-3-line"></i> Cuotas de Redacción</button>
        </div>

        <!-- CONTENIDO -->
        <div class="settings-content">

            <!-- ========================= TAB GENERAL ========================= -->
            <div class="tab-pane active" id="tab-general">
                <div class="cfg-panel">
                    <h3><i class="ri-fingerprint-line"></i> Identidad del Sitio</h3>
                    
                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); margin-bottom: 1.5rem;">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #3b82f6;"><i class="ri-text"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Título del Portal</h4>
                                    <p>SEO Title y nombre de marca</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="text" name="site_title" value="<?php echo htmlspecialchars($configs['site_title'] ?? ''); ?>" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                            </div>
                        </div>
                        
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #8b5cf6;"><i class="ri-quote-text"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Eslogan</h4>
                                    <p>Lema debajo del título</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="text" name="site_slogan" value="<?php echo htmlspecialchars($configs['site_slogan'] ?? ''); ?>" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); margin-bottom: 1.5rem;">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #14b8a6;"><i class="ri-image-edit-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Logo Principal</h4>
                                    <p>Sube el isotipo/logotipo</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white; display: flex; flex-direction: column; gap: 0.75rem;">
                                <div class="img-preview" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; text-align: center;">
                                    <img src="<?php echo htmlspecialchars('<?= APP_BASE ?>/' . ($configs['logo_url'] ?? 'img/logo.webp')); ?>" alt="Logo" style="max-height: 50px;">
                                </div>
                                <input type="file" name="logo_upload" accept="image/*" style="width: 100%; font-size: 0.85rem;">
                            </div>
                        </div>
                        
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #f59e0b;"><i class="ri-window-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Favicon</h4>
                                    <p>Ícono para la pestaña del navegador</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white; display: flex; flex-direction: column; gap: 0.75rem;">
                                <div class="img-preview" style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; text-align: center;">
                                    <img src="<?php echo htmlspecialchars('<?= APP_BASE ?>/' . ($configs['favicon_url'] ?? 'img/logo.webp')); ?>" alt="Favicon" style="height:32px;">
                                </div>
                                <input type="file" name="favicon_upload" accept=".png,.ico,.svg" style="width: 100%; font-size: 0.85rem;">
                            </div>
                        </div>
                    </div>

                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); margin-bottom: 1.5rem;">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #64748b;"><i class="ri-drop-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Marca de Agua</h4>
                                    <p>.PNG Transparente</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white; display: flex; flex-direction: column; gap: 0.75rem;">
                                <div class="img-preview" style="background: var(--bg-main); border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; text-align: center;">
                                    <?php if(!empty($configs['watermark_url'])): ?>
                                    <img src="<?php echo htmlspecialchars('<?= APP_BASE ?>/' . $configs['watermark_url']); ?>" alt="Watermark" style="max-height: 40px;">
                                    <?php else: ?>
                                    <span style="font-size:0.75rem; color:var(--text-muted);">Sin marca de agua</span>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="watermark_upload" accept=".png" style="width: 100%; font-size: 0.85rem;">
                            </div>
                        </div>
                        
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #0ea5e9;"><i class="ri-layout-top-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Fondo Expandido</h4>
                                    <p>Portada o Cabecera del sitio</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white; display: flex; flex-direction: column; gap: 0.75rem;">
                                <div class="img-preview" style="background: var(--bg-main); border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; text-align: center;">
                                    <?php if(!empty($configs['header_bg_url'])): ?>
                                    <img src="<?php echo htmlspecialchars('<?= APP_BASE ?>/' . $configs['header_bg_url']); ?>" alt="Header BG" style="max-height: 40px; border-radius: 4px;">
                                    <?php else: ?>
                                    <span style="font-size:0.75rem; color:var(--text-muted);">Cabecera sólida (Sin fondo)</span>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="header_bg_upload" accept=".png,.jpg,.jpeg,.webp" style="width: 100%; font-size: 0.85rem;">
                            </div>
                        </div>
                    </div>

                    <?php $val_watermark = ($configs['watermark_estado'] ?? 'inactivo'); ?>
                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
                        <div class="module-card <?php echo $val_watermark === 'inactivo' ? 'is-inactive' : ''; ?>" id="card_wm">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: linear-gradient(135deg, #14b8a6, #0d9488);">
                                    <i class="ri-drop-fill"></i>
                                </div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Marca de Agua Automática</h4>
                                    <p>Inyecta la marca sobre las imágenes nuevas</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <span class="module-status <?php echo $val_watermark === 'inactivo' ? 'off' : ''; ?>" id="status_wm">
                                    <span class="status-dot"></span>
                                    <?php echo $val_watermark === 'activo' ? 'Activo' : 'Inactivo'; ?>
                                </span>
                                <label class="toggle-switch">
                                    <input type="checkbox" data-select="watermark_estado" <?php echo $val_watermark === 'activo' ? 'checked' : ''; ?> 
                                        onchange="document.getElementById('card_wm').classList.toggle('is-inactive', !this.checked); document.getElementById('status_wm').classList.toggle('off', !this.checked); document.getElementById('status_wm').innerHTML = '<span class=\'status-dot\'></span>' + (this.checked ? 'Activo' : 'Inactivo');">
                                    <span class="slider"></span>
                                </label>
                                <select name="watermark_estado" class="module-native-select">
                                    <option value="inactivo" <?php echo $val_watermark === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                    <option value="activo" <?php echo $val_watermark === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cfg-panel">
                    <h3><i class="ri-line-chart-line"></i> SEO Analíticas Globales</h3>
                    
                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); margin-bottom: 1.5rem;">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #eab308;"><i class="ri-bar-chart-2-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Google Analytics ID</h4>
                                    <p>Ejemplo: G-XXXXXXXXXX</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="text" name="google_analytics_id" value="<?php echo htmlspecialchars($configs['google_analytics_id'] ?? ''); ?>" placeholder="Opcional" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                            </div>
                        </div>
                        
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #1877f2;"><i class="ri-facebook-box-fill"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">ID del Píxel de Meta</h4>
                                    <p>Solo el ID numérico</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="text" name="facebook_pixel_id" value="<?php echo htmlspecialchars($configs['facebook_pixel_id'] ?? ''); ?>" placeholder="Opcional" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                            </div>
                        </div>
                    </div>

                    <div class="module-grid" style="grid-template-columns: 1fr; margin-bottom: 1.5rem;">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #6366f1;"><i class="ri-search-eye-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Meta Descripción Global</h4>
                                    <p>Para Google y Redes Sociales (OpenGraph)</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <textarea name="seo_og_desc" rows="3" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem; resize: vertical;"><?php echo htmlspecialchars($configs['seo_og_desc'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="module-grid" style="grid-template-columns: 1fr;">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #ec4899;"><i class="ri-image-2-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Miniatura Predeterminada</h4>
                                    <p>Se usará al compartir el enlace en WhatsApp/Facebook</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white; display: flex; flex-direction: column; gap: 0.75rem;">
                                <div class="img-preview" style="background: var(--bg-main); border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; text-align: center;">
                                    <?php if(!empty($configs['seo_og_image'])): ?>
                                    <img src="<?php echo htmlspecialchars('<?= APP_BASE ?>/' . $configs['seo_og_image']); ?>" alt="og:image" style="max-height: 80px;">
                                    <?php else: ?>
                                    <span style="font-size:0.75rem; color:var(--text-muted);">Sin imagen general</span>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="seo_og_image_upload" accept=".png,.jpg,.jpeg" style="width: 100%; font-size: 0.85rem;">
                                <small style="color:var(--text-muted); font-size: 0.8rem;">Ideal 1200x630px para vistas previas óptimas.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================= TAB APARIENCIA ========================= -->
            <div class="tab-pane" id="tab-apariencia">
                
                <div class="cfg-panel">
                    <h3><i class="ri-layout-top-line"></i> Personalización de Cabecera y Colores</h3>
                    
                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); margin-bottom: 1.5rem;">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #3b82f6;"><i class="ri-ruler-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Altura de la Cabecera</h4>
                                    <p>Medida en píxeles (px)</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="number" name="header_height" value="<?php echo htmlspecialchars($configs['header_height'] ?? '100'); ?>" min="60" max="300" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem; text-align: center;">
                            </div>
                        </div>

                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #14b8a6;"><i class="ri-aspect-ratio-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Tamaño del Logo</h4>
                                    <p>Escala proporcional</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="number" name="header_logo_scale" value="<?php echo htmlspecialchars($configs['header_logo_scale'] ?? '1.0'); ?>" min="0.5" max="2.5" step="0.1" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem; text-align: center;">
                            </div>
                        </div>
                    </div>

                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); margin-bottom: 1.5rem;">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #f59e0b;"><i class="ri-search-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Ancho del Buscador</h4>
                                    <p>Medida en píxeles (px)</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="number" name="header_search_width" value="<?php echo htmlspecialchars($configs['header_search_width'] ?? '280'); ?>" min="200" max="800" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem; text-align: center;">
                            </div>
                        </div>

                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #8b5cf6;"><i class="ri-space-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Separación de Botones</h4>
                                    <p>Medida en rem (Ej: 1.0)</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="number" name="header_actions_gap" value="<?php echo htmlspecialchars($configs['header_actions_gap'] ?? '1.0'); ?>" min="0.5" max="3.0" step="0.1" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem; text-align: center;">
                            </div>
                        </div>
                    </div>

                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #ef4444;"><i class="ri-paint-brush-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Color Primario</h4>
                                    <p>Color principal de la marca</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white; display: flex; align-items: center; gap: 0.5rem;">
                                <input type="color" name="color_primario" id="color_primario" value="<?php echo htmlspecialchars($configs['color_primario'] ?? '#2563eb'); ?>" style="width: 44px; height: 44px; padding: 0; border: none; border-radius: 6px; cursor: pointer;" oninput="document.getElementById('color_primario_txt').value = this.value;">
                                <input type="text" id="color_primario_txt" value="<?php echo htmlspecialchars($configs['color_primario'] ?? '#2563eb'); ?>" disabled class="modern-input" style="flex: 1; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem; background: #f8fafc;">
                            </div>
                        </div>

                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #64748b;"><i class="ri-contrast-drop-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Color Secundario</h4>
                                    <p>Color para hover y acentos</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white; display: flex; align-items: center; gap: 0.5rem;">
                                <input type="color" name="color_secundario" id="color_secundario" value="<?php echo htmlspecialchars($configs['color_secundario'] ?? '#1e40af'); ?>" style="width: 44px; height: 44px; padding: 0; border: none; border-radius: 6px; cursor: pointer;" oninput="document.getElementById('color_secundario_txt').value = this.value;">
                                <input type="text" id="color_secundario_txt" value="<?php echo htmlspecialchars($configs['color_secundario'] ?? '#1e40af'); ?>" disabled class="modern-input" style="flex: 1; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem; background: #f8fafc;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cfg-panel">
                    <h3><i class="ri-layout-masonry-line"></i> Control de Apariencia de Portada</h3>
                    <p class="section-desc"><i class="ri-information-line"></i> Activa o desactiva los módulos principales que se muestran en la portada pública del sitio.</p>

                    <div class="module-grid">
                        <?php $val_carrusel = ($configs['ui_mostrar_carrusel'] ?? 'activo'); ?>
                        <div class="module-card <?php echo $val_carrusel === 'inactivo' ? 'is-inactive' : ''; ?>" data-module="carrusel">
                            <div class="module-header">
                                <div class="module-icon-wrap" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                                    <i class="ri-slideshow-3-line"></i>
                                </div>
                                <div class="module-info">
                                    <h4>Carrusel Superior</h4>
                                    <p>Slider rotativo con noticias destacadas</p>
                                </div>
                            </div>
                            <div class="module-body">
                                <span class="module-status <?php echo $val_carrusel === 'inactivo' ? 'off' : ''; ?>">
                                    <span class="status-dot"></span>
                                    <?php echo $val_carrusel === 'activo' ? 'Visible' : 'Oculto'; ?>
                                </span>
                                <label class="toggle-switch">
                                    <input type="checkbox" data-select="ui_mostrar_carrusel" <?php echo $val_carrusel === 'activo' ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <select name="ui_mostrar_carrusel" class="module-native-select">
                                    <option value="activo" <?php echo $val_carrusel === 'activo' ? 'selected' : ''; ?>>Mostrar siempre</option>
                                    <option value="inactivo" <?php echo $val_carrusel === 'inactivo' ? 'selected' : ''; ?>>Ocultar Carrusel</option>
                                </select>
                            </div>
                        </div>

                        <?php $val_stories = ($configs['ui_mostrar_stories'] ?? 'activo'); ?>
                        <div class="module-card <?php echo $val_stories === 'inactivo' ? 'is-inactive' : ''; ?>" data-module="stories">
                            <div class="module-header">
                                <div class="module-icon-wrap" style="background: linear-gradient(135deg, #ec4899, #f43f5e);">
                                    <i class="ri-donut-chart-fill"></i>
                                </div>
                                <div class="module-info">
                                    <h4>Bloque de "Historias"</h4>
                                    <p>Círculos tipo stories de las categorías</p>
                                </div>
                            </div>
                            <div class="module-body">
                                <span class="module-status <?php echo $val_stories === 'inactivo' ? 'off' : ''; ?>">
                                    <span class="status-dot"></span>
                                    <?php echo $val_stories === 'activo' ? 'Visible' : 'Oculto'; ?>
                                </span>
                                <label class="toggle-switch">
                                    <input type="checkbox" data-select="ui_mostrar_stories" <?php echo $val_stories === 'activo' ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <select name="ui_mostrar_stories" class="module-native-select">
                                    <option value="activo" <?php echo $val_stories === 'activo' ? 'selected' : ''; ?>>Mostrar siempre</option>
                                    <option value="inactivo" <?php echo $val_stories === 'inactivo' ? 'selected' : ''; ?>>Ocultar círculos</option>
                                </select>
                            </div>
                        </div>

                        <?php $val_urgente = ($configs['ui_mostrar_urgente'] ?? 'activo'); ?>
                        <div class="module-card <?php echo $val_urgente === 'inactivo' ? 'is-inactive' : ''; ?>" data-module="urgente">
                            <div class="module-header">
                                <div class="module-icon-wrap" style="background: linear-gradient(135deg, #f59e0b, #f97316);">
                                    <i class="ri-flashlight-fill"></i>
                                </div>
                                <div class="module-info">
                                    <h4>Bloque "LO ÚLTIMO"</h4>
                                    <p>Feed de noticias urgentes y recientes</p>
                                </div>
                            </div>
                            <div class="module-body">
                                <span class="module-status <?php echo $val_urgente === 'inactivo' ? 'off' : ''; ?>">
                                    <span class="status-dot"></span>
                                    <?php echo $val_urgente === 'activo' ? 'Visible' : 'Oculto'; ?>
                                </span>
                                <label class="toggle-switch">
                                    <input type="checkbox" data-select="ui_mostrar_urgente" <?php echo $val_urgente === 'activo' ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <select name="ui_mostrar_urgente" class="module-native-select">
                                    <option value="activo" <?php echo $val_urgente === 'activo' ? 'selected' : ''; ?>>Mostrar siempre</option>
                                    <option value="inactivo" <?php echo $val_urgente === 'inactivo' ? 'selected' : ''; ?>>Ocultar Bloque</option>
                                </select>
                            </div>
                        </div>

                        <?php $val_policial = ($configs['ui_mostrar_policial'] ?? 'activo'); ?>
                        <div class="module-card <?php echo $val_policial === 'inactivo' ? 'is-inactive' : ''; ?>" data-module="policial">
                            <div class="module-header">
                                <div class="module-icon-wrap" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);">
                                    <i class="ri-police-car-fill"></i>
                                </div>
                                <div class="module-info">
                                    <h4>Sección Policial</h4>
                                    <p>Noticias de crónica roja y seguridad</p>
                                </div>
                            </div>
                            <div class="module-body">
                                <span class="module-status <?php echo $val_policial === 'inactivo' ? 'off' : ''; ?>">
                                    <span class="status-dot"></span>
                                    <?php echo $val_policial === 'activo' ? 'Visible' : 'Oculto'; ?>
                                </span>
                                <label class="toggle-switch">
                                    <input type="checkbox" data-select="ui_mostrar_policial" <?php echo $val_policial === 'activo' ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <select name="ui_mostrar_policial" class="module-native-select">
                                    <option value="activo" <?php echo $val_policial === 'activo' ? 'selected' : ''; ?>>Mostrar siempre</option>
                                    <option value="inactivo" <?php echo $val_policial === 'inactivo' ? 'selected' : ''; ?>>Ocultar Bloque</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cfg-panel">
                    <h3><i class="ri-tv-2-line"></i> Señal de TV En Vivo / Portada Dinámica</h3>

                    <?php $val_tv = ($configs['tv_envivo_estado'] ?? ''); ?>
                    <div class="module-grid" style="grid-template-columns: 1fr;">
                        <div class="module-card <?php echo $val_tv === 'inactivo' ? 'is-inactive' : ''; ?>">
                            <div class="module-header">
                                <div class="module-icon-wrap" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                                    <i class="ri-live-line"></i>
                                </div>
                                <div class="module-info">
                                    <h4>Botón "TV En Vivo"</h4>
                                    <p>Muestra el indicador de transmisión en vivo en el header</p>
                                </div>
                            </div>
                            <div class="module-body">
                                <span class="module-status <?php echo $val_tv === 'inactivo' ? 'off' : ''; ?>">
                                    <span class="status-dot"></span>
                                    <?php echo $val_tv === 'activo' ? 'Al Aire' : 'Apagado'; ?>
                                </span>
                                <label class="toggle-switch">
                                    <input type="checkbox" data-select="tv_envivo_estado" <?php echo $val_tv === 'activo' ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <select name="tv_envivo_estado" class="module-native-select">
                                    <option value="activo" <?php echo $val_tv === 'activo' ? 'selected' : ''; ?>>Encendido</option>
                                    <option value="inactivo" <?php echo $val_tv === 'inactivo' ? 'selected' : ''; ?>>Apagado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); margin-top: 1.25rem;">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #ef4444;"><i class="ri-youtube-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Enlace de Transmisión</h4>
                                    <p>URL de YouTube Embed o Streaming</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="url" name="tv_envivo_url" value="<?php echo htmlspecialchars($configs['tv_envivo_url'] ?? ''); ?>" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;" placeholder="https://...">
                            </div>
                        </div>

                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #f59e0b;"><i class="ri-flashlight-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Sección Urgente</h4>
                                    <p>¿Qué categoría poblará el bloque "Lo Último"?</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <select name="cat_urgente" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem; background-color: #f8fafc;">
                                    <?php foreach($categorias_select as $cs): ?>
                                    <option value="<?php echo htmlspecialchars($cs['nombre']); ?>" <?php echo ($configs['cat_urgente'] ?? '') === $cs['nombre'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cs['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #6366f1;"><i class="ri-slideshow-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Sección Carrusel</h4>
                                    <p>¿Qué categoría poblará el carrusel superior?</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <select name="cat_carrusel" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem; background-color: #f8fafc;">
                                    <?php foreach($categorias_select as $cs): ?>
                                    <option value="<?php echo htmlspecialchars($cs['nombre']); ?>" <?php echo ($configs['cat_carrusel'] ?? '') === $cs['nombre'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cs['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cfg-panel">
                    <h3><i class="ri-notification-badge-line"></i> Avisos Top-Bar y Cookies</h3>
                    <p class="section-desc"><i class="ri-information-line"></i> Controla las barras de aviso y el cumplimiento legal de cookies.</p>

                    <div class="module-grid">
                        <?php $val_alert = ($configs['alert_top_estado'] ?? 'inactivo'); ?>
                        <div class="module-card <?php echo $val_alert === 'inactivo' ? 'is-inactive' : ''; ?>">
                            <div class="module-header">
                                <div class="module-icon-wrap" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                    <i class="ri-megaphone-fill"></i>
                                </div>
                                <div class="module-info">
                                    <h4>Barra Superior de Avisos</h4>
                                    <p>Marquesina con texto animado en el top del sitio</p>
                                </div>
                            </div>
                            <div class="module-body">
                                <span class="module-status <?php echo $val_alert === 'inactivo' ? 'off' : ''; ?>">
                                    <span class="status-dot"></span>
                                    <?php echo $val_alert === 'activo' ? 'Activo' : 'Oculto'; ?>
                                </span>
                                <label class="toggle-switch">
                                    <input type="checkbox" data-select="alert_top_estado" <?php echo $val_alert === 'activo' ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <select name="alert_top_estado" class="module-native-select">
                                    <option value="inactivo" <?php echo $val_alert === 'inactivo' ? 'selected' : ''; ?>>Oculto</option>
                                    <option value="activo" <?php echo $val_alert === 'activo' ? 'selected' : ''; ?>>Mostrar marquesina</option>
                                </select>
                            </div>
                        </div>

                        <?php $val_cookie = ($configs['cookie_banner_estado'] ?? 'inactivo'); ?>
                        <div class="module-card <?php echo $val_cookie === 'inactivo' ? 'is-inactive' : ''; ?>">
                            <div class="module-header">
                                <div class="module-icon-wrap" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                                    <i class="ri-shield-check-fill"></i>
                                </div>
                                <div class="module-info">
                                    <h4>Banner GDPR (Cookies)</h4>
                                    <p>Aviso legal de consentimiento de cookies</p>
                                </div>
                            </div>
                            <div class="module-body">
                                <span class="module-status <?php echo $val_cookie === 'inactivo' ? 'off' : ''; ?>">
                                    <span class="status-dot"></span>
                                    <?php echo $val_cookie === 'activo' ? 'Activo' : 'Inactivo'; ?>
                                </span>
                                <label class="toggle-switch">
                                    <input type="checkbox" data-select="cookie_banner_estado" <?php echo $val_cookie === 'activo' ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <select name="cookie_banner_estado" class="module-native-select">
                                    <option value="inactivo" <?php echo $val_cookie === 'inactivo' ? 'selected' : ''; ?>>Desactivado</option>
                                    <option value="activo" <?php echo $val_cookie === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); margin-top: 1.25rem;">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #14b8a6;"><i class="ri-text"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Texto del Aviso Superior</h4>
                                    <p>Mensaje que se mostrará en la marquesina</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="text" name="alert_top_texto" value="<?php echo htmlspecialchars($configs['alert_top_texto'] ?? ''); ?>" placeholder="Texto del aviso..." class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                            </div>
                        </div>
                        
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #64748b;"><i class="ri-link"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">URL del Aviso</h4>
                                    <p>Enlace opcional al hacer clic</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="url" name="alert_top_url" value="<?php echo htmlspecialchars($configs['alert_top_url'] ?? ''); ?>" placeholder="https://..." class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cfg-panel">
                    <h3><i class="ri-palette-line"></i> Tipografía y CSS Personalizado</h3>
                    
                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); margin-bottom: 1.5rem;">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #8b5cf6;"><i class="ri-font-family"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Familia Tipográfica Global</h4>
                                    <p>Elige la letra de Google Fonts</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <select name="theme_font_family" class="modern-input" style="width: 100%; font-family:var(--font-sans); border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem; background-color: #f8fafc;">
                                    <?php $current_font = $configs['theme_font_family'] ?? 'Inter'; ?>
                                    <option value="Inter" <?php echo $current_font === 'Inter' ? 'selected' : ''; ?>>Inter (Moderna) - Recomendado</option>
                                    <option value="Roboto" <?php echo $current_font === 'Roboto' ? 'selected' : ''; ?>>Roboto (Periodística)</option>
                                    <option value="Playfair Display" <?php echo $current_font === 'Playfair Display' ? 'selected' : ''; ?>>Playfair Display (Clásica)</option>
                                    <option value="Merriweather" <?php echo $current_font === 'Merriweather' ? 'selected' : ''; ?>>Merriweather (Elegante)</option>
                                    <option value="Poppins" <?php echo $current_font === 'Poppins' ? 'selected' : ''; ?>>Poppins (Creativa)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #0ea5e9;"><i class="ri-code-s-css-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Estilos CSS Personalizados</h4>
                                    <p>Sobrescribe las reglas de la plantilla</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <textarea name="theme_custom_css" rows="4" placeholder="Ejemplo: .header { padding: 20px; }" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem; font-family: monospace; resize: vertical; background: #f1f5f9;"><?php echo htmlspecialchars($configs['theme_custom_css'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ========================= TAB SOCIAL ========================= -->
            <div class="tab-pane" id="tab-social">
                
                <div class="cfg-panel">
                    <h3><i class="ri-contacts-book-line"></i> Información de Contacto</h3>
                    
                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); margin-bottom: 1.5rem;">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #3b82f6;"><i class="ri-mail-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Email Público</h4>
                                    <p>correo@tudominio.com</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="email" name="contact_email" value="<?php echo htmlspecialchars($configs['contact_email'] ?? ''); ?>" placeholder="correo@tudominio.com" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                            </div>
                        </div>
                        
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #10b981;"><i class="ri-phone-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Teléfono Corporativo</h4>
                                    <p>+51 987 654 321</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($configs['contact_phone'] ?? ''); ?>" placeholder="+51 987 654 321" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="module-grid" style="grid-template-columns: 1fr; margin-bottom: 1.5rem;">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #64748b;"><i class="ri-scales-3-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Texto Legal / Derechos (Footer)</h4>
                                    <p>Derechos de autor para el pie de página</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="text" name="footer_text" value="<?php echo htmlspecialchars($configs['footer_text'] ?? ''); ?>" placeholder="© 2026 Tu Medio - Todos los derechos reservados." class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                            </div>
                        </div>
                    </div>

                    <div class="module-grid" style="grid-template-columns: 1fr;">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #dc2626;"><i class="ri-file-shield-2-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Documento de Políticas de Privacidad</h4>
                                    <p>Sube un PDF para cumplimiento legal</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white; display: flex; flex-direction: column; gap: 0.75rem;">
                                <div class="pdf-upload-zone" style="margin-bottom: 0;">
                                    <?php if(!empty($configs['privacy_policy_url'])): ?>
                                        <i class="ri-file-pdf-2-fill" style="color: #dc2626;"></i>
                                        <a href="<?php echo htmlspecialchars('<?= APP_BASE ?>/' . $configs['privacy_policy_url']); ?>" target="_blank" style="font-size:0.85rem; font-weight:700; color:var(--primary-color); display:inline-flex; align-items:center; gap:0.3rem; text-decoration:none;">
                                            <i class="ri-external-link-line"></i> Ver Documento Actual
                                        </a>
                                    <?php else: ?>
                                        <i class="ri-file-close-line" style="color: #cbd5e1;"></i>
                                        <span style="font-size:0.82rem; color: #94a3b8; display:block;">No hay políticas subidas todavía</span>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="privacy_policy_upload" accept=".pdf" style="width: 100%; font-size: 0.85rem;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cfg-panel">
                    <h3><i class="ri-share-line"></i> Enlaces a Redes Sociales</h3>
                    <p class="section-desc"><i class="ri-information-line"></i> Estos enlaces alimentan los íconos sociales del Header, Footer y los botones de compartir.</p>
                    
                    <div class="social-grid">
                        <!-- Facebook -->
                        <div class="social-card" style="--social-accent: #1877f2;">
                            <div class="social-icon" style="background: #1877f2;"><i class="ri-facebook-circle-fill"></i></div>
                            <div class="social-input-wrap">
                                <label>Facebook</label>
                                <input type="url" name="social_facebook" value="<?php echo htmlspecialchars($configs['social_facebook'] ?? ''); ?>" placeholder="https://facebook.com/...">
                            </div>
                        </div>
                        <!-- X (Twitter) -->
                        <div class="social-card" style="--social-accent: #0f1419;">
                            <div class="social-icon" style="background: #0f1419;"><i class="ri-twitter-x-fill"></i></div>
                            <div class="social-input-wrap">
                                <label>X (Twitter)</label>
                                <input type="url" name="social_twitter" value="<?php echo htmlspecialchars($configs['social_twitter'] ?? ''); ?>" placeholder="https://x.com/...">
                            </div>
                        </div>
                        <!-- Instagram -->
                        <div class="social-card" style="--social-accent: #e1306c;">
                            <div class="social-icon" style="background: linear-gradient(135deg, #f58529, #dd2a7b, #8134af);"><i class="ri-instagram-line"></i></div>
                            <div class="social-input-wrap">
                                <label>Instagram</label>
                                <input type="url" name="social_instagram" value="<?php echo htmlspecialchars($configs['social_instagram'] ?? ''); ?>" placeholder="https://instagram.com/...">
                            </div>
                        </div>
                        <!-- YouTube -->
                        <div class="social-card" style="--social-accent: #ff0000;">
                            <div class="social-icon" style="background: #ff0000;"><i class="ri-youtube-fill"></i></div>
                            <div class="social-input-wrap">
                                <label>YouTube</label>
                                <input type="url" name="social_youtube" value="<?php echo htmlspecialchars($configs['social_youtube'] ?? ''); ?>" placeholder="https://youtube.com/@...">
                            </div>
                        </div>
                        <!-- TikTok -->
                        <div class="social-card" style="--social-accent: #010101;">
                            <div class="social-icon" style="background: linear-gradient(135deg, #00f2ea, #ff0050);"><i class="ri-tiktok-fill"></i></div>
                            <div class="social-input-wrap">
                                <label>TikTok</label>
                                <input type="url" name="social_tiktok" value="<?php echo htmlspecialchars($configs['social_tiktok'] ?? ''); ?>" placeholder="https://tiktok.com/@...">
                            </div>
                        </div>
                        <!-- Twitch -->
                        <div class="social-card" style="--social-accent: #9146ff;">
                            <div class="social-icon" style="background: #9146ff;"><i class="ri-twitch-fill"></i></div>
                            <div class="social-input-wrap">
                                <label>Twitch</label>
                                <input type="url" name="social_twitch" value="<?php echo htmlspecialchars($configs['social_twitch'] ?? ''); ?>" placeholder="https://twitch.tv/...">
                            </div>
                        </div>
                        <!-- Kick -->
                        <div class="social-card" style="--social-accent: #53fc18;">
                            <div class="social-icon" style="background: linear-gradient(135deg, #53fc18, #1a8c00);"><i class="ri-live-fill"></i></div>
                            <div class="social-input-wrap">
                                <label>Kick</label>
                                <input type="url" name="social_kick" value="<?php echo htmlspecialchars($configs['social_kick'] ?? ''); ?>" placeholder="https://kick.com/...">
                            </div>
                        </div>
                        <!-- Threads -->
                        <div class="social-card" style="--social-accent: #000;">
                            <div class="social-icon" style="background: #000;"><i class="ri-threads-fill"></i></div>
                            <div class="social-input-wrap">
                                <label>Threads</label>
                                <input type="url" name="social_threads" value="<?php echo htmlspecialchars($configs['social_threads'] ?? ''); ?>" placeholder="https://threads.net/@...">
                            </div>
                        </div>
                        <!-- Telegram -->
                        <div class="social-card" style="--social-accent: #2ca5e0;">
                            <div class="social-icon" style="background: linear-gradient(135deg, #2ca5e0, #0088cc);"><i class="ri-telegram-fill"></i></div>
                            <div class="social-input-wrap">
                                <label>Telegram</label>
                                <input type="url" name="social_telegram" value="<?php echo htmlspecialchars($configs['social_telegram'] ?? ''); ?>" placeholder="https://t.me/...">
                            </div>
                        </div>
                        <!-- Discord -->
                        <div class="social-card" style="--social-accent: #5865F2;">
                            <div class="social-icon" style="background: #5865F2;"><i class="ri-discord-fill"></i></div>
                            <div class="social-input-wrap">
                                <label>Discord</label>
                                <input type="url" name="social_discord" value="<?php echo htmlspecialchars($configs['social_discord'] ?? ''); ?>" placeholder="https://discord.gg/...">
                            </div>
                        </div>
                        <!-- Pinterest -->
                        <div class="social-card" style="--social-accent: #E60023;">
                            <div class="social-icon" style="background: #E60023;"><i class="ri-pinterest-fill"></i></div>
                            <div class="social-input-wrap">
                                <label>Pinterest</label>
                                <input type="url" name="social_pinterest" value="<?php echo htmlspecialchars($configs['social_pinterest'] ?? ''); ?>" placeholder="https://pinterest.com/...">
                            </div>
                        </div>
                        <!-- LinkedIn -->
                        <div class="social-card" style="--social-accent: #0a66c2;">
                            <div class="social-icon" style="background: linear-gradient(135deg, #0a66c2, #004182);"><i class="ri-linkedin-fill"></i></div>
                            <div class="social-input-wrap">
                                <label>LinkedIn</label>
                                <input type="url" name="social_linkedin" value="<?php echo htmlspecialchars($configs['social_linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/in/...">
                            </div>
                        </div>
                        <!-- WhatsApp -->
                        <div class="social-card" style="--social-accent: #25d366;">
                            <div class="social-icon" style="background: linear-gradient(135deg, #25d366, #128c7e);"><i class="ri-whatsapp-fill"></i></div>
                            <div class="social-input-wrap">
                                <label>WhatsApp (+51...)</label>
                                <input type="text" name="social_whatsapp" value="<?php echo htmlspecialchars($configs['social_whatsapp'] ?? ''); ?>" placeholder="+51971234567">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ========================= TAB AUTO-PUBLICADOR ========================= -->
            <div class="tab-pane" id="tab-autopub">
                <div class="cfg-panel">
                    <h3><i class="ri-rocket-2-fill"></i> Auto-Publicador en Redes Sociales (Nativo)</h3>
                    <p class="section-desc"><i class="ri-information-line"></i> Publica tus noticias automáticamente en redes sociales cuando cambien a estado "Publicado" o cuando el sistema las libere tras ser programadas.</p>
                    
                    <div style="display:flex; flex-direction:column; gap:1.5rem;">
                        <!-- Panel Webhook -->
                        <div class="api-panel" style="--api-accent: #d97706;">
                            <h4><i class="ri-instance-line"></i> Conexión Vía Webhook (Make / Zapier)</h4>
                            <p>Envía los datos de la noticia (JSON) a un servicio de automatización externo.</p>
                            
                            <?php $val_webhook = ($configs['auto_pub_webhook_estado'] ?? 'inactivo'); ?>
                            <div class="module-grid" style="grid-template-columns: 1fr; margin-bottom: 1rem;">
                                <div class="module-card <?php echo $val_webhook === 'inactivo' ? 'is-inactive' : ''; ?>">
                                    <div class="module-body" style="padding: 0.85rem 1.25rem;">
                                        <div style="display:flex; align-items:center; gap:0.65rem;">
                                            <div class="module-icon-wrap" style="background: linear-gradient(135deg, #f59e0b, #d97706); width:34px; height:34px; font-size:1rem;">
                                                <i class="ri-webhook-fill"></i>
                                            </div>
                                            <span style="font-weight:700; font-size:0.85rem; color:#1e293b;">Estado del Webhook</span>
                                        </div>
                                        <div style="display:flex; align-items:center; gap:0.75rem;">
                                            <span class="module-status <?php echo $val_webhook === 'inactivo' ? 'off' : ''; ?>">
                                                <span class="status-dot"></span>
                                                <?php echo $val_webhook === 'activo' ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                            <label class="toggle-switch">
                                                <input type="checkbox" data-select="auto_pub_webhook_estado" <?php echo $val_webhook === 'activo' ? 'checked' : ''; ?>>
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <select name="auto_pub_webhook_estado" class="module-native-select">
                                            <option value="inactivo" <?php echo $val_webhook === 'inactivo' ? 'selected' : ''; ?>>DESACTIVADO</option>
                                            <option value="activo" <?php echo $val_webhook === 'activo' ? 'selected' : ''; ?>>ACTIVO</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="module-grid" style="grid-template-columns: 1fr; margin-top: 0.75rem;">
                                <div class="module-card">
                                    <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                        <div class="module-icon-wrap" style="background: #d97706;"><i class="ri-link"></i></div>
                                        <div class="module-info">
                                            <h4 style="font-size: 1rem;">URL del Webhook</h4>
                                            <p>Destination URL de Make / Zapier</p>
                                        </div>
                                    </div>
                                    <div class="module-body" style="background: white;">
                                        <input type="url" name="auto_pub_webhook_url" value="<?php echo htmlspecialchars($configs['auto_pub_webhook_url'] ?? ''); ?>" placeholder="https://hook.us1.make.com/..." class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Facebook -->
                        <div class="api-panel" style="--api-accent: #1877f2;">
                            <h4><i class="ri-facebook-circle-fill"></i> API Nativa: Facebook Page</h4>
                            <p>Publica directamente en el muro de tu Fanpage de Facebook mediante Graph API.</p>
                            
                            <?php $val_fb = ($configs['auto_pub_fb_estado'] ?? 'inactivo'); ?>
                            <div class="module-grid" style="grid-template-columns: 1fr; margin-bottom: 1rem;">
                                <div class="module-card <?php echo $val_fb === 'inactivo' ? 'is-inactive' : ''; ?>">
                                    <div class="module-body" style="padding: 0.85rem 1.25rem;">
                                        <div style="display:flex; align-items:center; gap:0.65rem;">
                                            <div class="module-icon-wrap" style="background: linear-gradient(135deg, #1877f2, #1565c0); width:34px; height:34px; font-size:1rem;">
                                                <i class="ri-facebook-circle-fill"></i>
                                            </div>
                                            <span style="font-weight:700; font-size:0.85rem; color:#1e293b;">Estado de Facebook</span>
                                        </div>
                                        <div style="display:flex; align-items:center; gap:0.75rem;">
                                            <span class="module-status <?php echo $val_fb === 'inactivo' ? 'off' : ''; ?>">
                                                <span class="status-dot"></span>
                                                <?php echo $val_fb === 'activo' ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                            <label class="toggle-switch">
                                                <input type="checkbox" data-select="auto_pub_fb_estado" <?php echo $val_fb === 'activo' ? 'checked' : ''; ?>>
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <select name="auto_pub_fb_estado" class="module-native-select">
                                            <option value="inactivo" <?php echo $val_fb === 'inactivo' ? 'selected' : ''; ?>>DESACTIVADO</option>
                                            <option value="activo" <?php echo $val_fb === 'activo' ? 'selected' : ''; ?>>ACTIVO</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); margin-top: 0.75rem;">
                                <div class="module-card">
                                    <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                        <div class="module-icon-wrap" style="background: #1877f2;"><i class="ri-pages-line"></i></div>
                                        <div class="module-info">
                                            <h4 style="font-size: 1rem;">Page ID</h4>
                                            <p>ID numérico de tu Fanpage</p>
                                        </div>
                                    </div>
                                    <div class="module-body" style="background: white;">
                                        <input type="text" name="auto_pub_fb_page_id" value="<?php echo htmlspecialchars($configs['auto_pub_fb_page_id'] ?? ''); ?>" placeholder="Ej: 104534534534534" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                                    </div>
                                </div>
                                <div class="module-card">
                                    <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                        <div class="module-icon-wrap" style="background: #0d47a1;"><i class="ri-key-2-line"></i></div>
                                        <div class="module-info">
                                            <h4 style="font-size: 1rem;">Page Access Token</h4>
                                            <p>Token permanente de Graph API</p>
                                        </div>
                                    </div>
                                    <div class="module-body" style="background: white;">
                                        <input type="password" name="auto_pub_fb_token" value="<?php echo htmlspecialchars($configs['auto_pub_fb_token'] ?? ''); ?>" placeholder="EAAIxxxxx..." class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Twitter -->
                        <div class="api-panel" style="--api-accent: #0f1419;">
                            <h4><i class="ri-twitter-x-fill"></i> API Nativa: X (Twitter) v2</h4>
                            <p>Publica directamente un Tweet en tu cuenta.</p>
                            
                            <?php $val_tw = ($configs['auto_pub_tw_estado'] ?? 'inactivo'); ?>
                            <div class="module-grid" style="grid-template-columns: 1fr; margin-bottom: 1rem;">
                                <div class="module-card <?php echo $val_tw === 'inactivo' ? 'is-inactive' : ''; ?>">
                                    <div class="module-body" style="padding: 0.85rem 1.25rem;">
                                        <div style="display:flex; align-items:center; gap:0.65rem;">
                                            <div class="module-icon-wrap" style="background: linear-gradient(135deg, #1da1f2, #0f1419); width:34px; height:34px; font-size:1rem;">
                                                <i class="ri-twitter-x-fill"></i>
                                            </div>
                                            <span style="font-weight:700; font-size:0.85rem; color:#1e293b;">Estado de X (Twitter)</span>
                                        </div>
                                        <div style="display:flex; align-items:center; gap:0.75rem;">
                                            <span class="module-status <?php echo $val_tw === 'inactivo' ? 'off' : ''; ?>">
                                                <span class="status-dot"></span>
                                                <?php echo $val_tw === 'activo' ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                            <label class="toggle-switch">
                                                <input type="checkbox" data-select="auto_pub_tw_estado" <?php echo $val_tw === 'activo' ? 'checked' : ''; ?>>
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <select name="auto_pub_tw_estado" class="module-native-select">
                                            <option value="inactivo" <?php echo $val_tw === 'inactivo' ? 'selected' : ''; ?>>DESACTIVADO</option>
                                            <option value="activo" <?php echo $val_tw === 'activo' ? 'selected' : ''; ?>>ACTIVO</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); margin-top: 0.75rem;">
                                <div class="module-card">
                                    <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                        <div class="module-icon-wrap" style="background: #1da1f2;"><i class="ri-key-line"></i></div>
                                        <div class="module-info">
                                            <h4 style="font-size: 1rem;">API Key</h4>
                                            <p>Consumer Key</p>
                                        </div>
                                    </div>
                                    <div class="module-body" style="background: white;">
                                        <input type="text" name="auto_pub_tw_api_key" value="<?php echo htmlspecialchars($configs['auto_pub_tw_api_key'] ?? ''); ?>" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                                    </div>
                                </div>
                                <div class="module-card">
                                    <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                        <div class="module-icon-wrap" style="background: #0f1419;"><i class="ri-lock-line"></i></div>
                                        <div class="module-info">
                                            <h4 style="font-size: 1rem;">API Key Secret</h4>
                                            <p>Consumer Secret</p>
                                        </div>
                                    </div>
                                    <div class="module-body" style="background: white;">
                                        <input type="password" name="auto_pub_tw_api_secret" value="<?php echo htmlspecialchars($configs['auto_pub_tw_api_secret'] ?? ''); ?>" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                                    </div>
                                </div>
                                <div class="module-card">
                                    <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                        <div class="module-icon-wrap" style="background: #14b8a6;"><i class="ri-shield-keyhole-line"></i></div>
                                        <div class="module-info">
                                            <h4 style="font-size: 1rem;">Access Token</h4>
                                            <p>Token de acceso OAuth</p>
                                        </div>
                                    </div>
                                    <div class="module-body" style="background: white;">
                                        <input type="text" name="auto_pub_tw_access_token" value="<?php echo htmlspecialchars($configs['auto_pub_tw_access_token'] ?? ''); ?>" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                                    </div>
                                </div>
                                <div class="module-card">
                                    <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                        <div class="module-icon-wrap" style="background: #64748b;"><i class="ri-lock-password-line"></i></div>
                                        <div class="module-info">
                                            <h4 style="font-size: 1rem;">Access Token Secret</h4>
                                            <p>Secret del Token OAuth</p>
                                        </div>
                                    </div>
                                    <div class="module-body" style="background: white;">
                                        <input type="password" name="auto_pub_tw_access_secret" value="<?php echo htmlspecialchars($configs['auto_pub_tw_access_secret'] ?? ''); ?>" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================= TAB AVANZADO Y APIS ========================= -->
            <div class="tab-pane" id="tab-avanzado">
                
                <div class="cfg-panel">
                    <h3><i class="ri-shield-user-fill"></i> Login Social (OAuth para Comentarios)</h3>
                    <p class="section-desc"><i class="ri-information-line"></i> Requiere credenciales de Google Cloud y Meta for Developers.</p>
                    
                    <?php $val_oauth = ($configs['social_login_estado'] ?? 'inactivo'); ?>
                    <div class="module-grid" style="grid-template-columns: 1fr; margin-bottom: 1.5rem;">
                        <div class="module-card <?php echo $val_oauth === 'inactivo' ? 'is-inactive' : ''; ?>">
                            <div class="module-header">
                                <div class="module-icon-wrap" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9);">
                                    <i class="ri-user-shared-fill"></i>
                                </div>
                                <div class="module-info">
                                    <h4>Sistema de Comentarios OAuth</h4>
                                    <p>Fuerza inicio de sesión con Google o Facebook para comentar</p>
                                </div>
                            </div>
                            <div class="module-body">
                                <span class="module-status <?php echo $val_oauth === 'inactivo' ? 'off' : ''; ?>">
                                    <span class="status-dot"></span>
                                    <?php echo $val_oauth === 'activo' ? 'Activo' : 'Inactivo'; ?>
                                </span>
                                <label class="toggle-switch">
                                    <input type="checkbox" data-select="social_login_estado" <?php echo $val_oauth === 'activo' ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <select name="social_login_estado" class="module-native-select">
                                    <option value="inactivo" <?php echo $val_oauth === 'inactivo' ? 'selected' : ''; ?>>DESACTIVADO</option>
                                    <option value="activo" <?php echo $val_oauth === 'activo' ? 'selected' : ''; ?>>ACTIVO</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                        <!-- Panel Google -->
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #4285f4;"><i class="ri-google-fill"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Google Client ID</h4>
                                    <p>Credencial OAuth de Google Cloud</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="text" name="google_client_id" value="<?php echo htmlspecialchars($configs['google_client_id'] ?? ''); ?>" placeholder="123456789-xxxx.apps.googleusercontent.com" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem; font-size: 0.85rem;">
                            </div>
                        </div>
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #34a853;"><i class="ri-lock-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Google Client Secret</h4>
                                    <p>Clave secreta de Google Cloud</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="password" name="google_client_secret" value="<?php echo htmlspecialchars($configs['google_client_secret'] ?? ''); ?>" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                            </div>
                        </div>

                        <!-- Panel Facebook -->
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #1877f2;"><i class="ri-facebook-circle-fill"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Facebook App ID</h4>
                                    <p>Credencial OAuth de Meta</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="text" name="facebook_app_id" value="<?php echo htmlspecialchars($configs['facebook_app_id'] ?? ''); ?>" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                            </div>
                        </div>
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #0d47a1;"><i class="ri-key-2-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">Facebook App Secret</h4>
                                    <p>Clave secreta de Meta</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <input type="password" name="facebook_app_secret" value="<?php echo htmlspecialchars($configs['facebook_app_secret'] ?? ''); ?>" class="modern-input" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cfg-panel">
                    <h3><i class="ri-code-s-slash-line"></i> Inyector de Scripts (Zero-Code)</h3>
                    <p class="section-desc"><i class="ri-information-line"></i> Permite inyectar Píxeles, Chats o AdSense sin modificar archivos del core.</p>
                    
                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #6366f1;"><i class="ri-code-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">&lt;head&gt; Scripts</h4>
                                    <p>Analytics, Meta Pixel, etc.</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <textarea name="script_header" rows="4" class="modern-input" style="width: 100%; font-family: 'JetBrains Mono', monospace; background: #0f172a; color: #a5b4fc; border: 1px solid #334155; border-radius: 6px; padding: 0.5rem; resize: vertical; font-size: 0.85rem;"><?php echo htmlspecialchars($configs['script_header'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: #ec4899;"><i class="ri-terminal-box-line"></i></div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;">&lt;body&gt; Scripts</h4>
                                    <p>Chatbots, AdSense, Verificadores</p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white;">
                                <textarea name="script_footer" rows="4" class="modern-input" style="width: 100%; font-family: 'JetBrains Mono', monospace; background: #0f172a; color: #a5b4fc; border: 1px solid #334155; border-radius: 6px; padding: 0.5rem; resize: vertical; font-size: 0.85rem;"><?php echo htmlspecialchars($configs['script_footer'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cfg-panel">
                    <h3><i class="ri-lock-2-line"></i> Seguridad y Control Maestro</h3>
                    
                    <?php $val_mant = ($configs['modo_mantenimiento'] ?? 'inactivo'); ?>
                    <div class="module-grid" style="grid-template-columns: 1fr;">
                        <div class="module-card <?php echo $val_mant === 'activo' ? 'is-inactive' : ''; ?>" style="border-color: <?php echo $val_mant === 'activo' ? '#fecaca' : '#e2e8f0'; ?>;">
                            <div class="module-header" style="background: <?php echo $val_mant === 'activo' ? 'linear-gradient(135deg, #fef2f2, #fee2e2)' : 'linear-gradient(135deg, #f0fdf4, #dcfce7)'; ?>;">
                                <div class="module-icon-wrap" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                                    <i class="ri-error-warning-fill"></i>
                                </div>
                                <div class="module-info">
                                    <h4>Modo Mantenimiento (Offline)</h4>
                                    <p>Bloquea el acceso público al sitio — solo admins pueden ver la web</p>
                                </div>
                            </div>
                            <div class="module-body">
                                <span class="module-status <?php echo $val_mant === 'activo' ? 'off' : ''; ?>">
                                    <span class="status-dot"></span>
                                    <?php echo $val_mant === 'activo' ? 'Bloqueado' : 'Público'; ?>
                                </span>
                                <label class="toggle-switch">
                                    <input type="checkbox" data-select="modo_mantenimiento" <?php echo $val_mant === 'activo' ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <select name="modo_mantenimiento" class="module-native-select">
                                    <option value="inactivo" <?php echo $val_mant === 'inactivo' ? 'selected' : ''; ?>>PÚBLICO (Normal)</option>
                                    <option value="activo" <?php echo $val_mant === 'activo' ? 'selected' : ''; ?>>EN MANTENIMIENTO (Bloqueado)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ========================= TAB CUOTAS ========================= -->
            <div class="tab-pane" id="tab-cuotas">
                <div class="cfg-panel">
                    <h3><i class="ri-group-line"></i> Metas Globales por Rol</h3>
                    <p class="section-desc"><i class="ri-information-line"></i> Define la cantidad mínima de noticias que un empleado debe publicar al día según su cargo.</p>
                    
                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); margin-bottom: 2rem;">
                        <?php foreach($roles_list as $r): ?>
                        <div class="module-card">
                            <div class="module-header" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: var(--primary-color);">
                                    <i class="ri-user-star-line"></i>
                                </div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;"><?=htmlspecialchars($r['nombre'])?></h4>
                                    <p>Cuota aplicable por defecto</p>
                                </div>
                            </div>
                            <div class="module-body" style="display: flex; justify-content: space-between; align-items: center; background: white;">
                                <span style="font-size: 0.85rem; font-weight: 600; color: #475569;">Noticias por día:</span>
                                <input type="number" min="0" name="roles[<?=$r['id']?>][cuota]" value="<?=$r['cuota_diaria_default']?>" class="modern-input" style="width: 80px; text-align: center; margin: 0; padding: 0.5rem;">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="cfg-panel">
                    <h3><i class="ri-user-settings-line"></i> Excepciones por Usuario</h3>
                    <p class="section-desc"><i class="ri-information-line"></i> Sobreescribe la regla global para empleados específicos (ej: practicantes o part-time).</p>
                    
                    <div class="module-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
                        <?php foreach($usuarios_list as $u): ?>
                        <?php 
                            $rol_label = $u['rol_nombre'] ?? $u['rol'] ?? 'Sin Rol'; 
                            $hasCustom = $u['cuota_diaria_personal'] !== null;
                        ?>
                        <div class="module-card <?=$hasCustom ? '' : 'is-inactive'?>" id="card_user_<?=$u['id']?>" style="transition: all 0.3s ease;">
                            <div class="module-header" style="background: <?=$hasCustom ? 'linear-gradient(135deg, #eff6ff, #dbeafe)' : 'linear-gradient(135deg, #f8fafc, #f1f5f9)'?>; border-bottom: 1px solid #e2e8f0;">
                                <div class="module-icon-wrap" style="background: <?=$hasCustom ? '#3b82f6' : '#94a3b8'?>;">
                                    <i class="ri-user-settings-line"></i>
                                </div>
                                <div class="module-info">
                                    <h4 style="font-size: 1rem;"><?=htmlspecialchars($u['nombre_completo'])?></h4>
                                    <p><?=htmlspecialchars($u['email'])?></p>
                                </div>
                            </div>
                            <div class="module-body" style="background: white; display: flex; flex-direction: column; gap: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Rol Actual</span>
                                    <span style="background: #e2e8f0; color: #475569; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;"><?=htmlspecialchars($rol_label)?></span>
                                </div>
                                <div style="border-top: 1px solid #f1f5f9; padding-top: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; font-weight: 600; color: #1e293b; cursor: pointer;">
                                        <input type="radio" name="usuarios[<?=$u['id']?>][usar_rol]" value="1" <?=!$hasCustom ? 'checked' : ''?> 
                                            onchange="document.getElementById('cuota_input_<?=$u['id']?>').style.display = 'none'; document.getElementById('card_user_<?=$u['id']?>').classList.add('is-inactive'); document.getElementById('card_user_<?=$u['id']?>').querySelector('.module-header').style.background = 'linear-gradient(135deg, #f8fafc, #f1f5f9)'; document.getElementById('card_user_<?=$u['id']?>').querySelector('.module-icon-wrap').style.background = '#94a3b8';">
                                        Usar Meta del Rol
                                    </label>
                                    
                                    <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 0.75rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; font-weight: 600; color: #1e293b; cursor: pointer; margin: 0;">
                                            <input type="radio" name="usuarios[<?=$u['id']?>][usar_rol]" value="0" <?=$hasCustom ? 'checked' : ''?> 
                                                onchange="document.getElementById('cuota_input_<?=$u['id']?>').style.display = 'block'; document.getElementById('card_user_<?=$u['id']?>').classList.remove('is-inactive'); document.getElementById('card_user_<?=$u['id']?>').querySelector('.module-header').style.background = 'linear-gradient(135deg, #eff6ff, #dbeafe)'; document.getElementById('card_user_<?=$u['id']?>').querySelector('.module-icon-wrap').style.background = '#3b82f6';">
                                            Meta Personal:
                                        </label>
                                        <input type="number" id="cuota_input_<?=$u['id']?>" name="usuarios[<?=$u['id']?>][cuota_personal]" value="<?=$u['cuota_diaria_personal'] ?? 0?>" min="0" class="modern-input" style="width: 70px; text-align: center; margin: 0; padding: 0.4rem; display: <?=$hasCustom ? 'block' : 'none'?>;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div> <!-- /.settings-content -->
    </div> <!-- /.settings-container -->

    <div style="height: 80px;"></div>
    <div class="floating-save-bar">
        <button type="submit" class="btn-save" style="margin: 0; border-radius: 30px;"><i class="ri-save-3-line"></i> Guardar Cambios Globales</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.settings-tab');
    const panes = document.querySelectorAll('.tab-pane');

    // Recuperar la última pestaña activa del localStorage o usar la primera
    const savedTab = localStorage.getItem('activeConfigTab') || 'tab-general';
    
    function switchTab(targetId) {
        tabs.forEach(t => t.classList.remove('active'));
        panes.forEach(p => p.classList.remove('active'));

        const activeTabBtn = document.querySelector(`.settings-tab[data-target="${targetId}"]`);
        const activePane = document.getElementById(targetId);

        if (activeTabBtn && activePane) {
            activeTabBtn.classList.add('active');
            activePane.classList.add('active');
            localStorage.setItem('activeConfigTab', targetId);
        }
    }

    // Inicializar
    switchTab(savedTab);

    // Event Listeners para Tabs
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            switchTab(this.getAttribute('data-target'));
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    // ═══ MODULE TOGGLE SWITCHES ═══
    document.querySelectorAll('.toggle-switch input[type="checkbox"]').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const selectName = this.getAttribute('data-select');
            const nativeSelect = document.querySelector('select[name="' + selectName + '"]');
            const card = this.closest('.module-card');
            const statusBadge = card.querySelector('.module-status');
            
            if (this.checked) {
                nativeSelect.value = 'activo';
                card.classList.remove('is-inactive');
                statusBadge.classList.remove('off');
                statusBadge.innerHTML = '<span class="status-dot"></span> Visible';
            } else {
                nativeSelect.value = 'inactivo';
                card.classList.add('is-inactive');
                statusBadge.classList.add('off');
                statusBadge.innerHTML = '<span class="status-dot"></span> Oculto';
            }
        });
    });
});
</script>
