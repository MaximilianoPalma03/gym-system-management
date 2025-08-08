<?php
require_once 'bdd.php';

function safeFormatDate($fecha) {
    if (empty($fecha)) return '-';
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    // Validar que parseó bien
    if ($d && $d->format('Y-m-d') === $fecha) {
        return $d->format('d/m/Y');
    }
    // fallback
    try {
        $d2 = new DateTime($fecha);
        return $d2->format('d/m/Y');
    } catch (Exception $e) {
        return '-';
    }
}

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = $_POST['dni'] ?? '';
    if (ctype_digit($dni)) {
        $stmt = $conexion->prepare("SELECT nombre,apellido,dni,fecha_inscripcion,fecha_vencimiento,
            DATEDIFF(fecha_vencimiento,CURRENT_DATE()) AS dias_restantes
            FROM socios WHERE dni = :dni");
        $stmt->execute([':dni' => $dni]);
        $result = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Registro – Bull Gym</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #000; color: #fff; }
    .form-control, .btn-primary { background: #111; color: #fff; border: 1px solid #444; }
    .btn-primary { background: #FFD700; border-color: #FFD700; color: #000; }
    .info-box { background: rgba(255,255,255,0.1); padding: 2rem; border-radius: .5rem; }
  </style>
  <?php if ($result): ?>
  <script>
    // tras mostrar 8 segundos, recarga la página
    setTimeout(() => location.href = 'registro.php', 8000);
  </script>
  <?php endif; ?>
</head>
<body>
  <div class="container pt-5">
    <div class="row justify-content-center">
      <div class="col-md-6 text-center">

       <!-- Acceso admin -->
        <div class="text-end mb-4">
          <a href="login.php" class="btn btn-outline-light">Acceder a Administración</a>
        </div>

        <h2 class="mb-4">Consulta de Socio</h2>
        <?php if ($result): ?>
          <div class="info-box mb-4">
            <p><strong>Nombre:</strong> <?=htmlspecialchars($result['nombre'])?></p>
            <p><strong>Apellido:</strong> <?=htmlspecialchars($result['apellido'])?></p>
            <p><strong>DNI:</strong> <?=htmlspecialchars($result['dni'])?></p>
            <p><strong>Inscripción:</strong> <?= safeFormatDate($result['fecha_inscripcion']) ?></p>
            <p><strong>Vencimiento:</strong> <?= safeFormatDate($result['fecha_vencimiento']) ?></p>
            <p><strong>Días restantes:</strong> <?= $result['dias_restantes'] ?> días</p>
          </div>
        <?php endif; ?>
        <form method="post">
          <input type="text" name="dni" class="form-control mb-3" placeholder="Ingrese DNI" required>
          <button type="submit" class="btn btn-primary w-100">Consultar</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>