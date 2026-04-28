<?php
namespace App\Controllers;

use Core\Controller;
use Config\Database;

class PublicAuthController extends Controller {
    private $pdo;
    private $configs;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $this->pdo = Database::getInstance();
        
        $this->configs = [];
        $stmt = $this->pdo->query("SELECT clave, valor FROM configuracion WHERE clave IN ('social_login_estado', 'google_client_id', 'google_client_secret', 'facebook_app_id', 'facebook_app_secret')");
        while ($row = $stmt->fetch()) {
            $this->configs[$row['clave']] = $row['valor'];
        }
    }

    private function getBaseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if (basename($dir) === 'public') {
            $dir = dirname($dir); // Go up one level if in public directory
        }
        return $protocol . $_SERVER['HTTP_HOST'] . ($dir === '/' || $dir === '\\' ? '' : $dir);
    }

    private function getRedirectBackUrl() {
        return $_SESSION['auth_redirect_back'] ?? $this->getBaseUrl() . '/';
    }

    public function googleRedirect() {
        if (($this->configs['social_login_estado'] ?? 'inactivo') !== 'activo') {
            die("El login social está desactivado.");
        }
        if (isset($_GET['return_to'])) {
            $_SESSION['auth_redirect_back'] = $_GET['return_to'];
        }
        
        $clientId = $this->configs['google_client_id'] ?? '';
        $redirectUri = $this->getBaseUrl() . '/auth/google/callback';
        
        $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'prompt' => 'select_account'
        ]);
        
        header('Location: ' . $authUrl);
        exit;
    }

    public function googleCallback() {
        if (!isset($_GET['code'])) {
            header('Location: ' . $this->getRedirectBackUrl());
            exit;
        }

        $clientId = $this->configs['google_client_id'] ?? '';
        $clientSecret = $this->configs['google_client_secret'] ?? '';
        $redirectUri = $this->getBaseUrl() . '/auth/google/callback';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
            'code' => $_GET['code']
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $tokenData = json_decode($response, true);
        
        if (isset($tokenData['access_token'])) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v2/userinfo');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenData['access_token']]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $userInfoResponse = curl_exec($ch);
            curl_close($ch);

            $userInfo = json_decode($userInfoResponse, true);
            
            if (isset($userInfo['id'])) {
                $this->loginOrCreateUser('google', $userInfo['id'], $userInfo['name'], $userInfo['email'] ?? null, $userInfo['picture'] ?? null);
            }
        }
        
        header('Location: ' . $this->getRedirectBackUrl() . '#comentarios');
        exit;
    }

    public function facebookRedirect() {
        if (($this->configs['social_login_estado'] ?? 'inactivo') !== 'activo') {
            die("El login social está desactivado.");
        }
        if (isset($_GET['return_to'])) {
            $_SESSION['auth_redirect_back'] = $_GET['return_to'];
        }

        $appId = $this->configs['facebook_app_id'] ?? '';
        $redirectUri = $this->getBaseUrl() . '/auth/facebook/callback';
        
        $authUrl = "https://www.facebook.com/v19.0/dialog/oauth?" . http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'scope' => 'email,public_profile'
        ]);
        
        header('Location: ' . $authUrl);
        exit;
    }

    public function facebookCallback() {
        if (!isset($_GET['code'])) {
            header('Location: ' . $this->getRedirectBackUrl());
            exit;
        }

        $appId = $this->configs['facebook_app_id'] ?? '';
        $appSecret = $this->configs['facebook_app_secret'] ?? '';
        $redirectUri = $this->getBaseUrl() . '/auth/facebook/callback';

        $tokenUrl = "https://graph.facebook.com/v19.0/oauth/access_token?" . http_build_query([
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'redirect_uri' => $redirectUri,
            'code' => $_GET['code']
        ]);

        $response = @file_get_contents($tokenUrl);
        if ($response === false) {
            die("Error comunicándose con Facebook.");
        }
        $tokenData = json_decode($response, true);
        
        if (isset($tokenData['access_token'])) {
            $userUrl = "https://graph.facebook.com/me?fields=id,name,email,picture.width(200).height(200)&access_token=" . $tokenData['access_token'];
            $userInfoResponse = @file_get_contents($userUrl);
            $userInfo = json_decode($userInfoResponse, true);
            
            if (isset($userInfo['id'])) {
                $avatar = $userInfo['picture']['data']['url'] ?? null;
                $this->loginOrCreateUser('facebook', $userInfo['id'], $userInfo['name'], $userInfo['email'] ?? null, $avatar);
            }
        }
        
        header('Location: ' . $this->getRedirectBackUrl() . '#comentarios');
        exit;
    }

    private function loginOrCreateUser($provider, $uid, $name, $email, $avatar) {
        $stmt = $this->pdo->prepare("SELECT id, estado FROM usuarios_publicos WHERE proveedor = ? AND proveedor_uid = ?");
        $stmt->execute([$provider, $uid]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['estado'] === 'bloqueado') {
                die("Tu cuenta ha sido bloqueada. No puedes comentar.");
            }
            $update = $this->pdo->prepare("UPDATE usuarios_publicos SET nombre = ?, email = ?, avatar_url = ? WHERE id = ?");
            $update->execute([$name, $email, $avatar, $user['id']]);
            $_SESSION['public_user_id'] = $user['id'];
            $_SESSION['public_user_name'] = $name;
            $_SESSION['public_user_avatar'] = $avatar;
        } else {
            $insert = $this->pdo->prepare("INSERT INTO usuarios_publicos (proveedor, proveedor_uid, nombre, email, avatar_url) VALUES (?, ?, ?, ?, ?)");
            $insert->execute([$provider, $uid, $name, $email, $avatar]);
            $_SESSION['public_user_id'] = $this->pdo->lastInsertId();
            $_SESSION['public_user_name'] = $name;
            $_SESSION['public_user_avatar'] = $avatar;
        }
    }

    public function logout() {
        unset($_SESSION['public_user_id']);
        unset($_SESSION['public_user_name']);
        unset($_SESSION['public_user_avatar']);
        
        $returnTo = $_GET['return_to'] ?? $this->getBaseUrl() . '/';
        header('Location: ' . $returnTo . '#comentarios');
        exit;
    }
}
