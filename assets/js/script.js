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
    carregarVelas: function() {
        // Verifica se podemos usar o cache
        if (velasCache.isCacheValid()) {
            this.renderizarVelas(velasCache.getData());
            return Promise.resolve(velasCache.getData());
        }
        
        // Se não tiver cache, faz a requisição
        return fetch("api/carregar_velas.php")
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
    },
    
    /**
     * Renderiza as velas na tela
     * @param {Array} velas Lista de velas a serem renderizadas
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
    },
    
    /**
     * Cria o elemento HTML para uma vela normal
     * @param {Object} vela Dados da vela
     * @returns {HTMLElement} Elemento da vela
     */
    criarVelaNormal: function(vela) {
        const velaContainer = document.createElement("div");
        velaContainer.classList.add("vela-container");
        
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
        
        // Monta a estrutura da vela
        velaElement.appendChild(chamaElement);
        velaContainer.appendChild(velaElement);
        velaContainer.appendChild(infoVelaElement);
        
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

// Gerenciador de reações
const reacaoManager = {
    /**
     * Inicializa o sistema de reações
     */
    inicializar: function() {
        document.addEventListener("click", (event) => {
            const button = event.target.closest(".reagir-btn");
            if (!button) return;
            
            this.reagirVela(button);
        });
    },
    
    /**
     * Processa a reação a uma vela
     * @param {HTMLElement} button Botão de reação clicado
     */
    reagirVela: function(button) {
        const idVela = button.getAttribute("data-id");
        
        // Obter o token do elemento global
        const csrfToken = document.getElementById('global_csrf_token')?.value || '';
        
        // Obter o elemento contador dentro do botão
        const countElement = button.querySelector(".reacao-count");
        
        if (!countElement) {
            console.error("Elemento do contador não encontrado!");
            return;
        }
        
        // Evita múltiplos cliques
        button.disabled = true;
        
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
            if (data.status === "success") {
                button.classList.add("btn-success");
                countElement.textContent = data.reacoes;
                
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
            }
            
            // Reativa o botão
            button.disabled = false;
        })
        .catch(error => {
            console.error("Erro na requisição:", error);
            this.exibirMensagemErro("Erro ao reagir à vela. Tente novamente.");
            button.disabled = false;
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
                velasManager.carregarVelas();
                
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