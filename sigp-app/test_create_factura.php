<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'concessionaria_id' => \App\Models\Concessionaria::first()->id ?? 1,
        'tipo_documento' => 'FT',
        'items' => [
            [
                'descricao' => 'Teste item',
                'quantidade' => 1,
                'preco_unitario' => 1000,
                'imposto_id' => \App\Models\Imposto::where('tipo', 'IVA')->first()->id ?? 1
            ]
        ]
    ]);

    $controller = new \App\Http\Controllers\FacturaController();
    $response = $controller->store($request);
    
    echo "Response Class: " . get_class($response) . "\n";
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "Redirect URL: " . $response->getTargetUrl() . "\n";
        echo "Session Success: " . session('success') . "\n";
        echo "Session Error: " . session('error') . "\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
