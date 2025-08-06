<?php
require_once 'bdd.php';

if (!isset($_GET['id'])) {
    echo "ID no válido";
    exit;
}

$id = $_GET['id'];

// Obtener datos actuales
$sql = "SELECT * FROM vehiculos WHERE id = :id";
$stmt = $conexion->prepare($sql);
$stmt->execute([':id' => $id]);
$vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehiculo) {
    echo "Vehículo no encontrado.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editar Vehículo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Editar vehículo</h2>
    <form action="actualizar.php" method="POST">
        <input type="hidden" name="id" value="<?= $vehiculo['id'] ?>">
        <div class="mb-3">
            <label>Marca</label>
            <input type="text" name="marca" class="form-control" value="<?= $vehiculo['marca'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Modelo</label>
            <input type="text" name="modelo" class="form-control" value="<?= $vehiculo['modelo'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Año</label>
            <input type="number" name="anio" class="form-control" value="<?= $vehiculo['anio'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Color</label>
            <input type="text" name="color" class="form-control" value="<?= $vehiculo['color'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Precio</label>
            <input type="number" step="0.01" name="precio" class="form-control" value="<?= $vehiculo['precio'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Tipo</label>
            <input type="text" name="tipo" class="form-control" value="<?= $vehiculo['tipo'] ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>
