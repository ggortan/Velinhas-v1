<?php
header('Content-Type: application/json');

$file = 'velas.json';

if (!file_exists($file)) {
    echo json_encode([]);
    exit;
}

$velas = json_decode(file_get_contents($file), true);
if (!is_array($velas)) {
    echo json_encode(["status" => "error", "message" => "Erro ao carregar as velas."]);
    exit;
}

$velasAtivas = [];
$agora = time();

foreach ($velas as $key => $vela) {
    if (!isset($vela['timestamp'], $vela['duracao']) || !is_numeric($vela['timestamp']) || !is_numeric($vela['duracao'])) {
        continue; // Ignora velas com dados inválidos
    }

    $dataExpiraTimestamp = $vela['timestamp'] + ((int)$vela['duracao'] * 86400);

    if ($dataExpiraTimestamp > $agora) {
        // Adiciona informações formatadas antes de enviar para o frontend
        $vela['dataAcesa'] = date("d/m/Y H:i", $vela['timestamp']);
        $vela['dataExpira'] = date("d/m/Y H:i", $dataExpiraTimestamp);
        
        // Garante que o campo 'reacoes' exista
        if (!isset($vela['reacoes'])) {
            $vela['reacoes'] = 0;
        }

        $velasAtivas[] = $vela;
    } else {
        // Remove velas expiradas do arquivo para evitar lixo acumulado
        unset($velas[$key]);
    }
}

// Atualiza o arquivo removendo velas expiradas
file_put_contents($file, json_encode(array_values($velas), JSON_PRETTY_PRINT));

echo json_encode($velasAtivas);
?>
