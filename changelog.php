<?php
/**
 * Página de Changelog do Velinhas
 * Versão: 3.6.0
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/utils.php';

// Define a página ativa para o menu
$activePage = 'changelog';

// Define o título e descrição da página
$pageTitle = "Changelog - Velinhas Virtuais 🕯";
$pageDescription = "Histórico de versões e atualizações do Velinhas - capela virtual para acender velas e fazer suas orações.";

// Define estilos adicionais específicos para esta página
$extraHeadContent = '
<style>
    .changelog-item {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #ddd;
    }
    
    /* Resto dos estilos permanecem iguais */
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
    
    <div class="container mb-4" id="main-content">
        <h1 class="text-center my-4">Histórico de Atualizações 🕯</h1>
        
        <div class="container py-3">
            <div class="changelog-item">
                <div class="version-header">
                    <h4>Versão 3.6.0</h4>
                </div>
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
            <div class="changelog-item">https://www.abibliadigital.com.br/
                <div class="version-header">
                    <h4>Versão 3.5.0</h4>
                </div>
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
                        Refatoração de codigo para melhor desempenho e segurança. Reorganização dos diretórios e arquivos.
                        <span class="badge bg-info feature-tag">Otimização</span>
                    </li>
                </ul>
            </div>
            <div class="changelog-item">
                <div class="version-header">
                    <h4>Versão 3.1.0</h4>
                </div>
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
            
            <div class="changelog-item">
                <div class="version-header">
                    <h4>Versão 3.0.3</h4>
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
            
            <div class="changelog-item">
                <div class="version-header">
                    <h4>Versão 3.0.1</h4>
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
            
            <div class="changelog-item">
                <div class="version-header">
                    <h4>Versão 2.1.4</h4>
                </div>
                <ul>
                    <li>
                        Correção de bugs e estabilidade geral do sistema
                        <span class="badge bg-warning feature-tag">Correção</span>
                    </li>
                </ul>
            </div>
            
            <div class="changelog-item">
                <div class="version-header">
                    <h4>Versão 2.1.0</h4>
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
            
            <div class="changelog-item">
                <div class="version-header">
                    <h4>Versão 2.0.0</h4>
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
            
            <div class="changelog-item">
                <div class="version-header">
                    <h4>Versão 1.3.0</h4>
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
            
            <div class="changelog-item">
                <div class="version-header">
                    <h4>Versão 1.2.0</h4>
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
            
            <div class="changelog-item">
                <div class="version-header">
                    <h4>Versão 1.1.0</h4>
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
            
            <div class="changelog-item">
                <div class="version-header">
                    <h4>Versão 1.0.0</h4>
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
    
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>