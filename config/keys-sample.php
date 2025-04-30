<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    exit('Acesso direto não permitido.');
}

$API_KEYS = [
    'biblia_api' => '',
    'openai' => '',
];