<?php
// die('ESTOU VIVO! Se você ler isso, o arquivo foi carregado.');
require_once __DIR__ . '/../src/conecta.php';


// Função para gerar código aleatório curto (4 caracteres)
function gerarCodigo($tamanho = 4) {
    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($caracteres), 0, $tamanho);
}

try {
    $pdo = conexao::conectar();
    $codigoSala = gerarCodigo();

    $genero = $_POST['genero'];
    if($genero === ''){
        $genero = null;
    }

    $tipoMidia = $_POST['tipo_midia'];

    // Prepara o SQL para inserir a nova sala
    $sql = "INSERT INTO rooms (code,genre,media_type) VALUES (:code,:genre,:media_type)";
    $stmt = $pdo->prepare($sql);
    
    // Executa a inserção
    $stmt->execute([
        'code' => $codigoSala,
        'genre' => $genero,
        'media_type' => $tipoMidia
    ]);

    // Redireciona o usuário para a "sala de espera/votação"
    // Vamos chamar de 'votar.php' (que faremos depois)
    header("Location: votar.php?sala=" . $codigoSala);
    exit;

} catch (PDOException $e) {
    die("Erro ao criar sala: " . $e->getMessage());
}
?>