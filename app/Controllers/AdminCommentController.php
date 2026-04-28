<?php
namespace App\Controllers;

use Core\Controller;
use Config\Database;
use PDO;

class AdminCommentController extends Controller {
    
    private $pdo;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $this->pdo = Database::getInstance();
    }

    public function index() {
        require_permission('manage_comments');

        $msg = '';
        if (isset($_GET['msg'])) { 
            $msg = htmlspecialchars($_GET['msg'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); 
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $per_page = 50;
        $offset = ($page - 1) * $per_page;

        $total_comments = (int)$this->pdo->query("SELECT COUNT(*) FROM comentarios WHERE deleted_at IS NULL")->fetchColumn();
        $total_pages = ceil($total_comments / $per_page);

        $stmt_comments = $this->pdo->prepare("
            SELECT c.*, n.titulo as noticia_titulo, n.slug as noticia_slug
            FROM comentarios c
            LEFT JOIN noticias n ON c.noticia_id = n.id
            WHERE c.deleted_at IS NULL
            ORDER BY c.fecha DESC
            LIMIT ? OFFSET ?
        ");
        $stmt_comments->execute([$per_page, $offset]);
        $comentarios = $stmt_comments->fetchAll();

        $this->render('admin/comentarios/index', [
            'msg' => $msg,
            'comentarios' => $comentarios,
            'page' => $page,
            'total_pages' => $total_pages,
            'user_role' => $_SESSION['user_role'] ?? ''
        ], 'admin');
    }

    public function action() {
        require_permission('manage_comments');

        if (isset($_GET['action']) && isset($_GET['id'])) {

            $id = (int)$_GET['id'];
            $action = $_GET['action'];
            $user_role = $_SESSION['user_role'] ?? '';
            $msg = '';
            
            if ($action === 'aprobar') {
                $this->pdo->prepare("UPDATE comentarios SET estado = 'Aprobado' WHERE id = ?")->execute([$id]);
                $msg = "Comentario aprobado.";
            } elseif ($action === 'rechazar') {
                $this->pdo->prepare("UPDATE comentarios SET estado = 'Rechazado' WHERE id = ?")->execute([$id]);
                $msg = "Comentario rechazado.";
            } elseif ($action === 'eliminar' && $user_role === 'admin') {
                $this->pdo->prepare("UPDATE comentarios SET deleted_at = NOW() WHERE id = ?")->execute([$id]);
                $msg = "Comentario enviado a la Papelera.";
            }
            header("Location: /piura_noticias_php/admin/comentarios?msg=" . urlencode($msg));
            exit;
        }

        header("Location: /piura_noticias_php/admin/comentarios");
        exit;
    }
}
