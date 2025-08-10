<?php
require_once 'bdd.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Asegúrate de tener un token en sesión
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Recibimos por GET el id (esta página muestra el formulario de confirmación)
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);
$stmt = $conexion->prepare("SELECT nombre, apellido FROM socios WHERE id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();

if (!$s) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Confirmar Eliminación</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h3>Eliminar Socio</h3>
  <p><strong><?= htmlspecialchars($s['nombre'].' '.$s['apellido']) ?></strong></p>

  <!-- FORM que envía el POST a eliminar_socio.php -->
  <form action="eliminar_socio.php" method="POST" class="d-inline">
    <input type="hidden" name="id" value="<?= $id ?>">
    <!-- token CSRF incluido aquí -->
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
    <button class="btn btn-danger">Sí, eliminar</button>
  </form>

  <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
</div>
</body>
</html>
