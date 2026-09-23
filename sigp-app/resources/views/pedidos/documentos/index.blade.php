@extends('layouts.app')

@section('title', 'Documentos do Pedido')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Documentos do Pedido</h1>
                    <p class="text-muted mb-0">
                        Pedido: <strong>{{ $pedido->numero_pedido }}</strong> - 
                        Embarcação: <strong>{{ $pedido->embarcacao->nome ?? 'N/A' }}</strong>
                    </p>
                </div>
                <div>
                    <a href="{{ route('pedidos.show', $pedido) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar ao Pedido
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Progresso dos FALs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Progresso dos FALs
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $fals = [
                                'fal1DeclaracaoGeral' => ['nome' => 'FAL 1 - Declaração Geral', 'tipo' => 'fal1_declaracao_geral'],
                                'fal2DeclaracaoCarga' => ['nome' => 'FAL 2 - Declaração de Carga', 'tipo' => 'fal2_declaracao_carga'],
                                'fal3ProvisoesBordo' => ['nome' => 'FAL 3 - Provisões de Bordo', 'tipo' => 'fal3_provisoes_bordo'],
                                'fal4PertencessTripulacao' => ['nome' => 'FAL 4 - Pertences da Tripulação', 'tipo' => 'fal4_pertences_tripulacao'],
                                'fal5ListaTripulantes' => ['nome' => 'FAL 5 - Lista de Tripulantes', 'tipo' => 'fal5_documentos_tripulantes'],
                                'fal6ListaPassageiros' => ['nome' => 'FAL 6 - Lista de Passageiros', 'tipo' => 'fal6_documentos_passageiros'],
                                'fal7MercadoriasPerigosas' => ['nome' => 'FAL 7 - Mercadorias Perigosas', 'tipo' => 'fal7_mercadorias_perigosas']
                            ];
                        @endphp

                        @foreach($fals as $relacao => $info)
                            @php
                                $fal = $pedido->$relacao;
                                $status = $fal ? $fal->status : 'pendente';
                                $documentosCount = $documentos[$info['tipo']]->count() ?? 0;
                                
                                $badgeClass = match($status) {
                                    'aprovado' => 'bg-success',
                                    'rejeitado' => 'bg-danger',
                                    'submetido' => 'bg-warning',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border-left-{{ $status === 'aprovado' ? 'success' : ($status === 'rejeitado' ? 'danger' : 'warning') }}">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $info['nome'] }}</h6>
                                                <small class="text-muted">{{ $documentosCount }} documento(s)</small>
                                            </div>
                                            <span class="badge {{ $badgeClass }}">
                                                {{ ucfirst($status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload de Documentos -->
    @if(auth()->user()->can('update', $pedido))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cloud-upload-alt"></i> Upload de Documentos
                    </h5>
                </div>
                <div class="card-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tipo_documento" class="form-label">Tipo de Documento *</label>
                                <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                    <option value="">Selecione o tipo...</option>
                                    @foreach($tiposDocumentos as $valor => $nome)
                                        <option value="{{ $valor }}">{{ $nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-8 mb-3">
                                <label for="arquivos" class="form-label">Arquivos *</label>
                                <input type="file" class="form-control" id="arquivos" name="arquivos[]" 
                                       multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx" required>
                                <div class="form-text">
                                    Formatos aceitos: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX. Máximo 10MB por arquivo.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="descricao" class="form-label">Descrição (opcional)</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="2" 
                                          placeholder="Descrição adicional sobre os documentos..."></textarea>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" id="btnUpload">
                                    <i class="fas fa-upload"></i> Fazer Upload
                                </button>
                                <div class="spinner-border spinner-border-sm ms-2 d-none" id="uploadSpinner"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Lista de Documentos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt"></i> Documentos Enviados
                    </h5>
                </div>
                <div class="card-body">
                    @if($documentos->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhum documento foi enviado ainda.</p>
                        </div>
                    @else
                        @foreach($tiposDocumentos as $tipo => $nomeCompleto)
                            @if(isset($documentos[$tipo]) && $documentos[$tipo]->count() > 0)
                                <div class="mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">{{ $nomeCompleto }}</h6>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Arquivo</th>
                                                    <th>Tamanho</th>
                                                    <th>Enviado por</th>
                                                    <th>Data</th>
                                                    <th>Status</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($documentos[$tipo] as $documento)
                                                    <tr>
                                                        <td>
                                                            <i class="fas fa-file-{{ $documento->extensao === 'pdf' ? 'pdf' : 'alt' }} me-1"></i>
                                                            {{ $documento->nome_original }}
                                                            @if($documento->descricao)
                                                                <br><small class="text-muted">{{ $documento->descricao }}</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ number_format($documento->tamanho / 1024, 1) }} KB</td>
                                                        <td>{{ $documento->uploadedBy->name ?? 'N/A' }}</td>
                                                        <td>{{ $documento->uploaded_at->format('d/m/Y H:i') }}</td>
                                                        <td>
                                                            @php
                                                                $badgeClass = match($documento->status) {
                                                                    'aprovado' => 'bg-success',
                                                                    'rejeitado' => 'bg-danger',
                                                                    default => 'bg-warning'
                                                                };
                                                            @endphp
                                                            <span class="badge {{ $badgeClass }}">
                                                                {{ ucfirst($documento->status) }}
                                                            </span>
                                                            
                                                            @if($documento->status === 'aprovado' && $documento->aprovadoPor)
                                                                <br><small class="text-muted">
                                                                    por {{ $documento->aprovadoPor->name }}
                                                                </small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <!-- Download -->
                                                                <a href="{{ route('documentos.download', $documento) }}" 
                                                                   class="btn btn-outline-primary btn-sm" title="Download">
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                                
                                                                <!-- Visualizar (apenas para PDFs e imagens) -->
                                                                @if(in_array(strtolower($documento->extensao), ['pdf', 'jpg', 'jpeg', 'png']))
                                                                    <a href="{{ route('documentos.view', $documento) }}" 
                                                                       class="btn btn-outline-info btn-sm" title="Visualizar" target="_blank">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                @endif
                                                                
                                                                <!-- Aprovar/Rejeitar (apenas para admin/operador) -->
                                                                @if(auth()->user()->hasAnyRole(['admin', 'operador']) && $documento->status === 'pendente')
                                                                    <button class="btn btn-outline-success btn-sm" 
                                                                            onclick="aprovarDocumento({{ $documento->id }})" title="Aprovar">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                    <button class="btn btn-outline-danger btn-sm" 
                                                                            onclick="rejeitarDocumento({{ $documento->id }})" title="Rejeitar">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                @endif
                                                                
                                                                <!-- Excluir (apenas para quem enviou ou admin) -->
                                                                @if(auth()->user()->can('update', $pedido) && ($documento->uploaded_by === auth()->id() || auth()->user()->hasRole('admin')))
                                                                    <button class="btn btn-outline-danger btn-sm" 
                                                                            onclick="excluirDocumento({{ $documento->id }})" title="Excluir">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para rejeição -->
<div class="modal fade" id="modalRejeicao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejeitar Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRejeicao">
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo da rejeição *</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" required
                                  placeholder="Descreva o motivo da rejeição..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarRejeicao()">Rejeitar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let documentoParaRejeitar = null;

// Upload de documentos
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const btnUpload = document.getElementById('btnUpload');
    const spinner = document.getElementById('uploadSpinner');
    
    // Mostrar loading
    btnUpload.disabled = true;
    spinner.classList.remove('d-none');
    
    fetch('{{ route("pedidos.documentos.upload", $pedido) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar sucesso
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: data.message,
                timer: 3000,
                showConfirmButton: false
            }).then(() => {
                // Recarregar página
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Erro no upload');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: error.message || 'Erro ao fazer upload dos documentos'
        });
    })
    .finally(() => {
        // Esconder loading
        btnUpload.disabled = false;
        spinner.classList.add('d-none');
    });
});

// Aprovar documento
function aprovarDocumento(documentoId) {
    Swal.fire({
        title: 'Aprovar Documento',
        text: 'Tem certeza que deseja aprovar este documento?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, aprovar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/documentos/${documentoId}/aprovar`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Aprovado!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: error.message || 'Erro ao aprovar documento'
                });
            });
        }
    });
}

// Rejeitar documento
function rejeitarDocumento(documentoId) {
    documentoParaRejeitar = documentoId;
    document.getElementById('motivo').value = '';
    new bootstrap.Modal(document.getElementById('modalRejeicao')).show();
}

function confirmarRejeicao() {
    const motivo = document.getElementById('motivo').value.trim();
    
    if (!motivo) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção!',
            text: 'Por favor, informe o motivo da rejeição.'
        });
        return;
    }
    
    fetch(`/documentos/${documentoParaRejeitar}/rejeitar`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ motivo: motivo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalRejeicao')).hide();
            Swal.fire({
                icon: 'success',
                title: 'Rejeitado!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: error.message || 'Erro ao rejeitar documento'
        });
    });
}

// Excluir documento
function excluirDocumento(documentoId) {
    Swal.fire({
        title: 'Excluir Documento',
        text: 'Tem certeza que deseja excluir este documento? Esta ação não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/documentos/${documentoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Excluído!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: error.message || 'Erro ao excluir documento'
                });
            });
        }
    });
}
</script>
@endpush