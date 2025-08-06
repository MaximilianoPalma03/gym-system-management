<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agregar Vehículo</title> 
  <!--CSS de Bootstrap-->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Carga Bootstrap desde CDN -->
</head>
<body>
  <!--div principal-->
  <div class="container mt-5"> 
    <h2 class="mb-4">Agregar nuevo vehículo</h2>

    <!-- Formulario para enviar los datos del nuevo vehículo -->
    <form action="insertar.php" method="POST">
      
      <!-- Campo: Marca del vehículo -->
      <div class="mb-3">
        <label for="marca" class="form-label">Marca</label>
        <input type="text" name="marca" id="marca" class="form-control" required>
      </div>

      <!-- Campo: Modelo del vehículo -->
      <div class="mb-3">
        <label for="modelo" class="form-label">Modelo</label>
        <input type="text" name="modelo" id="modelo" class="form-control" required>
      </div>

      <!-- Campo: Año de fabricación -->
      <div class="mb-3">
        <label for="anio" class="form-label">Año</label>
        <input type="number" name="anio" id="anio" class="form-control" required>
      </div>

      <!-- Campo: Color del vehículo -->
      <div class="mb-3">
        <label for="color" class="form-label">Color</label>
        <input type="text" name="color" id="color" class="form-control" required>
      </div>

      <!-- Campo: Precio del vehículo -->
      <div class="mb-3">
        <label for="precio" class="form-label">Precio</label>
        <input type="number" step="0.01" name="precio" id="precio" class="form-control" required>
      </div>

      <!-- Campo: Tipo de vehículo -->
      <div class="mb-3">
        <label for="tipo" class="form-label">Tipo</label>
        <input type="text" name="tipo" id="tipo" class="form-control" required>
      </div>

      <!--enviar formulario-->
      <button type="submit" class="btn btn-success">Guardar</button>

      <a href="index.php" class="btn btn-secondary ms-2">Volver</a>
    </form>
  </div>
</body>
</html>
