<?php

require_once __DIR__ . '/../conecta.php';

class usuario {

    public function listarTodos() {
        // 1. Pega a conexão
        $pdo = conexao::conectar();

        // 2. Prepara o SQL
        $sql = "SELECT * FROM usuarios";
        
        // 3. Executa
        $stmt = $pdo->query($sql);

        // 4. Retorna os dados
        return $stmt->fetchAll();
    }
}

?>