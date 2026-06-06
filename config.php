<?php

// Configuración de bases de datos TrinityCore
$config = [

    // Base de datos de cuentas (auth)
    'auth' => [
        'host' => '127.0.0.1',
        'dbname' => 'auth',
        'user' => 'trinity',
        'pass' => 'contraseñalargaysegura',
    ],

    // Base de datos de personajes (characters)
    'characters' => [
        'host' => '127.0.0.1',
        'dbname' => 'characters',
        'user' => 'trinity',
        'pass' => 'contraseñalargaysegura',
    ],

    // Opciones comunes de PDO
    'pdo_options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ],
];

