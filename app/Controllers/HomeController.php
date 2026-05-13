<?php
namespace App\Controllers;

use Config\Database;

class HomeController {
    public function index() {
        require_once __DIR__ . '/../../config/bootstrap.php';
        global $pdo;

date_default_timezone_set('America/Lima');
$fecha_espanol = formatear_fecha_espanol();

// Configuraciones Dinámicas (NUEVO SISTEMA PREMIUM + CACHE)
$global_configs = [];
$menu_cats_dynamic = [];
$ads_dynamic = [];

// FASE 1: CacheService se autocarga vía Composer PSR-4
$cacheService = new \App\Services\CacheService();
$g_data = $cacheService->get('global_cache');
if (!$g_data) {
    if (function_exists('build_global_cache')) {
        $g_data = build_global_cache($pdo);
    }
}

if ($g_data) {
    $global_configs = $g_data['configs'] ?? [];
    $menu_cats_dynamic = $g_data['categorias'] ?? [];
    foreach (($g_data['publicidad'] ?? []) as $ubicacion => $ads_pool) {
        if (!empty($ads_pool)) {
            $ads_dynamic[$ubicacion] = get_ad_from_cache($g_data['publicidad'], $ubicacion);
        }
    }
}

$cat_urgente = $global_configs['cat_urgente'] ?? 'Policiales';
$cat_carrusel = $global_configs['cat_carrusel'] ?? 'Política';
$cat_distrito = $global_configs['cat_distrito'] ?? 'Regional';

// MODO MANTENIMIENTO
if (isset($global_configs['modo_mantenimiento']) && $global_configs['modo_mantenimiento'] === 'activo') {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        header('Status: 503 Service Temporarily Unavailable');
        header('Retry-After: 3600');
        die('<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Sitio en Mantenimiento</title><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;800&display=swap" rel="stylesheet"><style>body{background:#111827;color:white;font-family:"Inter",sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;text-align:center;} h1{color:#3b82f6;font-size:3rem;margin-bottom:1rem;} p{color:#9ca3af;font-size:1.2rem;}</style></head><body><div><h1>Volvemos Pronto</h1><p>Estamos realizando mejoras en el sistema. Por favor, regresa más tarde.</p></div></body></html>');
    }
}

// SISTEMA DE BÚSQUEDA (Fase 13)
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$is_search = ($search_query !== '');



if ($is_search) {
    $newsRepo = new \App\Repositories\NewsRepository($pdo);
    $resultados_busqueda = $newsRepo->searchNoticias($search_query);

    $ultimas = [];
    $destacadas = [];
    $hero_slides = [];
    $recientes = [];
    $mas_leido = [];
    $todas_noticias = [];
    $anuncio = null;
    $policiales = [];
    $politica = [];
    $entretenimiento = [];
    $noticias_por_categoria = [];
} else {
    $use_cache = true;
    if (isset($_GET['nocache'])) $use_cache = false;
    
    if ($use_cache && ($cached_data = $cacheService->get('home'))) {
        // ANTIGRAVITY FIX HIGH-04: Explicit assignment instead of dangerous extract()
        $ultimas = $cached_data['ultimas'] ?? [];
        $destacadas = $cached_data['destacadas'] ?? [];
        $hero_slides = $cached_data['hero_slides'] ?? [];
        $recientes = $cached_data['recientes'] ?? [];
        $mas_leido = $cached_data['mas_leido'] ?? [];
        $todas_noticias = $cached_data['todas_noticias'] ?? [];
        $anuncio = $cached_data['anuncio'] ?? null;
        $policiales = $cached_data['policiales'] ?? [];
        $politica = $cached_data['politica'] ?? [];
        $entretenimiento = $cached_data['entretenimiento'] ?? [];
        $noticias_por_categoria = $cached_data['noticias_por_categoria'] ?? [];
        $stories = $cached_data['stories'] ?? [];
        $breaking_news = $cached_data['breaking_news'] ?? null;
        $regionales_todas = $cached_data['regionales_todas'] ?? [];
    } else {
        $newsRepo = new \App\Repositories\NewsRepository($pdo);
        $ultimas = $newsRepo->getUltimas();
        $destacadas = $newsRepo->getDestacadas();
        $hero_slides = array_chunk($destacadas, 3);
        $recientes = $newsRepo->getRecientes();
        $mas_leido = $newsRepo->getMasLeido();
        $todas_noticias = $newsRepo->getTodasNoticias();
        $anuncio = $newsRepo->getAnuncioCabecera();
        $regionales_todas = $newsRepo->getRegionales();

        $cat_urgente = $global_configs['cat_urgente'] ?? 'Policiales';
        $cat_carrusel = $global_configs['cat_carrusel'] ?? 'Política';
        $cat_distrito = $global_configs['cat_distrito'] ?? 'Regional';

        $policiales = $newsRepo->getByCategoria($cat_urgente, 4);
        $politica = $newsRepo->getByCategoria($cat_carrusel, 6);
        $entretenimiento = $newsRepo->getEntretenimiento();
        $stories = $newsRepo->getStories();
        $breaking_news = $newsRepo->getBreakingNews($cat_urgente);

        $noticias_por_categoria = [];
        foreach ($todas_noticias as $n) {
            if (!isset($noticias_por_categoria[$n['categoria']])) {
                $noticias_por_categoria[$n['categoria']] = [];
            }
            if (count($noticias_por_categoria[$n['categoria']]) < 4) {
                $noticias_por_categoria[$n['categoria']][] = $n;
            }
        }

        if ($use_cache) {
            $cacheService->set('home', compact('ultimas', 'destacadas', 'hero_slides', 'recientes', 'fecha_espanol', 'mas_leido', 'todas_noticias', 'anuncio', 'policiales', 'politica', 'entretenimiento', 'noticias_por_categoria', 'stories', 'breaking_news', 'regionales_todas'));
        }
    }
} // FIN DEL MODO NO-BÚSQUEDA


        // Restaurar contexto absoluto al directorio raíz para garantizar compatibilidad con Vistas HTML Legacy
        // (Soluciona 100% el problema de "file_exists" ciego de renderMedia() y fallos de "include 'includes/...'")
        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/home/index.php';
    }
}
