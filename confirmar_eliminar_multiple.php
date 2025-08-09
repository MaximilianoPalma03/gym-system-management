<?php
// confirmar_eliminar_multiple.php
require_once 'bdd.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Solo aceptar POST (viene del form en index.php)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Validar CSRF básico
$csrf_post = $_POST['csrf'] ?? '';
$csrf_sess = $_SESSION['csrf'] ?? '';
if (empty($csrf_post) || !hash_equals($csrf_sess, $csrf_post)) {
    $_SESSION['msg'] = ['type'=>'danger','text'=>'Token inválido.'];
    header('Location: index.php');
    exit;
}

// Obtener ids del POST (pueden venir como array)
$ids = $_POST['ids'] ?? [];
if (!is_array($ids) || count($ids) === 0) {
    $_SESSION['msg'] = ['type'=>'warning','text'=>'No se seleccionaron socios.'];
    header('Location: index.php');
    exit;
}

// Filtrar por seguridad
$ids = array_values(array_filter($ids, function($v){ return ctype_digit(strval($v)); }));
if (count($ids) === 0) {
    $_SESSION['msg'] = ['type'=>'warning','text'=>'IDs inválidos.'];
    header('Location: index.php');
    exit;
}

// Si llegamos acá, mostramos la pantalla de confirmación.
// (No listamos nombres para no sobrecargar; podés hacerlo si querés.)
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Confirmar eliminación múltiple</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h3>Eliminar socios seleccionados</h3>
    <p>¿Estás seguro que deseas eliminar los socios seleccionados? Esta acción no se puede deshacer.</p>

    <!-- Form que confirma la eliminación y envía los mismos ids a eliminar_multiple.php -->
    <form action="eliminar_multiples.php" method="POST" class="d-inline">
      <?php foreach ($ids as $id): ?>
        <input type="hidden" name="ids[]" value="<?= htmlspecialchars($id) ?>">
      <?php endforeach; ?>
      <!-- reenviamos el CSRF también -->
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf_post) ?>">
      <button type="submit" class="btn btn-danger">Sí, eliminar</button>
    </form>

    <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
  </div>
</body>
</html>
