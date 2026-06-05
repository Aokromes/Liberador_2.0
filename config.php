<?php

// Configuración de bases de datos TrinityCore
return [

    // Base de datos de cuentas (auth)
    'auth' => [
        'host' => 'localhost',
        'dbname' => 'auth',
        'user' => 'trinitycore',
        'pass' => 'mysecureverylongpassword',
    ],

    // Base de datos de personajes (characters)
    'characters' => [
        'host' => 'localhost',
        'dbname' => 'characters',
        'user' => 'trinitycore',
        'pass' => 'mysecureverylongpassword',
    ],

    // Opciones comunes de PDO
    'pdo_options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ],
];

