<?php
require_once __DIR__ . "/../src/conecta.php";
set_time_limit(300);
$mensagem = '';
$tipoMensagem = '';
// php public/importar.php comando para dodar via terminal
// SE ESTIVER RODANDO PELO TERMINAL (CLI)
if (php_sapi_name() === 'cli') {
    // Simula um POST com sua chave fixa pra não dar erro
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST['api_key'] = 'c942e42c5709ce00fe6772c631e4c760'; 
    $paginas = 50; // Aqui no terminal pode abusar! Põe 10, 20...
    echo "Rodando via Terminal... Aguarde...\n";
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $apiKey = $_POST['api_key'] ?? '';
    // $paginas = 2;

    if(empty($apiKey)){
        $mensagem = "Por favor insira a chave de api";
        $tipoMensagem = "erro";
    }else{
        $mapaGeneros = [];

        function carregaGeneros($url,&$mapa){
            $resp = @file_get_contents($url);
            if ($resp) {
                $json = json_decode($resp, true);
                foreach ($json['genres'] as $g) {
                    $mapa[$g['id']] = $g['name'];
                }
            }
        }

        carregaGeneros("https://api.themoviedb.org/3/genre/movie/list?api_key={$apiKey}&language=pt-BR",$mapaGeneros);
        carregaGeneros("https://api.themoviedb.org/3/genre/tv/list?api_key={$apiKey}&language=pt-BR",$mapaGeneros);
        // $respGeneros = @file_get_contents($urlGeneros);


        try{
            $pdo = conexao::conectar();

            $pdo->query('TRUNCATE TABLE items');

            $sql = "INSERT INTO items (title, image_url, category, genre, description, tmdb_id) VALUES (:titulo, :img, :cat, :genre, :desc, :tid)";
            $stmt = $pdo->prepare($sql);

            $contagem = 0;

            for($i = 1;$i <= $paginas;$i++){
                $apiUrl = "https://api.themoviedb.org/3/trending/all/week?api_key={$apiKey}&language=pt-BR&page={$i}";
                $resposta = @file_get_contents($apiUrl);

                if($resposta){
                    $dados = json_decode($resposta,true);
                    foreach($dados['results'] as $filme){
                        
                        if(empty($filme['poster_path']) || $filme['media_type'] == 'person'){
                            continue;
                        }

                        $titulo = $filme['title'] ?? $filme['name'] ?? "Sem Título";
                        $categoria = ($filme['media_type'] == 'tv') ? 'serie' : 'filme';

                        $idGenero = $filme['genre_ids'][0] ?? 0;
                        $nomeGenero = $mapaGeneros[$idGenero] ?? 'Outros';
                        $sinopse = $filme['overview'] ?? 'Sinopse não disponível.';

                        if(!empty($filme['poster_path'])){
                            $imagemCompleta = "https://image.tmdb.org/t/p/w500".$filme['poster_path'];

                            $stmt->execute([
                                'titulo' => $titulo,
                                'img'    => $imagemCompleta,
                                'cat'    => $categoria, // Salva 'filme' ou 'serie'
                                'genre' => $nomeGenero,
                                'desc'   => $sinopse,
                                'tid'    => $filme['id']
                            ]);
                            $contagem++;
                        }
                    }
                }
                sleep(1);
            }


            $mensagem = "Sucesso! $contagem filmes foram importados.";
            $tipoMensagem = "sucesso";
            
        }catch(Exception $e){
            echo $e;
            echo "<br>";
            $mensagem = "Erro no banco!";
            $tipoMensagem = "erro";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Filmes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-while h-screen flex items-center justify-center">
    <div class="bg-gray-800 p-8 rounded-xl shadow-2xl max-w-md w-full border border-gray-700">
        <h1 class="text-3xl font-bold text-center mb-6 text-pink-500">
            🤖 Importador Pro
        </h1>
        <p class="text-gray-400 text-sm mb-4 text-center">
            Cole sua chave de API do The Movie Database abaixo para popular o banco com os sucesso da semana.
        </p>

        <?php if($mensagem): ?>
            <div class="p-4 mb-6 rounded-lg text-center font-bold <?=$tipoMensagem == 'sucesso' ? 'bg-green-600 text-white' : 'bg-red-600 text-white' ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif;?>

        <form class="space-y-4" method="post">
            <div>
                <label class="block text-sm mb-1">Sua Chave de API (v3)</label>
                <input type="text" name="api_key" required placeholder="Ex: 81737642a..." class="w-ful bg-gray-900 border border-gray-600 rounded-lg p-3 focus:border-pink-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 rounded-lg transition">
                📥 Baixar Filmes
            </button>
        </form>
        <div class="mt-6 text-center">
            <a href="index.php" class="text-gray-500 hover:text-white text-sm underline">Voltar para o Início</a>
        </div>

    </div>
    
</body>
</html>