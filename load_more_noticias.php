<?php
require_once 'conexion.php';

// PRE-PRODUCCION: Rate limiting para endpoint legacy
if (function_exists('check_rate_limit') && !check_rate_limit($pdo, 'load_more', 30, 1)) {
    http_response_code(429);
    die('Demasiadas solicitudes. Intenta de nuevo en un momento.');
}

$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 19;
$limit = 10;

// Obtener noticias de load more
$recientes_stmt = $pdo->prepare("SELECT id, slug, titulo, categoria, extracto, imagen_url, video_poster_url FROM noticias WHERE categoria != 'Publicidad' AND estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC LIMIT ? OFFSET ?");
$recientes_stmt->bindValue(1, $limit, PDO::PARAM_INT);
$recientes_stmt->bindValue(2, $offset, PDO::PARAM_INT);
$recientes_stmt->execute();
$recientes = $recientes_stmt->fetchAll();

if (count($recientes) === 0) {
    echo ""; // Vacío indica que no hay más
    exit;
}

foreach ($recientes as $r) {
    ?>
    <a href="article.php?slug=<?php echo urlencode($r['slug'] ?? ''); ?>" class="news-card">
        <div class="card-img-wrap">
            <?php echo renderMedia($r['imagen_url'], 'card-img', $r['video_poster_url'] ?? '', false); ?>
        </div>
        <div class="card-content">
            <span class="card-category"><?php echo htmlspecialchars($r['categoria']); ?></span>
            <h3 class="card-title"><?php echo htmlspecialchars($r['titulo']); ?></h3>
        </div>
    </a>
    <?php
}
?>
