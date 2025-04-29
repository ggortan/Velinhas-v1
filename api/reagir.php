<?php
/**
 * API para registrar reações às velas
 * Versão corrigida para garantir o funcionamento consistente
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/utils.php';

// Recebe os dados da requisição
$data = json_decode(file_get_contents('php://input'), true);
$idVela = $data['id'] ?? null;
$csrfToken = $data['csrf_token'] ?? null;

// Verifica se o ID da vela foi fornecido
if (!$idVela) {
    echo json_encode([
        "status" => "error", 
        "message" => "Vela não encontrada."
    ]);
    exit;
}

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
$reacaoAtual = 0;

foreach ($velas as &$vela) {
    if ($vela['id'] == $idVela) {
        // Garante que reacoes seja um número válido
        if (!isset($vela['reacoes']) || !is_numeric($vela['reacoes'])) {
            $vela['reacoes'] = 0;
        }
        
        // Incrementa o contador
        $vela['reacoes'] = (int)$vela['reacoes'] + 1;
        $reacaoAtual = $vela['reacoes'];
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

// Salva as atualizações no arquivo de velas
if (!saveJsonFile(VELAS_FILE, $velas)) {
    error_log("Erro ao salvar arquivo de velas após reação. ID: $idVela");
    echo json_encode([
        "status" => "error", 
        "message" => "Erro ao salvar sua reação. Tente novamente."
    ]);
    exit;
}

// Invalidar o cache para garantir que as alterações sejam visíveis
if (file_exists(CACHE_FILE)) {
    if (!unlink(CACHE_FILE)) {
        error_log("Erro ao excluir arquivo de cache após reação");
    }
}

// Atualiza o cookie do usuário
$reacaoCookie[] = $idVela;
setcookie('reacao_velas', json_encode($reacaoCookie), time() + (365 * 24 * 60 * 60), "/");

echo json_encode([
    "status" => "success", 
    "message" => "Reação adicionada com sucesso!", 
    "reacoes" => $reacaoAtual
]);