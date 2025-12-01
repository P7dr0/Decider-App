<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../src/conecta.php';

$method = $_SERVER['REQUEST_METHOD'];


try {
    $pdo = conexao::conectar();
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro conexao']);
    exit;
}

// --- ROTA GET (BUSCAR DADOS) ---
if ($method === 'GET') {
    $userId = $_GET['user_id'] ?? '';
    $room   = $_GET['room'] ?? ''; 
    $check  = $_GET['check'] ?? false;

    // ====================================================
    // MODO 1: VERIFICAÇÃO DE MATCH (Polling / Espião)
    // Só entra aqui se tiver ?check=1 na URL
    // ====================================================
    if ($check && $room) {
        $sql = "SELECT item_id, COUNT(DISTINCT user_id) as total 
                FROM votes 
                WHERE room_code = :room AND vote = 1 
                GROUP BY item_id HAVING total >= 2 LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['room' => $room]);
        $matchData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($matchData) {
            $stmtItem = $pdo->prepare("SELECT title, image_url, description, tmdb_id, category FROM items WHERE id = ?");
            $stmtItem->execute([$matchData['item_id']]);
            $filme = $stmtItem->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['match' => true, 'data' => $filme]);   
        } else {
            echo json_encode(['match' => false]);
        }
        exit; // IMPORTANTE: Para o script aqui se for só checagem!
    }

    // ====================================================
    // MODO 2: BUSCAR PRÓXIMO FILME (Com Filtro)
    // Entra aqui quando o app pede um card novo
    // ====================================================
    
    // 1. Descobre o gênero da sala (Se tiver sala)
    $generoSala = null;
    if ($room) {
        $stmtRoom = $pdo->prepare("SELECT genre, media_type FROM rooms WHERE code = ?");
        $stmtRoom->execute([$room]); // Corrigido: array com a variável $room
        $salaData = $stmtRoom->fetch(PDO::FETCH_ASSOC);
        $generoSala = $salaData['genre'] ?? null;
        $midiaSala = $salaData['media_type'];
    }

    // 2. Query Base: Pega itens que o usuário NÃO votou ainda
    $sql = "SELECT * FROM items 
            WHERE id NOT IN (SELECT item_id FROM votes WHERE user_id = :uid)";
    
    $params = ['uid' => $userId];

    // 3. Aplica o Filtro se a sala tiver gênero definido
    if ($generoSala) {
        $sql .= " AND genre = :genero";
        $params['genero'] = $generoSala;
    }

    if($midiaSala !== 'ambos'){
        $sql .= " AND category = :cat";
        $params['cat'] = $midiaSala;
    }

    $sql .= " ORDER BY RAND() LIMIT 1";

    // 4. Executa e Retorna
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($item ?: ['error' => 'Acabaram os filmes dessa categoria!']);
    exit;
}

// --- ROTA POST (VOTAR) ---
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $room  = $input['room'] ?? '';
    $user  = $input['user'] ?? '';
    $item  = $input['item'] ?? 0;
    $vote  = $input['vote'] ?? 0;

    try {
        $sqlInsert = "INSERT IGNORE INTO votes (room_code, user_id, item_id, vote) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sqlInsert);
        $stmt->execute([$room, $user, $item, $vote]);

        // Verifica Match Imediato (Opcional, já temos o polling mas ajuda na UX)
        $matchFound = false;
        $filmeMatch = null;
        
        if ($vote == 1) {
            $sqlMatch = "SELECT COUNT(DISTINCT user_id) as total FROM votes 
                         WHERE room_code = ? AND item_id = ? AND vote = 1";
            $stmtMatch = $pdo->prepare($sqlMatch);
            $stmtMatch->execute([$room, $item]);
            $result = $stmtMatch->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['total'] >= 2) {
                $matchFound = true;
                $stmtItem = $pdo->prepare("SELECT title, image_url, description, tmdb_id, category FROM items WHERE id = ?");
                $stmtItem->execute([$item]);
                $filmeMatch = $stmtItem->fetch(PDO::FETCH_ASSOC);
            }
        }

        echo json_encode([
            'status' => 'success',
            'match'  => $matchFound,
            'data'   => $filmeMatch
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>