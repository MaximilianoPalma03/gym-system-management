<?php
session_start();
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

// Mensaje flash (si existe)
$msg = $_SESSION['msg'] ?? null;
unset($_SESSION['msg']);


// Parámetros de paginación
$porPagina = 20;
$pagina = isset($_GET['p']) && ctype_digit($_GET['p']) && $_GET['p'] > 0
    ? intval($_GET['p'])
    : 1;
$offset = ($pagina - 1) * $porPagina;

// Base de parámetros para mantener filtro y orden al paginar
$baseParams = $_GET;

// Parámetros de filtro y orden
$filtroDni = isset($_GET['dni']) && ctype_digit($_GET['dni']) ? $_GET['dni'] : null;
$ordenDias = isset($_GET['orden']) && $_GET['orden'] === 'dias';

// Preparar cláusula ORDER BY
$orderClause = $ordenDias
    ? "dias_restantes ASC, apellido, nombre"
    : "apellido, nombre";

// 1) Contar total de socios
$countSql = "SELECT COUNT(*) FROM socios";
$countParams = [];
if ($filtroDni) {
    $countSql .= " WHERE dni = :dni";
    $countParams[':dni'] = $filtroDni;
}
$countStmt = $conexion->prepare($countSql);
$countStmt->execute($countParams);
$totalSocios = $countStmt->fetchColumn();
$totalPaginas = max(1, ceil($totalSocios / $porPagina));

// 2) Traer datos de esta página
$sql = "
  SELECT *,
    DATEDIFF(fecha_vencimiento, CURRENT_DATE()) AS dias_restantes
  FROM socios
" . ($filtroDni ? " WHERE dni = :dni" : "") . "
  ORDER BY $orderClause
  LIMIT :limit OFFSET :offset
";
$stmt = $conexion->prepare($sql);
if ($filtroDni) {
    $stmt->bindValue(':dni', $filtroDni, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$socios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Socios del Gimnasio</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
   <!-- Mensaje flash -->
  <?php if ($msg): ?>
    <div class="alert alert-<?= htmlspecialchars($msg['type']) ?> alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($msg['text']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>
  <h1 class="mb-4">Socios del Gimnasio</h1>

  <div class="d-flex mb-3 gap-2">
    <a href="agregar_socio.php" class="btn btn-success">Agregar Socio</a>

    <!-- NUEVO: Botón a sección de registro -->
    <a href="registro.php" class="btn btn-warning">Registro de Socio</a>
    <!-- Toggle orden por días -->
    <?php
      // Construir URL de toggle sin mutar baseParams
      $toggleParams = $baseParams;
      if ($ordenDias) {
          unset($toggleParams['orden']);
          $label = 'Desordenar';
          $btnClass = 'btn-outline-secondary';
      } else {
          $toggleParams['orden'] = 'dias';
          $label = 'Ordenar por Vencimiento';
          $btnClass = 'btn-primary';
      }
      $toggleUrl = basename(__FILE__) . '?' . http_build_query($toggleParams);
    ?>
    <a href="<?= $toggleUrl ?>" class="btn <?= $btnClass ?>"><?= $label ?></a>

    <!-- Búsqueda por DNI -->
    <form method="get" class="d-flex ms-auto">
      <?php if ($ordenDias): ?>
        <input type="hidden" name="orden" value="dias">
      <?php endif; ?>
      <input
        type="text"
        name="dni"
        class="form-control me-2"
        placeholder="Buscar por DNI"
        value="<?= htmlspecialchars($filtroDni ?? '') ?>"
      >
      <button type="submit" class="btn btn-primary">Buscar</button>
      <?php if ($filtroDni): ?>
        <a href="index.php<?= $ordenDias ? '?orden=dias' : '' ?>" class="btn btn-outline-secondary ms-2">Limpiar</a>
      <?php endif; ?>
    </form>
  </div>

  <table class="table table-bordered table-hover">
    <thead class="table-dark">
      <tr>
        <th>Nombre</th><th>Apellido</th><th>DNI</th>
        <th>Inscripción</th><th>Vencimiento</th><th>Días Rest.</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($socios as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['nombre']) ?></td>
        <td><?= htmlspecialchars($s['apellido']) ?></td>
        <td><?= htmlspecialchars($s['dni']) ?></td>
        <td><?= safeFormatDate($s['fecha_inscripcion']) ?></td>
        <td><?= safeFormatDate($s['fecha_vencimiento']) ?></td>
        <td>
          <?= $s['dias_restantes'] >= 0
                ? $s['dias_restantes'] . ' días'
                : 'Venció hace ' . abs($s['dias_restantes']) . ' días' ?>
        </td>
        <td>
          <a href="editar_socio.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
          <a href="confirmar_eliminar.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-danger">Eliminar</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Paginación -->
  <nav aria-label="Paginación">
    <ul class="pagination justify-content-center">
      <?php
        // Prefila parámetros para 'Anterior'
        $prevParams = $baseParams;
        $prevParams['p'] = max(1, $pagina - 1);
      ?>
      <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="?<?= http_build_query($prevParams) ?>">&laquo; Anterior</a>
      </li>
      <?php for ($i = 1; $i <= $totalPaginas; $i++):
        $pageParams = $baseParams;
        $pageParams['p'] = $i;
      ?>
      <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
        <a class="page-link" href="?<?= http_build_query($pageParams) ?>"><?= $i ?></a>
      </li>
      <?php endfor; ?>
      <?php
        // Prefila parámetros para 'Siguiente'
        $nextParams = $baseParams;
        $nextParams['p'] = min($totalPaginas, $pagina + 1);
      ?>
      <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
        <a class="page-link" href="?<?= http_build_query($nextParams) ?>">Siguiente &raquo;</a>
      </li>
    </ul>
  </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Ocultar automáticamente la alerta tras 5 segundos
  (function() {
    const alertEl = document.querySelector('.alert');
    if (!alertEl) return;
    setTimeout(() => {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
      bsAlert.close();
    }, 5000);
  })();
</script>

</body>
</html>