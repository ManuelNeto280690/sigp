class DocumentosManager {
    constructor() {
        this.pedidoId = window.location.pathname.split('/')[2];
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadDocuments();
    }

    setupEventListeners() {
        // Upload de arquivos
        document.querySelectorAll('.select-files-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const form = e.target.closest('.upload-form');
                const fileInput = form.querySelector('.file-input');
                fileInput.click();
            });
        });

        // Mudança nos arquivos selecionados
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', (e) => {
                this.handleFileSelection(e);
            });
        });

        // Submit dos formulários de upload
        document.querySelectorAll('.upload-form').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.uploadFiles(form);
            });
        });

        // Drag and drop
        document.querySelectorAll('.upload-area .card').forEach(card => {
            card.addEventListener('dragover', (e) => {
                e.preventDefault();
                card.classList.add('border-primary');
            });

            card.addEventListener('dragleave', (e) => {
                e.preventDefault();
                card.classList.remove('border-primary');
            });

            card.addEventListener('drop', (e) => {
                e.preventDefault();
                card.classList.remove('border-primary');
                
                const form = card.closest('.fal-section').querySelector('.upload-form');
                const fileInput = form.querySelector('.file-input');
                fileInput.files = e.dataTransfer.files;
                
                this.handleFileSelection({ target: fileInput });
            });
        });
    }

    handleFileSelection(e) {
        const files = Array.from(e.target.files);
        const form = e.target.closest('.upload-form');
        const section = form.closest('.fal-section');
        const selectedFilesDiv = section.querySelector('.selected-files');
        const filesList = section.querySelector('.files-list');
        const uploadBtn = form.querySelector('.upload-btn');

        if (files.length === 0) {
            selectedFilesDiv.classList.add('d-none');
            uploadBtn.classList.add('d-none');
            return;
        }

        // Validar arquivos
        const validFiles = this.validateFiles(files);
        if (validFiles.length === 0) {
            return;
        }

        // Mostrar arquivos selecionados
        filesList.innerHTML = '';
        validFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item d-flex justify-content-between align-items-center p-2 border rounded mb-2';
            fileItem.innerHTML = `
                <div>
                    <i class="fas fa-file me-2"></i>
                    <span>${file.name}</span>
                    <small class="text-muted ms-2">(${this.formatFileSize(file.size)})</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-file" data-index="${index}">
                    <i class="fas fa-times"></i>
                </button>
            `;
            filesList.appendChild(fileItem);
        });

        // Event listeners para remover arquivos
        filesList.querySelectorAll('.remove-file').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.closest('.remove-file').dataset.index);
                this.removeFile(form, index);
            });
        });

        selectedFilesDiv.classList.remove('d-none');
        uploadBtn.classList.remove('d-none');
    }

    validateFiles(files) {
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        const maxSize = 10 * 1024 * 1024; // 10MB
        const maxFiles = 10;

        if (files.length > maxFiles) {
            this.showAlert('error', `Máximo de ${maxFiles} arquivos permitidos`);
            return [];
        }

        const validFiles = [];
        for (const file of files) {
            if (!allowedTypes.includes(file.type)) {
                this.showAlert('error', `Tipo de arquivo não permitido: ${file.name}`);
                continue;
            }
            if (file.size > maxSize) {
                this.showAlert('error', `Arquivo muito grande: ${file.name} (máximo 10MB)`);
                continue;
            }
            validFiles.push(file);
        }

        return validFiles;
    }

    removeFile(form, index) {
        const fileInput = form.querySelector('.file-input');
        const dt = new DataTransfer();
        
        Array.from(fileInput.files).forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        
        fileInput.files = dt.files;
        this.handleFileSelection({ target: fileInput });
    }

    async uploadFiles(form) {
        const formData = new FormData(form);
        const tipoDocumento = form.dataset.tipo;
        const uploadBtn = form.querySelector('.upload-btn');
        const originalText = uploadBtn.innerHTML;

        try {
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Enviando...';

            const response = await fetch(`/pedidos/${this.pedidoId}/documentos/upload`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('success', data.message);
                this.resetForm(form);
                this.loadDocumentsForType(tipoDocumento);
                this.updateProgress(data.progresso_fals);
            } else {
                this.showAlert('error', data.message || 'Erro ao enviar documentos');
            }

        } catch (error) {
            console.error('Erro no upload:', error);
            this.showAlert('error', 'Erro interno do servidor');
        } finally {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = originalText;
        }
    }

    resetForm(form) {
        form.reset();
        const section = form.closest('.fal-section');
        section.querySelector('.selected-files').classList.add('d-none');
        form.querySelector('.upload-btn').classList.add('d-none');
    }

    async loadDocuments() {
        try {
            const response = await fetch(`/pedidos/${this.pedidoId}/documentos`);
            const data = await response.json();

            if (data.success) {
                Object.keys(data.documentos).forEach(tipo => {
                    this.renderDocuments(tipo, data.documentos[tipo]);
                });
            }
        } catch (error) {
            console.error('Erro ao carregar documentos:', error);
        }
    }

    async loadDocumentsForType(tipo) {
        try {
            const response = await fetch(`/pedidos/${this.pedidoId}/documentos`);
            const data = await response.json();

            if (data.success && data.documentos[tipo]) {
                this.renderDocuments(tipo, data.documentos[tipo]);
            }
        } catch (error) {
            console.error('Erro ao carregar documentos:', error);
        }
    }

    renderDocuments(tipo, documentos) {
        const section = document.querySelector(`[data-tipo="${tipo}"]`);
        if (!section) return;

        const documentsList = section.querySelector('.documents-list');
        const tabId = tipo.replace('_', '').replace('fal', 'fal');
        const countBadge = document.querySelector(`#${tabId}-count`);

        if (documentos.length === 0) {
            documentsList.innerHTML = `
                <div class="text-center text-muted py-3">
                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                    <p>Nenhum documento enviado ainda</p>
                </div>
            `;
            if (countBadge) countBadge.textContent = '0';
            return;
        }

        let html = '';
        documentos.forEach(doc => {
            const statusClass = doc.status === 'aprovado' ? 'success' : 
                              doc.status === 'rejeitado' ? 'danger' : 'warning';
            
            html += `
                <div class="document-item border rounded p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <i class="fas fa-file me-2"></i>
                                ${doc.nome_original}
                            </h6>
                            <div class="small text-muted">
                                <span>Tamanho: ${this.formatFileSize(doc.tamanho)}</span> |
                                <span>Enviado em: ${doc.uploaded_at}</span> |
                                <span>Por: ${doc.uploaded_by.name}</span>
                            </div>
                            ${doc.descricao ? `<p class="small mt-1 mb-0">${doc.descricao}</p>` : ''}
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-${statusClass} me-2">${doc.status}</span>
                            <div class="btn-group">
                                <a href="/pedidos/${this.pedidoId}/documentos/${doc.id}/download" 
                                   class="btn btn-sm btn-outline-primary" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                ${this.canManageDocument(doc) ? `
                                    <button class="btn btn-sm btn-outline-success aprovar-doc" 
                                            data-id="${doc.id}" data-nome="${doc.nome_original}" title="Aprovar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger rejeitar-doc" 
                                            data-id="${doc.id}" data-nome="${doc.nome_original}" title="Rejeitar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                ` : ''}
                                ${this.canDeleteDocument(doc) ? `
                                    <button class="btn btn-sm btn-outline-danger excluir-doc" 
                                            data-id="${doc.id}" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                    ${doc.observacoes_aprovacao ? `
                        <div class="mt-2 p-2 bg-light rounded">
                            <small><strong>Observações:</strong> ${doc.observacoes_aprovacao}</small>
                        </div>
                    ` : ''}
                </div>
            `;
        });

        documentsList.innerHTML = html;
        if (countBadge) countBadge.textContent = documentos.length;

        // Event listeners para ações dos documentos
        this.setupDocumentActions(documentsList);
    }

    setupDocumentActions(container) {
        // Aprovar documento
        container.querySelectorAll('.aprovar-doc').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('.aprovar-doc').dataset.id;
                const nome = e.target.closest('.aprovar-doc').dataset.nome;
                this.showAprovarModal(id, nome);
            });
        });

        // Rejeitar documento
        container.querySelectorAll('.rejeitar-doc').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('.rejeitar-doc').dataset.id;
                const nome = e.target.closest('.rejeitar-doc').dataset.nome;
                this.showRejeitarModal(id, nome);
            });
        });

        // Excluir documento
        container.querySelectorAll('.excluir-doc').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('.excluir-doc').dataset.id;
                this.excluirDocumento(id);
            });
        });
    }

    showAprovarModal(id, nome) {
        document.getElementById('nomeDocumentoAprovar').textContent = nome;
        document.getElementById('formAprovarDocumento').dataset.documentoId = id;
        new bootstrap.Modal(document.getElementById('modalAprovarDocumento')).show();
    }

    showRejeitarModal(id, nome) {
        document.getElementById('nomeDocumentoRejeitar').textContent = nome;
        document.getElementById('formRejeitarDocumento').dataset.documentoId = id;
        new bootstrap.Modal(document.getElementById('modalRejeitarDocumento')).show();
    }

    async excluirDocumento(id) {
        if (!confirm('Tem certeza que deseja excluir este documento?')) {
            return;
        }

        try {
            const response = await fetch(`/pedidos/${this.pedidoId}/documentos/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('success', data.message);
                this.loadDocuments();
                this.updateProgress(data.progresso_fals);
            } else {
                this.showAlert('error', data.message || 'Erro ao excluir documento');
            }
        } catch (error) {
            console.error('Erro ao excluir documento:', error);
            this.showAlert('error', 'Erro interno do servidor');
        }
    }

    canManageDocument(doc) {
        // Verificar se o usuário pode aprovar/rejeitar documentos
        // Esta lógica deve ser ajustada conforme suas regras de negócio
        return window.userRole && ['admin', 'operador'].includes(window.userRole);
    }

    canDeleteDocument(doc) {
        // Verificar se o usuário pode excluir documentos
        return doc.status !== 'aprovado';
    }

    updateProgress(progresso) {
        const progressBar = document.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = `${progresso}%`;
            progressBar.textContent = `${progresso}%`;
            progressBar.setAttribute('aria-valuenow', progresso);
        }
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    showAlert(type, message) {
        // Implementar sistema de alertas (pode usar Bootstrap alerts, SweetAlert, etc.)
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Adicionar o alerta no topo da página
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Remover automaticamente após 5 segundos
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    new DocumentosManager();

    // Event listeners para os modais
    document.getElementById('formAprovarDocumento').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const documentoId = form.dataset.documentoId;
        const formData = new FormData(form);

        try {
            const response = await fetch(`/pedidos/${window.location.pathname.split('/')[2]}/documentos/${documentoId}/aprovar`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalAprovarDocumento')).hide();
                new DocumentosManager().showAlert('success', data.message);
                new DocumentosManager().loadDocuments();
                new DocumentosManager().updateProgress(data.progresso_fals);
            } else {
                new DocumentosManager().showAlert('error', data.message || 'Erro ao aprovar documento');
            }
        } catch (error) {
            console.error('Erro ao aprovar documento:', error);
            new DocumentosManager().showAlert('error', 'Erro interno do servidor');
        }
    });

    document.getElementById('formRejeitarDocumento').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const documentoId = form.dataset.documentoId;
        const formData = new FormData(form);

        try {
            const response = await fetch(`/pedidos/${window.location.pathname.split('/')[2]}/documentos/${documentoId}/rejeitar`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalRejeitarDocumento')).hide();
                new DocumentosManager().showAlert('success', data.message);
                new DocumentosManager().loadDocuments();
                new DocumentosManager().updateProgress(data.progresso_fals);
            } else {
                new DocumentosManager().showAlert('error', data.message || 'Erro ao rejeitar documento');
            }
        } catch (error) {
            console.error('Erro ao rejeitar documento:', error);
            new DocumentosManager().showAlert('error', 'Erro interno do servidor');
        }
    });
});