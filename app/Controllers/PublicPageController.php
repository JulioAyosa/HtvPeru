<?php
namespace App\Controllers;

use Config\Database;
use PDO;
use Exception;
use DateTime;

class PublicPageController {

    private function getGlobalData($pdo = null) {
        if ($pdo === null) {
            $pdo = \Config\Database::getInstance();
        }
        // FASE 1: CacheService se autocarga vía Composer PSR-4
        $cacheService = new \App\Services\CacheService();
        $g_data = $cacheService->get('global_cache');
        if (!$g_data) {
            if (function_exists('build_global_cache')) {
                $g_data = build_global_cache($pdo);
            } else {
                $g_data = ['configs' => [], 'publicidad' => []];
            }
        }
        return $g_data;
    }

    private function checkMaintenance($global_configs) {
        if (isset($global_configs['modo_mantenimiento']) && $global_configs['modo_mantenimiento'] === 'activo') {
            @session_start();
            if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
                header('HTTP/1.1 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header('Retry-After: 3600');
                die('<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Sitio en Mantenimiento</title><style>body{background:#111827;color:white;font-family:sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;text-align:center;} h1{color:#3b82f6;font-size:3rem;margin-bottom:1rem;} p{color:#9ca3af;font-size:1.2rem;}</style></head><body><div><h1>Volvemos Pronto</h1><p>Estamos realizando mejoras en el sistema. Por favor, regresa más tarde.</p></div></body></html>');
            }
        }
    }

    public function article() {
        require_once __DIR__ . '/../../session_config.php';
        @session_start();
        require_once __DIR__ . '/../../conexion.php';
        require_once __DIR__ . '/../../html_sanitizer.php';
        
        $pdo = \Config\Database::getInstance();

        $g_data = $this->getGlobalData($pdo);
        $global_configs = $g_data['configs'] ?? [];
        $this->checkMaintenance($global_configs);

        $comment_msg = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {

            $noticia_id_post = (int)$_POST['noticia_id'];
            $comentario = mb_substr(trim($_POST['comentario'] ?? ''), 0, 1000);
            $honeypot = trim($_POST['website_hp'] ?? '');
            
            $social_login_active = ($global_configs['social_login_estado'] ?? 'inactivo') === 'activo';
            $usuario_publico_id = null;
            $nombre = '';
            $facebook_url = '';

            if ($social_login_active) {
                if (!isset($_SESSION['public_user_id'])) {
                    $comment_msg = '<div style="background:#ef4444; color:white; padding:10px; border-radius:4px; margin-bottom:1rem; font-weight:bold;">Debes iniciar sesión para comentar.</div>';
                } else {
                    $usuario_publico_id = $_SESSION['public_user_id'];
                    $nombre = $_SESSION['public_user_name'] ?? 'Usuario';
                    // facebook_url stays empty
                }
            } else {
                $nombre = mb_substr(trim($_POST['nombre'] ?? ''), 0, 100);
                $facebook_url = trim($_POST['facebook_url'] ?? '');
            }

            if (empty($comment_msg)) {
                if (!empty($honeypot)) {
                    $comment_msg = '<div style="background:#10b981; color:white; padding:10px; border-radius:4px; margin-bottom:1rem; font-weight:bold;">¡Gracias! Tu comentario ha sido enviado y está en revisión.</div>';
                } elseif (!$social_login_active && !empty($facebook_url) && !filter_var($facebook_url, FILTER_VALIDATE_URL)) {
                    $comment_msg = '<div style="background:#ef4444; color:white; padding:10px; border-radius:4px; margin-bottom:1rem; font-weight:bold;">La URL proporcionada no es válida.</div>';
                } elseif (!empty($noticia_id_post) && !empty($comentario) && ($social_login_active || (!empty($nombre) && !empty($facebook_url)))) {
                    $client_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                    $rate_check = $pdo->prepare("SELECT COUNT(*) FROM comentarios WHERE ip_address = ? AND fecha > NOW() - INTERVAL 1 MINUTE");
                    try {
                        $rate_check->execute([$client_ip]);
                        $recent_count = (int)$rate_check->fetchColumn();
                    } catch(Exception $e) { $recent_count = 0; }

                    if ($recent_count >= 3) {
                        $comment_msg = '<div style="background:#ef4444; color:white; padding:10px; border-radius:4px; margin-bottom:1rem; font-weight:bold;">Has enviado demasiados comentarios. Por favor espera un momento.</div>';
                    } else {
                        try {
                            $stmt_com = $pdo->prepare("INSERT INTO comentarios (noticia_id, usuario_publico_id, nombre, facebook_url, comentario, estado, ip_address) VALUES (?, ?, ?, ?, ?, 'Pendiente', ?)");
                            $stmt_com->execute([$noticia_id_post, $usuario_publico_id, $nombre, $facebook_url, $comentario, $client_ip]);
                            $comment_msg = '<div style="background:#10b981; color:white; padding:10px; border-radius:4px; margin-bottom:1rem; font-weight:bold;">¡Gracias! Tu comentario ha sido enviado y está en revisión antes de ser publicado.</div>';
                        } catch (\PDOException $e) {
                            $comment_msg = '<div style="background:#ef4444; color:white; padding:10px; border-radius:4px; margin-bottom:1rem; font-weight:bold;">Error al enviar el comentario: ' . $e->getMessage() . '</div>';
                        }
                    }
                } else {
                    $comment_msg = '<div style="background:#ef4444; color:white; padding:10px; border-radius:4px; margin-bottom:1rem; font-weight:bold;">Todos los campos son obligatorios.</div>';
                }
            }
        }

        $slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $newsRepo = new \App\Repositories\NewsRepository($pdo);
        if ($slug) {
            $view_key = 'viewed_' . md5($slug);
            if (!isset($_SESSION[$view_key])) { $_SESSION[$view_key] = true; }
            $articulo = $newsRepo->getArticleBySlug($slug);
        } else {
            $view_key = 'viewed_id_' . $id;
            if (!isset($_SESSION[$view_key])) { $_SESSION[$view_key] = true; }
            $articulo = $newsRepo->getArticleById($id);
        }

        if (!$articulo || ($articulo['estado_publicacion'] !== 'publicado')) {
            header("Location: /");
            exit;
        }

        $relacionadas = $newsRepo->getRelacionadas($articulo['categoria'], $articulo['id'], 3);
        $todas_noticias = $newsRepo->getTodasNoticias(10);

        $contenido_html = sanitize_html($articulo['contenido']);
        $contenido_html = str_replace('<img ', '<img loading="lazy" ', $contenido_html);

        $ad_inread = get_ad_from_cache($g_data['publicidad'] ?? [], 'in_read');
        if ($ad_inread) {
            if ($ad_inread['tipo'] === 'adsense') {
                $ad_html = '<div class="ad-in-read" style="margin:2.5rem 0; text-align:center;">' . $ad_inread['codigo_script'] . '</div>';
            } else {
                $ad_html = '<div class="ad-in-read" style="margin:2.5rem 0; text-align:center;"><a href="'.htmlspecialchars($ad_inread['enlace_url']??'#').'" target="_blank"><img src="'.htmlspecialchars($ad_inread['imagen_url']).'" style="max-width:100%; border-radius:4px; box-shadow:0 4px 6px rgba(0,0,0,0.1);"></a></div>';
            }
            $paragraphs = explode('</p>', $contenido_html);
            if (count($paragraphs) > 3) {
                array_splice($paragraphs, 2, 0, $ad_html);
                $contenido_html = implode('</p>', $paragraphs);
            } else {
                $contenido_html .= $ad_html;
            }
        }

        $word_count = str_word_count(strip_tags($contenido_html));
        $reading_time = ceil($word_count / 200);
        if ($reading_time < 1) $reading_time = 1;

        if (!empty($relacionadas)) {
            $paragraphs = explode('</p>', $contenido_html);
            if (count($paragraphs) > 2) {
                $in_read_post = $relacionadas[0];
                $in_read_html = '<div style="margin: 2rem 0; padding: 1.5rem; background: var(--bg-main); border-left: 4px solid var(--primary-color); border-radius: 4px;">' .
                                '<span style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">Lea También</span>' .
                                '<a href="article.php?slug=' . urlencode($in_read_post['slug']) . '" style="font-size: 1.25rem; font-weight: 800; color: var(--primary-color); text-decoration: none; line-height: 1.4; display: block;">' .
                                htmlspecialchars($in_read_post['titulo']) . '</a>' .
                                '</div>';
                $paragraphs[1] .= '</p>' . $in_read_html;
                $contenido_html = implode('</p>', $paragraphs);
            }
        }

        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/public/article.php';
    }

    public function category() {
        require_once __DIR__ . '/../../session_config.php';
        @session_start();
        require_once __DIR__ . '/../../conexion.php';
        $pdo = \Config\Database::getInstance();
        $g_data = $this->getGlobalData($pdo);
        $global_configs = $g_data['configs'] ?? [];

        $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;

        if (empty($slug) && isset($_GET['c'])) {
            $category_name = trim($_GET['c']);
        } else {
            $category_name = ucwords(str_replace('-', ' ', $slug));
        }

        if(empty($category_name)) { header("Location: /"); exit; }

        $catRepo = new \App\Repositories\CategoryRepository($pdo);
        $cat_info = $catRepo->getCategoryByNameOrSlug($category_name, $slug);

        if($cat_info && !empty($cat_info['nombre'])) {
            $category_name = $cat_info['nombre'];
        }
        $category_desc = ($cat_info && !empty($cat_info['descripcion'])) ? $cat_info['descripcion'] : "Encuentra todas las noticias de " . $category_name;
        $category_bg = ($cat_info && !empty($cat_info['imagen_fondo'])) ? $cat_info['imagen_fondo'] : '';

        $newsRepo = new \App\Repositories\NewsRepository($pdo);
        $total_rows = $newsRepo->countCategory($category_name);
        $total_pages = ceil($total_rows / $limit);
        $noticias = $newsRepo->getCategoryPaginated($category_name, $limit, $offset);

        $todas_noticias = $newsRepo->getTodasNoticias(50);

        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/public/category.php';
    }

    public function search() {
        require_once __DIR__ . '/../../session_config.php';
        @session_start();
        require_once __DIR__ . '/../../conexion.php';
        $pdo = \Config\Database::getInstance();
        $g_data = $this->getGlobalData($pdo);
        $global_configs = $g_data['configs'] ?? [];

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;

        if(empty($q)) { header("Location: /"); exit; }

        if (isset($_SESSION['last_search_time']) && (time() - $_SESSION['last_search_time']) < 2) {
            die("<div style='padding:2rem; font-family:sans-serif; text-align:center;'>Has excedido el límite de búsquedas. Por favor espera unos segundos.</div>");
        }
        $_SESSION['last_search_time'] = time();

        $newsRepo = new \App\Repositories\NewsRepository($pdo);
        $total_rows = $newsRepo->countSearchFulltext($boolean_q);
        $total_pages = ceil($total_rows / $limit);
        $noticias = $newsRepo->searchFulltextPaginated($boolean_q, $limit, $offset);

        $todas_noticias = $newsRepo->getTodasNoticias(50);

        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/public/search.php';
    }

    public function ultimasNoticias() {
        require_once __DIR__ . '/../../session_config.php';
        @session_start();
        require_once __DIR__ . '/../../conexion.php';
        $pdo = \Config\Database::getInstance();
        $g_data = $this->getGlobalData($pdo);
        $global_configs = $g_data['configs'] ?? [];

        $page = 1;
        $limit = 15;
        $offset = 0;

        $newsRepo = new \App\Repositories\NewsRepository($pdo);
        $noticias = $newsRepo->getUltimasPaginated($limit, $offset);
        $todas_noticias = $newsRepo->getTodasNoticias(50);

        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/public/ultimas_noticias.php';
    }

    public function tag() {
        require_once __DIR__ . '/../../session_config.php';
        @session_start();
        require_once __DIR__ . '/../../conexion.php';
        $pdo = \Config\Database::getInstance();
        $g_data = $this->getGlobalData($pdo);
        $global_configs = $g_data['configs'] ?? [];

        $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;

        if(empty($slug)) {
            header("Location: /piura_noticias_php/index.php");
            exit;
        }

        $tag_name = str_replace('-', ' ', $slug);

        $newsRepo = new \App\Repositories\NewsRepository($pdo);
        $total_rows = $newsRepo->countByTag($tag_name);
        $total_pages = ceil($total_rows / $limit);
        $noticias = $newsRepo->getByTagPaginated($tag_name, $limit, $offset);

        $todas_noticias = $newsRepo->getTodasNoticias(50);

        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/public/tag.php';
    }

    public function bookmarks() {
        require_once __DIR__ . '/../../session_config.php';
        @session_start();
        require_once __DIR__ . '/../../conexion.php';
        $pdo = \Config\Database::getInstance();
        $g_data = $this->getGlobalData($pdo);
        $global_configs = $g_data['configs'] ?? [];

        $newsRepo = new \App\Repositories\NewsRepository($pdo);
        $todas_noticias = $newsRepo->getTodasNoticias(50);

        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/public/bookmarks.php';
    }

    public function page() {
        require_once __DIR__ . '/../../session_config.php';
        @session_start();
        require_once __DIR__ . '/../../conexion.php';
        $pdo = \Config\Database::getInstance();
        $g_data = $this->getGlobalData($pdo);
        $global_configs = $g_data['configs'] ?? [];

        $slug = isset($_GET['s']) ? $_GET['s'] : '';
        $stmt = $pdo->prepare("SELECT * FROM paginas WHERE slug = ? AND estado = 'activo'");
        $stmt->execute([$slug]);
        $pagina = $stmt->fetch();

        if (!$pagina) {
            header("HTTP/1.0 404 Not Found");
            echo "Página no encontrada.";
            exit;
        }

        $site_title = ($global_configs['site_title'] ?? 'HTVPERU') . ' - ' . htmlspecialchars($pagina['titulo']);
        $color_primario = $global_configs['color_primario'] ?? '#2563eb';
        $color_secundario = $global_configs['color_secundario'] ?? '#1e40af';
        setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'esp');
        $fecha_espanol = utf8_encode(strftime("%A %d de %B del %Y"));

        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/public/page.php';
    }
}
