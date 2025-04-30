<?php
/**
 * Arquivo de configuração central do Velinhas
 * Versão: 3.8.x
 */
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
     http_response_code(403);
     exit('Acesso direto não permitido.');
 }
require_once __DIR__ . '/keys.php';

// Definição de caminhos
define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . '/data');
define('ASSETS_PATH', BASE_PATH . '/assets');

// Arquivos de dados
define('VELAS_FILE', DATA_PATH . '/velas.json');
define('STATS_FILE', DATA_PATH . '/stats.json');
define('SPAM_FILE', DATA_PATH . '/spam.json');
define('REACTION_SPAM_FILE', DATA_PATH . '/reaction_spam.json');
define('BAN_FILE', DATA_PATH . '/banlist.json');
define('CACHE_FILE', DATA_PATH . '/velas_cache.json');

// Configurações de tempo
define('SPAM_COOLDOWN', 30);    // Tempo entre criações (segundos)
define('MAX_VELAS', 5);         // Máximo de velas no intervalo
define('TEMPO_LIMITE', 20);     // Janela para verificação (segundos)
define('TEMPO_BAN', 600);       // Tempo de bloqueio (10 minutos)
define('CACHE_VALIDITY', 30);   // Validade do cache (segundos)

// Configurações de reações
define('MAX_REACTIONS', 5);     // Máximo de reações permitidas em curto período
define('REACTION_WINDOW', 5);   // Janela de tempo para reações (segundos)
define('REACTION_BAN_DURATION', 60); // Duração do bloqueio de reações (segundos)

// Configurações gerais
define('MAX_NOME_LENGTH', 40);  // Comprimento máximo do nome da vela
define('VERSION', '3.8.0');     // Versão atual do sistema

// URLs
define('BASE_URL', '');
define('ASSETS_URL', BASE_URL . '/assets');

//API BIBLIA
// Configurações da API
define('API_BIBLIA', 'https://www.abibliadigital.com.br/api/verses/nvi/random');
define('ATUALIZA_VERSICULO', 3600);

//API PIX
define('CHAVE_PIX', '');
define('BENEFICIARIO_PIX', '');
define('CIDADE_PIX', '');
define('IDENTIFICADOR_PIX', '');

//AREA ADMIN
define('SENHA_ADMIN', '');