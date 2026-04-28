<?php
require_once 'conexion.php';

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Fetch latest
$stmt = $pdo->prepare("
    SELECT n.id, n.slug, n.titulo, n.categoria, n.extracto, n.imagen_url, n.video_poster_url, n.fecha_publicacion, u.nombre_completo AS autor 
    FROM noticias n 
    JOIN usuarios u ON n.autor_id = u.id 
    WHERE n.estado_publicacion = 'publicado' 
    ORDER BY n.fecha_publicacion DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$noticias = $stmt->fetchAll();

if (count($noticias) === 0) {
    exit; // Returns empty string for JS to detect end
}

// Logic copied from ultimas-noticias.php
function time_elapsed_string_local($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    $weeks = floor($diff->d / 7);
    $diff->d -= $weeks * 7;
    $string = array(
        'y' => $diff->y ? $diff->y . ' año' . ($diff->y > 1 ? 's' : '') : null,
        'm' => $diff->m ? $diff->m . ' mes' . ($diff->m > 1 ? 'es' : '') : null,
        'w' => $weeks ? $weeks . ' semana' . ($weeks > 1 ? 's' : '') : null,
        'd' => $diff->d ? $diff->d . ' día' . ($diff->d > 1 ? 's' : '') : null,
        'h' => $diff->h ? $diff->h . ' hora' . ($diff->h > 1 ? 's' : '') : null,
        'i' => $diff->i ? $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '') : null,
        's' => $diff->s ? $diff->s . ' segundo' . ($diff->s > 1 ? 's' : '') : null,
    );
    $string = array_filter($string);
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? 'Hace ' . implode(', ', $string) : 'justo ahora';
}

foreach ($noticias as $n) {
    $date = new DateTime($n['fecha_publicacion']);
    $time_display = $date->format('H:i');
    $date_display = $date->format('d/m/Y');
    $time_ago = time_elapsed_string_local($n['fecha_publicacion']);
    
    // HTML de tarjeta
    echo '<a href="article.php?slug=' . urlencode($n['slug'] ?? '') . '" class="timeline-item">';
    echo '<div class="timeline-time">';
    echo '<span class="hour">' . $time_display . '</span>';
    echo '<span>' . $date_display . '</span>';
    echo '<span style="font-size:0.75rem; color:var(--primary-color); margin-top:5px; font-weight:800;">' . $time_ago . '</span>';
    echo '</div>';
    
    if (!empty($n['imagen_url']) || !empty($n['video_poster_url'])) {
        echo '<div class="timeline-img-wrap">';
        echo renderMedia($n['imagen_url'], 'card-img', $n['video_poster_url'] ?? '', false);
        echo '</div>';
    }
    
    echo '<div class="timeline-content">';
    echo '<span class="timeline-cat">' . htmlspecialchars($n['categoria']) . '</span>';
    echo '<h3 class="timeline-title">' . htmlspecialchars($n['titulo']) . '</h3>';
    echo '<p class="timeline-excerpt">' . htmlspecialchars($n['extracto'] ?? '') . '</p>';
    echo '<div style="font-size:0.85rem; color:var(--text-muted); font-weight:600; display:flex; justify-content:space-between; align-items:center;">';
    echo '<span><i class="ri-user-line"></i> Por: ' . htmlspecialchars($n['autor']) . '</span>';
    echo '<span style="color:var(--primary-color); font-weight:800; display:flex; align-items:center; gap:5px;">Leer más <i class="ri-arrow-right-line"></i></span>';
    echo '</div>';
    echo '</div>';
    echo '</a>';
}
?>
