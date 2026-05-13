<?php
/**
 * Media Firewall (Antigravity Mode)
 * Blindaje contra OOM (Pixel Bombs) y File Upload Bypasses (Magic Bytes)
 */

function media_firewall_check($tmp_path, $original_name) {
    // 1. Verificar si el archivo físico es válido y leíble
    if (!file_exists($tmp_path) || !is_readable($tmp_path)) {
        return ['ok' => false, 'error' => 'Archivo temporal corrupto o desaparecido.'];
    }

    // 2. Comprobación real de Magic Bytes
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $real_mime = finfo_file($finfo, $tmp_path);
    finfo_close($finfo);

    $allowed_mimes = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif', 
        'application/pdf', 'video/mp4', 'text/plain' 
    ];

    if (!in_array($real_mime, $allowed_mimes)) {
        return ['ok' => false, 'error' => "Archivo malicioso: El tipo detectado del archivo ($real_mime) no coincide con lo permitido aunque la extensión sea diferente."];
    }

    // 3. Si es imagen, prevengamos la Bomba de Píxeles (Megapíxeles)
    if (strpos($real_mime, 'image/') === 0) {
        $size_info = @getimagesize($tmp_path);
        if ($size_info !== false) {
            $width = $size_info[0];
            $height = $size_info[1];
            
            // Limit to 16 Megapixels (e.g. 4000x4000)
            if (($width * $height) > 16000000) {
                return ['ok' => false, 'error' => "Pixel Bomb detectada: La imagen excede los 16 Megapíxeles ($width x $height). Podría colapsar la memoria del servidor al procesarla."];
            }
        } else {
             // Es una imagen pero getimagesize falló -> Archivo bitmap posiblemente manipulado
             return ['ok' => false, 'error' => "Archivo de imagen corrupto o sintéticamente malformado."];
        }
    }

    return ['ok' => true];
}
