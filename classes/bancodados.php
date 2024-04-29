<?php

class BancoDados{

    public static function Conexao(){
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $base = 'api';

        try{
            $conexao = new PDO("mysql:host={$host};dbname={$base};charset=UTF8;", $user, $pass);

            return $conexao;
        }catch(PDOException $e){
            echo json_encode(["Erro" => $e->getMessage()]);
            http_response_code(500);
            die();
        }

        
    }

}
