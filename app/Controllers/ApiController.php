<?php
// app/Controllers/ApiParcController.php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Models/Parc.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiParcController {
    private $model;
    private $config;

    public function __construct() {
        $this->config = require __DIR__ . '/../../config/config.php';
        $this->model = new Parc($GLOBALS['pdo']);
        header('Content-Type: application/json');
    }

    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $stmt = $GLOBALS['pdo']->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $payload = [
                'iss' => 'parcs-api',
                'sub' => $admin['id'],
                'iat' => time(),
                'exp' => time() + 3600 // 1h
            ];
            $jwt = JWT::encode($payload, $this->config['jwt_secret'], 'HS256');
            echo json_encode(['token' => $jwt]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Identifiants invalides']);
        }
    }

    private function getBearerToken() {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function authenticate() {
        $token = $this->getBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant']);
            exit;
        }
        try {
            $decoded = JWT::decode($token, new Key($this->config['jwt_secret'], 'HS256'));
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token invalide']);
            exit;
        }
    }

    public function index() {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $stmt = $GLOBALS['pdo']->prepare("SELECT * FROM parcs ORDER BY date_creation DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $parcs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($parcs);
    }

    public function show($id) {
        $parc = $this->model->getById($id);
        if ($parc) {
            echo json_encode($parc);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Parc non trouvé']);
        }
    }

    public function store() {
        $this->authenticate();
        $data = json_decode(file_get_contents('php://input'), true);

        if ($this->model->create($data)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Erreur lors de la création']);
        }
    }

    public function update($id) {
        $this->authenticate();
        $data = json_decode(file_get_contents('php://input'), true);

        if ($this->model->update($id, $data)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Erreur lors de la mise à jour']);
        }
    }

    public function delete($id) {
        $this->authenticate();
        if ($this->model->delete($id)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Erreur lors de la suppression']);
        }
    }
}