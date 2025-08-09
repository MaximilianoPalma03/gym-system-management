<?php
<?php
require_once 'bdd.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids']) && is_array($_POST['ids'])) {
    $ids = array_filter($_POST['ids'], 'ctype_digit');
    if (count($ids) > 0) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        try {
            $stmt = $conexion->prepare("DELETE FROM socios WHERE id IN ($in)");
            $stmt->execute($ids);
            $_SESSION['msg'] = [
                'type' => 'success',
                'text' => 'Socios eliminados correctamente.'
            ];
        } catch (PDOException $e) {
            $_SESSION['msg'] = [
                'type' => 'danger',
                'text' => 'Ocurrió un error al eliminar los socios seleccionados.'
            ];
        }
    }
}
header('Location: index.php');
exit;