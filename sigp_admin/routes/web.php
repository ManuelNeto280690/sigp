<?php


use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\TwoFactorAuthController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EntradaSaidaEmbarcacaoConcessionariaController;
use App\Http\Controllers\PortoController;

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
    return view('trafego');
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

    // Rotas para Portos
    Route::resource('portos', PortoController::class);

     // Rotas para Usuários
    Route::resource('users', UserController::class);
     // Rotas adicionais para ações específicas dos usuários
    Route::post('/users/{user}/block', [UserController::class, 'block'])->name('users.block');
    Route::post('/users/{user}/unblock', [UserController::class, 'unblock'])->name('users.unblock');
    Route::post('/users/{user}/reset-attempts', [UserController::class, 'resetFailedAttempts'])->name('users.reset-attempts');
    Route::post('/users/{user}/reset-2fa', [UserController::class, 'resetTwoFactor'])->name('users.reset-2fa');


    
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
    
    
  });
         


require __DIR__.'/auth.php';
//require __DIR__.'/janela-unica.php';

// Remover também a rota duplicada do dashboard que está fora do middleware 2fa