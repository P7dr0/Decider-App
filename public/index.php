<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decisor de Casal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white h-screen flex flex-col items-center justify-center">

    <div class="text-center p-6">
        <h1 class="text-4xl font-bold mb-4 text-pink-500">Decisor do Casal❤️</h1>
        <!-- <p class="text-gray-300 mb-8">Chega de brigar pra escolher filme ou comida.</p> ADIÇÃO DA OPÇÃO DE COMIDA FUTURAMENTE-->
        <p class="text-gray-300 mb-8">Chega de brigar pra escolher o filme.</p>
        <div>
            <form action="../criar_sala.php" method="post">
                <label class="block text-gray-400 text-sm mb-2 font-bold text-left">Filme ou Série?</label>
                <select name="tipo_midia" class="w-full bg-gray-900 text-white border border-gray-600 rounded-lg p-4 mb-4 focus:border-pink-500 outline-none">
                    <option value="ambos">Os dois🍿</option>
                    <option value="filme">Filmes🎬</option>
                    <option value="serie">Séries📺</option>
                </select>

                <label class="block text-gray-400 text-sm mb-2 font-bold text-left">Escolha o clima de hoje:</label>
                <select name="genero" class="w-full bg-gray-900 text-white border border-gray-600 rounded-lg p-4 mb-4 focus:border-pink-500 outline-none">
                    <option value="">🎲Todos</option>
                    <option value="acao">💥Ação</option>
                    <option value="aventura">🤠Aventura</option>
                    <option value="cinema">📺Cinema TV</option>
                    <option value="crime">🚓Crime</option>
                    <option value="comedia">😂Comédia</option>
                    <option value="documentario">📹Documentário</option>
                    <option value="drama">🎭Drama</option>
                    <option value="faroeste">🐎Faroeste</option>
                    <option value="familia">👨‍👩‍👧‍👦Família</option>
                    <option value="fantasia">🧙‍♂️Fantasia</option>
                    <option value="ficcao cientifica">👽Ficção científica</option>
                    <option value="guerra">⚔️Guerra</option>
                    <option value="historia">📜Hitória</option>
                    <option value="musica">🎵Música</option>
                    <option value="misterio">🕵️‍♂️Mistério</option>
                    <option value="Romance">🌹Romance</option>
                    <option value="terror">👻Terror</option>
                    <option value="thriller">🔪Thriller</option>
                    <option value="animacao">🎨Animação</option>
                </select>
                <button type="submit" class="block w-full bg-gradient-to-r from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 text-white font-bol py-4 rounded-xl text-xl shadow-lg transition hover:-translate-y-1">🔥Criar Sala</button>
            </form>
        </div>
        <!-- <a href="../criar_sala.php" 
           class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-4 px-8 rounded-full text-xl shadow-lg transition transform hover:scale-105">
            🔥 Criar Nova Sessão
        </a> -->
        
        <p class="mt-6 text-sm text-gray-500">
            Crie uma sala e mande o código pro seu amor.
        </p>
    </div>
    <div class="relative flex py-2 items-center">
            <div class="flex-grow border-t border-gray-700"></div>
            <span class="flex-shrink-0 mx-4 text-gray-500 text-sm">OU ENTRE NUMA SALA</span>
            <div class="flex-grow border-t border-gray-700"></div>
        </div>

        <form action="votar.php" method="GET" class="flex shadow-lg">
            <input type="text" 
                   name="sala" 
                   placeholder="Código da Sala" 
                   class="w-full bg-gray-800 text-white px-6 py-4 rounded-l-xl border border-gray-700 focus:outline-none focus:border-pink-500 uppercase tracking-widest placeholder-gray-600"
                   required
                   maxlength="10">
            
            <button type="submit" 
                    class="bg-gray-700 hover:bg-gray-600 text-white px-6 rounded-r-xl font-bold border border-l-0 border-gray-700 transition">
                Entrar
            </button>
        </form>

    </div>

</body>
</html>