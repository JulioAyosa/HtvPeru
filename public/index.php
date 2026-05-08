<?php
// /public/index.php
// FRONT CONTROLLER & PROGRESSIVE FALLBACK

// === GLOBAL SECURITY HEADERS (OWASP) ===
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
// =======================================

// Configuración robusta de Sesiones (Antes de iniciar)
require_once __DIR__ . '/../session_config.php';
// Inicio de sesión inteligente para habilitar Edge Caching perimetral
$req_uri = $_SERVER['REQUEST_URI'] ?? '';
$is_admin_or_auth = (strpos($req_uri, '/admin') !== false || strpos($req_uri, '/login') !== false || strpos($req_uri, '/auth') !== false);
$is_stateless_api = (strpos($req_uri, '/api/view_counter') !== false || strpos($req_uri, '/ajax_view_counter') !== false);

$needs_session = isset($_COOKIE[session_name()]) || $is_admin_or_auth || ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_stateless_api);

if ($needs_session) {
    @session_start();
}

// FASE 1 MODERNIZACIÓN: Autoloader PSR-4 estándar de Composer
// Reemplaza el autoloader manual anterior. Composer resuelve todos los namespaces
// registrados en composer.json (App\, Core\, Config\) automáticamente.
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap global: conexión BD, helpers, sanitizers
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../app/Helpers/csrf_helper.php';
require_once __DIR__ . '/../app/Helpers/auth_helper.php';
require_once __DIR__ . '/../app/Helpers/rate_limiter.php';
require_once __DIR__ . '/../app/Helpers/view_helper.php';

// Importar Router
require_once __DIR__ . '/../core/Router.php';

$router = new \Core\Router();

// ============================================
// REGISTRO DE RUTAS MVC PURAS
// ============================================
$router->add('GET', '/ajax_heartbeat.php', 'ApiPublicController@heartbeat');
$router->add('GET', '/ajax_search.php', 'ApiPublicController@search');
$router->add('POST', '/ajax_view_counter.php', 'ApiPublicController@viewCounter');

// Rutas API Públicas Puras
$router->add('GET', '/api/heartbeat', 'ApiPublicController@heartbeat');
$router->add('GET', '/api/search', 'ApiPublicController@search');
$router->add('POST', '/api/view_counter', 'ApiPublicController@viewCounter');
$router->add('POST', '/api/encuestas', 'ApiPublicController@pollVote', ['CsrfMiddleware']);
$router->add('POST', '/api/suscriptores', 'ApiPublicController@subscribe', ['CsrfMiddleware']);

// Mantenemos las URLs legacy por compatibilidad si es que algún script de frontend no se actualizó
$router->add('POST', '/encuestas_api.php', 'ApiPublicController@pollVote', ['CsrfMiddleware']);
$router->add('POST', '/suscriptores_api.php', 'ApiPublicController@subscribe', ['CsrfMiddleware']);

// Rutas API Admin
$router->add('POST', '/api/admin/autosave', 'ApiAdminController@autosave');
$router->add('GET', '/api/admin/backup_size', 'ApiAdminController@backupSize');
$router->add('GET', '/api/admin/boletin_destinatarios', 'ApiAdminController@boletinDestinatarios');

// Compatibilidad temporal backward calls
$router->add('POST', '/ajax_autosave.php', 'ApiAdminController@autosave');
$router->add('GET', '/ajax_backup_size.php', 'ApiAdminController@backupSize');
$router->add('GET', '/ajax_boletin_destinatarios.php', 'ApiAdminController@boletinDestinatarios');

// VISTAS ADMIN MVC
$router->add('GET', '/admin', 'AdminDashboardController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('GET', '/admin/dashboard/action', 'AdminDashboardController@action', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/dashboard/action', 'AdminDashboardController@action', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);
$router->add('POST', '/admin/dashboard/bulk', 'AdminDashboardController@bulkAction', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);
$router->add('POST', '/admin/dashboard/store', 'AdminDashboardController@store', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/categorias', 'AdminCategoryController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('GET', '/admin/categorias/action', 'AdminCategoryController@action', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/categorias/action', 'AdminCategoryController@action', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/paginas', 'AdminPageController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('GET', '/admin/paginas/get', 'AdminPageController@get', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/paginas/store', 'AdminPageController@store', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);
$router->add('GET', '/admin/paginas/action', 'AdminPageController@action', ['AuthMiddleware', 'AdminMiddleware']);

$router->add('GET', '/admin/contenidos', 'AdminContentController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/contenidos/store', 'AdminContentController@store', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);
$router->add('GET', '/admin/contenidos/action', 'AdminContentController@action', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/contenidos/action', 'AdminContentController@action', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/encuestas', 'AdminPollController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/encuestas/create', 'AdminPollController@store', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);
$router->add('POST', '/admin/encuestas/action', 'AdminPollController@action', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/usuarios', 'AdminUserController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('GET', '/admin/usuarios/action', 'AdminUserController@action', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/usuarios/action', 'AdminUserController@action', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/usuarios-publicos', 'AdminPublicUsersController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('GET', '/admin/usuarios-publicos/toggle', 'AdminPublicUsersController@toggleStatus', ['AuthMiddleware', 'AdminMiddleware']);

$router->add('GET', '/admin/roles', 'AdminRoleController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/roles/action', 'AdminRoleController@action', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/perfil', 'AdminProfileController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/perfil/action', 'AdminProfileController@action', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/multimedia', 'AdminMultimediaController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/multimedia/action', 'AdminMultimediaController@action', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/publicidad', 'AdminAdsController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/publicidad/action', 'AdminAdsController@action', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/papelera', 'AdminTrashController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/papelera/action', 'AdminTrashController@action', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/configuracion', 'AdminSettingsController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/configuracion/action', 'AdminSettingsController@action', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/respaldos', 'AdminBackupsController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('GET', '/admin/respaldos/action', 'AdminBackupsController@action', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/respaldos/action', 'AdminBackupsController@action', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/comentarios', 'AdminCommentController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('GET', '/admin/comentarios/action', 'AdminCommentController@action', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/comentarios/bulk', 'AdminCommentController@bulkAction', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/boletines', 'AdminNewsletterController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/boletines/action', 'AdminNewsletterController@action', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/reportes', 'AdminReportController@index', ['AuthMiddleware', 'AdminMiddleware']);

$router->add('GET', '/admin/optimizador', 'AdminOptimizerController@index', ['AuthMiddleware', 'AdminMiddleware']);
$router->add('POST', '/admin/optimizador/action', 'AdminOptimizerController@action', ['AuthMiddleware', 'AdminMiddleware', 'CsrfMiddleware']);

$router->add('GET', '/admin/actividad', 'AdminActivityController@index', ['AuthMiddleware', 'AdminMiddleware']);

// VISTAS FRONT-END
$router->add('GET', '/', 'HomeController@index');
$router->add('GET', '/index.php', 'HomeController@index');

$router->add('GET', '/login.php', 'AuthController@index');
$router->add('POST', '/login.php', 'AuthController@index');
$router->add('GET', '/login', 'AuthController@index');
$router->add('POST', '/login', 'AuthController@index');

$router->add('GET', '/article.php', 'PublicPageController@article');
$router->add('POST', '/article.php', 'PublicPageController@article', ['CsrfMiddleware']);
$router->add('GET', '/category.php', 'PublicPageController@category');
$router->add('GET', '/search.php', 'PublicPageController@search');
$router->add('GET', '/ultimas-noticias.php', 'PublicPageController@ultimasNoticias');
$router->add('GET', '/tag.php', 'PublicPageController@tag');
$router->add('GET', '/bookmarks.php', 'PublicPageController@bookmarks');
$router->add('GET', '/pagina.php', 'PublicPageController@page');

$router->add('GET', '/rss.php', 'RssController@index');
$router->add('GET', '/rss', 'RssController@index');

$router->add('GET', '/sitemap.php', 'SitemapController@index');
$router->add('GET', '/sitemap.xml', 'SitemapController@index');
$router->add('GET', '/sitemap-generator.php', 'SitemapController@generate');
$router->add('GET', '/sitemap-generator.php', 'SitemapController@generate');
$router->add('POST', '/sitemap-generator.php', 'SitemapController@generate');

// RUTAS OAUTH (PUBLIC)
$router->add('GET', '/auth/google', 'PublicAuthController@googleRedirect');
$router->add('GET', '/auth/google/callback', 'PublicAuthController@googleCallback');
$router->add('GET', '/auth/facebook', 'PublicAuthController@facebookRedirect');
$router->add('GET', '/auth/facebook/callback', 'PublicAuthController@facebookCallback');
$router->add('GET', '/auth/logout', 'PublicAuthController@logout');

// URLs LIMPIAS (SEO-friendly) - Rutas sin .php ni query strings
$router->add('GET', '/categoria/{slug}', 'PublicPageController@categoryBySlug');
$router->add('GET', '/etiqueta/{slug}', 'PublicPageController@tagBySlug');
$router->add('GET', '/buscar', 'PublicPageController@search');
$router->add('GET', '/ultimas-noticias', 'PublicPageController@ultimasNoticias');
$router->add('GET', '/guardados', 'PublicPageController@bookmarks');
$router->add('GET', '/pagina/{slug}', 'PublicPageController@pageBySlug');

// URLs LIMPIAS: Artículos por slug (CATCH-ALL, debe ir AL FINAL)
// Esto permite URLs como /piura_noticias_php/mi-titulo-de-noticia
$router->add('GET', '/{slug}', 'PublicPageController@articleBySlug');
$router->add('POST', '/{slug}', 'PublicPageController@articleBySlug', ['CsrfMiddleware']);

// ============================================
// DESPACHADOR Y FALLBACK DE COMPATIBILIDAD
// ============================================
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
// Limpiar query strings para el route path
$route_path = parse_url($request_uri, PHP_URL_PATH);

// PRE-PRODUCCION: Usar APP_BASE dinámico en vez de hardcodear /piura_noticias_php
$base_path = defined('APP_BASE') ? APP_BASE : '/piura_noticias_php'; 
if (!empty($base_path) && stripos($route_path, $base_path) === 0) {
    $route_path = substr($route_path, strlen($base_path));
}
if ($route_path === '' || $route_path === '/') {
    $route_path = '/index.php'; // Apuntamos a la home antigua por defecto
}

try {
    $dispatched = $router->dispatch($route_path);
    
    if ($dispatched === false) {
        header("HTTP/1.0 404 Not Found");
        if (file_exists(__DIR__ . '/../404.php')) {
            chdir(__DIR__ . '/..');
            require __DIR__ . '/../404.php';
        } else {
            echo "404 Not Found";
        }
        exit;
    }
} catch (\Throwable $e) {
    // FASE 4 MODERNIZACIÓN: Logger profesional con Monolog
    // Los detalles técnicos se guardan en storage/logs/app-YYYY-MM-DD.log
    // El usuario solo ve una página limpia de error 500
    try {
        $logger = \App\Services\LoggerService::getInstance();
        $logger->critical('Error fatal en Front Controller', [
            'message'   => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => $e->getTraceAsString(),
            'url'       => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method'    => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_id'   => $_SESSION['user_id'] ?? null,
        ]);
    } catch (\Throwable $logError) {
        // Fallback: si Monolog falla, usar error_log nativo
        error_log('CRITICAL [Front Controller]: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    }
    
    header("HTTP/1.0 500 Internal Server Error");
    if (file_exists(__DIR__ . '/../500.php')) {
        chdir(__DIR__ . '/..');
        require __DIR__ . '/../500.php';
    } else {
        echo '<h1>500 - Error Interno del Servidor</h1><p>Estamos trabajando para resolverlo.</p>';
    }
}

