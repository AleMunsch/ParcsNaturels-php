<?php
// app/Controllers/AuthController.php

require_once __DIR__ . '/../../core/Controller.php';

class AuthController extends Controller {
    public function login() {
        $this->render('auth/login');
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
            header('Location: /ParcsNaturels-php/public/parc/index');
            exit;
        } else {
            $this->render('auth/login', [
                'error' => 'Identifiants invalides'
            ]);
        }
    }

    public function logout() {
        unset($_SESSION['admin_logged']);
        session_destroy();
        header('Location: /ParcsNaturels-php/public/auth/login');
        exit;
    }
}