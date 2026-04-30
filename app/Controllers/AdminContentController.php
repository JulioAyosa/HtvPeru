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

        $sql_datos = "SELECT r.*, u.nombre_completo AS autor 
                      FROM registro_contenidos r LEFT JOIN usuarios u ON r.usuario_id = u.id 
                      WHERE $where_sql ORDER BY r.fecha DESC, r.hora DESC";
        $stmt = $pdo->prepare($sql_datos);
        $stmt->execute($params);
        $registros = $stmt->fetchAll();

        if (isset($_GET['download_csv']) && $_GET['download_csv'] == '1') {
            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=control_contenidos_" . date('Y-m-d') . ".xls");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="utf-8"></head>';
            echo '<body>';
            echo '<table border="1" cellpadding="5">';
            echo '<tr style="background-color: #1e293b; color: #ffffff; font-weight: bold; text-align: center;">';
            echo '<th>FECHA</th><th>HORA</th><th>HORA PUBL.</th><th>TITULAR</th><th>ENLACE</th><th>FUENTE</th><th>REDACTOR</th><th>SECCIÓN</th><th>PLATAFORMA</th><th>ESTADO/REBOTE</th><th>COMPLETADO</th>';
            echo '</tr>';
            
            foreach ($registros as $r) {
                $comp = $r['completado'] ? 'SÍ' : 'NO';
                
                $r_style = "";
                $sec = strtoupper($r['seccion']);
                if ($sec === 'PUBLICIDAD') $r_style = "background-color: #cffafe;";
                if ($sec === 'FLYER') $r_style = "background-color: #fef08a;";

                echo "<tr style='{$r_style}'>";
                echo "<td style='text-align:center;'>" . date('d/m/Y', strtotime($r['fecha'])) . "</td>";
                echo "<td style='text-align:center;'>" . date('H:i', strtotime($r['hora'])) . "</td>";
                echo "<td style='text-align:center;'>" . (!empty($r['hora_publicacion']) ? date('H:i', strtotime($r['hora_publicacion'])) : '-') . "</td>";
                echo "<td>" . htmlspecialchars($r['titular']) . "</td>";
                $enlace_full = strpos($r['enlace'], 'http') === 0 ? $r['enlace'] : "https://htvperu.com/" . ltrim($r['enlace'], '/');
                echo "<td><a href='" . htmlspecialchars($enlace_full) . "'>Abrir Enlace</a></td>";
                echo "<td>" . (!empty($r['fuente_url']) ? htmlspecialchars($r['fuente_url']) : '-') . "</td>";
                echo "<td>" . htmlspecialchars($r['autor'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($r['seccion']) . "</td>";
                echo "<td>" . htmlspecialchars($r['plataforma']) . "</td>";
                echo "<td>" . htmlspecialchars($r['rebote']) . "</td>";
                
                $color_comp = $r['completado'] ? '#10b981' : '#ef4444';
                echo "<td style='text-align:center; color: {$color_comp}; font-weight:bold;'>" . $comp . "</td>";
                echo "</tr>";
            }
            echo '</table></body></html>';
            exit;
        }

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
