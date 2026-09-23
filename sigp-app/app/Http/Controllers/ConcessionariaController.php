<?php

namespace App\Http\Controllers;

use App\Models\Concessionaria;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class ConcessionariaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:concessionarias.view')->only(['index', 'show']);
        $this->middleware('permission:concessionarias.create')->only(['create', 'store']);
        $this->middleware('permission:concessionarias.edit')->only(['edit', 'update']);
        $this->middleware('permission:concessionarias.delete')->only(['destroy']);
        $this->middleware('permission:concessionarias.export')->only(['export']);
    }

    /**
     * Display a listing of concessionarias
     */
    public function index(Request $request)
    {
        try {
            $query = Concessionaria::with('user')
                ->when($request->search, function ($q) use ($request) {
                    $q->where(function ($query) use ($request) {
                        $query->where('nome', 'like', '%' . $request->search . '%')
                            ->orWhere('nif', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%')
                            ->orWhere('telefone', 'like', '%' . $request->search . '%');
                    });
                })
                ->when($request->status !== null, function ($q) use ($request) {
                    $q->where('is_active', $request->status);
                })
                ->when($request->data_inicio && $request->data_fim, function ($q) use ($request) {
                    $q->whereBetween('created_at', [
                        Carbon::parse($request->data_inicio)->startOfDay(),
                        Carbon::parse($request->data_fim)->endOfDay()
                    ]);
                });

            $concessionarias = $query->withCount(['embarcacoes' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy($request->get('sort', 'nome'), $request->get('direction', 'asc'))
                ->paginate($request->get('per_page', 15))
                ->withQueryString();

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'view',
                'auditable_type' => 'Concessionaria',
                'description' => 'Visualizou listagem de concessionárias',
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);

            return view('concessionarias.index', compact('concessionarias'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar concessionárias: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new concessionaria
     */
    public function create()
    {
        try {
            // Verificar se a role 'concessionaria' existe antes de buscar usuários
            $roleExists = \Spatie\Permission\Models\Role::where('name', 'concessionaria')->exists();
            
            if ($roleExists) {
                $users = \App\Models\User::whereHas('roles', function($query) {
                    $query->where('name', 'concessionaria');
                })->orderBy('name')->get();
            } else {
                // Se a role não existir, retornar array vazio
                $users = collect();
            }
            
            return view('concessionarias.create', compact('users'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar formulário: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created concessionaria
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'user_id' => 'nullable|exists:users,id',
                'nif' => 'required|string|unique:concessionarias,nif',
                'email' => 'required|email|unique:concessionarias,email',
                'telefone' => 'nullable|string',
                'observacoes' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            // Garantir que is_active seja boolean
            $validated['is_active'] = $request->has('is_active');
            
            // Adicionar o campo created_by obrigatório
            $validated['created_by'] = Auth::id();

            // Criar a concessionária
            $concessionaria = Concessionaria::create($validated);

            // Log da ação com campos corretos
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'created',
                'auditable_type' => 'Concessionaria',
                'auditable_id' => $concessionaria->id,
                'description' => "Criou concessionária: {$concessionaria->nome}",
                'new_values' => json_encode($validated),
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);

            return redirect()->route('concessionarias.index')
                ->with('success', 'Concessionária cadastrada com sucesso!');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Erro ao cadastrar concessionária: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified concessionaria
     */
    public function show(Concessionaria $concessionaria)
    {
        try {
            $concessionaria->load(['embarcacoes' => function ($query) {
                $query->where('is_active', true);
            }, 'users']);

            // Estatísticas
            $stats = [
                'total_embarcacoes' => $concessionaria->embarcacoes()->count(),
                'embarcacoes_ativas' => $concessionaria->embarcacoes()->where('is_active', true)->count(),
                'movimentos_mes' => 0 // Temporariamente zerado até ajustar o relacionamento correto
            ];

            // Buscar usuários disponíveis (com role 'concessionaria' e sem concessionária associada)
            $usuariosDisponiveis = collect();
            if (Role::where('name', 'concessionaria')->exists()) {
                $usuariosDisponiveis = User::whereHas('roles', function($query) {
                    $query->where('name', 'concessionaria');
                })->whereNull('concessionaria_id')
                ->orderBy('name')
                ->get();
            }

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'view',
                'auditable_type' => 'Concessionaria',
                'auditable_id' => $concessionaria->id,
                'description' => "Visualizou concessionária: {$concessionaria->nome}",
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);

            return view('concessionarias.show', compact('concessionaria', 'stats', 'usuariosDisponiveis'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar concessionária: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified concessionaria
     */
    public function edit(Concessionaria $concessionaria)
    {
        try {
            // Buscar usuários com role 'concessionaria' para o select
            $users = collect();
            
            if (Role::where('name', 'concessionaria')->exists()) {
                $users = User::whereHas('roles', function($query) {
                    $query->where('name', 'concessionaria');
                })->orderBy('name')->get();
            }

            return view('concessionarias.edit', compact('concessionaria', 'users'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar formulário: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified concessionaria
     */
    public function update(Request $request, Concessionaria $concessionaria)
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'user_id' => 'nullable|exists:users,id',
                'nif' => 'required|string|unique:concessionarias,nif,' . $concessionaria->id,
                'email' => 'required|email|unique:concessionarias,email,' . $concessionaria->id,
                'telefone' => 'nullable|string',
                'observacoes' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            // Garantir que is_active seja boolean
            $validated['is_active'] = $request->has('is_active');

            DB::beginTransaction();

            $originalData = $concessionaria->toArray();
            $concessionaria->update($validated);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'auditable_type' => 'Concessionaria',
                'auditable_id' => $concessionaria->id,
                'description' => "Atualizou concessionária: {$concessionaria->nome}",
                'old_values' => json_encode($originalData),
                'new_values' => json_encode($validated),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);

            DB::commit();

            return redirect()->route('concessionarias.index')
                ->with('success', 'Concessionária atualizada com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar concessionária: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified concessionaria
     */
    public function destroy(Concessionaria $concessionaria)
    {
        try {
            DB::beginTransaction();

            // Verificar se há embarcações associadas
            if ($concessionaria->embarcacoes()->exists()) {
                return back()->with('error', 'Não é possível excluir esta concessionária pois possui embarcações associadas.');
            }

            $nome = $concessionaria->nome;
            $concessionaria->delete();

            // Log da ação com campos corretos
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'auditable_type' => 'Concessionaria',
                'auditable_id' => $concessionaria->id,
                'description' => "Excluiu concessionária: {$nome}",
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);

            DB::commit();

            return redirect()->route('concessionarias.index')
                ->with('success', 'Concessionária excluída com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao excluir concessionária: ' . $e->getMessage());
        }
    }

    /**
     * Toggle concessionaria status
     */
    public function toggleStatus(Concessionaria $concessionaria)
    {
        try {
            DB::beginTransaction();

            $concessionaria->update(['is_active' => !$concessionaria->is_active]);
            $status = $concessionaria->is_active ? 'ativada' : 'desativada';

            // Log da ação com campos corretos
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'auditable_type' => 'Concessionaria',
                'auditable_id' => $concessionaria->id,
                'description' => "Concessionária {$concessionaria->nome} foi {$status}",
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);

            DB::commit();

            return back()->with('success', "Concessionária {$status} com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao alterar status: ' . $e->getMessage());
        }
    }

    /**
     * Export concessionarias to Excel
     */
    public function export(Request $request)
    {
        try {
            // Implementar exportação para Excel
            return back()->with('info', 'Funcionalidade de exportação será implementada em breve.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao exportar dados: ' . $e->getMessage());
        }
    }

    /**
     * Get concessionarias for API/AJAX
     */
    public function api(Request $request)
    {
        try {
            $concessionarias = Concessionaria::query()
                ->when($request->search, function ($q) use ($request) {
                    $q->where('nome', 'like', '%' . $request->search . '%')
                        ->orWhere('cnpj', 'like', '%' . $request->search . '%');
                })
                ->ativas()
                ->orderBy('nome')
                ->limit(50)
                ->get()
                ->map(function ($concessionaria) {
                    return [
                        'id' => $concessionaria->id,
                        'nome' => $concessionaria->nome,
                        'cnpj' => $concessionaria->cnpj,
                        'email' => $concessionaria->email,
                        'cidade' => $concessionaria->cidade,
                        'estado' => $concessionaria->estado
                    ];
                });

            return response()->json($concessionarias);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar concessionárias'], 500);
        }
    }

    /**
     * Dashboard específico para concessionárias
     */
    public function dashboard()
    {
        try {
            // Verificar se o usuário logado é de uma concessionária
            $user = Auth::user();
            if (!$user->hasRole('concessionaria')) {
                abort(403, 'Acesso negado.');
            }

            // Buscar a concessionária do usuário
            $concessionaria = $user->concessionaria;
            if (!$concessionaria) {
                return redirect()->route('dashboard')
                    ->with('error', 'Concessionária não encontrada.');
            }

            // Estatísticas da concessionária
            $stats = [
                'embarcacoes_total' => $concessionaria->embarcacoes()->count(),
                'embarcacoes_ativas' => $concessionaria->embarcacoes()->ativas()->count(),
                'movimentos_hoje' => $concessionaria->embarcacoes()
                    ->withCount(['entradaSaidas' => function ($query) {
                        $query->whereDate('created_at', today());
                    }])
                    ->get()
                    ->sum('entrada_saidas_count'),
                'movimentos_mes' => $concessionaria->embarcacoes()
                    ->withCount(['entradaSaidas' => function ($query) {
                        $query->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year);
                    }])
                    ->get()
                    ->sum('entrada_saidas_count')
            ];

            // Últimas embarcações
            $ultimasEmbarcacoes = $concessionaria->embarcacoes()
                ->with(['entradaSaidas' => function ($query) {
                    $query->latest()->take(1);
                }])
                ->latest()
                ->take(5)
                ->get();

            // Próximos movimentos
            $proximosMovimentos = $concessionaria->embarcacoes()
                ->with(['entradaSaidas' => function ($query) {
                    $query->where('status', 'programado')
                        ->where('data_programada', '>=', now())
                        ->orderBy('data_programada')
                        ->take(10);
                }])
                ->get()
                ->flatMap->entradaSaidas
                ->sortBy('data_programada')
                ->take(10);

            return view('concessionarias.dashboard', compact(
                'concessionaria',
                'stats',
                'ultimasEmbarcacoes',
                'proximosMovimentos'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Adicionar usuário à concessionária
     */
    public function addUser(Request $request, Concessionaria $concessionaria)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $user = User::findOrFail($validated['user_id']);

            // Verificar se o usuário tem a role 'concessionaria'
            if (!$user->hasRole('concessionaria')) {
                return back()->with('error', 'O usuário deve ter a role de concessionária.');
            }

            // Verificar se o usuário já está associado a uma concessionária
            if ($user->concessionaria_id) {
                return back()->with('error', 'O usuário já está associado a uma concessionária.');
            }

            DB::beginTransaction();

            // Associar o usuário à concessionária
            $user->update(['concessionaria_id' => $concessionaria->id]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'user_added',
                'auditable_type' => 'Concessionaria',
                'auditable_id' => $concessionaria->id,
                'description' => "Adicionou usuário {$user->name} à concessionária: {$concessionaria->nome}",
                'new_values' => json_encode(['user_id' => $user->id]),
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);

            DB::commit();

            return back()->with('success', 'Usuário adicionado à concessionária com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao adicionar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Remover usuário da concessionária
     */
    public function removeUser(Request $request, Concessionaria $concessionaria)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $user = User::findOrFail($validated['user_id']);

            if ($user->concessionaria_id != $concessionaria->id) {
                return back()->with('error', 'O usuário não pertence a esta concessionária.');
            }

            DB::beginTransaction();

            $userName = $user->name;

            // Remover a associação
            $user->update(['concessionaria_id' => null]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'user_removed',
                'auditable_type' => 'Concessionaria',
                'auditable_id' => $concessionaria->id,
                'description' => "Removeu usuário {$userName} da concessionária: {$concessionaria->nome}",
                'old_values' => json_encode(['concessionaria_id' => $concessionaria->id]),
                'new_values' => json_encode(['concessionaria_id' => null]),
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);

            DB::commit();

            return back()->with('success', 'Usuário removido da concessionária com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao remover usuário: ' . $e->getMessage());
        }
    }

    /**
     * Gerar relatório de concessionárias
     */
    public function relatorioFiltrado(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf');
            
            // Aplicar os mesmos filtros do método index
            $query = Concessionaria::with(['user', 'embarcacoes'])
                ->when($request->search, function ($q, $search) {
                    return $q->where(function ($query) use ($search) {
                        $query->where('nome', 'like', '%' . $search . '%')
                            ->orWhere('nif', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('telefone', 'like', '%' . $search . '%');
                    });
                })
                ->when($request->status !== null, function ($q) use ($request) {
                    return $q->where('is_active', $request->status);
                });

            $concessionarias = $query->orderBy('nome')->get();

            // Preparar filtros aplicados para exibir no relatório
            $filtrosAplicados = [];
            if ($request->search) {
                $filtrosAplicados['Busca'] = $request->search;
            }
            if ($request->status !== null) {
                $filtrosAplicados['Status'] = $request->status == '1' ? 'Ativa' : 'Inativa';
            }

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'report_generated',
                'auditable_type' => 'App\\Models\\Concessionaria',
                'auditable_id' => null,
                'description' => "Relatório de concessionárias gerado - Formato: {$formato}",
                'new_values' => json_encode([
                    'formato' => $formato,
                    'total_registros' => $concessionarias->count(),
                    'filtros' => $filtrosAplicados
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            if ($formato === 'excel') {
                return $this->generateExcelReport($concessionarias, $filtrosAplicados);
            }

            return $this->generatePdfReport($concessionarias, $filtrosAplicados);

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }

    /**
     * Gerar relatório PDF
     */
    private function generatePdfReport($concessionarias, $filtrosAplicados)
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
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('concessionarias.relatorio-pdf', compact('concessionarias', 'configuracoes', 'filtrosAplicados'));
        
        // Configurações do PDF
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'Arial',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
            'dpi' => 150
        ]);

        $filename = 'relatorio_concessionarias_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Gerar relatório Excel
     */
    private function generateExcelReport($concessionarias, $filtrosAplicados)
    {
        $filename = 'relatorio_concessionarias_' . now()->format('Y-m-d_H-i-s') . '.xls';
        
        // Gerar conteúdo CSV compatível com Excel
        $csvContent = $this->generateExcelCsv($concessionarias, $filtrosAplicados);
        
        // Headers para download compatível com Office
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
     * Gerar conteúdo CSV para Excel
     */
    private function generateExcelCsv($concessionarias, $filtrosAplicados)
    {
        $csv = "\xEF\xBB\xBF"; // BOM para UTF-8
        
        // Cabeçalho do relatório
        $csv .= "RELATÓRIO DE CONCESSIONÁRIAS\n";
        $csv .= "Data de Geração: " . now()->format('d/m/Y H:i:s') . "\n";
        $csv .= "Total de Registros: " . $concessionarias->count() . "\n";
        $csv .= "Gerado por: " . Auth::user()->name . "\n\n";
        
        // Filtros aplicados
        if (!empty($filtrosAplicados)) {
            $csv .= "FILTROS APLICADOS:\n";
            foreach ($filtrosAplicados as $filtro => $valor) {
                $csv .= "{$filtro}: {$valor}\n";
            }
            $csv .= "\n";
        }
        
        // Cabeçalhos das colunas
        $csv .= "Nome;NIF;Email;Telefone;Status;Usuário Principal;Total Embarcações;Data Cadastro\n";
        
        // Dados das concessionárias
        foreach ($concessionarias as $concessionaria) {
            $csv .= '"' . str_replace('"', '""', $concessionaria->nome) . '";';
            $csv .= '"' . str_replace('"', '""', $concessionaria->nif) . '";';
            $csv .= '"' . str_replace('"', '""', $concessionaria->email) . '";';
            $csv .= '"' . str_replace('"', '""', $concessionaria->telefone) . '";';
            $csv .= '"' . ($concessionaria->is_active ? 'Ativa' : 'Inativa') . '";';
            $csv .= '"' . str_replace('"', '""', $concessionaria->user ? $concessionaria->user->name : 'Não definido') . '";';
            $csv .= '"' . $concessionaria->embarcacoes->count() . '";';
            $csv .= '"' . $concessionaria->created_at->format('d/m/Y H:i:s') . '"';
            $csv .= "\n";
        }
        
        return $csv;
    }
}

    