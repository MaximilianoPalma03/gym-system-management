<?php
// generar_pdf_local.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'bdd.php';

// comprobar vendor/autoload
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Falta vendor/autoload.php. Ejecutá composer require dompdf/dompdf en la carpeta del proyecto.']);
    exit;
}

require $autoload;
use Dompdf\Dompdf;

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// CSRF (opcional pero recomendado)
$csrf = $_POST['csrf'] ?? '';
if (empty($csrf) || !hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Token inválido.']);
    exit;
}

try {
    // Validaciones
    $id = intval($_POST['id'] ?? 0);
    $telefono_raw = $_POST['telefono'] ?? '';
    $telefono = preg_replace('/[^0-9\+]/','', $telefono_raw);
    $importe = trim($_POST['importe'] ?? '');

    if (!$id || $telefono === '' || $importe === '') {
        echo json_encode(['ok'=>false,'error'=>'Faltan datos o id inválido.']);
        exit;
    }

    // Traer socio
    $stmt = $conexion->prepare("SELECT nombre, apellido, dni FROM socios WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $s = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$s) {
        echo json_encode(['ok'=>false,'error'=>'Socio no encontrado.']);
        exit;
    }

    $nombre = $s['nombre'];
    $apellido = $s['apellido'];
    $dni = $s['dni'];
    $fecha = date('Y-m-d');

    // HTML del comprobante
    $html = "
      <div style='font-family: Arial, sans-serif;margin:20px'>
        <h2 style='color:#333'>Comprobante de Pago - Bull Gym</h2>
        <p><strong>Nombre:</strong> " . htmlspecialchars($nombre . ' ' . $apellido) . "</p>
        <p><strong>DNI:</strong> " . htmlspecialchars($dni) . "</p>
        <p><strong>Fecha:</strong> " . htmlspecialchars($fecha) . "</p>
        <p><strong>Importe:</strong> " . htmlspecialchars($importe) . "</p>
        <p><strong>Teléfono:</strong> " . htmlspecialchars($telefono) . "</p>
        <hr>
        <p>Gracias por renovar su cuota en Bull Gym.</p>
      </div>";

    // Carpeta dentro del proyecto
    $dir = __DIR__ . '/comprobantes';
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0777, true)) {
            echo json_encode(['ok'=>false,'error'=>'No se pudo crear carpeta comprobantes. Comprá permisos.']);
            exit;
        }
    }

    // Nombre único
    $safeLast = preg_replace('/[^A-Za-z0-9_-]/', '_', $apellido);
    $safeFirst = preg_replace('/[^A-Za-z0-9_-]/', '_', $nombre);
    $fname = "{$safeLast}_{$safeFirst}_" . date('Ymd_His') . ".pdf";
    $fullpath = $dir . DIRECTORY_SEPARATOR . $fname;

    // Generar PDF
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    file_put_contents($fullpath, $dompdf->output());

    // Construir URL de WhatsApp Web (sin +)
    $telefono_wa = preg_replace('/[^0-9]/','', $telefono);
    $mensaje = "Nombre: {$nombre}\nApellido: {$apellido}\nFecha: {$fecha}\nImporte: {$importe}\nTeléfono: {$telefono}\n\nGracias por renovar su cuota en Bull Gym";
    $waUrl = 'https://web.whatsapp.com/send?phone=' . urlencode($telefono_wa) . '&text=' . urlencode($mensaje);

    // Devolver JSON con ruta absoluta del archivo (para abrir localmente si querés)
    echo json_encode(['ok' => true, 'wa' => $waUrl, 'file' => $fullpath]);
    exit;

} catch (Throwable $e) {
    // Loguear error en servidor y devolver JSON
    error_log('generar_pdf_local.php error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Error interno al generar comprobante.']);
    exit;
}
