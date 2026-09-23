<?php

namespace App\Http\Controllers;

use App\Models\InfracaoAmbiental;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Embarcacao;
use App\Models\Terminal;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Support\Facades\Log;

class InfracaoAmbientalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('role:admin|inspector_ambiental', ['except' => ['index', 'show', 'create', 'deelete']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = InfracaoAmbiental::with(['embarcacao', 'terminal']);

            // Filtros para concessionárias
            if (Auth::user()->hasRole('concessionaria')) {
                $query->where('concessionaria_id', Auth::user()->concessionaria_id);
            }

            // Filtros
            if ($request->filled('numero')) {
                $query->where('numero', 'like', '%' . $request->numero . '%');
            }

            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            if ($request->filled('gravidade')) {
                $query->where('gravidade', $request->gravidade);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('embarcacao_id')) {
                $query->where('embarcacao_id', $request->embarcacao_id);
            }

           

            if ($request->filled('data_inicio')) {
                $query->whereDate('data_infracao', '>=', $request->data_inicio);
            }

            if ($request->filled('data_fim')) {
                $query->whereDate('data_infracao', '<=', $request->data_fim);
            }

            if ($request->filled('valor_min')) {
                $query->where('valor_multa', '>=', $request->valor_min);
            }

            if ($request->filled('valor_max')) {
                $query->where('valor_multa', '<=', $request->valor_max);
            }

            // Ordenação
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            $infracoes = $query->paginate(15);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'view',
                'auditable_type' => 'InfracaoAmbiental',
                'auditable_id' => null,
                'description' => 'Visualizou listagem de Infracao Ambiental',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            return view('infracoes-ambientais.index', compact('infracoes'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar infrações: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       // $this->authorize('create', InfracaoAmbiental::class);
        $embarcacoes = Embarcacao::where('is_active', true)->get();
         $terminais = Terminal::where('is_active', true)->get();

        return view('infracoes-ambientais.create', compact('embarcacoes', 'terminais'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //$this->authorize('create', InfracaoAmbiental::class);

        $request->validate([
            'embarcacao_id' => 'nullable|exists:embarcacoes,id',
            'tipo_infracao' => 'required|string',
            'gravidade' => 'required|string',
            'descricao_infracao' => 'required|string|max:2000',
            'data_infracao' => 'required|date',
            'local_infracao' => 'required|string|max:500',
          //  'valor_multa' => 'nullable|numeric|min:0',
           // 'prazo_regularizacao' => 'required|date|after:today',
            'medidas_corretivas' => 'string|max:1000',
            'evidencias' =>'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
            'observacoes' => 'nullable|string|max:1000',
        ]);

        try {
            Log::info('Iniciando criação de infração ambiental', [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            DB::beginTransaction();

            // Gerar número único
            $numero = $this->generateNumeroInfracao();
            Log::info('Número gerado para infração', ['numero' => $numero]);

            $infracao = InfracaoAmbiental::create([
                'numero_auto' => $numero,
                'embarcacao_id' => $request->embarcacao_id,
                'terminal_id' => $request->terminal_id,
                'user_id' => Auth::id(),
                'tipo_infracao' => $request->tipo_infracao,
                'descricao_infracao' => $request->descricao_infracao,
                'data_infracao' => $request->data_infracao,
                'local_infracao' => $request->local_infracao,
                'gravidade' => $request->gravidade,
               // 'valor_multa' => $request->valor_multa,
              //  'prazo_regularizacao' => $request->prazo_regularizacao,
                'medidas_corretivas' => $request->medidas_corretivas,
                'observacoes' => $request->observacoes,
                'status' => 'registrada',
                'is_active' => true
            ]);

            Log::info('Infração criada com sucesso', ['infracao_id' => $infracao->id]);

            // Upload de evidências
            if ($request->hasFile('evidencias')) {
                Log::info('Processando upload de evidências');
                $evidencias = [];
                foreach ($request->file('evidencias') as $file) {
                    $path = $file->store('infracoes/' . $infracao->id . '/evidencias', 'public');
                    $evidencias[] = [
                        'nome' => $file->getClientOriginalName(),
                        'path' => $path,
                        'tipo' => $file->getClientMimeType(),
                        'tamanho' => $file->getSize(),
                    ];
                }
                $infracao->update(['evidencias' => $evidencias]);
                Log::info('Evidências processadas', ['count' => count($evidencias)]);
            }

            // Log da ação
            Log::info('Criando log de auditoria');
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'created',
                'auditable_type' => 'InfracaoAmbiental',
                'auditable_id' => $infracao->id,
                'description' => "Infração ambiental criada: {$infracao->numero_auto}",
                'new_values' => $infracao->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            Log::info('Log de auditoria criado com sucesso');

            DB::commit();
            Log::info('Transação commitada com sucesso');

            return redirect()->route('infracoes-ambientais.show', $infracao)
                           ->with('success', 'Infração ambiental registrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao registrar infração ambiental', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);
            return back()->withInput()
                        ->with('error', 'Erro ao registrar infração: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    { //InfracaoAmbiental $infracao, Request $request
        //$this->authorize('view', $infracao);

     
        $infracao = InfracaoAmbiental::with(['embarcacao', 'terminal', 'inspetor', 'aprovadoPor'])->findOrFail($id);

        // Log da ação
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'view',
            'auditable_type' => 'InfracaoAmbiental',
            'auditable_id' => null,
            'description' => 'Visualizou listagem de infrações ambientais',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl()
        ]);

        return view('infracoes-ambientais.show', compact('infracao'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //InfracaoAmbiental $infracaoAmbiental
       // $this->authorize('update', $infracaoAmbiental);
        $embarcacoes = Embarcacao::where('is_active', true)->get();
         $terminais = Terminal::where('is_active', true)->get();

        // Só permite edição se estiver em status que permite
       /* if (!in_array($infracaoAmbiental->status, ['registrada', 'em_analise'])) {
            return back()->with('error', 'Infração não pode ser editada no status atual.');
        }*/
          $infracao = InfracaoAmbiental::findOrFail($id);
      

        return view('infracoes-ambientais.edit', compact('infracao', 'embarcacoes', 'terminais'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
      
       // $this->authorize('update', $infracao);
       $infracao = InfracaoAmbiental::findOrFail($id);

        // Só permite edição se estiver em status que permite
     /*   if (!in_array($infracao->status, ['registrada', 'em_analise'])) {
            return back()->with('error', 'Infração não pode ser editada no status atual.');
        }*/

        $request->validate([
            'embarcacao_id' => 'nullable|exists:embarcacoes,id',
            'tipo_infracao' => 'required|string',
            'gravidade' => 'required|string',
            'descricao_infracao' => 'required|string|max:2000',
            'data_infracao' => 'required|date',
            'local_infracao' => 'required|string',
           // 'valor_multa' => 'nullable|numeric|min:0',
           // 'prazo_regularizacao' => 'required|date|after:today',
            'medidas_corretivas' => 'string|max:1000',
            //'evidencias' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
            'observacoes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $oldData = $infracao->toArray();

            $infracao->update([
                'embarcacao_id' => $request->embarcacao_id,
                'terminal_id' => $request->terminal_id,
                'tipo_infracao' => $request->tipo_infracao,
                'descricao_infracao' => $request->descricao_infracao,
                'data_infracao' => $request->data_infracao,
                'local_infracao' => $request->local_infracao,
                'gravidade' => $request->gravidade,
               // 'valor_multa' => $request->valor_multa,
               // 'prazo_regularizacao' => $request->prazo_regularizacao,
                //'medidas_corretivas' => $request->medidas_corretivas,
                'observacoes' => $request->observacoes,
                'status' => $request->status,
            ]);

            // Upload de novas evidências
            if ($request->hasFile('evidencias')) {
                $evidenciasExistentes = $infracao->evidencias ?? [];
                foreach ($request->file('evidencias') as $file) {
                    $path = $file->store('infracoes/' . $infracao->id . '/evidencias', 'public');
                    $evidenciasExistentes[] = [
                        'nome' => $file->getClientOriginalName(),
                        'path' => $path,
                        'tipo' => $file->getClientMimeType(),
                        'tamanho' => $file->getSize(),
                    ];
                }
                $infracao->update(['evidencias' => $evidenciasExistentes]);
            }

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'auditable_type' => 'InfracaoAmbiental',
                'auditable_id' => $infracao->id,
                'description' => "Infração ambiental atualizada: {$infracao->numero_auto}",
                'old_values' => $oldData,
                'new_values' =>  $infracao->getChanges(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            return redirect()->route('infracoes-ambientais.index')
                           ->with('success', 'Infração atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'Erro ao atualizar infração: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        //$this->authorize('delete', $infracao);


            $id=$request->id;

            $infracao = InfracaoAmbiental::findOrFail($id);
        // Só permite exclusão se estiver em status que permite
        if (!in_array($infracao->status, ['registrada'])) {
            return back()->with('error', 'Infração não pode ser excluída no status atual.');
        }

        try {
            DB::beginTransaction();

            // Log da ação antes de deletar
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'deleted',
                'auditable_type' => 'InfracaoAmbiental',
                'auditable_id' => $infracao->id,
                'description' => "Infração ambiental excluída: {$infracao->numero_auto}",
                'old_values' => $infracao->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl()
            ]);

            $infracao->delete();

            DB::commit();

            return redirect()->route('infracoes-ambientais.index')
                           ->with('success', 'Infração excluída com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao excluir infração: ' . $e->getMessage());
        }
    }

    /**
     * Notificar infração
     */
    public function notificar(InfracaoAmbiental $infracao)
    {
        $this->authorize('update', $infracao);

        if ($infracao->status !== 'registrada') {
            return back()->with('error', 'Infração não pode ser notificada no status atual.');
        }

        try {
            DB::beginTransaction();

            $oldData = $infracao->toArray();

            $infracao->update([
                'status' => 'notificada',
                'data_notificacao' => now(),
                'notificado_por' => Auth::id(),
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'notified',
                'auditable_type' => 'InfracaoAmbiental',
                'auditable_id' => $infracao->id,
                'description' => "Infração ambiental notificada: {$infracao->numero_auto}",
                'old_values' => $oldData,
                'new_values' => $infracao->fresh()->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl()
            ]);

            DB::commit();

            return back()->with('success', 'Infração notificada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao notificar infração: ' . $e->getMessage());
        }
    }

    /**
     * Processar defesa
     */
    public function processarDefesa(Request $request, InfracaoAmbiental $infracao)
    {
        $this->authorize('update', $infracao);

        $request->validate([
            'decisao' => 'required|in:deferida,indeferida',
            'justificativa' => 'required|string|max:2000',
            'valor_final' => 'nullable|numeric|min:0',
        ]);

        if (!in_array($infracao->status, ['defesa_apresentada', 'em_analise'])) {
            return back()->with('error', 'Infração não pode ter defesa processada no status atual.');
        }

        try {
            DB::beginTransaction();

            $oldData = $infracao->toArray();
            $status = $request->decisao === 'deferida' ? 'defesa_deferida' : 'defesa_indeferida';
            $valorFinal = $request->decisao === 'deferida' ? ($request->valor_final ?? 0) : $infracao->valor_multa;

            $infracao->update([
                'status' => $status,
                'decisao_defesa' => $request->decisao,
                'justificativa_decisao' => $request->justificativa,
                'valor_final_multa' => $valorFinal,
                'data_decisao' => now(),
                'decidido_por' => Auth::id(),
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'defense_processed',
                'auditable_type' => 'InfracaoAmbiental',
                'auditable_id' => $infracao->id,
                'description' => "Defesa processada para infração: {$infracao->numero_auto} - Decisão: {$request->decisao}",
                'old_values' => $oldData,
                'new_values' => $infracao->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            return back()->with('success', 'Defesa processada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao processar defesa: ' . $e->getMessage());
        }
    }

    /**
     * Marcar como paga
     */
    public function marcarPaga(Request $request, InfracaoAmbiental $infracao)
    {
        $this->authorize('update', $infracao);

        $request->validate([
            'data_pagamento' => 'required|date|before_or_equal:today',
            'valor_pago' => 'required|numeric|min:0',
            'forma_pagamento' => 'required|string|max:100',
            'comprovante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if (!in_array($infracao->status, ['defesa_indeferida', 'notificada'])) {
            return back()->with('error', 'Infração não pode ser marcada como paga no status atual.');
        }

        try {
            DB::beginTransaction();

            $oldData = $infracao->toArray();
            $comprovantePath = null;
            if ($request->hasFile('comprovante')) {
                $comprovantePath = $request->file('comprovante')
                    ->store('infracoes/' . $infracao->id . '/comprovantes', 'public');
            }

            $infracao->update([
                'status' => 'paga',
                'data_pagamento' => $request->data_pagamento,
                'valor_pago' => $request->valor_pago,
                'forma_pagamento' => $request->forma_pagamento,
                'comprovante_pagamento' => $comprovantePath,
                'recebido_por' => Auth::id(),
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'payment_received',
                'auditable_type' => 'InfracaoAmbiental',
                'auditable_id' => $infracao->id,
                'description' => "Pagamento recebido para infração: {$infracao->numero_auto} - Valor: R$ {$request->valor_pago}",
                'old_values' => $oldData,
                'new_values' => $infracao->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            return back()->with('success', 'Pagamento registrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao registrar pagamento: ' . $e->getMessage());
        }
    }

    /**
     * Dashboard de infrações
     */
    public function dashboard(Request $request)
    {
        try {
            $query = InfracaoAmbiental::query();

            

            // Filtro de período
            $periodo = $request->get('periodo', '30');
            if ($periodo !== 'todos') {
                $query->where('created_at', '>=', now()->subDays($periodo));
            }

            // Estatísticas gerais
            $totalInfracoes = $query->count();
            $infracoesPendentes = (clone $query)->whereIn('status', ['registrada', 'notificada', 'em_analise'])->count();
            $infracoesResolvidas = (clone $query)->whereIn('status', ['paga', 'defesa_deferida'])->count();
            $valorTotalMultas = (clone $query)->sum('valor_multa');
            $valorArrecadado = (clone $query)->where('status', 'paga')->sum('valor_pago');

            // Infrações por tipo
            $infracoesPorTipo = (clone $query)
                ->select('tipo_infracao', DB::raw('count(*) as total'))
                ->groupBy('tipo_infracao')
                ->pluck('total', 'tipo_infracao')
                ->toArray();

            // Infrações por gravidade
            $infracoesPorGravidade = (clone $query)
                ->select('gravidade', DB::raw('count(*) as total'))
                ->groupBy('gravidade')
                ->pluck('total', 'gravidade')
                ->toArray();

            // Infrações por status
            $infracoesPorStatus = (clone $query)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            // Infrações por mês (últimos 12 meses)
            $infracoesPorMes = (clone $query)
                ->selectRaw('YEAR(created_at) as ano, MONTH(created_at) as mes, COUNT(*) as total')
                ->where('created_at', '>=', now()->subYear())
                ->groupBy('ano', 'mes')
                ->orderBy('ano')
                ->orderBy('mes')
                ->get();

            // Top concessionárias com mais infrações (apenas para admin/operador)
            $topConcessionarias = [];
            if (Auth::user()->hasRole(['admin', 'operador'])) {
                $topConcessionarias = (clone $query)
                    ->join('embarcacoes', 'infracoes_ambientais.embarcacao_id', '=', 'embarcacoes.id')
                    ->join('concessionarias', 'embarcacoes.concessionaria_id', '=', 'concessionarias.id')
                    ->select('concessionarias.nome', DB::raw('count(*) as total'))
                    ->groupBy('concessionarias.id', 'concessionarias.nome')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get();
            }

            return view('infracoes.dashboard', compact(
                'totalInfracoes',
                'infracoesPendentes',
                'infracoesResolvidas',
                'valorTotalMultas',
                'valorArrecadado',
                'infracoesPorTipo',
                'infracoesPorGravidade',
                'infracoesPorStatus',
                'infracoesPorMes',
                'topConcessionarias',
                'periodo'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Relatório de infrações
     */
    public function relatorio(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'formato' => 'required|in:pdf,excel,csv',
        ]);

        try {
            $query = InfracaoAmbiental::with(['embarcacao', 'terminal'])
                ->whereBetween('data_infracao', [$request->data_inicio, $request->data_fim]);

            // Filtro para concessionárias
            if (Auth::user()->hasRole('concessionaria')) {
                $query->whereHas('embarcacao', function($q) {
                    $q->where('concessionaria_id', Auth::user()->concessionaria_id);
                });
            }

            $infracoes = $query->orderBy('data_infracao', 'desc')->get();

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'report_generated',
                'auditable_type' => 'InfracaoAmbiental',
                'auditable_id' => null,
                'description' => "Relatório de infrações gerado - Período: {$request->data_inicio} a {$request->data_fim} - Formato: {$request->formato}",
                'new_values' => [
                    'periodo' => $request->data_inicio . ' a ' . $request->data_fim,
                    'formato' => $request->formato,
                    'total_registros' => $infracoes->count()
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            // Gerar relatório baseado no formato
            switch ($request->formato) {
                case 'pdf':
                    // Implementar geração de PDF
                    break;
                case 'excel':
                    // Implementar geração de Excel
                    break;
                case 'csv':
                    // Implementar geração de CSV
                    break;
            }

            return back()->with('success', 'Relatório gerado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }

    /**
     * API para listagem de infrações
     */
    public function api(Request $request)
    {
        try {
            $query = InfracaoAmbiental::with(['embarcacao', 'terminal']);

            // Filtro para concessionárias
            if (Auth::user()->hasRole('concessionaria')) {
                $query->whereHas('embarcacao', function($q) {
                    $q->where('concessionaria_id', Auth::user()->concessionaria_id);
                });
            }

            // Aplicar filtros se fornecidos
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('tipo')) {
                $query->where('tipo_infracao', $request->tipo);
            }

            if ($request->filled('gravidade')) {
                $query->where('gravidade', $request->gravidade);
            }

            $infracoes = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $infracoes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar infrações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate report with filtered infrações ambientais (PDF or Excel)
     */
    public function relatorioFiltrado(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf'); // Default para PDF
            
            // Aplicar os mesmos filtros do método index
            $query = InfracaoAmbiental::with(['embarcacao', 'terminal', 'inspetor', 'aprovadoPor']);

            // Filtro de busca
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('numero_auto', 'like', '%' . $search . '%')
                      ->orWhere('descricao', 'like', '%' . $search . '%')
                      ->orWhere('local_infracao', 'like', '%' . $search . '%')
                      ->orWhereHas('embarcacao', function ($q) use ($search) {
                          $q->where('nome', 'like', '%' . $search . '%');
                      });
                });
            }

            // Filtros específicos
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('tipo_infracao')) {
                $query->where('tipo_infracao', $request->tipo_infracao);
            }

            if ($request->filled('gravidade')) {
                $query->where('gravidade', $request->gravidade);
            }

            if ($request->filled('embarcacao_id')) {
                $query->where('embarcacao_id', $request->embarcacao_id);
            }

            if ($request->filled('terminal_id')) {
                $query->where('terminal_id', $request->terminal_id);
            }

            // Filtro de período
            if ($request->filled('data_inicio') && $request->filled('data_fim')) {
                $query->whereBetween('data_infracao', [
                    Carbon::parse($request->data_inicio)->startOfDay(),
                    Carbon::parse($request->data_fim)->endOfDay()
                ]);
            }

            // Filtro de valor de multa
            if ($request->filled('valor_min')) {
                $query->where('valor_multa', '>=', $request->valor_min);
            }

            if ($request->filled('valor_max')) {
                $query->where('valor_multa', '<=', $request->valor_max);
            }

            $infracoes = $query->orderBy('data_infracao', 'desc')->get();

            // Preparar dados dos filtros aplicados
            $filtrosAplicados = [];
            if ($request->search) $filtrosAplicados['Busca'] = $request->search;
            if ($request->status) $filtrosAplicados['Status'] = $request->status;
            if ($request->tipo_infracao) $filtrosAplicados['Tipo de Infração'] = $request->tipo_infracao;
            if ($request->gravidade) $filtrosAplicados['Gravidade'] = $request->gravidade;
            if ($request->data_inicio && $request->data_fim) {
                $filtrosAplicados['Período'] = Carbon::parse($request->data_inicio)->format('d/m/Y') . ' a ' . Carbon::parse($request->data_fim)->format('d/m/Y');
            }
            if ($request->valor_min) $filtrosAplicados['Valor Mínimo'] = 'AOA ' . number_format($request->valor_min, 2, ',', '.');
            if ($request->valor_max) $filtrosAplicados['Valor Máximo'] = 'AOA ' . number_format($request->valor_max, 2, ',', '.');

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'export_report',
                'auditable_type' => 'App\\Models\\InfracaoAmbiental',
                'auditable_id' => null,
                'description' => "Gerou relatório de infrações ambientais em formato {$formato} com " . $infracoes->count() . " registros",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Gerar relatório baseado no formato
            if ($formato === 'excel') {
                return $this->generateExcelReport($infracoes, $filtrosAplicados);
            } else {
                return $this->generatePdfReport($infracoes, $filtrosAplicados);
            }

        } catch (\Exception $e) {
            Log::error('InfracaoAmbientalController@relatorioFiltrado: Erro ao gerar relatório', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'filters' => $request->all(),
                'formato' => $formato ?? 'pdf'
            ]);
            
            return back()->with('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF report
     */
    private function generatePdfReport($infracoes, $filtrosAplicados)
    {
        // Obter configurações do sistema
        $configuracoes = [
            'logo_relatorios' => \App\Helpers\ConfigHelper::get('logo_relatorios', ''),
            'cabecalho' => \App\Helpers\ConfigHelper::get('cabecalho_documentos'),
            'rodape' => \App\Helpers\ConfigHelper::get('rodape_relatorios', 'Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária')
        ];

        // Se cabecalho_documentos for string JSON, decodificar
        if (is_string($configuracoes['cabecalho'])) {
            $configuracoes['cabecalho'] = json_decode($configuracoes['cabecalho'], true) ?: $configuracoes['cabecalho'];
        }

        // Gerar PDF usando DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('infracoes-ambientais.relatorio-pdf', compact('infracoes', 'configuracoes', 'filtrosAplicados'));
        
        // Configurações do PDF
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'Arial',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
            'dpi' => 150
        ]);

        $filename = 'relatorio_infracoes_ambientais_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Generate Excel report
     */
    private function generateExcelReport($infracoes, $filtrosAplicados)
    {
        $filename = 'relatorio_infracoes_ambientais_' . now()->format('Y-m-d_H-i-s') . '.xls';
        
        // Gerar conteúdo CSV compatível com Excel
        $csvContent = $this->generateExcelCsv($infracoes, $filtrosAplicados);
        
        // Headers para download compatível com Office 2013
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
            'Pragma' => 'public',
            'Expires' => '0'
        ];
        
        return response($csvContent, 200, $headers);
    }

    /**
     * Generate CSV content compatible with Excel
     */
    private function generateExcelCsv($infracoes, $filtrosAplicados)
    {
        // BOM para UTF-8 (necessário para caracteres especiais no Excel)
        $csv = "\xEF\xBB\xBF";
        
        // Cabeçalho do relatório
        $csv .= "PORTO DE SOYO\n";
        $csv .= "Sistema Integrado de Gestão Portuária\n";
        $csv .= "Relatório de Infrações Ambientais\n\n";
        
        // Informações do relatório
        $csv .= "Data de Geração:;" . now()->format('d/m/Y H:i:s') . "\n";
        $csv .= "Total de Infrações:;" . $infracoes->count() . "\n\n";
        
        // Filtros aplicados
        if (!empty($filtrosAplicados)) {
            $csv .= "Filtros Aplicados:\n";
            foreach ($filtrosAplicados as $filtro => $valor) {
                $csv .= $filtro . ":;" . $valor . "\n";
            }
            $csv .= "\n";
        }

        // Resumo estatístico
        $statusCount = $infracoes->groupBy('status')->map->count();
        $csv .= "Resumo por Status:\n";
        foreach ($statusCount as $status => $count) {
            $statusLabel = match($status) {
                'registrada' => 'Registrada',
                'notificada' => 'Notificada',
                'contestada' => 'Contestada',
                'em_analise' => 'Em Análise',
                'paga' => 'Paga',
                'cancelada' => 'Cancelada',
                'defesa_deferida' => 'Defesa Deferida',
                default => ucfirst($status)
            };
            $csv .= $statusLabel . ":;" . $count . "\n";
        }
        $csv .= "\n";
        
        // Cabeçalhos da tabela
        $csv .= "Número Auto;Data Infração;Embarcação;Tipo;Gravidade;Local;Valor Multa;Status;Inspetor;Data Cadastro\n";
        
        // Dados das infrações
        foreach ($infracoes as $infracao) {
            $csv .= '"' . $infracao->numero_auto . '";';
            $csv .= '"' . Carbon::parse($infracao->data_infracao)->format('d/m/Y') . '";';
            $csv .= '"' . ($infracao->embarcacao->nome ?? 'N/A') . '";';
            $csv .= '"' . $infracao->getTipoInfracaoLabel() . '";';
            $csv .= '"' . ucfirst($infracao->gravidade) . '";';
            $csv .= '"' . $infracao->local_infracao . '";';
            $csv .= '"AOA ' . number_format($infracao->valor_multa, 2, ',', '.') . '";';
            $csv .= '"' . ucfirst($infracao->status) . '";';
            $csv .= '"' . ($infracao->inspetor->name ?? 'N/A') . '";';
            $csv .= '"' . Carbon::parse($infracao->created_at)->format('d/m/Y H:i') . '"';
            $csv .= "\n";
        }
        
        return $csv;
    }

    /**
     * Export infração ambiental to PDF
     */
    public function exportPdf(InfracaoAmbiental $infracao, Request $request)
    {
        try {
            // Carregar relacionamentos necessários
            $infracao->load(['embarcacao', 'terminal', 'inspetor', 'aprovadoPor']);

            // Buscar configurações do sistema para cabeçalho e rodapé
            $configuracoes = [
                'nome_sistema' => config('app.name', 'Sistema Integrado de Gestão Portuária'),
                'nome_porto' => 'PORTO DE SOYO',
                'logotipo' => asset('images/logo.png'),
                'endereco' => 'Soyo, Província do Zaire, Angola',
                'telefone' => '+244 XXX XXX XXX',
                'email' => 'contato@portodesoyo.ao'
            ];

            // Buscar logs de auditoria relacionados à infração
            $auditLogs = AuditLog::where('auditable_type', 'InfracaoAmbiental')
                ->where('auditable_id', $infracao->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'pdf_exported',
                'auditable_type' => 'InfracaoAmbiental',
                'auditable_id' => $infracao->id,
                'description' => "PDF da infração ambiental exportado: {$infracao->numero_auto}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            // Gerar PDF
            $pdf = Pdf::loadView('infracoes-ambientais.pdf', compact('infracao', 'configuracoes', 'auditLogs'));
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial'
            ]);

            $filename = 'infracao_ambiental_' . $infracao->numero_auto . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF da infração ambiental', [
                'infracao_id' => $infracao->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erro ao gerar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Gerar número único para infração
     */
    private function generateNumeroInfracao()
    {
        $ano = date('Y');
        $mes = date('m');
        
        // Buscar último número do mês
        $ultimaInfracao = InfracaoAmbiental::where('numero_auto', 'like', "INF-{$ano}{$mes}%")
            ->orderBy('numero_auto', 'desc')
            ->first();

        if ($ultimaInfracao) {
            $ultimoNumero = (int) substr($ultimaInfracao->numero_auto, -4);
            $novoNumero = $ultimoNumero + 1;
        } else {
            $novoNumero = 1;
        }

        return "INF-{$ano}{$mes}" . str_pad($novoNumero, 4, '0', STR_PAD_LEFT);
    }
}