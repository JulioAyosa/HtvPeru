<?php
namespace App\Controllers;

use Core\Controller;
use Config\Database;
use PDO;

class AdminNewsletterController extends Controller {
    
    private $pdo;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $this->pdo = Database::getInstance();
    }

    public function index() {
        require_permission('manage_newsletters');

        $msg = '';
        if (isset($_GET['msg'])) {
            $msg_map = [
                'ok_1' => 'Boletín guardado y enviado exitosamente.',
                'eliminado' => 'Suscriptor eliminado correctamente.',
            ];
            $msg = $msg_map[$_GET['msg']] ?? '';
        }

        $configs = [];
        $stmt_cfg = $this->pdo->query("SELECT clave, valor FROM configuracion");
        while ($row = $stmt_cfg->fetch()) { $configs[$row['clave']] = $row['valor']; }

        $suscriptores = $this->pdo->query("SELECT id, email, fecha_suscripcion FROM suscriptores ORDER BY id DESC")->fetchAll();
        $total_subs = count($suscriptores);

        $historial = $this->pdo->query("SELECT id, asunto, fecha_envio, total_enviados FROM boletines_historial ORDER BY id DESC")->fetchAll();

        $this->render('admin/boletines/index', [
            'msg' => $msg,
            'configs' => $configs,
            'suscriptores' => $suscriptores,
            'total_subs' => $total_subs,
            'historial' => $historial
        ], 'admin');
    }

    public function action() {
        require_permission('manage_newsletters');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $action = $_POST['action'] ?? '';
            $redirect_msg = '';
            
            if ($action === 'send_newsletter') {
                $subject = trim($_POST['subject'] ?? '');
                $contenido = $_POST['contenido'] ?? '';
                
                $act_subs = $this->pdo->query("SELECT email FROM suscriptores")->fetchAll(PDO::FETCH_COLUMN);
                $total = count($act_subs);
                $destinatarios_json = json_encode($act_subs);

                $stmt_hist = $this->pdo->prepare("INSERT INTO boletines_historial (asunto, contenido, destinatarios, total_enviados) VALUES (?, ?, ?, ?)");
                $stmt_hist->execute([$subject, $contenido, $destinatarios_json, $total]);

                $redirect_msg = 'ok_1';
                $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], 'Envío Boletín', 'Simulación masiva e historial creado: ' . $subject]);
            }
            
            if ($action === 'delete_subscriber') {
                $id = (int)($_POST['subscriber_id'] ?? 0);
                if ($id > 0) {
                    $this->pdo->prepare("DELETE FROM suscriptores WHERE id = ?")->execute([$id]);
                }
                $redirect_msg = 'eliminado';
            }
            
            header('Location: " . APP_BASE . "admin/boletines?msg=' . urlencode($redirect_msg));
            exit;
        }

        header('Location: " . APP_BASE . "admin/boletines');
        exit;
    }
}
