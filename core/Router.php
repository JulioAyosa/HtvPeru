<?php
namespace Core;

class Router {
    protected $routes = [];

    // Método para agregar rutas
    public function add($method, $route, $controller_action, $middlewares = []) {
        // Convertimos variables {id} a regex
        $route = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $route);
        $route = '#^' . $route . '$#';
        
        $this->routes[] = [
            'method' => strtoupper($method),
            'route' => $route,
            'action' => $controller_action,
            'middlewares' => $middlewares
        ];
    }

    // Comprobar y despachar la ruta
    public function dispatch($url) {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = parse_url($url, PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['route'], $url, $matches)) {
                
                // Extraer parámetros limpios
                $params = [];
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = htmlspecialchars($match, ENT_QUOTES, 'UTF-8');
                    }
                }
                
                // Ejecutar Middlewares antes del Controller
                if (!empty($route['middlewares'])) {
                    foreach ($route['middlewares'] as $middleware) {
                        $middleware_class = "App\\Middlewares\\" . $middleware;
                        if (class_exists($middleware_class)) {
                            $middleware_obj = new $middleware_class();
                            $middleware_obj->handle();
                        } else {
                            throw new \Exception("Middleware $middleware_class not found");
                        }
                    }
                }

                // Parseamos Action "Controller@method"
                list($controller, $action_method) = explode('@', $route['action']);
                
                $controller_class = "App\\Controllers\\" . $controller;
                
                if (class_exists($controller_class)) {
                    $controller_object = new $controller_class();
                    
                    if (method_exists($controller_object, $action_method)) {
                        return call_user_func_array([$controller_object, $action_method], $params);
                    } else {
                        throw new \Exception("Method $action_method not found in $controller_class");
                    }
                } else {
                    throw new \Exception("Controller $controller_class not found");
                }
            }
        }
        
        // Retornamos falso si no se halló ruta, para que el Front Controller actúe como Fallback
        return false; 
    }
}
