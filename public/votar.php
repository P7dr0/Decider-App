<?php 
// Pega o código da sala da URL (ex: votar.php?sala=X92A)
$sala = $_GET['sala'] ?? '';
if(!$sala) die("Sala inválida!");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votação - Sala <?= $sala ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
    <style>
        #card-container{
            touch-action: none;
            user-select: none;
            transform-origin: 50% 100%;
        }
        .rest-anim{
            transition: transform 0.3s ease-out;
        }
    </style>
</head>
<body class="bg-gray-900 text-white h-screen flex flex-col items-center justify-center overflow-hidden">
    <div id="luz-esquerda" class="fixed inset-y-0 left-0 w-32 bg-gradient-to-r from-red-600/60 to-transparent pointer-events-none opacity-0 transition-opacity duration-100 z-0"></div>
    
    <div id="luz-direita" class="fixed inset-y-0 right-0 w-32 bg-gradient-to-l from-green-600/60 to-transparent pointer-events-none opacity-0 transition-opacity duration-100 z-0"></div>


    <div class="absolute top-4 right-4 bg-gray-800 px-4 py-2 rounded-lg">
        Sala: <span class="font-bold text-pink-500"><?= htmlspecialchars($sala) ?></span>
    </div>

    <div id="card-container" class="relative w-80 h-[450px] bg-gray-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-gray-700 group cursor-grab active:cursor-grabbing">
        
        <div id="frente-card" class="absolute inset-0 z-10 transition-transform duration-500 bg-gray-900">
            <img id="img-filme" src="" draggable="false" class="w-full h-full object-cover opacity-80 group-hover:opacity-60 transition-opacity">
            
            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>

            <button onclick="toggleSinopse()" class="absolute top-4 right-4 bg-black/50 hover:bg-pink-600 text-white p-2 rounded-full backdrop-blur-sm border border-white/20 transition transform hover:scale-110 z-20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>

            <div class="absolute bottom-0 w-full p-6 text-center">
                <h2 id="titulo-filme" class="text-2xl font-bold text-white drop-shadow-lg leading-tight">
                    Carregando...
                </h2>
                <!-- <p class="text-xs text-gray-400 mt-1 uppercase tracking-widest">Toque no (i) para saber mais</p> -->
            </div>
        </div>

        <div id="verso-card" class="absolute inset-0 z-20 bg-gray-900 p-8 flex flex-col items-center justify-center text-center opacity-0 pointer-events-none transition-opacity duration-300">
            <h3 class="text-pink-500 font-bold mb-4 text-xl uppercase tracking-wider">Sinopse</h3>
            <p id="desc-filme" class="text-gray-300 text-base leading-relaxed overflow-y-auto max-h-[300px] scrollbar-hide">
                </p>
            <button onclick="toggleSinopse()" class="mt-6 bg-gray-700 hover:bg-gray-600 px-6 py-2 rounded-full text-sm font-bold transition">
                Voltar
            </button>
        </div>

        <div id="fim-msg" class="hidden absolute inset-0 flex items-center justify-center bg-gray-800 z-30 text-center p-4">
             </div>
    </div>

    <div class="mt-8 flex gap-6">
        <button onclick="votar(0)" class="bg-red-500 hover:bg-red-600 text-white p-6 rounded-full shadow-lg text-2xl transition hover:scale-110">❌</button>
        <button onclick="votar(1)" class="bg-green-500 hover:bg-green-600 text-white p-6 rounded-full shadow-lg text-2xl transition hover:scale-110">💚</button>
    </div>

    <div id="modal-match" class="hidden fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50">
        <div class="text-center">
            <h1 class="text-6xl mb-4">IT'S A MATCH! 😍</h1>
            <img id="img-match" src="" class="w-64 h-96 object-cover rounded-lg border-4 border-pink-500 mx-auto mb-4">
            <h2 id="titulo-match" class="text-3xl font-bold text-pink-500"></h2>
            <div id="streaming-area" class="mt-4 min-h-[60px]">
                <button id="btn-onde-assistir" onclick="carregarProviders()" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-full font-bold text-sm transition shadow-lg flex items-center gap-2 mx-auto">
                    📺 Onde Assistir?
                </button>
                
                <div id="lista-providers" class="hidden mt-3 flex justify-center gap-3 flex-wrap">
                    <p class="text-gray-400 text-sm w-full">Carregando...</p>
                </div>
            </div>
            <p class="mt-4 text-gray-300">Prepare a pipoca!</p>
        </div>
    </div>

    <script>
        const salaCode = "<?= $sala ?>";
        let currentItemId = null;
        // Variáveis globais para o streaming
        let matchTmdbId = null;
        let matchCategory = null;
        
        let userId = localStorage.getItem('decisor_user_id');

        if (!userId) {
            userId = 'user_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('decisor_user_id', userId);
        }

        const luzEsq = document.getElementById('luz-esquerda');
        const luzDir = document.getElementById('luz-direita');
        const card = document.getElementById('card-container');
        const hammer = new Hammer(card);

        // --- FUNÇÕES DE INTERFACE ---

        function toggleSinopse() {
            const frente = document.getElementById('frente-card');
            const verso = document.getElementById('verso-card');
            if (verso.classList.contains('opacity-0')) {
                verso.classList.remove('opacity-0', 'pointer-events-none');
                frente.classList.add('opacity-10');
            } else {
                verso.classList.add('opacity-0', 'pointer-events-none');
                frente.classList.remove('opacity-10');
            }
        }

        // --- FUNÇÕES DO MATCH E STREAMING (NOVAS) ---

        function matchEncontrado(filme) {
            // Para o espião para não ficar recarregando a tela
            clearInterval(checkMatchInterval);

            // Salva dados globais
            matchTmdbId = filme.tmdb_id;
            matchCategory = filme.category;

            document.getElementById('modal-match').classList.remove('hidden');
            document.getElementById('titulo-match').innerText = filme.title;
            document.getElementById('img-match').src = filme.image_url;
            
            // Reseta a área de streaming (Botão aparece, lista some)
            document.getElementById('btn-onde-assistir').classList.remove('hidden');
            document.getElementById('lista-providers').classList.add('hidden');
            document.getElementById('lista-providers').innerHTML = '<p class="text-gray-400 text-sm w-full">Carregando...</p>';

            if(navigator.vibrate) navigator.vibrate([200, 100, 200]);
        }

        async function carregarProviders() {
            const btn = document.getElementById('btn-onde-assistir');
            const lista = document.getElementById('lista-providers');
            
            btn.classList.add('hidden');
            lista.classList.remove('hidden');

            try {
                // Chama sua API nova
                const res = await fetch(`api_providers.php?id=${matchTmdbId}&type=${matchCategory}`);
                const data = await res.json();

                lista.innerHTML = ''; 

                if (data.length === 0) {
                    lista.innerHTML = '<p class="text-gray-400 text-sm">Não encontrado em streamings comuns 😢</p>';
                    return;
                }

                data.forEach(prov => {
                    const img = document.createElement('img');
                    img.src = prov.logo;
                    img.title = prov.name;
                    img.className = "w-16 h-16 rounded-xl shadow-md border border-gray-700 transition transform hover:scale-110";
                    lista.appendChild(img);
                });

            } catch (e) {
                lista.innerHTML = '<p class="text-red-400 text-sm">Erro ao carregar</p>';
            }
        }

        // --- LÓGICA DO SWIPE (HAMMER) ---

        hammer.get('pan').set({ direction: Hammer.DIRECTION_ALL, threshold: 10 });

        hammer.on("pan", (event) => {
            if(event.target.closest('button')) return;
            
            const xPos = event.deltaX;
            const yPos = event.deltaY;
            const rotate = xPos * 0.1;

            card.classList.remove('reset-anim');
            card.style.transform = `translate(${xPos}px, ${yPos}px) rotate(${rotate}deg)`;

            const opacidade = Math.min(Math.abs(xPos) / 150, 0.8);

            if (xPos > 0) {
                luzDir.style.opacity = opacidade;
                luzEsq.style.opacity = 0;
            } else {
                luzEsq.style.opacity = opacidade;
                luzDir.style.opacity = 0;
            }
        });

        hammer.on("panend", (event) => {
            luzEsq.style.opacity = 0;
            luzDir.style.opacity = 0;
            const moveuPouco = Math.abs(event.deltaX) < 100;

            if(moveuPouco){
                card.classList.add('reset-anim');
                card.style.transform = '';
            }else{
                const voto = event.deltaX > 0 ? 1 : 0;
                const destinoX = voto === 1 ? window.innerWidth : - window.innerWidth;
                card.classList.add('reset-anim');
                card.style.transform = `translate(${destinoX}px, 100px) rotate(${voto === 1 ? 45 : -45}deg)`;
                setTimeout(() => votar(voto),300);
            }
        });

        function animarVoto(voto) {
            const moveX = voto === 1 ? window.innerWidth : -window.innerWidth;
            if(voto === 1) {
                luzDir.style.opacity = 1;
                setTimeout(() => luzDir.style.opacity = 0, 300);
            } else {
                luzEsq.style.opacity = 1;
                setTimeout(() => luzEsq.style.opacity = 0, 300);
            }
            card.classList.add('reset-anim');
            card.style.transform = `translate(${moveX}px, 100px) rotate(${voto === 1 ? 45 : -45}deg)`;
            setTimeout(() => votar(voto), 300);
        }

        // --- LÓGICA DO APP (BACKEND) ---

        async function carregarProximo() {
            card.style.transition = 'none';
            card.style.transform = 'scale(0.95)';
            card.style.opacity = '0';
            
            const response = await fetch(`api_votar.php?user_id=${userId}&room=${salaCode}`);
            const data = await response.json();

            if (data.error) {
                document.getElementById('card-container').innerHTML = "<div class='h-full flex items-center justify-center text-center p-4'><h3>Acabaram as opções! <br>Aguarde o match...</h3></div>";
                document.querySelector('.flex.gap-6').style.display = 'none';
                return;
            }

            currentItemId = data.id;
            document.getElementById('titulo-filme').innerText = data.title;
            document.getElementById('img-filme').src = data.image_url;
            document.getElementById('desc-filme').innerText = data.description || "Sinopse indisponível.";

            document.getElementById('verso-card').classList.add('opacity-0', 'pointer-events-none');
            document.getElementById('frente-card').classList.remove('opacity-10');

            setTimeout(() => {
                card.classList.add('reset-anim');
                card.style.transform = '';
                card.style.opacity = '1';
            },100);
        }

        async function votar(voto) {
            if (!currentItemId) return;

            const res = await fetch('api_votar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    room: salaCode, user: userId, item: currentItemId, vote: voto
                })
            });

            const data = await res.json();

            if (data.match) {
                // Chama a nova função centralizada
                matchEncontrado(data.data);
            } else {
                carregarProximo();
            }
        }

        // ESPIÃO DE MATCH (Guardado na variável checkMatchInterval)
        const checkMatchInterval = setInterval(async function() {
            if(!document.getElementById('modal-match').classList.contains('hidden')){
                return;
            }
            try{
                const res = await fetch(`api_votar.php?check=1&room=${salaCode}`);
                const data = await res.json();

                if(data.match){
                    // Chama a nova função centralizada
                    matchEncontrado(data.data);
                }
            }catch(e){ console.log(e); }
        }, 3000);

        // Inicializa
        carregarProximo();
    </script>
</body>
</html>