/**
 * Velinhas - Javascript principal
 * Versão: 3.1.0
 */

// Verificar se o Service Worker é suportado
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
            .then(registration => {
                console.log('Service Worker registrado com sucesso:', registration.scope);
            })
            .catch(error => {
                console.error('Falha ao registrar o Service Worker:', error);
            });
    });
}

// Cache de velas para reduzir requisições
const velasCache = {
    data: null,
    timestamp: 0,
    cacheValidity: 60000, // 1 minuto em milissegundos
    
    isCacheValid: function() {
        return this.data && (Date.now() - this.timestamp < this.cacheValidity);
    },
    
    updateCache: function(velas) {
        this.data = velas;
        this.timestamp = Date.now();
        
        // Também armazena no localStorage como backup para funcionalidade offline
        try {
            localStorage.setItem('velinhasCacheData', JSON.stringify(velas));
            localStorage.setItem('velinhasCacheTime', this.timestamp);
        } catch (e) {
            console.warn('Erro ao salvar cache no localStorage:', e);
        }
    },
    
    getDataFromStorage: function() {
        try {
            const cacheTime = parseInt(localStorage.getItem('velinhasCacheTime') || '0');
            if (Date.now() - cacheTime < this.cacheValidity * 3) { // Cache offline válido por 3 minutos
                const cachedData = localStorage.getItem('velinhasCacheData');
                if (cachedData) {
                    return JSON.parse(cachedData);
                }
            }
        } catch (e) {
            console.warn('Erro ao recuperar cache do localStorage:', e);
        }
        return null;
    },
    
    getData: function() {
        return this.data || this.getDataFromStorage() || [];
    }
};

// Gerenciador de velas
const velasManager = {
    /**
     * Carrega as velas do servidor ou do cache
     */
    // Modificação para o velasManager.carregarVelas
    carregarVelas: function(forceReload = false) {
        // Se forceReload for true ou se não houver cache válido, faz a requisição
        if (forceReload || !velasCache.isCacheValid()) {
            // Mostra um indicador de carregamento
            const velasContainer = document.getElementById("velas-container");
            if (velasContainer) {
                velasContainer.innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-3">Carregando velas...</p>
                    </div>
                `;
            }
            
            return fetch("api/carregar_velas.php?nocache=" + Date.now())
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erro HTTP: ${response.status}`);
                    }
                    return response.json();
                })
                .then(velas => {
                    // Atualiza o cache e renderiza
                    velasCache.updateCache(velas);
                    this.renderizarVelas(velas);
                    return velas;
                })
                .catch(error => {
                    console.error("Erro ao carregar velas:", error);
                    
                    // Em caso de erro, tenta usar dados do cache local
                    const cachedData = velasCache.getDataFromStorage();
                    if (cachedData) {
                        this.renderizarVelas(cachedData);
                        return cachedData;
                    }
                    
                    return [];
                });
        } else {
            // Usa o cache existente
            this.renderizarVelas(velasCache.getData());
            return Promise.resolve(velasCache.getData());
        }
    },
    
        marcarReacoesAnteriores: function() {
        // Tenta obter reações salvas do cookie
        try {
            // Verifica primeiro o cookie
            const reacaoCookie = this.getCookie('reacao_velas');
            if (reacaoCookie) {
                const reacoes = JSON.parse(reacaoCookie);
                
                // Marca todos os botões de velas que o usuário já reagiu
                document.querySelectorAll('.reagir-btn').forEach(button => {
                    const velaId = button.getAttribute('data-id');
                    if (reacoes.includes(velaId)) {
                        button.classList.add('btn-success');
                        button.disabled = true;
                    }
                });
            }
            
            // Verifica também localStorage como backup
            const localReacoes = localStorage.getItem('velinhas_reacoes');
            if (localReacoes) {
                const reacoes = JSON.parse(localReacoes);
                
                document.querySelectorAll('.reagir-btn').forEach(button => {
                    const velaId = button.getAttribute('data-id');
                    if (reacoes.includes(velaId)) {
                        button.classList.add('btn-success');
                        button.disabled = true;
                    }
                });
            }
        } catch (e) {
            console.error("Erro ao verificar reações anteriores:", e);
        }
    },
    
    /**
     * Obtém um cookie pelo nome
     * 
     * @param {string} name Nome do cookie
     * @return {string|null} Valor do cookie ou null se não existir
     */
    getCookie: function(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    },
    
    /**
     * Modifique a função renderizarVelas para chamar marcarReacoesAnteriores
     */
    renderizarVelas: function(velas) {
        const velasContainer = document.getElementById("velas-container");
        if (!velasContainer) return;
        
        // Usa DocumentFragment para melhor performance
        const fragment = document.createDocumentFragment();
        
        // Inverte a ordem para mostrar as mais recentes primeiro
        velas.reverse().forEach((vela, index) => {
            // A cada 20 velas, insere uma vela publicitária
            if (index > 0 && index % 20 === 0) {
                fragment.appendChild(this.criarVelaPublicitaria(index));
            }
            
            // Cria a vela normal
            fragment.appendChild(this.criarVelaNormal(vela));
        });
        
        // Limpa o container e adiciona todas as velas de uma vez
        velasContainer.innerHTML = "";
        velasContainer.appendChild(fragment);
        
        // Atualiza o contador de velas
        const badgeVelasAcesas = document.getElementById("badgeVelasAcesas");
        if (badgeVelasAcesas) {
            badgeVelasAcesas.textContent = velas.length;
        }
        
        // Marca as velas que o usuário já reagiu
        this.marcarReacoesAnteriores();
    },
    
    /**
     * Cria o elemento HTML para uma vela normal com link para página individual
     * @param {Object} vela Dados da vela
     * @returns {HTMLElement} Elemento da vela
     */
    /**
     * Cria o elemento HTML para uma vela normal com botões de compartilhamento
     * @param {Object} vela Dados da vela
     * @returns {HTMLElement} Elemento da vela
     */
    criarVelaNormal: function(vela) {
        const velaContainer = document.createElement("div");
        velaContainer.classList.add("vela-container");
        
        // Cria um link para a página individual da vela
        const velaLink = document.createElement("a");
        velaLink.href = `/vela/${vela.id}`;
        velaLink.classList.add("vela-link");
        
        const velaElement = document.createElement("div");
        velaElement.classList.add("vela");
        
        // Aplica a personalização da vela
        if (vela.personalizacao) {
            if (vela.personalizacao.startsWith("#")) {
                velaElement.style.backgroundColor = vela.personalizacao;
                const darkerColor = this.shadeColor(vela.personalizacao, -20);
                velaElement.style.border = `3px solid ${darkerColor}`;
            } else {
                velaElement.style.backgroundImage = `url(/assets/img/${vela.personalizacao}.png)`;
                velaElement.style.backgroundSize = "cover";
            }
        }
        
        // Adiciona a chama
        const chamaElement = document.createElement("div");
        chamaElement.classList.add("vela-chama");
        
        // Cria as informações da vela
        const infoVelaElement = document.createElement("div");
        infoVelaElement.classList.add("info-vela");
        
        // Prepara o token CSRF para reações
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        infoVelaElement.innerHTML = `
            <button class="btn btn-sm btn-secondary reagir-btn" data-id="${vela.id}" data-csrf="${csrfToken}">
                🙏<span class="reacao-count">${vela.reacoes || 0}</span>
            </button>
            <p><strong>${this.escapeHtml(vela.nome)}</strong></p>
            <p><small>Acesa em: ${vela.dataAcesa}</small></p>
            <p><small>Apaga em: ${vela.dataExpira}</small></p>
        `;
        
        // Cria os botões de compartilhamento
        const shareButtons = document.createElement("div");
        shareButtons.classList.add("share-buttons");
        
        // URL completa para compartilhamento
        const shareUrl = `${window.location.origin}/vela/${vela.id}`;
        const shareText = `Veja a vela que acendi para ${this.escapeHtml(vela.nome)}`;
        
        // Botão WhatsApp
        const whatsappButton = document.createElement("button");
        whatsappButton.classList.add("share-btn", "whatsapp");
        whatsappButton.innerHTML = '<i class="bi bi-whatsapp"></i>';
        whatsappButton.title = "Compartilhar no WhatsApp";
        whatsappButton.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            window.open(`https://wa.me/?text=${encodeURIComponent(shareText + ': ' + shareUrl)}`, '_blank');
        });
        
        // Botão Facebook
        const facebookButton = document.createElement("button");
        facebookButton.classList.add("share-btn", "facebook");
        facebookButton.innerHTML = '<i class="bi bi-facebook"></i>';
        facebookButton.title = "Compartilhar no Facebook";
        facebookButton.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`, '_blank');
        });
        
        // Botão Copiar Link
        const copyButton = document.createElement("button");
        copyButton.classList.add("share-btn", "copy");
        copyButton.innerHTML = '<i class="bi bi-clipboard"></i>';
        copyButton.title = "Copiar link";
        copyButton.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Copia o link para a área de transferência
            if (navigator.clipboard) {
                navigator.clipboard.writeText(shareUrl)
                    .then(() => {
                        copyButton.classList.add("copied");
                        setTimeout(() => {
                            copyButton.classList.remove("copied");
                        }, 2000);
                    })
                    .catch(err => {
                        console.error("Erro ao copiar link:", err);
                    });
            } else {
                // Fallback para navegadores que não suportam clipboard API
                const tempInput = document.createElement("input");
                document.body.appendChild(tempInput);
                tempInput.value = shareUrl;
                tempInput.select();
                
                try {
                    document.execCommand("copy");
                    copyButton.classList.add("copied");
                    setTimeout(() => {
                        copyButton.classList.remove("copied");
                    }, 2000);
                } catch (err) {
                    console.error("Erro ao copiar link:", err);
                }
                
                document.body.removeChild(tempInput);
            }
        });
        
        // Adiciona os botões ao container de compartilhamento
        shareButtons.appendChild(whatsappButton);
        shareButtons.appendChild(facebookButton);
        shareButtons.appendChild(copyButton);
        
        // Monta a estrutura da vela
        velaElement.appendChild(chamaElement);
        velaLink.appendChild(velaElement);
        velaContainer.appendChild(velaLink);
        velaContainer.appendChild(infoVelaElement);
        velaContainer.appendChild(shareButtons);
        
        return velaContainer;
    },
    
    /**
     * Cria o elemento HTML para uma vela publicitária
     * @param {number} index Índice da vela
     * @returns {HTMLElement} Elemento da vela publicitária
     */
    criarVelaPublicitaria: function(index) {
        // Array de velas publicitárias
        const velasPublicitarias = [
            {
                imagem: "vela_shopee.jpeg",
                titulo: "Loja Shopee!",
                descricao: "Confira nossa loja e aproveite!",
                link: "https://shpe.site/shopeebrasil_achadinhos",
                botaoTexto: "Visitar Loja"
            }
            // Adicione mais opções aqui se necessário
        ];
        
        // Seleciona uma vela publicitária com base no índice
        const velaPublicitaria = velasPublicitarias[index / 20 % velasPublicitarias.length];
        
        const velaPublicitariaElement = document.createElement("div");
        velaPublicitariaElement.classList.add("vela-container");
        
        const linkElement = document.createElement("a");
        linkElement.href = velaPublicitaria.link;
        linkElement.target = "_blank";
        linkElement.classList.add("vela-publicitaria");
        
        const velaPublicitariaDiv = document.createElement("div");
        velaPublicitariaDiv.classList.add("vela");
        velaPublicitariaDiv.style.backgroundImage = `url(/assets/img/${velaPublicitaria.imagem})`;
        velaPublicitariaDiv.style.backgroundSize = "cover";
        
        // Adiciona a chama à vela publicitária
        const chamaElement = document.createElement("div");
        chamaElement.classList.add("vela-chama");
        
        const infoPublicitariaElement = document.createElement("div");
        infoPublicitariaElement.classList.add("info-vela");
        infoPublicitariaElement.innerHTML = `
            <p><strong>${velaPublicitaria.titulo}</strong></p>
            <p><small>${velaPublicitaria.descricao}</small></p>
        `;
        
        const botaoPublicitario = document.createElement("button");
        botaoPublicitario.classList.add("btn", "btn-sm", "btn-secondary");
        botaoPublicitario.textContent = velaPublicitaria.botaoTexto;
        
        // Monta a vela publicitária completa
        velaPublicitariaDiv.appendChild(chamaElement);
        linkElement.appendChild(velaPublicitariaDiv);
        linkElement.appendChild(infoPublicitariaElement);
        linkElement.appendChild(botaoPublicitario);
        
        velaPublicitariaElement.appendChild(linkElement);
        
        return velaPublicitariaElement;
    },
    
    /**
     * Escurece ou clareia uma cor hexadecimal
     * @param {string} color Cor em formato hexadecimal (#RRGGBB)
     * @param {number} percent Percentual de mudança (-100 a 100)
     * @returns {string} Nova cor em formato hexadecimal
     */
    shadeColor: function(color, percent) {
        let R = parseInt(color.substring(1, 3), 16);
        let G = parseInt(color.substring(3, 5), 16);
        let B = parseInt(color.substring(5, 7), 16);

        R = Math.round(R * (100 + percent) / 100);
        G = Math.round(G * (100 + percent) / 100);
        B = Math.round(B * (100 + percent) / 100);

        R = Math.min(R, 255);
        G = Math.min(G, 255);
        B = Math.min(B, 255);

        const RR = (R.toString(16).length === 1) ? `0${R.toString(16)}` : R.toString(16);
        const GG = (G.toString(16).length === 1) ? `0${G.toString(16)}` : G.toString(16);
        const BB = (B.toString(16).length === 1) ? `0${B.toString(16)}` : B.toString(16);

        return `#${RR}${GG}${BB}`;
    },
    
    /**
     * Escapa caracteres HTML para prevenir XSS
     * @param {string} text Texto a ser escapado
     * @returns {string} Texto seguro
     */
    escapeHtml: function(text) {
        if (!text) return '';
        
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return text.replace(/[&<>"']/g, m => map[m]);
    }
};

// Gerenciador de atualização automática
const atualizacaoManager = {
    intervaloAtualizacao: null,
    
    /**
     * Inicializa o sistema de atualização automática
     */
    inicializar: function() {
        const atualizarCheckbox = document.getElementById("atualizarVelas");
        if (!atualizarCheckbox) return;
        
        // Carrega o estado salvo no localStorage
        if (localStorage.getItem("atualizarVelas") === "true") {
            atualizarCheckbox.checked = true;
            this.iniciarAtualizacao();
        }
        
        // Adiciona o evento de alternar atualização
        atualizarCheckbox.addEventListener("change", () => this.alternarAtualizacao());
    },
    
    /**
     * Inicia a atualização automática
     */
    iniciarAtualizacao: function() {
        const atualizarCheckbox = document.getElementById("atualizarVelas");
        if (!atualizarCheckbox || !atualizarCheckbox.checked) return;
        
        localStorage.setItem("atualizarVelas", "true");
        this.pararAtualizacao(); // Limpa qualquer intervalo existente
        this.intervaloAtualizacao = setInterval(() => velasManager.carregarVelas(), 5000);
    },
    
    /**
     * Para a atualização automática
     */
    pararAtualizacao: function() {
        localStorage.setItem("atualizarVelas", "false");
        if (this.intervaloAtualizacao) {
            clearInterval(this.intervaloAtualizacao);
            this.intervaloAtualizacao = null;
        }
    },
    
    /**
     * Alterna o estado da atualização automática
     */
    alternarAtualizacao: function() {
        const atualizarCheckbox = document.getElementById("atualizarVelas");
        if (!atualizarCheckbox) return;
        
        if (atualizarCheckbox.checked) {
            this.iniciarAtualizacao();
        } else {
            this.pararAtualizacao();
        }
    }
};

// Gerenciador de formulário
const formManager = {
    /**
     * Inicializa o gerenciamento do formulário
     */
    inicializar: function() {
        const form = document.getElementById("velaForm");
        if (!form) return;
        
        form.addEventListener("submit", (event) => this.submitForm(event));
        
        // Inicializa o seletor de cor
        this.initColorPicker();
    },
    
    /**
     * Processa o envio do formulário
     * @param {Event} event Evento de submit
     */
    submitForm: function(event) {
        event.preventDefault();
        
        const form = document.getElementById("velaForm");
        const nome = document.getElementById("nome").value;
        const duracao = document.getElementById("duracao").value;
        const corVela = document.getElementById("corVela").value;
        
        // Obter o token diretamente do input oculto no formulário
        const csrfToken = form.querySelector('input[name="csrf_token"]').value;
        
        console.log("CSRF Token:", csrfToken); // Para depuração
        
        // Validação básica no cliente
        if (!nome || nome.length > 40) {
            this.exibirMensagemErro("O nome da vela deve ter entre 1 e 40 caracteres.");
            return;
        }
        
        // Determina a personalização da vela
        let personalizacao = corVela;
        
        if (document.getElementById("imgRadio0").checked) {
            personalizacao = "vela0";
        } else if (document.getElementById("imgRadio1").checked) {
            personalizacao = "vela1";
        } else if (document.getElementById("imgRadio2").checked) {
            personalizacao = "vela2";
        } else if (document.getElementById("imgRadio3").checked) {
            personalizacao = "vela3";
        }
        
        // Adicionando um indicador visual de carregamento
        const submitButton = document.getElementById("acenderBtn");
        const originalText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Acendendo...';
        
        // Envia os dados para o servidor
        fetch("api/salvar_vela.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                nome,
                duracao,
                personalizacao,
                csrf_token: csrfToken
            })
        })
        .then(response => {
            // Verificar se a resposta é válida
            if (!response.ok) {
                throw new Error('Resposta do servidor não foi OK: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            // Restaura o botão
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
            
            // Exibe o alerta retornado pelo PHP
            if (data.alert) {
                const alertContainer = document.getElementById("alert-container");
                if (alertContainer) {
                    alertContainer.innerHTML = data.alert;
                }
            }
            
            if (data.status === "success") {
                // Recarrega as velas para mostrar a nova
                velasManager.carregarVelas(true);
                
                // Reseta o formulário
                form.reset();
                
                // Fecha o modal
                const modal = bootstrap.Modal.getInstance(document.getElementById("velaModal"));
                if (modal) {
                    modal.hide();
                }
                
                // Remove a mensagem após 5 segundos
                setTimeout(() => {
                    const alertContainer = document.getElementById("alert-container");
                    if (alertContainer) {
                        const alerts = alertContainer.querySelectorAll('.alert');
                        alerts.forEach(alert => {
                            alert.classList.remove("show");
                            setTimeout(() => alert.remove(), 300);
                        });
                    }
                }, 5000);
                
                // AQUI: Abre o modal de PIX após um pequeno delay
                setTimeout(() => {
                    // Verifica se há modal de doação
                    const doacaoModal = document.getElementById("doacaoModal");
                    if (doacaoModal) {
                        // Cria mensagem personalizada antes de abrir o modal
                        const mensagemElement = document.getElementById("mensagem-pos-vela");
                        if (mensagemElement) {
                            mensagemElement.textContent = "Obrigado por acender uma velinha! Que tal ajudar o Velinhas a se manter aceso?";
                        }
                        
                        // Abre o modal de doação
                        const modalInstance = new bootstrap.Modal(doacaoModal);
                        modalInstance.show();
                        
                        // Marca que o modal foi aberto após acender vela
                        doacaoModal.setAttribute('data-after-lighting', 'true');
                    }
                }, 1000); // Abre após 1 segundo
            }
        })
        .catch(error => {
            // Restaura o botão
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
            
            console.error("Erro ao salvar vela:", error);
            this.exibirMensagemErro("Erro ao acender vela. Tente novamente.");
        });
    },
    
    /**
     * Inicializa o seletor de cor
     */
    initColorPicker: function() {
        const colorPickerWrapper = document.getElementById("colorPickerWrapper");
        const corRadio = document.getElementById("corRadio");
        
        if (!colorPickerWrapper || !corRadio) return;
        
        // Mostra/esconde o seletor de cor
        corRadio.addEventListener('change', function() {
            if (corRadio.checked) {
                colorPickerWrapper.classList.remove('d-none');
            }
        });
        
        // Esconde o seletor de cor ao selecionar outro tipo
        document.querySelectorAll('input[name="tipoVela"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this !== corRadio) {
                    colorPickerWrapper.classList.add('d-none');
                }
            });
        });
    },
    
    /**
     * Exibe uma mensagem de erro temporária
     * @param {string} mensagem Mensagem a ser exibida
     */
    exibirMensagemErro: function(mensagem) {
        const alertContainer = document.getElementById("alert-container");
        if (!alertContainer) return;
        
        const alertDiv = document.createElement("div");
        alertDiv.className = "alert alert-warning alert-dismissible fade show";
        alertDiv.setAttribute("role", "alert");
        alertDiv.innerHTML = `
            ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        `;
        
        alertContainer.appendChild(alertDiv);
        
        // Remove a mensagem após 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.classList.remove("show");
                setTimeout(() => alertDiv.remove(), 300);
            }
        }, 5000);
    }
};

// Inicialização do sistema quando o DOM estiver carregado
document.addEventListener("DOMContentLoaded", function() {
    // Inicializa todos os gerenciadores
    velasManager.carregarVelas();
    atualizacaoManager.inicializar();
    reacaoManager.inicializar();
    formManager.inicializar();
    
    // Exibe o toast de boas-vindas
    const toastEl = document.getElementById("toastMessage");
    if (toastEl) {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
});


const reacaoManagerOtimizado = {
    /**
     * Inicializa o sistema de reações com controle de inicialização
     * para evitar duplicação de eventos
     */
    inicializado: false,
    
    inicializar: function() {
        // Evita inicialização múltipla
        if (this.inicializado) {
            return;
        }
        
        // Remove todos os event listeners existentes nos botões de reação
        document.querySelectorAll(".reagir-btn").forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
        });
        
        // Adiciona um único event listener no documento para usar event delegation
        document.addEventListener("click", (event) => {
            const button = event.target.closest(".reagir-btn");
            if (!button) return;
            
            // Impede comportamento padrão
            event.preventDefault();
            
            // Verifica se o botão já foi clicado
            if (button.disabled || button.classList.contains("btn-success")) {
                return;
            }
            
            this.reagirVela(button);
        });
        
        // Marca como inicializado
        this.inicializado = true;
        console.log("Sistema de reações inicializado com sucesso!");
    },
    
    /**
     * Processa a reação a uma vela de forma otimizada
     * com proteção contra duplos cliques e requisições duplicadas
     * 
     * @param {HTMLElement} button Botão de reação clicado
     */
    reagirVela: function(button) {
        const idVela = button.getAttribute("data-id");
        
        // Obter o token do elemento global
        const csrfToken = document.getElementById('global_csrf_token')?.value || 
                          document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        // Obter o elemento contador dentro do botão
        const countElement = button.querySelector(".reacao-count");
        
        if (!countElement) {
            console.error("Elemento do contador não encontrado!");
            return;
        }
        
        // Evita múltiplos cliques e requisições duplicadas
        button.disabled = true;
        
        // Feedback visual imediato para o usuário
        button.classList.add("processing");
        
        fetch("api/reagir.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ 
                id: idVela,
                csrf_token: csrfToken
            })
        })
        .then(response => {
            // Verifica se a resposta é válida
            if (!response.ok) {
                throw new Error('Resposta do servidor não foi OK: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            button.classList.remove("processing");
            
            if (data.status === "success") {
                // Atualiza a interface
                button.classList.add("btn-success");
                countElement.textContent = data.reacoes;
                
                // Salva a reação no cookie para lembrar posteriormente
                this.salvarReacaoNoCookie(idVela);
                
                // Atualiza o cache
                if (velasCache.data) {
                    velasCache.data.forEach(vela => {
                        if (vela.id == idVela) {
                            vela.reacoes = data.reacoes;
                        }
                    });
                }
            } else {
                // Exibe mensagem de erro
                this.exibirMensagemErro(data.message);
                
                // Re-habilita o botão se não foi uma reação duplicada
                if (data.message !== "Você já reagiu a esta vela.") {
                    button.disabled = false;
                } else {
                    // Se for reação duplicada, mantém o botão como sucesso
                    button.classList.add("btn-success");
                }
            }
        })
        .catch(error => {
            button.classList.remove("processing");
            console.error("Erro na requisição:", error);
            this.exibirMensagemErro("Erro ao reagir à vela. Tente novamente.");
            
            // Re-habilita o botão após erro
            button.disabled = false;
        });
    },
    
    /**
     * Salva o ID da vela reagida em um cookie para evitar reações duplicadas
     * mesmo sem resposta do servidor
     * 
     * @param {string} idVela ID da vela
     */
    salvarReacaoNoCookie: function(idVela) {
        try {
            let reacoes = [];
            const cookieReacoes = this.getCookie('reacao_velas');
            
            if (cookieReacoes) {
                reacoes = JSON.parse(cookieReacoes);
            }
            
            if (!reacoes.includes(idVela)) {
                reacoes.push(idVela);
            }
            
            // Define o cookie com validade de 1 ano
            const expireDate = new Date();
            expireDate.setFullYear(expireDate.getFullYear() + 1);
            
            document.cookie = `reacao_velas=${JSON.stringify(reacoes)}; expires=${expireDate.toUTCString()}; path=/; SameSite=Lax`;
        } catch (e) {
            console.error("Erro ao salvar reação no cookie:", e);
        }
    },
    
    /**
     * Obtém o valor de um cookie pelo nome
     * 
     * @param {string} name Nome do cookie
     * @return {string|null} Valor do cookie ou null se não existir
     */
    getCookie: function(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    },
    
    /**
     * Verifica se o usuário já reagiu a uma vela específica
     * 
     * @param {string} idVela ID da vela
     * @return {boolean} True se já reagiu, false caso contrário
     */
    jaReagiu: function(idVela) {
        try {
            const cookieReacoes = this.getCookie('reacao_velas');
            if (cookieReacoes) {
                const reacoes = JSON.parse(cookieReacoes);
                return reacoes.includes(idVela);
            }
        } catch (e) {
            console.error("Erro ao verificar reação:", e);
        }
        return false;
    },
    
    /**
     * Inicializa visualmente os botões com base no estado salvo
     * para refletir as reações já feitas pelo usuário
     */
    inicializarEstadoBotoes: function() {
        document.querySelectorAll(".reagir-btn").forEach(button => {
            const idVela = button.getAttribute("data-id");
            if (this.jaReagiu(idVela)) {
                button.classList.add("btn-success");
                button.disabled = true;
            }
        });
    },
    
    /**
     * Exibe uma mensagem de erro temporária
     * 
     * @param {string} mensagem Mensagem a ser exibida
     */
    exibirMensagemErro: function(mensagem) {
        const alertContainer = document.getElementById("alert-container");
        if (!alertContainer) return;
        
        const alertDiv = document.createElement("div");
        alertDiv.className = "alert alert-warning alert-dismissible fade show";
        alertDiv.setAttribute("role", "alert");
        alertDiv.innerHTML = `
            ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        `;
        
        alertContainer.appendChild(alertDiv);
        
        // Remove a mensagem após 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.classList.remove("show");
                setTimeout(() => alertDiv.remove(), 300);
            }
        }, 5000);
    }
};

// Estilo para indicação visual de processamento de reação
document.addEventListener('DOMContentLoaded', function() {
    // Adiciona estilos CSS para o indicador de processamento
    const style = document.createElement('style');
    style.textContent = `
        .reagir-btn.processing {
            position: relative;
            pointer-events: none;
        }
        .reagir-btn.processing::after {
            content: "";
            position: absolute;
            width: 1em;
            height: 1em;
            top: calc(50% - 0.5em);
            right: 10px;
            border: 2px solid rgba(255,255,255,0.5);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s infinite linear;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
});

// Substitui o gerenciador de reações original
document.addEventListener('DOMContentLoaded', function() {
    // Substitui o objeto original pelo otimizado
    reacaoManager = reacaoManagerOtimizado;
    
    // Inicializa o gerenciador otimizado
    reacaoManagerOtimizado.inicializar();
    
    // Configura visualmente os botões com base no estado anterior
    reacaoManagerOtimizado.inicializarEstadoBotoes();
    
    function setupShareButtonsEventPropagation() {
        // Usa delegação de eventos para capturar cliques nos botões de compartilhamento
        document.addEventListener('click', function(event) {
            // Verifica se o clique foi em um botão de compartilhamento
            if (event.target.closest('.share-btn')) {
                // Previne propagação para não acionar o link da vela
                event.preventDefault();
                event.stopPropagation();
            }
        }, true); // Use capture para garantir que este evento seja processado primeiro
    }
    
    // Adiciona a inicialização na carga do documento
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa gerenciadores existentes
        setupShareButtonsEventPropagation();
    });
    
});


/**
 * Sistema simplificado de marcação de reações
 * Adicione este código ao final do seu arquivo script.js
 */

// Sistema de verificação de reações
const reacaoVerificador = {
    // Inicializa o sistema
    init: function() {
        // Verifica reações imediatamente ao carregar
        this.verificarTodasReacoes();
        
        // Configura um observador para detectar novas velas adicionadas ao DOM
        this.observarNovasVelas();
    },
    
    // Verifica todas as reações existentes no DOM
    verificarTodasReacoes: function() {
        // Obtém todas as reações salvas
        const reacoesUsuario = this.obterReacoesUsuario();
        
        // Marca todos os botões correspondentes
        document.querySelectorAll('.reagir-btn').forEach(botao => {
            const idVela = botao.getAttribute('data-id');
            
            // Se o usuário já reagiu a esta vela
            if (reacoesUsuario.includes(idVela)) {
                // Aplica o estilo visual de reagido
                botao.classList.add('btn-success');
                botao.disabled = true;
            }
        });
    },
    
    // Configura um observador para detectar novas velas
    observarNovasVelas: function() {
        // Cria um MutationObserver para monitorar alterações no DOM
        const observer = new MutationObserver((mutations) => {
            let verificarReacoes = false;
            
            // Verifica se alguma mutação adicionou novos nós
            mutations.forEach(mutation => {
                if (mutation.addedNodes.length > 0) {
                    verificarReacoes = true;
                }
            });
            
            // Se houve adição de novos elementos, verifica as reações
            if (verificarReacoes) {
                setTimeout(() => this.verificarTodasReacoes(), 100);
            }
        });
        
        // Configuração do observador para monitorar todo o DOM
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    },
    
    // Obtém as reações do usuário de todas as fontes possíveis
    obterReacoesUsuario: function() {
        const reacoes = new Set();
        
        // Tenta obter do cookie
        try {
            const cookieReacoes = this.getCookie('reacao_velas');
            if (cookieReacoes) {
                JSON.parse(cookieReacoes).forEach(id => reacoes.add(id));
            }
        } catch (e) {
            console.warn('Erro ao ler cookie de reações:', e);
        }
        
        // Tenta obter do localStorage
        try {
            const localReacoes = localStorage.getItem('velinhas_reacoes');
            if (localReacoes) {
                JSON.parse(localReacoes).forEach(id => reacoes.add(id));
            }
        } catch (e) {
            console.warn('Erro ao ler localStorage de reações:', e);
        }
        
        return Array.from(reacoes);
    },
    
    // Helper para obter cookie por nome
    getCookie: function(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? decodeURIComponent(match[2]) : null;
    }
};

// Modifica o reacaoManager para marcar visualmente as reações
// e salvar em ambos localStorage e cookie
const reacaoManagerExtended = {
    // Estende o comportamento padrão de reagirVela
    reagirVelaExtended: function(originalMethod) {
        return function(button) {
            // Obtém o ID da vela
            const idVela = button.getAttribute("data-id");
            
            // Obter o token do elemento global
            const csrfToken = document.getElementById('global_csrf_token')?.value || 
                             document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            // Obter o elemento contador dentro do botão
            const countElement = button.querySelector(".reacao-count");
            
            if (!countElement) {
                console.error("Elemento do contador não encontrado!");
                return;
            }
            
            // Evita múltiplos cliques
            button.disabled = true;
            
            // Adiciona indicador visual de loading
            button.classList.add("processando");
            
            fetch("api/reagir.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ 
                    id: idVela,
                    csrf_token: csrfToken
                })
            })
            .then(response => response.json())
            .then(data => {
                // Remove indicador de loading
                button.classList.remove("processando");
                
                if (data.status === "success") {
                    // Marca como reagido visualmente
                    button.classList.add("btn-success");
                    countElement.textContent = data.reacoes;
                    
                    // Salva no localStorage
                    this.salvarReacaoLocal(idVela);
                    
                    // Atualiza o cache
                    if (window.velasCache && window.velasCache.data) {
                        window.velasCache.data.forEach(vela => {
                            if (vela.id == idVela) {
                                vela.reacoes = data.reacoes;
                            }
                        });
                    }
                } else {
                    // Exibe mensagem de erro
                    if (typeof this.exibirMensagemErro === 'function') {
                        this.exibirMensagemErro(data.message);
                    } else {
                        console.error(data.message);
                    }
                    
                    // Se a mensagem for de "já reagiu", ainda assim marca o botão
                    if (data.message === "Você já reagiu a esta vela.") {
                        button.classList.add("btn-success");
                        this.salvarReacaoLocal(idVela);
                    } else {
                        // Reativa o botão apenas para outros tipos de erro
                        button.disabled = false;
                    }
                }
            })
            .catch(error => {
                // Remove indicador de loading
                button.classList.remove("processando");
                
                console.error("Erro na requisição:", error);
                if (typeof this.exibirMensagemErro === 'function') {
                    this.exibirMensagemErro("Erro ao reagir à vela. Tente novamente.");
                }
                button.disabled = false;
            });
        };
    },
    
    // Salva a reação em armazenamento local
    salvarReacaoLocal: function(idVela) {
        // Salva no localStorage
        try {
            let reacoes = [];
            const localReacoes = localStorage.getItem('velinhas_reacoes');
            
            if (localReacoes) {
                reacoes = JSON.parse(localReacoes);
            }
            
            if (!reacoes.includes(idVela)) {
                reacoes.push(idVela);
                localStorage.setItem('velinhas_reacoes', JSON.stringify(reacoes));
            }
        } catch (e) {
            console.warn('Erro ao salvar reação no localStorage:', e);
        }
        
        // Atualiza o cookie também
        try {
            let cookieReacoes = [];
            const existingCookie = this.getCookie('reacao_velas');
            
            if (existingCookie) {
                cookieReacoes = JSON.parse(existingCookie);
            }
            
            if (!cookieReacoes.includes(idVela)) {
                cookieReacoes.push(idVela);
                
                // Define o cookie com validade de 1 ano
                const expiryDate = new Date();
                expiryDate.setFullYear(expiryDate.getFullYear() + 1);
                
                document.cookie = `reacao_velas=${JSON.stringify(cookieReacoes)}; expires=${expiryDate.toUTCString()}; path=/; SameSite=Lax`;
            }
        } catch (e) {
            console.warn('Erro ao salvar reação no cookie:', e);
        }
    },
    
    // Helper para obter cookie por nome
    getCookie: function(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? decodeURIComponent(match[2]) : null;
    }
};

// Adicione estilo CSS para indicador de carregamento
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar estilos CSS para o estado de processamento
    const styleElement = document.createElement('style');
    styleElement.textContent = `
        .reagir-btn.processando {
            position: relative;
            pointer-events: none;
        }
        
        .reagir-btn.processando::after {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            animation: spin 1s infinite linear;
            top: calc(50% - 5px);
            right: 5px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Garante que o botão reagido tenha sempre a mesma aparência */
        .reagir-btn.btn-success {
            background-color: #28a745 !important;
            color: white !important;
            border-color: #28a745 !important;
        }
    `;
    document.head.appendChild(styleElement);
    
    // Estender o reacaoManager existente
    if (window.reacaoManager && typeof window.reacaoManager.reagirVela === 'function') {
        // Guarda a referência original
        const originalReagirVela = window.reacaoManager.reagirVela;
        
        // Substitui pelo método estendido
        window.reacaoManager.reagirVela = reacaoManagerExtended.reagirVelaExtended(originalReagirVela).bind(window.reacaoManager);
        
        // Adiciona o método de salvar reação
        window.reacaoManager.salvarReacaoLocal = reacaoManagerExtended.salvarReacaoLocal.bind(window.reacaoManager);
        window.reacaoManager.getCookie = reacaoManagerExtended.getCookie.bind(window.reacaoManager);
    }
    
    // Inicializa o verificador de reações
    reacaoVerificador.init();
});