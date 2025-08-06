<?php
require_once 'bdd.php';

if (
    isset($_POST['id'], $_POST['marca'], $_POST['modelo'], $_POST['anio'],
          $_POST['color'], $_POST['precio'], $_POST['tipo'])
) {
    $sql = "UPDATE vehiculos SET
                marca = :marca,
                modelo = :modelo,
                anio = :anio,
                color = :color,
                precio = :precio,
                tipo = :tipo
            WHERE id = :id";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':id'     => $_POST['id'],
        ':marca'  => $_POST['marca'],
        ':modelo' => $_POST['modelo'],
        ':anio'   => $_POST['anio'],
        ':color'  => $_POST['color'],
        ':precio' => $_POST['precio'],
        ':tipo'   => $_POST['tipo']
    ]);

    header("Location: index.php");
    exit;
} else {
    echo "Faltan datos.";
}
?>
