<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $factura = \App\Models\Factura::latest()->first();
    echo "Fatura: " . $factura->numero . "\n";
    
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'email_destino' => 'test@example.com',
        'assunto' => 'Teste',
        'mensagem' => 'Teste MSG'
    ]);

    $controller = new \App\Http\Controllers\FacturaController();
    $response = $controller->sendEmail($request, $factura);
    
    echo "Response Class: " . get_class($response) . "\n";
    if (method_exists($response, 'getSession')) {
        echo "Session Error: " . $response->getSession()->get('error') . "\n";
        echo "Session Success: " . $response->getSession()->get('success') . "\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
