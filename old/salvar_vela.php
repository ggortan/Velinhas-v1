<?php
session_start();

$file = 'velas.json';
$fileStats = 'stats.json';
$spamFile = 'spam.json';  // Controle de spam por tempo
$banFile = 'banlist.json'; // Lista de IPs bloqueados
$spamCooldown = 30;  // Tempo mínimo entre criações (segundos)
$maxVelas = 5;       // Máximo de velas permitidas no intervalo de tempo
$tempoLimite = 20;   // Janela de tempo para verificação de spam (segundos)
$tempoBan = 600;     // Tempo de bloqueio se ultrapassar o limite (10 minutos)


// Lê arquivos JSON existentes
$velas = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
$spamData = file_exists($spamFile) ? json_decode(file_get_contents($spamFile), true) : [];
$banList = file_exists($banFile) ? json_decode(file_get_contents($banFile), true) : [];
$stats = file_exists($fileStats) ? json_decode(file_get_contents($fileStats), true) : ['ultimo_id' => 0];

$data = json_decode(file_get_contents('php://input'), true);
$ip = $_SERVER['REMOTE_ADDR']; // Captura o IP do usuário
$timestamp = time();
$sessionKey = "last_vela_time";

// **1 Verifica se o IP está bloqueado**
if (isset($banList[$ip]) && $timestamp < $banList[$ip]) {
    $tempoRestante = $banList[$ip] - $timestamp;
    echo json_encode(["status" => "error", "alert" => "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Você foi bloqueado por excesso de envios. Tente novamente em $tempoRestante segundos.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>"]);
    exit;
}

// **2 Verifica se há cookies ou sessão de spam**
if (isset($_COOKIE['vela_spam']) || (isset($_SESSION[$sessionKey]) && ($timestamp - $_SESSION[$sessionKey] < $spamCooldown))) {
    echo json_encode(["status" => "error", "alert" => "<div class='alert alert-warning alert-dismissible fade show' role='alert'>Aguarde antes de criar outra vela.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>"]);
    exit;
}

// **3 Verifica o IP no histórico de spam**
if (isset($spamData[$ip])) {
    $enviosRecentes = array_filter($spamData[$ip], function ($time) use ($timestamp, $tempoLimite) {
        return ($timestamp - $time) <= $tempoLimite;
    });

    if (count($enviosRecentes) >= $maxVelas) {
        $banList[$ip] = $timestamp + $tempoBan;
        file_put_contents($banFile, json_encode($banList, JSON_PRETTY_PRINT));
        echo json_encode(["status" => "error", "alert" => "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Você criou velas rápido demais! Espere 10 minutos antes de tentar novamente.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>"]);
        exit;
    }
    $spamData[$ip] = $enviosRecentes;
}

// **4 Verifica o limite de caracteres no nome e a duração**
$tiposValidos = ['vela0', 'vela1', 'vela2', 'vela3', 'vela4'];
if (isset($data['nome']) && mb_strlen($data['nome'], 'UTF-8') > 40) {
    echo json_encode(["status" => "error", "alert" => "<div class='alert alert-danger alert-dismissible fade show' role='alert'>O nome da vela deve ter no máximo 40 caracteres.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>"]);
    exit;
}
if (!isset($data['duracao']) || !in_array($data['duracao'], ['1', '7'])) {
    echo json_encode(["status" => "error", "alert" => "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Erro ao criar vela.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>"]);
    exit;
}
if (!isset($data['personalizacao']) || !in_array($data['personalizacao'], $tiposValidos)) {
    echo json_encode(["status" => "error", "alert" => "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Tipo de vela não existe.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>"]);
    exit;
}
if (isset($data['reacoes'])) {
    echo json_encode(["status" => "error", "alert" => "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Erro ao acender vela<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>"]);
    exit;
}

// **5 Gera um novo ID para a vela**
// **5.1 Lê o último ID do stats.json**
$ultimoId = isset($stats['ultimo_id']) ? $stats['ultimo_id'] : 0;
$novoId = $ultimoId + 1;

// **5.2 Adiciona o ID gerado à nova vela**
$data['id'] = $novoId;
$data['timestamp'] = $timestamp;
$data['personalizacao'] = $data['personalizacao'] ?? '#FFEA70';
$data['reacoes'] = 0; // Inicia com 0 reações


// **5.3 Salva a vela no JSON**
$velas[] = $data;
file_put_contents($file, json_encode($velas, JSON_PRETTY_PRINT));

// **5.4 Atualiza o último ID no stats.json**
$stats['ultimo_id'] = $novoId;
file_put_contents($fileStats, json_encode($stats, JSON_PRETTY_PRINT));


// **6 Atualiza o controle de spam**
$spamData[$ip][] = $timestamp;
file_put_contents($spamFile, json_encode($spamData, JSON_PRETTY_PRINT));
file_put_contents($file, json_encode($velas, JSON_PRETTY_PRINT));
file_put_contents($fileStats, json_encode($stats, JSON_PRETTY_PRINT));

// **7 Define cookie e sessão de proteção**
$_SESSION[$sessionKey] = $timestamp;
setcookie("vela_spam", "1", time() + $spamCooldown, "/");

echo json_encode(["status" => "success", "alert" => "<div class='alert alert-success alert-dismissible fade show' role='alert'>Vela salva com sucesso!<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>"]);
?>
