<?php
// bdd.php

try {
    // DSN: host, nombre de la base y charset
    $dsn = "mysql:host=localhost;dbname=vehiculos;charset=utf8mb4";
    // Usuario y contraseña (en XAMPP suele ser root y sin clave)
    $user = "root";
    $pass = "";

    // Opciones de PDO
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // lanza PDOException
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetchAll sin especificar modo
        PDO::ATTR_EMULATE_PREPARES   => false,                  // usa sentencias preparadas reales
    ];

    // Crear la conexión
    $conexion = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {
    // Si hay error, lo mostramos y detenemos el script
    echo "<h2>Error de conexión a la base de datos</h2>";
    echo "<p>{$e->getMessage()}</p>";
    exit;
}
