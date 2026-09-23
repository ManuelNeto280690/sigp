<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FalDocumentoController extends Controller
{
    /**
     * Exibir página de upload de documentos para um pedido
     */
    public function index(Pedido $pedido)
    {
        // Verificar permissões
        if (!auth()->user()->can('view', $pedido)) {
            abort(403, 'Você não tem permissão para visualizar este pedido.');
        }

        // Carregar documentos existentes agrupados por tipo
        $documentos = $pedido->documentos()
            ->with(['uploadedBy', 'aprovadoPor'])
            ->orderBy('tipo_documento')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('tipo_documento');

        // Carregar FALs para verificar status
        $pedido->load([
            'fal1DeclaracaoGeral',
            'fal2DeclaracaoCarga', 
            'fal3ProvisoesBordo',
            'fal4PertencessTripulacao',
            'fal5ListaTripulantes',
            'fal6ListaPassageiros',
            'fal7MercadoriasPerigosas'
        ]);

        // Tipos de documentos disponíveis
        $tiposDocumentos = [
            'fal1_declaracao_geral' => 'FAL 1 - Declaração Geral',
            'fal2_declaracao_carga' => 'FAL 2 - Declaração de Carga',
            'fal3_provisoes_bordo' => 'FAL 3 - Provisões de Bordo',
            'fal4_pertences_tripulacao' => 'FAL 4 - Pertences da Tripulação',
            'fal5_documentos_tripulantes' => 'FAL 5 - Documentos dos Tripulantes',
            'fal6_documentos_passageiros' => 'FAL 6 - Documentos dos Passageiros',
            'fal7_mercadorias_perigosas' => 'FAL 7 - Mercadorias Perigosas',
            'documento_adicional' => 'Documento Adicional'
        ];

        return view('pedidos.documentos.index', compact('pedido', 'documentos', 'tiposDocumentos'));
    }

    /**
     * Upload de documentos
     */
    public function upload(Request $request, Pedido $pedido)
    {
        // Verificar permissões
        if (!auth()->user()->can('update', $pedido)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para fazer upload de documentos neste pedido.'
            ], 403);
        }

        $request->validate([
            'tipo_documento' => 'required|in:fal1_declaracao_geral,fal2_declaracao_carga,fal3_provisoes_bordo,fal4_pertences_tripulacao,fal5_documentos_tripulantes,fal6_documentos_passageiros,fal7_mercadorias_perigosas,documento_adicional',
            'arquivos' => 'required|array|max:10',
            'arquivos.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx|max:10240', // 10MB max
            'descricao' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $documentosUpload = [];
            $errors = [];

            foreach ($request->file('arquivos') as $arquivo) {
                try {
                    // Gerar nome único para o arquivo
                    $nomeOriginal = $arquivo->getClientOriginalName();
                    $extensao = $arquivo->getClientOriginalExtension();
                    $nomeArquivo = Str::uuid() . '.' . $extensao;
                    
                    // Definir caminho baseado no tipo de documento
                    $caminho = "pedidos/{$pedido->id}/documentos/{$request->tipo_documento}";
                    
                    // Fazer upload do arquivo
                    $caminhoCompleto = $arquivo->storeAs($caminho, $nomeArquivo, 'private');
                    
                    // Criar registro no banco
                    $documento = PedidoDocumento::create([
                        'pedido_id' => $pedido->id,
                        'nome_original' => $nomeOriginal,
                        'nome_arquivo' => $nomeArquivo,
                        'caminho_arquivo' => $caminhoCompleto,
                        'tipo_mime' => $arquivo->getMimeType(),
                        'tamanho' => $arquivo->getSize(),
                        'extensao' => $extensao,
                        'tipo_documento' => $request->tipo_documento,
                        'descricao' => $request->descricao,
                        'uploaded_by' => Auth::id(),
                        'uploaded_at' => now(),
                        'status' => 'pendente'
                    ]);

                    $documentosUpload[] = $documento;

                    Log::info('Documento uploaded com sucesso', [
                        'pedido_id' => $pedido->id,
                        'documento_id' => $documento->id,
                        'nome_original' => $nomeOriginal,
                        'tipo_documento' => $request->tipo_documento
                    ]);

                } catch (\Exception $e) {
                    $errors[] = "Erro ao fazer upload do arquivo {$nomeOriginal}: " . $e->getMessage();
                    Log::error('Erro no upload de documento', [
                        'pedido_id' => $pedido->id,
                        'arquivo' => $nomeOriginal,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Atualizar status do FAL correspondente se necessário
            $this->atualizarStatusFal($pedido, $request->tipo_documento);

            // Recalcular progresso dos FALs
            $pedido->atualizarProgressoFals();

            DB::commit();

            $message = count($documentosUpload) . ' documento(s) enviado(s) com sucesso.';
            if (!empty($errors)) {
                $message .= ' Alguns arquivos apresentaram erro: ' . implode(', ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'documentos' => $documentosUpload->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'nome_original' => $doc->nome_original,
                        'tipo_documento' => $doc->tipo_documento,
                        'status' => $doc->status,
                        'uploaded_at' => $doc->uploaded_at->format('d/m/Y H:i')
                    ];
                })
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro geral no upload de documentos', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Download de documento
     */
    public function download(PedidoDocumento $documento)
    {
        // Verificar permissões
        if (!auth()->user()->can('view', $documento->pedido)) {
            abort(403, 'Você não tem permissão para baixar este documento.');
        }

        // Verificar se o arquivo existe
        if (!Storage::disk('private')->exists($documento->caminho_arquivo)) {
            abort(404, 'Arquivo não encontrado.');
        }

        // Log do download
        Log::info('Download de documento', [
            'documento_id' => $documento->id,
            'pedido_id' => $documento->pedido_id,
            'user_id' => Auth::id(),
            'nome_arquivo' => $documento->nome_original
        ]);

        return Storage::disk('private')->download(
            $documento->caminho_arquivo,
            $documento->nome_original
        );
    }

    /**
     * Visualizar documento (para PDFs e imagens)
     */
    public function view(PedidoDocumento $documento)
    {
        // Verificar permissões
        if (!auth()->user()->can('view', $documento->pedido)) {
            abort(403, 'Você não tem permissão para visualizar este documento.');
        }

        // Verificar se é um tipo visualizável
        $tiposVisualizaveis = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array(strtolower($documento->extensao), $tiposVisualizaveis)) {
            abort(400, 'Este tipo de arquivo não pode ser visualizado no navegador.');
        }

        // Verificar se o arquivo existe
        if (!Storage::disk('private')->exists($documento->caminho_arquivo)) {
            abort(404, 'Arquivo não encontrado.');
        }

        $conteudo = Storage::disk('private')->get($documento->caminho_arquivo);
        
        return response($conteudo)
            ->header('Content-Type', $documento->tipo_mime)
            ->header('Content-Disposition', 'inline; filename="' . $documento->nome_original . '"');
    }

    /**
     * Excluir documento
     */
    public function destroy(PedidoDocumento $documento)
    {
        // Verificar permissões
        if (!auth()->user()->can('update', $documento->pedido)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para excluir este documento.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Excluir arquivo do storage
            if (Storage::disk('private')->exists($documento->caminho_arquivo)) {
                Storage::disk('private')->delete($documento->caminho_arquivo);
            }

            // Excluir registro do banco
            $documento->delete();

            // Recalcular progresso dos FALs
            $documento->pedido->atualizarProgressoFals();

            DB::commit();

            Log::info('Documento excluído', [
                'documento_id' => $documento->id,
                'pedido_id' => $documento->pedido_id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documento excluído com sucesso.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir documento', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir documento.'
            ], 500);
        }
    }

    /**
     * Aprovar documento
     */
    public function aprovar(PedidoDocumento $documento)
    {
        // Verificar permissões (apenas admin/operador)
        if (!auth()->user()->hasAnyRole(['admin', 'operador'])) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para aprovar documentos.'
            ], 403);
        }

        try {
            $documento->update([
                'status' => 'aprovado',
                'aprovado_por' => Auth::id(),
                'aprovado_em' => now()
            ]);

            // Verificar se todos os documentos do FAL foram aprovados
            $this->verificarAprovacaoCompletaFal($documento->pedido, $documento->tipo_documento);

            Log::info('Documento aprovado', [
                'documento_id' => $documento->id,
                'pedido_id' => $documento->pedido_id,
                'aprovado_por' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documento aprovado com sucesso.'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao aprovar documento', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao aprovar documento.'
            ], 500);
        }
    }

    /**
     * Rejeitar documento
     */
    public function rejeitar(Request $request, PedidoDocumento $documento)
    {
        // Verificar permissões (apenas admin/operador)
        if (!auth()->user()->hasAnyRole(['admin', 'operador'])) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para rejeitar documentos.'
            ], 403);
        }

        $request->validate([
            'motivo' => 'required|string|max:500'
        ]);

        try {
            $documento->update([
                'status' => 'rejeitado',
                'aprovado_por' => Auth::id(),
                'aprovado_em' => now(),
                'observacoes_aprovacao' => $request->motivo
            ]);

            Log::info('Documento rejeitado', [
                'documento_id' => $documento->id,
                'pedido_id' => $documento->pedido_id,
                'rejeitado_por' => Auth::id(),
                'motivo' => $request->motivo
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documento rejeitado.'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao rejeitar documento', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao rejeitar documento.'
            ], 500);
        }
    }

    /**
     * Atualizar status do FAL baseado nos documentos
     */
    private function atualizarStatusFal(Pedido $pedido, string $tipoDocumento)
    {
        // Mapear tipo de documento para FAL
        $mapeamentoFal = [
            'fal1_declaracao_geral' => 'fal1DeclaracaoGeral',
            'fal2_declaracao_carga' => 'fal2DeclaracaoCarga',
            'fal3_provisoes_bordo' => 'fal3ProvisoesBordo',
            'fal4_pertences_tripulacao' => 'fal4PertencessTripulacao',
            'fal5_documentos_tripulantes' => 'fal5ListaTripulantes',
            'fal6_documentos_passageiros' => 'fal6ListaPassageiros',
            'fal7_mercadorias_perigosas' => 'fal7MercadoriasPerigosas'
        ];

        if (isset($mapeamentoFal[$tipoDocumento])) {
            $relacao = $mapeamentoFal[$tipoDocumento];
            $fal = $pedido->$relacao;
            
            if ($fal && $fal->status === 'pendente') {
                $fal->update(['status' => 'submetido']);
            }
        }
    }

    /**
     * Verificar se todos os documentos de um FAL foram aprovados
     */
    private function verificarAprovacaoCompletaFal(Pedido $pedido, string $tipoDocumento)
    {
        // Contar documentos pendentes do mesmo tipo
        $documentosPendentes = $pedido->documentos()
            ->where('tipo_documento', $tipoDocumento)
            ->where('status', '!=', 'aprovado')
            ->count();

        // Se não há documentos pendentes, aprovar o FAL
        if ($documentosPendentes === 0) {
            $mapeamentoFal = [
                'fal1_declaracao_geral' => 'fal1DeclaracaoGeral',
                'fal2_declaracao_carga' => 'fal2DeclaracaoCarga',
                'fal3_provisoes_bordo' => 'fal3ProvisoesBordo',
                'fal4_pertences_tripulacao' => 'fal4PertencessTripulacao',
                'fal5_documentos_tripulantes' => 'fal5ListaTripulantes',
                'fal6_documentos_passageiros' => 'fal6ListaPassageiros',
                'fal7_mercadorias_perigosas' => 'fal7MercadoriasPerigosas'
            ];

            if (isset($mapeamentoFal[$tipoDocumento])) {
                $relacao = $mapeamentoFal[$tipoDocumento];
                $fal = $pedido->$relacao;
                
                if ($fal) {
                    $fal->aprovar(Auth::id(), 'Todos os documentos foram aprovados.');
                }
            }
        }

        // Recalcular progresso geral
        $pedido->atualizarProgressoFals();
    }
}