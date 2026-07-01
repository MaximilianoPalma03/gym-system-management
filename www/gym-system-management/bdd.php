<?php
// Establece la zona horaria correcta
date_default_timezone_set('America/Argentina/Buenos_Aires');

try {
    $dsn = "mysql:host=localhost;dbname=gimnasio;charset=utf8mb4";
    $user = "root";
    $pass = "";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $conexion = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo "<h2>Error de conexión:</h2><p>{$e->getMessage()}</p>";
    exit;
}