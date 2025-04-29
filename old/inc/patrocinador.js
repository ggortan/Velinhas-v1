const produtos = [{
        img: "https://velinhas.com.br/img/produto1.jpeg",
        titulo: "Petit Four Recheado e Coberto",
        descricao: "Bolachas Petit Four recheadas com doce de leite e cobertas com chocolate.",
        preco: "R$ 25,00",
        link: "https://wa.me/5519983587968?text=Ol%C3%A1!%20Tenho%20interesse%20nas%20del%C3%ADcias%20de%20P%C3%A1scoa."
    },
    {
        img: "https://velinhas.com.br/img/produto2.jpeg",
        titulo: "Petit Four Coberto com Chocolate",
        descricao: "Bolachas Petit Four cobertas com chocolate branco e preto (sortidas).",
        preco: "R$ 28,00",
        link: "https://wa.me/5519983587968?text=Ol%C3%A1!%20Tenho%20interesse%20nas%20del%C3%ADcias%20de%20P%C3%A1scoa."
    },
    {
        img: "https://velinhas.com.br/img/produto3.jpeg",
        titulo: "Bolachas Petit Four com formato de cabecinha de coelho",
        descricao: "Bolachas Petit Four Cabecinha de Coelho.",
        preco: "R$ 25,00",
        link: "https://wa.me/5519983587968?text=Ol%C3%A1!%20Tenho%20interesse%20nas%20del%C3%ADcias%20de%20P%C3%A1scoa."
    },
    {
        img: "https://velinhas.com.br/img/logo_hora.jpeg",
        titulo: "Faça seu pedido!",
        descricao: "Tem alguma dúvida? Entre em contato conosco e faça sua encomenda 🐰",
        contato: "https://wa.me/5519983587968?text=Ol%C3%A1!%20Tenho%20interesse%20nas%20del%C3%ADcias%20de%20P%C3%A1scoa."
    },
    {
        img: "https://velinhas.com.br/img/produto4.jpeg",
        titulo: "Petit Four Coelho com Gravata",
        descricao: "Bolachas Petit Four com formato de cabecinha de gravatinha.",
        preco: "R$ 18,00",
        link: "https://wa.me/5519983587968?text=Ol%C3%A1!%20Tenho%20interesse%20nas%20del%C3%ADcias%20de%20P%C3%A1scoa."
    },
    {
        img: "https://velinhas.com.br/img/produto5.jpeg",
        titulo: "Pão de Mel Decorado",
        descricao: "Pão de Mel individual coberto com chocolate e decorado.",
        preco: "R$ 4,00",
        link: "https://wa.me/5519983587968?text=Ol%C3%A1!%20Tenho%20interesse%20nas%20del%C3%ADcias%20de%20P%C3%A1scoa."
    },
    {
        img: "https://velinhas.com.br/img/produto6.jpeg",
        titulo: "Caixa 4 Pães de Mel",
        descricao: "Caixa com 4 Pães de Mel cobertos com chocolate e decorados.",
        preco: "R$ 35,00",
        link: "https://wa.me/5519983587968?text=Ol%C3%A1!%20Tenho%20interesse%20nas%20del%C3%ADcias%20de%20P%C3%A1scoa."
    },
    // Card de contato
    {
        img: "https://velinhas.com.br/img/logo_hora.jpeg",
        titulo: "A Páscoa está chegando!",
        descricao: "Surpreenda quem você ama com nossos deliciosos chocolates artesanais e bolachas de Páscoa feitas com muito carinho!",
        contato: "https://wa.me/5519983587968?text=Ol%C3%A1!%20Tenho%20interesse%20nas%20del%C3%ADcias%20de%20P%C3%A1scoa."
    }
];

const scrollContent = document.querySelector(".scroll-content");
// Criar os cards
produtos.forEach((produto) => {
    scrollContent.appendChild(criarCard(produto));
});

function criarCard(produto) {
    const card = document.createElement("div");
    card.classList.add("card", "card-custom1");

    if (produto.contato) {
        // Card de Contato mantém o botão
        card.classList.add("contact-card-custom1");
        card.innerHTML = `
      <img src="${produto.img}" class="card-img-top" alt="${produto.titulo}">
      <div class="card-body card-body-custom1">
        <h5 class="card-title card-title-custom1">${produto.titulo}</h5>
        <p class="card-text card-text-custom1">${produto.descricao}</p>
        <a href="${produto.contato}" class="btn btn-custom1" target="_blank">Enviar Whatsapp</a>
      </div>
    `;
    } else {
        // Cards normais são clicáveis
        card.innerHTML = `
      <img src="${produto.img}" class="card-img-top" alt="${produto.titulo}">
      <div class="card-body card-body-custom1">
        <h5 class="card-title card-title-custom1">${produto.titulo}</h5>
        <p class="card-text card-text-custom1">${produto.descricao}</p>
      </div>
      <div class="card-footer card-footer-custom1">
        <span class="price">${produto.preco}</span>
      </div>
    `;

        // Deixar o card inteiro clicável sem mudar o estilo
        card.style.cursor = "pointer";
        card.addEventListener("click", () => {
            window.open(produto.link, "_blank");
        });
    }

    return card;
}

let intervalo;


function iniciarCarrossel() {
    intervalo = setInterval(() => {
        // Desloca um card para a esquerda a cada 5 segundos
        scrollContent.style.transition = "transform 0.5s ease";
        scrollContent.style.transform = `translateX(-${cardWidth}px)`;

        setTimeout(() => {
            scrollContent.style.transition = "none";
            scrollContent.style.transform = "translateX(0)";
            const firstCard = scrollContent.firstElementChild;
            scrollContent.appendChild(firstCard); // Move o primeiro card para o final
        }, 500);
    }, 5000); // Intervalo de 5 segundos
}

// Navegação manual com setas
const btnLeft = document.querySelector(".arrow-left");
const btnRight = document.querySelector(".arrow-right");

let cardWidth; // Define a variável globalmente

document.addEventListener("DOMContentLoaded", function() {
    const scrollContent = document.querySelector(".scroll-content");

    if (!scrollContent) {
        console.error("Erro: .scroll-content não encontrado!");
        return;
    }

    // Selecionar apenas os cards com a classe "card card-custom1"
    const firstValidCard = scrollContent.querySelector(".card.card-custom1");

    if (!firstValidCard) {
        console.error("Erro: Nenhum card válido encontrado dentro de .scroll-content!");
        return;
    }

    const cardWidth = firstValidCard.offsetWidth + 15; // Considerando margem/padding

    iniciarCarrossel();

    const btnLeft = document.querySelector(".arrow-left");
    const btnRight = document.querySelector(".arrow-right");

    if (btnLeft && btnRight) {
        btnLeft.addEventListener("click", () => moverEsquerda(cardWidth));
        btnRight.addEventListener("click", () => moverDireita(cardWidth));
    } else {
        console.error("Erro: Botões de navegação não encontrados!");
    }
});

function moverEsquerda() {
    const scrollContent = document.querySelector(".scroll-content");
    const lastCard = scrollContent.lastElementChild;
    scrollContent.insertBefore(lastCard, scrollContent.firstElementChild);
    scrollContent.style.transition = "none";
    scrollContent.style.transform = `translateX(-${cardWidth}px)`;

    setTimeout(() => {
        scrollContent.style.transition = "transform 0.5s ease";
        scrollContent.style.transform = "translateX(0)";
    }, 10);
}

function moverDireita() {
    const scrollContent = document.querySelector(".scroll-content");
    scrollContent.style.transition = "transform 0.5s ease";
    scrollContent.style.transform = `translateX(-${cardWidth}px)`;

    setTimeout(() => {
        scrollContent.style.transition = "none";
        scrollContent.style.transform = "translateX(0)";
        const firstCard = scrollContent.firstElementChild;
        scrollContent.appendChild(firstCard);
    }, 500);
}

function showElementById(elementId) {
    var element = document.getElementById(elementId);
    if (element) {
        element.style.display = 'block';
    } else {
        console.error('Elemento não encontrado:', elementId);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        showElementById('patrocinador');
    }, 10000);
});