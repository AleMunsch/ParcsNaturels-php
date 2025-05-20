<?php
// core/Controller.php

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class Controller {
    protected $twig;

    public function __construct() {
        // Initialisation de Twig
        $loader = new FilesystemLoader(__DIR__ . '/../app/Views');
        $this->twig = new Environment($loader);

        // Démarrer la session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Méthode utilitaire pour charger une vue Twig
    protected function render($template, $data = []) {
        echo $this->twig->render($template . '.twig', $data);
    }
    protected function wantsJson(): bool {
    $headers = getallheaders();
    return isset($headers['X-Return-JSON']) && strtolower($headers['X-Return-JSON']) === 'true';
}

protected function renderJsonOrView(string $view, array $data = []) {
    if ($this->wantsJson()) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    } else {
        $this->render($view, $data);
    }
}
}
