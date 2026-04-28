<?php
function applyWatermark($image_path, $pdo) {
    if(!file_exists($image_path)) return false;
    
    // Validar configuracion
    $stmt = $pdo->query("SELECT clave, valor FROM configuracion WHERE clave IN ('watermark_url', 'watermark_estado')");
    $configs = [];
    while($row = $stmt->fetch()) { $configs[$row['clave']] = $row['valor']; }
    
    if(!isset($configs['watermark_estado']) || $configs['watermark_estado'] !== 'activo') return false;
    if(empty($configs['watermark_url']) || !file_exists($configs['watermark_url'])) return false;
    
    $watermark_path = $configs['watermark_url'];
    
    // Obtener info de imagenes
    $img_info = getimagesize($image_path);
    $wm_info = getimagesize($watermark_path);
    if(!$img_info || !$wm_info) return false;
    
    // Crear recurso de la imagen principal
    $ext = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
    switch($ext) {
        case 'jpg':
        case 'jpeg': $img = @imagecreatefromjpeg($image_path); break;
        case 'png': $img = @imagecreatefrompng($image_path); break;
        case 'webp': $img = @imagecreatefromwebp($image_path); break;
        default: return false; // formato no soportado
    }
    if(!$img) return false;
    
    // Crear recurso del watermark
    $wm_ext = strtolower(pathinfo($watermark_path, PATHINFO_EXTENSION));
    switch($wm_ext) {
        case 'png': $wm = @imagecreatefrompng($watermark_path); break;
        case 'webp': $wm = @imagecreatefromwebp($watermark_path); break;
        default: imagedestroy($img); return false;
    }
    if(!$wm) { imagedestroy($img); return false; }
    
    // Calcular escala y posicion (15% del ancho, centrado y abajo)
    $img_w = imagesx($img);
    $img_h = imagesy($img);
    $wm_w = imagesx($wm);
    $wm_h = imagesy($wm);
    
    $target_wm_w = $img_w * 0.15; // 15% width
    if ($target_wm_w < 50) $target_wm_w = 50; // min 50px
    $target_wm_h = ($wm_h / $wm_w) * $target_wm_w;
    
    $pos_x = $img_w - $target_wm_w - 20; // 20px padding from right
    $pos_y = $img_h - $target_wm_h - 20; // 20px padding from bottom
    
    // Aplicar con alpha blending
    imagealphablending($img, true);
    imagecopyresampled($img, $wm, $pos_x, $pos_y, 0, 0, $target_wm_w, $target_wm_h, $wm_w, $wm_h);
    
    // Sobrescribir original
    switch($ext) {
        case 'jpg':
        case 'jpeg': imagejpeg($img, $image_path, 90); break;
        case 'png': imagepng($img, $image_path); break;
        case 'webp': imagewebp($img, $image_path, 85); break;
    }
    
    imagedestroy($img);
    imagedestroy($wm);
    return true;
}
?>
