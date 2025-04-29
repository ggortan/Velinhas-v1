<!DOCTYPE html>
<html lang="pt-br">
<?php require 'head.php' ?>
   <body>
         <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
            <div class="container-fluid">
              <!-- Logo e nome do site -->
              <a class="navbar-brand" href="#">
                <img src="https://velinhas.com.br/img/vela.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top">
                Velinhas
              </a>
              <!-- Botão de menu para dispositivos pequenos -->
              <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
              </button>
              <!-- Itens de navegação -->
              <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                  <li class="nav-item">
                    <a class="nav-link" href="https://velinhas.com.br">Capela de Velas</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="#footer">Sobre</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link active" href="changelog.php">Changelog</a>
                  </li>
                </ul>
              </div>
            </div>
          </nav>
      <div class="container mb-4">
         <h1 class="text-center">Changelog 🕯</h1>
            <div class="container py-5">
               <div class="changelog-item">
                  <div class="version-header">
                     <h4>Versão 3.1.0</h4>
                  </div>
                  <ul>
                     <li>Implementação da funcionalidade de reação nas velas.</li>
                  </ul>
               </div>
               <div class="changelog-item">
                  <div class="version-header">
                     <h4>Versão 3.0.3</h4>
                  </div>
                  <ul>
                     <li>Correção de bugs</li>
                  </ul>
               </div>
               <div class="changelog-item">
                  <div class="version-header">
                     <h4>Versão 3.0.1</h4>
                  </div>
                  <ul>
                     <li>Velas Virtuais agora é <strong>Velinhas.com.br</strong> <span class="badge text-bg-success">Novidade!<span></span></li>
                     <li>Migração do serviço.</li>
                     <li>Reorganização dos diretorios.</li>
                  </ul>
               </div>
               <div class="changelog-item">
                  <div class="version-header">
                     <h4>Versão 2.1.4</h4>
                  </div>
                  <ul>
                     <li>Correção de bugs.</li>
                  </ul>
               </div>
               <div class="changelog-item">
                  <div class="version-header">
                     <h4>Versão 2.1.0</h4>
                  </div>
                  <ul>
                     <li>Alteração visual da pagina inical (cores e elementos).</li>
                     <li>Implementação do bloco de patrocinador oficial.</li>
                     <li>Implementação da vela publicitária <span class="badge text-bg-primary">Beta<span></li>
                     <li>Correção de bugs.</li>
                     <li>Alteração do rótulo "Atualização Automatica" para icone.</li>
                  </ul>
               </div>
               <div class="changelog-item">
                  <div class="version-header">
                     <h4>Versão 2.0.0</h4>
                  </div>
                  <ul>
                     <li>Implementação de regras anti-spam</li>
                     <li>Implementação de mensagens de retorno em formato de alertas.</li>
                     <li>Correção de bugs.</li>
                     <li>Restauração da capela de velas após incêndio no servidor.</li>
                  </ul>
               </div>
               <div class="changelog-item">
                  <div class="version-header">
                     <h4>Versão 1.3.0</h4>
                  </div>
                  <ul>
                     <li>Adição de velas personalizadas</li>
                     <li>Correção de bugs no carregamento das páginas principais relacioandos a capela de velas.</li>
                  </ul>
               </div>
               <div class="changelog-item">
                  <div class="version-header">
                     <h4>Versão 1.2.0</h4>
                  </div>
                  <ul>
                     <li>Modificaão no modal de criação de velas</li>
                     <li>Implementação da capacidade na capela de velas</li>
                     <li>Adição da barra de navegação e melhorias de responsividade</li>
                  </ul>
               </div>
               <div class="changelog-item">
                  <div class="version-header">
                     <h4>Versão 1.1.0</h4>
                  </div>
                  <ul>
                     <li>Criação da funcionalidade de personalização de velas</li>
                     <li>Implementação da funcionalidade de autialização automatica da capela de velas</li>
                     <li>Design responsivo usando Bootstrap 5.</li>
                  </ul>
               </div>
               <div class="changelog-item">
                  <div class="version-header">
                     <h4>Versão 1.0.0</h4>
                  </div>
                  <ul>
                     <li>Lançamento do Velas Virtuais</li>
                  </ul>
               </div>
            </div>
      </div>
    <?php require 'footer.php' ?>
   </body>
</html>