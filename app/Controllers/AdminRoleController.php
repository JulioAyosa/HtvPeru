<?php
namespace App\Controllers;

use Config\Database;

class AdminRoleController {
    
    public function __construct() {
        require_once __DIR__ . '/../../conexion.php';
        
        
        
        if (function_exists('require_permission')) {
            require_permission('manage_roles');
        } else {
            if ($_SESSION['user_role'] !== 'admin') {
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
            'created' => 'Rol creado exitosamente.',
            'updated' => 'Rol actualizado correctamente.',
            'deleted' => 'Rol eliminado permanentemente.',
            'protected' => 'Error: No puedes modificar los permisos del Administrador Principal a menos que seas el Administrador Principal.',
            'system_protected' => 'Error: No puedes eliminar un rol base del sistema.',
            'has_users' => 'Error: No puedes eliminar este rol porque hay usuarios asignados a él. Cambia el rol de esos usuarios primero.',
        ];
        $msg_key = $_GET['msg'] ?? '';
        $msg = $msg_map[$msg_key] ?? '';

        $roles = $pdo->query("SELECT id, nombre, descripcion, is_system FROM roles ORDER BY id ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $permisos_raw = $pdo->query("SELECT id, clave, modulo, descripcion FROM permisos ORDER BY modulo ASC, id ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $permisos_agrupados = [];
        foreach ($permisos_raw as $p) {
            $permisos_agrupados[$p['modulo']][] = $p;
        }

        $rol_permisos = [];
        $rp_stmt = $pdo->query("SELECT rol_id, permiso_id FROM rol_permisos");
        while ($rp = $rp_stmt->fetch(\PDO::FETCH_ASSOC)) {
            $rol_permisos[$rp['rol_id']][] = $rp['permiso_id'];
        }

        $page_title = 'Roles y Permisos';
        
        ob_start();
        require __DIR__ . '/../Views/admin/roles/index.php';
        $view_content = ob_get_clean();
        
        chdir(__DIR__ . '/../../');
        require __DIR__ . '/../Views/layouts/admin.php';
    }

    public function action() {
        global $pdo;

        $user_id = $_SESSION['user_id'];
        $action = $_POST['action'] ?? '';

        if ($action === 'create_role') {
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $permisos_seleccionados = $_POST['permisos'] ?? [];

            if (!empty($nombre)) {
                $stmt = $pdo->prepare("INSERT INTO roles (nombre, descripcion) VALUES (?, ?)");
                $stmt->execute([$nombre, $descripcion]);
                $nuevo_rol_id = $pdo->lastInsertId();

                if (!empty($permisos_seleccionados)) {
                    $stmt_p = $pdo->prepare("INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (?, ?)");
                    foreach ($permisos_seleccionados as $p_id) {
                        $stmt_p->execute([$nuevo_rol_id, $p_id]);
                    }
                }
                
                $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$user_id, 'Creación de Rol', "Creó el rol: $nombre"]);
                header('Location: /piura_noticias_php/admin/roles?msg=created');
                exit;
            }
        } elseif ($action === 'edit_role') {
            $rol_id = (int)$_POST['rol_id'];
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $permisos_seleccionados = $_POST['permisos'] ?? [];

            if ($rol_id === 1 && $_SESSION['rol_id'] != 1) {
                 header('Location: /piura_noticias_php/admin/roles?msg=protected');
                 exit;
            }

            if (!empty($nombre)) {
                $stmt = $pdo->prepare("UPDATE roles SET nombre = ?, descripcion = ? WHERE id = ?");
                $stmt->execute([$nombre, $descripcion, $rol_id]);

                if ($rol_id !== 1) { 
                    $pdo->prepare("DELETE FROM rol_permisos WHERE rol_id = ?")->execute([$rol_id]);
                    if (!empty($permisos_seleccionados)) {
                        $stmt_p = $pdo->prepare("INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (?, ?)");
                        foreach ($permisos_seleccionados as $p_id) {
                            $stmt_p->execute([$rol_id, $p_id]);
                        }
                    }
                }
                
                $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$user_id, 'Actualización de Rol', "Actualizó el rol ID: $rol_id"]);
                header('Location: /piura_noticias_php/admin/roles?msg=updated');
                exit;
            }
        } elseif ($action === 'delete_role') {
            $rol_id = (int)$_POST['rol_id'];
            
            $stmt_check = $pdo->prepare("SELECT is_system FROM roles WHERE id = ?");
            $stmt_check->execute([$rol_id]);
            $rol = $stmt_check->fetch();

            if ($rol && $rol['is_system'] == 1) {
                header('Location: /piura_noticias_php/admin/roles?msg=system_protected');
                exit;
            }

            $stmt_users = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE rol_id = ?");
            $stmt_users->execute([$rol_id]);
            $count = $stmt_users->fetchColumn();

            if ($count > 0) {
                header('Location: /piura_noticias_php/admin/roles?msg=has_users');
                exit;
            }

            $pdo->prepare("DELETE FROM roles WHERE id = ?")->execute([$rol_id]);
            $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$user_id, 'Eliminación de Rol', "Eliminó el rol ID: $rol_id"]);
            
            header('Location: /piura_noticias_php/admin/roles?msg=deleted');
            exit;
        }

        header('Location: /piura_noticias_php/admin/roles');
        exit;
    }
}
