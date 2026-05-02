<?php
namespace App\Services;

class SocialPublisherService {
    private $pdo;
    private $configs;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->loadConfigs();
    }

    private function loadConfigs() {
        $this->configs = [];
        $stmt = $this->pdo->query("SELECT clave, valor FROM configuracion");
        while ($row = $stmt->fetch()) {
            $this->configs[$row['clave']] = $row['valor'];
        }
    }

    private function getConfig($key, $default = '') {
        return $this->configs[$key] ?? $default;
    }

    public function publish($noticia_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM noticias WHERE id = ?");
        $stmt->execute([$noticia_id]);
        $noticia = $stmt->fetch();

        if (!$noticia || $noticia['estado_publicacion'] !== 'publicado' || $noticia['enviado_redes'] == 1) {
            return false;
        }

        // Domain to build full URLs
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domain = $protocol . $_SERVER['HTTP_HOST'];
        
        // Ensure we don't end up with http://localhost if run via CLI (cron)
        if (php_sapi_name() === 'cli' || empty($_SERVER['HTTP_HOST'])) {
            // Try to get a default domain from config, or fallback
            $domain = "https://tudominio.com"; // User should adjust this or set a env var
        }

        $link = $domain . "/piura_noticias_php/article.php?id=" . $noticia['id'];
        $titulo = $noticia['titulo'];
        $imagen_url = $noticia['imagen_url'] ? ($domain . '/piura_noticias_php/' . $noticia['imagen_url']) : '';
        $excerpt = mb_substr(strip_tags($noticia['contenido']), 0, 150) . '...';

        $mensaje = $titulo . "\n\n" . $excerpt . "\n\nSigue leyendo: " . $link;

        $logs = [];

        // 1. Webhook (Make / Zapier)
        if ($this->getConfig('auto_pub_webhook_estado') === 'activo') {
            $url = $this->getConfig('auto_pub_webhook_url');
            if (!empty($url)) {
                $payload = [
                    'id' => $noticia['id'],
                    'titulo' => $titulo,
                    'link' => $link,
                    'imagen' => $imagen_url,
                    'fecha' => date('Y-m-d H:i:s'),
                    'excerpt' => $excerpt
                ];
                $this->sendPostRequest($url, $payload, 'json');
                $logs[] = "Webhook (Make/Zapier) disparado.";
            }
        }

        // 2. Facebook Graph API
        if ($this->getConfig('auto_pub_fb_estado') === 'activo') {
            $page_id = $this->getConfig('auto_pub_fb_page_id');
            $token = $this->getConfig('auto_pub_fb_token');
            if (!empty($page_id) && !empty($token)) {
                $fb_url = "https://graph.facebook.com/v19.0/{$page_id}/feed";
                $payload = [
                    'message' => $mensaje,
                    'link' => $link,
                    'access_token' => $token
                ];
                $response = $this->sendPostRequest($fb_url, $payload, 'form');
                if ($response && isset($response['id'])) {
                    $logs[] = "Publicado en Facebook OK.";
                } else {
                    $logs[] = "Error en Facebook: " . ($response['error']['message'] ?? 'Desconocido');
                }
            }
        }

        // 3. X (Twitter) API v2
        if ($this->getConfig('auto_pub_tw_estado') === 'activo') {
            $tw_token = $this->getConfig('auto_pub_tw_access_token');
            if (!empty($tw_token)) {
                // Twitter v2 using Bearer / User Context (Simplified)
                $tw_url = "https://api.twitter.com/2/tweets";
                $payload = [
                    'text' => $titulo . " " . $link
                ];
                $headers = [
                    "Authorization: Bearer {$tw_token}",
                    "Content-Type: application/json"
                ];
                $response = $this->sendPostRequest($tw_url, $payload, 'json', $headers);
                if ($response && isset($response['data']['id'])) {
                    $logs[] = "Publicado en X (Twitter) OK.";
                } else {
                    $logs[] = "Error en X: " . ($response['detail'] ?? 'Desconocido');
                }
            }
        }

        // Marcar como enviado
        $stmt_update = $this->pdo->prepare("UPDATE noticias SET enviado_redes = 1 WHERE id = ?");
        $stmt_update->execute([$noticia['id']]);

        // Registrar en historial si hubo acción
        if (!empty($logs)) {
            $detalles = "Auto-Publicador para Noticia #" . $noticia['id'] . " ('" . $titulo . "'): " . implode(" | ", $logs);
            // We use user_id 0 to represent SYSTEM
            $stmt_log = $this->pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (0, 'Auto-Publicación', ?)");
            $stmt_log->execute([$detalles]);
        }

        return true;
    }

    private function sendPostRequest($url, $data, $type = 'json', $custom_headers = []) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        
        if ($type === 'json') {
            $json_data = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            $headers = ['Content-Type: application/json', 'Accept: application/json'];
            if (!empty($custom_headers)) {
                $headers = array_merge($headers, $custom_headers);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        // Para evitar problemas en Windows/XAMPP locales sin certificados SSL configurados
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}
