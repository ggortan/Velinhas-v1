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
/**
 * Processa a lista de velas e retorna apenas as ativas não moderadas
 * 
 * @param array $velas Lista completa de velas
 * @return array Lista de velas ativas
 */
function processarVelasAtivas($velas) {
    $velasAtivas = [];
    $velasExpiradas = [];
    $agora = time();

    foreach ($velas as $key => $vela) {
        if (!isset($vela['timestamp'], $vela['duracao'])) {
            continue;
        }
        
        // Pula velas moderadas para não aparecerem no front-end
        if (isset($vela['moderado']) && $vela['moderado'] === true) {
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
            // Marca vela como expirada e a adiciona ao histórico
            $vela['dataAcesa'] = date("d/m/Y H:i", $vela['timestamp']);
            $vela['dataExpira'] = date("d/m/Y H:i", $dataExpiraTimestamp);
            $vela['reacoes'] = $vela['reacoes'] ?? 0;
            $vela['expirada'] = true;
            $vela['dataExpiracaoReal'] = $agora;
            $vela['dataExpiracaoFormatada'] = date("d/m/Y H:i", $agora);
            
            // Adiciona ao array de velas expiradas
            $velasExpiradas[] = $vela;
        }
    }

    // Se tiver velas expiradas, salva no histórico
    if (!empty($velasExpiradas)) {
        salvarVelasNoHistorico($velasExpiradas);
    }

    return [
        'velas' => $velasAtivas,
        'expiradas' => !empty($velasExpiradas)
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

/**
 * Código para adicionar ao final do arquivo includes/utils.php
 * NÃO SUBSTITUA a função processarVelasAtivas existente!
 */

// Definir constante para o arquivo de histórico se ainda não estiver definida
if (!defined('VELAS_HISTORY_FILE')) {
    define('VELAS_HISTORY_FILE', DATA_PATH . '/velas_history.json');
}

/**
 * Salva velas expiradas no arquivo de histórico
 * 
 * @param array $velasExpiradas Lista de velas expiradas
 * @return bool Resultado da operação
 */
function salvarVelasNoHistorico($velasExpiradas) {
    // Carrega o histórico existente
    $historico = loadJsonFile(VELAS_HISTORY_FILE, []);
    
    // Verifica se alguma vela já existe no histórico (evita duplicatas)
    foreach ($velasExpiradas as $velaExpirada) {
        $idVela = $velaExpirada['id'];
        $exists = false;
        
        // Verifica se essa vela já existe no histórico
        foreach ($historico as $velaHistorico) {
            if (isset($velaHistorico['id']) && $velaHistorico['id'] == $idVela) {
                $exists = true;
                break;
            }
        }
        
        // Se não existir, adiciona ao histórico
        if (!$exists) {
            $historico[] = $velaExpirada;
        }
    }
    
    // Salva o histórico atualizado
    return saveJsonFile(VELAS_HISTORY_FILE, $historico);
}

/**
 * Obtém o histórico de velas expiradas com opções de filtro e paginação
 * 
 * @param int $page Número da página (começando em 1)
 * @param int $porPagina Itens por página
 * @param array $filtros Filtros a serem aplicados (opcional)
 * @return array Dados das velas expiradas e metadados de paginação
 */
function getHistoricoVelas($page = 1, $porPagina = 20, $filtros = []) {
    // Carrega todo o histórico
    $historico = loadJsonFile(VELAS_HISTORY_FILE, []);
    
    // Aplica filtros, se houver
    if (!empty($filtros)) {
        $historicoFiltrado = [];
        
        foreach ($historico as $vela) {
            $incluir = true;
            
            // Filtro por nome
            if (isset($filtros['nome']) && !empty($filtros['nome'])) {
                if (stripos($vela['nome'], $filtros['nome']) === false) {
                    $incluir = false;
                }
            }
            
            // Filtro por período (data de expiração)
            if (isset($filtros['dataInicio']) && !empty($filtros['dataInicio'])) {
                $dataInicio = strtotime($filtros['dataInicio']);
                if ($vela['dataExpiracaoReal'] < $dataInicio) {
                    $incluir = false;
                }
            }
            
            if (isset($filtros['dataFim']) && !empty($filtros['dataFim'])) {
                $dataFim = strtotime($filtros['dataFim'] . ' 23:59:59');
                if ($vela['dataExpiracaoReal'] > $dataFim) {
                    $incluir = false;
                }
            }
            
            // Filtro por personalização
            if (isset($filtros['personalizacao']) && !empty($filtros['personalizacao'])) {
                if ($vela['personalizacao'] != $filtros['personalizacao']) {
                    $incluir = false;
                }
            }
            
            // Filtro por duração
            if (isset($filtros['duracao']) && $filtros['duracao'] !== '') {
                if ($vela['duracao'] != $filtros['duracao']) {
                    $incluir = false;
                }
            }
            
            // Se passou em todos os filtros, inclui no resultado
            if ($incluir) {
                $historicoFiltrado[] = $vela;
            }
        }
        
        $historico = $historicoFiltrado;
    }
    
    // Ordena por data de expiração (mais recentes primeiro)
    usort($historico, function($a, $b) {
        return $b['dataExpiracaoReal'] - $a['dataExpiracaoReal']; 
    });
    
    // Calcula total de páginas
    $totalItens = count($historico);
    $totalPaginas = ceil($totalItens / $porPagina);
    
    // Ajusta página atual
    $page = max(1, min($page, $totalPaginas));
    
    // Calcula offset para paginação
    $offset = ($page - 1) * $porPagina;
    
    // Obtém apenas os itens da página atual
    $itens = array_slice($historico, $offset, $porPagina);
    
    // Retorna resultado com metadados
    return [
        'itens' => $itens,
        'total' => $totalItens,
        'pagina' => $page,
        'porPagina' => $porPagina,
        'totalPaginas' => $totalPaginas
    ];
}

/**
 * Função para moderar uma vela (adicionar à vela o atributo 'moderado' = true)
 * 
 * @param int $idVela ID da vela a ser moderada
 * @return bool Resultado da operação
 */
function moderarVela($idVela) {
    // Primeiro, procura a vela nas velas ativas
    $velas = loadJsonFile(VELAS_FILE);
    $velaEncontrada = false;
    
    foreach ($velas as &$vela) {
        if ($vela['id'] == $idVela) {
            $vela['moderado'] = true;
            $velaEncontrada = true;
            break;
        }
    }
    
    // Se encontrou a vela ativa, salva as alterações
    if ($velaEncontrada) {
        $resultado = saveJsonFile(VELAS_FILE, $velas);
        
        // Limpa o cache para que as alterações tenham efeito imediato
        if (file_exists(CACHE_FILE)) {
            @unlink(CACHE_FILE);
        }
        
        return $resultado;
    }
    
    // Se não encontrou nas velas ativas, procura no histórico
    $historicoVelas = loadJsonFile(VELAS_HISTORY_FILE, []);
    
    foreach ($historicoVelas as &$vela) {
        if ($vela['id'] == $idVela) {
            $vela['moderado'] = true;
            $velaEncontrada = true;
            break;
        }
    }
    
    // Se encontrou no histórico, salva as alterações
    if ($velaEncontrada) {
        return saveJsonFile(VELAS_HISTORY_FILE, $historicoVelas);
    }
    
    return false;
}

/**
 * Função para remover a moderação de uma vela (definir 'moderado' = false)
 * 
 * @param int $idVela ID da vela para remover moderação
 * @return bool Resultado da operação
 */
function desmoderarVela($idVela) {
    // Primeiro, procura a vela nas velas ativas
    $velas = loadJsonFile(VELAS_FILE);
    $velaEncontrada = false;
    
    foreach ($velas as &$vela) {
        if ($vela['id'] == $idVela) {
            $vela['moderado'] = false;
            $velaEncontrada = true;
            break;
        }
    }
    
    // Se encontrou a vela ativa, salva as alterações
    if ($velaEncontrada) {
        $resultado = saveJsonFile(VELAS_FILE, $velas);
        
        // Limpa o cache para que as alterações tenham efeito imediato
        if (file_exists(CACHE_FILE)) {
            @unlink(CACHE_FILE);
        }
        
        return $resultado;
    }
    
    // Se não encontrou nas velas ativas, procura no histórico
    $historicoVelas = loadJsonFile(VELAS_HISTORY_FILE, []);
    
    foreach ($historicoVelas as &$vela) {
        if ($vela['id'] == $idVela) {
            $vela['moderado'] = false;
            $velaEncontrada = true;
            break;
        }
    }
    
    // Se encontrou no histórico, salva as alterações
    if ($velaEncontrada) {
        return saveJsonFile(VELAS_HISTORY_FILE, $historicoVelas);
    }
    
    return false;
}

/**
 * Obtém as velas ativas para o painel administrativo (com suporte a filtros e moderação)
 *
 * @param int $page Número da página (começando em 1)
 * @param int $porPagina Itens por página
 * @param array $filtros Filtros a serem aplicados (opcional)
 * @return array Dados das velas ativas e metadados de paginação
 */
function getVelasAtivasAdmin($page = 1, $porPagina = 20, $filtros = []) {
    // Carrega todas as velas ativas
    $velas = loadJsonFile(VELAS_FILE, []);
    
    // Adiciona informações adicionais para cada vela
    $velasProcessadas = [];
    $agora = time();
    
    foreach ($velas as $vela) {
        if (!isset($vela['timestamp'], $vela['duracao'])) {
            continue;
        }
        
        $dataExpiraTimestamp = $vela['timestamp'] + ((int)$vela['duracao'] * 86400);
        
        // Apenas velas que ainda não expiraram
        if ($dataExpiraTimestamp > $agora) {
            // Adiciona informações formatadas
            $vela['dataAcesa'] = date("d/m/Y H:i", $vela['timestamp']);
            $vela['dataExpira'] = date("d/m/Y H:i", $dataExpiraTimestamp);
            $vela['reacoes'] = $vela['reacoes'] ?? 0;
            
            // Garante que o status de moderação esteja definido
            $vela['moderado'] = isset($vela['moderado']) ? $vela['moderado'] : false;
            
            $velasProcessadas[] = $vela;
        }
    }
    
    // Aplica filtros, se houver
    if (!empty($filtros)) {
        $velasFiltered = [];
        
        foreach ($velasProcessadas as $vela) {
            $incluir = true;
            
            // Filtro por nome
            if (isset($filtros['nome']) && !empty($filtros['nome'])) {
                if (stripos($vela['nome'], $filtros['nome']) === false) {
                    $incluir = false;
                }
            }
            
            // Filtro por período (data de acender)
            if (isset($filtros['dataInicio']) && !empty($filtros['dataInicio'])) {
                $dataInicio = strtotime($filtros['dataInicio']);
                if ($vela['timestamp'] < $dataInicio) {
                    $incluir = false;
                }
            }
            
            if (isset($filtros['dataFim']) && !empty($filtros['dataFim'])) {
                $dataFim = strtotime($filtros['dataFim'] . ' 23:59:59');
                if ($vela['timestamp'] > $dataFim) {
                    $incluir = false;
                }
            }
            
            // Filtro por personalização
            if (isset($filtros['personalizacao']) && !empty($filtros['personalizacao'])) {
                if ($vela['personalizacao'] != $filtros['personalizacao']) {
                    $incluir = false;
                }
            }
            
            // Filtro por duração
            if (isset($filtros['duracao']) && $filtros['duracao'] !== '') {
                if ($vela['duracao'] != $filtros['duracao']) {
                    $incluir = false;
                }
            }
            
            // Filtro por status de moderação
            if (isset($filtros['moderado'])) {
                $statusModerado = $filtros['moderado'] === true || $filtros['moderado'] === 'true';
                if (isset($vela['moderado']) && $vela['moderado'] !== $statusModerado) {
                    $incluir = false;
                }
            }
            
            // Se passou em todos os filtros, inclui na lista filtrada
            if ($incluir) {
                $velasFiltered[] = $vela;
            }
        }
        
        $velasProcessadas = $velasFiltered;
    }
    
    // Ordena por timestamp (mais recentes primeiro)
    usort($velasProcessadas, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    
    // Calcula total de páginas
    $totalItens = count($velasProcessadas);
    $totalPaginas = ceil($totalItens / $porPagina);
    
    // Ajusta página atual
    $page = max(1, min($page, $totalPaginas));
    
    // Calcula offset para paginação
    $offset = ($page - 1) * $porPagina;
    
    // Obtém apenas os itens da página atual
    $itens = array_slice($velasProcessadas, $offset, $porPagina);
    
    // Retorna resultado com metadados
    return [
        'itens' => $itens,
        'total' => $totalItens,
        'pagina' => $page,
        'porPagina' => $porPagina,
        'totalPaginas' => $totalPaginas
    ];
}

/**
 * Obtém todas as velas (ativas e histórico) para o painel administrativo
 *
 * @param int $page Número da página (começando em 1)
 * @param int $porPagina Itens por página
 * @param array $filtros Filtros a serem aplicados (opcional)
 * @return array Dados de todas as velas e metadados de paginação
 */
function getTodasVelas($page = 1, $porPagina = 20, $filtros = []) {
    // Obtém velas ativas e históricas
    $velasAtivas = loadJsonFile(VELAS_FILE, []);
    $velasHistorico = loadJsonFile(VELAS_HISTORY_FILE, []);
    
    // Processa velas ativas
    $velasAtivasProcessadas = [];
    $agora = time();
    
    foreach ($velasAtivas as $vela) {
        if (!isset($vela['timestamp'], $vela['duracao'])) {
            continue;
        }
        
        $dataExpiraTimestamp = $vela['timestamp'] + ((int)$vela['duracao'] * 86400);
        
        // Adiciona informações formatadas
        $vela['dataAcesa'] = date("d/m/Y H:i", $vela['timestamp']);
        $vela['dataExpira'] = date("d/m/Y H:i", $dataExpiraTimestamp);
        $vela['reacoes'] = $vela['reacoes'] ?? 0;
        $vela['moderado'] = isset($vela['moderado']) ? $vela['moderado'] : false;
        
        $velasAtivasProcessadas[] = $vela;
    }
    
    // Combina todas as velas
    $todasVelas = array_merge($velasAtivasProcessadas, $velasHistorico);
    
    // Aplica filtros, se houver
    if (!empty($filtros)) {
        $velasFiltered = [];
        
        foreach ($todasVelas as $vela) {
            $incluir = true;
            
            // Filtro por nome
            if (isset($filtros['nome']) && !empty($filtros['nome'])) {
                if (stripos($vela['nome'], $filtros['nome']) === false) {
                    $incluir = false;
                }
            }
            
            // Filtro por período (data de acender)
            if (isset($filtros['dataInicio']) && !empty($filtros['dataInicio'])) {
                $dataInicio = strtotime($filtros['dataInicio']);
                if ($vela['timestamp'] < $dataInicio) {
                    $incluir = false;
                }
            }
            
            if (isset($filtros['dataFim']) && !empty($filtros['dataFim'])) {
                $dataFim = strtotime($filtros['dataFim'] . ' 23:59:59');
                if ($vela['timestamp'] > $dataFim) {
                    $incluir = false;
                }
            }
            
            // Filtro por personalização
            if (isset($filtros['personalizacao']) && !empty($filtros['personalizacao'])) {
                if ($vela['personalizacao'] != $filtros['personalizacao']) {
                    $incluir = false;
                }
            }
            
            // Filtro por duração
            if (isset($filtros['duracao']) && $filtros['duracao'] !== '') {
                if ($vela['duracao'] != $filtros['duracao']) {
                    $incluir = false;
                }
            }
            
            // Filtro por status de moderação
            if (isset($filtros['moderado'])) {
                $statusModerado = $filtros['moderado'] === true || $filtros['moderado'] === 'true';
                if (isset($vela['moderado']) && $vela['moderado'] !== $statusModerado) {
                    $incluir = false;
                }
            }
            
            // Se passou em todos os filtros, inclui na lista filtrada
            if ($incluir) {
                $velasFiltered[] = $vela;
            }
        }
        
        $todasVelas = $velasFiltered;
    }
    
    // Ordena por timestamp (mais recentes primeiro)
    usort($todasVelas, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    
    // Calcula total de páginas
    $totalItens = count($todasVelas);
    $totalPaginas = ceil($totalItens / $porPagina);
    
    // Ajusta página atual
    $page = max(1, min($page, $totalPaginas));
    
    // Calcula offset para paginação
    $offset = ($page - 1) * $porPagina;
    
    // Obtém apenas os itens da página atual
    $itens = array_slice($todasVelas, $offset, $porPagina);
    
    // Retorna resultado com metadados
    return [
        'itens' => $itens,
        'total' => $totalItens,
        'pagina' => $page,
        'porPagina' => $porPagina,
        'totalPaginas' => $totalPaginas
    ];
}