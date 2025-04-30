<?php
/**
 * Página Sobre o Velinhas
 * Versão: 3.7.1
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/utils.php';

// Define a página ativa para o menu
$activePage = 'sobre';

// Define o título e descrição da página
$pageTitle = "Sobre - Velinhas Virtuais 🕯";
$pageDescription = "Conheça a história do Velinhas - capela virtual para acender velas e fazer suas orações.";

// Define estilos adicionais específicos para esta página
$extraHeadContent = '
<style>
    .about-section {
        margin-bottom: 4rem;
    }
    
    .about-header {
        border-bottom: 2px solid #f8c471;
        padding-bottom: 1rem;
        margin-bottom: 2rem;
    }
    
    .creator-card {
        border-radius: 10px;
        background-color: var(--color-card);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        padding: 2rem;
        transition: all 0.3s ease;
    }
    
    .creator-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transform: translateY(-5px);
    }
    
    .creator-image {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #f8c471;
    }
    
    .timeline-item {
        position: relative;
        padding-left: 30px;
        margin-bottom: 1.5rem;
    }
    
    .timeline-item:before {
        content: "";
        position: absolute;
        left: 0;
        top: 5px;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background-color: #f8c471;
        z-index: 1;
    }
    
    .timeline-item:after {
        content: "";
        position: absolute;
        left: 7px;
        top: 20px;
        bottom: -15px;
        width: 1px;
        background-color: #ddd;
    }
    
    .timeline-item:last-child:after {
        display: none;
    }
    
    .badge.feature-tag {
        font-size: 0.75rem;
        padding: 0.3rem 0.5rem;
        border-radius: 50px;
        margin-left: 0.5rem;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: rgba(248, 196, 113, 0.6);
        color: #212529;
    }
    
    .mission-vision-card {
        border-radius: 10px;
        background-color: rgba(248, 196, 113, 0.1);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid #f8c471;
    }
    
    .candle-icon {
        color: #f8c471;
        margin-right: 0.5rem;
    }
    
    .stats-box {
        background-color: var(--color-card);
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        text-align: center;
        margin-bottom: 1.5rem;
    }
    
    .stats-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #f8c471;
        display: block;
        margin-bottom: 0.5rem;
    }
    .creator-label {
        color: var(--color-primary);
    }
    
</style>';

// Inclui o componente head
require_once __DIR__ . '/includes/head.php';
?>
<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
    <!-- Esta tag será substituída pelo conteúdo de includes/head.php -->
</head>
<body>
    <?php require_once __DIR__ . '/includes/header.php'; ?>
    
    <div class="container mb-5" id="main-content">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="text-center my-4">Sobre o Velinhas 🕯</h1>
                
                <!-- Seção de Introdução -->
                <div class="about-section">
                    <div class="about-header">
                        <h2><i class="fas fa-heart candle-icon"></i> Nossa História</h2>
                    </div>
                    <p class="lead text-center">O Velinhas é um espaço virtual sagrado, criado com amor e dedicação para permitir que pessoas de qualquer lugar possam acender uma vela, fazer suas orações e compartilhar intenções. Fundado em março de 2025, nosso projeto foi inspirado nas capelas de velas das igrejas que já passaram pelo processo de modernização com velas de LED e agora avançam para a digitalização.</p>
                    <p class="text-center">A ideia do Velinhas surgiu durante uma roda de conversa sobre como a tecnologia poderia aproximar as pessoas de suas práticas espirituais. Percebendo a necessidade de um espaço virtual que preservasse a essência das capelas tradicionais, decidimos criar uma plataforma que permitisse acender velas virtuais, mantendo o significado e o simbolismo das velas físicas, mas com o alcance e acessibilidade que apenas o ambiente digital pode proporcionar.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-4 mb-4">
                            <div class="mission-vision-card">
                                <h4><i class="fas fa-bullseye candle-icon"></i> Missão</h4>
                                <p>Ser um espaço digital de fé e esperança, onde pessoas possam expressar suas intenções e encontrar conforto espiritual.</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="mission-vision-card">
                                <h4><i class="fas fa-eye candle-icon"></i> Visão</h4>
                                <p>Tornar-se a maior capela virtual do Brasil, unindo pessoas através da fé e da oração.</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="mission-vision-card">
                                <h4><i class="fas fa-gem candle-icon"></i> Valores</h4>
                                <p>Respeito, inclusão, fé, esperança e simplicidade guiam todas as nossas decisões.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estatísticas -->
                <div class="about-section">
                    <div class="about-header">
                        <h2><i class="fas fa-chart-line candle-icon"></i> Nosso Impacto</h2>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stats-box">
                                <?php
                                // Obtém o número total de velinhas acesas
                                $statsFile = DATA_PATH . '/../data/stats.json';
                                $totalVelinhas = 0;
                                if (file_exists($statsFile)) {
                                    $stats = json_decode(file_get_contents($statsFile), true);
                                    $totalVelinhas = isset($stats['ultimo_id']) ? (int)$stats['ultimo_id'] : 0;
                                }
                                
                                // Formata o número para exibição
                                $formattedTotal = $totalVelinhas;
                                if ($totalVelinhas >= 1000) {
                                    $formattedTotal = number_format($totalVelinhas / 1000, 1) . 'k';
                                }
                                if ($totalVelinhas >= 1000000) {
                                    $formattedTotal = number_format($totalVelinhas / 1000000, 1) . 'M';
                                }
                                ?>
                                <span class="stats-number"><?php echo $formattedTotal; ?></span>
                                <span>Velas acesas</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-box">
                                <span class="stats-number">50+</span>
                                <span>Usuários únicos</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-box">
                                <span class="stats-number">2k+</span>
                                <span>Orações compartilhadas</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-box">
                                <span class="stats-number">3.7.1</span>
                                <span>Versão atual</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Criador -->
                <div class="about-section">
                    <div class="about-header">
                        <h2><i class="fas fa-user candle-icon"></i> Criador</h2>
                    </div>
                    
                    <div class="creator-card">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <img src="https://media.licdn.com/dms/image/v2/D4D03AQFwRECiCCYg1A/profile-displayphoto-shrink_800_800/profile-displayphoto-shrink_800_800/0/1732231629174?e=1751500800&v=beta&t=YaAYADsq_HjAXIp5EAAurHW9JxA7ti1422aBVoNP_PE" alt="Criador do Velinhas" class="creator-image">
                            </div>
                            <div class="col-md-8">
                                <h3>Gabriel Gortan</h3>
                                <p class="text-muted creator-label mb-3">Desenvolvedor & Fundador</p>
                                <p>Apaixonado por tecnologia e espiritualidade, criei o Velinhas com o objetivo de unir esses dois mundos. Acredito que a tecnologia pode ser utilizada como uma ferramenta para aproximar as pessoas de sua fé e proporcionar momentos de reflexão em um mundo cada vez mais acelerado.</p>
                                <div class="mt-3">
                                    <a href="https://github.com/ggortan" class="btn btn-sm btn-outline-dark me-2" target="_blank"><i class="fab fa-github"></i> GitHub</a>
                                    <a href="https://www.linkedin.com/in/gabrielgortan" class="btn btn-sm btn-outline-primary me-2" target="_blank"><i class="fab fa-linkedin"></i> LinkedIn</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Seção Changelog -->
                <div class="about-section">
                    <div class="about-header">
                        <h2><i class="fas fa-history candle-icon"></i> Histórico de Atualizações</h2>
                    </div>
                    
                    <p class="mb-4">Conheça a evolução do Velinhas ao longo do tempo e como estamos constantemente melhorando a experiência dos nossos usuários:</p>
                    
                    <div class="accordion" id="changelogAccordion">
                        <!-- Versão 3.8.0 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading380">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse380" aria-expanded="true" aria-controls="collapse380">
                                    <strong>Versão 3.8.0</strong> - Novos recursos 
                                </button>
                            </h2>
                            <div id="collapse380" class="accordion-collapse collapse show" aria-labelledby="heading380" data-bs-parent="#changelogAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li>
                                            Painel administrativo para moderação e consulta de velas
                                            <span class="badge bg-success feature-tag">Novo Recurso</span>
                                        </li>
                                        <li>
                                            Mensagem personalizada ao criar a vela
                                            <span class="badge bg-success feature-tag">Novo Recurso</span>
                                        </li>
                                        <li>
                                            Criação da página Sobre
                                            <span class="badge bg-success feature-tag">Novo Recurso</span>
                                        </li>
                                        <li>
                                            Remoção da página changelog
                                            <span class="badge bg-danger feature-tag">Depreciado</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- Versão 3.7.1 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading370">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse370" aria-expanded="false" aria-controls="collapse370">
                                    <strong>Versão 3.7.0</strong> - Melhorias na experiência de compartilhamento
                                </button>
                            </h2>
                            <div id="collapse370" class="accordion-collapse collapse" aria-labelledby="heading370" data-bs-parent="#changelogAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li>
                                            Implementação da página individual para velas
                                            <span class="badge bg-success feature-tag">Novo Recurso</span>
                                        </li>
                                        <li>
                                            Sistema de compartilhamento
                                            <span class="badge bg-success feature-tag">Novo Recurso</span>
                                        </li>
                                        <li>
                                            Melhorias no sistema de reações
                                            <span class="badge bg-info feature-tag">Otimização</span>
                                        </li>
                                        <li>
                                            Melhorias no sistema de cache
                                            <span class="badge bg-info feature-tag">Otimização</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Versão 3.6.0 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading360">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse360" aria-expanded="false" aria-controls="collapse360">
                                    <strong>Versão 3.6.0</strong> - Integração com API da Bíblia Digital
                                </button>
                            </h2>
                            <div id="collapse360" class="accordion-collapse collapse" aria-labelledby="heading360" data-bs-parent="#changelogAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li>
                                            Versículo do dia fornecido pela API <a href="https://www.abibliadigital.com.br/" target="_blank">https://www.abibliadigital.com.br/</a>
                                            <span class="badge bg-success feature-tag">Novo Recurso</span>
                                        </li>
                                        <li>
                                            Implementação de banners de produtos afiliados
                                            <span class="badge bg-primary feature-tag">Beta</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Versão 3.5.0 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading350">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse350" aria-expanded="false" aria-controls="collapse350">
                                    <strong>Versão 3.5.0</strong> - Tema escuro e suporte a PWA
                                </button>
                            </h2>
                            <div id="collapse350" class="accordion-collapse collapse" aria-labelledby="heading350" data-bs-parent="#changelogAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li>
                                            Implementação do tema escuro
                                            <span class="badge bg-success feature-tag">Novo Recurso</span>
                                        </li>
                                        <li>
                                            Adição de suporte para instalação como aplicativo (PWA)
                                            <span class="badge bg-success feature-tag">Novo Recurso</span>
                                        </li>
                                        <li>
                                            Refatoração de código para melhor desempenho e segurança. Reorganização dos diretórios e arquivos.
                                            <span class="badge bg-info feature-tag">Otimização</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Versão 3.1.0 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading310">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse310" aria-expanded="false" aria-controls="collapse310">
                                    <strong>Versão 3.1.0</strong> - Sistema de reações
                                </button>
                            </h2>
                            <div id="collapse310" class="accordion-collapse collapse" aria-labelledby="heading310" data-bs-parent="#changelogAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li>
                                            Implementação da funcionalidade de reação nas velas
                                            <span class="badge bg-success feature-tag">Novo Recurso</span>
                                        </li>
                                        <li>
                                            Melhorias de desempenho e otimização de código
                                            <span class="badge bg-info feature-tag">Otimização</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Versões Anteriores (Acordeão expandido) -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingPrevious">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrevious" aria-expanded="false" aria-controls="collapsePrevious">
                                    <strong>Versões Anteriores</strong> - Da versão 1.0.0 até 3.0.3
                                </button>
                            </h2>
                            <div id="collapsePrevious" class="accordion-collapse collapse" aria-labelledby="headingPrevious" data-bs-parent="#changelogAccordion">
                                <div class="accordion-body">
                                    <!-- Versão 3.0.3 -->
                                    <div class="timeline-item">
                                        <div class="version-header">
                                            <h5>Versão 3.0.3</h5>
                                        </div>
                                        <ul>
                                            <li>
                                                Correção de bugs diversos e melhorias de estabilidade
                                                <span class="badge bg-warning feature-tag">Correção</span>
                                            </li>
                                            <li>
                                                Otimização da renderização de velas na capela
                                                <span class="badge bg-info feature-tag">Otimização</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Versão 3.0.1 -->
                                    <div class="timeline-item">
                                        <div class="version-header">
                                            <h5>Versão 3.0.1</h5>
                                        </div>
                                        <ul>
                                            <li>
                                                Velas Virtuais agora é <strong>Velinhas.com.br</strong>
                                                <span class="badge bg-primary feature-tag">Novidade</span>
                                            </li>
                                            <li>
                                                Migração completa do serviço para nova infraestrutura
                                                <span class="badge bg-secondary feature-tag">Infraestrutura</span>
                                            </li>
                                            <li>
                                                Reorganização dos diretórios e estrutura de arquivos
                                                <span class="badge bg-info feature-tag">Otimização</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Versão 2.1.4 -->
                                    <div class="timeline-item">
                                        <div class="version-header">
                                            <h5>Versão 2.1.4</h5>
                                        </div>
                                        <ul>
                                            <li>
                                                Correção de bugs e estabilidade geral do sistema
                                                <span class="badge bg-warning feature-tag">Correção</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Versão 2.1.0 -->
                                    <div class="timeline-item">
                                        <div class="version-header">
                                            <h5>Versão 2.1.0</h5>
                                        </div>
                                        <ul>
                                            <li>
                                                Alteração visual da página inicial (cores e elementos)
                                                <span class="badge bg-success feature-tag">Design</span>
                                            </li>
                                            <li>
                                                Implementação do bloco de patrocinador oficial
                                                <span class="badge bg-success feature-tag">Novo Recurso</span>
                                            </li>
                                            <li>
                                                Implementação da vela publicitária
                                                <span class="badge bg-primary feature-tag">Beta</span>
                                            </li>
                                            <li>
                                                Correção de bugs diversos
                                                <span class="badge bg-warning feature-tag">Correção</span>
                                            </li>
                                            <li>
                                                Alteração do rótulo "Atualização Automática" para ícone
                                                <span class="badge bg-info feature-tag">UX</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Versão 2.0.0 -->
                                    <div class="timeline-item">
                                        <div class="version-header">
                                            <h5>Versão 2.0.0</h5>
                                        </div>
                                        <ul>
                                            <li>
                                                Implementação de regras anti-spam
                                                <span class="badge bg-success feature-tag">Segurança</span>
                                            </li>
                                            <li>
                                                Implementação de mensagens de retorno em formato de alertas
                                                <span class="badge bg-info feature-tag">UX</span>
                                            </li>
                                            <li>
                                                Correção de bugs e melhorias gerais
                                                <span class="badge bg-warning feature-tag">Correção</span>
                                            </li>
                                            <li>
                                                Restauração da capela de velas após incêndio no servidor
                                                <span class="badge bg-secondary feature-tag">Infraestrutura</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Versão 1.3.0 -->
                                    <div class="timeline-item">
                                        <div class="version-header">
                                            <h5>Versão 1.3.0</h5>
                                        </div>
                                        <ul>
                                            <li>
                                                Adição de velas personalizadas
                                                <span class="badge bg-success feature-tag">Novo Recurso</span>
                                            </li>
                                            <li>
                                                Correção de bugs no carregamento das páginas principais
                                                <span class="badge bg-warning feature-tag">Correção</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Versão 1.2.0 -->
                                    <div class="timeline-item">
                                        <div class="version-header">
                                            <h5>Versão 1.2.0</h5>
                                        </div>
                                        <ul>
                                            <li>
                                                Modificação no modal de criação de velas
                                                <span class="badge bg-info feature-tag">UX</span>
                                            </li>
                                            <li>
                                                Implementação da capacidade na capela de velas
                                                <span class="badge bg-success feature-tag">Novo Recurso</span>
                                            </li>
                                            <li>
                                                Adição da barra de navegação e melhorias de responsividade
                                                <span class="badge bg-info feature-tag">Design</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Versão 1.1.0 -->
                                    <div class="timeline-item">
                                        <div class="version-header">
                                            <h5>Versão 1.1.0</h5>
                                        </div>
                                        <ul>
                                            <li>
                                                Criação da funcionalidade de personalização de velas
                                                <span class="badge bg-success feature-tag">Novo Recurso</span>
                                            </li>
                                            <li>
                                                Implementação da funcionalidade de atualização automática
                                                <span class="badge bg-success feature-tag">Novo Recurso</span>
                                            </li>
                                            <li>
                                                Design responsivo usando Bootstrap 5
                                                <span class="badge bg-info feature-tag">Design</span>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Versão 1.0.0 -->
                                    <div class="timeline-item">
                                        <div class="version-header">
                                            <h5>Versão 1.0.0</h5>
                                        </div>
                                        <ul>
                                            <li>
                                                🚀 Lançamento inicial do Velas Virtuais
                                                <span class="badge bg-primary feature-tag">Lançamento</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contribua -->
                <div class="about-section">
                    <div class="about-header">
                        <h2><i class="fas fa-hands-helping candle-icon"></i> Contribua</h2>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <p>O Velinhas é mantido com muito esforço e dedicação. Se você gostou do nosso projeto e deseja contribuir, considere fazer uma doação ou compartilhar o Velinhas com amigos e familiares.</p>
                            <p>Também estamos abertos a parcerias e sugestões para melhorar ainda mais a experiência dos nossos usuários.</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#doacaoModal">
                                <i class="bi bi-heart-fill"></i> Ajudar o Velinhas
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Contato -->
                <div class="about-section mb-0">
                    <div class="about-header">
                        <h2><i class="fas fa-envelope candle-icon"></i> Entre em Contato</h2>
                    </div>
                    
                    <div class="row justify-content-center">
                        <div class="col-md-8 text-center">
                            <p class="lead mb-2">Tem dúvidas, sugestões ou precisa de suporte? Estamos aqui para ajudar!</p>
                            
                            <div class="contact-card p-4 mb-4">
                                <i class="fab fa-linkedin fa-3x mb-3" style="color: #0077b5;"></i>
                                <h4 class="mb-3">Contato via LinkedIn</h4>
                                <p>A maneira mais eficiente de entrar em contato conosco é através do LinkedIn.</p>
                                <a href="https://www.linkedin.com/in/gabrielgortan" class="btn btn-primary btn-lg mt-2" target="_blank">
                                    <i class="bi bi-linkedin me-2"></i> Enviar Mensagem
                                </a>
                            </div>
                            
                            <p class="text-muted small">Agradecemos seu interesse no Velinhas. Estamos sempre buscando melhorar nossa capela virtual!</p>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
    
    <script>
        // Script para animar os números de estatísticas quando entram na tela
        document.addEventListener('DOMContentLoaded', function() {
            const statsNumbers = document.querySelectorAll('.stats-number');
            
            const observerOptions = {
                threshold: 0.5
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = entry.target;
                        const value = target.textContent;
                        
                        // Verificar se termina com + (para valores como 150k+)
                        const hasPlus = value.endsWith('+');
                        let numericValue = hasPlus ? value.slice(0, -1) : value;
                        
                        // Verificar se tem k (para valores como 150k)
                        const hasK = numericValue.endsWith('k');
                        if (hasK) {
                            numericValue = parseFloat(numericValue.slice(0, -1)) * 1000;
                        } else {
                            numericValue = parseFloat(numericValue);
                        }
                        
                        let startValue = 0;
                        let duration = 1500;
                        let startTime = null;
                        
                        function animate(timestamp) {
                            if (!startTime) startTime = timestamp;
                            const progress = Math.min((timestamp - startTime) / duration, 1);
                            let currentValue = Math.floor(progress * numericValue);
                            
                            if (hasK) {
                                currentValue = (currentValue / 1000).toFixed(1) + 'k';
                            }
                            
                            if (hasPlus) {
                                currentValue += '+';
                            }
                            
                            target.textContent = currentValue;
                            
                            if (progress < 1) {
                                requestAnimationFrame(animate);
                            }
                        }
                        
                        requestAnimationFrame(animate);
                        observer.unobserve(target);
                    }
                });
            }, observerOptions);
            
            statsNumbers.forEach(number => {
                observer.observe(number);
            });
        });
    </script>