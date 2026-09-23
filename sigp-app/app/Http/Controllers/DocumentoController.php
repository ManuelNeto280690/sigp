<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DocumentoController extends Controller
{
    /**
     * Upload de documentos para um FAL específico
     */
    public function upload(Request $request, Pedido $pedido)
    {
        // Verificar se o usuário tem permissão para editar este pedido
        if (!auth()->user()->can('update', $pedido)) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'required|in:fal1_declaracao_geral,fal2_declaracao_carga,fal3_provisoes_bordo,fal4_pertences_tripulacao,fal5_documentos_tripulantes,fal6_documentos_passageiros,fal7_mercadorias_perigosas,documento_adicional',
            'arquivos' => 'required|array|min:1|max:10',
            'arquivos.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx|max:10240', // 10MB max
            'descricao' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Dados inválidos',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            $documentosUpload = [];
            $tipoDocumento = $request->tipo_documento;
            
            foreach ($request->file('arquivos') as $arquivo) {
                // Gerar nome único para o arquivo
                $nomeOriginal = $arquivo->getClientOriginalName();
                $extensao = $arquivo->getClientOriginalExtension();
                $nomeArquivo = Str::uuid() . '.' . $extensao;
                
                // Definir caminho baseado no tipo de documento e pedido
                $caminhoArquivo = "pedidos/{$pedido->id}/{$tipoDocumento}/{$nomeArquivo}";
                
                // Fazer upload para o disco privado
                $arquivo->storeAs("pedidos/{$pedido->id}/{$tipoDocumento}", $nomeArquivo, 'private');
                
                // Criar registro no banco
                $documento = PedidoDocumento::create([
                    'pedido_id' => $pedido->id,
                    'nome_original' => $nomeOriginal,
                    'nome_arquivo' => $nomeArquivo,
                    'caminho_arquivo' => $caminhoArquivo,
                    'tipo_mime' => $arquivo->getMimeType(),
                    'tamanho' => $arquivo->getSize(),
                    'extensao' => $extensao,
                    'tipo_documento' => $tipoDocumento,
                    'descricao' => $request->descricao,
                    'uploaded_by' => Auth::id(),
                    'uploaded_at' => now(),
                    'status' => 'pendente'
                ]);

                $documentosUpload[] = [
                    'id' => $documento->id,
                    'nome_original' => $documento->nome_original,
                    'tamanho' => $documento->tamanho,
                    'tipo_mime' => $documento->tipo_mime,
                    'uploaded_at' => $documento->uploaded_at->format('d/m/Y H:i')
                ];
            }

            // Atualizar status do FAL correspondente
            $this->atualizarStatusFal($pedido, $tipoDocumento);

            // Recalcular progresso dos FALs
            $pedido->atualizarProgressoFals();

            Log::info('Documentos enviados com sucesso', [
                'pedido_id' => $pedido->id,
                'tipo_documento' => $tipoDocumento,
                'quantidade_arquivos' => count($documentosUpload),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documentos enviados com sucesso!',
                'documentos' => $documentosUpload,
                'progresso_fals' => $pedido->fresh()->progresso_fals
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao fazer upload de documentos', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível fazer upload dos documentos'
            ], 500);
        }
    }

    /**
     * Listar documentos de um pedido
     */
    public function index(Pedido $pedido)
    {
        // Verificar se o usuário tem permissão para ver este pedido
        if (!auth()->user()->can('view', $pedido)) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $documentos = PedidoDocumento::where('pedido_id', $pedido->id)
            ->with(['uploadedBy:id,name', 'aprovadoPor:id,name'])
            ->orderBy('tipo_documento')
            ->orderBy('uploaded_at', 'desc')
            ->get()
            ->groupBy('tipo_documento');

        return response()->json([
            'success' => true,
            'documentos' => $documentos
        ]);
    }

    /**
     * Download de um documento específico
     */
    public function download(Pedido $pedido, PedidoDocumento $documento)
    {
        // Verificar se o documento pertence ao pedido
        if ($documento->pedido_id !== $pedido->id) {
            abort(404);
        }

        // Verificar se o usuário tem permissão para ver este pedido
        if (!auth()->user()->can('view', $pedido)) {
            abort(403);
        }

        // Verificar se o arquivo existe
        if (!Storage::disk('private')->exists($documento->caminho_arquivo)) {
            abort(404, 'Arquivo não encontrado');
        }

        // Log do download
        Log::info('Download de documento', [
            'documento_id' => $documento->id,
            'pedido_id' => $pedido->id,
            'user_id' => Auth::id(),
            'nome_arquivo' => $documento->nome_original
        ]);

        return Storage::disk('private')->download(
            $documento->caminho_arquivo,
            $documento->nome_original
        );
    }

    /**
     * Excluir um documento
     */
    public function destroy(Pedido $pedido, PedidoDocumento $documento)
    {
        // Verificar se o documento pertence ao pedido
        if ($documento->pedido_id !== $pedido->id) {
            return response()->json(['error' => 'Documento não encontrado'], 404);
        }

        // Verificar se o usuário tem permissão para editar este pedido
        if (!auth()->user()->can('update', $pedido)) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Verificar se o documento não está aprovado
        if ($documento->status === 'aprovado') {
            return response()->json([
                'error' => 'Não é possível excluir documento aprovado'
            ], 422);
        }

        try {
            // Excluir arquivo do storage
            if (Storage::disk('private')->exists($documento->caminho_arquivo)) {
                Storage::disk('private')->delete($documento->caminho_arquivo);
            }

            // Excluir registro do banco
            $documento->delete();

            // Atualizar status do FAL correspondente
            $this->atualizarStatusFal($pedido, $documento->tipo_documento);

            // Recalcular progresso dos FALs
            $pedido->atualizarProgressoFals();

            Log::info('Documento excluído', [
                'documento_id' => $documento->id,
                'pedido_id' => $pedido->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documento excluído com sucesso',
                'progresso_fals' => $pedido->fresh()->progresso_fals
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao excluir documento', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Aprovar um documento
     */
    public function aprovar(Pedido $pedido, PedidoDocumento $documento, Request $request)
    {
        // Verificar se o documento pertence ao pedido
        if ($documento->pedido_id !== $pedido->id) {
            return response()->json(['error' => 'Documento não encontrado'], 404);
        }

        // Verificar se o usuário tem permissão para aprovar
        if (!auth()->user()->hasRole(['admin', 'operador'])) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $request->validate([
            'observacoes' => 'nullable|string|max:500'
        ]);

        try {
            $documento->update([
                'status' => 'aprovado',
                'aprovado_por' => Auth::id(),
                'aprovado_em' => now(),
                'observacoes_aprovacao' => $request->observacoes
            ]);

            // Atualizar status do FAL correspondente
            $this->atualizarStatusFal($pedido, $documento->tipo_documento);

            // Recalcular progresso dos FALs
            $pedido->atualizarProgressoFals();

            Log::info('Documento aprovado', [
                'documento_id' => $documento->id,
                'pedido_id' => $pedido->id,
                'aprovado_por' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documento aprovado com sucesso',
                'progresso_fals' => $pedido->fresh()->progresso_fals
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao aprovar documento', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Rejeitar um documento
     */
    public function rejeitar(Pedido $pedido, PedidoDocumento $documento, Request $request)
    {
        // Verificar se o documento pertence ao pedido
        if ($documento->pedido_id !== $pedido->id) {
            return response()->json(['error' => 'Documento não encontrado'], 404);
        }

        // Verificar se o usuário tem permissão para rejeitar
        if (!auth()->user()->hasRole(['admin', 'operador'])) {
            return response()->json(['error' => 'Não autorizado'], 403);
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

            // Atualizar status do FAL correspondente
            $this->atualizarStatusFal($pedido, $documento->tipo_documento);

            // Recalcular progresso dos FALs
            $pedido->atualizarProgressoFals();

            Log::info('Documento rejeitado', [
                'documento_id' => $documento->id,
                'pedido_id' => $pedido->id,
                'rejeitado_por' => Auth::id(),
                'motivo' => $request->motivo
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documento rejeitado',
                'progresso_fals' => $pedido->fresh()->progresso_fals
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao rejeitar documento', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Atualizar status do FAL baseado nos documentos
     */
    private function atualizarStatusFal(Pedido $pedido, string $tipoDocumento)
    {
        $documentos = PedidoDocumento::where('pedido_id', $pedido->id)
            ->where('tipo_documento', $tipoDocumento)
            ->get();

        if ($documentos->isEmpty()) {
            $status = 'pendente';
        } elseif ($documentos->every(fn($doc) => $doc->status === 'aprovado')) {
            $status = 'aprovado';
        } elseif ($documentos->some(fn($doc) => $doc->status === 'rejeitado')) {
            $status = 'rejeitado';
        } else {
            $status = 'submetido';
        }

        // Mapear tipo de documento para modelo FAL
        $falMap = [
            'fal1_declaracao_geral' => 'fal1DeclaracaoGeral',
            'fal2_declaracao_carga' => 'fal2DeclaracaoCarga',
            'fal3_provisoes_bordo' => 'fal3ProvisoesBordo',
            'fal4_pertences_tripulacao' => 'fal4PertencessTripulacao',
            'fal5_documentos_tripulantes' => 'fal5ListaTripulantes',
            'fal6_documentos_passageiros' => 'fal6ListaPassageiros',
            'fal7_mercadorias_perigosas' => 'fal7MercadoriasPerigosas'
        ];

        if (isset($falMap[$tipoDocumento])) {
            $fal = $pedido->{$falMap[$tipoDocumento]};
            if ($fal) {
                $fal->update(['status' => $status]);
            }
        }
    }
}