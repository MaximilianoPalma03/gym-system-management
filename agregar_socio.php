<?php
$nombre = $_GET['nombre'] ?? '';
$apellido = $_GET['apellido'] ?? '';
$dni = $_GET['dni'] ?? '';
$error = $_GET['error'] ?? '';

$hoy = date('Y-m-d');
$venc = date('Y-m-d', strtotime('+1 month'));
$fecha_inscripcion = $_GET['fecha_inscripcion'] ?? $hoy;
$fecha_vencimiento = $_GET['fecha_vencimiento'] ?? $venc;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agregar Socio</title>
  <?php if ($error === 'dni'): ?>
    <div class="alert alert-danger">El DNI ingresado ya está registrado. Por favor, ingrese uno diferente.</div>
  <?php elseif ($error === 'otro'): ?>
    <div class="alert alert-danger">Ocurrió un error al agregar el socio. Intente nuevamente.</div>
  <?php endif; ?>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2>Agregar Nuevo Socio</h2>
  <form action="insertar_socio.php" method="POST">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre</label>
      <input type="text" name="nombre" id="nombre" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="apellido" class="form-label">Apellido</label>
      <input type="text" name="apellido" id="apellido" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="dni" class="form-label">DNI</label>
      <input type="text" name="dni" id="dni" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="fecha_inscripcion" class="form-label">Fecha de inscripción</label>
      <input type="date" name="fecha_inscripcion" id="fecha_inscripcion" class="form-control" required value="<?= htmlspecialchars($fecha_inscripcion) ?>">
    </div>
    <div class="mb-3">
      <label for="fecha_vencimiento" class="form-label">Fecha de vencimiento</label>
      <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control" required value="<?= htmlspecialchars($fecha_vencimiento) ?>">
    </div>
    <div class="form-check form-switch mb-4">
      <input class="form-check-input" type="checkbox" id="parcial" name="parcial" value="1">
      <label class="form-check-label" for="parcial">Parcial</label>
    </div>
    <button type="submit" class="btn btn-success">Guardar</button>
    <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
  </form>
</div>
</body>
</html>