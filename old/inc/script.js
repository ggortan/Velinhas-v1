document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("velaForm");
    const velasContainer = document.getElementById("velas-container");
    const avisoAcenderElement = document.getElementById("aviso-acender-vela"); // Onde a mensagem será exibida
                
        function carregarVelas() {
            fetch("carregar_velas.php")
                .then(response => response.json())
                .then(velas => {
                    velasContainer.innerHTML = "";
                    velas.reverse();
        
                    // Array de velas publicitárias
                    const velasPublicitarias = [
                        {
                            imagem: "vela_shopee.jpeg",
                            titulo: "Loja Shopee!",
                            descricao: "Confira nossa loja e aproveite!",
                            link: "https://shpe.site/shopeebrasil_achadinhos",
                            botaoTexto: "Visitar Loja"
                        }
                    ];
        
                    // Calcular a quantidade restante de velas
                    const quantidadeVelas = velas.length;
                    //const velasRestantes = 1000 - velas.length;
        
                    // Exibir a mensagem com o número de velas restantes
                    //if (avisoAcenderElement) {
                    //    avisoAcender.innerHTML = ``;
                    //}
        
                    // Atualizar o badge com a quantidade de velas acesas
                    const badgeVelasAcesas = document.getElementById("badgeVelasAcesas");
                    if (badgeVelasAcesas) {
                        badgeVelasAcesas.innerHTML = `${velas.length}`;
                    }
        
                    // Iterar sobre as velas e adicionar uma vela publicitária a cada 10 velas
                    velas.forEach((vela, index) => {
                        if (index > 0 && index % 20 === 0) {
                            const velaPublicitaria = velasPublicitarias[index / 20 % velasPublicitarias.length]; 
        
                            const velaPublicitariaElement = document.createElement("div");
                            velaPublicitariaElement.classList.add("vela-container");
        
                            const linkElement = document.createElement("a");
                            linkElement.href = velaPublicitaria.link;
                            linkElement.target = "_blank"; 
                            linkElement.classList.add("vela-publicitaria");
        
                            const velaPublicitariaDiv = document.createElement("div");
                            velaPublicitariaDiv.classList.add("vela");
                            velaPublicitariaDiv.style.backgroundImage = `url(/img/${velaPublicitaria.imagem})`;
                            velaPublicitariaDiv.style.backgroundSize = "cover";
        
                            // Adicionando a chama à vela publicitária
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
                            
                            linkElement.appendChild(velaPublicitariaDiv);
                            linkElement.appendChild(infoPublicitariaElement);
                            linkElement.appendChild(botaoPublicitario);
        
                            velaPublicitariaElement.appendChild(linkElement);
                            velasContainer.appendChild(velaPublicitariaElement);
                            velaPublicitariaDiv.appendChild(chamaElement);
                        }
        
                        // Vela normal
                        const velaContainer = document.createElement("div");
                        velaContainer.classList.add("vela-container");
        
                        const velaElement = document.createElement("div");
                        velaElement.classList.add("vela");
        
                        if (vela.personalizacao && typeof vela.personalizacao === 'string') {
                            if (vela.personalizacao.startsWith("#")) {
                                velaElement.style.backgroundColor = vela.personalizacao;
                                const darkerColor = shadeColor(vela.personalizacao, -20);
                                velaElement.style.border = `3px solid ${darkerColor}`;
                            } else {
                                velaElement.style.backgroundImage = `url(/img/${vela.personalizacao}.png)`;
                                velaElement.style.backgroundSize = "cover";
                            }
                        } else {
                            console.warn("Vela sem personalização definida:", vela);
                        }
        
                        const chamaElement = document.createElement("div");
                        chamaElement.classList.add("vela-chama");
        
                        const infoVelaElement = document.createElement("div");
                        infoVelaElement.classList.add("info-vela");
                        infoVelaElement.innerHTML = `
                            <button class="btn btn-sm btn-secondary reagir-btn" data-id="${vela.id}">🙏<span class="reacao-count">${vela.reacoes}</span></button>
                            <p><strong>${vela.nome}</strong></p>
                            <p><small>Acesa em: ${vela.dataAcesa}</small></p>
                            <p><small>Apaga em: ${vela.dataExpira}</small></p>
                        `;
        
                        velaElement.appendChild(chamaElement);
                        velaContainer.appendChild(velaElement);
                        velaContainer.appendChild(infoVelaElement);
                        velasContainer.appendChild(velaContainer);
                    });
                })
                .catch(error => console.error("Erro ao carregar velas:", error));
        }
                            
                
    
    

        form.addEventListener("submit", function(event) {
        event.preventDefault();
        const nome = document.getElementById("nome").value;

        // Verifica se o nome tem mais de 150 caracteres
        if (nome.length > 40) {
            alert("O nome da vela deve ter no máximo 40 caracteres.");
            return; // Impede o envio do formulário
        }

        const duracao = document.getElementById("duracao").value;
        const corVela = document.getElementById("corVela").value;
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

        fetch("salvar_vela.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    nome,
                    duracao,
                    personalizacao
                })
            })
            .then(response => response.json())
            .then(data => {
                // Exibe o alerta retornado pelo PHP
                if (data.status === "success") {
                    // Chama a função para recarregar as velas
                    carregarVelas();
                }

                // Exibe o alerta na tela
                document.getElementById("alert-container").innerHTML = data.alert;
            })
            .catch(error => console.error("Erro ao salvar vela:", error));

        form.reset();

        // Fecha o modal após o envio
        var modal = bootstrap.Modal.getInstance(document.getElementById("velaModal"));
        modal.hide();
    });

    carregarVelas();

    const atualizarCheckbox = document.getElementById("atualizarVelas");
    let intervaloAtualizacao;

    // Carrega o estado salvo no localStorage
    if (localStorage.getItem("atualizarVelas") === "true") {
        atualizarCheckbox.checked = true;
        iniciarAtualizacaoAutomatica();
    }

    function iniciarAtualizacaoAutomatica() {
        if (atualizarCheckbox.checked) {
            localStorage.setItem("atualizarVelas", "true");
            intervaloAtualizacao = setInterval(carregarVelas, 5000);
        } else {
            localStorage.setItem("atualizarVelas", "false");
            clearInterval(intervaloAtualizacao);
        }
    }

    atualizarCheckbox.addEventListener("change", iniciarAtualizacaoAutomatica);


    function shadeColor(color, percent) {
        var R = parseInt(color.substr(1, 2), 16);
        var G = parseInt(color.substr(3, 2), 16);
        var B = parseInt(color.substr(5, 2), 16);

        R = Math.round(R * (100 + percent) / 100);
        G = Math.round(G * (100 + percent) / 100);
        B = Math.round(B * (100 + percent) / 100);

        R = (R < 255 ? R : 255);
        G = (G < 255 ? G : 255);
        B = (B < 255 ? B : 255);

        var RR = (R.toString(16).length == 1) ? "0" + R.toString(16) : R.toString(16);
        var GG = (G.toString(16).length == 1) ? "0" + G.toString(16) : G.toString(16);
        var BB = (B.toString(16).length == 1) ? "0" + B.toString(16) : B.toString(16);

        return "#" + RR + GG + BB;
    }

});


// Captura o seletor de cor, o radio de cor e o wrapper
const colorPickerWrapper = document.getElementById("colorPickerWrapper");
const colorPicker = document.getElementById("corVela");
const corRadio = document.getElementById("corRadio");

// FunÃ§Ã£o para controlar a visibilidade do seletor de cor
corRadio.addEventListener('change', function() {
    if (corRadio.checked) {
        colorPickerWrapper.classList.remove('d-none'); // Mostra o seletor de cor
    }
});

// Esconde o seletor de cor quando outro radio button for selecionado
document.querySelectorAll('.form-check-input').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this !== corRadio) {
            colorPickerWrapper.classList.add('d-none'); // Oculta o seletor de cor
        }
    });
});

// Exibir o toast automaticamente ao carregar a página
document.addEventListener("DOMContentLoaded", function() {
    var toastEl = document.getElementById("toastMessage");
    var toast = new bootstrap.Toast(toastEl);
    toast.show();
});
// Após 5 segundos, o alerta desaparecerá
setTimeout(function() {
    var alert = document.getElementById('error-alert');
    if (alert) {
        alert.classList.remove('show');
        alert.classList.add('fade');
    }
}, 5000); // 5000 milissegundos = 5 segundos

document.addEventListener("click", function (event) {
    let button = event.target.closest(".reagir-btn");
    if (!button) return;

    let idVela = button.getAttribute("data-id");
    let countElement = button.querySelector(".reacao-count");

    if (!countElement) {
        console.error("Elemento do contador não encontrado dentro do botão!");
        return;
    }

    // Evita múltiplos cliques enquanto a requisição está em andamento
    button.disabled = true;

    fetch("reagir.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: idVela })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            button.classList.add("btn-success"); // Adiciona uma indicação visual de sucesso
            countElement.textContent = data.reacoes;  // Atualiza o contador com o novo valor
        } else {
        }
    })
    .catch(error => {
        console.error("Erro na requisição:", error);
        button.disabled = false; // Reativa o botão em caso de erro
    });
});

