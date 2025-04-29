<?php
/**
 * Componente Footer compartilhado entre as páginas
 * Versão: 3.1.0
 */
require_once __DIR__ . '/../config/config.php';

// Obtém o número total de velinhas acesas
$statsFile = DATA_PATH . '/../data/stats.json';
$totalVelinhas = 0;

if (file_exists($statsFile)) {
    $stats = json_decode(file_get_contents($statsFile), true);
    $totalVelinhas = isset($stats['ultimo_id']) ? (int)$stats['ultimo_id'] : 0;
}

?>
<!-- Footer -->
<?php include_once BASE_PATH . '/api/pix.php'; ?>
<footer class="text-light py-4" id="footer">
    <div class="container text-center">
        
        <h5>Sobre o Velinhas</h5>
        <p><small>Este é um projeto sem fins lucrativos, criado com a intenção de promover momentos de reflexão, paz e espiritualidade. Pedimos que usem a ferramenta com consciência e respeito, lembrando sempre que é importante não abusar da criação de velas, pois <mark>Deus está de olho em nossas ações.</mark>
            <br>Proibido: Acender velas que incitem ódio, violência, racismo ou qualquer forma de discriminação. Qualquer uso indevido da plataforma poderá resultar em bloqueio do acesso.
            <br>Aproveite este espaço com respeito, amor e harmonia. Vamos manter a luz acesa de maneira positiva, espalhando boas energias para todos.</small>
        </p>
        <!-- Contador de velinhas -->
        <div class="mt-2 mb-2">
            <span class="badge bg-warning text-dark">
                <i class="bi bi-fire"></i> Total de velinhas acesas: <?php echo number_format($totalVelinhas, 0, ',', '.'); ?>
            </span>
        </div>
        <span class="badge text-bg-light mb-2">Versão: <?php echo VERSION; ?></span>
        <div>
            <span class="me-2">Criado com fé por Gabriel Gortan</span>
            <a href="https://www.linkedin.com/in/gabrielgortan" target="_blank" class="text-decoration-none me-2">
                <i class="bi bi-linkedin" style="color: white;"></i>
            </a>
            <a href="https://github.com/ggortan" target="_blank" class="text-decoration-none">
                <i class="bi bi-github" style="color: white;"></i>
            </a>
        </div>
        <p class="mb-2">&copy; 2025 Velinhas.com.br</p>
    </div>
</footer>

<!-- Botão de alternância de tema -->
<button class="theme-toggle" id="theme-toggle" aria-label="Alternar tema">
    <i class="bi bi-moon-fill"></i>
</button>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/script.js?v=<?php echo VERSION; ?>"></script>
<script src="/assets/js/reacao-system.js?v=<?php echo VERSION; ?>"></script>


<!-- Script para verificação de conexão -->
<script>
    // Verificar conexão
    function updateOnlineStatus() {
        if (navigator.onLine) {
            document.body.classList.remove('offline');
        } else {
            document.body.classList.add('offline');
        }
    }
    
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    updateOnlineStatus();
    
    // Alternância de tema
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = themeToggle.querySelector('i');
        
        // Verificar se há um tema salvo no localStorage
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateIcon(savedTheme);
        } else {
            // Verificar preferência do sistema
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (prefersDark) {
                document.documentElement.setAttribute('data-theme', 'dark');
                updateIcon('dark');
            }
        }
        
        // Alternar tema ao clicar no botão
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });
        
        // Atualizar ícone com base no tema
        function updateIcon(theme) {
            if (theme === 'dark') {
                themeIcon.classList.remove('bi-moon-fill');
                themeIcon.classList.add('bi-sun-fill');
            } else {
                themeIcon.classList.remove('bi-sun-fill');
                themeIcon.classList.add('bi-moon-fill');
            }
        }
    });
</script>
<?php if (isset($extraFooterContent)) echo $extraFooterContent; ?>