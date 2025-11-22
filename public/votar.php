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
</head>
<body class="bg-gray-900 text-white h-screen flex flex-col items-center justify-center overflow-hidden">

    <div class="absolute top-4 right-4 bg-gray-800 px-4 py-2 rounded-lg">
        Sala: <span class="font-bold text-pink-500"><?= htmlspecialchars($sala) ?></span>
    </div>

    <div id="card-container" class="relative w-80 h-[450px] bg-gray-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden">
        <img id="img-filme" src="" class="w-full h-3/4 object-cover">
        <div class="h-1/4 flex items-center justify-center p-4 text-center">
            <h2 id="titulo-filme" class="text-2xl font-bold">Carregando...</h2>
        </div>
    </div>

    <div class="mt-8 flex gap-6">
        <button onclick="votar(0)" class="bg-red-500 hover:bg-red-600 text-white p-6 rounded-full shadow-lg text-2xl transition hover:scale-110">❌</button>
        <button onclick="votar(1)" class="bg-green-500 hover:bg-green-600 text-white p-6 rounded-full shadow-lg text-2xl transition hover:scale-110">💚</button>
    </div>

    <div id="modal-match" class="hidden fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50">
        <div class="text-center animate-bounce">
            <h1 class="text-6xl mb-4">IT'S A MATCH! 😍</h1>
            <img id="img-match" src="" class="w-64 h-96 object-cover rounded-lg border-4 border-pink-500 mx-auto mb-4">
            <h2 id="titulo-match" class="text-3xl font-bold text-pink-500"></h2>
            <p class="mt-4 text-gray-300">Prepare a pipoca!</p>
        </div>
    </div>

    <script>
        const salaCode = "<?= $sala ?>";
        let currentItemId = null;
        let userId = localStorage.getItem('decisor_user_id');

        // 1. Gera ID do usuário se não existir (Login fake)
        if (!userId) {
            userId = 'user_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('decisor_user_id', userId);
        }

        // 2. Função para carregar o próximo filme
        async function carregarProximo() {
            const response = await fetch(`api_votar.php?user_id=${userId}`);
            const data = await response.json();

            if (data.error) {
                document.getElementById('card-container').innerHTML = "<div class='h-full flex items-center justify-center text-center p-4'><h3>Acabaram os filmes! <br>Aguarde o match...</h3></div>";
                document.querySelector('.flex.gap-6').style.display = 'none'; // Esconde botões
                return;
            }

            // Atualiza a tela
            currentItemId = data.id;
            document.getElementById('titulo-filme').innerText = data.title;
            document.getElementById('img-filme').src = data.image_url;
        }

        // 3. Função de Votar
        async function votar(voto) {
            if (!currentItemId) return;

            // Envia o voto pro backend
            const res = await fetch('api_votar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    room: salaCode,
                    user: userId,
                    item: currentItemId,
                    vote: voto
                })
            });

            const data = await res.json();

            if (data.match) {
                // SE DEU MATCH: Mostra a tela de comemoração!
                document.getElementById('modal-match').classList.remove('hidden');
                document.getElementById('titulo-match').innerText = data.data.title;
                document.getElementById('img-match').src = data.data.image_url;
                // Dispara confetes (imaginários por enquanto)
            } else {
                // SE NÃO: Carrega o próximo
                carregarProximo();
            }
        }

        // Inicia carregando o primeiro
        carregarProximo();

        setInterval(async function(params) {
            if(!document.getElementById('modal-match').classList.contains('hidden')){
                return;
            }

            try{
                const res = await fetch(`api_votar.php?check=1&room=${salaCode}`);
                const data = await res.json();

                if(data.match){
                    document.getElementById('modal-match').classList.remove('hidden');
                    document.getElementById('titulo-match').innerText = data.data.title;
                    document.getElementById('img-match').src = data.data.image_url;
                    
                    //Som de match! Comentar se quiser deixar mudo.
                    if(navigator.vibrate) navigator.vibrate([200, 100, 200]);
                }
            }catch(e){
                console.log("Erro! Verificar match silencioso",e);
            }
        },3000);
        carregarProximo();
    </script>
</body>
</html>