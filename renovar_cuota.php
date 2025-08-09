<?php
require_once 'bdd.php';

// sesión segura
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Solicitud inválida.'];
    header('Location: index.php');
    exit;
}

// Validar CSRF
$csrf = $_POST['csrf'] ?? '';
if (empty($csrf) || !hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Token inválido.'];
    header('Location: index.php');
    exit;
}

$id = intval($_POST['id']);

// Decide aquí si querés setear fecha_inscripcion también o sólo vencimiento.
// En este ejemplo solo actualizamos vencimiento a +1 mes desde hoy.
try {
    $stmt = $conexion->prepare("
        UPDATE socios 
        SET 
            fecha_inscripcion = CURDATE(),
            fecha_vencimiento = DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
        WHERE id = :id
    ");
    $stmt->execute([':id' => $id]);

    $ins = $conexion->prepare("INSERT INTO renovaciones (socio_id, fecha_renovacion) VALUES (:sid, :f)");
    $ins->execute([':sid' => $id, ':f' => date('Y-m-d')]);


    $_SESSION['msg'] = ['type' => 'success', 'text' => '¡Cuota renovada correctamente!'];
} catch (PDOException $e) {
    error_log('renovar_cuota.php - Error renovar id='.$id.' - '.$e->getMessage());
    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Error al renovar la cuota.'];
}

header('Location: index.php');
exit;