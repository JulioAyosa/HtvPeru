<?php
namespace App\Controllers;

use Config\Database;
use PDO;
use Exception;

class SitemapController {

    public function index() {
        require_once __DIR__ . '/../../conexion.php';
        $pdo = \Config\Database::getInstance();
        
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $base_url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
        $base_url = str_replace('/public', '', $base_url);
        if(substr($base_url, -1) !== '/') { $base_url .= '/'; }

        header("Content-Type: application/xml; charset=utf-8");

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";

        echo "\n\t<url>";
        echo "\n\t\t<loc>" . htmlspecialchars($base_url . "index.php") . "</loc>";
        echo "\n\t\t<changefreq>always</changefreq>";
        echo "\n\t\t<priority>1.0</priority>";
        echo "\n\t</url>";

        $paginas = $pdo->query("SELECT slug, fecha_modificacion FROM paginas WHERE estado='activo'");
        while($p = $paginas->fetch()){
            echo "\n\t<url>";
            echo "\n\t\t<loc>" . htmlspecialchars($base_url . "pagina/" . $p['slug']) . "</loc>";
            $date = date('Y-m-d', strtotime($p['fecha_modificacion']));
            echo "\n\t\t<lastmod>". $date ."</lastmod>";
            echo "\n\t\t<changefreq>monthly</changefreq>";
            echo "\n\t\t<priority>0.5</priority>";
            echo "\n\t</url>";
        }

        $categorias = $pdo->query("SELECT nombre, slug FROM categorias WHERE estado='activo'");
        while($c = $categorias->fetch()){
            echo "\n\t<url>";
            echo "\n\t\t<loc>" . htmlspecialchars($base_url . "categoria/" . urlencode($c['slug'])) . "</loc>";
            echo "\n\t\t<changefreq>daily</changefreq>";
            echo "\n\t\t<priority>0.8</priority>";
            echo "\n\t</url>";
        }

        $noticias = $pdo->query("SELECT slug, fecha_publicacion FROM noticias WHERE estado_publicacion='publicado' ORDER BY fecha_publicacion DESC LIMIT 1000");
        while($n = $noticias->fetch()){
            echo "\n\t<url>";
            echo "\n\t\t<loc>" . htmlspecialchars($base_url . "" . urlencode($n['slug'])) . "</loc>";
            $date = date('Y-m-d', strtotime($n['fecha_publicacion']));
            if($date !== '1970-01-01') {
                echo "\n\t\t<lastmod>". $date ."</lastmod>";
            }
            echo "\n\t\t<changefreq>never</changefreq>";
            echo "\n\t\t<priority>0.6</priority>";
            echo "\n\t</url>";
        }

        echo "\n</urlset>";
    }

    public function generate() {
        require_once __DIR__ . '/../../conexion.php';
        $pdo = \Config\Database::getInstance();

        if (php_sapi_name() !== 'cli') {
            require_once __DIR__ . '/../../session_config.php';
            @session_start();
            if (function_exists('cargar_permisos_usuario')) {
                cargar_permisos_usuario();
            }
            if (!function_exists('has_permission') || !has_permission('manage_settings')) {
                http_response_code(403);
                die(json_encode(['status' => 'error', 'message' => 'Acceso denegado. Solo administradores.']));
            }
        }

        try {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
            $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $path = str_replace('/public', '', $path);
            
            if (php_sapi_name() === 'cli') {
                $base_url = defined('APP_URL') && !empty(APP_URL) ? APP_URL . '/' : "https://htvperu.com.pe/"; 
            } else {
                $base_url = $protocol . "://" . $host . $path . "/";
            }

            // Sitemap News
            $sqlNews = "SELECT slug, titulo, fecha_publicacion FROM noticias WHERE estado_publicacion = 'publicado' AND fecha_publicacion >= DATE_SUB(NOW(), INTERVAL 2 DAY) ORDER BY fecha_publicacion DESC LIMIT 1000";
            $noticiasNews = $pdo->query($sqlNews)->fetchAll(PDO::FETCH_ASSOC);

            $newsXml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $newsXml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";
            foreach ($noticiasNews as $n) {
                $url = $base_url . "" . urlencode($n['slug']);
                $date = date('c', strtotime($n['fecha_publicacion']));
                $title = htmlspecialchars($n['titulo'], ENT_XML1, 'UTF-8');
                
                $newsXml .= "  <url>\n";
                $newsXml .= "    <loc>" . htmlspecialchars($url, ENT_XML1, 'UTF-8') . "</loc>\n";
                $newsXml .= "    <news:news>\n";
                $newsXml .= "      <news:publication>\n";
                $newsXml .= "        <news:name>HTVPERU</news:name>\n";
                $newsXml .= "        <news:language>es</news:language>\n";
                $newsXml .= "      </news:publication>\n";
                $newsXml .= "      <news:publication_date>" . $date . "</news:publication_date>\n";
                $newsXml .= "      <news:title>" . $title . "</news:title>\n";
                $newsXml .= "    </news:news>\n";
                $newsXml .= "  </url>\n";
            }
            $newsXml .= '</urlset>';

            file_put_contents(__DIR__ . '/../../sitemap-news.xml', $newsXml);

            // Sitemap General
            $sqlGen = "SELECT slug, fecha_publicacion FROM noticias WHERE estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC LIMIT 5000";
            $noticiasGen = $pdo->query($sqlGen)->fetchAll(PDO::FETCH_ASSOC);

            $genXml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $genXml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            foreach ($noticiasGen as $n) {
                $url = $base_url . "" . urlencode($n['slug']);
                $date = date('c', strtotime($n['fecha_publicacion']));
                $genXml .= "  <url>\n";
                $genXml .= "    <loc>" . htmlspecialchars($url, ENT_XML1, 'UTF-8') . "</loc>\n";
                $genXml .= "    <lastmod>" . $date . "</lastmod>\n";
                $genXml .= "    <changefreq>daily</changefreq>\n";
                $genXml .= "    <priority>0.8</priority>\n";
                $genXml .= "  </url>\n";
            }
            $genXml .= '</urlset>';

            file_put_contents(__DIR__ . '/../../sitemap.xml', $genXml);

            if(php_sapi_name() !== 'cli') {
                header('Content-Type: application/json');
                echo json_encode(["status" => "success", "message" => "Sitemaps generados"]);
            } else {
                echo "Sitemaps generados exitosamente.\n";
            }
        } catch(Exception $e) {
            if(php_sapi_name() !== 'cli') {
                header('Content-Type: application/json');
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            } else {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }
}
