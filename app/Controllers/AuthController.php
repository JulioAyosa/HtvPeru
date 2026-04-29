<?php
namespace App\Controllers;

use Config\Database;

class AuthController {
    public function index() {
        // Redirigir si ya está logueado
        if (isset($_SESSION['user_id'])) {
            $base = defined('APP_BASE') ? APP_BASE : '';
            header("Location: {$base}/admin");
            exit;
        }

        $error = '';
        $msg_success = '';
        
        $pdo = Database::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? 'login';
            
            if ($action === 'recover') {
                $email_rec = trim($_POST['email'] ?? '');
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email_rec]);
                if ($stmt->fetch()) {
                    try {
                        $token = bin2hex(random_bytes(16));
                        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
                        
                        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email_rec]);
                        $pdo->prepare("INSERT INTO password_resets (email, token, fecha_expiracion) VALUES (?, ?, ?)")->execute([$email_rec, $token, $expira]);
                        
                        $msg_success = "Si el correo está registrado, recibirás un enlace de recuperación en tu bandeja.";
                    } catch(\Exception $e) {
                        $error = "Error al procesar recuperación.";
                    }
                } else {
                    $msg_success = "Si el correo está registrado, recibirás un enlace rápido en tu bandeja.";
                }
            } elseif ($action === 'login') {
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $client_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

                $stmt_attempts = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE (ip = ? OR email = ?) AND attempted_at > NOW() - INTERVAL 15 MINUTE");
                $stmt_attempts->execute([$client_ip, $email]);
                $attempt_count = (int)$stmt_attempts->fetchColumn();

                if ($attempt_count >= 5) {
                    $error = "Demasiados intentos de inicio de sesión para esta cuenta o IP. Por favor espera 15 minutos.";
                } elseif (empty($email) || empty($password)) {
                    $error = "Por favor ingrese ambos campos.";
                } else {
                    $stmt = $pdo->prepare("SELECT id, nombre_completo, password_hash, rol, rol_id, estado, motivo_bloqueo FROM usuarios WHERE email = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();

                    if ($user && password_verify($password, $user['password_hash'])) {
                        if (isset($user['estado']) && $user['estado'] === 'bloqueado') {
                            $motivo = isset($user['motivo_bloqueo']) ? htmlspecialchars($user['motivo_bloqueo']) : 'Violación de políticas.';
                            $error = "Tu cuenta ha sido BLOQUEADA.<br><br><strong>Motivo:</strong> " . $motivo;
                        } else {
                            $pdo->prepare("DELETE FROM login_attempts WHERE ip = ?")->execute([$client_ip]);

                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = $user['nombre_completo'];
                            $_SESSION['user_role'] = $user['rol'];
                            $_SESSION['rol_id'] = $user['rol_id'];
                            $_SESSION['last_activity'] = time();

                            if (function_exists('cargar_permisos_usuario')) {
                                cargar_permisos_usuario();
                            }

                            try {
                                $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([$user['id'], 'Login', 'Inicio de sesión desde IP: ' . $client_ip]);
                            } catch(\Exception $e) {}

                            $base = defined('APP_BASE') ? APP_BASE : '';
                            header("Location: {$base}/admin");
                            exit;
                        }
                    } else {
                        $pdo->prepare("INSERT INTO login_attempts (ip, email) VALUES (?, ?)")->execute([$client_ip, $email]);
                        try {
                            $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?, ?, ?)")->execute([0, 'Login Fallido', 'Intento fallido para email: ' . htmlspecialchars($email) . ' desde IP: ' . $client_ip]);
                        } catch(\Exception $e) {}

                        $error = "Credenciales incorrectas.";
                    }
                }
            }
        }

        require __DIR__ . '/../Views/auth/login.php';
    }
}
