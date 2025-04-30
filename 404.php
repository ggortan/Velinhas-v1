<?php
/**
 * Página de erro 404 do Velinhas
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/utils.php';

// Define o título e descrição da página
$pageTitle = "Página não encontrada - Velinhas Virtuais 🕯";
$pageDescription = "Oops! A página que você procura não foi encontrada.";

// Define a página ativa para o menu (nenhuma estará ativa)
$activePage = '';

// Define estilos adicionais específicos para esta página
$extraHeadContent = '
<style>
    .error-container {
        text-align: center;
        padding: 5rem 1rem;
    }
    
    .error-code {
        font-size: 8rem;
        font-weight: bold;
        color: #f8c471;
        margin-bottom: 1rem;
        line-height: 1;
    }
    
    .error-icon {
        font-size: 5rem;
        margin-bottom: 2rem;
        color: #f8c471;
    }
    
    .error-message {
        font-size: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .candle-animation {
        width: 100px;
        height: 150px;
        margin: 0 auto 2rem;
        position: relative;
    }
    
    .flame {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 30px;
        height: 60px;
        background: linear-gradient(to bottom, #ffcc33, #ff9933);
        border-radius: 50% 50% 20% 20%;
        box-shadow: 0 0 20px #ffcc33, 0 0 40px #ffcc33;
        animation: flicker 1s infinite alternate;
    }
    
    .candle-body {
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 40px;
        height: 100px;
        background: linear-gradient(to bottom, #fff, #f1f1f1);
        border-radius: 10px;
    }
    
    @keyframes flicker {
        0%, 100% {
            transform: translateX(-50%) scale(1);
            opacity: 1;
        }
        50% {
            transform: translateX(-50%) scale(0.9);
            opacity: 0.8;
        }
    }
</style>';

// Inclui o componente head
require_once __DIR__ . '/includes/head.php';

// Define o código de status HTTP como 404
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
    <!-- Esta tag será substituída pelo conteúdo de includes/head.php -->
</head>
<body>
    <?php require_once __DIR__ . '/includes/header.php'; ?>
    
    <div class="container" id="main-content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="error-container">
                    
                    <div class="candle-animation">
                        <div class="flame"></div>
                        <div class="candle-body"></div>
                    </div>
                    
                    <div class="error-code">404</div>
                    
                    <h1 class="error-message">Oops! Esta página se apagou...</h1>
                    
                    <p class="mb-4">A página que você está procurando não foi encontrada. Talvez tenha sido movida, excluída ou nunca tenha existido.</p>
                    
                    <div class="mb-5">
                        <a href="/" class="btn btn-acender btn-lg me-2">
                            <i class="fas fa-home me-2"></i>Voltar para a Capela
                        </a>
                        <a href="/sobre" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-info-circle me-2"></i>Sobre o Velinhas
                        </a>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>