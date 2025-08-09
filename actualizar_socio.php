<?php
require_once 'bdd.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (
    !empty($_POST['id']) &&
    !empty($_POST['nombre']) &&
    !empty($_POST['apellido']) &&
    !empty($_POST['dni'])
) {
    // Sanitizar id
    $id = intval($_POST['id']);

    // Validar que DNI sea numérico y largo adecuado
    $dni = $_POST['dni'];
    if (!ctype_digit($dni)) {
        $_SESSION['msg'] = ['type' => 'danger', 'text' => 'El DNI debe contener solo números.'];
        header("Location: editar_socio.php?id={$id}");
        exit;
    }

    try {
        $parcial = isset($_POST['parcial']) && $_POST['parcial'] == '1' ? 1 : 0;

        $sql = "UPDATE socios SET
                    nombre = :n,
                    apellido = :a,
                    dni = :dni,
                    fecha_inscripcion = :fi,
                    fecha_vencimiento = :fv,
                    parcial = :parcial
                WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':n'   => $_POST['nombre'],
            ':a'   => $_POST['apellido'],
            ':dni' => $dni,
            ':fi'  => $_POST['fecha_inscripcion'],
            ':fv'  => $_POST['fecha_vencimiento'],
            ':parcial' => $parcial,
            ':id'  => $id
        ]);

        // Mensaje flash
        $_SESSION['msg'] = ['type' => 'success', 'text' => 'Socio actualizado correctamente.'];
        $parcial = isset($_POST['parcial']) && $_POST['parcial'] == '1';
        if ($parcial) {
            header('Location: index.php?parcial=1&id=' . $id);
        } else {
            header('Location: index.php');
        }
        exit;
    } catch (PDOException $e) {
        // Si es violación de unique (dni duplicado)
        if ($e->getCode() === '23000') {
            $_SESSION['msg'] = ['type' => 'danger', 'text' => "Ya existe un socio con el DNI $dni."];
            header("Location: editar_socio.php?id={$id}");
            exit;
        } else {
            // Loggear error (opcional) y mostrar mensaje genérico
            error_log('Error actualizar_socio: ' . $e->getMessage());
            $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Error al actualizar socio.'];
            header("Location: editar_socio.php?id={$id}");
            exit;
        }
    }
} else {
    $id = isset($_POST['id']) ? intval($_POST['id']) : '';
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Faltan datos obligatorios.'];
    header("Location: editar_socio.php?id={$id}");
    exit;
}
