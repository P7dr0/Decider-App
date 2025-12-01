<?php
// public/api_providers.php
header('Content-Type: application/json');

// --- CONFIGURAÇÃO ---

$apiKey = 'c942e42c5709ce00fe6772c631e4c760'; 
$tmdbId = $_GET['id'] ?? '';
$type   = $_GET['type'] ?? 'movie'; // 'movie' (filme) ou 'tv' (serie)

// O banco salva como 'filme'/'serie', mas a API usa 'movie'/'tv'
if ($type === 'filme') $type = 'movie';
if ($type === 'serie') $type = 'tv';

if (!$tmdbId || !$apiKey) {
    echo json_encode([]);
    exit;
}

// URL da API para buscar provedores (BR)
$url = "https://api.themoviedb.org/3/{$type}/{$tmdbId}/watch/providers?api_key={$apiKey}";
$response = @file_get_contents($url);

if ($response) {
    $data = json_decode($response, true);
    
    // Pega apenas os serviços de assinatura (flatrate) do Brasil (BR)
    $providers = $data['results']['BR']['flatrate'] ?? [];

    $watchLink = $data['results']['BR']['link'] ?? "https://www.google.com/search?q=assistir+{$type}+{$tmdbId}";
    
    // Devolve só o nome e a logo
    $nomesJaVistos = []; // Lista para controlar duplicatas

    foreach($providers as $p) {
        $nome = $p['provider_name'];

        // Se já adicionamos esse streaming antes, pula para o próximo
        if (in_array($nome, $nomesJaVistos)) {
            continue;
        }

        // Se é novo, marca como visto e adiciona na lista final
        $nomesJaVistos[] = $nome;

        $cleanList[] = [
            'name' => $nome,
            'logo' => "https://image.tmdb.org/t/p/original" . $p['logo_path'],
            'url' => $watchLink
        ];
    }
    
    echo json_encode($cleanList);
} else {
    echo json_encode([]);
}
?>