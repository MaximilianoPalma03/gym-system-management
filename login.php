<?php
session_start();

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminPassword = 'gym123';
    if (isset($_POST['pass']) && $_POST['pass'] === $adminPassword) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        $_SESSION['msg'] = ['type' => 'success', 'text' => 'Acceso administrativo correcto.'];
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Contraseña incorrecta.'];
        header('Location: login.php');
        exit;
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
    .back-link {
      margin-top: 1.2rem;
      text-align: center;
    }
    .back-link a {
      color: #FFD700;
      font-weight: 600;
      text-decoration: none;
      transition: color 0.2s;
    }
    .back-link a:hover {
      color: #fff;
      text-decoration: underline;
    }
    @media (max-width: 600px) {
      .main-card { padding: 1.2rem .5rem 1.5rem .5rem; }
      .logo-gym { width: 90px; height: 90px; }
    }
  </style>
</head>
<body>
  <div class="main-card">
    <img src="logo gym.jpg" alt="Bull Gym Logo" class="logo-gym">
    <h2>Acceso Administrativo</h2>
    <?php if (isset($_SESSION['msg'])): ?>
      <div class="alert alert-<?= $_SESSION['msg']['type'] ?> text-center">
        <?= htmlspecialchars($_SESSION['msg']['text']) ?>
      </div>
      <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>
    <form method="post">
      <input type="password" name="pass" class="form-control" placeholder="Contraseña" required>
      <button class="btn btn-primary w-100">Ingresar</button>
    </form>
    <div class="back-link">
      <a href="registro.php">Volver</a>
    </div>
  </div>
</body>
</html>