<?php
namespace App\Controllers;

use Config\Database;

class AdminContentController {
    
    public function __construct() { require_once __DIR__ . '/../../conexion.php'; }

    public function index() {
        global $pdo;
        
        $user_role = $_SESSION['user_role'] ?? 'autor';
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'] ?? 'Usuario';
        $is_admin = in_array($user_role, ['admin', 'gerente']);
        $msg = $_GET['msg'] ?? '';

        $f_fecha_ini = $_GET['fecha_ini'] ?? date('Y-m-01');
        $f_fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $f_autor = $_GET['autor'] ?? '';
        $f_plataforma = $_GET['plataforma'] ?? '';

        if (!$is_admin) {
            $f_autor = $user_id;
        }

        $where_clauses = ["fecha >= :fini", "fecha <= :ffin"];
        $params = [':fini' => $f_fecha_ini, ':ffin' => $f_fecha_fin];

        if (!empty($f_autor)) {
            $where_clauses[] = "usuario_id = :autor";
            $params[':autor'] = $f_autor;
        }
        if (!empty($f_plataforma)) {
            $where_clauses[] = "plataforma = :plataforma";
            $params[':plataforma'] = $f_plataforma;
        }

        $where_sql = implode(' AND ', $where_clauses);

        $sql_datos = "SELECT r.*, u.nombre_completo AS autor,
                       COALESCE(n.vistas, 0) AS vistas
                       FROM registro_contenidos r 
                       LEFT JOIN usuarios u ON r.usuario_id = u.id 
                       LEFT JOIN noticias n ON n.id = CAST(SUBSTRING_INDEX(r.enlace, '=', -1) AS UNSIGNED) AND r.enlace LIKE 'article.php?id=%'
                       WHERE $where_sql ORDER BY r.fecha DESC, r.hora DESC";
        $stmt = $pdo->prepare($sql_datos);
        $stmt->execute($params);
        $registros = $stmt->fetchAll();

        // Excel export is handled after grouping (see below)

        $autores = $pdo->query("SELECT id, nombre_completo FROM usuarios ORDER BY nombre_completo ASC")->fetchAll();
        
        $agrupados = [];
        try {
            $period = new \DatePeriod(
                 new \DateTime($f_fecha_ini),
                 new \DateInterval('P1D'),
                 (new \DateTime($f_fecha_fin))->modify('+1 day')
            );
            $dates = [];
            foreach ($period as $dt) {
                $dates[] = $dt->format("Y-m-d");
            }
            // Reverse so newest date is first
            $dates = array_reverse($dates);
            
            $autores_filtrados = [];
            if (!empty($f_autor)) {
                foreach($autores as $a) {
                    if ($a['id'] == $f_autor) $autores_filtrados[] = $a;
                }
            } else {
                $autores_filtrados = $autores;
            }

            $meses_es = ['01'=>'Enero', '02'=>'Febrero', '03'=>'Marzo', '04'=>'Abril', '05'=>'Mayo', '06'=>'Junio', '07'=>'Julio', '08'=>'Agosto', '09'=>'Septiembre', '10'=>'Octubre', '11'=>'Noviembre', '12'=>'Diciembre'];

            foreach ($autores_filtrados as $a) {
                $nombre = $a['nombre_completo'];
                foreach ($dates as $d) {
                    $ts = strtotime($d);
                    $m_str = $meses_es[date('m', $ts)] . ' ' . date('Y', $ts);
                    $w_str = 'Semana ' . ceil(date('j', $ts) / 7);
                    $agrupados[$nombre][$m_str][$w_str][$d] = [];
                }
            }
            
            foreach ($registros as $r) {
                $autor_nombre = $r['autor'] ?? 'Desconocido';
                $fecha = $r['fecha'];
                $ts = strtotime($fecha);
                $m_str = $meses_es[date('m', $ts)] . ' ' . date('Y', $ts);
                $w_str = 'Semana ' . ceil(date('j', $ts) / 7);
                
                // Only group if author is in filtered list (or if they are unknown)
                if (!isset($agrupados[$autor_nombre])) {
                    if (empty($f_autor)) {
                        foreach ($dates as $d) {
                            $dts = strtotime($d);
                            $dm_str = $meses_es[date('m', $dts)] . ' ' . date('Y', $dts);
                            $dw_str = 'Semana ' . ceil(date('j', $dts) / 7);
                            $agrupados[$autor_nombre][$dm_str][$dw_str][$d] = [];
                        }
                    } else {
                        continue; // Skip if filtered and author doesn't match
                    }
                }
                if (!isset($agrupados[$autor_nombre][$m_str][$w_str][$fecha])) {
                    $agrupados[$autor_nombre][$m_str][$w_str][$fecha] = [];
                }
                $agrupados[$autor_nombre][$m_str][$w_str][$fecha][] = $r;
            }
        } catch (\Exception $e) {
            $agrupados = [];
        }
        // ── Excel Export (Hierarchical) ──
        if (isset($_GET['download_csv']) && $_GET['download_csv'] == '1') {
            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=planificador_contenidos_" . date('Y-m-d') . ".xls");
            header("Pragma: no-cache");
            header("Expires: 0");

            $base_domain = 'https://htvperu.com.pe/';

            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="utf-8">';
            echo '<style>';
            echo 'body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; }';
            echo 'table { border-collapse: collapse; width: 100%; }';
            echo 'td, th { border: 1px solid #cbd5e1; padding: 5px 8px; vertical-align: middle; }';
            echo '.author-row { background-color: #1e293b; color: #ffffff; font-size: 13pt; font-weight: bold; }';
            echo '.month-row { background-color: #334155; color: #ffffff; font-size: 12pt; font-weight: bold; }';
            echo '.week-row { background-color: #64748b; color: #ffffff; font-size: 11pt; font-weight: bold; }';
            echo '.date-row { background-color: #e2e8f0; color: #1e293b; font-size: 11pt; font-weight: bold; }';
            echo '.date-empty { background-color: #fee2e2; color: #dc2626; font-style: italic; }';
            echo '.header-row { background-color: #f1f5f9; color: #475569; font-weight: bold; font-size: 10pt; text-align: center; }';
            echo '.pub-row { font-size: 10pt; }';
            echo '.pub-row-ad { background-color: #ecfeff; }';
            echo '.pub-row-flyer { background-color: #fefce8; }';
            echo '.stat { font-size: 9pt; }';
            echo '</style>';
            echo '</head><body>';
            echo '<table>';

            // Title row
            echo '<tr><td colspan="9" style="background-color:#0f172a; color:#ffffff; font-size:16pt; font-weight:bold; text-align:center; padding:12px;">PLANIFICADOR DE CONTENIDOS — HTV PERÚ</td></tr>';
            echo '<tr><td colspan="9" style="background-color:#0f172a; color:#94a3b8; font-size:10pt; text-align:center; padding:4px 0 8px 0;">Período: ' . date('d/m/Y', strtotime($f_fecha_ini)) . ' al ' . date('d/m/Y', strtotime($f_fecha_fin)) . ' | Generado: ' . date('d/m/Y H:i') . '</td></tr>';
            echo '<tr><td colspan="9"></td></tr>';

            $col_count = 9;

            foreach ($agrupados as $autor => $meses) {
                // Calculate author totals
                $total_autor = 0; $vistas_autor = 0;
                foreach ($meses as $semanas) {
                    foreach ($semanas as $fechas) {
                        foreach ($fechas as $pubs) {
                            $total_autor += count($pubs);
                            foreach ($pubs as $p) { $vistas_autor += intval($p['vistas'] ?? 0); }
                        }
                    }
                }
                echo "<tr class='author-row'><td colspan='{$col_count}'>▸ REDACTOR: " . htmlspecialchars(strtoupper($autor)) . "  —  {$total_autor} publicaciones  |  👁 " . number_format($vistas_autor) . " vistas totales</td></tr>";

                foreach ($meses as $mes_nombre => $semanas) {
                    // Month totals
                    $total_mes = 0; $vistas_mes = 0;
                    foreach ($semanas as $f) {
                        foreach ($f as $p) {
                            $total_mes += count($p);
                            foreach ($p as $pp) { $vistas_mes += intval($pp['vistas'] ?? 0); }
                        }
                    }
                    echo "<tr class='month-row'><td colspan='{$col_count}'>    📅 " . htmlspecialchars($mes_nombre) . "  —  {$total_mes} pub.  |  👁 " . number_format($vistas_mes) . " vistas</td></tr>";

                    foreach ($semanas as $semana_nombre => $fechas) {
                        // Week totals
                        $total_semana = 0; $vistas_semana = 0;
                        foreach ($fechas as $p) {
                            $total_semana += count($p);
                            foreach ($p as $pp) { $vistas_semana += intval($pp['vistas'] ?? 0); }
                        }
                        echo "<tr class='week-row'><td colspan='{$col_count}'>        📆 " . htmlspecialchars($semana_nombre) . "  —  {$total_semana} pub.  |  👁 " . number_format($vistas_semana) . " vistas</td></tr>";

                        foreach ($fechas as $fecha => $pubs) {
                            $count_pubs = count($pubs);
                            $is_empty = $count_pubs === 0;

                            if ($is_empty) {
                                echo "<tr class='date-empty'><td colspan='{$col_count}'>            📌 " . date('d/m/Y', strtotime($fecha)) . "  —  SIN PUBLICACIONES</td></tr>";
                            } else {
                                $vistas_dia = 0;
                                foreach ($pubs as $pp) { $vistas_dia += intval($pp['vistas'] ?? 0); }
                                echo "<tr class='date-row'><td colspan='{$col_count}'>            📌 " . date('d/m/Y', strtotime($fecha)) . "  —  {$count_pubs} registros  |  👁 " . number_format($vistas_dia) . " vistas</td></tr>";

                                // Table header for publications
                                echo "<tr class='header-row'>";
                                echo "<th>HORA</th><th>TITULAR</th><th>ENLACE</th><th>FUENTE</th><th>SECCIÓN</th><th>PLATAFORMA</th><th>VISTAS</th><th>ESTADO</th><th>✓</th>";
                                echo "</tr>";

                                foreach ($pubs as $r) {
                                    $row_class = 'pub-row';
                                    $sec = strtoupper($r['seccion'] ?? '');
                                    if ($sec === 'PUBLICIDAD') $row_class .= ' pub-row-ad';
                                    if ($sec === 'FLYER') $row_class .= ' pub-row-flyer';

                                    // Build full URLs
                                    $enlace_val = $r['enlace'] ?? '';
                                    if (!empty($enlace_val) && strpos($enlace_val, 'http') !== 0) {
                                        $enlace_val = $base_domain . ltrim($enlace_val, '/');
                                    }
                                    $fuente_val = $r['fuente_url'] ?? '';

                                    $comp = $r['completado'] ? 'SÍ' : 'NO';
                                    $comp_color = $r['completado'] ? '#059669' : '#dc2626';
                                    $vistas_r = number_format(intval($r['vistas'] ?? 0));

                                    echo "<tr class='{$row_class}'>";
                                    echo "<td style='text-align:center; width:60px;'>" . date('H:i', strtotime($r['hora'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($r['titular'] ?? '') . "</td>";
                                    echo "<td>" . (!empty($enlace_val) ? "<a href='" . htmlspecialchars($enlace_val) . "'>" . htmlspecialchars($enlace_val) . "</a>" : '-') . "</td>";
                                    echo "<td>" . (!empty($fuente_val) ? "<a href='" . htmlspecialchars($fuente_val) . "'>" . htmlspecialchars($fuente_val) . "</a>" : '-') . "</td>";
                                    echo "<td style='text-align:center;'>" . htmlspecialchars($r['seccion'] ?? '') . "</td>";
                                    echo "<td style='text-align:center;'>" . htmlspecialchars($r['plataforma'] ?? '') . "</td>";
                                    echo "<td style='text-align:center; font-weight:bold;'>{$vistas_r}</td>";
                                    echo "<td style='text-align:center;'>" . htmlspecialchars($r['rebote'] ?? '') . "</td>";
                                    echo "<td style='text-align:center; color:{$comp_color}; font-weight:bold;'>{$comp}</td>";
                                    echo "</tr>";
                                }
                            }
                        }
                    }
                }
                // Spacer row between authors
                echo "<tr><td colspan='{$col_count}' style='height:10px; border:none;'></td></tr>";
            }

            echo '</table></body></html>';
            exit;
        }

        $page_title = 'Planificador de Contenidos';
        
        ob_start();
        require __DIR__ . '/../Views/admin/control_contenidos/index.php';
        $view_content = ob_get_clean();
        
        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/layouts/admin.php';
    }

    public function store() {
        global $pdo;

        $user_role = $_SESSION['user_role'] ?? 'autor';
        $user_id = $_SESSION['user_id'];
        $is_admin = in_array($user_role, ['admin', 'gerente']);

        $action = $_POST['action'] ?? '';
        
        if ($action === 'add') {
            $fecha = $_POST['fecha'] ?? date('Y-m-d');
            $hora = $_POST['hora'] ?? date('H:i');
            $hora_publicacion = !empty($_POST['hora_publicacion']) ? $_POST['hora_publicacion'] : null;
            $titular = $_POST['titular'] ?? '';
            $enlace = $_POST['enlace'] ?? '';
            $seccion = $_POST['seccion'] ?? '';
            $plataforma = $_POST['plataforma'] ?? '';
            $rebote = $_POST['rebote'] ?? '';
            $fuente_url = $_POST['fuente_url'] ?? '';
            $completado = isset($_POST['completado']) ? 1 : 0;
            
            $insert_user = $is_admin ? ($_POST['usuario_id'] ?? $user_id) : $user_id;

            $stmt = $pdo->prepare("INSERT INTO registro_contenidos (fecha, hora, hora_publicacion, titular, enlace, fuente_url, usuario_id, seccion, plataforma, rebote, completado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$fecha, $hora, $hora_publicacion, $titular, $enlace, $fuente_url, $insert_user, $seccion, $plataforma, $rebote, $completado]);
            
            header("Location: /piura_noticias_php/admin/contenidos?msg=Registrado");
            exit;
        }
        
        header("Location: /piura_noticias_php/admin/contenidos");
        exit;
    }

    public function action() {
        global $pdo;

        $user_role = $_SESSION['user_role'] ?? 'autor';
        $user_id = $_SESSION['user_id'];
        $is_admin = in_array($user_role, ['admin', 'gerente']);

        if (isset($_GET['delete_id']) && $is_admin) {
            $pdo->prepare("DELETE FROM registro_contenidos WHERE id = ?")->execute([(int)$_GET['delete_id']]);
            header("Location: /piura_noticias_php/admin/contenidos?msg=" . urlencode("Registro eliminado permanentemente."));
            exit;
        }

        if (isset($_GET['toggle_id'])) {
            $tid = (int)$_GET['toggle_id'];
            $val = (int)$_GET['val'];
            
            if (!$is_admin) {
                $check = $pdo->prepare("SELECT id FROM registro_contenidos WHERE id = ? AND usuario_id = ?");
                $check->execute([$tid, $user_id]);
                if ($check->rowCount() > 0) {
                    $pdo->prepare("UPDATE registro_contenidos SET completado = ? WHERE id = ?")->execute([$val, $tid]);
                }
            } else {
                $pdo->prepare("UPDATE registro_contenidos SET completado = ? WHERE id = ?")->execute([$val, $tid]);
            }
            header("Location: /piura_noticias_php/admin/contenidos");
            exit;
        }

        header("Location: /piura_noticias_php/admin/contenidos");
        exit;
    }
}
