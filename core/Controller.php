<?php
namespace Core;

class Controller {
    /**
     * Render a view within a layout
     *
     * @param string $view The view path relative to app/Views/ (e.g. 'admin/multimedia/index')
     * @param array $data Data to be extracted into the view
     * @param string|null $layout The layout to use (e.g. 'admin' or 'main'). null for no layout.
     */
    protected function render(string $view, array $data = [], ?string $layout = 'admin') {
        // Importar variables globales necesarias para las vistas y layouts legacy
        global $pdo;
        
        extract($data);
        
        ob_start();
        $view_path = __DIR__ . '/../app/Views/' . $view . '.php';
        if (file_exists($view_path)) {
            require $view_path;
        } else {
            throw new \Exception("View not found: " . $view_path);
        }
        $view_content = ob_get_clean();
        
        if ($layout) {
            $cwd = getcwd();
            chdir(__DIR__ . '/../');
            $layout_path = __DIR__ . '/../app/Views/layouts/' . $layout . '.php';
            if (file_exists($layout_path)) {
                require $layout_path;
            } else {
                echo $view_content;
            }
            chdir($cwd);
        } else {
            echo $view_content;
        }
    }
}
