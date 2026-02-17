<?php
require_once 'bdd.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Validar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['msg'] = ['type'=>'danger','text'=>'Solicitud inválida.'];
    header('Location: index.php'); exit;
}

// Validar CSRF
$csrf = $_POST['csrf'] ?? '';
if (empty($csrf) || !hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    $_SESSION['msg'] = ['type'=>'danger','text'=>'Token inválido.'];
    header('Location: index.php'); exit;
}

// Procesar ids...
if (!empty($_POST['ids']) && is_array($_POST['ids'])) {
    $ids = array_filter($_POST['ids'], 'ctype_digit');
    if (count($ids) > 0) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        try {
            $stmt = $conexion->prepare("DELETE FROM socios WHERE id IN ($in)");
            $stmt->execute($ids);
            $_SESSION['msg'] = [
                'type' => 'success',
                'text' => count($ids) . ' socio(s) eliminados correctamente.'
            ];
        } catch (PDOException $e) {
            $_SESSION['msg'] = [
                'type' => 'danger',
                'text' => 'Ocurrió un error al eliminar los socios seleccionados.'
            ];
        }
    } else {
        $_SESSION['msg'] = ['type'=>'warning','text'=>'No hay IDs válidos.'];
    }
} else {
    $_SESSION['msg'] = ['type'=>'warning','text'=>'No se enviaron socios para eliminar.'];
}

header('Location: index.php');
exit;