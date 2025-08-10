<?php
require_once 'bdd.php';

// Inicia la sesión si no está iniciada (evita warning si ya la iniciás en otro include)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    $_SESSION['msg'] = ['type'=>'danger','text'=>'Solicitud inválida.'];
    header('Location: index.php');
    exit;
}

// Validar CSRF
$csrf = $_POST['csrf'] ?? '';
if (empty($csrf) || !hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    $_SESSION['msg'] = ['type'=>'danger','text'=>'Token inválido.'];
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id = intval($_POST['id']);

    try {
        $stmt = $conexion->prepare("DELETE FROM socios WHERE id = :id");
        $stmt->execute([':id' => $id]);

        // Mensaje flash de éxito
        $_SESSION['msg'] = [
            'type' => 'success',
            'text' => 'Socio eliminado correctamente.'
        ];
    } catch (PDOException $e) {
        // En producción no mostrar $e->getMessage() al usuario.
        // Si querés registrar el error para debug, escribilo en un log aquí.
        $_SESSION['msg'] = [
            'type' => 'danger',
            'text' => 'Ocurrió un error al eliminar el socio.'
        ];
    }
}

// Redirige a index para que allí se muestre la alerta (flash)
header('Location: index.php');
exit;