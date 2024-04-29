<?php

class Rotas
{

    private $listaAPIRotas          = [''];
    private $listaAPIHeader         = [''];
    private $listaAPICallback       = [''];
    private $listaAPIProtecao       = [''];

    //Rotas permitidas na API
    public function rotasPermitidas(array $metodo,array $header, string $rota, string $callback, bool $protecao){
        //Verifica o array de rotas API
        foreach($metodo as $value){
            $this->listaAPIRotas[]      = strtoupper($value).':'.$rota;
            $this->listaAPIHeader[]     = $header;
            $this->listaAPICallback[]   = $callback;
            $this->listaAPIProtecao[]   = $protecao;
        }

        return $this;
    }

    //Trás a rota permitida com method GET,POST,PUT,PATCH,DELETE
    public function rotaApi(string $rota){

        //Adição de log por chamada de rota
        $this->logs();

        $codigo     = '';
        $callback   = '';
        $protecao   = '';

        //Method de execução do ambiente
        $metodo = $_SERVER['REQUEST_METHOD'];

        //Caso exista no post a variavel _method faz a substituição
        $metodo = isset($_POST['_method']) ? $_POST['_method'] : $metodo;

        //Junta a rota recebida com o method ex: POST:/oauth/token
        $rota = $metodo.":/".$rota;


        //Verifica rotas que é passado o parâmetro caso GET
        if(substr_count($rota, "/") >= 3){
            $codigo = substr($rota, strrpos($rota, "/")+1);
            //Se a rota de consulta, alteração ou exclusão não for inteiro irá apresentar a mensagem
            if(!is_numeric($codigo)){
                http_response_code(400);
                echo json_encode(["dados" => "A variavel informada deve ser do tipo inteiro."]);
                exit();
            }
            $rota = substr($rota, 0, strrpos($rota, "/"))."/[VAR]";
        }
        
        $indice = array_search($rota, $this->listaAPIRotas);
        if ($indice > 0) {
            //Pega a função da classe
            $callback = explode("::", $this->listaAPICallback[$indice]);
            //Caso a rota precise de login ficara como true
            $protecao   = $this->listaAPIProtecao[$indice];
            $headerRota = $this->listaAPIHeader[$indice];
        }

        $class = isset($callback[0]) ? $callback[0] : '';
        $method = isset($callback[1]) ? $callback[1] : '';


        //verifica se a classe existe
        if (class_exists($class)){
            //Verifica se o method existe
            if (method_exists($class, $method)){
                //Caso o metodo e a classe exista faz a instanciação
                $instanciaClass = new $class();
                //Caso a rota necessite de access_token é verificado a validade
                if($protecao){
                    //Pega no token no header
                    $headers = apache_request_headers();
                    
                    //Validação dos Headers
                    foreach($headerRota as $key => $value){
                        if(!empty($key) && !isset($headers[$key])){
                            //Erro do header faltando o Content-Type: application/x-www-form-urlencoded
                            http_response_code(401);
                            echo json_encode(['ERRO' => 'Header com problema faltando Content-Type: application/x-www-form-urlencoded.']);
                            exit();
                        }
                    }
                    
                    if(isset($headers['authorization'])){
                        $token = str_replace("Bearer ", "", $headers['authorization']);
                    }else if(isset($headers['Authorization'])){
                        $token = str_replace("Bearer ", "", $headers['Authorization']);
                    }else{
                        //Erro do header faltando o token
                        http_response_code(401);
                        echo json_encode(['ERRO' => 'Access_token é inválido, verifique se cabeçalho novamente.']);
                        exit();
                    }
                    //Fim validação dos Headers

                    //Verifica se usuário tem o token válido
                    $verificacao = new Usuarios();
                    $usuarioAut = $verificacao->verificar($token);
                    if ($usuarioAut[0]) {
                        return call_user_func_array(array($instanciaClass, $method),array($codigo,$usuarioAut[1]));
                    }else{
                        http_response_code(401);
                        echo json_encode(["dados" => "Access_token inválido. Deve ser gerado um novo para o consumo da API."]);
                    }
                }else{
                    return call_user_func_array( array($instanciaClass, $method),array($codigo));
                }
            }else{
                $this->erroRota();
            }
        }else{
            $this->erroRota();
        }
    }

    //Mensagem de erro de rota
    private function erroRota(){
        http_response_code(404);
        echo json_encode(["codigoErro" => "404", "mensagemErro" => "O recurso solicitado não está disponível"]);
    }

    //Log de conexões da API
    private function logs(){
        if(isset($_SERVER['REMOTE_ADDR'])){
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if(isset($_SERVER['REMOTE_PORT'])){
            $porta = $_SERVER['REMOTE_PORT'];
        }
        if(isset($_GET['path'])){
            $endpoint = $_GET['path'];
        }
        if(isset($_SERVER['HTTP_USER_AGENT'])){
            $browser = $_SERVER['HTTP_USER_AGENT'];
        }
        $variaveis = [];
        if(isset($_POST)){
            $variaveis['POST'] = $_POST;
        }
        if(isset($_GET)){
            $variaveis['GET'] = $_GET;
        }
        //Method de execução do ambiente
        if(isset($_SERVER['REQUEST_METHOD'])){
            $metodo = $_SERVER['REQUEST_METHOD'];
        }
        //Caso exista no post a variavel _method faz a substituição
        $metodo = isset($_POST['_method']) ? $_POST['_method'] : $metodo;

        if(!empty($ip) && !empty($porta) && !empty($endpoint) && !empty($browser) && !empty($variaveis) && !empty($metodo)){
            $sql = "INSERT INTO logs (";
            $sql .= "ip, porta, endpoint, navegador, data_consulta, variaveis, method";
            $sql .= ") VALUES (";
            //Evitar SQL Injection
            $sql .= "'{$ip}', '{$porta}', '{$endpoint}', '{$browser}', '".date('Y-m-d H:i:s')."', '".addslashes(htmlspecialchars(json_encode($variaveis)))."', '{$metodo}' ";
            $sql .= ")";

            $db = BancoDados::Conexao();
            $rs = $db->prepare($sql);
            $rs->execute();
        }
        
    }
}