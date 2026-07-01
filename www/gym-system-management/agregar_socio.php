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
  
  <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <?php if ($error === 'debug' && !empty($_GET['msg'])): ?>
    <div class="alert alert-danger">Error al insertar: <?= htmlspecialchars(urldecode($_GET['msg'])) ?></div>
  <?php endif; ?>
  <h2>Agregar Nuevo Socio</h2>
  <form action="insertar_socio.php" method="POST">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre</label>
      <input type="text" name="nombre" id="nombre" class="form-control" style="text-transform: uppercase;" required>
    </div>
    <div class="mb-3">
      <label for="apellido" class="form-label">Apellido</label>
      <input type="text" name="apellido" id="apellido" class="form-control" style="text-transform: uppercase;" required>
    </div>
    <div class="mb-3">
      <label for="dni" class="form-label">DNI</label>
      <input type="text" name="dni" id="dni" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="fecha_inscripcion_display" class="form-label">Fecha de inscripción</label>
      <input type="hidden" name="fecha_inscripcion" id="fecha_inscripcion" value="<?= htmlspecialchars($fecha_inscripcion) ?>">
      <input type="text" id="fecha_inscripcion_display" class="form-control" required value="<?= htmlspecialchars((new DateTime($fecha_inscripcion))->format('d/m/Y')) ?>">
      <div class="form-text">Formato: dd/mm/yyyy</div>
    </div>
    <div class="mb-3">
      <label for="fecha_vencimiento_display" class="form-label">Fecha de vencimiento</label>
      <input type="hidden" name="fecha_vencimiento" id="fecha_vencimiento" value="<?= htmlspecialchars($fecha_vencimiento) ?>">
      <input type="text" id="fecha_vencimiento_display" class="form-control" required value="<?= htmlspecialchars((new DateTime($fecha_vencimiento))->format('d/m/Y')) ?>">
      <div class="form-text">Formato: dd/mm/yyyy</div>
    </div>
    <div class="form-check form-switch mb-4">
      <input class="form-check-input" type="checkbox" id="parcial" name="parcial" value="1">
      <label class="form-check-label" for="parcial">Parcial</label>
    </div>
    <button type="submit" class="btn btn-success">Guardar</button>
    <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
  </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  function ymdToDmy(ymd) {
    try {
      const parts = ymd.split('-');
      if (parts.length !== 3) return '';
      return [parts[2], parts[1], parts[0]].join('/');
    } catch (e) { return ''; }
  }
  function dmyToYmd(dmy) {
    const parts = dmy.split('/');
    if (parts.length !== 3) return '';
    const d = parts[0].padStart(2,'0');
    const m = parts[1].padStart(2,'0');
    const y = parts[2];
    return [y, m, d].join('-');
  }

  const insHidden = document.getElementById('fecha_inscripcion');
  const insDisplay = document.getElementById('fecha_inscripcion_display');
  const venHidden = document.getElementById('fecha_vencimiento');
  const venDisplay = document.getElementById('fecha_vencimiento_display');

  // Ensure displays reflect hidden initial values (in case PHP formatting changed)
  if (insHidden && insDisplay) {
    insDisplay.value = ymdToDmy(insHidden.value) || insDisplay.value;
  }
  if (venHidden && venDisplay) {
    venDisplay.value = ymdToDmy(venHidden.value) || venDisplay.value;
  }

  // On display change, sync to hidden in Y-m-d
  function attachSync(displayEl, hiddenEl) {
    displayEl.addEventListener('blur', function () {
      const ymd = dmyToYmd(this.value);
      if (ymd) hiddenEl.value = ymd;
    });
    // Also try on input for live feedback
    displayEl.addEventListener('input', function () {
      const ymd = dmyToYmd(this.value);
      if (ymd) hiddenEl.value = ymd;
    });
  }

  if (insDisplay && insHidden) attachSync(insDisplay, insHidden);
  if (venDisplay && venHidden) attachSync(venDisplay, venHidden);

  // Before submit, validate date format and ensure hidden fields are set
  const form = document.querySelector('form[action="insertar_socio.php"]');
  if (form) {
    form.addEventListener('submit', function (e) {
      // simple validation: both hidden values must be yyyy-mm-dd
      if (!insHidden.value || !/^\d{4}-\d{2}-\d{2}$/.test(insHidden.value) || !venHidden.value || !/^\d{4}-\d{2}-\d{2}$/.test(venHidden.value)) {
        e.preventDefault();
        alert('Las fechas deben ingresarse en formato dd/mm/yyyy (ej: 13/06/2026).');
      }
    });
  }

  const nombreInput = document.getElementById('nombre');
  const apellidoInput = document.getElementById('apellido');
  function forceUpperCase(field) {
    if (!field) return;
    field.addEventListener('input', function () {
      const pos = this.selectionStart;
      this.value = this.value.toUpperCase();
      this.setSelectionRange(pos, pos);
    });
  }

  forceUpperCase(nombreInput);
  forceUpperCase(apellidoInput);
});
</script>
</html>