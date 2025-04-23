<?php
/**
 * API para registrar reações às velas
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/utils.php';

// Recebe os dados da requisição
$data = json_decode(file_get_contents('php://input'), true);
$idVela = $data['id'] ?? null;
$csrfToken = $data['csrf_token'] ?? null;

// if (!verificarCsrfToken($csrfToken)) {
//     echo json_encode([
//         "status" => "error", 
//         "message" => "Erro de validação do token de segurança."
//     ]);
//     exit;
// }

// Verifica se o ID da vela foi fornecido
if (!$idVela) {
    echo json_encode([
        "status" => "error", 
        "message" => "Vela não encontrada."
    ]);
    exit;
}

// Recupera o IP do usuário
//$ip = $_SERVER['REMOTE_ADDR'];

// Verifica se o usuário está banido
//$tempoBan = verificarBanimento($ip, REACTION_SPAM_FILE);
//if ($tempoBan !== false) {
//    echo json_encode([
//        "status" => "error", 
//        "message" => "Você foi banido por excesso de reações. Tente novamente em {$tempoBan} segundos."
//    ]);
//    exit;
//}

// Carrega o arquivo de controle de spam
//$spamData = loadJsonFile(REACTION_SPAM_FILE);

// Verifica o histórico de reações do IP
//$timestamp = time();
//if (isset($spamData[$ip])) {
//    // Filtra reações anteriores dentro da janela de tempo
//    $recentReactions = array_filter($spamData[$ip]['reactions'], function ($reactionTime) use ($timestamp) {
//        return ($timestamp - $reactionTime) <= REACTION_WINDOW;
//    });
//
//    // Se o número de reações exceder o limite, bloqueia o IP
//    if (count($recentReactions) >= MAX_REACTIONS) {
//        $spamData[$ip]['ban_until'] = $timestamp + REACTION_BAN_DURATION;
//        saveJsonFile(REACTION_SPAM_FILE, $spamData);
//        
//        echo json_encode([
//            "status" => "error", 
//            "message" => "Você fez muitas reações em pouco tempo! Você foi temporariamente banido."
//        ]);
//        exit;
//    }
//
//    // Atualiza o histórico de reações recentes
//    $spamData[$ip]['reactions'] = array_merge($recentReactions, [$timestamp]);
//} else {
//    // Caso o IP não tenha registros anteriores, inicializa com a reação atual
//    $spamData[$ip] = [
//        'reactions' => [$timestamp],
//        'ban_until' => 0  // Sem banimento por padrão
//    ];
//}

// Verifica se o usuário já reagiu a esta vela (controle por cookie)
$reacaoCookie = isset($_COOKIE['reacao_velas']) ? json_decode($_COOKIE['reacao_velas'], true) : [];

if (is_array($reacaoCookie) && in_array($idVela, $reacaoCookie)) {
    echo json_encode([
        "status" => "error", 
        "message" => "Você já reagiu a esta vela."
    ]);
    exit;
}

// Carrega as velas e encontra a vela solicitada
$velas = loadJsonFile(VELAS_FILE);
$velaEncontrada = false;

foreach ($velas as &$vela) {
    if ($vela['id'] == $idVela) {
        $vela['reacoes'] = isset($vela['reacoes']) ? $vela['reacoes'] + 1 : 1;
        $velaEncontrada = true;
        break;
    }
}

if (!$velaEncontrada) {
    echo json_encode([
        "status" => "error", 
        "message" => "Vela não encontrada."
    ]);
    exit;
}

// Salva as atualizações
saveJsonFile(VELAS_FILE, $velas);
saveJsonFile(REACTION_SPAM_FILE, $spamData);

// Invalidar o cache
if (file_exists(CACHE_FILE)) {
    unlink(CACHE_FILE);
}

// Atualiza o cookie do usuário
$reacaoCookie[] = $idVela;
setcookie('reacao_velas', json_encode($reacaoCookie), time() + (365 * 24 * 60 * 60), "/");

echo json_encode([
    "status" => "success", 
    "message" => "Reação adicionada com sucesso!", 
    "reacoes" => $vela['reacoes']
]);