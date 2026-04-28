<?php
/**
 * build_assets.php — FASE 5 MODERNIZACIÓN
 * Script de build para minificar CSS/JS y generar manifest.
 * 
 * Ejecutar desde la raíz del proyecto:
 *   php build_assets.php
 * 
 * Qué hace:
 *   1. Lee css/style.css y js/premium-features.js
 *   2. Los minifica (elimina comentarios, whitespace excesivo)
 *   3. Genera archivos con hash en dist/ (ej: dist/css/style.a1b2c3d4.min.css)
 *   4. Crea dist/manifest.json para resolución automática
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\AssetManager;

echo "╔══════════════════════════════════════════════╗\n";
echo "║  HTVPERU Asset Builder — Fase 5 Build Tool   ║\n";
echo "╚══════════════════════════════════════════════╝\n\n";

// Lista de assets a minificar
$assets = [
    'css/style.css',
    'js/premium-features.js',
];

$results = AssetManager::build($assets);

// Mostrar resultados
echo str_pad('Archivo', 30) . str_pad('Original', 12) . str_pad('Minificado', 12) . str_pad('Ahorro', 10) . "Estado\n";
echo str_repeat('─', 80) . "\n";

foreach ($results as $r) {
    echo str_pad($r['file'], 30)
       . str_pad($r['original'] ?? '-', 12)
       . str_pad($r['minified'] ?? '-', 12)
       . str_pad($r['savings'] ?? '-', 10)
       . $r['status'] . "\n";
}

echo "\n✅ Build completado. Los assets minificados están en dist/\n";
echo "   Manifest generado en: dist/manifest.json\n\n";

// Mostrar instrucción
echo "Para usar los assets minificados en las vistas, reemplaza:\n";
echo "  <link rel=\"stylesheet\" href=\"css/style.css?v=4\">\n";
echo "Por:\n";
echo "  <?= \\App\\Services\\AssetManager::css('css/style.css') ?>\n\n";
