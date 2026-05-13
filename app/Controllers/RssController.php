<?php
namespace App\Controllers;

use Config\Database;

class RssController {
    public function index() {
        require_once __DIR__ . '/../../config/bootstrap.php';
        $pdo = \Config\Database::getInstance();
        
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        // Ajustamos la url base al directorio público asumiendo que está en /piura_noticias_php o similar
        $base_url = rtrim($protocol . "://" . $host . dirname($_SERVER['PHP_SELF']), '/');
        // Quitar /public si quedó
        $base_url = str_replace('/public', '', $base_url);

        $stmt = $pdo->query("SELECT id, slug, titulo, extracto, imagen_url, fecha_publicacion FROM noticias WHERE estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC LIMIT 50");
        $noticias = $stmt->fetchAll();

        header('Content-Type: application/rss+xml; charset=utf-8');

        echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
        echo '<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        echo '<channel>' . "\n";
        echo '  <title>HTVPERU | Una Mirada al Mundo</title>' . "\n";
        echo '  <link>' . htmlspecialchars($base_url) . '/</link>' . "\n";
        echo '  <description>Las últimas noticias policiales, políticas y de actualidad regional, nacional e internacional.</description>' . "\n";
        echo '  <language>es</language>' . "\n";
        echo '  <atom:link href="' . htmlspecialchars($base_url) . '/rss" rel="self" type="application/rss+xml" />' . "\n";

        foreach ($noticias as $row) {
            $item_link = $base_url . "/" . urlencode($row['slug']);
            $pubDate = date('D, d M Y H:i:s T', strtotime($row['fecha_publicacion']));
            
            echo "  <item>\n";
            echo "    <title><![CDATA[" . str_replace(']]>', ']]&gt;', $row['titulo']) . "]]></title>\n";
            echo "    <link>" . htmlspecialchars($item_link) . "</link>\n";
            echo "    <guid isPermaLink=\"true\">" . htmlspecialchars($item_link) . "</guid>\n";
            echo "    <description><![CDATA[" . str_replace(']]>', ']]&gt;', $row['extracto']) . "]]></description>\n";
            echo "    <pubDate>" . $pubDate . "</pubDate>\n";
            
            if (!empty($row['imagen_url'])) {
                $img_url = rtrim($base_url, '/') . '/' . ltrim($row['imagen_url'], '/');
                $ext = strtolower(pathinfo($row['imagen_url'], PATHINFO_EXTENSION));
                $mime = 'image/jpeg';
                if ($ext === 'png') $mime = 'image/png';
                if ($ext === 'gif') $mime = 'image/gif';
                if ($ext === 'webp') $mime = 'image/webp';
                
                echo "    <enclosure url=\"" . htmlspecialchars($img_url) . "\" type=\"$mime\" length=\"0\" />\n";
                echo "    <media:content url=\"" . htmlspecialchars($img_url) . "\" medium=\"image\" />\n";
            }
            
            echo "  </item>\n";
        }

        echo "</channel>\n";
        echo "</rss>\n";
    }
}
