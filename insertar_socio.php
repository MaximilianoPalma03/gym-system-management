<?php
session_start();
require_once 'bdd.php';

if (
    !empty($_POST['nombre']) &&
    !empty($_POST['apellido']) &&
    !empty($_POST['dni']) &&
    !empty($_POST['fecha_inscripcion']) &&
    !empty($_POST['fecha_vencimiento'])
) {
    // Validar que DNI sea numérico
    $dni = $_POST['dni'];
    if (!ctype_digit($dni)) {
        die("<p>El DNI debe contener solo números.</p><a href=\"agregar_socio.php\">Volver</a>");
    }

    // NUEVO: Tomar fechas del formulario
    $inscripcion = $_POST['fecha_inscripcion'];
    $vencimiento = $_POST['fecha_vencimiento'];

    $parcial = isset($_POST['parcial']) && $_POST['parcial'] == '1' ? 1 : 0;;

    try {
        $sql = "INSERT INTO socios
                (nombre, apellido, dni, fecha_inscripcion, fecha_vencimiento, parcial)
                VALUES
                (:n, :a, :dni, :fi, :fv, :parcial)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':n'   => $_POST['nombre'],
            ':a'   => $_POST['apellido'],
            ':dni' => $dni,
            ':fi'  => $inscripcion,
            ':fv'  => $vencimiento,
            ':parcial' => $parcial
        ]);

        $lastId = $conexion->lastInsertId();
        $insStmt = $conexion->prepare("INSERT INTO renovaciones (socio_id, fecha_renovacion) VALUES (:sid, :f)");
        $insStmt->execute([':sid' => $lastId, ':f' => $inscripcion]); // $inscripcion = fecha de hoy 'Y-m-d'
        
        $_SESSION['msg'] = [
        'type' => 'success',
        'text' => 'Socio agregado correctamente.'
    ];

     if ($parcial) {
            header('Location: index.php?parcial=1&id=' . $lastId);
        } else {
            header('Location: index.php');
        }
        exit;


    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            // Redirige con error y datos previos
            header('Location: agregar_socio.php?error=dni&nombre=' . urlencode($_POST['nombre']) . '&apellido=' . urlencode($_POST['apellido']) . '&dni=' . urlencode($dni));
            exit;
        } else {
            error_log('Error insertar_socio: ' . $e->getMessage());
            header('Location: agregar_socio.php?error=otro');
            exit;
        }
    }
} else {
    echo "<p>Faltan datos obligatorios.</p><a href=\"agregar_socio.php\">Volver</a>";
}