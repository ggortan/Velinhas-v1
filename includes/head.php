<?php
/**
 * Componente Head compartilhado entre as páginas
 * Versão: 3.1.0
 */
require_once __DIR__ . '/../config/config.php';

// Se não foi definido um título, usa o padrão
if (!isset($pageTitle)) {
    $pageTitle = "Velinhas Virtuais - Acenda a sua 🕯";
}

// Se não foi definida uma descrição, usa o padrão
if (!isset($pageDescription)) {
    $pageDescription = "Acenda uma velinha virtual e faça sua oração. Um espaço para fortalecer sua fé, refletir e encontrar paz através da espiritualidade.";
}

// Gerar token CSRF para formulários se ainda não existir
if (!isset($csrfToken)) {
    $csrfToken = gerarCsrfToken();
}
?>
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-P1KX1K9TYQ"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-P1KX1K9TYQ');
    </script>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2375563319019863"
     crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <meta name="keywords" content="velinha, vela virtual, oração, fé, religião, espiritualidade, paz, esperança">
    <meta name="author" content="Velinhas Virtuais - Acenda a sua 🕯">
    <meta name="csrf-token" content="<?php echo $csrfToken; ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://velinhas.com.br">
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $pageDescription; ?>">
    <meta property="og:image" content="https://velinhas.com.br/assets/img/capela_velas.jpeg">
    <meta property="og:image:width" content="1024">
    <meta property="og:image:height" content="640">
    
    <!-- Manifesto PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#fbe8c0">
    <link rel="apple-touch-icon" href="/assets/img/vela-icon-192.png">
    
    <!-- Estilos e scripts -->
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo VERSION; ?>">
    <link rel="icon" type="image/png" href="/assets/img/vela.png">
    
    <?php if (isset($extraHeadContent)) echo $extraHeadContent; ?>
</head>