<?php
session_start();

$file = 'velas.json';
$velas = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

$data = json_decode(file_get_contents('php://input'), true);
$idVela = $data['id'] ?? null;

if (!$idVela) {
    echo json_encode(["status" => "error", "message" => "Vela não encontrada."]);
    exit;
}

// **Configuração Anti-Flood**
$maxReactions = 5;  // Máximo de reações permitidas em 5 segundos
$reactionWindow = 5;  // Janela de tempo em segundos
$banDuration = 60; // Duração do bloqueio em segundos (1 minutos)

// Recupera o IP do usuário
$ip = $_SERVER['REMOTE_ADDR'];

// Lê o arquivo de controle de spam
$spamFile = 'reaction_spam.json';
$spamData = file_exists($spamFile) ? json_decode(file_get_contents($spamFile), true) : [];

// Verifica se o usuário foi banido
if (isset($spamData[$ip]['ban_until']) && time() < $spamData[$ip]['ban_until']) {
    $banRemaining = $spamData[$ip]['ban_until'] - time();
    echo json_encode(["status" => "error", "alert" => "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Você foi banido por excesso de reações. Tente novamente em $banRemaining segundos.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>"]);
    exit;
}

// **Verifica o histórico de reações do IP**
$timestamp = time();
if (isset($spamData[$ip])) {
    // Filtra reações anteriores dentro da janela de 10 segundos
    $recentReactions = array_filter($spamData[$ip]['reactions'], function ($reactionTime) use ($timestamp, $reactionWindow) {
        return ($timestamp - $reactionTime) <= $reactionWindow;
    });

    // Se o número de reações exceder o limite, bloqueia o IP
    if (count($recentReactions) >= $maxReactions) {
        $spamData[$ip]['ban_until'] = $timestamp + $banDuration;  // Define o tempo de bloqueio
        file_put_contents($spamFile, json_encode($spamData, JSON_PRETTY_PRINT));
        echo json_encode(["status" => "error", "alert" => "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Você fez muitas reações em pouco tempo! Você foi temporariamente banido. Tente novamente em 10 minutos.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>"]);
        exit;
    }

    // Atualiza o histórico de reações recentes
    $spamData[$ip]['reactions'] = array_merge($recentReactions, [$timestamp]);
} else {
    // Caso o IP não tenha registros anteriores, inicializa com a reação atual
    $spamData[$ip] = [
        'reactions' => [$timestamp],
        'ban_until' => 0  // Sem banimento por padrão
    ];
}

// **Verifica se o usuário já reagiu a esta vela (controle por cookie)**
$reacaoCookie = isset($_COOKIE['reacao_velas']) ? json_decode($_COOKIE['reacao_velas'], true) : [];

if (in_array($idVela, $reacaoCookie)) {
    echo json_encode(["status" => "error", "message" => "Você já louvou esta vela."]);
    exit;
}

// **Encontra a vela no JSON e incrementa o contador**
foreach ($velas as &$vela) {
    if ($vela['id'] == $idVela) {
        $vela['reacoes'] += 1;
        break;
    }
}

// **Salva a atualização no JSON**
file_put_contents($file, json_encode($velas, JSON_PRETTY_PRINT));

// **Atualiza o cookie do usuário**
$reacaoCookie[] = $idVela;
setcookie('reacao_velas', json_encode($reacaoCookie), time() + (365 * 24 * 60 * 60), "/"); // Expira em 1 ano

// **Salva o histórico de reações atualizado**
file_put_contents($spamFile, json_encode($spamData, JSON_PRETTY_PRINT));

echo json_encode(["status" => "success", "message" => "Amém adicionado com sucesso!", "reacoes" => $vela['reacoes']]);
?>
