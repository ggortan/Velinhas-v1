<?php
/**
 * Velinhas - Página de Vela Individual (com suporte a velas expiradas)
 * Versão: 3.7.0
 */
// Iniciar a sessão se ainda não foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/utils.php';

// Capturar o ID da vela a partir da URL
$idVela = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($idVela <= 0) {
    // Redireciona para a página inicial se não houver ID válido
    header('Location: /');
    exit;
}

// Buscar a vela específica
$velasAtivas = getVelasCache();
$velaAtual = null;
$velaExpirada = false;

// Primeiro procura nas velas ativas
foreach ($velasAtivas as $vela) {
    if ($vela['id'] == $idVela) {
        $velaAtual = $vela;
        break;
    }
}

// Se não encontrar nas velas ativas, procura no histórico
if ($velaAtual === null) {
    // Carrega o histórico de velas
    $historicoVelas = loadJsonFile(VELAS_HISTORY_FILE, []);
    
    foreach ($historicoVelas as $vela) {
        if ($vela['id'] == $idVela) {
            $velaAtual = $vela;
            $velaExpirada = true;
            break;
        }
    }
}

// Se a vela não for encontrada nem nas ativas nem no histórico, redireciona para a página inicial
if ($velaAtual === null) {
    header('Location: /?erro=vela_nao_encontrada');
    exit;
}

// Define a página ativa para o menu
$activePage = 'vela';

// Define o título e descrição da página com base na vela
$pageTitle = "Vela para " . htmlspecialchars($velaAtual['nome']) . " - Velinhas 🕯";
$pageDescription = "Confira a vela acesa para " . htmlspecialchars($velaAtual['nome']) . " e deixe sua oração. Compartilhe esta vela com amigos e familiares.";

// Conteúdo extra para o head - Meta tags para compartilhamento
$extraHeadContent = '
<meta property="og:type" content="website">
<meta property="og:url" content="https://velinhas.com.br/vela.php?id=' . $idVela . '">
<meta property="og:title" content="' . htmlspecialchars($pageTitle) . '">
<meta property="og:description" content="' . htmlspecialchars($pageDescription) . '">
<meta property="og:image" content="https://velinhas.com.br/assets/img/' . ($velaAtual['personalizacao'] == 'vela0' || $velaAtual['personalizacao'] == 'vela1' || $velaAtual['personalizacao'] == 'vela2' || $velaAtual['personalizacao'] == 'vela3' ? $velaAtual['personalizacao'] . '.png' : 'vela0.png') . '">
<meta name="twitter:card" content="summary_large_image">
';

// Estilos adicionais para velas expiradas
if ($velaExpirada) {
    $extraHeadContent .= '
    <style>
        .vela-expired {
            position: relative;
        }
        
        .vela-expired::before {
            content: "Vela Apagada";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            z-index: 10;
        }
        
        .vela-grande.expired {
            filter: grayscale(80%);
            opacity: 0.8;
        }
        
        .vela-chama-grande.expired {
            display: none;
        }
        
        .expired-info {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            margin-top: 15px;
            border-radius: 5px;
        }
        
        .new-vela-prompt {
            background-color: #cce5ff;
            border: 1px solid #b8daff;
            color: #004085;
            padding: 10px;
            margin-top: 15px;
            border-radius: 5px;
        }
    </style>
    ';
}

// Inclui o componente head
require_once __DIR__ . '/includes/head.php';
?>
<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
    <!-- Esta tag será substituída pelo conteúdo de includes/head.php -->
</head>
<body>
    <?php require_once __DIR__ . '/includes/header.php'; ?>
    
    <div class="container mb-4" id="main-content">
        <input type="hidden" id="global_csrf_token" name="csrf_token" value="<?php echo $csrfToken; ?>">
        
        <div id="alert-container"></div>
        
        <div class="row mt-4 d-flex justify-content-center">
            <div class="col-md-8 mb-2 d-flex align-items-center justify-content-between">
                <a href="/" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <a href="#" class="btn btn-acender float-end" data-bs-toggle="modal" data-bs-target="#velaModal">
                    <i class="bi bi-fire"></i> Acender Nova Vela
                </a>
            </div>
            <div class="col-md-8">
                <div class="card capela-card">
                    <div class="card-header capela-card-header">
                        <h1 class="capela-card-title">Vela para <?php echo htmlspecialchars($velaAtual['nome']); ?></h1>
                    </div>
                    
                    <div class="card-body capela-card-body text-center">
                        <div class="vela-page-container <?php echo $velaExpirada ? 'vela-expired' : ''; ?>">
                            <!-- Container da vela grande -->
                            <div class="vela-grande-container">
                                <?php
                                // Define classe ou estilo com base na personalização da vela
                                $velaStyle = '';
                                $velaBorder = '';
                                
                                if ($velaAtual['personalizacao']) {
                                    if ($velaAtual['personalizacao'] == 'vela0' || $velaAtual['personalizacao'] == 'vela1' || 
                                        $velaAtual['personalizacao'] == 'vela2' || $velaAtual['personalizacao'] == 'vela3') {
                                        // Vela com imagem de fundo
                                        $velaStyle = "background-image: url('/assets/img/{$velaAtual['personalizacao']}.png');";
                                    } elseif (strpos($velaAtual['personalizacao'], '#') === 0) {
                                        // Vela com cor personalizada
                                        $velaStyle = "background-color: {$velaAtual['personalizacao']};";
                                        $colorDarker = shadeColor($velaAtual['personalizacao'], -20);
                                        $velaBorder = "border: 3px solid $colorDarker;";
                                    }
                                }
                                ?>
                                
                                <div class="vela-grande <?php echo $velaExpirada ? 'expired' : ''; ?>" style="<?php echo $velaStyle . $velaBorder; ?>">
                                    <div class="vela-chama-grande <?php echo $velaExpirada ? 'expired' : ''; ?>"></div>
                                </div>
                                
                                <div class="info-vela-grande">
                                    <h2><?php echo htmlspecialchars($velaAtual['nome']); ?></h2>
                                    <p>Acesa em: <?php echo $velaAtual['dataAcesa']; ?></p>
                                    <?php if ($velaExpirada): ?>
                                        <p>Apagou em: <?php echo $velaAtual['dataExpiracaoFormatada']; ?></p>
                                        <div class="expired-info">
                                            Esta vela já se apagou. Ficou acesa por <?php echo $velaAtual['duracao']; ?> dia(s).
                                        </div>
                                        <div class="new-vela-prompt d-flex align-items-center justify-content-between">
                                            🕯 Deseja acender uma nova vela? 
                                            <button class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#velaModal">
                                                <i class="bi bi-fire"></i> Acender Nova Vela
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <p>Apaga em: <?php echo $velaAtual['dataExpira']; ?></p>
                                        
                                        <!-- Botão de reação (apenas para velas ativas) -->
                                        <button class="btn btn-lg reagir-btn mt-3" id="btn-reacao-principal" data-id="<?php echo $velaAtual['id']; ?>" data-csrf="<?php echo $csrfToken; ?>">
                                            🙏 <span class="reacao-count"><?php echo $velaAtual['reacoes'] ?? 0; ?></span>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Botões de compartilhamento -->
                                    <div class="compartilhar-container mt-4">
                                        <h5>Compartilhar esta vela:</h5>
                                        <div class="d-flex justify-content-center gap-2 mt-3">
                                            <a href="https://wa.me/?text=<?php echo urlencode('Veja a vela que acendi para ' . $velaAtual['nome'] . ': ' . 'https://velinhas.com.br/vela.php?id=' . $idVela); ?>" target="_blank" class="btn btn-success">
                                                <i class="bi bi-whatsapp"></i> 
                                            </a>
                                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://velinhas.com.br/vela.php?id=' . $idVela); ?>" target="_blank" class="btn btn-primary">
                                                <i class="bi bi-facebook"></i> 
                                            </a>
                                            <button class="btn btn-secondary" id="copiarLink">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer capela-card-footer">
                        <?php include_once BASE_PATH . '/api/pix.php'; ?>
                    </div>
                </div>
                
                <!-- Seção para Versículo -->
                <div class="mt-4">
                    <?php include BASE_PATH . '/api/versiculo.php'; ?>
                </div>
                
                <!-- Banners abaixo da vela -->
                <div class="mt-4">
                    <?php include_once 'api/banners.php'; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para acender vela (igual ao da página inicial) -->
    <div class="modal fade" id="velaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Acender uma Vela</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="velaForm">
                        <!-- Token CSRF oculto -->
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label">Para quem será a vela?</label>
                            <input type="text" id="nome" class="form-control" maxlength="40" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Duração:</label>
                            <select id="duracao" class="form-select">
                                <option value="1">1 Dia</option>
                                <option value="7">7 Dias</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Personalizar Vela:</label>
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipoVela" id="imgRadio0" value="vela0" checked>
                                    <label class="form-check-label" for="imgRadio0">
                                        <img src="/assets/img/vela0.png" alt="Vela Branca" width="40" class="img-fluid rounded border border-0 img-radio">
                                    </label>
                                    <div class="d-block">
                                        <label for="imgRadio0">Vela Branca</label>
                                    </div>
                                </div>
                                
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipoVela" id="imgRadio1" value="vela1">
                                    <label class="form-check-label" for="imgRadio1">
                                        <img src="/assets/img/vela1.png" alt="Vela 1" width="40" class="img-fluid rounded border border-0 img-radio">
                                    </label>
                                    <div class="d-block">
                                        <label for="imgRadio1">Vela 1</label>
                                    </div>
                                </div>
                                
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipoVela" id="imgRadio2" value="vela2">
                                    <label class="form-check-label" for="imgRadio2">
                                        <img src="/assets/img/vela2.png" alt="Vela 2" width="40" class="img-fluid rounded border border-0 img-radio">
                                    </label>
                                    <div class="d-block">
                                        <label for="imgRadio2">Vela 2</label>
                                    </div>
                                </div>
                                
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipoVela" id="imgRadio3" value="vela3">
                                    <label class="form-check-label" for="imgRadio3">
                                        <img src="/assets/img/vela3.png" alt="Vela 3" width="40" class="img-fluid rounded border border-0 img-radio">
                                        <span class="badge bg-success" style="font-size: 0.6rem; padding: 0.2em 0.4em;">Novidade!</span>
                                    </label>
                                    <div class="d-block">
                                        <label for="imgRadio3">Vela 3</label>
                                    </div>
                                </div>
                                
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipoVela" id="corRadio" value="cor">
                                    <label class="form-check-label" for="corRadio">
                                        <span class="badge text-bg-secondary">Cor Personalizada</span>
                                    </label>
                                    <div id="colorPickerWrapper" class="d-none mt-2">
                                        <input type="color" id="corVela" class="form-control form-control-color me-3" value="#FFE4B8">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" id="acenderBtn">Acender 🕯</button>
                    </form>
                </div>
                
                <div class="modal-footer">
                    <div class="alert alert-vela" role="alert">
                        <b>ATENÇÃO:</b> Após acender uma vela não é possível apagar. Cuidado para não se queimar
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
    
    <!-- Script específico para página da vela -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa o formulário
        formManager.inicializar();
        
        // Função para copiar o link da vela para a área de transferência
        const btnCopiarLink = document.getElementById('copiarLink');
        if (btnCopiarLink) {
            btnCopiarLink.addEventListener('click', function() {
                const url = window.location.href;
                
                // Usa a API moderna de clipboard
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url)
                        .then(() => {
                            // Muda temporariamente o texto do botão
                            const originalText = btnCopiarLink.innerHTML;
                            btnCopiarLink.innerHTML = '<i class="bi bi-check-lg"></i> Link Copiado!';
                            
                            // Restaura o texto original após 2 segundos
                            setTimeout(() => {
                                btnCopiarLink.innerHTML = originalText;
                            }, 2000);
                        })
                        .catch(err => {
                            console.error('Erro ao copiar o link: ', err);
                            alert('Não foi possível copiar o link automaticamente. Por favor, copie o endereço da página manualmente.');
                        });
                } else {
                    // Fallback para navegadores que não suportam a API Clipboard
                    const tempInput = document.createElement('input');
                    document.body.appendChild(tempInput);
                    tempInput.value = url;
                    tempInput.select();
                    
                    try {
                        document.execCommand('copy');
                        const originalText = btnCopiarLink.innerHTML;
                        btnCopiarLink.innerHTML = '<i class="bi bi-check-lg"></i> Link Copiado!';
                        
                        setTimeout(() => {
                            btnCopiarLink.innerHTML = originalText;
                        }, 2000);
                    } catch (err) {
                        console.error('Erro ao copiar o link: ', err);
                        alert('Não foi possível copiar o link automaticamente. Por favor, copie o endereço da página manualmente.');
                    }
                    
                    document.body.removeChild(tempInput);
                }
            });
        }
    });

    // Garantir que o reacaoManager só seja inicializado uma vez, após carregar a página completamente
    window.addEventListener('load', function() {
        // Só inicializa o reacaoManager para velas ativas
        const btnReacao = document.getElementById('btn-reacao-principal');
        if (btnReacao) {
            // Inicializar o reacaoManager depois que a página estiver completamente carregada
            reacaoManager.inicializar();
        }
    });
    </script>
</body>
</html>

<?php
/**
 * Função auxiliar para escurecer uma cor
 * 
 * @param string $color Cor em formato hexadecimal (#RRGGBB)
 * @param int $percent Percentual de mudança (-100 a 100)
 * @return string Nova cor em formato hexadecimal
 */
function shadeColor($color, $percent) {
    $R = hexdec(substr($color, 1, 2));
    $G = hexdec(substr($color, 3, 2));
    $B = hexdec(substr($color, 5, 2));

    $R = round($R * (100 + $percent) / 100);
    $G = round($G * (100 + $percent) / 100);
    $B = round($B * (100 + $percent) / 100);

    $R = min($R, 255);
    $G = min($G, 255);
    $B = min($B, 255);

    $RR = sprintf("%02X", $R);
    $GG = sprintf("%02X", $G);
    $BB = sprintf("%02X", $B);

    return "#$RR$GG$BB";
}
?>