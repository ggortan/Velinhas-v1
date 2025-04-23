<?php
/**
 * API para carregar velas ativas
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/utils.php';

// Usar o sistema de cache para obter as velas
$velasAtivas = getVelasCache();

// Retorna as velas ativas como JSON
echo json_encode($velasAtivas);