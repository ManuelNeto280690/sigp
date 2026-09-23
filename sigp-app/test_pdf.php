<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $factura = \App\Models\Factura::latest()->first();
    echo "Fatura: " . $factura->numero . "\n";
    $controller = new \App\Http\Controllers\FacturaController();
    $response = $controller->exportPdf($factura);
    if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse || $response instanceof \Illuminate\Http\Response) {
        echo "Sucesso! Tipo de resposta: " . get_class($response) . "\n";
    } else {
        echo "Falhou com classe: " . get_class($response) . "\n";
        if (method_exists($response, 'getSession')) {
            echo "Session Error: " . $response->getSession()->get('error') . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
