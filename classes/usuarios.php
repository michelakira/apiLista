<?php

class Usuarios
{

    //Secret do JWT
    private $secretJWT = "02]g%(qyA70.";

    //Gera ou retorna o token consultado pela API
    public function token(){
        if($_POST){
            if (!isset($_POST['login']) || !isset($_POST['senha'])){
                $erros = [];
                if(!isset($_POST['login'])){
                    $erros[] = "Login não preenchido.";
                }
                if(!isset($_POST['senha'])){
                    $erros[] = "Senha não preenchido.";
                }

                if(!empty($erros)){
                    http_response_code(400);
                    echo json_encode(['CODIGO_ERRO' => '400', 'ERRO' => $erros]);
                }
                exit();
            }else{
                //Retira caracteres especiais
                //Evitar SQL Injection
                $login = addslashes(htmlspecialchars($_POST['login'])) ?? '';
                $senha = addslashes(htmlspecialchars($_POST['senha'])) ?? '';             


                $db     = BancoDados::Conexao();
                $query  = "SELECT * FROM usuarios WHERE login = '{$login}' AND senha = PASSWORD('{$senha}')";
                $rs     = $db->prepare($query);
                $rs->execute();
                $obj    = $rs->fetchObject();
                $rows   = $rs->rowCount();

                //Se encontrar o login e senha carrega a informações na variável
                if($rows > 0){
                    $codigo          = $obj->codigo;
                    $nameDB        = $obj->nome;
                    $tokenDB       = $obj->token;

                    //Verifica se o token é válido ainda, caso valido ele devolve o token
                    if(!empty($tokenDB)){
                        $decodedJWT     = MyJWT::decode($tokenDB, $this->secretJWT);
                        $dataExpiracao  = strtotime($decodedJWT['expires_in_date']);
                        $dataAtual      = strtotime(date('Y-m-d H:i:s'));
                        $intervalo      = $dataExpiracao - $dataAtual;
                        if($intervalo > 0){
                            
                            $expire_in_second = $intervalo;
                            $dataExpiracao = $decodedJWT['expires_in_date'];
                            $dadosToken     = [
                                'codigo'            => $codigo,
                                'nome'              => $nameDB,
                                'expires_in'        => $expire_in_second,
                                'expires_in_date'   => $dataExpiracao,
                            ];
        
                            http_response_code(200);
                            echo json_encode(['access_token' => $tokenDB, 'data' => $dadosToken ]);
                            return;
                        }
                    }
                    
                    $validUsername = true;
                    $validPassword = true;

                }else{
                    $validUsername = false;
                    $validPassword = false;
                }

                //Gera o token caso o login e senha forem válidos
                if($validUsername && $validPassword){
                    $expire_in_second = 600;
                    $dataAtualSegundos = strtotime(date('Y-m-d H:i:s'));
                    $dataExpiracao = $dataAtualSegundos+$expire_in_second;
                    
                    $token     = MyJWT::encode([
                        'codigo'            => $codigo,
                        'nome'              => $nameDB,
                        'expires_in'        => $expire_in_second,
                        'expires_in_date'   => date('Y-m-d H:i:s',$dataExpiracao),
                    ], $this->secretJWT);

                    $db->query("UPDATE usuarios SET token = '$token' WHERE codigo = $codigo LIMIT 1");
                    echo json_encode(['access_token' => $token, 'data' => MyJWT::decode($token, $this->secretJWT)]);
                }else{
                    http_response_code(400);
                    echo json_encode(['CODIGO_ERRO' => '400', 'ERRO' => 'Login ou senha informados não são válidos!']);
                }
            }
        }else{
            http_response_code(400);
            echo json_encode(['CODIGO_ERRO' => '400', 'ERRO' => 'Login e senha são obrigatórios']);
            exit();
        }
    }

    public function verificar($token){
        
        $db     = BancoDados::Conexao();
        $query  = "SELECT * FROM usuarios WHERE token = '{$token}' LIMIT 1";
        $rs     = $db->prepare($query);
        $rs->execute();
        $obj    = $rs->fetchObject();
        $rows   = $rs->rowCount();

        if($rows > 0){
            $codigo    = $obj->codigo;
            $tokenDB = $obj->token;

            $decodedJWT     = MyJWT::decode($tokenDB, $this->secretJWT);
            $dataExpiracao  = strtotime($decodedJWT['expires_in_date']);
            $dataAtual      = strtotime(date('Y-m-d H:i:s'));
            $intervalo      = $dataExpiracao - $dataAtual;
            if($intervalo > 0){
                return array(1, $codigo);
            }else{
                $db->query("UPDATE usuarios SET token = '' WHERE codigo = $codigo LIMIT 1");
                return array(0, $codigo);
            }
        }else{
            return array(0);
        }
    }
}
