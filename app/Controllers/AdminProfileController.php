<?php
namespace App\Controllers;

use Config\Database;

class AdminProfileController {
    
    public function __construct() {
        require_once __DIR__ . '/../../config/bootstrap.php';
        
        
    }

    public function index() {
        global $pdo;
        
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'];
        $user_role = $_SESSION['user_role'];
        
        $msg_map = [
            'ok_1' => 'Error: El correo ya está en uso por otro usuario.',
            'ok_2' => 'Perfil actualizado correctamente.',
            'ok_3' => 'Error: El nombre y correo son obligatorios.',
            'avatar_error' => 'Error: El archivo de avatar no es válido o está corrupto.',
            'pass_short' => 'Error: La contraseña debe tener al menos 8 caracteres.',
            'pass_wrong' => 'Error: La contraseña actual es incorrecta.',
        ];
        $msg_key = $_GET['msg'] ?? '';
        $msg = $msg_map[$msg_key] ?? '';

        $stmt_user = $pdo->prepare("SELECT nombre_completo, email, rol, fecha_creacion, avatar_url FROM usuarios WHERE id = ?");
        $stmt_user->execute([$user_id]);
        $info = $stmt_user->fetch();

        $page_title = 'Mi Perfil';
        
        ob_start();
        require __DIR__ . '/../Views/admin/perfil/index.php';
        $view_content = ob_get_clean();
        
        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/layouts/admin.php';
    }

    public function action() {
        global $pdo;

        $user_id = $_SESSION['user_id'];

        $nombre = trim($_POST['nombre_completo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($nombre && $email) {
            $avatar_path = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
                $fw_result = media_firewall_check($_FILES['avatar']['tmp_name'], $_FILES['avatar']['name']);
                if (!$fw_result['ok']) {
                    $redirect_msg = 'avatar_error';
                    header('Location: ' . APP_BASE . '/admin/perfil?msg=' . urlencode($redirect_msg));
                    exit;
                }
                $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                    if (!is_dir(PUBLIC_PATH . 'uploads/')) mkdir(PUBLIC_PATH . 'uploads/', 0755, true);
                    $avatar_path = 'uploads/avatar_' . $user_id . '_' . time() . '.' . $ext;
                    require_once __DIR__ . '/../../app/services/MediaUploaderService.php';
                    $uploader = new \App\Services\MediaUploaderService(PUBLIC_PATH . 'uploads/');
                    $upload_res = $uploader->handleSingleUpload($_FILES['avatar']);
                    if ($upload_res['success']) {
                        $avatar_path = ltrim($upload_res['url'], '/');
                    }
                }
            }
            
            $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $check->execute([$email, $user_id]);
            if ($check->fetch()) {
                $redirect_msg = 'ok_1';
            } else {
                $stmt_old = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
                $stmt_old->execute([$user_id]);
                $old = $stmt_old->fetch();
                $cambios = [];
                if ($old && $old['nombre_completo'] !== $nombre) $cambios[] = 'nombre';
                if ($old && $old['email'] !== $email) $cambios[] = 'correo';
                if ($password !== '') $cambios[] = 'contraseña';
                if ($avatar_path) $cambios[] = 'foto de perfil';

                if ($password !== '') {
                    if (mb_strlen($password) < 8) {
                        $redirect_msg = 'pass_short';
                        header('Location: ' . APP_BASE . '/admin/perfil?msg=' . urlencode($redirect_msg));
                        exit;
                    }
                    $current_pass = $_POST['current_password'] ?? '';
                    $stmt_pw = $pdo->prepare("SELECT password_hash FROM usuarios WHERE id = ?");
                    $stmt_pw->execute([$user_id]);
                    $pw_row = $stmt_pw->fetch();
                    if (!$pw_row || !password_verify($current_pass, $pw_row['password_hash'])) {
                        $redirect_msg = 'pass_wrong';
                        header('Location: ' . APP_BASE . '/admin/perfil?msg=' . urlencode($redirect_msg));
                        exit;
                    }
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre_completo = ?, email = ?, password_hash = ? WHERE id = ?");
                    $stmt->execute([$nombre, $email, $hash, $user_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre_completo = ?, email = ? WHERE id = ?");
                    $stmt->execute([$nombre, $email, $user_id]);
                }
                if ($avatar_path) {
                    if (!empty($old['avatar_url']) && file_exists(PUBLIC_PATH . ltrim($old['avatar_url'], '/'))) {
                        @unlink(PUBLIC_PATH . ltrim($old['avatar_url'], '/'));
                    }
                    $pdo->prepare("UPDATE usuarios SET avatar_url = ? WHERE id = ?")->execute([$avatar_path, $user_id]);
                    $_SESSION['user_avatar'] = '/' . $avatar_path;
                }
                
                $detalles_msg = 'Actualizó su propia cuenta.';
                if (!empty($cambios)) {
                    $detalles_msg .= ' Modificó: ' . implode(', ', $cambios);
                } else {
                    $detalles_msg .= ' Guardó su perfil sin cambiar datos principales.';
                }
                $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$user_id, 'Actualización', $detalles_msg]);
                
                $_SESSION['user_name'] = $nombre;
                $redirect_msg = 'ok_2';
            }
        } else {
            $redirect_msg = 'ok_3';
        }
        
        header('Location: " . APP_BASE . "admin/perfil?msg=' . urlencode($redirect_msg));
        exit;
    }
}
