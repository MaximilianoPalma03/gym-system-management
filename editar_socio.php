<?php
require_once 'bdd.php';
if (!isset($_GET['id'])) { header('Location:index.php'); exit; }
$id = intval($_GET['id']);
$stmt = $conexion->prepare("SELECT * FROM socios WHERE id=?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { header('Location:index.php'); exit; }

// Obtener fechas para el formulario
$fecha_inscripcion = isset($_GET['fecha_inscripcion']) ? $_GET['fecha_inscripcion'] : $s['fecha_inscripcion'];
$fecha_vencimiento = isset($_GET['fecha_vencimiento']) ? $_GET['fecha_vencimiento'] : $s['fecha_vencimiento'];

// Switch parcial (solo visual, no se guarda)
$parcial = isset($_GET['parcial']) ? $_GET['parcial'] == '1' : ($s['parcial'] == 1);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Socio</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2>Editar Socio</h2>
  <form action="actualizar_socio.php" method="POST">
    <input type="hidden" name="id" value="<?=$s['id']?>">
    <div class="mb-3">
      <label class="form-label">Nombre</label>
      <input type="text" name="nombre" class="form-control" value="<?=htmlspecialchars($s['nombre'])?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Apellido</label>
      <input type="text" name="apellido" class="form-control" value="<?=htmlspecialchars($s['apellido'])?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">DNI</label>
      <input type="text" name="dni" class="form-control" value="<?=htmlspecialchars($s['dni'])?>" required>
    </div>
     <div class="mb-3">
      <label class="form-label">Fecha de inscripción</label>
      <input type="date" name="fecha_inscripcion" class="form-control" required value="<?=htmlspecialchars($fecha_inscripcion)?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Fecha de vencimiento</label>
      <input type="date" name="fecha_vencimiento" class="form-control" required value="<?=htmlspecialchars($fecha_vencimiento)?>">
    </div>
    <div class="form-check form-switch mb-4">
      <input class="form-check-input" type="checkbox" id="parcial" name="parcial" value="1" <?= $parcial ? 'checked' : '' ?>>
      <label class="form-check-label" for="parcial">Parcial</label>
    </div>
    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
  </form>
</div>
</body>
</html>