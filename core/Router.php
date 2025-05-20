<?php

class Router {
    private $url;

    public function __construct() {
        $this->url = isset($_GET['url']) ? trim($_GET['url'], '/') : '';
        $this->route();
    }

    private function route() {
        if ($this->url === '') {
            $controllerName = 'ParcController';
            $method = 'index';
        } else {
            $segments = explode('/', $this->url);
            $controllerName = ucfirst($segments[0]) . 'Controller';
            $method = $segments[1] ?? 'index';
        }

        $controllerFile = __DIR__ . '/../app/Controllers/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;

            if (class_exists($controllerName)) {
                $controller = new $controllerName();

                if (method_exists($controller, $method)) {
                    $params = array_slice(explode('/', $this->url), 2);
                    call_user_func_array([$controller, $method], $params);
                } else {
                    http_response_code(404);
                    echo "Méthode '$method' non trouvée.";
                }
            } else {
                http_response_code(404);
                echo "Contrôleur '$controllerName' non trouvé.";
            }
        } else {
            http_response_code(404);
            echo "Fichier du contrôleur '$controllerFile' introuvable.";
        }
    }
}