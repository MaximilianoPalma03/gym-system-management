<?php
require_once 'bdd.php';

// Trae todos los vehículos
$sql = "SELECT * FROM vehiculos";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Listado de Vehículos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .modo-edicion .btn-warning,
    .modo-edicion .btn-danger {
      display: inline-block !important;
    }
    .btn-warning, .btn-danger {
      display: none;
    }
  </style>
</head>
<body>

<div class="container mt-4">
  <h1 class="mb-4">Listado de Vehículos</h1>

  <div class="d-flex justify-content-between mb-3">
    <a href="agregar.php" class="btn btn-success">Agregar nuevo vehículo</a>
    <button class="btn btn-primary" onclick="document.body.classList.toggle('modo-edicion')">
      Editar tabla
    </button>
  </div>

  <table class="table table-bordered table-hover">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Marca</th>
        <th>Modelo</th>
        <th>Año</th>
        <th>Color</th>
        <th>Precio</th>
        <th>Tipo</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($vehiculos as $vehiculo): ?>
        <tr>
          <td><?= htmlspecialchars($vehiculo['id']) ?></td>
          <td><?= htmlspecialchars($vehiculo['marca']) ?></td>
          <td><?= htmlspecialchars($vehiculo['modelo']) ?></td>
          <td><?= htmlspecialchars($vehiculo['anio']) ?></td>
          <td><?= htmlspecialchars($vehiculo['color']) ?></td>
          <td>$<?= htmlspecialchars($vehiculo['precio']) ?></td>
          <td><?= htmlspecialchars($vehiculo['tipo']) ?></td>
          <td>
            <a href="editar.php?id=<?= $vehiculo['id'] ?>"
               class="btn btn-sm btn-warning">Editar</a>
            <button class="btn btn-sm btn-danger"
                    onclick="confirmarEliminacion(<?= $vehiculo['id'] ?>)">
              Eliminar
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="eliminar.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmar eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        ¿Estás seguro de que deseas eliminar este vehículo?
      </div>
      <div class="modal-footer">
        <input type="hidden" name="id" id="vehiculoAEliminar">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Cancelar
        </button>
        <button type="submit" class="btn btn-danger">Eliminar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function confirmarEliminacion(id) {
    document.getElementById('vehiculoAEliminar').value = id;
    const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
    modal.show();
  }
</script>
</body>
</html>
