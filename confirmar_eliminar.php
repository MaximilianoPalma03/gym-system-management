<?php
require_once 'bdd.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$csrf = $_POST['csrf'] ?? '';
if (empty($csrf) || !hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    $_SESSION['msg'] = ['type'=>'danger','text'=>'Token inválido.'];
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id'])) header('Location:index.php');
$id = intval($_GET['id']);
$stmt = $conexion->prepare("SELECT nombre, apellido FROM socios WHERE id=?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) header('Location:index.php');
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Confirmar Eliminación</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light"><div class="container mt-5"><h3>Eliminar Socio</h3><p><strong><?=htmlspecialchars($s['nombre'].' '.$s['apellido'])?></strong></p><form action="eliminar_socio.php" method="POST" class="d-inline"><input type="hidden" name="id" value="<?=$id?>"><button class="btn btn-danger">Sí, eliminar</button></form><a href="index.php" class="btn btn-secondary ms-2">Cancelar</a></div></body></html>