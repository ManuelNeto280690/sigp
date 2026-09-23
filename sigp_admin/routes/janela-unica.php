<?php

use App\Http\Controllers\JanelaUnicaController;
use App\Http\Controllers\Fal1Controller;
use App\Http\Controllers\Fal2Controller;
use App\Http\Controllers\Fal3Controller;
use App\Http\Controllers\Fal4Controller;
use App\Http\Controllers\Fal5Controller;
use App\Http\Controllers\Fal6Controller;
use App\Http\Controllers\Fal7Controller;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Janela Única Marítima Routes
|--------------------------------------------------------------------------
|
| Aqui estão definidas todas as rotas relacionadas à Janela Única Marítima,
| incluindo as escalas e os sete formulários FAL obrigatórios.
|
*/

Route::middleware(['auth', '2fa'])->group(function () {
    
    // ========== ROTAS PRINCIPAIS DA JANELA ÚNICA ==========
    
    Route::prefix('janela-unica')->name('janela-unica.')->group(function () {
        // Dashboard da Janela Única
        Route::get('/dashboard', [JanelaUnicaController::class, 'dashboard'])->name('dashboard');
        
        // CRUD básico de escalas
        Route::get('/', [JanelaUnicaController::class, 'index'])->name('index');
        Route::get('/create', [JanelaUnicaController::class, 'create'])->name('create');
        Route::post('/', [JanelaUnicaController::class, 'store'])->name('store');
        Route::get('/{pedido}', [JanelaUnicaController::class, 'show'])->name('show');
        Route::get('/{pedido}/edit', [JanelaUnicaController::class, 'edit'])->name('edit');
        Route::put('/{pedido}', [JanelaUnicaController::class, 'update'])->name('update');
        Route::delete('/{pedido}', [JanelaUnicaController::class, 'destroy'])->name('destroy');
        
        // Ações específicas do ciclo de vida da escala
        Route::post('/{pedido}/confirmar', [JanelaUnicaController::class, 'confirmar'])->name('confirmar');
        Route::post('/{pedido}/atracar', [JanelaUnicaController::class, 'atracar'])->name('atracar');
        Route::post('/{pedido}/desatracar', [JanelaUnicaController::class, 'desatracar'])->name('desatracar');
        Route::post('/{pedido}/cancelar', [JanelaUnicaController::class, 'cancelar'])->name('cancelar');
        
        // Relatórios e exportação
        Route::get('/relatorios/escalas', [JanelaUnicaController::class, 'relatorioEscalas'])->name('relatorios.escalas');
        Route::get('/export/escalas', [JanelaUnicaController::class, 'exportEscalas'])->name('export.escalas');
        Route::get('/api/estatisticas', [JanelaUnicaController::class, 'estatisticas'])->name('api.estatisticas');
        Route::get('/api/progresso/{pedido}', [JanelaUnicaController::class, 'progressoDetalhado'])->name('api.progresso');
    });
    
    // ========== ROTAS DOS FORMULÁRIOS FAL ==========
    
    // FAL 1 - Declaração Geral
    Route::prefix('fal1')->name('fal1.')->group(function () {
        Route::get('/{pedido}', [Fal1Controller::class, 'show'])->name('show');
        Route::get('/{pedido}/edit', [Fal1Controller::class, 'edit'])->name('edit');
        Route::post('/{pedido}', [Fal1Controller::class, 'store'])->name('store');
        Route::put('/{pedido}', [Fal1Controller::class, 'update'])->name('update');
        
        // Ações de aprovação/rejeição
        Route::post('/{pedido}/aprovar', [Fal1Controller::class, 'aprovar'])->name('aprovar');
        Route::post('/{pedido}/rejeitar', [Fal1Controller::class, 'rejeitar'])->name('rejeitar');
        
        // Exportação
        Route::get('/{pedido}/export', [Fal1Controller::class, 'export'])->name('export');
    });
    
    // FAL 2 - Declaração de Carga
    Route::prefix('fal2')->name('fal2.')->group(function () {
        Route::get('/{pedido}', [Fal2Controller::class, 'show'])->name('show');
        Route::get('/{pedido}/edit', [Fal2Controller::class, 'edit'])->name('edit');
        Route::post('/{pedido}', [Fal2Controller::class, 'store'])->name('store');
        Route::put('/{pedido}', [Fal2Controller::class, 'update'])->name('update');
        
        // Ações de aprovação/rejeição
        Route::post('/{pedido}/aprovar', [Fal2Controller::class, 'aprovar'])->name('aprovar');
        Route::post('/{pedido}/rejeitar', [Fal2Controller::class, 'rejeitar'])->name('rejeitar');
        
        // Gestão de itens de carga
        Route::post('/{pedido}/itens', [Fal2Controller::class, 'adicionarItem'])->name('itens.adicionar');
        Route::put('/{pedido}/itens/{item}', [Fal2Controller::class, 'atualizarItem'])->name('itens.atualizar');
        Route::delete('/{pedido}/itens/{item}', [Fal2Controller::class, 'removerItem'])->name('itens.remover');
        
        // Exportação
        Route::get('/{pedido}/export', [Fal2Controller::class, 'export'])->name('export');
    });
    
    // FAL 3 - Provisões de Bordo
    Route::prefix('fal3')->name('fal3.')->group(function () {
        Route::get('/{pedido}', [Fal3Controller::class, 'show'])->name('show');
        Route::get('/{pedido}/edit', [Fal3Controller::class, 'edit'])->name('edit');
        Route::post('/{pedido}', [Fal3Controller::class, 'store'])->name('store');
        Route::put('/{pedido}', [Fal3Controller::class, 'update'])->name('update');
        
        // Ações de aprovação/rejeição
        Route::post('/{pedido}/aprovar', [Fal3Controller::class, 'aprovar'])->name('aprovar');
        Route::post('/{pedido}/rejeitar', [Fal3Controller::class, 'rejeitar'])->name('rejeitar');
        
        // Gestão de provisões
        Route::post('/{pedido}/provisoes', [Fal3Controller::class, 'adicionarProvisao'])->name('provisoes.adicionar');
        Route::put('/{pedido}/provisoes/{provisao}', [Fal3Controller::class, 'atualizarProvisao'])->name('provisoes.atualizar');
        Route::delete('/{pedido}/provisoes/{provisao}', [Fal3Controller::class, 'removerProvisao'])->name('provisoes.remover');
        
        // Exportação
        Route::get('/{pedido}/export', [Fal3Controller::class, 'export'])->name('export');
    });
    
    // FAL 4 - Pertences da Tripulação
    Route::prefix('fal4')->name('fal4.')->group(function () {
        Route::get('/{pedido}', [Fal4Controller::class, 'show'])->name('show');
        Route::get('/{pedido}/edit', [Fal4Controller::class, 'edit'])->name('edit');
        Route::post('/{pedido}', [Fal4Controller::class, 'store'])->name('store');
        Route::put('/{pedido}', [Fal4Controller::class, 'update'])->name('update');
        
        // Ações de aprovação/rejeição
        Route::post('/{pedido}/aprovar', [Fal4Controller::class, 'aprovar'])->name('aprovar');
        Route::post('/{pedido}/rejeitar', [Fal4Controller::class, 'rejeitar'])->name('rejeitar');
        
        // Gestão de pertences
        Route::post('/{pedido}/pertences', [Fal4Controller::class, 'adicionarPertence'])->name('pertences.adicionar');
        Route::put('/{pedido}/pertences/{pertence}', [Fal4Controller::class, 'atualizarPertence'])->name('pertences.atualizar');
        Route::delete('/{pedido}/pertences/{pertence}', [Fal4Controller::class, 'removerPertence'])->name('pertences.remover');
        
        // Exportação
        Route::get('/{pedido}/export', [Fal4Controller::class, 'export'])->name('export');
    });
    
    // FAL 5 - Lista de Tripulantes
    Route::prefix('fal5')->name('fal5.')->group(function () {
        Route::get('/{pedido}', [Fal5Controller::class, 'show'])->name('show');
        Route::get('/{pedido}/edit', [Fal5Controller::class, 'edit'])->name('edit');
        Route::post('/{pedido}', [Fal5Controller::class, 'store'])->name('store');
        Route::put('/{pedido}', [Fal5Controller::class, 'update'])->name('update');
        
        // Ações de aprovação/rejeição
        Route::post('/{pedido}/aprovar', [Fal5Controller::class, 'aprovar'])->name('aprovar');
        Route::post('/{pedido}/rejeitar', [Fal5Controller::class, 'rejeitar'])->name('rejeitar');
        
        // Gestão de tripulantes
        Route::post('/{pedido}/tripulantes', [Fal5Controller::class, 'adicionarTripulante'])->name('tripulantes.adicionar');
        Route::put('/{pedido}/tripulantes/{tripulante}', [Fal5Controller::class, 'atualizarTripulante'])->name('tripulantes.atualizar');
        Route::delete('/{pedido}/tripulantes/{tripulante}', [Fal5Controller::class, 'removerTripulante'])->name('tripulantes.remover');
        
        // Exportação
        Route::get('/{pedido}/export', [Fal5Controller::class, 'export'])->name('export');
    });
    
    // FAL 6 - Lista de Passageiros
    Route::prefix('fal6')->name('fal6.')->group(function () {
        Route::get('/{pedido}', [Fal6Controller::class, 'show'])->name('show');
        Route::get('/{pedido}/edit', [Fal6Controller::class, 'edit'])->name('edit');
        Route::post('/{pedido}', [Fal6Controller::class, 'store'])->name('store');
        Route::put('/{pedido}', [Fal6Controller::class, 'update'])->name('update');
        
        // Ações de aprovação/rejeição
        Route::post('/{pedido}/aprovar', [Fal6Controller::class, 'aprovar'])->name('aprovar');
        Route::post('/{pedido}/rejeitar', [Fal6Controller::class, 'rejeitar'])->name('rejeitar');
        
        // Gestão de passageiros
        Route::post('/{pedido}/passageiros', [Fal6Controller::class, 'adicionarPassageiro'])->name('passageiros.adicionar');
        Route::put('/{pedido}/passageiros/{passageiro}', [Fal6Controller::class, 'atualizarPassageiro'])->name('passageiros.atualizar');
        Route::delete('/{pedido}/passageiros/{passageiro}', [Fal6Controller::class, 'removerPassageiro'])->name('passageiros.remover');
        
        // Exportação
        Route::get('/{pedido}/export', [Fal6Controller::class, 'export'])->name('export');
    });
    
    // FAL 7 - Mercadorias Perigosas
    Route::prefix('fal7')->name('fal7.')->group(function () {
        Route::get('/{pedido}', [Fal7Controller::class, 'show'])->name('show');
        Route::get('/{pedido}/edit', [Fal7Controller::class, 'edit'])->name('edit');
        Route::post('/{pedido}', [Fal7Controller::class, 'store'])->name('store');
        Route::put('/{pedido}', [Fal7Controller::class, 'update'])->name('update');
        
        // Ações de aprovação/rejeição
        Route::post('/{pedido}/aprovar', [Fal7Controller::class, 'aprovar'])->name('aprovar');
        Route::post('/{pedido}/rejeitar', [Fal7Controller::class, 'rejeitar'])->name('rejeitar');
        
        // Gestão de itens perigosos
        Route::post('/{pedido}/itens-perigosos', [Fal7Controller::class, 'adicionarItem'])->name('itens.adicionar');
        Route::put('/{pedido}/itens-perigosos/{item}', [Fal7Controller::class, 'atualizarItem'])->name('itens.atualizar');
        Route::delete('/{pedido}/itens-perigosos/{item}', [Fal7Controller::class, 'removerItem'])->name('itens.remover');
        
        // Exportação
        Route::get('/{pedido}/export', [Fal7Controller::class, 'export'])->name('export');
    });
    
    // ========== ROTAS DE API PARA AJAX ==========
    
    Route::prefix('api/janela-unica')->name('api.janela-unica.')->group(function () {
        // Estatísticas em tempo real
        Route::get('/stats', [JanelaUnicaController::class, 'getStats'])->name('stats');
        Route::get('/escalas-recentes', [JanelaUnicaController::class, 'getEscalasRecentes'])->name('escalas-recentes');
        Route::get('/fals-pendentes', [JanelaUnicaController::class, 'getFalsPendentes'])->name('fals-pendentes');
        
        // Progresso dos FALs
        Route::get('/{pedido}/progresso', [JanelaUnicaController::class, 'getProgresso'])->name('progresso');
        Route::get('/{pedido}/fals-status', [JanelaUnicaController::class, 'getFalsStatus'])->name('fals-status');
        
        // Validações em tempo real
        Route::post('/validar-embarcacao', [JanelaUnicaController::class, 'validarEmbarcacao'])->name('validar-embarcacao');
        Route::post('/validar-terminal', [JanelaUnicaController::class, 'validarTerminal'])->name('validar-terminal');
        Route::post('/verificar-conflitos', [JanelaUnicaController::class, 'verificarConflitos'])->name('verificar-conflitos');
    });
});