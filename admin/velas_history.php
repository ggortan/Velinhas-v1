<?php
/**
 * Administração - Histórico de Velas Expiradas
 * Versão: 3.7.0
 */

// Verificação de autenticação básica (implemente de acordo com seu sistema)
session_start();
require_once __DIR__ . '/../config/config.php';


$admin_password = SENHA_ADMIN;

// Verificar autenticação
$isAuthenticated = false;

// Verificar se está tentando fazer login
if (isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === $admin_password) {
        $_SESSION['admin_auth'] = true;
        $isAuthenticated = true;
    } else {
        $loginError = "Senha incorreta!";
    }
}

// Verificar se já está autenticado
if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true) {
    $isAuthenticated = true;
}

// Se não estiver autenticado, exibe formulário de login
if (!$isAuthenticated) {
    showLoginForm($loginError ?? null);
    exit;
}

// A partir daqui, o usuário está autenticado como admin

require_once __DIR__ . '/../includes/utils.php';

// Processar filtros
$filtros = [];
if (isset($_GET['filtrar'])) {
    if (!empty($_GET['nome'])) {
        $filtros['nome'] = $_GET['nome'];
    }
    
    if (!empty($_GET['dataInicio'])) {
        $filtros['dataInicio'] = $_GET['dataInicio'];
    }
    
    if (!empty($_GET['dataFim'])) {
        $filtros['dataFim'] = $_GET['dataFim'];
    }
    
    if (isset($_GET['personalizacao']) && $_GET['personalizacao'] !== '') {
        $filtros['personalizacao'] = $_GET['personalizacao'];
    }
    
    if (isset($_GET['duracao']) && $_GET['duracao'] !== '') {
        $filtros['duracao'] = $_GET['duracao'];
    }
}

// Processar paginação
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$itensPorPagina = isset($_GET['porPagina']) ? (int)$_GET['porPagina'] : 20;

// Obter histórico de velas
$resultados = getHistoricoVelas($paginaAtual, $itensPorPagina, $filtros);

// Preparar dados para a view
$velas = $resultados['itens'];
$totalVelas = $resultados['total'];
$totalPaginas = $resultados['totalPaginas'];

// Exportar para CSV
if (isset($_GET['exportar']) && $_GET['exportar'] === 'csv') {
    // Obter todos os resultados para exportação
    $todosDados = getHistoricoVelas(1, 9999999, $filtros)['itens'];
    
    // Configurar cabeçalhos para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="historico_velas_'.date('Y-m-d').'.csv"');
    
    // Criar arquivo CSV
    $output = fopen('php://output', 'w');
    
    // Adicionar BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalhos
    fputcsv($output, [
        'ID', 'Nome', 'Data Acesa', 'Data Expiração', 'Data Expiração Real',
        'Duração (dias)', 'Personalização', 'Reações'
    ]);
    
    // Dados
    foreach ($todosDados as $vela) {
        fputcsv($output, [
            $vela['id'],
            $vela['nome'],
            $vela['dataAcesa'],
            $vela['dataExpira'],
            $vela['dataExpiracaoFormatada'],
            $vela['duracao'],
            $vela['personalizacao'],
            $vela['reacoes']
        ]);
    }
    
    fclose($output);
    exit;
}

// Função para exibir formulário de login
function showLoginForm($error = null) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Administração - Login</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Acesso Administrativo</h4>
                        </div>
                        
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="admin_password" class="form-label">Senha Administrativa</label>
                                    <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Acessar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// Define o título e descrição da página
$pageTitle = "Administração - Histórico de Velas | Velinhas";
$pageDescription = "Histórico de velas expiradas do sistema Velinhas.";

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="/assets/img/vela.png">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .admin-header {
            background-color: #6f4f1f;
            color: white;
            padding: 15px 0;
            margin-bottom: 20px;
        }
        .filter-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .data-table {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .pagination-container {
            margin-top: 20px;
        }
        .vela-preview {
            width: 30px;
            height: 70px;
            display: inline-block;
            background-size: cover;
            border-radius: 3px;
        }
        .logout-btn {
            color: white;
            text-decoration: none;
        }
        .logout-btn:hover {
            color: #f0f0f0;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Administração - Histórico de Velas</h1>
                <div>
                    <a href="/admin/velas_admin.php" class="btn btn-warning btn-sm me-2">
                        <i class="bi bi-shield-fill"></i> Gerenciamento de Velas
                    </a>
                    <a href="?logout=1" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Sair</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Seção de Filtros -->
        <div class="filter-section">
            <h4><i class="bi bi-funnel"></i> Filtros</h4>
            <form action="" method="GET" class="row g-3">
                <div class="col-md-6">
                    <label for="nome" class="form-label">Nome da vela</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo isset($_GET['nome']) ? htmlspecialchars($_GET['nome']) : ''; ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="dataInicio" class="form-label">Data inicial</label>
                    <input type="date" class="form-control" id="dataInicio" name="dataInicio" value="<?php echo isset($_GET['dataInicio']) ? htmlspecialchars($_GET['dataInicio']) : ''; ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="dataFim" class="form-label">Data final</label>
                    <input type="date" class="form-control" id="dataFim" name="dataFim" value="<?php echo isset($_GET['dataFim']) ? htmlspecialchars($_GET['dataFim']) : ''; ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="personalizacao" class="form-label">Tipo de vela</label>
                    <select class="form-select" id="personalizacao" name="personalizacao">
                        <option value="">Todas</option>
                        <option value="vela0" <?php echo isset($_GET['personalizacao']) && $_GET['personalizacao'] === 'vela0' ? 'selected' : ''; ?>>Vela Branca</option>
                        <option value="vela1" <?php echo isset($_GET['personalizacao']) && $_GET['personalizacao'] === 'vela1' ? 'selected' : ''; ?>>Vela 1</option>
                        <option value="vela2" <?php echo isset($_GET['personalizacao']) && $_GET['personalizacao'] === 'vela2' ? 'selected' : ''; ?>>Vela 2</option>
                        <option value="vela3" <?php echo isset($_GET['personalizacao']) && $_GET['personalizacao'] === 'vela3' ? 'selected' : ''; ?>>Vela 3</option>
                        <option value="cor" <?php echo isset($_GET['personalizacao']) && $_GET['personalizacao'] === 'cor' ? 'selected' : ''; ?>>Cor Personalizada</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="duracao" class="form-label">Duração</label>
                    <select class="form-select" id="duracao" name="duracao">
                        <option value="">Todas</option>
                        <option value="1" <?php echo isset($_GET['duracao']) && $_GET['duracao'] === '1' ? 'selected' : ''; ?>>1 Dia</option>
                        <option value="7" <?php echo isset($_GET['duracao']) && $_GET['duracao'] === '7' ? 'selected' : ''; ?>>7 Dias</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="porPagina" class="form-label">Itens por página</label>
                    <select class="form-select" id="porPagina" name="porPagina">
                        <option value="20" <?php echo $itensPorPagina === 20 ? 'selected' : ''; ?>>20</option>
                        <option value="50" <?php echo $itensPorPagina === 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $itensPorPagina === 100 ? 'selected' : ''; ?>>100</option>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" name="filtrar" value="1" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    
                    <a href="?" class="btn btn-secondary me-2">
                        <i class="bi bi-x-circle"></i> Limpar
                    </a>
                    
                    <button type="submit" name="exportar" value="csv" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel"></i> Exportar CSV
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Resumo dos resultados -->
        <div class="badge bg-secondary mb-2">
            <strong><?php echo $totalVelas; ?></strong> velas encontradas 
            <?php if (!empty($filtros)): ?>
                com os filtros aplicados
            <?php endif; ?>
        </div>

        <!-- Tabela de Dados -->
        <div class="data-table">
            <?php if (empty($velas)): ?>
                <div class="alert alert-warning m-3">
                    Nenhum registro encontrado.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Vela</th>
                                <th>Nome</th>
                                <th>Acesa em</th>
                                <th>Apagada em</th>
                                <th>Duração</th>
                                <th>Reações</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($velas as $vela): ?>
                                <tr>
                                    <td><?php echo $vela['id']; ?></td>
                                    <td>
                                        <?php if (isset($vela['personalizacao'])): ?>
                                            <?php if (strpos($vela['personalizacao'], '#') === 0): ?>
                                                <div class="vela-preview" style="background-color: <?php echo $vela['personalizacao']; ?>"></div>
                                            <?php else: ?>
                                                <div class="vela-preview" style="background-image: url('/assets/img/<?php echo $vela['personalizacao']; ?>.png')"></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="vela-preview" style="background-image: url('/assets/img/vela0.png')"></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($vela['nome']); ?></td>
                                    <td><?php echo $vela['dataAcesa']; ?></td>
                                    <td><?php echo $vela['dataExpiracaoFormatada']; ?></td>
                                    <td><?php echo $vela['duracao']; ?> dia(s)</td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <i class="bi bi-heart-fill"></i> <?php echo $vela['reacoes']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/vela/<?php echo $vela['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Paginação -->
        <?php if ($totalPaginas > 1): ?>
            <div class="pagination-container">
                <nav aria-label="Navegação de páginas">
                    <ul class="pagination justify-content-center">
                        <?php
                        // Constrói os parâmetros para manter filtros
                        $params = array_merge($_GET, ['pagina' => '']);
                        unset($params['exportar']); // Remove exportar para não causar download
                        
                        $queryString = http_build_query($params);
                        $baseUrl = '?' . $queryString;
                        $baseUrl = str_replace('pagina=', '', $baseUrl);
                        ?>
                        
                        <li class="page-item <?php echo $paginaAtual <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $baseUrl . '1'; ?>" aria-label="Primeira">
                                <span aria-hidden="true">&laquo;&laquo;</span>
                            </a>
                        </li>
                        
                        <li class="page-item <?php echo $paginaAtual <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $baseUrl . ($paginaAtual - 1); ?>" aria-label="Anterior">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php
                        // Determina quais números de página mostrar
                        $startPage = max(1, $paginaAtual - 2);
                        $endPage = min($totalPaginas, $paginaAtual + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <li class="page-item <?php echo $i == $paginaAtual ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo $baseUrl . $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $paginaAtual >= $totalPaginas ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $baseUrl . ($paginaAtual + 1); ?>" aria-label="Próxima">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        
                        <li class="page-item <?php echo $paginaAtual >= $totalPaginas ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $baseUrl . $totalPaginas; ?>" aria-label="Última">
                                <span aria-hidden="true">&raquo;&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <div class="text-center mt-2">
                    <small class="text-muted">
                        Página <?php echo $paginaAtual; ?> de <?php echo $totalPaginas; ?>
                    </small>
                </div>
            </div>
        <?php endif; ?>

        <!-- Rodapé administrativo -->
        <footer class="mt-4 mb-4 text-center">
            <p><small>Sistema Administrativo Velinhas - v3.7.0</small></p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>