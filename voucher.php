<?php
// Asegúrate de que el autoloader esté incluido
require_once __DIR__ . '/../vendor/autoload.php';

// Si el formulario fue enviado mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $destino = $_POST['destino'];
    $fecha = $_POST['fecha'];
    $cantidad = $_POST['cantidad'];
    $pago = $_POST['pago'];
    $total = $cantidad * 50; // Ejemplo: Cada boleto cuesta $50
}

// Crear contenido del voucher
$html = "
    <h1>Fast Express - Comprobante de Reserva</h1>
    <p><strong>Nombre:</strong> $nombre</p>
    <p><strong>Destino:</strong> $destino</p>
    <p><strong>Fecha del Viaje:</strong> $fecha</p>
    <p><strong>Cantidad de Boletos:</strong> $cantidad</p>
    <p><strong>Método de Pago:</strong> $pago</p>
    <p><strong>Total Pagado:</strong> $$total</p>
    <p>¡Gracias por reservar con Fast Express!</p>
";

// Crear el PDF

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output("voucher.pdf", "D"); // Descargar el PDF
?>
