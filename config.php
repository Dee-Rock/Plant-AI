<?php
// Application configuration
$config = [
    // Database configuration
    'db' => [
        'host' => 'localhost',
        'name' => 'plantapp',
        'user' => 'Del',
        'pass' => 'RockZ@12',
        'charset' => 'utf8mb4'
    ],
    // Plant.id API configuration
    'plant_id_api_key' => 'C2fuI6zKWRrywWILYy07xRJpYF6WPWl2bHrLjiUtuAREOuWfVw' // Replace with your actual API key
];

// Set up database connection
extract($config['db']);
$dsn = "mysql:host=$host;dbname=$name;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}