<?php
namespace App\Controllers;

use Config\Database;

class AdminUserController {
    
    public function __construct() {
        require_once __DIR__ . '/../../conexion.php';
        
        
        
        if (function_exists('require_permission')) {
            require_permission('manage_users');
        } else {
            if ($_SESSION['user_id'] != 1 && $_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'gerencia') {
                header('HTTP/1.1 403 Forbidden');
                die('Acceso denegado.');
            }
        }
    }

    public function index() {
        global $pdo;
        
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'];
        
        $msg_map = [
            'ok_1' => 'Error: El correo ya está registrado.',
            'ok_2' => 'Usuario creado exitosamente.',
            'ok_3' => 'Usuario actualizado correctamente.',
            'blocked' => 'Usuario bloqueado y desconectado del sistema.',
            'unblocked' => 'Usuario desbloqueado correctamente.',
            'deleted' => 'Usuario enviado a la papelera correctamente.',
            'block_self' => 'Error: No puedes bloquearte a ti mismo.',
            'delete_self' => 'Error: No puedes eliminarte a ti mismo.',
            'weak_password' => 'Error: La contraseña debe tener mínimo 8 caracteres, 1 mayúscula y 1 número.',
            'edit_protected' => 'Error: No puedes editar al Administrador Principal.',
        ];
        $msg_key = $_GET['msg'] ?? '';
        $msg = $msg_map[$msg_key] ?? '';

        $usuarios = $pdo->query("SELECT u.id, u.nombre_completo, u.email, u.rol_id, r.nombre as rol_nombre, r.is_system, u.fecha_creacion, u.estado, u.motivo_bloqueo FROM usuarios u LEFT JOIN roles r ON u.rol_id = r.id WHERE u.deleted_at IS NULL ORDER BY u.id ASC")->fetchAll();

        $roles_disponibles = $pdo->query("SELECT id, nombre FROM roles ORDER BY id ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $page_title = 'Gestión de Usuarios';
        
        ob_start();
        require __DIR__ . '/../Views/admin/usuarios/index.php';
        $view_content = ob_get_clean();
        
        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/layouts/admin.php';
    }

    public function action() {
        global $pdo;
        
        $user_id = $_SESSION['user_id'];
        $action = $_POST['action'] ?? '';

        if (isset($_GET['unblock'])) {
            $id_unblock = (int)$_GET['unblock'];
            $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'activo', motivo_bloqueo = NULL WHERE id = ?");
            $stmt->execute([$id_unblock]);
            $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$user_id, 'Actualización', 'Desbloqueó al usuario ID #' . $id_unblock]);
            header('Location: /piura_noticias_php/admin/usuarios?msg=unblocked');
            exit;
        }

        if ($action === 'block') {
            $id_block = (int)$_POST['block_user_id'];
            $motivo = trim($_POST['motivo_bloqueo']);
            
            if ($id_block === $user_id) {
                header('Location: /piura_noticias_php/admin/usuarios?msg=block_self');
                exit;
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'bloqueado', motivo_bloqueo = ? WHERE id = ?");
                $stmt->execute([$motivo, $id_block]);
                $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$user_id, 'Bloqueo', 'Bloqueó al usuario ID #' . $id_block . ' por: ' . $motivo]);
                header('Location: /piura_noticias_php/admin/usuarios?msg=blocked');
                exit;
            }
        }

        if ($action === 'delete') {
            $id_del = (int)$_POST['delete_user_id'];
            $id_reassign = (int)$_POST['reassign_user_id'];
            
            if ($id_del === $user_id) {
                header('Location: /piura_noticias_php/admin/usuarios?msg=delete_self');
                exit;
            } else {
                if ($id_reassign > 0) {
                    $stmt_reassign = $pdo->prepare("UPDATE noticias SET autor_id = ? WHERE autor_id = ?");
                    $stmt_reassign->execute([$id_reassign, $id_del]);
                }
                $del = $pdo->prepare("UPDATE usuarios SET deleted_at = NOW() WHERE id = ?");
                $del->execute([$id_del]);
                
                $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$user_id, 'Eliminación', 'Envió al usuario ID #' . $id_del . ' a la papelera.']);
                
                header('Location: /piura_noticias_php/admin/usuarios?msg=deleted');
                exit;
            }
        }

        if ($action === 'create' || $action === 'edit') {
            $redirect_msg = '';
            $id = $_POST['user_id'] ?? 0;
            $nombre = trim($_POST['nombre_completo'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $rol_id = (int)($_POST['rol_id'] ?? 3);
            $password = $_POST['password'] ?? '';

            $stmt_r = $pdo->prepare("SELECT nombre FROM roles WHERE id = ?");
            $stmt_r->execute([$rol_id]);
            $rol_nombre = $stmt_r->fetchColumn() ?: 'editor';
            $rol_enum = ($rol_id == 1) ? 'admin' : (($rol_id == 2) ? 'gerencia' : 'editor');

            if ($action === 'create' && $nombre && $email && $password) {
                if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
                    $redirect_msg = 'weak_password';
                } else {
                    $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                    $check->execute([$email]);
                    if ($check->fetch()) {
                        $redirect_msg = 'ok_1';
                    } else {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, email, password_hash, rol, rol_id) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$nombre, $email, $hash, $rol_enum, $rol_id]);
                        $nuevo_id = $pdo->lastInsertId();
                        $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$user_id, 'Creación', 'Creó al usuario ID #' . $nuevo_id . ' (' . $nombre . ') con rol ' . $rol_nombre]);
                        $redirect_msg = 'ok_2';
                    }
                }
            } elseif ($action === 'edit' && $id) {
                if ($id == 1 && $user_id != 1) {
                    header('Location: /piura_noticias_php/admin/usuarios?msg=edit_protected');
                    exit;
                }

                $stmt_old = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
                $stmt_old->execute([$id]);
                $old = $stmt_old->fetch();
                
                if ($id == 1) {
                    $rol_id = 1;
                    $rol_enum = 'admin';
                }

                $cambios = [];
                if ($old && $old['nombre_completo'] !== $nombre) $cambios[] = 'nombre';
                if ($old && $old['email'] !== $email) $cambios[] = 'correo';
                if ($old && $old['rol_id'] != $rol_id) $cambios[] = 'rol';
                if ($password !== '') $cambios[] = 'contraseña';

                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre_completo = ?, email = ?, rol = ?, rol_id = ?, password_hash = ? WHERE id = ?");
                    $stmt->execute([$nombre, $email, $rol_enum, $rol_id, $hash, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre_completo = ?, email = ?, rol = ?, rol_id = ? WHERE id = ?");
                    $stmt->execute([$nombre, $email, $rol_enum, $rol_id, $id]);
                }
                
                $detalles_msg = 'Actualizó usuario ID #' . $id . ' (' . $nombre . ')';
                if (!empty($cambios)) {
                    $detalles_msg .= '. Modificó: ' . implode(', ', $cambios);
                }
                $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$user_id, 'Actualización', $detalles_msg]);
                $redirect_msg = 'ok_3';
            }
            header('Location: /piura_noticias_php/admin/usuarios?msg=' . urlencode($redirect_msg));
            exit;
        }

        header('Location: /piura_noticias_php/admin/usuarios');
        exit;
    }
}
