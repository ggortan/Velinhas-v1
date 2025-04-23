/**
 * Service Worker para o site Velinhas
 * Permite funcionalidade offline básica e melhor desempenho
 */

// Nome e versão do cache
const CACHE_NAME = 'velinhas-cache-v1';

// Arquivos para cache inicial (arquivos estáticos)
const INITIAL_CACHE_URLS = [
  '/',
  '/index.php',
  '/offline.html',
  '/assets/css/style.css',
  '/assets/js/script.js',
  '/assets/img/vela.png',
  '/assets/img/vela0.png',
  '/assets/img/vela1.png',
  '/assets/img/vela2.png',
  '/assets/img/vela3.png',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'
];

// Instalação do Service Worker
self.addEventListener('install', event => {
  // Faz o novo service worker tomar controle imediatamente
  self.skipWaiting();
  
  // Pré-carrega os arquivos estáticos
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      console.log('Cache aberto');
      return cache.addAll(INITIAL_CACHE_URLS);
    })
  );
});

// Ativação do Service Worker
self.addEventListener('activate', event => {
  // Toma controle de todos os clientes não controlados
  event.waitUntil(clients.claim());
  
  // Remove caches antigos
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.filter(cacheName => {
          return cacheName !== CACHE_NAME;
        }).map(cacheName => {
          console.log('Removendo cache antigo:', cacheName);
          return caches.delete(cacheName);
        })
      );
    })
  );
});

// Estratégia de cache e rede para todos os recursos
self.addEventListener('fetch', event => {
  // Ignora requisições não GET
  if (event.request.method !== 'GET') return;
  
  // Ignora requisições a APIs (deixa que o cache de aplicação cuide disso)
  if (event.request.url.includes('/api/')) {
    return;
  }
  
  // Ignora solicitações de extensões de navegador
  if (!(event.request.url.startsWith('http://') || 
        event.request.url.startsWith('https://'))) {
    return;
  }
  
  // Estratégia "Cache First, fallback para Network"
  event.respondWith(
    caches.match(event.request).then(cachedResponse => {
      // Retorna do cache se disponível
      if (cachedResponse) {
        // Em segundo plano, tenta atualizar o cache
        event.waitUntil(
          fetch(event.request).then(response => {
            return caches.open(CACHE_NAME).then(cache => {
              cache.put(event.request, response.clone());
              return response;
            });
          }).catch(() => {
            // Falha silenciosa se não puder atualizar
          })
        );
        
        return cachedResponse;
      }
      
      // Se não estiver no cache, busca na rede e atualiza o cache
      return fetch(event.request).then(response => {
        // Não armazena em cache respostas falhas
        if (!response || response.status !== 200 || response.type !== 'basic') {
          return response;
        }
        
        // Armazena a resposta no cache
        const responseToCache = response.clone();
        caches.open(CACHE_NAME).then(cache => {
          cache.put(event.request, responseToCache);
        });
        
        return response;
      }).catch(error => {
        // Se a rede falhar, retorna uma página offline
        console.error('Erro ao buscar recurso:', error);
        
        // Para imagens, retorna uma imagem padrão
        if (event.request.url.match(/\.(jpg|jpeg|png|gif|svg)$/)) {
          return caches.match('/assets/img/offline.png');
        }
        
        // Para HTML, retorna a página offline
        return caches.match('/offline.html');
      });
    })
  );
});

// Sincronização em segundo plano
self.addEventListener('sync', event => {
  if (event.tag === 'sync-velas') {
    event.waitUntil(
      // Aqui implementaria a sincronização de velas pendentes
      console.log('Sincronizando velas...')
    );
  }
});

// Notificações push
self.addEventListener('push', event => {
  if (!event.data) return;
  
  const data = event.data.json();
  
  // Mostra uma notificação
  event.waitUntil(
    self.registration.showNotification('Velinhas', {
      body: data.message || 'Nova atualização nas velinhas!',
      icon: '/assets/img/vela.png',
      badge: '/assets/img/vela.png'
    })
  );
});

// Clique em notificação
self.addEventListener('notificationclick', event => {
  event.notification.close();
  
  // Abre a aplicação quando o usuário clica na notificação
  event.waitUntil(
    clients.matchAll({type: 'window'}).then(windowClients => {
      // Se já estiver aberto, foca nele
      for (let client of windowClients) {
        if (client.url.includes('velinhas.com.br') && 'focus' in client) {
          return client.focus();
        }
      }
      
      // Se não estiver aberto, abre uma nova aba
      if (clients.openWindow) {
        return clients.openWindow('/');
      }
    })
  );
});