<?php
// generar_pdf_local.php (corregido)
require_once 'bdd.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Devolver siempre JSON (para que el front lo interprete seguro)
header('Content-Type: application/json; charset=utf-8');

// evitar que notices/war ning output "ensucien" el JSON
ob_start();

try {
    // comprueba vendor autoload
    $autoload = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoload)) {
        // Si no tenés composer/domPDF instalado, devolver error claro
        throw new Exception('Falta vendor/autoload.php. Ejecuta: composer require dompdf/dompdf en la carpeta del proyecto.');
    }
    require_once $autoload;

    // recibiendo datos
    $csrf = $_POST['csrf'] ?? '';
    if (empty($csrf) || !hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
        throw new Exception('Token inválido.');
    }

    $id = intval($_POST['id'] ?? 0);
    $telefono = preg_replace('/[^0-9\+]/','', $_POST['telefono'] ?? '');
    $importe = trim($_POST['importe'] ?? '');

    if (!$id || !$telefono || $importe === '') {
        throw new Exception('Faltan datos o id inválido.');
    }

    // buscar socio
    $stmt = $conexion->prepare("SELECT nombre, apellido, dni FROM socios WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $s = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$s) throw new Exception('Socio no encontrado.');

    $nombre = $s['nombre'];
    $apellido = $s['apellido'];
    $dni = $s['dni'];
    $fecha = date('Y-m-d');

    // html comprobante
    $html = "
      <div style='font-family: Arial, sans-serif;margin:20px'>
        <h2 style='color:#333'>Comprobante de Pago - Bull Gym</h2>
        <p><strong>Nombre:</strong> {$nombre} {$apellido}</p>
        <p><strong>DNI:</strong> {$dni}</p>
        <p><strong>Fecha:</strong> {$fecha}</p>
        <p><strong>Importe:</strong> {$importe}</p>
        <p><strong>Teléfono:</strong> {$telefono}</p>
        <hr>
        <p>Gracias por renovar su cuota en Bull Gym.</p>
      </div>";

    // carpeta corregida: guardamos dentro de /comprobantes relative al proyecto
    $dir = __DIR__ . '/comprobantes';
    if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
        throw new Exception('No se pudo crear la carpeta de comprobantes (permisos).');
    }

    $fname = "{$apellido}_{$nombre}_" . date('Ymd_His') . ".pdf";
    $fullpath = $dir . DIRECTORY_SEPARATOR . $fname;

    // generar PDF usando Dompdf (namespace completo para evitar problemas)
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdfBytes = $dompdf->output();

    if (file_put_contents($fullpath, $pdfBytes) === false) {
        throw new Exception('Error al guardar el PDF en disco.');
    }

    // intentar abrir la carpeta - OPCIONAL y no crítico.
    $opened = false;
    try {
        $os = PHP_OS_FAMILY;
        // Solo intentamos si las funciones existen (y estamos en Windows)
        if ($os === 'Windows' && function_exists('popen')) {
            // usamos un comando que suele funcionar en Windows
            $cmd = 'explorer.exe /select,"' . str_replace('/','\\', $fullpath) . '"';
            // intenta ejecutar sin bloquear. En phpdesktop esto puede fallar; no es crítico.
            @pclose(@popen("start \"\" cmd /c ".escapeshellarg($cmd), "r"));
            $opened = true;
        }
    } catch (Exception $e) {
        $opened = false;
    }

    // preparar URL de WA (web.whatsapp.com)
    $telefono_wa = preg_replace('/[^0-9]/','', $telefono);
    $mensaje = "Nombre: {$nombre}\nApellido: {$apellido}\nFecha: {$fecha}\nImporte: {$importe}\nTeléfono: {$telefono}\n\nGracias por renovar su cuota en Bull Gym";
    $waUrl = 'https://web.whatsapp.com/send?phone=' . urlencode($telefono_wa) . '&text=' . urlencode($mensaje);

    // limpiar buffer de salida accidental
    ob_end_clean();
    echo json_encode(['ok'=>true, 'wa'=>$waUrl, 'file'=>$fullpath, 'opened'=>$opened]);
    exit;
} catch (Exception $e) {
    // limpiar buffer y devolver JSON con error
    @ob_end_clean();
    http_response_code(400);
    echo json_encode(['ok'=>false, 'error' => $e->getMessage()]);
    exit;
}
