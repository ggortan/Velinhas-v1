<?php
/**
 * Componente Header com navegação compartilhada entre as páginas
 * Versão: 3.1.0
 */
?>
<a href="#main-content" class="skiplink">Pular para o conteúdo principal</a>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">
            <img src="/assets/img/vela.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top">
            Velinhas
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($activePage == 'home') ? 'active' : ''; ?>" href="<?php echo ($activePage == 'home') ? '#capela' : '/'; ?>">Capela de Velas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo ($activePage == 'home') ? '#footer' : '/#footer'; ?>">Sobre</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($activePage == 'changelog') ? 'active' : ''; ?>" href="/changelog.php">Changelog</a>
                </li>
            </ul>
            <?php if ($activePage == 'home'): ?>
                <button class="btn btn-acender" data-bs-toggle="modal" data-bs-target="#velaModal">Acender Velinha</button>
            <?php else: ?>
                <a href="/" class="btn btn-acender">Acender Velinha</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Indicador de offline -->
<div class="offline-badge">
    <i class="bi bi-wifi-off"></i> Modo Offline
</div>