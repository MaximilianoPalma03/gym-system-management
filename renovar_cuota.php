<?php
require_once 'bdd.php';

// sesión segura
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    // detectar si es petición AJAX (fetch)
    $isAjax = (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Solicitud inválida.']);
        exit;
    }

    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Solicitud inválida.'];
    header('Location: index.php');
    exit;
}

// Validar CSRF
$csrf = $_POST['csrf'] ?? '';
if (empty($csrf) || !hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    $isAjax = (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
        exit;
    }

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

    // Si la petición es AJAX devolvemos JSON en lugar de redirigir
    $isAjax = (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'message' => 'Cuota renovada correctamente.']);
        exit;
    }

    $_SESSION['msg'] = ['type' => 'success', 'text' => '¡Cuota renovada correctamente!'];
} catch (PDOException $e) {
    error_log('renovar_cuota.php - Error renovar id='.$id.' - '.$e->getMessage());

    $isAjax = (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Error al renovar la cuota.']);
        exit;
    }

    $_SESSION['msg'] = ['type' => 'danger', 'text' => 'Error al renovar la cuota.'];
}

header('Location: index.php');
exit;