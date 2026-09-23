<?php


use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MovimentoTerminalController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\EmbarcacaoController;
use App\Http\Controllers\EmbarcacaoConcessionariaController;
use App\Http\Controllers\EntradaSaidaEmbarcacaoController;
use App\Http\Controllers\IncidenteController;
use App\Http\Controllers\InspecaoController;
use App\Http\Controllers\InfracaoAmbientalController;
use App\Http\Controllers\ConcessionariaController;
use App\Http\Controllers\TerminalController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\PedidoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\TwoFactorAuthController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EntradaSaidaEmbarcacaoConcessionariaController;
use App\Http\Controllers\PlanejamentoController;
use App\Http\Controllers\BercoController;
use App\Http\Controllers\GuindasteController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\ImpostoController;
use App\Http\Controllers\SaftController;
use App\Http\Controllers\MovimentoCargaController;


Route::get('/', function () {
    return redirect('/login');
});

// Remover esta linha duplicada que está causando o problema
// Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', '2fa'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rotas de 2FA
Route::middleware(['auth'])->group(function () {
    Route::get('/2fa/setup', [TwoFactorAuthController::class, 'showSetup'])->name('2fa.setup');
    Route::post('/2fa/setup', [TwoFactorAuthController::class, 'setup']);
    Route::get('/2fa/verify', [TwoFactorAuthController::class, 'showVerify'])->name('2fa.verify');
    Route::post('/2fa/verify', [TwoFactorAuthController::class, 'verify'])->name('2fa.check');
    Route::post('/2fa/resend', [TwoFactorAuthController::class, 'resendEmailCode'])->name('2fa.resend');
    Route::post('/2fa/disable', [TwoFactorAuthController::class, 'disable'])->name('2fa.disable');
});

// Manter apenas esta definição da rota dashboard
Route::middleware(['auth', '2fa'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/trafego', function () {
    return view('trafic');
});
    
    // Rotas AJAX para o dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    Route::get('/dashboard/recent-activities', [DashboardController::class, 'getRecentActivities'])->name('dashboard.recent-activities');
    
    // Rotas de notificações
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::post('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
    });
    
    // Rotas para Roles (Funções)
    Route::resource('roles', RoleController::class);

     // Rotas para Permissões
    Route::resource('permissions', PermissionController::class);

     // Rotas para Usuários
    Route::resource('users', UserController::class);
     // Rotas adicionais para ações específicas dos usuários
    Route::post('/users/{user}/block', [UserController::class, 'block'])->name('users.block');
    Route::post('/users/{user}/unblock', [UserController::class, 'unblock'])->name('users.unblock');
    Route::post('/users/{user}/reset-attempts', [UserController::class, 'resetFailedAttempts'])->name('users.reset-attempts');
    Route::post('/users/{user}/reset-2fa', [UserController::class, 'resetTwoFactor'])->name('users.reset-2fa');

    // Rotas para Movimento Terminal
    Route::resource('movimento-terminal', MovimentoTerminalController::class)->names([
        'index' => 'movimento-terminal.index',
        'create' => 'movimento-terminal.create',
        'store' => 'movimento-terminal.store',
        'show' => 'movimento-terminal.show',
        'edit' => 'movimento-terminal.edit',
        'update' => 'movimento-terminal.update',
        'destroy' => 'movimento-terminal.destroy'
    ]);
    
    // Rotas adicionais para ações específicas do movimento terminal
    Route::post('/movimento-terminal/{movimento}/iniciar', [MovimentoTerminalController::class, 'iniciar'])->name('movimento-terminal.iniciar');
    Route::post('/movimento-terminal/{movimento}/finalizar', [MovimentoTerminalController::class, 'finalizar'])->name('movimento-terminal.finalizar');
    Route::post('/movimento-terminal/{movimento}/cancelar', [MovimentoTerminalController::class, 'cancelar'])->name('movimento-terminal.cancelar');
    Route::get('/movimento-terminal-api', [MovimentoTerminalController::class, 'api'])->name('movimento-terminal.api');
    Route::get('/movimento-terminal-dashboard', [MovimentoTerminalController::class, 'dashboard'])->name('movimento-terminal.dashboard');
    
    // Rotas para Alertas
    Route::resource('alertas', AlertaController::class)->names([
        'index' => 'alertas.index',
        'create' => 'alertas.create',
        'store' => 'alertas.store',
        'show' => 'alertas.show',
        'edit' => 'alertas.edit',
        'update' => 'alertas.update',
        'destroy' => 'alertas.destroy'
    ]);
    
    // Rotas adicionais para ações específicas dos alertas
    Route::post('/alertas/{alerta}/resolve', [AlertaController::class, 'resolve'])->name('alertas.resolve');
    Route::post('/alertas/{alerta}/dismiss', [AlertaController::class, 'dismiss'])->name('alertas.dismiss');
    Route::get('/alertas-dashboard', [AlertaController::class, 'dashboard'])->name('alertas.dashboard');
    Route::get('/alertas-api', [AlertaController::class, 'api'])->name('alertas.api');
    
    // Rotas para Embarcações
    Route::resource('embarcacoes', EmbarcacaoController::class, [
        'parameters' => ['embarcacoes' => 'embarcacao']
    ])->names([
        'index' => 'embarcacoes.index',
        'create' => 'embarcacoes.create',
        'store' => 'embarcacoes.store',
        'show' => 'embarcacoes.show',
        'edit' => 'embarcacoes.edit',
        'update' => 'embarcacoes.update',
        'destroy' => 'embarcacoes.destroy'
    ]);
    
    // Rotas adicionais para ações específicas das embarcações
    Route::get('/embarcacoes-export', [EmbarcacaoController::class, 'export'])->name('embarcacoes.export');
    Route::get('/embarcacoes-relatorio-pdf', [EmbarcacaoController::class, 'relatorioFiltrado'])->name('embarcacoes.relatorioFiltrado');
    Route::get('/embarcacoes/{embarcacao}/pdf', [EmbarcacaoController::class, 'exportPdf'])->name('embarcacoes.exportPdf');
    Route::post('/embarcacoes-import', [EmbarcacaoController::class, 'import'])->name('embarcacoes.import');
    Route::post('/embarcacoes-process-import', [EmbarcacaoController::class, 'processImport'])->name('embarcacoes.processImport');
    Route::get('/embarcacoes-relatorio', [EmbarcacaoController::class, 'relatorio'])->name('embarcacoes.relatorio');
    Route::get('/embarcacoes-api', [EmbarcacaoController::class, 'api'])->name('embarcacoes.api');
    
    // Rotas para Entrada/Saída de Embarcações
    Route::resource('entrada-saida-embarcacao', EntradaSaidaEmbarcacaoController::class, [
        'parameters' => ['entrada-saida-embarcacao' => 'entradaSaida']
    ])->names([
        'index' => 'entrada-saida-embarcacao.index',
        'create' => 'entrada-saida-embarcacao.create',
        'store' => 'entrada-saida-embarcacao.store',
        'show' => 'entrada-saida-embarcacao.show',
        'edit' => 'entrada-saida-embarcacao.edit',
        'update' => 'entrada-saida-embarcacao.update',
        'destroy' => 'entrada-saida-embarcacao.destroy'
    ]);

// Rotas API para AJAX (buscar dados do navio, terminais e berços)
Route::get('/planejamento/embarcacao/{id}', [PlanejamentoController::class, 'getEmbarcacaoInfo']);
Route::get('/planejamento/terminais-disponiveis', [PlanejamentoController::class, 'getTerminaisDisponiveis']);
Route::get('/planejamento/guindastes', [PlanejamentoController::class, 'getGuindastesPorTerminal']);
    
    // Rotas adicionais para ações específicas dos movimentos de entrada/saída
    Route::post('/entrada-saida-embarcacao/{entradaSaida}/autorizar', [EntradaSaidaEmbarcacaoController::class, 'autorizar'])->name('entrada-saida-embarcacao.autorizar');
    Route::post('/entrada-saida-embarcacao/{entradaSaida}/iniciar', [EntradaSaidaEmbarcacaoController::class, 'iniciar'])->name('entrada-saida-embarcacao.iniciar');
    Route::post('/entrada-saida-embarcacao/{entradaSaida}/atracar', [EntradaSaidaEmbarcacaoController::class, 'atracar'])->name('entrada-saida-embarcacao.atracar');
    Route::post('/entrada-saida-embarcacao/{entradaSaida}/desatracar', [EntradaSaidaEmbarcacaoController::class, 'desatracar'])->name('entrada-saida-embarcacao.desatracar');
    Route::post('/entrada-saida-embarcacao/{entradaSaida}/concluir', [EntradaSaidaEmbarcacaoController::class, 'concluir'])->name('entrada-saida-embarcacao.concluir');
    Route::post('/entrada-saida-embarcacao/{entradaSaida}/cancelar', [EntradaSaidaEmbarcacaoController::class, 'cancelar'])->name('entrada-saida-embarcacao.cancelar');
    Route::post('/entrada-saida-embarcacao/{entradaSaida}/revogar', [EntradaSaidaEmbarcacaoController::class, 'revogar'])->name('entrada-saida-embarcacao.revogar');
    Route::get('/entrada-saida-embarcacao/{entradaSaida}/pdf', [EntradaSaidaEmbarcacaoController::class, 'exportPdf'])->name('entrada-saida-embarcacao.exportPdf');
    Route::get('/entrada-saida-embarcacao-relatorio-filtrado', [EntradaSaidaEmbarcacaoController::class, 'relatorioFiltrado'])->name('entrada-saida-embarcacao.relatorioFiltrado');
    Route::get('/entrada-saida-embarcacao-dashboard', [EntradaSaidaEmbarcacaoController::class, 'dashboard'])->name('entrada-saida-embarcacao.dashboard');
    Route::get('/entrada-saida-embarcacao-api', [EntradaSaidaEmbarcacaoController::class, 'api'])->name('entrada-saida-embarcacao.api');
    Route::get('/entrada-saida-embarcacao-relatorio', [EntradaSaidaEmbarcacaoController::class, 'relatorio'])->name('entrada-saida-embarcacao.relatorio');
    Route::get('/entrada-saida-embarcacao/weather-preview', [EntradaSaidaEmbarcacaoController::class, 'weatherPreview'])->name('entrada-saida-embarcacao.weather-preview');
    
    // Rotas para Concessionárias
    Route::resource('concessionarias', ConcessionariaController::class);
    Route::get('/concessionarias-dashboard', [ConcessionariaController::class, 'dashboard'])->name('concessionarias.dashboard');
    Route::get('/concessionarias-api', [ConcessionariaController::class, 'api'])->name('concessionarias.api');
    Route::post('/concessionarias/{concessionaria}/add-user', [ConcessionariaController::class, 'addUser'])->name('concessionarias.add-user');
    Route::delete('/concessionarias/{concessionaria}/remove-user', [ConcessionariaController::class, 'removeUser'])->name('concessionarias.remove-user');
    Route::get('/concessionarias-relatorio-filtrado', [ConcessionariaController::class, 'relatorioFiltrado'])->name('concessionarias.relatorioFiltrado');
   
   
    
    // Rotas para Terminais
    Route::resource('terminais', TerminalController::class);
    Route::post('/terminais/{terminal}/toggle-status', [TerminalController::class, 'toggleStatus'])->name('terminais.toggle-status');
    Route::get('/terminais-api', [TerminalController::class, 'api'])->name('terminais.api');
    Route::get('/terminais-relatorio', [TerminalController::class, 'relatorio'])->name('terminais.relatorio');
    Route::get('/terminais-relatorioFiltrado', [TerminalController::class, 'relatorioFiltrado'])->name('terminais.relatorioFiltrado');

       // Rotas para Berços
    Route::resource('bercos', BercoController::class)->names([
        'index' => 'bercos.index',
        'create' => 'bercos.create',
        'store' => 'bercos.store',
        'show' => 'bercos.show',
        'edit' => 'bercos.edit',
        'update' => 'bercos.update',
        'destroy' => 'bercos.destroy'
    ]);

    Route::resource('guindastes', GuindasteController::class)->names([
        'index' => 'guindastes.index',
        'create' => 'guindastes.create',
        'store' => 'guindastes.store',
        'show' => 'guindastes.show',
        'edit' => 'guindastes.edit',
        'update' => 'guindastes.update',
        'destroy' => 'guindastes.destroy'
    ]);

    Route::resource('contratos', ContratoController::class)->names([
        'index' => 'contratos.index',
        'create' => 'contratos.create',
        'store' => 'contratos.store',
        'show' => 'contratos.show',
        'edit' => 'contratos.edit',
        'update' => 'contratos.update',
        'destroy' => 'contratos.destroy'
    ]);
    Route::post('contratos/{contrato}/tarifas', [ContratoController::class, 'storeTarifa'])->name('contratos.tarifas.store');
    Route::put('contratos/{contrato}/tarifas/{tarifa}', [ContratoController::class, 'updateTarifa'])->name('contratos.tarifas.update');
    Route::delete('contratos/{contrato}/tarifas/{tarifa}', [ContratoController::class, 'destroyTarifa'])->name('contratos.tarifas.destroy');

    Route::get('/facturas/relatorio', [FacturaController::class, 'relatorioFiltrado'])->name('facturas.relatorioFiltrado');
    Route::resource('facturas', FacturaController::class)->only(['index', 'create', 'store', 'show'])->names([
        'index' => 'facturas.index',
        'create' => 'facturas.create',
        'store' => 'facturas.store',
        'show' => 'facturas.show'
    ]);
    Route::post('/facturas/{factura}/marcar-paga', [FacturaController::class, 'marcarPaga'])->name('facturas.marcar-paga');
    Route::post('/facturas/{factura}/emitir-nota-credito', [FacturaController::class, 'emitirNotaCredito'])->name('facturas.emitir-nota-credito');
    Route::get('/facturas/{factura}/pdf', [FacturaController::class, 'exportPdf'])->name('facturas.exportPdf');
    Route::post('/facturas/{factura}/send-email', [FacturaController::class, 'sendEmail'])->name('facturas.send-email');

    // Impostos e SAF-T
    Route::resource('impostos', ImpostoController::class);
    Route::post('/faturacao/configuracao', [ImpostoController::class, 'salvarConfiguracao'])->name('faturacao.salvar-configuracao');
    Route::get('/saft', [SaftController::class, 'index'])->name('saft.index');
    Route::get('/saft/export', [SaftController::class, 'export'])->name('saft.export');

    Route::resource('movimentos-carga', MovimentoCargaController::class)->names([
        'index' => 'movimentos-carga.index',
        'create' => 'movimentos-carga.create',
        'store' => 'movimentos-carga.store',
        'show' => 'movimentos-carga.show',
        'edit' => 'movimentos-carga.edit',
        'update' => 'movimentos-carga.update',
        'destroy' => 'movimentos-carga.destroy'
    ]);
    Route::get('/movimentos-carga/contexto/{entradaSaida}', [MovimentoCargaController::class, 'contextoEntradaSaida'])->name('movimentos-carga.contexto');
    
    // Rotas para Incidentes
    Route::resource('incidentes', IncidenteController::class)->names([
        'index' => 'incidentes.index',
        'create' => 'incidentes.create',
        'store' => 'incidentes.store',
        'show' => 'incidentes.show',
        'edit' => 'incidentes.edit',
        'update' => 'incidentes.update',
        'destroy' => 'incidentes.destroy'
    ]);
    
    // Rotas adicionais para ações específicas dos incidentes
    Route::post('/incidentes/{incidente}/investigate', [IncidenteController::class, 'investigate'])->name('incidentes.investigate');
    Route::post('/incidentes/{incidente}/close', [IncidenteController::class, 'close'])->name('incidentes.close');
    Route::get('/incidentes/{incidente}/pdf', [IncidenteController::class, 'exportPdf'])->name('incidentes.exportPdf');
    Route::get('/incidentes-dashboard', [IncidenteController::class, 'dashboard'])->name('incidentes.dashboard');
    Route::get('/incidentes-relatorio-filtrado', [IncidenteController::class, 'relatorioFiltrado'])->name('incidentes.relatorioFiltrado');
    Route::get('/incidentes-api', [IncidenteController::class, 'api'])->name('incidentes.api');
    
    // Rotas para Inspeções
    Route::resource('inspecoes', InspecaoController::class)->names([
        'index' => 'inspecoes.index',
        'create' => 'inspecoes.create',
        'store' => 'inspecoes.store',
        'show' => 'inspecoes.show',
        'edit' => 'inspecoes.edit',
        'update' => 'inspecoes.update',
        'destroy' => 'inspecoes.destroy'
    ]);
    
    // Rotas adicionais para ações específicas das inspeções
    Route::post('/inspecoes/{inspecao}/start', [InspecaoController::class, 'start'])->name('inspecoes.start');
    Route::post('/inspecoes/{inspecao}/complete', [InspecaoController::class, 'complete'])->name('inspecoes.complete');
    Route::post('/inspecoes/{inspecao}/cancel', [InspecaoController::class, 'cancel'])->name('inspecoes.cancel');
    Route::get('/inspecoes-dashboard', [InspecaoController::class, 'dashboard'])->name('inspecoes.dashboard');
    Route::get('/inspecoes-relatorio', [InspecaoController::class, 'relatorio'])->name('inspecoes.relatorio');
    Route::get('/inspecoes-relatorio-filtrado', [InspecaoController::class, 'relatorioFiltrado'])->name('inspecoes.relatorio-filtrado');
    Route::get('/inspecoes-ambientais-relatorio', [InspecaoController::class, 'relatorioAmbiental'])->name('inspecoes-ambientais.relatorio');
    Route::get('/inspecoes-api', [InspecaoController::class, 'api'])->name('inspecoes.api');
        Route::get('/inspecoes/{inspecao}/pdf', [InspecaoController::class, 'exportPdf'])->name('inspecoes.exportPdf');
    
    // Rotas para Pedidos
    Route::resource('pedidos', PedidoController::class);
    Route::post('/pedidos/{pedido}/aprovar', [PedidoController::class, 'aprovar'])->name('pedidos.aprovar');
    Route::post('/pedidos/{pedido}/rejeitar', [PedidoController::class, 'rejeitar'])->name('pedidos.rejeitar');
    Route::post('/pedidos/{pedido}/revogar', [PedidoController::class, 'revogar'])->name('pedidos.revogar');
    Route::post('/pedidos/{pedido}/iniciar-analise', [PedidoController::class, 'iniciarAnalise'])->name('pedidos.iniciar-analise');
    Route::get('/pedidos-dashboard', [PedidoController::class, 'dashboard'])->name('pedidos.dashboard');
    Route::get('/pedidos-relatorio', [PedidoController::class, 'relatorio'])->name('pedidos.relatorio');
    Route::get('/pedidos-relatorio-filtrado', [PedidoController::class, 'relatorioFiltrado'])->name('pedidos.relatorio-filtrado');
    Route::get('/pedidos-api', [PedidoController::class, 'api'])->name('pedidos.api');
    
    // Rotas para Infrações Ambientais
    Route::resource('infracoes-ambientais', InfracaoAmbientalController::class)->names([
        'index' => 'infracoes-ambientais.index',
        'create' => 'infracoes-ambientais.create',
        'store' => 'infracoes-ambientais.store',
        'show' => 'infracoes-ambientais.show',
        'edit' => 'infracoes-ambientais.edit',
        'update' => 'infracoes-ambientais.update',
        'destroy' => 'infracoes-ambientais.destroy'
    ]);
    
    // Rotas adicionais para ações específicas das infrações ambientais
    Route::get('/infracoes-ambientais/{infracao}/pdf', [InfracaoAmbientalController::class, 'exportPdf'])->name('infracoes-ambientais.exportPdf');
    Route::post('/infracoes-ambientais/{infracao}/notificar', [InfracaoAmbientalController::class, 'notificar'])->name('infracoes-ambientais.notificar');
    Route::post('/infracoes-ambientais/{infracao}/processar-defesa', [InfracaoAmbientalController::class, 'processarDefesa'])->name('infracoes-ambientais.processar-defesa');
    Route::post('/infracoes-ambientais/{infracao}/marcar-paga', [InfracaoAmbientalController::class, 'marcarPaga'])->name('infracoes-ambientais.marcar-paga');
    Route::get('/infracoes-ambientais-dashboard', [InfracaoAmbientalController::class, 'dashboard'])->name('infracoes-ambientais.dashboard');
    Route::get('/infracoes-ambientais-relatorio', [InfracaoAmbientalController::class, 'relatorio'])->name('infracoes-ambientais.relatorio');
    Route::get('/infracoes-ambientais-relatorio-filtrado', [InfracaoAmbientalController::class, 'relatorioFiltrado'])->name('infracoes-ambientais.relatorio-filtrado');
    Route::get('/infracoes-ambientais-api', [InfracaoAmbientalController::class, 'api'])->name('infracoes-ambientais.api');
    
    // Rotas para Auditoria
    Route::resource('audit-logs', AuditLogController::class)->only(['index', 'show']);
    Route::get('/audit-logs-dashboard', [AuditLogController::class, 'dashboard'])->name('audit-logs.dashboard');
    Route::get('/audit-logs-export', [AuditLogController::class, 'export'])->name('audit-logs.export');
    Route::get('/audit-logs-api', [AuditLogController::class, 'api'])->name('audit-logs.api');
    Route::get('/audit-logs-activity', [AuditLogController::class, 'activity'])->name('audit-logs.activity');
    
    // Rotas para Configurações
    Route::resource('configuracoes', ConfiguracaoController::class);
    Route::post('/configuracoes/{configuracao}/toggle-status', [ConfiguracaoController::class, 'toggleStatus'])->name('configuracoes.toggle-status');
    Route::get('/configuracoes-backup', [ConfiguracaoController::class, 'backup'])->name('configuracoes.backup');
    Route::post('/configuracoes-restore', [ConfiguracaoController::class, 'restore'])->name('configuracoes.restore');
    Route::post('/configuracoes-reset-default', [ConfiguracaoController::class, 'resetToDefault'])->name('configuracoes.reset-default');
    Route::get('/configuracoes-export', [ConfiguracaoController::class, 'export'])->name('configuracoes.export');
    Route::put('/configuracoes-update-batch', [ConfiguracaoController::class, 'updateBatch'])->name('configuracoes.updateBatch');
    Route::post('/configuracoes-clear-cache', [ConfiguracaoController::class, 'clearCache'])->name('configuracoes.clearCache');
    Route::get('/configuracoes-api', [ConfiguracaoController::class, 'api'])->name('configuracoes.api');
    
    // Rotas para Relatórios
    Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
    Route::get('/relatorios/geral', [RelatorioController::class, 'geral'])->name('relatorios.geral');
    Route::get('/relatorios/incidentes', [RelatorioController::class, 'incidentes'])->name('relatorios.incidentes');
    Route::get('/relatorios/inspecoes', [RelatorioController::class, 'inspecoes'])->name('relatorios.inspecoes');
    Route::get('/relatorios/infracoes', [RelatorioController::class, 'infracoes'])->name('relatorios.infracoes');
    Route::get('/relatorios/embarcacoes', [RelatorioController::class, 'embarcacoes'])->name('relatorios.embarcacoes');
    Route::get('/relatorios/terminais', [RelatorioController::class, 'terminais'])->name('relatorios.terminais');
    Route::get('/relatorios/consolidado', [RelatorioController::class, 'consolidado'])->name('relatorios.consolidado');
    // Adicionar as rotas faltantes para relatórios
    Route::get('/relatorios/concessionarias', [RelatorioController::class, 'concessionarias'])->name('concessionarias.relatorio');
    Route::get('/relatorios/entrada-saida', [RelatorioController::class, 'entradaSaida'])->name('entrada-saida.relatorio');
    Route::get('/relatorios/alertas', [AlertaController::class, 'relatorio'])->name('alertas.relatorio');
    Route::get('/relatorios/alertas-filtrado', [AlertaController::class, 'relatorioFiltrado'])->name('alertas.relatorioFiltrado');
    Route::get('/relatorios/inspecao-navios', [RelatorioController::class, 'inspecaoNavios'])->name('inspecao-navios.relatorio');
    Route::get('/relatorios/inspecao-ambiental', [RelatorioController::class, 'inspecaoAmbiental'])->name('inspecao-ambiental.relatorio');
    Route::get('/relatorios/pedidos', [RelatorioController::class, 'pedidos'])->name('pedidos.relatorio');
    
    Route::get('/relatorios/export', [RelatorioController::class, 'export'])->name('relatorios.export');
    Route::post('/relatorios/generate', [RelatorioController::class, 'generate'])->name('relatorios.generate');
    
    // Todas as outras rotas protegidas do sistema devem estar aqui
    Route::get('/terminais-api', [TerminalController::class, 'api'])->name('terminais.api');
    Route::get('/terminais-relatorio', [TerminalController::class, 'relatorio'])->name('terminais.relatorio');
    
    //CONCESSIONARIAS - Rotas para Embarcações de Concessionárias (fora do grupo 2FA)
Route::prefix('concessionaria')->name('concessionarias.')->group(function () {
            Route::resource('embarcacoes', EmbarcacaoConcessionariaController::class)->names([
                'index' => 'embarcacoes.index',
                'create' => 'embarcacoes.create',
                'store' => 'embarcacoes.store',
                'show' => 'embarcacoes.show',
                'edit' => 'embarcacoes.edit',
                'update' => 'embarcacoes.update',
                'destroy' => 'embarcacoes.destroy'
            ]);
    
    // Rotas adicionais para ações específicas das embarcações de concessionárias
    Route::get('/embarcacoes-export', [EmbarcacaoConcessionariaController::class, 'export'])->name('embarcacoes.export');
    Route::get('/embarcacoes-relatorio-filtrado', [EmbarcacaoConcessionariaController::class, 'relatorioFiltrado'])->name('embarcacoes.relatorio-filtrado');
    Route::post('/embarcacoes-import', [EmbarcacaoConcessionariaController::class, 'import'])->name('embarcacoes.import');
    Route::get('/embarcacoes-api', [EmbarcacaoConcessionariaController::class, 'api'])->name('embarcacoes.api');
    Route::get('/embarcacoes-dashboard', [EmbarcacaoConcessionariaController::class, 'dashboard'])->name('embarcacoes.dashboard');

        // Rotas para Entrada/Saída de Embarcações de Concessionárias
    Route::resource('entrada-saida-embarcacao-concessionaria', EntradaSaidaEmbarcacaoConcessionariaController::class, [
        'parameters' => ['entrada-saida-embarcacao-concessionaria' => 'entradaSaidaConcessionaria']
    ])->names([
        'index' => 'entrada-saida-embarcacao-concessionaria.index',
        'create' => 'entrada-saida-embarcacao-concessionaria.create',
        'store' => 'entrada-saida-embarcacao-concessionaria.store',
        'show' => 'entrada-saida-embarcacao-concessionaria.show',
        'edit' => 'entrada-saida-embarcacao-concessionaria.edit',
        'update' => 'entrada-saida-embarcacao-concessionaria.update',
        'destroy' => 'entrada-saida-embarcacao-concessionaria.destroy'
    ]);
    
    // Rotas adicionais para ações específicas dos movimentos de entrada/saída de concessionárias
    Route::post('/entrada-saida-embarcacao-concessionaria/{entradaSaidaConcessionaria}/autorizar', [EntradaSaidaEmbarcacaoConcessionariaController::class, 'autorizar'])->name('entrada-saida-embarcacao-concessionaria.autorizar');
    Route::post('/entrada-saida-embarcacao-concessionaria/{entradaSaidaConcessionaria}/iniciar', [EntradaSaidaEmbarcacaoConcessionariaController::class, 'iniciar'])->name('entrada-saida-embarcacao-concessionaria.iniciar');
    Route::post('/entrada-saida-embarcacao-concessionaria/{entradaSaidaConcessionaria}/concluir', [EntradaSaidaEmbarcacaoConcessionariaController::class, 'concluir'])->name('entrada-saida-embarcacao-concessionaria.concluir');
    Route::post('/entrada-saida-embarcacao-concessionaria/{entradaSaidaConcessionaria}/cancelar', [EntradaSaidaEmbarcacaoConcessionariaController::class, 'cancelar'])->name('entrada-saida-embarcacao-concessionaria.cancelar');
    Route::get('/entrada-saida-embarcacao-concessionaria-relatorio-filtrado', [EntradaSaidaEmbarcacaoConcessionariaController::class, 'relatorioFiltrado'])->name('entrada-saida-embarcacao-concessionaria.relatorio-filtrado');
    Route::get('/entrada-saida-embarcacao-concessionaria-dashboard', [EntradaSaidaEmbarcacaoConcessionariaController::class, 'dashboard'])->name('entrada-saida-embarcacao-concessionaria.dashboard');
    Route::get('/entrada-saida-embarcacao-concessionaria-api', [EntradaSaidaEmbarcacaoConcessionariaController::class, 'api'])->name('entrada-saida-embarcacao-concessionaria.api');
    Route::get('/entrada-saida-embarcacao-concessionaria-relatorio', [EntradaSaidaEmbarcacaoConcessionariaController::class, 'relatorio'])->name('entrada-saida-embarcacao-concessionaria.relatorio');
    
  });
         
  Route::resource('pedidos', PedidoController::class);
        Route::post('/pedidos/{pedido}/aprovar', [PedidoController::class, 'aprovar'])->name('pedidos.aprovar');
        Route::post('/pedidos/{pedido}/rejeitar', [PedidoController::class, 'rejeitar'])->name('pedidos.rejeitar');
        Route::get('pedidos/{pedido}/arquivo/{tipo}/{index?}', [PedidoController::class, 'downloadArquivo'])->name('pedidos.download-arquivo');
        
       

}); // Fechamento do grupo middleware ['auth', '2fa']

require __DIR__.'/auth.php';
//require __DIR__.'/janela-unica.php';

// Remover também a rota duplicada do dashboard que está fora do middleware 2fa