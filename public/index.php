<?php
// public/index.php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../core/Router.php';

// Charger la config
$config = require __DIR__ . '/../config/config.php';

// Connexion à la base de données
try {
    $pdo = new PDO(
        'mysql:host=' . $config['db']['host'] .
        ';dbname=' . $config['db']['name'] .
        ';charset=' . $config['db']['charset'],
        $config['db']['user'],
        $config['db']['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // On stocke la connexion dans une variable globale
    $GLOBALS['pdo'] = $pdo;
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Lancer le routeur
new Router();