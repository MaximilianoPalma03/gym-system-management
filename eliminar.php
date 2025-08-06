<?php
require_once 'bdd.php';

// Solo procesamos POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id = intval($_POST['id']);
    $sql = "DELETE FROM vehiculos WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id]);
}

// Redirigimos siempre de vuelta
header('Location: index.php');
exit;
