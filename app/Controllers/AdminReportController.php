<?php
namespace App\Controllers;

use Core\Controller;
use Config\Database;
use PDO;

class AdminReportController extends Controller {
    
    private $pdo;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $this->pdo = Database::getInstance();
    }

    public function index() {
        require_permission('view_reports');

        $f_fecha_ini = $_GET['fecha_ini'] ?? date('Y-m-01');
        $f_fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $f_autor = $_GET['autor'] ?? '';
        $f_categoria = $_GET['categoria'] ?? '';

        $where_clauses = ["n.deleted_at IS NULL", "n.fecha_publicacion >= :fini", "n.fecha_publicacion <= :ffin"];
        $params = [':fini' => $f_fecha_ini . ' 00:00:00', ':ffin' => $f_fecha_fin . ' 23:59:59'];

        if (!empty($f_autor)) {
            $where_clauses[] = "n.autor_id = :autor";
            $params[':autor'] = $f_autor;
        }
        if (!empty($f_categoria)) {
            $where_clauses[] = "n.categoria = :categoria";
            $params[':categoria'] = $f_categoria;
        }

        $where_sql = implode(' AND ', $where_clauses);

        $sql_datos = "SELECT n.id, n.titulo, n.fuente_url, n.categoria, n.estado_publicacion, n.vistas, n.fecha_publicacion, u.nombre_completo AS autor 
                      FROM noticias n LEFT JOIN usuarios u ON n.autor_id = u.id 
                      WHERE $where_sql ORDER BY n.fecha_publicacion DESC";
        $stmt = $this->pdo->prepare($sql_datos);
        $stmt->execute($params);
        $noticias = $stmt->fetchAll();

        // CSV Export
        if (isset($_GET['download_csv']) && $_GET['download_csv'] == '1') {
            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=reporte_gerencial_" . date('Y-m-d') . ".xls");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="utf-8"></head>';
            echo '<body>';
            echo '<table border="1" cellpadding="5">';
            echo '<tr style="background-color: #1e293b; color: #ffffff; font-weight: bold;">';
            echo '<th>ID</th><th>TITULO</th><th>URL_FUENTE</th><th>CATEGORÍA</th><th>ESTADO</th><th>VISTAS</th><th>FECHA</th><th>AUTOR</th>';
            echo '</tr>';
            
            foreach ($noticias as $n) {
                echo "<tr>";
                echo "<td>{$n['id']}</td>";
                echo "<td>" . htmlspecialchars($n['titulo']) . "</td>";
                echo "<td>" . (!empty($n['fuente_url']) ? htmlspecialchars($n['fuente_url']) : '-') . "</td>";
                echo "<td>" . htmlspecialchars($n['categoria']) . "</td>";
                echo "<td>" . htmlspecialchars($n['estado_publicacion']) . "</td>";
                echo "<td>{$n['vistas']}</td>";
                echo "<td>{$n['fecha_publicacion']}</td>";
                echo "<td>" . htmlspecialchars($n['autor']) . "</td>";
                echo "</tr>";
            }
            echo '</table></body></html>';
            exit;
        }

        $total_vistas = array_sum(array_column($noticias, 'vistas'));
        $total_noticias = count($noticias);
        $promedio_vistas = $total_noticias > 0 ? ceil($total_vistas / $total_noticias) : 0;

        $sql_cat = "SELECT n.categoria, SUM(n.vistas) as total_v FROM noticias n WHERE $where_sql GROUP BY n.categoria ORDER BY total_v DESC LIMIT 6";
        $stmt_cat = $this->pdo->prepare($sql_cat);
        $stmt_cat->execute($params);
        $grafico_cat = $stmt_cat->fetchAll();

        $lbl_cats = json_encode(array_column($grafico_cat, 'categoria'));
        $val_cats = json_encode(array_column($grafico_cat, 'total_v'));

        $top_noticias = array_slice($noticias, 0); 
        usort($top_noticias, function($a, $b) { return $b['vistas'] <=> $a['vistas']; });
        $top5 = array_slice($top_noticias, 0, 5);

        $lbl_top = json_encode(array_map(function($t) { return substr($t['titulo'], 0, 30) . '...'; }, $top5));
        $val_top = json_encode(array_column($top5, 'vistas'));

        $autores = $this->pdo->query("SELECT id, nombre_completo FROM usuarios ORDER BY nombre_completo ASC")->fetchAll();
        $categorias_disponibles = $this->pdo->query("SELECT DISTINCT(categoria) FROM noticias WHERE categoria IS NOT NULL ORDER BY categoria")->fetchAll();

        // Monitor de Productividad en Vivo
        $staff_productivity = [];
        $stmt_staff = $this->pdo->query("
            SELECT u.id, u.nombre_completo, u.email, r.nombre as rol_nombre, 
                   u.cuota_diaria_personal, r.cuota_diaria_default 
            FROM usuarios u 
            LEFT JOIN roles r ON u.rol COLLATE utf8mb4_unicode_ci = r.nombre COLLATE utf8mb4_unicode_ci
            WHERE u.deleted_at IS NULL
        ");
        $all_staff = $stmt_staff->fetchAll();
        
        $stmt_news_today = $this->pdo->prepare("
            SELECT COUNT(*) FROM noticias 
            WHERE autor_id = ? AND DATE(fecha_publicacion) = CURDATE() AND deleted_at IS NULL
        ");

        foreach ($all_staff as $staff) {
            $cuota = $staff['cuota_diaria_personal'] !== null ? (int)$staff['cuota_diaria_personal'] : (int)$staff['cuota_diaria_default'];
            if ($cuota > 0) {
                $stmt_news_today->execute([$staff['id']]);
                $count_today = (int)$stmt_news_today->fetchColumn();
                $staff_productivity[] = [
                    'nombre' => $staff['nombre_completo'],
                    'rol' => $staff['rol_nombre'],
                    'cuota' => $cuota,
                    'is_custom' => $staff['cuota_diaria_personal'] !== null,
                    'hoy' => $count_today,
                    'pct' => min(100, round(($count_today / $cuota) * 100))
                ];
            }
        }

        $this->render('admin/reportes/index', [
            'f_fecha_ini' => $f_fecha_ini,
            'f_fecha_fin' => $f_fecha_fin,
            'f_autor' => $f_autor,
            'f_categoria' => $f_categoria,
            'noticias' => $noticias,
            'total_vistas' => $total_vistas,
            'total_noticias' => $total_noticias,
            'promedio_vistas' => $promedio_vistas,
            'lbl_cats' => $lbl_cats,
            'val_cats' => $val_cats,
            'lbl_top' => $lbl_top,
            'val_top' => $val_top,
            'autores' => $autores,
            'categorias_disponibles' => $categorias_disponibles,
            'staff_productivity' => $staff_productivity
        ], 'admin');
    }
}
