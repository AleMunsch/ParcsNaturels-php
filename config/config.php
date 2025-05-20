<?php
// config/config.php

return [
    'db' => [
        'host' => 'localhost',
        'name' => 'parcs-db',
        'user' => 'root',
        'pass' => '', 
        'charset' => 'utf8mb4',
    ],
    'jwt_secret' => 'e1bfccc2b16177e36f2f95447e85ff0aa043f37bc4d873c036c8ddff5614f1c7',
    'base_url' => 'http://localhost/ParcsNaturels-php/public/',
    'admin' => 'admin@parcs-naturels.fr',
    // 'admin_password' => 'admin123',
    'smtp' => [
        'host' => 'smtp.example.com',
        'username' => 'user@example.com',
        'password' => 'motdepasse',
        'port' => 1025,
        'encryption' => 'tls'
    ]
];