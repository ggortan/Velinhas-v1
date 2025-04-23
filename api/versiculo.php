<?php
/**
 * Versículo Diário - Bíblia Digital API
 * 
 * Script para exibir um versículo bíblico com atualização periódica
 * Utilizando a API https://www.abibliadigital.com.br/ com autenticação por token
 */
 
require_once __DIR__ . '/../config/config.php';

// Configurações básicas
$arquivo_cache = DATA_PATH . '/versiculo_cache.json';
$arquivo_log = DATA_PATH . '/versiculo_erro.log';


// Configuração do intervalo de atualização (em segundos)
$intervalo_atualizacao = 3600; // 1 hora por padrão

// Configurações da API
$api_url = API_BIBLIA;
$api_token = $API_KEYS['biblia_api'];

/**
 * Função para registrar erros em log
 */
function registrarErro($mensagem) {
    global $arquivo_log, $intervalo_atualizacao;
    $data = date('Y-m-d H:i:s');
    $intervalo_formatado = gmdate("H:i:s", $intervalo_atualizacao);
    error_log("[$data] $mensagem (Intervalo de atualização: $intervalo_formatado)\n", 3, $arquivo_log);
}

/**
 * Função para obter o versículo do dia
 */
function obterVersiculoDiario() {
    global $arquivo_cache, $api_url, $intervalo_atualizacao;
    $agora = time();
    
    // Verifica se já existe cache válido
    if (file_exists($arquivo_cache)) {
        $conteudo_cache = file_get_contents($arquivo_cache);
        if ($conteudo_cache !== false) {
            $cache = json_decode($conteudo_cache, true);
            
            // Verifica se o cache ainda é válido baseado no intervalo configurado
            if (isset($cache['timestamp']) && 
                ($agora - $cache['timestamp']) < $intervalo_atualizacao && 
                isset($cache['verse'])) {
                // O cache ainda é válido
                return $cache['verse'];
            }
        }
    }
    
    // Se o cache está expirado ou não existe, buscamos um novo versículo
    $versiculo = buscarVersiculoAPI();
    
    // Salva no cache com timestamp
    $dados_cache = array(
        'timestamp' => $agora,
        'verse' => $versiculo
    );
    
    if (file_put_contents($arquivo_cache, json_encode($dados_cache)) === false) {
        registrarErro("Não foi possível salvar o cache: $arquivo_cache");
    }
    
    return $versiculo;
}

/**
 * Função para buscar um versículo aleatório da API
 */
function buscarVersiculoAPI() {
    global $api_url, $api_token;
    
    try {
        // Configura o contexto da requisição com o token de autenticação
        $opcoes = array(
            'http' => array(
                'method' => 'GET',
                'header' => "Accept: application/json\r\n" .
                            "Authorization: Bearer $api_token\r\n"
            )
        );
        $contexto = stream_context_create($opcoes);
        
        // Realiza a requisição à API
        $resposta = file_get_contents($api_url, false, $contexto);
        
        if ($resposta === false) {
            registrarErro("Falha ao acessar a API: $api_url");
            throw new Exception("Falha ao acessar a API");
        }
        
        // Decodifica a resposta JSON
        $dados = json_decode($resposta, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            registrarErro("Erro ao decodificar JSON: " . json_last_error_msg());
            throw new Exception("Erro ao decodificar resposta da API");
        }
        
        // Formata o resultado da API para o formato utilizado pelo script
        $versiculo = array(
            'book' => array('name' => isset($dados['book']['name']) ? $dados['book']['name'] : 'Desconhecido'),
            'chapter' => isset($dados['chapter']) ? $dados['chapter'] : 1,
            'number' => isset($dados['number']) ? $dados['number'] : 1,
            'text' => isset($dados['text']) ? $dados['text'] : 'Versículo não disponível.'
        );
        
        return $versiculo;
        
    } catch (Exception $e) {
        registrarErro("Exceção ao buscar versículo da API: " . $e->getMessage());
        throw $e;
    }
}

// ----- EXECUÇÃO PRINCIPAL -----

// Obtém o versículo para exibição
try {
    $versiculo = obterVersiculoDiario();
    
    // Prepara os dados para exibição
    $textoVersiculo = $versiculo['text'];
    $nomeDoLivro = $versiculo['book']['name'];
    $capitulo = $versiculo['chapter'];
    $numero = $versiculo['number'];
    
    $referenciaVersiculo = "$nomeDoLivro $capitulo:$numero";
} catch (Exception $e) {
    // Em caso de erro inesperado, exibe mensagem de erro
    $textoVersiculo = "Não foi possível carregar o versículo. Por favor, tente novamente mais tarde.";
    $referenciaVersiculo = "";
    
    registrarErro("Exceção não tratada: " . $e->getMessage());
}

?>
<!-- Componente de Versículo Diário -->
<div class="versiculo-diario">
    <div class="versiculo-texto">
        <i class="versiculo-icone-esquerda"></i>
        <?php echo htmlspecialchars($textoVersiculo); ?>
        <i class="versiculo-icone-direita"></i>
    </div>
    <div class="versiculo-referencia">
        <?php echo htmlspecialchars($referenciaVersiculo); ?>
    </div>
</div>
