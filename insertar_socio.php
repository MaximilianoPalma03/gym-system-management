<?php
session_start();
require_once 'bdd.php';

if (
    !empty($_POST['nombre']) &&
    !empty($_POST['apellido']) &&
    !empty($_POST['dni'])
) {
    // Validar que DNI sea numérico
    $dni = $_POST['dni'];
    if (!ctype_digit($dni)) {
        die("<p>El DNI debe contener solo números.</p><a href=\"agregar_socio.php\">Volver</a>");
    }

    // Fecha de inscripción = hoy, inmutable
    $hoy = new DateTimeImmutable();
    $inscripcion = $hoy->format('Y-m-d');
    // Fecha de vencimiento = +1 mes
    $vencimiento = $hoy->modify('+1 month')->format('Y-m-d');

    try {
        $sql = "INSERT INTO socios
                (nombre, apellido, dni, fecha_inscripcion, fecha_vencimiento)
                VALUES
                (:n, :a, :dni, :fi, :fv)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':n'   => $_POST['nombre'],
            ':a'   => $_POST['apellido'],
            ':dni' => $dni,
            ':fi'  => $inscripcion,
            ':fv'  => $vencimiento
        ]);

        $_SESSION['msg'] = [
        'type' => 'success',
        'text' => 'Socio agregado correctamente.'
    ];

        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        $_SESSION['msg'] = ['type' => 'danger', 'text' => "Ya existe un socio con el DNI $dni."];
        header('Location: agregar_socio.php');
        exit;
    } else {
        error_log('Error insertar_socio: ' . $e->getMessage());
        $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Error al insertar socio.'];
        header('Location: agregar_socio.php');
        exit;
    }
}
} else {
    echo "<p>Faltan datos obligatorios.</p><a href=\"agregar_socio.php\">Volver</a>";
}