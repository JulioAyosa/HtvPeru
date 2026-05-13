<?php
// file: auth_helper.php
@session_start();

/**
 * Verifica si el usuario actual tiene el permiso especificado.
 *
 * @param string $clave_permiso La clave del permiso a verificar (ej. 'manage_users').
 * @return bool true si tiene el permiso, false en caso contrario.
 */
function has_permission($clave_permiso) {
    // Si la sesión no está iniciada o no hay usuario, denegar acceso.
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Los permisos se cargan en $_SESSION['user_permissions'] durante el login.
    // Si no existen (por ejemplo, si el sistema se actualizó pero el usuario no ha vuelto a hacer login),
    // intentamos cargarlos de la BD.
    if (!isset($_SESSION['user_permissions'])) {
        cargar_permisos_usuario();
    }

    $permisos = $_SESSION['user_permissions'] ?? [];

    return in_array($clave_permiso, $permisos);
}

/**
 * Carga los permisos del usuario en la sesión actual.
 * Se debe llamar durante el login o si la sesión los pierde.
 */
function cargar_permisos_usuario() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol_id'])) {
        return;
    }
    
    global $pdo;
    if (!$pdo) {
        // En caso de que se llame y no haya conexión, intentamos incluirla
        if (file_exists(__DIR__ . '/../../config/bootstrap.php')) {
            require_once __DIR__ . '/../../config/bootstrap.php';
        } else {
            return;
        }
    }

    $rol_id = (int)$_SESSION['rol_id'];
    
    $stmt = $pdo->prepare("
        SELECT p.clave 
        FROM permisos p
        INNER JOIN rol_permisos rp ON p.id = rp.permiso_id
        WHERE rp.rol_id = ?
    ");
    $stmt->execute([$rol_id]);
    
    $permisos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $_SESSION['user_permissions'] = $permisos;
}

/**
 * Función helper para requerir un permiso. 
 * Si el usuario no lo tiene, lo redirige al panel con un error o muestra un error de acceso denegado.
 */
function require_permission($clave_permiso) {
    if (!has_permission($clave_permiso)) {
        // Podríamos redirigir a un archivo de 'acceso_denegado' o mostrar un error genérico
        header("Location: admin.php?msg=access_denied");
        exit;
    }
}
