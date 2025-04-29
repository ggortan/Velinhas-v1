<?php
/**
 * Módulo de Doações via PIX
 * Velinhas v3.7.5
 * 
 * Este módulo permite que os usuários façam doações via PIX
 * para ajudar a manter o projeto Velinhas.
 */
require_once __DIR__ . '/../config/config.php';
// Dados da chave PIX (agora usando a chave aleatória)
$chavePix = CHAVE_PIX;
$beneficiario = BENEFICIARIO_PIX;
$cidade = CIDADE_PIX;
$identificador = IDENTIFICADOR_PIX . date('YmdHis');

// Valores sugeridos para doação
$valoresSugeridos = [
    3, 5, 10, 20
];

// ID de campos do payload do PIX
const ID_PAYLOAD_FORMAT = '00';            // Formato do payload
const ID_MERCHANT_ACCOUNT = '26';          // Conta do recebedor
const ID_MERCHANT_CATEGORY = '52';         // Categoria do comerciante
const ID_TRANSACTION_CURRENCY = '53';      // Moeda da transação
const ID_TRANSACTION_AMOUNT = '54';        // Valor da transação
const ID_COUNTRY_CODE = '58';              // País
const ID_MERCHANT_NAME = '59';             // Nome do recebedor
const ID_MERCHANT_CITY = '60';             // Cidade do recebedor
const ID_ADDITIONAL_FIELD = '62';          // Campo adicional
const ID_CRC16 = '63';                     // CRC16
const ID_ADDITIONAL_FIELD_TXID = '05';     // ID da transação

/**
 * Método para criar um valor formatado para o payload
 * 
 * @param string $id Identificador do campo
 * @param string $value Valor do campo
 * @return string
 */
function getValue($id, $value) {
    $size = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);
    return $id . $size . $value;
}

/**
 * Gerar o payload do Pix usando a chave do recebedor
 * 
 * @param float $valor Valor da transação
 * @param string $chave Chave Pix do recebedor
 * @param string $nome Nome do recebedor
 * @param string $cidade Cidade do recebedor
 * @param string $identificador Identificador da transação
 * @return string
 */
function gerarPayloadPix($valor, $chave, $nome, $cidade, $identificador = '***') {
    // Formata o valor com 2 casas decimais e ponto como separador
    $valorFormatado = number_format($valor, 2, '.', '');
    
    // Informações da conta
    $merchantAccount = getValue('00', 'br.gov.bcb.pix');
    $merchantAccount .= getValue('01', $chave);
    $merchantAccountInfo = getValue(ID_MERCHANT_ACCOUNT, $merchantAccount);
    
    // Informações adicionais
    $additionalField = getValue(ID_ADDITIONAL_FIELD_TXID, $identificador);
    $additionalFieldTemplate = getValue(ID_ADDITIONAL_FIELD, $additionalField);
    
    // Monta o payload completo
    $payload = getValue(ID_PAYLOAD_FORMAT, '01') . 
               $merchantAccountInfo .
               getValue(ID_MERCHANT_CATEGORY, '0000') .
               getValue(ID_TRANSACTION_CURRENCY, '986') .
               getValue(ID_TRANSACTION_AMOUNT, $valorFormatado) .
               getValue(ID_COUNTRY_CODE, 'BR') .
               getValue(ID_MERCHANT_NAME, $nome) .
               getValue(ID_MERCHANT_CITY, $cidade) .
               $additionalFieldTemplate;
    
    // Adiciona o CRC16 ao final
    return $payload . getCRC16($payload);
}

/**
 * Método para calcular o CRC16 (CCITT-FALSE) conforme especificação do Bacen
 * 
 * @param string $payload Payload para cálculo
 * @return string Campo CRC16 formatado (ID+Tamanho+CRC16)
 */
function getCRC16($payload) {
    // Adiciona o campo do CRC16 (tamanho fixo 04)
    $payload .= ID_CRC16 . '04';
    
    // Parâmetros para CRC16-CCITT (0xFFFF)
    $polinomio = 0x1021;
    $resultado = 0xFFFF;
    
    // Cálculo do CRC16
    if (($length = strlen($payload)) > 0) {
        for ($offset = 0; $offset < $length; $offset++) {
            $resultado ^= (ord($payload[$offset]) << 8);
            for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                if (($resultado <<= 1) & 0x10000) {
                    $resultado ^= $polinomio;
                }
                $resultado &= 0xFFFF;
            }
        }
    }
    
    // Formata o resultado como campo EMV
    return ID_CRC16 . '04' . strtoupper(dechex($resultado));
}

// Formatar valor para exibição
function formatarValorReais($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

// Processar requisição AJAX para gerar código PIX
if (isset($_GET['valor']) && is_numeric($_GET['valor'])) {
    $valor = floatval($_GET['valor']);
    
    // Limita o valor mínimo e máximo
    $valor = max(1, min(1000, $valor));
    
    try {
        // Gera o código PIX
        $codigoPix = gerarPayloadPix(
            $valor,
            $chavePix,
            $beneficiario,
            $cidade,
            $identificador
        );
        
        // Informações de debug para ajudar na solução de problemas
        $debug = [
            'chave' => $chavePix,
            'beneficiario' => $beneficiario,
            'cidade' => $cidade,
            'identificador' => $identificador,
            'valor' => $valor
        ];
        
        // Retorna os dados em formato JSON
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'codigo' => $codigoPix,
            'valor' => $valor,
            'valorFormatado' => formatarValorReais($valor),
            'debug' => $debug
        ]);
    } catch (Exception $e) {
        // Em caso de erro, retorna a mensagem para debug
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao gerar código PIX: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Se chegou aqui, exibe o módulo de doação
?>

<!-- Modal de Doação PIX -->
<div class="modal fade" id="doacaoModal" tabindex="-1" aria-labelledby="doacaoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="doacaoModalLabel">Mantenha as Velinhas Acesas!</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-4">
          <img src="/assets/img/vela.png" alt="Velinhas" height="60" class="mb-3">
          <p>Ajude a manter as velinhas acesas com uma doação via PIX.</p>
        </div>
        
        <div class="pix-values mb-4">
          <p class="text-center mb-2">Escolha um valor:</p>
          <div class="d-flex justify-content-center gap-2 mb-3">
            <?php foreach ($valoresSugeridos as $valor): ?>
              <button type="button" class="btn btn-outline-primary valor-pix" data-valor="<?php echo $valor; ?>">
                R$ <?php echo $valor; ?>
              </button>
            <?php endforeach; ?>
          </div>
          <div class="input-group mt-2">
            <span class="input-group-text">R$</span>
            <input type="number" class="form-control" id="valorPersonalizado" placeholder="Outro valor" min="1" max="1000" step="1">
            <button class="btn btn-primary" type="button" id="gerarPixBtn">Gerar PIX</button>
          </div>
        </div>
        
        <!-- Nova linha para mensagem personalizada após acender velinha -->
        <p id="mensagem-pos-vela" class="alert alert-vela">Sua contribuição é muito importante!</p>
        
        <div id="pix-result" class="text-center d-none">
          <div class="qrcode-container mb-3">
            <div id="pixQrCodeContainer" class="mx-auto" style="width: 200px; height: 200px; background-color: white; padding: 10px;"></div>
          </div>
          
          <p class="mb-1">Valor: <strong id="pixValor"></strong></p>
          <p class="mb-3">Beneficiário: <strong><?php echo $beneficiario; ?></strong></p>
          
          <div class="input-group mb-3">
            <input type="text" class="form-control" id="pixCopyCola" readonly>
            <button class="btn btn-outline-secondary" type="button" id="copiarPixBtn">
              <i class="bi bi-clipboard"></i> Copiar
            </button>
          </div>
          
          <div class="alert alert-info">
            <small>
              <i class="bi bi-info-circle"></i> Abra o aplicativo do seu banco, escolha a opção PIX &gt; QR Code ou Copia e Cola.
            </small>
          </div>
          
          <!-- Área de informações de erro -->
          <div id="error-container" class="alert alert-danger d-none mt-3">
            <h6><i class="bi bi-exclamation-triangle-fill"></i> Erro ao gerar PIX</h6>
            <div id="error-details"></div>
          </div>
          
          <div class="accordion accordion-flush mb-3" id="accordionFlushExample">
              <div class="accordion-item">
                <h2 class="accordion-header" id="flush-headingOne">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                    Quem é <strong class="ms-2"><?php echo $beneficiario; ?></strong> <span class="ms-2">(Saber mais)</span>
                  </button>
                </h2>
                <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                  <div class="accordion-body">
                    Gabriel Gortan é o desenvolvedor do Velinhas.com.br</br>Conhenha mais sobre em seu perfil no LinkedIn <a href="https://www.linkedin.com/in/gabrielgortan" target="_blank">aqui</a>
                  </div>
                </div>
              </div>
            </div>
            
            <style>
              /* Ajuste para o acordeão inteiro ser transparente */
              .accordion-item {
                background-color: transparent !important;
              }
              .accordion-button {
                background-color: transparent !important;
                color: var(--bs-text-color, #fff); /* Ajuste para usar a cor do tema */
                border: 1px solid rgba(255, 255, 255, 0.2); /* Borda sutil visível no tema escuro */
              }
            
              .accordion-button:not(.collapsed) {
                background-color: transparent !important;
                color: var(--bs-text-color, #fff); /* Cor do texto ao expandir */
              }
            
              .accordion-button:focus {
                box-shadow: none; /* Remover o foco brilhante */
              }
            
              .accordion-collapse {
                background-color: transparent !important;
              }
            
              .accordion-body {
                color: var(--bs-text-color, #fff); /* Cor do texto dentro do corpo */
              }
            </style>
          
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
        <button type="button" class="btn btn-success" id="voltarValoresBtn" style="display: none;">
          <i class="bi bi-arrow-left"></i> Voltar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Botão para abrir o modal de doação -->
<div class="text-center my-4">
  <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#doacaoModal">
    <i class="bi bi-heart-fill"></i> Ajudar o Velinhas com PIX
  </button>
</div>

<!-- Biblioteca QRCode.js via CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<!-- Script para o módulo de doação -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Elementos do DOM
  const valorPersonalizado = document.getElementById('valorPersonalizado');
  const gerarPixBtn = document.getElementById('gerarPixBtn');
  const valoresBtns = document.querySelectorAll('.valor-pix');
  const pixResult = document.getElementById('pix-result');
  const pixValor = document.getElementById('pixValor');
  const pixCopyCola = document.getElementById('pixCopyCola');
  const pixQrCodeContainer = document.getElementById('pixQrCodeContainer');
  const copiarPixBtn = document.getElementById('copiarPixBtn');
  const voltarValoresBtn = document.getElementById('voltarValoresBtn');
  const errorContainer = document.getElementById('error-container');
  const errorDetails = document.getElementById('error-details');
  
  // Variável para armazenar a instância do QR Code
  let qrcode = null;
  
  // Funções
  function mostrarSecaoValores() {
    document.querySelector('.pix-values').classList.remove('d-none');
    pixResult.classList.add('d-none');
    voltarValoresBtn.style.display = 'none';
    errorContainer.classList.add('d-none');
  }
  
  function mostrarResultadoPix() {
    document.querySelector('.pix-values').classList.add('d-none');
    pixResult.classList.remove('d-none');
    voltarValoresBtn.style.display = 'block';
  }
  
  function mostrarErro(mensagem, detalhes = null) {
    errorContainer.classList.remove('d-none');
    errorDetails.innerHTML = mensagem;
    
    if (detalhes) {
      const detalhesHTML = document.createElement('pre');
      detalhesHTML.className = 'error-json mt-2';
      detalhesHTML.style.fontSize = '12px';
      detalhesHTML.style.maxHeight = '150px';
      detalhesHTML.style.overflow = 'auto';
      detalhesHTML.innerText = JSON.stringify(detalhes, null, 2);
      errorDetails.appendChild(detalhesHTML);
    }
  }
  
  function gerarQRCode(codigoPix) {
    // Limpa o container
    pixQrCodeContainer.innerHTML = '';
    
    // Destroi instância anterior se existir
    if (qrcode !== null) {
      qrcode.clear();
      qrcode = null;
    }
    
    // Cria um novo QR code
    try {
      console.log('Gerando QR Code para:', codigoPix);
      qrcode = new QRCode(pixQrCodeContainer, {
        text: codigoPix,
        width: 180,
        height: 180,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
      });
      
      console.log('QR Code gerado com sucesso');
    } catch (error) {
      console.error('Erro ao gerar QR Code:', error);
      pixQrCodeContainer.innerHTML = '<div class="alert alert-danger">Erro ao gerar QR Code</div>';
      mostrarErro('Falha ao gerar o QR Code: ' + error.message);
    }
  }
  
  function gerarPix(valor) {
    // Validar valor
    valor = parseFloat(valor);
    if (isNaN(valor) || valor < 1 || valor > 1000) {
      alert('Por favor, informe um valor entre R$ 1,00 e R$ 1.000,00');
      return;
    }
    
    // Esconder área de erro se estiver visível
    errorContainer.classList.add('d-none');
    
    // Mostrar indicador de carregamento
    pixQrCodeContainer.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div>';
    pixResult.classList.remove('d-none');
    document.querySelector('.pix-values').classList.add('d-none');
    voltarValoresBtn.style.display = 'block';
    
    // Buscar código PIX via AJAX
    fetch(`/api/pix.php?valor=${valor}`)
      .then(response => {
        if (!response.ok) {
          return response.json().then(data => {
            throw new Error(data.message || `Erro ${response.status}: ${response.statusText}`);
          });
        }
        return response.json();
      })
      .then(data => {
        // Verificar se a resposta tem o código PIX
        if (!data.codigo) {
          throw new Error('Resposta do servidor não contém código PIX');
        }
        
        console.log('Resposta do servidor:', data);
        
        // Preencher dados
        pixValor.textContent = data.valorFormatado;
        pixCopyCola.value = data.codigo;
        
        // Gerar o QR code
        gerarQRCode(data.codigo);
        
        // Mostrar resultado
        mostrarResultadoPix();
      })
      .catch(error => {
        console.error('Erro ao gerar PIX:', error);
        pixQrCodeContainer.innerHTML = `<div class="alert alert-danger">Erro ao gerar PIX</div>`;
        mostrarErro('Erro ao gerar PIX: ' + error.message);
      });
  }
  
  // Event Listeners
  valoresBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      gerarPix(this.dataset.valor);
    });
  });
  
  gerarPixBtn.addEventListener('click', function() {
    gerarPix(valorPersonalizado.value);
  });
  
  valorPersonalizado.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      gerarPix(this.value);
    }
  });
  
  voltarValoresBtn.addEventListener('click', function() {
    mostrarSecaoValores();
  });
  
  copiarPixBtn.addEventListener('click', function() {
    pixCopyCola.select();
    document.execCommand('copy');
    
    // Feedback visual
    const originalText = this.innerHTML;
    this.innerHTML = '<i class="bi bi-check-lg"></i> Copiado!';
    
    setTimeout(() => {
      this.innerHTML = originalText;
    }, 2000);
  });
  
  // Resetar modal quando for fechado
  document.getElementById('doacaoModal').addEventListener('hidden.bs.modal', function() {
    mostrarSecaoValores();
    valorPersonalizado.value = '';
  });
});


// Adicione este script ao final do script existente
// para detectar quando o modal foi aberto por código externo
document.addEventListener('DOMContentLoaded', function() {
  // Obtenha o elemento do modal
  const doacaoModal = document.getElementById('doacaoModal');
  
  // Configure evento para quando o modal for aberto
  doacaoModal.addEventListener('show.bs.modal', function() {
    // Verifica se o modal está sendo aberto normalmente ou após acender uma vela
    const afterLighting = doacaoModal.getAttribute('data-after-lighting') === 'true';
    const mensagemElement = document.getElementById("mensagem-pos-vela");
    
    if (afterLighting) {
      // Configura a mensagem para quando aberto após acender vela
      if (mensagemElement) {
        mensagemElement.textContent = "Obrigado por acender uma velinha! Que tal ajudar o Velinhas a se manter aceso?";
        mensagemElement.classList.add('alert-success');
        mensagemElement.classList.remove('alert-vela');
      }
      
      // Reseta o atributo para futuras aberturas
      doacaoModal.setAttribute('data-after-lighting', 'false');
    } else {
      // Mensagem padrão para abertura manual do modal
      if (mensagemElement) {
        mensagemElement.textContent = "Sua contribuição é muito importante!";
        mensagemElement.classList.add('alert-vela');
        mensagemElement.classList.remove('alert-success');
      }
    }
  });
});


</script>

<!-- Estilos para o módulo de doação -->
<style>
.valor-pix {
  min-width: 80px;
}

#pixQrCodeContainer {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 0 auto;
  background-color: white;
}

#pixQrCodeContainer img {
  display: block;
  max-width: 100%;
  height: auto;
}

.pix-values {
  transition: all 0.3s ease;
}

#pix-result {
  transition: all 0.3s ease;
}

.btn-warning {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.btn-warning:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

.error-json {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  padding: 8px;
  white-space: pre-wrap;
  word-break: break-all;
}
</style>