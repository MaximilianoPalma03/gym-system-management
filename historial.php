<?php
session_start();
require_once 'bdd.php';

// Reutilizamos safeFormatDate si lo tenés, si no agregalo:
function safeFormatDate($fecha) {
    if (empty($fecha)) return '-';
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    if ($d && $d->format('Y-m-d') === $fecha) return $d->format('d/m/Y');
    try { $d2 = new DateTime($fecha); return $d2->format('d/m/Y'); } catch (Exception $e) { return '-'; }
}

// Parámetros: paginación, filtro dni, orden (por última renovación)
$porPagina = 20;
$pagina = isset($_GET['p']) && ctype_digit($_GET['p']) && $_GET['p'] > 0 ? intval($_GET['p']) : 1;
$offset = ($pagina - 1) * $porPagina;

$filtroDni = isset($_GET['dni']) && ctype_digit($_GET['dni']) ? $_GET['dni'] : null;
$orden = isset($_GET['orden']) && $_GET['orden'] === 'recientes'; // si viene ?orden=recientes ordena por último renov

// Construir count
$countSql = "SELECT COUNT(*) FROM socios" . ($filtroDni ? " WHERE dni = :dni" : "");
$countStmt = $conexion->prepare($countSql);
if ($filtroDni) $countStmt->execute([':dni' => $filtroDni]); else $countStmt->execute();
$totalSocios = $countStmt->fetchColumn();
$totalPaginas = max(1, ceil($totalSocios / $porPagina));

// Consulta principal:
// Traemos datos del socio y el historial concatenado (GROUP_CONCAT). ORDER BY depende de $orden.
$sql = "
SELECT
  s.id,
  s.nombre,
  s.apellido,
  s.dni,
  s.fecha_alta,
  MAX(r.fecha_renovacion) AS ultima_renovacion,
  GROUP_CONCAT(DATE_FORMAT(r.fecha_renovacion, '%Y-%m-%d') ORDER BY r.fecha_renovacion DESC SEPARATOR ',') AS historial
FROM socios s
LEFT JOIN renovaciones r ON r.socio_id = s.id
" . ($filtroDni ? " WHERE s.dni = :dni " : "") . "
GROUP BY s.id, s.nombre, s.apellido, s.dni, s.fecha_alta
ORDER BY s.apellido, s.nombre
LIMIT :limit OFFSET :offset
";

$stmt = $conexion->prepare($sql);
if ($filtroDni) $stmt->bindValue(':dni', $filtroDni, PDO::PARAM_STR);
$stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$filas = $stmt->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Historial de Renovaciones</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .small-hist { max-width: 600px; white-space: normal; word-wrap: break-word; }
  </style>
</head>
<body class="bg-light">
<div class="container mt-4">
  <h1 class="mb-4">Historial de Pagos / Renovaciones</h1>

  <div class="d-flex mb-3 gap-2">
    <a href="index.php" class="btn btn-secondary">Volver a Administración</a>

    <?php
      $qs = $_GET;
      if ($orden) { unset($qs['orden']); $label = 'Desordenar'; $btnClass = 'btn-outline-secondary'; }
      else { $qs['orden'] = 'recientes'; $label = 'Ordenar por Renovación reciente'; $btnClass = 'btn-primary'; }
      $toggleUrl = basename(__FILE__) . '?' . http_build_query($qs);
    ?>
    <a href="<?= $toggleUrl ?>" class="btn <?= $btnClass ?>"><?= $label ?></a>

    <form method="get" class="d-flex ms-auto">
      <?php if ($orden): ?><input type="hidden" name="orden" value="recientes"><?php endif; ?>
      <input type="text" name="dni" class="form-control me-2" placeholder="Buscar por DNI" value="<?= htmlspecialchars($filtroDni ?? '') ?>">
      <button class="btn btn-primary">Buscar</button>
    </form>
  </div>

  <table class="table table-bordered table-hover">
    <thead class="table-dark">
      <tr>
        <th>Nombre</th><th>Apellido</th><th>DNI</th><th>Inscripción</th><th>Últ. Renov.</th><th>Historial (fechas)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($filas as $r):
        $hist_raw = $r['historial'] ?? '';
        $hist_items = [];
    if ($hist_raw !== '') {
        foreach (explode(',', $hist_raw) as $p) {
            $p = trim($p);
            if ($p !== '') $hist_items[] = safeFormatDate($p);
        }
    }

    // texto a mostrar en la columna historial (si está vacío, quedará en blanco)
     $hist_text = '';
    if (!empty($hist_items)) {
        $hist_text = implode(', ', $hist_items);
    } else {
        $hist_text = 'Sin renovaciones';
    }
        ?>
      <tr>
        <td><?= htmlspecialchars($r['nombre']) ?></td>
        <td><?= htmlspecialchars($r['apellido']) ?></td>
        <td><?= htmlspecialchars($r['dni']) ?></td>
        <td><?= safeFormatDate($r['fecha_alta'] ?? $r['fecha_inscripcion']) ?></td>
        <td><?= safeFormatDate($r['ultima_renovacion']) ?></td>
        <td class="small-hist"><?= htmlspecialchars($hist_text) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- paginación simple -->
  <nav><ul class="pagination">
    <?php
      $base = $_GET;
      for ($i=1;$i<=$totalPaginas;$i++):
        $base['p'] = $i;
    ?>
      <li class="page-item <?= $i==$pagina ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query($base) ?>"><?= $i ?></a></li>
    <?php endfor; ?>
  </ul></nav>

</div>
</body>
</html>
