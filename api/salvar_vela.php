<?php
/**
 * API para salvar novas velas (com suporte a mensagens)
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/utils.php';

// Define o tamanho máximo da mensagem
define('MAX_MENSAGEM_LENGTH', 200);

// Recebe os dados da requisição
$data = json_decode(file_get_contents('php://input'), true);
$idVela = $data['id'] ?? null;
$csrfToken = $data['csrf_token'] ?? null;

// Verifica o IP e as regras de spam (código existente)
// ...

// Validação dos dados de entrada
$errors = [];
$tiposValidos = ['vela0', 'vela1', 'vela2', 'vela3', 'vela4'];

// Valida o nome
if (!isset($data['nome']) || empty($data['nome'])) {
    $errors[] = "O nome da vela é obrigatório";
} elseif (mb_strlen($data['nome'], 'UTF-8') > MAX_NOME_LENGTH) {
    $errors[] = "O nome da vela deve ter no máximo " . MAX_NOME_LENGTH . " caracteres";
}

// Valida a mensagem (se fornecida)
if (isset($data['mensagem']) && !empty($data['mensagem'])) {
    // Verifica o tamanho da mensagem
    if (mb_strlen($data['mensagem'], 'UTF-8') > MAX_MENSAGEM_LENGTH) {
        $errors[] = "A mensagem deve ter no máximo " . MAX_MENSAGEM_LENGTH . " caracteres";
    }
    
    // Sanitiza a mensagem removendo tags HTML e caracteres perigosos
    $data['mensagem'] = htmlspecialchars(strip_tags($data['mensagem']), ENT_QUOTES, 'UTF-8');
}

// Valida a duração
if (!isset($data['duracao']) || !in_array($data['duracao'], ['1', '7'])) {
    $errors[] = "Duração inválida";
}

// Valida a personalização
if (!isset($data['personalizacao'])) {
    $errors[] = "Personalização obrigatória";
} elseif (!in_array($data['personalizacao'], $tiposValidos) && !preg_match('/^#[0-9A-F]{6}$/i', $data['personalizacao'])) {
    $errors[] = "Tipo de vela inválido";
}

// Verifica manipulação do campo reações
if (isset($data['reacoes'])) {
    $errors[] = "Campo inválido detectado";
}

// Se houver erros, retorna
if (!empty($errors)) {
    echo json_encode([
        "status" => "error", 
        "alert" => gerarAlerta(implode("<br>", $errors), "danger")
    ]);
    exit;
}

// Gera um novo ID para a vela
$stats = loadJsonFile(STATS_FILE, ['ultimo_id' => 0]);
$novoId = $stats['ultimo_id'] + 1;

// Prepara a nova vela
$novaVela = [
    'id' => $novoId,
    'nome' => $data['nome'],
    'duracao' => $data['duracao'],
    'personalizacao' => $data['personalizacao'],
    'timestamp' => time()
];

// Adiciona mensagem apenas se estiver presente
if (isset($data['mensagem']) && !empty($data['mensagem'])) {
    $novaVela['mensagem'] = $data['mensagem'];
}

// Inicializa o contador de reações em 0
$novaVela['reacoes'] = 0;

// Carrega as velas existentes e adiciona a nova
$velas = loadJsonFile(VELAS_FILE);
$velas[] = $novaVela;

// Atualiza o arquivo de stats
$stats['ultimo_id'] = $novoId;

// Atualiza o controle de spam
if (!isset($spamData[$ip])) {
    $spamData[$ip] = [];
}
$spamData[$ip][] = $timestamp;

// Salva todos os arquivos
saveJsonFile(VELAS_FILE, $velas);
saveJsonFile(STATS_FILE, $stats);
saveJsonFile(SPAM_FILE, $spamData);

// Certifica-se de que o cache seja removido
if (file_exists(CACHE_FILE)) {
    @unlink(CACHE_FILE);
}

// Também garante invalidação do cache via headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Define cookie e sessão de proteção
$_SESSION[$sessionKey] = $timestamp;
setcookie("vela_spam", "1", time() + SPAM_COOLDOWN, "/");

echo json_encode([
    "status" => "success", 
    "alert" => gerarAlerta("Vela acesa com sucesso!", "success"),
    "vela_id" => $novoId
]);