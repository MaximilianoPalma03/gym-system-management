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
  <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2>Editar Socio</h2>
  <form action="actualizar_socio.php" method="POST">
    <input type="hidden" name="id" value="<?=$s['id']?>">
    <div class="mb-3">
      <label class="form-label">Nombre</label>
      <input type="text" name="nombre" id="nombre" class="form-control" style="text-transform: uppercase;" value="<?=htmlspecialchars($s['nombre'])?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Apellido</label>
      <input type="text" name="apellido" id="apellido" class="form-control" style="text-transform: uppercase;" value="<?=htmlspecialchars($s['apellido'])?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">DNI</label>
      <input type="text" name="dni" class="form-control" value="<?=htmlspecialchars($s['dni'])?>" required>
    </div>
     <div class="mb-3">
      <label class="form-label">Fecha de inscripción</label>
      <input type="hidden" name="fecha_inscripcion" id="fecha_inscripcion" value="<?=htmlspecialchars($fecha_inscripcion)?>">
      <input type="text" id="fecha_inscripcion_display" class="form-control" required value="<?=htmlspecialchars((new DateTime($fecha_inscripcion))->format('d/m/Y'))?>">
      <div class="form-text">Formato: dd/mm/yyyy</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Fecha de vencimiento</label>
      <input type="hidden" name="fecha_vencimiento" id="fecha_vencimiento" value="<?=htmlspecialchars($fecha_vencimiento)?>">
      <input type="text" id="fecha_vencimiento_display" class="form-control" required value="<?=htmlspecialchars((new DateTime($fecha_vencimiento))->format('d/m/Y'))?>">
      <div class="form-text">Formato: dd/mm/yyyy</div>
    </div>
    <div class="form-check form-switch mb-4">
      <input class="form-check-input" type="checkbox" id="parcial" name="parcial" value="1" <?= $parcial ? 'checked' : '' ?>>
      <label class="form-check-label" for="parcial">Parcial</label>
    </div>
    <button type="submit" class="btn btn-primary">Actualizar</button>
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

  if (insHidden && insDisplay) insDisplay.value = ymdToDmy(insHidden.value) || insDisplay.value;
  if (venHidden && venDisplay) venDisplay.value = ymdToDmy(venHidden.value) || venDisplay.value;

  function attachSync(displayEl, hiddenEl) {
    displayEl.addEventListener('blur', function () {
      const ymd = dmyToYmd(this.value);
      if (ymd) hiddenEl.value = ymd;
    });
    displayEl.addEventListener('input', function () {
      const ymd = dmyToYmd(this.value);
      if (ymd) hiddenEl.value = ymd;
    });
  }

  if (insDisplay && insHidden) attachSync(insDisplay, insHidden);
  if (venDisplay && venHidden) attachSync(venDisplay, venHidden);

  const form = document.querySelector('form[action="actualizar_socio.php"]');
  if (form) {
    form.addEventListener('submit', function (e) {
      if (!insHidden.value || !/^\d{4}-\d{2}-\d{2}$/.test(insHidden.value) || !venHidden.value || !/^\d{4}-\d{2}-\d{2}$/.test(venHidden.value)) {
        e.preventDefault();
        alert('Las fechas deben ingresarse en formato dd/mm/yyyy (ej: 13/06/2026).');
      }
    });
  }

  const nombreInput = document.querySelector('input[name="nombre"]');
  const apellidoInput = document.querySelector('input[name="apellido"]');
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