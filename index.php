<?php
session_start();
require_once 'bdd.php';

if (empty($_SESSION['csrf'])) {
    // random_bytes requiere PHP 7+. Genera 64 hex chars.
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

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
    : "ID DESC";

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

   <style>
    .logo-gym {
      width: 110px;
      height: 110px;
      object-fit: contain;
      display: block;
      margin: 0 auto 1.2rem auto;
      filter: drop-shadow(0 2px 12px #FFD70055);
      background: #fff;
      border-radius: 50%;
      border: 3px solid #FFD700;
      padding: 8px;
    }
    @media (max-width: 600px) {
      .logo-gym { width: 80px; height: 80px; }
    }

    .selected-row {
  background-color: #ffe0e0 !important;
  }
  
  .fab {
  position: fixed;
  right: 20px;
  bottom: 24px;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #FFD700;
  color: #000;
  display:flex;
  align-items:center;
  justify-content:center;
  box-shadow: 0 6px 18px rgba(0,0,0,0.2);
  z-index: 9999;
  text-decoration:none;
  font-size: 24px;
}
.fab:hover { transform: translateY(-2px); }

  </style>

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
<img src="logo-gym.png" alt="Bull Gym Logo" class="logo-gym">

  <h1 class="mb-4 text-center">Socios del Gimnasio</h1>

  <a href="historial.php" class="fab" title="Historial de renovaciones" aria-label="Historial de renovaciones">⏱</a>

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

    <!-- FORM que contiene el botón Eliminar seleccionados (NO envuelve la tabla) -->
 <form id="multi-delete-form" action="confirmar_eliminar_multiple.php" method="POST" style="margin-left:8px;">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">
  <button type="submit" class="btn btn-danger" id="delete-selected" style="display:none;">
    Eliminar seleccionados
  </button>
</form>
    
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
        <th><input type="checkbox" id="select-all"></th>
        <th>Nombre</th><th>Apellido</th><th>DNI</th>
        <th>Inscripción</th><th>Vencimiento</th><th>Días Rest.</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($socios as $s): 
    $esParcial = isset($_GET['parcial'], $_GET['id']) && $_GET['parcial'] == 1 && $_GET['id'] == $s['id'];
      $clase = ($s['dias_restantes'] < 0) ? 'table-danger' : '';
    if ($s['parcial'] == 1) $clase = 'table-primary';
?>
<tr<?= $clase ? ' class="'.$clase.'"' : '' ?>>
     <td><input type="checkbox" class="row-checkbox" value="<?= intval($s['id']) ?>"
      aria-label="Seleccionar socio <?= htmlspecialchars($s['nombre'].' '.$s['apellido']) ?>"></td>    
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
      <!-- FORM POST para RENOVAR (confirmación + token CSRF) -->
    <form action="renovar_cuota.php" method="POST" style="display:inline;">
      <input type="hidden" name="id" value="<?= $s['id'] ?>">
      <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
      <button type="submit" class="btn btn-warning btn-sm">Renovar cuota</button>
    </form>

      <!-- EDITAR -->
      <a href="editar_socio.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-warning">Editar</a>

      <!-- ELIMINAR -->
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
document.addEventListener('DOMContentLoaded', function() {
  const selectAll = document.getElementById('select-all');
  const deleteBtn = document.getElementById('delete-selected');
  const bulkForm = document.getElementById('multi-delete-form');

  function updateDeleteButtonVisibility() {
    const anyChecked = Array.from(document.querySelectorAll('.row-checkbox')).some(cb => cb.checked);
    if (deleteBtn) deleteBtn.style.display = anyChecked ? 'inline-block' : 'none';
  }

  // select-all
  if (selectAll) {
    selectAll.addEventListener('change', function() {
      const checked = this.checked;
      document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.checked = checked;
        cb.closest('tr').classList.toggle('selected-row', checked);
      });
      updateDeleteButtonVisibility();
    });
  }

  // cada checkbox de fila
  document.querySelectorAll('.row-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
      this.closest('tr').classList.toggle('selected-row', this.checked);
      // mantener selectAll en sync
      const all = Array.from(document.querySelectorAll('.row-checkbox'));
      if (selectAll) selectAll.checked = all.length > 0 && all.every(c => c.checked);
      updateDeleteButtonVisibility();
    });
  });

  // Antes de enviar: crear inputs ocultos name="ids[]"
  if (bulkForm) {
    bulkForm.addEventListener('submit', function(e) {
      // eliminar inputs previos si existen
      bulkForm.querySelectorAll('input[name="ids[]"]').forEach(n => n.remove());

      // recolectar ids seleccionados
      const checkedBoxes = Array.from(document.querySelectorAll('.row-checkbox')).filter(cb => cb.checked);
      if (checkedBoxes.length === 0) {
        e.preventDefault();
        alert('No seleccionaste ningún socio.');
        return;
      }

      // Opcional: si preferís preguntar con pop-up (no recomendado por supresión), descomenta:
      // if (!confirm('¿Eliminar los socios seleccionados?')) { e.preventDefault(); return; }

      // Añadir inputs ocultos
      checkedBoxes.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        bulkForm.appendChild(input);
      });

      // Si tu flow usa confirm en servidor: el form va a confirmar allí.
      // Si envías directo a eliminar_multiple.php, este recibirá $_POST['ids'] correctamente.
    });
  }

  // estado inicial
  updateDeleteButtonVisibility();
});


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