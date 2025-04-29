<!DOCTYPE html>
<html lang="pt-br">
<?php require 'head.php' ?>
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
              <a class="nav-link active" href="#capela">Capela de Velas</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#footer">Sobre</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="changelog.php">Changelog</a>
            </li>
          </ul>
          <button class="btn btn-acender" data-bs-toggle="modal" data-bs-target="#velaModal"> Acender Velinha</button>
        </div>
      </div>
    </nav>
    <div class="container text-center mb-4">
      <div class="alert alert-vela mb-4 mt-1" role="alert">
        <strong>Novidade:</strong> Velas Virtuais agora é <strong>Velinhas.com.br</strong>! Acenda a sua velinha e ore suas preces 🙏
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
          <div class="d-flex align-items-center">
            <label for="autoUpdate" class="form-check-label me-2" style="color: white;">
              <i class="bi bi-arrow-clockwise"></i>
            </label>
            <input type="checkbox" id="atualizarVelas" class="form-check-input">
          </div>
        </div>
        <div class="card-body capela-card-body">
        <p>Temos <span id="badgeVelasAcesas" class="badge rounded-pill text-bg-secondary"></span> velas acesas na capela!</p>
          <div id="velas-container" class="mt-4 d-flex flex-wrap justify-content-center"></div>
        </div>
        <div class="card-footer text-body-secondary capela-card-footer" style="color:645032;">
          <b>ATENÇÃO: Após acender uma vela não é possível apagar. Cuidado para não se queimar</b>
        </div>
      </div>
      <div class="sponsor-container" id="patrocinador" style="display: none;">
        <div class="sponsor-label">Patrocinador Oficial</div>
        <div class="scroll-container">
          <div class="scroll-content">
          </div>
          <div class="arrow-button arrow-left" onclick="moveLeft()">
            <i class="bi bi-arrow-left-square-fill"></i>
          </div>
          <div class="arrow-button arrow-right" onclick="moveRight()">
            <i class="bi bi-arrow-right-square-fill"></i>
          </div>
        </div>
        <small>Encomendas para Piracicaba/SP e região.</small>
      </div>
    </div>
    <div class="modal fade" id="velaModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Acender uma Vela</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="velaForm">
              <!--div id="aviso-acender-vela" class="alert alert-info"></div -->
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
                      <img src="https://velinhas.com.br/img/vela0.png" alt="Vela Branca" width="40" class="img-fluid rounded border border-0 img-radio">
                    </label>
                    <div class="d-block">
                      <label for="imgRadio0">Vela Branca</label>
                    </div>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tipoVela" id="imgRadio1" value="vela1">
                    <label class="form-check-label" for="imgRadio1">
                      <img src="https://velinhas.com.br/img/vela1.png" alt="Vela 1" width="40" class="img-fluid rounded border border-0 img-radio">
                    </label>
                    <div class="d-block">
                      <label for="imgRadio1">Vela 1</label>
                    </div>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tipoVela" id="imgRadio2" value="vela2">
                    <label class="form-check-label" for="imgRadio2">
                      <img src="https://velinhas.com.br/img/vela2.png" alt="Vela 2" width="40" class="img-fluid rounded border border-0 img-radio">
                    </label>
                    <div class="d-block">
                      <label for="imgRadio2">Vela 2</label>
                    </div>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tipoVela" id="imgRadio3" value="vela3">
                    <label class="form-check-label" for="imgRadio3">
                      <img src="https://velinhas.com.br/img/vela3.png" alt="Vela 3" width="40" class="img-fluid rounded border border-0 img-radio">
                      <span class="badge bg-success" style="font-size: 0.6rem; padding: 0.2em 0.4em;">Novidade!</span>
                    </label>
                    <div class="d-block">
                      <label for="imgRadio3">Vela 3</label>
                    </div>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tipoVela" id="corRadio" value="cor">
                    <label class="form-check-label" for="corRadio"><span class="badge text-bg-secondary">Cor Personalizada</span></label>
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
            <div class="alert alert-warning" role="alert">
              <b>ATENÇÃO:</b> Após acender uma vela não é possível apagar. Cuidado para não se queimar
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="novoModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
        </div>
      </div>
    </div>
    <div class="toast-container">
      <div id="toastMessage" class="toast border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">🕊 Mensagem Divina</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
        <div class="toast-body"> Crie velas com moderação e respeito. </div>
      </div>
    </div>
<?php require 'footer.php' ?>
  </body>
</html>