<?php
/**
 * Componente de Banners de E-commerce
 * Carrega dados de anúncios a partir de um arquivo JSON
 */

// Configurações do componente
$configBanners = [
    'rotationTime' => 5000, // Tempo de rotação em milissegundos (5000 = 5 segundos)
    'pauseTime' => 10000    // Tempo de pausa após clique em indicador (10000 = 10 segundos)
];

// Configurações das lojas: nome da loja, cor do botão e do badge
$storeColors = [
    'shopee' => [
        'name' => 'Shopee',
        'color' => '#ee4d2d',
        'hover' => '#d73211'
    ],
    'mercadolivre' => [
        'name' => 'Mercado Livre',
        'color' => '#fff159',
        'hover' => '#ebe55d',
        'textColor' => '#333333' // Texto escuro para fundo claro
    ],
    'magalu' => [
        'name' => 'Magazine Luiza',
        'color' => '#0086ff',
        'hover' => '#0072db'
    ],
    'amazon' => [
        'name' => 'Amazon',
        'color' => '#ff9900',
        'hover' => '#e88a00'
    ],
    'aliexpress' => [
        'name' => 'AliExpress',
        'color' => '#e62e04',
        'hover' => '#c42200'
    ]
];

// Caminho para o arquivo JSON de anúncios
$jsonFilePath = __DIR__ . '/../data/pub.json';

// Função para carregar os dados do JSON
function loadBannerData($filePath, $storeColors) {
    if (file_exists($filePath)) {
        $jsonContent = file_get_contents($filePath);
        $data = json_decode($jsonContent, true);
        
        // Verificar se o JSON foi decodificado corretamente
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            // Validar e garantir que os dados tenham a propriedade store
            foreach ($data as &$item) {
                if (!isset($item['store']) || !isset($storeColors[$item['store']])) {
                    $item['store'] = 'shopee'; // Usar shopee como fallback
                }
            }
            return $data;
        }
    }
    
    // Retornar dados padrão caso o arquivo não exista ou tenha problema
    return [
        [
            'title' => 'Velas Aromáticas Artesanais',
            'description' => 'Velas aromáticas para momentos de paz e oração. Diversas fragrâncias.',
            'image' => '/assets/img/vela0.png',
            'price_original' => 'R$49,90',
            'price_current' => 'R$39,90',
            'link' => 'https://shopee.com.br/velas-artesanais',
            'alt' => 'Vela Artesanal Aromática',
            'store' => 'shopee' // Nome da loja para cores e badge
        ],
        [
            'title' => 'Kit Velas Decorativas',
            'description' => 'Kit com 3 velas decorativas para sua casa. Perfeitas para altar ou decoração.',
            'image' => '/assets/img/vela2.png',
            'price_original' => 'R$89,90',
            'price_current' => 'R$69,90',
            'link' => 'https://mercadolivre.com.br/kit-velas-decorativas',
            'alt' => 'Kit Velas Decorativas',
            'store' => 'mercadolivre' // Nome da loja para cores e badge
        ]
    ];
}

// Carregar dados dos banners
$bannerData = loadBannerData($jsonFilePath, $storeColors);

// Verificar que temos pelo menos um banner
$bannerCount = count($bannerData);
if ($bannerCount < 1) {
    // Se não houver banners, não exibe nada
    return;
}
?>

<!-- Estilo para os banners de e-commerce -->
<style>
  /* Estilos para o container de banners */
  .shop-banner-container {
    display: flex;
    gap: 15px;
    margin: 15px 0;
    width: 100%;
  }

  /* Estilos para cada banner */
  .shop-banner {
    background-color: #ffffff;
    border: 1px solid #eeeeee;
    border-radius: 8px;
    padding: 10px 15px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    flex: 1;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
  }

  /* Efeito de hover */
  .shop-banner:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
  }

  /* Badge da loja */
  .platform-badge {
    position: absolute;
    top: 0;
    right: 0;
    color: white;
    font-size: 0.7rem;
    padding: 3px 8px;
    border-radius: 0 0 0 8px;
  }

  /* Container para a imagem */
  .product-image {
    width: 80px;
    height: 80px;
    border-radius: 6px;
    overflow: hidden;
    margin-right: 15px;
    flex-shrink: 0;
  }

  .product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  /* Conteúdo do banner */
  .product-content {
    flex-grow: 1;
  }

  .product-title {
    font-weight: 600;
    margin: 0 0 5px 0;
    font-size: 1rem;
    color: #333;
  }

  .product-description {
    font-size: 0.85rem;
    margin: 0 0 8px 0;
    color: #666;
  }

  /* Preço e botão */
  .price-action {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 5px;
  }

  .product-price {
    font-weight: 700;
    font-size: 1.1rem;
  }

  .product-price .original {
    text-decoration: line-through;
    color: #999;
    font-size: 0.85rem;
    margin-right: 5px;
    font-weight: normal;
  }

  .shop-button {
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 0.85rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.2s;
  }

  /* Controle de rotação mobile */
  .mobile-banner {
    display: none; /* Inicialmente oculto */
    width: 100%;
  }

  .banner-indicator {
    display: none;
    text-align: center;
    margin-top: 10px;
    z-index: 2;
  }

  .banner-dot {
    height: 8px;
    width: 8px;
    background-color: #bbb;
    border-radius: 50%;
    display: inline-block;
    margin: 0 4px;
    cursor: pointer;
  }

  .banner-dot.active {
    background-color: #333; /* Cor genérica que será substituída por JavaScript */
  }

  /* Cores para as lojas (fallback caso o inline não funcione) */
  .store-shopee .product-price, 
  .store-shopee .banner-dot.active {
    color: #ee4d2d;
  }
  .store-shopee .shop-button,
  .store-shopee .platform-badge {
    background-color: #ee4d2d;
  }

  .store-mercadolivre .product-price, 
  .store-mercadolivre .banner-dot.active {
    color: #333333;
  }
  .store-mercadolivre .shop-button,
  .store-mercadolivre .platform-badge {
    background-color: #fff159;
    color: #333333;
  }

  .store-magalu .product-price, 
  .store-magalu .banner-dot.active {
    color: #0086ff;
  }
  .store-magalu .shop-button,
  .store-magalu .platform-badge {
    background-color: #0086ff;
  }

  .store-amazon .product-price, 
  .store-amazon .banner-dot.active {
    color: #ff9900;
  }
  .store-amazon .shop-button,
  .store-amazon .platform-badge {
    background-color: #ff9900;
  }

  .store-aliexpress .product-price, 
  .store-aliexpress .banner-dot.active {
    color: #e62e04;
  }
  .store-aliexpress .shop-button,
  .store-aliexpress .platform-badge {
    background-color: #e62e04;
  }

  /* Responsividade */
  @media (max-width: 768px) {
    .shop-banner-container {
      display: block;
      position: relative;
    }

    .desktop-banner {
      display: none;
    }

    .mobile-banner.active {
      display: flex; /* Apenas o banner ativo é mostrado */
      z-index: 1;
    }

    .banner-indicator {
      display: block;
      position: relative;
      text-align: center;
      margin-top: 10px;
    }

    .shop-banner {
      flex-direction: column;
      text-align: center;
      padding: 15px;
      width: 100%;
    }

    .product-image {
      margin-right: 0;
      margin-bottom: 10px;
    }

    .price-action {
      flex-direction: column;
      gap: 10px;
    }
  }
</style>

<!-- Container para os banners de e-commerce -->
<div class="shop-banner-container">
  <?php 
  // Exibir banners para desktop
  for ($i = 0; $i < $bannerCount; $i++) {
      $banner = $bannerData[$i];
      
      // Determinar a loja e suas cores
      $store = isset($banner['store']) && isset($storeColors[$banner['store']]) 
              ? $banner['store'] 
              : 'shopee'; // Shopee como padrão
      
      $storeName = $storeColors[$store]['name'];
      $storeColor = $storeColors[$store]['color'];
      $storeHover = $storeColors[$store]['hover'];
      $textColor = isset($storeColors[$store]['textColor']) ? $storeColors[$store]['textColor'] : 'white';
  ?>
  <!-- Banner <?= $i+1 ?> (Versão Desktop) -->
  <a href="<?= htmlspecialchars($banner['link']) ?>" target="_blank" class="shop-banner desktop-banner store-<?= $store ?>" data-store="<?= $store ?>">
    <span class="platform-badge" style="background-color: <?= $storeColor ?>; color: <?= $textColor ?>;"><?= $storeName ?></span>
    
    <div class="product-image">
      <img src="<?= htmlspecialchars($banner['image']) ?>" alt="<?= htmlspecialchars($banner['alt']) ?>">
    </div>
    
    <div class="product-content">
      <h4 class="product-title"><?= htmlspecialchars($banner['title']) ?></h4>
      <p class="product-description"><?= htmlspecialchars($banner['description']) ?></p>
      
      <div class="price-action">
        <div class="product-price" style="color: <?= $storeColor ?>;">
          <span class="original"><?= htmlspecialchars($banner['price_original']) ?></span>
          <?= htmlspecialchars($banner['price_current']) ?>
        </div>
        <span class="shop-button" style="background-color: <?= $storeColor ?>; color: <?= $textColor ?>;">Comprar</span>
      </div>
    </div>
  </a>
  <?php } ?>
  
  <?php
  // Exibir banners para mobile (mesmo conteúdo mas layout diferente)
  for ($i = 0; $i < $bannerCount; $i++) {
      $banner = $bannerData[$i];
      $isActive = $i === 0 ? 'active' : ''; // Primeiro banner começa ativo
      
      // Determinar a loja e suas cores
      $store = isset($banner['store']) && isset($storeColors[$banner['store']]) 
              ? $banner['store'] 
              : 'shopee'; // Shopee como padrão
      
      $storeName = $storeColors[$store]['name'];
      $storeColor = $storeColors[$store]['color'];
      $storeHover = $storeColors[$store]['hover'];
      $textColor = isset($storeColors[$store]['textColor']) ? $storeColors[$store]['textColor'] : 'white';
  ?>
  <!-- Banner <?= $i+1 ?> (Versão Mobile) -->
  <a href="<?= htmlspecialchars($banner['link']) ?>" target="_blank" class="shop-banner mobile-banner store-<?= $store ?> <?= $isActive ?>" id="mobile-banner-<?= $i+1 ?>" data-store="<?= $store ?>">
    <span class="platform-badge" style="background-color: <?= $storeColor ?>; color: <?= $textColor ?>;"><?= $storeName ?></span>
    
    <div class="product-image">
      <img src="<?= htmlspecialchars($banner['image']) ?>" alt="<?= htmlspecialchars($banner['alt']) ?>">
    </div>
    
    <div class="product-content">
      <h4 class="product-title"><?= htmlspecialchars($banner['title']) ?></h4>
      <p class="product-description"><?= htmlspecialchars($banner['description']) ?></p>
      
      <div class="price-action">
        <div class="product-price" style="color: <?= $storeColor ?>;">
          <span class="original"><?= htmlspecialchars($banner['price_original']) ?></span>
          <?= htmlspecialchars($banner['price_current']) ?>
        </div>
        <span class="shop-button" style="background-color: <?= $storeColor ?>; color: <?= $textColor ?>;">Comprar</span>
      </div>
    </div>
  </a>
  <?php } ?>
  
  <!-- Indicadores para o mobile com cores dinâmicas -->
  <div class="banner-indicator">
    <?php for ($i = 0; $i < $bannerCount; $i++) { 
        $isActive = $i === 0 ? 'active' : '';
        $banner = $bannerData[$i];
        $store = isset($banner['store']) && isset($storeColors[$banner['store']]) ? $banner['store'] : 'shopee';
        $storeColor = $storeColors[$store]['color'];
    ?>
    <span class="banner-dot <?= $isActive ?>" 
          onclick="showBanner(<?= $i+1 ?>, true)"
          data-store="<?= $store ?>"
          style="<?= $isActive ? 'background-color: '.$storeColor.';' : '' ?>"></span>
    <?php } ?>
  </div>
</div>

<!-- Script para rotação dos banners no mobile -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Iniciar a rotação automática
    startBannerRotation();
    
    // Verificar se está em viewport mobile quando redimensiona
    window.addEventListener('resize', checkMobileView);
    
    // Configurar os indicadores para evitar que o clique no indicador também ative o link do banner
    document.querySelectorAll('.banner-dot').forEach(function(dot, index) {
      dot.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const bannerNum = index + 1;
        showBanner(bannerNum, true); // true indica que é um clique de usuário
      });
    });
  });
  
  let currentBanner = 1;
  let rotationInterval;
  let bannerCount = <?= $bannerCount ?>; // Número total de banners do PHP
  let rotationTime = <?= $configBanners['rotationTime'] ?>; // Tempo de rotação configurável
  let pauseTime = <?= $configBanners['pauseTime'] ?>; // Tempo de pausa após clique
  
  // Mapeamento de lojas para cores
  const storeColors = {
    'shopee': '#ee4d2d',
    'mercadolivre': '#fff159',
    'magalu': '#0086ff',
    'amazon': '#ff9900',
    'aliexpress': '#e62e04'
  };
  
  // Função para trocar banners
  function showBanner(bannerNum, isUserClick) {
    // Esconder todos os banners
    document.querySelectorAll('.mobile-banner').forEach(function(banner) {
      banner.classList.remove('active');
    });
    
    // Mostrar o banner solicitado
    const targetBanner = document.getElementById('mobile-banner-' + bannerNum);
    targetBanner.classList.add('active');
    
    // Obter a loja do banner atual para atualizar a cor dos indicadores
    const store = targetBanner.getAttribute('data-store') || 'shopee';
    const storeColor = storeColors[store] || '#ee4d2d';
    
    // Atualizar os indicadores e suas cores
    document.querySelectorAll('.banner-dot').forEach(function(dot, index) {
      dot.classList.remove('active');
      dot.style.backgroundColor = '';
      
      if (index === bannerNum - 1) {
        dot.classList.add('active');
        dot.style.backgroundColor = storeColor;
      }
    });
    
    currentBanner = bannerNum;
    
    // Parar temporariamente a rotação automática APENAS se for um clique do usuário
    if (isUserClick && rotationInterval) {
      clearInterval(rotationInterval);
      // Reiniciar após o tempo de pausa configurado
      setTimeout(startBannerRotation, pauseTime);
    }
  }
  
  // Função para rotação automática
  function rotateBanners() {
    currentBanner = currentBanner >= bannerCount ? 1 : currentBanner + 1;
    showBanner(currentBanner, false); // false indica que não é um clique de usuário
  }
  
  // Função para iniciar rotação
  function startBannerRotation() {
    // Limpar qualquer intervalo existente
    if (rotationInterval) {
      clearInterval(rotationInterval);
    }
    
    // Iniciar novo intervalo com o tempo configurável se tiver mais de um banner
    if (bannerCount > 1) {
      rotationInterval = setInterval(rotateBanners, rotationTime);
    }
  }
  
  // Função para verificar se está em viewport mobile
  function checkMobileView() {
    if (window.innerWidth <= 768) {
      startBannerRotation();
    } else {
      // Se não estiver em mobile, para a rotação
      if (rotationInterval) {
        clearInterval(rotationInterval);
      }
    }
  }
</script>