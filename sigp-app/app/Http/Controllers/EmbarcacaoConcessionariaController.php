<?php

namespace App\Http\Controllers;

use App\Models\EmbarcacaoConcessionaria;
use App\Models\Concessionaria;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmbarcacaoConcessionariaController extends Controller
{
  /*  public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('role:admin|gestor')->except(['index', 'show', 'api']);
        $this->middleware('role:admin|gestor|operador|concessionaria')->only(['index', 'show', 'api']);
    }*/

    /**
     * Display a listing of embarcacoes.
     */
    public function index(Request $request)
    {
        $query = EmbarcacaoConcessionaria::with('concessionaria')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc');

        // Filtro por concessionária (para usuários de concessionária)
        if (Auth::user()->hasRole('concessionaria')) {
            $user = Auth::user();
            $userConcessionariaId = $user->concessionaria_id;
            
          
            
            if ($userConcessionariaId) {
                $query->where('concessionaria_id', $userConcessionariaId);
            } else {
                // Se não tem concessionária associada, não mostrar nenhuma embarcação
                $query->where('id', null);
            }
        }

        // Filtros
        if ($request->filled('concessionaria_id')) {
            $query->where('concessionaria_id', $request->concessionaria_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tipo_embarcacao')) {
            $query->where('tipo_embarcacao', $request->tipo_embarcacao);
        }

        if ($request->filled('bandeira')) {
            $query->where('bandeira', $request->bandeira);
        }

        if ($request->filled('porto_origem')) {
            $query->where('porto_origem', 'like', "%{$request->porto_origem}%");
        }

        if ($request->filled('porto_destino')) {
            $query->where('porto_destino', 'like', "%{$request->porto_destino}%");
        }

        if ($request->filled('eta_inicio')) {
            $query->whereDate('eta', '>=', $request->eta_inicio);
        }

        if ($request->filled('eta_fim')) {
            $query->whereDate('eta', '<=', $request->eta_fim);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('imo', 'like', "%{$search}%")
                  ->orWhere('mmsi', 'like', "%{$search}%")
                  ->orWhere('armador', 'like', "%{$search}%")
                  ->orWhere('agente_maritimo', 'like', "%{$search}%")
                  ->orWhere('capitao', 'like', "%{$search}%")
                  ->orWhereHas('concessionaria', function($concQuery) use ($search) {
                      $concQuery->where('nome', 'like', "%{$search}%")
                              ->orWhere('cnpj', 'like', "%{$search}%");
                  });
            });
        }

        $embarcacoes = $query->paginate(20);

        // Estatísticas - aplicar o mesmo filtro por concessionária
        $statsQuery = EmbarcacaoConcessionaria::query();
        
        // Aplicar filtro por concessionária nas estatísticas também
        if (Auth::user()->hasRole('concessionaria')) {
            $userConcessionariaId = Auth::user()->concessionaria_id;
            if ($userConcessionariaId) {
                $statsQuery->where('concessionaria_id', $userConcessionariaId);
            } else {
                $statsQuery->where('id', null);
            }
        }

        // Estatísticas
        $stats = [
            'total' => $statsQuery->where('is_active', true)->count(),
            'ativas' => $statsQuery->where('is_active', true)->count(),
            'inativas' => $statsQuery->where('is_active', false)->count(),
            'no_porto' => $statsQuery->where('status', 'atracada')->where('is_active', true)->count(),
            'total_embarcacoes' => $statsQuery->where('is_active', true)->count(),
            'embarcacoes_atracadas' => $statsQuery->where('status', 'atracada')->where('is_active', true)->count(),
            'embarcacoes_navegando' => $statsQuery->where('status', 'navegando')->where('is_active', true)->count(),
            'embarcacoes_mes' => $statsQuery->whereMonth('created_at', now()->month)->where('is_active', true)->count(),
            'por_tipo' => (clone $statsQuery)->select('tipo_embarcacao', DB::raw('count(*) as total'))
                                                ->where('is_active', true)
                                                ->groupBy('tipo_embarcacao')
                                                ->get(),
            'por_bandeira' => (clone $statsQuery)->select('bandeira', DB::raw('count(*) as total'))
                                                    ->where('is_active', true)
                                                    ->groupBy('bandeira')
                                                    ->get(),
        ];

        // Embarcações por tipo
        $porTipo = EmbarcacaoConcessionaria::select('tipo_embarcacao', DB::raw('count(*) as total'))
            ->where('is_active', true)
            ->groupBy('tipo_embarcacao')
            ->get();

        // Embarcações por concessionária
        $porConcessionaria = EmbarcacaoConcessionaria::with('concessionaria')
            ->select('concessionaria_id', DB::raw('count(*) as total'))
            ->where('is_active', true)
            ->groupBy('concessionaria_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Embarcações por mês
        $porMes = EmbarcacaoConcessionaria::where('created_at', '>=', now()->subMonths(12))
            ->where('is_active', true)
            ->select(DB::raw('YEAR(created_at) as ano'), DB::raw('MONTH(created_at) as mes'), DB::raw('count(*) as total'))
            ->groupBy('ano', 'mes')
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->get();

        // Embarcações por bandeira
        $porBandeira = EmbarcacaoConcessionaria::select('bandeira', DB::raw('count(*) as total'))
            ->where('is_active', true)
            ->groupBy('bandeira')
            ->orderBy('total', 'desc')
            ->get();

        // Dados para os filtros
        $concessionarias = Concessionaria::orderBy('nome')->get();
        $tipos = EmbarcacaoConcessionaria::select('tipo_embarcacao')
            ->distinct()
            ->whereNotNull('tipo_embarcacao')
            ->orderBy('tipo_embarcacao')
            ->pluck('tipo_embarcacao');
        $bandeiras = EmbarcacaoConcessionaria::select('bandeira')
            ->distinct()
            ->whereNotNull('bandeira')
            ->orderBy('bandeira')
            ->pluck('bandeira');

        return view('concessionarias.embarcacoes.index', compact(
            'embarcacoes', 'stats', 'concessionarias', 'tipos', 'bandeiras'
        ));
    }

    /**
     * Show the form for creating a new embarcacao.
     */
    public function create()
    {
       // $this->authorize('create', EmbarcacaoConcessionaria::class);

        $concessionarias = Concessionaria::orderBy('nome')->get();
        
        // Tipos de embarcação disponíveis
        $tipos = [
            'container',
            'graneis_solidos', 
            'graneis_liquidos',
            'carga_geral',
            'passageiros',
            'tanque',
            'ro_ro',
            'frigorifico'
        ];

        return view('concessionarias.embarcacoes.create', compact('concessionarias', 'tipos'));
    }

    /**
     * Store a newly created embarcacao.
     */
    public function store(Request $request)
    {
        //$this->authorize('create', EmbarcacaoConcessionaria::class);

        // Buscar a concessionária do usuário logado
        $user = Auth::user();
        $concessionaria = $user->concessionaria;
        
        if (!$concessionaria) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Usuário não está associado a nenhuma concessionária.');
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'imo' => 'nullable|string|max:20',
            'mmsi' => 'nullable|string|max:20',
            'bandeira' => 'required|string|max:100',
            'tipo_embarcacao' => 'required|string',
            'comprimento' => 'nullable|numeric|min:0',
            'largura' => 'nullable|numeric|min:0',
            'calado' => 'nullable|numeric|min:0',
            'arqueacao_bruta' => 'nullable|numeric|min:0',
            'arqueacao_liquida' => 'nullable|numeric|min:0',
            'armador' => 'nullable|string|max:255',
            'agente_maritimo' => 'nullable|string|max:255',
            'capitao' => 'nullable|string|max:255',
            'porto_origem' => 'nullable|string|max:255',
            'porto_destino' => 'nullable|string|max:255',
            'eta' => 'nullable|date',
            'etd' => 'nullable|date',
            'ata' => 'nullable|date',
            'atd' => 'nullable|date',
            'status' => 'required|string',
            'observacoes' => 'nullable|string|max:1000'
        ]);

        // Verificar se já existe embarcação com mesmo IMO ou MMSI
        if ($request->filled('imo')) {
            $existeImo = EmbarcacaoConcessionaria::where('imo', $request->imo)
                ->where('is_active', true)
                ->exists();
            if ($existeImo) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Já existe uma embarcação ativa com este número IMO.');
            }
        }

        if ($request->filled('mmsi')) {
            $existeMmsi = EmbarcacaoConcessionaria::where('mmsi', $request->mmsi)
                ->where('is_active', true)
                ->exists();
            if ($existeMmsi) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Já existe uma embarcação ativa com este número MMSI.');
            }
        }

        

        DB::beginTransaction();
        try {
            $embarcacao = EmbarcacaoConcessionaria::create([
                'concessionaria_id' => $concessionaria->id, // Usar o ID da concessionária do usuário logado
                'nome' => $request->nome,
                'imo' => $request->imo,
                'mmsi' => $request->mmsi,
                'bandeira' => $request->bandeira,
                'tipo_embarcacao' => $request->tipo_embarcacao,
                'comprimento' => $request->comprimento,
                'largura' => $request->largura,
                'calado' => $request->calado,
                'arqueacao_bruta' => $request->arqueacao_bruta,
                'arqueacao_liquida' => $request->arqueacao_liquida,
                'armador' => $request->armador,
                'agente_maritimo' => $request->agente_maritimo,
                'capitao' => $request->capitao,
                'porto_origem' => $request->porto_origem,
                'porto_destino' => $request->porto_destino,
                'eta' => $request->eta,
                'etd' => $request->etd,
                'ata' => $request->ata,
                'atd' => $request->atd,
                'status' => $request->status,
                'observacoes' => $request->observacoes,
                'is_active' => true,
            ]);

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'create',
                'auditable_type' => 'App\\Models\\EmbarcacaoConcessionaria',
                'auditable_id' => $embarcacao->id,
                'description' => "Embarcação {$embarcacao->nome} criada para concessionária {$embarcacao->concessionaria->nome}",
                'new_values' => $embarcacao->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

                        // Log da ação
         

            DB::commit();

            return redirect()->route('concessionarias.embarcacoes.index', $embarcacao)
                ->with('success', 'Embarcação criada com sucesso!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao criar embarcação: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified embarcacao.
     */
    public function show(string $id)
    {
        //$this->authorize('view', $embarcacaoConcessionaria);

        $embarcacao = EmbarcacaoConcessionaria::findOrFail($id);
       // $embarcacao->load('concessionaria');

        return view('concessionarias.embarcacoes.show', compact('embarcacao'));
    }

    /**
     * Show the form for editing the embarcacao.
     */
    public function edit(string $id)
    {
        //$this->authorize('update', $embarcacaoConcessionaria);
        $embarcacao = EmbarcacaoConcessionaria::findOrFail($id);

        $concessionarias = Concessionaria::orderBy('nome')->get();

        return view('concessionarias.embarcacoes.edit', compact(
            'embarcacao', 'concessionarias'
        ));
    }

    /**
     * Update the specified embarcacao.
     */
    public function update(Request $request,$id)
    {
       // $this->authorize('update', $embarcacaoConcessionaria);
      

        $embarcacaoConcessionaria = EmbarcacaoConcessionaria::findOrFail($id);

        $request->validate([
           // 'concessionaria_id' => 'required|exists:concessionarias,id',
            'nome' => 'required|string|max:255',
            'imo' => 'nullable|string|max:20',
            'mmsi' => 'nullable|string|max:20',
            'bandeira' => 'required|string|max:100',
            'tipo_embarcacao' => 'required|string',
            'comprimento' => 'nullable|numeric|min:0',
            'largura' => 'nullable|numeric|min:0',
            'calado' => 'nullable|numeric|min:0',
            'arqueacao_bruta' => 'nullable|numeric|min:0',
            'arqueacao_liquida' => 'nullable|numeric|min:0',
            'armador' => 'nullable|string|max:255',
            'agente_maritimo' => 'nullable|string|max:255',
            'capitao' => 'nullable|string|max:255',
            'porto_origem' => 'nullable|string|max:255',
            'porto_destino' => 'nullable|string|max:255',
            'eta' => 'nullable|date',
            'etd' => 'nullable|date',
            'ata' => 'nullable|date',
            'atd' => 'nullable|date',
            'status' => 'string',
            'observacoes' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        try {
            $dadosAnteriores = $embarcacaoConcessionaria->toArray();

            $embarcacaoConcessionaria->update([
              //  'concessionaria_id' => $request->concessionaria_id,
                'nome' => $request->nome,
                'imo' => $request->imo,
                'mmsi' => $request->mmsi,
                'bandeira' => $request->bandeira,
                'tipo_embarcacao' => $request->tipo_embarcacao,
                'comprimento' => $request->comprimento,
                'largura' => $request->largura,
                'calado' => $request->calado,
                'arqueacao_bruta' => $request->arqueacao_bruta,
                'arqueacao_liquida' => $request->arqueacao_liquida,
                'armador' => $request->armador,
                'agente_maritimo' => $request->agente_maritimo,
                'capitao' => $request->capitao,
                'porto_origem' => $request->porto_origem,
                'porto_destino' => $request->porto_destino,
                'eta' => $request->eta,
                'etd' => $request->etd,
                'ata' => $request->ata,
                'atd' => $request->atd,
                'status' => $request->status,
                'observacoes' => $request->observacoes,
            ]);

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'App\\Models\\EmbarcacaoConcessionaria',
                'auditable_id' => $embarcacaoConcessionaria->id,
                'description' => "Embarcação {$embarcacaoConcessionaria->nome} atualizada",
                'old_values' => $dadosAnteriores,
                'new_values' => $embarcacaoConcessionaria->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            return redirect()->route('concessionarias.embarcacoes.index', $embarcacaoConcessionaria)
                ->with('success', 'Embarcação atualizada com sucesso!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar embarcação: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified embarcacao.
     */
    public function destroy(Request $request,$id)
    {
       // $this->authorize('delete', $embarcacaoConcessionaria);
        $embarcacaoConcessionaria = EmbarcacaoConcessionaria::findOrFail($id);

        DB::beginTransaction();
        try {
            // Log de auditoria antes da exclusão
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'delete',
                'auditable_type' => 'App\\Models\\EmbarcacaoConcessionaria',
                'auditable_id' => $embarcacaoConcessionaria->id,
                'description' => "Embarcação {$embarcacaoConcessionaria->nome} excluída (soft delete)",
                'old_values' => $embarcacaoConcessionaria->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl()
            ]);

            // Soft delete
            $embarcacaoConcessionaria->update(['is_active' => false]);

            DB::commit();

            return redirect()->route('concessionarias.embarcacoes.index')
                ->with('success', 'Embarcação excluída com sucesso!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Erro ao excluir embarcação: ' . $e->getMessage());
        }
    }

    /**
     * Reactivate a deactivated embarcacao.
     */
    public function reactivate(EmbarcacaoConcessionaria $embarcacaoConcessionaria)
    {
       // $this->authorize('update', $embarcacaoConcessionaria);

        DB::beginTransaction();
        try {
            $embarcacaoConcessionaria->update(['is_active' => true]);

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'App\\Models\\EmbarcacaoConcessionaria',
                'auditable_id' => $embarcacaoConcessionaria->id,
                'description' => "Embarcação {$embarcacaoConcessionaria->nome} reativada",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Embarcação reativada com sucesso!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Erro ao reativar embarcação: ' . $e->getMessage());
        }
    }

    /**
     * Dashboard with statistics.
     */
    public function dashboard(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        $stats = [
            'total_embarcacoes' => EmbarcacaoConcessionaria::where('is_active', true)->count(),
            'embarcacoes_atracadas' => EmbarcacaoConcessionaria::where('status', 'atracada')->where('is_active', true)->count(),
            'embarcacoes_navegando' => EmbarcacaoConcessionaria::where('status', 'navegando')->where('is_active', true)->count(),
            'embarcacoes_mes' => EmbarcacaoConcessionaria::whereMonth('created_at', now()->month)->where('is_active', true)->count(),
            'por_tipo' => EmbarcacaoConcessionaria::select('tipo_embarcacao', DB::raw('count(*) as total'))
                                                ->where('is_active', true)
                                                ->groupBy('tipo_embarcacao')
                                                ->get(),
            'por_bandeira' => EmbarcacaoConcessionaria::select('bandeira', DB::raw('count(*) as total'))
                                                    ->where('is_active', true)
                                                    ->groupBy('bandeira')
                                                    ->get(),
        ];

        // Embarcações por tipo
        $porTipo = EmbarcacaoConcessionaria::select('tipo_embarcacao', DB::raw('count(*) as total'))
            ->where('is_active', true)
            ->groupBy('tipo_embarcacao')
            ->get();

        // Embarcações por concessionária
        $porConcessionaria = EmbarcacaoConcessionaria::with('concessionaria')
            ->select('concessionaria_id', DB::raw('count(*) as total'))
            ->where('is_active', true)
            ->groupBy('concessionaria_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Embarcações por mês
        $porMes = EmbarcacaoConcessionaria::where('created_at', '>=', now()->subMonths(12))
            ->where('is_active', true)
            ->select(DB::raw('YEAR(created_at) as ano'), DB::raw('MONTH(created_at) as mes'), DB::raw('count(*) as total'))
            ->groupBy('ano', 'mes')
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->get();

        // Embarcações por bandeira
        $porBandeira = EmbarcacaoConcessionaria::select('bandeira', DB::raw('count(*) as total'))
            ->where('is_active', true)
            ->groupBy('bandeira')
            ->orderBy('total', 'desc')
            ->get();

        // Dados para os filtros
        $concessionarias = Concessionaria::orderBy('nome')->get();
        $tipos = EmbarcacaoConcessionaria::select('tipo_embarcacao')
            ->distinct()
            ->whereNotNull('tipo_embarcacao')
            ->orderBy('tipo_embarcacao')
            ->pluck('tipo_embarcacao');
        $bandeiras = EmbarcacaoConcessionaria::select('bandeira')
            ->distinct()
            ->whereNotNull('bandeira')
            ->orderBy('bandeira')
            ->pluck('bandeira');

        return view('concessionarias.embarcacoes.index', compact(
            'embarcacoes', 'stats', 'concessionarias', 'tipos', 'bandeiras'
        ));
    }

    /**
     * Export embarcacoes to various formats.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $query = EmbarcacaoConcessionaria::with('concessionaria')->where('is_active', true);

        // Aplicar filtros
        if ($request->filled('concessionaria_id')) {
            $query->where('concessionaria_id', $request->concessionaria_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tipo_embarcacao')) {
            $query->where('tipo_embarcacao', $request->tipo_embarcacao);
        }

        $embarcacoes = $query->get();
        $filename = 'embarcacoes_concessionaria_' . now()->format('Y-m-d_H-i-s');

        switch ($format) {
            case 'csv':
                return $this->exportToCsv($embarcacoes, $filename);
            case 'excel':
                return $this->exportToExcel($embarcacoes, $filename);
            case 'pdf':
                return $this->exportToPdf($embarcacoes, $filename);
            default:
                return redirect()->back()->with('error', 'Formato de exportação inválido.');
        }
    }

    /**
     * API endpoint for embarcacoes.
     */
    public function api(Request $request)
    {
        $query = EmbarcacaoConcessionaria::with('concessionaria:id,nome,cnpj')
            ->where('is_active', true);

        // Filtros via API
        if ($request->filled('concessionaria_id')) {
            $query->where('concessionaria_id', $request->concessionaria_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tipo_embarcacao')) {
            $query->where('tipo_embarcacao', $request->tipo_embarcacao);
        }

        $embarcacoes = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $embarcacoes,
            'message' => 'Embarcações recuperadas com sucesso.'
        ]);
    }

    /**
     * Export to CSV format.
     */
    private function exportToCsv($embarcacoes, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($embarcacoes) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalhos
            fputcsv($file, [
                'ID', 'Concessionária', 'Nome', 'IMO', 'MMSI', 'Bandeira',
                'Tipo Embarcação', 'Comprimento', 'Largura', 'Calado',
                'Arqueação Bruta', 'Arqueação Líquida', 'Armador', 'Agente Marítimo',
                'Capitão', 'Porto Origem', 'Porto Destino', 'ETA', 'ETD',
                'ATA', 'ATD', 'Status', 'Observações', 'Criado em'
            ], ';');

            foreach ($embarcacoes as $embarcacao) {
                fputcsv($file, [
                    $embarcacao->id,
                    $embarcacao->concessionaria->nome,
                    $embarcacao->nome,
                    $embarcacao->imo,
                    $embarcacao->mmsi,
                    $embarcacao->bandeira,
                    $embarcacao->tipo_embarcacao,
                    $embarcacao->comprimento,
                    $embarcacao->largura,
                    $embarcacao->calado,
                    $embarcacao->arqueacao_bruta,
                    $embarcacao->arqueacao_liquida,
                    $embarcacao->armador,
                    $embarcacao->agente_maritimo,
                    $embarcacao->capitao,
                    $embarcacao->porto_origem,
                    $embarcacao->porto_destino,
                    $embarcacao->eta ? $embarcacao->eta->format('d/m/Y H:i') : '',
                    $embarcacao->etd ? $embarcacao->etd->format('d/m/Y H:i') : '',
                    $embarcacao->ata ? $embarcacao->ata->format('d/m/Y H:i') : '',
                    $embarcacao->atd ? $embarcacao->atd->format('d/m/Y H:i') : '',
                    $embarcacao->status,
                    $embarcacao->observacoes,
                    $embarcacao->created_at->format('d/m/Y H:i:s')
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export to Excel format.
     */
    private function exportToExcel($embarcacoes, $filename)
    {
        // Implementar com PhpSpreadsheet
        return $this->exportToCsv($embarcacoes, $filename);
    }

    /**
     * Export to PDF format.
     */
    private function exportToPdf($embarcacoes, $filename)
    {
        // Implementar com DomPDF
        return redirect()->back()->with('error', 'Exportação PDF não implementada ainda.');
    }

    /**
     * Generate report with filtered embarcacoes (PDF or Excel)
     */
    public function relatorioFiltrado(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf'); // Default para PDF
            
            // Aplicar os mesmos filtros do método index
            $query = EmbarcacaoConcessionaria::with('concessionaria')
                ->where('is_active', true)
                ->when($request->search, function ($q) use ($request) {
                    $q->where(function ($query) use ($request) {
                        $query->where('nome', 'like', '%' . $request->search . '%')
                            ->orWhere('imo', 'like', '%' . $request->search . '%')
                            ->orWhere('mmsi', 'like', '%' . $request->search . '%')
                            ->orWhere('bandeira', 'like', '%' . $request->search . '%');
                    });
                })
                ->when($request->status, function ($q) use ($request) {
                    $q->where('status', $request->status);
                })
                ->when($request->tipo_embarcacao, function ($q) use ($request) {
                    $q->where('tipo_embarcacao', $request->tipo_embarcacao);
                })
                ->when($request->bandeira, function ($q) use ($request) {
                    $q->where('bandeira', $request->bandeira);
                })
                ->when($request->concessionaria_id, function ($q) use ($request) {
                    $q->where('concessionaria_id', $request->concessionaria_id);
                })
                ->when($request->data_inicio && $request->data_fim, function ($q) use ($request) {
                    $q->whereBetween('created_at', [
                        Carbon::parse($request->data_inicio)->startOfDay(),
                        Carbon::parse($request->data_fim)->endOfDay()
                    ]);
                });

            // Filtro por concessionária (para usuários de concessionária)
            if (Auth::user()->hasRole('concessionaria')) {
                $userConcessionariaId = Auth::user()->concessionaria_id;
                if ($userConcessionariaId) {
                    $query->where('concessionaria_id', $userConcessionariaId);
                } else {
                    $query->where('id', null);
                }
            }

            $embarcacoes = $query->orderBy('nome', 'asc')->get();

            // Preparar dados dos filtros aplicados
            $filtrosAplicados = [];
            if ($request->search) $filtrosAplicados['Busca'] = $request->search;
            if ($request->status) $filtrosAplicados['Status'] = $request->status;
            if ($request->tipo_embarcacao) $filtrosAplicados['Tipo'] = $request->tipo_embarcacao;
            if ($request->bandeira) $filtrosAplicados['Bandeira'] = $request->bandeira;
            if ($request->concessionaria_id) {
                $concessionaria = Concessionaria::find($request->concessionaria_id);
                $filtrosAplicados['Concessionária'] = $concessionaria ? $concessionaria->nome : $request->concessionaria_id;
            }
            if ($request->data_inicio && $request->data_fim) {
                $filtrosAplicados['Período'] = Carbon::parse($request->data_inicio)->format('d/m/Y') . ' a ' . Carbon::parse($request->data_fim)->format('d/m/Y');
            }

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'export_report',
                'auditable_type' => 'App\\Models\\EmbarcacaoConcessionaria',
                'auditable_id' => null,
                'description' => "Gerou relatório de embarcações de concessionárias em formato {$formato} com " . $embarcacoes->count() . " registros",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Gerar relatório baseado no formato
            if ($formato === 'excel') {
                return $this->generateExcelReport($embarcacoes, $filtrosAplicados);
            } else {
                return $this->generatePdfReport($embarcacoes, $filtrosAplicados);
            }

        } catch (\Exception $e) {
            Log::error('EmbarcacaoConcessionariaController@relatorioFiltrado: Erro ao gerar relatório', [
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
    private function generatePdfReport($embarcacoes, $filtrosAplicados)
    {
        // Obter configurações do sistema
        $configuracoes = \App\Helpers\ConfigHelper::get('cabecalho_documentos');
        
        // Se cabecalho_documentos for string JSON, decodificar
        if (is_string($configuracoes)) {
            $configuracoes = json_decode($configuracoes, true);
        }
        
        // Se for array, extrair o título, senão usar como string
        $cabecalho_documentos = is_array($configuracoes) && isset($configuracoes['titulo']) 
            ? $configuracoes['titulo'] 
            : (is_string($configuracoes) ? $configuracoes : 'PORTO DE SOYO');

        $data = [
            'embarcacoes' => $embarcacoes,
            'filtros' => $filtrosAplicados,
            'cabecalho_documentos' => $cabecalho_documentos,
            'data_geracao' => now()->format('d/m/Y H:i:s'),
            'usuario' => Auth::user()->name
        ];

        // Gerar PDF usando DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('concessionarias.embarcacoes.relatorio-pdf', $data);
        
        // Configurações do PDF
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'Arial',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
            'dpi' => 150
        ]);

        $filename = 'relatorio_embarcacoes_concessionarias_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Generate Excel report
     */
    private function generateExcelReport($embarcacoes, $filtrosAplicados)
    {
        $filename = 'relatorio_embarcacoes_concessionarias_' . now()->format('Y-m-d_H-i-s') . '.xls';
        
        // Gerar conteúdo CSV compatível com Excel
        $csvContent = $this->generateExcelCsv($embarcacoes, $filtrosAplicados);
        
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
    private function generateExcelCsv($embarcacoes, $filtrosAplicados)
    {
        // Obter configurações do sistema
        $configuracoes = \App\Helpers\ConfigHelper::get('cabecalho_documentos');
        
        // Se cabecalho_documentos for string JSON, decodificar
        if (is_string($configuracoes)) {
            $configuracoes = json_decode($configuracoes, true);
        }
        
        // Se for array, extrair o título, senão usar como string
        $cabecalho_documentos = is_array($configuracoes) && isset($configuracoes['titulo']) 
            ? $configuracoes['titulo'] 
            : (is_string($configuracoes) ? $configuracoes : 'PORTO DE SOYO');

        // BOM para UTF-8 (necessário para caracteres especiais no Excel)
        $csv = "\xEF\xBB\xBF";
        
        // Cabeçalho do relatório
        $csv .= $cabecalho_documentos . "\n";
        $csv .= "Sistema Integrado de Gestão Portuária\n";
        $csv .= "Relatório de Embarcações de Concessionárias\n\n";
        
        // Informações do relatório
        $csv .= "Data de Geração:;" . now()->format('d/m/Y H:i:s') . "\n";
        $csv .= "Usuário:;" . Auth::user()->name . "\n";
        $csv .= "Total de Embarcações:;" . $embarcacoes->count() . "\n\n";
        
        // Filtros aplicados
        if (!empty($filtrosAplicados)) {
            $csv .= "Filtros Aplicados:\n";
            foreach ($filtrosAplicados as $filtro => $valor) {
                $csv .= $filtro . ":;" . $valor . "\n";
            }
            $csv .= "\n";
        }
        
        // Cabeçalhos da tabela
        $csv .= "Nome;IMO;MMSI;Bandeira;Tipo;Comprimento (m);Largura (m);Calado (m);Arqueação Bruta;Status;Concessionária;Data Cadastro\n";
        
        // Dados das embarcações
        foreach ($embarcacoes as $embarcacao) {
            $csv .= implode(';', [
                $embarcacao->nome ?? '',
                $embarcacao->imo ?? '',
                $embarcacao->mmsi ?? '',
                $embarcacao->bandeira ?? '',
                $embarcacao->tipo_embarcacao ?? '',
                $embarcacao->comprimento ?? '',
                $embarcacao->largura ?? '',
                $embarcacao->calado ?? '',
                $embarcacao->arqueacao_bruta ?? '',
                $embarcacao->status ?? '',
                $embarcacao->concessionaria->nome ?? '',
                $embarcacao->created_at->format('d/m/Y H:i:s')
            ]) . "\n";
        }
        
        return $csv;
    }

    /**
     * Export to CSV format.
     */
   
}