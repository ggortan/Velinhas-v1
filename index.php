<?php
/**
 * Velinhas - Página principal
 * Versão: 3.1.0
 */
// Iniciar a sessão se ainda não foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/utils.php';

// Define a página ativa para o menu
$activePage = 'home';

// Define o título e descrição da página
$pageTitle = "Velinhas Virtuais - Acenda a sua 🕯";
$pageDescription = "Acenda uma velinha virtual e faça sua oração. Um espaço para fortalecer sua fé, refletir e encontrar paz através da espiritualidade.";

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
    
    <div class="container text-center mb-4" id="main-content">
        <input type="hidden" id="global_csrf_token" name="csrf_token" value="<?php echo $csrfToken; ?>">

        <div class="alert alert-vela mb-4 mt-1" role="alert">
            <strong>Novidade:</strong> Agora você pode compartilhar suas Velinhas! Acenda a sua e compartilhe suas orações 🙏
        </div>
        
        <div id="alert-container"></div>
        
        <h1>Acenda uma Vela Virtual 🕯</h1>
        <button class="btn btn-acender btn-lg mt-3 hover-scale" data-bs-toggle="modal" data-bs-target="#velaModal">Acender Velinha</button>
        
        <div class="mt-3">
            <a href="#footer">
                <small>Saiba mais sobre Velinhas Virtuais</small>
            </a>
        </div>

        
        <div class="card mt-4 mb-4 capela-card" id="capela">
            <div class="card-header d-flex justify-content-between align-items-center capela-card-header">
                <span class="capela-card-title">Capela de velas</span>
                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#doacaoModal">
                    <i class="bi bi-heart-fill"></i> Ajudar o Velinhas
                 </button>
            </div>
            
            <div class="card-body capela-card-body">
                <p>Temos <span id="badgeVelasAcesas" class="badge rounded-pill text-bg-secondary">0</span> velas acesas na capela!</p>
                <div id="velas-container" class="mt-4 d-flex flex-wrap justify-content-center">
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-3">Carregando velas...</p>
                    </div>
                </div>
            </div>
            
            <div class="card-footer text-body-secondary capela-card-footer" style="color:#645032;">
                <b>ATENÇÃO: Após acender uma vela não é possível apagar. Cuidado para não se queimar</b>
            </div>
        </div>
        
        <?php include BASE_PATH . '/api/versiculo.php'; ?>
        <?php include_once 'api/banners.php'; ?>
        
    </div>
    
    <!-- Modal para acender vela -->
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
    
    
    <!-- Toast de boas-vindas -->
    <div class="toast-container">
        <div id="toastMessage" class="toast border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">🕊 Mensagem Divina</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
            <div class="toast-body">
                Crie velas com moderação e respeito. Suas orações são importantes!
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>