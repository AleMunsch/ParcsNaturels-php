<?php

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
                'exp' => time() + 3600
            ];
            $jwt = JWT::encode($payload, $this->config['jwt_secret'], 'HS256');
            $this->renderJson(['token' => $jwt]);
        } else {
            $this->renderJson(['error' => 'Identifiants invalides'], 401);
        }
    }

    public function index() {
        $this->authenticate();

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $stmt = $GLOBALS['pdo']->prepare("SELECT * FROM parcs ORDER BY date_creation DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $parcs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->renderJson([
            'page' => $page,
            'limit' => $limit,
            'count' => count($parcs),
            'data' => $parcs
        ]);
    }

    public function show($id) {
        $this->authenticate();

        $parc = $this->model->getById($id);
        if ($parc) {
            $this->renderJson($parc);
        } else {
            $this->renderJson(['error' => 'Parc non trouvé'], 404);
        }
    }

    public function store() {
        $this->authenticate();
        $data = json_decode(file_get_contents('php://input'), true);

        if ($this->model->create($data)) {
            $this->renderJson(['success' => true], 201);
        } else {
            $this->renderJson(['error' => 'Erreur lors de la création'], 400);
        }
    }

    public function update($id) {
        $this->authenticate();
        $data = json_decode(file_get_contents('php://input'), true);

        if ($this->model->update($id, $data)) {
            $this->renderJson(['success' => true]);
        } else {
            $this->renderJson(['error' => 'Erreur lors de la mise à jour'], 400);
        }
    }

    public function delete($id) {
        $this->authenticate();

        if ($this->model->delete($id)) {
            $this->renderJson(['success' => true]);
        } else {
            $this->renderJson(['error' => 'Erreur lors de la suppression'], 400);
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
            $this->renderJson(['error' => 'Token manquant'], 401);
        }
        try {
            JWT::decode($token, new Key($this->config['jwt_secret'], 'HS256'));
        } catch (Exception $e) {
            $this->renderJson(['error' => 'Token invalide : ' . $e->getMessage()], 401);
        }
    }

    protected function renderJson($data, int $statusCode = 200): void {
        $headers = getallheaders();
        $wantsJson = true;

        if (isset($headers['X-Return-JSON']) && strtolower($headers['X-Return-JSON']) === 'false') {
            $wantsJson = false;
        }

        http_response_code($statusCode);

        if ($wantsJson) {
            header('Content-Type: application/json');
            echo json_encode($data);
        } else {
            header('Content-Type: text/plain');
            echo is_array($data) ? print_r($data, true) : (string) $data;
        }

        exit;
    }
}