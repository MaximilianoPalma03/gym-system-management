<?php
require_once 'bdd.php';
if (
    $_SERVER['REQUEST_METHOD']==='POST' &&
    !empty($_POST['id'])
) {
    $stmt = $conexion->prepare("DELETE FROM socios WHERE id=?");
    $stmt->execute([intval($_POST['id'])]);
}
header('Location:index.php'); exit;