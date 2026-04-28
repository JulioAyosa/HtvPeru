<?php
namespace App\Services;

/**
 * AssetManager — FASE 5 MODERNIZACIÓN
 * Sistema de gestión de assets CSS/JS sin dependencia de Node.js.
 * 
 * Funcionalidades:
 *   1. Minificación nativa de CSS y JS
 *   2. Cache-busting automático basado en hash del contenido del archivo
 *   3. Generación de assets minificados en dist/
 *   4. Helper para generar tags <link> y <script> con hash automático
 * 
 * Uso en vistas:
 *   <?= \App\Services\AssetManager::css('css/style.css') ?>
 *   <?= \App\Services\AssetManager::js('js/premium-features.js') ?>
 * 
 * Comando de build (ejecutar después de cambiar CSS/JS):
 *   php build_assets.php
 */
class AssetManager
{
    private static string $basePath = '';
    private static string $distPath = 'dist';
    private static ?array $manifest = null;

    /**
     * Inicializar el base path del proyecto.
     */
    private static function init(): void
    {
        if (empty(self::$basePath)) {
            self::$basePath = realpath(__DIR__ . '/../..') . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * Genera un tag <link> para un archivo CSS con hash de cache-busting.
     * Si existe la versión minificada en dist/, la usa. Si no, usa el original.
     * 
     * @param string $path Ruta relativa al proyecto (ej: 'css/style.css')
     * @return string Tag HTML <link>
     */
    public static function css(string $path): string
    {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        // Si estamos en /public, quitarlo del base
        $base = preg_replace('#/public$#', '', $base);
        // Si base está vacío, usar /
        $url = ($base ?: '') . '/' . ltrim(self::resolveAssetUrl($path), '/');
        return '<link rel="stylesheet" href="' . htmlspecialchars($url) . '">';
    }

    /**
     * Genera un tag <script> para un archivo JS con hash de cache-busting.
     * 
     * @param string $path Ruta relativa al proyecto (ej: 'js/premium-features.js')
     * @param bool $defer Agregar atributo defer
     * @return string Tag HTML <script>
     */
    public static function js(string $path, bool $defer = false): string
    {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $base = preg_replace('#/public$#', '', $base);
        $url = ($base ?: '') . '/' . ltrim(self::resolveAssetUrl($path), '/');
        $deferAttr = $defer ? ' defer' : '';
        return '<script src="' . htmlspecialchars($url) . '"' . $deferAttr . '></script>';
    }

    /**
     * Resuelve la URL final del asset:
     * 1. Si hay un manifest (build previo), usa el hash del manifest
     * 2. Si no, usa filemtime como fallback
     */
    private static function resolveAssetUrl(string $path): string
    {
        self::init();
        
        // Intentar usar el manifest de build
        $manifest = self::loadManifest();
        if ($manifest !== null && isset($manifest[$path])) {
            return $manifest[$path];
        }

        // Fallback: usar hash basado en la fecha de modificación del archivo
        $fullPath = self::$basePath . str_replace('/', DIRECTORY_SEPARATOR, $path);
        if (file_exists($fullPath)) {
            $hash = substr(md5_file($fullPath), 0, 8);
            return $path . '?v=' . $hash;
        }

        return $path;
    }

    /**
     * Carga el manifest de build (generado por build_assets.php).
     */
    private static function loadManifest(): ?array
    {
        if (self::$manifest !== null) {
            return self::$manifest;
        }

        self::init();
        $manifestFile = self::$basePath . self::$distPath . DIRECTORY_SEPARATOR . 'manifest.json';
        
        if (file_exists($manifestFile)) {
            $content = file_get_contents($manifestFile);
            self::$manifest = json_decode($content, true);
            return self::$manifest;
        }

        self::$manifest = [];
        return null;
    }

    // ====================================================================
    // MÉTODOS DE BUILD (usados por build_assets.php)
    // ====================================================================

    /**
     * Minifica un archivo CSS eliminando comentarios, whitespace y líneas vacías.
     */
    public static function minifyCss(string $css): string
    {
        // Eliminar comentarios
        $css = preg_replace('/\/\*.*?\*\//s', '', $css);
        // Eliminar espacios alrededor de :, ;, {, }, ,
        $css = preg_replace('/\s*([{};:,>~+])\s*/', '$1', $css);
        // Eliminar últimos ; antes de }
        $css = preg_replace('/;+}/', '}', $css);
        // Eliminar múltiples espacios
        $css = preg_replace('/\s+/', ' ', $css);
        // Eliminar espacios al inicio y final
        $css = trim($css);
        // Eliminar 0px → 0 (excepto 0s, 0%)
        $css = preg_replace('/(?<=[\s:,])0px/', '0', $css);
        
        return $css;
    }

    /**
     * Minifica un archivo JS de forma conservadora:
     * - Elimina comentarios de línea y bloque
     * - Elimina whitespace excesivo
     * - Preserva strings y regex para no romper funcionalidad
     */
    public static function minifyJs(string $js): string
    {
        // Eliminar comentarios de bloque
        $js = preg_replace('/\/\*.*?\*\//s', '', $js);
        // Eliminar comentarios de línea (cuidando URLs con //)
        $js = preg_replace('/(?<=[^:"\'])\/\/(?![\'"]).*/m', '', $js);
        // Reducir múltiples newlines a una
        $js = preg_replace('/\n\s*\n+/', "\n", $js);
        // Eliminar espacios al inicio de líneas
        $js = preg_replace('/^\s+/m', '', $js);
        // Eliminar líneas vacías
        $js = preg_replace('/^\s*$/m', '', $js);
        $js = preg_replace('/\n+/', "\n", $js);
        
        return trim($js);
    }

    /**
     * Ejecuta el build: minifica assets y genera manifest.
     * 
     * @param array $assets Lista de assets a procesar ['css/style.css', 'js/file.js']
     * @return array Resultados del build
     */
    public static function build(array $assets): array
    {
        self::init();
        $distDir = self::$basePath . self::$distPath;
        $results = [];
        $manifest = [];

        // Crear directorio dist/ si no existe
        if (!is_dir($distDir)) {
            mkdir($distDir, 0755, true);
        }

        foreach ($assets as $asset) {
            $fullPath = self::$basePath . str_replace('/', DIRECTORY_SEPARATOR, $asset);
            
            if (!file_exists($fullPath)) {
                $results[] = ['file' => $asset, 'status' => 'NOT FOUND'];
                continue;
            }

            $content = file_get_contents($fullPath);
            $originalSize = strlen($content);
            $ext = pathinfo($asset, PATHINFO_EXTENSION);

            // Minificar según el tipo
            if ($ext === 'css') {
                $minified = self::minifyCss($content);
            } elseif ($ext === 'js') {
                $minified = self::minifyJs($content);
            } else {
                $minified = $content;
            }

            $minifiedSize = strlen($minified);
            $hash = substr(md5($minified), 0, 8);

            // Generar nombre de archivo minificado con hash
            $basename = pathinfo($asset, PATHINFO_FILENAME);
            $subdir = pathinfo($asset, PATHINFO_DIRNAME);
            $minFileName = $basename . '.' . $hash . '.min.' . $ext;
            $minRelPath = $subdir . '/' . $minFileName;

            // Crear subdirectorio en dist/ si no existe
            $outputDir = $distDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $subdir);
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // Escribir archivo minificado
            $outputPath = $distDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $minRelPath);
            file_put_contents($outputPath, $minified);

            // Registrar en manifest
            $manifest[$asset] = self::$distPath . '/' . $minRelPath;

            $savings = round((1 - $minifiedSize / $originalSize) * 100, 1);
            $results[] = [
                'file'     => $asset,
                'original' => self::formatBytes($originalSize),
                'minified' => self::formatBytes($minifiedSize),
                'savings'  => $savings . '%',
                'output'   => $minRelPath,
                'status'   => 'OK',
            ];
        }

        // Escribir manifest
        file_put_contents($distDir . DIRECTORY_SEPARATOR . 'manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $results;
    }

    private static function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . 'B';
        return round($bytes / 1024, 1) . 'KB';
    }
}
