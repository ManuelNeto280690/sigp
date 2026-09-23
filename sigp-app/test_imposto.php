<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $imposto = App\Models\Imposto::create([
        'tipo' => 'IVA',
        'sigla' => 'TST',
        'nome' => 'Teste',
        'taxa' => 14,
        'ativo' => true
    ]);
    echo "SUCCESS: " . $imposto->id . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
