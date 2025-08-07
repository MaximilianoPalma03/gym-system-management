<?php
require_once 'bdd.php';

if (
    !empty($_POST['id']) &&
    !empty($_POST['nombre']) &&
    !empty($_POST['apellido']) &&
    !empty($_POST['dni'])
) {
    // Validar que DNI sea numérico
    $dni = $_POST['dni'];
    if (!ctype_digit($dni)) {
        die("<p>El DNI debe contener solo números.</p><a href=\"editar_socio.php?id={$$_POST['id']}\">Volver</a>");
    }

    try {
        $sql = "UPDATE socios SET
                    nombre = :n,
                    apellido = :a,
                    dni = :dni
                WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':n'   => $_POST['nombre'],
            ':a'   => $_POST['apellido'],
            ':dni' => $dni,
            ':id'  => intval($_POST['id'])
        ]);

        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            echo "<p>Error: Ya existe un socio con el DNI $dni.</p>";
            echo "<a href=\"editar_socio.php?id={$_POST['id']}\">Volver</a>";
        } else {
            echo "<p>Error al actualizar: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
} else {
    echo "<p>Faltan datos obligatorios.</p><a href=\"editar_socio.php?id={$_POST['id']}\">Volver</a>";
}