<?php
session_start();
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['pass'] === 'gym123') {
        $_SESSION['admin'] = true;
        header('Location: index.php'); exit;
    } else {
        $err = 'Contraseña incorrecta.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login Admin – Bull Gym</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style> body { background: #000; color: #fff; } .form-control, .btn-primary { background: #111; color: #fff; border:1px solid #444; } .btn-primary{background:#FFD700;color:#000;} </style>
</head>
<body>
  <div class="container pt-5">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <h3 class="text-center mb-4">Acceso Administrativo</h3>
        <?php if ($err): ?><div class="alert alert-danger"><?=$err?></div><?php endif; ?>
        <form method="post">
          <input type="password" name="pass" class="form-control mb-3" placeholder="Contraseña" required>
          <button class="btn btn-primary w-100">Ingresar</button>
        </form>
        <div class="mt-2 text-center">
          <a href="registro.php" class="text-light">Volver</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>