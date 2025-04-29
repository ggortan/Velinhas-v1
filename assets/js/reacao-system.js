/**
 * Sistema simplificado de reações para Velinhas
 * Substitui completamente o sistema anterior
 */

// Sistema de reações simplificado
const sistemaReacao = {
    // Estado de inicialização
    inicializado: false,
    
    // Inicializa o sistema
    inicializar: function() {
        // Previne inicialização múltipla
        if (this.inicializado) return;
        
        console.log("Inicializando sistema de reações...");
        
        // Adiciona evento único de clique usando delegação de eventos
        document.addEventListener("click", this.handleClick.bind(this));
        
        // Marca botões que já foram reagidos pelo usuário
        this.marcarReacoesAnteriores();
        
        this.inicializado = true;
    },
    
    // Manipulador de eventos de clique
    handleClick: function(event) {
        // Encontra o botão mais próximo se o clique foi dentro dele
        const botao = event.target.closest('.reagir-btn');
        if (!botao) return;
        
        // Previne o comportamento padrão
        event.preventDefault();
        
        // Ignora se o botão estiver desabilitado ou já tiver sido clicado
        if (botao.disabled || botao.classList.contains('btn-success')) {
            return;
        }
        
        // Processa a reação
        this.reagir(botao);
    },
    
    // Processa a reação
    reagir: function(botao) {
        // Extrai dados do botão
        const idVela = botao.getAttribute('data-id');
        if (!idVela) {
            console.error("Botão de reação sem ID da vela");
            return;
        }
        
        // Encontra o contador de reações
        const contador = botao.querySelector('.reacao-count');
        if (!contador) {
            console.error("Contador de reações não encontrado");
            return;
        }
        
        // Obtém o token CSRF
        const csrfToken = document.getElementById('global_csrf_token')?.value || 
                         document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        // Desabilita o botão durante o processo
        botao.disabled = true;
        botao.classList.add('processando');
        
        // Faz a requisição AJAX
        fetch('/api/reagir.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: idVela,
                csrf_token: csrfToken
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Falha na requisição: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            // Remove indicador de processamento
            botao.classList.remove('processando');
            
            if (data.status === 'success') {
                // Atualiza o contador
                contador.textContent = data.reacoes;
                
                // Marca como reagido
                botao.classList.add('btn-success');
                
                // Salva no cache local
                this.registrarReacaoLocal(idVela);
                
                // Atualiza o cache da aplicação se disponível
                if (typeof velasCache !== 'undefined' && velasCache.data) {
                    velasCache.data.forEach(vela => {
                        if (vela.id == idVela) {
                            vela.reacoes = data.reacoes;
                        }
                    });
                }
            } else {
                // Em caso de erro de "já reagiu", marca como sucesso mesmo assim
                if (data.message === "Você já reagiu a esta vela.") {
                    botao.classList.add('btn-success');
                    this.registrarReacaoLocal(idVela);
                } else {
                    // Outros erros - reativa o botão
                    botao.disabled = false;
                    this.mostrarMensagem(data.message || "Erro ao processar reação", "warning");
                }
            }
        })
        .catch(erro => {
            console.error("Erro na reação:", erro);
            botao.classList.remove('processando');
            botao.disabled = false;
            this.mostrarMensagem("Erro ao processar sua reação. Tente novamente mais tarde.", "danger");
        });
    },
    
    // Registra a reação localmente para evitar duplicação
    registrarReacaoLocal: function(idVela) {
        try {
            // Obtém reações existentes
            let reacoes = [];
            const reacoesSalvas = localStorage.getItem('velinhas_reacoes');
            
            if (reacoesSalvas) {
                reacoes = JSON.parse(reacoesSalvas);
            }
            
            // Adiciona a nova reação se não existir
            if (!reacoes.includes(idVela)) {
                reacoes.push(idVela);
                localStorage.setItem('velinhas_reacoes', JSON.stringify(reacoes));
            }
        } catch (erro) {
            console.error("Erro ao salvar reação local:", erro);
        }
    },
    
    // Marca botões de reações anteriores
    marcarReacoesAnteriores: function() {
        try {
            const reacoesSalvas = localStorage.getItem('velinhas_reacoes');
            if (!reacoesSalvas) return;
            
            const reacoes = JSON.parse(reacoesSalvas);
            
            // Marca todos os botões correspondentes
            reacoes.forEach(idVela => {
                document.querySelectorAll(`.reagir-btn[data-id="${idVela}"]`).forEach(botao => {
                    botao.classList.add('btn-success');
                    botao.disabled = true;
                });
            });
        } catch (erro) {
            console.error("Erro ao marcar reações anteriores:", erro);
        }
    },
    
    // Exibe uma mensagem para o usuário
    mostrarMensagem: function(texto, tipo = "info") {
        const container = document.getElementById('alert-container');
        if (!container) return;
        
        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
        alerta.setAttribute('role', 'alert');
        
        alerta.innerHTML = `
            ${texto}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        `;
        
        container.appendChild(alerta);
        
        // Remove a mensagem após alguns segundos
        setTimeout(() => {
            alerta.classList.remove('show');
            setTimeout(() => alerta.remove(), 300);
        }, 5000);
    }
};

// Adiciona estilos CSS para o indicador de processamento
document.addEventListener('DOMContentLoaded', function() {
    // Cria elemento de estilo
    const estilos = document.createElement('style');
    estilos.textContent = `
        .reagir-btn.processando {
            position: relative;
            pointer-events: none;
        }
        
        .reagir-btn.processando::after {
            content: '';
            position: absolute;
            width: 1em;
            height: 1em;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: girar 1s linear infinite;
            right: 10px;
            top: calc(50% - 0.5em);
        }
        
        @keyframes girar {
            to { transform: rotate(360deg); }
        }
    `;
    
    // Adiciona os estilos ao documento
    document.head.appendChild(estilos);
    
    // Inicializa o sistema quando o DOM estiver pronto
    sistemaReacao.inicializar();
});

// Substitui o sistema de reações antigo pelo novo
window.reacaoManager = sistemaReacao;