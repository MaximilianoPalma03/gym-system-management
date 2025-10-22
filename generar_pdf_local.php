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

 // === antes: obtener $nombre, $apellido, $dni, $telefono, $importe, $fecha
// Generar número de recibo simple (opcional usar DB para numeración real)
$numero_recibo = date('YmdHis');

// logo: convertimos logo a base64 para que Dompdf lo lea sin problemas de rutas
$logoPath = __DIR__ . '/logo-gym.png';
$logoData = '';
if (file_exists($logoPath)) {
    $logoData = base64_encode(file_get_contents($logoPath));
    $logoSrc = 'data:image/png;base64,' . $logoData;
} else {
    $logoSrc = ''; // sin logo
}

// formateos
$fecha_form = date('d/m/Y', strtotime($fecha));
$importe_form = number_format(floatval(str_replace(',', '.', $importe)), 2, ',', '.');

$html = '
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color:#111; font-size:13px; margin:18px; }
  .header { position: relative; margin-bottom:12px; height: 110px; }
  .brand { text-align:left; position: absolute; left: 0; top: 0; }
  .brand h1 { margin:0; font-size:20px; color:#222; letter-spacing:1px; }
  .brand small { color:#666; display:block; margin-top:4px; font-size:11px; }
  .logo { width:110px; height:auto; position: absolute; right: 0; top: 0; }
  .content { border:2px solid #222; padding:10px; border-radius:6px; }
  .info-container { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; }
  .info { width:65%; }
  table.datos { width:100%; border-collapse:collapse; margin-top:8px; }
  table.datos td { padding:6px 8px; border:1px dashed #ddd; vertical-align:top; }
  footer { margin-top:10px; font-size:11px; color:#666; text-align:center; }
</style>
</head>
<body>
  <div class="header">
    <div class="brand">
      <h1>BULL GYM</h1>
      <small>Remedios de Escalada 178 · Villa Dolores (Cba)</small>
      <small>Tel: 3544-464002 · Cel: 351 6453752</small>
    </div>
    <div class="logo">
      '.($logoSrc ? "<img src=\"$logoSrc\" class=\"logo\">" : '').'
    </div>
  </div>

  <div class="content">
    <div class="info-container">
      <div class="info">
        <strong>Recibí de:</strong> '.htmlspecialchars($nombre).' '.htmlspecialchars($apellido).'<br>
        <strong>DNI:</strong> '.htmlspecialchars($dni).'<br>
        <strong>Teléfono:</strong> '.htmlspecialchars($telefono).'<br>
        <strong>Fecha:</strong> '.$fecha_form.'
      </div>
    </div>

    <table class="datos">
      <tr>
        <td><strong>Concepto</strong></td>
        <td><strong>Importe</strong></td>
      </tr>
      <tr>
        <td>Renovación de cuota mensual</td>
        <td>$ '.$importe_form.'</td>
      </tr>
    </table>
    <div style="text-align:right; font-size:12px; color:#444; margin-top:10px;">Comprobante no fiscal</div>
  </div>

  <footer>Gracias por renovar su cuota en Bull Gym.</footer>
</body>
</html>
';

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
