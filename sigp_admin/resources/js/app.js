import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Fix para compatibilidade com onclick handlers
document.addEventListener('DOMContentLoaded', function() {
    // Aguarda o Alpine.js inicializar
    Alpine.start();
    
    // Após Alpine inicializar, processa onclick handlers
    setTimeout(function() {
        document.querySelectorAll('[onclick]').forEach(function(element) {
            const onclickCode = element.getAttribute('onclick');
            if (onclickCode && !element.hasAttribute('data-onclick-processed')) {
                // Marca como processado para evitar duplicação
                element.setAttribute('data-onclick-processed', 'true');
                
                // Remove o onclick original
                element.removeAttribute('onclick');
                
                // Adiciona event listener que funciona com Alpine
                element.addEventListener('click', function(event) {
                    try {
                        // Executa o código onclick no contexto correto
                        const func = new Function('event', onclickCode);
                        func.call(this, event);
                    } catch (error) {
                        console.error('Erro ao executar onclick:', error, onclickCode);
                    }
                });
            }
        });
        
        // Observer para elementos adicionados dinamicamente
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        const elementsWithOnclick = node.querySelectorAll ? 
                            node.querySelectorAll('[onclick]') : [];
                        
                        elementsWithOnclick.forEach(function(element) {
                            const onclickCode = element.getAttribute('onclick');
                            if (onclickCode && !element.hasAttribute('data-onclick-processed')) {
                                element.setAttribute('data-onclick-processed', 'true');
                                element.removeAttribute('onclick');
                                
                                element.addEventListener('click', function(event) {
                                    try {
                                        const func = new Function('event', onclickCode);
                                        func.call(this, event);
                                    } catch (error) {
                                        console.error('Erro ao executar onclick:', error);
                                    }
                                });
                            }
                        });
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
    }, 150); // Delay para garantir que Alpine terminou de processar
});
