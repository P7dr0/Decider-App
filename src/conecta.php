<?php

class conexao {

    public static function conectar(){

        $config =require __DIR__ . '/../config/database.php';

        try{
            $pdo = new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4",$config['username'],$config['password']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        }
        catch(PDOException $e){
            die("Erro na conexão: " . $e->getMessage());
        }
    }
}

