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
        // ── Excel Export (Compact Single-Sheet) ──
        if (isset($_GET['download_csv']) && $_GET['download_csv'] == '1') {
            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=planificador_contenidos_" . date('Y-m-d') . ".xls");
            header("Pragma: no-cache");
            header("Expires: 0");

            $base_domain = 'https://htvperu.com.pe/';
            $col_count = 9;
            $periodo_str = date('d/m/Y', strtotime($f_fecha_ini)) . ' al ' . date('d/m/Y', strtotime($f_fecha_fin));

            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="utf-8">';
            echo '<style>';
            echo 'body { font-family: Calibri, Arial, sans-serif; font-size: 10pt; }';
            echo 'table { border-collapse: collapse; width: 100%; }';
            echo 'td, th { border: 1px solid #cbd5e1; padding: 4px 6px; vertical-align: middle; }';
            echo '</style>';
            echo '</head><body>';
            echo '<table>';

            // ── HEADER ──
            echo '<tr><td colspan="' . $col_count . '" style="background:#0f172a; color:#fff; font-size:16pt; font-weight:bold; text-align:center; padding:12px;">PLANIFICADOR DE CONTENIDOS — HTV PERÚ</td></tr>';
            echo '<tr><td colspan="' . $col_count . '" style="background:#0f172a; color:#94a3b8; font-size:10pt; text-align:center;">Período: ' . $periodo_str . ' | Generado: ' . date('d/m/Y H:i') . '</td></tr>';
            echo '<tr><td colspan="' . $col_count . '"></td></tr>';

            // ── RESUMEN EJECUTIVO ──
            echo '<tr><td colspan="' . $col_count . '" style="background:#1e293b; color:#fff; font-size:12pt; font-weight:bold; padding:8px;">📊 RESUMEN EJECUTIVO</td></tr>';
            echo '<tr style="background:#334155; color:#fff; font-weight:bold; text-align:center; font-size:9pt;">';
            echo '<th colspan="3">REDACTOR</th><th>PUB.</th><th>VISTAS</th><th>MESES</th><th>DÍAS ACTIVOS</th><th>DÍAS INACTIVOS</th><th>% ACTIVIDAD</th>';
            echo '</tr>';

            foreach ($agrupados as $autor => $meses) {
                $ta = 0; $va = 0; $da = 0; $di = 0;
                foreach ($meses as $semanas) {
                    foreach ($semanas as $fechas) {
                        foreach ($fechas as $pubs) {
                            $c = count($pubs);
                            $ta += $c;
                            if ($c > 0) $da++; else $di++;
                            foreach ($pubs as $p) { $va += intval($p['vistas'] ?? 0); }
                        }
                    }
                }
                $total_dias = $da + $di;
                $pct = $total_dias > 0 ? round(($da / $total_dias) * 100) : 0;
                $pct_bg = $pct < 50 ? 'background:#fee2e2; color:#dc2626;' : ($pct < 75 ? 'background:#fef3c7; color:#d97706;' : 'background:#dcfce7; color:#059669;');
                echo "<tr>";
                echo "<td colspan='3' style='font-weight:bold;'>" . htmlspecialchars($autor) . "</td>";
                echo "<td style='text-align:center; font-weight:bold;'>{$ta}</td>";
                echo "<td style='text-align:center; font-weight:bold; color:#be185d;'>" . number_format($va) . "</td>";
                echo "<td style='text-align:center;'>" . count($meses) . "</td>";
                echo "<td style='text-align:center; color:#059669; font-weight:bold;'>{$da}</td>";
                echo "<td style='text-align:center; color:#dc2626; font-weight:bold;'>{$di}</td>";
                echo "<td style='text-align:center; font-weight:bold; {$pct_bg}'>{$pct}%</td>";
                echo "</tr>";
            }

            echo '<tr><td colspan="' . $col_count . '" style="border:none; height:20px;"></td></tr>';

            // ── DETALLE POR REDACTOR (solo días con actividad) ──
            foreach ($agrupados as $autor => $meses) {
                $total_autor = 0; $vistas_autor = 0;
                foreach ($meses as $semanas) {
                    foreach ($semanas as $fechas) {
                        foreach ($fechas as $pubs) {
                            $total_autor += count($pubs);
                            foreach ($pubs as $p) { $vistas_autor += intval($p['vistas'] ?? 0); }
                        }
                    }
                }

                echo "<tr><td colspan='{$col_count}' style='background:#0f172a; color:#fff; font-size:13pt; font-weight:bold; padding:10px;'>👤 " . htmlspecialchars(strtoupper($autor)) . "  —  {$total_autor} pub.  |  👁 " . number_format($vistas_autor) . " vistas</td></tr>";

                foreach ($meses as $mes_nombre => $semanas) {
                    $tm = 0; $vm = 0;
                    foreach ($semanas as $f) {
                        foreach ($f as $p) {
                            $tm += count($p);
                            foreach ($p as $pp) { $vm += intval($pp['vistas'] ?? 0); }
                        }
                    }
                    if ($tm === 0) {
                        echo "<tr><td colspan='{$col_count}' style='background:#334155; color:#94a3b8; font-weight:bold; padding:6px 10px;'>📅 " . htmlspecialchars($mes_nombre) . "  —  SIN ACTIVIDAD</td></tr>";
                        continue;
                    }
                    echo "<tr><td colspan='{$col_count}' style='background:#334155; color:#fff; font-size:11pt; font-weight:bold; padding:6px 10px;'>📅 " . htmlspecialchars($mes_nombre) . "  —  {$tm} pub.  |  👁 " . number_format($vm) . " vistas</td></tr>";

                    foreach ($semanas as $semana_nombre => $fechas) {
                        $ts = 0; $vs = 0;
                        foreach ($fechas as $p) {
                            $ts += count($p);
                            foreach ($p as $pp) { $vs += intval($pp['vistas'] ?? 0); }
                        }
                        if ($ts === 0) continue; // Skip empty weeks entirely
                        echo "<tr><td colspan='{$col_count}' style='background:#64748b; color:#fff; font-weight:bold; padding:5px 14px;'>📆 " . htmlspecialchars($semana_nombre) . "  —  {$ts} pub.  |  👁 " . number_format($vs) . " vistas</td></tr>";

                        foreach ($fechas as $fecha => $pubs) {
                            if (count($pubs) === 0) continue; // Skip empty days
                            $vd = 0;
                            foreach ($pubs as $pp) { $vd += intval($pp['vistas'] ?? 0); }
                            echo "<tr><td colspan='{$col_count}' style='background:#e2e8f0; font-weight:bold; padding:4px 18px;'>📌 " . date('d/m/Y', strtotime($fecha)) . "  —  " . count($pubs) . " reg.  |  👁 " . number_format($vd) . " vistas</td></tr>";

                            // Column headers
                            echo "<tr style='background:#f1f5f9; font-weight:bold; text-align:center; font-size:9pt; color:#475569;'>";
                            echo "<th>HORA</th><th>TITULAR</th><th>ENLACE</th><th>FUENTE</th><th>SECCIÓN</th><th>PLATAFORMA</th><th>VISTAS</th><th>ESTADO</th><th>✓</th>";
                            echo "</tr>";

                            foreach ($pubs as $r) {
                                $rs = '';
                                $sec = strtoupper($r['seccion'] ?? '');
                                if ($sec === 'PUBLICIDAD') $rs = "background:#ecfeff;";
                                if ($sec === 'FLYER') $rs = "background:#fefce8;";

                                $ev = $r['enlace'] ?? '';
                                if (!empty($ev) && strpos($ev, 'http') !== 0) {
                                    $ev = $base_domain . ltrim($ev, '/');
                                }
                                $fv = $r['fuente_url'] ?? '';
                                $comp = $r['completado'] ? 'SÍ' : 'NO';
                                $cc = $r['completado'] ? '#059669' : '#dc2626';

                                echo "<tr style='{$rs}'>";
                                echo "<td style='text-align:center;'>" . date('H:i', strtotime($r['hora'])) . "</td>";
                                echo "<td>" . htmlspecialchars($r['titular'] ?? '') . "</td>";
                                echo "<td>" . (!empty($ev) ? "<a href='" . htmlspecialchars($ev) . "'>" . htmlspecialchars($ev) . "</a>" : '-') . "</td>";
                                echo "<td>" . (!empty($fv) ? "<a href='" . htmlspecialchars($fv) . "'>" . htmlspecialchars($fv) . "</a>" : '-') . "</td>";
                                echo "<td style='text-align:center;'>" . htmlspecialchars($r['seccion'] ?? '') . "</td>";
                                echo "<td style='text-align:center;'>" . htmlspecialchars($r['plataforma'] ?? '') . "</td>";
                                echo "<td style='text-align:center; font-weight:bold; color:#be185d;'>" . number_format(intval($r['vistas'] ?? 0)) . "</td>";
                                echo "<td style='text-align:center;'>" . htmlspecialchars($r['rebote'] ?? '') . "</td>";
                                echo "<td style='text-align:center; color:{$cc}; font-weight:bold;'>{$comp}</td>";
                                echo "</tr>";
                            }
                        }
                    }
                }
                // Spacer between authors
                echo "<tr><td colspan='{$col_count}' style='border:none; height:15px;'></td></tr>";
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
