<?php
require_once 'bdd.php';

function safeFormatDate($fecha) {
    if (empty($fecha)) return '-';
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    if ($d && $d->format('Y-m-d') === $fecha) {
        return $d->format('d/m/Y');
    }
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
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;400&display=swap" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #181818 0%, #232526 100%);
      font-family: 'Montserrat', Arial, sans-serif;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .main-card {
      background: rgba(20,20,20,0.98);
      border-radius: 1.2rem;
      box-shadow: 0 8px 32px 0 rgba(0,0,0,0.45);
      border: 2px solid #FFD700;
      padding: 2.5rem 2rem 2rem 2rem;
      max-width: 420px;
      width: 100%;
      margin: 2rem auto;
      position: relative;
      z-index: 2;
    }
    .logo-gym {
      width: 120px;
      height: 120px;
      object-fit: contain;
      margin-bottom: 1.2rem;
      display: block;
      margin-left: auto;
      margin-right: auto;
      filter: drop-shadow(0 2px 12px #FFD70055);
      background: #fff;
      border-radius: 50%;
      border: 3px solid #FFD700;
      padding: 8px;
    }
    h2 {
      font-weight: 700;
      letter-spacing: 1px;
      color: #FFD700;
      margin-bottom: 1.5rem;
      text-shadow: 0 2px 8px #000a;
      text-align: center;
    }
    .info-box {
      background: rgba(255,255,255,0.07);
      border: 1.5px solid #FFD700;
      border-radius: .7rem;
      padding: 1.5rem 1.2rem;
      margin-bottom: 1.5rem;
      color: #fff;
      font-size: 1.08rem;
      box-shadow: 0 2px 12px #0004;
      text-align: left;
    }
    .info-box p {
      margin-bottom: .5rem;
      font-weight: 500;
      letter-spacing: .2px;
    }
    .info-box strong {
      color: #FFD700;
      font-weight: 700;
    }
    .form-control {
      background: #111214;
      color: #FFD700;
      border: 1.5px solid #FFD700;
      border-radius: .5rem;
      font-size: 1.1rem;
      padding: .8rem 1rem;
      margin-bottom: 1.2rem;
      transition: border-color 0.2s;
    }
    .form-control::placeholder {
      color: #FFD700cc;
     opacity: 1;
     font-weight: 500;
      letter-spacing: .5px;
  }
    .form-control:focus {
      border-color: #fff;
      box-shadow: 0 0 0 2px #FFD70055;
      background: #232526;
      color: #FFD700;
  }
    .btn-primary {
      background: linear-gradient(90deg, #FFD700 60%, #fff700 100%);
      border: none;
      color: #181818;
      font-weight: 700;
      font-size: 1.1rem;
      border-radius: .5rem;
      padding: .8rem 0;
      box-shadow: 0 2px 8px #FFD70033;
      transition: background 0.2s, color 0.2s;
    }
    .btn-primary:hover, .btn-primary:focus {
      background: #fff700;
      color: #000;
    }
    .admin-link {
      position: absolute;
      top: 1.2rem;
      right: 1.2rem;
      z-index: 3;
    }
    .admin-link .btn {
      font-size: .98rem;
      padding: .4rem 1.1rem;
      border-radius: .4rem;
      border: 1.5px solid #FFD700;
      background: #232526;
      color: #FFD700;
      font-weight: 600;
      transition: background 0.2s, color 0.2s;
    }
    .admin-link .btn:hover {
      background: #FFD700;
      color: #181818;
    }
    @media (max-width: 600px) {
      .main-card { padding: 1.2rem .5rem 1.5rem .5rem; }
      .logo-gym { width: 90px; height: 90px; }
    }
  </style>
  <?php if ($result): ?>
  <script>
    setTimeout(() => location.href = 'registro.php', 8000);
  </script>
  <?php endif; ?>
</head>
<body>
  <div class="main-card">
    <div class="admin-link">
      <a href="login.php" class="btn">Admin</a>
    </div>
    <img src="logo gym.jpg" alt="Bull Gym Logo" class="logo-gym">
    <h2>Consulta de Socio</h2>
    <?php if ($result): ?>
      <div class="info-box">
        <p><strong>Nombre:</strong> <?=htmlspecialchars($result['nombre'])?></p>
        <p><strong>Apellido:</strong> <?=htmlspecialchars($result['apellido'])?></p>
        <p><strong>DNI:</strong> <?=htmlspecialchars($result['dni'])?></p>
        <p><strong>Inscripción:</strong> <?= safeFormatDate($result['fecha_inscripcion']) ?></p>
        <p><strong>Vencimiento:</strong> <?= safeFormatDate($result['fecha_vencimiento']) ?></p>
        <p><strong>Días restantes:</strong> <?= $result['dias_restantes'] ?> días</p>
      </div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
      <input type="text" name="dni" class="form-control" placeholder="Ingrese DNI" required maxlength="12" pattern="\d+">
      <button type="submit" class="btn btn-primary w-100">Consultar</button>
    </form>
  </div>
</body>
</html>