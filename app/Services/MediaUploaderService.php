<?php
namespace App\Services;

class MediaUploaderService {
    
    private $upload_dir;
    private $max_batch_size = 524288000; // 500 MB
    private $max_file_size = 52428800; // 50 MB
    private $allowed_mimes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/webm', 'video/ogg'
    ];

    public function __construct($upload_dir_path) {
        $this->upload_dir = rtrim($upload_dir_path, '/') . '/';
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
        
        // Quotas asimétricas
        $role = $_SESSION['user_role'] ?? 'autor';
        if ($role === 'admin' || $role === 'gerente') {
            $this->max_batch_size = 524288000; // 500 MB
            $this->max_file_size = 52428800; // 50 MB
        } else {
            $this->max_batch_size = 52428800; // 50 MB batch normal
            $this->max_file_size = 10485760; // 10 MB per file normal
        }
    }

    /**
     * Procesa una subida múltiple originada en $_FILES['form_name']
     * Retorna array con contador de éxito y mensajes
     */
    public function handleMultipleUpload($files_array) {
        $success_count = 0;
        $failed_count = 0;
        $msg = "";

        // Verificamos si vino vacío
        if (empty($files_array['name'][0])) {
            return ['success' => 0, 'msg' => "No se subió ningún archivo."];
        }

        $total_batch_size = array_sum($files_array['size'] ?? []);
        if ($total_batch_size > $this->max_batch_size) {
            return ['success' => 0, 'msg' => "Error: Límite global superado (Max 500MB)."];
        }

        foreach ($files_array['name'] as $key => $original_name) {
            if ($files_array['error'][$key] === UPLOAD_ERR_OK) {
                $tmp_path = $files_array['tmp_name'][$key];
                $size = $files_array['size'][$key];
                
                // Ignorar si pasa de 50MB
                if ($size > $this->max_file_size) {
                    $failed_count++;
                    continue;
                }

                $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                
                // Forense MIME en memoria
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $tmp_path);
                finfo_close($finfo);

                if (!in_array($mime, $this->allowed_mimes)) {
                    $failed_count++;
                    continue;
                }
                
                // Ejecutamos la compresión WebP
                $saved = $this->transcodeToWebp($tmp_path, $mime);
                
                // Fallback a copiado seguro sin ejecutar contenido
                if (!$saved) {
                    $secure_name = uniqid('media_') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    if (move_uploaded_file($tmp_path, $this->upload_dir . $secure_name)) {
                        $success_count++;
                    } else {
                        $failed_count++;
                    }
                } else {
                    $success_count++;
                }
            }
        }

        if ($success_count > 0) {
            $msg = "$success_count archivo(s) validado(s) y procesado(s) exitosamente.";
            if ($failed_count > 0) $msg .= " ($failed_count ignorados por seguridad).";
        } else {
            $msg = "Error: No se logró subir ningún archivo protegido.";
        }

        return ['success' => $success_count, 'msg' => $msg];
    }

    /**
     * Procesa una subida individual (ej: $_FILES['imagen'])
     * Retorna array con success (bool), URL del archivo guardado y mensaje.
     */
    public function handleSingleUpload($file) {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'url' => '', 'msg' => 'Error al subir o archivo no enviado.'];
        }

        $tmp_path = $file['tmp_name'];
        $size = $file['size'];
        $original_name = $file['name'];

        if ($size > $this->max_file_size) {
            return ['success' => false, 'url' => '', 'msg' => 'El archivo supera el tamaño máximo de 50MB.'];
        }

        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp_path);
        finfo_close($finfo);

        if (!in_array($mime, $this->allowed_mimes)) {
            return ['success' => false, 'url' => '', 'msg' => 'Tipo de archivo no permitido (Solo imágenes y videos).'];
        }


        // Mejorado: generar nombre aquí
        $dest_base = uniqid('media_') . '_' . bin2hex(random_bytes(4));
        if (in_array($mime, ['image/jpeg', 'image/png'])) {
            $dest_name = $dest_base . '.webp';
            $dest_path = $this->upload_dir . $dest_name;
            $saved = $this->transcodeToWebpDirect($tmp_path, $mime, $dest_path);
        } else {
            $saved = false;
        }

        if (!$saved) {
            $dest_name = $dest_base . '.' . $ext;
            $dest_path = $this->upload_dir . $dest_name;
            if (!move_uploaded_file($tmp_path, $dest_path)) {
                return ['success' => false, 'url' => '', 'msg' => 'Error al guardar el archivo en el directorio final.'];
            }
        }

        // Retornar path relativo para la BD (ej. /uploads/media_123.webp)
        $relative_url = '/uploads/' . $dest_name;
        return ['success' => true, 'url' => $relative_url, 'msg' => 'Archivo procesado con éxito.'];
    }

    /**
     * Intercepta la imagen, escanea megapíxeles máximos y reduce a WEBP
     */
    private function transcodeToWebpDirect($tmp_path, $mime, $dest_path) {
        $img = null;
        if ($mime === 'image/jpeg') $img = @imagecreatefromjpeg($tmp_path);
        if ($mime === 'image/png') {
            $img = @imagecreatefrompng($tmp_path);
            if ($img !== false) {
                imagepalettetotruecolor($img);
                imagealphablending($img, true);
                imagesavealpha($img, true);
            }
        }

        if ($img !== false) {
            $width = imagesx($img);
            $height = imagesy($img);
            
            if (($width * $height) > 20000000) { 
                imagedestroy($img);
                return false; 
            }

            $max_w = 1280;
            if ($width > $max_w) {
                $new_w = $max_w;
                $new_h = floor($height * ($new_w / $width));
                $dst_img = imagecreatetruecolor($new_w, $new_h);
                imagealphablending($dst_img, false);
                imagesavealpha($dst_img, true);
                imagecopyresampled($dst_img, $img, 0, 0, 0, 0, $new_w, $new_h, $width, $height);
                imagedestroy($img);
                $img = $dst_img;
            }

            $result = imagewebp($img, $dest_path, 80);
            imagedestroy($img);
            return $result;
        }
        return false;
    }
    
    /**
     * Intercepta la imagen, escanea megapíxeles máximos y reduce a WEBP
     */
    private function transcodeToWebp($tmp_path, $mime) {
        $is_compressible = in_array($mime, ['image/jpeg', 'image/png']);
        if (!$is_compressible) return false;

        $dest_name = uniqid('media_') . '_' . bin2hex(random_bytes(4)) . '.webp';
        $dest_path = $this->upload_dir . $dest_name;

        $img = null;
        if ($mime === 'image/jpeg') $img = @imagecreatefromjpeg($tmp_path);
        if ($mime === 'image/png') {
            $img = @imagecreatefrompng($tmp_path);
            if ($img !== false) {
                imagepalettetotruecolor($img);
                imagealphablending($img, true);
                imagesavealpha($img, true);
            }
        }

        if ($img !== false) {
            $width = imagesx($img);
            $height = imagesy($img);
            
            // Límite drástico contra pixel bombs OOM (Evitar colapsar RAM)
            if (($width * $height) > 20000000) { 
                imagedestroy($img);
                return false; 
            }

            $max_w = 1280;
            if ($width > $max_w) {
                $new_w = $max_w;
                $new_h = floor($height * ($new_w / $width));
                $dst_img = imagecreatetruecolor($new_w, $new_h);
                imagealphablending($dst_img, false);
                imagesavealpha($dst_img, true);
                imagecopyresampled($dst_img, $img, 0, 0, 0, 0, $new_w, $new_h, $width, $height);
                imagedestroy($img);
                $img = $dst_img;
            }

            $result = imagewebp($img, $dest_path, 80);
            imagedestroy($img);
            return $result;
        }
        return false;
    }
}
