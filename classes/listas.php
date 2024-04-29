<?php

class Listas
{
    

    //Obter todos os dados da lista do usuário
    public function getAll($codigo, int $usuario){
        $db     = BancoDados::Conexao();
        $query  = "SELECT * FROM listas WHERE usuario_codigo = '{$usuario}' ORDER BY data_entrada";
        $rs     = $db->prepare($query);
        $rs->execute();
        $obj    = $rs->fetchAll(PDO::FETCH_ASSOC);

        if($obj){
            echo json_encode(["dados" => $obj]);
        }else{
            echo json_encode(["dados" => 'Não existem dados para retornar']);
        }
        http_response_code(200);
    }

    //Obter os dados da lista especifica do usuário
    public function get(int $codigo, int $usuario){
        $db     = BancoDados::Conexao();
        $query  = "SELECT * FROM listas WHERE usuario_codigo = '{$usuario}' AND codigo = '{$codigo}' ORDER BY data_entrada LIMIT 1";
        $rs     = $db->prepare($query);
        $rs->execute();
        $obj    = $rs->fetchObject();

        if($obj){
            echo json_encode(["dados" => $obj]);
        }else{
            echo json_encode(["dados" => 'Não existem dados para retornar']);
        }
        http_response_code(200);
    }

    //Adicionar novas lista
    public function adicionar($codigo, int $usuario){
        if($_POST){
            //Verifica as variáveis foram preenchidas
            if (!isset($_POST['titulo']) || !isset($_POST['descricao'])){
                $erros = [];
                if(!isset($_POST['titulo'])){
                    $erros[] = "Título não preenchido.";
                }
                if(!isset($_POST['descricao'])){
                    $erros[] = "Descrição não preenchida.";
                }

                if(!empty($erros)){
                    http_response_code(400);
                    echo json_encode(['CODIGO_ERRO' => '400', 'ERRO' => $erros]);
                }
                exit();
            }else{

                //Validação to tamanho máximo de caracteres
                $erros = [];
                if(strlen($_POST['titulo']) > 150){
                    $erros[] = "Título deve conter no máximo 150 caracteres.";
                }
                if(strlen($_POST['descricao']) > 2000){
                    $erros[] = "Descrição deve conter no máximo 2000 caracteres.";
                }

                if(!empty($erros)){
                    http_response_code(400);
                    echo json_encode(['CODIGO_ERRO' => '400', 'ERRO' => $erros]);
                    exit();
                }
                

                $sql = "INSERT INTO listas (";
                $contador = 1;
                foreach (array_keys($_POST) as $indice) {
                    if(count($_POST) > $contador){
                        $sql .= "{$indice},";
                    }else{
                        $sql .= "{$indice}";
                    }
                    $contador++;
                }
                $sql .= ", usuario_codigo";
                $sql .= ", data_entrada";
                $sql .= ", status";
                $sql .= ") VALUES (";
                $contador = 1;
                foreach (array_values($_POST) as $valor) {
                    if(count($_POST) > $contador){
                        //Evitar SQL Injection
                        $sql .= "'".addslashes(htmlspecialchars($valor))."',";
                    }else{
                        //Evitar SQL Injection
                        $sql .= "'".addslashes(htmlspecialchars($valor))."'";
                    }
                    $contador++;
                }

                $sql .= ", '{$usuario}'";
                $sql .= ", CURRENT_TIMESTAMP()";
                $sql .= ", 1";
                $sql .= ")";


                $db = BancoDados::Conexao();
                $rs = $db->prepare($sql);
                $exec = $rs->execute();
                $codigoLista = $db->lastInsertId();

                if($exec){
                    http_response_code(200);
                    echo json_encode(["dados" => 'Dados foram inseridos com sucesso.', 'codigoLista' => $codigoLista]);
                }else{
                    http_response_code(406);
                    echo json_encode(["dados" => 'Houve algum erro ao inserir os dados.']);
                }
            }
        }else{
            //Caso a requisição não contenha campos POST é enviado um erro
            http_response_code(400);
            echo json_encode(['CODIGO_ERRO' => '400', 'ERRO' => 'Campos obrigatórios não informados']);
        }
    }

    public function update($codigo, int $usuario){

        $conteudo = file_get_contents('php://input');
        if(!empty($conteudo)){
            $conteudoExplode = explode('&',$conteudo);
            foreach($conteudoExplode as $value){
                $variaveis = explode('=', $value);
                $_POST[$variaveis[0]] = $variaveis[1];
            }
        }

        if($_POST){
            //Verifica as variáveis foram preenchidas
            $erros = [];
            $encontrouUmaAlteracao = 0;
            if(isset($_POST['titulo']) && strlen($_POST['titulo']) > 150){
                $erros[] = "Título deve conter no máximo 150 caracteres.";
                $encontrouUmaAlteracao = 1;
            }
            if(isset($_POST['descricao']) && strlen($_POST['descricao']) > 2000){
                $erros[] = "Título deve conter no máximo 2000 caracteres.";
                $encontrouUmaAlteracao = 1;
            }
            if(isset($_POST['status']) && (!is_numeric($_POST['status']) || ($_POST['status'] < 1) || $_POST['status'] > 2)){
                $erros[] = "Status deve conter o código 1 - Aberto ou 2 - Fechado.";
                $encontrouUmaAlteracao = 1;
            }

            if(!empty($erros) && $encontrouUmaAlteracao == 1){
                http_response_code(400);
                echo json_encode(['CODIGO_ERRO' => '400', 'ERRO' => $erros]);
                exit();
            }else{

                $db     = BancoDados::Conexao();
                $query  = "SELECT * FROM listas WHERE codigo = '{$codigo}' AND usuario_codigo = '{$usuario}' LIMIT 1";
                $rs     = $db->prepare($query);
                $exec   = $rs->execute();
                $rows   = $rs->rowCount();

                //Verifica se o registro existe e se existir faz o update
                if($rows > 0){

                    $variaveis = array("titulo", "descricao", "status"); 

                    $sql = "UPDATE listas SET ";

                    $contador = 1;
                    foreach (array_keys($_POST) as $indice) {
                        if(in_array($indice, $variaveis)){ 
                            //Evitar SQL Injection
                            $sql .= "{$indice} = '".addslashes(htmlspecialchars($_POST[$indice]))."',";
                        }
                    }
                    $sql = substr($sql, 0, -1);
                    $sql .= ", data_atualizacao = CURRENT_TIMESTAMP()";
                    $sql .= " WHERE codigo = {$codigo} AND usuario_codigo = '{$usuario}' LIMIT 1";

                    $db = BancoDados::Conexao();
                    $rs = $db->prepare($sql);
                    $exec = $rs->execute();

                    if($exec){
                        echo json_encode(["dados" => 'Dados atualizados com sucesso.']);
                    }else{
                        echo json_encode(["dados" => 'Houve erro ao atualizar dados.']);
                    }
                }else{
                    echo json_encode(["dados" => 'Registro não existe para realizar a alteração.']);
                }
                http_response_code(200);
            }
        }else{
            //Caso a requisição não contenha campos POST é enviado um erro
            http_response_code(400);
            echo json_encode(['CODIGO_ERRO' => '400', 'ERRO' => 'Campos obrigatórios não informados']);
        }
    }

    public function delete(int $codigo){

        $db     = BancoDados::Conexao();
        $query  = "SELECT * FROM listas WHERE codigo = '{$codigo}' LIMIT 1";
        $rs     = $db->prepare($query);
        $exec   = $rs->execute();
        $rows   = $rs->rowCount();

        //Verifica se o registro existe e se existir faz o delete
        if($rows > 0){

            $db     = BancoDados::Conexao();
            $query  = "DELETE FROM listas WHERE codigo = {$codigo} LIMIT 1";
            $rs     = $db->prepare($query);
            $exec   = $rs->execute();

            if ($exec) {
                echo json_encode(["dados" => 'Dados foram excluidos com sucesso.']);
            }else{
                echo json_encode(["dados" => 'Houve algum erro ao excluir os dados.']);
            }
        }else{
            echo json_encode(["dados" => 'Registro não existe ou já foi excluído.']);
        }
        http_response_code(200);
    }
}