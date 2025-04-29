<?php
/**
 * API para salvar novas velas
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/utils.php';

// Recebe os dados da requisição
$data = json_decode(file_get_contents('php://input'), true);
$idVela = $data['id'] ?? null;
$csrfToken = $data['csrf_token'] ?? null;

// Temporariamente desabilite a verificação CSRF para depuração
// if (!verificarCsrfToken($csrfToken)) {
//     echo json_encode([
//         "status" => "error", 
//         "message" => "Erro de validação do token de segurança."
//     ]);
//     exit;
// }

// Obtém o IP do usuário
$ip = $_SERVER['REMOTE_ADDR'];
$timestamp = time();
$sessionKey = "last_vela_time";

// Verifica se o IP está bloqueado
$tempoBan = verificarBanimento($ip);
if ($tempoBan !== false) {
    echo json_encode([
        "status" => "error", 
        "alert" => gerarAlerta("Você foi bloqueado por excesso de envios. Tente novamente em {$tempoBan} segundos.", "danger")
    ]);
    exit;
}

// Verifica se há cookies ou sessão de spam
if (isset($_COOKIE['vela_spam']) || (isset($_SESSION[$sessionKey]) && ($timestamp - $_SESSION[$sessionKey] < SPAM_COOLDOWN))) {
    echo json_encode([
        "status" => "error", 
        "alert" => gerarAlerta("Aguarde antes de criar outra vela.", "warning")
    ]);
    exit;
}

// Verifica o IP no histórico de spam
$spamData = loadJsonFile(SPAM_FILE);
if (isset($spamData[$ip])) {
    $enviosRecentes = array_filter($spamData[$ip], function ($time) use ($timestamp) {
        return ($timestamp - $time) <= TEMPO_LIMITE;
    });

    if (count($enviosRecentes) >= MAX_VELAS) {
        $banList = loadJsonFile(BAN_FILE);
        $banList[$ip] = $timestamp + TEMPO_BAN;
        saveJsonFile(BAN_FILE, $banList);
        
        echo json_encode([
            "status" => "error", 
            "alert" => gerarAlerta("Você criou velas rápido demais! Espere 10 minutos antes de tentar novamente.", "danger")
        ]);
        exit;
    }
    
    $spamData[$ip] = $enviosRecentes;
}

// Validação dos dados de entrada
$errors = [];
$tiposValidos = ['vela0', 'vela1', 'vela2', 'vela3', 'vela4'];

// Valida o nome
if (!isset($data['nome']) || empty($data['nome'])) {
    $errors[] = "O nome da vela é obrigatório";
} elseif (mb_strlen($data['nome'], 'UTF-8') > MAX_NOME_LENGTH) {
    $errors[] = "O nome da vela deve ter no máximo " . MAX_NOME_LENGTH . " caracteres";
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
    'timestamp' => $timestamp,
    'reacoes' => 0
];

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