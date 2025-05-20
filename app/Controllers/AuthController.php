<?php
// app/Controllers/AuthController.php

require_once __DIR__ . '/../../core/Controller.php';

class AuthController extends Controller {
    public function __construct() {
        parent::__construct();
        // Vérifier si l'utilisateur est déjà connecté
        if (!empty($_SESSION['admin_logged'])) {
            header('Location: /ParcsNaturels-php/public/?url=parc/index');
            exit;
        }
    }
    public function login() {
        $this->renderJsonOrView('auth/login');
    }

    public function doLogin() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        
        // Connexion à la BDD
        $stmt = $GLOBALS['pdo']->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged'] = true;
            header('Location: /ParcsNaturels-php/public/?url=parc/index');
            exit;
        } else {
            $this->renderJsonOrView('auth/login', [
                'error' => 'Identifiants invalides'
            ]);
        }
    }

    public function logout() {
        echo "Déconnexion réussie";
        unset($_SESSION['admin_logged']);
        session_destroy();
        header('Location: /ParcsNaturels-php/public/?url=auth/login');
        exit;
    }
}