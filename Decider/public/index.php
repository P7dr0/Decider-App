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
        <h1 class="text-4xl font-bold mb-4 text-pink-500">Decisor de Casal ❤️</h1>
        <p class="text-gray-300 mb-8">Chega de brigar pra escolher filme ou comida.</p>

        <a href="../criar_sala.php" 
           class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-4 px-8 rounded-full text-xl shadow-lg transition transform hover:scale-105">
            🔥 Criar Nova Sessão
        </a>
        
        <p class="mt-6 text-sm text-gray-500">
            Crie uma sala e mande o link pro seu amor.
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
                   placeholder="Digite o código (ex: X9A2)" 
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