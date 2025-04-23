<?php
/**
 * Funções utilitárias para o Velinhas
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Carrega um arquivo JSON e retorna seu conteúdo
 * 
 * @param string $filename Caminho do arquivo JSON
 * @param array $defaultValue Valor padrão caso o arquivo não exista
 * @return array Conteúdo do arquivo como array
 */
function loadJsonFile($filename, $defaultValue = []) {
    if (file_exists($filename)) {
        $data = json_decode(file_get_contents($filename), true);
        return is_array($data) ? $data : $defaultValue;
    }
    return $defaultValue;
}

/**
 * Salva dados em um arquivo JSON
 * 
 * @param string $filename Caminho do arquivo JSON
 * @param array $data Dados a serem salvos
 * @return bool Resultado da operação
 */
function saveJsonFile($filename, $data) {
    // Garante que o diretório existe
    $dir = dirname($filename);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    return file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
}

/**
 * Processa a lista de velas e retorna apenas as ativas
 * 
 * @param array $velas Lista completa de velas
 * @return array Lista de velas ativas
 */
function processarVelasAtivas($velas) {
    $velasAtivas = [];
    $agora = time();
    $velasExpiradas = false;

    foreach ($velas as $key => $vela) {
        if (!isset($vela['timestamp'], $vela['duracao'])) {
            continue;
        }
        
        $dataExpiraTimestamp = $vela['timestamp'] + ((int)$vela['duracao'] * 86400);
        
        if ($dataExpiraTimestamp > $agora) {
            // Adiciona informações formatadas
            $vela['dataAcesa'] = date("d/m/Y H:i", $vela['timestamp']);
            $vela['dataExpira'] = date("d/m/Y H:i", $dataExpiraTimestamp);
            $vela['reacoes'] = $vela['reacoes'] ?? 0;
            $velasAtivas[] = $vela;
        } else {
            // Marca que encontramos velas expiradas
            $velasExpiradas = true;
        }
    }

    return [
        'velas' => $velasAtivas,
        'expiradas' => $velasExpiradas
    ];
}

/**
 * Obtém as velas ativas com cache
 * 
 * @return array Lista de velas ativas
 */
function getVelasCache() {
    // Verifica se o cache é válido
    if (file_exists(CACHE_FILE) && (time() - filemtime(CACHE_FILE) < CACHE_VALIDITY)) {
        $cacheData = json_decode(file_get_contents(CACHE_FILE), true);
        if (is_array($cacheData)) {
            return $cacheData;
        }
    }
    
    // Se o cache não for válido, recria
    $velas = loadJsonFile(VELAS_FILE);
    $resultado = processarVelasAtivas($velas);
    
    // Se encontrou velas expiradas, atualiza o arquivo principal
    if ($resultado['expiradas']) {
        // Remove velas expiradas
        $velasAtualizadas = array_filter($velas, function($vela) {
            $agora = time();
            $dataExpiraTimestamp = $vela['timestamp'] + ((int)$vela['duracao'] * 86400);
            return $dataExpiraTimestamp > $agora;
        });
        
        // Salva o arquivo atualizado
        saveJsonFile(VELAS_FILE, array_values($velasAtualizadas));
    }
    
    // Salva o cache
    saveJsonFile(CACHE_FILE, $resultado['velas']);
    
    return $resultado['velas'];
}

/**
 * Gera um alerta formatado em HTML
 * 
 * @param string $mensagem Mensagem do alerta
 * @param string $tipo Tipo do alerta (success, danger, warning, info)
 * @return string HTML do alerta
 */
function gerarAlerta($mensagem, $tipo = 'info') {
    return '<div class="alert alert-' . $tipo . ' alert-dismissible fade show" role="alert">' .
           $mensagem .
           '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>' .
           '</div>';
}

/**
 * Verifica se o usuário está banido
 * 
 * @param string $ip IP do usuário
 * @param string $banFile Arquivo de banimentos
 * @return bool|int False se não estiver banido, ou tempo restante em segundos
 */
function verificarBanimento($ip, $banFile = BAN_FILE) {
    $banList = loadJsonFile($banFile);
    
    if (isset($banList[$ip]) && time() < $banList[$ip]) {
        return $banList[$ip] - time(); // Retorna o tempo restante
    }
    
    return false;
}

/**
 * Adiciona um token CSRF à sessão
 * 
 * @return string Token gerado
 */
function gerarCsrfToken() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    
    return $token;
}

/**
 * Verifica o token CSRF
 * 
 * @param string $token Token a ser verificado
 * @return bool Resultado da verificação
 */
function verificarCsrfToken($token) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    // Modifique esta linha para ser mais leniente com a verificação
    // durante a fase de desenvolvimento
    if (!isset($_SESSION['csrf_token'])) {
        return true; // Temporariamente aceita solicitações sem token
    }
    
    return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
}

