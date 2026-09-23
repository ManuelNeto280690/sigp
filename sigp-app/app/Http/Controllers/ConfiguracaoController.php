<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ConfiguracaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('role:admin', ['except' => ['index', 'show', 'create']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Configuracao::query();

            // Filtros
            if ($request->filled('categoria')) {
                $query->where('categoria', $request->categoria);
            }

            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            if ($request->filled('ativo')) {
                $query->where('ativo', $request->ativo === 'true');
            }

            if ($request->filled('busca')) {
                $query->where(function($q) use ($request) {
                    $q->where('chave', 'like', '%' . $request->busca . '%')
                      ->orWhere('nome', 'like', '%' . $request->busca . '%')
                      ->orWhere('descricao', 'like', '%' . $request->busca . '%');
                });
            }

            $configuracoes = $query->paginate(20);

            // Agrupar por categoria para melhor visualização
            $configuracoesAgrupadas = $configuracoes->groupBy('categoria');

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'view',
                'auditable_type' => 'Configuracao',
                'auditable_id' => null,
                'description' => 'Visualizou listagem de configurações',
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return view('configuracoes.index', compact('configuracoes', 'configuracoesAgrupadas'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar configurações: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar configurações: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       // $this->authorize('create', Configuracao::class);

        // Log da ação
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'view',
            'auditable_type' => 'Configuracao',
            'auditable_id' => null,
            'description' => 'Acessou formulário de criação de configuração',
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return view('configuracoes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //$this->authorize('create', Configuracao::class);

        $request->validate([
            'chave' => 'required|string|max:100',
            'nome' => 'required|string|max:200',
            'descricao' => 'nullable|string|max:1000',
            'valor' => 'required|string|max:2000',
            'tipo' => 'required|in:string,integer,float,boolean,json,textarea,file,email,url,date,time,datetime',
            'categoria' => 'required|in:sistema,email,empresa,seguranca,interface,relatorios,notificacoes,backup,integracao',
            'validacao' => 'nullable|string|max:500',
            'opcoes' => 'nullable|json',
            'ativo' => 'boolean',
            'publico' => 'boolean',
            'arquivo' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,txt|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $valor = $request->valor;

            // Upload de arquivo se necessário
            if ($request->tipo === 'file' && $request->hasFile('arquivo')) {
                $path = $request->file('arquivo')->store('configuracoes', 'public');
                $valor = $path;
            }

            // Validar valor baseado no tipo
            $this->validarValorPorTipo($request->tipo, $valor, $request->validacao);

            $configuracao = Configuracao::create([
                'chave' => $request->chave,
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'valor' => $valor,
                'tipo' => $request->tipo,
                'categoria' => $request->categoria,
                'validacao' => $request->validacao,
                'opcoes' => $request->opcoes ? json_decode($request->opcoes, true) : null,
                'ativo' => $request->boolean('ativo', true),
                'publico' => $request->boolean('publico', false),
            ]);

            // Limpar cache de configurações
            Cache::forget('configuracoes');
            Cache::forget('configuracoes_publicas');

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'create',
                'auditable_type' => 'Configuracao',
                'auditable_id' => $configuracao->id,
                'description' => "Configuração criada: {$configuracao->nome} ({$configuracao->chave})",
                'new_values' => $configuracao->toArray(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            Log::info('Configuração criada com sucesso', ['configuracao_id' => $configuracao->id, 'user_id' => Auth::id()]);

            return redirect()->route('configuracoes.index', $configuracao)
                           ->with('success', 'Configuração criada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar configuração: ' . $e->getMessage());
            return back()->withInput()
                        ->with('error', 'Erro ao criar configuração: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Configuracao $configuracao)
    {
        // Log da ação
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'view',
            'auditable_type' => 'Configuracao',
            'auditable_id' => $configuracao->id,
            'description' => "Visualizou configuração: {$configuracao->nome} ({$configuracao->chave})",
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return view('configuracoes.show', compact('configuracao'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Configuracao $configuracao)
    {
       // $this->authorize('update', $configuracao);

        // Log da ação
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'view',
            'auditable_type' => 'Configuracao',
            'auditable_id' => $configuracao->id,
            'description' => "Acessou formulário de edição da configuração: {$configuracao->nome}",
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return view('configuracoes.edit', compact('configuracao'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Configuracao $configuracao)
    {
       // $this->authorize('update', $configuracao);

        $request->validate([
            'chave' => 'required|string|max:100|unique:configuracoes,chave',
            'nome' => 'required|string|max:200',
            'descricao' => 'nullable|string|max:1000',
            'valor' => 'required|string|max:2000',
            'tipo' => 'required|in:string,integer,float,boolean,json,file,email,url,date,time,datetime',
            'categoria' => 'required|in:sistema,email,empresa,seguranca,interface,relatorios,notificacoes,backup,integracao',
            'validacao' => 'nullable|string|max:500',
            'opcoes' => 'nullable|json',
            'ativo' => 'boolean',
            'publico' => 'boolean',
            'arquivo' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,txt|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $oldData = $configuracao->toArray();
            $valor = $request->valor;

            // Upload de novo arquivo se necessário
            if ($configuracao->tipo === 'file' && $request->hasFile('arquivo')) {
                // Remover arquivo anterior
                if ($configuracao->valor && Storage::disk('public')->exists($configuracao->valor)) {
                    Storage::disk('public')->delete($configuracao->valor);
                }
                
                $path = $request->file('arquivo')->store('configuracoes', 'public');
                $valor = $path;
            }

            // Validar valor baseado no tipo
            $this->validarValorPorTipo($configuracao->tipo, $valor, $request->validacao);

            $configuracao->update([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'valor' => $valor,
                'validacao' => $request->validacao,
                'opcoes' => $request->opcoes ? json_decode($request->opcoes, true) : null,
                'ativo' => $request->boolean('ativo', true),
                'publico' => $request->boolean('publico', false),
            ]);

            // Limpar cache de configurações
            Cache::forget('configuracoes');
            Cache::forget('configuracoes_publicas');
            Cache::forget('config_' . $configuracao->chave);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'Configuracao',
                'auditable_id' => $configuracao->id,
                'description' => "Configuração atualizada: {$configuracao->nome} ({$configuracao->chave})",
                'old_values' => $oldData,
                'new_values' => $configuracao->fresh()->toArray(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            Log::info('Configuração atualizada com sucesso', ['configuracao_id' => $configuracao->id, 'user_id' => Auth::id()]);

            return redirect()->route('configuracoes.show', $configuracao)
                           ->with('success', 'Configuração atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar configuração: ' . $e->getMessage());
            return back()->withInput()
                        ->with('error', 'Erro ao atualizar configuração: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Configuracao $configuracao)
    {
       // $this->authorize('delete', $configuracao);

        try {
            DB::beginTransaction();

            $oldData = $configuracao->toArray();

            // Remover arquivo se existir
            if ($configuracao->tipo === 'file' && $configuracao->valor) {
                if (Storage::disk('public')->exists($configuracao->valor)) {
                    Storage::disk('public')->delete($configuracao->valor);
                }
            }

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'delete',
                'auditable_type' => 'Configuracao',
                'auditable_id' => $configuracao->id,
                'description' => "Configuração excluída: {$configuracao->nome} ({$configuracao->chave})",
                'old_values' => $oldData,
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $configuracao->delete();

            // Limpar cache de configurações
            Cache::forget('configuracoes');
            Cache::forget('configuracoes_publicas');
            Cache::forget('config_' . $configuracao->chave);

            DB::commit();

            Log::info('Configuração excluída com sucesso', ['configuracao_id' => $configuracao->id, 'user_id' => Auth::id()]);

            return redirect()->route('configuracoes.index')
                           ->with('success', 'Configuração excluída com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir configuração: ' . $e->getMessage());
            return back()->with('error', 'Erro ao excluir configuração: ' . $e->getMessage());
        }
    }

    /**
     * Ativar/Desativar configuração
     */
    public function toggleStatus(Configuracao $configuracao)
    {
       // $this->authorize('update', $configuracao);

        try {
            $oldStatus = $configuracao->ativo;
            
            $configuracao->update([
                'ativo' => !$configuracao->ativo
            ]);

            // Limpar cache
            Cache::forget('configuracoes');
            Cache::forget('configuracoes_publicas');
            Cache::forget('config_' . $configuracao->chave);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'Configuracao',
                'auditable_id' => $configuracao->id,
                'description' => "Status da configuração alterado: {$configuracao->nome} - " . ($configuracao->ativo ? 'Ativada' : 'Desativada'),
                'old_values' => ['ativo' => $oldStatus],
                'new_values' => ['ativo' => $configuracao->ativo],
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $status = $configuracao->ativo ? 'ativada' : 'desativada';
            Log::info("Configuração {$status}", ['configuracao_id' => $configuracao->id, 'user_id' => Auth::id()]);
            
            return back()->with('success', "Configuração {$status} com sucesso!");
        } catch (\Exception $e) {
            Log::error('Erro ao alterar status da configuração: ' . $e->getMessage());
            return back()->with('error', 'Erro ao alterar status: ' . $e->getMessage());
        }
    }

    /**
     * Backup das configurações
     */
    public function backup(Request $request)
    {
       // $this->authorize('create', Configuracao::class);

        try {
            $configuracoes = Configuracao::all();
            
            $backup = [
                'data_backup' => now()->toISOString(),
                'usuario' => Auth::user()->name,
                'total_configuracoes' => $configuracoes->count(),
                'configuracoes' => $configuracoes->toArray()
            ];

            $filename = 'backup_configuracoes_' . now()->format('Y-m-d_H-i-s') . '.json';
            $path = 'backups/configuracoes/' . $filename;
            
            Storage::disk('local')->put($path, json_encode($backup, JSON_PRETTY_PRINT));

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'backup',
                'auditable_type' => 'Configuracao',
                'auditable_id' => null,
                'description' => "Backup de configurações gerado: {$filename} ({$configuracoes->count()} configurações)",
                'new_values' => ['arquivo' => $filename, 'total' => $configuracoes->count()],
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Log::info('Backup de configurações gerado', ['filename' => $filename, 'total' => $configuracoes->count(), 'user_id' => Auth::id()]);

            return response()->download(storage_path('app/' . $path), $filename)
                           ->deleteFileAfterSend(false);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar backup de configurações: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar backup: ' . $e->getMessage());
        }
    }

    /**
     * Restaurar configurações do backup
     */
    public function restore(Request $request)
    {
       // $this->authorize('create', Configuracao::class);

        $request->validate([
            'backup_file' => 'required|file|mimes:json|max:10240',
            'sobrescrever' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $content = file_get_contents($request->file('backup_file')->getRealPath());
            $backup = json_decode($content, true);

            if (!$backup || !isset($backup['configuracoes'])) {
                throw new \Exception('Arquivo de backup inválido.');
            }

            $restored = 0;
            $skipped = 0;
            $errors = [];

            foreach ($backup['configuracoes'] as $configData) {
                try {
                    $existing = Configuracao::where('chave', $configData['chave'])->first();

                    if ($existing && !$request->boolean('sobrescrever')) {
                        $skipped++;
                        continue;
                    }

                    if ($existing) {
                        $existing->update([
                            'nome' => $configData['nome'],
                            'descricao' => $configData['descricao'],
                            'valor' => $configData['valor'],
                            'tipo' => $configData['tipo'],
                            'categoria' => $configData['categoria'],
                            'validacao' => $configData['validacao'],
                            'opcoes' => $configData['opcoes'],
                            'ativo' => $configData['ativo'],
                            'publico' => $configData['publico'],
                        ]);
                    } else {
                        Configuracao::create([
                            'chave' => $configData['chave'],
                            'nome' => $configData['nome'],
                            'descricao' => $configData['descricao'],
                            'valor' => $configData['valor'],
                            'tipo' => $configData['tipo'],
                            'categoria' => $configData['categoria'],
                            'validacao' => $configData['validacao'],
                            'opcoes' => $configData['opcoes'],
                            'ativo' => $configData['ativo'],
                            'publico' => $configData['publico'],
                        ]);
                    }

                    $restored++;
                } catch (\Exception $e) {
                    $errors[] = "Erro na configuração {$configData['chave']}: " . $e->getMessage();
                }
            }

            // Limpar cache
            Cache::forget('configuracoes');
            Cache::forget('configuracoes_publicas');

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'restore',
                'auditable_type' => 'Configuracao',
                'auditable_id' => null,
                'description' => "Backup de configurações restaurado: {$restored} restauradas, {$skipped} ignoradas, " . count($errors) . " com erro",
                'new_values' => [
                    'restored' => $restored,
                    'skipped' => $skipped,
                    'errors' => count($errors),
                    'error_details' => $errors
                ],
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            $message = "Backup restaurado! {$restored} configurações restauradas";
            if ($skipped > 0) {
                $message .= ", {$skipped} ignoradas";
            }
            if (count($errors) > 0) {
                $message .= ", " . count($errors) . " com erro";
            }

            Log::info('Backup de configurações restaurado', [
                'restored' => $restored, 
                'skipped' => $skipped, 
                'errors' => count($errors), 
                'user_id' => Auth::id()
            ]);

            return back()->with('success', $message)
                        ->with('restore_errors', $errors);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao restaurar backup de configurações: ' . $e->getMessage());
            return back()->with('error', 'Erro ao restaurar backup: ' . $e->getMessage());
        }
    }

    /**
     * Resetar configurações para padrão
     */
    public function resetToDefault(Request $request)
    {
       // $this->authorize('create', Configuracao::class);

        try {
            DB::beginTransaction();

            // Carregar configurações padrão
            $defaultConfigs = $this->getDefaultConfigurations();
            $reset = 0;
            $resetDetails = [];

            foreach ($defaultConfigs as $config) {
                $existing = Configuracao::where('chave', $config['chave'])->first();
                
                if ($existing) {
                    $oldValue = $existing->valor;
                    $existing->update(['valor' => $config['valor']]);
                    $reset++;
                    $resetDetails[] = [
                        'chave' => $config['chave'],
                        'valor_anterior' => $oldValue,
                        'valor_novo' => $config['valor']
                    ];
                }
            }

            // Limpar cache
            Cache::forget('configuracoes');
            Cache::forget('configuracoes_publicas');

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'reset',
                'auditable_type' => 'Configuracao',
                'auditable_id' => null,
                'description' => "Configurações resetadas para padrão: {$reset} configurações alteradas",
                'new_values' => [
                    'reset_count' => $reset,
                    'reset_details' => $resetDetails
                ],
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            Log::info('Configurações resetadas para padrão', ['reset_count' => $reset, 'user_id' => Auth::id()]);

            return back()->with('success', "{$reset} configurações resetadas para o padrão!");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao resetar configurações: ' . $e->getMessage());
            return back()->with('error', 'Erro ao resetar configurações: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint para configurações públicas
     */
    public function api()
    {
        try {
            $configuracoes = Cache::remember('configuracoes_publicas', 3600, function () {
                return Configuracao::where('publico', true)
                                 ->where('ativo', true)
                                 ->select('chave', 'valor', 'tipo')
                                 ->get()
                                 ->mapWithKeys(function ($config) {
                                     return [$config->chave => $this->formatarValor($config->valor, $config->tipo)];
                                 });
            });

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'api_access',
                'auditable_type' => 'Configuracao',
                'auditable_id' => null,
                'description' => 'Acessou API de configurações públicas',
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $configuracoes,
                'message' => 'Configurações públicas carregadas com sucesso'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar configurações via API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar configurações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export configurações to various formats.
     */
    public function export(Request $request)
    {
        try {
            $format = $request->get('format', 'csv');
            $query = Configuracao::query()->where('is_active', true);

            // Aplicar filtros se fornecidos
            if ($request->filled('categoria')) {
                $query->where('categoria', $request->categoria);
            }

            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            if ($request->filled('busca')) {
                $query->where(function($q) use ($request) {
                    $q->where('chave', 'like', '%' . $request->busca . '%')
                      ->orWhere('descricao', 'like', '%' . $request->busca . '%');
                });
            }

            $configuracoes = $query->orderBy('categoria')->orderBy('chave')->get();
            $filename = 'configuracoes_' . now()->format('Y-m-d_H-i-s');

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'export',
                'auditable_type' => 'Configuracao',
                'auditable_id' => null,
                'description' => "Exportação de configurações realizada (formato: {$format})",
                'new_values' => [
                    'formato' => $format,
                    'total_registros' => $configuracoes->count(),
                    'filtros' => $request->only(['categoria', 'tipo', 'busca'])
                ],
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Log::info('Exportação de configurações iniciada', [
                'formato' => $format,
                'total' => $configuracoes->count(),
                'user_id' => Auth::id()
            ]);

            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($configuracoes, $filename);
                case 'excel':
                    return $this->exportToExcel($configuracoes, $filename);
                case 'pdf':
                    return $this->exportToPdf($configuracoes, $filename);
                default:
                    return redirect()->back()->with('error', 'Formato de exportação inválido.');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao exportar configurações: ' . $e->getMessage());
            return back()->with('error', 'Erro ao exportar configurações: ' . $e->getMessage());
        }
    }

    /**
     * Export to CSV format.
     */
    private function exportToCsv($configuracoes, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($configuracoes) {
            $file = fopen('php://output', 'w');
            
            // Cabeçalhos CSV
            fputcsv($file, [
                'ID',
                'Chave',
                'Valor',
                'Tipo',
                'Descrição',
                'Categoria',
                'Editável',
                'Ativo',
                'Criado em',
                'Atualizado em'
            ]);

            // Dados
            foreach ($configuracoes as $config) {
                fputcsv($file, [
                    $config->id,
                    $config->chave,
                    $config->valor,
                    $config->tipo,
                    $config->descricao,
                    $config->categoria,
                    $config->editavel ? 'Sim' : 'Não',
                    $config->is_active ? 'Sim' : 'Não',
                    $config->created_at->format('d/m/Y H:i:s'),
                    $config->updated_at->format('d/m/Y H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel format.
     */
    private function exportToExcel($configuracoes, $filename)
    {
        // Por enquanto, usar CSV até implementar PhpSpreadsheet
        return $this->exportToCsv($configuracoes, $filename);
    }

    /**
     * Export to PDF format.
     */
    private function exportToPdf($configuracoes, $filename)
    {
        // Implementação básica - pode ser melhorada com DomPDF
        return redirect()->back()->with('info', 'Exportação PDF será implementada em breve. Use CSV por enquanto.');
    }

    /**
     * Clear configurations cache.
     */
    public function clearCache(Request $request)
    {
        try {
            // Limpar cache do Laravel
            Cache::flush();
            
            // Limpar cache de configurações específico se existir
            Cache::forget('configuracoes_cache');
            Cache::forget('configuracoes_ativas');
            
            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'clear_cache',
                'auditable_type' => 'Configuracao',
                'auditable_id' => null,
                'description' => 'Cache de configurações limpo',
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Log::info('Cache de configurações limpo', [
                'user_id' => Auth::id(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cache limpo com sucesso!'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache de configurações: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update multiple configurations in batch.
     */
    public function updateBatch(Request $request)
    {
        try {
            $configuracoes = $request->input('configs', []);
            $updatedCount = 0;
            
            DB::beginTransaction();
            
            foreach ($configuracoes as $id => $data) {
                $configuracao = Configuracao::find($id);
                if ($configuracao) {
                    $valorAntigo = $configuracao->valor;
                    $novoValor = $valorAntigo;
                    
                    // Tratar upload de arquivo para configurações do tipo 'file'
                    if ($configuracao->tipo === 'file' && $request->hasFile("configs.{$id}.file")) {
                        $file = $request->file("configs.{$id}.file");
                        
                        // Validar arquivo
                        $request->validate([
                            "configs.{$id}.file" => 'image|mimes:jpeg,png,jpg,gif|max:2048'
                        ]);
                        
                        // Remover arquivo anterior se existir
                        if ($valorAntigo && Storage::disk('public')->exists($valorAntigo)) {
                            Storage::disk('public')->delete($valorAntigo);
                        }
                        
                        // Salvar novo arquivo
                        $path = $file->store('configuracoes/logos', 'public');
                        $novoValor = $path;
                    } elseif (isset($data['valor'])) {
                        // Para outros tipos de configuração
                        $novoValor = $data['valor'];
                    }
                    
                    // Atualizar status se fornecido
                    if (isset($data['is_active'])) {
                        $configuracao->is_active = (bool) $data['is_active'];
                    }
                    
                    // Atualizar valor se mudou
                    if ($novoValor !== $valorAntigo) {
                        $configuracao->valor = $novoValor;
                        
                        // Log da alteração
                        AuditLog::create([
                            'user_id' => Auth::id(),
                            'event' => 'updated',
                            'auditable_type' => 'Configuracao',
                            'auditable_id' => $configuracao->id,
                            'description' => "Configuração '{$configuracao->chave}' alterada de '{$valorAntigo}' para '{$novoValor}'",
                            'url' => $request->fullUrl(),
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'old_values' => ['valor' => $valorAntigo],
                            'new_values' => ['valor' => $novoValor],
                        ]);
                        
                        $updatedCount++;
                    }
                    
                    $configuracao->save();
                }
            }
            
            DB::commit();
            
            // Limpar cache de configurações
            Cache::forget('configuracoes_cache');
            Cache::forget('configuracoes_ativas');
            
            Log::info('Configurações atualizadas em lote', [
                'user_id' => Auth::id(),
                'count' => $updatedCount,
                'ip' => $request->ip()
            ]);
            
            return redirect()->back()->with('success', "Configurações atualizadas com sucesso! ({$updatedCount} alterações)");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar configurações em lote: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Erro ao atualizar configurações: ' . $e->getMessage());
        }
    }

    /**
     * Validar valor baseado no tipo da configuração
     */
    private function validarValorPorTipo($tipo, $valor, $validacao = null)
    {
        switch ($tipo) {
            case 'integer':
                if (!is_numeric($valor) || !filter_var($valor, FILTER_VALIDATE_INT)) {
                    throw new \InvalidArgumentException('O valor deve ser um número inteiro válido.');
                }
                break;

            case 'float':
                if (!is_numeric($valor)) {
                    throw new \InvalidArgumentException('O valor deve ser um número decimal válido.');
                }
                break;

            case 'boolean':
                if (!in_array(strtolower($valor), ['true', 'false', '1', '0', 'sim', 'não', 'yes', 'no'])) {
                    throw new \InvalidArgumentException('O valor deve ser um booleano válido (true/false, 1/0, sim/não).');
                }
                break;

            case 'json':
                $decoded = json_decode($valor, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \InvalidArgumentException('O valor deve ser um JSON válido: ' . json_last_error_msg());
                }
                break;

            case 'email':
                if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                    throw new \InvalidArgumentException('O valor deve ser um endereço de email válido.');
                }
                break;

            case 'url':
                if (!filter_var($valor, FILTER_VALIDATE_URL)) {
                    throw new \InvalidArgumentException('O valor deve ser uma URL válida.');
                }
                break;

            case 'date':
                if (!strtotime($valor)) {
                    throw new \InvalidArgumentException('O valor deve ser uma data válida.');
                }
                break;

            case 'time':
                if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $valor)) {
                    throw new \InvalidArgumentException('O valor deve ser um horário válido (HH:MM ou HH:MM:SS).');
                }
                break;

            case 'datetime':
                if (!strtotime($valor)) {
                    throw new \InvalidArgumentException('O valor deve ser uma data e hora válida.');
                }
                break;

            case 'textarea':
            case 'string':
            case 'file':
            default:
                // Para string, textarea e file, não há validação específica além do que já foi feito
                break;
        }

        // Aplicar validação customizada se fornecida
        if ($validacao && $tipo !== 'file') {
            if (!preg_match($validacao, $valor)) {
                throw new \InvalidArgumentException('O valor não atende aos critérios de validação definidos.');
            }
        }

        return true;
    }
}