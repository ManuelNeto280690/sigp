<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Mail\NovoPedidoNotification;
use App\Helpers\ConfigHelper;
use Illuminate\Support\Facades\Mail;




class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Pedido::with(['user', 'decisor']);
    
        // Restrição de acesso: agente_navio só vê seus próprios pedidos
        if (Auth::user()->hasRole('agente_navio')) {
            $query->where('user_id', Auth::id());
        }
    
        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome_navio', 'like', "%{$search}%")
                  ->orWhere('numero_imo', 'like', "%{$search}%")
                  ->orWhere('indicativo_chamada', 'like', "%{$search}%")
                  ->orWhere('nome_agente', 'like', "%{$search}%");
            });
        }
    
        // Filtro de variação de data - JÁ IMPLEMENTADO!
        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('data_chegada', [$request->data_inicio, $request->data_fim]);
        }
    
        $pedidos = $query->orderBy('created_at', 'desc')->paginate(15);
    
        return view('pedidos.index', compact('pedidos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pedidos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome_navio' => 'required|string|max:255',
            'numero_imo' => 'required|string|max:20',
            'indicativo_chamada' => 'required|string|max:50',
            'numero_viagem' => 'required|string|max:50',
            'bandeira_navio' => 'required|string|max:100',
            'data_chegada' => 'required|date',
            'hora_chegada' => 'required|date_format:H:i',
            'data_partida' => 'required|date|after_or_equal:data_chegada',
            'hora_partida' => 'required|date_format:H:i',
            'nome_agente' => 'required|string|max:255',
            'contato_agente' => 'required|string|max:255',
            'numero_tripulantes' => 'required|integer|min:0',
            'numero_passageiros' => 'required|integer|min:0',
            'observacoes_operacao' => 'nullable|string',
            
            // Arquivos
            'certificados_navio.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'declaracoes_cargas.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'declaracao_provisoes_bordo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'declaracao_pertences_tripulacao' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'documentos_tripulantes.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'documentos_passageiros.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'declaracao_mercadorias_perigosas.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Mapear numero_viagem para viagem_numero (campo correto da base de dados)
            $validated['viagem_numero'] = $validated['numero_viagem'];
            unset($validated['numero_viagem']);
    
            // Processar uploads de arquivos
            $arquivos = $this->processarArquivos($request);
    
            $pedido = Pedido::create([
                ...$validated,
                'user_id' => Auth::id(),
                ...$arquivos
            ]);

            // Enviar e-mails para administradores e lista de notificação
            try {
                $emailsAdmins = ConfigHelper::emailsAdmins();
                $emailsNotificacaoPedidos = ConfigHelper::emailsNotificacaoPedidos();
                
                // Combinar e remover duplicatas
                $todosEmails = array_unique(array_merge($emailsAdmins, $emailsNotificacaoPedidos));
                
                if (empty($todosEmails)) {
                    Log::warning('Nenhum e-mail encontrado para envio de notificação');
                    return redirect()->route('pedidos.index')->with('success', 'Pedido criado com sucesso!');
                }
                
                Log::info('Iniciando envio de e-mails para: ' . json_encode($todosEmails));
                
                // Removido o carregamento de documentos que estava causando erro
                // $pedido->load('documentos');
                
                foreach ($todosEmails as $email) {
                    try {
                        Mail::to($email)->send(new \App\Mail\NovoPedidoNotification($pedido));
                        Log::info('E-mail enviado com sucesso para: ' . $email);
                    } catch (\Exception $e) {
                        Log::error('Erro ao enviar e-mail para ' . $email . ': ' . $e->getMessage());
                    }
                }
                
                Log::info('Processo de envio de e-mails concluído');
            } catch (\Exception $e) {
                Log::error('Erro geral no envio de e-mails: ' . $e->getMessage());
            }
    
            DB::commit();
    
            return redirect()->route('pedidos.show', $pedido)
                           ->with('success', 'Pedido criado com sucesso!');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar pedido', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Erro ao criar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Pedido $pedido)
    {
        // Verificar se o usuário pode ver este pedido
        if (Auth::user()->hasRole('agente_navio') && $pedido->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para ver este pedido.');
        }

        $pedido->load(['user', 'decisor']);
        return view('pedidos.show', compact('pedido'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pedido $pedido)
    {
        // Verificar se o usuário pode editar este pedido
        if (Auth::user()->hasRole('agente_navio') && $pedido->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para editar este pedido.');
        }

        if (!$pedido->podeSerEditado()) {
            return back()->with('error', 'Este pedido não pode ser editado.');
        }

        return view('pedidos.edit', compact('pedido'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pedido $pedido)
    {
        // Verificar se o usuário pode atualizar este pedido
        if (Auth::user()->hasRole('agente_navio') && $pedido->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para atualizar este pedido.');
        }

        if (!$pedido->podeSerEditado()) {
            return back()->with('error', 'Este pedido não pode ser editado.');
        }

        $validated = $request->validate([
            'nome_navio' => 'required|string|max:255',
            'numero_imo' => 'required|string|max:20',
            'indicativo_chamada' => 'required|string|max:50',
            'numero_viagem' => 'required|string|max:50', // Corrigido: era viagem_numero
            'bandeira_navio' => 'required|string|max:100',
            'data_chegada' => 'date',
            'hora_chegada' => 'date_format:H:i',
            'data_partida' => 'date|after_or_equal:data_chegada',
            'hora_partida' => 'date_format:H:i',
            'nome_agente' => 'required|string|max:255',
            'contato_agente' => 'required|string|max:255',
            'numero_tripulantes' => 'required|integer|min:0',
            'numero_passageiros' => 'required|integer|min:0',
            'observacoes_operacao' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Mapear numero_viagem para viagem_numero (campo correto da base de dados)
            $validated['viagem_numero'] = $validated['numero_viagem'];
            unset($validated['numero_viagem']);

            // Processar novos arquivos se houver
            $arquivos = $this->processarArquivos($request, $pedido);

            $pedido->update([
                ...$validated,
                ...$arquivos
            ]);

            DB::commit();

            return redirect()->route('pedidos.show', $pedido)
                           ->with('success', 'Pedido atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar pedido: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao atualizar pedido. Tente novamente.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pedido $pedido)
    {
        if ($pedido->status !== 'pendente') {
            return back()->with('error', 'Pedidos já decididos não podem ser excluídos.');
        }

        try {
            // Remover arquivos do storage
            $this->removerArquivos($pedido);
            
            $pedido->delete();

            return redirect()->route('pedidos.index')
                           ->with('success', 'Pedido excluído com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao excluir pedido: ' . $e->getMessage());
            return back()->with('error', 'Erro ao excluir pedido. Tente novamente.');
        }
    }

    /**
     * Aprovar pedido
     */
    public function aprovar(Pedido $pedido)
    {
        if ($pedido->status !== 'pendente' && $pedido->status !== 'em_analise') {
            return back()->with('error', 'Este pedido não pode ser aprovado.');
        }

        $pedido->update([
            'status' => 'aprovado',
            'decidido_por' => Auth::id()
        ]);

        return back()->with('success', 'Pedido aprovado com sucesso!');
    }

    /**
     * Rejeitar pedido
     */
    public function rejeitar(Request $request, Pedido $pedido)
    {
        if ($pedido->status !== 'pendente' && $pedido->status !== 'em_analise') {
            return back()->with('error', 'Este pedido não pode ser rejeitado.');
        }

        $pedido->update([
            'status' => 'rejeitado',
            'decidido_por' => Auth::id()
        ]);

        return back()->with('success', 'Pedido rejeitado com sucesso!');
    }

    /**
     * Revogar pedido (apenas admin)
     */
    public function revogar(Request $request, Pedido $pedido)
    {
        // Verificar se o usuário é admin
        if (!Auth::user()->hasRole('admin')) {
            return back()->with('error', 'Apenas administradores podem revogar pedidos.');
        }

        // Verificar se o pedido pode ser revogado
        if (!in_array($pedido->status, ['aprovado', 'rejeitado'])) {
            return back()->with('error', 'Apenas pedidos aprovados ou rejeitados podem ser revogados.');
        }

        $request->validate([
            'motivo_revogacao' => 'required|string|max:500'
        ], [
            'motivo_revogacao.required' => 'O motivo da revogação é obrigatório.'
        ]);

        try {
            $statusAnterior = $pedido->status;
            
            $pedido->update([
                'status' => 'pendente',
                'decidido_por' => null,
                'observacoes' => ($pedido->observacoes ? $pedido->observacoes . "\n\n" : '') . 
                    "REVOGADO EM " . now()->format('d/m/Y H:i') . " POR " . Auth::user()->name . ":\n" . 
                    "Status anterior: " . ucfirst($statusAnterior) . "\n" .
                    "Motivo: " . $request->motivo_revogacao
            ]);

            return back()->with('success', 'Pedido revogado com sucesso! Status alterado para pendente.');

        } catch (\Exception $e) {
            Log::error('Erro ao revogar pedido: ' . $e->getMessage());
            return back()->with('error', 'Erro ao revogar pedido. Tente novamente.');
        }
    }

    /**
     * Processar upload de arquivos
     */
    private function processarArquivos(Request $request, Pedido $pedido = null): array
    {
        $arquivos = [];

        // Certificados do navio
        if ($request->hasFile('certificados_navio')) {
            $paths = [];
            foreach ($request->file('certificados_navio') as $file) {
                $path = $file->store('pedidos/certificados', 'private');
                $paths[] = $path;
            }
            $arquivos['certificados_navio'] = $paths;
        }

        // Declarações de cargas
        if ($request->hasFile('declaracoes_cargas')) {
            $paths = [];
            foreach ($request->file('declaracoes_cargas') as $file) {
                $path = $file->store('pedidos/declaracoes_cargas', 'private');
                $paths[] = $path;
            }
            $arquivos['declaracoes_cargas'] = $paths;
        }

        // Declaração de provisões de bordo
        if ($request->hasFile('declaracao_provisoes_bordo')) {
            $paths = [];
            foreach ($request->file('declaracao_provisoes_bordo') as $file) {
                $path = $file->store('pedidos/provisoes', 'private');
                $paths[] = $path;
            }
            $arquivos['declaracao_provisoes_bordo'] = $paths;
        }

        // Declaração de pertences da tripulação
        if ($request->hasFile('declaracao_pertences_tripulacao')) {
            $paths = [];
            foreach ($request->file('declaracao_pertences_tripulacao') as $file) {
                $path = $file->store('pedidos/pertences', 'private');
                $paths[] = $path;
            }
            $arquivos['declaracao_pertences_tripulacao'] = $paths;
        }

        // Declaração de mercadorias perigosas (agora múltiplos arquivos)
        if ($request->hasFile('declaracao_mercadorias_perigosas')) {
            $paths = [];
            foreach ($request->file('declaracao_mercadorias_perigosas') as $file) {
                $path = $file->store('pedidos/mercadorias_perigosas', 'private');
                $paths[] = $path;
            }
            $arquivos['declaracao_mercadorias_perigosas'] = $paths;
        }

        // Documentos de tripulantes
        if ($request->hasFile('documentos_tripulantes')) {
            $paths = [];
            foreach ($request->file('documentos_tripulantes') as $file) {
                $path = $file->store('pedidos/documentos_tripulantes', 'private');
                $paths[] = $path;
            }
            $arquivos['documentos_tripulantes'] = $paths;
        }

        // Documentos de passageiros
        if ($request->hasFile('documentos_passageiros')) {
            $paths = [];
            foreach ($request->file('documentos_passageiros') as $file) {
                $path = $file->store('pedidos/documentos_passageiros', 'private');
                $paths[] = $path;
            }
            $arquivos['documentos_passageiros'] = $paths;
        }

        return $arquivos;
    }

    /**
     * Remover arquivos do storage
     */
    private function removerArquivos(Pedido $pedido): void
    {
        $campos = [
            'certificados_navio',
            'declaracoes_cargas',
            'declaracao_provisoes_bordo',
            'declaracao_pertences_tripulacao',
            'documentos_tripulantes',
            'documentos_passageiros',
            'declaracao_mercadorias_perigosas'
        ];

        foreach ($campos as $campo) {
            if ($pedido->$campo) {
                foreach ($pedido->$campo as $path) {
                    Storage::disk('private')->delete($path);
                }
            }
        }
    }

    /**
     * Download de arquivo
     */
    public function downloadArquivo(Pedido $pedido, $tipo, $index = 0)
    {
        if (!isset($pedido->$tipo) || !isset($pedido->$tipo[$index])) {
            abort(404);
        }

        $path = $pedido->$tipo[$index];
        
        if (!Storage::disk('private')->exists($path)) {
            abort(404);
        }

        return Storage::disk('private')->download($path);
    }

    /**
     * Generate report with filtered pedidos (PDF or Excel)
     */
    public function relatorioFiltrado(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf'); // Default para PDF
            
            // Aplicar os mesmos filtros do método index
            $query = Pedido::with(['user', 'decisor']);
            
            // Restrição de acesso: agente_navio só vê seus próprios pedidos
            if (Auth::user()->hasRole('agente_navio')) {
                $query->where('user_id', Auth::id());
            }
            
            // Filtros
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nome_navio', 'like', "%{$search}%")
                      ->orWhere('numero_imo', 'like', "%{$search}%")
                      ->orWhere('indicativo_chamada', 'like', "%{$search}%")
                      ->orWhere('nome_agente', 'like', "%{$search}%");
                });
            }
            
            // Filtro de variação de data
            if ($request->filled('data_inicio') && $request->filled('data_fim')) {
                $query->whereBetween('data_chegada', [$request->data_inicio, $request->data_fim]);
            }
            
            $pedidos = $query->orderBy('created_at', 'desc')->get();
            
            // Preparar filtros aplicados para exibição no relatório
            $filtrosAplicados = [];
            if ($request->filled('status')) {
                $filtrosAplicados['Status'] = ucfirst($request->status);
            }
            if ($request->filled('search')) {
                $filtrosAplicados['Busca'] = $request->search;
            }
            if ($request->filled('data_inicio') && $request->filled('data_fim')) {
                $filtrosAplicados['Período'] = date('d/m/Y', strtotime($request->data_inicio)) . ' a ' . date('d/m/Y', strtotime($request->data_fim));
            }
            
            // Gerar relatório baseado no formato
            if ($formato === 'excel') {
                return $this->generateExcelReport($pedidos, $filtrosAplicados);
            } else {
                return $this->generatePdfReport($pedidos, $filtrosAplicados);
            }
            
        } catch (\Exception $e) {
            Log::error('PedidoController@relatorioFiltrado: Erro ao gerar relatório', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF report
     */
    private function generatePdfReport($pedidos, $filtrosAplicados)
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
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pedidos.relatorio-pdf', compact('pedidos', 'configuracoes', 'filtrosAplicados'));
        
        // Configurações do PDF
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'Arial',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
            'dpi' => 150
        ]);

        $filename = 'relatorio_pedidos_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Generate Excel report
     */
    private function generateExcelReport($pedidos, $filtrosAplicados)
    {
        $filename = 'relatorio_pedidos_' . now()->format('Y-m-d_H-i-s') . '.xls';
        
        // Gerar conteúdo CSV compatível com Excel
        $csvContent = $this->generateExcelCsv($pedidos, $filtrosAplicados);
        
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
    private function generateExcelCsv($pedidos, $filtrosAplicados)
    {
        $output = fopen('php://temp', 'r+');
        
        // BOM para UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // Cabeçalho do relatório
        fputcsv($output, ['RELATÓRIO DE PEDIDOS'], ';');
        fputcsv($output, ['Data de Geração: ' . now()->format('d/m/Y H:i:s')], ';');
        fputcsv($output, ['Total de Registros: ' . $pedidos->count()], ';');
        fputcsv($output, ['Gerado por: ' . Auth::user()->name], ';');
        fputcsv($output, [''], ';'); // Linha em branco
        
        // Filtros aplicados
        if (!empty($filtrosAplicados)) {
            fputcsv($output, ['FILTROS APLICADOS:'], ';');
            foreach ($filtrosAplicados as $filtro => $valor) {
                fputcsv($output, [$filtro . ': ' . $valor], ';');
            }
            fputcsv($output, [''], ';'); // Linha em branco
        }
        
        // Resumo estatístico
        fputcsv($output, ['RESUMO:'], ';');
        fputcsv($output, ['Pendentes: ' . $pedidos->where('status', 'pendente')->count()], ';');
        fputcsv($output, ['Aprovados: ' . $pedidos->where('status', 'aprovado')->count()], ';');
        fputcsv($output, ['Rejeitados: ' . $pedidos->where('status', 'rejeitado')->count()], ';');
        fputcsv($output, ['Total: ' . $pedidos->count()], ';');
        fputcsv($output, [''], ';'); // Linha em branco
        
        // Cabeçalhos das colunas
        fputcsv($output, [
            'Data Criação',
            'Nome do Navio',
            'IMO',
            'Indicativo',
            'Data Chegada',
            'Nome do Agente',
            'Solicitante',
            'Status',
            'Decisor',
            'Observações'
        ], ';');
        
        // Dados dos pedidos
        foreach ($pedidos as $pedido) {
            fputcsv($output, [
                $pedido->created_at->format('d/m/Y'),
                $pedido->nome_navio,
                $pedido->numero_imo,
                $pedido->indicativo_chamada,
                $pedido->data_chegada ? date('d/m/Y', strtotime($pedido->data_chegada)) : '',
                $pedido->nome_agente,
                $pedido->user->name ?? '',
                ucfirst($pedido->status),
                $pedido->decisor->name ?? '',
                $pedido->observacoes
            ], ';');
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }

    private function enviarNotificacoesPedido(Pedido $pedido): void
    {
        try {
            Log::info('Iniciando envio de notificações de pedido', ['pedido_id' => $pedido->id]);
            
            // Verificar se as notificações por e-mail estão ativadas
            $notificacaoAtiva = ConfigHelper::notificacaoEmailAtiva();
            Log::info('Status da notificação por email', ['ativa' => $notificacaoAtiva]);
            
            if (!$notificacaoAtiva) {
                Log::info('Notificações por e-mail desativadas', ['pedido_id' => $pedido->id]);
                return;
            }

            // Obter lista de e-mails dos administradores
            $emailsAdmins = ConfigHelper::emailsAdmins();
            Log::info('Emails dos administradores para notificação', ['emails' => $emailsAdmins, 'count' => count($emailsAdmins)]);
            
            // Obter lista de e-mails de notificação de pedidos
            $emailsNotificacaoPedidos = ConfigHelper::emailsNotificacaoPedidos();
            Log::info('Emails de notificação de pedidos', ['emails' => $emailsNotificacaoPedidos, 'count' => count($emailsNotificacaoPedidos)]);
            
            // Combinar e remover duplicatas
            $todosEmails = array_unique(array_merge($emailsAdmins, $emailsNotificacaoPedidos));
            Log::info('Total de emails para notificação', ['emails' => $todosEmails, 'count' => count($todosEmails)]);
            
            if (empty($todosEmails)) {
                Log::warning('Nenhum e-mail encontrado para notificações de pedidos', ['pedido_id' => $pedido->id]);
                return;
            }

            // Carregar documentos do pedido para incluir nos anexos
            $pedido->load('documentos');
            Log::info('Documentos carregados', ['pedido_id' => $pedido->id, 'documentos_count' => $pedido->documentos->count()]);

            // Enviar e-mail para cada destinatário
            foreach ($todosEmails as $email) {
                try {
                    Log::info('Tentando enviar email', ['pedido_id' => $pedido->id, 'destinatario' => $email]);
                    
                    Mail::to($email)->send(new NovoPedidoNotification($pedido));
                    
                    Log::info('E-mail de notificação enviado com sucesso', [
                        'pedido_id' => $pedido->id,
                        'destinatario' => $email,
                        'total_anexos' => $pedido->documentos->count()
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erro ao enviar e-mail de notificação', [
                        'pedido_id' => $pedido->id,
                        'destinatario' => $email,
                        'erro' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro geral ao enviar notificações de pedido', [
                'pedido_id' => $pedido->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
