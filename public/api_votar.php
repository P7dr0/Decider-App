<?php
// api_votar.php
header('Content-Type: application/json');
require_once __DIR__ . '/../src/conecta.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = conexao::conectar();
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao conectar: ' . $e->getMessage()]);
    exit;
}

// --- ROTA 1: BUSCAR O PRÓXIMO ITEM (GET) ---
if ($method === 'GET') {
    $userId = $_GET['user_id'] ?? '';
    
    // Seleciona um filme que esse usuário AINDA NÃO votou
    // (Isso evita mostrar o mesmo filme duas vezes)
    $sql = "SELECT * FROM items 
            WHERE id NOT IN (
                SELECT item_id FROM votes WHERE user_id = :uid
            ) 
            ORDER BY RAND() LIMIT 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $userId]);
    $item = $stmt->fetch();

    echo json_encode($item ?: ['error' => 'Acabaram os filmes!']);
    exit;
}

// --- ROTA 2: SALVAR VOTO E CHECAR MATCH (POST) ---
if ($method === 'POST') {
    // Lê o JSON enviado pelo Javascript
    $input = json_decode(file_get_contents('php://input'), true);
    
    $room  = $input['room'];
    $user  = $input['user'];
    $item  = $input['item'];
    $vote  = $input['vote']; // 1 (Like) ou 0 (Dislike)

    try {
        // 1. Salva o voto
        $sqlInsert = "INSERT IGNORE INTO votes (room_code, user_id, item_id, vote) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sqlInsert);
        $stmt->execute([$room, $user, $item, $vote]);

        // 2. Se foi LIKE, verifica se deu MATCH
        $matchFound = false;
        if ($vote == 1) {
            // A query mágica: conta quantos usuários DIFERENTES deram like nesse item nessa sala
            $sqlMatch = "SELECT COUNT(DISTINCT user_id) as total FROM votes 
                         WHERE room_code = ? AND item_id = ? AND vote = 1";
            $stmtMatch = $pdo->prepare($sqlMatch);
            $stmtMatch->execute([$room, $item]);
            $result = $stmtMatch->fetch();

            // Se tiver 2 ou mais likes, é MATCH!
            if ($result['total'] >= 2) {
                $matchFound = true;
                // Busca o nome do filme pra devolver
                $stmtItem = $pdo->prepare("SELECT title, image_url FROM items WHERE id = ?");
                $stmtItem->execute([$item]);
                $filmeMatch = $stmtItem->fetch();
            }
        }

        echo json_encode([
            'status' => 'success',
            'match'  => $matchFound,
            'data'   => $matchFound ? $filmeMatch : null
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}