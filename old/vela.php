<?php
$file = 'velas.json';

if (!file_exists($file)) {
    die("Arquivo de velas não encontrado.");
}

$velas = json_decode(file_get_contents($file), true);
if (!is_array($velas)) {
    die("Erro ao carregar as velas.");
}

$idVela = null;

// Pegando ID da URL
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $idVela = (int) $_GET['id'];
}

// Verifica se o ID foi passado
if ($idVela === null) {
    die("ID da vela inválido ou não fornecido.");
}

$agora = time();
$velaEncontrada = null;

foreach ($velas as $key => $vela) {
    if (!isset($vela['id'], $vela['timestamp'], $vela['duracao'])) {
        continue;
    }

    if ($vela['id'] === $idVela) {
        $dataExpiraTimestamp = $vela['timestamp'] + ((int)$vela['duracao'] * 86400);

        if ($dataExpiraTimestamp > $agora) {
            $vela['dataAcesa'] = date("d/m/Y H:i", $vela['timestamp']);
            $vela['dataExpira'] = date("d/m/Y H:i", $dataExpiraTimestamp);
            $velaEncontrada = $vela;
        } else {
            //unset($velas[$key]); // Remove vela expirada
        }
        break;
    }
}

// Atualiza o JSON se uma vela foi removida
file_put_contents($file, json_encode(array_values($velas), JSON_PRETTY_PRINT));

if (!$velaEncontrada) {
    die("Vela não encontrada ou expirada.");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velinhas Virtuais - Acenda a sua 🕯</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://velinhas.com.br/inc/style.css?version=3.0.1">
    <link rel="stylesheet" href="https://velinhas.com.br/inc/style_backup.css">
    <link rel="icon" type="image/png" href="https://velinhas.com.br/img/vela.png">
    <meta name="description" content="Acenda uma velinha virtual e faça sua oração. Um espaço para fortalecer sua fé, refletir e encontrar paz através da espiritualidade.">
    <meta name="keywords" content="velinha, vela virtual, oração, fé, religião, espiritualidade, paz, esperança">
    <meta name="author" content="Velinhas Virtuais - Acenda a sua 🕯">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://velinhas.com.br/vela/<?php echo $velaEncontrada['dataAcesa']; ?>">
    <meta property="og:title" content="Velinhas Virtuais - Acenda a sua 🕯">
    <meta property="og:description" content="Acenda uma velinha virtual e faça sua oração. Um espaço para fortalecer sua fé, refletir e encontrar paz através da espiritualidade.">
    <meta property="og:image" content="https://velinhas.com.br/img/capela_velas.jpeg">
    <meta property="og:image:width" content="1024">
    <meta property="og:image:height" content="640">
  </head>
  <style>
  .capela-card-body {
    background-color: #fff6e2;
    justify-content: center;
    align-items: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.ca
  </style>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
      <div class="container-fluid">
        <a class="navbar-brand" href="https://velinhas.com.br">
          <img src="https://velinhas.com.br/img/vela.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top"> Velinhas</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="https://velinhas.com.br">Capela de Velas</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#footer">Sobre</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="changelog.html">Changelog</a>
            </li>
          </ul>
          <button class="btn btn-acender" data-bs-toggle="modal" data-bs-target="#velaModal"> Acender Velinha</button>
        </div>
      </div>
    </nav>
    <div class="container text-center mb-4">
      <div class="card mt-4 mb-4 capela-card" id="capela">
        <div class="card-header d-flex justify-content-between align-items-center capela-card-header">
          <span class="capela-card-title">Capela de velas</span>
        </div>
        <div class="card-body capela-card-body">
            <div class="vela-container">
                <div class="vela" style="background-image: url(https://velinhas.com.br/img/<?php echo $velaEncontrada['personalizacao']; ?>.png); background-size: cover;">
                    <div class="vela-chama">
                    </div>
                </div>
                <div class="info-vela">
                    <p><strong><?php echo htmlspecialchars($velaEncontrada['nome'] ?? "Vela"); ?></strong></p>
                    <p><small>Acesa em: <?php echo $velaEncontrada['dataAcesa']; ?></small></p>
                    <p><small>Apaga em: <?php echo $velaEncontrada['dataExpira']; ?></small></p>
                </div>
            </div>
        </div>
        <div class="card-footer text-body-secondary capela-card-footer" style="color:645032;">
          <b>ATENÇÃO: Após acender uma vela não é possível apagar. Cuidado para não se queimar</b>
        </div>
      </div>
    </div>
    <footer class="text-light py-4" id="footer">
        <div class="container text-center">
            <h5>Sobre o Velinhas</h5>
            <p><small>Este é um projeto sem fins lucrativos, criado com a intenção de promover momentos de reflexão, paz e espiritualidade. Pedimos que usem a ferramenta com consciência e respeito, lembrando sempre que é importante não abusar da criação de velas, pois <mark>Deus está de olho em nossas ações.</mark>
                <br>Proibido: Acender velas que incitem ódio, violência, racismo ou qualquer forma de discriminação. Qualquer uso indevido da plataforma poderá resultar em bloqueio do acesso.
                <br>Aproveite este espaço com respeito, amor e harmonia. Vamos manter a luz acesa de maneira positiva, espalhando boas energias para todos.</small>
            </p>
            <p class="mb-1">&copy; 2024 Velinhas.com.br</p>
            <span class="badge text-bg-light">Versão: 3.0.1</span>
        </div>
    </footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://velinhas.com.br/inc/script.js?version=2-1-4-1"></script>
</body>
</html>
