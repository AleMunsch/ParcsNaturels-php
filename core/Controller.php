<?php
// core/Controller.php

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class Controller {
    protected $twig;

    public function construct() {
        // Initialisation de Twig
        $loader = new FilesystemLoader(DIR__ . '/../app/Views');
        $this->twig = new Environment($loader);

        // Démarrer la session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Méthode utilitaire pour charger une vue Twig
    protected function render($template, $data = []) {
        echo $this->twig->render($template . '.html.twig', $data);
    }
}
