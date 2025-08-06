<?php
require_once 'bdd.php';

// Validar que llegaron todos los datos
if (
    isset($_POST['marca'], $_POST['modelo'], $_POST['anio'], $_POST['color'], $_POST['precio'], $_POST['tipo'])
    && $_POST['marca'] !== '' && $_POST['modelo'] !== ''
) {
    try {
        // Preparar la sentencia SQL con placeholders
        $sql = "INSERT INTO vehiculos (marca, modelo, anio, color, precio, tipo)
                VALUES (:marca, :modelo, :anio, :color, :precio, :tipo)";

        $stmt = $conexion->prepare($sql);

        // Ejecutar con los datos del formulario
        $stmt->execute([
            ':marca' => $_POST['marca'],
            ':modelo' => $_POST['modelo'],
            ':anio' => $_POST['anio'],
            ':color' => $_POST['color'],
            ':precio' => $_POST['precio'],
            ':tipo' => $_POST['tipo']
        ]);

        // Redireccionar al index
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        echo "Error al insertar: " . $e->getMessage();
    }
} else {
    echo "Faltan datos del formulario.";
}
?>